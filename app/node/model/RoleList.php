<?php
namespace app\node\model;

use app\BaseModel;
use app\massage\model\Admin;
use app\mobilenode\info\PermissionMobilenode;
use app\node\info\PermissionNode;
use think\facade\Db;

class RoleList extends BaseModel
{
    //定义表名
    protected $name = 'massage_role_list';



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:04
     * @功能说明:添加
     */
    public function dataAdd($data){

        $data['create_time'] = time();

        if(isset($data['node'])){

            $node = $data['node'];

            unset($data['node']);
        }

        $res = $this->insert($data);

        $id  = $this->getLastInsID();

        if(isset($node)){

            $this->updateSome($node,$id,$data['uniacid']);
        }

        return $id;

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-01-04 14:10
     * @功能说明:添加权限节点
     */
    public function updateSome($data,$id,$uniacid){

        $node_model = new RoleNode();

        $node_model->where(['role_id'=>$id])->delete();

        if(!empty($data)){

            foreach ($data as $k=>$v){

                $data[$k]['uniacid'] = $uniacid;

                $data[$k]['role_id'] = $id;

                $data[$k]['auth']    = !empty($v['auth'])?implode(',',$v['auth']):'';

            }

            $node_model->saveAll($data);

        }

        return true;
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:05
     * @功能说明:编辑
     */
    public function dataUpdate($dis,$data){

        if(isset($data['node'])){

            $node = $data['node'];

            unset($data['node']);
        }

        $res = $this->where($dis)->update($data);

        if(isset($node)){

            $this->updateSome($node,$dis['id'],$data['uniacid']);

        }

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
     * @DataTime: 2021-03-19 16:08
     * @功能说明:开启默认
     */
    public function updateOne($id){

        $user_id = $this->where(['id'=>$id])->value('user_id');

        $res = $this->where(['user_id'=>$user_id])->where('id','<>',$id)->update(['status'=>0]);

        return $res;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-04 17:54
     * @功能说明:校验添加文件
     */
    public function checkDatas($uniacid){

        $p = new PermissionNode($uniacid);

        $auth_num = $p->getAuthNumber();

        $num = $this->where(['uniacid'=>$uniacid])->where('status','>',-1)->count();

        if($auth_num<=$num){

            return ['code'=>500,'msg'=>'授权数量不足'.$auth_num.'-'.$num];

        }

        return true;
    }


    /**
     * @param $uniacid
     * @功能说明:获取有订单权限的角色
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-12-11 16:53
     */
    public function getShopOrderRole($uniacid){

        $dis = [

            'a.uniacid' => $uniacid,

            'a.status'  => 1,

            'b.node'    => 'ShopOrder'
        ];

        $list = $this->alias('a')
            ->join('massage_role_node b','a.id = b.role_id')
            ->where($dis)
            ->field('b.*')
            ->group('b.id')
            ->select()
            ->toArray();

        $arr = [];

        if(!empty($list)){

            foreach ($list as $value){

                $auth = !empty($value['auth'])?explode(',',$value['auth']):[];

                if(in_array('operAbnormal',$auth)){

                    $arr[] = $value['role_id'];

                }
            }
        }

        $role_admin_mdoel = new RoleAdmin();

        $admin_id = $role_admin_mdoel->where(['uniacid'=>$uniacid])->where('role_id','in',$arr)->column('admin_id');

        return $admin_id;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-12-13 14:43
     * @功能说明:获取用户的角色
     */
    public function getUserRole($user_id){

        $admin_model = new Admin();

        $admin = $admin_model->dataInfo(['id'=>$user_id]);

        if(!empty($admin)){

            if($admin['is_admin']==0){

                return '代理商';

            }elseif ($admin['is_admin']==0){

                return '平台';
            }
        }

        $dis = [

            'b.admin_id' => $user_id,

            'a.status'   => 1
        ];

        $data = $this->alias('a')
                ->join('massage_role_admin b','a.id = b.role_id')
                ->where($dis)
                ->group('a.id')
                ->column('a.title');

        return !empty($data)?implode(',',$data):'';
    }







}