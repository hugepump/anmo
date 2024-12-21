<?php
namespace app\publics\controller;
use app\baiying\model\BaiYingPhoneRecord;
use app\BaseController;
use app\card\service\UserService;
use app\massage\model\ChannelQr;
use app\massage\model\Police;
use app\massage\model\SendMsgConfig;
use app\massage\model\Wallet;
use app\memberdiscount\model\OrderList;
use think\facade\Db;
use think\facade\Log;
use think\facade\Request;

class HttpAsyn extends BaseController {


    public function message(){

        $param = Request::param();

        $data = json_decode($param['message'],true) ;

        switch ( $action = $data['action']  )
        {
            //发送消息服务通知
            case 'channelQr':

                $model = new ChannelQr();

                $model->batchChannelQrImgInit($data['uniacid']);

                break;
            case 'orderServiceNotice':

                $model = new SendMsgConfig();

                $model->orderServiceNotice($data['uniacid']);

                break;

            case 'wallet_check':
                //校验微信转账是否到账
                $model = new Wallet();

                $model->wxCheck($data['uniacid'],$data['pay_config'],$data['id']);

                break;

            case 'police_notice':
                //技师求救通知
                $model = new Police();

                $model->sendPoliceNotice($data['uniacid'],$data['coach_id'],$data['address']);

                break;

            case 'check_order_pay':
                //校验各类付款是否成功
                $model = new \app\shop\controller\IndexWxPay($this->app);

                $model->checkOrderPayData($data['user_id'],$data['paymentApp']);

                break;

            case 'coupon_notice':
                //卡券通知
                $model = new SendMsgConfig();

                $model->sendCouponNotice($data['user'],$data['coupon']);

                break;

            case 'list_commison':
                //各类失败的佣金队列
                $member_discount_model = new OrderList();
                //会员折扣佣金
                $member_discount_model->listCommSuccess($data['uniacid']);

                $balance_discount_model = new \app\balancediscount\model\OrderList();
                //储值折扣佣金
                $balance_discount_model->listCommSuccess($data['uniacid']);

                break;

            case 'auto_phone':
                //自动拨打电话
                BaiYingPhoneRecord::auto($data['uniacid']);
                break;
        }
        echo 'message ok ';

    }

}
