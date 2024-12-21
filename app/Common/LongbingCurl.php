<?php
namespace app\Common;
class LongbingCurl{
    protected $_errno;
    protected $_error;
    public function __construct(){

    }
    /**
     * 获取错误信息
     * @return object | null
     */
    public function getError() {
        return $this->_error ? (object) [
            'errno' => $this->_errno,
            'error' => $this->_error
        ] : NULL;
    }
    //基础请求
    public function crulGetData($url, $post, $method, $header=1){
        $this->_errno = NULL;
        $this->_error = NULL;
        $ch = curl_init($url);
                                                                       
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if($post != NULL)
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION,true);
        curl_setopt($ch, CURLOPT_AUTOREFERER,true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        
        if($header)
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-type: application/json',
            )
        );
        $result = curl_exec($ch);
        return $result;
    } 
    //get请求
    public function curlGet($url,$post=NULL){
        $result = $this->crulGetData($url,$post,'GET');
        return $result;
    }
    //post请求
    public function curlPost($url,$post=NULL){
        $result = $this->crulGetData($url,$post,'POST');
        return $result;
    }
    //patch请求
    public function curlPatch($url,$post){
        $result = $this->crulGetData($url,$post,'PATCH');
        return $result;
    }
    //put请求
    public function curlPut($url,$post){
        $result = $this->crulGetData($url,$post,'PUT');
        return $result;
    }
    //deletd请求
    public function curlDelete($url,$post){
        $result = $this->crulGetData($url,$post,'DELETE',$heard=1);
        return $result;
    }
    
    public function curlPublic($url,$post,$method = 'GET'){
        $result = $this->crulGetData($url,$post,$method,$heard=1);
        return $result;
    }
}