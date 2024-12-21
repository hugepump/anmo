<?php
namespace app\massage\model;

use app\BaseModel;
use think\facade\Db;

class OrderAddress extends BaseModel
{
    //定义表名
    protected $name = 'massage_service_order_address';





    public function getMobileAttr($value,$data){

        if(!empty($value)&&isset($data['uniacid'])){

            if(numberEncryption($data['uniacid'])==1){

                return  substr_replace($value, "****", 2,4);
            }

        }

        return $value;

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
    public function dataInfo($dis,$field='*'){

        $data = $this->where($dis)->field($field)->find();

        return !empty($data)?$data->toArray():[];

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-22 10:34
     * @功能说明:添加下单地址
     */
    public function orderAddressAdd($address,$order_id,$coach_info=[]){

        if(empty($address)){

            return ['code'=>500,'msg'=>'下单地址已删除'];

        }

        $insert = [

            'uniacid'  => $address['uniacid'],

            'order_id' => $order_id,

            'user_name'=> $address['user_name'],

            'mobile'   => $address['mobile'],

            'province' => $address['province'],

            'city'     => $address['city'],

            'area'     => $address['area'],

            'lng'      => $address['lng'],

            'lat'      => $address['lat'],

            'address'      => $address['address'],

            'address_id'   => !empty($address['id'])?$address['id']:0,

            'address_info' => $address['address_info'],

            'coach_lng'    => !empty($coach_info['lng'])?$coach_info['lng']:'',

            'coach_lat'    => !empty($coach_info['lat'])?$coach_info['lat']:'',

            'coach_address'=> !empty($coach_info['address'])?$coach_info['address']:'',

        ];

        $res = $this->dataAdd($insert);

        if($res!=1){

            return ['code'=>500,'msg'=>'下单失败'];

        }

        return $res;

    }


    /**
     * @param $uniacid
     * @param $user_name
     * @param $phone
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-03 16:08
     */
    public function getDefaultSetting($uniacid,$user_name,$phone,$store_id){

        $store_model = new \app\store\model\StoreList();

        $store = $store_model->dataInfo(['id'=>$store_id]);

        if(empty($store)){

            return false;
        }

        $insert = [

            'uniacid'  => $uniacid,

            'user_name'=> $user_name,

            'mobile'   => $phone,

            'province' => '',

            'city'     => '',

            'area'     => '',

            'lng'      => $store['lng'],

            'lat'      => $store['lat'],

            'address'  => $store['address'],

            'address_info' => '',

        ];

        return $insert;
    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-05-16 15:08
     * @功能说明:修改订单电话
     */
    public function updateOrderMobile($order_id,$mobile,$user_id){

        $order_address_model = new OrderAddress();

        $log_model = new OrderLog();

        $address = $order_address_model->dataInfo(['order_id'=>$order_id],'mobile,uniacid');

        if(empty($address)){

            return ['code'=>500,'msg'=>'获取信息失败'];

        }

        $res = $order_address_model->dataUpdate(['order_id'=>$order_id],['mobile'=>$mobile]);

        if($res==0){

            return ['code'=>500,'msg'=>'修改失败'];
        }

        $log_model->addLog($order_id,$address['uniacid'],1,1,1,$user_id,3,$address['mobile']);

        return true;

    }


    /**
     * @param $order_id
     * @param $user_id
     * @param $address
     * @param $address_info
     * @param $lng
     * @param $lat
     * @功能说明:修改订单地址
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-05-22 16:29
     */
    public function updateOrderAddress($order_id,$input,$user_id,$is_admin){

        $order_address_model = new OrderAddress();

        $log_model = new OrderLog();

        $address_data = $order_address_model->dataInfo(['order_id'=>$order_id],'mobile,uniacid,address,address_info,lng,lat,city,area,province');

        if(empty($address_data)){

            return ['code'=>500,'msg'=>'获取信息失败'];
        }

        $update = [

            'address' => $input['address'],

            'address_info' => $input['address_info'],

            'lng' => $input['lng'],

            'lat' => $input['lat'],

            'city' => $input['city'],

            'area' => $input['area'],

            'province' => $input['province'],
        ];

        $res = $order_address_model->dataUpdate(['order_id'=>$order_id],$update);

        if($res==0){

            return ['code'=>500,'msg'=>'修改失败'];
        }

        $log_model->addLog($order_id,$address_data['uniacid'],1,1,$is_admin,$user_id,8);

        $log_id = $log_model->getLastInsID();

        $address_record_model= new UpdateAddressRecord();

        $insert = [

            'uniacid' => $address_data['uniacid'],

            'order_id'=> $order_id,

            'address' => $address_data['address'],

            'address_info' => $address_data['address_info'],

            'lng' => $address_data['lng'],

            'lat' => $address_data['lat'],
            'city' => $address_data['city'],
            'area' => $address_data['area'],
            'province' => $address_data['province'],
            'log_id' => $log_id
        ];

        $address_record_model->dataAdd($insert);

        return true;
    }









}