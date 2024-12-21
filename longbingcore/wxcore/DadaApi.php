<?php
declare(strict_types=1);

namespace longbingcore\wxcore;

use app\Common\LongbingServiceNotice;

use think\facade\Db;

/**
 * 达达开放平台sdk
 * User: 仝帅
 * Date: 2016-12-19
 * Time: 16:48
 */
class DadaApi
{
    //private $URL = 'http://newopen.qa.imdada.cn';
    private $URL = 'http://newopen.imdada.cn';
    private $APP_KEY = '';
    private $VERSION = '1.0';
    private $APP_SECRET = '';
    private $API_ADDORDER = '/api/order/addOrder';
    private $API_FETCHORDER = '/api/order/fetch';
    private $API_CITY_LIST = "/api/cityCode/list";
    private $API_FINISHORDER = '/api/order/finish';
    private $API_CANCELORDER = '/api/order/cancel';
    private $API_EXPIREORDER = '/api/order/expire';
    private $API_FORMALCANCEL = '/api/order/formalCancel';
    private $API_CANCELREASONS = '/api/order/cancel/reasons';
    private $API_ACCEPTORDER = '/api/order/accept';
    private $API_ADDTIP = '/api/order/addTip';
    private $API_READDORDER = '/api/order/reAddOrder';

    private $API_QUERYDELIVERFEE = '/api/order/queryDeliverFee';
    private $API_ADDAFTERQUERY = '/api/order/addAfterQuery';
    private $API_ADDSHOP = '/api/shop/add';
    private $API_ADDMERCHANT = '/merchantApi/merchant/add';

    private $API_ORDERINFO = '/api/order/status/query';


    private $SOURCE_ID = '73753';  //商户编号
    private $SHOP_NO = '11047059'; //门店编号
    private $SUCCESS = "success";
    private $FAIL = "fail";

    private $call_back;

    public function __construct($appkey,$appsec,$source_id,$shop_no)
    {
        $this->APP_KEY    = $appkey;

        $this->APP_SECRET = $appsec;

        $this->SHOP_NO    = $shop_no;

        $this->SOURCE_ID  = $source_id;

        $this->incon      = '?';

     //   dump($this->APP_KEY , $this->APP_SECRET, $this->SHOP_NO , $this->SOURCE_ID);exit;

        if(longbingIsWeiqin()){

//            $url = substr($this->request->url(),0,strpos($this->request->url(), '&s='));

            $params = $_GET;

            $i=$params['i'];

            $t=$params['t'];

            $v=$params['v'];

            $n=$params['m'];

            $this->call_back = "https://".$_SERVER['HTTP_HOST']."/app/index.php?i=$i&t=".$t."&v=".$v."&from=wxapp&c=entry&a=wxapp&do=api&core=core2&m=".$n."&s=restaurant/SendResult/dadaSend";

//            $this->call_back = 'http://'. $_SERVER['HTTP_HOST'].$url.'&s=/restaurant/SendResult/dadaSend';

            $this->incon = '&';
        }else{

            $this->call_back  = 'http://'. $_SERVER['HTTP_HOST'].'/restaurant/SendResult/dadaSend';

            $this->incon = '?';

        }

    }

    /** 新增订单
     * @return bool
     */
    public function addOrder($data)
    {

        $city_code = $this->cityCode($data['city']);

        if(!empty($city_code['code'])&&$city_code['code']==500){

            return $city_code;

        }

        $arr = [

            'shop_no'          => $this->SHOP_NO,

            'origin_id'        => $data['order_code'],

            'city_code'        => $city_code,

            'is_prepay'        => 0,

            'cargo_price'      => $data['pay_price'],

            'receiver_name'    => $data['address']['name'],

            'receiver_address' => $data['address']['address'].$data['address']['info'],

            'receiver_phone'   => $data['address']['phone'],

            'receiver_lat'     => $data['address']['lat'],

            'receiver_lng'     => $data['address']['long'],

            'callback'         => $this->call_back.$this->incon.'id='.$data['id'],

            'cargo_weight'     => $data['weight']

        ];

        $res =  self::getResult($this->API_ADDORDER,$arr);


//        dump($this->SHOP_NO,$res);exit;

        return $res;
    }

