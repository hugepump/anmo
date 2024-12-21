<?php

namespace app\massage\controller;

use app\abnormalorder\model\OrderInfo;
use app\abnormalorder\model\OrderInfoHandle;
use app\abnormalorder\model\OrderList;
use app\abnormalorder\model\OrderProcess;
use app\adapay\model\Bank;
use app\adapay\model\Member;
use app\ApiRest;

use app\balancediscount\model\OrderShare;
use app\fdd\model\FddAgreementRecord;
use app\fdd\model\FddAttestationRecord;
use app\fdd\model\FddConfig;
use app\fxq\info\PermissionFxq;
use app\fxq\model\FxqConfig;
use app\fxq\model\FxqContract;
use app\fxq\model\FxqContractFile;
use app\fxq\model\FxqFaceCheck;
use app\fxq\model\FxqIdCheck;
use app\industrytype\model\Type;
use app\massage\model\Appeal;
use app\massage\model\BalanceOrder;
use app\massage\model\BalanceWater;
use app\massage\model\CashUpdateRecord;
use app\massage\model\City;
use app\massage\model\Coach;

use app\massage\model\CoachAccount;
use app\massage\model\CoachChangeLog;
use app\massage\model\CoachLevel;
use app\massage\model\CoachNotice;
use app\massage\model\CoachUpdate;
use app\massage\model\CoachWater;
use app\massage\model\Comment;
use app\massage\model\Commission;
use app\massage\model\CommShare;
use app\massage\model\Config;
use app\massage\model\ConfigSetting;
use app\massage\model\CouponRecord;
use app\massage\model\CreditConfig;
use app\massage\model\CreditRecord;
use app\massage\model\CustomBalance;
use app\massage\model\Feedback;
use app\massage\model\Goods;

use app\massage\model\Integral;
use app\massage\model\MassageConfig;
use app\massage\model\NoticeList;
use app\massage\model\Order;
use app\massage\model\OrderAddress;
use app\massage\model\OrderData;
use app\massage\model\OrderGoods;
use app\massage\model\OrderLog;
use app\massage\model\Police;
use app\massage\model\RefundOrder;
use app\massage\model\RefundOrderGoods;
use app\massage\model\SendMsgConfig;
use app\massage\model\ShieldList;
use app\massage\model\ShopCarte;
use app\massage\model\ShopGoods;
use app\massage\model\ShortCodeConfig;
use app\massage\model\StoreCoach;
use app\massage\model\StoreCoachUpdate;
use app\massage\model\StoreList;
use app\massage\model\Trajectory;
use app\massage\model\User;
use app\massage\model\UserComment;
use app\massage\model\UserLabelData;
use app\massage\model\UserLabelList;
use app\massage\model\Wallet;
use app\massage\model\WorkLog;
use app\node\model\RoleList;
use longbingcore\permissions\AdminMenu;
use longbingcore\wxcore\Adapay;
use longbingcore\wxcore\Fdd;
use longbingcore\wxcore\Fxq;
use longbingcore\wxcore\WxSetting;
use think\App;
use think\facade\Db;
use think\Request;


class IndexCoach extends ApiRest
{

    protected $order_model;

    protected $model;

    protected $cap_info;

