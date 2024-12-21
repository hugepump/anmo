<?php
namespace app\adapay\model;

use app\BaseModel;
use think\facade\Db;

class Record extends BaseModel
{
    //定义表名
    protected $name = 'shequshop_adapay_record';




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:04
     * @功能说明:添加
     */
    public function dataAdd($data){

      //  $data['create_time'] = time();

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
     * @param $order
     * @param $commission
     * @param $id
     * @功能说明:添加记录
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-15 14:06
     */
    public function addRecord($order,$commission,$id){

        $order['top_order_id'] = !empty($order['top_order_id'])?$order['top_order_id']:$order['order_id'];

        $insert = [

            'uniacid' => $order['uniacid'],

            'order_id'=> $order['top_order_id'],

            'commission_id' => $commission['id'],

            'cash' => $commission['cash'],

            'type' => $commission['type'],

            'adapay_id' => $id,

            'son_order_id' => $order['top_order_id']==$order['order_id']?0:$order['order_id']

        ];

        $res = $this->dataAdd($insert);

        return $res;
    }




}