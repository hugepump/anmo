<?php
namespace app\admin\controller;
use app\Rest;
use think\App;
use think\Request;
use app\admin\model\User as UserModel;
use app\admin\model\Role as RoleModel;
class Role extends Rest
{
    public function __construct(App $app) {
        parent::__construct($app);
    }
    //创建权限
    public function createRole() {
        //获去角色信息    
        $role = $this->_input['role'];
        //生成和填充相关数据
        $role['role_id'] = uuid();
        $role['uniacid'] = $this->_uniacid;
        //生成权限数据库操作模型
        $role_model = new RoleModel;
        //创建
        $result = $role_model->createRole($role);
        //返回相关数据
        return $this->seccess($result);
         
    }
    //获取权限列表
    public function listRole() {
        //获取权限查询信息
        $param = $this->_param;
        //获取分页信息
        $page_config = array(
            'page' => 1,
            'page_count' => 20
        );
        if(isset($param['page']) && $param['page'] > 0) $page_config['page'] = $param['page'];
        if(isset($param['page_count']) && $param['page_count'] > 0) $page_config['page_count'] = $param['page_count'];
        //查询过滤
        $filter = $param;
        $filter['uniacid'] = $this->_uniacid;
        //生成权限操作模型
        $role_model = new RoleModel();
        //获取权限列表
        $page_config['totle'] = $role_model->listRoleCount($filter);
        $roles = $role_model->listRole($filter ,$page_config);
        //生成返回数据
        $page_config['total_page'] = (int)($page_config['total'] / $page_config['page_count']);
        if(($page_config['total'] % $page_config['page_count']) > 0) $page_config['total_page'] = $page_config['total_page'] + 1;
        $result = $page_config;
        $result['roles'] = $roles;
        return $this->success($result);
    }
    //获取权限详情
    public function getRole() {
        //获取权限id
        $role_id = $this->_param['role_id'];
        //生成权限操作模型
        $role_model = new RoleModel();
        //获取权限数据
        $role = $role_model->getRole(['role_id' => $role_id ,'uniacid' => $this->_uniacid]);
        return $this->success($role);
    }
    //更改权限信息
    public function updateRole() {
        //获取角色id
        $role_id = $this->_param['role_id'];
        //判断权限是否存在
        $role_model = new RoleModel();
        $role = $role_model->getRole(['role_id' => $role_id ,'uniacid' => $this->_uniacid]);
        if(empty($role)) return $this->error('the role is nit exist ,please check the role id.');
        //获去角色更新信息    
        $role = $this->_input['role'];
        //更新
        $result = $role_model->updateRole(['role_id' => $role_id ,'uniacid' => $this->_uniacid] ,$role);
        //返回相关数据
        return $this->seccess($result);
    }
    //删除权限信息
    public function delRole() {
        //获取角色id
        $role_id = $this->_param['role_id'];
        //判断权限是否存在
        $role_model = new RoleModel();
        $role = $role_model->getRole(['role_id' => $role_id ,'uniacid' => $this->_uniacid]);
        if(empty($role)) return $this->error('the role is nit exist ,please check the role id.');
        //更新
        $result = $role_model->delRole(['role_id' => $role_id ,'uniacid' => $this->_uniacid]);
        if(!empty($result)) {
            $user_model = new UserModel();
            $user_model->update(['role_id' => $role_id ,'uniacid' => $this->_uniacid] ,['role_id' => 0]);
        }
        //返回相关数据
        return $this->seccess($result);
        
    }
    
}
