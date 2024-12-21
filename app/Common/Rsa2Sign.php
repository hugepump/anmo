<?php
namespace app\Common;
/**
 * Created by lb.
 * User: yangqi
 * Date: 2019/4/3
 * Time: 17:56
 */
class Rsa2Sign
{
    private $PRIVATE_KEY ="private_key";
    private $PUBLIC_KEY  ="public_key";
    function __construct($keys){
        $this->PRIVATE_KEY = $keys['private_key'];
        $this->PUBLIC_KEY  = $keys['public_key'];
    }
    /**
     * 获取私钥
     * @return bool|resource
     */
    private function getPrivateKey()
    {
        return openssl_pkey_get_private($this->PRIVATE_KEY);
    }
    /**
     * 获取公钥
     * @return bool|resource
     */
    private function getPublicKey()
    {
        return openssl_pkey_get_public($this->PUBLIC_KEY);
    }
    /**
     * 创建签名
     * @param string $data 数据
     * @return null|string
     */
    public function createSign($data = '')
    {
      //  var_dump(self::getPrivateKey());die;
        if (!is_string($data)) {
            return null;
        }
        return openssl_sign($data, $sign, $this->getPrivateKey(),OPENSSL_ALGO_SHA256 ) ? base64_encode(base64_encode($sign)) : null;
    }
    /**
     * 验证签名
     * @param string $data 数据
     * @param string $sign 签名
     * @return bool
     */
    public function verifySign($data = '', $sign = '')
    {
        if (!is_string($sign) || !is_string($sign)) {
            return false;
        }
        $sign = base64_decode($sign);
        return (bool)openssl_verify(
            $data,
            base64_decode($sign),
            $this->getPublicKey(),
            OPENSSL_ALGO_SHA256
        );
    }
    /**
     * 加密
     * @param string $data 数据
     * @return string $result
     */
    public function encrypt($data){
        $key = openssl_pkey_get_public($this->PUBLIC_KEY);
        if (!$key) {
            return false;
        }
        $return_en = openssl_public_encrypt($data, $crypted, $key);
        if (!$return_en) {
            return false;
        }
        $eb64_cry = base64_encode($crypted);
        return $eb64_cry;
    }
    /**
     * 解密
     * @param string $data 数据
     * @return string $result
     */
    public function decrypt($data){
        $private_key = openssl_pkey_get_private($this->PRIVATE_KEY);
        if (!$private_key) {
            return false;
        }
        $return_check = openssl_private_decrypt(base64_decode($data), $decrypted, $private_key);
        if (!$return_check) {
            return false;
        }
        return $decrypted;
    }
}
