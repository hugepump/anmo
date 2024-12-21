<?php
namespace app\massage\model;

use AlibabaCloud\Client\AlibabaCloud;
use app\BaseModel;
use Exception;
use think\facade\Db;

class UpOrderGoods extends BaseModel
{
    //定义表名
    protected $name = 'massage_service_up_order_goods_list';


    protected $append = [

        'original_order_goods'
    ];


    /**
     * @param $value
     * @param $data
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-12-16 15:20
     */
    public function getOriginalOrderGoodsAttr($value,$data){

        if(!empty($data['order_goods_id'])){

            $order_goods_model = new OrderGoods();

            $info = $order_goods_model->where(['id'=>$data['order_goods_id']])->field('goods_name,price,true_price,goods_cover,material_price')->find();

            return $info;
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
    public function orderGoodsAdd($order_goods,$order_id){

        $goods_model = new Service();

        foreach ($order_goods as $v){

            $ser_status = $goods_model->where(['id'=>$v['service_id']])->value('status');

            if($ser_status!=1){

                return ['code'=>500,'msg'=>'服务已经下架'];
            }

            if($v['num']<=0){

                return ['code'=>500,'msg'=>'服务数量错误'];
            }

            $insert = [

                'uniacid'        => $v['uniacid'],

                'order_id'       => $order_id,

                'goods_name'     => $v['title'],

                'goods_cover'    => $v['cover'],

                'price'          => $v['price'],

                'true_price'     => round($v['true_price']/$v['num'],5),

                'num'            => $v['num'],

                'goods_id'       => $v['service_id'],

                'time_long'      => $v['time_long'],

                'order_goods_id' => $v['order_goods_id'],

                'pay_price'      => $v['pay_price'],

                'material_price' => round($v['true_material_price']/$v['num'],2),

                'init_material_price' => $v['all_material_price'],

                'coupon_discount'=> !empty($v['coupon_discount'])?$v['coupon_discount']:0,
                'member_discount_cash'=> !empty($v['member_discount_cash'])?$v['member_discount_cash']:0,
                'balance_discount_cash'=> !empty($v['balance_discount_cash'])?$v['balance_discount_cash']:0,
            ];

            $res = $this->dataAdd($insert);

            if($res!=1){

                return ['code'=>500,'msg'=>'下单失败'];
            }
            //减少库存 增加销量
            $res = $goods_model->setOrDelStock($v['service_id'],$v['num']);

            if(!empty($res['code'])){

                return $res;
            }

        }

        return true;

    }







}