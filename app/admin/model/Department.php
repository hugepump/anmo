<?php
namespace app\admin\model;

use app\BaseModel;
use think\facade\Db;
class Department extends BaseModel
{
	//定义表名
	protected $name = 'department';
	protected $pk   = 'department_id';
	
	//初始化
	function __construct() {
		parent::__construct();
	}
	
	protected static function init()
    {
        //TODO:初始化内容
    }
	//新建部门
	public function createDepartment($data) {
		return $this->createRow($data);
	}
	//更新部门信息
	public function updateDepartment($filter ,$data) {
		return $this->updateRow($filter ,$data);
	}
	//删除部门信息
	public function delDepartment($filter) {
		return $this->delRow($filter);
	}
	//获取部门信息
	public function getDepartment($filter) {
		return $this->getRow($filter);
	}
	//获取部门列表信息
	public function listDepartment($filter ,$page_config = ['page' => 1 ,'page_count' => 20]) {
		$filter['deleted'] = 0;
		$result = Db::name($this->name)
			->where($filter)
			->page($page_congig['page'] ,$page_config['page_count'])
			->select();
	}
	//获取部门列表总数
	public function listDepartmentCount($filter) {
		$filter['deleted'] = 0;
		$result = Db::name($this->name)
			->where($filter)
			->count();
	}
	//获取所有部门列表信息
	public function listDepartmentAll($filter) {
		return $this->listRow($filter);
	}
}
