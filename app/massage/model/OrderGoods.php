<?php
namespace app\massage\model;

use app\BaseModel;
use Darabonba\GatewaySpi\Models\InterceptorContext\response;
use think\facade\Db;

class OrderGoods extends BaseModel
{
    //定义表名
    protected $name = 'massage_service_order_goods_list';


    protected $append = [

        'refund_num',

        'service_id'
    ];


    /**
     * @param $value
     * @param $data
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-28 15:21
     */
    public function getServiceIdAttr($value,$data){

        if(isset($data['goods_id'])){

            return $data['goods_id'];
        }
    }


    /**
     * @param $value
     * @param $data
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-28 15:21
     */
    public function getTruePriceAttr($value,$data){

        if(isset($value)){

            return round($value,2);
        }
    }

    /**
     * @param $value
     * @param $data
     * @功能说明:获取退款的数量
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-04-12 10:46
     */
    public function getRefundNumAttr($value,$data){

        if(!empty($data['id'])){

            $refund_model = new RefundOrder();

            $num = $refund_model->refundNum($data['id']);

            return $num;
        }

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:04
     * @功能说明:添加
     */
    public function dataAdd($data){

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
     * @DataTime: 2020-09-29 11:06
     * @功能说明:列表
     */
    public function dataSelect($dis){

        $data = $this->where($dis)->order('id desc')->select()->toArray();

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
     * @DataTime: 2021-03-22 11:12
     * @功能说明:添加商品子订单
     */
    public function orderGoodsAdd($order_goods,$order_id,$cap_id,$user_id){

        $goods_model = new Service();

        $car_model   = new Car();

        foreach ($order_goods as $v){

            $ser_status = $goods_model->where(['id'=>$v['service_id']])->value('status');

            if($ser_status!=1){

                return ['code'=>500,'msg'=>'服务已经下架'];
            }
            //校验该用户是否允许会员商品
            if(!empty($v['member_info'])&&$v['member_info']['can_buy']==0){

                return ['code'=>500,'msg'=>$v['title'].'为'.$v['member_info']['title'].'商品,你不能购买'];
            }

            if($v['num']<=0){

                return ['code'=>500,'msg'=>'服务数量错误'];
            }

            $insert = [

                'uniacid'        => $v['uniacid'],

                'order_id'       => $order_id,

                'user_id'        => $user_id,

                'pay_type'       => 1,

                'coach_id'       => $cap_id,

                'goods_name'     => $v['title'],

                'goods_cover'    => $v['cover'],

                'price'          => $v['price'],

                'init_material_price' => $v['material_price'],

                'true_price'     => round($v['true_price']/$v['num'],5),

                'material_price' => round($v['true_material_price']/$v['num'],5),

                'time_long'      => $v['time_long'],

                'num'            => $v['num'],

                'can_refund_num' => $v['num'],

                'goods_id'       => $v['service_id'],

                'coupon_discount'=> !empty($v['coupon_discount'])?$v['coupon_discount']:0,

                'member_discount_cash'=> !empty($v['member_discount_cash'])?$v['member_discount_cash']:0,

                'balance_discount_cash'=> !empty($v['balance_discount_cash'])?$v['balance_discount_cash']:0,
            ];

            $res = $this->dataAdd($insert);

            if($res!=1){

                return ['code'=>500,'msg'=>'下单失败'];
            }

            $order_goods_id = $this->getLastInsID();
            //减少库存 增加销量
            $res = $goods_model->setOrDelStock($v['service_id'],$v['num']);

            if(!empty($res['code'])){

                return $res;
            }
            //删除购物车
            $car_model->where(['user_id'=>$user_id,'coach_id'=>$cap_id,'status'=>1,'service_id'=>$v['service_id']])->delete();

//            $balance_order_goods_model = new \app\balancediscount\model\OrderGoods();
//
//            if(!empty($v['balance_discount_list'])){
//
//              //  $balance_order_goods_model->orderGoodsAdd($v['balance_discount_list'],$order_id,$order_goods_id,$order_goods_id,$v['uniacid'],1);
//            }
        }

        return true;

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-22 11:12
     * @功能说明:升级订单添加商品子订单
     */
    public function upOrderGoodsAdd($order_goods,$order_id,$cap_id,$user_id){

        foreach ($order_goods as $v){

            $dis = [

                'goods_name'   => $v['title'],

                'goods_cover'  => $v['cover'],

                'price'        => $v['price'],

                'true_price'   => $v['true_price'],

                'time_long'    => $v['time_long'],

                'material_price'=> $v['material_price'],

                'order_id'     => $order_id,

            ];

            $find = $this->dataInfo($dis);

            $coupon_discount = !empty($v['coupon_discount'])?$v['coupon_discount']:0;

            if(empty($find)){

                $insert = [

                    'uniacid'        => $v['uniacid'],

                    'order_id'       => $order_id,

                    'user_id'        => $user_id,

                    'pay_type'       => 1,

                    'coach_id'       => $cap_id,

                    'goods_name'     => $v['title'],

                    'goods_cover'    => $v['cover'],

                    'price'          => $v['price'],

                    'true_price'     => $v['true_price'],

                    'time_long'      => $v['time_long'],

                    'num'            => $v['num'],

                    'can_refund_num' => $v['num'],

                    'goods_id'       => $v['service_id'],

                    'material_price' => $v['material_price'],

                    'init_material_price' => $v['init_material_price'],

                    'member_discount_cash' => $v['member_discount_cash'],

                    'balance_discount_cash' => $v['balance_discount_cash'],

                    'coupon_discount'=> $coupon_discount,
                ];

                $res = $this->dataAdd($insert);

            }else{

                $update = [

                    'num'=>$find['num']+$v['num'],

                    'can_refund_num'=>$find['can_refund_num']+$v['num'],

                    'member_discount_cash'=>$find['member_discount_cash']+$v['member_discount_cash'],

                    'balance_discount_cash'=>$find['balance_discount_cash']+$v['balance_discount_cash'],

                    'coupon_discount'=>$find['coupon_discount']+$coupon_discount,
                ];

                $res = $this->dataUpdate(['id'=>$find['id']],$update);
            }

        }

        return $res;

    }
    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-12-13 11:17
     * @功能说明:获取升级的价格
     */
    public function getUpGoodsData($order_goods_list,$order,$is_pay=0,$input=[]){

        $order_id = $order['id'];

        $coupon_id = $order['coupon_id'];

        $coach_id = $order['coach_id'];

        $service_model     = new Service();

        $order_goods_model = new OrderGoods();

        $coupon_model      = new Coupon();

        $connect_model     = new ServiceCoach();

        $order_server = new \app\massage\server\Order();

        foreach ($order_goods_list as &$value){
            //原项目信息
            $order_goods = $order_goods_model->dataInfo(['id'=>$value['order_goods_id']]);

            if(empty($order_goods)){

                return ['code'=>500,'msg'=>'升级商品错误'];
            }

            $num[$value['order_goods_id']] = !empty($num[$value['order_goods_id']])?$num[$value['order_goods_id']]+$value['num']:$value['num'];
            //校验数量
            if($num[$value['order_goods_id']]>$order_goods['num']){

                return ['code'=>500,'msg'=>'升级商品数量错误错误'];
            }
            //新项目
            $service = $service_model->serviceInfo(['id'=>$value['service_id']]);

            $value  = array_merge($service,$value);
            //获取每个技师自己的价格
            $coach_price = $connect_model->where(['coach_id'=>$coach_id,'ser_id'=>$value['service_id']])->value('price');
            //没有就还是原来的价格
            $value['price'] = is_numeric($coach_price)&&$coach_price>0?$coach_price:$value['price'];
            //校验价格
            if($value['price']+$value['material_price']<$order_goods['true_price']+$order_goods['material_price']){

                return ['code'=>500,'msg'=>'只能选择价格更高的服务，请刷新重新选择'];
            }
            //校验项目
            if($order_goods['goods_id']==$value['service_id']){

                return ['code'=>500,'msg'=>'不能升级本来项目'];
            }

            $value['order_init_service_price'] = $order_goods['price']*$value['num'];

            $value['order_true_service_price'] = $order_goods['true_price']*$value['num'];

            $value['order_init_material_price']= $order_goods['init_material_price']*$value['num'];

            $value['order_true_material_price']= $order_goods['material_price']*$value['num'];

            $value['true_price'] = $value['all_price'] = $value['now_init_service_price'] = round($value['price']*$value['num'],2);

            $value['true_material_price']= $value['all_material_price'] = $value['now_init_material_price']=round($value['material_price']*$value['num'],2);
            //相差的服务价格（原价）
            $value['del_init_service_price'] = round($value['now_init_service_price'] - $value['order_init_service_price'],2);
            //相差的物料费（原价）
            $value['del_init_material_price']= round($value['now_init_material_price'] - $value['order_init_material_price'],2);
            //总时长
            $value['total_time_long'] = $value['time_long']*$value['num'];
            //原服务的时长
            $value['old_time_long'] = $order_goods['time_long']*$value['num'];
            //相差时长
            $value['del_time_long'] = $value['total_time_long']-$value['old_time_long'];
            //原支付价格
          //  $value['pay_price'] = round($value['del_init_service_price'] + $value['del_init_material_price'],2);

            if(!empty($coupon_id)){

                $value['pay_price']  = round($value['now_init_service_price']+$value['now_init_material_price'] - $value['order_true_material_price']-$value['order_true_service_price'],2);
            }else{

                $value['pay_price']  = round($value['now_init_service_price']+$value['now_init_material_price'] - $value['order_init_material_price']-$value['order_init_service_price'],2);
            }
            //储值支付折扣
            $value['order_inin_balance_discount_cash'] = $order_goods['balance_discount_cash']/$order_goods['num']*$value['num'];

            $value['old_discount'] = $order_goods['coupon_discount']/$order_goods['num']*$value['num'];
        }

     //   dump($order_goods_list);exit;

        $res['list'] = $order_goods_list;
        //储值折扣卡
        if($order['pay_model']==4){

            $pay_price = array_sum(array_column($order_goods_list,'pay_price'));

            $balance_discount_card_arr = !empty($input['balance_discount_card_arr'])?explode(',',$input['balance_discount_card_arr']):[];

            $res = $order_server->UpOrderBalanceDiscountData($res,$order['user_id'],$pay_price,$balance_discount_card_arr);

            if($is_pay==1&&empty($res['is_balancediscount'])){

                return ['code'=>500,'msg'=>'储值折扣失败'];
            }

            $data['balance_discount_data']['is_balancediscount'] = $res['is_balancediscount'];

            $data['balance_discount_data']['balance_discount_cash'] = $res['balance_discount_cash'];

            if(isset($res['min_discount'])){

                $data['balance_discount_data']['min_discount'] = $res['min_discount'];
            }

            if(!empty($res['is_balancediscount'])){

                $data['balance_discount_data']['balance_discount_list'] = $res['balance_discount_list'];
            }
        }
        //会员折扣
        $res = $order_server->upOrderMemberDiscountData($res,$order_id);

        $order_goods_list = $res['list'];

        foreach ($order_goods_list as &$v) {
            //相差的物料费（现价）
            $v['del_true_service_price'] = round($v['true_price'] - $v['order_true_service_price'], 2);
            //相差的服务价格（现价）
            $v['del_true_material_price'] = round($v['true_material_price'] - $v['order_true_material_price'], 2);

            $v['true_pay_price'] = round($v['del_true_service_price'] + $v['del_true_material_price'], 2);
        }

        $order_goods_id = array_column($order_goods_list,'order_goods_id');

        $old_discount = $this->where(['status'=>1,'order_id'=>$order_id])->where('id','not in',$order_goods_id)->sum('coupon_discount');

        $old_full_list= $this->where(['status'=>1,'order_id'=>$order_id])->where('coupon_discount','>',0)->where('id','not in',$order_goods_id)->field('init_material_price,price,num')->select()->toArray();

        $old_full = 0;

        if(!empty($old_full_list)){

            foreach ($old_full_list as $values){

                $old_full += ($values['price']+$values['init_material_price'])*$values['num'];
            }
        }

        $res = $coupon_model->orderCouponData($res,$coupon_id,$old_discount,2,$old_full);

        $coupon_discount = !empty($res['total_discount'])?$res['total_discount']:0;

        $coupon_goods_discount = !empty($res['total_goods_discount'])?$res['total_goods_discount']:0;

        $coupon_material_discount = !empty($res['total_material_discount'])?$res['total_material_discount']:0;

        $order_goods_list = $res['list'];

        $data['order_goods']     = $order_goods_list;
        //总的差价折扣
        $data['member_discount_cash'] = $res['del_total_discount'];

        $data['member_discount_balance'] = $res['member_discount_balance'];

        $data['balance_discount_cash'] = isset($res['balance_discount_cash'])?$res['balance_discount_cash']:0;
        //现价
        $data['order_price']     = round(array_sum(array_column($order_goods_list,'true_price')),2);
        //原价差
        $data['service_price']   = round(array_sum(array_column($order_goods_list,'del_init_service_price')),2);
        //现价差
        $data['material_price']  = round(array_sum(array_column($order_goods_list,'del_true_material_price'))-$coupon_material_discount,2);
        //原价差
        $data['init_material_price']= round(array_sum(array_column($order_goods_list,'del_init_material_price')),2);
        //现价差
        $data['true_service_price'] = round(array_sum(array_column($order_goods_list,'del_true_service_price'))-$coupon_goods_discount,2);

        $data['pay_price']       = round(array_sum(array_column($order_goods_list,'true_pay_price'))-($coupon_material_discount+$coupon_goods_discount),2);

        $data['int_pay_price']   = round(array_sum(array_column($order_goods_list,'pay_price')),2);

        $data['total_time_long'] = array_sum(array_column($order_goods_list,'total_time_long'));
        //相差时长
        $data['time_long']       = array_sum(array_column($order_goods_list,'del_time_long'));

        $data['old_time_long']   = array_sum(array_column($order_goods_list,'old_time_long'));

        $data['discount']        = array_sum(array_column($order_goods_list,'old_discount'));

        $data['coupon_discount'] = round($coupon_discount-$old_discount,2);

        $data['coupon_discount'] = $data['coupon_discount']>0?$data['coupon_discount']:0;

       // dump($data['coupon_discount'],$old_discount,$data['discount']);exit;

        $data['del_coupon_discount'] = $data['coupon_discount'];

      //  dump($data)
//
//        $data['del_coupon_discount'] = $data['coupon_discount']-$data['discount'];
//
//        $data['del_coupon_discount'] = $data['del_coupon_discount']>0?round($data['del_coupon_discount'],2):0;

        return $data;
    }


    /**
     * @param $order_goods_id
     * @param $field
     * @功能说明:获取订单商品已经退款的金额
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-12-06 11:28
     */
    public function getRefundCash($order_goods_id){

        $refund_model = new RefundOrder();

        $dis = [

            'b.order_goods_id' => $order_goods_id
        ];

        $find = $refund_model->alias('a')
            ->join('massage_service_refund_order_goods b','a.id = b.refund_id')
            ->where($dis)
            ->where('a.status','in',[1,2,4,5])
            ->field('a.id,b.num,b.goods_price,b.material_price')
            ->group('b.id')
            ->select()
            ->toArray();

        $list['total_service_price'] = $list['total_material_price'] = 0;

        if(!empty($find)){

            foreach ($find as $value){

                $list['total_service_price']  += round($value['goods_price']*$value['num'],2);

                $list['total_material_price'] += round($value['material_price']*$value['num'],2);
            }
        }

        return $list;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-12-12 14:30
     * @功能说明:获取未完成的加钟服务
     */
    public function getAddOrderGoods($order_id){

        $order_model = new Order();

        $where[] = ['a.add_pid','=',$order_id];

        $where[] = ['a.pay_type','in',[3,4,5,6]];

        $where[] = ['b.can_refund_num','>',0];

        $data = $order_model->alias('a')
                ->join('massage_service_order_goods_list b','a.id = b.order_id')
                ->where($where)
                ->group('b.id')
                ->column('b.goods_name');

        return $data;
    }


    /**
     * @param $order_id
     * @功能说明:退了款的原价
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-04-25 12:02
     */
    public function getRefundOrderGoodsInit($order_id){

        $refund_model = new RefundOrder();

        $dis= [

            'a.status' => 2,

            'b.order_id' => $order_id
        ];

        $refund_list = $refund_model->alias('a')
            ->join('massage_service_refund_order_goods b','a.id = b.refund_id')
            ->join('massage_service_order_goods_list c','b.order_goods_id = c.id')
            ->where($dis)
            ->group('b.id')
            ->field('a.empty_service_cash,a.empty_material_cash,a.comm_service_cash,a.comm_material_cash,b.num as refund_num,c.num as goods_num,(b.goods_price) as refund_service_price,(b.material_price) as refund_material_price,(c.true_price) as service_price,(c.material_price) as material_price,c.price,c.init_material_price,(c.price*c.num) as total_init_price,(c.init_material_price*c.num) as total_init_material_price')
            ->select()
            ->toArray();

        $init_service_price = $init_material_price = $refund_service_discount =$refund_material_discount=$empty_service_cash=$empty_material_cash=$comm_service_cash=$comm_material_cash=0;

        if(!empty($refund_list)){

            foreach ($refund_list as $value){

                $service_bin  = $value['service_price']>0?round($value['refund_service_price']+$value['comm_service_cash']+$value['empty_service_cash'],2)/$value['service_price']:0;

              //  dump($value['refund_service_price']+$value['comm_service_cash'],$value['service_price']);exit;

                $material_bin = $value['material_price']>0?round($value['refund_material_price']+$value['comm_material_cash']+$value['empty_material_cash'],2)/$value['material_price']:0;

                $init_service_price += $service_bin*$value['price']*$value['refund_num'];

             //   dump($value['refund_service_price'],$value['refund_service_price']+$value['comm_service_cash'],$value['service_price'],$service_bin,$init_service_price,$value['service_price']);exit;

                $init_material_price+= $material_bin*$value['init_material_price']*$value['refund_num'];

                $refund_service_discount += ($value['price']-$value['service_price'])*$service_bin*$value['refund_num'];

                $refund_material_discount+= ($value['init_material_price']-$value['material_price'])*$material_bin*$value['refund_num'];

//
//                $empty_service_cash += $value['empty_service_cash'];
//
//                $empty_material_cash+= $value['empty_material_cash'];
//
//                $comm_service_cash  += $value['comm_service_cash'];
//
//                $comm_material_cash += $value['comm_material_cash'];
            }
        }

       // dump($refund_list,$comm_service_cash);exit;

        $arr = [

            'refund_service_price'    => round($init_service_price,2),

            'refund_material_price'   => round($init_material_price,2),

            'refund_service_discount' => round($refund_service_discount,2),

            'refund_material_discount'=> round($refund_material_discount,2),

            'empty_service_cash'      => round($empty_service_cash,2),

            'empty_material_cash'     => round($empty_material_cash,2),

            'comm_service_cash'       => round($comm_service_cash,2),

            'comm_material_cash'      => round($comm_material_cash,2),
        ];

      //  dump($arr);exit;

        return $arr;
    }








}