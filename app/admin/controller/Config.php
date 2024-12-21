<?php
namespace app\admin\controller;
use app\admin\service\UpdateService;
use app\AdminRest;
use app\admin\model\WxUpload;
use app\coachbroker\model\CoachBroker;
use app\Common\LongbingServiceNotice;
use app\massage\model\BtnConfig;
use app\massage\model\Coach;
use app\massage\model\ConfigSetting;
use app\massage\model\Order;
use app\massage\model\RefundOrder;
use app\massage\model\StoreCoach;
use app\sendmsg\model\SendConfig;
use longbingcore\wxcore\WxSetting;
use longbingcore\wxcore\WxTmpl;
use think\App;
use think\facade\Db;
use think\Request;
use think\file\UploadedFile;
use app\admin\model\OssConfig;
use app\admin\model\AppConfig;
use app\admin\model\AppTabbar;
use app\admin\model\TmplConfig;
use app\Common\Upload;

/**
 * @author yangqi
 * @create time: 2019年11月25日21:29:30
 */
class Config extends AdminRest
{
    public function __construct(App $app) {
        parent::__construct($app);
        //测试数据
    //	$this->_uniacid = 8;
    }
    //创建或者修改
    public function updateOssConfig()
    {

        //获取nuiacid
        $uniacid = $this->_uniacid;
        //获取上传参数
        $input = [];

        if(isset($this->_input['oss_config'])) $input = $this->_input['oss_config'];
        //数据清洗
        $data = getOssConfigData($input);

        $data['uniacid'] = $uniacid;
        //生成操作模型
        $oss_config_model = new OssConfig();
        //查询数据是否存在 
        $oss_config = $oss_config_model->getConfig(['uniacid' => $uniacid]);

        $result = false;

        $data['is_sync'] = 1;

        if(empty($oss_config))
        {
            $result = $oss_config_model->createConfig($data);
        }else{
            //检查上传配置是否正确
            $result = $oss_config_model->updateConfig(['uniacid' => $uniacid] ,$data);
        }
        $config = longbingGetOssConfig($uniacid ,true);
        
        if(!empty($result) && !empty($data['open_oss']))
        {
            $path = LONGBING_EXTEND_PATH . 'timg.jpg';
            if(file_exists($path)){
                $file = new UploadedFile($path ,'test.jpg');
                $file_upload_model = new Upload($uniacid);
                $check = $file_upload_model->upload('picture' ,$file);

                if(empty($check)) return $this->error(lang('upload config error'));
            }
        }
        return $this->success($result);
        
    }
    //获取配置
    public function getOssConfig()
    {
        //获取uniacid
        $uniacid = $this->_uniacid;
        //生成操作模型
        $oss_config_model = new OssConfig();
        //获取数据
        $config = $oss_config_model->getConfig(['uniacid' => $uniacid]);
        if(!empty($config)) unset($config['id']); 
        return $this->success($config);
    }
    
    
    //小程序设置
    public function getAppConfig()
    {
        //获取参数
        $uniacid = $this->_uniacid;
        //获取数据
        $result  = longbingGetAppConfig($uniacid);

        //返回数据
        return $this->success($result);
    }


    //小程序设置
    public function updateAppConfig(){





    }
    
