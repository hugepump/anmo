<?php
/**
 * Created by PhpStorm
 * User: shurong(龙兵科技)
 * Date: 2024/10/14
 * Time: 18:26
 * docs:
 */

namespace longbingcore\wxcore;

use app\fxq\model\FxqCompanyCheck;
use app\fxq\model\FxqConfig;
use app\fxq\model\FxqContract;
use app\fxq\model\FxqContractFile;
use app\fxq\model\FxqFaceCheck;
use app\fxq\model\FxqIdCheck;
use app\massage\model\Coach;
use think\facade\Db;

require_once EXTEND_PATH . 'fxq/Token.php';
require_once EXTEND_PATH . 'fxq/Constant.php';
require_once EXTEND_PATH . 'fxq/Curl.php';
require_once EXTEND_PATH . 'fxq/Sign.php';
require_once EXTEND_PATH . 'fxq/SignUtils.php';

class Fxq
{

    protected $token;

    protected $admin_id;

    protected $uniacid;

    protected $coach_id;

    protected $user_id;

    public function __construct($uniacid, $coach_id, $admin_id)
    {

        if (!empty($coach_id)) {

            $coach = Coach::where(['uniacid' => $uniacid, 'id' => $coach_id])->find();

            $admin_id = $coach['admin_id'];
        }

        $config = FxqConfig::getInfo(['uniacid' => $uniacid, 'admin_id' => $admin_id]);

        if (empty($config) || empty($config['fxq_api_key']) || empty($config['fxq_secret_key'])) {

            throw new \Exception('请先配置合同签署接口信息');
        }

        if ($config['status'] != 1) {

            throw new \Exception('合同签署已关闭');
        }

        $appId = $config['fxq_api_key'];

        $secret = $config['fxq_secret_key'];

        $key = md5($appId . $secret);

        $this->token = getCache($key, $uniacid);

        if (empty($this->token)) {

            $model = new \Token();

            $this->token = $model->getToken($appId, $secret);

            setCache($key, $this->token, 7200, $uniacid);
        }

        $this->admin_id = $admin_id;

        $this->uniacid = $uniacid;

        $this->coach_id = $coach_id;

        $this->user_id = $coach['user_id'] ?? 0;
    }


    public static function create($uniacid = 666, $coach_id = 0, $admin_id = 0)
    {
        try {
            return new self($uniacid, $coach_id, $admin_id);
        } catch (\Exception $exception) {
            return ['code' => 1, 'msg' => $exception->getMessage()];
        }
    }

    /**
     * @Desc: 接口数据处理
     * @param $data
     * @return array
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/15 16:01
     */
    protected function handle($data, $url)
    {
        $signString = "";

        //排序
        $data = \SignUtils::sortParam($data);

        //签名加密
        \SignUtils::readParams($data, $signString);

        //流水号，每次请求保证唯一，五分钟之类不能重复
        $nonce = orderCode();

        $sign = md5(sha1(base64_encode($signString . "||token=" . $this->token . "||nonce=" . $nonce)));

        $curl = new \Curl();

        return $curl->serverSubmit($url, $data, 'post', $this->token, $nonce, $sign);
    }

    /**
     * @Desc: 公安二要素
     * @param $name
     * @param $idCode
     * @param $uniacid
     * @return true|array
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/15 16:35
     */
    public function idCheck($name, $idCode)
    {

        if (empty($name) || empty($idCode)) {

            return ['code' => 1, 'msg' => '姓名或身份证号为空'];
        }

        $is_check = FxqIdCheck::where(['user_id' => $this->user_id, 'uniacid' => $this->uniacid])->count();

        if ($is_check) {

            return true;
        }

        $data = [
            "realName" => $name,
            "idCardNo" => $idCode
        ];

        $result = $this->handle($data, \Constant::ID_CHECK_URL);

        if (empty($result)) {

            return ['code' => 1, 'msg' => '请求失败'];
        }

        if (!isset($result['code']) || $result['code'] != 10000) {

            return ['code' => 1, 'msg' => $result['code'] . '  ' . $result['msg']];
        }

        if (!isset($result['data']['state']) || $result['data']['state'] != 1) {

            return ['code' => 1, 'msg' => '实名认证结果不匹配'];
        }

        $code = $this->signature($name, 2);

        if (isset($code['code'])) {

            return ['code' => 1, 'msg' => $code['msg']];
        }

        $insert = [
            'uniacid' => $this->uniacid,
            'user_id' => $this->user_id,
            'coach_id' => $this->coach_id,
            'user_name' => $name,
            'id_code' => $idCode,
            'admin_id' => $this->admin_id,
            'create_time' => time(),
            'signature' => $code
        ];

        FxqIdCheck::insert($insert);

        return true;
    }

