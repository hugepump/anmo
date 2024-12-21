<?php
namespace app\memberdiscount\model;

use app\BaseModel;
use think\facade\Db;

class Config extends BaseModel
{
    //定义表名
    protected $name = 'massage_member_discount_config';




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
     * @author chenniang
     * @DataTime: 2024-09-03 18:43
     * @功能说明:详情
     */
    public function dataInfo($dis){

        $data = $this->where($dis)->find();

        if(empty($data)){

            $this->insert($dis);

            $data = $this->where($dis)->find();
        }

        return $data->toArray();

    }











}