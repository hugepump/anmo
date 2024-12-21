<?php
namespace app\massage\model;

use AlibabaCloud\Client\AlibabaCloud;
use app\adapay\model\PayRecord;
use app\balancediscount\model\UserCard;
use app\BaseModel;
use Exception;
use longbingcore\wxcore\PayModel;
use think\facade\Db;

class UpOrderList extends BaseModel
{
    //定义表名
    protected $name = 'massage_service_up_order_list';



    protected $append = [


        'order_goods',


    ];
    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-19 17:05
     * @功能说明:子订单信息
     */

    public function getOrderGoodsAttr($value,$data){

        if(!empty($data['id'])){

            $order_goods_model = new UpOrderGoods();

            $dis = [

                'order_id' => $data['id'],

            ];

            $list = $order_goods_model->where($dis)->select()->toArray();

            return $list;

        }

    }
    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:04
     * @功能说明:添加
     */
    public function dataAdd($data){

        $data['create_time'] = time();

        $res = $this->insert($data);

        return $res;

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:05
     * @功能说明:编辑
     */
    public function dataUpdate($dis,$data){

        $res = $this->where($dis)->update($data);

        return $res;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:06
     * @功能说明:列表
     */
    public function dataList($dis,$page){

        $data = $this->where($dis)->order('id desc')->paginate($page)->toArray();

        return $data;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:43
     * @功能说明:
     */
    public function dataInfo($dis){

        $data = $this->where($dis)->find();

        return !empty($data)?$data->toArray():[];

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-22 11:31
     * @功能说明:订单支付回调
     */
    public function orderResult($order_code,$transaction_id){

        $order = $this->dataInfo(['order_code'=>$order_code,'transaction_id'=>'']);

        if(!empty($order)&&!empty($transaction_id)){

            Db::startTrans();

            $update = [

                'transaction_id' => $transaction_id,

                'pay_type'       => 2,

                'pay_time'       => time(),

            ];

            $res = $this->dataUpdate(['id'=>$order['id'],'transaction_id'=>''],$update);

            if($res==0){

                Db::rollback();

                return false;
            }
            //扣除余额
            if($order['balance']>0){

                $water_model = new BalanceWater();

                $res = $water_model->updateUserBalance($order,4);

                if($res==0){

                    Db::rollback();

                    return false;
                }
            }
            //储值卡支付
            if($order['pay_model']==4){

                $discount_card_model = new UserCard();

                $res = $discount_card_model->updateCardCash($order['id'],$order['user_id'],2);

                if($res==0){

                    Db::rollback();

                    return false;
                }
            }

            $order['transaction_id'] = $transaction_id;

            $order_model = new Order();

            $service_order = $order_model->dataInfo(['id'=>$order['order_id']]);
            //需要退款
            if(empty($service_order)||!in_array($service_order['pay_type'],[2,3,4,5,6])){

                $this->refundCash($order);

                Db::commit();

                return true;
            }

            $order_price_log = new OrderPrice();
            //增加订单金额日志
            $order_price_log->logAdd($order,$order['order_id'],0,$order['pay_model']);

            $res = $this->changeOrder($order);

            if($res==0){

                Db::rollback();

                return false;
            }

            Db::commit();
        }

        return true;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-02-19 11:57
     * @功能说明:特殊情况 升级订单付款后 订单已经完成或者取消需要退款
     */
    public function refundCash($order){

        $up_refund_model = new UpRefundOrder();

        $refund_model    = new RefundOrder();

        $payConfig       = payConfig($order['uniacid'],$order['app_pay']);

        $insert = [

            'uniacid'     => $order['uniacid'],

            'order_id'    => $order['id'],

            'order_code'  => orderCode(),

            'status'      => 1,

            'apply_price' => $order['pay_price'],

            'refund_price'=> $order['pay_price'],

            'create_time' => time(),

            'refund_time' => time(),
        ];

        $up_refund_model->dataAdd($insert);

        $refund_id = $up_refund_model->getLastInsID();

        $res = $refund_model->adapayRefundCash($order,$order['pay_price'],$refund_id,0,'up_refund');

        if($res==false){

            $res = $refund_model->heepayRefundCash($order,$order['pay_price'],$refund_id,0,'up_refund');
        }

        if($res==false){
            //微信
            if($order['pay_model']==1){

                $response = orderRefundApi($payConfig,$order['pay_price'],$order['pay_price'],$order['transaction_id']);
                //如果退款成功修改一下状态
                if ( isset($response[ 'return_code' ]) && isset( $response[ 'result_code' ] ) && $response[ 'return_code' ] == 'SUCCESS' && $response[ 'result_code' ] == 'SUCCESS' ) {

                    $update = [

                        'transaction_id' => !empty($response['out_refund_no'])?$response['out_refund_no']:$order['order_code'],

                        'status' => 2,

                        'have_price' => $order['pay_price']
                    ];

                }else {

                    $update = [

                        'failure_reason' => !empty($response['err_code_des'])?$response['err_code_des']:$response['return_msg'],

                        'status' => 5
                    ];
                }
            }elseif ($order['pay_model']==3){
                //支付宝
                $pay_model = new PayModel($payConfig);

                $res = $pay_model->aliRefund($order['transaction_id'], $order['pay_price']);

                if (isset($res['alipay_trade_refund_response']['code']) && $res['alipay_trade_refund_response']['code'] == 10000) {

                    $update = [

                        'transaction_id' => $res['alipay_trade_refund_response']['out_trade_no'],

                        'status' => 2,

                        'have_price' => $order['pay_price']
                    ];

                } else {

                    $update = [

                        'failure_reason' => $res['alipay_trade_refund_response']['sub_msg'],

                        'status' => 5
                    ];
                }

            }else{

                $water_model = new BalanceWater();

                $water_model->updateUserBalance($order,6,1);

                $update = [

                    'transaction_id' => $order['order_code'],

                    'have_price' => $order['pay_price'],

                    'status' => 2
                ];
            }

        }else{

            if(!empty($res['code'])){

                $update = [

                    'failure_reason' => $res['error_msg'],

                    'status' => 5
                ];
            }else{
                $update = [

                    'status' => 4
                ];
            }
        }

        if(!empty($update)){

            $res = $up_refund_model->dataUpdate(['id'=>$refund_id],$update);
        }
        //无论是否退款成功都要改状态
        $this->dataUpdate(['id'=>$order['id']],['pay_type'=>-1]);

        return $res;
    }


    /**
     * @param $time_long
     * @param $order_id
     * @功能说明:校验加钟订单
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-27 11:13
     */
    public function checkAddOrderTime($time_long,$order_id){

        $order_model = new Order();

        $where[] = ['pay_type','not in',[-1]];

        $where[] = ['add_pid','=',$order_id];

        $where[] = ['is_add','=',1];
        //目前加单只能一单一单对加 所以这里其实只有一条数据
        $order_list = $order_model->where($where)->field('id,coach_id,start_time,end_time,order_end_time,pay_type,add_pid')->select()->toArray();

        if(!empty($order_list)){

            foreach ($order_list as $value){

                $start_time = $value['start_time'] + $time_long;
                //校验时间
                $check = $order_model->checkTime($value,$start_time,$value['add_pid']);

                if(!empty($check['code'])){

                    return $check;

                }
            }
        }

        return true;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-12-13 16:23
     * @功能说明:修改原来的订单
     */
    public function changeOrder($order){

        $order_model       = new Order();

        $order_goods_model = new OrderGoods();

        $up_goods_model    = new UpOrderGoods();

        $order_goods = $up_goods_model->where(['order_id'=>$order['id']])->field('*,goods_name as title,goods_cover as cover,goods_id as service_id')->select()->toArray();
        //添加订单商品
        $res = $order_goods_model->upOrderGoodsAdd($order_goods,$order['order_id'],$order['coach_id'],$order['user_id']);

        if($res==0){

            return false;
        }

        $goods_model = new Service();

        foreach ($order_goods as $v){

            $p_order_goods = $order_goods_model->dataInfo(['id'=>$v['order_goods_id']]);
            //退回销量
            $goods_model->setOrDelStock($p_order_goods['goods_id'],$v['num'],1);
            //说明全部升级
            if($v['num']>=$p_order_goods['num']){

                $res = $order_goods_model->dataUpdate(['id'=>$v['order_goods_id']],['status'=>-1]);

            }else{

                $update = [

                    'num' => $p_order_goods['num']-$v['num'],

                    'can_refund_num' => $p_order_goods['can_refund_num']-$v['num']
                ];

                $update['coupon_discount'] = $update['coupon_discount']/$p_order_goods['num']*$update['num'];

                $update['member_discount_cash'] = $update['member_discount_cash']/$p_order_goods['num']*$update['num'];

                $update['balance_discount_cash'] = $update['balance_discount_cash']/$p_order_goods['num']*$update['num'];

                $res = $order_goods_model->dataUpdate(['id'=>$v['order_goods_id']],$update);
            }

            if($res==0){

                return false;
            }
        }

        $p_order = $order_model->dataInfo(['id'=>$order['order_id']]);

        $order_update['time_long']          = $p_order['time_long'] + $order['time_long'];

        $order_update['true_time_long']     = $p_order['true_time_long'] + $order['time_long'];

        $order_update['end_time']           = $p_order['end_time'] + $order['time_long']*60;

        $order_update['pay_price']          = round($p_order['pay_price'] + $order['pay_price'],2);

        $order_update['material_price']     = round($p_order['material_price'] + $order['material_price'],2);

        $order_update['start_material_price']= round($p_order['start_material_price'] + $order['material_price'],2);

        $order_update['init_material_price']= round($p_order['init_material_price'] + $order['init_material_price'],2);

        $order_update['service_price']      = round($p_order['service_price'] + $order['true_service_price'],2);

        $order_update['true_service_price'] = round($p_order['true_service_price'] + $order['true_service_price'],2);

        $order_update['init_service_price'] = round($p_order['init_service_price'] + $order['service_price'],2);

        $order_update['discount']           = round($p_order['discount'] - $order['discount']+$order['coupon_discount'],2);

        $order_model->dataUpdate(['id'=>$order['order_id']],$order_update);

        $order_data_model = new OrderData();
        //会员储值折扣
        if(!empty($order['member_discount_cash'])){

            $member_discount_cash = $order['member_discount_cash'];

            $order_data_model->where(['order_id'=>$order['order_id']])->update(['member_discount_cash'=>Db::Raw("member_discount_cash+$member_discount_cash")]);
        }
        //储值折扣卡
        if($order['pay_model']==4){

            $balance_discount_cash = $order['balance_discount_cash'];

            $order_data_model->where(['order_id'=>$order['order_id']])->update(['balance_discount_cash'=>Db::Raw("balance_discount_cash+$balance_discount_cash")]);
        }

        $p_order = $order_model->dataInfo(['id'=>$order['order_id']]);

        $comm_model  = new Commission();

        $car_find = $comm_model->where(['order_id'=>$order['order_id'],'status'=>2])->where('type','in',[8,13])->find();

        if(!empty($car_find)){

            $p_order['true_car_price'] = 0;
        }
        //结算佣金
        $comm_model->refundCash($p_order,3);
        //更改加钟订单的时间
        $this->changeAddOrderTime($order['time_long']*60,$order['order_id'],$p_order);
        //使用了优惠券但是升级时候没有抵扣 需要退还优惠券
        if(!empty($p_order['coupon_id'])&&$order['coupon_discount']==0&&$order_update['discount']<=0){
            //退换优惠券
            $coupon_model = new CouponRecord();

            $coupon_model->couponRefund($p_order['id']);
        }

        return $res;
    }





    /**
     * @param $time_long
     * @param $order_id
     * @功能说明:更改加钟订单的时间
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-27 11:54
     */
    public function changeAddOrderTime($time_long,$order_id,$p_order){

        $order_model = new Order();

        if($p_order['is_add']==0){

            $where[] = ['pay_type','not in',[-1]];

            $where[] = ['add_pid','=',$order_id];

            $where[] = ['is_add','=',1];
        }else{

            $where[] = ['pay_type','not in',[-1]];

            $where[] = ['add_pid','=',$p_order['add_pid']];

            $where[] = ['start_time','>',$p_order['start_time']];

            $where[] = ['is_add','=',1];
        }

        $order_list = $order_model->where($where)->field('id,start_time,end_time,order_end_time,pay_type')->select()->toArray();

        if(!empty($order_list)){

            foreach ($order_list as $value){

                $update['start_time'] = $value['start_time'] + $time_long;

                $update['end_time'] = $value['end_time'] + $time_long;

                $order_model->dataUpdate(['id'=>$value['id']],$update);
            }
        }

        return true;
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-12-15 11:18
     * @功能说明:订单升级记录
     */
    public function orderUpRecord($order_id){

        $dis = [

            'order_id'=>$order_id,

            'pay_type'=>2
        ];

        $data = $this->where($dis)->order('id desc')->select()->toArray();

        return $data;

    }






}