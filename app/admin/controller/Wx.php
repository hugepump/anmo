<?php
namespace app\admin\controller;
use think\App;
use app\ApiRest;
class Wx extends ApiRest
{
    // 继承 验证用户登陆
    public function __construct ( App $app )
    {
        parent::__construct( $app );
    }
    
    public function getTabbar()
    {
        $tabbars = longbingGetAppTabbar($this->_uniacid);
        $tabbars = longbingGetWxAppTabbarResponse($tabbars);


        $pluginAuth = longbingGetPluginAuth($this->_uniacid);

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


        foreach ($tabbars['data'] as $k => $item) {
            if (in_array($k, array_keys($plugin_map)) && ($pluginAuth['plugin'][$plugin_map[$k]] == 0)) {
                unset($tabbars['data'][$k]);
                continue;
            }

            if (in_array($k, array_keys($meta_map)) && ($pluginAuth['web_manage_meta_config'][$meta_map[$k]] == 0)) {
                unset($tabbars['data'][$k]);
                continue;
            }
        }
        return $this->success($tabbars);
    }
}
