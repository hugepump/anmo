<?php
namespace app\massage\server;
use app\ApiRest;
use app\balancediscount\model\Card;
use app\balancediscount\model\UserCard;
use app\BaseController;
use app\massage\model\OrderData;
use app\massage\model\User;
use app\member\model\Config;
use app\member\model\Goods;
use app\member\model\Member;
use app\member\model\StoredOrder;
use app\publics\model\TmplConfig;
use app\shop\model\BargainRecord;
use longbingcore\wxcore\WxTmpl;
use think\App;
use think\facade\Db;
use app\shop\model\IndexShopOrder as OrderModel;
use app\shop\model\IndexUserInfo;
use app\shop\model\IndexUser;
use app\shop\model\IndexGoods;
use app\shop\model\IndexShopSpePrice;
use app\shop\model\IndexAddress;
use app\shop\model\IndexShopOrderGoods;
use app\shop\model\IndexCouponRecord;
use app\shop\model\IndexShopCollageList;
use app\shop\model\IndexUserCollage;
use app\shop\model\IndexSellingProfit;
use app\shop\model\IndexSellingWater;
use app\shop\model\IndexCardCount;
use work;


class Order
{

    public $_observer = [];


    public function __construct() {


    }


    /**
     * @param $list
     * @param $uniacid
     * @功能说明:会员折扣计算
     * @author chenniang
     * @DataTime: 2024-09-05 10:42
     */
    public function memberDiscountData($list,$user_id,$is_remember=1,$uniacid=666){

        $list['member_discount_balance'] = 0;

        $list['member_discount_cash'] = 0;

        $list['member_status'] = 0;

        $data = memberDiscountAuth($uniacid);

        if($data['status']!=1){

            return $list;
        }

        $user_model = new User();

        $member_discount_time = $user_model->where(['id'=>$user_id])->value('member_discount_time');
        //如果不是会员
        if($member_discount_time<time()){

            return $list;
        }

        $list['member_status'] = 1;

        if($is_remember==0){

            return $list;
        }

        $total_goods_discount = $total_material_discount = 0;

        foreach ($list['order_goods'] as $k=>$v){
            //服务费折扣多少金额
            $goods_discount = (10-$data['discount'])/10*$v['true_price'];

            $list['order_goods'][$k]['true_price'] = $v['true_price'] - $goods_discount;
            //物料费折扣多少金额
            $material_discount = (10-$data['discount'])/10*$v['true_material_price'];

            $list['order_goods'][$k]['true_material_price'] = $v['true_material_price'] - $material_discount;

            $list['order_goods'][$k]['member_discount_cash']= $material_discount+$goods_discount;

            $total_goods_discount+=$goods_discount;

            $total_material_discount+=$material_discount;
        }

        $list['member_discount_balance'] = $data['discount'];

        $list['member_discount_cash'] = round($total_goods_discount+$total_material_discount,2);
        //会员折扣后的服务费
        $list['goods_price'] = round($list['goods_price']-$total_goods_discount,2);
        //会员折扣后的物料费
        $list['material_price'] = round($list['material_price']-$total_material_discount,2);

        $list['is_remember'] = 1;

        return $list;
    }


    /**
     * @param $list
     * @param $order_id
     * @功能说明:升级订单会员折扣
     * @author chenniang
     * @DataTime: 2024-09-10 19:07
     */
    public function upOrderMemberDiscountData($list,$order_id){

        $order_data_model  = new OrderData();

        $member_discount_balance = $order_data_model->where(['order_id'=>$order_id])->value('member_discount_balance');

        $member_discount = $member_discount_balance>0?$member_discount_balance:10;

        $del_service_discount = $del_material_discount =  0;

        foreach ($list['list'] as $k=>$v){
            //服务费折扣多少金额
            $goods_discount = (10-$member_discount)/10*$v['now_init_service_price'];
            //物料费折扣多少金额
            $material_discount = (10-$member_discount)/10*$v['now_init_material_price'];

            $list['list'][$k]['member_discount_cash']= $material_discount+$goods_discount;

            $list['list'][$k]['true_price'] -= $goods_discount;

            $list['list'][$k]['true_material_price'] -= $material_discount;
            //服务费差价折扣
            $del_service_discount += $v['del_init_service_price']*(10-$member_discount)/10;

            $del_material_discount+= $v['del_init_material_price']*(10-$member_discount)/10;
        }

        $list['del_total_discount'] = round($del_service_discount+$del_material_discount,2);

        $list['member_discount_balance'] = round($member_discount_balance,2);

        return $list;
    }

