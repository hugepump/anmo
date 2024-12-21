<?php

namespace Summary;

/*
RSA2签名方法
*/
function RSA2signs($signatureContent,$private_key_data)
{

    /*
商户私钥
*/
    $private_key_str = "-----BEGIN RSA PRIVATE KEY-----\n" . wordwrap($private_key_data, 64, "\n",true) . "\n-----END RSA PRIVATE KEY-----\n";

    $private_key = openssl_pkey_get_private($private_key_str);
    openssl_sign($signatureContent, $signature, $private_key, OPENSSL_ALGO_SHA256);
    $sign = base64_encode($signature);
    //输出签名后数据
    return $sign;
}

//RSA2分段加密方法
function RSA2data($plaintext,$public_key)
{
    /*
汇付宝公钥
*/

    $partner_public_key_str = "-----BEGIN PUBLIC KEY-----\n" . wordwrap($public_key, 64, "\n",true) . "\n-----END PUBLIC KEY-----\n";

    $publicKey = openssl_pkey_get_public($partner_public_key_str);

// 获取公钥长度
    $keyDetails = openssl_pkey_get_details($publicKey);
    $keyLength = $keyDetails['bits'] / 8;

// 分段加密
    $encryptedText = '';
    $chunks = str_split($plaintext, $keyLength - 11); // 每段长度为密钥长度减去11
    foreach ($chunks as $chunk) {
        $encryptedChunk = '';
        if (openssl_public_encrypt($chunk, $encryptedChunk, $publicKey)) {
            $encryptedText .= $encryptedChunk;
        } else {
            die('加密失败');
        }
    }
    $encryptedText = base64_encode($encryptedText);
    return $encryptedText;

}



function PostJsons($Posturl,$jsonData)
{
// 目标URL
    $url = $Posturl;


// 创建Header选项
    $options = array(
        'http' => array(
            'header'  => "Content-type: application/json\r\n",
            'method'  => 'POST',
            'content' => $jsonData,
        ),
    );

// 创建上下文选项
    $context  = stream_context_create($options);

// 发送POST请求并获取响应

    $response = file_get_contents($url, false, $context);

    if ($response === false) {
        // 处理请求失败的情况
        echo "Failed to send POST request.";
    } else {
        // 处理请求成功的响应
        return $response;
    }
}

//私钥解密

function rsaDecrypt($encryptedData,$private_key_data)
{
//    $private_key_str =
//        "-----BEGIN PRIVATE KEY-----
//MIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQCc9owirKeq4FVNlpXoFYQXqbJHU1v16iBkI9bMI2VJxjUI3YLhzLlEcj294MSw0eCrSVCrkQ6Y9Ud67o4Hb8OrcKV4xelRGe3F+AORuLvPVqPPS5XAdiViptR4MSATfwbiOsQtknobnmt0LzA4LXmL1sPhMJAvRI4d6yhqqWx1ppoFW6WCgze0ijCcQ29+VmowuO4bruaSNdCUaJsLPKz80vmD+83Drjvwhg+PHOhA11Lv6sHtP623dy4AqlM5tJp8mK6A1WK0Sb9QH0Z6B8/IH++BalLnXU78/RoU3F00DrjAd8ePoxTOcIEn3sp2caSXHuSqyZKIoEQpfQfzxVjJAgMBAAECggEAbhGCAYq7ZNs9VlRDuOTlpUZNizDMau0MW8y6AprLm2lPpXDYKAlM3c+StkUZCLmUZ+jYlgb5io9t2anRlSttthofH0sBquqYz1t0/UnjAalK48GoOLdgzgYZNlOUn7dTF2+IREDUOevkhCvXD33sHwCyiYZphYv4cMiHpgw6z2CxOOrQ9Q0AKasxPGbvoPU23kiuScr8Mv1galrstZcWA5lyCMXDFzfRzBcyodC/C836G2EeQ+zNcK75l2bwYpxxFiEAgbRXyIun84/VXLYSCUAYciv2/UydTFCd1uWW97QDhnvfIL36+2TQp6URVpozU9WpE4EfmfgJpRFMfVFAlQKBgQDqvEHWoQmaq8VSKnc2zTwYl5gnE/5atXJxNhTTZHfTTrSgGTxxwYpNHks+osZr9BbSLDpzolx5uTIVZl69KMvkI+jBPFcHegUGr9Du2em0arW2hf0tsVQqC87obewrbKgaLmBbvbYFAOw6lTXy1oyvh89DnwdaVjNOx7xCip9VgwKBgQCrLqysTk3+7NeuR4GRAvBUcR5Bpi7zUQDq+0Q6Fl4zY1VCTdf8rR0ov6FQPw54zGufEMbLrU4uWm+TGvrxwI5+0Tg1lDDDYOLAWeWlhlobeuduQwJ0QQGPQ+P9TdCMtAyfU1JdLt+fMGMl6TKX6XLly/FBZS9mNUpzr2CPrMwSwwKBgD1bxm3/HE6U9fhbZ1wo27ul7LzSShuV8HtNYrY7PeM7YJW04wrtR2SPNaYC0JEpdcmsi/7sAvZChaf4YW1au6lABbh1OF6Y87Viwd+dkKx7dFJoxdxqzRBMk+JH0YMsXOizFLcGaQz3x8gsdrSqho3flzsa18YyYCkIptpR+AJXAoGALMUEO/wuweFHdzkUVcyi9jKvaxP/a3tf9hTI/zgiYuYzwieBuX+9BI85rTcQnd84tl044MPukojsbVi9EMT/f4NQZBLhvfd01kGsoeHFZzJ5nIjB942YztM+qVzUkjf0pSaxf9Vmfse05pFavgg8GIWCD0xbXSvK0P6pDmpu5OkCgYEAzntawZTNNXdxOBjYITDswZgU/KZ0s8duWjl9qLMPKUVRk/kof1P1Q6v3p9/3sfpOM4TvM62OzF3b0/OTIAt8ltQx1ra1tHsNTB8DAuxo994ad4dLFSxFe9uE9yObE73NN460urOuEeA/XfueUEedFWOOwfHxcMWdFZuhwRosFxQ=
//-----END PRIVATE KEY-----";

    $private_key_str = "-----BEGIN RSA PRIVATE KEY-----\n" . wordwrap($private_key_data, 64, "\n",true) . "\n-----END RSA PRIVATE KEY-----\n";

    $res = openssl_pkey_get_private($private_key_str);
    $encrypted = '';
    $part_len = 256;
    $parts = str_split($encryptedData, $part_len);
    // dump($parts);exit;
    foreach ($parts as $part) {
        $encrypted_temp = '';
        $result = openssl_private_decrypt($part, $encrypted_temp, $res);
        if (!$result) {
            die('Decryption failed: ' . openssl_error_string());
        }
        $encrypted .= $encrypted_temp;
    }
    return $encrypted;
}
?>