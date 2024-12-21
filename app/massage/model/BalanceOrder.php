<?php
namespace app\massage\model;

use app\adapay\model\Member;
use app\BaseModel;
use app\member\model\Level;
use think\facade\Db;

class BalanceOrder extends BaseModel
{
    //定义表名
    protected $name = 'massage_service_balance_order_list';

//
    protected $append = [

        'nick_name',


    ];


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-11 15:47
     * @功能说明:用户昵称
     */
    public function getNickNameAttr($value,$data){

        if(!empty($data['user_id'])){

            $user_model = new User();

            return $user_model->where(['id'=>$data['user_id']])->value('nickName');

        }
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:04
     * @功能说明:添加
     */
    public function dataAdd($data){

        $data['status'] = 1;

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
    public function dataList($dis,$page=10){

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

            $user_model = new User();

            $water_model= new BalanceWater();

            $user = $user_model->dataInfo(['id'=>$order['user_id']]);

            Db::startTrans();

            $update = [

                'transaction_id' => $transaction_id,

                'status'         => 2,

                'pay_time'       => time(),

                'now_balance'    => $order['true_price']+$user['balance']

            ];
            //修改订单信息
            $res = $this->dataUpdate(['id'=>$order['id'],'transaction_id'=>''],$update);

            if($res==0){

                Db::rollback();

            }

            $is_add = $order['type']==3?0:1;

            $type   = $is_add==1?1:5;
            //添加余额流水
            $res = $water_model->updateUserBalance($order,$type,$is_add);

            if($res==0){

                Db::rollback();

            }

            $integral_model = new Integral();

            $coach_model = new Coach();

            $integral_record = $integral_model->dataInfo(['order_id'=>$order['id'],'type'=>0,'status'=>-1]);
            //分销返佣积分
            if(!empty($integral_record)){

                $integral_model->dataUpdate(['id'=>$integral_record['id']],['status'=>1]);

                $integral = $integral_record['integral'];

                $coach_model->where(['id'=>$order['coach_id']])->update(['integral'=>Db::Raw("integral+$integral")]);
            }

            $comm_model = new Commission();

            $comm_data = $comm_model->dataInfo(['order_id'=>$order['id'],'status'=>-1,'type'=>7]);
            //分销返佣佣金
            if(!empty($comm_data)){

                $integral_model->dataUpdate(['order_id'=>$order['id'],'type'=>1,'status'=>-1],['status'=>1]);

                $comm_model->dataUpdate(['id'=>$comm_data['id']],['status'=>1]);
            }

            if($is_add==1){

                $level_model = new Level();
                //如果有会员插件 需要加成长值
                $level_model->levelUp($order,4);
            }

            $member_model = new Member();
            //分账
            $member_model->adaPayBalanceMyself($order);

            Db::commit();

            Db::startTrans();
            //分销返佣佣金
            if(!empty($comm_data)){

                $comm_model->dataUpdate(['id'=>$comm_data['id']],['status'=>2,'cash_time'=>time()]);

                $cash = $comm_data['cash'];

                $coach_model->where(['id'=>$order['coach_id']])->update(['balance_cash'=>Db::Raw("balance_cash+$cash")]);
                //技师服务费
                $water_model = new CoachWater();

                $res = $water_model->updateCash($comm_data['uniacid'],$order['coach_id'],$cash,1,1);

                if($res==0){

                    Db::rollback();
                }
            }

            Db::commit();

        }

        return true;

    }





}