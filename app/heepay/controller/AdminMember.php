<?php
namespace app\heepay\controller;

use app\AdminRest;
use app\heepay\model\Config;
use app\heepay\model\Member;
use app\massage\model\User;
use longbingcore\heepay\HeePay;
use longbingcore\wxcore\Adapay;
use think\App;

use think\facade\Db;
use think\Request;



class AdminMember extends AdminRest
{

    protected $model;

    protected $rights_model;

    protected $config_model;

    public function __construct(App $app) {

        parent::__construct($app);

        $this->model = new Member();

        $this->config_model= new Config();
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

           $where[] = ['a.bank_account_name','like','%'.$input['name'].'%'];

           $where[] = ['a.apply_no','like','%'.$input['name'].'%'];
       }

       $data = $this->model->adminList($dis,$input['limit'],$where);

       return $this->success($data);
   }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-11 11:39
     * @功能说明:添加账户
     */
   public function memberAdd(){

       $heepay_model = new HeePay($this->_uniacid);

       $input = $this->_input;

       $find = $this->model->where(['user_id'=>$input['user_id']])->where('status','>',-1)->find();

       if(!empty($find)){

           $this->errorMsg('该用户已有账户');
       }

       $list = $input['list'];

       unset($input['list']);

       $list['if_sign'] = 1;

       $res = $heepay_model->addUser($list);

       if($res['code']!=10000){

           $this->errorMsg($res['sub_msg']);
       }

       $input = array_merge($input,$list);

       $input['uniacid'] = $this->_uniacid;

       $input['apply_no']= $res['biz_content']['apply_no'];
       //创建结算方式
       $this->model->dataAdd($input);

       return $this->success(true,200);
   }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-11 14:17
     * @功能说明:编辑账户信息
     */
   public function memberUpdate(){

       $heepay_model = new HeePay($this->_uniacid);

       $input = $this->_input;

       $data = $this->model->dataInfo(['id'=>$input['id']]);

       $find = $this->model->where(['user_id'=>$data['user_id']])->where('id','<>',$input['id'])->where('status','>',-1)->find();

       if(!empty($find)){

           $this->errorMsg('该用户已有账户');
       }

       $list = $input['list'];

       $list['apply_no'] = $data['apply_no'];

       unset($input['list']);

       $list['if_sign'] = 1;

       $res = $heepay_model->updateUser($list);

       if($res['code']!=10000){

           $this->errorMsg($res['sub_msg']);
       }
       $input = array_merge($input,$list);

       $input['audit_status'] = 0;
       //创建结算方式
       $this->model->dataUpdate(['id'=>$input['id']],$input);

       return $this->success(true,200);
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

       $data = $this->model->dataInfo(['id'=>$input['id']]);

       $user_model = new User();

       $data['nickName'] = $user_model->where(['id'=>$data['user_id']])->value('nickName');

       return $this->success($data);
   }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-04-23 18:07
     * @功能说明:上传文件
     */
   public function fileAdd(){

       $input = $this->_input;

       $heepay_model = new HeePay($this->_uniacid);

       $input['file'] = FILE_UPLOAD_PATH.$input['file'];

       $res = $heepay_model->fileUpdate($input['file'],$input['type']);

       if($res['code']!=10000){

           $this->errorMsg($res['sub_msg']);
       }

       return $this->success($res);
   }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-20 15:14
     * @功能说明:代理商接口信息
     */
    public function adminHeepay(){

        $admin_model = new \app\massage\model\Admin();

        $user_id = $admin_model->where(['id'=>$this->_user['id']])->value('user_id');

        $data = $this->model->where(['user_id'=>$user_id])->where('status','>',-1)->find();

        return $this->success($data);

    }
















}
