<?php
namespace app\dynamic\model;

use app\BaseModel;
use app\massage\model\ConfigSetting;
use app\massage\model\ShieldList;
use think\facade\Db;

class DynamicFollow extends BaseModel
{
    //定义表名
    protected $name = 'massage_dynamic_follow';


    protected $append = [


        'friend_time'
    ];


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-01-29 13:54
     * @功能说明:处理友好时间
     */
    public function getFriendTimeAttr($value,$data){

        if(isset($data['create_time'])){

            return lbGetDates($data['create_time']);
        }

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
     * @DataTime: 2023-01-29 11:44
     * @功能说明:评论列表
     */
    public function getFollowList($dis,$page=10){

        $data = $this->alias('a')
            ->join('massage_service_user_list b','a.user_id = b.id','left')
            ->where($dis)
            ->field('a.*,b.nickName,b.avatarUrl')
            ->group('a.id')
            ->order('a.create_time desc,a.id desc')
            ->paginate($page)
            ->toArray();

        return $data;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-08 17:53
     * @功能说明:关注的技师列表
     */
    public function followCoachList($dis,$alh,$page=10){

        $data = $this->alias('a')
            ->join('massage_service_coach_list b','a.coach_id = b.id')
            ->where($dis)
            ->field(['a.*,b.coach_name,b.work_img,b.order_num',$alh])
            ->group('a.coach_id')
            ->order('distance,a.id desc')
            ->paginate($page)
            ->toArray();

        return $data;
    }



    /**
     * @param $user_id
     * @功能说明:用户关注技师的数量
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-08 17:56
     */
    public function followCoachNum($user_id){

        $dis = [

            'a.user_id' => $user_id,

            'a.status'  => 1,

            'b.status'  => 2,

        ];

        $shield_model = new ShieldList();
        //除开屏蔽技师的
        $coach_id = $shield_model->where(['user_id'=>$user_id])->where('type','in',[2,3])->column('coach_id');

        $data = $this->alias('a')
            ->join('massage_service_coach_list b','a.coach_id = b.id')
            ->where($dis)
            ->where('a.coach_id','not in',$coach_id)
            ->group('a.coach_id')
            ->count();

        return $data;
    }




    /**
     * @param $user_id
     * @功能说明:关注技师的用户数量
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-08 17:56
     */
    public function followUserNum($coach_id){

        $dis = [

            'a.coach_id' => $coach_id,

            'a.status'  => 1,
        ];

        $data = $this->alias('a')
            ->join('massage_service_coach_list b','a.coach_id = b.id')
            ->where($dis)
            ->group('a.id')
            ->count();

        return $data;
    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-01-29 16:52
     * @功能说明:获取技师最新动态条数
     */
    public function getFollowDynamicNum($user_id,$where=[]){

        $shield_model = new ShieldList();
        //除开屏蔽技师的
        $coach_id = $shield_model->where(['user_id'=>$user_id])->column('coach_id');

        $dis = [

            'a.user_id' => $user_id,

            'b.status'  => 2
        ];

        $total_num = $this->alias('a')
                     ->join('massage_dynamic_list b','a.coach_id = b.coach_id AND a.create_time < b.create_time')
                     ->join('massage_service_coach_list d','a.coach_id = d.id')
                     ->where($dis)
                     ->where($where)
                     ->where('a.coach_id','not in',$coach_id)
                     ->group('b.id')
                     ->count();


        $have_num = $this->alias('a')
                    ->join('massage_dynamic_list b','a.coach_id = b.coach_id AND a.create_time < b.create_time')
                    ->join('massage_dynamic_watch_record c','b.id = c.dynamic_id')
                    ->join('massage_service_coach_list d','a.coach_id = d.id')
                    ->where($dis)
                    ->where($where)
                    ->where('a.coach_id','not in',$coach_id)
                    ->group('b.id')
                    ->count();

        return ($total_num - $have_num)>=0?$total_num - $have_num:0;

    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-01-29 16:52
     * @功能说明:获取技师最新动态条数
     */
    public function getFollowDynamicData($user_id){

        $dis = [

            'a.user_id' => $user_id,

            'b.status'  => 2
        ];

        $record_model = new DynamicWatchRecord();

        $id_arr = $record_model->where(['user_id'=>$user_id])->column('dynamic_id');

        $data = $this->alias('a')
            ->join('massage_dynamic_list b','a.coach_id = b.coach_id AND a.create_time < b.create_time')
            ->where($dis)
            ->where('b.id','not in',$id_arr)
            ->field('b.id,a.uniacid')
            ->group('b.id')
            ->select()
            ->toArray();

        return $data;

    }





}