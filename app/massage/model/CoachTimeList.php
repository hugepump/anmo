<?php


namespace app\massage\model;


use app\BaseModel;
use app\store\model\StoreList;
use think\facade\Db;

class CoachTimeList extends BaseModel
{
    protected $name = 'massage_service_coach_time_list';


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-06-29 18:25
     * @功能说明:获取正在上班时间内的技师
     */
    public static function getWorkOrResetCoach($uniacid,$type=1){

        $tt = time();

        $dis = [

            'uniacid'     => $uniacid,

            'status'      => 2,
        ];

        $coach_model = new Coach();

        $list = $coach_model->where($dis)->field('id as coach_id,start_time,end_time,is_work')->select()->toArray();

        $arr  = [];

        if(!empty($list)){

            foreach ($list as $value){

                $start_time = strtotime($value['start_time'])-strtotime(date('Y-m-d',time()))+strtotime(date('Y-m-d',$tt));

                $end_time   = strtotime($value['end_time'])-strtotime(date('Y-m-d',time()))+strtotime(date('Y-m-d',$tt));;
                //跨日
                if($end_time <=$start_time){
                    //查看此时处于上一个周期还是这个周期
                    if($tt<$end_time){

                        $start_time -= 86400;

                    }else{
                        //当前周期
                        $end_time += 86400;
                    }
                }

                $condition = $tt>$start_time&&$tt<$end_time&&$value['is_work']==1;

                if($type==1){

                    if($condition){

                        $arr[] = $value['coach_id'];
                    }

                }else{

                    if(!$condition){

                        $arr[] = $value['coach_id'];

                    }
                }
            }
        }

        return $arr;
    }




