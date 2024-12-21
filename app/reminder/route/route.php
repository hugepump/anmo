<?php

use think\facade\Route;



Route::group('admin', function () {


    Route::get('AdminSetting/configInfo', 'AdminSetting/configInfo');

    Route::post('AdminSetting/configUpdate', 'AdminSetting/configUpdate');

    Route::get('AdminSetting/phoneRecordList', 'AdminSetting/phoneRecordList');


});
//支付
Route::any('CallBack/timingSendCalled', 'CallBack/timingSendCalled');
