    /**
     * 重新发布订单
     * 在调用新增订单后，订单被取消、过期或者投递异常的情况下，调用此接口，可以在达达平台重新发布订单。
     * @return bool
     */
    public function reAddOrder($data)
    {

        $arr = [

            'shop_no'          => $this->SHOP_NO,

            'origin_id'        => $data['order_code'],

            'city_code'        => $this->cityCode($data['city']),

            'is_prepay'        => 0,

            'cargo_price'      => $data['pay_price'],

            'receiver_name'    => $data['address']['name'],

            'receiver_address' => $data['address']['address'].$data['address']['info'],

            'receiver_phone'   => $data['address']['phone'],

            'receiver_lat'     => $data['address']['lat'],

            'receiver_lng'     => $data['address']['long'],

            'callback'         => $this->call_back.$this->incon.'id='.$data['id'],

            'cargo_weight'     => $data['weight']

        ];
        return self::getResult($this->API_READDORDER,$arr);
    }

    /**
     * 查询订单运费接口
     * @return bool
     */
    public function queryDeliverFee($data)
    {
        $data['shop_no'] = $this->SHOP_NO;

        $arr = [

            'shop_no'          => $this->SHOP_NO,

            'origin_id'        => $data['id'],

            'city_code'        => $this->cityCode($data['city']),

            'is_prepay'        => 0,

            'receiver_name'    => $data['address']['name'],

            'receiver_address' => $data['address']['address'].$data['address']['info'],

            'receiver_phone'   => $data['address']['phone'],

            'receiver_lat'     => $data['address']['lat'],

            'receiver_lng'     => $data['address']['long'],

            'callback'         => $this->call_back.$this->incon.'id='.$data['id'],

            'cargo_weight'     => $data['weight']

        ];

//        dump($this->call_back.'/id/'.$data['id']);exit;
        return self::getResult($this->API_QUERYDELIVERFEE,$arr);
    }

    /**
     * 查询运费后发单接口
     */
    public function addAfterQuery($data)
    {


        return self::getResult($this->API_ADDAFTERQUERY,$data);
    }


    /**
     * 查询运费后发单接口
     */
    public function orderInfo($data)
    {

        $arr['order_id'] = $data['order_code'];
//        dump($data);exit;
//        $data['deliveryNo'] = '';
        return self::getResult($this->API_ORDERINFO,$arr);
    }

    /**
     * 取消订单(线上环境)
     * 在订单待接单或待取货情况下，调用此接口可取消订单。注意：订单接单后1-15分钟取消订单，会扣除相应费用补贴给接单达达
     * @return bool
     */
    public function formalCancel($data)
    {
//        $data['order_id'] = '12321';
//        $data['cancel_reason_id'] = '1';
//        $data['cancel_reason'] = "";
        return self::getResult($this->API_FORMALCANCEL,$data);
    }

    /**
     * 取消订单(仅在测试环境供调试使用)
     * @return bool
     */
    public function cancelOrder($data)
    {
//        $data['order_id'] = '12321';
        return self::getResult($this->API_CANCELORDER,$data);
    }


    /**
     * 增加小费
     * 可以对待接单状态的订单增加小费。需要注意：订单的小费，以最新一次加小费动作的金额为准，故下一次增加小费额必须大于上一次小费额。
     * @return bool
     */
    public function addTip($data)
    {
//        $data['order_id'] = '12321';
//        $data['tips'] = '2.5';
//        $data['city_code'] = '029';
//        $data['info'] = '';
        return self::getResult($this->API_ADDTIP,$data);
    }

    /**
     * 新增门店
     * @return bool
     */
    public function addShop($data)
    {
//        $data['origin_shop_id'] = '';
//        $data['station_name'] = '';
//        $data['business'] = '';
//        $data['city_name'] = '';
//        $data['area_name'] = '';
//        $data['station_address'] = '';
//        $data['lng'] = '';
//        $data['lat'] = '';
//        $data['contact_name'] = '';
//        $data['phone'] = '';
//        $data['username'] = '';
//        $data['password'] = '';
        return self::getResult($this->API_ADDSHOP,$data);
    }

    public function addMerchant($data)
    {
//        $data['mobile'] = '';
//        $data['city_name'] = '';
//        $data['enterprise_name'] = '';
//        $data['enterprise_address'] = '';
//        $data['contact_name'] = '';
//        $data['contact_phone'] = '';
        $this->SOURCE_ID = '';
        return self::getResult($this->API_ADDMERCHANT,$data);


    }

    /**
     * 获取取消订单原因列表
     * array {0 =>array{'reason' =>'没有达达接单','id' =>1},....}
     */
    public function cancelReasons()
    {
        $res = self::getResult($this->API_CANCELREASONS);
        var_dump($res);
    }

    /**
     * 接单(仅在测试环境供调试使用)
     * @return bool
     */
    public function acceptOrder($data)
    {
//        $data['order_id'] = '12321';
        return self::getResult($this->API_ACCEPTORDER,$data);
    }

