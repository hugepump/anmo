<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// [ 应用入口文件 ]
namespace think;
require_once("keygen.php");

require __DIR__ . '/../vendor/autoload.php';

header( "Access-Control-Allow-Origin: *" );
header( "Access-Control-Allow-Headers: content-type,token , autograph, Origin, X-Requested-With, Content-Type, Accept, Authorization" );
header( 'Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS,PATCH' );
// 执行HTTP应用并响应


$http = ( new App() )->http;



$response = $http->run();
$response->send();
$http->end( $response );
