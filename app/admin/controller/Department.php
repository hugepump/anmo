<?php
namespace app\admin\controller;
use app\Rest;
use think\App;
use think\Request;
use app\admin\model\User as UserModel;
use app\admin\model\Department as DepartmentModel;
class Department extends Rest
{
    public function __construct(App $app) {
        parent::__construct($app);
    }
    
    //新建Departmet
    public function createDepartmet() {
        //获取创建部门数据
        $department = $this->_input['department'];
        //生成相关数据
        $department['department_id'] = uuid();
        //获取创建者user_id
        if(isset($this->_user)) $department['creator_id'] = $this->_user['user_id'];
        //修改数据
        $result = $deparmet_model->updateDepartment(['deparement_id' => $department_id ,'uniacid' => $this->_uniacid] ,$data);
        //返回数据
        return $this->success($result);
    }
    
    //获取Departmet列表
    public function listDepartmet() {
        //筛选部门信息
        $param = $this->_param;
        //获取分页信息
        $page_config = array(
            'page' => 1,
            'page_count' => 20
        );
        //设置页码
        if(isset($param['page']) && $param['page'] > 0) $page_config['page'] = $param['page'];
        //设置每页的数据
        if(isset($param['page_count']) && $param['page_count'] > 0) $page_config['page_count'] = $param['page_count'];
        //查询过滤
        $filter = $param;
        //默认uniacid
        $filter['uniacid'] = $this->_uniacid;
        //生成部门模型
        $department_model = new DepartmentModel();
        //获取部门总数
        $page_config['total'] = $department_model->listDepartmentCount($filter);
        $departmets =  $department_model->listDepartment($filter);
        //生生成返回数据
        $page_config['total_page'] = (int)($page_config['total'] / $page_config['page_count']);
        if(($page_config['total'] % $page_config['page_count']) > 0) $page_config['total_page'] = $page_config['total_page'] + 1;
        //设置返回参数
        $result = $page_config;
        //返回数据
        $result['departments'] = $departmets;
        return $this->success($result);
        
        
        
    }    
    
    //获取Departmet详情
    public function getDepartmet() {
        //获取部门id
        $department_id   = $this->_param['department_id'];
        //获取部门详情
        $deparmet_model  = new DepartmentModel();
        //获取部门信息
        $department      = $department_model->getDepartment(['deparement_id' => $department_id ,'uniacid' => $this->_uniacid]);
        //判断部门信息是否存在
        if(!empty($department)){
            //获取部门下的子部门
            $deparements = $department_model->listDepartmentAll(['parent_id' => $department_id ,'uniacid' => $this->_uniacid]);
            if(!empty($departments)) $department['departments'] = $deparements;
            //获取部门下的员工
            $user_model  = new UserModel(); 
            $users       = $user_model->listUserAll(['department_id' => $department_id ,'uniacid' => $this->_uniacid]);
            if(!empty($users)) $department['users'] = $users;
        }
        //返回数据
        return $this->success($department);
    }
    
    //更新Departmet
    public function updateDepartmet() {
        //获取部门id
        $department_id = $this->_param['department_id'];
        //获取修改数据
        $data          = $this->_input['department'];
        //获取部门详情
        $deparmet_model = new DepartmentModel();
        $department     = $department_model->getDepartment(['deparement_id' => $department_id ,'uniacid' => $this->_uniacid]);
        if(empty($department)) return $this->error('the department not exist ,please check department id .');
        if(empty($data)) return $this->error('the department change data is note exist ,please check department data.');
        //修改数据
        $result = $deparmet_model->updateDepartment(['deparement_id' => $department_id ,'uniacid' => $this->_uniacid] ,$data);
        return $this->success($result);
    }
    
    //删除Departmet
    public function delDepartmet() {
        //获取部门id
        $department_id = $this->_param['department_id'];
        //生成部门模型
        $deparmet_model = new DepartmentModel();
        //获取部门信息
        $department     = $department_model->getDepartment(['deparement_id' => $department_id ,'uniacid' => $this->_uniacid]);
        if(empty($department)) return $this->error('the department not exist ,please check department id .');
        //删除数据
        $result = $deparmet_model->delDepartment(['deparement_id' => $department_id ,'uniacid' => $this->_uniacid] ,$data);
        if(!empty($result)) {
            $user_model = new UserModel();
            //设置部门为空
            $user_model->updateUser(['uniacid' => $this->_uniacid ,'department_id' => $department_id] ,['department_id' => 0]);
        }
        return $this->success($result);
    }
}
