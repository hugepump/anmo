<?php
namespace app\massage\controller;
use app\abnormalorder\model\OrderList;
use app\AdminRest;
use app\balancediscount\model\OrderShare;
use app\massage\model\ChannelList;
use app\massage\model\ChannelQr;
use app\massage\model\Coach;
use app\massage\model\CoachChangeLog;
use app\massage\model\Comment;
use app\massage\model\CommentLable;
use app\massage\model\Commission;
use app\massage\model\EmptyTicketFeeConfig;
use app\massage\model\HelpConfig;
use app\massage\model\Lable;
use app\massage\model\NoPayRecord;
use app\massage\model\NoPayRecordGoods;
use app\massage\model\NoticeList;
use app\massage\model\Order;
use app\massage\model\OrderAddress;
use app\massage\model\OrderData;
use app\massage\model\OrderGoods;
use app\massage\model\OrderLog;
use app\massage\model\OrderPrice;
use app\massage\model\OrderProcess;
use app\massage\model\Police;
use app\massage\model\RefundOrder;
use app\massage\model\RefundOrderGoods;
use app\massage\model\SendMsgConfig;
use app\massage\model\StoreCoach;
use app\massage\model\StoreList;
use app\massage\model\Trajectory;
use app\massage\model\UpdateAddressRecord;
use app\massage\model\UpOrderList;
use app\massage\model\User;
use app\massage\test\Asd;
use app\node\model\RoleAdmin;
use app\orderradar\info\PermissionOrderradar;
use app\recording\model\Recording;
use app\test\server\a;
use longbingcore\heepay\WeixinPay;
use longbingcore\wxcore\WxSetting;
use think\App;
use think\facade\Db;
use think\facade\Queue;


class AdminOrder extends AdminRest
{


    protected $model;

    protected $refund_order_model;

    protected $comment_model;

    protected $process_model;

    protected $b;

