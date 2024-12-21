<?php
namespace app\massage\model;

use app\BaseModel;
use think\facade\Db;

class StoreList extends BaseModel
{
    //定义表名
    protected $name = 'massage_store_list';




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
     * @DataTime: 2023-04-12 18:46
     * @功能说明:获取代理商下面门店关联的技师
     */
    public function getAdminStoreCoach($admin_id){

        $dis = [

            'a.admin_id' => $admin_id,

            'a.status'   => 1,

            'b.admin_id' => $admin_id
        ];

        $data = $this->alias('a')
                ->join('massage_service_coach_list b','a.id = b.store_id')
                ->where($dis)
                ->column('b.id');

        return $data;
    }


//    /**
//     * @author chenniang(龙兵科技)
//     * @DataTime: 2023-04-16 13:20
//     * @功能说明:获取技师
//     */
//    public function getCoachStoreAdmin($coach_id){
//
//        $dis = [
//
//            'a.admin_id' => $admin_id,
//
//            'a.status'   => 1,
//
//            'b.admin_id' => $admin_id
//        ];
//
//        $data = $this->alias('a')
//            ->join('massage_service_coach_list b','a.id = b.store_id')
//            ->where($dis)
//            ->column('b.id');
//
//        return $data;
//
//    }






}