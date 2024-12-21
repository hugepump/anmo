<?php
namespace app\admin\model;

use app\BaseModel;
use think\Model;


//底部菜单
class AppTabbar extends BaseModel
{
    //定义表名
    protected $name = 'longbing_card_tabbar';
	
	//创建
	public function createTabbar($data)
	{
		$data['create_time'] = time();
		$result = $this->save($data);
		return !empty($result);	
	}
	
	//获取
	public function getTabbar($filter)
	{
		$result = $this->where($filter)->find();
		if(!empty($result)) $result = $result->toArray();
		return $result;
	}
	
	//修改
	public function updateTabbar($filter ,$data)
	{
		$data['update_time'] = time();
		$result = $this->where($filter)->update($data);
		return !empty($result);		
	}
	
	//删除
	public function delTabbar($filter)
	{
		$result = $this->destoryTabbar($filter);
		return !empty($result);
	}
	
	//删除(真删除)
	public function destoryTabbar($filter)
	{
		$result = $this->where($filter)->delete()	;
		return !empty($result);
	}
	
}