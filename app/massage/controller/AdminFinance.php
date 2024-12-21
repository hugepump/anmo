<?php
namespace app\massage\controller;
use AlibabaCloud\SDK\Dyvmsapi\V20170525\Models\GetCallInfoResponseBody\data;
use app\AdminRest;
use app\balancediscount\model\OrderList;
use app\coachbroker\model\CoachBroker;
use app\massage\model\BalanceOrder;
use app\massage\model\CashUpdateRecord;
use app\massage\model\ChannelList;
use app\massage\model\City;
use app\massage\model\Coach;
use app\massage\model\CoachTimeList;
use app\massage\model\Commission;
use app\massage\model\Coupon;
use app\massage\model\CouponAtv;
use app\massage\model\CouponAtvRecord;
use app\massage\model\CouponRecord;
use app\massage\model\DistributionList;
use app\massage\model\Order;
use app\massage\model\Police;
use app\massage\model\ResellerRecommendCash;
use app\massage\model\Salesman;
use app\massage\model\Service;
use app\massage\model\ShortCodeConfig;
use app\massage\model\User;
use app\mobilenode\model\CoachCashRecord;
use app\mobilenode\model\RechargeList;
use app\shop\model\Article;
use app\shop\model\Banner;
use app\shop\model\Cap;
use app\shop\model\Date;
use app\shop\model\MsgConfig;
use app\shop\model\OrderAddress;
use app\shop\model\OrderGoods;
use app\shop\model\RefundOrder;
use app\shop\model\RefundOrderGoods;
use app\shop\model\Wallet;
use think\App;
use app\shop\model\Order as Model;
use think\facade\Db;


class AdminFinance extends AdminRest
{


    protected $model;

    protected $balance_order_model;

    protected $comm_model;

    protected $refund_model;

    protected $wallet_model;


