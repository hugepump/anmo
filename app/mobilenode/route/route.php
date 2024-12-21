<?php

use think\facade\Route;

//商城后端路由表
Route::group('admin', function () {
    //账号列表
    Route::get('AdminUser/adminList', 'AdminUser/adminList');
    //账号详情
    Route::get('AdminUser/adminInfo', 'AdminUser/adminInfo');
    //添加账号
    Route::post('AdminUser/adminAdd', 'AdminUser/adminAdd');
    //编辑账号
    Route::post('AdminUser/adminUpdate', 'AdminUser/adminUpdate');

    Route::post('AdminUser/adminStatusUpdate', 'AdminUser/adminStatusUpdate');

});

Route::group('app', function () {

    Route::group('IndexAdminOrder', function () {

        Route::get('index', 'IndexAdminOrder/index');

        Route::get('orderList', 'IndexAdminOrder/orderList');

        Route::get('orderInfo', 'IndexAdminOrder/orderInfo');

        Route::get('refundOrderList', 'IndexAdminOrder/refundOrderList');

        Route::get('refundOrderInfo', 'IndexAdminOrder/refundOrderInfo');

        Route::post('noPassRefund', 'IndexAdminOrder/noPassRefund');

        Route::post('passRefund', 'IndexAdminOrder/passRefund');

        Route::post('adminUpdateOrder', 'IndexAdminOrder/adminUpdateOrder');

        Route::post('orderChangeCoach', 'IndexAdminOrder/orderChangeCoach');

        Route::get('orderUpRecord', 'IndexAdminOrder/orderUpRecord');

        Route::get('orderChangeCoachList', 'IndexAdminOrder/orderChangeCoachList');

        Route::post('noticeUpdate', 'IndexAdminOrder/noticeUpdate');

        Route::any('getVirtualPhone', 'IndexAdminOrder/getVirtualPhone');

        Route::get('adminSelect', 'IndexAdminOrder/adminSelect');

        Route::post('passRefundV2', 'IndexAdminOrder/passRefundV2');

        Route::post('applyOrderRefund', 'IndexAdminOrder/applyOrderRefund');

        Route::get('canRefundOrderInfo', 'IndexAdminOrder/canRefundOrderInfo');
        //异常订单标示
        Route::get('abnOrderInfo', 'IndexAdminOrder/abnOrderInfo');

        Route::get('storeList', 'IndexAdminOrder/storeList');
        //选择门店 store_id
        Route::post('selectStore', 'IndexAdminOrder/selectStore');

        Route::get('hxCouponInfo', 'IndexAdminOrder/hxCouponInfo');

        Route::post('hxCoupon', 'IndexAdminOrder/hxCoupon');

        Route::get('couponHxRecordInfo', 'IndexAdminOrder/couponHxRecordInfo');

        Route::get('couponHxRecord', 'IndexAdminOrder/couponHxRecord');



    });


    Route::group('IndexAgentOrder', function () {

        Route::get('index', 'IndexAgentOrder/index');

        Route::get('orderList', 'IndexAgentOrder/orderList');

        Route::get('orderInfo', 'IndexAgentOrder/orderInfo');

        Route::get('refundOrderList', 'IndexAgentOrder/refundOrderList');

        Route::get('refundOrderInfo', 'IndexAgentOrder/refundOrderInfo');

        Route::post('noPassRefund', 'IndexAgentOrder/noPassRefund');

        Route::post('passRefund', 'IndexAgentOrder/passRefund');

        Route::post('adminUpdateOrder', 'IndexAgentOrder/adminUpdateOrder');

        Route::post('orderChangeCoach', 'IndexAgentOrder/orderChangeCoach');

        Route::get('orderUpRecord', 'IndexAgentOrder/orderUpRecord');

        Route::get('orderChangeCoachList', 'IndexAgentOrder/orderChangeCoachList');

        Route::post('noticeUpdate', 'IndexAgentOrder/noticeUpdate');

        Route::post('applyWallet', 'IndexAgentOrder/applyWallet');

        Route::get('walletList', 'IndexAgentOrder/walletList');

        Route::any('getVirtualPhone', 'IndexAgentOrder/getVirtualPhone');

        Route::get('coachList', 'IndexAgentOrder/coachList');

        Route::get('commList', 'IndexAgentOrder/commList');

        Route::get('adminSelect', 'IndexAgentOrder/adminSelect');

        Route::get('adminInfoData', 'IndexAgentOrder/adminInfoData');

        Route::post('coachApply', 'IndexAgentOrder/coachApply');

        Route::post('coachDataUpdate', 'IndexAgentOrder/coachDataUpdate');

        Route::post('coachUpdateAdmin', 'IndexAgentOrder/coachUpdateAdmin');

        Route::get('coachUserList', 'IndexAgentOrder/coachUserList');

        Route::get('coachInfo', 'IndexAgentOrder/coachInfo');

        Route::get('agentInviteQr', 'IndexAgentOrder/agentInviteQr');

        Route::get('storeSelect', 'IndexAgentOrder/storeSelect');

        Route::get('storeList', 'IndexAgentOrder/storeList');

        Route::post('passRefundV2', 'IndexAgentOrder/passRefundV2');

        Route::post('applyOrderRefund', 'IndexAgentOrder/applyOrderRefund');

        Route::get('canRefundOrderInfo', 'IndexAgentOrder/canRefundOrderInfo');

        Route::get('agentInvresellerQr', 'IndexAgentOrder/agentInvresellerQr');
      //异常订单标示
        Route::get('abnOrderInfo', 'IndexAgentOrder/abnOrderInfo');

        Route::post('rechargeCash', 'IndexAgentOrder/rechargeCash');

        Route::get('rechargeList', 'IndexAgentOrder/rechargeList');

        Route::get('agentInvAgentQr', 'IndexAgentOrder/agentInvAgentQr');

        Route::get('updateCoachCashList', 'IndexAgentOrder/updateCoachCashList');

        Route::get('getCity', 'IndexAgentOrder/getCity');

        Route::get('adminCarCashInfo', 'IndexAgentOrder/adminCarCashInfo');
        //选择门店 store_id
        Route::post('selectStore', 'IndexAgentOrder/selectStore');

        Route::get('storeDataList', 'IndexAgentOrder/storeDataList');

        Route::get('hxCouponInfo', 'IndexAgentOrder/hxCouponInfo');

        Route::post('hxCoupon', 'IndexAgentOrder/hxCoupon');

        Route::get('couponHxRecord', 'IndexAgentOrder/couponHxRecord');

        Route::get('couponHxRecordInfo', 'IndexAgentOrder/couponHxRecordInfo');
    });


});
























