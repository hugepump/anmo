<?php
use think\facade\Route;
/**
 * @model agent
 * @author yangqi
 * @create time 2019年11月25日23:09:59
 * 
 */
Route::get('index', 'Index/index');

Route::group('admin', function () {
    Route::post('auth', 'AdminAuthController/auth');
    Route::get('isWe7', 'AdminAuthController/isWe7');
    Route::get('unAuth', 'AdminAuthController/unAuth');
    Route::get('authStatus', 'AdminAuthController/AuthStatus');
    Route::get('list', 'AdminController/list');
    Route::post('addSubAdmin', 'AdminController/addSubAdmin');
    Route::post('updateSubAdmin', 'AdminController/updateSubAdmin');
    Route::post('delSubAdmin', 'AdminController/delSubAdmin');
    Route::post('bindApp', 'AdminController/bindApp');
    //代理商列表
    Route::any('AgentList/agentList', 'AgentList/agentList');
    //添加代理商
    Route::any('AgentList/agentAdd', 'AgentList/agentAdd');
    //编辑代理商
    Route::any('AgentList/agentUpdate', 'AgentList/agentUpdate');
    //代理商详情
    Route::any('AgentList/agentInfo', 'AgentList/agentInfo');
    //代理下拉选择框
    Route::any('AgentList/agentSelect', 'AgentList/agentSelect');
    //上传配置添加
    Route::any('OssConfig/configAdd', 'OssConfig/configAdd');
    //上传配置列表
    Route::any('OssConfig/configList', 'OssConfig/configList');
    //上传配置编辑
    Route::any('OssConfig/configUpdate', 'OssConfig/configUpdate');
    //上传配置详情
    Route::any('OssConfig/configInfo', 'OssConfig/configInfo');
    //上传配置删除
    Route::any('OssConfig/configDel', 'OssConfig/configDel');
    //上传配置下拉框
    Route::any('OssConfig/configSelect', 'OssConfig/configSelect');
    //添加代理商等级
    Route::any('AgentLevel/agentAdd', 'AgentLevel/agentAdd');
    //代理商等级列表
    Route::any('AgentLevel/levelList', 'AgentLevel/levelList');
    //代理商等级更新
    Route::any('AgentLevel/agentUpdate', 'AgentLevel/agentUpdate');
    //代理商等级详情
    Route::any('AgentLevel/agentInfo', 'AgentLevel/agentInfo');
    //代理商等级选择框
    Route::any('AgentLevel/levelSelect', 'AgentLevel/levelSelect');
    //删除代理商等级
    Route::any('AgentLevel/levelDel', 'AgentLevel/levelDel');
    //删除代理商等级
    Route::any('getUpgradeInfo', 'AppUpgrade/getUpgradeInfo');
    //获取当前系统版本和升级信息
    Route::any('upgrade', 'AppUpgrade/upgrade');
    //更新管理后台系统
    Route::any('uploadWxapp', 'AppUpgrade/uploadWxapp');

    Route::any('orderData', 'AritcleController/orderData');

});

Route::group('user' ,function() {
    Route::post('updatePasswd' ,'UserController/updateSelfPasswd');
});

Route::get('BossController/extendedOneYears' ,'BossController/extendedOneYears');
Route::post('BossController/coachList' ,'BossController/coachList');
Route::post('BossController/balanceDiscountCardWaterList' ,'BossController/balanceDiscountCardWaterList');


Route::group('CopyRightAgent', function () {
    Route::get('list', 'CopyRightAgentController/list');
    Route::get('getAll', 'CopyRightAgentController/getAll');
    Route::get('get', 'CopyRightAgentController/get');
    Route::post('create', 'CopyRightAgentController/create');
    Route::post('update', 'CopyRightAgentController/update');
    Route::post('destory', 'CopyRightAgentController/destroy');
});


Route::group('ConfigDefault', function () {
    Route::get('getOne', 'ConfigDefault/getOne');
    Route::post('update', 'ConfigDefault/update');
});
Route::get('AdminMassage/getMassageList' ,'AdminMassage/getMassageList');


Route::group('Permission', function () {
    Route::get('getAgentPermission', 'PermissionContrller/getAgentPermission');
});


Route::group('MessageConfig', function () {
    Route::post('delMessageByDay', 'MessageConfigController/delMessageByDay');
});


Route::group('AritcleConfig', function () {
    Route::get('list', 'AritcleController/list');
    Route::post('create', 'AritcleController/create');
    Route::post('update', 'AritcleController/update');
    Route::get('test', 'AritcleController/test');
});

Route::group('ActivityConfig', function () {
    Route::get('list', 'ActivityController/list');
    Route::post('create', 'ActivityController/create');
    Route::post('extendedOneYear', 'ActivityController/extendedOneYear');
});


Route::group('HouseConfig', function () {
    Route::any('list', 'HouseController/list');
    Route::post('create', 'HouseController/create');
    Route::post('extendedOneYear', 'HouseController/extendedOneYear');
});


Route::group('CopyRightAgentController', function () {
    Route::any('list', 'CopyRightAgentController/list');
    Route::post('create', 'CopyRightAgentController/create');
    Route::get('updateimg', 'CopyRightAgentController/updateimg');
    Route::get('destroy', 'CopyRightAgentController/destroy');
});


Route::group('BossConfig', function () {
    Route::get('list', 'BossController/list');
    Route::post('create', 'BossController/create');
    Route::post('extendedOneYear', 'BossController/extendedOneYear');
});


Route::group('App', function () {
    Route::get('list$', 'AppController/list');
    Route::get('get$', 'AppController/get');
    Route::get('getAuthList$', 'AppController/getAuthList');
    Route::post('create$', 'AppController/create');
    Route::post('update$', 'AppController/update');
    Route::post('delete$', 'AppController/delete');
	Route::get('getWxApp$', 'AppController/getWxApp');
    Route::get('isWe7$', 'AppController/isWe7');
    Route::post('redirectAppBackgroundToken$', 'AppController/redirectAppBackgroundToken');
    Route::any('websitebind$', 'AppController/websitebind');
    Route::post('updateApp$' ,'AppController/updateApp');
});


//新的授权列表路由
Route::group('AdmincardAuth', function () {
    Route::get('list', 'AdminAuthAppController/list');
    Route::post('create', 'AdminAuthAppController/create');
    Route::post('extendedOneYear', 'AdminAuthAppController/extendedOneYear');
});
