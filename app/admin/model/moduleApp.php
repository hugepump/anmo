<?php
namespace app\admin\model;

use app\BaseModel;
use think\facade\Db;
class ModuleApp extends BaseModel
{
	//定义表名
	protected $name = 'longbing_module_app';
	//初始化
//	function __construct() {
//		parent::__construct();
//	}
	
	//关联信息
	public function Module(){
		return $this->belongsTo('Module' ,'module_id' ,'module_id');
	}
}