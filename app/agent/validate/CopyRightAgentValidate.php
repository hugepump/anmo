<?php


namespace app\agent\validate;


use think\Validate;

class CopyRightAgentValidate extends Validate
{
    protected $rule = [
        'id'  => ['require','number', '>=' => 1],
        'status'  => ['require','number', 'in' => [-1, 0, 1]],
        'name|名称' => ['require'],
        'image|版权图片' => [ 'require', 'length' => '10,500'],
        'text|版权文字' => ['require'],
        'phone|联系号码' => ['require', 'length' => '8,20'],
    ];

    protected $scene = [
        'create' => ['name', 'image', 'text', 'phone'],
        'get'  =>  ['id'],
        'destroy' => ['id']
    ];


    public function sceneUpdate()
    {
        return $this->only(['id', 'image','phone'])
            ->remove('image', 'require')
            ->remove('phone', 'require');
    }

}