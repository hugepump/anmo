<?php
namespace app\massage\model;

use app\adapay\model\Member;
use app\adapay\model\PayRecord;
use app\admin\model\ShopOrderRefund;
use app\adminuser\model\AdminUser;
use app\balancediscount\model\OrderShare;
use app\BaseModel;
use app\heepay\model\RecordList;
use app\member\model\Level;
use longbingcore\heepay\HeePay;
use longbingcore\wxcore\PayModel;
use think\facade\Db;

class RefundOrder extends BaseModel
{
    //定义表名
    protected $name = 'massage_service_refund_order';



     protected $append = [

         'order_goods',

         'coach_info',

         'address_info',

         'all_goods_num',

     ];



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-26 16:48
     * @功能说明:
     */
    public function getImgsAttr($value,$data){

        if(!empty($value)){

            return explode(',',$value);
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

            $order_goods_model = new RefundOrderGoods();

            $dis = [

                'refund_id' => $data['id']
            ];

            $num = $order_goods_model->where($dis)->sum('num');

            return $num;
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

        if(isset($data['coach_id'])&&isset($data['id'])&&isset($data['order_id'])&&isset($data['is_add'])){

            if(!empty($data['coach_id'])){

                $cap_model = new Coach();

                $info = $cap_model->where(['id'=>$data['coach_id']])->field('id,coach_name,mobile,work_img')->find();

            }else{

                $change_log_model = new CoachChangeLog();

                if($data['is_add']==1){

                    $order_model = new Order();

                    $order_id = $order_model->where(['id'=>$data['order_id']])->value('add_pid');

                }else{

                    $order_id = $data['order_id'];
                }

                $info['coach_name'] = $change_log_model->where(['order_id'=>$order_id])->order('id desc')->value('now_coach_name');

                $info['mobile']     = $change_log_model->where(['order_id'=>$order_id])->order('id desc')->value('now_coach_mobile');

                $info['work_img']   = defaultCoachAvatar();
            }

            return $info;
        }

    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-17 17:16
     * @功能说明:收货信息
     */
    public function getAddressInfoAttr($value,$data){

        if(!empty($data['order_id'])){

            $address_model = new OrderAddress();

            $info = $address_model->dataInfo(['order_id'=>$data['order_id']]);

            return $info;

        }

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-17 17:16
     * @功能说明:收货信息
     */
     public function getOrderGoodsAttr($value,$data){

         if(!empty($data['id'])){

             $goods_model = new RefundOrderGoods();

             $order_goods_model = new OrderGoods();

             $info = $goods_model->dataSelect(['refund_id'=>$data['id']]);

             if(!empty($info)){

                 foreach ($info as &$v){
                     //实际退款的服务价格
                     $v['refund_goods_price']    = $v['goods_price'];
                     //实际退款的物料费
                     $v['refund_material_price'] = $v['material_price'];
                     //商品价格
                     $v['goods_price']    = $order_goods_model->where(['id'=>$v['order_goods_id']])->sum('true_price');
                     //物料费
                     $v['material_price'] = $order_goods_model->where(['id'=>$v['order_goods_id']])->sum('material_price');

                     $v['goods_price']    = round($v['goods_price'],2);

                     $v['material_price'] = round($v['material_price'],2);
                 }
             }
             return $info;
         }
     }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-15 14:37
     * @功能说明:后台列表
     */
    public function adminDataList($dis,$page=10,$mapor=[]){

        $data = $this->alias('a')
                ->join('massage_service_coach_list b','a.coach_id = b.id','left')
                ->join('massage_service_refund_order_goods c','a.id = c.refund_id')
                ->join('massage_service_order_list d','a.order_id = d.id')
                ->join('massage_service_order_address e','a.order_id = e.order_id')
                ->where($dis)
                ->where(function ($query) use ($mapor){
                    $query->whereOr($mapor);
                })
                ->field('a.*,e.mobile,d.order_code as pay_order_code,e.user_name,d.pay_price,d.pay_type')
                ->group('a.id')
                ->order('a.id desc')
                ->paginate($page)
                ->toArray();

        if(!empty($data['data'])){

            $user_model = new User();

            $admin_model = new Admin();

            foreach ($data['data'] as &$v){

                $v['admin_name'] = $admin_model->where(['id'=>$v['admin_id']])->value('agent_name');

                $v['nickName']   = $user_model->where(['id'=>$v['user_id']])->value('nickName');

                $v['partner_name']= $user_model->where(['id'=>$v['partner_id']])->value('nickName');

                $v['agent_type'] = !empty($v['partner_id'])?2:1;

            }
        }
        return $data;
    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-15 14:37
     * @功能说明:手机端操作后台列表
     */
    public function indexAdminDataList($dis,$page=10){

        $data = $this->alias('a')
            ->join('massage_service_order_list d','a.order_id = d.id')
            ->where($dis)
            ->field('a.*,d.order_code as pay_order_code,d.pay_price,d.start_time')
            ->group('a.id')
            ->order('a.id desc')
            ->paginate($page)
            ->toArray();

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $v['start_time'] = date('Y-m-d H:i:s',$v['start_time']);
            }
        }

        return $data;

    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-19 17:46
     * @功能说明:小程序退款列表
     */
    public function indexDataList($dis,$where=[],$page=10){

        $data = $this->alias('a')
            ->join('massage_service_refund_order_goods c','a.id = c.refund_id')
            ->join('massage_service_order_list d','a.order_id = d.id')
            ->where($dis)
            ->where(function ($query) use ($where){
                $query->whereOr($where);
            })
            ->field('a.*,d.order_code as pay_order_code')
            ->group('a.id')
            ->order('a.id desc')
            ->paginate($page)
            ->toArray();

        return $data;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-04-08 17:08
     * @功能说明:退款中
     */
    public function refundIng($cap_id){

        $dis = [

            'cap_id' => $cap_id,

            'status' => 1
        ];

        $count = $this->where($dis)->count();

        return $count;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:04
     * @功能说明:添加
     */
    public function dataAdd($data){

        $data['status'] = 1;

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
    public function dataInfo($dis){

        $data = $this->where($dis)->find();

        return !empty($data)?$data->toArray():[];

    }





    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-18 09:37
     * @功能说明:通过退款
     */
    public function passOrderData($id,$price,$status,$refund_user=0,$text='',$type=1,$is_mobile=0,$is_admin=1){

        $refund_order= $this->dataInfo(['id'=>$id]);

        $order_model = new Order();

        $pay_order   = $order_model->dataInfo(['id'=>$refund_order['order_id']]);

        if(!in_array($refund_order['status'],[1,4,5])){

            return ['code'=>500,'msg'=>'订单状态错误，请刷新页面'];
        }

        $update = [

            'status'      => $status,

            'refund_time' => time(),

            'refund_price'=> $price,

            'refund_text' => $text,

            'check_user'  => $refund_user,

            'check_user_mobile' => $is_mobile,

            'is_admin' => $is_admin
        ];

        $comm_model = new Commission();

        $queue_model= new Queue();

        Db::startTrans();

        if($type==1){

            $res = $this->dataUpdate(['id'=>$refund_order['id']],$update);
        }else{

            $res = $this->dataUpdate(['id'=>$refund_order['id']],['status'=>$status]);
        }
        //修改退款子订单的退款状态
        $order_refund_goods = new RefundOrderGoods();

        $order_goods_model  = new OrderGoods();

        $res = $order_refund_goods->dataUpdate(['refund_id'=>$id],['status'=>2]);

        $goods_model = new Service();
        //退换库存
        foreach ($refund_order['order_goods'] as $v){

            $goods_num = $order_goods_model->where(['id'=>$v['order_goods_id']])->sum('num');

            $refund_num= $this->refundNum($v['order_goods_id']);

            if($refund_num<$goods_num){

                $res = $goods_model->setOrDelStock($v['goods_id'],$v['num'],1);
            }
        }
        //如果是加钟订单后面的加钟订单时间要往前移
        $order_model->updateAddOrderTime($pay_order,$refund_order['time_long']*60);
        //修改支付订单的各类信息
        $res = $this->updatePayorderData($pay_order,$refund_order,$price,$refund_user,$is_admin);

        if(!empty($res['code'])){

            Db::rollback();

            $queue_model->addQueue($refund_order['id'],1,$refund_order['uniacid'],$price,$refund_user,$text,$type,$is_mobile,$is_admin);

            $this->dataUpdate(['id'=>$refund_order['id']],['status'=>4]);

            return false;
        }

        $pay_order = $order_model->dataInfo(['id'=>$refund_order['order_id']]);

        $pay_order['refund_id'] = $id;
        //空单费|退款手续费
        $comm_model->refundEmptyComm($refund_order);
        //修改佣金加盟商技师信息
        $comm_model->refundCash($pay_order);
        //空单费
        $res = $comm_model->emptyCommSuccess($refund_order['id'],1);

        if($res==0){

            Db::rollback();

            $queue_model->addQueue($refund_order['id'],1,$refund_order['uniacid'],$price,$refund_user,$text,$type,$is_mobile,$is_admin);

            $this->dataUpdate(['id'=>$refund_order['id']],['status'=>4]);

            return false;
        }
        //查看货是否退完了
        $refund_success = $this->checkRefundNum($refund_order['order_id']);

        if($refund_success==1){

            $res = $comm_model->refundCompanySuccess($refund_order['order_id']);

            if($res==0){

                Db::rollback();

                $queue_model->addQueue($refund_order['id'],1,$refund_order['uniacid'],$price,$refund_user,$text,$type,$is_mobile,$is_admin);

                $this->dataUpdate(['id'=>$refund_order['id']],['status'=>4]);

                return false;
            }
        }

        $log_model = new OrderLog();
        //退款订单的操作日志
        $log_model->addLog($refund_order['id'],$pay_order['uniacid'],2,$refund_order['status'],$is_admin,$refund_user,2);

        Db::commit();

        return true;
    }


    /**
     * @param $id
     * @param $price
     * @param $payConfig
     * @param int $refund_user
     * @param string $text
     * @功能说明:退款
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-28 11:08
     */
    public function passOrder($id,$price,$payConfig,$refund_user=0,$text='',$is_mobile=0,$is_admin=1,$car_price=0){

        $refund_order= $this->dataInfo(['id'=>$id]);

        $order_model = new Order();

        $pay_order   = $order_model->dataInfo(['id'=>$refund_order['order_id']]);

        if(!in_array($refund_order['status'],[1,5])){

            return ['code'=>500,'msg'=>'订单状态错误，请刷新页面'];
        }

        if(!empty($refund_order['out_refund_no'])){

            return ['code'=>500,'msg'=>'已退款'];
        }

        if($price>0){

           $res = $this->refundCashV2($payConfig,$pay_order,$price,$id,$car_price);
            //失败并报错
            if(!empty($res['code'])&&$res['code']==500){

                return ['code'=>500,'msg'=>$res['msg']];
            }

            $res['status'] = 2;
        }
        //不需要退款或者退款成功
        if(empty($res)||$res['status']==2){

            $this->passOrderData($id,$price,2,$refund_user,$text,1,$is_mobile,$is_admin);
        }
        //退款失败 或者部分退款
        if(!empty($res['status'])&&in_array($res['status'],[4,5])){

            $update = [

                'refund_time' => time(),

                'refund_price'=> $price,

                'refund_text' => $text,

                'status'      => $res['status'],

                'check_user'  => $refund_user,

                'check_user_mobile' => $is_mobile,

                'is_admin' => $is_admin,

            ];

            if(!empty($res['msg'])){
                //如果失败查看失败原因
                $update['failure_reason']= $res['msg'];
            }

            $this->dataUpdate(['id'=>$refund_order['id']],$update);

            $update = [

                'refund_end' => $this->refundEndOrder($pay_order['id'])
            ];

            $this->dataUpdate(['id'=>$refund_order['id']],$update);
        }

        return true;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-18 09:37
     * @功能说明:通过退款
     */
    public function passOrderV1($id,$price,$payConfig,$refund_user=0,$text=''){

        $refund_order= $this->dataInfo(['id'=>$id]);

        $order_model = new Order();

        $pay_order   = $order_model->dataInfo(['id'=>$refund_order['order_id']]);

        if($refund_order['status']!=1){

            return ['code'=>500,'msg'=>'订单状态错误，请刷新页面'];
        }

        $update = [

            'status'      => 2,

            'refund_time' => time(),

            'refund_price'=> $price,

            'refund_text' => $text
        ];

        $comm_model = new Commission();

        Db::startTrans();

        $res = $this->dataUpdate(['id'=>$refund_order['id']],$update);

        if($res!=1){

            Db::rollback();

            return ['code'=>500,'msg'=>'退款失败，请重试'];
        }
        //修改退款子订单的退款状态
        $order_refund_goods = new RefundOrderGoods();

        $res = $order_refund_goods->dataUpdate(['refund_id'=>$id],['status'=>2]);

        if($res==0){

            Db::rollback();

            return ['code'=>500,'msg'=>'退款失败，请重试1'.$res];
        }

        $goods_model = new Service();
        //退换库存
        foreach ($refund_order['order_goods'] as $v){

            $res = $goods_model->setOrDelStock($v['goods_id'],$v['num'],1);

            if(!empty($res['code'])){

                Db::rollback();

                return $res;
            }
        }
        //如果是加钟订单后面的加钟订单时间要往前移
        $order_model->updateAddOrderTime($pay_order,$refund_order['time_long']*60);
        //修改支付订单的各类信息
        $res = $this->updatePayorderData($pay_order,$refund_order,$price,$refund_user);

        $pay_order = $order_model->dataInfo(['id'=>$refund_order['order_id']]);

        $pay_order['refund_id'] = $id;
        //修改佣金加盟商技师信息
        $comm_model->refundCash($pay_order);

        if($res!=1){

            Db::rollback();

            return ['code'=>500,'msg'=>'退款失败，请重试2'];

        }
        $log_model = new OrderLog();
        //退款订单的操作日志
        $log_model->addLog($refund_order['id'],$pay_order['uniacid'],2,$refund_order['status'],1,$refund_user,2);
        //退款
        if($price>0){

            $res = $this->refundCash($payConfig,$pay_order,$price,$id);
            //失败并报错
            if(!empty($res['code'])&&$res['code']==500){

                Db::rollback();

                return ['code'=>500,'msg'=>$res['msg']];
            }

            Db::commit();
        }else{

            Db::commit();
        }

        return true;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-17 16:18
     * @功能说明:退款修改主订单代信息
     */
    public function updatePayorderData($pay_order,$refund_order,$price,$refund_user,$is_admin=1){

        $order_model = new Order();

        $comm_model = new Commission();
        //退款的总时长
        $true_time_long = $pay_order['true_time_long'] - $refund_order['time_long'];

        $true_time_long = $true_time_long>0?$true_time_long:0;

        $order_update = [

            'true_time_long' => $true_time_long,
        ];

        if($refund_order['apply_price']>0){

            if(!empty($refund_order['order_goods'])){

                $coach_price = $material_price = 0;

                if($refund_order['version']==2){

                    foreach ($refund_order['order_goods'] as $v){

                        $coach_price    += $v['refund_goods_price']*$v['num'];

                        $material_price += $v['refund_material_price']*$v['num'];
                    }

                }else{
                    //服务费占总退款的比例(兼容以前的老版本)
                    $ser_bin = $refund_order['service_price']/$refund_order['apply_price'];
                    //扣除退款后的服务费
                    $coach_price = $price*$ser_bin;

                    $coach_price = $coach_price>0?round($coach_price,2):0;

                    $m_bin = $refund_order['material_price']/$refund_order['apply_price'];

                    $material_price = $price*$m_bin;
                }
                //服务费
                $order_update['true_service_price'] = round($pay_order['true_service_price'] - $coach_price-$refund_order['empty_service_cash']-$refund_order['comm_service_cash'],2);
                //物料费
                $order_update['material_price']     = round($pay_order['material_price'] - $material_price-$refund_order['empty_material_cash']-$refund_order['comm_material_cash'],2);
            }
            //退车费
            if($refund_order['car_price']>0){

                $order_update['true_car_price'] = $pay_order['true_car_price'] - $refund_order['car_price'];
            }
        }

        if($refund_order['car_price']>0){

           $res = $comm_model->where(['order_id'=>$refund_order['order_id']])->where('type','in',[8,13])->update(['status'=>-1]);
        }
        //查看货是否退完了
        $refund_success = $this->checkRefundNum($refund_order['order_id']);
        //退完了 就修改订单状态
        if($refund_success==1&&$pay_order['pay_type']!=-1){
            //如果技师出发需要给技师车费
            $change_model = new CoachChangeLog();

            $res = $change_model->giveCarPrice($pay_order,[4,5,6,7]);

            if(!empty($res['code'])){

                return $res;
            }

            $order_update['pay_type'] = -1;
            //未接单退换优惠券
            if(!empty($pay_order['pay_type'])&&$pay_order['pay_type']<=3){
                //退换优惠券
                $coupon_model = new CouponRecord();

                $coupon_model->couponRefund($pay_order['id']);
            }

            $log_model = new OrderLog();
            //添加订单日志
            $log_model->addLog($pay_order['id'],$pay_order['uniacid'],-1,$pay_order['pay_type'],$is_admin,$refund_user);
            //结算成长值
            $level_model = new Level();

            $pay_order['true_service_price'] = isset($order_update['true_service_price'])?$order_update['true_service_price']:$pay_order['true_service_price'];
            //如果有会员插件 需要加成长值
            $level_model->levelUp($pay_order,1);

            $adapay_model = new Member();
            //如果使用了分账需要给平台分账
            $adapay_model->adaPayMyself($pay_order['id']);
            //增加技师信用分
            $credit_model = new CreditConfig();

            $credit_model->creditRecordAdd($pay_order['coach_id'],6,$pay_order['uniacid'],$pay_order['id']);
            //将分销记录删除
            $comm_model->where(['order_id'=>$refund_order['order_id'],'status'=>1])->where('type','not in',[16])->update(['status'=>-1]);
        }else{

            $order_update['end_time'] = $pay_order['end_time'] - $refund_order['time_long']*60;
        }

        $res = $order_model->dataUpdate(['id'=>$refund_order['order_id']],$order_update);

        return $res;
    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-12-14 12:19
     * @功能说明:是否升级过
     */
    public function isUpOrder($pay_order){

        $order_model = new UpOrderList();

        $dis = [

            'order_id' => $pay_order['id'],

            'pay_type' => 2
        ];

        $find = $order_model->dataInfo($dis);

        return !empty($find)?1:0;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-25 10:44
     * @功能说明:减去金额日志里面的可退金额
     */
    public function decLogPrice($log){

        if(!empty($log)){

            $price_log_model = new OrderPrice();

            foreach ($log as $value){

                $cash = $value['cash'];

                $price_log_model->where(['id'=>$value['id']])->update(['can_refund_price'=>Db::Raw("can_refund_price-$cash")]);

            }
        }

        return true;
    }


    /**
     * @param $payConfig
     * @param $pay_order
     * @param $price
     * @param $refund_id
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-25 10:42
     */
    public function refundCashData($payConfig,$pay_order,$price,$refund_id,$car_price=0){

        $price_log_model = new OrderPrice();

        $order_model = new Order();
        //订单金额日志
        $log = $price_log_model->where(['top_order_id'=>$pay_order['id']])->where('can_refund_price','>',0)->order('order_price desc,id')->select()->toArray();

        $refund_price = 0;

        $have_refund  = 0;
        //1 成功 2退了一部分 3退款中
        $result = ['status'=>2];

        if(empty($log)){

            return ['code'=>500,'msg'=>'数据错误'];
        }

        foreach ($log as $value){

            $price -= $refund_price;
            //说明退完了
            if($price<=0){

                return $result;
            }
            //要退的金额
            $app_price = $price>=$value['can_refund_price']?$value['can_refund_price']:$price;
            //增加退款金额
            $refund_price+= $app_price;
            //是否有分账
            $res = $this->adapayRefundCash($value,$app_price,$refund_id,$value['id']);

            if($res == false){

                $res = $this->heepayRefundCash($value,$app_price,$refund_id,['id']);
            }
            //没有分账
            if($res==false){

                if($value['pay_model']==1){

                    $response = orderRefundApi($payConfig,$value['order_price'],$app_price,$value['transaction_id']);
                    //如果退款成功修改一下状态
                    if ( isset($response[ 'return_code' ]) && isset( $response[ 'result_code' ] ) && $response[ 'return_code' ] == 'SUCCESS' && $response[ 'result_code' ] == 'SUCCESS' ) {

                        $response['out_refund_no'] = !empty($response['out_refund_no'])?$response['out_refund_no']:$pay_order['order_code'];
                        //减去可退款金额
                        $price_log_model->where(['id'=>$value['id']])->update(['can_refund_price'=>Db::Raw("can_refund_price-$app_price")]);

                        if(!empty($refund_id)){

                            $this->dataUpdate(['id'=>$refund_id],['out_refund_no'=>$response['out_refund_no'],'have_price' => Db::Raw("have_price+$app_price")]);

                        }else{

                            $order_model->dataUpdate(['id'=>$pay_order['id']],['coach_refund_code'=>$response['out_refund_no']]);
                        }

                        $have_refund=1;

                    }else {
                        //失败就报错
                        $discption = !empty($response['err_code_des'])?$response['err_code_des']:$response['return_msg'];

                        if($have_refund==0){

                            return ['code'=>500,'msg'=> $discption];

                        }else{

                            $this->dataUpdate(['id' => $refund_id], ['failure_reason' => $discption]);
                            //已经退了一部分
                            $result['status'] = 5;

                            return $result;
                        }

                    }
                }elseif ($value['pay_model']==3){
                    //支付宝
                    $pay_model = new PayModel($payConfig);

                    $res = $pay_model->aliRefund($pay_order['transaction_id'], $app_price);

                    if (isset($res['alipay_trade_refund_response']['code']) && $res['alipay_trade_refund_response']['code'] == 10000) {
                        //减去可退款金额
                        $price_log_model->where(['id'=>$value['id']])->update(['can_refund_price'=>Db::Raw("can_refund_price-$app_price")]);

                        if (!empty($refund_id)) {

                            $this->dataUpdate(['id' => $refund_id], ['out_refund_no' => $res['alipay_trade_refund_response']['out_trade_no'],'have_price' => Db::Raw("have_price+$app_price")]);

                        } else {

                            $order_model->dataUpdate(['id' => $pay_order['id']], ['coach_refund_code' => $res['alipay_trade_refund_response']['out_trade_no']]);
                        }
                    } else {

                        if($have_refund==0){

                            return ['code'=>500,'msg'=> $res['alipay_trade_refund_response']['sub_msg']];

                        }else{
                            //已经退了一部分
                            $result['status'] = 5;

                            $this->dataUpdate(['id' => $refund_id], ['failure_reason' => $res['alipay_trade_refund_response']['sub_msg']]);

                            return $result;
                        }

                    }
                    $have_refund=1;

                }elseif ($value['pay_model']==4){
                    //储值折扣卡
                    $share_model = new OrderShare();

                    $car_cash = $car_price>$app_price?$app_price:$car_price;

                    $car_price -= $car_cash;

                    $service_cash = $app_price-$car_cash;

                    $res = $share_model->refundUpdateOrderBalanceDiscount($pay_order['id'],$refund_id,$pay_order['user_id'],$service_cash,$car_cash);

                    if($res==0){

                        return ['code'=>500,'msg'=>'退款失败'];
                    }

                }else{

                    $water_model = new BalanceWater();

                    $pay_order['pay_price'] = $app_price;

                    $res = $water_model->updateUserBalance($pay_order,3,1);
                    //修改用户余额
                    if($res==0){

                        return ['code'=>500,'msg'=>'退款失败'];
                    }

                    $price_log_model->where(['id'=>$value['id']])->where('can_refund_price','>',0)->update(['can_refund_price'=>Db::Raw("can_refund_price-$app_price")]);

                    if (!empty($refund_id)) {

                        $this->dataUpdate(['id' => $refund_id], ['have_price' => Db::Raw("have_price+$app_price")]);
                    }
                }
            }else{
                //报错
                if(!empty($res['code'])){

                    if($have_refund==0){

                        return $res;

                    }else{
                        //已经退了一部分
                        $result['status'] = 5;

                        $this->dataUpdate(['id' => $refund_id], ['failure_reason' => $res['msg']]);

                        return $result;
                    }
                }
                //打款中
                if($res==true){

                    $have_refund=1;
                    //减去可退款金额
                    $price_log_model->where(['id'=>$value['id']])->update(['can_refund_price'=>Db::Raw("can_refund_price-$app_price")]);

                    $result['status'] = 4;
                }
            }
        }
        return $result;
    }



    /**
     * @param $payConfig
     * @param $pay_order
     * @param $price
     * @param int $refund_id
     * @功能说明:退钱
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-12 20:31
     */
    public function refundCashV2($payConfig,$pay_order,$price,$refund_id=0,$car_price=0){

        $order_model = new Order();

        $price_log_model = new OrderPrice();
        //是否升级过
        $is_up = $this->isUpOrder($pay_order);

        $pay_record = new PayRecord();

        $dis = [

            'uniacid'    => $pay_order['uniacid'],

            'order_code' => $pay_order['order_code'],

            'status'     => 1
        ];
        //是否是分账订单
        $adapay_record = $pay_record->dataInfo($dis);

        if(empty($adapay_record)){

            $heepay_record_model = new RecordList();

            $dis = [

                'uniacid'    => $pay_order['uniacid'],

                'order_code' => $pay_order['order_code'],

                'status'     => 2
            ];

            $adapay_record = $heepay_record_model->dataInfo($dis);
        }

        $result = ['status'=>2];
        //正常情况所有订单都可以走这里 这里分开是为了兼容数据
        if($is_up==1||!empty($adapay_record)){

            $result = $this->refundCashData($payConfig,$pay_order,$price,$refund_id);

        }else{
            //普通订单只有单笔 并且不是分账订单
            if($pay_order['pay_model']==1){
                //微信退款
                $response = orderRefundApi($payConfig,$pay_order['pay_price'],$price,$pay_order['transaction_id']);
                //如果退款成功修改一下状态
                if ( isset( $response[ 'return_code' ] ) && isset( $response[ 'result_code' ] ) && $response[ 'return_code' ] == 'SUCCESS' && $response[ 'result_code' ] == 'SUCCESS' ) {

                    $response['out_refund_no'] = !empty($response['out_refund_no'])?$response['out_refund_no']:$pay_order['order_code'];

                    $price_log_model->where(['order_id'=>$pay_order['id']])->where('can_refund_price','>',0)->update(['can_refund_price'=>Db::Raw("can_refund_price-$price")]);

                    if(!empty($refund_id)){

                        $this->dataUpdate(['id'=>$refund_id],['out_refund_no'=>$response['out_refund_no'],'have_price'=>$price]);

                    }else{

                        $order_model->dataUpdate(['id'=>$pay_order['id']],['coach_refund_code'=>$response['out_refund_no']]);
                    }

                }else {
                    //失败就报错
                    $discption = !empty($response['err_code_des'])?$response['err_code_des']:$response['return_msg'];

                    return ['code'=>500,'msg'=> $discption];
                }
            }elseif($pay_order['pay_model']==2){

                $water_model = new BalanceWater();

                $pay_order['pay_price'] = $price;

                $res = $water_model->updateUserBalance($pay_order,3,1);
                //修改用户余额
                if($res==0){

                    return ['code'=>500,'msg'=>'退款失败'];
                }

                $res = $price_log_model->where(['order_id'=>$pay_order['id']])->where('can_refund_price','>',0)->update(['can_refund_price'=>Db::Raw("can_refund_price-$price")]);

                if(!empty($refund_id)){

                    $this->dataUpdate(['id'=>$refund_id],['have_price'=>$price]);
                }

            }elseif ($pay_order['pay_model']==4){
                //储值折扣卡
                $share_model = new OrderShare();

                $service_cash= $price-$car_price;

                $res = $share_model->refundUpdateOrderBalanceDiscount($pay_order['id'],$refund_id,$pay_order['user_id'],$service_cash,$car_price);

                if($res==0){

                   // return ['code'=>500,'msg'=>'退款失败1'];
                }

            }else{
                //支付宝
                $pay_model = new PayModel($payConfig);

                $res = $pay_model->aliRefund($pay_order['transaction_id'], $price);

                if (isset($res['alipay_trade_refund_response']['code']) && $res['alipay_trade_refund_response']['code'] == 10000) {

                    $price_log_model->where(['order_id'=>$pay_order['id']])->where('can_refund_price','>',0)->update(['can_refund_price'=>Db::Raw("can_refund_price-$price")]);

                    if (!empty($refund_id)) {

                        $this->dataUpdate(['id' => $refund_id], ['out_refund_no' => $res['alipay_trade_refund_response']['out_trade_no'],'have_price'=>$price]);

                    }else {

                        $order_model->dataUpdate(['id' => $pay_order['id']], ['coach_refund_code' => $res['alipay_trade_refund_response']['out_trade_no']]);
                    }
                } else {

                    return ['code' => 500, 'msg' => $res['alipay_trade_refund_response']['sub_msg']];
                }
            }
        }

        return $result ;
    }





    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-12-14 12:35
     * @功能说明:升级订单退款（微信）
     */
    public function upOrderRefundWeixin($payConfig,$pay_order,$price,$refund_id=0){

        $price_log_model = new OrderPrice();

        $order_model = new Order();
        //订单金额日志
        $log = $price_log_model->where(['top_order_id'=>$pay_order['id']])->where('can_refund_price','>',0)->order('order_price desc,id')->select()->toArray();

        $refund_price = 0;

        foreach ($log as $value){

            $price -= $refund_price;
            //说明退完了
            if($price<=0){

                return true;
            }
            //要退的金额
            $app_price = $price>=$value['can_refund_price']?$value['can_refund_price']:$price;
            //修改订单日志里面的可退款金额
            $price_log_model->dataUpdate(['id'=>$value],['can_refund_price'=>$value['can_refund_price']-$app_price]);
            //增加退款金额
            $refund_price+= $app_price;
            //是否有分账
            $res = $this->adapayRefundCash($value,$price,$refund_id);

            if(!empty($res['code'])){

                return $res;
            }

            if($res==true){

                continue;
              //  return $res;
            }

            $response = orderRefundApi($payConfig,$value['order_price'],$app_price,$value['transaction_id']);
            //如果退款成功修改一下状态
            if ( isset($response[ 'return_code' ]) && isset( $response[ 'result_code' ] ) && $response[ 'return_code' ] == 'SUCCESS' && $response[ 'result_code' ] == 'SUCCESS' ) {

                $response['out_refund_no'] = !empty($response['out_refund_no'])?$response['out_refund_no']:$pay_order['order_code'];

                if(!empty($refund_id)){

                    $this->dataUpdate(['id'=>$refund_id],['out_refund_no'=>$response['out_refund_no']]);

                }else{

                    $order_model->dataUpdate(['id'=>$pay_order['id']],['coach_refund_code'=>$response['out_refund_no']]);
                }

            }else {
                //失败就报错
                $discption = !empty($response['err_code_des'])?$response['err_code_des']:$response['return_msg'];

                return ['code'=>500,'msg'=> $discption];

            }

        }

        return true;
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-12-14 12:35
     * @功能说明:升级订单退款（支付宝）
     */
    public function upOrderRefundAli($payConfig,$pay_order,$price,$refund_id=0){

        $price_log_model = new OrderPrice();

        $order_model = new Order();
        //支付宝
        $pay_model = new PayModel($payConfig);
        //订单金额日志
        $log = $price_log_model->where(['top_order_id'=>$pay_order['id']])->where('can_refund_price','>',0)->order('order_price desc,id')->select()->toArray();

        $refund_price = 0;

        foreach ($log as $value){

            $price -= $refund_price;
            //说明退完了
            if($price<=0){

                return true;
            }
            //要退的金额
            $app_price = $price>=$value['can_refund_price']?$value['can_refund_price']:$price;
            //修改订单日志里面的可退款金额
            $price_log_model->dataUpdate(['id'=>$value],['can_refund_price'=>$value['can_refund_price']-$app_price]);
            //增加退款金额
            $refund_price+= $app_price;

            $res = $pay_model->aliRefund($pay_order['transaction_id'],$price);

            if(isset($res['alipay_trade_refund_response']['code'])&&$res['alipay_trade_refund_response']['code']==10000){

                if(!empty($refund_id)){

                    $this->dataUpdate(['id'=>$pay_order['id']],['out_refund_no'=>$res['alipay_trade_refund_response']['out_trade_no']]);

                }else{

                    $order_model->dataUpdate(['id'=>$pay_order['id']],['coach_refund_code'=>$res['alipay_trade_refund_response']['out_trade_no']]);
                }
            }else{

                return ['code'=>500,'msg'=> $res['alipay_trade_refund_response']['sub_msg']];

            }
        }
        return true;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-12 15:42
     * @功能说明:分账退款
     */
    public function adapayRefundCash($pay_order,$price,$refund_id,$log_id=0,$type='refund'){

        $pay_record = new PayRecord();

        $dis = [

            'uniacid'    => $pay_order['uniacid'],

            'order_code' => $pay_order['order_code'],

            'status'     => 1
        ];

        $adapay_record = $pay_record->dataInfo($dis);

        $action = $type=='refund'?'refundCallback':'upOrderRefundCallback';
        //如果使用了分账 退款要走分账
        if(!empty($adapay_record)){

            $adapay = new \longbingcore\wxcore\Adapay($pay_order['uniacid']);

            $adapay_code = orderCode();

            if($adapay_record['pay_mode']==1){
                //分账
                $res = $adapay->orderRefund($adapay_record['adapay_id'],$adapay_code,$price,$action);
            }else{
                //不分账
                $res = $adapay->orderRefundNoAda($adapay_record['adapay_id'],$adapay_code,$price,$action);
            }

            if(isset($res['status'])&&$res['status']!='failed'){

                $pay_record = new PayRecord();

                $insert = [

                    'uniacid'    => $pay_order['uniacid'],

                    'adapay_code'=> $adapay_code,

                    'adapay_id'  => $res['id'],

                    'pay_price'  => $price,

                    'true_price' => $price,

                    'type'       => $type,

                    'order_id'   => $refund_id,

                    'pay_order_id'=> $pay_order['id'],

                    'log_id'      => $log_id,

                    'pay_record_id' => $adapay_record['id']
                ];

                $pay_record->dataAdd($insert);

                $pay_record->dataUpdate(['id'=>$adapay_record['id']],['true_price'=>$adapay_record['true_price']-$price]);

                return true;
            }

            $res['error_msg'] = !empty($res['error_msg'])?$res['error_msg']:'错误';
            //失败了返回失败原因
            return ['code'=>500,'msg'=>$res['error_msg']];
        }

        return false;
    }





    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-12 15:42
     * @功能说明:分账退款
     */
    public function heepayRefundCash($pay_order,$price,$refund_id,$log_id=0,$type='refund'){

        $pay_record = new RecordList();

        $dis = [

            'uniacid'    => $pay_order['uniacid'],

            'order_code' => $pay_order['order_code'],

            'status'     => 2
        ];

        $adapay_record = $pay_record->dataInfo($dis);

        $action = $type=='refund'?'refundCallback':'upOrderRefundCallback';
        //如果使用了分账 退款要走分账
        if(!empty($adapay_record)){

            $heepay = new HeePay($pay_order['uniacid']);

            $heepay_code = orderCode();

            $res = $heepay->refundOrder($adapay_record['heepay_order_code'],$heepay_code,$price,$action);

            if(isset($res['ret_code'])&&$res['ret_code']=='0000'){

                $insert = [

                    'uniacid'    => $pay_order['uniacid'],

                    'heepay_order_code'=> $heepay_code,

                    'cash'       => $price,

                    'true_price' => $price,

                    'type'       => $type,

                    'order_id'   => $refund_id,

                    'pay_order_id'=> $pay_order['id'],

                    'log_id'      => $log_id,

                    'pay_record_id' => $adapay_record['id']
                ];

                $pay_record->dataAdd($insert);

                $pay_record->dataUpdate(['id'=>$adapay_record['id']],['true_price'=>$adapay_record['true_price']-$price]);

                return true;
            }

            $res['error_msg'] = !empty($res['ret_msg'])?$res['ret_msg']:'错误';
            //失败了返回失败原因
            return ['code'=>500,'msg'=>$res['error_msg']];
        }

        return false;
    }






    /**
     * @param $payConfig
     * @param $pay_order
     * @param $price
     * @param int $refund_id
     * @功能说明:退钱
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-12 20:31
     */
    public function refundCash($payConfig,$pay_order,$price,$refund_id=0){

        $order_model = new Order();

        $is_up = $this->isUpOrder($pay_order);

        if($pay_order['pay_model']==1){
            //没有升级过 目前的设计看来退款不会存在升级过，先保留
            if($is_up==0){
                //分账退款
                $res = $this->adapayRefundCash($pay_order,$price,$refund_id);

                if(!empty($res['code'])){

                    return $res;
                }

                if($res==true){

                    return $res;
                }
                //微信退款
                $response = orderRefundApi($payConfig,$pay_order['pay_price'],$price,$pay_order['transaction_id']);
                //如果退款成功修改一下状态
                if ( isset( $response[ 'return_code' ] ) && isset( $response[ 'result_code' ] ) && $response[ 'return_code' ] == 'SUCCESS' && $response[ 'result_code' ] == 'SUCCESS' ) {

                    $response['out_refund_no'] = !empty($response['out_refund_no'])?$response['out_refund_no']:$pay_order['order_code'];

                    if(!empty($refund_id)){

                        $this->dataUpdate(['id'=>$refund_id],['out_refund_no'=>$response['out_refund_no']]);

                    }else{

                        $order_model->dataUpdate(['id'=>$pay_order['id']],['coach_refund_code'=>$response['out_refund_no']]);
                    }

                }else {
                    //失败就报错
                    $discption = !empty($response['err_code_des'])?$response['err_code_des']:$response['return_msg'];

                    return ['code'=>500,'msg'=> $discption];

                }

            }else{

                $res = $this->upOrderRefundWeixin($payConfig,$pay_order,$price,$refund_id=0);

                if(!empty($res['code'])){

                    return $res;
                }

            }
        }elseif($pay_order['pay_model']==2){

            $water_model = new BalanceWater();

            $pay_order['pay_price'] = $price;

            $res = $water_model->updateUserBalance($pay_order,3,1);
            //修改用户余额
            if($res==0){

                return false;

            }

        }else{
            //没有升级过
            if($is_up==0) {
                //支付宝
                $pay_model = new PayModel($payConfig);

                $res = $pay_model->aliRefund($pay_order['transaction_id'], $price);

                if (isset($res['alipay_trade_refund_response']['code']) && $res['alipay_trade_refund_response']['code'] == 10000) {

                    if (!empty($refund_id)) {

                        $this->dataUpdate(['id' => $pay_order['id']], ['out_refund_no' => $res['alipay_trade_refund_response']['out_trade_no']]);

                    } else {

                        $order_model->dataUpdate(['id' => $pay_order['id']], ['coach_refund_code' => $res['alipay_trade_refund_response']['out_trade_no']]);
                    }
                } else {

                    return ['code' => 500, 'msg' => $res['alipay_trade_refund_response']['sub_msg']];

                }
            }else{

                $res = $this->upOrderRefundAli($payConfig,$pay_order,$price,$refund_id=0);

                if(!empty($res['code'])){

                    return $res;
                }
            }

        }

        return true;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-18 10:29
     * @功能说明:检查改订单款退完了没
     */
    public function checkRefundNum($order_id){

        $order_goods_model = new OrderGoods();

        $order_refund_goods_model = new RefundOrderGoods();

        $dis = [

            'order_id' => $order_id,

            'status'   => 1
        ];

        $list = $order_goods_model->where($dis)->select()->toArray();

        $res = 1;

        if(!empty($list)){

            foreach ($list as $value){

                $dis['status'] = 2;

                $dis['order_goods_id'] = $value['id'];

                $refund_num = $order_refund_goods_model->where($dis)->sum('num');

                if($value['num']>$refund_num){

                    $res = 0;
                }
            }
        }

        return $res;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-18 15:38
     * @功能说明:该天的退款
     */
    public function datePrice($date,$uniacid,$cap_id=0,$end_time='',$type=1){

        $end_time = !empty($end_time)?$end_time:$date+86399;

        $dis = [];

        $dis[] = ['status','=',2];

        $dis[] = ['create_time','between',"$date,$end_time"];

        $dis[] = ['uniacid',"=",$uniacid];

        if(!empty($cap_id)){

            $dis[] = ['cap_id','=',$cap_id];
        }

        if($type==1){

            $price = $this->where($dis)->sum('refund_price');

            return round($price,2);

        }else{

            $count = $this->where($dis)->count();

            return $count;
        }
    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-26 13:33
     * @功能说明:申请退款
     */
    public function applyRefund($order,$input,$is_user = 1){

        $order_goods_model = new OrderGoods();

        $refund_price = $material_price = 0;

        Db::startTrans();

        $list = $input['list'];

        $time_long = $comm_balance = $apply_empty_cash = $apply_comm_cash = 0;
        //已经开始服务 需要收取退款手续费
        if($order['pay_type']==6&&$is_user==1){

            $after_service_can_refund = getConfigSetting($order['uniacid'],'after_service_can_refund');

            if($after_service_can_refund==0){

                return ['code'=>500,'msg'=>'开始服务后，不允许退款'];
            }

            $fee_model = new EmptyTicketFeeConfig();

            $cash_list = $fee_model->where(['uniacid'=>$order['uniacid']])->order('minute,id desc')->select()->toArray();

            $service_time = time() - $order['start_service_time'];

            $max_minute = $fee_model->where(['uniacid'=>$order['uniacid']])->max('minute');

            if($service_time>$max_minute*60){

                return ['code'=>500,'msg'=>"开始服务后{$max_minute}分钟后，不允许退款"];
            }

            if(!empty($cash_list)){

                foreach ($cash_list as $key=>$vs){

                    $minute = $key>0?$cash_list[$key-1]['minute']:0;

                    if($service_time>$minute*60){

                        $comm_balance = $vs['balance'];
                    }
                }
            }
        }

        foreach ($list as $k=>$value){

            if(!empty($value['id'])){

                $order_goods = $order_goods_model->dataInfo(['id'=>$value['id']]);

                $time_long += $order_goods['time_long']*$value['num'];

                if(empty($order_goods)){

                    return ['code'=>500,'msg'=>'商品未找到'];
                }

                if($value['num']>$order_goods['can_refund_num']||$value['num']==0){

                    return ['code'=>500,'msg'=>'退款数量错误'];
                }
                //退款金额
                $refund_price   += $order_goods['true_price']*$value['num'];

                $material_price += $order_goods['material_price']*$value['num'];
                //退款手续费
                $apply_comm_cash += ($refund_price+$material_price)*$comm_balance/100;

                $list[$k]['goods_id']    = $order_goods['goods_id'];

                $list[$k]['goods_name']  = $order_goods['goods_name'];

                $list[$k]['goods_cover'] = $order_goods['goods_cover'];

                $list[$k]['goods_price'] = $order_goods['price'];

                $list[$k]['material_price'] = $order_goods['init_material_price'];

                $list[$k]['apply_comm_cash']= ($order_goods['true_price']+$order_goods['material_price'])*$value['num']*$comm_balance/100;

                $res = $order_goods_model->where(['id'=>$value['id']])->update(['can_refund_num'=>$order_goods['can_refund_num']-$value['num']]);

                if($res!=1){

                    Db::rollback();

                    return ['code'=>500,'msg'=>'申请失败'];
                }
            }
        }

        $new_order_goods = $order_goods_model->where(['order_id'=>$order['id']])->select()->toArray();
        //剩余可申请退款数量
        $can_refund_num = array_sum(array_column($new_order_goods,'can_refund_num'));

        $car_price = 0;

        $refund_goods_model = new RefundOrderGoods();

        $change_log_model   = new CoachChangeLog();
        //转派订单的时候是否给了车费
        $have_car_price = $change_log_model->dataInfo(['order_id'=>$order['id'],'status'=>1,'have_car_price'=>1]);

        $have_apply_car = $this->where(['order_id'=>$order['id']])->where('car_price','>',0)->where('status','in',[1,2,4,5])->find();
        //退车费
        if(empty($have_apply_car)&&!in_array($order['pay_type'],[4,5,6,7])&&$can_refund_num==0&&empty($have_car_price)&&$order['free_fare']==0){

            $car_price = $order['car_price'];
        }

        $have_empty = $this->where(['order_id'=>$order['id']])->where('status','in',[1,2,4,5])->where('refund_empty_cash','>',0)->count();
        //到达后需要收空单费 空单费只收取一次
        if($order['pay_type']==5&&$can_refund_num==0&&$have_empty==0&&$is_user==1&&$order['is_add']==0){

            $apply_empty_cash = getConfigSetting($order['uniacid'],'empty_order_cash');

            $apply_empty_cash = $apply_empty_cash>$refund_price+$material_price?$refund_price+$material_price:$apply_empty_cash;
        }

        $insert = [

            'uniacid'    => $order['uniacid'],

            'user_id'    => $order['user_id'],

            'admin_id'   => $order['admin_id'],

            'partner_id' => $order['partner_id'],

            'time_long'  => $time_long,

            'order_code' => orderCode(),

            'coach_id'   => $order['coach_id'],

            'apply_price'=> round($refund_price+$car_price+$material_price,2),

            'service_price' => $refund_price,

            'material_price'=> $material_price,

            'order_id'   => $order['id'],

            'is_add'     => $order['is_add'],

            'text'       => $input['text'],

            'car_price'  => $car_price,

            'imgs'       => !empty($input['imgs'])?implode(',',$input['imgs']):'',

            'balance'    => !empty($order['balance'])?$refund_price:0,
            //退款手续费比例
            'comm_balance' => $comm_balance,
            //空单费
            'refund_empty_cash'=> $apply_empty_cash,
            //退款手续费
            'apply_comm_cash' => round($apply_comm_cash,2)
        ];

        $res = $this->dataAdd($insert);

        if($res!=1){

            Db::rollback();

            return ['code'=>500,'msg'=>'申请失败'];
        }

        $refund_id = $this->getLastInsID();

        foreach ($list as $value){

            $insert = [

                'uniacid'        => $order['uniacid'],

                'order_id'       => $order['id'],

                'refund_id'      => $refund_id,

                'order_goods_id' => $value['id'],

                'goods_id'       => $value['goods_id'],

                'goods_name'     => $value['goods_name'],

                'goods_cover'    => $value['goods_cover'],

                'num'            => $value['num'],

                'goods_price'    => $value['goods_price'],

                'material_price' => $value['material_price'],

                'status'         => 1,

                'apply_comm_cash' => $value['apply_comm_cash']
            ];

            $res = $refund_goods_model->dataAdd($insert);

            if($res!=1){

                Db::rollback();

                return ['code'=>500,'msg'=>'申请失败'];
            }
        }

        Db::commit();

        $notice_model = new NoticeList();
        //增加后台提醒
        $notice_model->dataAdd($order['uniacid'],$refund_id,2,$order['admin_id']);

        $coach_model = new Coach();

        $refund_order = $this->dataInfo(['id'=>$refund_id]);
        //发送公众号消息
        $coach_model->refundSendMsg($refund_order);

        $log_model = new OrderLog();
        //退款订单的操作日志
        $log_model->addLog($refund_id,$order['uniacid'],1,0,3,$order['user_id'],2);

        return $refund_id;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-04-12 09:23
     * @功能说明:获取订单已经退款的数量
     */
    public function refundNum($order_goods_id){

        $dis = [

            'b.order_goods_id' => $order_goods_id,

            'a.status'   => 2
        ];

        $num = $this->alias('a')
                ->join('massage_service_refund_order_goods b','a.id = b.refund_id')
                ->where($dis)
                ->group('b.order_goods_id')
                ->sum('b.num');

        return $num;

    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-04-12 12:04
     * @功能说明:拒绝退款
     */
    public function noPassRefund($refund_id,$refund_user=0,$is_mobile=0,$is_admin=1){

        $dis = [

            'id' => $refund_id
        ];

        $refund_order = $this->dataInfo($dis);

        if(!in_array($refund_order['status'],[1,5])){

            return ['code'=>500,'msg'=>'退款状态错误'];

        }

        if(!empty($refund_order['out_refund_no'])){

            return ['code'=>500,'msg'=>'已退款'];
        }

        $update = [

            'status'      => 3,

            'refund_time' => time(),

            'check_user'  => $refund_user,

            'check_user_mobile' => $is_mobile
        ];

        Db::startTrans();

        $res = $this->dataUpdate($dis,$update);

        if($res==0){

            Db::rollback();

            return ['code'=>500,'msg'=>'退款失败，请重试'];

        }
        //修改退款子订单的退款状态
        $order_refund_goods = new RefundOrderGoods();

        $res = $order_refund_goods->dataUpdate(['refund_id'=>$refund_id],['status'=>3]);

        if($res==0){

            Db::rollback();

            return ['code'=>500,'msg'=>'退款失败，请重试'];
        }

        if(!empty($refund_order['order_goods'])){

            $order_goods_model = new OrderGoods();

            foreach ($refund_order['order_goods'] as $v){

                $num = $v['num'];

                $order_goods_model->where(['id'=>$v['order_goods_id']])->update(['can_refund_num'=>Db::Raw("can_refund_num+$num")]);
            }
        }

        $this->where(['order_id'=>$refund_order['order_id']])->update(['refund_end'=>0]);

        $log_model = new OrderLog();
        //退款订单的操作日志
        $log_model->addLog($refund_order['id'],$refund_order['uniacid'],3,$refund_order['status'],$is_admin,$refund_user,2);

        Db::commit();

        return true;

    }


    /**
     * @param $order_id
     * @param $car_price
     * @功能说明:获取该订单可以退的车费
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-12-06 14:51
     */
    public function canRefundOrderPrice($order_id){

        $order_model= new Order();

        $comm_model = new Commission();
        //查看佣金是否被结算
        $car_record = $comm_model->where(['order_id'=>$order_id,'status'=>2])->where('type','in',[8,13])->find();

        if(!empty($car_record)){

            return 0;
        }
        //车费
        $car_price = $order_model->where(['id'=>$order_id,'free_fare'=>0])->where('pay_time','>',0)->where('pay_type','<>',7)->sum('car_price');

        $car_price = $car_price>0?round($car_price,2):0;

        return $car_price;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-26 13:33
     * @功能说明:后台申请退款
     */
    public function applyRefundAdmin($order,$list,$car_price,$admin_user_id,$is_admin=1,$refund_empty_cash=0,$apply_empty_cash=0,$comm_balance=0){

        $order_goods_model = new OrderGoods();

        $refund_price = $material_price = $time_long = $apply_comm_cash = 0;

        Db::startTrans();

        if(!empty($list)){

            foreach ($list as $k=>$value){

                if(!empty($value['id'])){

                    $order_goods = $order_goods_model->dataInfo(['id'=>$value['id']]);

                    if(empty($order_goods)){

                        return ['code'=>500,'msg'=>'商品未找到'];
                    }
                    //商品未退完
                    if($order_goods['can_refund_num']>0){

                        $res = $order_goods_model->where(['id'=>$value['id']])->where('can_refund_num','>=',0)->update(['can_refund_num'=>$order_goods['can_refund_num']-$value['num']]);

                        if($res!=1){

                            Db::rollback();

                            return ['code'=>500,'msg'=>'申请失败'];

                        }

                        $time_long += $order_goods['time_long']*$value['num'];
                    }
                    //获取该子订单已经退款金额
                    $list_cash = $order_goods_model->getRefundCash($value['id']);
                    //退完
                    if($value['num']>=$order_goods['can_refund_num']){

                        if($value['true_price']>round($order_goods['true_price']*$order_goods['num']-$list_cash['total_service_price'],2)){

                            Db::rollback();

                            return ['code'=>500,'msg'=>'超出可退款服务费'];
                        }

                        if($value['material_price']>round($order_goods['material_price']*$order_goods['num']-$list_cash['total_material_price'],2)){

                            Db::rollback();

                            return ['code'=>500,'msg'=>'超出可退款'.getConfigSetting($this->_uniacid,'material_text')];
                        }
                    }else{

                        if(round($value['true_price']/$value['num'],2)>round($order_goods['true_price'],2)){

                            Db::rollback();

                            return ['code'=>500,'msg'=>'超出可退款服务费'];
                        }

                        if(round($value['material_price']/$value['num'],2)>round($order_goods['material_price'],2)){

                            Db::rollback();

                            return ['code'=>500,'msg'=>'超出可退款'.getConfigSetting($this->_uniacid,'material_text')];
                        }
                    }
                    //退款金额
                    $refund_price   += $value['true_price'];

                    $material_price += $value['material_price'];

                    $list[$k]['goods_id']    = $order_goods['goods_id'];

                    $list[$k]['goods_name']  = $order_goods['goods_name'];

                    $list[$k]['goods_cover'] = $order_goods['goods_cover'];
                }
            }
        }

        $arr['list'] = $list;

        $arr['refund_empty_cash'] = $refund_empty_cash;

        $arr['apply_empty_cash']  = $apply_empty_cash;
        //空单费|退款手续费
        $input = $this->emptyCashSetAdmin($arr,$refund_price,$material_price,$order['pay_type'],$order['is_add']);

        $list = $input['list'];
        //可以退款的车费
        $can_refund_car_price = $this->canRefundOrderPrice($order['id']);

        if($can_refund_car_price<$car_price){

            Db::rollback();

            return ['code'=>500,'msg'=>'退款车费大于可退款车费'];
        }

        $refund_price   = $refund_price-$input['comm_service_cash']-$input['empty_service_cash'];

        $material_price = $material_price-$input['comm_material_cash']-$input['empty_material_cash'];

        $refund_goods_model = new RefundOrderGoods();

        $insert = [

            'uniacid'    => $order['uniacid'],

            'user_id'    => $order['user_id'],

            'admin_id'   => $order['admin_id'],

            'partner_id' => $order['partner_id'],

            'time_long'  => $time_long,

            'order_code' => orderCode(),

            'coach_id'   => $order['coach_id'],

            'apply_price'=> round($refund_price+$car_price+$material_price,2),

            'service_price'=> $refund_price,

            'material_price'=> $material_price,

            'refund_service_price'=> $refund_price,

            'refund_material_price'=> $material_price,

            'order_id'   => $order['id'],

            'is_add'     => $order['is_add'],

            'car_price'  => $car_price,

            'refund_car_price'  => $car_price,

            'balance'    => !empty($order['balance'])?$refund_price:0,

            'is_admin_apply' => 1,

            'apply_comm_cash'    => $input['refund_comm_cash'],

            'refund_comm_cash'   => $input['refund_comm_cash'],

            'comm_service_cash'  => $input['comm_service_cash'],

            'comm_material_cash' => $input['comm_material_cash'],

            'empty_service_cash' => $input['empty_service_cash'],

            'empty_material_cash'=> $input['empty_material_cash'],

            'refund_empty_cash'  => $input['refund_empty_cash'],

            'comm_balance'       => $comm_balance,

            'apply_empty_cash'   => $apply_empty_cash,
        ];

        $res = $this->dataAdd($insert);

        if($res!=1){

            Db::rollback();

            return ['code'=>500,'msg'=>'申请失败'];
        }

        $refund_id = $this->getLastInsID();

        if(!empty($list)){

            foreach ($list as $value){

                $insert = [

                    'uniacid'        => $order['uniacid'],

                    'order_id'       => $order['id'],

                    'refund_id'      => $refund_id,

                    'order_goods_id' => $value['id'],

                    'goods_id'       => $value['goods_id'],

                    'goods_name'     => $value['goods_name'],

                    'goods_cover'    => $value['goods_cover'],

                    'num'            => $value['num'],

                    'goods_price'    => round($value['true_price']/$value['num'],2),

                    'material_price' => round($value['material_price']/$value['num'],2),

                    'status'         => 1,

                    'comm_service_cash'  => $value['comm_service_cash'],

                    'comm_material_cash' => $value['comm_material_cash'],

                    'refund_comm_cash'   => $value['refund_comm_cash'],

                    'apply_comm_cash'    => $value['refund_comm_cash'],

                    'empty_service_cash' => $value['empty_service_cash'],

                    'empty_material_cash'=> $value['empty_material_cash'],

                    'refund_empty_cash'  => $value['refund_empty_cash'],

                    'apply_empty_service_cash' => $value['apply_empty_service_cash'],

                    'apply_empty_material_cash'=> $value['apply_empty_material_cash'],

                    'apply_empty_cash'  => $value['apply_empty_cash'],
                ];

                $res = $refund_goods_model->dataAdd($insert);

                if($res!=1){

                    Db::rollback();

                    return ['code'=>500,'msg'=>'申请失败'];
                }
            }
        }

        $log_model = new OrderLog();
        //退款订单的操作日志
        $log_model->addLog($refund_id,$order['uniacid'],1,0,$is_admin,$admin_user_id,2);

        Db::commit();

        return $refund_id;

    }


    /**
     * @param $refund_order
     * @功能说明:获取审核人
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-12-16 11:51
     */
    public function checkUserName($data){

        if($data['check_user_mobile']==0){

            $admin_model = new \app\massage\model\Admin();

            $check_user_name = $admin_model->where(['id'=>$data['check_user']])->value('agent_name');

        }else{

            $user_model = new User();

            $check_user_name = $user_model->where(['id'=>$data['check_user']])->value('nickName');
        }

        return $check_user_name;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-02-04 10:17
     * @功能说明:获取订单退款金额
     */
    public function financeOrderRefundCash($uniacid,$start_time='',$end_time='',$date=0,$is_balance=0,$is_add=2,$is_cash=1,$user=[],$admin_arr=[],$filed='a.refund_price'){

        $dis[] = ['a.uniacid','=',$uniacid];

        $dis[] = ['a.status','=',2];

        if(!empty($start_time)&&!empty($end_time)){

            $dis[] = ['b.create_time','between',"$start_time,$end_time"];
        }

        if($is_add!=2){

            $dis[] = ['a.is_add','=',$is_add];
        }

        if($is_balance==1){

            $dis[] = ['b.pay_model','in',[2,4]];
        }

        if($is_balance==2){

            $dis[] = ['b.pay_model','in',[1,3]];
        }

        if(!empty($user)&&$user['is_admin']==0){

            $dis[] = ['b.admin_id','in',$admin_arr];
        }

        if(!empty($date)){

            if($is_cash==1){

                $refund_cash = $this->alias('a')
                    ->join('massage_service_order_list b','a.order_id = b.id')
                    ->whereDay('b.create_time',$date)
                    ->where($dis)
                    ->group('a.id')
                    ->sum($filed);
            }else{

                $refund_cash = $this->alias('a')
                    ->join('massage_service_order_list b','a.order_id = b.id')
                    ->whereDay('b.create_time',$date)
                    ->where($dis)
                    ->group('a.order_id')
                    ->count();
            }

        }else{

            if($is_cash==1){

                $refund_cash = $this->alias('a')
                    ->join('massage_service_order_list b','a.order_id = b.id')
                    ->where($dis)
                    ->group('a.id')
                    ->sum($filed);
            }else{

                $refund_cash = $this->alias('a')
                    ->join('massage_service_order_list b','a.order_id = b.id')
                    ->where($dis)
                    ->group('a.order_id')
                    ->count();
            }
        }

        return floatval($refund_cash);
    }


    /**
     * @param $order
     * @功能说明:技师据单
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-03-01 14:11
     */
    public function coachRefundOrder($order,$is_admin){

        $admin_model = new Admin();

        $check_user = $admin_model->where(['is_admin'=>1])->value('id');

        $order_code = orderCode();

        $insert = [

            'uniacid'    => $order['uniacid'],

            'user_id'    => $order['user_id'],

            'admin_id'   => $order['admin_id'],

            'partner_id' => $order['partner_id'],

            'time_long'  => $order['time_long'],

            'order_code' => $order_code,

            'coach_id'   => $order['coach_id'],

            'apply_price'=> $order['pay_price'],

            'refund_price'=> $order['pay_price'],

            'have_price'=> $order['pay_price'],

            'service_price'=> $order['service_price'],

            'material_price'=> $order['start_material_price']>=0?$order['start_material_price']:0,

            'refund_service_price'=> $order['service_price'],

            'refund_material_price'=> $order['start_material_price']>=0?$order['start_material_price']:0,

            'order_id'   => $order['id'],

            'is_add'     => $order['is_add'],

            'car_price'  => $order['free_fare']==0?$order['car_price']:0,

            'refund_car_price' => $order['free_fare']==0?$order['car_price']:0,

            'balance'    => !empty($order['balance'])?$order['pay_price']:0,

            'type'       => 2,

            'status'     => 2,

            'refund_time'=> $order['coach_refund_time'],

            'create_time'=> $order['create_time'],

            'refund_text'=> $order['coach_refund_text'],

            'version' => 2,

            'is_admin_apply' => $is_admin,

            'init' => 1,

            'check_user'  => $check_user,

            'check_user_mobile' => 0,
        ];

        $this->insert($insert);

        $id = $this->getLastInsID();

        $refund_goods_model = new RefundOrderGoods();

        if(!empty($order['order_goods'])){

            foreach ($order['order_goods'] as $value){

                $insert = [

                    'uniacid'        => $order['uniacid'],

                    'order_id'       => $order['id'],

                    'refund_id'      => $id,

                    'order_goods_id' => $value['id'],

                    'goods_id'       => $value['goods_id'],

                    'goods_name'     => $value['goods_name'],

                    'goods_cover'    => $value['goods_cover'],

                    'num'            => $value['num'],

                    'goods_price'    => round($value['true_price'],2),

                    'material_price' => round($value['material_price'],2),

                    'status'         => 1,
                ];

                $res = $refund_goods_model->dataAdd($insert);
            }
        }

        return $id;
    }


    /**
     * @param $uniacid
     * @功能说明:初始化退款订单的各类金额
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-04-01 18:53
     */
    public function initRefundOrderData($uniacid){

        $dis[] = ['refund_service_price','<',0];

        $dis[] = ['status','=',2];

        $dis[] = ['uniacid','=',$uniacid];

        $data = Db::name('massage_service_refund_order')->where($dis)->order('id desc')->select()->toArray();

        if(!empty($data)){

            foreach ($data as $key=>$refund_order){

                $coach_price = $material_price = 0;

                if($refund_order['version']==2){

                    $order_goods = Db::name('massage_service_refund_order_goods')->where(['refund_id'=>$refund_order['id']])->field('*,goods_price as refund_goods_price,material_price as refund_material_price')->select()->toArray();

                    foreach ($order_goods as $v){

                        $coach_price += $v['refund_goods_price']*$v['num'];

                        $material_price += $v['refund_material_price']*$v['num'];
                    }

                }else{
                    //服务费占总退款的比例(兼容以前的老版本)
                    $ser_bin = $refund_order['apply_price']>0?$refund_order['service_price']/$refund_order['apply_price']:0;
                    //扣除退款后的服务费
                    $coach_price = $refund_order['refund_price']*$ser_bin;

                    $coach_price = $coach_price>0?round($coach_price,2):0;

                    $m_bin = $refund_order['apply_price']>0?$refund_order['material_price']/$refund_order['apply_price']:0;

                    $material_price = $refund_order['refund_price']*$m_bin;
                }

                $update[$key] = [

                    'id' => $refund_order['id'],

                    'refund_service_price' => $coach_price,

                    'refund_material_price'=> $material_price>0?$material_price:0,

                    'init' => 1
                ];

                if($refund_order['refund_car_price']<0){

                    $update[$key]['refund_car_price'] = round($refund_order['refund_price']-$coach_price-$material_price,2);
                }
            }

            $this->saveAll($update);
        }

        return true;
    }


    /**
     * @author chenniang
     * @DataTime: 2024-07-23 17:26
     * @功能说明:空单费
     */
    public function emptyCashSet($input,$goods_price,$material_price,$pay_type,$is_add){

        $input['refund_empty_cash'] = isset($input['refund_empty_cash'])?$input['refund_empty_cash']:0;

        $input['apply_empty_cash']  = isset($input['apply_empty_cash'])?$input['apply_empty_cash']:0;

        if($input['refund_empty_cash']>0&&$is_add==1){

            return ['code'=>500,'msg'=>'加钟订单不能扣空单费'];
        }

        if($input['refund_empty_cash']>0&&$pay_type!=5){

            return ['code'=>500,'msg'=>'只有技师到达才能扣空单费'];
        }
        $refund_comm_cash = 0;

        $refund_goods_model = new RefundOrderGoods();

        foreach ($input['list'] as $value){

            $num = $refund_goods_model->where(['id'=>$value['id']])->sum('num');

            $value['refund_comm_cash'] = isset($value['refund_comm_cash'])?$value['refund_comm_cash']*$num:0;

            $refund_comm_cash+= $value['refund_comm_cash'];
        }
        //校验金额
        if($input['refund_empty_cash']+$refund_comm_cash>$goods_price+$material_price){

            return ['code'=>500,'msg'=>'空单费+退款服务费不能大于服务费+物料费'];
        }
        //有空单费
        $empty_bin       = ($goods_price+$material_price-$refund_comm_cash)>0?$input['refund_empty_cash']/($goods_price+$material_price-$refund_comm_cash):0;

        $apply_empty_bin = ($goods_price+$material_price-$refund_comm_cash)>0?$input['apply_empty_cash']/($goods_price+$material_price-$refund_comm_cash):0;

        $comm_service_cash = $comm_material_cash = $empty_service_cash = $empty_material_cash = 0;

        foreach ($input['list'] as &$value){

            $bin = $value['refund_comm_cash']>0?$value['refund_comm_cash']/($value['goods_price']+$value['material_price']):0;
            //退款数量
            $num = $refund_goods_model->where(['id'=>$value['id']])->sum('num');

            $value['refund_comm_cash'] = isset($value['refund_comm_cash'])?$value['refund_comm_cash']*$num:0;
            //退款手续费
            if($value['refund_comm_cash']>($value['goods_price']+$value['material_price'])*$num){

                return ['code'=>500,'msg'=>'退款手续费不能大于服务费+物料费'];
            }

            if($value['refund_comm_cash']>0&&$pay_type!=6){

                return ['code'=>500,'msg'=>'只有技师开发服务才能扣退款手续费'];
            }
            //手续费服务费扣了多少
            $value['comm_service_cash'] = $bin*$value['goods_price']*$num;
            //手续费物料费扣了多少
            $value['comm_material_cash']= $bin*$value['material_price']*$num;

            $value['goods_price']    = $value['goods_price']-$value['comm_service_cash']/$num;

            $value['material_price'] = $value['material_price']-$value['comm_material_cash']/$num;

            $comm_service_cash += $value['comm_service_cash'];

            $comm_material_cash+= $value['comm_material_cash'];
            //空单费服务费扣了多少
            $value['empty_service_cash'] = $empty_bin*$value['goods_price']*$num;
            //空单费物料费扣了多少
            $value['empty_material_cash']= $empty_bin*$value['material_price']*$num;
            //空单费服务费扣了多少
            $value['apply_empty_service_cash'] = $apply_empty_bin*$value['goods_price']*$num;
            //空单费物料费扣了多少
            $value['apply_empty_material_cash']= $apply_empty_bin*$value['material_price']*$num;

            $value['refund_empty_cash'] = round($value['empty_service_cash']+$value['empty_material_cash'],2);

            $value['apply_empty_cash']  = round($value['apply_empty_service_cash']+$value['apply_empty_material_cash'],2);
            //扣取空单费
            $value['goods_price']    = round($value['goods_price']-$value['empty_service_cash']/$num,2);

            $value['material_price'] = round($value['material_price']-$value['empty_material_cash']/$num,2);

            $empty_service_cash += $value['empty_service_cash'];

            $empty_material_cash+= $value['empty_material_cash'];
        }

        $input['empty_bin'] = $empty_bin;

        $input['refund_comm_cash']  = $refund_comm_cash;

        $input['comm_service_cash'] = $comm_service_cash;

        $input['comm_material_cash']= $comm_material_cash;

        $input['empty_service_cash'] = $empty_service_cash;

        $input['empty_material_cash']= $empty_material_cash;

        if(!empty($input['order_id'])){

            $update = [

                'refund_comm_cash'   => $refund_comm_cash,

                'refund_empty_cash'  => $input['refund_empty_cash'],

                'apply_empty_cash'   => $input['apply_empty_cash'],

                'comm_service_cash'  => $comm_service_cash,

                'comm_material_cash' => $comm_material_cash,

                'empty_service_cash' => $empty_service_cash,

                'empty_material_cash'=> $empty_material_cash,
            ];

            $this->dataUpdate(['id'=>$input['order_id']],$update);
        }

        return $input;
    }



    /**
     * @author chenniang
     * @DataTime: 2024-07-23 17:26
     * @功能说明:空单费
     */
    public function emptyCashSetAdmin($input,$goods_price,$material_price,$pay_type,$is_add){

        $input['refund_empty_cash'] = isset($input['refund_empty_cash'])?$input['refund_empty_cash']:0;

        $input['apply_empty_cash']  = isset($input['apply_empty_cash'])?$input['apply_empty_cash']:0;

        if($input['refund_empty_cash']>0&&$is_add==1){

            return ['code'=>500,'msg'=>'加钟订单不能扣空单费'];
        }

        if($input['refund_empty_cash']>0&&$pay_type!=5){

            return ['code'=>500,'msg'=>'只有技师到达才能扣空单费'];
        }

        $refund_comm_cash = 0;

        foreach ($input['list'] as $value){

            $value['refund_comm_cash'] = isset($value['refund_comm_cash'])?$value['refund_comm_cash']:0;

            $refund_comm_cash+= $value['refund_comm_cash'];
        }
        //校验金额
        if($input['refund_empty_cash']+$refund_comm_cash>$goods_price+$material_price){

            return ['code'=>500,'msg'=>'空单费+退款服务费不能大于服务费+物料费'];
        }
        //有空单费
        $empty_bin = ($goods_price+$material_price-$refund_comm_cash)>0?$input['refund_empty_cash']/($goods_price+$material_price-$refund_comm_cash):0;

        $apply_empty_bin = ($goods_price+$material_price-$refund_comm_cash)>0?$input['apply_empty_cash']/($goods_price+$material_price-$refund_comm_cash):0;

        $comm_service_cash = $comm_material_cash =  $empty_service_cash = $empty_material_cash =$apply_empty_service_cash = $apply_empty_material_cash = 0;

        foreach ($input['list'] as &$value){

            $value['refund_comm_cash'] = isset($value['refund_comm_cash'])?$value['refund_comm_cash']:0;
            //退款手续费
            if($value['refund_comm_cash']>$value['true_price']+$value['material_price']){

                return ['code'=>500,'msg'=>'退款手续费不能大于服务费+物料费'];
            }

            if($value['refund_comm_cash']>0&&$pay_type!=6){

                return ['code'=>500,'msg'=>'只有技师开发服务才能扣退款手续费'];
            }

            $bin = $value['refund_comm_cash']>0?$value['refund_comm_cash']/($value['true_price']+$value['material_price']):0;
            //手续费服务费扣了多少
            $value['comm_service_cash'] = $bin*$value['true_price'];
            //手续费物料费扣了多少
            $value['comm_material_cash']= $bin*$value['material_price'];

            $value['true_price']    = $value['true_price']-$value['comm_service_cash'];

            $value['material_price'] = $value['material_price']-$value['comm_material_cash'];

            $comm_service_cash += $value['comm_service_cash'];

            $comm_material_cash+= $value['comm_material_cash'];
            //空单费服务费扣了多少
            $value['empty_service_cash'] = $empty_bin*$value['true_price'];
            //空单费物料费扣了多少
            $value['empty_material_cash']= $empty_bin*$value['material_price'];
            //空单费服务费扣了多少
            $value['apply_empty_service_cash'] = $apply_empty_bin*$value['true_price'];
            //空单费物料费扣了多少
            $value['apply_empty_material_cash']= $apply_empty_bin*$value['material_price'];

            $value['refund_empty_cash'] = round($value['empty_service_cash']+$value['empty_material_cash'],2);

            $value['apply_empty_cash']  = round($value['apply_empty_service_cash']+$value['apply_empty_material_cash'],2);
            //扣取空单费
            $value['true_price']    = $value['true_price']-$value['empty_service_cash'];

            $value['material_price']= $value['material_price']-$value['empty_material_cash'];

            $empty_service_cash += $value['empty_service_cash'];

            $empty_material_cash+= $value['empty_material_cash'];

            $apply_empty_service_cash += $value['apply_empty_service_cash'];

            $apply_empty_material_cash+= $value['apply_empty_material_cash'];
        }


        $input['empty_bin']          = $empty_bin;

        $input['refund_comm_cash']   = $refund_comm_cash;

        $input['comm_service_cash']  = $comm_service_cash;

        $input['comm_material_cash'] = $comm_material_cash;

        $input['empty_service_cash'] = $empty_service_cash;

        $input['empty_material_cash']= $empty_material_cash;

        $input['apply_empty_service_cash'] = $apply_empty_service_cash;

        $input['apply_empty_material_cash']= $apply_empty_material_cash;

        return $input;
    }


    /**
     * @author chenniang
     * @DataTime: 2024-08-15 14:34
     * @功能说明:订单退款中也算完成
     */
    public function refundEndOrder($order_id){

        $order_goods_model = new OrderGoods();

        $dis = [

            'order_id' => $order_id,

            'status'   => 1
        ];

        $list = $order_goods_model->where($dis)->field('id as order_goods_id,num')->select()->toArray();

        $res = 1;

        if(!empty($list)){

            foreach ($list as $value){

                $where['b.order_goods_id'] = $value['order_goods_id'];

                $refund_num = $this->alias('a')
                              ->join('massage_service_refund_order_goods b','a.id = b.refund_id')
                              ->where($where)
                              ->where('a.status','in',[2,4,5])
                              ->group('b.id')
                              ->sum('b.num');

                if($value['num']>$refund_num){

                    $res = 0;
                }
            }
        }

        return $res;
    }






}