<?php
namespace app\virtual\model;

use AlibabaCloud\Client\AlibabaCloud;
use app\BaseModel;
use app\massage\model\City;
use app\massage\model\Coach;
use app\massage\model\OrderAddress;
use app\reminder\model\Record;
use app\virtual\info\PermissionVirtual;
use Exception;
use longbingcore\wxcore\aliyunVirtual;
use longbingcore\wxcore\Moor;
use longbingcore\wxcore\Winnerlook;
use think\facade\Db;

class Config extends BaseModel
{
    //定义表名
    protected $name = 'massage_aliyun_phone_config';


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

        if(empty($data)){

            $this->dataAdd($dis);

            $data = $this->where($dis)->find();

        }

        return !empty($data)?$data->toArray():[];

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-01-03 12:09
     * @功能说明:获取虚拟电话权限
     */
    public function getVirtualAuth($uniacid){

        $p = new PermissionVirtual($uniacid);

        $auth = $p->pAuth();
        //如果没有权限返回真实号码
        if($auth==false){

            return false;
        }

        $config = $this->dataInfo(['uniacid'=>$uniacid]);
        //如果未开启返回真实号码
        if($config['virtual_status']==0){

            return false;

        }

        return true;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-12-08 11:53
     * @功能说明:获取虚拟电话
     *
     * type1 技师打电话给用户 2用户打电话给技师
     */
    public function getVirtual($order,$type=1,$phone=0){

        $p = new PermissionVirtual($order['uniacid']);

        $auth = $p->pAuth();

        if(!empty($phone)){

            $order['coach_info']['mobile'] = $phone;
        }
        //如果没有权限返回真实号码
        if($auth==false){

            if($type==1){

                return $order['address_info']['mobile'];

            }else{

                return $order['coach_info']['mobile'];
            }
        }

        $config = $this->dataInfo(['uniacid'=>$order['uniacid']]);
        //如果未开启返回真实号码
        if($config['virtual_status']==0){

            if($type==1){

                return $order['address_info']['mobile'];

            }else{

                return $order['coach_info']['mobile'];
            }
        }

        $coach_phone = $order['coach_info']['mobile'];

        if(empty($phone)){

            $coach_model = new Coach();

            $coach_phone = $coach_model->where(['id'=>$order['coach_id']])->value('mobile');
        }

        $address_model = new OrderAddress();

        $order['address_info']['mobile'] = $address_model->where(['order_id'=>$order['id']])->value('mobile');

        if($config['virtual_type']==1){

            $res = $this->aliPhoneBind($order,$coach_phone,$config);

        }elseif($config['virtual_type']==2){

            if($config['moor_virtual_type']==1){

                $res = $this->moorPhoneBind($coach_phone,$order['address_info']['mobile'],$order,$config);

            }else{

                $res = $this->moorPhoneBindWebCall($coach_phone,$order['address_info']['mobile'],$order,$config);

                if($res!=false){

                    return '';
                }
            }

        }else{

            $res = $this->winnerPhoneBind($coach_phone,$order['address_info']['mobile'],$order,$config);
        }

        if($res==false){

            if($type==1){

                return $order['address_info']['mobile'];

            }else{

                return $order['coach_info']['mobile'];
            }
        }

        return $res;
    }


    /**
     * @param $order
     * @param $coach_phone
     * @param $config
     * @功能说明:阿里云隐私号码绑定
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-16 16:33
     */
    public function aliPhoneBind($order,$coach_phone,$config){

        $core_model   = new aliyunVirtual();

        $record_model = new \app\virtual\model\Record();
        //查询客户电话和技师电话有无绑定关系
        $arr = [$order['address_info']['mobile'],$coach_phone];

        foreach ($arr as $value){

            $find = $record_model->findRecord($value,$config['pool_key']);
            //解除绑定关系
            if(!empty($find)){

                foreach ($find as $values){

                    $record_model->dataUpdate(['id'=>$values['id']],['status'=>-1]);

                    $core_model->delBind($order['uniacid'],$values['subs_id'],$values['phone_x'],$values['pool_key']);
                }
            }
        }
        //新增绑定关系 过期时间
        $expiration = date('Y-m-d H:i:s',time()+180);

        $res = $core_model->bindPhone($order['uniacid'],$coach_phone,$order['address_info']['mobile'],$expiration,$config['pool_key'],$order['id']);

        $insert  = [

            'uniacid' =>$order['uniacid'],

            'order_id' =>$order['id'],

            'order_code' =>$order['order_code'],

            'phone_a' => $coach_phone,

            'phone_b' => $order['address_info']['mobile'],

            'phone_x' => !empty($res['secretBindDTO']['secretNo'])?$res['secretBindDTO']['secretNo']:'',

            'subs_id' => !empty($res['secretBindDTO']['subsId'])?$res['secretBindDTO']['subsId']:'',

            'pool_key'=> $config['pool_key'],

            'expire_date' => strtotime($expiration),

            'create_time' => time(),

            'status' => -1,

            'text' => json_encode($res)

        ];
        //绑定成功
        if(!empty($res['code'])&&$res['code']=='OK'&&!empty($res['message'])&&$res['message']=='OK'){

            $insert['status'] = 1;
            //返回虚拟号码
            $true_phone =  $insert['phone_x'];

        }else{

            $true_phone =  false;

        }

        $record_model->dataAdd($insert);

        return $true_phone;

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-16 15:58
     * @功能说明:云信号码绑定
     */
    public function winnerPhoneBind($phoneA,$phoneB,$order,$config){

        $phone_arr = !empty($config['winnerlook_phone_arr'])?explode(',',$config['winnerlook_phone_arr']):[];

        if(empty($phone_arr)){

            return false;
        }
        $record_model = new \app\virtual\model\Record();

        $coach_model = new Coach();

        $city_model  = new City();

        $city_id = $coach_model->where(['id'=>$order['coach_id']])->value('city_id');

        $city_info = $city_model->dataInfo(['id'=>$city_id]);

        if(!empty($city_info['winner_appid'])&&!empty($city_info['winner_token'])){

            $is_city = 1;

            $moor_model = new Winnerlook($order['uniacid'],$city_info['winner_appid'],$city_info['winner_token']);

        }else{

            $is_city = 0;

            $moor_model = new Winnerlook($order['uniacid']);
        }
        //查询客户电话和技师电话有无绑定关系
        $arr = [$phoneA,$phoneB];

        foreach ($arr as $value){

            $find = $record_model->findRecord($value,0,3);
            //解除绑定关系
            if(!empty($find)){

                foreach ($find as $values){

                    $record_model->dataUpdate(['id'=>$values['id']],['status'=>-1]);

                    $res = $moor_model->delBind($values['phone_a'],$values['phone_b'],$values['phone_x']);
                }
            }
        }

        if($is_city==0){
            //查询可用虚拟号码
            foreach ($phone_arr as $value){

                $dis = [

                    'status' => 1,

                    'phone_x'=> $value

                ];
                //查询在用未过期的号码绑定记录
                $count = $record_model->where($dis)->where('expire_date','>',time())->count();
                //每个号码只有200个并发
                if($count<=200){

                    $phoneX = $value;
                }
            }

            if(empty($phoneX)){

                return false;
            }
        }else{

            $phoneX = '';
        }
        //绑定号码
        $res = $moor_model->bindphone($phoneA,$phoneB,$phoneX,$order['id'],$is_city);

        $insert  = [

            'uniacid' =>$order['uniacid'],

            'order_id' =>$order['id'],

            'order_code' =>$order['order_code'],

            'phone_a' => $phoneA,

            'phone_b' => $phoneB,

            'phone_x' => !empty($res['middleNumber'])?$res['middleNumber']:$phoneX,

            'subs_id' => !empty($res['bindId'])?$res['bindId']:'',

            'create_time' => time(),

            'status' => -1,

            'expire_date' => time()+60,

            'text' => json_encode($res),

            'type' => 3

        ];
        //绑定成功
        if(!empty($res['result'])&&($res['result']==000000)){

            $insert['status'] = 1;
            //返回虚拟号码
        }else{

            $phoneX =  false;

        }

        $record_model->dataAdd($insert);

        return !empty($res['middleNumber'])?$res['middleNumber']:$phoneX;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-16 15:58
     * @功能说明:七莫号码绑定
     */
    public function moorPhoneBind($phoneA,$phoneB,$order,$config){

        $phone_arr = !empty($config['moor_phone_arr'])?explode(',',$config['moor_phone_arr']):[];

        if(empty($phone_arr)){

            return false;
        }
        $record_model = new \app\virtual\model\Record();

        $moor_model   = new Moor($order['uniacid']);
        //查询客户电话和技师电话有无绑定关系
        $arr = [$phoneA,$phoneB];

        foreach ($arr as $value){

            $find = $record_model->findRecord($value,0,2);
            //解除绑定关系
            if(!empty($find)){

                foreach ($find as $values){

                    $record_model->dataUpdate(['id'=>$values['id']],['status'=>-1]);

                    $moor_model->delBind($values['subs_id'],$values['phone_x']);
                }

            }

        }
        //查询可用虚拟号码
        foreach ($phone_arr as $value){

            $dis = [

                'status' => 1,

                'phone_x'=> $value

            ];
            //查询在用未过期的号码绑定记录
            $count = $record_model->where($dis)->where('expire_date','>',time())->count();
            //每个号码只有5个并发
            if($count<=5){

                $phoneX = $value;
            }
        }

        if(empty($phoneX)){

            return false;
        }
        //绑定号码
        $res = $moor_model->bindphone($phoneA,$phoneB,$phoneX,$order['id']);

        $insert  = [

            'uniacid' =>$order['uniacid'],

            'order_id' =>$order['id'],

            'order_code' =>$order['order_code'],

            'phone_a' => $phoneA,

            'phone_b' => $phoneB,

            'phone_x' => $phoneX,

            'subs_id' => !empty($res['mappingId'])?$res['mappingId']:'',

            'create_time' => time(),

            'status' => -1,

            'expire_date' => time()+60,

            'text' => json_encode($res)

        ];
        //绑定成功
        if(!empty($res['code'])&&$res['code']=='200'){

            $insert['status'] = 1;
            //返回虚拟号码
        }else{

            $phoneX =  false;

        }

        $record_model->dataAdd($insert);

        return $phoneX;
    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-16 15:58
     * @功能说明:七莫号码绑定
     */
    public function moorPhoneBindWebCall($phoneA,$phoneB,$order,$config){

        $phone_arr = !empty($config['moor_phone_arr'])?explode(',',$config['moor_phone_arr']):[];

        if(empty($phone_arr)){

            return false;
        }
        $record_model = new \app\virtual\model\Record();

        $moor_model   = new Moor($order['uniacid']);
        //查询客户电话和技师电话有无绑定关系
        $arr = [$phoneA,$phoneB];

        foreach ($arr as $value){

            $find = $record_model->findRecord($value,0,2);
            //解除绑定关系
            if(!empty($find)){

                foreach ($find as $values){

                    $record_model->dataUpdate(['id'=>$values['id']],['status'=>-1]);

                    $moor_model->delBind($values['subs_id'],$values['phone_x']);
                }

            }

        }
        //查询可用虚拟号码
        foreach ($phone_arr as $value){

            $dis = [

                'status' => 1,

                'phone_x'=> $value

            ];
            //查询在用未过期的号码绑定记录
            $count = $record_model->where($dis)->where('expire_date','>',time())->count();
            //每个号码只有10个并发
            if($count<=10){

                $phoneX = $value;
            }
        }

        if(empty($phoneX)){

            return false;
        }
        //绑定号码
        $res = $moor_model->webCallPhone($phoneA,$phoneB,$phoneX,$order['order_code']);

        $insert  = [

            'uniacid' =>$order['uniacid'],

            'order_id' =>$order['id'],

            'order_code' =>$order['order_code'],

            'phone_a' => $phoneA,

            'phone_b' => $phoneB,

            'phone_x' => $phoneX,

            'subs_id' => !empty($res['ActionID'])?$res['ActionID']:'',

            'create_time' => time(),

            'status' => -1,

            'expire_date' => time()+60,

            'text' => json_encode($res)
        ];
        //绑定成功
        if(!empty($res['Succeed'])&&$res['Succeed']=='true'){

            $insert['status'] = 1;
            //返回虚拟号码
        }else{

            $phoneX = false;
        }

        $record_model->dataAdd($insert);

        return $phoneX;
    }
    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-12-09 16:22
     * @功能说明:解除绑定虚拟号码
     */
    public function delBindVirtualPhone($order){

        $record_model = new \app\virtual\model\Record();

        $virtual_model = new aliyunVirtual();

        $winner_model  = new Winnerlook($order['uniacid']);

        $moor_model = new Moor($order['uniacid']);

        $datas = $record_model->where(['order_id'=>$order['id'],'status'=>1])->select()->toArray();

        if(!empty($datas)){

            foreach ($datas as $data){

                $record_model->dataUpdate(['id'=>$data['id']],['status'=>-1]);

                if($data['type']==1){
                    //解除绑定 阿里
                    $virtual_model->delBind($order['uniacid'],$data['subs_id'],$data['phone_x'],$data['pool_key']);

                }elseif($data['type']==2){
                    //七陌
                    $moor_model->delBind($data['subs_id'],$data['phone_x']);

                }else{

                    $winner_model->delBind($data['phone_a'],$data['phone_b'],$data['phone_x']);
                }

            }

        }

        return true;
    }











}