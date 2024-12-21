<?php
declare(strict_types=1);

namespace longbingcore\heepay;







//use adapay\test1;
//use adapay\test2;

use AdaPaySdk\Payment;
use app\adapay\model\Config;

use think\Exception;

include 'Util.php';
use function Util\ASCIIAZ;
use function Util\DeleteValue;
use function Util\PostJson;
use function Util\RSA2sign;

class AddUser{

    static protected $uniacid;

    protected $appid;

    protected $draw_cash_type;

    public function __construct($uniacid)
    {
        self::$uniacid = $uniacid;

    }



    public function addUser(){



//入驻申请业务参数 文档里必传字段必须传
        $Oldbiz_content = array(
            "if_sign" => '1',
            "business_developer" => '张三',                //商户的业务员
            "customer_type" => '1',                       //1=企业 2=个体工商户 3=小微
            "register_type" => 'HEEPAY_MERCH',            //账户类型（默认收单）HEEPAY_MERCH=收单商户；HEEPAY_BALANCE=钱包账户
            "customer_name" => '小二黑科技有限公司',        //客户名称
            "customer_short_name" => '小锤',               //客户简称
            "customer_phone" => '12312312312',            //客服电话
            "licence_code" => '98665112MA7HB5C80P',       //营业执照编码
            "licence_valid_type" => '1',                   //营业执照有效期类型  1=定期,2=长期
            "licence_valid_begin" => '2021-05-21',         //营业执照生效日期
            "licence_valid_end" => '2033-05-21',           //营业执照失效日期
            "licence_region_code" => '110108',             //营业执照区划编码
            "licence_address" => '北京市海淀区中关村',       //营业执照注册地址
            "business_category_code" => '1035',            //经营类目编码
            "legal_name" => '张三',                        //法人姓名
            "legal_id_card" => '610321192303032321',       //法人证件号
            "legal_id_card_valid_type" => '1',             //法人有效期类型  1=定期,2=长期
            "legal_id_card_valid_begin" => '2023-10-26',   //法人证件生效日期
            "legal_id_card_valid_end" => '2033-10-26',      //法人证件失效日期
            "legal_mobile" => '18312344321',                //法人手机号
            "contact_name" => '李四',                       //联系人名称
            "contact_id_card" => '610321199303203921',      //联系人证件号
            "contact_id_card_valid_type" => '1',           //联系人证件有效期类型 1=定期,2=长期
            "contact_id_card_valid_begin" => '2021-05-21',  //联系人证件生效日期
            "contact_id_card_valid_end" => '2041-05-21',    //联系人证件失效日期
            "contact_mobile" => '13943211234',              //联系人手机号
            "contact_email" => '12345124214312@qq.com',      //联系人邮箱/创建商户登录账号
            "bank_account_type" => '1',                     //结算银行账户类型
            "bank_account_name" => '小二黑科技有限公司',      //结算银行账户名称
            "bank_card_no" => '61021234123',                //结算银行卡号
            "bank_name" => '建设银行',                       //结算开户行名称
            "bank_region_code" => '110100',                  //结算银行所属区划编码
            "withdraw_type" => '1',                          //提现类型 1=手动提现,2=自动提现
            "licence_img" => '/public/01/20231208/ff3b85802ff9fced6371b3b61c2c0548.png',//营业执照图片路径
            "company_build_img" => '/public/01/20231208/ff3b85802ff9fced6371b3b61c2c0548.png',//公司大楼图片路径
            "company_front_img" => '/public/01/20231208/ff3b85802ff9fced6371b3b61c2c0548.png',//公司前台图片路径
            "shop_env_img" => '/public/01/20231208/ff3b85802ff9fced6371b3b61c2c0548.png',//店内场景照图片路径
            "reg_certificate_img" => '/public/01/20231208/ff3b85802ff9fced6371b3b61c2c0548.png',//开户许可证图片路径
            "legal_id_card_front_img" => '/public/01/20231208/ff3b85802ff9fced6371b3b61c2c0548.png',//法人身份证人像面图片路径
            "legal_id_card_back_img" => '/public/01/20231208/ff3b85802ff9fced6371b3b61c2c0548.png',//法人身份证国徽面图片路径
            "contact_id_card_front_img" => '/public/01/20231208/ff3b85802ff9fced6371b3b61c2c0548.png',// 业务员身份证人像面图片路径
            "contact_id_card_back_img" => '/public/01/20231208/ff3b85802ff9fced6371b3b61c2c0548.png',//业务员身份证国徽面图片路径
        );


        /*
        公共请求参数
        */
        $app_id='hykj001';
        $charset='utf-8';
        $biz_content=json_encode($Oldbiz_content,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        $method='customer.entry.apply';
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
        echo "签名前参数：".$signatureContent;
        echo "<br/><hr>";


        $sign = RSA2sign($signatureContent);


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
        echo "请求json".$arrayData1;
        echo "<br/><hr>";


        $Posturl = "https://openapi.heepay.com/customer-api/gateway";
        $jsonData = $arrayData1;


        PostJson($Posturl,$jsonData);

    }






}