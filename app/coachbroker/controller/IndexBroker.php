<?php
namespace app\coachbroker\controller;
use app\AdminRest;
use app\ApiRest;
use app\coachbroker\model\CoachBroker;
use app\massage\model\BrokerWater;
use app\massage\model\City;
use app\massage\model\Coach;
use app\massage\model\CoachLevel;
use app\massage\model\Commission;
use app\massage\model\CommShare;
use app\massage\model\Config;
use app\massage\model\DistributionList;
use app\massage\model\Order;
use app\massage\model\User;
use app\massage\model\Wallet;
use longbingcore\wxcore\YsCloudApi;
use think\App;
use think\facade\Db;


class IndexBroker extends ApiRest
{


    protected $model;

    protected $user_model;

    protected $cash_model;

    protected $wallet_model;

    protected $coach_model;

    protected $order_model;

    protected $broker_info;


    public function __construct(App $app) {

        parent::__construct($app);

        $this->model        = new CoachBroker();

        $this->user_model   = new User();

        $this->cash_model   = new Commission();

        $this->wallet_model = new Wallet();

        $this->coach_model  = new Coach();

        $this->order_model  = new Order();

        $this->broker_info  = $this->brokerInfo();

        if(empty($this->broker_info)){

            $this->errorMsg('你还不是经纪人');
        }

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-12-19 19:01
     * @功能说明:经纪人
     */
    public function brokerInfo(){

        $dis[] = ['user_id','=',$this->getUserId()];

        $dis[] = ['status','in',[2,3]];

        $data = $this->model->dataInfo($dis);

        return $data;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-23 13:49
     * @功能说明:合伙人中心
     */
    public function brokerIndex(){

        $order_model = new Order();
        //超时自动取消订单
        $order_model->coachBalanceArr($this->_uniacid);

        $data = $this->broker_info;

        $user = $this->user_model->dataInfo(['id'=>$this->_user['id']],'nickName,avatarUrl');

        $data = array_merge($data,$user);

        $data['cash'] = floatval($data['cash']);
        //累计佣金
        $data['total_cash'] = $this->model->partnerOrderCount($this->broker_info['id'],2);
        //累计单量
        $data['total_order_count']= $this->model->partnerOrderCount($this->broker_info['id']);
        //今日单量
        $data['today_order_count']= $this->model->partnerOrderCount($this->broker_info['id'],3);
        //未入帐佣金
        $data['not_recorded_cash']= $this->model->partnerOrderCount($this->broker_info['id'],4);
        //已提现金额
        $data['wallet_cash'] = $this->wallet_model->where(['coach_id'=>$this->broker_info['id'],'type'=>10])->where('status','<>',3)->sum('total_price');
        //累计邀请技师
        $data['total_coach_count'] = $this->coach_model->where(['broker_id'=>$this->broker_info['id'],'status'=>2])->count();
        //今日邀请技师
        $data['today_coach_count'] = $this->coach_model->where(['broker_id'=>$this->broker_info['id'],'status'=>2])->whereTime('partner_time','today')->count();

        $data['broker_poster'] = getConfigSetting($this->_uniacid,'broker_poster');

        $data['wallet_cash'] = round($data['wallet_cash'],2);

        return $this->success($data);
    }






    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-08-30 14:34
     * @功能说明:订单列表
     */
    public function brokerCashList(){

        $input = $this->_param;

        $dis[] = ['a.uniacid','=',$this->_uniacid];

        $dis[] = ['a.pay_type','>',1];

        $dis[] = ['a.broker_id','=',$this->broker_info['id']];

        if(!empty($input['start_time'])&&!empty($input['end_time'])){

            $dis[] = ['a.create_time','between',"{$input['start_time']},{$input['end_time']}"];
        }

        $where = [];

        if(!empty($input['name'])){

            $where[] = ['b.goods_name','like','%'.$input['name'].'%'];

            $where[] = ['a.order_code','like','%'.$input['name'].'%'];
        }

        $data = $this->order_model->brokerDataList($dis,$where);

        if(!empty($data['data'])){

            $user_model = new User();

            $share_model = new CommShare();

            foreach ($data['data'] as &$v){

                $v['nickName']  = $user_model->where(['id'=>$v['user_id']])->value('nickName');

                $v['point_cash']= $share_model->where(['comm_id'=>$v['comm_id'],'cash_type'=>1])->sum('share_cash');
            }
        }

        return $this->success($data);

    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-23 14:30
     * @功能说明:合伙人邀请的技师
     */
    public function brokerCoachList(){

        $input = $this->_param;

        $dis[] = ['status','=',2];

        $dis[] = ['broker_id','=',$this->broker_info['id']];

        if(!empty($input['coach_name'])){

            $dis[] = ['coach_name','like','%'.$input['coach_name'].'%'];

        }

        $data = $this->coach_model->where($dis)->field('admin_id,id,coach_name,work_img,city_id,sh_time')->order('partner_time desc,id desc')->paginate(10)->toArray();

        if(!empty($data['data'])){

            $city_model   = new City();

            $admin_model = new \app\massage\model\Admin();

            foreach ($data['data'] as &$v){

                $v['sh_time'] = date('Y-m-d H:i:s',$v['sh_time']);

                $v['city'] = $city_model->where(['id'=>$v['city_id'],'status'=>1])->value('city');

                $admin = $admin_model->dataInfo(['id'=>$v['admin_id'],'status'=>1]);
                //代理商
                $v['admin_name'] = !empty($admin)?$admin['agent_name']:'平台';
            }

        }
        return $this->success($data);
    }







    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-08-30 14:18
     * @功能说明 合伙人邀请技师码
     */
    public function resellerInvCoachQr(){

        $input = $this->_param;

        $admin_id = !empty($input['admin_id'])?$input['admin_id']:0;

        $key = 'resellerInvCoach_qr'.$this->_user['id'].'-'.$this->is_app.'-'.$admin_id;

        $qr  = getCache($key,$this->_uniacid);

        if(empty($qr)){
            //小程序
            if($this->is_app==0){

                $input['page'] = 'technician/pages/apply';

                $input['partner_id'] = $this->_user['id'];
                //获取二维码
                $qr = $this->user_model->orderQr($input,$this->_uniacid);

            }else{

                $page = 'https://'.$_SERVER['HTTP_HOST'].'/h5/#/technician/pages/apply?partner_id='.$this->_user['id'].'&admin_id='.$input['admin_id'];

                $qr = base64ToPng(getCode($this->_uniacid,$page));

            }

            setCache($key,$qr,86400,$this->_uniacid);
        }

        $qr = !empty($qr)?$qr:'https://'.$_SERVER['HTTP_HOST'].'/favicon.ico';

        return $this->success($qr);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-09 10:43
     * @功能说明:
     */
    public function adminList(){

        $admin_model = new \app\massage\model\Admin();

        $dis = [

            'status'       => 1,

            'is_admin'     => 0,

            'partner_auth' => 1,

            'agent_coach_auth' => 1
        ];

        $input = $this->_param;

        $where = [];

        if(!empty($input['nickName'])){

            $where[] =['agent_name','like','%'.$input['nickName'].'%'];
        }

        $data = $admin_model->where($dis)->where($where)->field('agent_name,id,city_type,user_id')->paginate(10)->toArray();

        $user_model = new User();

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $v['avatarUrl'] = $user_model->where(['id'=>$v['user_id']])->value('avatarUrl');
            }
        }

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-24 13:33
     * @功能说明:用户申请提现
     */
    public function applyWallet(){

        $input = $this->_input;

        $key = 'broker_wallet'.$this->broker_info['id'];
        //加一个锁防止重复提交
        incCache($key,1,$this->_uniacid,10);

        $value = getCache($key,$this->_uniacid);

        if($value!=1){

            decCache($key,1,$this->_uniacid);

            $this->errorMsg('网络错误，请刷新重试');
        }

        if(empty($input['apply_price'])||$input['apply_price']<0.01){

            decCache($key,1,$this->_uniacid);

            $this->errorMsg('提现费最低一分');
        }
        //服务费
        if($input['apply_price']>$this->broker_info['cash']){

            decCache($key,1,$this->_uniacid);

            $this->errorMsg('余额不足');
        }
        //获取税点
        $tax_point = getConfigSetting($this->_uniacid,'tax_point');

        $balance = 100-$tax_point;

        Db::startTrans();

        $insert = [

            'uniacid'       => $this->_uniacid,

            'user_id'       => $this->getUserId(),

            'coach_id'      => $this->broker_info['id'],

            'admin_id'      => 0,

            'total_price'   => $input['apply_price'],

            'balance'       => $balance,

            'apply_price'   => round($input['apply_price']*$balance/100,2),

            'service_price' => round( $input['apply_price'] * $tax_point / 100, 2),

            'tax_point'     => $tax_point,

            'code'          => orderCode(),

            'text'          => $input['text'],

            'type'          => 10,

            'last_login_type' => $this->is_app,

            'apply_transfer' => !empty($input['apply_transfer'])?$input['apply_transfer']:0

        ];

        $wallet_model = new Wallet();
        //提交审核
        $res = $wallet_model->dataAdd($insert);

        if($res!=1){

            Db::rollback();
            //减掉
            decCache($key,1,$this->_uniacid);

            $this->errorMsg('申请失败');
        }
        $id = $wallet_model->getLastInsID();

        $water_model = new BrokerWater();

        $res = $water_model->updateCash($this->_uniacid,$this->broker_info['id'],$input['apply_price'],2,$id,0,2);

        if ($res == 0) {

            Db::rollback();
            //减掉
            decCache($key,1, $this->_uniacid);

            $this->errorMsg('申请失败');
        }

        Db::commit();
        //减掉
        decCache($key,1,$this->_uniacid);

        return $this->success($res);

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-30 14:39
     * @功能说明:用户分销提现记录
     */
    public function walletList(){

        $wallet_model = new Wallet();

        $input = $this->_param;

        $dis = [

            'coach_id' => $this->broker_info['id']
        ];

        if(!empty($input['status'])){

            $dis['status'] = $input['status'];
        }

        $dis['type'] = 10;
        //提现记录
        $data = $wallet_model->dataList($dis,10);

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $v['create_time'] = date('Y-m-d H:i:s',$v['create_time']);
            }
        }
        //累计提现
        $data['extract_total_price'] = $wallet_model->capCash($this->broker_info,2,10);

        $data['personal_income_tax_text'] = getConfigSetting($this->_uniacid,'personal_income_tax_text');

        return $this->success($data);
    }















}