    /**
     * @Desc:  H5人脸认证
     * @param $name
     * @param $idCode
     * @return array
     * @throws \think\db\exception\DbException
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/16 15:14
     */
    public function faceCheckH5($name, $idCode, $isapp)
    {
        if (empty($name) || empty($idCode)) {

            return ['code' => 1, 'msg' => '姓名或身份证号为空'];
        }

        $is_check = FxqFaceCheck::where(['user_id' => $this->user_id, 'uniacid' => $this->uniacid, 'status' => 2])->count();

        if ($is_check) {

            return ['code' => 1, 'msg' => '已认证，请勿重复认证'];
        }

        $data = [
            "name" => $name,
            "idno" => $idCode,
            "companyId" => $this->user_id,
            "returnUrl" => urlencode('https://' . $_SERVER['HTTP_HOST'] . ($isapp == 2 ? '/h5/?#/technician/pages/fxq/success' : '/fxq.html')),
            "orderId" => orderCode()
        ];

        $result = $this->handle($data, \Constant::FACE_URL);

        if (empty($result)) {

            return ['code' => 1, 'msg' => '请求失败'];
        }

        if (!isset($result['code']) || $result['code'] != 10000) {

            return ['code' => 1, 'msg' => $result['code'] . '  ' . $result['msg']];
        }

        $insert = [
            'uniacid' => $this->uniacid,
            'user_id' => $this->user_id,
            'coach_id' => $this->coach_id,
            'admin_id' => $this->admin_id,
            'user_name' => $name,
            'id_code' => $idCode,
            'order_id' => $data['orderId'],
            'trade_no' => $result['tradeNo'],
            'status' => 1,
            'create_time' => time(),
            'check_type' => 1,
            'url' => $result['data']
        ];

        $re_id = FxqFaceCheck::insertGetId($insert);

        return ['re_id' => $re_id, 'url' => $result['data']];
    }

    /**
     * @Desc: 微信小程序人脸认证
     * @param $name
     * @param $idCode
     * @return array
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/16 18:16
     */
    public function faceCheckWxApp($name, $idCode)
    {
        if (empty($name) || empty($idCode)) {

            return ['code' => 1, 'msg' => '姓名或身份证号为空'];
        }

        $is_check = FxqFaceCheck::where(['user_id' => $this->user_id, 'uniacid' => $this->uniacid, 'status' => 2])->count();

        if ($is_check) {

            return ['code' => 1, 'msg' => '已认证，请勿重复认证'];
        }

        $data = [
            'name' => $name,
            'idCard' => $idCode,
            "inputType" => 4,
            "extra" => time(),
        ];

        $result = $this->handle($data, \Constant::E_TOKEN_URL);

        if (empty($result)) {

            return ['code' => 1, 'msg' => '请求失败'];
        }

        if (!isset($result['code']) || $result['code'] != 10000) {

            return ['code' => 1, 'msg' => $result['code'] . '  ' . $result['msg']];
        }

        $insert = [
            'uniacid' => $this->uniacid,
            'user_id' => $this->user_id,
            'coach_id' => $this->coach_id,
            'admin_id' => $this->admin_id,
            'user_name' => $name,
            'id_code' => $idCode,
            'order_id' => orderCode(),
            'trade_no' => $result['tradeNo'],
            'status' => 1,
            'create_time' => time(),
            'check_type' => 2,
            'eid_token' => $result['data']['eidToken']
        ];

        $re_id = FxqFaceCheck::insertGetId($insert);

        return ['re_id' => $re_id, 'token' => $result['data']['eidToken']];
    }

