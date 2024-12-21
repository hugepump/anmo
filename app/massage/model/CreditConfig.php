<?php
namespace app\massage\model;

use app\BaseModel;
use think\facade\Db;

class CreditConfig extends BaseModel
{
    //定义表名
    protected $name = 'massage_coach_credit_config';



    protected function getDistanceAttr($value,$data){

        return !empty($value)?@unserialize($value):$value;
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

        if(empty($data)){

            $this->dataAdd($dis);

            $data = $this->where($dis)->find();

        }

        return !empty($data)?$data->toArray():[];

    }


    /**
     * @param $coach_id
     * @param $type
     * @param $uniacid
     * @param int $price
     * @功能说明:添加信用分记录
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-09-25 17:52
     */
    public function creditRecordAdd($coach_id,$type,$uniacid,$order_id,$price=0,$create_time=0){

        $config = $this->dataInfo(['uniacid'=>$uniacid]);

        switch ($type){

            case 1:
                //订单
                $value = 'order_value';

                break;
            case 2:
                //加钟
                $value = 'add_order_value';

                break;
            case 3:
                //时长
                $value = 'time_long_value';

                break;
            case 4:
                //复购
                $value = 'repeat_order_value';

                break;
            case 5:
                //好评
                $value = 'good_evaluate_value';

                break;
            case 6:
                //退单
                $value = 'refund_order_value';

                break;
            case 7:
                //拒单
                $value = 'refuse_order_value';

                break;

            case 8:
                //差评
                $value = 'bad_evaluate_value';

                break;
        }

        $value = !empty($price)?$config[$value]*$price:$config[$value];

        if(empty($value)){

            return true;
        }

        $insert = [

            'uniacid' => $uniacid,

            'coach_id'=> $coach_id,

            'order_id'=> $order_id,

            'type'    => $type,

            'value'   => in_array($type,[6,7,8])?$value*-1:$value,

            'order_price' => in_array($type,[6,7,8])?$price*-1:$price,

            'create_time' => !empty($create_time)?$create_time:time()
        ];

        $record_model = new CreditRecord();

        $res = $record_model->dataAdd($insert);

        return $res;
    }










}