<?php
namespace app\massage\model;

use app\BaseModel;
use think\facade\Db;

class Diy extends BaseModel
{
    //定义表名
    protected $name = 'massage_action_diy';




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

        if(empty($data)){

            $input = $dis;

            $input['page'] = [

                1=>[],

                2=>[],

                4=>[],
                5=>[],
            ];

            $input['page'] = json_encode($input['page']);

            $input['tabbar'] = [
                [
                    'id'=> 1,
                    'name'=>'首页',
                    'default_img'=>'iconshouye11',
                    'selected_img'=>'iconshouye21',
                ],
                [
                    'id'=> 2,
                    'name'=>'技师',
                    'default_img'=>'iconanmo1',
                    'selected_img'=>'iconanmo2',
                ],
                [
                    'id'=> 4,
                    'name'=>'订单',
                    'default_img'=>'icondingdan3',
                    'selected_img'=>'icondingdan2',
                ],
                [
                    'id'=> 5,
                    'name'=>'我的',
                    'default_img'=>'iconwode1',
                    'selected_img'=>'iconwode2',
                ]

            ];
            $input['tabbar'] = json_encode($input['tabbar']);

            $this->dataAdd($input);

            $data = $this->where($dis)->find();

        }

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






}