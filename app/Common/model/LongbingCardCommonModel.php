<?php
namespace app\Common\model;

use app\BaseModel;

class LongbingCardCommonModel extends BaseModel
{
    //定义表名称
    protected $name = 'longbing';
        
    public function getRows($table_name,$filter)
    {
        $this->name = $table_name;
        $result = $this->where($filter)->find();
        if(!empty($result)) $result = $result->toArray();
        return $result;
    }
    
    public function getCount($table_name ,$filter)
    {
        $this->name = $table_name;
        $result = $this->where($filter)->count();
        return $result;
    }
    
    public function listRows($table_name ,$filter ,$field)
    {
        $this->name = $table_name;
        $result = $this->where($filter);
        if(!empty($field)) $result->field($field);
        $result = $result->select();
        if(!empty($result)) $result = $result->toArray();
        return $result;
    }
}