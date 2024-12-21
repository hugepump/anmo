<?php
namespace app\massage\model;

use app\BaseModel;
use think\facade\Db;

class CustomBalance extends BaseModel
{
    //定义表名
    protected $name = 'massage_coach_custom_balance';




    public function getAddBasisBalanceAttr($value){

        if(isset($value)){

            return round($value,2);
        }
    }

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
     * @DataTime: 2023-07-13 15:01
     * @功能说明:获取技师的自定义佣金比例
     */
    public function getCoachCustomBalance($coach_id){

        $dis[] = ['coach_id','=',$coach_id];

        $dis[] = ['is_update','=',0];

        $dis[] = ['status','=',1];

        $dis[] = ['start_time','<',time()];

        $dis[] = ['end_time','>',time()];

        $data = $this->dataInfo($dis);

        return $data;
    }






}