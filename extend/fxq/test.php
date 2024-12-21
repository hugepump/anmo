<?php
/**
 * Created by PhpStorm.
 * User: 方琪
 * Date: 2021/11/29
 * 测试
 */
include './Token.php';
include './Constant.php';
include './Curl.php';
include './Sign.php';
include './SignUtils.php';


//appId
$appId="";
//secret
$secret="";

$s1="0";
$token = 'eyJhbGciOiJIUzI1NiJ9.eyJjb21wYW55SWQiOiI0MCIsImNvbXBhbnlOYW1lIjoi5rWZ5rGf6JGr6Iqm5aiD572R57uc6ZuG5Zui5pyJ6ZmQ5YWs5Y-4IiwiYXBwS2V5IjoiaXlzRXlHNkZlYyIsImFwcFNlY3JldCI6IjZhM2QwNjQ0NjFkMTQ0MmE5OWQ1NDRmODQwYTliNzY2IiwiaWF0IjoxNjM4NDk0OTUwLCJleHAiOjE2Mzg1MDIxNTB9.PyfU3JA5sNoLWuLx3YNPFkUSADFS86gb5kvlontJDy8';
switch ($s1) {
    case "0":
        //获取鉴权token
        $token = new Token();
        $token=$token->getToken($appId,$secret);
        break;
    case "1":
        //单文件签署
        $signClass = new Sign();
        $data = [
                "contract"=>'https://fxq-contract-gzh.oss-cn-qingdao.aliyuncs.com/saas/local/20211126/1637910683m1cGwDpU.pdf',//url地址或者base64
                "type"=>1,
                "signers"=> [
                    [
                        "name"=>"浙江葫芦娃网络集团有限公司",
                        "idno"=>"91330101589882738D",
                        "seal"=>"https://sign-online-group.oss-cn-hangzhou.aliyuncs.com/seal/15458792218544918.png",//图片
                        "height"=>300,
                        "areas"=>[
                            [
                                "x"=>179,
                                "y"=>421,
                                "page"=>1
                            ]
                        ]
                    ]
                ],
        ];
        $signString  = "";
        //排序
        $data = SignUtils::sortParam($data);
        //签名加密
        SignUtils::readParams($data,$signString);
        //流水号，每次请求保证唯一，五分钟之类不能重复
        $nonce = '12345678911355';

        echo "加密sign是--".$signString."||token=".$token."||nonce=".$nonce."\n";
        $sign = md5(sha1(base64_encode($signString."||token=".$token."||nonce=".$nonce)));
        $contract = $signClass->portSign($data,$token,$nonce,$sign);
        break;
    default:
        echo "error s1!";
}





