<?php
/**
 * Created by PhpStorm
 * User: shurong(龙兵科技)
 * Date: 2024/10/25
 * Time: 17:36
 * docs:
 */

namespace app\partner\controller;

use app\ApiRest;
use app\balancediscount\model\UserCard;
use app\massage\model\Config;
use app\massage\model\User;
use app\partner\model\PartnerField;
use app\partner\model\PartnerOrder;
use app\partner\model\PartnerOrderField;
use app\partner\model\PartnerOrderJoin;
use app\partner\model\PartnerType;
use app\store\model\StoreList;
use longbingcore\wxcore\PayModel;
use think\App;
use think\facade\Db;

class IndexPartner extends ApiRest
{
    public function __construct(App $app)
    {
        parent::__construct($app);
    }

    /**
     * @Desc: 分类列表
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/25 17:41
     */
    public function typeList()
    {

        $data = PartnerType::getIndexList($this->_uniacid);

        return $this->success($data);
    }

    /**
     * @Desc: 字段列表
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/25 17:44
     */
    public function fieldList()
    {
        $where = [
            ['uniacid', '=', $this->_uniacid],

            ['status', '=', 1],
        ];

        $data = PartnerField::getListNoPage($where, 'id,name,type,is_required');

        return $this->success($data);
    }

    /**
     * @Desc: 下单
     * @return mixed
     * @throws \WxPayException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/28 14:11
     */
    public function payOrder()
    {
        $input = request()->only(['address', 'content', 'cover', 'end_time', 'field', 'img', 'is_check', 'is_open', 'join_end_time', 'join_price', 'lat', 'limit_age', 'limit_sex', 'lng', 'max_age', 'min_age', 'money_type', 'pay_model', 'show_user_info', 'sign_num', 'sign_start_minute', 'start_time', 'store_id', 'title', 'type_id', 'type_pid', 'address_info', 'phone']);

        $field = PartnerField::where([['id', 'in', $input['field']]])
            ->field('id as field_id,name,type,select,is_required,top')
            ->select()
            ->toArray();

        $arr_field = [];
        foreach ($input['field'] as $value) {

            foreach ($field as $item) {

                if ($item['field_id'] == $value) {
                    $arr_field[] = $item;
                }
            }
        }

        $field = $arr_field;

        unset($input['field']);

        if (!empty($input['store_id'])) {

            $store_model = new StoreList();

            $store = $store_model->dataInfo(['id' => $input['store_id']]);

            $input['address'] = $store['address'];

            $input['lng'] = $store['lng'];

            $input['lat'] = $store['lat'];
        }

        $insert = [
            'uniacid' => $this->_uniacid,
            'user_id' => $this->getUserId(),
            'order_code' => orderCode(),
            'img' => implode(',', $input['img']),
            'content' => json_encode($input['content']),
            'sign_start_time' => strtotime('-' . $input['sign_start_minute'] . ' minutes', $input['start_time']),
            'partner_money' => getConfigSetting($this->_uniacid, 'partner_money'),
            'app_pay' => $this->is_app,
            'pay_model' => $input['pay_model']
        ];

        $input = array_merge($input, $insert);

        Db::startTrans();

        try {

            $order_id = PartnerOrder::add($input);

            if (empty($order_id)) {

                throw new \Exception('发布失败');
            }

            foreach ($field as &$item) {

                $item['uniacid'] = $this->_uniacid;

                $item['order_id'] = $order_id;

                $item['create_time'] = time();

                $item['select'] = empty($item['select']) ? '' : json_encode($item['select']);
            }

            $res = PartnerOrderField::insertAll($field);

            if ($res === false) {

                throw new \Exception('发布订单失败');
            }

            if ($input['pay_model'] == 2 && $input['partner_money'] > 0) {

                $user_model = new User();

                $user_balance = $user_model->where(['id' => $this->getUserId()])->value('balance');

                if ($user_balance < $input['partner_money']) {

                    throw new \Exception('余额不足');
                }
            }

            Db::commit();
        } catch (\Exception $exception) {

            Db::rollback();
            return $this->error($exception->getMessage());
        }

        if ((float)$input['partner_money'] <= 0) {

            $result = [
                'order_code' => $input['order_code'],
                'transaction_id' => $input['order_code'],
                'money' => 0
            ];

            PartnerOrder::notify($result);

            return $this->success(true);
        }

        if ($input['pay_model'] == 2) {

            $result = [
                'order_code' => $input['order_code'],
                'transaction_id' => $input['order_code'],
                'money' => $input['partner_money']
            ];

            PartnerOrder::notify($result);

            return $this->success(true);
        } elseif ($input['pay_model'] == 3) {

            $pay_model = new PayModel($this->payConfig());

            $jsApiParameters = $pay_model->aliPay($input['order_code'], $input['partner_money'], 'PartnerOrder', 8, ['openid' => $this->getUserInfo()['openid'], 'uniacid' => $this->_uniacid, 'type' => 'PartnerOrder', 'out_trade_no' => $input['order_code'], 'order_id' => (string)$order_id]);

            $arr['pay_list'] = $jsApiParameters;

            $arr['order_code'] = $input['order_code'];

            $arr['order_id'] = $order_id;

        } else {
            //微信支付
            $pay_controller = new \app\shop\controller\IndexWxPay($this->app);
            //支付
            $jsApiParameters = $pay_controller->createWeixinPay($this->payConfig(), $this->getUserInfo()['openid'], $this->_uniacid, "消费", ['type' => 'PartnerOrder', 'out_trade_no' => $input['order_code'], 'order_id' => (string)$order_id], $input['partner_money']);

            $arr['pay_list'] = $jsApiParameters;

            $arr['order_id'] = $order_id;
        }

        return $this->success($arr);
    }

