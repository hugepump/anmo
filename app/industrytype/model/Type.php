<?php
namespace app\industrytype\model;

use AlibabaCloud\Client\AlibabaCloud;
use app\BaseModel;
use Exception;
use longbingcore\permissions\AdminMenu;
use think\facade\Db;

class Type extends BaseModel
{
    //定义表名
    protected $name = 'massage_industry_type';


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
     * @author chenniang
     * @DataTime: 2024-07-05 15:34
     * @功能说明:初始化
     */
    public function initData($uniacid){

        $arr = [

            [

                'uniacid' => $uniacid,
                'title' => '按摩师',
                'type'  => 1,
                'business_license' => 0,
                'health_certificate' => 0,
                'massage_certificate' => 1,
                'id_card' => 1,
                'avatar_verification' => 1,
                'door_service' => 1,
                'store_service' => 1,
                'birthday' => 1,
                'employment_years' => 1,
                'qualification' => 1,
                'age_sex' => 0
            ],
            [
                'uniacid' => $uniacid,
                'title' => '陪玩官',
                'type'  => 2,
                'business_license' => 0,
                'constellation' => 1,
                'health_certificate' => 0,
                'massage_certificate' => 0,
                'id_card' => 1,
                'avatar_verification' => 1,
                'door_service' => 1,
                'store_service' => 1,
                'height' => 1,
                'weight' => 1,
                'birthday' => 1,
                'qualification' => 0,
                'age_sex' => 1
            ]

        ];

        $key = 'industry_type_key';

        incCache($key,1,$uniacid,3);

        if(getCache($key,$uniacid)==1){

            foreach ($arr as $value){

                $dis = [

                    'uniacid' => $uniacid,

                    'type'    => $value['type']
                ];

                $find = $this->dataInfo($dis);

                if(empty($find)){

                    $this->dataAdd($value);
                }
            }
        }
        decCache($key,1,$uniacid);

        return true;
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
    public function dataInfo($dis,$filed='*'){

        $data = $this->where($dis)->field($filed)->find();

        return !empty($data)?$data->toArray():[];
    }

    /**
     * @Desc: 列表不分页
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @Auther: shurong
     * @Time: 2024/7/17 18:30
     */
    public function dataSelect($where,$filed='*')
    {
        return $this->where($where)->column($filed,'id');
    }


}