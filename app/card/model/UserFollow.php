<?php
namespace app\card\model;

use app\BaseModel;



class UserFollow extends BaseModel
{
    //定义表名
    protected $name = 'longbing_card_user_follow';
    public function getCount($where){
        $data = $this->where($where)->count();
        return $data;
    }

}