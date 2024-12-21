<?php
namespace app\admin\model;

use app\BaseModel;
use think\facade\Db;
class Company extends BaseModel
{
	//定义表名
	protected $name = 'longbing_company';
	//初始化
	function __construct() {
		parent::__construct();
	}
	
	//创建公司信息		
	function createCompany($data) {
		$data['create_time'] = $this->time;
		return $this->createRow($data);
	}
	
	//修改公司信息
	function updateCompany($filter ,$data) {
		$filter['deleted'] = 0;
		return $this->updateRow($filter ,$data);
	}
	
	//删除公司信息
	function delCompany($filter){
		$filter['deleted'] = 0;
		return $this->delRow($filter);
	}
	
	//获取公司详情
	function getCompany($filter) {
		$filter['company.deleted'] = 0;
		$company = $this
			->alias('company')
			->leftJoin('user user' ,'company.creator_id = user.user_id')
			->where($filter)
			->field(['company.*' ,'user.user_name as creator_name'])
			->find();
		return $company;
	}
	
	//获取公司列表
	function listCompany($filter ,$page_config) {
		$filter['user.deleted'] = 0;
		$companys = $this
			->alias('company')
			->leftJoin('user user' ,'company.creator_id = user.user_id')
			->where($filter)
			->field(['company.*' ,'user.user_name as creator_name'])
			->page($page_config['page'] ,$page_config['page_count'])	
			->select();
		return $companys;
	}
	
	//获取公司总数
	function listCompanyCount($filter) {
		$filter['user.deleted'] = 0;
		$count = $this
			->alias('company')
			->leftJoin('user user' ,'company.creator_id = user.user_id')
			->field(['company.company_id'])
			->where($filter)
			->count();
		return $count;
	}
}