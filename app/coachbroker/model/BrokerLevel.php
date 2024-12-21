<?php
namespace app\coachbroker\model;

use app\BaseModel;
use app\massage\model\Coach;
use app\massage\model\Commission;
use app\massage\model\Config;
use app\massage\model\DistributionList;
use app\massage\model\Order;
use app\massage\model\RefundOrder;
use app\massage\model\User;
use think\facade\Db;

class BrokerLevel extends BaseModel
{



    protected $name = 'massage_broker_level';



    public function getTotalPerformanceAttr($value,$data){

        if(isset($value)){

            return floatval($value);
        }
    }


    public function getBalanceAttr($value,$data){

        if(isset($value)){

            return floatval($value);
        }
    }


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

        $data = $this->where($dis)->order('inv_num desc,id desc')->paginate($page)->toArray();

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
     * @DataTime: 2022-11-04 17:47
     * @功能说明:初始化
     */
    public function initTop($uniacid){

        $dis = [

            'uniacid' => $uniacid,

            'status'  => 1
        ];

        $list = $this->where($dis)->order('inv_num,id desc')->select()->toArray();

        $key = 'broker_level_key';

        incCache($key,1,$this->_uniacid);

        $key_value = getCache($key,$this->_uniacid);

        if($key_value==1){

            if(!empty($list)){

                foreach ($list as $k=>$value){

                    $this->dataUpdate(['id'=>$value['id']],['top'=>$k+1]);

                }

            }
        }

        decCache($key,1,$this->_uniacid);

        return true;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-11-04 15:50
     * @功能说明:获取最低业绩
     */
    public function getMinPrice($broker_id,$level_cycle,$add = 0,$type=1){

        $order_model = new Order();

        $dis = [

            'broker_id' => $broker_id,

            'pay_type' => 7
        ];

        if($add==1){

            $dis['is_add'] = $add;
        }
        //每周
        if($level_cycle==1){

            $week = $type==1?'week':'last week';

            $price = $order_model->where($dis)->whereTime('create_time',$week)->sum('true_service_price');
            //每月
        }elseif ($level_cycle==2){

            $month = $type==1?'month':'last month';

            $price = $order_model->where($dis)->whereTime('create_time',$month)->sum('true_service_price');
            //每季度
        }elseif ($level_cycle==3){

            $quarter = $type==1 ? ceil((date('n'))/3) : ceil((date('n'))/3)-1;//获取当前季度

            $start_quarter= mktime(0, 0, 0,$quarter*3-2,1,date('Y'));

            $end_quarter  = mktime(0, 0, 0,$quarter*3+1,1,date('Y'));

            $price = $order_model->where($dis)->where('create_time','between',"$start_quarter,$end_quarter")->sum('true_service_price');
            //每年
        }elseif ($level_cycle==4){

            $year = $type==1?'year':'last year';

            $price = $order_model->where($dis)->whereTime('create_time',$year)->sum('true_service_price');
        }elseif ($level_cycle==5){

            $day = date('d',time());
            //本期
            if($type==1){
                //下半月
                if($day>15){

                    $start_time = strtotime(date ('Y-m-16', time()));

                    $end_time   = strtotime(date('Y-m-t', time()))+86399;

                }else{

                    $start_time = strtotime(date ('Y-m-01', time()));

                    $end_time   = strtotime(date('Y-m-16', time()))-1;

                }

            }else{
                //上期
                //下半月
                if($day>15){

                    $start_time = strtotime(date ('Y-m-01', time()));

                    $end_time   = strtotime(date('Y-m-16', time()))-1;

                }else{

                    $start_time = strtotime(date ('Y-m-16', strtotime('-1 month')));

                    $end_time   = strtotime(date('Y-m-t', strtotime('-1 month')))+86399;

                }

            }

            $price = $order_model->where($dis)->where('create_time','between',"$start_time,$end_time")->sum('true_service_price');
        }else{
            //不限
            $price = $order_model->where($dis)->sum('true_service_price');

        }

        return round($price,2);

    }
    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-08 09:58
     * @功能说明:获取技师等级
     */
    public function getCoachLevel($broker_id, $uniacid)
    {

        $config_model = new Config();

        $coach_model  = new Coach();

        $config = $config_model->dataInfo(['uniacid'=>$uniacid]);

        $level_cycle = $config['level_cycle'];

        $is_current  = $config['is_current'];

        $cash = $this->getMinPrice($broker_id,$level_cycle,0,1);

        $inv_num = $coach_model->where(['broker_id'=>$broker_id,'status'=>2])->count();

        $level   = $this->where(['uniacid' => $uniacid, 'status' => 1])->order('inv_num,id desc')->select()->toArray();

        $coach_level = [];

        if (!empty($level)) {

            foreach ($level as $key=>$value) {

                $level_inv_num = $key>0?$level[$key-1]['inv_num']:0;

                if($inv_num>=$level_inv_num&&$cash>=$value['total_performance']){

                    $coach_level = $value;

                }elseif (empty($coach_level)) {
                    //都不符合给一个最低都等级
                    $coach_level = $value;
                }
            }
        }

        return !empty($coach_level)?$coach_level : [];
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-12-22 13:51
     * @功能说明:获取经纪人的提成比例
     */
    public function getBrokerBalance($broker_id,$uniacid){

        $broker_cash_type = getConfigSetting($uniacid,'broker_cash_type');

        $broker_model = new CoachBroker();

        if($broker_cash_type==0){

            $balance = $broker_model->where(['id'=>$broker_id])->value('balance');

            if($balance==-1){
                //固定比例
                $balance = getConfigSetting($uniacid,'coach_agent_balance');
            }
        }else{
            //浮动比例
            $level = $this->getCoachLevel($broker_id,$uniacid);

            $balance = !empty($level)?$level['balance']:0;
        }

        return $balance;
    }










}