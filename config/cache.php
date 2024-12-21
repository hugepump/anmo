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
use think\facade\Env;
defined('IN_IA') || define('IN_IA',true);
// +----------------------------------------------------------------------
// | 缓存设置
// +----------------------------------------------------------------------
$host     = Env::get('cache.host', '127.0.0.1');
$prefix   = Env::get('cache.prefix', 'longbing_');
$password = Env::get('cache.passwd', '');
$expire   = Env::get('cache.expire', 0);
$port     = Env::get('cache.port', 6379);



//$dir_path = __DIR__ . '/../../../../data/config.php';
//// var_dump(file_exists($dir_path));
//if(file_exists($dir_path) && longbingIsWeiqin())
//{
//    require_once $dir_path;
//
//    if(isset($config['setting']['redis']['server']) )       $host     = $config['setting']['redis']['server'];
//    if(isset($config['setting']['redis']['prefix']) )       $prefix   = $config['setting']['redis']['prefix'];
//    if(isset($config['setting']['redis']['pconnect']) )     $expire   = $config['setting']['redis']['pconnect'];
//    if(isset($config['setting']['redis']['requirepass']) )  $password = $config['setting']['redis']['requirepass'];
//    if(isset($config['setting']['redis']['port']) )         $port     = $config['setting']['redis']['port'];
//}
// var_dump($host ,$prefix ,$password ,$expire);die;
return [
    // 默认缓存驱动
    'default' => Env::get('cache.driver', 'redis'),

    // 缓存连接方式配置
    'stores'  => [
//      'file' => [
//          // 驱动方式
//          'type'       =>  'file',
//          // 缓存保存目录
//          'path'       => '',
//          // 缓存前缀
//          'prefix'     => '',
//          // 缓存有效期 0表示永久缓存
//          'expire'     => 0,
//          // 缓存标签前缀
//          'tag_prefix' => 'tag:',
//          // 序列化机制 例如 ['serialize', 'unserialize']
//          'serialize'  => [],
//      ],
        // 更多的缓存连接
        // redis缓存
        'redis'   =>  [
            // 驱动方式
            'type'       => 'redis',
            // 服务器地址
            'host'       => !empty($host)?$host:'127.0.0.1',
            //前缀
            'prefix'     => !empty($prefix)?$prefix:'longbing_',
            //密码
            'password'   => $password,
            //有效时长
            'expire'     => $expire,
            //端口
            'port'       => !empty($port)?$port:'6379'


        ],
    ],
];

