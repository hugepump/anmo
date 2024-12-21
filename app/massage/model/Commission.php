<?php
namespace app\massage\model;

use app\adapay\model\Member;
use app\adminuser\model\AdminUser;
use app\balancediscount\model\OrderShare;
use app\BaseModel;
use app\coachbroker\model\CoachBroker;
use think\facade\Db;

class Commission extends BaseModel
{


    //type 1分销 2加盟商 3技师 4分销商 5上级分销商 6省代分销 7技师拉用户充值余额 8车费 9合伙人 10渠道商 11平台 12业务员 13车费（代理商）14 二级分销 15分销推荐佣金 16平台（11废弃）17技师空单费 18技师退款手续费 19代理商空单费 20代理商退款手续费 21平台空单费 22平台退款手续费 23代理商承担车费（注意这个佣金是扣除）24技师会员折扣佣金 25技师储值折扣佣金
    //定义表名
    protected $name = 'massage_service_order_commission';


    protected $append = [

        'order_goods',

        'point_cash'

    ];


    /**
     * @param $value
     * @param $data
     * @功能说明:手续费
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-09-14 19:00
     */
    public function getPointCashAttr($value,$data){

        if(!empty($data['id'])){

            $share_model = new CommShare();

            $point_cash  = $share_model->where(['comm_id'=>$data['id'],'cash_type'=>1])->sum('share_cash');

            return $point_cash;
        }
    }


    /**
     * @param $value
     * @param $data
     * @功能说明:保留两位小数
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-23 15:29
     */
    public function getCashAttr($value,$data){

        if(isset($value)){

            return round($value,2);
        }
    }


