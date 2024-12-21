<?php
declare(strict_types=1);

namespace longbingcore\wxcore;

use AopCertClient;
use app\adapay\info\PermissionAdapay;
use app\adapay\model\Config;
use app\adapay\model\ErrLog;
use app\adapay\model\PayRecord;
use app\ApiRest;
use app\ApiRest2;
use Exception;
use think\App;
use think\facade\Db;

class PayModel extends ApiRest {

   // static protected $uniacid;

    protected $pay_config_data;

    public function __construct($pay_config)
    {
        $this->pay_config_data = $pay_config;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-08-17 14:36
     * @功能说明:
     */
    public function findOrder(){

        $pay_config = $this->payConfig();

        require_once  EXTEND_PATH.'alipay/aop/AopClient.php';

        require_once  EXTEND_PATH.'alipay/aop/request/AlipayTradeQueryRequest.php';

        $aop = new \AopClient ();

        $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
       // $aop->gatewayUrl = 'https://openapi.alipaydev.com/gateway.do';

        $aop->appId = $pay_config[ 'payment' ][ 'ali_appid' ];

        $aop->rsaPrivateKey = $pay_config['payment']['ali_privatekey'];

        $aop->alipayrsaPublicKey=$pay_config['payment']['ali_publickey'];
        $aop->apiVersion = '1.0';
        $aop->signType = 'RSA2';
        $aop->postCharset='UTF-8';
        $aop->format='json';
        $object = new \stdClass();
        $object->out_trade_no = '20150320010101001';
//$object->trade_no = '2014112611001004680073956707';
        $json = json_encode($object);
        $request = new \AlipayTradeQueryRequest();
        $request->setBizContent($json);

        $result = $aop->execute ( $request);

        return $result;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-12 15:33
     * @功能说明:分账支付
     */
    public function adaPay($uniacid,$totalprice,$attach){

        $uniacid = intval($uniacid);

        $p = new PermissionAdapay($uniacid);

        if($p->pAuth()==false){

            return false;
        }

        $adapay_config_model = new Config();

        $adapay_config = $adapay_config_model->dataInfo(['uniacid'=>$uniacid]);
        //分账支付
        if($adapay_config['status']==1){

            $adapay = new \longbingcore\wxcore\Adapay($uniacid);

            $adapay_code = orderCode();

            $pay_mode = $adapay_config['pay_mode']==1?'delay':'';

            $res = $adapay->createAliPay($adapay_code,$totalprice,'alipay_wap',$pay_mode);

            if($res['status']=='succeeded'&&isset($res['expend']['pay_info'])){

                $pay_record = new PayRecord();

                $dis = [

                    'uniacid'    => $uniacid,

                    'order_code' => $attach['out_trade_no'],

                    'type'       => $attach['type']
                ];

                $pay_record->dataUpdate($dis,['status'=>-1]);

                $insert = [

                    'uniacid'    => $uniacid,

                    'order_code' => $attach['out_trade_no'],

                    'adapay_code'=> $adapay_code,

                    'adapay_id'  => $res['id'],

                    'pay_price'  => $totalprice,

                    'true_price' => $totalprice,

                    'type'       => $attach['type'],

                    'order_id'   => $attach['order_id'],

                    'pay_mode'   => $adapay_config['pay_mode'],

                    'status'     => 0
                ];

                $pay_record->dataAdd($insert);

//                $arr = json_decode($res['expend']['pay_info'],true);
//
//                $arr['adapay'] = 1;

                $list_cache = [

                    'order_code' => $res['id'],

                    'type' => $attach['type'],

                    'pay_model' => 'adapay'
                ];

                pushCache($attach['openid'].'payresult',$list_cache,$uniacid);

                return $res;
            }else{

                $errlog_model = new ErrLog();

                $errinsert = [

                    'uniacid' => $uniacid,

                    'text'    => serialize($res),

                    'type'    => 'massage_pay',

                    'order_id'=> $attach['order_id']
                ];
                $errlog_model->dataAdd($errinsert);
            }
        }

        return false;

    }
    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-08-10 16:36
     * @功能说明:支付宝支付
     */
    public function aliPay($order_code,$price,$subject,$type=1,$attach=[]){

    //    $this->request->header();

      //  if(!empty($attach)&&$this->is_app==2){
            //分账
            $res = $this->adaPay($attach['uniacid'],$price,$attach);

            if($res!=false){

                return $res;
            }
      //  }

        require_once  EXTEND_PATH.'alipay/aop/AopClient.php';

        require_once  EXTEND_PATH.'alipay/aop/request/AlipayTradeAppPayRequest.php';
        require_once  EXTEND_PATH.'alipay/aop/request/AlipayTradeWapPayRequest.php';

        $pay_config = $this->pay_config_data;

        $aop = new \AopClient ();

        $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';

        $aop->appId =  $pay_config[ 'payment' ][ 'ali_appid' ];

        $aop->rsaPrivateKey     = $pay_config['payment']['ali_privatekey'];;

        $aop->alipayrsaPublicKey= $pay_config['payment']['ali_publickey'];;

        $aop->apiVersion = '1.0';

        $aop->signType = 'RSA2';

        $aop->postCharset='UTF-8';

        $aop->format='json';

        $object = new \stdClass();

        $object->out_trade_no = $order_code;

        $object->total_amount = $price;

        $object->subject = $subject;

        $object->product_code ='QUICK_MSECURITY_PAY';

        //$object->time_expire = date('Y-m-d H:i:s',time());

        $json = json_encode($object);
        //1app支付 0web
        $request = $pay_config['is_app']==1?new \AlipayTradeAppPayRequest():new \AlipayTradeWapPayRequest();

        if($type==1){

            $request->setNotifyUrl("https://".$_SERVER['HTTP_HOST'].'/index.php/massage/IndexWxPay/aliNotify');
        }elseif($type==2){

            $request->setNotifyUrl("https://".$_SERVER['HTTP_HOST'].'/index.php/massage/IndexWxPay/aliNotifyBalance');
        }elseif($type==3){

            $request->setNotifyUrl("https://".$_SERVER['HTTP_HOST'].'/index.php/massage/IndexWxPay/aliNotifyUp');

        }elseif($type==4){

            $request->setNotifyUrl("https://".$_SERVER['HTTP_HOST'].'/index.php/massage/IndexWxPay/aliNotifyReseller');

        }elseif($type==5){

            $request->setNotifyUrl("https://".$_SERVER['HTTP_HOST'].'/index.php/massage/IndexWxPay/aliAgentRecharge');

        }elseif($type==6){

            $request->setNotifyUrl("https://".$_SERVER['HTTP_HOST'].'/index.php/massage/IndexWxPay/aliMemberdiscount');

        }elseif($type==7){

            $request->setNotifyUrl("https://".$_SERVER['HTTP_HOST'].'/index.php/massage/IndexWxPay/aliBalancediscount');
        }elseif($type==8){

            $request->setNotifyUrl("https://".$_SERVER['HTTP_HOST'].'/index.php/massage/IndexWxPay/aliPartnerOrder');
        }elseif($type==9){

            $request->setNotifyUrl("https://".$_SERVER['HTTP_HOST'].'/index.php/massage/IndexWxPay/aliPartnerOrderJoin');
        }

        $request->setBizContent($json);

        $result = $pay_config['is_app']==1?$aop->sdkExecute ( $request):$aop->pageExecute($request);

        return $result;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-08-11 17:23
     * @功能说明:退款
     */
    public function aliRefund($order_code,$price){

        require_once  EXTEND_PATH.'alipay/aop/AopClient.php';

        require_once  EXTEND_PATH.'alipay/aop/request/AlipayTradeRefundRequest.php';

        $pay_config = $this->pay_config_data;

        $aop = new \AopClient ();

        $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';

        $aop->appId = $pay_config[ 'payment' ][ 'ali_appid' ];

        $aop->rsaPrivateKey     = $pay_config['payment']['ali_privatekey'];;

        $aop->alipayrsaPublicKey= $pay_config['payment']['ali_publickey'];;

        $aop->apiVersion = '1.0';

        $aop->signType = 'RSA2';

        $aop->postCharset='UTF-8';

        $aop->format='json';

        $object = new \stdClass();

        $object->trade_no = $order_code;

        $object->refund_amount = $price;

        $object->out_request_no = 'HZ01RF001';

        $json = json_encode($object);

        $request = new \AlipayTradeRefundRequest();

        $request->setBizContent($json);

        $result = $aop->execute ( $request);

        $result = !empty($result)?object_array($result):[];

        return $result;

    }


    /**
     * @param $alipayUserId
     * @param $nMoney
     * @功能说明:支付宝转账到账户（密钥模式）
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-01-19 15:34
     */
    public function onPaymentByAlipay($alipayUserId, $nMoney,$name=''){

        $pay_config = $this->pay_config_data;

        require_once  EXTEND_PATH.'alipay/aop/AopClient.php';

        require_once  EXTEND_PATH.'alipay/aop/request/AlipayFundTransToaccountTransferRequest.php';

        $aop = new \AopClient ();

        $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
        $aop->appId = $pay_config['payment']['ali_appid'];

     //   dump($pay_config);exit;
        $aop->rsaPrivateKey =  $pay_config['payment']['ali_privatekey'];
        $aop->alipayrsaPublicKey=$pay_config['payment']['ali_publickey'];
        $aop->apiVersion = '1.0';
        $aop->signType = 'RSA2';
        $aop->postCharset='UTF-8';
        $aop->format='json';
        $request = new \AlipayFundTransToaccountTransferRequest ();

        $biz_content = [

            'out_biz_no' => orderCode(),

            'payee_type' => "ALIPAY_LOGONID",

            'payee_account' => $alipayUserId,

            'amount' => $nMoney,

            'payee_real_name' => $name,

        ];

        $request->setBizContent(json_encode($biz_content,256));

        $result = $aop->execute($request);

        $result = !empty($result)?object_array($result):[];

         return $result;

    }










}