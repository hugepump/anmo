<?php
namespace app\middleware;
use app\Common\model\LongbingWeqingWxApp as WxAppWeqingWxApp;
use app\admin\model\AppConfig;
use app\admin\model\OssConfig;
use app\admin\model\AppTabbar;
class AppInit
{
    public function handle($request, \Closure $next)
    {
        //同步

        /*if(longbingIsWeiqin())
        {
            //获取uniacid
            global $_GPC, $_W;
            $uniacid = $_W[ 'uniacid' ];
            if(!empty($uniacid))
            {
                //获取数据
                //获取config
                $app_config = longbingGetAppConfig($uniacid);
                //判断config是否存在或者是否同步
                if(empty($app_config) || empty($app_config['is_sync']))
                {
                    //获取微擎配置
                    $weiqing_wx_app_model = new WxAppWeqingWxApp();
                    $weiqing_wx_app       = $weiqing_wx_app_model->getApp(['uniacid' => $uniacid]);
//                    var_dump($weiqing_wx_app);die;
                    if(!empty($weiqing_wx_app))
                    {
                        $data = ['is_sync' => 1];
                        if(isset($weiqing_wx_app['key']) && !empty($weiqing_wx_app['key'])) $data['appid'] = $weiqing_wx_app['key'];
                        if(isset($weiqing_wx_app['secret']) && !empty($weiqing_wx_app['secret'])) $data['app_secret'] = $weiqing_wx_app['secret'];
                        if(isset($weiqing_wx_app['name']) && !empty($weiqing_wx_app['name'])) $data['mini_app_name'] = $weiqing_wx_app['name'];
                        $app_config_model = new AppConfig();
                        if(empty($app_config))
                        {
                            $data['uniacid'] = $uniacid;
                            $data['force_phone'] = 0;
                            $app_config_model->createConfig($data);
                        }else{
                            $app_config_model->updateConfig(['uniacid' => $uniacid],$data);
                        }
                        longbingGetAppConfig($uniacid ,true);
                    }
                }
                //同步存储设置
                $oss_config = longbingGetOssConfig($uniacid);
                if(empty($oss_config) || empty($oss_config['is_sync'])){
                    $weiqing_oss = null;
                    if(isset($_W['setting']['remote_complete_info'][$_W['uniacid']])) $weiqing_oss = $_W['setting']['remote_complete_info'][$_W['uniacid']];
                    if(empty($weiqing_oss) && isset($_W['setting']['remote_complete_info'])) $weiqing_oss = $_W['setting']['remote_complete_info'];
                    if(!empty($weiqing_oss))
                    {
                        $data = ['is_sync' => 1];
                        if(isset($weiqing_oss['qiniu'])){
                            $data['open_oss'] = 2;
                            if(isset($weiqing_oss['qiniu']['accesskey']) && !empty($weiqing_oss['qiniu']['accesskey'])) $data['qiniu_accesskey'] = $weiqing_oss['qiniu']['accesskey'];
                            if(isset($weiqing_oss['qiniu']['secretkey']) && !empty($weiqing_oss['qiniu']['secretkey'])) $data['qiniu_secretkey'] = $weiqing_oss['qiniu']['secretkey'];
                            if(isset($weiqing_oss['qiniu']['bucket']) && !empty($weiqing_oss['qiniu']['bucket'])) $data['qiniu_bucket'] = $weiqing_oss['qiniu']['bucket'];
                            if(isset($weiqing_oss['qiniu']['url']) && !empty($weiqing_oss['qiniu']['url'])) $data['qiniu_yuming'] = $weiqing_oss['qiniu']['url'];
                        }
                        $oss_config_model = new OssConfig();
                        if(empty($oss_config))
                        {
                            $data['uniacid'] = $uniacid;
                            $oss_config_model->createConfig($data);
                        }else{
                            $oss_config_model->updateConfig(['uniacid' => $uniacid] ,$data);
                        }
                        longbingGetOssConfig($uniacid ,true);
                    }
                }
                //初始化地步菜单
                $tabbars = longbingGetAppTabbar($uniacid);
                if(empty($tabbars))
                {
                    $tabbar_model = new AppTabbar();
                    $data = array(
                                  'uniacid' => $uniacid,
                                  'menu2_is_hide'         => 0,
                                  'menu3_is_hide'         => 0,
                                  'menu4_is_hide'         => 0,
                                  'menu_activity_is_show' => 0,
                                  'menu_house_is_show'    => 0,
                                  'menu_appoint_is_hide'  => 0
                                );
                    $tabbar_model->createTabbar($data);
                    $tabbars = longbingGetAppTabbar($uniacid ,true);
                }
                longbingGetCompanyConfig($uniacid);
            }

        }
        */
        return $next($request);
    }
}
