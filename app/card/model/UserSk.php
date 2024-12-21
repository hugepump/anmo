<?php
namespace app\card\model;

use app\BaseModel;



class UserSk extends BaseModel
{
    //定义表名
    protected $name = 'longbing_card_user_sk';
	
	public function getSk($filter)
	{
		$result = $this->where($filter)->find();
		if(!empty($result)) $result = $result->toArray();
		return $result;	
	}
}