<?php
namespace app\admin\controller;
use app\Rest;
use think\App;
use think\Request;
use app\admin\model\Admin as UserModel;
use app\admin\model\AdminRole as UserRoleModel;
class User extends Rest
{
	public function __construct(App $app) {
        parent::__construct($app);
    }
	//创建用户
	public function createUser() {
		$user = $this->_input['user'];
        $data = checkAccountIsExist($user['account'] ,$this->_uniacid);
		//判读账号是否存在
        if($data) return $this->error('account is exist ,please check again.');
        
        $user_id = uuid();
        $user['user_id'] = $user_id;
        $user['offset']  = createOffset();
        $user['passwd']  = createPasswd($user['passwd'] ,$user['offset']);
		$user['uniacid'] = $this->_uniacid;
		//判断权限
        if(!isset($user['role_id']) || !ckeckRole($user['role_id'] ,$this->_uniacid)) $user['role_id'] =  getRole()['role_id'];
		if(!empty($this->_user))$user['creator_id'] = $this->_user['user_id'];
		//创建数据
		$user_model = new UserModel();
		$result = $user_model->createUser($user);
//		if(!empty($result)) setAccountToCache($user['account'] ,$this->_uniacid);
    	return $this->success($result);
	}
	
	//获取用户列表
	public function listUser() {
		//获取查询参数
		$param = $this->_param;
		//获取分页数据
		$page_config = array(
			'page' => 1,
			'page_count' => 20
		);
		if(isset($param['page']) && $param['page'] > 0) $page_config['page'] = $param['page'];
		if(isset($param['page_count']) && $param['page_count'] > 0) $page_config['page_count'] = $param['page_count'];
		
		//参数过滤
		$param['uniacid'] = $this->_uniacid;
		$filter = listUserFilter($param);
		//查询数据
		$user_model = new UserModel();
		//获取总数据总条数
		$page_config['total'] = $user_model->listUserCount($filter);
		
		$users = $user_model->listUser($filter ,$page_config);
		//构造返回数据
		$page_config['total_page'] = (int)($page_config['total'] / $page_config['page_count']);
		if(($page_config['total'] % $page_config['page_count']) > 0) $page_config['total_page'] = $page_config['total_page'] + 1;
		$result = $page_config;
		$result['users'] = $users;
		return $this->success($result);
	}
	
	//获取用户详情
	public function getUser() {
		$user_id = $this->_param['user_id'];
		//获取用户详细信息
		$user_model = new UserModel();
		$user = $user_model->getUser(['user_id' => $user_id ,'uniacid' => $this->_uniacid]);
		//移除密码 偏移量
		unset($user['passwd']);
		unset($user['offset']);
		//返回数据
		return $this->success($user);
	}
	
	//修改用户信息
	public function updateUser() {
		//获取用户id
		$user_id    = $this->_param['user_id'];
		//生成用户模型类
		$user_model = new UserModel(); 
		//获取用户数据
		$user       = $user_model->getUser(['user_id' => $user_id ,'uniacid' => $this->_uniacid]);
		if(empty($user)) return $this->error('the user not is exist ,please check user id.');
		//获取修改信息
		$user_data  = getUpdateUserFilter($this->_input['user']);
		//更改密码
		if(isset($user_data['passwd'])){
			//判断偏移量是否存在
			if(!isset($user['offset'])) $user['offset'] = createOffset(); $user_data['offset'] = $user['offset'];
			$user_data['passwd'] = createPasswd($user_data['passwd'] ,$user['offset']);
		}
		//修改数据
		$result = $user_model->updateUser(['user_id' => $user_id ,'uniacid' => $this->_uniacid] ,$user_data);
		//返回数据
		return $this->success($result);
	}
	
	//删除用户
	public function delUser() {
		//获取用户id
		$user_id    = $this->_param['user_id'];
		//生成用户模型类
		$user_model = new UserModel(); 
		//获取用户数据
		$user       = $user_model->getUser(['user_id' => $user_id ,'uniacid' => $this->_uniacid]);
		if(empty($user)) return $this->error('the user not is exist ,please check user id.');
		//删除用户数据
		$result = $user_model->delUser(['user_id' => $user_id ,'uniacid' => $this->_uniacid] ,['deleted' => 0]);
		//删除用户权限信息
		$admin_role_model = new UserRoleModel();
		$admin_role_model->delUserRole(['user_id' => $user_id]);
		//删除数据缓存
	
		//返回数据
		return $this->success($result);
	}
	//给用户增加权限
	public function setUserRole() {
		//获取用户id
		$user_id    = $this->_param['user_id'];
		//获取角色id
		$role_id    = $this->_param['role_id'];
		//生成用户模型类
		$user_model = new UserModel(); 
		//获取用户数据
		$user       = $user_model->getUser(['user_id' => $user_id ,'uniacid' => $this->_uniacid]);
		if(empty($user)) return $this->error('the user is not exist ,please check user id.');
		//获取角色信息
		$role = ckeckRole($role_id);
		if(empty($role)) return $this->error('the role is not exist ,please check role id.');
		//判断用户权限是否已存在
		$exist_role_ids = [];
		foreach($user['role']  as $role){
			$exist_role_ids[] = $role['role_ids'];
		}
		if(in_array($role_id, $exist_role_ids)) return $this->error('the user had the role ,please do not repeat add role to the user.');
		//添加角色
		$user_role_model = UserRoleModel();
		$result = $user_role_model->createUserRole(['user_id' => $user_id ,$role_id => $role_id ,'uniacid' => $this->_uniacid]);
		//返回数据
		return $this->success($result);
	}
	//移除用户权限
	public function removeUserRole() {
		//获取用户id
		$user_id    = $this->_param['user_id'];
		//获取角色id
		$role_id    = $this->_param['role_id'];
		//生成用户模型类
		$user_model = new UserModel(); 
		//获取用户数据
		$user       = $user_model->getUser(['user_id' => $user_id ,'uniacid' => $this->_uniacid]);
		if(empty($user)) return $this->error('the user is not exist ,please check user id.');
		//判断用户权限是否已存在
		$exist_role_ids = [];
		foreach($user['role']  as $role){
			$exist_role_ids[] = $role['role_ids'];
		}
		if(!in_array($role_id, $exist_role_ids)) return $this->error('the user role is not exist ,please check role id.');
		//添加角色
		$user_role_model = UserRoleModel();
		$result = $user_role_model->delUserRole(['user_id' => $user_id ,$role_id => $role_id ,'uniacid' => $this->_uniacid]);
		//返回数据
		return $this->success($result);
	}
}
