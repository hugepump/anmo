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
        //合伙人
        'type' => 1,
        //技师分摊比列
        'coach_share_balance' => 'partner_coach_balance',
        //代理商分摊比列
        'agent_share_balance' => 'partner_admin_balance',
        //平台分摊比列
        'company_share_balance'=> 'partner_company_balance',
        //技师分摊金额
        'coach_share_cash' => 'partner_coach_cash',
        //代理商分摊金额
        'agent_share_cash' => 'partner_admin_cash',
        //平台分摊金额
        'company_share_cash'=> 'partner_company_cash',

        'admin_id'=> 'partner_admin_id',

        'cash'    => 'partner_cash'

    ],
    [

        //渠道商
        'type' => 2,
        //技师分摊比列
        'coach_share_balance' => 'channel_coach_balance',
        //代理商分摊比列
        'agent_share_balance' => 'channel_admin_balance',
        //平台分摊比列
        'company_share_balance'=> 'channel_company_balance',
        //技师分摊金额
        'coach_share_cash' => 'channel_coach_cash',
        //代理商分摊金额
        'agent_share_cash' => 'channel_admin_cash',
        //平台分摊金额
        'company_share_cash'=> 'channel_company_cash',

        'admin_id'=> 'channel_admin_id',

        'cash'    => 'channel_cash'

    ],
    [

        //业务员
        'type' => 3,
        //技师分摊比列
        'coach_share_balance' => 'salesman_coach_balance',
        //代理商分摊比列
        'agent_share_balance' => 'salesman_admin_balance',
        //平台分摊比列
        'company_share_balance'=> 'salesman_company_balance',
        //技师分摊金额
        'coach_share_cash' => 'salesman_coach_cash',
        //代理商分摊金额
        'agent_share_cash' => 'salesman_admin_cash',
        //平台分摊金额
        'company_share_cash'=> 'salesman_company_cash',

        'admin_id'=> 'salesman_admin_id',

        'cash'    => 'salesman_cash'

    ],[

        //分销员
        'type' => 4,
        //技师分摊比列
        'coach_share_balance' => 'reseller_coach_balance',
        //代理商分摊比列
        'agent_share_balance' => 'reseller_admin_balance',
        //平台分摊比列
        'company_share_balance'=> 'reseller_company_balance',
        //技师分摊金额
        'coach_share_cash' => 'reseller_coach_cash',
        //代理商分摊金额
        'agent_share_cash' => 'reseller_admin_cash',
        //平台分摊金额
        'company_share_cash'=> 'reseller_company_cash',

        'admin_id'=> 'reseller_admin_id',

        'cash'    => 'user_c_cash'

    ],
    [

        //二级分销员
        'type' => 5,
        //技师分摊比列
        'coach_share_balance' => 'level_reseller_coach_balance',
        //代理商分摊比列
        'agent_share_balance' => 'level_reseller_admin_balance',
        //平台分摊比列
        'company_share_balance'=> 'level_reseller_company_balance',
        //技师分摊金额
        'coach_share_cash' => 'level_reseller_coach_cash',
        //代理商分摊金额
        'agent_share_cash' => 'level_reseller_admin_cash',
        //平台分摊金额
        'company_share_cash'=> 'level_reseller_company_cash',

        'admin_id'=> 'level_reseller_admin_id',

        'cash'    => 'level_reseller_cash'

    ]

];

return $arrs;