<?php

use think\facade\Route;

//商城后端路由表
Route::group('admin', function () {
    //商品列表
    Route::post('Admin/login', 'Admin/login');
    Route::any('Admin/test', 'Admin/test');

    Route::get('AdminApi/checkCash', 'AdminApi/checkCash');
    Route::get('AdminApi/channelCheckCash', 'AdminApi/channelCheckCash');
    Route::get('AdminApi/adminList', 'AdminApi/adminList');
    Route::get('AdminApi/resellerList', 'AdminApi/resellerList');
    Route::post('Admin/drawCashInfo', 'Admin/drawCashInfo');

    Route::get('Admin/getMapCoach', 'Admin/getMapCoach');

    Route::get('Admin/citySelect', 'Admin/citySelect');

    Route::get('Admin/coachInfo', 'Admin/coachInfo');

    Route::get('Admin/authPhone', 'Admin/authPhone');

    Route::post('Admin/sendAuthCode', 'Admin/sendAuthCode');

    Route::post('Admin/sendAuthCodeV2', 'Admin/sendAuthCodeV2');

    Route::get('Admin/excel', 'Admin/excel');

    Route::get('Admin/getConfig', 'Admin/getConfig');

    Route::get('Admin/getW7TmpV2', 'Admin/getW7TmpV2');

    Route::group('Admin', function () {

        Route::get('orderData', 'AdminFinance/orderData');

        Route::get('dataScreen', 'AdminFinance/dataScreen');

        Route::get('policeList', 'AdminFinance/policeList');
    });

    //配置详情
    Route::post('AdminSetting/configInfo', 'AdminSetting/configInfo');
    //车费配置详情
    Route::get('AdminSetting/carConfigInfo', 'AdminSetting/carConfigInfo');
    //编辑车费配置
    Route::post('AdminSetting/carConfigUpdate', 'AdminSetting/carConfigUpdate');
    //配置修改
    Route::post('AdminSetting/configUpdate', 'AdminSetting/configUpdate');

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
    //修改密码（pass）
    Route::post('AdminSetting/updatePass', 'AdminSetting/updatePass');
    //评价标签列表
    Route::get('AdminSetting/lableList', 'AdminSetting/lableList');
    //评价标签详情
    Route::get('AdminSetting/lableInfo', 'AdminSetting/lableInfo');
    //添加评价标签
    Route::post('AdminSetting/lableAdd', 'AdminSetting/lableAdd');
    //编辑评价标签
    Route::post('AdminSetting/lableUpdate', 'AdminSetting/lableUpdate');

    Route::get('AdminSetting/adminList', 'AdminSetting/adminList');

    Route::get('AdminSetting/adminInfo', 'AdminSetting/adminInfo');

    Route::post('AdminSetting/adminAdd', 'AdminSetting/adminAdd');

    Route::post('AdminSetting/adminUpdate', 'AdminSetting/adminUpdate');

    Route::post('AdminSetting/adminStatusUpdate', 'AdminSetting/adminStatusUpdate');

    Route::get('AdminSetting/userSelect', 'AdminSetting/userSelect');

    Route::get('AdminSetting/cityList', 'AdminSetting/cityList');

    Route::get('AdminSetting/cityInfo', 'AdminSetting/cityInfo');

    Route::get('AdminSetting/citySelect', 'AdminSetting/citySelect');

    Route::post('AdminSetting/cityAdd', 'AdminSetting/cityAdd');

    Route::post('AdminSetting/cityUpdate', 'AdminSetting/cityUpdate');

    Route::get('AdminSetting/adminSelect', 'AdminSetting/adminSelect');

    Route::get('AdminSetting/getSaasAuth', 'AdminSetting/getSaasAuth');

    Route::get('AdminSetting/helpConfigInfo', 'AdminSetting/helpConfigInfo');

    Route::post('AdminSetting/helpConfigUpate', 'AdminSetting/helpConfigUpate');

    Route::get('AdminSetting/sendMsgConfigInfo', 'AdminSetting/sendMsgConfigInfo');

    Route::post('AdminSetting/sendMsgConfigUpdate', 'AdminSetting/sendMsgConfigUpdate');

    Route::get('AdminSetting/shortCodeConfigInfo', 'AdminSetting/shortCodeConfigInfo');

    Route::post('AdminSetting/shortCodeConfigUpdate', 'AdminSetting/shortCodeConfigUpdate');

    Route::get('AdminSetting/addClockInfo', 'AdminSetting/addClockInfo');

    Route::get('AdminSetting/provinceList', 'AdminSetting/provinceList');

    Route::post('AdminSetting/addClockUpdate', 'AdminSetting/addClockUpdate');

    Route::get('AdminSetting/agentApplyList', 'AdminSetting/agentApplyList');

    Route::post('AdminSetting/agentApplyCheck', 'AdminSetting/agentApplyCheck');

    Route::get('AdminSetting/diyInfo', 'AdminSetting/diyInfo');

    Route::get('AdminSetting/getTabbar', 'AdminSetting/getTabbar');

    Route::get('AdminSetting/getFunctionPageList', 'AdminSetting/getFunctionPageList');

    Route::get('AdminSetting/getFunctionPageInfo', 'AdminSetting/getFunctionPageInfo');

    Route::post('AdminSetting/diyUpdate', 'AdminSetting/diyUpdate');

    Route::get('AdminSetting/creditConfigInfo', 'AdminSetting/creditConfigInfo');

    Route::post('AdminSetting/creditConfigUpdate', 'AdminSetting/creditConfigUpdate');

    Route::get('AdminSetting/getUpRecord', 'AdminSetting/getUpRecord');

    Route::post('AdminSetting/base64ToPngClouds', 'AdminSetting/base64ToPngClouds');
    //技师列表
    Route::get('AdminCoach/coachList', 'AdminCoach/coachList');

    Route::get('AdminCoach/coachDataList', 'AdminCoach/coachDataList');
    //技师详情
    Route::get('AdminCoach/coachInfo', 'AdminCoach/coachInfo');
    //技师审核(status2通过,3拒绝,sh_text)
    Route::post('AdminCoach/coachUpdate', 'AdminCoach/coachUpdate');
    //技师等级列表
    Route::get('AdminCoach/levelList', 'AdminCoach/levelList');
    //添加技师等级
    Route::post('AdminCoach/levelAdd', 'AdminCoach/levelAdd');
    //编辑技师等级
    Route::post('AdminCoach/levelUpdate', 'AdminCoach/levelUpdate');
    //技师等级详情
    Route::get('AdminCoach/levelInfo', 'AdminCoach/levelInfo');
    //技师提现申请列表(type1是服务费提现，2是车费)
    Route::get('AdminCoach/walletList', 'AdminCoach/walletList');
    //提现详情
    Route::get('AdminCoach/walletInfo', 'AdminCoach/walletInfo');
    //通过提现(online:1线上，0线下)
    Route::post('AdminCoach/walletPass', 'AdminCoach/walletPass');
    //拒绝提现
    Route::post('AdminCoach/walletNoPass', 'AdminCoach/walletNoPass');
    //报警列表
    Route::get('AdminCoach/policeList', 'AdminCoach/policeList');
    //编辑报警
    Route::post('AdminCoach/policeUpdate', 'AdminCoach/policeUpdate');

    Route::post('AdminCoach/coachDataUpdate', 'AdminCoach/coachDataUpdate');
    //审核认证状态
    Route::post('AdminCoach/coachAuthCheck', 'AdminCoach/coachAuthCheck');

    Route::post('AdminCoach/coachUpdateAdmin', 'AdminCoach/coachUpdateAdmin');
    //修改技师余额 coach_id cash is_add
    Route::post('AdminCoach/updateCoachCash', 'AdminCoach/updateCoachCash');
    //技师佣金修改记录 coach_id
    Route::get('AdminCoach/updateCoachCashList', 'AdminCoach/updateCoachCashList');

    Route::get('AdminCoach/coachServiceList', 'AdminCoach/coachServiceList');

    Route::get('AdminCoach/coachNoServiceList', 'AdminCoach/coachNoServiceList');

    Route::post('AdminCoach/addCoachService', 'AdminCoach/addCoachService');

    Route::post('AdminCoach/delCoachService', 'AdminCoach/delCoachService');

    Route::post('AdminCoach/updateCoachServicePrice', 'AdminCoach/updateCoachServicePrice');

    Route::post('AdminCoach/addWatermarkImg', 'AdminCoach/addWatermarkImg');

    Route::get('AdminCoach/WatermarkImgInfo', 'AdminCoach/WatermarkImgInfo');

    Route::group('AdminCoach', function () {

        Route::get('getTime', 'AdminCoach/getTime');

        Route::post('setTimeConfig', 'AdminCoach/setTimeConfig');

        Route::get('coachNoticeList', 'AdminCoach/coachNoticeList');

        Route::get('coachNoticeInfo', 'AdminCoach/coachNoticeInfo');
        Route::post('coachNoticeAdd', 'AdminCoach/coachNoticeAdd');
        Route::post('coachNoticeUpdate', 'AdminCoach/coachNoticeUpdate');

        Route::get('dayText', 'AdminCoach/dayText');
        Route::get('timeText', 'AdminCoach/timeText');
        Route::post('updateCoachAddress', 'AdminCoach/updateCoachAddress');

        Route::get('coachWorkList', 'AdminCoach/coachWorkList');

    });
    //优惠券列表(搜索：name)
    Route::get('AdminCoupon/couponList', 'AdminCoupon/couponList');
    //优惠券详情（id）
    Route::get('AdminCoupon/couponInfo', 'AdminCoupon/couponInfo');
    //添加优惠券
    Route::post('AdminCoupon/couponAdd', 'AdminCoupon/couponAdd');
    //编辑优惠券
    Route::post('AdminCoupon/couponUpdate', 'AdminCoupon/couponUpdate');
    //活动详情
    Route::get('AdminCoupon/couponAtvInfo', 'AdminCoupon/couponAtvInfo');
    //编辑活动
    Route::post('AdminCoupon/couponAtvUpdate', 'AdminCoupon/couponAtvUpdate');
    //后台派发卡券(coupon_id,user_id)
    Route::post('AdminCoupon/couponRecordAdd', 'AdminCoupon/couponRecordAdd');

    Route::get('AdminCoupon/couponData', 'AdminCoupon/couponData');

    Route::get('AdminCoupon/couponHxRecordList', 'AdminCoupon/couponHxRecordList');

    Route::post('AdminCoupon/couponCancel', 'AdminCoupon/couponCancel');


    //储值充值卡列表
    Route::get('AdminBalance/cardList', 'AdminBalance/cardList');
    //user_id
    Route::get('AdminBalance/payWater', 'AdminBalance/payWater');
    //储值充值卡列表
    Route::post('AdminBalance/cardAdd', 'AdminBalance/cardAdd');
    //编辑充值卡
    Route::post('AdminBalance/cardUpdate', 'AdminBalance/cardUpdate');
    //充值卡详情
    Route::get('AdminBalance/cardInfo', 'AdminBalance/cardInfo');
    //储值订单列表
    Route::get('AdminBalance/orderList', 'AdminBalance/orderList');
    //充值订单详情
    Route::get('AdminBalance/orderInfo', 'AdminBalance/orderInfo');

    Route::get('AdminBalance/orderInfo', 'AdminBalance/orderInfo');

    Route::get('AdminBalance/balanceDiscountOrderList', 'AdminBalance/balanceDiscountOrderList');

    Route::post('AdminBalance/payBalanceOrder', 'AdminBalance/payBalanceOrder');

    Route::group('AdminService', function () {
        //服务列表(搜索：name)
        Route::get('serviceList', 'AdminService/serviceList');
        //服务详情
        Route::get('serviceInfo', 'AdminService/serviceInfo');
        //添加服务
        Route::post('serviceAdd', 'AdminService/serviceAdd');
        //编辑服务|上下架删除
        Route::post('serviceUpdate', 'AdminService/serviceUpdate');

        Route::post('checkStoreGoods', 'AdminService/checkStoreGoods');

        Route::get('cateList', 'AdminService/cateList');

        Route::post('cateAdd', 'AdminService/cateAdd');

        Route::post('cateUpdate', 'AdminService/cateUpdate');

        Route::get('cateInfo', 'AdminService/cateInfo');

        Route::get('cateSelect', 'AdminService/cateSelect');

        Route::get('positionList', 'AdminService/positionList');

        Route::get('positionSelect', 'AdminService/positionSelect');

        Route::get('positionInfo', 'AdminService/positionInfo');

        Route::post('positionAdd', 'AdminService/positionAdd');

        Route::post('positionUpdate', 'AdminService/positionUpdate');
        //服务保障列表(分页)
        Route::get('guaranteeList', 'AdminService/guaranteeList');
        //服务保障添加
        Route::post('guaranteeAdd', 'AdminService/guaranteeAdd');
        //服务保障编辑
        Route::post('guaranteeUpdate', 'AdminService/guaranteeUpdate');
        //服务保障列表(不分页)
        Route::get('guaranteeListNoPage', 'AdminService/guaranteeListNoPage');
    });

    //后台提现列表
    Route::get('AdminCoach/walletList', 'AdminCoach/walletList');
    //同意打款（id,status=2,online 1：线上，0线下）
    Route::post('AdminCoach/walletPass', 'AdminCoach/walletPass');
    //拒绝打款（id,status=3）
    Route::post('AdminCoach/walletNoPass', 'AdminCoach/walletNoPass');
    //财务管理
    Route::get('AdminCoach/financeList', 'AdminCoach/financeList');

    Route::get('AdminCoach/userLabelList', 'AdminCoach/userLabelList');


    Route::get('AdminCoach/coachUserList', 'AdminCoach/coachUserList');

    Route::get('AdminCoach/coachCashData', 'AdminCoach/coachCashData');

    Route::post('AdminCoach/coachAdd', 'AdminCoach/coachAdd');

    Route::post('AdminCoach/coachUpdateCheck', 'AdminCoach/coachUpdateCheck');

    Route::get('AdminCoach/coachUpdateInfo', 'AdminCoach/coachUpdateInfo');

    Route::post('AdminCoach/updateCoachBroker', 'AdminCoach/updateCoachBroker');

    Route::post('AdminCoach/setCoachAccount', 'AdminCoach/setCoachAccount');

    Route::post('AdminCoach/sendShortMsg', 'AdminCoach/sendShortMsg');
    Route::post('AdminCoach/changeUser', 'AdminCoach/changeUser');

    Route::group('AdminCoach', function () {
        //服务列表(搜索：name)
        Route::get('coachTypeList', 'AdminCoach/coachTypeList');

        Route::get('coachTypeInfo', 'AdminCoach/coachTypeInfo');

        Route::get('coachTypeSelect', 'AdminCoach/coachTypeSelect');
        //服务详情
        Route::post('coachTypeAdd', 'AdminCoach/coachTypeAdd');

        Route::post('coachTypeUpdate', 'AdminCoach/coachTypeUpdate');
        //添加个性标签 title
        Route::post('coachIconAdd', 'AdminCoach/coachIconAdd');
        //编辑个性标签 id title
        Route::post('coachIconUpdate', 'AdminCoach/coachIconUpdate');
        //个性标签列表 limit
        Route::get('coachIconList', 'AdminCoach/coachIconList');

        Route::get('coachIconSelect', 'AdminCoach/coachIconSelect');
        //添加岗位标签 title
        Route::post('stationIconAdd', 'AdminCoach/stationIconAdd');
        //编辑岗位标签 id title
        Route::post('stationIconUpdate', 'AdminCoach/stationIconUpdate');
        //岗位标签列表 limit
        Route::get('stationIconList', 'AdminCoach/stationIconList');
        //岗位标签列表
        Route::get('stationIconSelect', 'AdminCoach/stationIconSelect');

    });
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

    Route::get('AdminUser/getAttestationInfo', 'AdminUser/getAttestationInfo');

    Route::get('AdminUser/getCompanyVerifyUrl', 'AdminUser/getCompanyVerifyUrl');

    Route::get('AdminUser/agreementList', 'AdminUser/agreementList');

    Route::get('AdminUser/noAgreementCoach', 'AdminUser/noAgreementCoach');

    Route::post('AdminUser/delAgreement', 'AdminUser/delAgreement');

    Route::post('AdminUser/Extsign', 'AdminUser/Extsign');

    Route::post('AdminUser/test', 'AdminUser/test');

    Route::post('AdminUser/updateUserGrowth', 'AdminUser/updateUserGrowth');

    Route::get('AdminUser/getAdminAliAccount', 'AdminUser/getAdminAliAccount');

    Route::get('AdminUser/userGrowthList', 'AdminUser/userGrowthList');
    //代理商财务
    Route::get('AdminUser/adminFinanceList', 'AdminUser/adminFinanceList');

    Route::group('AdminOrder', function () {

        Route::get('cateList', 'AdminOrder/cateList');
        //退款列表
        Route::get('refundOrderList', 'AdminOrder/refundOrderList');
        //订单列表
        Route::get('orderList', 'AdminOrder/orderList');

        Route::get('orderTotalData', 'AdminOrder/orderTotalData');
        //订单详情
        Route::get('orderInfo', 'AdminOrder/orderInfo');
        //退款详情
        Route::get('refundOrderInfo', 'AdminOrder/refundOrderInfo');
        //拒绝退款
        Route::post('noPassRefund', 'AdminOrder/noPassRefund');
        //同意退款
        Route::post('passRefund', 'AdminOrder/passRefund');
        //订单评价列表
        Route::get('commentList', 'AdminOrder/commentList');
        //编辑订单评价
        Route::post('commentUpdate', 'AdminOrder/commentUpdate');
        //评价标签列表
        Route::get('commentLableList', 'AdminOrder/commentLableList');
        //评价标签详情
        Route::get('commentLableInfo', 'AdminOrder/commentLableInfo');
        //添加评价标签
        Route::post('commentLableAdd', 'AdminOrder/commentLableAdd');
        //编辑评价标签
        Route::post('commentLableUpdate', 'AdminOrder/commentLableUpdate');
        //提示列表(type,have_look,start_time,end_time)
        Route::get('noticeList', 'AdminOrder/noticeList');
        //编辑提示()
        Route::post('noticeUpdate', 'AdminOrder/noticeUpdate');
        //未查看的数量
        Route::post('noLookCount', 'AdminOrder/noLookCount');
        //全部已读
        Route::post('allLook', 'AdminOrder/allLook');

        Route::post('adminUpdateOrder', 'AdminOrder/adminUpdateOrder');

        Route::post('orderChangeCoach', 'AdminOrder/orderChangeCoach');

        Route::get('orderChangeCoachList', 'AdminOrder/orderChangeCoachList');

        Route::get('orderUpRecord', 'AdminOrder/orderUpRecord');

        Route::get('lableList', 'AdminOrder/lableList');

        Route::post('addComment', 'AdminOrder/addComment');

        Route::get('financeDetailedList', 'AdminOrder/financeDetailedList');
        //order_id
        Route::get('coachTrajectory', 'AdminOrder/coachTrajectory');

        Route::get('companyWater', 'AdminOrder/companyWater');

        Route::get('canRefundOrderInfo', 'AdminOrder/canRefundOrderInfo');

        Route::post('applyOrderRefund', 'AdminOrder/applyOrderRefund');

        Route::post('passRefundV2', 'AdminOrder/passRefundV2');

        Route::get('orderControlRecord', 'AdminOrder/orderControlRecord');

        Route::get('coachCommissionInfo', 'AdminOrder/coachCommissionInfo');

        Route::get('getFinanceDetailedObj', 'AdminOrder/getFinanceDetailedObj');

        Route::get('noPayRecordList', 'AdminOrder/noPayRecordList');

        Route::post('nopayNotice', 'AdminOrder/nopayNotice');

        Route::post('updateOrderMobile', 'AdminOrder/updateOrderMobile');

        Route::post('updateOrderAddress', 'AdminOrder/updateOrderAddress');

        Route::get('financeDetailedListTotal', 'AdminOrder/financeDetailedListTotal');

    });

    Route::group('AdminExcel', function () {

        Route::get('balanceOrderList', 'AdminExcel/balanceOrderList');

        Route::get('userList', 'AdminExcel/userList');
        //订单导出
        Route::get('orderList', 'AdminExcel/orderList');

        Route::get('companyWater', 'AdminExcel/companyWater');

        Route::get('financeDetailedList', 'AdminExcel/financeDetailedList');
        //财务导出
        Route::get('dateCount', 'AdminExcel/dateCount');

        Route::get('commList', 'AdminExcel/commList');

        Route::get('subDataList', 'AdminExcel/subDataList');

        Route::get('coachDataList', 'AdminExcel/coachDataList');

        Route::get('walletList', 'AdminExcel/walletList');

        Route::get('noPayRecordList', 'AdminExcel/noPayRecordList');

        Route::get('coachFinanceList', 'AdminExcel/coachFinanceList');

        Route::get('adminFinanceList', 'AdminExcel/adminFinanceList');

        Route::get('brokerFinanceList', 'AdminExcel/brokerFinanceList');
        Route::get('channelFinanceList', 'AdminExcel/channelFinanceList');
        Route::get('salesmanFinanceList', 'AdminExcel/salesmanFinanceList');
        Route::get('resellerFinanceList', 'AdminExcel/resellerFinanceList');
        //折扣储值订单导出 massage/admin
        Route::get('balanceDiscountOrderList', 'AdminExcel/balanceDiscountOrderList');
    });
    //打印机详情(id)
    Route::get('AdminPrinter/printerInfo', 'AdminPrinter/printerInfo');
    //编辑打印机
    Route::post('AdminPrinter/printerUpdate', 'AdminPrinter/printerUpdate');
    //打印机列表
    Route::get('AdminPrinter/printerList', 'AdminPrinter/printerList');
    //打印机添加
    Route::post('AdminPrinter/printerAdd', 'AdminPrinter/printerAdd');
    //佣金记录
    Route::post('AdminUser/commList', 'AdminUser/commList');

    Route::get('AdminUser/commList', 'AdminUser/cashList');
    //phone
    Route::get('AdminUser/userSelectByPhone', 'AdminUser/userSelectByPhone');

    Route::post('AdminUser/adminUpdateCoachCommisson', 'AdminUser/adminUpdateCoachCommisson');

    Route::post('AdminUser/BlockUser', 'AdminUser/BlockUser');

    Route::get('AdminUser/coachCommentUserData', 'AdminUser/coachCommentUserData');

    Route::get('AdminUser/cashList', 'AdminUser/cashList');

    Route::get('AdminUser/userInfo', 'AdminUser/userInfo');

    Route::post('AdminUser/delUserLabel', 'AdminUser/delUserLabel');

    Route::post('AdminUser/applyWallet', 'AdminUser/applyWallet');

    Route::get('AdminUser/userOrderList', 'AdminUser/userOrderList');

    Route::group('AdminUser', function () {
        //用户优惠券 user_id
        Route::get('userCouponList', 'AdminUser/userCouponList');
        //用户屏蔽技师 user_id
        Route::get('shieldCoachList', 'AdminUser/shieldCoachList');

        Route::get('getUpgradeInfo', 'AdminUser/getUpgradeInfo');

        Route::post('updateUserAddress', 'AdminUser/updateUserAddress');
        //后端储值折扣卡列表 user_id  massage/admin
        Route::get('balanceDiscountCardList', 'AdminUser/balanceDiscountCardList');

    });

    Route::get('AdminReseller/resellerList', 'AdminReseller/resellerList');

    Route::get('AdminReseller/resellerInfo', 'AdminReseller/resellerInfo');

    Route::post('AdminReseller/resellerUpdate', 'AdminReseller/resellerUpdate');

    Route::get('AdminReseller/partnerDataList', 'AdminReseller/partnerDataList');
    //分销下级绑定用户 user_id
    Route::get('AdminReseller/subUser', 'AdminReseller/subUser');
    //分销下级绑定下级 id
    Route::get('AdminReseller/subReseller', 'AdminReseller/subReseller');
    //解除下级绑定用户关系 user_id
    Route::post('AdminReseller/delSubUser', 'AdminReseller/delSubUser');

    Route::get('AdminReseller/resellerRelationshipTop', 'AdminReseller/resellerRelationshipTop');

    Route::group('AdminReseller', function () {

        Route::get('resellerOrderList', 'AdminReseller/resellerOrderList');

        Route::post('updateRecommendCash', 'AdminReseller/updateRecommendCash');

        Route::get('recommendRecord', 'AdminReseller/recommendRecord');

        Route::get('noresellerUserList', 'AdminReseller/noresellerUserList');

        Route::post('applyReseller', 'AdminReseller/applyReseller');
    });

    Route::group('AdminChannel', function () {

        Route::get('cateList', 'AdminChannel/cateList');

        Route::get('cateSelect', 'AdminChannel/cateSelect');

        Route::get('channelSelect', 'AdminChannel/channelSelect');

        Route::post('cateAdd', 'AdminChannel/cateAdd');

        Route::post('cateUpdate', 'AdminChannel/cateUpdate');

        Route::get('cateInfo', 'AdminChannel/cateInfo');

        Route::get('channelList', 'AdminChannel/channelList');

        Route::get('channelInfo', 'AdminChannel/channelInfo');

        Route::post('channelUpdate', 'AdminChannel/channelUpdate');
        //channel_id salesman_id
        Route::post('bindSalesman', 'AdminChannel/bindSalesman');

        Route::post('setChannelBalance', 'AdminChannel/setChannelBalance');

        Route::post('delChannelBalance', 'AdminChannel/delChannelBalance');

        Route::post('channelQrAdd', 'AdminChannel/channelQrAdd');

        Route::any('channelQrList', 'AdminChannel/channelQrList');

        Route::get('channelQrInfo', 'AdminChannel/channelQrInfo');

        Route::post('channelQrUpdate', 'AdminChannel/channelQrUpdate');

        Route::post('channelQrDel', 'AdminChannel/channelQrDel');

        Route::post('bindChannel', 'AdminChannel/bindChannel');

        Route::post('downloadQr', 'AdminChannel/downloadQr');

        Route::get('channelUserList', 'AdminChannel/channelUserList');

        Route::get('channelQr', 'AdminChannel/channelQr');

        Route::post('applyChannel', 'AdminChannel/applyChannel');

        Route::post('setChannelQrBalance', 'AdminChannel/setChannelQrBalance');

        Route::get('nochannelUserList', 'AdminChannel/nochannelUserList');

    });

    /********************按摩6.0接口**********************/
    //添加物料分类
    Route::post('AdminShop/addCarte', 'AdminShop/addCarte');

    Route::get('AdminShop/changeOpenid', 'AdminShop/changeOpenid');
    //编辑物料分类
    Route::post('AdminShop/editCarte', 'AdminShop/editCarte');
    Route::get('AdminShop/editCarte', 'AdminShop/editCarte');
    //分类列表
    Route::get('AdminShop/carteList', 'AdminShop/carteList');
    //上下架、删除
    Route::post('AdminShop/carteStatus', 'AdminShop/carteStatus');


    //分类下拉
    Route::get('AdminShop/goodsCarteList', 'AdminShop/goodsCarteList');
    //添加商品
    Route::post('AdminShop/addGoods', 'AdminShop/addGoods');
    //编辑商品
    Route::post('AdminShop/editGoods', 'AdminShop/editGoods');
    Route::get('AdminShop/editGoods', 'AdminShop/editGoods');
    //商品列表
    Route::get('AdminShop/goodsList', 'AdminShop/goodsList');
    //商品上下架、删除
    Route::post('AdminShop/goodsStatus', 'AdminShop/goodsStatus');


    //反馈记录列表
    Route::get('AdminSetting/feedbackList', 'AdminSetting/feedbackList');
    //反馈记录详情
    Route::get('AdminSetting/feedbackInfo', 'AdminSetting/feedbackInfo');
    //处理反馈记录
    Route::post('AdminSetting/feedbackHandle', 'AdminSetting/feedbackHandle');
    //申诉记录列表
    Route::get('AdminSetting/appealList', 'AdminSetting/appealList');
    //申诉记录详情
    Route::get('AdminSetting/appealInfo', 'AdminSetting/appealInfo');
    //处理申诉记录
    Route::post('AdminSetting/appealHandle', 'AdminSetting/appealHandle');

    Route::post('AdminSetting/configUpdateSchedule', 'AdminSetting/configUpdateSchedule');

    Route::get('AdminSetting/configInfoSchedule', 'AdminSetting/configInfoSchedule');

    Route::get('AdminSetting/getCarConfigList', 'AdminSetting/getCarConfigList');

    Route::get('AdminSetting/getCarConfigInfo', 'AdminSetting/getCarConfigInfo');

    Route::post('AdminSetting/getCarConfigAdd', 'AdminSetting/getCarConfigAdd');

    Route::post('AdminSetting/getCarConfigUpdate', 'AdminSetting/getCarConfigUpdate');

    Route::post('AdminSetting/getCarConfigDel', 'AdminSetting/getCarConfigDel');

    Route::get('AdminSetting/configSettingInfo', 'AdminSetting/configSettingInfo');

    Route::post('AdminSetting/configSettingUpdate', 'AdminSetting/configSettingUpdate');

    Route::get('AdminSetting/distributionConfigInfo', 'AdminSetting/distributionConfigInfo');

    Route::post('AdminSetting/distributionConfigUpdate', 'AdminSetting/distributionConfigUpdate');

    Route::get('AdminSetting/fddConfigInfo', 'AdminSetting/fddConfigInfo');

    Route::post('AdminSetting/fddConfigUpdate', 'AdminSetting/fddConfigUpdate');

    Route::get('AdminSetting/userLabelList', 'AdminSetting/userLabelList');

    Route::get('AdminSetting/userLabelInfo', 'AdminSetting/userLabelInfo');

    Route::get('AdminSetting/getCity', 'AdminSetting/getCity');

    Route::post('AdminSetting/userLabelUpdate', 'AdminSetting/userLabelUpdate');

    Route::post('AdminSetting/userLabelAdd', 'AdminSetting/userLabelAdd');

    Route::get('AdminSetting/coachIconList', 'AdminSetting/coachIconList');

    Route::get('AdminSetting/coachIconInfo', 'AdminSetting/coachIconInfo');

    Route::get('AdminSetting/noticeInfo', 'AdminSetting/noticeInfo');

    Route::post('AdminSetting/noticeUpdate', 'AdminSetting/noticeUpdate');

    Route::post('AdminSetting/coachIconUpdate', 'AdminSetting/coachIconUpdate');

    Route::post('AdminSetting/coachIconAdd', 'AdminSetting/coachIconAdd');

    Route::group('AdminSetting', function () {

        Route::get('btnConfigInfo', 'AdminSetting/btnConfigInfo');

        Route::post('btnConfigUpdate', 'AdminSetting/btnConfigUpdate');

        Route::get('getCoachClockInfo', 'AdminSetting/getCoachClockInfo');

        Route::get('orderConfigInfo', 'AdminSetting/orderConfigInfo');

        Route::post('orderConfigUpdate', 'AdminSetting/orderConfigUpdate');

        Route::get('adminCarCashInfo', 'AdminSetting/adminCarCashInfo');

        Route::get('getMap', 'AdminSetting/getMap');

        Route::get('getMapInfo', 'AdminSetting/getMapInfo');

        Route::post('adminCarCashUpdate', 'AdminSetting/adminCarCashUpdate');

        //百应
        Route::post('bySetting', 'AdminSetting/bySetting');

        Route::get('bySetting', 'AdminSetting/bySetting');
    });

    Route::group('AdminIndex', function () {

        Route::get('orderData', 'AdminIndex/orderData');
        //数据大屏
        Route::get('dataScreen', 'AdminIndex/dataScreen');

        Route::get('agentOrderData', 'AdminIndex/agentOrderData');

        Route::get('coachAndUserData', 'AdminIndex/coachAndUserData');
        //
        Route::get('coachSaleData', 'AdminIndex/coachSaleData');

        Route::get('cityData', 'AdminIndex/cityData');
        Route::get('indexData', 'AdminIndex/indexData');
        Route::get('userData', 'AdminIndex/userData');
        Route::get('getMapCoach', 'AdminIndex/getMapCoach');

        Route::get('adminData', 'AdminIndex/adminData');

        Route::get('expectationCityList', 'AdminIndex/expectationCityList');

        Route::post('expectationCityUpdate', 'AdminIndex/expectationCityUpdate');

    });


    Route::group('AdminArticle', function () {

        Route::get('fieldList', 'AdminArticle/fieldList');
        Route::get('fieldSelect', 'AdminArticle/fieldSelect');

        Route::get('fieldInfo', 'AdminArticle/fieldInfo');

        Route::get('articleList', 'AdminArticle/articleList');

        Route::post('fieldAdd', 'AdminArticle/fieldAdd');

        Route::post('fieldUpdate', 'AdminArticle/fieldUpdate');

        Route::get('articleInfo', 'AdminArticle/articleInfo');

        Route::post('articleAdd', 'AdminArticle/articleAdd');

        Route::post('articleUpdate', 'AdminArticle/articleUpdate');

        Route::get('subTitle', 'AdminArticle/subTitle');

        Route::get('subDataList', 'AdminArticle/subDataList');

    });

    Route::group('AdminSalesman', function () {

        Route::get('salesmanList', 'AdminSalesman/salesmanList');

        Route::get('salesmanInfo', 'AdminSalesman/salesmanInfo');

        Route::get('salesmanDataList', 'AdminSalesman/salesmanDataList');

        Route::post('checkSalesman', 'AdminSalesman/checkSalesman');

        Route::post('setSalesmanBalance', 'AdminSalesman/setSalesmanBalance');

        Route::post('delSalesmanBalance', 'AdminSalesman/delSalesmanBalance');

        Route::post('addSalesman', 'AdminSalesman/addSalesman');

        Route::get('noSalesmanUserList', 'AdminSalesman/noSalesmanUserList');

    });

    Route::group('AdminMember', function () {

        Route::get('levelList', 'AdminMember/levelList');

        Route::get('levelInfo', 'AdminMember/levelInfo');

        Route::post('levelAdd', 'AdminMember/levelAdd');

        Route::post('levelUpdate', 'AdminMember/levelUpdate');

        Route::post('levelStatusUpdate', 'AdminMember/levelStatusUpdate');

        Route::get('rightsList', 'AdminMember/rightsList');

        Route::get('rightsInfo', 'AdminMember/rightsInfo');

        Route::post('rightsUpdate', 'AdminMember/rightsUpdate');

        Route::get('configInfo', 'AdminMember/configInfo');

        Route::post('configUpdate', 'AdminMember/configUpdate');

    });

    Route::group('AdminFinance', function () {

        Route::get('index', 'AdminFinance/index');

        Route::get('couponData', 'AdminFinance/couponData');

        Route::get('serviceTopList', 'AdminFinance/serviceTopList');
        Route::get('timeDataList', 'AdminFinance/timeDataList');

        Route::get('resellerList', 'AdminFinance/resellerList');

        Route::get('channelList', 'AdminFinance/channelList');
        Route::get('salesmanList', 'AdminFinance/salesmanList');
        Route::get('brokerList', 'AdminFinance/brokerList');

        Route::post('updateCash', 'AdminFinance/updateCash');
        Route::post('sendShortMsg', 'AdminFinance/sendShortMsg');

        Route::get('walletCheckConfig', 'AdminFinance/walletCheckConfig');

        Route::get('adminRechargeList', 'AdminFinance/adminRechargeList');
    });

});


