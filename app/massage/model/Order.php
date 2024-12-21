<?php
namespace app\massage\model;

use app\abnormalorder\model\OrderList;
use app\balancediscount\model\UserCard;
use app\BaseModel;
use app\coachbroker\model\CoachBroker;
use app\member\model\Level;
use app\store\model\StoreList;
use Exception;
use longbingcore\permissions\AdminMenu;
use think\facade\Db;

class Order extends BaseModel
{
    //定义表名
    protected $name = 'massage_service_order_list';


    protected $append = [

        'coach_info',

        'order_goods',

        'all_goods_num',

        'address_info'

    ];

    /**
     * @param $value
     * @param $data
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-11-10 11:38
     */
    public function getPayModelAttr($value,$data){
        //兼容
        if(isset($value)&&isset($data['balance'])){

            if($value==1&&$data['balance']>0){

                $value = 2;
            }
            return $value;
        }
    }


    /**
     * @param $value
     * @param $data
     * @功能说明:地址信息
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-04-08 10:11
     */
    public function getAddressInfoAttr($value,$data){

        if(!empty($data['id'])){

            $address_model = new OrderAddress();

            $address_info = $address_model->dataInfo(['order_id'=>$data['id']]);

            return $address_info;
        }
    }


    /**
     * @param $value
     * @param $data
     * @功能说明:总商品数量
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-25 14:39
     */
    public function getAllGoodsNumAttr($value,$data){

        if(!empty($data['id'])){

            $order_goods_model = new OrderGoods();

            $dis = [

                'order_id' => $data['id'],

                'status' => 1
            ];

            $num = $order_goods_model->where($dis)->sum('num');

            return $num;
        }
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-19 17:05
     * @功能说明:子订单信息
     */

    public function getOrderGoodsAttr($value,$data){

        if(!empty($data['id'])){

            $order_goods_model = new OrderGoods();

            $dis = [

                'order_id' => $data['id'],

                'status' => 1
            ];

            $list = $order_goods_model->where($dis)->select()->toArray();

            return $list;
        }
    }


    /**
     * @param $value
     * @param $data
     * @功能说明:兼容老版本
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-06-14 11:43
     */
    public function getInitMaterialPriceAttr($value,$data){

        if(isset($value)&&isset($data['material_price'])){

            if($value>0){

                return $value;
            }else{

                return $data['material_price'];
            }
        }
    }


    /**
     * @param $value
     * @param $data
     * @功能说明:订单的团长信息
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-19 16:49
     */
    public function getCoachInfoAttr($value,$data){

        if(isset($data['coach_id'])&&isset($data['id'])&&isset($data['add_pid'])){

            if(!empty($data['coach_id'])&&$data['coach_id']>0){

                $info = Db::name('massage_service_coach_list')->where(['id'=>$data['coach_id']])->field('id,city_id,uniacid,coach_name,mobile,work_img,lng,lat')->find();

            }else{

                $change_log_model = new CoachChangeLog();

                $order_id = !empty($data['add_pid'])?$data['add_pid']:$data['id'];

                $info['coach_name'] = $change_log_model->where(['order_id'=>$order_id])->order('id desc')->value('now_coach_name');

                $info['mobile']   = $change_log_model->where(['order_id'=>$order_id])->order('id desc')->value('now_coach_mobile');

                $info['work_img'] = defaultCoachAvatar();
            }

            return $info;
        }
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-19 16:23
     * @功能说明:前端订单列表
     */

    public function indexDataList($dis,$mapor,$page=10){

        $data = $this->alias('a')
            ->join('massage_service_order_goods_list b','a.id = b.order_id')
            ->where($dis)
            ->where(function ($query) use ($mapor){
                $query->whereOr($mapor);
            })
            ->field('a.pay_model,a.start_service_time,a.balance,a.label_time,a.have_tx,a.id,a.coach_id,a.store_id,a.is_comment,a.order_code,a.true_service_price,a.pay_type,a.pay_price,a.start_time,a.create_time,a.user_id,a.end_time,a.add_pid,a.is_add,a.init_material_price,a.material_price')
            ->group('a.id')
            ->order('a.id desc')
            ->paginate($page)
            ->toArray();

        if(!empty($data['data'])){

            $abn_model = new OrderList();

            foreach ($data['data'] as &$v){

                $v['create_time'] = date('Y-m-d H:i:s',$v['create_time']);

                $v['start_time']  = date('Y-m-d H:i',$v['start_time']);

                $v['end_time']    = date('Y-m-d H:i',$v['end_time']);
                //异常订单标示
                $v['abn_order_id']= $abn_model->where(['order_id'=>$v['id']])->value('id');
            }
        }

        return $data;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-19 16:23
     * @功能说明:前端订单列表
     */

    public function coachDataList($dis,$mapor,$page=10,$sort='a.id desc'){

        $data = $this->alias('a')
            ->join('massage_service_order_goods_list b','a.id = b.order_id')
            ->where($dis)
            ->where(function ($query) use ($mapor){
                $query->whereOr($mapor);
            })
            ->field('a.pay_model,a.car_type,a.car_price,a.coach_cash,a.distance,a.start_service_time,a.balance,a.label_time,a.have_tx,a.id,a.coach_id,a.store_id,a.is_comment,a.order_code,a.true_service_price,a.pay_type,a.pay_price,a.start_time,a.create_time,a.user_id,a.end_time,a.add_pid,a.is_add,a.init_material_price,a.material_price')
            ->group('a.id')
            ->order($sort)
            ->paginate($page)
            ->toArray();

        if(!empty($data['data'])){

            $abn_model = new OrderList();

            foreach ($data['data'] as &$v){

                $v['distance']    = distance_text($v['distance']);

                $v['create_time'] = date('Y-m-d H:i:s',$v['create_time']);

                $v['start_time']  = date('Y-m-d H:i',$v['start_time']);

                $v['end_time']    = date('Y-m-d H:i',$v['end_time']);
                //异常订单标示
                $v['abn_order_id']= $abn_model->where(['order_id'=>$v['id']])->value('id');
            }
        }

        return $data;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-15 14:37
     * @功能说明:后台列表
     */
    public function adminDataList($dis,$page=10,$map=[],$phone_encryption=0){

        $data = $this->alias('a')
            ->join('massage_service_coach_list b','a.coach_id = b.id','left')
            ->join('massage_order_coach_change_logs c','(a.add_pid = c.order_id||a.id = c.order_id) AND c.is_new = 1','left')
            ->join('massage_channel_list e','a.channel_id = e.id','left')
            ->where($dis)
            ->where(function ($query) use ($map){
                $query->whereOr($map);
            })
            ->field('a.id,a.store_id,a.car_price,a.material_price,a.true_car_price,a.is_add,a.coach_id,a.user_id,a.distance,a.pay_price,a.type,a.car_type,a.service_price,a.true_service_price,a.start_time,a.admin_id,a.partner_id,a.order_code,a.transaction_id,a.pay_type,a.pay_model,a.balance,a.is_show,a.init_service_price,a.create_time,a.end_time,a.uniacid,a.add_pid,b.coach_name,e.user_name as channel_name,e.cate_id as channel_cate')
            ->group('a.id')
            ->order('a.id desc')
            ->paginate($page)
            ->toArray();

        if(!empty($data['data'])){

            $user_model  = new User();

            $admin_model = new Admin();

            $refund_model = new RefundOrder();

            $channel_cate = new ChannelCate();

            foreach ($data['data'] as &$v){

                $v['admin_name'] = $admin_model->where(['id'=>$v['admin_id']])->value('agent_name');

                $v['partner_name']= $user_model->where(['id'=>$v['partner_id']])->value('nickName');

                $v['agent_type'] = !empty($v['partner_id'])?2:1;
                //代理商如果没有权限需要隐藏用户的手机号
                if($phone_encryption==0&&!empty($v['address_info'])){

                    $v['address_info']['mobile'] = substr_replace($v['address_info']['mobile'], "****", 2,4);
                }

                $v['mobile'] = !empty($v['address_info'])?$v['address_info']['mobile']:'';

                $v['user_name'] = !empty($v['address_info'])?$v['address_info']['user_name']:'';

                $v['channel'] = $channel_cate->where(['id'=>$v['channel_cate']])->value('title');

                $v['nickName'] = $user_model->where(['id'=>$v['user_id']])->value('nickName');

                $v['distance'] = distance_text($v['distance']);

                $v['refund_price'] = $refund_model->where(['order_id'=>$v['id'],'status'=>2])->sum('refund_price');
                //加钟订单
                if($v['is_add']==0){

                    $v['add_order_id'] = Db::name('massage_service_order_list')->where(['add_pid'=>$v['id']])->where('pay_type','>',1)->field('id,order_code')->select();

                }else{

                    $v['add_pid'] = Db::name('massage_service_order_list')->where(['id'=>$v['add_pid']])->field('id,order_code')->find();

                }

                $v['can_refund_price'] = $this->getOrderRefundPrice($v);
            }
        }

        return $data;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-15 14:37
     * @功能说明:后台列表
     */
    public function adminDataListV2($dis,$page=10,$map=[],$phone_encryption=0){

        $data = $this
            ->where($dis)
            ->where(function ($query) use ($map){
                $query->whereOr($map);
            })
            ->field('coach_show,channel_qr_id,broker_id,free_fare,free_fare,id,discount,store_id,channel_id,car_price,material_price,true_car_price,is_add,coach_id,user_id,distance,pay_price,type,car_type,service_price,true_service_price,start_time,admin_id,partner_id,order_code,transaction_id,pay_type,pay_model,balance,is_show,init_service_price,create_time,end_time,uniacid,add_pid')
            ->order('id desc')
            ->paginate($page)
            ->toArray();

        if(!empty($data['data'])){

            $user_model  = new User();

            $admin_model = new Admin();

            $refund_model = new RefundOrder();

            $channel_list = new ChannelList();

            $channel_qr_model = new ChannelQr();

            $order_data_model = new OrderData();

            $broker_model = new CoachBroker();

            $abn_model = new OrderList();

            foreach ($data['data'] as &$v){

                $v['car_price'] = round($v['car_price'],2);

                $v['total_price'] = round($v['pay_price']+$v['discount'],2);

                if(!empty($v['channel_id'])){

                    $channel_info = $channel_list->dataInfo(['id'=>$v['channel_id']]);

                    if(!empty($channel_info)){

                        $v['channel_name'] = $channel_info['user_name'];

                        $v['channel'] = $channel_info['cate_text'];
                    }
                }
                // 渠道码
                if(!empty($v['channel_qr_id'])){

                    $v['channel_qr_name'] = $channel_qr_model->where(['id'=>$v['channel_qr_id']])->value('title');
                }else{

                    $v['channel_qr_name'] = '';
                }

                $v['coach_name'] = !empty($v['coach_info'])?$v['coach_info']['coach_name']:'';

                if(!empty($v['admin_id'])){

                    $v['admin_name'] = $admin_model->where(['id'=>$v['admin_id']])->value('agent_name');
                }else{

                    $v['admin_name'] = '';
                }

                if(!empty($v['broker_id'])){
                    //技师经纪人
                    $v['partner_name']= $broker_model->where(['id'=>$v['broker_id']])->value('user_name');
                }else{

                    $v['partner_name']= '';
                }

                $v['agent_type'] = !empty($v['partner_id'])?2:1;
                //代理商如果没有权限需要隐藏用户的手机号
                if($phone_encryption==0&&!empty($v['address_info'])){

                    $v['address_info']['mobile'] = substr_replace($v['address_info']['mobile'], "****", 2,4);
                }

                $v['mobile']   = !empty($v['address_info'])?$v['address_info']['mobile']:'';

                $v['user_name']= !empty($v['address_info'])?$v['address_info']['user_name']:'';

                $v['nickName'] = $user_model->where(['id'=>$v['user_id']])->value('nickName');

                $v['distance'] = distance_text($v['distance']);

                $v['refund_price'] = $refund_model->where(['order_id'=>$v['id'],'status'=>2])->sum('refund_price');
                //加钟订单
                if($v['is_add']==0){

                    $v['add_order_id'] = Db::name('massage_service_order_list')->where(['add_pid'=>$v['id']])->where('pay_type','>',1)->field('id,order_code')->select();

                }else{

                    $v['add_pid'] = Db::name('massage_service_order_list')->where(['id'=>$v['add_pid']])->field('id,order_code')->find();
                }
                //可退款金额
                $v['can_refund_price'] = $this->getOrderRefundPrice($v);
                //后台是否可以申请退款
                if($v['is_add']==0){

                    $v['admin_apply_refund'] = $this->orderCanRefundV2($v['order_goods'],$v['pay_type']);
                }
                //加钟流程
                $v['add_flow_path'] = $order_data_model->where(['order_id'=>$v['id']])->value('add_flow_path');
                //异常订单标示
                $v['abn_order_id'] = $abn_model->where(['order_id'=>$v['id']])->value('id');
            }
        }

        return $data;
    }
    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-06-11 00:40
     * @功能说明:
     */
    public function getCoachOrderId($name){

        $map[] = ['b.coach_name','like','%'.$name.'%'];

        $map[] = ['c.now_coach_name','like','%'.$name.'%'];

        $data = $this->alias('a')
            ->join('massage_service_coach_list b','a.coach_id = b.id','left')
            ->join('massage_order_coach_change_logs c','if(a.is_add=0,a.id,a.add_pid) = c.order_id AND c.is_new = 1','left')
            ->where(function ($query) use ($map){
                $query->whereOr($map);
            })->column('a.id');

        return $data;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-06-11 00:56
     * @功能说明:同伙渠道搜索分类
     */
    public function getOrderIdByChannelCate($channel_cate_id){

        $channel_list = new ChannelList();

        $dis = [

            'cate_id' => $channel_cate_id
        ];

        $id = $channel_list->where($dis)->column('id');

        return $id;

    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-06-11 00:56
     * @功能说明:同伙渠道搜索分类
     */
    public function getOrderIdByChannelName($channel_cate_name){

        $channel_list = new ChannelList();

        $dis[] = ['user_name','like','%'.$channel_cate_name.'%'];

        $id = $channel_list->where($dis)->column('id');

        return $id;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-15 14:37
     * @功能说明:后台列表
     */
    public function adminOrderPrice($dis,$map=[]){

        $data = $this->alias('a')
            ->join('massage_service_coach_list b','a.coach_id = b.id','left')
            ->join('massage_order_coach_change_logs c','if(a.is_add=0,a.id,a.add_pid) = c.order_id AND c.is_new = 1','left')
            ->join('massage_channel_list e','a.channel_id = e.id','left')
            ->where($dis)
            ->where(function ($query) use ($map){
                $query->whereOr($map);
            })
            ->group('a.id')
            ->column('a.id');

        $arr['order_price'] =  $this->where('id','in',$data)->sum('true_service_price');

        $arr['car_price']   =  $this->where('id','in',$data)->sum('true_car_price');

        // $arr['car_price']   =  $this->whereExists('id','in',$data)->sum('true_car_price');

        $arr['order_price'] = round($arr['order_price'],2);

        $arr['car_price']   = round($arr['car_price'],2);

        return $arr;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-15 14:37
     * @功能说明:后台列表
     */
    public function adminOrderPriceV2($dis,$map=[]){

        $arr['car_price'] = $this->where($dis)
            ->where(['free_fare'=>0])
            ->where('pay_time','<>',0)
            ->where(function ($query) use ($map){
                $query->whereOr($map);
            })
            ->sum('car_price');

        $arr['pay_price'] = $this->where($dis)
            ->where('pay_time','<>',0)
            ->where(function ($query) use ($map){
                $query->whereOr($map);
            })
            ->sum('pay_price');

        $arr['service_price'] = $this->where($dis)
            ->where('pay_time','<>',0)
            ->where(function ($query) use ($map){
                $query->whereOr($map);
            })
            ->sum('service_price');

        $arr['material_price'] = $this->where($dis)
            ->where('pay_time','<>',0)
            ->where('start_material_price','>',0)
            ->where(function ($query) use ($map){
                $query->whereOr($map);
            })
            ->sum('start_material_price');

        foreach ($dis as $k=>$v){

            $dis[$k][0] = 'a.'.$v[0];
        }

        if(!empty($map)){

            foreach ($map as $k=>$v){

                $dis[$k][0] = 'a.'.$v[0];
            }
        }

        $list = ['refund_service_price','refund_car_price','refund_material_price','refund_price'];

        foreach ($list as $value){

            $arr[$value] = 0;
        }

        $refund_cash = $this->alias('a')
            ->join('massage_service_refund_order b','a.id = b.order_id')
            ->where($dis)
            ->where('a.pay_time','<>',0)
            ->where('b.status','=',2)
            ->where(function ($query) use ($map){
                $query->whereOr($map);
            })
            ->field('sum(b.refund_material_price) as refund_material_price,sum(refund_service_price) as refund_service_price,sum(refund_car_price) as refund_car_price,sum(refund_price) as refund_price')
            ->find();

        if(!empty($refund_cash)){

            $refund_cash = $refund_cash->toArray();

            foreach ($refund_cash as $k=>$v){

                $arr[$k] = $v;
            }
        }

        foreach ($arr as $k=>$v){

            $arr[$k] = round($v,2);
        }

        return $arr;
    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-15 14:37
     * @功能说明:后台列表
     */
    public function adminDataSelect($dis,$map=[],$phone_encryption=0,$is_channel=0){

        $data = $this->alias('a')
            ->join('massage_service_coach_list b','a.coach_id = b.id')
            ->join('massage_service_order_goods_list c','a.id = c.order_id')
            ->join('massage_service_order_address h','a.id = h.order_id')
            ->join('massage_channel_list e','a.channel_id = e.id','left')
            ->join('shequshop_school_admin f','a.admin_id = f.id','left')
            ->join('massage_channel_qr g','a.channel_qr_id = g.id','left')
            ->join('massage_order_coach_change_logs d','(a.add_pid = d.order_id||a.id = d.order_id) AND d.is_new = 1','left')
            ->where($dis)
            ->where(function ($query) use ($map){
                $query->whereOr($map);
            })
            ->field('a.coach_id,a.store_id,a.start_time,a.create_time,a.pay_model,a.balance,a.pay_type,a.is_add,a.car_price,a.init_service_price,a.pay_price,a.order_code,a.transaction_id,a.id as order_id,b.coach_name,h.user_name,h.mobile,c.goods_name,g.title as channel_qr_name,a.channel_qr_id,f.agent_name as admin_name,a.is_add,b.coach_name,c.goods_id,c.goods_name,c.price,c.num,e.user_name as channel_name,e.cate_id as channel_cate,c.init_material_price as init_material_prices')
            ->group('a.id,c.goods_id')
            ->order('a.id desc')
            ->select()
            ->toArray();

        if(!empty($data)){

            $channel_cate= new ChannelCate();

            $refund_model= new RefundOrder();

            foreach ($data as &$v){

                $v['id'] = $v['order_id'];

                $v['init_material_price'] = $v['init_material_prices'];

                if($is_channel==1){

                    $v['channel'] = $channel_cate->where(['id'=>$v['channel_cate']])->value('title');
                }
                //代理商如果没有权限需要隐藏用户的手机号
                if($phone_encryption==0){

                    $v['mobile'] = substr_replace($v['mobile'], "****", 2,4);
                }

                $v['refund_price'] = $refund_model->where(['order_id'=>$v['id'],'status'=>2])->sum('refund_price');
            }
        }

        return $data;
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
     * @DataTime: 2021-03-18 14:33
     * @功能说明:
     */
    public function datePrice($date,$uniacid,$cap_id=0,$end_time = '',$type=1){

        $end_time = !empty($end_time)?$end_time:$date+86399;

        $dis = [];

        $dis[] = ['transaction_id','<>',''];

        $dis[] = ['auto_refund','=',0];

        $dis[] = ['create_time','between',"$date,$end_time"];

        $dis[] = ['uniacid',"=",$uniacid];

        if(!empty($cap_id)){

            $dis[] = ['cap_id','=',$cap_id];
        }

        if($type==1){

            $price = $this->where($dis)->sum('pay_price');

            return round($price,2);

        }else{

            $count = $this->where($dis)->count();

            return $count;

        }
    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-19 09:28
     * @功能说明:计算用户下单时候的各类金额
     */
    public function payOrderInfo($user_id,$cap_id,$lat=0,$lng=0,$car_type=0,$coupon=0,$order_id=0,$service_type=1,$order_start_time=0,$input=[]){

        $data = getConfigSettingArr(666,['recharge_status']);

        $is_remember = isset($input['is_remember'])?$input['is_remember']:1;

        $pay_model   = isset($input['pay_model'])?$input['pay_model']:4;

        if($coupon>0){

            $is_remember = 0;
        }

        $car_model   = new Car();

        $coupon_model= new Coupon();

        $coach_model = new Coach();

        $car_config_model = new CarPrice();

        $admin_config_model = new AdminConfig();

        $order_server = new \app\massage\server\Order();

        $coach = $coach_model->dataInfo(['id'=>$cap_id]);
        //获取购物车里面的信息
        $car_list = $car_model->carPriceAndCount($user_id,$cap_id,0,$order_id,$service_type);

        if($is_remember==0){
            //卡券折扣
            $car_list = $coupon_model->orderCouponData($car_list,$coupon);
        }

        $goods_price = $car_list['car_price'];

        $data['coupon_id'] = $car_list['coupon_id'];
        //原来的物料费
        $data['init_material_price'] = $car_list['all_material_price'];
        //折扣后的物料费
        $data['material_price'] = round($data['init_material_price']-$car_list['total_material_discount'],2);
        //购物车列表
        $data['order_goods'] = $car_list['list'];
        //优惠券优惠
        $data['discount']    = $car_list['total_discount'];
        //商品总价格
        $data['init_goods_price'] = round($goods_price,2);

        $data['goods_price'] = round($goods_price-$car_list['total_goods_discount'],2);

        $data['free_fare']   = 0;
        //会员折扣
        $data = $order_server->memberDiscountData($data,$user_id,$is_remember);

        if($coupon>0||!empty($data['is_remember'])){

            $pay_model = 0;
        }

        if(!empty($cap_id)&&$service_type==1){

            $data['car_config'] = $car_config_model->getCityConfig($coach['uniacid'],$coach['city_id'],$order_start_time);

            if($lat==0){

                $data['car_price'] = 0;

                $data['distance'] = 0;

            }else{

                $data['distance']  = getDriveDistance($coach['lng'],$coach['lat'],$lng,$lat,$coach['uniacid']);

                $data['distance'] += $data['car_config']['invented_distance']*$data['distance']/100;
                //车费
                $data['car_price'] = $this->getCarPrice($data['distance'],$data['car_config'],$car_type);

                if(!empty($coach['admin_id'])){

                    $admin_config = $admin_config_model->dataInfo(['admin_id'=>$coach['admin_id'],'uniacid'=>$coach['uniacid']]);
                    //等于3是为了兼容数据
                    $free_fare = $admin_config['free_fare_bear']!=1?$admin_config['free_fare_bear']:3;
                }else{

                    $free_fare = getConfigSetting($coach['uniacid'],'free_fare_bear');
                }
                //免车费
                if(!empty($coach['free_fare_distance'])&&$coach['free_fare_distance']>0&&$free_fare>0){
                    //可以免车费
                    if($coach['free_fare_distance']*1000>=$data['distance']){
                        //谁来承担这个车费
                        $data['free_fare'] = $free_fare;
                    }
                }
            }
        }else{

            $data['car_price'] = 0;

            $data['distance'] = 0;
        }

        $data['coach_info']= $coach;

        $car_price = $data['free_fare']==0?$data['car_price']:0;

        $balance_discount_card_arr = !empty($input['balance_discount_card_arr'])?explode(',',$input['balance_discount_card_arr']):[];
        //储值折扣
        $data = $order_server->balanceDiscountData($data,$user_id,$data['goods_price']+$data['material_price'],$car_price,$pay_model,666,$balance_discount_card_arr);

        $data['pay_price'] = round($data['goods_price']+$car_price+$data['material_price'],2);

        $data['coach_id']  = $cap_id;
        //到店服务需要返回一个就近的门店
        if($service_type==2){

            if(empty($input['store_id'])){

                $data['store_info'] = StoreCoach::getNearStore($cap_id,$coach['admin_id'],$lng,$lat);
            }else{

                $store_model = new StoreList();

                $data['store_info'] = $store_model->dataInfo(['id'=>$input['store_id']]);
            }
            //技师关联门店数量
            $data['store_num'] = StoreCoach::getStoreCount($cap_id);
        }
        //线下技师没有门店id
        $data['store_id'] = !empty($data['store_info'])?$data['store_info']['id']:0;

        $service_model = new Service();
        //获取服务的会员信息
        $data['order_goods'] = $service_model->giveListMemberInfo($data['order_goods'],666,$user_id,2);

        return $data;
    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-06-19 18:21
     * @功能说明:看支持上门还是到店
     */
    public function storeOrDoor($user_id,$cap_id,$uniacid){

        $car_model   = new Car();
        //获取购物车里面的信息
        $car_list = $car_model->carList($user_id,$cap_id,0,0,0);

        $is_store = array_column($car_list,'is_store');

        $data['is_store'] = in_array(1,$is_store)?1:0;

        $is_door = array_column($car_list,'is_door');

        $data['is_door'] = in_array(1,$is_door)?1:0;

        if($data['is_store']==1){

            $store_count = StoreCoach::getStoreCount($cap_id);

            if($store_count==0){

                $data['is_store'] =0;
            }
        }

        if($data['is_store']==1){

            $auth = AdminMenu::getAuthList((int)$uniacid,['store']);

            if(empty($auth)||$auth['store']==false){

                $data['is_store'] = 0;
            }
        }

        return $data;
    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-16 10:45
     * @功能说明:代理商的分销比例
     */
    public function agentCashData($admin,$order){
        //县级代理商
        if(!empty($admin)&&!empty($admin['admin_pid'])&&in_array($admin['city_type'],[1,2])){

            $order['level_balance'] = $admin['level_balance'];

            $order['admin_pid']     = $admin['admin_pid'];
            //查看是否还有上级hx
            $admin_pdata = $this->dataInfo(['id'=>$order['admin_pid']]);
            //只有市才有上级
            if(!empty($admin_pdata)&&$admin_pdata['city_type']==1){

                $order['p_level_balance'] = $admin_pdata['level_balance'];

                $order['p_admin_pid']     = $admin_pdata['admin_pid'];
            }
        }

        return $order;

    }


    /**
     * @param $order
     * @param int $type
     * @param int $admin_id
     * @功能说明:获取佣金数据
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-30 15:17
     */
    public function getCashData($order,$type=1,$admin_id=0){

        //  $value = getConfigSetting($order['uniacid'],'commission_custom');

        //  if($value==0){
        //常规
        $arr_data = $this->getCashDataCommon($order,$type,$admin_id);
//        }else{
//            //自定义
//            $arr_data = $this->getCashDataCustom($order,$type,$admin_id);
//
//        }

        return $arr_data;

    }


    /**
     * @param $order
     * @功能说明:获取佣金数据(常规)
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-06-13 13:42
     */
    public function getCashDataCommon($order,$type=1,$admin_id=0){

        $comm_model = new Commission();
        //下单
        if($type==1){
            //计算比例
            $order = $comm_model->balanceData($order,$admin_id);

            if(!empty($order['code'])&&$order['code']==300){

                return $order;
            }
        }
        //计算佣金
        $order = $comm_model->cashData($order,$type);
        //转单时候线下技师需要把佣金给到加盟商
        if(empty($order['coach_id'])&&!empty($order['admin_id'])){

            $order['admin_cash'] = $order['admin_cash']+$order['coach_cash']+$order['true_car_price'];
        }
        //增加分销记录
        if($type==1){
            //车费记录
            $comm_model->carCommission($order);
            //技师佣金记录
            $comm_model->coachCommission($order);
            //用户分销
            $comm_model->commissionAddDataV2($order);
            //用户二级分销
            $comm_model->commissionLevelAddData($order);
            //加盟商佣金记录
            $comm_model->adminCommission($order);
            //有二级
            $comm_model->adminLevelCommission($order);
            //省代
            $comm_model->adminProvinceCommission($order);
            //技师合伙人
            $comm_model->partnerCommission($order);
            //渠道商
            $comm_model->channelCommission($order);
            //业务员
            $comm_model->salesmanCommission($order);
            //平台
            $comm_model->companyCommission($order);
            //代理商承担车费
            $comm_model->adminCarCommission($order);
        }
        //用户升级的时候
        if($type==3){

            $comm_id = $comm_model->where(['order_id'=>$order['id']])->where('type','in',[1,14])->column('id');

            $comm_model->where(['order_id'=>$order['id']])->where('type','in',[1,14])->delete();

            $share_model = new CommShare();

            $share_model->where(['order_id'=>$order['id']])->where('comm_id','in',$comm_id)->delete();
            //用户分销
            $comm_model->commissionAddDataV2($order);
            //用户二级分销
            $comm_model->commissionLevelAddData($order);

            $comm_model->where(['order_id'=>$order['id'],'status'=>-1])->where('type','in',[1,14])->update(['status'=>1]);
        }

        $arr = ['free_fare','coach_balance','admin_balance','admin_id','user_cash','company_cash','coach_cash','admin_cash','partner_id','salesman_id','true_car_price','broker_id','channel_id','channel_qr_id'];

        foreach ($arr as $value){

            if(key_exists($value,$order)){

                $list[$value] = $order[$value];
            }
        }

        $arr_data['order_data'] = $list;

        $arr_data['data'] = $order;

        return $arr_data;

    }



    /**
     * @param $order
     * @功能说明:获取佣金数据(自定义)
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-06-13 13:42
     */
    public function getCashDataCustom($order,$type=1,$admin_id=0){

        $comm_model = new CommissionCustom();
        //下单
        if($type==1){
            //计算比例
            $order = $comm_model->balanceDataCustom($order,$admin_id);

            if(!empty($order['code'])&&$order['code']==300){

                return $order;
            }
        }
        //计算佣金
        $order = $comm_model->cashDataCustom($order);
        //转单时候线下技师需要把佣金给到加盟商
        if(empty($order['coach_id'])&&!empty($order['admin_id'])){

            $order['admin_cash'] = $order['admin_cash']+$order['coach_cash']+$order['true_car_price'];

        }
        //增加分销记录
        if($type==1){
            //车费记录
            $comm_model->carCommission($order);
            //技师佣金记录
            $comm_model->coachCommission($order);
            //用户分销
            $comm_model->commissionAddDataV3($order);
            //有二级
            $comm_model->adminLevelCommission($order);
            //加盟商佣金记录
            $comm_model->adminCommission($order);
            //渠道商
            $comm_model->channelCommission($order);
            //平台
            $comm_model->companyCommission($order);
        }

        $arr = ['coach_balance','admin_balance','admin_id','user_cash','company_cash','coach_cash','admin_cash','partner_id'];

        foreach ($arr as $value){

            if(key_exists($value,$order)){

                $list[$value] = $order[$value];
            }
        }

        $arr_data['order_data'] = $list;

        $arr_data['data'] = $order;

        return $arr_data;
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-09 17:43
     * @功能说明:计算车费
     */
    public function getCarPrice($distance,$config,$car_type=1){

        if($car_type==0){

            return 0;
        }

        if(!empty($config['day_type'])&&$config['day_type']==2){

            $config['distance_free'] = $config['distance_free_night'];

            $config['start_distance']= $config['start_distance_night'];

            $config['start_price']   = $config['start_price_night'];

            $config['distance_price']= $config['distance_price_night'];

            $config['cash_type']     = $config['cash_type_night'];

            $config['cash_setting_day'] = $config['cash_setting_night'];
        }
        //起步距离
        $start = $config['start_distance'];
        //起步价
        $start_price = $config['start_price'];
        //每公里多少钱
        $to_price = $config['distance_price'];

        $distance = $distance/1000;
        //超过起步距离
        if($distance>$start){
            //阶梯计算车费
            if($config['cash_type']==2&&!empty($config['cash_setting_day'])){

                $to_price = $this->stepCarPrice($start,$config,$distance);

            }else{

                $to_price = round($distance - $start,2)*$to_price;
            }

            $total = $start_price+$to_price;

            return round($total*2,2);

        }else{

            return round($start_price*2,2);
        }
    }


    /**
     * @param $start
     * @param $config
     * @param $distance
     * @功能说明:阶梯计算车费
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-01-23 17:23
     */
    public function stepCarPrice($start,$config,$distance){

        $to_price = 0;

        $have_km  = $start;
        //阶梯计算车费
        foreach ($config['cash_setting_day'] as $k=>$v){

            if($distance<=$have_km){

                return $to_price;
            }

            $true_km = $distance<$v['km']||count($config['cash_setting_day'])==$k+1?$distance:$v['km'];

            $km   = round($true_km - $have_km,2);

            $cash = $v['cash']*$km;

            $to_price+=$cash;

            $have_km = $true_km;
        }

        return $to_price;
    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-22 11:31
     * @功能说明:订单支付回调
     */
    public function orderResult($order_code,$transaction_id){

        $order = $this->dataInfo(['order_code'=>$order_code,'transaction_id'=>'']);

        if(!empty($order)&&!empty($transaction_id)){

            Db::startTrans();

            $update = [

                'transaction_id' => $transaction_id,

                'pay_type'       => 2,

                'pay_time'       => time(),

            ];

            $res = $this->dataUpdate(['id'=>$order['id'],'transaction_id'=>''],$update);

            if($res==0){

                Db::rollback();

                return false;

            }
            //扣除余额
            if($order['balance']>0){

                $water_model = new BalanceWater();

                $res = $water_model->updateUserBalance($order,2);

                if($res==0){

                    Db::rollback();

                    return false;
                }
            }
            //储值卡支付
            if($order['pay_model']==4){

                $discount_card_model = new UserCard();

                $res = $discount_card_model->updateCardCash($order['id'],$order['user_id'],1);

                if($res==0){

                    Db::rollback();

                    return false;
                }
            }

            $order['transaction_id'] = $transaction_id;
            //分销
            $comm_model = new Commission();
            //将分销记录打开
            $comm_model->dataUpdate(['order_id'=>$order['id'],'status'=>-1],['status'=>1]);

            $order_model = new Order();

            $company_cash = $order_model->companySurplusCash($order['id']);

            $comm_model->dataUpdate(['type'=>16,'order_id'=>$order['id']],['company_cash'=>$company_cash]);

            $order_price_log = new OrderPrice();
            //增加订单金额日志
            $order_price_log->logAdd($order,$order['id'],1,$order['pay_model']);

            Db::commit();

            $log_model = new OrderLog();
            //增加订单操作日志
            $log_model->addLog($order['id'],$order['uniacid'],2,1,3,$order['user_id']);

            $coach_model = new Coach();
            //平台订单需要将状态改为拒单
            $coach_model->companyOrderResult($order);

            $notice_model = new NoticeList();
            //增加后台提醒
            $notice_model->dataAdd($order['uniacid'],$order['id'],1,$order['admin_id']);
            //发送通知
            $coach_model->paySendMsg($order);

            if($order['is_add']==0){
                //语音电话通知
                $call_model = new \app\reminder\model\Config();

                $call_model->sendCalled($order);
            }
            //打印
            $print_model = new Printer();

            $print_model->printer($order['id'],0);
        }

        return true;

    }





    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-22 15:31
     * @功能说明:团长冻结资金
     */
    public function capFrozenPrice($cap_id,$total=0,$toDay=0){

        $dis[] = ['cap_id','=',$cap_id];

        if($total==0){

            $dis[] = ['have_tx','=',0];
        }

        $dis[] = ['pay_type','>',1];

        if($toDay==1){
            //当日
            $price = $this->where($dis)->whereDay('create_time')->sum('cap_price');

        }else{

            $price = $this->where($dis)->sum('cap_price');

        }

        return round($price,2);

    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-22 15:31
     * @功能说明:团长冻结资金
     */
    public function capFrozenCount($cap_id,$total=0,$toDay=0){

        $dis[] = ['cap_id','=',$cap_id];

        if($total==0){

            $dis[] = ['have_tx','=',0];
        }

        $dis[] = ['pay_type','>',1];

        if($toDay==1){
            //当日
            $price = $this->where($dis)->whereDay('create_time')->count();

        }else{

            $price = $this->where($dis)->count();
        }

        return $price;
    }








    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-26 15:15
     * @功能说明:核销订单
     */
    public function hxOrder($order,$cap_id=0){

        $time = time();

        $update = [

            'order_end_time' => $time,

            'pay_type'       => 7,

            'hx_user'        => $cap_id,
            //可申请退款的时间
            'can_tx_date'    => $time
        ];

        Db::startTrans();

        $res = $this->dataUpdate(['id'=>$order['id']],$update);
        //解除虚拟电话绑定
        $called = new \app\virtual\model\Config();

        $called->delBindVirtualPhone($order);
        //资金到账
        $res = $this->coachBalanceArr($order['uniacid'],$order['id']);

        if(!empty($res['code'])){

            Db::rollback();

            return $res;
        }

        Db::commit();

        $level_model = new Level();
        //如果有会员插件 需要加成长值
        $level_model->levelUp($order);
        //增加技师销量
        $coach_model = new Coach();

        $coach_info = $coach_model->dataInfo(['id'=>$order['coach_id']]);

        if(!empty($coach_info)){

            $coach_model->where(['id'=>$coach_info['id']])->update(['total_order_num'=>Db::Raw('total_order_num+1')]);
        }
        //增加技师信用分
        $credit_model = new CreditConfig();

        if($order['is_add']==0){

            $find = $this->where(['coach_id'=>$order['coach_id'],'user_id'=>$order['user_id'],'pay_type'=>7])->where('id','<>',$order['id'])->find();

            if(!empty($find)){

                $type = 4;
            }else{

                $type = 1;
            }

        }else{

            $type = 2;
        }
        //金额
        $credit_model->creditRecordAdd($order['coach_id'],$type,$order['uniacid'],$order['id'],$order['true_service_price'],$order['create_time']);
        //服务时长
        $credit_model->creditRecordAdd($order['coach_id'],3,$order['uniacid'],$order['id'],$order['true_time_long'],$order['create_time']);

        return $res;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-04-01 10:13
     * @功能说明:超时自动退款
     */
    public function autoCancelOrder($uniacid,$user_id=0){

        $log_model = new OrderLog();

        $dis[] = ['uniacid','=',$uniacid];

        $dis[] = ['pay_type','=',1];

        $dis[] = ['over_time','<',time()];

        if(!empty($user_id)){

            $dis[] = ['user_id','=',$user_id];
        }

        $time_key = 'order_key_massage_time';

        if(!empty(getCache($time_key,$uniacid))){

            return false;
        }

        setCache($time_key,1,10,$uniacid);

        $key = 'order_key_massage';

        incCache($key,1,$uniacid,60);

        $key_value = getCache($key,$uniacid);

        if($key_value==1){

            $arr = [new Order(),new UpOrderList()];

            foreach ($arr as $k=>$values){

                $order = $values->where($dis)->limit(5)->select()->toArray();

                if(!empty($order)){

                    foreach ($order as $value){

                        $this->cancelOrder($value,$k);

                        $log_model->addLog($value['id'],$value['uniacid'],-1,$value['pay_type'],1);
                    }
                }
            }
        }

        decCache($key,1,$uniacid);

        $order = $this->where(['pay_type'=>7])->whereColumn('can_tx_date','<>','order_end_time')->field('id,order_end_time,can_tx_date')->limit(50)->select()->toArray();

        if(!empty($order)){

            foreach ($order as $value){

                $this->dataUpdate(['id'=>$value['id']],['can_tx_date'=>$value['order_end_time']]);
            }
        }

        return true;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-04-01 10:13
     * @功能说明:退款
     */
    public function cancelOrder($order,$is_up=0,$is_cancel=0){

        Db::startTrans();

        $old_paytype = !empty($is_cancel)?$is_cancel:$order['pay_type'];

        if($is_up==0){

            $res = $this->where(['id'=>$order['id']])->where('pay_type','=',$old_paytype)->update(['pay_type'=>-1]);

        }else{

            $order_model = new UpOrderList();

            $res = $order_model->where(['id'=>$order['id']])->where('pay_type','=',$old_paytype)->update(['pay_type'=>-1]);
        }

        if($res!=1){

            Db::rollback();

            return ['code'=>500,'msg'=>'取消失败'];
        }

        $goods_model = new Service();
        //退换库存
        foreach ($order['order_goods'] as $v){

            $res = $goods_model->setOrDelStock($v['goods_id'],$v['num'],1);

            if(!empty($res['code'])){

                Db::rollback();

                return $res;
            }
        }

        if($is_up==0&&($order['pay_type']<3||$order['pay_type']==8)){
            //退换优惠券
            $coupon_model = new CouponRecord();

            $coupon_model->couponRefund($order['id']);
        }

        if($is_up==0){
            //删除佣金
            $comm_model = new Commission();

            $comm_model->dataUpdate(['order_id' => $order['id']], ['status' => -1]);

            $company_cash = $this->companySurplusCash($order['id']);

            $comm_model->dataUpdate(['type'=>16,'order_id'=>$order['id']],['company_cash'=>$company_cash]);

            $res = $comm_model->refundCompanySuccess($order['id']);

            if($res==0){

                Db::rollback();

                return ['code'=>500,'msg'=>'取消失败'];
            }
            //如果是加钟订单后面的加钟订单时间要往前移(但是目前只能一单一单加，这个接口暂时没用)
            $this->updateAddOrderTime($order,$order['time_long']*60);
        }

        Db::commit();

        return true;
    }


    /**
     * @param $coach
     * @param $start_time
     * @param $end_time
     * @功能说明:校验技师时间
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-02 14:48
     */
    public function checkCoachTime($coach,$start_time,$end_time){

        $all_day = 1;
        //判断不是全体24小时上班
        if($coach['start_time']!=$coach['end_time']){

            $all_day = 0;

        }
        if(strtotime($coach['end_time'])-strtotime($coach['start_time'])==86400){

            $all_day = 1;
        }
        //全天不判断
        if($all_day==0){
            //教练上班时间
            $coach_start_time = strtotime($coach['start_time'])-strtotime(date('Y-m-d',time()))+strtotime(date('Y-m-d',$start_time));
            //教练下班时间
            $coach_end_time   = strtotime($coach['end_time'])-strtotime(date('Y-m-d',time()))+strtotime(date('Y-m-d',$start_time));

            if($end_time<$coach_start_time){

                $coach_start_time -= 86400;

                $coach_end_time   -= 86400;
            }

            $coach_end_time = $coach_end_time>$coach_start_time?$coach_end_time:$coach_end_time+86400;

            if($start_time<$coach_start_time||$end_time>$coach_end_time){

                return ['code'=>500,'msg'=>'不在服务时间内,服务时间:'.$coach['start_time'].'-'.$coach['end_time']];
            }
        }

        return true;

    }


    /**
     * @param $order_list
     * @param $config
     * @param $start_time
     * @param $end_time
     * @功能说明:校验订单时间
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-02 14:53
     */
    public function checkOrderTime($order_list,$config,$start_time,$end_time,$coach_id=0){

        $order_model = new Order();

        if(!empty($order_list)){

            foreach ($order_list as $value){

                if(!empty($coach_id)&&$coach_id!=$value['coach_id']){

                    continue ;
                }

                //  $order_end_time = $order_model->where(['add_pid'=>$value['id']])->where('pay_type','not in',[-1,7])->max('end_time');

                $value['end_time'] = !empty($order_end_time)&&$order_end_time>$value['end_time']?$order_end_time:$value['end_time'];

                $time_interval = $config['time_interval']>0?$config['time_interval']*60-1:0;
                //判断两个时间段是否有交集(不允许时间相同)
                $res = is_time_crossV2($start_time,$end_time,$value['start_time']-$time_interval,$value['end_time']+$time_interval);

                if($res==false){

                    return ['code'=>500,'msg'=>'该时间段已经被预约:'.date('Y-m-d H:i',$value['start_time']).'-'.date('Y-m-d H:i',$value['end_time']).'-'.$value['id']];

                }

            }

        }

        return true;

    }


    /**
     * @param $rest_arr
     * @param $start_time
     * @param $end_time
     * @功能说明:校验技师休息时间
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-02 15:09
     */
    public function checkCoachRestTime($rest_arr,$start_time,$end_time){

        if(!empty($rest_arr)){

            foreach ($rest_arr as $values){

                $res = is_time_cross($start_time,$end_time,$values['time_str'],$values['time_str_end']);

                if($res==false&&$values['is_click']==1){

                    return ['code'=>500,'msg'=>'该时间段正在休息'];

                }
            }
        }

        return true;
    }





    /**
     * @param $order
     * @param $start_time
     * @param int $order_id 加钟订单
     * @param int $p_order_id 升级订单
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-02 11:56
     */
    public function checkTime($order,$start_time,$order_id=0,$p_order_id=0,$store_id=0){

        $service_model = new Service();

        $coach_model   = new Coach();

        $order_model   = new Order();

        $total_long = 0;

        $config_model = new Config();

        if(empty($order['order_goods'])){

            return ['code'=>500,'msg'=>'请选择服务项目'];
        }

        foreach ($order['order_goods'] as $v){
            //如果退款了就不算
            if(isset($v['can_refund_num'])&&$v['can_refund_num']==0){

                $time_long = 0;

            }else{

                $time_long = $service_model->where(['id'=>$v['service_id']])->value('time_long');
            }

            $total_long += $time_long*$v['num'];
        }

        $end_time = $start_time+$total_long*60;

        if(!empty($order['coach_id'])){

            $coach  = $coach_model->dataInfo(['id'=>$order['coach_id']]);

            $config = $config_model->dataInfo(['uniacid' => $coach['uniacid']]);
            //校验技师时间
            $res = $this->checkCoachTime($coach,$start_time,$end_time);

            if(!empty($res['code'])){

                return $res;
            }
            //检查该时间段是否被预约
            $where[] = ['coach_id','=',$order['coach_id']];

            $where[] = ['pay_type','not in',[-1,7]];

            $where[] = ['end_time','>',time()];

            if(!empty($p_order_id)){
                //升级订单的时候
                $where[] = ['id','<>',$p_order_id];

                $where[] = ['add_pid','<>',$p_order_id];
            }
            //加单的时候
            if(!empty($order_id)){

                $where[] = ['add_pid','<>',$order_id];

                $where[] = ['id','<>',$order_id];
            }

            $refund_model = new RefundOrder();

            $refund_ing_order = $refund_model->where('status','in',[4,5])->where(['refund_end'=>1])->column('order_id');

            if(!empty($refund_ing_order)){

                $where[] = ['id', 'not in', $refund_ing_order];
            }

            $order_list = $order_model->where($where)->field('id,start_time,end_time,order_end_time,pay_type,coach_id')->select()->toArray();
            //校验技师时间
            $res = $this->checkOrderTime($order_list,$config,$start_time,$end_time);

            if(!empty($res['code'])){

                return $res;
            }
            //校验技师休息时间
            $rest_arr = $coach_model->getCoachRestTime($coach,$start_time,$end_time,$config);

            $res = $this->checkCoachRestTime($rest_arr,$start_time,$end_time);

            if(!empty($res['code'])){

                return $res;
            }

        }
        //店铺订单 校验时间
        if(!empty($store_id)){

            $store_model = new \app\store\model\StoreList();

            $res = $store_model->checkStoreStatus($store_id,$start_time,$end_time);

            if(!empty($res['code'])){

                return $res;
            }
        }

        $arr = [

            'end_time'  => $end_time,

            'time_long' => $total_long,

        ];

        return $arr;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-11 22:39
     * @功能说明:技师修改订单信息
     */
    public function coachOrdertext($input,$is_admin=0){

        $update['pay_type'] = $input['type'];

        switch ($input['type']){

            case 3:

                $update['receiving_time'] = time();

                break;
            case 4:

                $update['serout_time'] = time();

                $update['serout_lng']    = !empty($input['serout_lng'])?$input['serout_lng']:0;

                $update['serout_lat']    = !empty($input['serout_lat'])?$input['serout_lat']:0;

                $update['serout_address']= !empty($input['serout_address'])?$input['serout_address']:'';

                break;
            case 5:

                $update['arrive_time'] = time();

                $update['arrive_img'] = !empty($input['arrive_img'])?$input['arrive_img']:'';

                $update['arr_lng']    = !empty($input['arr_lng'])?$input['arr_lng']:0;

                $update['arr_lat']    = !empty($input['arr_lat'])?$input['arr_lat']:0;

                $update['arr_address']= !empty($input['arr_address'])?$input['arr_address']:'';

                break;
            case 6:

                $update['start_service_time'] = time();

                break;

            case 7:

                $update['order_end_time'] = time();

                $update['end_lng']    = !empty($input['end_lng'])?$input['end_lng']:0;

                $update['end_lat']    = !empty($input['end_lat'])?$input['end_lat']:0;

                $update['end_address']= !empty($input['end_address'])?$input['end_address']:'';

                $update['end_img']    = !empty($input['end_img'])?$input['end_img']:'';

                break;
            case -1:

                $update['coach_refund_time'] = time();

                $update['version'] = 2;

                $update['coach_refund_text'] = !empty($input['coach_refund_text'])?$input['coach_refund_text']:'';

                break;

        }

        return $update;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-11 23:18
     * @功能说明:技师佣金到账
     *
     */
    public function coachBalanceArr($uniacid,$order_id=0){

        $key = 'key_coach_arr_aaaann11aa'.$order_id;

        incCache($key,1,$uniacid,30);

        $key_value = getCache($key,$uniacid);

        if($key_value==1){

            $dis[] = ['uniacid','=',$uniacid];

            $dis[] = ['pay_type','=',7];

            $dis[] = ['have_tx','=',0];

            if(!empty($order_id)){

                $dis[] = ['id','=',$order_id];

            }
            $order = $this->where($dis)->field('admin_id,admin_cash,id as order_id,coach_id,service_price,car_price,true_service_price,true_car_price,admin_cash,coach_cash')->select()->toArray();

            if(!empty($order)){

                $refund_model= new RefundOrder();

                $comm_model  = new Commission();

                foreach ($order as $value){

                    $value['id'] = $value['order_id'];

                    $refund_order = $refund_model->dataInfo(['order_id'=>$value['id'],'status'=>1]);

                    if(empty($refund_order)){

                        Db::startTrans();

                        try{
                            //修改订单状态
                            $res = $this->where(['id'=>$value['id'],'have_tx'=>0])->update(['have_tx'=>1]);
                            //增加团长佣金
                            if($res!=0){
                                //各类佣金
                                $res = $comm_model->commissionSucessCash($value['id']);

                                if($res==0){

                                    Db::rollback();

                                    if(!empty($order_id)){

                                        decCache($key,1,$uniacid);

                                        return ['code'=>500,'msg'=>'网络不佳,核销失败,请重试'];
                                    }
                                    continue;
                                }
                            }

                        }catch (Exception $e) {

                            Db::rollback();
                        }

                        Db::commit();
                    }
                }
            }
        }

        decCache($key,1,$uniacid);

        return true;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-08-30 14:12
     * @功能说明:获取渠道商订单信息
     */
    public function channelData($channel_id,$input=[]){

        $dis[] = ['a.channel_id','=',$channel_id];

        $dis[] = ['a.pay_type','>',1];

        if(!empty($input['start_time'])&&!empty($input['end_time'])){

            $dis[] = ['a.create_time','between',"{$input['start_time']},{$input['end_time']}"];

        }

        if(isset($input['qr_id'])){

            $dis[] = ['a.channel_qr_id','=',$input['qr_id']];
        }

        $where = [];

        if(!empty($input['name'])){

            $where[] = ['b.goods_name','like','%'.$input['name'].'%'];

            $where[] = ['a.order_code','like','%'.$input['name'].'%'];

        }

        $id = $this->alias('a')
            ->join('massage_service_order_goods_list b','a.id = b.order_id')
            ->where($dis)
            ->where(function ($query) use ($where){
                $query->whereOr($where);
            })
            ->group('a.id')
            ->column('a.id');

        $service_price = $this->where('id','in',$id)->sum('true_service_price');

        $data['order_price'] = round($service_price,2);

        $data['order_count'] = count($id);

        $comm_model = new Commission();

        $data['all_cash'] = $comm_model->where('order_id','in',$id)->where('status','>',-1)->where(['type'=>10])->sum('cash');

        $data['all_cash'] = round($data['all_cash'],2);

        return $data;
    }

    /**
     * 获取订单数量
     * @param $where
     * @return int
     */
    public function getOrderNum($where)
    {
        return $this->where($where)->count();
    }


    /**
     * 车费列表
     * @param $dis
     * @param $page
     * @return \think\Paginator
     * @throws \think\db\exception\DbException
     */
    public function carMoneyList($dis,$page=10)
    {
        $data =  $this->where($dis)
            ->field('id,coach_id,serout_time,arrive_time,car_price,order_code,trip_start_address,trip_end_address')
            ->group('id')
            ->order('id desc')
            ->paginate($page)
            ->each(function($item){
                $item['serout_time'] = date('Y-m-d H:i:s',$item['serout_time']);
                $item['arrive_time'] = !empty($item['arrive_time'])?date('Y-m-d H:i:s',$item['arrive_time']):'';
                return $item;
            })
            ->toArray();

        $address_model = new OrderAddress();

        $coach_model = new Coach();

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){
                //兼容老数据
                if(empty($v['trip_end_address'])){

                    $address = $address_model->dataInfo(['order_id'=>$v['id']]);

                    $v['trip_end_address'] = !empty($address)?$address['address'].' '.$address['address_info']:'';
                }

                if(empty($v['trip_start_address'])){

                    $v['trip_start_address'] = $coach_model->where(['id'=>$v['coach_id']])->value('address');
                }

            }

        }

        return $data;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-06-09 11:03
     * @功能说明:车费列表
     *
     */
    public function carMoneyListV2($dis,$page=10){

        $comm_model = new Commission();
        $data = $comm_model->alias('a')
            ->join('massage_service_order_list b','a.order_id = b.id','right')
            ->where($dis)
            ->field('a.order_id as id,b.coach_id,b.serout_time,b.arrive_time,a.cash as car_price,b.order_code,b.trip_start_address,b.trip_end_address')
            ->group('b.id')
            ->order('b.id desc')
            ->paginate($page)
            ->toArray();

        $address_model = new OrderAddress();

        $coach_model = new Coach();

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $v['serout_time'] = date('Y-m-d H:i:s',$v['serout_time']);
                $v['arrive_time'] = !empty($v['arrive_time'])?date('Y-m-d H:i:s',$v['arrive_time']):'';
                //兼容老数据
                if(empty($v['trip_end_address'])){

                    $address = $address_model->dataInfo(['order_id'=>$v['id']]);

                    $v['trip_end_address'] = !empty($address)?$address['address'].' '.$address['address_info']:'';
                }

                if(empty($v['trip_start_address'])){

                    $v['trip_start_address'] = $coach_model->where(['id'=>$v['coach_id']])->value('address');
                }

            }

        }

        return $data;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-10-26 11:16
     * @功能说明:获取加钟的开始时间结束时间
     */
    public function addOrderTime($order_id){
        //查询是否已经有加钟
        $end_time = $this->where(['add_pid'=>$order_id])->where('pay_type','>',0)->field('id,start_time,end_time')->max('end_time');

        if(empty($end_time)){

            $end_time = $this->where(['id'=>$order_id])->value('end_time');

        }

        return $end_time;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-17 11:46
     * @功能说明:获取加钟的次数
     */
    public function addOrderTimes($order_id){

        $times = $this->where(['add_pid'=>$order_id])->where('pay_type','>',0)->count();

        return !empty($times)?$times:1;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-10-26 13:40
     * @功能说明:获取订单服务结束时间
     */
    public function getOrderEndTime($order_goods,$start_time){

        $total_long = 0;

        $service_model = new Service();

        foreach ($order_goods as $v){

            $time_long = $service_model->where(['id'=>$v['service_id']])->value('time_long');

            $total_long+=$time_long*$v['num'];

        }

        $end_time = $start_time+$total_long*60;

        return $end_time;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-10-27 10:52
     * @功能说明:订单是否能加钟
     */
    public function orderCanAdd($order){

        //return in_array($order['pay_type'],[6])&&$order['is_add']==0&&!empty($order['address_info']['address_id'])?1:0;
        return in_array($order['pay_type'],[6])&&$order['is_add']==0&&!empty($order['address_info'])?1:0;
    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-17 14:20
     * @功能说明:如果是加钟订单后面的加钟订单时间要往前移
     */
    public function updateAddOrderTime($pay_order,$time_long){

        if(!empty($pay_order['is_add'])){

            $list = $this->where(['add_pid'=>$pay_order['add_pid']])->where('pay_type','>',0)->where('start_time','>',$pay_order['start_time'])->select()->toArray();

            if(!empty($list)){

                foreach ($list as $value){

                    $this->dataUpdate(['id'=>$value['id']],['start_time'=>$value['start_time']-$time_long,'end_time'=>$value['end_time']-$time_long]);

                }
            }

        }

        return true;

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-23 16:42
     * @功能说明:
     */
    public function coachCashList($dis,$page=10,$month=''){

        if(!empty($month)){

            $firstday = date('Y-m-01', $month);

            $lastday  = date('Y-m-d H:i:s', strtotime("$firstday +1 month")-1);

            $data = $this->where($dis)->whereTime('create_time','<=',$lastday)->field('id,coach_id,order_code,pay_type,pay_price,start_time,create_time,user_id,end_time,add_pid,is_add,coach_cash,true_car_price')
                ->order('create_time desc,id desc')->paginate($page)->toArray();
        }else{

            $data = $this->where($dis)->order('create_time desc,id desc')->field('id,coach_id,order_code,pay_type,pay_price,start_time,create_time,user_id,end_time,add_pid,is_add,coach_cash,true_car_price')->paginate($page)->toArray();
        }

        return $data;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-23 16:42
     * @功能说明:技师佣金
     */
    public function adminCashList($dis,$page=10,$month=''){

        if(!empty($month)){

            $firstday = date('Y-m-01', $month);

            $lastday  = date('Y-m-d H:i:s', strtotime("$firstday +1 month")-1);

            $data = $this->where($dis)->whereTime('create_time','<=',$lastday)->field('id,create_time,can_tx_date,add_pid,end_time,pay_type,have_tx,uniacid,user_id,coach_id,pay_price,true_service_price,true_car_price,start_time,coach_cash')
                ->order('create_time desc,id desc')->paginate($page)->toArray();
        }else{

            $data = $this->where($dis)->order('create_time desc,id desc')->field('id,create_time,can_tx_date,add_pid,end_time,pay_type,have_tx,uniacid,user_id,coach_id,pay_price,true_service_price,true_car_price,start_time,coach_cash')->paginate($page)->toArray();
        }

        return $data;

    }


    /**
     * @param $where
     * @功能说明:获取订单id
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-30 10:14
     */
    public function getFinanceOrderId($name,$type=0){

        $comm_model = new Commission();

        $where[] = ['c.nickName','like','%'.$name.'%'];

        $where[] = ['e.agent_name','like','%'.$name.'%'];

        $where[] = ['f.coach_name','like','%'.$name.'%'];

        $where[] = ['h.user_name','like','%'.$name.'%'];

        $where[] = ['m.user_name','like','%'.$name.'%'];

        $where[] = ['i.user_name','like','%'.$name.'%'];

        $dis = [];

        $dis[] = ['b.status','>',-1];

        if(!empty($type)){

            if($type==2){

                $dis[] = ['b.type','in',[2,5,6]];

            }else{

                $dis[] = ['b.type','=',$type];

            }
        }
        $change_order = [];
        //获取转单的
        if(in_array($type,[0,3,8])){

            $where1[] = ['g.now_coach_name','like','%'.$name.'%'];

            $where1[] = ['b.type','in',[3,8]];

            $change_order = $this->alias('a')
                ->join('massage_service_order_commission b','a.id = b.order_id')
                ->join('massage_order_coach_change_logs g','if(a.is_add=0,a.id,a.add_pid) = g.order_id AND g.is_new = 1')
                ->where($where1)
                ->group('a.id')
                ->column('a.id');
        }

        $order_id = $comm_model->alias('b')
            ->join('massage_service_user_list c','b.top_id = c.id  AND b.type in (1,14)','left')
            ->join('shequshop_school_admin e','b.top_id = e.id AND b.type in (2,5,6,11,19,20)','left')
            ->join('massage_service_coach_list f','b.top_id = f.id AND b.type in (3,8,17,18)','left')
            ->join('massage_channel_list h','b.top_id = h.id AND b.type = 10','left')
            ->join('massage_salesman_list m','b.top_id = m.id AND b.type = 12','left')
            ->join('massage_coach_broker_list i','b.broker_id = i.id AND b.type = 9','left')
            ->where($dis)
            ->where(function ($query) use ($where){
                $query->whereOr($where);
            })
            ->group('b.id')
            ->column('b.order_id');

        return array_merge($change_order,$order_id);

    }



    /**
     * @param $where
     * @功能说明:获取订单id
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-30 10:14
     */
    public function getFinanceObj($name,$type=0){

        $comm_model = new Commission();

        $dis = [];

        $dis[] = ['a.status','>',-1];

        $dis[] = ['a.type','<>',11];

        if(!empty($type)){

            if($type==2){

                $dis[] = ['a.type','in',[2,5,6]];

            }else{

                $dis[] = ['a.type','=',$type];

            }
        }

        $dataPath = APP_PATH  . 'massage/info/FinanceObj.php' ;

        $arr =  include $dataPath;

        $list = [];

        foreach ($arr as $value){
            $where = [];

            $table = $value['table'];

            $top_id = isset($value['field'])?$value['field']:'b.top_id';

            $type = '('.implode(',',$value['type']).')';

            $user_name = $value['name'];

            $obj_type = $value['obj_type'];

            $where[] = ["c.$user_name",'like','%'.$name.'%'];

            $data = $comm_model->alias('a')
                ->join("$table c","$top_id = c.id  AND a.type in $type",'left')
                ->where($dis)
                ->where(function ($query) use ($where){
                    $query->whereOr($where);
                })
                ->field("c.$user_name as user_name,c.id as user_id,if(c.id=-1,0,$obj_type) as obj_type")
                ->group('c.id')
                ->select()
                ->toArray();

            $list = array_merge($list,$data);
        }

        return $list;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-14 16:37
     * @功能说明:财务明细列表
     */
    public function financeDetailedList($dis,$page=10){

        $data = $this
            ->alias('b')
            ->join('massage_service_order_commission a','b.id = a.order_id','left')
            ->where($dis)
            ->field('b.id as order_id,b.init_service_price,b.init_material_price,b.material_price,b.free_fare,b.pay_type,b.pay_price,b.true_car_price,b.coach_refund_time,b.coach_id,b.create_time as end_time,b.pay_model,b.balance,b.is_add,b.true_service_price,b.order_code,b.transaction_id,b.pay_price,b.car_price')
            ->group('b.id')
            ->order('b.id desc')
            ->paginate($page)
            ->toArray();

        $comm = new Commission();

        $share_model = new CommShare();

        $refund_model= new RefundOrder();

        $data = $comm->getFinanceText($data);

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $v['init_price'] = round($v['init_service_price']+$v['init_material_price']+$v['true_car_price'],2);

                $v['discount_price'] = round($v['init_price']-$v['pay_price'],2);

                $v['true_service_price']  = round($v['pay_price'],2);
                //已经退款的金额
                $v['refund_cash'] = $refund_model->where(['order_id'=>$v['order_id'],'status'=>2])->sum('refund_price');

                $v['refund_cash'] = round($v['refund_cash'],2);
                //各类服务费
                $v = $share_model->financeShareData(['a.order_id'=>$v['order_id']],$v);

                $v['remain_cash'] = round($v['remain_cash']-$v['poster_cash']-$v['point_cash']-$v['refund_cash'],2);

                $car_admin = $comm->where(['order_id'=>$v['order_id'],'type'=>13])->where('status','>',-1)->find();

                $v['car_admin'] = !empty($car_admin)?1:0;
            }
        }

        return $data;
    }


    /**
     * @author chenniang
     * @DataTime: 2024-06-25 16:32
     * @功能说明:计算平台剩余佣金
     */
    public function companySurplusCash($id){

        $order_cash = $this->where(['id'=>$id])->where('transaction_id','<>',0)->sum('pay_price');

        $comm_model = new Commission();

        $refund_model= new RefundOrder();

        $share_model = new CommShare();

        $comm_cash = $comm_model->where(['order_id'=>$id])->where('status','>',-1)->where('type','in',[1,2,3,4,5,6,8,9,10,12,13,14,17,18,19,20,21,22])->sum('cash');

        $refund_cash= $refund_model->where(['order_id'=>$id,'status'=>2])->sum('refund_price');

        $share_cash = $share_model->orderShareData($id);

        $admin_share_cash = $comm_model->where(['order_id'=>$id,'type'=>23])->where('status','>',-1)->sum('cash');

        $cash = round($order_cash-$comm_cash-$refund_cash-$share_cash+$admin_share_cash,2);

        return $cash;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-07-02 17:30
     * @功能说明:获取具有佣金类型的订单id
     */
    public function getTypeCommOrder($type){

        $comm_model = new Commission();

        if($type==2){

            $dis[] = ['type','in',[2,5,6]];

        }elseif($type==8){

            $dis[] = ['type','in',[8,13]];

        }else{

            $dis[] = ['type','=',$type];
        }

        $dis[] = ['status','>',-1];

        $order_id = $comm_model->where($dis)->column('order_id');

        return $order_id;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-18 14:16
     * @功能说明:财务报表资金统计
     */
    public function financeDetailedData($dis){

        $data = $this
            //->alias('a')
            // ->join('massage_service_order_commission b','a.id = b.order_id','left')
            ->where($dis)
            //  ->group('id')
            ->column('id');

        $dataPath = APP_PATH  . 'massage/info/FinanceText.php' ;

        $text =  include $dataPath ;

        $comm = new Commission();

        $arr['true_service_price'] = $this->where($dis)->sum('true_service_price');

        $arr['true_car_price'] = $this->where($dis)->sum('true_car_price');

        $arr['remain_cash'] = round($arr['true_service_price'],2);

        $arr['true_service_price'] = round($arr['true_service_price']+$arr['true_car_price'],2);

        $arr['true_car_price']     = round($arr['true_car_price'],2);

        foreach ($text as $value){

            $map = [];

            $map[] = ['order_id','in',$data];

            $map[] = ['status','=',2];

            $map[] = ['type','in',$value['type']];
            //代理商
            if(!empty($value['city_type'])){

                $map[] = ['city_type','=',$value['city_type']];

            }

            $cash = $comm->where($map)->sum('cash');
            //对应的佣金
            $arr[$value['cash']] = !empty($cash)?round($cash,2):0;

            if(!in_array(8,$value['type'])){

                $arr['remain_cash'] -= $arr[$value['cash']];
            }
        }

        $under_cash = $comm->where('order_id','in',$data)->where('type','in',[2,5,6])->where(['status'=>2])->sum('coach_cash');

        $arr['remain_cash'] = round($arr['remain_cash']+$under_cash,2);

        return $arr;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-18 14:16
     * @功能说明:财务报表资金统计¥179479.94
     */
    public function financeDetailedDataV2($where){

        $dataPath = APP_PATH  . 'massage/info/FinanceTextAll.php' ;

        $text = include $dataPath ;

        $comm = new Commission();

        $share_model = new CommShare();

        $refund_model = new RefundOrder();

        $order = $this->alias('b')
            ->join('massage_service_order_commission a','a.order_id = b.id','left')
            ->where($where)
            ->group('b.id')
            ->column("b.pay_price,b.material_price,b.init_service_price,b.init_material_price,b.true_car_price");

        $arr['true_service_price'] = round(array_sum(array_column($order,'pay_price')),2);

        $arr['remain_cash']        = $arr['true_service_price'];

        $arr['material_price']     = round(array_sum(array_column($order,'material_price')),2);

        $arr['init_price']         = round(array_sum(array_column($order,'init_service_price'))+array_sum(array_column($order,'init_material_price'))+array_sum(array_column($order,'true_car_price')),2);

        $arr['discount_price']     = round($arr['init_price']-$arr['true_service_price'],2);

        $cash_list = $comm->alias('c')
                    ->whereExists(function ($query) use($where) {
                        $query->name('massage_service_order_commission')
                            ->alias('a')
                            ->join('massage_service_order_list b','a.order_id = b.id')
                            ->where(['a.status'=>2])
                            ->where($where)
                            ->whereRaw('c.order_id = b.id');
                    })
                    ->where(['c.status'=>2])
                    ->group('c.type,c.city_type')
                    ->field('round(sum(c.coach_cash),2)as coach_cash,round(sum(c.car_cash),2)as car_cash,round(sum(c.cash),2) as cash,c.type,c.city_type')
                    ->select()
                    ->toArray();

        foreach ($text as $ks=>$value){
            //对应的佣金
            $arr[$value['cash']] = 0;

            $admin_share_cash = 0;

            if(!empty($cash_list)){

                foreach ($cash_list as $v){

                    if(in_array($v['type'],$value['type'])&&(empty($value['city_type'])||$value['city_type']==$v['city_type'])){

                        $arr[$value['cash']] +=$v['cash']-$v['coach_cash']-$v['car_cash'];

                        $arr[$value['cash']] = round($arr[$value['cash']],2);
                    }

                    if($v['type']==23&&$ks==0){

                        $admin_share_cash = $v['cash'];
                    }
                }
            }

            $arr['remain_cash'] = $arr['remain_cash']- $arr[$value['cash']]+$admin_share_cash;
        }

        $arr['car_cash'] += $arr['admin_car_cash'];
        //技师佣金需要加上空单费和退款手续费
//        $arr['coach_cash']    += $arr['coach_refund_empty_cash']+$arr['coach_refund_comm_cash'];
//
//        $arr['district_cash'] += $arr['district_refund_empty_cash']+$arr['district_refund_comm_cash'];
//
//        $arr['province_cash'] += $arr['province_refund_empty_cash']+$arr['province_refund_comm_cash'];
//
//        $arr['city_cash']     += $arr['city_refund_empty_cash']+$arr['city_refund_comm_cash'];

        $car_point_cash = $share_model->alias('c')
            ->join('massage_service_order_list b','c.order_id = b.id')
            ->join('massage_service_order_commission a','a.order_id = b.id')
            ->where($where)
            ->where(['c.cash_type'=>1,'a.status'=>2])
            ->where('c.comm_type','in',[8,13])
            ->group('c.id')
            ->column('c.share_cash');

        $car_point_cash = array_sum($car_point_cash);

        if(!empty($car_point_cash)){

            $arr['car_cash'] .= '(手续费'.round($car_point_cash,2).')';
        }
        //各类服务费
        $arr = $share_model->financeShareData($where,$arr,2);
        //退款
        $refund_cash = $refund_model->alias('c')
            ->join('massage_service_order_list b','c.order_id = b.id')
            ->join('massage_service_order_commission a','a.order_id = b.id')
            ->where($where)
            ->where(['c.status'=>2])
            ->field('c.id')
            ->group('c.id')
            ->column('c.refund_price');

        $arr['refund_cash'] = round(array_sum($refund_cash),2);

        $arr['remain_cash'] = round($arr['remain_cash']-$arr['poster_cash']-$arr['point_cash']-$arr['refund_cash'],2);

        return $arr;
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-18 14:16
     * @功能说明:财务报表资金统计
     */
    public function financeDetailedDataV3($where){

        $dataPath = APP_PATH  . 'massage/info/FinanceTextAll.php' ;

        $text = include $dataPath ;

        $comm = new Commission();

        $share_model = new CommShare();

        $refund_model = new RefundOrder();

        $order = $this->alias('b')
            ->join('massage_service_order_commission a','a.order_id = b.id','left')
            ->where($where)
            ->group('b.id')
            ->column("b.pay_price,b.material_price");

        $arr['true_service_price'] = round(array_sum(array_column($order,'pay_price')),2);

        $arr['remain_cash']        = $arr['true_service_price'];

        $arr['material_price']     = round(array_sum(array_column($order,'material_price')),2);

        $cash_list = $comm->alias('c')
            ->whereExists(function ($query) use($where) {
                $query->name('massage_service_order_commission')
                    ->alias('a')
                    ->join('massage_service_order_list b','a.order_id = b.id')
                    ->where(['a.status'=>2])
                    ->where($where)
                    ->whereRaw('c.order_id = b.id');
            })
            ->where(['c.status'=>2])
            ->group('c.type,c.city_type')
            ->field('round(sum(c.coach_cash),2)as coach_cash,round(sum(c.car_cash),2)as car_cash,round(sum(c.cash),2) as cash,c.type,c.city_type')
            ->select()
            ->toArray();

        foreach ($text as $ks=>$value){
            //对应的佣金
            $arr[$value['cash']] = 0;

            $admin_share_cash = 0;

            if(!empty($cash_list)){

                foreach ($cash_list as $v){

                    if(in_array($v['type'],$value['type'])&&(empty($value['city_type'])||$value['city_type']==$v['city_type'])){

                        $arr[$value['cash']] = $v['cash']-$v['coach_cash']-$v['car_cash'];
                    }

                    if($v['type']==23&&$ks==0){

                        $admin_share_cash = $v['cash'];
                    }
                }
            }

            $arr['remain_cash'] = $arr['remain_cash']- $arr[$value['cash']]+$admin_share_cash;
        }

        $arr['car_cash'] += $arr['admin_car_cash'];
        //技师佣金需要加上空单费和退款手续费
        $arr['coach_cash']    += $arr['coach_refund_empty_cash']+$arr['coach_refund_comm_cash'];

        $arr['district_cash'] += $arr['district_refund_empty_cash']+$arr['district_refund_comm_cash'];

        $arr['province_cash'] += $arr['province_refund_empty_cash']+$arr['province_refund_comm_cash'];

        $arr['city_cash']     += $arr['city_refund_empty_cash']+$arr['city_refund_comm_cash'];

        $car_point_cash = $share_model->alias('c')
            ->join('massage_service_order_list b','c.order_id = b.id')
            ->join('massage_service_order_commission a','a.order_id = b.id')
            ->where($where)
            ->where(['c.cash_type'=>1,'a.status'=>2])
            ->where('c.comm_type','in',[8,13])
            ->group('c.id')
            ->column('c.share_cash');

        $car_point_cash = array_sum($car_point_cash);

        if(!empty($car_point_cash)){

            $arr['car_cash'] .= '(手续费'.round($car_point_cash,2).')';
        }
        //各类服务费
        $arr = $share_model->financeShareData($where,$arr,2);
        //退款
        $refund_cash = $refund_model->alias('c')
            ->join('massage_service_order_list b','c.order_id = b.id')
            ->join('massage_service_order_commission a','a.order_id = b.id')
            ->where($where)
            ->where(['c.status'=>2])
            ->field('c.id')
            ->group('c.id')
            ->column('c.refund_price');

        $arr['refund_cash'] = round(array_sum($refund_cash),2);

        $arr['remain_cash'] = round($arr['remain_cash']-$arr['poster_cash']-$arr['point_cash']-$arr['refund_cash'],2);

        return $arr;
    }
    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-14 16:37
     * @功能说明:财务明细列表
     */
    public function financeDetailedSelect($dis){

        $data = $this->alias('b')
            ->join('massage_service_order_commission a','b.id = a.order_id','left')
            ->where($dis)
            ->field('b.id as order_id,b.init_service_price,b.init_material_price,b.free_fare,b.pay_type,b.pay_price,b.true_car_price,b.coach_refund_time,b.coach_id,b.create_time as end_time,b.pay_model,b.balance,b.is_add,b.true_service_price,b.order_code,b.transaction_id,b.pay_price,b.car_price')
            ->group('b.id')
            ->order('b.id desc')
            ->select()
            ->toArray();

        $comm        = new Commission();

        $share_model = new CommShare();

        $refund_model= new RefundOrder();

        $arr['data'] = $data;

        $arr = $comm->getFinanceText($arr);

        if(!empty($arr['data'])){

            foreach ($arr['data'] as &$v){

                $v['true_service_price']  = round($v['pay_price'],2);

                $v['init_price'] = round($v['init_service_price']+$v['init_material_price']+$v['true_car_price'],2);

                $v['discount_price'] = round($v['init_price']-$v['pay_price'],2);
                //各类服务费
                $v = $share_model->financeShareData(['a.order_id'=>$v['order_id']],$v);
                //已经退款的金额
                $v['refund_cash'] = $refund_model->where(['order_id'=>$v['order_id'],'status'=>2])->sum('refund_price');

                $v['refund_cash'] = round($v['refund_cash'],2);

                $v['remain_cash'] = round($v['remain_cash']-$v['poster_cash']-$v['point_cash']-$v['refund_cash'],2);
            }
        }

        return $arr['data'];

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-06-02 10:21
     * @功能说明:获取当前订单可退款金额(目前只有拒单才需要)
     */
    public function getOrderRefundPrice($order){

        return round($order['true_service_price']+$order['true_car_price']+$order['material_price'],2);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-06-09 11:25
     * @功能说明:初始化技师车费，老版本佣金记录没有车费
     */
    public function initCarprice($coach_id,$uniacid){

        $comm_model = new Commission();

        $dis = [

            'a.coach_id' => $coach_id,

            'a.pay_type' => 7,
        ];

        $order_id = $this->alias('a')
            ->join('massage_service_order_commission b','a.id = b.order_id')
            ->where($dis)
            ->where('b.type','in',[8,13])
            ->group('a.id')
            ->column('a.id');
        $dis = [

            'coach_id' => $coach_id,

            'pay_type' => 7,

            'free_fare'=> 0,
        ];

        $order = Db::name('massage_service_order_list')->where($dis)->where('true_car_price','>',0)->where('id','not in',$order_id)->select()->toArray();

        $key   = 'initCarprice_key'.$coach_id;

        incCache($key,1,$uniacid);

        $value = getCache($key,$uniacid);

        if($value==1){

            if(!empty($order)){

                foreach ($order as $value){

                    $comm_model->carCommission($value,2);
                }
            }
        }

        decCache($key,1,$uniacid);

        return true;
    }


    /**
     * @param $order_id
     * @param int $type
     * @功能说明:用户端 订单列表和退款列表需要获取最开始的技师
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-06-13 13:46
     */
    public function getInitCoachInfo($order,$type=1){

        $change_log_model = new CoachChangeLog();

        $coach_model = new Coach();

        if(!empty($order['coach_id'])){

            return $order;
        }

        if($type==1){

            $add_id = $order['is_add']==0?$order['id']:$order['add_pid'];

        }else{

            $add_id = $order['is_add']==0?$order['order_id']:$this->where(['id'=>$order['order_id']])->value('add_pid');
        }

        $change_log = $change_log_model->dataInfo(['order_id'=>$add_id,'status'=>1]);

        if(!empty($change_log)){

            $order['coach_info'] = $coach_model->where(['id'=>$change_log['init_coach_id']])->field('id,uniacid,coach_name,mobile,work_img')->find();

        }

        return $order;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-11-24 11:08
     * @功能说明:获取订单加钟列表
     */
    public function getAddOrderList($order_id,$is_coach=0){

        if($is_coach==0){

            $data = $this->where(['add_pid'=>$order_id])->where('pay_type','>',0)->field('id,over_time,order_code,pay_type,balance,material_price,pay_model')->order('id desc')->select()->toArray();
        }else{

            $data = $this->where(['add_pid'=>$order_id])->where('pay_type','not in',[-1,1,8])->field('id,over_time,order_code,pay_type,balance,material_price,pay_model')->order('id desc')->select()->toArray();
        }

        if(!empty($data)){

            foreach ($data as &$vs){

                $vs['over_time'] -= time();

                $vs['over_time']  = $vs['over_time']>0?$vs['over_time']:0;

                if(!empty($vs['order_goods'])&&$is_coach==0){

                    foreach ($vs['order_goods'] as &$v){

                        $v['price']      = round($v['price']+$v['init_material_price'],2);

                        $v['true_price'] = round($v['true_price']+$v['material_price'],2);
                    }
                }
                //剩余可申请退款数量
                $can_refund_num = array_sum(array_column($vs['order_goods'],'can_refund_num'));
                //是否可以申请退款
                if((in_array($vs['pay_type'],[2,3,4,5])&&$can_refund_num>0)){

                    $vs['can_refund'] = 1;

                }else{

                    $vs['can_refund'] = 0;
                }
            }
        }

        return $data;
    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-12-06 19:18
     * @功能说明:订单是否还能退款 判断依据是否钱退完了
     */
    public function adminOrderCanRefund($order_id){

        $order = $this->dataInfo(['id'=>$order_id]);

        $refund_model = new RefundOrder();

        if($order['true_service_price']>0||$order['material_price']>0){

            return 1;

        }
        $can_refund_car_price = $refund_model->canRefundOrderPrice($order['id']);

        if($can_refund_car_price>0){

            return 1;
        }

        return 0;
    }


    public function orderCanRefundV2($order_goods,$pay_type){

        $can_refund_num = is_array($order_goods)?array_sum(array_column($order_goods,'can_refund_num')):0;
        //是否可以申请退款
        if((in_array($pay_type,[2,3,4,5,6])&&$can_refund_num>0)){

            return 1;

        }else{

            return 0;
        }

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-12-07 14:27
     * @功能说明:判断订单以及自订单是否可以退款
     */
    public function orderCanRefund($order_id){

        $order = $this->dataInfo(['id'=>$order_id]);

        $can_refund_num = is_array($order['order_goods'])?array_sum(array_column($order['order_goods'],'can_refund_num')):0;
        //是否可以申请退款
        if((in_array($order['pay_type'],[2,3,4,5,6])&&$can_refund_num>0)){

            return 1;

        }else{

            return 0;
        }
        //下面暂时不执行
        $price_log_model = new OrderPrice();

        $map = [

            'b.add_pid' => $order_id,

            'b.id'      => $order_id,
        ];

        $data = $price_log_model->alias('a')
            ->join('massage_service_order_list b','a.top_order_id = b.id')
            ->where('b.version','=',1)
            ->where('b.pay_type','<>',7)
            ->where('a.can_refund_price','>',0)
            ->where(function ($query) use ($map){
                $query->whereOr($map);
            })
            ->field('a.*')
            ->group('a.id')
            ->select()
            ->toArray();
        //如果没有可退金额肯定就退完了
        if(empty($data)){

            return 0;
        }

        $comm_model = new Commission();

        $car_record = $comm_model->where(['order_id'=>$order_id,'status'=>2])->where('type','in',[8,13])->find();
        //结算过车费
        if(!empty($car_record)){

            $car_record = $car_record->toArray();

            $cash = array_sum(array_column($data,'can_refund_price'));

            if($cash<=$car_record['cash']){

                return 0;
            }
        }

        return 1;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-12-12 14:40
     * @功能说明:核销加钟订单
     */
    public function hxAddOrder($order,$coach_id=0,$is_admin=0,$control_id=0){

        $refund_model = new RefundOrder();

        $log_model = new OrderLog();

        if($order['is_add']==0){

            $add_order = $this->where(['add_pid'=>$order['id'],'pay_type'=>2])->find();

            if(!empty($add_order)){

                return ['code'=>500,'msg'=>'该订单还有待接单待加钟订单，请先处理'];
            }

            $add_order = $this->where(['add_pid'=>$order['id'],'pay_type'=>1])->find();

            if(!empty($add_order)){

                return ['code'=>500,'msg'=>'该订单还有未支付的加钟订单，请先联系客户支付或者取消'];
            }

            $add_order = $this->where(['add_pid'=>$order['id']])->where('pay_type','in',[3,4,5,6])->select()->toArray();

            if(!empty($add_order)){

                foreach ($add_order as $value){
                    //判断有无申请中的退款订单
                    $refund_order = $refund_model->where(['order_id' => $value['id']])->where('status','in',[1,4,5])->count();

                    if ($refund_order>0) {

                        return ['code'=>500,'msg'=>'该订单加钟订单正在申请退款，请先联系平台处理再进行下一步'];
                    }

                    $res = $this->hxOrder($value,$coach_id);

                    if(!empty($res['code'])){

                        return $res;
                    }

                    $log_model->addLog($value['id'],$value['uniacid'],7,$value['pay_type'],$is_admin,$control_id);
                }
            }
        }
        return true;
    }


    /**
     * @param $order_type
     * @param $input_type
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-01-03 15:11
     */
    public function checkOrderStatus($order_type,$input_type,$is_add){

        if($order_type<2){

            return ['code'=>500,'msg'=>'订单已被取消,请刷新页面'];
        }

        if($order_type==7){

            return ['code'=>500,'msg'=>'订单已经完成,请刷新页面'];
        }

        if($order_type==$input_type){

            return ['code'=>500,'msg'=>'订单状态错误，请刷新'];
        }

        if($input_type==-1&&!in_array($order_type,[2,8])){

            return ['code'=>500,'msg'=>'订单状态错误，请刷新'];
        }

        if($input_type==3&&$order_type!=2){

            return ['code'=>500,'msg'=>'订单状态错误，请刷新'];
        }

        if($input_type==4&&$order_type!=3){

            return ['code'=>500,'msg'=>'订单状态错误，请刷新'];
        }

        if($input_type==5&&$order_type!=4){

            return ['code'=>500,'msg'=>'订单状态错误，请刷新'];
        }

//        if($input_type==6&&$order_type!=5&&$is_add==0){
//
//            return ['code'=>500,'msg'=>'订单状态错误，请刷新'];
//        }

        if($input_type==6&&!in_array($order_type,[2,3,5])){

            return ['code'=>500,'msg'=>'订单状态错误，请刷新'];
        }

        if($input_type==7&&$order_type!=6){

            return ['code'=>500,'msg'=>'订单状态错误，请刷新'];
        }


        return true;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-01-18 17:13
     * @功能说明:用户下单次数
     */
    public function useOrderTimes($user_id,$order_create_time){

        $times = $this->where(['user_id'=>$user_id])->where('pay_time','>',0)->where('create_time','<=',$order_create_time)->count();

        return $times;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-02-04 14:27
     * @功能说明:初始化一下没有兼容的订单数据
     */
    public function initOrderData($uniacid){

        // $this->dataUpdate(['uniacid'=>666],['start_material_price'=>-1]);

        $mapor[] = ['init_material_price','>',0];

        $mapor[] = ['material_price','>',0];

        $order = $this->where(['uniacid'=>$uniacid])->where(function ($query) use ($mapor){
            $query->whereOr($mapor);
        })->where('pay_time','>',0)->where('start_material_price','<',0)->field('id as order_id,discount,init_service_price,service_price,init_material_price,material_price,car_price,pay_price,free_fare')->select()->toArray();

        lbGetfDates();

        if(!empty($order)){

            foreach ($order as $key=> $value){

                $value['car_price'] = $value['free_fare']==0?$value['car_price']:0;

                $start_material_price = round($value['pay_price']-$value['car_price']-$value['service_price'],2);

                $update[$key] = [

                    'id' => $value['order_id'],

                    'start_material_price' => $start_material_price
                ];
            }

            $this->saveAll($update);
        }

        return true;
    }


    /**
     * @param $uniacid
     * @功能说明:初始化据单数据到退款表里
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-03-01 10:47
     */
    public function initCoachRefundOrder($uniacid){

        $key  = 'initCoachRefundOrder';

        incCache($key,1,$uniacid);

        if(getCache($key,$uniacid)==1){

            Db::startTrans();

            $refund_model = new RefundOrder();

            $refund_model->where(['uniacid'=>$uniacid,'type'=>2,'init'=>0,'status'=>2])->update(['status'=>-1]);

            $map[] = ['coach_refund_time','>',0];

            $map[] = ['coach_refund_code','>',0];

            $order_id = $refund_model->where(['uniacid'=>$uniacid,'type'=>2,'status'=>2])->column('order_id');

            $data = $this->where(['pay_type'=>-1])->where('id','not in',$order_id)->where('pay_time','>',0) ->where(function ($query) use ($map){
                $query->whereOr($map);
            })->field('id as order_id,uniacid,time_long,pay_price,coach_id,user_id,admin_id,partner_id,start_material_price,service_price,car_price,free_fare,is_add,balance,coach_refund_time,create_time,coach_refund_text')->select()->toArray();

            if(!empty($data)){

                // $order_id = array_column($data,'order_id');
                //  $this->where('id','in',$order_id)->update(['version'=>2]);
                foreach ($data as $k=>$v){

                    $insert[$k] = [

                        'uniacid'    => $v['uniacid'],

                        'user_id'    => $v['user_id'],

                        'admin_id'   => $v['admin_id'],

                        'partner_id' => $v['partner_id'],

                        'time_long'  => $v['time_long'],

                        'order_code' => orderCode(),

                        'coach_id'   => $v['coach_id'],

                        'apply_price'=> $v['pay_price'],

                        'refund_price'=> $v['pay_price'],

                        'have_price'=> $v['pay_price'],

                        'service_price'=> $v['service_price'],

                        'material_price'=> $v['start_material_price']>=0?$v['start_material_price']:0,

                        'refund_service_price'=> $v['service_price'],

                        'refund_material_price'=> $v['start_material_price']>=0?$v['start_material_price']:0,

                        'order_id'   => $v['order_id'],

                        'is_add'     => $v['is_add'],

                        'car_price'  => $v['free_fare']==0?$v['car_price']:0,

                        'refund_car_price' => $v['free_fare']==0?$v['car_price']:0,

                        'balance'    => !empty($v['balance'])?$v['pay_price']:0,

                        'type'       => 2,

                        'status'     => 2,

                        'refund_time'=> $v['coach_refund_time'],

                        'create_time'=> $v['create_time'],

                        'refund_text'=> $v['coach_refund_text'],

                        'version' => 2,

                        'init'    => 1
                    ];
                }

                $refund_model->saveAll($insert);
            }

            Db::commit();
        }

        decCache($key,1,$uniacid);

        return true;
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-19 16:23
     * @功能说明:渠道商佣金列表吧
     */
    public function channelDataList($dis,$mapor,$page=10){

        $data = $this->alias('a')
            ->join('massage_service_order_goods_list b','a.id = b.order_id')
            ->join('massage_service_order_commission c','a.id = c.order_id')
            ->where($dis)
            // ->where('c.cash','>',0)
            ->where('c.type','=',10)
            ->where('c.status','>',-1)
            ->where(function ($query) use ($mapor){
                $query->whereOr($mapor);
            })
            ->field('c.cash as channel_cash,c.id as comm_id,a.pay_model,a.balance,a.label_time,a.have_tx,a.id,a.coach_id,a.store_id,a.is_comment,a.order_code,a.true_service_price,a.pay_type,a.pay_price,a.start_time,a.create_time,a.user_id,a.end_time,a.add_pid,a.is_add,a.init_material_price,a.material_price')
            ->group('a.id')
            ->order('a.id desc')
            ->paginate($page)
            ->toArray();

        if(!empty($data['data'])){

            $abn_model = new OrderList();

            foreach ($data['data'] as &$v){

                $v['create_time'] = date('Y-m-d H:i:s',$v['create_time']);

                $v['start_time']  = date('Y-m-d H:i',$v['start_time']);

                $v['end_time']    = date('Y-m-d H:i',$v['end_time']);
                //异常订单标示
                $v['abn_order_id']= $abn_model->where(['order_id'=>$v['id']])->value('id');
            }
        }

        return $data;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-19 16:23
     * @功能说明:经纪人佣金列表
     */
    public function brokerDataList($dis,$mapor,$page=10){

        $data = $this->alias('a')
            ->join('massage_service_order_goods_list b','a.id = b.order_id')
            ->join('massage_service_order_commission c','a.id = c.order_id')
            ->where($dis)
            // ->where('c.cash','>',0)
            ->where('c.type','=',9)
            ->where('c.status','>',-1)
            ->where(function ($query) use ($mapor){
                $query->whereOr($mapor);
            })
            ->field('c.cash as broker_cash,c.balance as broker_balance,c.id as comm_id,a.pay_model,a.balance,a.label_time,a.have_tx,a.id,a.coach_id,a.store_id,a.is_comment,a.order_code,a.true_service_price,a.pay_type,a.pay_price,a.start_time,a.create_time,a.user_id,a.end_time,a.add_pid,a.is_add,a.init_material_price,a.material_price')
            ->group('a.id')
            ->order('a.id desc')
            ->paginate($page)
            ->toArray();

        if(!empty($data['data'])){

            $abn_model = new OrderList();

            foreach ($data['data'] as &$v){

                $v['create_time'] = date('Y-m-d H:i:s',$v['create_time']);

                $v['start_time']  = date('Y-m-d H:i',$v['start_time']);

                $v['end_time']    = date('Y-m-d H:i',$v['end_time']);
                //异常订单标示
                $v['abn_order_id']= $abn_model->where(['order_id'=>$v['id']])->value('id');
            }
        }

        return $data;
    }


    /**
     * @param $data
     * @功能说明:订单是否可以退款
     * @author chenniang
     * @DataTime: 2024-09-11 16:57
     */
    public function canRefundOrder($data,$after_service_can_refund,$max_minute){

        $can_refund_num = array_sum(array_column($data['order_goods'],'can_refund_num'));

        $refund_type = $after_service_can_refund==1?[2,3,4,5,6]:[2,3,4,5];
        //是否可以申请退款
        if((in_array($data['pay_type'],$refund_type)&&$can_refund_num>0)){

            $res = true;

            if($data['pay_type']==6){

                $service_time = time() - $data['start_service_time'];

                if($service_time>$max_minute*60){

                    $res = false;
                }
            }

        }else{

            $res = false;
        }

        return $res;
    }








}