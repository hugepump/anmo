<?php

use think\facade\Route;

//商城后端路由表
Route::group('admin', function () {

    Route::group('AdminBroker', function () {

        Route::get('brokerList', 'AdminBroker/brokerList');

        Route::get('brokerInfo', 'AdminBroker/brokerInfo');

        Route::get('brokerDataList', 'AdminBroker/brokerDataList');
        //不是经纪人的用户 name
        Route::get('noBrokerUserList', 'AdminBroker/noBrokerUserList');

        Route::get('brokerCoachList', 'AdminBroker/brokerCoachList');

        Route::get('configInfo', 'AdminBroker/configInfo');

        Route::post('configUpdate', 'AdminBroker/configUpdate');

        Route::post('brokerAdd', 'AdminBroker/brokerAdd');

        Route::post('brokerUpdate', 'AdminBroker/brokerUpdate');

        Route::get('levelList', 'AdminBroker/levelList');

        Route::post('levelAdd', 'AdminBroker/levelAdd');

        Route::post('levelUpdate', 'AdminBroker/levelUpdate');
        //批量设置提成比例 id balance
        Route::post('setBrokerBalance', 'AdminBroker/setBrokerBalance');

        Route::get('levelInfo', 'AdminBroker/levelInfo');

    });


});


//后端路由表
Route::group('app', function () {

    Route::group('IndexBroker', function () {

        Route::get('brokerIndex', 'IndexBroker/brokerIndex');

        Route::get('brokerCashList', 'IndexBroker/brokerCashList');

        Route::get('brokerCoachList', 'IndexBroker/brokerCoachList');

        Route::get('resellerInvCoachQr', 'IndexBroker/resellerInvCoachQr');

        Route::get('walletList', 'IndexBroker/walletList');

        Route::get('adminList', 'IndexBroker/adminList');

        Route::post('applyWallet', 'IndexBroker/applyWallet');

    });
});

















