<?php
declare(strict_types=1);

namespace longbingcore\wxcore;







//use adapay\test1;
//use adapay\test2;

use AdaPaySdk\Payment;
use app\adapay\model\Config;
use app\Common\order;
use think\Exception;

class Adapay{

    static protected $uniacid;

    protected $appid;

    protected $draw_cash_type;

    protected $url;

    public function __construct($uniacid)
    {
       self::$uniacid = $uniacid;

       $config_model = new Config();

       $config = $config_model->dataInfo(['uniacid'=>$uniacid]);

       $this->appid = $config['appid'];

       $this->draw_cash_type = $config['draw_cash_type'];

        include_once  EXTEND_PATH.'adapay/AdapaySdk/init.php';

        include_once  EXTEND_PATH.'adapay/AdapayDemo/config.php';

        $this->url = getConfigSetting($uniacid,'callback_url');

        $this->url = !empty($this->url)?$this->url:'https://'.$_SERVER['HTTP_HOST'];

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-08 14:50
     * @功能说明:
     */
    public function createPay($order_code,$pay_price,$opend_id,$pay_type='wx_pub',$pay_mode='delay'){

        include_once  EXTEND_PATH.'adapay/AdapaySdk/init.php';

        include_once  EXTEND_PATH.'adapay/AdapayDemo/config.php';

        $account = new \AdaPaySdk\Payment();

        # 支付设置
        $obj_params = array(

            'app_id'=> $this->appid,

            'order_no'=> $order_code,

            'pay_channel'=> $pay_type,
            //'time_expire'=> date("YmdHis", time()+86400),
            'pay_amt'=> sprintf("%01.2f", $pay_price),

           // 'pay_mode'=> $pay_mode,

            'goods_title'=> '服务',

            'goods_desc' => '服务',

            'description'=> 'description',
           // 'fee_mode'=> 'I',
            'device_info'=> ['device_p'=>"111.121.9.10"],

            'expend' => [

                'open_id' => $opend_id
            ],

           'notify_url' =>  $this->url.'/adapay/CallBack/payCallback'

        );

        if(!empty($pay_mode)){

            $obj_params['pay_mode'] = $pay_mode;
        }

        $account->create($obj_params);

        $res  =  $account->result;

        $data = json_decode($res[1],true);

        $data = json_decode($data['data'],true);

        return $data;

    }






    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-08 14:50
     * @功能说明:
     */
    public function createAliPay($order_code,$pay_price,$pay_type='wx_pub',$pay_mode='delay'){

        include_once  EXTEND_PATH.'adapay/AdapaySdk/init.php';

        include_once  EXTEND_PATH.'adapay/AdapayDemo/config.php';

        $account = new \AdaPaySdk\Payment();

        # 支付设置
        $obj_params = array(

            'app_id'=> $this->appid,

            'order_no'=> $order_code,

            'pay_channel'=> $pay_type,
            //'time_expire'=> date("YmdHis", time()+86400),
            'pay_amt'=> sprintf("%01.2f", $pay_price),

            // 'pay_mode'=> $pay_mode,

            'goods_title'=> '服务',

            'goods_desc' => '服务',

            'description'=> 'description',
            // 'fee_mode'=> 'I',
            'device_info'=> ['device_p'=>"111.121.9.10"],

            'expend' => [

              //  'open_id' => $opend_id
            ],

            'notify_url' =>  $this->url.'/adapay/CallBack/payCallback'

        );

        if(!empty($pay_mode)){

            $obj_params['pay_mode'] = $pay_mode;
        }

        $account->create($obj_params);

        $res  =  $account->result;

        $data = json_decode($res[1],true);

        $data = json_decode($data['data'],true);

        return $data;

    }


    public function orderInfo($payment_id)
    {
        $account =  new \AdaPaySdk\Payment();

        //$adaPay = new \AdaPay\AdaPay();

       // $adaPay->gateWayType = 'api';

        $obj_params = array(

            'payment_id'=> $payment_id
        );
        $account->query($obj_params);

        $res  =  $account->result;

        $data = json_decode($res[1],true);

        $data = json_decode($data['data'],true);

        return $data;

        print("查询支付对象".$obj->isError().'=>'.json_encode($obj->result)."\n");
        $this->assertEquals('succeeded', $obj->result['status']);
        // $this->assertTrue($account->isError());
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-11 15:43
     * @功能说明:创建确认订单
     */
    public function confirmorderCreate($payment_id,$order_code,$confirm_amt,$div_members,$text='',$fee_mode=1){

        $fee_mode = $fee_mode==1?'I':'O';
        # 初始化支付类
        $payment = new \AdaPaySdk\PaymentConfirm();
        # 支付确认参数设置
        $payment_params = array(

            "payment_id" => $payment_id,

            "order_no"   => $order_code,

            "confirm_amt"=> sprintf("%01.2f",$confirm_amt),

            "description"=> $text,

            "div_members"=> $div_members, //分账参数列表 默认是数组List

            'fee_mode'   => $fee_mode
        );

# 发起支付确认创建
        $payment->create($payment_params);

        $res = $payment->result;

        $data = json_decode($res[1],true);

        $data = json_decode($data['data'],true);

        return $data;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-11 15:56
     * @功能说明:订单退款
     */
    public function orderRefund($payment_id,$order_code,$price,$action = 'refundCallback'){

        $payment = new \AdaPaySdk\PaymentReverse();

        $payment_params = array(
            # 支付对象ID
            "payment_id"=> $payment_id,
            # 商户app_id
            "app_id"=> $this->appid,
            # 撤销订单号
            "order_no"=> $order_code,
            # 撤销金额
            "reverse_amt"=> sprintf("%01.2f", $price),
            # 通知地址
            'notify_url' => $this->url.'/adapay/CallBack/'.$action
//            # 撤销原因
//            "reason"=> "订单支金额错误",
//            # 扩展域
//            "expand"=> "",
//            # 设备信息
//            "device_info"=> "",
        );

# 发起支付撤销
        $payment->create($payment_params);

        $res  = $payment->result;

        $data = json_decode($res[1],true);

        $data = json_decode($data['data'],true);

        return $data;
# 对支付撤销结果进行处理
        if ($payment->isError()){
            //失败处理
            var_dump($payment->result);
        } else {
            //成功处理
            var_dump($payment->result);
        }


    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-11 15:56
     * @功能说明:订单退款
     */
    public function orderRefundNoAda($payment_id,$order_code,$price,$action='refundCallback'){

        $payment = new \AdaPaySdk\refund();

        $payment_params = array(
            # 支付对象ID
            "payment_id"=> $payment_id,
            # 撤销订单号
            "refund_order_no"=> $order_code,
            # 撤销金额
            "refund_amt"=> sprintf("%01.2f", $price),

            'notify_url' => $this->url.'/adapay/CallBack/'.$action
        );

        $payment->create($payment_params);

        $res  = $payment->result;

        $data = json_decode($res[1],true);

        $data = json_decode($data['data'],true);

        return $data;
    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-10 17:45
     * @功能说明:创建个人分账对象
     */
    public function createUserObj($member_id){
        # 初始化用户对象类
        $member = new \AdaPaySdk\Member();

        $member_params = array(
            # app_id
            'app_id'   => $this->appid,
            # 用户id
            'member_id'=> $member_id,
        );
        $member->create($member_params);

        $res = $member->result;

        $data = json_decode($res[1],true);

        $data = json_decode($data['data'],true);

        return $data;
    }


    /**
     * @param $data
     * @功能说明:创建企业分账对象
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-11 12:02
     */
    public function createCompanyObj($data,$type,$http,$bank_info){

        $member = new \AdaPaySdk\CorpMember();

        if(isset($data['attach_file'])){

            $http = strstr($data['attach_file'],'https://')==false?'':'https://';

            $data['attach_file'] = realpath(str_replace($http.$_SERVER['HTTP_HOST'].'/','',$data['attach_file']));
        }

        $member_params = array(
            # app_id
            "app_id"=> $this->appid,
            # 商户用户id
            "member_id"=> $data['member_id'],
            # 订单号
            "order_no"=> $data['order_no'],
            # 企业名称
            "name"=> $data['name'],
            # 省份
            "prov_code"=> $data['prov_code'],
            # 地区
            "area_code"=> $data['area_code'],
            # 统一社会信用码
            "social_credit_code"=> $data['social_credit_code'],

            "social_credit_code_expires"=> $data['social_credit_code_expires'],
            # 经营范围
            "business_scope"=> $data['business_scope'],
            # 法人姓名
            "legal_person"=> $data['legal_person'],
            # 法人身份证号码
            "legal_cert_id"=> $data['legal_cert_id'],
            # 法人身份证有效期
            "legal_cert_id_expires"=> $data['legal_cert_id_expires'],
            # 法人手机号
            "legal_mp"=> $data['legal_mp'],
            # 企业地址
            "address"=> $data['address'],
//            # 邮编
//            "zip_code"=> "企业地址测试",
//            # 企业电话
//            "telphone"=> "1234567890",
//            # 企业邮箱
//            "email"=> "1234567890@126.com",
            # 上传附件
            "attach_file"=> new \CURLFile($data['attach_file']),

            "notify_url"=> $this->url.'/adapay/CallBack/companyCallback',
            # 银行代码
            "bank_code"=> !empty($bank_info['bank_code'])?$bank_info['bank_code']:'',

            "bank_acct_type"=> !empty($bank_info['bank_acct_type'])?$bank_info['bank_acct_type']:2,

            "card_no"=> !empty($bank_info['card_id'])?$bank_info['card_id']:'',

            "card_name"=> !empty($bank_info['card_name'])?$bank_info['card_name']:'',

        );

        if($type==1){

            $member->create($member_params);
        }else{

            $member->update($member_params);
        }

        $res = $member->result;

        $data = json_decode($res[1],true);

        $data = json_decode($data['data'],true);

        return $data;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-10 18:01
     * @功能说明:创建账户信息
     */
    public function createAccountsObj($data){

        $account = new \AdaPaySdk\SettleAccount();

        $account_params = array(
            "app_id"   => $this->appid,
            "member_id"=> $data['member_id'],
            "channel"  => "bank_account",
            "account_info"=> [

                "card_id"   => $data['card_id'],

                "card_name" => $data['card_name'],

                "tel_no"    => $data['tel_no'],

                "bank_acct_type" =>$data['bank_acct_type'],
            ]
        );
        //对公
        if($data['bank_acct_type']==1){

            $account_params['account_info']['bank_code'] = $data['bank_code'];

            $account_params['account_info']['prov_code'] = $data['prov_code'];

            $account_params['account_info']['area_code'] = $data['area_code'];

        }else{
            //对私
            $account_params['account_info']['cert_id'] = $data['cert_id'];

            $account_params['account_info']['cert_type'] = "00";

        }

        $account->create($account_params);

        $res = $account->result;

        $data = json_decode($res[1],true);

        $data = json_decode($data['data'],true);

        return $data;
    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-11 14:51
     * @功能说明:删除结算账户
     */
    public function delAccountsObj($data){

        $account = new \AdaPaySdk\SettleAccount();

        $account_params = array(

            "app_id"   => $this->appid,

            "member_id"=> $data['member_id'],

            "settle_account_id"=> $data['settle_account_id'],

        );
        $account->delete($account_params);

        $res = $account->result;

        $data = json_decode($res[1],true);

        $data = json_decode($data['data'],true);

        return $data;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-17 16:45
     * @功能说明:提现
     */
    public function drawCash($order_code,$member_id,$cash,$uniacid=666){

        $drawcash = new \AdaPaySdk\Drawcash();
        $drawcash_params = array(
            'order_no'=> $order_code,
            'app_id'=> $this->appid,
            'cash_type'=> $this->draw_cash_type,
            'cash_amt'=> sprintf("%01.2f", $cash),
            'member_id'=> $member_id,
            'notify_url'=> $this->url.'/adapay/CallBack/walletCallback'
        );

        $drawcash->create($drawcash_params);

        $res = $drawcash->result;

        $data = json_decode($res[1],true);

        $data = json_decode($data['data'],true);

        return $data;
# 对账户取现结果进行处理
        if ($drawcash->isError()){
            //失败处理
            dump($drawcash->result);exit;
        } else {
            //成功处理
            var_dump($drawcash->result);
        }

    }



    public function drawCashInfo($order_code){

        $drawcash = new \AdaPaySdk\Drawcash();
        $cash_params = array(
            'order_no'=> $order_code,
        );
# 账户取现
        $drawcash->query($cash_params);

        $res = $drawcash->result;

        $data = json_decode($res[1],true);

        $data = json_decode($data['data'],true);

        return $data;
# 对账户取现结果进行处理
        if ($drawcash->isError()){
            //失败处理
            var_dump($drawcash->result);
        } else {
            //成功处理
            var_dump($drawcash->result);
        }
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-18 15:56
     * @功能说明:查询用户余额
     */
    public function settleAccount($member_id,$adapay_id){

        $account = new \AdaPaySdk\SettleAccount();

        $account_params = array(
            'app_id'=> $this->appid,
            'member_id'=> $member_id,
           // 'settle_account_id'=> $adapay_id
        );

        if(!empty($adapay_id)){

            $account_params['settle_account_id'] =    $adapay_id;
        }
# 查询账户余额
        $account->balance($account_params);

        $res = $account->result;

        $data = json_decode($res[1],true);

        $data = json_decode($data['data'],true);

        return $data;
    }


    /**
     * @param $member_id
     * @param $cash
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-09-20 18:41
     */
    public function balancePay($member_id,$cash){

        $account = new \AdaPaySdk\AdaPayCommon();

        $account_params = array(

            'adapay_func_code' => 'settle_accounts.balancePay',

            'app_id'           => $this->appid,

            'out_member_id'    => 0,

            'in_member_id'     => $member_id,

            'order_no'         => orderCode(),

            'trans_amt'        => sprintf("%01.2f", $cash),

            'goods_title'      => 'balancePay',

            'goods_desc'       => 'balancePay',

           // 'fee_mode'         => 'O'

        );

        $account->requestAdapay($account_params);

        $res = $account->result;

        $data = json_decode($res[1],true);

        $data = json_decode($data['data'],true);

        return $data;

    }





    public function settleDetails($member_id,$settle_account_id){

        $account = new \AdaPaySdk\SettleAccount();

        $account_params = array(
            "app_id"=> $this->appid,
            "member_id"=> $member_id,
            "settle_account_id"=> $settle_account_id,
            "begin_date"=> date('Ymd',time()-86400),
            "end_date"=> date('Ymd',time())
        );

# 查询结算账户
        $account->query($account_params);

        $res = $account->result;

        $data = json_decode($res[1],true);

        $data = json_decode($data['data'],true);

      //  dump(date('md',time()-86400));exit;
        return $data;
# 对查询结算账户结果进行处理
        if ($account->isError()){
            //失败处理
            var_dump($account->result);
        } else {
            //成功处理
            var_dump($account->result);
        }

    }










}