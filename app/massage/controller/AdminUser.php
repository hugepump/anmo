<?php
namespace app\massage\controller;
use app\adapay\model\Record;
use app\AdminRest;
use app\balancediscount\model\OrderList;
use app\balancediscount\model\UserCard;
use app\coachbroker\model\CoachBroker;
use app\fdd\model\FddAgreementRecord;
use app\fdd\model\FddAttestationRecord;
use app\massage\model\AdminWater;
use app\massage\model\ChannelList;
use app\massage\model\ChannelQr;
use app\massage\model\ChannelScanQr;
use app\massage\model\Coach;
use app\massage\model\CoachChangeLog;
use app\massage\model\Commission;
use app\massage\model\CommShare;
use app\massage\model\ConfigSetting;
use app\massage\model\Coupon;
use app\massage\model\CouponRecord;
use app\massage\model\Order;
use app\massage\model\Salesman;
use app\massage\model\ShieldList;
use app\massage\model\User;
use app\massage\model\UserComment;
use app\massage\model\UserLabelData;
use app\member\info\PermissionMember;
use app\member\model\Config;
use app\member\model\Growth;
use app\member\model\Level;
use app\memberdiscount\info\PermissionMemberdiscount;
use app\memberdiscount\model\Card;
use app\shop\model\Article;
use app\shop\model\Banner;
use app\shop\model\Date;
use app\massage\model\OrderGoods;
use app\massage\model\RefundOrder;
use app\shop\model\Wallet;
use http\Env;
use longbingcore\wxcore\Fdd;
use LongbingUpgrade;
use function PHPSTORM_META\type;
use think\App;
use app\massage\model\User as Model;
use think\facade\Db;


class AdminUser extends AdminRest
{


    protected $model;

    protected $order_goods_model;

    protected $refund_order_model;

    protected $comm_share_model;



