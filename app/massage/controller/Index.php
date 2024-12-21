<?php
namespace app\massage\controller;
use app\admin\controller\Arcv;
use app\ApiRest;

use app\dynamic\model\DynamicFollow;
use app\dynamic\model\DynamicThumbs;
use app\industrytype\model\Type;
use app\massage\model\Address;
use app\massage\model\AdminConfig;
use app\massage\model\ArticleList;
use app\massage\model\BtnConfig;
use app\massage\model\CateConnect;
use app\massage\model\CateList;
use app\massage\model\City;
use app\massage\model\Coach;
use app\massage\model\CoachCollect;
use app\massage\model\CoachIcon;
use app\massage\model\CoachTimeList;
use app\massage\model\Comment;
use app\massage\model\ConfigSetting;
use app\massage\model\Coupon;
use app\massage\model\CouponRecord;
use app\massage\model\CreditConfig;
use app\massage\model\CreditRecord;
use app\massage\model\Diy;
use app\massage\model\ExpectationCityInfo;
use app\massage\model\ExpectationCityList;
use app\massage\model\IconCoach;
use app\massage\model\MassageConfig;
use app\massage\model\Order;
use app\massage\model\PayConfig;
use app\massage\model\Service;
use app\massage\model\ServiceGuarantee;
use app\massage\model\ServiceGuaranteeConnect;
use app\massage\model\ServicePositionConnect;
use app\massage\model\ShieldList;
use app\massage\model\ShortCodeConfig;
use app\massage\model\StationIcon;
use app\massage\model\StoreCoach;
use app\massage\model\StoreList;
use app\massage\model\WatermarkList;
use app\Rest;
use app\massage\model\Banner;
use app\massage\model\Car;
use app\massage\model\Config;
use app\massage\model\User;
use longbingcore\heepay\WeixinPay;
use longbingcore\permissions\AdminMenu;
use think\App;
use think\facade\Db;

use think\Request;



class Index extends ApiRest
{

    protected $model;

    protected $article_model;

    protected $coach_model;

    protected $banner_model;

    protected $car_model;

    protected $admin_model;

