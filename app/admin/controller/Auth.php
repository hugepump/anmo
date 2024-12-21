<?php
namespace app\admin\controller;
use app\AdminRest;
use think\App;
use think\Request;
use app\admin\model\Admin as AdminModel;
use app\admin\model\Role as RoleModel;
class Auth extends AdminRest
{
	public function __construct(App $app) {
        parent::__construct($app);
    }
	//登陆
	public function auth() {
		//获取参数
		$filter = $this->_input['user'];
//		//检查用户名是否存在
//		if(empty(checkAccountIsExist($filter['account'] ,$this->_uniacid))) return $this->error('account is not exist ,please check user account.');
		
		//获取用户信息
		$admin_model = new AdminModel();
		$admin = $admin_model->getAdmin(['account' => $filter['account'] ,'uniacid' => $this->_uniacid]);
		//判断用户是否存在
		if(empty($admin)) return $this->error('account is not exist ,please check user account.');
		//判断密码是否正确
		
		if(!checkPasswd($filter['passwd'] ,$admin['offset'],$admin['passwd'])) return $this->error('passwd is error ,please check user passwd.');
		//返回数据
		unset($admin['passwd']);unset($admin['offset']);
		
		$result['user'] = $admin;
		$result['token'] = createToken();
		if(empty($result['token'])) return $this->error('System is busy,please try again later.');
		//生成加密key（暂不使用）
//		$keys = get2keys();
//		if(!empty($keys)){
//			$result['keys'] = $keys['api_key'];
//			$user['keys']   = $keys['sever_key'];
//		}
		//添加缓存数据
		setUserForToken($result['token'] ,$admin);
		
		return $this->success($result);
	}
	
	//注销
	public function unAuth() {
		//判断用户是否登录
		if(empty($this->_user)) return $this->error('The user is not logged in.');
		//删除缓存
		delUserForToken($this->_token);
		//返回数据
		return $this->success(true);
	}
	
}
