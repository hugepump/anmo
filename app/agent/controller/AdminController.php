<?php
namespace app\agent\controller;

use think\App;
use app\AdminRest;
use app\agent\model\AdminModel;
use app\agent\model\AppAdminModel;
use app\AgentRest;
use app\agent\validate\AgentAdminValidate;

class AdminController extends AgentRest
{
    public function __construct ( App $app ){
        parent::__construct( $app );
        if ($this->_user['role_name'] != 'admin') {
            echo json_encode(['code' => 401, 'error' => lang('Permission denied')]);
            exit;
        }
    }

    /*
     *获取用户列表 
     */
    public function list()
    {
        $param = $this->_param;


        $dis = [];


        if(!empty($param['name'])){

            $dis[] = ['a.account','like',"%".$param['name'].'%'];

            $dis[] = ['d.mini_app_name','like',"%".$param['name'].'%'];
        }

        $list = AdminModel::alias('a')
            ->field(['a.admin_id', 'a.level','a.account', 'a.role_id', 'a.create_time', 'r.description','d.mini_app_name as mini_name','c.modular_id'])
            ->leftJoin('longbing_role r', 'a.role_id = r.role_id')
            ->leftJoin('longbing_app_admin c', 'a.admin_id = c.admin_id')
            ->leftJoin('longbing_card_config d', 'c.modular_id = d.uniacid')
            ->where([['a.status', '=', 1], ['a.uniacid', '=', $this->_uniacid]])
            ->where(function ($query) use ($dis){
                $query->whereOr($dis);
            })
            ->order('a.create_time desc')
            ->paginate(['list_rows' => $param['page_count'] ? $param['page_count'] : 10, 'page' => $param['page'] ? $param['page'] : 1])
            ->toArray();


//        $admin_ids = array_column($list['data'], 'admin_id');
//        $app_admins = [];
//        $app_admins_tmp = AppAdminModel::alias('aa')
//            ->field(['aa.admin_id', 'aa.modular_id', 'c.mini_app_name' => 'mini_name'])
//            ->leftJoin('longbing_card_config c', 'aa.modular_id = c.uniacid')
//            ->where([['aa.admin_id', 'IN', $admin_ids]])->select();
//
//        foreach ($app_admins_tmp as $k => $v) {
//            $app_admins[$v['admin_id']] = $v;
//        }

        foreach ($list['data'] as $k => $v) {

            $list['data'][$k]['is_bind'] = !empty($v['mini_name']);


            if($v['description']!='超级管理员'){

                $list['data'][$k]['description'] = $v['level']==0?'管理员':'员工';
            }

//            $list['data'][$k]['mini_name'] = $app_admins[$v['admin_id']]['mini_name'] ?? null;
//            $list['data'][$k]['modular_id'] = $app_admins[$v['admin_id']]['modular_id'] ?? null;
        }

        $list['zhihuituike'] = longbingIsZhihuituike();

        return $this->success($list);
    }

    //添加用户
    public function addSubAdmin()
    {
        $input = $this->_input;

        $validate = new AgentAdminValidate();
        if (false == $validate->scene('addSubAdmin')->check($input)) {
            return $this->error($validate->getError());
        };

        /**
         * @var AdminModel $subAdmin
         */
        $subAdmin = AdminModel::where([['account', '=', $input['account']], ['status', '=', 1]])->findOrEmpty();
        if (!$subAdmin->isEmpty()) {
            return $this->error('该账号已存在');
        }

        $offset = createOffset();
        $new = [
            'admin_id' => uuid(),
            'account' => $input['account'],
            'uniacid' => $this->_uniacid,
            'offset' => $offset,
            'passwd' => createPasswd($input['passwd'], $offset),
            'role_id' => 'e7d81116997011e99b985595a87cbdcb',
            'creator_id' => $this->_user['admin_id'],
            'status' => 1,

            'level'  => $input['level']
        ];
        $rst = $subAdmin->save($new);
        if (!$rst) {
            return $this->error('fail');
        }
        return $this->success($rst);
    }

    /*
     * 管理员修改用户信息
     */ 
    public function updateSubAdmin()
    {
        //获取数据
        $input = $this->_input;
        //字段校验
        $validate = new AgentAdminValidate();
        if (false == $validate->scene('addSubAdmin')->check($input)) {
            return $this->error($validate->getError());
        };
        
        /**
         * @var AdminModel $subAdmin
         */
        $subAdmin = AdminModel::where([['admin_id', '=', $input['admin_id']], ['uniacid', '=', $this->_uniacid]])->find();
        if (empty($subAdmin)) {
            return $this->error('用户不存在');
        }
        //强制修改密码
        $input['passwd'] = createPasswd($input['passwd'] ,$subAdmin['offset']);
        $result = $subAdmin->save([
            'passwd' => $input['passwd'],
            'level' => $input['level'],
        ]);
        
        return $this->success($result);
    }


    public function delSubAdmin()
    {
        $input = $this->_input;

        $validate = new AgentAdminValidate();
        if (false == $validate->scene('delSubAdmin')->check($input)) {
            return $this->error($validate->getError());
        };

        /**
         * @var AdminModel $subAdmin
         */
        $subAdmin = AdminModel::where([['admin_id', '=', $input['admin_id']], ['uniacid', '=', $this->_uniacid]])->findOrEmpty();

        if ($subAdmin->isEmpty()) {
            return $this->error('用户不存在');
        }

        if ($subAdmin->admin_id == $this->_user['admin_id']) {
            return $this->error("不可以删除自己");
        }

        $rst = $subAdmin->save([
            'status' => 0,
        ]);

        return $this->success($rst);
    }


    public function bindApp()
    {
        $input = $this->_input;
        $admin_id = $input['admin_id'];
        $modular_id = $input['modular_id'];

        if ($admin_id == $this->_user['admin_id']) {
            return $this->error("超级管理员不能绑定小程序， 您可以创建子账号绑定");
        }
        $admin_bind_count = AppAdminModel::where([['admin_id', '=', $admin_id]])->count();
        if ($admin_bind_count > 0) {
            return $this->error('该用户已经绑定了一个小程序');
        }

        $appAdmin = new AppAdminModel();
        $rst = $appAdmin->save([
            'id' => md5($modular_id . $admin_id),
            'modular_id' => $modular_id,
            'uniacid' => $this->_uniacid,
            'admin_id' => $admin_id
        ]);

        return $this->success($rst);

    }
}