<?php
namespace app\massage\model;

use app\BaseModel;
use think\facade\Db;

class OrderProcess extends BaseModel
{
    //定义表名
    protected $name = 'massage_service_order_process';




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
     * @param int $order_id
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-04 15:55
     */
    public function initData($uniacid,$order_id=0){

        $dis = [

            'have_sql' => 1,

        ];

        if(!empty($order_id)){

            $dis['id'] = $order_id;
        }

        $order_model = new Order();

        $order = $order_model->where($dis)->limit(10)->select()->toArray();

        $key = 'orderUpdateSql'.$order_id;

        incCache($key,1,$uniacid);

        $value = getCache($key,$uniacid);

        if($value==1){

            if(!empty($order)){

                foreach ($order as $value){

                    $arr = [4,5,7];

                    foreach ($arr as $ks=> $vs){

                        $update_text = $this->getText($vs);

                        $update[$ks] = [

                            'order_id' => $value['id'],

                            'uniacid'  => $value['uniacid'],

                            'type'     => $vs
                        ];

                        foreach ($update_text as $k=> $v){

                            $update[$ks][$k] = $value[$v];
                        }

                    }

                    $this->saveAll($update);

                    $order_model->dataUpdate(['id'=>$value['id']],['have_sql'=>2]);
                }

            }
        }

        decCache($key,1,$uniacid);

        return true;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-04 15:43
     * @功能说明:
     */
    public function getText($type){

        switch ($type){

            case 4:
                $data['lng'] = 'serout_lng';

                $data['lat'] = 'serout_lat';

                $data['address'] = 'serout_address';

                break;
            case 5:
                $data['lng'] = 'arr_lng';

                $data['lat'] = 'arr_lat';

                $data['address'] = 'arr_address';

                $data['img'] = 'arrive_img';

                break;

            case 7:
                $data['lng'] = 'end_lng';

                $data['lat'] = 'end_lat';

                $data['address'] = 'end_address';

                $data['img'] = 'end_img';

                break;

        }

        return $data;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-04 16:14
     * @功能说明:获取订单的状态
     */
    public function getOrderProcess($order){

        $this->initData($order['uniacid'],$order['id']);

        $list = $this->where(['order_id'=>$order['id'],'uniacid'=>$order['uniacid']])->select()->toArray();

        foreach ($list as $value){

            $text = $this->getText($value['type']);

            //  $data[]

        }

        $arr = [4,5,7];

        foreach ($arr as $vs){



        }

        //  $data =


    }






}