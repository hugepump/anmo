<?php

use think\facade\Route;

//商城后端路由表
Route::group('admin', function () {

    //角色列表
    Route::get('AdminUser/roleList', 'AdminUser/roleList');

    Route::get('AdminUser/roleSelect', 'AdminUser/roleSelect');
    //添加角色
    Route::post('AdminUser/roleAdd', 'AdminUser/roleAdd');
    //编辑角色
    Route::post('AdminUser/roleUpdate', 'AdminUser/roleUpdate');
    //角色详情
    Route::get('AdminUser/roleInfo', 'AdminUser/roleInfo');
    //给账号分配角色（多选）
    Route::post('AdminUser/adminRoleAdd', 'AdminUser/adminRoleAdd');
    //账号所匹配的角色详情
    Route::get('AdminUser/adminRoleInfo', 'AdminUser/adminRoleInfo');
    //账号所匹配的角色的节点详情
    Route::get('AdminUser/adminNodeInfo', 'AdminUser/adminNodeInfo');
    //账号列表
    Route::get('AdminUser/adminList', 'AdminUser/adminList');
    //账号详情
    Route::get('AdminUser/adminInfo', 'AdminUser/adminInfo');
    //添加账号
    Route::post('AdminUser/adminAdd', 'AdminUser/adminAdd');
    //编辑账号
    Route::post('AdminUser/adminUpdate', 'AdminUser/adminUpdate');

    Route::get('AdminUser/logList', 'AdminUser/logList');




});


























