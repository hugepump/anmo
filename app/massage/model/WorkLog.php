<?php
namespace app\massage\model;

use app\BaseModel;
use app\dynamic\model\DynamicList;
use think\facade\Db;

class WorkLog extends BaseModel
{
    //定义表名
    protected $name = 'massage_coach_work_log';




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
     * @param $coach_id
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-01 17:23
     */
    public function initData($coach_id){
//
//        $find = $this->where('create_time','<',1603445953)->find();
//
//        if(!empty($find)){
//
//            $this->where('create_time','<',1603445953)->delete();
//        }

        $coach_model = new Coach();

        $now_time = time();

        $coach = $coach_model->dataInfo(['id'=>$coach_id]);

        $dis = [

            'coach_id' => $coach_id,

            'status'   => 2
        ];

        $day_start = strtotime(date('Y-m-d'),$now_time);
        //获取最后一次同步时间
        $last_time = $this->where($dis)->max('create_time');

        $is_work = 1;

        if(!empty($last_time)&&$last_time>=$day_start-86400){

            $is_work = 0;

        }

        if($coach['is_work'] != 1||$coach['status']!=2){

            $is_work = 0;
        }
        //如果没有就用审核时间
        $last_time = !empty($last_time)&&$last_time>0?$last_time+86400:$coach['sh_time'];

        if($last_time<=0){

            return false;
        }

        $i=0;

        while ($last_time<$day_start){

            $true_last_time = $last_time;

            if($i==0){

                $ing = $this->where(['coach_id'=>$coach_id,'status'=>1])->order('id desc')->find();

                $ing = !empty($ing)?$ing->toArray():[];

                $true_last_time = !empty($ing)?$ing['true_time']:$true_last_time;
                //第一天
                $time = $this->getHeadTimeData($coach['start_time'],$coach['end_time'],$true_last_time,strtotime(date('Y-m-d',$true_last_time))+86400);

                $time = $is_work==1?$time:0;

                if(!empty($ing)){

                    $time += $ing['time'];

                   //$time_list_model = new CoachTimeList();

                   //$rest_time = $time_list_model->where(['coach_id'=>$coach_id,'status'=>0,'is_click'=>1,'is_work'=>1,'time_texts'=>date('Y-m-d',$true_last_time)])->field('SUM(time_str_end-time_str) as time_long')->find();

                   //$rest_time = $rest_time->time_long;

                   //$time = $time>$rest_time?$time:$rest_time;

                    $this->dataUpdate(['id'=>$ing['id']],['time'=>$time,'true_time'=>time(),'start_time'=>$coach['start_time'],'end_time'=>$coach['end_time'],'status'=>2]);

                    $i++;

                    $last_time += 86400;

                    continue;
                }

            }else{

                $time = $this->getDayWorkTime($coach['start_time'],$coach['end_time']);
            }

            $time = $is_work==1?$time:0;

            $insert = [

                'uniacid' => $coach['uniacid'],

                'coach_id'=> $coach_id,

                'status'  => 2,

                'date'    => date('Y-m-d',$true_last_time),

                'create_time'=> strtotime(date('Y-m-d',$true_last_time)),

                'time'       => $time,

                'true_time'  => $now_time,

                'start_time' => $coach['start_time'],

                'end_time'   => $coach['end_time'],
            ];

            $this->insert($insert);

            $i++;

            $last_time += 86400;

        }

        return true;

    }





