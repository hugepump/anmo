<?php
namespace app\card\model;

use app\BaseModel;



class UserPhone extends BaseModel
{
    //定义表名
    protected $name = 'longbing_card_user_phone';

	public function getUserPhone($user_id ,$uniacid = null)
	{
		$filter = ['user_id' => $user_id];
		if(!empty($uniacid)) $filter['uniacid'] = $uniacid;
		$result = $this->where($filter)->field('phone')->find();
		if(!empty($result)) $result = $result->toArray();
		return $result;
	}
}