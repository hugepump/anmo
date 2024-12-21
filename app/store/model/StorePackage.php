<?php
/**
 * Created by PhpStorm
 * User: shurong(龙兵科技)
 * Date: 2024/10/24
 * Time: 10:38
 * docs:
 */

namespace app\store\model;

use app\BaseModel;
use app\massage\model\Config;
use think\facade\Db;

class StorePackage extends BaseModel
{
    protected $name = 'massage_store_package_list';

    public function getIntroduceTextAttr($value)
    {

        return empty($value) ? '' : json_decode($value, true);
    }


    public static function getFirstValue($where, $field = 'true_sale')
    {
        return self::where($where)->value($field);
    }

    /**
     * @Desc: 添加套餐
     * @param $data
     * @return array|true
     * @Auther: shurong
     * @Time: 2023/11/22 18:12
     */
    public static function add($data)
    {
        if (!empty($data['sku'])) {

            $sku = $data['sku'];

            unset($data['sku']);
        }

        $data['create_time'] = $data['update_time'] = time();

        $uniacid = $data['uniacid'];

        Db::startTrans();
        try {

            $id = self::insertGetId($data);

            $res = PackageSku::updateSku($sku, $id, $uniacid);

            if (isset($res['code'])) {

                throw new \Exception($res['msg']);
            }

            Db::commit();
        } catch (\Exception $exception) {

            Db::rollback();
            return ['code' => 1, 'msg' => $exception->getMessage()];
        }
        return true;

    }

    /**
     * @Desc: 编辑套餐
     * @param $data
     * @return array|true
     * @Auther: shurong
     * @Time: 2023/11/22 18:47
     */
    public static function edit($data)
    {
        if (!empty($data['sku'])) {

            $sku = $data['sku'];

            unset($data['sku']);
        }

        $uniacid = $data['uniacid'];

        Db::startTrans();
        try {

            $res = self::update($data, ['id' => $data['id']]);

            if ($res === false) {

                throw new \Exception('编辑失败');
            }

            $res = PackageSku::updateSku($sku, $data['id'], $uniacid);

            if (isset($res['code'])) {

                throw new \Exception($res['msg']);
            }

            Db::commit();
        } catch (\Exception $exception) {

            Db::rollback();
            return ['code' => 1, 'msg' => $exception->getMessage()];
        }
        return true;
    }

    /**
     * @Desc: 获取套餐详情
     * @param $id
     * @return StorePackage|array|mixed|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @Auther: shurong
     * @Time: 2023/11/23 10:23
     */
    public static function getInfo($id)
    {
        $data = self::where('id', $id)->find();

        $sku = PackageSku::getList(['package_id' => $id]);

        $price = SkuPrice::getList(['package_id' => $id]);

        $data['store'] = StoreList::where('id', $data['store_id'])->field('id,title as name,start_time,end_time')->find();

        $arr = [];
        foreach ($sku as $key => $item) {

            $arr[$key] = [
                'name' => $item['name']
            ];
            foreach ($price as $value) {

                if ($item['id'] == $value['sku_id']) {

                    $arr[$key]['price'][] = [
                        'name' => $value['name'],
                        'num' => $value['num'],
                        'price' => $value['price']
                    ];
                }
            }
        }

        $data['sku'] = $arr;

        return $data;
    }

    /**
     * @Desc: 套餐列表
     * @param $where
     * @param $limit
     * @return mixed
     * @Auther: shurong
     * @Time: 2023/11/23 10:51
     */
    public static function getList($where, $limit)
    {
        return self::alias('a')
            ->where($where)
            ->field('a.id,a.cover,a.name,a.price,a.init_price,a.true_sale,a.total_sale,b.title as store_name,a.status,a.create_time')
            ->leftJoin('massage_store_list b', 'a.store_id=b.id')
            ->order('a.id desc')
            ->paginate($limit)
            ->toArray();
    }

