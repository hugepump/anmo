<?php
/**
 * Created by PhpStorm.
 * User: shuixian
 * Date: 2019/11/20
 * Time: 18:29
 */
//行业版打包时可以用此配置,打包成功

use longbingcore\permissions\Tabbar;

return [
    //行业模块名称
    'app_model_name' => 'longbing_massages_city',
    //行业模块标题
    'app_model_title' => '龙兵预约按摩',
    //DIY默认数据
    'diy_default_data' =>[

//        $page = Tabbar::getAuthDefultTabbar($this->_uniacid);

        'page' => '{"1":{"list":[]},"2":{"list":[]},"3":{"list":[]},"4":{"list":[]},"20001":{"list":[{"title":"\u7528\u6237\u4fe1\u606f","type":"userInfo","icon":"iconyonghuxinxi","isDelete":false,"addNumber":1,"attr":[{"title":"\u5b57\u4f53\u989c\u8272","type":"ColorPicker","name":"fontColor"},{"title":"\u80cc\u666f\u56fe\u7247","type":"UploadImage","desc":"750*440","name":"bgImage"}],"data":{"nickName":"\u7528\u6237\u6635\u79f0","avatarUrl":"https:\/\/retail.xiaochengxucms.com\/defaultAvatar.png","nickText":"\u66f4\u65b0\u6211\u7684\u4e2a\u4eba\u8d44\u6599","fontColor":"#F9DEAF","bgImage":[{"url":"http:\/\/longbingcdn.xiaochengxucms.com\/admin\/diy\/user_bg.jpg"}]},"id":1578137234868,"compontents":"ucenterCompoent"},{"title":"\u521b\u5efa\u540d\u7247","type":"createCard","icon":"iconchuangjianmingpian","isDelete":false,"addNumber":1,"data":{"createText":"\u521b\u5efa\u6211\u7684\u540d\u7247","createBtn":"\u521b\u5efa\u540d\u7247"},"id":1578137237049,"compontents":"ucenterCompoent"},{"title":"\u8ba2\u5355\u7ba1\u7406","type":"moduleMenuShopOrder","icon":"iconshoporder","isDelete":true,"addNumber":1,"attr":[{"title":"\u6a21\u677f\u540d\u79f0","type":"Switch","name":"isShowTitle"},{"title":"\u9009\u62e9\u6a21\u677f","type":"ChooseModule","name":"module","data":[{"title":"\u4e00\u884c\u591a\u5217","name":"module-menu-row","img":"http:\/\/longbingcdn.xiaochengxucms.com\/admin\/diy\/module-menu-col.jpg"},{"title":"\u4e00\u884c\u4e00\u5217","name":"module-menu-col","img":"http:\/\/longbingcdn.xiaochengxucms.com\/admin\/diy\/module-menu-row.jpg"}]},{"title":"\u4e00\u884c\u591a\u5c11\u5217","type":"InputNumber","name":"row"}],"data":{"isShowTitle":false,"module":"module-menu-row","row":{"number":4,"min":2,"max":5,"label":"\u8bf7\u8f93\u5165"},"list":[{"title":"\u5168\u90e8","icon":"iconwodedingdan","link":{"type":2,"url":"\/shop\/pages\/order\/list?index=0"}},{"title":"\u5f85\u4ed8\u6b3e","icon":"icondingdandaifukuan","link":{"type":2,"url":"\/shop\/pages\/order\/list?index=1"}},{"title":"\u5f85\u53d1\u8d27","icon":"icondingdandaifahuo","link":{"type":2,"url":"\/shop\/pages\/order\/list?index=2"}},{"title":"\u5f85\u6536\u8d27","icon":"icondingdandaishouhuo","link":{"type":2,"url":"\/shop\/pages\/order\/list?index=3"}},{"title":"\u5df2\u5b8c\u6210","icon":"icondingdanyiwancheng","link":{"type":2,"url":"\/shop\/pages\/order\/list?index=4"}}]},"id":1578137248488,"compontents":"ucenterCompoent"},{"title":"\u5fc5\u5907\u5de5\u5177","type":"moduleMenuShop","icon":"iconshop","isDelete":true,"addNumber":1,"attr":[{"title":"\u6a21\u677f\u540d\u79f0","type":"Switch","name":"isShowTitle"},{"title":"\u9009\u62e9\u6a21\u677f","type":"ChooseModule","name":"module","data":[{"title":"\u4e00\u884c\u591a\u5217","name":"module-menu-row","img":"http:\/\/longbingcdn.xiaochengxucms.com\/admin\/diy\/module-menu-col.jpg"},{"title":"\u4e00\u884c\u4e00\u5217","name":"module-menu-col","img":"http:\/\/longbingcdn.xiaochengxucms.com\/admin\/diy\/module-menu-row.jpg"}]},{"title":"\u4e00\u884c\u591a\u5c11\u5217","type":"InputNumber","name":"row"}],"data":{"isShowTitle":false,"module":"module-menu-row","row":{"number":4,"min":2,"max":5,"label":"\u8bf7\u8f93\u5165"},"list":[{"title":"\u6211\u7684\u552e\u540e","icon":"iconwodeshouhou","link":{"type":2,"url":"\/shop\/pages\/refund\/list"}},{"title":"\u6211\u7684\u6536\u5165","icon":"icontixianguanli","link":{"type":2,"url":"\/shop\/pages\/partner\/income"}},{"title":"\u6211\u7684\u4f18\u60e0\u5238","icon":"iconwodekaquan","link":{"type":2,"url":"\/shop\/pages\/coupon\/list"}},{"title":"\u5206\u9500\u5546\u54c1","icon":"iconquanmianfenxiao","link":{"type":2,"needStaffId":true,"url":"\/shop\/pages\/partner\/distribution?staff_id="}},{"title":"\u6211\u7684\u5730\u5740","icon":"icondizhi2","link":{"type":2,"url":"\/shop\/pages\/address\/list"}}]},"id":1578137252032,"compontents":"ucenterCompoent"},{"title":"\u5207\u6362\u9500\u552e","type":"changeStaff","icon":"iconqiehuanmingpian-copy","isDelete":false,"addNumber":1,"attr":[{"title":"\u6a21\u677f\u540d\u79f0","type":"Input","name":"title"},{"title":"\u662f\u5426\u663e\u793a\u66f4\u591a","type":"Switch","name":"isShowMore"}],"data":{"title":"\u5207\u6362\u9500\u552e","isShowMore":true},"dataList":[],"id":1578137250013,"compontents":"ucenterCompoent"}]}}',

        'tabbar'=>'{"id":1,"uniacid":4,"status":1,"create_time":1578106749,"update_time":1578106749,"list":[{"is_show":1,"key":1,"iconPath":"icon-mingpian","selectedIconPath":"icon-mingpian1","pageComponents":"cardHome","name":"\u540d\u7247","url":"\/pages\/user\/home","url_out":"","jump_way":0},{"key":2,"is_show":1,"iconPath":"icon-shangcheng1","selectedIconPath":"icon-shangcheng","pageComponents":"shopHome","name":"\u5546\u57ce","url":"","url_jump_way":"0","url_out":"","is_delete":false,"bind_compoents":[],"bind_links":[],"page":[]},{"key":3,"is_show":1,"iconPath":"icon-dongtai1","selectedIconPath":"icon-dongtai","pageComponents":"infoHome","name":"\u52a8\u6001","url":"","url_jump_way":"0","url_out":"","is_delete":false,"bind_compoents":[],"bind_links":[],"page":[]},{"key":4,"is_show":1,"iconPath":"icon-guanwang","selectedIconPath":"icon-guanwang1","pageComponents":"websiteHome","name":"\u5b98\u7f51","url":"","url_jump_way":"0","url_out":"","is_delete":false,"bind_compoents":[],"bind_links":[],"page":[]},{"key":20001,"is_show":1,"iconPath":"iconyonghuduangerenzhongxin","selectedIconPath":"iconyonghuduangerenzhongxin1","pageComponents":"","name":"\u4e2a\u4eba\u4e2d\u5fc3","url":"","url_jump_way":"0","url_out":"","is_delete":false,"bind_compoents":["ucenterCompoent"],"bind_links":["case"],"page":[]}],"color":"#5d6268","selectedColor":"#19c865","backgroundColor":"#fff","borderStyle":"white"}',
    ],
    //控制能开放多少个小程序使用  这个是一个通用权限控制 , 请求授权时,应该知道是那个行业的  暂时还没用到
    'saas_auth_number_config' =>[
        'wxapp_number' => 0 ,
        'card_number' => 0 ,
        'company_number' => 0 ,
    ],
    //控制后台能展示的模块
    'saas_auth_admin_model_list' => [

        'shop'=>[
            'auth_platform'   => true ,
            'auth_is_platform_check' => true ,
            'auth_is_saas_check' => false ,
        ],
        'massage'=>[
            'auth_platform'   => true ,
            'auth_is_platform_check' => true ,
            'auth_is_saas_check' => false ,
        ],
        'reminder'=>[
            'auth_platform'   => true ,
            'auth_is_platform_check' => true ,
            'auth_is_saas_check' => true ,
        ],
        'virtual'=>[
            'auth_platform'   => true ,
            'auth_is_platform_check' => true ,
            'auth_is_saas_check' => true ,
        ],
        'dynamic'=>[
            'auth_platform'   => true ,
            'auth_is_platform_check' => true ,
            'auth_is_saas_check' => true ,
        ],
        'recommend'=>[
            'auth_platform'   => true ,
            'auth_is_platform_check' => true ,
            'auth_is_saas_check' => true ,
        ],
        'node'=>[
            'auth_platform'   => false ,
            'auth_is_platform_check' => false ,
            'auth_is_saas_check' => false ,
        ],
        'store'=>[
            'auth_platform'   => true ,
            'auth_is_platform_check' => true ,
            'auth_is_saas_check' => true ,
        ],
        'map'=>[
            'auth_platform'   => true ,
            'auth_is_platform_check' => true ,
            'auth_is_saas_check' => true ,
        ],
        'mobilenode'=>[
            'auth_platform'   => true ,
            'auth_is_platform_check' => true ,
            'auth_is_saas_check' => true ,
        ],
        'recording'=>[
            'auth_platform'   => true ,
            'auth_is_platform_check' => true ,
            'auth_is_saas_check' => true ,
        ],
        'fdd'=>[
            'auth_platform'   => true ,
            'auth_is_platform_check' => true ,
            'auth_is_saas_check' => true ,
        ],
        'member'=>[
            'auth_platform'   => true ,
            'auth_is_platform_check' => true ,
            'auth_is_saas_check' => true ,
        ],
        'adapay'=>[
            'auth_platform'   => true ,
            'auth_is_platform_check' => true ,
            'auth_is_saas_check' => true ,
        ],
        'coachtravel'=>[
            'auth_platform'   => true ,
            'auth_is_platform_check' => true ,
            'auth_is_saas_check' => true ,
        ],
        'caradmin'=>[
            'auth_platform'   => true ,
            'auth_is_platform_check' => true ,
            'auth_is_saas_check' => true ,
        ],
        'coachcredit'=>[
            'auth_platform'   => true ,
            'auth_is_platform_check' => true ,
            'auth_is_saas_check' => true ,
        ],
        'skillservice'=>[
            'auth_platform'   => true ,
            'auth_is_platform_check' => true ,
            'auth_is_saas_check' => false ,
        ],
        'channelforeverbind'=>[
            'auth_platform'   => true ,
            'auth_is_platform_check' => true ,
            'auth_is_saas_check' => true ,
        ],
        'channelcate'=>[
            'auth_platform'   => true ,
            'auth_is_platform_check' => true ,
            'auth_is_saas_check' => true ,
        ],
        'coachport'=>[
            'auth_platform'   => true ,
            'auth_is_platform_check' => true ,
            'auth_is_saas_check' => true ,
        ],
        //异常订单标示
        'abnormalorder'=>[
            'auth_platform'   => true ,
            'auth_is_platform_check' => true ,
            'auth_is_saas_check' => true ,
        ],
        'coachbroker'=>[
            'auth_platform'   => true ,
            'auth_is_platform_check' => true ,
            'auth_is_saas_check' => true ,
        ],
        'payreseller'=>[
            'auth_platform'   => true ,
            'auth_is_platform_check' => true ,
            'auth_is_saas_check' => true ,
        ],
        'salesman'=>[
            'auth_platform'   => true ,
            'auth_is_platform_check' => true ,
            'auth_is_saas_check' => true ,
        ],
        'channel'=>[
            'auth_platform'   => true ,
            'auth_is_platform_check' => true ,
            'auth_is_saas_check' => true ,
        ],
        'reseller'=>[
            'auth_platform'   => true ,
            'auth_is_platform_check' => true ,
            'auth_is_saas_check' => true ,
        ],
        'upgrade'=>[

            'auth_platform'   => true ,
            'auth_is_platform_check' => true ,
            'auth_is_saas_check' => true ,
        ],
        'heepay'=>[

            'auth_platform'   => true ,
            'auth_is_platform_check' => true ,
            'auth_is_saas_check' => true ,
        ],
        'orderradar'=>[

            'auth_platform'   => true ,
            'auth_is_platform_check' => true ,
            'auth_is_saas_check' => true ,
        ],
//        'subagent'=>[
//
//            'auth_platform'   => true ,
//            'auth_is_platform_check' => true ,
//            'auth_is_saas_check' => true ,
//        ],
//        'agentcoach'=>[
//
//            'auth_platform'   => true ,
//            'auth_is_platform_check' => true ,
//            'auth_is_saas_check' => true ,
//        ],
        'agentservice'=>[
            //代理商服务
            'auth_platform'   => true ,
            'auth_is_platform_check' => true ,
            'auth_is_saas_check' => true ,
        ],
        'materialshop'=>[
            //物料商城
            'auth_platform'   => true ,
            'auth_is_platform_check' => true ,
            'auth_is_saas_check' => true ,
        ],
        'couponatv'=>[
            //卡券
            'auth_platform'   => true ,
            'auth_is_platform_check' => true ,
            'auth_is_saas_check' => true ,
        ],
        'agentcoachcheck'=>[
            //代理商技师审核
            'auth_platform'   => true ,
            'auth_is_platform_check' => true ,
            'auth_is_saas_check' => true ,
        ],
        'recommendcash'=>[
            //推荐佣金
            'auth_platform'   => true ,
            'auth_is_platform_check' => true ,
            'auth_is_saas_check' => true ,
        ],
        'addclockcashsetting'=>[
            //加钟佣金设置
            'auth_platform'   => true ,
            'auth_is_platform_check' => true ,
            'auth_is_saas_check' => true ,
        ],
        'channelqrcount'=>[
            //渠道码统计
            'auth_platform'   => true ,
            'auth_is_platform_check' => true ,
            'auth_is_saas_check' => true ,
        ],
        'coupondiscountrule'=>[
            //卡券抵扣规则
            'auth_platform'   => true ,
            'auth_is_platform_check' => true ,
            'auth_is_saas_check' => true ,
        ],
        'adminuser'=>[
            //卡券抵扣规则
            'auth_platform'   => true ,
            'auth_is_platform_check' => true ,
            'auth_is_saas_check' => true ,
        ],
        'industrytype'=>[
            //卡券抵扣规则
            'auth_platform'   => true ,
            'auth_is_platform_check' => true ,
            'auth_is_saas_check' => true ,
        ],
        'baiying'=>[
            //百应外呼
            'auth_platform'   => true ,
            'auth_is_platform_check' => true ,
            'auth_is_saas_check' => true ,
        ],
        'memberdiscount'=>[
            //会员折扣
            'auth_platform'   => true ,
            'auth_is_platform_check' => true ,
            'auth_is_saas_check' => true ,
        ],
        'balancediscount'=>[
            //会员折扣
            'auth_platform'   => true ,
            'auth_is_platform_check' => true ,
            'auth_is_saas_check' => true ,
        ],
        'fxq' => [
            //放心签电子合同
            'auth_platform' => true,
            'auth_is_platform_check' => true,
            'auth_is_saas_check' => true,
        ],
        'partner' => [
            //搭子组局
            'auth_platform' => true,
            'auth_is_platform_check' => true,
            'auth_is_saas_check' => true,
        ],
        'package' => [
            //门店套餐
            'auth_platform' => true,
            'auth_is_platform_check' => true,
            'auth_is_saas_check' => true,
        ],
    ],
    //独立版升级使用
    //版本ID
    'version_id'    => '64c7ad0322f14b9c894e95220c9d00d5',
    //分支ID
    'branch_id'     => '9068836a0acd11eab9c765ac55de11af',
    //当前系统版本号
    'version_no'    => 'massages_city_235.2',
    //验证系统平台ID
    'auth_uniacid'  => 1 ,
    //授权的产品ID
    'auth_goods_id' => 19


];