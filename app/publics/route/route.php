<?php

use think\facade\Route;

Route::group('HttpAsyn', function () {
    //发送异步消息
    Route::any('sendMessage', 'HttpAsyn/message');
    Route::any('message', 'HttpAsyn/message');
});

Route::group('tmpl', function () {
    //前端获取模版消息
    Route::post('tmplList', 'IndexTmpl/getTmplId');
    //后端获取|生成数据库模版消息
    Route::post('AdminTmpl/tmpList', 'AdminTmpl/tmpList');
    //后端获取|生成数据库模版消息(拥有模块权限的所有订阅消息)
    Route::post('AdminTmpl/tmpLists', 'AdminTmpl/tmpLists');
    //获取模版消息id
    Route::post('AdminTmpl/getTmplId', 'AdminTmpl/getTmplId');
    //需改模版消息
    Route::post('AdminTmpl/tmplUpdate', 'AdminTmpl/tmplUpdate');

});


Route::group('someThing', function () {
    //获取公司
    Route::post('getCompany', 'SomeThing/getCompany');
    //获取员工
    Route::post('getStaffInfo', 'SomeThing/getStaffInfo');
    //职位
    Route::post('jobList', 'SomeThing/jobList');
    //所有用户
    Route::post('getAllUser', 'SomeThing/getAllUser');
    //商品
    Route::post('goodsSelect', 'SomeThing/goodsSelect');

    Route::post('goodsSpeList', 'SomeThing/goodsSpeList');

    Route::post('getSpePrice', 'SomeThing/getSpePrice');

});

Route::group('Pay', function () {
    //支付成功通知
    Route::any('notify', 'Pay/notify');

});