<?php

namespace app\card\model;

use app\BaseModel;
use app\dynamic\model\CardStatistics;
use think\Model;


class CardForward extends BaseModel
{
    //定义表名
    protected $name = 'longbing_card_forward';


    protected static function init ()
    {
        //TODO:初始化内容
    }

    public function getCount($where){
        $data = $this
            ->field('staff_id as user_id,count(staff_id) as number,uniacid')
            ->whereDay('create_time','yesterday')
            ->group('staff_id')
            ->where($where)->select()->toArray();
        if($data){
            foreach ($data as $key=>$val){
                $data[$key]['table'] = 'forward';
                $data[$key]['create_time'] = strtotime("-1 day");
            }
            $stat = new CardStatistics();
            $stat->createRows($data);
        }
        return $data;
    }
    public function getYesterday($where){
       return $this->where($where)->whereDay('create_time','yesterday')->count();
    }
    public function forwardCount($where4){
        return $this->where($where4)->count();
    }
}