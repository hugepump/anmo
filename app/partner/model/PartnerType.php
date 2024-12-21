<?php
/**
 * Created by PhpStorm
 * User: shurong(龙兵科技)
 * Date: 2024/10/25
 * Time: 11:26
 * docs:
 */

namespace app\partner\model;

use app\BaseModel;

class PartnerType extends BaseModel
{
    protected $name = 'massage_partner_type';

    public static function add($data)
    {
        $data['create_time'] = $data['update_time'] = time();

        return self::insert($data);
    }

    public static function getList($where, $limit = 10)
    {
        return self::where($where)
            ->order('top desc,create_time desc')
            ->paginate($limit)
            ->toArray();
    }

    public static function edit($where, $update)
    {
        return self::where($where)->update($update);
    }

    /**
     * @Desc: 不分页
     * @param $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/25 17:38
     */
    public static function getListNoPage($where,$field = 'id,name')
    {
        return self::where($where)->field($field)
            ->order('top desc,id desc')
            ->select()
            ->toArray();
    }

    /**
     * @Desc: 二级列表
     * @param $uniacid
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/25 17:40
     */
    public static function getIndexList($uniacid)
    {
        $where = [
            ['uniacid', '=', $uniacid],

            ['status', '=', 1],

            ['pid', '=', 0]
        ];

        return self::where($where)
            ->field('id,name')
            ->order('top desc,id desc')
            ->select()
            ->each(function ($item) {
                $item['children'] = self::where(['pid' => $item['id'], 'status' => 1])
                    ->field('id,name')
                    ->order('top desc,id desc')
                    ->select()
                    ->toArray();
            })
            ->toArray();
    }
}