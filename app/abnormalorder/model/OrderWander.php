<?php
namespace app\abnormalorder\model;

use app\BaseModel;
use think\facade\Db;

class OrderWander extends BaseModel
{
    //定义表名
    protected $name = 'massage_service_abnormal_order_wander';


    protected $append = [

        'role',
    ];


    /**
     * @param $value
     * @param $data
     * @功能说明:节点关联的角色 账号 代理商
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-11-27 14:28
     */
    public function getRoleAttr($value,$data){

        if(!empty($data['id'])&&!empty($data['process_id'])){

            $role_model = new OrderRole();

            $dis = [

                'a.process_id' => $data['process_id'],

                'a.wander_id'  => $data['id']
            ];

//            $list = $role_model->alias('a')
//                ->join('massage_role_list b','a.user_id = b.id')
//                ->where($dis)
//                ->where(['a.type'=>1])
//                ->where(['b.status'=>1])
//                ->field('a.*,b.title as user_name')
//                ->group('a.id')
//                ->select()
//                ->toArray();

            $list2 = $role_model->alias('a')
                ->join('shequshop_school_admin b','a.user_id = b.id AND b.status =1','left')
                ->where($dis)
                ->where('a.type','in',[2,3])
                // ->where(['b.status'=>1])
                ->field('a.*,b.agent_name as user_name')
                ->group('a.id')
                ->select()
                ->toArray();

            if(!empty($list2)){

                foreach ($list2 as &$s){

                    if($s['type']==3){

                        $s['user_name'] = '代理商';
                    }
                }
            }

            return $list2;

            return array_merge($list,$list2);
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
     * @param $insert
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-11-27 16:07
     */
    public function wanderAdd($insert,$uniacid,$process_id){

        $role_model = new OrderRole();
        //流转设置
        $this->where(['process_id'=>$process_id])->delete();

        if(!empty($insert)){

            foreach ($insert as $value){

                $wander_insert = [

                    'uniacid'      => $uniacid,

                    'process_id'   => $process_id,

                    'pass_type'    => $value['pass_type'],

                    'deduct_status'=> $value['deduct_status'],

                    'status'       => $value['status'],
                ];

                $this->dataAdd($wander_insert);

                $wander_id = $this->getLastInsID();

                $role_insert = [];

                foreach ($value['role'] as $ks=>$vs){

                    $role_insert[$ks] = [

                        'uniacid'    => $uniacid,

                        'process_id' => $process_id,

                        'type'       => $vs['type'],

                        'user_id'    => $vs['user_id'],

                        'pass_type'  => $value['pass_type'],

                        'wander_id'  => $wander_id
                    ];
                }

                $role_model->saveAll($role_insert);
            }
        }

        return true;
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