    /**
     * 完成取货(仅在测试环境供调试使用)
     * @return bool
     */
    public function fetchOrder($data)
    {
//        $data['order_id'] = '12321';
        return self::getResult($this->API_FETCHORDER,$data);
    }

    /**
     * 完成订单(仅在测试环境供调试使用)
     * @return bool
     */
    public function finishOrder($data)
    {
//        $data['order_id'] = '12321';
        return self::getResult($this->API_FINISHORDER,$data);
    }

    /**
     * 订单过期(仅在测试环境供调试使用)
     * @return bool
     */
    public function expireOrder($data)
    {
//        $data['order_id'] = '12321';
        return self::getResult($this->API_EXPIREORDER,$data);
    }

    /**
     * 订单状态变化后，达达回调我们
     */
    public function processCallback()
    {
        $content = file_get_contents("php://input");
        //{"order_status":2,"cancel_reason":"","update_time":1482220973,"dm_id":666,"signature":"7a177ae4b1cf63d13261580e4f721cb9","dm_name":"测试达达","order_id":"12321","client_id":"","dm_mobile":"13546670420"}
        if($content){
            $arr = json_decode($content,true);

        }
    }

    /** 获取城市信息
     * @return bool
     */
    public function cityCode($city){

        $code = 0;

        $data = self::getResult($this->API_CITY_LIST);

        if(empty($data['result'])){

            return $data;
        }

        $city = str_replace('市','',$city);

        $found_key = array_search($city, array_column($data['result'], 'cityName'));

        if(!is_numeric($found_key)){

            return ['code'=>500,'msg'=>'达达暂不支持该城市'];
        }

        if(key_exists($found_key,$data['result'])){

            $code = $data['result'][$found_key]['cityCode'];
        }

        return $code;
    }

    /**
     *
     * @param $param
     * @param $time
     * @return string
     */
    private function sign($param,$time)
    {
        $tmpArr = array(
            "app_key"=>$this->APP_KEY,
            "body"=>$param,
            "format"=>"json",
            "source_id"=>$this->SOURCE_ID,
            "timestamp"=>$time,
            "v"=>$this->VERSION,
        );

//        dump($tmpArr);exit;
        if(empty($this->SOURCE_ID)){
            unset($tmpArr['source_id']);
        }
        $str = '';
        foreach ($tmpArr as $k=>$v){
            $str .= $k.$v;
        }
        $str = $this->APP_SECRET.$str.$this->APP_SECRET;
        $signature = md5($str);

//        dump($signature);exit;
        return strtoupper($signature);
    }

    private function getParam($data='')
    {
        if(empty($data)){
            $param = '';
        }else{
            $param = json_encode($data);
        }
        $time = time();
        $sign = self::sign($param,$time);
        $tmpArr = array(
            "app_key"=>$this->APP_KEY,
            "body"=>$param,
            "format"=>"json",
            "signature"=>$sign,
            "source_id"=>$this->SOURCE_ID,
            "timestamp"=>$time,
            "v"=>$this->VERSION,
        );
        if(empty($this->SOURCE_ID)){
            unset($tmpArr['source_id']);
        }

        return json_encode($tmpArr);
    }

    /** 根据参数获取结果信息
     * @param $api
     * @param string $data
     * @return bool
     */
    private function getResult($api,$data=''){
        $param = self::getParam($data);

        $url = $this->URL.$api;
        $res = self::http_post($url,$param);

        if($res){
            $res = json_decode($res,true);
//            if($res['status'] == $this->SUCCESS){
            return $res;
//            }
        }
        return false;
    }

    /**
     * POST 请求
     * @param string $url
     * @param array $param
     * @param boolean $post_file 是否文件上传
     * @return string content
     */
    private function http_post($url,$param,$post_file=false){
        $oCurl = curl_init();
        if(stripos($url,"https://")!==FALSE){
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
        }
        if (is_string($param) || $post_file) {
            $strPOST = $param;
        } else {
            $aPOST = array();
            foreach($param as $key=>$val){
                $aPOST[] = $key."=".urlencode($val);
            }
            $strPOST =  join("&", $aPOST);
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt($oCurl, CURLOPT_POST,true);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS,$strPOST);
        $header = array(
            'Content-Type: application/json',
        );
        curl_setopt($oCurl, CURLOPT_HTTPHEADER, $header);

        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        if(intval($aStatus["http_code"])==200){
            return $sContent;
        }else{
            return false;
        }
    }


}