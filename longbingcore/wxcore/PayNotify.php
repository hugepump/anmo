<?php
declare(strict_types=1);

namespace longbingcore\wxcore;

use app\ApiRest;
use app\massage\model\BalanceOrder;
use app\massage\model\Order;
use think\App;
use think\facade\Db;

class PayNotify  {

   // static protected $uniacid;

//    public function __construct(App $app)
//    {
//        $this->app = $app;
//
//    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-08-19 13:29
     * @功能说明:支付宝回调信息
     */
    public function aliNotify($arr){

        switch ($arr['subject']){

            case '按摩订单':

                $order_model = new Order();

                break;

            case '充值订单':

                $order_model = new BalanceOrder();

                break;

        }

        $order_model->orderResult($arr['out_trade_no'],$arr['trade_no']);

        return true;
    }



}