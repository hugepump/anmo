<?php

/**
 * Created by PhpStorm.
 * User: feidao
 * Date: 2019/9/9
 * Time: 11:44 AM
 */
class EncryptUtil
{
    /**
     * @param $key 加密KEY
     * @param $iv 加密向量
     * @param $data 需要加密的数据
     * @return string
     */
    public static function encrypt($key, $data)
    {
        $key = substr($key, 0, 16);
        $iv = substr(md5($key), 0, 16);
        /**
         * 打开加密
         */
        $td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, "", MCRYPT_MODE_CBC, "");
        /**
         * 初始化加密
         */
        mcrypt_generic_init($td, $key, $iv);
        /**
         * 加密
         */
        $encrypted = mcrypt_generic($td, $data);
        /**
         * 清理加密
         */
        mcrypt_generic_deinit($td);
        /**
         * 关闭
         */
        mcrypt_module_close($td);
        return base64_encode($encrypted);
    }

    /**
     * @param $key
     * @param $iv
     * @param $data
     * @return string
     */
    public static function decrypt($key, $data)
    {
        $key = substr($key, 0, 16);
        $iv = substr(md5($key), 0, 16);
        /**
         * 打开加密
         */
        $td = mcrypt_module_open(MCRYPT_RIJNDAEL_128,"",MCRYPT_MODE_CBC,"");
        /**
         * 初始化加密
         */
        mcrypt_generic_init($td, $key, $iv);
        $decode = base64_decode($data);
        /**
         * 解密
         */
        $dencrypted = mdecrypt_generic($td, $decode);
        /**
         * 清理加密
         */
        mcrypt_generic_deinit($td);
        /**
         * 关闭
         */
        mcrypt_module_close($td);

        return $dencrypted;
    }
}