<?php
/**
 * Created by PhpStorm
 * User: shurong(龙兵科技)
 * Date: 2024/10/16
 * Time: 15:23
 * docs:
 */

use think\facade\Route;

//后端路由表
Route::group('admin', function () {

    Route::post('Admin/setConfig', 'Admin/setConfig');

    Route::get('Admin/setConfig', 'Admin/setConfig');

    Route::post('Admin/addContract', 'Admin/addContract');

    Route::get('Admin/getContractList', 'Admin/getContractList');

    Route::post('Admin/companySign', 'Admin/companySign');

    Route::post('Admin/delContract', 'Admin/delContract');
});

//前端路由表
Route::group('app', function () {

    Route::any('CallBack/faceCallBack', 'CallBack/faceCallBack');
});