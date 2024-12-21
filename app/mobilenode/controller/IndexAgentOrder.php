<?php
namespace app\mobilenode\controller;
use app\abnormalorder\model\OrderInfo;
use app\abnormalorder\model\OrderInfoHandle;
use app\abnormalorder\model\OrderList;
use app\AdminRest;
use app\ApiRest;
use app\balancediscount\model\OrderShare;
use app\dynamic\model\DynamicList;
use app\fdd\model\FddAgreementRecord;
use app\fdd\model\FddAttestationRecord;
use app\fdd\model\FddConfig;
use app\industrytype\model\Type;
use app\massage\model\Admin;
use app\massage\model\AdminConfig;
use app\massage\model\AdminWater;
use app\massage\model\CashUpdateRecord;
use app\massage\model\City;
use app\massage\model\Coach;
use app\massage\model\CoachChangeLog;
use app\massage\model\CoachLevel;
use app\massage\model\CoachTimeList;
use app\massage\model\CoachUpdate;
use app\massage\model\Comment;
use app\massage\model\CommentGoods;
use app\massage\model\CommentLable;
use app\massage\model\Commission;
use app\massage\model\Config;
use app\massage\model\CouponRecord;
use app\massage\model\CouponStore;
use app\massage\model\EmptyTicketFeeConfig;
use app\massage\model\HelpConfig;
use app\massage\model\Lable;
use app\massage\model\NoticeList;
use app\massage\model\Order;

use app\massage\model\OrderAddress;
use app\massage\model\OrderData;
use app\massage\model\OrderGoods;
use app\massage\model\OrderLog;
use app\massage\model\OrderPrice;
use app\massage\model\Police;
use app\massage\model\RefundOrder;
use app\massage\model\RefundOrderGoods;
use app\massage\model\ShortCodeConfig;
use app\massage\model\StoreCoach;
use app\massage\model\StoreCoachUpdate;
use app\massage\model\StoreList;
use app\massage\model\UpOrderList;
use app\massage\model\User;
use app\massage\model\Wallet;
use app\massage\model\WorkLog;
use app\mobilenode\model\RechargeList;
use app\mobilenode\model\RoleAdmin;
use app\store\info\PermissionStore;
use longbingcore\permissions\SaasAuthConfig;
use longbingcore\wxcore\aliyun;
use longbingcore\wxcore\Fdd;
use longbingcore\wxcore\Moor;
use longbingcore\wxcore\PayModel;
use longbingcore\wxcore\WxSetting;
use think\App;
use app\shop\model\Order as Model;
use think\facade\Db;
use tp5er\Backup;


class IndexAgentOrder extends ApiRest
{


    protected $model;

    protected $refund_order_model;

    protected $comment_model;

    protected $admin_info;

