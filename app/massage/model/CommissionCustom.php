<?php
namespace app\massage\model;

use app\BaseModel;
use think\facade\Db;

class CommissionCustom extends BaseModel
{
    //定义表名
    protected $name = 'massage_service_order_commission';



    protected $append = [

        'order_goods'

    ];



    public function getOrderGoodsAttr($value,$data){

        if(!empty($data['id'])){

            $order_goods_model = new CommissionGoods();

            $list = $order_goods_model->goodsList(['a.commission_id'=>$data['id']]);

            return $list;

        }

    }





    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-08-25 23:24
     * @功能说明:记录
     */
    public function recordList($dis,$page=10){

        $data = $this->alias('a')
                ->join('massage_service_user_list b','a.user_id = b.id','left')
                ->join('massage_service_user_list c','a.top_id = c.id','left')
                ->join('massage_service_order_list d','a.order_id = d.id','left')
                ->where($dis)
                ->field('a.*,b.nickName,c.nickName as top_name,d.order_code,d.pay_type,d.pay_price,d.transaction_id,d.car_price')
                ->group('a.id')
                ->order('a.id desc')
                ->paginate($page)
                ->toArray();

        return $data;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-08-25 23:24
     * @功能说明:记录
     */
    public function recordListV2($dis,$where,$page=10){


        $data = $this->alias('a')
            ->join('massage_service_user_list b','a.user_id = b.id','left')
            ->join('massage_service_user_list c','a.top_id = c.id  AND a.type in (1,9)','left')
            ->join('massage_service_order_list d','a.order_id = d.id ','left')
            ->join('shequshop_school_admin e','a.top_id = e.id AND a.type in (2,5,6)','left')
            ->join('massage_service_coach_list f','a.top_id = f.id AND a.type in (3,8)','left')
            ->join('massage_order_coach_change_logs g','(d.id = g.order_id OR d.add_pid = g.order_id) AND g.is_new = 1 AND a.top_id=0 AND a.type in (3,8) ','left')
            ->where($dis)
            ->where(function ($query) use ($where){
                $query->whereOr($where);
            })
            ->field('a.*,e.agent_name as admin_name,f.coach_name,b.nickName,c.nickName as top_name,d.order_code,d.pay_type,d.pay_price,d.transaction_id,d.car_price,g.now_coach_name')
            ->group('a.id')
            ->order('a.id desc')
            ->paginate($page)
            ->toArray();

        return $data;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-08-25 23:34
     * @功能说明:佣金到账
     */
    public function successCash($order_id){

        $data = $this->dataInfo(['order_id'=>$order_id,'type'=>1]);

        if(!empty($data)&&$data['status']==1&&$data['cash']>0){

            $user_model = new User();

            $user = $user_model->dataInfo(['id'=>$data['top_id']]);

            $res = $user_model->where(['id'=>$data['top_id'],'new_cash'=>$user['new_cash']])->update(['new_cash'=>$user['new_cash']+$data['cash'],'cash'=>$user['cash']+$data['cash']]);

            if($res==0){

                return $res;
            }

        }

        return 1;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-11-07 16:21
     * @功能说明:代理商以及上级代理佣金到账
     */
    public function adminSuccessCash($order_id){

        $dis = [

            'order_id' => $order_id,

            'status'   => 1
        ];

        $data = $this->where($dis)->where('type','in',[2,5,6])->select()->toArray();

        $admin_model = new Admin();

        if(!empty($data)){

            foreach ($data as $v){

                $cash = $v['cash'];

                $admin = $admin_model->dataInfo(['id'=>$v['admin_id']]);

                if(!empty($admin)&&$cash>0){

                    $res = $admin_model->where(['id'=>$v['admin_id'],'cash'=>$admin['cash']])->update(['cash'=>Db::raw("cash+$cash")]);

                    if($res==0){

                        return $res;
                    }
                }

            }

        }
        //结束所有佣金
        $this->dataUpdate(['order_id'=>$order_id],['status'=>2,'cash_time'=>time()]);

        return true;

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

        $data['update_time'] = time();

        $res = $this->where($dis)->update($data);

        return $res;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:06
     * @功能说明:列表
     */
    public function dataList($dis,$page=10){

        $data = $this->where($dis)->order('top desc,id desc')->paginate($page)->toArray();

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
     * @DataTime: 2021-08-26 23:39
     * @功能说明:添加佣金
     */
    public function commissionAdd($order){
        //技师佣金
        $this->coachCommission($order);
        //加盟商佣金
        $this->adminCommission($order);

        $user_model = new User();
        //上级
        $top = $user_model->where(['id'=>$order['user_id']])->value('pid');

        if(!empty($top)){

            $ser_model = new Service();

            $com_mdoel = new Commission();

            $com_goods_mdoel = new CommissionGoods();

            foreach ($order['order_goods'] as $v){
                //查看是否有分销
                $ser = $ser_model->dataInfo(['id'=>$v['goods_id']]);

                if(!empty($ser['com_balance'])){

                    $insert = [

                        'uniacid' => $order['uniacid'],

                        'user_id' => $order['user_id'],

                        'top_id'  => $top,

                        'order_id'=> $order['id'],

                        'order_code' => $order['order_code'],

                    ];

                   $find = $com_mdoel->dataInfo($insert);

                   $cash = $v['true_price']*$ser['com_balance']/100*$v['num'];

                   if(empty($find)){

                       $insert['cash'] = $cash;

                       $com_mdoel->dataAdd($insert);

                       $id = $com_mdoel->getLastInsID();

                   }else{

                       $id = $find['id'];

                       $update = [

                           'cash' => $find['cash']+$cash
                       ];
                        //加佣金
                       $com_mdoel->dataUpdate(['id'=>$find['id']],$update);

                   }

                   $insert = [

                       'uniacid' => $order['uniacid'],

                       'order_goods_id' => $v['id'],

                       'commission_id'  => $id,

                       'cash'           => $cash,

                       'num'            => $v['num'],

                       'balance'        => $ser['com_balance']
                   ];
                   //添加到自订单记录表
                   $res = $com_goods_mdoel->dataAdd($insert);

                }

            }

        }

        return true;

    }











    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-08-26 23:39
     * @功能说明:添加佣金
     */
    public function commissionAddData($order){

        $user_model = new User();

        $dis = [

            'id' => $order['user_id'],

        ];
        //上级
        $top_id = $user_model->where($dis)->value('pid');

        $top = $user_model->dataInfo(['id'=>$top_id]);

        $total_cash = 0;

        if(!empty($top)){

            if(getFxStatus($order['uniacid'])==1&&$top['is_fx']==0){

                return $total_cash;
            }

            $top = $top['id'];

            $ser_model = new Service();

            $com_mdoel = new Commission();

            $com_goods_mdoel = new CommissionGoods();

            foreach ($order['order_goods'] as $v){
                //查看是否有分销
                $ser = $ser_model->dataInfo(['id'=>$v['goods_id']]);

                if($ser['com_balance']<=0){
                    //全局设置
                    $ser['com_balance'] = getConfigSetting($order['uniacid'],'user_agent_balance');

                    $user_agent_balance = 1;
                }

                if(!empty($ser['com_balance'])){

                    $insert = [

                        'uniacid' => $order['uniacid'],

                        'user_id' => $order['user_id'],

                        'top_id'  => $top,

                        'order_id'=> $order['id'],

                        'order_code' => $order['order_code'],

                        'balance'    => $user_agent_balance==1?$ser['com_balance']:0,

                    ];

                    $find = $com_mdoel->dataInfo($insert);

                    $cash = $v['true_price']*$ser['com_balance']/100*$v['num'];

                    $total_cash += $cash;

                    if(empty($find)){

                        $insert['cash'] = $cash;

                        $insert['status'] = -1;

                        $insert['admin_id'] = !empty($order['admin_id'])?$order['admin_id']:0;

                        $com_mdoel->dataAdd($insert);

                        $id = $com_mdoel->getLastInsID();

                    }else{

                        $id = $find['id'];

                        $update = [

                            'cash' => $total_cash
                        ];
                        //加佣金
                        $com_mdoel->dataUpdate(['id'=>$find['id']],$update);

                    }

                    $insert = [

                        'uniacid' => $order['uniacid'],

                        'order_goods_id' => $v['id'],

                        'commission_id'  => $id,

                        'cash'           => $cash,

                        'num'            => $v['num'],

                        'balance'        => $ser['com_balance']
                    ];
                    //添加到自订单记录表
                    $res = $com_goods_mdoel->dataAdd($insert);

                }

            }

        }

        return $total_cash;

    }



    /**
     * @param $order
     * @功能说明:技师佣金
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-06-12 13:14
     */
    public function carCommission($order){

        if(isset($order['true_car_price'])&&$order['true_car_price']>0){

            $insert = [

                'uniacid' => $order['uniacid'],

                'user_id' => $order['user_id'],

                'top_id'  => $order['coach_id'],

                'order_id'=> $order['id'],

                'order_code' => $order['order_code'],

                'type'    => 8,

                'cash'    => $order['true_car_price'],

                'admin_id'=> $order['admin_id'],

                'balance' => 0,

                'status'  => -1,
            ];

            if(empty($order['coach_id'])){

                $insert['cash_status'] = 0;
            }

            $res = $this->dataAdd($insert);
        }

        return true;
    }

    /**
     * @param $order
     * @功能说明:技师佣金
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-06-12 13:14
     */
    public function coachCommission($order){

        if(isset($order['coach_cash'])&&$order['coach_cash']>0){

            $insert = [

                'uniacid' => $order['uniacid'],

                'user_id' => $order['user_id'],

                'top_id'  => $order['coach_id'],

                'order_id'=> $order['id'],

                'order_code' => $order['order_code'],

                'type'    => 3,

                'cash'    => $order['coach_cash'],

                'admin_id'=> $order['admin_id'],

                'balance' => $order['coach_balance'],

                'status'  => -1,
            ];

            if(empty($order['coach_id'])){

                $insert['cash_status']= 0;
            }

            $res = $this->dataAdd($insert);

            return $res;
        }
        return 0;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-06-12 13:32
     * @功能说明:加盟商佣金
     */
    public function adminCommission($order){

        if(!empty($order['admin_id'])){

            $admin_model = new Admin();

            $city_type = $admin_model->where(['id'=>$order['admin_id']])->value('city_type');

            $insert = [

                'uniacid' => $order['uniacid'],

                'user_id' => $order['user_id'],

                'top_id'  => $order['admin_id'],

                'order_id'=> $order['id'],

                'order_code' => $order['order_code'],

                'type'    => 2,

                'cash'    => $order['admin_cash'],

                'admin_id'=> $order['admin_id'],

                'balance' => $order['admin_balance'],

                'status'  => -1,

                'city_type' => $city_type,

            ];
            //如果是线下技师需要把佣金返回给代理商
            if(empty($order['coach_id'])){

                $insert['coach_cash'] = $order['coach_cash'];

                $insert['car_cash']  = $order['true_car_price'];

            }

            $res = $this->dataAdd($insert);
        }

        return true;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-06-12 13:32
     * @功能说明:加盟商上级佣金
     */
    public function adminLevelCommission($order){

        if(!empty($order['admin_pid'])){

            $admin_model = new Admin();

            $city_type = $admin_model->where(['id'=>$order['admin_pid']])->value('city_type');

            $insert = [

                'uniacid' => $order['uniacid'],

                'user_id' => $order['user_id'],

                'top_id'  => $order['admin_pid'],

                'order_id'=> $order['id'],

                'order_code' => $order['order_code'],

                'type'    => 5,

                'cash'    => $order['level_cash'],

                'admin_id'=> $order['admin_pid'],

                'balance' => $order['level_balance'],

                'status'  => -1,

                'city_type' => $city_type,

            ];

            $res = $this->dataAdd($insert);

        }

        return true;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-06-12 13:32
     * @功能说明:省代理商佣金
     */
    public function adminProvinceCommission($order){

        if(!empty($order['p_admin_pid'])){

            $admin_model = new Admin();

            $city_type = $admin_model->where(['id'=>$order['p_admin_pid']])->value('city_type');

            $insert = [

                'uniacid' => $order['uniacid'],

                'user_id' => $order['user_id'],

                'top_id'  => $order['p_admin_pid'],

                'order_id'=> $order['id'],

                'order_code' => $order['order_code'],

                'type'    => 6,

                'cash'    => $order['p_level_cash'],

                'admin_id'=> $order['p_admin_pid'],

                'balance' => $order['p_level_balance'],

                'status'  => -1,

                'city_type' => $city_type,

            ];

            $res = $this->dataAdd($insert);

        }

        return true;

    }
    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-08-28 14:35
     * @功能说明:佣金到账
     */
    public function successCommission($order_id){

        $comm = $this->dataInfo(['order_id'=>$order_id,'status'=>1]);

        if(!empty($comm)){

            $user_model = new User();

            $user = $user_model->dataInfo(['id'=>$comm['top_id']]);

            if(!empty($user)){

                $update = [

                    'balance' => $user['balance']+$comm['cash'],

                    'cash'    => $user['cash']+$comm['cash'],
                ];

                $user_model->dataUpdate(['id'=>$comm['top_id']],$update);

                $this->dataUpdate(['id'=>$comm['id']],['status'=>2,'cash_time'=>time()]);

            }

        }

        return true;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-08-28 14:48
     * @功能说明:退款的时候要减去分销
     */
    public function refundComm($refund_id){

        $refund_model    = new RefundOrder();

        $com_goods_mdoel = new CommissionGoods();

        $order_model     = new Order();

        $refund_order = $refund_model->dataInfo(['id'=>$refund_id]);

        if(!empty($refund_order)){
            //查询这笔等待有无佣金
            $comm = $this->dataInfo(['order_id'=>$refund_order['order_id'],'status'=>1,'type'=>1]);

            if(!empty($comm)){

                foreach ($refund_order['order_goods'] as $v){

                    $comm_goods = $com_goods_mdoel->dataInfo(['commission_id'=>$comm['id'],'order_goods_id'=>$v['order_goods_id']]);

                    if(!empty($comm_goods)){

                        $comm_goods_cash = $comm_goods['cash']/$comm_goods['num'];

                        $true_num = $comm_goods['num'] - $v['num'];

                        $true_num = $true_num>0?$true_num:0;

                        $update = [

                            'num' => $true_num,

                            'cash'=> $comm_goods_cash*$true_num
                        ];

                        $com_goods_mdoel->dataUpdate(['id'=>$comm_goods['id']],$update);
                    }

                }

                $total_cash = $com_goods_mdoel->where(['commission_id'=>$comm['id']])->sum('cash');

                $total_cash = $total_cash>0?$total_cash:0;

                $update = [

                    'cash' => $total_cash,

                    'status' => $total_cash>0?1:-1
                ];

                $this->dataUpdate(['id'=>$comm['id']],$update);

                $order_model->dataUpdate(['id'=>$refund_order['order_id']],['user_cash'=>$total_cash]);

            }

        }

        return true;

    }





    /**
     * @param $type
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-17 16:05
     */
    public function getTypeText($type){

        switch ($type){

            case 2:
                $arr['cash'] = 'admin_cash';

                $arr['balance'] = 'admin_balance';

                break;
            case 3:
                $arr['cash'] = 'coach_cash';

                $arr['cash'] = 'coach_balance';

                break;

            case 5:
                $arr['cash'] = 'level_cash';

                $arr['balance'] = 'level_balance';

                $arr['admin_id'] = 'admin_pid';

                break;

            case 6:
                $arr['cash'] = 'p_level_cash';

                $arr['balance'] = 'p_level_balance';

                $arr['admin_id'] = 'p_admin_pid';

                break;

            case 9:
                $arr['cash'] = 'partner_cash';

                $arr['balance'] = 'coach_agent_balance';

                $arr['admin_id'] = 'partner_id';

                break;
            case 10:
                $arr['cash'] = 'channel_cash';

                $arr['balance'] = 'channel_balance';

                $arr['admin_id'] = 'channel_id';

                break;

            case 11:
                $arr['cash'] = 'company_cash';

                $arr['balance'] = 'company_balance';

                break;
        }

        return $arr;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-22 10:12
     * @功能说明:获取佣金相关参数
     */
    public function commissionData(){

        $arr = [
            //技师佣金
             [

                'action_name' => "getCoachCash",

                'parameter'   => 'coach_balance',

            ],//用户分销
            [

                'action_name' => 'getUserCash',

                'parameter'   => 'user_agent_balance',
            ],
            //合伙人
            [

                'action_name' => 'getPartnerCash',

                'parameter'   => 'coach_agent_balance',
            ],
            //平台抽成
            [

                'action_name' => 'getCompanyCash',

                'parameter'   => 'admin_balance',
            ],
            //省代
            [

                'action_name' => 'getProvinceCash',

                'parameter'   => 'p_level_balance',

            ],//城市代理
            [

                'action_name' => 'getCityCash',

                'parameter'   => 'level_balance',
            ],

        ];

        return $arr;
    }


    /**
     * @param $order
     * @功能说明:计算各类分销比例
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-22 11:52
     */
    public function balanceData($order,$admin_id=0){

        $coach_model = new Coach();

        $admin_model = new Admin();

        $clock_model = new ClockSetting();

        $coach = $coach_model->dataInfo(['id'=>$order['coach_id']]);
        //技师等级
        $coach_level = $coach_model->getCoachLevel($order['coach_id'],$order['uniacid']);

        if(empty($coach_level)&&!empty($order['coach_id'])){

            return ['code'=>300];
        }

        $coach_level['balance'] = !empty($coach_level)?$coach_level['balance']:0;
        //技师佣金比列
        $order['coach_balance'] = $coach_level['balance'];
        //加钟的时候比例可能是特殊设置
        $order['coach_balance'] = $clock_model->getCoachBalance($order);

        if(empty($coach)||$coach['agent_type']==1){

            $admin_id = !empty($coach['admin_id'])?$coach['admin_id']:$admin_id;
            //代理商各类分销比例
            $order = $admin_model->agentBalanceData($admin_id,$order);

        }else{
            //合伙人分销比例
            $order = $coach_model->partnerBalance($coach,$order);
        }

        return $order;
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-22 10:19
     * @功能说明:计算每类佣金的金额
     */
    public function cashData($order){

        $list = $this->commissionData();

        foreach ($list as $key=>$value){

            $balance = isset($order[$value['parameter']])?$order[$value['parameter']]:0;

            $order['surplus_cash'] = $key==0?$order['true_service_price']:$order['surplus_cash'];

            $action_name = $value['action_name'];

            $order = $this->$action_name($balance,$order,$order['surplus_cash']);

        }

        $order['admin_cash'] = $order['surplus_cash'];

        return $order;

    }


    /**
     * @param $balance
     * @param $order
     * @param $cash
     * @功能说明:合伙人佣金
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-22 11:14
     */
    public function getPartnerCash($balance,$order,$cash){

        $order['partner_cash'] = round($balance*$order['true_service_price']/100,2);

        $order['partner_cash'] = $order['partner_cash']>$cash?$cash:$order['partner_cash'];

        $order['surplus_cash'] = $cash - $order['partner_cash'];

        return $order;

    }


    /**
     * @param $balance
     * @param $order
     * @param $cash
     * @功能说明:平台
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-22 11:12
     */
    public function getCompanyCash($balance,$order,$cash){

        $order['company_cash'] = round($balance*$order['true_service_price']/100,2);

        $order['company_cash'] = $order['company_cash']>$cash?$cash:$order['company_cash'];

        $order['surplus_cash'] = $cash - $order['company_cash'];

        return $order;
    }

    /**
     * @param $balance
     * @param $order
     * @param $cash
     * @功能说明:技师佣金
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-21 17:55
     */
    public function getCoachCash($balance,$order,$cash){
        //技师佣金
        $order['coach_cash'] = round($balance*$order['true_service_price']/100,2);

        $order['coach_cash'] = $order['coach_cash']>$cash?$cash:$order['coach_cash'];

        $order['surplus_cash'] = $cash - $order['coach_cash'];

        return $order;
    }


    /**
     * @param $balance
     * @param $order
     * @param $cash
     * @功能说明:省代佣金
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-21 18:05
     */
    public function getProvinceCash($balance,$order,$cash){
        //上级代理提成
        $order['p_level_cash'] = round($balance*$order['true_service_price']/100,2);

        $order['p_level_cash'] = $order['p_level_cash'] - $cash>0?$cash:$order['p_level_cash'];

        $order['surplus_cash'] = $cash - $order['p_level_cash'];

        return $order;
    }


    /**
     * @param $balance
     * @param $order
     * @param $cash
     * @功能说明:渠道商佣金
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-21 18:05
     */
    public function getChannelCash($balance,$order,$cash){
        //上级代理提成
        $order['channel_cash'] = round($balance*$order['true_service_price']/100,2);

        $order['channel_cash'] = $order['channel_cash'] - $cash>0?$cash:$order['channel_cash'];

        $order['surplus_cash'] = $cash - $order['channel_cash'];

        return $order;
    }




    /**
     * @param $order
     * @param $cash
     * @功能说明:区县佣金
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-21 18:20
     */
    public function getCityCash($balance,$order,$cash){
        //上级代理提成
        $order['city_cash'] = round($balance*$order['true_service_price']/100,2);

        $order['city_cash'] = $order['city_cash'] - $cash>0?$cash:$order['city_cash'];

        $order['surplus_cash'] = $cash - $order['city_cash'];


        return $order;
    }


    /**
     * @param $order
     * @param $cash
     * @功能说明:区县佣金
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-21 18:20
     */
    public function getDistrictCash($balance,$order,$cash){
        //上级代理提成
        $order['district_cash'] = round($balance*$order['true_service_price']/100,2);

        $order['district_cash'] = $order['district_cash'] - $cash>0?$cash:$order['district_cash'];

        $order['surplus_cash']  = $cash - $order['district_cash'];

        return $order;
    }




    /**
     * @param $balance
     * @param $order
     * @param $cash
     * @功能说明:用户分销
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-22 11:09
     */
    public function getUserCash($balance,$order,$cash){

        $user_model   = new User();

        $config_model = new Config();

        $dis = [

            'id' => $order['user_id'],

        ];

        $total_cash = 0;
        //上级
        $top_id = $user_model->where($dis)->value('pid');

        $top = $user_model->dataInfo(['id'=>$top_id]);

        $config = $config_model->dataInfo(['uniacid'=>$order['uniacid']]);

        if(!empty($top)&&$config['fx_check']==1&&$top['is_fx']==0){

            return $order;
        }

        if(!empty($top)){

            $order['user_top_id'] = $top['id'];

            $total_cash = round($balance*$order['true_service_price']/100,2);


        }

        $order['user_c_cash'] = $total_cash>$cash?$cash:$total_cash;

        $order['surplus_cash'] = $cash - $order['user_c_cash'];

        return $order;

    }





    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-08-26 23:39
     * @功能说明:添加佣金
     */
    public function commissionAddDataV2($order){

        if(!empty($order['user_top_id'])){

            $ser_model = new Service();

            $com_mdoel = new Commission();

            $com_goods_mdoel = new CommissionGoods();

            $top = $order['user_top_id'];

            $insert = [

                'uniacid' => $order['uniacid'],

                'user_id' => $order['user_id'],

                'top_id'  => $top,

                'order_id'=> $order['id'],

                'order_code' => $order['order_code'],

                'balance'    => getConfigSetting($order['uniacid'],'user_agent_balance'),

                'cash' => $order['user_c_cash'],

                'status' => -1,

                'admin_id' => !empty($order['admin_id'])?$order['admin_id']:0

            ];

            $com_mdoel->dataAdd($insert);

            $id = $com_mdoel->getLastInsID();

            foreach ($order['order_goods'] as $v){
                //查看是否有分销
                $ser = $ser_model->dataInfo(['id'=>$v['goods_id']]);

                if($ser['com_balance']<=0){
                    //全局设置
                    $ser['com_balance'] = getConfigSetting($order['uniacid'],'user_agent_balance');

                }

                if(!empty($ser['com_balance'])){

                    $cash = $v['true_price']*$ser['com_balance']/100*$v['num'];

                    $insert = [

                        'uniacid' => $order['uniacid'],

                        'order_goods_id' => $v['id'],

                        'commission_id'  => $id,

                        'cash'           => $cash,

                        'num'            => $v['num'],

                        'balance'        => $ser['com_balance']
                    ];
                    //添加到自订单记录表
                    $res = $com_goods_mdoel->dataAdd($insert);

                }

            }

        }

        return true;

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-08-26 23:39
     * @功能说明:添加佣金
     */
    public function commissionAddDataV3($order){

        if(!empty($order['user_top_id'])){

            $com_mdoel = new Commission();

            $com_goods_mdoel = new CommissionGoods();

            $top = $order['user_top_id'];

            $insert = [

                'uniacid' => $order['uniacid'],

                'user_id' => $order['user_id'],

                'top_id'  => $top,

                'order_id'=> $order['id'],

                'order_code'=> $order['order_code'],

                'balance'  => $order['user_agent_balance'],

                'cash'     => $order['user_c_cash'],

                'status'   => -1,

                'admin_id' => !empty($order['admin_id'])?$order['admin_id']:0

            ];

            $com_mdoel->dataAdd($insert);

            $id = $com_mdoel->getLastInsID();

            foreach ($order['order_goods'] as $v){

                $cash = $v['true_price']*$order['user_agent_balance']/100*$v['num'];

                $insert = [

                    'uniacid' => $order['uniacid'],

                    'order_goods_id' => $v['id'],

                    'commission_id'  => $id,

                    'cash'           => $cash,

                    'num'            => $v['num'],

                    'balance'        => $order['user_agent_balance']
                ];
                //添加到自订单记录表
                $res = $com_goods_mdoel->dataAdd($insert);

            }

        }

        return true;

    }

    /**
     * @param $order
     * @功能说明:增加合伙人佣金
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-22 15:55
     */
    public function partnerCommission($order){

        if(!empty($order['partner_id'])){

            $insert = [

                'uniacid' => $order['uniacid'],

                'user_id' => $order['user_id'],

                'top_id'  => $order['partner_id'],

                'order_id'=> $order['id'],

                'order_code' => $order['order_code'],

                'type'    => 9,

                'cash'    => $order['partner_cash'],

               // 'admin_id'=> $order['p_admin_pid'],

                'balance' => $order['coach_agent_balance'],

                'status'  => -1,


            ];

            $res = $this->dataAdd($insert);

        }

        return true;


    }


    /**
     * @param $order
     * @功能说明:增加渠道商佣金
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-22 15:55
     */
    public function channelCommission($order){

        if(!empty($order['channel_id'])){

            $insert = [

                'uniacid' => $order['uniacid'],

                'user_id' => $order['user_id'],

                'top_id'  => $order['channel_id'],

                'order_id'=> $order['id'],

                'order_code' => $order['order_code'],

                'type'    => 10,

                'cash'    => $order['channel_cash'],

                // 'admin_id'=> $order['p_admin_pid'],

                'balance' => $order['channel_balance'],

                'status'  => -1,


            ];

            $res = $this->dataAdd($insert);

        }

        return true;


    }


    /**
     * @param $order
     * @功能说明:增加平台佣金
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-22 15:55
     */
    public function companyCommission($order){

        if(!empty($order['company_cash'])){

            $admin_model = new Admin();

            $admin_id = $admin_model->where(['uniacid'=>$order['uniacid'],'is_admin'=>1])->value('id');

            $insert = [

                'uniacid' => $order['uniacid'],

                'user_id' => $order['user_id'],

                'top_id'  => $admin_id,

                'order_id'=> $order['id'],

                'order_code' => $order['order_code'],

                'type'    => 11,

                'cash'    => $order['company_cash'],

                'balance' => $order['company_balance'],

                'status'  => -1,

            ];

            $res = $this->dataAdd($insert);

        }

        return true;


    }

    /**
     * @param $order
     * @功能说明:计算各类分销比例（自定义）
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-22 11:52
     */
    public function balanceDataCustom($order,$admin_id){

        $coach_model = new Coach();

        $clock_model = new ClockSetting();

        $admin_model = new Admin();

        $distri_model = new DistributionConfig();

        $dis_config = $distri_model->where(['uniacid'=>$order['uniacid']])->select()->toArray();

        foreach ($dis_config as $value){

            $order[$value['balance_name']] = $value['balance'];

        }

        $coach = $coach_model->dataInfo(['id'=>$order['coach_id']]);
        //技师等级
        $coach_level = $coach_model->getCoachLevel($order['coach_id'],$order['uniacid']);

        if(empty($coach_level)&&!empty($order['coach_id'])){

            return ['code'=>300];
        }

        $coach_level['balance'] = !empty($coach_level)?$coach_level['balance']:0;
        //技师佣金比列
        $order['coach_balance'] = $coach_level['balance'];
        //加钟的时候比例可能是特殊设置
        $order['coach_balance'] = $clock_model->getCoachBalance($order);

        $admin_id = !empty($coach['admin_id'])?$coach['admin_id']:$admin_id;
        //获取代理商
        $order = $admin_model->agentBalanceDataCustom($admin_id,$order);
        //获取分销用户信息
        $order = $this->getFxUserId($order);

        return $order;
    }


    /**
     * @param $order
     * @功能说明:获取分销用户信息
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-30 14:27
     */
    public function getFxUserId($order){

        $user_model  = new User();
        //上级
        $top_id = $user_model->where(['id'=>$order['user_id']])->value('pid');

        $top = $user_model->dataInfo(['id'=>$top_id]);

        $config_model = new Config();

        $config = $config_model->dataInfo(['uniacid'=>$order['uniacid']]);

        if(!empty($top)&&$config['fx_check']==1&&$top['is_fx']==0){

            return $order;
        }

        if(!empty($top)) {

            $order['user_top_id'] = $top['id'];

        }

        return $order;
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-22 10:19
     * @功能说明:计算每类佣金的金额(自定义)
     */
    public function cashDataCustom($order){

        $distri_model = new DistributionConfig();

        $distri_model->initData($order['uniacid']);

        $list = $distri_model->where(['uniacid'=>$order['uniacid']])->order('top,id desc')->select()->toArray();

        foreach ($list as $key=>$value){

            $balance = isset($order[$value['balance_name']])?$order[$value['balance_name']]:0;

            $order['surplus_cash'] = $key==0?$order['true_service_price']:$order['surplus_cash'];

            $action_name = $value['name'];

            $order = $this->$action_name($balance,$order,$order['surplus_cash']);

        }

        if(key_exists('admin_balance_name',$order)){

            $order['admin_balance'] = $order[$order['admin_balance_name']];

            $order['admin_cash']    = $order[$order['admin_cash_name']];

        }

        if(key_exists('level_balance_name',$order)){

            $order['level_balance'] = $order[$order['level_balance_name']];

            $order['level_cash']    = $order[$order['level_cash_name']];

        }

        return $order;

    }

















}