<?php
namespace app\node\controller;
use app\AdminRest;
use app\massage\model\ActionLog;
use app\massage\model\Admin;
use app\massage\model\AdminRole;
use app\node\model\RoleAdmin;
use app\node\model\RoleList;
use app\node\model\RoleNode;
use LongbingUpgrade;
use think\App;
use think\Env;


class AdminUser extends AdminRest
{

    public function __construct(App $app) {

        parent::__construct($app);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-01-04 14:02
     * @功能说明:角色列表
     */
    public function roleList(){

        $input = $this->_param;

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['status','>',-1];

        if(!empty($input['title'])){

            $dis[] = ['title','like','%'.$input['title'].'%'];

        }

        if($this->_user['is_admin']==0){

            $dis[] = ['admin_id','=',$this->_user['id']];

        }elseif ($this->_user['is_admin']==1){

            $dis[] = ['admin_id','=',0];
        }else{
            //其他人的权限都不能开
            $dis[] = ['admin_id','=',-1];
        }

        $role_model = new RoleList();

        $data = $role_model->dataList($dis,$input['limit']);

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-01-04 14:02
     * @功能说明:角色列表
     */
    public function roleSelect(){

        $input = $this->_param;

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['status','=',1];

        if(!empty($input['title'])){

            $dis[] = ['title','like','%'.$input['title'].'%'];

        }
        if($this->_user['is_admin']==0){

            $dis[] = ['admin_id','=',$this->_user['id']];

        }elseif ($this->_user['is_admin']==1){

            $dis[] = ['admin_id','=',0];
        }else{
            //其他人的权限都不能开
            $dis[] = ['admin_id','=',-1];
        }

        $role_model = new RoleList();

        $data = $role_model->where($dis)->select()->toArray();

        return $this->success($data);


    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-01-04 13:56
     * @功能说明:添加角色
     */
    public function roleAdd(){

        $input = $this->_input;

        $input['uniacid'] = $this->_uniacid;

        $role_model = new RoleList();

        if($this->_user['is_admin']==0){

            $input['admin_id'] = $this->_user['id'];
        }

        $data = $role_model->dataAdd($input);

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-01-04 14:17
     * @功能说明:编辑角色
     */
    public function roleUpdate(){

        $input = $this->_input;

        $dis = [

            'id' => $input['id']

        ];

        $input['uniacid'] = $this->_uniacid;

        $role_model = new RoleList();
        //删除
        if(isset($input['status'])&&$input['status']==-1){

            $adminRole_mdoel = new RoleAdmin();

            $find = $adminRole_mdoel->alias('a')
                ->join('shequshop_school_admin b','a.admin_id = b.id')
                ->where(['a.role_id'=>$input['id']])
                ->where('b.status','>',-1)
                ->find();

            if(!empty($find)){

                $this->errorMsg('该角色正在被使用');
            }

        }

        $data = $role_model->dataUpdate($dis,$input);

        return $this->success($data);


    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-01-04 14:17
     * @功能说明:角色详情
     */
    public function roleInfo(){

        $input = $this->_param;

        $dis = [

            'id' => $input['id']

        ];

        $role_model = new RoleList();

        $data = $role_model->dataInfo($dis);

        $node_model = new RoleNode();

        $node = $node_model->where(['role_id'=>$input['id']])->select()->toArray();

        if(!empty($node)){

            foreach ($node as $k=>$v){

                $node[$k]['auth'] = !empty($v['auth'])?explode(',',$v['auth']):[];

            }

        }

        $data['node'] = $node;

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-01-04 14:18
     * @功能说明:给账号分配角色（多选）
     */
    public function adminRoleAdd(){

        $input = $this->_input;

        $adminRole_mdoel = new RoleAdmin();

        $adminRole_mdoel->where(['admin_id'=>$input['admin_id']])->delete();

        if(!empty($input['role'])){

            foreach ($input['role'] as $key => $value){

                $insert[$key] = [

                    'uniacid' => $this->_uniacid,

                    'admin_id'=> $input['admin_id'],

                    'role_id' => $value

                ];

            }

            $adminRole_mdoel->saveAll($insert);
        }

        return $this->success(true);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-01-04 14:24
     * @功能说明:账号所匹配的角色详情
     */
    public function adminInfo(){

        $input = $this->_param;

        $adminRole_mdoel = new RoleAdmin();

        $admin_model = new \app\massage\model\Admin();

        $dis= [

            'id' => $input['id']
        ];

        $data = $admin_model->dataInfo($dis);

        $dis = [

            'a.uniacid' => $this->_uniacid,

            'b.status'  => 1,

            'a.admin_id'=> $input['id']
        ];

        $data['role'] = $adminRole_mdoel->alias('a')
                ->join('massage_role_list b','a.role_id = b.id')
                ->where($dis)
                ->field('b.*,a.role_id')
                ->group('b.id')
                ->select()
                ->toArray();

        return $this->success($data);

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-01-04 14:24
     * @功能说明:账号所匹配的角色的节点详情
     */
    public function adminNodeInfo(){

        $input = $this->_param;

        $adminRole_mdoel = new RoleAdmin();

        $data['is_admin'] = isset($this->_user['is_admin'])?$this->_user['is_admin']:1;

        $dis = [

            'a.uniacid' => $this->_uniacid,

            'b.status'  => 1,

            'a.admin_id'=> $this->_user['id']
        ];

        $data['node'] = $adminRole_mdoel->alias('a')
            ->join('massage_role_list b','a.role_id = b.id')
            ->join('massage_role_node c','c.role_id = b.id')
            ->where($dis)
            ->field('c.*')
            ->group('c.id')
            ->select()
            ->toArray();

        if(!empty($data['node'])){

            foreach ($data['node'] as $k=>$v){

                $data['node'][$k]['auth'] = !empty($v['auth'])?explode(',',$v['auth']):[];
            }
        }

        $config = getConfigSettingArr($this->_uniacid,['life_text','material_text','attendant_name','channel_menu_name','agent_default_name','reseller_menu_name','broker_menu_name']);

        $data   = array_merge($data,$config);

        return $this->success($data);
    }





    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-01-04 14:41
     * @功能说明:账号列表
     */
    public function adminList(){

        $input = $this->_param;

        $admin_model = new Admin();

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['status','>',-1];

        $where = [];
        if(!empty($input['title'])){

            $where[] = ['username','like','%'.$input['title'].'%'];

            $where[] = ['agent_name','like','%'.$input['title'].'%'];
        }

        if($this->_user['is_admin']==0){

            $dis[] = ['is_admin','=',3];

            $dis[] = ['admin_id','=',$this->_user['id']];

        }elseif ($this->_user['is_admin']==1){

            $dis[] = ['is_admin','=',2];

            $dis[] = ['admin_id','=',0];
        }else{
            //其他人的权限都不能开
            $dis[] = ['admin_id','=',-1];
        }

        $data = $admin_model->dataList($dis,$input['limit'],$where);

        if(!empty($data['data'])){

            $adminRole_mdoel = new RoleAdmin();

            foreach ($data['data'] as &$v){

                $dis = [

                    'a.uniacid' => $this->_uniacid,

                    'b.status'  => 1,

                    'a.admin_id'=> $v['id']
                ];

                $v['role'] = $adminRole_mdoel->alias('a')
                    ->join('massage_role_list b','a.role_id = b.id')
                    ->where($dis)
                    ->field('b.title')
                    ->group('b.id')
                    ->select()
                    ->toArray();
            }
        }

        return $this->success($data);

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-01-04 14:47
     * @功能说明:添加账号
     */
    public function adminAdd(){

        $input = $this->_input;

        $admin_model = new Admin();

        $dis = [

            'uniacid' => $this->_uniacid,

            'username'=> $input['username'],
        ];

        $check = $admin_model->checkDatas($this->_uniacid);

        if(!empty($check['code'])){

            $this->errorMsg($check['msg']);
        }

        $find = $admin_model->where($dis)->where('status','>',-1)->find();

        if(!empty($find)){

            if($find->is_admin==2){

                $this->errorMsg('账号名不能重复');

            }elseif ($find->is_admin==1){

                $this->errorMsg('admin为超管账号');

            }else{
                $this->errorMsg('该账号名为代理商账号');

            }

        }

        $insert = [

            'uniacid' => $this->_uniacid,

            'username'=> $input['username'],

            'agent_name'=> !empty($input['agent_name'])?$input['agent_name']:$input['username'],

            'passwd_text'=> $input['passwd'],

            'phone'=> $input['phone'],

            'passwd'  => checkPass($input['passwd']),
             //代理商开的账号为 3
            'is_admin'=> $this->_user['is_admin']==1?2:3,
        ];

        if($this->_user['is_admin']==0){

            $insert['admin_id'] = $this->_user['id'];
        }

        $admin_model->dataAdd($insert);

        $admin_id = $admin_model->getLastInsID();

        $adminRole_mdoel = new RoleAdmin();

        $adminRole_mdoel->where(['admin_id'=>$admin_id])->delete();

        if(!empty($input['role'])){

            foreach ($input['role'] as $key => $value){

                $inserts[$key] = [

                    'uniacid' => $this->_uniacid,

                    'admin_id'=> $admin_id,

                    'role_id' => $value

                ];

            }

            $adminRole_mdoel->saveAll($inserts);
        }

        return $this->success(true);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-01-04 14:47
     * @功能说明:添加账号
     */
    public function adminUpdate(){

        $input = $this->_input;

        $admin_model = new Admin();

        if(!empty($input['username'])){

            $dis = [

                'uniacid' => $this->_uniacid,

                'username'=> $input['username'],
            ];

            $find = $admin_model->where($dis)->where('status','>',-1)->where('id','<>',$input['id'])->find();

            if(!empty($find)){

                if($find->is_admin==2){

                    $this->errorMsg('账号名不能重复');

                }elseif ($find->is_admin==1){

                    $this->errorMsg('admin为超管账号');

                }else{
                    $this->errorMsg('该账号名为代理商账号');

                }
            }
        }

        if(!empty($input['passwd'])){

            $input['passwd_text'] = $input['passwd'];

            $input['passwd']   = checkPass($input['passwd']);

        }

        $dis = [

            'id' => $input['id']
        ];

        if(isset($input['role'])){

            $role = $input['role'];

            unset($input['role']);
        }


        $admin_info = $admin_model->dataInfo($dis);

        $admin_model->dataUpdate($dis,$input);

        $adminRole_mdoel = new RoleAdmin();

        $adminRole_mdoel->where(['admin_id'=>$input['id']])->delete();

        if(!empty($role)){

            foreach ($role as $key => $value){

                $inserts[$key] = [

                    'uniacid' => $this->_uniacid,

                    'admin_id'=> $input['id'],

                    'role_id' => $value

                ];

            }

            $adminRole_mdoel->saveAll($inserts);
        }

        if(!empty($input['passwd_text'])){

            if($admin_info['passwd_text']!=$input['passwd_text']||$input['username']!=$admin_info['username']){
                //添加缓存数据
                clearCache(7777,$_SERVER['HTTP_HOST'].$input['id']);
            }
        }

        return $this->success(true);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-20 19:05
     * @功能说明:日志列表
     */
    public function logList(){

        $input = $this->_param;

        $dis[] = ['a.uniacid','=',$this->_uniacid];

        if($this->_user['is_admin']!=1){

            $dis[] = ['a.user_id','=',$this->_user['id']];

        }

        if(!empty($input['user_name'])){

            $dis[] = ['b.username','like',"%".$input['user_name'].'%'];

        }

        if(!empty($input['start_time'])&&!empty($input['end_time'])){

            $dis[] = ['a.create_time','between',"{$input['start_time']},{$input['end_time']}"];

        }

        $log_model =new ActionLog();

        $data = $log_model->logList($dis,$input['limit']);

        return $this->success($data);
    }












}
