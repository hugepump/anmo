<?php

// This file is auto-generated, don't edit it. Thanks.
namespace longbingcore\wxcore;

use AlibabaCloud\SDK\Dyplsapi\V20170525\Dyplsapi;
use AlibabaCloud\SDK\Dyplsapi\V20170525\Models\BindAxbRequest;
use AlibabaCloud\SDK\Dyplsapi\V20170525\Models\UnbindSubscriptionRequest;
use \Exception;
use AlibabaCloud\Tea\Exception\TeaError;
use AlibabaCloud\Tea\Utils\Utils;

use Darabonba\OpenApi\Models\Config;
use AlibabaCloud\SDK\Dyplsapi\V20170525\Models\QuerySubscriptionDetailRequest;
use AlibabaCloud\Tea\Utils\Utils\RuntimeOptions;

class aliyunVirtual {

    /**
     * 使用AK&SK初始化账号Client
     * @param string $accessKeyId
     * @param string $accessKeySecret
     * @return Dyvmsapi Client
     */
    public static function createClient($uniacid){

        $configmodel = new \app\massage\model\Config();

        $config      = $configmodel->dataInfo(['uniacid'=>$uniacid]);

        $accessKeyId     = trim($config['short_id']);

        $accessKeySecret = trim($config['short_secret']);

        $config = new Config([
            // 必填，您的 AccessKey ID
            "accessKeyId" => $accessKeyId,
            // 必填，您的 AccessKey Secret
            "accessKeySecret" => $accessKeySecret
        ]);
        // 访问的域名
        $config->endpoint = "dyplsapi.aliyuncs.com";

        return new Dyplsapi($config);

    }


    /**
     * @param $uniacid
     * @param $phoneA
     * @param $phoneB
     * @param $expiration
     * @param bool $isRecordingEnabled
     * @功能说明:绑定手机号
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-12-08 10:58
     */
    public function bindPhone($uniacid,$phoneA,$phoneB,$expiration,$poolKey,$out_id,$isRecordingEnabled=true){

        $client = self::createClient($uniacid);

        $bindAxbRequest = new BindAxbRequest([

            "phoneNoA"   => $phoneA,
            "phoneNoB"   => $phoneB,
            "expiration" => $expiration,
            "isRecordingEnabled" => $isRecordingEnabled,
            "poolKey" => $poolKey,
            "outId" => $out_id
        ]);

        $runtime = new RuntimeOptions([]);

        try {
            // 复制代码运行请自行打印 API 的返回值
           $res = $client->bindAxbWithOptions($bindAxbRequest, $runtime);

           $res =  object_array($res);

           return !empty($res['body'])?$res['body']:[];

        }
        catch (Exception $error) {
            if (!($error instanceof TeaError)) {
                $error = new TeaError([], $error->getMessage(), $error->getCode(), $error);
            }

            return $error->message;
            // 如有需要，请打印 error
            Utils::assertAsString($error->message);
        }
    }


    /**
     * @param $uniacid
     * @功能说明:解除绑定
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-12-08 11:57
     */
    public function delBind($uniacid,$subsId,$phoneX,$poolKey){

        $client = self::createClient($uniacid);

        $unbindSubscriptionRequest = new UnbindSubscriptionRequest([
            "subsId"   => $subsId,
            "secretNo" => $phoneX,
            "productType" => "AXB_170",
            "poolKey"  => $poolKey
        ]);
        $runtime = new RuntimeOptions([]);
        try {
            // 复制代码运行请自行打印 API 的返回值
            $res = $client->unbindSubscriptionWithOptions($unbindSubscriptionRequest, $runtime);

            $res = object_array($res);

            return !empty($res['body'])?$res['body']:[];
        }
        catch (Exception $error) {
            if (!($error instanceof TeaError)) {
                $error = new TeaError([], $error->getMessage(), $error->getCode(), $error);
            }
            // 如有需要，请打印 error
            Utils::assertAsString($error->message);
        }
    }




}

