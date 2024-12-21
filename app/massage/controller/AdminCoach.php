<?php
namespace app\massage\controller;
use AdaPaySdk\Wallet;
use app\adapay\model\AccountsRecord;
use app\adapay\model\Member;
use app\AdminRest;
use app\coachbroker\model\CoachBroker;
use app\fdd\model\FddAgreementRecord;
use app\fxq\model\FxqIdCheck;
use app\industrytype\model\Type;
use app\massage\model\AdminWater;
use app\massage\model\BrokerWater;
use app\massage\model\CashUpdateRecord;
use app\massage\model\ChannelList;
use app\massage\model\ChannelWater;
use app\massage\model\City;
use app\massage\model\Coach;
use app\massage\model\CoachAccount;
use app\massage\model\CoachChangeLog;
use app\massage\model\CoachIcon;
use app\massage\model\CoachLevel;

use app\massage\model\CoachNotice;
use app\massage\model\CoachTimeList;
use app\massage\model\CoachType;
use app\massage\model\CoachUpdate;
use app\massage\model\CoachWater;
use app\massage\model\Commission;
use app\massage\model\CompanyWater;
use app\massage\model\Config;
use app\massage\model\CreditRecord;
use app\massage\model\CustomBalance;
use app\massage\model\DistributionList;
use app\massage\model\IconCoach;
use app\massage\model\Order;
use app\massage\model\Police;
use app\massage\model\RefundOrder;
use app\massage\model\Salesman;
use app\massage\model\SalesmanWater;
use app\massage\model\Service;
use app\massage\model\ServiceCoach;
use app\massage\model\ShortCodeConfig;
use app\massage\model\StationIcon;
use app\massage\model\StoreCoach;
use app\massage\model\StoreCoachUpdate;
use app\massage\model\StoreList;
use app\massage\model\User;
use app\massage\model\UserLabelData;
use app\massage\model\UserWater;
use app\massage\model\WatermarkList;
use app\massage\model\WorkLog;
use longbingcore\heepay\HeePay;
use longbingcore\permissions\AdminMenu;
use longbingcore\wxcore\Adapay;
use longbingcore\wxcore\PayModel;
use longbingcore\wxcore\WxPay;
use longbingcore\wxcore\WxSetting;
use think\App;
use app\shop\model\Order as Model;
use think\facade\Db;
use think\facade\Env;


class AdminCoach extends AdminRest
{


    protected $model;

    protected $order_goods_model;

    protected $wallet_model;

