<?php
namespace app\dynamic\model;

use app\BaseModel;
use think\facade\Db;

class DynamicComment extends BaseModel
{
    //定义表名
    protected $name = 'massage_dynamic_comment';


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
    public function getCommentList($dis,$page=10,$where=[]){

        $data = $this->alias('a')
            ->join('massage_service_user_list b','a.user_id = b.id','left')
            ->join('massage_dynamic_list c','a.dynamic_id = c.id','left')
            ->where($dis)
            ->where(function ($query) use ($where){
                $query->whereOr($where);
            })
            ->field('a.*,b.nickName,b.avatarUrl,c.cover,c.title')
            ->group('a.id')
            ->order('a.id desc')
            ->paginate($page)
            ->toArray();

        return $data;
    }







}