<?php

declare(strict_types=1);

namespace app\agent\controller;


include_once LONGBING_EXTEND_PATH . 'LongbingUpgrade.php';

use app\admin\model\WxUpload;
use app\admin\service\UpdateService;
use app\AdminRest;
use app\AgentRest;
use app\diy\service\DiyService;
use longbingcore\wxcore\WxSetting;
use app\industrytype\info\PermissionIndustrytype;
use LongbingUpgrade;
use think\facade\Env;

class AppUpgrade extends AdminRest
{

    /**
     * @author jingshuixian
     * @DataTime: 2020-06-08 9:33
     * @功能说明: 获得升级信息
     */
    public function getUpgradeInfo(){

        $goods_name   = config('app.AdminModelList')['app_model_name'];

        $auth_uniacid =  config('app.AdminModelList')['auth_uniacid'];

        $version_no   =  config('app.AdminModelList')['version_no'];

        $upgrade      = new LongbingUpgrade($auth_uniacid , $goods_name , Env::get('j2hACuPrlohF9BvFsgatvaNFQxCBCc' , false));

        $data = $upgrade->checkAuth();

        $data['location_version_no'] =  $version_no ;

        $data['is_upgrade'] = $this->getIsUpgrade();

        $auth = new PermissionIndustrytype(666);

        $p_auth = $auth->pAuth();

        if($p_auth==1){

            $title = '按摩';

        }elseif ($p_auth==2){

            $title = '上门';

        }else{

            $title = '全行业';
        }

        if(!empty($data['data']['version']['title'])){

            $longbing_title = getConfigSetting(666,'longbing_title');

            $data['data']['version']['title'] = str_replace('龙兵',$longbing_title,$data['data']['version']['title']);

            $data['data']['version']['title'] = str_replace('按摩',$title,$data['data']['version']['title']);
        }

        return $this->success( $data );
    }


    /**
     * By.jingshuixian
     * 2019年11月23日21:43:47
     * 升级脚本导入执行
     */
    public function update(){

        $key  = 'init_all_data';

        setCache($key,'',7200,$this->_uniacid);

        UpdateService::installSql($this->_uniacid);

        // UpdateService::initWeiqinConfigData();

        //   DiyService::addDefaultDiyData($this->_uniacid);
        //各个模块初始化数据事件
        //event('InitModelData');
        //处理雷达
        // lbInitRadarMsg($this->_uniacid);

        return $this->success([]);

    }
    /**
     * @author jingshuixian
     * @DataTime: 2020-06-08 18:04
     * @功能说明: 判断是否有升级权限
     */
    private function getIsUpgrade(){

        // $goods_name = config('app.AdminModelList')['app_model_name'];

        if(!longbingIsWeiqin()){

            return true;

        }else{

            return false  ;
        }
    }


    /**
     * @author jingshuixian
     * @DataTime: 2020-06-08 14:43
     * @功能说明: 升级后台系统
     */
    public function upgrade(){

        if($this->getIsUpgrade()){
            $goods_name = config('app.AdminModelList')['app_model_name'];
            $auth_uniacid =  config('app.AdminModelList')['auth_uniacid'];
            $version_no =  config('app.AdminModelList')['version_no'];

            $upgrade = new LongbingUpgrade($auth_uniacid , $goods_name , Env::get('j2hACuPrlohF9BvFsgatvaNFQxCBCc' , false));

            $file_temp_path =  ROOT_PATH . "runtime/" ;
            $toFilePath =  ROOT_PATH ;
            // 自动下载文件到  core/runtime     解压到  core/    根目是thinkphp所在目录
            $data = $upgrade->update( $toFilePath ,$file_temp_path );

            return $this->success( $data );
        }else{
            return $this->success( [] );
        }


    }
}