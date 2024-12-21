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
use app\member\model\Level;
use app\member\model\Rights;
use app\member\model\RightsConnect;
use app\memberdiscount\model\Card;
use app\memberdiscount\model\OrderList;
use app\Rest;


use app\massage\model\Banner;

use app\massage\model\Car;
use app\massage\model\Config;

use app\massage\model\User;
use longbingcore\permissions\AdminMenu;
use think\App;

use think\facade\Db;
use think\Request;



class AdminCard extends AdminRest
{

    protected $config_model;

    protected $card_model;

    protected $coupon_model;

    protected $order_model;


    public function __construct(App $app) {

        parent::__construct($app);

        $this->config_model = new \app\memberdiscount\model\Config();

        $this->card_model = new Card();

        $this->coupon_model = new \app\memberdiscount\model\Coupon();

        $this->order_model = new OrderList();
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-16 19:25
     * @功能说明:套餐列表
     */
    public function cardSelect(){

        $input = $this->_param;

        $dis = [

            'uniacid' => $this->_uniacid,

            'status'  => 1
        ];

        $data = $this->card_model->where($dis)->order('top desc,id desc')->select()->toArray();

        return $this->success($data);
    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-23 09:20
     * @功能说明:配置详情
     */
   public function configInfo(){

       $input = $this->_param;

       $dis = [

           'uniacid' => $this->_uniacid
       ];

       $data = $this->config_model->dataInfo($dis);

       return $this->success($data);
   }


    /**
     * @author chenniang
     * @DataTime: 2024-09-03 18:45
     * @功能说明:修改配置
     */
   public function configUpdate(){

       $input = $this->_input;

       $dis = [

           'uniacid' => $this->_uniacid
       ];

       $data = $this->config_model->dataUpdate($dis,$input);

       return $this->success($data);
   }


    /**
     * @author chenniang
     * @DataTime: 2024-09-03 18:46
     * @功能说明:套餐列表
     */
   public function cardList(){

       $input = $this->_param;

       $dis[] = ['uniacid','=',$this->_uniacid];

       $dis[] = ['status','>',-1];

       $data = $this->card_model->dataList($dis,$input['limit']);

       return $this->success($data);

   }


    /**
     * @author chenniang
     * @DataTime: 2024-09-04 10:08
     * @功能说明:添加套餐
     */
   public function cardAdd(){

       $input = $this->_input;

       $insert = [

           'uniacid' => $this->_uniacid,

           'day'     => $input['day'],

           'title'   => $input['title'],

           'price'   => $input['price'],

           'init_price'=> $input['init_price'],

           'top'=> $input['top'],

           'icon'=> $input['icon'],

           'create_time' => time()
       ];

       $res = $this->card_model->insert($insert);

       $id  = $this->card_model->getLastInsID();

       if(!empty($input['coupon'])){

           foreach ($input['coupon'] as $key=>$value){

               $map[$key]=[

                   'uniacid' => $this->_uniacid,

                   'coupon_id'=> $value['coupon_id'],

                   'num'=> $value['num'],

                   'card_id' => $id
               ];
           }

           $this->coupon_model->saveAll($map);
       }

       return $this->success($res);
   }


    /**
     * @author chenniang
     * @DataTime: 2024-09-04 10:08
     * @功能说明:添加套餐
     */
    public function cardUpdate(){

        $input = $this->_input;

        $dis = [

            'id' => $input['id']
        ];

        $insert = [

            'uniacid' => $this->_uniacid,

            'day'     => $input['day'],

            'title'   => $input['title'],

            'price'   => $input['price'],

            'init_price'=> $input['init_price'],

            'top'=> $input['top'],

            'icon'=> $input['icon'],

        ];

        $res = $this->card_model->dataUpdate($dis,$insert);

        $this->coupon_model->where(['card_id'=>$input['id']])->delete();

        if(!empty($input['coupon'])){

            foreach ($input['coupon'] as $key=>$value){

                $map[$key]=[

                    'uniacid' => $this->_uniacid,

                    'coupon_id'=> $value['coupon_id'],

                    'num'=> $value['num'],

                    'card_id' => $input['id']
                ];
            }

            $this->coupon_model->saveAll($map);
        }

        return $this->success($res);
    }


    /**
     * @author chenniang
     * @DataTime: 2024-09-04 10:19
     * @功能说明:套餐详情
     */
    public function cardInfo(){

        $input = $this->_param;

        $dis = [

            'id' => $input['id']
        ];

        $data = $this->card_model->dataInfo($dis);

        $data['coupon'] = $this->card_model->cardCoupon($input['id']);

        return $this->success($data);
    }


    /**
     * @author chenniang
     * @DataTime: 2024-09-04 10:20
     * @功能说明:套餐上下架|删除
     */
    public function cardStatusUpdate(){

        $input = $this->_input;

        $dis = [

            'id' => $input['id']
        ];

        $data = $this->card_model->dataUpdate($dis,['status'=>$input['status']]);

        return $this->success($data);
    }


    /**
     * @author chenniang
     * @DataTime: 2024-09-06 17:06
     * @功能说明:套餐购买列表
     */
    public function orderList(){

        $input = $this->_param;

        $dis[] = ['pay_type','=',2];

        if(!empty($input['order_code'])){

            $dis[] = ['order_code','like','%'.$input['order_code'].'%'];
        }

        if(!empty($input['start_time'])){

            $dis[] = ['create_time','between',"{$input['start_time']},{$input['end_time']}"];
        }

        $data = $this->order_model->dataList($dis,$input['limit']);

        $user_model = new User();

        $coach_model= new Coach();

        $comm_model = new Commission();

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $v['over_time'] = $v['member_take_effect_time']+$v['day']*86400;

                $v['nickName'] = $user_model->where(['id'=>$v['user_id']])->value('nickName');

                if(!empty($v['coach_id'])){

                    $v['coach_name'] = $coach_model->where(['id'=>$v['coach_id']])->value('coach_name');
                }
                //返佣信息
                $v['comm_data'] = $comm_model->where(['order_id'=>$v['id'],'status'=>2,'type'=>24])->field('cash,balance')->find();
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

        $user_model = new User();

        $coach_model= new Coach();

        $data['nickName'] = $user_model->where(['id'=>$data['user_id']])->value('nickName');

        if(!empty($data['coach_id'])){

            $data['coach_name'] = $coach_model->where(['id'=>$data['coach_id']])->value('coach_name');
        }

        return $this->success($data);
    }







}
