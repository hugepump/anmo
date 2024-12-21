<?php
namespace app\massage\model;

use AlibabaCloud\Client\AlibabaCloud;
use app\BaseModel;
use Exception;
use think\facade\Db;

class OrderPrice extends BaseModel
{
    //定义表名
    protected $name = 'massage_service_order_price_log';


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
     * @DataTime: 2020-09-29 11:43
     * @功能说明:
     */
    public function dataInfo($dis){

        $data = $this->where($dis)->find();

        return !empty($data)?$data->toArray():[];

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-12-14 13:52
     * @功能说明:20230825154117090100000901
     */
    public function logAdd($order,$top_order_id,$is_top=0,$pay_model=1){

        $insert = [

            'uniacid' => $order['uniacid'],

            'order_id'=> $order['id'],

            'order_price' => $order['pay_price'],

            'can_refund_price' => $order['pay_price'],

            'top_order_id'=> $top_order_id,

            'is_top' => $is_top,

            'pay_model' => $pay_model,

            'order_code' => $order['order_code'],

            'transaction_id' => $order['transaction_id'],
        ];

        $res = $this->insert($insert);

        return $res;
    }






}