    public function __construct(App $app) {

        parent::__construct($app);

        $this->model = new Order();

        $this->balance_order_model = new BalanceOrder();

        $this->comm_model = new Commission();

        $this->wallet_model = new \app\massage\model\Wallet();

        $this->refund_model = new \app\massage\model\RefundOrder();

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-04 19:09
     * @功能说明:财务列表统计
     */
    public function index(){

        $input = $this->_param;

        $this->model->initOrderData($this->_uniacid);

        $dis[] = ['uniacid','=',$this->_uniacid];

        if(!empty($input['start_time'])){

            $dis[] = ['create_time','between',"{$input['start_time']},{$input['end_time']}"];
        }

        $start_time = !empty($input['start_time'])?$input['start_time']:0;

        $end_time = !empty($input['end_time'])?$input['end_time']:0;
        //所有支付了的金额
        $pay_cash = $this->model->where($dis)->where('pay_time','>',0)->where('pay_model','in',[1,3])->sum('pay_price');
        //充值金额
        $arr['recharge_cash'] = $this->balance_order_model->where($dis)->where(['status'=>2,'type'=>1])->sum('pay_price');

        $balance_discount_pay_cash = OrderList::where(['pay_type'=>2])->where($dis)->sum('pay_price');

        $member_discount_pay_cash  = \app\memberdiscount\model\OrderList::where(['pay_type'=>2])->where($dis)->sum('pay_price');

        $arr['recharge_cash'] = round($arr['recharge_cash']+$balance_discount_pay_cash,2);
        //退款金额
        $refund_cash = $this->refund_model->financeOrderRefundCash($this->_uniacid,$start_time,$end_time,0,2);
        //营业额
        $arr['turnover'] = $pay_cash-$refund_cash+$arr['recharge_cash']+$member_discount_pay_cash;
        //服务费金额
        $arr['service_cash']     = $this->model->where($dis)->where(['is_add'=>0])->where('pay_time','>',0)->sum('service_price');
        //物料费
        $arr['material_cash']    = $this->model->where($dis)->where('pay_time','>',0)->where('start_material_price','>',0)->sum('start_material_price');
        //加钟服务金额
        $arr['add_service_cash'] = $this->model->where($dis)->where(['is_add'=>1])->where('pay_time','>',0)->sum('service_price');
        //退款金额
        $arr['refund_price']     = $this->refund_model->financeOrderRefundCash($this->_uniacid,$start_time,$end_time);
        //余额支付金额
        $arr['balance_cash']     = $this->model->where($dis)->where('pay_model','in',[2,4])->where('pay_time','>',0)->sum('pay_price');
        //退款金额
        $refund_balance_cash = $this->refund_model->financeOrderRefundCash($this->_uniacid,$start_time,$end_time,0,1);

        $arr['balance_cash'] -= $refund_balance_cash;
        //车费
        $arr['car_cash']  = $this->model->where($dis)->where('pay_time','>',0)->where(['free_fare'=>0])->sum('car_price');
        //支付佣金
        $arr['comm_cash'] = $this->comm_model->where($dis)->where('status','=',2)->where('type','in',[1,2,3,5,6,9,10,12,14])->sum('cash');
        //代理商收线下技师的服务费
        $admin_share_coach_cash = $this->comm_model->where($dis)->where('status','=',2)->where('type','in',[2,5,6])->sum('coach_cash');
        //代理商收线下技师的车费
        $admin_share_car_cash = $this->comm_model->where($dis)->where('status','=',2)->where('type','in',[2,5,6])->sum('car_cash');

        $arr['comm_cash'] = $arr['comm_cash']-$admin_share_coach_cash-$admin_share_car_cash;

        $recharge_model = new RechargeList();
        //代理商充值金额
        $arr['admin_recharge_cash'] = $recharge_model->where($dis)->where(['pay_type'=>2])->sum('cash');

        foreach ($arr as $k=>$v){

            $arr[$k] = round($v,2);
        }

        return $this->success($arr);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-02-06 17:15
     * @功能说明:曲线图
     */
    public function timeDataList(){

        $input = $this->_param;

        $start_time = $input['start_time'];

        $end_time   = $input['end_time'];

        while ($start_time<$end_time){

            $date = date('Y-m-d',$start_time);
            //所有支付了的金额
            $pay_cash = $this->model->where(['uniacid'=>$this->_uniacid])->whereDay('create_time',$date)->where('pay_time','>',0)->where('pay_model','<>',2)->sum('pay_price');
            //充值金额
            $recharge_cash = $this->balance_order_model->where(['status'=>2,'type'=>1,'uniacid'=>$this->_uniacid])->whereDay('create_time',$date)->sum('pay_price');
            //退款金额
            $refund_cash = $this->refund_model->financeOrderRefundCash($this->_uniacid,$start_time,$end_time,$date,2);

            $order_cash  = $this->model->where(['uniacid'=>$this->_uniacid])->whereDay('create_time',$date)->where('pay_time','>',0)->sum('pay_price');

            $list[] = [

                'date'       => date('m-d',$start_time),
                //营业额
                'turnover'   => round($pay_cash-$refund_cash+$recharge_cash,2),

                'order_cash' => round($order_cash,2),

                'refund_cash'=> $this->refund_model->financeOrderRefundCash($this->_uniacid,0,0,$date)
            ];

            $start_time+=86400;
        }

        return $this->success($list);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-02-04 16:58
     * @功能说明:服务项目排行
     */
    public function serviceTopList(){

        $input = $this->_param;

        $order_goods_model = new \app\massage\model\OrderGoods();

        $service_model = new Service();

        $admin_model = new \app\massage\model\Admin();

        $city_model  = new City();

        $dis[] = ['a.uniacid','=',$this->_uniacid];

        $dis[] = ['a.status','=',1];

        $dis[] = ['b.pay_time','>',0];

        if(!empty($input['start_time'])&&!empty($input['end_time'])){

            $dis[] = ['b.create_time','between',"{$input['start_time']},{$input['end_time']}"];
        }

        $service_id = $service_model->where('status','>',-1)->column('id');

        $dis[] = ['a.goods_id','in',$service_id];

        $data = $order_goods_model->alias('a')
                ->join('massage_service_order_list b','a.order_id = b.id')
                ->where($dis)
                ->field('a.goods_name,a.goods_id,a.goods_cover,sum(a.num) as goods_num,sum(a.true_price*a.num) as total_price')
                ->group('a.goods_id')
                ->order('total_price desc,a.id desc')
                ->paginate($input['limit'])
                ->toArray();

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $service = $service_model->where(['id'=>$v['goods_id']])->field('title,cover,admin_id')->find();

                $v['total_price'] = round($v['total_price'],2);

                if(!empty($service)){

                    $v['goods_name'] = $service['title'];

                    $v['goods_cover']= $service['cover'];
                }

                $admin_id = !empty($service)?$service['admin_id']:0;

                $v['admin_id'] = $admin_id;

                if(!empty($admin_id)){

                    $v['admin_name'] = $admin_model->where(['id'=>$admin_id])->value('agent_name');

                    $city_id   = $admin_model->where(['id'=>$admin_id])->value('city_id');

                    $v['city'] = $city_model->where(['id'=>$city_id])->value('title');

                }else{

                    $v['admin_name'] = '平台';
                }
            }
        }
        return $this->success($data);
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-03-14 11:55
     * @功能说明:发送验证码
     */
    public function sendShortMsg(){

        $input = $this->_input;
        //验证码验证
        $config = new ShortCodeConfig();

        if($this->_user['is_admin']==1){

            $phone  = getConfigSetting($this->_uniacid,'login_auth_phone');

        }else{

            $phone = $this->_user['login_auth_phone'];
        }

        $key    = 'updateCashKey'.$input['type'];

        $res    = $config->sendSmsCode($phone,$this->_uniacid,$key);

        if(!empty($res['Message'])&&$res['Message']=='OK'){

            return $this->success(1);

        }else{

            return $this->error($res['Message']);
        }
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-02-07 14:10
     * @功能说明:修改各类角色的余额
     */
    public function updateCash(){

        $input = $this->_input;

        $wallet_phone_check = getConfigSetting($this->_uniacid,'wallet_phone_check');

        if($wallet_phone_check==1){

            if(empty($input['phone_code'])){

                $this->errorMsg('请输入验证码');
            }

            if($this->_user['is_admin']==1){

                $phone  = getConfigSetting($this->_uniacid,'login_auth_phone');

            }else{

                $phone = $this->_user['login_auth_phone'];
            }

            $key = $phone.'updateCashKey'.$input['type'];

            if($input['phone_code']!= getCache($key,$this->_uniacid)){

                $this->errorMsg('验证码错误');
            }
        }
        //代理商操作
        if($this->_user['is_admin']==0&&$input['type']==3){

            $this->errorMsg('状态错误');
        }

        $record_model = new CashUpdateRecord();

        Db::startTrans();

        $res = $record_model->totalUpdateCash($this->_uniacid,$input['type'],$input['id'],$input['cash'],$input['is_add'],$input['text'],$this->_user['id']);

        if(!empty($res['code'])){

            Db::rollback();

            $this->errorMsg($res['msg']);
        }
        //如果是代理商操作需要加减代理商的余额
        if(in_array($input['type'],[1,2,4,5,6])&&in_array($this->_user['is_admin'],[0,3])){

           $is_add = $input['is_add']==1?0:1;

           $res = $record_model->totalUpdateCash($this->_uniacid,3,$this->_user['admin_id'],$input['cash'],$is_add,'',$this->_user['id'],$input['type'],$res,$input['id']);

            if(!empty($res['code'])){

                Db::rollback();

                $this->errorMsg($res['msg']);
            }
        }

        if($wallet_phone_check==1){

            delCache($key,$this->_uniacid);
        }

        Db::commit();

        return $this->success($res);
    }





    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-23 10:22
     * @功能说明:分销员统计
     */
    public function resellerList(){

        $input = $this->_param;

        $dis[] = ['a.uniacid','=',$this->_uniacid];

        if($this->_user['is_admin']==0){

            $dis[] = ['b.admin_id','=',$this->_user['admin_id']];
        }

        if(getFxStatus($this->_uniacid)==1){

            $dis[] = ['b.status','in',[2,3]];
        }

        $where = [];

        if(!empty($input['name'])){

            $where[] = ['a.nickName','like','%'.$input['name'].'%'];

            $where[] = ['b.user_name','like','%'.$input['name'].'%'];

            $where[] = ['b.mobile','like','%'.$input['name'].'%'];
        }

        if(!empty($input['start_time'])&&!empty($input['end_time'])){

            $dis[] = ['b.sh_time','between',"{$input['start_time']},{$input['end_time']}"];
        }

        $reseller_model = new DistributionList();

        $data = $reseller_model->userDataList($dis,$where,$input['limit']);

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $del_time = $reseller_model->where(['user_id'=>$v['user_id'],'status'=>-1])->max('del_time');

                $fx_check = !empty($del_time)?1:0;
                //总佣金
                $v['total_cash'] = $reseller_model->getOrderPrice($v['user_id'],$fx_check,$del_time,0,0);

                $recommend_cash  = ResellerRecommendCash::where(['reseller_id'=>$v['id'],'status'=>1])->sum('recommend_cash');

                $v['total_cash'] = round($v['total_cash']+$recommend_cash,2);
                //累计提现
                $v['wallet_cash']= $reseller_model->getWalletCash($v['user_id'],$fx_check,$del_time);
                //未入账
                $v['not_recorded_cash'] = $reseller_model->getOrderPrice($v['user_id'],$fx_check,$del_time,0,1);
            }
        }

        return $this->success($data);
    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-08-30 11:30
     * @功能说明:渠道商列表
     */
    public function channelList(){

        $input = $this->_param;

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['status','in',[2,3]];

        if($this->_user['is_admin']==0){

            $dis[] = ['admin_id','=',$this->_user['admin_id']];
        }

        if(!empty($input['name'])){

            $dis[] = ['user_name','like','%'.$input['name'].'%'];
        }

        $channel_model = new ChannelList();

        $data = $channel_model->where($dis)->order('id desc')->paginate($input['limit'])->toArray();

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $v['total_cash'] = $this->comm_model->where(['top_id'=>$v['id'],'type'=>10])->where('status','>',-1)->sum('cash');

                $v['not_recorded_cash'] = $this->comm_model->where(['top_id'=>$v['id'],'type'=>10])->where('status','=',1)->sum('cash');

                $v['wallet_cash'] = $this->wallet_model->where(['coach_id'=>$v['id'],'type'=>5])->where('status','<>',3)->sum('total_price');

                $v['total_cash'] = round($v['total_cash'],2);

                $v['not_recorded_cash'] = round($v['not_recorded_cash'],2);

                $v['wallet_cash'] = round($v['wallet_cash'],2);
            }
        }

        $data['total_cash'] = $channel_model->where($dis)->sum('cash');

        $data['total_cash'] = round($data['total_cash'],2);

        return $this->success($data);
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-21 11:17
     * @功能说明:业务员列表
     */
    public function salesmanList(){

        $input = $this->_param;

        $dis[] = ['uniacid','=',$this->_uniacid];
        //是否是代理商
        if($this->_user['is_admin']==0){

            $dis[] = ['admin_id','=',$this->_user['admin_id']];
        }

        $dis[] = ['status','in',[2,3]];

        if(!empty($input['name'])){

            $dis[] = ['user_name','like','%'.$input['name'].'%'];
        }

        $salesman_model = new Salesman();

        $data = $salesman_model->where($dis)->order('id desc')->paginate($input['limit'])->toArray();

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $v['total_cash'] = $this->comm_model->where(['top_id'=>$v['id'],'type'=>12])->where('status','>',-1)->sum('cash');

                $v['not_recorded_cash'] = $this->comm_model->where(['top_id'=>$v['id'],'type'=>12])->where('status','=',1)->sum('cash');

                $v['wallet_cash'] = $this->wallet_model->where(['coach_id'=>$v['id'],'type'=>6])->where('status','<>',3)->sum('total_price');

                $v['total_cash'] = round($v['total_cash'],2);

                $v['not_recorded_cash'] = round($v['not_recorded_cash'],2);

                $v['wallet_cash'] = round($v['wallet_cash'],2);
            }
        }