    /**
     * @Desc: 小程序人脸认证结果查询
     * @param $token
     * @return FxqFaceCheck|array
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/17 11:22
     */
    public function getEidResult($token)
    {
        $data = [
            'eidToken' => $token,
        ];

        $result = $this->handle($data, \Constant::E_RESULT_URL);

        if (empty($result)) {

            return ['code' => 1, 'msg' => '请求失败'];
        }

        if (!isset($result['code']) || $result['code'] != 10000) {

            return ['code' => 1, 'msg' => $result['code'] . '  ' . $result['msg']];
        }

        return FxqFaceCheck::where('eid_token', $token)->update(['status' => $result['data']['text']['errCode'] == 0 ? 2 : 3, 'live_rate' => $result['data']['text']['sim']]);
    }

    /**
     * @Desc: 文件转base64
     * @param $file
     * @return string
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/17 14:47
     */
    public function fileToBase64($file)
    {
        return base64_encode(file_get_contents($file));
    }

    /**
     * @Desc: word转pdf
     * @param $file
     * @return array|mixed
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/17 14:51
     */
    public function wordToPdf($file)
    {
        $base64 = $this->fileToBase64($file);

        $data = [
            'Base64' => $base64,
        ];

        $result = $this->handle($data, \Constant::WORD_TO_PDF_URL);

        if (empty($result)) {

            return ['code' => 1, 'msg' => '请求失败'];
        }

        if (!isset($result['code']) || $result['code'] != 1000) {

            return ['code' => 1, 'msg' => $result['code'] . '  ' . ($result['msg'] ?? '')];
        }

        return $result['data'];
    }

    /**
     * @Desc: 签章
     * @param $name
     * @param $type 1:公司签章，2：个人签章
     * @param $title
     * @param $seq_no
     * @return array|mixed
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/17 17:11
     */
    public function signature($name, $type = 1, $title = '', $seq_no = '')
    {
        $data = [
            "name" => $name,
            "color" => 0,
            "font" => 0,
            "type" => 0,
            "rtype" => 0,
        ];

        if ($type == 1) {

            $data['isRound'] = 0;

            if (!empty($title)) {

                $data['title'] = $title;
            }

            if (!empty($seq_no)) {

                $data['seqNo'] = $seq_no;
            }
        }

        $url = $type == 1 ? \Constant::COMPANY_SIGN_URL : \Constant::PERSONAL_SIGN_URL;

        $result = $this->handle($data, $url);

        if (empty($result)) {

            return ['code' => 1, 'msg' => '请求失败'];
        }

        if (!isset($result['code']) || $result['code'] != 10000) {

            return ['code' => 1, 'msg' => $result['code'] . '  ' . ($result['msg'] ?? '')];
        }

        $data = preg_replace('/^data:image\/[a-z0-9+\/]+;base64,/i', '', $result['data']);

        return $data;
    }

    /**
     * @Desc: 修改配置文件、word转为pdf、生成公司签章
     * @return true|array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/17 15:05
     */
    public function configUpdate($new, $old)
    {
        $update = [];

        //企业实名认证
        if ($new['company_name'] != $old['company_name'] || $new['company_id_no'] != $old['company_id_no'] || $new['corporation'] != $old['corporation'] || empty($old['is_check'])) {

            $code = $this->companyCheck($new['company_name'], $new['company_id_no'], $new['corporation']);

            if (isset($code['code'])) {

                return $code;
            }

            $update['is_check'] = 1;
        }

        //合同转pdf
        if ($new['contract'] != $old['contract'] || empty($old['contract_pdf_base64'])) {

            $code = $this->wordToPdf($new['contract']);

            if (isset($code['code'])) {

                return $code;
            }

            $update['contract_pdf_base64'] = $code;
        }

        //承诺书转pdf
        if (($new['commitment'] != $old['commitment'] || empty($old['commitment_pdf_base64'])) && !empty($new['commitment'])) {

            $code = $this->wordToPdf($new['commitment']);

            if (isset($code['code'])) {

                return $code;
            }

            $update['commitment_pdf_base64'] = $code;

            $img = $this->pdfToImg(base64ToPdf($code));

            if (isset($img['code'])) {

                return $img;
            }

            $update['commitment_pdf_img'] = $img;
        }

        if (empty($new['commitment'])) {

            $update['commitment_pdf_base64'] = '';
        }

        //生成公司签章
        if ($new['company_name'] != $old['company_name'] || $new['company_title'] != $old['company_title'] || $new['company_seq_no'] != $old['company_seq_no'] || empty($old['company_signature'])) {

            $code = $this->signature($new['company_name'], 1, $new['company_title'], $new['company_seq_no']);

            if (isset($code['code'])) {

                return $code;
            }

            $update['company_signature'] = $code;
        }

        if (!empty($update)) {

            $res = FxqConfig::where(['uniacid' => $this->uniacid, 'admin_id' => $this->admin_id])->update($update);

            if ($res === false) {

                return ['code' => 1, 'msg' => '编辑失败'];
            }
        }

        return true;
    }

