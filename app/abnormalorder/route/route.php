<?php

use think\facade\Route;

//商城后端路由表
Route::group('admin', function () {


    Route::group('AdminProcess', function () {

        Route::get('processList', 'AdminProcess/processList');

        Route::get('getProcessStartInfo', 'AdminProcess/getProcessStartInfo');

        Route::post('processAdd', 'AdminProcess/processAdd');

        Route::post('stopProcess', 'AdminProcess/stopProcess');

    });

    Route::group('AdminOrder', function () {

        Route::post('orderAdd', 'AdminOrder/orderAdd');

        Route::post('updateOrder', 'AdminOrder/updateOrder');

        Route::post('handleOrder', 'AdminOrder/handleOrder');

        Route::get('pendingOrderList', 'AdminOrder/pendingOrderList');

        Route::get('alreadyOrderList', 'AdminOrder/alreadyOrderList');

        Route::get('orderInfo', 'AdminOrder/orderInfo');



    });
});


//后端路由表
Route::group('app', function () {

    Route::group('IndexMember', function () {

        Route::post('memberAdd', 'IndexMember/memberAdd');

    });
});

