    /**
     * @param $coach_id
     * @功能说明:修改技师是否工作时结算当前工作时间
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-02 09:59
     */
    public function updateTimeOnline($coach_id,$is_admin=0){

        if(empty($coach_id)){

            return true;
        }

        $key = $coach_id.'update_online_time_key';

        $now_time = time();

        $coach_model = new Coach();

        $coach = $coach_model->dataInfo(['id'=>$coach_id]);

        incCache($key,1,$coach['uniacid']);

        $key_value = getCache($key,$coach['uniacid']);

        $day_start = strtotime(date('Y-m-d'),$now_time);

        $key_values= getCache('update_online_time_key_key'.$coach_id,$coach['uniacid']);

        if(($key_value==1&&empty($key_values))||$is_admin!=1){

            setCache('update_online_time_key_key'.$coach_id,1,3600,$coach['uniacid']);
            //结算之前的
            $this->initData($coach_id);
            //如果当前未上班 则没有时间结算
            if($coach['status']!=2||$coach['is_work']==0||empty($coach['start_time'])){

                $is_work=0;

            }else{

                $is_work=1;
            }
            //查找当日的修改日志
            $ing = $this->dataInfo(['coach_id'=>$coach_id,'status'=>1,'create_time'=>$day_start]);

            if(!empty($ing)){

                $time = $this->getHeadTimeData($coach['start_time'],$coach['end_time'],$ing['true_time'],$now_time);

                $time = $is_work==1?$time:0;

                $this->dataUpdate(['id'=>$ing['id']],['time'=>$ing['time']+$time,'true_time'=>$now_time,'start_time'=>$coach['start_time'],'end_time'=>$coach['end_time']]);

            }else{

                $day_start = $coach['sh_time']<$day_start?$day_start:$coach['sh_time'];
                //如果没有获取当日到当前在线到时间
                $time = $this->getHeadTimeData($coach['start_time'],$coach['end_time'],$day_start,$now_time);

                $time = $is_work==1?$time:0;
                //添加工作时间
                $insert = [

                    'uniacid' => $coach['uniacid'],

                    'coach_id'=> $coach_id,

                    'date'    => date('Y-m-d',time()),

                    'create_time'=> strtotime(date('Y-m-d',time())),

                    'time'       => $time,

                    'status'     => 1,

                    'true_time'  => time(),

                    'start_time' => $coach['start_time'],

                    'end_time'   => $coach['end_time'],

                ];

                $this->dataAdd($insert);
            }

            $insert = [

                'uniacid' => $coach['uniacid'],

                'coach_id'=> $coach_id,

                'date'    => date('Y-m-d',time()),

                'create_time'=> time(),

                'time'       => $time,

                'start_time' => $coach['start_time'],

                'end_time'   => $coach['end_time'],

                'is_work'    => $coach['is_work'],

                'is_admin'   => $is_admin,

            ];

            $time_model = new TimeLog();

            $time_model->dataAdd($insert);
        }

        decCache($key,1,$coach['uniacid']);

        return true;
    }


    /**
     * @param $work_start
     * @param $work_end
     * @param $start
     * @param $end
     * @功能说明:注意 $start 和$end 必须同一天 不然会有数据错误
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-09 15:36
     */
    public function getHeadTimeData($work_start,$work_end,$start,$end){

        if(empty($work_start)||$start>$end){

            return 0;
        }

        $now_time = time();

        $now = strtotime(date('Y-m-d',$now_time));

        $work_start = strtotime($work_start)-$now;

        $work_end   = strtotime($work_end)-$now;

        $time = 0;

        $start_int = strtotime(date('Y-m-d',$start));

        $work_starts = $work_start+$start_int;

        $work_ends   = $work_end+$start_int;

        $arr = [];
        //跨日
        if($work_starts>=$work_ends){

            $star_arr['start_time'] = $work_starts;

            $star_arr['end_time']  = $start_int+86400;

            $end_arr['start_time'] = $start_int;

            $end_arr['end_time']   = $work_ends;

            $arr[] = $star_arr;

            $arr[] = $end_arr;

        }else{

            $star_arr['start_time']= $work_starts;

            $star_arr['end_time']  = $work_ends;

            $arr[] = $star_arr;
        }

        foreach ($arr as $value){

            $true_start = $value['start_time']>$start?$value['start_time']:$start;

            $true_end   = $value['end_time']<$end?$value['end_time']:$end;

            $true_time = ($true_end-$true_start)>0?$true_end-$true_start:0;

            $time +=$true_time;

        }

        return $time;
    }

