<?php
namespace app\massage\model;

use app\BaseModel;
use think\facade\Db;

class AdminWater extends BaseModel
{
    //定义表名
    protected $name = 'massage_admin_water';




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
     * @author chenniang
     * @DataTime: 2024-07-25 13:46
     * @功能说明:修改佣金
     */
    public function updateCash($uniacid,$admin_id,$cash,$is_add){

        $is_add = $is_add==1?$is_add:-1;

        $admin_model = new Admin();

        $admin_info = $admin_model->dataInfo(['id'=>$admin_id]);

        if(empty($admin_info)){

            return true;
        }

        $admin_cash = $admin_info['cash'];

        if(!empty($last_record)&&round($last_record->after_cash,2)!=round($admin_cash,2)){

            return 0;
        }

        $true_cash = $is_add==1?$cash:$cash*-1;

        if($true_cash==0){

            return true;
        }

        $res = $admin_model->where(['id'=>$admin_id,'cash'=>$admin_info['cash']])->update(['cash'=>Db::Raw("cash+$true_cash")]);

        if($res==0){

            return 0;
        }

        $insert = [

            'uniacid' => $uniacid,

            'admin_id'=> $admin_id,

            'before_cash' => $admin_cash,

            'after_cash'  => $admin_cash+$true_cash,

            'cash'        => $cash,

            'res'         => $res,

            'create_time' => time(),

            'add'         => $is_add
        ];

        $res = $this->insert($insert);

        if($res==0){

            return 0;
        }

        return $res;
    }


    /**
     * @param $uniacid
     * @param $coach_id
     * @功能说明:初始化代理商流水
     * @author chenniang
     * @DataTime: 2024-11-06 11:58
     */
    public static function initWater($uniacid,$admin_id){

        $insert = [

            'uniacid' => $uniacid,

            'admin_id'=> $admin_id,

            'before_cash' => 0,

            'after_cash'  => 0,

            'cash'        => 0,

            'res'         => 1,

            'create_time' => time(),

            'add'         => 1
        ];

        return self::insert($insert);
    }







}