<?php
/**
 * Created by PhpStorm
 * User: shurong
 * Date: 2024/7/12
 * Time: 17:53
 * docs:
 */

namespace app\massage\model;

use app\BaseModel;

class ServiceGuarantee extends BaseModel
{
    protected $name = 'massage_service_guarantee_list';

    /**
     * @Desc: 列表分页
     * @param $where
     * @param $limit
     * @return array
     * @throws \think\db\exception\DbException
     * @Auther: shurong
     * @Time: 2024/7/12 17:57
     */
    public static function getList($where, $limit = 10)
    {
        return self::where($where)
            ->order('top desc,id desc')
            ->paginate($limit)
            ->toArray();
    }

    /**
     * @Desc: 插入
     * @param $data
     * @return int|string
     * @Auther: shurong
     * @Time: 2024/7/12 18:02
     */
    public static function add($data)
    {
        $data['create_time'] = $data['update_time'] = time();

        return self::insert($data);
    }

    /**
     * @Desc: 编辑
     * @param $where
     * @param $update
     * @return ServiceGuarantee
     * @Auther: shurong
     * @Time: 2024/7/12 18:06
     */
    public static function edit($where, $update)
    {
        return self::where($where)->update($update);
    }

    /**
     * @Desc: 列表-不分页
     * @param $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @Auther: shurong
     * @Time: 2024/7/12 18:09
     */
    public static function getListNoPae($where)
    {
        return self::where($where)
            ->field('id,title,sub_title')
            ->order('top desc,id desc')
            ->order('top desc,id desc')
            ->select()
            ->toArray();
    }
}