    /**
     * @Desc: 企业实名认证
     * @param $name
     * @param $keyword
     * @param $legalPerson
     * @return array|true
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/22 11:39
     */
    public function companyCheck($name, $keyword, $legalPerson)
    {
        if (empty($name) || empty($keyword) || empty($legalPerson)) {

            return ['code' => 1, 'msg' => '参数为空'];
        }

        $data = [
            "name" => $name,
            "keyword" => $keyword,
            "legalPerson" => $legalPerson,
        ];

        $res = FxqCompanyCheck::where($data)->find();

        if ($res) {

            return true;
        }

        $result = $this->handle($data, \Constant::COMPANY_CHECK_URL);

        if (empty($result)) {

            return ['code' => 1, 'msg' => '请求失败'];
        }

        if (!isset($result['code']) || $result['code'] != 10000) {

            return ['code' => 1, 'msg' => $result['code'] . '  ' . $result['msg']];
        }

        if (!isset($result['data']['status']) || $result['data']['status'] != 1) {

            return ['code' => 1, 'msg' => '实名认证结果不匹配'];
        }

        $insert = [
            'uniacid' => $this->uniacid,
            'name' => $name,
            'keyword' => $keyword,
            'legalPerson' => $legalPerson,
            'create_time' => time()
        ];

        FxqCompanyCheck::insert($insert);

        return true;
    }

    /**
     * @Desc: 发送短信
     * @param $phone
     * @return array|true
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/17 18:27
     */
    public function sendCode($phone)
    {
        $code = mt_rand(100000, 999999);

        $data = [
            'phone' => $phone,

            'content' => '验证码为：' . $code . '请在页面完成验证，如果非本人操作请忽略，该验证码5分钟内有效'
        ];

        $result = $this->handle($data, \Constant::SEND_URL);

        if (empty($result)) {

            return ['code' => 1, 'msg' => '请求失败'];
        }

        if (!isset($result['code']) || $result['code'] != 10000) {

            return ['code' => 1, 'msg' => $result['code'] . '  ' . ($result['msg'] ?? '')];
        }

        $key = 'fxq_code';

        setCache($phone . $key, $code, 600, $this->uniacid);

        return true;
    }

    /**
     * @Desc: 企业签署合同
     * @param $contract_id
     * @return array|true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/18 16:00
     */
    public function companyContractSigning($contract_id)
    {
        $contract = FxqContract::getInfo(['id' => $contract_id]);

        $contract_file = FxqContractFile::getInfo(['contract_id' => $contract_id, 'type' => 1]);

        $data = [
            'contract' => [
                [
                    "url" => $contract_file['contract'],
                    "contract_code" => $contract_file['contract_no'],
                    "key" => "甲方（盖章）："
                ],
            ],
            "signDate" => [
                "enable" => 1,
                "colorType" => 1,
                "moveY" => 89,
                "moveX" => 0
            ],
            "type" => 0,
            "size" => 120,
            "move" => 0,
            "signers" => [
                "name" => $contract['company_name'],
                "customerType" => 1,
                "idno" => $contract['company_id_no'],
                "seal" => $contract['company_signature'],
                "height" => 400
            ]
        ];

        $result = $this->handle($data, \Constant::CONTRACT_DETAIL_URL);

        if (empty($result)) {

            return ['code' => 1, 'msg' => '请求失败'];
        }

        if (!isset($result['code']) || $result['code'] != 10000) {

            return ['code' => 1, 'msg' => $result['code'] . '  ' . ($result['msg'] ?? '')];
        }

        Db::startTrans();

        try {

            foreach ($result['data'] as $datum) {

                $pdf = base64ToPdf($datum['url']);

                $res = FxqContractFile::edit(['contract_no' => $datum['contract_code']], ['company_contract' => $datum['url'], 'company_view_contract' => $pdf, 'company_view_contract_img' => $this->pdfToImg($pdf)]);

                if ($res === false) {

                    throw new \Exception('编辑失败');
                }
            }

            $res = FxqContract::edit(['id' => $contract_id], ['status' => 2]);

            if ($res === false) {

                throw new \Exception('编辑失败');
            }

            Db::commit();

        } catch (\Exception $exception) {

            Db::rollback();

            return ['code' => 1, 'msg' => $exception->getMessage()];
        }

        return true;
    }