    /**
     * @Desc: 配置
     * @return mixed
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/28 16:12
     */
    public function getConfig()
    {
        $partner_money = getConfigSetting($this->_uniacid, 'partner_money');

        $data = Config::where('uniacid', $this->_uniacid)->field(['partner_agreement', 'partner_instructions'])->find();

        $data['partner_money'] = $partner_money;

        return $this->success($data);
    }

    /**
     * @Desc: 重新支付
     * @return mixed
     * @throws \WxPayException
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/28 16:20
     */
    public function rePayOrder()
    {
        $input = request()->param();

        $order_insert = PartnerOrder::getInfo(['id' => $input['id']]);

        if ($order_insert['status'] != 1) {

            $this->errorMsg('订单状态错误，请刷新页面');
        }

        if ($order_insert['app_pay'] == 1 && $this->is_app != 1) {

            $this->errorMsg('请到APP完成支付');
        }

        if ($order_insert['app_pay'] == 0 && $this->is_app != 0) {

            $this->errorMsg('请到小程序完成支付');
        }

        if ($order_insert['app_pay'] == 2 && $this->is_app != 2) {

            $this->errorMsg('请到公众号完成支付');
        }

        if ($order_insert['pay_model'] == 2) {

            $user_model = new User();

            $user_balance = $user_model->where(['id' => $this->getUserId()])->value('balance');

            if ($user_balance < $order_insert['partner_money']) {

                $this->errorMsg('余额不足');
            }

            $result = [
                'order_code' => $order_insert['order_code'],
                'transaction_id' => $order_insert['order_code'],
                'money' => $order_insert['partner_money']
            ];

            PartnerOrder::notify($result);

            return $this->success(true);

        } elseif ($order_insert['pay_model'] == 3) {

            $pay_model = new PayModel($this->payConfig());

            $jsApiParameters = $pay_model->aliPay($order_insert['order_code'], $order_insert['partner_money'], 'PartnerOrder', 8, ['openid' => $this->getUserInfo()['openid'], 'uniacid' => $this->_uniacid, 'type' => 'PartnerOrder', 'out_trade_no' => $order_insert['order_code'], 'order_id' => (string)$order_insert['id']]);

            $arr['pay_list'] = $jsApiParameters;

            $arr['order_code'] = $order_insert['order_code'];

        } else {
            //微信支付
            $pay_controller = new \app\shop\controller\IndexWxPay($this->app);
            //支付
            $jsApiParameters = $pay_controller->createWeixinPay($this->payConfig(), $this->getUserInfo()['openid'], $this->_uniacid, "消费", ['type' => 'PartnerOrder', 'out_trade_no' => $order_insert['order_code'], 'order_id' => (string)$order_insert['id']], $order_insert['partner_money']);

            $arr['pay_list'] = $jsApiParameters;
        }

        return $this->success($arr);
    }

