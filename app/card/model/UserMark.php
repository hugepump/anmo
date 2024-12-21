<?php
namespace app\card\model;

use app\BaseModel;
use app\dynamic\model\CardStatistics;


class UserMark extends BaseModel
{
    //定义表名
    protected $name = 'longbing_card_user_mark';
    public function getCount($where3){
       return $this->where($where3)->count();
    }
    public function getCountlist($where){
        $data = $this
            ->field('staff_id as user_id,count(staff_id) as number,uniacid')
            ->whereDay('create_time','yesterday')
            ->group('staff_id')
            ->where($where)->select()->toArray();
        if($data){
            foreach ($data as $key=>$val){
                $data[$key]['table'] = 'mark';
                $data[$key]['create_time'] = strtotime("-1 day");
            }
            $stat = new CardStatistics();
            $stat->createRows($data);
        }
        return $data;
    }
    //跟进状态
    public function getMarkStatus($where){
         $mark = $this->where($where)->value('mark');
         if($mark ==2){
             $mark ='已成交';
         }elseif ($mark ==1){
             $mark ='跟进中';
         }else{
             $mark ='还未跟进';
         }
        return $mark;
    }
}