    /**
     * @Desc: 技师签署合同
     * @param $contract_id
     * @return array|true
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/21 15:09
     */
    public function coachContractSigning($contract_id)
    {

        $check_type = getConfigSetting($this->uniacid, 'fxq_check_type');

        if ($check_type == 1) {

            $check = FxqIdCheck::where(['user_id' => $this->user_id, 'uniacid' => $this->uniacid])->find();
        } else {

            $check = FxqFaceCheck::where(['user_id' => $this->user_id, 'uniacid' => $this->uniacid, 'status' => 2])->find();
        }

        if (empty($check)) {

            return ['code' => 1, 'msg' => '请先完成实名认证'];
        }

        $contract = FxqContract::getInfo(['id' => $contract_id]);

        $contract_file = FxqContractFile::getInfo(['contract_id' => $contract_id, 'type' => 1]);

        $commitment_file = FxqContractFile::getInfo(['contract_id' => $contract_id, 'type' => 2]);

        $contract_data = [
            [
                "url" => $contract_file['company_contract'],
                "contract_code" => $contract_file['contract_no'],
                "key" => "乙方（签字）："
            ]
        ];

        if (!empty($commitment_file)) {

            $contract_data[] = [
                "url" => $commitment_file['company_contract'],
                "contract_code" => $commitment_file['contract_no'],
                "key" => "可承诺人："
            ];
        }

        $data = [
            'contract' => $contract_data,
            "signDate" => [
                "enable" => 1,
                "colorType" => 1,
                "moveY" => 89,
                "moveX" => 0
            ],
            "type" => 0,
            "size" => 120,
            "move" => 0,
            "signers" => [
                "name" => $check['user_name'],
                "customerType" => 0,
                "idno" => $check['id_code'],
                "seal" => $check['signature'],
                "height" => 300
            ]
        ];

        $result = $this->handle($data, \Constant::CONTRACT_DETAIL_URL);

        if (empty($result)) {

            return ['code' => 1, 'msg' => '请求失败'];
        }

        if (!isset($result['code']) || $result['code'] != 10000) {

            return ['code' => 1, 'msg' => $result['code'] . '  ' . ($result['msg'] ?? '')];
        }

        Db::startTrans();

        try {

            foreach ($result['data'] as $key => $datum) {

                if ($key == 0) {

                    $transaction_id = '';
                } else {

                    $transaction_id = FxqContractFile::where([['contract_no', '=', $datum['contract_code']], ['transaction_id', '<>', '']])->value('transaction_id');
                }

                $full = $this->full(2, $datum['url'], $transaction_id);

                if (isset($full['code'])) {

                    throw new \Exception($full['msg']);
                }

                $pdf = base64ToPdf($datum['url']);

                $img = $this->pdfToImg($pdf);

                if (isset($img['code'])) {

                    return $img;
                }

                $res = FxqContractFile::edit(['contract_no' => $datum['contract_code']], ['coach_contract' => $datum['url'], 'coach_view_contract' => $pdf, 'coach_view_contract_img' => $img, 'transaction_id' => $full['transactionId'], 'hash' => $full['hash']]);

                if ($res === false) {

                    throw new \Exception('编辑失败');
                }
            }
            $start_time = time();

            $update = [
                'status' => 3,
                'coach_signature' => $check['signature'],
                'start_time' => $start_time,
                'end_time' => strtotime('+ ' . $contract['contract_years'] . ' years', $start_time),
                'user_name' => $check['user_name'],
                'id_code' => $check['id_code']
            ];

            $res = FxqContract::edit(['id' => $contract_id], $update);

            if ($res === false) {

                throw new \Exception('编辑失败');
            }

            Db::commit();

        } catch (\Exception $exception) {

            Db::rollback();

            return ['code' => 1, 'msg' => $exception->getMessage()];
        }

        return true;
    }

