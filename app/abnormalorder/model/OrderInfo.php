<?php
namespace app\abnormalorder\model;

use app\BaseModel;
use think\facade\Db;

class OrderInfo extends BaseModel
{
    //定义表名
    protected $name = 'massage_service_abnormal_order_info';




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
     * @DataTime: 2023-12-12 15:25
     * @功能说明:补全订单流程
     */
    public function completionProcess($info){

        $order_model = new OrderList();

        $default_process = $this->where(['order_id'=>$info['order_id'],'is_default'=>1])->order('top,id desc')->select()->toArray();

        foreach ($default_process as $k=>$v){

            if($info['process_id']==$v['process_id']){

                $default_key = $k;
            }
        }
        //上一个流程
        $last_key = $default_key-1;

        $top = 0;

        $id = [];

        foreach ($default_process as $k=>$v){

            if(in_array($k,[$last_key,$default_key])){

                $top++;

                $insert = [

                    'uniacid'    => $v['uniacid'],

                    'order_id'   => $info['order_id'],

                    'process_id' => $v['process_id'],

                    'sub_type'   => $v['sub_type'],

                    'is_cancel'  => $last_key==$k?1:0,

                    'top'        => $info['top']+$top
                ];

                $this->dataAdd($insert);

                $i = $this->getLastInsID();

                $info_id = $top==1?$i:0;

                $id[] = $i;
                //修改订单当前的流程
                if($top==1){

                    $update = [

                        'process_id' => $v['process_id'],

                        'info_id'    => $info_id,

                        'pass_type'  => 0,

                        'is_cancel'  => 1
                    ];

                    $order_model->dataUpdate(['id'=>$info['order_id']],$update);
                }
            }
        }

        $this->where(['order_id'=>$info['order_id']])->where('id','not in',$id)->where('top','>',$info['top'])->update(['top'=>Db::Raw("top+$top")]);
        //第一个流程被拒绝
        if($last_key<0){

            $order_model->dataUpdate(['id'=>$info['order_id']],['first_cancel'=>1]);
        }

        return true;
    }


    /**
     * @param $info
     * @param $wander
     * @功能说明:添加流转
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-12-13 10:27
     */
    public function wanderProcess($info,$wander){

        $this->where(['order_id'=>$info['order_id']])->where('top','>',$info['top'])->update(['status'=>-1]);

        $insert = [

            'uniacid'    => $info['uniacid'],

            'order_id'   => $info['order_id'],

            'process_id' => $info['process_id'],

            'sub_type'   => $info['sub_type'],

            'pass_type'  => $wander['pass_type'],

            'top'        => $info['top']+1
        ];

        $this->dataAdd($insert);

        $info_id = $this->getLastInsID();

        $order_model = new OrderList();

        $update = [

            'process_id' => $info['process_id'],

            'pass_type'  => $wander['pass_type'],

            'info_id'    => $info_id
        ];

        $order_model->dataUpdate(['id'=>$info['order_id']],$update);

        return true;
    }








}