<?php
/**
 * Created by PhpStorm.
 * User: shuixian
 * Date: 2019/11/20
 * Time: 18:30
 */

return [

    //模块名称[必填]
    'name'        => 'admin',
    //模块标题[必填]
    'title'      =>'系统',
    //内容简介
    'desc'       =>'',
    //封面图标
    'icon'       =>'',
    //模块类型[必填]  model:模块 可以出现在左侧一级  app:应用中心 , 是一个应用中心的应用
    'type'       => 'model',
    // 模块唯一标识[必填]，格式：模块名.开发者标识.module
    'identifier'  => 'admin.longbing.module',
    // 版本[必填],格式采用三段式：主版本号.次版本号.修订版本号
    'version'     => '1.1.42',
    // 模块依赖[可选]，格式[[模块名, 模块唯一标识, 依赖版本, 对比方式]]
    'need_module' => [],
    // 插件依赖[可选]，格式[[插件名, 插件唯一标识, 依赖版本, 对比方式]]
    'need_plugin' => [],
];