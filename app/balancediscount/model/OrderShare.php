<?php
namespace app\balancediscount\model;

use app\BaseModel;
use app\massage\model\Admin;
use think\facade\Db;

class OrderShare extends BaseModel
{
    //定义表名
    protected $name = 'massage_balance_discount_order_share';




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
     * @param $order_id
     * @param $uniacid
     * @param $list
     * @功能说明:
     * @author chenniang
     * @DataTime: 2024-09-18 14:08
     */
    public function orderShareAdd($order_id,$uniacid,$list,$admin_id,$p_order_id=0,$type=1){

        if(empty($list)){

            return true;
        }

        $p_order_id = $type==1?$order_id:$p_order_id;

        if($type==1){

            if(!empty($admin_id)){

                $admin_model = new Admin();

                $admin = $admin_model->where(['id'=>$admin_id,'status'=>1,'agent_coach_auth'=>1])->count();

                if($admin==0){

                    $admin_id = 0;
                }
            }

            $arr = getConfigSettingArr($uniacid,['balance_discount_coach_balance','balance_discount_admin_balance']);

            $balance_discount_coach_balance = $arr['balance_discount_coach_balance'];

            $balance_discount_admin_balance = !empty($admin_id)?$arr['balance_discount_admin_balance']:0;

        }else{

            $data = $this->dataInfo(['p_order_id'=>$p_order_id,'status'=>2]);

            $balance_discount_coach_balance = $data['coach_balance'];

            $balance_discount_admin_balance = $data['admin_balance'];
        }

        foreach ($list as $key=>$value){

            $insert = [

                'uniacid' => $uniacid,

                'order_id'=> $order_id,

                'p_order_id'=> $p_order_id,

                'card_id' => $value['id'],

                'coach_balance' => $balance_discount_coach_balance,

                'admin_balance' => $balance_discount_admin_balance,

                'company_balance' => 100-$balance_discount_coach_balance-$balance_discount_admin_balance,

                'status' => 1,

                'type' => $type,

                'cash' => $value['total_cash'],

                'discount' => $value['discount'],

                'service_cash' => $value['true_total_cash'],

                'value_cash' => $value['value_cash'],

                'car_cash' => $value['discount_car_cash'],

                'discount_cash'     => $value['discount_cash'],

                'coach_share_cash'  => $balance_discount_coach_balance/100*$value['discount_cash'],

                'admin_share_cash'  => $balance_discount_admin_balance/100*$value['discount_cash'],

                'company_share_cash'=> (100-$balance_discount_coach_balance-$balance_discount_admin_balance)/100*$value['discount_cash'],
            ];

            $this->dataAdd($insert);
        }

        return true;
    }


    /**
     * @param $order_id
     * @功能说明:订单储值折扣详情
     * @author chenniang
     * @DataTime: 2024-09-19 15:57
     */
    public function orderShareData($order_id,$type=1){

        $dis[] = ['a.p_order_id','=',$order_id];

       // $dis[] = ['a.type','=',$type];

        $dis[] = ['a.status','in',[-2,2]];

        $data = $this->alias('a')
                ->join('massage_balance_discount_user_card b','a.card_id = b.id')
                ->where($dis)
                ->field('b.title,b.discount,sum(a.value_cash) as value_cash,sum(a.cash) as cash,sum(a.discount_cash) as discount_cash')
                ->group('a.card_id')
                ->order('b.discount,a.id desc')
                ->select()
                ->toArray();

        $arr['balance_discount_list']= $data;

        $arr['balance_discount_num'] = count($data);

        $arr['balance_discount_cash']= round(array_sum(array_column($data,'discount_cash')),2);

        return $arr;
    }


    /**
     * @param $order_id
     * @param $service_cash
     * @param $car_cash
     * @功能说明:储值折扣卡支付的订单退款
     * @author chenniang
     * @DataTime: 2024-09-20 14:15
     */
    public function refundUpdateOrderBalanceDiscount($order_id,$refund_id,$user_id,$service_cash,$car_cash){

        if($service_cash==0&&$car_cash==0){

            return true;
        }

        $dis = [

            'p_order_id' => $order_id,

            'status'   => 2,

          //  'type'     => 1
        ];

        $list = $this->where($dis)->select()->toArray();

        if(empty($list)){

            return false;
        }

        $water_model = new CardWater();

        Db::startTrans();

        foreach ($list as $value){

            $refund_cash = 0;

            $i = 0;
            //退车费
            if($value['car_cash']>0&&$car_cash>0){

                $i=1;

                $true_car_cash = $car_cash>$value['car_cash']?$value['car_cash']:$car_cash;

                $car_cash -= $true_car_cash;

                $value['car_cash'] -= $true_car_cash;

                $value['cash'] -= $true_car_cash;

                $value['value_cash'] -= $true_car_cash;

                $refund_cash += $true_car_cash;
            }
            //退服务费
            if($value['service_cash']>0&&$service_cash>0){

                $i=1;

                $true_service_cash = $service_cash>$value['value_cash']?$value['value_cash']:$service_cash;

                $service_cash -= $true_service_cash;

                $value['service_cash'] = round($value['service_cash']-$true_service_cash/$value['discount']*10,2);

                $value['cash'] = round($value['cash']-$true_service_cash/$value['discount']*10,2);

                $value['value_cash']    = round($value['value_cash']-$true_service_cash,2);

                $value['discount_cash'] = $value['service_cash']- $value['value_cash'];

                $value['coach_share_cash'] = $value['discount_cash']*$value['coach_balance']/100;

                $value['admin_share_cash'] = $value['discount_cash']*$value['admin_balance']/100;

                $value['company_share_cash'] = $value['discount_cash']*$value['company_balance']/100;

                $refund_cash+=$true_service_cash;
            }

            if($i==1){

                if($value['value_cash']<=0){

                    $value['status'] = -2;
                }

                $res = $this->where(['id'=>$value['id']])->update($value);

                if($res==0){

                    Db::rollback();

                    return false;
                }
                //退回余额
                $res = $water_model->updateCash($value['uniacid'],$value['card_id'],$refund_cash,1,$order_id,3,$user_id,$refund_id);

                if($res==0){

                    Db::rollback();

                    return false;
                }
            }
        }

        Db::commit();

        return true;
    }



    








}