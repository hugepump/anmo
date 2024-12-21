<?php
namespace app\massage\controller;
use app\AdminRest;
use app\massage\model\CashUpdateRecord;
use app\massage\model\ChannelScanQr;
use app\massage\model\City;
use app\massage\model\Coach;
use app\massage\model\CoachTimeList;
use app\massage\model\Comment;
use app\massage\model\Commission;
use app\massage\model\ExpectationCityList;
use app\massage\model\Order;
use app\massage\model\User;
use think\App;
use app\shop\model\Order as Model;
use think\facade\Db;


class AdminIndex extends AdminRest
{


    protected $model;

    protected $order_goods_model;

    protected $refund_order_model;

    protected $comment_model;

    public function __construct(App $app) {

        parent::__construct($app);

        $this->model = new Order();

        $this->refund_order_model = new \app\massage\model\RefundOrder();

        $this->comment_model = new Comment();

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-05-25 15:16
     * @功能说明:销售额订单数据
     */
    public function orderData(){

        $input = $this->_param;

        $dis[] = ['uniacid','=',$this->_uniacid];

        $where[] = ['b.uniacid','=',$this->_uniacid];

        $where[] = ['a.status','=',2];

        $dis[] = ['pay_time','>',0];

        $where[] = ['b.pay_time','>',0];

        if($this->_user['is_admin']==0){

            $dis[] = ['admin_id','in',$this->admin_arr];

            $where[] = ['b.admin_id','in',$this->admin_arr];
        }
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
                $shop_price = $this->model->where($dis)->whereMonth('create_time',$time_text)->sum('service_price');
                //录入订单
                $arr[$i]['shop_price']  = round($shop_price,2);

                $refund_cash = $this->refund_order_model->alias('a')
                    ->join('massage_service_order_list b','a.order_id = b.id')
                    ->whereMonth('b.create_time',$time_text)
                    ->where($where)
                    ->group('a.id')
                    ->sum('refund_service_price');

                $arr[$i]['refund_cash']  = round($refund_cash,2);

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

                $shop_price = $this->model->where($dis)->whereDay('create_time',$time_text)->sum('service_price');

                $arr[$i]['shop_price']  = round($shop_price,2);

                $refund_cash = $this->refund_order_model->alias('a')
                    ->join('massage_service_order_list b','a.order_id = b.id')
                    ->whereDay('b.create_time',$time_text)
                    ->where($where)
                    ->group('a.id')
                    ->sum('refund_service_price');

                $arr[$i]['refund_cash']  = round($refund_cash,2);

                $start += 86400;

                $i++;
            }
        }

        $today_price = $this->model->where($dis)->whereDay('create_time','today')->sum('service_price');

        $refund_cash = $this->refund_order_model->alias('a')
            ->join('massage_service_order_list b','a.order_id = b.id')
            ->whereDay('b.create_time','today')
            ->where($where)
            ->group('a.id')
            ->sum('refund_service_price');

        $list['today_price'] = round($today_price-$refund_cash,2);

        $list['data'] = $arr;