    /**
     * @Desc: 获取列表
     * @return mixed
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/28 18:28
     */
    public function getPartnerList()
    {
        PartnerOrderJoin::cancel($this->_uniacid);

        $input = request()->param();

        $where = [
            ['a.uniacid', '=', $this->_uniacid],
            ['a.status', '=', 4],
            ['a.join_end_time', '>', time()],
            ['a.is_open', '=', 1],
            ['a.is_cancel', '=', 0]
        ];

        if (!empty($input['title'])) {

            $where[] = ['a.title', 'like', '%' . $input['title'] . '%'];
        }

        if (!empty($input['type_id'])) {

            $where[] = ['a.type_pid', '=', $input['type_id']];
        }

        if (!empty($input['time'])) {

            switch ($input['time']) {
                //今天
                case 1:
                    $where[] = ['a.join_end_time', 'between', [strtotime(date('Y-m-d 00:00:00')), strtotime(date('Y-m-d 23:59:59'))]];
                    break;
                //明天
                case 2:
                    $where[] = ['a.join_end_time', 'between', [strtotime(date('Y-m-d 00:00:00', strtotime('+1 day'))), strtotime(date('Y-m-d 23:59:59', strtotime('+1 day')))]];
                    break;
                //三天内
                case 3:
                    $where[] = ['a.join_end_time', 'between', [time(), strtotime('+3 day')]];
                    break;
                //一周内
                case 4:
                    $where[] = ['a.join_end_time', 'between', [time(), strtotime('+7 day')]];
                    break;
                //一月内
                case 5:
                    $where[] = ['a.join_end_time', 'between', [time(), strtotime('+1 month')]];
                    break;
            }
        }

        if (!empty($input['money_type'])) {

            $where[] = ['a.money_type', '=', $input['money_type']];
        }

        $alh = 'ACOS(SIN((' . $input['lat'] . ' * 3.1415) / 180 ) *SIN((a.lat * 3.1415) / 180 ) +COS((' . $input['lat'] . ' * 3.1415) / 180 ) * COS((a.lat * 3.1415) / 180 ) *COS((' . $input['lng'] . ' * 3.1415) / 180 - (a.lng * 3.1415) / 180 ) ) * 6378.137*1000 as distance';

        $data = PartnerOrder::getIndexList($where, $alh, $input['limit'] ?? 10);

        return $this->success($data);
    }

    /**
     * @Desc: 获取详情
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/29 15:14
     */
    public function getPartnerInfo()
    {
        $id = request()->param('id', 0);

        $data = PartnerOrder::getDetail(['id' => $id]);

        $data['is_me'] = $data['user_id'] == $this->getUserId() ? 1 : 0;

        return $this->success($data);
    }

