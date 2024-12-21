<?php
namespace app\member\model;

use app\BaseModel;
use app\member\info\PermissionMember;
use think\facade\Db;

class Service extends BaseModel
{
    //定义表名
    protected $name = 'massage_member_service';




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
     * @DataTime: 2023-03-26 22:33
     * @功能说明:支持该服务的会员等级
     */
    public function getMemberService($service_id){

        $dis = [

            'a.service_id' => $service_id,

            'b.status'     => 1
        ];

        $data = $this->alias('a')
            ->join('massage_member_level b','a.level_id = b.id')
            ->where($dis)
            ->field('b.id,b.title')
            ->group('a.id')
            ->order('b.growth,a.id desc')
            ->select()
            ->toArray();

        return array_values($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-14 18:54
     * @功能说明:
     */
    public function getMemberServiceOne($service_id,$level_id=0){

        $dis = [

            'a.service_id' => $service_id,

            'b.status'     => 1
        ];

        if(!empty($level_id)){

            $dis['b.id'] = $level_id;
        }

        $data = $this->alias('a')
            ->join('massage_member_level b','a.level_id = b.id')
            ->where($dis)
            ->field('b.title')
            ->group('a.id')
            ->order('b.growth,a.id desc')
            ->find();

        return !empty($data)?$data->toArray():[];
    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-14 18:46
     * @功能说明:
     */
    public function getServiceMember($service_id,$level_id,$member_auth){
        //是否是会员商品
        if($member_auth==1){
            //是否是会员
            if(!empty($level_id)){

                $list = $this->getMemberServiceOne($service_id,$level_id);

                if(!empty($list)){

                    $data['can_buy'] = 1;

                    return array_merge($data,$list);
                }
            }

            $list = $this->getMemberServiceOne($service_id);

            $data['can_buy'] = !empty($list)?0:1;

            $data = array_merge($data,$list);

        }else{

            $data['can_buy'] = 1;
        }

        return $data;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-14 19:10
     * @功能说明:获取用户会员情况
     */
    public function getUserMember($uniacid,$user_id){

        $m_auth = new PermissionMember( $uniacid);

        $data['member_level'] = 0;

        $data['member_auth']  = 1;
        //判断有无插件权限
        if($m_auth->pAuth()==false){

            $data['member_auth'] = 0;

            return $data;
        }
        $config_model = new Config();
        //判断是否开启会员功能
        $config = $config_model->dataInfo(['uniacid'=>$uniacid]);

        if($config['status']==0){

            $data['member_auth'] = 0;

            return $data;
        }

        $level_model = new Level();
        //获取会员等级
        $member_level = $level_model->getUserLevel($user_id,0);

        $data['member_level']  = !empty($member_level)?$member_level['id']:0;

        return $data;
    }











}