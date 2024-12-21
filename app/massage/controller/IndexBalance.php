<?php
namespace app\massage\controller;
use app\ApiRest;

use app\balancediscount\model\CardWater;
use app\balancediscount\model\OrderList;
use app\balancediscount\model\UserCard;
use app\massage\model\BalanceCard;
use app\massage\model\BalanceOrder;
use app\massage\model\BalanceWater;
use app\massage\model\Coach;
use app\massage\model\Commission;
use app\massage\model\CommShare;
use app\massage\model\Integral;
use app\massage\model\MassageConfig;
use app\massage\model\Order;
use app\massage\model\RefundOrder;
use app\massage\model\UpOrderList;
use app\massage\model\User;
use app\partner\model\PartnerOrder;
use app\partner\model\PartnerOrderJoin;
use app\Rest;


use longbingcore\wxcore\PayModel;
use think\App;

use think\facade\Db;
use think\Request;



class IndexBalance extends ApiRest
{

    protected $model;

    protected $article_model;

    protected $coach_model;

    protected $water_model;


    public function __construct(App $app) {

        parent::__construct($app);

        $this->model = new BalanceCard();

        $this->balance_order = new BalanceOrder();

        $this->water_model    = new BalanceWater();
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-04 19:09
     * @功能说明:储值充值卡列表
     */
    public function cardList(){

        $input = $this->_param;

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['status','=',1];

        if(!empty($input['name'])){

            $dis[] = ['title','like','%'.$input['name'].'%'];

        }

        $data = $this->model->dataList($dis,$input['limit']);

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $v['create_time'] = date('Y-m-d H:i:s',$v['create_time']);
            }
        }

        if(!empty($input['coach_id'])){

            $coach_model = new Coach();

            $data['coach_name'] = $coach_model->where(['id'=>$input['coach_id']])->value('coach_name');
        }

