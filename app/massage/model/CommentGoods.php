<?php
namespace app\massage\model;

use app\BaseModel;
use think\facade\Db;

class CommentGoods extends BaseModel
{
    //定义表名
    protected $name = 'massage_service_order_comment_goods';





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
     * @param $data
     * @param $uniacid
     * @param $id
     * @功能说明:添加评价
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-08-03 13:46
     */
    public function commentAdd($data,$uniacid,$id){

        if(!empty($data)){

            foreach ($data as $v){

                $insert = [

                    'uniacid' => $uniacid,

                    'comment_id' => $id,

                    'star' => $v['star'],

                    'service_id' => $v['service_id']
                ];

                $this->dataAdd($insert);

                $this->commentUpdate($v['service_id']);

            }

        }

        return true;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-08-04 13:50
     * @功能说明:
     */
    public function commentUpdate($service_id){

        $all_count= $this->where(['service_id'=>$service_id])->count();

        $all_star = $this->where(['service_id'=>$service_id])->sum('star');

        $now_star = $all_count>0?round($all_star/$all_count,1):5;

        $now_star = $now_star>5?5:$now_star;

        $service_model = new Service();

        $service_model->dataUpdate(['id'=>$service_id],['star'=>$now_star]);

        return true;
    }









}