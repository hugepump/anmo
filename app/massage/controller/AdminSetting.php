<?php
namespace app\massage\controller;
use app\AdminRest;
use app\baiying\model\BaiYingConfig;
use app\fdd\model\FddAttestationRecord;
use app\fdd\model\FddConfig;
use app\industrytype\info\PermissionIndustrytype;
use app\massage\info\PermissionMassage;
use app\massage\model\AddClockBalance;
use app\massage\model\AdminConfig;
use app\massage\model\AdminWater;
use app\massage\model\AgentApply;
use app\massage\model\Appeal;
use app\massage\model\ArticleList;
use app\massage\model\BtnConfig;
use app\massage\model\CarCashType;
use app\massage\model\CarPrice;
use app\massage\model\ChannelList;
use app\massage\model\ChannelQr;
use app\massage\model\City;
use app\massage\model\ClockSetting;
use app\massage\model\Coach;
use app\massage\model\CoachIcon;
use app\massage\model\CoachLevel;
use app\massage\model\Commission;
use app\massage\model\Config;
use app\massage\model\Config as Model;
use app\massage\model\ConfigSetting;
use app\massage\model\CreditConfig;
use app\massage\model\DistributionConfig;
use app\massage\model\DistributionList;
use app\massage\model\Diy;
use app\massage\model\EmptyTicketFeeConfig;
use app\massage\model\Feedback;
use app\massage\model\HelpConfig;
use app\massage\model\Lable;
use app\massage\model\MassageConfig;
use app\massage\model\Order;
use app\massage\model\Salesman;
use app\massage\model\SendMsgConfig;
use app\massage\model\Service;
use app\massage\model\ShortCodeConfig;
use app\massage\model\StoreList;
use app\massage\model\User;
use app\massage\model\UserLabelList;
use app\mobilenode\model\RoleAdmin;
use app\reminder\info\PermissionReminder;
use app\massage\model\Banner;
use app\massage\model\MsgConfig;
use app\massage\model\PayConfig;
use app\virtual\info\PermissionVirtual;
use longbingcore\permissions\AdminMenu;
use longbingcore\permissions\SaasAuthConfig;
use think\App;



class AdminSetting extends AdminRest
{


    protected $model;

    protected $admin_model;

