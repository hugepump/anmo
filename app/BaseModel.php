<?php
namespace app;
use think\Model;
use think\facade\Db;
class BaseModel extends Model {
	//设置数据库表名称
	protected $name = 'longbing';
	protected $type = [
        'create_time' => 'int',
        'update_time' => 'int',
        'delete_time' => 'int',
        'deleted'     => 'int'
    ];
	//设置操作时间
//	public 	  $time;
//	function __construct() {
//		parent::__construct();
//		$this->time = time();
//	}
//	//获取详情
//	public function getRow($filter) {
//		$filter['deleted'] = 0;
//		return $this->where($filter)->find();
//	}
//	//获取列表
//	public function listRow($filter ,$field = []) {
//		$filter['deleted'] = 0;
//		return $this->where($filter)
//			->order('create_time', 'asc')
//			->field($field)
//			->select();
//	}
//	//创建
//	public function createRow($data) {
//		$data['create_time'] = time();
//		return $this->save($data);
//	}
//	//批量创建
//	public function createRows($datas) {
//		return $this->saveAll($datas);
//	}
//	
//	//更新
//	public function updateRow($filter ,$data) {
//		$filter['deleted'] = 0;
//		$data['update_time'] = time();
//		$result = $this->where($filter)->update($data);
//		return $result;
//	}
//	//删除
//	public function deleteRow($filter) {
//		$filter['deleted'] = 0;
//		return $this->updateRow($filter ,['delete_time' => time() ,'deleted' => 1]);
//	}

	//获取详情
	public function getRow($filter) 
	{
		$filter['deleted'] = 0;
		$result  = $this
			->where($filter)
			->find();
		if(!empty($result)) $result = $result->toArray();
		return $result;
	}
	//获取列表
	public function listRow($filter) 
	{
		$filter['deleted'] = 0;
		$result = $this
			->where($filter)
			->select();
		if(!empty($result)) $result = $result->toArray();
		return $result;
	}
	//创建
	public function createRow($data) 
	{
	    if(!empty($data['create_time'])){
            $data['create_time'] = time();
        }
		return $this->save($data);
	}
	//批量创建
	public function createRows($datas) 
	{
		return $this->saveAll($datas);
	}
    public function upsave($filter ,$data)
    {
        $data['update_time'] = time();
        $result = $this->where($filter)->update($data);
        if(empty($result)) return false;
        return true;
    }
	//更新
	public function updateRow($filter ,$data) 
	{
		$filter['deleted'] = 0;
		$data['update_time'] = time();
		$result = $this->where($filter)->update($data);
		if(empty($result)) return false;
		return true;
	}
	//删除
	public function deleteRow($filter) 
	{
		$filter['deleted'] = 0;
		$result =  $this->updateRow($filter ,['delete_time' => time() ,'deleted' => 1]);
		if(empty($result)) return false;
		return true;
	}

	//真删除
	public function destroyRow($filter)
	{
		$result =  $this->where($filter)->delete();
		if(empty($result)) return false;
		return true;
	}
} 