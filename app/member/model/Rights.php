<?php
namespace app\member\model;

use app\BaseModel;
use think\facade\Db;

class Rights extends BaseModel
{
    //定义表名
    protected $name = 'massage_member_rights';


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-07 17:59
     * @功能说明:初始化系统权益
     */
    public function initData($uniacid){

        $arr=[
            'send_coupon'=>[

                'title' => '会员送券',

                'show_title' => '尊享优券',

                'icon' => 'https://' . $_SERVER['HTTP_HOST'] . '/admin/anmo/member/kaquan.png'
            ],
            'appoint_service'=>[

                'title' => '指定服务购买',

                'show_title' => '专属服务',

                'icon' => 'https://' . $_SERVER['HTTP_HOST'] . '/admin/anmo/member/fuwu.png'

            ],
            'customer_service'=>[

                'title' => '专属客服',

                'show_title' => '专属客服',

                'icon' => 'https://' . $_SERVER['HTTP_HOST'] . '/admin/anmo/member/kefu.png'
            ],
            'first_refund' =>[

                'title' => '会员优先退款',

                'show_title' => '极速退款',

                'icon' => 'https://' . $_SERVER['HTTP_HOST'] . '/admin/anmo/member/tuikuan.png'
            ]

        ];

        $key = 'member_init_rights';

        incCache($key,1,$uniacid);

        if(getCache($key,$uniacid)==1){

            foreach ($arr as $key=> $value){

                $dis = [

                    'uniacid' => $uniacid,

                    'key'     => $key
                ];

                $find = $this->dataInfo($dis);

                if(empty($find)){

                    $insert = [

                        'uniacid'=> $uniacid,

                        'key'    => $key,

                        'title'  => $value['title'],

                        'show_title'  => $value['show_title'],

                        'rights_icon'  => $value['icon'],

                        'is_admin' => 1
                    ];

                    $this->dataAdd($insert);
                }
            }
        }

        decCache($key,1,$uniacid);

        return true;
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








}