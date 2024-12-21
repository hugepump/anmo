<?php
namespace app\massage\model;

use app\BaseModel;
use think\facade\Db;

class BtnConfig extends BaseModel
{
    //定义表名
    protected $name = 'massage_service_btn_config';




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
     * @DataTime: 2024-01-24 13:48
     * @功能说明:初始化数据
     */
    public function initData($uniacid){

        $config = longbingGetAppConfig($uniacid);

        $arr = [

            [

                'type' => 1,

                'text' => '可服务',

                'btn_color' => $config['service_btn_color'],

                'font_color'=> $config['service_font_color'],
            ],
            [

                'type' => 2,

                'text' => '服务中',

                'btn_color' => '#2A2D35',

                'font_color'=> '#FFFFFF',
            ],
            [

                'type' => 3,

                'text' => '可预约',

                'btn_color' => '#FF971E',

                'font_color'=> '#FFFFFF',
            ],
            [

                'type' => 4,

                'text' => '不可预约',

                'btn_color' => '#E82F21',

                'font_color'=> '#FFFFFF',
            ]
        ];

        $key = 'init_bnt_config_key';

        incCache($key,1,$uniacid,30);

        if(getCache($key,$uniacid)==1){

            foreach ($arr as $ks=>$vs){

                $find = $this->where(['uniacid'=>$uniacid,'type'=>$vs['type']])->find();

                if(empty($find)){

                    $vs['uniacid'] = $uniacid;

                    $this->insert($vs);
                }
            }
        }

        decCache($key,1,$uniacid);

        return true;
    }






}