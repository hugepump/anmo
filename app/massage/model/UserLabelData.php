<?php
namespace app\massage\model;

use app\BaseModel;
use think\facade\Db;

class UserLabelData extends BaseModel
{
    //定义表名
    protected $name = 'massage_service_user_label_data';


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
     * @DataTime: 2022-10-24 15:16
     * @功能说明:获取用户标签
     */
    public function getUserLabel($user_id){

        $dis = [

            'a.user_id' => $user_id,

            'b.status'  => 1,

            'a.status'  => 1
        ];

        $data = $this->alias('a')
                ->join('massage_service_user_label_list b','a.label_id = b.id')
                ->where($dis)
                ->field('a.*,b.title')
                ->group('a.label_id')
                ->order('a.id desc')
                ->select()
                ->toArray();

        return $data;

    }








}