    /**
     * @Desc: 报名
     * @return mixed
     * @throws \WxPayException
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/29 17:41
     */
    public function joinPartner()
    {
        PartnerOrderJoin::cancel($this->_uniacid, $this->getUserId());

        $data = request()->only(['id', 'phone', 'code', 'text', 'field', 'pay_model']);

        $key = 'join_partner' . $data['id'];

        incCache($key, 1, $this->_uniacid);

        $cache = getCache($key, $this->_uniacid);

        if ($cache != 1) {

            decCache($key, 1, $this->_uniacid);

            $this->errorMsg('参与当前组局人数过多，请稍后再试~');
        }

        $partner = PartnerOrder::getInfo(['id' => $data['id']]);

        if (empty($partner)) {

            decCache($key, 1, $this->_uniacid);
            $this->errorMsg('活动不存在');
        } elseif ($partner['status'] != 4) {

            decCache($key, 1, $this->_uniacid);
            $this->errorMsg('活动不可加入');
        } elseif ($partner['is_cancel'] == 1) {

            decCache($key, 1, $this->_uniacid);
            $this->errorMsg('活动已取消');
        } elseif ($partner['join_end_time'] < time()) {

            decCache($key, 1, $this->_uniacid);
            $this->errorMsg('报名已截止');
        } elseif ($partner['sign_num'] <= $partner['join_num']) {

            decCache($key, 1, $this->_uniacid);
            $this->errorMsg('报名人数已满');
        } elseif ($this->getUserId() == $partner['user_id']) {

            decCache($key, 1, $this->_uniacid);
            $this->errorMsg('不能报名自己发布的活动');
        }

        $count = PartnerOrderJoin::getCount([['user_id', '=', $this->getUserId()], ['order_id', '=', $data['id']], ['status', 'not in', [-1, 1, 3]]]);

        if ($count) {

            decCache($key, 1, $this->_uniacid);
            $this->errorMsg('您已报名该活动');
        }

        if (!empty($data['code'])) {

            $get_code = getCache($data['phone'], $this->_uniacid);

            if ($get_code != $data['code']) {

                decCache($key, 1, $this->_uniacid);
                $this->errorMsg('验证码错误');
            }

            $phone = User::where('id', $this->getUserId())->value('phone');

            if (empty($phone)) {

                User::update(['phone' => $data['phone']], ['id' => $this->getUserId()]);
            }
        }

        $field = PartnerOrderField::handle($data['field']);

        $config_model = new Config();

        $config = $config_model->dataInfo(['uniacid' => $this->_uniacid]);

        $insert = [
            'uniacid' => $this->_uniacid,
            'order_id' => $data['id'],
            'user_id' => $this->getUserId(),
            'is_create' => 0,
            'order_code' => orderCode(),
            'status' => 1,
            'phone' => $data['phone'],
            'text' => $data['text'],
            'field' => json_encode($field, true),
            'join_price' => $partner['join_price'],
            'app_pay' => $this->is_app,
            'is_check' => $partner['is_check'],
            'pay_model' => $data['pay_model'],
            'over_time' => time() + $config['over_time'] * 60,
        ];

        Db::startTrans();
        try {
            $order_id = PartnerOrderJoin::add($insert);

            if (!$order_id) {

                throw new \Exception('报名失败');
            }

            //小程序
            if ($this->is_app == 0) {

                $user_model = new User();

                $input['page'] = 'partner/pages/sign-in';

                $input['join_id'] = $order_id;
                //获取二维码
                $qr = $user_model->orderQr($input, $this->_uniacid);

            } else {

                $page = 'https://' . $_SERVER['HTTP_HOST'] . '/h5/#/partner/pages/sign-in?join_id=' . $order_id;

                $qr = base64ToPng(getCode($this->_uniacid, $page));
            }

            if (empty($qr)) {

                throw new \Exception('二维码生成失败');
            }

            $res = PartnerOrderJoin::edit(['id' => $order_id], ['qr_url' => $qr]);

            if (!$res) {

                throw new \Exception('报名失败');
            }

            $res = PartnerOrder::edit(['id' => $data['id']], ['join_num' => Db::raw('join_num+1')]);

            if (!$res) {

                throw new \Exception('报名失败');
            }

            if ($data['pay_model'] == 2 && $insert['join_price'] > 0) {

                $user_model = new User();

                $user_balance = $user_model->where(['id' => $this->getUserId()])->value('balance');

                if ($user_balance < $insert['join_price']) {

                    throw new \Exception('余额不足');
                }
            }

            Db::commit();
        } catch (\Exception $exception) {
            Db::rollback();
            decCache($key, 1, $this->_uniacid);

            $this->errorMsg($exception->getMessage());
        }

        if ((float)$insert['join_price'] <= 0) {

            $result = [
                'order_code' => $insert['order_code'],
                'transaction_id' => $insert['order_code'],
                'money' => 0
            ];

            PartnerOrderJoin::notify($result);

            decCache($key, 1, $this->_uniacid);
            return $this->success(true);
        }

        if ($data['pay_model'] == 2) {

            $result = [
                'order_code' => $insert['order_code'],
                'transaction_id' => $insert['order_code'],
                'money' => $insert['join_price']
            ];

            PartnerOrderJoin::notify($result);
            decCache($key, 1, $this->_uniacid);

            return $this->success(true);
        } elseif ($data['pay_model'] == 3) {

            $pay_model = new PayModel($this->payConfig());

            $jsApiParameters = $pay_model->aliPay($insert['order_code'], $insert['join_price'], 'PartnerOrderJoin', 9, ['openid' => $this->getUserInfo()['openid'], 'uniacid' => $this->_uniacid, 'type' => 'PartnerOrderJoin', 'out_trade_no' => $insert['order_code'], 'order_id' => (string)$order_id]);

            $arr['pay_list'] = $jsApiParameters;

            $arr['order_code'] = $insert['order_code'];

            $arr['order_id'] = $order_id;

        } else {
            //微信支付
            $pay_controller = new \app\shop\controller\IndexWxPay($this->app);
            //支付
            $jsApiParameters = $pay_controller->createWeixinPay($this->payConfig(), $this->getUserInfo()['openid'], $this->_uniacid, "消费", ['type' => 'PartnerOrderJoin', 'out_trade_no' => $insert['order_code'], 'order_id' => (string)$order_id], $insert['join_price']);

            $arr['pay_list'] = $jsApiParameters;

            $arr['order_id'] = $order_id;
        }
        decCache($key, 1, $this->_uniacid);

        return $this->success($arr);
    }

