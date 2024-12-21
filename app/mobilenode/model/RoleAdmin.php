<?php
namespace app\mobilenode\model;

use app\BaseModel;
use app\massage\model\Admin;
use app\massage\model\User;
use app\mobilenode\info\PermissionMobilenode;
use think\facade\Db;

class RoleAdmin extends BaseModel
{
    //定义表名
    protected $name = 'massage_mobile_role_admin';


    protected $append = [

        'nickName'

    ];


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-20 11:11
     * @功能说明:获取绑定用户姓名
     */
    public function getNickNameAttr($value,$data){

        if(!empty($data['user_id'])){

            $user_model = new User();

            $name = $user_model->where(['id'=>$data['user_id']])->value('nickName');

            return $name;
        }

    }


    /**
     * @param $value
     * @param $data
     * @功能说明:权限
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-20 11:10
     */
    public function getNodeAttr($value,$data){

        if(!empty($value)){

            return explode(',',$value);
        }

        return $value;

    }


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
     * @DataTime: 2023-05-04 17:54
     * @功能说明:校验添加文件
     */
    public function checkDatas($uniacid,$user_id,$admin_user){

        $dis = [

            'uniacid' => $uniacid,

            'user_id' => $user_id,
        ];

        $find = $this->where($dis)->where('status','>',-1)->find();

        if(!empty($find)){

            return ['code'=>500,'msg'=>'已经绑定过该用户'];

        }

        $p = new PermissionMobilenode($uniacid);

        $auth_num = $p->getAuthNumber();

        $num = $this->where(['uniacid'=>$uniacid])->where('status','>',-1)->count();

        if($auth_num<=$num){

            return ['code'=>500,'msg'=>'授权数量不足'.$auth_num.'-'.$num];

        }

        $admin_model = new Admin();

        $phone_admin_num = $admin_model->where(['id'=>$admin_user['admin_id']])->value('phone_admin_num');
        //代理商
        if($admin_user['is_admin']==0){

            $num = $this->where(['uniacid'=>$uniacid,'admin_id'=>$admin_user['admin_id']])->where('status','>',-1)->count();

            if($num>=$phone_admin_num){

                return ['code'=>500,'msg'=>'代理商授权权限数量不足'];
            }

        }

        return true;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-05 16:34
     * @功能说明:是否是管理员
     */
    public function checkAuthData($data){

        $dis = [

            'status' => 1,

            'user_id'=> $data['id']
        ];

        $find = $this->dataInfo($dis);

        if(!empty($find['admin_id'])){

            $admin_model = new Admin();

            $admin_where = [

                'id' => $find['admin_id'],

                'status'  => 1,

                //'agent_coach_auth' => 1
            ];

            $find = $admin_model->dataInfo($admin_where);

            if(!empty($find)&&$find['agent_coach_auth']==0&&$find['store_auth']==0&&$find['store_package_auth']&&$find['group_write_off_auth']==0){

                $find = [];
            }
        }

        $arr['mobilenode_auth'] = !empty($find)?1:0;

        return $arr;
    }













}