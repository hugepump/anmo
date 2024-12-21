<?php
namespace app\balancediscount\controller;

use app\ApiRest;
use app\balancediscount\model\Card;
use app\balancediscount\model\OrderList;
use app\balancediscount\model\UserCard;
use app\massage\model\Coach;
use app\massage\model\Commission;
use app\massage\model\Integral;
use longbingcore\wxcore\PayModel;
use think\App;
use think\facade\Db;




class IndexCard extends ApiRest
{



    protected $card_model;

    protected $coupon_model;

    protected $order_model;


    public function __construct(App $app) {

        parent::__construct($app);

        $this->card_model = new Card();

        $this->order_model = new OrderList();

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-16 19:25
     * @功能说明:套餐列表
     */
    public function cardList(){

        $input = $this->_param;

        $dis = [

            'uniacid' => $this->_uniacid,

            'status'  => 1
        ];

        $data['list'] = $this->card_model->where($dis)->order('top desc,id desc')->select()->toArray();

        if(!empty($input['coach_id'])){

            $coach_model = new Coach();

            $data['coach_name'] = $coach_model->where(['id'=>$input['coach_id']])->value('coach_name');
        }

        return $this->success($data);
    }


    /**
     * @author chenniang
     * @DataTime: 2024-09-04 11:08
     * @功能说明:购买套餐
     */
    public function payOrder(){

        $input = $this->_input;

        $card = $this->card_model->dataInfo(['id'=>$input['card_id'],'status'=>1]);

        if(empty($card)){

            $this->errorMsg('套餐已下架');
        }

        $order_insert = [

            'uniacid' => $this->_uniacid,

            'user_id' => $this->_user['id'],

            'order_code'=> orderCode(),

            'pay_price' => $card['price'],

            //'true_price'=> $card['price'],

            'card_id'   => $card['id'],

            'title'     => $card['title'],

            'discount'  => $card['discount'],

            'balance'   => getConfigSetting($this->_uniacid,'balance_discount_balance'),

            'create_time'=> time(),

            'pay_model' => $input['pay_model'],

            'app_pay'   => $this->is_app,

            'month'   => $card['month'],

            'coach_id'   => !empty($input['coach_id'])?$input['coach_id']:0,

            'over_time' => strtotime("+{$card['month']} months"),

            'total_cash' => round($card['price']/$card['discount']*10,2),

            'operating_costs' => round($card['price']/$card['discount']*10-$card['price'],2),
        ];

        Db::startTrans();

        $this->order_model->dataAdd($order_insert);

        $order_id = $this->order_model->getLastInsID();
        //添加技师佣金
        if(!empty($order_insert['coach_id'])){

            $order = $this->order_model->dataInfo(['id'=>$order_id]);

            $balance_discount_cash = getConfigSetting($this->_uniacid,'balance_discount_cash');

            $balance_discount_integral = getConfigSetting($this->_uniacid,'balance_discount_integral');

            if($balance_discount_cash==1){
                //赠送佣金
                $comm_model = new Commission();

                $comm_model->memberDiscountCoachCommission($order,$input['pay_model'],25);
            }

            if($balance_discount_integral==1){
                //赠送积分
                $integral_model = new Integral();

                $integral_model->integralAdd($order);
            }
        }

        Db::commit();
        //如果是0元
        if($order_insert['pay_price']<=0){

            $this->order_model->orderResult($order_insert['order_code'],$order_insert['order_code']);

            return $this->success(true);
        }
        if ($input['pay_model']==3){

            $pay_model = new PayModel($this->payConfig());

            $jsApiParameters  = $pay_model->aliPay($order_insert['order_code'],$order_insert['pay_price'],'BalancediscountOrder',7,['openid'=>$this->getUserInfo()['openid'],'uniacid'=>$this->_uniacid,'type' => 'Blancediscount' , 'out_trade_no' => $order_insert['order_code'],'order_id'=>(string)$order_id],$order_insert['pay_price']);

            $arr['pay_list']  = $jsApiParameters;

            $arr['order_code']= $order_insert['order_code'];

            $arr['order_id']  = $order_id;

        }else{
            //微信支付
            $pay_controller = new \app\shop\controller\IndexWxPay($this->app);
            //支付
            $jsApiParameters= $pay_controller->createWeixinPay($this->payConfig(),$this->getUserInfo()['openid'],$this->_uniacid,"anmo",['type' => 'Balancediscount' , 'out_trade_no' => $order_insert['order_code'],'order_id'=>(string)$order_id],$order_insert['pay_price']);

            $arr['pay_list']= $jsApiParameters;

            $arr['order_id']= $order_id;
        }

        return $this->success($arr);
    }




    /**
     * @author chenniang
     * @DataTime: 2024-09-06 17:06
     * @功能说明:套餐购买列表
     */
    public function orderList(){

        $input = $this->_param;

        $user_card_model = new UserCard();

        $user_card_model->where(['status'=>1])->where('over_time','<',time())->update(['status'=>2]);

        $dis[] = ['a.user_id','=',$this->_user['id']];

        $dis[] = ['a.pay_type','=',2];

        $data = $this->order_model->alias('a')
                ->join('massage_balance_discount_user_card b','a.id = b.card_order_id','left')
                ->where($dis)
                ->field('a.*,b.cash,b.over_time')
                ->group('a.id')
                ->order('b.status,b.cash desc,a.id desc')
                ->paginate(10)
                ->toArray();

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $v['is_over']  = $v['over_time']>time()?0:1;

                $v['over_time']= date('Y.m.d H:i:s',$v['over_time']);

                $v['pay_time'] = date('Y.m.d H:i:s',$v['pay_time']);

                $v['cash'] = round($v['cash'],2);

                $v['pay_price'] = round($v['pay_price'],2);
            }
        }

        return $this->success($data);
    }















}