    /**
     * @Desc: 存证
     * @param $evidenceType int 类型 1文本存证 2文件存证
     * @param $content string|array
     * @param $transactionId string 存证ID，首次不传会返回
     * @param $phase string 存证阶段、文本存证时必传
     * @return array|mixed
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/22 15:16
     */
    public function full($evidenceType = 1, $content = '', $transactionId = '', $phase = '')
    {
        $data = [
            'evidenceType' => $evidenceType,
            'content' => $content
        ];

        if ($evidenceType == 1) {

            $data['phase'] = $phase;
        }

        if ($transactionId) {

            $data['transactionId'] = $transactionId;
        }

        $result = $this->handle($data, \Constant::FULL_URL);

        if (empty($result)) {

            return ['code' => 1, 'msg' => '请求失败'];
        }

        if (!isset($result['code']) || $result['code'] != 10000) {

            return ['code' => 1, 'msg' => $result['code'] . '  ' . ($result['msg'] ?? '')];
        }

        return $result['data'];
    }

    /**
     * @Desc: pdf转图片
     * @param $url
     * @return array|mixed
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/23 18:31
     */
    public function pdfToImg($url)
    {
        $data = [

            'fileUrl' => $url
        ];

        $result = $this->handle($data, \Constant::PDF_TO_IMG_URL);

        if (empty($result)) {

            return ['code' => 1, 'msg' => '请求失败'];
        }

        if (!isset($result['code']) || $result['code'] != 10000) {

            return ['code' => 1, 'msg' => $result['code'] . '  ' . ($result['msg'] ?? '')];
        }

        $data = $result['data']['pngs'];

        if (!empty($data)) {

            $data = array_column($data, 'png');
        } else {

            $data = [];
        }
        //todo 临时处理
//        $data = '["https://fxq-contract-api.oss-cn-qingdao.aliyuncs.com/API3.0-IMAGE/20241023/172967902886965.png?Expires=1792751028&OSSAccessKeyId=LTAI5t8sSdX4FtNxpvWDrtu7&Signature=Mtl%2FqV3cx%2BV4LHEdiN1l6XIUixI%3D","https://fxq-contract-api.oss-cn-qingdao.aliyuncs.com/API3.0-IMAGE/20241023/172967902886554.png?Expires=1792751028&OSSAccessKeyId=LTAI5t8sSdX4FtNxpvWDrtu7&Signature=rybEbIUqPe%2FpBNS0EXc6ls3TBK8%3D","https://fxq-contract-api.oss-cn-qingdao.aliyuncs.com/API3.0-IMAGE/20241023/172967902899848.png?Expires=1792751029&OSSAccessKeyId=LTAI5t8sSdX4FtNxpvWDrtu7&Signature=Fc%2B7g%2F77wpBPALVwUWnwg0%2BKnLY%3D","https://fxq-contract-api.oss-cn-qingdao.aliyuncs.com/API3.0-IMAGE/20241023/172967902884155.png?Expires=1792751028&OSSAccessKeyId=LTAI5t8sSdX4FtNxpvWDrtu7&Signature=xU3aChlhqmWTtfdkw%2FKbU%2BlEypM%3D","https://fxq-contract-api.oss-cn-qingdao.aliyuncs.com/API3.0-IMAGE/20241023/172967902898068.png?Expires=1792751028&OSSAccessKeyId=LTAI5t8sSdX4FtNxpvWDrtu7&Signature=YsrTQcis2keV6W%2B6sLsui0HK2uo%3D","https://fxq-contract-api.oss-cn-qingdao.aliyuncs.com/API3.0-IMAGE/20241023/172967902861427.png?Expires=1792751028&OSSAccessKeyId=LTAI5t8sSdX4FtNxpvWDrtu7&Signature=xTW3AsVPYknn4ZdHE1x%2Fyohcj7o%3D","https://fxq-contract-api.oss-cn-qingdao.aliyuncs.com/API3.0-IMAGE/20241023/172967902903289.png?Expires=1792751029&OSSAccessKeyId=LTAI5t8sSdX4FtNxpvWDrtu7&Signature=ti7XdQ9qAm715FYVYDJAObf828c%3D","https://fxq-contract-api.oss-cn-qingdao.aliyuncs.com/API3.0-IMAGE/20241023/172967902881223.png?Expires=1792751028&OSSAccessKeyId=LTAI5t8sSdX4FtNxpvWDrtu7&Signature=nmnQrTunpVYg07o0CoVsMtAKvKk%3D"]';
//
        $data = json_encode($data);

        return $data;
    }
}