<?php
namespace app\abnormalorder\model;

use app\BaseModel;
use app\massage\model\Admin;
use app\node\model\RoleAdmin;
use app\node\model\RoleList;
use think\facade\Db;

class OrderList extends BaseModel
{
    //定义表名
    protected $name = 'massage_service_abnormal_order_list';




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:04
     * @功能说明:添加
     */
    public function dataAdd($data){

        $data['create_time'] = time();

        $res = $this->insert($data);

        return $res;

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:05
     * @功能说明:编辑
     */
    public function dataUpdate($dis,$data){

        $res = $this->where($dis)->update($data);

        return $res;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:06
     * @功能说明:列表
     */
    public function dataList($dis,$page){

        $data = $this->where($dis)->order('id desc')->paginate($page)->toArray();

        return $data;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:43
     * @功能说明:
     */
    public function dataInfo($dis){

        $data = $this->where($dis)->find();

        return !empty($data)?$data->toArray():[];

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-11-28 15:11
     * @功能说明:获取未处理订单数量
     */
    public function getPendingOrderCount($uniacid,$user_id,$is_admin){

        $adminRole_mdoel = new RoleAdmin();

        $where = $where1 = [];

        $dis[] = ['a.uniacid','=',$uniacid];

        $dis[] = ['a.is_handle','=',0];

        $dis[] = ['a.first_cancel','=',0];

        if($is_admin==2){

            $role = $adminRole_mdoel->getUserRole($user_id,$uniacid);

            $where = [

                ['d.user_id','=',$user_id],

                ['d.type','=',2],
            ];

            $where1 =[
                ['d.user_id','in',$role],

                ['d.type','=',1]
            ];

        }elseif ($is_admin==0){

            $where = [

                ['c.admin_id' ,'=', $user_id],

                ['d.type','=',  3],
            ];
        }
        if(empty($where1)&&empty($where)){

            $where = $where1 = ['a.uniacid','=',$uniacid];

        }elseif(empty($where)){

            $where = ['a.uniacid','=',-1];

        }elseif (empty($where1)){

            $where1 = ['a.uniacid','=',-1];
        }

        $admin_model = new Admin();

        $admin = $admin_model->dataInfo(['id'=>$user_id]);

        $user_id = $this->getAdminUser($admin);

        $user_id = implode(',',$user_id);

        $data = $this->alias('a')
            ->join('massage_service_order_list c','a.order_id = c.id')
            ->join('massage_service_abnormal_order_role d','d.process_id = a.process_id AND a.pass_type = d.pass_type','left')
            ->join('massage_service_abnormal_order_info_handle e',"a.info_id = e.order_info_id AND e.user_id in ($user_id)",'left')
            ->where($dis)
            ->whereNull('e.id')
            ->where(function ($query) use ($where,$where1){
                $query->whereOr([$where,$where1]);
            })
            ->group('a.id')
            ->count();

        return $data;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-11-28 15:32
     * @功能说明:获取已经处理的订单数量
     */
    public function getAlreadyOrderCount($uniacid,$user){

        $dis[] = ['a.uniacid','=',$uniacid];

        $dis[] = ['a.status','>',-1];

        $dis[] = ['a.is_handle','=',1];

        if($user['is_admin']!=1){

            $user_id = $this->getAdminUser($user);

            $dis[] = ['c.user_id','in',$user_id];
        }

        $data = $this->alias('a')
            ->join('massage_service_abnormal_order_info_handle c','a.id = c.order_id')
            ->where($dis)
            ->group('a.id')
            ->count();

        return $data;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-12-15 15:50
     * @功能说明:获取订单处理信息
     */
    public function getOrderResult($data,$is_mobile=0){

        $admin_model = new Admin();

        $role_model  = new RoleList();

        $info_model  = new OrderInfo();
        //发起人
        $data['first_handle'] = [

            'user_name' => $admin_model->where(['id'=>$data['user_id']])->value('agent_name'),

            'role'      => $role_model->getUserRole($data['user_id']),

            'time'      => $is_mobile==0?$data['create_time']:date('Y-m-d H:i:s',$data['create_time']),

        ];
        //已经处理
        if($data['is_handle']==1){
            //最终结果
            $data['end_handle'] = [

                'user_name' => $admin_model->where(['id'=>$data['end_user_id']])->value('agent_name'),

                'role'      => $role_model->getUserRole($data['end_user_id']),

                'time'      => $is_mobile==0?$data['end_time']:date('Y-m-d H:i:s',$data['end_time']),

                'status'    => $data['status']
            ];
        }
        //已经扣款金额
        $data['have_deduct_cash'] = $info_model->where(['order_id'=>$data['id']])->sum('deduct_cash');

        return $data;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-12-15 18:15
     * @功能说明:获取当前用户是否可以处理该订单
     */
    public function getCanHandleUser($order,$user,$process_id){

        $adminRole_mdoel = new RoleAdmin();

        if($user['is_admin']==2){

            $role = $adminRole_mdoel->getUserRole($user['id'],$order['uniacid']);

            $where = [

                ['d.user_id','=',$user['id']],

                ['d.type','=',2],
            ];

            $where1 =[
                ['d.user_id','in',$role],

                ['d.type','=',1]
            ];

        }elseif ($user['is_admin']==0){

            $where = [

                ['b.admin_id' ,'=', $user['admin_id']],

                ['d.type','=',  3],
            ];
        }

        if(empty($where1)&&empty($where)){

            $where = $where1 = ['a.uniacid','=',$order['uniacid']];

        }elseif(empty($where)){

            $where = ['a.uniacid','=',-1];

        }elseif (empty($where1)){

            $where1 = ['a.uniacid','=',-1];
        }

        $dis[] = ['a.id','=',$order['id']];

        $dis[] = ['a.process_id','=',$process_id];

        $dis[] = ['a.first_cancel','=',0];

        $user_id = $this->getAdminUser($user);

        $user_id = implode(',',$user_id);

        $data = $this->alias('a')
            ->join('massage_service_order_list b','a.order_id = b.id')
            ->join('massage_service_abnormal_order_role d','d.process_id = a.process_id AND a.pass_type = d.pass_type','left')
            ->join('massage_service_abnormal_order_info_handle e',"a.info_id = e.order_info_id AND e.user_id in ($user_id)",'left')
            ->where($dis)
            ->whereNull('e.id')
            ->where(function ($query) use ($where,$where1){
                $query->whereOr([$where,$where1]);
            })
            ->find();

        return !empty($data)?1:0;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-02-07 18:48
     * @功能说明:获取代理商和下级代理商id
     */
    public function getAdminUser($user){

        if(in_array($user['is_admin'],[0,3])){

            $admin_model = new Admin();

            $admin = $admin_model->dataInfo(['id'=>$user['id']]);

            if($admin['is_admin']==0){

                $user_id = $admin_model->where(['admin_id'=>$user['id']])->column('id');

                $user_id[] = $user['id'];
            }else{

                $user_id = $admin_model->where(['admin_id'=>$user['admin_id']])->column('id');

                $user_id[] = $user['admin_id'];
            }
        }else{

            $user_id = [$user['id']];
        }

        return $user_id;
    }





}