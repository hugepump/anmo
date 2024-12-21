<?php
namespace app\massage\model;

use app\BaseModel;
use think\facade\Db;

class OrderLog extends BaseModel
{
    //定义表名
    protected $name = 'massage_order_control_log';




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
     * @param $order_id
     * @param $uniacid
     * @param $pay_type
     * @param $old_pay_type
     * @param $is_admin
     * @param $user_id
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-27 16:15
     */
    public function addLog($order_id,$uniacid,$pay_type,$old_pay_type,$is_admin,$user_id=0,$type=1,$mobile='',$map_type=0){

        $insert = [

            'uniacid' => $uniacid,

            'order_id'=> $order_id,

            'pay_type'=> $pay_type,

            'old_pay_type' => $old_pay_type,

            'admin_control'=> $is_admin,

            'user_id' => $user_id,

            'type'    => $type,

            'old_mobile' => $mobile,

            'map_type' => $map_type,
        ];

        $res = $this->dataAdd($insert);

        return $res;
    }





}