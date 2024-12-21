<?php
namespace app\Common\model;

use app\BaseModel;

class LongbingClientInfo extends BaseModel
{
    //定义表名称
    protected $name = 'longbing_card_client_info';
    
    public function getClientInfo($filter)
    {
        $result = $this->where($filter)->find();
        if(!empty($result)) $result = $result->toArray();
        return $result;
    }
    
    public function listClientData($filter ,$field = null)
    {
        $result = $this->where($filter);
        if(empty($field) && is_array($field)) $result = $result->field($field);
        $result = $result->select();
        if(!empty($result)) $result = $result->toArray();
        return $result;
    }
    
    public function getClientData($filter ,$field = null)
    {
        $result = $this->where($filter);
        if(empty($field) && is_array($field)) $result = $result->field($field);
        $result = $result->find();
        if(!empty($result)) $result = $result->toArray();
        return $result;
    }
    public function updateClientInfo($filter ,$data)
    {
        $data['update_time'] = time();
        $result = $this->where($filter)->update($data);
        return !empty($result);
    }
}