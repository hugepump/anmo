<?php
namespace app\admin\controller;
use app\admin\service\UpdateService;
use app\AdminRest;
use app\card\info\InitData;
use app\diy\service\DiyService;
use think\App;

class Update extends AdminRest
{
	public function __construct(App $app) {
        parent::__construct($app);
    }

    /**
     * By.jingshuixian
     * 2019年11月23日21:43:47
     * 升级脚本导入执行
     */
    public function update(){

//        return $this->success([]);
        $key  = 'init_all_data';

        $data = getCache($key,$this->_uniacid);

        if(!empty($data)){

            return $this->success([]);

        }

        setCache($key,1,7200,$this->_uniacid);

        UpdateService::installSql($this->_uniacid);

        UpdateService::initWeiqinConfigData();

        DiyService::addDefaultDiyData($this->_uniacid);
        //各个模块初始化数据事件
        event('InitModelData');
        //处理雷达
        lbInitRadarMsg($this->_uniacid);

        return $this->success([]);

    }


}
	