    public function __construct(App $app) {

        parent::__construct($app);

        $this->model = new Model();

        $this->order_goods_model  = new OrderGoods();

        $this->refund_order_model = new RefundOrder();

        $this->comm_share_model   = new CommShare();

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-24 10:24
     * @功能说明:用户列表
     */
    public function userList(){

        $input = $this->_param;

        $dis[] = ['uniacid','=',$this->_uniacid];

        if(!empty($input['user_status'])){

            $dis[] = ['user_status','=',$input['user_status']];
        }
        //是否授权
        if(!empty($input['type'])){

            if($input['type']==1){

                $dis[] = ['nickName','=',''];

            }elseif($input['type']==2){

                $dis[] = ['nickName','<>',''];
            }elseif ($input['type']==3){

                $dis[] = ['phone','=',''];

            }else{

                $dis[] = ['phone','<>',''];
            }
        }

        $where = [];

        if(!empty($input['nickName'])){

            $where[] = ['nickName','like','%'.$input['nickName'].'%'];

            $where[] = ['phone','like','%'.$input['nickName'].'%'];
        }

        if(!empty($input['start_time'])&&!empty($input['end_time'])){

            $start_time = $input['start_time'];

            $end_time = $input['end_time'];

            $dis[] = ['create_time','between',"$start_time,$end_time"];
        }

        if(!empty($input['id'])){

            $dis[] = ['id','=',$input['id']];
        }

        if(!empty($input['phone'])){

            $dis[] = ['phone','like','%'.$input['phone'].'%'];
        }

        if(!empty($input['province'])){

            $dis[] = ['province','=',$input['province']];
        }

        if(!empty($input['card_id'])){

            $dis[] = ['member_discount_id','=',$input['card_id']];
        }

        if(!empty($input['city'])){

            $dis[] = ['city','=',$input['city']];
        }

        if(!empty($input['area'])){

            $dis[] = ['area','=',$input['area']];
        }

        if(isset($input['source_type'])){

            $dis[] = ['source_type','=',$input['source_type']];
        }

        $map1 = $map2=[];

        if($this->_user['is_admin']==0){

            $map1 = [

                ['admin_id' ,'=', $this->_user['admin_id']],
            ];

            $city = \app\massage\model\Admin::adminCityData($this->_user['admin_id']);

            $map2 = [

                ['source_type' ,'=', 0],
            ];

            if(!empty($city)){

                $map2[] = ['province','=',$city['province']];

                if(in_array($city['city_type'],[1])){

                    $map2[] = ['city','=',$city['city']];
                }

                if(in_array($city['city_type'],[2])){

                    $map2[] = ['area','=',$city['area']];
                }
            }
        }

        $sort = 'id desc';

        if(!empty($input['sort'])){

            if($input['sort']==1){

                $sort = 'balance desc,id desc';
            }else{
                $sort = 'balance,id desc';
            }
        }

        $data = $this->model->dataList($dis,$input['limit'],$where,'*',$sort,$map1,$map2);

        if(!empty($data['data'])){

            $label_model = new UserLabelData();

            $level_model = new Level();

            $scan_model  = new ChannelScanQr();

            $admin_model = new \app\massage\model\Admin();

            $member_card_model = new Card();

            $member_auth = false;

            $m_auth = new PermissionMember($this->_uniacid);

            if($m_auth->pAuth()==true){

                $config_model = new Config();

                $config = $config_model->dataInfo(['uniacid'=>$this->_uniacid]);

                $member_auth = $config['status'];

                if($member_auth==1){

                    $create_user = $admin_model->where(['is_admin'=>1])->value('id');
                }
            }
            //会员折扣权限
            $member_discount_auth = memberDiscountAuth($this->_uniacid)['status'];

            foreach ($data['data'] as &$v){

                $v['user_label'] = $label_model->getUserLabel($v['id']);

                if($member_auth==1){

                    $v['member_level'] = $level_model->getUserLevelV2($v,$create_user);
                }
                //消费金额
                $v['user_use_cash']= $this->model->userUseCash($v['id']);

                if($v['source_type']!=0){
                    //扫的渠道码名称
                    $v['channel_qr'] = $scan_model->getQrTitle($user_id);
                }

                if($member_discount_auth==true&&$v['member_discount_time']>time()){

                    $v['member_discount_title'] = $member_card_model->where(['id'=>$v['member_discount_id']])->value('title');
                }
            }
        }
        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-08-28 23:03
     * @功能说明:佣金记录
     */
    public function commList(){

        $input = $this->_param;

        $order_model= new Order();

        $comm_model = new Commission();

        $change_model = new CoachChangeLog();

        $order_model->coachBalanceArr($this->_uniacid);

        $queue_model = new \app\massage\model\Queue();

        $queue_model->queueDo(1);

        $version = getConfigSetting($this->_uniacid,'version');

        if($version==0){

            $comm_model->where(['type'=>16])->where('status','>',-1)->update(['status'=>-2]);

            $config_setting_model = new ConfigSetting();

            $config_setting_model->dataUpdate(['version'=>1],$this->_uniacid);
        }

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['type','not in',[11,15,7,23]];

        if(!empty($input['status'])){

            $dis[] = ['status','=',$input['status']];
        }else{

            $dis[] = ['status','>',-1];
        }

        if($this->_user['is_admin']==0){

            $dis[] = ['admin_id','in',$this->admin_arr];
        }

        if(!empty($input['type'])){

            if($input['type']==2){

                $dis[] = ['type','in',[2,5,6]];

            }elseif ($input['type']==8){

                $dis[] = ['type','in',[8,13]];

            } else{

                $dis[] = ['type','=',$input['type']];
            }
        }

        if(!empty($input['order_code'])){

            $dis[] = ['order_code','like','%'.$input['order_code'].'%'];
        }

        if(!empty($input['start_time'])&&!empty($input['end_time'])){

            $dis[] = ['create_time','between',"{$input['start_time']},{$input['end_time']}"];
        }

        if(!empty($input['top_name'])){

            if(empty($input['type'])){

                $this->errorMsg('请选择类型');
            }

            $data = $comm_model->getIdByNameRecordList($input['type'],$dis,$input['top_name'],$input['limit']);

        }else{

            $where[] = ['type','=',16];

            $where[] = ['cash','>',0];

            $data = $comm_model->where($dis)
                ->where(function ($query) use ($where){
                    $query->whereOr($where);
                })
                ->group('id')
                ->order('order_id desc,id desc')
                ->paginate($input['limit'])
                ->toArray();
        }

        $commission_custom = getConfigSetting($this->_uniacid,'commission_custom');

        $material_text     = getConfigSetting($this->_uniacid,'material_text');

        $adapay_model      = new Record();

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $order = Db::name('massage_service_order_list')->where(['id'=>$v['order_id']])->field('add_pid,free_fare,coupon_bear_type,init_service_price,order_code,pay_type,pay_price,transaction_id,car_price,material_price,coupon_id')->find();

                if(!empty($order)){

                    $v = array_merge($v,$order);
                }

                if(in_array($v['type'],[17,18,19,20,21,22])){

                    $v['pay_price'] = $v['order_cash'];
                }

                $v['car_admin'] = $v['type']==13?1:0;

                $is_adapay = $adapay_model->dataInfo(['commission_id'=>$v['id']]);

                $v['is_adapay']= !empty($is_adapay)?1:0;

                $v['nickName'] = $this->model->where(['id'=>$v['user_id']])->value('nickName');

                $v['coach_cash_control'] = $v['status']==2&&$v['admin_id']==0&&in_array($v['type'],[3,8])&&$v['top_id']==0?1:0;

                if(!in_array($v['type'],[17,18,19,20,21,22])&&!empty($order)){

                    if($v['coupon_bear_type']==2&&$v['coupon_id']>0){

                        $v['pay_price'] = $v['pay_price'].' 优惠前¥'.$v['init_service_price'];
                    }
                    if($v['car_price']>0){

                        $v['pay_price'] = $v['pay_price'].' 含车费'.$v['car_price'];
                    }
                    if($v['material_price']>0){

                        $v['pay_price'] = $v['pay_price'].' 含'.$material_text.$v['material_price'];
                    }

                    if($v['top_id']==0&&$v['car_cash']>0){

                        $v['cash'] = $v['cash'].' 含车费'.$v['car_cash'];
                    }
                }

                $table_data = $comm_model->getCommTable($v['type']);

                if(!empty($table_data)){

                    $table = $table_data['table'];

                    $filed = $table_data['filed'];

                    $title = $table_data['name'];

                    $v['top_name'] = Db::name($table)->where(["id"=>$v[$filed]])->value($title);
                }

                if(in_array($v['type'],[3,8])&&empty($v['top_id'])){

                    $order_id = !empty($v['add_pid'])?$v['add_pid']:$v['order_id'];

                    $v['top_name'] = $change_model->where(['order_id'=>$order_id,'is_new'=>1])->value('now_coach_name');
                }

                if (in_array($v['type'],[16,21,22])){

                    $v['top_name'] = '平台';

                    $v['cash'] = $v['type']==16?$v['company_cash']:$v['cash'];
                }

                if($v['type']==2){

                    if($commission_custom==0){

                        $v['balance'] = '平台抽成-'.$v['balance'];
                    }

                    $coach_cash = $v['coach_cash']>0?'包含'.$v['coach_cash'].'线下服务费，':'';

                    $car_cash   = $v['car_cash']>0?'包含'.$v['car_cash'].'线下车费':'';

                    $v['cash'] = !empty($coach_cash)||!empty($car_cash)?$v['cash'].'('.$coach_cash.$car_cash.')':$v['cash'];
                }

                $share_cash = $poster_cash = $coach_balance_cash= $skill_cash=$coupon_cash = $share_car_cash =  $balance_discount_cash= 0;

                $share_data = [];
                //技师查询是否有分摊金额
                if(in_array($v['type'],[3])){

                    $share_type = $v['type']==3?1:3;

                    $share_data = $this->comm_share_model->where(['order_id'=>$v['order_id'],'type'=>$share_type])->field('sum(share_cash) as share_cash,cash_type')->group('cash_type')->select()->toArray();
                }
                //代理商分摊金额
                if(in_array($v['type'],[2,5,6])){

                    $share_data = $this->comm_share_model->where(['order_id'=>$v['order_id'],'type'=>2,'share_id'=>$v['top_id']])->field('sum(share_cash) as share_cash,cash_type')->group('cash_type')->select()->toArray();
                }

                if(in_array($v['type'],[16])){

                    $share_car_cash = $this->comm_share_model->where(['order_id'=>$v['order_id'],'cash_type'=>5,'comm_id'=>$v['id']])->sum('share_cash');
                }

                if(!empty($share_data)){

                    foreach ($share_data as $value){

                        if($value['cash_type']==0){

                            $share_cash = $value['share_cash'];
                        }elseif ($value['cash_type']==2){

                            $poster_cash = $value['share_cash'];

                        }elseif ($value['cash_type']==3){

                            $coach_balance_cash = $value['share_cash'];

                        }elseif ($value['cash_type']==4){

                            $skill_cash = $value['share_cash'];

                        }elseif ($value['cash_type']==6){

                            $coupon_cash = $value['share_cash'];

                        }elseif ($value['cash_type']==7){

                            $balance_discount_cash = $value['share_cash'];
                        }
                    }
                }

                if($coach_balance_cash>0&&$coach_balance_cash>0){

                    $v['cash'] .= '  储值扣款:'.$coach_balance_cash.'元';
                }

                if(!empty($share_cash)&&$share_cash>0){

                    $v['cash'] .= '  被分摊金额:'.$share_cash.'元';
                }

                if(!empty($poster_cash)&&$poster_cash>0){

                    $v['cash'] .= '  被分摊广告费:'.$poster_cash.'元';
                }

                if(!empty($skill_cash)&&$skill_cash>0){

                    $v['cash'] .= '  技术服务费:'.$skill_cash.'元';
                }
                if(!empty($coupon_cash)&&$coupon_cash>0){

                    $v['cash'] .= '  优惠券分摊:'.$coupon_cash.'元';
                }

                if(!empty($share_car_cash)&&$share_car_cash>0){

                    $v['cash'] .= '  车费分摊:'.$share_car_cash.'元';
                }

                if(!empty($balance_discount_cash)&&$balance_discount_cash>0){

                    $v['cash'] .= '  储值折扣分摊:'.$balance_discount_cash.'元';
                }
                //手续费
                if($v['point_cash']>0){

                    $v['cash'] .= '  手续费:'.$v['point_cash'].'元';
                }

                $v['type'] = $v['type']==13?8:$v['type'];
            }
        }

        return $this->success($data);
    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-08-28 23:03
     * @功能说明:佣金记录
     */
    public function cashList(){

        $input = $this->_param;

        $order_model = new Order();

        $change_model = new CoachChangeLog();

        $order_model->coachBalanceArr($this->_uniacid);

        $queue_model = new \app\massage\model\Queue();

        $queue_model->queueDo(1);

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['cash','>',0];

        $dis[] = ['type','not in',[11,15,7,23,21,22]];

        if(!empty($input['status'])){

            $dis[] = ['status','=',$input['status']];
        }else{

            $dis[] = ['status','>',-1];
        }

        if($this->_user['is_admin']==0){

            $dis[] = ['admin_id','in',$this->admin_arr];
        }

        if(!empty($input['type'])){

            if($input['type']==2){

                $dis[] = ['type','in',[2,5,6]];

            }elseif ($input['type']==8){

                $dis[] = ['type','in',[8,13]];

            } else{

                $dis[] = ['type','=',$input['type']];
            }
        }

        if(!empty($input['order_code'])){

            $dis[] = ['order_code','like','%'.$input['order_code'].'%'];
        }

        if(!empty($input['start_time'])&&!empty($input['end_time'])){

            $dis[] = ['create_time','between',"{$input['start_time']},{$input['end_time']}"];
        }

        $comm_model = new Commission();

        if(!empty($input['top_name'])){

            if(empty($input['type'])){

                $this->errorMsg('请选择类型');
            }

            $data = $comm_model->getIdByNameRecordList($input['type'],$dis,$input['top_name'],$input['limit']);

        }else{

            $where[] = ['type','=',16];

            $where[] = ['cash','>',0];

            $data = $comm_model->where($dis)
                ->where(function ($query) use ($where){
                    $query->whereOr($where);
                })
                ->group('id')
                ->order('order_id desc,id desc')
                ->paginate($input['limit'])
                ->toArray();
        }

        $admin_model = new \app\massage\model\Admin();

        $commission_custom = getConfigSetting($this->_uniacid,'commission_custom');

        $material_text = getConfigSetting($this->_uniacid,'material_text');

        $adapay_model = new Record();

        $wallet_check_auth = $admin_model->where(['id'=>$this->_user['admin_id']])->value('wallet_check_auth');

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $order = Db::name('massage_service_order_list')->where(['id'=>$v['order_id']])->field('add_pid,free_fare,coupon_bear_type,init_service_price,order_code,pay_type,pay_price,transaction_id,car_price,material_price')->find();

                if(!empty($order)){

                    $v = array_merge($v,$order);
                }

                if(in_array($v['type'],[17,18,19,20,21,22])){

                    $v['pay_price'] = $v['order_cash'];
                }

                $v['car_admin'] = $v['type']==13?1:0;

                $is_adapay = $adapay_model->dataInfo(['commission_id'=>$v['id']]);

                $v['is_adapay']= !empty($is_adapay)?1:0;

                $v['nickName'] = $this->model->where(['id'=>$v['user_id']])->value('nickName');

                $v['coach_cash_control'] = $v['status']==2&&$v['admin_id']==0&&in_array($v['type'],[3,8])&&$v['top_id']==0?1:0;

                if(!in_array($v['type'],[17,18,19,20,21,22])){

                    if($v['coupon_bear_type']==2){

                        $v['pay_price'] = $v['pay_price'].' 优惠前¥'.$v['init_service_price'];
                    }
                    if($v['car_price']>0){

                        $v['pay_price'] = $v['pay_price'].' 含车费'.$v['car_price'];
                    }
                    if($v['material_price']>0){

                        $v['pay_price'] = $v['pay_price'].' 含'.$material_text.$v['material_price'];
                    }

                    if($v['top_id']==0&&$v['car_cash']>0){

                        $v['cash'] = $v['cash'].' 含车费'.$v['car_cash'];
                    }
                }

                $table_data = $comm_model->getCommTable($v['type']);

                if(!empty($table_data)){

                    $table = $table_data['table'];

                    $filed = $table_data['filed'];

                    $title = $table_data['name'];

                    $v['top_name'] = Db::name($table)->where(["id"=>$v[$filed]])->value($title);
                }

                if(in_array($v['type'],[3,8])&&empty($v['top_id'])){

                    $order_id = !empty($v['add_pid'])?$v['add_pid']:$v['order_id'];

                    $v['top_name'] = $change_model->where(['order_id'=>$order_id,'is_new'=>1])->value('now_coach_name');
                }

                if($v['type']==2){

                    if($commission_custom==0){

                        $v['balance'] = '平台抽成-'.$v['balance'];
                    }

                    $coach_cash = $v['coach_cash']>0?'包含'.$v['coach_cash'].'线下服务费，':'';

                    $car_cash   = $v['car_cash']>0?'包含'.$v['car_cash'].'线下车费':'';

                    $v['cash'] = !empty($coach_cash)||!empty($car_cash)?$v['cash'].'('.$coach_cash.$car_cash.')':$v['cash'];
                }

                $v['coach_cash_control'] = $wallet_check_auth==true&&$v['status']==2&&$this->_user['admin_id'] == $v['admin_id']&&in_array($v['type'],[3,8])&&$v['top_id']==0?1:0;

                $share_cash = $poster_cash =$coach_balance_cash = $skill_cash= $share_car_cash = $coupon_cash = $balance_discount_cash= $balance_discount_cash= 0;

                $share_data = [];
                //技师查询是否有分摊金额
                if(in_array($v['type'],[3])){

                    $share_type = $v['type']==3?1:3;

                    $share_data = $this->comm_share_model->where(['order_id'=>$v['order_id'],'type'=>$share_type])->field('sum(share_cash) as share_cash,cash_type')->group('cash_type')->select()->toArray();
                }
                //代理商分摊金额
                if(in_array($v['type'],[2,5,6])){

                    $share_data = $this->comm_share_model->where(['order_id'=>$v['order_id'],'type'=>2,'share_id'=>$v['top_id']])->field('sum(share_cash) as share_cash,cash_type')->group('cash_type')->select()->toArray();
                }

                if(in_array($v['type'],[16])){

                    $share_car_cash = $this->comm_share_model->where(['order_id'=>$v['order_id'],'cash_type'=>5,'comm_id'=>$v['id']])->sum('share_cash');
                }

                if(!empty($share_data)){

                    foreach ($share_data as $value){

                        if($value['cash_type']==0){

                            $share_cash = $value['share_cash'];
                        }elseif ($value['cash_type']==2){

                            $poster_cash = $value['share_cash'];

                        }elseif ($value['cash_type']==3){

                            $coach_balance_cash = $value['share_cash'];

                        }elseif ($value['cash_type']==4){

                            $skill_cash = $value['share_cash'];

                        }elseif ($value['cash_type']==6){

                            $coupon_cash = $value['share_cash'];

                        }elseif ($value['cash_type']==7){

                            $balance_discount_cash = $value['share_cash'];
                        }
                    }
                }

                if($coach_balance_cash>0){

                    $v['cash'] .= '  储值扣款:'.$coach_balance_cash.'元';
                }

                if(!empty($share_cash)&&$share_cash>0){

                    $v['cash'] .= '  被分摊金额:'.$share_cash.'元';
                }

                if(!empty($poster_cash)&&$poster_cash>0){

                    $v['cash'] .= '  被分摊广告费:'.$poster_cash.'元';
                }

                if(!empty($skill_cash)&&$skill_cash>0){

                    $v['cash'] .= '  技术服务费:'.$skill_cash.'元';
                }

                if(!empty($coupon_cash)&&$coupon_cash>0){

                    $v['cash'] .= '  优惠券分摊:'.$coupon_cash.'元';
                }

                if(!empty($share_car_cash)&&$share_car_cash>0){

                    $v['cash'] .= '  车费分摊:'.$share_car_cash.'元';
                }
                if(!empty($balance_discount_cash)&&$balance_discount_cash>0){

                    $v['cash'] .= '  储值折扣分摊:'.$balance_discount_cash.'元';
                }
                //手续费
                if($v['point_cash']>0){

                    $v['cash'] .= '  手续费:'.$v['point_cash'].'元';
                }

                $v['type'] = $v['type']==13?8:$v['type'];
            }
        }

