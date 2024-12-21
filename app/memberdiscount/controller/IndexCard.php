<?php
namespace app\memberdiscount\controller;
use app\AdminRest;
use app\ApiRest;

use app\massage\model\ArticleList;
use app\massage\model\CateConnect;
use app\massage\model\City;
use app\massage\model\Coach;
use app\massage\model\CoachCollect;
use app\massage\model\CoachTimeList;
use app\massage\model\Comment;
use app\massage\model\Commission;
use app\massage\model\ConfigSetting;
use app\massage\model\Coupon;
use app\massage\model\CouponRecord;
use app\massage\model\MassageConfig;
use app\massage\model\Order;
use app\massage\model\PayConfig;
use app\massage\model\Service;
use app\massage\model\ServiceCoach;
use app\massage\model\ShortCodeConfig;
use app\massage\model\StoreList;
use app\member\model\Growth;
use app\member\model\Level;
use app\member\model\Rights;
use app\member\model\RightsConnect;
use app\memberdiscount\model\Card;
use app\memberdiscount\model\OrderCoupon;
use app\memberdiscount\model\OrderList;
use app\Rest;


use app\massage\model\Banner;

use app\massage\model\Car;
use app\massage\model\Config;

use app\massage\model\User;
use longbingcore\permissions\AdminMenu;
use longbingcore\wxcore\PayModel;
use think\App;

use think\facade\Db;
use think\Request;



class IndexCard extends ApiRest
{

    protected $config_model;

    protected $card_model;

    protected $coupon_model;

    protected $order_model;


