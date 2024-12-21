<?php
namespace app\memberdiscount\model;

use app\BaseModel;
use app\massage\model\CoachWater;
use app\massage\model\Commission;
use app\massage\model\CouponRecord;
use app\massage\model\User;
use think\facade\Db;

class OrderList extends BaseModel
{
    //定义表名
    protected $name = 'massage_member_discount_order_list';




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
     * @param $order_code
     * @param $transaction_id
     * @功能说明:订单回调
     * @author chenniang
     * @DataTime: 2024-09-04 11:45
     */
    public function orderResult($order_code,$transaction_id){

        $order = $this->dataInfo(['order_code'=>$order_code,'transaction_id'=>'']);

        if(!empty($order)&&!empty($transaction_id)){

            Db::startTrans();

            $user_model = new User();

            $user_info = $user_model->dataInfo(['id'=>$order['user_id']]);

            if($user_info['member_discount_time']<time()){

                $member_take_effect_time = time();
            }else{

                $member_take_effect_time = $user_info['member_discount_time'];
            }

            $update = [

                'transaction_id' => $transaction_id,

                'pay_type'       => 2,

                'pay_time'       => time(),

                'member_take_effect_time' => $member_take_effect_time
            ];

            $res = $this->dataUpdate(['id'=>$order['id'],'transaction_id'=>''],$update);

            if($res==0){

                Db::rollback();

                return false;
            }

            if(!empty($user_info)&&$order['day']>0){

                $member_discount_time = $member_take_effect_time+$order['day']*86400;

                $res = $user_model->dataUpdate(['id'=>$order['user_id']],['member_discount_time'=>$member_discount_time,'member_discount_id'=>$order['card_id']]);

                if($res==0){

                    Db::rollback();

                    return false;
                }

                $user_info['member_discount_time'] = $member_discount_time;

                $user_info['member_discount_id'] = $order['card_id'];
            }

            $key = 'longbing_user_autograph_' . $user_info['openid'];

            $key = md5($key);

            setCache($key, $user_info, 86400*3,999999999999);

            $comm_model = new Commission();

            $comm_model->dataUpdate(['order_id'=>$order['id'],'type'=>24,'status'=>-1],['status'=>1]);

            Db::commit();
            //技师佣金到账
            $this->commSuccess($order['id']);
            //获取优惠券
            $this->getCoupon($order);
        }

        return true;
    }


    /**
     * @param $order
     * @功能说明:获取优惠券
     * @author chenniang
     * @DataTime: 2024-09-05 13:59
     */
    public function getCoupon($order){

        $coupon_model = new OrderCoupon();

        $record_model = new CouponRecord();

        $data = $coupon_model->where(['order_id'=>$order['id']])->select()->toArray();

        if(!empty($data)){

            foreach ($data as $v){

                $record_model->recordAdd($v['coupon_id'],$order['user_id'],$v['num']);
            }
        }
        return true;
    }


    /**
     * @param $order_id
     * @功能说明:佣金到账
     * @author chenniang
     * @DataTime: 2024-09-04 15:05
     */
    public function commSuccess($order_id){

        $comm_model = new Commission();

        $data = $comm_model->dataInfo(['order_id'=>$order_id,'status'=>1,'type'=>24]);

        if(!empty($data)){

            Db::startTrans();

            $res = $comm_model->dataUpdate(['id'=>$data['id'],'status'=>1],['status'=>2,'cash_time'=>time()]);

            if($res==0){

                Db::rollback();

                cacheLpush('member_discount_comm',$order_id,$data['uniacid']);
            }

            $water_model = new CoachWater();

            $res = $water_model->updateCash($data['uniacid'],$data['top_id'],$data['cash'],1);

            if($res==0){

                Db::rollback();

                cacheLpush('member_discount_comm',$order_id,$data['uniacid']);
            }

            Db::commit();
        }

        return true;
    }


    /**
     * @author chenniang
     * @DataTime: 2024-09-04 15:44
     * @功能说明：处理队列里面的佣金
     */
    public function listCommSuccess($uniacid,$i=10){

        $num = 0;

        while ($num<$i){

            $num++;

            $data_id = cacheLpop('member_discount_comm',$uniacid);

            if(!empty($data_id)){

                $this->commSuccess($data_id);

            }else{

                return true;
            }
        }

        return true;
    }











}