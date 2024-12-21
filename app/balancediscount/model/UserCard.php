<?php
namespace app\balancediscount\model;

use app\BaseModel;
use think\facade\Db;

class UserCard extends BaseModel
{
    //定义表名
    protected $name = 'massage_balance_discount_user_card';


    public function getCashAttr($value,$data){

        if(isset($value)){

            return round($value,2);
        }
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

        $data = $this->where($dis)->order('top desc,id desc')->paginate($page)->toArray();

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
     * @param $order
     * @功能说明:
     * @author chenniang
     * @DataTime: 2024-09-12 16:04
     */
    public function dataAdd($order){

        $insert = [

            'uniacid' => $order['uniacid'],

            'user_id' => $order['user_id'],

            'order_code' => longbingorderCodetf(),

            'card_order_id' => $order['id'],

            'cash' => $order['pay_price'],

            'total_cash' => $order['pay_price'],

            'discount' => $order['discount'],

           // 'over_time' => $order['over_time'],

            'over_time' => strtotime("+{$order['month']} months"),

            'title'  => $order['title'],

            'create_time' => time(),
        ];

        $res = $this->insert($insert);

        return $res;
    }


    /**
     * @param $user_id
     * @param $pay_price
     * @功能说明:获取可以使用的储值卡
     * @author chenniang
     * @DataTime: 2024-09-12 18:39
     */
    public function getUserCard($user_id,$service_price,$car_price,$card_arr=[]){

        if(empty($card_arr)){

            $dis = [

                'user_id' => $user_id,

                'status'  => 1
            ];

        }else{

            $dis[] = ['id','in',$card_arr];

        }

        $card = $this->where($dis)->where('over_time','>',time())->where('cash','>',0)->order('discount,cash,id desc')->select()->toArray();

        if(empty($card)){

            return [];
        }

        $arr = [];
      //  $count = count($card);
        //车费不打折
      //  $y_price = $card[$count-1]['cash'] - $car_price;
        //查询一张卡能不能搞定
//        if($y_price>=round($service_price*$card[$count-1]['discount']/10,2)&&empty($card_arr)){
//
//            $list = [
//
//                'title' => $card[$count-1]['title'],
//
//                'id'    => $card[$count-1]['id'],
//
//                'discount' => $card[$count-1]['discount'],
//                //抵扣了多少钱
//                'discount_cash' => round($service_price*(10-$card[$count-1]['discount'])/10,2),
//                //价值多少钱
//                'value_cash' => round($service_price*$card[$count-1]['discount']/10+$car_price,2),
//
//                'total_cash' => round($service_price+$car_price,2),
//
//                'discount_car_cash' => $car_price,
//
//                'true_total_cash' => $service_price,
//            ];
//
//            $arr[] = $list;
//
//            return $arr;
//        }

        foreach ($card as $value){

            $discount_cash = $value_cash = $total_cash = $d_car_cash = 0;
            //减服务费
            if($value['cash']>0&&$service_price>0){

                $true_cash = round($value['cash']/$value['discount']*10,2);

                if($service_price>$true_cash){
                    //折扣卡扣多少钱
                    $value_cash += $value['cash'];

                    $service_price -= $true_cash;
                    //抵扣的金额
                    $discount_cash+= ($true_cash-$value_cash);

                    $total_cash += $true_cash;

                    $value['cash'] = 0;

                }else{

                    $value_cash += round($service_price*$value['discount']/10,2);

                    $discount_cash+= ($service_price-$value_cash);

                    $total_cash += $service_price;

                    $value['cash'] -= round($service_price*$value['discount']/10,2);

                    $service_price = 0;
                }
            }
            //减车费
            if($value['cash']>0&&$car_price>0){

                $car_discount_cash = $car_price>$value['cash']?$value['cash']:$car_price;

                $car_price -= $car_discount_cash;

                $value_cash += $car_discount_cash;

                $total_cash += $car_discount_cash;

                $d_car_cash += $car_discount_cash;
            }

            if($total_cash>0){

                $list = [

                    'title' => $value['title'],

                    'id'    => $value['id'],
                    //折扣
                    'discount' => $value['discount'],
                    //抵扣了多少钱
                    'discount_cash' => round($discount_cash,2),
                    //扣了多少钱的折扣卡
                    'value_cash' => round($value_cash,2),
                    //总的价值多少钱
                    'total_cash' => round($total_cash,2),
                    //车费
                    'discount_car_cash' => round($d_car_cash,2),
                    //总的价值多少除去车费
                    'true_total_cash' => round($total_cash - $d_car_cash,2),
                ];

                $arr[] = $list;
            }
        }

        if($service_price>0||$car_price>0){

            return [];
        }

        return $arr;
    }


    /**
     * @author chenniang
     * @DataTime: 2024-09-18 15:50
     * @功能说明:校验储值卡余额
     */
    public function checkChangeUserCard($order_id,$type){

        $share_model = new OrderShare();

        $list = $share_model->where(['order_id'=>$order_id,'type'=>$type,'status'=>1])->select()->toArray();

        if(!empty($list)){

            foreach ($list as $value){

                $cash = $this->where(['id'=>$value['card_id']])->sum('cash');

                if($cash<$value['value_cash']){

                    return false;
                }
            }
        }

        return true;
    }


    /**
     * @param $user_id
     * @param $pay_price
     * @功能说明:获取可以使用的储值卡
     * @author chenniang
     * @DataTime: 2024-09-12 18:39
     */
    public function getUserCardList($user_id){

        $dis = [

            'a.user_id' => $user_id,

            'a.status'  => 1
        ];

        $data = $this->alias('a')
                ->join('massage_balance_discount_order_list b','a.card_order_id = b.id')
                ->where($dis)
                ->where('a.over_time','>',time())
                ->where('a.cash','>',0)
                ->field('a.*,b.pay_price')
                ->group('a.id')
                ->order('a.discount,a.cash,a.id desc')
                ->paginate(10)
                ->toArray();

        return $data;
    }


    /**
     * @param $order_id
     * @param $type
     * @param $card_id
     * @功能说明:修改卡余额
     * @author chenniang
     * @DataTime: 2024-09-18 17:31
     */
    public function updateCardCash($order_id,$user_id,$type){

        $share_model = new OrderShare();

        $water_model = new CardWater();

        $list = $share_model->where(['order_id'=>$order_id,'type'=>$type,'status'=>1])->select()->toArray();

       // Db::startTrans();

        if(!empty($list)){

            foreach ($list as $value){

                $res = $water_model->updateCash($value['uniacid'],$value['card_id'],$value['value_cash'],-1,$value['order_id'],$type,$user_id);

                if($res==0){

                   // Db::rollback();

                    return false;
                }

                $res = $share_model->dataUpdate(['id'=>$value['id']],['status'=>2]);

                if($res==0){

                    // Db::rollback();

                    return false;
                }
            }
        }

       // Db::commit();

        return true;
    }









}