<?php
/**
 * Created by PhpStorm
 * User: shurong(龙兵科技)
 * Date: 2024/10/15
 * Time: 10:53
 * docs:
 */

namespace app\fxq\model;

use app\BaseModel;
use app\fxq\info\PermissionFxq;

class FxqConfig extends BaseModel
{
    protected $name = 'massage_fxq_config';

    /**
     * @Desc: 详情
     * @param $where
     * @return FxqConfig|array|mixed|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/15 15:36
     */
    public static function getInfo($where)
    {
        $data = self::where($where)->find();

        if (empty($data)) {

            self::insert($where);

            $data = self::where($where)->find();
        }

        return $data;
    }

    public static function getStatus($uniacid, $admin_id)
    {

        $p = new PermissionFxq((int)$uniacid);

        $auth = $p->pAuth();

        if ($auth == false) {

            return 0;
        }

        $data = self::getInfo(['uniacid' => $uniacid, 'admin_id' => 0]);

        if ($data['status'] == 0) {

            return 0;
        }

        $data = self::getInfo(['uniacid' => $uniacid, 'admin_id' => $admin_id]);

        return $data['status'];

    }
}