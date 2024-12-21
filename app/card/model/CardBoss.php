<?php

namespace app\card\model;

use app\BaseModel;
use think\Model;


class CardBoss extends BaseModel
{
    //定义表名
    protected $name = 'longbing_card_boss';


    protected static function init ()
    {
        //TODO:初始化内容
    }

    public function bosslist($where){
        return $this->where($where)->value('boss');
    }
}