<?php
/**
 * Created by PhpStorm
 * User: shurong(龙兵科技)
 * Date: 2024/10/29
 * Time: 16:39
 * docs:
 */

namespace app\partner\model;

use app\BaseModel;
use app\massage\model\BalanceWater;
use think\facade\Db;

//status 状态-1取消 1待支付 2待审核 3驳回 4同意
class PartnerOrderJoin extends BaseModel
{
    protected $name = 'massage_partner_order_join';

    public static function getCount($where)
    {
        return self::where($where)->count();
    }

    public static function add($insert)
    {
        $insert['create_time'] = time();

        return self::insertGetId($insert);
    }

    public static function getInfo($where)
    {
        return self::where($where)->find();
    }

    public static function edit($where, $update)
    {
        return self::where($where)->update($update);
    }

    public static function notify($result)
    {
        $order = self::getInfo(['order_code' => $result['order_code']]);

        if ($order['status'] != 1 || empty($order) || empty($result['transaction_id'])) {

            return false;
        }

        $result['money'] = isset($result['money']) ? $result['money'] : $order['join_price'];

        $update = [
            'status' => $order['is_check'] == 1 ? 2 : 4,
            'pay_price' => $result['money'],
            'pay_time' => time(),
            'transaction_id' => $result['transaction_id'],
        ];

        Db::startTrans();

        $res = self::edit(['order_code' => $result['order_code']], $update);

        if (!$res) {

            Db::rollback();

            return false;
        }

        if ($order['pay_model'] == 2 && $result['money'] > 0) {

            $water_model = new BalanceWater();

            $order['pay_price'] = $order['join_price'];

            $res = $water_model->updateUserBalance($order, 8);

            if ($res == 0) {

                Db::rollback();

                return false;
            }
        }

        Db::commit();

        return true;
    }

    /**
     * @Desc: 回调
     * @param $order_code
     * @param $transaction_id
     * @return bool
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/11/20 18:46
     */
    public function orderResult($order_code, $transaction_id)
    {
        $order = self::getInfo(['order_code' => $order_code]);

        if ($order['status'] != 1 || empty($order) || empty($transaction_id)) {

            return false;
        }

        $update = [
            'status' => $order['is_check'] == 1 ? 2 : 4,
            'pay_price' => $order['join_price'],
            'pay_time' => time(),
            'transaction_id' => $transaction_id,
        ];

        Db::startTrans();

        $res = self::edit(['order_code' => $order_code], $update);

        if (!$res) {

            Db::rollback();

            return false;
        }

        if ($order['pay_model'] == 2 && $order['join_price'] > 0) {

            $water_model = new BalanceWater();

            $order['pay_price'] = $order['join_price'];

            $res = $water_model->updateUserBalance($order, 8);

            if ($res == 0) {

                Db::rollback();

                return false;
            }
        }

        Db::commit();

        return true;
    }

    /**
     * @Desc: 加入列表
     * @param $where
     * @return mixed
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/29 18:09
     */
    public static function getOrderJoin($where)
    {
        $data = self::alias('a')
            ->where($where)
            ->field('a.id,a.is_create,a.status,a.phone,a.text,a.field,a.is_sign,b.nickName,b.avatarUrl,a.user_id,a.is_sign')
            ->leftJoin('massage_service_user_list b', 'a.user_id = b.id')
            ->order('a.is_create desc,a.create_time desc')
            ->select()
            ->toArray();

        if ($data) {

            foreach ($data as &$item) {

                $item['field'] = empty($item['field']) ? '' : json_decode($item['field'], true);
            }
        }

        return $data;
    }

    /**
     * @Desc: 取消订单
     * @param $uniacid
     * @param $user_id
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/30 10:18
     */
    public static function cancel($uniacid, $user_id = 0)
    {

        $key = 'cancel_order_join' . $uniacid;

        incCache($key, 1, $uniacid);

        $cache = getCache($key, $uniacid);

        if ($cache != 1) {

            decCache($key, 1, $uniacid);

            return false;
        }

        //取消未支付订单
        $where = [
            ['uniacid', '=', $uniacid],
            ['is_create', '=', 0],
            ['status', '=', 1]
        ];

        if ($user_id) {

            $where[] = ['user_id', '=', $user_id];
        } else {

            $where[] = ['over_time', '<', time()];
        }

        $data = self::where($where)->select()->toArray();

        if ($data) {

            foreach ($data as $datum) {

                self::cancelHandel($datum);
            }
        }

        //取消超时后台未审核订单
        PartnerOrder::cancelOrder($uniacid);

        //活动经费到账
        PartnerOrder::userBalance($uniacid);

        decCache($key, 1, $uniacid);

        return true;
    }

    public static function cancelHandel($datum)
    {
        Db::startTrans();

        $res = self::where('id', $datum['id'])->update(['status' => -1]);

        if (!$res) {

            Db::rollback();

            return false;
        }

        $res = PartnerOrder::edit(['id' => $datum['order_id']], ['join_num' => Db::raw('join_num-1')]);

        if (!$res) {

            Db::rollback();

            return false;
        }

        Db::commit();

        return true;
    }

    /**
     * @Desc: 我的组局记录
     * @param $where
     * @param $limit
     * @return mixed
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/30 11:45
     */
    public static function getMyPartner($where, $limit = 10)
    {

        return self::alias('a')
            ->where($where)
            ->field('a.id as join_id,b.id,a.is_create,b.cover,b.start_time,b.title,b.address,b.sign_num,b.join_num,b.join_price,b.money_type,b.status,b.partner_check_text,a.status as join_status,b.end_time,b.sign_start_time,a.is_sign,b.address_info')
            ->leftJoin('massage_partner_order b', 'a.order_id=b.id')
            ->order('b.start_time desc')
            ->paginate($limit)
            ->toArray();
    }

    /***
     * @Desc: 报名审核
     * @param $data
     * @return array|true
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/30 15:26
     */
    public static function joinCheck($data)
    {
        $order = self::getInfo(['id' => $data['id']]);

        $update = [
            'status' => $data['status']
        ];

        Db::startTrans();
        try {

            self::edit(['id' => $data['id']], $update);

            if ($data['status'] == 3) {

                PartnerOrder::edit(['id' => $order['order_id']], ['join_num' => Db::raw('join_num-1')]);

                if ($order['pay_price'] > 0) {
                    $code = PartnerOrder::refund($order, 10);

                    if (isset($code['code'])) {

                        throw new \Exception($code['msg']);
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
}