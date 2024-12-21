<?php
/**
 * Created by PhpStorm.
 * User: shuixian
 * Date: 2019/11/20
 * Time: 18:29
 */

$log = [

    'AdminShop' => [

        [

            'code_action'=> 'editCarte',

            'table'      => 'massage_service_shop_carte',

            'action_type'=> '',

            'name'       => '商品分类',

            'text'       => '分类',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'update',

            'title'      => 'name'

        ],
        [

            'code_action'=> 'carteStatus',

            'table'      => 'massage_service_shop_carte',

            'action_type'=> '',

            'name'       => '商品分类',

            'text'       => '分类',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'update',

            'title'      => 'name'

        ],
        [

            'code_action'=> 'addCarte',

            'table'      => 'massage_service_shop_carte',

            'action_type'=> '',

            'name'       => '商品分类',

            'text'       => '分类',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'add',

            'title'      => 'name'

        ],
        [

            'code_action'=> 'editGoods',

            'table'      => 'massage_service_shop_goods',

            'action_type'=> '',

            'name'       => '商品管理',

            'text'       => '商品',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'update',

            'title'      => 'name'

        ],
        [

            'code_action'=> 'editGoods',

            'table'      => 'massage_service_shop_goods',

            'action_type'=> '',

            'name'       => '商品管理',

            'text'       => '商品',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'update',

            'title'      => 'name'

        ]
        ,
        [

            'code_action'=> 'goodsStatus',

            'table'      => 'massage_service_shop_goods',

            'action_type'=> '',

            'name'       => '商品管理',

            'text'       => '商品',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'update',

            'title'      => 'name'

        ],
        [

            'code_action'=> 'addGoods',

            'table'      => 'massage_service_shop_goods',

            'action_type'=> '',

            'name'       => '商品管理',

            'text'       => '商品',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'add',

            'title'      => 'name'

        ]

    ],
    'AdminService' => [
        [
            'code_action'=> 'serviceAdd',

            'table'      => 'massage_service_service_list',

            'action_type'=> '',

            'name'       => '服务管理',

            'text'       => '服务',

            'parameter'  => 'id',

            'title'      => 'title',
            //自定义参数
            'custom_parameters' => [

                'key' => 'is_add',

                'value' => 0
            ],

            'method'     => 'POST',

            'action'     => 'add'
//transmit_parameters
        ],
        [
            'code_action'=> 'cateAdd',

            'table'      => 'massage_service_cate_list',

            'action_type'=> '',

            'name'       => '服务分类',

            'text'       => '分类',

            'parameter'  => 'id',

            'title'      => 'title',

            'method'     => 'POST',

            'action'     => 'add'
        ],
        [
            'code_action'=> 'cateUpdate',

            'table'      => 'massage_service_cate_list',

            'action_type'=> '',

            'name'       => '服务分类',

            'text'       => '分类',

            'parameter'  => 'id',

            'title'      => 'title',

            'method'     => 'POST',

            'action'     => 'update'
        ],
        [
            'code_action'=> 'serviceUpdate',

            'table'      => 'massage_service_service_list',

            'action_type'=> '',

            'title'      => 'title',

            'name'       => '服务管理',

            'text'       => '服务',
            //自定义参数
            'custom_parameters' => [

                'key' => 'is_add',

                'value' => 0
            ],

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'update'

        ],
        [
            'code_action'=> 'checkStoreGoods',

            'table'      => 'massage_service_service_list',

            'action_type'=> '',

            'title'      => 'title',

            'name'       => '服务管理',

            'text'       => '服务',
            //自定义参数
            'transmit_parameters' => [

                'key' => 'check_status',

                'value' => 2
            ],

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'pass'

        ],
        [
            'code_action'=> 'checkStoreGoods',

            'table'      => 'massage_service_service_list',

            'action_type'=> '',

            'title'      => 'title',

            'name'       => '服务管理',

            'text'       => '服务',
            //自定义参数
            'transmit_parameters' => [

                'key' => 'check_status',

                'value' => 3
            ],

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'nopass'

        ],
        [
            'code_action'=> 'serviceAdd',

            'title'      => 'title',

            'table'      => 'massage_service_service_list',

            'action_type'=> 'add',

            'name'       => '加钟服务',

            'text'       => '服务',
            //自定义参数
            'custom_parameters' => [

                'key' => 'is_add',

                'value' => 1
            ],

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'add'

        ],
        [
            'code_action'=> 'serviceUpdate',

            'title'      => 'title',

            'table'      => 'massage_service_service_list',

            'action_type'=> 'add',

            'name'       => '加钟服务',
            //自定义参数
            'custom_parameters' => [

                'key' => 'is_add',

                'value' => 1
            ],

            'text'       => '服务',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'update'

        ],

    ],

    'AdminSetting' => [
        [
            'code_action'=> 'diyUpdate',

            'table'      => 'massage_service_service_list',

            'action_type'=> '',

            'name'       => 'diy设置-页面设置',

            'text'       => '',

            'parameter'  => '',

            'method'     => 'POST',

            'title'      => '',

            'action'     => 'update'

        ],
        [
            'code_action'=> 'noticeUpdate',

            'table'      => 'massage_service_service_list',

            'action_type'=> '',

            'name'       => '通知管理',

            'text'       => '',

            'parameter'  => '',

            'method'     => 'POST',

            'title'      => '',

            'action'     => 'update'

        ],
        [
            'code_action'=> 'bannerUpdate',

            'table'      => 'massage_service_service_list',

            'action_type'=> '',

            'name'       => '轮播图设置',

            'text'       => '轮播图',

            'parameter'  => 'id',

            'method'     => 'POST',

            'title'      => 'id',

            'action'     => 'update'

        ],
        [
            'code_action'=> 'agentApplyCheck',

            'table'      => 'massage_agent_apply',

            'action_type'=> '',

            'name'       => '代理商管理-代理商审核',

            'text'       => '代理商',

            'parameter'  => 'id',

            'method'     => 'POST',

            'title'      => 'user_name',

            'action'     => 'read'

        ],
        [
            'code_action'=> 'updatePass',

            'table'      => 'shequshop_school_admin',

            'action_type'=> '',

            'name'       => '系统管理',

            'text'       => '',

            'parameter'  => '',

            'method'     => 'POST',

            'action'     => 'updatepassworld'

        ],
        [
            'code_action'=> 'bannerAdd',

            'table'      => 'massage_service_service_list',

            'action_type'=> '',

            'name'       => '轮播图设置',

            'text'       => '轮播图',

            'title'      => 'id',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'add'

        ],
        [
            'code_action'=> 'addClockUpdate',

            'table'      => 'massage_add_clock_setting',

            'action_type'=> '',

            'name'       => '加钟设置',

            'text'       => '设置',

            'parameter'  => '',

            'method'     => 'POST',

            'action'     => 'update'

        ],
        [
            'code_action'=> 'addClockUpdate',

            'table'      => 'massage_add_clock_setting',

            'action_type'=> '',

            'name'       => '加钟设置',

            'text'       => '设置',

            'parameter'  => '',

            'method'     => 'POST',

            'action'     => 'update'

        ],
        [
            'code_action'=> 'configUpdate',

            'table'      => 'shequshop_school_config',

            'action_type'=> 'coach_level',

            'name'       => '技师等级',

            'text'       => '折算周期',

            'parameter'  => '',

            'method'     => 'POST',

            'action'     => 'update',
            //自定义参数
            'transmit_parameters' => [

                'key' => 'level_cycle',

            ],

        ],
        [
            'code_action'=> 'configUpdate',

            'table'      => 'shequshop_school_config',

            'action_type'=> 'coach_level',

            'name'       => '技师等级',

            'text'       => '折算周期',

            'parameter'  => '',

            'method'     => 'POST',

            'action'     => 'update',
            //自定义参数
            'transmit_parameters' => [

                'key' => 'level_cycle',

            ],

        ], [
            'code_action'=> 'configUpdate',

            'table'      => 'shequshop_school_config',

            'action_type'=> 'winnerlook_appid',

            'name'       => '系统设置-云信配置',

            'parameter'  => '',

            'method'     => 'POST',

            'action'     => 'update',
            //自定义参数
            'transmit_parameters' => [

                'key' => 'winnerlook_appid',

            ],

        ], [
            'code_action'=> 'helpConfigUpate',

            'table'      => 'shequshop_school_config',

            'action_type'=> 'winnerlook_appid',

            'name'       => '系统设置-通知管理',

            'parameter'  => '',

            'method'     => 'POST',

            'action'     => 'update',
//            //自定义参数
//            'transmit_parameters' => [
//
//                'key' => 'winnerlook_appid',
//
//            ],

        ],
        [
            'code_action'=> 'configUpdate',

            'table'      => 'shequshop_school_config',

            'action_type'=> 'salesman_balance',

            'name'       => '业务员管理-业务员设置',

            'text'       => '',

            'parameter'  => '',

            'method'     => 'POST',

            'action'     => 'update',
            //自定义参数
            'transmit_parameters' => [

                'key' => 'salesman_balance',

            ],

        ],
        [
            'code_action'=> 'configUpdate',

            'table'      => 'shequshop_school_config',

            'action_type'=> 'moor_id',

            'name'       => '系统设置-容联七陌配置',

            'text'       => '',

            'parameter'  => '',

            'method'     => 'POST',

            'action'     => 'update',
            //自定义参数
            'transmit_parameters' => [

                'key' => 'moor_id',

            ],

        ],
        [
            'code_action'=> 'fddConfigUpdate',

            'table'      => 'shequshop_school_config',

            'action_type'=> '',

            'name'       => '系统设置-电子合同',

            'text'       => '',

            'parameter'  => '',

            'method'     => 'POST',

            'action'     => 'update',
            //自定义参数

        ],
        [
            'code_action'=> 'configUpdate',

            'table'      => 'shequshop_school_config',

            'action_type'=> 'level_other',

            'name'       => '技师管理-技师设置',

          //  'text'       => '其他设置',

            'parameter'  => '',

            'method'     => 'POST',

            'action'     => 'update',
            //自定义参数
            'transmit_parameters' => [

                'key' => 'coach_format',

            ],

        ],
        [
            'code_action'=> 'configUpdate',

            'table'      => 'shequshop_school_config',

            'action_type'=> 'fx_config',

            'name'       => '分销管理',

            'text'       => '分销设置',

            'parameter'  => '',

            'method'     => 'POST',

            'action'     => 'update',
            //自定义参数
            'transmit_parameters' => [

                'key' => 'fx_check',

            ],

        ],
        [
            'code_action'=> 'configUpdate',

            'table'      => 'shequshop_school_config',

            'action_type'=> 'agent_update',

            'name'       => '代理管理',

            'text'       => '代理商设置',

            'parameter'  => '',

            'method'     => 'POST',

            'action'     => 'update',
            //自定义参数
            'transmit_parameters' => [

                'key' => 'agent_article_id',

            ],

        ],
        [
            'code_action'=> 'adminUpdate',

            'table'      => 'shequshop_school_admin',

            'action_type'=> '',

            'name'       => '代理商账号',

            'title'      => 'agent_name',

            'text'       => '账号',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'update',

        ],
        [
            'code_action'=> 'adminStatusUpdate',

            'table'      => 'shequshop_school_admin',

            'action_type'=> '',

            'title'      => 'agent_name',

            'name'       => '代理商账号',

            'text'       => '账号',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'del',

        ],
        [
            'code_action'=> 'adminAdd',

            'table'      => 'shequshop_school_admin',

            'action_type'=> '',

            'title'      => 'agent_name',

            'name'       => '代理商账号',

            'text'       => '账号',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'add',

        ],
        [
            'code_action'=> 'configUpdate',

            'table'      => 'massage_config',

            'action_type'=> 'balance_cofig',

            'name'       => '财务管理',

            'text'       => '储值返佣',

            'parameter'  => '',

            'method'     => 'POST',

            'action'     => 'update',
            //自定义参数
            'transmit_parameters' => [

                'key' => 'balance_balance',

            ],

        ],
        [
            'code_action'=> 'configUpdate',

            'table'      => 'massage_config_setting',

            'action_type'=> '',

            'name'       => '渠道商管理-渠道商设置',

            'text'       => '',

            'parameter'  => '',

           // 'title'      => '',

            'method'     => 'POST',

            'action'     => 'update',
            //自定义参数
            'transmit_parameters' => [

                'key' => 'channel_admin_balance',

            ],

        ],
        [
            'code_action'=> 'configUpdateSchedule',

            'table'      => 'massage_config',

            'action_type'=> 'dynamic_cofig',

            'name'       => '动态管理',

            'text'       => '动态设置',

            'parameter'  => '',

            'method'     => 'POST',

            'action'     => 'update',
            //自定义参数
            'transmit_parameters' => [

                'key' => 'dynamic_check',

            ],
        ],
        [
            'code_action'=> 'configUpdateSchedule',

            'table'      => 'massage_config',

            'action_type'=> 'dynamic_cofig',

            'name'       => '储值返佣',

            'text'       => '设置',

            'parameter'  => '',

            'method'     => 'POST',

            'action'     => 'update',
            //自定义参数
            'transmit_parameters' => [

                'key' => 'balance_balance',

            ],
        ],
        [
            'code_action'=> 'userLabelUpdate',

            'table'      => 'massage_service_user_label_list',

            'action_type'=> '',

            'title'      => 'title',

            'name'       => '客户标签',

            'text'       => '标签',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'update',

        ],
        [
            'code_action'=> 'userLabelAdd',

            'table'      => 'massage_service_user_label_list',

            'action_type'=> '',

            'title'      => 'title',

            'name'       => '客户标签',

            'text'       => '标签',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'add',

        ],
        [
            'code_action'=> 'helpConfigUpate',

            'table'      => 'massage_config',

            'action_type'=> '',

            'name'       => '求救通知',

            'text'       => '求救设置',

            'parameter'  => '',

            'method'     => 'POST',

            'action'     => 'update',

        ],
        [
            'code_action'=> 'feedbackHandle',

            'table'      => 'massage_service_coach_feedback',

            'action_type'=> '',

            'name'       => '问题反馈',

            'text'       => '问题',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'update',

            'title'      => 'id'

        ],
        [
            'code_action'=> 'appealHandle',

            'table'      => 'massage_service_coach_appeal',

            'action_type'=> '',

            'name'       => '差评申诉',

            'text'       => '申诉',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'update',

            'title'      => 'id'

        ],
        [
            'code_action'=> 'configUpdate',

            'table'      => 'massage_config',

            'action_type'=> 'diy_config',

            'name'       => 'DIY设置',

            'text'       => 'DIY',

            'parameter'  => '',

            'method'     => 'POST',

            'action'     => 'update',
            //自定义参数
            'transmit_parameters' => [

                'key' => 'primaryColor',

            ],

        ],
        [
            'code_action'=> 'configUpdate',

            'table'      => 'massage_config',

            'action_type'=> 'diy_other_config',

            'name'       => 'DIY其他设置',

            'text'       => '设置',

            'parameter'  => '',

            'method'     => 'POST',

            'action'     => 'update',
            //自定义参数
            'transmit_parameters' => [

                'key' => 'coach_font_color',

            ],

        ],
        [
            'code_action'=> 'configUpdate',

            'table'      => 'massage_config',

            'action_type'=> 'web_config',

            'name'       => '公众号设置',

            'text'       => '设置',

            'parameter'  => '',

            'method'     => 'POST',

            'action'     => 'update',
            //自定义参数
            'transmit_parameters' => [

                'key' => 'web_app_id',

            ],

        ],
        [
            'code_action'=> 'configUpdate',

            'table'      => 'massage_config',

            'action_type'=> 'app_config',

            'name'       => 'APP设置',

            'text'       => '设置',

            'parameter'  => '',

            'method'     => 'POST',

            'action'     => 'update',
            //自定义参数
            'transmit_parameters' => [

                'key' => 'app_app_id',

            ],

        ],
        [
            'code_action'=> 'configUpdate',

            'table'      => 'massage_config',

            'action_type'=> 'apply_config',

            'name'       => '应用设置',

            'text'       => '设置',

            'parameter'  => '',

            'method'     => 'POST',

            'action'     => 'update',
            //自定义参数
            'transmit_parameters' => [

                'key' => 'app_logo',

            ],

        ],
        [
            'code_action'=> 'configUpdate',

            'table'      => 'massage_config',

            'action_type'=> 'conceal_config',

            'name'       => '隐私设置',

            'text'       => '设置',

            'parameter'  => '',

            'method'     => 'POST',

            'action'     => 'update',
            //自定义参数
            'transmit_parameters' => [

                'key' => 'information_protection',

            ],

        ],
        [
            'code_action'=> 'configUpdate',

            'table'      => 'massage_config',

            'action_type'=> 'business_config',

            'name'       => '交易设置',

            'text'       => '设置',

            'parameter'  => '',

            'method'     => 'POST',

            'action'     => 'update',
            //自定义参数
            'transmit_parameters' => [

                'key' => 'trading_rules',

            ],

        ],
        [
            'code_action'=> 'configUpdate',

            'table'      => 'massage_config',

            'action_type'=> 'putonrecord_config',

            'name'       => '备案信息',

            'text'       => '信息',

            'parameter'  => '',

            'method'     => 'POST',

            'action'     => 'update',
            //自定义参数
            'transmit_parameters' => [

                'key' => 'record_no',

            ],

        ],
        [
            'code_action'=> 'configUpdate',

            'table'      => 'shequshop_school_config',

            'action_type'=> '',

            'name'       => '卡券设置',

            'text'       => '',

            'parameter'  => '',

            'method'     => 'POST',

            'action'     => 'update',
            //自定义参数
            'transmit_parameters' => [

                'key' => 'coupon_bear_type',

            ],

        ],
        [
            'code_action'=> 'configUpdate',

            'table'      => 'massage_config',

            'action_type'=> 'ali_config',

            'name'       => '阿里云配置',

            'text'       => '配置',

            'parameter'  => '',

            'method'     => 'POST',

            'action'     => 'update',
            //自定义参数
            'transmit_parameters' => [

                'key' => 'short_id',

            ],

        ],
        [
            'code_action'=> 'configUpdate',

            'table'      => 'massage_config',

            'action_type'=> 'virtual_config',

            'name'       => '虚拟号配置',

            'text'       => '配置',

            'parameter'  => '',

            'method'     => 'POST',

            'action'     => 'update',
            //自定义参数
            'transmit_parameters' => [

                'key' => 'pool_key',

            ],

        ],
        [
            'code_action'=> 'configUpdate',

            'table'      => 'massage_config',

            'action_type'=> 'reminder_config',

            'name'       => '来电通知配置',

            'text'       => '配置',

            'parameter'  => '',

            'method'     => 'POST',

            'action'     => 'update',
            //自定义参数
            'transmit_parameters' => [

                'key' => 'reminder_phone',

            ],

        ],
        [
            'code_action'=> 'configUpdate',

            'table'      => 'massage_config',

            'action_type'=> 'carout_config',

            'name'       => '出行配置',

            'text'       => '配置',

            'parameter'  => '',

            'method'     => 'POST',

            'action'     => 'update',
            //自定义参数
            'transmit_parameters' => [

                'key' => 'bus_end_time',

            ],

        ],
        [
            'code_action'=> 'configUpdate',

            'table'      => 'massage_config',

            'action_type'=> 'other_config',

            'name'       => '其他配置',

            'text'       => '配置',

            'parameter'  => '',

            'method'     => 'POST',

            'action'     => 'update',
            //自定义参数
            'transmit_parameters' => [

                'key' => 'anonymous_evaluate',

            ],

        ],
        [
            'code_action'=> 'payConfigUpdate',

            'table'      => 'shequshop_school_pay_config',

            'action_type'=> 'wechat_config',

            'name'       => '微信支付设置',

            'text'       => '设置',

            'parameter'  => '',

            'method'     => 'POST',

            'action'     => 'update',
            //自定义参数
            'transmit_parameters' => [

                'key' => 'mch_id',

            ],

        ],
        [
            'code_action'=> 'payConfigUpdate',

            'table'      => 'shequshop_school_pay_config',

            'action_type'=> 'alipay_config',

            'name'       => '支付宝支付设置',

            'text'       => '设置',

            'parameter'  => '',

            'method'     => 'POST',

            'action'     => 'update',
            //自定义参数
            'transmit_parameters' => [

                'key' => 'ali_appid',

            ],

        ],
        [
            'code_action'=> 'sendMsgConfigUpdate',

            'table'      => 'massage_send_msg_config',

            'action_type'=> '',

            'name'       => '万能通知',

            'text'       => '通知',

            'parameter'  => '',

            'method'     => 'POST',

            'action'     => 'update',

        ],
        [
            'code_action'=> 'shortCodeConfigUpdate',

            'table'      => 'massage_short_code_config',

            'action_type'=> '',

            'name'       => '短信通知',

            'text'       => '通知',

            'parameter'  => '',

            'method'     => 'POST',

            'action'     => 'update',

        ],
        [
            'code_action'=> 'getCarConfigAdd',

            'table'      => 'massage_service_car_price',

            'action_type'=> '',

            'name'       => '城市车费',

            'text'       => '设置',

            'title'      => 'id',

            'parameter'  => '',

            'method'     => 'POST',

            'action'     => 'add',

        ],
        [
            'code_action'=> 'getCarConfigUpdate',

            'table'      => 'massage_service_car_price',

            'action_type'=> '',

            'name'       => '城市车费',

            'text'       => '设置',

            'title'      => 'id',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'update',

        ],
        [
            'code_action'=> 'getCarConfigDel',

            'table'      => 'massage_service_car_price',

            'action_type'=> '',

            'name'       => '城市车费',

            'text'       => '设置',

            'title'      => 'id',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'del',

        ],
        [
            'code_action'=> 'carConfigUpdate',

            'table'      => 'massage_service_car_price',

            'action_type'=> '',

            'name'       => '全局车费',

            'text'       => '设置',

            'parameter'  => '',

            'method'     => 'POST',

            'action'     => 'update',

        ],
        [
            'code_action'=> 'cityUpdate',

            'table'      => 'massage_service_city_list',

            'action_type'=> '',

            'title'      => 'title',

            'name'       => '城市设置',

            'text'       => '设置',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'update',

        ],
        [
            'code_action'=> 'cityAdd',

            'table'      => 'massage_service_city_list',

            'action_type'=> '',

            'name'       => '城市设置',

            'title'      => 'title',

            'text'       => '设置',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'add',

        ]

    ],
    'AdminCoach' => [
        [
            'code_action'=> 'coachAdd',

            'table'      => 'massage_service_coach_list',

            'action_type'=> '',

            'name'       => '技师管理',

            'text'       => '技师',

            'title'      => 'coach_name',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'add',

        ],
        [
            'code_action'=> 'updateCoachServicePrice',

            'table'      => 'massage_service_coach_list',

            'action_type'=> '',

            'name'       => '技师管理-修改服务价格',

            'text'       => '技师',

            'title'      => 'coach_name',

            'parameter'  => 'coach_id',

            'method'     => 'POST',

            'action'     => 'update',
        ],
        [
            'code_action'=> 'addCoachService',

            'table'      => 'massage_service_coach_list',

            'action_type'=> '',

            'name'       => '技师管理-添加服务',

            'text'       => '技师',

            'title'      => 'coach_name',

            'parameter'  => 'coach_id',

            'method'     => 'POST',

            'action'     => 'update',

        ],
        [
            'code_action'=> 'delCoachService',

            'table'      => 'massage_service_coach_list',

            'action_type'=> '',

            'name'       => '技师管理-删除服务',

            'text'       => '技师',

            'title'      => 'coach_name',

            'parameter'  => 'coach_id',

            'method'     => 'POST',

            'action'     => 'update',

        ],
        [
            'code_action'=> 'walletPass',

            'table'      => 'massage_service_wallet_list',

            'action_type'=> '',

            'name'       => '财务管理-提现申请',

            'text'       => '提现',

            'title'      => 'code',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'pass',
            //自定义参数
            'transmit_parameters' => [

                'key' => 'status',

                'value' => 2
            ],

        ],
        [
            'code_action'=> 'walletPass',

            'table'      => 'massage_service_wallet_list',

            'action_type'=> '',

            'name'       => '财务管理-提现申请',

            'text'       => '提现',

            'title'      => 'code',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'nopass',
            //自定义参数
            'transmit_parameters' => [

                'key' => 'status',

                'value' => 3
            ],

        ],
        [
            'code_action'=> 'coachDataUpdate',

            'table'      => 'massage_service_coach_list',

            'action_type'=> '',

            'name'       => '技师管理',

            'text'       => '技师',

            'title'      => 'coach_name',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'update',

        ],
        [
            'code_action'=> 'coachUpdateAdmin',

            'table'      => 'massage_service_coach_list',

            'action_type'=> '',

            'name'       => '技师管理',

            'text'       => '技师',

            'title'      => 'coach_name',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'update',

        ],
        [
            'code_action'=> 'coachUpdateCheck',

            'table'      => 'massage_service_coach_list',

            'action_type'=> '',

            'name'       => '技师管理-重新审核',

            'text'       => '技师',

            'title'      => 'coach_name',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'pass',
            //自定义参数
            'transmit_parameters' => [

                'key' => 'status',

                'value' => 2
            ],

        ],

        [
            'code_action'=> 'coachAuthCheck',

            'table'      => 'massage_service_coach_list',

            'action_type'=> '',

            'name'       => '技师管理-技师认证',

            'text'       => '技师',

            'title'      => 'coach_name',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'pass',
            //自定义参数
            'transmit_parameters' => [

                'key' => 'auth_status',

                'value' => 2
            ],

        ]
        ,[
            'code_action'=> 'coachAuthCheck',

            'table'      => 'massage_service_coach_list',

            'action_type'=> '',

            'name'       => '技师管理-技师认证',

            'text'       => '技师',

            'title'      => 'coach_name',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'nopass',
            //自定义参数
            'transmit_parameters' => [

                'key' => 'auth_status',

                'value' => 3
            ],

        ],

        [
            'code_action'=> 'coachUpdateCheck',

            'table'      => 'massage_service_coach_list',

            'action_type'=> '',

            'name'       => '技师管理-重新审核',

            'text'       => '技师',

            'title'      => 'coach_name',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'nopass',
            //自定义参数
            'transmit_parameters' => [

                'key' => 'status',

                'value' => 4
            ],

        ],
        [
            'code_action'=> 'coachUpdate',

            'table'      => 'massage_service_coach_list',

            'action_type'=> '',

            'name'       => '技师管理',

            'text'       => '技师',

            'title'      => 'coach_name',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'recommend_set',
            //自定义参数
            'transmit_parameters' => [

                'key' => 'recommend',

                'value' => 1
            ],

        ],
        [
            'code_action'=> 'coachUpdate',

            'table'      => 'massage_service_coach_list',

            'action_type'=> '',

            'title'      => 'coach_name',

            'name'       => '技师管理',

            'text'       => '技师',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'recommend_cancel',
            //自定义参数
            'transmit_parameters' => [

                'key'  => 'recommend',

                'value' => 0
            ],

        ],
        [
            'code_action'=> 'coachUpdate',

            'table'      => 'massage_service_coach_list',

            'action_type'=> '',

            'name'       => '技师管理',

            'title'      => 'coach_name',

            'text'       => '技师',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'pass_coach',
            //自定义参数
            'transmit_parameters' => [

                'key' => 'status',

                'value' => 2
            ],

        ],
        [
            'code_action'=> 'coachUpdate',

            'table'      => 'massage_service_coach_list',

            'action_type'=> '',

            'name'       => '技师管理',

            'title'      => 'coach_name',

            'text'       => '技师',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'no_pass_coach',
            //自定义参数
            'transmit_parameters' => [

                'key' => 'status',

                'value' => 4
            ],

        ],
        [
            'code_action'=> 'coachUpdate',

            'table'      => 'massage_service_coach_list',

            'action_type'=> '',

            'name'       => '技师管理',

            'title'      => 'coach_name',

            'text'       => '技师',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'cancel_pass_coach',
            //自定义参数
            'transmit_parameters' => [

                'key' => 'status',

                'value' => 3
            ],

        ],
        [
            'code_action'=> 'coachUpdate',

            'table'      => 'massage_service_coach_list',

            'action_type'=> '',

            'name'       => '技师管理',

            'title'      => 'coach_name',

            'text'       => '技师',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'del',
            //自定义参数
            'transmit_parameters' => [

                'key' => 'status',

                'value' => -1
            ],

        ],
        [
            'code_action'=> 'coachUpdate',

            'table'      => 'massage_service_coach_list',

            'action_type'=> '',

            'name'       => '技师管理',

            'title'      => 'coach_name',

            'text'       => '技师',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'update_admin',
            //自定义参数
            'transmit_parameters' => [

                'key'   => 'admin_id',

                // 'value' => 4
            ],

        ],
        [
            'code_action'=> 'coachUpdate',

            'table'      => 'massage_service_coach_list',

            'action_type'=> '',

            'name'       => '技师管理',

            'title'      => 'coach_name',

            'text'       => '技师',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'update_partner',
            //自定义参数
            'transmit_parameters' => [

                'key'   => 'partner_id',

                // 'value' => 4
            ],

        ]
        ,[
            'code_action'=> 'levelUpdate',

            'table'      => 'massage_service_coach_level',

            'action_type'=> '',

            'name'       => '技师等级',

            'title'      => 'title',

            'text'       => '等级',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'update',
        ],
        [
            'code_action'=> 'levelAdd',

            'table'      => 'massage_service_coach_level',

            'action_type'=> '',

            'name'       => '技师等级',

            'text'       => '等级',

            'title'      => 'title',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'add',
        ],
        [
            'code_action'=> 'walletPass',

            'table'      => 'massage_service_wallet_list',

            'action_type'=> '',

            'name'       => '提现申请',

            'text'       => '提现',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'pass',

            'title'      => 'code'
        ],
        [
            'code_action'=> 'walletNoPass',

            'table'      => 'massage_service_wallet_list',

            'action_type'=> '',

            'name'       => '提现申请',

            'text'       => '提现',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'nopass',

            'title'      => 'code'
        ],
        [
            'code_action'=> 'policeUpdate',

            'table'      => 'massage_service_coach_police',

            'action_type'=> '',

            'name'       => '求救通知',

            'text'       => '通知',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'update',

            'title'      => 'id'
        ],
    ],

    'AdminOrder' => [

        [
            'code_action'=> 'adminUpdateOrder',

            'table'      => 'massage_service_order_list',

            'action_type'=> '',

            'name'       => '订单管理',

            'text'       => '订单',

            'title'      => 'order_code',

            'parameter'  => 'order_id',

            'method'     => 'POST',

            'action'     => 'coach_get_order',
            //自定义参数
            'transmit_parameters' => [

                'key'  => 'type',

                'value' => 3
            ],
            //自定义参数
            'custom_parameters' => [

                'key' => 'is_add',

                'value' => 0
            ],

        ],
        [
            'code_action'=> 'adminUpdateOrder',

            'table'      => 'massage_service_order_list',

            'action_type'=> '',

            'name'       => '订单管理',

            'text'       => '订单',

            'parameter'  => 'order_id',

            'title'      => 'order_code',

            'method'     => 'POST',

            'action'     => 'coach_setout_order',
            //自定义参数
            'transmit_parameters' => [

                'key'  => 'type',

                'value' => 4
            ],
            //自定义参数
            'custom_parameters' => [

                'key' => 'is_add',

                'value' => 0
            ],

        ],
        [
            'code_action'=> 'adminUpdateOrder',

            'table'      => 'massage_service_order_list',

            'action_type'=> '',

            'title'      => 'order_code',

            'name'       => '订单管理',

            'text'       => '订单',

            'parameter'  => 'order_id',

            'method'     => 'POST',

            'action'     => 'coach_arr_order',
            //自定义参数
            'transmit_parameters' => [

                'key'  => 'type',

                'value' => 5
            ],
            //自定义参数
            'custom_parameters' => [

                'key' => 'is_add',

                'value' => 0
            ],

        ],
        [
            'code_action'=> 'adminUpdateOrder',

            'table'      => 'massage_service_order_list',

            'action_type'=> '',

            'title'      => 'order_code',

            'name'       => '订单管理',

            'text'       => '订单',

            'parameter'  => 'order_id',

            'method'     => 'POST',

            'action'     => 'coach_start_order',
            //自定义参数
            'transmit_parameters' => [

                'key'  => 'type',

                'value' => 6
            ],  //自定义参数
            'custom_parameters' => [

                'key' => 'is_add',

                'value' => 0
            ],

        ],
        [
            'code_action'=> 'adminUpdateOrder',

            'table'      => 'massage_service_order_list',

            'action_type'=> '',

            'title'      => 'order_code',

            'name'       => '订单管理',

            'text'       => '订单',

            'parameter'  => 'order_id',

            'method'     => 'POST',

            'action'     => 'coach_end_order',
            //自定义参数
            'transmit_parameters' => [

                'key'  => 'type',

                'value' => 7
            ],
            //自定义参数
            'custom_parameters' => [

                'key' => 'is_add',

                'value' => 0
            ],

        ],
        [
            'code_action'=> 'adminUpdateOrder',

            'table'      => 'massage_service_order_list',

            'action_type'=> '',

            'title'      => 'order_code',

            'name'       => '订单管理',

            'text'       => '订单',

            'parameter'  => 'order_id',

            'method'     => 'POST',

            'action'     => 'refund_order',
            //自定义参数
            'transmit_parameters' => [

                'key'  => 'type',

                'value' => -1
            ],
            //自定义参数
            'custom_parameters' => [

                'key' => 'is_add',

                'value' => 0
            ],

        ],
        [
            'code_action'=> 'orderChangeCoach',

            'table'      => 'massage_service_order_list',

            'action_type'=> '',

            'title'      => 'order_code',

            'name'       => '订单管理',

            'text'       => '订单',

            'parameter'  => 'order_id',

            'method'     => 'POST',

            'action'     => 'change_order',


        ],     [
            'code_action'=> 'adminUpdateOrder',

            'table'      => 'massage_service_order_list',

            'action_type'=> 'add',

            'title'      => 'order_code',

            'name'       => '订单管理',

            'text'       => '订单',

            'parameter'  => 'order_id',

            'method'     => 'POST',

            'action'     => 'coach_get_order',
            //自定义参数
            'transmit_parameters' => [

                'key'  => 'type',

                'value' => 3
            ],
            //自定义参数
            'custom_parameters' => [

                'key' => 'is_add',

                'value' => 1
            ],

        ],
        [
            'code_action'=> 'adminUpdateOrder',

            'table'      => 'massage_service_order_list',

            'action_type'=> 'add',

            'title'      => 'order_code',

            'name'       => '订单管理',

            'text'       => '订单',

            'parameter'  => 'order_id',

            'method'     => 'POST',

            'action'     => 'coach_setout_order',
            //自定义参数
            'transmit_parameters' => [

                'key'  => 'type',

                'value' => 4
            ],
            //自定义参数
            'custom_parameters' => [

                'key' => 'is_add',

                'value' => 1
            ],

        ],
        [
            'code_action'=> 'adminUpdateOrder',

            'table'      => 'massage_service_order_list',

            'action_type'=> 'add',

            'title'      => 'order_code',

            'name'       => '订单管理',

            'text'       => '订单',

            'parameter'  => 'order_id',

            'method'     => 'POST',

            'action'     => 'coach_arr_order',
            //自定义参数
            'transmit_parameters' => [

                'key'  => 'type',

                'value' => 5
            ],
            //自定义参数
            'custom_parameters' => [

                'key' => 'is_add',

                'value' => 1
            ],

        ],
        [
            'code_action'=> 'adminUpdateOrder',

            'table'      => 'massage_service_order_list',

            'action_type'=> 'add',

            'title'      => 'order_code',

            'name'       => '订单管理',

            'text'       => '订单',

            'parameter'  => 'order_id',

            'method'     => 'POST',

            'action'     => 'coach_start_order',
            //自定义参数
            'transmit_parameters' => [

                'key'  => 'type',

                'value' => 6
            ],
            //自定义参数
            'custom_parameters' => [

                'key' => 'is_add',

                'value' => 1
            ],
        ],
        [
            'code_action'=> 'adminUpdateOrder',

            'table'      => 'massage_service_order_list',

            'action_type'=> 'add',

            'title'      => 'order_code',

            'name'       => '订单管理',

            'text'       => '订单',

            'parameter'  => 'order_id',

            'method'     => 'POST',

            'action'     => 'coach_end_order',
            //自定义参数
            'transmit_parameters' => [

                'key'  => 'type',

                'value' => 7
            ],
            //自定义参数
            'custom_parameters' => [

                'key' => 'is_add',

                'value' => 1
            ],

        ],
        [
            'code_action'=> 'passRefund',

            'table'      => 'massage_service_refund_order',

            'action_type'=> '',

            'title'      => 'order_code',

            'name'       => '服务退款',

            'text'       => '订单',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'pass_refund_order',
            //自定义参数
            'custom_parameters' => [

                'key' => 'is_add',

                'value' => 0
            ],

        ],
        [
            'code_action'=> 'addComment',

            'table'      => 'massage_service_coach_list',

            'action_type'=> '',

            'title'      => 'coach_name',

            'name'       => '评价管理-新增评价',

            'text'       => '技师',

            'parameter'  => 'coach_id',

            'method'     => 'POST',

            'action'     => 'add',

        ],
        [
            'code_action'=> 'passRefund',

            'table'      => 'massage_service_refund_order',

            'action_type'=> 'add',

            'title'      => 'order_code',

            'name'       => '加钟退款',

            'text'       => '订单',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'pass_refund_order',
            //自定义参数
            'custom_parameters' => [

                'key' => 'is_add',

                'value' => 1
            ],

        ],[
            'code_action'=> 'noPassRefund',

            'table'      => 'massage_service_refund_order',

            'action_type'=> '',

            'title'      => 'order_code',

            'name'       => '服务退款',

            'text'       => '订单',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'nopass_refund_order',
            //自定义参数
            'custom_parameters' => [

                'key' => 'is_add',

                'value' => 0
            ],

        ],
        [
            'code_action'=> 'noPassRefund',

            'table'      => 'massage_service_refund_order',

            'action_type'=> 'add',

            'title'      => 'order_code',

            'name'       => '加钟退款',

            'text'       => '订单',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'nopass_refund_order',
            //自定义参数
            'custom_parameters' => [

                'key' => 'is_add',

                'value' => 1
            ],

        ],[
            'code_action'=> 'commentLableUpdate',

            'table'      => 'massage_service_lable',

            'action_type'=> '',

            'name'       => '评价标签',

            'title'      => 'title',

            'text'       => '标签',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'update',

        ],
        [
            'code_action'=> 'commentLableAdd',

            'table'      => 'massage_service_lable',

            'action_type'=> '',

            'title'      => 'title',

            'name'       => '评价标签',

            'text'       => '标签',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'add',

        ],
        [
            'code_action'=> 'commentUpdate',

            'table'      => 'massage_service_order_comment',

            'action_type'=> '',

            'title'      => 'id',

            'name'       => '评价管理',

            'text'       => '评价',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'del',

        ]

    ],
    'AdminReseller' => [

        [
            'code_action'=> 'resellerUpdate',

            'table'      => 'massage_distribution_list',

            'action_type'=> '',

            'name'       => '分销商审核',

            'title'      => 'user_name',

            'text'       => '分销商',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'pass',
            //自定义参数
            'transmit_parameters' => [

                'key'  => 'status',

                'value' => 2
            ],

        ],
        [
            'code_action'=> 'resellerUpdate',

            'table'      => 'massage_distribution_list',

            'action_type'=> '',

            'title'      => 'user_name',

            'name'       => '分销商审核',

            'text'       => '分销商',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'cancel',
            //自定义参数
            'transmit_parameters' => [

                'key'  => 'status',

                'value' => 3
            ],

        ],
        [
            'code_action'=> 'resellerUpdate',

            'table'      => 'massage_distribution_list',

            'action_type'=> '',

            'title'      => 'user_name',

            'name'       => '分销商审核',

            'text'       => '分销商',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'nopass',
            //自定义参数
            'transmit_parameters' => [

                'key'  => 'status',

                'value' => 4
            ],

        ],
        [
            'code_action'=> 'resellerUpdate',

            'table'      => 'massage_distribution_list',

            'action_type'=> '',

            'title'      => 'user_name',

            'name'       => '分销商审核',

            'text'       => '分销商',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'del',
            //自定义参数
            'transmit_parameters' => [

                'key'  => 'status',

                'value' => -1
            ],

        ],

    ],
    'AdminChannel' => [
        [
            'code_action'=> 'setChannelBalance',

            'table'      => 'massage_channel_list',

            'action_type'=> '',

            'name'       => '渠道商提成比例',

            'title'      => 'user_name',

            'text'       => '渠道商',

            'parameter_arr'  => 1,

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'update',

        ],
        [
            'code_action'=> 'delChannelBalance',

            'table'      => 'massage_channel_list',

            'action_type'=> '',

            'name'       => '渠道商提成比例',

            'title'      => 'user_name',

            'text'       => '渠道商',

            'parameter_arr'  => 1,

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'del',

        ],
        [
            'code_action'=> 'channelUpdate',

            'table'      => 'massage_channel_list',

            'action_type'=> '',

            'name'       => '渠道商修改所属',

            'title'      => 'user_name',

            'text'       => '渠道商',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'updateBindArr',
            //自定义参数
            'transmit_parameters' => [

                'key'   => 'admin_id',

            ]
        ],
        [
            'code_action'=> 'channelUpdate',

            'table'      => 'massage_channel_list',

            'action_type'=> '',

            'name'       => '渠道商删除业务员',

            'title'      => 'user_name',

            'text'       => '渠道商',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'delBind',
            //自定义参数
            'transmit_parameters' => [

                'key'   => 'salesman_id',

                'value' => 0
            ]
        ],
        [
            'code_action'=> 'bindSalesman',

            'table'      => 'massage_salesman_list',

            'action_type'=> '',

            'name'       => '业务员管理-绑定渠道商',

            'title'      => 'user_name',

            'text'       => '业务员',

            'parameter'  => 'salesman_id',

            'method'     => 'POST',

            'action'     => 'bind',


        ],
        [
            'code_action'=> 'channelUpdate',

            'table'      => 'massage_channel_list',

            'action_type'=> '',

            'name'       => '渠道商',

            'title'      => 'user_name',

            'text'       => '渠道商',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'updateBind',
            //自定义参数
            'transmit_parameters' => [

                'key'  => 'salesman_id',

            ],
        ],
        [
            'code_action'=> 'channelUpdate',

            'table'      => 'massage_channel_list',

            'action_type'=> '',

            'name'       => '渠道商审核',

            'title'      => 'user_name',

            'text'       => '渠道商',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'del',
            //自定义参数
            'transmit_parameters' => [

                'key'  => 'status',

                'value' => -1
            ],

        ],
        [
            'code_action'=> 'channelUpdate',

            'table'      => 'massage_channel_list',

            'action_type'=> '',

            'name'       => '渠道商审核',

            'title'      => 'user_name',

            'text'       => '渠道商',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'pass',
            //自定义参数
            'transmit_parameters' => [

                'key'  => 'status',

                'value' => 2
            ],

        ],
        [
            'code_action'=> 'channelUpdate',

            'table'      => 'massage_channel_list',

            'action_type'=> '',

            'name'       => '渠道商审核',

            'title'      => 'user_name',

            'text'       => '渠道商',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'cancel',
            //自定义参数
            'transmit_parameters' => [

                'key'  => 'status',

                'value' => 4
            ],

        ],
        [
            'code_action'=> 'channelUpdate',

            'table'      => 'massage_channel_list',

            'action_type'=> '',

            'name'       => '渠道商审核',

            'title'      => 'user_name',

            'text'       => '渠道商',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'cancel',
            //自定义参数
            'transmit_parameters' => [

                'key'  => 'status',

                'value' => 3
            ],

        ],
        [
            'code_action'=> 'channelUpdate',

            'table'      => 'massage_channel_list',

            'action_type'=> '',

            'name'       => '渠道商审核',

            'title'      => 'user_name',

            'text'       => '渠道商',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'update',


        ],
        [
            'code_action'=> 'cateUpdate',

            'table'      => 'massage_channel_cate',

            'action_type'=> '',

            'title'      => 'title',

            'name'       => '渠道类目',

            'text'       => '类目',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'update',


        ],
        [
            'code_action'=> 'cateAdd',

            'table'      => 'massage_channel_cate',

            'action_type'=> '',

            'title'      => 'title',

            'name'       => '渠道类目',

            'text'       => '类目',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'add',


        ],
        [
            'code_action'=> 'channelQrAdd',

            'table'      => 'massage_channel_qr',

            'action_type'=> '',

            'title'      => 'code',

            'name'       => '渠道商',

            'parameter_arr' =>1,

            'text'       => '渠道码',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'add',

        ],
        [
            'code_action'=> 'channelQrUpdate',

            'table'      => 'massage_channel_qr',

            'action_type'=> '',

            'title'      => 'code',

            'name'       => '渠道商',

            'text'       => '渠道码',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'update',

        ],
        [
            'code_action'=> 'channelQrDel',

            'table'      => 'massage_channel_qr',

            'action_type'=> '',

            'title'      => 'code',

            'parameter_arr' =>1,

            'name'       => '渠道商',

            'text'       => '渠道码',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'del',

        ],
        [
            'code_action'=> 'bindChannel',

            'table'      => 'massage_channel_qr',

            'action_type'=> '',

            'title'      => 'code',

            'parameter_arr' =>1,

            'name'       => '绑定渠道人员',

            'text'       => '渠道码',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'update',

        ]

    ],
    'AdminArticle' => [

        [
            'code_action'=> 'articleUpdate',

            'table'      => 'massage_article_list',

            'action_type'=> '',

            'title'      => 'title',

            'name'       => '文章管理',

            'text'       => '文章',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'update',


        ],[
            'code_action'=> 'articleAdd',

            'table'      => 'massage_article_list',

            'title'      => 'title',

            'action_type'=> '',

            'name'       => '文章管理',

            'text'       => '文章',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'add',

        ],
        [
            'code_action'=> 'fieldUpdate',

            'table'      => 'massage_article_form_field',

            'action_type'=> '',

            'title'      => 'title',

            'name'       => '文章管理-表单字段',

            'text'       => '字段',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'update',

        ],
        [
            'code_action'=> 'fieldAdd',

            'table'      => 'massage_article_form_field',

            'action_type'=> '',

            'title'      => 'title',

            'name'       => '文章管理-表单字段',

            'text'       => '字段',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'add',

        ]

    ],
    'AdminCoupon' => [

        [
            'code_action'=> 'couponUpdate',

            'table'      => 'massage_service_coupon',

            'action_type'=> '',

            'title'      => 'title',

            'name'       => '卡券管理',

            'text'       => '卡券',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'update',

        ],
        [
            'code_action'=> 'couponRecordAdd',

            'table'      => 'massage_service_coupon',

            'action_type'=> '',

            'title'      => 'title',

            'name'       => '卡券管理-指定派发卡券',

            'text'       => '卡券',

            'parameter'  => 'coupon_id',

            'method'     => 'POST',

            'action'     => 'send',

        ],
        [
            'code_action'=> 'couponAdd',

            'table'      => 'massage_service_coupon',

            'action_type'=> '',

            'title'      => 'title',

            'name'       => '卡券管理',

            'text'       => '卡券',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'add',

        ],
        [
            'code_action'=> 'couponAtvUpdate',

            'table'      => 'massage_service_coupon_atv',

            'action_type'=> '',

            'name'       => '邀请有礼',

            'text'       => '活动',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'update',

        ]

    ],

    'AdminBalance' => [

        [
            'code_action'=> 'cardUpdate',

            'table'      => 'massage_service_balance_card',

            'action_type'=> '',

            'name'       => '储值管理',

            'title'      => 'title',

            'text'       => '套餐',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'update',

        ],
        [
            'code_action'=> 'payBalanceOrder',

            'table'      => 'massage_service_balance_card',

            'action_type'=> 'payBalanceOrder',

            'name'       => '储值管理-定向储值',

            'text'       => '',

            'parameter'  => 'card_id',

            'method'     => 'POST',

            'action'     => 'payBalanceOrder',
            //自定义参数
            'transmit_parameters' => [

                'key' => 'card_id',

                'value' => 0
            ],

        ],

        [
            'code_action'=> 'payBalanceOrder',

            'table'      => 'massage_service_balance_card',

            'action_type'=> '',

            'name'       => '储值管理-定向储值',

            'title'      => 'title',

            'text'       => '套餐',

            'parameter'  => 'card_id',

            'method'     => 'POST',

            'action'     => 'update',

        ],
        [
            'code_action'=> 'cardAdd',

            'table'      => 'massage_service_balance_card',

            'action_type'=> '',

            'name'       => '储值管理',

            'title'      => 'title',

            'text'       => '套餐',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'add',

        ]
    ],
    'AdminDynamicList' => [

        [
            'code_action'=> 'dynamicTop',

            'table'      => 'massage_dynamic_list',

            'action_type'=> '',

            'title'      => 'title',

            'name'       => '动态管理',

            'text'       => '动态',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'top_one',

        ],
        [
            'code_action'=> 'dynamicCheck',

            'table'      => 'massage_dynamic_list',

            'action_type'=> '',

            'title'      => 'title',

            'name'       => '动态管理',

            'text'       => '动态',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'pass',
            //自定义参数
            'transmit_parameters' => [

                'key'  => 'status',

                'value' => 2
            ],

        ],
        [
            'code_action'=> 'dynamicCheck',

            'table'      => 'massage_dynamic_list',

            'action_type'=> '',

            'title'      => 'title',

            'name'       => '动态管理',

            'text'       => '动态',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'nopass',
            //自定义参数
            'transmit_parameters' => [

                'key'  => 'status',

                'value' => 3
            ],

        ],
        [
            'code_action'=> 'dynamicDel',

            'table'      => 'massage_dynamic_list',

            'action_type'=> '',

            'title'      => 'title',

            'name'       => '动态管理',

            'text'       => '动态',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'del',

        ],[
            'code_action'=> 'commentCheck',

            'table'      => 'massage_dynamic_comment',

            'action_type'=> '',

            'title'      => 'id',

            'name'       => '动态评论',

            'text'       => '评论',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'pass',
            //自定义参数
            'transmit_parameters' => [

                'key'  => 'status',

                'value' => 2
            ],

        ],
        [
            'code_action'=> 'commentCheck',

            'table'      => 'massage_dynamic_comment',

            'action_type'=> '',

            'title'      => 'id',

            'name'       => '动态评论',

            'text'       => '评论',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'nopass',
            //自定义参数
            'transmit_parameters' => [

                'key'  => 'status',

                'value' => 3
            ],

        ],
        [
            'code_action'=> 'commentDel',

            'title'      => 'id',

            'table'      => 'massage_dynamic_comment',

            'action_type'=> '',

            'name'       => '动态评论',

            'text'       => '评论',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'del',

        ]

    ],
    'AppUpgrade' => [


        [
            'code_action'=> 'upgrade',

            'table'      => '',

            'action_type'=> '',

            'name'       => '系统升级',

            'text'       => '系统',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'upgrade',

        ]

    ],
    'Config' => [

        [
            'code_action'=> 'updateOssConfig',

            'table'      => 'shequshop_school_oos_config',

            'action_type'=> '',

            'name'       => '上传设置',

            'text'       => '设置',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'update',

        ]

    ],
    'AdminPrinter' => [

        [
            'code_action'=> 'printerUpdate',

            'table'      => 'massage_service_printer',

            'action_type'=> '',

            'name'       => '打印机设置',

            'text'       => '设置',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'update',

        ]

    ],
    'Admin' => [
        [
            'code_action'=> 'delContract',

            'table'      => 'massage_fxq_contract_list',

            'action_type'=> '',

            'title'      => 'id',

            'name'       => '系统设置-电子合同（放心签）',

            'text'       => '合同',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'del',
        ],
        [
            'code_action'=> 'companySign',

            'table'      => 'massage_fxq_contract_list',

            'action_type'=> '',

            'title'      => 'id',

            'name'       => '系统设置-电子合同（放心签）',

            'text'       => '商家签署合同',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'company_sign',
        ],
        [
            'code_action'=> 'setConfig',

            'table'      => 'shequshop_school_config',

            'action_type'=> '',

            'name'       => '系统设置-电子合同（放心签）',

            'text'       => '',

            'parameter'  => '',

            'method'     => 'POST',

            'action'     => 'update',
        ],
        [
            'code_action'=> 'addContract',

            'table'      => 'massage_fxq_contract_list',

            'action_type'=> '',

            'title'      => 'id',

            'name'       => '系统设置-电子合同（放心签）',

            'text'       => '合同',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'add',
        ],
        [
            'code_action'=> 'login',

            'table'      => 'shequshop_school_admin',

            'action_type'=> '',

            'name'       => '系统',

            'text'       => '',

            'parameter'  => '',

            'method'     => 'POST',

            'action'     => 'login',

        ]

    ],
    'AdminExcel' => [

        [
            'code_action'=> 'orderList',

            'table'      => 'massage_service_order_list',

            'action_type'=> '',

            'name'       => '订单管理',

            'text'       => '',

            'parameter'  => '',

            'method'     => 'GET',

            'action'     => 'excel',

        ],
        [
            'code_action'=> 'subDataList',

            'table'      => 'massage_service_order_list',

            'action_type'=> '',

            'name'       => '文章管理',

            'text'       => '',

            'parameter'  => '',

            'method'     => 'GET',

            'action'     => 'excel',

        ],
        [
            'code_action'=> 'financeDetailedList',

            'table'      => 'massage_service_order_list',

            'action_type'=> '',

            'name'       => '财务报表',

            'text'       => '',

            'parameter'  => '',

            'method'     => 'GET',

            'action'     => 'excel',

        ],[
            'code_action'=> 'coachDataList',

            'table'      => 'massage_service_order_list',

            'action_type'=> '',

            'name'       => '技师数据',

            'text'       => '',

            'parameter'  => '',

            'method'     => 'GET',

            'action'     => 'excel',

        ],[
            'code_action'=> 'walletList',

            'table'      => 'massage_service_order_list',

            'action_type'=> '',

            'name'       => '提现数据',

            'text'       => '',

            'parameter'  => '',

            'method'     => 'GET',

            'action'     => 'excel',

        ]

    ],
    'AdminUser' => [

        [
            'code_action'=> 'delUserLabel',

            'table'      => 'massage_service_user_list',

            'action_type'=> '',

            'title'      => 'nickName',

            'name'       => '客户-删除标签',

            'text'       => '用户',

            'parameter'  => 'user_id',

            'method'     => 'POST',

            'action'     => 'del',

        ],
        [
            'code_action'=> 'adminUpdateCoachCommisson',

            'table'      => 'massage_service_order_commission',

            'action_type'=> '',

            'title'      => 'id',

            'name'       => '分销佣金-线下技师转账',

            'text'       => '',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'pass',

        ],[
            'code_action'=> 'updateUserGrowth',

            'table'      => 'massage_service_user_list',

            'action_type'=> '',

            'title'      => 'nickName',

            'name'       => '成长值',

            'text'       => '用户',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'update',

        ]
    ],
    'AdminStore' => [

        [
            'code_action'=> 'storeAdd',

            'table'      => 'massage_store_list',

            'action_type'=> '',

            'title'      => 'title',

            'name'       => '门店管理',

            'text'       => '门店',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'add',

        ],
        [
            'code_action'=> 'storeUpdate',

            'table'      => 'massage_store_list',

            'action_type'=> '',

            'title'      => 'title',

            'name'       => '门店管理',

            'text'       => '门店',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'update',

        ],[
            'code_action'=> 'cateUpdate',

            'table'      => 'massage_store_cate',

            'action_type'=> '',

            'title'      => 'title',

            'name'       => '门店管理',

            'text'       => '门店分类',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'update',

        ],
        [
            'code_action'=> 'cateAdd',

            'table'      => 'massage_store_cate',

            'action_type'=> '',

            'title'      => 'title',

            'name'       => '门店管理',

            'text'       => '门店分类',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'add',

        ]
    ],
    'AdminSalesman' => [

        [
            'code_action'=> 'checkSalesman',

            'table'      => 'massage_salesman_list',

            'action_type'=> '',

            'title'      => 'user_name',

            'name'       => '业务员管理',

            'text'       => '业务员',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'pass',
            //自定义参数
            'transmit_parameters' => [

                'key' => 'status',

                'value' => 2
            ],

        ],
        [
            'code_action'=> 'checkSalesman',

            'table'      => 'massage_salesman_list',

            'action_type'=> '',

            'title'      => 'user_name',

            'name'       => '业务员管理',

            'text'       => '业务员',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'nopass',
            //自定义参数
            'transmit_parameters' => [

                'key' => 'status',

                'value' => 4
            ],

        ],
        [
            'code_action'=> 'checkSalesman',

            'table'      => 'massage_salesman_list',

            'action_type'=> '',

            'title'      => 'user_name',

            'name'       => '业务员管理',

            'text'       => '业务员',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'cancel',
            //自定义参数
            'transmit_parameters' => [

                'key' => 'status',

                'value' => 3
            ],

        ],
        [
            'code_action'=> 'checkSalesman',

            'table'      => 'massage_salesman_list',

            'action_type'=> '',

            'title'      => 'user_name',

            'name'       => '业务员管理',

            'text'       => '业务员',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'del',
            //自定义参数
            'transmit_parameters' => [

                'key' => 'status',

                'value' => -1
            ],

        ],
        [
            'code_action'=> 'checkSalesman',

            'table'      => 'massage_salesman_list',

            'action_type'=> '',

            'title'      => 'user_name',

            'name'       => '业务员管理-编辑代理商',

            'text'       => '业务员',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'update',
            //自定义参数
            'transmit_parameters' => [

                'key' => 'admin_id',

            ],

        ]
    ],
    'AdminIndex' =>[

        [
            'code_action'=> 'expectationCityUpdate',

            'table'      => 'massage_expectation_city_list',

            'action_type'=> '',

            'title'      => 'city',

            'name'       => '用户城市投票',

            'text'       => '城市',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'pass',
        ]
    ],
    'AdminMember' => [

        [

            'code_action'=> 'levelAdd',

            'table'      => 'massage_member_level',

            'action_type'=> '',

            'title'      => 'title',

            'name'       => '会员等级',

            'text'       => '等级',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'add',

        ],
        [

            'code_action'=> 'levelUpdate',

            'table'      => 'massage_member_level',

            'action_type'=> '',

            'title'      => 'title',

            'name'       => '会员等级',

            'text'       => '等级',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'update',
        ],
        [

            'code_action'=> 'levelStatusUpdate',

            'table'      => 'massage_member_level',

            'action_type'=> '',

            'title'      => 'title',

            'name'       => '会员等级',

            'text'       => '等级',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'stop',
            //自定义参数
            'transmit_parameters' => [

                'key' => 'status',

                'value' => 0
            ]
        ],
        [

            'code_action'=> 'levelStatusUpdate',

            'table'      => 'massage_member_level',

            'action_type'=> '',

            'title'      => 'title',

            'name'       => '会员等级',

            'text'       => '等级',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'start',
            //自定义参数
            'transmit_parameters' => [

                'key' => 'status',

                'value' => 1
            ]
        ],
        [

            'code_action'=> 'levelStatusUpdate',

            'table'      => 'massage_member_level',

            'action_type'=> '',

            'title'      => 'title',

            'name'       => '会员等级',

            'text'       => '等级',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'del',
            //自定义参数
            'transmit_parameters' => [

                'key' => 'status',

                'value' => -1
            ]
        ],
        [

            'code_action'=> 'rightsUpdate',

            'table'      => 'massage_member_rights',

            'action_type'=> '',

            'title'      => 'title',

            'name'       => '会员权益',

            'text'       => '权益',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'update',

        ],
        [

            'code_action'=> 'configUpdate',

            'table'      => 'massage_member_rights',

            'action_type'=> 'member',

            // 'title'      => 'title',

            'name'       => '会员配置',

            'text'       => '',

            'parameter'  => '',

            'method'     => 'POST',

            'action'     => 'update',
            //自定义参数
            'transmit_parameters' => [

                'key' => 'growth_limit',

            ]

        ],
        [

            'code_action'=> 'memberUpdate',

            'table'      => 'shequshop_adapay_member',

            'action_type'=> '',

            'title'      => 'member_id',

            'name'       => '分账账户',

            'text'       => '账户ID',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'update',
        ],
        [

            'code_action'=> 'memberAdd',

            'table'      => 'shequshop_adapay_member',

            'action_type'=> '',

            'title'      => 'member_id',

            'name'       => '分账账户',

            'text'       => '账户ID',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'add',
        ]
        ,[

        'code_action'=> 'configUpdate',

        'table'      => 'massage_member_rights',

        'action_type'=> 'adapay',

        // 'title'      => 'title',

        'name'       => '分账配置',

        'text'       => '',

        'parameter'  => '',

        'method'     => 'POST',

        'action'     => 'update',
        //自定义参数
        'transmit_parameters' => [

            'key' => 'api_key_live',

        ]

    ]




    ],
    'AdminPackage' => [

    [
        'code_action'=> 'edit',

        'table'      => 'massage_store_package_list',

        'action_type'=> '',

        'title'      => 'name',

        'name'       => '门店管理-团购/套餐管理',

        'text'       => '套餐',

        'parameter'  => 'id',

        'method'     => 'POST',

        'action'     => 'update',

    ],
    [
        'code_action'=> 'add',

        'table'      => 'massage_store_package_list',

        'action_type'=> '',

        'title'      => 'name',

        'name'       => '门店管理-团购/套餐管理',

        'text'       => '套餐',

        'parameter'  => 'id',

        'method'     => 'POST',

        'action'     => 'add',

    ],
    [
        'code_action'=> 'updateStatus',

        'table'      => 'massage_store_package_list',

        'action_type'=> '',

        'title'      => 'name',

        'name'       => '门店管理-团购/套餐管理',

        'text'       => '套餐',

        'parameter'  => 'id',

        'method'     => 'POST',

        'action'     => 'del',

        //自定义参数
        'transmit_parameters' => [

            'key'  => 'status',

            'value' => -1
        ],

    ],
    [
        'code_action'=> 'updateStatus',

        'table'      => 'massage_store_package_list',

        'action_type'=> '',

        'title'      => 'name',

        'name'       => '门店管理-团购/套餐管理',

        'text'       => '套餐',

        'parameter'  => 'id',

        'method'     => 'POST',

        'action'     => 'update',
    ],
],
    'AdminPartner' => [
        [
            'code_action'=> 'partnerCheck',

            'table'      => 'massage_partner_order',

            'action_type'=> '',

            'title'      => 'id',

            'name'       => '组局活动-活动审核',

            'text'       => '活动',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'pass',

            'transmit_parameters' => [

                'key'  => 'status',

                'value' => 4
            ],
        ],
        [
            'code_action'=> 'partnerCheck',

            'table'      => 'massage_partner_order',

            'action_type'=> '',

            'title'      => 'id',

            'name'       => '组局活动-活动审核',

            'text'       => '活动',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'nopass',

            'transmit_parameters' => [

                'key'  => 'status',

                'value' => 3
            ],
        ],
        [
            'code_action'=> 'typeAdd',

            'table'      => 'massage_partner_type',

            'action_type'=> '',

            'title'      => 'name',

            'name'       => '组局活动-活动类型',

            'text'       => '类型',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'add',
        ],
        [
            'code_action'=> 'typeEdit',

            'table'      => 'massage_partner_type',

            'action_type'=> '',

            'title'      => 'name',

            'name'       => '组局活动-活动类型',

            'text'       => '类型',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'update',
        ],
        [
            'code_action'=> 'fieldAdd',

            'table'      => 'massage_partner_field',

            'action_type'=> '',

            'title'      => 'name',

            'name'       => '组局活动-报名字段',

            'text'       => '类型',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'add',
        ],
        [
            'code_action'=> 'fieldEdit',

            'table'      => 'massage_partner_field',

            'action_type'=> '',

            'title'      => 'name',

            'name'       => '组局活动-报名字段',

            'text'       => '类型',

            'parameter'  => 'id',

            'method'     => 'POST',

            'action'     => 'update',
        ],
    ]
];


return $log;





