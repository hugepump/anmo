<?php
namespace app\massage\model;

use AlibabaCloud\Client\AlibabaCloud;
use app\BaseModel;
use Exception;
use think\facade\Db;

class OrderData extends BaseModel
{
    //定义表名
    protected $name = 'massage_service_order_list_data';


    /**
     * @param $value
     * @param $data
     * @功能说明:备注图片
     * @author chenniang
     * @DataTime: 2024-10-23 11:02
     */
    public function getTextImgAttr($value,$data){

        if(isset($value)){

            return !empty($value)?explode(',',$value):[];
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
     * @DataTime: 2020-09-29 11:43
     * @功能说明:
     */
    public function dataInfo($dis){

        $data = $this->where($dis)->order('id desc')->find();

        if(empty($data)){

            $this->dataAdd($dis);

            $data = $this->where($dis)->find();
        }

        return !empty($data)?$data->toArray():[];
    }


    /**
     * @param $order_id
     * @param $uniacid
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-09-12 14:04
     */
    public function orderDataAdd($order_id,$uniacid,$pay_model,$input=[]){

        $config = getConfigSettingArr($uniacid,['coupon_bear_coach','coupon_bear_admin','add_flow_path','wx_point','ali_point','balance_point','poster_point','poster_coach_share','poster_admin_share','comm_coach_balance']);

        if($pay_model==2){

            $point = $config['balance_point'];

        }elseif ($pay_model==3){

            $point = $config['ali_point'];

        }elseif ($pay_model==4){

            $point = $config['balance_point'];

        }else{

            $point = $config['wx_point'];
        }

        $order_model = new Order();

        $coach_model = new Coach();

        $city_model  = new City();

        $order = $order_model->dataInfo(['id'=>$order_id]);
        //加钟订单 流程需要跟着主订单走
        if($order['is_add']==1){

            $config['add_flow_path'] = $this->where(['order_id'=>$order['add_pid']])->value('add_flow_path');
        }

        $city_id = $coach_model->where(['id'=>$order['coach_id']])->value('city_id');
        //技术服务费
        $skill_balance = $city_model->where(['id'=>$city_id])->value('skill_service_balance');

        $insert = [

            'order_id' => $order_id,

            'uniacid'  => $uniacid,

            'pay_point'=> $point,

            'poster_point' => $config['poster_point'],

            'poster_coach_share' => $config['poster_coach_share'],

            'poster_admin_share' => $config['poster_admin_share'],

            'skill_balance'      => $skill_balance,

            'add_flow_path'      => $config['add_flow_path'],

            'coupon_bear_coach'  => $config['coupon_bear_coach'],

            'coupon_bear_admin'  => $config['coupon_bear_admin'],

            'member_discount_cash'  => $input['member_discount_cash'],

            'member_discount_balance'=> $input['member_discount_balance'],
            //储值折扣金额
            'balance_discount_cash'  => $input['balance_discount_cash'],

            'is_store_admin'  => isset($input['is_store_admin'])?$input['is_store_admin']:0,

            'late_notice' => 0,

            'receiving_order_notice' => 0,

            'text_img' => $input['text_img'],
        ];
        //储值返佣扣款
        if(isset($order['pay_model'])&&$order['pay_model']==2){

            $insert['comm_coach_balance'] = $config['comm_coach_balance'];
        }

        $info = $this->where(['order_id' => $order_id])->find();

        if (empty($info)) {

            $res = $this->dataAdd($insert);
        } else {

            $res = $this->dataUpdate(['order_id' => $order_id], $insert);
        }

        return $res;
    }





}