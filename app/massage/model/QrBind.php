<?php
namespace app\massage\model;

use app\BaseModel;
use think\facade\Db;

class QrBind extends BaseModel
{
    //定义表名
    protected $name = 'massage_qr_bind';




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
     * @DataTime: 2021-03-19 16:08
     * @功能说明:开启默认
     */
    public function updateOne($id){

        $user_id = $this->where(['id'=>$id])->value('user_id');

        $res = $this->where(['user_id'=>$user_id])->where('id','<>',$id)->update(['status'=>0]);

        return $res;
    }


    /**
     * @param $openid
     * @功能说明:获取上级
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-06-05 18:40
     */
    public function getPid($openid,$unionid=''){

        $where[] = ['forever','=',1];

        $where[] = ['over_time','>',time()];

        $dis = [

            'openid' => $openid,
        ];

        if(!empty($unionid)){

            $dis['unionid'] = $unionid;
        }

        $pid = $this->where(function ($query) use ($dis){
            $query->whereOr($dis);
        })->where(function ($query) use ($where){
            $query->whereOr($where);
        })->value('pid');

        return !empty($pid)?$pid:0;
    }








}