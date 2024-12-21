<?php
namespace app\massage\model;

use app\BaseModel;
use think\facade\Db;

class StoreService extends BaseModel
{
    //定义表名
    protected $name = 'massage_store_service';




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
     * @DataTime: 2023-03-26 22:33
     * @功能说明:获取服务绑定的门店
     */
    public function getServiceStore($service_id){

        $dis = [

            'a.service_id' => $service_id,

            'b.status'     => 2
        ];

        $data = $this->alias('a')
                ->join('massage_store_list b','a.store_id = b.id')
                ->where($dis)
                ->field('a.*,b.title')
                ->group('a.id')
                ->select()
                ->toArray();
        return $data;
    }






}