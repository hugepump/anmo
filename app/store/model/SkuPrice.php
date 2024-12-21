<?php
/**
 * Created by PhpStorm
 * User: shurong
 * Date: 2023/11/22
 * Time: 17:51
 * docs:
 */

namespace app\store\model;

use app\BaseModel;
use think\facade\Db;

class SkuPrice extends BaseModel
{
    protected $name = 'massage_store_package_sku_price';

    /**
     * @Desc: 插入规格价格
     * @param $price
     * @param $pack_id
     * @param $sku_id
     * @param $uniacid
     * @return array|true
     * @Auther: shurong
     * @Time: 2023/11/22 18:09
     */
    public static function updatePrice($price, $pack_id, $sku_id, $uniacid)
    {
        Db::startTrans();
        try {

            $insert = [];
            if (!empty($price)) {

                foreach ($price as $item) {

                    $insert[] = [
                        'uniacid' => $uniacid,
                        'name' => $item['name'],
                        'package_id' => $pack_id,
                        'sku_id' => $sku_id,
                        'num' => $item['num'],
                        'price' => $item['price'],
                        'create_time' => time()
                    ];
                }
            }

            if (!empty($insert)) {
                $res = self::insertAll($insert);
                if (!$res) {

                    throw new \Exception('规格价格添加失败');
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