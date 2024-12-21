<?php
/**
 * Created by PhpStorm.
 * User: 方琪
 * Date: 2021/9/16
 * 签署类
 */


class Sign
{

    //单文件签署
    public function portSign($data, $token, $nonce, $sign){
        echo '发起签署----------------开始';
        echo "\n";
        $curl = new Curl();
        $result = $curl->serverSubmit(Constant::CONTRACT_DETAIL_URL,$data,'post',$token,$nonce,$sign);
        echo 'result:'.json_encode($result);
        echo '发起签署结果：'.$result['msg'];
        echo "\n";
        echo '发起签署----------------结束';
        echo "\n";
        return $result;
    }


}