    public function __construct(App $app) {

        parent::__construct($app);

        $this->model = new Order();

        $this->refund_order_model = new RefundOrder();

        $this->comment_model = new Comment();

        $this->admin_info = $this->adminInfo();

        if(empty($this->admin_info)){

            $this->errorMsg('你还不是代理商');
        }
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-20 11:44
     * @功能说明:获取当前用户的角色权限
     */
    public function adminInfo(){

        $admin_model = new Admin();

        $dis = [

            'user_id' => $this->getUserId(),

            'status'  => 1
        ];

        $data = $admin_model->dataInfo($dis);

        if(!empty($data)){

            $data['admin_arr'] = $admin_model->getAdminId($data);

            if($data['store_auth']==1){

                $data['store_info'] = $this->adminStoreId($data['store_id'],$data['id']);
            }else{

                $data['store_info'] = [];
            }
        }

        return $data;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-20 11:54
     * @功能说明:首页各项数据统计
     */
    public function index(){
        //订单管理各类状态
        $order_type = [2,3,4,5,6,7];

        $dis = [

            'uniacid' => $this->_uniacid,

            'is_add'  => 0,
        ];
        //订单数据
        foreach ($order_type as $value){

            $data['order_count'][$value] = $this->model->where($dis)->where('admin_id','in',$this->admin_info['admin_arr'])->where(['pay_type'=>$value])->count();
        }
        //拒单
        $data['refuse_order'][8] = $this->model->where($dis)->where('admin_id','in',$this->admin_info['admin_arr'])->where(['pay_type'=>8])->count();
        //加钟服务
        $dis['is_add'] = 1;

        $order_type = [2,3,6,7];
        //订单数据
        foreach ($order_type as $value){

            if($value==6){

                $data['add_count'][$value] = $this->model->where($dis)->where('admin_id','in',$this->admin_info['admin_arr'])->where('pay_type','in',[4,5,6])->count();

            }else{

                $data['add_count'][$value] = $this->model->where($dis)->where('admin_id','in',$this->admin_info['admin_arr'])->where(['pay_type'=>$value])->count();
            }

        }
        $order_type = [1];
        //加钟退款
        foreach ($order_type as $value){

            $data['add_refund_count'][$value] = $this->refund_order_model->where($dis)->where('admin_id','in',$this->admin_info['admin_arr'])->where(['status'=>$value])->count();

        }

        $dis['is_add'] = 0;
        //退款
        foreach ($order_type as $value){

            $data['refund_count'][$value] = $this->refund_order_model->where($dis)->where('admin_id','in',$this->admin_info['admin_arr'])->where(['status'=>$value])->count();
        }
        //是否具有邀请业务员的权限
        $data['salesman_auth'] = $this->admin_info['salesman_auth'];
        //是否有邀请渠道商的权限
        $data['channel_auth']  = $this->admin_info['channel_auth'];
        //邀请技师权限
        $data['agent_coach_auth'] = $this->admin_info['agent_coach_auth'];
        //发展下级代理商权限
        $data['sub_agent_auth'] = $this->admin_info['sub_agent_auth'];

        $data['store_auth'] = $this->admin_info['store_auth'];

        if($data['store_auth']==1){

            $store_p = new PermissionStore((int)$this->_uniacid);

            $p_auth = $store_p->pAuth();

            if($p_auth==false){

                $data['store_auth'] = 0;
            }
        }

        $data['group_write_off_auth'] = $this->admin_info['group_write_off_auth'];

        if($data['store_auth']==1){

            $data['store_info'] = $this->admin_info['store_info'];

            $store_model = new \app\store\model\StoreList();

            $data['store_num'] = $store_model->where(['admin_id'=>$this->admin_info['id'],'auth_status'=>2])->where('status','>',-1)->count();
        }

        $channel_check_status = getConfigSetting($this->_uniacid,'channel_check_status');

        $data['channel_auth'] = $channel_check_status==0?0:$data['channel_auth'];

        $comm_model = new Commission();

        $dis = [

            'admin_id' => $this->admin_info['id'],

            'status'   => 1,
        ];
        //未入账金额
        $data['unrecorded_cash'] = $comm_model->where($dis)->where('type','in',[2,5,6,13])->sum('cash');

        $dis['status'] = 2;
        //总金额
        $data['total_cash'] = $comm_model->where($dis)->where('type','in',[2,5,6,13])->sum('cash');

        $data['cash'] = $this->admin_info['cash'];

        $data['unrecorded_cash'] = round($data['unrecorded_cash'],2);

        $data['total_cash']      = round($data['total_cash'],2);

        $data['cash']            = round($data['cash'],2);
        //通知
        $notice_model = new NoticeList();
        //列表
        $data = $notice_model->indexOrderNotice($data,$this->_uniacid,$this->admin_info['admin_arr']);

        $config = getConfigSettingArr($this->_uniacid,['admin_reseller_poster','agent_sub_img']);

        $data = array_merge($data,$config);

        $data['reseller_auth'] = getFxStatus($this->_uniacid)==0?0:$this->admin_info['reseller_auth'];

        $data['agent_name']    = $this->admin_info['agent_name'];

        return $this->success($data);

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-15 14:43
     * @功能说明:列表
     */
    public function orderList(){

        $input = $this->_param;

        $is_add = isset($input['is_add'])?$input['is_add']:0;

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['is_add','=',$is_add];

        $dis[] = ['admin_id','in',$this->admin_info['admin_arr']];

        if(!empty($input['pay_type'])){

            $dis[] = ['pay_type','=',$input['pay_type']];

        }else{

            $dis[] = ['pay_time','>',0];

            $dis[] = ['pay_type','not in',[8]];
        }
        //订单号搜索
        if(!empty($input['order_code'])){

            $dis[] = ['order_code','like','%'.$input['order_code'].'%'];
        }

        $data = $this->model->where($dis)->field('id,material_price,coach_id,store_id,is_comment,order_code,true_car_price,true_service_price,pay_type,pay_price,start_time,create_time,user_id,end_time,add_pid,is_add')->order('id desc')->paginate(10)->toArray();

        if(!empty($data['data'])){

            $order_data_model = new OrderData();

            $order_goods_model= new OrderGoods();

            $abn_model = new OrderList();

            foreach ($data['data'] as &$v){

                $v['create_time'] = date('Y-m-d H:i:s',$v['create_time']);

                $v['start_time']  = date('Y-m-d H:i',$v['start_time']);

                $v['end_time']    = date('Y-m-d H:i',$v['end_time']);

                $v['can_refund_price'] = $this->model->getOrderRefundPrice($v);
                //加钟流程
                $v['add_flow_path'] = $order_data_model->where(['order_id'=>$v['id']])->value('add_flow_path');

                if($v['is_add']==0){

                    $v['add_service'] = $order_goods_model->getAddOrderGoods($v['id']);
                }
                //后台是否可以申请退款
                if($v['is_add']==0){

                    $v['admin_apply_refund'] = $this->model->orderCanRefund($v['id']);
                }
                //异常订单标示
                $v['abn_order_id']= $abn_model->where(['order_id'=>$v['id']])->value('id');
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

        $arr = ['create_time','pay_time','serout_time','arrive_time','receiving_time','start_service_time','order_end_time','coach_refund_time','sign_time'];

        foreach ($arr as $value){

            $data[$value] = !empty($data[$value])&&$data[$value]>1?date('Y-m-d H:i:s',$data[$value]):0;
        }

        $data['start_time'] = date('Y-m-d H:i',$data['start_time']).'-'.date('H:i',$data['end_time']);

        $data['can_refund_price'] = $this->model->getOrderRefundPrice($data);

        $data['phone_encryption'] = $this->admin_info['phone_encryption'];

        $order_goods_model = new OrderGoods();

        if($data['is_add']==0){

            $data['add_service'] = $order_goods_model->getAddOrderGoods($data['id']);
        }
        //后台是否可以申请退款
        if($data['is_add']==0){

            $data['admin_apply_refund'] = $this->model->orderCanRefund($data['id']);
        }

        $data['order_auth'] = in_array($data['admin_id'],$this->admin_info['admin_arr'])?1:0;

        $abn_model = new OrderList();
        //异常订单标示
        $data['abn_order_id'] = $abn_model->where(['order_id'=>$data['id']])->value('id');

        if(!empty($data['admin_id'])){

            $data['delegated_coach'] = $admin_model->where(['id'=>$data['admin_id']])->value('delegated_coach');
        }else{
            $data['delegated_coach'] = 1;
        }

        $log_model = new OrderLog();
        //那些流程是手动打卡
        $data['map_type'] = $log_model->where(['order_id'=>$data['id'],'map_type'=>1])->column('pay_type');

        $comm_model = new Commission();

        $find = $comm_model->where(['order_id'=>$data['id'],'type'=>13])->count();

        $data['car_admin'] = $find>0?1:0;
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

        $data['phone_encryption'] = $this->admin_info['phone_encryption'];
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

        $dis[] = ['a.type','=',1];
        //订单状态搜索
        if(!empty($input['status'])){

            $dis[] = ['a.status','=',$input['status']];

        }else{

            $dis[] = ['a.status','>',-1];

        }
        $dis[] = ['d.admin_id','in',$this->admin_info['admin_arr']];

        if(!empty($input['order_code'])){

            $dis[] = ['a.order_code','like','%'.$input['order_code'].'%'];
        }

        $is_add = !empty($input['is_add'])?$input['is_add']:0;

        $dis[] = ['a.is_add','=',$is_add];

        $data = $this->refund_order_model->indexAdminDataList($dis);

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-18 09:21
     * @功能说明:拒绝退款
     */
    public function noPassRefund(){

        $input = $this->_input;

        $res = $this->refund_order_model->noPassRefund($input['id'],$this->admin_info['id']);

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

        $res   = $this->refund_order_model->passOrder($input['id'],$input['price'],$this->payConfig($this->_uniacid,$is_app),$this->admin_info['id'],$input['text']);

        if(!empty($res['code'])){

            $this->errorMsg($res['msg']);
        }

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
     * @DataTime: 2021-07-11 22:34
     * @功能说明:技师修改订单信息)
     */
    public function adminUpdateOrder()
    {

        $input = $this->_input;

        $order = $this->model->dataInfo(['id' => $input['order_id']]);

        $update= $this->model->coachOrdertext($input,1);

        $refund_model = new RefundOrder();
        //判断有无申请中的退款订单
        $refund_order = $refund_model->where(['order_id' => $order['id']])->where('status','in',[1,4,5])->count();

        if (!empty($refund_order)) {

            $this->errorMsg('该订单正在申请退款，请先联系平台处理再进行下一步');
        }

        if(!in_array($order['admin_id'],$this->admin_info['admin_arr'])){

            $this->errorMsg('你没有权限');
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

                $this->errorMsg('订单状态错误，请刷新页面');
            }
            //核销加钟订单
            $res = $this->model->hxAddOrder($order,0,1,$this->admin_info['id']);

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

                $res = $refund_model->refundCashV2($this->payConfig($this->_uniacid,$order['app_pay']), $order, $order['pay_price'],$refund_id);

                if (!empty($res['code'])) {

                    decCache($key,1,$this->_uniacid);

                    Db::rollback();

                    $this->errorMsg($res['msg']);

                }

                if (!in_array($res['status'],[2,4])) {

                    decCache($key,1,$this->_uniacid);

                    Db::rollback();

                    $this->errorMsg('退款失败，请重试2');
                }
            }
        }
        $this->model->dataUpdate(['id' => $input['order_id']], $update);
        //到达后车费秒到账
        if($input['type']==5){

            $coach_model = new Coach();

            $coach_model->coachCarPriceAccount($order,$this->payConfig($this->_uniacid,$order['app_pay']));
        }

        $log_model = new OrderLog();

        $log_model->addLog($input['order_id'],$this->_uniacid,$input['type'],$order['pay_type'],1,$this->admin_info['id']);

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

        $change_model = new CoachChangeLog();

        $coach_name = !empty($input['coach_name'])?$input['coach_name']:'';

        $text = !empty($input['text'])?$input['text']:'';

        $phone = !empty($input['mobile'])?$input['mobile']:'';

        $admin_id = !empty($input['admin_id'])?$input['admin_id']:0;

        $res = $change_model->orderChangeCoach($order,$input['coach_id'],$this->admin_info['id'],$admin_id,$coach_name,$text,$phone);

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

        $dis[] = ['admin_id','in',$this->admin_info['admin_arr']];

        if(!empty($input['coach_name'])){

            $dis[] = ['coach_name','like','%'.$input['coach_name'].'%'];
        }
        //门店订单 只能转同门店的技师
        if(!empty($order['store_id'])){

            $dis[] = ['store_id','=',$order['store_id']];
        }

        $list = $coach_model->where($dis)->select()->toArray();

        $log_model = new CoachChangeLog();
        //转派技师时候 获取满足条件的技师 并获取最近的服务时间
        $arr = $log_model->getNearTimeCoach($order,$list);

        $top = !empty($input['type'])&&$input['type']==1?'distance asc,id desc':'near_time asc,id desc';

        $lat = $order['address_info']['lat'];

        $lng = $order['address_info']['lng'];

        $alh = 'ACOS(SIN(('.$lat.' * 3.1415) / 180 ) *SIN((lat * 3.1415) / 180 ) +COS(('.$lat.' * 3.1415) / 180 ) * COS((lat * 3.1415) / 180 ) *COS(('.$lng.' * 3.1415) / 180 - (lng * 3.1415) / 180 ) ) * 6378.137*1000 as distance';

        $data= $coach_model->where('id','in',$arr)->field(['*', $alh])->order($top)->paginate(10)->toArray();

        if(!empty($data['data'])){

            $admin_model = new \app\massage\model\Admin();

            $user_model  = new User();

            foreach ($data['data'] as &$v){

                $v['partner_name'] = $user_model->where(['id'=>$v['partner_id']])->value('nickName');

                $v['near_time'] = date('m-d H:i',$v['near_time']);

                $v['admin_info']= $admin_model->where(['id'=>$v['admin_id']])->field('city_type,agent_name as username')->find();

                $v['price'] = $coach_model->getCoachServicePrice($order,$v['id']);
            }
        }
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
     * @DataTime: 2021-03-24 13:33
     * @功能说明:团长审核提现
     */
    public function applyWallet(){

        $input = $this->_input;

        $key = 'agent_wallets'.$this->admin_info['id'];

        incCache($key,1,$this->_uniacid,30);

        $value = getCache($key,$this->_uniacid);

        if($value!=1){
            //减掉
            delCache($key,$this->_uniacid);

            $this->errorMsg('网络错误，请刷新重试');
        }

        if(empty($input['apply_price'])||$input['apply_price']<0.01){

            delCache($key,$this->_uniacid);

            $this->errorMsg('提现费最低一分');
        }

        $admin_model= new Admin();

        $admin_user = $admin_model->dataInfo(['id'=>$this->admin_info['id']]);
        //服务费
        if($input['apply_price']>$admin_user['cash']){

            delCache($key,$this->_uniacid);

            $this->errorMsg('余额不足');
        }
        //获取税点
        $tax_point = getConfigSetting($this->_uniacid,'tax_point');

        $balance = 100-$tax_point;

        Db::startTrans();

        $admin_water_model = new AdminWater();

        $res = $admin_water_model->updateCash($this->_uniacid,$this->admin_info['id'],$input['apply_price'],2);

        if($res==0){

            Db::rollback();
            //减掉
            delCache($key,$this->_uniacid);

            $this->errorMsg('申请失败');
        }

        $insert = [

            'uniacid'       => $this->_uniacid,

            'user_id'       => $admin_user['user_id'],

            'admin_id'      => $admin_user['admin_pid'],

            'coach_id'      => $admin_user['id'],

            'total_price'   => $input['apply_price'],

            'balance'       => $balance,

            'apply_price'   => round($input['apply_price']*$balance/100,2),

            'service_price' => round( $input['apply_price'] * $tax_point / 100, 2),

            'tax_point'     => $tax_point,

            'code'          => orderCode(),

            'text'          => !empty($input['text'])?$input['text']:'',

            'type'          => 8,

            'apply_transfer' => !empty($input['apply_transfer'])?$input['apply_transfer']:0,

        ];

        $wallet_model = new Wallet();
        //提交审核
        $res = $wallet_model->dataAdd($insert);

        if($res!=1){

            Db::rollback();
            //减掉
            delCache($key,$this->_uniacid);

            $this->errorMsg('申请失败');
        }

        Db::commit();
        //减掉
        delCache($key,$this->_uniacid);

        return $this->success($res);

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-07 18:46
     * @功能说明:提现列表
     */
    public function walletList(){

        $input = $this->_param;

        $dis[] = ['uniacid','=',$this->_uniacid];

        if(!empty($input['code'])){

            $dis[] = ['code','like','%'.$input['code'].'%'];
        }

        if(!empty($input['status'])){

            $dis[] = ['status','=',$input['status']];
        }

        $where = [

            ['user_id' ,'=', $this->admin_info['id']],

            [ 'type'    ,'=',  3]
        ];

        $where1 = [

            ['coach_id' ,'=', $this->admin_info['id']],

            [ 'type'   ,'in',  [7,8,9]]
        ];

        $wallet_model = new Wallet();

        $data = $wallet_model->where($dis)->where(function ($query) use ($where,$where1){
            $query->whereOr([$where,$where1]);
        })->order('id desc')->paginate(10)->toArray();

        $admin_model = new Admin();

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $v['create_time']= date('Y-m-d H:i:s',$v['create_time']);

                $v['coach_name'] = $admin_model->where(['id'=>$v['user_id']])->value('agent_name');
            }
        }
        //累计提现
        $data['extract_total_price'] = $wallet_model->adminCash($this->admin_info['id'],2);

        $data['personal_income_tax_text'] = getConfigSetting($this->_uniacid,'personal_income_tax_text');

        return $this->success($data);

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-12-09 15:18
     * @功能说明:技师获取客户虚拟电话
     */
    public function getVirtualPhone(){

        $input = $this->_input;

        $order_model = new Order();

        $order = $order_model->dataInfo(['id'=>$input['order_id']]);

        $called = new \app\virtual\model\Config();

        $phone = $this->admin_info['phone'];

//        if(in_array($order['pay_type'],[-1,7])){
//
//            return $this->error('接单已结束');
//        }

        if(empty($phone)){

            $res = $order['address_info']['mobile'];
        }else{

            $res = $called->getVirtual($order,1,$phone);
        }

        return $this->success($res);
    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-15 14:43
     * @功能说明:列表
     */
    public function coachList(){

        $input = $this->_param;

        $dis[] = ['uniacid','=',$this->_uniacid];

        if(!empty($input['status'])){

            $dis[] = ['status','=',$input['status']];

        }else{

            $dis[] = ['status','>',-1];

        }

        $dis[] = ['admin_id','in',$this->admin_info['admin_arr']];

        $where = [];

        if(!empty($input['name'])){

            $where[] = ['coach_name','like','%'.$input['name'].'%'];

            $where[] = ['mobile','like','%'.$input['name'].'%'];
        }

        if(!empty($input['is_update'])){

            $dis[] = ['is_update','=',$input['is_update']];
        }

        $coach_model = new Coach();

        $data = $coach_model->dataList($dis,10,$where,'industry_type,is_update,admin_add,id,admin_id,user_id,coach_name,work_img,mobile,create_time,status,auth_status,sh_text,sh_time');

        if(!empty($data['data'])){

            $admin_model = new Admin();

            $user_model  = new User();

            $industry_model = new Type();

            foreach ($data['data'] as &$v){

                $v['create_time'] = date('Y-m-d H:i:s',$v['create_time']);

                $v['sh_time'] =!empty($v['sh_time'])?date('Y-m-d H:i:s',$v['sh_time']):'';

                $admin_info = $admin_model->dataInfo(['id'=>$v['admin_id'],'status'=>1],'agent_name,city_type');

                $v['admin_name'] = !empty($admin_info)?$admin_info['agent_name']:'';

                $v['city_type']  = !empty($admin_info)?$admin_info['city_type']:'';

                $v['nickName']   = $user_model->where(['id'=>$v['user_id']])->value('nickName');

                $v['industry_title'] = $industry_model->where(['id'=>$v['industry_type'],'status'=>1])->value('title');
            }
        }

        $list = [

            1=>'ing',
        ];

        foreach ($list as $k=> $value){

            $dis_s = [];

            $dis_s[] = ['uniacid','=',$this->_uniacid];

            $dis_s[] = ['status','=',$k];

            $dis_s[] = ['admin_id','in',$this->admin_info['admin_arr']];

            $data[$value] = $coach_model->where($dis_s)->count();
        }

        $data['admin_id'] = $this->admin_info['id'];

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-26 10:21
     * @功能说明:佣金信息
     */
    public function commList(){

        $input = $this->_param;

        $comm_model = new Commission();

        $order_model = new Order();

        $refund_model = new RefundOrder();

        $dis[] = ['top_id','=',$this->admin_info['id']];

        $dis[] = ['type','in',[2,5,6,19,20]];

        $dis[] = ['cash','>',0];

        if(!empty($input['status'])){

            $dis[] = ['status','=',$input['status']];

        }else{

            $dis[] = ['status','>',0];
        }
        if(!empty($input['start_time'])){

            $dis[] = ['create_time','between',"{$input['start_time']},{$input['end_time']}"];
        }

        $month = !empty($input['month'])?$input['month']:'';

        if(!empty($month)){

            $firstday = date('Y-m-01', $month);

            $lastday  = date('Y-m-d H:i:s', strtotime("$firstday +1 month")-1);

            $data = $comm_model->where($dis)->whereTime('create_time','<=',$lastday)->order('id desc')->paginate(10)->toArray();

        }else{

            $data = $comm_model->where($dis)->order('id desc')->paginate(10)->toArray();
        }

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $v['my_city_type'] = $v['city_type'];

                $order = $order_model->dataInfo(['id'=>$v['order_id']]);

                $v['coach_info'] = $order['coach_info'];

                $v['order_goods'] = $order['order_goods'];

                $v['coach_cash'] = $order['coach_cash'];

                $v['pay_price'] = $order['pay_price'];

                $v['start_time'] = $order['start_time'];

                $v['month']       = date('Y-m',$v['create_time']);

                $v['start_time']  = date('Y-m-d H:i',$v['start_time']);

                $v['create_time'] = date('Y-m-d H:i',$v['create_time']);
                //代理商佣金信息
                $v['admin_cash_list'] = $comm_model->where(['order_id'=>$v['order_id']])->where('admin_id','in',$this->admin_info['admin_arr'])->where('type','in',[2,5,6])->where('status','>',-1)->field('cash,city_type')->select()->toArray();
                //查询当前月份的佣金
                $v['total_cash'] = $comm_model->where($dis)->whereMonth('create_time',$v['month'])->where('type','in',[2,5,6,19,20])->where(['admin_id'=>$this->admin_info['id']])->sum('cash');

                $v['total_cash'] = round($v['total_cash'],2);

                $v['refund_price'] = $refund_model->where(['order_id'=>$v['order_id'],'status'=>2])->sum('refund_price');
            }
        }

        $data['city_type'] = $this->admin_info['city_type'];

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-06-22 21:30
     * @功能说明:加盟商下拉框
     */
    public function adminSelect(){

        $input = $this->_param;

        $dis = [

            'is_admin' => 0,

            'status'   => 1,

            'uniacid'  => $this->_uniacid,
        ];

        if(!empty($input['agent_coach_auth'])){

            $dis['agent_coach_auth'] = $input['agent_coach_auth'];
        }

        $where[] = ['id','in',$this->admin_info['admin_arr']];

        $admin_model = new Admin();

        $data = $admin_model->where($dis)->where($where)->field('id,username,agent_name')->select()->toArray();

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-04 16:28
     * @功能说明:代理商详情
     */
    public function adminInfoData(){

        $data = $this->admin_info;

        $city_model = new City();

        $admin_model= new Admin();

        $data['city_name'] = $city_model->where(['id'=>$data['city_id']])->value('title');

        $data['sub_data'] = $admin_model->where(['admin_pid'=>$data['id'],'status'=>1])->field('id,username,agent_name,passwd_text,city_id,city_type')->select()->toArray();

        if(!empty($data['sub_data'])){

            foreach ($data['sub_data'] as &$v){

                $v['city_name'] = $city_model->where(['id'=>$v['city_id']])->value('title');
            }
        }

        $where[] = ['id','=',$data['admin_pid']];

        $where[] = ['status','=',1];
        //上级
        $data['top_data'] = $admin_model->dataInfo($where,'id,username,agent_name,passwd_text,city_id,city_type');

        if(!empty($data['top_data'])){

            $data['top_data']['city_name'] = $city_model->where(['id'=>$data['top_data']['city_id']])->value('title');
        }

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-22 13:35
     * @功能说明:申请技师
     */
    public function coachApply(){

        $input = $this->_input;

        $coach_model = new Coach();
        //后台添加
        $input['admin_add'] = 1;

        $res = $coach_model->coachApply($input,$input['user_id'],$this->_uniacid,$this->admin_info['id']);

        if(!empty($res['code'])){

            $this->errorMsg($res['msg']);
        }

        return $this->success($res);
    }






    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-09-22 15:19
     * @功能说明:团长用户列表
     */
    public function coachUserList(){

        $input = $this->_param;

        if(empty($input['nickName'])){

            $where[] = ['id','=',-1];

        }

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['status','in',[0,1,2,3]];

        $coach_model = new Coach();

        $user_id = $coach_model->where($dis)->column('user_id');

        $where1 = [];

        if(!empty($input['nickName'])){

            $where1[] = ['nickName','like','%'.$input['nickName'].'%'];

            $where1[] = ['phone','like','%'.$input['nickName'].'%'];
        }

        $user_model = new User();

        $where[] = ['uniacid','=',$this->_uniacid];

        $where[] = ['id','not in',$user_id];

        $list = $user_model->dataList($where,$input['limit'],$where1,'id,nickName,avatarUrl,phone');

        return $this->success($list);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-15 14:58
     * @功能说明:订单详情
     */
    public function coachInfo(){

        $input = $this->_param;

        $dis = [

            'id' => $input['id']
        ];

        $coach_model = new Coach();

        $data = $coach_model->dataInfo($dis);

        $user_model = new User();

        $city_model = new City();

        $store_model= new StoreList();

        $data['nickName'] = $user_model->where(['id'=>$data['user_id']])->value('nickName');

        $data['city'] = $city_model->where(['id'=>$data['city_id']])->value('title');

        $data['order_num'] = $coach_model->where(['id' => $data['id']])->value('order_num');
        //绑定门店
        if(!empty($data['store_id'])){

            $data['store_name'] = $store_model->where(['id'=>$data['store_id']])->value('title');
        }

        $record_model= new FddAgreementRecord();

        $dis = [

            'user_id' => $data['user_id'],

            'status' => 3,

            'admin_id'=> $data['admin_id']
        ];
        //法大大合同
        $data['fdd_agreement'] = $record_model->where($dis)->field('id,download_url,viewpdf_url,end_time')->order('id desc')->find();

        $data['address'] = getCoachAddress($data['lng'],$data['lat'],$data['uniacid'],$data['id']);

        $industry_model = new Type();

        $data['industry_info'] = $industry_model->where(['id'=>$data['industry_type'],'status'=>1])->find();
        //绑定门店(新)
        $data['store'] = StoreCoach::getStoreList($data['id']);

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-05 13:52
     * @功能说明:代理商绑定码
     */
    public function agentInviteQr(){

        $input = $this->_param;

        $key = 'channel_qr'.$this->admin_info['id'].'-'.$this->is_app.'-'.$input['type'];

        $qr  = getCache($key,$this->_uniacid);

        if(empty($qr)){
            //小程序
            if($this->is_app==0){

                $user_model = new User();

                $input['page'] = $input['type']==1?'user/pages/channel/apply':'user/pages/salesman/apply';

                $input['admin_id'] = $this->admin_info['id'];
                //获取二维码
                $qr = $user_model->orderQr($input,$this->_uniacid);

            }else{

                if($input['type']==1){

                    $page = 'https://'.$_SERVER['HTTP_HOST'].'/h5/#/user/pages/channel/apply?admin_id='.$this->admin_info['id'];

                }else{

                    $page = 'https://'.$_SERVER['HTTP_HOST'].'/h5/#/user/pages/salesman/apply?admin_id='.$this->admin_info['id'];
                }

                $qr = base64ToPng(getCode($this->_uniacid,$page));
            }

            setCache($key,$qr,86400,$this->_uniacid);
        }

        $qr = !empty($qr)?$qr:'https://'.$_SERVER['HTTP_HOST'].'/favicon.ico';

        return $this->success($qr);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-03 00:15
     * @功能说明:编辑技师
     */
    public function coachDataUpdate(){

        $input = $this->_input;

        $coach_model = new Coach();

        if(!empty($input['user_id'])){

            $cap_dis[] = ['user_id','=',$input['user_id']];

            $cap_dis[] = ['status','>',-1];

            if(!empty($input['id'])){

                $cap_dis[] = ['id','<>',$input['id']];

            }

            $cap_info = $coach_model->dataInfo($cap_dis);

            if(empty($input['id'])&&!empty($cap_info)&&in_array($cap_info['status'],[1,2,3])){

                $this->errorMsg('已经申请过技师了，');
            }

        }else{

            $wehre[] = ['mobile','=',$input['mobile']];

            $wehre[] = ['status','>',-1];

            if(!empty($input['id'])){

                $wehre[] = ['id','<>',$input['id']];

            }

            $find = $coach_model->where($wehre)->find();

            if(!empty($find)){

                $this->errorMsg('该电话号码已经注册技师');
            }
        }

        $input['id_card']  = !empty($input['id_card'])?implode(',',$input['id_card']):'';

        $input['license']  = !empty($input['license'])?implode(',',$input['license']):'';

        $input['self_img'] = !empty($input['self_img'])?implode(',',$input['self_img']):'';
        //同步技师的免出行配置
        $input = $coach_model->synCarConfig($input);

        $res = $coach_model->dataUpdate(['id'=>$input['id']],$input);

        return $this->success($res);

    }









    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-23 09:59
     * @功能说明:代理商修改技师信息
     */
    public function coachUpdateAdmin(){

        $input = $this->_input;

        $dis = [

            'id' => $input['id']
        ];

        $coach_model = new Coach();

        $coach = $coach_model->dataInfo($dis);

        $input['uniacid'] = $this->_uniacid;

        $input['user_id'] = !empty($input['user_id'])?$input['user_id']:$coach['user_id'];

        if (isset($input['id'])) {

            unset($input['id']);
        }

        if (!empty($input['id_card'])) {

            $input['id_card'] = implode(',', $input['id_card']);
        }

        if (!empty($input['license'])) {

            $input['license'] = implode(',', $input['license']);
        }

        if (!empty($input['self_img'])) {

            $input['self_img'] = implode(',', $input['self_img']);
        }
        if (isset($input['admin_id'])) {

            unset($input['admin_id']);
        }

        if (isset($input['partner_id'])) {

            unset($input['partner_id']);
        }

        if(!empty($input['short_code'])){

            $short_code = getCache($input['mobile'],$this->_uniacid);
            //验证码验证手机号
            if($input['short_code']!=$short_code){

                return $this->error('验证码错误');
            }

            unset($input['short_code']);

            setCache($input['mobile'],'',99,$this->_uniacid);
        }
        if(isset($input['store'])){

            if($coach['auth_status']!=2){

                StoreCoach::where(['coach_id'=>$coach['id']])->delete();
            }

            $store = $input['store'];

            unset($input['store']);
        }
        //重新审核
        if($coach['auth_status']==2){

            $input['coach_id'] = $coach['id'];

            $update_model = new CoachUpdate();

            $input['status'] = 1;

            $update_model->dataUpdate(['coach_id'=>$coach['id'],'status'=>1],['status'=>-1]);

            $input['create_user'] = $this->admin_info['id'];

            $update_model->dataAdd($input);

            $update_id = $update_model->getLastInsID();

            $res = $coach_model->dataUpdate($dis, ['is_update' => 1]);

            if(!empty($store)){

                foreach ($store as $key=>$value){

                    $store_insert[$key] = [

                        'uniacid' => $this->_uniacid,

                        'store_id'=> $value,

                        'coach_id'=> $coach['id'],

                        'update_id'=> $update_id,
                    ];
                }

                StoreCoachUpdate::createAll($store_insert);
            }
        }else{
            //被驳回
            if($coach['status']==4){

                $input['status'] = 1;
            }

            $res = $coach_model->dataUpdate($dis,$input);

            if(!empty($store)){

                foreach ($store as $key=>$value){

                    $store_insert[$key] = [

                        'uniacid' => $this->_uniacid,

                        'store_id'=> $value,

                        'coach_id'=> $coach['id'],
                    ];
                }
                StoreCoach::createAll($store_insert);
            }
        }

        return $this->success($res);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-28 15:48
     * @功能说明:门店下拉框
     */
    public function storeSelect(){

        $input = $this->_param;

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['status','=',1];

        if(!empty($input['title'])){

            $dis[] = ['title','like','%'.$input['title'].'%'];

        }

        $dis[] = ['admin_id','=',$this->admin_info['id']];

        $store_model = new \app\store\model\StoreList();

        $data = $store_model->where($dis)->select()->toArray();

        return $this->success($data);
    }




    /**\
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

        foreach ($input['list'] as $value){

            $update = [

                'goods_price'    => $value['goods_price'],

                'material_price' => $value['material_price'],

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

        $this->refund_order_model->dataUpdate(['id'=>$input['order_id']],['version'=>2]);

        $res = $this->refund_order_model->passOrder($input['order_id'],$input['price'],$this->payConfig($this->_uniacid,$is_app),$this->admin_info['id'],$input['text']);

        if(!empty($res['code'])){

            $this->errorMsg($res['msg']);
        }

        $this->refund_order_model->dataUpdate(['id'=>$input['order_id']],['refund_car_price'=>$input['car_price'],'refund_service_price'=>$goods_price,'refund_material_price'=>$material_price]);

        return $this->success($res);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-12-04 16:34
     * @功能说明:可以退款的商品
     */
    public function canRefundOrderInfo(){

        $input = $this->_param;

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
            ->field('b.id as order_id,b.start_service_time,b.pay_type,b.car_price,b.discount,b.is_add,b.pay_model,b.true_car_price,b.start_time,b.end_time,b.true_service_price,b.material_price')
            ->group('b.id')
            ->order('b.is_add,b.id desc')
            ->select()
            ->toArray();

        if(empty($order)){

            $this->errorMsg('订单状态错误1');
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

                $order = $this->model->dataInfo(['id'=>$value['order_id']]);

                if(empty($order)){
                    Db::rollback();
                    decCache($key,1,$this->_uniacid);
                    $this->errorMsg('订单未找到');
                }

                if(empty($value['list'])){
                    Db::rollback();
                    decCache($key,1,$this->_uniacid);

                    $this->errorMsg('请选择商品');

                }

                $can_refund_num = array_sum(array_column($order['order_goods'],'can_refund_num'));
                if($can_refund_num<=0){

                    $this->errorMsg('数量已退完');
                }

                $add_order = $this->model->where(['add_pid'=>$order['id']])->where('pay_type','>',1)->count();

                if($add_order>0){
                    Db::rollback();
                    decCache($key,1,$this->_uniacid);
                    $this->errorMsg('请先申请加钟订单退款');
                }

                if($order['pay_type']==7){
                    Db::rollback();
                    decCache($key,1,$this->_uniacid);

                    $this->errorMsg('核销后不能退款');
                }

                $car_price = !empty($value['car_price'])?$value['car_price']:0;

                $refund_empty_cash = isset($value['refund_empty_cash'])?$value['refund_empty_cash']:0;

                $apply_empty_cash  = isset($value['apply_empty_cash'])?$value['apply_empty_cash']:0;

                $comm_balance      = isset($value['comm_balance'])?$value['comm_balance']:0;
                //申请退款
                $res = $refund_model->applyRefundAdmin($order,$value['list'],$car_price,$this->admin_info['id'],1,$refund_empty_cash,$apply_empty_cash,$comm_balance);

                if(!empty($res['code'])){
                    Db::rollback();
                    decCache($key,1,$this->_uniacid);

                    $this->errorMsg($res['msg']);
                }

                $refund_order = $this->refund_order_model->dataInfo(['id'=>$res]);

                $res = $this->refund_order_model->passOrder($res,$refund_order['apply_price'],$this->payConfig($this->_uniacid,$order['app_pay']),$this->admin_info['id']);

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
     * @DataTime: 2022-08-30 14:18
     * @功能说明 代理商邀请分销员码
     */
    public function agentInvresellerQr(){

        $input = $this->_param;

        if($this->admin_info['reseller_auth']==0){

            $this->errorMsg('你没有该权限');

        }

        $key = 'agentInvresellerQr'.$this->admin_info['id'];

        $qr = getCache($key,$this->_uniacid);

        if(empty($qr)){
            //小程序
            if($this->is_app==0){

                $input['page'] = 'user/pages/distribution/apply';

                $input['admin_id'] = $this->admin_info['id'];

                $user_model = new User();
                //获取二维码
                $qr = $user_model->orderQr($input,$this->_uniacid);

            }else{

                $page = 'https://'.$_SERVER['HTTP_HOST'].'/h5/#/user/pages/distribution/apply?admin_id='.$this->admin_info['id'];

                $qr = base64ToPng(getCode($this->_uniacid,$page));

            }

            setCache($key,$qr,86400,$this->_uniacid);
        }

        $qr = !empty($qr)?$qr:'https://'.$_SERVER['HTTP_HOST'].'/favicon.ico';

        return $this->success($qr);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-08-30 14:18
     * @功能说明 代理商邀请分销员码
     */
    public function agentInvAgentQr(){

        $input = $this->_param;

        if($this->admin_info['sub_agent_auth']==0){

            $this->errorMsg('你没有该权限');
        }

        $key = 'agentInvAgentQr'.$this->admin_info['id'];

        $qr = getCache($key,$this->_uniacid);

        if(empty($qr)){
            //小程序
            if($this->is_app==0){

                $input['page'] = '/agent/pages/apply';

                $input['admin_id'] = $this->admin_info['id'];

                $user_model = new User();
                //获取二维码
                $qr = $user_model->orderQr($input,$this->_uniacid);

            }else{

                $page = 'https://'.$_SERVER['HTTP_HOST'].'/h5/#/agent/pages/apply?admin_id='.$this->admin_info['id'];

                $qr = base64ToPng(getCode($this->_uniacid,$page));
            }

            setCache($key,$qr,8640000,$this->_uniacid);
        }

        $qr = !empty($qr)?$qr:'https://'.$_SERVER['HTTP_HOST'].'/favicon.ico';

        return $this->success($qr);
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-12-14 13:37
     * @功能说明:异常订单详情 异常订单标示
     */
    public function abnOrderInfo(){

        $input = $this->_param;

        $order_model = new OrderList();

        $handle_model  = new OrderInfoHandle();

        $data = $order_model->dataInfo(['id'=>$input['id']]);

        $data = $order_model->getOrderResult($data,1);
        //扣款时间
        $data['deduct_time'] = $handle_model->where(['order_id'=>$data['id']])->where('status','>',1)->where('deduct_cash','>',0)->value('create_time');;

        $data['deduct_time'] = !empty($data['deduct_time'])?date('Y-m-d H:i:s',$data['deduct_time']):'';

        $arr['info']    = $data;

        return $this->success($arr);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-05-06 19:00
     * @功能说明:代理商充值金额
     */
    public function rechargeCash(){

        $input = $this->_param;

        if($this->admin_info['is_admin']==3){

            $admin_id = $this->admin_info['admin_id'];
        }else{

            $admin_id = $this->admin_info['id'];
        }

        $order_insert = [

            'uniacid' => $this->_uniacid,

            'admin_id'=> $admin_id,

            'create_user_id'=> $this->admin_info['id'],

            'cash'    => $input['cash'],

            'pay_model'    => $input['pay_model'],

            'order_code' => orderCode(),
        ];

        $recharge_model = new RechargeList();

        $res = $recharge_model->dataAdd($order_insert);

        if($res==0){

            $this->errorMsg('充值失败');
        }

        $order_id = $recharge_model->getLastInsID();

        if ($input['pay_model']==3){

            $pay_model = new PayModel($this->payConfig());

            $jsApiParameters = $pay_model->aliPay($order_insert['order_code'],$order_insert['cash'],'AgentRecharge',5,['openid'=>$this->getUserInfo()['openid'],'uniacid'=>$this->_uniacid,'type' => 'AgentRecharge' , 'out_trade_no' => $order_insert['order_code'],'order_id'=>(string)$order_id]);

            $arr['pay_list']= $jsApiParameters;

            $arr['order_code']= $order_insert['order_code'];

            $arr['order_id']= $order_id;

        }else{
            //微信支付
            $pay_controller = new \app\shop\controller\IndexWxPay($this->app);
            //支付
            $jsApiParameters= $pay_controller->createWeixinPay($this->payConfig(),$this->getUserInfo()['openid'],$this->_uniacid,"AgentRecharge",['type' => 'AgentRecharge' , 'out_trade_no' => $order_insert['order_code'],'order_id'=>(string)$order_id],$order_insert['cash']);

            $arr['pay_list']= $jsApiParameters;

            $arr['order_id']= $order_id;
        }

        return $this->success($arr);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-05-07 11:12
     * @功能说明:充值记录
     */
    public function rechargeList(){

        $input = $this->_param;

        $recharge_model = new RechargeList();

        $dis[] = ['pay_type','=',2];

        $dis[] = ['create_user_id','=',$this->admin_info['id']];

        if(!empty($input['start_time'])){

            $dis[] = ['pay_time','between',"{$input['start_time']},{$input['end_time']}"];
        }

        $data = $recharge_model->dataList($dis,10);

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $v['pay_time'] = date('Y-m-d H:i:s',$v['pay_time']);
            }
        }

        $data['total_recharge_cash'] = $recharge_model->where($dis)->sum('cash');

        return $this->success($data);
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-20 13:59
     * @功能说明:技师佣金修改记录
     */
    public function updateCoachCashList(){

        $input = $this->_param;

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['status','=',1];

        if(!empty($input['start_time'])&&!empty($input['end_time'])){

            $dis[] = ['create_time','between',"{$input['start_time']},{$input['end_time']}"];
        }

        $dis[] = ['coach_id','=',$this->admin_info['id']];

        $dis[] = ['type','=',3];

        $dis[] = ['admin_type','not in',[-2,-1]];

        $record_model = new CashUpdateRecord();

        if(!empty($input['name'])){

            $id = $record_model->getDataByTitle($input['name']);

            $dis[] = ['id','in',$id];
        }

        $admin_model = new \app\massage\model\Admin();

        $data = $record_model->dataList($dis,10);

        $comm_model = new Commission();

        $coach_model= new Coach();

        $change_log_model = new CoachChangeLog();

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $v['create_time'] = date('Y-m-d H:i:s',$v['create_time']);

                $v['create_user'] = $admin_model->where(['id'=>$v['create_user']])->value('username');

                if(!empty($v['admin_update_id'])){

                    $v['admin_update_name'] = $record_model->getUpdateObjTitle($v['admin_update_id'],$v['admin_type']);
                }
                //技师分摊车费
                if($v['type']==3&&$v['admin_type']==9){

                    $order_id = $comm_model->where(['id'=>$v['info_id']])->value('order_id');

                    $car_record = $comm_model->where(['order_id'=>$order_id])->where('type','in',[8,13])->find();

                    if(!empty($car_record)&&$car_record->type==8){

                        if(!empty($car_record->top_id)){

                            $v['admin_update_name'] = $coach_model->where(['id'=>$car_record->top_id])->value('coach_name');
                        }else{

                            $v['admin_update_name'] = $change_log_model->where(['order_id'=>$order_id])->order('id desc')->value('now_coach_name');

                        }

                    }elseif (!empty($car_record)&&$car_record->type==13){

                        $v['admin_update_name'] = $admin_model->where(['id'=>$car_record->top_id])->value('agent_name');
                    }
                }

                $v['user_name'] = $record_model->getUpdateObjTitle($v['coach_id'],$v['type']);
            }
        }

        return $this->success($data);
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-06-15 15:48
     * @功能说明:获取
     */
    public function getCity(){

        $input = $this->_param;

        $city_model = new City();

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['status','=',1];

        $agent_update_city = getConfigSetting($this->_uniacid, 'agent_update_city');

        if ($agent_update_city == 0) {

            if ($this->admin_info['city_type'] == 3) {

                $dis[] = ['pid', '=', $this->admin_info['city_id']];

            } elseif ($this->admin_info['city_type'] == 1) {

                $dis[] = ['id', '=', $this->admin_info['city_id']];
            } else {

                $city = $city_model->dataInfo(['id' => $this->admin_info['city_id']]);

                if (!empty($city)) {

                    if ($city['is_city'] == 1) {

                        $dis[] = ['id', '=', $this->admin_info['city_id']];
                    } else {

                        $dis[] = ['id', '=', $city['pid']];
                    }
                }
            }
        }

        $mapor = [

            'city_type' => 1,

            'is_city'   => 1

        ];

        $data = $city_model->where($dis)->where(function ($query) use ($mapor){
            $query->whereOr($mapor);
        })->field(['id,title,lat,lng'])->order('id desc')->select()->toArray();

        return $this->success($data);
    }



    /**
     * @author chenniang
     * @DataTime: 2024-07-15 11:59
     * @功能说明:代理商车费配置详情
     */
    public function adminCarCashInfo(){

        $input = $this->_param;

        $config_model = new AdminConfig();

        if(empty($input['admin_id'])){

            if($this->admin_info['is_admin']!=0){

                $this->errorMsg('数据错误');
            }

            $admin_id = $this->admin_info['id'];
        }else{

            $admin_id = $input['admin_id'];
        }

        $dis = [

            'uniacid' => $this->_uniacid,

            'admin_id'=> $admin_id
        ];

        $data = $config_model->dataInfo($dis);

        return $this->success($data);
    }


    /**
     * @author chenniang
     * @DataTime: 2024-10-15 16:58
     * @功能说明:门店概览
     */
    public function storeDataList(){

        $input = $this->_param;

        $store_model = new \app\store\model\StoreList();

        $order_model = new Order();

        $refund_model= new RefundOrder();

        $store_coach_model = new StoreCoach();

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['status','>',-1];

        $dis[] = ['auth_status','=',2];

        $dis[] = ['admin_id','=',$this->admin_info['id']];

        $data = $store_model->dataList($dis,10);

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $dis = [

                    'store_id' => $v['id']
                ];

                $today_pay_price = $order_model->where($dis)->where('pay_time','>',0)->whereTime('create_time','today')->sum('pay_price');

                $total_pay_price = $order_model->where($dis)->where('pay_time','>',0)->sum('pay_price');

                $today_refund_price = $refund_model->alias('a')
                                      ->join('massage_service_order_list b','a.order_id = b.id')
                                      ->where(['b.store_id'=>$v['id'],'a.status'=>2])
                                      ->whereTime('b.create_time','today')
                                      ->group('a.id')
                                      ->sum('a.refund_price');

                $total_refund_price = $refund_model->alias('a')
                                      ->join('massage_service_order_list b','a.order_id = b.id')
                                      ->where(['b.store_id'=>$v['id'],'a.status'=>2])
                                      ->group('a.id')
                                      ->sum('a.refund_price');
                //今日营收
                $v['today_pay_price'] = round($today_pay_price-$today_refund_price,2);
                //累计营收
                $v['total_pay_price'] = round($total_pay_price-$total_refund_price,2);
                //技师数量
                $v['coach_num'] = $store_coach_model->alias('a')
                                  ->join('massage_service_coach_list b','a.coach_id = b.id')
                                  ->where(['b.status'=>2,'a.store_id'=>$v['id']])
                                  ->group('b.id')
                                  ->count();

                $v['work_status'] = $store_model->workStatus($v);
            }
        }
        return $this->success($data);
    }


    /**
     * @param $store_id
     * @功能说明:校验该账号绑定的门店
     * @author chenniang
     * @DataTime: 2024-10-15 15:19
     */
    public function adminStoreId($store_id,$admin_id){

        $store_model = new \app\store\model\StoreList();

        $dis = [

            'admin_id' => $admin_id,

            'auth_status'=> 2
        ];

        if(!empty($store_id)){

            $dis['id'] = $store_id;
        }

        $store = $store_model->alias('a')
            ->where($dis)
            ->where('status','>',-1)
            ->field('title,id as store_id,status')
            ->order('id')
            ->find();

        if(empty($store)&&!empty($store_id)){

            unset($dis['id']);

            $store = $store_model->alias('a')
                ->where($dis)
                ->where('status','>',-1)
                ->field('title,id as store_id,status')
                ->order('id')
                ->find();
        }

        return !empty($store)?$store->toArray():[];
    }


    /**
     * @author chenniang
     * @DataTime: 2024-10-15 15:41
     * @功能说明:选择门店
     */
    public function selectStore(){

        $input = $this->_input;

        $admin_model = new Admin();

        $res = $admin_model->dataUpdate(['id'=>$this->admin_info['id']],['store_id'=>$input['store_id']]);

        return $this->success($res);
    }



    /**
     * @author chenniang
     * @DataTime: 2024-10-15 14:18
     * @功能说明:核销线下优惠券
     */
    public function hxCoupon(){

        $input = $this->_input;

        $coupon_model = new CouponRecord();

        $coupon = $coupon_model->dataInfo(['id'=>$input['id']]);

        if(empty($coupon)){

            $this->errorMsg('卡券已被核销');
        }

        if($coupon['status']==2){

            $this->errorMsg('卡券已被核销');
        }

        if($coupon['status']==4){

            $this->errorMsg('卡券已被作废');
        }

        if($coupon['status']!=1){

            $this->errorMsg('卡券已被核销');
        }

        if($coupon['use_scene']!=1){

            $this->errorMsg('只有线下券才能核销');
        }

        if($coupon['start_time']>time()){

            $this->errorMsg('未到卡券核销日期,核销日期'.date('Y-m-d H:i:s',$coupon['start_time']));
        }

        if($coupon['end_time']<time()){

            $this->errorMsg('卡券已过期,过期时间'.date('Y-m-d H:i:s',$coupon['end_time']));
        }

        if(empty($this->admin_info['store_info'])){

            $this->errorMsg('请先选择门店');
        }

        $store_model = new \app\store\model\StoreList();

        $dis = [

            'b.coupon_id' => $input['id'],

            'b.type' => 1
        ];

        $list =  $store_model->alias('a')
            ->join('massage_service_coupon_store b','b.store_id = a.id')
            ->where($dis)
            ->where('a.status','>',-1)
            ->field('a.id,a.title,b.store_id,a.admin_id')
            ->group('a.id')
            ->order('a.id desc')
            ->select()
            ->toArray();

        $store_arr = array_column($list,'id');

        if(!in_array($this->admin_info['store_info']['store_id'],$store_arr)){

            $store_name = implode(',',array_column($list,'title'));

            $this->errorMsg('该卡券不支持当前门店,卡券支持门店'.$store_name);
        }

        $res = $coupon_model->couponUse($input['id'],0,$this->admin_info['store_info']['store_id'],$this->_user['id'],$this->admin_info['id']);

        if($res==0){

            $this->errorMsg('核销失败');
        }

        return $this->success($res);
    }


    /**
     * @author chenniang
     * @DataTime: 2024-10-15 18:36
     * @功能说明:卡券核销记录
     */
    public function couponHxRecord(){

        $input = $this->_param;

        $dis[] = ['a.hx_admin_id','=',$this->admin_info['id']];

        $dis[] = ['a.use_scene','=',1];

        $dis[] = ['a.status','=',2];

        if(!empty($input['store_name'])){

            $dis[] = ['b.title','like','%'.$input['store_name'].'%'];
        }

        if(!empty($input['start_time'])&&!empty($input['end_time'])){

            $dis[] = ['a.use_time','between',"{$input['start_time']},{$input['end_time']}"];
        }

        $coupon_model = new CouponRecord();

        $user_model = new User();

        $data = $coupon_model->alias('a')
                ->join('massage_store_list b','a.hx_store_id = b.id','left')
                ->where($dis)
                ->field('a.*,b.title as store_name')
                ->group('a.id')
                ->order('a.use_time desc,a.id desc')
                ->paginate(10)
                ->toArray();

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $v['hx_user_name'] = $user_model->where(['id'=>$v['hx_user_id']])->value('nickName');

                $v['use_time'] = date('Y.m.d H:i:s',$v['use_time']);
            }
        }

        $data['discount_cash'] = $coupon_model->alias('a')
                ->join('massage_store_list b','a.hx_store_id = b.id','left')
                ->where($dis)
                ->group('a.id')
                ->sum('discount');

        $data['discount_cash'] = round($data['discount_cash'],2);

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-28 15:48
     * @功能说明:门店下拉框
     */
    public function storeList(){

        $input = $this->_param;

        $dis[] = ['uniacid','=',$this->_uniacid];

        if(isset($input['status'])){

            $dis[] = ['status','=',$input['status']];
        }else{

            $dis[] = ['status','>',-1];
        }

        $dis[] = ['auth_status','=',2];

        if(!empty($input['title'])){

            $dis[] = ['title','like','%'.$input['title'].'%'];
        }

        $dis[] = ['admin_id','=',$this->admin_info['id']];

        $store_model = new \app\store\model\StoreList();

        $data = $store_model->where($dis)->order('status desc,id desc')->paginate(10)->toArray();

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $v['work_status'] = $store_model->workStatus($v);
            }
        }

        return $this->success($data);
    }



    /**
     * @author chenniang
     * @DataTime: 2024-10-23 15:22
     * @功能说明:卡券详情
     */
    public function hxCouponInfo(){

        $input = $this->_param;

        $coupon_record_model = new CouponRecord();

        $dis = [

            'id' => $input['id']
        ];

        $data = $coupon_record_model->dataInfo($dis);

        $data['end_time'] = date('Y.m.d H:i',$data['end_time']);

        $data['use_time'] = date('Y.m.d H:i',$data['use_time']);

        $data['is_auth'] = 1;

        $data['is_pop'] = 0;

        if($data['use_scene']==1){

            $store_model = new \app\store\model\StoreList();

            $dis = [

                'b.coupon_id' => $data['id'],

                'b.type' => 1
            ];

            $data['store'] =  $store_model->alias('a')
                ->join('massage_service_coupon_store b','b.store_id = a.id')
                ->where($dis)
                ->where('a.status','=',1)
                ->field('a.id,a.title,b.store_id,a.admin_id,a.cover,a.status')
                ->group('a.id')
                ->order('a.id desc')
                ->select()
                ->toArray();

            $list = $store_model->alias('a')
                ->join('massage_service_coupon_store b','b.store_id = a.id')
                ->where($dis)
                ->where('a.status','=',1)
                ->where('a.admin_id','=',$this->admin_info['id'])
                ->field('a.id,a.title,b.store_id,a.admin_id,a.cover,a.status,a.address')
                ->group('a.id')
                ->order('a.id desc')
                ->select()
                ->toArray();
            //没有门店权限
            if(empty($list)){

                $data['is_auth'] = 0;
            }

            $data['store_select'] = $list;
            //弹窗
            if(empty($this->admin_info['store_info'])||!in_array($this->admin_info['store_info']['store_id'],array_column($list,'id'))){

                $data['is_pop'] = 1;
            }

            $data['store_num'] = count($list);
        }

        $user_model = new User();

        $data['hx_name'] = $user_model->where(['id'=>$this->admin_info['user_id']])->value('nickName');

        $data['hx_store_info'] = $this->admin_info['store_info'];

        $data['group_write_off_auth'] = $this->admin_info['group_write_off_auth'];

        return $this->success($data);
    }


    /**
     * @author chenniang
     * @DataTime: 2024-10-23 15:22
     * @功能说明:卡券核销详情
     */
    public function couponHxRecordInfo(){

        $input = $this->_param;

        $coupon_record_model = new CouponRecord();

        $dis = [

            'id' => $input['id']
        ];

        $data = $coupon_record_model->dataInfo($dis);

        $data['end_time'] = date('Y.m.d H:i',$data['end_time']);

        $data['use_time'] = date('Y.m.d H:i',$data['use_time']);

        $store_model = new \app\store\model\StoreList();

        if($data['use_scene']==1){

            $dis = [

                'b.coupon_id' => $data['id'],

                'b.type' => 1
            ];

            $data['store'] =  $store_model->alias('a')
                ->join('massage_service_coupon_store b','b.store_id = a.id')
                ->where($dis)
                ->where('a.status','=',1)
                ->field('a.id,a.title,b.store_id,a.admin_id,a.cover,a.status')
                ->group('a.id')
                ->order('a.id desc')
                ->select()
                ->toArray();
        }

        $user_model = new User();

        $data['hx_name'] = $user_model->where(['id'=>$data['hx_user_id']])->value('nickName');

        $data['hx_store_info'] = $store_model->where(['id'=>$data['hx_store_id']])->field('id as store_id,title')->find();

        return $this->success($data);
    }




}
