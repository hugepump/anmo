<?php

use think\facade\Route;

//商城后端路由表
Route::group('admin', function () {


    Route::group('AdminCard', function () {

        Route::get('configInfo', 'AdminCard/configInfo');

        Route::post('configUpdate', 'AdminCard/configUpdate');

        Route::get('cardList', 'AdminCard/cardList');

        Route::get('cardInfo', 'AdminCard/cardInfo');

        Route::post('cardAdd', 'AdminCard/cardAdd');

        Route::post('cardUpdate', 'AdminCard/cardUpdate');

        Route::post('cardStatusUpdate', 'AdminCard/cardStatusUpdate');

        Route::get('cardSelect', 'AdminCard/cardSelect');

        Route::get('discountCardWaterList', 'AdminCard/discountCardWaterList');





    });


});


//商城后端路由表
Route::group('app', function () {
    //首页
    Route::get('Index/index', 'Index/index');

    Route::group('IndexCard', function () {

        Route::get('cardList', 'IndexCard/cardList');

        Route::post('payOrder', 'IndexCard/payOrder');
        //rights_id level_id
        Route::get('orderList', 'IndexCard/orderList');

        Route::get('configInfo', 'IndexCard/configInfo');


    });

});
