    //小程序设置
    public function setAppConfig()
    {
        //获取参数
        $uniacid = $this->_uniacid;
        //获取数据
        $input   = null;


        if(isset($this->_input['app_config'])) $input = $this->_input['app_config'];

        if(empty($input)) return $this->error('not app config data ,please check input data.');

        $input['uniacid'] = $this->_uniacid;
        //获取数据
        $result = longbingGetAppConfig($uniacid);

        $app_config_model = new AppConfig();

        $input['is_sync'] = 1;

        //企业微信小程序通知
        if(!empty($input['notice_switch'])&&$input['notice_switch']==4){

            $insrt['yq_corpid']     = $input['yq_corpid'];

            $insrt['yq_corpsecret'] = $input['yq_corpsecret'];

            $insrt['yq_agentid']    = $input['yq_agentid'];

            $send_model = new SendConfig();

            $data  =  $send_model->configUpdate(['uniacid'=>$this->_uniacid],$insrt);

            unset($input['yq_corpid']);

            unset($input['yq_corpsecret']);

            unset($input['yq_agentid']);
        }

        if(!isset($result['uniacid']) || empty($result))
        {
            //创建
            $result = $app_config_model->createConfig($input);
        }else{
            //更新
            $result = $app_config_model->updateConfig(['id' => $result['id']] ,$input);
        }

        longbingGetAppConfig($uniacid ,true);

        return $this->success($result);    
    }
    
    
    //自动同步服务通知模板
    public function autoServiceNoticeTemplate()
    {
        //获取配置信息
        $config = longbingGetAppConfig($this->_uniacid);
        if(!isset($config['appid']) || empty($config['appid']) || !isset($config['app_secret']) || empty($config['app_secret'])) return $this->error('wx app site not exist ,please check site message.');
        //获取accesstoken
        $ac  = longbingSingleGetAccessTokenByUniacid($this->_uniacid);
        //判断accesstoken是否存在
        if(empty($ac)) return $this->error(lang('wx app site error'));
        //生成获取服务通知模板的url
           $url = "https://api.weixin.qq.com/cgi-bin/wxopen/template/add?access_token={$ac}";
        //生成数据
        $data = [ 'id' => 'AT1442', 'keyword_id_list' => [ 4, 7, 1 ] ];
        $data = json_encode( $data );
        //获取数据
        $result = longbingCurl( $url, $data ,'POST');
        //解析数据
        $result = json_decode( $result, true );
        if ( isset( $result[ 'errcode' ] ) && $result[ 'errcode' ] == 40001 ) {
            //重新获取accesstoken
            $ac  = longbingSingleGetAccessTokenByUniacid($this->_uniacid ,true);
            $url = "https://api.weixin.qq.com/cgi-bin/wxopen/template/add?access_token={$ac}";
            //获取数据
            $result = longbingCurl( $url, $data ,'POST');
            //数据接续
            $result = json_decode( $result, true );
        }
        //判断

        if ( isset( $result[ 'errcode' ] ) && !empty($result[ 'errcode' ]) ) return $this->error(lang('auto get template error'));
        //更新设置信息
        $app_config_model = new AppConfig();
        $mini_template_id = $result['template_id'];
        $result = $app_config_model->updateConfig(['id' => $config['id']] ,['mini_template_id' => $mini_template_id]);
        if($result) longbingGetAppConfig($this->_uniacid ,true); $result = ['mini_template_id' => $mini_template_id];

        return $this->success($result);
    }
    
    //获取底部菜单
    public function getTabbar()
    {
        //获取参数
        $uniacid = $this->_uniacid;
        //获取数据
        $result  = longbingGetAppTabbar($uniacid ,true);
        //数据封装
        $result  = longbingGetAppTabbarResponse($result);

        $pluginAuth = longbingGetPluginAuth($uniacid);
        $plugin_map = [
            "activity"=> 'activity',
            'appointment' => 'appoint',
            'house' => 'house',
        ];
        $meta_map = [
            'card' => 'BusinessCard',
            'shop' => 'Malls',
            'dynamic' => 'Dynamic',
            'website' => 'Website',
        ];


        foreach ($result['data'] as $k => $item) {
            if (in_array($k, array_keys($plugin_map)) && ($pluginAuth['plugin'][$plugin_map[$k]] == 0)) {
                unset($result['data'][$k]);
                continue;
            }

            if (in_array($k, array_keys($meta_map)) && ($pluginAuth['web_manage_meta_config'][$meta_map[$k]] == 0)) {
                unset($result['data'][$k]);
                continue;
            }
        }

        $result = array_merge($result, $pluginAuth);

        return $this->success($result);
    }
    
