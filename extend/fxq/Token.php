<?php

/**
 * Created by PhpStorm.
 * User: 方琪
 * Date: 2021/9/16
 * token鉴权
 */
class Token
{
    //获取鉴权token
    public function getToken($appId, $secret)
    {
        $arr = ["key" => $appId, "secret" => $secret];
        $curl = new Curl();
        $result = $curl->serverSubmit(Constant::TOKEN_URL, $arr, 'post');
        //返回数据
//        echo "返回json".json_encode($result)."\n";
//        echo '获取到的鉴权token：：'.$result['data'];
//        echo "\n";
//        echo '获取鉴权Token----------------结束';
//        echo "\n";

        if ($result['code'] != 10000) {

            throw new Exception($result['code'] . '   ' . $result['msg']);
        }

        return $result['data'];
    }

}