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

$refund_text = [

    [

        'type' => 1,

        'name' => 'cash_user_name',

        'cash' => 'user_cash',

        'table'=> 'massage_service_user_list',

        'title' => 'nickName',

        'text' => '用户分销'

    ],
    [

        'type' => [2,5,6],

        'name' => 'district_name',

        'cash' => 'district_cash',

        'table'=> 'shequshop_school_admin',

        'title' => 'agent_name',

        'text'  => '区县代理商',

        'city_type' => 2

    ],
    [

        'type'  => [3],

        'name'  => 'coach_name',

        'cash'  => 'coach_cash',

        'table' => 'massage_service_coach_list',

        'title' => 'coach_name',

        'text'  => '技师'

    ],[

        'type'  => [2,5,6],

        'name'  => 'city_name',

        'cash'  => 'city_cash',

        'table' => 'shequshop_school_admin',

        'title' => 'agent_name',

        'text'  => '市代理',

        'city_type' => 1

    ],
    [

        'type'  => [2,5,6],

        'name'  => 'province_name',

        'cash'  => 'province_cash',

        'table' => 'shequshop_school_admin',

        'title' => 'agent_name',

        'text'  => '省代理',

        'city_type' => 3

    ],[

        'type' => [9],

        'name' => 'partner_name',

        'cash' => 'partner_cash',

        'table'=> 'massage_service_user_list',

        'title' => 'nickName',

        'text' => '合伙人'

    ],[

        'type' => [10],

        'name' => 'channel_name',

        'cash' => 'channel_cash',

        'table'=> 'massage_channel_list',

        'title' => 'user_name',

        'text' => '渠道人'

    ],

];

return $arrs;