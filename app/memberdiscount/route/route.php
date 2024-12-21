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

        Route::get('orderList', 'AdminCard/orderList');

        Route::get('orderInfo', 'AdminCard/orderInfo');





    });


});


//商城后端路由表
Route::group('app', function () {

    Route::group('IndexCard', function () {

        Route::get('cardList', 'IndexCard/cardList');
        //卡券折扣 card_id
        Route::get('cardDiscount', 'IndexCard/cardDiscount');
        //card_id pay_model coach_id
        Route::post('payOrder', 'IndexCard/payOrder');

        Route::get('orderList', 'IndexCard/orderList');

        Route::get('orderInfo', 'IndexCard/orderInfo');


    });

});
















