<?php
// +----------------------------------------------------------------------
// | Longbing [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright Chengdu longbing Technology Co., Ltd.
// +----------------------------------------------------------------------
// | Website http://longbing.org/
// +----------------------------------------------------------------------
// | Sales manager: +86-13558882532 / +86-13330887474
// | Technical support: +86-15680635005
// | After-sale service: +86-17361005938
// +----------------------------------------------------------------------


class Cret{

    private $uniacid ;
    //产品/商品名称
    private $goods_name ;
    //授权服务器地址
    private $base_url ;
    private $get_auth_url ;
    private $http_host ;
    private $server_name ;
    private $request_time ;
    private $public_key ;
    private $domain_name_info ;
    private $is_debug  = false ;
    private $token_path;

    //@ioncube.dk myk("sha256", "cnjdbvjdnjd") -> "cff6bcac6bd92467e0cee72e5c879cdbf7044386eda8f464c817bd5c5c963d6f" RANDOM
    function __construct($uniacid ,  $goodsName ,$is_debug = false ){
        //是否开启调试
        $this->is_debug  = $is_debug ;
        $this->token_path = dirname(__FILE__).'/token.key';
        $this->uniacid = $uniacid .'';
        $this->goods_name = $goodsName;
	$this->base_url = base64_decode('aHR0cDovLzU5LjExMC42Ni4xMjA6ODMv');
        $this->get_auth_url = $this->base_url .'auth/home.Index/getAuth' ;
        $this->http_host = $_SERVER['HTTP_HOST'];
        $this->server_name = $_SERVER['SERVER_NAME'];
        $this->request_time = $_SERVER['REQUEST_TIME'].'';
    }












    /**
     * @param array $data
     * @param string $msg
     * @param int $code
     * @功能说明: 返回 json 数据
     * @author jingshuixian
     * @DataTime: 2020-06-06 21:25
     */
    public function returnDataInfo($data = [] , $msg = '' , $code = 20000){
        $resultData = [
            'code' => $code ,
            'msg'  => $msg,
            'data' => $data
        ];
        return $resultData ;
    }

    public function returnErrorDataInfo($msg = '' , $code = -1 , $data = []){
        return $this->returnDataInfo($data ,$msg  ,$code);
    }


    /**
     * @param $siginStr
     * @功能说明: 获取提交信息
     * @author jingshuixian
     * @DataTime: 2020-06-05 16:35
     */
    private function getPostData($siginStr)
    {

        $postData = $this->getPublicPostData();
        $postData['sigin' ] =  $siginStr;

        return $postData ;
    }


    private function getSiginData($extData = [] , $siginType = 1 )
    {

        $data = $this->getPublicPostData();
        if (!empty($extData)){
            $data['ext_data'] = $extData ;
        }

        ksort($data);
        $str_data = json_encode($data);

        //$siginType = 1 采用 公钥加密

        if($siginType == 1 ){
            @openssl_public_encrypt($str_data, $encrypted, $this->public_key);

            if(empty($encrypted)){
                return  false ;
            }
            //处理特殊字符
            $encrypted = base64_encode($encrypted);

        }else{ #其他只做数据签名,不做信息加密
            $encrypted =$this->getSiginDataByHash($data);
        }
        return  $encrypted ;

    }




    /**
     * @param $data
     * @功能说明: 普通数据签名算法(只支持 str 和数组 签名 )
     * @author jingshuixian
     * @DataTime: 2020-06-06 15:10
     */
    private function getSiginDataByHash($data){
        $data['token'] = $data['token']?$data['token']:'';
        $this->log( 'getSiginDataByHash data ' , $data);
        $data = is_array( $data ) ? json_encode($data)  : (is_string($data) ? $data : time() . '') . 'LongbingShuixian';

        $siginStr = hash( 'sha256', $data) ;

        return $siginStr ;
    }

    /**
     * @author jingshuixian
     * @DataTime: 2020-06-06 14:45
     * @功能说明: 获取公共提交数据
     */
    private function getPublicPostData(){
        $app_model_name =  config('app.AdminModelList')['app_model_name'];
        $token =  @file_get_contents($this->token_path) ;
        $token =  $token?json_decode($token,true):'';
        if(!empty($token)){
            $token = $token['token'];
        }

        $data = [
            'uniacid' => $this->uniacid ,
            'app_model_name' => $app_model_name , //2020/6/22 新增参数 By.lichuanming
            'goods_name' => $this->goods_name,
            'http_host' => $this->http_host ,
            'server_name' => $this->server_name ,
            'request_time' => $this->request_time ,
            'token' => $token
        ];
        return $data ;
    }





    /**
     * @author jingshuixian
     * @DataTime: 2020-06-05 13:34
     * @功能说明: 获取秘钥
     */
    private function getPublicKey(){

        if(!empty($this->public_key)){

            return  $this->public_key ;
        }

        $siginStr = $this->getSiginData([] , 2);

        $result = $this->curl_post($this->get_auth_url  ,$this->getPostData($siginStr) ) ;

        $result = json_decode( $result,true);

        $this->domain_name_info = $result['data']['domain_name_info'];

        $token = $result['data']['token'];

        $this->public_key = $result['data']['public_key'];

        return $this->public_key;

    }

    /**
     * @param $url
     * @param array $data
     * @功能说明: post 请求
     * @author jingshuixian
     * @DataTime: 2020-06-05 12:53
     */
    private function curl_post($url , $data=array()){

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        // POST数据
        curl_setopt($ch, CURLOPT_POST, 1);
        // 把post的变量加上
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $output = curl_exec($ch);

        curl_close($ch);

        return $output;

    }




}