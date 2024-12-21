<?php
namespace app\massage\controller;
use app\AdminRest;
use app\ApiRest;
use app\massage\model\BalanceOrder;
use app\massage\model\Order;
use app\massage\model\UpOrderList;
use app\memberdiscount\model\OrderList;
use app\mobilenode\model\RechargeList;
use app\partner\model\PartnerOrder;
use app\partner\model\PartnerOrderJoin;
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
        global $_GPC, $_W;
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
            $a     = file_get_contents($paths);
            if($a != 1){
                $this->errorMsg('发起支付失败');
            }
        }else{
            $path  = "https://" . $_SERVER['HTTP_HOST'] ."/wexinPay.php?params=".$reply_path;
            $paths = "https://" . $_SERVER['HTTP_HOST'] ."/wexinPay.php?ck=789";
            $a     = file_get_contents($paths);
            if($a != 1){
                $this->errorMsg('发起支付失败');
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
        // dump($paymentApp);exit;
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

            $order_model = new Order();

            $order_model->orderResult($data['out_trade_no'],$data['trade_no']);

        }

        return true;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-08-19 11:26
     * @功能说明:阿里回调
     */
    public function aliNotifyUp(){

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

            $order_model = new UpOrderList();

            $order_model->orderResult($data['out_trade_no'],$data['trade_no']);


        }

        return true;

    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-08-19 11:26
     * @功能说明:阿里回调
     */
    public function aliNotifyBalance(){

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
            $order_model = new BalanceOrder();

            $order_model->orderResult($data['out_trade_no'],$data['trade_no']);
        }

        return true;

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-08-19 11:26
     * @功能说明:阿里回调
     */
    public function aliNotifyReseller(){

        $order_model = new \app\payreseller\model\Order();

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

            $order_model->orderResult($data['out_trade_no'],$data['trade_no']);
        }

        return true;

    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-08-19 11:26
     * @功能说明:阿里回调
     */
    public function aliAgentRecharge(){

        $order_model = new RechargeList();

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

            $order_model->orderResult($data['out_trade_no'],$data['trade_no']);
        }

        return true;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-08-19 11:26
     * @功能说明:阿里回调
     */
    public function aliMemberdiscount(){

        $order_model = new OrderList();

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

            $order_model->orderResult($data['out_trade_no'],$data['trade_no']);
        }

        return true;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-08-19 11:26
     * @功能说明:阿里回调
     */
    public function aliBalancediscount(){

        $order_model = new \app\balancediscount\model\OrderList();

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

            $order_model->orderResult($data['out_trade_no'],$data['trade_no']);
        }

        return true;
    }

    /**
     * @Desc:回调
     * @return bool
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/11/19 18:14
     */
    public function aliPartnerOrder()
    {
        $data = $_POST;

        if(empty($data)){

            $data = $_GET;
        }

//        Db::name('massage_fxq_log')->insert(['log'=>json_encode($data)]);

        $pay_config = $this->payConfig();

        require_once  EXTEND_PATH.'alipay/aop/AopClient.php';

        $aop = new \AopClient;

        $aop->alipayrsaPublicKey = $pay_config[ 'payment' ][ 'ali_publickey' ];

        $flag = $aop->rsaCheckV1($data, NULL, "RSA2");

        if(!$flag){

            return false;
        }

        $data['flag'] = $flag;

        if (!empty($data) && $data['trade_status'] == 'TRADE_SUCCESS')     //支付状态
        {
            $result = [
                'money' => $data['total_amount'],
                'order_code' => $data['out_trade_no'],
                'transaction_id' => $data['trade_no']
            ];
            $a = PartnerOrder::notify($result);

            dd($a);
        }

        return true;

    }

    /**
     * @Desc: 回调
     * @return bool
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/11/19 18:13
     */
    public function aliPartnerOrderJoin()
    {
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

            $result = [
                'money' => $data['total_amount'],
                'order_code' => $data['out_trade_no'],
                'transaction_id' => $data['trade_no']
            ];
            PartnerOrderJoin::notify($result);

        }

        return true;

    }

}
