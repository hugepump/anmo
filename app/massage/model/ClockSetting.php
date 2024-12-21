<?php
namespace app\massage\model;

use app\BaseModel;
use think\facade\Db;

class ClockSetting extends BaseModel
{
    //定义表名
    protected $name = 'massage_add_clock_setting';



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
     * @DataTime: 2023-02-17 13:44
     * @功能说明:获取技师提成佣金
     * 1678435200
     * 1678438800
     */
    public function getCoachBalance($order,$coach_balance=0){

        $order_model = new Order();

        $add_order = $order_model->where(['pay_type'=>1,'add_pid'=>$order['add_pid'],'is_add'=>1])->where('id','<>',$order['id'])->select()->toArray();
        //取消未支付的
        if(!empty($add_order)){

            foreach ($add_order as $value){

                $order_model->cancelOrder($value);
            }
        }

        if(empty($order['coach_id'])){

            return $order['coach_balance'];
        }

        if($order['is_add']!=1){

            return $order['coach_balance'];
        }

        $config_model = new MassageConfig();
        //如果有代理商
        if(!empty($order['admin_id'])){

            $admin_model = new Admin();

            $admin_clock_cash_status = $admin_model->where(['id'=>$order['admin_id']])->value('clock_cash_status');

            $clock_cash_type = $admin_model->where(['id'=>$order['admin_id']])->value('clock_cash_type');
        }

        $times = $order_model->addOrderTimes($order['add_pid']);
        //代理商
        if(!empty($admin_clock_cash_status)&&$admin_clock_cash_status==1){

            $balance = $this->where(['uniacid'=>$order['uniacid'],'admin_id'=>$order['admin_id'],'type'=>$clock_cash_type])->where('times','<=',$times)->order('times desc,id desc')->find();

            if(!empty($balance)){

                if($clock_cash_type==1){

                    $balance->balance = $balance->balance+$coach_balance;
                }

                return $balance->balance>100?100:$balance->balance;
            }
        }
        //平台
        $config = $config_model->dataInfo(['uniacid'=>$order['uniacid']]);

        if($config['clock_cash_status']!=1){

            return $order['coach_balance'];
        }

        $balance = $this->where(['uniacid'=>$order['uniacid'],'admin_id'=>0,'type'=>$config['clock_cash_type']])->where('times','<=',$times)->order('times desc,id desc')->find();

        if(empty($balance)){

            return $order['coach_balance'];

        }else{

            $balance = $balance->toArray();
        }

        $balance = !empty($balance)?$balance['balance']:0;

        if($config['clock_cash_type']==1){

            $balance = $balance+$coach_balance;
        }

        return $balance>100?100:$balance;
    }








}