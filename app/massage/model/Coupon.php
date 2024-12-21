<?php
namespace app\massage\model;

use app\BaseModel;
use think\facade\Db;

class Coupon extends BaseModel
{
    //定义表名
    protected $name = 'massage_service_coupon';


    protected $append = [

        'service',

        'send_count',

        'store'

    ];


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-15 15:22
     * @功能说明:已派发多少张
     */
    public function getSendCountAttr($value,$data){

        if(!empty($data['id'])){

            $record_model = new CouponRecord();

            $count = $record_model->where(['coupon_id'=>$data['id']])->sum('num');

            return $count;

        }

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-11 01:54
     * @功能说明:
     */
    public function getServiceAttr($value,$data){

        if(!empty($data['id'])){

            $ser_model = new Service();

            $dis = [
                'a.status' => 1,

                'b.coupon_id' => $data['id'],

                'b.type' => 0
            ];

            $list =  $ser_model->alias('a')
                    ->join('massage_service_coupon_goods b','b.goods_id = a.id')
                    ->where($dis)
                    ->field('a.id,a.title,a.price,b.goods_id')
                    ->group('a.id')
                    ->order('a.top desc,a.id desc')
                    ->select()
                    ->toArray();

            return $list;
        }
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-11 01:54
     * @功能说明:关联的门店
     */
    public function getStoreAttr($value,$data){

        if(!empty($data['id'])&&isset($data['use_scene'])&&$data['use_scene']==1){

            $store_model = new \app\store\model\StoreList();

            $dis = [

                'b.coupon_id' => $data['id'],

                'b.type' => 0
            ];

            $list =  $store_model->alias('a')
                ->join('massage_service_coupon_store b','b.store_id = a.id')
                ->where($dis)
                ->where('a.status','>',-1)
                ->field('a.id,a.title,b.store_id,a.admin_id,a.cover,a.status')
                ->group('a.id')
                ->order('a.id desc')
                ->select()
                ->toArray();

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

        $service = $data['service'];

        unset($data['service']);

        $store = [];

        if(isset($data['store'])){

            $store = $data['store'];

            unset($data['store']);
        }

        $res = $this->insert($data);

        $id  = $this->getLastInsID();

        $this->updateSome($id,$data['uniacid'],$service,$store);

        return $id;

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:05
     * @功能说明:编辑
     */
    public function dataUpdate($dis,$data){

//        $data['update_time'] = time();

        if(isset($data['service'])){

            $service = $data['service'];

            unset($data['service']);
        }

        $store = [];

        if(isset($data['store'])){

            $store = $data['store'];

            unset($data['store']);
        }

        $res = $this->where($dis)->update($data);

        if(isset($service)){

            $this->updateSome($dis['id'],$data['uniacid'],$service,$store);
        }

        return $res;
    }


    /**
     * @param $id
     * @param $uniacid
     * @param $spe
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-23 13:35
     */
    public function updateSome($id,$uniacid,$goods,$store=[]){

        $s_model = new CouponService();

        $s_model->where(['coupon_id'=>$id])->delete();

        if(!empty($goods)){

            foreach ($goods as $value){

                $insert['uniacid']   = $uniacid;

                $insert['coupon_id'] = $id;

                $insert['goods_id']  = $value;

                $s_model->dataAdd($insert);
            }
        }

        $store_model = new CouponStore();

        $store_model->where(['coupon_id'=>$id])->delete();

        if(!empty($store)){

            foreach ($store as $value){

                $insert['uniacid']   = $uniacid;

                $insert['coupon_id'] = $id;

                $insert['store_id']  = $value;

                $store_model->dataAdd($insert);
            }
        }

        return true;
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
    public function dataInfo($dis,$filed='*'){

        $data = $this->where($dis)->field($filed)->find();

        return !empty($data)?$data->toArray():[];

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-09 23:22
     * @功能说明:计算优惠券可以优惠多少钱
     */
    public function getDicountPrice($order_goods,$coupon_id,$table_type,$coupon_info_id=0){

        $coupon_se_model = new CouponService();

        $coupon_record_model = new CouponRecord();

        $table = $coupon_record_model->getServiceTable($table_type);

        $goods_id = Db::name("$table")->where(['coupon_id'=>$coupon_id,'type'=>1])->column('goods_id');

        $price = 0;

        $service_peice = 0;

        $material_price = 0;

        $send_type = $this->where(['id'=>$coupon_info_id])->value('send_type');

        foreach ($order_goods as $v){
            //3是通用卡券
            if(in_array($v['service_id'],$goods_id)||$send_type==3){

                $service_peice += $v['all_price'];
                //物料费也可以抵扣
                $v['all_price'] += $v['all_material_price'];

                $material_price += $v['all_material_price'];

                $price += $v['all_price'];
            }
        }

        $data['discount'] = $price;

        $data['service_discount'] = $service_peice;

        $data['material_discount'] = $material_price;

        $data['service_id'] = $goods_id;

        return $data;

    }








    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-09 23:37
     * @功能说明:订单优惠券
     */
    public function orderCouponData($order_goods,$coupon_id,$have_discount=0,$type=1,$have_full=0){

        if(empty($coupon_id)){

            return $order_goods;
        }

        $coupon_record_model = new CouponRecord();

        $info = $coupon_record_model->dataInfo(['id'=>$coupon_id]);

        if(empty($info)){

            return $order_goods;
        }
        //是否被使用或者过期
        if($info['status']!=1&&$type==1){

            return $order_goods;
        }

        if(($info['start_time']<time()&&$info['end_time']>time())||$type==2){

            $p_coupon_id = !empty($info['pid'])?$info['pid']:$coupon_id;

            $can_discount_price = $this->getDicountPrice($order_goods['list'],$p_coupon_id,$info['table_type'],$info['coupon_id']);

            $info['full'] -= $have_full;

            $info['discount'] -=$have_discount;
            //是否满足满减条件
            if(($info['full']>$can_discount_price['discount']&&$info['type']==0)||($type==2&&$can_discount_price['discount']<=0)){

                return $order_goods;
            }

            $info['full'] = $info['type']==1?0:$info['full'];

            $total_discount = $have_discount;

            $total_goods_discount = $total_material_discount = 0;
            //说明不加物料费也满足条件 则物料费不参加抵扣
            $service_discount = $info['full']<=$can_discount_price['service_discount']?$can_discount_price['service_discount']:$can_discount_price['discount'];

            $send_type = $this->where(['id'=>$info['coupon_id']])->value('send_type');

            foreach ($order_goods['list'] as &$v){
                //如果该商品可以使用优惠券
                if(in_array($v['service_id'],$can_discount_price['service_id'])||$send_type==3){

                    $bin  = $service_discount>0?$v['true_price']/$service_discount:0;
                    //单个服务抵扣多少钱
                    $goods_discount = $bin*$info['discount']<$v['true_price']?$bin*$info['discount']:$v['true_price'];

                    $material_discount = 0;
                    //要加物料费才够抵扣
                    if($info['full']>$can_discount_price['service_discount']){

                        $sbin = $service_discount>0?$v['true_material_price']/$service_discount:0;

                        $material_discount = $sbin*$info['discount']<$v['true_material_price']?$sbin*$info['discount']:$v['true_material_price'];

                    }
                    $all_discount = $goods_discount+$material_discount;

                    $v['coupon_discount'] = $all_discount;
                    //总计折扣
                    $total_discount+=$all_discount;
                    //服务折扣总计
                    $total_goods_discount    += $goods_discount;
                    //物料折扣总计
                    $total_material_discount += $material_discount;

                    $v['true_price'] = round($v['true_price']-$goods_discount,2);

                    $v['true_material_price'] = round($v['true_material_price']-$material_discount,2);
                }
            }
            //这种情况针对无门槛服务费扣完了 需要扣物料费
            if($total_discount<$info['discount']&&$total_material_discount==0){

                $del_discount = $info['discount'] - $total_discount;

                foreach ($order_goods['list'] as &$vv){

                    if(in_array($vv['service_id'],$can_discount_price['service_id'])&&$vv['true_material_price']>0){

                        $sbin = $vv['true_material_price']/$can_discount_price['material_discount'];

                        $material_discount = $sbin*$del_discount<$vv['true_material_price']?$sbin*$del_discount:$vv['true_material_price'];

                        $vv['true_material_price'] = round($vv['true_material_price']-$material_discount,2);
                        //服务折扣总计
                        $total_material_discount += $material_discount;

                        $total_discount+=$material_discount;
                    }
                }
            }

            $total_discount = $info['full']>$info['discount']?$info['discount']:round($total_discount,2);

            $order_goods['total_discount'] = $total_discount;

            $order_goods['total_goods_discount'] = $total_goods_discount;

            $order_goods['total_material_discount'] = $total_material_discount;

            $order_goods['coupon_id'] = $coupon_id;
        }

      //  dump($order_goods['total_discount'],$order_goods['total_goods_discount'],$order_goods['total_material_discount']);exit;

        return $order_goods;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-13 11:58
     * @功能说明:用户可用的优惠券
     */
    public function canUseCoupon($user_id,$coach_id){

        $coupon_model = new CouponRecord();

        $coach_model  = new Coach();

        $admin_model  = new Admin();

        $coupon_model->where(['user_id'=>$user_id,'status'=>1])->where('end_time','<',time())->update(['status'=>3]);

        $admin_id = $coach_model->where(['id'=>$coach_id])->value('admin_id');

        $admin_id = $admin_model->where(['id'=>$admin_id,'agent_coach_auth'=>1,'status'=>1])->value('id');

        $admin_id = !empty($admin_id)?$admin_id:0;
        //代理商发的优惠券只有代理商的技师可以用
        $list = $coupon_model->where(['user_id'=>$user_id,'status'=>1])->where('admin_id','in',[0,$admin_id])->order('discount desc,id desc')->select()->toArray();

        $car_model = new Car();
        //获取购物车里面的信息
        $car_list  = $car_model->carPriceAndCount($user_id,$coach_id,1);

        $car_list  = $car_list['list'];

        $data = [];

        if(!empty($list)){

            foreach ($list as &$v){

                if($v['start_time']<time()&&$v['end_time']>time()){

                    $id = !empty($v['pid'])?$v['pid']:$v['id'];

                    $info = $this->getDicountPrice($car_list,$id,$v['table_type'],$v['coupon_id']);
                    //无门槛
                    if($v['type']==1){

                        $v['full'] = 0;
                    }

                    if($v['full']<=$info['discount']&&$info['discount']>0){

                        $data[] = $v['id'];

                    }
                }
            }
        }

        return $data;
    }




//        foreach ($order_goods_list as &$v){
//            //原项目信息
//            $order_goods = $order_goods_model->dataInfo(['id'=>$v['order_goods_id']]);
//
//            if(empty($order_goods)){
//
//                return ['code'=>500,'msg'=>'升级商品错误'];
//            }
//            //新项目
//            $service = $service_model->serviceInfo(['id'=>$v['service_id']]);
//
//            $v = array_merge($service,$v);
//            //获取每个技师自己的价格
//            $coach_price = $connect_model->where(['coach_id'=>$coach_id,'ser_id'=>$v['service_id']])->value('price');
//            //没有就还是原来的价格
//            $v['price'] = is_numeric($coach_price)&&$coach_price>0?$coach_price:$v['price'];
//
//            $num[$v['order_goods_id']] = !empty($num[$v['order_goods_id']])?$num[$v['order_goods_id']]+$v['num']:$v['num'];
//            //校验数量
//            if($num[$v['order_goods_id']]>$order_goods['num']){
//
//                return ['code'=>500,'msg'=>'升级商品数量错误错误'];
//            }
//            //校验价格
//            if($v['price']+$v['material_price']<$order_goods['true_price']+$order_goods['material_price']){
//
//                return ['code'=>500,'msg'=>'只能选择价格更高的服务，请刷新重新选择'];
//            }
//            //校验项目
//            if($order_goods['goods_id']==$v['service_id']){
//
//                return ['code'=>500,'msg'=>'不能升级本来项目'];
//            }
//
//            $discount_service_price = $member_discount/10*$v['price'];
//
//            $discount_material_price = $member_discount/10*$v['material_price'];
//            //原来的储值折扣
//            $v['init_balance_discount_cash'] = round($order_goods['balance_discount_cash']/$order_goods['num']*$v['num'],2);
//
//            $v['all_price']  = round($v['price']*$v['num'],2);
//
//            $v['all_material_price'] = round($v['material_price']*$v['num'],2);
//
//            $v['true_price'] = round($discount_service_price*$v['num'],2);
//            //100 7 70
//
//            //200 5 50
//            $v['true_material_price'] = round($discount_material_price*$v['num'],2);
//            //支付价格(差价 含物料差价)
//            $v['pay_price']  = round(($v['price']+$v['material_price'] - $order_goods['price']-$order_goods['init_material_price'])*$v['num'],2);
//
//            $v['true_pay_price']  = round(($discount_service_price+$discount_material_price - $order_goods['true_price']-$order_goods['material_price'])*$v['num'],2);
//            //总时长
//            $v['total_time_long'] = $v['time_long']*$v['num'];
//            //相差时长
//            $v['del_time_long'] = ($v['time_long'] - $order_goods['time_long'])*$v['num'];
//            //原价
//            $goods_price        = $v['num']*$order_goods['price'];
//
//            $true_goods_price   = $v['num']*$order_goods['true_price'];
//            //物料费
//            $material_price     = $v['num']*$order_goods['material_price'];
//
//            $init_material_price= $v['num']*$order_goods['init_material_price'];
//            //相差的服务价格（原价）
//            $v['service_price']          = round($v['all_price'] - $goods_price,2);
//            //相差的物料费（原价）
//            $v['del_init_material_price']= round($v['all_material_price'] - $init_material_price,2);
//            //相差的物料费（现价）
//            $v['true_service_price']     = round($v['true_price'] - $true_goods_price,2);
//            //相差的服务价格（现价）
//            $v['del_material_price']     = round($v['true_material_price'] - $material_price,2);
//            //原服务的时长
//            $v['old_time_long'] = $order_goods['time_long']*$v['num'];
//            //原项目的折扣
//            $v['old_discount']  = ($order_goods['price'] - $order_goods['true_price']+$order_goods['init_material_price']-$order_goods['material_price'])*$v['num'];
//        }







}