<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | 应用设置
// +----------------------------------------------------------------------

use think\facade\Env;
use think\facade\Request;

//By.jingshuixian
//自定义开发模式
$adminModelListPaht = include app()->getBasePath().'/Info.php';



if(Env::get('DEV_MODE',false)){
    $host = Request::host();
    $infoPath = app()->getBasePath().'/Info_'.$host.'.php';
    if(file_exists($infoPath)){
        $infoData = include $infoPath;
        $adminModelListPaht = $infoData ;


    }
}

return [
    // 应用地址
    'app_host'         => Env::get('app.host', ''),
    // 应用Trace（环境变量优先读取）
    'app_trace'        => false,
    // 应用的命名空间
    'app_namespace'    => '',
    // 是否启用路由
    'with_route'       => true,
    // 是否启用事件
    'with_event'       => true,
    // 自动多应用模式
    'auto_multi_app'   => true,
    // 应用映射（自动多应用模式有效）
    'app_map'          => [],
    // 域名绑定（自动多应用模式有效）
    'domain_bind'      => [],
    // 禁止URL访问的应用列表（自动多应用模式有效）
    'deny_app_list'    => [],
    // 默认应用
    'default_app'      => 'index',
    // 默认时区
    'default_timezone' => 'Asia/Shanghai',
    // 默认验证器
    'default_validate' => '',
    // 异常页面的模板文件
    'exception_tmpl'   => app()->getThinkPath() . 'tpl/think_exception.tpl',
   // 'exception_tmpl'   => '',
    // 错误显示信息,非调试模式有效
    'error_message'    => '页面错误！请稍后再试～',
    // 显示错误信息
    'show_error_msg'   => true,
   // 'show_error_msg'   => false,
    //是否是微擎系统
    //'is_weiqin'  	   => true,
    //By.jingshuixian  2019年11月23日16:13:36
    'AdminModelList' => $adminModelListPaht,
    //验证地址 // 这里应该是多个验证地址,防止验证失败
    'longbing_saas_url'=> 'http://api.longbing.org',

    'longbing_version' => '0.0.1',

];
