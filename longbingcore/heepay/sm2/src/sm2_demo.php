<?php

//require_once 'vendor/autoload.php';

 //require_once __DIR__ .'overwrite.php';
require __DIR__.'/ecc/Curves/CurveFactory.php';
require __DIR__.'/ecc/Serializer/Util/CurveOidMapper.php';
require __DIR__.'/ecc/RtEccFactory.php';
require __DIR__.'/ecc/Sm2Curve.php';
require __DIR__.'/ecc/Sm2Signer.php';

require __DIR__.'/util/FormatSign.php';
require __DIR__.'/smecc/SM2/Cipher.php';
require __DIR__.'/smecc/SM2/Hex2ByteBuf.php';
require __DIR__.'/smecc/SM2/Sm2Enc.php';
require __DIR__.'/smecc/SM2/Sm2WithSm3.php';
require __DIR__.'/smecc/SM3/GeneralDigest.php';
require __DIR__.'/smecc/SM3/SM3Digest.php';
require __DIR__.'/sm/RtSm2.php';
require __DIR__.'/sm/RtSm3.php';
//require '/sm/RtSm2.php';

function convert_sign_rs_to_pkcs1($rs)
{
    // 转换至pkcs1格式
    $sign_pkcs1_format = "30{len}{r}{s}";
    $sign_r = substr($rs, 0, 64);
    $sign_s = substr($rs, 64,64);
    $len_add = 0;
    if($sign_r[0] >= '8') {
        $sign_r = "022100".$sign_r;
        $len_add = $len_add + 1;
    } else {
        $sign_r = "0220".$sign_r;
    }
    if($sign_s[0] >= '8') {
        $sign_s = "022100".$sign_s;
        $len_add = $len_add + 1;
    } else {
        $sign_s = "0220".$sign_s;
    }

    $sign_pkcs1_format = str_replace("{r}", $sign_r, $sign_pkcs1_format);
    $sign_pkcs1_format = str_replace("{s}", $sign_s, $sign_pkcs1_format);

    if($len_add==1) {
        $sign_pkcs1_format = str_replace("{len}", "45", $sign_pkcs1_format);
    }
    elseif($len_add==2) {
        $sign_pkcs1_format = str_replace("{len}", "46", $sign_pkcs1_format);
    } else {
        $sign_pkcs1_format = str_replace("{len}", "44", $sign_pkcs1_format);
    }
    return $sign_pkcs1_format;
}


