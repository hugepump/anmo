<?php
namespace app\massage\controller;
use app\adapay\model\Record;
use app\AdminRest;
use app\baiying\info\PermissionBaiying;
use app\balancediscount\model\OrderList;
use app\coachbroker\model\CoachBroker;
use app\massage\model\Article;

use app\massage\model\ArticleList;
use app\massage\model\BalanceOrder;
use app\massage\model\Cap;
use app\massage\model\ChannelList;
use app\massage\model\ChannelQr;
use app\massage\model\ChannelScanQr;
use app\massage\model\Coach;
use app\massage\model\CoachChangeLog;
use app\massage\model\CoachTimeList;
use app\massage\model\Commission;
use app\massage\model\CommShare;
use app\massage\model\Date;

use app\massage\model\DistributionList;
use app\massage\model\NoPayRecord;
use app\massage\model\NoPayRecordGoods;
use app\massage\model\Order;
use app\massage\model\OrderAddress;
use app\massage\model\OrderGoods;
use app\massage\model\RefundOrder;
use app\massage\model\Salesman;
use app\massage\model\SubData;
use app\massage\model\SubList;
use app\massage\model\User;
use app\massage\model\Wallet;
use app\massage\model\WorkLog;
use app\member\info\PermissionMember;
use app\member\model\Config;
use app\member\model\Level;
use longbingcore\permissions\AdminMenu;
use longbingcore\wxcore\Excel;
use think\App;
use app\massage\model\Order as Model;
use think\facade\Db;


class AdminExcel extends AdminRest
{


    protected $model;

    protected $order_goods_model;

    protected $refund_order_model;

    protected $attendant_name;

    protected $comm_share_model;

    protected $channel_name;

    protected $channel_cate_status;

    protected $channel_status;

    protected $material_text;

    protected $agent_name;

    protected $reseller_name;

    protected $broker_name;