    public function __construct(App $app) {

        parent::__construct($app);

        $this->config_model = new \app\memberdiscount\model\Config();

        $this->card_model = new Card();

        $this->order_model = new OrderList();

        $this->coupon_model = new \app\memberdiscount\model\Coupon();
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-16 19:25
     * @功能说明:套餐列表
     */
    public function cardList(){

        $user_model = new User();

        $arr['member_discount_time'] = $user_model->where(['id'=>$this->_user['id']])->value('member_discount_time');

        $arr['member_status'] = $arr['member_discount_time']>time()?1:0;

        $config_model = $this->config_model->dataInfo(['uniacid'=>$this->_uniacid]);

        $arr['member_discount_time'] = date('Y.m.d',$arr['member_discount_time']);

        $arr['text']     = $config_model['text'];

        $arr['discount'] = $config_model['discount'];

        $arr['balance']  = $config_model['balance'];

        $input = $this->_param;

        $dis = [

            'uniacid' => $this->_uniacid,

            'status'  => 1
        ];

        $arr['list'] = $this->card_model->where($dis)->order('top desc,id desc')->select()->toArray();

        if(!empty($arr['list'])){

            foreach ($arr['list'] as &$value){

                $value['difference_price'] = round($value['init_price'] - $value['price'],2);
            }
        }

        if(!empty($this->getUserId())){

            $cap_dis[] = ['user_id','=',$this->getUserId()];

            $cap_dis[] = ['status','in',[1,2,3,4]];

            $coach_model = new Coach();
            //查看是否是团长
            $cap_info = $coach_model->where($cap_dis)->field('status,coach_name,work_img')->order('status')->find();

            $cap_info = !empty($cap_info)?$cap_info->toArray():[];
            //-1表示未申请团长，1申请中，2已通过，3取消,4拒绝
            $arr['coach_status'] = !empty($cap_info)?$cap_info['status']:-1;

            $arr['coach_info'] = !empty($cap_info)?$cap_info:[];
        }

        return $this->success($arr);
    }


    /**
     * @author chenniang
     * @DataTime: 2024-09-06 16:13
     * @功能说明:卡券权益
     */
    public function cardDiscount(){

        $input = $this->_param;

        $card = $this->card_model->dataInfo(['id'=>$input['card_id']]);

        if(empty($card)){

            $this->errorMsg('套餐已下架');
        }

        $coupon = $this->card_model->cardCoupon($card['id']);

        if(!empty($coupon)){

            $data['coupon_discount'] = array_sum(array_column($coupon,'discount_price'));
        }else{

            $data['coupon_discount'] = 0;
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

        $coupon = $this->card_model->cardCoupon($card['id']);

        $config = $this->config_model->dataInfo(['uniacid'=>$this->_uniacid]);

        $card['balance'] = $config['balance'];

        $order_insert = [

            'uniacid' => $this->_uniacid,

            'user_id' => $this->_user['id'],

            'order_code' => orderCode(),

            'pay_price' => $card['price'],

            'card_id'   => $card['id'],

            'title'   => $card['title'],

            'balance' => $card['balance'],

            'day'   => $card['day'],

            'create_time' => time(),

            'pay_model' => $input['pay_model'],

            'app_pay' => $this->is_app,

            'coach_id' => !empty($input['card_id'])?$input['coach_id']:0,
        ];

        Db::startTrans();

        $this->order_model->dataAdd($order_insert);

        $order_id = $this->order_model->getLastInsID();

        if(!empty($coupon)){

            foreach ($coupon as $key=> $value){

                $map[$key]=[

                    'order_id' => $order_id,

                    'coupon_id'=> $value['coupon_id'],

                    'num'      => $value['num']
                ];
            }

            $coupon_model = new OrderCoupon();

            $coupon_model->saveAll($map);
        }
        //添加技师佣金
        if($card['balance']>0&&!empty($order_insert['coach_id'])){

            $user_model = new User();

            $member_discount_time = $user_model->where(['id'=>$this->_user['id']])->value('member_discount_time');

            if($member_discount_time<time()){

                $comm_model = new Commission();

                $order = $this->order_model->dataInfo(['id'=>$order_id]);

                $comm_model->memberDiscountCoachCommission($order,$input['pay_model']);
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

            $jsApiParameters  = $pay_model->aliPay($order_insert['order_code'],$order_insert['pay_price'],'MemberdiscountOrder',6,['openid'=>$this->getUserInfo()['openid'],'uniacid'=>$this->_uniacid,'type' => 'Memberdiscount' , 'out_trade_no' => $order_insert['order_code'],'order_id'=>(string)$order_id],$order_insert['pay_price']);

            $arr['pay_list']  = $jsApiParameters;

            $arr['order_code']= $order_insert['order_code'];

            $arr['order_id']  = $order_id;

        }else{
            //微信支付
            $pay_controller = new \app\shop\controller\IndexWxPay($this->app);
            //支付
            $jsApiParameters= $pay_controller->createWeixinPay($this->payConfig(),$this->getUserInfo()['openid'],$this->_uniacid,"anmo",['type' => 'Memberdiscount' , 'out_trade_no' => $order_insert['order_code'],'order_id'=>(string)$order_id],$order_insert['pay_price']);

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

        $dis[] = ['user_id','=',$this->_user['id']];

        $dis[] = ['pay_type','=',2];

        $data = $this->order_model->dataList($dis,10);

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $v['over_time'] = $v['member_take_effect_time']+$v['day']*86400;

                $v['member_take_effect_time'] = date('Y.m.d H:i:s',$v['member_take_effect_time']);

                $v['over_time'] = date('Y.m.d H:i:s',$v['over_time']);

                $v['pay_time'] = date('Y.m.d H:i:s',$v['pay_time']);
            }
        }

        return $this->success($data);
    }


    /**
     * @author chenniang
     * @DataTime: 2024-09-06 18:24
     * @功能说明:订单详情
     */
    public function orderInfo(){

        $input = $this->_param;

        $data = $this->order_model->dataInfo(['id'=>$input['id']]);

        $data['over_time'] = $data['member_take_effect_time']+$data['day']*86400;

        $data['member_take_effect_time'] = date('Y.m.d H:i:s',$data['member_take_effect_time']);

        $data['over_time'] = date('Y.m.d H:i:s',$data['over_time']);

        $data['pay_time']  = date('Y.m.d H:i:s',$data['pay_time']);

        return $this->success($data);


    }




















}
