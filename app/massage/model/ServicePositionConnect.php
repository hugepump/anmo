<?php
namespace app\massage\model;

use app\BaseModel;
use think\facade\Db;

class ServicePositionConnect extends BaseModel
{
    //定义表名
    protected $name = 'massage_service_position_connect';




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
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-02-20 14:37
     * @功能说明:关连的部位
     */
    public function positionInfo($service_id){

        $dis = [

            'a.service_id' => $service_id,

            'b.status'     => 1
        ];

        $data = $this->alias('a')
                ->join('massage_service_position_list b','a.position_id = b.id')
                ->where($dis)
                ->field('b.id,b.title')
                ->group('b.id')
                ->order('b.top desc,b.id desc')
                ->select()
                ->toArray();

        return $data;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-02-20 14:37
     * @功能说明:关连的部位
     */
    public function positionTitle($service_id){

        $dis = [

            'a.service_id' => $service_id,

            'b.status'     => 1
        ];

        $data = $this->alias('a')
            ->join('massage_service_position_list b','a.position_id = b.id')
            ->where($dis)
            ->group('b.id')
            ->order('b.top desc,b.id desc')
            ->column('b.title');

        return $data;
    }





}