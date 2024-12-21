<?php
namespace app\hotel\model;

use app\BaseModel;
use think\facade\Db;

class HotelList extends BaseModel
{
    //定义表名
    protected $name = 'massage_hotel_list';




    public function getImgsAttr($value){

        return !empty($value)?explode(',',$value):[];

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

        return !empty($data)?$data->toArray():[];

    }


    public function adminDataList($dis,$limit){

        $data = $this->alias('a')
                ->join('shequshop_school_admin b','a.admin_id = b.id','left')
                ->join('massage_service_city_list c','b.city_id = c.id','left')
                ->where($dis)
                ->field('a.*,b.agent_name,b.city_type,c.title as admin_city')
                ->group('a.id')
                ->order('a.id desc')
                ->paginate($limit)
                ->toArray();

        return $data;
    }











}