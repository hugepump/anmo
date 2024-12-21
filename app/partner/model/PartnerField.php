<?php
/**
 * Created by PhpStorm
 * User: shurong(龙兵科技)
 * Date: 2024/10/25
 * Time: 15:12
 * docs:
 */

namespace app\partner\model;

use app\BaseModel;

class PartnerField extends BaseModel
{
    protected $name = 'massage_partner_field';

    public function getSelectAttr($value, $data)
    {
        if (!empty($data['select'])) {

            return json_decode($data['select'], true);
        }
    }

    public static function initData($uniacid)
    {
        if (self::where([['uniacid', '=', $uniacid], ['status', '>', -1]])->count() == 0) {

            $data = [
                ['uniacid' => $uniacid, 'name' => '手机号', 'type' => '1', 'top' => 100, 'select' => '', 'is_required' => 1, 'is_def' => 1],
                ['uniacid' => $uniacid, 'name' => '性别', 'type' => '2', 'top' => 99, 'select' => json_encode(["男", "女"]), 'is_required' => 1, 'is_def' => 1]
            ];

            self::insertAll($data);
        }

        return true;
    }

    public static function add($data)
    {
        $data['create_time'] = $data['update_time'] = time();

        return self::insert($data);
    }

    public static function getList($where, $limit = 10)
    {
        return self::where($where)
            ->order('is_def desc,top desc,id desc')
            ->paginate($limit)
            ->toArray();
    }

    public static function edit($where, $update)
    {
        return self::where($where)->update($update);
    }

    /**
     * @Desc: 获取列表(不分页)
     * @param $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/25 17:43
     */
    public static function getListNoPage($where, $field = 'id,name')
    {
        return self::where($where)->field($field)
            ->order('is_def desc,top desc,id desc')
            ->select()
            ->toArray();
    }
}