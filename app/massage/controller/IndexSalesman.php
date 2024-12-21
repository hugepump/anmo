<?php
namespace app\massage\controller;
use app\AdminRest;
use app\ApiRest;
use app\massage\model\ChannelList;
use app\massage\model\ChannelQr;
use app\massage\model\City;
use app\massage\model\Coach;
use app\massage\model\CoachLevel;
use app\massage\model\Commission;
use app\massage\model\CommShare;
use app\massage\model\Config;
use app\massage\model\DistributionList;
use app\massage\model\Order;
use app\massage\model\Salesman;
use app\massage\model\SalesmanWater;
use app\massage\model\User;
use app\massage\model\Wallet;
use longbingcore\wxcore\YsCloudApi;
use think\App;
use think\facade\Db;


class IndexSalesman extends ApiRest
{


    protected $model;

    protected $user_model;

    protected $cash_model;

    protected $wallet_model;

    protected $coach_model;

    protected $salesman_info;


    public function __construct(App $app) {

        parent::__construct($app);

        $this->model        = new Salesman();

        $this->user_model   = new User();

        $this->cash_model   = new Commission();

        $this->wallet_model = new Wallet();

        $this->coach_model  = new Coach();

        $this->salesman_info  = $this->salesmanDataInfo();

        if(empty($this->salesman_info)){

            $this->errorMsg('你还不是业务员');
        }

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-26 14:39
     * @功能说明:业务员详情
     */
    public function salesmanDataInfo(){

        $cap_dis[] = ['user_id', '=', $this->getUserId()];

        $cap_dis[] = ['status', 'in', [2,3]];

        $data = $this->model->dataInfo($cap_dis);

        $salesman_balance = getConfigSetting($this->_uniacid,'salesman_balance');

        $data['balance'] = $data['balance']>=0?$data['balance']:$salesman_balance;

        return $data;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-23 13:49
     * @功能说明:业务员中心
     */
    public function index(){

        $data = $this->salesman_info;

        $user_model = new User();

        $wallet_model = new Wallet();

        $data['avatarUrl']   = $user_model->where(['id'=>$this->salesman_info['user_id']])->value('avatarUrl');
        //累计提现
        $data['wallet_cash'] = $wallet_model->where(['coach_id'=>$this->salesman_info['id'],'type'=>6])->where('status','in',[1,2])->sum('total_price');
        //总成交金额
        $data['order_price']   = $this->model->salesmanOrderPrice($this->salesman_info['id']);

        $data['material_price'] = $this->model->salesmanOrderPrice($this->salesman_info['id'],0,3);

        $data['wallet_cash'] = round($data['wallet_cash'],2);
        //分销方式
        $data['salesman_channel_fx_type'] = getConfigSetting($this->_uniacid,'salesman_channel_fx_type');

        if(!empty($data['admin_id'])){

            $admin_model = new \app\massage\model\Admin();

            $data['agent_name'] = $admin_model->where(['id'=>$data['admin_id'],'status'=>1])->value('agent_name');
        }

        return $this->success($data);

    }





    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-23 14:30
     * @功能说明:业务渠道商佣金记录
     */
    public function salesmanChannelCash(){

        $channel_model = new ChannelList();

        $user_model    = new User();

        $dis[] = ['salesman_id','=',$this->salesman_info['id']];

        $dis[] = ['status','in',[2,3]];

        $channel_list = $channel_model->dataList($dis,10,'user_name,id,balance,user_id');

        $channel_balance = getConfigSetting($this->_uniacid,'channel_balance');

        if(!empty($channel_list['data'])){

            foreach ($channel_list['data'] as &$value){
                //订单金额
                $value['order_price']   = $this->model->salesmanOrderPrice($this->salesman_info['id'],$value['id']);
                //物料费
                $value['material_price']= $this->model->salesmanOrderPrice($this->salesman_info['id'],$value['id'],3);
                //佣金
                $value['salesman_cash'] = $this->model->getSalesmanChannelCash($this->salesman_info['id'],$value['id']);
                //头像
                $value['avatarUrl']     = $user_model->where(['id'=>$value['user_id']])->value('avatarUrl');
                //比例
                $value['balance'] = $value['balance']>=0?$value['balance']:$channel_balance;
            }
        }

        $channel_list['salesman_channel_fx_type'] = getConfigSetting($this->_uniacid,'salesman_channel_fx_type');

        return $this->success($channel_list);
    }







    /**\
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-28 10:54
     * @功能说明:业务员渠道商明细
     */
    public function salesmanChannelOrderList(){

        $input = $this->_param;

        $dis[] = ['pay_type','=',7];

        $dis[] = ['salesman_id','=',$this->salesman_info['id']];

        $dis[] = ['channel_id','=',$input['channel_id']];

        if(!empty($input['start_time'])&&!empty($input['end_time'])){

             $dis[] = ['can_tx_date','between',"{$input['start_time']},{$input['end_time']}"];
        }

        $order_model = new Order();

        $commis_model= new Commission();

        $share_model = new CommShare();

        $data = $order_model->where($dis)->order('id desc')->field('order_code,can_tx_date,id')->paginate(10)->toArray();

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $v['can_tx_date'] = date('Y-m-d H:i:s',$v['can_tx_date']);

                $dis = [

                    'status'  => 2,

                    'type'    => 12,

                    'order_id'=> $v['id']
                ];
                //佣金
                $v['salesman_cash'] = $commis_model->where($dis)->sum('cash');

                $v['salesman_cash'] = round($v['salesman_cash'],2);
                //手续费
                $v['point_cash']    = $share_model->where(['order_id'=>$v['id'],'cash_type'=>1,'comm_type'=>12])->sum('share_cash');

            }
        }

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-11-30 18:43
     * @功能说明:业务员渠道商明细
     */
    public function salesmanChannelOrderListV2(){

        $input = $this->_param;

        $dis[] = ['a.pay_type','>',1];

        $dis[] = ['c.type','=',12];

     //   $dis[] = ['c.cash','>',0];

        $dis[] = ['c.status','>',-1];

        $dis[] = ['a.salesman_id','=',$this->salesman_info['id']];

        if(!empty($input['channel_name'])){

            $dis[] = ['b.user_name','like','%'.$input['channel_name'].'%'];

        }

        if(!empty($input['start_time'])){

            $dis[] = ['a.create_time','between',"{$input['start_time']},{$input['end_time']}"];
        }

        if(isset($input['status'])){

            $dis[] = ['a.have_tx','=',$input['status']];

        }

        $order_model = new Order();

        $commis_model= new Commission();

        $share_model = new CommShare();

        $channel_model= new ChannelList();

        $data = $order_model->alias('a')
                ->join('massage_channel_list b','a.channel_id = b.id')
                ->join('massage_service_order_commission c','a.id = c.order_id')
                ->where($dis)
                ->field('a.id,a.have_tx,a.create_time,a.order_code,a.create_time,channel_id,(a.true_service_price) as service_price,a.material_price')
                ->group('a.id')
                ->order('a.id desc')
                ->paginate(10)
                ->toarray();

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $v['status'] = $v['have_tx'];

                $v['create_time'] = date('Y-m-d H:i:s',$v['create_time']);

                $dis = [

                    'type'    => 12,

                    'order_id'=> $v['id']
                ];
                //佣金
                $salesman_cash = $commis_model->where($dis)->where('status','>',-1)->find();

                $v['salesman_cash_info']['cash'] = !empty($salesman_cash)?round($salesman_cash['cash'],2):0;

                $v['salesman_cash_info']['balance'] = !empty($salesman_cash)?$salesman_cash['balance']:0;
                //手续费
                $v['salesman_cash_info']['point_cash'] = $share_model->where(['order_id'=>$v['id'],'cash_type'=>1,'comm_type'=>12])->sum('share_cash');

                $v['channel']['channel_name'] = $channel_model->where(['id'=>$v['channel_id']])->value('user_name');

                $dis['type'] = 10;

                $v['channel']['balance'] = $commis_model->where($dis)->where('status','>',-1)->value('balance');

            }
        }

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
        //获取税点
        $tax_point = getConfigSetting($this->_uniacid,'tax_point');

