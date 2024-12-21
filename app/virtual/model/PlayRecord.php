<?php
namespace app\virtual\model;

use AlibabaCloud\Client\AlibabaCloud;
use app\BaseModel;
use Exception;
use think\facade\Db;

class PlayRecord extends BaseModel
{
    //定义表名
    protected $name = 'massage_aliyun_play_record';


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

        if(empty($data)){

            $this->dataAdd($dis);

            $data = $this->where($dis)->find();

        }

        return !empty($data)?$data->toArray():[];

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-12-08 14:11
     * @功能说明:查找记录
     */
    public function findRecord($phone,$pool_key){

        $dis = [

            'status'  => 1,

            'pool_key'=> $pool_key

        ];

        $where = [

            'phone_a' => $phone,

            'phone_b' => $phone,

        ];

        $data = $this->where($dis)->where(function ($query) use ($where){
            $query->whereOr($where);
        })->find();

        return !empty($data)?$data->toArray():[];

    }







}