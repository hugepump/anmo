<?php
namespace app\admin\model;

use app\BaseModel;
use think\facade\Db;
class AdminRole extends BaseModel
{
	//定义表名
	protected $name = 'longbing_admin_role';
	protected $pk   = 'ur_id';
	
	//初始化
	function __construct() {
		parent::__construct();
	}
	
	protected static function init(){
        //TODO:初始化内容
    }
	
	//创建用户权限
	public function createUserRole($data){
		$data['ur_id'] = uuid();
		$data['create_time'] = $this->time();
		return $this->save($data);
	}
	//删除用户权限 
	public function delUserRole($filter){
		return $this->delRow($filter);
	}

}