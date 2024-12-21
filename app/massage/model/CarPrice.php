<?php
namespace app\massage\model;

use app\BaseModel;
use think\facade\Db;

class CarPrice extends BaseModel
{
    //定义表名
    protected $name = 'massage_service_car_price';


    protected $append = [

        'city_name',

        'cash_setting_day',

        'cash_setting_night',
    ];


    /**
     * @param $value
     * @param $data
     * @功能说明:车费叠加配置
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-01-22 17:48
     */
    public function getCashSettingDayAttr($value,$data){

        if(isset($data['id'])){

            $type_model = new CarCashType();

            return $type_model->getConfigList($data['id'],1);
        }
    }


    /**
     * @param $value
     * @param $data
     * @功能说明:车费叠加配置
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-01-22 17:48
     */
    public function getCashSettingNightAttr($value,$data){

        if(isset($data['id'])){

            $type_model = new CarCashType();

            return $type_model->getConfigList($data['id'],2);
        }
    }


    /**
     * @param $value
     * @param $data
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-02 16:33
     */
    public function getCityNameAttr($value,$data){

        if(!empty($data['city_id'])){

            $city_model = new City();

            $name = $city_model->where(['id'=>$data['city_id']])->value('title');

            return $name;

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

        if(empty($data)){

            $this->dataAdd($dis);

            $data = $this->where($dis)->find();
        }

        return !empty($data)?$data->toArray():[];

    }


    /**
     * @param $uniacid
     * @param $city_id
     * @功能说明:获取城市车费配置
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-08 10:08
     */
    public function getCityConfigOld($uniacid,$city_id){

        $dis = [

            'uniacid' => $uniacid,

            'city_id' => $city_id,

            'status'  => 1
        ];

        $data = $this->where($dis)->find();
        //可能是区县 获取上级城市的
        if(empty($data)){

            $city_model = new City();

            $city_id = $city_model->where(['id'=>$city_id,'city_type'=>2])->value('pid');

            $dis = [

                'uniacid' => $uniacid,

                'city_id' => $city_id,

                'status'  => 1
            ];

            $data = $this->where($dis)->find();
        }

        if(!empty($data)){

            $data = $data->toArray();
        }else{

            $data = $this->dataInfo(['uniacid'=>$uniacid]);
        }

        return $data;
    }




    /**
     * @param $uniacid
     * @param $city_id
     * @功能说明:获取城市车费配置
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-08 10:08
     */
    public function getCityConfig($uniacid,$city_id,$order_start_time=0){

        $dis = [

            'uniacid' => $uniacid,

            'city_id' => $city_id,

            'status'  => 1,
        ];

        $data = $this->where($dis)->find();
        //可能是区县 获取上级城市的
        if(empty($data)){

            $city_model = new City();

            $city_id = $city_model->where(['id'=>$city_id,'city_type'=>2])->value('pid');

            $dis = [

                'uniacid' => $uniacid,

                'city_id' => $city_id,

                'status'  => 1,
            ];

            $data = $this->where($dis)->find();
        }

        if(!empty($data)){

            $data = $data->toArray();
        }else{

            $data = $this->dataInfo(['uniacid'=>$uniacid,'city_id'=>0]);
        }

        if(empty($data)){

            $data = $this->dataInfo(['uniacid'=>$uniacid,'city_id'=>0]);
        }

        $config = longbingGetAppConfig($uniacid);

        $order_start_time -=$config['time_interval']*60;

        $day = strtotime(date('Y-m-d',$order_start_time));

        $now = strtotime(date('Y-m-d',time()));

        $start_time = strtotime($data['car_price_start'])-$now+$day;

        $end_time   = strtotime($data['car_price_end'])-$now+$day;
        //跨日
        if($end_time <=$start_time){
            //查看此时处于上一个周期还是这个周期
            //上一个周期
            if($order_start_time<$end_time){

                $start_time -= 86400;

            }else{
                //当前周期
                $end_time += 86400;
            }
        }

        if($order_start_time>=$start_time&&$order_start_time<=$end_time){

            $data['day_type'] = 1;
        }else{

            $data['day_type'] = 2;
        }

      //  dump($data['day_type'],date('Y-m-d H:i',$start_time),date('Y-m-d H:i',$end_time),date('Y-m-d H:i',$order_start_time));exit;

        return $data;
    }



}