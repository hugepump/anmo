<?php
namespace app\massage\model;

use AlibabaCloud\Client\AlibabaCloud;
use app\BaseModel;
use Exception;
use think\facade\Db;

class Config extends BaseModel
{
    //定义表名
    protected $name = 'shequshop_school_config';


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

        if(empty($dis['uniacid'])){

            $dis['uniacid'] = 666;
        }

        $data = $this->where($dis)->find();

        if(empty($data)){

            $this->dataAdd($dis);

            $data = $this->where($dis)->find();

        }

        return !empty($data)?$data->toArray():[];

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-11-20 10:48
     * @功能说明:从缓存里面获取配置
     */
    public function getCacheInfo($uniacid){

        $key = 'getCacheInfo_getCacheInfo'.$uniacid;

        $data= getCache($key,$uniacid);

        if(!empty($data)){

            return $data;
        }

        $dis = [

            'uniacid' => $uniacid
        ];

        $data = $this->dataInfo($dis);

        setCache($key,$data,5,$uniacid);

        return $data;
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-06-30 14:27
     * @功能说明:是否支持bus//408.36
     */
    public function getIsBus($uniacid){

        $config_model = new Config();

        $config = $config_model->dataInfo(['uniacid'=>$uniacid]);

        $is_bus = $config['is_bus'];

        if(!empty($input['start_time'])&&!empty($config['bus_start_time'])&&!empty($config['bus_end_time'])){

            $z_time = strtotime(date('Y-m-d',$input['start_time']));

            $start_time = strtotime($config['bus_start_time'])-strtotime(date('Y-m-d',time()))+$z_time;

            $end_time   = strtotime($config['bus_end_time'])-strtotime(date('Y-m-d',time()))+$z_time;

            if($input['start_time']<$start_time){

                $start_time -= 86400;

                $end_time   -= 86400;
            }

            $end_time = $end_time<$start_time?$end_time+86400:$end_time;

            if($input['start_time']<$start_time||$input['start_time']>$end_time){

                $is_bus = 0;
            }

        }

        return $is_bus;

    }



}