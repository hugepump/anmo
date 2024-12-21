<?php
namespace app\heepay\controller;
use AdaPaySdk\Member;
use app\adapay\model\AdminPay;
use app\adapay\model\Bank;
use app\adapay\model\PayRecord;
use app\AdminRest;
use app\ApiRest;
use app\heepay\model\Config;
use app\heepay\model\RechargeRecord;
use app\heepay\model\RecordList;
use app\massage\model\Order;
use app\massage\model\OrderPrice;
use app\massage\model\RefundOrder;
use app\massage\model\UpRefundOrder;
use app\massage\model\Wallet;
use app\memberdiscount\model\OrderList;
use app\mobilenode\model\RechargeList;
use app\virtual\model\PlayRecord;
use app\virtual\model\Record;
use longbingcore\wxcore\PayNotify;
use think\App;
use think\facade\Db;
use WxPayApi;


class CallBack  extends ApiRest
{

    protected $app;

    public function __construct ( App $app )
    {
        $this->app = $app;
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-12-08 15:33
     * @功能说明:企业审核回调
     */
    public function companyCallback(){

        $this->request = $this->app->request;

        $inputs = json_decode($this->request->getInput(), true);

        if(empty($inputs)){

            $inputs = $_POST;
        }

        foreach ($inputs as $k=>$value){

            $input[$k] = iconv("gbk","utf-8",urldecode($value));
        }

        $insert = [

            'uniacid' => $this->_uniacid,

            'text'    => serialize($input),

            'type'    => 'company',

        ];

        $callback_model = new \app\heepay\model\Callback();

        $callback_model->dataAdd($insert);

        $member_model = new \app\heepay\model\Member();

        $dis = [

            'apply_no' => $input['apply_no'],
        ];

        $update = [] ;

        if(isset($input['notify_type'])){

            $audit_status = in_array($input['notify_type'],[1,2])?1:$input['notify_type'];

            $update = [

                'audit_status' => $audit_status
            ];
        }

        if(isset($input['reject_reason'])){

            $update['reject_reason'] = $input['reject_reason'];
        }

        $member_model->dataUpdate($dis,$update);

        $res = ['code'=>10000];

        echo json_encode($res);exit;

    }

//a:8:{s:8:"agent_id";s:7:"2208181";s:10:"hy_bill_no";s:16:"H2405156731693A3";s:13:"agent_bill_id";s:30:"202405151724537713635458322235";s:20:"agent_refund_bill_no";s:30:"202405151728119892882481962333";s:10:"refund_amt";s:4:"0.01";s:13:"refund_status";s:7:"SUCCESS";s:12:"hy_deal_time";s:14:"20240515172820";s:4:"sign";s:32:"cdba34605271b333c6bad4a8c381ec58";}

//a:8:{s:8:"agent_id";s:7:"2208181";s:10:"hy_bill_no";s:16:"H2405156736973AJ";s:13:"agent_bill_id";s:30:"202405151717299811332714601924";s:20:"agent_refund_bill_no";s:30:"202405151720375676281750528567";s:10:"refund_amt";s:4:"0.03";s:13:"refund_status";s:7:"SUCCESS";s:12:"hy_deal_time";s:14:"20240515172043";s:4:"sign";s:32:"a3fd0d9dc1d3d71794626d458d8e3535";}
    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-12-08 15:33
     * @功能说明:退款回调
     */
    public function refundCallback(){

        $this->request = $this->app->request;

        $inputs = $this->request->param();

        if(empty($inputs)){

            $inputs = $_GET;
        }

        foreach ($inputs as $k=>$value){

            $input[$k] = iconv("gbk","utf-8",urldecode($value));
        }

        $insert = [

            'uniacid' => $this->_uniacid,

            'text'    => @serialize($input),

            'type'    => 'refund',

            'heepay_order_code' => $input['agent_refund_bill_no']
        ];

        $callback_model = new \app\heepay\model\Callback();

        $callback_model->dataAdd($insert);

        $refund_model = new RefundOrder();

        $log_model = new OrderPrice();

        $record_model = new RecordList();

        $record = $record_model->dataInfo(['heepay_order_code'=>$_GET['agent_refund_bill_no']]);

        $agent_refund_bill_no = !empty($_GET['hy_bill_no'])?$_GET['hy_bill_no']:'';

        if(!empty($record)){

            if(strtolower($_GET['refund_status'])=='success') {

                $record_model->dataUpdate(['id' => $record['id']], ['status' => 2]);

                $cash = $_GET['refund_amt'];
                //说明是后台退款
                if (!empty($record['order_id'])) {
                    //已经成功退款的金额
                    $refund_model->where(['id' => $record['order_id']])->update(['have_price' => Db::Raw("have_price+$cash"),'out_refund_no'=>$agent_refund_bill_no]);

                    $refund_order = $refund_model->dataInfo(['id' => $record['order_id']]);
                    //说明退完了
                    if (round($refund_order['have_price'],2) >= round($refund_order['refund_price'],2)&&$refund_order['status']==4) {
                        //执行成功退款流程
                        $refund_model->passOrderData($record['order_id'], $refund_order['have_price'], 2, $refund_order['check_user'], 0, 2,0,$refund_order['is_admin']);
                    }
                }
            }
            //失败 修改状态以及原因
            if(strtolower($_GET['refund_status'])=='fail'){
                //修改退款订单的状态
                $refund_model->dataUpdate(['id'=>$record['order_id']],['status'=>5]);

                $record_model->where(['id'=>$record['pay_record_id']])->update(['true_price'=>Db::raw("true_price+$cash")]);

                $log_model->where(['id'=>$record['log_id']])->update(['can_refund_price'=>Db::raw("can_refund_price+$cash")]);
            }
        }

        echo 'ok';
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-02-19 15:32
     * @功能说明:升级订单的退款回调
     */
    public function upOrderRefundCallback(){

        $insert = [

            'uniacid' => $this->_uniacid,

            'text'    => serialize($_GET),

            'type'    => 'up_refund',

            'heepay_order_code' => $_GET['agent_refund_bill_no']
        ];

        $callback_model = new \app\heepay\model\Callback();

        $callback_model->dataAdd($insert);

        $pay_model    = new RecordList();

        $refund_model = new UpRefundOrder();

        $record = $pay_model->dataInfo(['heepay_order_code'=>$_GET['agent_refund_bill_no']]);

        $agent_refund_bill_no = !empty($_GET['hy_bill_no'])?$_GET['hy_bill_no']:'';

        $cash = $_GET['refund_amt'];

        if(strtolower($_GET['refund_status'])=='success') {

            $pay_model->dataUpdate(['adapay_id' => $record['id']], ['status' => 2]);

            $refund_model->dataUpdate(['id'=>$record['order_id']],['status'=>2,'have_price'=>$cash,'transaction_id'=>$agent_refund_bill_no]);
        }
        //失败 修改状态以及原因
        if(strtolower($_GET['refund_status'])=='fail'){
            //修改退款订单的状态
            $refund_model->dataUpdate(['id'=>$record['order_id']],['status'=>5]);

            $pay_model->where(['id'=>$record['pay_record_id']])->update(['true_price'=>Db::raw("true_price+$cash")]);
        }

        return true;
    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-12 16:16
     * @功能说明:支付回调
     */
    public function payCallback(){

        $config_model = new Config();

        $config = $config_model->dataInfo(['uniacid'=>$this->_uniacid]);

        $callback_model = new \app\heepay\model\Callback();

        $this->request = $this->app->request;

        $_GET['remark'] = iconv("gbk","utf-8",urldecode($_GET['remark']));;

        $insert = [

            'uniacid' => $this->_uniacid,

            'text'    => serialize($_GET),

            'type'    => $_GET['remark'],

            'heepay_order_code' => $_GET['agent_bill_id']
        ];

        $callback_model->dataAdd($insert);
        $result=$_GET['result'];
        $agent_id=$_GET['agent_id'];
        $jnet_bill_no=$_GET['jnet_bill_no'];
        $agent_bill_id=$_GET['agent_bill_id'];
        $pay_type=$_GET['pay_type'];
        $pay_amt=$_GET['pay_amt'];
        $remark=$_GET['remark'];
        $returnSign=$_GET['sign'];
        $key = $config['pay_key'];
        $signStr='';
        $signStr  = $signStr . 'result=' . $result;
        $signStr  = $signStr . '&agent_id=' . $agent_id;
        $signStr  = $signStr . '&jnet_bill_no=' . $jnet_bill_no;
        $signStr  = $signStr . '&agent_bill_id=' . $agent_bill_id;
        $signStr  = $signStr . '&pay_type=' . $pay_type;
        $signStr  = $signStr . '&pay_amt=' . $pay_amt;
        $signStr  = $signStr .  '&remark=' . $remark;
        $signStr = $signStr . '&key=' . $key;
        $Verify_array = $signStr;
        $sign=$returnSign;
        $Verify_Sign=md5($Verify_array);

        $record_model = new RecordList();

        $record_model->dataUpdate(['heepay_order_code'=>$agent_bill_id],['status'=>2]);

        $agent_bill_id = $record_model->where(['heepay_order_code'=>$agent_bill_id])->value('order_code');

        if($sign==$Verify_Sign){   //比较MD5签名结果 是否相等 确定交易是否成功  成功返回ok 否则返回error

            if( $_GET['remark']=='Balance'){

                $order_model = new \app\massage\model\BalanceOrder();

                $order_model->orderResult($agent_bill_id,$jnet_bill_no);

            }elseif($_GET['remark']=='Massage'){

                $order_model = new \app\massage\model\Order();

                $order_model->orderResult($agent_bill_id,$jnet_bill_no);

            }elseif($_GET['remark']=='MassageUp'){

                $order_model = new \app\massage\model\UpOrderList();

                $order_model->orderResult($agent_bill_id,$jnet_bill_no);

            }elseif($_GET['remark']=='Adapay'){

                $order_model = new AdminPay();

                $order_model->orderResult($agent_bill_id,$jnet_bill_no);

            }elseif($_GET['remark']=='ResellerPay'){

                $order_model = new \app\payreseller\model\Order();

                $order_model->orderResult($agent_bill_id,$jnet_bill_no);

            }elseif($_GET['remark']=='AgentRecharge'){

                $order_model = new RechargeList();

                $order_model->orderResult($agent_bill_id,$jnet_bill_no);

            }elseif($_GET['remark']=='Memberdiscount'){

                $order_model = new OrderList();

                $order_model->orderResult($agent_bill_id,$jnet_bill_no);

            }elseif($_GET['remark']=='Balancediscount'){

                $order_model = new \app\balancediscount\model\OrderList();

                $order_model->orderResult($agent_bill_id,$jnet_bill_no);
            } elseif ($_GET['remark'] == 'PartnerOrder') {

                $order_model = new \app\partner\model\PartnerOrder();

                $order_model->orderResult($agent_bill_id, $jnet_bill_no);
            } elseif ($_GET['remark'] == 'PartnerOrderJoin') {

                $order_model = new \app\partner\model\PartnerOrderJoin();

                $order_model->orderResult($agent_bill_id, $jnet_bill_no);
            }

            echo 'ok';
        }else{
            echo 'error';
        }
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-18 18:35
     * @功能说明:打款回调
     */
    public function walletCallback(){

        $this->request = $this->app->request;

        $inputs = json_decode($this->request->getInput(), true);

        if(empty($inputs)){

            $inputs = $_POST;
        }

        foreach ($inputs as $k=>$value){

            $input[$k] = iconv("gbk","utf-8",urldecode($value));
        }

        $insert = [

            'uniacid' => $this->_uniacid,

            'text'    => serialize($input),

            'type'    => 'wallet',

            'heepay_order_code' => $input['out_trade_no']
        ];

        $callback_model = new \app\heepay\model\Callback();

        $callback_model->dataAdd($insert);

        $wallet_model  = new Wallet();

        $dis = [

            'payment_no' => $input['out_trade_no']
        ];

        $record = $wallet_model->dataInfo($dis);
        //回调成功
        if($input['bill_status']=='1'&&!empty($record)){

            $wallet_model->dataUpdate(['id'=>$record['id']],['status'=>2]);
        }
        //失败 修改状态以及原因
        if($input['bill_status']=='-1'&&!empty($record)){

            $wallet_model->dataUpdate(['id'=>$record['id']],['status'=>5]);
        }

        echo 'ok';exit;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-04-30 10:33
     * @功能说明:充值转账回调
     */
    public function rechargeCallBack(){

        $this->request = $this->app->request;

        $inputs = $this->request->param();

        if(empty($inputs)){

            $inputs = $_GET;
        }

        foreach ($inputs as $k=>$value){

            $input[$k] = iconv("gbk","utf-8",urldecode($value));
        }

        $insert = [

            'uniacid' => $this->_uniacid,

            'text'    => serialize($input),

            'type'    => 'recharge',

            'heepay_order_code' => $input['batch_no']
        ];

        $callback_model = new \app\heepay\model\Callback();

        $callback_model->dataAdd($insert);

        $record_model = new RechargeRecord();

        $record = $record_model->dataInfo(['agent_bill_id'=>$input['batch_no']]);

        if(!empty($record)&&$input['status']==1){

            $record_model->dataUpdate(['id'=>$record['id']],['status'=>2]);
        }

        if(!empty($record)&&$input['status']==-1){

            $record_model->dataUpdate(['id'=>$record['id']],['status'=>3]);
        }

        echo 'ok';exit;
    }




}
