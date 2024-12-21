<?php

use think\facade\Route;

//商城后端路由表
Route::group('admin', function () {
    //商品列表

    Route::group('AdminDynamicList', function () {

        Route::get('dynamicList', 'AdminDynamicList/dynamicList');

        Route::get('dynamicInfo', 'AdminDynamicList/dynamicInfo');

        Route::get('commentList', 'AdminDynamicList/commentList');

        Route::post('dynamicCheck', 'AdminDynamicList/dynamicCheck');

        Route::post('commentCheck', 'AdminDynamicList/commentCheck');

        Route::post('commentDel', 'AdminDynamicList/commentDel');

        Route::post('dynamicDel', 'AdminDynamicList/dynamicDel');
        //置顶取消置顶top 1 ，0
        Route::post('dynamicTop', 'AdminDynamicList/dynamicTop');

    });

});


//商城后端路由表
Route::group('app', function () {

    Route::group('Index', function () {

        Route::get('coachList', 'Index/coachList');




    });



});
