    //设置底部菜单
    public function setTabbar()
    {
        //获取参数
        $uniacid = $this->_uniacid;
        $input   = null;
        if(isset($this->_input['data'])) $input = $this->_input['data'];
        $input = longbingGetAppTabbarRequest($input);
        if(empty($input)) return $this->error('not tabbar data');
//        var_dump($input);die;
        //获取数据
        $tabbar  = longbingGetAppTabbar($uniacid);


        //限制只能有5个tabbar
        $menu_now = [
           'menu1_is_hide' => $tabbar['menu1_is_hide'],
           'menu2_is_hide' => $tabbar['menu2_is_hide'],
           'menu3_is_hide' => $tabbar['menu3_is_hide'],
           'menu4_is_hide' => $tabbar['menu4_is_hide'],
           'menu_appoint_is_hide' => $tabbar['menu_appoint_is_hide'],
           'menu_activity_is_show' => $tabbar['menu_activity_is_show'],
           'menu_house_is_show' => $tabbar['menu_house_is_show'],
        ];

        $permissions = longbingGetPluginAuth($this->_uniacid);
        
        $not_tabbars = [];
        if(!empty($permissions) && !empty($permissions['plugin']))
        {
            //预约
            if(!isset($permissions['plugin']['appoint'])  || empty($permissions['plugin']['appoint']))
            {
                $not_tabbars[] = 'menu_appoint_is_hide';
                $input['menu_appoint_is_hide'] = 0;
            }
            //活动
            if(!isset($permissions['plugin']['activity']) || empty($permissions['plugin']['activity']))
            {
                $not_tabbars[] = 'menu_activity_is_show';
                $input['menu_activity_is_show'] = 0;
            }
            //房产
            if(!isset($permissions['plugin']['house'])    || empty($permissions['plugin']['house']))
            {
                $not_tabbars[] = 'menu_house_is_show';
                $input['menu_house_is_show'] = 0;
            }
            //官网
            if(!isset($permissions['web_manage_meta_config']['Website'])    || empty($permissions['web_manage_meta_config']['Website'])) 
            {
                $not_tabbars[] = 'menu4_is_hide';
                $input['menu4_is_hide'] = 0;
            }
            //商场
            if(!isset($permissions['web_manage_meta_config']['Malls'])      || empty($permissions['web_manage_meta_config']['Malls']))
            {
                $not_tabbars[] = 'menu2_is_hide';
                $input['menu3_is_hide'] = 0;
            }
            //动态
            if(!isset($permissions['web_manage_meta_config']['Dynamic'])    || empty($permissions['web_manage_meta_config']['Dynamic']))
            {
                $not_tabbars[] = 'menu3_is_hide';
                $input['menu3_is_hide'] = 0;
            }
        }
        // var_dump($not_tabbars);die;
        $max_tabbar_count = env('MAX_TABBAR_COUNT', 5);
        $all_tabbar_count = 0;
        foreach ($menu_now as $k => $v) {
          if (isset($input[$k])) $v = $input[$k];
          if(!in_array($k ,$not_tabbars)) $all_tabbar_count = $all_tabbar_count + $v;
        }

//      $max_tabbar_count = env('MAX_TABBAR_COUNT', 5);
//      $all_tabbar_count = $menu_now['menu1_is_hide']
//                        + $menu_now['menu2_is_hide']
//                        + $menu_now['menu3_is_hide']
//                        + $menu_now['menu4_is_hide']
//                        + $menu_now['menu_appoint_is_hide']
//                        + $menu_now['menu_activity_is_show']
//                        + $menu_now['menu_house_is_show'];

		if ($all_tabbar_count > $max_tabbar_count) {
		    return $this->error('显示的菜单栏不能大于 ' . $max_tabbar_count);
        }

        //判断数据是否存在
        $result = false;
        $tabbar_model = new AppTabbar();
        if(empty($tabbar)){
            $input['uniacid'] = $uniacid;
            $result = $tabbar_model->createTabbar($input);
        }else{
            $result = $tabbar_model->updateTabbar(['id' => $tabbar['id']] ,$input);
        }
        longbingGetAppTabbar($uniacid ,true);
        return $this->success($result);
    }
    //清理缓存
    public function clearCache()
    {
        //获取数据
        $uniacid = $this->_uniacid;
        //更新数据库
        UpdateService::installSql(8888,0);

        $a = new WxSetting($this->_uniacid);

        $a->setH5Info();

        $config_model = new ConfigSetting();

        $config_model->initData($uniacid);

        $broker_model = new CoachBroker();

        $broker_model->initBroker($uniacid);

        $config_model = new BtnConfig();

        $config_model->initData($uniacid);

        $order_model = new Order();

        $order_model->initOrderData($uniacid);

        $order_model->initCoachRefundOrder($uniacid);

        $refund_model = new RefundOrder();

        $refund_model->initRefundOrderData($uniacid);

        StoreCoach::initCoachData($uniacid);

        $coach_model = new Coach();

        $free_fare_bear = getConfigSetting($uniacid,'free_fare_bear');

        $key = 'free_fare_bear_key';

        if(empty(getCache($key,3333))&&$free_fare_bear>0){

            $coach_model->where(['admin_id'=>0])->update(['free_fare_bear'=>$free_fare_bear]);

            setCache($key,1,99999999999,3333);
        }

        $result  = @clearCache($uniacid);

        @clearCache($this->_uniacid,'city_key');
//        clearCache(999999999999);

        return $this->success($result);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-06-08 18:51
     * @功能说明:小程序上传配置详情
     */
    public function wxUploadInfo(){

        $model = new WxUpload();

        $dis   = [

            'uniacid' => $this->_uniacid
        ];
        //详情
        $data = $model->settingInfo($dis);

        $data['app_id'] = !empty($data['app_id'])?explode(',',$data['app_id']):[];

        return $this->success($data);
    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-06-08 18:51
     * @功能说明:小程序上传配置详情
     */
    public function wxUploadUpdate(){

        $input = $this->_input;

        $model = new WxUpload();

        $dis   = [

            'uniacid' => $this->_uniacid
        ];

//        $data = [
//            //密钥
//            'key'     => $input['key'],
//            //版本号
//            'version' => $input['version'],
//            //描述
//            'content' => $input['content'],
//            //appid
//            'app_id'  => $input['app_id'],
//        ];
        //详情
        $data = $model->settingUpdate($dis,$input);

        return $this->success($data);
    }




}