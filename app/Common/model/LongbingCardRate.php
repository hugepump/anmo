<?php
namespace app\Common\model;

use app\BaseModel;

class LongbingCardRate extends BaseModel
{
    //定义表名称
    protected $name = 'longbing_card_rate';
    
    //新建
    public function createRate($data)
    {
        $data['crate_time'] = time();
        $result = $this->save($data);
        return !empty($result);
    }
    
    
    //获取单条数据
    public function getRate($filter)
    {
        $result = $this->where($filter)->find();
        if(!empty($result)) $result = $result->toArray();
        return $result;
    }
    
    //更新
    public function updateRate($filter ,$data)
    {
        $data['update_time'] = time();
        $result = $this->where($filter)->update($data);
        return !empty($result);
    }
    
    //删除
    public function deleRate($filter)
    {
        $result = $this->where($filter)->delete();
        return !empty($result);
    }
}