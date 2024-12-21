<?php
namespace app\Common\model;

use app\BaseModel;

class LongbingTabbar extends BaseModel
{
	//定义表名称
	protected $name = 'longbing_card_tabbar';
	
	public function getTabbar($filter)
	{
		$result = $this->where($filter)->find();
		if(!empty($result)) $result = $result->toArray();
		return $result;
	}
}