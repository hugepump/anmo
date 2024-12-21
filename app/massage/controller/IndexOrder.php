<?php
namespace app\massage\controller;
use app\adapay\model\PayRecord;
use app\admin\controller\Arcv;
use app\ApiRest;

use app\balancediscount\model\Card;
use app\balancediscount\model\OrderShare;
use app\balancediscount\model\UserCard;
use app\massage\model\Address;
use app\massage\model\Car;
use app\massage\model\CarPrice;
use app\massage\model\Coach;
use app\massage\model\CoachChangeLog;
use app\massage\model\Comment;
use app\massage\model\CommentGoods;
use app\massage\model\CommentLable;
use app\massage\model\Config;

use app\massage\model\Coupon;
use app\massage\model\CouponRecord;
use app\massage\model\CreditConfig;
use app\massage\model\EmptyTicketFeeConfig;
use app\massage\model\Lable;
use app\massage\model\MassageConfig;
use app\massage\model\MsgConfig;
use app\massage\model\NoPayRecord;
use app\massage\model\NoPayRecordGoods;
use app\massage\model\NoticeList;
use app\massage\model\Order;
use app\massage\model\OrderAddress;
use app\massage\model\OrderData;
use app\massage\model\OrderGoods;
use app\massage\model\OrderLog;
use app\massage\model\OrderPrice;
use app\massage\model\PayConfig;
use app\massage\model\RefundOrder;
use app\massage\model\Service;
use app\massage\model\ShieldList;
use app\massage\model\StoreCoach;
use app\massage\model\UpOrderGoods;
use app\massage\model\UpOrderList;
use app\massage\model\User;
use app\massage\controller\IndexWxPay;
use app\massage\model\UserChannel;
use app\member\model\Level;
use app\memberdiscount\model\OrderList;
use app\partner\model\PartnerOrder;
use app\partner\model\PartnerOrderJoin;
use app\store\model\StoreList;
use longbingcore\wxcore\PayModel;
use longbingcore\wxcore\WxSetting;
use think\App;
use think\facade\Db;
use think\Request;


class IndexOrder extends ApiRest
{

    protected $model;

    protected $refund_model;

    protected $order_goods_model;

    protected $coach_model;

