<?php
namespace app\adapay\model;

use app\BaseModel;
use longbingcore\wxcore\Adapay;
use think\facade\Db;

class AdminPay extends BaseModel
{
    //定义表名
    protected $name = 'massage_adapay_admin_pay';




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
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-17 19:04
     * @功能说明:平台充钱
     */
    public function adminPay($uniacid,$pay_price){

        $insert = [

            'uniacid' => $uniacid,

            'pay_price'=> $pay_price,

            'true_price'=> $pay_price,

            'order_code' => orderCode()
        ];

        $res = $this->dataAdd($insert);

        $id = $this->getLastInsID();

        $adapay_model = new Adapay($uniacid);

        $pay_record_model = new PayRecord();

        $res = $adapay_model->createPay($insert['order_code'],$insert['pay_price'],'1','alipay_qr');

        if($res['status']=='succeeded'&&isset($res['expend'])){

            $insert = [

                'uniacid'    => $uniacid,

                'order_code' => $insert['order_code'],

                'adapay_code'=> $insert['order_code'],

                'adapay_id'  => $res['id'],

                'pay_price'  => $insert['pay_price'],

                'true_price' => $insert['pay_price'],

                'type'       => 'Adapay',

                'order_id'   => $id
            ];

            $pay_record_model->dataAdd($insert);

        }

        return $res;
   }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-17 19:14
     * @功能说明:
     */
   public function orderResult($order_code,$adapay_id){

       $data = $this->dataInfo(['order_code'=>$order_code,'pay_type'=>1]);

       if(!empty($data)){

           $update = [

               'pay_time' => time(),

               'pay_type' => 2,

               'adapay_id' => $adapay_id
           ];

           $this->dataUpdate(['id'=>$data['id']],$update);
       }

       return true;

   }








}