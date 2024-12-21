<?php

use think\facade\Route;

//商城后端路由表
Route::group('admin', function () {


    Route::group('Type', function () {

        Route::get('typeList', 'Type/typeList');

        Route::post('typeAdd', 'Type/typeAdd');

        Route::post('typeUpdate', 'Type/typeUpdate');

        Route::get('typeInfo', 'Type/typeInfo');

        Route::get('typeSelect', 'Type/typeSelect');


    });

});


//商城后端路由表
Route::group('app', function () {


    Route::group('IndexUser', function () {


        Route::get('index', 'IndexUser/index');

        Route::post('applyWallet', 'IndexUser/applyWallet');

        Route::get('walletList', 'IndexUser/walletList');

        Route::get('updateCoachCashList', 'IndexUser/updateCoachCashList');



    });
});

















