<?php
/**
 * Created by PhpStorm
 * User: shurong(龙兵科技)
 * Date: 2024/10/28
 * Time: 10:48
 * docs:
 */

namespace app\partner\model;

use app\BaseModel;
use app\massage\model\BalanceWater;
use app\massage\model\User;
use longbingcore\wxcore\PayModel;
use think\facade\Db;

//status 1待支付 2待审核 3拒绝 4通过

class PartnerOrder extends BaseModel
{
    protected $name = 'massage_partner_order';

    protected $append = [
        'join_list'
    ];

    public function getJoinListAttr($value, $data)
    {
        $where = [
            ['a.order_id', '=', $data['id']],
            ['a.status', 'in', [2, 4]]
        ];
        return PartnerOrderJoin::getOrderJoin($where);
    }

    public static function add($input)
    {
        $input['create_time'] = $input['update_time'] = time();

        return self::insertGetId($input);
    }

    public static function edit($where, $update)
    {
        return self::where($where)->update($update);
    }

    public static function getInfo($where)
    {
        return self::where($where)->find();
    }

    /**
     * @Desc: 回调
     * @param $result
     * @return bool
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/29 15:24
     */
    public static function notify($result)
    {
        $order = self::getInfo(['order_code' => $result['order_code']]);

        if ($order['status'] != 1 || empty($order) || empty($result['transaction_id'])) {

            return false;
        }

        $result['money'] = isset($result['money']) ? $result['money'] : $order['partner_money'];

        $partner_check_type = getConfigSetting($order['uniacid'], 'partner_check_type');

        $update = [
            'status' => $partner_check_type == 2 ? 4 : 2,
            'pay_price' => $result['money'],
            'pay_time' => time(),
            'transaction_id' => $result['transaction_id'],
            'partner_check_type' => $partner_check_type
        ];

        Db::startTrans();

        $code = orderCode();

        $join = [
            'uniacid' => $order['uniacid'],
            'order_id' => $order['id'],
            'user_id' => $order['user_id'],
            'is_create' => 1,
            'order_code' => $code,
            'status' => 4,
            'phone' => User::where('id', $order['user_id'])->value('phone'),
            'transaction_id' => $code,
            'pay_time' => time(),
            'pay_model' => $order['pay_model']
        ];

        $res = PartnerOrderJoin::add($join);

        if (!$res) {

            Db::rollback();

            return false;
        }

        $res = self::edit(['order_code' => $result['order_code']], $update);

        if (!$res) {

            Db::rollback();

            return false;
        }

        if ($order['pay_model'] == 2 && $result['money'] > 0) {

            $water_model = new BalanceWater();

            $order['pay_price'] = $order['partner_money'];

            $res = $water_model->updateUserBalance($order, 7);

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
     * @Time: 2024/11/20 18:45
     */
    public function orderResult($order_code, $transaction_id)
    {
        $order = self::getInfo(['order_code' => $order_code]);

        if ($order['status'] != 1 || empty($order) || empty($transaction_id)) {

            return false;
        }

        $partner_check_type = getConfigSetting($order['uniacid'], 'partner_check_type');

        $update = [
            'status' => $partner_check_type == 2 ? 4 : 2,
            'pay_price' => $order['partner_money'],
            'pay_time' => time(),
            'transaction_id' => $transaction_id,
            'partner_check_type' => $partner_check_type
        ];

        Db::startTrans();

        $code = orderCode();

        $join = [
            'uniacid' => $order['uniacid'],
            'order_id' => $order['id'],
            'user_id' => $order['user_id'],
            'is_create' => 1,
            'order_code' => $code,
            'status' => 4,
            'phone' => User::where('id', $order['user_id'])->value('phone'),
            'transaction_id' => $code,
            'pay_time' => time()
        ];

        $res = PartnerOrderJoin::add($join);

        if (!$res) {

            Db::rollback();

            return false;
        }

        $res = self::edit(['order_code' => $order_code], $update);

        if (!$res) {

            Db::rollback();

            return false;
        }

        if ($order['pay_model'] == 2 && $order['partner_money'] > 0) {

            $water_model = new BalanceWater();

            $order['pay_price'] = $order['partner_money'];

            $res = $water_model->updateUserBalance($order, 7);

            if ($res == 0) {

                Db::rollback();

                return false;
            }
        }

        Db::commit();

        return true;
    }

    /**
     * @Desc: 前台列表
     * @param $where
     * @param $alh
     * @param $limit
     * @return mixed
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/29 10:10
     */
    public static function getIndexList($where, $alh, $limit)
    {
        $data = self::alias('a')
            ->where($where)
            ->field(['a.id,a.user_id,a.title,a.cover,a.start_time,a.join_price,a.address,a.money_type,a.join_num,a.sign_num,a.address_info', $alh, 'b.name as type_name'])
            ->leftJoin('massage_partner_type b', 'a.type_id=b.id')
            ->order('distance asc')
            ->paginate($limit)
            ->toArray();

        foreach ($data['data'] as &$datum) {

            $datum['join_price'] += 0;
        }

        return $data;
    }

    /***
     * @Desc: 后台列表
     * @param $where
     * @param $limit
     * @return mixed
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/29 10:10
     */
    public static function getAdminList($where, $limit = 10)
    {
        $data = self::alias('a')
            ->where($where)
            ->field(['a.id,a.title,a.cover,a.create_time,a.type_pid,a.type_id,a.user_id,a.money_type,a.partner_money,a.status,a.is_cancel', 'b.name as type_pname,c.name as type_name,d.nickname as create_name,a.content'])
            ->leftJoin('massage_partner_type b', 'a.type_pid=b.id')
            ->leftJoin('massage_partner_type c', 'a.type_id=c.id')
            ->leftJoin('massage_service_user_list d', 'a.user_id=d.id')
            ->orderRaw("(CASE 
                        WHEN a.status = 2 THEN 0 
                        ELSE 1 
                    END) ASC, 
                    a.create_time desc")
            ->paginate($limit)
            ->toArray();

        if ($data['data']) {

            foreach ($data['data'] as &$item) {

                $item['content'] = json_decode($item['content'], true);
            }
        }

        return $data;
    }

    public static function getCount($where)
    {
        return self::where($where)->count();
    }

    /**
     * @Desc: 详情
     * @param $where
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/29 15:31
     */
    public static function getDetail($where)
    {
        $data = self::where($where)->field('id,user_id,order_code,status,type_id,title,cover,img,content,start_time,end_time,sign_start_time,join_end_time,address,lat,lng,sign_num,limit_age,min_age,max_age,limit_sex,money_type,join_price,join_num,is_cancel,show_user_info,sign_start_minute,address_info,partner_check_text,phone')->find();

        $data['img'] = explode(',', $data['img']);

        $data['content'] = json_decode($data['content'], true);

        $data['field'] = PartnerOrderField::getListByOrderId($data['id']);

        $data['type_name'] = PartnerType::where('id', $data['type_id'])->value('name');

        $data['join_price'] = $data['join_price'] + 0;

        return $data;
    }

    /**
     * @Desc: 订单审核
     * @param $input
     * @return array|true
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/30 16:17
     */
    public static function partnerCheck($input)
    {
        $order = self::getInfo(['id' => $input['id']]);

        Db::startTrans();
        try {
            self::edit(['id' => $input['id']], $input);

            if ($input['status'] == 3) {

                if ($order['pay_price'] > 0) {

                    $code = self::refund($order, 9);

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

    /**
     * @Desc: 退款
     * @param $order
     * @param $type
     * @return array|true
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/30 16:43
     */
    public static function refund($order, $type)
    {

        $payConfig = payConfig($order['uniacid'], $order['app_pay']);

        //微信
        if ($order['pay_model'] == 1) {

            $response = orderRefundApi($payConfig, $order['pay_price'], $order['pay_price'], $order['transaction_id']);

            //如果退款成功修改一下状态
            if (isset($response['return_code']) && isset($response['result_code']) && $response['return_code'] == 'SUCCESS' && $response['result_code'] == 'SUCCESS') {

                $out_refund_no = !empty($response['out_refund_no']) ? $response['out_refund_no'] : $order['order_code'];
            } else {
                return ['code' => 1, 'msg' => empty($response['err_code_des']) ? $response['err_code_des'] : $response['return_msg']];
            }
        } elseif ($order['pay_model'] == 3) {
            //支付宝
            $pay_model = new PayModel($payConfig);

            $res = $pay_model->aliRefund($order['transaction_id'], $order['pay_price']);

            if (isset($res['alipay_trade_refund_response']['code']) && $res['alipay_trade_refund_response']['code'] == 10000) {

                $out_refund_no = $res['alipay_trade_refund_response']['out_trade_no'];
            } else {

                return ['code' => 1, 'msg' => $res['alipay_trade_refund_response']['sub_msg']];
            }

        } else {

            $water_model = new BalanceWater();

            $water_model->updateUserBalance($order, $type, 1);

            $out_refund_no = $order['order_code'];
        }

        if ($type == 9) {

            PartnerOrder::edit(['id' => $order['id']], ['out_refund_no' => $out_refund_no]);
        } else {

            PartnerOrderJoin::edit(['id' => $order['id']], ['out_refund_no' => $out_refund_no]);
        }

        return true;
    }

    /**
     * @Desc: 取消
     * @param $join
     * @return array|true
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/30 17:33
     */
    public static function cancelPartner($join)
    {
        Db::startTrans();

        try {
            //创建
            if ($join['is_create'] == 1) {

                $order = self::getInfo(['id' => $join['order_id']]);

                self::edit(['id' => $join['order_id']], ['is_cancel' => 1]);

                if ($order['pay_price'] > 0) {

                    $code = self::refund($order, 9);

                    if (isset($code['code'])) {

                        throw new \Exception($code['msg']);
                    }
                }

                if ($order['join_price'] > 0) {

                    $join_list = PartnerOrderJoin::where([['order_id', '=', $join['order_id']], ['is_create', '=', 0], ['status', 'in', [2, 4]]])
                        ->select()
                        ->toArray();

                    if ($join_list) {

                        foreach ($join_list as $item) {

                            if ($item['pay_price'] > 0) {

                                $code = PartnerOrder::refund($item, 10);

                                if (isset($code['code'])) {

                                    throw new \Exception($code['msg']);
                                }
                            }
                        }
                    }
                }
                PartnerOrderJoin::edit(['order_id' => $join['order_id']], ['status' => -1]);

            } else {
                PartnerOrderJoin::edit(['id' => $join['id']], ['status' => -1]);

                PartnerOrder::edit(['id' => $join['order_id']], ['join_num' => Db::raw('join_num-1')]);

                if ($join['pay_price'] > 0) {
                    $code = PartnerOrder::refund($join, 10);

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

    /**
     * @Desc: 取消超时未审核订单
     * @param $uniacid
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/11/18 14:10
     */
    public static function cancelOrder($uniacid)
    {
        $where = [
            ['status', '=', 2],
            ['start_time', '<', time()],
            ['uniacid', '=', $uniacid],
            ['is_cancel', '=', 0]
        ];

        $data = self::where($where)->select()->toArray();

        if ($data) {

            foreach ($data as $order) {

                if ($order['pay_price'] > 0) {

                    $code = PartnerOrder::refund($order, 9);

                    if (isset($code['code'])) {

                        return false;
                    }

                    self::edit(['id' => $order['id']], ['is_cancel' => 1]);
                }
            }
        }

        return true;
    }

    /**
     * @Desc: 获取冻结金额
     * @param $uniacid
     * @param $user_id
     * @return float
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/30 18:36
     */
    public static function getWaitPrice($uniacid, $user_id)
    {
        $where = [
            ['uniacid', '=', $uniacid],
            ['user_id', '=', $user_id],
            ['end_time', '>=', time()],
            ['status', '=', 4],
            ['is_cancel', '=', 0]
        ];

        $order_ids = self::where($where)->column('id');

        $data = PartnerOrderJoin::where([['order_id', 'in', $order_ids], ['status', '=', 4]])->sum('pay_price');

        return round($data, 2);
    }

    /**
     * @Desc: 获取流水
     * @param $uniacid
     * @param $user_id
     * @return mixed
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/31 11:01
     */
    public static function getWater($uniacid, $user_id, $limit = 10)
    {
        $where = [
            ['uniacid', '=', $uniacid],
            ['user_id', '=', $user_id],
            ['status', '=', 4],
            ['is_cancel', '=', 0]
        ];

        $order_ids = self::where($where)->column('id');

        return PartnerOrderJoin::alias('a')
            ->where([['a.order_id', 'in', $order_ids], ['a.status', '=', 4], ['a.pay_price', '>', 0]])
            ->field('a.order_id,b.nickName,b.avatarUrl,c.title,c.end_time,a.pay_price')
            ->leftJoin('massage_service_user_list b', 'a.user_id = b.id')
            ->leftJoin('massage_partner_order c', 'a.order_id = c.id')
            ->order('a.create_time desc')
            ->paginate($limit)
            ->toArray();
    }

    /**
     * @Desc: 活动经费到账
     * @param $uniacid
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/31 14:39
     */
    public static function userBalance($uniacid)
    {
        $key = 'partner_user_balance_' . $uniacid;

        incCache($key, 1, $uniacid);

        $value = getCache($key, $uniacid);

        if ($value != 1) {

            decCache($key, 1, $uniacid);

            return false;
        }

        $where = [
            ['uniacid', '=', $uniacid],
            ['end_time', '<=', time()],
            ['status', '=', 4],
            ['is_cancel', '=', 0],
            ['is_hx', '=', 0]
        ];

        Db::startTrans();

        $data = self::where($where)->select()->toArray();

        if ($data) {

            foreach ($data as $datum) {

                $res = self::edit(['id' => $datum['id'], 'is_hx' => 0], ['is_hx' => 1]);

                if (!$res) {

                    Db::rollback();

                    decCache($key, 1, $uniacid);

                    return false;
                }

                $join_list = PartnerOrderJoin::where([['order_id', 'in', $datum['id']], ['status', '=', 4], ['is_hx', '=', 0]])->field('id,pay_price')->select()->toArray();

                $money = array_sum(array_column($join_list, 'pay_price'));

                $ids = array_column($join_list, 'id');

                if ((float)$money > 0) {

                    $res = User::where('id', $datum['user_id'])->update(['total_partner_money' => Db::raw("total_partner_money+$money"), 'partner_money' => Db::raw("partner_money+$money")]);

                    if (!$res) {

                        Db::rollback();

                        decCache($key, 1, $uniacid);

                        return false;
                    }
                }

                $res = PartnerOrderJoin::where('id', 'in', $ids)->update(['is_hx' => 1]);

                if (!$res) {

                    Db::rollback();

                    decCache($key, 1, $uniacid);

                    return false;
                }
                //活动结束，还有未审核的加入 则要退款
                $wait = PartnerOrderJoin::where([['order_id', 'in', $datum['id']], ['status', '=', 2]])->select()->toArray();

                if ($wait) {

                    foreach ($wait as $item) {

                        PartnerOrderJoin::edit(['id' => $item['id']], ['status' => -1]);

                        PartnerOrder::edit(['id' => $datum['id']], ['join_num' => Db::raw('join_num-1')]);

                        if ($item['pay_price'] > 0) {

                            $code = self::refund($item, 10);

                            if (isset($code['code'])) {

                                Db::rollback();

                                decCache($key, 1, $uniacid);

                                return false;
                            }
                        }
                    }
                }
            }
        }

        Db::commit();

        decCache($key, 1, $uniacid);

        return true;
    }

    /**
     * @Desc: 费用列表
     * @param $where
     * @param $limit
     * @return mixed
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/31 17:41
     */
    public static function getMoneyList($where, $limit = 10)
    {
        return self::alias('a')
            ->field('a.id,a.title,a.pay_price,a.status,a.order_code,a.transaction_id,a.pay_model,a.out_refund_no,a.pay_time,a.is_cancel,a.end_time,b.nickName,b.avatarUrl')
            ->where($where)
            ->leftJoin('massage_service_user_list b', 'a.user_id=b.id')
            ->order('a.pay_time desc')
            ->paginate($limit)
            ->toArray();
    }
}