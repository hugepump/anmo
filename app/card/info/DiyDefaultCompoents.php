<?php
/**
 * Created by PhpStorm.
 * User: shuixian
 * Date: 2019/11/20
 * Time: 18:29
 */


$defaultPage=<<<DEFAULT
            
{"key":1,"list":[{"title":"名片模块","type":"cardModule","icon":"iconCardModule","isDelete":true,"addNumber":1,"attr":[{"title":"显示发名片按钮","type":"Switch","name":"isShowSendBtn"},{"title":"显示保存到通讯录","type":"Switch","name":"isShowSaveBtn"},{"title":"显示电话","type":"Switch","name":"isShowPhone"},{"title":"显示座机","type":"Switch","name":"isShowZuoji"},{"title":"显示400热线","type":"Switch","name":"isShowHotPhone"},{"title":"显示微信","type":"Switch","name":"isShowWechat"},{"title":"显示邮箱","type":"Switch","name":"isShowEmail"},{"title":"显示公司名称","type":"Switch","name":"isShowCompanyName"},{"title":"显示公司地址","type":"Switch","name":"isShowCompanyAddr"}],"data":{"title":"名片模块","isShowSendBtn":true,"isShowSaveBtn":true,"isShowPhone":true,"isShowZuoji":true,"isShowHotPhone":true,"isShowWechat":true,"isShowEmail":true,"isShowCompanyName":true,"isShowCompanyAddr":true,"dataList":[]},"id":1591842905840,"compontents":"cardCompoent"},{"title":"优惠券","type":"couponList","icon":"iconCouponList","isDelete":true,"addNumber":1,"attr":[{"title":"模板名称","type":"Input","name":"title","maxLength":10},{"title":"卡券样式","type":"Radio","name":"type","data":[{"label":1,"title":"弹窗样式"},{"label":2,"title":"列表样式"}]}],"data":{"title":"领取优惠券","type":1,"dataList":[]},"id":1591842929534,"compontents":"operate"},{"title":"公众号组件","type":"official","icon":"iconOfficial","isDelete":true,"addNumber":1,"data":{"title":"公众号组件"},"id":1591842933706,"compontents":"cardCompoent"},{"title":"个人简介","type":"staffIntroduce","icon":"iconStaffIntroduce","isDelete":true,"addNumber":1,"data":{"title":"个人简介","dataList":[]},"id":1591842938310,"compontents":"cardCompoent"},{"title":"主推商品","type":"myGoods","icon":"iconMyGoods","isDelete":true,"addNumber":1,"data":{"title":"主推商品","dataList":[]},"id":1591842976731,"compontents":"cardCompoent"},{"title":"VR全景","type":"myVR","icon":"iconMyVR","isDelete":true,"addNumber":1,"data":{"title":"VR全景","dataList":[]},"id":1591842986079,"compontents":"cardCompoent"},{"title":"我的视频","type":"myVideo","icon":"iconMyVideo","isDelete":true,"addNumber":1,"data":{"title":"我的视频","dataList":[]},"id":1591842987329,"compontents":"cardCompoent"},{"title":"我的动态","type":"myDynamic","icon":"iconMyDynamic","isDelete":true,"addNumber":1,"attr":[{"title":"点击图片\/视频","type":"Radio","name":"clickType","data":[{"label":1,"title":"查看图片\/视频"},{"label":2,"title":"进入详情"}]},{"title":"显示数量","type":"InputNumber","name":"row"}],"data":{"title":"我的动态","clickType":1,"row":{"number":3,"min":1,"max":3,"label":"请输入"},"dataList":[]},"id":1591843000578,"compontents":"cardCompoent"},{"title":"我的照片","type":"myPhoto","icon":"iconMyPhoto","isDelete":true,"addNumber":1,"data":{"title":"我的照片","dataList":[]},"id":1591843001799,"compontents":"cardCompoent"}]}
DEFAULT;


$page = json_decode( $defaultPage , true);


return $page;