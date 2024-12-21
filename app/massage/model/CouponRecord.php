<?php
namespace app\massage\model;

use app\BaseModel;
use think\facade\Db;

class CouponRecord extends BaseModel
{
    //定义表名
    protected $name = 'massage_service_coupon_record';


    protected $append = [

        'service'
    ];


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-11 01:54
     * @功能说明:
     */
    public function getServiceAttr($value,$data){

        if(!empty($data['id'])&&isset($data['table_type'])){

            $table = $this->getServiceTable($data['table_type']);

            $id = !empty($data['pid'])?$data['pid']:$data['id'];

            $ser_model = new Service();

            $dis = [

                'a.status'    => 1,

                'b.coupon_id' => $id,

                'b.type'      => 1
            ];

            $list =  $ser_model->alias('a')
                ->join("$table b",'b.goods_id = a.id')
                ->where($dis)
                ->field('a.id,a.title,a.price,b.goods_id')
                ->group('a.id')
                ->order('a.top desc,a.id desc')
                ->select()
                ->toArray();

            return $list;
        }

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-15 14:37
     * @功能说明:后台列表
     */
    public function adminDataList($dis,$page=10,$where=[]){

        $data = $this->alias('a')
                ->join('shequshop_school_cap_list b','a.cap_id = b.id')
                ->where($dis)
                ->where(function ($query) use ($where){
                    $query->whereOr($where);
                })
                ->field('a.*,b.store_name,b.store_img,b.name,b.mobile')
                ->group('a.id')
                ->order('a.id desc')
                ->paginate($page)
                ->toArray();

        return $data;

    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-15 14:37
     * @功能说明:后台审核详情
     */
    public function adminDataInfo($dis){

        $data = $this->alias('a')
            ->join('shequshop_school_cap_list b','a.cap_id = b.id')
            ->where($dis)
            ->field('a.*,b.store_name,b.store_img,b.school_name,b.mobile')
            ->find();

        return !empty($data)?$data->toArray():[];

    }

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
     * @DataTime: 2021-04-08 17:08
     * @功能说明:审核中
     */
    public function shIng($cap_id){

        $dis = [

            'cap_id' => $cap_id,

            'status' => 1
        ];

        $count = $this->where($dis)->count();

        return $count;

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
     * @DataTime: 2020-09-29 11:43
     * @功能说明:
     */
    public function dataInfo($dis){

        $data = $this->where($dis)->find();

        return !empty($data)?$data->toArray():[];

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-06 00:02
     * @功能说明:用户订单数
     */
    public function couponCount($user_id){

        $dis[] = ['user_id','=',$user_id];

        $dis[] = ['status','=',1];

        $dis[] = ['end_time','>',time()];

        $data = $this->where($dis)->sum('num');

        return $data;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-08 11:57
     * @功能说明:初始化
     */
    public function initCoupon($uniacid){

        $dis[] = ['status','=',1];

        $dis[] = ['uniacid','=',$uniacid];

        $dis[] = ['end_time','<',time()];

        $res = $this->dataUpdate($dis,['status'=>3]);

        return $res;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-12 15:36
     * @功能说明:派发优惠券
     */
    public function recordAdd($coupon_id,$user_id,$num=1){

        $coupon_model = new Coupon();

        $coupon = $coupon_model->dataInfo(['id'=>$coupon_id]);

        if($coupon['send_type']==2&&$coupon['stock']<$num){

            return ['code'=>500,'msg'=>'库存不足'];
        }
        //注意这里是一个简单的水平分表 也可以用mysql 中间键
        $table_type = $this->where(['uniacid'=>$coupon['uniacid']])->max('table_type');

        $table = $this->getServiceTable($table_type);

        $count = Db::name($table)->where(['uniacid'=>$coupon['uniacid']])->count();
        //600w分
        if($count>6000000){

            $table_type++;

            $table = $this->getServiceTable($table_type);
        }

        $insert = [

            'uniacid'   => $coupon['uniacid'],

            'user_id'   => $user_id,

            'coupon_id' => $coupon_id,

            'title'     => $coupon['title'],

            'type'      => $coupon['type'],

            'full'      => $coupon['full'],

            'discount'  => $coupon['discount'],

            'rule'      => $coupon['rule'],

            'text'      => $coupon['text'],

            'admin_id'  => $coupon['admin_id'],

            'use_scene'  => $coupon['use_scene'],

            'num'       => $num,

            'table_type'=> $table_type,

            'start_time'=> $coupon['time_limit']==1?time():$coupon['start_time'],

            'end_time'  => $coupon['time_limit']==1?time()+$coupon['day']*86400:$coupon['end_time'],
        ];

        Db::startTrans();

        $res = $this->dataAdd($insert);

        if($res==0){

            Db::rollback();

            return $res;
        }

        $record_id = $this->getLastInsID();

        if($coupon['send_type']==2){
            //修改优惠券库存
            $res = $coupon_model->dataUpdate(['id'=>$coupon_id,'i'=>$coupon['i']],['stock'=>$coupon['stock']-$num,'i'=>$coupon['i']+1]);

            if($res==0){

                Db::rollback();

                return $res;
            }
        }

        if(!empty($coupon['service'])){

            foreach ($coupon['service'] as $value){

                $insert = [

                    'uniacid' => $coupon['uniacid'],

                    'type'    => 1,

                    'goods_id'=> $value['goods_id'],

                    'coupon_id'=> $record_id,

                ];

                $res = Db::name($table)->insert($insert);
            }
        }

        if(!empty($coupon['store'])){

            foreach ($coupon['store'] as $value){

                $insert = [

                    'uniacid' => $coupon['uniacid'],

                    'type'    => 1,

                    'store_id'=> $value['store_id'],

                    'coupon_id'=> $record_id,

                ];

                $res = Db::name('massage_service_coupon_store')->insert($insert);
            }
        }

        Db::commit();

        return $res;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-08-29 23:02
     * @功能说明:退换优惠券
     */
    public function couponRefund($order_id){

        $order_model = new Order();

        $coupon_id   = $order_model->where(['id'=>$order_id])->value('coupon_id');

        $order_model->dataUpdate(['id'=>$order_id],['coupon_id'=>0]);

        if(!empty($coupon_id)){

            $this->dataUpdate(['id'=>$coupon_id],['status'=>1,'use_time'=>0,'order_id'=>0]);
        }

        return true;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-13 09:34
     * @功能说明:使用优惠券
     */
    public function couponUse($coupon_id,$order_id=0,$hx_store_id=0,$hx_user_id=0,$hx_admin_id=0,$hx_role_id=0){

        if(empty($coupon_id)){

            return true;
        }

        $record = $this->dataInfo(['id'=>$coupon_id]);

        if($record['num']>1){

            $this->dataUpdate(['id'=>$coupon_id],['num'=>$record['num']-1]);

            unset($record['id']);

            if(isset($record['service'])){

                unset($record['service']);
            }

            $record['pid']      = $coupon_id;

            $record['num']      = 1;

            $record['status']   = 2;

            $record['use_time'] = time();

            $record['order_id'] = $order_id;

            $record['hx_store_id'] = $hx_store_id;
            $record['hx_user_id'] = $hx_user_id;
            $record['hx_admin_id'] = $hx_admin_id;
            $record['hx_role_id'] = $hx_role_id;

            $res = $this->insert($record);

            $coupon_id = $this->getLastInsID();

        }else{

             $res = $this->dataUpdate(['id'=>$coupon_id,'status'=>1],['status'=>2,'use_time'=>time(),'order_id'=>$order_id,'hx_store_id'=>$hx_store_id,'hx_user_id'=>$hx_user_id,'hx_admin_id'=>$hx_admin_id,'hx_role_id'=>$hx_role_id]);
        }

        if($res==0){

            return false;
        }

        if(!empty($order_id)){

            $order_model = new Order();

            $res = $order_model->dataUpdate(['id'=>$order_id],['coupon_id'=>$coupon_id]);

            if($res==0){

                return false;
            }
        }

        return $coupon_id;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-01-16 11:20
     * @功能说明:自动获取优惠券
     */
    public function autoGetCoupon($user_id,$uniacid,$user_create_time,$lng,$lat){

        $coupon_cash = 0;

        $coupon_get_type = getConfigSetting($uniacid,'coupon_get_type');

        $key = 'autoGetCouponautoGetCoupon'.$user_id;

        incCache($key,1,$uniacid,30);

        if(getCache($key,$uniacid)==1){

            if($coupon_get_type==1){

                $coupon_record_model = new CouponRecord();

                $have_get = $coupon_record_model->where(['user_id'=>$user_id])->column('coupon_id');

                $dis[] = ['a.uniacid','=',$uniacid];

                $dis[] = ['a.send_type','=',2];

                $dis[] = ['a.status','=',1];

                $dis[] = ['a.stock','>',0];

                $dis[] = ['a.id','not in',$have_get];

                $time = strtotime(date('Y-m-d',time()));
                //不是新用户
                if($user_create_time<$time){

                    $dis[] = ['a.user_limit','<>',2];
                }

                $agent_coupon_location = getConfigSetting($uniacid,'agent_coupon_location');

                $where = [];

                if($agent_coupon_location==1){

                    $city = getCityByLat($lng,$lat,$uniacid);

                    $city = array_values($city);

                    $where[] = ['a.admin_id','=',0];

                    $where[] = ['c.title','in',$city];
                }

                $data = Db::name('massage_service_coupon')->alias('a')
                    ->join('shequshop_school_admin b','a.admin_id = b.id','left')
                    ->join('massage_service_city_list c','b.city_id = c.id','left')
                    ->where($dis)
                    ->where(function ($query) use ($where){
                        $query->whereOr($where);
                    })
                    ->field('a.id,a.title,a.user_limit,a.full,a.discount')
                    ->group('a.id')
                    ->select();

                $coupon_cash = array_sum(array_column($data,'discount'));

                if(!empty($data)){

                    foreach ($data as $value){
                        //领取优惠券
                        $coupon_record_model->recordAdd($value,$user_id);
                    }
                }
            }
        }

        decCache($key,1,$uniacid);

        return $coupon_cash;
    }






    public function useCounponGoods(){

        $coupon_model = new CouponRecord();

        $data = $coupon_model->alias('a')
                ->join('massage_service_coupon_goods b','a.id = b.coupon_id')
                ->where('a.status','in',[2,3])
                ->where('b.type','=',1)
                ->group('b.id')
                ->count();

        return $data;
    }


    /**
     * @param int $type
     * @功能说明:
     * @author chenniang
     * @DataTime: 2024-08-14 14:49
     */
    public function getServiceTable($type=1){

        switch ($type){

            case 1:
                $table = 'massage_service_coupon_goods';
                break;
            case 2:
                $table = 'massage_service_coupon_goods_v2';
                break;
            case 3:
                $table = 'massage_service_coupon_goods_v3';
                break;
            case 4:
                $table = 'massage_service_coupon_goods_v4';
                break;
            case 5:
                $table = 'massage_service_coupon_goods_v5';
                break;
            case 6:
                $table = 'massage_service_coupon_goods_v6';
                break;
            case 7:
                $table = 'massage_service_coupon_goods_v7';
                break;
            case 8:
                $table = 'massage_service_coupon_goods_v8';
                break;
            case 9:
                $table = 'massage_service_coupon_goods_v9';
                break;
            default:
                $table = 'massage_service_coupon_goods_v9';
                break;
        }

        return $table;
    }



    /**
     * @param int $type
     * @功能说明:
     * @author chenniang
     * @DataTime: 2024-08-14 14:49
     */
    public function getStoreTable($type=1){

        switch ($type){

            case 1:
                $table = 'massage_service_coupon_goods';
                break;
            case 2:
                $table = 'massage_service_coupon_goods_v2';
                break;
            case 3:
                $table = 'massage_service_coupon_goods_v3';
                break;
            case 4:
                $table = 'massage_service_coupon_goods_v4';
                break;
            case 5:
                $table = 'massage_service_coupon_goods_v5';
                break;
            case 6:
                $table = 'massage_service_coupon_goods_v6';
                break;
            case 7:
                $table = 'massage_service_coupon_goods_v7';
                break;
            case 8:
                $table = 'massage_service_coupon_goods_v8';
                break;
            case 9:
                $table = 'massage_service_coupon_goods_v9';
                break;
            default:
                $table = 'massage_service_coupon_goods_v9';
                break;
        }

        return $table;
    }





    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-13 09:34
     * @功能说明:作废优惠券
     */
    public function couponCancel($coupon_id,$num=1,$hx_admin_id=0){

        if(empty($coupon_id)){

            return true;
        }

        $record = $this->dataInfo(['id'=>$coupon_id]);

        if($num>$record['num']){

            return ['code'=>500,'msg'=>'超过卡券数量'];
        }

        if($record['status']!=1){

            return ['code'=>500,'msg'=>'订单状态错误'];
        }

        if($record['num']>$num){

            $this->dataUpdate(['id'=>$coupon_id],['num'=>$record['num']-$num]);

            unset($record['id']);

            if(isset($record['service'])){

                unset($record['service']);
            }

            $record['pid']      = $coupon_id;

            $record['num']      = $num;

            $record['status']   = 4;

            $record['use_time'] = time();

            $record['hx_admin_id'] = $hx_admin_id;

            $res = $this->insert($record);

            $coupon_id = $this->getLastInsID();

        }else{

            $res = $this->dataUpdate(['id'=>$coupon_id,'status'=>1],['status'=>4,'use_time'=>time(),'hx_admin_id'=>$hx_admin_id]);
        }

        if($res==0){

            return false;
        }

        return $coupon_id;
    }



}