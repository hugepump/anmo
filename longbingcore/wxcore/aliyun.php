<?php

// This file is auto-generated, don't edit it. Thanks.
namespace longbingcore\wxcore;

use AlibabaCloud\SDK\Dyvmsapi\V20170525\Dyvmsapi;
use AlibabaCloud\SDK\Dyvmsapi\V20170525\Models\SingleCallByTtsRequest;
use \Exception;
use AlibabaCloud\Tea\Exception\TeaError;
use AlibabaCloud\Tea\Utils\Utils;

use Darabonba\OpenApi\Models\Config;
use AlibabaCloud\SDK\Dyvmsapi\V20170525\Models\SingleCallByVoiceRequest;
use AlibabaCloud\SDK\Dyplsapi\V20170525\Models\QuerySubscriptionDetailRequest;
use AlibabaCloud\Tea\Utils\Utils\RuntimeOptions;

class aliyun {

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
        $config->endpoint = "dyvmsapi.aliyuncs.com";

        return new Dyvmsapi($config);
    }

    /**
     * @param $uniacid
     * @param $phone
     * @功能说明:电话通知
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-12-08 18:03
     */
    public static function main($uniacid,$phone,$type=1){

        $client = self::createClient($uniacid);

        $config_model = new \app\reminder\model\Config();

        $config = $config_model->dataInfo(['uniacid'=>$uniacid]);

        if($type==1){

            $tmpl_id = $config['reminder_tmpl_id'];

        }elseif ($type==2){

            $tmpl_id = $config['help_tmpl_id'];

        }else{

            $tmpl_id = $config['order_end_tmpl_id'];
        }

        $singleCallByTtsRequest = new SingleCallByTtsRequest([

            "calledNumber"     => $phone,

            "ttsCode"          => $tmpl_id,

            "calledShowNumber" => $config['reminder_public']==0?$config['reminder_phone']:'',

        ]);
        $runtime = new RuntimeOptions([]);
        try {
            // 复制代码运行请自行打印 API 的返回值
            $res = $client->singleCallByTtsWithOptions($singleCallByTtsRequest, $runtime);

            $res =  object_array($res);

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