    public function __construct(App $app) {

        parent::__construct($app);

        $this->model = new Coach();

        $this->level_model = new CoachLevel();
//
        $this->wallet_model = new \app\massage\model\Wallet();

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-15 14:43
     * @功能说明:列表
     */
    public function coachList(){

        $input = $this->_param;

        $dis[] = ['uniacid','=',$this->_uniacid];

        if(!empty($input['is_update'])){

            $dis[] = ['is_update','=',1];
        }

        if(!empty($input['industry_type'])){

            $dis[] = ['industry_type','=',$input['industry_type']];
        }

        if(!empty($input['status'])){

            $dis[] = ['status','=',$input['status']];

        }else{

            $dis[] = ['status','>',-1];
        }

        if(!empty($input['auth_status'])){

            $dis[] = ['auth_status','=',$input['auth_status']];
        }

        $store_model = new StoreList();

        $admin_model = new \app\massage\model\Admin();

        if($this->_user['is_admin']==0){

            $dis[] = ['admin_id','in',$this->admin_arr];
        }

        if(!empty($input['admin_id'])){

            $agent_coach_auth = $admin_model->where(['id'=>$input['admin_id']])->value('agent_coach_auth');

            if($agent_coach_auth!=1){

                $input['admin_id'] = -1;
            }

            $dis[] = ['admin_id','=',$input['admin_id']];
        }
        if(!empty($input['partner_id'])){

            $dis[] = ['partner_id','=',$input['partner_id']];

        }
        if(!empty($input['start_time'])&&!empty($input['end_time'])){

            $start_time = $input['start_time'];

            $end_time   = $input['end_time'];

            $dis[] = ['create_time','between',"$start_time,$end_time"];
        }

        if(!empty($input['type_id'])){

            $dis[] = ['type_id','=',$input['type_id']];
        }

        $broker_model = new CoachBroker();

        if(!empty($input['partner_name'])){

            $partner_id = $broker_model->getBrokerId($input['partner_name']);

            $dis[] = ['broker_id','in',$partner_id];
        }

        $where = [];

        if(!empty($input['name'])){

            $where[] = ['coach_name','like','%'.$input['name'].'%'];

            $where[] = ['mobile','like','%'.$input['name'].'%'];
        }

        if(!empty($input['is_user'])){

            if($input['is_user']==1){

                $dis[] = ['user_id','>',0];

            }else{

                $dis[] = ['user_id','=',0];
            }
        }

        $data = $this->model->dataList($dis,$input['limit'],$where,'industry_type,id as coach_id,is_update,user_id,coach_name,work_img,mobile,create_time,auth_status,admin_add,status,service_price,car_price,city_id,address,recommend,recommend_icon,store_id,admin_id,broker_id,type_id,coach_icon,sh_time');

        $coach_check_auth = 1;

        if($this->_user['is_admin']==0){

            $coach_check_auth = $admin_model->where(['id'=>$this->_user['admin_id']])->value('coach_check_auth');
        }

        $icon_model = new CoachIcon();

        $type_model = new CoachType();

        $industry_model = new Type();

        if(!empty($data['data'])){

            $city_model = new City();

            foreach ($data['data'] as &$v){

                $v['id'] = $v['coach_id'];

                $broker_info = $broker_model->dataInfo(['id'=>$v['broker_id'],'status'=>2]);

                if(!empty($broker_info)){

                    $v['partner_name'] = $broker_info['user_name'];

                }else{

                    $v['partner_id'] = 0;
                }
                //绑定门店
                $v['store_name'] = StoreCoach::getStoreName($v['id']);

                $v['city_name'] = $city_model->where(['id'=>$v['city_id'],'status'=>1])->value('title');
                //代理商是否有审核权限
                $v['coach_check_auth'] = $v['admin_id']==$this->_user['admin_id']||$this->_user['is_admin']!=0?$coach_check_auth:0;
                //图标名字
                $v['coach_icon_title'] = $icon_model->where(['id'=>$v['coach_icon'],'status'=>1])->value('title');

                if(!empty($v['type_id'])){

                    $v['type_title'] = $type_model->where(['id'=>$v['type_id']])->where('status','>',-1)->value('title');
                }

                $v['industry_title'] = $industry_model->where(['id'=>$v['industry_type'],'status'=>1])->value('title');
            }
        }

        $list = [

            0=>'all',

            1=>'ing',

            2=>'pass',

            4=>'nopass',

            5=>'update_num'
        ];

        foreach ($list as $k=> $value){

            $dis_s = [];

            $dis_s[] =['uniacid','=',$this->_uniacid];

            if($this->_user['is_admin']==0){

                $dis_s[] = ['admin_id','in',$this->admin_arr];
            }

            if(!empty($k)&&$k!=5){

                $dis_s[] = ['status','=',$k];

            }else{

                $dis_s[] = ['status','>',-1];

            }

            if($k==5){

                $dis_s[] = ['is_update','=',1];
            }

            $data[$value] = $this->model->where($dis_s)->count();
        }

        return $this->success($data);
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

        $data = $this->model->dataInfo($dis);

        $user_model = new User();

        $city_model = new City();

        $store_model= new StoreList();

        $admin_model= new \app\massage\model\Admin();

        $data['nickName'] = $user_model->where(['id'=>$data['user_id']])->value('nickName');

        $data['city'] = $city_model->where(['id'=>$data['city_id']])->value('title');
        //绑定门店(老)
        if(!empty($data['store_id'])){

            $data['store_name'] = $store_model->where(['id'=>$data['store_id'],'status'=>1])->value('title');
        }
        //绑定门店(新)
        $data['store'] = StoreCoach::getStoreList($data['id']);

        $broker_model = new CoachBroker();
        //经纪人
        if(!empty($data['broker_id'])){

            $data['partner_name'] = $broker_model->where(['id'=>$data['broker_id'],'status'=>2])->value('user_name');
        }
        //代理商
        if(!empty($data['admin_id'])){

            $admin = $admin_model->where(['id'=>$data['admin_id']])->field('agent_name,agent_coach_auth')->find();

            if(!empty($admin)&&$admin->agent_coach_auth==1){

                $data['admin_name'] = $admin->agent_name;
            }else{

                $data['admin_id'] = 0;
            }
        }
        //佣金自定义
        $customBalalance_model = new CustomBalance();
        //是否有自定义配置
        $data['custom_balance'] = $customBalalance_model->dataInfo(['coach_id'=>$input['id'],'status'=>1,'is_update'=>0]);

        $record_model= new FddAgreementRecord();

        $dis = [

            'user_id' => $data['user_id'],

            'status' => 3,

            'admin_id'=> $data['admin_id']
        ];
        //法大大合同
        $data['fdd_agreement'] = $record_model->where($dis)->field('id,download_url,viewpdf_url,end_time')->order('id desc')->find();

        $credit_record_model = new CreditRecord();
        //信用分
        $data['credit_value'] = $credit_record_model->getSingleCoachValue($this->_uniacid,$data['id']);

        $coach_check_auth = 1;

        if($this->_user['is_admin']==0){

            $admin_model = new \app\massage\model\Admin();

            $coach_check_auth = $admin_model->where(['id'=>$this->_user['admin_id']])->value('coach_check_auth');
        }
        //代理商是否有审核权限
        $data['coach_check_auth'] = $data['admin_id']==$this->_user['admin_id']||$this->_user['is_admin']!=0?$coach_check_auth:0;

        $data['address'] = getCoachAddress($data['lng'],$data['lat'],$data['uniacid'],$data['id']);

        $account_model = new CoachAccount();
        //账号
        $data['account_info'] = $account_model->dataInfo(['coach_id'=>$data['id'],'status'=>1]);

        $type_model = new CoachType();

        if($data['type_id']){

            $data['type_title'] = $type_model->where(['id'=>$data['type_id']])->where('status','>',-1)->value('title');
        }

        $industry_model = new Type();

        $data['industry_title'] = $industry_model->where(['id'=>$data['industry_type'],'status'=>1])->value('title');

        //岗位标签
        $station_model = new StationIcon();

        $station_icon_name = $station_model->where(['uniacid' => $this->_uniacid, 'status' => 1, 'id' => $data['station_icon']])->value('title');

        $data['station_icon_name'] = empty($station_icon_name) ? '' : $station_icon_name;
        //个性标签
        $personality_icon_model = new IconCoach();

        $personality_icon_name = $personality_icon_model->where(['id' => $data['personality_icon'], 'status' => 1])->value('title');

        $data['personality_icon_name'] = empty($personality_icon_name) ? '' : $personality_icon_name;

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-13 19:11
     * @功能说明:
     */
    public function financeList(){

        $input = $this->_param;

        $order_model = new Order();

        $order_model->coachBalanceArr($this->_uniacid);

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['user_id','>',0];

        $dis[] = ['status','in',[2,3]];

        if(!empty($input['coach_name'])){

            $dis[] = ['coach_name','like','%'.$input['coach_name'].'%'];
        }

        if($this->_user['is_admin']==0){

            $dis[] = ['admin_id','=',$this->_user['admin_id']];
        }

        $coach = $this->model->dataList($dis,$input['limit'],[],'id as coach_id,user_id,coach_name,work_img,mobile,create_time,auth_status,status,service_price,car_price');

        if(!empty($coach['data'])){

            $comm_model = new Commission();

            foreach ($coach['data'] as &$v){

                $v['id'] = $v['coach_id'];

                $v['wallet_price'] = $this->wallet_model->where(['coach_id'=>$v['id']])->where('type','in',[1,2])->where('status','in',[2])->sum('apply_price');
                //到账多少元
                $v['wallet_price'] = round($v['wallet_price'],2);

                $v['total_price']  = $this->wallet_model->where(['coach_id'=>$v['id']])->where('type','in',[1,2])->where('status','in',[1,2,4,5])->sum('total_price');
                //申请多少元
                $v['total_price']  = round($v['total_price'],2);
                //到账笔数
                $v['wallet_count'] = $this->wallet_model->where(['coach_id'=>$v['id']])->where('type','in',[1,2])->where('status','in',[2])->count();
                //申请笔数
                $v['total_count']  = $this->wallet_model->where(['coach_id'=>$v['id']])->where('type','in',[1,2])->where('status','in',[1,2,4,5])->count();

                $v['order_count'] = $order_model->where(['coach_id'=>$v['id'],'pay_type'=>7])->count();

                $v['order_price'] = $comm_model->where(['top_id'=>$v['id'],'status'=>2])->where('type','in',[3,8,17,18,24,25])->sum('cash');

                $v['order_price'] = round($v['order_price'],2);
                //余额
                $v['balance']     = round($v['service_price']+$v['car_price'],2);
            }
        }

        $coach['service_cash'] = $this->model->where($dis)->sum('service_price');

        $coach['car_cash'] = $this->model->where($dis)->sum('car_price');

        $coach['service_cash'] = round($coach['service_cash'],2);
        $coach['car_cash'] = round($coach['car_cash'],2);

        return $this->success($coach);

    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-09-22 15:19
     * @功能说明:团长用户列表
     */
    public function coachUserList(){

        $input = $this->_param;

        if($this->_user['is_admin']==0&&empty($input['nickName'])){

            $where[] = ['id','=',-1];
        }

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['status','in',[0,1,2,3]];

        $user_id = $this->model->where($dis)->column('user_id');

        $where1 = [];

        if(!empty($input['nickName'])){

            $where1[] = ['nickName','like','%'.$input['nickName'].'%'];

            $where1[] = ['phone','like','%'.$input['nickName'].'%'];
        }

        $user_model = new User();

        $where[] = ['uniacid','=',$this->_uniacid];

        $where[] = ['id','not in',$user_id];

        $where[] = ['status', '=', 1];

        $list = $user_model->dataList($where,$input['limit'],$where1);

        return $this->success($list);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-03 00:15
     * @功能说明:审核(2通过,3取消,4拒绝)
     */
    public function coachAdd(){

        $input = $this->_input;

        $admin_id = $this->_user['is_admin']==0?$this->_user['admin_id']:0;
        //后台添加
        $input['admin_add'] = 1;

        $input['auth_status'] = 0;

        if($this->_user['is_admin']!=0){

            $input['status'] = 2;

            $input['auth_status'] = 2;
        }

        $res = $this->model->coachApply($input,$input['user_id'],$this->_uniacid,$admin_id);

        if(!empty($res['code'])){

            $this->errorMsg($res['msg']);
        }

        return $this->success($res);

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-03 00:15
     * @功能说明:审核(2通过,3取消,4拒绝)
     */
    public function coachDataUpdate(){

        $input = $this->_input;

        if(!empty($input['user_id'])){

            $cap_dis[] = ['user_id','=',$input['user_id']];

            $cap_dis[] = ['status','>',-1];

            if(!empty($input['id'])){

                $cap_dis[] = ['id','<>',$input['id']];

            }

            $cap_info = $this->model->dataInfo($cap_dis);

            if(empty($input['id'])&&!empty($cap_info)&&in_array($cap_info['status'],[1,2,3])){

                $this->errorMsg('已经申请过技师了，');
            }

        }else{

            $wehre[] = ['mobile','=',$input['mobile']];

            $wehre[] = ['status','>',-1];

            if(!empty($input['id'])){

                $wehre[] = ['id','<>',$input['id']];

            }

            $find = $this->model->where($wehre)->find();

            if(!empty($find)){

               // $this->errorMsg('该电话号码已经注册技师');
            }
        }

        $input['uniacid'] = $this->_uniacid;

        $input['id_card']  = !empty($input['id_card'])?implode(',',$input['id_card']):'';

        $input['license']  = !empty($input['license'])?implode(',',$input['license']):'';

        $input['self_img'] = !empty($input['self_img'])?implode(',',$input['self_img']):'';

        $coach_info = $this->model->dataInfo(['id'=>$input['id']]);

        if(!empty($input['id_card'])&&$coach_info['auth_status']==0){

            $input['auth_status'] = 2;
        }

        if(!empty($input['status'])&&in_array($input['status'],[2,4])&&$coach_info['status']==1){

            $input['sh_time'] = time();

            if($input['status']==2){

                $input['auth_status'] = 2;
            }
        }

        if($this->_user['is_admin']==0&&$coach_info['status']==4){

            $input['status'] = 1;
        }

        $customBalalance_model = new CustomBalance();

        $customBalalance_model->where(['coach_id'=>$input['id'],'is_update'=>0])->delete();
        //自定义佣金比例
        if(isset($input['custom_balance'])){

            $insert = [

                'uniacid'   => $this->_uniacid,

                'coach_id'  => $input['id'],

                'balance'   => $input['custom_balance']['balance'],

                'start_time'=> $input['custom_balance']['start_time'],

                'end_time'  => $input['custom_balance']['end_time'],

                'add_balance_status'  => $input['custom_balance']['add_balance_status'],

                'add_basis_balance'  => $input['custom_balance']['add_basis_balance'],
            ];

            $customBalalance_model->dataAdd($insert);

            unset($input['custom_balance']);
        }

        if(isset($input['store'])){

            StoreCoach::where(['coach_id'=>$input['id']])->delete();

            $store = $input['store'];

            unset($input['store']);
        }
        //关联门店
        if(!empty($store)){

            foreach ($store as $key=>$value){

                $store_insert[$key] = [

                    'uniacid' => $this->_uniacid,

                    'store_id'=> $value,

                    'coach_id'=> $input['id']
                ];
            }

            StoreCoach::createAll($store_insert);
        }

        $log_model = new WorkLog();
        //结算在线时间
        $log_model->updateTimeOnline($input['id'],2);
        //同步技师的免出行配置
        $input = $this->model->synCarConfig($input);

        if(isset($input['service_price'])){

            unset($input['service_price']);
        }

        if(isset($input['car_price'])){

            unset($input['car_price']);
        }

        $res = $this->model->dataUpdate(['id'=>$input['id']],$input);

        if(isset($input['is_work'])){

            $time_list_model = new CoachTimeList();

            $time_list_model->where(['coach_id'=>$input['id'],'status'=>0,'is_click'=>1])->where('time_str','>',time())->update(['is_work'=>$input['is_work']]);
        }

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

        $coach = $this->model->dataInfo($dis);

        $input['uniacid'] = $this->_uniacid;

        $input['user_id'] = $coach['user_id'];

        if (isset($input['id'])) {

            unset($input['id']);
        }

        if (isset($input['admin_id'])) {

            unset($input['admin_id']);
        }

        if (isset($input['partner_id'])) {

            unset($input['partner_id']);
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

        if(!empty($input['short_code'])){

            $short_code = getCache($input['mobile'],$this->_uniacid);
            //验证码验证手机号
            if($input['short_code']!=$short_code){

                return $this->error('验证码错误');
            }

            unset($input['short_code']);

            setCache($input['mobile'],'',99,$this->_uniacid);
        }

        $customBalalance_model = new CustomBalance();

        $is_update = $coach['auth_status']==2&&$input['status']==2?1:0;

        $customBalalance_model->where(['coach_id'=>$coach['id'],'is_update'=>$is_update])->delete();
        //自定义佣金比例
        if(isset($input['custom_balance'])){

            $insert = [

                'uniacid'   => $this->_uniacid,

                'coach_id'  => $coach['id'],

                'balance'   => $input['custom_balance']['balance'],

                'start_time'=> $input['custom_balance']['start_time'],

                'end_time'  => $input['custom_balance']['end_time'],

                'add_balance_status'  => $input['custom_balance']['add_balance_status'],

                'add_basis_balance'  => $input['custom_balance']['add_basis_balance'],

                'is_update' => $is_update
            ];

            $customBalalance_model->dataAdd($insert);

            unset($input['custom_balance']);
        }

        if(isset($input['store'])){

            if($coach['auth_status']!=2||$input['status']!=2){

                StoreCoach::where(['coach_id'=>$coach['id']])->delete();
            }

            $store = $input['store'];

            unset($input['store']);
        }
        //重新审核
        if($coach['auth_status']==2&&$input['status']==2){

            $input['coach_id'] = $coach['id'];

            $input['status']   = 1;

            $update_model = new CoachUpdate();

            $update_model->dataUpdate(['coach_id'=>$coach['id'],'status'=>1],['status'=>-1]);

            $input['create_user'] = $this->_user['id'];

            $update_model->dataAdd($input);

            $update_id = $update_model->getLastInsID();

            $res = $this->model->dataUpdate($dis, ['is_update' => 1]);

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

            if(isset($input['service_price'])){

                unset($input['service_price']);
            }

            if(isset($input['car_price'])){

                unset($input['car_price']);
            }

            $res = $this->model->dataUpdate($dis,$input);

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
     * @DataTime: 2022-09-16 10:36
     * @功能说明:审核技师编辑信息
     */
    public function coachUpdateCheck(){

        $input = $this->_input;

        $update_model = new CoachUpdate();

        $dis = [

            'coach_id' => $input['id'],

            'status'   => 1
        ];

        $info = Db::name('massage_service_coach_update')->where($dis)->order('id desc')->find();

        if(empty($info)){

            $this->errorMsg('暂无更新信息');
        }

        $res = $update_model->dataUpdate(['coach_id'=>$input['id'],'status'=>1],['status'=>$input['status'],'sh_text'=>$input['sh_text']]);

        $update = [];

        $update['is_update'] = 0;
        //通过覆盖技师信息
        if($input['status']==2){

            if(!empty($info['create_user'])){

                $arr = ['constellation','user_id','weight','height','true_user_name','coach_name','type_id','show_salenum','free_fare_distance','store_id','sex','work_time','mobile','city_id','address','text','id_card','license','work_img','self_img','id_code','video','lng','lat','birthday','is_work','start_time','end_time','order_num','station_icon','personality_icon'];

            }else{

                $arr = ['constellation','user_id','weight','height','true_user_name','coach_name','type_id','show_salenum','free_fare_distance','store_id','sex','work_time','mobile','city_id','address','text','id_card','license','work_img','self_img','id_code','video','lng','lat','birthday','station_icon','personality_icon'];
            }

            foreach ($arr as $value) {

                if(key_exists($value,$info)){

                    $update[$value] = $info[$value];
                }
            }

            $customBalalance_model = new CustomBalance();
            //是否有自定义配置
            $custom_balance = $customBalalance_model->dataInfo(['coach_id'=>$input['id'],'status'=>1,'is_update'=>1]);

            if(!empty($custom_balance)&&!empty($info['create_user'])){

                $customBalalance_model->where(['coach_id'=>$input['id'],'is_update'=>0])->delete();

                $customBalalance_model->dataUpdate(['id'=>$custom_balance['id']],['is_update'=>0]);
            }
            //绑定门店
            $store_list = StoreCoachUpdate::where(['update_id'=>$info['id']])->select()->toArray();

            StoreCoach::where(['coach_id'=>$input['id']])->delete();

            if(!empty($store_list)){

                foreach ($store_list as $key=>$value){

                    $store_insert[$key] = [

                        'uniacid' => $value['uniacid'],

                        'store_id'=> $value['store_id'],

                        'coach_id'=> $value['coach_id']
                    ];
                }

                StoreCoach::createAll($store_insert);
            }
        }

        $res = $this->model->dataUpdate(['id'=>$input['id']],$update);

        if(isset($update['is_work'])){

            $time_list_model = new CoachTimeList();

            $time_list_model->where(['coach_id'=>$input['id'],'status'=>0,'is_click'=>1])->where('time_str','>',time())->update(['is_work'=>$update['is_work']]);
        }
        //发送审核结果通知
        $res = $this->model->updateSendMsg($input['id'],$input['status'],$input['sh_text']);

        return $this->success($res);

    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-15 14:58
     * @功能说明:技师提交信息
     */
    public function coachUpdateInfo(){

        $input = $this->_param;

        $data  = $this->model->dataInfo(['id'=>$input['id']]);

        $admin_id = $data['admin_id'];

        if($data['auth_status']==2){

            $dis = [

                'coach_id'=> $input['id'],

                'status' => 1
            ];

            $update_model = new CoachUpdate();

            $data = $update_model->dataInfo($dis);

            $data['admin_id'] = $admin_id;

            if(!empty($data)){

                $data['store'] = StoreCoachUpdate::getStoreList($data['id']);
            }

        }else{

            $data['store'] = StoreCoach::getStoreList($input['id']);
        }

        if(empty($data)){

            $this->errorMsg('暂无更新信息');
        }

        $customBalalance_model = new CustomBalance();
        //是否有自定义配置
        $data['custom_balance'] = $customBalalance_model->dataInfo(['coach_id'=>$input['id'],'status'=>1,'is_update'=>1]);

        $user_model = new User();

        $city_model = new City();

        $data['nickName'] = $user_model->where(['id'=>$data['user_id']])->value('nickName');

        $data['city'] = $city_model->where(['id'=>$data['city_id']])->value('title');
        //绑定门店
        if(!empty($data['store_id'])){

            $store_model = new StoreList();

            $data['store_name'] = $store_model->where(['id'=>$data['store_id']])->value('title');
        }
        //经纪人
        if(!empty($data['partner_id'])){

            $data['partner_name'] = $user_model->where(['id'=>$data['partner_id']])->value('nickName');
        }

        if(!empty($data['type_id'])){

            $type_model = new CoachType();

            $data['type_title'] = $type_model->where(['id'=>$data['type_id']])->where('status','>',-1)->value('title');
        }

        $industry_model = new Type();

        $data['industry_info'] = $industry_model->where(['id'=>$data['industry_type'],'status'=>1])->find();

        $station_model = new StationIcon();

        $station_icon_name = $station_model->where(['uniacid' => $this->_uniacid, 'status' => 1, 'id' => $data['station_icon']])->value('title');

        $data['station_icon_name'] = empty($station_icon_name) ? '' : $station_icon_name;
        //个性标签
        $personality_icon_model = new IconCoach();

        $personality_icon_name = $personality_icon_model->where(['id' => $data['personality_icon'], 'status' => 1])->value('title');

        $data['personality_icon_name'] = empty($personality_icon_name) ? '' : $personality_icon_name;

        return $this->success($data);

    }
    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-03 00:15
     * @功能说明:审核(2通过,3取消,4拒绝)
     */
    public function coachUpdate(){

        $input = $this->_input;

        $dis = [

            'id' => $input['id']
        ];

        $coach = $this->model->dataInfo(['id'=>$input['id']]);

        if(!empty($input['status'])&&in_array($input['status'],[2,4])&&$coach['status']==1){

            $input['sh_time'] = time();

            if($input['status']==2&&$coach['admin_add']==1){

                $input['auth_status'] = 2;
            }
            if ($input['status'] == 2) {

                if (!empty($coach['true_user_name']) && !empty($coach['id_code'])) {

                    FxqIdCheck::defCheck($coach['true_user_name'], $coach['id_code'], $this->_uniacid, $input['id']);
                }
            }
        }

        if(!empty($coach['id_card'])&&$coach['auth_status']==0){

            $input['auth_status'] = 2;
        }

        if(empty($input['user_id'])&&!empty($input['mobile'])){

            $find = $this->model->where(['mobile'=>$input['mobile']])->where('status','>',-1)->where('id','<>',$input['id'])->find();

            if(!empty($find)){

                $this->errorMsg('该电话号码已经注册技师');
            }
        }

        if(isset($input['store'])){

            StoreCoach::where(['coach_id'=>$input['id']])->delete();

            $store = $input['store'];

            unset($input['store']);
        }

        if(!empty($store)){

            foreach ($store as $key=>$value){

                $store_insert[$key] = [

                    'uniacid' => $this->_uniacid,

                    'store_id'=> $value,

                    'coach_id'=> $input['id']
                ];
            }

            StoreCoach::createAll($store_insert);
        }
        //如果是删除需要判断有无余额
        if(!empty($input['status'])&&$input['status']==-1){

            if(!empty($coach['service_price'])||!empty($coach['car_price'])){

                $this->errorMsg('还有未提现的费用，无法删除');
            }

            $order_model = new Order();

            $where[] = ['uniacid','=',$this->_uniacid];

            $where[] = ['coach_id','=',$input['id']];

            $where[] = ['pay_type','in',[2,3,4,5,6,8]];

            $order = $order_model->dataInfo($where);

            if(!empty($order)){

                $this->errorMsg('还有未完成的订单，无法删除'.$order['id']);
            }

            $wallet_dis = [

                'coach_id' => $input['id'],

                'status'   => 1
            ];

            $wallet = $this->wallet_model->where($wallet_dis)->where('type','in',[1,2])->find();

            if(!empty($wallet)){

                $this->errorMsg('还有提现申请中，无法删除');

            }
            //冻结金额
            $arr_dis = [

                'pay_type' => 7,

                'have_tx'  => 0,

                'coach_id' => $input['id']
            ];

            $no_arr_order = $order_model->dataInfo($arr_dis);

            if(!empty($no_arr_order)){

                $this->errorMsg('还有冻结订单，无法删除');
            }

            $coach_account = new CoachAccount();

            $coach_account->where(['coach_id'=>$input['id']])->delete();
        }

        $log_model = new WorkLog();
        //结算在线时间
        $log_model->updateTimeOnline($input['id'],2);

        $input['uniacid'] = $this->_uniacid;
        //同步技师的免出行配置
        $input = $this->model->synCarConfig($input);

        $data = $this->model->dataUpdate($dis,$input);

        return $this->success($data);

    }






    /**\
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-18 09:28
     * @功能说明:同意退款
     */
    public function passRefund(){

        $input = $this->_input;

        $res = $this->refund_order_model->passOrder($input['id'],$input['price'],$this->payConfig(),0,$input['text']);

        if(!empty($res['code'])){

            $this->errorMsg($res['msg']);
        }

        return $this->success($res);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-04 18:53
     * @功能说明:等级列表
     */
    public function levelList(){

        $input = $this->_param;

        $this->level_model->initTop($this->_uniacid);

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['status','=',1];

        $dis = [

            'uniacid' => $this->_uniacid,

            'status'  => 1
        ];

        $data = $this->level_model->dataList($dis,$input['limit']);

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $v['lower'] = $this->level_model->where($dis)->where('time_long','<',$v['time_long'])->max('time_long');

                $v['lower_price'] = $this->level_model->where($dis)->where('time_long','<',$v['time_long'])->max('price');

            }
        }

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-04 18:56
     * @功能说明:添加等级
     */
    public function levelAdd(){

        $input = $this->_input;

        $input['uniacid'] = $this->_uniacid;

        $dis = [

            'uniacid' => $this->_uniacid,

            'status'  => 1,
        ];

        $arr = [

            'time_long' => '累计时长不能相同',

           // 'top'       => '等级排序值不能相同',

            'price'     => '最低业绩不能相同',
        ];

        foreach ($arr as $k=>$value){

            $find = $this->level_model->where($dis)->where([$k=>$input[$k]])->find();

            if(!empty($find)){

                $this->errorMsg($value);
            }
        }

//        if($input['top']<1){
//
//            $this->errorMsg('等级排序值不能小于1');
//
//        }

        $res = $this->level_model->dataAdd($input);

        return $this->success($res);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-04 18:57
     * @功能说明:编辑等级
     */
    public function levelUpdate(){

        $input = $this->_input;

        $dis = [

            'id' => $input['id']
        ];

        if(isset($input['time_long'])){

            $diss = [

                'uniacid' => $this->_uniacid,

                'status'  => 1,
            ];

            $arr = [

                'time_long' => '累计时长不能相同',

               // 'top'       => '等级排序值不能相同',

                'price'     => '最低业绩不能相同',
            ];


            foreach ($arr as $k=>$value){

                $find = $this->level_model->where($diss)->where('id','<>',$input['id'])->where([$k=>$input[$k]])->find();

                if(!empty($find)){

                    $this->errorMsg($value);
                }
            }

//            if($input['top']<1){
//
//                $this->errorMsg('等级排序值不能小于1');
//
//            }
        }

        $res = $this->level_model->dataUpdate($dis,$input);

        return $this->success($res);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-04 18:59
     * @功能说明:等级详情
     */
    public function levelInfo(){

        $input = $this->_input;

        $dis = [

            'id' => $input['id']
        ];

        $res = $this->level_model->dataInfo($dis);

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

        $where = [];

        if($this->_user['is_admin']==0){

            $where[] = ['admin_id','=',$this->_user['admin_id']];
            //所有自己提现的
            $id_arr = $this->wallet_model->where(['user_id'=>$this->_user['admin_id'],'type'=>3])->column('id');

            $id_arrs = $this->wallet_model->where(['coach_id'=>$this->_user['admin_id']])->where('type','in',[8,9])->column('id');

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

            $id = $this->wallet_model->getIdByName($input['coach_name']);

            $dis[] = ['id','in',$id];
        }

        if(isset($input['min_cash'])&&is_numeric($input['min_cash'])){

            $dis[] = ['total_price','between',"{$input['min_cash']},{$input['max_cash']}"];
        }

        $data = $this->wallet_model->dataList($dis,$input['limit'],$where);

        $admin_model = new \app\massage\model\Admin();

        $reseller_model = new DistributionList();

        if(!empty($data['data'])){

            $wallet_check_auth = $admin_model->where(['id'=>$this->_user['admin_id']])->value('wallet_check_auth');

            foreach ($data['data'] as &$v){

                $v['true_price'] = round($v['true_price'],2);
                //是否有审核对权限
                $v['wallet_check_auth'] = $this->wallet_model->agentCheckAuth($this->_user['admin_id'],$v,$wallet_check_auth);
                //操作人
                if(in_array($v['status'],[2,3,4])){

                    if(!empty($v['control_id'])){

                        $v['control_name'] = $admin_model->where(['id'=>$v['control_id']])->value('agent_name');
                    }else{

                        $v['control_name'] = $admin_model->where(['is_admin'=>1])->value('agent_name');
                    }
                }

                $obj = $this->wallet_model->getWalletObjInfo($v['type']);

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

                    $reseller = $reseller_model->where(['user_id'=>$v['user_id']])->where('status','in',[2,3])->field('user_name,true_user_name')->find();

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

                    $v['agent_name'] = $admin_model->where(['id'=>$v['admin_id']])->field('agent_name,city_type')->find();
                }
                //微信打款的时候兼容一下
                if($v['online']==1){

                    $v['payment_no'] = $v['detail_id'];
                }

                if(in_array($v['type'],[7,9])){

                    $v['type'] = 2;
                }

                if(in_array($v['type'],[8])){

                    $v['type'] = 3;
                }
            }
        }

        $data['total_price'] = $this->wallet_model->where($dis)->where(function ($query) use ($where){
            $query->whereOr($where);
        })->sum('total_price');

        $data['total_price'] = round($data['total_price'],2);

        $id = array_column($data['data'],'id');
        //异步执行订单消息通知
        publisher(json_encode(['pay_config'=>$this->payConfig(2),'uniacid'=>$this->_uniacid,'id'=>$id,'action'=>'wallet_check'],true));

        $model = new \app\massage\model\Wallet();

        $model->wxCheck(666,$this->payConfig(2),$id);
     //   publisher(json_encode(['pay_config'=>$this->payConfig(2),'uniacid'=>$this->_uniacid,'action'=>'wallet_check'],true));

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-07 18:57
     * @功能说明:提现详情
     */
    public function walletInfo(){

        $input = $this->_param;

        $dis = [

            'id' => $input['id']
        ];

        $data = $this->wallet_model->dataInfo($dis);

        return $this->success($data);


    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-07 18:58
     * @功能说明:通过提现申请
     */
    public function walletPass(){

        $input = $this->_input;

        $key = 'wallet_pass_key'.$input['id'];

        incCache($key,1,$this->_uniacid,10);

        if(getCache($key,$this->_uniacid)!=1){

            decCache($key,1,$this->_uniacid);

            return $this->error('请稍后重试');
        }

        $dis = [

            'id' => $input['id']
        ];

        $data = $this->wallet_model->dataInfo($dis);

        if(!in_array($data['status'],[1,5])){

            decCache($key,1,$this->_uniacid);

            $this->errorMsg('申请已审核');
        }

        $admin_model = new \app\massage\model\Admin();

        if($this->_user['is_admin']==0){

            $admin_info = $admin_model->where(['id'=>$this->_user['admin_id']])->field('wallet_check_auth,offline_transfer_auth,wechat_transfer_auth,alipay_transfer_auth,bank_transfer_auth')->find();

            if(empty($admin_info)){

                decCache($key,1,$this->_uniacid);

                $this->errorMsg('你无权操作');
            }
            //是否有审核对权限
            $wallet_check_auth = $this->wallet_model->agentCheckAuth($this->_user['admin_id'],$data,$admin_info->wallet_check_auth);

            if($wallet_check_auth==0){

                decCache($key,1,$this->_uniacid);

                $this->errorMsg('你无权操作');
            }

            $check_type = $this->wallet_model->agentTransferCheck($input['online']);

            if($admin_info->$check_type==0){

                decCache($key,1,$this->_uniacid);

                $this->errorMsg('你无权操作该提现方式');
            }
        }

        if($input['online']==4){

            $auth = AdminMenu::getAuthList((int)$this->_uniacid,['heepay']);

            if($auth['heepay']==true){

                $input['online']=5;
            }
        }
        $update = [

            'sh_time'   => time(),

            'status'    => 2,

            'online'    => $input['online'],

            'true_price'=> $data['apply_price'],

            'control_id'=> $this->_user['id']
        ];

        if($data['type']==7){

            decCache($key,1,$this->_uniacid);

            $this->errorMsg('信息错误');
        }
        //汇付银行卡打款
        if(in_array($data['type'],[4,5,6,10,12])){

            $user_id = $data['user_id'];

        }elseif (in_array($data['type'],[3])){
            //这里是老的带来提现，新数据可以不考虑
            $user_id = $admin_model->where(['id'=>$data['user_id']])->value('user_id');

        }elseif (in_array($data['type'],[8,9])){

            $user_id = $admin_model->where(['id'=>$data['coach_id']])->value('user_id');

        }elseif (in_array($data['type'],[1,2])){

            $user_id = $this->model->where(['id'=>$data['coach_id']])->value('user_id');

        }elseif (in_array($data['type'],[11])){

            $admin_user_model = new \app\adminuser\model\AdminUser();

            $user_id = $admin_user_model->where(['id'=>$data['coach_id']])->value('user_id');
        }

        if($input['online']==4){

            $member_model = new Member();

            $member = $member_model->dataInfo(['user_id'=>$user_id,'status'=>1]);

            if(empty($member)){

                decCache($key,1,$this->_uniacid);

                return $this->error('该用户还没有账户');
            }

            $record_model = new AccountsRecord();
            //初始化账户
            $res = $record_model->giveMemberCash($member['id'],$data['tax_point'],$this->_uniacid,$data['apply_price']);

            if(!empty($res['code'])){

                decCache($key,1,$this->_uniacid);

                return $this->error($res['msg']);
            }
        }elseif ($input['online']==5){

            $heepay_model = new \app\heepay\model\Member();

            $heepay_res = $heepay_model->checkUserCash($user_id,$data['apply_price']);

            if(!empty($heepay_res['code'])){

                decCache($key,1,$this->_uniacid);

                return $this->error($heepay_res['msg']);
            }
        }

        Db::startTrans();

        $res = $this->wallet_model->where(['id'=>$input['id']])->where('status','in',[1,5])->update($update);

        if($res!=1){

            Db::rollback();

            decCache($key,1,$this->_uniacid);

            $this->errorMsg('打款失败');
        }

        $user_model = new \app\massage\model\User();
        //线上转账
        if($input['online']==1){

            $openid_text = [

                0 => 'wechat_openid',

                1 => 'app_openid',

                2 => 'web_openid'
            ];
            //暂时写死
            $data['last_login_type'] = 2;

            $openid = $user_model->where(['id'=>$user_id])->value($openid_text[$data['last_login_type']]);

            if(empty($openid)){

                Db::rollback();

                decCache($key,1,$this->_uniacid);

                return $this->error('用户信息错误，未获取到openid');
            }
            //微信相关模型
            $wx_pay = new WxPay($this->_uniacid);

            $user_name = !empty($input['user_name'])?$input['user_name']:'';
            //微信提现
            $res    = $wx_pay->crteateMchPay($this->payConfig($data['last_login_type']),$openid,$update['true_price'],$user_name);

            if($res['result_code']=='SUCCESS'&&$res['return_code']=='SUCCESS'){

                $wx_update = [

                    'user_num' => $openid
                ];

                if(!empty($res['out_batch_no'])){

                    $wx_update['payment_no'] = $res['out_batch_no'];
                }

                if(!empty($res['batch_id'])){

                    $wx_update['detail_id'] = $res['batch_id'];
                }
                //转账中
                if(isset($res['batch_status'])&&(in_array($res['batch_status'],['ACCEPTED','PROCESSING','CLOSED']))){

                    $wx_update['status'] = 4;
                }

                $this->wallet_model->dataUpdate(['id'=>$input['id']],$wx_update);

            }else{

                Db::rollback();

                decCache($key,1,$this->_uniacid);

                return $this->error(!empty($res['err_code_des'])?$res['err_code_des']:'你还未该权限');
            }

        }elseif ($input['online']==2){
            //支付宝转账
            $pay_model = new PayModel($this->payConfig());

            $alipay_number = $user_model->dataInfo(['id'=>$user_id]);

            if(empty($alipay_number)||empty($alipay_number['alipay_number'])){

                Db::rollback();

                decCache($key,1,$this->_uniacid);

                return $this->error('该用户未绑定支付宝账号');
            }

            $res = $pay_model->onPaymentByAlipay($alipay_number['alipay_number'],$update['true_price'],$alipay_number['alipay_name']);

            if(!empty($res['alipay_fund_trans_toaccount_transfer_response']['code'])&&$res['alipay_fund_trans_toaccount_transfer_response']['code']==10000&&$res['alipay_fund_trans_toaccount_transfer_response']['msg']=='Success'){

                $this->wallet_model->dataUpdate(['id'=>$input['id']],['payment_no'=>$res['alipay_fund_trans_toaccount_transfer_response']['order_id'],'user_num'=>$alipay_number['alipay_number']]);
            }else{

                Db::rollback();

                decCache($key,1,$this->_uniacid);

                return $this->error(!empty($res['alipay_fund_trans_toaccount_transfer_response']['sub_msg'])?$res['alipay_fund_trans_toaccount_transfer_response']['sub_msg']:'你还未该权限');
            }

        }elseif ($input['online']==4){

            $adapay_model = new Adapay($this->_uniacid);

            $code = orderCode();
            //打款
            $res = $adapay_model->drawCash($code,$member['member_id'],$update['true_price']);

            if($res['status']=='failed'){

                Db::rollback();

                decCache($key,1,$this->_uniacid);

                return $this->error($res['error_msg']);
            }

            $update = [

                'payment_no' => $res['id'],

                'adapay_code' => $code
            ];
            //处理中
            if($res['status']=='pending'){

                $update['status'] = 4;
            }

            $this->wallet_model->dataUpdate(['id'=>$data['id']],$update);

        }elseif ($input['online']==5){

            $heepay_model = new HeePay($this->_uniacid);

            $code = longbingorderCodetf();

            $res = $heepay_model->wallet($code,$update['true_price'],$heepay_res['heepay_id']);

            if($res['body']['bill_status']=='-1'){

                Db::rollback();

                decCache($key,1,$this->_uniacid);

                return $this->error($res['body']['return_msg']);
            }

            $update = [

                'payment_no' => $code,

                'adapay_code'=> $res['body']['hy_bill_no'],

                'status'=> 4,
            ];

            $this->wallet_model->dataUpdate(['id'=>$data['id']],$update);
        }

        Db::commit();

        decCache($key,1,$this->_uniacid);

        return $this->success($res);

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-26 15:03
     * @功能说明:拒绝提现
     */
    public function walletNoPass(){

        $input = $this->_input;

        $info = $this->wallet_model->dataInfo(['id'=>$input['id']]);

        $admin_model = new \app\massage\model\Admin();

        if($this->_user['is_admin']==0){

            $wallet_check_auth = $admin_model->where(['id'=>$this->_user['admin_id']])->value('wallet_check_auth');
            //是否有审核对权限
            $wallet_check_auth = $this->wallet_model->agentCheckAuth($this->_user['admin_id'],$info,$wallet_check_auth);

            if($wallet_check_auth==0){

                $this->errorMsg('你无权操作');
            }
        }

        if($info['status']==2){

            $this->errorMsg('已同意打款');
        }

        if($info['status']==3){

            $this->errorMsg('已拒绝打款');
        }

        if($info['type']==7){

            $this->errorMsg('类型错误');
        }

        Db::startTrans();

        $update = [

            'sh_time' => time(),

            'control_id'=> $this->_user['id'],

            'status'    => 3,

            'check'     => 1
        ];

        $res = $this->wallet_model->where(['id'=>$input['id']])->where('status','in',[1,5])->update($update);

        if($res!=1){

            Db::rollback();

            $this->errorMsg('打款失败');
        }

        $cash = $info['total_price'];

        if(in_array($info['type'],[1,2])){

            $coach_water_model = new CoachWater();

            $res = $coach_water_model->updateCash($this->_uniacid,$info['coach_id'],$cash,1,$info['type']);

        }elseif($info['type']==3){

            $admin_water_model = new AdminWater();

            $res = $admin_water_model->updateCash($this->_uniacid,$info['user_id'],$cash,1);

        }elseif($info['type']==4){

//            $user_model = new User();
//
//            $res = $user_model->where(['id'=>$info['user_id']])->update(['new_cash'=>Db::Raw("new_cash+$cash")]);

            $water_model = new UserWater();

            $res = $water_model->updateCash($this->_uniacid,$info['user_id'],$cash,1,$input['id'],0,3);

        }elseif ($info['type']==5){

            $channel_water_model = new ChannelWater();

            $res = $channel_water_model->updateCash($this->_uniacid,$info['coach_id'],$cash,1,$input['id'],0,3);

        }elseif ($info['type']==6){

            $water_model = new SalesmanWater();

            $res = $water_model->updateCash($this->_uniacid,$info['coach_id'],$cash,1,$input['id'],0,3);

        }elseif (in_array($info['type'],[8,9])){

            $admin_water_model = new AdminWater();

            $res = $admin_water_model->updateCash($this->_uniacid,$info['coach_id'],$cash,1);

        }elseif ($info['type']==10){

            $water_model = new BrokerWater();

            $res = $water_model->updateCash($this->_uniacid,$info['coach_id'],$cash,1,$input['id'],0,3);

        }elseif ($info['type']==11){

            $company_water_model = new CompanyWater();

            $res = $company_water_model->updateCash($this->_uniacid,0,$cash,1);
        }elseif ($info['type']==12){
            $user_model = new User();

            $res = $user_model->where(['id' => $info['user_id']])->update(['partner_money' => Db::raw("partner_money+$cash")]);

        }

        if($res==0){

            Db::rollback();

            $this->errorMsg('审核失败'.$info['type'].'-'.$res);
        }

        Db::commit();

        return $this->success($res);
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
        //代理商
        if($this->_user['is_admin']==0){

            $dis[] = ['b.admin_id','=',$this->_user['admin_id']];
        }

        $police_model = new Police();

        $data = $police_model->dataList($dis,$input['limit']);

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-08 17:19
     * @功能说明:编辑报警
     */
    public function policeUpdate(){

        $input = $this->_input;

        $dis = [

            'id' => $input['id']
        ];

        $police_model = new Police();

        $res = $police_model->dataUpdate($dis,$input);

        return $this->success($res);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-10-24 15:25
     * @功能说明:获取用户标签
     */
    public function userLabelList(){

        $input = $this->_param;

        $label_model = new UserLabelData();

        $data = $label_model->getUserLabel($input['user_id']);

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-10 16:39
     * @功能说明:认证审核
     */
    public function coachAuthCheck(){

        $input = $this->_input;

        $dis = [

            'id' => $input['id']
        ];

        $res = $this->model->dataUpdate($dis,['auth_status'=>$input['auth_status']]);

        return $this->success($res);

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

                $top = 'service_timelong desc,a.id desc';

                break;

            case 2:

                $top = 'service_timelong desc,a.id desc';

                break;
            case 3:

                $top = 'order_service_price desc,a.id desc';

                break;
            case 4:

                $top = 'add_balance desc,a.id desc';

                break;

            case 5:

                $top = 'coach_integral desc,a.id desc';

                break;

            case 6:

                $top = 'coach_star desc,a.id desc';

                break;
        }

        if($input['time_type']==1){

            $start_time = strtotime(date('Y-m-d'));

            $end_time   = $start_time+86400;

        }elseif($input['time_type']==2){

            $start_time = strtotime(date('Y-m-d', strtotime("monday this week")));

            $end_time   = strtotime(date('Y-m-d', strtotime("+6 day", $start_time)))+86399;

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

        if(empty($start_time)||empty($end_time)){

            $this->errorMsg('请输入时间');
        }

        $order_model = new Order();

        $log_model   = new WorkLog();

        $refund_model= new RefundOrder();

        $dis [] = ['uniacid','=',$this->_uniacid];

        $dis [] = ['status','=',2];

        $dis_sql  = "a.uniacid = $this->_uniacid AND a.status = 2";

        if(!empty($input['coach_name'])){

            $coach_name = $input['coach_name'];

            $dis [] = ['coach_name','=',$input['coach_name']];

            $dis_sql.= " AND a.coach_name = '".$coach_name."'";
        }

        if($this->_user['is_admin']==0){

            $admin_arr = implode(',',$this->admin_arr);

            if(!empty($admin_arr)){

                $admin_arr = '('.$admin_arr.')';

                $dis_sql.= " AND a.admin_id IN $admin_arr";
            }

            $dis[] = ['admin_id','in',$this->admin_arr];
        }

       // dump($dis_sql);exit;

        $count = $this->model->where($dis)->count();

        $limit = $input['limit'];

        $page  = $input['page'];

        $start = $limit;

        $end   = ($page-1)*$limit;

        $sql = "SELECT a.id as coach_id,a.coach_name,a.work_img,a.user_id,ifnull(b.order_service_price,0) as order_service_price ,ifnull(b.service_timelong,0) as service_timelong,ifnull(b.add_balance,0) as add_balance,ifnull(c.coach_integral,0) as coach_integral,ifnull(d.coach_star,5) as coach_star
             FROM `ims_massage_service_coach_list` `a`
             LEFT JOIN (SELECT sum(true_service_price) as order_service_price,sum(true_time_long) as service_timelong,sum(true_service_price*is_add)/sum(true_service_price) as add_balance,id,coach_id FROM `ims_massage_service_order_list` where pay_type = 7 AND create_time between $start_time AND $end_time GROUP BY coach_id) AS b ON a.id=b.coach_id 
             LEFT JOIN (SELECT sum(integral) as coach_integral,coach_id FROM `ims_massage_integral_list` where type = 0 AND status=1 AND create_time between $start_time AND $end_time GROUP BY coach_id) AS c ON a.id=c.coach_id
             LEFT JOIN (SELECT ROUND(avg(star),1) as coach_star,coach_id FROM `ims_massage_service_order_comment` where status > -1 AND create_time between $start_time AND $end_time GROUP BY coach_id) AS d ON a.id=d.coach_id
             WHERE $dis_sql GROUP BY a.id ORDER BY $top LIMIT $start OFFSET $end";

        $data = Db::query($sql);

        $arr['data'] = $data;

        $arr['total']= $count;

        $arr['current_page'] = $page;

        $arr['per_page']     = $limit;

        $arr['last_page']    = ceil($arr['total']/$limit);

        $time_list_model = new CoachTimeList();

        $time_list_model->delData();

        $credit_record_model = new CreditRecord();

        if(!empty($arr['data'])){

            foreach ($arr['data'] as &$v){
                //信用分
                $v['credit_value'] = $credit_record_model->getSingleCoachValue($this->_uniacid,$v['coach_id']);

                $v['add_balance']  = round($v['add_balance']*100,2);

                $v['order_service_price']= round($v['order_service_price'],2);

                $v['coach_level']        = $this->model->getCoachLevel($v['coach_id'],$this->_uniacid);

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
                //在线时长转换积分
                if(!empty($v['coach_level'])&&$v['coach_level']['online_change_integral_status']==1){

                    $more_online_time = floor($v['coach_onlinetime']/60 - $v['coach_level']['online_time']);

                    if($more_online_time>0){

                        $change_integral = $more_online_time*$v['coach_level']['online_change_integral'];

                        $v['coach_integral']+= $change_integral;
                    }
                }

                $service_order_count= $order_model->where(['coach_id'=>$v['coach_id']])->where('pay_type','>',1)->whereBetween("create_time","$start_time,$end_time")->count();

                $v['service_order_count']= $order_model->where(['coach_id'=>$v['coach_id']])->where('pay_type','=',7)->whereBetween("create_time","$start_time,$end_time")->count();

                $v['cancel_order_count'] = $refund_model->where(['coach_id'=>$v['coach_id'],'type'=>2,'is_admin_apply'=>0])->whereBetween("create_time","$start_time,$end_time")->count();

                $v['refund_balance']     = $v['total_order_count']>0?round(($v['total_order_count']-$service_order_count)/$v['total_order_count']*100,2):0;

            }
        }

        return $this->success($arr);
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-06-14 18:28
     * @功能说明:技师业绩统计
     */
    public function coachDataList1(){

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

        $where = [];

        if($this->_user['is_admin']==0){

            $where[] = ['admin_id','in',$this->admin_arr];
        }

        $data = $this->model
            ->where($dis)
            ->where($where)
            ->field('id as coach_id,coach_name,work_img,user_id')
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

                $v['add_balance']         = $v['order_service_price']>0?$v['add_service_price']/$v['order_service_price']:0;

                $v['add_balance']         = round($v['add_balance']*100,2);

                $v['coach_integral']      = Db::name('massage_integral_list')->where(['coach_id'=>$v['coach_id'],'type'=>0,'status'=>1])->whereBetween("create_time","$start_time,$end_time")->sum('integral');

                $v['coach_star']          = Db::name('massage_service_order_comment')->where(['coach_id'=>$v['coach_id']])->where('status','>',-1)->whereBetween("create_time","$start_time,$end_time")->avg('star');

                $v['coach_star']          = !empty($v['coach_star'])?round($v['coach_star'],1):5;

            }
        }

        $data = arraySort($data,$top,'desc');

        $start = $input['page']*$input['limit']-$input['limit'];

        $end   = $input['page']*$input['limit']-1;

        $time_list_model = new CoachTimeList();

        foreach ($data as $ks=>$vs){

            if($ks>=$start&&$ks<=$end){

                $vs['coach_level'] = $this->model->getCoachLevel($vs['coach_id'],$this->_uniacid);

                $vs['total_order_count']  = $order_model->where(['coach_id'=>$vs['coach_id']])->where('pay_time','>',0)->whereBetween("create_time","$start_time,$end_time")->count();

                $vs['service_order_count']= $order_model->where(['coach_id'=>$vs['coach_id']])->where('pay_type','>',1)->whereBetween("create_time","$start_time,$end_time")->count();

                $vs['cancel_order_count'] = $order_model->where(['coach_id'=>$vs['coach_id']])->where('coach_refund_time','>',0)->whereBetween("create_time","$start_time,$end_time")->count();

                $vs['refund_balance']     = $vs['total_order_count']>0?round(($vs['total_order_count']-$vs['service_order_count'])/$vs['total_order_count']*100,2):0;

                if(!empty($vs['user_id'])){

                    $vs['coach_onlinetime'] = $log_model->where(['coach_id'=>$vs['coach_id']])->whereBetween("create_time","$start_time,$end_time")->sum('time');

                    $rest_time = $time_list_model->where(['coach_id'=>$vs['coach_id'],'status'=>0,'is_click'=>1,'is_work'=>1])->where('time_str','<',time())->where('time_str','between',"$start_time,$end_time")->field('SUM(time_str_end-time_str) as time_long')->find();

                    $rest_time = $rest_time->time_long;

                    $vs['coach_onlinetime'] = floor(($vs['coach_onlinetime']-$rest_time)/60);

                    $vs['coach_onlinetime'] = $vs['coach_onlinetime']>0?$vs['coach_onlinetime']:0;

                }else{

                    $vs['coach_onlinetime'] = 0;
                }

                $arr['data'][] = $vs;
            }
        }

        $arr['total'] = count($data);

        $arr['current_page'] = $input['page'];

        $arr['per_page']     = $input['limit'];

        $arr['last_page']    = ceil($arr['total']/$input['limit']);

        return $this->success($arr);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-07-13 16:51
     * @功能说明:
     */
    public function coachCashData(){

        $input = $this->_param;

        $coach = $this->model->dataInfo(['id'=>$input['id']],'service_price,car_price');

        $coach['service_price'] += $coach['car_price'];

        $coach['service_price'] = round($coach['service_price'],2);

        $achievement = $this->model->getCurrentAchievement($input['id'],$this->_uniacid);

        $achievement['coach_time_long'] = floor($achievement['coach_time_long']/60);

        $coach = array_merge($coach,$achievement);

        $custom_model  = new CustomBalance();

        $coach_level = $custom_model->getCoachCustomBalance($input['id']);

        $true_coach_level = $this->model->getCoachLevel($input['id'],$this->_uniacid);

        if(empty($coach_level)){

            $coach_level = $true_coach_level;
        }

        $coach_level_model = new CoachLevel();
        //技师周期评分
        $coach['coach_star'] = $coach_level_model->getCoachStar($input['id'],$this->_uniacid);

        $coach['balance'] = !empty($coach_level['balance'])?$coach_level['balance']:0;

        if(!empty($true_coach_level)&&$true_coach_level['online_change_integral_status']==1){

            $more_online_time = floor($achievement['online_time'] - $true_coach_level['online_time']);

            if($more_online_time>0){

                $change_integral = $more_online_time*$true_coach_level['online_change_integral'];

                $coach['coach_integral']+= $change_integral;
            }
        }

        return $this->success($coach);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-19 15:42
     * @功能说明:修改技师余额
     */
    public function updateCoachCash(){

        $input = $this->_input;

        $record_model = new CashUpdateRecord();

        $coach_info = $this->model->dataInfo(['id'=>$input['coach_id']]);

        if($coach_info['service_price']<$input['cash']&&$input['is_add']==0){

            $this->errorMsg('服务费小于修改金额');
        }

        $insert = [

            'uniacid' => $this->_uniacid,

            'coach_id'=> $input['coach_id'],

            'user_id' => $coach_info['user_id'],

            'cash'    => $input['cash'],

            'is_add'  => $input['is_add'],

            'text'    => $input['text'],

            'before_cash'=> $coach_info['service_price'],

            'create_user'=> $this->_user['id'],

            'after_cash' => $input['is_add']==1?$coach_info['service_price']+$input['cash']:$coach_info['service_price']-$input['cash'],
        ];

        Db::startTrans();

        $res = $record_model->dataAdd($insert);

        if($res==0){

            Db::rollback();

            $this->errorMsg('修改失败');
        }

        $res = $this->model->dataUpdate(['id'=>$insert['coach_id'],'service_price'=>$coach_info['service_price']],['service_price'=>$insert['after_cash']]);

        if($res==0){

            Db::rollback();

            $this->errorMsg('修改失败');
        }

        Db::commit();

        return $this->success($res);
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

        if(!empty($input['coach_id'])){

            $dis[] = ['coach_id','=',$input['coach_id']];
        }

        if(!empty($input['type'])){

            $dis[] = ['type','=',$input['type']];
        }

        $dis[] = ['admin_type','not in',[-2,-1]];

        $record_model = new CashUpdateRecord();

        if(!empty($input['name'])){

            $id = $record_model->getDataByTitle($input['name']);

            $dis[] = ['id','in',$id];
        }

        $admin_model = new \app\massage\model\Admin();

        if($this->_user['is_admin']==0){

            foreach ($dis as &$vv){

                $vv[0] = 'a.'.$vv[0];
            }

            $dis[] = ['a.type','in',[1,2,3,4,5,6]];

            $data = $record_model->updateRecordList($dis,$this->_user['admin_id'],$input['limit']);

        }else{

            $data = $record_model->dataList($dis,$input['limit']);
        }

        $comm_model = new Commission();

        $coach_model= new Coach();

        $change_log_model = new CoachChangeLog();

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

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
     * @DataTime: 2023-09-13 10:44
     * @功能说明:技师关联的服务列表
     */
    public function coachServiceList(){

        $input = $this->_param;

        $dis[] = ['a.uniacid','=',$this->_uniacid];

        $dis[] = ['a.status','=',1];

        $dis[] = ['b.coach_id','=',$input['coach_id']];

        $dis[] = ['a.check_status','=',2];

        $service_model = new Service();

        $data = $service_model->getCoachService($dis,$input['limit']);

        $admin_model = new \app\massage\model\Admin();

        $city_model  = new City();

        $industry_model = new Type();

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $v['industry_title'] = $industry_model->where(['id'=>$v['industry_type'],'status'=>1])->value('title');

                if(!empty($v['admin_id'])){

                    $v['admin_name'] = $admin_model->where(['id'=>$v['admin_id']])->value('agent_name');

                    $city_id = $admin_model->where(['id'=>$v['admin_id']])->value('city_id');

                    $v['admin_city'] = $city_model->where(['id'=>$city_id])->value('title');

                }else{

                    $v['admin_name'] = '平台';
                }
            }
        }

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-09-13 10:44
     * @功能说明:技师关联的服务列表
     */
    public function coachNoServiceList(){

        $input = $this->_param;

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['status','=',1];

        $dis[] = ['check_status','=',2];

        if(!empty($input['industry_type'])){

            $dis[] = ['industry_type','=',$input['industry_type']];
        }

        $ser_coach_model = new ServiceCoach();

        $have_id = $ser_coach_model->where(['coach_id'=>$input['coach_id']])->column('ser_id');

        $dis[] = ['id','not in',$have_id];

        if(!empty($input['title'])){

            $dis[] = ['title','like','%'.$input['title'].'%'];
        }

        $admin_id = $this->model->where(['id'=>$input['coach_id']])->value('admin_id');

        $only_admin = !empty($input['only_admin'])?$input['only_admin']:0;

        if($only_admin==1){

            $dis[] = ['admin_id','=',$admin_id];

        }else{
            //技师只能关联自己代理商或者平台的服务
            $dis[] = ['admin_id','in',[$admin_id,0]];
        }
        $service_model = new Service();

        $data = $service_model->dataList($dis,$input['limit']);

        $admin_model = new \app\massage\model\Admin();

        $city_model  = new City();

        $industry_model = new Type();

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $v['industry_title'] = $industry_model->where(['id'=>$v['industry_type'],'status'=>1])->value('title');

                if(!empty($v['admin_id'])){

                    $v['admin_name'] = $admin_model->where(['id'=>$v['admin_id']])->value('agent_name');

                    $city_id = $admin_model->where(['id'=>$v['admin_id']])->value('city_id');

                    $v['admin_city'] = $city_model->where(['id'=>$city_id])->value('title');
                }else{

                    $v['admin_name'] = '平台';
                }
            }
        }

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-09-13 11:30
     * @功能说明:技师关联服务
     */
    public function addCoachService(){

        $input = $this->_input;

        $ser_coach_model = new ServiceCoach();

        if(empty($input['data'])){

            $this->errorMsg('请选择内容');

        }

        foreach ($input['data'] as $value){

            $insert['coach_id'] = $input['coach_id'];

            $insert['uniacid']  = $this->_uniacid;

            $insert['ser_id']   = $value['service_id'];

            $insert['price']    = $value['price'];

            $ser_coach_model->dataAdd($insert);
        }

        return $this->success(true);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-09-13 11:37
     * @功能说明:取消关联技师服务
     */
    public function delCoachService(){

        $input = $this->_input;

        $ser_coach_model = new ServiceCoach();

        $dis = [

            'id' => $input['id']
        ];

        $res = $ser_coach_model->where($dis)->delete();

        return $this->success($res);

    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-09-13 11:37
     * @功能说明:修改关联技师服务价格
     */
    public function updateCoachServicePrice(){

        $input = $this->_input;

        $ser_coach_model = new ServiceCoach();

        $dis = [

            'id' => $input['id']
        ];

        $res = $ser_coach_model->where($dis)->update(['price'=>$input['price']]);

        return $this->success($res);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-11-20 14:21
     * @功能说明:添加水印图片
     */
    public function addWatermarkImg(){

        $input = $this->_input;

        $watermark_model = new WatermarkList();
        //这里最好还需要远程删图片
        $watermark_model->where(['coach_id'=>$input['coach_id'],'type'=>$input['type']])->delete();

        if(!empty($input['img'])){

            foreach ($input['img'] as $key=>$value){

                $insert[$key] = [

                    'uniacid' => $this->_uniacid,
                    //技师id
                    'coach_id'=> $input['coach_id'],
                    //类型
                    'type'    => $input['type'],
                    //原图片
                    'original_img' => $value['original_img'],
                    //水印图片
                    'watermark_img' => $value['watermark_img'],
                    //水印透明度
                    'watermark_transparent' => $value['watermark_transparent'],
                ];
            }

            $res = $watermark_model->saveAll($insert);

            return $this->success($res);
        }

        return $this->success(true);

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-11-20 14:21
     * @功能说明:水印图片详情
     */
    public function WatermarkImgInfo(){

        $input = $this->_param;

        $watermark_model = new WatermarkList();

        $dis = [

            'coach_id' => $input['coach_id'],

            'type'     => $input['type']
        ];

        $res = $watermark_model->where($dis)->select()->toArray();

        return $this->success($res);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-12-22 16:30
     * @功能说明:修改技师经纪人
     */
    public function updateCoachBroker(){

        $input = $this->_input;

        $broker_model = new CoachBroker();

        if(!empty($input['broker_id'])){

            $broker = $broker_model->dataInfo(['id'=>$input['broker_id'],'status'=>2]);

            if(empty($broker)){

                $this->errorMsg('经纪人不存在');
            }

            $update = [

                'broker_id' => $broker['id'],

                'partner_id'=> $broker['user_id']
            ];
        }else{

            $update = [

                'broker_id' => 0,

                'partner_id'=> 0
            ];
        }

        $res = $this->model->dataUpdate(['id'=>$input['coach_id']],$update);

        return $this->success($res);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-01-24 14:51
     * @功能说明:获取技师日程
     */
    public function getTime(){

        $input = $this->_param;

        $coach = $this->model->dataInfo(['id'=>$input['coach_id']]);

        $time_model = new CoachTimeList();

        $data = $time_model->getTimeData($coach['start_time'], $coach['end_time'], $coach['id'], $input['dat_str'],1);

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-01-24 16:56
     * @功能说明:设置技师休息时间
     */
    public function setTimeConfig(){

        $input = $this->_input;

        $log_model = new WorkLog();
        //结算在线时间
        $log_model->updateTimeOnline($input['coach_id']);

        $input['uniacid']  = $this->_uniacid;

        $res = Coach::timeEdit($input);

        if ($res === false) {

            return $this->error('设置失败');
        }
        return $this->success(true);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-02-27 17:39
     * @功能说明:设置技师账号
     */
    public function setCoachAccount(){

        $input = $this->_input;

        $dis = [

            'coach_id' => $input['coach_id'],

            'status'   => 1
        ];

        $account_model = new CoachAccount();

        $find = $account_model->dataInfo($dis);

        $insert = [

            'uniacid'   => $this->_uniacid,

            'user_name' => $input['user_name'],

            'pass_word'=> $input['pass_word'],

            'pass_word_text'=> checkPass($input['pass_word']),

            'status'    => 1
        ];

        if(!empty($find)){
            //编辑
            $check = $account_model->where($insert)->where('id','<>',$find['id'])->find();

            if(!empty($check)){

                return $this->error('该账号密码已被设置');
            }

            $insert['coach_id'] = $input['coach_id'];

            $res = $account_model->dataUpdate(['id'=>$find['id']],$insert);

        }else{
            //新增
            $check = $account_model->where($insert)->find();

            if(!empty($check)){

                return $this->error('该账号密码已被设置');
            }
            $insert['coach_id'] = $input['coach_id'];

            $res = $account_model->dataAdd($insert);
        }

        return $this->success($res);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-02-28 14:34
     * @功能说明:技师更换用户
     */
    public function changeUser(){

        $input = $this->_input;

        $coach_update_phone_code_status = getConfigSetting($this->_uniacid,'coach_update_phone_code_status');

        if($coach_update_phone_code_status==1){

            if(empty($input['phone_code'])){

                return $this->error('请输入验证码');
            }

            $phone  = getConfigSetting($this->_uniacid,'login_auth_phone');

            $key    = $phone.'updateCoachUserKey';

            if($input['phone_code']!= getCache($key,$this->_uniacid)){

                $this->errorMsg('验证码错误');
            }
        }

        $find = $this->model->where(['user_id'=>$input['user_id']])->where('status','in',[1,2,3])->find();

        if(!empty($find)){

            return $this->error('该用户已经申请过技师');
        }

        $res = $this->model->where(['user_id'=>$input['user_id']])->where('id','<>',$input['coach_id'])->update(['status'=>-1]);

        $res = $this->model->dataUpdate(['id'=>$input['coach_id']],['user_id'=>$input['user_id']]);

        if($coach_update_phone_code_status==1){

            delCache($key,$this->_uniacid);
        }

        return $this->success($res);
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

        $phone  = getConfigSetting($this->_uniacid,'login_auth_phone');

        $key    = 'updateCoachUserKey';

        $res    = $config->sendSmsCode($phone,$this->_uniacid,$key);

        if(!empty($res['Message'])&&$res['Message']=='OK'){

            return $this->success(1);

        }else{

            return $this->error($res['Message']);
        }
    }



    //'1公众号搜索、2代理商邀请技师二维码、3经纪人邀请技师二维码、4代理商邀请分销员二维码、5分销员邀请下级二维码、6分销员邀请粉丝二维码、7代理商邀请业务员二维码、8代理商邀请渠道商二维码、9业务员邀请渠道商二维码、10渠道码、11原生渠道码;
    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-03-20 15:11
     * @功能说明:
     */
    public function coachTypeList(){

        $input = $this->_param;

        $type_model = new CoachType();

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['status','>',-1];

        if(!empty($input['title'])){

            $dis[] = ['title','like',"%".$input['title'].'%'];

        }

        $data = $type_model->dataList($dis,$input['limit']);

        return $this->success($data);
    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-03-20 15:11
     * @功能说明:
     */
    public function coachTypeSelect(){

        $input = $this->_param;

        $type_model = new CoachType();

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['status','=',1];

        $data = $type_model->where($dis)->order('top desc,id desc')->select()->toArray();

        return $this->success($data);
    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-03-20 15:11
     * @功能说明:
     */
    public function coachTypeAdd(){

        $input = $this->_input;

        $type_model = new CoachType();

        $input['uniacid'] = $this->_uniacid;

        $data = $type_model->dataAdd($input);

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-03-20 15:11
     * @功能说明:
     */
    public function coachTypeUpdate(){

        $input = $this->_input;

        $type_model = new CoachType();

        $dis = [

            'id' => $input['id']
        ];

        $data = $type_model->dataUpdate($dis,$input);

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-03-20 15:11
     * @功能说明:
     */
    public function coachTypeInfo(){

        $input = $this->_param;

        $type_model = new CoachType();

        $dis = [

            'id' => $input['id']
        ];

        $data = $type_model->dataInfo($dis);

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-06-05 15:15
     * @功能说明:技师公告列表
     */
    public function coachNoticeList(){

        $input = $this->_param;

        $notice_model = new CoachNotice();

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['true_status','>',-1];

        $dis[] = ['status','>',-1];

        if(!empty($input['title'])){

            $dis[] = ['title','like','%'.$input['title'].'%'];
        }

        $data = $notice_model->dataList($dis,$input['limit']);

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-06-05 15:15
     * @功能说明:技师公告列表
     */
    public function coachNoticeAdd(){

        $input = $this->_input;

        $notice_model = new CoachNotice();

        $input['uniacid'] = $this->_uniacid;

        $input['create_user'] = $this->_user['id'];

        $input['status'] = 0;
        //只能有一个置顶
        if(isset($input['status'])&&$input['status']==1){

            $find = $notice_model->where(['uniacid'=>$this->_uniacid,'status'=>1])->where('true_status','>',-1)->find();

            if(!empty($find)){

                return $this->error('只能置顶一个公告');
            }
        }

        $data = $notice_model->dataAdd($input);

        return $this->success($data);
    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-06-05 15:15
     * @功能说明:技师公告列表
     */
    public function coachNoticeUpdate(){

        $input = $this->_input;

        $notice_model = new CoachNotice();

        $dis = [

            'id' => $input['id']
        ];
        //只能有一个置顶
        if(isset($input['status'])&&$input['status']==1){

            $find = $notice_model->where(['uniacid'=>$this->_uniacid,'status'=>1])->where('true_status','>',-1)->where('id','<>',$input['id'])->find();

            if(!empty($find)){

                return $this->error('只能置顶一个公告');
            }
        }

        $data = $notice_model->dataUpdate($dis,$input);

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-06-05 15:15
     * @功能说明:技师公告列表
     */
    public function coachNoticeInfo(){

        $input = $this->_param;

        $notice_model = new CoachNotice();

        $dis = [

            'id' => $input['id']
        ];

        $data = $notice_model->dataInfo($dis);

        return $this->success($data);
    }


    /**
     * @author chenniang
     * @DataTime: 2024-08-14 16:18
     * @功能说明:技师标签
     */
    public function coachIconList(){

        $input = $this->_param;

        if($this->_user['is_admin']==0){

            return $this->error('暂无权限');
        }

        $model = new IconCoach();

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['status','>',-1];

        $data = $model->dataList($dis,$input['limit']);

        return $this->success($data);
    }


    /**
     * @author chenniang
     * @DataTime: 2024-08-14 16:20
     * @功能说明:添加技师个性标签
     */
    public function coachIconAdd(){

        $input = $this->_input;

        if($this->_user['is_admin']==0){

            return $this->error('暂无权限');
        }

        $insert = [

            'uniacid' => $this->_uniacid,

            'title'   => $input['title'],

            'create_time' => time()
        ];

        $model = new IconCoach();

        $res = $model->dataAdd($insert);

        return $this->success($res);
    }


    /**
     * @author chenniang
     * @DataTime: 2024-08-14 16:20
     * @功能说明:添加技师个性标签
     */
    public function coachIconUpdate(){

        $input = $this->_input;

        if($this->_user['is_admin']==0){

            return $this->error('暂无权限');
        }

        $dis= [

            'id'=> $input['id']
        ];

        $model = new IconCoach();

        $res = $model->dataUpdate($dis,$input);

        return $this->success($res);
    }


    /**
     * @author chenniang
     * @DataTime: 2024-08-14 16:18
     * @功能说明:技师标签
     */
    public function coachIconSelect(){

        $input = $this->_param;

//        if($this->_user['is_admin']==0){
//
//            return $this->error('暂无权限');
//        }

        $model = new IconCoach();

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['status','=',1];

        $data = $model->where($dis)->order('id desc')->select()->toArray();

        return $this->success($data);
    }


    /**
     * @author chenniang
     * @DataTime: 2024-08-14 16:18
     * @功能说明:岗位标签
     */
    public function stationIconList()
    {

        $input = $this->_param;

//        if ($this->_user['is_admin'] == 0) {
//
//            return $this->error('暂无权限');
//        }

        $model = new StationIcon();

        $dis[] = ['uniacid', '=', $this->_uniacid];

        $dis[] = ['status', '>', -1];

        $data = $model->dataList($dis, $input['limit']);

        $type = Type::where('uniacid', $this->_uniacid)->column('title', 'id');

        if ($data['data']) {

            foreach ($data['data'] as &$item) {

                $item['industry_type_name'] = $type[$item['industry_type']];
            }
        }
        return $this->success($data);
    }


    /**
     * @author chenniang
     * @DataTime: 2024-08-14 16:20
     * @功能说明:添加岗位标签
     */
    public function stationIconAdd()
    {

        $input = $this->_input;

//        if ($this->_user['is_admin'] == 0) {
//
//            return $this->error('暂无权限');
//        }

        $insert = [

            'uniacid' => $this->_uniacid,

            'title' => $input['title'],

            'industry_type' => $input['industry_type'],

            'create_time' => time()
        ];

        $model = new StationIcon();

        $res = $model->dataAdd($insert);

        return $this->success($res);
    }


    /**
     * @author chenniang
     * @DataTime: 2024-08-14 16:20
     * @功能说明:编辑岗位标签
     */
    public function stationIconUpdate()
    {

        $input = $this->_input;

//        if ($this->_user['is_admin'] == 0) {
//
//            return $this->error('暂无权限');
//        }

        $dis = [

            'id' => $input['id']
        ];

        $model = new StationIcon();

        $res = $model->dataUpdate($dis, $input);

        return $this->success($res);
    }


    /**
     * @author chenniang
     * @DataTime: 2024-08-14 16:18
     * @功能说明:技师标签
     */
    public function stationIconSelect()
    {

        $input = $this->_param;

//        if ($this->_user['is_admin'] == 0) {
//
//            return $this->error('暂无权限');
//        }
        $model = new StationIcon();

        $dis[] = ['uniacid', '=', $this->_uniacid];

        $dis[] = ['status', '=', 1];

        if (!empty($input['industry_type'])) {

            $dis[] = ['industry_type', '=', $input['industry_type']];
        }

        $data = $model->where($dis)->order('id desc')->select()->toArray();

        return $this->success($data);
    }


    /**
     * @author chenniang
     * @DataTime: 2024-11-22 17:42
     * @功能说明:获取可预约天数
     */
    public function dayText(){

        $config_model = new Config();

        $config = $config_model->dataInfo(['uniacid'=>$this->_uniacid]);

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

        $coach     = $coach_model->dataInfo(['id'=>$input['coach_id']]);

        $is_store  = !empty($input['is_store'])?$input['is_store']:0;

        $time_long = !empty($input['time_long'])?$input['time_long']:0;

        $coach_time_model = new CoachTimeList();

        $data = $coach_time_model->getTimeData($coach['start_time'],$coach['end_time'],$coach['id'],$input['day'],0,$is_store,$time_long);

        return $this->success($data);
    }


    /**
     * @author chenniang
     * @DataTime: 2024-11-22 18:00
     * @功能说明:批量更新技师地址
     */
    public function updateCoachAddress(){

        $input = $this->_input;

        $data = $this->model->where('id','in',$input['coach_id'])->field('id as coach_id,uniacid,lng,lat')->select()->toArray();

        if(!empty($data)){

            foreach ($data as $v){

                getCoachAddress($v['lng'],$v['lat'],$v['uniacid'],$v['coach_id']);
            }
        }

        return $this->success(true);
    }


    /**
     * @author chenniang
     * @DataTime: 2024-11-25 10:54
     * @功能说明:技师排班列表
     */
    public function coachWorkList(){

        $input= $this->_param;

        $data = lbData('massage/admin/Index/coachList',$this->_token,1,$input);

        $data = $data['data'];

        return $this->success($data);
    }






}
