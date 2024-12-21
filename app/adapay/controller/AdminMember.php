<?php
namespace app\adapay\controller;
use app\adapay\model\AccountsRecord;
use app\adapay\model\AdminPay;
use app\adapay\model\Bank;
use app\adapay\model\Config;
use app\adapay\model\Member;
use app\AdminRest;
use app\massage\model\User;
use longbingcore\wxcore\Adapay;
use think\App;

use think\facade\Db;
use think\Request;


//test11
class AdminMember extends AdminRest
{

    protected $model;

    protected $rights_model;

    protected $bank_model;



    public function __construct(App $app) {

        parent::__construct($app);

        $this->model = new Member();

        $this->bank_model = new Bank();


    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-11 16:25
     * @功能说明:
     */
    public function configInfo(){

        $config_model = new Config();

        $dis = [

            'uniacid' => $this->_uniacid
        ];

        $data = $config_model->dataInfo($dis);

        if($this->_user['is_admin']==0){

            return $this->success(['status'=>$data['status']]);
        }

        $data['commission'] = $config_model->where($dis)->value('commission');

        return $this->success($data);
    }






    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-11 16:26
     * @功能说明:
     */
    public function configUpdate(){

        $input = $this->_input;

        $config_model = new Config();

        $dis = [

            'uniacid' => $this->_uniacid
        ];

        $data = $config_model->dataUpdate($dis,$input);

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-23 09:20
     * @功能说明:列表
     * //type 1分销 2加盟商 3技师 9合伙人 10渠道商 11平台 12业务员
     */
   public function memberList(){

       $input = $this->_param;

       $dis[] = ['a.status','>',-1];

       $dis[] = ['a.uniacid','=',$this->_uniacid];

       $where = [];

       if(!empty($input['name'])){

           $where[] = ['b.card_id','like','%'.$input['name'].'%'];

           $where[] = ['b.card_name','like','%'.$input['name'].'%'];

       }

       $data = $this->model->adminList($dis,$input['limit'],$where);

       if(!empty($data['data'])){

           $user_model  = new User();

           foreach ($data['data'] as $k=>&$v){

               $v['user_name'] = $user_model->where(['id'=>$v['user_id']])->value('nickName');

           }
       }

       return $this->success($data);
   }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-11 11:39
     * @功能说明:添加账户
     */
   public function memberAdd(){

       $adapay_model = new Adapay($this->_uniacid);

       $input = $this->_input;

       if(empty($input['member_info']['user_id'])&&$this->_user['is_admin']==0){

           $input['member_info']['user_id'] = $this->_user['user_id'];
       }

       $find = $this->model->where(['user_id'=>$input['member_info']['user_id']])->where('status','>',-1)->find();

       if(!empty($find)){

           $this->errorMsg('该用户已有账户');
       }

       $member_id = orderCode();

       $member_info = $input['member_info'];

       $bank_info = $input['bank_info'];

       $member_info['member_id']= $member_id;

       $member_info['uniacid']  = $this->_uniacid;

       $member_info['order_no'] = orderCode();

       Db::startTrans();
       //创建账户
       if($member_info['is_company']==1){
           //企业
           $res = $adapay_model->createCompanyObj($member_info,1,0,$bank_info);

       }else{
           //个人
           $res = $adapay_model->createUserObj($member_id);
       }

       if($res['status']=='failed'){

           Db::rollback();

           $this->errorMsg($res['error_msg']);
       }

       if($res['status']=='pending'){

           $member_info['status'] = 0;
       }

       $this->model->dataAdd($member_info);

       $order_member_id = $this->model->getLastInsID();

       $bank_info['uniacid'] = $this->_uniacid;

       $bank_info['order_member_id'] = $order_member_id;

       $bank_info['member_id'] = $member_id;

       if($member_info['is_company']!=1){

           $res = $adapay_model->createAccountsObj($bank_info);

           if($res['status']=='failed'){

               Db::rollback();

               $this->errorMsg($res['error_msg']);
           }

           $bank_info['settle_account_id'] = $res['id'];
       }
       //创建结算方式
       $this->bank_model->dataAdd($bank_info);

       Db::commit();

       return $this->success(true,200,$order_member_id);
   }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-11 14:17
     * @功能说明:编辑账户信息
     */
   public function memberUpdate(){

       $adapay_model = new Adapay($this->_uniacid);

       $input = $this->_input;
       //账户
       $member_info = $input['member_info'];
       //结算账户
       $bank_info   = $input['bank_info'];

       $bank = $this->bank_model->dataInfo(['id'=>$input['bank_info']['id']]);

       $have = $this->model->where(['user_id'=>$member_info['user_id']])->where('status','>',-1)->where('id','<>',$member_info['id'])->find();

       if(!empty($have)){

           $this->errorMsg('该用户已有账户');
       }

       Db::startTrans();

       $member_info['order_no'] = orderCode();
       //企业
       if($member_info['is_company']==1){

           $res = $adapay_model->createCompanyObj($member_info,2,0,$bank_info);

           if($res['status']=='failed'){

               Db::rollback();

               $this->errorMsg($res['error_msg']);
           }

           if($res['status']=='pending'){

               $member_info['status'] = 0;
           }
       }

       $this->model->dataUpdate(['id'=>$member_info['id']],$member_info);

       $bank_info['member_id'] = $member_info['member_id'];
       //说明修改了结算账户
       //if($member_info['is_company']!=1){
           //先删除结算账户
           $res = $adapay_model->delAccountsObj($bank);

//           if($res['status']=='failed'){
//
//               Db::rollback();
//
//               $this->errorMsg($res['error_msg']);
//           }
           //创建新的账户
           $res = $adapay_model->createAccountsObj($bank_info);

           if($res['status']=='failed'){

               Db::rollback();

               $this->errorMsg($res['error_msg']);
           }

           $bank_info['settle_account_id'] = $res['id'];
      // }

       $this->bank_model->dataUpdate(['id'=>$bank_info['id']],$bank_info);

       Db::commit();

       return $this->success(true);
   }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-11 15:09
     * @功能说明:账号状态修改
     */
   public function memberStatusUpdate(){

       $input = $this->_input;

       $this->model->dataUpdate(['id'=>$input['id']],['status'=>$input['status']]);

       return $this->success(true);
   }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-11 15:11
     * @功能说明:账号详情
     */
   public function memberInfo(){

       $input = $this->_param;

       $data['member_info'] = $this->model->dataInfo(['id'=>$input['id']]);

       $data['bank_info'] = $this->bank_model->dataInfo(['order_member_id'=>$data['member_info']['id'],'status'=>1]);

       $user_model = new User();

       $data['member_info']['nickName'] = $user_model->where(['id'=>$data['member_info']['user_id']])->value('nickName');

       if(empty($data['bank_info']['cert_id'])){

           $data['bank_info']['cert_id'] = $data['member_info']['legal_cert_id'];
       }

       return $this->success($data);
   }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-17 19:04
     * @功能说明:平台充钱
     */
   public function adminPay(){

       $input = $this->_input;

       $model= new AdminPay();

       $res = $model->adminPay($this->_uniacid,$input['pay_price']);

       if($res['status']=='succeeded'){

           $code = getCode($this->_uniacid,$res['expend']['qrcode_url']);
           //两小时
           $expiration_time = $res['created_time']+60*120;

           $data['code'] = $code;

           $data['expiration_time'] = $expiration_time;

           $data['order_code'] = $res['id'];

       }else{

           return $this->error($res['error_msg']);
       }

       return $this->success($data);

   }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-19 12:56
     * @功能说明:查询平台支付是否完成
     */
   public function checkAdminPay(){

       $input = $this->_param;

       $order_model = new AdminPay();

       $dis = [

           'adapay_id' => $input['order_code'],

           'pay_type'  => 2
       ];

       $find = $order_model->dataInfo($dis);

       $res = !empty($find)?1:0;

       return $this->success($res);

   }





    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-18 18:53
     * @功能说明:平台账号详情
     */
   public function adminInfo(){

       $pay_model = new AdminPay();

       $cash = $pay_model->where(['pay_type'=>2])->sum('true_price');

       $canuse_cash = $pay_model->whereTime('pay_time','-547 days')->where(['pay_type'=>2])->sum('true_price');
       //余额
       $data['cash'] = round($canuse_cash,2);
       //过期余额
       $data['over_cash'] = round($cash-$canuse_cash,2);

       $record_model = new AccountsRecord();
       //被分账金额
       $data['accounts_cash'] = $record_model->where(['uniacid'=>$this->_uniacid])->sum('cash');

       return $this->success($data);
   }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-20 15:14
     * @功能说明:代理商接口信息
     */
    public function adminAdapay(){

        $admin_model = new \app\massage\model\Admin();

        $user_id = $admin_model->where(['id'=>$this->_user['id']])->value('user_id');

        $data['member_info'] = $this->model->where(['user_id'=>$user_id])->where('status','>',-1)->find();

        if(empty($data['member_info'])){

            return $this->success([]);

        }

        if(!empty($data['member_info'])){

            $data['bank_info'] = $this->bank_model->dataInfo(['order_member_id'=>$data['member_info']['id'],'status'=>1]);

            $user_model = new User();

            $data['member_info']['nickName'] = $user_model->where(['id'=>$data['member_info']['user_id']])->value('nickName');
        }

        return $this->success($data);

    }





















}