    public function __construct(App $app) {

        parent::__construct($app);

        $this->b= $app;

        $this->model = new Order();

        $this->refund_order_model = new RefundOrder();

        $this->comment_model = new Comment();

        $this->process_model = new OrderProcess();

    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-15 14:43
     * @功能说明:列表
     */

    public function orderList(){

        $input = $this->_param;

        $dis[] = ['uniacid','=',$this->_uniacid];
        //时间搜素
        if(!empty($input['start_time'])&&!empty($input['end_time'])){

            $start_time = $input['start_time'];

            $end_time = $input['end_time'];

            $dis[] = ['create_time','between',"$start_time,$end_time"];
        }
        //商品名字搜索
        if(!empty($input['goods_name'])){

            $order_goods_dis[] = ['goods_name','like','%'.$input['goods_name'].'%'];

            $order_goods_dis[] = ['status','=',1];

            $order_goods_model = new OrderGoods();

            $order_id = $order_goods_model->where($order_goods_dis)->column('order_id');

            $dis[] = ['id','in',$order_id];
        }
        //手机号搜索
        if(!empty($input['mobile'])){

            $order_address_model = new OrderAddress();

            $order_address_dis[] = ['mobile','like','%'.$input['mobile'].'%'];

            $order_id = $order_address_model->where($order_address_dis)->column('order_id');

            $dis[] = ['id','in',$order_id];
        }

        if(!empty($input['pay_type'])){
            //订单状态搜索
            $dis[] = ['pay_type','=',$input['pay_type']];

        }else{
            //除开待转单
            $dis[] = ['pay_type','<>',8];
        }
        //是否是渠道商
        if(!empty($input['is_channel'])){

            $dis[] = ['pay_type','>',1];

            $dis[] = ['channel_id','<>',0];
        }

        if(!empty($input['coach_id'])){

            $dis[] = ['coach_id','=',$input['coach_id']];
        }

        $map = [];
        //店铺名字搜索
        if(!empty($input['coach_name'])){

            $order_id = $this->model->getCoachOrderId($input['coach_name']);

            $dis[] = ['id','in',$order_id];
        }
        //订单号搜索
        if(!empty($input['order_code'])){

            $dis[] = ['order_code','like','%'.$input['order_code'].'%'];
        }

        if(!empty($input['transaction_id'])){

            $dis[] = ['transaction_id','like','%'.$input['transaction_id'].'%'];
        }
        //代理商端查询
        if($this->_user['is_admin']==0){

            $dis[] = ['admin_id','in',$this->admin_arr];
        }
        //代理商查询
        if(!empty($input['admin_id'])){

            $dis[] = ['admin_id','=',$input['admin_id']];
        }
        //合伙人
        if(!empty($input['partner_id'])){

            $dis[] = ['partner_id','=',$input['partner_id']];
        }
        //渠道搜索
        if(!empty($input['channel_cate_id'])){

            $channel_id = $this->model->getOrderIdByChannelCate($input['channel_cate_id']);

            $dis[] = ['channel_id','in',$channel_id];
        }
        //渠道商搜索
        if(!empty($input['channel_name'])){

            $channel_id = $this->model->getOrderIdByChannelName($input['channel_name']);

            $dis[] = ['channel_id','in',$channel_id];
        }
        //渠道码
        if(!empty($input['channel_qr_name'])){

            $qr_model = new ChannelQr();

            $channel_qr_id = $qr_model->getQrID($input['channel_qr_name'],$this->_uniacid);

            $dis[] = ['channel_qr_id ','in',$channel_qr_id];
        }
        //是否是加钟
        if(isset($input['is_add'])){

            $dis[] = ['is_add','=',$input['is_add']];
        }
        //订单类型 门店|上门服务
        if(!empty($input['is_store'])){

            if($input['is_store']==1){

                $dis[] = ['store_id','>',0];
            }else{

                $dis[] = ['store_id','=',0];
            }
        }
        //是否是线下技师
        if(!empty($input['is_coach'])){

            if($input['is_coach']==2){

                $dis[] = ['coach_id','=',0];
            }else{

                $dis[] = ['coach_id','>',0];
            }
        }

        $data = $this->model->adminDataListV2($dis,$input['limit'],$map,$this->_user['phone_encryption']);

        return $this->success($data);
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-15 14:43
     * @功能说明:订单列表统计
     */
    public function orderTotalData(){
        //超时自动取消订单
        $this->model->autoCancelOrder($this->_uniacid);

        $this->model->initOrderData($this->_uniacid);

        $input = $this->_param;

        $dis[] = ['uniacid','=',$this->_uniacid];
        //时间搜素
        if(!empty($input['start_time'])&&!empty($input['end_time'])){

            $start_time = $input['start_time'];

            $end_time = $input['end_time'];

            $dis[] = ['create_time','between',"$start_time,$end_time"];
        }
        //商品名字搜索
        if(!empty($input['goods_name'])){

            $order_goods_dis[] = ['goods_name','like','%'.$input['goods_name'].'%'];

            $order_goods_dis[] = ['status','=',1];

            $order_goods_model = new OrderGoods();

            $order_id = $order_goods_model->where($order_goods_dis)->column('order_id');

            $dis[] = ['id','in',$order_id];
        }
        //手机号搜索
        if(!empty($input['mobile'])){

            $order_address_model = new OrderAddress();

            $order_address_dis[] = ['mobile','like','%'.$input['mobile'].'%'];

            $order_id = $order_address_model->where($order_address_dis)->column('order_id');

            $dis[] = ['id','in',$order_id];
        }

        if(!empty($input['pay_type'])){
            //订单状态搜索
            $dis[] = ['pay_type','=',$input['pay_type']];

        }else{
            //除开待转单
            $dis[] = ['pay_type','<>',8];
        }
        //是否是渠道商
        if(!empty($input['is_channel'])){

            $dis[] = ['pay_type','>',1];

            $dis[] = ['channel_id','<>',0];
        }

        if(!empty($input['coach_id'])){

            $dis[] = ['coach_id','=',$input['coach_id']];
        }

        $map = [];
        //店铺名字搜索
        if(!empty($input['coach_name'])){

            $order_id = $this->model->getCoachOrderId($input['coach_name']);

            $dis[] = ['id','in',$order_id];
        }
        //订单号搜索
        if(!empty($input['order_code'])){

            $dis[] = ['order_code','like','%'.$input['order_code'].'%'];
        }

        if(!empty($input['transaction_id'])){

            $dis[] = ['transaction_id','like','%'.$input['transaction_id'].'%'];
        }
        //代理商端查询
        if($this->_user['is_admin']==0){

            $dis[] = ['admin_id','in',$this->admin_arr];
        }
        //代理商查询
        if(!empty($input['admin_id'])){

            $dis[] = ['admin_id','=',$input['admin_id']];
        }
        //合伙人
        if(!empty($input['partner_id'])){

            $dis[] = ['partner_id','=',$input['partner_id']];
        }
        //渠道搜索
        if(!empty($input['channel_cate_id'])){

            $channel_id = $this->model->getOrderIdByChannelCate($input['channel_cate_id']);

            $dis[] = ['channel_id','in',$channel_id];
        }
        //渠道商搜索
        if(!empty($input['channel_name'])){

            $channel_id = $this->model->getOrderIdByChannelName($input['channel_name']);

            $dis[] = ['channel_id','in',$channel_id];
        }
        //渠道码
        if(!empty($input['channel_qr_name'])){

            $qr_model = new ChannelQr();

            $channel_qr_id = $qr_model->getQrID($input['channel_qr_name'],$this->_uniacid);

            $dis[] = ['channel_qr_id ','in',$channel_qr_id];
        }
        //是否是加钟
        if(isset($input['is_add'])){

            $dis[] = ['is_add','=',$input['is_add']];
        }
        //订单类型 门店|上门服务
        if(!empty($input['is_store'])){

            if($input['is_store']==1){

                $dis[] = ['store_id','>',0];
            }else{

                $dis[] = ['store_id','=',0];
            }
        }
        //是否是线下技师
        if(!empty($input['is_coach'])){

            if($input['is_coach']==2){

                $dis[] = ['coach_id','=',0];
            }else{

                $dis[] = ['coach_id','>',0];
            }
        }
        //订单金额
        $data  = $this->model->adminOrderPriceV2($dis,$map);

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

            'id' => $input['id']
        ];

        $data = $this->model->dataInfo($dis);
        //下单次数
        $data['pay_order_times'] = $this->model->useOrderTimes($data['user_id'],$data['create_time']);

        $data['time_long'] = $data['true_time_long'];

        $data['distance'] = distance_text($data['distance']);
        //加钟订单
        if($data['is_add']==0){

            $data['add_order_id'] = $this->model->where(['add_pid'=>$data['id']])->where('pay_type','>',1)->field('id,order_code')->select()->toArray();

        }else{

            $data['add_pid'] = $this->model->where(['id'=>$data['add_pid']])->field('id,order_code')->find();
        }

        $order_model = new OrderData();
        //订单附表
        $order_data = $order_model->dataInfo(['order_id'=>$input['id'],'uniacid'=>$this->_uniacid]);

        $data = array_merge($order_data,$data);
        //订单转派记录
        $change_log_model = new CoachChangeLog();

        $data['dispatch_record'] = $change_log_model->orderChangeLog($input['id']);

        $admin_model = new \app\massage\model\Admin();

        $data['admin_name'] = $admin_model->where(['id'=>$data['admin_id']])->value('agent_name');
        //门店订单
        if(!empty($data['store_id'])){

            $store_model = new StoreList();

            $data['store_info'] = $store_model->where(['id'=>$data['store_id']])->field('title,cover,address,lng,lat,phone')->find();
        }
        //服务录音
        $recording_model = new Recording();

        $data['service_recording'] = $recording_model->where(['order_id'=>$data['id']])->field('link,create_time')->select()->toArray();

        $channel_list = new ChannelList();

        $channel_qr_model = new ChannelQr();
        //渠道商名称
        $data['channel_name'] = $channel_list->where(['id'=>$data['channel_id']])->value('user_name');
        //渠道码名称
        $data['channel_qr_name'] = $channel_qr_model->where(['id'=>$data['channel_qr_id']])->value('title');

        $comm_model = new Commission();

        $car_admin = $comm_model->where(['order_id'=>$data['id'],'type'=>13])->where('status','>',-1)->find();
        //车费是否给代理商
        $data['car_admin'] = !empty($car_admin)?1:0;

        $order_goods_model = new OrderGoods();

        if(!empty($data['order_goods'])){

            foreach ($data['order_goods'] as &$vv){

                $refund_data = $order_goods_model->getRefundCash($vv['id']);

                $vv['refund_goods_price'] = round($refund_data['total_service_price'],2);

                $vv['refund_material_price'] = round($refund_data['total_material_price'],2);
            }
        }
        if(!empty($data['add_order_id'])){

            foreach ($data['add_order_id'] as &$add_order){

                if(!empty($add_order['order_goods'])){

                    foreach ($add_order['order_goods'] as &$vs){

                        $refund_data = $order_goods_model->getRefundCash($vs['id']);

                        $vs['refund_goods_price'] = round($refund_data['total_service_price'],2);

                        $vs['refund_material_price'] = round($refund_data['total_material_price'],2);
                    }
                }
            }
        }
        if($data['is_add']==0){

            $data['add_service'] = $order_goods_model->getAddOrderGoods($data['id']);
        }

        $user_model = new User();
        //下单人相关信息
        $data['user_info'] = $user_model->where(['id'=>$data['user_id']])->field('nickName,phone,avatarUrl,id')->find();
        //退款金额
        $data['refund_price'] = $this->refund_order_model->where(['status'=>2,'order_id'=>$data['id']])->sum('refund_price');
        //手机号加密
        if($this->_user['phone_encryption']==0){

            $data['address_info']['mobile'] = substr_replace($data['address_info']['mobile'], "****", 2,4);

            $data['user_info']['phone'] = substr_replace($data['user_info']['phone'], "****", 2,4);
        }

        $log_model = new OrderLog();
        //那些流程是手动打卡
        $data['map_type'] = $log_model->where(['order_id'=>$data['id'],'map_type'=>1])->column('pay_type');
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
     * @DataTime: 2021-03-19 17:50
     * @功能说明:退款订单详情
     */
    public function refundOrderInfo(){

        $input = $this->_param;

        $dis = [

            'id' => $input['id']
        ];

        $data = $this->refund_order_model->dataInfo($dis);

        $data['pay_order_code'] = $this->model->where(['id'=>$data['order_id']])->value('order_code');

        $data['create_time'] = date('Y-m-d H:i:s',$data['create_time']);

        $data['refund_time'] = !empty($data['refund_time'])?date('Y-m-d H:i:s',$data['refund_time']):"";

        $pay_order = $this->model->dataInfo(['id'=>$data['order_id']]);

        $data['car_type'] = $pay_order['car_type'];

        $data['pay_type'] = $pay_order['pay_type'];

        $data['free_fare']= $pay_order['free_fare'];

        $data['distance'] = distance_text($pay_order['distance']);

        $data['pay_car_price'] = $pay_order['car_price'];

        $admin_model = new \app\massage\model\Admin();

        $data['admin_name'] = $admin_model->where(['id'=>$data['admin_id']])->value('agent_name');

        $data['store_id']   = $pay_order['store_id'];
        //门店订单
        if(!empty($pay_order['store_id'])){

            $store_model = new StoreList();

            $data['store_info'] = $store_model->where(['id'=>$pay_order['store_id']])->field('title,cover,address,lng,lat,phone')->find();
        }
        //手机号加密
        if($this->_user['phone_encryption']==0){

            $data['address_info']['mobile'] = substr_replace($data['address_info']['mobile'], "****", 2,4);
        }
        //审核人员
        $data['check_user_name'] = $this->refund_order_model->checkUserName($data);

        return $this->success($data);

    }
    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-17 17:44
     * @功能说明:退款订单列表
     */
    public function refundOrderList(){

        $input = $this->_param;

        $dis[] = ['a.uniacid','=',$this->_uniacid];

       // $dis[] = ['a.type','=',1];
        //商品名字搜索
        if(!empty($input['goods_name'])){

            $dis[] = ['c.goods_name','like','%'.$input['goods_name'].'%'];
        }
        //订单状态搜索
        if(!empty($input['status'])){

            $dis[] = ['a.status','=',$input['status']];

        }else{

            $dis[] = ['a.status','>',-2];
        }

        $where = [];

        if(!empty($input['order_code'])){

            $where[] = ['a.order_code','like','%'.$input['order_code'].'%'];

            $where[] = ['d.order_code','like','%'.$input['order_code'].'%'];
        }

        if($this->_user['is_admin']==0){

            $dis[] = ['a.admin_id','in',$this->admin_arr];
        }

        if(!empty($input['admin_id'])){

            $dis[] = ['a.admin_id','=',$input['admin_id']];
        }

        if(!empty($input['partner_id'])){

            $dis[] = ['a.partner_id','=',$input['partner_id']];
        }

        $is_add = !empty($input['is_add'])?$input['is_add']:0;

        $dis[] = ['a.is_add','=',$is_add];

        $data = $this->refund_order_model->adminDataList($dis,$input['limit'],$where);

        $queue_model = new \app\massage\model\Queue();

        $queue_model->queueDo(1);

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-18 09:21
     * @功能说明:拒绝退款
     */
    public function noPassRefund(){

        $input = $this->_input;

        $res = $this->refund_order_model->noPassRefund($input['id'],$this->_user['id']);

        if(!empty($res['code'])){

            $this->errorMsg($res['msg']);
        }

        return $this->success($res);
    }


    /**\
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-18 09:28
     * @功能说明:同意退款
     */
    public function passRefund(){

        $input = $this->_input;

        $order = $this->refund_order_model->dataInfo(['id'=>$input['id']]);

        $is_app= $this->model->where(['id'=>$order['order_id']])->value('app_pay');

        $this->refund_order_model->dataUpdate(['id'=>$input['id']],['version'=>1]);

        $res   = $this->refund_order_model->passOrder($input['id'],$input['price'],$this->payConfig($is_app),$this->_user['id'],$input['text']);

        if(!empty($res['code'])){

            $this->errorMsg($res['msg']);
        }

        return $this->success($res);
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-18 09:28
     * @功能说明:同意退款
     */
    public function passRefundV2(){

        $input = $this->_input;

        $order = $this->refund_order_model->dataInfo(['id'=>$input['order_id']]);

        $pay_order = $this->model->where(['id'=>$order['order_id']])->field('app_pay,pay_type,is_add')->find()->toArray();

        $is_app    = $pay_order['app_pay'];

        if(empty($input['list'])){

            $this->errorMsg('请选择商品');
        }

        $refund_goods_model = new RefundOrderGoods();

        $goods_price = $material_price = 0;

        foreach ($input['list'] as $value){

            $num = $refund_goods_model->where(['id'=>$value['id']])->sum('num');

            $goods_price    += $value['goods_price']*$num;

            $material_price += $value['material_price']*$num;
        }

        $input['refund_empty_cash'] = isset($input['refund_empty_cash'])?$input['refund_empty_cash']:0;

        $input['apply_empty_cash']  = isset($input['apply_empty_cash'])?$input['apply_empty_cash']:0;
        //空单费|退款手续费
        $input = $this->refund_order_model->emptyCashSet($input,$goods_price,$material_price,$pay_order['pay_type'],$pay_order['is_add']);

        if(!empty($input['code'])&&$input['code']==500){

            $this->errorMsg($input['msg']);
        }

        $car_price = !empty($input['car_price'])?$input['car_price']:0;

        if(round($input['price'],2)!=round($goods_price+$material_price+$car_price-$input['refund_empty_cash']-$input['refund_comm_cash'],2)){

            $this->errorMsg('请检查金额');
        }

        $goods_price    = $goods_price-$input['comm_service_cash']-$input['empty_service_cash'];

        $material_price = $material_price-$input['comm_material_cash']-$input['empty_material_cash'];

        $this->refund_order_model->dataUpdate(['id'=>$input['order_id']],['version'=>2]);

        foreach ($input['list'] as $value){

            $update = [

                'goods_price'        => $value['goods_price'],

                'material_price'     => $value['material_price'],

                'comm_service_cash'  => $value['comm_service_cash'],

                'comm_material_cash' => $value['comm_material_cash'],

                'refund_comm_cash'   => $value['refund_comm_cash'],

                'empty_service_cash' => $value['empty_service_cash'],

                'empty_material_cash'=> $value['empty_material_cash'],

                'refund_empty_cash'  => $value['refund_empty_cash'],

                'apply_empty_service_cash' => $value['apply_empty_service_cash'],

                'apply_empty_material_cash'=> $value['apply_empty_material_cash'],

                'apply_empty_cash'  => $value['apply_empty_cash'],
            ];

            $refund_goods_model->dataUpdate(['id'=>$value['id']],$update);
        }

        $res = $this->refund_order_model->passOrder($input['order_id'],$input['price'],$this->payConfig($is_app),$this->_user['id'],$input['text']);

        if(!empty($res['code'])){

            $this->errorMsg($res['msg']);
        }

        $res = $this->refund_order_model->dataUpdate(['id'=>$input['order_id']],['refund_car_price'=>$input['car_price'],'refund_service_price'=>$goods_price,'refund_material_price'=>$material_price]);

        return $this->success($res);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-05 23:16
     * @功能说明:评价列表
     */
    public function commentList(){

        $input = $this->_param;

        $dis[] = ['a.uniacid','=',$this->_uniacid];

        $dis[] = ['a.status','>',-1];

        if(!empty($input['star'])){

            $dis[] = ['a.star','=',$input['star']];
        }

        if(!empty($input['coach_name'])){

            $dis[] = ['d.coach_name','like','%'.$input['coach_name'].'%'];
        }

        if(!empty($input['goods_name'])){

            $dis[] = ['c.goods_name','like','%'.$input['goods_name'].'%'];

        }

        if($this->_user['is_admin']==0){

            $dis[] = ['a.admin_id','in',$this->admin_arr];
        }

        if (!empty($input['order_id'])){

            $dis[] = ['a.order_id','=',$input['order_id']];
        }

        $data = $this->comment_model->adminDataList($dis,$input['limit']);

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-05 23:31
     * @功能说明:编辑评价
     */
    public function commentUpdate(){

        $input = $this->_input;

        $dis = [

            'id' => $input['id']
        ];

        $data = $this->comment_model->dataUpdate($dis,$input);
        //删除评价需要重新计算分数
        if(!empty($input['status'])&&$input['status']==-1){

            $info = $this->comment_model->dataInfo($dis);

            $this->comment_model->updateStar($info['coach_id']);
        }

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-04 18:53
     * @功能说明:评价标签列表
     */
    public function commentLableList(){

        $input = $this->_param;

        $lable_model = new Lable();

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['status','>',-1];

        $data = $lable_model->dataList($dis,$input['limit']);

        return $this->success($data);


    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-04 18:56
     * @功能说明:添加评价标签
     */
    public function commentLableAdd(){

        $input = $this->_input;

        $input['uniacid'] = $this->_uniacid;

        $lable_model = new Lable();

        $res = $lable_model->dataAdd($input);

        return $this->success($res);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-04 18:57
     * @功能说明:编辑评价标签
     */
    public function commentLableUpdate(){

        $input = $this->_input;

        $dis = [

            'id' => $input['id']
        ];

        $lable_model = new Lable();

        $res = $lable_model->dataUpdate($dis,$input);

        return $this->success($res);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-04 18:59
     * @功能说明:评价标签详情
     */
    public function commentLableInfo(){

        $input = $this->_input;

        $dis = [

            'id' => $input['id']
        ];

        $lable_model = new Lable();

        $res = $lable_model->dataInfo($dis);

        return $this->success($res);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-16 10:27
     * @功能说明:提示列表
     */
    public function noticeList(){

        $input = $this->_param;

        $notice_model = new NoticeList();

        $dis[] = ['a.uniacid','=',$this->_uniacid];

        if(!empty($input['type'])){

            $dis[] = ['a.type','=',$input['type']];
        }

        if(isset($input['have_look'])){

            $dis[] = ['a.have_look','=',$input['have_look']];

        }else{

            $dis[] = ['a.have_look','>',-1];

        }

        if(!empty($input['start_time'])){

            $dis[] = ['a.create_time','between',"{$input['start_time']},{$input['end_time']}"];
        }

        if($this->_user['is_admin']==0){

            $dis[] = ['a.admin_id','in',$this->admin_arr];
        }
        //后台开单账号需要判断权限
        $role_arr = [];

        $this->_user['is_admin'] = !empty($this->_user['true_is_admin'])?$this->_user['true_is_admin']:$this->_user['is_admin'];

        if(in_array($this->_user['is_admin'],[2,3])){

            $adminRole_mdoel = new RoleAdmin();

            $role = $adminRole_mdoel->getUserAuth($this->_user['id']);

            $role_arr = array_column($role,'node');
        }

        $data = $notice_model->noticeData($dis,$this->_user['is_admin'],$role_arr,1,$input['limit']);

        if(!empty($data['data'])){

            $order_model = new Order();

            $refund_model= new RefundOrder();

            foreach ($data['data'] as &$v){

                if($v['type']==1){

                    $v['is_add'] = $order_model->where(['id'=>$v['order_id']])->value('is_add');

                }elseif ($v['type']==2){

                    $order_id = $refund_model->where(['id'=>$v['order_id']])->value('order_id');

                    $v['is_add'] = $order_model->where(['id'=>$order_id])->value('is_add');
                }
            }
        }

        return $this->success($data);
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-16 10:38
     * @功能说明:未查看的数量
     */
    public function noLookCount(){

        $input = $this->_param;

        $dis[] = ['a.uniacid','=',$this->_uniacid];

        $dis[] = ['a.have_look','=',0];

        if($this->_user['is_admin']==0){

            $dis[] = ['a.admin_id','in',$this->admin_arr];
        }
        $notice_model = new NoticeList();

        $role_arr = [];

        $is_admin = !empty($this->_user['true_is_admin'])?$this->_user['true_is_admin']:$this->_user['is_admin'];
        //后台开单账号需要判断权限
        if(in_array($is_admin,[2,3])){

            $adminRole_mdoel = new RoleAdmin();

            $role = $adminRole_mdoel->getUserAuth($this->_user['id']);

            $role_arr= array_column($role,'node');
        }

        $data = getConfigSettingArr($this->_uniacid,['have_order_notice','order_tmpl_notice','nopay_notice']);

        $num = $notice_model->noticeData($dis,$is_admin,$role_arr,3,10);

        $data['notice_num'] = $num;

        $dis[] = ['a.is_pop','=',0];

        $data['notice_info'] = $notice_model->noticeData($dis,$is_admin,$role_arr,2,10,$data['have_order_notice']);

        if(!empty($data['notice_info'])){

            $order_model = $data['notice_info']['type']==2?new RefundOrder():new Order();

            $data['notice_info']['order_code'] = $order_model->where(['id'=>$data['notice_info']['order_id']])->value('order_code');

            $data['notice_info']['is_add']     = $order_model->where(['id'=>$data['notice_info']['order_id']])->value('is_add');
        }

        $where[] = ['a.uniacid','=',$this->_uniacid];

        $where[] = ['a.have_look','=',0];

        $where[] = ['a.status','>',-1];

        if($this->_user['is_admin']==0){

            $where[] = ['b.admin_id','=',$this->_user['admin_id']];
        }

        $police_model = new Police();

        $data['police_num'] = $police_model->alias('a')
                             ->join('massage_service_coach_list b','a.coach_id = b.id')
                             ->where($where)
                             ->group('a.id')
                             ->count();

        if($data['police_num']>0){

            $config = new HelpConfig();

            $data['help_voice'] = $config->where(['uniacid'=>$this->_uniacid])->value('help_voice');
        }

        $data['admin_id'] = $this->_user['id'];

        $abn_model = new OrderList();
        //异常订单待处理数量 异常订单标示
        $data['abn_pending_count'] = $abn_model->getPendingOrderCount($this->_uniacid,$this->_user['admin_id'],$this->_user['is_admin']);

        $order_radar = new PermissionOrderradar($this->_uniacid);

        $no_pay_model = new NoPayRecord();

        if($order_radar->pAuth()==true){

            $data['nopay_record'] = $no_pay_model->where(['have_notice'=>0,'uniacid'=>$this->_uniacid])->order('id desc')->value('id');
        }else{

            $data['nopay_record'] = '';
        }
        //异步执行订单消息通知
        publisher(json_encode(['action'=>'orderServiceNotice','uniacid'=>$this->_uniacid],true));
        //各类失败的佣金队列
        publisher(json_encode(['action'=>'list_commison','uniacid'=>$this->_uniacid],true));

//        $model = new SendMsgConfig();
//
//        $model->orderServiceNotice($this->_uniacid);

        return $this->success($data);
    }


    public function nopayNotice(){

        $input = $this->_input;

        $no_pay_model = new NoPayRecord();

        $no_pay_model->where(['id'=>$input['id']])->update(['have_notice'=>1]);

        return $this->success(true);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-16 10:41
     * @功能说明:全部已读
     */
    public function allLook(){

        $input = $this->_param;

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['have_look','=',0];

        $notice_model = new NoticeList();

        $data = $notice_model->dataUpdate($dis,['have_look'=>1,'is_pop'=>1]);

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-16 10:28
     * @功能说明:
     */
    public function noticeUpdate(){

        $input = $this->_input;

        $notice_model = new NoticeList();

        $data = $notice_model->dataUpdate(['id'=>$input['id']],$input);

        return $this->success($data);
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
     * @DataTime: 2021-07-11 22:34
     * @功能说明:技师修改订单信息)
     */
    public function adminUpdateOrder(){

        $input = $this->_input;

        $order = $this->model->dataInfo(['id' => $input['order_id']]);

        $update= $this->model->coachOrdertext($input,1);

        $refund_model = new RefundOrder();
        //判断有无申请中的退款订单
        $refund_order = $refund_model->where(['order_id' => $order['id']])->where('status','in',[1,4,5])->count();

        if (!empty($refund_order)) {

            $this->errorMsg('该订单正在申请退款，请先联系处理再进行下一步');
        }

        $check = $this->model->checkOrderStatus($order['pay_type'],$input['type'],$order['is_add']);

        if(!empty($check['code'])){

            $this->errorMsg($check['msg']);
        }

        $key = 'adminUpdateOrder'.$input['order_id'].'-'.date('Y-m-d H:i',time());

        incCache($key,1,$this->_uniacid);

        if(getCache($key,$this->_uniacid)!=1){

            decCache($key,1,$this->_uniacid);

            $this->errorMsg('当前订单正在被操作，请稍后再试');
        }

        Db::startTrans();

        if($input['type']==7){

            if ($order['pay_type'] != 6&&!empty($order['coach_id'])) {

                decCache($key,1,$this->_uniacid);

                Db::rollback();

                $this->errorMsg('订单状态错误，请刷新页面');
            }
            //核销加钟订单
            $res = $this->model->hxAddOrder($order,0,1,$this->_user['id']);

            if(!empty($res['code'])){

                decCache($key,1,$this->_uniacid);

                Db::rollback();

                $this->errorMsg($res['msg']);
            }

            $res = $this->model->hxOrder($order);

            if(!empty($res['code'])){

                decCache($key,1,$this->_uniacid);

                Db::rollback();

                $this->errorMsg($res['msg']);
            }

        }elseif ($input['type'] == -1){
            //取消订单
            $res = $this->model->cancelOrder($order);

            if (!empty($res['code'])) {

                decCache($key,1,$this->_uniacid);

                Db::rollback();

                $this->errorMsg($res['msg']);
            }

            if ($order['pay_price'] > 0&&$order['pay_type']>1) {

                $refund_model = new RefundOrder();

                $order['coach_refund_time'] = $update['coach_refund_time'];

                $order['coach_refund_text'] = $update['coach_refund_text'];
                //添加到退款订单表
                $refund_id = $refund_model->coachRefundOrder($order,1);

                $res = $refund_model->refundCashV2($this->payConfig($order['app_pay']), $order, $order['pay_price'],$refund_id);

                if (!empty($res['code'])) {

                    decCache($key,1,$this->_uniacid);

                    Db::rollback();

                    $this->errorMsg($res['msg']);
                }

                if (!in_array($res['status'],[2,4])) {

                    decCache($key,1,$this->_uniacid);

                    $this->errorMsg('退款失败，请重试2');
                }
            }
        }

        $this->model->dataUpdate(['id' => $input['order_id']], $update);
        //到达后车费秒到账
        if($input['type']==5){

            $coach_model = new Coach();

            $coach_model->coachCarPriceAccount($order,$this->payConfig($order['app_pay']));
        }

        $log_model = new OrderLog();

        $log_model->addLog($input['order_id'],$this->_uniacid,$input['type'],$order['pay_type'],1,$this->_user['id']);

        Db::commit();

        decCache($key,1,$this->_uniacid);

        return $this->success(true);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-27 14:52
     * @功能说明:订单更换技师
     */
    public function orderChangeCoach(){

        $input = $this->_input;

        $refund_model = new RefundOrder();
        //判断有无申请中的退款订单
        $refund_order = $refund_model->where(['order_id' => $input['order_id']])->where('status','in',[1,4,5])->count();

        if ($refund_order>0) {

            $this->errorMsg('该订单正在申请退款，请先处理再进行下一步');
        }

        $success_add_order = $this->model->dataInfo(['add_pid'=>$input['order_id'],'pay_type'=>7]);

        if(!empty($success_add_order)){

            $this->errorMsg('该订单加钟订单已经完成，无法转单');
        }

        $order = $this->model->dataInfo(['id'=>$input['order_id']]);

        if(!empty($order['coach_id'])&&$order['coach_id']==$input['coach_id']){

            $this->errorMsg('技师不能相同');
        }

        $change_model = new CoachChangeLog();

        $coach_name = !empty($input['coach_name'])?$input['coach_name']:'';

        $text = !empty($input['text'])?$input['text']:'';

        $phone = !empty($input['mobile'])?$input['mobile']:'';

        $admin_id = !empty($input['admin_id'])?$input['admin_id']:0;

        $res = $change_model->orderChangeCoach($order,$input['coach_id'],$this->_user['id'],$admin_id,$coach_name,$text,$phone);

        if (!empty($res['code'])) {

            $this->errorMsg($res['msg']);
        }

        return $this->success($res);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-28 14:21
     * @功能说明:订单转派技师列表
     */
    public function orderChangeCoachList(){

        $input = $this->_param;

        $coach_model = new Coach();

        $order = $this->model->dataInfo(['id'=>$input['order_id']]);
        //获取订单里想关联服务的技师
        $coach_id = $coach_model->getOrderServiceCoach($order);

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['status','=',2];

        $dis[] = ['id','in',$coach_id];

        $dis[] = ['id','<>',$order['coach_id']];

        $dis[] = ['is_work','=',1];

        $dis[] = ['user_id','>',0];

        if($this->_user['is_admin']==0){

            $dis[] = ['admin_id','in',$this->admin_arr];
        }

        if(!empty($input['coach_name'])){

            $dis[] = ['coach_name','like','%'.$input['coach_name'].'%'];
        }
        //门店订单 只能转同门店的技师
        if(!empty($order['store_id'])){

            $store_coach_id = StoreCoach::where(['store_id'=>$order['store_id']])->column('coach_id');

            $dis[] = ['id','in',$store_coach_id];
        }

        $top = !empty($input['type'])&&$input['type']==1?'distance asc,id desc':'near_time asc,id desc';

        $lat = $order['address_info']['lat'];

        $lng = $order['address_info']['lng'];

        $alh = 'ACOS(SIN(('.$lat.' * 3.1415) / 180 ) *SIN((lat * 3.1415) / 180 ) +COS(('.$lat.' * 3.1415) / 180 ) * COS((lat * 3.1415) / 180 ) *COS(('.$lng.' * 3.1415) / 180 - (lng * 3.1415) / 180 ) ) * 6378.137*1000 as distance';

        $list = Db::name('massage_service_coach_list')->where($dis)->field("id,start_time,end_time,$alh")->order($top)->limit(50)->select()->toArray();

        $log_model = new CoachChangeLog();
        //转派技师时候 获取满足条件的技师 并获取最近的服务时间
        $arr = $log_model->getNearTimeCoach($order,$list);

        $data = $coach_model->where('id','in',$arr)->field(['id as coach_id,partner_id,near_time,admin_id,coach_name,work_img,mobile', $alh])->order($top)->paginate($input['limit'])->toArray();

        if(!empty($data['data'])){

            $admin_model = new \app\massage\model\Admin();

            $user_model  = new User();

            foreach ($data['data'] as &$v){

                $v['id'] = $v['coach_id'];

                $v['price'] = $coach_model->getCoachServicePrice($order,$v['id']);

                $v['partner_name'] = $user_model->where(['id'=>$v['partner_id']])->value('nickName');

                $v['near_time'] = date('m-d H:i',$v['near_time']);

                $v['admin_info']= $admin_model->where(['id'=>$v['admin_id'],'agent_coach_auth'=>1,'status'=>1])->field('city_type,agent_name as username')->find();
            }
        }

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-12 14:01
     * @功能说明:添加评价
     */
    public function addComment(){

        $input = $this->_input;

        $insert = [

            'uniacid' => $this->_uniacid,

            'user_id' => 0,

            'order_id'=> 0,

            'star'    => $input['star'],

            'text'    => $input['text'],

            'coach_id'=> $input['coach_id'],

            'admin_id'=> $this->_user['admin_id'],

            'create_time' => $input['create_time'] ?? time(),
        ];

        Db::startTrans();

        $comment_model = new Comment();

        $comment_lable_model = new CommentLable();

        $lable_model = new Lable();

        $res = $comment_model->dataAdd($insert);

        if($res==0){

            Db::rollback();

            $this->errorMsg('评价失败');
        }

        $comment_id = $comment_model->getLastInsID();

        if(!empty($input['label'])){

            foreach ($input['label'] as $value){

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
        $comment_model->updateStar($input['coach_id']);

        Db::commit();

        return $this->success($res,200,$input['coach_id']);
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
     * @DataTime: 2023-04-18 11:31
     * @功能说明:财务报表
     */
    public function financeDetailedList(){

        $comm_model = new Commission();

        $comm_model->initCarpriceAll($this->_uniacid);

        $input = $this->_param;

        $where[] = ['b.uniacid','=',$this->_uniacid];

        $where[] = ['b.pay_time','>',0];

        if(!empty($input['pay_type'])){

            $where[] = ['b.pay_type','=',$input['pay_type']];
        }else{

            $where[] = ['b.pay_type','in',[-1,7]];
        }

        if(!empty($input['order_code'])){

            $where[] = ['b.order_code','like','%'.$input['order_code'].'%'];
        }

        if(!empty($input['start_time'])&&!empty($input['end_time'])){

            $where[] = ['b.create_time','between',"{$input['start_time']},{$input['end_time']}"];
        }

        if($this->_user['is_admin']==0){

            $where[] = ['b.admin_id','in',$this->admin_arr];
        }

        $type = !empty($input['type'])?$input['type']:0;

        if(!empty($input['top_name'])){

            $id = $this->model->getFinanceOrderId($input['top_name'],$type);

            $where[] = ['b.id','in',$id];
        }

        if(!empty($input['user_id'])){

            $dataPath = APP_PATH  . 'massage/info/FinanceObj.php' ;

            $text =  include $dataPath;

            foreach ($text as $v){

                if($input['obj_type']==$v['obj_type']){

                    $where[] = ['a.type','in',$v['type']];

                    $where[] = [$v['field'],'=',$input['user_id']];

                    $where[] = ['a.status','=',2];

                    break;
                }
            }
        }

        if(!empty($input['type'])){

            if($type==2){

                $where[] = ['a.type','in',[2,5,6]];

            }elseif($type==8){

                $where[] = ['a.type','in',[8,13]];

            }else{

                $where[] = ['a.type','=',$type];
            }

            $where[] = ['a.status','=',2];
        }

        $data = $this->model->financeDetailedList($where,$input['limit']);

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-18 11:31
     * @功能说明:财务报表
     */
    public function financeDetailedListTotal(){

        $input = $this->_param;

        $where[] = ['b.uniacid','=',$this->_uniacid];

        $where[] = ['b.pay_time','>',0];

        if(!empty($input['pay_type'])){

            $where[] = ['b.pay_type','=',$input['pay_type']];
        }else{

            $where[] = ['b.pay_type','in',[-1,7]];
        }

        if(!empty($input['order_code'])){

            $where[] = ['b.order_code','like','%'.$input['order_code'].'%'];
        }

        if(!empty($input['start_time'])&&!empty($input['end_time'])){

            $where[] = ['b.create_time','between',"{$input['start_time']},{$input['end_time']}"];
        }

        if($this->_user['is_admin']==0){

            $where[] = ['b.admin_id','in',$this->admin_arr];
        }

        $type = !empty($input['type'])?$input['type']:0;

        if(!empty($input['top_name'])){

            $id = $this->model->getFinanceOrderId($input['top_name'],$type);

            $where[] = ['b.id','in',$id];
        }

        if(!empty($input['user_id'])){

            $dataPath = APP_PATH  . 'massage/info/FinanceObj.php' ;

            $text =  include $dataPath;

            foreach ($text as $v){

                if($input['obj_type']==$v['obj_type']){

                    $where[] = ['a.type','in',$v['type']];

                    $where[] = [$v['field'],'=',$input['user_id']];

                    $where[] = ['a.status','=',2];

                    break;
                }
            }
        }

        if(!empty($input['type'])){

            if($type==2){

                $where[] = ['a.type','in',[2,5,6]];

            }elseif($type==8){

                $where[] = ['a.type','in',[8,13]];

            }else{

                $where[] = ['a.type','=',$type];
            }

            $where[] = ['a.status','=',2];
        }
        //总计
        $data = $this->model->financeDetailedDataV2($where);

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-03-29 15:21
     * @功能说明:获取财务报表搜索的角色
     */
    public function getFinanceDetailedObj(){

        $input = $this->_param;

        $type = !empty($input['type'])?$input['type']:0;

        $data = $this->model->getFinanceObj($input['top_name'],$type);

        return $this->success($data);

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-30 09:48
     * @功能说明:技师轨迹BAIY SGYE
     */
    public function coachTrajectory(){

        $input = $this->_param;

        $order = $this->model->dataInfo(['id'=>$input['order_id']]);

        $trajectory_model = new Trajectory();
        //已经行驶的轨迹
        $data = $trajectory_model->dataInfo(['order_id'=>$input['order_id']]);

        if(empty($data['text'])){

            $text = [

                [
                    'lat' => $order['address_info']['coach_lat'],

                    'lng' => $order['address_info']['coach_lng'],
                ],
                [

                    'lat' => $order['address_info']['lat'],

                    'lng' => $order['address_info']['lng'],
                ]
            ];
        }else{

            $text = unserialize($data['text']);

            if(in_array($order['pay_type'],[4])){

                if(!empty($data)){

                    $start = array_pop($text);

                    $strt_lat = $start['lat'];

                    $strt_lng = $start['lng'];
                }

                $arr['list'] = getTrajectory($strt_lng,$strt_lat,$order['address_info']['lng'],$order['address_info']['lat'],$this->_uniacid);
            }

        }

        $arr['have_list'] = $text;

        return $this->success($arr);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-09-18 14:11
     * @功能说明:平台流水
     */
    public function companyWater(){

        $order_model = new Order();

        $order_model->where(['pay_model'=>1])->where('balance','>',0)->update(['pay_model'=>2]);

        $input = $this->_param;

        $where1 = $where3= $dis1=$dis2=$map1=$map2=[];

        if(!empty($input['start_time'])){

            $where1[] = ['pay_time','between',"{$input['start_time']},{$input['end_time']}"];

            $where3[] = ['a.refund_time','between',"{$input['start_time']},{$input['end_time']}"];
        }

        if(!empty($input['order_code'])){

            $dis1[] = ['order_code','like','%'.$input['order_code'].'%'];

            $dis1[] = ['transaction_id','like','%'.$input['order_code'].'%'];

            $dis2[] = ['a.order_code','like','%'.$input['order_code'].'%'];

            $dis2[] = ['a.out_refund_no','like','%'.$input['order_code'].'%'];
        }

        if(!empty($input['pay_model'])){

            $map1[] = ['pay_model','=',$input['pay_model']];

            $map2[] = ['b.pay_model','=',$input['pay_model']];
        }

        $a = Db::name('massage_service_refund_order')->alias('a')
            ->join('massage_service_order_list b','a.order_id = b.id','right')
            ->where($where3)
            ->where($map2)
            ->where(['a.type'=>2])
            ->where(function ($query) use ($dis2){
                $query->whereOr($dis2);
            })->field(['a.order_id as id','b.user_id','a.order_code','b.pay_model','a.out_refund_no as transaction_id','a.refund_price as pay_price','a.refund_time as pay_time','if(a.id=-1,-1,1) as type'])->where(['a.uniacid'=>$this->_uniacid,'a.status'=>2])->buildSql();

        $b = Db::name('massage_service_balance_order_list')->where($map1)->where(function ($query) use ($dis1){
            $query->whereOr($dis1);
        })->where($where1)->field(['id','user_id','order_code','pay_model','transaction_id','pay_price','pay_time','if(id=-1,-1,3) as type'])->where('pay_time','>',0)->where(['uniacid'=>$this->_uniacid,'type'=>1])->buildSql();

        $c = Db::name('massage_service_refund_order')->alias('a')
            ->join('massage_service_order_list b','a.order_id = b.id','right')
            ->where($where3)
            ->where($map2)
            ->where(['a.type'=>1])
            ->where(function ($query) use ($dis2){
            $query->whereOr($dis2);
        })->field(['a.id','b.user_id','a.order_code','b.pay_model','a.out_refund_no as transaction_id','a.refund_price as pay_price','a.refund_time as pay_time','if(a.id=-1,-1,4) as type'])->where(['a.uniacid'=>$this->_uniacid,'a.status'=>2])->buildSql();

        $d = Db::name('massage_service_up_order_list')->where($map1)->where($where1)->where(function ($query) use ($dis1){
            $query->whereOr($dis1);
        })->field(['id','user_id','order_code','pay_model','transaction_id','pay_price','pay_time','if(id=-1,-1,5) as type'])->where('pay_time','>',0)->where(['uniacid'=>$this->_uniacid])->buildSql();

        $e = Db::name('massage_service_order_list')->where($map1)->where($where1)->where(function ($query) use ($dis1){
            $query->whereOr($dis1);
        })->where('pay_time','>',0)->where(['is_add'=>1])->field(['id','user_id','order_code','pay_model','transaction_id','pay_price','pay_time','if(id=-1,-1,6) as type'])->where(['uniacid'=>$this->_uniacid])->buildSql();

        $f = Db::name('massage_payreseller_order_list')->where($map1)->where($where1)->where(function ($query) use ($dis1){
            $query->whereOr($dis1);
        })->where('pay_time','>',0)->field(['id','user_id','order_code','pay_model','transaction_id','pay_price','pay_time','if(id=-1,-1,7) as type'])->where(['uniacid'=>$this->_uniacid])->buildSql();

        $g = Db::name('massage_service_order_list')->where($map1)->where(['is_add'=>0])->where($where1)->where(function ($query) use ($dis1){
            $query->whereOr($dis1);
        })->where('pay_time','>',0)->field(['id','user_id','order_code','pay_model','transaction_id','pay_price','pay_time','if(id=-1,-1,2) as type'])->where(['uniacid'=>$this->_uniacid])->order('pay_time desc,id desc')->buildSql();

        $h = Db::name('massage_admin_recharge_list')->where($map1)->where($where1)->where(function ($query) use ($dis1){
            $query->whereOr($dis1);
        })->where('pay_time','>',0)->field(['id','admin_id as user_id','order_code','pay_model','transaction_id','cash as pay_price','pay_time','if(id=-1,-1,8) as type'])->where(['uniacid'=>$this->_uniacid])->order('pay_time desc,id desc')->buildSql();

        $i = Db::name('massage_member_discount_order_list')->where($map1)->where($where1)->where(function ($query) use ($dis1){
            $query->whereOr($dis1);
        })->where('pay_time','>',0)->field(['id','user_id','order_code','pay_model','transaction_id',' pay_price','pay_time','if(id=-1,-1,9) as type'])->where(['uniacid'=>$this->_uniacid])->order('pay_time desc,id desc')->buildSql();

        $j = Db::name('massage_balance_discount_order_list')->where($map1)->where($where1)->where(function ($query) use ($dis1){
            $query->whereOr($dis1);
        })->where('pay_time','>',0)->field(['id','user_id','order_code','pay_model','transaction_id',' pay_price','pay_time','if(id=-1,-1,10) as type'])->where(['uniacid'=>$this->_uniacid])->order('pay_time desc,id desc')->buildSql();

        $k = Db::name('massage_partner_order')->where($map1)->where($where1)->where(function ($query) use ($dis1) {
            $query->whereOr($dis1);
        })->where('pay_time', '>', 0)->field(['id', 'user_id', 'order_code', 'pay_model', 'transaction_id', ' pay_price', 'pay_time', 'if(id=-1,-1,11) as type'])->where(['uniacid' => $this->_uniacid])->order('pay_time desc,id desc')->buildSql();

        $l = Db::name('massage_partner_order')->where($map1)->where($where1)->where(function ($query) use ($dis1) {
            $query->whereOr($dis1);
        })
            ->where(function ($query) {
                $query->whereOr(['is_cancel' => 1, 'status' => 3]);
            })
            ->where('pay_time', '>', 0)->field(['id', 'user_id', 'order_code', 'pay_model', 'transaction_id', ' pay_price', 'pay_time', 'if(id=-1,-1,12) as type'])->where(['uniacid' => $this->_uniacid])->order('pay_time desc,id desc')->buildSql();

        $m = Db::name('massage_partner_order_join')->where($map1)->where($where1)->where(function ($query) use ($dis1) {
            $query->whereOr($dis1);
        })->where('is_create', 0)->where('pay_time', '>', 0)->field(['id', 'user_id', 'order_code', 'pay_model', 'transaction_id', ' pay_price', 'pay_time', 'if(id=-1,-1,13) as type'])->where(['uniacid' => $this->_uniacid])->order('pay_time desc,id desc')->buildSql();

        $n = Db::name('massage_partner_order_join')->where($map1)->where($where1)->where(function ($query) use ($dis1) {
            $query->whereOr($dis1);
        })
            ->where(function ($query) {
                $query->whereOr([['status', '=', -1], ['status', '=', 3]]);
            })
            ->where('is_create', 0)->where('pay_time', '>', 0)->field(['id', 'user_id', 'order_code', 'pay_model', 'transaction_id', ' pay_price', 'pay_time', 'if(id=-1,-1,14) as type'])->where(['uniacid' => $this->_uniacid])->order('pay_time desc,id desc')->buildSql();

        $arr = [
            1=>$a,
            2=>$g,
            3=>$b,
            4=>$c,
            5=>$d,
            6=>$e,
            7=>$f,
            8=>$h,
            9=>$i,
            10=>$j,
            11=>$k,
            12=>$l,
            13=>$m,
            14=>$n,
        ];

        if(!empty($input['type'])){

            $arrs[] = $arr[$input['type']];

        }else{

            $arrs = $arr;
        }

        $sql = Db::name('massage_service_order_list')->where($map1)->where(['is_add'=>-1])->where($where1)->where(function ($query) use ($dis1){
            $query->whereOr($dis1);
        })->where('pay_time','>',0)->unionAll($arrs)->field(['id','user_id','order_code','pay_model','transaction_id','pay_price','pay_time','if(id=-1,-1,2) as type'])->where(['uniacid'=>$this->_uniacid])->order('pay_time desc,id desc')->buildSql();

        $data = Db::table($sql.' a')->paginate($input['limit'])->toArray();

        if(!empty($data)){

            $refund_model = new RefundOrder();

            $user_model   = new User();

            $admin_model  = new \app\massage\model\Admin();

            foreach ($data['data'] as $k=>$v){

                if(in_array($v['type'],[2,6])){

                    $data['data'][$k]['refund_cash'] = $refund_model->where(['order_id'=>$v['id'],'status'=>2])->sum('refund_price');

                    $up_price = Db::name('massage_service_up_order_list')->where(['pay_type'=>2,'order_id'=>$v['id']])->sum('pay_price');

                    $data['data'][$k]['pay_price'] -= $up_price;

                    $data['data'][$k]['pay_price'] = round($data['data'][$k]['pay_price'],2);
                }
                if($v['type']!=8){
                    //用户昵称
                    $data['data'][$k]['nickName'] = $user_model->where(['id'=>$v['user_id']])->value('nickName');
                }else{
                    //用户昵称
                    $data['data'][$k]['nickName'] = $admin_model->where(['id'=>$v['user_id']])->value('agent_name');
                }
            }
        }
        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-12-04 16:34
     * @功能说明:可以退款的商品
     * BAIY SGYE
     */
    public function canRefundOrderInfo(){

        $input = $this->_param;

        $can_refund = $this->model->orderCanRefund($input['order_id']);

        if($can_refund==0){

            $this->errorMsg('订单已经申请退款');
        }

        $order_goods_model = new OrderGoods();

        $refund_model = new RefundOrder();

        $map[] = ['b.add_pid','=',$input['order_id']];

        $map[] = ['b.id','=',$input['order_id']];

        $price_log_model = new OrderPrice();

        $order = $price_log_model->alias('a')
            ->join('massage_service_order_list b','a.top_order_id = b.id')
            ->where('b.pay_type','not in',[7,-1,1])
           // ->where('a.can_refund_price','>',0)
            ->where(function ($query) use ($map){
                $query->whereOr($map);
            })
            ->field('b.id as order_id,b.pay_type,b.start_service_time,b.pay_price,b.car_price,b.discount,b.is_add,b.pay_model,b.true_car_price,b.start_time,b.end_time,b.true_service_price,b.material_price')
            ->group('b.id')
            ->order('b.is_add,b.id desc')
            ->select()
            ->toArray();

        if(empty($order)){

            $this->errorMsg('订单状态错误，请刷新页面');
        }

        $arr['discount'] = $arr['car_price'] = 0;

        foreach ($order as &$value){

            $order_goods = $order_goods_model->where(['order_id'=>$value['order_id'],'status'=>1])->where('can_refund_num','>',0)->field('id,goods_name,goods_cover,true_price,material_price,num,can_refund_num')->select()->toArray();

            $order_goods_list = [];

            if(!empty($order_goods)){

                foreach ($order_goods as $v){

                    $v['residue_service_price'] = $v['true_price']*$v['can_refund_num'];

                    $v['residue_material_price']= $v['material_price']*$v['can_refund_num'];

                    $v['residue_service_price'] = $v['residue_service_price']<$value['true_service_price']?$v['residue_service_price']:$value['true_service_price'];

                    $v['residue_material_price']= $v['residue_material_price']<$value['material_price']?$v['residue_material_price']:$value['material_price'];

                    $v['true_price'] = round($v['true_price'],2);

                    $order_goods_list[] = $v;
                }
            }

            $value['order_goods'] = $order_goods_list;

            $arr['discount']+= $value['discount'];

            if($value['is_add']==0){
                //可退车费
                $arr['car_price'] = $refund_model->canRefundOrderPrice($value['order_id']);
            }
        }

        $arr['order'] = $order;

        $refund_model = new RefundOrder();

        $have_empty = $refund_model->where(['order_id'=>$input['order_id']])->where('status','in',[1,2,4,5])->where('refund_empty_cash','>',0)->count();

        if(!empty($have_empty)){
            //空单费
            $arr['empty_order_cash']= 0;
        }else{
            //空单费
            $arr['empty_order_cash']= getConfigSetting($this->_uniacid,'empty_order_cash');
        }
        //空单费
        $arr['after_service_can_refund']= getConfigSetting($this->_uniacid,'after_service_can_refund');

        $fee_model = new EmptyTicketFeeConfig();

        $arr['cash_list'] = $fee_model->where(['uniacid'=>$this->_uniacid])->select()->toArray();

        return $this->success($arr);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-12-06 14:32
     * @功能说明:申请退款
     */
    public function applyOrderRefund(){

        $input = $this->_input;

        if(empty($input['order'])){

            $this->errorMsg('请勾选订单');
        }

        $refund_model = new RefundOrder();

        $input['order'] = arraySort($input['order'],'order_id','desc');

        foreach ($input['order'] as $value){

            $key = 'order_refund_key_cant_refund'.$value['order_id'];

            incCache($key,1,$this->_uniacid,20);

            if(getCache($key,$this->_uniacid)==1){

                Db::startTrans();

                $car_price = !empty($value['car_price'])?$value['car_price']:0;

                $order = $this->model->dataInfo(['id'=>$value['order_id']]);

                if(empty($order)){
                    Db::rollback();
                    decCache($key,1,$this->_uniacid);
                    $this->errorMsg('订单未找到！');
                }

                $add_order = $this->model->where(['add_pid'=>$order['id']])->where('pay_type','>',1)->count();

                if($add_order>0){
                    Db::rollback();
                    decCache($key,1,$this->_uniacid);
                    $this->errorMsg('请先申请加钟订单退款');
                }

                if(empty($value['list'])&&$car_price<=0){
                    Db::rollback();
                    decCache($key,1,$this->_uniacid);

                    $this->errorMsg('请选择商品');
                }


                $can_refund_num = array_sum(array_column($order['order_goods'],'can_refund_num'));
                if($can_refund_num<=0){

                    $this->errorMsg('数量已退完');
                }

                if($order['pay_type']==7){
                    Db::rollback();
                    decCache($key,1,$this->_uniacid);

                    $this->errorMsg('核销后不能退款');
                }

                $refund_empty_cash = isset($value['refund_empty_cash'])?$value['refund_empty_cash']:0;

                $apply_empty_cash  = isset($value['apply_empty_cash'])?$value['apply_empty_cash']:0;

                $comm_balance      = isset($value['comm_balance'])?$value['comm_balance']:0;
                //申请退款
                $res = $refund_model->applyRefundAdmin($order,$value['list'],$car_price,$this->_user['id'],1,$refund_empty_cash,$apply_empty_cash,$comm_balance);

                if(!empty($res['code'])){
                    Db::rollback();
                    decCache($key,1,$this->_uniacid);

                    $this->errorMsg($res['msg']);
                }

                $refund_order = $this->refund_order_model->dataInfo(['id'=>$res]);

                $res = $this->refund_order_model->passOrder($res,$refund_order['apply_price'],$this->payConfig($order['app_pay']),$this->_user['id']);

                if(!empty($res['code'])){

                    decCache($key,1,$this->_uniacid);

                    $this->errorMsg($res['msg']);
                }

                decCache($key,1,$this->_uniacid);

                Db::commit();
            }else{

                decCache($key,1,$this->_uniacid);

                $this->errorMsg('该订单正在发起退款，请稍后再试');
            }
        }

        return $this->success(true);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-01-18 18:18
     * @功能说明:订单操作记录
     */
    public function orderControlRecord(){

        $input = $this->_param;

        $log_model = new OrderLog();

        $admin_model = new \app\massage\model\Admin();

        $coach_model = new Coach();

        $user_model  = new User();

        $up_model = new UpOrderList();

        $address_record_model= new UpdateAddressRecord();

        $order = $this->model->dataInfo(['id'=>$input['order_id']]);

        $user_name = $user_model->where(['id'=>$order['user_id']])->value('nickName');

        $up_order_price = $up_model->where(['order_id'=>$order['id'],'pay_type'=>2])->sum('pay_price');
        //订单付款后的操作记录
        $dis = [

            ['a.type' ,'in', [1,3,8]],

            ['b.id'    ,'=',  $input['order_id']],
        ];

        $dis1 =[
            ['c.order_id' ,'=', $input['order_id']],

            ['a.type'   ,'=',  2]
        ];

        $list = $log_model->alias('a')
                ->join('massage_service_order_list b','a.order_id = b.id AND a.type in (1,3,8)','left')
                ->join('massage_service_refund_order c','a.order_id = c.id AND a.type = 2','left')
                ->where(function ($query) use ($dis,$dis1){
                    $query->whereOr([$dis,$dis1]);
                })
                ->field('a.id,a.admin_control,a.old_mobile,a.user_id,ifnull(b.order_code,c.order_code) as order_code,a.create_time,a.pay_type,a.type,ifnull(b.pay_price,c.refund_price) as price')
                ->group('a.id')
                ->order('a.id desc')
                ->select()
                ->toArray();

        if(!empty($list)){

            foreach ($list as $k=>$v){

                if($v['type']==8){

                    $list[$k]['address'] = $address_record_model->dataInfo(['log_id'=>$v['id']]);
                }

                $list[$k]['type'] = $v['type']==3?7:$v['type'];

                if($v['type']==1){

                    $list[$k]['price'] -= $up_order_price;
                }

                $v['admin_control'] = $v['pay_type']==2&&$v['type']==1?3:$v['admin_control'];

                $v['user_id'] = $v['admin_control']==2&&$v['pay_type']!=8?$order['coach_id']:$v['user_id'];

                if($v['admin_control']==1){

                    $list[$k]['user_name'] = $admin_model->where(['id'=>$v['user_id']])->value('username');

                }elseif ($v['admin_control']==2){

                    $list[$k]['user_name'] = $coach_model->where(['id'=>$v['user_id']])->value('coach_name');

                }elseif ($v['admin_control']==4){

                    $list[$k]['user_name'] = $user_model->where(['id'=>$v['user_id']])->value('nickName');

                }else{

                    $list[$k]['user_name'] = $user_model->where(['id'=>$v['user_id']])->value('nickName');
                }
            }
        }
        //升级订单
        $up_order = $up_model->where(['order_id'=>$order['id'],'pay_type'=>2])->field("order_code,pay_price as price,create_time,if(id=-1,-1,3) as type")->select()->toArray();

        if(!empty($up_order)){

            foreach ($up_order as $k=>$v){

                $up_order[$k]['user_name'] = $user_name;
            }

            $list = array_merge($list,$up_order);
        }
        //加钟订单
        $add_order = $this->model->where(['is_add'=>1,'add_pid'=>$input['order_id']])->field("order_code,pay_price as price,create_time,if(id=-1,-1,4) as type")->where('pay_time','>',0)->select()->toArray();

        if(!empty($add_order)){

            foreach ($add_order as $k=>$v){

                $add_order[$k]['user_name'] = $user_name;
            }

            $list = array_merge($list,$add_order);
        }

        $comment_model = new Comment();
        //评价
        $comment = $comment_model->where(['order_id'=>$input['order_id']])->field('create_time,if(id=-1,-1,5) as type')->select()->toArray();

        if(!empty($comment)){

            foreach ($comment as $k=>$v){

                $comment[$k]['user_name'] = $user_name;
            }

            $list = array_merge($list,$comment);
        }

        $change_model = new CoachChangeLog();
        //转单
        $change = $change_model->where(['order_id'=>$input['order_id']])->field('create_time,if(id=-1,-1,6) as type,control_id,control_type')->select()->toArray();

        if(!empty($change)){

            foreach ($change as $k=>$v){

                if($v['control_type']==1){

                    $change[$k]['user_name'] = $admin_model->where(['id'=>$v['control_id']])->value('agent_name');
                }else{

                    $change[$k]['user_name'] = $user_model->where(['id'=>$v['control_id']])->value('nickName');
                }
            }
            $list = array_merge($list,$change);
        }

        $list = arraySort($list,'create_time','desc');

        $arr = [

            'order_code' => $order['order_code'],

            'create_time'=> $order['create_time'],

            'type'       => 1,

            'pay_type'   => 1,

            'price'      => $order['pay_price'],

            'user_name'  => $user_name
        ];

        $list[] = $arr;

        return $this->success($list);
    }



    /**DXV RGWU
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-28 11:03
     * @功能说明:技师佣金详情
     */
    public function coachCommissionInfo(){

        $input = $this->_param;

        $comm_model = new Commission();

        $arr = [
            [//技师
                'type' => [3],

                'cash' => 'coach_cash'
            ],
            [//车费
                'type' => [8],

                'cash' => 'car_cash'
            ],
            [//分销
                'type' => [1],

                'cash' => 'reseller_cash'
            ],
            [//二级分销
                'type' => [14],

                'cash' => 'level_reseller_cash'
            ],
            [//经纪人
                'type' => [9],

                'cash' => 'broker_cash'
            ],
            [//渠道商
                'type' => [10],

                'cash' => 'channel_cash'
            ],
            [//业务员
                'type' => [12],

                'cash' => 'salesman_cash'
            ],
            [//代理商城市
                'type' => [2,5,6],

                'cash' => 'admin_cash',

                'city_type' => 1
            ],
            [//区代理
                'type' => [2,5,6],

                'cash' => 'level_admin_cash',

                'city_type' => 2
            ],
            [//省代理
                'type' => [2,5,6],

                'cash' => 'province_admin_cash',

                'city_type' => 3
            ]
        ];

        foreach ($arr as $value){

            $dis = [];

            $dis[] = ['order_id','=',$input['order_id']];

            $dis[] = ['type','in',$value['type']];

            if(!empty($value['city_type'])){

                $dis[] = ['city_type','=',$value['city_type']];
            }

            $arrs[$value['cash']] = $comm_model->where($dis)->where('status','>',-1)->sum('cash');

            $arrs[$value['cash']] = round($arrs[$value['cash']],2);
        }

        $refund_model = new RefundOrder();
        //退款金额
        $arrs['refund_cash'] = $refund_model->where(['order_id'=>$input['order_id'],'status'=>2])->sum('refund_price');
        //空单费
        $arrs['refund_empty_cash']= $refund_model->where(['order_id'=>$input['order_id'],'status'=>2])->sum('refund_empty_cash');
        //退款手续费
        $arrs['refund_comm_cash'] = $refund_model->where(['order_id'=>$input['order_id'],'status'=>2])->sum('refund_comm_cash');

        return $this->success($arrs);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-05-07 16:06
     * @功能说明:未提交订单记录
     */
    public function noPayRecordList(){

        $input = $this->_param;

        $no_pay_model = new NoPayRecord();

        $no_pay_goods_model = new NoPayRecordGoods();

        $coach_model = new Coach();

        $order_model = new Order();

        $dis[] = ['a.uniacid','=',$this->_uniacid];

        $dis[] = ['a.status','=',1];

        if(!empty($input['nickName'])){

            $dis[] = ['b.nickName','like','%'.$input['nickName'].'%'];
        }

        if(!empty($input['start_time'])){

            $dis[] = ['a.create_time','between',"{$input['start_time']},{$input['end_time']}"];
        }

        if($this->_user['is_admin']==0){

            $dis[] = ['c.admin_id','=',$this->_user['admin_id']];
        }

        if (isset($input['by_status']) && $input['by_status'] !== '') {

            $dis[] = ['a.by_status', '=', $input['by_status']];
        }

        if (!empty($input['province'])) {

            $dis[] = ['b.province', 'like', '%' . $input['province'] . '%'];
        }

        if (!empty($input['city'])) {

            $dis[] = ['b.city', 'like', '%' . $input['city'] . '%'];
        }

        if (!empty($input['area'])) {

            $dis[] = ['b.area', 'like', '%' . $input['area'] . '%'];
        }

        $data = $no_pay_model->alias('a')
                ->join('massage_service_user_list b','a.user_id = b.id')
                ->join('massage_service_coach_list c','a.coach_id = c.id')
                ->where($dis)
                ->field('a.*,b.nickName,b.avatarUrl,b.phone,b.city,b.province,b.area')
                ->group('a.id')
                ->order('a.id desc')
                ->paginate($input['limit'])
                ->toArray();

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $goods_name = $no_pay_goods_model->where(['record_id'=>$v['id']])->column('goods_name');

                $v['goods_name'] = implode('、',$goods_name);

                $v['coach_name'] = $coach_model->where(['id'=>$v['coach_id']])->value('coach_name');

                $v['order_count']= $order_model->where(['user_id'=>$v['user_id']])->where('pay_time','>',0)->count();
            }
        }

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-05-16 15:08
     * @功能说明:修改订单电话
     */
    public function updateOrderMobile(){

        $input = $this->_input;

        $order_address_model = new OrderAddress();

        $order_info = $this->model->where(['id'=>$input['order_id']])->field('id as order_id,is_add,add_pid')->find();

        $order_id = $order_info['is_add']==0?$order_info['order_id']:$order_info['add_pid'];

        $dis = [

            ['add_pid' ,'=', $order_id],

            ['is_add'    ,'=',  1],
        ];

        $dis1 =[

            ['id' ,'=', $order_id],

            ['is_add'   ,'=',  0]
        ];

        $order_list = $this->model->where(function ($query) use ($dis,$dis1){
            $query->whereOr([$dis,$dis1]);
        })->field('id as order_id')->select()->toArray();

        Db::startTrans();

        if(!empty($order_list)){

            foreach ($order_list as $value){

                $res = $order_address_model->updateOrderMobile($value['order_id'],$input['mobile'],$this->_user['id']);

                if(!empty($res['code'])){

                    Db::rollback();

                    $this->errorMsg($res['msg']);
                }
            }
        }

        Db::commit();

        return $this->success($res);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-05-22 16:44
     * @功能说明:修改订单地址
     */
    public function updateOrderAddress(){

        $input = $this->_input;

        $order_address_model = new OrderAddress();

        $order_info = $this->model->where(['id'=>$input['order_id']])->field('id as order_id,is_add,add_pid')->find();

        $order_id = $order_info['is_add']==0?$order_info['order_id']:$order_info['add_pid'];

        $dis = [

            ['add_pid' ,'=', $order_id],

            ['is_add'    ,'=',  1],
        ];

        $dis1 =[
            ['id' ,'=', $order_id],

            ['is_add'   ,'=',  0]
        ];

        $order_list = $this->model->where(function ($query) use ($dis,$dis1){
            $query->whereOr([$dis,$dis1]);
        })->field('id as order_id')->select()->toArray();

        Db::startTrans();

        if(!empty($order_list)){

            foreach ($order_list as $value){

                $res = $order_address_model->updateOrderAddress($value['order_id'],$input,$this->_user['id'],1);

                if(!empty($res['code'])){

                    Db::rollback();

                    $this->errorMsg($res['msg']);
                }
            }
        }

        Db::commit();

        return $this->success(true);
    }








}
