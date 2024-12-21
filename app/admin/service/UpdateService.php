<?php
// +----------------------------------------------------------------------
// | Longbing [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright Chengdu longbing Technology Co., Ltd.
// +----------------------------------------------------------------------
// | Website http://longbing.org/
// +----------------------------------------------------------------------
// | Sales manager: +86-13558882532 / +86-13330887474
// | Technical support: +86-15680635005
// | After-sale service: +86-17361005938
// +----------------------------------------------------------------------

declare(strict_types=1);

namespace app\admin\service;
use app\Common\model\LongbingWeqingWxApp as WxAppWeqingWxApp;
use app\admin\model\AppConfig;
use app\admin\model\OssConfig;
use app\admin\model\AppTabbar;

use http\Env;
use longbingcore\permissions\AdminMenu;
use LongbingUpgrade;
use think\facade\Db;
require_once("keygen.php");

class UpdateService
{


    //@ioncube.dk myk("sha256", "random5676u71113r45") -> "f7329ae655d0b975a8a97b193b4f6a13627d59c8d357fad4985ffaa8cb3a0cf8" RANDOM
    public static function isAuth($uniacid){

        $key = 'sass_auth_auth_authssss';

        $value = getCache($key,$uniacid);

        if(empty($value)){

            include_once LONGBING_EXTEND_PATH . 'Site.php';

            $goods_name   = config('app.AdminModelList')['app_model_name'];

            $auth_uniacid = config('app.AdminModelList')['auth_uniacid'];

            $upgrade      = new \Site($auth_uniacid , $goods_name , \think\facade\Env::get('j2hACuPrlohF9BvFsgatvaNFQxCBCc' , false));

            $p = $upgrade->isAuthPa($uniacid);

            if($p==813){

                setCache($key,1813,86400*5,$uniacid);

                return true;
            }

            return false;
        }

        return true;
    }
    /**
     * 安装脚本
     *
     * @param $_uniacid
     * @author shuixian
     * @DataTime: 2020/1/4 9:40
     */
    //@ioncube.dk myk("sha256", "random5676u711112") -> "82a5e1413499396c23051be99ea73defd9c3d36b0f155dc4879aff75ece25db7" RANDOM
    public static function installSql($_uniacid,$types=1){

        // $res = self::isAuth($_uniacid);


//        if($res==false){
//
//            return false;
//        }

        //打算进入首页时,自动初始化和安装系统,不在外面进行安装
        //可以根据当前的版本号创建锁定文件,不用每次都执行


        //执行SQL
        /*$updateSqlData = longbing_init_info_data('UpdateSql');

        foreach ($updateSqlData as $sql ){
            Db::query($sql) ;
        }*/

        //更加配置模块,自动载入php
        $myModelList =  config('app.AdminModelList');

        $myModelList = $myModelList['saas_auth_admin_model_list'];

//        dump($myModelList);exit;
        //$myModelList = AdminMenu::getAuthList($_uniacid);

        //1.筛选权限模块
        //2.加载权限执行脚本
        //3.判断升级脚本是否存在
        //4.判断是否升级过了,版本号是否正确 (UpdateSql.lock)
        //var_dump($myModelList); exit;
        foreach ($myModelList as $model_name => $model_item ) {
            $updateSqlPath =  APP_PATH . $model_name . '/server/UpdateData.php' ;
            $lockPath =  APP_PATH . $model_name . '/info/UpdateSql.lock' ;
            $infoPath =  APP_PATH . $model_name . '/info/Info.php' ;
            $infoData = include $infoPath ;

            $isUpdate = false ;
            $nowVersion = array_key_exists('version',$infoData) ? $infoData['version']  : '0.0.0';
            if(file_exists($lockPath)){  // 有锁定文件,需要进一步判断模块版本号

                $lockVersion = file_get_contents($lockPath); //读取锁定文件版本号
                $isUpdate = longbing_compare_version($lockVersion, $nowVersion) ? true : false ;

            }else{
                //锁定文件不存在,直接升级
                $isUpdate = true ;
            }

            if($types==0){

                $isUpdate = true ;
            }

            if($isUpdate && file_exists($updateSqlPath)){
                $sql = include $updateSqlPath ;
                $sql = str_replace(PHP_EOL, '', $sql);
                $sqlArray = explode(';', $sql);

               // dump($sqlArray);
                foreach ($sqlArray as $_value) {
                    if(!empty($_value)){

                        try{
                            Db::query($_value) ;
                        }catch (\Exception $e){
                            if (!APP_DEBUG){
                                //echo '操作失败: '.$_value . '<br> <br>' ;
                            }

                        }
                    }
                }
                //调试模式下,不写入锁定文件 ,方便调试
                if (!APP_DEBUG){
                    file_put_contents($lockPath , $nowVersion) ;
                }
            }
        }

        if($isUpdate){

            $sqlArray = lbData('massage/admin/Index/getSql',1,1,['prefix'=>longbing_get_prefix()]);

            $sqlArray = $sqlArray['data'];

            if(!empty($sqlArray)){

                foreach ($sqlArray as $_value) {
                    if(!empty($_value)){

                        try{
                            Db::query($_value) ;
                        }catch (\Exception $e){
                            if (!APP_DEBUG){
                                //echo '操作失败: '.$_value . '<br> <br>' ;
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * 初始化微擎数据
     *
     * @author shuixian
     * @DataTime: 2020/1/4 9:42
     */
    public static function initWeiqinConfigData(){
        if(longbingIsWeiqin())
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
                /*$tabbars = longbingGetAppTabbar($uniacid);
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
                }*/
                longbingGetCompanyConfig($uniacid);
            }

        }
    }

}