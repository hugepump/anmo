<?php
/**
 * Created by PhpStorm
 * User: shurong
 * Date: 2024/8/7
 * Time: 11:23
 * docs:
 */

namespace longbingcore\wxcore;

use app\baiying\model\BaiYingConfig;
use app\baiying\model\BaiYingPhoneRecord;
use app\massage\model\OrderAddress;
use think\facade\Log;

require_once EXTEND_PATH . 'baiying/BYTokenClient.php';

require_once EXTEND_PATH . 'baiying/BYGetTokenClient.php';

class BaiYing
{
    protected $uniacid;

    protected $access_token;

    protected $company_id;

    protected $config;

    /**
     * 初始化（赋值token等）
     * @param $uniacid
     */
    public function __construct($uniacid)
    {
        $this->uniacid = $uniacid;

        $key = 'by_access_token' . $uniacid;

        $config = BaiYingConfig::getInfo(['uniacid' => $uniacid]);

        $access_token = getCache($key, $uniacid);

        if (empty($access_token)) {

            $con = new \BYGetTokenClient($config['app_key'], $config['app_secret']);

            $data = $con->get_token('platform', $config['company_id']);

            if (isset($data['code']) && $data['code'] == 200 && isset($data['data']['access_token'])) {

                $access_token = $data['data']['access_token'];

                setCache($key, $access_token, 604800);
            }
        }

        $this->company_id = $config['company_id'];

        $this->access_token = $access_token;

        $this->config = $config;
    }

    /**
     * @Desc: 获取话术
     * @return mixed
     * @throws \Exception
     * @Auther: shurong
     * @Time: 2024/8/8 14:02
     */
    public function scriptList()
    {
        if (empty($this->access_token)) {

            return ['code' => 400, 'resultMsg' => '请配置基础信息'];
        }

        $client = new \BYTokenClient($this->access_token);

        $method = 'byai.openapi.robot.list'; //要调用的api名称

        $api_version = '1.0.0'; //要调用的api版本号

        $my_params = [

            'companyId' => $this->company_id,

            'robotStatus' => 1
        ];

        $my_files = [

        ];

        $data = $client->post($method, $api_version, $my_params, $my_files);

        if ($data['code'] == 200) {

            return $data['data'];
        }

        return $data;
    }

    /**
     * @Desc: 外呼线路列表
     * @return mixed
     * @throws \Exception
     * @Auther: shurong
     * @Time: 2024/8/8 18:14
     */
    public function phoneList()
    {
        if (empty($this->access_token)) {

            return ['code' => 400, 'resultMsg' => '请配置基础信息'];
        }

        $client = new \BYTokenClient($this->access_token);

        $method = 'byai.openapi.phone.list'; //要调用的api名称

        $api_version = '1.0.0'; //要调用的api版本号

        $my_params = [
            'companyId' => $this->company_id,
        ];


        $my_files = [
        ];

        $data = $client->post($method, $api_version, $my_params, $my_files);

        if ($data['code'] == 200) {

            return $data['data'];
        }

        return $data;
    }

    /**
     * @Desc: 创建任务
     * @param $type 1 待付款 2 订单雷达
     * @return array
     * @throws \Exception
     * @Auther: shurong
     * @Time: 2024/8/8 14:03
     */
    public function make($type = 1)
    {
        if (empty($this->access_token)) {

            return ['code' => 400, 'resultMsg' => '请配置基础信息'];
        }

        //判断当前时间是否在 9:00-20:00之间
        $time = date('H:i:s');

        if (($time >= '09:00:00' && $time <= '20:00:00') == false) {

            return ['code' => 400, 'resultMsg' => '不在规定时间内'];
        }

        if (($type == 1 && (empty($this->config['wait_pay_script_id']) || empty($this->config['wait_pay_phone_id']))) || ($type == 2 && (empty($this->config['order_radar_script_id']) || empty($this->config['order_radar_phone_id'])))) {

            return ['code' => 400, 'resultMsg' => '话术ID或外呼号码未配置'];
        }

        $client = new \BYTokenClient($this->access_token);

        $method = 'byai.openapi.calljob.create'; //要调用的api名称

        $api_version = '1.0.0'; //要调用的api版本号

        $my_params = [
            'callJobName' => '系统自动创建' . date('YmdHis'),
            'callJobType' => '2',
            'companyId' => $this->company_id,
            'robotDefId' => $type == 1 ? $this->config['wait_pay_script_id'] : $this->config['order_radar_script_id'],
            'userPhoneIds' => '[' . ($type == 1 ? $this->config['wait_pay_phone_id'] : $this->config['order_radar_phone_id']) . ']',
            'concurrencyQuota' => 50
        ];

        $my_files = [
        ];

        $data = $client->post($method, $api_version, $my_params, $my_files);

        return $data;
    }

