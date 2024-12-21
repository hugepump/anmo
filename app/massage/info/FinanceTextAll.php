<?php
// +----------------------------------------------------------------------
// | Longbing [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright Chengdu longbing Technology Co., Ltd.
// +----------------------------------------------------------------------
// | Website http://longbing.org/
// +----------------------------------------------------------------------
// | Sales manager: +86-13558882532 / +86-13330887474
// | Technical support: +86-15680635005
// | After-sale service: +86-17361005938
// +----------------------------------------------------------------------

$arrs = [

    [

        'type' => [1],

        'name' => 'cash_user_name',

        'cash' => 'user_cash',

        'table'=> 'massage_service_user_list',

        'title' => 'nickName',

        'text' => '用户分销'

    ],
    [

        'type' => [2,5,6,19,20],

        'name' => 'district_name',

        'cash' => 'district_cash',

        'table'=> 'shequshop_school_admin',

        'title' => 'agent_name',

        'text'  => '区县代理商',

        'city_type' => 2

    ],
    [

        'type'  => [3,17,18],

        'name'  => 'coach_name',

        'cash'  => 'coach_cash',

        'table' => 'massage_service_coach_list',

        'title' => 'coach_name',

        'text'  => '技师'

    ],[

        'type'  => [2,5,6,19,20],

        'name'  => 'city_name',

        'cash'  => 'city_cash',

        'table' => 'shequshop_school_admin',

        'title' => 'agent_name',

        'text'  => '市代理',

        'city_type' => 1

    ],
    [

        'type'  => [2,5,6,19,20],

        'name'  => 'province_name',

        'cash'  => 'province_cash',

        'table' => 'shequshop_school_admin',

        'title' => 'agent_name',

        'text'  => '省代理',

        'city_type' => 3

    ],
    [
        'type'  => [8],

        'name'  => 'car_name',

        'cash'  => 'car_cash',

        'table' => 'massage_service_coach_list',

        'title' => 'coach_name',

        'text'  => '车费'

    ],
    [
        'type'  => [13],

        'name'  => 'car_name',

        'cash'  => 'admin_car_cash',

        'table' => 'massage_service_coach_list',

        'title' => 'coach_name',

        'text'  => '车费'

    ],
    [

        'type' => [9],

        'name' => 'partner_name',

        'cash' => 'partner_cash',

        'table'=> 'massage_coach_broker_list',

        'title' => 'user_name',

        'text' => '合伙人',

        'top_id' => 'broker_id'

    ],[

        'type' => [10],

        'name' => 'channel_name',

        'cash' => 'channel_cash',

        'table'=> 'massage_channel_list',

        'title' => 'user_name',

        'text' => '渠道人'

    ],[

        'type' => [12],

        'name' => 'salesman_name',

        'cash' => 'salesman_cash',

        'table'=> 'massage_salesman_list',

        'title' => 'user_name',

        'text' => '业务员'

    ],[

        'type' => [14],

        'name' => 'level_reseller_name',

        'cash' => 'level_reseller_cash',

        'table'=> 'massage_service_user_list',

        'title' => 'nickName',

        'text' => '二级分销'

    ],
//    [
//
//        'type' => [17],
//
//        'name' => 'coach_name',
//
//        'cash' => 'coach_refund_empty_cash',
//
//        'table' => 'massage_service_coach_list',
//
//        'title' => 'coach_name',
//
//        'text' => '技师空单费'
//
//    ],
//    [
//
//        'type' => [18],
//
//        'name' => 'coach_name',
//
//        'cash' => 'coach_refund_comm_cash',
//
//        'table' => 'massage_service_coach_list',
//
//        'title' => 'coach_name',
//
//        'text' => '技师退款手续费'
//    ],
//    [
//
//        'type' => [19],
//
//        'name' => 'city_name',
//
//        'cash' => 'city_refund_empty_cash',
//
//        'table'=> 'shequshop_school_admin',
//
//        'title' => 'agent_name',
//
//        'text' => '代理商空单费',
//
//        'city_type' => 1
//
//    ],
//    [
//
//        'type' => [19],
//
//        'name' => 'province_name',
//
//        'cash' => 'province_refund_empty_cash',
//
//        'table'=> 'shequshop_school_admin',
//
//        'title' => 'agent_name',
//
//        'text' => '代理商空单费',
//
//        'city_type' => 3
//
//    ]
//    ,
//    [
//
//        'type' => [19],
//
//        'name' => 'district_name',
//
//        'cash' => 'district_refund_empty_cash',
//
//        'table'=> 'shequshop_school_admin',
//
//        'title' => 'agent_name',
//
//        'text' => '代理商空单费',
//
//        'city_type' => 2
//
//    ]
//    ,[
//
//        'type' => [20],
//
//        'name' => 'city_name',
//
//        'cash' => 'city_refund_comm_cash',
//
//        'table'=> 'shequshop_school_admin',
//
//        'title' => 'agent_name',
//
//        'text' => '代理商空单费',
//
//        'city_type' => 1
//
//    ],
//    [
//
//        'type' => [20],
//
//        'name' => 'province_name',
//
//        'cash' => 'province_refund_comm_cash',
//
//        'table'=> 'shequshop_school_admin',
//
//        'title' => 'agent_name',
//
//        'text' => '代理商空单费',
//
//        'city_type' => 3
//
//    ]
//    ,
//    [
//
//        'type' => [20],
//
//        'name' => 'district_name',
//
//        'cash' => 'district_refund_comm_cash',
//
//        'table'=> 'shequshop_school_admin',
//
//        'title' => 'agent_name',
//
//        'text' => '代理商空单费',
//
//        'city_type' => 2
//
//    ]
];

return $arrs;