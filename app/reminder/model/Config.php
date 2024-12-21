<?php
namespace app\reminder\model;

use AlibabaCloud\Client\AlibabaCloud;
use app\BaseModel;
use app\massage\model\Admin;
use app\massage\model\Coach;
use app\massage\model\HelpConfig;
use app\massage\model\Order;
use app\massage\model\RefundOrder;
use app\massage\model\ShortCodeConfig;
use app\massage\model\User;
use app\reminder\info\PermissionReminder;
use app\virtual\info\PermissionVirtual;
use Exception;
use longbingcore\wxcore\aliyun;
use longbingcore\wxcore\aliyunVirtual;
use longbingcore\wxcore\Moor;
use longbingcore\wxcore\Winnerlook;
use think\facade\Db;

class Config extends BaseModel
{
    //定义表名
    protected $name = 'massage_aliyun_phone_config';


    /**
     * @param $value
     * @param $data
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-12-15 10:58
     */
    public function getReminderAdminPhoneAttr($value,$data){

        if(isset($value)){

            return !empty($value)?explode(',',$value):'';
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

        $data = $this->where($dis)->find();

        if(empty($data)){

            $this->dataAdd($dis);

            $data = $this->where($dis)->find();

        }

        return !empty($data)?$data->toArray():[];

    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-12-09 10:04
     * @功能说明:发送语音
     */
    public function sendCalled($order,$phone=0){

        $p = new PermissionReminder($order['uniacid']);

        $auth = $p->pAuth();

        if($auth==false){

            return true;
        }

        $config_model = new Config();

        $config = $config_model->dataInfo(['uniacid'=>$order['uniacid']]);

        if($config['reminder_status']==0){

            return false;
        }

        $aliyun = new aliyun();

        $coach_model = new Coach();

        $record_model = new Record();

        $config_model = new ShortCodeConfig();

        $moor = new Moor($order['uniacid']);

        $winner_model = new Winnerlook($order['uniacid']);

        if(!empty($order['coach_id'])){

            $phone = $coach_model->where(['id'=>$order['coach_id']])->value('mobile');
        }

        if($config['reminder_type']==1){
            //发送语音通知
            $res = $aliyun::main($order['uniacid'],$phone);

        }elseif($config['reminder_type']==2){

            $res = $moor->webCall($phone);
        }else{

            $res = $winner_model->webCall($phone);
        }
        //添加发送记录
        $insert  = [

            'uniacid' => $order['uniacid'],

            'order_id'=> $order['id'],

            'res'     => json_encode($res)

        ];

        $record_model->dataAdd($insert);
        //如果技师有代理商是否通知平台
        $notice_admin = $config['notice_admin']==1||$config['notice_admin']==0&&empty($order['admin_id'])?1:0;
        //给管理员发通知
        if($notice_admin==1&&!empty($config['reminder_admin_status'])&&!empty($config['reminder_admin_phone'])&&is_array($config['reminder_admin_phone'])){

            foreach ($config['reminder_admin_phone'] as $value){

                if($config['reminder_type']==1){
                    //发送语音通知
                    $res = $aliyun::main($order['uniacid'],$value);

                }elseif($config['reminder_type']==2){

                    $res = $moor->webCall($value);

                }else{

                    $res = $winner_model->webCall($value);
                }
                //如果被限流 就发短信
                if(!empty($res['code'])&&($res['code']=='isv.BUSINESS_LIMIT_CONTROL'||$res['code']==402)){

                     $config_model->sendSms($value, $order['uniacid'], $order['order_code'], 1);
                }
            }
        }
        //给代理商发通知
        if(!empty($order['admin_id'])&&!empty($config['notice_agent'])&&$config['notice_agent']==1){

            $admin_model = new Admin();

            $phone = $admin_model->where(['id'=>$order['admin_id']])->value('phone');

            if(!empty($phone)){

                if($config['reminder_type']==1) {
                    //发送语音通知
                    $res = $aliyun::main($order['uniacid'], $phone);

                }elseif($config['reminder_type']==2){

                    $res = $moor->webCall($phone);
                }else{

                    $res = $winner_model->webCall($phone);
                }
                //如果被限流 就发短信
                if(!empty($res['code'])&&($res['code']=='isv.BUSINESS_LIMIT_CONTROL'||$res['code']==402)){

                    $config_model->sendSms($phone, $order['uniacid'], $order['order_code'], 1);
                }
            }
        }

        return $res;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-08 10:06
     * @功能说明:发送通知
     */
    public function sendPhoneNotice($order,$phone,$admin_id=0){

        $uniacid = $order['uniacid'];

        $p = new PermissionReminder($uniacid);

        $auth = $p->pAuth();

        if($auth==false){

            return true;
        }

        $config_model = new Config();

        $config = $config_model->dataInfo(['uniacid'=>$uniacid]);

        if($config['reminder_status']==0){

            return false;
        }

        $aliyun = new aliyun();

        $short_config_model = new ShortCodeConfig();

        $res = $aliyun::main($uniacid,$phone);
        //给代理商发通知
        if($config['notice_agent']==1&&!empty($admin_id)){

            $admin_model = new Admin();

            $phone = $admin_model->where(['id'=>$order['admin_id']])->value('phone');

            if(!empty($phone)){
                //发送语音通知
                $res = $aliyun::main($order['uniacid'],$phone);
                //如果被限流 就发短信
                if($res['code']=='isv.BUSINESS_LIMIT_CONTROL'){

                    $short_config_model->sendSms($phone, $order['uniacid'], $order['order_code'], 1);
                }
            }
        }

        return $res;

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-12-09 11:35
     * @功能说明:定时任务给未截单的技师发送语音通知
     */
    public function timingSendCalled($uniacid){

        $p = new PermissionReminder($uniacid);

        $auth = $p->pAuth();

        if($auth==false){

            return true;
        }

        $config_model = new Config();

        $config = $config_model->dataInfo(['uniacid'=>$uniacid]);
        //未开启语音通知 或未开启定时任务
        if($config['reminder_status']==0||$config['reminder_timing']==0){

            return false;
        }

        $order_model = new Order();

        $dis = [

            'uniacid' => $uniacid,
            //未接单单
            'pay_type'=> 2
        ];

        $order = $order_model->where($dis)->where('pay_time','<',time()-$config['reminder_timing']*60)->order('id desc')->limit(20)->select()->toArray();

        $record_model = new Record();

        $aliyun = new aliyun();

        $coach_model = new Coach();

        $refund_model = new RefundOrder();

        $winner_model = new Winnerlook($uniacid);

        if(!empty($order)){

            $moor = new Moor($uniacid);

            foreach ($order as $value){
                //判断有无申请中的退款订单
                $refund_order = $refund_model->dataInfo(['order_id' => $value['id'], 'status' => 1]);

                $dis = [

                    'order_id' => $value['id'],

                    'uniacid'  => $value['uniacid'],

                    'type'     => 1
                ];

                $find = $record_model->where($dis)->where('create_time','>',time()-$config['reminder_timing']*60)->find();
                //如果没有发送过通知
                if(empty($find)&&empty($refund_order)){

                    $phone = $coach_model->where(['id'=>$value['coach_id']])->value('mobile');

                    if($config['reminder_type']==1){
                        //发送语音通知
                        $res = $aliyun::main($uniacid,$phone);

                    }elseif($config['reminder_type']==2){

                        $res = $moor->webCall($phone);

                    }else{

                        $res = $winner_model->webCall($phone);
                    }

                    $insert  = [

                        'uniacid' => $value['uniacid'],

                        'order_id'=> $value['id'],

                        'res'     => json_encode($res)

                    ];

                    $record_model->dataAdd($insert);
                }
            }
        }
        return true;
    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-12-09 10:04
     * @功能说明:发送语音
     */
    public function sendCalledPolice($coach){

        $p = new PermissionReminder($coach['uniacid']);

        $auth = $p->pAuth();

        if($auth==false){

            return true;
        }

        $config_model = new Config();

        $config = $config_model->dataInfo(['uniacid'=>$coach['uniacid']]);

        if($config['reminder_status']==0){

            return false;
        }
        $help_model = new HelpConfig();

        $help_config = $help_model->dataInfo(['uniacid'=>$coach['uniacid']]);

        $aliyun = new aliyun();

        $moor = new Moor($coach['uniacid']);

        $winner_model = new Winnerlook($coach['uniacid']);
        //给管理员发通知
        if($help_config['reminder_admin_status']==1&&!empty($help_config['reminder_notice_phone'])&&(empty($coach['admin_id'])||$help_config['reminder_notice_admin']==1)){

            foreach ($help_config['reminder_notice_phone'] as $value){

                if($config['reminder_type']==1){
                    //发送语音通知
                    $res = $aliyun::main($coach['uniacid'],$value,2);

                }elseif($config['reminder_type']==2){

                    $res = $moor->webCall($value,2);

                }else{

                    $res = $winner_model->webCall($value,2);
                }
            }
        }
        //给代理商发通知
        if(!empty($coach['admin_id'])&&$help_config['help_reminder_agent_status']){

            $admin_model = new Admin();

            $phone = $admin_model->where(['id'=>$coach['admin_id']])->value('phone');

            if(!empty($phone)){

                if($config['reminder_type']==1) {
                    //发送语音通知
                    $res = $aliyun::main($coach['uniacid'], $phone,2);

                }elseif($config['reminder_type']==2){

                    $res = $moor->webCall($phone,2);
                }else{

                    $res = $winner_model->webCall($phone,2);
                }
            }
        }

        return !empty($res)?$res:true;
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-12-09 11:35
     * @功能说明:定时任务给还有5分钟完成的订单给技师发送语音消息
     */
    public function orderEndSendCalled($uniacid){

        $p = new PermissionReminder($uniacid);

        $auth = $p->pAuth();

        if($auth==false){

            return true;
        }

        $config_model = new Config();

        $config = $config_model->dataInfo(['uniacid'=>$uniacid]);
        //未开启语音通知 或未开启定时任务
        if($config['reminder_status']==0||$config['order_end_status']==0){

            return false;
        }

        $order_model = new Order();

        $dis = [

            'uniacid' => $uniacid,
            //未接单单
            'pay_type'=> 6,

            'is_add'  => 0
        ];

        $order = $order_model->where($dis)->order('id desc')->limit(20)->select()->toArray();

        $record_model = new Record();

        $aliyun = new aliyun();

        $coach_model = new Coach();

        $winner_model = new Winnerlook($uniacid);

        $moor = new Moor($uniacid);

        if(!empty($order)){

            foreach ($order as $value){
                //已经服务的时长
                $have_service_time = time()-$value['start_service_time'];

                $have_add_time_long = $order_model->where(['add_pid'=>$value['id'],'pay_type'=>7])->sum('true_time_long');

                $add_time_long = $order_model->where(['add_pid'=>$value['id']])->where('pay_type','in',[3,4,5,6])->sum('true_time_long');
                //总时长
                $total_time = ($add_time_long+$value['true_time_long'])*60;
                //已经服务的时长
                $have_service_time = $have_service_time-$have_add_time_long*60;

                if($total_time-$have_service_time>5*60){

                    continue;
                }

                $dis = [

                    'order_id' => $value['id'],

                    'uniacid'  => $value['uniacid'],

                    'type'     => 2
                ];

                $find = $record_model->where($dis)->find();
                //如果没有发送过通知
                if(empty($find)){

                    $phone = $coach_model->where(['id'=>$value['coach_id']])->value('mobile');

                    if($config['reminder_type']==1){
                        //发送语音通知
                        $res = $aliyun::main($uniacid,$phone,3);

                    }elseif($config['reminder_type']==2){

                        $res = $moor->webCall($phone,3);

                    }else{

                        $res = $winner_model->webCall($phone,3);
                    }

                    $insert  = [

                        'uniacid' => $value['uniacid'],

                        'order_id'=> $value['id'],

                        'res'     => json_encode($res),

                        'type'    => 2

                    ];

                    $record_model->dataAdd($insert);
                }
            }
        }
        return true;
    }





}