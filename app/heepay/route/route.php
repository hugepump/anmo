<?php

use think\facade\Route;

//商城后端路由表
Route::group('admin', function () {


    Route::group('AdminMember', function () {

        Route::get('memberList', 'AdminMember/memberList');

        Route::post('memberAdd', 'AdminMember/memberAdd');

        Route::post('fileAdd', 'AdminMember/fileAdd');

        Route::post('memberUpdate', 'AdminMember/memberUpdate');

        Route::post('memberStatusUpdate', 'AdminMember/memberStatusUpdate');

        Route::get('memberInfo', 'AdminMember/memberInfo');

        Route::get('configInfo', 'AdminMember/configInfo');

        Route::post('configUpdate', 'AdminMember/configUpdate');

        Route::post('adminPay', 'AdminMember/adminPay');

        Route::get('adminInfo', 'AdminMember/adminInfo');

        Route::any('findOrderPay', 'AdminMember/checkAdminPay');
        //查询平台支付是否完成 order_code  1支付 0未支付
        Route::get('checkAdminPay', 'AdminMember/checkAdminPay');

        Route::get('adminAdapay', 'AdminMember/adminAdapay');

        Route::get('adminHeepay', 'AdminMember/adminHeepay');
    });
});


//商城后端路由表
Route::group('app', function () {
    //首页
    Route::get('Index/index', 'Index/index');

    Route::group('IndexMember', function () {

        Route::post('memberAdd', 'IndexMember/memberAdd');

        Route::post('memberUpdate', 'IndexMember/memberUpdate');

        Route::post('memberStatusUpdate', 'IndexMember/memberStatusUpdate');

        Route::post('adminPay', 'IndexMember/adminPay');

        Route::get('memberInfo', 'IndexMember/memberInfo');
        //查询平台支付是否完成 order_code  1支付 0未支付
        Route::get('checkAdminPay', 'IndexMember/checkAdminPay');

    });
});

Route::any('CallBack/payCallback', 'CallBack/payCallback');
Route::any('CallBack/refundCallback', 'CallBack/refundCallback');
Route::any('CallBack/companyCallback', 'CallBack/companyCallback');
Route::any('CallBack/walletCallback', 'CallBack/walletCallback');
Route::any('CallBack/upOrderRefundCallback', 'CallBack/upOrderRefundCallback');
Route::any('CallBack/rechargeCallBack', 'CallBack/rechargeCallBack');
















