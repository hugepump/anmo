<?php
/**
 * Created by PhpStorm.
 * User: shuixian
 * Date: 2019/11/20
 * Time: 18:29
 */

$data = [

    [

        'key'   => 'wechat_transfer',

        'default_value' => 0,

        'text' => '微信转账',

        'field_type' => 1
    ],
    [

        'key'   => 'alipay_transfer',

        'default_value' => 0,

        'text' => '支付宝转账',

        'field_type' => 1,
    ],
    [

        'key'   => 'under_transfer',

        'default_value' => 1,

        'text' => '线下转账',

        'field_type' => 1,
    ],
    [

        'key'   => 'bank_transfer',

        'default_value' => 0,

        'text' => '银行转账',

        'field_type' => 1,
    ],
    [

        'key'   => 'coach_format',

        'default_value' => 1,

        'text' => '技师列表的版式',

        'field_type' => 1,
    ],
    [

        'key'   => 'recommend_style',

        'default_value' => 1,

        'text' => '推荐技师样式',

        'field_type' => 1,
    ],
    [

        'key'   => 'coach_level_show',

        'default_value' => 1,

        'text' => '技师比例是否显示',

        'field_type' => 1,
    ],
    [

        'key'   => 'order_dispatch',

        'default_value' => 0,

        'text' => '是否派单',

        'field_type' => 1,
    ],
    [

        'key'   => 'index_city_find',

        'default_value' => 0,

        'text' => '是否必须定位所在城市',

        'field_type' => 1,
    ],
    [

        'key'   => 'coach_partner',

        'default_value' => 0,

        'text' => '技师合伙人',

        'field_type' => 1,
    ],
    [

        'key' => 'user_agent_balance',

        'default_value' => 0,

        'text' => '用户返佣',

        'field_type' => 1,
    ],
    [

        'key'   => 'coach_agent_balance',

        'default_value' => 0,

        'text' => '邀请技师返佣',

        'field_type' => 1,
    ],
    [

        'key'   => 'commission_custom',

        'default_value' => 0,

        'text' => '佣金自定义',

        'field_type' => 1,
    ],
    [

        'key'   => 'tax_point',

        'default_value' => 0,

        'text' => '提现税点',

        'field_type' => 1,
    ],
    [

        'key'   => 'recharge_status',

        'default_value' => 1,

        'text' => '余额充值入口',

        'field_type' => 1,
    ],
    [

        'key'   => 'number_encryption',

        'default_value' => 0,

        'text' => '是否号码加密',

        'field_type' => 1,
    ]
    ,
    [

        'key'   => 'number_encryption_ip',

        'default_value' => 0,

        'text' => '公司ip可以看到真实号码',

        'field_type' => 2,
    ],
    [

        'key'   => 'order_contact_coach',

        'default_value' => 0,

        'text' => '订单详情是否可以联系技师',

        'field_type' => 1,
    ],
    [

        'key'   => 'salesman_check_status',

        'default_value' => 0,

        'text' => '业务员审核',

        'field_type' => 1,
    ],
    [

        'key'   => 'salesman_balance',

        'default_value' => 0,

        'text' => '业务员佣金比例',

        'field_type' => 1,
    ],
    [

        'key'   => 'salesman_coach_balance',

        'default_value' => 0,

        'text' => '业务员佣金技师承担比例',

        'field_type' => 1,
    ],
    [

        'key'   => 'salesman_admin_balance',

        'default_value' => 0,

        'text' => '业务员佣金代理商承担比例',

        'field_type' => 1,
    ],
    [

        'key'   => 'salesman_poster',

        'default_value' => 'https://' . $_SERVER['HTTP_HOST'] . '/admin/anmo/mine/salesman-share.png',

        'text' => '业务员海报',

        'field_type' => 2,
    ],
    [

        'key'   => 'channel_poster',

        'default_value' => 'https://' . $_SERVER['HTTP_HOST'] . '/admin/anmo/mine/channel-share.png',

        'text' => '渠道商海报',

        'field_type' => 2,
    ],
    [

        'key'   => 'channel_balance',

        'default_value' => 0,

        'text' => '渠道商分销佣金比例',

        'field_type' => 1,
    ],[

        'key'   => 'channel_coach_balance',

        'default_value' => 0,

        'text' => '渠道商分销佣金技师承担比例',

        'field_type' => 1,
    ],[

        'key'   => 'channel_admin_balance',

        'default_value' => 0,

        'text' => '渠道商分销佣金代理商承担比例',

        'field_type' => 1,
    ],[

        'key'   => 'partner_coach_balance',

        'default_value' => 0,

        'text' => '合伙人佣金技师承担比例',

        'field_type' => 1,
    ],[

        'key'   => 'partner_admin_balance',

        'default_value' => 0,

        'text' => '合伙人佣金代理商承担比例',

        'field_type' => 1,
    ],[

        'key'   => 'attendant_name',

        'default_value' => '技师',

        'text' => '行业服务人员名称',

        'field_type' => 2,
    ],
    [

        'key'   => 'cash_share_admin',

        'default_value' => 1,

        'text' => '佣金由上级代理商分摊还是城市代理商分摊',

        'field_type' => 1,
    ],
    [

        'key'   => 'service_start_recording',

        'default_value' => '',

        'text' => '服务开始时候录音',

        'field_type' => 2,
    ],
    [

        'key'   => 'service_end_recording',

        'default_value' => '',

        'text' => '服务结束时候录音',

        'field_type' => 2,
    ],
    [

        'key'   => 'service_recording_show',

        'default_value' => 0,

        'text' => '服务过程录音',

        'field_type' => 1,
    ],
    [

        'key'   => 'coach_apply_show',

        'default_value' => 0,

        'text' => '首页是否显示申请技师入口',

        'field_type' => 1,
    ],
    [

        'key'   => 'auto_recommend',

        'default_value' => 0,

        'text' => '自动推荐',

        'field_type' => 1,
    ],
    [

        'key'   => 'copyright',

        'default_value' => '',

        'text' => '版权',

        'field_type' => 2,
    ],
    [

        'key'   => 'login_phone_auth',

        'default_value' => 0,

        'text' => '登录系统时是否需要手机验证',

        'field_type' => 1,
    ],
    [

        'key'   => 'login_auth_phone',

        'default_value' => '',

        'text' => '登录系统时需要验证的手机号',

        'field_type' => 2,
    ],
    [

        'key'   => 'user_channel_over_time',

        'default_value' => 1,

        'text' => '用户绑定渠道商过期时间',

        'field_type' => 1,
    ],
    [

        'key'   => 'force_login',

        'default_value' => 2,

        'text' => '是否强制登录 1是强制登录',

        'field_type' => 1,
    ],
    [

        'key'   => 'amap_key',

        'default_value' => '',

        'text' => '高德地图key',

        'field_type' => 2,
    ],
    [

        'key'   => 'wechat_qr_type',

        'default_value' => 0,

        'text' => '1扫码直接到公众号',

        'field_type' => 1,
    ],
    [

        'key'   => 'car_price_account',

        'default_value' => 0,

        'text' => '车费秒到账 1开启 0关闭',

        'field_type' => 1,
    ],
    [

        'key'   => 'account_pay_type',

        'default_value' => 1,

        'text' => '秒到账支付方式 1微信 2支付宝',

        'field_type' => 1,
    ],
    [

        'key'   => 'merchant_switch_show',

        'default_value' => 0,

        'text' => '商家信息',

        'field_type' => 1,
    ],
    [

        'key'   => 'realtime_location',

        'default_value' => 0,

        'text' => '实时定位',

        'field_type' => 1,
    ],
    [

        'key'   => 'material_type',

        'default_value' => 0,

        'text' => '物料费提成方式',

        'field_type' => 1,
    ],
    [

        'key'   => 'coach_license_show',

        'default_value' => 0,

        'text' => '技师是否显示营业执照',

        'field_type' => 1,
    ],
    [

        'key'   => 'wechat_reply_text',

        'default_value' => '',

        'text' => '公众号自动回复',

        'field_type' => 2,
    ],
    [

        'key'   => 'wechat_tmpl',

        'default_value' => 0,

        'text' => '公众号模版消息通知管理员',

        'field_type' => 1,
    ],
    [

        'key'   => 'wechat_tmpl_admin',

        'default_value' => '',

        'text' => '公众号模版消息通知管理员信息',

        'field_type' => 2,
    ],
    [

        'key'   => 'order_wechat_agent_status',

        'default_value' => 1,

        'text' => '订单服务通知是否通知代理商',

        'field_type' => 1,
    ],
    [

        'key'   => 'order_wechat_admin_status',

        'default_value' => 1,

        'text' => '订单服务通知代理商订单是否通知平台',

        'field_type' => 1,
    ],
    [

        'key'   => 'service_lat_type',

        'default_value' => 0,

        'text' => '技师服务迟到提醒，0服务开始前，1服务开始后',

        'field_type' => 1,
    ],
    [

        'key'   => 'service_lat_minute',

        'default_value' => 0,

        'text' => '技师服务迟到提醒，分钟',

        'field_type' => 1,
    ]
    ,
    [

        'key'   => 'coach_receiving_minute',

        'default_value' => 0,

        'text' => '用户下单后多少分钟不接单，提醒平台和管理员',

        'field_type' => 1,
    ],
    [

        'key'   => 'jump_order_minute',

        'default_value' => 0,

        'text' => '完成订单后，技师多少分钟内没离开提醒平台(分钟)',

        'field_type' => 1,
    ],
    [

        'key'   => 'jump_order_distance',

        'default_value' => '',

        'text'  => '完成订单后，技师多少分钟内没离开提醒平台（距离）',

        'field_type' => 2,
    ],
    [

        'key'   => 'coach_force_show',

        'default_value' => 0,

        'text'  => '技师强制实时定位',

        'field_type' => 1,
    ],
    [

        'key'   => 'order_tmpl_notice',

        'default_value' => 0,

        'text'  => '后端订单消息提醒',

        'field_type' => 1,
    ]
    ,
    [

        'key'   => 'coach_list_format',

        'default_value' => 1,

        'text'  => '技师列表样式',

        'field_type' => 1,
    ] ,
    [

        'key'   => 'play_list_format',

        'default_value' => 1,

        'text'  => '陪玩官列表样式',

        'field_type' => 1,
    ] ,
    [

        'key'   => 'coach_apply_type',

        'default_value' => 1,

        'text'  => '技师申请模式',

        'field_type' => 1,
    ],
    [

        'key'   => 'coach_filter_show',

        'default_value' => 1,

        'text'  => '技师筛选',

        'field_type' => 1,
    ],
    [

        'key'   => 'notice_admin',

        'default_value' => 1,

        'text'  => '是否通知平台',

        'field_type' => 1,
    ],
    [

        'key'   => 'wx_point',

        'default_value' => 0,

        'text'  => '微信手续费',

        'field_type' => 3,
    ],
    [

        'key'   => 'ali_point',

        'default_value' => 0,

        'text'  => '支付宝手续费',

        'field_type' => 3,
    ],
    [

        'key'   => 'balance_point',

        'default_value' => 0,

        'text'  => '余额手续费',

        'field_type' => 3,
    ],
    [

        'key'   => 'poster_point',

        'default_value' => 0,

        'text'  => '广告手续费',

        'field_type' => 3,
    ],
    [

        'key'   => 'poster_coach_share',

        'default_value' => 0,

        'text'  => '广告费技师承担',

        'field_type' => 3,
    ],
    [

        'key'   => 'poster_admin_share',

        'default_value' => 0,

        'text'  => '广告费代理商承担',

        'field_type' => 3,
    ],
    [

        'key'   => 'hide_coach_image',

        'default_value' => 0,

        'text'  => '隐藏技师头像',

        'field_type' => 1,
    ],
    [

        'key'   => 'hide_admin_mobile',

        'default_value' => 0,

        'text'  => '隐藏平台电话',

        'field_type' => 1,
    ],
    [

        'key'   => 'comm_coach_balance',

        'default_value' => 0,

        'text'  => '余额支付技师扣款比例',

        'field_type' => 3,
    ],
    [

        'key'   => 'block_user_type',

        'default_value' => 1,

        'text'  => '拉黑客户的方式 1技师拉黑 2平台拉黑',

        'field_type' => 1,
    ],
    [


        'key'   => 'free_fare_distance',

        'default_value' => 0,

        'text'  => '多少公里内免车费',

        'field_type' => 2,
    ],
    [


        'key'   => 'free_fare_bear',

        'default_value' => 0,

        'text'  => '车费免费时谁承担,0不开启,1平台 2技师',

        'field_type' => 1,
    ],
    [
        'key'   => 'adapay_balance_point',

        'default_value' => 0,

        'text'  => '汇付余额支付手续费',

        'field_type' => 3,
    ],
    [
        'key'   => 'coach_hot_num',

        'default_value' => 5,

        'text'  => '技师火苗数量',

        'field_type' => 1,
    ],
    [
        'key'   => 'coach_top_num',

        'default_value' => 3,

        'text'  => '技师皇冠数量',

        'field_type' => 1,
    ],
    [
        'key'   => 'coach_show_lable',

        'default_value' => 1,

        'text'  => '技师不看用户的标签',

        'field_type' => 1,
    ],
    [
        'key'   => 'coach_wallet_cash_type',

        'default_value' => 0,

        'text'  => '技师可以提现金额 0所有到账 1到账后15天',

        'field_type' => 1,
    ],
    [
        'key'   => 'coach_icon_type',

        'default_value' => 0,

        'text'  => '技师图标类型 0默认 1自定义',

        'field_type' => 1,
    ],
    [
        'key'   => 'img_watermark',

        'default_value' => '盗图必究',

        'text'  => '图片水印',

        'field_type' => 2,
    ],
    [
        'key'   => 'channel_bind_forever',

        'default_value' => 0,

        'text'  => '渠道码永久绑定 0不永久绑定 1永久绑定',

        'field_type' => 1,
    ],
    [
        'key'   => 'channel_menu_name',

        'default_value' => '渠道商',

        'text'  => '渠道商后台菜单名字',

        'field_type' => 2,
    ],
    [
        'key'   => 'channel_check_status',

        'default_value' => 1,

        'text'  => '渠道商审核 关闭审核后，手机端无申请渠道商入口',

        'field_type' => 1,
    ],
    [
        'key'   => 'channel_cate_status',

        'default_value' => 1,

        'text'  => '关闭之后，渠道类目不会出现在管理后台菜单栏',

        'field_type' => 1,
    ],
    [
        'key'   => 'coach_app_name',

        'default_value' => '',

        'text'  => '技师端小程序名字',

        'field_type' => 2,
    ],
    [
        'key'   => 'coach_appid',

        'default_value' => '',

        'text'  => '技师端小程序appid',

        'field_type' => 2,
    ],
    [
        'key'   => 'coach_appsecret',

        'default_value' => '',

        'text'  => '技师端小程序密钥',

        'field_type' => 2,
    ],
    [
        'key'   => 'coach_app_app_id',

        'default_value' => '',

        'text'  => '技师端app appid',

        'field_type' => 2,
    ],
    [
        'key'   => 'coach_app_app_secret',

        'default_value' => '',

        'text'  => '技师端app 密钥',

        'field_type' => 2,
    ],
    [
        'key'   => 'coach_android_link',

        'default_value' => '',

        'text'  => '技师端app安卓下载链接',

        'field_type' => 2,
    ],
    [
        'key'   => 'coach_ios_link',

        'default_value' => '',

        'text'  => '技师端app苹果下载链接',

        'field_type' => 2,
    ],
    [
        'key'   => 'coach_app_text',

        'default_value' => '',

        'text'  => '技师端应用名称',

        'field_type' => 2,
    ],
    [
        'key'   => 'channel_fx_superpose',

        'default_value' => 1,

        'text'  => '渠道商分销商佣金叠加',

        'field_type' => 1,
    ],
    [
        'key'   => 'agent_coupon_location',

        'default_value' => 0,

        'text'  => '代理商优惠券只能在代理商所在城市可以领取',

        'field_type' => 1,
    ],
    [
        'key'   => 'add_flow_path',

        'default_value' => 1,

        'text'  => '加钟流程 1正常 2简约',

        'field_type' => 1,
    ],
    [
        'key'   => 'salesman_channel_fx_type',

        'default_value' => 1,

        'text'  => '业务员渠道商分销方式 1默认 2相减',

        'field_type' => 1,
    ],
    [
        'key'   => 'free_fare_top_type',

        'default_value' => 1,

        'text'  => '免出行费的排前面',

        'field_type' => 1,
    ],
    [
        'key'   => 'web_coach_port',

        'default_value' => 1,

        'text'  => 'web端技师入口',

        'field_type' => 1,
    ],
    [

        'key' => 'user_level_balance',

        'default_value' => 0,

        'text' => '用户二级分销返佣',

        'field_type' => 3,
    ],
    [

        'key' => 'broker_apply_port',

        'default_value' => 1,

        'text' => '经纪人申请端口 1前端可以申请 0不能',

        'field_type' => 1,
    ],
    [

        'key' => 'broker_cash_type',

        'default_value' => 0,

        'text' => '经纪人返佣模式 0固定 1浮动',

        'field_type' => 1,
    ],
    [

        'key' => 'broker_poster',

        'default_value' => 'https://' . $_SERVER['HTTP_HOST'] . '/admin/anmo/mine/inv_coach.png',

        'text' => '经纪人海报',

        'field_type' => 2,
    ],
    [

        'key' => 'reseller_inv_reseller_poster',

        'default_value' => 'https://' . $_SERVER['HTTP_HOST'] . '/admin/anmo/mine/inv_user_fx.png',

        'text' => '分销员邀请分销员海报',

        'field_type' => 2,
    ],
    [

        'key' => 'admin_reseller_poster',

        'default_value' => 'https://' . $_SERVER['HTTP_HOST'] . '/admin/anmo/mine/inv_fx.png',

        'text' => '代理商分销员海报',

        'field_type' => 2,
    ],
    [

        'key' => 'reseller_cash_type',

        'default_value' => '1',

        'text' => '分销返佣方式 0按人 1按服务 ',

        'field_type' => 1,
    ],
    [

        'key' => 'agent_reseller_max_balance',

        'default_value' => '0',

        'text' => '代理商分销员邀请用户返佣最高比例',

        'field_type' => 3,
    ],
    [

        'key' => 'reseller_threshold',

        'default_value' => '0',

        'text' => '一级分销员门槛',

        'field_type' => 3,
    ],
    [

        'key' => 'reseller_inv_balance',

        'default_value' => '0',

        'text' => '一级分销员推荐提成',

        'field_type' => 3,
    ],
    [

        'key' => 'level_reseller_threshold',

        'default_value' => '0',

        'text' => '二级分销员门槛',

        'field_type' => 3,
    ],
    [

        'key' => 'level_reseller_inv_balance',

        'default_value' => '0',

        'text' => '二级分销员推荐提成',

        'field_type' => 3,
    ],
    [

        'key' => 'coach_advance_end',

        'default_value' => 0,

        'text' => '技师是否可以提前完成订单',

        'field_type' => 1,
    ],
    [

        'key' => 'user_update_location',

        'default_value' => 0,

        'text' => '用户是否可以修改定位',

        'field_type' => 1,
    ],
    [

        'key' => 'coupon_get_type',

        'default_value' => 0,

        'text' => '用户卡券领取方式 0手动 1自动',

        'field_type' => 1,
    ],
//    [
//
//        'key' => 'car_price_start',
//
//        'default_value' => '00:00',
//
//        'text' => '车费白天开始时间',
//
//        'field_type' => 2,
//    ],
//    [
//
//        'key' => 'car_price_end',
//
//        'default_value' => '24:00',
//
//        'text' => '车费白天结束时间',
//
//        'field_type' => 2,
//    ],
    [

        'key' => 'fx_time_type',

        'default_value' => 0,

        'text' => '分销绑定时效性 0永久 1时间限制',

        'field_type' => 1,
    ],
    [

        'key' => 'fx_time_day',

        'default_value' => 0,

        'text' => '分销绑定时间',

        'field_type' => 1,
    ],
    [
        'key' => 'channel_bind_type',

        'default_value' => 1,

        'text' => '渠道商绑定方式 1时效性内可换绑 0不可换绑',

        'field_type' => 1,
    ],
    [
        'key' => 'reseller_coach_balance',

        'default_value' => 0,

        'text' => '分销员佣金技师承担多少',

        'field_type' => 3,
    ],
    [
        'key' => 'reseller_admin_balance',

        'default_value' => 0,

        'text' => '分销员佣金代理商承担多少',

        'field_type' => 3,
    ],
    [
        'key' => 'pageColor',

        'default_value' => '#f6f6f6',

        'text' => '页面背景色',

        'field_type' => 2,
    ],
    [
        'key' => 'wallet_phone_check',

        'default_value' => 0,

        'text' => '修改财务 是否手机验证，0关闭 1开启',

        'field_type' => 1,
    ],
    [
        'key' => 'reseller_status',

        'default_value' => 1,

        'text' => '分销员开关',

        'field_type' => 1,
    ],
    [
        'key' => 'channel_status',

        'default_value' => 1,

        'text' => '渠道商开关',

        'field_type' => 1,
    ],
    [
        'key' => 'broker_status',

        'default_value' => 1,

        'text' => '经纪人开关',

        'field_type' => 1,
    ],
    [
        'key' => 'salesman_status',

        'default_value' => 1,

        'text' => '业务员开关',

        'field_type' => 1,
    ],
    [
        'key' => 'coach_update_phone_code_status',

        'default_value' => 0,

        'text' => '技师修改所属用户是否开启手机验证码校验',

        'field_type' => 1,
    ],
    [
        'key' => 'coach_account_phone_status',

        'default_value' => 0,

        'text' => '技师修改账号是否需要验证码',

        'field_type' => 1,
    ],
    [
        'key' => 'login_des',

        'default_value' => '用户登录',

        'text' => '登录界面的文案',

        'field_type' => 2,
    ],
    [
        'key' => 'coach_service_wallet_cash_t_type',

        'default_value' => 0,

        'text' => '0不限 1半月 2星期',

        'field_type' => 1,
    ],
    [
        'key' => 'coupon_bear_type',

        'default_value' => 1,

        'text' => '卡券承担方式',

        'field_type' => 1,
    ],
    [
        'key' => 'coupon_bear_coach',

        'default_value' => 0,

        'text' => '卡券技师承担',

        'field_type' => 2,
    ],
    [
        'key' => 'coupon_bear_admin',

        'default_value' => 0,

        'text' => '卡券代理商承担',

        'field_type' => 2,
    ],
    [
        'key' => 'end_order_rules',

        'default_value' => '· ',

        'text' => '用户完成订单须知内容',

        'field_type' => 2,
    ],
    [
        'key' => 'order_rules_status',

        'default_value' => 0,

        'text' => '下单需同意交易须知',

        'field_type' => 1,
    ],
    [
        'key' => 'order_rules',

        'default_value' => '',

        'text' => '交易须知',

        'field_type' => 2,
    ],
    [
        'key' => 'personal_income_tax_text',

        'default_value' => '个人所得税',

        'text' => '交易须知',

        'field_type' => 2,
    ],
    [
        'key' => 'nopay_notice',

        'default_value' => 0,

        'text' => '未下单订单通知',

        'field_type' => 1,
    ],
    [
        'key' => 'show_user_order_num',

        'default_value' => 1,

        'text' => '技师端是否现实用户下单次数',

        'field_type' => 1,
    ],
    [
        'key' => 'distribution_range_type',

        'default_value' => 0,

        'text' => '分销按范围返回佣金',

        'field_type' => 1,
    ],
    [
        'key' => 'coach_update_address_auth',

        'default_value' => 0,

        'text' => '技师是否可以修改地址',

        'field_type' => 1,
    ],
    [
        'key' => 'tencent_map_key',

        'default_value' => '',

        'text' => '腾讯地图key',

        'field_type' => 2,
    ],
    [
        'key' => 'callback_url',

        'default_value' => '',

        'text' => '回调域名',

        'field_type' => 2,
    ],
    [
        'key' => 'agent_sub_img',

        'default_value' => 'https://' . $_SERVER['HTTP_HOST'] . '/admin/anmo/mine/agent-share.png',

        'text' => '代理商发展代理制背景图',

        'field_type' => 2,
    ],
    [
        'key' => 'material_text',

        'default_value' => '物料费',

        'text' => '物料费文案',

        'field_type' => 2,
    ],
    [
        'key' => 'popup_img',

        'default_value' => '',

        'text' => '弹窗图片',

        'field_type' => 2,
    ],
    [
        'key' => 'reseller_check_type',

        'default_value' => 0,

        'text' => '分销员审核方式 0手动 1自动',

        'field_type' => 1,
    ],
    [
        'key' => 'inv_qr_type',

        'default_value' => 1,

        'text' => '分销员邀请码样式 0默认 1海报二维码',

        'field_type' => 1,
    ],
    [
        'key' => 'empty_order_cash',

        'default_value' => 0,

        'text' => '技师到达后扣取多少空单费',

        'field_type' => 3,
    ],
    [
        'key' => 'coach_empty_cash',

        'default_value' => 0,

        'text' => '技师获得多少比例的空单费',

        'field_type' => 2,
    ],
    [
        'key' => 'admin_empty_cash',

        'default_value' => 0,

        'text' => '代理商获得多少比例的空单费',

        'field_type' => 2,
    ],
    [
        'key' => 'coach_refund_comm',

        'default_value' => 0,

        'text' => '技师获得多少比例的退款手续费',

        'field_type' => 2,
    ],
    [
        'key' => 'admin_refund_comm',

        'default_value' => 0,

        'text' => '代理商获得多少比例的退款手续费',

        'field_type' => 2,
    ],
    [
        'key' => 'after_service_can_refund',

        'default_value' => 0,

        'text' => '开始服务后是否允许退款',

        'field_type' => 1,
    ],
    [
        'key' => 'db_code_do',

        'default_value' => 0,

        'text' => '码处理',

        'field_type' => 1,
    ],
    [
        'key' => 'coach_career_show',

        'default_value' => 0,

        'text' => '职业类型筛选',

        'field_type' => 1,
    ],
    [
        'key' => 'version',

        'default_value' => 0,

        'text' => '一个版本号',

        'field_type' => 1,
    ],
    [
        'key' => 'water_version',

        'default_value' => 0,

        'text' => '一个版本号',

        'field_type' => 1,
    ],
    [
        'key' => 'agent_update_city',

        'default_value' => 1,

        'text' => '代理商修改意向城市',

        'field_type' => 1,
    ],
    [
        'key' => 'free_fare_select',

        'default_value' => 1,

        'text' => '免出行筛选',

        'field_type' => 1,
    ],
    [
        'key' => 'order_pay_timeout_remind',

        'default_value' => 0,

        'text' => '订单支付超时提醒',

        'field_type' => 1,
    ],
    [
        'key' => 'have_order_notice',

        'default_value' => 1,

        'text' => '来单通知',

        'field_type' => 1,
    ],
    [
        'key' => 'shield_massage',

        'default_value' => 0,

        'text' => '是否屏蔽按摩行业',

        'field_type' => 1,
    ],
    [
        'key' => 'life_icon_status',

        'default_value' => 1,

        'text' => '生活标签是否显示',

        'field_type' => 1,
    ],
    [
        'key' => 'life_icon_text',

        'default_value' => '艺术照仅供参考',

        'text' => '生活标签内容',

        'field_type' => 2,
    ],
    [
        'key' => 'balance_discount_status',

        'default_value' => 0,

        'text' => '储值折扣开关',

        'field_type' => 1,
    ],
    [
        'key' => 'balance_discount_cash',

        'default_value' => 1,

        'text' => '邀请购买储值折扣卡是否返回佣金',

        'field_type' => 1,
    ],
    [
        'key' => 'balance_discount_coach_balance',

        'default_value' => 0,

        'text' => '储值折扣技师承担比例',

        'field_type' => 2,
    ],
    [
        'key' => 'balance_discount_admin_balance',

        'default_value' => 0,

        'text' => '储值折扣代理商承担比例',

        'field_type' => 2,
    ],
    [
        'key' => 'balance_discount_balance',

        'default_value' => 0,

        'text' => '邀请购买储值折扣卡是返多少佣金|比例',

        'field_type' => 2,
    ],
    [
        'key' => 'balance_discount_integral',

        'default_value' => 0,

        'text' => '邀请购买储值折扣卡是否返回积分',

        'field_type' => 1,
    ],
    [
        'key' => 'longbing_title',

        'default_value' => '龙兵',

        'text' => '',

        'field_type' => 2,
    ],
    [
        'key' => 'agent_default_name',

        'default_value' => '代理商',

        'text' => '代理商默认名字',

        'field_type' => 2,
    ],
    [
        'key' => 'life_text',

        'default_value' => '生活照',

        'text' => '技师生活照自定义字段',

        'field_type' => 2,
    ],
    [
        'key' => 'reseller_menu_name',

        'default_value' => '分销员',

        'text' => '分销员自定义字段',

        'field_type' => 2,
    ],
    [
        'key' => 'broker_menu_name',

        'default_value' => '经纪人',

        'text' => '经纪人自定义字段',

        'field_type' => 2,
    ],
//    [
//        'key' => 'recharge_entrance',
//
//        'default_value' => 1,
//
//        'text' => '余额支付入口',
//
//        'field_type' => 1,
//    ],
//    [
//        'key' => 'balance_discount_entrance',
//
//        'default_value' => 1,
//
//        'text' => '储值折扣卡支付入口',
//
//        'field_type' => 1,
//    ],
    [
        'key' => 'app_wechat_pay',

        'default_value' => 1,

        'text' => 'APP端是否开启微信支付',

        'field_type' => 1,
    ],
    [
        'key' => 'fxq_check_type',

        'default_value' => 1,

        'text' => '放心签实名认证方式 1公安二要素 2人脸识别',

        'field_type' => 1,
    ],

    [
        'key' => 'partner_money',

        'default_value' => 0,

        'text' => '组局活动发布费用',

        'field_type' => 2,
    ],
    [
        'key' => 'partner_check_type',

        'default_value' => 1,

        'text' => '组局审核类型 1人工 2自动',

        'field_type' => 1,
    ],



];


return $data;





