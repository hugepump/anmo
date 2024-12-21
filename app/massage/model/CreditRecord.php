<?php
namespace app\massage\model;

use app\BaseModel;
use think\facade\Db;

class CreditRecord extends BaseModel
{
    //定义表名
    protected $name = 'massage_coach_credit_record';




    public function getValueAttr($value,$data){

        return !empty($value)?floatval($value):$value;
    }



    public function getOrderPriceAttr($value,$data){

        return !empty($value)?floatval($value):$value;
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

        $data = $this->where($dis)->order('status desc,id desc')->paginate($page)->toArray();

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
     * @param $city_id
     * @param $uniacid
     * @功能说明:核算信用分
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-09-26 10:28
     */
    public function getCoachValue($uniacid,$alh,$credit_config,$city_id=0){

        $arr = [
            1 => 'order_empty',
            2 => 'add_order_empty',
            3 => 'time_long_empty',
            4 => 'repeat_order_empty',
            5 => 'good_evaluate_empty',
            6 => 'refund_order_empty',
            7 => 'refuse_order_empty',
            8 => 'bad_evaluate_empty',
        ];
        //获取所有不需要周期清零的类型
        foreach ($arr as $k=>$v){

            if($credit_config[$v]==0){

                $type_arr[]=$k;
            }
        }

        $config_model = new Config();

        $config = $config_model->dataInfo(['uniacid'=>$uniacid]);

        $level_cycle = $config['level_cycle'];

        $type = $config['is_current'];

        if($level_cycle==1){

            if($type==1){
                //每周
                $start_time = strtotime("this week Monday");

                $end_time   = strtotime("this week Sunday")+86400-1;
            }else{
                //上周
                $start_time = strtotime("last week Monday");

                $end_time   = strtotime("last week Sunday")+86400-1;
            }
        }elseif ($level_cycle==2){

            if($type==1){
                //本月
                $start_time = mktime(0, 0, 0, date('m'), 1, date('Y'));

                $end_time   = mktime(23, 59, 59, date('m'), date('t'), date('Y'));

            }else{
                //上月
                $start_time = mktime(0, 0, 0, date('m') - 1, 1, date('Y'));

                $end_time   = mktime(23, 59, 59, date('m') - 1, date('t', $start_time), date('Y'));

            }
        }elseif ($level_cycle==3){
            //本季度|上季度
            $quarter = $type==1 ? ceil((date('n'))/3) : ceil((date('n'))/3)-1;//获取当前季度

            $start_time= mktime(0, 0, 0,$quarter*3-2,1,date('Y'));

            $end_time  = mktime(0, 0, 0,$quarter*3+1,1,date('Y'));

        }elseif ($level_cycle==4){

            if($type==1){
                //本年
                $start_time = mktime(0, 0, 0, 1, 1, date('Y'));

                $end_time   = mktime(23, 59, 59, 12, 31, date('Y'));

            }else{
                //去年
                $year = date('Y') - 1;

                $start_time = mktime(0, 0, 0, 1, 1, $year);

                $end_time   = mktime(23, 59, 59, 12, 31, $year);

            }

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
                //下半月
                if($day>15){

                    $start_time = strtotime(date ('Y-m-01', time()));

                    $end_time   = strtotime(date('Y-m-16', time()))-1;

                }else{

                    $start_time = strtotime(date ('Y-m-16', strtotime('-1 month')));

                    $end_time   = strtotime(date('Y-m-t', strtotime('-1 month')))+86399;
                }
            }
        }

        $dis = [

            'a.status' => 2,

            'a.auth_status' => 2,

            'a.uniacid' => $uniacid
        ];

        if(!empty($city_id)){

            $dis['a.city_id'] = $city_id;
        }

        $where = [];

        if(!empty($start_time)){

            $where[] = ['b.create_time','between',"$start_time,$end_time"];

            $where[] = ['b.type','in',$type_arr];
        }

        $data = Db::name('massage_service_coach_list')->alias('a')
                ->join('massage_coach_credit_record b','a.id = b.coach_id')
                ->where($dis)
                ->where(function ($query) use ($where){
                    $query->whereOr($where);
                })
                ->field(['b.*,sum(b.value) as total_value,a.sh_time',$alh])
                ->group('a.id')
                ->select()
                ->toArray();

        return $data;
    }