    /**
     * @param $list
     * @param $user_id
     * @param $service_price
     * @param $car_price
     * @param int $is_remember
     * @param int $uniacid
     * @功能说明:储值折扣计算
     * @author chenniang
     * @DataTime: 2024-09-14 11:02
     */
    public function UpOrderBalanceDiscountData($list,$user_id,$service_price,$balance_discount_card_arr=[]){

        $list['balance_discount_cash'] = $list['is_balancediscount'] = 0;

        $balance_card_model = new UserCard();

        $card_list = $balance_card_model->getUserCard($user_id,$service_price,0,$balance_discount_card_arr);

        $uniacid = 666;

        $data = balanceDiscountAuth($uniacid,2);
        //最低折扣
        if($data['status']==1){

            if(empty($card_list)){

                $card_model = new Card();

                $balance_discount_card = $card_model->where(['status'=>1,'uniacid'=>$uniacid])->order('discount,id desc')->find();
            }else{

                $card_model = new UserCard();

                $balance_discount_card = $card_model->where(['status'=>1,'uniacid'=>$uniacid,'user_id'=>$user_id])->where('over_time','>',time())->where('cash','>',0)->order('discount,id desc')->find();
            }

            $list['min_discount'] = !empty($balance_discount_card)?$balance_discount_card->discount:10;
        }

        if(empty($card_list)){
            //是否有卡可以抵扣
            return $list;
        }

        $list['balance_discount_list'] = $card_list;

        foreach ($list['list'] as $k=>$v){
            //差价
            $total_cash = $v['pay_price'];

            $bin = $v['now_init_service_price']/($v['now_init_service_price']+$v['now_init_material_price']);

            $list['list'][$k]['balance_discount_cash'] = $v['order_inin_balance_discount_cash'];

            foreach ($card_list as &$value){

                if($value['true_total_cash']>0){

                    $set_cash = $total_cash>$value['true_total_cash']?$value['true_total_cash']:$total_cash;
                    //该张卡抵扣了多少钱
                    $discount_cash = $set_cash*(10-$value['discount'])/10;

                    $list['list'][$k]['balance_discount_list'][] = [

                        'id'            => $value['id'],

                        'total_cash'    => $set_cash,

                        'discount'      => $value['discount'],

                        'discount_cash' => $discount_cash,

                        'value_cash'    => round($set_cash-$discount_cash,2),
                    ];

                    $value['true_total_cash'] -= $set_cash;
                    //抵扣了多少
                    $list['list'][$k]['balance_discount_cash'] += $discount_cash;

                    if($value['true_total_cash']>0){

                        break;
                    }
                }
            }

            $service_discount =  $list['list'][$k]['balance_discount_cash']*$bin;

            $material_discount= round( $list['list'][$k]['balance_discount_cash']-$service_discount,2);

            $list['list'][$k]['true_price'] = round($list['list'][$k]['true_price']-$service_discount,2);

            $list['list'][$k]['true_material_price']= round($list['list'][$k]['true_material_price']-$material_discount,2);
        }

        $list['balance_discount_cash'] = round(array_sum(array_column($card_list,'discount_cash')),2);

        $list['is_balancediscount'] = 1;

        return $list;
    }


