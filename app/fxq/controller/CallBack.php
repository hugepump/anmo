<?php
/**
 * Created by PhpStorm
 * User: shurong(龙兵科技)
 * Date: 2024/10/16
 * Time: 15:18
 * docs:
 */

namespace app\fxq\controller;

use app\ApiRest;
use app\fxq\model\FxqFaceCheck;
use longbingcore\wxcore\Fxq;
use think\App;
use think\facade\Db;

class CallBack extends ApiRest
{
    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * @Desc: 人脸识别回调
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/17 11:23
     */
    public function faceCallBack()
    {
        $input = request()->param();

//        $res = Db::name('massage_fxq_log')->insert(['log' => json_encode($input)]);

        if (!isset($input['orderNo']) && !isset($input['eid_token'])) {

            return $this->success('');
        }

        //H5
//        {"orderNo":"202410161843176388929230943832","code":"0","signature":"5B1FA459D4C03A20A5C19667E8DBF24F57F7078E","newSignature":"E6E6D5F5B291E6C8DE2A0386FBA8399B4C5DB66B","liveRate":"99","h5faceId":"tx00c3cb58cb50de956759f69020d0f8"};

        //小程序
//        {"eid_token":"4654df4qw11asd5456wqw"}

        if (isset($input['orderNo'])) {

            $update = [
                'status' => $input['code'] == 0 ? 2 : 3,
                'live_rate' => $input['liveRate'] ?? 0
            ];

            $res = FxqFaceCheck::where('order_id', $input['orderNo'])->update($update);

            $face = FxqFaceCheck::where('order_id', $input['orderNo'])->find();
        } elseif (isset($input['eid_token'])) {

            $data = FxqFaceCheck::where('eid_token', $input['eid_token'])->find();

            $model = Fxq::create($data['uniacid'], $data['coach_id']);

            if (is_array($model) && isset($model['code'])) {

                return $this->error($model['msg']);
            }

            $res = $model->getEidResult($input['eid_token']);

            if (isset($res['code'])) {

                return $this->error($res['msg']);
            }

            $face = FxqFaceCheck::where('eid_token', $input['eid_token'])->find();
        }

        $model = Fxq::create($face['uniacid'], $face['coach_id']);

        if (is_array($model) && isset($model['code'])) {

            return $this->error($model['msg']);
        }

        $code = $model->signature($face['user_name'], 2);

        if (isset($res['code'])) {

            return $this->error($res['msg']);
        }

        FxqFaceCheck::where('id', $face['id'])->update(['signature' => $code]);

        return $this->success($res);
    }
}