    //@ioncube.dk myk("sha256", "random5676u71113r40011") -> "5277be6f3490a79791b53e40943429dec4313cb42ee7a29b9b6766ae3d886966" RANDOM
    public function __construct(App $app) {

        parent::__construct($app);

        $this->model = new Service();

        $this->banner_model = new Banner();

        $this->car_model = new Car();

        $this->coach_model = new Coach();

        $this->admin_model = new \app\massage\model\Admin();

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-23 09:20
     * @功能说明:首页
     */
    //@ioncube.dk myk("sha256", "random5676u71113r40011333") -> "e19f70685c9521668dc38b4c98d6a9be85eaccddcc2269289307bb8c0200a6ea" RANDOM
    public function index(){

       $input = $this->_param;

       $key = 'index_cach_index';

       $data= getCache($key,$this->_uniacid);

       if(empty($data)){

           $dis = [

               'uniacid' => $this->_uniacid,

               'status'  => 1
           ];
           $data['banner'] = $this->banner_model->where($dis)->field('id,img,link,type_id,connect_type')->order('top desc,id desc')->select()->toArray();

           $cate_model = new CateList();

           $data['service_cate'] = $cate_model->where($dis)->field('title,id,cover')->order('top desc,id desc')->select()->toArray();
           //判断插件权限没有返回空
           $auth = $this->getAuthList((int)$this->_uniacid,['recommend']);

           $config_model = new ConfigSetting();

           $config = $config_model->dataInfo($this->_uniacid,['recommend_style','coach_apply_show','auto_recommend','popup_img']);

           $data = array_merge($data,$config);

           $data['recommend_auth']= $auth['recommend'];

           $type_model = new Type();

           if($this->is_app==0&&getConfigSetting($this->_uniacid,'shield_massage')==1){

               $wheres[] = ['type','<>',1];
           }else{
               $wheres = [];
           }

           $data['industry_type'] = $type_model->where($dis)->where($wheres)->field('id,title,desc')->where(['index_show'=>1])->order('top desc,id desc')->select()->toArray();

           setCache($key,$data,5,$this->_uniacid);
       }
       //是否有优惠券要领取
       if(!empty($this->getUserId())){

           $data['is_pop_img'] = isset($this->_user['is_pop_img'])?$this->_user['is_pop_img']:1;

           $coupon_record_model = new CouponRecord();

           $have_get = $coupon_record_model->where(['user_id'=>$this->getUserId()])->column('coupon_id');

           $dis = [];

           $dis[] = ['uniacid','=',$this->_uniacid];

           $dis[] = ['send_type','=',2];

           $dis[] = ['status','=',1];

           $dis[] = ['stock','>',0];

           $dis[] = ['id','not in',$have_get];

           $time = strtotime(date('Y-m-d',time()));

           $del_user = \app\massage\model\User::getDelUser($this->_user);

           if(!empty($del_user)){

               $dis[] = ['user_limit','<>',2];
           }
           //不是新用户
           if(!empty($this->_user['del_user_id'])){

               $dis[] = ['user_limit','<>',2];
           }

           $map[] = ['time_limit','=',1];

           $map[] = ['end_time','>',time()];

           $coupon_count = Db::name('massage_service_coupon')->where($dis) ->where(function ($query) use ($map){
               $query->whereOr($map);
           })->count();
       }

       $data['have_coupon'] = !empty($coupon_count)?1:0;

       return $this->success($data);
   }


    /**
     * @author chenniang
     * @DataTime: 2024-06-20 17:21
     * @功能说明:
     */
   public function updateUserPopImg(){

       $input = $this->_input;

       $user_model = new User();

       $user_model->where(['id'=>$this->_user['id']])->update(['is_pop_img'=>$input['is_pop_img']]);

       $this->_user['is_pop_img'] = $input['is_pop_img'];

       $key = $this->autograph;

      // $key = md5($key);

       setCache($key, $this->_user, 86400*3,999999999999);

       return $this->success(true);

   }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-18 14:15
     * @功能说明:推荐技师列表
     */
   public function recommendList(){

       $input = $this->_param;

       $lat = !empty($input['lat'])?$input['lat']:0;

       $lng = !empty($input['lng'])?$input['lng']:0;

       $key = 'recommend_key-'.$lat.'-'.$lng;

      // if(empty($this->getUserId())){

           $list = getCache($key,$this->_uniacid);
     //  }

       if(empty($list)){

           $config_model = new ConfigSetting();

           $config = $config_model->dataInfo($this->_uniacid,['recommend_style','coach_apply_show','auto_recommend']);

           $where[] = ['uniacid','=',$this->_uniacid];

           $where[] = ['status','=',2];

           $where[] = ['auth_status','=',2];

           $where[] = ['is_work','=',1];

           if(!empty($input['city_id'])){

             //  $where[] = ['city_id','=',$input['city_id']];
           }

           if($this->is_app==0&&getConfigSetting($this->_uniacid,'shield_massage')==1){

               $where[] = ['industry_type','<>',1];
           }

           if(!empty($this->getUserId())){

               $shield_coach = $this->coach_model->getShieldCoach($this->getUserId());

               if(!empty($shield_coach)){

                   $where[] = ['id','not in',$shield_coach];
               }
           }

           $alh = 'ACOS(SIN(('.$lat.' * 3.1415) / 180 ) *SIN((lat * 3.1415) / 180 ) +COS(('.$lat.' * 3.1415) / 180 ) * COS((lat * 3.1415) / 180 ) *COS(('.$lng.' * 3.1415) / 180 - (lng * 3.1415) / 180 ) ) * 6378.137*1000 as distance';
           //手动推荐
           if($config['auto_recommend']==0){

               $list = $this->coach_model->coachRecommendSelect($where,$alh);
           }else{
               //自动推荐
               $list = $this->coach_model->aotuCoachRecommendSelect($where,$alh);
           }

           if(!empty($list)){
               //最近七天注册
               $seven = $this->model->getSaleTopSeven($this->_uniacid);

               foreach ($list as &$v){

                   $v['id'] = $v['coach_id'];
                   //是否是新人
                   $v['is_new'] = in_array($v['id'],$seven)?1:0;

                   $v['star'] = number_format($v['star'],1);
               }
           }

           if(!empty($input['diy_type'])&&$input['diy_type']==3){

               $list = $this->coach_model->getCoachListWorkStatus($list,$this->_uniacid);
           }

           setCache($key,$list,5,$this->_uniacid);
       }

       return $this->success($list);
   }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-15 14:43
     * @功能说明:服务列表
     */
    public function serviceList(){

        $input = $this->_param;

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['status','=',1];

        $dis[] = ['type','=',1];

        $dis[] = ['check_status','=',2];

        $dis[] = ['is_add','=',0];
        //判断插件权限没有返回空
        $auth = AdminMenu::getAuthList((int)$this->_uniacid,['store']);
        //加钟时候需要判断服务方式
        if(empty($auth['store'])){

            $dis[] = ['is_door','=',1];
        }

        if(!empty($input['cate_id'])){

            $cate_model = new CateConnect();

            $id = $cate_model->where(['cate_id'=>$input['cate_id']])->column('service_id');

            $dis[] = ['id','in',$id];
        }

        if(!empty($input['name'])){

            $dis[] = ['title','like','%'.$input['name'].'%'];
        }

        if(!empty($input['title'])){

            $dis[] = ['title','like','%'.$input['title'].'%'];
        }

        if(!empty($input['industry_id'])){

            $dis[] = ['industry_type','=',$input['industry_id']];
        }

        if($this->is_app==0&&getConfigSetting($this->_uniacid,'shield_massage')==1){

            $dis[] = ['industry_type','<>',1];
        }

        $page = !empty($input['page'])?$input['page']:1;

        $input['sort'] = !empty($input['sort'])?$input['sort']:'top desc';

        $key = serialize($dis).'serviceList'.$input['sort'].$page;

        $data= getCache($key,$this->_uniacid);

        if(empty($data)){

            $data = $this->model->indexDataList($dis,10,$input['sort']);

            setCache($key,$data,5,$this->_uniacid);
        }
        //获取服务的会员信息
        $data['data'] = $this->model->giveListMemberInfo($data['data'],$this->_uniacid,$this->getUserId());
        //会员价
        $data['data'] = giveMemberPrice($this->_uniacid,$data['data']);

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-07-14 14:59
     * @功能说明:根据技师返回服务
     */
    public function getCoachService(){

        $input = $this->_param;

        if(!empty($input['city_id'])){

            $city_id =$city= $input['city_id'];

        }else{

            $lat = $input['lat'];

            $lng = $input['lng'];

            $city= $this->getLatData($lng,$lat,$this->_uniacid);

            $city_model = new City();

            $mapor = [

                'city_type' => 1,

                'is_city'   => 1
            ];

            $city_id = $city_model->where(['uniacid'=>$this->_uniacid,'status'=>1])->where(function ($query) use ($mapor){
                $query->whereOr($mapor);
            })->where('title','in',$city)->order('id desc')->value('id');

        }

        $dis[] = ['a.uniacid','=',$this->_uniacid];

        $dis[] = ['a.status','=',1];

        $dis[] = ['c.status','=',2];

        $dis[] = ['c.auth_status','=',2];

        if(!empty($input['industry_id'])){

            $dis[] = ['c.industry_type','=',$input['industry_id']];
        }

        if($this->is_app==0&&getConfigSetting($this->_uniacid,'shield_massage')==1){

            $dis[] = ['c.industry_type','<>',1];
        }

        if(!empty($city)){

            $dis[] = ['c.city_id','=',$city_id];
        }

        if(!empty($input['title'])){

            $dis[] = ['a.title','like',"%".$input['title'].'%'];
        }

        $dis[] = ['a.check_status','=',2];

        if(!empty($input['coach_id'])){

            $dis[] = ['b.coach_id','=',$input['coach_id']];
        }

        if(!empty($input['cate_id'])){

            $cate_model = new CateConnect();

            $service_id = $cate_model->getCateService($input['cate_id']);

            $dis[] = ['a.id','in',$service_id];
        }

        $is_add = !empty($input['is_add'])?$input['is_add']:0;

        $dis[] = ['a.is_add','=',$is_add];
        //判断插件权限没有返回空
        $auth = AdminMenu::getAuthList((int)$this->_uniacid,['store']);
        //加钟时候需要判断服务方式
        if(empty($auth['store'])){

            $dis[] = ['a.is_door','=',1];
        }

        $key  = serialize($dis).$input['sort'].$input['page'];

        $data = getCache($key,$this->_uniacid);

        if(empty($data)){

            $data = $this->model->serviceCoachPageList($dis,$input['sort']);

            setCache($key,$data,5,$this->_uniacid);
        }
        //获取服务的会员信息
        $data['data'] = $this->model->giveListMemberInfo($data['data'],$this->_uniacid,$this->getUserId());
        //会员价
        $data['data'] = giveMemberPrice($this->_uniacid,$data['data']);

        return $this->success($data);
    }


    /**
     * @author chenniang
     * @DataTime: 2024-08-15 17:22
     * @功能说明:全行业首页服务列表|关闭用户实时定位
     */
    public function industryServiceList(){

        $dis = [

            'uniacid' => $this->_uniacid,

            'status'  => 1,
        ];

        $type_model = new Type();

        if($this->is_app==0&&getConfigSetting($this->_uniacid,'shield_massage')==1){

            $wheres[] = ['type','<>',1];
        }else{

            $wheres = [];
        }

        $data = $type_model->where($dis)->where($wheres)->order('top desc,id desc')->field('cate_name,id')->select()->toArray();
        //判断插件权限没有返回空
        $auth = AdminMenu::getAuthList((int)$this->_uniacid,['store']);

        $dis = [

            'uniacid' => $this->_uniacid,

            'status'  => 1,

            'type'    => 1,

            'check_status'=> 2,

            'is_add'=> 0,

            'index_show' => 1
        ];

        if(empty($auth['store'])){

            $dis['is_door'] = 1;
        }

        $arr = [];

        if(!empty($data)){

            foreach ($data as $k=>$v){

                $dis['industry_type'] = $v['id'];

                $list = $this->model->where($dis)->field('id,show_unit,cover,title,ROUND(price+material_price,2) as price')->order('top desc,id desc')->limit(6)->select()->toArray();

                if(!empty($list)){

                    $arr[$k]['cate_name'] = $v['cate_name'];

                    $arr[$k]['id'] = $v['id'];

                    $arr[$k]['list'] = $list;
                }
            }
        }

        $total['list'] = array_values($arr);

        return $this->success($total);
    }



    /**
     * @author chenniang
     * @DataTime: 2024-08-15 17:22
     * @功能说明:全行业首页服务列表|开启用户实时定位
     */
    public function industryGetCoachService(){

        $input = $this->_param;

        if(!empty($input['city_id'])){

            $city_id = $city=$input['city_id'];
        }else{

            $lat = $input['lat'];

            $lng = $input['lng'];

            $city = $this->getLatData($lng,$lat,$this->_uniacid);

            $city_model = new City();

            $mapor = [

                'city_type' => 1,

                'is_city'   => 1
            ];

            $city_id = $city_model->where(['uniacid'=>$this->_uniacid,'status'=>1])->where(function ($query) use ($mapor){
                $query->whereOr($mapor);
            })->where('title','in',$city)->order('id desc')->value('id');

        }

        $type_model = new Type();

        $dis = [

            'uniacid' => $this->_uniacid,

            'status'  => 1,
        ];

        if($this->is_app==0&&getConfigSetting($this->_uniacid,'shield_massage')==1){

            $wheres[] = ['type','<>',1];
        }else{

            $wheres = [];
        }

        $data = $type_model->where($dis)->where($wheres)->order('top desc,id desc')->field('cate_name,id')->select()->toArray();
        //判断插件权限没有返回空
        $auth = AdminMenu::getAuthList((int)$this->_uniacid,['store']);

        $dis = [

            'a.uniacid' => $this->_uniacid,

            'a.status'  => 1,

            'a.check_status'=> 2,

            'a.is_add'=> 0,

            'a.index_show' => 1
        ];

        if(empty($auth['store'])){

            $dis['a.is_door'] = 1;
        }

        if(!empty($city)){

            $dis['c.city_id'] = $city_id;
        }

        $arr = [];

        if(!empty($data)){

            foreach ($data as $k=>$v){

                $dis['a.industry_type'] = $v['id'];

                $list = $this->model->alias('a')
                    ->join('massage_service_service_coach b','a.id = b.ser_id')
                    ->join('massage_service_coach_list c','b.coach_id = c.id')
                    ->where($dis)
                    ->field('a.id,a.show_unit,a.cover,a.title,ROUND(a.price+a.material_price,2) as price')
                    ->group('a.id')
                    ->order("a.type desc,a.top desc,a.id desc")
                    ->limit(6)
                    ->select()
                    ->toArray();

                if(!empty($list)){

                    $arr[$k]['cate_name'] = $v['cate_name'];

                    $arr[$k]['list'] = $list;

                    $arr[$k]['id'] = $v['id'];
                }
            }
        }

        $total['list'] = array_values($arr);

        return $this->success($total);
    }





    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-15 14:58
     * @功能说明:审核详情
     */
    public function serviceInfo(){

        $input = $this->_param;

        $dis = [

            'id' => $input['id']
        ];

        $data = $this->model->dataInfo($dis);

        $data['price'] = round($data['price']+$data['material_price'],2);

        $member_service = new \app\member\model\Service();

        $user_member = $member_service->getUserMember($this->_uniacid,$this->getUserId());
        //有会员插件
        $data['member_service'] = $user_member['member_auth']==1?$data['member_service']:0;

        $data['member_info'] = $member_service->getServiceMember($data['id'],$user_member['member_level'],$data['member_service']);
        //服务部位
        $position_model = new ServicePositionConnect();

        $position = $position_model->positionInfo($data['id']);

        $data['position_data'] = $position;
        //保障
        $guarantee_model = new ServiceGuaranteeConnect();

        $data['guarantee_data']= $guarantee_model->guaranteeInfo($data['id']);
        //是否开启了会员折扣插件
        $member_auth = memberDiscountAuth($this->_uniacid);

        if($member_auth['status']==1){

            $data['member_price'] = round($data['price']*$member_auth['discount']/10,2);
            //会员状态
            $data['member_status'] = 0;
            //多少折
            $data['member_discount'] = $member_auth['discount'];
            //节约多少元
            $data['member_discount_price'] = round($data['price']-$data['member_price'],2);

            if(!empty($this->getUserId())){

                $user_model = new User();

                $member_discount_time = $user_model->where(['id'=>$this->getUserId()])->value('member_discount_time');
                //如果不是会员
                if($member_discount_time>=time()){

                    $data['member_status'] = 1;
                }
            }
        }

        return $this->success($data);
    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-23 14:16
     * @功能说明:获取配置信息
     */
   public function configInfo(){

       $input = $this->_param;

       $rules_status = !empty($input['rules_status'])?$input['rules_status']:0;

       $key = 'key_config_key'.$rules_status;

       $config = getCache($key,$this->_uniacid);

       if(empty($config)){

           $dis = [

               'uniacid' => $this->_uniacid
           ];

           $config_model = new Config();

           $arr = 'agent_article_id,bus_end_time,uniacid,appsecret,app_app_secret,appid,app_app_id,web_app_id,web_app_secret,gzh_appid,order_tmp_id,cancel_tmp_id,max_day,time_unit,service_cover_time,can_tx_time,company_pay,short_id,short_secret';

           $config = $config_model->where($dis)->withoutField($arr)->find()->toArray();

           $pay_config_model = new PayConfig();

           $pay_config = $pay_config_model->dataInfo($dis);

           $config['alipay_status'] = $pay_config['alipay_status'];

           $short_config_model = new ShortCodeConfig();

           $short_config = $short_config_model->dataInfo($dis);

           $config['short_code_status'] = $short_config['short_code_status'];

           $config['bind_phone_type'] = $short_config['bind_phone_type'];
           //代理商文章标题
           if(!empty($config['agent_article_id'])){

               $article_model = new ArticleList();

               $config['agent_article_title'] = $article_model->where(['id'=>$config['agent_article_id']])->value('title');
           }

           $config_model = new ConfigSetting();

           $data = $config_model->dataInfo($this->_uniacid,['app_wechat_pay','reseller_menu_name','broker_menu_name','life_text','agent_default_name','balance_discount_status','material_text','personal_income_tax_text','salesman_check_status','pageColor','coach_account_phone_status','user_update_location','broker_apply_port','web_coach_port','add_flow_path','coach_app_text','channel_menu_name','channel_check_status','coach_show_lable','free_fare_distance','free_fare_bear','block_user_type','hide_admin_mobile','hide_coach_image','coach_filter_show','coach_apply_type','coach_list_format','coach_force_show','coach_license_show','realtime_location','merchant_switch_show','force_login','tax_point','salesman_poster','channel_poster','order_contact_coach','service_recording_show','coach_level_show','attendant_name','service_start_recording','service_end_recording','coach_apply_show','recharge_status','recommend_style','coach_format','wechat_transfer','alipay_transfer','under_transfer','bank_transfer','coach_career_show', 'play_list_format', 'agent_update_city', 'free_fare_select','coupon_get_type']);

           $config = array_merge($config,$data);

          // $auth = AdminMenu::getAuthList((int)$this->_uniacid,['memberdiscount','balancediscount','industrytype','materialshop','couponatv','subagent','agentcoach','orderradar','heepay','salesman','abnormalorder','channel','reseller','payreseller','coachbroker','channelcate','coachport','skillservice','coachtravel','member','dynamic','recommend','store','map','recording','adapay','coachcredit','partner']);
		  $auth = [
		                  
		                  'memberdiscount'=>true,
		                  'balancediscount=>true',
		                  'industrytype'=> 1,
		                  'materialshop'=>true,
		                  'couponatv'=>true,
		                  'subagent'=>true,
		                  'agentcoach'=>true,
		                  'orderradar'=>true,
		                  'heepay'=>true,
		                  'salesman'=>true,
		                  'abnormalorder'=>true,
		                  'channel'=>true,
		                  'reseller'=>true,
		                  'coachbroker'=>true,
		                  'payreseller'=>true,
		                  'channelcate'=>true,
		                  'coachport'=>true,
		                  'skillservice'=>true,
		                  'coachtravel'=>true,
		                  'member'=>true,
		                  'dynamic'=>true,
		                  'recommend'=>true,
		                  'store'=>true,
		                  'map'=>true,
		                  'recording'=>true,
		                  'adapay'=>true,
		                  'coachcredit'=>true,
				          'partner'=>true,
		              ];

           $setting_status = getConfigSettingArr($this->_uniacid,['reseller_status','channel_status','broker_status','salesman_status']);

           $auth['reseller'] = $auth['reseller']==false?false:$setting_status['reseller_status'];

           $auth['channel']  = $auth['channel']==false?false:$setting_status['channel_status'];

           $auth['coachbroker'] = $auth['coachbroker']==false?false:$setting_status['broker_status'];

           $auth['salesman'] = $auth['salesman']==false?false:$setting_status['salesman_status'];

           $config_model = new MassageConfig();

           if($auth['dynamic']!=false){

               $config_data = $config_model->dataInfo(['uniacid'=>$this->_uniacid]);

               $auth['dynamic'] = !empty($config_data['dynamic_status'])?$auth['dynamic']:0;
           }

           if($auth['adapay']!=false){

               $adapay_config = new \app\adapay\model\Config();

               $config_data = $adapay_config->dataInfo(['uniacid'=>$this->_uniacid]);

               $auth['adapay'] = !empty($config_data['status'])?$auth['adapay']:0;
           }

           if($auth['heepay']!=false&&$auth['adapay']!=1){

               $adapay_config  = new \app\heepay\model\Config();

               $config_data    = $adapay_config->dataInfo(['uniacid'=>$this->_uniacid]);

               $auth['heepay'] = !empty($config_data['status'])?$auth['heepay']:0;
           }

           if($auth['member']!=false){

               $member_config = new \app\member\model\Config();

               $config_data = $member_config->dataInfo(['uniacid'=>$this->_uniacid]);

               $auth['member'] = !empty($config_data['status'])?$auth['member']:0;
           }

           if($auth['coachcredit']!=false){

               $member_config = new CreditConfig();

               $config_data = $member_config->dataInfo(['uniacid'=>$this->_uniacid]);

               $auth['coachcredit'] = !empty($config_data['status'])?$auth['coachcredit']:0;
           }

           if($auth['memberdiscount']!=false){

               $auth['memberdiscount'] = memberDiscountAuth($this->_uniacid)['status'];
           }

           $config['plugAuth']  = $auth;

           if(!empty($input['rules_status'])){

               unset($config['trading_rules']);

               unset($config['login_protocol']);

               unset($config['information_protection']);
           }

           $diy_model = new Diy();

           $diy_config = $diy_model->dataInfo(['uniacid'=>$this->_uniacid]);

           $config['page']   = json_decode($diy_config['page'],true);

           $config['tabBar'] = json_decode($diy_config['tabbar'],true);

           $config_model = new BtnConfig();

           $config['btn_config'] = $config_model->where(['uniacid'=>$this->_uniacid])->field('text,btn_color,font_color,type')->order('type,id desc')->select()->toArray();

           setCache($key,$config,7,$this->_uniacid);
       }

       return $this->success($config);

   }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-11 17:12
     * @功能说明:技师的服务列表
     */

   public function coachServiceList(){

       $input = $this->_param;

       $dis[] = ['a.uniacid','=',$this->_uniacid];

       $dis[] = ['a.status','=',1];

       $dis[] = ['a.check_status','=',2];

       if(!empty($input['coach_id'])){

           $dis[] = ['b.coach_id','=',$input['coach_id']];
       }

       $is_add = !empty($input['is_add'])?$input['is_add']:0;

       $dis[] = ['a.is_add','=',$is_add];
       //判断插件权限没有返回空
       $auth = AdminMenu::getAuthList((int)$this->_uniacid,['store']);
       //加钟时候需要判断服务方式
       if(!empty($input['order_id'])){

           $order_model = new Order();

           $store_id = $order_model->where(['id'=>$input['order_id']])->value('store_id');

           if(!empty($store_id)){

               $dis[] = ['a.is_store','=',1];

           }else{

               $dis[] = ['a.is_door','=',1];
           }
       }elseif(empty($auth['store'])){

           $dis[] = ['a.is_door','=',1];
       }

       $data['data'] = $this->model->serviceCoachList($dis);

       $data['car_price'] = $data['car_count'] =  0;

       if(!empty($data['data'])){

           $car_model   = new Car();

           foreach ($data['data'] as $k=>&$v){

               $v['price'] = round($v['price']+$v['material_price'],2);

               $dis = [

                   'service_id' => $v['id'],

                   'coach_id'   => $input['coach_id'],

                   'user_id'    => $this->getUserId(),

                   'status'     => 1,

               ];

               if(!empty($input['order_id'])){

                   $dis['order_id'] = $input['order_id'];
               }

               $car_info = $car_model->dataInfo($dis);

               $v['car_id']  = !empty($car_info)?$car_info['id']:0;

               $v['car_num'] = !empty($car_info)?$car_info['num']:0;

               if(!empty($car_info)){

                   $data['car_price'] += $v['price']*$v['car_num'];

                   $data['car_price'] = round($data['car_price'],2);

                   $data['car_count'] += $v['car_num'];

               }
           }
       }
       //获取服务的会员信息
       $data['data'] = $this->model->giveListMemberInfo($data['data'],$this->_uniacid,$this->getUserId());
       //会员价
       $data['data'] = giveMemberPrice($this->_uniacid,$data['data']);

       return $this->success($data);

   }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-07 10:21
     * @功能说明:服务技师列表
     */
   public function serviceCoachList(){

       $input = $this->_param;

       $sql_type = 1;

       $dis[] = ['a.uniacid','=',$this->_uniacid];

       $dis[] = ['a.status','=',2];

       $dis[] = ['a.auth_status','=',2];

       $dis[] = ['a.is_work','=',1];

       if(!empty($input['ser_id'])){

           $dis[] = ['b.ser_id','=',$input['ser_id']];

           $sql_type = 2;
       }

       if(!empty($input['coach_name'])){

           $dis[] = ['a.coach_name','like','%'.$input['coach_name'].'%'];
       }

       if(!empty($input['city_id'])){

           $dis[] = ['a.city_id','=',$input['city_id']];
       }

       if(isset($input['sex'])){

           $dis[] = ['a.sex','=',$input['sex']];
       }
       if(!empty($input['work_time_start'])&&!empty($input['work_time_end'])){

           $dis[] = ['a.work_time','between',"{$input['work_time_start']},{$input['work_time_end']}"];
       }
       //收藏技师
       $collect_model = new CoachCollect();

       $user_id = !empty($this->getUserId()) ? $this->getUserId() : 0;

       $collect = $collect_model->where(['user_id' => $user_id])->column('coach_id');
       //推荐技师
       if(!empty($input['recommend'])|| (isset($input['sort']) && $input['sort'] == 1)) {

           $config_model = new ConfigSetting();

           $config = $config_model->dataInfo($this->_uniacid, ['auto_recommend']);
           //手动推荐
           if ($config['auto_recommend'] == 0) {

               $dis[] = ['a.recommend', '=', 1];
           }
       }
       //根据服务类型查询上门 还是到店
       if(!empty($input['service_type'])){

            $dis[] = ['c.status','=',1];

            $dis[] = ['c.is_add','=',0];

            if($input['service_type']==1){

                $dis[] = ['c.is_store','=',1];

            }else{

                $dis[] = ['c.is_door','=',1];
            }
            $sql_type = 2;
       }
       //服务类目查询
       if(!empty($input['cate_id'])){

           $cate_model = new CateConnect();

           $id = $cate_model->getCateServiceOnline($input['cate_id']);

           $dis[] = ['b.ser_id','in',$id];

           $sql_type = 2;
       }
        //职业
       if (!empty($input['industry_type'])) {

           $dis[] = ['a.industry_type', '=', $input['industry_type']];
       }

       if($this->is_app==0&&getConfigSetting($this->_uniacid,'shield_massage')==1){

           $dis[] = ['a.industry_type','<>',1];
       }
       //不查陪玩官
       if (!empty($input['not_have_play'])) {

           $dis[] = ['a.industry_type', '<>', 2];
       }
       //收藏
       if (!empty($input['collect']) || (isset($input['sort']) && $input['sort'] == 6)) {

           $dis[] = ['a.id', 'in', $collect];
       }
       //新人
       if (isset($input['sort']) && $input['sort'] == 3) {
           //近7天开始时间戳
           $start_time = strtotime("-7 day");

           $dis[] = ['a.create_time', '>=', $start_time];
       }

       $store_model = new \app\store\model\StoreList();
       //搜索名字
       if(!empty($input['store_name'])){

           $store_coach_id = $store_model->getStoreCoachId($input['store_name'],2);

           $dis[] = ['a.id','in',$store_coach_id];
       }

       $city_id = !empty($input['city_id'])?$input['city_id']:0;

       if(!empty($input['store_id'])){

           $store_coach_id = $store_model->getStoreCoachId($input['store_id']);

           $dis[] = ['a.id','in',$store_coach_id];
       }
       //服务中
       $working_coach = $this->coach_model->getWorkingCoach($this->_uniacid);
       //当前时间不可预约
       $cannot = CoachTimeList::getCannotCoach($this->_uniacid,0,$city_id);

       $cannot = array_diff($cannot,$working_coach);
       //如果登录不返回被屏蔽的技师
       if(!empty($this->getUserId())){

           $shield_coach = $this->coach_model->getShieldCoach($this->getUserId());

           if(!empty($input['service_time'])){
               //当前时间不可预约
               $cannot = CoachTimeList::getCannotCoach($this->_uniacid,$input['service_time'],$city_id);

               $working_coach = array_merge($working_coach,$cannot);

               $shield_coach  = array_merge($working_coach,$shield_coach);
           }

           if(!empty($shield_coach)){

               $dis[] = ['a.id','not in',$shield_coach];
           }
       }
       //可服务不可服务
       if(!empty($input['type'])){
           //可服务
           if($input['type']==1){

               $array = array_merge($working_coach,$cannot);

               $dis[] = ['a.id','not in',$array];

           }elseif($input['type']==2){//服务中

               $dis[] = ['a.id','in',$working_coach];

           }elseif ($input['type']==3){//可预约

               $dis[] = ['a.id','in',$cannot];
           }
       }

       $free_fare = !empty($input['free_fare'])?$input['free_fare']:0;

       $sort = !empty($input['sort'])?$input['sort']:0;

       $auth = AdminMenu::getAuthList((int)$this->_uniacid,['recommend','coachcredit','store']);

       $lat = !empty($input['lat'])?$input['lat']:0;

       $lng = !empty($input['lng'])?$input['lng']:0;

       $alh = 'ACOS(SIN(('.$lat.' * 3.1415) / 180 ) *SIN((a.lat * 3.1415) / 180 ) +COS(('.$lat.' * 3.1415) / 180 ) * COS((a.lat * 3.1415) / 180 ) *COS(('.$lng.' * 3.1415) / 180 - (a.lng * 3.1415) / 180 ) ) * 6378.137*1000 as distance';

       $data = $this->coach_model->serviceCoachList($dis,$alh,10,$auth['coachcredit'],$this->_uniacid,$city_id,$free_fare,$sql_type,$sort);

       if(!empty($data['data'])){

           $config_model = new Config();

           $coach_model  = new Coach();

           $icon_model   = new CoachIcon();

           $personality_icon_model = new IconCoach();

           $config= $config_model->dataInfo(['uniacid'=>$this->_uniacid]);

           $coach_icon_type = getConfigSetting($this->_uniacid,'coach_icon_type');
            //销冠
           $top   = $this->model->getSaleTopOne($this->_uniacid,$city_id,$coach_icon_type);
            //销售单量前5
           $five  = $this->model->getSaleTopFive($this->_uniacid,$top,$city_id,$coach_icon_type);
            //最近七天注册
           $seven = $this->model->getSaleTopSeven($this->_uniacid,$city_id,$coach_icon_type);

           $type_model = new Type();

           $station_model = new StationIcon();

           $type = $type_model->dataSelect(['uniacid' => $this->_uniacid], 'employment_years,id');

           $station = $station_model->where(['uniacid' => $this->_uniacid, 'status' => 1])->column('title', 'id');

           $store_id = !empty($input['store_id'])?$input['store_id']:0;

           foreach ($data['data'] as &$v){

               $v['star'] = number_format($v['star'],1);
               //车费免费
               $v['free_fare'] = $v['free_fare_bear']>0&&$v['free_fare_distance']>0&&$v['free_fare_distance']*1000>=$v['distance_data']?1:0;

               if(isset($v['credit_value'])){

                   $v['credit_value'] = floatval($v['credit_value']);
               }

               if($auth['store']==true){
                   //返回距离最近的门店
                   $v['store'] = StoreCoach::getNearStore($v['id'],$v['admin_id'],$lng,$lat,$store_id);
               }

               $v['is_collect'] = in_array($v['id'],$collect)?1:0;

               $v['near_time']  = $coach_model->getCoachEarliestTimev3($v,$config,0,0,1);

               if (in_array($v['id'],$working_coach)){

                   $text_type = 2;

               }elseif (empty($v['near_time'])){

                   $text_type = 4;

               }elseif (!in_array($v['id'],$cannot)){

                   $text_type = 1;

               }else{

                   $text_type = 3;
               }

               $v['text_type']  = $text_type;

               if(in_array($v['id'],$top)){

                   $v['coach_type_status'] = 1;

               }elseif (in_array($v['id'],$five)){

                   $v['coach_type_status'] = 2;

               }elseif (in_array($v['id'],$seven)){

                   $v['coach_type_status'] = 3;

               }elseif ($v['recommend_icon']==1){

                   $v['coach_type_status'] = 4;

               }else{

                   $v['coach_type_status'] = 0;
               }

               if($coach_icon_type==1){

                   $v['coach_icon'] = $icon_model->where(['id'=>$v['coach_icon']])->value('icon');

               }else{

                   $v['coach_icon'] = '';
               }
               //个性标签
               if(!empty($v['personality_icon'])){

                   $v['personality_icon'] = $personality_icon_model->where(['id'=>$v['personality_icon'],'status'=>1])->value('title');
               }else{

                   $v['personality_icon'] = '';
               }

               $v['year'] = !empty($v['birthday']) ? floor((time() - $v['birthday']) / (86400 * 365)) : 0;

               $v['industry_data'] = empty($v['industry_type']) ? [] : $type[$v['industry_type']];

               $v['station_icon_name'] = empty($v['station_icon']) ? '' : (isset($station[$v['station_icon']]) ? $station[$v['station_icon']] : '');
           }
       }
       //商家电话
       if(!empty($input['city_id'])){

           $data['merchant_phone'] = City::where(['id'=>$input['city_id']])->value('merchant_phone');
       }

       return $this->success($data);
   }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-15 17:00
     * @功能说明:代理商详情
     */
   public function agentInfo(){

       $input = $this->_param;

       $admin_model = new \app\massage\model\Admin();

       $data = $admin_model->where(['id'=>$input['admin_id']])->field('agent_name,id,license,merchant_name')->find();

       return $this->success($data);

   }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-21 17:03
     * @功能说明:第二中板式第技师列表
     */
   public function typeServiceCoachList(){

       $input = $this->_param;

       $dis[] = ['a.uniacid','=',$this->_uniacid];

       $dis[] = ['a.status','=',2];

       $dis[] = ['a.auth_status','=',2];

       $sql_type = 1;

       if(!empty($input['ser_id'])){

           $dis[] = ['b.ser_id','=',$input['ser_id']];

           $sql_type = 2;
       }

       if(!empty($input['coach_name'])){

           $dis[] = ['a.coach_name','like','%'.$input['coach_name'].'%'];
       }
       //收藏技师
       $collect_model = new CoachCollect();

       $user_id = !empty($this->getUserId()) ? $this->getUserId() : 0;

       $collect = $collect_model->where(['user_id' => $user_id])->column('coach_id');
       //推荐技师
       if(!empty($input['recommend'])|| (isset($input['sort']) && $input['sort'] == 1)){

           $config_model = new ConfigSetting();

           $config = $config_model->dataInfo($this->_uniacid,['auto_recommend']);
           //手动推荐
           if($config['auto_recommend']==0){

               $dis[] = ['a.recommend','=',1];
           }
       }

       $store_model = new \app\store\model\StoreList();

       if(!empty($input['store_id'])){

           $store_coach_id = $store_model->getStoreCoachId($input['store_id']);

           $dis[] = ['a.id','in',$store_coach_id];
       }
       //搜索名字
       if(!empty($input['store_name'])){

           $store_coach_id = $store_model->getStoreCoachId($input['store_name'],2);

           $dis[] = ['a.id','in',$store_coach_id];
       }

       if(isset($input['sex'])){

           $dis[] = ['a.sex','=',$input['sex']];
       }
       //根据服务类型查询上门 还是到店
       if(!empty($input['service_type'])){

           $dis[] = ['c.is_add','=',0];

           $dis[] = ['c.status','=',1];

           if($input['service_type']==1){

               $dis[] = ['c.is_store','=',1];

           }else{

               $dis[] = ['c.is_door','=',1];
           }
           $sql_type = 2;
       }

       if(!empty($input['city_id'])){

           $dis[] = ['a.city_id','=',$input['city_id']];
       }

       $city_id = !empty($input['city_id'])?$input['city_id']:0;

       if(!empty($input['work_time_start'])&&!empty($input['work_time_end'])){

           $dis[] = ['a.work_time','between',"{$input['work_time_start']},{$input['work_time_end']}"];
       }

       if(!empty($input['cate_id'])){

           $cate_model = new CateConnect();

           $id = $cate_model->getCateServiceOnline($input['cate_id']);

           $dis[] = ['b.ser_id','in',$id];

           $sql_type = 2;
       }

       if($this->is_app==0&&getConfigSetting($this->_uniacid,'shield_massage')==1){

           $dis[] = ['a.industry_type','<>',1];
       }
       //职业
       if (!empty($input['industry_type'])) {

           $dis[] = ['a.industry_type', '=', $input['industry_type']];
       }
        //不查陪玩官
       if (!empty($input['not_have_play'])) {

           $dis[] = ['a.industry_type', '<>', 2];
       }
       //收藏
       if (!empty($input['collect']) || (isset($input['sort']) && $input['sort'] == 6)) {

           $dis[] = ['a.id', 'in', $collect];
       }
       //新人
       if (isset($input['sort']) && $input['sort'] == 3) {
           //近7天开始时间戳
           $start_time = strtotime("-7 day");

           $dis[] = ['a.create_time', '>=', $start_time];
       }

       $this->coach_model->setIndexTopCoach($this->_uniacid);
       //如果登录不返回被屏蔽的技师
       if(!empty($this->getUserId())){

           $shield_coach = $this->coach_model->getShieldCoach($this->getUserId());

           if(!empty($input['service_time'])){
               //服务中
               $working_coach = $this->coach_model->getWorkingCoach($this->_uniacid,$input['service_time']);
               //当前时间不可预约
               $cannot = CoachTimeList::getCannotCoach($this->_uniacid,$input['service_time']);

               $working_coach = array_merge($working_coach,$cannot);

               $shield_coach  = array_merge($working_coach,$shield_coach);
           }

           if(!empty($shield_coach)){

               $dis[] = ['a.id','not in',$shield_coach];
           }
       }

       $free_fare = !empty($input['free_fare'])?$input['free_fare']:0;

       $sort = !empty($input['sort'])?$input['sort']:0;

       $lat = !empty($input['lat'])?$input['lat']:0;

       $lng = !empty($input['lng'])?$input['lng']:0;

       $alh = 'ACOS(SIN(('.$lat.' * 3.1415) / 180 ) *SIN((a.lat * 3.1415) / 180 ) +COS(('.$lat.' * 3.1415) / 180 ) * COS((a.lat * 3.1415) / 180 ) *COS(('.$lng.' * 3.1415) / 180 - (a.lng * 3.1415) / 180 ) ) * 6378.137*1000 as distance';

       $auth = AdminMenu::getAuthList((int)$this->_uniacid,['recommend','coachcredit','store']);

       $data = $this->coach_model->typeServiceCoachList($dis,$alh,10,$auth['coachcredit'],$this->_uniacid,$city_id,$free_fare,$sql_type,$sort);

       $store_id = !empty($input['store_id'])?$input['store_id']:0;

       if(!empty($data['data'])){

           $config_model = new Config();

           $coach_model  = new Coach();

           $icon_model   = new CoachIcon();

           $personality_icon_model = new IconCoach();

           $config= $config_model->dataInfo(['uniacid'=>$this->_uniacid]);

           $coach_icon_type = getConfigSetting($this->_uniacid,'coach_icon_type');
           //销冠
           $top   = $this->model->getSaleTopOne($this->_uniacid,$city_id,$coach_icon_type);
           //销售单量前5
           $five  = $this->model->getSaleTopFive($this->_uniacid,$top,$city_id,$coach_icon_type);
           //最近七天注册
           $seven = $this->model->getSaleTopSeven($this->_uniacid,$city_id,$coach_icon_type);

           $type_model = new Type();

           $type = $type_model->dataSelect(['uniacid'=>$this->_uniacid],'employment_years,id');

           $station_model = new StationIcon();

           $station = $station_model->where(['uniacid' => $this->_uniacid, 'status' => 1])->column('title', 'id');

           foreach ($data['data'] as &$v){

               $v['star'] = number_format($v['star'],1);
               //车费免费
               $v['free_fare'] = $v['free_fare_bear']>0&&$v['free_fare_distance']>0&&$v['free_fare_distance']*1000>=$v['distance_data']?1:0;

               if(isset($v['credit_value'])){

                   $v['credit_value'] = floatval($v['credit_value']);
               }

               if($auth['store']==true){
                   //返回距离最近的门店
                   $v['store'] = StoreCoach::getNearStore($v['id'],$v['admin_id'],$lng,$lat,$store_id);
               }

               $v['is_collect'] = in_array($v['id'],$collect)?1:0;

               $v['near_time']  = $coach_model->getCoachEarliestTimev3($v,$config,0,0,1);

               if ($v['is_work']==0||empty($v['near_time'])){

                   $text_type = 4;

               }elseif ($v['index_top']==1){

                   $text_type = 1;

               }else{

                   $text_type = 3;
               }

               $v['text_type']  = $text_type;

               if(in_array($v['id'],$top)){

                   $v['coach_type_status'] = 1;

               }elseif (in_array($v['id'],$five)){

                   $v['coach_type_status'] = 2;

               }elseif (in_array($v['id'],$seven)){

                   $v['coach_type_status'] = 3;

               }elseif ($v['recommend_icon']==1){

                   $v['coach_type_status'] = 4;

               }else{

                   $v['coach_type_status'] = 0;
               }

               if($coach_icon_type==1){

                   $v['coach_icon'] = $icon_model->where(['id'=>$v['coach_icon']])->value('icon');

               }else{

                   $v['coach_icon'] = '';
               }
               //个性标签
               if(!empty($v['personality_icon'])){

                   $v['personality_icon'] = $personality_icon_model->where(['id'=>$v['personality_icon'],'status'=>1])->value('title');
               }else{

                   $v['personality_icon'] = '';
               }

               $v['year'] = !empty($v['birthday']) ? floor((time() - $v['birthday']) / (86400 * 365)) : 0;

               $v['industry_data'] = empty($v['industry_type']) ? [] : $type[$v['industry_type']];

               $v['station_icon_name'] = empty($v['station_icon']) ? '' : (isset($station[$v['station_icon']]) ? $station[$v['station_icon']] : '');
           }
       }
       //商家电话
       if(!empty($input['city_id'])){

           $data['merchant_phone'] = City::where(['id'=>$input['city_id']])->value('merchant_phone');
       }

       return $this->success($data);
   }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-24 14:07
     * @功能说明:购物车信息
     */
    public function carInfo(){

        $input = $this->_param;

        $order_id = !empty($input['order_id'])?$input['order_id']:0;
        //购物车信息
        $car_info = $this->car_model->carPriceAndCount($this->getUserId(),$input['coach_id'],1,$order_id);

        return $this->success($car_info);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-23 09:48
     * @功能说明:再来一单
     */
    public function onceMoreOrder(){

        $input = $this->_input;

        $order_model = new Order();

        $order = $order_model->dataInfo(['id'=>$input['order_id']]);

        $coach = $this->coach_model->dataInfo(['id'=>$order['coach_id']]);

        if($coach['status']!=2||$coach['is_work']==0){

            $this->errorMsg('技师未上班');
        }
        //清空购物车
        $this->car_model->where(['user_id'=>$this->getUserId(),'coach_id'=>$order['coach_id']])->delete();

        Db::startTrans();

        foreach ($order['order_goods'] as $v){

            $ser = $this->model->dataInfo(['id'=>$v['goods_id']]);

            if(empty($ser)||$ser['status']!=1){

                Db::rollback();

                $this->errorMsg('服务已经下架');
            }

            $dis = [

                'user_id'   => $this->getUserId(),

                'uniacid'   => $this->_uniacid,

                'coach_id'  => $order['coach_id'],

                'service_id'=> $v['goods_id'],

                'num'       => $v['num']
            ];

            $res = $this->car_model->dataAdd($dis);
        }

        Db::commit();

        return $this->success($res);

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-24 14:46
     * @功能说明:添加到购物车
     */
    public function addCar(){

        $input = $this->_input;

        $order_id = !empty($input['order_id'])?$input['order_id']:0;

        $insert = [

            'uniacid'   => $this->_uniacid,

            'user_id'   => $this->getUserId(),

            'coach_id'  => $input['coach_id'],

            'service_id'=> $input['service_id'],

            'order_id'  => $order_id,

        ];
        //目前只能加钟一个
        if(!empty($order_id)){

           // $this->car_model->where(['order_id'=>$order_id])->delete();
        }
        //从服务详情直接下单
        if(!empty($input['coach_service'])){

            $this->car_model->where(['coach_id'=>$input['coach_id']])->delete();

        }

        if(isset($input['num'])&&$input['num']<=0){

            $this->errorMsg('数据错误');
        }

        $info = $this->car_model->dataInfo($insert);
        //增加数量
        if(!empty($info)){

            if(!empty($input['is_top'])){

                return $this->success(1);

            }

            $res = $this->car_model->dataUpdate(['id'=>$info['id']],['num'=>$info['num']+$input['num']]);

        }else{
            //添加到购物车
            $insert['num'] = $input['num'];

            $insert['status'] = 1;

            $res = $this->car_model->dataAdd($insert);

            $id  = $this->car_model->getLastInsID();

            return $this->success($id);
        }

        return $this->success($res);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-24 14:54
     * @功能说明:删除购物车
     */
    public function delCar(){

        $input = $this->_input;

        $info = $this->car_model->dataInfo(['id'=>$input['id']]);
        //加少数量
        if(!empty($info)&&$info['num']>$input['num']){

            $res = $this->car_model->dataUpdate(['id'=>$info['id']],['num'=>$info['num']-$input['num']]);

        }else{

            $res = $this->car_model->where(['id'=>$info['id']])->delete();
        }

        return $this->success($res);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-25 10:39
     * @功能说明:
     */
    public function carUpdate(){

        $input = $this->_input;

        $res = $this->car_model->where('id','in',$input['id'])->update(['status'=>$input['status']]);

        return $this->success($res);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-24 14:59
     * @功能说明:批量删除购物车
     */
    public function delSomeCar(){

        $input = $this->_input;

        $dis = [

            'uniacid' => $this->_uniacid,

            'user_id' => $this->getUserId(),

            'coach_id'=> $input['coach_id'],

        ];

        $res = $this->car_model->where($dis)->delete();

        return $this->success($res);

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-05 23:16
     * @功能说明:评价列表
     */
    public function commentList(){

        $input = $this->_param;

        $dis[] = ['a.uniacid','=',$this->_uniacid];

        $dis[] = ['a.status','=',1];

        if(!empty($input['coach_id'])){

            $dis[] = ['d.id','=',$input['coach_id']];
        }

        if(!empty($input['coach_name'])){

            $dis[] = ['d.coach_name','like','%'.$input['coach_name'].'%'];
        }

        if(!empty($input['goods_name'])){

            $dis[] = ['c.goods_name','like','%'.$input['goods_name'].'%'];

        }

        $comment_model = new Comment();

        $config_model  = new Config();

        $data = $comment_model->dataList($dis);

        $anonymous_evaluate = $config_model->where(['uniacid'=>$this->_uniacid])->value('anonymous_evaluate');

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $v['create_time'] = date('Y-m-d H:i:s',$v['create_time']);
                //开启匿名评价
                if($anonymous_evaluate==1||$v['user_id']==0){

                    $v['nickName'] = '匿名用户';

                    $v['avatarUrl']= 'https://' . $_SERVER['HTTP_HOST'] . '/admin/farm/default-user.png';
                }

            }
        }

        return $this->success($data);
    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-15 14:58
     * @功能说明:技师详情
     */
    public function coachInfo(){

        $input = $this->_param;

        $dis = [

            'id' => $input['id']
        ];

        $data = $this->coach_model->where($dis)->withoutField('id_card,mobile,service_price,car_price')->find()->toArray();

        $length = strlen($data['id_code']);

        $masked = $length > 0 ? substr_replace($data['id_code'], str_repeat("*", $length - 3), 3, $length - 3) : '';

        $data['id_code'] = $masked;

        $user_model = new User();

        $data['nickName'] = $user_model->where(['id'=>$data['user_id']])->value('nickName');

        $city_model = new City();

        $data['city'] = $city_model->where(['id'=>$data['city_id']])->value('title');

        $shield_model = new ShieldList();
        //是否拉黑
        $shield = $shield_model->where(['user_id'=>$this->_user['id'],'coach_id'=>$data['id']])->where('type','in',[2,3])->find();

        $data['is_shield'] = !empty($shield)?1:0;

        $store_model = new StoreList();
        //门店名字
        $data['store_name'] = $store_model->where(['id'=>$data['store_id'],'status'=>1])->value('title');
        //绑定门店(新)
        $data['store'] = StoreCoach::getStoreList($data['id']);

        $config_model = new Config();

        $config= $config_model->dataInfo(['uniacid'=>$this->_uniacid]);
        //最早可预约时间
        $data['near_time'] = $this->coach_model->getCoachEarliestTimev3($data,$config);
        //服务状态
        $data['text_type'] = $this->coach_model->getCoachWorkStatus($data['id'],$this->_uniacid);
        //生日
        $data['year'] = !empty($data['birthday'])?floor((time()-$data['birthday'])/(86400*365)):0;

        $data['order_num'] += $data['total_order_num'];
        //信用分
        $record_model = new CreditRecord();

        $data['credit_value'] = $record_model->getSingleCoachValue($this->_uniacid,$input['id']);
        //是否收藏
        $where = [

            'uniacid' => $this->_uniacid,

            'coach_id' => $data['id'],

            'user_id' => $this->getUserId()
        ];

        $collect_model = new CoachCollect();

        $res = $collect_model->dataInfo($where);

        $data['is_collect'] = !empty($res) ? 1 : 0;

        $type_model = new Type();

        $data['industry_data'] = $type_model->dataInfo(['id' => $data['industry_type']]);
        //动态点赞数
        $dy_model = new  DynamicThumbs();

        $data['dynamic_thumbs_num'] = $dy_model->where(['coach_id' => $data['id'],'status'=>1])->count();
        //动态关注`
        $follow_model = new DynamicFollow();

        $follow = $follow_model->dataInfo(['coach_id' => $input['id'], 'user_id' => $this->getUserId(), 'status' => 1]);

        $data['is_follow'] = empty($follow) ? 0 : 1;

        $data['follow_num'] = $follow_model->where(['coach_id' => $data['id'], 'status' => 1])->count();

        $station_model = new StationIcon();

        $station_icon = $station_model->where(['uniacid' => $this->_uniacid, 'status' => 1, 'id' => $data['station_icon']])->value('title');

        $data['station_icon_name'] = empty($station_icon) ? '' : $station_icon;
        //个性标签
        $personality_icon_model = new IconCoach();

        if(!empty($data['personality_icon'])){

            $data['personality_icon'] = $personality_icon_model->where(['id'=>$data['personality_icon'],'status'=>1])->value('title');
        }else{

            $data['personality_icon'] = '';
        }
        //生活标签
        $life_icon = getConfigSettingArr($this->_uniacid,['life_icon_status','life_icon_text','life_text']);

        $data = array_merge($data,$life_icon);
        //加浏览数量
        $this->coach_model->where(['id'=>$input['id']])->update(['pv'=>Db::Raw("pv+1")]);

       //$data['star'] = number_format($data['star'],1);

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-02-28 11:50
     * @功能说明:获取腾讯地图信息
     */
    public function getMapInfo(){

        $input = $this->_param;

        $key  = $input['location'];

        $data = getCache($key,$this->_uniacid);

        if(empty($data)){

            $map_key_key = getCache('map_key_key',$this->_uniacid);

            if(empty($map_key_key)){

                $config_model = new Config();

                $config = $config_model->getCacheInfo($this->_uniacid);

                $map_key_key = getConfigSetting($this->_uniacid,'tencent_map_key');

                $map_key_key = !empty($map_key_key)?explode(',',$map_key_key):[];

                if(!empty($map_key_key)){

                    array_unshift($map_key_key,$config['map_secret']);
                }else{

                    $map_key_key[] = $config['map_secret'];
                }

                setCache('map_key_key',$map_key_key,86400,$this->_uniacid);
            }

            foreach ($map_key_key as $k=>$value){

                $url  = 'https://apis.map.qq.com/ws/geocoder/v1/?location=';

                $url  = $url.$input['location'].'&key='.$value;

                $data = longbingCurl($url,[]);

                $data_arr = json_decode($data,true);

                if(isset($data_arr['status'])&&$data_arr['status']==0){

                    setCache($key,$data,300,$this->_uniacid);

                    if($k!=0){

                        unset($map_key_key[$k]);

                        array_unshift($map_key_key,$value);

                        setCache('map_key_key',$map_key_key,86400,$this->_uniacid);
                    }

                    break;

                }else{

                    $data = '{"status":1,"result":{}}';
                }
            }
        }

        return $this->success($data);
    }




    /**
     * @author chenniang
     * @DataTime: 2024-08-22 10:27
     * @功能说明:搜索附件地址
     */
    public function nearbyLocation(){

        $input = $this->_param;

        $key = 'nearbyLocation';

        $map_key_key = getCache($key,$this->_uniacid);

        if(empty($map_key_key)){

            $config_model = new Config();

            $config = $config_model->getCacheInfo($this->_uniacid);

            $map_key_key = getConfigSetting($this->_uniacid,'tencent_map_key');

            $map_key_key = !empty($map_key_key)?explode(',',$map_key_key):[];

            array_unshift($map_key_key,$config['map_secret']);

            setCache($key,$map_key_key,86400,$this->_uniacid);
        }

        foreach ($map_key_key as $k=>$value){

            $url  = 'https://apis.map.qq.com/ws/place/v1/search?boundary='.$input['boundary'].'&key='.$value.'&keyword='.$input['keyword'].'&page_size=20&page_index='.$input['page'];

            $data = longbingCurl($url,[]);

            $data_arr = json_decode($data,true);

            if(isset($data_arr['status'])&&$data_arr['status']==0){

                if($k!=0){

                    unset($map_key_key[$k]);

                    array_unshift($map_key_key,$value);

                    setCache($key,$map_key_key,86400,$this->_uniacid);
                }

                break;

            }else{

                $data = '{"status":1,"result":{}}';
            }
        }

        return $this->success($data);
    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-06-15 15:48
     * @功能说明:获取
     */
    public function getCity(){

        $input = $this->_param;

        $city_model = new City();

        $lat = $input['lat'];

        $lng = $input['lng'];

        $alh = 'ACOS(SIN(('.$lat.' * 3.1415) / 180 ) *SIN((lat * 3.1415) / 180 ) +COS(('.$lat.' * 3.1415) / 180 ) * COS((lat * 3.1415) / 180 ) *COS(('.$lng.' * 3.1415) / 180 - (lng * 3.1415) / 180 ) ) * 6378.137*1000 as distance';

        $key_find = getConfigSetting($this->_uniacid,'index_city_find');

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['status','=',1];

        $admin_id = !empty($input['admin_id'])?$input['admin_id']:0;

        $agent_update_city = getConfigSetting($this->_uniacid, 'agent_update_city');

        if (!empty($input['admin_id']) && $agent_update_city == 0) {

            $admin_model = new \app\massage\model\Admin();

            $admin = $admin_model->dataInfo(['id'=>$input['admin_id']]);

            if(!empty($admin)){

                if($admin['city_type']==3){

                    $dis[] = ['pid','=',$admin['city_id']];

                }elseif($admin['city_type']==1){

                    $dis[] = ['id','=',$admin['city_id']];
                }else{

                    $city = $city_model->dataInfo(['id'=>$admin['city_id']]);

                    if(!empty($city)){

                        if($city['is_city']==1){

                            $dis[] = ['id','=',$admin['city_id']];
                        }else{

                            $dis[] = ['id','=',$city['pid']];
                        }
                    }
                }
            }
        }

        $mapor = [

            'city_type' => 1,

            'is_city'   => 1
        ];

        $key = 'getCity_getCityss' . round($lng, 4) . '-' . round($lat, 4) . '-' . $key_find . $admin_id . $agent_update_city;

        $data= getCache($key,$this->_uniacid);

        if(!empty($data)){

            return $this->success($data);
        }
        //是否必须定位到当前城市
        if($key_find==1){

            $data = $city_model->where($dis)->where(function ($query) use ($mapor){
                $query->whereOr($mapor);
            })->field(['id,title,lat,lng,city_type,if(city_type=1,id,pid) as top',$alh])->order('city_type desc,id desc')->select()->toArray();

            $city = $this->getLatData($lng,$lat,$this->_uniacid);

            if(!empty($data)){

                foreach ($data as &$v){

                    if(is_array($city)&&in_array($v['title'],$city)){

                        $v['is_select'] = 1;

                        break;
                    }
                }
            }

        }else{

            $data = $city_model->where($dis)->where(function ($query) use ($mapor){
                $query->whereOr($mapor);
            })->field(['id,title,lat,lng,city_type,if(city_type=1,id,pid) as top',$alh])->order('distance asc,id desc')->select()->toArray();

            $data[0]['is_select'] = 1;
        }

        $data = $this->cityTop($data);

//        if(empty($lat)){
//
//            $key = 'articleJsapiTicket-';
//
//            $keys= 'articleToken-';
//
//            setCache($key,'',1,$this->_uniacid);
//
//            setCache($keys,'',1,$this->_uniacid);
//        }

        setCache($key,$data,3600,$this->_uniacid,'city_key');

        return $this->success($data);
    }



    /**
     * @param $arr
     * @功能说明:城市排序
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-09-08 17:56
     */
    public function cityTop($arr){

        if(empty($arr)){

            return $arr;
        }

        $new_arr = [];

        foreach ($arr as $value){

            $new_arr[$value['top']] = !empty($new_arr[$value['top']])?$new_arr[$value['top']]:[];
            //城市放在前面
            if($value['city_type']==1){

                array_unshift( $new_arr[$value['top']],$value);

            }else{

                array_push( $new_arr[$value['top']],$value);
            }
        }

        $total = [];

        foreach ($new_arr as $value){

            foreach ($value as $values){

                $total[] = $values;
            }
        }

        return $total;
    }






    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-06-15 16:29
     * @功能说明:优惠券
     */
    public function couponList(){

        if(empty($this->getUserId())){

            return $this->success([]);
        }

        $input = $this->_param;

        $coupon_record_model = new CouponRecord();

        $have_get = $coupon_record_model->where(['user_id'=>$this->getUserId()])->column('coupon_id');

        $dis[] = ['a.uniacid','=',$this->_uniacid];

        $dis[] = ['a.send_type','=',2];

        $dis[] = ['a.status','=',1];

        $dis[] = ['a.stock','>',0];

        $dis[] = ['a.id','not in',$have_get];

        $time = strtotime(date('Y-m-d',time()));
        //不是新用户
        if($this->_user['create_time']<$time){

            $dis[] = ['a.user_limit','<>',2];
        }

        if(!empty($this->_user['del_user_id'])){

            $dis[] = ['a.user_limit','<>',2];
        }

        $agent_coupon_location = getConfigSetting($this->_uniacid,'agent_coupon_location');

        $where = [];

        if(isset($input['lng'])&&$agent_coupon_location==1){

            $city = getCityByLat($input['lng'],$input['lat'],$this->_uniacid);

            $city = array_values($city);

            $where[] = ['a.admin_id','=',0];

            $where[] = ['c.title','in',$city];
        }

        $data = Db::name('massage_service_coupon')->alias('a')
                ->join('shequshop_school_admin b','a.admin_id = b.id','left')
                ->join('massage_service_city_list c','b.city_id = c.id','left')
                ->where($dis)
                ->where(function ($query) use ($where){
                    $query->whereOr($where);
                })
                ->field('a.id,a.title,a.user_limit,a.full,a.discount')
                ->group('a.id')
                ->select();

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-06-15 16:29
     * @功能说明:优惠券
     */
    public function couponListV2(){

        if(empty($this->getUserId())){

            return $this->success([]);
        }

        $input = $this->_param;

        $coupon_record_model = new CouponRecord();

        $have_get = $coupon_record_model->where(['user_id'=>$this->getUserId()])->column('coupon_id');

        $dis[] = ['a.uniacid','=',$this->_uniacid];

        $dis[] = ['a.send_type','=',2];

        $dis[] = ['a.status','=',1];

        $dis[] = ['a.stock','>',0];

        $dis[] = ['a.id','not in',$have_get];

        $time = strtotime(date('Y-m-d',time()));
        //不是新用户
        if($this->_user['create_time']<$time){

            $dis[] = ['a.user_limit','<>',2];
        }

        $agent_coupon_location = getConfigSetting($this->_uniacid,'agent_coupon_location');

        $where = [];

        if($agent_coupon_location==1){

//            if(!empty($input['city_id'])){
//
//                $where[] = ['c.id','=',$input['city_id']];
//
//            }else
                if(isset($input['lng'])){



                $city = getCityByLat($input['lng'],$input['lat'],$this->_uniacid);

                $city = array_values($city);

                $where[] = ['a.admin_id','=',0];

                $where[] = ['c.title','in',$city];
            }
        }

        $map[] = ['time_limit','=',1];

        $map[] = ['end_time','>',time()];

        $data = Db::name('massage_service_coupon')->alias('a')
            ->join('shequshop_school_admin b','a.admin_id = b.id','left')
            ->join('massage_service_city_list c','b.city_id = c.id','left')
            ->where($dis)
            ->where(function ($query) use ($where){
                $query->whereOr($where);
            })
            ->where(function ($query) use ($map){
                $query->whereOr($map);
            })
            ->field('a.id,a.title,a.user_limit,a.full,a.discount')
            ->group('a.id')
            ->select()
            ->toArray();

        $coupon_get_type = getConfigSetting($this->_uniacid,'coupon_get_type');

        $arr['discount'] = 0;

        $arr['coupon_num'] = count($data);

        $arr['list'] = $data;
        //自动领取
        if($coupon_get_type==1){

            $arr['discount'] = !empty($data)?array_sum(array_column($data,'discount')):0;

            $key = 'autoGetCouponautoGetCoupon'.$this->getUserId();

            incCache($key,1,$this->_uniacid);

            if(getCache($key,$this->_uniacid)==1&&!empty($data)){

                foreach ($data as $value){
                    //领取优惠券
                    $coupon_record_model->recordAdd($value['id'],$this->getUserId());
                }
            }

            foreach ($arr['list'] as &$item) {

                $record = $coupon_record_model->where(['user_id' => $this->getUserId(), 'coupon_id' => $item['id']])->find();

                $item['start_time'] = date('Y.m.d H:i:s', $record['start_time']);

                $item['end_time'] = date('Y.m.d H:i:s', $record['end_time']);
            }

            decCache($key,1,$this->_uniacid);
        }

        return $this->success($arr);
    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-06-15 22:49
     * @功能说明:用户获取卡券
     */
    public function userGetCoupon(){

        $input = $this->_input;

        $coupon_record_model = new CouponRecord();

        $coupon_model = new Coupon();

        if(!empty($input['coupon_id'])){

            foreach ($input['coupon_id'] as $value){

                $dis = [

                    'coupon_id' => $value,

                    'user_id'   => $this->getUserId()
                ];
                //判断是否领取过
                $find = $coupon_record_model->dataInfo($dis);

                if(!empty($find)){

                    continue;
                }

                $dis = [

                    'status' => 1,

                    'uniacid'=> $this->_uniacid,

                    'send_type' => 2,

                    'id' => $value
                ];
                //检查优惠券
                $coupon = $coupon_model->dataInfo($dis);

                if(!empty($coupon)){

                    $coupon_record_model->recordAdd($value,$this->getUserId());
                }
            }
        }
        return $this->success(true);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-01-30 16:59
     * @功能说明:获取插件的权限
     */
    public function plugAuth(){

        $data = AdminMenu::getAuthList((int)$this->_uniacid,['dynamic','recommend','store','map']);

        $config_model = new MassageConfig();

        $config = $config_model->dataInfo(['uniacid'=>$this->_uniacid]);

        $data['dynamic'] = !empty($config['dynamic_status'])?$data['dynamic']:0;

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-15 17:38
     * @功能说明:服务分类
     */
    public function serviceCateList(){

        $cate_model = new CateList();

        $dis = [

            'uniacid' => $this->_uniacid,

            'status'  => 1
        ];

        $data = $cate_model->where($dis)->field('title,id,cover')->order('top desc,id desc')->select()->toArray();

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-07-26 16:24
     * @功能说明:是否投过该城市
     */
    public function expectationCityCheck(){

        $input = $this->_param;

        $list_model = new ExpectationCityList();

        $info_model = new ExpectationCityInfo();

//        $dis = [
//
//            'user_id' => $this->_user['id'],
//
//            'city'    => $input['city']
//        ];
//
//        $find = $info_model->dataInfo($dis);
//
//        $data['auth'] = !empty($find)?1:0;

        $data['num']  = $info_model->where(['city'=>$input['city'],'uniacid'=>$this->_uniacid])->count();

        if(!empty($this->getUserId())){

            $admin_where = [

                'user_id' => $this->getUserId(),

                'status'  => 1
            ];

            $admin_model = new \app\massage\model\Admin();

            $admin_user = $admin_model->dataInfo($admin_where);
        }
        //是否是加盟商
        $data['is_admin'] = !empty($admin_user)?1:0;

        return $this->success($data);

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-07-24 14:15
     * @功能说明:期待开通的城市
     */
    public function expectationCity(){

        $input = $this->_param;

        $list_model = new ExpectationCityList();

        $info_model = new ExpectationCityInfo();

        $dis = [

            'user_id' => $this->_user['id'],

            'city'    => $input['city']
        ];

        $finds = $info_model->dataInfo($dis);

        if(!empty($finds)){

            $this->errorMsg('您已投过票，不可重复提交');
        }

        $dis = [

            'uniacid'=> $this->_uniacid,

            'city'   => $input['city']
        ];

        $find = $list_model->dataInfo($dis);

        Db::startTrans();

        if(empty($find)){

            $dis['num'] = 1;

            $list_model->dataAdd($dis);

        }else{

            $dis['num'] = $find['num']+1;

            $list_model->dataUpdate(['id'=>$find['id']],$dis);
        }

        $insert = [

            'uniacid' => $this->_uniacid,

            'user_id' => $this->_user['id'],

            'city'    => $input['city'],
        ];

        $res = $info_model->dataAdd($insert);

        Db::commit();

        return $this->success($res);
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-11-20 14:21
     * @功能说明:水印图片详情
     */
    public function WatermarkImgInfo(){

        $input = $this->_param;

        $watermark_model = new WatermarkList();

        $dis = [

            'coach_id' => $input['coach_id'],

            'type'     => $input['type']
        ];

        $res = $watermark_model->where($dis)->field('watermark_img,original_img')->select()->toArray();

        $data = [];

        if(!empty($res)){

            foreach ($res as $k=>$v){

                $data[$k]['img'] = !empty($v['watermark_img'])? $v['watermark_img']:$v['original_img'];
            }
        }

        return $this->success($data);
    }



    /**
     * @author chenniang
     * @DataTime: 2024-07-15 11:59
     * @功能说明:代理商车费配置详情
     */
    public function adminCarCashInfo(){

        $input = $this->_param;

        $config_model = new AdminConfig();

        $admin_id = $input['admin_id'];

        $dis = [

            'uniacid' => $this->_uniacid,

            'admin_id'=> $admin_id
        ];

        $data = $config_model->dataInfo($dis);

        return $this->success($data);
    }


    /**
     * @author chenniang
     * @DataTime: 2024-08-16 11:47
     * @功能说明:各类行业服务人员列表
     */
    public function getServiceObjList(){

        $input = $this->_param;

        $city_model = new City();

        $lat = $input['lat'];

        $lng = $input['lng'];

        $alh = 'ACOS(SIN(('.$lat.' * 3.1415) / 180 ) *SIN((lat * 3.1415) / 180 ) +COS(('.$lat.' * 3.1415) / 180 ) * COS((lat * 3.1415) / 180 ) *COS(('.$lng.' * 3.1415) / 180 - (lng * 3.1415) / 180 ) ) * 6378.137*1000 as distance';

        if(!empty($input['city_id'])){

            $city_id = $input['city_id'];

        }else{

            $dis[] = ['uniacid','=',$this->_uniacid];

            $dis[] = ['status','=',1];

            $mapor = [

                'city_type' => 1,

                'is_city'   => 1
            ];

            $key_find = getConfigSetting($this->_uniacid,'index_city_find');

            if($key_find==0){

                $city_id = $city_model->where($dis)->field(['id',$alh])->order('distance asc,id desc')->find();

            }else{

                $city = $this->getLatData($lng,$lat,$this->_uniacid);

                $city_id = $city_model->where('title','in',$city)->where($dis)->where(function ($query) use ($mapor){
                    $query->whereOr($mapor);
                })->field(['id',$alh])->order('city_type desc,distance asc,id desc')->find();
            }

            $city_id = !empty($city_id)?$city_id->id:0;
        }

        $where[] = ['uniacid','=',$this->_uniacid];

        $where[] = ['status','=',2];

        $where[] = ['auth_status','=',2];

        $where[] = ['is_work','=',1];

//        $auto_recommend = getConfigSetting($this->_uniacid,'auto_recommend');
//
//        if($auto_recommend==0){
//
//            $where[] = ['recommend','=',1];
//        }

        $where[] = ['city_id','=',$city_id];

        $where[] = ['industry_type','=',$input['industry_type']];

        $collect = [];

        if(!empty($this->getUserId())){

            $shield_coach = $this->coach_model->getShieldCoach($this->getUserId());

            if(!empty($shield_coach)){

                $where[] = ['id','not in',$shield_coach];
            }

            $collect_model = new CoachCollect();

            $collect = $collect_model->where(['user_id' => $this->getUserId()])->column('coach_id');
        }

        $coach_model = new Coach();

        $station_icon_model = new StationIcon();

        $data = $coach_model->where($where)
            ->field(['id,start_time,end_time,store_id,admin_id,coach_name,work_img,star,city_id,is_work,index_top,(order_num+total_order_num) as order_count,station_icon', $alh])
            ->order('distance asc,id desc')
            ->paginate(10)
            ->toArray();

        $config_model = new Config();

        $store_model = new StoreList();

        $config= $config_model->dataInfo(['uniacid'=>$this->_uniacid]);

        $auth = AdminMenu::getAuthList((int)$this->_uniacid,['store']);

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                if(!empty($v['station_icon'])){

                    $v['station_icon_name'] = $station_icon_model->where(['id'=>$v['station_icon'],'status'=>1])->value('title');

                }else{

                    $v['station_icon_name'] = '';
                }

                $v['is_collect'] = in_array($v['id'],$collect)?1:0;

                $v['near_time']  = $coach_model->getCoachEarliestTimev3($v,$config,0,0,1);

                if($auth['store']==true){
                    //返回距离最近的门店
                    $v['store'] = StoreCoach::getNearStore($v['id'],$v['admin_id'],$lng,$lat);
                }
            }
        }

        $data['city_id'] = $city_id;

        return $this->success($data);
    }


    /**
     * @author chenniang
     * @DataTime: 2024-08-21 10:48
     * @功能说明:定位也没城市信息
     */
    public function cityData(){

        if(!empty($this->getUserId())){

            $address_model = new Address();

            $data['address'] = $address_model->where(['user_id'=>$this->getUserId()])->order('status desc,id desc')->limit(2)->select()->toArray();
        }

        $city_model = new City();

        $mapor = [

            'city_type' => 1,

            'is_city'   => 1
        ];

        $data['city_list'] = $city_model->where(['uniacid'=>$this->_uniacid,'status'=>1,'is_hot'=>1])->where(function ($query) use ($mapor){
            $query->whereOr($mapor);
        })->field('id,title,lng,lat')->order('id desc')->limit(8)->select()->toArray();

        $hot_count = count($data['city_list']);

        $total_count = $city_model->where(['uniacid'=>$this->_uniacid,'status'=>1])->where(function ($query) use ($mapor){
            $query->whereOr($mapor);
        })->count();

        $data['have_more'] = $total_count>$hot_count?1:0;

        return $this->success($data);
    }


    /**
     * @author chenniang
     * @DataTime: 2024-08-21 11:04
     * @功能说明:城市列表
     */
    public function getCityList(){

        $city_model = new City();

        $mapor = [

            'city_type' => 1,

            'is_city'   => 1
        ];

        $data = $city_model->where(['uniacid'=>$this->_uniacid,'status'=>1])->where(function ($query) use ($mapor){
            $query->whereOr($mapor);
        })->field('id,title,lng,lat')->order('id desc')->select()->toArray();

        return $this->success($data);
    }

    /**
     * @Desc: 获取路线规划
     * @return mixed
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/15 16:51
     */
    public function getDriving()
    {
        $start_lang = \request()->param('start_lang', '');

        $start_lat = \request()->param('start_lat', '');

        $end_lng = \request()->param('end_lng', '');

        $end_lat = \request()->param('end_lat', '');

        $uniacid = $this->_uniacid;

        $data = getDriving($start_lang, $start_lat, $end_lng, $end_lat, $uniacid);

        return $this->success($data);
    }






}