    /**
     * 获取这个时段不能接单的技师id
     * @return array
     */
    public static function getCannotCoach($uniacid,$time=0,$city_id=0)
    {

        $key = $uniacid.$time.'-'.$city_id.'getCannotCoach';

        $value = getCache($uniacid,$key);

        if(!empty($value)){

            return $value;
        }

        $config_model = new Config();

        $config = $config_model->dataInfo(['uniacid'=>$uniacid]);

        $time_add = $config['time_unit']>$config['time_interval']?$config['time_unit']:$config['time_interval'];

        $tt = time()+$time_add*60;

        $tt = $tt>$time?$tt:$time;

        $coach_model = new Coach();

        $order_model = new Order();

        $dis = [

            'uniacid'     => $uniacid,

            'status'      => 2,

            'auth_status' => 2
        ];

        if(!empty($city_id)){

            $dis['city_id'] = $city_id;
        }

        $list = $coach_model->where($dis)->field('id as coach_id,start_time,end_time')->select()->toArray();

        $arr = [];

        $where = [];

        $where[] = ['uniacid', '=', $uniacid];

        $where[] = ['pay_type', 'in', [2, 3, 4, 5, 6,8]];

        $where[] = ['start_time', '<=', $tt+$config['time_unit']*60];

        $where[] = ['end_time', '>=', $tt-$config['time_unit']*60];

        $refund_model = new RefundOrder();

        $refund_ing_order = $refund_model->where('status','in',[4,5])->where(['refund_end'=>1])->column('order_id');

        if(!empty($refund_ing_order)){

            $where[] = ['id', 'not in', $refund_ing_order];
        }

        $order = $order_model->where($where)->field('start_time,end_time,coach_id')->select()->toArray();

        if(!empty($list)){

            foreach ($list as $value){

                $start_time = strtotime($value['start_time'])-strtotime(date('Y-m-d',time()))+strtotime(date('Y-m-d',$tt));

                $end_time   = strtotime($value['end_time'])-strtotime(date('Y-m-d',time()))+strtotime(date('Y-m-d',$tt));;
                //跨日
                if($end_time <=$start_time){
                    //查看此时处于上一个周期还是这个周期
                    if($tt<$end_time){

                        $start_time -= 86400;

                    }else{
                        //当前周期
                        $end_time += 86400;
                    }
                }

                if($tt<$start_time||$tt>$end_time){

                    $arr[] = $value['coach_id'];

                }else{

                    $a = ($tt-$start_time)%($config['time_unit']*60);

                    $min = $tt-$a;

                    $max = $min +$config['time_unit']*60;

                    if(!empty($order)){

                        foreach ($order as $vv){

                            if($vv['coach_id']==$value['coach_id']){

                                $res = is_time_crossV2($min,$max,$vv['start_time'],$vv['end_time']);

                                if($res==false){

                                    $arr[] = $value['coach_id'];
                                }
                            }
                        }
                    }
                }
            }
        }

        $where = [

            ['status', '=', 0],
            ['uniacid', '=', $uniacid],
            ['is_click', '=', 1]
        ];

        if(!empty($time)){

            $where[] = ['time_str', '<=', $tt];

            $where[] = ['time_str_end', '>', $tt];
        }else{

            $where[] = ['time_str', '<', $tt];

            $where[] = ['time_str_end', '>=', $tt];
        }

        $rest = self::where($where)->column('coach_id');

        $arr  = array_merge($arr,$rest);

        setCache($key,$arr,5,$uniacid);

        return $arr;
    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-11-04 15:50
     * @功能说明:获取技师休息时长
     */
    public function getCoachRestTimeLong($caoch_id,$level_cycle,$type=1){

        $dis = [

            'coach_id' => $caoch_id,

            'status'   => 0,

            'is_click' => 1,

            'is_work'  => 1
        ];
        //每周
        if($level_cycle==1){

            $week = $type==1?'week':'last week';

            $price = $this->where($dis)->where('time_str','<',time())->whereTime('time_str',$week)->field('SUM(time_str_end-time_str) as time_long')->find();
            //每月
        }elseif ($level_cycle==2){

            $month = $type==1?'month':'last month';

            $price = $this->where($dis)->where('time_str','<',time())->whereTime('time_str',$month)->field('SUM(time_str_end-time_str) as time_long')->find();
            //每季度
        }elseif ($level_cycle==3){

            $quarter = $type==1 ? ceil((date('n'))/3) : ceil((date('n'))/3)-1;//获取当前季度

            $start_quarter = mktime(0, 0, 0,$quarter*3-2,1,date('Y'));

            $end_quarter   = mktime(0, 0, 0,$quarter*3+1,1,date('Y'));

            $price = $this->where($dis)->where('time_str','<',time())->where('time_str','between',"$start_quarter,$end_quarter")->field('SUM(time_str_end-time_str) as time_long')->find();
            //每年
        }elseif ($level_cycle==4){

            $year = $type==1?'year':'last year';

            $price = $this->where($dis)->where('time_str','<',time())->whereTime('time_str',$year)->field('SUM(time_str_end-time_str) as time_long')->find();

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

            $price = $this->where($dis)->where('time_str','<',time())->where('time_str','between',"$start_time,$end_time")->field('SUM(time_str_end-time_str) as time_long')->find();
        }else{
            //不限
            $price = $this->where($dis)->where('time_str','<',time())->field('SUM(time_str_end-time_str) as time_long')->find();

        }

        return !empty($price->time_long)?$price->time_long:0;

    }


    /**
     * @param $uniacid
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-07-01 17:42
     */
    public function haveWorkTimeCoach($uniacid){

        $coach_model = new Coach();

        $config_model= new Config();

        $where[] = ['pay_type', 'in', [2, 3, 4, 5, 6,8]];

        $config = $coach_model->dataInfo(['uniacid'=>$uniacid]);

        $start = time();

        $end_time = strtotime(date('Y-m-d',time()))+$config['max_day']*86400;

//
//        $data = $coach_model->alias('a')
//                ->join('massage_service_order_list b','a.id = b.coach_id','left')
//                ->whereRaw("not (start_time >$max or end_time<$min)")









    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-10-08 18:13
     * @功能说明:删除垃圾数据
     */
    public function delData(){

        $time = strtotime(date('Y-m-d',time()));

        $dis = [

            'status'   => 1,

            'is_click' => 0
        ];

        $this->where('time_str','<',$time)->where(function ($query) use ($dis){
            $query->whereOr($dis);
        })->delete();

        return true;
    }


    /**
     * @param $start_time
     * @param $end_time
     * @param $coach_id
     * @param $time_
     * @param int $is_coach
     * @功能说明:获取技师日程
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-01-24 14:43
     */
    public function getTimeData($start_time, $end_time, $coach_id, $time_,$is_coach=0,$is_store=0,$time_long=0)
    {
        $time_ = (int)$time_;

        $config_model = new Config();

        $store_model  = new StoreList();

        $config = $config_model->dataInfo(['uniacid' => $this->_uniacid]);

        $end_time   = strtotime($end_time) - strtotime(date("Y-m-d", time())) + strtotime(date("Y-m-d", $time_));

        $start_time = strtotime($start_time) - strtotime(date("Y-m-d", time())) + strtotime(date("Y-m-d", $time_));

        $coach_model = new Coach();

        $coach = $coach_model->dataInfo(['id'=>$coach_id]);

        $rest_arr = $coach_model->getCoachRestTime($coach,$start_time,$end_time,$config);

        $end_time = $end_time>$start_time?$end_time:$end_time+86400;

        $i = 0;

        $data = [];

        $time = $start_time;

        $time_interval = $is_coach==1?0:$config['time_interval']*60-1;

        $where[] = ['coach_id', '=', $coach_id];

        $where[] = ['end_time', '>=', time()];

        $where[] = ['pay_type', 'not in', [-1,7]];

        $order = Db::name('massage_service_order_list')->where($where)->field('start_time,end_time,order_end_time,pay_type')->select();

        while ($time < $end_time) {

            $time = $start_time + $config['time_unit'] * $i * 60;

            $times = date("Y-m-d", $time) == date("Y-m-d", $time_) ? $time : $time - 86400;

            if ($time >= $end_time) {

                break;
            }

            if (!empty($data[0]) && $times == $data[0]['time_str']) {

                $i++;

                continue;
            }
            //过期时间直接不显示
            if ($times<time()&&$is_coach==0) {

                $i++;

                continue;
            }

            $max_time = $times + $config['time_unit']* 60-1;

            $max_time = $max_time>$time_long*60+$times?$max_time:$time_long*60+$times;
            //时间戳
            $data[$i]['time_str']   = $times;

            $data[$i]['time_text']  = date('H:i', $times);

            $data[$i]['time_texts'] = date('Y-m-d', $times);

            $data[$i]['is_click'] = 0;

            $data[$i]['is_order'] = 0;

            $data[$i]['status']   = 1;

            if(!empty($order)){

                foreach ($order as $value){

                    $res = is_time_crossV2($times,$max_time,$value['start_time']-$time_interval,$value['end_time']+$time_interval);

                    if($res==false){

                        $data[$i]['is_order'] = 1;

                        $data[$i]['status'] = 0;

                    }
                }
            }

            if(!empty($rest_arr)&&$data[$i]['status']==1){

                $order_model = new Order();

                $res = $order_model->checkCoachRestTime($rest_arr,$times,$max_time);

                if(!empty($res['code'])){

                    $data[$i]['status'] = 0;

                    $data[$i]['is_click'] = 1;
                }
            }

            if($times-$time_interval<=time()){

                $data[$i]['status'] = 0;

            }
            //门店订单 判断门店时间
            if($is_store==1&&!empty($coach['store_id'])){

                $store_status = $store_model->checkStoreStatus($coach['store_id'],$times,$max_time);

                if(!empty($store_status['code'])){

                    $data[$i]['status'] = 0;
                }
            }

            $i++;
        }

        $data = !empty($data)?arraySort($data,'time_str'):$data;

        return $data;
    }




    /**
     * 时间管理
     * @param $data
     * @return bool
     */
    public static function timeEdit($data)
    {

        $config_model = new Config();

        $config = $config_model->dataInfo(['uniacid' => $data['uniacid']]);

        $insert = [];

        foreach ($data['time_text'] as $item) {

            $item['dat_str'] = !empty($item['dat_str'])?$item['dat_str']:time();

            $info = [];

            $date[] = date('Y-m-d', $item['dat_str']);

            if(!empty($item['sub'])){

                foreach ($item['sub'] as $v){

                    if($v['status']==0&&$v['is_click']==1){

                        $info[] =$v;
                    }
                }
            }
            if(!empty($info)){

                $insert[] = [
                    'coach_id'   => $data['coach_id'],
                    'date'       => date('Y-m-d', $item['dat_str']),
                    'info'       => $info,
                    // 'hours'      => $hours * ($data['time_unit'] / 60),
                    'uniacid'    => $data['uniacid'],
                    'create_time'=> time(),
                    'start_time' => $data['start_time'],
                    'end_time'   => $data['end_time'],
                    'max_day'    => $config['max_day'],
                    'time_unit'  => $config['time_unit']
                ];
            }
        }

        $is_work = $data['is_work'];

        CoachTime::where(['coach_id' => $data['coach_id']])->whereIn('date', $date)->delete();

        CoachTimeList::where(['coach_id' => $data['coach_id']])->whereIn('time_texts', $date)->delete();

        if ($insert) {
            foreach ($insert as $item) {
                $info = $item['info'];
                $item['info'] = json_encode($item['info']);
                $id = CoachTime::insertGetId($item);
                $list_insert = [];
                foreach ($info as $value) {
                    $list_insert[] = [
                        'uniacid' => $data['uniacid'],
                        'coach_id' => $data['coach_id'],
                        'time_id' => $id,
                        "time_str" => $value['time_str'],
                        "time_str_end" => (int)$value['time_str'] + ($config['time_unit'] * 60),
                        "time_text" => $value['time_text'],
                        "time_texts" => $value['time_texts'],
                        "status" => $value['status'],
                        'create_time' => time(),
                        'is_click' => $value['is_click'],
                        'is_work' => $is_work
                    ];
                }
                $res = CoachTimeList::insertAll($list_insert);
            }
        }

        return true;
    }







}