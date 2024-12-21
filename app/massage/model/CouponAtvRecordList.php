<?php
namespace app\massage\model;

use app\BaseModel;
use think\facade\Db;

class CouponAtvRecordList extends BaseModel
{
    //定义表名
    protected $name = 'massage_service_coupon_atv_record_list';




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
     * @DataTime: 2021-04-08 17:08
     * @功能说明:审核中
     */
    public function shIng($cap_id){

        $dis = [

            'cap_id' => $cap_id,

            'status' => 1
        ];

        $count = $this->where($dis)->count();

        return $count;

    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:06
     * @功能说明:列表
     */
    public function dataList($dis,$page=10){

        $data = $this->alias('a')
                ->join('massage_service_user_list b','b.id = a.to_inv_id')
                ->where($dis)
                ->field('b.nickName,b.avatarUrl,b.id,a.create_time')
                ->group('b.id')
                ->paginate($page)
                ->toArray();

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $v['create_time'] = date('Y-m-d H:i:s',$v['create_time']);

            }
        }

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














}