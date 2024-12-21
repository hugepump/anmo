<?php

use think\facade\Route;

//商城后端路由表
Route::group('admin', function () {


    Route::group('AdminHotel', function () {

        Route::get('hotelList', 'AdminHotel/hotelList');
        Route::get('hotelInfo', 'AdminHotel/hotelInfo');
        Route::post('hotelAdd', 'AdminHotel/hotelAdd');
        Route::post('hotelUpdate', 'AdminHotel/hotelUpdate');
        Route::post('hotelStatusUpdate', 'AdminHotel/hotelStatusUpdate');

        Route::post('adminHotelUpdate', 'AdminHotel/adminHotelUpdate');
        Route::post('hotelCheck', 'AdminHotel/hotelCheck');
        Route::post('hotelDataCheck', 'AdminHotel/hotelDataCheck');
        Route::get('hotelUpdateInfo', 'AdminHotel/hotelUpdateInfo');


    });
});


Route::group('app', function () {

    Route::group('IndexHotel', function () {
        //title lng lat city 列表
        Route::get('hotelList', 'IndexHotel/hotelList');
        //id 详情
        Route::get('hotelInfo', 'IndexHotel/hotelInfo');



    });
});


















