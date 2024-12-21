<?php
namespace app\admin\model;

use app\BaseModel;

class Role extends BaseModel
{
	//定义表名
	protected $name = 'role';
	protected $pk   = 'role_id';
	protected $schema = [
        'role_id'          => 'string',
        'role_name'        => 'string',
        'is_system'        => 'int',
        'description'      => 'string',
        'create_time'      => 'int',
        'uniacid'          => 'int',
        'update_time'      => 'int',
        'deleted'	       => 'int'
    ];
	protected $resultSetType  = 'array';
	//初始化
	function __construct() {
		parent::__construct();
	}
	
	protected static function init()
    {
        //TODO:初始化内容
    }
	//用户关联绑定
	public function user() {
		return $this->belongsTo('AdminUser' ,'role_id' ,'role_id');
	}
	
	
	//获取权限信息
	public function getRole($filter) {	
		return $this->getRow($filter);
	}
	//获取权限列表
	public function listRoleAll($filter) {
//		return $this->select($filter);
		return $this->listRow($filter ,['role_id' ,'role_name' ,'is_system' ,'description' ,'deleted']);
	}
	//创建权限
	public function createRole($data) {
		return $this->createRow($data);
	}
	//更新权限
	public function updateRole($filter ,$data) {
		return $this->updateRow($filter ,$data);
	}
	//删除权限 
	public function deleteRole($filter) {
		return $this->deleteRow($filter);
	}
}