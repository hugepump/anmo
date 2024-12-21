<?php
namespace app\adapay\controller;
use AdaPaySdk\Member;
use app\adapay\model\AdminPay;
use app\adapay\model\Bank;
use app\adapay\model\PayRecord;
use app\AdminRest;
use app\ApiRest;
use app\massage\model\Order;
use app\massage\model\OrderPrice;
use app\massage\model\RefundOrder;
use app\massage\model\UpRefundOrder;
use app\massage\model\Wallet;
use app\memberdiscount\model\OrderList;
use app\mobilenode\model\RechargeList;
use app\partner\model\PartnerOrder;
use app\partner\model\PartnerOrderJoin;
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
     * @功能说明:退款回调
     */
    public function companyCallback(){

        $this->request = $this->app->request;

        $inputs = json_decode($this->request->getInput(), true);

        if(empty($inputs)){

            $inputs = $_POST;
        }
        //添加回调记录
        $callbak_model = new \app\adapay\model\Callback();

        $data = json_decode($inputs['data'],true);

        $insert = [

            'uniacid' => 666,

            'adapay_id' => $data['member_id'],

            'status' => $inputs['type'],

            'type'   => 'company',

            'text'   => serialize($inputs)
        ];

        $callbak_model->dataAdd($insert);

        $member_model = new \app\adapay\model\Member();

        $find = $member_model->dataInfo(['status'=>0,'member_id'=>$data['member_id']]);

        if(!empty($find)){
            //成功
            if(in_array($data['audit_state'],['D','E'])){

                $member_model->dataUpdate(['id'=>$find['id']],['status'=>1]);

//                $bank_model = new Bank();
//
//                $bank_model->dataUpdate(['member_id'=>$find['member_id']],['settle_account_id'=>$inputs['id']]);
            }else{

                $member_model->dataUpdate(['id'=>$find['id']],['status'=>2,'failure_reason'=>$data['audit_desc']]);
            }
        }

        $res = ['code'=>0,'msg'=>'成功'];

        echo json_encode($res);exit;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-12-08 15:33
     * @功能说明:退款回调
     */
    public function refundCallback(){

        $this->request = $this->app->request;

        $inputs = json_decode($this->request->getInput(), true);

        if(empty($inputs)){

            $inputs = $_POST;
        }
        //添加回调记录
        $callbak_model = new \app\adapay\model\Callback();

        $data = json_decode($inputs['data'],true);

       // dump($data,$inputs,json_encode(['id'=>'0022120240523191110980639674959356596224','status'=>'succeeded']));exit;
        $dis = [

            'adapay_id' => $data['id'],

            'type'      => 'refund'
        ];

        $find = $callbak_model->dataInfo($dis);

        if(empty($find)){

            $insert = [

                'uniacid' => 666,

                'adapay_id' => $data['id'],

                'status' => $inputs['type'],

                'type'   => 'refund',

                'text'   => serialize($inputs)
            ];

            $callbak_model->dataAdd($insert);
        }else{

//            $res = ['code'=>0,'msg'=>'成功'];
//
//            echo json_encode($res);exit;
        }

        $pay_model = new PayRecord();

        $refund_model = new RefundOrder();

        $log_model = new OrderPrice();

        $record = $pay_model->dataInfo(['adapay_id' => $data['id'], 'type' => 'refund']);

        $cash = $record['pay_price'];

        if(!empty($record)&&$record['status']!=2){

            if($data['status']=='succeeded') {

                $pay_model->dataUpdate(['adapay_id' => $data['id']], ['status' => 2]);
                //说明是后台退款
                if (!empty($record['order_id'])) {
                    //已经成功退款的金额
                    $refund_model->where(['id' => $record['order_id']])->update(['have_price' => Db::Raw("have_price+$cash"),'out_refund_no'=>$data['id']]);

                    $refund_order = $refund_model->dataInfo(['id' => $record['order_id']]);
                    //说明退完了
                    if (round($refund_order['have_price'],2) >= round($refund_order['refund_price'],2)&&$refund_order['status']==4) {
                        //执行成功退款流程
                        $refund_model->passOrderData($record['order_id'], $refund_order['have_price'], 2, $refund_order['check_user'], 0, 2,0,$refund_order['is_admin']);
                    }
                }
            }
            //失败 修改状态以及原因
            if($data['status']=='failed'){
                //修改退款订单的状态
                $refund_model->dataUpdate(['id'=>$record['order_id']],['status'=>5,'failure_reason'=>$data['error_msg']]);

                $pay_model->where(['id'=>$record['pay_record_id']])->update(['true_price'=>Db::raw("true_price+$cash")]);

                $log_model->where(['id'=>$record['log_id']])->update(['can_refund_price'=>Db::raw("can_refund_price+$cash")]);

            }
        }

        $res = ['code'=>0,'msg'=>'成功'];

        echo json_encode($res);exit;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-02-19 15:32
     * @功能说明:升级订单的退款回调
     */
    public function upOrderRefundCallback(){

        $this->request = $this->app->request;

        $inputs = json_decode($this->request->getInput(), true);

        if(empty($inputs)){

            $inputs = $_POST;
        }

        $data = json_decode($inputs['data'],true);

        $pay_model = new PayRecord();

        $refund_model = new UpRefundOrder();

        $record = $pay_model->dataInfo(['adapay_id' => $data['id'], 'type' => 'up_refund']);

        $cash = $record['pay_price'];

        if($data['status']=='succeeded') {

            $pay_model->dataUpdate(['adapay_id' => $data['id']], ['status' => 2]);

            $refund_model->dataUpdate(['id'=>$record['order_id']],['status'=>2,'have_price'=>$cash,'transaction_id'=>$data['id']]);
        }
        //失败 修改状态以及原因
        if($data['status']=='failed'){
            //修改退款订单的状态
            $refund_model->dataUpdate(['id'=>$record['order_id']],['status'=>5,'failure_reason'=>$data['error_msg']]);

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
//
        $this->request = $this->app->request;

        $inputs = json_decode($this->request->getInput(), true);

        if(empty($inputs)){

            $inputs = $_POST;
        }
        //添加回调记录
        $callbak_model = new \app\adapay\model\Callback();

        $data = json_decode($inputs['data'],true);
        //回调成功
        if($data['status']=='succeeded'){

            $pay_record_model = new PayRecord();

            $dis = [

                'adapay_id' => $data['id']
            ];

            $record = $pay_record_model->dataInfo($dis);

            $pay_record_model->dataUpdate($dis,['status'=>1]);

            if(!empty($record)){

                if( $record['type']=='Balance'){

                    $order_model = new \app\massage\model\BalanceOrder();

                    $order_model->orderResult($record['order_code'],$data['id']);

                }elseif($record['type']=='Massage'){

                    $order_model = new \app\massage\model\Order();

                    $order_model->orderResult($record['order_code'],$data['id']);

                }elseif($record['type']=='MassageUp'){

                    $order_model = new \app\massage\model\UpOrderList();

                    $order_model->orderResult($record['order_code'],$data['id']);

                }elseif($record['type']=='Adapay'){

                    $order_model = new AdminPay();

                    $order_model->orderResult($record['order_code'],$data['id']);

                }elseif($record['type']=='ResellerPay'){

                    $order_model = new \app\payreseller\model\Order();

                    $order_model->orderResult($record['order_code'],$data['id']);

                }elseif($record['type']=='AgentRecharge'){

                    $order_model = new RechargeList();

                    $order_model->orderResult($record['order_code'],$data['id']);

                }elseif($record['type']=='Memberdiscount'){

                    $order_model = new OrderList();

                    $order_model->orderResult($record['order_code'],$data['id']);

                }elseif($record['type']=='Balancediscount'){

                    $order_model = new \app\balancediscount\model\OrderList();

                    $order_model->orderResult($record['order_code'],$data['id']);
                } elseif ($record['type'] == 'PartnerOrder') {

                    $notify = [
                        'order_code' => $record['order_code'],
                        'transaction_id' => $data['id'],
                    ];
                    PartnerOrder::notify($notify);
                } elseif ($record['type'] == 'PartnerOrderJoin') {

                    $notify = [
                        'order_code' => $record['order_code'],
                        'transaction_id' => $data['id'],
                    ];
                    PartnerOrderJoin::notify($notify);
                }
            }
        }

        $dis = [

            'adapay_id' => $data['id']
        ];

        $find = $callbak_model->dataInfo($dis);
        //添加回调记录
        if(empty($find)){

            $insert = [

                'uniacid' => 666,

                'adapay_id' => $data['id'],

                'status' => $inputs['type'],

                'type'   => 'pay',

                'text'   => serialize($inputs),

                'out_trans_id' => !empty($data['out_trans_id'])?$data['out_trans_id']:'',

                'party_order_id' => !empty($data['party_order_id'])?$data['party_order_id']:'',
            ];

            $callbak_model->dataAdd($insert);
        }

        $res = ['code'=>0,'msg'=>'成功'];

        echo json_encode($res);exit;

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
        //添加回调记录
        $callbak_model = new \app\adapay\model\Callback();

        $data = json_decode($inputs['data'],true);

        $wallet_model  = new Wallet();

        $dis = [

            'payment_no' => $data['id']
        ];

        $record = $wallet_model->dataInfo($dis);
        //回调成功
        if($data['status']=='succeeded'&&!empty($record)){

            $wallet_model->dataUpdate(['id'=>$record['id']],['status'=>2]);
        }
        //失败 修改状态以及原因
        if($data['status']=='failed'&&!empty($record)){

            $wallet_model->dataUpdate(['id'=>$record['id']],['status'=>5,'failure_reason'=>$data['error_msg']]);
        }

        $insert = [

            'uniacid' => 666,

            'adapay_id' => $data['id'],

            'status' => $inputs['type'],

            'type'   => 'wallet',

            'text'   => serialize($inputs)
        ];

        $callbak_model->dataAdd($insert);

        $res = ['code'=>0,'msg'=>'成功'];

        echo json_encode($res);exit;

    }



}