    public function __construct(App $app) {

        parent::__construct($app);

        $this->model = new Order();

        $this->refund_model = new RefundOrder();

        $this->order_goods_model = new OrderGoods();

        $this->coach_model = new Coach();
        //超时自动取消订单
        $this->model->autoCancelOrder($this->_uniacid);

    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-12 00:26
     * @功能说明:天数
     */
    public function dayText(){

        $config = $this->_config;

        $start_time = strtotime(date('Y-m-d',time()));

        $i=0;

        while ($i<$config['max_day']){

            $str = $start_time+$i*86400;

            $data[$i]['dat_str'] = $str;

            $data[$i]['dat_text'] = date('m-d',$str);

            $data[$i]['week'] = changeWeek(date('w',$str));

            $i++;
        }

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-09 14:41
     * @功能说明:时间段
     */
    public function timeText(){

        $input = $this->_param;

        $coach_model  = new Coach();

        $coach  = $coach_model->dataInfo(['id'=>$input['coach_id']]);

        $is_store = !empty($input['is_store'])?$input['is_store']:0;

        $time_long = !empty($input['time_long'])?$input['time_long']:0;

        $data = $this->getTimeData($coach['start_time'],$coach['end_time'],$coach['id'],$input['day'],0,$is_store,$time_long);

        return $this->success($data);

    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-19 15:48
     * @功能说明:个人中心
     */
    public function orderList(){

        if(empty($this->getUserId())){

            return $this->success([]);
        }

        $input = $this->_param;

        $dis[] = ['a.uniacid','=',$this->_uniacid];

        $dis[] = ['a.user_id','=',$this->getUserId()];

        $dis[] = ['a.is_show','=',1];

        $dis[] = ['a.is_add','=',0];

        $where = [];

        if(!empty($input['name'])){

            $where[] = ['b.goods_name','like','%'.$input['name'].'%'];

            $where[] = ['a.order_code','like','%'.$input['name'].'%'];
        }

        if(!empty($input['pay_type'])){

            if($input['pay_type']==5){

                $dis[] = ['a.pay_type','in',[2,3,4,5]];

            }else{

                $dis[] = ['a.pay_type','=',$input['pay_type']];
            }
        }
        //是否是加钟
        $data = $this->model->indexDataList($dis,$where);

        $fee_model = new EmptyTicketFeeConfig();

        $max_minute = $fee_model->where(['uniacid'=>$this->_uniacid])->max('minute');

        $after_service_can_refund = getConfigSetting($this->_uniacid,'after_service_can_refund');

        if(!empty($data['data'])){

            $shield_model = new ShieldList();

            foreach ($data['data'] as &$v){

                $v['can_refund']  = $this->model->canRefundOrder($v,$after_service_can_refund,$max_minute);
                //是否能加钟
                $v['can_add_order'] = $this->model->orderCanAdd($v);

                $shield = $shield_model->where(['user_id'=>$v['user_id'],'coach_id'=>$v['coach_id']])->where('type','in',[2,3])->find();

                $v['can_again'] = !empty($shield)?0:1;

                $v = $this->model->getInitCoachInfo($v);
            }
        }

        return $this->success($data);
    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-15 14:58
     * @功能说明:订单详情
     */
    public function orderInfo(){

        $input = $this->_param;

        $dis = [

            'id' => $input['id'],

            'is_show' => 1
        ];

        $data = $this->model->dataInfo($dis);

        if(empty($data)){

            $this->errorMsg('订单已被删除');
        }

        $fee_model = new EmptyTicketFeeConfig();

        $max_minute = $fee_model->where(['uniacid'=>$data['uniacid']])->max('minute');

        $after_service_can_refund = getConfigSetting($this->_uniacid,'after_service_can_refund');

        $data['can_refund']  = $this->model->canRefundOrder($data,$after_service_can_refund,$max_minute);

        $data['time_long'] = $data['true_time_long'];
        //是否能加钟
        $data['can_add_order'] = $this->model->orderCanAdd($data);

        $arr = ['create_time','pay_time','serout_time','arrive_time','receiving_time','start_service_time','order_end_time','coach_refund_time'];

        foreach ($arr as $value){

            $data[$value] = !empty($data[$value])?date('Y-m-d H:i:s',$data[$value]):0;
        }

        $data['start_time'] = date('Y-m-d H:i',$data['start_time']).'-'.date('H:i',$data['end_time']);

        $data['distance'] = distance_text($data['distance']);

        $add_time_long = $this->model->where(['add_pid'=>$data['id']])->where('pay_type','in',[4,5,6,7])->sum('true_time_long');

        $data['total_time_long'] = $data['true_time_long']+$add_time_long;

        $data['over_time'] -= time();

        $data['over_time']  = $data['over_time']>0?$data['over_time']:0;
        //查询是否有转派记录 处理技师信息
        $data = $this->model->getInitCoachInfo($data);

        if($data['is_add']==0){
            //加钟订单
            $data['add_order_id'] = $this->model->getAddOrderList($data['id']);

        }else{

            $data['add_pid'] = $this->model->where(['id'=>$data['add_pid']])->field('id,order_code')->find();
        }

        $order_model = new OrderData();
        //订单附表
        $order_data = $order_model->dataInfo(['order_id'=>$input['id'],'uniacid'=>$this->_uniacid]);

        $data = array_merge($order_data,$data);

        $data['sign_time'] = !empty($data['sign_time'])?date('Y-m-d H:i:s',$data['sign_time']):'';

        $shield_model = new ShieldList();

        $shield = $shield_model->where(['user_id'=>$data['user_id'],'coach_id'=>$data['coach_id']])->where('type','in',[2,3])->find();

        $data['can_again'] = !empty($shield)?0:1;

        $change_log_model = new CoachChangeLog();

        $have_car_price = $change_log_model->dataInfo(['order_id'=>$data['id'],'status'=>1,'have_car_price'=>1]);

        $have_apply_car = $this->refund_model->where(['order_id'=>$data['id']])->where('car_price','>',0)->where('status','in',[1,2,4,5])->find();
        //是否可以退车费
        $data['can_refund_car_price'] = empty($have_apply_car)&&empty($have_car_price)&&$data['pay_type']<4?1:0;

        $admin_model = new \app\massage\model\Admin();
        //代理商电话
        $data['admin_phone'] = $admin_model->where(['id'=>$data['admin_id']])->value('phone');

        $data['phone_encryption'] = $admin_model->where(['id'=>$data['admin_id']])->value('phone_encryption');
        //门店订单
        if(!empty($data['store_id'])){

            $store_model = new \app\massage\model\StoreList();

            $data['store_info'] = $store_model->where(['id'=>$data['store_id']])->field('title,cover,address,lng,lat,phone')->find();
        }
        //是否可以联系技师
        $data['order_contact_coach'] = getConfigSetting($this->_uniacid,'order_contact_coach');
        //客户端服务费需加上物料费
        $data['init_service_price']  = round($data['init_service_price']+$data['init_material_price'],2);

        if(!empty($data['order_goods'])){

            foreach ($data['order_goods'] as &$v){

                $v['price']      = round($v['price']+$v['init_material_price'],2);

                $v['true_price'] = round($v['true_price']+$v['material_price'],2);
            }
        }
        //加钟合集金额
        $data['total_add_price'] = $this->model->where(['add_pid'=>$data['id']])->where('pay_type','>',1)->sum('pay_price');

        $data['total_add_price'] = round($data['total_add_price'],2);
        //总金额
        $data['total_price']     = round($data['total_add_price']+$data['pay_price'],2);

        if(!empty($input['apply_refund'])){

            $refund_model = new RefundOrder();

            $have_empty = $refund_model->where(['order_id'=>$data['id']])->where('status','in',[1,2,4,5])->where('refund_empty_cash','>',0)->count();

            if(!empty($have_empty)){
                //空单费
                $data['empty_order_cash']= 0;
            }else{
                //空单费
                $data['empty_order_cash']= getConfigSetting($this->_uniacid,'empty_order_cash');
            }

            $fee_model = new EmptyTicketFeeConfig();

            $data['refund_cash_list'] = $fee_model->where(['uniacid'=>$this->_uniacid])->select()->toArray();
        }
        //储值卡折扣
        if($data['pay_model']==4){

            $share_model = new OrderShare();

            $balance_discount_data = $share_model->orderShareData($data['id']);

            $data = array_merge($data,$balance_discount_data);
        }

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-23 18:32
     * @功能说明:获取加钟订单
     */
    public function getAddClockOrder(){

        $input = $this->_param;

        $dis[] = ['a.add_pid','=',$input['order_id']];

        $dis[] = ['a.pay_type','>',1];

        $dis[] = ['a.is_show','=',1];

        $data = $this->model->indexDataList($dis,[]);

        if(!empty($data['data'])){

            $shield_model = new ShieldList();

            foreach ($data['data'] as &$v){

                $can_refund_num = is_array($v['order_goods'])?array_sum(array_column($v['order_goods'],'can_refund_num')):0;

                if((in_array($v['pay_type'],[2,3,4,5])&&$can_refund_num>0)){

                    $v['can_refund'] = 1;

                }else{

                    $v['can_refund'] = 0;
                }

                $shield = $shield_model->where(['user_id'=>$v['user_id'],'coach_id'=>$v['coach_id']])->where('type','in',[2,3])->find();

                $v['can_again'] = !empty($shield)?0:1;
            }
        }

        return $this->success($data);

    }





    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-19 17:29
     * @功能说明:退款订单详情
     *
     */
    public function refundOrderList(){

        $input = $this->_param;

        $dis[] = ['a.uniacid','=',$this->_uniacid];

        $dis[] = ['d.is_show','=',1];

        $dis[] = ['a.user_id','=',$this->getUserId()];

        $where = [];

        if(!empty($input['name'])){

            $where[] = ['b.goods_name','like','%'.$input['name'].'%'];

            $where[] = ['a.order_code','like','%'.$input['name'].'%'];
        }

        if(!empty($input['status'])){

            $dis[] = ['a.status','=',$input['status']];

        }else{

            $dis[] = ['a.status','>',-1];

        }

        $data = $this->refund_model->indexDataList($dis,$where);

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $v = $this->model->getInitCoachInfo($v,2);

                if(!empty($v['order_goods'])){

                    foreach ($v['order_goods'] as &$vs){

                        $vs['goods_price']  = round($vs['goods_price']+$vs['material_price'],2);
                    }
                }
            }
        }
        //待接单数量
        $data['agent_order_count'] = $this->refund_model->where(['user_id'=>$this->getUserId(),'status'=>1])->count();

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-19 17:50
     * @功能说明:退款订单详情
     */

    public function refundOrderInfo(){

        $input = $this->_param;

        $dis = [

            'id' => $input['id']
        ];

        $data = $this->refund_model->dataInfo($dis);

        $data['create_time'] = date('Y-m-d H:i:s',$data['create_time']);

        $data['refund_time'] = date('Y-m-d H:i:s',$data['refund_time']);

        $order = $this->model->dataInfo(['id'=>$data['order_id']]);

        $data = $this->model->getInitCoachInfo($data,2);

        if(!empty($order)){
            //加钟订单
            if($order['is_add']==0){

                $data['add_order_id'] = $this->model->where(['add_pid'=>$order['id']])->where('pay_type','>',1)->field('id,order_code')->select()->toArray();

            }else{

                $data['add_pid'] = $this->model->where(['id'=>$order['add_pid']])->field('id,order_code')->find();
            }
        }
        $data['store_id'] = $order['store_id'];
        //门店订单
        if(!empty($order['store_id'])){

            $store_model = new \app\massage\model\StoreList();

            $data['store_info'] = $store_model->where(['id'=>$order['store_id']])->field('title,cover,address,lng,lat,phone')->find();
        }
        if(!empty($data['order_goods'])){

            foreach ($data['order_goods'] as &$vs){

                $vs['goods_price']  = round($vs['goods_price']+$vs['material_price'],2);
            }
        }
        //审核人员
        $data['check_user_name'] = $this->refund_model->checkUserName($data);

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-22 09:43
     * @功能说明:下单页面详情
     */
    public function payOrderInfo(){

        $input = $this->_param;

        $coupon   = !empty($input['coupon_id'])?$input['coupon_id']:0;

        $order_id = !empty($input['order_id'])?$input['order_id']:0;

        $pay_model = !empty($input['pay_model'])?$input['pay_model']:1;

        $address_model = new Address();

        $coupon_modle  = new Coupon();

        $coupon_record_model  = new CouponRecord();

        $user_model = new User();

        if(!empty($input['address_id'])){

            $address = $address_model->dataInfo(['id'=>$input['address_id']]);

        }else{

            $address = $address_model->dataInfo(['user_id'=>$this->getUserId(),'status'=>1]);
        }
        $service_type = !empty($input['is_store'])&&$input['is_store']==1?2:1;
        //加钟订单
        if(!empty($order_id)){

            $input['car_type'] = 0;

            $p_order = $this->model->dataInfo(['id'=>$order_id]);

            $address = $p_order['address_info'];

            $service_type = !empty($p_order['store_id'])?2:1;
        }

        if($service_type==2){

            $address['lat'] = !empty($input['lat'])?$input['lat']:0;

            $address['lng'] = !empty($input['lng'])?$input['lng']:0;
        }
        //可用优惠券数量
        $canUseCoupon = $coupon_modle->canUseCoupon($this->getUserId(),$input['coach_id']);

        if(empty($input['is_remember'])&&empty($order_id)&&$pay_model!=4){

            if(!empty($input['coupon_id'])){

                $coupon = $input['coupon_id'];

            }elseif (!empty($canUseCoupon)&&!isset($input['coupon_id'])){

                $coupon = $canUseCoupon[0];
            }
        }

        $lat = !empty($address['lat'])?$address['lat']:0;

        $lng = !empty($address['lng'])?$address['lng']:0;

        $start_time = !empty($input['start_time'])?$input['start_time']:time();

        $order_info = $this->model->payOrderInfo($this->getUserId(),$input['coach_id'],$lat,$lng,$input['car_type'],$coupon,$order_id,$service_type,$start_time,$input);

        if(!empty($order_info['order_goods'])){

            foreach ($order_info['order_goods'] as &$v){

                $v['price'] = round($v['price']+$v['material_price'],2);
            }
        }
        //默认地址
        $order_info['address_info'] = $address;

        $coach_model = new Coach();

        if(!empty($input['coach_id'])&&$input['coach_id']>0){

            $order_info['coach_info'] = $coach_model->where(['id'=>$input['coach_id']])->field('id,city_id,coach_name,mobile,work_img,store_id')->find();

        }else{

            $change_log_model = new CoachChangeLog();

            $change_log = $change_log_model->dataInfo(['order_id'=>$order_id,'status'=>1]);

            $order_info['coach_info'] = $coach_model->where(['id'=>$change_log['init_coach_id']])->field('id,city_id,coach_name,mobile,work_img')->find();
        }
        //是否支持门店服务
        $order_info['is_store'] = !empty($order_info['store_info'])?1:0;

        $order_info['distance'] = distance_text($order_info['distance']);

        $order_info['canUseCoupon'] = $coupon_record_model->where('id','in',$canUseCoupon)->sum('num');

        $car_model = new CarPrice();

        $city_id = !empty($input['coach_id'])?$order_info['coach_info']['city_id']:0;
        //车费配置
        $order_info['car_config'] = $car_model->getCityConfig($this->_uniacid,$city_id,$start_time);

        $config_model = new Config();

        $order_info['trading_rules'] = $config_model->where(['uniacid'=>$this->_uniacid])->value('trading_rules');
        //加钟订单开始
        if(!empty($order_id)){

            $order_start_time = $this->model->addOrderTime($order_id);

            $order_end_time   = $this->model->getOrderEndTime($order_info['order_goods'],$order_start_time);

            $order_info['order_end_time']  = date('Y-m-d H:i:s',$order_end_time);

            $order_info['order_start_time']= date('Y-m-d H:i:s',$order_start_time);
        }

        $config_model = new Config();

        $config = $config_model->dataInfo(['uniacid'=>$this->_uniacid]);

        $time_long = array_sum(array_column($order_info['order_goods'],'total_time_long'));

        $order_info['time_long'] = $time_long;
        //默认时间
        $near_time  = $coach_model->getCoachEarliestTime($input['coach_id'],$config,1,$time_long);

        if(!empty($near_time)){

            $order_info['near_time']['text'] = nearTimeText($near_time);

            $order_info['near_time']['str']  = $near_time;
        }
        //用户余额
        $order_info['user_cash'] = $user_model->where(['id'=>$this->getUserId()])->sum('balance');

        $pay_config_model = new PayConfig();

        $pay_config = $pay_config_model->dataInfo(['uniacid'=>$this->_uniacid]);
        //支付宝支付
        $order_info['alipay_status'] = $pay_config['alipay_status'];
        //交易规则
        $order_info['trading_rules'] = $config['trading_rules'];

        return $this->success($order_info);
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-06-20 10:17
     * @功能说明:获取服务方式上门还是到店
     */
    public function getServiceType(){

        $input = $this->_param;
        //获取支持服务方式
        $arr = $this->model->storeOrDoor($this->_user['id'],$input['coach_id'],$this->_uniacid);

        return $this->success($arr);

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-10 00:40
     * @功能说明:可用的优惠券列表
     */
    public function couponList(){

        $input = $this->_param;

        $coupon_model = new Coupon();

        $level_model = new Level();
        //初始化会员权益
        $level_model->initMemberRights($this->getUserId());

        $coupon_record_model = new CouponRecord();

        $coupon_id = $coupon_model->canUseCoupon($this->getUserId(),$input['coach_id']);

        $data = $coupon_record_model->where('id','in',$coupon_id)->order('id desc')->paginate(10)->toArray();

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $v['start_time'] = date('Y.m.d H:i',$v['start_time']).' - '.date('Y.m.d H:i',$v['end_time']);

                $v['send_type']  = $coupon_model->where(['id'=>$v['coupon_id']])->value('send_type');
            }
        }

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-06 14:09
     * @功能说明:校验是否可以加单
     */
    public function checkAddOrder(){

        $input = $this->_input;

        $add_order = $this->model->where(['add_pid'=>$input['order_id']])->where('pay_type','in',[2,3,4,5,6,8])->field('id,start_time,end_time')->find();

        if(!empty($add_order)){

            $this->errorMsg('请先完成上一次加钟订单，才能继续加钟');

        }

        return $this->success(true);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-04-17 16:08
     * @功能说明:下单时候需要获取下单须知等内容
     */
    public function payOrderInfoConfig(){

        $data = getConfigSettingArr($this->_uniacid,['end_order_rules','order_rules_status','order_rules_show_type']);

        $config_model = new MassageConfig();

        $data['order_rules'] = $config_model->where(['uniacid'=>$this->_uniacid])->value('order_rules');

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-22 09:53
     * @功能说明:下单
     */
    //@ioncube.dk myk("sha256", "random5676u71113r400113331212") -> "c6669d2ef412584acc0f12ef175cec905def9e9c40e4a07ba864f617adfe3a11" RANDOM
    public function payOrder(){

        $input = $this->_input;

        $address_order_model = new OrderAddress();

        $address_model       = new Address();

        $coupon_record_model = new CouponRecord();

        $coach_model         = new Coach();

        $channel_model       = new UserChannel();

        $channel_record = $channel_model->getChannelId($this->_user['id']);

        $input['channel_id'] = !empty($channel_record)?$channel_record['channel_id']:0;

        $input['channel_qr_id'] = !empty($channel_record)?$channel_record['channel_qr_id']:0;

        $cap_info = $coach_model->dataInfo(['id'=>$input['coach_id']]);

        $cap_info['address'] = getCoachAddress($cap_info['lng'],$cap_info['lat'],$cap_info['uniacid'],$cap_info['id']);

        $order_id = !empty($input['order_id'])?$input['order_id']:0;

        $admin_id = 0;

        if(!empty($cap_info)&&$cap_info['is_work']==0){

            $this->errorMsg('该技师未上班');
        }

        if(!empty($cap_info)&&$cap_info['status']!=2){

            $this->errorMsg('该技师已下架');
        }

        $coupon_id = !empty($input['coupon_id'])?$input['coupon_id']:0;
        //加钟订单
        if(!empty($order_id)){

            $p_order = $this->model->dataInfo(['id'=>$order_id]);

            $can_add = $this->model->orderCanAdd($p_order);

            if($can_add==0){

                $this->errorMsg('该订单不能加钟');
            }

            $add_order = $this->model->where(['add_pid'=>$order_id,'pay_type'=>1])->select()->toArray();

            if(!empty($add_order)){

                foreach ($add_order as $value){

                    $this->model->cancelOrder($value);
                }
            }

            $address = $p_order['address_info'];

            $address['id'] = $address['address_id'];
            //加钟订单不计算车费
            $input['car_type'] = 0;
            //加钟
            $input['start_time'] = $this->model->addOrderTime($order_id);

            $store_id = $p_order['store_id'];

        }else{

            if(empty($input['is_store'])){

                $address = $address_model->dataInfo(['id'=>$input['address_id']]);

                if(empty($address)){

                    $this->errorMsg('请添加地址');
                }
            }else{

                $cap_info['store_id'] = $input['store_id'];
                //到店服务
                $address = $address_order_model->getDefaultSetting($this->_uniacid,$input['user_name'],$input['user_phone'],$cap_info['store_id']);

                if(empty($address)){

                    $this->errorMsg('该技师未绑定门店');
                }
            }

            $store_id = !empty($input['is_store'])&&!empty($cap_info['store_id'])?$cap_info['store_id']:0;
        }

        $service_type = !empty($store_id)?2:1;

        $order_info = $this->model->payOrderInfo($this->getUserId(),$input['coach_id'],$address['lat'],$address['lng'],$input['car_type'],$coupon_id,$order_id,$service_type,$input['start_time'],$input);

        $config_model = new Config();

        $config = $config_model->dataInfo(['uniacid'=>$this->_uniacid]);
        //储值折扣
        if($input['pay_model']==4&&empty($order_info['balance_discount_data']['is_balancediscount'])){

            $this->errorMsg('储值卡抵扣失败');
        }

        Db::startTrans();

        $key = $order_info['coach_id'].'order_key';

        incCache($key,1,$this->_uniacid,20);

        $key_value = getCache($key,$this->_uniacid);

        if($key_value!=1){

            decCache($key,1,$this->_uniacid);

            Db::rollback();

            $this->errorMsg('下单人数过多，请重试');
        }
        //检查技师时间(返回结束时间)
        $check = $this->model->checkTime($order_info,$input['start_time'],$order_id,0,$store_id);

        if(!empty($check['code'])){

            decCache($key,1,$this->_uniacid);

            Db::rollback();

            $this->errorMsg($check['msg']);
        }
        //默认微信
        $pay_model = isset($input['pay_model'])?$input['pay_model']:1;

        $order_insert = [

            'uniacid'    => $this->_uniacid,

            'over_time'  => time()+$config['over_time']*60,

            'order_code' => orderCode(),

            'user_id'    => $this->getUserId(),

            'pay_price'  => $order_info['pay_price'],

            'balance'    => $pay_model==2?$order_info['pay_price']:0,

            'init_service_price'=> $order_info['init_goods_price'],

            'service_price'=> $order_info['goods_price'],
            //物料费
            'init_material_price'=> $order_info['init_material_price'],

            'material_price'=> $order_info['material_price'],
            //包含退款的物料费后加字段
            'start_material_price'=> $order_info['material_price'],

            'true_service_price' => $order_info['goods_price'],

            'discount'    => $order_info['discount'],

            'car_price'   => $order_info['car_price'],

            'true_car_price' => $order_info['free_fare']!=2?$order_info['car_price']:0,

            'pay_type'    => 1,

            'coach_id'    => $order_info['coach_id'],

            'start_time'  => $input['start_time'],

            'end_time'    => $check['end_time'],

            'distance'    => $order_info['distance'],

            'time_long'   => $check['time_long'],

            'true_time_long' => $check['time_long'],
            //备注
            'text'        => !empty($input['text'])?$input['text']:'',

            'can_tx_time' => $config['can_tx_time'],

            'car_type'    => $input['car_type'],

            'channel_id'  => !empty($input['channel_id'])?$input['channel_id']:0,

            'channel_qr_id'=> !empty($input['channel_qr_id'])?$input['channel_qr_id']:0,

            'app_pay'     => $this->is_app,
            //技师出发地址
            'trip_start_address'=> !empty($cap_info['address'])?$cap_info['address']:'',
            //订单到达地址
            'trip_end_address' => $address['address'].' '.$address['address_info'],
            //加钟fu
            'add_pid' => $order_id,

            'is_add'  => !empty($order_id)?1:0,

            'pay_model' => $pay_model,

            'store_id'  => $store_id,

            'is_safe' => 0,

            'material_type' => getConfigSetting($this->_uniacid,'material_type'),

            'coupon_bear_type' => getConfigSetting($this->_uniacid,'coupon_bear_type'),
             //是否免车费
            'free_fare'     => $order_info['car_price']>0?$order_info['free_fare']:0,

            'version'       => 1,
        ];
        //下单
        $res = $this->model->dataAdd($order_insert);

        if($res!=1){

            decCache($key,1,$this->_uniacid);

            Db::rollback();

            $this->errorMsg('下单失败');
        }

        decCache($key,1,$this->_uniacid);

        $order_id = $this->model->getLastInsID();
        //添加订单附表
        $order_model = new OrderData();

        $order_info['text_img'] = !empty($input['text_img'])?implode(',',$input['text_img']):'';
        //使用优惠券
        $res = $coupon_record_model->couponUse($coupon_id,$order_id);

        if($res==false){

            $this->errorMsg('优惠券抵扣失败');
        }
        //添加下单地址
        $res = $address_order_model->orderAddressAdd($address,$order_id,$cap_info);

        if(!empty($res['code'])){

            Db::rollback();

            $this->errorMsg($res['msg']);
        }
        //添加到子订单
        $res = $this->order_goods_model->orderGoodsAdd($order_info['order_goods'],$order_id,$input['coach_id'],$this->getUserId());

        if(!empty($res['code'])){

            Db::rollback();

            $this->errorMsg($res['msg']);
        }

        $order_insert_data = $this->model->dataInfo(['id'=>$order_id]);
        //储值折扣
        $order_share_model = new OrderShare();

        if($pay_model==4){

            $order_share_model->orderShareAdd($order_id,$this->_uniacid,$order_info['balance_discount_data']['balance_discount_list'],$cap_info['admin_id']);
        }

        $order_model->orderDataAdd($order_id,$this->_uniacid,$pay_model,$order_info);
        //处理各类佣金情况
        $order_update = $this->model->getCashData($order_insert_data,1,$admin_id);

        if(!empty($order_update['code'])&&$order_update['code']==300){

            Db::rollback();

            $this->errorMsg('请添加技师等级');
        }

        if(!empty($order_update['order_data'])){

            $this->model->dataUpdate(['id'=>$order_id],$order_update['order_data']);

            $order_info['is_store_admin'] = isset($order_update['order_data']['is_store_admin'])?$order_update['order_data']['is_store_admin']:0;

            if($order_info['is_store_admin']==1){

                $order_model->dataUpdate(['order_id'=>$order_id],['is_store_admin'=>1]);
            }
        }

        if($pay_model==2&&$order_insert['pay_price']>0){

            $user_model  = new User();

            $user_balance= $user_model->where(['id'=>$this->getUserId()])->value('balance');

            if($user_balance<$order_insert['pay_price']){

                Db::rollback();

                $this->errorMsg('余额不足');
            }
        }

        Db::commit();
        //如果是0元
        if($order_insert['pay_price']<=0){

            $this->model->orderResult($order_insert['order_code'],$order_insert['order_code']);

            return $this->success(true);
        }
        //余额支付
        if(in_array($pay_model,[2,4])){

            $this->model->orderResult($order_insert['order_code'],$order_insert['order_code']);

            return $this->success(true);

        }elseif ($pay_model==3){

            $pay_model = new PayModel($this->payConfig());

            $jsApiParameters = $pay_model->aliPay($order_insert['order_code'],$order_insert['pay_price'],'MassageOrder',1,['openid'=>$this->getUserInfo()['openid'],'uniacid'=>$this->_uniacid,'type' => 'Massage' , 'out_trade_no' => $order_insert['order_code'],'order_id'=>(string)$order_id],$order_insert['pay_price']);

            $arr['pay_list']= $jsApiParameters;

            $arr['order_code']= $order_insert['order_code'];

            $arr['order_id']= $order_id;

        }else{
            //微信支付
            $pay_controller = new \app\shop\controller\IndexWxPay($this->app);
            //支付
            $jsApiParameters= $pay_controller->createWeixinPay($this->payConfig(),$this->getUserInfo()['openid'],$this->_uniacid,"anmo",['type' => 'Massage' , 'out_trade_no' => $order_insert['order_code'],'order_id'=>(string)$order_id],$order_insert['pay_price']);

            $arr['pay_list']= $jsApiParameters;

            $arr['order_id']= $order_id;
        }

        return $this->success($arr);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-25 15:59
     * @功能说明:重新支付
     */
    public function rePayOrder(){

        $input = $this->_input;

        $order_insert = $this->model->dataInfo(['id'=>$input['id']]);

        if($order_insert['pay_type']!=1){

            $this->errorMsg('订单状态错误，请刷新页面');
        }

        if($order_insert['app_pay']==1&&$this->is_app!=1){

            $this->errorMsg('请到APP完成支付');
        }

        if($order_insert['app_pay']==0&&$this->is_app!=0){

            $this->errorMsg('请到小程序完成支付');
        }

        if($order_insert['app_pay']==2&&$this->is_app!=2) {

            $this->errorMsg('请到公众号完成支付');
        }

        if($order_insert['pay_model']==2){

            $user_model = new User();

            $user_balance= $user_model->where(['id'=>$this->getUserId()])->value('balance');

            if($user_balance<$order_insert['pay_price']){

                $this->errorMsg('余额不足');
            }

            $this->model->orderResult($order_insert['order_code'],$order_insert['order_code']);

            return $this->success(true);

        }elseif ($order_insert['pay_model']==3){

            $pay_model = new PayModel($this->payConfig());

            $jsApiParameters = $pay_model->aliPay($order_insert['order_code'],$order_insert['pay_price'],'MassageOrder',1,['openid'=>$this->getUserInfo()['openid'],'uniacid'=>$this->_uniacid,'type' => 'Massage' , 'out_trade_no' => $order_insert['order_code'],'order_id'=>(string)$order_insert['id']]);

            $arr['pay_list']= $jsApiParameters;

            $arr['order_code']= $order_insert['order_code'];

        }elseif ($order_insert['pay_model']==4){

            $balance_discount_model = new UserCard();

            $res = $balance_discount_model->checkChangeUserCard($order_insert['id'],1);

            if($res==false){

                $this->errorMsg('储值卡金额不足');
            }

            $this->model->orderResult($order_insert['order_code'],$order_insert['order_code']);

            return $this->success(true);
        }else{
            //微信支付
            $pay_controller = new \app\shop\controller\IndexWxPay($this->app);
            //支付
            $jsApiParameters= $pay_controller->createWeixinPay($this->payConfig(),$this->getUserInfo()['openid'],$this->_uniacid,"anmo",['type' => 'Massage' , 'out_trade_no' => $order_insert['order_code'],'order_id'=>(string)$order_insert['id']],$order_insert['pay_price']);

            $arr['pay_list']= $jsApiParameters;
        }

        return $this->success($arr);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-25 16:38
     * @功能说明:取消订单
     */

    public function cancelOrder(){

        $input = $this->_input;

        $order_insert = $this->model->dataInfo(['id'=>$input['id']]);

        if($order_insert['pay_type']!=1){

            $this->errorMsg('订单状态错误，请刷新页面');
        }

       $res = $this->model->cancelOrder($order_insert);

       if(!empty($res['code'])){

           $this->errorMsg($res['msg']);
       }

       $log_model = new OrderLog();

       $log_model->addLog($input['id'],$this->_uniacid,-1,$order_insert['pay_type'],3,$this->_user['id']);

       return $this->success($res);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-26 11:39
     * @功能说明:申请退款
     */
    public function applyOrder(){

        $input = $this->_input;

        $order = $this->model->dataInfo(['id'=>$input['order_id']]);

        if(empty($order)){

            $this->errorMsg('订单未找到');
        }

        if($order['pay_type']<2){

            $this->errorMsg('订单状态错误，请刷新页面');
        }

        if(empty($input['list'])){

            $this->errorMsg('请选择商品');
        }

        if($order['pay_type']==7){

            $this->errorMsg('核销后不能退款');
        }
        //加钟订单
        if($order['is_add']==0){

            $where[] = ['add_pid','=',$order['id']];

            $where[] = ['pay_type','>',1];

            $add_order = $this->model->dataInfo($where);

            if(!empty($add_order)){

                $this->errorMsg('请先申请加钟订单退款');
            }

            $add_order = $this->model->where(['add_pid'=>$order['id'],'pay_type'=>1])->select()->toArray();

            if(!empty($add_order)){

                foreach ($add_order as $value){

                    $this->model->cancelOrder($value);
                }
            }
        }

        $key = 'order_refund_key_cant_refund'.$order['id'];

        incCache($key,1,$this->_uniacid);

        if(getCache($key,$this->_uniacid)==1){
            //申请退款
            $res = $this->refund_model->applyRefund($order,$input);

            if(!empty($res['code'])){

                decCache($key,1,$this->_uniacid);

                $this->errorMsg($res['msg']);
            }

            decCache($key,1,$this->_uniacid);
        }else{

            decCache($key,1,$this->_uniacid);

            $this->errorMsg('该订单正在发起退款，请稍后再试');
        }

        return $this->success(true);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-26 15:55
     * @功能说明:取消退款
     */
    public function cancelRefundOrder(){

        $input = $this->_input;

        $order = $this->refund_model->dataInfo(['id'=>$input['id']]);

        if($order['status']!=1){

            $this->errorMsg('订单已经审核');
        }

        if(!empty($order['out_refund_no'])){

            $this->errorMsg('订单已经退款');
        }

        Db::startTrans();

        $res = $this->refund_model->dataUpdate(['id'=>$input['id']],['status'=>-1,'cancel_time'=>time()]);

        if($res!=1){

            Db::rollback();

            $this->errorMsg('取消失败');
        }

        if(!empty($order['order_goods'])){

            $order_goods_model = new OrderGoods();

            foreach ($order['order_goods'] as $v){

                if(!empty($v['order_goods_id'])){

                    $num = $v['num'];

                    $res = $order_goods_model->where(['id'=>$v['order_goods_id']])->update(['can_refund_num'=>Db::Raw("can_refund_num+$num")]);

                    if($res!=1){

                        Db::rollback();

                        $this->errorMsg('取消失败');
                    }
                }
            }
        }

        $notice_model = new NoticeList();
        //增加后台提醒
        $notice_model->where(['type'=>2,'order_id'=>$input['id']])->delete();

        $log_model = new OrderLog();
        //退款订单的操作日志
        $log_model->addLog($input['id'],$order['uniacid'],-1,$order['status'],3,$order['user_id'],2);

        Db::commit();

        return $this->success($res);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-04-07 15:30
     * @功能说明:刷新订单二维码
     */
    public function refreshQr(){

        $input = $this->_input;

        $qr_insert = [

            'id' => $input['id']
        ];
        //获取二维码
        $qr = $this->model->orderQr($qr_insert,$this->_uniacid);

        if(!empty($qr)){

            $this->model->dataUpdate(['id'=>$input['id']],['qr'=>$qr]);

        }

        return $this->success($qr);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-13 00:18
     * @功能说明:评价标签
     */
    public function lableList(){

        $dis = [

            'uniacid' => $this->_uniacid,

            'status'  => 1
        ];

        $lable_model = new Lable();

        $res = $lable_model->where($dis)->order('top desc,id desc')->select()->toArray();

        return $this->success($res);

    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-12 14:01
     * @功能说明:添加评价
     */
    public function addComment(){

        $input = $this->_input;

        $order = $this->model->dataInfo(['id'=>$input['order_id']]);

        if($order['is_comment']==1){

            $this->errorMsg('你已经评价过了');
        }

        $setting = new WxSetting($this->_uniacid);


        $res = $setting->checkKeyWords($input['text']);

        if($res==false){

            return $this->error('评价含有敏感违禁词');
        }

        $insert = [

            'uniacid' => $this->_uniacid,

            'user_id' => $this->getUserId(),

            'order_id'=> $input['order_id'],

            'star'    => $input['star'],

            'text'    => $input['text'],

            'coach_id'=> $order['coach_id'],

            'admin_id'=> $order['admin_id'],

        ];

        Db::startTrans();

        $comment_model = new Comment();

        $comment_lable_model   = new CommentLable();

        $lable_model = new Lable();

        $res = $comment_model->dataAdd($insert);

        if($res==0){

            Db::rollback();

            $this->errorMsg('评价失败');
        }

        $comment_id = $comment_model->getLastInsID();

        if(!empty($input['lable'])){

            foreach ($input['lable'] as $value){

                $title = $lable_model->where(['id'=>$value])->value('title');

                $insert = [

                    'uniacid'    => $this->_uniacid,

                    'comment_id' => $comment_id,

                    'lable_id'   => $value,

                    'lable_title'=> $title,

                ];

                $comment_lable_model->dataAdd($insert);
            }
        }

        $comment_model->updateStar($order['coach_id']);

        $res = $this->model->dataUpdate(['id'=>$order['id']],['is_comment'=>1]);

        if($res==0){

            Db::rollback();

            $this->errorMsg('评价失败');
        }
        //添加服务评价
        if(!empty($input['service_star'])){

            $comment_goods_model = new CommentGoods();

            $comment_goods_model->commentAdd($input['service_star'],$this->_uniacid,$comment_id);
        }
        //增加技师信用分
        $credit_model = new CreditConfig();

        $type = $input['star']>3?5:8;

        $credit_model->creditRecordAdd($order['coach_id'],$type,$order['uniacid'],$order['id']);

        Db::commit();

        return $this->success($res);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-16 18:35
     * @功能说明:删除订单
     */
    public function delOrder(){

        $input = $this->_input;

        $order = $this->model->dataInfo(['id'=>$input['id']]);

        if(!in_array($order['pay_type'],[-1,7])){

            $this->errorMsg('只有取消或完成的订单才能删除');
        }

        $res = $this->model->dataUpdate(['id'=>$input['id']],['is_show'=>0]);

        $res = $this->model->dataUpdate(['add_pid'=>$input['id']],['is_show'=>0]);

        return $this->success($res);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-06-30 14:27
     * @功能说明:是否支持bus//408.36
     */
    public function getIsBus(){

        $input = $this->_param;

        $config_model = new Config();

        $config = $config_model->dataInfo(['uniacid'=>$this->_uniacid]);

        $is_bus = $config['is_bus'];

        if(!empty($input['start_time'])&&!empty($config['bus_start_time'])&&!empty($config['bus_end_time'])){

            $z_time = strtotime(date('Y-m-d',$input['start_time']));

            $start_time = strtotime($config['bus_start_time'])-strtotime(date('Y-m-d',time()))+$z_time;

            $end_time   = strtotime($config['bus_end_time'])-strtotime(date('Y-m-d',time()))+$z_time;

            if($input['start_time']<$start_time){

                $start_time -= 86400;

                $end_time   -= 86400;
            }

            $end_time = $end_time<$start_time?$end_time+86400:$end_time;

            if($input['start_time']<$start_time||$input['start_time']>$end_time){

                $is_bus = 0;
            }

        }

        return $this->success($is_bus);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-12-13 10:04
     * @功能说明:回去可以升级的服务
     */
    public function getUpOrderGoods(){

        $input = $this->_param;

        $order_goods = $this->order_goods_model->dataInfo(['id'=>$input['order_goods_id'],'status'=>1]);

        if(empty($order_goods)){

            $this->errorMsg('未找到升级项目');
        }

        $is_add   = $this->model->where(['id'=>$order_goods['order_id']])->value('is_add');

        $coach_id = $this->model->where(['id'=>$order_goods['order_id']])->value('coach_id');

        $store_id = $this->model->where(['id'=>$order_goods['order_id']])->value('store_id');
        //到店
        if(!empty($store_id)){

            $dis[] = ['a.is_store','=',1];

        }else{
            //上门
            $dis[] = ['a.is_door','=',1];
        }

        $dis[] = ['a.uniacid','=',$this->_uniacid];

        $dis[] = ['a.status','=',1];

        if(!empty($coach_id)){

            $dis[] = ['b.coach_id','=',$coach_id];
        }

        $dis[] = ['a.is_add','=',$is_add];

        $dis[] = ['a.id','<>',$order_goods['goods_id']];

        $service_model = new Service();

        $total_price = $order_goods['price'] + $order_goods['material_price'];

        $data = $service_model->upServiceCoachList($dis,$total_price);
        //会员价
        $data = giveMemberPrice($this->_uniacid,$data);
        //获取服务的会员信息
        $data = $service_model->giveListMemberInfo($data,$this->_uniacid,$this->getUserId());

        return $this->success($data);
    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-12-16 11:44
     * @功能说明:服务升级下单页面
     */
    public function upOrderInfo(){

        $input = $this->_input;

        $order = $this->model->dataInfo(['id'=>$input['order_id']]);

        if(empty($order)||!in_array($order['pay_type'],[2,3,4,5,6])){

            $this->errorMsg('订单已关闭');
        }
        //获取升级的服务价格
        $order_data = $this->order_goods_model->getUpGoodsData($input['order_goods'],$order,0,$input);

        if(!empty($order_data['code'])){

            $this->errorMsg($order_data['msg']);
        }

        $service_model = new Service();
        //获取服务的会员信息
        $order_data['order_goods'] = $service_model->giveListMemberInfo($order_data['order_goods'],$this->_uniacid,$this->_user['id'],2);

        $order_data['order_start_time'] = date('Y-m-d H:i:s',$order['start_time']);

        $order_data['pay_model'] = $order['pay_model'];

        $order_data['end_time'] = $order['start_time'] + $order_data['total_time_long']*60;

        $order_data['order_end_time'] = date('Y-m-d H:i:s',$order_data['end_time']);

        $coach_model = new Coach();

        if($order['pay_model']==2){

            $user_model = new User();

            $order_data['user_cash'] = $user_model->where(['id'=>$this->_user['id']])->sum('balance');
        }

        if(!empty($order['coach_id'])&&$order['coach_id']>0){

            $order_data['coach_info']['coach_name'] = $coach_model->where(['id'=>$order['coach_id']])->value('coach_name');

        }else{

            $change_log_model = new CoachChangeLog();

            $change_log = $change_log_model->dataInfo(['order_id'=>$input['order_id'],'status'=>1]);

            $order_data['coach_info']['coach_name'] = $coach_model->where(['id'=>$change_log['init_coach_id']])->value('coach_name');
        }

        return $this->success($order_data);
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-12-13 10:27
     * @功能说明:服务项目升级
     */
    public function upOrderGoods(){

        $input = $this->_input;

        $order_price_log = new OrderPrice();

        $price_log = $order_price_log->dataInfo(['top_order_id'=>$input['order_id']]);

        if(empty($price_log)){

            $this->error('改订单无法升级,请重新下单');
        }

        $order = $this->model->dataInfo(['id'=>$input['order_id']]);

        if(empty($order)||!in_array($order['pay_type'],[2,3,4,5,6])){

            $this->errorMsg('订单已关闭');
        }

        $refund_model = new RefundOrder();
        //判断有无申请中的退款订单
        $refund_order = $refund_model->where(['order_id' => $order['id']])->where('status','in',[1,4,5])->count();

        if ($refund_order>0) {

            $this->errorMsg('该订单正在申请退款，无法升级');

        }
        //获取升级的服务价格
        $order_data = $this->order_goods_model->getUpGoodsData($input['order_goods'],$order,1,$input);

        if(!empty($check['code'])){

            $this->errorMsg($check['msg']);
        }

        $start_time = $order['start_time'];

        $order_data['coach_id'] = $order['coach_id'];
        //校验时间
        $check = $this->model->checkTime($order_data,$start_time,$order['add_pid'],$order['id']);

        if(!empty($check['code'])){

            $this->errorMsg($check['msg']);
        }

        $up_order_model = new UpOrderList();
        //校验加钟订单时间 如果有加钟订单时间需要往后面推
        $check = $up_order_model->checkAddOrderTime($order_data['time_long']*60,$order['id']);

        if(!empty($check['code'])){

            $this->errorMsg($check['msg']);
        }

        $config_model = new Config();

        $config = $config_model->dataInfo(['uniacid'=>$this->_uniacid]);

        Db::startTrans();

        $insert = [

            'uniacid' => $this->_uniacid,

            'order_id'=> $order['id'],

            'user_id' => $order['user_id'],

            'coach_id' => $order['coach_id'],

            'order_code' => orderCode(),

            'order_price'=> $order_data['order_price'],

            'pay_price'  => $order_data['pay_price'],

            'material_price' => $order_data['material_price'],

            'init_material_price' => $order_data['init_material_price'],

            'service_price' => $order_data['service_price'],

            'true_service_price' => $order_data['true_service_price'],

            'surplus_price' => $order_data['pay_price'],

            'pay_model'  => $order['pay_model'],

            'over_time'  => time()+$config['over_time']*60,

            'balance'    => $order['pay_model']==2?$order_data['pay_price']:0,

            'time_long' => $order_data['time_long'],

            'discount'  => $order_data['discount'],

            'coupon_discount'  => $order_data['coupon_discount'],
            //会员折扣差价
            'member_discount_cash'  => $order_data['member_discount_cash'],
            //储值折扣差价
            'balance_discount_cash' => $order_data['balance_discount_cash'],

            'app_pay'     => $this->is_app,
        ];

        $up_goods_model = new UpOrderGoods();

        $up_order_model->dataAdd($insert);

        $id = $up_order_model->getLastInsID();
        //添加订单商品
        $res = $up_goods_model->orderGoodsAdd($order_data['order_goods'],$id);

        if(!empty($res['code'])){

            Db::rollback();

            $this->errorMsg($res['msg']);
        }

        if($order['pay_model']==4){

            $order_share_model = new OrderShare();

            $order_share_model->orderShareAdd($id,$this->_uniacid,$order_data['balance_discount_data']['balance_discount_list'],$order['admin_id'],$order['id'],2);
        }

        if($insert['pay_model']==2){

            $user_model = new User();

            $user_balance= $user_model->where(['id'=>$this->getUserId()])->value('balance');

            if($user_balance<$insert['pay_price']){

                $this->errorMsg('余额不足');
            }
        }

        Db::commit();
        //如果是0元
        if($insert['pay_price']<=0){

            $up_order_model->orderResult($insert['order_code'],$insert['order_code']);

            return $this->success(true);
        }
        //余额支付
        if(in_array($insert['pay_model'],[2,4])){

            $res = $up_order_model->orderResult($insert['order_code'],$insert['order_code']);

            return $this->success($res);

        }elseif ($insert['pay_model']==3){

            $pay_model = new PayModel($this->payConfig());

         //   $jsApiParameters = $pay_model->aliPay($order_insert['order_code'],$order_insert['pay_price'],'MassageOrder',1,['openid'=>$this->getUserInfo()['openid'],'uniacid'=>$this->_uniacid,'type' => 'Massage' , 'out_trade_no' => $order_insert['order_code'],'order_id'=>(string)$order_id],$order_insert['pay_price']);


            $jsApiParameters = $pay_model->aliPay($insert['order_code'],$insert['pay_price'],'MassageOrder',3,['openid'=>$this->getUserInfo()['openid'],'uniacid'=>$this->_uniacid,'type' => 'MassageUp' , 'out_trade_no' => $insert['order_code'],'order_id'=>(string)$id]);

            $arr['pay_list']  = $jsApiParameters;

            $arr['order_code']= $insert['order_code'];

            $arr['order_id']= $id;

        }else{
            //微信支付
            $pay_controller = new \app\shop\controller\IndexWxPay($this->app);
            //支付
            $jsApiParameters= $pay_controller->createWeixinPay($this->payConfig(),$this->getUserInfo()['openid'],$this->_uniacid,"anmo",['type' => 'MassageUp' , 'out_trade_no' => $insert['order_code'],'order_id'=>(string)$id],$insert['pay_price']);

            $arr['pay_list']= $jsApiParameters;

            $arr['order_id']= $id;
        }

        return $this->success($arr);
    }





    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-25 15:59
     * @功能说明:重新支付
     */
    public function rePayUpOrder(){

        $input = $this->_input;

        $up_order_model = new UpOrderList();

        $order_insert = $up_order_model->dataInfo(['id'=>$input['id']]);

        if($order_insert['pay_type']!=1){

            $this->errorMsg('订单状态错误，请刷新页面');
        }

//        if($order_insert['app_pay']==1&&$this->is_app!=1){
//
//            $this->errorMsg('请到APP完成支付');
//
//        }
//
//        if($order_insert['app_pay']==0&&$this->is_app!=0){
//
//            $this->errorMsg('请到小程序完成支付');
//        }
//
//        if($order_insert['app_pay']==2&&$this->is_app!=2) {
//
//            $this->errorMsg('请到公众号完成支付');
//
//        }

        if($order_insert['pay_model']==2){

            $user_model = new User();

            $user_balance= $user_model->where(['id'=>$this->getUserId()])->value('balance');

            if($user_balance<$order_insert['pay_price']){

                $this->errorMsg('余额不足');
            }

            $up_order_model->orderResult($order_insert['order_code'],$order_insert['order_code']);

            return $this->success(true);

        }elseif ($order_insert['pay_model']==3){

            $pay_model = new PayModel($this->payConfig());

            $jsApiParameters = $pay_model->aliPay($order_insert['order_code'],$order_insert['pay_price'],'MassageOrder',3,['uniacid'=>$this->_uniacid,'openid'=>$this->getUserInfo()['openid'],'type' => 'MassageUp' , 'out_trade_no' => $order_insert['order_code'],'order_id'=>(string)$input['id']]);

            $arr['pay_list']= $jsApiParameters;

            $arr['order_code']= $order_insert['order_code'];
        }else{
            //微信支付
            $pay_controller = new \app\shop\controller\IndexWxPay($this->app);
            //支付
            $jsApiParameters= $pay_controller->createWeixinPay($this->payConfig(),$this->getUserInfo()['openid'],$this->_uniacid,"anmo",['type' => 'MassageUp' , 'out_trade_no' => $order_insert['order_code'],'order_id'=>(string)$input['id']],$order_insert['pay_price']);

            $arr['pay_list']= $jsApiParameters;
        }

        return $this->success($arr);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-12-14 17:26
     * @功能说明:客户订单签字
     */
    public function userSignOrder(){

        $input = $this->_input;

        $refund_model = new RefundOrder();
        //判断有无申请中的退款订单
        $refund_order = $refund_model->where(['order_id' => $input['id']])->where('status','in',[1,4,5])->count();

        if ($refund_order>0) {

            Db::rollback();

            $this->errorMsg('该订单正在申请退款，请先联系平台处理再进行下一步');
        }

        $dis = [

            'uniacid' => $this->_uniacid,

            'order_id'=> $input['id']
        ];

        $order_model = new OrderData();

        $order_model->dataInfo($dis);

        $update = [

            'sign_time' => time(),

            'sign_img'  => $input['sign_img']
        ];

        $res = $order_model->dataUpdate($dis,$update);

        return $this->success($res);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-12-15 11:34
     * @功能说明:订单升级记录
     */
    public function orderUpRecord(){

        $input = $this->_param;

        $order_model = new UpOrderList();

        $data = $order_model->orderUpRecord($input['order_id']);

        return $this->success($data);

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-12-16 11:44
     * @功能说明:服务升级下单页面
     */
    public function adapayOrderInfo(){

        $input = $this->_param;

        $callback_model = new \app\adapay\model\Callback();

        $pay_record_model = new PayRecord();

        $callback = $callback_model->dataInfo(['party_order_id'=>$input['out_trade_no']]);

        $order = [];

        if(!empty($callback)){

            $find = $pay_record_model->dataInfo(['adapay_id'=>$callback['adapay_id'],'status'=>1]);

            if(!empty($find)){

                if($find['type']=='Massage'){

                    $order = $this->model->dataInfo(['order_code'=>$find['order_code']]);

                }elseif ($find['type']=='MassageUp'){

                    $order_model = new UpOrderList();

                    $order = $order_model->dataInfo(['order_code'=>$find['order_code']]);

                    if(!empty($order['order_id'])){

                        $order['order_code'] = $this->model->where(['id'=>$order['order_id']])->value('order_code');
                    }

                }elseif ($find['type']=='Balance'){

                    $order_model = new \app\massage\model\BalanceOrder();

                    $order = $order_model->dataInfo(['order_code'=>$find['order_code']]);

                }elseif ($find['type']=='ResellerPay'){

                    $order_model = new \app\payreseller\model\Order();

                    $order = $order_model->dataInfo(['order_code'=>$find['order_code']]);

                }elseif ($find['type']=='AgentRecharge'){

                    $order_model = new \app\mobilenode\model\RechargeList();

                    $order = $order_model->dataInfo(['order_code'=>$find['order_code']]);

                    $order['pay_price'] = $order['cash'];

                }elseif ($find['type']=='Balancediscount'){

                    $order_model = new \app\balancediscount\model\OrderList();

                    $order = $order_model->dataInfo(['order_code'=>$find['order_code']]);

                }elseif ($find['type']=='Memberdiscount'){

                    $order_model = new OrderList();

                    $order = $order_model->dataInfo(['order_code'=>$find['order_code']]);
                } elseif ($find['type'] == 'PartnerOrder') {

                    $order = PartnerOrder::getInfo(['order_code' => $find['order_code']]);
                } elseif ($find['type'] == 'PartnerOrderJoin') {

                    $order = PartnerOrderJoin::getInfo(['order_code' => $find['order_code']]);
                }

                $order['order_type'] = $find['type'];
            }
        }

        return $this->success($order);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-11-24 16:01
     * @功能说明:客户结束订单
     */
    public function endOrder(){

        $input = $this->_input;

        $refund_model = new RefundOrder();

        $log_model = new OrderLog();

        $order = $this->model->dataInfo(['id'=>$input['id']]);

        if ($order['pay_type'] != 6) {

            Db::rollback();

            $this->errorMsg('订单状态错误，请刷新页面');
        }
        //判断有无申请中的退款订单
        $refund_order = $refund_model->where(['order_id' => $input['id']])->where('status','in',[1,4,5])->count();

        if ($refund_order>0) {

            Db::rollback();

            $this->errorMsg('该订单正在申请退款，请先联系平台处理再进行下一步');
        }

        Db::startTrans();

        if($order['is_add']==0){

            $add_order = $this->model->where(['add_pid'=>$order['id']])->where('pay_type','in',[1,2])->find();

            if(!empty($add_order)){

                Db::rollback();

                $this->errorMsg('该订单还有待接单或待付款加钟订单，请先处理');
            }

            $add_order = $this->model->where(['add_pid'=>$order['id']])->where('pay_type','in',[3,4,5,6])->select()->toArray();

            if(!empty($add_order)){

                foreach ($add_order as $value){
                    //判断有无申请中的退款订单
                    $refund_order = $refund_model->where(['order_id' => $value['id']])->where('status','in',[1,4,5])->count();

                    if ($refund_order>0) {

                        Db::rollback();

                        $this->errorMsg('该订单加钟订单正在申请退款，请先联系平台处理再进行下一步');
                    }

                    $res = $this->model->hxOrder($value, -1);

                    if(!empty($res['code'])){

                        Db::rollback();

                        $this->errorMsg($res['msg']);

                    }

                    $log_model->addLog($value['id'],$this->_uniacid,7,$value['pay_type'],3,$this->_user['id']);
                }
            }
        }

        $res = $this->model->hxOrder($order, -1);

        if(!empty($res['code'])){

            Db::rollback();

            $this->errorMsg($res['msg']);

        }

        $log_model->addLog($order['id'],$this->_uniacid,7,$order['pay_type'],3,$this->_user['id']);

        Db::commit();

        return $this->success(true);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-04-18 10:48
     * @功能说明:订单是否可以结束
     */
    public function canEndOrder(){

        $input = $this->_param;

        $refund_model = new RefundOrder();

        $order = $this->model->dataInfo(['id'=>$input['id']]);

        if ($order['pay_type'] != 6) {

            $this->errorMsg('订单状态错误，请刷新页面');
        }

        if($order['is_add']==0){

            $add_order = $this->model->where(['add_pid'=>$order['id']])->where('pay_type','in',[1,2])->find();

            if(!empty($add_order)){

                $this->errorMsg('该订单还有待接单或待付款加钟订单，请先处理');
            }

            $add_order = $this->model->where(['add_pid'=>$order['id']])->where('pay_type','in',[3,4,5,6])->select()->toArray();

            if(!empty($add_order)){

                foreach ($add_order as $value){
                    //判断有无申请中的退款订单
                    $refund_order = $refund_model->where(['order_id' => $value['id']])->where('status','in',[1,4,5])->count();

                    if ($refund_order>0) {

                        $this->errorMsg('该订单加钟订单正在申请退款，请先联系平台处理再进行下一步');
                    }
                }
            }
        }

        return $this->success(true);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-05-07 15:51
     * @功能说明:增加未付款记录
     */
    public function noPayRecordAdd(){

        $input = $this->_input;

        $insert = [

            'uniacid' => $this->_uniacid,

            'user_id' => $this->_user['id'],

            'coach_id'=> $input['coach_id']
        ];

        $no_pay_model = new NoPayRecord();

        $no_pay_goods_model = new NoPayRecordGoods();

        $no_pay_model->dataAdd($insert);

        $record_id = $no_pay_model->getLastInsID();

        foreach ($input['order_goods'] as $k=>$v){

            $goods[$k] = [

                'uniacid' => $this->_uniacid,

                'goods_id'=> $v['goods_id'],

                'goods_name'=> $v['goods_name'],

                'goods_cover'=> $v['goods_cover'],

                'goods_price'=> $v['goods_price'],

                'record_id'=> $record_id,
            ];
        }
        $no_pay_goods_model->saveAll($goods);

        publisher(json_encode(['action' => 'auto_phone', 'uniacid' => $this->_uniacid], true));

        return $this->success(true);
    }


    /**
     * @author chenniang
     * @DataTime: 2024-09-11 16:32
     * @功能说明:判断订单是否可以退款
     */
    public function canRefundOrder(){

        $input= $this->_param;

        $data = $this->model->dataInfo(['id'=>$input['id']]);

        $fee_model = new EmptyTicketFeeConfig();

        $max_minute = $fee_model->where(['uniacid'=>$data['uniacid']])->max('minute');

        $after_service_can_refund = getConfigSetting($this->_uniacid,'after_service_can_refund');

        $res  = $this->model->canRefundOrder($data,$after_service_can_refund,$max_minute);

        return $this->success($res);
    }


    /**
     * @author chenniang
     * @DataTime: 2024-09-19 14:14
     * @功能说明:用户的储值卡
     */
    public function balanceDiscountList(){

        $card_model = new UserCard();

        $data = $card_model->getUserCardList($this->_user['id']);

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $v['over_time']= date('Y.m.d H:i:s',$v['over_time']);
            }
        }

        return $this->success($data);
    }






}
