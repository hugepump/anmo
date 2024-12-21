<?php
namespace app\massage\model;

use app\adapay\model\Member;
use app\BaseModel;
use think\facade\Db;

class CoachChangeLog extends BaseModel
{
    //定义表名
    protected $name = 'massage_order_coach_change_logs';




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
     * @param $order
     * @param array $arr
     * @功能说明:给技师车费
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-02-26 16:52
     */
    public function giveCarPrice($order,$arr = [5,6,7]){

        $comm_model = new Commission();

        $car_record = $comm_model->where(['order_id'=>$order['id'],'status'=>1])->where('type','in',[8,13])->find();

        $give_car = 0;

        if(in_array($order['pay_type'],$arr)&&!empty($car_record)){

            $member_model= new Member();

            $admin_model = new Admin();

            $coach_water_model = new CoachWater();

            $admin_water_model = new AdminWater();

            $car_cash = $car_record['cash'];

            $admin = $admin_model->dataInfo(['id'=>$order['admin_id']]);

            if($car_record['type']==8){

                if(!empty($car_record['top_id'])){

                    $res = $coach_water_model->updateCash($order['uniacid'],$car_record['top_id'],$car_cash,1,2);

                    if($res==0){

                        return ['code'=>500,'msg'=>'转派失败，请从试'];
                    }

                }else{
                   //线下技师结算给代理商
                    if(!empty($order['admin_id'])){

                        $update = [

                            'top_id' => $order['admin_id'],

                            'type'   => 13,

                            'city_type'=> $admin['city_type'],
                        ];

                        $comm_model->dataUpdate(['id'=>$car_record['id']],$update);

                        $car_record = $comm_model->dataInfo(['id'=>$car_record['id']]);
                    }
                }
            }
            //车费给代理商
            if ($car_record['type']==13){

                $res = $admin_water_model->updateCash($order['uniacid'],$car_record['top_id'],$car_cash,1);

                if($res==0){

                    return ['code'=>500,'msg'=>'转派失败，请从试'];
                }
            }

            $res = $comm_model->dataUpdate(['id'=>$car_record['id']],['status'=>2,'cash_time'=>time()]);

            if($res==0){

                return ['code'=>500,'msg'=>'转派失败，请从试'];
            }

            $admin_share_car = $comm_model->where(['order_id'=>$order['id'],'status'=>1,'type'=>23])->find();

            if(!empty($admin_share_car)){

                $admin_share_car = $admin_share_car->toArray();

                $res = $comm_model->where(['id'=>$admin_share_car['id']])->update(['status'=>2,'cash_time'=>time()]);

                if($res==0){

                    Db::rollback();

                    return ['code'=>500,'msg'=>'转派失败，请从试'];
                }
                //代理商车费，注意是扣除
                $record_model = new CashUpdateRecord();

                $res = $record_model->totalUpdateCash($order['uniacid'],3,$admin_share_car['top_id'],$admin_share_car['cash'],0,'',1,9,$admin_share_car['id']);

                if(!empty($res['code'])){

                    Db::rollback();

                    return ['code'=>500,'msg'=>'转派失败，请从试'];
                }
            }

            $member_model->adapayCommission($car_record,$car_record['order_id']);

            $give_car = 1;
        }

        return $give_car;
    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-24 11:37
     * @功能说明:更换技师时候计算车费
     */
    public function giveCarPriceV2($order,$arr = [5,6,7]){

        $comm_model = new Commission();

        $car_record = $comm_model->where(['order_id'=>$order['id'],'status'=>2])->where('type','in',[8,13])->find();
        //当前次是否给了车费
        $give_car = 0;
        //如果技师出发需要给技师车费
        if(in_array($order['pay_type'],$arr)&&$order['true_car_price']>0&&empty($car_record)&&in_array($order['free_fare'],[0,1])){

            $coach_model = new Coach();

            $member_model= new Member();

            $admin_model = new Admin();

            $car_admin = $comm_model->dataInfo(['order_id'=>$order['id'],'status'=>1,'type'=>13]);

            $car_cash = $order['true_car_price'];

            if(empty($car_admin)){
                //加车费记录
                $insert = [

                    'uniacid' => $order['uniacid'],

                    'user_id' => $order['user_id'],

                    'top_id'  => $order['coach_id'],

                    'order_id'=> $order['id'],

                    'order_code' => $order['order_code'],

                    'admin_id'=> $order['admin_id'],

                    'type'    => 8,

                    'cash'    => $car_cash,

                    'status'  => 2,

                    'cash_status' => 0

                ];

                $comm_model->dataAdd($insert);

                if(!empty($order['coach_id'])){

                    $id = $comm_model->getLastInsID();

                    $res = $coach_model->where(['id'=>$order['coach_id']])->update(['car_price'=>Db::raw("car_price+$car_cash")]);

                    if($res==0){

                        return ['code'=>500,'msg'=>'转派失败，请从试'];
                    }

                    $comm = $comm_model->dataInfo(['id'=>$id]);

                    $member_model->adapayCommission($comm,$comm['order_id']);
                }

            }
            //车费给代理商或者线下技师
            if((!empty($car_admin)||empty($order['coach_id']))&&!empty($order['admin_id'])){

                $admin = $admin_model->dataInfo(['id'=>$order['admin_id']]);

                $insert = [

                    'uniacid' => $order['uniacid'],

                    'user_id' => $order['user_id'],

                    'top_id'  => $order['admin_id'],

                    'order_id'=> $order['id'],

                    'order_code' => $order['order_code'],

                    'type'    => 13,

                    'cash'    => $car_cash,

                    'admin_id'=> $order['admin_id'],

                    'balance' => 0,

                    'status'  => 2,

                    'city_type'=> $admin['city_type'],

                ];

                $comm_model->dataAdd($insert);

                $id = $comm_model->getLastInsID();

                $res = $admin_model->where(['id'=>$order['admin_id'],'cash'=>$admin['cash']])->update(['cash'=>Db::raw("cash+$car_cash")]);

                if($res==0){

                    return ['code'=>500,'msg'=>'转派失败，请从试'];
                }

                $comm = $comm_model->dataInfo(['id'=>$id]);

                $member_model->adapayCommission($comm,$comm['order_id']);

            }
            //
//            if(!empty($car_cash)){
//
//                $price_log_model = new OrderPrice();
//
//                $price_log_model->where(['order_id'=>$order['id']])->update(['can_refund_price'=>Db::Raw("can_refund_price-$car_cash")]);
//            }

            $give_car = 1;
        }

        return $give_car;
    }


    /**
     * @param $uniacid
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-08 11:36
     */
    public function initData($uniacid){

        $this->dataUpdate(['uniacid'=>$uniacid],['is_new'=>0]);

        $data = $this->where(['uniacid'=>$uniacid])->group('order_id')->order('id desc')->select()->toArray();

        if(!empty($data)){

            foreach ($data as $v){

                $find = $this->where(['order_id'=>$v['order_id']])->order('id desc')->find();

                $this->dataUpdate(['id'=>$find->id],['is_new'=>1]);
            }
        }


    }

    /**
     * @param $order
     * @param $coach_id
     * @param $coach_name
     * @功能说明:添加转发记录
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-24 11:42
     */
    public function addLog($order,$coach_id,$coach_name,$init_coach_id,$text,$phone,$have_car_price,$control_id=1,$control_type=1){

        $dis = [

            'status' => 1,

            'order_id' => $order['id']
        ];

        $this->dataUpdate($dis,['is_new'=>0]);
        //原来的订单没有技师
        if($order['coach_id']==0){

            $old_coach_name = $this->where($dis)->order('id desc')->value('now_coach_name');

            $old_coach_mobile = $this->where($dis)->order('id desc')->value('now_coach_mobile');
        }

        $insert = [

            'uniacid'       => $order['uniacid'],

            'old_coach_id'  => $order['coach_id'],

            'pay_type'      => $order['pay_type'],

            'is_new'        => 1,

            'text'          => $text,

            'now_coach_id'  => $coach_id,

            'old_coach_name'=> !empty($old_coach_name)?$old_coach_name:'',

            'old_coach_mobile'=> !empty($old_coach_mobile)?$old_coach_mobile:'',

            'now_coach_name'=> $coach_name,

            'now_coach_mobile'=> $phone,

            'car_price'     => $order['true_car_price'],

            'order_id'      => $order['id'],

            'start_time'    => $order['start_time'],

            'end_time'      => $order['end_time'],

            'free_fare'     => $order['free_fare'],

            'init_coach_id' => $init_coach_id,

            'have_car_price'=> $have_car_price,

            'control_id'    => $control_id,

            'control_type'  => $control_type,
        ];
        //添加日志
        $res = $this->dataAdd($insert);

        return $res;

    }


    /**
     * @param $time_long
     * @param $order_id
     * @功能说明:更改加钟订单的时间
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-27 11:54
     */
    public function changeAddOrderTime($order,$time_long,$coach_id){

        $order_model = new Order();

        $add_order = $order_model->where(['add_pid'=>$order['id'],'pay_type'=>1])->select()->toArray();

        if(!empty($add_order)){

            foreach ($add_order as $value){

                $order_model->cancelOrder($value);
            }
        }

        $where[] = ['pay_type','>',1];

        $where[] = ['add_pid','=',$order['id']];

        $where[] = ['is_add','=',1];

        $order_list = $order_model->where($where)->select()->toArray();

        if(!empty($order_list)){

            foreach ($order_list as $value){

                $update['coach_id']   = $coach_id;

                $update['start_time'] = $value['start_time'] + $time_long;

                $update['end_time']   = $value['end_time'] + $time_long;

                if($value['type']!=7){

                    $update['pay_type']  = 3;
                }

                $update['admin_id']   = 0;

                $update['partner_id'] = 0;

                $update['receiving_time'] = time();

                $order_model->dataUpdate(['id'=>$value['id']],$update);

                if($value['type']!=7){
                    //重新结算佣金
                    $this->getCashData($value,$coach_id,$order['admin_id']);
                }
            }
        }

        return true;

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-24 11:50
     * @功能说明:修改订单信息
     */
    public function updateOrderInfo($order,$coach_id,$have_car_price,$start_time,$control_id,$admin_id,$control_type=1){

        $update = [

            'coach_id' => $coach_id,

            'pay_type' => 3,

            'receiving_time' => time(),

            'start_time' => $start_time,

            'admin_id'   => 0,

            'partner_id' => 0,

            'end_time'   => $start_time+$order['true_time_long']*60

        ];

        $order_model = new Order();

        if(!empty($coach_id)){
            //修改订单信息
            $res = $order_model->where(['id'=>$order['id']])->where('coach_id','<>',$coach_id)->update($update);
        }else{
            //修改订单信息
            $res = $order_model->dataUpdate(['id'=>$order['id']],$update);
        }

        $time_long = $update['start_time'] - $order['start_time'];

        $order['admin_id'] = $admin_id;
        //修改加单的信息
        $this->changeAddOrderTime($order,$time_long,$coach_id);

        $log_model = new OrderLog();

        $is_admin  = $control_type==1?1:4;

        $log_model->addLog($order['id'],$order['uniacid'],3,$order['pay_type'],$is_admin,$control_id);

        return $res;
    }



    /**
     * @param $order
     * @param $coach_id
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-27 14:40
     */
    public function getCashData($order,$coach_id,$admin_id){

        $order_model = new Order();

        $comm_model  = new Commission();

        $share_model = new CommShare();

        $channel_model= new UserChannel();

        $comm_id = $comm_model->where(['order_id'=>$order['id'],'status'=>2])->column('id');

        $share_model->where(['order_id'=>$order['id']])->where('comm_id','not in',$comm_id)->delete();
        //清空以前所有的佣金
        $comm_model->where(['order_id'=>$order['id']])->where('status','<>',2)->delete();

        $order['coach_id'] = $coach_id;

        $channel_record = $channel_model->getChannelId($order['user_id']);

        $order['channel_id']    = !empty($channel_record)?$channel_record['channel_id']:0;

        $order['channel_qr_id'] = !empty($channel_record)?$channel_record['channel_qr_id']:0;
        //结算新的佣金信息
        $res = $order_model->getCashData($order,1,$admin_id);

        if(!empty($res['code'])){

            return $res;
        }
        //修改订单信息
        $order_model->dataUpdate(['id'=>$order['id']],$res['order_data']);
        //将分销记录打开
        $comm_model->dataUpdate(['order_id'=>$order['id'],'status'=>-1],['status'=>1]);
        //结算平台利润
        $order_model = new Order();

        $company_cash = $order_model->companySurplusCash($order['id']);

        $comm_model->dataUpdate(['type'=>16,'order_id'=>$order['id']],['company_cash'=>$company_cash]);
        //修改通知
        if(isset($res['order_data']['admin_id'])){

            $this->updateNotice($order['id'],$res['order_data']['admin_id']);
        }

        return $res;

    }


    /**
     * @param $order_id
     * @param $admin_id
     * @功能说明:处理代理商订单通知
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-14 10:24
     */
    public function updateNotice($order_id,$admin_id){

        $notice_model = new NoticeList();

        $refund_model = new RefundOrder();

        $notice_model->dataUpdate(['order_id'=>$order_id,'type'=>1],['admin_id'=>$admin_id]);

        $refund_id = $refund_model->where(['order_id'=>$order_id])->column('id');

        $notice_model->where(['type'=>2])->where('order_id','in',$refund_id)->update(['admin_id'=>$admin_id]);

        return true;
    }


    /**
     * @param $time_long
     * @param $order_id
     * @功能说明:校验加钟订单
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-27 11:13
     */
    public function checkAddOrderTime($order,$start_time,$coach_id){

        $order_model = new Order();

        $where[] = ['pay_type','not in',[-1]];

        $where[] = ['add_pid','=',$order['id']];

        $where[] = ['is_add','=',1];

        $order_list = $order_model->where($where)->field('id,coach_id,start_time,end_time,order_end_time,pay_type,add_pid')->select()->toArray();

        if(!empty($order_list)){

            foreach ($order_list as $value){

                $value['coach_id'] = $coach_id;

                $start_time = $start_time-$order['start_time']+$value['start_time'] ;
                //校验时间
                $check = $order_model->checkTime($value,$start_time,$value['add_pid']);

                if(!empty($check['code'])){

                    return $check;

                }

            }

        }

        return true;

    }

    public function checkAddOrderTimeV2($order,$order_list,$start_time,$coach_id){

        $order_model = new Order();

        if(!empty($order_list)){

            foreach ($order_list as $value){

                $value['coach_id'] = $coach_id;

                $start_time = $start_time-$order['start_time']+$value['start_time'] ;
                //校验时间
                $check = $order_model->checkTime($value,$start_time,$value['add_pid']);

                if(!empty($check['code'])){

                    return $check;

                }
            }
        }

        return true;

    }

    /**
     * @param $order
     * @功能说明:校验时间
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-28 17:22
     */
    public function checkTime($order,$coach_id){

        $config_model = new Config();

        $order_model  = new Order();

        $config = $config_model->dataInfo(['uniacid' => $order['uniacid']]);

        $start_time = $order['start_time']>time()+$config['time_interval']?$order['start_time']:time()+$config['time_interval'];

        if($coach_id!=0){

            $order['coach_id'] = $coach_id;

            $coach_model = new Coach();

            $coach = $coach_model->dataInfo(['id'=>$coach_id]);
            //获取该技师最近的服务时间
            $start_time = $this->getCoachNearTime($order,$coach);

            if(empty($start_time)){

                return ['code'=>500,'msg'=>'不符合该订单时间要求，请刷新重试'];
            }
            //校验主订单
            $check = $order_model->checkTime($order,$start_time);

            if(!empty($check['code'])){

                return $check;
            }
            //校验加钟订单
            $check = $this->checkAddOrderTime($order,$start_time,$coach_id);

            if(!empty($check['code'])){

                return $check;
            }

        }

        return $start_time;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-22 11:33
     * @功能说明:订单更换技师
     */
    public function orderChangeCoach($order,$coach_id,$control_id,$admin_id,$coach_name='',$text='',$phone='',$control_type=1){

        if($order['pay_type']<1){

            return ['code'=>500,'msg'=>'订单未付款，无法转派'];
        }

        if($order['pay_type']==7){

            return ['code'=>500,'msg'=>'订单已结束，无法转派'];
        }

        $init_coach_id = $this->where(['order_id'=>$order['id']])->value('init_coach_id');

        $init_coach_id = !empty($init_coach_id)?$init_coach_id:$order['coach_id'];

        Db::startTrans();
        //给技师结算车费
        $have_car_price = $this->giveCarPrice($order);

        if(!empty($have_car_price['code'])){

            Db::rollback();

            return $have_car_price;
        };
        //添加转发日志
        $res = $this->addLog($order,$coach_id,$coach_name,$init_coach_id,$text,$phone,$have_car_price,$control_id,$control_type);

        if($res==0){

            Db::rollback();

            return ['code'=>500,'msg'=>'转派失败，请重试'];
        }
        //校验时间
        $start_time = $this->checkTime($order,$coach_id);

        if(!empty($start_time['code'])){

            return $start_time;
        }
        //修改订单信息
        $res = $this->updateOrderInfo($order,$coach_id,$have_car_price,$start_time,$control_id,$admin_id,$control_type);

        if($res==0){

            Db::rollback();

            return ['code'=>500,'msg'=>'转派失败，请重试1'];
        }

        $order['true_car_price'] = $have_car_price==1?0:$order['car_price'];

        $change_record = $this->where(['order_id'=>$order['id']])->order('id')->find();

        if(!empty($change_record)&&$change_record->free_fare!=-1){

            $order['free_fare'] = $change_record->free_fare;
        }
        //重新结算佣金
        $res = $this->getCashData($order,$coach_id,$admin_id);

        if(!empty($res['code'])&&$res['code']==300){

            Db::rollback();

            return ['code'=>500,'msg'=>'请添加技师等级'];
        }

        if($res==0){

            Db::rollback();

            return ['code'=>500,'msg'=>'转派失败，请重试2'];
        }

        Db::commit();

        $order_model = new Order();

        $order = $order_model->dataInfo(['id'=>$order['id']]);

        if(!empty($phone)){

            $config_model = new ShortCodeConfig();

            $config_model->sendSms($phone, $order['uniacid'], $order['order_code'], 1);

        }else{

            $coach_model = new Coach();
            //发送通知
            $coach_model->paySendMsg($order);
        }

        if(!empty($admin_id)){

            $order['admin_id'] = $admin_id;
        }
        //语音电话通知
        $call_model = new \app\reminder\model\Config();

        $call_model->sendCalled($order,$phone);
        //打印
        $print_model = new Printer();

        $print_model->printer($order['id'],0);

        return $res;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-28 17:35
     * @功能说明:订单转派记录
     */
    public function orderChangeLog($order_id){

        $dis = [

            'order_id' => $order_id
        ];

        $data = $this->where($dis)->select()->toArray();

        $coach_model = new Coach();

        if(!empty($data)){

            foreach ($data as &$v){

                if($v['old_coach_id']!=0){

                    $v['old_coach_name'] = $coach_model->where(['id'=>$v['old_coach_id']])->value('coach_name');

                    $v['old_coach_img']  = $coach_model->where(['id'=>$v['old_coach_id']])->value('work_img');

                    $v['old_coach_mobile']  = $coach_model->where(['id'=>$v['old_coach_id']])->value('mobile');
                }else{

                    $v['old_coach_img'] = defaultCoachAvatar();
                }

                if($v['now_coach_id']!=0){

                    $v['now_coach_name'] = $coach_model->where(['id'=>$v['now_coach_id']])->value('coach_name');

                    $v['now_coach_img']  = $coach_model->where(['id'=>$v['now_coach_id']])->value('work_img');

                    $v['now_coach_mobile']  = $coach_model->where(['id'=>$v['now_coach_id']])->value('mobile');

                }else{

                    $v['now_coach_img'] = defaultCoachAvatar();
                }

            }
        }

        return $data;

    }





    /**
     * @param $order
     * @param $start_time
     * @param int $order_id 加钟订单
     * @param int $p_order_id 升级订单
     * @功能说明:转派技师时候 获取满足条件的技师 并获取最近的服务时间
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-02 11:56
     */
    public function getNearTimeCoach($order,$list){

        $coach_model   = new Coach();

        $order_model   = new Order();

        $config_model = new Config();

        $total_long = $order['true_time_long']*60;

        $add_where[] = ['pay_type','not in',[-1]];

        $add_where[] = ['add_pid','=',$order['id']];

        $add_where[] = ['is_add','=',1];

        $add_time_long = $order_model->where($add_where)->sum('true_time_long');

        $total_long+= $add_time_long*60;

        $arr = [];

        $config = $config_model->dataInfo(['uniacid' => $order['uniacid']]);

        $start_time = $order['start_time']>time()?$order['start_time']:time();
        //目前只获取往后24小时
        $order_end_time = $start_time+86400;
        //检查该时间段是否被预约
        $where[] = ['pay_type','not in',[-1,7]];

        $where[] = ['end_time','>',time()];

        $where[] = ['id','<>',$order['id']];

        $order_list = Db::name('massage_service_order_list')->where($where)->field('id,start_time,end_time,order_end_time,pay_type,coach_id')->select()->toArray();

        $time_interval = $config['time_interval']>0?$config['time_interval']*60-1:0;

        foreach ($list as $coach){

            if(empty($coach['start_time'])){

                continue;
            }
            //校验技师休息时间
            $rest_arr = $coach_model->getCoachRestTime($coach,$start_time,$order_end_time,$config);

            $i = 0;

            $work_start_time = $start_time;

            while ($work_start_time<$order_end_time){

                $work_start_time = $start_time + $i*60;

                $end_time = $total_long+$work_start_time;

                $i++;

                if($work_start_time-$time_interval<=time()){

                    continue;
                }
                //校验技师服务时间
                $res = $order_model->checkCoachTime($coach,$work_start_time,$end_time);

                if(!empty($res['code'])){

                    continue;
                }
                //校验技师休息时间
                $res = $order_model->checkCoachRestTime($rest_arr,$work_start_time,$end_time);

                if(!empty($res['code'])){

                    continue;
                }
                //校验技师订单时间
                $res = $order_model->checkOrderTime($order_list,$config,$work_start_time,$end_time,$coach['id']);

                if(!empty($res['code'])){

                    continue;
                }
//                //校验加钟订单
//                $check = $this->checkAddOrderTimeV2($order,$add_order,$work_start_time,$coach['id']);
//
//                if(!empty($check['code'])){
//
//                    continue;
//                }

                $arr[] = $coach['id'];

                $coach_model->dataUpdate(['id'=>$coach['id']],['near_time'=>$work_start_time]);

                break;
            }
        }

        return $arr;

    }



    /**
     * @param $order
     * @param $start_time
     * @param int $order_id 加钟订单
     * @param int $p_order_id 升级订单
     * @功能说明:转派技师时候 获取满足条件的技师 并获取最近的服务时间
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-02 11:56
     */
    public function getCoachNearTime($order,$coach){

        $service_model = new Service();

        $coach_model   = new Coach();

        $order_model   = new Order();

        $total_long = 0;

        $config_model = new Config();

        foreach ($order['order_goods'] as $v){

            $time_long = $service_model->where(['id'=>$v['service_id']])->value('time_long');

            $total_long += $time_long*$v['num'];

        }

        $total_long = $total_long*60;

        $config = $config_model->dataInfo(['uniacid' => $order['uniacid']]);

        $start_time = $order['start_time']>time()+$config['time_interval']*60?$order['start_time']:time()+$config['time_interval']*60;
        //目前只获取往后24小时
        $order_end_time = $start_time+86400;
        //检查该时间段是否被预约
        $where[] = ['pay_type','not in',[-1,7]];

        $where[] = ['end_time','>',time()];

        $order_list = $order_model->where($where)->field('id,start_time,end_time,order_end_time,pay_type,coach_id')->select()->toArray();
        //校验技师休息时间
        $rest_arr = $coach_model->getCoachRestTime($coach,$start_time,$order_end_time,$config);

        $i = 0;

        $order_start_time = $start_time;

        while ($order_start_time<$order_end_time){

            $order_start_time = $start_time + $i * 60;

            $end_time = $total_long+$order_start_time;

            $i++;
            //校验技师时间
            $res = $order_model->checkCoachTime($coach,$order_start_time,$end_time);

            if(!empty($res['code'])){

                continue;
            }
            //校验技师时间
            $res = $order_model->checkOrderTime($order_list,$config,$order_start_time,$end_time,$coach['id']);

            if(!empty($res['code'])){

                continue;
            }
            //校验加钟订单
            $check = $this->checkAddOrderTime($order,$order_start_time,$coach['id']);

            if(!empty($check['code'])){

                continue;
            }
            //校验技师休息时间
            $res = $order_model->checkCoachRestTime($rest_arr,$order_start_time,$end_time);

            if(!empty($res['code'])){

                continue;
            }

           return $order_start_time;

        }

        return false;

    }


    /**
     * @param $order_id
     * @功能说明:根据订单获取技师名字
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-30 11:15
     */
    public function getCoachByOrder($order_id){

        $dis = [

            'a.id' => $order_id,

            'b.is_new' => 1
        ];

        $order_model = new Order();

        $data = $order_model->alias('a')
                ->join('massage_order_coach_change_logs b','a.id = b.order_id OR a.add_pid = b.order_id')
                ->where($dis)
                ->value('now_coach_name');

        return $data;
    }











}