        return $this->success($data);
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-07 17:00
     * @功能说明:充值余额
     */
    public function payBalanceOrder(){

        $input = $this->_input;

        if(!empty($input['card_id'])){

            $dis = [

                'id'     => $input['card_id'],

                'status' => 1
            ];


            $card = $this->model->dataInfo($dis);

            if(empty($card)){

                $this->errorMsg('充值卡已被下架');
            }

        }else{

            $card = [

                'price'      => $input['price'],

                'true_price' => $input['price'],

                'id'         => 0,

                'title'      => '自定义金额充值'
            ];
        }

        $pay_model = isset($input['pay_model'])?$input['pay_model']:1;

        $insert = [

            'uniacid'    => $this->_uniacid,

            'user_id'    => $this->getUserId(),

            'order_code' => orderCode(),

            'pay_price'  => $card['price'],

            'integral'   => $card['price'],

            'sale_price' => $card['price'],

            'true_price' => $card['true_price'],

            'card_id'    => $card['id'],

            'title'      => $card['title'],

            'coach_id'   => !empty($input['coach_id'])?$input['coach_id']:0,

            'app_pay'    => $this->is_app,

            'pay_model' => $pay_model
        ];

        $res = $this->balance_order->dataAdd($insert);

        if($res==0){

            $this->errorMsg('充值失败');
        }

        $order_id = $this->balance_order->getLastInsID();

        $config_model = new MassageConfig();

        $config = $config_model->dataInfo(['uniacid'=>$this->_uniacid]);
        //储值返回佣金
        if(!empty($input['coach_id'])&&$config['balance_cash']==1){

            $config_setting = getConfigSettingArr($this->_uniacid,['wx_point','ali_point','balance_point',]);

            if($pay_model==2){

                $point = $config_setting['balance_point'];

            }elseif ($pay_model==3){

                $point = $config_setting['ali_point'];

            }elseif($pay_model==4){

                $point = $config_setting['balance_point'];

            }else{

                $point = $config_setting['wx_point'];
            }

            $cash_insert = [

                'uniacid' => $this->_uniacid,

                'user_id' => $this->getUserId(),

                'top_id'  => $input['coach_id'],

                'order_id'=> $order_id,

                'order_code' => $insert['order_code'],

                'type'    => 7,

                'cash'    => round($config['balance_balance']*$card['price']/100,2),

                'balance' => $config['balance_balance'],

                'status'  => -1,

            ];

            $point_cash = $cash_insert['cash']*$point/100;

            $cash_insert['cash'] = $cash_insert['cash']-$point_cash;

            $comm_model = new Commission();

            $comm_model->dataAdd($cash_insert);

            $id = $comm_model->getLastInsID();

            $inserts = [

                'uniacid'      => $this->_uniacid,

                'comm_id'      => $id,

                'share_balance'=> $point,

                'share_cash'   => $point_cash,

                'order_id'     => $order_id,

                'cash_type'    => 1,

                'type'         => 1,

                'comm_type'    => 7,
            ];

            $share_model = new CommShare();

            $share_model->dataAdd($inserts);
            //
            $integral_insert = [

                'uniacid' => $this->_uniacid,

                'coach_id' => $input['coach_id'],

                'order_id'=> $order_id,

                'integral'   => round($config['balance_balance']*$card['price']/100,2),

                'balance' => $config['balance_balance'],

                'status'  => -1,

                'type'    => 1,

                'user_id' => $this->getUserId(),

                'user_cash'=>  $card['price']
            ];

            $integral_model = new Integral();

            $integral_insert['integral'] -= $point_cash;

            $integral_model->dataAdd($integral_insert);

        }
        //储值返回积分
        if(!empty($input['coach_id'])&&$config['balance_integral']==1){

            $integral_insert = [

                'uniacid' => $this->_uniacid,

                'coach_id' => $input['coach_id'],

                'order_id'=> $order_id,

                'integral'   => round($card['price'],2),

                'balance' => 100,

                'status'  => -1,

                'user_id' => $this->getUserId(),

                'user_cash'=> $card['price']

            ];

            $integral_model = new Integral();

            $integral_model->dataAdd($integral_insert);
        }

        if($pay_model==1){
            //微信支付
            $pay_controller = new \app\shop\controller\IndexWxPay($this->app);
            //支付
            $jsApiParameters= $pay_controller->createWeixinPay($this->payConfig(),$this->getUserInfo()['openid'],$this->_uniacid,"储值",['type' => 'Balance' , 'out_trade_no' => $insert['order_code'],'order_id'=>$order_id],$insert['pay_price']);

            $arr['pay_list']= $jsApiParameters;
        }else{

            $pay_model = new PayModel($this->payConfig());

            $jsApiParameters = $pay_model->aliPay($insert['order_code'],$insert['pay_price'],'充值订单',2,['openid'=>$this->getUserInfo()['openid'],'uniacid'=>$this->_uniacid,'type' => 'Balance' , 'out_trade_no' => $insert['order_code'],'order_id'=>$order_id]);

            $arr['pay_list']= $jsApiParameters;

            $arr['order_code']= $insert['order_code'];
        }

        return $this->success($arr);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-22 10:16
     * @功能说明:技师列表
     */
    public function coachList(){

        $input = $this->_param;

        $dis[] = ['a.uniacid','=',$this->_uniacid];

        $dis[] = ['a.status','=',2];

        $dis[] = ['a.user_id','>',0];

        $coach_model = new Coach();

        $shield_coach = $coach_model->getShieldCoach($this->getUserId());

        if(!empty($shield_coach)){

            $dis[] = ['a.id','not in',$shield_coach];
        }

        if(!empty($input['coach_name'])){

            $dis[] = ['a.coach_name','like','%'.$input['coach_name'].'%'];

        }

        $lat = !empty($input['lat'])?$input['lat']:0;

        $lng = !empty($input['lng'])?$input['lng']:0;

        $alh = 'ACOS(SIN(('.$lat.' * 3.1415) / 180 ) *SIN((a.lat * 3.1415) / 180 ) +COS(('.$lat.' * 3.1415) / 180 ) * COS((a.lat * 3.1415) / 180 ) *COS(('.$lng.' * 3.1415) / 180 - (a.lng * 3.1415) / 180 ) ) * 6378.137*1000 as distance';

        $coach_model = new Coach();

        $data = $coach_model->serviceCoachList($dis,$alh);

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-07 17:34
     * @功能说明:充值订单列表
     */
    public function balaceOrder(){

        $input = $this->_param;

        $dis[] = ['status','=',2];

        $dis[] = ['user_id','=',$this->getUserId()];

        $dis[] = ['type','<>',3];

        if(!empty($input['start_time'])){

            $dis[] = ['pay_time','between',"{$input['start_time']},{$input['end_time']}"];
        }

        $map[] = ['pay_type','=',2];

        $map[] = ['user_id','=',$this->getUserId()];

        if(!empty($input['start_time'])){

            $map[] = ['pay_time','between',"{$input['start_time']},{$input['end_time']}"];
        }

        $balance_model = new OrderList();

        $sql2 = $balance_model->where($map)->field('id,title,pay_price as true_price,create_time,pay_time,pay_price as now_balance,if(id=-1,-1,3) as type,over_time')->order('create_time desc,id desc')->buildSql();

        $sql = $this->balance_order->where($dis)->field('id,title,true_price,create_time,pay_time,now_balance,type,pay_time as over_time')->unionAll([$sql2])->order('create_time desc,id desc')->buildSql();

        $data = Db::table($sql.' a')->paginate(10)->toArray();

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $v['true_price'] = round($v['true_price'],2);

                $v['create_time'] = date('Y-m-d H:i:s',$v['create_time']);

                $v['pay_time']    = date('Y-m-d H:i:s',$v['pay_time']);

                $v['over_time']   = date('Y-m-d H:i:s',$v['over_time']);
            }
        }

        return $this->success($data);
    }


