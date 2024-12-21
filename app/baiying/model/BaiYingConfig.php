<?php
/**
 * Created by PhpStorm
 * User: shurong
 * Date: 2024/8/8
 * Time: 14:08
 * docs:
 */

namespace app\baiying\model;

use app\BaseModel;

class BaiYingConfig extends BaseModel
{
    protected $name = 'massage_bai_ying_config';

    /**
     * @Desc: 详情
     * @param $where
     * @return BaiYingConfig|array|mixed|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @Auther: shurong
     * @Time: 2024/8/8 14:15
     */
    public static function getInfo($where)
    {
        $info = self::where($where)->find();

        if (empty($info)) {

            self::insert($where);

            $info = self::where($where)->find();
        }
        return $info;
    }

}