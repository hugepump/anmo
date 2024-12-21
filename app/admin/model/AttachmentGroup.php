<?php
namespace app\admin\model;

use app\BaseModel;
use think\facade\Db;
class AttachmentGroup extends BaseModel
{
	//定义表名
	protected $name = 'shequshop_school_attachment_group';
	//查询器
	public function searchNameAttr($query, $value, $data)
    {
        $query->where('name','like', '%' . $value . '%');
    }
	
	
	//创建
	function createGroup($data)
	{
		$result =  $this->save($data);
		return !empty($result);
	}
	
	//更新
	function updateGroup($filter ,$data)
	{
		$result =  $this->where($filter)->update($data);
		return !empty($result);
	}
	
	//获取列表
//	function listGroup($filter)
//	{
//		$result = $this->where($filter)->select();
//		if(!empty($result)) $result = $result->toArray();
//		return $result;
//	}
	
	//获取列表
	function listGroup($filter)
	{
		$result = $this->withSearch(['name'] ,$filter)->where('uniacid','=',$filter['uniacid'])
			->order('id')
			->select();
		if(!empty($result)) $result = $result->toArray();
		return $result;
	}
	
	//获取单个数据
	function getGroup($filter)
	{
		$result = $this->where($filter)->find();
		if(!empty($result)) $result = $result->toArray();
		return $result;
	}
	
	//删除
	function delGroup($filter)
	{
		return $this->destoryGroup($filter);
	}
	
	//删除（真删除）	
	function destoryGroup($filter)
	{
		$result = $this->where($filter)->delete();
		return !empty($result);
	}
}
