<?php
/**
 * Created by PhpStorm
 * User: shurong(龙兵科技)
 * Date: 2024/10/17
 * Time: 15:06
 * docs:
 */

namespace app\fxq\controller;

use app\AdminRest;
use app\fxq\model\FxqConfig;
use app\fxq\model\FxqContract;
use app\fxq\model\FxqContractFile;
use app\massage\model\Coach;
use longbingcore\wxcore\Fxq;
use think\App;
use think\facade\Db;

class Admin extends AdminRest
{
    public function __construct(App $app)
    {
        parent::__construct($app);
    }

    protected function getAdminId()
    {
        $admin = \app\massage\model\Admin::where('id', $this->_user['id'])->field('id,is_admin,admin_id')->find();

        if ($admin['is_admin'] == 0) {

            return $admin['id'];
        } elseif ($admin['is_admin'] == 3) {

            return $admin['admin_id'];
        }
        return 0;
    }

    /**
     * @Desc: 设置配置
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/17 15:14
     */
    public function setConfig()
    {
        $data = request()->only(['fxq_api_key', 'fxq_secret_key', 'contract', 'commitment', 'company_name', 'company_title', 'company_seq_no', 'company_id_no', 'contract_years', 'corporation', 'status']);

        $data['admin_id'] = $this->getAdminId();

        if (request()->isPost()) {

            $config = FxqConfig::getInfo(['uniacid' => $this->_uniacid, 'admin_id' => $data['admin_id']]);

            $res = FxqConfig::where(['uniacid' => $this->_uniacid, 'admin_id' => $data['admin_id']])->update($data);

            if ($res === false) {

                return $this->error('编辑失败');
            }

            if ($data['status'] == 1) {

                $model = Fxq::create($this->_uniacid, 0, $data['admin_id']);

                if (is_array($model) && isset($model['code'])) {

                    return $this->error($model['msg']);
                }

                //修改公司信息
                $code = $model->configUpdate($data, $config);

                if (isset($code['code'])) {

                    return $this->error($code['msg']);
                }
            }

            return $this->success('');
        }

        $config = FxqConfig::getInfo(['uniacid' => $this->_uniacid, 'admin_id' => $data['admin_id']]);

        return $this->success($config);
    }

    /**
     * @Desc: 生成合同
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/18 13:39
     */
    public function addContract()
    {
        $coach_id = request()->param('coach_id', 0);


        $admin_id = Coach::where('id', $coach_id)->value('admin_id');

        $contract = FxqContract::getInfo([['uniacid', '=', $this->_uniacid], ['coach_id', '=', $coach_id], ['admin_id', '=', $admin_id], ['status', '>', -1]]);

        if (!empty($contract)) {

            if (in_array($contract['status'], [1, 2])) {

                return $this->error('此技师合同正在签署中，请勿重复签署');
            } elseif ($contract['end_time'] > time()) {

                return $this->error('此技师合同未过期，请勿重复签署');
            }
        }

        $key = 'fxq_contract_' . $this->_uniacid;

        incCache($key, 1, $this->_uniacid);

        $value = getCache($key, $this->_uniacid);

        if ($value != 1) {

            decCache($key, 1, $this->_uniacid);

            return $this->error('正在生成合同，请稍后');
        }

        $num = FxqContract::getNo($this->_uniacid);

        $config = FxqConfig::getInfo(['uniacid' => $this->_uniacid, 'admin_id' => $this->getAdminId()]);

        if (empty($config['contract_pdf_base64']) || empty($config['commitment_pdf_base64']) || empty($config['contract_years'])) {
            decCache($key, 1, $this->_uniacid);

            return $this->error('请先配置合同');
        }

        Db::startTrans();

        try {

            $insert = [
                'uniacid' => $this->_uniacid,
                'fxq_admin_id' => $this->getAdminId(),
                'admin_id' => $admin_id,
                'coach_id' => $coach_id,
                'status' => 1,
                'contract_years' => $config['contract_years'],
                'date' => date('Y-m-d'),
                'number' => $num,
                'company_name' => $config['company_name'],
                'company_id_no' => $config['company_id_no'],
                'company_signature' => $config['company_signature']
            ];

            $contract_id = FxqContract::add($insert);

            if (!$contract_id) {

                throw new \Exception('合同生成失败');
            }

            $insert_file = [
                [
                    'uniacid' => $this->_uniacid,
                    'contract_id' => $contract_id,
                    'contract' => $config['contract_pdf_base64'],
                    'contract_no' => 'CN-' . date('Ymd') . '-' . $num,
                    'type' => 1,
                    'company_contract' => '',
                    'create_time' => time(),
                    'company_view_contract' => '',
                    'company_view_contract_img' => ''
                ]
            ];

            if (!empty($config['commitment_pdf_base64'])) {
                $insert_file[] = [
                    'uniacid' => $this->_uniacid,
                    'contract_id' => $contract_id,
                    'contract' => $config['commitment_pdf_base64'],
                    'contract_no' => 'CM-' . date('Ymd') . '-' . $num,
                    'type' => 2,
                    'company_contract' => $config['commitment_pdf_base64'],
                    'create_time' => time(),
                    'company_view_contract' => base64ToPdf($config['commitment_pdf_base64']),
                    'company_view_contract_img' => $config['commitment_pdf_img']
                ];
            }

            $res = FxqContractFile::insertAll($insert_file);

            if (!$res) {

                throw new \Exception('合同文件生成失败');
            }

            Db::commit();
        } catch (\Exception $exception) {

            Db::rollback();

            decCache($key, 1, $this->_uniacid);

            return $this->error($exception->getMessage());
        }

        decCache($key, 1, $this->_uniacid);

        return $this->success($contract_id, 200, $contract_id);
    }

    /**
     * @Desc: 合同列表
     * @return mixed
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/18 14:51
     */
    public function getContractList()
    {
        $input = request()->param();

        $where = [
            ['a.uniacid', '=', $this->_uniacid],
            ['a.status', '>', -1],
        ];

        if ($this->_user['is_admin'] != 1) {

            $where[] = ['a.admin_id', '=', $this->getAdminId()];
        }

        if (!empty($input['name'])) {

            $where[] = ['b.coach_name', 'like', '%' . $input['name'] . '%'];
        }

        $data = FxqContract::getList($where, $input['limit'] ?? 10);

        return $this->success($data);
    }

    /**
     * @Desc: 签署公司合同
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/18 16:03
     */
    public function companySign()
    {
        $id = request()->param('id');

        $data = FxqContract::getInfo(['id' => $id]);

        if (empty($data)) {

            return $this->error('合同不存在');
        }

        if ($data['status'] != 1) {

            return $this->error('此合同已签署');
        }

        $model = Fxq::create($this->_uniacid, 0, $this->getAdminId());

        if (is_array($model) && isset($model['code'])) {

            return $this->error($model['msg']);
        }

        $res = $model->companyContractSigning($id);

        if (isset($res['code'])) {

            return $this->error($res['msg']);
        }

        return $this->success($res);
    }

    /**
     * @Desc: 删除合同
     * @return mixed
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/22 16:49
     */
    public function delContract()
    {
        $id = request()->param('id', 0);

        $res = FxqContract::edit(['id' => $id], ['status' => -1]);

        return $this->success($res);
    }
}