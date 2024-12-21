<?php
namespace app\Common\model;

use app\BaseModel;

class LongbingUserInfo extends BaseModel
{
    //定义表名称
    protected $name = 'longbing_card_user_info';
    
    //获取用户信息
    public function getUser($filter)
    {
        $result = $this->where($filter)->find();
        if(!empty($result)) $result = $result->toArray();
        return $result;
    }
    
    //获取名片信息
    public function getStaff($fans_id ,$uniacid)
    {
        $result = $this->alias('card')
                       ->field( [ 'card.*', 'job.name as job_name' ] )
                       ->join( 'longbing_card_job job', 'card.job_id = job.id', 'LEFT' )
                       ->where( [ [ 'card.fans_id', '=', $fans_id ], [ 'card.uniacid', '=', $uniacid ] ]
                        )
                        ->find();
        if(!empty($result)) $result = $result->toArray();
        return $result;
    }
    //修改
    public function updateUser($filter ,$data)
    {
        $data['update_time'] = time();
        $result = $this->where($filter)->update($data);
        return !empty($result);
    }
    //统计
    public function getUserCount($filter = [])
    {
        $count = $this->where($filter)->count();
        return $count;
    }
    //按顺序获取一个员工
    public function getOneStaff($uniacid = 7777)
    {
        $where[] = [ 'uniacid' ,'=' ,$uniacid ];
        $where[] = [ 'is_staff' ,'=' , 1 ];
        $where[] = [ 'is_default' ,'=' ,1 ];
        $where[] = [ 'fans_id' ,'<>' ,0 ];
        $result = $this->where($where)
                       ->order('auto_count asc')
                       ->field('id,fans_id as staff_id')
                       ->find();
        if(!empty($result))
        {
            $result = $result->toArray();
            $this->where(['id' => $result['id']])->inc( 'auto_count' );
        }
        return $result;
    }
    //统计
    public function getCount($filter)
    {
        $result = $this->where($filter)->count();
        return $result;
    }
}