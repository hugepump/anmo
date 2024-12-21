<?php
namespace app\dynamic\model;

use app\BaseModel;
use think\facade\Db;

class DynamicList extends BaseModel
{
    //定义表名
    protected $name = 'massage_dynamic_list';




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
     * @param $value
     * @param $data
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-01-30 14:58
     */
    public function getImgsAttr($value,$data){

        if(!empty($value)){

            return explode(',',$value);
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
    public function dataList($dis,$page=10){

        $data = $this->where($dis)->order('top desc,id desc')->paginate($page)->toArray();

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
     * @DataTime: 2023-01-29 14:11
     * @功能说明:
     */
    public function coachDataList($dis,$alh,$page=10){

        $data = $this->alias('a')
                ->join('massage_service_coach_list b','a.coach_id = b.id','left')
                ->where($dis)
                ->field(['a.*',$alh,'b.coach_name,b.work_img'])
                ->order('a.top desc,a.create_time desc,distance asc,a.id desc')
                ->paginate($page)
                ->toArray();

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $v['distance'] = distance_text($v['distance']);

            }
        }

        return $data;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-01-29 14:11
     * @功能说明:
     */
    public function adminDataList($dis,$page=10){

        $data = $this->alias('a')
            ->join('massage_service_coach_list b','a.coach_id = b.id','left')
            ->where($dis)
            ->field('a.*,b.coach_name,b.work_img')
            ->order('a.top desc,a.create_time desc,a.id desc')
            ->paginate($page)
            ->toArray();

        return $data;
    }
    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-01-29 14:11
     * @功能说明:
     */
    public function coachFollowDataList($dis,$alh,$page=10){

        $data = $this->alias('a')
            ->join('massage_service_coach_list b','a.coach_id = b.id','left')
            ->join('massage_dynamic_follow c','a.coach_id = c.coach_id AND a.create_time >= c.create_time')
            ->where($dis)
            ->field(['a.*',$alh,'b.coach_name,b.work_img'])
            ->group('a.id')
            ->order('a.top desc,a.create_time desc,distance asc,a.id desc')
            ->paginate($page)
            ->toArray();

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $v['distance'] = distance_text($v['distance']);

            }
        }

        return $data;
    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-01-29 11:12
     * @功能说明:获取动态评论数量
     */
    public function getCommentNum($dis){

        $num = $this->alias('a')
                ->join('massage_dynamic_comment b','a.id = b.dynamic_id')
                ->where($dis)
                ->where('b.status','=',2)
                ->group('b.id')
                ->count();

        return $num;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-01-29 11:12
     * @功能说明:获取动态点赞数量
     */
    public function getThumbsNum($dis){

        $num = $this->alias('a')
            ->join('massage_dynamic_thumbs b','a.id = b.dynamic_id')
            ->where($dis)
            ->where('b.status','=',1)
            ->group('b.id')
            ->count();

        return $num;
    }







}