    /**
     * @Desc: 前端套餐列表
     * @param $where
     * @param $limit
     * @return array
     * @throws \think\db\exception\DbException
     * @Auther: shurong
     * @Time: 2023/11/23 15:07
     */
    public static function getIndexList($where, $limit)
    {
        $data = self::alias('a')
            ->where($where)
            ->field('id,cover,name,sub_name,price,init_price,total_sale')
            ->order('id desc')
            ->paginate($limit)
            ->toArray();
        if (!empty($data['data'])) {

            foreach ($data['data'] as &$item) {

                $item['sale'] = $item['total_sale'] > 10 ? (int)($item['total_sale'] / 10) * 10 : $item['total_sale'];

                $item['discount'] = 0;

                if ($item['price'] < $item['init_price']) {

                    $item['discount'] = round(($item['price'] / $item['init_price']) * 10, 1);
                }
            }
        }

        return $data;
    }

    /**
     * @Desc: 过期自动下架
     * @param $uniacid
     * @return bool
     * @Auther: shurong
     * @Time: 2023/11/23 17:22
     */
    public static function cancel($uniacid)
    {

        $key = 'package_cancel_' . $uniacid;

        incCache($key, 1, $uniacid);

        $value = getCache($key, $uniacid);

        if ($value != 1) {

            decCache($key, 1, $uniacid);

            return false;
        }

        $where = [
            ['uniacid', '=', $uniacid],
            ['term_type', '=', 1],
            ['term_end_time', '<', time()],
            ['status', '=', 1]
        ];
        self::where($where)->update(['status' => 0]);

        decCache($key, 1, $uniacid);

        return true;
    }

    /**
     * @Desc: 验证是否过期
     * @param $id
     * @param $is_down 1 是否验证状态
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @Auther: shurong
     * @Time: 2023/11/23 17:27
     */
    public static function checkStatus($id, $is_down = 0)
    {
        $info = self::find($id);

        if (!$info || ($info['term_type'] == 1 && $info['term_end_time'] < time())) {

            return false;
        }
        //验证下架也返回false
        if ($is_down == 1 && $info['status'] != 1) {

            return false;
        }

        return true;
    }

    /**
     * @Desc: 支付信息
     * @param $data
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @Auther: shurong
     * @Time: 2023/11/24 11:13
     */
    public static function payInfo($data)
    {
        $goods = self::getInfo($data['package_id']);

        if (!empty($goods)) {

            $goods = $goods->toArray();
        }

        $over_time = Config::where('uniacid', $data['uniacid'])->value('over_time');

        if ($goods['term_type'] == 1) {

            $data['start_time'] = $goods['term_start_time'];

            $data['end_time'] = $goods['term_end_time'];
        } else {
            $data['start_time'] = time();

            $data['end_time'] = strtotime('+ ' . $goods['days'] . 'days');
        }

        $data['over_time'] = time() + $over_time * 60;

        $data = array_merge($data, $goods);

        return $data;
    }

    /**
     * @Desc: 修改套餐销售数量
     * @param $package_id
     * @param $num
     * @param $type
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @Auther: shurong
     * @Time: 2023/11/29 10:54
     */
    public static function updateSale($package_id, $num, $type = 1)
    {

        $package = self::where('id', $package_id)->find();

        if ($type == 1) {

            $update = [

                'true_sale' => $package['true_sale'] + $num,

                'total_sale' => $package['total_sale'] + $num,

                'lock' => $package['lock'] + 1,

            ];
        } else {

            $update = [
                'true_sale' => ($package['true_sale'] - $num) < 0 ? 0 : $package['true_sale'] - $num,

                'total_sale' => ($package['total_sale'] - $num) < 0 ? 0 : $package['total_sale'] - $num,

                'lock' => $package['lock'] + 1,

            ];
        }

        //修改
        $res = self::where(['id' => $package_id, 'lock' => $package['lock']])->update($update);

        if ($res != 1) {

            return ['code' => 1, 'msg' => '提交失败'];
        }
        return ['code' => 0];
    }

    /**
     * @Desc: 下拉
     * @param $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @Auther: shurong
     * @Time: 2024/9/19/019 22:10
     */
    public static function getListNoPage($where)
    {
        return self::where($where)->field('id,cover,name,price,true_sale')->select()->toArray();
    }
}