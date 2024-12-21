<?php
namespace app\admin\model;

use app\BaseModel;
use think\facade\Db;

class CardUser extends BaseModel
{
    //定义表名
    protected $name = 'longbing_card_user';


    protected static function init()
    {
        //TODO:初始化内容
    }
    public function username($idlist){
        return $this->whereIn('id',$idlist)->field('id,avatarUrl')->select();
    }
}