<?php

use think\facade\Route;

//商城后端路由表
Route::group('admin', function () {

    Route::group('AdminStore', function () {
        //分类
        Route::get('storeList', 'AdminStore/storeList');

        Route::get('storeSelect', 'AdminStore/storeSelect');

        Route::get('storeInfo', 'AdminStore/storeInfo');

        Route::post('storeAdd', 'AdminStore/storeAdd');

        Route::post('storeUpdate', 'AdminStore/storeUpdate');

        Route::get('cateList', 'AdminStore/cateList');

        Route::post('cateAdd', 'AdminStore/cateAdd');

        Route::post('cateUpdate', 'AdminStore/cateUpdate');

        Route::get('cateInfo', 'AdminStore/cateInfo');

        Route::get('cateSelect', 'AdminStore/cateSelect');

        Route::post('storeCheck', 'AdminStore/storeCheck');

        Route::post('adminStoreUpdate', 'AdminStore/adminStoreUpdate');

        Route::post('storeDataCheck', 'AdminStore/storeDataCheck');

        Route::get('storeUpdateInfo', 'AdminStore/storeUpdateInfo');

    });

    Route::group('AdminPackage', function () {

        Route::post('add', 'AdminPackage/add');

        Route::post('edit', 'AdminPackage/edit');

        Route::get('getInfo', 'AdminPackage/getInfo');

        Route::get('getList', 'AdminPackage/getList');

        Route::post('updateStatus', 'AdminPackage/updateStatus');
    });
});


//商城后端路由表
Route::group('app', function () {

    Route::group('IndexStore', function () {
        //分类
        Route::get('storeList', 'IndexStore/storeList');

        Route::get('storeCateList', 'IndexStore/storeCateList');

        Route::get('storeSelect', 'IndexStore/storeSelect');

        Route::get('storeInfo', 'IndexStore/storeInfo');

        Route::get('storeServiceList', 'IndexStore/storeServiceList');

        Route::get('commentList', 'IndexStore/commentList');

        Route::post('storeAdd', 'IndexStore/storeAdd');

        Route::post('storeUpdate', 'IndexStore/storeUpdate');

        Route::get('storePackList', 'IndexStore/storePackList');

        Route::get('storePackInfo', 'IndexStore/storePackInfo');

    });


});
























