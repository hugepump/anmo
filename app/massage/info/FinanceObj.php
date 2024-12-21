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

$arr = [

    [
        'type' => [1,14],

        'name' => 'nickName',
        //用户
        'obj_type' => 1,

        'field'  => 'a.top_id',

        'table'  => 'massage_service_user_list'
    ],
    [
        'type' => [2,5,6,11,19,20],

        'name' => 'agent_name',
        //代理商
        'obj_type' => 2,

        'field'  => 'a.top_id',

        'table'  => 'shequshop_school_admin'
    ],
    [
        'type' => [3,8,17,18],

        'name' => 'coach_name',
        //技师
        'obj_type' => 3,

        'field'  => 'a.top_id',

        'table'  => 'massage_service_coach_list'

    ],
    [
        'type' => [10],

        'name' => 'user_name',
        //渠道商
        'obj_type' => 4,

        'field'  => 'a.top_id',

        'table'  => 'massage_channel_list'
    ],
    [
        'type' => [12],

        'name' => 'user_name',
        //业务员
        'obj_type' => 5,

        'field'  => 'a.top_id',

        'table'  => 'massage_salesman_list'
    ],
    [
        'type' => [9],

        'name' => 'user_name',
        //经纪人
        'obj_type' => 6,

        'table'  => 'massage_coach_broker_list',

        'field'  => 'a.broker_id'
    ]
];

return $arr;