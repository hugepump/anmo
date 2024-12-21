<?php
namespace app\massage\model;

use app\BaseModel;
use think\facade\Db;

class DistributionConfig extends BaseModel
{
    //定义表名
    protected $name = 'massage_distribution_config';




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

        $this->initData($dis['uniacid']);

        $data = $this->where($dis)->find();

        return !empty($data)?$data->toArray():[];

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-30 11:03
     * @功能说明:默认配置
     */
    public function defaultConfig(){

        $data = [
            [
                //用户
                'name' => 'getCoachCash',

                'balance' => 0,

                'balance_name' => 'coach_balance',

                'top' => 1,

                'obj_name' => 'coach_id'
            ]
            , [
                //用户
                'name' => 'getUserCash',

                'balance' => 0,

                'balance_name' => 'user_agent_balance',

                'top' => 1,

                'obj_name' => 'user_top_id'
            ],
            [
                //平台
                'name' => 'getCompanyCash',

                'balance' => 0,

                'balance_name' => 'company_balance',

                'top' => 2
            ],
            [
                //渠道
                'name' => 'getChannelCash',

                'balance' => 0,

                'balance_name' => 'channel_balance',

                'top' => 3,

                'obj_name' => 'channel_id'
            ],
            [
                //城市
                'name' => 'getCityCash',

                'balance' => 0,

                'balance_name' => 'city_balance',

                'top' => 4,

            ],
            [
                //区县
                'name' => 'getDistrictCash',

                'balance' => 0,

                'balance_name' => 'district_balance',

                'top' => 5
            ]

        ];

        return $data;
    }


    /**
     * @param $uniacid
     * @功能说明:初始化数据
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-30 11:28
     */
    public function initData($uniacid){

        $key = 'DistributionConfig_key';

        incCache($key,1,$uniacid,99);

        $value = getCache($key,$uniacid);

        if($value==1){

            $list = $this->defaultConfig();

            foreach ($list as $v){

                $dis = [

                    'uniacid' => $uniacid,

                    'name'    => $v['name']
                ];

                $find = $this->where($dis)->find();

                if(empty($find)){

                    $v['uniacid'] = $uniacid;

                    $this->insert($v);

                }
            }
        }

        decCache($key,1,$uniacid);

        return true;

    }






}