        $data['total_cash'] = $salesman_model->where($dis)->sum('cash');

        $data['total_cash'] = round($data['total_cash'],2);

        return $this->success($data);
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-15 14:43
     * @功能说明:列表
     */
    public function brokerList(){

        $input = $this->_param;

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['status','in',[2,3]];

        if(!empty($input['name'])){

            $dis[] = ['user_name','like','%'.$input['name'].'%'];
        }

        $broker_model = new CoachBroker();

        $data = $broker_model->where($dis)->order('id desc')->paginate($input['limit'])->toArray();

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $v['total_cash'] = $this->comm_model->where(['broker_id'=>$v['id'],'type'=>9])->where('status','>',-1)->sum('cash');

                $v['not_recorded_cash'] = $this->comm_model->where(['broker_id'=>$v['id'],'type'=>9])->where('status','=',1)->sum('cash');

                $v['wallet_cash'] = $this->wallet_model->where(['coach_id'=>$v['id'],'type'=>10])->where('status','<>',3)->sum('total_price');

                $v['total_cash'] = round($v['total_cash'],2);

                $v['not_recorded_cash'] = round($v['not_recorded_cash'],2);

                $v['wallet_cash'] = round($v['wallet_cash'],2);
            }
        }

        $data['total_cash'] = $broker_model->where($dis)->sum('cash');

        $data['total_cash'] = round($data['total_cash'],2);

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-04 09:54
     * @功能说明:数据大屏
     */
    public function dataScreen(){

        if($this->_user['is_admin']==0){

            return $this->success([]);
        }

        $order_model = new Order();

        $dis = [

            'uniacid' => $this->_uniacid,

            'status'  => 2
        ];

        $where = [];

        $coach_model = new Coach();
        //技师总数
        $data['coach']['total_count'] = $coach_model->where($dis)->where($where)->count();
        //待认证
        $data['coach']['notcertified_count'] = $coach_model->where($dis)->where($where)->where('auth_status','<>',2)->count();

        $work = CoachTimeList::getWorkOrResetCoach($this->_uniacid);

        $reset = CoachTimeList::getWorkOrResetCoach($this->_uniacid,2);

        $working_coach = $coach_model->getWorkingCoach($this->_uniacid);
        //休息技师
        $data['coach']['rest_count'] = $coach_model->where($dis)->where($where)->where('id','in',$reset)->count();
        //可服务
        $data['coach']['app_count']  = $coach_model->where($dis)->where($where)->where('id','in',$working_coach)->count();
        //在线
        $data['coach']['work_count'] = $coach_model->where($dis)->where($where)->where('id','in',$work)->count();

        $dis = [];

        $dis[] = ['pay_type','>',1];

        $dis[] = ['uniacid','=',$this->_uniacid];
        //订单信息
        $data['order']['today_order_cash']  = $order_model->where($dis)->whereTime('create_time','today')->sum('true_service_price');

        $data['order']['today_order_cash']  = round($data['order']['today_order_cash'],2);

        $data['order']['today_order_count'] = $order_model->where($dis)->whereTime('create_time','today')->count();

        $data['order']['total_order_cash']  = $order_model->where($dis)->sum('true_service_price');

        $data['order']['total_order_cash']  = round($data['order']['total_order_cash'],2);

        $data['order']['total_order_count'] = $order_model->where($dis)->count();
        //技师排行
        $dis = [];

        $dis[] = ['b.pay_type','=',7];

        $dis[] = ['a.status','=',2];

        $dis[] = ['a.uniacid','=',$this->_uniacid];

        $coach_data = $coach_model->alias('a')
            ->join('massage_service_order_list b','a.id = b.coach_id')
            ->where($dis)
            ->whereTime('b.create_time','-30 days')
            ->field('a.coach_name,a.admin_id,sum(b.true_service_price) as order_price')
            ->group('a.id')
            ->order('order_price desc,a.id desc')
            ->limit(10)
            ->select()
            ->toArray();
        if(!empty($coach_data)){

            foreach ($coach_data as &$v){

                $v['order_price']= round($v['order_price'],2);
            }
        }

        $data['coach_top'] = $coach_data;
        //最新订单
        $dis = [];

        $dis[] = ['pay_type','=',2];

        $dis[] = ['uniacid','=',$this->_uniacid];

        $data['new_order'] = $order_model->where($dis)->field('id as order_id,pay_price,create_time')->order('id desc')->limit(10)->select()->toArray();

        if(!empty($data['new_order'])){

            $address_model = new \app\massage\model\OrderAddress();

            $goods_model   = new \app\massage\model\OrderGoods();

            foreach ($data['new_order'] as $k=>$vs){

                $address = $address_model->dataInfo(['order_id'=>$vs['order_id']]);

                $data['new_order'][$k]['user_name'] = $address['user_name'];

                $data['new_order'][$k]['address'] = $address['address'].$address['address_info'];

                $data['new_order'][$k]['goods_name']= $goods_model->where(['order_id'=>$vs['order_id'],'status'=>1])->value('goods_name');
            }
        }
        //城市
        $dis = [

            'uniacid' => $this->_uniacid,

            'status'  => 1,

            'city_type'=> 1
        ];

        $city_model = new City();

        $data['city_list'] = $city_model->where($dis)->field('city,lng,lat')->select()->toArray();

        $end = strtotime(date('Y-m-d',time()));

        $start = $end-86400*29;

        $user_model = new User();

        $dis = [];

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['status','=',1];

        $i = 0;

        $arr = [];

        while ($start<=$end){

            $arr[$i]['time'] = $start;

            $time_text = date('Y-m-d',$start);

            $arr[$i]['time_text'] = date('m-d',$start);

            $user_count = $user_model->where($dis)->whereDay('create_time',$time_text)->count();

            $order_user_id = $order_model->where('pay_time','>',0)->whereDay('create_time',$time_text)->column('user_id');

            $order_user_count = $user_model->where($dis)->where('id','in',$order_user_id)->whereDay('create_time',$time_text)->count();
            //新用户
            $arr[$i]['new_user_count']  = $user_count;
            //下单用户
            $arr[$i]['order_user_count'] = $order_user_count;

            $start += 86400;

            $i++;
        }
        $data['user_data'] = $arr;

        $data['attendant_name'] = getConfigSetting($this->_uniacid,'attendant_name');

        return $this->success($data);
    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-08 17:15
     * @功能说明:报警
     */
    public function policeList(){

        $input = $this->_param;

        $dis[] = ['a.uniacid','=',$this->_uniacid];

        $dis[] = ['a.status','>',-1];

        if(isset($input['have_look'])){

            $dis[] = ['a.have_look','=',$input['have_look']];

        }

        if(!empty($input['start_time'])){

            $start_time = $input['start_time'];

            $end_time = $input['end_time'];

            $dis[] = ['a.create_time','between',"$start_time,$end_time"];
        }

        $police_model = new Police();

        $data = $police_model->noLogindataList($dis,$input['limit']);

        return $this->success($data);
    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-05-25 15:16
     * @功能说明:销售额订单数据
     */
    public function orderData(){

        if($this->_user['is_admin']==0){

            return $this->success([]);
        }

        $input = $this->_param;

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['pay_time','>',0];

        $dis[] = ['pay_type','>',1];

        $order_model = new Order();
        //全年
        if($input['day']==4){

            $start = strtotime(date('Y-01'));

            $i = 0;

            while ($i<12){

                $arr[$i]['month'] = $i+1;

                $arr[$i]['time'] = $start;

                $time_text = date('Y-m',$start);

                $arr[$i]['time_text'] = $time_text;
                //商城收益
                $shop_price = $order_model->where($dis)->whereMonth('create_time',$time_text)->sum('true_service_price');
                //录入订单
                $arr[$i]['shop_price']  = round($shop_price,2);

                $i++;

                $start = strtotime("$time_text +1 month");
            }

        }else{
            //自定义
            if($input['day']==5) {

                $start = $input['start_time'];

                $end   = $input['end_time'];

            }else{

                if($input['day']==1){

                    $time = 1;

                }elseif ($input['day']==2){

                    $time = 7;

                }else{

                    $time = 30;

                }

                $end   = strtotime(date('Y-m-d',time()));

                $start = $end - ($time-1)*86400;

            }

            $i = 0;

            while ($start<=$end){

                $arr[$i]['time'] = $start;

                $time_text = date('Y-m-d',$start);

                $arr[$i]['time_text'] = date('m-d',$start);

                $shop_price = $order_model->where($dis)->whereDay('create_time',$time_text)->sum('true_service_price');

                $arr[$i]['shop_price']  = round($shop_price,2);

                $start += 86400;

                $i++;
            }
        }

        return $this->success($arr);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-05-07 11:03
     * @功能说明:修改余额配置
     */
    public function walletCheckConfig(){

        $data['wallet_phone_check'] = getConfigSetting($this->_uniacid,'wallet_phone_check');
        //超级管理员需要获取平台授权手机号
        if($this->_user['is_admin']==1){

            $data['login_auth_phone'] = getConfigSetting($this->_uniacid,'login_auth_phone');

        }else{

            $data['login_auth_phone'] = $this->_user['login_auth_phone'];
        }

        return $this->success($data);
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-05-07 11:12
     * @功能说明:充值记录
     */
    public function adminRechargeList(){

        $input = $this->_param;

        $recharge_model = new RechargeList();

        $dis[] = ['a.pay_type','=',2];

        if(!empty($input['start_time'])){

            $dis[] = ['a.pay_time','between',"{$input['start_time']},{$input['end_time']}"];
        }
        //代理商端查询
        if($this->_user['is_admin']==0){

            $dis[] = ['a.admin_id','=',$this->_user['admin_id']];
        }

        $where = $dis;

        if(!empty($input['agent_name'])){

            $dis[] = ['b.agent_name','like','%'.$input['agent_name'].'%'];
        }

        $data = $recharge_model->alias('a')
                ->join('shequshop_school_admin b','a.admin_id = b.id','left')
                ->where($dis)
                ->field('a.*,b.agent_name')
                ->group('a.id')
                ->order('a.id desc')
                ->paginate($input['limit'])
                ->toArray();

        $data['total_recharge_cash'] = $recharge_model->alias('a')->where($where)->sum('cash');

        return $this->success($data);
    }





}