    /**
     * @param $value
     * @param $data
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-23 15:29
     */
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
     * @功能说明:记录15.51
     * 2099.82
     */
    public function recordList($dis,$page=10){

        $data = $this->alias('a')
                ->join('massage_service_user_list b','a.user_id = b.id','left')
                ->join('massage_service_user_list c','a.top_id = c.id','left')
                ->join('massage_service_order_list d','a.order_id = d.id','left')
                ->where($dis)
                ->field('a.*,b.nickName,c.nickName as top_name,d.order_code,d.pay_type,d.pay_price,d.transaction_id,d.car_price,d.material_type,d.material_price,d.discount')
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
            ->join('massage_service_user_list c','a.top_id = c.id  AND a.type in (1,14)','left')
            ->join('massage_service_order_list d','a.order_id = d.id')
            ->join('shequshop_school_admin e','a.top_id = e.id AND a.type in (2,5,6,11,13)','left')
            ->join('massage_service_coach_list f','a.top_id = f.id AND a.type in (3,8)','left')
            ->join('massage_order_coach_change_logs g','if(d.is_add=0,d.id,d.add_pid) = g.order_id AND g.is_new = 1 AND a.top_id=0 AND a.type in (3,8) ','left')
            ->join('massage_channel_list h','a.top_id = h.id AND a.type = 10','left')
            ->join('massage_salesman_list m','a.top_id = m.id AND a.type = 12','left')
            ->join('massage_coach_broker_list i','a.broker_id = i.id AND a.type = 9','left')
            ->where($dis)
            ->where(function ($query) use ($where){
                $query->whereOr($where);
            })
            ->field('a.*,d.coupon_bear_type,d.init_service_price,m.user_name as salesman_name,h.user_name as channel_name,e.agent_name as admin_name,f.coach_name,c.nickName as top_name,d.order_code,d.pay_type,d.pay_price,d.transaction_id,d.car_price,d.material_price,g.now_coach_name,i.user_name as broker_name')
            ->group('a.id')
            ->order('d.id desc,a.id desc')
            ->paginate($page)
            ->toArray();

        return $data;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-05-17 16:02
     * @功能说明:同个各类角色名字搜索
     */
    public function getIdByName($where){

        $data = $this->alias('a')
            ->join('massage_service_user_list c','a.top_id = c.id  AND a.type in (1,14)','left')
            ->join('massage_service_order_list d','a.order_id = d.id')
            ->join('shequshop_school_admin e','a.top_id = e.id AND a.type in (2,5,6,11,13)','left')
            ->join('massage_service_coach_list f','a.top_id = f.id AND a.type in (3,8)','left')
            ->join('massage_order_coach_change_logs g','if(d.is_add=0,d.id,d.add_pid) = g.order_id AND g.is_new = 1 AND a.top_id=0 AND a.type in (3,8) ','left')
            ->join('massage_channel_list h','a.top_id = h.id AND a.type = 10','left')
            ->join('massage_salesman_list m','a.top_id = m.id AND a.type = 12','left')
            ->join('massage_coach_broker_list i','a.broker_id = i.id AND a.type = 9','left')
            ->where(function ($query) use ($where){
                $query->whereOr($where);
            })
            ->group('a.id')
            ->column('a.id');

        return $data;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-05-17 16:02
     * @功能说明:同个各类角色名字搜索
     */
    public function getIdByNameV2($name){

        $where2[]= ['c.nickName','like','%'.$name.'%'];

        $where[] = ['e.agent_name','like','%'.$name.'%'];

        $where[] = ['f.coach_name','like','%'.$name.'%'];

        $where[] = ['h.user_name','like','%'.$name.'%'];

        $where[] = ['m.user_name','like','%'.$name.'%'];

        $where[] = ['i.user_name','like','%'.$name.'%'];

        $where1[]= ['g.now_coach_name','like','%'.$name.'%'];

        $data1 = $this->alias('a')
            ->join('massage_service_user_list c','a.top_id = c.id  AND a.type in (1,14)','left')
            ->where(function ($query) use ($where2){
                $query->whereOr($where2);
            })
            ->group('a.id')
            ->column('a.id');

        $data = $this->alias('a')
            ->join('shequshop_school_admin e','a.top_id = e.id AND a.type in (2,5,6,11,13,19,20)','left')
            ->join('massage_service_coach_list f','a.top_id = f.id AND a.type in (3,8,17,18)','left')
            ->join('massage_channel_list h','a.top_id = h.id AND a.type = 10','left')
            ->join('massage_salesman_list m','a.top_id = m.id AND a.type = 12','left')
            ->join('massage_coach_broker_list i','a.broker_id = i.id AND a.type = 9','left')
            ->where(function ($query) use ($where){
                $query->whereOr($where);
            })
            ->group('a.id')
            ->column('a.id');

        $data = array_merge($data,$data1);

        $list = $this->alias('a')
            ->join('massage_service_order_list d','a.order_id = d.id')
            ->join('massage_order_coach_change_logs g','if(d.is_add=0,d.id,d.add_pid) = g.order_id AND g.is_new = 1 AND a.top_id=0 AND a.type in (3,8) ','left')
            ->where(function ($query) use ($where1){
                $query->whereOr($where1);
            })
            ->group('a.id')
            ->column('a.id');

        return array_merge($data,$list);
    }



    public function getCommTable($type){

        $arr = [
            [
                'type'=>[1,14],

                'table' => 'massage_service_user_list',

                'name' => 'nickName',

                'filed' => 'top_id',
            ],
            [

                'type'=>[2,5,6,11,13,19,20],

                'table' => 'shequshop_school_admin',

                'name' => 'agent_name',

                'filed' => 'top_id',
            ],
            [
                'type'=>[3,8,17,18],

                'table' => 'massage_service_coach_list',

                'name' => 'coach_name',

                'filed' => 'top_id',
            ],
            [
                'type'=>[10],

                'table' => 'massage_channel_list',

                'name' => 'user_name',

                'filed' => 'top_id',
            ],
            [
                'type'=>[12],

                'table' => 'massage_salesman_list',

                'name'  => 'user_name',

                'filed' => 'top_id',
            ],
            [
                'type'=>[9],

                'table' => 'massage_coach_broker_list',

                'name' => 'user_name',

                'filed' => 'broker_id',
            ]
        ];

        foreach ($arr as $value){

            if(in_array($type,$value['type'])){

                return $value;
            }
        }

        return [];
    }


    /**
     * @param $type
     * @param $dis
     * @param $name
     * @param $limit
     * @功能说明:
     * @author chenniang
     * @DataTime: 2024-08-07 14:59
     */
    public function getIdByNameRecordList($type,$dis,$name,$limit){

        $table_data = $this->getCommTable($type);

        foreach ($dis as $k=>$v){

            $dis[$k][0] = 'a.'.$v[0];
        }
        $map[] = ['a.type','=',16];

        $map[] = ['a.cash','>',0];

        if(in_array($type,[3,8,17,18])){

            $where[] = ['f.coach_name','like','%'.$name.'%'];

            $where[] = ['g.now_coach_name','like','%'.$name.'%'];

            $data = $this->alias('a')
                ->join('massage_service_order_list d','a.order_id = d.id')
                ->join('massage_service_coach_list f','a.top_id = f.id','left')
                ->join('massage_order_coach_change_logs g','if(d.is_add=0,d.id,d.add_pid) = g.order_id AND g.is_new = 1 AND a.top_id=0 ','left')
                ->where($dis)
                ->where(function ($query) use ($where){
                    $query->whereOr($where);
                })
                ->where(function ($query) use ($map){
                    $query->whereOr($map);
                })
                ->field('a.*')
                ->group('a.id')
                ->order('a.order_id desc,a.id desc')
                ->paginate($limit)
                ->toArray();

            return $data;
        }else{

            if(!empty($table_data)){

                $table = $table_data['table'];

                $filed = $table_data['filed'];

                $title = $table_data['name'];

                $dis[] = ["b.$title",'like','%'.$name.'%'];

                $data = $this->alias('a')
                    ->join("$table b","a.$filed = b.id")
                    ->where($dis)
                    ->where(function ($query) use ($map){
                        $query->whereOr($map);
                    })
                    ->field('a.*')
                    ->group('a.id')
                    ->order('a.order_id desc,a.id desc')
                    ->paginate($limit)
                    ->toArray();
            }else{

                $data = $this->alias('a')
                    ->where($dis)
                    ->where(function ($query) use ($map){
                        $query->whereOr($map);
                    })
                    ->field('a.*')
                    ->group('a.id')
                    ->order('a.order_id desc,a.id desc')
                    ->paginate($limit)
                    ->toArray();

            }

            return $data;
        }

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-08-25 23:24
     * @功能说明:记录
     */
    public function recordSelect($type,$dis,$name){

        $table_data = $this->getCommTable($type);

        foreach ($dis as $k=>$v){

            $dis[$k][0] = 'a.'.$v[0];
        }
        $map[] = ['a.type','=',16];

        $map[] = ['a.cash','>',0];

        if(in_array($type,[3,8,17,18])){

            $where[] = ['f.coach_name','like','%'.$name.'%'];

            $where[] = ['g.now_coach_name','like','%'.$name.'%'];

            $data = $this->alias('a')
                ->join('massage_service_order_list d','a.order_id = d.id')
                ->join('massage_service_coach_list f','a.top_id = f.id','left')
                ->join('massage_order_coach_change_logs g','if(d.is_add=0,d.id,d.add_pid) = g.order_id AND g.is_new = 1 AND a.top_id=0 ','left')
                ->where($dis)
                ->where(function ($query) use ($where){
                    $query->whereOr($where);
                })
                ->where(function ($query) use ($map){
                    $query->whereOr($map);
                })
                ->field('a.*')
                ->group('a.id')
                ->order('a.order_id desc,a.id desc')
                ->select()
                ->toArray();

            return $data;
        }else{

            if(!empty($table_data)){

                $table = $table_data['table'];

                $filed = $table_data['filed'];

                $title = $table_data['name'];

                $dis[] = ["b.$title",'like','%'.$name.'%'];

                $data = $this->alias('a')
                    ->join("$table b","a.$filed = b.id")
                    ->where($dis)
                    ->where(function ($query) use ($map){
                        $query->whereOr($map);
                    })
                    ->field('a.*')
                    ->group('a.id')
                    ->order('a.order_id desc,a.id desc')
                    ->select()
                    ->toArray();
            }else{

                $data = $this->alias('a')
                    ->where($dis)
                    ->where(function ($query) use ($map){
                        $query->whereOr($map);
                    })
                    ->field('a.*')
                    ->group('a.id')
                    ->order('a.order_id desc,a.id desc')
                    ->select()
                    ->toArray();

            }

            return $data;
        }

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
     * @DataTime: 2023-03-31 14:08
     * @功能说明:佣金到账
     */
    public function commissionSucessCash($order_id){

        $dis = [

            'order_id' => $order_id,

            'status'   => 1
        ];

        $data = $this->where($dis)->where('type','not in',[7,11,17,18,19,20,21,22])->select()->toArray();

        $user_model  = new User();

        $member_model = new Member();

        if(!empty($data)){

            foreach ($data as $v){

                if($v['type']!=16){
                    //结束所有佣金
                    $res = $this->dataUpdate(['id'=>$v['id']],['status'=>2,'cash_time'=>time()]);

                    if($res==0){

                        return $res;
                    }
                }

                $cash = $v['type']==16?$v['company_cash']:$v['cash'];

                if($cash<=0&&!in_array($v['type'],[2,5,6,16])){

                    continue;
                }

                if(in_array($v['type'],[1,9,14])){

                    if($v['type']==9&&!empty($v['broker_id'])){

                        $water_model = new BrokerWater();

                        $res = $water_model->updateCash($v['uniacid'],$v['broker_id'],$v['cash'],1,$order_id,$v['id']);

                        if($res==false){

                            return 0;
                        }

                    }else{

                        $water_model = new UserWater();

                        $res = $water_model->updateCash($v['uniacid'],$v['top_id'],$v['cash'],1,$order_id,$v['id'],$v['type']);

                        if($res==false){

                            return 0;
                        }
                    }

                }elseif (in_array($v['type'],[2,5,6,13])){
                    //技师车费
                    $water_model = new AdminWater();

                    $res = $water_model->updateCash($v['uniacid'],$v['top_id'],$v['cash'],1);

                    if($res==false){

                        return 0;
                    }
                }elseif (in_array($v['type'],[10])){
                    //渠道商
                    $water_model = new ChannelWater();

                    $res = $water_model->updateCash($v['uniacid'],$v['top_id'],$v['cash'],1,$order_id,$v['id']);

                    if($res==false){

                        return 0;
                    }

                }elseif (in_array($v['type'],[12])){
                    //业务员
                    $water_model = new SalesmanWater();

                    $salesman_model= new Salesman();

                    $res = $salesman_model->where(['id' => $v['top_id']])->update(['total_cash' => Db::raw("total_cash+$cash")]);

                    if ($res == false) {

                        return 0;
                    }
                    $res = $water_model->updateCash($v['uniacid'],$v['top_id'],$v['cash'],1,$order_id,$v['id']);

                    if($res==false){

                        return 0;
                    }

                }elseif(in_array($v['type'],[8])&&!empty($v['top_id'])){
                    //技师车费
                    $water_model = new CoachWater();

                    $res = $water_model->updateCash($v['uniacid'],$v['top_id'],$v['cash'],1,2);

                    if($res==0){

                        return $res;
                    }

                }elseif (in_array($v['type'],[16])){
                    //技师车费
                    $water_model = new CompanyWater();

                    $res = $water_model->addWaterQueue($v['uniacid'],$v['id']);

                    if($res==0){

                        return $res;
                    }

                }elseif (in_array($v['type'],[3])&&!empty($v['top_id'])){
                    //技师服务费
                    $water_model = new CoachWater();

                    $res = $water_model->updateCash($v['uniacid'],$v['top_id'],$v['cash'],1,1);

                    if($res==0){

                        return $res;
                    }
                }elseif(in_array($v['type'],[23])&&!empty($v['top_id'])){
                    //代理商车费，注意是扣除
                    $record_model = new CashUpdateRecord();

                    $res = $record_model->totalUpdateCash($v['uniacid'],3,$v['top_id'],$v['cash'],0,'',1,9,$v['id']);

                    if(!empty($res['code'])){

                        return 0;
                    }
                }
                //分账
                $member_model->adapayCommission($v,$order_id);
            }
        }
        //给平台分账
        $member_model->adaPayMyself($order_id);

        return true;
    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-11-07 16:21
     * @功能说明:渠道商代理佣金到账
     */
    public function channelSuccessCash($order_id){

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

        $data['create_time'] = !empty($data['create_time'])?$data['create_time']:time();

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

                $user_agent_balance = 0;

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
    public function carCommission($order,$status=-1){

        if(isset($order['true_car_price'])&&$order['true_car_price']>0&&$order['free_fare']!=2){

            $find = $this->where(['order_id'=>$order['id']])->where('type','in',[8,13])->find();

            if(!empty($find)){

                return false;
            }

            if(!empty($order['caradmin'])||(empty($order['coach_id'])&&!empty($order['admin_id']))){

                $insert = [

                    'uniacid' => $order['uniacid'],

                    'user_id' => $order['user_id'],

                    'top_id'  => $order['admin_id'],

                    'order_id'=> $order['id'],

                    'order_code' => $order['order_code'],

                    'type'    => 13,

                    'cash'    => $order['true_car_price'],

                    'admin_id'=> $order['admin_id'],

                    'balance' => 0,

                    'status'  => $status,

                    'cash_time' => time(),

                    'update_time' => time(),
                ];

            }else{

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

                    'status'  => $status,

                    'cash_time' => time(),

                    'update_time'=> time(),
                ];
            }

            if(empty($order['coach_id'])){

                $insert['cash_status'] = 0;
            }

            $insert['create_time'] = !empty($order['create_time'])?$order['create_time']:time();

            $res = $this->dataAdd($insert);

            $id = $this->getLastInsID();

            $share_model = new CommShare();
            //添加手续费
            $share_model->addPointData($id,$order,$insert['type'],$insert['top_id']);
            //添加代理商分摊
            if(!empty($order['admin_share_car_price'])){

                $share_model->addData($order['uniacid'],0,$order['admin_share_car_price'],$id,2,$order['id'],$order['admin_id'],5);
            }
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

        if(isset($order['coach_cash'])){

            $find = $this->where(['order_id'=>$order['id']])->where('type','in',[3])->find();

            if(!empty($find)){

                return false;
            }

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

            $insert['create_time'] = !empty($order['create_time'])?$order['create_time']:time();

            $res = $this->dataAdd($insert);

            $id = $this->getLastInsID();

            $share_model = new CommShare();
            //添加手续费
            $share_model->addPointData($id,$order,$insert['type'],$insert['top_id']);
            //添加广告费
            $share_model->addPosterData($id,$order,1,$insert['type'],$insert['top_id']);
            //储值扣款
            $share_model->coachBalanceCash($id,$order,$insert['top_id']);
            //技术服务费
            $share_model->coachSkillCash($id,$order,$insert['top_id']);
            //优惠券分摊
            $share_model->addCouponBearData($id,$order,1,3,$insert['top_id']);
            //储值折扣卡分摊
            $share_model->addBalanceDiscountShare($id,$order,$order['coach_id'],3,1);

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

            $find = $this->where(['order_id'=>$order['id']])->where('type','in',[2])->find();

            if(!empty($find)){

                return false;
            }

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

                $insert['car_cash']   = $order['true_car_price'];

            }

            $insert['create_time'] = !empty($order['create_time'])?$order['create_time']:time();

            $res = $this->dataAdd($insert);

            $id = $this->getLastInsID();

            $share_model = new CommShare();
            //添加手续费
            $share_model->addPointData($id,$order,$insert['type'],$insert['top_id']);
            //添加广告费
            $share_model->addPosterData($id,$order,2,$insert['type'],$insert['top_id']);
            //优惠券分摊
            $share_model->addCouponBearData($id,$order,2,2,$insert['top_id']);
            //注意这是一个显示 实际并未扣该笔佣金 而是直接扣的代理商余额
            if($order['free_fare']==3){

                $share_model->addCarData($id,$order,2);
            }
            //储值折扣卡分摊
            $share_model->addBalanceDiscountShare($id,$order,$order['admin_id'],2,2);
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

            $find = $this->where(['order_id'=>$order['id']])->where('type','in',[5])->find();

            if(!empty($find)){

                return false;
            }

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
            $insert['create_time'] = !empty($order['create_time'])?$order['create_time']:time();

            $res = $this->dataAdd($insert);

            $id  = $this->getLastInsID();

            $share_model = new CommShare();
            //添加手续费
            $share_model->addPointData($id,$order,$insert['type'],$insert['top_id']);
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

            $find = $this->where(['order_id'=>$order['id']])->where('type','in',[6])->find();

            if(!empty($find)){

                return false;
            }

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
            $insert['create_time'] = !empty($order['create_time'])?$order['create_time']:time();

            $res = $this->dataAdd($insert);

            $id  = $this->getLastInsID();

            $share_model = new CommShare();
            //添加手续费
            $share_model->addPointData($id,$order,$insert['type'],$insert['top_id']);
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
     * @param $v
     * @param $pay_order
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-31 00:35
     */
    public function adminCashCustom($v,$pay_order){

        if($v['type']==2){

            if($v['city_type']==1){

                $pay_order['city_balance'] = $v['balance'];

                $pay_order['admin_balance_name'] = 'city_balance';

                $pay_order['admin_cash_name']    = 'city_cash';
            }else{

                $pay_order['district_balance']   = $v['balance'];

                $pay_order['admin_balance_name'] = 'district_balance';

                $pay_order['admin_cash_name']    = 'district_cash';
            }

        }

        if($v['type']==5){

            $pay_order['city_balance']       = $v['balance'];

            $pay_order['level_balance_name'] = 'city_balance';

            $pay_order['level_cash_name']    = 'city_cash';
        }

        return $pay_order;

    }


    /**
     * @param $order
     * @功能说明:获取主订单的各类佣金比例
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-12-27 15:05
     */
    public function getCommBalance($pay_order){

        $level_cash_data = $this->where(['order_id'=>$pay_order['id'],'status'=>1])->where('type','in',[1,2,3,5,6,9,10,11,12,14,16])->select()->toArray();

        $share_model = new CommShare();

        foreach ($level_cash_data as $v) {

            $cash_text = $this->getTypeText($v['type']);

            if (in_array($v['type'], [5, 6])) {

                $pay_order[$cash_text['admin_id']] = $v['admin_id'];
            }

            if (in_array($v['type'], [9, 10, 12,1,14])) {

                $pay_order[$cash_text['admin_id']] = $v['top_id'];

                $pay_order[$cash_text['coach_share_balance']] = $share_model->getShareBalance($v['id'], 1);

                $pay_order[$cash_text['admin_share_balance']] = $share_model->getShareBalance($v['id'], 2);

                $pay_order[$cash_text['company_share_balance']]= $share_model->getShareBalance($v['id'], 3);

                if(isset($cash_text['reseller_id'])){

                    $pay_order[$cash_text['reseller_id']] = $v['reseller_id'];
                }
            }

            $pay_order[$cash_text['balance']] = $v['balance'];

            $pay_order = $this->adminCashCustom($v, $pay_order);
        }

        return $pay_order;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-06-13 14:50
     * @功能说明:订单退款修改佣金记录
     */
    //type 1分销 2加盟商 3技师 4分销商 5上级分销商 6省代分销 7技师拉用户充值余额 8车费 9合伙人 10渠道商 11平台 12业务员 13车费（代理商）
    public function refundCash($pay_order,$comm_type=2){

        $order_model = new Order();

        $share_model = new CommShare();

        $level_cash_data = $this->where(['order_id'=>$pay_order['id'],'status'=>1])->where('type','in',[1,2,3,5,6,9,10,11,12,14])->select()->toArray();

        $pay_order = $this->getCommBalance($pay_order);
        //升级订单
        if($comm_type==3){

            $user_model= new User();
            //分销
            $pay_order = $user_model->getUserPid($pay_order);
        }
        //查询有无二级市级代理佣金或者省代
        if(!empty($level_cash_data)){
            //修改佣金信息
            $cash_data = $order_model->getCashData($pay_order,$comm_type);

            $cash_data = $cash_data['data'];

            $cash_order_update = [

                'admin_cash'  => $cash_data['admin_cash'],

                'coach_cash'  => $cash_data['coach_cash'],

                'company_cash'=> $cash_data['company_cash'],

                'user_cash'   => $cash_data['user_c_cash'],
            ];

            $arr = [1,2,3,5,6,9,10,11,12,14,16];

            foreach ($arr as $value){

                $cash_text = $this->getTypeText($value);

                if(key_exists($cash_text['cash'],$cash_data)){

                    $update = [

                        'cash' => $cash_data[$cash_text['cash']]
                    ];
                    //修改各类佣金记录
                    $this->dataUpdate(['order_id'=>$pay_order['id'],'type'=>$value,'status'=>1],$update);

                    $comm_data = $this->dataInfo(['order_id'=>$pay_order['id'],'type'=>$value,'status'=>1]);

                    if(!empty($comm_data)){
                        //分销员，二级分销员 渠道商 合伙人 业务员 对应的分摊金额可能会变
                        if(in_array($value,[9,10,12,1,14])){

                            if(key_exists($cash_text['coach_share_cash'],$cash_data)){

                                $share_model->dataUpdate(['comm_id'=>$comm_data['id'],'type'=>1],['share_cash'=>$cash_data[$cash_text['coach_share_cash']]]);
                            }

                            if(key_exists($cash_text['admin_share_cash'],$cash_data)){

                                $share_model->dataUpdate(['comm_id'=>$comm_data['id'],'type'=>2],['share_cash'=>$cash_data[$cash_text['admin_share_cash']]]);
                            }

                            if(key_exists($cash_text['company_share_cash'],$cash_data)){

                                $share_model->dataUpdate(['comm_id'=>$comm_data['id'],'type'=>3],['share_cash'=>$cash_data[$cash_text['company_share_cash']]]);
                            }
                        }

                        if(key_exists($cash_text['point'],$cash_data)){
                            //修改手续费
                            $share_model->dataUpdate(['comm_id'=>$comm_data['id'],'cash_type'=>1],['share_cash'=>$cash_data[$cash_text['point']]]);
                        }
                        //修改广告分摊
                        if(in_array($value,[2,3,11,16])&&key_exists($cash_text['poster'],$cash_data)){

                            $share_model->dataUpdate(['comm_id'=>$comm_data['id'],'cash_type'=>2],['share_cash'=>$cash_data[$cash_text['poster']]]);
                        }
                        //优惠券分摊
                        if($pay_order['coupon_bear_type']==2&&in_array($value,[2,3,11,16])&&key_exists($cash_text['coupon_bear'],$cash_data['coupon_bear'])){

                            $share_model->dataUpdate(['comm_id'=>$comm_data['id'],'cash_type'=>6],['share_cash'=>$cash_data['coupon_bear'][$cash_text['coupon_bear']]]);
                        }
                        //储值扣款和技术服务费
                        if(in_array($value,[3])){

                            if(key_exists($cash_text['comm_coach_balance'],$cash_data)) {

                                $share_model->dataUpdate(['comm_id' => $comm_data['id'], 'cash_type' => 3], ['share_cash' => $cash_data[$cash_text['comm_coach_balance']]]);
                            }

                            if(key_exists($cash_text['skill_balance'],$cash_data)) {

                                $share_model->dataUpdate(['comm_id' => $comm_data['id'], 'cash_type' => 4], ['share_cash' => $cash_data[$cash_text['skill_balance']]]);
                            }
                        }
                        //储值折扣卡分摊
                        if(in_array($value,[2,3,16])&&key_exists($cash_text['balance_discount_cash'],$cash_data)){

                            $share_model->dataUpdate(['comm_id' => $comm_data['id'], 'cash_type' => 7], ['share_cash' => $cash_data[$cash_text['balance_discount_cash']]]);
                        }
                    }
                }
            }
            //代理商分摊车费
            if(isset($cash_data['admin_share_car_price'])){

                $share_model->dataUpdate(['order_id'=>$pay_order['id'],'cash_type'=>5],['share_cash'=>$cash_data['admin_share_car_price']]);
            }

            $res = $order_model->dataUpdate(['id'=>$pay_order['id']],$cash_order_update);

        }

        $order_model = new Order();

        $company_cash = $order_model->companySurplusCash($pay_order['id']);

        $this->dataUpdate(['type'=>16,'order_id'=>$pay_order['id']],['company_cash'=>$company_cash]);

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

            case 1:
                $arr['cash'] = 'user_c_cash';

                $arr['balance'] = 'user_reseller_balance';

                $arr['point'] = 'user_point_cash';

                $arr['coach_share_balance'] = 'reseller_coach_balance';

                $arr['admin_share_balance'] = 'reseller_admin_balance';

                $arr['company_share_balance']= 'reseller_company_balance';

                $arr['coach_share_cash'] = 'reseller_coach_cash';

                $arr['admin_share_cash'] = 'reseller_admin_cash';

                $arr['company_share_cash']= 'reseller_company_cash';

                $arr['admin_id']= 'user_top_id';

                $arr['reseller_id'] = 'user_reseller_id';

                break;

            case 2:
                $arr['cash'] = 'admin_cash';

                $arr['balance']  = 'admin_balance';

                $arr['admin_id'] = 'admin_pid';

                $arr['point'] = 'admin_point_cash';
                //广告分摊
                $arr['poster'] = 'share_poster_admin';

                $arr['coupon_bear'] = 'admin_cash';

                $arr['balance_discount_cash'] = 'admin_balancediscount_share_cash';

                break;
            case 3:
                $arr['cash'] = 'coach_cash';

                $arr['balance'] = 'coach_balance';

                $arr['point'] = 'coach_point_cash';
                //广告分摊
                $arr['poster'] = 'share_poster_coach';

                $arr['comm_coach_balance'] = 'comm_coach_cash';

                $arr['skill_balance'] = 'skill_cash';

                $arr['coupon_bear'] = 'coach_cash';

                $arr['balance_discount_cash'] = 'coach_balancediscount_share_cash';

                break;

            case 5:
                $arr['cash'] = 'level_cash';

                $arr['balance'] = 'level_balance';

                $arr['admin_id'] = 'admin_pid';

                $arr['point'] = 'city_point_cash';

                break;

            case 6:
                $arr['cash'] = 'p_level_cash';

                $arr['balance'] = 'p_level_balance';

                $arr['admin_id'] = 'p_admin_pid';

                $arr['point'] = 'province_point_cash';

                break;

            case 8:

                $arr['cash']  = 'true_car_price';

                $arr['point'] = 'car_point_cash';

                break;
            case 9:
                $arr['cash'] = 'partner_cash';

                $arr['balance'] = 'coach_agent_balance';

                $arr['admin_id'] = 'partner_id';

                $arr['coach_share_balance'] = 'partner_coach_balance';

                $arr['admin_share_balance'] = 'partner_admin_balance';

                $arr['company_share_balance']= 'partner_company_balance';

                $arr['coach_share_cash'] = 'partner_coach_cash';

                $arr['admin_share_cash'] = 'partner_admin_cash';

                $arr['company_share_cash']= 'partner_company_cash';

                $arr['point'] = 'partner_point_cash';

                break;
            case 10:
                $arr['cash'] = 'channel_cash';

                $arr['balance'] = 'channel_balance';

                $arr['admin_id'] = 'channel_id';

                $arr['coach_share_balance'] = 'channel_coach_balance';

                $arr['admin_share_balance'] = 'channel_admin_balance';

                $arr['company_share_balance']= 'channel_company_balance';

                $arr['coach_share_cash'] = 'channel_coach_cash';

                $arr['admin_share_cash'] = 'channel_admin_cash';

                $arr['company_share_cash']= 'channel_company_cash';

                $arr['point'] = 'channel_point_cash';

                break;

            case 11:
                $arr['cash'] = 'company_cash';

                $arr['balance'] = 'company_balance';

                $arr['point'] = 'company_point_cash';
                //广告分摊
                $arr['poster'] = 'share_poster_company';

                $arr['coupon_bear'] = 'company_cash';

                break;

            case 12:
                $arr['cash'] = 'salesman_cash';

                $arr['balance']  = 'salesman_balance';

                $arr['admin_id'] = 'salesman_id';

                $arr['coach_share_balance']  = 'salesman_coach_balance';

                $arr['admin_share_balance']  = 'salesman_admin_balance';

                $arr['company_share_balance']= 'salesman_company_balance';

                $arr['coach_share_cash']  = 'salesman_coach_cash';

                $arr['admin_share_cash']  = 'salesman_admin_cash';

                $arr['company_share_cash']= 'salesman_company_cash';

                $arr['point'] = 'salesman_point_cash';

                break;
            case 13:

                $arr['cash']  = 'true_car_price';

                $arr['point'] = 'car_point_cash';

                break;

            case 14:
                $arr['cash'] = 'level_reseller_cash';

                $arr['balance'] = 'level_reseller_balance';

                $arr['point'] = 'level_reseller_point_cash';

                $arr['coach_share_balance'] = 'level_reseller_coach_balance';

                $arr['admin_share_balance'] = 'level_reseller_admin_balance';

                $arr['company_share_balance']= 'level_reseller_company_balance';

                $arr['coach_share_cash'] = 'level_reseller_coach_cash';

                $arr['admin_share_cash'] = 'level_reseller_admin_cash';

                $arr['company_share_cash']= 'level_reseller_company_cash';

                $arr['admin_id']= 'level_top_id';

                $arr['reseller_id'] = 'level_reseller_id';
                break;
            case 15:

                $arr['point'] = 'inv_reseller_point_cash';

                break;
            case 16:
                $arr['cash'] = 'company_cash';

                $arr['balance'] = 'company_balance';

                $arr['point'] = 'company_point_cash';
                //广告分摊
                $arr['poster'] = 'share_poster_company';

                $arr['coupon_bear'] = 'company_cash';

                $arr['balance_discount_cash'] = 'company_balancediscount_share_cash';

                break;
            default:

                $arr = [];
        }

        return $arr;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-22 10:12
     * @功能说明:获取佣金相关参数
     */
    public function commissionData($type=1){

        $arr = [
            //技师佣金
             [

                'action_name' => "getCoachCash",

                'parameter'   => 'coach_balance',

                'point'       => 'coach_point_cash',

            ],
            //平台抽成
            [

                'action_name' => 'getCompanyCash',

                'parameter'   => 'admin_balance',

                'point'       => 'company_point_cash'
            ],
            //省代
            [

                'action_name' => 'getProvinceCash',

                'parameter'   => 'p_level_balance',

                'point'       => 'province_point_cash'


            ],//城市代理
            [

                'action_name' => 'getCityCash',

                'parameter'   => 'level_balance',

                'point'       => 'city_point_cash'

            ],
            [


                'action_name' => $type!=2?'getUserCash':'getUserCashRefund',

                'parameter'   => 'user_agent_balance',

                'point'       => 'user_point_cash'
            ],
            //合伙人
            [

                'action_name' => 'getPartnerCash',

                'parameter'   => 'coach_agent_balance',

                'point'       => 'partner_point_cash'

            ],
            //渠道商
            [
                'action_name' => 'getChannelCash',

                'parameter'   => 'channel_balance',

                'point'       => 'channel_point_cash'

            ],
            //业务员
            [

                'action_name' => 'getSalesmanCash',

                'parameter'   => 'salesman_balance',

                'point'       => 'salesman_point_cash'

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

        $channel_model = new ChannelList();

        $custom_model  = new CustomBalance();

        $user_model    = new User();

        $coach = $coach_model->dataInfo(['id'=>$order['coach_id']]);

        $coach_level = $custom_model->getCoachCustomBalance($order['coach_id']);

        if(empty($coach_level)){
            //技师等级
            $coach_level = $coach_model->getCoachLevel($order['coach_id'],$order['uniacid']);

            if(empty($coach_level)&&!empty($order['coach_id'])){

                return ['code'=>300];
            }
        }
        //加钟基础值特别设置
        if(!empty($coach_level)&&$order['is_add']==1&&$coach_level['add_balance_status']==1){

            $coach_level['balance'] = $coach_level['add_basis_balance'];
        }

        $coach_level['balance'] = !empty($coach_level)?$coach_level['balance']:0;
        //技师佣金比列
        $order['coach_balance'] = $coach_level['balance'];

        $order['admin_id'] = $order['is_store_admin'] = 0;

        $admin_id = !empty($coach['admin_id'])?$coach['admin_id']:$admin_id;

        if(empty($admin_id)&&!empty($order['store_id'])){

            $store_model = new \app\store\model\StoreList();

            $admin_id = $store_model->where(['id'=>$order['store_id']])->value('admin_id');

            $order['is_store_admin'] = 1;
        }
        //代理商各类分销比例
        $order = $admin_model->agentBalanceData($admin_id,$order);
        //加钟的时候比例可能是特殊设置
        $order['coach_balance'] = $clock_model->getCoachBalance($order,$order['coach_balance']);
        //合伙人
        $order = $coach_model->partnerBalance($coach,$order);
        //渠道商|业务员
        $order = $channel_model->channelBalance($order);
        //分销
        $order = $user_model->getUserPid($order);
        //渠道商分销商佣金是否叠加
        $order = $this->channelFxSuperpose($order);

        return $order;
    }










    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-22 10:19
     * @功能说明:计算每类佣金的金额
     *
     */
    public function cashData($order,$type){

        $list = $this->commissionData($type);

        $order_data_model = new OrderData();

        $order_goods_model= new OrderGoods();

        $order_data = $order_data_model->dataInfo(['order_id'=>$order['id'],'uniacid'=>$order['uniacid']]);

        $order['pay_point'] = $order_data['pay_point'];

        $order['poster_coach_share'] = $order_data['poster_coach_share'];

        $order['poster_admin_share'] = $order_data['poster_admin_share'];

        $order['comm_coach_balance'] = $order_data['comm_coach_balance'];

        $order['skill_balance']      = $order_data['skill_balance'];

        $order['coupon_bear']['coupon_bear_coach'] = $order_data['coupon_bear_coach'];

        $order['coupon_bear']['coupon_bear_admin'] = $order_data['coupon_bear_admin'];

        $order['coupon_bear']['coupon_bear_company'] = 100-$order['coupon_bear']['coupon_bear_admin']-$order['coupon_bear']['coupon_bear_coach'];

        if(empty($order['coupon_id'])){

            $order['coupon_bear_type'] = 1;
        }

        if((isset($order['coupon_bear_type'])&&$order['coupon_bear_type']==2&&!empty($order['coupon_id']))||$order_data['balance_discount_cash']>0){

            $order['discount_init'] = 1;
        }else{

            $order['discount_init'] = 0;
        }

        if($order['discount_init']==1){

            $true_service_price = $order['true_service_price'];

            $material_price = $order['material_price'];

            $refund_info = $order_goods_model->getRefundOrderGoodsInit($order['id']);

            $order['true_service_price'] = $order['init_service_price']-$refund_info['refund_service_price']-$refund_info['empty_service_cash']-$refund_info['comm_service_cash'];

            $order['material_price']     = $order['init_material_price']-$refund_info['refund_material_price']-$refund_info['empty_material_cash']-$refund_info['comm_material_cash'];
        }

        if(isset($order['coupon_bear_type'])&&$order['coupon_bear_type']==2&&!empty($order['coupon_id'])){

            $order['coupon_bear']['coach_discount']   = ($order['init_service_price'] - $order['service_price'])-$refund_info['refund_service_discount'];

            $order['coupon_bear']['material_discount']= ($order['init_material_price'] - $order['start_material_price'])-$refund_info['refund_material_discount'];

            $order['coupon_bear']['coach_discount']   = $order['coupon_bear']['coach_discount']>0?$order['coupon_bear']['coach_discount']:0;

            $order['coupon_bear']['material_discount']= $order['coupon_bear']['material_discount']>0?$order['coupon_bear']['material_discount']:0;
            //平台分摊时候的折扣
            $order['coupon_bear']['company_discount'] =$order['coupon_bear']['admin_discount'] = $order['coupon_bear']['coach_discount'] ;
        }

        if($order['material_type']==1){

            $order['true_service_price'] = $order['true_service_price']+$order['material_price'];
        }
        //广告费
        $order['poster_cash'] = round($order['true_service_price']*$order_data['poster_point']/100,2);

        foreach ($list as $key=>$value){

            $balance = isset($order[$value['parameter']])?$order[$value['parameter']]:0;

            $order['surplus_cash'] = $key==0?$order['true_service_price']:$order['surplus_cash'];

            $action_name = $value['action_name'];

            $order = $this->$action_name($balance,$order,$order['surplus_cash']);
        }

        if($order['material_type']==1){

            $order['true_service_price'] = $order['true_service_price']-$order['material_price'];
        }

        if($order['discount_init']==1){

            $order['true_service_price'] = $true_service_price;

            $order['material_price'] = $material_price;
        }

        $order['admin_cash'] = $order['surplus_cash'];
        //如果没有代理商 剩下的就是平台的
        if(empty($order['admin_id'])){

            $order['company_cash'] = $order['surplus_cash'];
        }
        //计算手续费
        if(!empty($order['pay_point'])){
            //type 1分销 2加盟商 3技师 4分销商 5上级分销商 6省代分销 7技师拉用户充值余额 8车费 9合伙人 10渠道商 11平台 12业务员 13车费（代理商） 14二级分销
            //没有13不然车费会重复扣
            $arr = [1,2,3,5,6,8,9,10,12,14];

            foreach ($arr as $value){

                $info = $this->getTypeText($value);
                //说明有这个佣金
                if(isset($order[$info['cash']])){
                    //手续费
                    $point_cash = round($order[$info['cash']]*$order['pay_point']/100,2);

                    $order[$info['cash']] -= $point_cash;

                    $order[$info['cash']] = round($order[$info['cash']],2);

                    $order[$info['point']]= $point_cash;
                }
            }
        }

        return $order;
    }


    /**
     * @param $balance
     * @param $order
     * @param $cash 16。28
     * @功能说明:合伙人佣金
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-22 11:14
     *2.6
     * 市1.4  省1  技师5  合伙人2.6 县5  经济1  渠道商4.4 业务3.4
     */
    public function getPartnerCash($balance,$order,$cash){

        if(!empty($order['partner_id'])){

            $order['partner_company_cash'] = 0;
            //合伙人提成
            $order['partner_cash'] = round($balance*$order['true_service_price']/100,2);
            //处理分摊金额
            $order = $this->getShareData($order,1);

        }
        return $order;
    }


    /**
     * @param $cash
     * @param $coach_cash
     * @param $surplus_cash
     * @param $company_cash
     * @param $coach_share_balane
     * @param $agent_share_balance
     * @param $company_share_balance
     * @param $admin_id
     * @功能说明:计算分摊的金额
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-01-31 16:27
     */
    public function getShareData($order,$type){

        $dataPath = APP_PATH  . 'massage/info/ShareData.php' ;

        $text = include $dataPath ;

        foreach ($text as $value){

            if($value['type']==$type){

                $arr = $value;
            }
        }

        if(empty($arr)){

            return $order;
        }

        $cash = round($order[$arr['cash']],2);
        //技师需要分摊的钱
        $coach_cash = round($cash*$order[$arr['coach_share_balance']]/100,2);

        $order[$arr['cash']] -= $coach_cash;

        $coach_cash = $coach_cash<$order['coach_cash']?$coach_cash:$order['coach_cash'];

        $order[$arr['cash']] += $coach_cash;

        $order['coach_cash'] -= $coach_cash;
        //技师承担了多少钱
        $order[$arr['coach_share_cash']] = $coach_cash;
        //没有代理商就由平台承担
        if(empty($order['admin_id'])){
            //平台需要承担的费用
            $admin_cash = round($cash*(100-$order[$arr['coach_share_balance']])/100,2);

            $order[$arr['cash']] -= $admin_cash;

            $admin_cash = $admin_cash<$order['surplus_cash']?$admin_cash:$order['surplus_cash'];

            $admin_cash = $admin_cash>0?$admin_cash:0;

            $order[$arr['cash']] += $admin_cash;

            $order['surplus_cash'] -= $admin_cash;
            //平台承担佣金
            $order[$arr['company_share_cash']] = $admin_cash;

            $order[$arr['company_share_balance']] = 100-$order[$arr['coach_share_balance']];

            $order[$arr['agent_share_balance']] = 0;

        }else{

            $admin_cash = round($cash*$order[$arr['agent_share_balance']]/100,2);

            $order[$arr['cash']] -= $admin_cash;
            //上级代理商承担
            if(getConfigSetting($order['uniacid'],'cash_share_admin')==1||in_array($type,[1,4,5])){

                $admin_cash = $admin_cash<$order['surplus_cash']?$admin_cash:$order['surplus_cash'];

                $admin_cash = $admin_cash>0?$admin_cash:0;

                $order[$arr['cash']] += $admin_cash;

                $order['surplus_cash'] -= $admin_cash;

                $order[$arr['admin_id']] = $order['admin_id'];

            }else{
                //由城市代理商承担
                if(isset($order['city_admin'])&&isset($order[$order['city_admin']])){

                    $admin_cash = $admin_cash<$order[$order['city_admin']]?$admin_cash:$order[$order['city_admin']];

                    $admin_cash = $admin_cash>0?$admin_cash:0;

                    $order[$arr['cash']] += $admin_cash;

                    $order[$order['city_admin']] -= $admin_cash;

                    $order[$arr['admin_id']] = $order[$order['city_admin_id']];
                }
            }

            $order[$arr['agent_share_cash']] = $admin_cash;
            //平台承担部分
            $company_balance = $order[$arr['company_share_balance']];

            if($company_balance>0){

                $admin_cash = round($cash*$company_balance/100,2);

                $order[$arr['cash']] -= $admin_cash;

                $admin_cash = $admin_cash<$order['company_cash']?$admin_cash:$order['company_cash'];

                $admin_cash = $admin_cash>0?$admin_cash:0;

                $order[$arr['cash']] += $admin_cash;

                $order['company_cash'] -= $admin_cash;

                $order[$arr['company_share_cash']] = $admin_cash;
            }
        }

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
     * @param $order
     * @param $cash
     * @功能说明:区县佣金
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-21 18:20
     */
    public function getCityCash($balance,$order,$cash){
        //上级代理提成
        $order['level_cash'] = round($balance*$order['true_service_price']/100,2);

        $order['level_cash'] = $order['level_cash'] - $cash>0?$cash:$order['level_cash'];

        $order['surplus_cash'] = $cash - $order['level_cash'];

        if(empty($order['admin_id'])){

            $order['company_cash'] = &$order['surplus_cash'];

            $order['poster_company_share'] = 100-$order['poster_coach_share'];

            if($order['free_fare']==1){

                $order['company_cash'] -= $order['car_price'];
            }

        }else{
            //储值卡折扣分摊
            if(isset($order['pay_model'])&&$order['pay_model']==4){

                $balance_share_model = new OrderShare();

                $share_cash = $balance_share_model->where(['p_order_id'=>$order['id']])->where('status','>',-1)->sum('admin_share_cash');

                $share_cash = $share_cash>$order['surplus_cash']?$order['surplus_cash']:$share_cash;

                $order['surplus_cash'] = $order['surplus_cash']-$share_cash;

                $order['admin_balancediscount_share_cash'] = $share_cash;
            }

            $order['poster_company_share'] = 100-$order['poster_coach_share']-$order['poster_admin_share'];
            //分摊广告费
            $order['share_poster_admin'] = round($order['poster_cash']*$order['poster_admin_share']/100,2);

            $order['share_poster_admin'] = $order['share_poster_admin']>$order['surplus_cash']?$order['surplus_cash']:$order['share_poster_admin'];

            $order['share_poster_admin'] = $order['share_poster_admin']>0?$order['share_poster_admin']:0;

            $order['surplus_cash'] = $order['surplus_cash']-$order['share_poster_admin'];
            //代理商优惠券分摊
            if($order['coupon_bear_type']==2){

                $order['coupon_bear']['admin_cash'] = round($order['coupon_bear']['coupon_bear_admin']*$order['coupon_bear']['admin_discount']/100,2);

                $order['coupon_bear']['admin_cash'] = $order['coupon_bear']['admin_cash']<$order['surplus_cash']?$order['coupon_bear']['admin_cash']:$order['surplus_cash'];

                $order['coupon_bear']['admin_cash'] = $order['coupon_bear']['admin_cash']>0?$order['coupon_bear']['admin_cash']:0;

                $order['surplus_cash'] = $order['surplus_cash']-$order['coupon_bear']['admin_cash'];
            }
        }
        //储值卡折扣分摊
        if(isset($order['pay_model'])&&$order['pay_model']==4){

            $balance_share_model = new OrderShare();

            $share_cash = $balance_share_model->where(['p_order_id'=>$order['id']])->where('status','>',-1)->sum('company_share_cash');

            $share_cash = $share_cash>$order['company_cash']?$order['company_cash']:$share_cash;

            $order['company_cash'] = $order['company_cash']-$share_cash;

            $order['company_balancediscount_share_cash'] = $share_cash;
        }

        $order['share_poster_company'] = round($order['poster_cash']*$order['poster_company_share']/100,2);

        $order['share_poster_company'] = $order['share_poster_company']>$order['company_cash']?$order['company_cash']:$order['share_poster_company'];

        $order['share_poster_company'] = $order['share_poster_company']>0?$order['share_poster_company']:0;

        $order['company_cash'] = $order['company_cash']-$order['share_poster_company'];
        //代理商优惠券分摊
        if($order['coupon_bear_type']==2){

            $order['coupon_bear']['company_cash'] = round($order['coupon_bear']['coupon_bear_company']*$order['coupon_bear']['company_discount']/100,2);

            $order['coupon_bear']['company_cash'] += $order['coupon_bear']['material_discount'];

            $order['coupon_bear']['company_cash'] = $order['coupon_bear']['company_cash']<$order['company_cash']?$order['coupon_bear']['company_cash']:$order['company_cash'];

            $order['coupon_bear']['company_cash'] = $order['coupon_bear']['company_cash']>0?$order['coupon_bear']['company_cash']:0;

            $order['company_cash'] = $order['company_cash']-$order['coupon_bear']['company_cash'];
        }

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

        $coach_cash = $order['coach_cash'];
        //储值卡折扣分摊
        if(isset($order['pay_model'])&&$order['pay_model']==4){

            $balance_share_model = new OrderShare();

            $share_cash = $balance_share_model->where(['p_order_id'=>$order['id']])->where('status','>',-1)->sum('coach_share_cash');

            $share_cash = $share_cash>$order['coach_cash']?$order['coach_cash']:$share_cash;

            $order['coach_cash'] = $order['coach_cash']-$share_cash;

            $order['coach_balancediscount_share_cash'] = $share_cash;
        }
        //余额支付 技师可能有余额扣款
        if(isset($order['pay_model'])&&$order['pay_model']==2&&isset($order['comm_coach_balance'])&&$order['comm_coach_balance']>0){

            $comm_coach_balance_cash = round($order['coach_cash']*$order['comm_coach_balance']/100,2);

            $comm_coach_balance_cash = $comm_coach_balance_cash>$order['coach_cash']?$order['coach_cash']:$comm_coach_balance_cash;

            $order['coach_cash'] = $order['coach_cash']-$comm_coach_balance_cash;

            $order['comm_coach_cash'] = $comm_coach_balance_cash;
        }
        //技术服务费
        if(!empty($order['coach_id'])&&isset($order['skill_balance'])&&$order['skill_balance']>0){

            $skill_cash = round($coach_cash*$order['skill_balance']/100,2);

            $skill_cash = $skill_cash>$order['coach_cash']?$order['coach_cash']:$skill_cash;

            $order['coach_cash'] = $order['coach_cash']-$skill_cash;

            $order['skill_cash'] = $skill_cash;
        }
        //第二种物料费规则
        if($order['material_type']==1){

            $order['coach_cash'] = $order['coach_cash']-$order['material_price'];
        }
        //优惠券分摊
        if($order['coupon_bear_type']==2){

            $order['coupon_bear']['coach_cash'] = round($order['coupon_bear']['coupon_bear_coach']*$order['coupon_bear']['coach_discount']/100,2);

            $order['coupon_bear']['coach_cash'] = $order['coupon_bear']['coach_cash']<$order['coach_cash']?$order['coupon_bear']['coach_cash']:$order['coach_cash'];

            $order['coach_cash'] = $order['coach_cash']-$order['coupon_bear']['coach_cash'];
        }

        $order['coach_cash'] = $order['coach_cash']>0?$order['coach_cash']:0;
        //分摊广告费
        $order['share_poster_coach'] = round($order['poster_cash']*$order['poster_coach_share']/100,2);

        $order['share_poster_coach'] = $order['share_poster_coach']>$order['coach_cash']?$order['coach_cash']:$order['share_poster_coach'];

        $order['coach_cash'] = $order['coach_cash']-$order['share_poster_coach'];

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

        if(!empty($order['channel_id'])){

            $order['channel_company_cash'] = 0;
            //上级代理提成
            $order['channel_cash'] = round($balance*$order['true_service_price']/100,2);
            //处理分摊金额
            $order = $this->getShareData($order,2);

            return $order;
        }

        return $order;
    }



    /**
     * @param $balance
     * @param $order
     * @param $cash
     * @功能说明:业务员佣金
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-21 18:05
     */
    public function getSalesmanCash($balance,$order,$cash){

        if(!empty($order['salesman_id'])){
            //上级代理提成
            $order['salesman_cash'] = round($balance*$order['true_service_price']/100,2);
            //处理分摊金额
            $order = $this->getShareData($order,3);

            return $order;
        }

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

        $total_cash = $total_level_cash = 0;

        $reseller_cash_type = getConfigSetting($order['uniacid'],'reseller_cash_type');

        if(!empty($order['user_top_id'])){

            $reseller_model = new DistributionList();

            $ser_model  = new Service();

            $admin_id = $reseller_model->where(['user_id'=>$order['user_top_id'],'status'=>2])->value('admin_id');

            if(!empty($order['level_top_id'])){

                $l_admin_id = $reseller_model->where(['user_id'=>$order['level_top_id'],'status'=>2])->value('admin_id');
            }

            $addclockBalance_model = new AddClockBalance();
//            if(!empty($balance)&&$balance>0){
//                //这里是单独客户兼容版本 可以不用管
//                $total_cash = $balance*$order['true_service_price'];
//
//            }else{

                foreach ($order['order_goods'] as &$v){

                    if(isset($order['discount_init'])&&$order['discount_init']==1){

                        $true_price = $v['price'];

                        $material_price = $v['init_material_price'];
                    }else{

                        $true_price = $v['true_price'];

                        $material_price = $v['material_price'];
                    }
                    //按服务
                    if($reseller_cash_type==1){
                        //查看是否有分销
                        $ser = $ser_model->dataInfo(['id'=>$v['goods_id']]);
                        //一级分销
                        $v['user_reseller_balance']  = $ser['com_balance'];
                        //二级分销
                        $v['level_reseller_balance'] = !empty($order['level_top_id'])?$ser['level_balance']:0;
                    }else{
                        //一级分销
                        $v['user_reseller_balance']  = $order['user_reseller_balance'];
                        //二级分销
                        $v['level_reseller_balance'] = !empty($order['level_top_id'])?$order['level_reseller_balance']:0;
                    }
                    //没设置就用全局设置
                    if($v['user_reseller_balance']<0){

                        $v['user_reseller_balance'] = getConfigSetting($order['uniacid'],'user_agent_balance');

                        $v['user_reseller_balance'] = $v['user_reseller_balance']>0?$v['user_reseller_balance']:0;
                    }

                    if($v['level_reseller_balance']<0){

                        $v['level_reseller_balance'] = getConfigSetting($order['uniacid'],'user_level_balance');

                        $v['level_reseller_balance'] = $v['level_reseller_balance']>0?$v['level_reseller_balance']:0;
                    }

                    $v['user_reseller_balance'] = $addclockBalance_model->getObjBalance($order,$v['user_reseller_balance'],1,$admin_id);

                    if(!empty($order['level_top_id'])){

                        $v['level_reseller_balance']= $addclockBalance_model->getObjBalance($order,$v['level_reseller_balance'],1,$l_admin_id);
                    }

                    if($order['material_type']==1){

                        $v['user_reseller_cash']  = ($true_price+$material_price)*$v['user_reseller_balance']/100*$v['num'];

                        $v['level_reseller_cash'] = ($true_price+$material_price)*$v['level_reseller_balance']/100*$v['num'];

                    }else{

                        $v['user_reseller_cash']   = $true_price*$v['user_reseller_balance']/100*$v['num'];

                        $v['level_reseller_cash'] = $true_price*$v['level_reseller_balance']/100*$v['num'];
                    }

                    $total_cash       += $v['user_reseller_cash'];

                    $total_level_cash += $v['level_reseller_cash'];
                }
           // }
        }

        $order['user_c_cash'] = $total_cash;

        $order['level_reseller_cash'] = $total_level_cash;

        if($total_cash<=0){

            $order['user_top_id'] = 0;
        }
        if($total_level_cash<=0){

            $order['level_top_id'] = 0;
        }

        if(!empty($order['user_top_id'])){
            //处理分摊金额
            $order = $this->getShareData($order,4);
        }
        if(!empty($order['level_top_id'])){
            //处理分摊金额
            $order = $this->getShareData($order,5);
        }

        return $order;

    }








    /**
     * @param $balance
     * @param $order
     * @param $cash
     * @功能说明:升级订单时候的分销
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-12-27 15:35
     */
    public function getUserCashUp($balance,$order,$cash){

        $order = $this->getUserCash(0,$order,$cash);

        $this->where(['order_id'=>$order['id']])->where('type','in',[1,14])->delete();

        return $order;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-08-28 14:48
     * @功能说明:退款的时候要减去分销
     */
    public function getUserCashRefund($balance,$order,$cash){
        //一级分销
        $order = $this->getUserCashRefundData($order,$cash,1);
        //二级分销
        $order = $this->getUserCashRefundData($order,$order['surplus_cash'],14);

        if(!empty($order['user_top_id'])){
            //处理分摊金额
            $order = $this->getShareData($order,4);

            if(!empty($order['level_top_id'])){
                //处理分摊金额
                $order = $this->getShareData($order,5);
            }
        }

        return $order;
    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-08-28 14:48
     * @功能说明:退款的时候要减去分销
     */
    public function getUserCashRefundData($order,$cash,$type=1){

        $refund_model    = new RefundOrder();

        $com_goods_mdoel = new CommissionGoods();

        $refund_order = $refund_model->dataInfo(['id'=>$order['refund_id']]);

        $total_cash   = 0;

        if(!empty($refund_order)){
            //查询这笔等待有无佣金
            $comm = $this->dataInfo(['order_id'=>$refund_order['order_id'],'status'=>1,'type'=>$type]);

            if(!empty($comm)){

                foreach ($refund_order['order_goods'] as $v){

                    $comm_goods = $com_goods_mdoel->dataInfo(['commission_id'=>$comm['id'],'order_goods_id'=>$v['order_goods_id']]);

                    if(!empty($comm_goods)&&$comm_goods['num']>0){

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
            }
        }

        if($type==1){

            $order['user_c_cash'] = $total_cash>$cash?$cash:$total_cash;

        }else{

            $order['level_reseller_cash'] = $total_cash>$cash?$cash:$total_cash;
        }

        return $order;
    }





    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-08-26 23:39
     * @功能说明:添加佣金
     */
    public function commissionAddDataV2($order){

        if(!empty($order['user_top_id'])){

            $find = $this->where(['order_id'=>$order['id']])->where('type','in',[1])->find();

            if(!empty($find)){

                return false;
            }

            $com_mdoel = new Commission();

            $com_goods_mdoel = new CommissionGoods();

            $insert = [

                'uniacid' => $order['uniacid'],

                'user_id' => $order['user_id'],

                'top_id'  => $order['user_top_id'],

                'reseller_id'=> $order['user_reseller_id'],

                'order_id'=> $order['id'],

                'order_code' => $order['order_code'],

                'balance'    => $order['user_reseller_balance'],

                'cash' => $order['user_c_cash'],

                'status' => -1,

                'admin_id' => !empty($order['admin_id'])?$order['admin_id']:0
            ];

            $insert['create_time'] = !empty($order['create_time'])?$order['create_time']:time();

            $com_mdoel->dataAdd($insert);

            $id = $com_mdoel->getLastInsID();

            foreach ($order['order_goods'] as $v){

                $insert = [

                    'uniacid' => $order['uniacid'],

                    'order_goods_id' => $v['id'],

                    'commission_id'  => $id,

                    'cash'           => $v['user_reseller_cash'],

                    'num'            => $v['num'],

                    'balance'        => $v['user_reseller_balance']
                ];
                //添加到自订单记录表
                $res = $com_goods_mdoel->dataAdd($insert);
            }

            $share_model = new CommShare();
            //添加技师分摊比例
            if(!empty($order['reseller_coach_balance'])){

                $share_model->addData($order['uniacid'],$order['reseller_coach_balance'],$order['reseller_coach_cash'],$id,1,$order['id'],$order['coach_id']);
            }
            //添加代理商分摊比例
            if(!empty($order['reseller_admin_balance'])){

                $share_model->addData($order['uniacid'],$order['reseller_admin_balance'],$order['reseller_admin_cash'],$id,2,$order['id'],$order['reseller_admin_id']);
            }
            //添加平台分摊比例
            if(!empty($order['reseller_company_balance'])){

                $share_model->addData($order['uniacid'],$order['reseller_company_balance'],$order['reseller_company_cash'],$id,3,$order['id']);
            }
            //添加手续费
            $share_model->addPointData($id,$order,1,$order['user_top_id']);
        }
        return true;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-08-26 23:39
     * @功能说明:二级分销添加佣金
     */
    public function commissionLevelAddData($order){

        if(!empty($order['level_top_id'])){

            $find = $this->where(['order_id'=>$order['id']])->where('type','in',[14])->find();

            if(!empty($find)){

                return false;
            }

            $com_mdoel = new Commission();

            $com_goods_mdoel = new CommissionGoods();

            $insert = [

                'uniacid' => $order['uniacid'],

                'user_id' => $order['user_top_id'],

                'top_id'  => $order['level_top_id'],

                'reseller_id'=> $order['level_reseller_id'],

                'sub_reseller_id'=> $order['user_reseller_id'],

                'order_id'=> $order['id'],

                'order_code' => $order['order_code'],

                'balance'    => $order['level_reseller_balance'],

                'cash' => $order['level_reseller_cash'],

                'status' => -1,

                'admin_id' => !empty($order['admin_id'])?$order['admin_id']:0,

                'type' => 14
            ];

            $insert['create_time'] = !empty($order['create_time'])?$order['create_time']:time();

            $com_mdoel->dataAdd($insert);

            $id = $com_mdoel->getLastInsID();

            foreach ($order['order_goods'] as $v){

                $insert = [

                    'uniacid' => $order['uniacid'],

                    'order_goods_id' => $v['id'],

                    'commission_id'  => $id,

                    'cash'           => $v['level_reseller_cash'],

                    'num'            => $v['num'],

                    'balance'        => $v['level_reseller_balance']
                ];
                //添加到自订单记录表
                $res = $com_goods_mdoel->dataAdd($insert);
            }

            $share_model = new CommShare();
            //添加技师分摊比例
            if(!empty($order['level_reseller_coach_balance'])){

                $share_model->addData($order['uniacid'],$order['level_reseller_coach_balance'],$order['level_reseller_coach_cash'],$id,1,$order['id'],$order['coach_id']);
            }
            //添加代理商分摊比例
            if(!empty($order['level_reseller_admin_balance'])){

                $share_model->addData($order['uniacid'],$order['level_reseller_admin_balance'],$order['level_reseller_admin_cash'],$id,2,$order['id'],$order['level_reseller_admin_id']);
            }
            //添加平台分摊比例
            if(!empty($order['level_reseller_company_balance'])){

                $share_model->addData($order['uniacid'],$order['level_reseller_company_balance'],$order['level_reseller_company_cash'],$id,3,$order['id']);
            }
            //添加手续费
            $share_model->addPointData($id,$order,14,$order['level_top_id']);
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

        if(!empty($order['partner_id'])&&isset($order['coach_agent_balance'])&&!empty($order['broker_id'])){

            $find = $this->where(['order_id'=>$order['id']])->where('type','in',[9])->find();

            if(!empty($find)){

                return false;
            }

            $insert = [

                'uniacid' => $order['uniacid'],

                'user_id' => $order['user_id'],

                'top_id'  => $order['partner_id'],

                'broker_id'=> $order['broker_id'],

                'order_id'=> $order['id'],

                'order_code' => $order['order_code'],

                'type'    => 9,

                'cash'    => $order['partner_cash'],

                'admin_id'=> !empty($order['admin_id'])?$order['admin_id']:0,

                'balance' => $order['coach_agent_balance'],

                'status'  => -1,
            ];
            $insert['create_time'] = !empty($order['create_time'])?$order['create_time']:time();

            $res = $this->dataAdd($insert);

            $id  = $this->getLastInsID();

            $share_model = new CommShare();
            //添加技师分摊比例
            if(!empty($order['partner_coach_balance'])){

                $share_model->addData($order['uniacid'],$order['partner_coach_balance'],$order['partner_coach_cash'],$id,1,$order['id'],$order['coach_id']);
            }
            //添加代理商分摊比例
            if(!empty($order['partner_admin_balance'])){

                $share_model->addData($order['uniacid'],$order['partner_admin_balance'],$order['partner_admin_cash'],$id,2,$order['id'],$order['partner_admin_id']);
            }
            //添加平台分摊比例
            if(!empty($order['partner_company_balance'])){

                $share_model->addData($order['uniacid'],$order['partner_company_balance'],$order['partner_company_cash'],$id,3,$order['id']);
            }
            $share_model = new CommShare();
            //添加手续费
            $share_model->addPointData($id,$order,$insert['type'],$insert['top_id']);
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

        if(!empty($order['channel_id'])&&isset($order['channel_balance'])){

            $find = $this->where(['order_id'=>$order['id']])->where('type','in',[10])->find();

            if(!empty($find)){

                return false;
            }
            $insert = [

                'uniacid' => $order['uniacid'],

                'user_id' => $order['user_id'],

                'top_id'  => $order['channel_id'],

                'order_id'=> $order['id'],

                'order_code' => $order['order_code'],

                'type'    => 10,

                'cash'    => $order['channel_cash'],

                'balance' => $order['channel_balance'],

                'admin_id'=> !empty($order['admin_id'])?$order['admin_id']:0,

                'channel_qr_id'=> !empty($order['channel_qr_id'])?$order['channel_qr_id']:0,

                'status'  => -1,

            ];
            $insert['create_time'] = !empty($order['create_time'])?$order['create_time']:time();

            $res = $this->dataAdd($insert);

            $id  = $this->getLastInsID();

            $share_model = new CommShare();
            //添加技师分摊比例
            if(!empty($order['channel_coach_balance'])&&$order['channel_coach_balance']>0){

                $share_model->addData($order['uniacid'],$order['channel_coach_balance'],$order['channel_coach_cash'],$id,1,$order['id'],$order['coach_id']);
            }
            //添加代理商分摊比例
            if(!empty($order['channel_admin_balance'])&&$order['channel_admin_balance']>0){

                $share_model->addData($order['uniacid'],$order['channel_admin_balance'],$order['channel_admin_cash'],$id,2,$order['id'],$order['channel_admin_id']);
            }
            //添加平台分摊比例
            if(!empty($order['channel_company_balance'])&&$order['channel_company_balance']>0){

                $share_model->addData($order['uniacid'],$order['channel_company_balance'],$order['channel_company_cash'],$id,3,$order['id']);
            }

            $share_model = new CommShare();
            //添加手续费
            $share_model->addPointData($id,$order,$insert['type'],$insert['top_id']);
        }

        return true;
    }


    /**
     * @param $order
     * @功能说明:增加渠道商佣金
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-22 15:55
     */
    public function salesmanCommission($order){

        if(!empty($order['salesman_id'])&&isset($order['salesman_balance'])){

            $find = $this->where(['order_id'=>$order['id']])->where('type','in',[12])->find();

            if(!empty($find)){

                return false;
            }

            $insert = [

                'uniacid' => $order['uniacid'],

                'user_id' => $order['user_id'],

                'top_id'  => $order['salesman_id'],

                'order_id'=> $order['id'],

                'order_code' => $order['order_code'],

                'type'    => 12,

                'cash'    => $order['salesman_cash'],

                'balance' => $order['salesman_balance'],

                'admin_id'=> !empty($order['admin_id'])?$order['admin_id']:0,

                'status'  => -1,

            ];
            $insert['create_time'] = !empty($order['create_time'])?$order['create_time']:time();

            $res = $this->dataAdd($insert);

            $id  = $this->getLastInsID();

            $share_model = new CommShare();
            //添加技师分摊比例
            if(!empty($order['salesman_coach_balance'])&&$order['salesman_coach_balance']>0){

                $share_model->addData($order['uniacid'],$order['salesman_coach_balance'],$order['salesman_coach_cash'],$id,1,$order['id'],$order['coach_id']);
            }
            //添加代理商分摊比例
            if(!empty($order['salesman_admin_balance'])&&$order['salesman_admin_balance']>0){

                $share_model->addData($order['uniacid'],$order['salesman_admin_balance'],$order['salesman_admin_cash'],$id,2,$order['id'],$order['salesman_admin_id']);
            }
            //添加平台分摊比例
            if(!empty($order['salesman_company_balance'])&&$order['salesman_company_balance']>0){

                $share_model->addData($order['uniacid'],$order['salesman_company_balance'],$order['salesman_company_cash'],$id,3,$order['id']);
            }
            $share_model = new CommShare();
            //添加手续费
            $share_model->addPointData($id,$order,$insert['type'],$insert['top_id']);

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

        if(isset($order['company_cash'])){

            $find = $this->where(['order_id'=>$order['id']])->where('type','in',[16])->find();

            if(!empty($find)){

                return false;
            }

            $insert = [

                'uniacid' => $order['uniacid'],

                'user_id' => $order['user_id'],

                'top_id'  => 0,

                'order_id'=> $order['id'],

                'order_code' => $order['order_code'],

                'type'    => 16,

                'cash'    => $order['company_cash'],

                'company_cash' => $order['company_cash'],

                'balance' => 0,

                'status'  => -1,
            ];
            $insert['create_time'] = !empty($order['create_time'])?$order['create_time']:time();

            $res = $this->dataAdd($insert);

            $id  = $this->getLastInsID();

            $share_model = new CommShare();
            //添加手续费
          //  $share_model->addPointData($id,$order);

            if(isset($order['share_poster_company'])){
                //添加广告费
                $share_model->addPosterData($id,$order,3,$insert['type'],$insert['top_id']);
            }
            //优惠券分摊
            $share_model->addCouponBearData($id,$order,3,16,$insert['top_id']);

            if($order['free_fare']==1){

                $share_model->addCarData($id,$order,3);
            }
            //储值折扣卡分摊
            $share_model->addBalanceDiscountShare($id,$order,0,16,3);
        }
        return true;
    }



    /**
     * @param $order
     * @功能说明:增加平台佣金
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-22 15:55
     */
    public function adminCarCommission($order){

        if(isset($order['free_fare'])&&$order['free_fare']==3&&$order['car_price']>0){

            $find = $this->where(['order_id'=>$order['id'],'type'=>23])->find();

            if(!empty($find)){

                return false;
            }

            $admin_model = new Admin();

            $city_type = $this->where(['order_id'=>$order['id'],'top_id'=>$order['admin_id']])->where('type','in',[2,3,6])->value('city_type');

            if(empty($city_type)){

                $city_type = $admin_model->where(['id'=>$order['admin_id']])->value('city_type');
            }

            $insert = [

                'uniacid' => $order['uniacid'],

                'user_id' => $order['user_id'],

                'top_id'  => $order['admin_id'],

                'order_id'=> $order['id'],

                'order_code'=> $order['order_code'],

                'type'     => 23,

                'cash'     => $order['car_price'],

                'balance'  => 0,

                'status'   => -1,

                'city_type'=> $city_type
            ];

            $insert['create_time'] = !empty($order['create_time'])?$order['create_time']:time();

            $res = $this->dataAdd($insert);
        }

        return true;
    }




    /**
     * @param $order_id
     * @param $order
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-31 13:58
     */
    public function getFinanceText($order){

        $dis[] = ['status','=',2];

        $dataPath = APP_PATH.'massage/info/FinanceText.php' ;

        $text =  include $dataPath ;

        $change_model = new CoachChangeLog();

        $share_model  = new CommShare();

        if(!empty($order['data'])){

            foreach ($order['data'] as &$values){
                //总的真实的支付金额包含车费
                $values['remain_cash'] = $values['pay_price'];

                foreach ($text as $vv){

                    $values[$vv['cash']] = 0;

                    $values[$vv['name']] = '';
                }

                $list = $this->where($dis)->where(['order_id'=>$values['order_id']])->withoutField('id')->select()->toArray();

                if(!empty($list)){

                    foreach ($list as $value){

                        $is_car = 0;

                        if($values['order_id'] == $value['order_id']){

                            foreach ($text as $vs){

                                if(in_array($value['type'],$vs['type'])){

                                    if(empty($vs['city_type'])||(!empty($vs['city_type'])&&$value['city_type']==$vs['city_type'])){
                                        //代理商的佣金可能会包含 线下技师的佣金和车费
                                        if($value['type']==2){

                                            $value['cash'] = $value['cash']-$value['coach_cash'] - $value['car_cash'];
                                        }

                                        $values[$vs['cash']] = !empty($value)?round($value['cash'],2):0;

                                        $obj_top_id = isset($vs['top_id'])?$vs['top_id']:'top_id';

                                        $values[$vs['name']] = Db::name($vs['table'])->where(['id'=>$value[$obj_top_id]])->value($vs['title']);
                                        //佣金对应者名称
                                        $values[$vs['name']] = !empty($values[$vs['name']])?$values[$vs['name']]:'';

                                        $values['remain_cash'] -= $values[$vs['cash']];
                                        //车费不减
                                        if(in_array($value['type'],[8,13])){

                                            $is_car = 1;
                                        }

                                        if(in_array($value['type'],[13])){

                                            $values['car_admin'] = 1;
                                        }else{

                                            $values['car_admin'] = 0;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

//                $values['coach_cash']    += $values['coach_refund_comm_cash']+$values['coach_refund_empty_cash'];
//
//                $values['district_cash'] += $values['district_refund_empty_cash']+$values['district_refund_comm_cash'];
//
//                $values['province_cash'] += $values['province_refund_empty_cash']+$values['province_refund_comm_cash'];
//
//                $values['city_cash']     += $values['city_refund_empty_cash']+$values['city_refund_comm_cash'];

                $values['coach_cash']  = round($values['coach_cash'] ,2);
                $values['district_cash']  = round($values['district_cash'] ,2);
                $values['province_cash']  = round($values['province_cash'] ,2);
                $values['city_cash']  = round($values['city_cash'] ,2);
                //代理商承担的车费
                $admin_share_cash = $this->where(['order_id'=>$values['order_id'],'type'=>23,'status'=>2])->sum('cash');

                $values['remain_cash']+=$admin_share_cash;

                $car_share = $share_model->where(['order_id'=>$values['order_id']])->where('comm_type','in',[8,13])->sum('share_cash');

                if(!empty($car_share)&&!empty($is_car)){

                    $values['car_cash'] .= '(扣除手续费'.$car_share.')';
                }

                $values['remain_cash'] = round($values['remain_cash'],2);
                //线下技师
                if(empty($values['coach_id'])){

                    $values['coach_name'] = $change_model->getCoachByOrder($values['order_id']);
                }

                if(empty($values['car_name'])&&!empty($is_car)){

                    $values['car_name'] = $values['coach_name'];
                }
            }
        }

        return $order;
    }


    /**
     * @param $order
     * @功能说明:渠道商佣金分销商佣金是否叠加
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-11-14 15:54
     */
    public function channelFxSuperpose($order){

        $channel_fx_superpose = getConfigSetting($order['uniacid'],'channel_fx_superpose');

        if($channel_fx_superpose==1||empty($order['user_top_id'])||empty($order['channel_id'])){

            return $order;
        }

       // $first = getConfigSetting($order['uniacid'],'channel_distribution_order');

        if($channel_fx_superpose==2){

            $order['user_top_id']  = 0;

            $order['level_top_id'] = 0;

        }else{

            $order['channel_id'] = 0;

            $order['salesman_id'] = 0;

            $order_model = new Order();

            $order_model->dataUpdate(['id'=>$order['id']],['channel_id'=>0,'salesman_id'=>0]);
        }

        return $order;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-02-05 11:20
     * @功能说明:初始化车费 最开始的车费没有记录到佣金里面
     */
    public function initCarpriceAll($uniacid){

        $dis = [

            'a.pay_type' => 7,

            'a.free_fare'=> 0,

            'a.version'  => 0,

            'a.uniacid'  => $uniacid
        ];

        $data = Db::name('massage_service_order_list')->alias('a')
                ->join('massage_service_order_commission b','a.id = b.order_id','left')
                ->where($dis)
                ->where('true_car_price','>',0)
                ->where('a.create_time','<',1679637056)
                ->whereNull('b.id')
                ->field('a.id as order_id,a.true_car_price,a.user_id,a.coach_id,a.order_code,a.admin_id,a.create_time,a.order_end_time')
                ->group('a.id')
                ->select()
                ->toArray();

        $key   = 'initCarprice_key.initCarprice_key';

        incCache($key,1,$uniacid);

        $value = getCache($key,$uniacid);

        if($value==1) {

            if (!empty($data)) {

                Db::startTrans();

                foreach ($data as $k => $v) {

                    $insert[$k] = [

                        'uniacid' => $uniacid,

                        'user_id' => $v['user_id'],

                        'top_id' => $v['coach_id'],

                        'order_id' => $v['order_id'],

                        'order_code' => $v['order_code'],

                        'type' => 8,

                        'cash' => $v['true_car_price'],

                        'admin_id' => $v['admin_id'],

                        'balance' => 0,

                        'status'  => 2,

                        'is_init' => 1,

                        'cash_time' => $v['order_end_time'],

                        'update_time' => $v['order_end_time'],

                        'create_time' => $v['create_time']
                    ];
                }

                $this->saveAll($insert);

                $id = array_column($data,'order_id');

                $order_model = new Order();

                $order_model->where('id','in',$id)->update(['version'=>1]);

                Db::commit();
            }
        }

        decCache($key,1,$uniacid);

        $dis = [

            'b.cash_type' => 2,

            'b.comm_type' => 0
        ];

        $list = $this->alias('a')
                ->join('massage_service_order_commission_share b','a.id = b.comm_id')
                ->where($dis)
                ->field('b.id,a.type')
                ->group('b.id')
                ->select()
                ->toArray();

        if(!empty($list)){

            foreach ($list as $key => $value){

                $share[$key] = [

                    'id' => $value['id'],

                    'comm_type' => $value['type']
                ];
            }

            $share_model = new CommShare();

            $share_model->saveAll($share);
        }

        return true;
    }




    public function share()
    {
        return $this->hasMany(CommShare::class,'comm_id');
    }


    /**
     * @param $refund_order
     * @param $pay_order
     * @功能说明:退款手续费|空单费
     * @author chenniang
     * @DataTime: 2024-07-24 18:31
     *///17技师空单费 18技师退款手续费 19代理商空单费 20代理商退款手续费 21平台空单费 22平台退款手续费
    public function refundEmptyComm($refund_order){

        $company_refund_comm = 100;

        $commany_refund_cempty = 100;

        $coach_refund_comm = getConfigSetting($refund_order['uniacid'],'coach_refund_comm');

        $admin_refund_comm = getConfigSetting($refund_order['uniacid'],'admin_refund_comm');

        $coach_empty_cash  = getConfigSetting($refund_order['uniacid'],'coach_empty_cash');

        $admin_empty_cash  = getConfigSetting($refund_order['uniacid'],'admin_empty_cash');

        $arr = [];

        if($refund_order['refund_empty_cash']>0){

            if(!empty($refund_order['coach_id'])){

                $arr[] = [

                    'type' => 17,

                    'model' => new CoachWater(),

                    'top_id'=> 'coach_id',

                    'balance' => $coach_empty_cash,

                    'comm_cash' => round($refund_order['refund_empty_cash']*$coach_empty_cash/100,2)
                ];

                $commany_refund_cempty -= $coach_empty_cash;
            }

            if(!empty($refund_order['admin_id'])){

                if(empty($refund_order['coach_id'])){

                    $admin_empty_cash +=$coach_empty_cash;
                }

                $arr[] = [

                    'type'  => 19,

                    'model' => new AdminWater(),

                    'top_id'=> 'admin_id',

                    'balance'=> $admin_empty_cash,

                    'comm_cash' => round($refund_order['refund_empty_cash']*$admin_empty_cash/100,2)
                ];

                $commany_refund_cempty -= $admin_empty_cash;
            }

            $arr[] = [

                'type'  => 21,

                'model' => new CompanyWater(),

                'top_id'=> 0,

                'balance'=> $commany_refund_cempty,

                'comm_cash' => round($refund_order['refund_empty_cash']*$commany_refund_cempty/100,2)
            ];
        }

        if($refund_order['refund_comm_cash']>0){

            if(!empty($refund_order['coach_id'])){

                $arr[] = [

                    'type' => 18,

                    'model' => new CoachWater(),

                    'top_id'=> 'coach_id',

                    'balance' => $coach_refund_comm,

                    'comm_cash' => round($refund_order['refund_comm_cash']*$coach_refund_comm/100,2)
                ];

                $company_refund_comm -= $coach_refund_comm;
            }

            if(!empty($refund_order['admin_id'])){

                if(empty($refund_order['coach_id'])){

                    $admin_refund_comm +=$coach_refund_comm;
                }

                $arr[] = [

                    'type'  => 20,

                    'model' => new AdminWater(),

                    'top_id'=> 'admin_id',

                    'balance'=> $admin_refund_comm,

                    'comm_cash' => round($refund_order['refund_comm_cash']*$admin_refund_comm/100,2)
                ];

                $company_refund_comm -= $admin_refund_comm;
            }

            $arr[] = [

                'type'  => 22,

                'model' => new CompanyWater(),

                'top_id'=> 0,

                'balance'=> $company_refund_comm,

                'comm_cash' => round($refund_order['refund_comm_cash']*$company_refund_comm/100,2)
            ];
        }

        if(!empty($arr)){

            foreach ($arr as $k=>$value){

                $find = $this->where(['order_id'=>$refund_order['id'],'type'=>$value['type']])->count();

                if($find==0){

                    $insert = [

                        'uniacid' => $refund_order['uniacid'],

                        'user_id' => $refund_order['user_id'],

                        'top_id'  => key_exists($value['top_id'],$refund_order)?$refund_order[$value['top_id']]:0,

                        'refund_id'=> $refund_order['id'],

                        'order_id' => $refund_order['order_id'],

                        'order_code' => $refund_order['order_code'],

                        'type'    => $value['type'],

                        'cash'    => $value['comm_cash'],

                        'balance' => $value['balance'],

                        'admin_id'=> $refund_order['admin_id'],

                        'status'  => 1,

                        'order_cash'=> in_array($value['type'],[17,19,21])?$refund_order['refund_empty_cash']:$refund_order['refund_comm_cash'],
                    ];

                    if(in_array($value['type'],[19,20])){

                        $admin_model = new Admin();

                        $city_type = $this->where(['order_id'=>$refund_order['order_id'],'top_id'=>$refund_order['admin_id']])->where('type','in',[2,3,6])->value('city_type');

                        if(empty($city_type)){

                            $city_type = $admin_model->where(['id'=>$refund_order['admin_id']])->value('city_type');
                        }

                        $insert['city_type'] = $city_type;
                    }

                    $res = $this->dataAdd($insert);
                }
            }
        }

        return true;
    }


    /**
     * @author chenniang
     * @DataTime: 2024-07-25 17:46
     * @功能说明:空单费到账
     */
    public function emptyCommSuccess($refund_order_id,$type = 1){

        $Queue = new Queue();

        Db::startTrans();

        $data = $this->where(['refund_id'=>$refund_order_id,'status'=>1])->where('type','in',[17,18,19,20,21,22])->select()->toArray();

        if(!empty($data)){

            foreach ($data as $v){

                if(in_array($v['type'],[17,18])){

                    $water_model = new CoachWater();

                }elseif (in_array($v['type'],[19,20])){

                    $water_model = new AdminWater();
                }else{

                    $water_model = new CompanyWater();
                }

                $res = $this->dataUpdate(['id'=>$v['id'],'status'=>1],['status'=>2,'cash_time'=>time()]);

                if($res==0){

                    Db::rollback();

                    if($type==2){

                        $Queue->addQueue($refund_order_id,1,$v['uniacid']);
                    }
                    return $res;
                }

                $res = $water_model->updateCash($v['uniacid'],$v['top_id'],$v['cash'],1);

                if($res==0){

                    Db::rollback();

                    if($type==2){

                        $Queue->addQueue($refund_order_id,1,$v['uniacid']);
                    }
                    return $res;
                }
            }
        }

        Db::commit();

        return true;
    }


    /**
     * @author chenniang
     * @DataTime: 2024-07-30 18:11
     * @功能说明:退款订单平台佣金到账
     */
    public function refundCompanySuccess($order_id){

        $data = $this->where(['order_id'=>$order_id,'status'=>1,'type'=>16])->find();

        if(!empty($data)){

            $res = $this->dataUpdate(['id'=>$data->id,'status'=>1],['status'=>2,'cash_time'=>time()]);

            if($res==0){

                return $res;
            }

            $water_model = new CompanyWater();

            $res = $water_model->updateCash($data->uniacid,0,$data->company_cash,1);

            if($res==0){

                return $res;
            }
        }

        return true;
    }




    /**
     * @param $order
     * @功能说明:技师佣金
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-06-12 13:14
     */
    public function memberDiscountCoachCommission($order,$pay_model,$type=24){

        if(isset($order['pay_price'])){

            $find = $this->where(['order_id'=>$order['id'],'type'=>$type])->find();

            if(!empty($find)){

                return false;
            }

            $config_setting = getConfigSettingArr($order['uniacid'],['wx_point','ali_point','balance_point',]);

            if($pay_model==2){

                $point = $config_setting['balance_point'];

            }elseif ($pay_model==3){

                $point = $config_setting['ali_point'];

            }elseif($pay_model==4){

                $point = $config_setting['balance_point'];

            }else{

                $point = $config_setting['wx_point'];
            }

            $insert = [

                'uniacid' => $order['uniacid'],

                'user_id' => $order['user_id'],

                'top_id'  => $order['coach_id'],

                'order_id'=> $order['id'],

                'order_code' => $order['order_code'],

                'type'    => $type,

                'cash'    => round($order['balance']*$order['pay_price']/100,2),

                'balance' => $order['balance'],

                'status'  => -1,

                'order_cash'  => $order['pay_price'],

                'create_time' => $order['create_time']
            ];

            $point_cash = $insert['cash']*$point/100;

            $insert['cash'] -= $point_cash;

            $res = $this->dataAdd($insert);

            $id  = $this->getLastInsID();

            $inserts = [

                'uniacid' => $this->_uniacid,

                'comm_id' => $id,

                'share_balance'=> $point,

                'share_cash'   => $point_cash,

                'order_id'     => $order['id'],

                'cash_type'    => 1,

                'type'         => 1,

                'comm_type'    => $type
            ];

            $share_model = new CommShare();

            $share_model->dataAdd($inserts);

            if($type!=24){

                $integral_insert = [

                    'uniacid' => $order['uniacid'],

                    'coach_id'=> $order['coach_id'],

                    'order_id'=> $order['id'],

                    'integral'=> round($order['balance']*$order['pay_price']/100,2),

                    'balance' => $order['balance'],

                    'status'  => -1,

                    'type'    => 3,

                    'user_id' => $order['user_id'],

                    'user_cash'=> $order['pay_price']
                ];

                $integral_insert['integral'] = round($integral_insert['integral']-$point_cash,2);

                $integral_model = new Integral();

                $integral_model->dataAdd($integral_insert);
            }

            return $res;
        }

        return 0;
    }



//a 卡 抵扣1.24 8.9折 抵扣11.24

//b 卡 抵扣0.74 9.2折 抵扣9.26

// a 服务费：7.00  物料费：1.50

// b 服务费：10    物料费：2.00


// a服务用的a卡：7.00X0.89=6.23 折扣0.77  物料费：1.50x0.89=1.33 折扣 0.17 共计 0.94 还剩 0.3











}