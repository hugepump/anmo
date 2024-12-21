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
class Long{
    function __construct(){
    }
    //@ioncube.dk mygen("sha256","ripemd128","3","1") -> "8549e8f5a" BASIC
    public function isAuthPa(){
        dump(1);exit;
        return 1;
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
     **@param array $token
     * @功能说明: token写入
     * @author lichuanming
     * @DataTime: 2020/6/19 11:35
     */
    private function writein_token($token):bool {
        $resultWriteToken = false;
        if(is_array($token)){
            //数据正常 直接保存
            $resultWriteToken = file_put_contents($this->token_path , json_encode($token));
        }else{
            //读取原有token 数据
            $token =  @file_get_contents($this->token_path) ;
            $token =  $token?json_decode($token,true):'';
            if(!empty($token)){
                if($token['token_expiration_time'] < time()){
                    $token['token'] = '';
                    //写入空token
                    $resultWriteToken = file_put_contents($this->token_path , json_encode($token));
                }
            }
        }
        return  $resultWriteToken?true:false;
    }
    /**
     **@author lichuanming
     * @DataTime: 2020/6/23 14:33
     * @功能说明: 重置双方通讯token
     */
    private function clearUp(){
        //读取原有token 数据
        $token =  @file_get_contents($this->token_path) ;
        $token =  $token?json_decode($token,true):'';
        if(!empty($token)){
            if($token['token_expiration_time'] < time() || !$token['token']){ //token 过期
                $this->public_key = $this->getPublicKey();
                $siginStr = $this->getSiginData([]);
                $result = $this->curl_post($this->clear_up_token,$this->getPostData($siginStr));
                $result = json_decode($result,true);
                if($result['data']['clear']){ //清除成功
                    $this->public_key = null;
                }
            }
        }
    }
}
