<?php
/**
 * Created by PhpStorm
 * User: shurong(龙兵科技)
 * Date: 2024/10/25
 * Time: 11:16
 * docs:
 */

namespace app\partner\controller;

use app\AdminRest;
use app\partner\model\PartnerField;
use app\partner\model\PartnerOrder;
use app\partner\model\PartnerOrderField;
use app\partner\model\PartnerOrderJoin;
use app\partner\model\PartnerType;
use think\App;
use think\facade\Db;

class AdminPartner extends AdminRest
{
    public function __construct(App $app)
    {
        parent::__construct($app);
    }

    /**
     * @Desc: 添加分类
     * @return mixed
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/25 11:33
     */
    public function typeAdd()
    {
        $data = request()->only(['name', 'pid', 'top']);

        $data['uniacid'] = $this->_uniacid;

        $res = PartnerType::add($data);

        return $this->success($res);
    }

    /**
     * @Desc: 分类列表
     * @return mixed
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/25 11:40
     */
    public function typeList()
    {
        $input = request()->param();

        $where = [
            ['uniacid', '=', $this->_uniacid],
            ['status', '>', -1],
            ['pid', '=', 0]
        ];

        if (!empty($input['name'])) {

            $where[] = ['name', 'like', "%{$input['name']}%"];
        }

        $data = PartnerType::getList($where, $input['limit'] ?? 10);

        if ($data['data']) {

            foreach ($data['data'] as &$item) {

                $item['children'] = PartnerType::getListNoPage([['pid', '=', $item['id']], ['status', '>', -1]], ['id,name,pid,status,create_time,top']);
            }
        }

        return $this->success($data);
    }

    /**
     * @Desc: 修改分类
     * @return mixed
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/25 11:36
     */
    public function typeEdit()
    {
        $data = request()->only(['id', 'name', 'pid', 'top', 'status']);

        $res = PartnerType::edit(['id' => $data['id']], $data);

        return $this->success($res);
    }

    /**
     * @Desc:下拉
     * @return mixed
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/25 13:49
     */
    public function typeSelect()
    {
//        $input = request()->param();
//
//        $where = [
//            ['uniacid', '=', $this->_uniacid],
//            ['status', '=', 1],
//            ['pid', '=', 0]
//        ];
//
//        if (!empty($input['name'])) {
//
//            $where[] = ['name', 'like', "%{$input['name']}%"];
//        }

        $res = PartnerType::getIndexList($this->_uniacid);

        return $this->success($res);
    }

    /**
     * @Desc: 字段添加
     * @return mixed
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/25 15:17
     */
    public function filedAdd()
    {
        $data = request()->only(['name', 'type', 'top', 'select', 'is_required']);

        $data['uniacid'] = $this->_uniacid;

        $data['select'] = empty($data['select']) ? '' : json_encode($data['select']);

        $res = PartnerField::add($data);

        return $this->success($res);
    }

    /**
     * @Desc: 字段修改
     * @return mixed
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/25 15:17
     */
    public function filedEdit()
    {
        $data = request()->only(['id', 'name', 'type', 'top', 'select', 'is_required', 'status']);

        $data['uniacid'] = $this->_uniacid;

        $data['select'] = empty($data['select']) ? '' : json_encode($data['select']);

        $res = PartnerField::edit(['id' => $data['id']], $data);

        return $this->success($res);
    }

    /**
     * @Desc: 字段列表
     * @return mixed
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/25 15:20
     */
    public function fieldList()
    {
        $input = request()->param();

        PartnerField::initData($this->_uniacid);

        $where = [
            ['uniacid', '=', $this->_uniacid],
            ['status', '>', -1]
        ];

        if (!empty($input['name'])) {

            $where[] = ['name', 'like', "%{$input['name']}%"];
        }

        $data = PartnerField::getList($where, $input['limit'] ?? 10);

        return $this->success($data);
    }

