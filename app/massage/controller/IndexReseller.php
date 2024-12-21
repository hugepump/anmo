<?php
namespace app\massage\controller;
use app\AdminRest;
use app\ApiRest;
use app\massage\model\City;
use app\massage\model\Coach;
use app\massage\model\CoachLevel;
use app\massage\model\CommentGoods;
use app\massage\model\Commission;
use app\massage\model\CommissionGoods;
use app\massage\model\CommShare;
use app\massage\model\Config;
use app\massage\model\DistributionList;
use app\massage\model\Order;
use app\massage\model\ResellerRecommendCash;
use app\massage\model\User;
use app\massage\model\Wallet;
use longbingcore\wxcore\PayModel;
use longbingcore\wxcore\YsCloudApi;
use think\App;
use think\facade\Db;


class IndexReseller extends ApiRest
{


    protected $model;

    protected $user_model;

    protected $cash_model;

    protected $wallet_model;

    protected $coach_model;

    protected $order_model;


    public function __construct(App $app) {

        parent::__construct($app);

        $this->model        = new DistributionList();

        $this->user_model   = new User();

        $this->cash_model   = new Commission();

        $this->wallet_model = new Wallet();

        $this->coach_model  = new Coach();

        $this->order_model  = new Order();

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-23 13:49
     * @功能说明:合伙人中心
     */
    public function partnerIndex(){

        $order_model = new Order();

        $admin_model = new \app\massage\model\Admin();
        //超时自动取消订单
        $order_model->coachBalanceArr($this->_uniacid);

        $data = $this->user_model->dataInfo(['id'=>$this->_user['id']],'nickName,avatarUrl,new_cash,cash');

        $data['cash'] = floatval($data['cash']);

        $data['new_cash'] = floatval($data['new_cash']);

        $cap_dis[] = ['user_id','=',$this->_user['id']];

        $cap_dis[] = ['status','in',[2,3]];

        $resller = $this->model->dataInfo($cap_dis);

        $data['nickName'] = empty($resller)?$data['nickName']:$resller['user_name'];

        $data['reseller_level'] = !empty($resller)?$resller['reseller_level']:0;

        $resller_id  = !empty($resller)?$resller['id']:-1;

        $config = getConfigSettingArr($this->_uniacid,['reseller_threshold','level_reseller_threshold']);

        if($data['reseller_level']==0){

            $data['reseller_up_price'] = $config['reseller_threshold'];

        }elseif ($data['reseller_level']==2){

            $resller_order_model = new \app\payreseller\model\Order();

            $pay_price = $resller_order_model->where(['reseller_id'=>$resller_id,'pay_type'=>2])->order('id desc')->value('pay_price');

            $pay_price = !empty($pay_price)?$pay_price:0;

            $data['reseller_up_price'] = $config['reseller_threshold']-$pay_price;
        }else{

            $data['reseller_up_price'] = 0;
        }

        $data['reseller_up_price'] =$data['reseller_up_price']>0?round($data['reseller_up_price'],2):0;

        if(!empty($resller['admin_id'])){

            $data['admin_name']= $admin_model->where(['id'=>$resller['admin_id']])->value('agent_name');
        }

        $data['id']  = $resller_id;

        $del_time = $this->model->where(['user_id'=>$this->_user['id'],'status'=>-1])->max('del_time');

        $fx_check = !empty($del_time)?1:0;

        $resller_data = $this->model->getResellerData($this->_user['id'],$fx_check,$del_time,$resller_id);

        $data = array_merge($data,$resller_data);
        //累计邀请技师
        $data['total_coach_count'] = $this->coach_model->where(['partner_id'=>$this->_user['id'],'status'=>2])->count();
        //今日邀请技师
        $data['today_coach_count'] = $this->coach_model->where(['partner_id'=>$this->_user['id'],'status'=>2])->whereTime('partner_time','today')->count();

        $data['reseller_inv_reseller_poster'] = getConfigSetting($this->_uniacid,'reseller_inv_reseller_poster');

        $data['inv_qr_type'] = getConfigSetting($this->_uniacid,'inv_qr_type');

        $data['user_id'] = $this->_user['id'];

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-23 14:30
     * @功能说明:合伙人邀请的技师
     */
    public function partnerCoachList(){

        $dis = [

            'status'     => 2,

            'partner_id' => $this->_user['id']
        ];

        $data = $this->coach_model->where($dis)->field('admin_id,id,coach_name,work_img,city_id')->order('partner_time desc,id desc')->paginate(10)->toArray();

        if(!empty($data['data'])){

            $config_model = new Config();

            $level_model  = new CoachLevel();

            $city_model   = new City();

            $admin_model = new \app\massage\model\Admin();

            $config = $config_model->dataInfo(['uniacid'=>$this->_uniacid]);

            $level_cycle = $config['level_cycle'];

            $is_current  = $config['is_current'];

            foreach ($data['data'] as &$v){

                $v['order_count'] = $level_model->getMinCount($v['id'],$level_cycle,0,1);

                $v['city'] = $city_model->where(['id'=>$v['city_id']])->value('city');

                $admin = $admin_model->dataInfo(['id'=>$v['admin_id'],'status'=>1]);
                //代理商
                $v['admin_name'] = !empty($admin)?$admin['agent_name']:'平台';
            }

        }
        return $this->success($data);
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-08-30 14:18
     * @功能说明 合伙人邀请技师码
     */
    public function resellerInvCoachQr(){

        $input = $this->_param;

        $admin_id = !empty($input['admin_id'])?$input['admin_id']:0;

        $key = 'resellerInvCoach_qr'.$this->_user['id'].'-'.$this->is_app.'-'.$admin_id;

        $qr  = getCache($key,$this->_uniacid);

        if(empty($qr)){
            //小程序
            if($this->is_app==0){

                $input['page'] = 'technician/pages/apply';

                $input['partner_id'] = $this->_user['id'];
                //获取二维码
                $qr = $this->user_model->orderQr($input,$this->_uniacid);

            }else{

                $page = 'https://'.$_SERVER['HTTP_HOST'].'/h5/#/technician/pages/apply?partner_id='.$this->_user['id'].'&admin_id='.$input['admin_id'];

                $qr = base64ToPng(getCode($this->_uniacid,$page));

            }

            setCache($key,$qr,86400,$this->_uniacid);
        }

        $qr = !empty($qr)?$qr:'https://'.$_SERVER['HTTP_HOST'].'/favicon.ico';

        return $this->success($qr);
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-08-30 14:18
     * @功能说明 分销邀请分销员码
     */
    public function resellerInvresellerQr(){

        $input = $this->_param;

        $reseller = $this->model->dataInfo(['user_id'=>$this->_user['id'],'status'=>2]);

        if(empty($reseller)){

            $this->errorMsg('你还不是分销员');
        }

        $key = 'resellerInvresellerQr'.$reseller['id'];

        $qr = getCache($key,$this->_uniacid);

        if(empty($qr)){
            //小程序
            if($this->is_app==0){

                $input['page'] = 'user/pages/distribution/apply';

                $input['level_reseller_id'] = $reseller['id'];
                //获取二维码
                $qr = $this->user_model->orderQr($input,$this->_uniacid);

            }else{

                $page = 'https://'.$_SERVER['HTTP_HOST'].'/h5/#/user/pages/distribution/apply?level_reseller_id='.$reseller['id'];

                $qr = base64ToPng(getCode($this->_uniacid,$page));

            }

            setCache($key,$qr,86400,$this->_uniacid);
        }

        $qr = !empty($qr)?$qr:'https://'.$_SERVER['HTTP_HOST'].'/favicon.ico';

        return $this->success($qr);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-09 10:43
     * @功能说明:
     */
    public function adminList(){

        $admin_model = new \app\massage\model\Admin();

        $dis = [

            'status'       => 1,

            'is_admin'     => 0,

            'partner_auth' => 1
        ];

        $input = $this->_param;

        $where = [];

        if(!empty($input['nickName'])){

            $where[] =['agent_name','like','%'.$input['nickName'].'%'];
        }

        $data = $admin_model->where($dis)->where($where)->field('agent_name,id,city_type,user_id')->paginate(10)->toArray();

        $user_model = new User();

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $v['avatarUrl'] = $user_model->where(['id'=>$v['user_id']])->value('avatarUrl');
            }
        }

        return $this->success($data);
    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-08-28 23:03
     * @功能说明:佣金记录
     */
    public function resellerCashList(){

        $input = $this->_param;

        $limit = !empty($input['limit'])?$input['limit']:10;

        $dis[] = ['a.uniacid','=',$this->_uniacid];

        $dis[] = ['b.cash','>',0];

        $dis[] = ['b.top_id','=',$this->getUserId()];

        $dis[] = ['b.type','in',[1,14]];

        if(!empty($input['status'])){

            $dis[] = ['b.status','=',$input['status']];
        }else{

            $dis[] = ['b.status','>',-1];
        }

        $del_time = $this->model->where(['user_id'=>$this->_user['id'],'status'=>-1])->max('del_time');

        if(!empty($del_time)){

            $dis[] = ['a.create_time','>',$del_time];
        }

        $order_model = new Order();

        $com_model   = new Commission();

        $user_model  = new User();

        $data = $order_model->alias('a')
            ->join('massage_service_order_commission b','a.id = b.order_id')
            ->where($dis)
            ->field('a.pay_model,a.balance,a.coupon_bear_type,a.label_time,a.material_type,a.have_tx,a.id,a.coach_id,a.store_id,a.is_comment,a.order_code,a.true_service_price,a.pay_type,a.pay_price,a.start_time,a.create_time,a.user_id,a.end_time,a.add_pid,a.is_add,a.init_material_price,a.material_price,b.type,b.user_id')
            ->group('a.id')
            ->order('a.id desc')
            ->paginate($limit)
            ->toArray();

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $v['create_time'] = date('Y-m-d H:i:s',$v['create_time']);

                $v['start_time']  = date('Y-m-d H:i',$v['start_time']);

                $v['end_time']    = date('Y-m-d H:i',$v['end_time']);
                //渠道商佣金
                $reseller_cash = $com_model->where(['order_id'=>$v['id'],'type'=>$v['type']])->where('status','>',-1)->find();

                if(!empty($reseller_cash)){

                    $v['reseller_cash'] = round($reseller_cash->cash,2);

                    $v['reseller_balance'] = $reseller_cash->balance;

                    $v['point_cash'] = round($reseller_cash->point_cash,2);

                }else{

                    $v['reseller_cash'] = 0;

                    $v['reseller_balance'] = 0;

                    $v['point_cash']  = 0;
                }

                $v['nickName'] = $user_model->where(['id'=>$v['user_id']])->value('nickName');

                $v['order_goods'] = $reseller_cash->order_goods;

                if($v['coupon_bear_type']==2&&$v['pay_model']==4){

                    foreach ( $v['order_goods']  as $key=>$value){

                        $v['order_goods'][$key]['true_price'] = $value['price'];

                        $v['order_goods'][$key]['material_price'] = $value['init_material_price'];
                    }
                }
            }
        }
        return $this->success($data);
    }





    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-12-26 11:24
     * @功能说明:下级分销员
     */
    public function subReseller(){

        $input = $this->_param;

        $cap_dis[] = ['user_id','=',$this->_user['id']];

        $cap_dis[] = ['status','in',[2,3]];

        $resller = $this->model->dataInfo($cap_dis);

        $resller_id = !empty($resller)?$resller['id']:-1;

        $dis[] = ['a.pid','=',$resller_id];

        $dis[] = ['a.status','=',2];

        if(!empty($input['name'])){

            $dis[] = ['a.user_name','like','%'.$input['name'].'%'];
        }

        $input['order'] = $input['order'] ?? 0;

        switch ($input['order']) {

            case 1:
                $order = 'total_cash desc,a.sh_time desc';
                break;
            case 2:
                $order = 'total_cash asc,a.sh_time desc';
                break;
            case 3:
                $order = 'a.sh_time asc';
                break;
            case 4:
            default:
                $order = 'a.sh_time desc';
        }

        $data = $this->model->dataIndexList($dis,10,$order);

        $user_model = new User();

        $comm_model = new Commission();

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $v['sh_time'] = !empty($v['sh_time'])?$v['sh_time']:$v['create_time'];

                $v['sh_time'] = date('Y-m-d H:i:s',$v['sh_time']);

                $v['avatarUrl'] = $user_model->where(['id'=>$v['user_id']])->value('avatarUrl');
                //累计邀请用户
                $del_time = $this->model->where(['user_id'=>$v['user_id'],'status'=>-1])->max('del_time');

                $fx_check = !empty($del_time)?1:0;
                //累计邀请用户
                $resller_data = $this->model->getResellerData($v['user_id'],$fx_check,$del_time,$v['id']);

                $v = array_merge($v,$resller_data);

                $v['cash'] = $comm_model->where(['reseller_id'=>$resller_id,'sub_reseller_id'=>$v['id'],'type'=>15,'status'=>2])->sum('cash');

            }
        }

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-01-08 15:58
     * @功能说明:分销员升级
     */
    public function resellerLevelUp(){

        $input = $this->_param;

        $order_model = new \app\payreseller\model\Order();

        $cap_dis[] = ['user_id','=',$this->_user['id']];

        $cap_dis[] = ['status','in',[2]];

        $reseller = $this->model->dataInfo($cap_dis);

        if(empty($reseller)){

            $this->errorMsg('你还不是分销员');
        }

        $config = getConfigSettingArr($this->_uniacid,['reseller_threshold','level_reseller_threshold','reseller_inv_balance','level_reseller_inv_balance','wx_point','ali_point']);

        if($reseller['reseller_level']==0){

            $pay_price = $config['reseller_threshold'];

        }elseif ($reseller['reseller_level']==2){

            $have_price = $order_model->where(['reseller_id'=>$reseller['id'],'pay_type'=>2])->order('id desc')->value('pay_price');

            $have_price = !empty($have_price)?$have_price:0;

            $pay_price = $config['reseller_threshold']-$have_price;
        }else{

            $pay_price = 0;
        }

        $order_insert = [

            'uniacid' => $this->_uniacid,

            'user_id' => $this->_user['id'],

            'reseller_id'=> $reseller['id'],

            'pay_price' => $pay_price,

            'order_code'=> orderCode(),

            'type'      => 1,

            'pay_model' => $input['pay_model'],

            'app_pay'   => $this->is_app,
        ];

        if(!empty($reseller['pid'])){

            $top = $this->model->where(['id'=>$reseller['pid']])->where('status','in',[2,3])->find();

            if(!empty($top)){

                $order_insert['top_reseller_id'] = $top['id'];

                $order_insert['top_user_id'] = $top['user_id'];
            }
        }

        $order_model->dataAdd($order_insert);

        $order_id = $order_model->getLastInsID();
        //如果有分享人 需要给佣金
        if(!empty($top)){

            $balance = $config['reseller_inv_balance'];

            $cash = round($order_insert['pay_price']*$balance/100,2);

            if ($input['pay_model']==3){

                $point = $config['ali_point'];

            }else{

                $point = $config['wx_point'];
            }

            $point_cash = round($cash*$point/100,2);

            $cash -= $point_cash;

            $comm_insert = [

                'uniacid' => $this->_uniacid,

                'user_id' => $this->_user['id'],

                'top_id'  => $order_insert['top_user_id'],

                'order_id'=> $order_id,

                'order_code' => $order_insert['order_code'],

                'cash' => $cash,

                'type' => 15,

                'balance' => $balance,

                'admin_id' => !empty($input['admin_id'])?$input['admin_id']:0,

                'sub_reseller_id' => $reseller['id'],

                'reseller_id' => $order_insert['top_reseller_id'],

                'status' => -1
            ];

            $comm_model = new Commission();

            $comm_model->dataAdd($comm_insert);

            $comm_id = $comm_model->getLastInsID();

            $share_data = [

                'pay_point' => $point,

                'inv_reseller_point_cash' => $point_cash,

                'id' => $order_id,

                'uniacid' => $this->_uniacid
            ];

            $share_model = new CommShare();
            //添加手续费
            $share_model->addPointData($comm_id,$share_data,$comm_insert['type'],$comm_insert['top_id']);
        }

        if($order_insert['pay_price']<=0){

            $order_model->orderResult($order_insert['order_code'],$order_insert['order_code']);

            return $this->success(true);
        }

        if ($input['pay_model']==3){

            $pay_model = new PayModel($this->payConfig());

            $jsApiParameters  = $pay_model->aliPay($order_insert['order_code'],$order_insert['pay_price'],'ResellerPay',4);

            $arr['pay_list']  = $jsApiParameters;

            $arr['order_code']= $order_insert['order_code'];

            $arr['order_id']  = $order_id;

        }else{
            //微信支付
            $pay_controller = new \app\shop\controller\IndexWxPay($this->app);
            //支付
            $jsApiParameters= $pay_controller->createWeixinPay($this->payConfig(),$this->getUserInfo()['openid'],$this->_uniacid,"reseller",['type' => 'ResellerPay' , 'out_trade_no' => $order_insert['order_code'],'order_id'=>(string)$order_id],$order_insert['pay_price']);

            $arr['pay_list']= $jsApiParameters;

            $arr['order_id']= $order_id;
        }

        return $this->success($arr);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-01-08 16:29
     * @功能说明:推荐门槛佣金记录
     */
    public function invCashList(){

        $cap_dis[] = ['user_id','=',$this->_user['id']];

        $cap_dis[] = ['status','in',[2,3]];

        $resller = $this->model->dataInfo($cap_dis);

        $resller_id = !empty($resller)?$resller['id']:-1;

        $dis[] = ['status','=',2];

        $dis[] = ['type','=',15];

        $dis[] = ['reseller_id','=',$resller_id];

        $comm_model  = new Commission();

        $share_model = new CommShare();

        $recommend_model = new ResellerRecommendCash();

        $order_model = new \app\payreseller\model\Order();

        $user_model = new User();

      //  $data = $comm_model->dataList($dis);

        $recommend_cash_sql = $recommend_model->where(['reseller_id'=>$resller_id])->field('id,id as order_id,id as order_code,id as balance,create_time,user_id,user_id as sub_reseller_id,recommend_cash as cash,if(id=-1,-1,3) as type')->order('id desc')->buildSql();

        $comm_sql = $comm_model->where($dis)->unionAll([$recommend_cash_sql])->field('id,order_id,order_code,balance,create_time,user_id,sub_reseller_id,cash,type')->order('id desc')->buildSql();

        $data = Db::table($comm_sql.' a')->paginate(10)->toArray();

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $v['create_time'] = date('Y-m-d H:i:s',$v['create_time']);

                if($v['type']==15){
                    //下级姓名
                    $v['user_name'] = $this->model->where(['id'=>$v['sub_reseller_id']])->value('user_name');

                    $v['point_cash']= $share_model->where(['comm_id'=>$v['id'],'comm_type'=>15])->sum('share_cash');

                    $v['order_price'] = $order_model->where(['id'=>$v['order_id']])->sum('pay_price');
                }else{

                    $v['user_name'] = $user_model->where(['id'=>$v['user_id']])->value('nickName');
                }
            }
        }

        return $this->success($data);
    }









}
