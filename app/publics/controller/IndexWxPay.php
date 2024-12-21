<?php
namespace app\shop\controller;
use app\adapay\model\Config;
use app\adapay\model\PayRecord;
use app\AdminRest;
use app\ApiRest;
use think\App;
use app\shop\controller\IndexPayResunt;
use think\facade\Db;


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

        $adapay_config_model  = new Config();

        $adapay_config = $adapay_config_model->dataInfo(['uniacid'=>$uniacid]);
        //分账支付
        if($adapay_config['status']==1){

            $adapay = new \longbingcore\wxcore\Adapay($uniacid);

            $adapay_code = orderCode();

            $res = $adapay->createPay($adapay_code,$totalprice,$openid);

            if($res['status']=='succeeded'&&isset($res['expend']['pay_info'])){

                $pay_record = new PayRecord();

                $dis = [

                    'uniacid'    => $uniacid,

                    'order_code' => $attach['out_trade_no'],

                ];

                $pay_record->dataUpdate($dis,['status'=>-1]);

                $insert = [

                    'uniacid'    => $uniacid,

                    'order_code' => $attach['out_trade_no'],

                    'adapay_code'=> $adapay_code,

                    'res_id'     => $res['id']

                ];

                $pay_record->dataAdd($insert);

                return json_decode($res['expend']['pay_info'],true);
            }
        }

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
            'n' => APP_MODEL_NAME
        ];
        $reply_path=json_encode($param_arr);
        //需要判断 是否是微擎的版本
        if(defined('IS_WEIQIN')){
            $path  = "https://" . $_SERVER['HTTP_HOST'] ."/addons/".APP_MODEL_NAME."/core2/app/Common/wexinPay.php?params=".$reply_path;
            $paths = "https://" . $_SERVER['HTTP_HOST'] ."/addons/".APP_MODEL_NAME."/core2/app/Common/wexinPay.php?ck=789";
            $a     = @file_get_contents($paths);
            if($a != 1){
                $this->errorMsg('发起支付失败');
            }
        }else{
            $path  = "https://" . $_SERVER['HTTP_HOST'] ."/wexinPay.php?params=".$reply_path;
            $paths = "https://" . $_SERVER['HTTP_HOST'] ."/wexinPay.php?ck=789";
            $a     = @file_get_contents($paths);
            if($a != 1){
                $this->errorMsg('发起支付失败');
            }
        }
        $this ->lb_logOutput('BaseApiPath:-----'.$path);
        $input->SetNotify_url($path);
        $input->SetTrade_type("JSAPI");
        $input->SetOpenid($openid);
        $order = \WxPayApi::unifiedOrder($input);
        if(!empty($order['return_code'])&&$order['return_code'] == 'FAIL'){
            $this->errorMsg($order['return_msg']);
        }
        $jsApiParameters = $tools->GetJsApiParameters($order);

        $jsApiParameters = json_decode($jsApiParameters, true) ;
        if (!empty($jsApiParameters['return_code']))
             $this->errorMsg( '发起支付失败');
        return $jsApiParameters;
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
        }else{
            $uniacid = $_GET['i'];
        }

        $paymentApp = $this->payConfig($uniacid);
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





}