    /**
     * @param $work_start
     * @param $work_end
     * @param $start
     * @param $end
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-09 15:36
     */
    public function getHeadTimeDataV2($work_start,$work_end,$start,$end){

        if(empty($work_start)){

            return 0;
        }

        $now_time = time();

        $now = strtotime(date('Y-m-d',$now_time));

        $work_start = strtotime($work_start)-$now;

        $work_end   = strtotime($work_end)-$now;

        $time = 0;

        while ($start+10<$end){

            $start_int = strtotime(date('Y-m-d',$start));

            $work_starts = $work_start+$start_int;

            $work_ends   = $work_end+$start_int;

            $arr = [];
            //跨日
            if($work_starts>=$work_ends){

                $star_arr['start_time'] = $work_starts;

                $star_arr['end_time']  = $start_int+86400;

                $end_arr['start_time'] = $start_int;

                $end_arr['end_time']   = $work_ends;

                $arr[] = $star_arr;

                $arr[] = $end_arr;

            }else{

                $star_arr['start_time']= $work_starts;

                $star_arr['end_time']  = $work_ends;

                $arr[] = $star_arr;
            }

            foreach ($arr as $value){
                //以分钟为单位
                if($start>=$value['start_time']&&$start<=$value['end_time']){

                    $time +=10;
                }

            }

            $start+=10;
        }

        return $time;
    }




    /**
     * @param $work_start
     * @param $work_end
     * @param $start_time
     * @功能说明:计算第一天在线的时间 因为可能不满一天所以单独计算
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-01 17:00
     * 1675868808
     * 1675908224
     * 1675915320
     *
     */
    public function getHeadTime($work_start,$work_end,$start_time){

        $now_time = time();

       // $now_time = 1675868808;

        $start_int = strtotime(date('Y-m-d',$start_time));

        $end_int = $start_int+86400;

        $end_int = $end_int>$now_time?$end_int:$now_time;

        $end_int = $end_int>$now_time?$end_int:$now_time;

        $now = strtotime(date('Y-m-d',$now_time));

        $work_start = strtotime($work_start)-$now+$start_int;

        $work_end   = strtotime($work_end)-$now+$start_int;
        //跨日或者整天
        if($work_start>=$work_end){

            $start = ($work_end - $start_time)>0?$work_end - $start_time:0;

            $end = $end_int-$work_start;

            return $start+$end;

        }else{

            $work_start = $work_start>=$start_time?$work_start:$start_time;

            return ($work_end-$work_start)>0?$work_end-$work_start:0;
        }

    }




    /**
     * @param $start_time
     * @param $end_time
     * @param $create_time
     * @功能说明:获取指定时间到现在的工作时间 指定时间不能跨日否则方法失效
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-01 13:44
     */
    public function getTailTime($work_start,$work_end,$start_time=0){

        $start_int = strtotime(date('Y-m-d',time()));

        $start_time = !empty($start_time)?$start_time:$start_int;
        //指定时间不能跨日否则方法失效
        if(!($start_time>=$start_int&&$start_time<=time())){

            return 0;
        }

        $work_start = strtotime($work_start);

        $work_end   = strtotime($work_end);
        //跨日或者整天
        if($work_start>=$work_end){

            $start = ($work_end-$start_time)>0?$work_end-$start_time:0;

            $work_start = $work_start>$start_time?$work_start:$start_time;

            $end = (time()-$work_start)>0?time()-$work_start:0;

            return $start+$end;

        }else{

            $work_start = $work_start>$start_time?$work_start:$start_time;

            $work_end = $work_end>=time()?time():$work_end;

            return ($work_end-$work_start)>0?$work_end-$work_start:0;
        }

    }


    /**
     * @param $strart_time
     * @param $end_time
     * @功能说明:获取每天的工作时长
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-01 14:26
     */
    public function getDayWorkTime($start_time,$end_time){

        if(empty($start_time)){

            return 0;
        }

        $start_time = strtotime($start_time);

        $end_time   = strtotime($end_time);

        $end_time = $end_time>$start_time?$end_time:$end_time+86400;
        //获取每天的在线时长
        $day_time = $end_time - $start_time;

        return $day_time;
    }




}