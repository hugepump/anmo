<?php
namespace app\admin\model;

use app\BaseModel;
use think\facade\Db;
class CoreAttachment extends BaseModel
{
	//定义表名
	protected $name = 'shequshop_school_attachment';
	
	//查询器
	public function searchIdsAttr($query, $ids, $data)
    {
        $query->where('id','in' ,$ids);
    }
	//创建
	public function createAttach($data)
	{
        $data['module_upload_dir'] = 0;
        $data['displayorder'] = 0;
        $data['group_id'] = !empty($data['group_id'])?$data['group_id']:0;
        $result =  $this->save($data);
        $result = !empty($result);
        return $result;
	}
	
	//更新
	public function updateAttach($filter ,$data)
	{
		$result =  $this->where($filter)->update($data);
		$result = !empty($result);
		return $result;
	}
	
	//获取列表
	public function listAttach($filter ,$page_config)
	{
		$start_row = ($page_config['page'] - 1) * $page_config['page_count'];
		$end_row   = $page_config['page_count'];
		$result    = $this->where($filter)
			->order('createtime' ,'desc')
			->limit($start_row ,$end_row)
			->select();
		if(!empty($result)) $result = $result->toArray();
		return $result;
	}
	
	//获取数据总数
	public function listAttachCount($filter)
	{
		$result = $this->where($filter)
			->count();
		return $result;
	}


    //创建
    public function addAttach($data)
    {
        $data['module_upload_dir'] = 0;

        $result =  $this->insert($data);

        return $result;
    }
	
	//获取
	public function getAttach($filter)
	{
		$result = $this->where($filter)->find();
		if(!empty($result)) $result = $result->toArray();
		return $result;
	}
	
	//删除
	public function delAttach($filter)
	{
		return $this->destoryAttach($filter);
	}
	
	//删除(真删除)
	public function destoryAttach($filter)
	{
		//return $this->where($filter)->delete();
		$result = $this->withSearch(['ids'] ,$filter)->delete();
		return !empty($result);
	}
	
}
