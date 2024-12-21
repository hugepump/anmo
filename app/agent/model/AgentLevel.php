<?php


namespace app\agent\model;


use app\BaseModel;
use think\facade\Db;

class AgentLevel extends BaseModel
{
    protected  $name = 'longbing_card_agent_level';




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-05-12 10:54
     * @功能说明:代理等级列表
     */
   public function levelList($dis,$page=10){

       $list = $this->where($dis)->order('top desc,id desc')->paginate($page)->toArray();

       return $list;

   }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-05-12 10:55
     * @功能说明:添加代理商等级
     */
   public function levelAdd($data){
       //创建时间
       $data['create_time'] = time();
       //更新时间
       $data['update_time'] = time();
       //状态
       $data['status']      = 1;

       $res = $this->insert($data);

       return $res;

   }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-05-12 10:57
     * @功能说明:代理商等级编辑
     */
    public function levelUpdate($dis,$data){

        $data['update_time'] = time();

        $res = $this->where($dis)->update($data);

        return $res;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-05-12 13:34
     * @功能说明:代理商等级详情
     */
    public function levelInfo($dis){

        $data = $this->where($dis)->find();

        return !empty($data)?$data->toArray():[];

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-05-26 13:28
     * @功能说明:代理商选择框
     */
    public function levelSelect($dis){

        $data = $this->where($dis)->order('top desc,id desc')->select();

        return !empty($data)?$data->toArray():[];

    }

}