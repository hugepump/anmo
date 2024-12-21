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
class AgentList extends AgentRest
{


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-05-12 13:20
     * @功能说明:代理商列表
     */
    public function agentList(){


        $input = $this->_input;

        $dis[] = ['uniacid','in',$this->_uniacid_arr];
        //模型
        $agent_model = new \app\agent\model\AgentList();
        //等级搜索
        if(!empty($input['level'])){

            $dis[] = ['level','=',$input['level']];
        }
        //用户名搜索
        if(!empty($input['user_name'])){

            $dis[] = ['user_name','like','%'.$input['user_name'].'%'];
        }
        //状态搜索
        if(!empty($input['status'])){

            $icon  = $input['status']==1?'>':'<';

            $dis[] =['over_time',$icon,time()];
        }
        //省搜索
        if(!empty($input['province_code'])){

            $dis[] = ['province_code','=',$input['province_code']];

        }
        //市搜索
        if(!empty($input['city_code'])){

            $dis[] = ['city_code','=',$input['city_code']];

        }
        //查询
        $data = $agent_model->agentList($dis,$input['page_count']);

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

        $agent_model = new \app\agent\model\AgentList();

        $res = $agent_model->agentAdd($input);

        return $this->success($res);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-05-12 13:31
     * @功能说明:编辑代理商
     */
    public function agentUpdate(){

        $input = $this->_input;

        $agent_model = new \app\agent\model\AgentList();

        $data  = $agent_model->agentUpdate(['id'=>$input['id']],$input);

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-05-12 13:33
     * @功能说明:代理商详情
     */
    public function agentInfo(){

        $input = $this->_input;

        $agent_model = new \app\agent\model\AgentList();

        $data  = $agent_model->agentInfo(['id'=>$input['id']]);

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-05-12 14:12
     * @功能说明:
     */
    public function agentSelect(){

        $dis[] = ['uniacid','in',$this->_uniacid_arr];

        $agent_model = new \app\agent\model\AgentList();

        $data = $agent_model->where($dis)->order('id desc')->field('id,user_name')->select()->toArray();

        return $this->success($data);
    }


}