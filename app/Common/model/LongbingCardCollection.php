<?php
namespace app\Common\model;

use app\BaseModel;

class LongbingCardCollection extends BaseModel
{
    //定义表名称
    protected $name = 'longbing_card_collection';
    
    public function getCollection($filter)
    {
        $result = $this->where($filter)->find();
        if(!empty($result)) $result = $result->toArray();
        return $result;
    }
    
    public function createCollection($data)
    {
        $data['create_time'] = time();
        $result = $this->save($data);
        return !empty($result);
    }
    
    public function listCollection($filter)
    {
        $result = $this->where($filter)->select();
        if(!empty($result)) $result = $result->toArray();
        return $result;
    }
    
    public function updateCollection($filter ,$data)
    {
        $data['update_time'] = time();
        $result = $this->where($filter)->update($data);
        return !empty($result);
    }
    
    public function delCollection($filter)
    {
        $result = $this->where($filter)->delete();
        return !empty($result);
    }
}