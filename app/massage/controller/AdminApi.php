<?php
namespace app\massage\controller;
use AlibabaCloud\Client\AlibabaCloud;
use app\AdminRest;
use app\BaseController;
use app\industrytype\model\Type;
use app\massage\info\PermissionMassage;
use app\massage\model\ActionLog;
use app\massage\model\ArticleList;
use app\massage\model\CashUpdateRecord;
use app\massage\model\City;
use app\massage\model\Coach;
use app\massage\model\Commission;
use app\massage\model\Config;
use app\massage\model\ConfigSetting;
use app\massage\model\Order;
use app\massage\model\OrderAddress;
use app\massage\model\OrderGoods;
use app\massage\model\Police;
use app\massage\model\ResellerRecommendCash;
use app\massage\model\ShortCodeConfig;
use app\massage\model\Wallet;
use Exception;
use longbingcore\wxcore\Adapay;
use longbingcore\wxcore\Excel;
use think\App;

use app\massage\model\CoachTimeList;
use app\massage\model\Comment;
use app\massage\model\ExpectationCityList;


use app\massage\model\User;


use think\facade\Cookie;
use think\facade\Db;
use think\facade\Lang;
use think\Response;

class AdminApi extends BaseController
{


    protected $model;

    protected $config_model;

    protected $uniacid;
    protected $_uniacid;



