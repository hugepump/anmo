<?php
namespace app\massage\model;

use app\BaseModel;
use think\facade\Db;

class CateConnect extends BaseModel
{
    //定义表名
    protected $name = 'massage_service_cate_connect';




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
     * @功能说明:获取服务绑定的分类
     */
    public function getServiceCate($service_id){

        $dis = [

            'a.service_id' => $service_id,

            'b.status'     => 1
        ];

        $data = $this->alias('a')
            ->join('massage_service_cate_list b','a.cate_id = b.id')
            ->where($dis)
            ->group('a.id')
            ->column('a.cate_id');

        return array_values($data);
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-26 22:33
     * @功能说明:获取服务绑定的分类
     */
    public function getCateService($cate_id){

        $dis = [

            'a.cate_id' => $cate_id,
        ];

        $data = $this->alias('a')
            ->join('massage_service_service_list b','a.service_id = b.id')
            ->where($dis)
            ->where('b.status','>',-1)
            ->group('a.id')
            ->column('b.id');

        return $data;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-26 22:33
     * @功能说明:获取服务绑定的分类
     */
    public function getCateServiceOnline($cate_id){

        $dis = [

            'a.cate_id' => $cate_id,

            'b.is_add' => 0,
        ];

        $data = $this->alias('a')
            ->join('massage_service_service_list b','a.service_id = b.id')
            ->where($dis)
            ->where('b.status','=',1)
            ->group('a.id')
            ->column('b.id');

        return $data;
    }





}