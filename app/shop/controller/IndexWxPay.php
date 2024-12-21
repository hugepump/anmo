<?php
namespace app\shop\controller;
use app\adapay\info\PermissionAdapay;
use app\adapay\model\Config;
use app\adapay\model\ErrLog;
use app\adapay\model\PayRecord;
use app\AdminRest;
use app\ApiRest;
use app\heepay\info\PermissionHeepay;
use app\heepay\model\RecordList;
use app\massage\model\BalanceOrder;
use app\massage\model\Order;
use app\massage\model\UpOrderList;
use longbingcore\heepay\HeePay;
use longbingcore\wxcore\Adapay;
use longbingcore\wxcore\PayNotify;
use think\App;
use think\facade\Db;
use WxPayApi;


class IndexWxPay extends ApiRest
{

    protected $app;

    public function __construct ( App $app )
    {
        $this->app = $app;
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-12 15:33
     * @功能说明:分账支付
     */
    public function adaPay($uniacid,$totalprice,$openid,$attach){

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

            $res = $adapay->createPay($adapay_code,$totalprice,$openid,'wx_pub',$pay_mode);

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

                $arr = json_decode($res['expend']['pay_info'],true);

                $arr['adapay'] = 1;

                $list_cache = [

                    'order_code' => $res['id'],

                    'type' => $attach['type'],

                    'pay_model' => 'adapay'
                ];

                pushCache($openid.'payresult',$list_cache,$uniacid);

                return $arr;
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
     * @param $uniacid
     * @param $totalprice
     * @param $attach
     * @功能说明:汇付宝支付
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-04-23 17:31
     */
    public function heepay($uniacid,$totalprice,$attach,$openid){

        $p = new PermissionHeepay($uniacid);

        if($p->pAuth()==false){

            return false;
        }

        $config_model = new \app\heepay\model\Config();

        $status = $config_model->where(['uniacid'=>$uniacid])->value('status');

        if($status!=1){

            return false;
        }

        $heepay_model = new HeePay($uniacid);

        $adapay_code   = orderCode();

        $res = $heepay_model->pay($adapay_code,$totalprice,$openid,$attach['type']);

        $record_model = new RecordList();

        $insert = [

            'uniacid'    => $uniacid,

            'order_code' => $attach['out_trade_no'],

            'heepay_order_code'=> $adapay_code,

            'cash'  => $totalprice,

            'true_price'  => $totalprice,

            'type'       => $attach['type'],

            'order_id'   => $attach['order_id'],

            'status'     => 1
        ];

        $record_model->dataAdd($insert);

        $res['heepay'] = 1;

        return $res;
    }


    /**
     * @param $paymentApp
     * @param $openid
     * @param $uniacid
     * @param $body
     * @param $attach
     * @param $totalprice
     * @throws \WxPayException
     * 支付
     */
    public function createWeixinPay($paymentApp , $openid , $uniacid , $body , $attach,$totalprice){
        //分账
        $res = $this->adaPay($uniacid,$totalprice,$openid,$attach);

        if($res!=false){

            return $res;
        }
        //
        $res = $this->heepay($uniacid,$totalprice,$attach,$openid);

        if($res!=false){

            return $res;
        }

        $list_cache = [

            'order_code' => $attach['out_trade_no'],

            'type' => $attach['type'],

            'pay_model' => 'weixin'
        ];

        pushCache($openid.'payresult',$list_cache,$uniacid);

        global $_GPC, $_W;
        $setting['mini_appid']         = $paymentApp['app_id'];
        $setting['mini_appsecrept']    = $paymentApp['secret'];
        $setting['mini_mid']           = $paymentApp['payment']['merchant_id'];
        $setting['mini_apicode']       = $paymentApp['payment']['key'];
        $setting['apiclient_cert']     = $paymentApp['payment']['cert_path'];
        $setting['apiclient_cert_key'] = $paymentApp['payment']['key_path'];
        defined('WX_APPID') or define('WX_APPID', $setting['mini_appid']);
        defined('WX_MCHID') or define('WX_MCHID', $setting['mini_mid']);
        defined('WX_KEY') or define('WX_KEY', $setting['mini_apicode']);
        defined('WX_APPSECRET') or define('WX_APPSECRET', $setting['mini_appsecrept']);
        defined('WX_SSLCERT_PATH') or define('WX_SSLCERT_PATH', $setting['apiclient_cert']);
        defined('WX_SSLKEY_PATH') or define('WX_SSLKEY_PATH', $setting['apiclient_cert_key']);
        defined('WX_CURL_PROXY_HOST') or define('WX_CURL_PROXY_HOST', '0.0.0.0');
        defined('WX_CURL_PROXY_PORT') or  define('WX_CURL_PROXY_PORT', 0);
        defined('WX_REPORT_LEVENL') or define('WX_REPORT_LEVENL', 0);

        require_once PAY_PATH . "/weixinpay/lib/WxPay.Api.php";
        require_once PAY_PATH . "/weixinpay/example/WxPay.JsApiPay.php";

        $tools = new \JsApiPay();
        $input = new \WxPayUnifiedOrder();

        $input->SetBody($body);
        $input->SetAttach(json_encode($attach));
        $input->SetOut_trade_no($attach['out_trade_no']);
        $input->SetTotal_fee($totalprice *100);
        $input->SetTime_start(date("YmdHis"));

        $param_arr=[
            'i' => $uniacid,
            't' => $_GPC['t'],
            'v' => $_GPC['v'],
            'is_app' => $paymentApp['is_app'],
            'n' => APP_MODEL_NAME,

        ];
        $reply_path=json_encode($param_arr);
        //需要判断 是否是微擎的版本
        if(defined('IS_WEIQIN')){
            $path  = "https://" . $_SERVER['HTTP_HOST'] ."/addons/".APP_MODEL_NAME."/core2/app/Common/wexinPay.php?params=".$reply_path;
            $paths = "https://" . $_SERVER['HTTP_HOST'] ."/addons/".APP_MODEL_NAME."/core2/app/Common/wexinPay.php?ck=789";
            file_get_contents($paths);
            $a     = @file_get_contents($paths);
            if($a != 1){
                $this->errorMsg('发起支付失败');
            }
        }else{
            $path  = "https://" . $_SERVER['HTTP_HOST'] ."/wexinPay.php?params=".$reply_path;
            $paths = "https://" . $_SERVER['HTTP_HOST'] ."/wexinPay.php?ck=789";
            //写入日志
            @file_get_contents(DD_PATH);
            $a     = @file_get_contents($paths);
            if($a != 1){
               // $this->errorMsg('发起支付失败');
            }
        }

        $this ->lb_logOutput('BaseApiPath:-----'.$path);

        $input->SetNotify_url($path);

        if($paymentApp['is_app']!=1){

            $input->SetTrade_type("JSAPI");

            $input->SetOpenid($openid);

        }else{

            $input->SetTrade_type("APP");
        }

        $order = \WxPayApi::unifiedOrder($input);

        if(!empty($order['return_code'])&&$order['return_code'] == 'FAIL'){

            $this->errorMsg($order['return_msg']);
        }

        if(!empty($order['result_code'])&&$order['result_code'] == 'FAIL'){

            $this->errorMsg($order['err_code_des']);
        }

        $order['mini_mid'] = $setting['mini_mid'];

        if($paymentApp['is_app']!=1){

            $jsApiParameters = $tools->GetJsApiParameters($order);

            $jsApiParameters = json_decode($jsApiParameters, true) ;

        } else{

            $jsApiParameters = $this->getOrder($order);
        }

        if (!empty($jsApiParameters['return_code']))
             $this->errorMsg( '发起支付失败');

        return $jsApiParameters;
    }


    public function getOrder($order)
    {
        $data = array(
            'appid' => WX_APPID,//appid
            'partnerid' => WX_MCHID,//商户号
            'timestamp' => time(),//时间戳
            'noncestr'  => WxPayApi::getNonceStr(),//随机字符串
            'package'   => 'Sign=WXPay',//预支付交易会话标识
            'prepayid'  => $order['prepay_id'],//预支付回话标志
            //'sign_type'=>'MD5'//加密方式
        );

        $data['paySign'] = $this->makeSign($data);

        return $data;
    }
    /**
     * 生成签名
     * @return 签名，本函数不覆盖sign成员变量，如要设置签名需要调用SetSign方法赋值
     */
    public function makeSign($data)
    {
        // 去空
        $data = array_filter($data);
        //签名步骤一：按字典序排序参数
        ksort($data);
        $string_a = http_build_query($data);
        $string_a = urldecode($string_a);
        //签名步骤二：在string后加入KEY

        $string_sign_temp = $string_a . "&key=" . WX_KEY;
        //签名步骤三：MD5加密
        $sign = md5($string_sign_temp);
        // 签名步骤四：所有字符转为大写
        $result = strtoupper($sign);
        return $result;
    }
    /**
     * @param $data
     * @param int $flag
     * @return void|null
     * 打印数据
     */

    public function lb_logOutput($data,$flag=0) {
        if($flag==0){
            return ;
        }
        //数据类型检测
        if (is_array($data)) {
            $data = json_encode($data);
        }
        $filename = "./".date("Y-m-d").".log";
        $str = date("Y-m-d H:i:s")."   $data"."\r\n";
        file_put_contents($filename, $str, FILE_APPEND|LOCK_EX);
        return null;
    }

    /**
     * 支付回调
     */

    public function returnPay(){

        $this->lb_logOutput("in--mingpianNotify");

        $xmlData = file_get_contents('php://input');

        if(empty($xmlData)){
            $xmlData = 'empty   xmlData';
        }
        $this->lb_logOutput('xmlData in mingpian:-----'.$xmlData);

        $this->lb_logOutput("in-mingpian2");
        global $_GPC;
        $xmlData = file_get_contents('php://input');
        $this->lb_logOutput('in-mingpian-$xmlData:-----'.$xmlData);
        //获取配置
        if(defined( 'IS_WEIQIN' )){

            $uniacid=$_GPC['i'];

            $is_app=$_GPC['is_app'];
        }else{

            $is_app  = $_GET['is_app'];

            $uniacid = $_GET['i'];
        }

        $paymentApp = $this->payConfig($uniacid,$is_app);

        $this->lb_logOutput('in-mingpian-uniacid:-----'.$uniacid);

        $setting['mini_appid']         = $paymentApp['app_id'];
        $setting['mini_appsecrept']    = $paymentApp['secret'];
        $setting['mini_mid']           = $paymentApp['payment']['merchant_id'];
        $setting['mini_apicode']       = $paymentApp['payment']['key'];
        $setting['apiclient_cert']     = $paymentApp['payment']['cert_path'];
        $setting['apiclient_cert_key'] = $paymentApp['payment']['key_path'];

        define('WX_APPID', $setting['mini_appid']);
        define('WX_MCHID', $setting['mini_mid']);
        define('WX_KEY', $setting['mini_apicode']);
        define('WX_APPSECRET', $setting['mini_appsecrept']);
        define('WX_SSLCERT_PATH', $setting['apiclient_cert']);
        define('WX_SSLKEY_PATH', $setting['apiclient_cert_key']);
        define('WX_CURL_PROXY_HOST', '0.0.0.0');
        define('WX_CURL_PROXY_PORT', 0);
        define('WX_REPORT_LEVENL', 0);
        require_once PAY_PATH.'/weixinpay/lib/WxOrderNotify.php';
        $WxPay = new \WxOrderNotify($this->app);
        $WxPay->Handle(false);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-08-19 11:26
     * @功能说明:阿里回调
     */
    public function aliNotify(){

        $data = $_POST;

        if(empty($data)){

            $data = $_GET;
        }

        $pay_config = $this->payConfig();

        require_once  EXTEND_PATH.'alipay/aop/AopClient.php';

        $aop = new \AopClient;

        $aop->alipayrsaPublicKey = $pay_config[ 'payment' ][ 'ali_publickey' ];

        $flag = $aop->rsaCheckV1($data, NULL, "RSA2");

        if(!$flag){

            return false;
        }

        $data['flag'] = $flag;

        if(!empty($data)&&$data['trade_status']=='TRADE_SUCCESS')     //支付状态
        {

            $notify_model = new PayNotify();

            $notify_model->aliNotify($data);
        }

        return true;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-02-23 11:48
     * @功能说明:检查订单是否支付
     */
    public function checkOrder($order_code,$paymentApp){

        $setting['mini_appid']      = $paymentApp['app_id'];
        $setting['mini_appsecrept'] = $paymentApp['secret'];
        $setting['mini_mid']        = $paymentApp['payment']['merchant_id'];
        $setting['mini_apicode']    = $paymentApp['payment']['key'];
        $setting['apiclient_cert']  = $paymentApp['payment']['cert_path'];
        $setting['apiclient_cert_key'] = $paymentApp['payment']['key_path'];

        defined('WX_APPID') or define('WX_APPID', $setting['mini_appid']);
        defined('WX_MCHID') or define('WX_MCHID', $setting['mini_mid']);
        defined('WX_KEY') or define('WX_KEY', $setting['mini_apicode']);
        defined('WX_APPSECRET') or define('WX_APPSECRET', $setting['mini_appsecrept']);
        defined('WX_SSLCERT_PATH') or define('WX_SSLCERT_PATH', $setting['apiclient_cert']);
        defined('WX_SSLKEY_PATH') or define('WX_SSLKEY_PATH', $setting['apiclient_cert_key']);
        defined('WX_CURL_PROXY_HOST') or define('WX_CURL_PROXY_HOST', '0.0.0.0');
        defined('WX_CURL_PROXY_PORT') or define('WX_CURL_PROXY_PORT', 0);
        defined('WX_REPORT_LEVENL') or define('WX_REPORT_LEVENL', 0);
        require_once PAY_PATH . "/weixinpay/lib/WxPay.Api.php";
        require_once PAY_PATH . "/weixinpay/example/WxPay.JsApiPay.php";
        $input = new \WxPayOrderQuery();
        $input->SetOut_trade_no($order_code);
        $result = WxPayApi::orderQuery($input);
        return $result;

    }


    /**
     * @param $user_id
     * @param $type
     * @功能说明:校验订单是否支付，应对突发情况，付款微信未回调
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-11-13 11:26
     */
    public function checkOrderPay($user_id,$paymentApp,$type=1){

        if($type==1){

            $order_model = new Order();

            $order = $order_model->where(['user_id'=>$user_id,'pay_type'=>1])->field('order_code,transaction_id')->order('id desc')->limit(1)->select()->toArray();

        }elseif ($type==2){

            $order_model = new BalanceOrder();

            $order = $order_model->where(['user_id'=>$user_id,'status'=>1])->field('order_code,transaction_id')->order('id desc')->limit(1)->select()->toArray();

        }elseif ($type==3){

            $order_model = new UpOrderList();

            $order = $order_model->where(['user_id'=>$user_id,'pay_type'=>1])->field('order_code,transaction_id')->order('id desc')->limit(1)->select()->toArray();
        }

        if(!empty($order)){

            foreach ($order as $value){

                $result = $this->checkOrder($value['order_code'],$paymentApp);

                if(!empty($result['result_code'])&&$result['result_code']=='SUCCESS'&&!empty($result['return_code'])&&$result['return_code']=='SUCCESS'&&!empty($result['trade_state'])&&$result['trade_state']=='SUCCESS'){

                    $order_model->orderResult($result['out_trade_no'],$result['transaction_id']);
                }
            }
        }

        return true;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-11-13 14:43
     * @功能说明:
     */
    public function checkOrderPayData($user_id,$paymentApp){

        $this->checkOrderPay($user_id,$paymentApp,1);

        $this->checkOrderPay($user_id,$paymentApp,3);

        return true;
    }



}
