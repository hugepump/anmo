<?php
namespace app\adapay\model;

use app\BaseModel;
use app\massage\model\Admin;
use app\massage\model\ChannelList;
use app\massage\model\Coach;
use app\massage\model\Commission;
use app\massage\model\Order;
use app\massage\model\OrderPrice;
use app\massage\model\Salesman;
use app\massage\model\User;
use app\massage\model\Wallet;
use longbingcore\wxcore\Adapay;
use think\facade\Db;

class Member extends BaseModel
{
    //定义表名
    protected $name = 'shequshop_adapay_member';




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
     * @DataTime: 2023-08-14 14:11
     * @功能说明:账户列表
     */
    public function adminList($dis,$page=10,$where=[]){

        $data = $this->alias('a')
                ->join('shequshop_adapay_bank b','a.id = b.order_member_id','left')
                ->join('massage_service_user_list c','a.user_id = c.id','left')
                ->where($dis)
                ->where(function ($query) use ($where){
                    $query->whereOr($where);
                })
                ->field('a.*,b.card_name,b.card_id,b.bank_name,b.tel_no,c.nickName')
                ->group('a.id desc')
                ->paginate($page)
                ->toArray();

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
     * @param $user_id
     * @功能说明:获取用户账户信息
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-14 10:40
     */
    public function getDivMembers($user_id,$cash,$fee_flag=1){

        $data = $this->dataInfo(['user_id'=>$user_id,'status'=>1]);

        if(!empty($data)){

            $div_members = [

                    'member_id' => $data['member_id'],

                    'amount'    => sprintf("%01.2f",$cash),

                ];

            if($fee_flag==1){

                $div_members['fee_flag'] = 'Y';
            }

            return $div_members;
        }

        return false;
    }


    /**
     * @param $order
     * @param $user
     * @param $cash
     * @param $obj
     * @param $id
     * @功能说明:添加提现记录
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-15 14:13
     */
    public function addWallet($order,$user,$cash,$obj,$id){
     //   $arr = getConfigSettingArr($order['uniacid'],['tax_point']);
        //获取税点
        $tax_point = 0;

        $balance   = 100-$tax_point;

        $insert = [

            'uniacid'  => $order['uniacid'],

            'user_id'  => $user['user_id'],

            'coach_id' => $user['id'],

            'admin_id' => !empty($user['admin_id'])?$user['admin_id']:0,

            'total_price' => $cash,

            'balance' => $balance,

            'apply_price' => round($cash * $balance / 100, 2),

            'service_price' => round( $cash * $tax_point / 100, 2),

            'code' => orderCode(),

            'tax_point' => $tax_point,

            'text' => '分账，自动到账，订单号:'.$order['order_code'],

            'type' => $obj['wallet_type'],

            'status' => 2,

            'is_auto' => 2,

            'apply_transfer' => 4,

            'payment_no' => $id,

            'online' => 4,

            'sh_time' => time(),

            'true_price' => round($cash * $balance / 100, 2),
        ];

        $wallet_model = new Wallet();
        //提交审核
        $res = $wallet_model->dataAdd($insert);

        $id  = $wallet_model->getLastInsID();

        return $id;
    }



    /**
     * @param $commission
     * @param $order
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-14 11:14
     */
    public function adapaySuccess($commission,$order,$fee_flag){

        if(empty($commission)||$commission['cash']<=0){

            return false;
        }

        $record_model = new PayRecord();

        $adapay_record_model = new Record();

        $adapay = new \longbingcore\wxcore\Adapay($order['uniacid']);
        //获取分账各类对象
        $obj = $this->getAdapayObj($commission['type']);

        if(empty($obj)){

            return false;
        }

        $model= $obj['model'];
        //获取用户id
        $user = $model->where(['id'=>$commission['top_id']])->find();

        if(!empty($user)){

            $user = $user->toArray();

            if(in_array($commission['type'],[1,9,14,15])){

                $user['user_id'] = $user['id'];
            }
            //获取账户信息
            $div_members = $this->getDivMembers($user['user_id'],$commission['cash'],$fee_flag);

            if(!empty($div_members)){

                $text = $obj['text'].',订单:'.$order['order_code'];
                //分账
                $res = $adapay->confirmorderCreate($order['adapay_id'],orderCode(),$commission['cash'],[$div_members],$text,$fee_flag);

                if($res['status']=='succeeded'){

                    $record_model->dataUpdate(['id'=>$order['id']],['true_price'=>$order['true_price']-$commission['cash']]);
                    //添加分账记录
                    $res = $adapay_record_model->addRecord($order,$commission,$res['id']);

                    return true;

                }else{

                    $errlog_model = new ErrLog();

                    $errinsert = [

                        'uniacid' => $order['uniacid'],

                        'adapay_id'=> $order['adapay_id'],

                        'text'    => serialize($res),

                        'type'    => 'massage',

                        'comm_id' => $commission['id'],

                        'order_id'=> $commission['order_id']
                    ];

                    $errlog_model->dataAdd($errinsert);
                }
            }
        }

        return false;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-15 13:33
     * @功能说明:获取
     */
    public function totalOrderList($order_id){

        $price_log_model = new OrderPrice();

        $dis = [

            'a.top_order_id' => $order_id,

            'b.status'       => 1,

            'b.pay_mode'     => 1
        ];

        $data = $price_log_model->alias('a')
                ->join('shequshop_adapay_pay_record b','a.order_code = b.order_code')
                ->where($dis)
                ->where('b.type','in',['Massage','MassageUp'])
                ->where('b.true_price','>',0)
                ->field('b.*,a.id as log_id,a.top_order_id')
                ->group('a.id')
                ->order('b.true_price desc,b.id desc')
                ->select()
                ->toArray();

        return $data;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-14 09:53
     * @功能说明:结束订单时分账
     */
    public function adapayCommission($commission,$order_id){

        $record_model = new PayRecord();
        //查看是否需要分账(主订单)
        $record = $record_model->dataInfo(['order_id'=>$order_id,'status'=>1,'type'=>'Massage','pay_mode'=>1]);

        if(empty($record)){

            return false;
        }

        if(empty($commission)){

            return false;
        }

        $config_model = new Config();

        $log_model    = new OrderPrice();

        $config = $config_model->dataInfo(['uniacid'=>$record['uniacid']]);
        //含升级订单
        $log    = $this->totalOrderList($order_id);

        $cash = $commission['cash'];

        $res = false;

        if(!empty($log)){

            foreach ($log as $value){

                $commission['cash'] = $value['true_price']>$cash?$cash:$value['true_price'];

                if($commission['cash']>0){

                    $res = $this->adapaySuccess($commission,$value,$config['commission']);

                    if($res==true){

                        $del_cash = $commission['cash'];
                        //减去日志里面的可退款金额
                        $log_model->where(['id'=>$value['log_id']])->update(['can_refund_price'=>Db::raw("can_refund_price-$del_cash")]);

                        $cash -= $commission['cash'];

                    }else{

                        return false;
                    }

                }else{

                    return true;
                }
            }
        }

        return $res;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-28 16:20
     * @功能说明:给平台分账
     */
    public function adaPayMyself($order_id){

        $log = $this->totalOrderList($order_id);

        if(!empty($log)){

            $adapay = new \longbingcore\wxcore\Adapay(666);

            $record_model = new PayRecord();

            $adapay_record_model = new Record();

            $config_model = new Config();

            $config = $config_model->dataInfo(['uniacid'=>666]);

            foreach ($log as $value){

                $div_members = [

                        'member_id' => 0,

                        'amount'    => sprintf("%01.2f",$value['true_price']),
                    ];

                if($config['commission']==1){

                    $div_members['fee_flag'] = 'Y';
                }
                //分账
                $res = $adapay->confirmorderCreate($value['adapay_id'],orderCode(),$value['true_price'],[$div_members],'',$config['commission']);

                if($res['status']=='succeeded'){

                    $record_model->dataUpdate(['id'=>$value['id']],['true_price'=>$value['true_price']-$value['true_price']]);
                    //添加分账记录
                    $insert = [

                        'uniacid' => $value['uniacid'],

                        'order_id'=> $value['top_order_id'],

                        'commission_id' => 0,

                        'cash' => $value['true_price'],

                        'type' => 0,

                        'adapay_id' => $res['id'],

                        'son_order_id' => $value['top_order_id']==$value['order_id']?0:$value['order_id']

                    ];

                    $adapay_record_model->dataAdd($insert);

                }else{

                    $errlog_model = new ErrLog();

                    $errinsert = [

                        'uniacid' => $value['uniacid'],

                        'adapay_id'=> $value['adapay_id'],

                        'text'    => serialize($res),

                        'type'    => 'company',

                        'order_id'=> $value['id']
                    ];

                    $errlog_model->dataAdd($errinsert);
                }
            }
        }

        return true;
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-28 16:20
     * @功能说明:储值订单给平台分账 给邀请用户充值的技师分账
     */
    public function adaPayBalanceMyself($order){

        $adapay = new \longbingcore\wxcore\Adapay($order['uniacid']);

        $record_model = new PayRecord();

        $adapay_record_model = new Record();

        $config_model = new Config();

        $commission_model = new Commission();

        $config = $config_model->dataInfo(['uniacid'=>$order['uniacid']]);

        $dis = [

            'status' => 1,

            'order_id'=> $order['id'],

            'type' => 'Balance',

            'pay_mode' => 1
        ];

        $data = $record_model->dataInfo($dis);

        if(empty($data)){

            return false;
        }

        $commission = $commission_model->dataInfo(['order_id'=>$order['id'],'status'=>2,'type'=>7]);

        if(!empty($commission)){
            //技师邀请用户充值佣金分账
            $this->adapaySuccess($commission,$data,$config['commission']);
        }

        $data = $record_model->dataInfo($dis);
        //剩下的给平台分账
        $cash = $data['true_price'];

        if($cash<=0){

            return false;
        }

        $div_members = [

                'member_id' => 0,

                'amount'    => sprintf("%01.2f",$cash),
            ];

        if($config['commission']==1){

            $div_members['fee_flag'] = 'Y';
        }
        //分账
        $res = $adapay->confirmorderCreate($data['adapay_id'],orderCode(),$cash,[$div_members],'',$config['commission']);

        if($res['status']=='succeeded'){

            $record_model->dataUpdate(['id'=>$data['id']],['true_price'=>$data['true_price']-$cash]);
            //添加分账记录
            $insert = [

                'uniacid' => $data['uniacid'],

                'order_id'=> $data['order_id'],

                'commission_id' => 0,

                'cash' => $cash,

                'type' => -1,

                'adapay_id' => $res['id'],

            ];

            $adapay_record_model->dataAdd($insert);

        }else{

            $errlog_model = new ErrLog();

            $errinsert = [

                'uniacid' => $data['uniacid'],

                'adapay_id'=> $data['adapay_id'],

                'text'    => serialize($res),

                'type'    => 'balance',

                'order_id'=> $data['order_id']
            ];

            $errlog_model->dataAdd($errinsert);
        }

        return true;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-29 14:37
     * @功能说明:初始化已经结束但未分账但订单
     */
    public function initData(){

         $order_model = new Order();

         $dis = [

             'a.pay_type' => 7,

             'a.have_tx'  => 1,

             'b.type'     => 'Massage',

             'b.pay_mode' => 1

         ];

         $data = $order_model->alias('a')
                 ->join('shequshop_adapay_pay_record b','a.id = b.order_id')
                 ->where($dis)
                 ->where('b.true_price','>','0')
                 ->where('b.status','>','-1')
                 ->field('b.*')
                 ->limit(10)
                 ->select()
                 ->toArray();

         if(!empty($data)){

             foreach ($data as $v){

                 $this->adaPayMyself($v['order_id']);

             }

         }
         return true;
    }

    /**
     * @param $type
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-14 11:15
     * 1分销 2加盟商 3技师 4分销商 5上级分销商 6省代分销 7技师拉用户充值余额 8车费 9合伙人 10渠道商 11平台 12业务员
     */
    public function getAdapayObj($type){

        if(in_array($type,[1,9,14,15])){

            $data['model'] = new User();

            $data['cash']  = 'new_cash';

            $data['text']  = '用户分销|合伙人佣金';

            $data['wallet_type'] = 4;

        }elseif (in_array($type,[2,5,6,13])){

            $data['model'] = new Admin();

            $data['cash']  = 'cash';

            $data['text']  = '代理商佣金';

            $data['wallet_type'] = 3;

        }elseif (in_array($type,[10])){

            $data['model'] = new ChannelList();

            $data['cash']  = 'cash';

            $data['text']  = '渠道商佣金';

            $data['wallet_type'] = 5;

        }elseif (in_array($type,[12])){

            $data['model'] = new Salesman();

            $data['cash']  = 'cash';

            $data['text']  = '业务员佣金';

            $data['wallet_type'] = 6;

        }elseif (in_array($type,[3])){

            $data['model'] = new Coach();

            $data['cash']  = 'service_price';

            $data['text']  = '技师服务费';

            $data['wallet_type'] = 1;

        }elseif (in_array($type,[8])){

            $data['model'] = new Coach();

            $data['cash']  = 'car_price';

            $data['text']  = '技师车费';

            $data['wallet_type'] = 2;

        }elseif (in_array($type,[7])){

            $data['model'] = new Coach();

            $data['cash']  = 'service_price';

            $data['text']  = '技师邀请用户充值';

            $data['wallet_type'] = 2;
        }else{

            $data = [];
        }

        return $data;
    }





    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-28 16:20
     * @功能说明:分销分账
     */
    public function adaPayResellerMyself($order){

        $adapay = new \longbingcore\wxcore\Adapay($order['uniacid']);

        $record_model = new PayRecord();

        $adapay_record_model = new Record();

        $config_model = new Config();

        $commission_model = new Commission();

        $config = $config_model->dataInfo(['uniacid'=>$order['uniacid']]);

        $dis = [

            'status' => 1,

            'order_id'=> $order['id'],

            'type' => 'ResellerPay',

            'pay_mode' => 1
        ];

        $data = $record_model->dataInfo($dis);

        if(empty($data)){

            return false;
        }

        $commission = $commission_model->dataInfo(['order_id'=>$order['id'],'status'=>2,'type'=>15]);

        if(!empty($commission)){
            //技师邀请用户充值佣金分账
            $this->adapaySuccess($commission,$data,$config['commission']);
        }

        $data = $record_model->dataInfo($dis);
        //剩下的给平台分账
        $cash = $data['true_price'];

        if($cash<=0){

            return false;
        }

        $div_members = [

            'member_id' => 0,

            'amount'    => sprintf("%01.2f",$cash),
        ];

        if($config['commission']==1){

            $div_members['fee_flag'] = 'Y';
        }
        //分账
        $res = $adapay->confirmorderCreate($data['adapay_id'],orderCode(),$cash,[$div_members],'',$config['commission']);

        if($res['status']=='succeeded'){

            $record_model->dataUpdate(['id'=>$data['id']],['true_price'=>$data['true_price']-$cash]);
            //添加分账记录
            $insert = [

                'uniacid' => $data['uniacid'],

                'order_id'=> $data['order_id'],

                'commission_id' => 0,

                'cash' => $cash,

                'type' => -1,

                'adapay_id' => $res['id'],

            ];

            $adapay_record_model->dataAdd($insert);

        }else{

            $errlog_model = new ErrLog();

            $errinsert = [

                'uniacid' => $data['uniacid'],

                'adapay_id'=> $data['adapay_id'],

                'text'    => serialize($res),

                'type'    => 'balance',

                'order_id'=> $data['order_id']
            ];

            $errlog_model->dataAdd($errinsert);
        }

        return true;
    }







}