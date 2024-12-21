<?php
/**
 * Created by PhpStorm
 * User: shurong
 * Date: 2024/8/12
 * Time: 11:45
 * docs:
 */

namespace app\baiying\controller;

use app\AdminRest;
use app\massage\model\Coach;
use app\massage\model\NoPayRecord;
use app\massage\model\NoPayRecordGoods;
use app\massage\model\Order;
use longbingcore\wxcore\BaiYing;

require_once EXTEND_PATH . 'baiying/BYTokenClient.php';

require_once EXTEND_PATH . 'baiying/BYGetTokenClient.php';

class Admin extends AdminRest
{
    /**
     * @Desc: 话术列表
     * @return mixed
     * @throws \Exception
     * @Auther: shurong
     * @Time: 2024/8/12 14:09
     */
    public function scriptList()
    {
        $baiYing = new BaiYing($this->_uniacid);

        $data = $baiYing->scriptList();

        if (isset($data['code'])) {

            return $this->error($data['resultMsg']);
        }

        return $this->success($data);
    }

    /**
     * @Desc: 话术列表
     * @return mixed
     * @throws \Exception
     * @Auther: shurong
     * @Time: 2024/8/14 14:33
     */
    public function scriptListV2()
    {

        $config = request()->only(['app_key', 'app_secret', 'company_id']);

        $con = new \BYGetTokenClient($config['app_key'], $config['app_secret']);

        $data = $con->get_token('platform', $config['company_id']);

        if (isset($data['code']) && $data['code'] == 200 && isset($data['data']['access_token'])) {

            $access_token = $data['data']['access_token'];

            $client = new \BYTokenClient($access_token);

            $method = 'byai.openapi.robot.list'; //要调用的api名称

            $api_version = '1.0.0'; //要调用的api版本号

            $my_params = [

                'companyId' => $config['company_id'],

                'robotStatus' => 0
            ];

            $my_files = [

            ];

            $data = $client->post($method, $api_version, $my_params, $my_files);

            if ($data['code'] == 200) {

                return $this->success($data['data']);
            }
        }

        return $this->error(isset($data['resultMsg']) ? $data['resultMsg'] : (isset($data['message']) ? $data['message'] : '未知错误'));
    }

    /**
     * @Desc: 外呼线路列表
     * @return mixed
     * @throws \Exception
     * @Auther: shurong
     * @Time: 2024/8/12 14:16
     */
    public function phoneList()
    {
        $baiYing = new BaiYing($this->_uniacid);

        $data = $baiYing->phoneList();

        if (isset($data['code'])) {

            return $this->error($data['resultMsg']);
        }

        return $this->success($data);
    }

    public function phoneListV2()
    {
        $config = request()->only(['app_key', 'app_secret', 'company_id']);

        $con = new \BYGetTokenClient($config['app_key'], $config['app_secret']);

        $data = $con->get_token('platform', $config['company_id']);

        if (isset($data['code']) && $data['code'] == 200 && isset($data['data']['access_token'])) {

            $access_token = $data['data']['access_token'];

            $client = new \BYTokenClient($access_token);

            $method = 'byai.openapi.phone.list'; //要调用的api名称

            $api_version = '1.0.0'; //要调用的api版本号

            $my_params = [
                'companyId' => $config['company_id'],
            ];


            $my_files = [
            ];

            $data = $client->post($method, $api_version, $my_params, $my_files);

            if ($data['code'] == 200) {

                return $this->success($data['data']);
            }
        }

        return $this->error(isset($data['resultMsg']) ? $data['resultMsg'] : (isset($data['message']) ? $data['message'] : '未知错误'));
    }

    /**
     * @Desc: 外呼
     * @return mixed
     * @throws \Exception
     * @Auther: shurong
     * @Time: 2024/8/13 17:19
     */
    public function outbound()
    {
        $id = request()->param('id', 0);

        $key = 'by_outbound' . $this->_uniacid;

        incCache($key, 1, $this->_uniacid);

        $value = getCache($key, $this->_uniacid);

        if ($value != 1) {

            decCache($key, 1, $this->_uniacid);

            return $this->error('上次操作暂未完成，请稍后操作');
        }

        $input = $this->_param;

        $where = [
            ['a.uniacid', '=', $this->_uniacid],

            ['b.phone', '<>', ''],

            ['a.status', '=', 1]
        ];

        if (!empty($id)) {

            $where [] = ['a.id', '=', $id];
        }

        if (!empty($input['nickName'])) {

            $where[] = ['b.nickName', 'like', '%' . $input['nickName'] . '%'];
        }

        if (!empty($input['start_time'])) {

            $where[] = ['a.create_time', 'between', "{$input['start_time']},{$input['end_time']}"];
        }

        if ($this->_user['is_admin'] == 0) {

            $where[] = ['c.admin_id', '=', $this->_user['admin_id']];
        }

        if (isset($input['by_status']) && $input['by_status'] !== '' && $input['by_status'] > -2) {

            $where[] = ['a.by_status', '=', $input['by_status']];
        }

        $phone = NoPayRecord::alias('a')
            ->field('a.id,b.nickName as user_name,b.phone as mobile,a.uniacid')
            ->join('massage_service_user_list b', 'a.user_id = b.id')
            ->join('massage_service_coach_list c', 'a.coach_id = c.id')
            ->where($where)
            ->group('a.id')
            ->select()
            ->toArray();

        if (empty($phone)) {

            decCache($key, 1, $this->_uniacid);

            return $this->error('所选记录无可外呼手机号');
        }

        foreach ($phone as &$item) {

            $item['order_id'] = $item['id'];

            unset($item['id']);
        }

        $baiYing = new BaiYing($this->_uniacid);

        $res = $baiYing->water($phone, 2);

        decCache($key, 1, $this->_uniacid);

        if (isset($res['code'])) {

            return $this->error($res['resultMsg']);
        }

        return $this->success($res);
    }
}