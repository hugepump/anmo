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

    Route::group('IndexDynamicCoach', function () {

        Route::post('dynamicAdd', 'IndexDynamicCoach/dynamicAdd');

        Route::get('dynamicList', 'IndexDynamicCoach/dynamicList');

        Route::get('dynamicData', 'IndexDynamicCoach/dynamicData');

        Route::get('dynamicInfo', 'IndexDynamicCoach/dynamicInfo');

        Route::post('dynamicUpdate', 'IndexDynamicCoach/dynamicUpdate');

        Route::post('dynamicDel', 'IndexDynamicCoach/dynamicDel');

        Route::get('thumbsList', 'IndexDynamicCoach/thumbsList');

        Route::get('commentList', 'IndexDynamicCoach/commentList');

        Route::get('followList', 'IndexDynamicCoach/followList');


    });


    Route::group('IndexDynamicList', function () {

        Route::post('dynamicAdd', 'IndexDynamicList/dynamicAdd');

        Route::get('dynamicList', 'IndexDynamicList/dynamicList');

        Route::get('followDynamicList', 'IndexDynamicList/followDynamicList');

        Route::get('dynamicData', 'IndexDynamicList/dynamicData');

        Route::get('dynamicInfo', 'IndexDynamicList/dynamicInfo');

        Route::get('commentList', 'IndexDynamicList/commentList');

        Route::get('getFollowData', 'IndexDynamicList/getFollowData');

        Route::post('commentAdd', 'IndexDynamicList/commentAdd');

        Route::post('commentDel', 'IndexDynamicList/commentDel');

        Route::post('followAddOrCancek', 'IndexDynamicList/followAddOrCancek');

        Route::post('thumbsAddOrCancek', 'IndexDynamicList/thumbsAddOrCancek');

        Route::get('followCoachList', 'IndexDynamicList/followCoachList');


    });

});
















