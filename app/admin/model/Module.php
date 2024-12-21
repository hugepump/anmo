<?php
namespace app\admin\model;

use app\BaseModel;
use think\facade\Db;
class Module extends BaseModel
{
	//定义表名
	protected $name = 'longbing_module';
	//pk
	protected $pk = 'module_id';
	//初始化
//	function __construct() {
//		parent::__construct();
//	}
	
	//权限关联
	public function menu()
    {
        return $this->hasMany('menu' , 'module_id' ,'module_id');
    }
	//模块与app之间的关系
	public function moduleApp()
	{
		return $this->hasOne('moduleApp' ,'module_id' ,'module_id');
	}
	//获取模块列表
	public function listModuleAll($filter ,$uniacid = '7777')
	{
		$filter['deleted'] = 0;
		$result = $this->with(['moduleApp' => function($query) use ($uniacid) {
				$query->where(['deleted' => 0 ,'uniacid' => $uniacid]);
			}])
			->where($filter)
			->select();
		if(!empty($result)) $result = $result->toArray();
		return $result;
	}
	public function getModule($filter ,$uniacid = '7777')
	{
		$filter['deleted'] = 0;
		$result = $this->with(['moduleApp' => function($query) use ($uniacid) {
				$query->where(['deleted' => 0 ,'uniacid' => $uniacid]);
			}])
			->where($filter)
			->find();
		if(!empty($result)) $result = $result->toArray();
		return $result;	
	}
	//获取模块列表(分页)
	public function listModule() {
		
	}
}