        $balance = 100 - $tax_point;

        $key = 'salesman_wallet' . $this->getUserId();
        //加一个锁防止重复提交
        incCache($key, 1, $this->_uniacid);

        $value = getCache($key,$this->_uniacid);

        if ($value!=1) {

            decCache($key,1, $this->_uniacid);

            $this->errorMsg('网络错误，请刷新重试');

        }

        if (empty($input['apply_price']) || $input['apply_price'] < 0.01) {

            decCache($key,1, $this->_uniacid);

            $this->errorMsg('提现费最低一分');
        }

        $salesman = $this->model->dataInfo(['id'=>$this->salesman_info['id']]);

        if ($input['apply_price'] > $salesman['cash']) {

            decCache($key,1, $this->_uniacid);

            $this->errorMsg('余额不足');
        }

        Db::startTrans();

        $insert = [

            'uniacid' => $this->_uniacid,

            'user_id' => $this->getUserId(),

            'coach_id' => $this->salesman_info['id'],

            'admin_id' => $this->salesman_info['admin_id'],

            'total_price' => $input['apply_price'],

            'balance' => $balance,

            'apply_price' => round($input['apply_price'] * $balance / 100, 2),

            'service_price' => round( $input['apply_price'] * $tax_point / 100, 2),

            'code' => orderCode(),

            'tax_point' => $tax_point,

            'text' => $input['text'],

            'type' => 6,

            'last_login_type' => $this->is_app,

            'apply_transfer' => !empty($input['apply_transfer'])?$input['apply_transfer']:0

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

        $id = $wallet_model->getLastInsID();

        $water_model = new SalesmanWater();

        $res = $water_model->updateCash($this->_uniacid,$this->salesman_info['id'],$input['apply_price'],2,$id,0,2);

        if ($res == 0) {

            Db::rollback();
            //减掉
            decCache($key,1, $this->_uniacid);

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

            'coach_id' => $this->salesman_info['id']
        ];

        if (!empty($input['status'])) {

            $dis['status'] = $input['status'];
        }

        $dis['type'] = 6;
        //提现记录
        $data = $wallet_model->dataList($dis, 10);

        if (!empty($data['data'])) {

            foreach ($data['data'] as &$v) {

                $v['create_time'] = date('Y-m-d H:i:s', $v['create_time']);
            }
        }
        //累计提现
        $data['extract_total_price'] = $wallet_model->capCash($this->salesman_info['id'], 2, 6);

        $data['personal_income_tax_text'] = getConfigSetting($this->_uniacid,'personal_income_tax_text');

        return $this->success($data);

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-08-30 14:18
     * @功能说明:业务员码
     */
    public function salesmanQr(){

        $input = $this->_param;

        $key = 'salesman'.$this->salesman_info['id'].'-'.$this->is_app;

        $qr  = getCache($key,$this->_uniacid);

        if(empty($qr)){
            //小程序
            if($this->is_app==0){

                $input['page'] = 'user/pages/channel/apply';

                $input['salesman_id'] = $this->salesman_info['id'];
                //获取二维码
                $qr = $this->user_model->orderQr($input,$this->_uniacid);

            }else{

                $page = 'https://'.$_SERVER['HTTP_HOST'].'/h5/#/user/pages/channel/apply?salesman_id='.$this->salesman_info['id'];

                $qr = base64ToPng(getCode($this->_uniacid,$page));

            }

            setCache($key,$qr,86400,$this->_uniacid);
        }

        $qr = !empty($qr)?$qr:'https://'.$_SERVER['HTTP_HOST'].'/favicon.ico';

        return $this->success($qr);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-11-29 18:17
     * @功能说明:解除渠道商关系
     */
    public function unfriendChannel(){

        $input = $this->_input;

        $channel_model = new ChannelList();

        $channel = $channel_model->dataInfo(['id'=>$input['channel_id'],'salesman_id'=>$this->salesman_info['id']]);

        if(empty($channel)){

            $this->errorMsg('该渠道商不是你的下级');

        }

        $res = $channel_model->dataUpdate(['id'=>$input['channel_id']],['salesman_id'=>0]);

        $qr_model = new ChannelQr();

        $qr_model->dataUpdate(['channel_id'=>$input['channel_id']],['salesman_id'=>0]);

        return $this->success($res);
    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-07-14 14:03
     * @功能说明:设置业务员单独的佣金比例
     */
    public function setSalesmanBalance(){

        $input = $this->_input;

        if($input['balance']>$this->salesman_info['balance']&&getConfigSetting($this->_uniacid,'salesman_channel_fx_type')==2){

            $this->errorMsg('设置比例不能高于你的分成比例');
        }

        $channel_model = new ChannelList();

        $res = $channel_model->where('id','=',$input['channel_id'])->update(['balance'=>$input['balance']]);

        return $this->success($res);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-07-14 14:03
     * @功能说明:删除业务员单独的佣金比例
     */
    public function delSalesmanBalance(){

        $input = $this->_input;

        $channel_model = new ChannelList();

        $res = $channel_model->where('id','=',$input['channel_id'])->update(['balance'=>-1]);

        return $this->success($res);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-12-01 11:06
     * @功能说明:编辑邀请技师的比例
     */
    public function setInvChannelBalance(){

        $input = $this->_input;

        $res = $this->model->dataUpdate(['id'=>$this->salesman_info['id']],['inv_channel_balance'=>$input['balance']]);

        return $this->success($res);
    }




















}
