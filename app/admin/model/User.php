<?php
namespace app\admin\model;

use app\BaseModel;
use think\facade\Db;
class User extends BaseModel
{
	//定义表名
	protected $name = 'user';
	//初始化
	function __construct() {
		parent::__construct();
	}
	
	//权限关联
	public function role()
    {
        return $this->hasOne('Role' , 'role_id' ,'role_id');
    }
	
	//获取用户信息
	public function getUser($filter) {
		$user = Db::name($this->name)
			->where(['deleted' => 0])
			->where($filter)
			->find();
		if(isset($user['user_id'])){
			$user['role'] = Db::name('user_role')
				->alias('ur')
				->leftJoin('role role' ,'ur.role_id = role.role_id')
				->where(['role.deleted' => 0 ,'ur.deleted' => 0])
				->where(['ur.user_id' => $user['user_id']])
				->field(['role.role_name' ,'role.role_id'])
				->select();
		}
		return $user;
	}
	//获取所有用户
	public function listUserAll($filter) {
		$users = Db::name($this->name)
			->alias('user')
			->leftJoin('department dp' ,'user.department_id = dp.department_id')
			->where(['user.deleted' => 0 ,'dp.deleted' => 0])
			->where($filter)
			->field(['user.*' ,'dp.department_name'])
			->select();
//			->leftJoin('role role' ,'user.role_id = role.role_id')
//			->where(['user.deleted' => 0 ,'role.deleted' => 0])
//			->where($filter)
//			->field(['user.*' ,'role.role_name'])
//			->select();
		return $users;
	}
	//获取用户列表
	public function listUser($filter ,$page_config = ['page' => 1 ,'page_count' => 10]) {
//		$start_row = ($page_config['page'] - 1) * $page_config['page_count'];
//		$end_row   = $page_config['page'] * $page_config['page_count'];
		$users = Db::name($this->name)
			->alias('user')
//			->leftJoin('role role' ,'user.role_id = role.role_id')
//			->where(['user.deleted' => 0 ,'role.deleted' => 0])
//			->where($filter)
//			->field(['user.*' ,'role.role_name'])
			->leftJoin('department dp' ,'user.department_id = dp.department_id')
			->where(['user.deleted' => 0 ,'dp.deleted' => 0])
			->where($filter)
			->field(['user.id' ,'dp.department_name'])
			->page($page_config['page'] ,$page_config['page_count'])
			->select();
		return $users;
	}
	//获取用户总数
	public function listUserCount($filter) {
		return $users = Db::name($this->name)
			->alias('user')
//			->leftJoin('role role' ,'user.role_id = role.role_id')
//			->where(['user.deleted' => 0 ,'role.deleted' => 0])
//			->where($filter)
//			->field(['user.*' ,'role.role_name'])
			->leftJoin('department dp' ,'user.department_id = dp.department_id')
			->where(['user.deleted' => 0 ,'dp.deleted' => 0])
			->where($filter)
			->field(['user.id'])
			->count();
	}
	
	//创建用户
	public function createUser($data) {
		return $this->createRow($data);
	}
	
	//修改用户
	public function updateUser($filter ,$data) {
		return $this->updateRow($filter ,$data);
	}
	
	//删除用户
	public function delUser($filter) {
		return $this->deleteRow($filter);
	}
}