    public function __construct(App $app) {

        parent::__construct($app);

        $this->model = new \app\massage\model\Admin();

        $this->uniacid = 666;

        $this->_uniacid = 666;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-19 16:36
     * @功能说明:获取版权信息
     */
    public function getW7TmpV2(){

        $copyright = getConfigSetting(666,'copyright');

        if(!empty($copyright)){

            $arr['w7tmp']['footerleft'] = "<div> $copyright </div>";
        }else{

            $arr['w7tmp'] = 1;
        }

        return $this->success($arr);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-24 11:41
     * @功能说明:是否需要手机号验证
     */
    public function authPhone(){

        $phone = getConfigSetting(666,'login_phone_auth');

        $auth = $phone==1&&empty(cookie('getAuthPhone'))?1:0;

        return $this->success($auth);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-24 13:51
     * @功能说明:发送短信验证码
     */
    public function sendAuthCode(){

        $input = json_decode( $this->request->getInput(), true );

        $dis = [

            'status'  => 1,

            'username'=> $input['username'],

            'passwd'  => checkPass($input['passwd'])
        ];

        $data = $this->model->dataInfo($dis);

        if(empty($data)){

            return $this->error('密码错误', 400);
        }
        //超级管理员需要获取平台授权手机号
        if($data['is_admin']==1){

            $phone = getConfigSetting($data['uniacid'],'login_auth_phone');

        }else{

            $phone = $data['login_auth_phone'];
        }

        if(empty($phone)){

            return $this->error('未获取到授权手机号，请联系开发平台', 400);
        }

        $config_model = new ShortCodeConfig();

        $res = $config_model->loginShortConfig($phone,$data['uniacid']);

        if(!empty($res['Message'])&&$res['Message']=='OK'){

            return $this->success($phone);

        }else{

            return $this->error($res['Message']);
        }
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-24 13:51
     * @功能说明:发送短信验证码
     */
    public function sendAuthCodeV2(){

        $input = json_decode( $this->request->getInput(), true );

        $phone = getConfigSetting($this->_uniacid,'login_auth_phone');

        if(!empty($input['phone'])){

            $phone = $input['phone'];
        }

        if(empty($phone)){

            return $this->error('未获取到授权手机号，请联系开发平台', 400);
        }

        $config_model = new ShortCodeConfig();

        $res = $config_model->loginShortConfigV2($phone,$this->_uniacid);

        if(!empty($res['Message'])&&$res['Message']=='OK'){

            return $this->success($phone);

        }else{

            return $this->error($res['Message']);
        }
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-11 13:53
     * @功能说明:登陆
     */
    public function login(){

        initLogin();

        $input = json_decode( $this->request->getInput(), true );

        $codeText = cookie('codeText');
        //判断是否需要短信验证
        $phone_auth = getConfigSetting(666,'login_phone_auth');

        $auth = $phone_auth==1&&empty(cookie('getAuthPhone'))?1:0;

        if($auth==0&&$codeText!=$input['codeText']){

            return $this->error('验证码错误');
        }

        $ip = getIP();

        $key = $ip.'errss_numsss';

        $err_num = getCache($key,66661);

        $err_num = !empty($err_num)?$err_num:0;

        if($err_num>=5){

            return $this->error('密码错误超过5次，请2小时后再试', 400);

        }

        $err_num+=1;

        setCache($key,$err_num,7200,66661);

        $dis = [

            'status'  => 1,

            'username'=> $input['username'],

            'passwd'  => checkPass($input['passwd'])
        ];

        $data = $this->model->dataInfo($dis);

        $login_num = 5 - $err_num;

        if(empty($data)){

            if($login_num<=0){

                return $this->error('密码错误超过5次，请2小时后再试', 400);

            }else{

                return $this->error('账号密码错误，你还剩'.$login_num.'次机会', 400);
            }
        }
        //需要验证码
        if($auth==1){
            //判断是否是超级管理员账号
            $short_phone = $data['is_admin']==1?getConfigSetting(666,'login_auth_phone'):$data['login_auth_phone'];

            $short_code  = getCache($short_phone.'login',$data['uniacid']);

            if(empty($input['short_code'])||$short_code!=$input['short_code']){

                return $this->error('短信验证码错误', 400);
            }

            \cookie('getAuthPhone',1,86400*30);

            setCache($short_phone.'login','',99,$data['uniacid']);
        }

        setCache($key,0,7200,66661);

        $result['user'] = $data;

        $result['token'] = uuid();

        if (empty($result['token'])) {

            return $this->error('系统错误', 400);
        }
        //添加缓存数据
        setUserForToken($result['token'], $data);

        $this->addActionLog($data['id']);

        return $this->success($result);
    }


    /**
     * @param $user_id
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-29 10:03
     */
    public function addActionLog($user_id){

        $insert = [

            'uniacid'     => 666,

            'user_id'     => $user_id,

            'obj_id'      => 0,

            'ip'          => getIP(),

            'model'       => 'Admin',

            'method'      => 'POST',

            'table'       => 'shequshop_school_admin',

            'code_action' => 'login',

            'action_type' => '',

            'action'      => 'login',

        ];

        $log_model = new ActionLog();

        $log_model->dataAdd($insert);

        return true;
    }



    public function success ( $data, $code = 200 )
    {
        $result[ 'data' ] = $data;
        $result[ 'code' ] = $code;
        $result[ 'sign' ] = null;
        //简单的签名
        if ( !empty( $this->_token ) ) $result[ 'sign' ] = createSimpleSign( $this->_token, is_string( $data ) ? $data : json_encode( $data ) );

        return $this->response( $result, 'json', $code  );
    }

    //返回错误数据
    public function error ( $msg, $code = 400 )
    {
        $result[ 'error' ] = Lang::get($msg);
        $result[ 'code' ]  = $code;
        return $this->response( $result, 'json', 200 );
    }

    /**
     * 输出返回数据
     * @access protected
     * @param mixed $data 要返回的数据
     * @param String $type 返回类型 JSON XML
     * @param integer $code HTTP状态码
     * @return Response
     */
    protected function response ( $data, $type = 'json', $code = 200 )
    {
        return Response::create( $data, $type )->code( $code );
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-08-11 11:33
     * @功能说明:
     */
    public function getConfig(){

        $config_model = new Config();

        $config = $config_model->dataInfo(['uniacid'=>666]);

        $data['record_type'] = $config['record_type'];

        $data['record_no'] = $config['record_no'];

        $data['login_des'] = getConfigSetting(666,'login_des');

        return $this->success($data);

    }




    public function excel(){
//
//        $a = Db::name('auth_domain_name')->where(['goods_id'=>19])->column('url');
//
//        $data = Db::name('admin_violation')->where(['goods_id'=>19])->where('url','not in',$a)->group('url')->select()->toArray();
//
//        $name = '按摩盗版';
//
//        $header=[
//            '域名',
//            '最后登录时间',
//        ];
//
//        foreach ($data as $k=>$v){
//
//            $info   = array();
//
//            $info[] = $v['url'];
//
//            $info[] = date('Y-m-d H:i:s',$v['last_time']);
//
//            if(!strstr($v['url'],':')&&!$this->checkipaddres($v['url'])){
//
//                if($v['last_time']>time()-30*86400){
//
//                    $new_data[] = $info;
//                }
//
//            }
//
//        }
//
//        $excel = new Excel();
//
//        $excel->excelExport($name,$header,$new_data);
//
//        return $this->success($data);
    }




    public function checkipaddres ($ipaddres) {

        $preg="/\A((([0-9]?[0-9])|(1[0-9]{2})|(2[0-4][0-9])|(25[0-5]))\.){3}(([0-9]?[0-9])|(1[0-9]{2})|(2[0-4][0-9])|(25[0-5]))\Z/";

        if(preg_match($preg,$ipaddres))return true;

        return false;

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-06-20 13:58
     * @功能说明:获取技师地图数据
     */
    public function getMapCoach(){

        $input = $this->_param;

        $dis[] = ['uniacid','=',666];

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
     * @DataTime: 2023-06-26 18:06
     * @功能说明:城市下拉框
     */
    public function citySelect(){

        $input = $this->_param;

        $city_model = new City();

        $dis[] = ['uniacid','=',666];

        $dis[] = ['status','=',1];

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
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-06-26 18:06
     * @功能说明:技师详情
     */
    public function coachInfo(){

        $input = $this->_param;

        $dis = [

            'id' => $input['id']
        ];

        $coach_model = new Coach();

        $data = $coach_model->where($dis)->withoutField('id_card,id_code,mobile,service_price')->find()->toArray();

        $config_model = new Config();

        $config= $config_model->dataInfo(['uniacid'=>$this->uniacid]);

        $data['text_type'] = $coach_model->getCoachWorkStatus($input['id'],$this->uniacid);

        $data['near_time'] = $coach_model->getCoachEarliestTime($input['id'],$config);

        $data['order_num'] += $data['total_order_num'];

        $type_model = new Type();

        $data['industry_data'] = $type_model->dataInfo(['id' => $data['industry_type']]);

        return $this->success($data);

    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-05-25 15:16
     * @功能说明:销售额订单数据
     */
    public function orderDataaa(){

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
     * @DataTime: 2023-08-04 09:54
     * @功能说明:数据大屏
     */
    public function dataScreenaa(){

        exit;
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

        $reset= CoachTimeList::getWorkOrResetCoach($this->_uniacid,2);

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

            $admin_model = new \app\massage\model\Admin();

            foreach ($coach_data as &$v){

                $v['order_price']= round($v['order_price'],2);

                $v['admin_name'] = $admin_model->where(['id'=>$v['admin_id'],'status'=>1])->value('agent_name');
            }
        }

        $data['coach_top'] = $coach_data;
        //最新订单
        $dis = [];

        $dis[] = ['pay_type','=',2];

        $dis[] = ['uniacid','=',$this->_uniacid];

        $data['new_order'] = $order_model->where($dis)->field('id as order_id,pay_price,create_time')->order('id desc')->limit(10)->select()->toArray();

        if(!empty($data['new_order'])){

            $address_model = new OrderAddress();

            $goods_model   = new OrderGoods();

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



    public function drawCashInfo(){

        $wallet = new Wallet();

        $adapay = new Adapay($this->_uniacid);

        $list = $wallet->where(['status'=>4,'online'=>4])->order('sh_time,id')->limit(10)->select()->toArray();

        if(!empty($list)){

            foreach ($list as  $value){

                $find = $adapay->drawCashInfo($value['adapay_code']);

                if(!empty($find)&&$find['status']=='succeeded'&&$find['cash_list'][0]['trans_stat']=='S'){

                    $wallet->dataUpdate(['id'=>$value['id']],['status'=>2]);
                }
            }
        }

        return $this->success(true);
    }


    public function checkCash(){

        $where1 = [];

       // $where1[] = ['id','=',15];

        $data = Db::name('massage_service_coach_list')->where($where1)->field('id,coach_name,check_cash,service_price,car_price')->where(['status'=>2])->order('id desc')->limit(0,800)->select();

        if(!empty($data)){

            foreach ($data as $v){

                $this->checkCoachCash($v);
            }
        }

        $where[] = ['check_cash','<>',0];

        $where[] = ['check_car_cash','<>',0];

        $list = Db::name('massage_service_coach_list')->where($where1)->field('user_id,id,coach_name,service_price,car_price,check_cash as 相差服务费,check_car_cash as 相差车费')->where(['status'=>2])->where(function ($query) use ($where){
            $query->whereOr($where);
        })->select()->toArray();

        $model = new CashUpdateRecord();


        if(!empty($list)){

            foreach ($list as $value){

                if($value['相差服务费']!=0){

                    $is_add = $value['相差服务费']<0?1:0;

                    $cash = abs($value['相差服务费']);

                    $insert = [

                        'uniacid' => 666,

                        'coach_id'=> $value['id'],

                        'user_id' => $value['user_id'],

                        'cash'    => $cash,

                        'is_add'  => $is_add,

                        'text'    => '平账用',

                        'type'    => 1,

                        'before_cash'=> $value['service_price'],

                        'create_user'=> 0,

                        'after_cash' => $is_add==1?$value['service_price']+$cash:$value['service_price']-$cash,

                        'admin_type' => -3,

                        'info_id'    => 0,

                        'admin_update_id' => 0,

                        'ip' => getIP()
                    ];

                    $model->dataAdd($insert);
                }

                if($value['相差车费']!=0){

                    $is_add = $value['相差车费']<0?1:0;

                    $cash = abs($value['相差车费']);

                    $insert = [

                        'uniacid' => 666,

                        'coach_id'=> $value['id'],

                        'user_id' => $value['user_id'],

                        'cash'    => $cash,

                        'is_add'  => $is_add,

                        'text'    => '平账用',

                        'type'    => 2,

                        'before_cash'=> $value['car_price'],

                        'create_user'=> 0,

                        'after_cash' => $is_add==1?$value['car_price']+$cash:$value['car_price']-$cash,

                        'admin_type' => -3,

                        'info_id'    => 0,

                        'admin_update_id' => 0,

                        'ip' => getIP()
                    ];

                    $model->dataAdd($insert);
                }

                dump($list);exit;
            }
        }

        dump($list);exit;

        return $this->success($list);

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-19 10:04
     * @功能说明:校验技师金额
     */
    public function checkCoachCash($coach){

        $order_model = new Order();

        $wallet_model= new Wallet();

        $comm_model  = new Commission();

        $coach_model = new Coach();

//        $service = $order_model->where(['pay_type'=>7,'coach_id'=>$coach['id']])->sum('coach_cash');
//
//        $service = 0;

        $balance_cash = $comm_model->where(['top_id'=>$coach['id'],'status'=>2])->where('type','in',[3,7,17,18,24,25])->sum('cash');

        $wallet_price = $wallet_model->where(['coach_id'=>$coach['id']])->where('type','=',1)->where('status','in',[1,2,4,5])->sum('total_price');

        $add_cash = Db::name('massage_coach_cash_update_record')->where(['coach_id'=>$coach['id'],'status'=>1,'is_add'=>1,'type'=>1])->sum('cash');

        $del_cash = Db::name('massage_coach_cash_update_record')->where(['coach_id'=>$coach['id'],'status'=>1,'is_add'=>0,'type'=>1])->sum('cash');

        $cash = round($balance_cash-$coach['service_price']-$wallet_price+$add_cash-$del_cash,2);



        $balance_cash = $comm_model->where(['top_id'=>$coach['id'],'status'=>2])->where('type','in',[8])->sum('cash');

        $wallet_price = $wallet_model->where(['coach_id'=>$coach['id']])->where('type','=',2)->where('status','in',[1,2,4,5])->sum('total_price');

        $add_cash = Db::name('massage_coach_cash_update_record')->where(['coach_id'=>$coach['id'],'status'=>1,'is_add'=>1,'type'=>2])->sum('cash');

        $del_cash = Db::name('massage_coach_cash_update_record')->where(['coach_id'=>$coach['id'],'status'=>1,'is_add'=>0,'type'=>2])->sum('cash');

        $car_cash = round($balance_cash-$coach['car_price']-$wallet_price+$add_cash-$del_cash,2);

        $coach_model->dataUpdate(['id'=>$coach['id']],['check_cash'=>$cash,'check_car_cash'=>$car_cash]);

        if($coach['id']==70){

            //  dump($balance_cash,$wallet_price,$car_cash);exit;
        }
        return true;
    }



    public function adminList(){

        $data = Db::name('shequshop_school_admin')->where(['status'=>1,'is_admin'=>0])->select()->toArray();

        foreach ($data as $v){

            $this->checkAdminCash($v);
        }
        $data = Db::name('shequshop_school_admin')->where(['status'=>1,'is_admin'=>0])->where('check_cash','<>',0)->field('id,agent_name,check_cash as 相差金额')->select()->toArray();

        dump($data);exit;

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-19 10:32
     * @功能说明:校验代理商佣金
     */
    public function checkAdminCash($admin){

        $comm_model = new Commission();

        $admin_model= new \app\massage\model\Admin();

        $wallet_model= new Wallet();

        $admin_cash = $comm_model->where(['top_id'=>$admin['id'],'status'=>2])->where('type','in',[2,5,6])->sum('cash');

        $wallet_price = $wallet_model->where(['coach_id'=>$admin['id'],'type'=>8])->where('status','in',[1,2,4,5])->sum('total_price');

        $add_cash = Db::name('massage_coach_cash_update_record')->where(['coach_id'=>$admin['id'],'status'=>1,'is_add'=>1,'type'=>3])->sum('cash');

        $del_cash = Db::name('massage_coach_cash_update_record')->where(['coach_id'=>$admin['id'],'status'=>1,'is_add'=>0,'type'=>3])->sum('cash');

        $recomm_cash = ResellerRecommendCash::where(['admin_id'=>$admin['id'],'status'=>1])->sum('recommend_cash');

        $sharecarcash = $comm_model->where(['top_id'=>$admin['id'],'status'=>2])->where('type','in',[23])->sum('cash');

        $cash = round($admin_cash-$admin['cash']-$wallet_price+$add_cash-$del_cash-$recomm_cash-$sharecarcash,2);

        $admin_model->dataUpdate(['id'=>$admin['id']],['check_cash'=>$cash]);

        if($admin['id']==3){

          //  dump($admin_cash,$wallet_price,$add_cash-$del_cash);exit;
        }

        return true;
    }





    public function resellerList(){

        $data = Db::name('massage_channel_list')->where(['status'=>2])->field('id,user_name,user_id')->group('user_id')->select()->toArray();

        if(!empty($data)){

            foreach ($data as $v){

                $find = Db::name('massage_channel_list')->where('id','<>',$v['id'])->where(['status'=>2,'user_id'=>$v['user_id']])->find();

                if(!empty($find)){

                    $v['r_id'] = $find['id'];

                    $v['r_user_name'] = $find['user_name'];

                    $arr[] = $v;
                }
            }
        }

        dump($arr);exit;
    }






    public function channelCheckCash(){

        $data = Db::name('massage_channel_list')->where(['status'=>2])->field('id,cash,user_name,user_id')->group('user_id')->select()->toArray();

        $comm_model = new Commission();

        $wallet_model= new Wallet();

        if(!empty($data)){

            foreach ($data as $v){

                $cash = $comm_model->where(['top_id'=>$v['id'],'status'=>2,'type'=>10])->sum('cash');

                $wallet_price = $wallet_model->where(['coach_id'=>$v['id'],'type'=>5])->where('status','in',[1,2,4,5])->sum('total_price');

                $add_cash = Db::name('massage_coach_cash_update_record')->where(['coach_id'=>$v['id'],'status'=>1,'is_add'=>1,'type'=>5])->sum('cash');

                $del_cash = Db::name('massage_coach_cash_update_record')->where(['coach_id'=>$v['id'],'status'=>1,'is_add'=>0,'type'=>5])->sum('cash');

                $cash = round($cash-$v['cash']-$wallet_price+$add_cash-$del_cash,2);

                Db::name('massage_channel_list')->where(['id'=>$v['id']])->update(['check_cash'=>$cash]);

            }
        }

        $data = Db::name('massage_channel_list')->where(['status'=>2])->where('check_cash','<>',0)->field('id,user_id,cash,user_name,user_id,check_cash')->group('user_id')->select()->toArray();

        if(!empty($data)){

            $model = new CashUpdateRecord();

            foreach ($data as $value){

                if($value['check_cash']!=0){

                    $is_add = $value['check_cash']<0?1:0;

                    $cash = abs($value['check_cash']);

                    $insert = [

                        'uniacid' => 666,

                        'coach_id'=> $value['id'],

                        'user_id' => $value['user_id'],

                        'cash'    => $cash,

                        'is_add'  => $is_add,

                        'text'    => '平账用',

                        'type'    => 5,

                        'before_cash'=> $value['cash'],

                        'create_user'=> 0,

                        'after_cash' => $is_add==1?$value['cash']+$cash:$value['cash']-$cash,

                        'admin_type' => -3,

                        'info_id'    => 0,

                        'admin_update_id' => 0,

                        'ip' => getIP()
                    ];

                    $model->dataAdd($insert);
                }

            }
        }

        dump($data);exit;
    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-10-26 17:06
     * @功能说明:获取技师上个半月可提现的金额
     */
    public function getCoachCashByHalfMonthV2($coach_id,$true_cash,$type=1,$date_type=1){

        if($date_type==1){

            $half = strtotime(date('Y-m-16'));

            if(time()>=$half){

                $time = strtotime(date('Y-m-01'));

            }else{

                $time = strtotime(date('Y-m-16',strtotime('-1 month')));
            }
        }else{

            $time = strtotime(date('Y-m-d'));

            $currentWeekDay = date('w', time());

            $time = $time - ($currentWeekDay - 1)*86400;

            $time -= 86400*7;
        }
        //前15天的
        // $time = 15*86400;

        // $time = 86400;

        $order_model = new Order();

        $dis[] = ['b.status','=',2];

        $dis[] = ['b.top_id','=',$coach_id];

        $dis[] = ['a.create_time','<=',$time];
        //服务费
        if($type==1){
            $dis[] = ['a.pay_type','=',7];

            $dis[] = ['b.type','in',[3,7,17,18,24,25]];
        }else{
            //车费
            $dis[] = ['b.type','in',[8]];
        }

        $cash  = $order_model->alias('a')
            ->join('massage_service_order_commission b','a.id = b.order_id')
            ->where($dis)
            ->group('b.id')
            ->sum('b.cash');

        $wallet_model = new Wallet();

        $where[] = ['coach_id','=',$coach_id];

        $where[] = ['status','in',[1,2,4,5]];

        if($type==1){

            $where[] = ['type','=',1];

        }else{
            $where[] = ['type','=',2];
        }

        $wallt_cash = $wallet_model->where($where)->sum('total_price');

        $update_model = new CashUpdateRecord();

        if($type==1){

            $add_update_cash = $update_model->where(['coach_id'=>$coach_id,'status'=>1,'is_add'=>1,'type'=>1])->where('create_time','<=',$time)->sum('cash');

            $del_update_cash = $update_model->where(['coach_id'=>$coach_id,'status'=>1,'is_add'=>0,'type'=>1])->where('create_time','<=',$time)->sum('cash');

            $coach_cash = $cash-$wallt_cash+$add_update_cash-$del_update_cash;
        }else{

            $coach_cash = $cash-$wallt_cash;
        }

        $coach_cash = $coach_cash>0?$coach_cash:0;

        $coach_cash = $coach_cash>$true_cash?$true_cash:$coach_cash;

        dump($coach_cash,$cash,$wallt_cash,$add_update_cash,$del_update_cash);exit;

        return round($coach_cash,2);
    }




    public function coachApply(){

        $coach_model  = new Coach();

        $coach = $coach_model->dataInfo(['id'=>1080]);

        $coach_service_wallet_cash_t_type = getConfigSetting(666,'coach_service_wallet_cash_t_type');

        $data = $this->getCoachCashByHalfMonthV2($coach['id'],$coach['service_price'],1,$coach_service_wallet_cash_t_type);

        dump($data);exit;
    }


}
