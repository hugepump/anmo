<?php

namespace app\hotel\controller;

use app\AdminRest;
use app\ApiRest;

use app\dynamic\model\DynamicComment;
use app\dynamic\model\DynamicFollow;
use app\dynamic\model\DynamicList;
use app\dynamic\model\DynamicThumbs;
use app\dynamic\model\DynamicWatchRecord;
use app\hotel\model\HotelList;
use app\hotel\model\HotelService;
use app\hotel\model\HotelUpdate;
use app\massage\model\Admin;
use app\massage\model\City;
use app\massage\model\Coach;

use app\massage\model\Goods;

use app\massage\model\MassageConfig;
use app\massage\model\Order;

use app\store\model\StoreList;
use think\App;
use think\facade\Db;
use think\Request;


class AdminHotel extends AdminRest
{



    protected $model;


    public function __construct(App $app)
    {

        parent::__construct($app);

        $this->model = new HotelList();



    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-01-29 11:00
     * @功能说明:酒店列表
     */
    public function hotelList(){

        $input = $this->_param;

        $dis[] = ['a.uniacid','=',$this->_uniacid];

        if(!empty($input['title'])){

            $dis[] = ['a.title','like','%'.$input['title'].'%'];
        }

        if(!empty($input['create_user'])){

            $dis[] = ['b.agent_name','like','%'.$input['create_user'].'%'];
        }

        if($this->_user['is_admin']==0){

            $dis[] = ['a.admin_id','=',$this->_user['admin_id']];
        }

        if(isset($input['admin_id'])){

            $dis[] = ['a.admin_id','=',$input['admin_id']];
        }

        if(!empty($input['is_update'])){

            $dis[] = ['a.is_update','=',$input['is_update']];

        }

        if(!empty($input['status'])){

            $dis[] = ['a.status','=',$input['status']];

        }else{

            $dis[] = ['a.status','>',-1];
        }

        $data = $this->model->adminDataList($dis,$input['limit']);

        $list = [

            0 =>'all',

            1 =>'ing',

            2 =>'pass',

            4 =>'nopass',

            5=>'update_num'

        ];

        foreach ($list as $k=> $value){

            $dis_s = [];

            $dis_s[] =['uniacid','=',$this->_uniacid];

            if($this->_user['is_admin']==0){

                $dis_s[] = ['admin_id','=',$this->_user['admin_id']];
            }

            if(!empty($k)&&$k!=5){

                $dis_s[] = ['status','=',$k];

            }else{

                $dis_s[] = ['status','>',-1];

            }

            if($k==5){

                $dis_s[] = ['is_update','=',1];
            }

            $data[$value] = $this->model->where($dis_s)->count();
        }

        return $this->success($data);

    }


    /**
     * @author chenniang
     * @DataTime: 2024-11-14 14:29
     * @功能说明:酒店详情
     */
    public function hotelInfo(){

        $input = $this->_param;

        $dis= [

            'id' => $input['id']
        ];

        if($this->_user['is_admin']==0){

            $dis['admin_id'] = $this->_user['admin_id'];
        }

        $data = $this->model->dataInfo($dis);

        $data['service'] = HotelService::getService($data);

        if(!empty($data['admin_id'])){

            $admin = Admin::where(['id'=>$data['admin_id']])->field('city_type,agent_name,city_id')->find();

            if(!empty($admin)){

                $data['city_type']  = $admin['city_type'];

                $data['agent_name'] = $admin['agent_name'];

                $data['admin_city'] = City::where(['id'=>$admin['city_id']])->value('title');
            }
        }

        return $this->success($data);
    }


    /**
     * @author chenniang
     * @DataTime: 2024-11-14 14:53
     * @功能说明:添加
     */
    public function hotelAdd(){

        $input = $this->_input;

        $input['uniacid'] = $this->_uniacid;

        $input['status'] = 2;

        $input['create_user'] = $this->_user['id'];

        if($this->_user['is_admin']==0){

            $input['status'] = 1;

            $input['admin_id'] = $this->_user['admin_id'];
        }

        $input['imgs'] = implode(',',$input['imgs']);

        if(isset($input['service'])){

            $service = $input['service'];

            unset($input['service']);
        }

        $res = $this->model->dataAdd($input);

        $id = $this->model->getLastInsID();

        if(!empty($service)){

            foreach ($service as $key=>$value){

                $s_insert[$key] = [

                    'hotel_id' => $id,

                    'uniacid'  => $this->_uniacid,

                    'service_id'=> $value
                ];
            }

            HotelService::createAll($s_insert);
        }

        return $this->success($res);
    }


    /**
     * @author chenniang
     * @DataTime: 2024-11-14 14:53
     * @功能说明:添加
     */
    public function hotelUpdate(){

        $input = $this->_input;

        $input['uniacid'] = $this->_uniacid;

        $input['imgs'] = implode(',',$input['imgs']);

        $data = $this->model->dataInfo(['id'=>$input['id']]);

        if($this->_user['is_admin']!=1){

            if($data['status']==4){

                $input['status'] = 1;
            }else{
                $this->errorMsg('你无权限');
            }
        }

        if(isset($input['service'])){

            HotelService::where(['hotel_id'=>$input['id']])->delete();

            $service = $input['service'];

            unset($input['service']);
        }

        $res = $this->model->dataUpdate(['id'=>$input['id']],$input);

        if(!empty($service)){

            foreach ($service as $key=>$value){

                $s_insert[$key] = [

                    'hotel_id' => $input['id'],

                    'uniacid'  => $this->_uniacid,

                    'service_id'=> $value
                ];
            }

            HotelService::createAll($s_insert);
        }

        return $this->success($res);
    }


    /**
     * @author chenniang
     * @DataTime: 2024-11-14 14:55
     * @功能说明:状态修改
     */
    public function hotelStatusUpdate(){

        $input = $this->_input;

        $res = $this->model->dataUpdate(['id'=>$input['id']],['status'=>$input['status']]);

        return $this->success($res);

    }


    /**
     * @author chenniang
     * @DataTime: 2024-11-14 17:19
     * @功能说明:代理商修改
     */
    public function adminHotelUpdate(){

        $input = $this->_input;

        $map= [

            'id' => $input['id'],

            'admin_id' => $this->_user['admin_id']
        ];

        $data = $this->model->dataInfo($map);

        if(empty($data)){

            $this->errorMsg('你无权限');
        }

        $input['uniacid'] = $this->_uniacid;

        if(isset($input['imgs'])&&$input['imgs']!=-1734593){

            $input['imgs'] = implode(',',$input['imgs']);
        }

        $input['hotel_id'] = $input['id'];

        $update_model = new HotelUpdate();

        if(isset($input['service'])&&$input['service']!=-1734593){

            $service = $input['service'];

            unset($input['service']);
        }

        Db::startTrans();

        $res = $this->model->dataUpdate(['id'=>$input['id']],['is_update'=>1]);

        if($res==0){

            Db::rollback();

            $this->errorMsg('修改失败1');
        }

        $res = HotelUpdate::where(['hotel_id'=>$input['id']])->update(['status'=>-1]);

//        if($res==0){
//
//            Db::rollback();
//
//            $this->errorMsg('修改失败2');
//        }

        $hotel_id = $input['id'];

        unset($input['id']);

        $res = $update_model->insert($input);

        $update_id = $update_model->getLastInsID();

        if(!empty($service)){

            foreach ($service as $key=>$value){

                $s_insert[$key] = [

                    'hotel_id' => $hotel_id,

                    'uniacid'  => $this->_uniacid,

                    'service_id'=> $value,

                    'type' => 1,

                    'update_id'=>$update_id
                ];
            }

            HotelService::createAll($s_insert);
        }

        Db::commit();

        return $this->success($res);

    }



    /**
     * @author chenniang
     * @DataTime: 2024-10-12 18:17
     * @功能说明:酒店审核
     */
    public function hotelCheck(){

        $input = $this->_input;

        $update['status'] = $input['status'];

        $update['sh_time'] = time();

        $update['sh_text'] = $input['sh_text'];

        $res = $this->model->dataUpdate(['id'=>$input['id']],$update);

        return $this->success($res);
    }


    /**
     * @author chenniang
     * @DataTime: 2024-11-14 17:35
     * @功能说明:酒店内容
     */
    public function hotelDataCheck(){

        $input = $this->_input;

        $update_model = new HotelUpdate();

        $data = $update_model->dataInfo(['id'=>$input['id'],'status'=>1]);

        if(empty($data)){

            $this->errorMsg('没有修改记录');
        }

        $arr = ['title','province','city','area','address','lng','lat','star','phone1','phone2','cover','imgs','min_price'];

        $update = ['is_update'=>0];

        foreach ($arr as $value){

            if($data[$value]!=-1734593){

                if($value=='imgs'){

                    $data['imgs'] = implode(',',$data['imgs']);
                }

                $update[$value] = $data[$value];
            }
        }

        Db::startTrans();

        $res = $this->model->dataUpdate(['id'=>$data['hotel_id']],$update);

        if($res==0){

            Db::rollback();

            $this->errorMsg('审核失败');
        }

        $res = $update_model->dataUpdate(['id'=>$input['id']],['status'=>2]);

        if($res==0){

            Db::rollback();

            $this->errorMsg('审核失败');
        }

        $service = HotelService::where(['update_id'=>$input['id']])->select()->toArray();

        if($data['service']!=-1734593){

            HotelService::where(['hotel_id'=>$data['hotel_id'],'type'=>0])->delete();

            if(!empty($service)){

                foreach ($service as $key=>$value){

                    $s_insert[$key] = [

                        'hotel_id' => $data['hotel_id'],

                        'uniacid'  => $this->_uniacid,

                        'service_id'=> $value['service_id']
                    ];
                }

                HotelService::createAll($s_insert);
            }
        }

        Db::commit();

        return $this->success($res);
    }


    /**
     * @author chenniang
     * @DataTime: 2024-11-15 14:27
     * @功能说明:修改详情
     */
    public function hotelUpdateInfo(){

        $input = $this->_param;

        $update_model = new HotelUpdate();

        $data = $update_model->dataInfo(['hotel_id'=>$input['id'],'status'=>1]);

        if(empty($data)){

            $this->errorMsg('没有修改记录');
        }

        $data['service'] = HotelService::getServiceUpdate($data['id']);

        $data['service'] = !empty($data['service'])?$data['service']:-1734593;

        return $this->success($data);
    }









}