    /**
     * @Desc: 报名重新支付
     * @return mixed
     * @throws \WxPayException
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/11/19 18:23
     */
    public function rePayJoin()
    {
        $input = request()->param();

        $order_insert = PartnerOrderJoin::getInfo(['id' => $input['id']]);

        if ($order_insert['status'] != 1) {

            $this->errorMsg('订单状态错误，请刷新页面');
        }

        if ($order_insert['app_pay'] == 1 && $this->is_app != 1) {

            $this->errorMsg('请到APP完成支付');
        }

        if ($order_insert['app_pay'] == 0 && $this->is_app != 0) {

            $this->errorMsg('请到小程序完成支付');
        }

        if ($order_insert['app_pay'] == 2 && $this->is_app != 2) {

            $this->errorMsg('请到公众号完成支付');
        }

        if ($order_insert['pay_model'] == 2) {

            $user_model = new User();

            $user_balance = $user_model->where(['id' => $this->getUserId()])->value('balance');

            if ($user_balance < $order_insert['partner_money']) {

                $this->errorMsg('余额不足');
            }

            $result = [
                'order_code' => $order_insert['order_code'],
                'transaction_id' => $order_insert['order_code'],
                'money' => $order_insert['partner_money']
            ];

            PartnerOrderJoin::notify($result);

            return $this->success(true);

        } elseif ($order_insert['pay_model'] == 3) {

            $pay_model = new PayModel($this->payConfig());

            $jsApiParameters = $pay_model->aliPay($order_insert['order_code'], $order_insert['join_price'], 'PartnerOrderJoin', 9, ['openid' => $this->getUserInfo()['openid'], 'uniacid' => $this->_uniacid, 'type' => 'PartnerOrderJoin', 'out_trade_no' => $order_insert['order_code'], 'order_id' => (string)$order_insert['id']]);

            $arr['pay_list'] = $jsApiParameters;

            $arr['order_code'] = $order_insert['order_code'];

        } else {
            //微信支付
            $pay_controller = new \app\shop\controller\IndexWxPay($this->app);
            //支付
            $jsApiParameters = $pay_controller->createWeixinPay($this->payConfig(), $this->getUserInfo()['openid'], $this->_uniacid, "消费", ['type' => 'PartnerOrderJoin', 'out_trade_no' => $order_insert['order_code'], 'order_id' => (string)$order_insert['id']], $order_insert['join_price']);

            $arr['pay_list'] = $jsApiParameters;
        }

        return $this->success($arr);
    }

