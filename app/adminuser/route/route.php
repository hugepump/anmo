<?php

use think\facade\Route;

//商城后端路由表
Route::group('admin', function () {


    Route::group('AdminUser', function () {

        Route::get('configInfo', 'AdminUser/configInfo');
        //绑定 user_is phone phone_code
        Route::post('configUpdate', 'AdminUser/configUpdate');
        //校验用户手机号 phone 发送验证码
        Route::post('bindsendPhoneCode', 'AdminUser/bindsendPhoneCode');
        //校验手机号验证码 phone_code phone
        Route::post('checkPhoneCode', 'AdminUser/checkPhoneCode');

        Route::post('delbind', 'AdminUser/delbind');

        Route::post('delBindsendPhoneCode', 'AdminUser/delBindsendPhoneCode');
        //发送管理员手机号验证码
        Route::post('sendAdminShortMsg', 'AdminUser/sendAdminShortMsg');
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

















