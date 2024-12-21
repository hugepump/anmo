<?php
namespace app\admin\model;

use app\BaseModel;
use think\facade\Db;
class Menu extends BaseModel
{
	//定义表名
	protected $name = 'longbing_menu';
	//初始化
//	function __construct() {
//		parent::__construct();
//	}
	
	//获取菜单列表
	function listMenu($filter) {
		$filter['deleted'] = 0;
		$result = $this->where($filter)
			->select();
		if(!empty($result)) $result = $result->toArray();
		return $result;
	}
}