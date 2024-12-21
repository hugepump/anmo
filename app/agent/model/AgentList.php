<?php


namespace app\agent\model;


use app\BaseModel;
use think\facade\Db;

class AgentList extends BaseModel
{
    protected  $name = 'longbing_card_agent_list';



    protected $append = [

        'status_text',

        'have_count',

        'level_title'
    ];


    /**
     * @param $value
     * @param $data
     * @功能说明:代理商等级名字
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-05-26 14:07
     */
    public function getLevelTitleAttr($value,$data){

        if(!empty($data['level'])){

            $level_model = new AgentLevel();

            $dis[] = ['id','=',$data['level']];

            $dis[] = ['status','=',1];

            return $level_model->where($dis)->value('title');

        }


    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-05-12 13:54
     * @功能说明:获取状态
     */
    public function getStatusTextAttr($value,$data){

        if(!empty($data['over_time'])){

            switch ($data['over_time']){

                case $data['over_time']>time():

                    return '使用中';

                    break;
                case $data['over_time']<time():

                    return '已过期';

                    break;
            }
        }
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-05-12 14:04
     * @功能说明:获取已经安装的数量
     */
    public function getHaveCountAttr($value,$data){

        $num = Db::name('longbing_cardauth2_config')->where(['agent_id'=>$data['id']])->count();

        return $num;

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-05-12 10:54
     * @功能说明:代理商列表
     */
   public function agentList($dis,$page=10){

       $list = $this->where($dis)->order('id desc')->paginate($page)->toArray();

       return $list;

   }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-05-12 10:55
     * @功能说明:添加代理商
     */
   public function agentAdd($data){
       //创建时间
       $data['create_time'] = time();
       //更新时间
       $data['update_time'] = time();

       $res = $this->insert($data);

       return $res;

   }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-05-12 10:57
     * @功能说明:代理商编辑
     */
    public function agentUpdate($dis,$data){

        $data['update_time'] = time();

        $res = $this->where($dis)->update($data);

        return $res;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-05-12 13:34
     * @功能说明:代理商详情
     */
    public function agentInfo($dis){

        $data = $this->where($dis)->find();

        return !empty($data)?$data->toArray():[];


    }

}