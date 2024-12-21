<?php

namespace app\card\model;

use app\BaseModel;
use app\dynamic\model\CardStatistics;


class CardMessage extends BaseModel
{
    //定义表名
    protected $name = 'longbing_card_message';


    protected static function init ()
    {
        //TODO:初始化内容
    }
    public function getCount($where){
        return $this->where($where)->count();
    }
//    public function getCountlist($where){
//        $data = $this
//            ->field('target_id as user_id,count(target_id) as number,uniacid')
//            ->whereDay('create_time','yesterday')
//            ->group('target_id')
//            ->where($where)->select()->toArray();
//        if($data){
//            foreach ($data as $key=>$val){
//                $data[$key]['table'] = 'message';
//                $data[$key]['create_time'] = strtotime("-1 day");
//            }
//            $stat = new CardStatistics();
//            $stat->createRows($data);
//        }
//        return $data;
//    }
}