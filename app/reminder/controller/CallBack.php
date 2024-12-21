<?php
namespace app\reminder\controller;
use app\AdminRest;
use app\ApiRest;
use app\massage\model\SendMsgConfig;
use app\reminder\model\Config;
use app\virtual\model\PlayRecord;
use longbingcore\wxcore\PayNotify;
use think\App;
use think\facade\Db;
use WxPayApi;


class CallBack  extends ApiRest
{

    protected $app;

    public function __construct ( App $app )
    {
        $this->app = $app;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-12-09 14:13
     * @功能说明:语音通知定时任务
     */
    public function timingSendCalled(){

        $called_model = new Config();

        $res = $called_model->timingSendCalled($this->_uniacid);

        $res = $called_model->orderEndSendCalled($this->_uniacid);
        //异步执行订单消息通知
        publisher(json_encode(['action'=>'orderServiceNotice','uniacid'=>$this->_uniacid], true));

        $send = new SendMsgConfig();
        //卡券过期提醒
        $send->couponOverNotice($this->_uniacid);

        return $this->success($res);
    }











}
