<?php
declare(strict_types=1);

namespace longbingcore\wxcore;

use app\Common\Rsa2;
use think\facade\Db;
use WeChatPay\Crypto\Rsa;
use WeChatPay\Util\PemUtil;

class WxPay{

    static protected $uniacid;

    public function __construct($uniacid)
    {
       self::$uniacid = $uniacid;

    }


    /**
     *
     * 获取支付信息
     */
    public function payConfig (){
        $uniacid_id = self::$uniacid;

        $pay    = Db::name('longbing_card_config_pay')->where(['uniacid'=>$uniacid_id])->find();
        $config = Db::name( 'longbing_card_config')->where(['uniacid' => $uniacid_id])->find();
        if(empty($pay[ 'mch_id' ])||empty($pay[ 'pay_key' ])){

            return [

                'result_code' => false,

                'return_code' => false,

                'err_code_des'=> '未配置支付信息'
            ];
        }
        $setting[ 'payment' ][ 'merchant_id' ] = $pay[ 'mch_id' ];
        $setting[ 'payment' ][ 'key' ]         = $pay[ 'pay_key' ];
        $setting[ 'payment' ][ 'cert_path' ]   = $pay[ 'cert_path' ];
        $setting[ 'payment' ][ 'key_path' ]    = $pay[ 'key_path' ];
        $setting[ 'app_id' ]                   = $config['appid'];
        $setting[ 'secret' ]                   = $config['app_secret'];

        $setting['company_pay']                = $config['company_pay'];
        return $setting;
    }
    /**
     * @param string $uid
     * @param string $money
     * @功能说明:提现
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-03-24 14:52
     */
    public function crteateMchPay($paymentApp,$openid,$money='',$order_code='')
    {

        if($paymentApp['company_pay'] ==2){
            //新版商家付款到零钱
            $res = $this->crteateMchPayV2($paymentApp,$openid,$money,$order_code);

            return $res;
        }

        $money = $money*100;

        $payid = substr(md5('longbing666'.time()),0,11);
        //没有配置支付信息
        if(empty($paymentApp['app_id'])||empty($paymentApp['payment']['merchant_id'])){

            return $paymentApp;

        }
        //没有openid
        if(empty($openid)){

            return [

                'result_code' => false,

                'return_code' => false,

                'err_code_des'=> '用户信息错误'
            ];
        }

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

        require_once PAY_PATH . "weixinpay/lib/WxPay.Api.php";
        require_once PAY_PATH . "weixinpay/example/WxPay.JsApiPay.php";
        require_once PAY_PATH . "weixinpay/lib/WxMchPay.php";
        $payClass=new \WxMchPay();

        $res=$payClass->MchPayOrder($openid,$money,$payid);

        return  $res;
    }


    /**
     * @param $paymentApp
     * @param $money
     * @param $openid
     * @功能说明:新版商家付款到零钱
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-07-19 17:58
     */
    public function crteateMchPayV2($paymentApp,$openid,$money,$user_name=''){

       // dump($paymentApp);exit;
        $out_trade_no = time().rand(1000,9999);//

        $url = 'https://api.mch.weixin.qq.com/v3/transfer/batches';

        $pars = [];

      //  $order_code = !empty($order_code)?$order_code:'sjzz'.date('Ymd').mt_rand(1000, 9999);

        $order_code = 'sjzz'.time().mt_rand(10000, 99999);

        $pars['appid'] = $paymentApp['app_id'];//直连商户的appid

        $pars['out_batch_no'] = $order_code;//商户系统内部的商家批次单号，要求此参数只能由数字、大小写字母组成，在商户系统内部唯一

        $pars['batch_name']   = '商家转账';//该笔批量转账的名称

        $pars['batch_remark'] = '商家转账';//转账说明，UTF8编码，最多允许32个字符

        $pars['total_amount'] = intval($money * 100);//转账总金额 单位为“分”

        $pars['total_num']    = 1;//转账总笔数

        $pars['transfer_detail_list'][0]  = [

            'out_detail_no'  => $order_code,

            'transfer_amount'=> $pars['total_amount'],

            'transfer_remark'=> '商家转账',

            'openid'=>$openid

        ];//转账明细列表

        $Wechatpay = 0;
        //大于200必须要姓名
        if($pars['total_amount']>=200000&&!empty($user_name)){

            $pars['transfer_detail_list'][0]['user_name'] = $this->getEncrypt($user_name,$paymentApp);

            $Wechatpay = 1;
        }

        $token  = $this->getToken($pars,$paymentApp);

        $res    = $this->http_post($url,json_encode($pars),$token,$paymentApp,$Wechatpay);//发送请求

        $resArr = json_decode($res,true);

        if(!empty($resArr['code'])){

            $resArr['result_code'] = $resArr['return_code'] = 'fail';

            $resArr['err_code_des'] = $resArr['message'];

        }else{

            $resArr['result_code'] = $resArr['return_code'] = 'SUCCESS';
        }

        return $resArr;
        //成功返回
    }


