<?php
/**
 * Created by PhpStorm.
 * User: 方琪
 * Date: 2021/9/16
 * curl工具类
 */
class Curl
{

    public  function serverSubmit($url,$parameter,$re = 'post',$token = "",$nonce = "",$sign = "")
    {
        $parameter = json_encode($parameter,true);
        if($token){
            $headers[]  =  "Content-Type:application/json";
            $headers[]  =  "token: ". $token;
            $headers[]  =  "fxq-nonce: ". $nonce;
            $headers[]  =  "fxq-sign: ". $sign;
        }else{
            $headers = array('Content-Type: application/json;charset=utf-8;');
        }
        //初始化
        $curl = curl_init();
        //设置抓取的url
        curl_setopt($curl, CURLOPT_URL, $url);
        //设置头文件的信息作为数据流输出
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        //设置请求头
        curl_setopt($curl, CURLOPT_HEADER, 1);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        //设置提交方式
        if ($re == 'post')
        {
            //post提交方式
            curl_setopt($curl, CURLOPT_POST, 1);
            //设置post数据
            curl_setopt($curl, CURLOPT_POSTFIELDS, $parameter);
        }
        elseif ($re == 'get')
        {

        }
        //执行命令
        $data = curl_exec($curl);

        //分离头信息与数据主体
        $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $datas['header'] = substr($data, 0, $headerSize);
        $datas['body'] = substr($data, $headerSize);

        //关闭URL请求
        curl_close($curl);
        unset($data);
        //显示获得的数据

        return json_decode($datas['body'], true);
    }


}