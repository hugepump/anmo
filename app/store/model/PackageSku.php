<?php
/**
 * Created by PhpStorm
 * User: shurong
 * Date: 2023/11/22
 * Time: 17:50
 * docs:
 */

namespace app\store\model;

use app\BaseModel;
use think\facade\Db;

class PackageSku extends BaseModel
{
    protected $name = 'massage_store_package_sku';

    /**
     * @Desc: 插入套餐规格
     * @param $sku
     * @param $pack_id
     * @param $uniacid
     * @return array|true
     * @Auther: shurong
     * @Time: 2023/11/22 18:11
     */
    public static function updateSku($sku, $pack_id, $uniacid)
    {
        Db::startTrans();
        try {
            if (self::where('package_id', $pack_id)->count() > 0) {

                self::where('package_id', $pack_id)->delete();
            }
            if (SkuPrice::where('package_id', $pack_id)->count() > 0) {

                SkuPrice::where('package_id', $pack_id)->delete();
            }
            if (!empty($sku)) {
                foreach ($sku as $item) {
                    $insert = [
                        'uniacid' => $uniacid,
                        'name' => $item['name'],
                        'package_id' => $pack_id,
                        'create_time' => time()
                    ];

                    $id = self::insertGetId($insert);

                    if (!$id) {
                        throw new \Exception('规格添加失败');
                    }

                    $price = $item['price'] ?? [];

                    if (!empty($price)) {

                        $res = SkuPrice::updatePrice($price, $pack_id, $id, $uniacid);

                        if (isset($res['code'])) {

                            throw new \Exception($res['msg']);
                        }
                    }
                }
            }
            Db::commit();
        } catch (\Exception $exception) {

            Db::rollback();
            return ['code' => 1, 'msg' => $exception->getMessage()];
        }
        return true;
    }

    /**
     * @Desc: 获取列表
     * @param $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @Auther: shurong
     * @Time: 2023/11/23 10:17
     */
    public static function getList($where)
    {
        return self::where($where)->select()->toArray();
    }
}