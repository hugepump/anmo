<?php
namespace app\Common\model;

use app\BaseModel;

class LongbingCardCount extends BaseModel
{
	//定义表名称
	protected $name = 'longbing_card_count';
	
	public function getCount($filter)
	{
		$result = $this->where($filter)->find();
		if(!empty($result)) $result = $result->toArray();
		return $result;
	}
	
	public function getCountNum($filter)
	{
		$result = $this->where($filter)->count();
		return $result;
	}
    //查询当天的数据
    public function getTodaylist($where){
        return $this->where($where)->whereDay('create_time')->count();
    }
    public function getCreateTimeAttr($value){
        return date('Y-m-d H:i:s',$value['create_time']);
    }
    public function getYesterdaylist($where){
        $data = $this->alias('a')
            ->join( 'longbing_card_collection b', 'a.to_uid = b.to_uid && a.uniacid = b.uniacid','left')
            ->field('a.to_uid,count(a.to_uid) as number,a.create_time')
            ->where($where)
            ->group('a.to_uid')
            ->whereDay('a.create_time')
            ->select()->toArray();print_r($this->getLastSql());exit;
        return $data;
    }

}