<?php
namespace app\massage\model;

use app\BaseModel;
use think\facade\Db;

class Integral extends BaseModel
{
    //定义表名
    protected $name = 'massage_integral_list';




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
    public function dataList($dis,$page=10){

        $data = $this->where($dis)->order('id desc')->paginate($page)->toArray();

        return $data;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-23 16:42
     * @功能说明:
     */
    public function coachDataList($dis,$page=10,$month=''){

        if(!empty($month)){

            $firstday = date('Y-m-01', $month);

            $lastday  = date('Y-m-d H:i:s', strtotime("$firstday +1 month")-1);

            $data = $this->where($dis)->whereTime('create_time','<=',$lastday)->order('create_time desc,id desc')->paginate($page)->toArray();
        }else{

            $data = $this->where($dis)->order('create_time desc,id desc')->paginate($page)->toArray();
        }

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
     * @功能说明:添加积分
     * @author chenniang
     * @DataTime: 2024-09-12 15:05
     */
    public function integralAdd($order){

        $integral_insert = [

            'uniacid' => $order['uniacid'],

            'coach_id'=> $order['coach_id'],

            'order_id'=> $order['id'],

            'integral'=> round($order['pay_price'],2),

            'balance' => 100,

            'status'  => -1,

            'user_id' => $order['user_id'],

            'user_cash'=> $order['pay_price'],

            'type' => 2

        ];

        $integral_model = new Integral();

        $integral_model->dataAdd($integral_insert);

        return true;
    }




}