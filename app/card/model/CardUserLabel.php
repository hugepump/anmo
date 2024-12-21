<?php

namespace app\card\model;

use app\BaseModel;
use think\Model;


class CardUserLabel extends BaseModel
{
    //定义表名
    protected $name = 'longbing_card_user_label';


    protected static function init ()
    {
        //TODO:初始化内容
    }
    public function getlist($where){
        return $this->where($where)->group('user_id')->field('user_id,uniacid')->select()->toArray();
    }
}