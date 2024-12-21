<?php
namespace app\adminuser\controller;
use app\ApiRest;

use app\massage\model\BalanceWater;
use app\massage\model\CashUpdateRecord;
use app\massage\model\ChannelList;
use app\massage\model\ChannelQr;
use app\massage\model\Coach;

use app\massage\model\Commission;
use app\massage\model\CommShare;
use app\massage\model\CompanyWater;
use app\massage\model\Config;
use app\massage\model\Goods;

use app\massage\model\Order;
use app\massage\model\OrderGoods;
use app\massage\model\Police;
use app\massage\model\RefundOrder;
use app\massage\model\RefundOrderGoods;
use app\massage\model\Salesman;
use app\massage\model\User;
use app\massage\model\Wallet;
use longbingcore\wxcore\WxSetting;
use think\App;
use think\facade\Db;
use think\Request;


class IndexUser extends ApiRest
{

    protected $model;

    protected $admin_user;


    public function __construct(App $app) {

        parent::__construct($app);

        $this->model = new \app\adminuser\model\AdminUser();

        $dis[] = ['user_id','=',$this->getUserId()];

        $this->admin_user = $this->model->dataInfo($dis);

        if(empty($this->admin_user)){

            $this->errorMsg('你不是管理员');
        }
    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-08 11:39
     * @功能说明:技师首页
     */
    public function index(){

        $comm_model = new Commission();

        $wallet_model = new Wallet();

        $data = $this->admin_user;

        $data['total_cash'] = $comm_model->where(['status'=>2,'type'=>16])->sum('company_cash');

        $data['wallet_cash'] = $wallet_model->where(['type'=>11])->where('status','in',[2])->sum('total_price');

        return $this->success($data);

    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-24 13:33
     * @功能说明:渠道商申请提现
     */
    public function applyWallet()
    {

        $input = $this->_input;

        if (empty($input['apply_price']) || $input['apply_price'] < 0.01) {

            $this->errorMsg('提现费最低一分');
        }

        if ($input['apply_price'] > $this->admin_user['cash']) {

            $this->errorMsg('余额不足');
        }
        //获取税点
        $tax_point = getConfigSetting($this->_uniacid,'tax_point');

        $balance = 100 - $tax_point;

        $key = 'adminuser_wallet';
        //加一个锁防止重复提交
        incCache($key, 1, $this->_uniacid);

        $value = getCache($key,$this->_uniacid);

        if ($value!=1) {

            decCache($key,1, $this->_uniacid);

            $this->errorMsg('网络错误，请刷新重试');
        }

        Db::startTrans();

        $cash = $input['apply_price'];
        //减佣金
       // $res = $this->model->where(['id'=>$this->admin_user['id'],'cash'=>$this->admin_user['cash']])->update(['cash'=>Db::Raw("cash-$cash")]);

        $water_model = new CompanyWater();

        $res = $water_model->updateCash($this->_uniacid,0,$cash,0);

        if ($res != 1) {

            Db::rollback();
            //减掉
            decCache($key,1, $this->_uniacid);

            $this->errorMsg('申请失败');
        }

        $insert = [

            'uniacid' => $this->_uniacid,

            'user_id' => $this->getUserId(),

            'coach_id' => $this->admin_user['id'],

            'admin_id' => 0,

            'total_price' => $input['apply_price'],

            'balance' => $balance,

            'apply_price' => round($input['apply_price'] * $balance / 100, 2),

            'service_price' => round( $input['apply_price'] * $tax_point / 100, 2),

            'code' => orderCode(),

            'tax_point' => $tax_point,

            'text' => $input['text'],

            'type' => 11,

            'last_login_type' => $this->is_app,

            'apply_transfer' => !empty($input['apply_transfer'])?$input['apply_transfer']:0

        ];

        $wallet_model = new Wallet();
        //提交审核
        $res = $wallet_model->dataAdd($insert);

        if ($res != 1) {

            Db::rollback();
            //减掉
            decCache($key, 1,$this->_uniacid);

            $this->errorMsg('申请失败');
        }

        Db::commit();
        //减掉
        decCache($key,1, $this->_uniacid);

        return $this->success($res);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-30 14:39
     * @功能说明:渠道商提现记录
     */
    public function walletList()
    {

        $wallet_model = new Wallet();

        $input = $this->_param;

        $dis = [

            'type' => 11
        ];

        if (!empty($input['status'])) {

            $dis['status'] = $input['status'];
        }
        //提现记录
        $data = $wallet_model->dataList($dis, 10);

        if (!empty($data['data'])) {

            foreach ($data['data'] as &$v) {

                $v['create_time'] = date('Y-m-d H:i:s', $v['create_time']);
            }
        }
        //累计提现
        $data['extract_total_price'] = $wallet_model->where(['type'=>11])->where('status','in',[2])->sum('total_price');

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

        if(!empty($input['coach_id'])){

            $dis[] = ['coach_id','=',$input['coach_id']];
        }

        $dis[] = ['type','=',8];

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

                $v['user_name'] = $record_model->getUpdateObjTitle($v['coach_id'],$v['type']);
            }
        }

        return $this->success($data);
    }





}
