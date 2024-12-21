<?php


namespace app\agent\validate;


use think\Validate;

class Cardauth2ArticleValidate extends Validate
{
    protected $rule =  [
        'modular_id' => 'require|number|gt:0',
        'number|获客文章数量' => 'require|integer|egt:0',
    ];

    protected $scene = [
        'create' => ['number', 'modular_id'],
    ];


}