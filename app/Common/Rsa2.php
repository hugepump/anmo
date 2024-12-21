<?php
namespace app\Common;
class Rsa2
{
    private $private_key;
    private $public_key;
    private $config = array(
                "digest_alg" => "sha512",
                "private_key_bits" =>2048,
                "private_key_type" => OPENSSL_KEYTYPE_RSA
            );
            
    function __construct(){
        $res = openssl_pkey_new($this->config);
        //生成私钥
        openssl_pkey_export($res, $this->private_key, null, $this->config);
        //生成公钥
        $this->public_key = openssl_pkey_get_details($res)['key'];
    }
    public function getKeys() {
        $result = [];
        if(!empty($this->private_key)) $result['private_key'] =$this->private_key;
        if(!empty($this->public_key)) $result['public_key'] =$this->public_key;
        return $result;
    }
}