    /**
     * @author chenniang
     * @DataTime: 2024-09-14 14:10
     * @功能说明:
     */
    public function balanceOrderList(){

        $input = $this->_param;

        $dis[] = ['status','=',2];

        $dis[] = ['user_id','=',$this->getUserId()];

        $dis[] = ['type','<>',3];

        if(!empty($input['start_time'])){

            $dis[] = ['pay_time','between',"{$input['start_time']},{$input['end_time']}"];
        }

        $data = $this->balance_order->dataList($dis);

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $v['create_time'] = date('Y-m-d H:i:s',$v['create_time']);

                $v['pay_time']    = date('Y-m-d H:i:s',$v['pay_time']);
            }
        }

        return $this->success($data);
    }


    /**
     * @author chenniang
     * @DataTime: 2024-09-14 14:11
     * @功能说明:
     */
    public function balanceDiscountOrderList(){

        $input = $this->_param;

        $balance_model = new OrderList();

        $map[] = ['pay_type','=',2];

        $map[] = ['user_id','=',$this->getUserId()];

        if(!empty($input['start_time'])){

            $map[] = ['pay_time','between',"{$input['start_time']},{$input['end_time']}"];
        }

        $data = $balance_model->dataList($map,10);

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $v['pay_price'] = round($v['pay_price'],2);

                $v['create_time'] = date('Y-m-d H:i:s',$v['create_time']);

                $v['pay_time']    = date('Y-m-d H:i:s',$v['pay_time']);

                $v['over_time']   = date('Y-m-d H:i:s',$v['over_time']);
            }
        }
        return $this->success($data);
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-07 18:00
     * @功能说明:消费明细
     */
    public function payWaterBalance(){

        $input = $this->_param;

        $dis[] = ['user_id','=',$this->getUserId()];

//        $dis[] = ['type','=',2];

        if(!empty($input['start_time'])){

            $dis[] = ['create_time','between',"{$input['start_time']},{$input['end_time']}"];
        }

        $data = $this->water_model->where($dis)->field(['order_id','id','add','price','create_time','after_balance','uniacid as card_id','type','if(id=-1,1,2) as true_type'])->order('id desc')->paginate(10)->toArray();

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $v['price'] = round($v['price'],2);

                $v['create_time'] = date('Y-m-d H:i:s',$v['create_time']);
            }
        }
        return $this->success($data);
    }


    /**
     * @author chenniang
     * @DataTime: 2024-09-19 18:07
     * @功能说明:储值折扣卡消费记录
     */
    public function payWaterBalanceDiscount(){

        $input = $this->_param;

        $dis[] = ['user_id','=',$this->getUserId()];

        if(!empty($input['start_time'])){

            $dis[] = ['create_time','between',"{$input['start_time']},{$input['end_time']}"];
        }

        $water_model = new CardWater();

        $card_model = new UserCard();

        $data = $water_model->where($dis)->field(['order_id','id','add','cash as price','create_time','after_cash as after_balance','card_id','type','if(id=-1,1,1) as true_type','refund_id'])->order('id desc')->paginate(10)->toArray();

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $v['price'] = round($v['price'],2);

                $v['create_time'] = date('Y-m-d H:i:s',$v['create_time']);

                $v['title'] = $card_model->where(['id'=>$v['card_id']])->value('title');

                if($v['type']==1){

                    $order_model = new Order();

                    $title = $order_model->where(['id'=>$v['order_id']])->value('order_code');

                }elseif ($v['type']==1){

                    $order_model = new UpOrderList();

                    $title = $order_model->where(['id'=>$v['order_id']])->value('order_code');
                }else{

                    $order_model = new RefundOrder();

                    $title = $order_model->where(['id'=>$v['refund_id']])->value('order_code');
                }

                $v['goods_title'] = '订单号:'.$title;
            }
        }
        return $this->success($data);
    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-07 17:34
     * @功能说明:充值订单列表
     */
    public function payWater(){

        $input = $this->_param;

        $dis[] = ['user_id','=',$this->getUserId()];

        if(!empty($input['start_time'])){

            $dis[] = ['create_time','between',"{$input['start_time']},{$input['end_time']}"];
        }

        $sql2 = $this->water_model->where($dis)->field(['order_id','id','add','price','create_time','after_balance','uniacid as card_id','type','if(id=-1,1,2) as true_type','uniacid as refund_id'])->order('create_time desc,id desc')->buildSql();

        $water_model = new CardWater();

        $sql = $water_model->where($dis)->field(['order_id','id','add','cash as price','create_time','after_cash as after_balance','card_id','type','if(id=-1,1,1) as true_type','refund_id'])->unionAll([$sql2])->order('create_time desc,id desc')->buildSql();

        $data = Db::table($sql.' a')->paginate(10)->toArray();

        $card_model = new UserCard();

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $v['price'] = round($v['price'],2);

                $v['create_time'] = date('Y-m-d H:i:s',$v['create_time']);

                if($v['true_type']==1){

                    $v['title'] = $card_model->where(['id'=>$v['card_id']])->value('title');

                    if($v['type']==1){

                        $order_model = new Order();

                        $title = $order_model->where(['id'=>$v['order_id']])->value('order_code');

                    }elseif ($v['type']==2){

                        $order_model = new UpOrderList();

                        $title = $order_model->where(['id'=>$v['order_id']])->value('order_code');
                    }else{

                        $order_model = new RefundOrder();

                        $title = $order_model->where(['id'=>$v['refund_id']])->value('order_code');
                    }

                    $v['goods_title'] = '订单号:'.$title;

                }else{

                    $balance_order_model = new BalanceOrder();
                    //充值
                    if(in_array($v['type'],[1,5])){

                        $title = $balance_order_model->where(['id'=>$v['order_id']])->value('title');

                    }else{

                        $order_goods_model = $v['type']==4?new UpOrderList():new Order();

                        if($v['type']==6){

                            $order_goods_model = new UpOrderList();
                        } elseif (in_array($v['type'], [7, 9])) {

                            $order_goods_model = new PartnerOrder();
                        } elseif (in_array($v['type'], [8, 10])) {

                            $order_goods_model = new PartnerOrderJoin();
                        }

                        $title = $order_goods_model->where(['id'=>$v['order_id']])->value('order_code');

                        $title = '订单号:'.$title;
                    }

                    $v['goods_title'] = $title;
                }
            }
        }
        return $this->success($data);
    }










}