    /**
     * @Desc: 导入用户手机号
     * @param $job_id
     * @param $phoneList
     * @return array
     * @throws \Exception
     * @Auther: shurong
     * @Time: 2024/8/9 11:40
     */
    public function importPhone($job_id, $phoneList)
    {

        if (empty($phoneList) || empty($this->access_token)) {

            return ['code' => 400, 'resultMsg' => '请配置基础信息'];
        }

        $customerInfoVOList = [];

        foreach ($phoneList as $key => $item) {

            $customerInfoVOList[] = [
                'name' => (string)$key,

                'phone' => (string)$item
            ];
        }

        $client = new \BYTokenClient($this->access_token);

        $method = 'byai.openapi.calljob.customer.import'; //要调用的api名称

        $api_version = '1.0.0'; //要调用的api版本号

        $my_params = [
            'callJobId' => $job_id,
            'companyId' => $this->company_id,
            'customerInfoVOList' => json_encode($customerInfoVOList),
        ];

        $my_files = [
        ];

//        $filename = "./log/baiying/baiying" . date('Y-m-d') . ".log";

        $str = [
            $method, $api_version, $my_params, $my_files
        ];

//        file_put_contents($filename, time() . "\n" . json_encode($str) . "\n" . "\n", FILE_APPEND | LOCK_EX);

        $data = $client->post($method, $api_version, $my_params, $my_files);

//        file_put_contents($filename, time() . "\n" . json_encode($data) . "\n" . "\n", FILE_APPEND | LOCK_EX);

        return $data;
    }

    /**
     * @Desc: 开始任务
     * @param $job_id
     * @return array
     * @throws \Exception
     * @Auther: shurong
     * @Time: 2024/8/9 11:47
     */
    public function start($job_id)
    {
        if (empty($this->access_token)) {

            return ['code' => 400, 'resultMsg' => '请配置基础信息'];
        }

        $client = new \BYTokenClient($this->access_token);

        $method = 'byai.openapi.calljob.execute'; //要调用的api名称

        $api_version = '1.0.0'; //要调用的api版本号

        $my_params = [
            'callJobId' => $job_id,
            'command' => '1', //1开始 2暂停 3停止
            'companyId' => $this->company_id,
        ];

        $my_files = [
        ];

        $data = $client->post($method, $api_version, $my_params, $my_files);

        return $data;
    }

    /**
     * @Desc: 发起任务并执行
     * @param $order
     * @return bool|array
     * @throws \Exception
     * @Auther: shurong
     * @Time: 2024/8/13 11:38
     */
    public function water($phone, $type = 1)
    {
        try {
            $job = $this->make($type);

            if ($job['code'] != 200) {

                throw new \Exception('创建任务-' . $job['resultMsg']);
            }

            $job_id = $job['data']['callJobId'];

            $phoneList = array_column($phone, 'mobile', 'user_name');

            $res = $this->importPhone($job_id, $phoneList);

            if ($res['code'] != 200) {

                throw new \Exception('导入手机号-' . $res['resultMsg']);
            }

            $res = $this->start($job_id);

            if ($res['code'] != 200) {

                throw new \Exception('启动任务-' . $res['resultMsg']);
            }

        } catch (\Exception $exception) {

            return ['code' => 400, 'resultMsg' => $exception->getMessage()];
        }

        $insert = [];

        foreach ($phone as $add) {

            $add['job_id'] = $job_id;

            $add['type'] = $type;

            $add['create_time'] = $add['update_time'] = time();

            $insert[] = $add;
        }

        if ($insert) {

            BaiYingPhoneRecord::insertAll($insert);
        }

        return true;
    }
}