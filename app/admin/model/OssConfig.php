<?php
namespace app\admin\model;

use app\BaseModel;
use think\facade\Db;
class OssConfig extends BaseModel
{
    //定义表名
    protected $name = 'shequshop_school_oos_config';
    
    //创建
    public function createConfig($data)
    {
        $data['create_time'] = time();
        $data['open_oss']    = 0;
        $result = $this->save($data);
        if(empty($result))    return false;
        return true;
    }
    //修改
    public function updateConfig($filter ,$data)
    {
        $filter['deleted'] = 0;
        $data['update_time'] = time();
        $result = $this->where($filter)->update($data);
        if(empty($result)) return false;
        return true;    
    }
    
    //获取列表
    public function listConfig($filter)
    {
        $filter['deleted'] = 0;
        $result = $this->where($filter)->select();
        if(!empty($result)) $result->toArray();
        return $result;
    }
    //获取总数
    public function listConfigCount($filter)
    {
        $filter['deleted'] = 0;    
        $result = $this->where($filter)->count();
        return $result;
    }
    
    //获取单条数据
    public function getConfig($filter)
    {
        $filter['deleted'] = 0;
        $result = $this->where($filter)->find();
        if(!empty($result)) $result = $result->toArray();
        return $result;
    }
    
    //删除
    public function delConfig($filter)
    {
        $filter['deleted'] = 0;
        return $this->updateConfig($filter ,['deleted' => 0 ,'delete_time' => time()]);
    }
    //删除(真删除)
    public function destoryConfig($filter)
    {
        return $this->where($filter)->delete();
    }
    
}