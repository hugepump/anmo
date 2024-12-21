<?php
namespace app\massage\model;

use app\BaseModel;
use think\facade\Db;

class CarCashType extends BaseModel
{
    //定义表名
    protected $name = 'massage_service_car_cash_type';


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

        return !empty($data)?$data->toArray():[];
    }


    /**
     * @param $config_id
     * @功能说明:获取车费叠加配置
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-01-22 17:50
     */
    public function getConfigList($config_id,$type){

        $dis = [

            'config_id' => $config_id,

            'type'      => $type
        ];

        $list = $this->where($dis)->order('km,id desc')->select()->toArray();

        return $list;
    }


    /**
     * @param $data
     * @param $type
     * @param $uniacid
     * @功能说明:编辑叠加配置
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-01-22 18:00
     */
    public function updateConfigList($data,$config_id,$type,$uniacid){

        $this->where(['config_id'=>$config_id,'type'=>$type])->delete();

        foreach ($data as $k=>$v){

            $insert[$k] = [

                'uniacid' => $uniacid,

                'config_id' => $config_id,

                'cash' => $v['cash'],

                'km' => $v['km'],

                'type' => $type,
            ];
        }

        $res = $this->saveAll($insert);

        return $res;
    }




}