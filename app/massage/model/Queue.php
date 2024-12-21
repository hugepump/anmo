<?php
namespace app\massage\model;

use app\BaseModel;
use think\facade\Db;

class Queue extends BaseModel
{
    //定义表名
    protected $name = 'massage_queue_list';


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
     * @DataTime: 2024-07-26 10:48
     * @功能说明:执行失败加入队列
     */
    public function addQueue($order_id,$type,$uniacid,$refund_price=0,$refund_user=0,$text='',$refund_type=1,$is_mobile=1,$is_admin=1){

        $find = $this->where(['order_id'=>$order_id,'type'=>$type])->find();

        if(!empty($find)){

            return false;
        }

        $insert = [

            'order_id' => $order_id,

            'type'     => $type,

            'uniacid'  => $uniacid,

            'refund_price'=> $refund_price,

            'refund_user'=> $refund_user,

            'text'=> $text,

            'refund_type'=> $refund_type,

            'is_mobile'=> $is_mobile,

            'is_admin'=> $is_admin,
        ];

        $res = $this->dataAdd($insert);

        return $res;
    }


    /**
     * @author chenniang
     * @DataTime: 2024-07-26 10:51
     * @功能说明:执行
     */
    public function queueDo($type){

        $uniacid = 666;

        $key = 'queueDo12'.$type;

        incCache($key,1,$uniacid,30);

        if(getCache($key,$uniacid)==1){

            $data = $this->where(['type'=>$type,'status'=>1])->order('id desc')->limit(5)->select()->toArray();

            $refund_model = new RefundOrder();

            if(!empty($data)){

                foreach ($data as $v){

                    if($v['type']==1){

                        $res = $refund_model->passOrderData($v['order_id'],$v['refund_price'],2,$v['refund_user'],$v['text'],$v['refund_type'],$v['is_mobile'],$v['is_admin']);
                    }

                    if($res==true){

                        $this->dataUpdate(['id'=>$v['id']],['status'=>2]);
                    }else{

                        $update = [];

                        if($v['times']==2){

                            $update['status'] = 3;
                        }

                        $update['times'] = $v['times']+1;

                        $this->dataUpdate(['id'=>$v['id']],$update);
                    }
                }
            }
        }

        decCache($key,1,$uniacid);

        return true;
    }







}