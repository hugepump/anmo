<?php

use think\facade\Route;



Route::group('admin', function () {


    Route::get('AdminSetting/configInfo', 'AdminSetting/configInfo');

    Route::post('AdminSetting/configUpdate', 'AdminSetting/configUpdate');

    Route::get('AdminSetting/phoneRecordList', 'AdminSetting/phoneRecordList');


});
//支付
Route::any('CallBack/aliyunCallBack', 'CallBack/aliyunCallBack');
Route::any('CallBack/aliyunCallBackMoor', 'CallBack/aliyunCallBackMoor');
Route::any('CallBack/aliyunCallBackLook', 'CallBack/aliyunCallBackLook');
