function test(){

    $sm2 = new Rtgm\sm\RtSm2();

    echo '<h2>SM2密钥对生成测试</h2>';
    [$privateKey, $publicKey] = $sm2->generatekey();
    echo '<br>private key: ';
    var_dump($privateKey);
    echo '<br>public key: ';
    var_dump($publicKey);


    echo '<br><h2>SM2私钥签名验签测试</h2>';
    /*
    private key: string(64) "fcde55bd1cd084decd3ed03a205277fa2779146f99a5bf918be55f1f55cda0a4"
    public key: string(130) "04a14539132ba78440838e9952a363447acced34cf627761596c99191683c38c18fa4e54a57c5ee3704593d2c30801be53d50147abd872acd964be20e6514e1092"
    sign result: string(140) "304402202d14d9110cdd877dcd911e7ca5da21203d8a05c459fc3e6900d1215670e61115022059349c06645edcd3d8c3891ca562ea6ea49de16e36326f03528fd1d64cd6a453"
    */
    $message = "To be signed message";
    $privateKey = "fcde55bd1cd084decd3ed03a205277fa2779146f99a5bf918be55f1f55cda0a4";
    $publicKey = "04a14539132ba78440838e9952a363447acced34cf627761596c99191683c38c18fa4e54a57c5ee3704593d2c30801be53d50147abd872acd964be20e6514e1092";

    echo '<br>message: '.$message;
    echo '<br>private key: ';
    var_dump($privateKey);
    echo '<br>public key: ';
    var_dump($publicKey);

    $sign_result = $sm2->doSign($message, $privateKey);

    echo '<br>签名数据: ';
    var_dump($sign_result);

    $verify_result = $sm2->verifySign($message, $sign_result, $publicKey);
    echo '<br>验签结果: ';
    var_dump($verify_result);


    echo '<br><h2>汇元公钥SM2验签测试</h2>';
    $hy_pub_key_pkcs8_base64 = "MIIBMzCB7AYHKoZIzj0CATCB4AIBATAsBgcqhkjOPQEBAiEA/////v////////////////////8AAAAA//////////8wRAQg/////v////////////////////8AAAAA//////////wEICjp+p6dn140TVqeS89lCafzl4n1FauPkt28vUFNlA6TBEEEMsSuLB8ZgRlfmQRGajnJlI/jC7/yZgvhcVpFiTNMdMe8Nzai9PZ3nFm9zuNraSFT0KmHfMYqR0AC3zLlITnwoAIhAP////7///////////////9yA99rIcYFK1O79Ak51UEjAgEBA0IABChf5Gs11hyWHD4Tn0MfyZvHjd9L5XO3xz2cU/hmXb+YcL9lk4xKMC+VZ0JEx6Pm/oVjwcGBINUEgi05oyNj7+U=";
    echo '<br>汇元公钥 pkcs8 base64: '.$hy_pub_key_pkcs8_base64;

//$hy_pub_key_pkcs8_hex = bin2hex(base64_decode($hy_pub_key_pkcs8_base64));
//echo '<br>汇元公钥 pkcs8 hex: '.$hy_pub_key_pkcs8_hex;

    $hy_pub_key_hex = "04285fe46b35d61c961c3e139f431fc99bc78ddf4be573b7c73d9c53f8665dbf9870bf65938c4a302f95674244c7a3e6fe8563c1c18120d504822d39a32363efe5";
    echo '<br>汇元公钥 hex串: '.$hy_pub_key_hex;

    echo '<br>';
    $origin_message =  '{
    "msg": "无效参数",
    "code": 40002,
    "sub_msg": "参数【biz_content】无效,请传入json对象",
    "sub_code": "invalid_param",
    "sign": "jhs+CGKfo2htaq/VSzehsPdBjZ2UAy+i65FXS12xMlSsne/ZpKDicPvcWQPMyeAqvRFZb9pAntl+5sAPYaDwBA=="
  }';
    echo '<br>汇元返回的json消息【带SM2签名】: '.$origin_message;

    $message = 'code=40002&msg=无效参数&sub_code=invalid_param&sub_msg=参数【biz_content】无效,请传入json对象';
    echo '<br>变换成签名检查的格式: '.$message;

    $sign_base64 = "jhs+CGKfo2htaq/VSzehsPdBjZ2UAy+i65FXS12xMlSsne/ZpKDicPvcWQPMyeAqvRFZb9pAntl+5sAPYaDwBA==";
    echo '<br>汇元签名【base64格式】: '.$sign_base64;

    $sign_hex = bin2hex(base64_decode($sign_base64));
    echo '<br>汇元签名【转成HEX格式，长度为128】: '.$sign_hex;

    $sign_pkcs1_format = convert_sign_rs_to_pkcs1($sign_hex);

    echo '<br>调用convert_sign_rs_to_pkcs1转换成pkcs1的格式: '.$sign_pkcs1_format;

    $verify_result = $sm2->verifySign($message, $sign_pkcs1_format, $hy_pub_key_hex);
    echo '<br>验签结果: ';
    var_dump($verify_result);
}





//$message = "app_id=hykj-sm2002&biz_content=testsign&charset=utf-8&format=json&method=customer.entry.apply&notify_url=https://www.zyuncai.cn/api/saas/v1/heepay/notify/entry?code=3&sign_type=SM2&timestamp=2022-05-28 11:50:11&version=1.0";
//echo '<br>message: '.$message;
