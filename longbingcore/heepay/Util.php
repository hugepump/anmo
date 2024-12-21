<?php

namespace Util;




/*
签名串排序
*/
function ASCIIAZ($arrayData)
{

    ksort($arrayData);
    // 将数组转换为 URL 编码的字符串
    $data1 = http_build_query($arrayData);
    $data2 = urldecode($data1);
    return $data2;
}




/*
RSA2签名方法
*/
function RSA2sign($signatureContent,$private_key_data)
{

    /*
商户私钥
*/
//    $private_key_str = '-----BEGIN PRIVATE KEY-----
//MIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQCI1pm6pnFLifow7bl2wVnNmfkYQM15IhO37K7XD0CpIOsg9NMNWYtJKMmLibHudUj+LuchrM9ZJxrV1lCTaXvzhVZP2UOBQ63SM/7ecDF5S7USiRm+ET52FwTqIycdWrnCjMOoLE2c5gAOBuqMfEecUV6mIDSeWGCQ3PwKL8/6LiWx95rsBZrteJdiHdU2DDxRVzbVH4nPG0bp5m2GZH8rlnuKn3Xm36IDByxytcFtgboxacc05yciL9TgZzzXet3dL0k3IabUT1NKcbKsMmPlxvt/kniaGNcXBBX4REX5Ph7kOJLzyCOo4fMUJnwUzc+8X/MvqD0gf6XgQ/qozk1VAgMBAAECggEAMKFr0uyyGPF5TAhAQFcozivpXdgx7dnRfi0MWfves/yx821u0DDBkod/USrnZGKkRUlv9QTQT9PY8qQO0MTNO1dovEnvdrIRxUGbU3w16PCh8mttDaJdO2Sk530Euqbym5ShOFQ+ZQNMg6+rja/hV0mHxKxvZtLrxq/ylsA1Pqi+qvxSUmz8HRzSQDlNYdgHkoz163IEZ/Qa/ugy/n5xz8UNrYnSZXzF9aqDKcn9xK9J3O8uoioBNA0cmZM5g4FSf2Xnjo/Lo3diTSh514CIS3DnZDJ5C2IVdmLNntx8i6INbrA+txaumnswnY40cXY4xYS+sfH70wp45Xh0T2D8YQKBgQDf2w8MGW/cWE7WVuxIP5nceezTqMkxQ5XAUfYDSZ+zeRPTyuNd9YcmZH4t+K5LWMJ47edqyl5uSMjejKKbeOrxstMvG77VCtHVGLhW+4G0JdGMk0YUHKekk4voNGx1HIaPLaN1TwALaL5O1zD1so7aQpbxb4V+FSCd6LsyhVEn/QKBgQCcfMeA42jLeXcvLT/ugm2njcR9TmOlpliMtF8kpLWUjR6E2xkGuhRD+ePlAaDDRBjn0EDS/4xNHHfTqfQOoGb9mdGfSMbIpn5R4c7piLiwwub4cinuk4AdQxcPF39GoiYAf55BlPBN9qzYhBBxXGb3BHJWaNLlgMF7RFwxNPveOQKBgBzrvUbv5l7OXekdM8ulw+gTLICv9sZRmABP1nvYQDS8uM4NEVDrTrmsFA02arY7UmyzN8m5OXgAGUt/WebCOYBefSBB8matziw81FwQhFJU7Hy/7jbc+N+vXEz0sOp0dAH9gHfAbB4NO0EOVjn4BrK2FbA9mz6N1jfwgHbneHO1AoGBAJGKG6Jr3lMfrRFbbFJPS9zBpTVFarftdf2m47YY2ihG91No0mXHOoHeL24VjYcOFnvC3AdhVQOCro9VSX8w/5htLuCNtxN2hZVyBhZ86gi7vETlKMqStFyCwTdwCxeORvm/t1gXRUe9XBQi/4fvAHRM1mo3I63/ifMzKOs68+VBAoGAW8xkzoj+DNcxI1fJIWisby+6/Yb/z0fzlBfSYV0xXdgs0j5ed74jMJqEHajCFBdvW+UqwyHeQY0aXOUeYdrQT8vIKZhNR4Kn0PrOAFu24egCcClTWgvGxITW9RehjHVgbtzU8ZGQYv3HxJnHX9I/47hfb6yF0nC6y2rclyqKxho=
//-----END PRIVATE KEY-----';

    // 私钥
    $private_key_str = "-----BEGIN RSA PRIVATE KEY-----\n" . wordwrap($private_key_data, 64, "\n",true) . "\n-----END RSA PRIVATE KEY-----\n";
    // dump($private_key_data);exit;
    $private_key = openssl_pkey_get_private($private_key_str);
    openssl_sign($signatureContent, $signature, $private_key, OPENSSL_ALGO_SHA256);
    $sign = base64_encode($signature);

    //输出签名后数据
    return $sign;
}



function PostJson($Posturl,$jsonData)
{
// 目标URL
    $url = $Posturl;


// 创建Header选项
    $options = array(
        'http' => array(
            'header' => "Content-type: application/json\r\n",
            'method' => 'POST',
            'content' => $jsonData,
        ),
    );

// 创建上下文选项
    $context = stream_context_create($options);

// 发送POST请求并获取响应

    $response = file_get_contents($url, false, $context);


    if ($response === false) {
        // 处理请求失败的情况
        echo "Failed to send POST request.";
    } else {
        // 处理请求成功的响应
        return $response;
        echo "<br/><hr>";
    }
}

/*
Post+form-data请求，文件上传专用
*/
function Postformdata($url, $data) {

    //初使化init方法
    $ch = curl_init();

    //指定URL
    curl_setopt($ch, CURLOPT_URL, $url);

    //设定请求后返回结果
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    //声明使用POST方式来进行发送
    curl_setopt($ch, CURLOPT_POST, 1);

    //发送什么数据呢
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);


    //忽略证书
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    //忽略header头信息
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));

    //设置超时时间
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    //发送请求
    $output = curl_exec($ch);

    //关闭curl
    curl_close($ch);

    //返回数据
    return $output;
}


/*
图片转换为二进制
*/
function PicToTow($img)
{
    $fp   = fopen($img, 'rb'); // 打开图片
    $content = fread($fp, filesize($img));//二进制数据
    $md1=md5($content);
    return $md1; //输出二进制数据
}

/*
删除json指定键的值双引号
*/
function DeleteValue($jsonString)
{

    // 将 JSON 字符串解码为关联数组
    $arrayData = json_decode($jsonString, true);

// 处理 biz_content 键的值，去掉外层双引号
    if (isset($arrayData['biz_content'])) {
        $arrayData['biz_content'] = json_decode($arrayData['biz_content'], true);
    }

// 打印结果
    $arrayData1 = json_encode($arrayData);

    return $arrayData1;

}




?>