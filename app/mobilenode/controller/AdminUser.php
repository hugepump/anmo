<?php
namespace app\mobilenode\controller;
use app\admin\model\User;
use app\AdminRest;
use app\mobilenode\info\PermissionMobilenode;
use app\mobilenode\model\AdminStore;
use app\mobilenode\model\RoleAdmin;
use app\store\model\StoreList;
use think\App;



class AdminUser extends AdminRest
{

    public function __construct(App $app) {

        parent::__construct($app);

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-01-04 14:41
     * @功能说明:账号列表
     */
    public function adminList(){

        $input = $this->_param;

        $admin_model = new RoleAdmin();

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['status','>',-1];

        if($this->_user['is_admin']==0){

             $dis[] = ['admin_id','=',$this->_user['admin_id']];
        }else{

            $dis[] = ['admin_id','=',0];
        }

        $data = $admin_model->dataList($dis,$input['limit']);

        if(!empty($data['data'])){

            $user_model = new \app\massage\model\User();

            $store_model= new StoreList();

            foreach ($data['data'] as  &$v){

                $v['phone'] = $user_model->where(['id'=>$v['user_id']])->value('phone');
                //代理商管理员绑定门店
                if(!empty($v['admin_id'])){

                    $dis = [

                        'b.admin_id' => $v['id'],

                        'a.admin_id' => $v['admin_id'],

                        'a.status'   => 1
                    ];

                    $v['store'] = $store_model->alias('a')
                                  ->join('massage_mobile_role_store b','a.id = b.store_id')
                                  ->where($dis)
                                  ->field('a.title,b.store_id')
                                  ->group('a.id')
                                  ->select()
                                  ->toArray();

                    $v['store_name'] = implode('、',array_column($v['store'],'title'));
                }
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

        $admin_model = new RoleAdmin();

        $check = $admin_model->checkDatas($this->_uniacid,$input['user_id'],$this->_user);

        if(!empty($check['code'])){

            $this->errorMsg($check['msg']);
        }

        $insert = [

            'uniacid' => $this->_uniacid,

            'user_id' => $input['user_id'],

            'mobile' => $input['mobile'],

            'node'    => implode(',',$input['node'])
        ];

        if($this->_user['is_admin']==0){

            $insert['admin_id'] = $this->_user['admin_id'];
        }

        $res = $admin_model->dataAdd($insert);

        if($this->_user['is_admin']==0&&!empty($input['store'])){

            $id = $admin_model->getLastInsID();

            $store_model = new AdminStore();

            foreach ($input['store'] as $key=>$value){

                $store_insert[$key] = [

                    'uniacid' => $this->_uniacid,

                    'admin_id'=> $id,

                    'store_id'=> $value
                ];
            }

            $store_model->saveAll($store_insert);
        }

        return $this->success($res);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-21 14:43
     * @功能说明:删除上下架
     */
    public function adminStatusUpdate(){

        $input = $this->_input;

        $dis = [

            'id' => $input['id']
        ];

        $admin_model = new RoleAdmin();

        $res = $admin_model->dataUpdate($dis,$input);

        return $this->success($res);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-01-04 14:47
     * @功能说明:添加账号
     * T00000028140
     * d3c62bc0-de60-11ed-8959-6d808d7ae24e
     * https://openapis.7moor.com
     */
    public function adminUpdate(){

        $input = $this->_input;

        $admin_model = new RoleAdmin();

        $dis = [

            'uniacid' => $this->_uniacid,

            'user_id' => $input['user_id'],
        ];

        $find = $admin_model->where($dis)->where('id','<>',$input['id'])->where('status','>',-1)->find();

        if(!empty($find)){

            $this->errorMsg('已经绑定过该用户');
        }

        $insert = [

            'uniacid' => $this->_uniacid,

            'user_id' => $input['user_id'],

            'node'    => implode(',',$input['node']),

            'mobile' => $input['mobile']
        ];

        $res = $admin_model->dataUpdate(['id'=>$input['id']],$insert);
        //绑定门店
        if($this->_user['is_admin']==0&&!empty($input['store'])){

            $store_model = new AdminStore();

            $store_model->where(['admin_id'=>$input['id']])->delete();

            foreach ($input['store'] as $key=>$value){

                $store_insert[$key] = [

                    'uniacid' => $this->_uniacid,

                    'admin_id'=> $input['id'],

                    'store_id'=> $value
                ];
            }

            $store_model->saveAll($store_insert);
        }

        return $this->success($res);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-20 11:23
     * @功能说明:详情
     */
    public function adminInfo(){

        $input = $this->_param;

        $admin_model = new RoleAdmin();

        $dis = [

            'id' => $input['id']
        ];

        $data = $admin_model->dataInfo($dis);

        return $this->success($data);

    }








}