    /**
     * @param $city_id
     * @param $uniacid
     * @功能说明:修改技师排序的信用分
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-09-26 10:29
     */
    public function updateCoachValue($uniacid,$alh,$credit_config,$city_id=0){

        $keys = 'updateCoachValue_key_updateCoachValue';

        if(getCache($keys,$uniacid)){

            //return true;
        }

        setCache($keys,1,3,$uniacid);

        $dis = [

            'a.status' => 2,

            'a.auth_status' => 2,

            'a.uniacid' => $uniacid
        ];

        if(!empty($city_id)){

            $dis['a.city_id'] = $city_id;
        }

        $coach_model = new Coach();

        $list = $coach_model->alias('a')
                ->where($dis)
               ->field(['a.id as coach_id,a.coach_name',$alh])
               ->select()
               ->toArray();

        if(!empty($list)&&!empty($credit_config['distance'])){

            foreach ($list as $key=>$value){

                $credit_top = 999;

                foreach ($credit_config['distance'] as $k=>$v){

                    if($value['distance_data']<$v*1000){

                        $credit_top = $k;

                        break;
                    }
                }

                $inserts[$key] = [

                    'id'           => $value['coach_id'],

                    'credit_top'   => $credit_top,

                    'coach_name'   => $value['coach_name'],

                    'credit_value' => 0
                ];
            }

            $coach_model->saveAll($inserts);
        }

        $list = $this->getCoachValue($uniacid,$alh,$credit_config,$city_id);

        if(!empty($list)){

            foreach ($list as $key=>$value){
                //新人信用分
             //   $new_protect_value = $value['sh_time']>time()-$credit_config['new_protect_day']*86400?$credit_config['new_protect_value']:$credit_config['init_value'];

                $insert[$key] = [

                    'id'           => $value['coach_id'],

                    'credit_value' => $value['total_value'],
                ];
            }

            $coach_model->saveAll($insert);
        }

        return true;
    }




    /**
     * @param $city_id
     * @param $uniacid
     * @功能说明:核算信用分
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-09-26 10:28
     */
    public function getSingleCoachValue($uniacid,$coach_id){

        $config_model = new CreditConfig();

        $credit_config = $config_model->dataInfo(['uniacid'=>$uniacid]);

        if($credit_config['status']==0){

            return 0;
        }

        $arr = [
            1 => 'order_empty',
            2 => 'add_order_empty',
            3 => 'time_long_empty',
            4 => 'repeat_order_empty',
            5 => 'good_evaluate_empty',
            6 => 'refund_order_empty',
            7 => 'refuse_order_empty',
            8 => 'bad_evaluate_empty',
        ];
        //获取所有不需要周期清零的类型
        foreach ($arr as $k=>$v){

            if($credit_config[$v]==0){

                $type_arr[]=$k;
            }
        }

        $config_model = new Config();

        $config = $config_model->dataInfo(['uniacid'=>$uniacid]);

        $level_cycle = $config['level_cycle'];

        $type = $config['is_current'];

        if($level_cycle==1){

            if($type==1){
                //每周
                $start_time = strtotime("this week Monday");

                $end_time   = strtotime("this week Sunday")+86400-1;
            }else{
                //上周
                $start_time = strtotime("last week Monday");

                $end_time   = strtotime("last week Sunday")+86400-1;
            }
        }elseif ($level_cycle==2){

            if($type==1){
                //本月
                $start_time = mktime(0, 0, 0, date('m'), 1, date('Y'));

                $end_time   = mktime(23, 59, 59, date('m'), date('t'), date('Y'));

            }else{
                //上月
                $start_time = mktime(0, 0, 0, date('m') - 1, 1, date('Y'));

                $end_time   = mktime(23, 59, 59, date('m') - 1, date('t', $start_time), date('Y'));

            }
        }elseif ($level_cycle==3){
            //本季度|上季度
            $quarter = $type==1 ? ceil((date('n'))/3) : ceil((date('n'))/3)-1;//获取当前季度

            $start_time= mktime(0, 0, 0,$quarter*3-2,1,date('Y'));

            $end_time  = mktime(0, 0, 0,$quarter*3+1,1,date('Y'));

        }elseif ($level_cycle==4){

            if($type==1){
                //本年
                $start_time = mktime(0, 0, 0, 1, 1, date('Y'));

                $end_time   = mktime(23, 59, 59, 12, 31, date('Y'));

            }else{
                //去年
                $year = date('Y') - 1;

                $start_time = mktime(0, 0, 0, 1, 1, $year);

                $end_time   = mktime(23, 59, 59, 12, 31, $year);

            }

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
                //下半月
                if($day>15){

                    $start_time = strtotime(date ('Y-m-01', time()));

                    $end_time   = strtotime(date('Y-m-16', time()))-1;

                }else{

                    $start_time = strtotime(date ('Y-m-16', strtotime('-1 month')));

                    $end_time   = strtotime(date('Y-m-t', strtotime('-1 month')))+86399;
                }
            }
        }

        $dis = [

            'a.id' => $coach_id,
        ];

        $where = [];

        if(!empty($start_time)){

            $where[] = ['b.create_time','between',"$start_time,$end_time"];

            $where[] = ['b.type','in',$type_arr];
        }

        $coach_model = new Coach();

        $data = $coach_model->alias('a')
            ->join('massage_coach_credit_record b','a.id = b.coach_id')
            ->where($dis)
            ->where(function ($query) use ($where){
                $query->whereOr($where);
            })
            ->field('b.id')
            ->group('a.id')
            ->sum('b.value');

        $init_value = $credit_config['init_value'];

        $new_protect_value = $credit_config['new_protect_value'];

        $new_protect_day = time()-$credit_config['new_protect_day']*86400;

        $coach_model = new Coach();

        $sh_time = $coach_model->where(['id'=>$coach_id])->value('sh_time');

        $init_value = $sh_time>$new_protect_day?$new_protect_value:$init_value;

        return round($data+$init_value,4);
    }















}