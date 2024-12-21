<?php
namespace app\mobilenode\controller;
use app\abnormalorder\model\OrderInfo;
use app\abnormalorder\model\OrderInfoHandle;
use app\abnormalorder\model\OrderList;
use app\AdminRest;
use app\ApiRest;
use app\balancediscount\model\OrderShare;
use app\dynamic\model\DynamicList;
use app\massage\model\Admin;
use app\massage\model\Coach;
use app\massage\model\CoachChangeLog;
use app\massage\model\CoachLevel;
use app\massage\model\CoachTimeList;
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
use app\massage\model\StoreList;
use app\massage\model\UpOrderList;
use app\massage\model\User;
use app\massage\model\WorkLog;
use app\mobilenode\model\AdminStore;
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


class IndexAdminOrder extends ApiRest
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

            $this->errorMsg('你还没有管理员权限');
        }
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-20 11:44
     * @功能说明:获取当前用户的角色权限
     */
    public function adminInfo(){

        $admin_model = new RoleAdmin();

        $dis = [

            'user_id' => $this->getUserId(),

            'status'  => 1
        ];

        $data = $admin_model->dataInfo($dis);

        if(empty($data)){

            $this->errorMsg('你还没有管理员权限');
        }

        if(!empty($data['admin_id'])){

            $admin_model = new Admin();

            $admin = $admin_model->dataInfo(['id'=>$data['admin_id']]);
            //获取下级的代理商
            $data['admin_arr'] = $admin_model->getAdminId($admin);

            $data['phone_encryption'] = $admin['phone_encryption'];

            $data['agent_coach_auth'] = $admin['agent_coach_auth'];
            //团购券核销权限
            $data['group_write_off_auth'] = $admin['group_write_off_auth'];

            if($data['group_write_off_auth']==1&&!in_array('MarketCoupHxrecord',$data['node'])){

                $data['group_write_off_auth'] = 0;
            }

            $data['store_auth'] = $admin['store_auth'];

            if($data['store_auth']==1){

                $data['store_info'] = $this->adminStoreId($data['store_id'],$data['id'],$data['admin_id']);
            }else{

                $data['store_info'] = [];
            }

        }else{

            $data['phone_encryption'] = 1;

            $data['agent_coach_auth'] = 1;
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

            'is_add'  => 0
        ];

        $where = [];

        if(!empty($this->admin_info['admin_id'])){

            $where[] = ['admin_id','in',$this->admin_info['admin_arr']];
        }
        //订单数据
        foreach ($order_type as $value){

            $data['order_count'][$value] = $this->model->where($dis)->where($where)->where(['pay_type'=>$value])->count();
        }
        //拒单
        $data['refuse_order'][8] = $this->model->where($dis)->where($where)->where(['pay_type'=>8])->count();
        //加钟服务
        $dis['is_add'] = 1;

        $order_type = [2,3,6,7];
        //订单数据
        foreach ($order_type as $value){

            if($value==6){

                $data['add_count'][$value] = $this->model->where($dis)->where($where)->where('pay_type','in',[4,5,6])->count();

            }else{

                $data['add_count'][$value] = $this->model->where($dis)->where($where)->where(['pay_type'=>$value])->count();
            }
        }
        $order_type = [1];
        //加钟退款
        foreach ($order_type as $value){

            $data['add_refund_count'][$value] = $this->refund_order_model->where($dis)->where($where)->where(['status'=>$value])->count();
        }

        $dis['is_add'] = 0;
        //退款
        foreach ($order_type as $value){

            $data['refund_count'][$value] = $this->refund_order_model->where($dis)->where($where)->where(['status'=>$value])->count();
        }
        //菜单权限
        $data['node'] = $this->admin_info['node'];

        $data['agent_coach_auth'] = $this->admin_info['agent_coach_auth'];
        //是否是代理商
        $data['is_agent'] = !empty($this->admin_info['admin_id'])?1:0;

        if(!empty($this->admin_info['admin_id'])){

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

                $data['store_num'] = $this->adminStoreCount();
            }
        }
        //通知
        $notice_model = new NoticeList();
        //列表
        $admin_arr = !empty($this->admin_info['admin_id'])?$this->admin_info['admin_arr']:0;

        $data = $notice_model->indexOrderNotice($data,$this->_uniacid,$admin_arr,$this->admin_info['node']);

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

        if(!empty($this->admin_info['admin_id'])){

            $dis[] = ['admin_id','in',$this->admin_info['admin_arr']];

        }

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

        $data = $this->model->where($dis)->field('id,material_price,coach_id,store_id,true_car_price,is_comment,order_code,true_service_price,pay_type,pay_price,start_time,create_time,user_id,end_time,add_pid,is_add')->order('id desc')->paginate(10)->toArray();

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

        $order_goods_model= new OrderGoods();

        if($data['is_add']==0){

            $data['add_service'] = $order_goods_model->getAddOrderGoods($data['id']);
        }
        //后台是否可以申请退款
        if($data['is_add']==0){

            $data['admin_apply_refund'] = $this->model->orderCanRefund($data['id']);
        }

        if(!empty($this->admin_info['admin_id'])){

            $data['order_auth'] = in_array($data['admin_id'],$this->admin_info['admin_arr'])?1:0;

        }else{

            $data['order_auth'] = 1;
        }

        $abn_model = new OrderList();
        //异常订单标示
        $data['abn_order_id'] = $abn_model->where(['order_id'=>$data['id']])->value('id');

        if(!empty($data['admin_id'])&&!empty($this->admin_info['admin_id'])){

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

        if(!empty($this->admin_info['admin_id'])){

            $dis[] = ['d.admin_id','in',$this->admin_info['admin_arr']];

        }

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

        $res = $this->refund_order_model->noPassRefund($input['id'],$this->admin_info['user_id'],1,3);

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

        $res   = $this->refund_order_model->passOrder($input['id'],$input['price'],$this->payConfig($this->_uniacid,$is_app),$this->admin_info['user_id'],$input['text'],1,3);

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

        $update = $this->model->coachOrdertext($input,1);

        $refund_model = new RefundOrder();
        //判断有无申请中的退款订单
        $refund_order = $refund_model->where(['order_id' => $order['id']])->where('status','in',[1,4,5])->count();

        if (!empty($refund_order)) {

            $this->errorMsg('该订单正在申请退款，请先联系处理再进行下一步');
        }

        if(!empty($this->admin_info['admin_id'])&&!in_array($order['admin_id'],$this->admin_info['admin_arr'])){

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

                Db::rollback();

                $this->errorMsg('订单状态错误，请刷新页面');
            }
            //核销加钟订单
            $res = $this->model->hxAddOrder($order,0,4,$this->getUserId());

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

        $log_model->addLog($input['order_id'],$this->_uniacid,$input['type'],$order['pay_type'],4,$this->getUserId());

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

        if (!empty($refund_order)) {

            $this->errorMsg('该订单正在申请退款，请先处理再进行下一步');
        }

        $success_add_order = $this->model->dataInfo(['add_pid'=>$input['order_id'],'pay_type'=>7]);

        if(!empty($success_add_order)){

            $this->errorMsg('该订单加钟订单已经完成，无法转单');
        }

        $order = $this->model->dataInfo(['id'=>$input['order_id']]);

        $change_model = new CoachChangeLog();

        $coach_name = !empty($input['coach_name'])?$input['coach_name']:'';

        $text     = !empty($input['text'])?$input['text']:'';

        $phone    = !empty($input['mobile'])?$input['mobile']:'';

        $admin_id = !empty($input['admin_id'])?$input['admin_id']:0;

        $res = $change_model->orderChangeCoach($order,$input['coach_id'],$this->_user['id'],$admin_id,$coach_name,$text,$phone,2);

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

        if(!empty($this->admin_info['admin_id'])){

            $dis[] = ['admin_id','in',$this->admin_info['admin_arr']];

        }

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

        $data = $coach_model->where('id','in',$arr)->field(['*', $alh])->order($top)->paginate(10)->toArray();

        if(!empty($data['data'])){

            $admin_model = new \app\massage\model\Admin();

            $user_model  = new User();

            foreach ($data['data'] as &$v){

                $v['partner_name'] = $user_model->where(['id'=>$v['partner_id']])->value('nickName');

                $v['near_time'] = date('m-d H:i',$v['near_time']);

                $v['admin_info']= $admin_model->where(['id'=>$v['admin_id']])->field('city_type,agent_name as username')->find();

                $v['price']     = $coach_model->getCoachServicePrice($order,$v['id']);
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
     * @DataTime: 2022-12-09 15:18
     * @功能说明:技师获取客户虚拟电话
     */
    public function getVirtualPhone(){

        $input = $this->_input;

        $order_model = new Order();

        $order = $order_model->dataInfo(['id'=>$input['order_id']]);

        $called = new \app\virtual\model\Config();

        $phone = $this->admin_info['mobile'];

        if(empty($phone)){

            $res = $order['address_info']['mobile'];

        }else{

            $res = $called->getVirtual($order,1,$phone);
        }

        return $this->success($res);
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

            'uniacid'  => $this->_uniacid
        ];
        if(!empty($input['agent_coach_auth'])){

            $dis['agent_coach_auth'] = $input['agent_coach_auth'];
        }

        $where = [];

        if(!empty($this->admin_info['admin_id'])){

            $where[] = ['id','in',$this->admin_info['admin_arr']];
        }

        $admin_model = new Admin();

        $data = $admin_model->where($dis)->where($where)->field('id,username,agent_name')->select()->toArray();

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

        $this->refund_order_model->dataUpdate(['id'=>$input['order_id']],['version'=>2]);

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

        $res = $this->refund_order_model->passOrder($input['order_id'],$input['price'],$this->payConfig($this->_uniacid,$is_app),$this->admin_info['user_id'],$input['text'],1,3);

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

                $car_price = !empty($value['car_price'])?$value['car_price']:0;

                if(empty($order)){
                    Db::rollback();
                    decCache($key,1,$this->_uniacid);
                    $this->errorMsg('订单未找到');
                }

                $add_order = $this->model->where(['add_pid'=>$order['id']])->where('pay_type','>',1)->count();

                if($add_order>0){
                    Db::rollback();
                    decCache($key,1,$this->_uniacid);
                    $this->errorMsg('请先申请加钟订单退款');
                }

                $can_refund_num = array_sum(array_column($order['order_goods'],'can_refund_num'));

                if($can_refund_num<=0){

                    $this->errorMsg('数量已退完');
                }

                if(empty($value['list'])&&$car_price<=0){
                    Db::rollback();
                    decCache($key,1,$this->_uniacid);

                    $this->errorMsg('请选择商品');
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
                $res = $refund_model->applyRefundAdmin($order,$value['list'],$car_price,$this->admin_info['user_id'],3,$refund_empty_cash,$apply_empty_cash,$comm_balance);

                if(!empty($res['code'])){
                    Db::rollback();
                    decCache($key,1,$this->_uniacid);

                    $this->errorMsg($res['msg']);
                }

                $refund_order = $this->refund_order_model->dataInfo(['id'=>$res]);

                $res = $this->refund_order_model->passOrder($res,$refund_order['apply_price'],$this->payConfig($this->_uniacid,$order['app_pay']),$this->admin_info['user_id'],'',1,3);

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
     * @param $store_id
     * @功能说明:校验该账号绑定的门店
     * @author chenniang
     * @DataTime: 2024-10-15 15:19
     */
    public function adminStoreId($store_id,$user_id,$admin_id){

        $store_model = new \app\store\model\StoreList();

        $dis = [

            'b.admin_id' => $user_id,

            'a.admin_id' => $admin_id,

            'a.auth_status' => 2
        ];

        if(!empty($store_id)){

            $dis['b.store_id'] = $store_id;
        }

        $store = $store_model->alias('a')
            ->join('massage_mobile_role_store b','a.id = b.store_id')
            ->where($dis)
            ->where('a.status','>',-1)
            ->field('a.title,b.store_id,a.status')
            ->order('a.id')
            ->find();

        if(empty($store)&&!empty($store_id)){

            unset($dis['b.store_id']);

            $store = $store_model->alias('a')
                ->join('massage_mobile_role_store b','a.id = b.store_id')
                ->where($dis)
                ->where('a.status','>',-1)
                ->field('a.title,b.store_id,a.status')
                ->order('a.id')
                ->find();
        }

        return !empty($store)?$store->toArray():[];
    }


    /**
     * @author chenniang
     * @DataTime: 2024-10-16 14:40
     * @功能说明:管理员关联的门店数量
     */
    public function adminStoreCount(){

        $store_model = new \app\store\model\StoreList();

        $dis = [

            'b.admin_id' => $this->admin_info['id'],

            'a.admin_id' => $this->admin_info['admin_id'],

            'a.auth_status' => 2
        ];

        $count = $store_model->alias('a')
            ->join('massage_mobile_role_store b','a.id = b.store_id')
            ->where($dis)
            ->where('a.status','>',-1)
            ->group('a.id')
            ->count();

        return $count;
    }

    /**
     * @author chenniang
     * @DataTime: 2024-10-16 14:40
     * @功能说明:管理员关联的门店数量
     */
    public function adminStoreIdArr(){

        $store_model = new \app\store\model\StoreList();

        $dis = [

            'b.admin_id' => $this->admin_info['id'],

            'a.admin_id' => $this->admin_info['admin_id'],

            'a.auth_status' => 2
        ];

        $data = $store_model->alias('a')
            ->join('massage_mobile_role_store b','a.id = b.store_id')
            ->where($dis)
            ->where('a.status','>',-1)
            ->group('a.id')
            ->column('a.id');

        return $data;
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

        $res = $coupon_model->couponUse($input['id'],0,$this->admin_info['store_info']['store_id'],$this->_user['id'],$this->admin_info['admin_id'],$this->admin_info['id']);

        if($res==0){

            $this->errorMsg('核销失败');
        }

        return $this->success($res);
    }


    /**
     * @author chenniang
     * @DataTime: 2024-10-15 15:40
     * @功能说明:可选择的门店下拉框
     */
    public function storeList(){

        $input = $this->_param;

        $store_model = new \app\store\model\StoreList();

        $dis[] = ['b.admin_id','=',$this->admin_info['id']];

        $dis[] = ['a.admin_id','=',$this->admin_info['admin_id']];

        if(!empty($input['title'])){

            $dis[] = ['a.title','like','%'.$input['title'].'%'];
        }

        $data = $store_model->alias('a')
            ->join('massage_mobile_role_store b','a.id = b.store_id')
            ->where($dis)
            ->where('a.status','>',-1)
            ->where('a.auth_status','=',2)
            ->field('a.*')
            ->group('a.id')
            ->order('a.status desc,a.id desc')
            ->paginate(10)
            ->toArray();

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $v['work_status'] = $store_model->workStatus($v);
            }
        }

        return $this->success($data);
    }


    /**
     * @author chenniang
     * @DataTime: 2024-10-15 15:41
     * @功能说明:选择门店
     */
    public function selectStore(){

        $input = $this->_input;

        $admin_model = new RoleAdmin();

        $res = $admin_model->dataUpdate(['id'=>$this->admin_info['id']],['store_id'=>$input['store_id']]);

        return $this->success($res);
    }


    /**
     * @author chenniang
     * @DataTime: 2024-10-15 18:36
     * @功能说明:卡券核销记录
     */
    public function couponHxRecord(){

        $input = $this->_param;

        $dis[] = ['a.hx_role_id','=',$this->admin_info['id']];

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
            ->group('a.use_time desc,a.id desc')
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

        $data['use_time'] = !empty($data['use_time'])?date('Y.m.d H:i',$data['use_time']):'';

        $data['is_auth'] = 1;

        $data['is_pop']  = 0;

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

            $store_id_arr = $this->adminStoreIdArr();

            $list = $store_model->alias('a')
                ->join('massage_service_coupon_store b','b.store_id = a.id')
                ->where($dis)
                ->where('a.id','in',$store_id_arr)
                ->where('a.status','=',1)
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
