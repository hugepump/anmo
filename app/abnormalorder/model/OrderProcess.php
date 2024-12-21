<?php
namespace app\abnormalorder\model;

use app\BaseModel;
use app\massage\model\Admin;
use app\massage\model\Order;
use app\node\model\RoleList;
use think\facade\Db;

class OrderProcess extends BaseModel
{
    //定义表名
    protected $name = 'massage_service_abnormal_order_process';



    protected $append = [

        'role',

        'wander'
    ];


    /**
     * @param $value
     * @param $data
     * @功能说明:节点关联的角色 账号 代理商
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-11-27 14:28
     */
    public function getRoleAttr($value,$data){

        if(!empty($data['id'])){

            $role_model = new OrderRole();

            $dis = [

                'a.process_id' => $data['id'],

                'a.wander_id'  => 0
            ];
            //添加的时候不要角色 所以这里不用管，有角色处理订单会有问题
//            $list = $role_model->alias('a')
//                ->join('massage_role_list b','a.user_id = b.id')
//                ->where($dis)
//                ->where(['a.type'=>1])
//                ->where(['b.status'=>1])
//                ->field('a.*,b.title as user_name')
//                ->group('a.user_id')
//                ->select()
//                ->toArray();

            $list2 = $role_model->alias('a')
                ->join('shequshop_school_admin b','a.user_id = b.id AND b.status =1','left')
                ->where($dis)
                ->where('a.type','in',[2,3])
               // ->where(['b.status'=>1])
                ->field('a.*,b.agent_name as user_name')
                ->group('a.user_id')
                ->select()
                ->toArray();

            if(!empty($list2)){

                foreach ($list2 as &$s){

                    if($s['type']==3){

                        $s['user_name'] = '代理商';
                    }
                }
            }

          //  return array_merge($list,$list2);

            return $list2;
        }
    }


    /**
     * @param $process_id
     * @param int $pass_type
     * @功能说明:获取角色
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-01-04 17:34
     */
    public function getRoleData($process_id,$pass_type=0,$order_info_id=0){

        $role_model = new OrderRole();

        $handle_model = new OrderInfoHandle();

        $roles_model  = new RoleList();

        $order_model  = new Order();

        $abn_order_model = new OrderList();

        $info_model = new OrderInfo();

        $admin_model = new Admin();

        $dis = [

            'a.process_id' => $process_id,

            'a.pass_type'  => $pass_type
        ];

//        $list = $role_model->alias('a')
//            ->join('massage_role_list b','a.user_id = b.id')
//            ->where($dis)
//            ->where(['a.type'=>1])
//            ->where(['b.status'=>1])
//            ->field('a.*,b.title as user_name')
//            ->group('a.user_id')
//            ->select()
//            ->toArray();

        $list2 = $role_model->alias('a')
            ->join('shequshop_school_admin b','a.user_id = b.id AND b.status =1','left')
            ->where($dis)
            ->where('a.type','in',[2,3])
            ->field('a.*,b.agent_name as user_name')
            ->group('a.user_id')
            ->select()
            ->toArray();

        $order_id = $info_model->where(['id'=>$order_info_id])->value('order_id');

        $order_id = $abn_order_model->where(['id'=>$order_id])->value('order_id');

        $admin_id = $order_model->where(['id'=>$order_id])->value('admin_id');

        if(!empty($list2)){

            foreach ($list2 as &$s){

                if($s['type']==3){

                    $s['user_name'] = '代理商';
                }
                //查看处理情况
                if(!empty($order_info_id)){

                    if($s['type']==3){

                        $user_id = $admin_model->where(['admin_id'=>$admin_id])->column('id');

                        $user_id[] = $admin_id;

                        $find = $handle_model->where('user_id','in',$user_id)->where(['order_info_id'=>$order_info_id])->find();

                        if(!empty($find)){

                            $s['user_name'] = $admin_model->where(['id'=>$find['user_id']])->value('agent_name');
                        }
                    }else{

                        $find = $handle_model->where(['user_id'=>$s['user_id'],'order_info_id'=>$order_info_id])->find();
                    }

                    $s['handle_status'] = !empty($find)?$find['status']:1;

                    $s['deduct_cash'] = !empty($find)?$find['deduct_cash']:0;

                    $s['create_time'] = !empty($find)?$find['create_time']:0;
                }

                $s['role'] = $roles_model->getUserRole($s['user_id']);
            }
        }

        return $list2;
    }









    /**
     * @param $value
     * @param $data
     * @功能说明:流转设置
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-11-27 15:08
     */
    public function getWanderAttr($value,$data){

        if(!empty($data['id'])){

            $wander_model = new OrderWander();

//            $arr['wander_pass'] = 0;
//
//            $arr['refuse_pass'] = 0;

            $list  = $wander_model->where(['process_id'=>$data['id']])->select()->toArray();

//            if(!empty($list)){
//
//                foreach ($list as $value){
//
//                    if($value['pass_type']==1){
//
//                        $arr['wander_pass'] = 1;
//
//                    }elseif($value['pass_type']==2){
//
//                        $arr['refuse_pass'] = 1;
//                    }
//                }
//            }
            return $list;
        }
    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:04
     * @功能说明:添加
     */
    public function dataAdd($data){


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








}