//商城后端路由表
Route::group('app', function () {
    //首页
    Route::get('Index/index', 'Index/index');

    Route::post('Index/updateUserPopImg', 'Index/updateUserPopImg');

    Route::get('Index/plugAuth', 'Index/plugAuth');

    Route::get('Index/recommendCoach', 'Index/recommendCoach');

    Route::get('Index/WatermarkImgInfo', 'Index/WatermarkImgInfo');

    Route::get('Index/getCity', 'Index/getCity');

    Route::get('Index/couponList', 'Index/couponList');

    Route::get('Index/couponListV2', 'Index/couponListV2');

    Route::get('Index/getCoachService', 'Index/getCoachService');

    Route::post('Index/userGetCoupon', 'Index/userGetCoupon');

    Route::get('Index/coachInfo', 'Index/coachInfo');
    //再来一单(order_id)
    Route::post('Index/onceMoreOrder', 'Index/onceMoreOrder');
    //评价列表(coach_id)
    Route::get('Index/commentList', 'Index/commentList');
    //服务列表(sort:price 价格排序 ，total_sale销量排序 star评价排序 ,)
    Route::get('Index/serviceList', 'Index/serviceList');
    //服务详情(id)
    Route::get('Index/serviceInfo', 'Index/serviceInfo');
    //服务技师列表(ser_id，服务id,lat,lng)
    Route::get('Index/serviceCoachList', 'Index/serviceCoachList');
    //技师服务列表(coach_id)
    Route::get('Index/coachServiceList', 'Index/coachServiceList');

    Route::get('Index/getMapInfo', 'Index/getMapInfo');

    Route::get('Index/adminCarCashInfo', 'Index/adminCarCashInfo');

    Route::get('Index/recommendList', 'Index/recommendList');

    Route::get('Index/typeServiceCoachList', 'Index/typeServiceCoachList');

    Route::post('Index/expectationCity', 'Index/expectationCity');

    Route::get('Index/expectationCityCheck', 'Index/expectationCityCheck');

    Route::get('Index/industryServiceList', 'Index/industryServiceList');

    Route::get('Index/industryGetCoachService', 'Index/industryGetCoachService');
    //人员列表 lat lng industry_type
    Route::get('Index/getServiceObjList', 'Index/getServiceObjList');

    Route::get('Index/cityData', 'Index/cityData');
    Route::get('Index/getCityList', 'Index/getCityList');

    Route::get('Index/nearbyLocation', 'Index/nearbyLocation');

    Route::post('IndexUser/delUserInfo', 'IndexUser/delUserInfo');
    //用户授权
    Route::post('IndexUser/userUpdate', 'IndexUser/userUpdate');

    Route::post('IndexUser/attestationCoach', 'IndexUser/attestationCoach');
    //申请技师
    Route::post('IndexUser/coachApply', 'IndexUser/coachApply');
    //教练收藏列表
    Route::get('IndexUser/coachCollectList', 'IndexUser/coachCollectList');

    Route::get('IndexUser/getUserCoachStatus', 'IndexUser/getUserCoachStatus');
    //添加技师收藏(coach_id)
    Route::post('IndexUser/addCollect', 'IndexUser/addCollect');
    //删除技师收藏(coach_id)
    Route::post('IndexUser/delCollect', 'IndexUser/delCollect');

    Route::post('IndexUser/shieldCoachAdd', 'IndexUser/shieldCoachAdd');

    Route::post('IndexUser/shieldCoachDel', 'IndexUser/shieldCoachDel');

    Route::get('IndexUser/shieldCoachList', 'IndexUser/shieldCoachList');

    Route::get('IndexUser/userInfo', 'IndexUser/userInfo');

    Route::post('IndexUser/reportPhone', 'IndexUser/reportPhone');
    //优惠券活动详情
    Route::post('IndexUser/couponAtvInfo', 'IndexUser/couponAtvInfo');
    //用户|团长个人中心
    Route::get('IndexUser/index', 'IndexUser/index');
    //个人团长信息
    Route::get('IndexUser/coachInfo', 'IndexUser/coachInfo');
    //用户地址列表
    Route::get('IndexUser/addressList', 'IndexUser/addressList');

    Route::get('IndexUser/getVirtualPhone', 'IndexUser/getVirtualPhone');
    //地址详情
    Route::get('IndexUser/addressInfo', 'IndexUser/addressInfo');
    //添加地址
    Route::post('IndexUser/addressAdd', 'IndexUser/addressAdd');
    //编辑地址
    Route::post('IndexUser/addressUpdate', 'IndexUser/addressUpdate');
    //删除地址
    Route::post('IndexUser/addressDel', 'IndexUser/addressDel');
    //获取默认地址
    Route::get('IndexUser/getDefultAddress', 'IndexUser/getDefultAddress');
    //活动二维码
    Route::post('IndexUser/atvQr', 'IndexUser/atvQr');
    //用户优惠券列表（status1，2，3）
    Route::get('IndexUser/userCouponList', 'IndexUser/userCouponList');
    //删除优惠券（coupon_id）
    Route::post('IndexUser/couponDel', 'IndexUser/couponDel');
    //channel_id
    Route::post('IndexUser/bindChannel', 'IndexUser/bindChannel');

    Route::group('IndexUser', function () {

        Route::post('addScanRecord', 'IndexUser/addScanRecord');

        Route::post('updateScanRecord', 'IndexUser/updateScanRecord');

        Route::get('getAddressByIp', 'IndexUser/getAddressByIp');

        Route::get('storeList', 'IndexUser/storeList');

        Route::get('storeListByCoach', 'IndexUser/storeListByCoach');
    });
     //获取配置信息
    Route::get('Index/getDriving', 'Index/getDriving');
    //获取配置信息
    Route::get('Index/configInfo', 'Index/configInfo');
    //技师首页
    Route::get('IndexCoach/coachIndex', 'IndexCoach/coachIndex');
    //技师编辑
    Route::post('IndexCoach/coachUpdate', 'IndexCoach/coachUpdate');
    //团长核销订单（id）
    Route::post('IndexCoach/hxOrder', 'IndexCoach/hxOrder');
    //订单列表
    Route::get('IndexCoach/orderList', 'IndexCoach/orderList');
    //团长佣金信息
    Route::get('IndexCoach/capCashInfo', 'IndexCoach/capCashInfo');
    //团长佣金信息(车费)
    Route::get('IndexCoach/capCashInfoCar', 'IndexCoach/capCashInfoCar');

    Route::get('IndexCoach/balanceCommissionList', 'IndexCoach/balanceCommissionList');

    Route::get('IndexCoach/balanceCommissionData', 'IndexCoach/balanceCommissionData');
    //提现记录
    Route::get('IndexCoach/capCashList', 'IndexCoach/capCashList');
    //申请提现(apply_price,text,type：1服务费提现，2车费提现)
    Route::post('IndexCoach/applyWallet', 'IndexCoach/applyWallet');
    //技师获取虚拟电话 order_id
    Route::post('IndexCoach/getVirtualPhone', 'IndexCoach/getVirtualPhone');
    //报警
    Route::post('IndexCoach/police', 'IndexCoach/police');
    //技师修改订单信息(type,order_id)
    Route::post('IndexCoach/updateOrder', 'IndexCoach/updateOrder');

    Route::post('IndexCoach/coachUpdateV2', 'IndexCoach/coachUpdateV2');

    Route::post('IndexCoach/shieldUserAdd', 'IndexCoach/shieldUserAdd');

    Route::post('IndexCoach/shieldUserDel', 'IndexCoach/shieldUserDel');

    Route::get('IndexCoach/shieldCoachList', 'IndexCoach/shieldCoachList');

    Route::get('IndexCoach/getFddStatus', 'IndexCoach/getFddStatus');

    Route::post('IndexCoach/coachTrajectoryAdd', 'IndexCoach/coachTrajectoryAdd');

    Route::get('IndexCoach/getCreditValueData', 'IndexCoach/getCreditValueData');
    //异常订单标示
    Route::get('IndexCoach/abnOrderInfo', 'IndexCoach/abnOrderInfo');

    Route::group('IndexCoach', function () {

        Route::get('getCoachWalletAccount', 'IndexCoach/getCoachWalletAccount');

        Route::post('coachAccountSendShortMsg', 'IndexCoach/coachAccountSendShortMsg');

        Route::post('setCoachAccount', 'IndexCoach/setCoachAccount');
    });

    Route::get('IndexGoods/indexCapList', 'IndexGoods/indexCapList');
    //选择楼长(cap_id)
    Route::post('IndexGoods/selectCap', 'IndexGoods/selectCap');
    //分类列表
    Route::get('IndexGoods/cateList', 'IndexGoods/cateList');
    //商品首页信息
    Route::get('IndexGoods/index', 'IndexGoods/index');

    //商品列表
    Route::get('IndexGoods/goodsList', 'IndexGoods/goodsList');
    //商品详情
    Route::get('IndexGoods/goodsInfo', 'IndexGoods/goodsInfo');

    //购物车信息（coach_id）
    Route::get('Index/carInfo', 'Index/carInfo');
    //admin_id
    Route::get('Index/agentInfo', 'Index/agentInfo');

    Route::get('Index/serviceCateList', 'Index/serviceCateList');
    //添加购物车（service_id,coach_id,num = 1）
    Route::post('Index/addCar', 'Index/addCar');
    //删除购物车|减少购物车商品数量（id,num=1）
    Route::post('Index/delCar', 'Index/delCar');
    //批量删除购物车（coach）
    Route::post('Index/delSomeCar', 'Index/delSomeCar');
    //修改购物车（ID ：arr）
    Route::post('Index/carUpdate', 'Index/carUpdate');
    //
    Route::post('IndexOrder/payOrder', 'IndexOrder/payOrder');
    //下单的那个页面(coach_id，有优惠券就传 coupon_id)
    Route::get('IndexOrder/payOrderInfo', 'IndexOrder/payOrderInfo');

    Route::get('IndexOrder/getServiceType', 'IndexOrder/getServiceType');
    //用户订单列表（pay_type,name）
    Route::get('IndexOrder/orderList', 'IndexOrder/orderList');

    Route::post('IndexOrder/delOrder', 'IndexOrder/delOrder');

    Route::get('IndexOrder/getUpOrderGoods', 'IndexOrder/getUpOrderGoods');

    Route::any('IndexOrder/upOrderGoods', 'IndexOrder/upOrderGoods');
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

    Route::post('IndexOrder/checkAddOrder', 'IndexOrder/checkAddOrder');
    //选中时间(coach_id,day)
    Route::get('IndexOrder/timeText', 'IndexOrder/timeText');

    Route::get('IndexOrder/dayText', 'IndexOrder/dayText');
    //添加评价(order_id,text，star)
    Route::post('IndexOrder/addComment', 'IndexOrder/addComment');

    Route::get('IndexOrder/lableList', 'IndexOrder/lableList');
    //可用的优惠券(coach_id)
    Route::get('IndexOrder/couponList', 'IndexOrder/couponList');

    Route::post('IndexOrder/userSignOrder', 'IndexOrder/userSignOrder');

    Route::get('IndexOrder/orderUpRecord', 'IndexOrder/orderUpRecord');

    Route::get('IndexOrder/getAddClockOrder', 'IndexOrder/getAddClockOrder');

    Route::post('IndexOrder/upOrderInfo', 'IndexOrder/upOrderInfo');

    Route::post('IndexOrder/rePayUpOrder', 'IndexOrder/rePayUpOrder');

    Route::group('IndexOrder', function () {

        Route::post('endOrder', 'IndexOrder/endOrder');

        Route::get('payOrderInfoConfig', 'IndexOrder/payOrderInfoConfig');

        Route::get('canEndOrder', 'IndexOrder/canEndOrder');

        Route::post('noPayRecordAdd', 'IndexOrder/noPayRecordAdd');

        Route::get('canRefundOrder', 'IndexOrder/canRefundOrder');

        Route::get('balanceDiscountList', 'IndexOrder/balanceDiscountList');
    });
    //储值充值卡列表
    Route::get('IndexBalance/cardList', 'IndexBalance/cardList');

    Route::get('IndexBalance/coachList', 'IndexBalance/coachList');
    //充值余额(card_id)
    Route::post('IndexBalance/payBalanceOrder', 'IndexBalance/payBalanceOrder');
    //充值订单列表(时间筛选 start_time,end_time)
    Route::get('IndexBalance/balaceOrder', 'IndexBalance/balaceOrder');
    //消费明细
    Route::get('IndexBalance/payWater', 'IndexBalance/payWater');

    Route::get('IndexBalance/payWaterBalance', 'IndexBalance/payWaterBalance');

    Route::get('IndexBalance/payWaterBalanceDiscount', 'IndexBalance/payWaterBalanceDiscount');

    Route::get('IndexBalance/balanceOrderList', 'IndexBalance/balanceOrderList');

    Route::get('IndexBalance/balanceDiscountOrderList', 'IndexBalance/balanceDiscountOrderList');


    //佣金列表 status 0,1,2
    Route::get('IndexUser/commList', 'IndexUser/commList');
    //img
    Route::post('IndexUser/base64ToImg', 'IndexUser/base64ToImg');

    Route::get('IndexUser/adminCoachQr', 'IndexUser/adminCoachQr');

    Route::get('IndexUser/userCashInfo', 'IndexUser/userCashInfo');

    Route::post('IndexUser/applyWallet', 'IndexUser/applyWallet');

    Route::post('IndexUser/bindAlipayNumber', 'IndexUser/bindAlipayNumber');

    Route::get('IndexUser/walletList', 'IndexUser/walletList');

    Route::get('IndexUser/myTeam', 'IndexUser/myTeam');

    Route::get('IndexUser/userCommQr', 'IndexUser/userCommQr');
    //申请分销商 user_name mobile
    Route::post('IndexUser/applyReseller', 'IndexUser/applyReseller');

    Route::get('IndexUser/resellerInfo', 'IndexUser/resellerInfo');

    Route::get('IndexUser/brokerInfo', 'IndexUser/brokerInfo');

    Route::post('IndexUser/applyBroker', 'IndexUser/applyBroker');

    Route::get('IndexOrder/getIsBus', 'IndexOrder/getIsBus');

    Route::get('IndexOrder/adapayOrderInfo', 'IndexOrder/adapayOrderInfo');
    //申请分销商 user_name mobile
    Route::post('IndexUser/applyChannel', 'IndexUser/applyChannel');

    Route::get('IndexUser/channelInfo', 'IndexUser/channelInfo');

    Route::get('IndexUser/channelCateSelect', 'IndexUser/channelCateSelect');

    Route::post('IndexUser/sendShortMsg', 'IndexUser/sendShortMsg');

    Route::post('IndexUser/bindUserPhone', 'IndexUser/bindUserPhone');

    Route::get('IndexUser/getStoreSelect', 'IndexUser/getStoreSelect');

    Route::get('IndexUser/salesmanInfo', 'IndexUser/salesmanInfo');

    Route::post('IndexUser/applySalesman', 'IndexUser/applySalesman');
    //申请代理商
    Route::post('IndexUser/agentApply', 'IndexUser/agentApply');

    Route::get('IndexUser/getWecomStaff', 'IndexUser/getWecomStaff');

    Route::get('IndexUser/getPayResellerData', 'IndexUser/getPayResellerData');

    Route::post('IndexUser/reApplyResellerOrder', 'IndexUser/reApplyResellerOrder');

    Route::group('IndexUser', function () {

        Route::get('getAccountCoach', 'IndexUser/getAccountCoach');

        Route::get('coachTypeSelect', 'IndexUser/coachTypeSelect');

        Route::get('adminList', 'IndexUser/adminList');

        Route::get('adminAuth', 'IndexUser/adminAuth');

        Route::get('industryTypeSelect', 'IndexUser/industryTypeSelect');

        Route::get('stationIconSelect', 'IndexUser/stationIconSelect');

        Route::get('coachIconSelect', 'IndexUser/coachIconSelect');

        Route::get('userCouponInfo', 'IndexUser/userCouponInfo');

        Route::get('couponHxQr', 'IndexUser/couponHxQr');

        Route::get('getPartnerWater', 'IndexUser/getPartnerWater');

        Route::get('partnerMoneyInfo', 'IndexUser/partnerMoneyInfo');

        Route::post('applyPartnerWallet', 'IndexUser/applyPartnerWallet');

        Route::get('partnerWalletList', 'IndexUser/partnerWalletList');
    });

    Route::group('IndexChannel', function () {

        Route::get('index', 'IndexChannel/index');

        Route::get('channelQr', 'IndexChannel/channelQr');

        Route::get('orderList', 'IndexChannel/orderList');

        Route::post('applyWallet', 'IndexChannel/applyWallet');

        Route::get('walletList', 'IndexChannel/walletList');

        Route::get('channelQrList', 'IndexChannel/channelQrList');

        Route::get('channelQrSelect', 'IndexChannel/channelQrSelect');

        Route::get('channelQrInfo', 'IndexChannel/channelQrInfo');
    });

    Route::group('IndexSalesman', function () {

        Route::get('index', 'IndexSalesman/index');

        Route::get('salesmanChannelCash', 'IndexSalesman/salesmanChannelCash');

        Route::get('salesmanChannelOrderList', 'IndexSalesman/salesmanChannelOrderList');

        Route::post('applyWallet', 'IndexSalesman/applyWallet');

        Route::get('walletList', 'IndexSalesman/walletList');

        Route::get('salesmanQr', 'IndexSalesman/salesmanQr');

        Route::get('salesmanInfo', 'IndexSalesman/salesmanInfo');

        Route::get('salesmanChannelOrderListV2', 'IndexSalesman/salesmanChannelOrderListV2');

        Route::post('unfriendChannel', 'IndexSalesman/unfriendChannel');

        Route::post('setSalesmanBalance', 'IndexSalesman/setSalesmanBalance');

        Route::post('delSalesmanBalance', 'IndexSalesman/delSalesmanBalance');

        Route::post('setInvChannelBalance', 'IndexSalesman/setInvChannelBalance');


    });

    Route::group('IndexGoods', function () {

        Route::get('goodsInfo', 'IndexGoods/goodsInfo');

    });

    /********************按摩6.0接口**********************/

    //技师时间管理回显
    Route::get('IndexCoach/timeConfig', 'IndexCoach/getTimeConfig');
    //技师时间管理设置
    Route::post('IndexCoach/timeConfig', 'IndexCoach/setTimeConfig');
    //技师接单时间获取时间节点
    Route::get('IndexCoach/getTime', 'IndexCoach/getTime');
    //技师车费明细列表
    Route::get('IndexCoach/carMoneyList', 'IndexCoach/carMoneyList');
    //订单数量
    Route::get('IndexCoach/getOrderNum', 'IndexCoach/getOrderNum');
    //物料商城-商品列表
    Route::get('IndexCoach/goodsList', 'IndexCoach/goodsList');
    //物料商城-分类列表
    Route::get('IndexCoach/carteList', 'IndexCoach/carteList');
    //物料商城-商品详情
    Route::get('IndexCoach/goodsInfo', 'IndexCoach/goodsInfo');
    //添加反馈
    Route::post('IndexCoach/addFeedback', 'IndexUser/addFeedback');
    //反馈列表
    Route::get('IndexCoach/listFeedback', 'IndexUser/listFeedback');
    //反馈详情
    Route::get('IndexCoach/feedbackInfo', 'IndexUser/feedbackInfo');
    //提交申诉
    Route::post('IndexCoach/addAppeal', 'IndexCoach/addAppeal');
    //申诉记录列表
    Route::get('IndexCoach/appealList', 'IndexCoach/appealList');
    //订单列表
    Route::get('IndexCoach/appealOrder', 'IndexCoach/appealOrder');

    Route::get('IndexCoach/userLabelList', 'IndexCoach/userLabelList');

    Route::get('IndexCoach/labelList', 'IndexCoach/labelList');

    Route::get('IndexCoach/orderInfo', 'IndexCoach/orderInfo');

    Route::post('IndexCoach/userLabelAdd', 'IndexCoach/userLabelAdd');

    Route::get('IndexCoach/coachBalanceQr', 'IndexCoach/coachBalanceQr');
    Route::get('IndexCoach/coachLevel', 'IndexCoach/coachLevel');

    Route::get('IndexCoach/coachCommissionList', 'IndexCoach/coachCommissionList');

    Route::get('IndexCoach/coachCommissionData', 'IndexCoach/coachCommissionData');

    Route::get('IndexCoach/coachCommissionInfo', 'IndexCoach/coachCommissionInfo');

    Route::get('IndexCoach/coachCommentUserData', 'IndexCoach/coachCommentUserData');


    Route::group('IndexCoach', function () {

        Route::get('getFddRecord', 'IndexCoach/getFddRecord');

        Route::get('getAttestationInfo', 'IndexCoach/getAttestationInfo');

        Route::get('getPersonVerifyUrl', 'IndexCoach/getPersonVerifyUrl');

        Route::get('coachCarCashInfo', 'IndexCoach/coachCarCashInfo');

        Route::post('Extsign', 'IndexCoach/Extsign');

        Route::get('commentList', 'IndexCoach/commentList');

        Route::get('commRefundInfo', 'IndexCoach/commRefundInfo');

        Route::get('updateCoachCashList', 'IndexCoach/updateCoachCashList');

        Route::post('updateCommentGood', 'IndexCoach/updateCommentGood');

        Route::post('updateOrderAddress', 'IndexCoach/updateOrderAddress');

        Route::post('delOrder', 'IndexCoach/delOrder');

        Route::get('memberDiscountCommissionList', 'IndexCoach/memberDiscountCommissionList');
        Route::get('memberDiscountCommissionData', 'IndexCoach/memberDiscountCommissionData');

        Route::get('coachMemberDiscountQr', 'IndexCoach/coachMemberDiscountQr');
        Route::get('coachBalanceDiscountQr', 'IndexCoach/coachBalanceDiscountQr');
        //今日订单数据 massage/app
        Route::get('todayOrderData', 'IndexCoach/todayOrderData');
        //技师统计
        Route::get('coachDataCount', 'IndexCoach/coachDataCount');

        Route::get('coachNoticeList', 'IndexCoach/coachNoticeList');

        Route::get('coachNoticeInfo', 'IndexCoach/coachNoticeInfo');
        //放心签实名认证
        Route::post('fxqCheck', 'IndexCoach/fxqCheck');
        //放心签意愿认证短信验证码
        Route::post('sendFxqCode', 'IndexCoach/sendFxqCode');
        //放心签技师签署合同
        Route::post('fxqSign', 'IndexCoach/fxqSign');
        //放心签添加合同
        Route::post('addContract', 'IndexCoach/addContract');
    });

    Route::group('IndexReseller', function () {

        Route::get('partnerIndex', 'IndexReseller/partnerIndex');

        Route::get('partnerCoachList', 'IndexReseller/partnerCoachList');
        //合伙人邀请技师码 admin_id
        Route::get('resellerInvCoachQr', 'IndexReseller/resellerInvCoachQr');

        Route::get('adminList', 'IndexReseller/adminList');

        Route::get('resellerCashList', 'IndexReseller/resellerCashList');

        Route::get('resellerInvresellerQr', 'IndexReseller/resellerInvresellerQr');

        Route::get('subReseller', 'IndexReseller/subReseller');

        Route::post('resellerLevelUp', 'IndexReseller/resellerLevelUp');

        Route::get('invCashList', 'IndexReseller/invCashList');

    });

    Route::group('IndexArticle', function () {

        Route::get('articleList', 'IndexArticle/articleList');

        Route::get('articleInfo', 'IndexArticle/articleInfo');

        Route::post('subArticleForm', 'IndexArticle/subArticleForm');

    });

});


