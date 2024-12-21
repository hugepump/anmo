<?php
namespace app\node\model;

use app\BaseModel;
use think\facade\Db;

class RoleAdmin extends BaseModel
{
    //定义表名
    protected $name = 'massage_role_admin';



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

        $data = $this->where($dis)->order('status desc,id desc')->paginate($page)->toArray();

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
     * @DataTime: 2023-08-02 15:42
     * @功能说明:获取用户权限
     */
    public function getUserAuth($user_id){

        $dis = [

            'b.status'  => 1,

            'a.admin_id'=> $user_id
        ];

        $data = $this->alias('a')
            ->join('massage_role_list b','a.role_id = b.id')
            ->join('massage_role_node c','c.role_id = b.id')
            ->where($dis)
            ->field('c.*')
            ->group('c.id')
            ->select()
            ->toArray();

        if(!empty($data)){

            foreach ($data as $k=>$v){

                $data[$k]['auth'] = !empty($v['auth'])?explode(',',$v['auth']):[];

            }
        }
        return $data;
    }


    /**
     * @param $user_id
     * @param $uniacid
     * @功能说明:获取角色关联的权限
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-11-28 14:26
     */
    public function getUserRole($user_id,$uniacid){

        $dis = [

            'a.uniacid' => $uniacid,

            'b.status'  => 1,

            'a.admin_id'=> $user_id
        ];

        $data = $this->alias('a')
            ->join('massage_role_list b','a.role_id = b.id')
            ->where($dis)
            ->field('b.*,a.role_id')
            ->group('b.id')
            ->column('b.id');

        return $data;
    }













}