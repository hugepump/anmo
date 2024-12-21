<?php
namespace app\massage\model;

use app\adminuser\model\AdminUser;
use app\BaseModel;
use app\coachbroker\model\CoachBroker;
use app\massage\server\RoleAuth;
use app\member\model\Level;
use app\mobilenode\model\RoleAdmin;
use think\facade\Db;

class User extends BaseModel
{
    //定义表名
    protected $name = 'massage_service_user_list';







    public function getPhoneAttr($value,$data){

        if(!empty($value)&&isset($data['uniacid'])){

            if(numberEncryption($data['uniacid'])==1){

                return  substr_replace($value, "****", 2,4);
            }
        }

        return $value;
    }
    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-08-29 21:18
     * @功能说明:
     */
    public function getBalanceAttr($value,$data){

        if(isset($value)){

            return round($value,2);
        }

    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-08-29 21:18
     * @功能说明:
     */
    public function getCashAttr($value,$data){

        if(isset($value)){

            return round($value,2);
        }

    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:04
     * @功能说明:添加
     */
    public function dataAdd($data){

        $data['create_time'] = time();

        $data['status']      = 1;

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
    public function dataList($dis,$page,$mapor=[],$field='*',$top='id desc',$map1=[],$map2=[]){


        if(!empty($map1)){

            $data = $this->where($dis)->where(function ($query) use ($mapor){
                $query->whereOr($mapor);
            })->where(function ($query) use ($map1,$map2){
                $query->whereOr([$map1,$map2]);
            })->field($field)->order($top)->paginate($page)->toArray();
        }else{

            $data = $this->where($dis)->where(function ($query) use ($mapor){
                $query->whereOr($mapor);
            })->field($field)->order($top)->paginate($page)->toArray();
        }

        return $data;
    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:06
     * @功能说明:列表
     */
    public function agentDataList($dis,$page,$mapor=[],$field='*',$top='id desc'){

        $data = $this->where($dis)->where(function ($query) use ($mapor){
            $query->whereOr($mapor);
        })->field($field)->order($top)->paginate($page)->toArray();

        return $data;
    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:43
     * @功能说明:
     */
    public function dataInfo($dis,$field='*'){

        $data = $this->where($dis)->field($field)->find();

        return !empty($data)?$data->toArray():[];

    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-10-27 15:42
     * @功能说明:订单自提码
     */
    public function orderQr($input,$uniacid){

        $data = longbingCreateWxCode($uniacid,$input,$input['page']);

        $data = transImagesOne($data ,['qr_path'] ,$uniacid);

        $qr   = $data['qr_path'];

        return $qr;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-06 16:40
     * @功能说明:获取各类角色的审核结果
     */
    public function authCheckData($data){

        $auth_server = new RoleAuth();
        //技师
        $coach_model = new Coach();
        //业务员
        $salesman_model = new Salesman();
        //分销员
        $distri_model = new DistributionList();
        //渠道商
        $channel_model = new ChannelList();
        //代理商
        $admin_model = new Admin();
        //手机操作权限
        $role_model  = new RoleAdmin();
        //是否是会员
        $member_level = new Level();
        //技师经济人
        $broker_model = new CoachBroker();

        $admin_user = new AdminUser();

        $auth_server->addObserver($coach_model);

        $auth_server->addObserver($salesman_model);

        $auth_server->addObserver($distri_model);

        $auth_server->addObserver($channel_model);

        $auth_server->addObserver($admin_model);

        $auth_server->addObserver($role_model);

        $auth_server->addObserver($member_level);

        $auth_server->addObserver($broker_model);

        $auth_server->addObserver($admin_user);

        $data = $auth_server->notify($data);

        return $data;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-19 10:04
     * @功能说明:校验技师金额
     */
    public function checkCoachCash($coach){

        $order_model = new Order();

        $wallet_model= new Wallet();

        $comm_model  = new Commission();

        $coach_model = new Coach();

        $service = $order_model->where(['pay_type'=>7,'coach_id'=>$coach['id']])->sum('coach_cash');

        $balance_cash = $comm_model->where(['top_id'=>$coach['id'],'status'=>2])->where('type','in',[7,17,18,24,25])->sum('cash');

        $wallet_price = $wallet_model->where(['coach_id'=>$coach['id'],'type'=>1])->where('status','in',[1,2])->sum('total_price');

        $add_cash = Db::name('massage_coach_cash_update_record')->where(['coach_id'=>$coach['id'],'status'=>1,'is_add'=>1])->sum('cash');
        $del_cash = Db::name('massage_coach_cash_update_record')->where(['coach_id'=>$coach['id'],'status'=>1,'is_add'=>0])->sum('cash');

        $cash = round($service+$balance_cash-$coach['service_price']-$wallet_price+$add_cash-$del_cash,2);



        $coach_model->dataUpdate(['id'=>$coach['id']],['check_cash'=>$cash]);

        return true;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-19 10:32
     * @功能说明:校验代理商佣金
     */
    public function checkAdminCash($admin){

        $comm_model = new Commission();

        $admin_model= new Admin();

        $wallet_model= new Wallet();

        $admin_cash = $comm_model->where(['top_id'=>$admin['id'],'status'=>2])->where('type','in',[2,5,6])->sum('cash');

        $wallet_price = $wallet_model->where(['admin_id'=>$admin['id'],'type'=>3])->where('status','in',[1,2])->sum('total_price');

        $cash = round($admin_cash-$admin['cash']-$wallet_price,2);

        $admin_model->dataUpdate(['id'=>$admin['id']],['check_cash'=>$cash]);

        return true;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-19 18:20
     * @功能说明:获取合伙人id
     */
    public function getPartnerId($name,$all_config){

        $where[] = ['id','=',$name];

        $where[] = ['nickName','like','%'.$name.'%'];

        $id = $this->whereOr($where)->column('id');

        if($all_config==1){

            $distr_model = new DistributionList();

            $id = $distr_model->where(['status'=>2])->where('user_id','in',$id)->column('user_id');
        }

        return $id;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-06-19 10:59
     * @功能说明:消费过一次的客户
     */
    public function orderOneUser($uniacid){

        $where[] = ['a.uniacid','=',$uniacid];

        $where[] = ['a.status','=',1];

        $where[] = ['b.pay_type','>',1];

        $data = $this->alias('a')
                ->join('massage_service_order_list b','a.id = b.user_id')
                ->where($where)
                ->field('COUNT(distinct(b.id)) as order_count,a.id')
                ->having("order_count = 1")
                ->group('a.id')
                ->count();

        return $data;
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-06-19 10:59
     * @功能说明:消费过两次及以上的客户
     */
    public function orderTwoUser($uniacid){

        $where[] = ['a.uniacid','=',$uniacid];

        $where[] = ['a.status','=',1];

        $where[] = ['b.pay_type','>',1];

        $data = $this->alias('a')
            ->join('massage_service_order_list b','a.id = b.user_id')
            ->where($where)
            ->field('COUNT(distinct(b.id)) as order_count,a.id')
            ->having("order_count > 1")
            ->group('a.id')
            ->count();

        return $data;
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-06-19 10:59
     * @功能说明:流失客户
     */
    public function lossUser($uniacid){

        $time = strtotime('-5 month', time());

        $time = strtotime(date('Y-m-d',$time));

        $where[] = ['a.uniacid','=',$uniacid];

        $where[] = ['a.status','=',1];

        $where[] = ['b.pay_type','>',1];

        $data = $this->alias('a')
            ->join('massage_service_order_list b','a.id = b.user_id')
            ->where($where)
            ->field('COUNT(distinct(b.id)) as order_count,max(b.create_time) as create_time,a.id')
            ->having("order_count >= 1")
            ->having("create_time < $time")
            ->group('a.id')
            ->count();

        return $data;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-06-19 11:16
     * @功能说明:没有消费过的用户
     */
    public function orderNoUser($uniacid){

        $count = $this->where(['uniacid'=>$uniacid,'status'=>1])->count();

        $model = new Order();

        $have = $model->where(['uniacid'=>$uniacid])->where('pay_type','>',1)->group('user_id')->count();

        return $count-$have;

        $where[] = ['a.uniacid','=',$uniacid];

        $where[] = ['a.status','=',1];

        $data = $this->alias('a')
            ->join('massage_service_order_list b','a.id = b.user_id AND b.pay_type > 1','left')
            ->where($where)
            ->field('a.id')
            ->whereNull('b.id')
            ->group('a.id')
            ->count();

        return $data;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-17 14:34
     * @功能说明:用户消费金额
     */
    public function userUseCash($user_id){

        $order_model = new Order();

        $refund_model= new RefundOrder();

        $dis[] = ['user_id','=',$user_id];
        //已经支付了的
        $dis[] = ['pay_time','>',0];
        //所有支付的钱
        $pay_price = $order_model->where($dis)->sum('pay_price');

      //  $refund_price = $refund_model->where(['user_id'=>$user_id,'status'=>2])->sum('refund_price');

        return round($pay_price,2);
    }


    /**
     * @param $order
     * @功能说明:获取用户的上级
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-11-14 17:43
     */
    public function getUserPid($order){

        $user_model     = new User();

        $reseller_model = new DistributionList();

        $admin_model    = new Admin();

        if(getPromotionRoleAuth(1,$order['uniacid'])==0){

            return $order;
        }
        //有没有授权付费分销插件
        $auth = $reseller_model->getPayResellerAuth($order['uniacid']);

        $dis = [

            'id' => $order['user_id'],
        ];
        //上级
        $top_id = $user_model->where($dis)->value('pid');

        $top = $user_model->dataInfo(['id'=>$top_id,'status'=>1]);

        if(empty($top)){

            return $order;
        }

        $reseller = $reseller_model->dataInfo(['user_id'=>$top['id'],'status'=>2]);

        if(getFxStatus($order['uniacid'])==1&&empty($reseller)){

            return $order;
        }
        //需要和代理商同范围
        if(!empty($reseller['admin_id'])&&getConfigSetting($order['uniacid'],'distribution_range_type')==1){

            $admin = $admin_model->where(['id'=>$reseller['admin_id'],'status'=>1])->field('city_type,city_id')->find();

            if(!empty($admin)){

                $address = !empty($order['address_info']['area'])?$order['address_info']:getCityByLat($order['address_info']['lng'],$order['address_info']['lat'],$order['uniacid']);

                if($admin['city_type']==1){

                    $city_filed = 'city';

                }elseif ($admin['city_type']==2){

                    $city_filed = 'area';
                }else{

                    $city_filed = 'province';
                }

                $city = $address[$city_filed];

                if(!empty($city)){

                    $city_model = new City();

                    $city_count = $city_model->where(['id'=>$admin['city_id'],'status'=>1,'title'=>$city])->count();

                    if($city_count==0){

                        return $order;
                    }
                }
            }
        }

        $order['user_top_id'] = $top_id;

        $order['user_reseller_id'] = !empty($reseller)?$reseller['id']:0;

        $order['user_reseller_balance'] = !empty($reseller)?$reseller['balance']:-1;

        $reseller_config = getConfigSettingArr($order['uniacid'],['reseller_coach_balance','reseller_admin_balance']);

        $order = array_merge($order,$reseller_config);

        $order['reseller_company_balance'] = 100-$order['reseller_coach_balance']-$order['reseller_admin_balance'];
        //这里二级也用一级的配置
        $order['level_reseller_coach_balance'] = $order['reseller_coach_balance'];

        $order['level_reseller_admin_balance'] = $order['reseller_admin_balance'];

        $order['level_reseller_company_balance'] = $order['reseller_company_balance'];
        //审核过的分销商才会有二级
        if(!empty($reseller['pid'])){

            $level_reseller = $reseller_model->dataInfo(['id'=>$reseller['pid'],'status'=>2]);

            if($auth==true){

                if(!empty($level_reseller)&&$level_reseller['reseller_level']!=1){

                    return $order;
                }
                //如果下级是一级 也没有佣金
                if(!empty($reseller)&&$reseller['reseller_level']==1){

                    return $order;
                }
            }

            if(!empty($level_reseller)){

                $order['level_top_id']      = $level_reseller['user_id'];

                $order['level_reseller_id'] = $level_reseller['id'];

                $order['level_reseller_balance'] = $level_reseller['level_balance'];
            }
        }

        return $order;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-03-06 16:52
     * @功能说明:获取注册时候的地址
     */
    public function getRegisterLocation($input,$insert,$uniacid){

        if(!empty($input['lng'])&&!empty($input['lat'])){

            $location = getCityByLat($input['lng'],$input['lat'],$uniacid);

            $insert['province'] = $location['province'];

            $insert['city']     = $location['city'];

            $insert['area']     = $location['area'];
        }else{

            $data = getAddressByIp($uniacid);

            $data = @json_decode($data,true);

            $insert['province'] = !empty($data['result']['ad_info']['province'])?$data['result']['ad_info']['province']:'';

            $insert['city']     = !empty($data['result']['ad_info']['city'])?$data['result']['ad_info']['city']:'';

            $insert['area']     = !empty($data['result']['ad_info']['district'])?$data['result']['ad_info']['district']:'';
        }

        return $insert;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-05-15 10:42
     * @功能说明:
     */
    public function userSelect($dis,$field='*'){

        $data = $this->where($dis)->field($field)->order('id desc')->select()->toArray();

        return $data;
    }


    /**
     * @param $user
     * @功能说明:获取被删除的用户(最老用户)
     * @author chenniang
     * @DataTime: 2024-11-26 11:12
     */
    public static function getDelUser($user){

        if(empty($user)){

            return false;
        }

        $dis[] = ['status','=',-1];

        if(!empty($user['web_openid'])){

            $where['web_openid'] = $user['web_openid'];
        }

        if(!empty($user['web_openid'])){

            $where['web_openid'] = $user['web_openid'];
        }

        if(!empty($user['wechat_openid'])){

            $where['wechat_openid'] = $user['wechat_openid'];
        }

        if(!empty($user['app_openid'])){

            $where['app_openid'] = $user['app_openid'];
        }

        if(!empty($user['unionid'])){

            $where['unionid'] = $user['unionid'];
        }

        $data = self::where($dis)->where(function ($query) use ($where){
            $query->whereOr($where);
        })->order('id')->find();

        return $data;
    }




}