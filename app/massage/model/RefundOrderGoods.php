<?php
namespace app\massage\model;

use app\BaseModel;
use think\facade\Db;

class RefundOrderGoods extends BaseModel
{
    //定义表名
    protected $name = 'massage_service_refund_order_goods';


    protected $append = [

        'time_long'
    ];


    /**
     * @param $value
     * @param $data
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-12-27 21:10
     */
    public function getTimeLongAttr($value,$data){

        if(!empty($data['order_goods_id'])){

            $order_model = new OrderGoods();

            return $order_model->where(['id'=>$data['order_goods_id']])->value('time_long');

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

        $data = $this->where($dis)->order('id desc')->paginate($page)->toArray();

        return $data;

    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:06
     * @功能说明:列表
     */
    public function dataSelect($dis){

        $data = $this->where($dis)->order('id desc')->select()->toArray();

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











}