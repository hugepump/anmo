<?php
/**
 * Created by PhpStorm
 * User: shurong
 * Date: 2024/8/13
 * Time: 18:33
 * docs:
 */

namespace app\baiying\controller;

use app\ApiRest;
use app\baiying\model\BaiYingCallback;
use app\baiying\model\BaiYingPhoneRecord;
use app\massage\model\NoPayRecord;
use think\App;
use think\facade\Db;

class CallBack extends ApiRest
{
    protected $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * @Desc: 外呼回调
     * @Auther: shurong
     * @Time: 2024/8/14 9:51
     */
    public function outboundCallBack()
    {
        $data = request()->param();

        if (isset($data['code']) && isset($data['data']['callbackType'])) {

            $callbackType = $data['data']['callbackType'];

            $insert = [
                'value' => json_encode($data),
                'callbackType' => $callbackType,
                'in_time' => date('Y-m-d H:i:s')
            ];

            BaiYingCallback::insert($insert);

            if ($callbackType == 'CALL_INSTANCE_RESULT' && isset($data['data']['data']['callInstance'])) {

                $mobile = $data['data']['data']['callInstance']['customerTelephone'];//手机号

                $finishStatus = $data['data']['data']['callInstance']['finishStatus'];//状态

                $job_id = $data['data']['data']['callInstance']['callJobId'];//任务id

                $voice = $data['data']['data']['callInstance']['userLuyinOssUrl'];

                $info = BaiYingPhoneRecord::where(['job_id' => $job_id, 'mobile' => $mobile])->find();

                if ($info) {

                    BaiYingPhoneRecord::where(['job_id' => $job_id, 'mobile' => $mobile])->update(['voice_path' => $voice, 'finish_status' => $finishStatus]);

                    if ($info['type'] == 2) {

                        $order_id = BaiYingPhoneRecord::where(['job_id' => $job_id, 'mobile' => $mobile, 'type' => 2])->column('order_id');

                        NoPayRecord::whereIn('id', $order_id)->update(['by_status' => $finishStatus]);
                    }
                }
            }
        }

        exit('success');
    }
}