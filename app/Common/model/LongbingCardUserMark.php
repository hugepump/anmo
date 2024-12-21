<?php
namespace app\Common\model;

use app\BaseModel;

class LongbingCardUserMark extends BaseModel
{
	//定义表名称
	protected $name = 'longbing_card_user_mark';

	//创建
	public function createMark($data)
	{
		$data['create_time'] = time();
		$result = $this->save($data);
		return !empty($result);
	}
	//修改
	public function updateMark($filter ,$data)
	{
		$data['update_time'] = time();
		$result = $this->where($filter)->update($data);
		return !empty($result);
	}
    //获取总数
    public function getMarkCount($filter)
    {
        $result = $this->where($filter)->count();
        return $result;
    }
    
    public function listMarkData($filter ,$field = null)
    {
        $result = $this->where($filter);
        if(empty($field) && is_array($field)) $result = $result->field($field);
        $result = $result->select();
        if(!empty($result)) $result = $result->toArray();
        return $result;
    }
    
    public function getMarkData($filter ,$field = null)
    {
        $result = $this->where($filter);
        if(empty($field) && is_array($field)) $result = $result->field($field);
        $result = $result->find();
        if(!empty($result)) $result = $result->toArray();
        return $result;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-08-07 10:18
     * @功能说明:获取跟进的用户
     */
    public function getMarkUser($dis){


        $data = $this->alias('a')
                ->join('longbing_card_user b','a.user_id = b.id')
                ->where($dis)
                ->column('a.id');

        return $data;

    }
}