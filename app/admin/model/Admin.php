<?php
namespace app\admin\model;

use app\BaseModel;
use think\facade\Db;
class Admin extends BaseModel
{
	//定义表名
	protected $name = 'longbing_admin';
	//初始化
//	function __construct() {
//		parent::__construct();
//	}
	
	//权限关联
	public function role(){
        return $this->hasOne('Role' , 'role_id' ,'role_id');
    }
	//公司关联
	public function company() {
		return $this->hasOne('Company' ,'company_id' ,'company_id');
	}
	//创建admin
	public function createAdmin($data) {
		return $this->createRow($data);
	}
	
	//修改admin
	public function updateAdmin($filter ,$data) {
		return $this->updateRow($filter ,$data);	
	}
	
	//删除admin
	public function delAdmin($filter) {
		return $this->delRow($filter);
	}
	
	//获取admin详情
	public function getAdmin($filter) {
		$data = $this->with(['company' => function($query) {
				$query->where(['deleted' => 0 ,'status' => 1])->field('company_name ,is_top');
			}])
			->where($filter)
			->find();
		if(!empty($data)) $data = $data->toArray();
//		$data = Db::name($this->name)
//			->alias('admin')
//			->leftJoin('user' ,'admin.user_id = user.user_id')
//			->where(['admin.account' => 'admin'])
//			->find();
		return $data;
	}
	
	//获取admin列表
	public function listAdmin($filter ,$page_config) {
		
	}
	
	//获取admin总数
	public function listAdminCount($filter) {
		
	}
	
	//获取所有admin
	public function listAdminAll($filter) {
		
	}
}