    /**
     * @param $str
     * @param $paymentApp
     * @功能说明:根据平台证书加密
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-09-04 22:14
     */
     public function getEncrypt($str,$paymentApp) {
        //$str是待加密字符串
        $public_key_path = $paymentApp['payment']['wx_certificates'];

        if(empty($public_key_path)){

            return '';
        }

        $public_key = file_get_contents($public_key_path);

        $encrypted = '';

        if (openssl_public_encrypt($str, $encrypted, $public_key, OPENSSL_PKCS1_OAEP_PADDING)) {
            //base64编码
            $sign = base64_encode($encrypted);
        } else {
            throw new Exception('encrypt failed');
        }
        return $sign;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-09-03 15:53
     * @功能说明:查询转账记录
     */
    public function getMchPayRecord($order_code,$paymentApp){

        $url = 'https://api.mch.weixin.qq.com/v3/transfer/batches/out-batch-no/'.$order_code.'/details/out-detail-no/'.$order_code;
        //请求方式
        $token  = $this->getToken([],$paymentApp,'GET',$url);

        $res    = $this->https_request($url,[],$token);//发送请求

        $resArr = json_decode($res,true);

        return $resArr;
    }




    public function https_request($url,$data,$token)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, (string)$url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //添加请求头
        $headers = [
            'Authorization:WECHATPAY2-SHA256-RSA2048 ' . $token,
            'Accept: application/json',
            'Content-Type: application/json; charset=utf-8',
            'User-Agent:Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.132 Safari/537.36',
        ];
        if (!empty($headers)) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }


    /**
     * @param $paymentApp
     * @功能说明:获取证书序列号
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-09-04 22:15
     */
    public function get_Certificates($paymentApp)
    {

        $platformCertificateFilePath = $paymentApp['payment']['wx_certificates'];

        if(empty($platformCertificateFilePath)){

            return '';
        }

        $a = openssl_x509_parse(file_get_contents($platformCertificateFilePath));

        return !empty($a['serialNumberHex'])?$a['serialNumberHex']:'';
    }



    /**
     * POST 请求
     * @param string $url
     * @param array $param
     * @param boolean $post_file 是否文件上传
     * @return string content
     */
    private function http_post($url,$data,$token,$paymentApp,$Wechatpay=0){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, (string)$url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //添加请求头
        $headers = [

            'Authorization:WECHATPAY2-SHA256-RSA2048 '.$token,
            'Accept: application/json',
            'Content-Type: application/json; charset=utf-8',
            'User-Agent:Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.132 Safari/537.36',
        ];
        //需要加密
        if($Wechatpay==1){

            $certificates = $this->get_Certificates($paymentApp);

            $headers[] = 'Wechatpay-Serial:'.$certificates;
        }
        if(!empty($headers)){

            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }
        $output = curl_exec($curl);
        curl_close($curl);

        return $output;
    }


    /**
     * @param $pars
     * @param $paymentApp
     * @功能说明:获取token
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-07-20 10:53
     */
    public function getToken($pars,$paymentApp,$http_method='POST',$usrl_data='')
    {

        $url = !empty($usrl_data)?$usrl_data:'https://api.mch.weixin.qq.com/v3/transfer/batches';
       // $http_method = 'POST';//请求方法（GET,POST,PUT）
        $timestamp = time();//请求时间戳
        $url_parts = parse_url($url);//获取请求的绝对URL
        $nonce = $timestamp . rand(10000, 99999);//请求随机串
        $body = !empty($pars)?json_encode((object)$pars):'';//请求报文主体
        $stream_opts = [
            "ssl" => [
                "verify_peer" => false,
                "verify_peer_name" => false,
            ]
        ];
        $apiclient_cert_path = $paymentApp['payment']['cert_path'];
        $apiclient_key_path  = $paymentApp['payment']['key_path'];
        $apiclient_cert_arr = openssl_x509_parse(file_get_contents($apiclient_cert_path, false, stream_context_create($stream_opts)));
        $serial_no = $apiclient_cert_arr['serialNumberHex'];//证书序列号
        $mch_private_key = file_get_contents($apiclient_key_path, false, stream_context_create($stream_opts));//密钥
        $merchant_id = $paymentApp['payment']['merchant_id'];//商户id
        $canonical_url = ($url_parts['path'] . (!empty($url_parts['query']) ? "?${url_parts['query']}" : ""));
        $message = $http_method . "\n" .
            $canonical_url . "\n" .
            $timestamp . "\n" .
            $nonce . "\n" .
            $body . "\n";
        openssl_sign($message, $raw_sign, $mch_private_key, 'sha256WithRSAEncryption');

        $sign = base64_encode($raw_sign);//签名
        $schema = 'WECHATPAY2-SHA256-RSA2048';
        $token = sprintf('mchid="%s",nonce_str="%s",timestamp="%d",serial_no="%s",signature="%s"',
            $merchant_id, $nonce, $timestamp, $serial_no, $sign);//微信返回token
        return $token;

    }



}