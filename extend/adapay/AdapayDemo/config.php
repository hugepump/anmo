<?php
/**
 * init方法参数介绍
 * 第一个是配置文件路径或者配置数组对象
 * 第二个参数是SDK模式
 * 第三个是标识第一个参数的类型 true为数组对象 false为文件路径
 **/

$config_model = new \app\adapay\model\Config();

$congfig = $config_model->dataInfo(['uniacid'=>666]);

$config_object = [

    "api_key_live" => $congfig['api_key_live'],

    "api_key_test" => $congfig['api_key_test'],

    "rsa_private_key" => $congfig['rsa_private_key']

];

\AdaPay\AdaPay::init($config_object, "live", true);

//\AdaPay\AdaPay::init(dirname(__FILE__). '/config/config.json', "live", false);