<?php
namespace app\abnormalorder\controller;
use app\abnormalorder\model\OrderProcess;
use app\abnormalorder\model\OrderRole;
use app\abnormalorder\model\OrderWander;
use app\AdminRest;


use app\massage\model\Admin;
use app\node\model\RoleList;
use think\App;


use think\facade\Db;


class AdminProcess extends AdminRest
{

    protected $model;

    public function __construct(App $app) {

        parent::__construct($app);

        $this->model = new OrderProcess();

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-12-11 16:43
     * @功能说明:
     */
    public function getProcessStartInfo(){

        $role_model = new RoleList();

        $admin_model= new Admin();

        $admin_id = $role_model->getShopOrderRole($this->_uniacid);

        $data = $admin_model->where(['status'=>1,'uniacid'=>$this->_uniacid])->where('id','in',$admin_id)->field('id,agent_name')->select()->toArray();

        return $this->success($data);
    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 13:28
     * @功能说明:流程列表
     */
    public function processList(){

        $input = $this->_param;

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['status','>',-1];

        $dis[] = ['type','=',$input['type']];

        $data = $this->model->where($dis)->order('top,id desc')->select()->toArray();

        $have = $this->model->where(['type'=>$input['type'],'uniacid'=>$this->_uniacid,'status'=>1])->find();

        $arr['can_stop'] = !empty($have)?1:0;

        $arr['list'] = $data;

        return $this->success($arr);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-12-13 14:20
     * @功能说明:停用流程
     */
    public function stopProcess(){

        $input = $this->_input;

        $res = $this->model->dataUpdate(['uniacid'=>$this->_uniacid,'type'=>$input['type'],'status'=>1],['status'=>0]);

        return $this->success($res);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-11-27 15:26
     * @功能说明:添加流程
     */
    public function processAdd(){

        $input = $this->_input;

        $role_model   = new OrderRole();

        $wander_model = new OrderWander();

        $this->model->dataUpdate(['uniacid'=>$this->_uniacid,'type'=>$input['type']],['status'=>-1]);

        Db::startTrans();

        foreach ($input['data'] as $key => $value){
            //添加流程
            $insert = [

                'uniacid' => $this->_uniacid,

                'top'     => $value['top'],

                'deduct_status' => $value['deduct_status'],

                'type'  => $input['type'],

                'sub_type' => $value['sub_type'],

                'status'=> 1
            ];

            if(!empty($value['id'])){

                $this->model->dataUpdate(['id'=>$value['id']],$insert);

                $id = $value['id'];
            }else{

                $this->model->dataAdd($insert);

                $id = $this->model->getLastInsID();
            }
            //关联人员
            $role_model->where(['process_id'=>$id])->delete();

            $role_insert = [];

            foreach ($value['role'] as $ks=>$vs){

                $role_insert[$ks] = [

                    'uniacid'      => $this->_uniacid,

                    'process_id'   => $id,

                    'type'         => $vs['type'],

                    'user_id'      => $vs['user_id'],

                    'process_type' => $input['type'],
                ];
            }

            $role_model->saveAll($role_insert);
            //流转设置
            $wander_model->where(['process_id'=>$id])->delete();

            $wander_model->wanderAdd($value['wander'],$this->_uniacid,$id);
        }

        Db::commit();

        return $this->success(true);
    }













}
