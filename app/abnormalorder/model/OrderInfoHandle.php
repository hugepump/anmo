<?php
namespace app\abnormalorder\model;

use app\BaseModel;
use app\node\model\RoleList;
use think\facade\Db;

class OrderInfoHandle extends BaseModel
{
    //定义表名
    protected $name = 'massage_service_abnormal_order_info_handle';




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
     * @DataTime: 2024-01-11 18:14
     * @功能说明:已经
     */
    public function handleUserList($order_info_id){

        $dis = [

            'order_info_id' => $order_info_id
        ];

        $data = $this->alias('a')
                ->join('shequshop_school_admin b','a.user_id = b.id')
                ->where($dis)
                ->field('a.*,b.agent_name as user_name')
                ->group('a.id')
                ->select()
                ->toArray();

        $role_model  = new RoleList();

        if(!empty($data)){

            foreach ($data as &$v){

                $v['handle_status'] = $v['status'];

                $v['role'] = $role_model->getUserRole($v['user_id']);
            }
        }

        return $data;
    }









}