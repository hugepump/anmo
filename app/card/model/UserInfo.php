<?php
namespace app\card\model;

use app\BaseModel;
use think\facade\Db;


class UserInfo extends BaseModel
{
    //定义表名
    protected $name = 'longbing_card_user_info';
    
    public function createUser($data)
    {
        $data['create_time'] = time();
        $result = $this->save($data);
        return !empty($result);
    }
    
    public function updateUser($filter ,$data)
    {
        $data['update_time'] = time();
        $result = $this->where($filter)->update($data);
        return !empty($result);
    }
    
    public function cardList ()
    {
        $data = $this->where(['fans_id' => 1])->find();
        return $data;
    }
    
    public function getUserPhone($user_id ,$uniacid = null)
    {
        $filter = ['fans_id' => $user_id];
        if(!empty($uniacid)) $filter['uniacid'] = $uniacid;
        $result = $this->where($filter)->field('phone,wechat')->find();
        if(!empty($result)) $result = $result->toArray();
        return $result;
    }
    
    public function getStaffMaxAutoCount($filter)
    {
        $filter['is_staff']   = 1;
        $filter['is_default'] = 1;
        $count = $this->where($filter)->min('auto_count');
        return $count;
    }
}