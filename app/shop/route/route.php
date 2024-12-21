<?php

use think\facade\Route;

//商城后端路由表
Route::group('admin', function () {
    //商品列表
    Route::post('Admin/login', 'Admin/login');
    //配置详情
    Route::post('AdminSetting/configInfo', 'AdminSetting/configInfo');
    //配置修改
    Route::post('AdminSetting/configUpdate', 'AdminSetting/configUpdate');
    //通知配置详情
    Route::post('AdminSetting/msgConfigInfo', 'AdminSetting/msgConfigInfo');
    //通知配置修改
    Route::post('AdminSetting/msgConfigUpdate', 'AdminSetting/msgConfigUpdate');

    Route::post('AdminSetting/payConfigInfo', 'AdminSetting/payConfigInfo');

    Route::post('AdminSetting/payConfigUpdate', 'AdminSetting/payConfigUpdate');
    //banne列表
    Route::post('AdminSetting/bannerList', 'AdminSetting/bannerList');
    //banner添加
    Route::post('AdminSetting/bannerAdd', 'AdminSetting/bannerAdd');
    //banner编辑
    Route::post('AdminSetting/bannerUpdate', 'AdminSetting/bannerUpdate');
    //banner详情（id）
    Route::get('AdminSetting/bannerInfo', 'AdminSetting/bannerInfo');
    //文章列表（title）
    Route::get('AdminSetting/articleList', 'AdminSetting/articleList');
    //添加文章
    Route::post('AdminSetting/articleAdd', 'AdminSetting/articleAdd');
    //编辑文章
    Route::post('AdminSetting/articleUpdate', 'AdminSetting/articleUpdate');
    //文章详情（id）
    Route::get('AdminSetting/articleInfo', 'AdminSetting/articleInfo');
    //文章下拉框
    Route::get('AdminSetting/articleSelect', 'AdminSetting/articleSelect');
    //修改密码（pass）
    Route::post('AdminSetting/updatePass', 'AdminSetting/updatePass');
    //楼长列表
    Route::get('AdminCap/capList', 'AdminCap/capList');
    //楼长数量
    Route::get('AdminCap/capCount', 'AdminCap/capCount');
    //修改楼长
    Route::post('AdminCap/capUpdate', 'AdminCap/capUpdate');
    //团长下拉框
    Route::get('AdminCap/capSelect', 'AdminCap/capSelect');
    //后台提现列表
    Route::get('AdminCap/walletList', 'AdminCap/walletList');
    //同意打款（id,status=2,online 1：线上，0线下）
    Route::post('AdminCap/walletPass', 'AdminCap/walletPass');
    //拒绝打款（id,status=3）
    Route::post('AdminCap/walletNoPass', 'AdminCap/walletNoPass');
    //财务管理
    Route::post('AdminCap/financeList', 'AdminCap/financeList');
    //商品列表
    Route::get('AdminGoods/goodsList', 'AdminGoods/goodsList');
    //审核商品数量
    Route::get('AdminGoods/goodsCount', 'AdminGoods/goodsCount');
    //审核详情
    Route::get('AdminGoods/shInfo', 'AdminGoods/shInfo');
    //审核商品详情
    Route::get('AdminGoods/shGoodsInfo', 'AdminGoods/shGoodsInfo');
    //同意|驳回申请 status 2 同意 3驳回
    Route::post('AdminGoods/shUpdate', 'AdminGoods/shUpdate');
    //用户列表
    Route::get('AdminUser/userList', 'AdminUser/userList');
    //退款列表
    Route::get('AdminOrder/refundOrderList', 'AdminOrder/refundOrderList');
    //订单列表
    Route::get('AdminOrder/orderList', 'AdminOrder/orderList');
    //订单详情
    Route::get('AdminOrder/orderInfo', 'AdminOrder/orderInfo');
    //退款详情
    Route::get('AdminOrder/refundOrderInfo', 'AdminOrder/refundOrderInfo');
    //拒绝退款
    Route::post('AdminOrder/noPassRefund', 'AdminOrder/noPassRefund');
    //同意退款
    Route::post('AdminOrder/passRefund', 'AdminOrder/passRefund');
    //某一天的数据统计
    Route::post('AdminOrder/dateCount', 'AdminOrder/dateCount');
    //订单导出
    Route::get('AdminExcel/orderList', 'AdminExcel/orderList');
    //财务导出
    Route::get('AdminExcel/dateCount', 'AdminExcel/dateCount');


});


