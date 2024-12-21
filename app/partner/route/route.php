<?php
/**
 * Created by PhpStorm
 * User: shurong(龙兵科技)
 * Date: 2024/10/25
 * Time: 11:41
 * docs:
 */

use think\facade\Route;

//后端路由表
Route::group('admin', function () {
    Route::group('type', function () {

        Route::post('typeAdd', 'AdminPartner/typeAdd');

        Route::post('typeEdit', 'AdminPartner/typeEdit');

        Route::get('typeList', 'AdminPartner/typeList');

        Route::get('typeSelect', 'AdminPartner/typeSelect');
    });

    Route::group('field', function () {

        Route::post('filedAdd', 'AdminPartner/filedAdd');

        Route::post('filedEdit', 'AdminPartner/filedEdit');

        Route::get('fieldList', 'AdminPartner/fieldList');
    });

    Route::group('order', function () {

        Route::get('getPartnerList', 'AdminPartner/getPartnerList');

        Route::post('partnerCheck', 'AdminPartner/partnerCheck');

        Route::get('getMoneyList', 'AdminPartner/getMoneyList');

        Route::get('getPartnerInfo', 'AdminPartner/getPartnerInfo');

        Route::post('partnerDel', 'AdminPartner/partnerDel');
    });
});

//前端路由表
Route::group('app', function () {

    Route::get('typeList', 'IndexPartner/typeList');

    Route::get('fieldList', 'IndexPartner/fieldList');

    Route::post('payOrder', 'IndexPartner/payOrder');

    Route::get('getConfig', 'IndexPartner/getConfig');

    Route::post('rePayOrder', 'IndexPartner/rePayOrder');

    Route::get('getPartnerList', 'IndexPartner/getPartnerList');

    Route::get('getPartnerInfo', 'IndexPartner/getPartnerInfo');

    Route::post('joinPartner', 'IndexPartner/joinPartner');

    Route::get('myPartner', 'IndexPartner/myPartner');

    Route::post('joinCheck', 'IndexPartner/joinCheck');

    Route::post('cancel', 'IndexPartner/cancel');

    Route::get('getJoinInfo', 'IndexPartner/getJoinInfo');

    Route::get('myPartnerInfo', 'IndexPartner/myPartnerInfo');

    Route::post('partnerSign', 'IndexPartner/partnerSign');

    Route::post('rePayJoin', 'IndexPartner/rePayJoin');
});