    public function __construct(App $app) {

        parent::__construct($app);

        $this->model = new Model();

        $this->order_goods_model  = new OrderGoods();

        $this->refund_order_model = new RefundOrder();

        $this->comm_share_model   = new CommShare();

        $config = getConfigSettingArr($this->_uniacid,['attendant_name','channel_menu_name','material_text','agent_default_name','reseller_menu_name','broker_menu_name']);

        $this->attendant_name = $config['attendant_name'];

        $this->channel_name   = $config['channel_menu_name'];

        $this->material_text  = $config['material_text'];

        $this->agent_name     = $config['agent_default_name'];

        $this->reseller_name  = $config['reseller_menu_name'];

        $this->broker_name    = $config['broker_menu_name'];

        $auth = AdminMenu::getAuthList((int)$this->_uniacid,['channelcate','channel']);

        $this->channel_cate_status = $auth['channelcate'];

        $this->channel_status = $auth['channel'];
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-15 14:43
     * @功能说明:列表
     */
    public function orderList(){

        $input = $this->_param;

        $dis[] = ['a.uniacid','=',$this->_uniacid];
        //时间搜素
        if(!empty($input['start_time'])&&!empty($input['end_time'])){

            $start_time = $input['start_time'];

            $end_time = $input['end_time'];

            $dis[] = ['a.create_time','between',"$start_time,$end_time"];
        }
        //商品名字搜索
        if(!empty($input['goods_name'])){

            $dis[] = ['c.goods_name','like','%'.$input['goods_name'].'%'];
        }
        //手机号搜索
        if(!empty($input['mobile'])){

            $order_address_model = new OrderAddress();

            $order_address_dis[] = ['mobile','like','%'.$input['mobile'].'%'];

            $order_id = $order_address_model->where($order_address_dis)->column('order_id');

            $dis[] = ['a.id','in',$order_id];
        }

        if($this->_user['is_admin']==0){

            $dis[] = ['a.admin_id','in',$this->admin_arr];
        }
        //合伙人
        if(!empty($input['partner_id'])){

            $dis[] = ['a.partner_id','=',$input['partner_id']];
        }

        if(!empty($input['admin_id'])){

            $dis[] = ['a.admin_id','=',$input['admin_id']];
        }

        if(!empty($input['pay_type'])){
            //订单状态搜索
            $dis[] = ['a.pay_type','=',$input['pay_type']];

        }else{
            //除开待转单
            $dis[] = ['a.pay_type','<>',8];
        }
        $map = [];
        //店铺名字搜索
        if(!empty($input['coach_name'])){

            $map[] = ['b.coach_name','like','%'.$input['coach_name'].'%'];

            $map[] = ['d.now_coach_name','like','%'.$input['coach_name'].'%'];
        }

        if(!empty($input['order_code'])){

            $dis[] = ['a.order_code','like','%'.$input['order_code'].'%'];
        }

        if(!empty($input['transaction_id'])){

            $dis[] = ['a.transaction_id','like','%'.$input['transaction_id'].'%'];
        }

        if(!empty($input['channel_cate_id'])){

            $dis[] = ['e.cate_id','=',$input['channel_cate_id']];
        }

        if(!empty($input['channel_name'])){

            $dis[] = ['e.user_name','like','%'.$input['channel_name'].'%'];
        }

        if(!empty($input['is_channel'])){

            $dis[] = ['a.pay_type','>',1];

            $dis[] = ['a.channel_id','<>',0];

        }
        //渠道码
        if(!empty($input['channel_qr_name'])){

            $qr_model = new ChannelQr();

            $channel_qr_id = $qr_model->getQrID($input['channel_qr_name'],$this->_uniacid);

            $dis[] = ['a.channel_qr_id ','in',$channel_qr_id];
        }
        //是否是加钟
        if(isset($input['is_add'])){

            $dis[] = ['a.is_add','=',$input['is_add']];
        }

        if(!empty($input['is_coach'])){

            if($input['is_coach']==2){

                $dis[] = ['a.coach_id','=',0];
            }else{

                $dis[] = ['a.coach_id','>',0];
            }
        }

        if(!empty($input['is_store'])){

            if($input['is_store']==1){

                $dis[] = ['a.store_id','>',0];
            }else{

                $dis[] = ['a.store_id','=',0];
            }
        }
        $input['is_channel'] = !empty($input['is_channel'])?$input['is_channel']:0;

        $data = $this->model->adminDataSelect($dis,$map,$this->_user['phone_encryption'],$input['is_channel']);

        if(!empty($input['is_channel'])){

            if(!empty($input['is_add'])){

                $name = $this->channel_name.'财务加单-'.date('Y-m-d H:i:s');
                $type = 2;

            }else{

                $name = $this->channel_name.'财务订单-'.date('Y-m-d H:i:s');
                $type = 1;
            }

        }else{

            if(!empty($input['is_add'])&&$input['is_add']==1){

                $name = '加单列表-'.date('Y-m-d H:i:s');
                $type = 3;

            }else{

                $name = '订单列表-'.date('Y-m-d H:i:s');
                $type = 2;
            }
        }
        $attendant_name = $this->attendant_name;
        $header[] = '订单ID';
        $header[] = '服务项目';
        $header[] = '项目价格';
        $header[] = '项目数量';
        $header[] = $this->material_text;
        $header[] = '下单人';
        $header[] = '手机号';
        $header[] = $attendant_name;
        $header[] = $attendant_name.'类型';
        $header[] = '服务方式';
        $header[] = '服务开始时间';

        if(empty($input['is_add'])){

            $header[] = '出行费用';
        }

        $header[] = '服务项目费用';

        if(empty($input['is_add'])){

            $header[] = '实收金额';
        }

        $header[] = '退款金额';

        $input['is_channel'] = $this->channel_cate_status==0?0:$input['is_channel'];
        $header[] = '系统订单号';
        $header[] = '付款订单号';
        $header[] = $this->agent_name;
        if($this->channel_status==true){
            $header[] = $this->channel_name;
            $header[] = '渠道码';
        }
        if(!empty($input['is_channel'])){

            $header[] = '渠道';
        }
        $header[] = '下单时间';
        $header[] = '支付方式';
        $header[] = '状态';
        $new_data = [];

        foreach ($data as $v){

            $info   = array();

            $info[] = $v['id'];

            $info[] = $v['goods_name'];

            $info[] = $v['price'];

            $info[] = $v['num'];

            $info[] = $v['init_material_price'];

            $info[] = $v['user_name'];

            $info[] = $v['mobile'];

            $info[] = $v['coach_name'];

            $info[] = $v['coach_id']>0?'入驻'.$attendant_name:'非入驻'.$attendant_name;

            $info[] = $v['store_id']>0?'到店服务':'上门服务';

            $info[] = date('Y-m-d H:i:s',$v['start_time']);

            if(empty($input['is_add'])) {

                $info[] = $v['car_price'];
            }

            $info[] = $v['init_service_price'];

            if(empty($input['is_add'])) {

                $info[] = $v['pay_price'];
            }

            $info[] = $v['refund_price'];

            $info[] = $v['order_code'];

            $info[] = $v['transaction_id'];

            $info[] = $v['admin_name'];

            if($this->channel_status==true){
                $info[] = $v['channel_name'];
                $info[] = $v['channel_qr_name'];
            }
            if(!empty($input['is_channel'])){

                $info[] = $v['channel'];
            }
            $info[] = date('Y-m-d H:i:s',$v['create_time']);

            $info[] = $this->payModel($v['pay_model']);

            $info[] = $this->orderStatusText($v['pay_type']);

            $new_data[] = $info;
        }

        $excel = new Excel();

        $excel->excelExport($name,$header,$new_data,'',$type);

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-12-26 21:59
     * @功能说明:支付方式
     */
    public function payModel($type){

        switch ($type){

            case 1;

                $text = '微信支付';
                break;

            case 2;

                $text = '余额支付';
                break;
            case 3;

                $text = '支付宝支付';
                break;
            case 4;

                $text = '折扣卡支付';
                break;

            default:

                $text = $type;
                break;

        }

        return $text;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-30 16:32
     * @功能说明:
     */
    public function orderStatusText($status){

        $attendant_name = $this->attendant_name;

        switch ($status){

            case 1:
                return '待支付';

                break;
            case 2:
                return '待接单';

                break;
            case 3:
                return '已接单';

                break;
            case 4:
                return $attendant_name.'出发';

                break;
            case 5:
                return $attendant_name.'到达';

                break;
            case 6:
                return '服务中';

                break;

            case 7:
                return '已完成';

                break;
            case 8:
                return '待转单';

                break;

            case -1:
                return '已取消';

                break;

        }

    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-12-15 12:05
     * @功能说明:提交内容导出
     */
    public function subDataList(){

        $input = $this->_param;

        $article_model = new ArticleList();

        $sub_list_model= new SubList();

        $sub_data_model= new SubData();

        $article_title = $article_model->where(['id'=>$input['article_id']])->value('title');
        //获取导出标题
        $title_data = $article_model->getFieldTitle($input['article_id']);

        $title = ['用户ID','微信昵称'];

        $title = array_merge($title,array_column($title_data,'title'));

        $title[] = '提交时间';

        $name  = '文章表单数据导出-'.$article_title.'-'.date('Y-m-d H:i:s');

        $diss[] = ['article_id','=',$input['article_id']];

        $diss[] = ['status','=',1];

        if(!empty($input['start_time'])&&!empty($input['end_time'])){

            $diss[] = ['create_time','between',"{$input['start_time']},{$input['end_time']}"];

        }

        if(!empty($input['id'])){

            $diss[] = ['id','in',$input['id']];

        }

        $list = $sub_list_model->where($diss)->order('id desc')->select()->toArray();

        $new_data = [];

        if(!empty($list)){

            $user_model = new User();

            foreach ($list as &$v){

                $user_info = $user_model->where(['id'=>$v['user_id']])->field('nickName,avatarUrl')->find();

                $info   = array();

                $info[] = $v['user_id'];

                $info[] = $user_info['nickName'];

                if(!empty($title_data)){

                    foreach ($title_data as $vs){

                        $dis = [

                            'field_id' => $vs['field_id'],

                            'sub_id'   => $v['id']
                        ];

                        $find = $sub_data_model->where($dis)->value('value');;

                        $info[] = !empty($find)?$find:'';
                    }
                }

                $info[] = date('Y-m-d H:i:s',$v['create_time']);

                $new_data[] = $info;

            }

        }

        $excel = new Excel();

        $excel->excelExport($name,$title,$new_data);

        return $this->success(true);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-18 11:31
     * @功能说明:财务报表
     */
    public function financeDetailedList(){

        $input = $this->_param;

        $where[] = ['b.uniacid','=',$this->_uniacid];

        $where[] = ['b.pay_time','>',0];

        $id = [];
        if(!empty($input['pay_type'])){

            $dis[]   = ['pay_type','=',$input['pay_type']];

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

        if(!empty($input['type'])){

            $id = $this->model->getTypeCommOrder($input['type']);

            if($type==2){

                $where[] = ['a.type','in',[2,5,6]];

            }elseif($type==8){

                $where[] = ['a.type','in',[8,13]];

            }else{

                $where[] = ['a.type','=',$type];
            }

            $where[] = ['a.status','=',2];
        }


        $data = $this->model->financeDetailedSelect($where);


        $total_list = $this->model->financeDetailedDataV2($where,$type,$id);

        $last[] = '合计:';
        $last[] = '';
        $last[] = '';
        $last[] = '';
        $last[] = '';
        $last[] = '';
        $last[] = $total_list['init_price'];
        $last[] = $total_list['discount_price'];
        $last[] = $total_list['true_service_price'];
        $last[] = $total_list['refund_cash'];
        $last[] = '';
        $last[] = $total_list['coach_cash'];
        $last[] = $total_list['car_cash'];
        $last[] = '';
        if(in_array($this->_user['city_type'],[3])||$this->_user['is_admin']!=0){
            $last[] = '';
            $last[] = $total_list['province_cash'];
        }
        if(in_array($this->_user['city_type'],[3,1])||$this->_user['is_admin']!=0) {
            $last[] = '';
            $last[] = $total_list['city_cash'];
        }
        $last[] = '';
        $last[] = $total_list['district_cash'];
        $last[] = '';
        $last[] = $total_list['user_cash'];
        $last[] = '';
        $last[] = $total_list['level_reseller_cash'];
        $last[] = '';
        $last[] = $total_list['partner_cash'];
        $last[] = '';
        $last[] = $total_list['salesman_cash'];
        $last[] = '';
        $last[] = $total_list['channel_cash'];
        $last[] = $total_list['poster_cash'];
        $last[] = $total_list['point_cash'];
        $last[] = $total_list['coach_balance_cash'];
       // $last[] = $total_list['skill_cash'];
        if($this->_user['is_admin']!=0){

            $last[] = $total_list['remain_cash'];
        }
        $name = '财务报表-'.date('Y-m-d H:i:s');
        $header[] = '系统订单号';
        $header[] = '付款订单号';
        $header[] = '下单时间';
        $header[] = '支付方式';
        $header[] = '财务类型';
        $header[] = '订单状态';
        $header[] = '项目原价';
        $header[] = '优惠价';
        $header[] = '实付金额';
        $header[] = '退款金额';
        $header[] = $this->attendant_name.'名称';
        $header[] = $this->attendant_name.'提成';
        $header[] = $this->attendant_name.'车费';
        $header[] = '车费所属人';
        if(in_array($this->_user['city_type'],[3])||$this->_user['is_admin']!=0){
            $header[] = '省代名称';
            $header[] = '省代提成';
        }
        if(in_array($this->_user['city_type'],[3,1])||$this->_user['is_admin']!=0) {
            $header[] = '市代名称';
            $header[] = '市代提成';
        }
        $header[] = '区县代理名称';
        $header[] = '区县代理提成';
        $header[] = '一级'.$this->reseller_name.'姓名';
        $header[] = '一级'.$this->reseller_name.'提成';
        $header[] = '二级'.$this->reseller_name.'姓名';
        $header[] = '二级'.$this->reseller_name.'提成';
        $header[] = $this->broker_name.'姓名';
        $header[] = $this->broker_name.'提成';
        $header[] = '业务员姓名';
        $header[] = '业务员提成';
        $header[] = $this->channel_name.'姓名';
        $header[] = $this->channel_name.'提成';
        $header[] = '广告费';
        $header[] = '手续费';
        $header[] = '储值扣款';
       // $header[] = '技术服务费';
        if($this->_user['is_admin']!=0){

            $header[] = '平台利润';
        }
        $new_data = [];

        if(!empty($data)){

            foreach ($data as $v){

               // $car_admin = !empty($v['car_admin'])?'车费归属代理商':'';

                $info   = array();

                $info[] = $v['order_code']."\t";

                $info[] = $v['transaction_id']."\t";

                $info[] = date('Y-m-d H:i:s',$v['end_time']);

                $info[] = $this->payModel($v['pay_model']);

                $info[] = $v['is_add']==1?'加钟':'销售';

                $info[] = $v['pay_type']==7?'已完成':'已取消';

                $info[] = $v['init_price'];

                $info[] = $v['discount_price'];

                $info[] = $v['true_service_price'];

                $info[] = $v['refund_cash'];

                $info[] = !empty($v['coach_name'])?$v['coach_name']:'';

                $info[] = !empty($v['coach_cash'])?$v['coach_cash']:0;

                $info[] = $v['car_cash'];

                $info[] = $v['car_name'];

                if(in_array($this->_user['city_type'],[3])||$this->_user['is_admin']!=0) {

                    $info[] = !empty($v['province_name']) ? $v['province_name'] : '';

                    $info[] = $v['province_cash'];
                }

                if(in_array($this->_user['city_type'],[3,1])||$this->_user['is_admin']!=0) {

                    $info[] = $v['city_name'];

                    $info[] = $v['city_cash'];
                }

                $info[] = $v['district_name'];

                $info[] = $v['district_cash'];

                $info[] = $v['cash_user_name'];

                $info[] = $v['user_cash'];

                $info[] = $v['level_reseller_name'];

                $info[] = $v['level_reseller_cash'];

                $info[] = $v['partner_name'];

                $info[] = $v['partner_cash'];

                $info[] = $v['salesman_name'];

                $info[] = $v['salesman_cash'];

                $info[] = $v['channel_name'];

                $info[] = $v['channel_cash'];

                $info[] = $v['poster_cash'];

                $info[] = $v['point_cash'];

                $info[] = $v['coach_balance_cash'];

                if($this->_user['is_admin']!=0) {

                    $info[] = $v['remain_cash'];
                }

                $new_data[] = $info;
            }
        }

        if(!empty($new_data)){

            $new_data[]=$last;
        }

        $excel = new Excel();

        $excel->excelCsv($name,$header,$new_data);

        $this->controlActionLog(0);

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-08-28 23:03
     * @功能说明:佣金记录
     */
    public function commList(){

        $input = $this->_param;

        if($this->_user['is_admin']==0){

            $dis[] = ['type','not in',[11,15,7,23,21,22]];
        }else{

            $dis[] = ['type','not in',[11,15,7,23]];
        }

        $dis[] = ['uniacid','=',$this->_uniacid];

        if(!empty($input['status'])){

            $dis[] = ['status','=',$input['status']];
        }else{

            $dis[] = ['status','>',-1];
        }

        if($this->_user['is_admin']==0){

            $dis[] = ['admin_id','in',$this->admin_arr];
        }

        if(!empty($input['type'])){

            if($input['type']==2){

                $dis[] = ['type','in',[2,5,6]];

            }elseif ($input['type']==8){

                $dis[] = ['type','in',[8,13]];

            } else{

                $dis[] = ['type','=',$input['type']];
            }
        }

        if(!empty($input['order_code'])){

            $dis[] = ['order_code','like','%'.$input['order_code'].'%'];
        }

        if(!empty($input['start_time'])&&!empty($input['end_time'])){

            $dis[] = ['create_time','between',"{$input['start_time']},{$input['end_time']}"];
        }

        $change_model = new CoachChangeLog();

        $comm_model = new Commission();

        if(!empty($input['top_name'])){

            if(empty($input['type'])){

                $this->errorMsg('请选择类型');
            }

            $data = $comm_model->recordSelect($input['type'],$dis,$input['top_name']);

        }else{

            $where[] = ['type','=',16];

            $where[] = ['cash','>',0];

            $data = $comm_model->where($dis)
                ->where(function ($query) use ($where){
                    $query->whereOr($where);
                })
                ->group('id')
                ->order('order_id desc,id desc')
                ->select()
                ->toArray();
        }

        $adapay_model= new Record();

        $user_model  = new User();

        $commission_custom = getConfigSetting($this->_uniacid,'commission_custom');

        $material_text     = getConfigSetting($this->_uniacid,'material_text');

        if(!empty($data)){

            foreach ($data as &$v){

                $order = Db::name('massage_service_order_list')->where(['id'=>$v['order_id']])->field('add_pid,free_fare,coupon_bear_type,init_service_price,order_code,pay_type,pay_price,transaction_id,car_price,material_price')->find();

                if(!empty($order)){

                    $v = array_merge($v,$order);
                }

                if(in_array($v['type'],[17,18,19,20,21,22])){

                    $v['pay_price'] = $v['order_cash'];
                }

                $v['car_admin'] = $v['type']==13?1:0;

                $is_adapay = $adapay_model->dataInfo(['commission_id'=>$v['id']]);

                $v['is_adapay']= !empty($is_adapay)?1:0;

                $v['nickName'] = $user_model->where(['id'=>$v['user_id']])->value('nickName');

                $v['coach_cash_control'] = $v['status']==2&&$v['admin_id']==0&&in_array($v['type'],[3,8])&&$v['top_id']==0?1:0;

                if(!in_array($v['type'],[17,18,19,20,21,22])&&!empty($order)){

                    if($v['coupon_bear_type']==2){

                        $v['pay_price'] = $v['pay_price'].' 优惠前¥'.$v['init_service_price'];
                    }
                    if($v['car_price']>0){

                        $v['pay_price'] = $v['pay_price'].' 含车费'.$v['car_price'];
                    }
                    if($v['material_price']>0){

                        $v['pay_price'] = $v['pay_price'].' 含'.$material_text.$v['material_price'];
                    }

                    if($v['top_id']==0&&$v['car_cash']>0){

                        $v['cash'] = $v['cash'].' 含车费'.$v['car_cash'];
                    }
                }

                $table_data = $comm_model->getCommTable($v['type']);

                if(!empty($table_data)){

                    $table = $table_data['table'];

                    $filed = $table_data['filed'];

                    $title = $table_data['name'];

                    $v['top_name'] = Db::name($table)->where(["id"=>$v[$filed]])->value($title);
                }

                if(in_array($v['type'],[3,8])&&empty($v['top_id'])){

                    $order_id = !empty($v['add_pid'])?$v['add_pid']:$v['order_id'];

                    $v['top_name'] = $change_model->where(['order_id'=>$order_id,'is_new'=>1])->value('now_coach_name');
                }

                if (in_array($v['type'],[16,21,22])){

                    $v['top_name'] = '平台';

                    $v['cash'] = $v['type']==16?$v['company_cash']:$v['cash'];
                }
                if($v['type']==2){

                    if($commission_custom==0){

                        $v['balance'] = '平台抽成-'.$v['balance'];
                    }

                    $coach_cash = $v['coach_cash']>0?'包含'.$v['coach_cash'].'线下服务费，':'';

                    $car_cash   = $v['car_cash']>0?'包含'.$v['car_cash'].'线下车费':'';

                    $v['cash'] = !empty($coach_cash)||!empty($car_cash)?$v['cash'].'('.$coach_cash.$car_cash.')':$v['cash'];
                }

                $share_cash = $poster_cash = $coach_balance_cash= $skill_cash=$coupon_cash = $share_car_cash = $balance_discount_cash=0;

                $share_data = [];
                //技师查询是否有分摊金额
                if(in_array($v['type'],[3])){

                    $share_type = $v['type']==3?1:3;

                    $share_data = $this->comm_share_model->where(['order_id'=>$v['order_id'],'type'=>$share_type])->field('sum(share_cash) as share_cash,cash_type')->group('cash_type')->select()->toArray();
                }
                //代理商分摊金额
                if(in_array($v['type'],[2,5,6])){

                    $share_data = $this->comm_share_model->where(['order_id'=>$v['order_id'],'type'=>2,'share_id'=>$v['top_id']])->field('sum(share_cash) as share_cash,cash_type')->group('cash_type')->select()->toArray();
                }
                if(in_array($v['type'],[16])){

                    $share_car_cash = $this->comm_share_model->where(['order_id'=>$v['order_id'],'cash_type'=>5,'comm_id'=>$v['id']])->sum('share_cash');
                }

                if(!empty($share_data)){

                    foreach ($share_data as $value){

                        if($value['cash_type']==0){

                            $share_cash = $value['share_cash'];
                        }elseif ($value['cash_type']==2){

                            $poster_cash = $value['share_cash'];

                        }elseif ($value['cash_type']==3){

                            $coach_balance_cash = $value['share_cash'];
                        }elseif ($value['cash_type']==4){

                            $skill_cash = $value['share_cash'];

                        }elseif ($value['cash_type']==6){

                            $coupon_cash = $value['share_cash'];

                        }elseif ($value['cash_type']==7){

                            $balance_discount_cash = $value['share_cash'];
                        }
                    }
                }

                if($coach_balance_cash>0){

                    $v['cash'] .= '  储值扣款:'.$coach_balance_cash.'元';
                }

                if($share_cash>0){

                    $v['cash'] .= '  被分摊金额:'.$share_cash.'元';
                }

                if($poster_cash>0){

                    $v['cash'] .= '  被分摊广告费:'.$poster_cash.'元';
                }
                if(!empty($skill_cash)&&$skill_cash>0){

                    $v['cash'] .= '  技术服务费:'.$skill_cash.'元';
                }
                if(!empty($coupon_cash)&&$coupon_cash>0){

                    $v['cash'] .= '  优惠券分摊:'.$coupon_cash.'元';
                }
                if(!empty($share_car_cash)&&$share_car_cash>0){

                    $v['cash'] .= '  车费分摊:'.$share_car_cash.'元';
                }
                if(!empty($balance_discount_cash)&&$balance_discount_cash>0){

                    $v['cash'] .= '  储值折扣分摊:'.$balance_discount_cash.'元';
                }
                //手续费
                if($v['point_cash']>0){

                    $v['cash'] .= '  手续费:'.$v['point_cash'].'元';
                }

                $v['type'] = $v['type']==13?8:$v['type'];
            }
        }

        $name = $this->reseller_name.'佣金-'.date('Y-m-d H:i:s');

        $header=[
            'ID',
            '佣金获得者',
            '来源',
            '系统订单号',
            '付款订单号',
            '佣金类型',
            '状态',
            '分佣通道',
            '提成比例',
            '订单总金额',
            '此单提成金额',
            '时间',
        ];

        $new_data =[];

        if(!empty($data)){

            foreach ($data as $vs){

                $info   = array();

                $info[] = $vs['id'];

                $info[] = $vs['top_name'];

                $info[] = $vs['nickName'];

                $info[] = $vs['order_code']."\t";

                $info[] = $vs['transaction_id']."\t";

                $info[] = $this->getCommType($vs['type'],$vs['city_type']);

                $info[] = $vs['status']==2?'已到账':'未到账';

                $info[] = $vs['is_adapay']==0?'系统分账':'三方分账';

                if(in_array($vs['type'],[1,14])){

                    if(!empty($vs['order_goods'])){

                        $str = '';

                        foreach ($vs['order_goods'] as $vss){

                            $str .= $vss['goods_name'].'比例'.$vss['balance'].'%,';
                        }
                    }
                    $info[] = $str;

                }else{

                    $info[] = $vs['balance'].'%';

                }

                $info[] = $vs['pay_price'];

                $info[] = $vs['cash'];

                $info[] = date('Y-m-d H:i:s',$vs['create_time']);

                $new_data[] = $info;
            }
        }

        $excel = new Excel();

        $excel->excelCsv($name,$header,$new_data);

        $this->controlActionLog(0);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-21 17:28
     * @功能说明:获取佣金类型
     */

    public function getCommType($type,$city_type){

        if(in_array($type,[2,5,6])){

            if($city_type==1){

                return '市代理';
            }elseif ($city_type==2){

                return '区县代理';
            }else{
                return '省代理';
            }
        }

        $name = $this->attendant_name;

        $arr = [

            1 =>'一级'.$this->reseller_name,
            2 =>$this->agent_name,
            3 => $name,
            5 => $this->agent_name,
            6 => $this->agent_name,
            7 => $name.'拉用户充值余额',
            8 => '车费',
            9 => $name.$this->broker_name,
            10 => $this->channel_name,
            11 => '平台利润',
            12 => '业务员',
            14 => '二级'.$this->reseller_name,
            16 => '平台',
            17 => $name.'空单费',
            18 => $name.'退款手续费',
            19 => $this->agent_name.'空单费',
            20 => $this->agent_name.'手续费',
            21 => '平台空单费',
            22 => '平台手续费',
        ];

        return $arr[$type];
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-06-14 18:28
     * @功能说明:技师业绩统计
     */
    public function coachDataList(){

        $input = $this->_param;

        switch ($input['top_type']){
            case 1:

                $top = 'service_timelong';

                break;

            case 2:

                $top = 'service_timelong';

                break;
            case 3:

                $top = 'order_service_price';

                break;
            case 4:

                $top = 'add_balance';

                break;

            case 5:

                $top = 'coach_integral';

                break;

            case 6:

                $top = 'coach_star';

                break;
        }

        if($input['time_type']==1){

            $start_time = strtotime(date('Y-m-d'));

            $end_time   = $start_time+86400;

        }elseif($input['time_type']==2){

            $start_time = strtotime('this week Monday');

            $end_time   = strtotime('this week Sunday');
        }elseif ($input['time_type']==3){

            $start_time = strtotime(date('Y-m-01 00:00:00'));

            $end_time   = time();

        }elseif ($input['time_type']==4){

            $start_time = strtotime('01/01 00:00:00');

            $end_time   = time();
        }else{
            $start_time = $input['start_time'];

            $end_time   = $input['end_time'];

        }

        $order_model = new Order();

        $log_model   = new WorkLog();

        $dis = [

            'uniacid' => $this->_uniacid,

            'status' => 2,
        ];

        if(!empty($input['coach_name'])){

            $dis ['coach_name'] = $input['coach_name'];
        }

        $where = [];

        if($this->_user['is_admin']==0){

            $where[] = ['admin_id','in',$this->admin_arr];
        }

        $coach_mdoel = new Coach();

        $time_list_model = new CoachTimeList();

        $refund_model = new RefundOrder();

        $data = $coach_mdoel
            ->where($dis)
            ->where($where)
            ->field('id as coach_id,coach_name,work_img,user_id')
            ->order('id desc')
            ->select()
            ->toArray();

        if(!empty($data)){

            foreach ($data as &$v){
                //服务时长
                $v['service_timelong']    = $order_model->where(['pay_type'=>7,'coach_id'=>$v['coach_id']])->whereBetween("create_time","$start_time,$end_time")->sum('true_time_long');

                $v['order_service_price'] = $order_model->where(['pay_type'=>7,'coach_id'=>$v['coach_id']])->whereBetween("create_time","$start_time,$end_time")->sum('true_service_price');

                $v['add_service_price']   = $order_model->where(['pay_type'=>7,'coach_id'=>$v['coach_id'],'is_add'=>1])->whereBetween("create_time","$start_time,$end_time")->sum('true_service_price');

                $v['order_service_price'] = round($v['order_service_price'],2);

                $v['add_service_price']   = round($v['add_service_price'],2);
                //积分
                $v['coach_integral']      = Db::name('massage_integral_list')->where(['coach_id'=>$v['coach_id'],'type'=>0,'status'=>1])->whereBetween("create_time","$start_time,$end_time")->sum('integral');

                $v['coach_star']          = Db::name('massage_service_order_comment')->where(['coach_id'=>$v['coach_id']])->where('status','>',-1)->whereBetween("create_time","$start_time,$end_time")->avg('star');

                $v['coach_star']          = !empty($v['coach_star'])?$v['coach_star']:5;

                $v['add_balance']         = $v['order_service_price']>0?$v['add_service_price']/$v['order_service_price']:0;

                $v['add_balance']        = round($v['add_balance']*100,2);

                $v['coach_level']        = $coach_mdoel->getCoachLevel($v['coach_id'],$this->_uniacid);

                $v['total_order_count']  = $order_model->where(['coach_id'=>$v['coach_id']])->where('pay_time','>',0)->whereBetween("create_time","$start_time,$end_time")->count();

                if(!empty($v['user_id'])){

                    $v['coach_onlinetime'] = $log_model->where(['coach_id'=>$v['coach_id']])->whereBetween("create_time","$start_time,$end_time")->sum('time');

                    $rest_time = $time_list_model->where(['coach_id'=>$v['coach_id'],'status'=>0,'is_click'=>1,'is_work'=>1])->where('time_str','<',time())->where('time_str','between',"$start_time,$end_time")->field('SUM(time_str_end-time_str) as time_long')->find();

                    $rest_time = $rest_time->time_long;

                    $v['coach_onlinetime'] = floor(($v['coach_onlinetime']-$rest_time)/60);

                    $v['coach_onlinetime'] = $v['coach_onlinetime']>0?$v['coach_onlinetime']:0;
                }else{

                    $v['coach_onlinetime'] = 0;
                }

                $v['service_order_count']= $order_model->where(['coach_id'=>$v['coach_id']])->where('pay_type','>',1)->whereBetween("create_time","$start_time,$end_time")->count();
                //技师据单
                $v['cancel_order_count'] = $refund_model->where(['coach_id'=>$v['coach_id'],'type'=>2,'is_admin_apply'=>0])->whereBetween("create_time","$start_time,$end_time")->count();

                $v['refund_balance']     = $v['total_order_count']>0?round(($v['total_order_count']-$v['service_order_count'])/$v['total_order_count']*100,2):0;
            }
        }

        $data = arraySort($data,$top,'desc');

        $name = $this->attendant_name.'数据-'.date('Y-m-d H:i:s');

        $header=[
            'ID',
            '姓名',
            '评分',
            '等级',
            '服务时长',
            '在线时长',
            '业绩',
            '加钟率',
            '积分',
            '总订单量',
            '已服务单量',
            '退单率',
            '总拒单量',
        ];

        $new_data =[];

        if(!empty($data)){

            foreach ($data as $vs){

                $info   = array();

                $info[] = $vs['coach_id'];

                $info[] = $vs['coach_name'];

                $info[] = $vs['coach_star'];

                $info[] = !empty($vs['coach_level']['title'])?$vs['coach_level']['title']:'';

                $info[] = $vs['service_timelong'].'分钟';

                $info[] = $vs['coach_onlinetime'].'分钟';

                $info[] = $vs['order_service_price'];

                $info[] = $vs['add_balance'].'%';

                $info[] = $vs['coach_integral'];

                $info[] = $vs['total_order_count'];

                $info[] = $vs['service_order_count'];

                $info[] = $vs['refund_balance'].'%';

                $info[] = $vs['cancel_order_count'];

                $new_data[] = $info;
            }
        }

        $excel = new Excel();

        $excel->excelCsv($name,$header,$new_data);

        $this->controlActionLog(0);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-07 18:46
     * @功能说明:提现列表
     */
    public function walletList(){

        $input = $this->_param;

        $wallet_model = new Wallet();

        $dis[] = ['uniacid','=',$this->_uniacid];

        $where = [];

        if($this->_user['is_admin']==0){

            $where[] = ['admin_id','in',$this->admin_arr];
            //所有自己提现的
            $id_arr = $wallet_model->where(['user_id'=>$this->_user['admin_id'],'type'=>3])->column('id');

            $id_arrs = $wallet_model->where(['coach_id'=>$this->_user['admin_id']])->where('type','in',[8,9])->column('id');

            $where[] = ['id','in',array_merge($id_arr,$id_arrs)];
        }

        if(!empty($input['start_time'])&&!empty($input['end_time'])){

            $dis[] = ['create_time','between',"{$input['start_time']},{$input['end_time']}"];
        }

        if(!empty($input['type'])){

            if($input['type']==2){

                $dis[] = ['type','in',[2,7,9]];

            }elseif ($input['type']==3){

                $dis[] = ['type','in',[3,8]];
            }else{

                $dis[] = ['type','=',$input['type']];
            }
        }

        if(!empty($input['code'])){

            $dis[] = ['code','like','%'.$input['code'].'%'];
        }

        if(!empty($input['status'])){

            $dis[] = ['status','=',$input['status']];
        }

        if(!empty($input['coach_name'])){

            $id = $wallet_model->getIdByName($input['coach_name']);

            $dis[] = ['id','in',$id];
        }

        if(isset($input['min_cash'])&&is_numeric($input['min_cash'])){

            $dis[] = ['total_price','between',"{$input['min_cash']},{$input['max_cash']}"];
        }

        $data = $wallet_model->where($dis)->where(function ($query) use ($where){
            $query->whereOr($where);
        })->order('id desc')->select()->toArray();

        $admin_model = new \app\massage\model\Admin();

        $reseller_model = new DistributionList();

        if(!empty($data)){

            foreach ($data as &$v){

                $v['control_name'] = '';
                //操作人
                if(in_array($v['status'],[2,3,4])){

                    if(!empty($v['control_id'])){

                        $v['control_name'] = $admin_model->where(['id'=>$v['control_id']])->value('agent_name');
                    }else{

                        $v['control_name'] = $admin_model->where(['is_admin'=>1])->value('agent_name');
                    }
                }

                $obj = $wallet_model->getWalletObjInfo($v['type']);

                $title    = $obj['title'];

                $true_user_name = !empty($obj['true_user_name'])?$obj['true_user_name']:'';

                $field    = !empty($true_user_name)?"$title,$true_user_name":"$title";

                $user_id  = !empty($obj['user_id'])?$obj['user_id']:'coach_id';
                //提现人信息
                $obj_info = $obj['model']->where(['id'=>$v[$user_id]])->field($field)->find();

                $v['coach_name'] = $obj_info[$title];

                if(!empty($true_user_name)){

                    $v[$true_user_name] = $obj_info[$true_user_name];
                }
                //兼容一下分销员的真实姓名
                if($v['type']==4){

                    $reseller = $reseller_model->where(['user_id'=>$v['user_id'],'status'=>2])->field('user_name,true_user_name')->find();

                    if(!empty($reseller)){

                        $v['true_user_name'] = $reseller['true_user_name'];
                    }
                }

                if($v['type']==11){

                    $admin_user_model= new \app\adminuser\model\AdminUser();

                    $v['true_user_name'] = $admin_user_model->where(['uniacid'=>$this->_uniacid])->value('user_name');
                }

                if(in_array($v['type'],[3,7,8,9])){

                    $v['true_user_name'] = $v['coach_name'];
                }
                //上级名字
                if(!empty($v['admin_id'])){

                    $agent_name =  $admin_model->where(['id'=>$v['admin_id']])->field('agent_name,city_type')->find();

                    $v['agent_name'] = $agent_name['agent_name'].'-'.$this->getAgentType($agent_name['city_type']);
                }

                if(in_array($v['type'],[7,9])){

                    $v['type'] = 2;
                }

                if(in_array($v['type'],[8])){

                    $v['type'] = 3;
                }
                //微信打款的时候兼容一下
                if($v['online']==1){

                    $v['payment_no'] = $v['detail_id'];
                }

                if($v['type']==11){

                    $v['agent_name'] = '平台';
                }
            }
        }

        $name = '提现数据-'.date('Y-m-d H:i:s');

        $personal_income_tax_text = getConfigSetting($this->_uniacid,'personal_income_tax_text');

        $header=[
            'ID',
            '姓名',
            '真实姓名',
            '提现号',
            '商户单号',
            '备注',
            '申请金额',
            $personal_income_tax_text,
            '所属上级',
            '到账金额',
            '提现方式',
            '到账方式',
            '提现类型',
            '状态',
            '申请时间',
            '处理时间',
            '打款人',
        ];
        $new_data =[];

        if(!empty($data)){

            foreach ($data as $vs){

                $info   = array();

                $info[] = $vs['id'];

                $info[] = $vs['coach_name'];

                $info[] = !empty($vs['true_user_name'])?$vs['true_user_name']:'';

                $info[] = $vs['code']."\t";

                $info[] = $vs['payment_no']."\t";

                $info[] = $vs['text'];

                $info[] = $vs['total_price'];

                $info[] = $personal_income_tax_text.'扣除'.$vs['tax_point'].'%,扣除'.$vs['service_price'];

                $info[] = !empty($vs['agent_name'])?$vs['agent_name']:'平台';

                $info[] = $vs['status']==2?$vs['true_price']:0;

                $info[] = $this->walletTransferType($vs['apply_transfer']);

                $info[] = $vs['status']==2?$this->walletTransferType($vs['online']):'';

                $info[] = $this->walletType($vs['type']);

                $info[] = $this->walletStatus($vs['status']);

                $info[] = date('Y-m-d H:i:s',$vs['create_time']);

                $info[] = !empty($vs['sh_time'])?date('Y-m-d H:i:s',$vs['sh_time']):'';

                $info[] = $vs['control_name'];

                $new_data[] = $info;
            }
        }

        $excel = new Excel();

        $excel->excelCsv($name,$header,$new_data);

        $this->controlActionLog(0);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-05-23 18:02
     * @功能说明:获取代理商类型
     */
    public function getAgentType($type){

        switch ($type){

            case 1:
                $text = '市';
                break;
            case 3:
                $text = '省';
                break;
            case 2:
                $text = '区县';
                break;
        }

        return $text;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-24 14:55
     * @功能说明:提现|到账方式
     */
    public function walletTransferType($type){

        switch ($type){

            case 0:

                $text = '线下转账';
                break;
            case 1:

                $text = '微信转账';
                break;
            case 2:

                $text = '支付宝转账';
                break;
            default:

                $text = '银行卡转账';
                break;
        }

        return $text;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-24 14:55
     * @功能说明:提现类型
     */
    //type 1服务费 2是车费  3加盟 4用户分销 5渠道商 6业务员
    public function walletType($type){

        switch ($type){

            case 1:

                $text = $this->attendant_name;
                break;
            case 2:

                $text = $this->attendant_name.'车费';
                break;
            case 3:

                $text = $this->agent_name;

                break;
            case 4:

                $text = $this->reseller_name;
                break;
            case 5:

                $text = $this->channel_name;
                break;
            case 10:

                $text = $this->broker_name;
                break;
            case 11:

                $text = '平台';
                break;
            case 12:
                $text = '活动经费';
                break;
            default:

                $text = '业务员';
                break;
        }

        return $text;
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-24 14:55
     * @功能说明:提现类型
     */
    public function walletStatus($type){

        switch ($type){

            case 1:

                $text = '未到账';
                break;
            case 2:

                $text = '已到账';
                break;
            case 3:

                $text = '已拒绝';

                break;
            case 4:

                $text = '转账中';
                break;
            case 5:

                $text = '转账失败';
                break;
            default:

                $text = '转账失败';
                break;
        }

        return $text;
    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-09-18 14:11
     * @功能说明:平台流水
     */
    public function companyWater(){

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

        $data = Db::table($sql.' a')->select()->toArray();

        $order_price = $refund_price = 0;

        if(!empty($data)){

            $refund_model = new RefundOrder();

            $user_model   = new User();

            $admin_model  = new \app\massage\model\Admin();

            foreach ($data as $k=>$v){
                //用户昵称
                $data[$k]['nickName'] = $user_model->where(['id'=>$v['user_id']])->value('nickName');

                if(in_array($v['type'],[2,6])){

                    $data[$k]['refund_cash'] = $refund_model->where(['order_id'=>$v['id'],'status'=>2])->sum('refund_price');

                    $up_price = Db::name('massage_service_up_order_list')->where(['pay_type'=>2,'order_id'=>$v['id']])->sum('pay_price');

                    $data[$k]['pay_price'] -= $up_price;

                    $data[$k]['pay_price'] = round($data[$k]['pay_price'],2);

                }else{

                    $data[$k]['refund_cash'] = 0;
                }

                if($v['type']!=8){
                    //用户昵称
                    $data[$k]['nickName'] = $user_model->where(['id'=>$v['user_id']])->value('nickName');
                }else{
                    //用户昵称
                    $data[$k]['nickName'] = $admin_model->where(['id'=>$v['user_id']])->value('agent_name');
                }

                if(in_array($v['type'],[4,1])){

                    $refund_price+= $data[$k]['pay_price'];

                    $data[$k]['pay_price'] = $data[$k]['pay_price']*-1;
                }else{

                    $order_price+= $data[$k]['pay_price'];
                }
            }
        }

        $name = '平台订单流水-'.date('Y-m-d H:i:s');

        $header=[
            '交易时间',
            '交易方式',
            '交易类型',
            '交易用户',
            '系统订单号',
            '支付宝/微信/三方支付商户单号',
            '订单金额',
            '申请退款金额',
        ];

        $new_data =[];

        if(!empty($data)){

            foreach ($data as $vs){

                $info   = array();

                $info[] = date('Y-m-d H:i:s',$vs['pay_time']);

                $info[] = $this->payModel($vs['pay_model']);

                $info[] = $this->getWaterType($vs['type']);

                $info[] = $vs['nickName'];

                $info[] = $vs['order_code']."\t";

                $info[] = $vs['transaction_id']."\t";

                $info[] = $vs['pay_price'];

                $info[] = $vs['refund_cash'];

                $new_data[] = $info;
            }
        }

        $excel = new Excel();

        $new_data[] = ['订单笔数:'.count($data)];

        $new_data[] = ['订单总金额:'.$order_price];

        $new_data[] = ['退款总金额:'.$refund_price];

        $excel->excelCsv($name,$header,$new_data);

        $this->controlActionLog(0);

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-09-18 18:02
     * @功能说明:流水类型
     */
    public function getWaterType($type){

        switch ($type){

            case 1:
                $text = $this->attendant_name.'退款';
                break;
            case 2:
                $text = '服务订单';
                break;
            case 3:
                $text = '余额储值订单';
                break;
            case 4:
                $text = '服务退款';
                break;
            case 5:
                $text = '升级订单';
                break;
            case 6:
                $text = '加钟订单';
                break;
            case 7:
                $text = $this->reseller_name.'门槛订单';
                break;
            case 8:
                $text = $this->agent_name.'充值订单';
                break;
            case 9:
                $text = '会员卡订单';
                break;
            case 10:
                $text = '储值折扣卡订单';
                break;
            case 11:
                $text = '活动发布';
                break;
            case 12:
                $text = '取消活动发布';
                break;
            case 13:
                $text = '活动报名';
                break;
            case 14:
                $text = '取消报名';
                break;
            default:
                $text = '';
        }

        return $text;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-02-20 11:57
     * @功能说明:储值订单导出
     */
    public function balanceOrderList(){

        $input = $this->_param;

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['status','>',1];

        $dis[] = ['type','<>',3];

        if(!empty($input['name'])){

            $dis[] = ['title','like','%'.$input['name'].'%'];
        }

        if(!empty($input['order_code'])){

            $dis[] = ['order_code','like','%'.$input['order_code'].'%'];
        }

        if(!empty($input['start_time'])){

            $dis[] = ['pay_time','between',"{$input['start_time']},{$input['end_time']}"];
        }

        $order_model = new BalanceOrder();

        $data = $order_model->where($dis)->order('id desc')->select()->toArray();

        if(!empty($data)){

            $coach_model = new Coach();

            $comm_model  = new Commission();

            foreach ($data as &$v){

                $v['coach_name'] = $coach_model->where(['id'=>$v['coach_id']])->value('coach_name');

                $v['send_price'] = $v['type']==2?$v['true_price']:$v['true_price']-$v['pay_price'];

                $v['pay_price']  = $v['type']==2?0:$v['pay_price'];

                $comm_info = $comm_model->dataInfo(['order_id'=>$v['id'],'type'=>7,'status'=>2]);

                if(!empty($comm_info)){
                    //佣金
                    $v['comm_cash']    = $comm_info['cash'];
                    //比列
                    $v['comm_balance'] = $comm_info['balance'];
                }
            }
        }

        $name = '储值订单-'.date('Y-m-d H:i:s');

        $header=[
            'ID',
            '客户ID',
            '客户昵称',
            '关联'.$this->attendant_name,
            '套餐名称',
            '充值类型',
            '充值金额',
            '赠送金额',
            '到账金额',
            '返佣比例',
            '储值佣金',
            '系统订单号',
            '付款订单号',
            '支付时间',
        ];

        $new_data =[];

        if(!empty($data)){

            foreach ($data as $vs){

                $info   = array();

                $info[] = $vs['id'];

                $info[] = $vs['user_id'];

                $info[] = $vs['nick_name'];

                $info[] = $vs['coach_name'];

                $info[] = $vs['title'];

                $info[] = $vs['type']==1?$this->payModel($vs['pay_model']):'系统自定义充值';

                $info[] = $vs['pay_price'];

                $info[] = $vs['send_price'];

                $info[] = $vs['true_price'];

                $info[] = isset($vs['comm_balance'])?$vs['comm_balance']:'';

                $info[] = isset($vs['comm_cash'])?$vs['comm_cash']:'';

                $info[] = $vs['order_code']."\t";

                $info[] = $vs['transaction_id']."\t";

                $info[] = !empty($vs['pay_time'])?date('Y-m-d H:i:s',$vs['pay_time']):date('Y-m-d H:i:s',$vs['create_time']);

                $new_data[] = $info;
            }
        }

        $excel = new Excel();

        $excel->excelCsv($name,$header,$new_data);

      //  $this->controlActionLog(0);
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-02-20 11:57
     * @功能说明:储值订单导出
     */
    public function balanceDiscountOrderList(){

        $input = $this->_param;

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['pay_type','=',2];

        if(!empty($input['name'])){

            $dis[] = ['title','like','%'.$input['name'].'%'];
        }

        if(!empty($input['order_code'])){

            $dis[] = ['order_code','like','%'.$input['order_code'].'%'];
        }

        if(!empty($input['start_time'])){

            $dis[] = ['pay_time','between',"{$input['start_time']},{$input['end_time']}"];
        }

        $model = new OrderList();

        $comm_model  = new Commission();

        $coach_model = new Coach();

        $user_model  = new User();

        $data = $model->where($dis)->order('id desc')->select()->toArray();

        if(!empty($data)){

            foreach ($data as &$v){

                if(!empty($v['coach_id'])){

                    $v['coach_name'] = $coach_model->where(['id'=>$v['coach_id']])->value('coach_name');
                }

                $v['nickName'] = $user_model->where(['id'=>$v['user_id']])->value('nickName');

                $comm_info = $comm_model->dataInfo(['order_id'=>$v['id'],'type'=>25,'status'=>2]);

                if(!empty($comm_info)){
                    //佣金
                    $v['comm_cash']    = $comm_info['cash'];
                    //比列
                    $v['comm_balance'] = $comm_info['balance'];
                }
            }
        }

        $name = '储值订单-'.date('Y-m-d H:i:s');

        $header=[
            'ID',
            '客户ID',
            '客户昵称',
            '套餐名称',
            '充值类型',
            '充值金额',
            '折扣',
            '运营成本',
            '关联'.$this->attendant_name,
            '返佣比例',
            '储值佣金',
            '系统订单号',
            '付款订单号',
            '支付时间',
        ];

        $new_data =[];

        if(!empty($data)){

            foreach ($data as $vs){

                $info   = array();

                $info[] = $vs['id'];

                $info[] = $vs['user_id'];

                $info[] = $vs['nickName'];

                $info[] = $vs['title'];

                $info[] = $this->payModel($vs['pay_model']);

                $info[] = $vs['pay_price'];

                $info[] = $vs['discount'];

                $info[] = $vs['operating_costs'];

                $info[] = !empty($vs['coach_name'])?$vs['coach_name']:'';

                $info[] = isset($vs['comm_balance'])?$vs['comm_balance']:'';

                $info[] = isset($vs['comm_cash'])?$vs['comm_cash']:'';

                $info[] = $vs['order_code']."\t";

                $info[] = $vs['transaction_id']."\t";

                $info[] = !empty($vs['pay_time'])?date('Y-m-d H:i:s',$vs['pay_time']):date('Y-m-d H:i:s',$vs['create_time']);

                $new_data[] = $info;
            }
        }

        $excel = new Excel();

        $excel->excelCsv($name,$header,$new_data);
        //  $this->controlActionLog(0);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-03-07 10:10
     * @功能说明:用户列表导出
     */
    public function userList(){

        $input = $this->_param;

        $dis[] = ['uniacid','=',$this->_uniacid];

        if(!empty($input['user_status'])){

            $dis[] = ['user_status','=',$input['user_status']];
        }
        //是否授权
        if(!empty($input['type'])){

            if($input['type']==1){

                $dis[] = ['nickName','=',''];

            }elseif($input['type']==2){

                $dis[] = ['nickName','<>',''];

            }elseif ($input['type']==3){

                $dis[] = ['phone','=',''];

            }else{

                $dis[] = ['phone','<>',''];
            }
        }

        $where = [];

        if(!empty($input['nickName'])){

            $where[] = ['nickName','like','%'.$input['nickName'].'%'];

            $where[] = ['phone','like','%'.$input['nickName'].'%'];
        }

        if(!empty($input['start_time'])&&!empty($input['end_time'])){

            $start_time = $input['start_time'];

            $end_time = $input['end_time'];

            $dis[] = ['create_time','between',"$start_time,$end_time"];
        }

        if(!empty($input['id'])){

            $dis[] = ['id','=',$input['id']];
        }

        if(!empty($input['card_id'])){

            $dis[] = ['member_discount_id','=',$input['card_id']];
        }

        if(!empty($input['phone'])){

            $dis[] = ['phone','like','%'.$input['phone'].'%'];
        }

        if(!empty($input['province'])){

            $dis[] = ['province','=',$input['province']];
        }

        if(!empty($input['city'])){

            $dis[] = ['city','=',$input['city']];
        }

        if(!empty($input['area'])){

            $dis[] = ['area','=',$input['area']];
        }

        if(isset($input['source_type'])){

            $dis[] = ['source_type','=',$input['source_type']];
        }

        $map1 = $map2=[];

        if($this->_user['is_admin']==0){

            $map1 = [

                ['admin_id' ,'=', $this->_user['admin_id']],
            ];

            $city = \app\massage\model\Admin::adminCityData($this->_user['admin_id']);

            $map2 = [

                ['source_type' ,'=', 0],
            ];

            if(!empty($city)){

                $map2[] = ['province','=',$city['province']];

                if(in_array($city['city_type'],[1])){

                    $map2[] = ['city','=',$city['city']];
                }

                if(in_array($city['city_type'],[2])){

                    $map2[] = ['area','=',$city['area']];
                }
            }
        }

        $sort = 'id desc';

        if(!empty($input['sort'])){

            if($input['sort']==1){

                $sort = 'balance desc,id desc';
            }else{
                $sort = 'balance,id desc';
            }
        }

        $user_model = new User();

        if($this->_user['is_admin']==0){

            $data = $user_model->where($dis) ->where(function ($query) use ($where){
                $query->whereOr($where);
            })->where(function ($query) use ($map1,$map2){
                $query->whereOr([$map1,$map2]);
            })->order($sort)->select()->toArray();

        }else{

            $data = $user_model->where($dis) ->where(function ($query) use ($where){
                $query->whereOr($where);
            })->order($sort)->select()->toArray();
        }
        if(!empty($data)){

            $scan_model  = new ChannelScanQr();

            foreach ($data as &$v){
                //消费金额
                $v['user_use_cash']= $user_model->userUseCash($v['id']);

                if($v['source_type']!=0){
                    //扫的渠道码名称
                    $v['channel_qr'] = $scan_model->getQrTitle($user_id).'-';
                }else{

                    $v['channel_qr'] = '';
                }
            }
        }
        $name = '用户列表-'.date('Y-m-d H:i:s');
        $header[] = 'ID';
        $header[] = '微信昵称';
        $header[] = '手机号';
//        if($member_auth==1){
//
//            $header[] = '会员等级';
//        }
        $header[] = '消费总金额';
        $header[] = '账户余额';
        $header[] = '客户来源';
        $header[] = '地区';
        $header[] = '加入时间';
        $new_data =[];

        if(!empty($data)){

            foreach ($data as $vs){

                $info   = array();

                $info[] = $vs['id'];

                $info[] = $vs['nickName'];

                $info[] = $vs['phone'];

//                if($member_auth==1){
//
//                    $info[] = $vs['member_level'];
//                }

                $info[] = $vs['user_use_cash'];

                $info[] = $vs['balance'];

                $info[] = $vs['channel_qr'].$this->getSourceType($vs['source_type']);

                $info[] = $vs['province'].$vs['city'].$vs['area'];

                $info[] = date('Y-m-d H:i:s',$vs['create_time']);

                $new_data[] = $info;
            }
        }

        $excel = new Excel();

        $excel->excelCsv($name,$header,$new_data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-03-07 11:00
     * @功能说明:用户来源
     */
    public function getSourceType($type){

        switch ($type){

            case 1:
                $text = '渠道码';
                break;
            case 2:
                $text = $this->reseller_name.'邀请粉丝';
                break;
            case 3:
                $text = $this->broker_name.'邀请'.$this->attendant_name.'二维码';
                break;
            case 4:
                $text = $this->agent_name.'邀请'.$this->attendant_name.'二维码';
                break;
            case 5:
                $text = $this->agent_name.'邀请业务员二维码';
                break;
            case 6:
                $text = $this->agent_name.'邀请'.$this->channel_name.'二维码';
                break;
            case 7:
                $text = $this->attendant_name.'邀请用户充值';
                break;
            case 8:
                $text = '业务员邀请'.$this->channel_name.'二维码';
                break;
            case 9:
                $text = '原生渠道码';
                break;
            case 10:
                $text = $this->reseller_name.'邀请下级二维码';
                break;
            case 11:
                $text = $this->agent_name.'邀请'.$this->reseller_name.'二维码';
                break;
            case 12:
                $text = $this->agent_name.'邀请'.$this->agent_name.'二维码';
                break;
            case 13:
                $text = $this->attendant_name.'邀请用户购买会员卡二维码';
                break;
            case 14:
                $text = $this->attendant_name.'邀请用户购买折扣卡二维码';
                break;
            default:
                $text = '公众号搜索';
                break;
        }
        return $text;
    }

    /**
     * @Desc: 获取百应状态
     * @param $status
     * @return string
     * @Auther: shurong
     * @Time: 2024/8/22 16:49
     */
    public function getByStatus($status)
    {
        $text = [
            -1 => '未呼出',
            0 => '已接听',
            1 => '拒接',
            2 => '无法接通',
            3 => '主叫号码不可用',
            4 => '空号',
            5 => '关机',
            6 => '占线',
            7 => '停机',
            8 => '未接',
            9 => '主叫欠费',
            10 => '呼损',
            11 => '黑名单',
            12 => '天盾拦截',
            22 => '线路盲区',
            25 => '无可用线路',
            100 => '等待呼出'
        ];
        return $text[$status] ?? '未知';
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

        if (isset($input['by_status']) && $input['by_status'] !== ''&& $input['by_status'] != -2) {

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
            ->field('a.*,b.nickName,b.avatarUrl,b.phone,c.coach_name,b.city,b.province,b.area')
            ->group('a.id')
            ->order('a.id desc')
            ->select()
            ->toArray();

        if(!empty($data)){

            foreach ($data as &$v){

                $goods_name = $no_pay_goods_model->where(['record_id'=>$v['id']])->column('goods_name');

                $v['goods_name'] = implode('、',$goods_name);

                $v['order_count']= $order_model->where(['user_id'=>$v['user_id']])->where('pay_time','>',0)->count();
            }
        }

        $model = new PermissionBaiying($this->_uniacid);

        $auth = $model->sAuth();

        $name = '订单雷达-'.date('Y-m-d H:i:s');
        $header[] = 'ID';
        $header[] = '用户ID';
        $header[] = '微信昵称';
        $header[] = '手机号';
        $header[] = '所属地区';
        $header[] = '消费次数';
        $header[] = '预约'.$this->attendant_name;
        $header[] = '加购服务';
        if ($auth && $this->_user['is_admin'] == 1) {

            $header[] = '外呼状态';
        }
        $header[] = '操作时间';
        $new_data =[];

        if(!empty($data)){

            foreach ($data as $vs){

                $info   = array();

                $info[] = $vs['id'];

                $info[] = $vs['user_id'];

                $info[] = $vs['nickName'];

                $info[] = $vs['phone'];

                $info[] = $vs['province'].' '.$vs['city'].' '.$vs['area'];

                $info[] = $vs['order_count'];

                $info[] = $vs['coach_name'];

                $info[] = $vs['goods_name'];

                if ($auth && $this->_user['is_admin'] == 1) {

                    $info[] = $this->getByStatus($vs['by_status']);
                }
                $info[] = date('Y-m-d H:i:s',$vs['create_time']);

                $new_data[] = $info;
            }
        }

        $excel = new Excel();

        $excel->excelCsv($name,$header,$new_data);
    }


    /**
     * @author chenniang
     * @DataTime: 2024-08-13 17:00
     * @功能说明:技师财务导出
     */
    public function coachFinanceList(){

        $input = $this->_param;

        $order_model = new Order();

        $coach_model = new Coach();

        $comm_model  = new Commission();

        $wallet_model= new Wallet();

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['user_id','>',0];

        $dis[] = ['status','in',[2,3]];

        if(!empty($input['coach_name'])){

            $dis[] = ['coach_name','like','%'.$input['coach_name'].'%'];
        }

        if($this->_user['is_admin']==0){

            $dis[] = ['admin_id','=',$this->_user['admin_id']];
        }

        $coach = $coach_model->where($dis)->field('id as coach_id,user_id,coach_name,work_img,mobile,create_time,auth_status,status,service_price,car_price')->order('id desc')->select()->toArray();

        if(!empty($coach)){

            foreach ($coach as &$v){

                $v['id'] = $v['coach_id'];

                $v['wallet_price'] = $wallet_model->where(['coach_id'=>$v['id']])->where('type','in',[1,2])->where('status','in',[2])->sum('apply_price');
                //到账多少元
                $v['wallet_price'] = round($v['wallet_price'],2);

                $v['total_price']  = $wallet_model->where(['coach_id'=>$v['id']])->where('type','in',[1,2])->where('status','in',[1,2,4,5])->sum('total_price');
                //申请多少元
                $v['total_price']  = round($v['total_price'],2);
                //到账笔数
                $v['wallet_count'] = $wallet_model->where(['coach_id'=>$v['id']])->where('type','in',[1,2])->where('status','in',[2])->count();
                //申请笔数
                $v['total_count']  = $wallet_model->where(['coach_id'=>$v['id']])->where('type','in',[1,2])->where('status','in',[1,2,4,5])->count();

                $v['order_count'] = $order_model->where(['coach_id'=>$v['id'],'pay_type'=>7])->count();

                $v['order_price'] = $comm_model->where(['top_id'=>$v['id'],'status'=>2])->where('type','in',[3,8,17,18])->sum('cash');

                $v['order_price'] = round($v['order_price'],2);
                //余额
                $v['balance']     = round($v['service_price']+$v['car_price'],2);
            }
        }

        $service_cash = $coach_model->where($dis)->sum('service_price');

        $car_cash     = $coach_model->where($dis)->sum('car_price');

        $name = $this->attendant_name.'佣金记录-'.date('Y-m-d H:i:s');

        $header=[
            '关联用户ID',
            $this->attendant_name.'姓名',
            '手机号',
            '状态',
            '收入',
            '提现',
            '服务费收入',
            '车费收入',
            '当前总余额',
        ];

        $new_data =[];

        if(!empty($coach)){

            foreach ($coach as $vs){

                $info   = array();

                $info[] = $vs['user_id'];

                $info[] = $vs['coach_name'];

                $info[] = $vs['mobile'];

                $info[] = $vs['status']==2?'已授权':'取消授权';

                $info[] = $vs['order_price'];

                $info[] = $vs['total_price'];

                $info[] = $vs['service_price'];

                $info[] = $vs['car_price'];

                $info[] = round($vs['car_price']+$vs['service_price'],2);

                $new_data[] = $info;
            }
        }

        $excel = new Excel();

        $new_data[] = [$this->attendant_name.'服务费余额总计:'.round($service_cash,2)];

        $new_data[] = ['车费总计:'.round($car_cash,2)];

        $excel->excelCsv($name,$header,$new_data);
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-10-20 18:11
     * @功能说明:代理商财务列表
     */
    public function adminFinanceList(){

        $input = $this->_param;

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['is_admin','=',0];

        if(!empty($input['city_type'])){

            $dis[] = ['city_type','=',$input['city_type']];
        }

        if(!empty($input['admin_name'])){

            $dis[] = ['agent_name','like','%'.$input['admin_name'].'%'];
        }

        $admin_model = new \app\massage\model\Admin();

        $list = $admin_model->where($dis)->order('status desc,id desc')->select()->toArray();

        $total_cash = $admin_model->where($dis)->sum('cash');

        if(!empty($list)){

            $comm_model = new Commission();

            $wallet_model = new \app\massage\model\Wallet();

            foreach ($list as &$v){

                $dis = [

                    ['user_id' ,'=', $v['id']],

                    ['type'    ,'=',  3],
                ];

                $dis1 =[
                    ['coach_id' ,'=', $v['id']],

                    ['type'   ,'=',  8]
                ];
                //加盟商提现
                $admin_cash = $wallet_model->where(function ($query) use ($dis,$dis1){
                    $query->whereOr([$dis,$dis1]);
                })->where('status','in',[1,2,4,5])->sum('total_price');
                //车费到代理商
                $car_cash  = $wallet_model->where(['coach_id'=>$v['id']])->where('type','in',[7,9])->where('status','in',[1,2,4,5])->sum('total_price');
                //申请多少元
                $v['wallet_cash']  = round($admin_cash+$car_cash,2);

                $v['total_cash'] = $comm_model->where(['top_id'=>$v['id']])->where('status','>',-1)->where('type','in',[2,5,6,13,19,20])->sum('cash');

                $v['total_cash'] = round($v['total_cash'],2);

                $v['not_recorded_cash'] = $comm_model->where(['top_id'=>$v['id']])->where('status','=',1)->where('type','in',[2,5,6,13])->sum('cash');

                $v['not_recorded_cash'] = round($v['not_recorded_cash'],2);
            }
        }

        $name = $this->agent_name.'佣金记录-'.date('Y-m-d H:i:s');

        $header=[
            '关联用户ID',
            $this->agent_name.'姓名',
            '代理等级',
            '手机号',
            '是否删除',
            '总佣金',
            '已提现',
            '未入账',
            '可提现金额',
        ];
        $new_data =[];

        if(!empty($list)){

            foreach ($list as $vs){

                $info   = array();

                $info[] = $vs['user_id'];

                $info[] = $vs['agent_name'];

                $info[] = $this->getAgentType($vs['city_type']);

                $info[] = $vs['phone'];

                $info[] = $vs['status']==1?'否':'是';

                $info[] = $vs['total_cash'];

                $info[] = $vs['wallet_cash'];

                $info[] = $vs['not_recorded_cash'];

                $info[] = $vs['cash'];

                $new_data[] = $info;
            }
        }

        $excel = new Excel();

        $new_data[] = [$this->agent_name.'佣金余额总计:'.round($total_cash,2)];

        $excel->excelCsv($name,$header,$new_data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-23 10:22
     * @功能说明:分销员统计
     */
    public function resellerFinanceList(){

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

        $data = $reseller_model->userDataSelect($dis,$where);

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $del_time = $reseller_model->where(['user_id'=>$v['user_id'],'status'=>-1])->max('del_time');

                $fx_check = !empty($del_time)?1:0;
                //总佣金
                $v['total_cash'] = $reseller_model->getOrderPrice($v['user_id'],$fx_check,$del_time,0,0);
                //累计提现
                $v['wallet_cash']= $reseller_model->getWalletCash($v['user_id'],$fx_check,$del_time);
                //未入账
                $v['not_recorded_cash'] = $reseller_model->getOrderPrice($v['user_id'],$fx_check,$del_time,0,1);
            }
        }

        $name = $this->reseller_name.'佣金记录-'.date('Y-m-d H:i:s');

        $header=[
            '关联用户ID',
            $this->reseller_name.'姓名',
            '手机号',
            '总佣金',
            '已提现',
            '未入账',
            '可提现',
        ];
        $new_data =[];

        if(!empty($data['data'])){

            foreach ($data['data'] as $vs){

                $info   = array();

                $info[] = $vs['user_id'];

                $info[] = !empty($vs['user_name'])?$vs['user_name']:$vs['nickName'];

                $info[] = $vs['mobile'];

                $info[] = $vs['total_cash'];

                $info[] = $vs['wallet_cash'];

                $info[] = $vs['not_recorded_cash'];

                $info[] = $vs['new_cash'];

                $new_data[] = $info;
            }
        }

        $excel = new Excel();

        $new_data[] = [$this->reseller_name.'佣金余额总计:'.round($data['cash'],2)];

        $excel->excelCsv($name,$header,$new_data);
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-21 11:17
     * @功能说明:业务员列表
     */
    public function salesmanFinanceList(){

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

        $comm_model     = new Commission();

        $wallet_model   = new Wallet();

        $data = $salesman_model->where($dis)->order('id desc')->select()->toArray();

        if(!empty($data)){

            foreach ($data as &$v){

                $v['total_cash'] = $comm_model->where(['top_id'=>$v['id'],'type'=>12])->where('status','>',-1)->sum('cash');

                $v['not_recorded_cash'] = $comm_model->where(['top_id'=>$v['id'],'type'=>12])->where('status','=',1)->sum('cash');

                $v['wallet_cash'] = $wallet_model->where(['coach_id'=>$v['id'],'type'=>6])->where('status','<>',3)->sum('total_price');

                $v['total_cash'] = round($v['total_cash'],2);

                $v['not_recorded_cash'] = round($v['not_recorded_cash'],2);

                $v['wallet_cash'] = round($v['wallet_cash'],2);
            }
        }

        $total_cash = $salesman_model->where($dis)->sum('cash');

        $name = '业务员佣金记录-'.date('Y-m-d H:i:s');

        $header=[
            '关联用户ID',
            '业务员姓名',
            '手机号',
            '总佣金',
            '已提现',
            '未入账',
            '可提现',
        ];
        $new_data =[];

        if(!empty($data)){

            foreach ($data as $vs){

                $info   = array();

                $info[] = $vs['user_id'];

                $info[] = $vs['user_name'];

                $info[] = $vs['phone'];

                $info[] = $vs['total_cash'];

                $info[] = $vs['wallet_cash'];

                $info[] = $vs['not_recorded_cash'];

                $info[] = $vs['cash'];

                $new_data[] = $info;
            }
        }

        $excel = new Excel();

        $new_data[] = ['业务员佣金余额总计:'.round($total_cash,2)];

        $excel->excelCsv($name,$header,$new_data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-08-30 11:30
     * @功能说明:渠道商列表
     */
    public function channelFinanceList(){

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

        $comm_model     = new Commission();

        $wallet_model   = new Wallet();

        $data = $channel_model->where($dis)->order('id desc')->select()->toArray();

        if(!empty($data)){

            foreach ($data as &$v){

                $v['total_cash'] = $comm_model->where(['top_id'=>$v['id'],'type'=>10])->where('status','>',-1)->sum('cash');

                $v['not_recorded_cash'] = $comm_model->where(['top_id'=>$v['id'],'type'=>10])->where('status','=',1)->sum('cash');

                $v['wallet_cash'] = $wallet_model->where(['coach_id'=>$v['id'],'type'=>5])->where('status','<>',3)->sum('total_price');

                $v['total_cash'] = round($v['total_cash'],2);

                $v['not_recorded_cash'] = round($v['not_recorded_cash'],2);

                $v['wallet_cash'] = round($v['wallet_cash'],2);
            }
        }

        $total_cash = $channel_model->where($dis)->sum('cash');

        $name = $this->channel_name.'佣金记录-'.date('Y-m-d H:i:s');

        $header=[
            '关联用户ID',
            $this->channel_name.'姓名',
            '手机号',
            '总佣金',
            '已提现',
            '未入账',
            '可提现',
        ];
        $new_data =[];

        if(!empty($data)){

            foreach ($data as $vs){

                $info   = array();

                $info[] = $vs['user_id'];

                $info[] = $vs['user_name'];

                $info[] = $vs['mobile'];

                $info[] = $vs['total_cash'];

                $info[] = $vs['wallet_cash'];

                $info[] = $vs['not_recorded_cash'];

                $info[] = $vs['cash'];

                $new_data[] = $info;
            }
        }

        $excel = new Excel();

        $new_data[] = [$this->channel_name.'佣金余额总计:'.round($total_cash,2)];

        $excel->excelCsv($name,$header,$new_data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-15 14:43
     * @功能说明:列表
     */
    public function brokerFinanceList(){

        $input = $this->_param;

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['status','in',[2,3]];

        if(!empty($input['name'])){

            $dis[] = ['user_name','like','%'.$input['name'].'%'];
        }

        $broker_model = new CoachBroker();

        $comm_model   = new Commission();

        $wallet_model = new Wallet();

        $data = $broker_model->where($dis)->order('id desc')->select()->toArray();

        if(!empty($data)){

            foreach ($data as &$v){

                $v['total_cash'] = $comm_model->where(['broker_id'=>$v['id'],'type'=>9])->where('status','>',-1)->sum('cash');

                $v['not_recorded_cash'] = $comm_model->where(['broker_id'=>$v['id'],'type'=>9])->where('status','=',1)->sum('cash');

                $v['wallet_cash'] = $wallet_model->where(['coach_id'=>$v['id'],'type'=>10])->where('status','<>',3)->sum('total_price');

                $v['total_cash'] = round($v['total_cash'],2);

                $v['not_recorded_cash'] = round($v['not_recorded_cash'],2);

                $v['wallet_cash'] = round($v['wallet_cash'],2);
            }
        }

        $total_cash = $broker_model->where($dis)->sum('cash');

        $name = $this->broker_name.'佣金记录-'.date('Y-m-d H:i:s');

        $header=[
            '关联用户ID',
            $this->broker_name.'姓名',
            '手机号',
            '总佣金',
            '已提现',
            '未入账',
            '可提现',
        ];
        $new_data =[];

        if(!empty($data)){

            foreach ($data as $vs){

                $info   = array();

                $info[] = $vs['user_id'];

                $info[] = $vs['user_name'];

                $info[] = $vs['mobile'];

                $info[] = $vs['total_cash'];

                $info[] = $vs['wallet_cash'];

                $info[] = $vs['not_recorded_cash'];

                $info[] = $vs['cash'];

                $new_data[] = $info;
            }
        }

        $excel = new Excel();

        $new_data[] = [$this->broker_name.'佣金余额总计:'.round($total_cash,2)];

        $excel->excelCsv($name,$header,$new_data);
    }





}
