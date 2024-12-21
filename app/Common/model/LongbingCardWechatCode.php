<?php
namespace app\Common\model;

use app\BaseModel;

class LongbingCardWechatCode extends BaseModel
{
	//定义表名称
//	protected $name = 'longbing_card_wechat_code';
	protected $name = 'shequshop_school_wechat_code';
	//创建
	public function createCode($data)
	{
		$data['create_time'] = time();
		$result = $this->createRow($data);
		return !empty($result);
	}
	//获取
	public function getCode($filter)
	{
		$filter['deleted'] = 0;
		$result = $this->getRow($filter);
		return $result;
	}
	//列表
	public function listCode($filter)
	{
		$filter['deleted'] = 0;
		$result = $this->listRow($filter);
		return $result;
	}
	//总数
	public function getCodeCount($filter)
	{
		$filter['deleted'] = 0;
		$count = $this->where($filter)->count();
		return $count;
	}
	//修改
	public function updateCode($filter ,$data)
	{
		$filter['deleted'] = 0;
		$data['update_time'] = time();
		$result = $this->updateRow($filter ,$data);
		return !empty($result);
	}
	//删除
	public function delCode($filter)
	{
		$filter['deleted'] = 0;
		$result = $this->deleteRow($filter);
		return !empty($result);
	}
	//删除(真删除)
	public function destoryCode($filter)
	{
		$result = $this->destoryRow($filter);
		return !empty($result);
	} 
}