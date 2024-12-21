<?php
namespace app\agent\controller;


use think\App;
use app\AgentRest;


/**
 * @author shuixian
 * @DataTime: 2020/1/3 9:44
 * Class AdminAuthAppController
 * @package app\agent\controller
 */
class AgentLevel extends AgentRest
{


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-05-12 13:20
     * @功能说明:代理商等级列表
     */
    public function levelList(){

        $input = $this->_input;

        $dis[] = ['uniacid','in',$this->_uniacid_arr];

        $dis[] = ['status','=',1];
        //模型
        $agent_model = new \app\agent\model\AgentLevel();
        //查询
        $data = $agent_model->levelList($dis,$input['page_count']);

        return $this->success($data);

    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-05-12 13:21
     * @功能说明:添加代理商
     */
    public function agentAdd(){

        $input = $this->_input;

        $input['uniacid'] = $this->_uniacid;

        if(empty(trim($input['title']))){

            $this->errorMsg('标题不能为空');
        }

        $agent_model = new \app\agent\model\AgentLevel();

        $res = $agent_model->levelAdd($input);

        return $this->success($res);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-05-12 13:31
     * @功能说明:编辑代理商
     */
    public function agentUpdate(){

        $input = $this->_input;

        $agent_model = new \app\agent\model\AgentLevel();

        $data  = $agent_model->levelUpdate(['id'=>$input['id']],$input);

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-05-12 13:33
     * @功能说明:代理商详情
     */
    public function agentInfo(){

        $input = $this->_input;

        $agent_model = new \app\agent\model\AgentLevel();

        $data  = $agent_model->levelInfo(['id'=>$input['id'],'status'=>1]);

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-05-12 14:12
     * @功能说明:选择框
     */
    public function levelSelect(){

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['uniacid','in',$this->_uniacid_arr];

        $agent_model = new \app\agent\model\AgentLevel();

        $data = $agent_model->levelSelect($dis);

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-05-26 13:46
     * @功能说明:删除
     */
    public function levelDel(){

        $input = $this->_input;

        $level_model = new \app\agent\model\AgentLevel();

        $agent_model = new \app\agent\model\AgentList();

        $dis[] = ['level','=',$input['id']];

        $dis[] = ['status','>',-1];

        $info = $agent_model->agentInfo($dis);

//        dump($info);exit;

        if(!empty($info)){

            $this->errorMsg('该等级正在被使用,使用代理商：'.$info['user_name']);
        }

        $data  = $level_model->levelUpdate(['id'=>$input['id']],['status'=>-1]);

        return $this->success($data);
    }


}