<?php
declare(strict_types=1);

namespace longbingcore\heepay;


include 'Util.php';
include 'Summary.php';
//include 'Sm2.php';
//include 'sm2/src/sm2_demo.php';

use app\heepay\model\Config;

use function Util\ASCIIAZ;
use function Util\DeleteValue;
use function Util\PostJson;
use function Util\RSA2sign;
use function Util\PicToTow;
use function Util\Postformdata;
use function Summary\RSA2signs;
use function Summary\RSA2data;
use function Summary\rsaDecrypt;
use function Summary\PostJsons;

//use function Sm2\test;

class HeePay{

    static protected $uniacid;

    protected $appid;

    protected $draw_cash_type;

    protected $config;

    public function __construct($uniacid)
    {
        self::$uniacid = $uniacid;

        if(empty($this->config)){

            $config_model = new Config();

            $this->config = $config_model->dataInfo(['uniacid'=>$uniacid]);
        }

    }


    /**
     * @param $number
     * @param $cash
     * @功能说明:支付
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-04-23 11:52
     */
    public function pay($number,$cash,$open_id,$type){
        //获取时间戳
        $currentTime = date("YmdHis");

        $config_model = new \app\massage\model\Config();

        $config = $config_model->dataInfo(['uniacid'=>self::$uniacid]);

        $String = array(
           // "is_guarantee" => '1',
            'wx_openid' => $open_id,
            'wx_sub_appid' => $config['web_app_id'],
        );
        $jsonString =json_encode($String,JSON_UNESCAPED_UNICODE);
        $encodedJsonString = iconv('utf-8', 'gbk', $jsonString);
        $Newmeta_option = urlencode(base64_encode($encodedJsonString));
        $version = '1';//版本号
        $is_phone = '1';//是否使用手机触屏版，1=是
        $is_frame = '1';//1=是
        $pay_type = '30';//支付类型
       // $agent_id = '2208181';//商户编号
        $agent_id = $this->config['agent_id'];//商户编号
      //  $ref_agent_id = '2210979';//二级商户号（集团商户模式传参），传了必须参与签名放在key后面
        $ref_agent_id = $this->config['ref_agent_id'];//二级商户号（集团商户模式传参），传了必须参与签名放在key后面
        $agent_bill_id = $number;//商户系统内部的订单号（要保证唯一）
        $agent_bill_time = $currentTime;//提交单据的时间yyyyMMddHHmmss
        $pay_amt = $cash;//订单总金额
        $notify_url = 'https://'.$_SERVER['HTTP_HOST'].'/heepay/CallBack/payCallback';//异步通知地址

        if($type=='Massage' || $type =='MassageUp'){

            $return_url = 'https://'.$_SERVER['HTTP_HOST'].'/h5/#/pages/order';//支付后返回的商户显示页面
            
        }elseif($type=='Balance' || $type=='Balancediscount'){

            $return_url = 'https://'.$_SERVER['HTTP_HOST'].'/h5/#/user/pages/stored/list?paysuc=1';//支付后返回的商户显示页面

        }elseif ($type=='AgentRecharge'){

            $return_url = 'https://'.$_SERVER['HTTP_HOST'].'/h5/#/agent/pages/index?paysuc=1';//支付后返回的商户显示页面

        }elseif ($type=='ResellerPay'){

            $return_url = 'https://'.$_SERVER['HTTP_HOST'].'/h5/#/user/pages/distribution/income?paysuc=1';//支付后返回的商户显示页面

        }elseif ($type=='Memberdiscount'){

            $return_url = 'https://'.$_SERVER['HTTP_HOST'].'/h5/#/memberdiscount/pages/index?paysuc=1';//支付后返回的商户显示页面

        }elseif ($type=='PartnerOrder'){

            $return_url = 'https://'.$_SERVER['HTTP_HOST'].'/h5/#/pages/mine';//支付后返回的商户显示页面

        }elseif ($type=='PartnerOrderJoin'){

            $return_url = 'https://'.$_SERVER['HTTP_HOST'].'/h5/#/pages/mine';//支付后返回的商户显示页面

        }else{
            $return_url = 'https://'.$_SERVER['HTTP_HOST'].'/h5';//支付后返回的商户显示页面
        }
        $user_ip = getIP();//用户真实的IP 127_127_12_12
        $goods_name = '服务费';//商品名称，不能为空（不参加签名）
        $goods_num = '1';//产品数量（不参加签名）
        $remark = $type;//商户自定义，原样返回,可以为空。（不参加签名）
        $goods_note = '服务费';//支付说明，（不参加签名）
        $expire_time = '100';//订单过期相对时间，单位分钟，最低1分钟，最高4320分钟。
        $meta_option = $Newmeta_option;//is_guarantee=1代表分润单（选填） 如果需要分润，此参数必填；不调分润资金不会结算，需要base64，再urlencode（不需要参与签名）
       // $key = '4AFCB5A2A31240A8A56F9B4F';//密钥
        $key = $this->config['pay_key'];//密钥

        $GBKgoods_name = iconv('UTF-8', 'GBK', $goods_name);
        $New_goods_name = urlencode($GBKgoods_name);
        $GBKremark = iconv('UTF-8', 'GBK', $remark);
        $New_remark = urlencode($GBKremark);
        $GBKgoods_note = iconv('UTF-8', 'GBK', $goods_note);
        $New_goods_note = urlencode($GBKgoods_note);
        if (empty($ref_agent_id)){
            $data = "version=$version&agent_id=$agent_id&agent_bill_id=$agent_bill_id&agent_bill_time=$agent_bill_time&pay_type=$pay_type&pay_amt=$pay_amt&notify_url=$notify_url&return_url=$return_url&user_ip=$user_ip&key=$key";
            $sign = md5($data);
            $param= "version=$version&agent_id=$agent_id&is_phone=$is_phone&is_frame=$is_frame&agent_bill_id=$agent_bill_id&agent_bill_time=$agent_bill_time&pay_type=$pay_type&pay_amt=$pay_amt&notify_url=$notify_url&return_url=$return_url&user_ip=$user_ip&sign=$sign&goods_name=$New_goods_name&goods_num=$goods_num&remark=$New_remark&goods_note=$New_goods_note&meta_option=$meta_option";
        }else{
            $data = "version=$version&agent_id=$agent_id&agent_bill_id=$agent_bill_id&agent_bill_time=$agent_bill_time&pay_type=$pay_type&pay_amt=$pay_amt&notify_url=$notify_url&return_url=$return_url&user_ip=$user_ip&key=$key&ref_agent_id=$ref_agent_id";
            $sign = md5($data);
            $param= "version=$version&agent_id=$agent_id&is_phone=$is_phone&is_frame=$is_frame&agent_bill_id=$agent_bill_id&agent_bill_time=$agent_bill_time&pay_type=$pay_type&pay_amt=$pay_amt&notify_url=$notify_url&return_url=$return_url&user_ip=$user_ip&sign=$sign&goods_name=$New_goods_name&goods_num=$goods_num&remark=$New_remark&goods_note=$New_goods_note&ref_agent_id=$ref_agent_id&meta_option=$meta_option";
        }
        //请求地址
        $url = 'https://pay.heepay.com/Payment/Index.aspx';
        // 构建请求上下文
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'content' => $param
            )
        );
        $context = stream_context_create($options);
        //发送 POST 请求
        $response = file_get_contents($url, false, $context);

        if ($response === false) {
            echo 'POST request failed';
        } else {
            $responsed=iconv('GBK', 'UTF-8', $response);

            return $this->matchData($responsed);

            return $responsed;
        }
    }


    /**
     * @param $yourString
     * @功能说明:截取有用数据
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-04-29 10:21
     */
    public function matchData($yourString){

        preg_match('/{"appId" : "(.*?)","timeStamp" : "(.*?)","nonceStr" : "(.*?)","package" : "(.*?)","signType" : "(.*?)","paySign" : "(.*?)"}/', $yourString, $matches);

        return json_decode($matches[0],true);
    }


    /**
     * @param $filePath
     * @param $type
     * @功能说明:文件上传
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-04-29 10:21
     */
    public function fileUpdate($filePath,$type){

        // $filePath = 'D:/bizhi/1.jpg';
        //图片转二进制处理
        $file_signd = PicToTow($filePath);
        $t=time();
        $timestampd=date('Y-m-d H:i:s', $t);
        $app_id=$this->config['appid'];
        $charset='utf-8';
        $credential_type=$type;
        $file_sign=$file_signd;
        $method='customer.media.upload';
        $format='json';
        $sign_type='RSA2';
        $version='1.0';
        $timestamp=$timestampd;
        $arrayData = array(
            'app_id' => $app_id,
            'credential_type' => $credential_type,
            'charset' => $charset,
            'method' => $method,
            'format' => $format,
            'sign_type' => $sign_type,
            'version' => $version,
            'timestamp' => $timestamp,
            'file_sign' => $file_sign
        );
        $signatureContent = ASCIIAZ($arrayData);

        $sign = RSA2sign($signatureContent,$this->config['private_key']);

        // 构造 POST 请求数据
        $post_string = array(
            'charset' => $charset,
            'credential_type' => $credential_type,
            'method' => $method,
            'format' => $format,
            'sign' => $sign,
            'file_sign' => $file_sign,
            'app_id' => $app_id,
            'sign_type' => $sign_type,
            'version' => $version,
            'timestamp' => $timestamp,
            'file_content'   => new \CURLFile($filePath)  // 添加文件字段，使用 CURLFile 类
        );

        // 目标 URL
        $url = 'https://openapi.heepay.com/customer-api/customer.media.upload';  //调用接口的平台服务地址

        $data =  Postformdata($url,$post_string);

        return json_decode($data,true);

    }


    /**
     * @param $Oldbiz_content
     * @功能说明:添加账号
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-04-22 14:33
     */
    public function addUser($Oldbiz_content){

        //入驻申请业务参数 文档里必传字段必须传
//        $Oldbiz_content = array(
//            "if_sign" => '1',
//            "business_developer" => '张三',                //商户的业务员
//            "customer_type" => '1',                       //1=企业 2=个体工商户 3=小微
//            "register_type" => 'HEEPAY_MERCH',            //账户类型（默认收单）HEEPAY_MERCH=收单商户；HEEPAY_BALANCE=钱包账户
//            "customer_name" => '小二黑科技有限公司',        //客户名称
//            "customer_short_name" => '小锤',               //客户简称
//            "customer_phone" => '12312312312',            //客服电话
//            "licence_code" => '98665112MA7HB5C80P',       //营业执照编码
//            "licence_valid_type" => '1',                   //营业执照有效期类型  1=定期,2=长期
//            "licence_valid_begin" => '2021-05-21',         //营业执照生效日期
//            "licence_valid_end" => '2033-05-21',           //营业执照失效日期
//            "licence_region_code" => '110108',             //营业执照区划编码
//            "licence_address" => '北京市海淀区中关村',       //营业执照注册地址
//            "business_category_code" => '1035',            //经营类目编码
//            "legal_name" => '张三',                        //法人姓名
//            "legal_id_card" => '610321192303032321',       //法人证件号
//            "legal_id_card_valid_type" => '1',             //法人有效期类型  1=定期,2=长期
//            "legal_id_card_valid_begin" => '2023-10-26',   //法人证件生效日期
//            "legal_id_card_valid_end" => '2033-10-26',      //法人证件失效日期
//            "legal_mobile" => '18312344321',                //法人手机号
//            "contact_name" => '李四',                       //联系人名称
//            "contact_id_card" => '610321199303203921',      //联系人证件号
//            "contact_id_card_valid_type" => '1',           //联系人证件有效期类型 1=定期,2=长期
//            "contact_id_card_valid_begin" => '2021-05-21',  //联系人证件生效日期
//            "contact_id_card_valid_end" => '2041-05-21',    //联系人证件失效日期
//            "contact_mobile" => '13943211234',              //联系人手机号
//            "contact_email" => '1234512421431211@qq.com',      //联系人邮箱/创建商户登录账号
//            "bank_account_type" => '1',                     //结算银行账户类型
//            "bank_account_name" => '小二黑科技有限公司',      //结算银行账户名称
//            "bank_card_no" => '61021234123',                //结算银行卡号
//            "bank_name" => '建设银行',                       //结算开户行名称
//            "bank_region_code" => '110100',                  //结算银行所属区划编码
//            "withdraw_type" => '1',                          //提现类型 1=手动提现,2=自动提现
//            "licence_img" => '/public/01/20231208/ff3b85802ff9fced6371b3b61c2c0548.png',//营业执照图片路径
//            "company_build_img" => '/public/01/20231208/ff3b85802ff9fced6371b3b61c2c0548.png',//公司大楼图片路径
//            "company_front_img" => '/public/01/20231208/ff3b85802ff9fced6371b3b61c2c0548.png',//公司前台图片路径
//            "shop_env_img" => '/public/01/20231208/ff3b85802ff9fced6371b3b61c2c0548.png',//店内场景照图片路径
//            "reg_certificate_img" => '/public/01/20231208/ff3b85802ff9fced6371b3b61c2c0548.png',//开户许可证图片路径
//            "legal_id_card_front_img" => '/public/01/20231208/ff3b85802ff9fced6371b3b61c2c0548.png',//法人身份证人像面图片路径
//            "legal_id_card_back_img" => '/public/01/20231208/ff3b85802ff9fced6371b3b61c2c0548.png',//法人身份证国徽面图片路径
//            "contact_id_card_front_img" => '/public/01/20231208/ff3b85802ff9fced6371b3b61c2c0548.png',// 业务员身份证人像面图片路径
//            "contact_id_card_back_img" => '/public/01/20231208/ff3b85802ff9fced6371b3b61c2c0548.png',//业务员身份证国徽面图片路径
//        );
       //240422173714039422
        $app_id=$this->config['appid'];
        $charset='utf-8';
        $biz_content=json_encode($Oldbiz_content,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        $method='customer.entry.apply';
        $format='json';
        $sign_type='RSA2';
        $version='1.0';
        $notify_url='https://'.$_SERVER['HTTP_HOST'].'/heepay/CallBack/companyCallback';;
        /*
        生成待签名串方法
        签名通用步骤第一步，设所有发送或者接收到的数据为集合M，将集合M内非空参数值的参数按照参数名ASCII码从小到大排序（字典序），使用URL键值对的格式（即key1=value1&key2=value2…）拼接成字符串stringA 特别注意以下重要规则：
        1.参数名ASCII码从小到大排序（字典序）；
        2.如果参数的值为空不参与签名；
        3.参数名区分大小写；第二步，使用商户RSA2私钥对待签名字符串stringA进行签名，得到签名sign
        */
        $arrayData = array(
            'app_id' => $app_id,
            'biz_content' => $biz_content,
            'charset' => $charset,
            'method' => $method,
            'format' => $format,
            'sign_type' => $sign_type,
            'version' => $version,
            'notify_url'=>$notify_url
        );
        $signatureContent = ASCIIAZ($arrayData);
        $sign = RSA2sign($signatureContent,$this->config['private_key']);
        // 构建请求的JSON数据
        $request_data = array(
            "charset" => $charset,
            "biz_content" => $biz_content,
            "method" => $method,
            "format" => $format,
            "sign" => $sign,
            "sign_type" => $sign_type,
            "version" => $version,
            "app_id" => $app_id,
            'notify_url'=>$notify_url
        );
       //将数组转换为JSON字符串
        $jsonString = json_encode($request_data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        $arrayData1 = DeleteValue($jsonString);
        $Posturl = "https://openapi.heepay.com/customer-api/gateway";
        $jsonData = $arrayData1;
        $data = PostJson($Posturl,$jsonData);
        return json_decode($data,true);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-04-22 17:38
     * @功能说明:修改入住
     */
    public function updateUser($Oldbiz_content=[]){

//        $Oldbiz_content = array(
//            "apply_no" => '240422173714039422',//申请编号
//            "contact_email" => '123451242143223412345@qq.com',//登录邮箱
//            "bank_front_img" => '/public/01/20231208/ff3b85802ff9fced6371b3b61c2c0548.png',
//        );
        $app_id=$this->config['appid'];
        $charset='utf-8';
        $biz_content=json_encode($Oldbiz_content,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        $method='customer.entry.modify';
        $format='json';
        $sign_type='RSA2';
        $version='1.0';
        $notify_url='https://'.$_SERVER['HTTP_HOST'].'/heepay/CallBack/companyCallback';
        /*
        生成待签名串方法
        签名通用步骤第一步，设所有发送或者接收到的数据为集合M，将集合M内非空参数值的参数按照参数名ASCII码从小到大排序（字典序），使用URL键值对的格式（即key1=value1&key2=value2…）拼接成字符串stringA 特别注意以下重要规则：
        1.参数名ASCII码从小到大排序（字典序）；
        2.如果参数的值为空不参与签名；
        3.参数名区分大小写；第二步，使用商户RSA2私钥对待签名字符串stringA进行签名，得到签名sign
        */
        $arrayData = array(
            'app_id' => $app_id,
            'biz_content' => $biz_content,
            'charset' => $charset,
            'method' => $method,
            'format' => $format,
            'sign_type' => $sign_type,
            'version' => $version,
            'notify_url'=>$notify_url
        );
        $signatureContent = ASCIIAZ($arrayData);
        $sign = RSA2sign($signatureContent,$this->config['private_key']);
        // 构建请求的JSON数据
        $request_data = array(
            "charset" => $charset,
            "biz_content" => $biz_content,
            "method" => $method,
            "format" => $format,
            "sign" => $sign,
            "sign_type" => $sign_type,
            "version" => $version,
            "app_id" => $app_id,
            'notify_url'=>$notify_url
        );
        // 将数组转换为JSON字符串
        $jsonString = json_encode($request_data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        $arrayData1 = DeleteValue($jsonString);
        //输出请求数据
        $Posturl = "https://openapi.heepay.com/customer-api/gateway/";
        $jsonData = $arrayData1;
        $data =  PostJson($Posturl,$jsonData);
        return json_decode($data,true);
    }


    /**
     * @param $order_code
     * @param $cash_fen
     * @param $sub_account_id
     * @功能说明:申请提现
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-04-22 18:42
     */

    public function wallet($order_code,$cash_fen,$sub_account_id){

        $version = '1';//版本号
        $agent_id = $this->config['agent_id'];//大商户号7位数ID
        $out_trade_no = $order_code;//商户订单号
        $note = '提现';//提现备注
        $notify_url = 'https://'.$_SERVER['HTTP_HOST'].'/heepay/CallBack/walletCallback';

        $Beforesign = array(
            "version" => $version,
            "agent_id" => $agent_id,
            "out_trade_no" => $out_trade_no,
            "sub_account_id" => $sub_account_id,
            "cash_fen" => $cash_fen*100,
            "note" => $note,
            "notify_url" => $notify_url,
        );
        $signatureContent = json_encode($Beforesign,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);//组装签名串

        $Aftersign= RSA2signs($signatureContent,$this->config['private_key']);//签名结果

        $data = array(
            "sign" => $Aftersign,//私钥签名结果
            "body" => $signatureContent,
        );

        $Befordata = json_encode($data);//组装加密前数据
        $Afterdata = RSA2data($Befordata,$this->config['c_public_key']);
        $request_data = array(
            "agentid" => $agent_id,
            "businessContext" => $Afterdata,
        );
        $jsonString = json_encode($request_data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        $Posturl = "https://www.heepay.com/API/Agent/AgentSubCash.aspx";

        $jsonData = $jsonString;

        $Responsedata = PostJsons($Posturl,$jsonData);//获取响应参数

        $beforedecryption = json_decode($Responsedata, true);

        $Decrypteddata = rsaDecrypt(base64_decode($beforedecryption["businessContext"]),$this->config['private_key']);

        return json_decode($Decrypteddata,true);

        echo $Decrypteddata ;//解密结果

    }


    /**
     * @param $input
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-04-23 17:07
     */
    public function payCallback($input){

        $result=$input['result'];
        $pay_message=$input['pay_message'];
        $agent_id=$input['agent_id'];
        $jnet_bill_no=$input['jnet_bill_no'];
        $agent_bill_id=$input['agent_bill_id'];
        $pay_type=$input['pay_type'];
        $pay_amt=$input['pay_amt'];
        $remark=$input['remark'];
        $deal_time=$input['deal_time'];
        $bank_card_type=$input['bank_card_type'];
        $bank_card_owner_type=$input['bank_card_owner_type'];
        $returnSign=$input['sign'];
        //解码remark  gbk格式  urldecode解码
        // $remark="%c9%cc%bb%a7%d7%d4%b6%a8%d2%e5%2c%d4%ad%d1%f9%b7%b5%bb%d8%2c%bf%c9%d2%d4%ce%aa%bf%d5";
        $remark = iconv("gbk","utf-8",urldecode($remark));;
        //商户的KEY
        $key = $this->config['pay_key'];
        $signStr='';
        $signStr  = $signStr . 'result=' . $result;
        $signStr  = $signStr . '&agent_id=' . $agent_id;
        $signStr  = $signStr . '&jnet_bill_no=' . $jnet_bill_no;
        $signStr  = $signStr . '&agent_bill_id=' . $agent_bill_id;
        $signStr  = $signStr . '&pay_type=' . $pay_type;
        $signStr  = $signStr . '&pay_amt=' . $pay_amt;
        $signStr  = $signStr .  '&remark=' . $remark;
        $signStr = $signStr . '&key=' . $key;
        $sign=$returnSign;
        $Verify_Sign=md5($signStr);
        //请确保 notify.php 和 return.php 判断代码一致
        if($sign==$Verify_Sign){   //比较MD5签名结果 是否相等 确定交易是否成功  成功返回ok 否则返回error
            return true;
        }else{

            return false;
        }
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-04-24 14:03
     * @功能说明:用户入住结果查询
     */
    public function userResInfo($apply_no){
//入驻结果查询业务参数
        $Oldbiz_content = array(
            "apply_no" => $apply_no,//申请编号
        );
        /*
        公共请求参数
        */
        $app_id=$this->config['appid'];
        $charset='utf-8';
        $biz_content=json_encode($Oldbiz_content,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        $method='customer.entry.query';
        $format='json';
        $sign_type='RSA2';
        $version='1.0';
        $notify_url='http://aidj.simaguo.com';
        /*
        生成待签名串方法
        签名通用步骤第一步，设所有发送或者接收到的数据为集合M，将集合M内非空参数值的参数按照参数名ASCII码从小到大排序（字典序），使用URL键值对的格式（即key1=value1&key2=value2…）拼接成字符串stringA 特别注意以下重要规则：
        1.参数名ASCII码从小到大排序（字典序）；
        2.如果参数的值为空不参与签名；
        3.参数名区分大小写；第二步，使用商户RSA2私钥对待签名字符串stringA进行签名，得到签名sign
        */
        $arrayData = array(
            'app_id' => $app_id,
            'biz_content' => $biz_content,
            'charset' => $charset,
            'method' => $method,
            'format' => $format,
            'sign_type' => $sign_type,
            'version' => $version,
            'notify_url'=>$notify_url
        );
        $signatureContent = ASCIIAZ($arrayData);

        $sign = RSA2sign($signatureContent,$this->config['private_key']);
// 构建请求的JSON数据
        $request_data = array(
            "charset" => $charset,
            "biz_content" => $biz_content,
            "method" => $method,
            "format" => $format,
            "sign" => $sign,
            "sign_type" => $sign_type,
            "version" => $version,
            "app_id" => $app_id,
            'notify_url'=>$notify_url
        );
// 将数组转换为JSON字符串
        $jsonString = json_encode($request_data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        $arrayData1 = DeleteValue($jsonString);

        $Posturl = "https://openapi.heepay.com/customer-api/gateway/";
        $jsonData = $arrayData1;

        $data = PostJson($Posturl,$jsonData);

        return json_decode($data,true);
    }


    /**
     * @param $pay_order_code
     * @param $refund_order_code
     * @param $cash
     * @功能说明:退款
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-04-24 16:08
     */
    public function refundOrder($pay_order_code,$refund_order_code,$cash,$action = 'refundCallback'){

        $currentTime = date("YmdHis");
        $version = '1';//版本号
        $agent_id = $this->config['agent_id'];//商户编号
        $agent_bill_id = $pay_order_code;//商户系统内部的订单号（要保证唯一）
        $refund_details = "$pay_order_code,$cash,$refund_order_code";//商户原支付单号，金额，商户退款单号 分割最多支持50笔
        $agent_bill_time = $currentTime;//提交单据的时间yyyyMMddHHmmss
        $notify_url = 'https://'.$_SERVER['HTTP_HOST'].'/heepay/CallBack/'.$action;//异步通知地址
        $key = $this->config['refund_key'];//密钥
        // 请求参数（使用 & 符号拼接）
        if (empty($refund_details)){
            $data = "agent_bill_id=$agent_bill_id&agent_id=$agent_id&key=$key&notify_url=$notify_url&version=$version";
            $sign = md5(strtolower($data));;
            $param= "version=$version&agent_id=$agent_id&agent_bill_id=$agent_bill_id&agent_bill_time=$agent_bill_time&sign=$sign&notify_url=$notify_url";

        }else{
            $data = "agent_id=$agent_id&key=$key&notify_url=$notify_url&refund_details=$refund_details&version=$version";
            $sign = md5(strtolower($data));;
            $param= "version=$version&agent_id=$agent_id&refund_details=$refund_details&agent_bill_time=$agent_bill_time&sign=$sign&notify_url=$notify_url";
        }
        //请求地址
        $url = 'https://pay.heepay.com/API/Payment/PaymentRefund.aspx';
        // 构建请求上下文
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'content' => $param
            )
        );
        $context  = stream_context_create($options);
        // 发送 POST 请求
        $response = file_get_contents($url, false, $context);
        $responsed= iconv('GBK', 'UTF-8', $response);
        $xmlObject= simplexml_load_string($responsed);
        $json  = json_encode($xmlObject);
        $array = json_decode($json, true);

        return $array;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-04-29 10:26
     * @功能说明:查询商户余额
     */
    public function agentCash($sub_agent_id){

        $agent_id = $this->config['agent_id'];

        $version  = 2;

        $data = "version=$version&agent_id=$agent_id&key=".$this->config['pay_key'];

        $sign = md5($data);

        $param= "version=$version&agent_id=$agent_id&sub_agent_id=$sub_agent_id&&sign=$sign";

        $url = 'https://www.heepay.com/API/Merchant/QueryBank.aspx?'.$param;

        $response = file_get_contents($url);

        $responsed= iconv('GBK', 'UTF-8', $response);

        $res = explode('|',$responsed);

        $arr = [];

        if(is_array($res)){

            foreach ($res as $k=>$v){

                $data = explode('=',$v);

                $arr[$data[0]] = $data[1];
            }
        }

        return $arr;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-04-29 14:05
     * @功能说明:商户充值接口
     */
    public function agentRecharge($ref_agent_id,$agent_bill_id,$cash){

        $version = 1;

        $agent_id = $this->config['agent_id'];

        $charge_amt_fen = $cash;

        $notify_url = 'https://aidj.simaguo.com';

        $return_url = 'https://aidj.simaguo.com';

        $key = $this->config['recharge_key'];

        $bank_code = $this->config['bank_code'];

        $data ="agent_bill_id=$agent_bill_id&agent_id=$agent_id&bank_code=$bank_code&charge_amt_fen=$charge_amt_fen&key=$key&notify_url=$notify_url&ref_agent_id=$ref_agent_id&return_url=$return_url&version=$version";

        $sign = md5(strtolower($data));

        $param ="agent_bill_id=$agent_bill_id&agent_id=$agent_id&bank_code=$bank_code&charge_amt_fen=$charge_amt_fen&notify_url=$notify_url&ref_agent_id=$ref_agent_id&return_url=$return_url&version=$version&sign=$sign";

        $url = 'https://www.heepay.com/API/Trade/AgentCharge.aspx?'.$param;

        $response = file_get_contents($url);

        $responsed= iconv('GBK', 'UTF-8', $response);

        $xmlObject= simplexml_load_string($responsed);

        $json  = json_encode($xmlObject);

        $array = json_decode($json, true);

        return $array;
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-04-29 14:05
     * @功能说明:商户充值接口
     */
    public function agentRechargev2($ref_agent_id,$agent_bill_id,$cash,$company_name){

        $version = 3;

        $agent_id = $this->config['agent_id'];

        $batch_amt = $cash;

        $batch_no = $agent_bill_id;

        $ext_param1 = 'Recharge';

        $batch_num = 1;

        $key = $this->config['recharge_key'];

        $des_key = $this->config['recharge_des_key'];

        $detail_data = longbingorderCode()."^$ref_agent_id^$company_name^$batch_amt^".$this->config['recharge_dec'];

        $notify_url = 'https://'.$_SERVER['HTTP_HOST'].'/heepay/CallBack/rechargeCallBack';;

        $data ="agent_id=$agent_id&batch_amt=$batch_amt&batch_no=$batch_no&batch_num=$batch_num&detail_data=$detail_data&ext_param1=$ext_param1&key=$key&notify_url=$notify_url&version=$version";

        $sign = md5(strtolower($data));

        $detail_data = $this->desdata($detail_data, $des_key);

        $param ="agent_id=$agent_id&batch_amt=$batch_amt&batch_no=$batch_no&batch_num=$batch_num&detail_data=$detail_data&ext_param1=$ext_param1&notify_url=$notify_url&version=$version&sign=$sign";

        $url = 'https://pay.heepay.com/api/paytransit/PayTransferAgentToAgent.aspx?'.$param;

        $response = file_get_contents($url);

        $responsed= iconv('GBK', 'UTF-8', $response);

        $xmlObject= simplexml_load_string($responsed);

        $json  = json_encode($xmlObject);

        $array = json_decode($json, true);

        return $array;
    }


    /**
     * @param $encrypted_hex
     * @param $deskey
     * @功能说明:3des 编码
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-04-29 19:19
     */
    public function desdata($encrypted_hex,$deskey){
        // 将参数转换为中文GBK编码
        $encrypted_hex = iconv('UTF-8', 'GBK', $encrypted_hex);
        //  3DES 加密
        $encrypted = openssl_encrypt($encrypted_hex, 'des-ede3', $deskey, OPENSSL_RAW_DATA);
        // 将加密结果转换为十六进制字符串
        $newencrypted = bin2hex($encrypted);

        return $newencrypted;
    }











}