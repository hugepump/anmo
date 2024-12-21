<?php


namespace app\agent\model;


use app\BaseModel;

class AdminModel extends BaseModel
{
    protected  $name = 'longbing_admin';

    
    public function appAdmin()
    {
        return $this->hasOne(AppAdminModel::class, 'admin_id', 'admin_id');
    }


    public function role()
    {
        return $this->hasOne(AdminRoleModel::class, 'role_id', 'role_id');
    }
    
    /**
     * 
     */
    public function createAdmin($data)
    {
        $data['create_time'] = time();
        $result = $this->save($data);
        return !empty($result);
    }
    
    //修改
    public function updateAdmin($filter ,$data)
    {
        $filter['status'] = 1;
        $data['update_time'] = time();
        $result = $this->where($filter)->update($data);
//      var_dump($result);die;
        return !empty($result);
    }
    /**
     * 获取用户
     */
    public function getAdmin($filter)
    {
        $filter['deleted'] = 0;
        $filter['status']  = 1;
        $result = $this->where($filter)->find();
        if(!empty($result)) $result = $result->toArray();
        return $result;
    }

}