    //@ioncube.dk myk("sha256", "cnjdbvjdnjd") -> "cff6bcac6bd92467e0cee72e5c879cdbf7044386eda8f464c817bd5c5c963d6f" RANDOM
    public function __construct(App $app) {

        parent::__construct($app);

        $this->model = new Model();

        $this->admin_model = new \app\massage\model\Admin();

        SaasAuthConfig::getSAuthConfig($this->_uniacid);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-12 15:04
     * @功能说明:配置详情
     */
    public function configInfo(){

        $input = $this->_input;

        $dis = [

            'uniacid' => $this->_uniacid
        ];

        $data  = $this->model->dataInfo($dis);

        if(empty($input['login_protocol'])){

            unset($data['login_protocol']);

            unset($data['information_protection']);

            unset($data['trading_rules']);
        }
        //代理商文章标题
        if(!empty($data['agent_article_id'])){

            $article_model = new ArticleList();

            $data['agent_article_title'] = $article_model->where(['id'=>$data['agent_article_id']])->value('title');
        }

        $config_model = new ConfigSetting();

        $arr = $config_model->dataInfo($this->_uniacid);

        $data = array_merge($data,$arr);

        if(!empty($data['wechat_tmpl_admin'])){

            $wechat_tmpl_admin = explode(',',$data['wechat_tmpl_admin']);

            $user_model = new User();

            $data['wechat_tmpl_admin'] = $user_model->where('id','in',$wechat_tmpl_admin)->field('id,nickName,avatarUrl,phone')->select()->toArray();
        }
        //企业微信配置
        if(isset($data['wecom_staff'])){

            $data['wecom_staff'] = unserialize($data['wecom_staff']);
        }

        if(isset($data['tencent_map_key'])&&!empty($data['tencent_map_key'])){

            $data['tencent_map_key'] = explode(',',$data['tencent_map_key']);
        }

        $config_model = new MassageConfig();

        $data['order_rules'] = $config_model->where(['uniacid'=>$this->_uniacid])->value('order_rules');

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-11-27 17:50
     * @功能说明:通知详情
     */
    public function noticeInfo(){

        $reminder_model = new \app\reminder\model\Config();

        $help_model     = new HelpConfig();

        $user_model     = new User();

        $reminder_config= $reminder_model->dataInfo(['uniacid'=>$this->_uniacid]);

        $help_config = $help_model->dataInfo(['uniacid'=>$this->_uniacid]);

        $config_setting = getConfigSettingArr($this->_uniacid,['wechat_tmpl','wechat_tmpl_admin','order_wechat_agent_status','order_wechat_admin_status']);
        //下单通知
        $data['pay_order_config'] = [
            //来电通知
            'reminder' => [
                //是否通知代理商
                'notice_agent' => $reminder_config['notice_agent'],

                'reminder_admin_status' => $reminder_config['reminder_admin_status'],

                'reminder_admin_phone' => $reminder_config['reminder_admin_phone'],

                'notice_admin' => $reminder_config['notice_admin'],
            ],
            //公众号通知
            'wechat' => [

                'order_tmpl_agent_status' => $help_config['order_tmpl_agent_status'],

                'order_tmpl_admin_status' => $help_config['order_tmpl_admin_status'],

                'order_tmpl_text' => $help_config['order_tmpl_text'],

                'order_tmpl_notice_admin' => $help_config['order_tmpl_notice_admin'],
            ]
        ];

        $data['pay_order_config']['wechat']['order_tmpl_text'] = $user_model->where('id','in',$data['pay_order_config']['wechat']['order_tmpl_text'])->field('id,nickName,avatarUrl,phone')->select()->toArray();
        //订单通知
        $data['order_config'] = [
            //公众号通知
            'wechat' => [

                'wechat_tmpl' => $config_setting['wechat_tmpl'],

                'order_wechat_agent_status' => $config_setting['order_wechat_agent_status'],

                'order_wechat_admin_status' => $config_setting['order_wechat_admin_status'],

                'wechat_tmpl_admin' => !empty($config_setting['wechat_tmpl_admin'])?explode(',',$config_setting['wechat_tmpl_admin']):[],
            ],

            'reminder' => [

                'order_end_status' => $reminder_config['order_end_status']
            ]
        ];

        if(!empty($data['order_config']['wechat']['wechat_tmpl_admin'])){

            $data['order_config']['wechat']['wechat_tmpl_admin'] = $user_model->where('id','in',$data['order_config']['wechat']['wechat_tmpl_admin'])->field('id,nickName,avatarUrl,phone')->select()->toArray();
        }
        //求救通知
        $data['help_config'] = [
            //来电通知
            'reminder' => [
                //是否通知代理商
                'reminder_admin_status' => $help_config['reminder_admin_status'],

                'reminder_notice_phone' => $help_config['reminder_notice_phone'],

                'reminder_notice_admin' => $help_config['reminder_notice_admin'],

                'help_reminder_agent_status'=> $help_config['help_reminder_agent_status'],
            ],
            //短信通知
            'short' => [
                //是否通知代理商
                'short_admin_status' => $help_config['short_admin_status'],

                'help_phone' => $help_config['help_phone'],

                'short_notice_admin' => $help_config['short_notice_admin'],

                'help_short_agent_status' => $help_config['help_short_agent_status'],
            ],
            //公众号通知
            'wechat' => [

                'tmpl_admin_status' => $help_config['tmpl_admin_status'],

                'help_user_id' => $help_config['help_user_id'],

                'tmpl_notice_admin' => $help_config['tmpl_notice_admin'],

                'help_wechat_agent_status' => $help_config['help_wechat_agent_status'],

            ]

        ];

        $user_model = new User();

        $data['help_config']['wechat']['help_user_id'] = $user_model->where('id','in',$data['help_config']['wechat']['help_user_id'])->field('id,nickName,avatarUrl,phone')->select()->toArray();

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-11-27 18:26
     * @功能说明:编辑通知
     */
    public function noticeUpdate(){

        $input = $this->_input;

        $reminder_model = new \app\reminder\model\Config();

        $help_model     = new HelpConfig();

        $update = [
            //是否通知代理商
            'notice_agent' => $input['pay_order_config']['reminder']['notice_agent'],

            'reminder_admin_status' => $input['pay_order_config']['reminder']['reminder_admin_status'],

            'reminder_admin_phone' => $input['pay_order_config']['reminder']['reminder_admin_phone'],

            'notice_admin' => $input['pay_order_config']['reminder']['notice_admin'],

            'order_end_status' => $input['order_config']['reminder']['order_end_status'],
        ];

        $update['reminder_admin_phone'] = !empty($update['reminder_admin_phone'])?implode(',',$update['reminder_admin_phone']):'';

        $reminder_model->dataUpdate(['uniacid'=>$this->_uniacid],$update);

        $update = [
            //是否通知代理商
            'order_tmpl_agent_status' => $input['pay_order_config']['wechat']['order_tmpl_agent_status'],

            'order_tmpl_admin_status' => $input['pay_order_config']['wechat']['order_tmpl_admin_status'],

            'order_tmpl_text' => $input['pay_order_config']['wechat']['order_tmpl_text'],

            'order_tmpl_notice_admin' => $input['pay_order_config']['wechat']['order_tmpl_notice_admin'],
            //是否通知代理商
            'reminder_admin_status' => $input['help_config']['reminder']['reminder_admin_status'],

            'reminder_notice_phone' => $input['help_config']['reminder']['reminder_notice_phone'],

            'reminder_notice_admin' => $input['help_config']['reminder']['reminder_notice_admin'],

            'help_reminder_agent_status'=> $input['help_config']['reminder']['help_reminder_agent_status'],
            //是否通知代理商
            'short_admin_status' => $input['help_config']['short']['short_admin_status'],

            'help_phone' => $input['help_config']['short']['help_phone'],

            'short_notice_admin' => $input['help_config']['short']['short_notice_admin'],

            'help_short_agent_status' => $input['help_config']['short']['help_short_agent_status'],

            'tmpl_admin_status' => $input['help_config']['wechat']['tmpl_admin_status'],

            'help_user_id' => $input['help_config']['wechat']['help_user_id'],

            'tmpl_notice_admin' => $input['help_config']['wechat']['tmpl_notice_admin'],

            'help_wechat_agent_status' => $input['help_config']['wechat']['help_wechat_agent_status'],
        ];

        $update['reminder_notice_phone'] = !empty($update['reminder_notice_phone'])?implode(',',$update['reminder_notice_phone']):'';
        $update['help_phone']      = !empty($update['help_phone'])?implode(',',$update['help_phone']):'';
        $update['help_user_id']    = !empty($update['help_user_id'])?implode(',',$update['help_user_id']):'';
        $update['order_tmpl_text'] = !empty($update['order_tmpl_text'])?implode(',',$update['order_tmpl_text']):'';

        $help_model->dataUpdate(['uniacid'=>$this->_uniacid],$update);

        $update = [

            'wechat_tmpl' => $input['order_config']['wechat']['wechat_tmpl'],

            'order_wechat_agent_status' => $input['order_config']['wechat']['order_wechat_agent_status'],

            'wechat_tmpl_admin' => $input['order_config']['wechat']['wechat_tmpl_admin'],

            'order_wechat_admin_status' => $input['order_config']['wechat']['order_wechat_admin_status'],
        ];

        $update['wechat_tmpl_admin'] = !empty($update['wechat_tmpl_admin'])?implode(',',$update['wechat_tmpl_admin']):'';

        $config_model = new ConfigSetting();

        $config_model->dataUpdate($update,$this->_uniacid);

        return $this->success(true);
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-12 16:14
     * @功能说明:编辑配置 12.51 7.43
     */
    public function configUpdate(){

        $input = $this->_input;

        $dis = [

            'uniacid' => $this->_uniacid
        ];

        $dataPath = APP_PATH  . 'massage/info/ConfigSetting.php' ;

        $list =  include $dataPath ;

        $list = array_column($list,'key');

        if(isset($input['tencent_map_key'])&&is_array($input['tencent_map_key'])){

            $input['tencent_map_key'] = implode(',',$input['tencent_map_key']);

            setCache('map_key_key','',1,$this->_uniacid);
        }

        foreach ($input as $k=>$v){

            if(in_array($k,$list)){

                $arr[$k] = $v;

                unset($input[$k]);
            }
        }
        //免车费距离
        if(isset($arr['free_fare_distance'])&&isset($arr['free_fare_bear'])){

            $coach_model = new Coach();

            if($arr['free_fare_bear']==1){

                $coach_model->where(['uniacid'=>$this->_uniacid,'admin_id'=>0])->where('free_fare_distance','>',$arr['free_fare_distance'])->update(['free_fare_distance'=>$arr['free_fare_distance']]);
            }

            $coach_model->where(['uniacid'=>$this->_uniacid,'admin_id'=>0])->update(['free_fare_bear'=>$arr['free_fare_bear']]);
        }

        if(!empty($arr)){

            $config_model = new ConfigSetting();

            $config_model->dataUpdate($arr,$this->_uniacid);
        }
        //微信客服
        if(isset($input['wecom_staff'])){

            $input['wecom_staff'] = serialize($input['wecom_staff']);
        }

        $data = $this->model->dataUpdate($dis,$input);

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-12 16:15
     * @功能说明:banner列表
     */
    public function bannerList(){

        $input = $this->_param;

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['status','>',-1];

        $banner_model = new Banner();

        $data = $banner_model->dataList($dis,$input['limit']);

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-12 16:18
     * @功能说明:添加banner
     */
    public function bannerAdd(){

        $input = $this->_input;

        $input['uniacid'] = $this->_uniacid;

        $banner_model = new Banner();

        $res = $banner_model->dataAdd($input);

        return $this->success($res);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-12 16:20
     * @功能说明:编辑banner
     */
    public function bannerUpdate(){

        $input = $this->_input;

        $dis = [

            'id' => $input['id']
        ];

        $banner_model = new Banner();

        $res = $banner_model->dataUpdate($dis,$input);

        return $this->success($res);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-19 13:27
     * @功能说明:banner详情
     */
    public function bannerInfo(){

        $input = $this->_param;

        $dis = [

            'id' => $input['id']
        ];

        $banner_model = new Banner();

        $res = $banner_model->dataInfo($dis);

        $article_model = new ArticleList();

        $res['type_title'] = $article_model->where(['id'=>$res['type_id']])->value('title');

        return $this->success($res);
    }







    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-18 10:53
     * @功能说明:支付配置详情
     */
    public function payConfigInfo(){

        $dis = [

            'uniacid' => $this->_uniacid
        ];

        $pay_model = new PayConfig();

        $data = $pay_model->dataInfo($dis);

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-18 10:55
     * @功能说明:编辑支付配置
     */
    public function payConfigUpdate(){

        $input = $this->_input;

        $dis = [

            'uniacid' => $this->_uniacid
        ];

        $http = $_SERVER['HTTP_REFERER'].'attachment/';

        if(isset($input['cert_path'])&&isset($input['key_path'])){

            if(strstr($input['cert_path'],$http)){

                $input['cert_path'] = str_replace($http,FILE_UPLOAD_PATH,$input['cert_path']);

               // $input['cert_path'] = FILE_UPLOAD_PATH.$input['cert_path'];

            }

//            if(isset($member_info['attach_file'])){
//
//                $member_info['attach_file'] = realpath(str_replace($_SERVER['HTTP_HOST'].'/','',$member_info['attach_file']));
//            }

            if(strstr($input['key_path'],$http)){

                $input['key_path'] = str_replace($http,FILE_UPLOAD_PATH,$input['key_path']);

               // $input['key_path']  = FILE_UPLOAD_PATH.$input['key_path'];
            }
        }

        $pay_model = new PayConfig();

        $data = $pay_model->dataUpdate($dis,$input);

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-31 15:16
     * @功能说明:修改密码
     */
    public function updatePass(){

        $input = $this->_input;

        $admin = new \app\massage\model\Admin();

        $update = [

            'passwd'  => checkPass($input['pass']),
        ];
        if($this->_user['is_admin']!=1){

            $update['passwd_text'] = $input['pass'];

        }

        $res = $admin->dataUpdate(['id'=>$this->_user['id']],$update);
        //添加缓存数据
        clearCache(7777,$_SERVER['HTTP_HOST'].$this->_user['id']);

        SaasAuthConfig::getSAuthConfig($this->_uniacid);

        return $this->success($res);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-12 15:04
     * @功能说明:配置详情
     */
    public function msgConfigInfo(){

        $msg_model = new MsgConfig();

        $dis = [

            'uniacid' => $this->_uniacid
        ];

        $data  = $msg_model->dataInfo($dis);

        return $this->success($data);

    }






    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-12 16:14
     * @功能说明:编辑配置
     */
    public function msgConfigUpdate(){

        $input = $this->_input;

        $msg_model = new MsgConfig();

        $dis = [

            'uniacid' => $this->_uniacid
        ];

        $data  = $msg_model->dataUpdate($dis,$input);

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-05 23:09
     * @功能说明:评价标签列表
     */
    public function lableList(){

        $input = $this->_param;

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['status','>',-1];

        $lable_model = new Lable();

        $data = $lable_model->dataList($dis,$input['limit']);

        return $this->success($data);


    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-05 23:09
     * @功能说明:评价标签详情
     */
    public function lableInfo(){

        $input = $this->_param;

        $dis = [

            'id' => $input['id']
        ];

        $lable_model = new Lable();

        $data = $lable_model->dataInfo($dis);

        return $this->success($data);


    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-05 23:09
     * @功能说明:添加评价标签
     */
    public function lableAdd(){

        $input = $this->_input;

        $input['uniacid'] = $this->_uniacid;

        $lable_model = new Lable();

        $data = $lable_model->dataAdd($input);

        return $this->success($data);


    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-05 23:09
     * @功能说明:编辑评价标签
     */
    public function lableUpdate(){

        $input = $this->_input;

        $dis = [

            'id' => $input['id']
        ];


        $lable_model = new Lable();

        $data = $lable_model->dataUpdate($dis,$input);

        return $this->success($data);


    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-16 18:46
     * @功能说明:车费配置详情
     */
    public function carConfigInfo(){

        $car_model = new CarPrice();

        $city_model= new City();

        $dis = [

            'uniacid' => $this->_uniacid,

            'city_id' => 0
        ];

        $data = $car_model->dataInfo($dis);

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-16 18:46
     * @功能说明:车费配置详情
     */
    public function carConfigUpdate(){

        $input = $this->_input;

        $car_model = new CarPrice();

        $dis = [

            'uniacid' => $this->_uniacid,

            'city_id' => 0
        ];

        if(isset($input['cash_setting_day'])){

            $cash_setting_day = $input['cash_setting_day'];

            unset($input['cash_setting_day']);
        }

        if(isset($input['cash_setting_night'])){

            $cash_setting_night = $input['cash_setting_night'];

            unset($input['cash_setting_night']);
        }

        $id = $car_model->where($dis)->value('id');

        $data = $car_model->dataUpdate($dis,$input);

        $type_model = new CarCashType();

        if(!empty($cash_setting_day)){

            $type_model->updateConfigList($cash_setting_day,$id,1,$this->_uniacid);
        }

        if(!empty($cash_setting_night)){

            $type_model->updateConfigList($cash_setting_night,$id,2,$this->_uniacid);
        }

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-02 16:11
     * @功能说明:获取车费配置列表
     */

    public function getCarConfigList(){

        $input = $this->_param;

        $dis[] = ['a.uniacid','=',$this->_uniacid];

        $dis[] = ['a.city_id','>',0];

        $dis[] = ['a.status','>',-1];

        if(!empty($input['city_name'])){

            $dis[] = ['b.title','like','%'.$input['city_name'].'%'];
        }

        $car_model = new CarPrice();

        $city_model= new City();

        $data = $car_model->alias('a')
                ->join('massage_service_city_list b','a.city_id = b.id','left')
                ->where($dis)
                ->field('a.*')
                ->group('a.id')
                ->paginate($input['limit'])
                ->toArray();

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $city_type = $city_model->where(['id'=>$v['city_id']])->value('city_type');

                if($city_type!=1){

                    $city_id = $city_model->where(['id'=>$v['city_id']])->value('pid');

                    $v['city_id'] = [$city_id,$v['city_id']];
                }else{

                    $v['city_id'] = [$v['city_id']];
                }
            }
        }

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-02 16:11
     * @功能说明:获取车费配置列表
     */
    public function getCarConfigAdd(){

        $input = $this->_param;

        $dis = [

            'uniacid' => $this->_uniacid,

            'city_id' => $input['city_id']
        ];

        $car_model = new CarPrice();

        $find = $car_model->where($dis)->where('status','>',-1)->find();

        if(!empty($find)){

            $this->errorMsg('该城市已有车费设置');
        }

        $input['uniacid'] = $this->_uniacid;

        if(isset($input['cash_setting_day'])){

            $cash_setting_day = $input['cash_setting_day'];

            unset($input['cash_setting_day']);
        }

        if(isset($input['cash_setting_night'])){

            $cash_setting_night = $input['cash_setting_night'];

            unset($input['cash_setting_night']);
        }

        $data = $car_model->dataAdd($input);

        $id   = $car_model->getLastInsID();

        $type_model = new CarCashType();

        if(!empty($cash_setting_day)){

            $type_model->updateConfigList($cash_setting_day,$id,1,$this->_uniacid);
        }

        if(!empty($cash_setting_night)){

            $type_model->updateConfigList($cash_setting_night,$id,2,$this->_uniacid);
        }

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-02 16:11
     * @功能说明:获取车费配置列表
     */
    public function getCarConfigInfo(){

        $input = $this->_param;

        $dis = [

            'id' => $input['id']
        ];

        $car_model = new CarPrice();

        $data = $car_model->where($dis)->find();

        $city_model = new City();

        $city_type = $city_model->where(['id'=>$data['city_id']])->value('city_type');

        if($city_type!=1){

            $city_id = $city_model->where(['id'=>$data['city_id']])->value('pid');

            $data['city_id'] = [$city_id,$data['city_id']];
        }else{

            $data['city_id'] = [$data['city_id']];
        }

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-02 16:11
     * @功能说明:获取车费配置编辑
     */
    public function getCarConfigUpdate(){

        $input = $this->_param;

        $car_model = new CarPrice();

        if(!empty($input['city_id'])){

            $dis = [

                'uniacid' => $this->_uniacid,

                'city_id' => $input['city_id']
            ];

            $find = $car_model->where($dis)->where('id','<>',$input['id'])->where('status','>',-1)->find();

            if(!empty($find)){

                $this->errorMsg('该城市已有车费设置');
            }
        }

        $dis = [

            'id' => $input['id']
        ];

        if(isset($input['cash_setting_day'])){

            $cash_setting_day = $input['cash_setting_day'];

            unset($input['cash_setting_day']);
        }

        if(isset($input['cash_setting_night'])){

            $cash_setting_night = $input['cash_setting_night'];

            unset($input['cash_setting_night']);
        }

        $find = $car_model->dataUpdate($dis,$input);

        $type_model = new CarCashType();

        $id = $input['id'];

        if(!empty($cash_setting_day)){

            $type_model->updateConfigList($cash_setting_day,$id,1,$this->_uniacid);
        }

        if(!empty($cash_setting_night)){

            $type_model->updateConfigList($cash_setting_night,$id,2,$this->_uniacid);
        }

        return $this->success($find);

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-02 16:11
     * @功能说明:获取车费配置编辑
     */
    public function getCarConfigDel(){

        $input = $this->_param;

        $dis = [

            'id' => $input['id']
        ];

        $car_model = new CarPrice();

        $find = $car_model->where($dis)->update(['status'=>-1]);

        return $this->success($find);

    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-06-08 17:17
     * @功能说明:加盟商列表
     */
    public function adminList(){

        $input = $this->_param;

        $dis[] = ['a.status','>',-1];

        $dis[] = ['a.uniacid','=',$this->_uniacid];

        $dis[] = ['a.is_admin','=',0];

        if($this->_user['is_admin']==0){

            $dis[] = ['a.id','in',$this->admin_arr];
        }

        if(!empty($input['city_id'])){

            $dis[] = ['a.city_id','in',$input['city_id']];
        }

        if(!empty($input['username'])){

            $dis[] = ['a.username','like','%'.$input['username'].'%'];
        }

        if(!empty($input['agent_name'])){

            $dis[] = ['a.agent_name','like','%'.$input['agent_name'].'%'];
        }

        if(!empty($input['nickName'])){

            $dis[] = ['b.nickName','like','%'.$input['nickName'].'%'];
        }

        if(!empty($input['id'])){

            $dis[] = ['a.id','<>',$input['id']];
        }

        if(isset($input['channel_auth'])){

            $dis[] = ['a.channel_auth','=',$input['channel_auth']];
        }

        if(isset($input['salesman_auth'])){

            $dis[] = ['a.salesman_auth','=',$input['salesman_auth']];
        }

        if(isset($input['sub_agent_auth'])){

            $dis[] = ['a.sub_agent_auth','=',$input['sub_agent_auth']];
        }

        if(!empty($input['city_type'])){

            $dis[] = ['a.city_type','=',$input['city_type']];
        }

        $data = $this->admin_model->adminUserList($dis,$input['limit']);

        if(!empty($data['data'])){

            $city_model = new City();

            foreach ($data['data'] as &$v){

                $v = $city_model->cityData($v);
            }
        }

        return $this->success($data);
    }






    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-06-08 17:31
     * @功能说明:添加加盟商
     */
    public function adminAdd(){

        $input = $this->_input;

        $input['uniacid'] = $this->_uniacid;

        $input['is_admin'] = 0;

        $check = $this->admin_model->jionAdminCheck($input);

        if(!empty($check['code'])){

            $this->errorMsg($check['msg']);
        }

        $input['passwd']  = checkPass($input['passwd_text']);

        $res = $this->admin_model->dataAdd($input);

        $id  = $this->admin_model->getLastInsID();

        AdminWater::initWater($this->_uniacid,$id);

        return $this->success($res);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-06-22 21:30
     * @功能说明:加盟商下拉框
     */
    public function adminSelect(){

        $input = $this->_param;

        $dis = [

            'is_admin' => 0,

            'status'   => 1,

            'uniacid'  => $this->_uniacid
        ];

        if(isset($input['reseller_auth'])){

            $dis['reseller_auth'] = $input['reseller_auth'];
        }

        if(isset($input['store_auth'])){

            $dis['store_auth'] = $input['store_auth'];
        }

        $where = [];

        if($this->_user['is_admin']==0){

            $where[] = ['id','in',$this->admin_arr];
        }

        if(!empty($input['admin_id'])){

            $dis['id'] = $input['admin_id'];
        }

        if(isset($input['channel_auth'])){

            $where[] = ['channel_auth','=',$input['channel_auth']];
        }

        if(isset($input['salesman_auth'])){

            $where[] = ['salesman_auth','=',$input['salesman_auth']];
        }

        if(isset($input['agent_coach_auth'])){

            $where[] = ['agent_coach_auth','=',$input['agent_coach_auth']];
        }

        $data = $this->admin_model->where($dis)->where($where)->field('id,username,agent_name')->select()->toArray();

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-06-08 22:45
     * @功能说明:编辑加盟商
     */

    public function adminUpdate(){

        $input = $this->_input;

        $input['uniacid'] = $this->_uniacid;

        if(isset($input['cash'])){

            unset($input['cash']);
        }

        $check = $this->admin_model->jionAdminCheck($input);

        if(!empty($check['code'])){

            $this->errorMsg($check['msg']);
        }

        $dis = [

            'id' => $input['id']
        ];

        $admin_info = $this->admin_model->dataInfo($dis);

        if($admin_info['passwd_text']!=$input['passwd_text']||$input['username']!=$admin_info['username']){
            //添加缓存数据
            clearCache(7777,$_SERVER['HTTP_HOST'].$input['id']);
        }

        if(!empty($input['passwd_text'])){

            $input['passwd'] = checkPass($input['passwd_text']);
        }

        $res = $this->admin_model->dataUpdate($dis,$input);

        if(isset($input['city_type'])){
            //修改不合格的上下级
            $admin_info = $this->admin_model->dataInfo($dis);

            $top = $this->admin_model->dataInfo(['id'=>$admin_info['admin_pid']]);
            //市
            if($admin_info['city_type']==1){
                //清空下级
                $this->admin_model->where(['admin_pid'=>$input['id']])->where('city_type','in',[1,3])->update(['admin_pid'=>0]);
                //清空上级
                if(!empty($top)&&in_array($top['city_type'],[1,2])){

                    $this->admin_model->dataUpdate($dis,['admin_pid'=>0]);
                }

            }elseif ($admin_info['city_type']==3){
                //清空下级
                $this->admin_model->where(['admin_pid'=>$input['id']])->where('city_type','in',[3])->update(['admin_pid'=>0]);
                //清空上级
              //  if(!empty($top)&&in_array($top['city_type'],[1,2])){

                    $this->admin_model->dataUpdate($dis,['admin_pid'=>0]);
               // }

            }else{
                //清空下级
                $this->admin_model->where(['admin_pid'=>$input['id']])->update(['admin_pid'=>0]);
                //清空上级
                if(!empty($top)&&in_array($top['city_type'],[2])){

                    $this->admin_model->dataUpdate($dis,['admin_pid'=>0]);
                }
            }
        }

        clearCache($this->_uniacid,'city_key');

        return $this->success($res);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-06-08 22:49
     * @功能说明:修改状态
     */
    public function adminStatusUpdate(){

        $input = $this->_input;

        $diss = [

            'id' => $input['id']
        ];

        $admin = $this->admin_model->dataInfo($diss);
        //删除代理商
        if($input['status']==-1&&!empty($input['id'])){

            if($admin['cash']>0){

                $this->errorMsg('还有佣金未提现');
            }

            $dis = [

                'top_id'  => $input['id'],

                'status'  => 1,
            ];

            $cash_model = new Commission();

            $cash = $cash_model->where($dis)->where('type','in',[2,5,6])->sum('cash');

            if(!empty($cash)){

                $this->errorMsg('还有佣金未到账');

            }

            $dis = [

                'user_id' => $input['id'],

                'status'  => 1,

                'type'    => 3
            ];

            $wallet_model = new \app\massage\model\Wallet();

            $wallet = $wallet_model->dataInfo($dis);

            if(!empty($wallet)){

                $this->errorMsg('还有提现未处理');
            }

            $this->admin_model->dataUpdate(['admin_pid'=>$input['id']],['admin_pid'=>0]);

            $store_model = new StoreList();
            //将所有关联该代理商的门店的代理商清空
            $store_model->dataUpdate(['admin_id'=>$input['id']],['admin_id'=>0]);

            $coach_model = new Coach();

            $free_fare_distance = getConfigSetting($this->_uniacid,'free_fare_distance');

            $free_fare_bear     = getConfigSetting($this->_uniacid,'free_fare_bear');

            $coach_model->where(['admin_id'=>$input['id']])->where('free_fare_distance','>',$free_fare_distance)->update(['free_fare_distance'=>$free_fare_distance]);

            $coach_model->where(['admin_id'=>$input['id']])->update(['free_fare_bear'=>$free_fare_bear]);
            //删除技师的代理商
            $coach_model->dataUpdate(['admin_id'=>$input['id']],['admin_id'=>0]);

            $service_model = new Service();
            //将该代理商的所有服务删除
            $service_model->dataUpdate(['admin_id'=>$input['id'],'type'=>2],['status'=>-1]);
            //删除手机上代理权限
            $role_model  = new RoleAdmin();

            $role_model->dataUpdate(['admin_id'=>$input['id']],['status'=>-1]);

            $channel_model = new ChannelList();
            //删除渠道商代理商
            $channel_model->dataUpdate(['admin_id'=>$input['id']],['admin_id'=>0]);

            $channe_qr_mdoel = new ChannelQr();
            //渠道码删除代理商
            $channe_qr_mdoel->dataUpdate(['admin_id'=>$input['id']],['admin_id'=>0]);

            $salesman_model = new Salesman();
            //业务员
            $salesman_model->dataUpdate(['admin_id'=>$input['id']],['admin_id'=>0]);

            $reseller_model = new DistributionList();

            $reseller_model->dataUpdate(['admin_id'=>$input['id']],['admin_id'=>0]);

            clearCache(7777,$_SERVER['HTTP_HOST'].$input['id']);

        }

        $res = $this->admin_model->dataUpdate($diss,['status'=>$input['status']]);

        return $this->success($res);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-06-08 22:50
     * @功能说明:加盟商详情
     */
    public function adminInfo(){

        $input = $this->_param;

        $dis = [

            'id' => $input['id']
        ];

        if($this->_user['is_admin']==0&&!in_array($input['id'],$this->admin_arr)){

            $this->errorMsg('错误');
        }

        $res = $this->admin_model->dataInfo($dis);

        $user_model = new User();

        $city_model = new City();

        $res['nickName'] = $user_model->where(['id'=>$res['user_id']])->value('nickName');

        $res = $city_model->cityData($res);

        return $this->success($res);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-06-08 23:33
     * @功能说明:用户下拉框
     */
    public function userSelect1(){

        $input = $this->_param;

        $user_model = new User();

        $dis[] = ['uniacid','=',$this->_uniacid];

        if(empty($input['nickName'])){

            return $this->success([]);

        }

        $where[] = ['nickName','like','%'.$input['nickName'].'%'];

        $where[] = ['id','=',$input['nickName']];

        $res = $user_model->where($dis)->where(function ($query) use ($where){
            $query->whereOr($where);
        })->field('id,nickName')->order('id desc')->select()->toArray();

        if(!empty($res)){

            foreach ($res as &$v){

                $v['nickName'] = $v['nickName'].'(ID:'.$v['id'].')';
            }
        }

        return $this->success($res);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-09-22 15:19
     * @功能说明:团长用户列表
     */
    public function userSelect(){

        $input = $this->_param;

        $where1 = [];

        if(!empty($input['nickName'])){

            $where1[] = ['nickName','like','%'.$input['nickName'].'%'];

            $where1[] = ['phone','like','%'.$input['nickName'].'%'];
        }

        $user_model = new User();

        $where[] = ['uniacid','=',$this->_uniacid];

        $where[] = ['status', '=', 1];

        $list = $user_model->dataList($where,$input['limit'],$where1);

        return $this->success($list);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-06-16 09:56
     * @功能说明:城市列表
     */
    public function cityList(){

        $input = $this->_param;

        $city_model = new City();

        $city_model->provinceInit($this->_uniacid);

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['status','>',-1];

        $dis[] = ['city_type','=',3];

        if(!empty($input['name'])){

            $dis[] = ['title','like','%'.$input['name'].'%'];
        }

        $data = $city_model->dataList($dis,$input['limit']);

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $v['children'] = $city_model->where(['pid'=>$v['id']])->where('status','>',-1)->select()->toArray();

                if(!empty($v['children'])){

                    foreach ($v['children'] as &$v){

                        $v['children'] = $city_model->where(['pid'=>$v['id']])->where('status','>',-1)->select()->toArray();
                    }
                }
            }
        }

        return $this->success($data);
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-06-16 09:58
     * @功能说明:添加城市
     */
    public function cityAdd(){

        $input = $this->_input;

        $input['uniacid'] = $this->_uniacid;

        $city_model = new City();

        $res = $city_model->checkCity($input);

        if(!empty($res['code'])){

            $this->errorMsg($res['msg']);
        }

        if(!empty($input['is_hot'])){

            $count = $city_model->where(['is_hot'=>1])->where('status','>',-1)->count();

            if($count>=8){

                $this->errorMsg('热门城市最多8个');
            }
        }

        $input['true_name'] = $input['title'];

        $res = $city_model->dataAdd($input);

        clearCache($this->_uniacid,'city_key');

        return $this->success($res);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-06-16 09:59
     * @功能说明:城市详情
     */
    public function cityInfo(){

        $input = $this->_param;

        $dis=[

            'id' => $input['id']
        ];

        $city_model = new City();

        $res = $city_model->dataInfo($dis);

        return $this->success($res);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-06-16 10:37
     * @功能说明:编辑城市
     */
    public function cityUpdate(){

        $input = $this->_input;

        $dis=[

            'id' => $input['id']
        ];

        $input['uniacid'] = $this->_uniacid;

        $city_model = new City();

        if(!empty($input['title'])){

            $res = $city_model->checkCity($input);

            if(!empty($res['code'])){

                $this->errorMsg($res['msg']);
            }
        }

        if(isset($input['city_type'])&&$input['city_type']==2&&$input['is_city']==0){

            $input['is_hot'] = 0;
        }

        if(!empty($input['is_hot'])){

            $count = $city_model->where(['is_hot'=>1])->where('status','>',-1)->where('id','<>',$input['id'])->count();

            if($count>=8){

                $this->errorMsg('热门城市最多8个');
            }
        }
        //删除的时候
        if(isset($input['status'])&&$input['status']==-1){

            $data = $city_model->dataInfo($dis);
            //删除省需要删除下面的市
            if($data['city_type']==3){

                $find = $city_model->dataInfo(['pid'=>$data['id'],'status'=>1]);

                if(!empty($find)){

                    $this->errorMsg('请删除下面的市');
                }
            }
            //删除市需要直接删除下面的区
            if($data['city_type']==1){

                $city_model->dataUpdate(['pid'=>$data['id']],['status'=>-1]);
            }
        }

        $res = $city_model->dataUpdate($dis,$input);

        clearCache($this->_uniacid,'city_key');

        return $this->success($res);
    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-06-16 09:56
     * @功能说明:城市列表
     */
    public function citySelect(){

        $input = $this->_param;

        $city_type = !empty($input['city_type'])?$input['city_type']:1;

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['status','=',1];

        $dis[] = ['city_type','=',$city_type];

        if(!empty($input['name'])){

            $dis[] = ['title','like','%'.$input['name'].'%'];
        }

        $city_model = new City();

        $data = $city_model->where($dis)->select()->toArray();

        if(!empty($data)){

            foreach ($data as &$v){

                $v['children'] = $city_model->where(['pid'=>$v['id']])->where('status','>',-1)->select()->toArray();

                if(!empty($v['children'])){

                    foreach ($v['children'] as &$vs){

                        $vs['children'] = $city_model->where(['pid'=>$vs['id']])->where('status','>',-1)->select()->toArray();
                    }
                }
            }
        }
        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-08-12 10:34
     * @功能说明:获取授权
     */
    public function getSaasAuth(){

        $data = AdminMenu::getAuthList($this->_uniacid);

          $data['payreseller'] = 1;
          $data['channelforeverbind'] = 0;
          $data['wechat'] = 1;
          $data['recommend']=1;
          $data['virtual']=1;
          $data['reminder']=1;
          $data['employ']=1;
          $data['app'] = 1;
          $data['h5'] = 1;
          $data['store'] = 1;
          $data['fdd'] = 1;
          $data['recording'] = 1;
          $data['node'] = 999999;
          $data['mobilenode'] = 99999;
          $data['heepay'] = 0;
          $data['adapay'] = 1;
          $data['dynamic']=1;
          $data['virtual']=1;
          $data['map']=1;
          $data['member']=1;
          $data['coachtravel'] = 1;
          $data['caradmin'] = 1;
          $data['coachcredit'] = 1;
          $data['channelcate'] = 1;
          $data['coachport'] = 1;
          $data['abnormalorder'] = 1;
          $data['coachbroker'] = 1;
          $data['orderradar'] = 1;
          $data['agentcoach'] = 1;
          $data['subagent'] = 1;
          $data['agentcoachcheck'] = 1;
          $data['materialshop'] = 1;
          $data['couponatv'] = 1;
          $data['addclockcashsetting'] = 1;
          $data['channelqrcount'] = 1;
          $data['coupondiscountrule'] = 1;  
          $data['recommendcash'] = 1;  
          $data['adminuser'] = 1;  
          $data['agentservice'] = 1;  
          $data['industrytype'] = 1;  
          $data['balancediscount'] = 1;  
          $data['memberdiscount'] = 1;  
          $data['baiying'] = 1;  
          $data['balancediscount'] = 1;  
          $data['memberdiscount'] = 1;  
          $data['fxq'] = 1;
	  $data['city'] = 999999;
          $data['reseller'] = 1;
          $data['channel'] = 1;
          $data['salesman'] = 1;
          $data['map'] = 1;
	  $data['package'] = 1;
	  $data['partner'] = 1;
	  $data['map'] = 1;
	  
        //代理商需要通过是否绑定门店判断门店权限
//        if(!empty($data['store'])&&$data['store']==true&&$this->_user['is_admin']==0){
//
//            $store_model = new StoreList();
//
//            $find = $store_model->where(['status'=>1,'admin_id'=>$this->_user['admin_id']])->find();
//
//            if(empty($find)){
//
//                $data['store'] = false;
//            }
//        }

        if(isset($this->_user['is_admin'])&&$this->_user['is_admin']==0){

            $admin_model = new \app\massage\model\Admin();

            $admin = $admin_model->dataInfo(['id'=>$this->_user['admin_id']]);

            if(!empty($admin)){

                $data['channel_auth'] = $admin['channel_auth'];
                $data['salesman_auth'] = $admin['salesman_auth'];
                $data['coach_check_auth']= $admin['coach_check_auth'];
                $data['wallet_check_auth']= $admin['wallet_check_auth'];
                $data['coupon_auth']  = $admin['coupon_auth'];
                $data['reseller_auth']= $admin['reseller_auth'];
                $data['partner_auth'] = $admin['partner_auth'];
                $data['coach_cash_auth'] = $admin['coach_cash_auth'];
                $data['offline_transfer_auth'] = $admin['offline_transfer_auth'];
                $data['wechat_transfer_auth'] = $admin['wechat_transfer_auth'];
                $data['alipay_transfer_auth'] = $admin['alipay_transfer_auth'];
                $data['bank_transfer_auth'] = $admin['bank_transfer_auth'];
                $data['recommend_cash_auth'] = $admin['recommend_cash_auth'];
                $data['sub_agent_auth'] = $admin['sub_agent_auth'];
                $data['agent_coach_auth'] = $admin['agent_coach_auth'];
                $data['delegated_coach'] = $admin['delegated_coach'];
                $data['store_auth'] = $admin['store_auth'];
                $data['user_auth'] = $admin['user_auth'];
                $data['store_package_auth'] = $admin['store_package_auth'];
                $data['group_write_off_auth'] = $admin['group_write_off_auth'];
                $data['hotel_auth'] = $admin['hotel_auth'];
                if(isset($data['agentcoach'])&&$data['agentcoach']==false){

                    $data['agent_coach_auth'] = $data['coach_check_auth'] = $data['coupon_auth'] = $data['partner_auth'] =  $data['agentservice'] =  0;
                }

                if(isset($data['agentcoachcheck'])&&$data['agentcoachcheck']==false){

                    $data['coach_check_auth'] = 0;
                }
            }
        }


        return $this->success($data);
    }

    /**
     * 反馈列表
     * @return \think\Response
     */
    public function feedbackList()
    {
        $input = $this->request->param();
        $limit = $this->request->param('limit',10);
        $where = [];
        if (isset($input['status']) && in_array($input['status'], [1, 2])) {
            $where[] = ['a.status', '=', $input['status']];
        }
        $where[] = ['a.uniacid', '=', $this->_uniacid];

        if(!empty($input['type'])){

            $where[] = ['a.type', '=', $input['type']];
        }
        $data = Feedback::getList($where,$limit);
        $data['status1'] = Feedback::where(['uniacid' => $this->_uniacid, 'status' => 1])->count();
        $data['status2'] = Feedback::where(['uniacid' => $this->_uniacid, 'status' => 2])->count();
        return $this->success($data);
    }


    /**
     * 详情
     * @return \think\Response
     */
    public function feedbackInfo()
    {
        $id = $this->request->param('id');
        if (empty($id)) {
            return $this->error('参数错误');
        }
        $data = Feedback::getInfo(['a.id' => $id]);
        return $this->success($data);
    }

    /**
     * 处理反馈
     * @return \think\Response
     */
    public function feedbackHandle()
    {
        $id = $this->request->param('id');
        $reply_content = $this->request->param('reply_content','');
        $reply_content = html_entity_decode($reply_content);
        if (empty($id)) {
            return $this->error('参数错误');
        }
        $res = Feedback::update(['status'=>2,'reply_content'=>$reply_content,'reply_date'=>date('Y-m-d H:i:s')],['id'=>$id]);
        if ($res===false){
            return $this->error('处理失败');
        }
        return $this->success('');
    }


    /**
     * 反馈列表
     * @return \think\Response
     */
    public function appealList()
    {
        $input = $this->request->param();
        $limit = $this->request->param('limit',10);
        $where = [];
        if (isset($input['status']) && in_array($input['status'], [1, 2])) {
            $where[] = ['a.status', '=', $input['status']];
        }
        $where[] = ['a.uniacid', '=', $this->_uniacid];
        $data = Appeal::getList($where,$limit);
        $data['status1'] = Appeal::where(['uniacid' => $this->_uniacid, 'status' => 1])->count();
        $data['status2'] = Appeal::where(['uniacid' => $this->_uniacid, 'status' => 2])->count();
        return $this->success($data);
    }

    /**
     * 详情
     * @return \think\Response
     */
    public function appealInfo()
    {
        $id = $this->request->param('id');
        if (empty($id)) {
            return $this->error('参数错误');
        }
        $data = Appeal::getInfo(['a.id' => $id]);


        return $this->success($data);
    }

    /**
     * 处理反馈
     * @return \think\Response
     */
    public function appealHandle()
    {
        $id = $this->request->param('id');
        $reply_content = $this->request->param('reply_content','');
        if (empty($id)) {
            return $this->error('参数错误');
        }
        $res = Appeal::update(['status'=>2,'reply_content'=>$reply_content,'reply_date'=>date('Y-m-d H:i:s')],['id'=>$id]);
        if ($res===false){
            return $this->error('处理失败');
        }
        return $this->success('');
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-10-24 14:53
     * @功能说明:用户标签
     */
    public function userLabelList(){

        $input = $this->_param;

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['status','>',-1];

        $label_model = new UserLabelList();

        $data = $label_model->dataList($dis,$input['limit']);

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-10-24 14:53
     * @功能说明:添加用户标签
     */
    public function userLabelAdd(){

        $input = $this->_input;

        $input['uniacid'] = $this->_uniacid;

        $label_model = new UserLabelList();

        $data = $label_model->dataAdd($input);

        return $this->success($data);
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-10-24 14:53
     * @功能说明:添加用户标签
     */
    public function userLabelInfo(){

        $input = $this->_param;

        $dis = [

            'id' => $input['id']
        ];

        $label_model = new UserLabelList();

        $data = $label_model->dataInfo($dis);

        return $this->success($data);
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-10-24 14:53
     * @功能说明:添加用户标签
     */
    public function userLabelUpdate(){

        $input = $this->_input;

        $dis = [

            'id' => $input['id']
        ];

        $label_model = new UserLabelList();

        $data = $label_model->dataUpdate($dis,$input);

        return $this->success($data);
    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-12 15:04
     * @功能说明:配置详情
     */
    public function helpConfigInfo(){

        $dis = [

            'uniacid' => $this->_uniacid
        ];

        $config = new HelpConfig();

        $data  = $config->dataInfo($dis);

        $user_model = new User();

        $data['help_user_id'] = $user_model->where('id','in',$data['help_user_id'])->field('id,nickName,avatarUrl,phone')->select()->toArray();

        $data['order_tmpl_text'] = $user_model->where('id','in',$data['order_tmpl_text'])->field('id,nickName,avatarUrl,phone')->select()->toArray();

        return $this->success($data);


    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-12 16:14
     * @功能说明:编辑配置
     */
    public function helpConfigUpate(){

        $input = $this->_input;

        $dis = [

            'uniacid' => $this->_uniacid
        ];

        $config = new HelpConfig();

//        $input['help_user_id'] = !empty($input['help_user_id'])?implode(',',$input['help_user_id']):'';
//
//        $input['order_tmpl_text'] = !empty($input['order_tmpl_text'])?implode(',',$input['order_tmpl_text']):'';
//
//        $input['help_phone'] = !empty($input['help_phone'])?implode(',',$input['help_phone']):'';
//
//        $input['reminder_notice_phone'] = !empty($input['reminder_notice_phone'])?implode(',',$input['reminder_notice_phone']):'';

        $data = $config->dataUpdate($dis,$input);

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-12 15:04
     * @功能说明:配置详情
     */
    public function configInfoSchedule(){

        $dis = [

            'uniacid' => $this->_uniacid
        ];

        $config_model = new MassageConfig();

        $data  = $config_model->dataInfo($dis);

        $config_model = new ConfigSetting();

        $arr = $config_model->dataInfo($this->_uniacid,['recharge_entrance','balance_discount_entrance','recharge_status','comm_coach_balance','balance_discount_status','balance_discount_cash','balance_discount_coach_balance','balance_discount_admin_balance','balance_discount_balance','balance_discount_integral']);

        $data = array_merge($data,$arr);

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-12 16:14
     * @功能说明:编辑配置
     */
    public function configUpdateSchedule(){

        $input = $this->_input;

        $dis = [

            'uniacid' => $this->_uniacid
        ];

        $dataPath = APP_PATH  . 'massage/info/ConfigSetting.php' ;

        $list =  include $dataPath ;

        $list = array_column($list,'key');

        foreach ($input as $k=>$v){

            if(in_array($k,$list)){

                $arr[$k] = $v;

                unset($input[$k]);
            }

        }

        if(!empty($arr)){

            $config_model = new ConfigSetting();

            $config_model->dataUpdate($arr,$this->_uniacid);
        }

        $config_model = new MassageConfig();

        $data  = $config_model->dataUpdate($dis,$input);

        return $this->success($data);

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-16 18:46
     * @功能说明:模版消息配置详情
     */
    public function sendMsgConfigInfo(){

        $config_model = new SendMsgConfig();

        $config_model->initData($this->_uniacid);

        $dis = [

            'uniacid' => $this->_uniacid,
        ];

        $data = $config_model->dataInfo($dis);

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-16 18:46
     * @功能说明:模版消息配置编辑
     */
    public function sendMsgConfigUpdate(){

        $input = $this->_input;

        $config_model = new SendMsgConfig();

        $dis = [

            'uniacid' => $this->_uniacid,
        ];

        $data = $config_model->dataUpdate($dis,$input);

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-16 18:46
     * @功能说明:短信配置详情
     */
    public function shortCodeConfigInfo(){

        $config_model = new ShortCodeConfig();

        $config_model->initData($this->_uniacid);

        $dis = [

            'uniacid' => $this->_uniacid,

        ];

        $data = $config_model->dataInfo($dis);

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-16 18:46
     * @功能说明:模版消息配置编辑
     */
    public function shortCodeConfigUpdate(){

        $input = $this->_input;

        $config_model = new ShortCodeConfig();

        $dis = [

            'uniacid' => $this->_uniacid,
        ];

        $data = $config_model->dataUpdate($dis,$input);

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-16 17:56
     * @功能说明:获取加钟配置详情
     */
    public function addClockInfo(){

        $input = $this->_param;

        $config_model = new MassageConfig();
        //代理商
        if($this->_user['is_admin']==0){

            $admin_model = new \app\massage\model\Admin();

            $config= $admin_model->dataInfo(['id'=>$this->_user['admin_id']]);

            $dis = [

                'uniacid' => $this->_uniacid,

                'admin_id'=> $this->_user['admin_id'],
            ];

        }else{
            //平台
            $config = $config_model->dataInfo(['uniacid'=>$this->_uniacid]);

            $dis = [

                'uniacid' => $this->_uniacid,

                'admin_id'=> 0
            ];
        }

        $clock_model = new ClockSetting();

        $dis['type'] = 0;

        $arr['list'] = $clock_model->where($dis)->order('times,id')->select()->toArray();

        $dis['type'] = 1;

        $arr['level_list'] = $clock_model->where($dis)->order('times,id')->select()->toArray();

        $arr['clock_cash_status'] = $config['clock_cash_status'];

        $arr['clock_cash_type'] = $config['clock_cash_type'];

        $addclockBalance_model = new AddClockBalance();

        $admin_id = $this->_user['is_admin']==0?$this->_user['id']:0;

        $addclockBalance_model->initData($this->_uniacid,$admin_id);

        $arr['addclock_balance'] = $addclockBalance_model->where(['uniacid'=>$this->_uniacid,'admin_id'=>$admin_id])->select()->toArray();

        return $this->success($arr);
    }




    public function getCoachClockInfo(){

        $input = $this->_param;

        $config_model = new MassageConfig();

        if(!empty($input['admin_id'])){

            $admin_model = new \app\massage\model\Admin();

            $config= $admin_model->where(['id'=>$input['admin_id']])->field('clock_cash_status,clock_cash_type')->find();

        }else{
            //平台
            $config = $config_model->where(['uniacid'=>$this->_uniacid])->field('clock_cash_status,clock_cash_type')->find();
        }

        return $this->success($config);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-16 18:02
     * @功能说明:编辑加钟配置
     */
    public function addClockUpdate(){

        $input = $this->_input;

        $config_model = new MassageConfig();

        $clock_cash_type = !empty($input['clock_cash_type'])?$input['clock_cash_type']:0;
        //代理商
        if($this->_user['is_admin']==0){

            $admin_model = new \app\massage\model\Admin();

            $admin_model->dataUpdate(['id'=>$this->_user['admin_id']],['clock_cash_status'=>$input['clock_cash_status'],'clock_cash_type'=>$clock_cash_type]);

            $admin_id = $this->_user['admin_id'];

        }else{

            $config_model->dataUpdate(['uniacid'=>$this->_uniacid],['clock_cash_status'=>$input['clock_cash_status'],'clock_cash_type'=>$clock_cash_type]);

            $admin_id = 0;
        }

        $clock_model = new ClockSetting();

        $clock_model->where(['uniacid'=>$this->_uniacid,'admin_id'=> $admin_id,'type'=>$clock_cash_type])->delete();

        if(!empty($input['list'])){

            foreach ($input['list'] as $k=>$value){

                $insert[$k] = [

                    'uniacid' => $this->_uniacid,

                    'times'   => $value['times'],

                    'balance' => $value['balance'],

                    'type'    => $clock_cash_type,

                    'admin_id'=> $admin_id
                ];

            }
            $clock_model->saveAll($insert);
        }

        if(!empty($input['addclock_balance'])){

            $addclockBalance_model = new AddClockBalance();

            $addclockBalance_model->saveAll($input['addclock_balance']);
        }

        return $this->success(true);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-20 11:18
     * @功能说明:省份列表
     */
    public function provinceList(){

        $input = $this->_param;

        $city_model = new City();

        $dis = [

            'uniacid' => $this->_uniacid,

            'status'  => 1
        ];

        $list = $city_model->where($dis)->group('province')->order('id desc')->column('province');

        return $this->success($list);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-23 13:53
     * @功能说明:配置详情
     */
    public function configSettingInfo(){

        $config_model = new ConfigSetting();

        $data = $config_model->dataInfo($this->_uniacid);

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-23 13:54
     * @功能说明:编辑配置
     */
    public function configSettingUpdate(){

        $input = $this->_input;

        $config_model = new ConfigSetting();

        $data = $config_model->dataUpdate($input,$this->_uniacid);

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-30 15:54
     * @功能说明:佣金自定义配置详情
     */
    public function distributionConfigInfo(){

        $config_model = new DistributionConfig();

        $config_model->initData($this->_uniacid);

        $config = $config_model->where(['uniacid'=>$this->_uniacid])->order('top,id desc')->select()->toArray();

        if(!empty($config)){

            $level_model = new CoachLevel();

            foreach ($config as &$value){

                if($value['name']=='getCoachCash'){

                    $dis = [

                        'status' => 1,

                        'uniacid'=> $this->_uniacid,
                    ];

                    $value['balance'] = $level_model->where($dis)->max('balance');

                }
            }
        }

        return $this->success($config);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-30 15:54
     * @功能说明:佣金自定义配置编辑
     */
    public function distributionConfigUpdate(){

        $input = $this->_input;

        $config_model = new DistributionConfig();

        foreach ($input['data'] as $v){

            $config_model->dataUpdate(['id'=>$v['id']],['balance'=>$v['balance'],'top'=>$v['top']]);
        }

        return $this->success(true);
    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-09 15:28
     * @功能说明:申请代理商合伙人
     */
    public function agentApplyList(){

        $input = $this->_param;

        $agent_model = new AgentApply();

        $dis[] = ['a.uniacid','=',$this->_uniacid];

        if(isset($input['status'])){

            $dis[] = ['a.status','=',$input['status']];
        }else{

            $dis[] = ['a.status','>',-1];
        }

        if(!empty($input['start_time'])&&!empty($input['end_time'])){

            $dis[] = ['a.create_time','between',"{$input['start_time']},{$input['end_time']}"];

        }

        $where = [];

        if($input['name']){

            $where[] =['a.user_name','like','%'.$input['name'].'%'];

            $where[] =['a.phone','like','%'.$input['name'].'%'];
        }

        if(!empty($input['top_name'])){

            $dis[] =['c.agent_name','like','%'.$input['top_name'].'%'];
        }

        $res = $agent_model->adminDataList($dis,$input['limit'],$where);

        return $this->success($res);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-09 15:48
     * @功能说明:审核代理商申请
     */
    public function agentApplyCheck(){

        $input = $this->_input;

        $update = [

            'status' => $input['status'],

            'sh_time'=> time()
        ];

        $agent_model = new AgentApply();

        $res = $agent_model->dataUpdate(['id'=>$input['id']],$update);

        return $this->success($res);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-10 10:08
     * @功能说明:发大大配置详情
     */
    public function fddConfigInfo(){

        $config_model = new FddConfig();

        $admin_model = new \app\massage\model\Admin();

        $arr = $admin_model->dataInfo(['id'=>$this->_user['admin_id']],'agreement,agreement_title,agreement_time,signature_content');
        //平台
        if($this->_user['is_admin']!=0){

            $data = $config_model->dataInfo(['uniacid'=>$this->_uniacid]);

            $data['fdd_status'] = $data['status'];

            $arr = array_merge($arr,$data);
        }

        $user_id = $this->_user['is_admin']==0?$this->_user['admin_id']:0;

        $attestation_model = new FddAttestationRecord();

        $dis = [

            'user_id' => $user_id,

            'type'    => 2,

            'status'  => 3
        ];

        $find = $attestation_model->dataInfo($dis);

        $arr['attestation_status'] = !empty($find)?1:0;

        return $this->success($arr);
    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-10 10:10
     * @功能说明:编辑法大大配置
     */
    public function fddConfigUpdate(){

        $input = $this->_input;

        $config_model = new FddConfig();

        if(isset($input['AppId'])){

            $update = [

                'AppId'     => $input['AppId'],

                'AppSecret' => $input['AppSecret'],

                'status'    => $input['status'],

            ];

            $data = $config_model->dataUpdate(['uniacid'=>$this->_uniacid],$update);
        }

        $admin_model = new \app\massage\model\Admin();

        $update = [

            'agreement'       => $input['agreement'],

            'agreement_title' => $input['agreement_title'],

            'agreement_time'  => $input['agreement_time'],

          //  'signature_content' => $input['signature_content'],
        ];

        $admin_model->dataUpdate(['id'=>$this->_user['admin_id']],$update);

        return $this->success(true);

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-06-15 15:48
     * @功能说明:获取
     */
    public function getCity(){

        $input = $this->_param;

        $city_model = new City();

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['status','=',1];

        $agent_update_city = getConfigSetting($this->_uniacid, 'agent_update_city');


        if ($this->_user['is_admin'] == 0 && $agent_update_city == 0) {

            if ($this->_user['city_type'] == 3) {

                $dis[] = ['pid', '=', $this->_user['city_id']];

            } elseif ($this->_user['city_type'] == 1) {

                $dis[] = ['id', '=', $this->_user['city_id']];
            } else {

                $city = $city_model->dataInfo(['id' => $this->_user['city_id']]);

                if (!empty($city)) {

                    if ($city['is_city'] == 1) {

                        $dis[] = ['id', '=', $this->_user['city_id']];
                    } else {

                        $dis[] = ['id', '=', $city['pid']];
                    }
                }
            }
        }

        $mapor = [

            'city_type' => 1,

            'is_city'   => 1

        ];

        $data = $city_model->where($dis)->where(function ($query) use ($mapor){
            $query->whereOr($mapor);
        })->field(['id,title,lat,lng'])->order('id desc')->select()->toArray();

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-17 16:05
     * @功能说明:diy详情
     */
    public function diyInfo(){

        $input = $this->_param;

        $dis = [

            'uniacid' => $this->_uniacid,

        ];

        $diy_model = new Diy();

        $data = $diy_model->dataInfo($dis);

        $data['page']   = json_decode($data['page'],true);

        $data['tabbar'] = json_decode($data['tabbar'],true);

        $config = $this->model->dataInfo($dis);

        $data['selectedColor'] = $config['primaryColor'];

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-17 16:07
     * @功能说明:编辑diy
     */
    public function diyUpdate(){

        $input = $this->_input;

        $dis = [

            'uniacid' => $this->_uniacid,

        ];

        $diy_model = new Diy();

        $input['page'] = json_encode($input['page']);

        $input['tabbar'] = json_encode($input['tabbar']);

        $data = $diy_model->dataUpdate($dis,$input);

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-17 16:53
     * @功能说明:获取菜单
     */
    public function getTabbar(){

        $input = $this->_input;

        $tabbar = [
            [
                'id'=> 1,
                'name'=>'首页',
                'default_img'=>'iconshouye11',
                'selected_img'=>'iconshouye21',
            ],
            [
                'id'=> 2,
                'name'=>'技师',
                'default_img'=>'iconanmo1',
                'selected_img'=>'iconanmo2',
            ],
            [
                'id'=> 4,
                'name'=>'订单',
                'default_img'=>'icondingdan3',
                'selected_img'=>'icondingdan2',
            ],
            [
                'id'=> 5,
                'name'=>'我的',
                'default_img'=>'iconwode1',
                'selected_img'=>'iconwode2',
            ]

        ];

        $auth = AdminMenu::getAuthList((int)$this->_uniacid,['dynamic','recommend','store','map','recording','adapay','partner']);

        if($auth['dynamic']==1){

            $tabbar[] =  [
                'id'=> 3,
                'name'=>'动态',
                'default_img'=>'icon-dongtai1',
                'selected_img'=>'icon-dongtai2',
            ];
        }
        if($auth['store']==1){

            $tabbar[] =  [
                'id'=> 6,
                'name'=>'门店',
                'default_img'=>'icondianpu',
                'selected_img'=>'iconmendian1',
            ];
        }if($auth['map']==1){

//            $tabbar[] =  [
//                'id'=> 7,
//                'name'=>'地图找人',
//                'default_img'=>'icondituzhaoren2',
//                'selected_img'=>'icondituzhaoren1',
//            ];
        }

        if ($auth['partner'] == 1) {

            $tabbar[] = [
                "id" => 8,
                "name" => "找搭子",
                "default_img" => "iconzhaodazi-weixuanzhong",
                "selected_img" => "iconzhaodazi-xuanzhong"
            ];
        }

        return $this->success($tabbar);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-18 10:28
     * @功能说明:获取功能页面
     */
    public function getFunctionPageList(){

        $data = [

            [

                'api_path' => '/massage/admin/AdminSetting/getFunctionPageInfo',

                'page' => 'common_page',

                'title' => '功能页面',
            ],
            [

                'api_path' => '/massage/admin/AdminService/cateList',

                'page' => '/user/pages/service/list',

                'title' => '服务分类',
            ],
            [

            'api_path' => '/massage/admin/AdminArticle/articleList',

            'page' => '/user/pages/article',

            'title' => '文章详情',
            ]
        ];

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-18 10:31
     * @功能说明:
     */
    public function getFunctionPageInfo(){

        $coach_name = getConfigSetting($this->_uniacid,'attendant_name');

        $data = [

            [

                'path'=>'/agent/pages/apply',
                'title' => '合作加盟'
            ],
            [

                'path'=>'/technician/pages/apply?type=1',
                'title' => $coach_name.'入驻'
            ],
            [

                'path'=>'/user/pages/service/cate',
                'title' => '全部服务分类'
            ]
        ];

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-09-25 17:30
     * @功能说明:技师信用分配置
     */
    public function creditConfigInfo(){

        $dis = [

            'uniacid' => $this->_uniacid
        ];

        $config_model = new CreditConfig();

        $config = $config_model->dataInfo($dis);

        $arr = ['init_value','order_value','new_protect_value','add_order_value','time_long_value','repeat_order_value','good_evaluate_value','refund_order_value','refuse_order_value','bad_evaluate_value'];

        foreach ($arr as $value){

            $config[$value] = floatval($config[$value]);
        }

        return $this->success($config);

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-09-25 17:30
     * @功能说明:技师信用分配置
     */
    public function creditConfigUpdate(){

        $input = $this->_input;

        $dis = [

            'uniacid' => $this->_uniacid
        ];

        $config_model = new CreditConfig();

        $input['distance'] = serialize($input['distance']);

        $config = $config_model->dataUpdate($dis,$input);

        return $this->success($config);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-10-23 11:21
     * @功能说明:获取版本更新记录
     */
    public function getUpRecord(){

        $input = $this->_param;

        $goods_name = config('app.AdminModelList')['app_model_name'];
        $auth_uniacid =  config('app.AdminModelList')['auth_uniacid'];
        $upgrade = new \LongbingUpgrade($auth_uniacid , $goods_name , false);

        $goods_id = 19;

        $data = $upgrade->getUpRecord($input['page'],$goods_id);

        $auth = new PermissionIndustrytype($this->_uniacid);

        $p_auth = $auth->pAuth();

        if($p_auth==1){

            $title = '按摩';

        }elseif ($p_auth==2){

            $title = '上门';

        }else{

            $title = '全行业';
        }

        $longbing_title = getConfigSetting($this->_uniacid,'longbing_title');

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $v['title'] = str_replace('龙兵',$longbing_title,$v['title']);

                $v['title'] = str_replace('按摩',$title,$v['title']);
            }
        }

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-10-31 14:27
     * @功能说明:技师图标列表
     */
    public function coachIconList(){

        $input = $this->_param;

        $icon_model = new CoachIcon();

        $dis[] = ['status','>',-1];

        $dis[] = ['uniacid','=',$this->_uniacid];

        $list = $icon_model->dataList($dis,$input['limit']);

        return $this->success($list);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-10-31 14:27
     * @功能说明:技师图标列表
     */
    public function coachIconInfo(){

        $input = $this->_param;

        $icon_model = new CoachIcon();

        $dis=[

            'id' => $input['id']
        ];

        $list = $icon_model->dataInfo($dis);

        return $this->success($list);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-10-31 14:27
     * @功能说明:技师图标列表
     */
    public function coachIconUpdate(){

        $input = $this->_input;

        $icon_model = new CoachIcon();

        $dis=[

            'id' => $input['id']
        ];

        $list = $icon_model->dataUpdate($dis,$input);
        //删除
        if(isset($input['status'])&&$input['status']==-1){

            $coach_model = new Coach();

            $coach_model->dataUpdate(['coach_icon'=>$input['id']],['coach_icon'=>0,'recommend_icon'=>0]);
        }

        return $this->success($list);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-10-31 14:27
     * @功能说明:技师图标列表
     */
    public function coachIconAdd(){

        $input = $this->_input;

        $icon_model = new CoachIcon();

        $input['uniacid'] = $this->_uniacid;

        $list = $icon_model->dataAdd($input);

        return $this->success($list);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-11-15 14:00
     * @功能说明:base64转图片
     */
    public function base64ToPngClouds(){

        $input = $this->_input;

        $data  = base64ToPngClouds($input['img'],$this->_uniacid,$this->_host);

        if($data==false){

            $this->errorMsg('生成图片失败');
        }

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-01-24 14:00
     * @功能说明:获取按钮配置
     */
    public function btnConfigInfo(){

        $config_model = new BtnConfig();

        $config_model->initData($this->_uniacid);

        $data = $config_model->where(['uniacid'=>$this->_uniacid])->select()->toArray();

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-01-24 14:01
     * @功能说明:修改按钮配置
     */
    public function btnConfigUpdate(){

        $input = $this->_input;

        $config_model = new BtnConfig();

        foreach ($input['data'] as $v){

            $config_model->dataUpdate(['uniacid'=>$this->_uniacid,'type'=>$v['type']],$v);
        }

        return $this->success(true);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-03-27 18:29
     * @功能说明:加钟比列
     */
    public function addclockBalanceList(){



    }


    /**
     * @author chenniang
     * @DataTime: 2024-07-12 15:55
     * @功能说明:订单设置详情
     */
    public function orderConfigInfo(){

        $data = getConfigSettingArr($this->_uniacid,['empty_order_cash','after_service_can_refund','coach_empty_cash','admin_empty_cash','coach_refund_comm','admin_refund_comm']);

        $fee_model = new EmptyTicketFeeConfig();

        $data['cash_list'] = $fee_model->where(['uniacid'=>$this->_uniacid])->select()->toArray();

        return $this->success($data);

    }


    /**
     * @author chenniang
     * @DataTime: 2024-07-12 17:16
     * @功能说明:订单配置修改
     */
    public function orderConfigUpdate(){

        $input = $this->_input;

        if($this->_user['is_admin']!=1){

            $this->errorMsg('数据错误');
        }

        $setting_model = new ConfigSetting();

        $arr = [

            'empty_order_cash' => $input['empty_order_cash'],
            'after_service_can_refund' => $input['after_service_can_refund'],
            'coach_empty_cash' => $input['coach_empty_cash'],
            'admin_empty_cash' => $input['admin_empty_cash'],
            'coach_refund_comm' => $input['coach_refund_comm'],
            'admin_refund_comm' => $input['admin_refund_comm'],
        ];

        $setting_model->dataUpdate($arr,$this->_uniacid);

        if(!empty($input['cash_list'])){

            $fee_model = new EmptyTicketFeeConfig();

            $fee_model->where(['uniacid'=>$this->_uniacid])->delete();

            foreach ($input['cash_list'] as $key=>$value){

                $insert[$key] = [

                    'uniacid' => $this->_uniacid,

                    'minute' => $value['minute'],

                    'balance' => $value['balance'],
                ];
            }

            $fee_model->saveAll($insert);
        }
        return $this->success(true);
    }


    /**
     * @author chenniang
     * @DataTime: 2024-07-15 11:59
     * @功能说明:代理商车费配置详情
     */
    public function adminCarCashInfo(){

        $input = $this->_param;

        $config_model = new AdminConfig();

        if(empty($input['admin_id'])){

            if($this->_user['is_admin']!=0){

                $this->errorMsg('数据错误');
            }

            $admin_id = $this->_user['admin_id'];
        }else{

            $admin_id = $input['admin_id'];
        }

        $dis = [

            'uniacid' => $this->_uniacid,

            'admin_id'=> $admin_id
        ];

        $data = $config_model->dataInfo($dis);

        return $this->success($data);
    }


    /**
     * @author chenniang
     * @DataTime: 2024-07-15 13:51
     * @功能说明:订单车费配置修改
     */
    public function adminCarCashUpdate(){

        $input = $this->_input;

        $config_model = new AdminConfig();

        if($this->_user['is_admin']!=0){

            $this->errorMsg('数据错误');
        }

        $dis = [

            'uniacid' => $this->_uniacid,

            'admin_id'=> $this->_user['admin_id']
        ];

        $update = [

            'free_fare_distance' => $input['free_fare_distance'],

            'free_fare_bear' => $input['free_fare_bear'],
        ];

        $data = $config_model->dataUpdate($dis,$update);

        $coach_model = new Coach();

        $coach_model->where(['admin_id'=>$this->_user['admin_id']])->update(['free_fare_bear'=>$input['free_fare_bear']]);

        $coach_model->where(['admin_id'=>$this->_user['admin_id']])->where('free_fare_distance','>',$input['free_fare_distance'])->update(['free_fare_distance'=>$input['free_fare_distance']]);

        return $this->success($data);

    }


    /**
     * @author chenniang
     * @DataTime: 2024-07-19 16:03
     * @功能说明:获取地址信息
     */
    public function getMap(){

        $input = $this->_param;

        if(!isset($input['address'])){

            $this->errorMsg('数据错误');
        }

        $address = $input['address'];

        $dis = [

            'uniacid' => $this->_uniacid
        ];

        $config_model = new Config();

        $config = $config_model->dataInfo($dis);

        $map_secret = $config['map_secret'];

        if(empty($map_secret)){

            $this->errorMsg('请配置腾讯地图');
        }

        $URL  = "https://apis.map.qq.com/ws/geocoder/v1/?address=$address&key=$map_secret";

        $data =  longbingCurl($URL,[]);

        $data =  @json_decode($data,true);

        return $this->success($data);

    }



    public function getMapInfo(){

        $input = $this->_param;

        if(!isset($input['location'])){

            $this->errorMsg('数据错误');
        }

        $location = $input['location'];

        $dis = [

            'uniacid' => $this->_uniacid
        ];

        $config_model = new Config();

        $config = $config_model->dataInfo($dis);

        $map_secret = $config['map_secret'];

        if(empty($map_secret)){

            $this->errorMsg('请配置腾讯地图');
        }

        $URL  = "https://apis.map.qq.com/ws/geocoder/v1/?location=$location&key=$map_secret";

        $data =  longbingCurl($URL,[]);

        $data =  @json_decode($data,true);

        return $this->success($data);

    }

    /**
     * @Desc: 百应配置
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @Auther: shurong
     * @Time: 2024/8/9 18:38
     */
    public function bySetting()
    {
        $input = $this->_param;

        if (request()->isPost()) {

            $res = BaiYingConfig::update($input, ['uniacid' => $this->_uniacid]);

            return $this->success($res);
        }
        $data = BaiYingConfig::getInfo(['uniacid' => $this->_uniacid]);

        return $this->success($data);
    }






}
