<?php
namespace app\massage\model;

use app\BaseModel;
use think\facade\Db;

class CoachLevel extends BaseModel
{
    //定义表名
    protected $name = 'massage_service_coach_level';



    public function getOnlineChangeIntegralAttr($value,$data){

        if(isset($value)){

            return floatval($value);
        }
    }


    public function getAddBasisBalanceAttr($value){

        if(isset($value)){

            return round($value,2);
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
     * @DataTime: 2020-09-29 11:04
     * @功能说明:添加
     */
    public function dataInfo($dis){

        $res = $this->where($dis)->find();

        return !empty($res)?$res->toArray():[];

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

        $data = $this->where($dis)->order('time_long desc,id desc')->paginate($page)->toArray();

        return $data;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:43
     * @功能说明:
     */
    public function dataInit($uniacid){
        //查询有无记录
        $info = $this->where(['uniacid'=>$uniacid])->order('date_str desc')->find();
        //没有就用订单的第一天
        if(empty($info)){

            $order_model = new Order();

            $start = $order_model->where(['uniacid'=>$uniacid])->min('create_time');

            $start = !empty($start)?$start-86400:0;

        }else{

            $start = $info['date_str'];

        }
        //没有记录就用今天
        $start = !empty($start)?$start:time();
        //确保零点
        $start = strtotime(date('Y-m-d',$start));

        $eve   = strtotime(date('Y-m-d',time())) - $start;

        $eve   = $eve/86400;

        if(!empty($eve)){

            for ($i=1;$i<=$eve;$i++){

                $insert = [

                    'uniacid' => $uniacid,

                    'date'    => date('Y-m-d',$start+$i*86400),

                    'date_str'=> $start+$i*86400
                ];

                $this->dataAdd($insert);

            }

        }

        return true;

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-11-04 15:50
     * @功能说明:获取最低时长
     */
    public function getMinTimeLong($caoch_id,$level_cycle,$type=1){

        $order_model = new Order();

        $dis = [

            'coach_id' => $caoch_id,

            'pay_type' => 7
        ];
        //每周
        if($level_cycle==1){

            $week = $type==1?'week':'last week';

            $price = $order_model->where($dis)->whereTime('create_time',$week)->sum('true_time_long');
            //每月
        }elseif ($level_cycle==2){

            $month = $type==1?'month':'last month';

            $price = $order_model->where($dis)->whereTime('create_time',$month)->sum('true_time_long');
            //每季度
        }elseif ($level_cycle==3){

            $quarter = $type==1 ? ceil((date('n'))/3) : ceil((date('n'))/3)-1;//获取当前季度

            $start_quarter= mktime(0, 0, 0,$quarter*3-2,1,date('Y'));

            $end_quarter  = mktime(0, 0, 0,$quarter*3+1,1,date('Y'));

            $price = $order_model->where($dis)->where('create_time','between',"$start_quarter,$end_quarter")->sum('true_time_long');
            //每年
        }elseif ($level_cycle==4){

            $year = $type==1?'year':'last year';

            $price = $order_model->where($dis)->whereTime('create_time',$year)->sum('true_time_long');

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

            $price = $order_model->where($dis)->where('create_time','between',"$start_time,$end_time")->sum('true_time_long');
        }else{
            //不限
            $price = $order_model->where($dis)->sum('true_time_long');

        }

        return $price;

    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-11-04 15:50
     * @功能说明:获取最低业绩
     */
    public function getMinPrice($caoch_id,$level_cycle,$add = 0,$type=1,$broker_id=0){

        $order_model = new Order();

        $dis = [

            'coach_id' => $caoch_id,

            'pay_type' => 7
        ];

        if(!empty($broker_id)){

            $dis['broker_id'] = $broker_id;
        }

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
     * @DataTime: 2022-11-04 15:50
     * @功能说明:获取技师在线时长
     */
    public function getCoachOnline($caoch_id,$level_cycle,$type=1){

        $log_model = new WorkLog();
        //初始化每一天的工作时间
        $log_model->updateTimeOnline($caoch_id,1);

        $dis = [

            'coach_id' => $caoch_id,
        ];
        //每周
        if($level_cycle==1){

            $week = $type==1?'week':'last week';

            $time_long = $log_model->where($dis)->whereTime('create_time',$week)->sum('time');
            //每月
        }elseif ($level_cycle==2){

            $month = $type==1?'month':'last month';

            $time_long = $log_model->where($dis)->whereTime('create_time',$month)->sum('time');
            //每季度
        }elseif ($level_cycle==3){

            $quarter = $type==1 ? ceil((date('n'))/3) : ceil((date('n'))/3)-1;//获取当前季度

            $start_quarter = mktime(0, 0, 0,$quarter*3-2,1,date('Y'));

            $end_quarter   = mktime(0, 0, 0,$quarter*3+1,1,date('Y'));

            $time_long = $log_model->where($dis)->where('create_time','between',"$start_quarter,$end_quarter")->sum('time');
            //每年
        }elseif ($level_cycle==4){

            $year = $type==1?'year':'last year';

            $time_long = $log_model->where($dis)->whereTime('create_time',$year)->sum('time');

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

            $time_long = $log_model->where($dis)->where('create_time','between',"$start_time,$end_time")->sum('time');
        }else{
            //不限
            $time_long = $log_model->where($dis)->sum('time');
        }
        $coach_time_model = new CoachTimeList();
        //休息时间
        $rest_time_long = $coach_time_model->getCoachRestTimeLong($caoch_id,$level_cycle,$type);
        //4772280.0
        $time_long =  ($time_long-$rest_time_long)>0?$time_long-$rest_time_long:0;

        return floor($time_long/3600);
    }





    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-11-04 15:50
     * @功能说明:获取最低业绩
     */
    public function getMinIntegral($caoch_id,$level_cycle,$type=1){

        $order_model = new Integral();

        $dis[] = ['coach_id','=',$caoch_id];

        $dis[] = ['status','=',1];

        $dis[] = ['type','in',[0,2]];
        //每周
        if($level_cycle==1){

            $week = $type==1?'week':'last week';

            $price = $order_model->where($dis)->whereTime('create_time',$week)->sum('integral');
            //每月
        }elseif ($level_cycle==2){

            $month = $type==1?'month':'last month';

            $price = $order_model->where($dis)->whereTime('create_time',$month)->sum('integral');
            //每季度
        }elseif ($level_cycle==3){

            $quarter = $type==1 ? ceil((date('n'))/3) : ceil((date('n'))/3)-1;//获取当前季度

            $start_quarter= mktime(0, 0, 0,$quarter*3-2,1,date('Y'));

            $end_quarter  = mktime(0, 0, 0,$quarter*3+1,1,date('Y'));

            $price = $order_model->where($dis)->where('create_time','between',"$start_quarter,$end_quarter")->sum('integral');
            //每年
        }elseif ($level_cycle==4){

            $year = $type==1?'year':'last year';

            $price = $order_model->where($dis)->whereTime('create_time',$year)->sum('integral');
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

            $price = $order_model->where($dis)->where('create_time','between',"$start_time,$end_time")->sum('integral');
        }else{
            //不限
            $price = $order_model->where($dis)->sum('integral');
        }

        return $price;
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

        $list = $this->where($dis)->order('time_long,id desc')->select()->toArray();

        $key = 'coach_level_key';

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
    public function getMinCount($caoch_id,$level_cycle,$add = 0,$type=1){

        $order_model = new Order();

        $dis = [

            'coach_id' => $caoch_id,

            'pay_type' => 7
        ];

        if($add==1){

            $dis['is_add'] = $add;
        }
        //每周
        if($level_cycle==1){

            $week = $type==1?'week':'last week';

            $price = $order_model->where($dis)->whereTime('create_time',$week)->count();
            //每月
        }elseif ($level_cycle==2){

            $month = $type==1?'month':'last month';

            $price = $order_model->where($dis)->whereTime('create_time',$month)->count();
            //每季度
        }elseif ($level_cycle==3){

            $quarter = $type==1 ? ceil((date('n'))/3) : ceil((date('n'))/3)-1;//获取当前季度

            $start_quarter= mktime(0, 0, 0,$quarter*3-2,1,date('Y'));

            $end_quarter  = mktime(0, 0, 0,$quarter*3+1,1,date('Y'));

            $price = $order_model->where($dis)->where('create_time','between',"$start_quarter,$end_quarter")->count();
            //每年
        }elseif ($level_cycle==4){

            $year = $type==1?'year':'last year';

            $price = $order_model->where($dis)->whereTime('create_time',$year)->count();
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

            $price = $order_model->where($dis)->where('create_time','between',"$start_time,$end_time")->count();
        }else{
            //不限
            $price = $order_model->where($dis)->count();

        }

        return $price;

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-11-04 15:50
     * @功能说明:获取技师在周期内的评分
     */
    public function getCoachStar($caoch_id,$level_cycle,$type=1){

        $order_model = new Comment();

        $dis[] = ['coach_id','=',$caoch_id];

        $dis[] = ['status','>',-1];
        //每周
        if($level_cycle==1){

            $week = $type==1?'week':'last week';

            $price = $order_model->where($dis)->whereTime('create_time',$week)->avg('star');
            //每月
        }elseif ($level_cycle==2){

            $month = $type==1?'month':'last month';

            $price = $order_model->where($dis)->whereTime('create_time',$month)->avg('star');
            //每季度
        }elseif ($level_cycle==3){

            $quarter = $type==1 ? ceil((date('n'))/3) : ceil((date('n'))/3)-1;//获取当前季度

            $start_quarter= mktime(0, 0, 0,$quarter*3-2,1,date('Y'));

            $end_quarter  = mktime(0, 0, 0,$quarter*3+1,1,date('Y'));

            $price = $order_model->where($dis)->where('create_time','between',"$start_quarter,$end_quarter")->avg('star');
            //每年
        }elseif ($level_cycle==4){

            $year = $type==1?'year':'last year';

            $price = $order_model->where($dis)->whereTime('create_time',$year)->avg('star');

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

            $price = $order_model->where($dis)->where('create_time','between',"$start_time,$end_time")->avg('star');
        }else{
            //不限
            $price = $order_model->where($dis)->avg('star');

        }

        return !empty($price)?round($price,1):5;

    }






}