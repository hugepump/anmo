<?php
namespace app\Common\model;

use app\BaseModel;

class LongbingWeqingWxApp extends BaseModel
{
	//定义表名称
	protected $name = 'account_wxapp';
	
	public function getApp($filter)
	{
		$result = $this->where($filter)->find();
		if(!empty($result)) $result = $result->toArray();
		return $result;
	}
}