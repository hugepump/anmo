<?php
require_once "Fdd.Config.php";

/**
 * 3DES加解密类
 */
class FddEncryption
{

    public $key = FddConfig::AppSecret;

    /**
     * 兼容PHP7.2 3DES加密
     * @param $message
     * @return string
     */
    public function encrypt($message)
    {
        return $str = bin2hex(openssl_encrypt($message, "des-ede3", $this->key, 1));
    }

    /**
     * 通用msg_digest加密函数
     * @param $param
     * @param $enc
     * @return string
     */
    public static function GeneralDigest($param, $enc)
    {
        $value = $param->GetValues();
        $md5Str = $param->GetTimestamp();
        $sha1Str = FddConfig::AppSecret;
        foreach ($enc as $k => $v) {
            switch ($k) {
                case "md5":
                    foreach ($v as $md5Key => $md5Value) {
                        if (isset($value[$md5Value])) {
                            $md5Str .= $value[$md5Value];
                        }
                    }
                    break;
                case "sha1":
                    foreach ($v as $sha1Key => $sha1Value) {
                        if (isset($value[$sha1Value])) {
                            $sha1Str .= $value[$sha1Value];
                        }
                    }
                    break;
            }
        }

        $enc = base64_encode(strtoupper(sha1(FddConfig::AppId . strtoupper(md5($md5Str)) . strtoupper(sha1($sha1Str)))));
        return $enc;
    }

    /**
     * 数组参数转字符串格式
     * @param $Array
     * @return string
     */
    public function ArrayParamToStr($Array)
    {
        $Str = "?";
        if (!empty($Array)) {
            foreach ($Array as $k => $v) {
                $Str .= $k . "=" . $v . "&";
            }
        }
        return trim($Str, "&");

    }


    /**
     * 合同生成msg_digest加密
     * @param FddTemplate $param
     * @return string
     */
    public static function ContractDigest(FddTemplate $param)
    {
        $sha1 = FddConfig::AppSecret . $param->GetTemplate_id() . $param->GetContract_id();
        $enc = base64_encode(strtoupper(sha1(FddConfig::AppId . strtoupper(md5($param->GetTimestamp())) . strtoupper(sha1($sha1)) . $param->GetParameter_map())));
        return $enc;
    }

    /**
     * 文档签署接口（手动签） msg_digest加密
     * @param FddSignContract $param
     * @return string
     */
    public static function ExtsignDigest(FddSignContract $param)
    {
        $sha1 = FddConfig::AppSecret . $param->GetCustomer_id();
        $enc = base64_encode(strtoupper(sha1(FddConfig::AppId . strtoupper(md5($param->GetTransaction_id() . $param->GetTimestamp())) . strtoupper(sha1($sha1)))));
        return $enc;
    }

    /**
     * 文档签署接口（含有效期和次数限制） msg_digest加密
     * @param FddSignContract $param
     * @return string
     */
    public static function ExtsignValiityDigest(FddSignContract $param)
    {
        $sha1 = FddConfig::AppSecret . $param->GetCustomer_id();
        $enc = base64_encode(strtoupper(sha1(FddConfig::AppId . strtoupper(md5($param->GetTransaction_id() . $param->GetTimestamp() . $param->GetValidity() . $param->GetQuantity())) . strtoupper(sha1($sha1)))));
        return $enc;
    }

    /**
     * 授权自动签摘要加密（与通用摘要的区别是，时间戳在尾部）
     *
     * @param $param
     * @param $enc
     * @return string
     */
    public static function AuthSignDigest($param, $enc)
    {
        $value = $param->GetValues();
        // 与通用摘要的区别是，时间戳在尾部
        $md5Str = '';
        $sha1Str = FddConfig::AppSecret;
        foreach ($enc as $k => $v) {
            switch ($k) {
                case "md5":
                    foreach ($v as $md5Key => $md5Value) {
                        if (isset($value[$md5Value])) {
                            $md5Str .= $value[$md5Value];
                        }
                    }
                    break;
                case "sha1":
                    foreach ($v as $sha1Key => $sha1Value) {
                        if (isset($value[$sha1Value])) {
                            $sha1Str .= $value[$sha1Value];
                        }
                    }
                    break;
            }
        }

        $md5Str .= $param->GetTimestamp();

        $enc = base64_encode(strtoupper(sha1(FddConfig::AppId . strtoupper(md5($md5Str)) . strtoupper(sha1($sha1Str)))));
        return $enc;
    }

}

?>