    /**
     * @Desc: 组团记录
     * @return mixed
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/30 13:43
     */
    public function myPartner()
    {
        PartnerOrderJoin::cancel($this->_uniacid);

        $input = request()->param();

        $where = [
            ['a.uniacid', '=', $this->_uniacid],
            ['a.user_id', '=', $this->getUserId()],
            ['a.status', 'in', [2, 4]],
            ['b.status', '>', 1],
            ['b.is_cancel', '=', 0]
        ];
        if (!empty($input['type'])) {

            $where[] = $input['type'] == 1 ? ['a.is_create', '=', 1] : ['a.is_create', '=', 0];
        }

        $data = PartnerOrderJoin::getMyPartner($where, $input['limit'] ?? 10);

        if ($data['data']) {

            foreach ($data['data'] as &$item) {

                $item['status_text'] = $this->get_status($item);

                $item['join_price'] = $item['join_price'] + 0;
            }
        }

        return $this->success($data);
    }

    /**
     * @Desc: 加入审核
     * @return mixed
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/30 15:34
     */
    public function joinCheck()
    {
        $input = request()->only(['id', 'status']);

        if (!in_array($input['status'], [3, 4])) {

            $this->errorMsg('状态错误');
        }

        $join = PartnerOrderJoin::getInfo(['id' => $input['id']]);

        if (empty($join)) {

            $this->errorMsg('未找到该记录');
        } elseif ($join['status'] != 2) {

            $this->errorMsg('该记录已处理');
        }

        $order = PartnerOrder::getInfo(['id' => $join['order_id']]);

        if ($order['status'] != 4 || $order['cancel'] == 1) {

            $this->errorMsg('该组局不可加入');
        } elseif ($order['user_id'] != $this->getUserId()) {

            $this->errorMsg('你没有权限');
        }


        $code = PartnerOrderJoin::joinCheck($input);

        if (isset($code['code'])) {

            return $this->error($code['msg']);
        }

        return $this->success(true);
    }

    /**
     * @Desc: 取消报名/组局
     * @return mixed
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/30 17:33
     */
    public function cancel()
    {
        $id = request()->param('id');

        $join = PartnerOrderJoin::getInfo([['order_id', '=', $id], ['user_id', '=', $this->getUserId()], ['status', 'in', [2, 4]]]);

        if (empty($join)) {

            $this->errorMsg('未找到组局记录');
        }

        $order = PartnerOrder::getInfo(['id' => $join['order_id']]);

        if ($order['start_time'] < time() && $join['status'] == 4 && $order['status'] == 4) {

            $this->errorMsg('该组局已开始，无法取消');
        } elseif ($order['sign_start_time'] < time() && $join['status'] == 4 && $order['status'] == 4) {

            $this->errorMsg('该组局已开始签到，无法取消');
        }

        $data = PartnerOrder::cancelPartner($join);

        if (isset($data['code'])) {

            return $this->error($data['msg']);
        }
        return $this->success(true);
    }