    public function __construct(App $app){

        parent::__construct($app);

        $this->model = new Coach();

        $this->order_model = new Order();

        $this->cap_info = $this->coachDataInfo();
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-09 14:52
     * @功能说明:技师详情
     */
    public function coachDataInfo(){

        if(!empty($this->_user['coach_account_login'])){

            $cap_dis[] = ['id', '=', $this->_user['coach_id']];

        }else{

            $cap_dis[] = ['user_id', '=', $this->getUserId()];
           // $cap_dis[] = ['id', '=', 1514];
        }

        $cap_dis[] = ['status', 'in', [2,3]];

        $cap_info = $this->model->dataInfo($cap_dis);

        if (empty($cap_info)) {

            $this->errorMsg('你还不是技师');
        }

        $attestation_model = new FddAttestationRecord();

        $find = $attestation_model->dataInfo(['user_id'=>$cap_info['user_id'],'status'=>3]);

        $cap_info['is_fdd'] = !empty($find)?1:0;


        $fxq = FxqContract::getInfo([['admin_id', '=', $cap_info['admin_id']], ['coach_id', '=', $cap_info['id']], ['status', '=', 3], ['end_time', '>', time()]]);

        $cap_info['is_fxq'] = !empty($fxq) ? 1 : 0;

        return $cap_info;
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-08 11:39
     * @功能说明:技师首页
     */
    public function coachIndex(){

        $cap_info = $this->cap_info;

        $city_model = new City();

        $cap_info['city'] = $city_model->where(['id'=>$cap_info['city_id']])->value('title');
        //技师真正的等级
        $coach_level = $this->model->getCoachLevel($cap_info['id'],$this->_uniacid);
        //技师等级
        $cap_info['coach_level']= $this->model->coachLevelInfo($coach_level);

        $cap_info['text_type']  = $this->model->getCoachWorkStatus($cap_info['id'],$this->_uniacid);

        $record_model= new FddAgreementRecord();

        $config_model= new FddConfig();

        $dis = [

            'user_id'  => $this->_user['id'],

            'status'   => 2,

            'admin_id' => $cap_info['admin_id']
        ];
        //待签约
        $fdd_agreement = $record_model->where($dis)->field('download_url,viewpdf_url,end_time')->order('id desc')->find();

        $dis['status'] = 4;
        //已经签约待合同
        $cap_info['fdd_agreement'] = $record_model->where($dis)->field('download_url,viewpdf_url,end_time')->order('id desc')->find();

        $fdd_status = $config_model->getStatus($this->_uniacid);

        $cap_info['fdd_auth_status'] = $fdd_status;
        //开启了法大大
        if($fdd_status==1){

            if(!empty($fdd_agreement)){

                $cap_info['fdd_status'] = 1;

            }else{

                $cap_info['fdd_status'] = 0;
            }
        }else{

            $cap_info['fdd_status'] = 2;
        }

        $level_model = new CoachLevel();

        $industry_model = new Type();
        //技师最高可提成
        $cap_info['max_level'] = $level_model->where(['uniacid'=>$this->_uniacid,'status'=>1])->max('balance');

        $cap_info['address']   = getCoachAddress($cap_info['lng'],$cap_info['lat'],$cap_info['uniacid'],$cap_info['id']);

        $account_model = new CoachAccount();
        //账号
        $cap_info['account_info'] = $account_model->dataInfo(['coach_id'=>$cap_info['id'],'status'=>1]);

        $this->model->coachCashInit($cap_info['id'],$cap_info['version'],$cap_info['uniacid']);

        $notice_model = new CoachNotice();

        $cap_info['notice_info'] = $notice_model->where(['uniacid'=>$this->_uniacid,'status'=>1,'true_status'=>1])->order('id desc')->find();

        $cap_info['industry_info'] = $industry_model->where(['id'=>$cap_info['industry_type'],'status'=>1])->find();

        $cap_info['address_update_time'] = !empty($cap_info['address_update_time'])?date('Y-m-d H:i:s',$cap_info['address_update_time']):'';
        //放心签实名认证
        $check_type = getConfigSetting($this->_uniacid,'fxq_check_type');

        if ($check_type == 1) {

            $fxq = FxqIdCheck::where(['user_id' => $this->getUserId(), 'uniacid' => $this->_uniacid])->count();
        } else {

            $fxq = FxqFaceCheck::where(['user_id' => $this->getUserId(), 'uniacid' => $this->_uniacid, 'status' => 2])->count();
        }

        $cap_info['is_fxq_check'] = !empty($fxq) ? 1 : 0;

        //放心签是否开启
        $cap_info['fxq_auth_status'] = FxqConfig::getStatus($this->_uniacid,$cap_info['admin_id']);

        //放心签合同
        $fxq = FxqContract::getInfo([['admin_id', '=', $cap_info['admin_id']], ['coach_id', '=', $cap_info['id']], ['status', '=', 3], ['end_time', '>', time()]], 'id,admin_id,status,contract_years,create_time,start_time,end_time');

        $cap_info['fxq_info'] = empty($fxq) ? '' : $fxq;
        //绑定门店(新)
        $cap_info['store'] = StoreCoach::getStoreList($cap_info['id']);

        return $this->success($cap_info);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-23 09:59
     * @功能说明:修改技师信息
     */
    public function coachUpdate()
    {
        $input = $this->_input;

        $dis = [

            'id' => $this->cap_info['id']
        ];

        if (!empty($input['id_card'])) {

            $input['id_card'] = implode(',', $input['id_card']);
        }

        if (!empty($input['license'])) {

            $input['license'] = implode(',', $input['license']);
        }

        if (!empty($input['self_img'])) {

            $input['self_img'] = implode(',', $input['self_img']);
        }

        if(isset($input['service_price'])){

            unset($input['service_price']);
        }

        if(isset($input['car_price'])){

            unset($input['car_price']);
        }

//        if(isset($input['lng'])&&empty()$input['lng']==0){
//
//            return $this->success(true);
//
//        }

        $res = $this->model->dataUpdate($dis, $input);

        return $this->success($res);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-23 09:59
     * @功能说明:修改技师信息 （重新审核和二次认证）
     */
    public function coachUpdateV2(){

        $input = $this->_input;

        $dis = [

            'id' => $this->cap_info['id']
        ];

        $input['uniacid'] = $this->_uniacid;

        $input['user_id'] = $this->getUserId();

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

            if($this->cap_info['auth_status']!=2){

                StoreCoach::where(['coach_id'=>$this->cap_info['id']])->delete();
            }

            $store = $input['store'];

            unset($input['store']);
        }
        //重新审核
        if($this->cap_info['auth_status']==2){

            $input['coach_id'] = $this->cap_info['id'];

            $update_model = new CoachUpdate();

            $update_model->dataUpdate(['coach_id'=>$this->cap_info['id'],'status'=>1],['status'=>-1]);

            $update_model->dataAdd($input);

            $update_id = $update_model->getLastInsID();

            $res = $this->model->dataUpdate($dis, ['is_update' => 1]);

            if(!empty($store)){

                foreach ($store as $key=>$value){

                    $store_insert[$key] = [

                        'uniacid' => $this->_uniacid,

                        'store_id'=> $value,

                        'coach_id'=> $this->cap_info['id'],

                        'update_id'=> $update_id,
                    ];
                }

                StoreCoachUpdate::createAll($store_insert);
            }

        }else{

            $input['auth_status'] = 1;

            $res = $this->model->dataUpdate($dis,$input);

            if(!empty($store)){

                foreach ($store as $key=>$value){

                    $store_insert[$key] = [

                        'uniacid' => $this->_uniacid,

                        'store_id'=> $value,

                        'coach_id'=> $this->cap_info['id'],
                    ];
                }
                StoreCoach::createAll($store_insert);
            }
        }

        return $this->success($res);

    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-19 15:48
     * @功能说明:个人中心
     */
    public function index()
    {

        $data = $this->getUserInfo();

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-19 15:48
     * @功能说明:个人中心
     */
    public function orderList()
    {

        $input = $this->_param;

        $dis[] = ['a.uniacid', '=', $this->_uniacid];

        $dis[] = ['a.is_add', '=', 0];

        $dis[] = ['a.coach_show', '=', 1];

        $dis[] = ['a.coach_id', '=', $this->cap_info['id']];

        $where = [];

        if (!empty($input['name'])) {

            $where[] = ['b.goods_name', 'like', '%' . $input['name'] . '%'];

            $where[] = ['a.order_code', 'like', '%' . $input['name'] . '%'];
        }

        $sort = 'a.id desc';

        if(!empty($input['pay_type'])){

            if(in_array($input['pay_type'],[2,5,6])){

                $sort = 'a.start_time,a.id desc';
            }

            if ($input['pay_type'] == 5) {

                $dis[] = ['a.pay_type', 'in', [3, 4, 5]];

            } else {

                $dis[] = ['a.pay_type', '=', $input['pay_type']];
            }
        }else{

            $dis[] = ['a.pay_type', '>', 1];
        }

        $order_model = new Order();

        $data = $order_model->coachDataList($dis, $where,10,$sort);

        if(!empty($data['data'])){

            $shield_model = new ShieldList();

            $user_model   = new User();

            $comm_model   = new Commission();

            foreach ($data['data'] as &$v){
                //是否还能屏蔽用户
                $can_shield = $shield_model->dataInfo(['user_id'=>$v['user_id'],'coach_id'=>$v['coach_id'],'type'=>3]);

                $user_status= $user_model->where(['id'=>$v['user_id']])->value('user_status');

                $v['can_shield'] = empty($can_shield)&&$user_status==1?1:0;

                $v['car_price']  = $comm_model->where(['order_id'=>$v['id'],'type'=>8,'top_id'=>$this->cap_info['id']])->sum('cash');
            }
        }
        //待接单数量
        $data['agent_order_count']   = $order_model->where(['coach_id' => $this->cap_info['id'], 'pay_type' => 2,'is_add'=>0])->count();
        $data['wait_order_count']    = $order_model->where([['coach_id', '=', $this->cap_info['id']], ['pay_type', 'in', [3, 4, 5]],['is_add','=',0]])->count();
        $data['service_order_count'] = $order_model->where(['coach_id' => $this->cap_info['id'], 'pay_type' => 6,'is_add'=>0])->count();

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-16 18:35
     * @功能说明:删除订单
     */
    public function delOrder(){

        $input = $this->_input;

        $order = $this->order_model->dataInfo(['id'=>$input['id'],'coach_id'=>$this->cap_info['id']]);

        if(empty($order)){

            $this->errorMsg('订单未找到');
        }

        if(!in_array($order['pay_type'],[-1,7])){

            $this->errorMsg('只有取消或完成的订单才能删除');
        }

        $res = $this->order_model->dataUpdate(['id'=>$input['id']],['coach_show'=>0]);

        $res = $this->order_model->dataUpdate(['add_pid'=>$input['id']],['coach_show'=>0]);

        return $this->success($res);
    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-24 13:33
     * @功能说明:技师申请提现
     */
    public function applyWallet(){

        $input = $this->_input;

        $key = 'cap_wallet' . $this->cap_info['id'];
        //加一个锁防止重复提交
        incCache($key, 1, $this->_uniacid,30);

        $value = getCache($key,$this->_uniacid);

        if ($value!=1) {

            decCache($key,1, $this->_uniacid);

            $this->errorMsg('网络错误，请刷新重试');
        }

        if (empty($input['apply_price']) || $input['apply_price'] < 0.01) {

            decCache($key,1, $this->_uniacid);

            $this->errorMsg('提现费最低一分');
        }

        $coach_info = $this->model->dataInfo(['id'=>$this->cap_info['id']]);

        $coach_cash = $input['type'] == 1?$coach_info['service_price']:$coach_info['car_price'];

        $coach_wallet_cash_type = getConfigSetting($this->_uniacid,'coach_wallet_cash_type');

        $coach_service_wallet_cash_t_type = getConfigSetting($this->_uniacid,'coach_service_wallet_cash_t_type');

        if($input['type']==1){

            if($coach_wallet_cash_type==0){
                //可提现佣金
                if($coach_service_wallet_cash_t_type!=0){

                    $coach_cash = $this->model->getCoachCashByHalfMonthV2($this->cap_info['id'],$coach_cash,$input['type'],$coach_service_wallet_cash_t_type);

                    if($coach_service_wallet_cash_t_type==1){

                        if(date('Y-m-d')!=date('Y-m-01')&&date('Y-m-d')!=date('Y-m-16')){

                            decCache($key,1, $this->_uniacid);

                            $this->errorMsg('只有指定日期才能提现');
                        }

                    }else{

                        $currentWeekDay = date('w', time());

                        if($currentWeekDay!=1){

                            decCache($key,1, $this->_uniacid);

                            $this->errorMsg('只有指定日期才能提现');
                        }
                    }
                }

            }else{

                $coach_cash = $this->model->getCoachCashByHalfMonth($this->cap_info['id'],$coach_cash,$input['type']);
            }
        }

        if($input['apply_price']>$coach_cash) {

            decCache($key,1, $this->_uniacid);

            $this->errorMsg('余额不足');
        }
        //获取税点
        $tax_point = getConfigSetting($this->_uniacid,'tax_point');

        $balance = 100-$tax_point;

        Db::startTrans();

        $water_model = new CoachWater();
        //修改技师余额 并校验资金合法性
        $res = $water_model->updateCash($this->_uniacid,$this->cap_info['id'],$input['apply_price'],2,$input['type']);

        if ($res == false) {

            Db::rollback();
            //减掉
            decCache($key,1, $this->_uniacid);

            $this->errorMsg('申请失败');
        }

        $insert = [

            'uniacid' => $this->_uniacid,

            'user_id' => $this->getUserId(),

            'coach_id' => $this->cap_info['id'],

            'admin_id' => $this->cap_info['admin_id'],

            'total_price' => $input['apply_price'],

            'balance' => $balance,

            'apply_price' => round($input['apply_price'] * $balance / 100, 2),

            'service_price' => round( $input['apply_price'] * $tax_point / 100, 2),

            'code' => orderCode(),

            'tax_point' => $tax_point,

            'text' => $input['text'],

            'type' => $input['type'],

            'apply_transfer' => !empty($input['apply_transfer'])?$input['apply_transfer']:0,

            'last_login_type' => $this->is_app
        ];

        $wallet_model = new Wallet();
        //提交审核
        $res = $wallet_model->dataAdd($insert);

        if ($res != 1) {

            Db::rollback();
            //减掉
            decCache($key,1, $this->_uniacid);

            $this->errorMsg('申请失败');
        }

        Db::commit();
        //减掉
        decCache($key, 1,$this->_uniacid);

        return $this->success($res);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-25 17:14
     * @功能说明:楼长核销订单
     */
    public function hxOrder()
    {

        $input = $this->_input;

        $order_model = new Order();

        $order = $order_model->dataInfo(['id' => $input['id']]);

        if (empty($order)) {

            $this->errorMsg('订单未找到');
        }

        if ($order['pay_type'] != 5) {

            $this->errorMsg('订单状态错误');
        }

        if ($order['coach_id'] != $this->cap_info['id']) {

            $this->errorMsg('你不是该订单的楼长');
        }

        $refund_model = new RefundOrder();
        //判断有无申请中的退款订单
        $refund_order = $refund_model->where(['order_id' => $order['id']])->where('status','in',[1,4,5])->count();

        if ($refund_order>0) {

            $this->errorMsg('该订单正在申请退款，请先处理再核销');
        }

        $res = $order_model->hxOrder($order, $this->cap_info['id']);

        if(!empty($res['code'])){

            $this->errorMsg($res['msg']);

        }

        return $this->success($res);
    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-30 13:38
     * @功能说明:团长端佣金信息
     */
    public function capCashInfo()
    {

        $this->order_model->coachBalanceArr($this->_uniacid);

        $key = 'cap_wallet' . $this->cap_info['id'];
        //减掉
        delCache($key, $this->_uniacid);

        $wallet_model = new Wallet();

        $coach_wallet_cash_type = getConfigSetting($this->_uniacid,'coach_wallet_cash_type');

        $coach_service_wallet_cash_t_type = getConfigSetting($this->_uniacid,'coach_service_wallet_cash_t_type');

        if($coach_wallet_cash_type==0){
            //可提现佣金
            $data['cap_cash'] = $this->cap_info['service_price'];

            if($coach_service_wallet_cash_t_type!=0){

                $data['cap_cash'] = $this->model->getCoachCashByHalfMonthV2($this->cap_info['id'],$this->cap_info['service_price'],1,$coach_service_wallet_cash_t_type);
            }

        }else{

            $data['cap_cash'] = $this->model->getCoachCashByHalfMonth($this->cap_info['id'],$this->cap_info['service_price']);
        }
        //冻结金额
        $data['freeze_cash'] = round($this->cap_info['service_price'] - $data['cap_cash'],2);
        //累计提现
        $data['extract_total_price'] = $wallet_model->capCash($this->cap_info['id'], 2, 1);
        //提现中
        $data['extract_ing_price'] = $wallet_model->capCash($this->cap_info['id'], 1, 1);

        $dis = [

            'coach_id' => $this->cap_info['id'],

            'have_tx' => 0
        ];
        //未到账
        $data['no_received'] = $this->order_model->where('pay_type','>',1)->where($dis)->sum('coach_cash');

        $data['coach_level'] = $this->model->getCoachLevel($this->cap_info['id'], $this->_uniacid);
        //税点
        $data['tax_point']   =  getConfigSetting($this->_uniacid,'tax_point');

        $data['coach_service_wallet_cash_t_type'] = $coach_service_wallet_cash_t_type;

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-10-27 16:16
     * @功能说明:技师车费详情
     */
    public function coachCarCashInfo(){

     //   $coach_wallet_cash_type = getConfigSetting($this->_uniacid,'coach_wallet_cash_type');

      //  if($coach_wallet_cash_type==0){
            //可提现佣金
            $data['cap_cash'] = $this->cap_info['car_price'];
//        }else{
//
//            $data['cap_cash'] = $this->model->getCoachCashByHalfMonth($this->cap_info['id'],$this->cap_info['car_price'],2);
//            //冻结金额
//            $data['freeze_cash'] = round($this->cap_info['car_price'] - $data['cap_cash'],2);
//        }
        $wallet_model = new Wallet();
        //累计提现
        $data['extract_total_price'] = $wallet_model->capCash($this->cap_info['id'], 2, 2);
        //提现中
        $data['extract_ing_price'] = $wallet_model->capCash($this->cap_info['id'], 1, 2);

        $dis = [

            'status' => 1,

            'top_id' => $this->cap_info['id'],

            'type' => 8
        ];

        $comm_model = new Commission();
        //未到账
        $data['no_received'] = $comm_model->where($dis)->sum('cash');

        $data['no_received'] = round($data['no_received'],2);
        //税点
        $data['tax_point']   = getConfigSetting($this->_uniacid,'tax_point');

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-30 13:38
     * @功能说明:团长端佣金信息
     */
    public function capCashInfoCar()
    {

        $key = 'cap_wallet' . $this->cap_info['id'];
        //减掉
        delCache($key, $this->_uniacid);

        $wallet_model = new Wallet();
        //可提现佣金
        $data['cap_cash'] = $this->cap_info['car_price'];
        //累计提现
        $data['extract_total_price'] = $wallet_model->capCash($this->cap_info['id'], 2, 2);
        //提现中
        $data['extract_ing_price'] = $wallet_model->capCash($this->cap_info['id'], 1, 2);

        $dis = [

            'pay_type' => 7,

            'coach_id' => $this->cap_info['id'],

            'have_tx' => 0
        ];
        //未到账
        $data['no_received'] = $this->order_model->where($dis)->sum('car_price');

        $data['coach_level'] = $this->model->getCoachLevel($this->cap_info['id'], $this->_uniacid);

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-30 14:39
     * @功能说明:团长提现记录
     */
    public function capCashList()
    {

        $wallet_model = new Wallet();

        $input = $this->_param;

        $dis = [

            'coach_id' => $this->cap_info['id']
        ];

        if (!empty($input['status'])) {

            $dis['status'] = $input['status'];
        }

        $dis['type'] = $input['type'];
        //提现记录
        $data = $wallet_model->dataList($dis, 10);

        if (!empty($data['data'])) {

            foreach ($data['data'] as &$v) {

                $v['create_time'] = date('Y-m-d H:i:s', $v['create_time']);
            }
        }
        //累计提现
        $data['extract_total_price'] = $wallet_model->capCash($this->cap_info['id'], 2, $input['type']);

        $id = array_column($data['data'],'id');
        //异步执行订单消息通知
        publisher(json_encode(['pay_config'=>$this->payConfig($this->_uniacid,2),'uniacid'=>$this->_uniacid,'id'=>$id,'action'=>'wallet_check'],true));

        return $this->success($data);


    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-08 17:09
     * @功能说明:报警
     */
    public function police(){

        $input = $this->_input;

        $insert = [

            'uniacid' => $this->_uniacid,

            'coach_id' => $this->cap_info['id'],

            'user_id' => $this->cap_info['user_id'],

            'text' => '正在发出求救信号，请及时查看技师正在服务的订单地址和电话，确认报警信息',

            'lng'  => !empty($input['lng'])?$input['lng']:'',

            'lat'  => !empty($input['lat'])?$input['lat']:'',

            'address'  => !empty($input['address'])?$input['address']:'',
        ];

        $police_model = new Police();

        $res = $police_model->dataAdd($insert);

      //  publisher(json_encode(['coach_id'=>$insert['coach_id'],'uniacid'=>$this->_uniacid,'address'=>$insert['address'],'action'=>'police_notice'],true));

        $model = new Police();

        $model->sendPoliceNotice($insert['uniacid'],$insert['coach_id'],$insert['address']);

        return $this->success($res);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-11 22:34
     * @功能说明:技师修改订单信息)
     */
    public function updateOrder()
    {

        $input = $this->_input;

        $log_model = new OrderLog();

        $order = $this->order_model->dataInfo(['id' => $input['order_id']]);
        //拒单，如果开启了派单就到转单状态
        if($input['type']==-1&&$order['is_add']==0){

            $order_dispatch= getConfigSetting($this->_uniacid,'order_dispatch');

            $input['type'] = $order_dispatch==1?8:-1;
            //增加技师信用分
            $credit_model  = new CreditConfig();

            $credit_model->creditRecordAdd($order['coach_id'],7,$order['uniacid'],$order['id']);
        }

        $update = $this->order_model->coachOrdertext($input);

        $refund_model = new RefundOrder();
        //判断有无申请中的退款订单
        $refund_order = $refund_model->where(['order_id' => $order['id']])->where('status','in',[1,4,5])->count();

        if (!empty($refund_order)) {

            $this->errorMsg('该订单正在申请退款，请先联系平台处理再进行下一步');
        }

        $check = $this->order_model->checkOrderStatus($order['pay_type'],$input['type'],$order['is_add']);

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

            if($order['is_add']==1){

                $order['start_service_time'] = $this->order_model->where(['id'=>$order['add_pid']])->value('start_service_time');
            }
            //技师不能提前完成服务
            if(getConfigSetting($this->_uniacid,'coach_advance_end')==0){
                //已经服务的时长
                $have_service_time = time()-$order['start_service_time'];

                if($order['is_add']==0){

                    $have_add_time_long = $this->order_model->where(['add_pid'=>$order['id'],'pay_type'=>7])->sum('true_time_long');

                    $add_time_long = $this->order_model->where(['add_pid'=>$order['id']])->where('pay_type','in',[3,4,5,6])->sum('true_time_long');

                }else{

                    $add_time_long = 0;

                    $have_add_time_long = $this->order_model->where(['add_pid'=>$order['add_pid'],'pay_type'=>7])->sum('true_time_long');
                }
                //总时长
                $total_time = ($add_time_long+$order['true_time_long'])*60;
                //已经服务的时长
                $have_service_time = $have_service_time-$have_add_time_long*60;

                $min = ceil(($total_time-$have_service_time)/60);

                if($min>0){

                    Db::rollback();

                    decCache($key,1,$this->_uniacid);

                    $this->errorMsg('您还有未完成的服务，时间未结束，不能提前结束，如是客人原因，让客人在自己手机端点完成服务,还差'.$min.'分钟');
                }
            }
            //核销加钟订单
            $res = $this->order_model->hxAddOrder($order,$this->cap_info['id'],2,$this->cap_info['id']);

            if(!empty($res['code'])){

                decCache($key,1,$this->_uniacid);

                Db::rollback();

                $this->errorMsg($res['msg']);
            }

            $res = $this->order_model->hxOrder($order, $this->cap_info['id']);

            if(!empty($res['code'])){

                decCache($key,1,$this->_uniacid);

                Db::rollback();

                $this->errorMsg($res['msg']);
            }

        }elseif ($input['type'] == -1){
            //取消订单
            $res = $this->order_model->cancelOrder($order);

            if (!empty($res['code'])) {

                decCache($key,1,$this->_uniacid);

                Db::rollback();

                $this->errorMsg($res['msg']);
            }

            if ($order['pay_price'] > 0) {

                $refund_model = new RefundOrder();

                $order['coach_refund_time'] = $update['coach_refund_time'];

                $order['coach_refund_text'] = $update['coach_refund_text'];
                //添加到退款订单表
                $refund_id = $refund_model->coachRefundOrder($order,0);

                $res = $refund_model->refundCashV2($this->payConfig(), $order, $order['pay_price'],$refund_id);

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
        }elseif ($input['type'] == 8){

            $notice_model = new NoticeList();
            //增加后台提醒
            $notice_model->dataAdd($order['uniacid'],$order['id'],3,$order['admin_id']);

            $send_model = new SendMsgConfig();

            $setting = getConfigSettingArr($order['uniacid'],['wechat_tmpl']);

            $send_model->webOrderServiceNoticeAdmin($order, 2);

            if($setting['wechat_tmpl']==1) {

                $send_model->webOrderServiceNoticeCompany($order, 2);
            }
        }
        //到达后车费秒到账
        if($input['type']==5){

            $this->model->coachCarPriceAccount($order,$this->payConfig($this->_uniacid,$order['app_pay']));
        }

        $this->order_model->dataUpdate(['id' => $input['order_id']], $update);
        //map_type 0正常1手动
        $map_type = isset($input['map_type'])?$input['map_type']:0;

        $log_model->addLog($input['order_id'],$this->_uniacid,$input['type'],$order['pay_type'],2,$this->cap_info['id'],1,'',$map_type);

        Db::commit();

        decCache($key,1,$this->_uniacid);

        return $this->success(true);
    }

    /**
     * 时间管理回显
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getTimeConfig()
    {
        $coach_info = $this->cap_info;

        $config_model = new Config();

        $config = $config_model->dataInfo(['uniacid'=>$this->_uniacid]);

        $data = [
            "is_work" => $coach_info['is_work'],
            "start_time" => $coach_info['start_time'],
            "end_time" => $coach_info['end_time'],
            'coach_status' => $coach_info['status'],
            'day_list' => $this->getDay(),
            'time_unit' => $config['time_unit'],


        ];
        return $this->success($data);
    }

    /**
     * 设置
     * @return \think\Response
     */
    public function setTimeConfig()
    {
        $data = $this->request->only(['start_time', 'end_time', 'is_work', 'time_text','time_unit']);
        $rule = [
            'start_time'=> 'require',
            'end_time'  => 'require',
            'is_work'   => 'require',
            'time_text' => 'require',
            'time_unit' => 'require',
        ];
        $validate = \think\facade\Validate::rule($rule);
        if (!$validate->check($data)) {
            return $this->error($validate->getError());
        }

        $log_model = new WorkLog();
        //结算在线时间
        $log_model->updateTimeOnline($this->cap_info['id']);

        $data['coach_id'] = $this->cap_info['id'];
        $data['uniacid'] = $this->_uniacid;
        $res = Coach::timeEdit($data);
        if ($res === false) {
            return $this->error('设置失败');
        }
        return $this->success('');
    }

    /**
     * 根据接单时间获取维度
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getTime()
    {
        $input = $this->request->param();
        if (empty($input['start_time']) || empty($input['end_time'] || empty($input['dat_str']))) {
            return $this->error('请选择接单时间');
        }
        $coach_id = $this->cap_info['id'];
        $data = $this->getTimeData($input['start_time'], $input['end_time'], $coach_id, $input['dat_str'],1);
        return $this->success($data);
    }

    /**
     * 获取天数
     * @return mixed
     */
    protected function getDay()
    {

        $config_model = new Config();

        $config = $config_model->dataInfo(['uniacid' => $this->_uniacid]);

        $start_time = strtotime(date('Y-m-d', time()));

        $i = 0;

        while ($i < $config['max_day']) {

            $str = $start_time + $i * 86400;

            $data[$i]['dat_str'] = $str;

            $data[$i]['dat_text'] = date('m-d', $str);

            $data[$i]['week'] = changeWeek(date('w', $str));

            $i++;
        }

        return $data;


    }

    /**
     * 车费列表
     * @return \think\Response
     * @throws \think\db\exception\DbException
     */
    public function carMoneyListV2()
    {
        $input = $this->_param;

        $dis[] = ['a.uniacid', '=', $this->_uniacid];
        $dis[] = ['a.coach_id', '=', $this->cap_info['id']];

        if (!empty($input['name'])) {
            $dis[] = ['order_code', 'like', '%' . $input['name'] . '%'];
        }

        if (!empty($input['start_time'])) {
            $dis[] = ['serout_time', '>=', $input['start_time']];;
        }

        if (!empty($input['end_time'])) {
            $dis[] = ['serout_time', '<=', $input['end_time']];;
        }

        $dis[] = ['pay_type', '=', 7];
        $dis[] = ['car_type', '=', 1];

        $dis[] = ['store_id', '=', 0];

        $list = $this->order_model->carMoneyList($dis);
        return $this->success($list);
    }

    /**
     * 车费列表
     * @return \think\Response
     * @throws \think\db\exception\DbException
     */
    public function carMoneyList()
    {
        $input = $this->_param;
        //初始化以前车费没有佣金记录
        //$this->order_model->initCarprice($this->cap_info['id'],$this->_uniacid);

        $dis[] = ['a.uniacid', '=', $this->_uniacid];

        $dis[] = ['a.top_id', '=', $this->cap_info['id']];

        $dis[] = ['a.type', '=', 8];

        $dis[] = ['a.status', '=', 2];

        if (!empty($input['name'])) {

            $dis[] = ['b.order_code', 'like', '%' . $input['name'] . '%'];
        }

        if (!empty($input['start_time'])) {

            $dis[] = ['b.serout_time', '>=', $input['start_time']];;
        }

        if (!empty($input['end_time'])) {

            $dis[] = ['b.serout_time', '<=', $input['end_time']];;
        }

        $list = $this->order_model->carMoneyListV2($dis);

        return $this->success($list);
    }

    /**
     * 订单数量
     * @return \think\Response
     */
    public function getOrderNum()
    {
        $data = [
            'wait'     => $this->order_model->getOrderNum([['uniacid', '=', $this->_uniacid], ['coach_id', '=', $this->cap_info['id']], ['pay_type', '=', 2],['is_add', '=', 0]]),
            'start'    => $this->order_model->getOrderNum([['uniacid', '=', $this->_uniacid], ['coach_id', '=', $this->cap_info['id']], ['pay_type', 'in', [3, 4, 5]],['is_add', '=', 0]]),
            'progress' => $this->order_model->getOrderNum([['uniacid', '=', $this->_uniacid], ['coach_id', '=', $this->cap_info['id']], ['pay_type', '=', 6],['is_add', '=', 0]]),
        ];
        return $this->success($data);
    }

    /**
     * 分类列表
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function carteList()
    {
        $list = ShopCarte::getListNoPage(['status' => 1, 'uniacid' => $this->_uniacid]);
        return $this->success($list);
    }

    /**
     * 商品列表
     * @return \think\Response
     * @throws \think\db\exception\DbException
     */
    public function goodsList()
    {
        $input = $this->request->param();
        $where = [];
        if (!empty($input['name'])) {
            $where[] = ['name', 'like', '%' . $input['name'] . '%'];
        }
        if (!empty($input['carte'])) {
            $where[] = ['', 'exp', Db::raw("find_in_set({$input['carte']},carte)")];
        }
        $where[] = ['status', '=', 1];
        $data = ShopGoods::getList($where);
        return $this->success($data);
    }

    /**
     * 商品详情
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function goodsInfo()
    {
        $id = $this->request->param('id', '');
        if (empty($id)) {
            return $this->error('商品不存在');
        }
        $data = ShopGoods::getInfo(['id' => $id]);
        $data['images'] = json_decode($data['images'], true);
        return $this->success($data);
    }

    /**
     * 添加反馈
     * @return \think\Response
     */
    public function addFeedback()
    {


        $input = $this->request->only(['type_name', 'order_code', 'content', 'images', 'video_url']);
        $rule = [
            'type_name' => 'require',
            'content'   => 'require',
        ];
        $validate = \think\facade\Validate::rule($rule);
        if (!$validate->check($input)) {
            return $this->error($validate->getError());
        }
        $input['coach_id'] = $this->cap_info['id'];
        $input['uniacid'] = $this->_uniacid;
        if (!empty($input['images'])) {
            $input['images'] = json_encode($input['images']);
        }
        $input['create_time'] = time();
        $res = Feedback::insert($input);
        if ($res) {
            return $this->success('');
        }
        return $this->error('提交失败');
    }

    /**
     * 反馈列表
     * @return \think\Response
     */
    public function listFeedback()
    {
        $input = $this->request->param();
        $where = [];
        if (isset($input['status']) && in_array($input['status'], [1, 2])) {
            $where[] = ['a.status', '=', $input['status']];
        }
        $where[] = ['a.coach_id', '=', $this->cap_info['id']];
        $where[] = ['a.uniacid', '=', $this->_uniacid];
        $data = Feedback::getList($where);
        $data['wait'] = Feedback::where(['coach_id' => $this->cap_info['id'], 'uniacid' => $this->_uniacid, 'status' => 1])->count();
        return $this->success($data);
    }

    /**
     * 详情
     * @return \think\Response
     */
    public function feedbackInfo()
    {
        $id = $this->request->param('id');
        if (empty($id)) {
            return $this->error('参数错误');
        }
        $data = Feedback::getInfo(['a.id' => $id]);
        return $this->success($data);
    }

    /**
     * 订单列表
     * @return \think\Response
     */
    public function appealOrder()
    {
        $name = $this->request->param('name', '');
        $dis = [];

        if (!empty($name)) {

            $dis[] = ['b.goods_name', 'like', '%' . $name . '%'];

            $dis[] = ['a.order_code', 'like', '%' . $name . '%'];

        }
        $where = [
            'a.coach_id' => $this->cap_info['id'],
            'a.pay_type' => 7,
            'a.is_comment' => 1,
            'a.uniacid' => $this->_uniacid
        ];
        $order = (new Order())->indexDataList($where, $dis);
        return $this->success($order);
    }

    /**
     * 提交申诉
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function addAppeal()
    {
        $input = $this->request->only(['order_code', 'content']);
        if (empty($input['order_code']) || empty($input['content'])) {
            return $this->error('请检查参数');
        }
        $order = Order::where(['order_code' => $input['order_code'], 'coach_id' => $this->cap_info['id'], 'pay_type' => 7, 'is_comment' => 1])->find();
        if (empty($order)) {
            return $this->error('订单不存在');
        }
        $input['coach_id'] = $this->cap_info['id'];
        $input['create_time'] = time();
        $input['order_id'] = $order['id'];
        $input['uniacid'] = $this->_uniacid;
        $res = Appeal::insert($input);
        if ($res) {
            return $this->success('');
        }
        return $this->error('申诉失败');
    }

    /**
     * 申诉列表
     * @return \think\Response
     */
    public function appealList()
    {
        $input = $this->request->param();
        $limit = $this->request->param('limit',10);
        $where = [];
        if (isset($input['status']) && in_array($input['status'], [1, 2])) {
            $where[] = ['a.status', '=', $input['status']];
        }
        $where[] = ['a.coach_id', '=', $this->cap_info['id']];
        $where[] = ['a.uniacid', '=', $this->_uniacid];
        $data = Appeal::getList($where,$limit);
        $data['wait'] = Appeal::where(['coach_id' => $this->cap_info['id'], 'uniacid' => $this->_uniacid, 'status' => 1])->count();
        return $this->success($data);
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
     * @DataTime: 2023-10-17 11:22
     * @功能说明:技师评价用户内容
     */
    public function coachCommentUserData(){

        $input = $this->_param;

        $comment_model = new UserComment();

        $coach_model  = new Coach();
        //评价内容
        $list = $comment_model->dataList(['user_id'=>$input['user_id'],'status'=>1],10);

        if(!empty($list['data'])){

            foreach ($list['data'] as &$v){

                $v['coach_name'] = $coach_model->where(['id'=>$v['coach_id']])->value('coach_name');

                $v['work_img']   = $coach_model->where(['id'=>$v['coach_id']])->value('work_img');

                $v['create_time'] = date('Y-m-d H:i:s',$v['create_time']);

            }
        }

        return $this->success($list);
    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-10-24 15:25
     * @功能说明:获取标签列表
     */
    public function labelList(){

        $input = $this->_param;

        $label_model = new UserLabelList();

        $dis = [

            'uniacid' => $this->_uniacid,

            'status'  => 1
        ];

        $data = $label_model->where($dis)->order('id desc')->select()->toArray();

        return $this->success($data);

    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-10-24 15:25
     * @功能说明:添加用户标签
     */
    public function userLabelAdd(){

        $input = $this->_input;

        $label_model = new UserLabelData();

        $list_model  = new UserLabelList();

        $comment_model= new UserComment();

        $text = !empty($input['text'])?$input['text']:'';

        $insert = [

            'uniacid' => $this->_uniacid,

            'user_id' => $input['user_id'],

            'coach_id'=> $this->cap_info['id'],

            'text'    => $text,

            'order_id'=> $input['order_id']
        ];

        $comment_model->dataAdd($insert);

        $id = $comment_model->getLastInsID();

        $order_mdoel = new Order();

        $order_mdoel->dataUpdate(['id'=>$input['order_id']],['label_time'=>time()]);

        foreach ($input['label'] as $k=>$value){

            $title = $list_model->where(['id'=>$value])->value('title');

            $arr[$k] = [

                'uniacid' => $this->_uniacid,

                'label_id'=> $value,

                'user_id' => $input['user_id'],

                'title'   => $title,

                'create_time'=> time(),

                'comment_id' => $id,
            ];
        }

        $data = $label_model->saveAll($arr);

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-25 17:40
     * @功能说明：技师重置分享码
     *
     */

    public function coachBalanceQr(){

        $input = $this->_param;

        $key = 'balance_coach'.$this->cap_info['id'].'-'.$this->is_app;

        $qr  = getCache($key,$this->_uniacid);

        if(empty($qr)){
            //小程序
            if($this->is_app==0){

                $input['page'] = 'user/pages/stored/list';

                $input['coach_id'] = $this->cap_info['id'];

                $user_model = new User();
                //获取二维码
                $qr = $user_model->orderQr($input,$this->_uniacid);

            }else{

                $page = 'https://'.$_SERVER['HTTP_HOST'].'/h5/#/user/pages/stored/list?coach_id='.$this->cap_info['id'];

                $qr = base64ToPng(getCode($this->_uniacid,$page));
            }

            setCache($key,$qr,86400,$this->_uniacid);
        }

        $qr = !empty($qr)?$qr:'https://'.$_SERVER['HTTP_HOST'].'/favicon.ico';

        return $this->success($qr);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-11-08 18:26
     * @功能说明:技师等级
     */
    public function coachLevel(){
        //本期业绩
        $current_achievement = $this->model->getCurrentAchievement($this->cap_info['id'],$this->_uniacid);

        $coach_level = $this->model->getCoachLevel($this->cap_info['id'],$this->_uniacid);
        //在线时长转换积分
        if(!empty($coach_level)&&$coach_level['online_change_integral_status']==1){

            $more_online_time = floor($current_achievement['online_time'] - $coach_level['online_time']);

            if($more_online_time>0){

                $change_integral = $more_online_time*$coach_level['online_change_integral'];

                $current_achievement['coach_integral']+= $change_integral;
            }
        }

        $coach_level = array_merge($coach_level,$current_achievement);

        $level_model  = new CoachLevel();

        $dis = [

            'uniacid' => $this->_uniacid,

            'status'  => 1
        ];

        $cap_info['level_list'] = $level_model->where($dis)->order('top,id desc')->select()->toArray();

        $custom_model= new CustomBalance();

        $custom_level = $custom_model->getCoachCustomBalance($this->cap_info['id']);

        if(!empty($cap_info['level_list'])){

            foreach ($cap_info['level_list'] as $key=> &$value){

                $value['lower'] = $level_model->where($dis)->where('time_long','<',$value['time_long'])->max('time_long');

                $level = $this->model->coachLevelInfo($value);

                $value['data'] = $level;
                //自定义佣金
                if(!empty($custom_level)&&$value['id']==$coach_level['id']){

                    $value['balance'] = $custom_level['balance'];
                }
            }
        }

        $cap_info['coach_level'] = $coach_level;

        $config_model = new ConfigSetting();

        $config = $config_model->dataInfo($this->_uniacid);

        $cap_info['coach_level_show'] = $config['coach_level_show'];

        return $this->success($cap_info);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-12-09 15:18
     * @功能说明:技师获取客户虚拟电话
     */
    public function getVirtualPhone(){

        $input = $this->_input;

        $order = $this->order_model->dataInfo(['id'=>$input['order_id']]);

        if(in_array($order['pay_type'],[-1,7])){

            return $this->error('接单已结束');
        }

        $called = new \app\virtual\model\Config();

        $res = $called->getVirtual($order);

        return $this->success($res);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-23 15:15
     * @功能说明:储值佣金总金额
     */
    public function balanceCommissionData(){

        $input = $this->_param;

        $integral_model = new Integral();

        $dis[] = ['status','=',1];

        $dis[] = ['coach_id','=',$this->cap_info['id']];

        $data['total_cash'] = $integral_model->where($dis)->where('type','in',[1,3])->sum('integral');

        $data['total_integral'] = $integral_model->where($dis)->where('type','in',[0,2])->sum('integral');

        $data['total_cash'] = round($data['total_cash'],2);

        $data['total_integral'] = round($data['total_integral'],2);

        $comm_model = new Commission();
        //手续费
        $data['total_point'] = $comm_model->alias('a')
            ->join('massage_service_order_commission_share b','a.id = b.comm_id')
            ->where(['a.uniacid'=>$this->_uniacid,'a.status'=>2,'a.top_id'=>$this->cap_info['id']])
            ->where('a.type','in',[7,25])
            ->group('a.id')
            ->sum('b.share_cash');

        $data['total_point'] = round($data['total_point'],2);

        if(!empty($input['start_time'])){

            $dis[] = ['create_time','between',"{$input['start_time']},{$input['end_time']}"];

            $data['cash'] = $integral_model->where($dis)->where('type','in',[1,3])->sum('integral');

            $data['integral'] = $integral_model->where($dis)->where('type','in',[0,2])->sum('integral');

            $data['cash'] = round($data['cash'],2);

            $data['integral'] = round($data['integral'],2);

            $data['point'] = $comm_model->alias('a')
                ->join('massage_service_order_commission_share b','a.id = b.comm_id')
                ->where(['a.uniacid'=>$this->_uniacid,'a.status'=>2,'a.top_id'=>$this->cap_info['id']])
                ->where('a.create_time','between',"{$input['start_time']},{$input['end_time']}")
                ->where('a.type','in',[7,25])
                ->group('a.id')
                ->sum('b.share_cash');

            $data['point'] = round($data['point'],2);
        }

        return $this->success($data);
    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-23 15:06
     * @功能说明：储值佣金列表
     */
    public function balanceCommissionList(){

        $input = $this->_param;

        $integral_model = new Integral();

        $dis[] = ['status','=',1];

        $dis[] = ['type','<>',5];

        $dis[] = ['coach_id','=',$this->cap_info['id']];

        if(!empty($input['start_time'])){

            $dis[] = ['create_time','between',"{$input['start_time']},{$input['end_time']}"];
        }

        $month = !empty($input['month'])?$input['month']:'';

        $data = $integral_model->coachDataList($dis,10,$month);

        if(!empty($data['data'])){

            $user_model = new User();

            $comm_model = new Commission();

            foreach ($data['data'] as &$v){

                $v['nickName'] = $user_model->where(['id'=>$v['user_id']])->value('nickName');

                $year  = date('Y',$v['create_time']);

                $month = date('m',$v['create_time']);

                $v['month_text'] = $year.'年'.$month.'月';

                $v['month'] = date('Y-m',$v['create_time']);

                $v['create_time'] = date('Y.m.d H:i:s',$v['create_time']);

                $v['total_cash']     = $integral_model->where($dis)->where('type','in',[1,3])->whereMonth('create_time',$v['month'])->sum('integral');

                $v['total_integral'] = $integral_model->where($dis)->where('type','in',[0,2])->whereMonth('create_time',$v['month'])->sum('integral');

                $v['total_cash'] = round($v['total_cash'],2);

                $v['total_integral'] = round($v['total_integral'],2);
                //手续费
                $v['point_cash'] = $comm_model->alias('a')
                                  ->join('massage_service_order_commission_share b','a.id = b.comm_id')
                                  ->where(['a.uniacid'=>$this->_uniacid,'a.status'=>2,'a.top_id'=>$this->cap_info['id']])
                                  ->where('a.type','in',[7,25])
                                  ->whereMonth('a.create_time',$v['month'])
                                  ->group('a.id')
                                  ->sum('b.share_cash');

            }
        }

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-27 14:24
     * @功能说明:技师佣金列表(新版可以直接查佣金表 不用去查订单表 这样只是为了兼容)
     */
    public function coachCommissionList(){

        $input = $this->_param;

        $order_model = new Order();

        $dis[] = ['a.status','=',2];

        $dis[] = ['a.top_id','=',$this->cap_info['id']];

        if(!empty($input['start_time'])){

            $dis[] = ['a.create_time','between',"{$input['start_time']},{$input['end_time']}"];
        }

        $where = [

            ['a.type','in',[17,18]],
        ];

        $where1 =[

            ['a.type','=', 3],

            ['b.is_add','=',0]
        ];

        $month = !empty($input['month'])?$input['month']:'';

        $comm_model = new Commission();

        if(!empty($month)){

            $firstday = date('Y-m-01', $month);

            $lastday  = date('Y-m-d H:i:s', strtotime("$firstday +1 month")-1);

            $data = $comm_model->alias('a')
                ->join('massage_service_order_list b','a.order_id = b.id','left')
                ->where($dis)
                ->where(function ($query) use ($where,$where1){
                    $query->whereOr([$where,$where1]);
                })
                ->whereTime('a.create_time','<=',$lastday)
                ->field('a.order_id as id,a.order_code,round(a.cash,2) as coach_cash,a.create_time,a.type,a.refund_id')
                ->group('a.id')
                ->order('a.create_time desc,a.id desc')
                ->paginate(10)
                ->toArray();
        }else{

            $data = $comm_model->alias('a')
                    ->join('massage_service_order_list b','a.order_id = b.id','left')
                    ->where($dis)
                    ->where(function ($query) use ($where,$where1){
                        $query->whereOr([$where,$where1]);
                    })
                    ->field('a.order_id as id,a.order_code,round(a.cash,2) as coach_cash,a.create_time,a.type,a.refund_id')
                    ->group('a.id')
                    ->order('a.create_time desc,a.id desc')
                    ->paginate(10)
                    ->toArray();
        }

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                if($v['type']==3){

                    $son_cash = $order_model->where(['pay_type'=>7,'add_pid'=>$v['id']])->sum('coach_cash');

                    $order_car_cash = $comm_model->where(['type'=>8,'order_id'=>$v['id'],'status'=>2])->sum('cash');

                    $v['coach_cash'] = round($son_cash + $v['coach_cash']+$order_car_cash,2);
                }else{

                    $v['id'] = $v['refund_id'];
                }

                $year  = date('Y',$v['create_time']);

                $month = date('m',$v['create_time']);

                $v['month_text'] = $year.'年'.$month.'月';

                $v['month'] = date('Y-m',$v['create_time']);

                $v['create_time'] = date('Y.m.d H:i:s',$v['create_time']);

                $v['total_cash']  = $order_model->where(['coach_id'=>$this->cap_info['id'],'pay_type'=>7,'is_add'=>0])->whereMonth('create_time',$v['month'])->sum('coach_cash');

                $v['total_count'] = $order_model->where(['coach_id'=>$this->cap_info['id'],'pay_type'=>7,'is_add'=>0])->whereMonth('create_time',$v['month'])->count();

                $empty_count = $comm_model->where(['status'=>2,'top_id'=>$this->cap_info['id']])->where('type','in',[17,18])->whereMonth('create_time',$v['month'])->count();

                $empty_cash  = $comm_model->where(['status'=>2,'top_id'=>$this->cap_info['id']])->where('type','in',[17,18])->whereMonth('create_time',$v['month'])->sum('cash');

                $v['total_count']+= $empty_count;

                $v['total_cash'] += $empty_cash;

                $order_id = $order_model->where(['coach_id'=>$this->cap_info['id'],'pay_type'=>7,'is_add'=>0])->whereMonth('create_time',$v['month'])->column('id');
                //加钟
                $add_order_cash = $order_model->where(['pay_type'=>7])->where('add_pid','in',$order_id)->sum('coach_cash');
                //车费
                $car_price = $comm_model->where(['status'=>2,'type'=>8])->where('order_id','in',$order_id)->sum('cash');

                $v['total_cash']= round($v['total_cash']+$add_order_cash+$car_price,2);
            }
        }

        return $this->success($data);
    }


    /**
     * @author chenniang
     * @DataTime: 2024-07-30 13:50
     * @功能说明:技师空单费|退款手续费详情
     */
    public function commRefundInfo(){

        $input = $this->_param;

        $refund_model = new RefundOrder();

        $comm_model   = new Commission();

        $order_model  = new Order();

        $data  = $refund_model->dataInfo(['id'=>$input['id']]);

        $start_time= $order_model->where(['id'=>$data['order_id']])->value('start_time');

        $end_time= $order_model->where(['id'=>$data['order_id']])->value('end_time');

        $data['time_text'] = date('Y-m-d H:i',$start_time).'~'.date('H:i',$end_time);
        //佣金信息
        $data['comm_info'] = $comm_model->where(['refund_id'=>$input['id'],'status'=>2])->where('type','in',[17,18])->field('round(cash,2) as cash,balance,type')->find();

        $data['refund_empty_cash'] = round($data['refund_empty_cash'],2);

        $data['refund_comm_cash']  = round($data['refund_comm_cash'],2);

        return $this->success($data);
    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-23 15:15
     * @功能说明:技师佣金总金额
     */
    public function coachCommissionData(){

        $input = $this->_param;

        $order_model = new Order();

        $comm_model  = new Commission();
        //初始化以前车费没有佣金记录
        $this->order_model->initCarprice($this->cap_info['id'],$this->_uniacid);

        $dis[] = ['pay_type','=',7];

        $dis[] = ['coach_id','=',$this->cap_info['id']];
        //服务费
        $coach_cash = $order_model->where($dis)->sum('coach_cash');

        $empty_cash = $comm_model->where(['top_id'=>$this->cap_info['id'],'status'=>2])->where('type','in',[17,18])->sum('cash');
        //车费
        $car_price  = $comm_model->where(['top_id'=>$this->cap_info['id'],'status'=>2,'type'=>8])->sum('cash');

        $share_model = new CommShare();
        //手续费
        $data['point_cash'] = $share_model->getCoachPointCash($this->cap_info['id']);

        $data['total_service_cash'] = round($coach_cash+$empty_cash,2);

        $data['total_car_cash']     = round($car_price,2);

        $arr = [
            9  => 'partner_share_cash',
            10 => 'channel_share_cash',
            12 => 'salesman_share_cash',
            2  => 'coach_poster_cash',
            3  => 'coach_balance_cash',
            4  => 'skill_cash',
            1  => 'reseller_cash',
            14 => 'level_reseller_cash',
            6  => 'coupon_share_cash',
        ];
        foreach ($arr as $k=>$v){

            if(in_array($k,[2,3,6,4])){
                //广告费 储值扣款
                $data[$v] = $share_model->coachPosterCash($this->cap_info['id'],$k);
            }else{
                //分摊的金额
                $data[$v] = $share_model->coachShareCash($this->cap_info['id'],$k);
            }
        }

        if(!empty($input['start_time'])){

            $where[] = ['create_time','between',"{$input['start_time']},{$input['end_time']}"];

            $where[] = ['status','=',2];

            $where[] = ['top_id','=',$this->cap_info['id']];

            $data['count'] = $comm_model->where($where)->where(['type'=>3])->count();

            $data['service_cash'] = $comm_model->where($where)->where('type','in',[3,8])->sum('cash');

            $data['service_cash'] = round($data['service_cash'],2);
        }

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-28 11:03
     * @功能说明:技师佣金详情
     */
    public function coachCommissionInfo(){

        $input = $this->_param;

        $order_model = new Order();

        $share_model= new CommShare();

        $refund_model = new RefundOrder();

        $order = $order_model->where(['id'=>$input['id']])->field('material_type,coupon_bear_type,pay_model,balance,coach_id,material_type,id,order_code,true_service_price,true_car_price,coach_cash,material_price,coach_balance,init_material_price,service_price,init_service_price,discount,start_time,end_time')->find()->toArray();

        if($order['material_type']==0){
            //使用的原价
            $order['refund_price'] = $refund_model->where(['status'=>2,'order_id'=>$order['id']])->sum('refund_service_price');
            //空单费
            $order['refund_empty_cash']= $refund_model->where(['status'=>2,'order_id'=>$order['id']])->sum('empty_service_cash');
            //退款手续费
            $order['refund_comm_cash'] = $refund_model->where(['status'=>2,'order_id'=>$order['id']])->sum('comm_service_cash');

        }else{

            //使用的原价
            $order['refund_price'] = $refund_model->where(['status'=>2,'order_id'=>$order['id']])->sum('refund_price');
            //空单费
            $order['refund_empty_cash']= $refund_model->where(['status'=>2,'order_id'=>$order['id']])->sum('refund_empty_cash');
            //退款手续费
            $order['refund_comm_cash'] = $refund_model->where(['status'=>2,'order_id'=>$order['id']])->sum('refund_comm_cash');
        }

        $order['refund_price'] = round($order['refund_price'],2);

        $order['time_text'] = date('Y-m-d H:i',$order['start_time']).'~'.date('H:i',$order['end_time']);
        //加钟订单
        $order['add_order'] = $order_model->where(['pay_type'=>7,'add_pid'=>$input['id']])->field('material_type,coupon_bear_type,pay_model,balance,id,order_code,true_service_price,material_price,init_material_price,init_service_price,true_car_price,coach_cash,coach_balance,service_price,discount,start_time,end_time')->select()->toArray();

        if(!empty($order['add_order'])){

            foreach ($order['add_order'] as &$value){
                //加钟各类分摊金额 含广告费 储值扣款
                $value['total_share_cash'] = $share_model->where(['order_id'=>$value['id'],'type'=>1])->sum('share_cash');
                //手续费
                $add_point_cash = $share_model->where(['order_id'=>$value['id'],'comm_type'=>3,'cash_type'=>1])->sum('share_cash');

                $value['total_share_cash'] = round($value['total_share_cash']+$add_point_cash,2);

                $value['time_text'] = date('Y-m-d H:i',$value['start_time']).'~'.date('H:i',$value['end_time']);

                if($order['material_type']==0){

                    $value['refund_price'] = $refund_model->where(['status'=>2,'order_id'=>$value['id']])->sum('refund_service_price');

                    $value['refund_empty_cash'] = $refund_model->where(['status'=>2,'order_id'=>$value['id']])->sum('empty_service_cash');

                    $value['refund_comm_cash'] = $refund_model->where(['status'=>2,'order_id'=>$value['id']])->sum('comm_service_cash');

                }else{

                    $value['refund_price'] = $refund_model->where(['status'=>2,'order_id'=>$value['id']])->sum('refund_price');

                    $value['refund_empty_cash'] = $refund_model->where(['status'=>2,'order_id'=>$value['id']])->sum('refund_empty_cash');

                    $value['refund_comm_cash'] = $refund_model->where(['status'=>2,'order_id'=>$value['id']])->sum('refund_comm_cash');
                }
            }
        }

        $comm_model = new Commission();
        //获取分摊金额
        $arr = [
            //合伙人分摊
            9 => 'partner_share_cash',
            //渠道商
            10=> 'channel_share_cash',
            //业务员
            12=> 'salesman_share_cash',
            //分销员
            1 => 'reseller_share_cash',
            //二级分销员
            14=> 'level_reseller_share_cash',
        ];
        //总分摊
        $order['total_share_cash'] = 0;

        foreach ($arr as $k=>$v){

            $order[$v] = $share_model->coachShareCash($order['coach_id'],$k,$input['id']);

            $order['total_share_cash'] += $order[$v];
        }
        //手续费
        $order['point_cash'] = $share_model->where(['order_id'=>$input['id'],'cash_type'=>1,'comm_type'=>3])->sum('share_cash');
        //广告费
        $order['poster_cash'] = $share_model->where(['order_id'=>$input['id'],'cash_type'=>2,'type'=>1])->sum('share_cash');
        //优惠券
        $order['coupon_share_cash'] = $share_model->where(['order_id'=>$input['id'],'cash_type'=>6,'type'=>1])->sum('share_cash');
        //储值折扣分摊
        $order['balance_discount_share_cash'] = $share_model->where(['order_id'=>$input['id'],'cash_type'=>7,'type'=>1])->sum('share_cash');
        //储值扣款
        $order['coach_balance_cash'] = $share_model->where(['order_id'=>$input['id'],'cash_type'=>3,'type'=>1])->sum('share_cash');
        //技术服务费
        $order['skill_cash'] = $share_model->where(['order_id'=>$input['id'],'cash_type'=>4,'type'=>1])->sum('share_cash');

        $order['total_share_cash'] = round($order['total_share_cash']+$order['point_cash']+ $order['poster_cash']+$order['coach_balance_cash']+$order['skill_cash']+$order['coupon_share_cash']+$order['balance_discount_share_cash'],2);
        //原佣金
        $order['coach_cash'] += $order['total_share_cash'];

        $order['coach_cash'] = round($order['coach_cash'],2);

        $order['total_share_cash'] = round($order['total_share_cash'],2);

        $order['true_car_price'] = $comm_model->where(['order_id'=>$input['id'],'type'=>8])->sum('cash');

        return $this->success($order);
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

            'coach_id' => $this->cap_info['id']

        ];

        $data = $this->order_model->dataInfo($dis);

        if(empty($data)){

            $this->errorMsg('订单已被删除');
        }
        //下单次数
        $data['pay_order_times'] = $this->order_model->useOrderTimes($data['user_id'],$data['create_time']);
        //是否能加钟
        $data['can_add_order'] = $this->order_model->orderCanAdd($data);

        $arr = ['create_time','pay_time','serout_time','arrive_time','receiving_time','start_service_time','order_end_time'];

        foreach ($arr as $value){

            $data[$value] = !empty($data[$value])?date('Y-m-d H:i:s',$data[$value]):0;
        }

        $data['start_time'] = date('Y-m-d H:i',$data['start_time']).'-'.date('H:i',$data['end_time']);
        //剩余可申请退款数量
        $can_refund_num = array_sum(array_column($data['order_goods'],'can_refund_num'));
        //是否可以申请退款
        if((in_array($data['pay_type'],[2,3,4,5])&&$can_refund_num>0)){

            $data['can_refund'] = 1;

        }else{

            $data['can_refund'] = 0;
        }

        $add_time_long = $this->order_model->where(['add_pid'=>$data['id']])->where('pay_type','in',[4,5,6,7])->sum('true_time_long');

        $data['total_time_long'] = $data['true_time_long']+$add_time_long;

        $data['distance']   = distance_text($data['distance']);

        $data['over_time'] -= time();

        $data['over_time']  = $data['over_time']>0?$data['over_time']:0;
        //加钟订单
        if($data['is_add']==0){

            $data['add_order_id'] = $this->order_model->getAddOrderList($data['id'],1);

        }else{

            $data['add_pid'] = $this->order_model->where(['id'=>$data['add_pid']])->field('id,order_code')->find();
        }

        $order_model = new OrderData();
        //订单附表
        $order_data = $order_model->dataInfo(['order_id'=>$input['id'],'uniacid'=>$this->_uniacid]);

        $data = array_merge($order_data,$data);

        $data['sign_time'] = !empty($data['sign_time'])?date('Y-m-d H:i:s',$data['sign_time']):'';

        $shield_model = new ShieldList();

        $shield = $shield_model->dataInfo(['user_id'=>$data['user_id'],'type'=>2,'coach_id'=>$data['coach_id']]);

        $data['can_again'] = !empty($shield)?0:1;
        //查询是否有转派记录
        $change_log_model = new CoachChangeLog();

        $change_log = $change_log_model->dataInfo(['order_id'=>$data['id'],'status'=>1]);

        if(!empty($change_log)){

            $data['old_coach_name'] = $this->model->where(['id'=>$change_log['init_coach_id']])->value('coach_name');
        }

        $admin_model = new \app\massage\model\Admin();
        //代理商电话
        $data['admin_phone'] = $admin_model->where(['id'=>$data['admin_id']])->value('phone');
        //门店订单
        if(!empty($data['store_id'])){

            $store_model = new StoreList();

            $data['store_info'] = $store_model->where(['id'=>$data['store_id']])->field('title,cover,address,lng,lat,phone')->find();
        }
        //加钟合集金额
        $data['total_add_price'] = $this->order_model->where(['add_pid'=>$data['id']])->where('pay_type','>',1)->sum('pay_price');

        $data['total_add_price'] = round($data['total_add_price'],2);
        //总金额
        $data['total_price']     = round($data['total_add_price']+$data['pay_price'],2);

        $abn_model = new OrderList();
        //异常订单标示
        $data['abn_order_id'] = $abn_model->where(['order_id'=>$data['id']])->value('id');
        //技师是否可以看到用户下单次数
        $config = getConfigSettingArr($this->_uniacid,['show_user_order_num','coach_update_address_auth']);

        $data = array_merge($data,$config);

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
     * @DataTime: 2023-01-30 16:03
     * @功能说明:屏蔽用户
     */
    public function shieldUserAdd(){

        $input = $this->_input;

        $dis = [

            'coach_id' => $this->cap_info['id'],

            'user_id'  => $input['user_id'],

            'type'     => 3,

            'uniacid'  => $this->_uniacid
        ];

        $shield_model = new ShieldList();
        //没屏蔽过再屏蔽
        $find = $shield_model->dataInfo($dis);

        if(empty($find)){

            $shield_model->dataAdd($dis);
        }

        return $this->success(true);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-01-30 16:03
     * @功能说明:解除用户屏蔽
     */
    public function shieldUserDel(){

        $input = $this->_input;

        $dis = [

            'id' => $input['id'],
        ];

        $shield_model = new ShieldList();

        $res = $shield_model->where($dis)->delete();

        return $this->success($res);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-01-30 16:03
     * @功能说明:用户屏蔽列表
     */
    public function shieldCoachList(){

        $input = $this->_param;

        $dis = [

            'a.coach_id' => $this->cap_info['id'],

            'a.type'    => 3
        ];

        $shield_model = new ShieldList();

        $res = $shield_model->dataUserList($dis);

        if(!empty($res['data'])){

            foreach ($res['data'] as &$v){

                $v['create_time'] = date('Y-m-d H:i:s',$v['create_time']);

            }
        }

        return $this->success($res);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-16 15:46
     * @功能说明:判断是否开启了发大大
     */
    public function getFddStatus(){

        $config_model = new FddConfig();

        $status = $config_model->getStatus($this->_uniacid);

        return $this->success($status);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-12 15:14
     * @功能说明:判断有无法大大认证
     */
    public function getFddRecord(){

        $record_model= new FddAgreementRecord();

        $config_model = new FddConfig();

        $status = $config_model->getStatus($this->_uniacid);
        //如果没有开启发大大不需要走发大大的流程
        if($status==0){

            return $this->success(1);

        }

        $dis[] = ['user_id','=',$this->_user['id']];

        $dis[] = ['status','=',4];

        $dis[] = ['admin_id','=',$this->cap_info['admin_id']];

        $dis[] = ['end_time','>',time()];

        $data = $record_model->dataInfo($dis);

        $res = !empty($data)?1:0;

        return $this->success($res);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-12 17:23
     * @功能说明:获取发大大注册信息
     */
    public function getAttestationInfo(){

        $attestation_model = new FddAttestationRecord();

        if(empty($this->_user['id'])){

            return $this->error('请绑定微信用户');
        }
        //status 1注册 2实名认证 3绑定
        $data = $attestation_model->getAttestationInfo($this->_user['id'],$this->_uniacid);

        if(!empty($data['code'])){

            return $this->error($data['msg']);
        }

        return $this->success($data);
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-12 17:25
     * @功能说明:获取发大大实名认证地址
     */
    public function getPersonVerifyUrl(){

        $attestation_model = new FddAttestationRecord();

        if(empty($this->_user['id'])){

            return $this->error('请绑定微信用户');
        }

        $data = $attestation_model->getPersonVerifyUrl($this->_user['id'],$this->_uniacid);

        if(!empty($data['code'])){

            return $this->error($data['msg']);
        }

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-12 17:27
     * @功能说明:手动签署发大大合同
     */
    public function Extsign(){

        $input = $this->_input;

        $attestation_model = new FddAttestationRecord();

        $agreement_model   = new FddAgreementRecord();

        if(empty($this->_user['id'])){

            return $this->error('请绑定微信用户');
        }
        //绑定实名
        $info = $attestation_model->ApplyCert($this->_user['id'],$this->_uniacid);

        if(!empty($info['code'])){

            return $this->error($info['msg']);
        }

        $dis = [

            'coach_id' => $this->cap_info['id'],

            'admin_id' => $this->cap_info['admin_id'],

            'status'   => 2
        ];

        $agreement = $agreement_model->dataInfo($dis);

        if(empty($agreement)){

            return $this->error('请商家先发起合同');
        }

        $core = new Fdd($this->_uniacid);

        $transaction_id = orderCode();

        $res = $core->Extsign($transaction_id,$agreement['contract_id'],$info['customer_id'],$agreement['agreement_title']);

        if(isset($res['code'])&&$res['code']!=1){

            $msg = !empty($res['msg'])?$res['msg']:'上传合同失败';

            return $this->error($msg);
        }

        $agreement_model->dataUpdate(['id'=>$agreement['id']],['transaction_id'=>$transaction_id,'customer_id'=>$info['customer_id']]);

        return $this->success($res);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-24 15:14
     * @功能说明:添加技师轨迹
     */
    public function coachTrajectoryAdd(){

        $input = $this->_input;

        $trajectory_model = new Trajectory();

        $dis = [

            'coach_id' => $this->cap_info['id'],

            'order_id' => $input['order_id']
        ];

        $find = $trajectory_model->dataInfo($dis);

        if(!empty($find)){

            $find['text'] = unserialize($find['text']);

            $find['text'][] = $input['text'];

            $update = [

                'text' => serialize($find['text'])
            ];

            $res = $trajectory_model->dataUpdate($dis,$update);

        }else{

            $insert = [

                'uniacid' => $this->_uniacid,

                'order_id'=> $input['order_id'],

                'coach_id'=> $this->cap_info['id'],

                'text'    => serialize([$input['text']]),
            ];

            $res = $trajectory_model->dataAdd($insert);
        }

        return $this->success($res);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-09-27 16:28
     * @功能说明:获取技师
     */
    public function getCreditValueData(){

        $record_model = new CreditRecord();

        $config_model = new CreditConfig();

        $dis = [

            'coach_id' => $this->cap_info['id']
        ];

        $arr = [
            1 => 'order_empty',
            2 => 'add_order_empty',
            3 => 'time_long_empty',
            4 => 'repeat_order_empty',
            5 => 'good_evaluate_empty',
            6 => 'refund_order_empty',
            7 => 'refuse_order_empty',
            8 => 'bad_evaluate_empty',
        ];

        $credit_config = $config_model->dataInfo(['uniacid'=>$this->_uniacid]);
        //获取所有不需要周期清零的类型
        foreach ($arr as $k=>$v){

            if($credit_config[$v]==0){

                $type_arr[]=$k;
            }
        }

        $config_model = new Config();

        $config = $config_model->dataInfo(['uniacid'=>$this->_uniacid]);

        $level_cycle = $config['level_cycle'];

        $type = $config['is_current'];

        if($level_cycle==1){

            if($type==1){
                //每周
                $start_time = strtotime("this week Monday");

                $end_time   = strtotime("this week Sunday")+86400-1;
            }else{
                //上周
                $start_time = strtotime("last week Monday");

                $end_time   = strtotime("last week Sunday")+86400-1;
            }
        }elseif ($level_cycle==2){

            if($type==1){
                //本月
                $start_time = mktime(0, 0, 0, date('m'), 1, date('Y'));

                $end_time   = mktime(23, 59, 59, date('m'), date('t'), date('Y'));

            }else{
                //上月
                $start_time = mktime(0, 0, 0, date('m') - 1, 1, date('Y'));

                $end_time   = mktime(23, 59, 59, date('m') - 1, date('t', $start_time), date('Y'));

            }
        }elseif ($level_cycle==3){
            //本季度|上季度
            $quarter = $type==1 ? ceil((date('n'))/3) : ceil((date('n'))/3)-1;//获取当前季度

            $start_time= mktime(0, 0, 0,$quarter*3-2,1,date('Y'));

            $end_time  = mktime(0, 0, 0,$quarter*3+1,1,date('Y'));

        }elseif ($level_cycle==4){

            if($type==1){
                //本年
                $start_time = mktime(0, 0, 0, 1, 1, date('Y'));

                $end_time   = mktime(23, 59, 59, 12, 31, date('Y'));

            }else{
                //去年
                $year = date('Y') - 1;

                $start_time = mktime(0, 0, 0, 1, 1, $year);

                $end_time   = mktime(23, 59, 59, 12, 31, $year);

            }

        }elseif ($level_cycle==5){

            $day = date('d',time());
            //本期
            if($type==1){
                //下半月
                if($day>15){

                    $start_time = strtotime(date ('Y-m-16', time()));

                    $end_time   = strtotime(date('Y-m-t', time()))+86399;

                }else{

                    $start_time = strtotime(date ('Y-m-01', time()));

                    $end_time   = strtotime(date('Y-m-16', time()))-1;

                }

            }else{
                //下半月
                if($day>15){

                    $start_time = strtotime(date ('Y-m-01', time()));

                    $end_time   = strtotime(date('Y-m-16', time()))-1;

                }else{

                    $start_time = strtotime(date ('Y-m-16', strtotime('-1 month')));

                    $end_time   = strtotime(date('Y-m-t', strtotime('-1 month')))+86399;
                }
            }
        }

        $where = [];

        if(!empty($start_time)){

            $where[] = ['create_time','between',"$start_time,$end_time"];

            $where[] = ['type','in',$type_arr];
        }
        //记录
        $data = $record_model->where($dis)->where(function ($query) use ($where){
            $query->whereOr($where);
        })->order('id desc')->paginate(10)->toArray();

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $v['create_time'] = date('Y.m.d H:i',$v['create_time']);
            }
        }
        //技师信用分
        $data['credit_value'] = $record_model->getSingleCoachValue($this->_uniacid,$this->cap_info['id']);

        $data['config'] = $credit_config;

        return $this->success($data);
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
     * @DataTime: 2021-07-05 23:16
     * @功能说明:评价列表
     */
    public function commentList(){

        $input = $this->_param;

        $dis[] = ['a.uniacid','=',$this->_uniacid];

        $dis[] = ['a.status','=',1];

        $dis[] = ['d.id','=',$this->cap_info['id']];

        if(!empty($input['is_good'])){

            if($input['is_good']==1){

                $dis[] = ['a.star','>',3];
            }else{

                $dis[] = ['a.star','<=',3];
            }
        }

        if(!empty($input['coach_name'])){

            $dis[] = ['d.coach_name','like','%'.$input['coach_name'].'%'];
        }

        if(!empty($input['goods_name'])){

            $dis[] = ['c.goods_name','like','%'.$input['goods_name'].'%'];
        }

        $comment_model = new Comment();

        $config_model  = new Config();

        $data = $comment_model->dataList($dis);

        $anonymous_evaluate = $config_model->where(['uniacid'=>$this->_uniacid])->value('anonymous_evaluate');

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $v['create_time'] = date('Y-m-d H:i:s',$v['create_time']);
                //开启匿名评价
                if($anonymous_evaluate==1||$v['user_id']==0){

                    $v['nickName'] = '匿名用户';

                    $v['avatarUrl']= 'https://' . $_SERVER['HTTP_HOST'] . '/admin/farm/default-user.png';
                }
            }
        }
        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-01-18 14:41
     * @功能说明:修改评论置顶状态
     */
    public function updateCommentGood(){

        $input = $this->_input;

        $comment_model = new Comment();

        $res = $comment_model->dataUpdate(['id'=>$input['id']],['is_good'=>$input['is_good']]);

        return $this->success($res);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-03-05 14:04
     * @功能说明:获取技师
     */
    public function getCoachWalletAccount(){

        $user_id = $this->cap_info['user_id'];

        $adapay_member_model = new Member();

        $adapay_bank_model   = new Bank();

        $user_model = new User();

        $auth = AdminMenu::getAuthList((int)$this->_uniacid,['heepay','adapay']);

        $data['bank_card_id'] = '';

        if($auth['adapay']==true){
            //分账系统绑定的银行卡 汇付
            $adapay_member = $adapay_member_model->where(['user_id'=>$user_id])->where('status','>',-1)->find();

            if(!empty($adapay_member)){

                $data['bank_card_id'] = $adapay_bank_model->where(['order_member_id'=>$adapay_member['id']])->value('card_id');
            }

            $data['bank_status'] = !empty($adapay_member)?$adapay_member->status:-1;
        }else{
            //汇付宝
            $member = new \app\heepay\model\Member();

            $heepay_member = $member->where(['user_id'=>$this->getUserId()])->where('status','>',-1)->field('bank_card_no,audit_status as status')->find();

            if(!empty($heepay_member)){

                $data['bank_card_id'] = $heepay_member->bank_card_no;
            }

            $data['bank_status'] = !empty($heepay_member)?$heepay_member->status:-1;
        }

        $data['alipay_number'] = $user_model->where(['id'=>$user_id])->value('alipay_number');

        $data['alipay_name']   = $user_model->where(['id'=>$user_id])->value('alipay_name');

        $data['user_id'] = $user_id;

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-03-14 11:55
     * @功能说明:发送验证码
     */
    public function coachAccountSendShortMsg(){

        $input = $this->_input;
        //验证码验证
        $config = new ShortCodeConfig();

        $phone  = getConfigSetting($this->_uniacid,'login_auth_phone');

        $key    = 'coachAccountSendShortMsgkey'.$this->cap_info['id'];

        $res    = $config->sendSmsCode($phone,$this->_uniacid,$key);

        if(!empty($res['Message'])&&$res['Message']=='OK'){

            return $this->success(1);

        }else{

            return $this->error($res['Message']);
        }
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-02-27 17:39
     * @功能说明:设置技师账号
     */
    public function setCoachAccount(){

        $input = $this->_input;

        $coach_account_phone_status = getConfigSetting($this->_uniacid,'coach_account_phone_status');

        if($coach_account_phone_status==1){

            if(empty($input['phone_code'])){

                return $this->error('请输入验证码');
            }

            $phone  = getConfigSetting($this->_uniacid,'login_auth_phone');

            $key    = $phone.'coachAccountSendShortMsgkey'.$this->cap_info['id'];

            if($input['phone_code']!= getCache($key,$this->_uniacid)){

                $this->errorMsg('验证码错误');
            }
        }

        $input['coach_id'] = $this->cap_info['id'];

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

        $dis[] = ['coach_id','=',$this->cap_info['id']];

        if(!empty($input['type'])){

            $dis[] = ['type','=',$input['type']];
        }

        $record_model = new CashUpdateRecord();

        if(!empty($input['name'])){

            $id = $record_model->getDataByTitle($input['name']);

            $dis[] = ['id','in',$id];
        }

        $admin_model = new \app\massage\model\Admin();

        $data = $record_model->dataList($dis,10);

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $v['create_time'] = date('Y-m-d H:i:s',$v['create_time']);

                $v['create_user'] = $admin_model->where(['id'=>$v['create_user']])->value('username');

                if(!empty($v['admin_update_id'])){

                    $v['admin_update_name'] = $record_model->getUpdateObjTitle($v['admin_update_id'],$v['admin_type']);
                }

                $v['user_name'] = $record_model->getUpdateObjTitle($v['coach_id'],$v['type']);
            }
        }

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-05-22 16:44
     * @功能说明:修改订单地址
     */
    public function updateOrderAddress(){

        $input = $this->_input;

        $order_address_model = new OrderAddress();

        $order_model = new Order();

        $order_info = $order_model->where(['id'=>$input['order_id']])->field('id as order_id,is_add,add_pid')->find();

        $order_id = $order_info['is_add']==0?$order_info['order_id']:$order_info['add_pid'];

        $dis = [

            ['add_pid' ,'=', $order_id],

            ['is_add'    ,'=',  1],
        ];

        $dis1 =[
            ['id' ,'=', $order_id],

            ['is_add'   ,'=',  0]
        ];

        $order_list = $order_model->where(function ($query) use ($dis,$dis1){
            $query->whereOr([$dis,$dis1]);
        })->field('id as order_id')->select()->toArray();

        Db::startTrans();

        if(!empty($order_list)){

            foreach ($order_list as $value){

                $res = $order_address_model->updateOrderAddress($value['order_id'],$input,$this->_user['id'],2);

                if(!empty($res['code'])){

                    Db::rollback();

                    $this->errorMsg($res['msg']);
                }
            }
        }

        Db::commit();

        return $this->success(true);
    }





    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-23 15:06
     * @功能说明：储值佣金列表
     */
    public function memberDiscountCommissionList(){

        $input = $this->_param;

        $dis[] = ['status','=',2];

        $where[] = ['a.status','=',2];

        $dis[] = ['type','=',24];

        $where[] = ['a.type','=',24];

        $dis[] = ['top_id','=',$this->cap_info['id']];

        $where[] = ['a.top_id','=',$this->cap_info['id']];

        if(!empty($input['start_time'])){

            $dis[] = ['create_time','between',"{$input['start_time']},{$input['end_time']}"];

            $where[] = ['a.create_time','between',"{$input['start_time']},{$input['end_time']}"];
        }

        $month = !empty($input['month'])?$input['month']:'';

        $comm_model = new Commission();

        $user_model = new User();

        if(!empty($month)){

            $firstday = date('Y-m-01', $month);

            $lastday  = date('Y-m-d H:i:s', strtotime("$firstday +1 month")-1);

            $data = $comm_model
                ->where($dis)
                ->whereTime('create_time','<=',$lastday)
                ->field('order_id,round(cash,2) as coach_cash,create_time,user_id,order_cash')
                ->order('create_time desc,id desc')
                ->paginate(10)
                ->toArray();
        }else{

            $data = $comm_model
                ->where($dis)
                ->field('order_id,round(cash,2) as coach_cash,create_time,user_id,order_cash')
                ->order('create_time desc,id desc')
                ->paginate(10)
                ->toArray();
        }

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $year  = date('Y',$v['create_time']);

                $month = date('m',$v['create_time']);

                $v['month_text'] = $year.'年'.$month.'月';

                $v['month'] = date('Y-m',$v['create_time']);

                $v['create_time'] = date('Y.m.d H:i:s',$v['create_time']);

                $v['total_cash']  = $comm_model->where($dis)->whereMonth('create_time',$v['month'])->sum('cash');

                $v['total_count'] = $comm_model->where($dis)->whereMonth('create_time',$v['month'])->count();
                //手续费
                $v['point_cash'] = $comm_model->alias('a')
                                    ->join('massage_service_order_commission_share b','a.id = b.comm_id')
                                    ->where($where)
                                    ->whereMonth('a.create_time',$v['month'])
                                    ->group('a.id')
                                    ->sum('b.share_cash');
                //用户昵称
                $v['nickName'] = $user_model->where(['id'=>$v['user_id']])->value('nickName');

                $v['total_cash'] = round($v['total_cash'],2);

                $v['point_cash'] = round($v['point_cash'],2);
            }
        }

        return $this->success($data);
    }


    /**
     * @author chenniang
     * @DataTime: 2024-09-09 19:12
     * @功能说明:统计
     */
    public function memberDiscountCommissionData(){

        $input = $this->_param;

        $dis[] = ['status','=',2];

        $dis[] = ['type','=',24];

        $dis[] = ['top_id','=',$this->cap_info['id']];

        $comm_model = new Commission();

        $data['total_cash'] = $comm_model->where($dis)->sum('cash');
        //手续费
        $data['total_point'] = $comm_model->alias('a')
            ->join('massage_service_order_commission_share b','a.id = b.comm_id')
            ->where(['a.uniacid'=>$this->_uniacid,'a.type'=>24,'a.status'=>2,'a.top_id'=>$this->cap_info['id']])
            ->group('a.id')
            ->sum('b.share_cash');

        $data['total_point'] = round($data['total_point'],2);

        $data['total_cash'] = round($data['total_cash'],2);

        if(!empty($input['start_time'])){

            $dis[] = ['create_time','between',"{$input['start_time']},{$input['end_time']}"];

            $data['cash'] = $comm_model->where($dis)->sum('cash');
            //手续费
            $data['point'] = $comm_model->alias('a')
                ->join('massage_service_order_commission_share b','a.id = b.comm_id')
                ->where(['a.uniacid'=>$this->_uniacid,'a.type'=>24,'a.status'=>2,'a.top_id'=>$this->cap_info['id']])
                ->where('a.create_time','between',"{$input['start_time']},{$input['end_time']}")
                ->group('a.id')
                ->sum('b.share_cash');

            $data['point'] = round($data['point'],2);

            $data['cash'] = round($data['cash'],2);
        }

        return $this->success($data);
    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-25 17:40
     * @功能说明：技师会员折扣卡
     *
     */
    public function coachMemberDiscountQr(){

        $input = $this->_param;

        $key = 'coachMemberDiscountQr'.$this->cap_info['id'].'-'.$this->is_app;

        $qr  = getCache($key,$this->_uniacid);

        if(empty($qr)){
            //小程序
            if($this->is_app==0){

                $input['page'] = 'memberdiscount/pages/index';

                $input['coach_id'] = $this->cap_info['id'];

                $user_model = new User();
                //获取二维码
                $qr = $user_model->orderQr($input,$this->_uniacid);

            }else{

                $page = 'https://'.$_SERVER['HTTP_HOST'].'/h5/#/memberdiscount/pages/index?coach_id='.$this->cap_info['id'];

                $qr = base64ToPng(getCode($this->_uniacid,$page));
            }

            setCache($key,$qr,86400,$this->_uniacid);
        }

        $qr = !empty($qr)?$qr:'https://'.$_SERVER['HTTP_HOST'].'/favicon.ico';

        return $this->success($qr);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-25 17:40
     * @功能说明：技师会员折扣卡
     *
     */
    public function coachBalanceDiscountQr(){

        $input = $this->_param;

        $key = 'coachBalanceDiscountQr'.$this->cap_info['id'].'-'.$this->is_app;

        $qr  = getCache($key,$this->_uniacid);

        if(empty($qr)){
            //小程序
            if($this->is_app==0){

                $input['page'] = 'user/pages/stored/list';

                $input['discount'] = 1;

                $input['coach_id'] = $this->cap_info['id'];

                $user_model = new User();
                //获取二维码
                $qr = $user_model->orderQr($input,$this->_uniacid);

            }else{

                $page = 'https://'.$_SERVER['HTTP_HOST'].'/h5/#/user/pages/stored/list?discount=1&coach_id='.$this->cap_info['id'];

                $qr = base64ToPng(getCode($this->_uniacid,$page));
            }

            setCache($key,$qr,86400,$this->_uniacid);
        }

        $qr = !empty($qr)?$qr:'https://'.$_SERVER['HTTP_HOST'].'/favicon.ico';

        return $this->success($qr);
    }


    /**
     * @author chenniang
     * @DataTime: 2024-09-20 13:52
     * @功能说明:今日订单数据
     */
    public function todayOrderData(){

        $data['coach_cash'] = $this->order_model->where(['coach_id'=>$this->cap_info['id']])->whereTime('create_time','today')->where('pay_type','in',[3,4,5,6,7])->sum('coach_cash');

        $data['order_num']  = $this->order_model->where(['coach_id'=>$this->cap_info['id']])->whereTime('create_time','today')->where('pay_type','in',[3,4,5,6,7])->count();

        $first_service_cash = $this->order_model->where(['coach_id'=>$this->cap_info['id'],'is_add'=>0])->whereTime('create_time','today')->where('pay_type','in',[3,4,5,6,7])->sum('true_service_price');

        $first_material_cash= $this->order_model->where(['coach_id'=>$this->cap_info['id'],'is_add'=>0])->whereTime('create_time','today')->where('pay_type','in',[3,4,5,6,7])->sum('material_price');
        //首单金额
        $data['first_order_cash']  = round($first_service_cash+$first_material_cash,2);
        //技师佣金
        $data['coach_cash']  = round(     $data['coach_cash'],2);

        $add_service_cash = $this->order_model->where(['coach_id'=>$this->cap_info['id'],'is_add'=>1])->whereTime('create_time','today')->where('pay_type','in',[3,4,5,6,7])->sum('true_service_price');

        $add_material_cash= $this->order_model->where(['coach_id'=>$this->cap_info['id'],'is_add'=>1])->whereTime('create_time','today')->where('pay_type','in',[3,4,5,6,7])->sum('material_price');
        //加钟金额
        $data['add_order_cash']  = round($add_service_cash+$add_material_cash,2);
        //服务时长
        $data['time_long'] = $this->order_model->where(['coach_id'=>$this->cap_info['id']])->whereTime('create_time','today')->where('pay_type','=',7)->sum('true_time_long');

        $data['time_long'] = timeHour($data['time_long']);

        $integral_model = new Integral();
        //积分
        $data['integral'] = $integral_model->where(['coach_id'=>$this->cap_info['id'],'status'=>1])->where('type','in',[0,2])->whereTime('create_time','today')->sum('integral');

        $data['integral'] = round($data['integral'],2);



        return $this->success($data);
    }


    /**
     * @author chenniang
     * @DataTime: 2024-09-23 11:13
     * @功能说明:技师数据统计
     */
    public function coachDataCount(){
        //获取考核数据和等级
        $data = $this->model->getCoachLevel($this->cap_info['id'],$this->_uniacid,2);

        $level_cycle = $data['level_cycle'];

        $is_current  = $data['is_current'];
        //每周
        if($level_cycle==1){

            $time_data = $is_current==1?'week':'last week';
            //每月
        }elseif ($level_cycle==2){

            $time_data = $is_current==1?'month':'last month';
            //每季度
        }elseif ($level_cycle==3){

            $quarter = $is_current==1 ? ceil((date('n'))/3) : ceil((date('n'))/3)-1;//获取当前季度

            $start_time= mktime(0, 0, 0,$quarter*3-2,1,date('Y'));

            $end_time  = mktime(0, 0, 0,$quarter*3+1,1,date('Y'));
            //每年
        }elseif ($level_cycle==4){

            $time_data = $is_current==1?'year':'last year';

        }elseif ($level_cycle==5){

            $day = date('d',time());
            //本期
            if($is_current==1){
                //下半月
                if($day>15){

                    $start_time = strtotime(date ('Y-m-16', time()));

                    $end_time   = strtotime(date('Y-m-t', time()))+86399;

                }else{

                    $start_time = strtotime(date ('Y-m-01', time()));

                    $end_time   = strtotime(date('Y-m-16', time()))-1;
                }

            }else{
                //上期
                //下半月
                if($day>15){

                    $start_time = strtotime(date ('Y-m-01', time()));

                    $end_time   = strtotime(date('Y-m-16', time()))-1;

                }else{

                    $start_time = strtotime(date ('Y-m-16', strtotime('-1 month')));

                    $end_time   = strtotime(date('Y-m-t', strtotime('-1 month')))+86399;
                }
            }
        }

        if(!empty($start_time)){

            $dis[] = ['create_time','between',"$start_time,$end_time"];

            $map[] = ['a.create_time','between',"$start_time,$end_time"];
        }

        $dis[] = ['top_id','=',$this->cap_info['id']];

        $map[] = ['b.share_id','=',$this->cap_info['id']];

        $dis[] = ['status','=',2];

        $map[] = ['a.status','=',2];

        $comm_model = new Commission();

        $map1 = [

            ['b.type','=',1],
        ];

        $map2 =[

            ['b.cash_type' ,'=', 1],

            ['a.type'   ,'in',  [3,8]]
        ];

        if(empty($time_data)){

            $list['coach_cash'] = $comm_model->where($dis)->where('type','in',[3,17,18])->sum('cash');

            $list['car_cash']   = $comm_model->where($dis)->where(['type'=>8])->sum('cash');

            $list['balance_cash']  = $comm_model->where($dis)->where('type','in',[7,24,25])->sum('cash');

            $list['charging_cash'] = $comm_model->alias('a')
                                      ->join('massage_service_order_commission_share b','a.id = b.comm_id')
                                      ->where($map)
                                      ->where(function ($query) use ($map1,$map2){
                                        $query->whereOr([$map1,$map2]);
                                      })
                                      ->group('b.id')
                                      ->sum('b.share_cash');
        }else{

            $list['coach_cash'] = $comm_model->where($dis)->whereTime('create_time',$time_data)->where('type','in',[3,17,18])->sum('cash');

            $list['car_cash']   = $comm_model->where($dis)->whereTime('create_time',$time_data)->where(['type'=>8])->sum('cash');

            $list['balance_cash']  = $comm_model->where($dis)->whereTime('create_time',$time_data)->where('type','in',[7,24,25])->sum('cash');

            $list['charging_cash'] = $comm_model->alias('a')
                                    ->join('massage_service_order_commission_share b','a.id = b.comm_id')
                                    ->where($map)
                                    ->whereTime('a.create_time',$time_data)
                                    ->where(function ($query) use ($map1,$map2){
                                        $query->whereOr([$map1,$map2]);
                                    })
                                    ->group('b.id')
                                    ->sum('b.share_cash');
        }

        $list['charging_cash'] = round($list['charging_cash'],2);

        $list['coach_cash'] = round($list['coach_cash'],2);

        $list['car_cash']   = round($list['car_cash'],2);

        $list['balance_cash'] = round($list['balance_cash'],2);

        $data['coach_data']= $list;

        $data['collect_num'] = $this->cap_info['collect_num'];

        $data['virtual_collect'] = $this->cap_info['virtual_collect'];

        $data['pv'] = $this->cap_info['pv'];

        $data['star'] = number_format($this->cap_info['star'],1);

        $custom_model = new CustomBalance();

        $coach_level = $custom_model->getCoachCustomBalance($this->cap_info['id']);
        //技师是否是阶段性提成
        $data['stage_commission'] = !empty($coach_level)?1:0;

        return $this->success($data);
    }



    /**
     * @author chenniang
     * @DataTime: 2024-09-23 16:33
     * @功能说明:技师公告列表
     */
    public function coachNoticeList(){

        $notice_model = new CoachNotice();

        $data = $notice_model->where(['uniacid'=>$this->_uniacid,'true_status'=>1,'status'=>0])->order('top desc,id desc')->paginate(10)->toArray();

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $v['create_time'] = date('Y-m-d H:i:s',$v['create_time']);
            }
        }

        return $this->success($data);
    }



    /**
     * @author chenniang
     * @DataTime: 2024-09-23 17:41
     * @功能说明:技师公告详情
     */
    public function coachNoticeInfo(){

        $input = $this->_param;

        $notice_model = new CoachNotice();

        $data = $notice_model->dataInfo(['id'=>$input['id'],'true_status'=>1]);

        if(empty($data)){

            $this->errorMsg('公告已下架');
        }

        $data['create_time'] = date('Y-m-d H:i:s',$data['create_time']);

        return $this->success($data);
    }

    /**
     * @Desc: 放心签实名认证
     * @return mixed
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/16 16:13
     */
    public function fxqCheck()
    {
        $input = $this->_param;

        $model = Fxq::create($this->_uniacid, $this->cap_info['id']);

        if (is_array($model) && isset($model['code'])) {

            return $this->error($model['msg']);
        }

        $p = new PermissionFxq((int)$this->_uniacid);

        $auth = $p->pAuth();

        if ($auth == false) {

            return $this->error('未开启合同签署');
        }

        $status = FxqConfig::getStatus($this->_uniacid, $this->cap_info['admin_id']);

        if (empty($status)){

            return $this->error('未开启合同签署');
        }

        $check_type = getConfigSetting($this->_uniacid,'fxq_check_type');

        if ($check_type == 1){

            $code = $model->idCheck($input['name'], $input['id_code']);
        }else{
            if ($this->is_app == 0) {

                $code = $model->faceCheckWxApp($input['name'], $input['id_code']);
            } else {

                $code = $model->faceCheckH5($input['name'], $input['id_code'],$this->is_app);
            }
        }

        if (isset($code['code'])) {

            return $this->error($code['msg']);
        }

        $data = [
            'check_type' => $check_type,

            'is_app' => $this->is_app,

            'url' => $code['url'] ?? '',

            'eid_token' => $code['token'] ?? ''
        ];

        return $this->success($data);
    }

    /**
     * @Desc: 放心签验证码
     * @return mixed
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/17 18:29
     */
    public function sendFxqCode()
    {
        $phone = \request()->param('phone', '');

        if (empty($phone)) {

            return $this->error('请输入手机号');
        }

        $model = Fxq::create($this->_uniacid, $this->cap_info['id']);

        if (is_array($model) && isset($model['code'])) {

            return $this->error($model['msg']);
        }

        $code = $model->sendCode($phone);

        if (isset($code['code'])) {

            return $this->error($code['msg']);
        }

        return $this->success($code);
    }

    /**
     * @Desc: 放心签技师签署合同
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/22 10:30
     */
    public function fxqSign()
    {
        $code = \request()->param('code', '');

        $phone = \request()->param('phone', '');

        if (empty($code) || empty($phone)) {

//            return $this->error('验证码错误');
        }

        $key = 'fxq_code';

        $value = getCache($phone . $key, $this->_uniacid);

        if ($value != $code) {

            return $this->error('验证码错误');
        }

        $fxq = FxqContract::where(['admin_id' => $this->cap_info['admin_id'], 'coach_id' => $this->cap_info['id'], 'status' => 2])->find();

        $model = Fxq::create($this->_uniacid, $this->cap_info['id']);

        if (is_array($model) && isset($model['code'])) {

            return $this->error($model['msg']);
        }

        $code = $model->coachContractSigning($fxq['id']);

        if (isset($code['code'])) {

            return $this->error($code['msg']);
        }

        return $this->success($code);
    }

    /**
     * @Desc:  新增合同
     * @param $coach_id
     * @param $admin_id
     * @return array|true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/11/6 14:34
     */
    public function addContract()
    {
        $coach_id = $this->cap_info['id'];

        $admin_id = $this->cap_info['admin_id'];

        $contract = FxqContract::getInfo([['uniacid', '=', $this->_uniacid], ['coach_id', '=', $coach_id], ['admin_id', '=', $admin_id], ['status', '>', -1]]);

        if (!empty($contract)) {

            if (in_array($contract['status'], [2])) {

                return $this->success($contract);
            } elseif ($contract['status'] == 3 && $contract['end_time'] > time()) {

                return $this->success($contract);
            }
        }

        $key = 'fxq_contract_' . $coach_id . $admin_id;

        incCache($key, 1, $this->_uniacid);

        $value = getCache($key, $this->_uniacid);

        if ($value != 1) {

            decCache($key, 1, $this->_uniacid);

            return $this->error('正在生成合同，请稍后');
        }

        $num = FxqContract::getNo($this->_uniacid);

        $config = FxqConfig::getInfo(['uniacid' => $this->_uniacid, 'admin_id' => $admin_id]);

        if (empty($config['contract_pdf_base64']) || (!empty($config['commitment']) && empty($config['commitment_pdf_base64'])) || empty($config['contract_years'])) {
            decCache($key, 1, $this->_uniacid);

            return $this->error('请联系平台配置合同');
        }

        Db::startTrans();

        try {

            $insert = [
                'uniacid' => $this->_uniacid,
                'fxq_admin_id' => $admin_id,
                'admin_id' => $admin_id,
                'coach_id' => $coach_id,
                'status' => 1,
                'contract_years' => $config['contract_years'],
                'date' => date('Y-m-d'),
                'number' => $num,
                'company_name' => $config['company_name'],
                'company_id_no' => $config['company_id_no'],
                'company_signature' => $config['company_signature']
            ];

            $contract_id = FxqContract::add($insert);

            if (!$contract_id) {

                throw new \Exception('合同生成失败');
            }

            $insert_file = [
                [
                    'uniacid' => $this->_uniacid,
                    'contract_id' => $contract_id,
                    'contract' => $config['contract_pdf_base64'],
                    'contract_no' => 'CN-' . date('Ymd') . '-' . $num,
                    'type' => 1,
                    'company_contract' => '',
                    'create_time' => time(),
                    'company_view_contract' => '',
                    'company_view_contract_img' => ''
                ]
            ];

            if (!empty($config['commitment_pdf_base64'])) {
                $insert_file[] = [
                    'uniacid' => $this->_uniacid,
                    'contract_id' => $contract_id,
                    'contract' => $config['commitment_pdf_base64'],
                    'contract_no' => 'CM-' . date('Ymd') . '-' . $num,
                    'type' => 2,
                    'company_contract' => $config['commitment_pdf_base64'],
                    'create_time' => time(),
                    'company_view_contract' => base64ToPdf($config['commitment_pdf_base64']),
                    'company_view_contract_img' => $config['commitment_pdf_img']
                ];
            }

            $res = FxqContractFile::insertAll($insert_file);

            if (!$res) {

                throw new \Exception('合同文件生成失败');
            }

            $model = Fxq::create($this->_uniacid, $this->cap_info['id']);

            if (is_array($model) && isset($model['code'])) {

                throw new \Exception($model['msg']);
            }

            $code = $model->companyContractSigning($contract_id);

            if (isset($code['code'])) {

                throw new \Exception($code['msg']);
            }

            Db::commit();
        } catch (\Exception $exception) {

            Db::rollback();

            decCache($key, 1, $this->_uniacid);

            return $this->error($exception->getMessage());
        }

        decCache($key, 1, $this->_uniacid);

        $contract = FxqContract::getInfo(['id' => $contract_id]);

        return $this->success($contract);
    }

}
