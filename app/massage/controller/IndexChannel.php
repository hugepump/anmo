<?php
namespace app\massage\controller;
use app\ApiRest;

use app\massage\model\BalanceWater;
use app\massage\model\ChannelList;
use app\massage\model\ChannelQr;
use app\massage\model\ChannelWater;
use app\massage\model\Coach;

use app\massage\model\Commission;
use app\massage\model\CommShare;
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


class IndexChannel extends ApiRest
{

    protected $model;

    protected $channel_info;

    protected $order_model;

    protected $user_model;

    protected $channel_name;

    public function __construct(App $app) {

        parent::__construct($app);

        $this->model = new ChannelList();

        $this->order_model = new Order();

        $this->user_model = new User();

        $cap_dis[] = ['user_id','=',$this->getUserId()];

        $cap_dis[] = ['status','in',[2,3]];

        $this->channel_info = $this->model->dataInfo($cap_dis);

        if(empty($this->channel_name)){

            $this->channel_name = getConfigSetting($this->_uniacid,'channel_menu_name');
        }

        if(empty($this->channel_info)){

            $this->errorMsg('你还不是'.$this->channel_name);
        }

    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-08 11:39
     * @功能说明:技师首页
     */
    public function index(){

        $this->order_model->coachBalanceArr($this->_uniacid);

        $input = $this->_param;

        $data  = $this->channel_info;

        $order_data = $this->order_model->channelData($this->channel_info['id'],$input);

        $data = array_merge($order_data,$data);

        $data['total_cash'] = $data['all_cash'];
        //税点
        $data['tax_point']  = getConfigSetting($this->_uniacid,'tax_point');

        if(!empty($data['salesman_id'])){

            $salesman_model = new Salesman();

            $data['salesman_name'] = $salesman_model->where(['id'=>$data['salesman_id']])->where('status','in',[2])->value('user_name');
        }

        if(!empty($data['admin_id'])){

            $admin_model = new \app\massage\model\Admin();

            $data['agent_name'] = $admin_model->where(['id'=>$data['admin_id'],'status'=>1])->value('agent_name');
        }

        $data['balance'] = $data['balance']>=0?$data['balance']:getConfigSetting($this->_uniacid,'channel_balance');

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-08-30 14:18
     * @功能说明:渠道码
     */
    public function channelQr(){

        $input = $this->_param;

        $type = getConfigSetting($this->_uniacid,'wechat_qr_type');

        $key = 'channel_qrssssss'.$this->channel_info['id'].'-'.$this->is_app.'-'.$type;

        $qr  = getCache($key,99999999);

        if(empty($qr)){
            //小程序
            if($this->is_app==0){

                //$input['page'] = 'pages/service';
                $input['page'] = 'user/pages/gzh';

                $input['channel_id'] = $this->channel_info['id'];
                //获取二维码
                $qr = $this->user_model->orderQr($input,$this->_uniacid);



            }else{

               // $page = 'https://'.$_SERVER['HTTP_HOST'].'/h5/#/pages/service?channel_id='.$this->channel_info['id'];

                if($type==0){

                    $page = 'https://'.$_SERVER['HTTP_HOST'].'/h5/#/user/pages/gzh?channel_id='.$this->channel_info['id'];

                    $qr = base64ToPng(getCode($this->_uniacid,$page));

                }else{

                    $core = new WxSetting($this->_uniacid);

                    $qr   = $core->qrCode($this->channel_info['id'].'_channel');
                }
            }

            setCache($key,$qr,8640000,99999999);
        }

        $qr = !empty($qr)?$qr:'https://'.$_SERVER['HTTP_HOST'].'/favicon.ico';

        return $this->success($qr);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-07-31 14:59
     * @功能说明:渠道码列表
     */
    public function channelQrList(){

        $qr_model = new ChannelQr();

        $dis = [

            'status' => 1,

            'channel_id' => $this->channel_info['id']
        ];

        $data = $qr_model->dataList($dis,10);

        return $this->success($data);

    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-07-31 14:59
     * @功能说明:渠道码列表
     */
    public function channelQrSelect(){

        $qr_model = new ChannelQr();

        $dis = [

            'status' => 1,

            'channel_id' => $this->channel_info['id']
        ];

        $data = $qr_model->where($dis)->field('id,title')->order('id desc')->select()->toArray();

        return $this->success($data);

    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-08-30 14:34
     * @功能说明:订单列表
     */
    public function orderList(){

        $input = $this->_param;

        $dis[] = ['a.uniacid','=',$this->_uniacid];

        $dis[] = ['a.pay_type','>',1];

        $dis[] = ['a.channel_id','=',$this->channel_info['id']];

        if(!empty($input['start_time'])&&!empty($input['end_time'])){

            $dis[] = ['a.create_time','between',"{$input['start_time']},{$input['end_time']}"];
        }

        if(isset($input['qr_id'])){

            $dis[] = ['a.channel_qr_id','=',$input['qr_id']];
        }

        $where = [];

        if(!empty($input['name'])){

            $where[] = ['b.goods_name','like','%'.$input['name'].'%'];

            $where[] = ['a.order_code','like','%'.$input['name'].'%'];
        }

        $data = $this->order_model->channelDataList($dis,$where);

        if(!empty($data['data'])){

            $refund_model = new RefundOrder();

            $share_model = new CommShare();

            foreach ($data['data'] as &$v){

                $v['true_service_price'] += $v['material_price'];

                $v['true_service_price'] = round($v['true_service_price'],2);

                $v['refund_price'] = $refund_model->where(['order_id'=>$v['id'],'status'=>2])->sum('service_price');

                $v['point_cash']  = $share_model->where(['comm_id'=>$v['comm_id'],'cash_type'=>1])->sum('share_cash');
            }
        }

        return $this->success($data);
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-24 13:33
     * @功能说明:渠道商申请提现
     */
    public function applyWallet(){

        $input = $this->_input;

        $key = 'channel_wallet' . $this->getUserId();
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

        $channel_info = $this->model->dataInfo(['id'=>$this->channel_info['id']]);

        if ($input['apply_price'] > $channel_info['cash']) {

            decCache($key,1, $this->_uniacid);

            $this->errorMsg('余额不足');
        }
        //获取税点
        $tax_point = getConfigSetting($this->_uniacid,'tax_point');

        $balance = 100 - $tax_point;

        Db::startTrans();

        $insert = [

            'uniacid' => $this->_uniacid,

            'user_id' => $this->getUserId(),

            'coach_id' => $this->channel_info['id'],

            'admin_id' => $this->channel_info['admin_id'],

            'total_price' => $input['apply_price'],

            'balance' => $balance,

            'apply_price' => round($input['apply_price'] * $balance / 100, 2),

            'service_price' => round( $input['apply_price'] * $tax_point / 100, 2),

            'code' => orderCode(),

            'tax_point' => $tax_point,

            'text' => $input['text'],

            'type' => 5,

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

        $water_model = new ChannelWater();

        $res = $water_model->updateCash($this->_uniacid,$this->channel_info['id'],$input['apply_price'],2,$id,0,2);

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

            'coach_id' => $this->channel_info['id']
        ];

        if (!empty($input['status'])) {

            $dis['status'] = $input['status'];
        }

        $dis['type'] = 5;
        //提现记录
        $data = $wallet_model->dataList($dis, 10);

        if (!empty($data['data'])) {

            foreach ($data['data'] as &$v) {

                $v['create_time'] = date('Y-m-d H:i:s', $v['create_time']);
            }
        }
        //累计提现
        $data['extract_total_price'] = $wallet_model->capCash($this->channel_info['id'], 2, 5);

        $data['personal_income_tax_text'] = getConfigSetting($this->_uniacid,'personal_income_tax_text');

        return $this->success($data);


    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-07-25 18:37
     * @功能说明:渠道码详情
     */
    public function channelQrInfo(){

        $input = $this->_param;

        $dis = [

            'id' => $input['id']
        ];

        $qr_model = new ChannelQr();

        $data = $qr_model->dataInfo($dis);

        if(empty($data['qr_img'])){

            $img = $qr_model->channelQrImg($data);

            $qr_model->dataUpdate($dis,['qr_img'=>$img]);

            $data = $qr_model->dataInfo($dis);

        }

        return $this->success($data);

    }



}