    /**
     * @Desc: 组局列表
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/29 10:23
     */
    public function getPartnerList()
    {
        PartnerOrderJoin::cancel($this->_uniacid);

        $input = request()->param();

        $where = [
            ['a.uniacid', '=', $this->_uniacid],
            ['a.status', '>', 1],
            ['a.is_admin_del', '=', 0]
        ];

        if (!empty($input['create_name'])) {

            $where[] = ['d.nickname', 'like', "%{$input['create_name']}%"];
        }

        if (!empty($input['start_time'])) {

            $where[] = ['a.create_time', '>=', $input['start_time']];
        }

        if (!empty($input['end_time'])) {

            $where[] = ['a.create_time', '<=', $input['end_time']];
        }

        if (!empty($input['status'])) {

            $where[] = ['a.status', '=', $input['status']];
        }

        if (!empty($input['type_pid'])) {

            $where[] = ['a.type_pid', '=', $input['type_pid']];
        }

        if (!empty($input['type_id'])) {

            $arr = PartnerType::where(['status' => 1, 'pid' => $input['type_id']])->column('id');

            $arr[] = $input['type_id'];

            $where[] = ['a.type_id', 'in', $arr];
        }

        $data = PartnerOrder::getAdminList($where, $input['limit'] ?? 10);

        $data['all_count'] = PartnerOrder::getCount([['uniacid', '=', $this->_uniacid], ['status', '>', 1], ['is_admin_del', '=', 0]]);

        $data['pass_count'] = PartnerOrder::getCount([['uniacid', '=', $this->_uniacid], ['status', '>=', 4], ['is_admin_del', '=', 0]]);

        $data['refuse_count'] = PartnerOrder::getCount([['uniacid', '=', $this->_uniacid], ['status', '=', 3], ['is_admin_del', '=', 0]]);

        $data['check_count'] = PartnerOrder::getCount(['uniacid' => $this->_uniacid, 'status' => 2, 'is_admin_del' => 0]);

        return $this->success($data);
    }

    /**
     * @Desc: 详情
     * @return mixed
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/11/14 18:48
     */
    public function getPartnerInfo()
    {
        $id = request()->param('id');

        $data = PartnerOrder::getInfo(['id' => $id]);

        $data['type_name'] = PartnerType::where('id', $data['type_id'])->value('name');

        $data['content'] = empty($data['content']) ? '' : json_decode($data['content'], true);

        $data['field'] = PartnerOrderField::getListByOrderId($data['id']);

        return $this->success($data);
    }

    /**
     * @Desc: 审核
     * @return mixed
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/29 13:51
     */
    public function partnerCheck()
    {
        $id = request()->param('id', '');

        $status = request()->param('status', '');

        $text = request()->param('text', '');

        if (empty($id) || empty($status) || !in_array($status, [3, 4])) {

            return $this->error('参数错误');
        }

        $partner = PartnerOrder::getInfo(['id' => $id]);

        if (empty($partner) || $partner['status'] != 2 || $partner['is_cancel'] == 1) {

            return $this->error('此组局不存在或不可审核');
        }

        $update = [
            'status' => $status,
            'partner_check_type' => 1,
            'partner_check_admin_id' => $this->_user['admin_id'],
            'partner_check_text' => $text,
            'partner_check_time' => time(),
            'id' => $id
        ];

        $res = PartnerOrder::partnerCheck($update);

        if (isset($res['code'])) {

            return $this->error($res['msg']);
        }

        return $this->success($res);
    }

    /**
     * @Desc: 费用列表
     * @return mixed
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/31 17:43
     */
    public function getMoneyList()
    {
        PartnerOrder::userBalance($this->_uniacid);

        $input = request()->param();

        $where = [
            ['a.uniacid', '=', $this->_uniacid],
            ['a.status', 'in', [2, 4]],
            ['a.pay_price', '>', 0]
        ];

        if (!empty($input['start_time']) && !empty($input['end_time'])) {

            $where[] = ['a.pay_time', 'between', "{$input['start_time']},{$input['end_time']}"];
        }

        if (!empty($input['type'])) {

            switch ($input['type']) {

                case 1:
                    $where[] = ['a.end_time', '<', time()];
                    $where[] = ['a.is_cancel', '=', 0];
                    break;
                case 2:
                    $where[] = ['a.end_time', '>', time()];
                    $where[] = ['a.is_cancel', '=', 0];
                    break;

                case 3:
                    $where[] = ['a.is_cancel', '=', 1];
                    break;
            }
        }

        $data = PartnerOrder::getMoneyList($where, $input['limit'] ?? 10);

        if ($data['data']) {

            foreach ($data['data'] as &$datum) {

                if ($datum['is_cancel'] == 1) {

                    $datum['status_text'] = 3;
                } else {

                    $datum['status_text'] = $datum['end_time'] < time() ? 2 : 1;
                }
            }
        }

        return $this->success($data);
    }

    /**
     * @Desc: 删除订单
     * @return mixed
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/11/19 11:27
     */
    public function partnerDel()
    {
        $id = request()->param('id', '');

        $res = PartnerOrder::edit(['id' => $id], ['is_admin_del' => 1]);

        return $this->success($res);
    }
}