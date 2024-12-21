<?php
use think\facade\Route;
/**
 * @model index
 * @author yangqi
 * @create time 2019年11月25日23:09:59
 * 
 */
Route::get('/', 'index/index');

Route::post('login', 'login/index');

Route::post('appLogin', 'login/appLogin');

Route::post('iosLogin', 'login/iosLogin');
Route::any('getCode', 'login/getCode');
Route::any('test', 'login/test');
Route::any('test1', 'login/test1');
Route::any('checkToken', 'login/checkToken');

Route::any('webLogin', 'login/webLogin');

Route::post('getLoginProtocol', 'login/getLoginProtocol');
//page code
Route::get('getWebConfig', 'login/getWebConfig');

Route::get('getConfig', 'login/getConfig');

Route::get('incCoachOrderNum', 'login/incCoachOrderNum');

Route::any('coachAccountLogin', 'login/coachAccountLogin');

Route::post('setCoachAccount', 'login/setCoachAccount');

Route::get('getCoachList', 'login/getCoachList');

Route::post('bindCoachAccount', 'login/bindCoachAccount');
Route::post('coachAccountSendShortMsg', 'login/coachAccountSendShortMsg');

