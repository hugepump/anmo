<?php
namespace app\agent\controller;

use think\App;
use app\agent\model\AdminModel;
use app\AgentRest;

class UserController extends AgentRest{
    public function __construct ( App $app ){
        parent::__construct( $app );
    }
//  
    public function updateSelfPasswd()
    {
      	if(defined('IS_WEIQIN'))
        {
        	return $this->error(lang('没有权限'));
        }
        //获取数据
        $input = $this->_input;
        if(isset($input['admin'])) $input = $input['admin'];
        //检查用户初始密码是否正确
        $user = $this->_user;
        if(isset($user['admin_id']) && !empty($user['admin_id']))
        {
            $admin_model = new AdminModel();
            $user        = $admin_model->getAdmin(['admin_id' => $user['admin_id']]);
//          var_dump($user);die;
        }else{
            return $this->error(lang('admin is not exist'));
        }
        if(empty($user)) return $this->error(lang('admin is not exist'));
//      var_dump($user);die;
        $check = checkPasswd($input['old_passwd'] ,$user['offset'] ,$user['passwd']);
        if(!$check) return $this->error(lang('old passwd error'));
        //修改密码
        $passwd = createPasswd($input['new_passwd'] ,$user['offset']);
        //保存修改
        $admin_model = new AdminModel();
        $result      = $admin_model->updateAdmin(['admin_id' => $user['admin_id']] ,['passwd' => $passwd]);
        //返回数据
        return $this->success($result);
    }
}
