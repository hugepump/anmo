<?php
use think\facade\Route;
/**
 * @model admin
 * @author yangqi
 * @create time 2019年11月25日23:09:59
 * 
 */

Route::group('admin' ,function() {
    //系统设置
    Route::group('config' ,function(){
        //获取底部菜单信息
        Route::get('getTabbar' ,'Config/getTabbar');
        //设置底部菜单
        Route::post('setTabbar' ,'Config/setTabbar');
        //设置小程序
        Route::post('setAppConfig' ,'Config/setAppConfig');
        //获取小程序设置
        Route::get('getAppConfig' ,'Config/getAppConfig');
        //获取模版消息配置
//        Route::post('test' ,'Config/test');

        Route::get('test' ,'Config/test');
        //自动同步小程序模信息
        Route::get('async' ,'Config/autoServiceNoticeTemplate');
        //清除缓存
        Route::get('clear' ,'Config/clearCache');
        //获取上传配置
        Route::get('getOssConfig' ,'Config/getOssConfig');
        //更新上传配置
        Route::post('updateOssConfig' ,'Config/updateOssConfig');
        //微信上传配置详情
        Route::get('wxUploadInfo' ,'Config/wxUploadInfo');
        //微信上传配置修改
        Route::post('wxUploadUpdate' ,'Config/wxUploadUpdate');
        //上传小程序
        Route::post('uploadWxapp' ,'AppUpgrade/uploadWxapp');

        Route::post('uploadWxappCoach' ,'AppUpgrade/uploadWxappCoach');
        //小程序版本信息
        Route::post('wxappVersion' ,'AppUpgrade/getWxappVersion');
    });
    Route::group('Module' ,function() {

        Route::get('getModuleList' ,'Module/getModuleList');

    });
    //文件处理
    Route::group('file' ,function() {
    //创建分组
        Route::post('createGroup' ,'File/createGroup');
        //获取分组列表
        Route::get('listGroup' ,'File/listGroup');
        //更新分组
        Route::post('updateGroup' ,'File/updateGroup');
        //删除分组
        Route::post('delGroup' ,'File/delGroup');
        Route::post('delAllGroup' ,'File/delAllGroup');

        //上传文件
        Route::post('uploadFiles' ,'File/uploadFiles');

        //上传文件
        Route::post('uploadFile' ,'File/uploadFile');

        Route::get('uploadConfig' ,'File/uploadConfig');

        Route::post('addFile' ,'File/addFile');
        //获取文件列表
        Route::get('listFile' ,'File/listFile');
        //获取文件
        Route::get('getFile/:file_id', 'File/getFile');
        //删除文件
        Route::post('delFile' ,'File/delFile');

        //移动分组(file_id 一维数组可批量传，group_id:分组id)
        Route::post('moveGroup' ,'File/moveGroup');
    });

    Route::group('UserList' ,function() {

        Route::get('getModuleList' ,'UserList/getModuleList');

    });

    Route::get('checkAuthDelFile' ,'index/checkAuthDelFile');

    Route::get('Coupon/couponquery' ,'Coupon/couponquery');

    Route::get('createData' ,'index/createData');

    Route::get('unlinkData' ,'index/unlinkData');
    //更新后台模块数据信息
    Route::get('updateModel' ,'Update/update');

    Route::get('getWxinPay' ,'Menu/getWxinPay');

    Route::get('checkAuth' ,'Admin/checkAuth');

    Route::get('giveAuth' ,'Admin/giveAuth');

    Route::get('isSaas' ,'Admin/isSaas');


});

Route::group('app' ,function() {
    //获取底部菜单
    Route::get('getTabbar' ,'Wx/getTabbar');
    Route::group('wx' ,function() {
        //上传文件
        Route::post('uploadFiles' ,'WxFile/uploadFiles');
        Route::post('uploadFile' ,'WxFile/uploadFile');
        Route::get('getTabbar' ,'Wx/getTabbar');
});
});
//获取底部菜单
Route::get('getTabbar' ,'Wx/getTabbar');
Route::group('wx' ,function() {
    //上传文件
    Route::post('uploadFiles' ,'WxFile/uploadFiles');
    Route::post('uploadFile' ,'WxFile/uploadFile');
    Route::get('getTabbar' ,'Wx/getTabbar');
});
//By.jingshuixian  跳转代理管理端
Route::get('', function (){

    header('Location:'. '/agent/index');
    header('Refresh: ');

});
