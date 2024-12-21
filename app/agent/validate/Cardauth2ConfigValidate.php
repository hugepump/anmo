<?php


namespace app\agent\validate;


use think\Validate;

class Cardauth2ConfigValidate extends Validate
{

    protected $rule =  [
        'id' => 'require|number|gt:0',
        'number|名片数量' => 'require|integer|egt:0',
        'end_time|到期时间' => 'require|integer',
        'copyright_id|版权id' => 'require|integer',
        'send_switch' => 'require|integer',
        'boss|BOSS数量' => 'require|integer|egt:0',
//        'appiont' =>'require|integer',
        'payqr' =>'require|integer',
        'shop_switch' => 'require|integer',
        'timeline_switch' => 'require|integer',
        'website_switch' => 'require|integer',
        'article' => 'require|integer',
//        'activity_switch' => 'require|integer',
        'pay_shop' => 'require|integer',
        'house_switch' => 'require|integer',
//        'tool_switch' => 'require|integer',
    ];

    protected $scene = [
        'create' => ['number', 'end_time', 'copyright_id', 'send_switch',
            'boss', 'appiont', 'payqr', 'shop_switch', 'timeline_switch',
            'website_switch', 'article', 'activity', 'pay_shop' ,
            'house_switch'],
        'delete' => ['id']
    ];


    public function sceneUpdate()
    {
        return $this->only(['id','number', 'end_time', 'copyright_id', 'send_switch',
            'boss', 'appiont', 'payqr', 'shop_switch', 'timeline_switch',
            'website_switch', 'article', 'activity', 'pay_shop' ,
            'house_switch', 'tool_switch'])
            ->remove('number', 'require')
            ->remove('end_time', 'require')
            ->remove('copyright_id', 'require')
            ->remove('boss', 'require')
            ->remove('appiont', 'require')
            ->remove('payqr', 'require')
            ->remove('shop_switch', 'require')
            ->remove('timeline_switch', 'require')
            ->remove('website_switch', 'require')
            ->remove('article', 'require')
            ->remove('activity', 'require')
            ->remove('pay_shop', 'require')
            ->remove('house_switch', 'require')
            ->remove('tool_switch', 'require');
    }

}