//商城后端路由表
Route::group('app', function () {
    //用户授权
    Route::post('IndexUser/userUpdate', 'IndexUser/userUpdate');
    //申请团长
    Route::post('IndexUser/capApply', 'IndexUser/capApply');

    Route::get('IndexUser/userInfo', 'IndexUser/userInfo');
    //用户|团长个人中心
    Route::get('IndexUser/index', 'IndexUser/index');
    //个人团长信息
    Route::get('IndexUser/capInfo', 'IndexUser/capInfo');
    //用户地址列表
    Route::get('IndexUser/addressList', 'IndexUser/addressList');
    //地址详情
    Route::get('IndexUser/addressInfo', 'IndexUser/addressInfo');
    //添加地址
    Route::post('IndexUser/addressAdd', 'IndexUser/addressAdd');
    //编辑地址
    Route::post('IndexUser/addressUpdate', 'IndexUser/addressUpdate');
    //删除地址
    Route::post('IndexUser/addressDel', 'IndexUser/addressDel');

    Route::get('Index/index', 'Index/index');
    //获取配置信息
    Route::get('Index/configInfo', 'Index/configInfo');
    //文章详情(id)
    Route::get('Index/articleInfo', 'Index/articleInfo');
    //团长修改自己的个人信息
    Route::post('IndexCap/capUpdate', 'IndexCap/capUpdate');
    //团长商品分类列表
    Route::get('IndexCap/goodsCateList', 'IndexCap/goodsCateList');
    //团长添加商品分类
    Route::post('IndexCap/goodsCateAdd', 'IndexCap/goodsCateAdd');
    //团长编辑商品分类
    Route::post('IndexCap/goodsCateUpdate', 'IndexCap/goodsCateUpdate');
    //团长商品分类下拉框
    Route::post('IndexCap/goodsCateSelect', 'IndexCap/goodsCateSelect');
    //团长端商品列表（status）
    Route::get('IndexCap/goodsList', 'IndexCap/goodsList');
    //团长端添加商品
    Route::post('IndexCap/goodsAdd', 'IndexCap/goodsAdd');
    //团长端编辑商品
    Route::post('IndexCap/goodsUpdate', 'IndexCap/goodsUpdate');
    //团长端商品信息
    Route::get('IndexCap/goodsInfo', 'IndexCap/goodsInfo');
    //批量上下架
    Route::post('IndexCap/someGoodsUpdate', 'IndexCap/someGoodsUpdate');
    //团长端提交商品审核（goods_id）
    Route::post('IndexCap/subGoodsSh', 'IndexCap/subGoodsSh');
    //团长端商品上下架（id,status）
    Route::post('IndexCap/goodsStatusUpdate', 'IndexCap/goodsStatusUpdate');
    //商品各个状态下的数量
    Route::post('IndexCap/goodsCount', 'IndexCap/goodsCount');
    //团长核销订单（id）
    Route::post('IndexCap/hxOrder', 'IndexCap/hxOrder');
    //订单列表
    Route::get('IndexCap/orderList', 'IndexCap/orderList');
    //商品审核列表
    Route::get('IndexCap/shList', 'IndexCap/shList');

    Route::get('IndexCap/shInfo', 'IndexCap/shInfo');
    //团长端退款列表
    Route::get('IndexCap/refundOrderList', 'IndexCap/refundOrderList');
    //团长同意退款(id,price)
    Route::post('IndexCap/passRefund', 'IndexCap/passRefund');
    //团长拒绝退款(id)
    Route::post('IndexCap/noPassRefund', 'IndexCap/noPassRefund');
    //团长佣金信息
    Route::get('IndexCap/capCashInfo', 'IndexCap/capCashInfo');
    //提现记录
    Route::get('IndexCap/capCashList', 'IndexCap/capCashList');
    //申请提现(apply_cash,text)
    Route::post('IndexCap/applyWallet', 'IndexCap/applyWallet');

    Route::get('IndexGoods/indexCapList', 'IndexGoods/indexCapList');
    //选择楼长(cap_id)
    Route::post('IndexGoods/selectCap', 'IndexGoods/selectCap');
    //分类列表
    Route::get('IndexGoods/cateList', 'IndexGoods/cateList');
    //商品首页信息
    Route::get('IndexGoods/index', 'IndexGoods/index');
    //购物车信息
    Route::get('IndexGoods/carInfo', 'IndexGoods/carInfo');
    //商品列表
    Route::get('IndexGoods/goodsList', 'IndexGoods/goodsList');
    //商品详情
    Route::get('IndexGoods/goodsInfo', 'IndexGoods/goodsInfo');
    //添加购物车（goods_id,spe_id,goods_num = 1）
    Route::post('IndexGoods/addCar', 'IndexGoods/addCar');
    //删除购物车|减少购物车商品数量（id,goods_num=1）
    Route::post('IndexGoods/delCar', 'IndexGoods/delCar');
    //批量删除购物车（ID ：arr）
    Route::post('IndexGoods/delSomeCar', 'IndexGoods/delSomeCar');
    //
    Route::post('IndexGoods/carUpdate', 'IndexGoods/carUpdate');
    //
    Route::post('IndexOrder/payOrder', 'IndexOrder/payOrder');

    Route::get('IndexOrder/payOrderInfo', 'IndexOrder/payOrderInfo');
    //用户订单列表（pay_type,name）
    Route::get('IndexOrder/orderList', 'IndexOrder/orderList');
    //订单详情
    Route::get('IndexOrder/orderInfo', 'IndexOrder/orderInfo');
    //重新支付
    Route::post('IndexOrder/rePayOrder', 'IndexOrder/rePayOrder');
    //取消订单
    Route::post('IndexOrder/cancelOrder', 'IndexOrder/cancelOrder');
    //申请退款（order_id,list:['id','num']）
    Route::post('IndexOrder/applyOrder', 'IndexOrder/applyOrder');
    //取消退款
    Route::post('IndexOrder/cancelRefundOrder', 'IndexOrder/cancelRefundOrder');
    //用户端退款列表（name,status）
    Route::get('IndexOrder/refundOrderList', 'IndexOrder/refundOrderList');
    //退款详情
    Route::get('IndexOrder/refundOrderInfo', 'IndexOrder/refundOrderInfo');
    //刷新订单二维码(id)
    Route::post('IndexOrder/refreshQr', 'IndexOrder/refreshQr');





});


//支付
Route::any('IndexWxPay/returnPay', 'IndexWxPay/returnPay');



















