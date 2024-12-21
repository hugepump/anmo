<?php


namespace app\agent\validate;


use think\Validate;

class AgentAdminValidate extends Validate
{

    protected $rule = [
        'admin_id'  => ['require'],
        'status'  => ['require','number', 'in' => [-1, 0, 1]],
        'account|名字' => ['require'],
        'passwd|密码' => [ 'require'],
    ];

    protected $scene = [
        'addSubAdmin' => ['account', 'passwd'],
        'updateSubAdmin' => ['passwd'],
        'delSubAdmin' => ['admin_id']
    ];



}