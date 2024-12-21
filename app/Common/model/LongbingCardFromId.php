<?php
namespace app\Common\model;

use app\BaseModel;

class LongbingCardFromId extends BaseModel
{
	//定义表名称
	protected $name = 'longbing_card_form_id';
	
	//获取Fromid
	public function getFromId($filter)
	{
		$result = $this->where($filter)->order('id asc')->find();
		if(!empty($result)) $result = $result->toArray();
		return $result; 
	}
	
	//删除7天前的fromid
	public function autoDelFromId()
	{
		$filter = [['create_time' ,'<' ,mktime( 0, 0, 0, date( 'm' ), date( 'd' ) - 6, date( 'Y' ) )]];
		$result = $this->delFromId($filter);
		return !empty($result);
	}
	//删除id
	public function delFromId($filter)
	{
		$result = $this->destoryFromId($filter);
		return !empty($result);	
	}
	
	//删除fromId（真删除）
	public function destoryFromId($filter)
	{
		$result = $this->where($filter)->delete();
		return !empty($result);
	}
}