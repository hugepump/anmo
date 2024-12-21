<?php
namespace app\massage\model;

use app\adminuser\model\AdminUser;
use app\BaseModel;
use app\coachbroker\model\CoachBroker;
use think\facade\Db;

class UserWater extends BaseModel
{
    //定义表名
    protected $name = 'massage_user_water';




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
    public function updateCash($uniacid,$user_id,$cash,$is_add,$order_id=0,$comm_id=0,$type=1){

        $is_add = $is_add==1?$is_add:-1;

        $user_model = new User();

        $user_info  = $user_model->dataInfo(['id'=>$user_id]);

        $last_record= $this->where(['uniacid'=>$uniacid,'user_id'=>$user_id])->order('id desc')->find();

        $user_cash= $user_info['new_cash'];

        if(!empty($last_record)&&round($last_record->after_cash,2)!=round($user_cash,2)){

            return 0;
        }

        $true_cash = $is_add==1?$cash:$cash*-1;

        if($true_cash==0){

            return true;
        }

        $update = ['new_cash'=>Db::Raw("new_cash+$true_cash")];

        if(in_array($type,[1,14,15,5])){

            $update = ['new_cash'=>Db::Raw("new_cash+$true_cash"),'cash'=>Db::Raw("cash+$true_cash")];
        }

        $res = $user_model->where(['id'=>$user_id,'new_cash'=>$user_cash])->update($update);

        if($res==0){

            return 0;
        }

        $insert = [

            'uniacid'     => $uniacid,

            'user_id'     => $user_id,

            'before_cash' => $user_cash,

            'after_cash'  => $user_cash+$true_cash,

            'cash'        => $cash,

            'res'         => $res,

            'create_time' => time(),

            'add'         => $is_add,

            'order_id'    => $order_id,

            'comm_id'     => $comm_id,

            'type'        => $type
        ];

        $res = $this->insert($insert);

        return $res;
    }



    /**
     * @param $uniacid
     * @param $coach_id
     * @功能说明:初始化代渠道商流水
     * @author chenniang
     * @DataTime: 2024-11-06 11:58
     */
    public static function initWater($uniacid,$user_id){

        $insert = [

            'uniacid'     => $uniacid,

            'user_id'   => $user_id,

            'before_cash' => 0,

            'after_cash'  => 0,

            'cash'        => 0,

            'res'         => 1,

            'create_time' => time(),

            'add'         => 1,

            'order_id'    => 0,

            'comm_id'     => 0,

            'type'        => 1
        ];

        return self::insert($insert);
    }





}