//支付
Route::any('IndexWxPay/returnPay', 'IndexWxPay/returnPay');

Route::any('IndexWxPay/aliNotify', 'IndexWxPay/aliNotify');

Route::any('IndexWxPay/aliNotifyBalance', 'IndexWxPay/aliNotifyBalance');

Route::any('IndexWxPay/aliNotifyUp', 'IndexWxPay/aliNotifyUp');

Route::any('IndexWxPay/aliNotifyReseller', 'IndexWxPay/aliNotifyReseller');

Route::any('IndexWxPay/aliAgentRecharge', 'IndexWxPay/aliAgentRecharge');

Route::any('IndexWxPay/aliMemberdiscount', 'IndexWxPay/aliMemberdiscount');

Route::any('IndexWxPay/aliBalancediscount', 'IndexWxPay/aliBalancediscount');

Route::any('IndexWxPay/aliPartnerOrder', 'IndexWxPay/aliPartnerOrder');

Route::any('IndexWxPay/aliPartnerOrderJoin', 'IndexWxPay/aliPartnerOrderJoin');

Route::any('CallBack/fddAttestationCallBack', 'CallBack/fddAttestationCallBack');

Route::any('CallBack/fddSignCallBack', 'CallBack/fddSignCallBack');

Route::any('CallBack/valid', 'CallBack/valid');

Route::get('CallBack/getMapCoach', 'CallBack/getMapCoach');

Route::any('Test/test', 'Test/test');

Route::any('Test/test1', 'Test/test1');
Route::any('Test/test2', 'Test/test2');





//1282.97  954.37








