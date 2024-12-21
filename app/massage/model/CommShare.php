<?php
namespace app\massage\model;

use app\balancediscount\model\OrderShare;
use app\BaseModel;
use think\facade\Db;

class CommShare extends BaseModel
{
    //定义表名
    protected $name = 'massage_service_order_commission_share';




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
     * @param $uniacid
     * @param $balance
     * @param $cash
     * @param $id
     * @param $type
     * @param int $share_id
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-23 17:41
     */
    public function addData($uniacid,$balance,$cash,$id,$type,$order_id,$share_id=0,$cash_type=0){

        $insert = [

            'uniacid' => $uniacid,

            'comm_id' => $id,

            'share_balance' => $balance,

            'share_cash' => $cash,

            'type' => $type,

            'share_id' => $share_id,

            'order_id' => $order_id,

            'cash_type' => $cash_type,
        ];

        $res = $this->insert($insert);

        return $res;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-23 18:03
     * @功能说明:获取分摊比例
     */
    public function getShareBalance($comm_id,$type){

        $dis = [

            'comm_id' => $comm_id,

            'type'    => $type
        ];

        $balance = $this->where($dis)->value('share_balance');

        return !empty($balance)?$balance:0;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-09-12 18:51
     * @功能说明:添加手续费分摊记录
     */
    public function addPointData($id,$order,$type,$top_id){

        $comm_model = new Commission();

        $cash   = $comm_model->getTypeText($type);

        if(!empty($cash['point'])&&!empty($order['pay_point'])&&$order['pay_point']>0){

            $insert = [

                'uniacid'      => $order['uniacid'],

                'comm_id'      => $id,

                'share_balance'=> $order['pay_point'],

                'share_cash'   => $order[$cash['point']],

                'order_id'     => $order['id'],

                'cash_type'    => 1,

                'share_id'     => $top_id,

                'comm_type'    => $type
            ];

            $this->dataAdd($insert);
        }

        return true;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-09-13 10:23
     * @功能说明:添加广告费记录
     */
    public function addPosterData($id,$order,$type,$comm_type,$top_id){

        if(!empty($order['poster_cash'])){

            if($type==1){

                $balance = $order['poster_coach_share'];

                $cash    = $order['share_poster_coach'];

            }elseif ($type==2){

                $balance = $order['poster_admin_share'];

                $cash    = $order['share_poster_admin'];

            }else{

                $balance = $order['poster_company_share'];

                $cash    = $order['share_poster_company'];
            }

            $insert = [

                'uniacid' => $order['uniacid'],

                'comm_id' => $id,

                'share_balance'=> $balance,

                'share_cash'   => $cash,

                'order_id'     => $order['id'],

                'cash_type'    => 2,

                'share_id'     => $top_id,

                'type'   => $type,

                'comm_type'   => $comm_type,
            ];

            $res = $this->dataAdd($insert);

            return $res;
        }

        return true;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-10-12 16:56
     * @功能说明:技师储值
     */
    public function coachBalanceCash($id,$order,$top_id){

        if(isset($order['comm_coach_balance'])&&$order['comm_coach_balance']>0){

            $insert = [

                'uniacid' => $order['uniacid'],

                'comm_id' => $id,

                'share_balance'=> $order['comm_coach_balance'],

                'share_cash'   => $order['comm_coach_cash'],

                'order_id'     => $order['id'],

                'cash_type'    => 3,

                'share_id'     => $top_id,

                'type'         => 1,

                'comm_type'    => 3,
            ];

            $res = $this->dataAdd($insert);

            return $res;
        }

        return true;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-10-12 16:56
     * @功能说明:技师储值
     */
    public function coachSkillCash($id,$order,$top_id){

        if(isset($order['skill_balance'])&&$order['skill_balance']>0&&!empty($order['coach_id'])){

            $insert = [

                'uniacid' => $order['uniacid'],

                'comm_id' => $id,

                'share_balance'=> $order['skill_balance'],

                'share_cash'   => $order['skill_cash'],

                'order_id'     => $order['id'],

                'cash_type'    => 4,

                'share_id'     => $top_id,

                'type'         => 1,

                'comm_type'    => 3,
            ];

            $res = $this->dataAdd($insert);

            return $res;
        }

        return true;

    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-09-14 19:23
     * @功能说明:获取手续费
     */
    public function getOrderPointCash($order_id,$type=1){

        if($type==1){

            $cash = $this->where('order_id','in',$order_id)->where(['cash_type'=>1])->sum('share_cash');
        }else{

            $cash = $this->where('order_id','=',$order_id)->where(['cash_type'=>1])->sum('share_cash');
        }

        return round($cash,2);
    }


    /**
     * @param $coach_id
     * @功能说明:技师佣金手续费
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-10-10 15:28
     */
    public function getCoachPointCash($coach_id){

        $dis[] = ['b.top_id','=',$coach_id];

        $dis[] = ['b.status','=',2];
        //车费和服务费
        $dis[] = ['b.type','in',[3,8]];

        $dis[] = ['a.cash_type','=',1];

        $cash = $this->alias('a')
                ->join('massage_service_order_commission b','a.comm_id = b.id')
                ->where($dis)
                ->group('a.id')
                ->sum('a.share_cash');

        return $cash;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-10-12 18:02
     * @功能说明:技师分摊了业务员|渠道上|合伙额多少钱
     */
    public function coachShareCash($coach_id,$type,$order_id=0){

        $dis[] = ['a.share_id','=',$coach_id];

        $dis[] = ['b.status','=',2];

        $dis[] = ['b.type','=',$type];

        $dis[] = ['a.cash_type','=',0];

        $dis[] = ['a.type','=',1];

        if(!empty($order_id)){

            $dis[] = ['b.order_id','=',$order_id];
        }

        $cash = $this->alias('a')
            ->join('massage_service_order_commission b','a.comm_id = b.id')
            ->where($dis)
            ->group('a.id')
            ->sum('a.share_cash');

        return round($cash,2);
    }


    /**
     * @param $coach_id
     * @param $type
     * @功能说明:技师广告费
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-10-12 18:55
     */
    public function coachPosterCash($coach_id,$type){

        $dis[] = ['a.share_id','=',$coach_id];

        $dis[] = ['b.status','=',2];

        $dis[] = ['a.type','=',1];

        if($type==3){

            $dis[] = ['a.cash_type','in',[3,7,24,25]];
        }else{

            $dis[] = ['a.cash_type','=',$type];
        }

        $cash = $this->alias('a')
            ->join('massage_service_order_commission b','a.comm_id = b.id')
            ->where($dis)
            ->group('a.id')
            ->sum('a.share_cash');

        return $cash;
    }


    /**
     * @param $coach_id
     * @param int $order_id
     * @功能说明:技师分摊手续费
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-10-13 11:54
     */
    public function coachShareCashData($coach_id,$order_id=0){
        //获取分摊金额
        $arr = [
            //合伙人分摊
            9 => 'partner_share_cash',
            //渠道商
            10=> 'channel_share_cash',
            //业务员
            12=> 'salesman_share_cash',

        ];
        //总分摊
        $order['total_share_cash'] = 0;

        foreach ($arr as $k=>$v){

            $order[$v] = $this->coachShareCash($coach_id,$k,$order_id);

            $order['total_share_cash'] += $order[$v];
        }

        $where = [];

        if(!empty($order_id)){

            $where = [

                'order_id' => $order_id
            ];
        }
        //手续费(只查服务费)
        $order['point_cash']  = $this->where($where)->where(['cash_type'=>1,'comm_type'=>3])->sum('share_cash');
        //广告费
        $order['poster_cash'] = $this->where($where)->where(['cash_type'=>2,'type'=>1])->sum('share_cash');
        //储值扣款
        $order['coach_balance_cash'] = $this->where($where)->where(['cash_type'=>3,'type'=>1])->sum('share_cash');

        $order['total_share_cash'] = round($order['total_share_cash']+$order['point_cash']+ $order['poster_cash']+$order['coach_balance_cash'],2);

        return $order;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-02-26 14:23
     * @功能说明:代理商分摊车费
     */
    public function shareCarPrice($order){
        //代理商承担车费
//        if(!empty($order['free_fare'])&&$order['free_fare']==1&&!empty($order['true_car_price'])&&!empty($order['admin_id'])){
//
//
//
//            $order['admin_share_car_price'] = $order['car_price']<$order['admin_cash']?$order['car_price']:$order['admin_cash'];
//
//            $order['admin_cash'] -= $order['admin_share_car_price'];
//        }

        return $order;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-02-29 14:30
     * @功能说明:财务分摊
     */
    public function financeShareData($where,$arr,$type=1){

        $list = [
            //广告费
            2 => 'poster_cash',
            //储值扣款
            3 => 'coach_balance_cash',
            //技术服务费
            4 => 'skill_cash',
            //手续费
            1 => 'point_cash',

            //  6 => 'coupon_share_cash',
        ];

        $comm = new Commission();

        foreach ($list as $key=>$value){

            $dis = [

                ['e.type' ,'=', 16],

                ['b.pay_type','>', -1],
            ];

            $dis1 =[

                ['e.type' ,'<>', 16],
            ];

            $arr[$value] = $comm->alias('a')
                ->join('massage_service_order_list b','a.order_id = b.id')
                ->join('massage_service_order_commission e','b.id = e.order_id')
                ->join('massage_service_order_commission_share c','c.comm_id = e.id')
                ->where($where)
                ->where(function ($query) use ($dis,$dis1){
                    $query->whereOr([$dis,$dis1]);
                })
                ->where(['c.cash_type'=>$key,'e.status'=>2])
                ->group('c.id')
                ->column('c.share_cash');

            $arr[$value] = round(array_sum($arr[$value]),2);
        }

        return $arr;
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-02-29 14:30
     * @功能说明:财务分摊
     */
    public function orderShareData($id){

        $order_model = new Order();

        $comm = new Commission();

        $pay_type = $order_model->where(['id'=>$id])->value('pay_type');

        if($pay_type==-1){

            $cash = $comm->alias('a')
                ->join('massage_service_order_commission_share c','c.comm_id = a.id')
                ->where(['a.order_id'=>$id])
                ->where(['c.cash_type'=>1])
                ->where('a.status','=',2)
                ->where('a.type','in',[8,13])
                ->group('c.id')
                ->sum('c.share_cash');

            return $cash;
        }

        $list = [
            //广告费
            2 => 'poster_cash',
            //手续费
            1 => 'point_cash',
        ];

        $total_cash = 0;

        foreach ($list as $key=>$value){

            $cash = $comm->alias('a')
                ->join('massage_service_order_commission_share c','c.comm_id = a.id')
                ->where(['a.order_id'=>$id])
                ->where(['c.cash_type'=>$key])
                ->where('a.status','>',-1)
                ->group('c.id')
                ->sum('c.share_cash');

            $total_cash += $cash;
        }

        return $total_cash;
    }


    /**
     * @author chenniang
     * @DataTime: 2024-09-23 15:45
     * @功能说明:初始化分摊表的状态
     */
    public function initShareDataStatus($coach_id){

        $dis = [

            'a.status' => 2,

            'b.status' => -1
        ];

        if(!empty($coach_id)){

           // $dis['']
        }


        $data = $this->alias('a')
                ->join('massage_service_order_commission b','a.comm_id = b.id')
                ->where(['a.status'=>2,'b.status'=>-1])
                ->field('a.id')
                ->group('a.id')
                ->limit(20000)
                ->column('a.id');

        $this->where('id','in',$data)->update(['status'=>-1]);

        return true;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-02-29 14:30
     * @功能说明:财务分摊
     */
    public function financeShareDataV2($where,$arr){

        $list = [
            //广告费
            2 => 'poster_cash',
            //储值扣款
            3 => 'coach_balance_cash',
            //技术服务费
            4 => 'skill_cash',
            //手续费
            1 => 'point_cash',

          //  6 => 'coupon_share_cash',
        ];

        //$this->initShareDataStatus();

        $share = $this->alias('a')
            ->join('massage_service_order_list b','a.order_id = b.id')
            ->where($where)
            ->where(['a.status'=>2])
            ->where('a.cash_type','in',[1,2,3,4])
            ->where('a.comm_type','not in',[8,13])
            ->group('a.cash_type')
            ->field('round(sum(a.share_cash),2) as share_cash,a.cash_type')
            ->select()
            ->toArray();





        $sql = '';

        if(!empty($where)){

            foreach ($where as $k=>$v){

                if($k>0){

                    $and = ' AND ';
                }else{

                    $and = '';
                }

                if(is_array($v[2])){

                    $v[2] = implode(',',$v[2]);

                    if(!empty( $v[2])){

                        $v[2] = '('. $v[2].')';
                    }
                }

                $sql .= $and.$v['0'].' '.$v[1].' '.$v[2];
            }
        }




//        $sqldata = "SELECT c.id,c.cash_type
//             FROM `ims_massage_service_order_commission` `a`
//             right JOIN `ims_massage_service_order_list` `b` on `a`.`order_id`=`b`.`id`
//             right JOIN `ims_massage_service_order_commission` `e` on `e`.`order_id`=`b`.`id`
//             right JOIN (SELECT id,comm_id,cash_type  FROM `ims_massage_service_order_commission_share` GROUP BY cash_type ) AS c ON `e`.`id`=`c`.`comm_id`
//             WHERE $sql GROUP BY cash_type ";
//
//        $data = Db::query($sqldata);




        foreach ($list as $key=>$value ){

            $arr[$value] = 0;

            if(!empty($share)){

                foreach ($share as $v){

                    if($key==$v['cash_type']){

                        $arr[$value] = $v['share_cash'];
                    }
                }
            }
        }

        return $arr;
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-09-13 10:23
     * @功能说明:添加优惠券分摊记录
     */
    public function addCouponBearData($id,$order,$type,$comm_type,$top_id){

        if(!empty($order['coupon_bear_type'])&&$order['coupon_bear_type']==2&&$order['coupon_id']>0){

            if($type==1){

                $balance = $order['coupon_bear']['coupon_bear_coach'];

                $cash    = $order['coupon_bear']['coach_cash'];

            }elseif ($type==2){

                $balance = $order['coupon_bear']['coupon_bear_admin'];

                $cash    = $order['coupon_bear']['admin_cash'];

            }else{

                $balance = $order['coupon_bear']['coupon_bear_company'];

                $cash    = $order['coupon_bear']['company_cash'];
            }

            if($balance<=0){

                return true;
            }

            $insert = [

                'uniacid' => $order['uniacid'],

                'comm_id' => $id,

                'share_balance'=> $balance,

                'share_cash'   => $cash,

                'order_id'     => $order['id'],

                'cash_type'    => 6,

                'share_id'     => $top_id,

                'type'   => $type,

                'comm_type'   => $comm_type,
            ];

            $res = $this->dataAdd($insert);

            return $res;
        }

        return true;
    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-09-13 10:23
     * @功能说明:添加广告费记录
     */
    public function addCarData($id,$order,$type=1){

        if(in_array($order['free_fare'],[1,3])){

            $comm_model = new Commission();

            $top_id = $comm_model->where(['id'=>$id])->value('top_id');

            $comm_type = $comm_model->where(['id'=>$id])->value('type');

            $insert = [

                'uniacid' => $order['uniacid'],

                'comm_id' => $id,

                'share_balance'=> 0,

                'share_cash'   => $order['car_price'],

                'order_id'     => $order['id'],

                'cash_type'    => 5,

                'share_id'     => $top_id,

                'type'   => $type,

                'comm_type'   => $comm_type,
            ];

            $res = $this->dataAdd($insert);

            return $res;
        }

        return true;
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-09-13 10:23
     * @功能说明:添加储值折扣卡分摊记录
     *
     * type 1技师 2代理商 3平台
     */
    public function addBalanceDiscountShare($id,$order,$top_id,$comm_type,$type){

        if($order['pay_model']==4&&isset($order['coach_balancediscount_share_cash'])){

            $share_model = new OrderShare();

            if($type==1){

                $cash = isset($order['coach_balancediscount_share_cash'])?$order['coach_balancediscount_share_cash']:0;

                $balance = $share_model->where(['order_id'=>$order['id']])->value('coach_balance');

            }elseif ($type==2){

                $cash = isset($order['admin_balancediscount_share_cash'])?$order['admin_balancediscount_share_cash']:0;

                $balance = $share_model->where(['order_id'=>$order['id']])->value('admin_balance');

            }else{

                $cash = isset($order['company_balancediscount_share_cash'])?$order['company_balancediscount_share_cash']:0;

                $balance = $share_model->where(['order_id'=>$order['id']])->value('company_balance');
            }

            if($balance>0){

                $insert = [

                    'uniacid' => $order['uniacid'],

                    'comm_id' => $id,

                    'share_balance'=> $balance,

                    'share_cash'   => $cash,

                    'order_id'     => $order['id'],

                    'cash_type'    => 7,

                    'share_id'     => $top_id,

                    'type'        => $type,

                    'comm_type'   => $comm_type,
                ];

                $res = $this->dataAdd($insert);

                return $res;
            }
        }

        return true;
    }




}