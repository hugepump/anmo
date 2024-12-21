<?php
namespace app\massage\model;

use app\BaseModel;
use think\facade\Db;

class StoreCoachUpdate extends BaseModel
{
    //定义表名
    protected $name = 'massage_store_coach_update';




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
     * @DataTime: 2021-03-19 16:08
     * @功能说明:开启默认
     */
    public function updateOne($id){

        $user_id = $this->where(['id'=>$id])->value('user_id');

        $res = $this->where(['user_id'=>$user_id])->where('id','<>',$id)->update(['status'=>0]);

        return $res;
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-11 01:54
     * @功能说明:关联的门店
     */
    public static function getStoreList($update_id){

        $store_model = new \app\store\model\StoreList();

        $dis = [

            'b.update_id' => $update_id,

            'd.store_auth' => 1
        ];

        $list =  $store_model->alias('a')
            ->join('massage_store_coach_update b','b.store_id = a.id')
            ->join('massage_service_coach_list c','c.id = b.coach_id')
            ->join('shequshop_school_admin d','(c.admin_id = d.id ||c.admin_id = 0) AND a.admin_id = d.id')
            ->where($dis)
            ->where('a.status','>',-1)
            ->field('a.id,a.title,b.store_id,a.admin_id,a.cover,a.status')
            ->group('a.id')
            ->order('a.id desc')
            ->select()
            ->toArray();

        return $list;
    }





}