    /**
     * @Desc: 获取组局详情
     * @return mixed
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/11/1 15:22
     */
    public function getJoinInfo()
    {
        $id = request()->param('join_id', '');

        $is_code = request()->param('is_code', 1);

        $join = PartnerOrderJoin::getInfo(['id' => $id]);

        if (empty($join)) {

            return $this->error('未找到该记录');
        } elseif ($join['status'] != 4 && $is_code == 1) {

            return $this->error('该记录无法签到');
        }

        $order = PartnerOrder::getInfo(['id' => $join['order_id']]);

        if ($order['user_id'] != $this->getUserId() && $is_code == 1) {

            return $this->success(201);
        }

        $arr = [
            'join_id' => $join['id'],
            'start_time' => $order['start_time'],
            'end_time' => $order['end_time'],
            'address' => $order['address'],
            'cover' => $order['cover'],
            'title' => $order['title'],
            'money_type' => $order['money_type'],
            'join_price' => $order['join_price'] + 0,
            'text' => $join['text'],
            'lat' => $order['lat'],
            'lng' => $order['lng'],
            'nickName' => User::where('id', $join['user_id'])->value('nickName'),
            'field' => json_decode($join['field'], true),
            'phone' => $join['phone'],
            'qr_url' => $join['qr_url'],
            'sign_start_minute' => $order['sign_start_minute'],
            'address_info' => $order['address_info'],
            'is_sign' => $join['is_sign'],
        ];

        return $this->success($arr);
    }

    protected function get_status($data)
    {
        if ($data['is_create'] == 1) {

            switch ($data['status']) {

                case 2:
                    //待审核
                    $status = 1;
                    break;
                case 3:
                    //驳回
                    $status = 2;
                    break;
                case 4:
                    //同意
                    $status = 3;

                    $join = PartnerOrderJoin::where(['order_id' => $data['id'], 'status' => 2, 'is_create' => 0])->count();

                    if ($join) {
                        //待加入
                        $status = 4;
                    }
                    break;
            }
        } else {

            $status = $data['join_status'] == 2 ? 5 : 6;
        }

        return $status;
    }


    /**
     * @Desc: 获取组局详情
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/11/1 15:50
     */
    public function myPartnerInfo()
    {
        $id = request()->param('join_id', '');

        $join = PartnerOrderJoin::getInfo(['id' => $id]);

        $data = PartnerOrder::getDetail(['id' => $join['order_id']]);

        $data['is_create'] = $join['is_create'];

        $data['join_status'] = $join['status'];

        $data['qr_url'] = $join['qr_url'];

        $data['status_text'] = $this->get_status($data);

        $data['is_me'] = $data['user_id'] == $this->getUserId() ? 1 : 0;

        return $this->success($data);
    }

    /**
     * @Desc: 搭子签到
     * @return mixed
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/11/1 16:20
     */
    public function partnerSign()
    {
        $id = request()->param('join_id', '');

        $join = PartnerOrderJoin::getInfo(['id' => $id]);

        if (empty($join)) {

            return $this->error('未找到该记录');
        } elseif ($join['status'] != 4) {

            return $this->error('该记录无法签到');
        } elseif ($join['is_sign'] == 1) {

            return $this->error('已签到');
        } elseif ($join['sign_start_time'] > time()) {

            return $this->error('未到签到时间，不可签到');
        }

        $order = PartnerOrder::getInfo(['id' => $join['order_id']]);

        if ($order['user_id'] != $this->getUserId()) {

            return $this->error('你没有权限');
        } elseif ($order['end_time'] < time()) {

            return $this->error('活动已结束，不可签到');
        }

        $res = PartnerOrderJoin::edit(['id' => $id], ['is_sign' => 1]);

        return $this->success($res);
    }
}