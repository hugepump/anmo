<?php
/**
 * Created by PhpStorm
 * User: shurong
 * Date: 2024/8/12
 * Time: 14:10
 * docs:
 */


use think\facade\Route;

//商城后端路由表
Route::group('admin', function () {
    //话术列表
    Route::get('scriptList', 'Admin/scriptList');
    //外呼线路列表
    Route::get('phoneList', 'Admin/phoneList');
    //话术列表
    Route::get('scriptListV2', 'Admin/scriptListV2');
    //外呼线路列表
    Route::get('phoneListV2', 'Admin/phoneListV2');
    //外呼
    Route::post('outbound', 'Admin/outbound');
});


Route::any('outboundCallBack', 'CallBack/outboundCallBack');