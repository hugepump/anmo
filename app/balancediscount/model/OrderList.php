<?php
namespace app\balancediscount\model;

use app\BaseModel;
use app\massage\model\Coach;
use app\massage\model\CoachWater;
use app\massage\model\Commission;
use app\massage\model\CouponRecord;
use app\massage\model\Integral;
use app\massage\model\User;
use think\facade\Db;

class OrderList extends BaseModel
{
    //定义表名
    protected $name = 'massage_balance_discount_order_list';




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

            $update = [

                'transaction_id' => $transaction_id,

                'pay_type'       => 2,

                'pay_time'       => time(),

                'over_time'      => strtotime("+{$order['month']} months"),
            ];

            $res = $this->dataUpdate(['id'=>$order['id'],'transaction_id'=>''],$update);

            if($res==0){

                Db::rollback();

                return false;
            }

            $user_card_model = new UserCard();
            //创建卡
            $res = $user_card_model->dataAdd($order);

            if($res==0){

                Db::rollback();

                return false;
            }

            $integral_model = new Integral();

            $coach_model = new Coach();

            $integral_record = $integral_model->dataInfo(['order_id'=>$order['id'],'type'=>2,'status'=>-1]);
            //分销返佣积分
            if(!empty($integral_record)){

                $integral_model->dataUpdate(['id'=>$integral_record['id']],['status'=>1]);

                $integral = $integral_record['integral'];

                $coach_model->where(['id'=>$order['coach_id']])->update(['integral'=>Db::Raw("integral+$integral")]);
            }

            $comm_model = new Commission();

            $comm_model->dataUpdate(['order_id'=>$order['id'],'type'=>25,'status'=>-1],['status'=>1]);

            Db::commit();
            //技师佣金到账
            $this->commSuccess($order['id']);
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

        $integral_model = new Integral();

        $data = $comm_model->dataInfo(['order_id'=>$order_id,'status'=>1,'type'=>25]);

        if(!empty($data)){

            Db::startTrans();

            $integral_model->dataUpdate(['order_id'=>$order_id,'type'=>3,'status'=>-1],['status'=>1]);

            $res = $comm_model->dataUpdate(['id'=>$data['id'],'status'=>1],['status'=>2,'cash_time'=>time()]);

            if($res==0){

                Db::rollback();

                cacheLpush('balance_discount_comm',$order_id,$data['uniacid']);
            }

            $water_model = new CoachWater();

            $res = $water_model->updateCash($data['uniacid'],$data['top_id'],$data['cash'],1);

            if($res==0){

                Db::rollback();

                cacheLpush('balance_discount_comm',$order_id,$data['uniacid']);
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

            $data_id = cacheLpop('balance_discount_comm',$uniacid);

            if(!empty($data_id)){

                $this->commSuccess($data_id);
            }else{

                return true;
            }
        }

        return true;
    }











}