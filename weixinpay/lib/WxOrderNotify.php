<?php
ini_set('date.timezone','Asia/Shanghai');
error_reporting(E_ERROR);

use think\App;
use think\facade\Db;

require_once "WxPay.Api.php";
require_once 'WxPay.Notify.php';


class WxOrderNotify extends WxPayNotify
{
    protected $app;
    public function __construct ( App $app )
    {
        $this->app = $app;
    }
    //查询订单
    public function Queryorder($transaction_id){

       // exit;
        @file_put_contents('./weixinQuery.txt','in_query',FILE_APPEND);

        $input = new WxPayOrderQuery();

        $input->SetTransaction_id($transaction_id);

        $result = WxPayApi::orderQuery($input);

        if(array_key_exists("return_code", $result) && array_key_exists("result_code", $result) && $result["return_code"] == "SUCCESS" && $result["result_code"] == "SUCCESS") {

            $arr = json_decode($result['attach'] , true);

            if(is_array($arr) && $arr['type']=='Balance'){

                $order_model = new \app\massage\model\BalanceOrder();

                $res    = $order_model->orderResult($arr['out_trade_no'],$transaction_id);

                return $res;
            }elseif(is_array($arr) && $arr['type']=='Massage'){

                $order_model = new \app\massage\model\Order();

                $res    = $order_model->orderResult($arr['out_trade_no'],$transaction_id);

                return $res;
            }elseif(is_array($arr) && $arr['type']=='MassageUp'){

                $order_model = new \app\massage\model\UpOrderList();

                $res    = $order_model->orderResult($arr['out_trade_no'],$transaction_id);

                return $res;

            }elseif(is_array($arr) && $arr['type']=='ResellerPay'){

                $order_model = new \app\payreseller\model\Order();

                $res    = $order_model->orderResult($arr['out_trade_no'],$transaction_id);

                return $res;
            }elseif(is_array($arr) && $arr['type']=='AgentRecharge'){

                $order_model = new \app\mobilenode\model\RechargeList();

                $res    = $order_model->orderResult($arr['out_trade_no'],$transaction_id);

                return $res;
            }elseif(is_array($arr) && $arr['type']=='Memberdiscount'){

                $order_model = new \app\memberdiscount\model\OrderList();

                $res    = $order_model->orderResult($arr['out_trade_no'],$transaction_id);

                return $res;
            }elseif(is_array($arr) && $arr['type']=='Balancediscount'){

                $order_model = new \app\balancediscount\model\OrderList();

                $res    = $order_model->orderResult($arr['out_trade_no'],$transaction_id);

                return $res;
            } elseif (is_array($arr) && $arr['type'] == 'PartnerOrder') {
//                Db::name('massage_fxq_log')->insert(['log'=>json_encode(['result' => $result, 'arr' => $arr])]);
                $data = [
                    'money' => $result['total_fee'] / 100,
                    'order_code' => $result['out_trade_no'],
                    'transaction_id' => $result['transaction_id']
                ];
                $res = \app\partner\model\PartnerOrder::notify($data);
                return $res;
            } elseif (is_array($arr) && $arr['type'] == 'PartnerOrderJoin') {
                $data = [
                    'money' => $result['total_fee'] / 100,
                    'order_code' => $result['out_trade_no'],
                    'transaction_id' => $result['transaction_id']
                ];
                $res = \app\partner\model\PartnerOrderJoin::notify($data);
                return $res;
            }
        }
        return false;
    }

    //重写回调处理函数
    public function NotifyProcess($data, &$msg)
    {
        $notfiyOutput = array();

        if(!array_key_exists("transaction_id", $data)){
            file_put_contents('./weixinQuery.txt','输入参数不正确',FILE_APPEND);
            $msg = "输入参数不正确";
            return false;
        }
        file_put_contents('./weixinQuery.txt','abc',FILE_APPEND);
        //查询订单，判断订单真实性
        if(!$this->Queryorder($data["transaction_id"])){
            $msg = "订单查询失败";
            return false;
        }
        return true;
    }
}


