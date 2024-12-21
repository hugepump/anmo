<?php
/**
 * Created by PhpStorm
 * User: shurong
 * Date: 2024/7/16
 * Time: 17:28
 * docs:
 */

namespace app\massage\model;

use app\BaseModel;

class ServiceGuaranteeConnect extends BaseModel
{
    protected $name = 'massage_service_guarantee_connect';

    /**
     * @Desc: 详情
     * @param $service_id
     * @return mixed
     * @Auther: shurong
     * @Time: 2024/7/16 17:31
     */
    public function guaranteeInfo($service_id)
    {

        $dis = [

            'a.service_id' => $service_id,

            'b.status' => 1
        ];

        $data = $this->alias('a')
            ->join('massage_service_guarantee_list b', 'a.guarantee_id = b.id')
            ->where($dis)
            ->field('b.id,b.title,b.sub_title')
            ->group('b.id')
            ->order('b.top desc,b.id desc')
            ->select()
            ->toArray();

        return $data;
    }

}