        if($this->_user['is_admin']==0){
            //可提现记录
            $data['total_cash'] = $admin_model->where(['id'=>$this->_user['admin_id']])->sum('cash');

            $dis = [

                'admin_id' => $this->_user['admin_id'],

                'status'   => 1,
            ];
            //未入账金额
            $data['unrecorded_cash'] = $comm_model->where($dis)->where('type','in',[2,5,6,13])->sum('cash');

            $dis['status'] = 2;

            $data['today_cash'] = $comm_model->where($dis)->where('type','in',[2,5,6,13])->whereDay('create_time','today')->sum('cash');

            $wallet_model = new \app\massage\model\Wallet();

            $dis = [

                ['user_id' ,'=', $this->_user['admin_id']],

                ['type'    ,'=',  3],
            ];

            $dis1 =[

                ['coach_id' ,'=', $this->_user['admin_id']],

                ['type'   ,'in',  [7,8,9]]
            ];
            //加盟商提现
            $data['wallet_cash'] = $wallet_model->where(function ($query) use ($dis,$dis1){
                $query->whereOr([$dis,$dis1]);
            })->where('status','in',[1,2,4,5])->sum('total_price');

            $data['total_cash'] = round($data['total_cash'],2);

            $data['unrecorded_cash'] = round($data['unrecorded_cash'],2);

            $data['wallet_cash'] = round($data['wallet_cash'],2);

            $data['today_cash'] = round($data['today_cash'],2);
        }

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-15 18:25
     * @功能说明:代理商修改线下技师佣金记录状态
     */
    public function adminUpdateCoachCommisson(){

        $input = $this->_input;

        $dis = [

            'id' => $input['id']
        ];

        $comm_model = new Commission();

        $data = $comm_model->dataInfo($dis);

        if($data['status']!=2){

            $this->errorMsg('佣金还未到账');
        }

        $res = $comm_model->dataUpdate($dis,['cash_status'=>1]);

        return $this->success($res);

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-24 13:33
     * @功能说明:代理商审核提现
     */
    public function applyWallet(){

        $input = $this->_input;

        $key = 'agent_wallets'.$this->_user['id'];

        incCache($key,1,$this->_uniacid,30);

        $value = getCache($key,$this->_uniacid);

        if($value!=1){
            //减掉
            decCache($key,1,$this->_uniacid);

            $this->errorMsg('网络错误，请刷新重试');
        }

        if(empty($input['apply_price'])||$input['apply_price']<0.01){

            decCache($key,1,$this->_uniacid);

            $this->errorMsg('提现费最低一分');
        }

        if($this->_user['is_admin']!=0){

            decCache($key,1,$this->_uniacid);

            $this->errorMsg('只有加盟商才能提现');
        }

        $admin_model = new \app\massage\model\Admin();

        $admin_user = $admin_model->dataInfo(['id'=>$this->_user['id']]);
        //服务费
        if($input['apply_price']>$admin_user['cash']){

            decCache($key,1,$this->_uniacid);

            $this->errorMsg('余额不足');
        }
        //获取税点
        $tax_point = getConfigSetting($this->_uniacid,'tax_point');

        $balance = 100-$tax_point;

        Db::startTrans();

        $admin_water_model = new AdminWater();

        $res = $admin_water_model->updateCash($this->_uniacid,$this->_user['id'],$input['apply_price'],2);

        if($res==0){

            Db::rollback();
            //减掉
            decCache($key,1,$this->_uniacid);

            $this->errorMsg('申请失败');
        }

        $insert = [

            'uniacid'       => $this->_uniacid,

            'user_id'       => $admin_user['user_id'],

            'admin_id'      => $admin_user['admin_pid'],

            'coach_id'      => $admin_user['id'],

            'total_price'   => $input['apply_price'],

            'balance'       => $balance,

            'apply_price'   => round($input['apply_price']*$balance/100,2),

            'service_price' => round( $input['apply_price'] * $tax_point / 100, 2),

            'tax_point'     => $tax_point,

            'code'          => orderCode(),

            'text'          => !empty($input['text'])?$input['text']:'',

            'type'          => 8,

            'apply_transfer'=> !empty($input['apply_transfer'])?$input['apply_transfer']:0,
        ];

        $wallet_model = new \app\massage\model\Wallet();
        //提交审核
        $res = $wallet_model->dataAdd($insert);

        if($res!=1){

            Db::rollback();
            //减掉
            decCache($key,1,$this->_uniacid);

            $this->errorMsg('申请失败');
        }

        Db::commit();
        //减掉
        decCache($key,1,$this->_uniacid);

        return $this->success($res);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-09-15 11:09
     * @功能说明:获取代理商支付宝账号
     */
    public function getAdminAliAccount(){

        $admin_model = new \app\massage\model\Admin();

        $user_id = $admin_model->where(['id'=>$this->_user['id']])->value('user_id');

        $user = $this->model->where(['id'=>$user_id])->field('alipay_number,alipay_name')->find();

        return $this->success($user);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-10-24 16:44
     * @功能说明:删除用户标签
     */
    public function delUserLabel(){

        $input = $this->_input;

        $label_model = new UserLabelData();

        $res = $label_model->dataUpdate(['user_id'=>$input['user_id'],'label_id'=>$input['label_id']],['status'=>-1]);

        return $this->success($res);

    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-24 10:24
     * @功能说明:用户列表
     */
    public function userSelectByPhone(){

        $input = $this->_param;

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['status', '=', 1];

        //如果是代理商 必须要传手机号
        if($this->_user['is_admin']==0&&empty($input['phone'])){

            $dis[] = ['id','=',-2];
        }
        $where = [];
        //手机号精准搜索
        if(!empty($input['phone'])&&$this->_user['is_admin']==0){

            $where[] = ['phone','=',$input['phone']];

            $where[] = ['nickName','=',$input['phone']];
        }

        if(!empty($input['phone'])&&$this->_user['is_admin']!=0){

            $where[] = ['phone','like','%'.$input['phone'].'%'];

            $where[] = ['nickName','like','%'.$input['phone'].'%'];
        }

        $data = $this->model->dataList($dis,$input['limit'],$where);

        return $this->success($data);

    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-12 17:23
     * @功能说明:获取发大大注册信息
     */
    public function getAttestationInfo(){

        $attestation_model = new FddAttestationRecord();

        if($this->_user['is_admin']!=0){

            $this->_user['id'] = 0;
        }
        //status 1注册 2实名认证 3绑定
        $data = $attestation_model->getAttestationInfo($this->_user['id'],$this->_uniacid,2);

        if(!empty($data['code'])){

            return $this->error($data['msg']);
        }

        return $this->success($data);
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-12 17:25
     * @功能说明:获取发大大实名认证地址
     */
    public function getCompanyVerifyUrl(){

        $attestation_model = new FddAttestationRecord();

        if($this->_user['is_admin']!=0){

            $this->_user['id'] = 0;
        }

        $data = $attestation_model->getCompanyVerifyUrl($this->_user['id'],$this->_uniacid);

        if(!empty($data['code'])){

            return $this->error($data['msg']);
        }

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-12 17:27
     * @功能说明:手动签署发大大合同
     */
    public function Extsign(){

        $input = $this->_input;

        $attestation_model = new FddAttestationRecord();

        if($this->_user['is_admin']!=0){

            $this->_user['id'] = 0;
        }

        $coach_model = new Coach();

        $coach_info = $coach_model->dataInfo(['id'=>$input['coach_id']]);
        //绑定实名
        $res = $attestation_model->ApplyCert($this->_user['id'],$this->_uniacid,2);

        if(!empty($res['code'])){

            return $this->error($res['msg']);
        }
        //上传合同
        $res = $attestation_model->companyUploaddocs($coach_info,$this->_uniacid,$this->_user['id']);

        if(!empty($res['code'])){

            return $this->error($res['msg']);
        }
        //获取签署地址
        $res = $attestation_model->ExtsignAuto($coach_info['user_id'],$this->_uniacid,$this->_user['id']);

        if(!empty($res['code'])){

            return $this->error($res['msg']);
        }

        return $this->success($res);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-06-27 14:29
     * @功能说明:合同列表
     * status -1删除 1商家待签署 2用户待签署 3待归档 4履约中 0到期
     */
    public function agreementList(){

        $input = $this->_param;

        $agreement_model = new FddAgreementRecord();
        //过期合同
        $agreement_model->where('status','=',4)->where('end_time','<',time())->update(['status'=>0]);

        $user_id = $this->_user['is_admin']==0?$this->_user['admin_id']:0;

        $dis[] = ['a.status','>',-1];

        $dis[] = ['a.admin_id','=',$user_id];

        $data = $agreement_model->alias('a')
                ->join('massage_service_coach_list b','a.coach_id = b.id','left')
                ->where($dis)
                ->field('a.*,b.coach_name,b.work_img')
                ->group('a.id')
                ->order('a.id')
                ->paginate($input['limit'])
                ->toArray();

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-06-27 17:53
     * @功能说明:没有签约的技师
     */
    public function noAgreementCoach(){

        $input = $this->_param;

        $user_id = $this->_user['is_admin']==0?$this->_user['admin_id']:0;

        $agreement_model = new FddAgreementRecord();

        $dis = [

            'admin_id' => $user_id,

        ];

        $coach_id = $agreement_model->where($dis)->where('status','>',0)->column('coach_id');

        $coach_model = new Coach();

        $dis = [];

        $dis[] = ['admin_id','=',$user_id];

        $dis[] = ['status','=',2];

        if(!empty($input['coach_name'])){

            $dis[] = ['coach_name','like','%'.$input['coach_name'].'%'];
        }

        $list = $coach_model->where($dis)->where('id','not in',$coach_id)->field('id,coach_name,work_img,auth_status')->order('id desc')->paginate($input['limit'])->toArray();

        return $this->success($list);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-06-27 18:18
     * @功能说明:删除合同
     */
    public function delAgreement(){

        $input = $this->_input;

        $agreement_model = new FddAgreementRecord();

        $dis = [

            'id' => $input['id']
        ];

        $res = $agreement_model->dataUpdate($dis,['status'=>-1]);

        return $this->success($res);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-10 16:07
     * @功能说明:修改用户的成长值
     */
    public function updateUserGrowth(){

        $input = $this->_input;

        $level_model = new Level();

        $res = $level_model->updateUserGrowth($input['id'],$this->_uniacid,$input['is_add'],$input['growth'],2,0,$this->_user['id']);

        return $this->success($res);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-10 16:51
     * @功能说明:成长值明细
     */
    public function userGrowthList(){

        $input = $this->_param;

        $growth_model = new Growth();

        $admin_model = new \app\massage\model\Admin();

        $user_model = new User();

        $dis[] = ['user_id','=',$input['id']];

        $data = $growth_model->dataList($dis,$input['limit']);

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                if(in_array($v['type'],[2])){

                    $v['create_user'] = $admin_model->where(['id'=>$v['create_user']])->value('agent_name');

                }else{

                   // $v['create_user'] = $admin_model->where(['is_admin'=>1])->value('agent_name');
                    $v['create_user'] = '系统';

                }
            }
        }

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-10-17 10:53
     * @功能说明:平台来黑用户|解除拉黑用户
     */
    public function BlockUser(){

        $input = $this->_input;

        $res = $this->model->dataUpdate(['id'=>$input['user_id']],['user_status'=>$input['user_status']]);

        return $this->success($res);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-10-17 11:22
     * @功能说明:技师评价用户内容
     */
    public function coachCommentUserData(){

        $input = $this->_param;

        $label_model = new UserLabelData();

        $comment_model = new UserComment();

        $coach_model  = new Coach();
        //用户标签
        $data['user_label'] = $label_model->getUserLabel($input['user_id']);
        //评价内容
        $list = $comment_model->dataList(['user_id'=>$input['user_id'],'status'=>1],$input['limit']);

        if(!empty($list['data'])){

            foreach ($list['data'] as &$v){

                $v['coach_name'] = $coach_model->where(['id'=>$v['coach_id']])->value('coach_name');

                $v['work_img']   = $coach_model->where(['id'=>$v['coach_id']])->value('work_img');

            }
        }

        $data['list'] = $list;

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-10-20 18:11
     * @功能说明:代理商财务列表
     */
    public function adminFinanceList(){

        $input = $this->_param;

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['is_admin','=',0];

        if(!empty($input['city_type'])){

            $dis[] = ['city_type','=',$input['city_type']];
        }

        if(!empty($input['admin_name'])){

            $dis[] = ['agent_name','like','%'.$input['admin_name'].'%'];
        }

        $admin_model = new \app\massage\model\Admin();

        $list = $admin_model->where($dis)->order('status desc,id desc')->paginate($input['limit'])->toArray();

        $list['total_cash'] = $admin_model->where($dis)->sum('cash');

        $list['total_cash'] = round($list['total_cash'],2);

        if(!empty($list['data'])){

            $comm_model = new Commission();

            $wallet_model = new \app\massage\model\Wallet();

            foreach ($list['data'] as &$v){

                $dis = [

                    ['user_id' ,'=', $v['id']],

                    ['type'    ,'=',  3],
                ];

                $dis1 =[
                    ['coach_id' ,'=', $v['id']],

                    ['type'   ,'=',  8]
                ];
                //加盟商提现
                $admin_cash = $wallet_model->where(function ($query) use ($dis,$dis1){
                    $query->whereOr([$dis,$dis1]);
                })->where('status','in',[1,2,4,5])->sum('total_price');
                //车费到代理商
                $car_cash  = $wallet_model->where(['coach_id'=>$v['id']])->where('type','in',[7,9])->where('status','in',[1,2,4,5])->sum('total_price');
                //申请多少元
                $v['wallet_cash']  = round($admin_cash+$car_cash,2);

                $v['total_cash'] = $comm_model->where(['top_id'=>$v['id']])->where('status','>',-1)->where('type','in',[2,5,6,13,19,20])->sum('cash');

                $v['total_cash'] = round($v['total_cash'],2);

                $v['not_recorded_cash'] = $comm_model->where(['top_id'=>$v['id']])->where('status','=',1)->where('type','in',[2,5,6,13,19,20])->sum('cash');

                $v['not_recorded_cash'] = round($v['not_recorded_cash'],2);
            }
        }

        return $this->success($list);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-01-17 18:20
     * @功能说明:用户详情IE GMYY
     */
    public function userInfo(){

        $input = $this->_param;

        $dis = [

            'id' => $input['user_id']
        ];

        $data = $this->model->dataInfo($dis);

        $order_model = new Order();

        $refund_model= new RefundOrder();

        $level_model = new Level();
        //消费总金额
        $data['total_use_cash'] = $order_model->where(['user_id'=>$input['user_id']])->where('pay_time','>',0)->sum('pay_price');
        //本月消费金额
        $data['month_use_cash'] = $order_model->where(['user_id'=>$input['user_id']])->whereTime('create_time','month')->where('pay_time','>',0)->sum('pay_price');
        //总退款
        $data['total_refund_cash'] = $refund_model->where(['user_id'=>$input['user_id'],'status'=>2])->sum('refund_price');

        $data['total_use_cash'] = round($data['total_use_cash'],2);
        $data['month_use_cash'] = round($data['month_use_cash'],2);
        $data['total_refund_cash'] = round($data['total_refund_cash'],2);
        //技师拒单退款
      //  $coach_refund_cash = $refund_model->where(['user_id'=>$input['user_id'],'type'=>2])->sum('refund_price');
        //技师本月拒单
     //   $coach_month_refund_cash = $refund_model->where(['user_id'=>$input['user_id'],'type'=>2])->whereTime('create_time','month')->sum('refund_price');

       // $data['total_refund_cash']+= $coach_refund_cash;
        //本月退款金额
        $data['month_refund_cash'] = $refund_model->alias('a')
                                   ->join('massage_service_order_list b','a.order_id = b.id')
                                   ->where(['a.user_id'=>$input['user_id'],'a.status'=>2])
                                   ->whereTime('b.create_time','month')
                                   ->group('a.id')
                                   ->sum('refund_price');

      //  $data['month_refund_cash'] += $coach_month_refund_cash;
        //总下单笔数
        $data['total_order_count'] = $order_model->where(['user_id'=>$input['user_id']])->where('pay_time','>',0)->count();
        //会员等级
        $data['member_level'] = $level_model->getUserLevel($data['id']);

        $scan_model  = new ChannelScanQr();

        if($data['source_type']!=0){
            //扫的渠道码名称
           // $data['channel_qr'] = $scan_model->getChannelQrTitle($data['id']);

            $data['channel_qr'] = $scan_model->getQrTitle($data['id']);
        }

        if($data['member_discount_time']>time()){

            $member_card_model = new Card();

            $data['member_discount_title'] = $member_card_model->where(['id'=>$data['member_discount_id']])->value('title');
        }

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-01-18 15:41
     * @功能说明:用户消费记录
     */
    public function userOrderList(){

        $input = $this->_param;

        $order_model = new Order();

        $dis[] = ['user_id','=',$input['user_id']];

        $dis[] = ['pay_time','>',0];

        $data = $order_model->adminDataListV2($dis,$input['limit'],[],$this->_user['phone_encryption']);

        return $this->success($data);
    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-08 11:51
     * @功能说明:用户优惠券列表
     */
    public function userCouponList(){

        $input = $this->_param;

        $coupon_record_model = new CouponRecord();

        $coupon_model = new Coupon();

        $coupon_record_model->initCoupon($this->_uniacid);

        $dis = [

            'user_id' => $input['user_id'],

            'is_show' => 1
        ];

        $data = $coupon_record_model->dataList($dis,$input['limit']);

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $v['send_type'] = $coupon_model->where(['id'=>$v['coupon_id']])->value('send_type');

                $v['start_time'] = date('Y.m.d H:i',$v['start_time']).' - '.date('Y.m.d H:i',$v['end_time']);
            }
        }

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-01-30 16:03
     * @功能说明:解除技师屏蔽
     */
    public function shieldCoachList(){

        $input = $this->_param;

        $dis = [

            'a.user_id' => $input['user_id'],

            'a.type'    => 2
        ];

        $shield_model = new ShieldList();

        $res = $shield_model->dataList($dis,$input['limit']);

        return $this->success($res);
    }




    /**
     * @author jingshuixian
     * @DataTime: 2020-06-08 9:33
     * @功能说明: 获得升级信息
     */
    public function getUpgradeInfo(){

        $goods_name   = config('app.AdminModelList')['app_model_name'];

        $auth_uniacid =  config('app.AdminModelList')['auth_uniacid'];

        $version_no   =  config('app.AdminModelList')['version_no'];

        $upgrade      = new LongbingUpgrade($auth_uniacid , $goods_name ,false);

        $data = $upgrade->checkAuth();

        if(!empty($data['code'])&&$data['code']==20000){

            $create_time = strtotime($data['data']['version']['create_time']);

            if($version_no!=$data['data']['version']['no']&&$create_time+86400*5>time()&&$this->_user['is_admin']==1){

                $data['is_upgrade'] = true;
            }else{

                $data['is_upgrade'] = false;
            }

            $data['location_version_no'] =  $version_no ;
        }else{

            $data['is_upgrade'] = false;
        }
        return $this->success( $data );
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-05-24 14:50
     * @功能说明:修改客户地址
     */
    public function updateUserAddress(){

        $input = $this->_input;

        $update = [

            'province' => $input['province'],

            'city'     => $input['city'],

            'area'     => $input['area']
        ];

        $res = $this->model->dataUpdate(['id'=>$input['id']],$update);

        return $this->success($res);
    }




    /**
     * @author chenniang
     * @DataTime: 2024-09-06 17:06
     * @功能说明:套餐购买列表
     */
    public function balanceDiscountCardList(){

        $input = $this->_param;

        $order_model = new OrderList();

        $dis[] = ['a.user_id','=',$input['user_id']];

        $dis[] = ['a.pay_type','=',2];

        $data = $order_model->alias('a')
            ->join('massage_balance_discount_user_card b','a.id = b.card_order_id','left')
            ->where($dis)
            ->field('a.*,b.cash,b.over_time')
            ->group('a.id')
            ->order('b.over_time desc,b.cash desc,a.id desc')
            ->paginate($input['limit'])
            ->toArray();

        $user_card_model = new UserCard();

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $v['is_over'] = $v['over_time']>time()?0:1;

                $v['cash']    = $user_card_model->where(['card_order_id'=>$v['id']])->value('cash');
            }
        }

        return $this->success($data);
    }








}