    /**
     * @param $list
     * @param $user_id
     * @param $service_price
     * @param $car_price
     * @param int $is_remember
     * @param int $uniacid
     * @功能说明:储值折扣计算
     * @author chenniang
     * @DataTime: 2024-09-14 11:02
     */
    public function balanceDiscountData($list,$user_id,$service_price,$car_price,$pay_model=4,$uniacid=666,$balance_discount_card_arr=[]){

        $list['balance_discount_cash'] = 0;

        $data = balanceDiscountAuth($uniacid,2);

        $list['balance_discount_status'] = $data['status'];
        //如果没有权限
        if($data['status']!=1){

            $list['balance_discount_data'] = $data;

            return $list;
        }

        $balance_card_model = new UserCard();

        $card_list = $balance_card_model->getUserCard($user_id,$service_price,$car_price,$balance_discount_card_arr);
        //最低折扣
        if($data['status']==1){

            if(empty($card_list)){

                $card_model = new Card();

                $balance_discount_card = $card_model->where(['status'=>1,'uniacid'=>$uniacid])->order('discount,id desc')->find();
            }else{

                $card_model = new UserCard();

                $balance_discount_card = $card_model->where(['status'=>1,'uniacid'=>$uniacid,'user_id'=>$user_id])->where('over_time','>',time())->where('cash','>',0)->order('discount,id desc')->find();
            }

            $data['min_discount'] = !empty($balance_discount_card)?$balance_discount_card->discount:10;
        }

        if(empty($card_list)){
            //是否有卡可以抵扣
            $data['user_status'] = 0;

            $list['balance_discount_data'] = $data;

            return $list;
        }

        $data['user_status'] = 1;

        if($pay_model!=4){

            $list['balance_discount_data'] = $data;

            return $list;
        }

        $data['balance_discount_list'] = $card_list;

       // $balance_discount_car_list = [];
//        //处理车费
//        if($car_price>0){
//
//            foreach ($card_list as $vs){
//
//                if($vs['discount_car_cash']>0){
//
//                    $balance_discount_car_list[] = [
//
//                        'id' => $vs['id'],
//
//                        'value_cash' => round($vs['discount_car_cash'],2),
//                    ];
//                }
//            }
//        }
//        //车费
//        $data['balance_discount_car_list'] = $balance_discount_car_list;

        $total_service_discount = $total_material_discount = 0;

        foreach ($list['order_goods'] as $k=>$v){

            $list['order_goods'][$k]['balance_discount_cash'] = 0;

            $total_cash = $v['true_price']+$v['true_material_price'];

            $bin = $total_cash>0?$v['true_price']/$total_cash:0;

            foreach ($card_list as $ks=>&$value){

                if($value['true_total_cash']>0){

                    $set_cash = $total_cash>$value['true_total_cash']?$value['true_total_cash']:$total_cash;
                    //该张卡抵扣了多少钱
                    $discount_cash = round($set_cash*(10-$value['discount'])/10,2);

                    $list['order_goods'][$k]['balance_discount_list'][] = [

                        'id'            => $value['id'],

                        'total_cash'    => $set_cash,

                        'discount'      => $value['discount'],

                        'discount_cash' => $discount_cash,

                        'value_cash'    => round($set_cash-$discount_cash,2),
                    ];

                    $value['true_total_cash'] -= $set_cash;

                    $total_cash -= $set_cash;
                    //抵扣了多少
                    $list['order_goods'][$k]['balance_discount_cash'] += $discount_cash;

                    $service_discount = round($discount_cash*$bin,2);

                    $material_discount= round($discount_cash-$service_discount,2);

                    $total_service_discount += $service_discount;

                    $total_material_discount+= $material_discount;

                    $list['order_goods'][$k]['true_price'] = round($list['order_goods'][$k]['true_price']-$service_discount,2);

                    $list['order_goods'][$k]['true_material_price'] = round($list['order_goods'][$k]['true_material_price']-$material_discount,2);

                    if($value['true_total_cash']>0){

                        break;
                    }
                }
            }
        }

        $list['balance_discount_cash'] = round(array_sum(array_column($card_list,'discount_cash')),2);

        $data['is_balancediscount'] = 1;

        $list['balance_discount_data'] = $data;
        //折扣后的服务费
        $list['goods_price']    = round($list['goods_price']-$total_service_discount,2);
        //折扣后的物料费
        $list['material_price'] = round($list['material_price']-$total_material_discount,2);

        return $list;
    }











}