        return $this->success($list);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-05-15 14:43
     * @功能说明:代理商统计排行
     */
    public function agentOrderData(){

        $input = $this->_param;

        $admin_model = new \app\massage\model\Admin();

        $date = strtotime(date('Y-m-d',time()));

        $end_time = time();

        if($input['day']==1){

            $start_time = $date;

        }elseif ($input['day']==2){

            $start_time = $date-6*86400;

        }elseif ($input['day']==3){

            $start_time = $date-29*86400;

        }elseif ($input['day']==4){

            $start_time = strtotime(date('Y-01-01',time()));

        }else{
            $start_time = $input['start_time'];

            $end_time = $input['end_time'];
        }

        $dis[] = ['a.uniacid','=',$this->_uniacid];

        $dis[] = ['a.status','=',1];

        $dis[] = ['a.is_admin','=',0];

       // $dis[] = ['a.city_type','in',[0,1]];

        if($this->_user['is_admin']==0){

            $dis[] = ['a.id','in',$this->admin_arr];
        }

        $input['limit'] = !empty($input['limit'])?$input['limit']:10;

        $data = $admin_model->agentDataTop($dis,$input['limit'],$start_time,$end_time);

        return $this->success($data);
    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-10-21 13:52
     * @功能说明:代理商订单数据
     * 124728
     */
    public function agentOrderDataV2(){

        $input = $this->_param;

        $admin_model = new \app\massage\model\Admin();

        $dis = [

            'uniacid'   => $this->_uniacid,

            'status'    => 1,

            'is_admin'  => 0,

            'city_type' => 1
        ];

        $where = [];

        if($this->_user['is_admin']==0){

            $where[] = ['id','in',$this->admin_arr];
        }

        $list = $admin_model->where($dis)->where($where)->limit(20)->select()->toArray();

        if(!empty($list)){

            foreach ($list as &$value){

                $dis = [];

                $admin_id = $admin_model->getAdminAndSon($value['id']);

                $dis[] = ['admin_id','in',$admin_id];

                $dis[] = ['coach_refund_time','=',0];

              //  $dis[] = ['pay_type','>',1];

                if($input['day']==4){
                    //全年
                    $price = $this->model->where($dis)->where('pay_time','>',0)->whereTime('create_time','year')->sum("true_service_price");

                }else{
                    //自定义时间
                    if($input['day']==5){

                        $start = $input['start_time'];

                        $end   = $input['end_time'];

                        $price = $this->model->where($dis)->where('pay_time','>',0)->where('create_time','between',"$start,$end")->sum('true_service_price');

                    }else{
                        //今日
                        if($input['day']==1){

                            $price = $this->model->where($dis)->where('pay_time','>',0)->whereTime('create_time','today')->sum('true_service_price');

                        }elseif ($input['day']==2){
                        //近7日
                            $price = $this->model->where($dis)->where('pay_time','>',0)->whereTime('create_time','-7 days')->sum('true_service_price');

                        }else{
                        //近30日
                            $price = $this->model->where($dis)->where('pay_time','>',0)->whereTime('create_time','-30 days')->sum('true_service_price');

                        }
                    }
                }
                $value['sale_price'] = round($price,2);
            }
        }

        $list = arraySort($list,'sale_price','desc');

        return $this->success($list);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-06-19 11:45
     * @功能说明:城市统计
     */
    public function cityData(){
        //城市
        $dis = [

            'uniacid' => $this->_uniacid,

            'status'  => 1,

            'city_type'=> 1

        ];

        $city_model = new City();

        $data['city_list'] = $city_model->where($dis)->select()->toArray();

        $dis['city_type'] = 3;

        $data['province_list'] = $city_model->where($dis)->field('id,title,lng,lat')->select()->toArray();

        $dis['city_type'] = 2;

        $data['area_list'] = $city_model->where($dis)->field('id,title,lng,lat')->select()->toArray();

        $admin_model = new \app\massage\model\Admin();

        $data['admin_count'] = $admin_model->where(['is_admin'=>0,'status'=>1])->count();

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-10-21 14:20
     * @功能说明:技师用户数据
     */
    public function coachAndUserData(){

        $dis = [

            'uniacid' => $this->_uniacid,

            'status'  => 3
        ];

        $where = [];

        if($this->_user['is_admin']==0){

            $where[] = ['admin_id','in',$this->admin_arr];
        }

        $coach_model = new Coach();
        //解约技师
        $data['cancel_coach'] = $coach_model->where($dis)->where($where)->count();

        $dis['status'] = 2;
        //全部技师
        $data['total_coach'] = $coach_model->where($dis)->where($where)->count();

        $data['sex']['man']['count'] = $coach_model->where($dis)->where($where)->where(['sex'=>0])->count();

        $data['sex']['man']['balance'] = $data['total_coach']>0?round($data['sex']['man']['count']/$data['total_coach']*100,2):0;

        $data['sex']['woman']['count'] = $coach_model->where($dis)->where($where)->where(['sex'=>1])->count();

        $data['sex']['woman']['balance'] = $data['total_coach']>0?round($data['sex']['woman']['count']/$data['total_coach']*100,2):0;
        //已经绑定技师
        $data['bind_coach']  = $coach_model->where($dis)->where($where)->where('user_id','>',0)->count();

        $data['nobind_coach']= $coach_model->where($dis)->where($where)->where('user_id','=',0)->count();

        $work = CoachTimeList::getWorkOrResetCoach($this->_uniacid);

        $reset = CoachTimeList::getWorkOrResetCoach($this->_uniacid,2);

        $working_coach = $coach_model->getWorkingCoach($this->_uniacid);
        //休息技师
        $data['rest_coach'] = $coach_model->where($dis)->where($where)->where('id','in',$reset)->count();
        //可服务
        $data['app_coach']  = $coach_model->where($dis)->where($where)->where('id','in',$working_coach)->count();
        //在线
        $data['work_coach'] = $coach_model->where($dis)->where($where)->where('id','in',$work)->count();

        $city_model = new City();

        $dis = [

            'status'    => 1,

            'city_type' => 3
        ];

        $province = $city_model->where($dis)->field('id,title,province_code')->select()->toArray();

        if(!empty($province)){

            foreach ($province as &$value){

                $city = $city_model->where(['status'=>1,'province_code'=>$value['province_code']])->column('id');

                $dis= [

                    'uniacid' => $this->_uniacid,

                    'status'  => 2
                ];

                $value['coach_count'] = $coach_model->where($dis)->where($where)->where('city_id','in',$city)->count();
            }
        }
        //省分布技师
        $data['province_coach'] = $province;

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-10-21 14:48
     * @功能说明:技师销售数据
     */
    public function coachSaleData(){

        $input = $this->_param;

        $dis[] = ['a.uniacid','=',$this->_uniacid];

        $dis[] = ['a.status','=',2];

        $dis[] = ['b.coach_refund_time','=',0];

        if(!empty($input['start_time'])){

            $dis[] = ['b.create_time','between',"{$input['start_time']},{$input['end_time']}"];
        }

        $coach_model = new Coach();

        $data = $coach_model->alias('a')
                ->join('massage_service_order_list b','a.id = b.coach_id','left')
                ->where($dis)
                ->where('b.pay_type','>',1)
                ->field('a.coach_name,a.id,SUM(b.coach_cash) as total_coach_cash,a.work_img')
                ->group('a.id')
                ->order('total_coach_cash desc,a.id desc')
                ->paginate($input['limit'])
                ->toArray();

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $v['coach_level'] = $coach_model->getCoachLevel($v['id'],$this->_uniacid);
            }
        }

        $list['yesterday_count'] = $this->model->where(['uniacid'=>$this->_uniacid,'coach_refund_time'=>0])->where('pay_time','>',0)->whereTime('create_time','yesterday')->count();

        $price = $this->model->where(['uniacid'=>$this->_uniacid,'coach_refund_time'=>0])->where('pay_time','>',0)->whereTime('create_time','yesterday')->sum('true_service_price');

        $list['yesterday_price'] = round($price,2);

        $list['list'] = $data;

        return $this->success($list);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-06-19 10:31
     * @功能说明:首页数据概括
     */
    public function indexData(){

        $dis[] = ['pay_type','>',1];

        $dis[] = ['uniacid','=',$this->_uniacid];

        if($this->_user['is_admin']==0){

            $dis[] = ['admin_id','in',$this->admin_arr];
        }

        $data['today_order_cash']  = $this->model->where($dis)->whereTime('create_time','today')->sum('true_service_price');

        $data['today_order_count'] = $this->model->where($dis)->whereTime('create_time','today')->count();

        $user_model = new User();

        $data['today_user_count'] = $user_model->where(['uniacid'=>$this->_uniacid])->whereTime('create_time','today')->count();

        $data['today_order_cash'] = round($data['today_order_cash'],2);

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-06-19 10:54
     * @功能说明:用户统计
     */
    public function userData(){

        $input = $this->_param;

        if(!empty($input['start_time'])){

            $start = $input['start_time'];

            $end   = $input['end_time'];

        }else{

            $end = strtotime(date('Y-m-d',time()));

            $start = $end-86400*59;
        }

        $user_model = new User();

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['status','=',1];

        $i = 0;

        while ($start<=$end){

            $arr[$i]['time'] = $start;

            $time_text = date('Y-m-d',$start);

            $arr[$i]['time_text'] = $time_text;

            $shop_price = $user_model->where($dis)->whereDay('create_time',$time_text)->count();

            $arr[$i]['user_count']  = round($shop_price,2);

            $start += 86400;

            $i++;
        }

        $data['list'] = $arr;

        $data['total']= $user_model->where(['uniacid'=>$this->_uniacid,'status'=>1])->count();

        $arr = [
            //未消费          一次消费        多次消费        流失客户
            'orderNoUser','orderOneUser','orderTwoUser','lossUser'
        ];

        foreach ($arr as $k=>$value){

            $balance[$k]['count']   = $user_model->$value($this->_uniacid);

            $balance[$k]['title']   = $value;

            $balance[$k]['balance'] = $data['total']>0?round($balance[$k]['count']/$data['total']*100,2):0;
        }

        $data['balance'] = array_values($balance);

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-06-20 13:58
     * @功能说明:获取技师地图数据
     */
    public function getMapCoach(){

        $input = $this->_param;

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['status','=',2];

        if(!empty($input['city_id'])){

            $dis[] = ['city_id','=',$input['city_id']];

        }

        $coach_model = new Coach();

        $data = $coach_model->where($dis)->field('id as coach_id,lng,lat,coach_name,work_img')->select()->toArray();

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-07-24 14:32
     * @功能说明:期待开通城市列表
     */
    public function expectationCityList(){

        $input = $this->_param;

        $list_model = new ExpectationCityList();

        $dis = [

            'uniacid' => $this->_uniacid
        ];

        $data = $list_model->where($dis)->order('num desc,id desc')->paginate($input['limit'])->toArray();

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-07-24 14:34
     * @功能说明:修改期待开通城市
     */
    public function expectationCityUpdate(){

        $input = $this->_param;

        $list_model = new ExpectationCityList();

        $dis = [

            'id' => $input['id']
        ];

        $data = $list_model->where($dis)->update(['status'=>2]);

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-04 09:54
     * @功能说明:数据大屏
     */
    public function dataScreen(){

        $dis = [

            'uniacid' => $this->_uniacid,

            'status'  => 2
        ];

        $where = [];

        if($this->_user['is_admin']==0){

            $where[] = ['admin_id','in',$this->admin_arr];
        }

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

        if($this->_user['is_admin']==0){

            $dis[] = ['admin_id','in',$this->admin_arr];
        }
        //订单信息
        $data['order']['today_order_cash']  = $this->model->where($dis)->whereTime('create_time','today')->sum('true_service_price');

        $data['order']['today_order_cash'] = round($data['order']['today_order_cash'],2);

        $data['order']['today_order_count'] = $this->model->where($dis)->whereTime('create_time','today')->count();

        $data['order']['total_order_cash']  = $this->model->where($dis)->sum('true_service_price');

        $data['order']['total_order_cash'] = round($data['order']['total_order_cash'],2);

        $data['order']['total_order_count'] = $this->model->where($dis)->count();
        //技师排行
        $dis = [];

        $dis[] = ['b.pay_type','>',1];

        $dis[] = ['a.status','=',2];

        $dis[] = ['a.uniacid','=',$this->_uniacid];

        if($this->_user['is_admin']==0){

            $dis[] = ['a.admin_id','in',$this->admin_arr];
        }
        $coach_data = $coach_model->alias('a')
                      ->join('massage_service_order_list b','a.id = b.coach_id')
                      ->where($dis)
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

        if($this->_user['is_admin']==0){

            $dis[] = ['admin_id','in',$this->admin_arr];
        }

        $data['new_order'] = $this->model->where($dis)->order('id desc')->limit(10)->select()->toArray();
        //城市
        $dis = [

            'uniacid' => $this->_uniacid,

            'status'  => 1,

            'city_type'=> 1
        ];

        $city_model = new City();

        $data['city_list'] = $city_model->where($dis)->field('city,lng,lat')->select()->toArray();

        $end = strtotime(date('Y-m-d',time()))+86400;

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

            $arr[$i]['time_text'] = $time_text;

            $user_count = $user_model->where($dis)->whereDay('create_time',$time_text)->count();

            $order_user_id = $this->model->where('pay_time','>',0)->whereDay('create_time',$time_text)->column('user_id');

            $order_user_count = $user_model->where($dis)->where('id','in',$order_user_id)->whereDay('create_time',$time_text)->count();
            //新用户
            $arr[$i]['new_user_count']  = round($user_count,2);
            //下单用户
            $arr[$i]['order_user_count'] = round($order_user_count,2);

            $start += 86400;

            $i++;
        }
        $data['user_data'] = $arr;

        return $this->success($data);
    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-05-25 15:16
     * @功能说明:获取平台数据
     */
    public function adminData(){

        $input = $this->_param;

        $scan_model = new ChannelScanQr();

        $user_model = new User();

        $order_model= new Order();

        $comm_model = new Commission();

        $order_model->initOrderData($this->_uniacid);

        $date = strtotime(date('Y-m-d'));

        if($input['day_type']==1){

            $start_time = $date;

            $end_time   = time();

        }elseif ($input['day_type']==2){

            $start_time = $date-6*86400;

            $end_time   = time();

        }elseif ($input['day_type']==3){

            $start_time = $date-29*86400;

            $end_time   = time();

        }elseif ($input['day_type']==4){

            $start_time = strtotime(date("Y",time())."-1"."-1");

            $end_time   = time();

        }else{

            $start_time = $input['start_time'];

            $end_time   = $input['end_time'];
        }

        $where = [];

        if($this->_user['is_admin']==0){

            $where[] = ['admin_id','=',$this->_user['admin_id']];
        }
        //扫码量
        $data['scan_code_num'] = $scan_model->where(['uniacid'=>$this->_uniacid,'is_qr'=>1])->where('create_time','between',"$start_time,$end_time")->count();
        //注册量
        $data['register_num']  = $user_model->where(['uniacid'=>$this->_uniacid,'status'=>1])->where('create_time','between',"$start_time,$end_time")->count();
        //关注量
        $data['follow_num']    = $user_model->where(['uniacid'=>$this->_uniacid,'status'=>1,'is_qr'=>0])->where('create_time','between',"$start_time,$end_time")->count();
        //订单量
        $data['order_num'] = $order_model->where($where)->where(['uniacid'=>$this->_uniacid])->where('pay_time','>',0)->where('create_time','between',"$start_time,$end_time")->count();
        //销售额
        $data['order_cash']= $order_model->where($where)->where(['uniacid'=>$this->_uniacid])->where('pay_time','>',0)->where('create_time','between',"$start_time,$end_time")->sum('pay_price');
        //主订单量
        $data['host_order_num']= $order_model->where($where)->where(['uniacid'=>$this->_uniacid,'is_add'=>0])->where('pay_time','>',0)->where('create_time','between',"$start_time,$end_time")->count();
        //车费
        $data['car_cash'] = $order_model->where($where)->where(['uniacid'=>$this->_uniacid,'free_fare'=>0])->where('pay_time','>',0)->where('create_time','between',"$start_time,$end_time")->sum('car_price');
        //主订单金额
        $data['host_order_cash'] = $order_model->where($where)->where(['uniacid'=>$this->_uniacid,'is_add'=>0])->where('pay_time','>',0)->where('create_time','between',"$start_time,$end_time")->sum('service_price');
        //首单退款数量
        $data['host_refund_num'] = $this->refund_order_model->financeOrderRefundCash($this->_uniacid,$start_time,$end_time,0,0,0,2,$this->_user,$this->admin_arr);
        //首单退款金额
        $data['host_refund_cash']= $this->refund_order_model->financeOrderRefundCash($this->_uniacid,$start_time,$end_time,0,0,0,1,$this->_user,$this->admin_arr,'a.refund_service_price');
        //加钟订单量
        $data['add_order_num']  = $order_model->where($where)->where(['uniacid'=>$this->_uniacid,'is_add'=>1])->where('pay_time','>',0)->where('create_time','between',"$start_time,$end_time")->count();
        //加钟金额
        $data['add_order_cash'] = $order_model->where($where)->where(['uniacid'=>$this->_uniacid,'is_add'=>1])->where('pay_time','>',0)->where('create_time','between',"$start_time,$end_time")->sum('service_price');
        //加钟退款数量
        $data['add_refund_num'] = $this->refund_order_model->financeOrderRefundCash($this->_uniacid,$start_time,$end_time,0,0,1,2,$this->_user,$this->admin_arr);
        //加钟退款金额
        $data['add_refund_cash'] = $this->refund_order_model->financeOrderRefundCash($this->_uniacid,$start_time,$end_time,0,0,1,1,$this->_user,$this->admin_arr,'a.refund_service_price');
        //优惠券抵扣
        $data['coupon_discount'] = $order_model->where($where)->where(['uniacid'=>$this->_uniacid])->where('pay_time','>',0)->where('create_time','between',"$start_time,$end_time")->sum('discount');
        //主订单物料费
        $data['total_material_cash']= $order_model->where($where)->where(['uniacid'=>$this->_uniacid])->where('start_material_price','>',0)->where('pay_time','>',0)->where('create_time','between',"$start_time,$end_time")->sum('start_material_price');

        $data['refund_material_cash'] = $this->refund_order_model->financeOrderRefundCash($this->_uniacid,$start_time,$end_time,0,0,2,1,$this->_user,$this->admin_arr,'a.refund_material_price');

        $data['refund_car_cash'] = $this->refund_order_model->financeOrderRefundCash($this->_uniacid,$start_time,$end_time,0,0,0,1,$this->_user,$this->admin_arr,'a.refund_car_price');
        //渠道商 业务员 分销员 经纪人 佣金总额
        $data['total_comm_cash'] = $comm_model->where($where)->where('status','=',2)->where('type','in',[1,3,9,10,12,14])->where('create_time','between',"$start_time,$end_time")->sum('cash');
        //净销售额
        $data['noly_order_cash'] = $order_model->where($where)->where(['uniacid'=>$this->_uniacid])->where('pay_type','=',7)->where('create_time','between',"$start_time,$end_time")->sum('true_service_price');

        foreach ($data as $k=>$v){

            $data[$k] = round($v,2);
        }

        return $this->success($data);
    }


}
