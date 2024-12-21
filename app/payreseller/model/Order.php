<?php
namespace app\payreseller\model;

use app\adapay\model\Member;
use app\BaseModel;
use app\massage\model\Commission;
use app\massage\model\DistributionList;
use app\massage\model\User;
use app\massage\model\UserWater;
use think\facade\Db;

class Order extends BaseModel
{
    //定义表名
    protected $name = 'massage_payreseller_order_list';




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
     * @DataTime: 2021-03-19 16:08
     * @功能说明:开启默认
     */
    public function updateOne($id){

        $user_id = $this->where(['id'=>$id])->value('user_id');

        $res = $this->where(['user_id'=>$user_id])->where('id','<>',$id)->update(['status'=>0]);

        return $res;
    }


    /**
     * @param $order_code
     * @param $transaction_id
     * @功能说明:订单回调
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-01-08 10:16
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

            $reseller_model = new DistributionList();

            $comm_model     = new Commission();

            $reseller_model->dataUpdate(['id'=>$order['reseller_id']],['status'=>2,'reseller_level'=>$order['type'],'sh_time'=>time()]);

            Db::commit();

            Db::startTrans();

            $comm_data = $comm_model->dataInfo(['order_id'=>$order['id'],'status'=>-1,'type'=>15]);
            //分销返佣佣金
            if(!empty($comm_data)){

                $res = $comm_model->dataUpdate(['id'=>$comm_data['id']],['status'=>2,'cash_time'=>time()]);

                if($res==0){

                    Db::rollback();

                    return 0;
                }

                $cash = $comm_data['cash'];

                $water_model = new UserWater();

                $res = $water_model->updateCash($comm_data['uniacid'],$comm_data['top_id'],$cash,1,$comm_data['order_id'],$comm_data['id'],$comm_data['type']);

                if($res==0){

                    Db::rollback();

                    return 0;
                }
            }

            $member = new Member();
            //如果使用了分账
            $member->adaPayResellerMyself($order);


            Db::commit();
        }

        return true;

    }






}