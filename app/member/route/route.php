<?php

use think\facade\Route;

//商城后端路由表
Route::group('admin', function () {


    Route::group('AdminMember', function () {

        Route::get('levelList', 'AdminMember/levelList');

        Route::get('levelInfo', 'AdminMember/levelInfo');

        Route::post('levelAdd', 'AdminMember/levelAdd');

        Route::post('levelUpdate', 'AdminMember/levelUpdate');

        Route::post('levelStatusUpdate', 'AdminMember/levelStatusUpdate');

        Route::get('rightsList', 'AdminMember/rightsList');

        Route::get('rightsInfo', 'AdminMember/rightsInfo');

        Route::post('rightsUpdate', 'AdminMember/rightsUpdate');

        Route::get('configInfo', 'AdminMember/configInfo');
        //权益下拉框
        Route::get('rightsSelect', 'AdminMember/rightsSelect');

        Route::get('levelSelect', 'AdminMember/levelSelect');

        Route::post('configUpdate', 'AdminMember/configUpdate');

    });


});


//商城后端路由表
Route::group('app', function () {
    //首页
    Route::get('Index/index', 'Index/index');

    Route::group('IndexMember', function () {

        Route::get('index', 'IndexMember/index');

        Route::get('growthList', 'IndexMember/growthList');
        //rights_id level_id
        Route::get('rightsInfo', 'IndexMember/rightsInfo');

        Route::get('configInfo', 'IndexMember/configInfo');


    });

});
















