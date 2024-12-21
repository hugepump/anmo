<?php
declare(strict_types=1);

namespace longbingcore\wxcore;

use app\card\model\User;
use FddAccount;
use FddCertification;
use FddContractManageMent;
use FddEncryption;
use FddSignContract;
use FddTemplate;
use think\facade\Db;

class Fdd{

    static protected $uniacid;

    public function __construct($uniacid)
    {
       self::$uniacid = $uniacid;
        //引用excel库
        require_once  EXTEND_PATH.'fdd/Fdd.Api.php';

        $this->getConfig();
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-10 10:04
     * @功能说明:
     */
    public function getConfig(){

        $config_model = new \app\fdd\model\FddConfig();

        $config = $config_model->dataInfo(['uniacid'=>self::$uniacid]);

        defined('FDD_ADDPID') or define('FDD_ADDPID',$config['AppId']);

        defined('FDD_SECERT') or define('FDD_SECERT',$config['AppSecret']);

        return $config;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-06 16:40
     * @功能说明:注册账号
     */
    public function registerAccount($user_id,$type=1){

        if($type==1){

            $user_model = new \app\massage\model\User();

            $open_id = $user_model->where(['id'=>$user_id])->value('openid');

            if(empty($open_id)){

                return ['code'=>500,'用户信息错误'];
            }
        }else{

            $open_id = 'adminadminadmin'.$user_id;
        }

        $open_id = $open_id.$type;

        $open_id = str_replace('-','',$open_id);

        $input = new FddAccount();

        $input->SetOpenID($open_id);

        $input->SetAccountType($type);

        $res = \FddApi::registerAccount($input,1);

        return $res;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-06 18:27
     * @功能说明:获取个人实名认证地址
     */
    public function getPersonVerifyUrl($customer_id){

        $input = new FddCertification();

        $input->SetCustomerID($customer_id);

        $input->SetVerifiedWay(9);

        $input->SetPageModify(1);

        $NotifyUrl = 'https://'.$_SERVER['HTTP_HOST'].'/massage/CallBack/fddAttestationCallBack';

        $input->SetNotifyUrl($NotifyUrl);

        $url = 'https://'.$_SERVER['HTTP_HOST'].'/h5/?#/pages/mine?type=2';

        $input->SetReturnUrl($url);

        $res = \FddApi::getPersonVerifyUrl($input);

        return $res;

    }


    /**
     * @param $customer_id
     * @功能说明:获取企业实名认证地址
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-07 14:21
     */
    public function getCompanyVerifyUrl($customer_id){

        $input = new FddCertification();

        $input->SetCustomerId($customer_id);

        $input->SetVerifiedWay(3);

        $input->SetMVerifieday(1);

        $input->SetPageModify(1);

        $NotifyUrl = 'https://'.$_SERVER['HTTP_HOST'].'/massage/CallBack/fddAttestationCallBack';

        $input->SetNotifyUrl($NotifyUrl);

        // $input->SetReturnUrl('');

        $res = \FddApi::getCompanyVerifyUrl($input);

        return $res;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-07 13:48
     * @功能说明:查询个人认证信息
     */
    public function FindPersonCertInfo($transactionNo){

        $input = new FddCertification();

        $input->SetVerifiedVSerialNo($transactionNo);

        $res = \FddApi::FindPersonCertInfo($input);

        return $res;

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-07 13:48
     * @功能说明:查询企业认证信息
     */
    public function FindCompanyCertInfo($transactionNo){

        $input = new FddCertification();

        $input->SetVerifiedVSerialNo($transactionNo);

        $res = \FddApi::FindCompanyCertInfo($input);

        return $res;


    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-07 14:04
     * @功能说明:通过uuid下载文件
     */
    public function getFile($uuid,$type=1){

        $input = new FddCertification();

        $input->SetUUID($uuid);

        $input->SetDoc_type($type);

        $res = \FddApi::getFile($input);

        return $res;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-07 14:13
     * @功能说明:绑定实名认证
     */
    public function ApplyCert($customer_id,$verified_serialno){

        $input = new FddCertification();

        $input->SetCustomerId($customer_id);

        $input->SetVerifiedVSerialNo($verified_serialno);

        $res = \FddApi::ApplyCert($input);

        return $res;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-06-26 16:13
     * @功能说明:添加印章
     */
    public function UploadSignatureContent($customer_id,$title){

        $input = new \FddSignatureContent();

        $input->SetCustomerId($customer_id);

        $input->SetContent($title);

        $res = \FddApi::UploadSignatureContent($input);

        return $res;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-07 15:55
     * @功能说明:模版填充
     */
    public function GenerateContract($tmpl_id,$title,$map){

        $input = new FddTemplate();

        $input->SetDoc_title($title);

        $input->SetTemplate_id($tmpl_id);

        $input->SetContract_id(orderCode());

        $input->SetParameter_map($map);

      //  $input->SetFile_type(1);

        $res = \FddApi::GenerateContract($input);

        return $res;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-07-16 20:11
     * @功能说明:上传合同模版
     */
    public function UploadTemplateDocs($tmpl_id,$title,$doc_url){

        $input = new FddTemplate();

        $input->SetTemplate_name($title);

        $input->SetTemplate_id($tmpl_id);

        $input->SetDoc_url($doc_url);
      //  $input->SetFile($doc_url);

        $res = \FddApi::uploadtemplate($input);

        return $res;
    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-07-16 20:11
     * @功能说明:上传合同模版
     */
    public function UploadTemplateDocsData($tmpl_id,$title,$doc_url){

        $input = new FddTemplate();

        $input->SetTemplate_name($title);

        $input->SetContract_template_id($tmpl_id);

       // $input->SetDoc_url($doc_url);
          $input->SetFile($doc_url);

        $res = \FddApi::UploadTemplateDocs($input);

        return $res;
    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-07 16:24
     * @功能说明:上传合同
     */
    public function Uploaddocs($contract_id,$doc_title,$doc_url){

        $input = new FddTemplate();

        $input->SetContract_id($contract_id);

        $input->SetDoc_title($doc_title);

        $input->SetDoc_url($doc_url);

        $doc = substr($doc_url,strripos($doc_url,".")+1);

        $doc= '.'.$doc;

        $input->SetDoc_type($doc);

        $res = \FddApi::Uploaddocs($input);

       // dump($res,$doc_url,$doc);exit;

        return $res;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-07 17:08
     * @功能说明:自动签署
     */
    public function ExtsignAuto($transaction_id,$contract_id,$customer_id,$doc_title){

        $input = new FddSignContract();

        $input->SetTransaction_id($transaction_id);

        $input->SetContract_id($contract_id);

        $input->SetCustomer_id($customer_id);

        $input->SetDoc_title($doc_title);

        $input->SetClient_role(1);

        $input->SetPosition_type(0);

        $input->SetKeyword_strategy(2);

        $input->SetSign_keyword('甲方');

        $input->SetPagenum(0);

        $input->SetX(200);

        $input->SetY(800);

        $input->SetKeyx(100);

        $input->SetKeyy(0);

//        $NotifyUrl = 'https://'.$_SERVER['HTTP_HOST'].'/massage/CallBack/fddSignCallBack';
//
//        $input->SetNotify_url(urlencode($NotifyUrl));

        $url = 'https://'.$_SERVER['HTTP_HOST'].'/#/sys/fdd-record';

        $input->SetReturn_url(urlencode($url));

       // dump($input);exit;

        $res = \FddApi::ExtsignAuto($input);

        return $res;

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-07 17:08
     * @功能说明:手动签署
     */
    public function Extsign($transaction_id,$contract_id,$customer_id,$doc_title,$type=1){

        $input = new FddSignContract();

        $input->SetTransaction_id($transaction_id);

        $input->SetContract_id($contract_id);

        $input->SetCustomer_id($customer_id);

        $input->SetDoc_title($doc_title);

        $NotifyUrl = 'https://'.$_SERVER['HTTP_HOST'].'/massage/CallBack/fddSignCallBack';

        $input->SetNotify_url(urlencode($NotifyUrl));

        if($type==1){

            $url = 'https://'.$_SERVER['HTTP_HOST'].'/h5/?#/pages/mine?type=2';

            $input->SetReturn_url(urlencode($url));
        }
        $res = \FddApi::Extsign($input);


        return $res;

    }


    /**
     * @param $contract_id
     * @功能说明:合同归档
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-07 17:26
     */
    public function ContractFiling($contract_id){

        $input = new FddContractManageMent();

        $input->SetContract_id($contract_id);

        $res = \FddApi::ContractFiling($input);

        return $res;

    }


    /**
     * @param $contract_id
     * @功能说明:下载合同
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-07 17:29
     */
    public function DownLoadContract($contract_id){

        $input = new FddContractManageMent();

        $input->SetContract_id($contract_id);

        $res = \FddApi::DownLoadContract($input);

        return $res;

    }



    /**
     * @param $contract_id
     * @功能说明:合同查看
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-07 17:29
     */
    public function ViewContract($contract_id){

        $input = new FddContractManageMent();

        $input->SetContract_id($contract_id);

        $res = \FddApi::ViewContract($input);

        return $res;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-09-01 11:50
     * @功能说明:获取自动签署合同的权限
     */
    public function getAutoAuth($company_customer_id){

        $input = new \FddAuthSign();

        $input->SetTransaction_id(orderCode());

        $input->SetAuth_type(1);

        $input->SetContract_id(orderCode());

        $input->SetCustomerId($company_customer_id);

        $input->SetNotify_url('https://'.$_SERVER['HTTP_HOST'].'/massage/CallBack/fddSignCallBacks');

        $url = 'https://'.$_SERVER['HTTP_HOST'].'/#/sys/fdd-record';

        $input->SetReturn_url(urlencode($url));

        $res = \FddApi::BeforeAuthsign($input);

        return $res;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-09-01 12:00
     * @功能说明:查询是否有自动签署合同的权限
     */
    public function findAutoAuth($company_customer_id){

        $input = new \FddAuthSign();

        $input->SetCustomerId($company_customer_id);

        $res = \FddApi::GetAuthStatus($input);

        return $res;

    }


    /**
     * @param $customer_id
     * @功能说明:取消自动签署合同的权限
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-09-01 14:41
     */
    public function CancelExtsignAutoPage($customer_id){

        $input = new \FddAuthSign();

        $input->SetCustomerId($customer_id);

        $url = 'https://'.$_SERVER['HTTP_HOST'].'/#/sys/fdd-record';

        $input->SetReturn_url(urlencode($url));

        $input->SetNotify_url('https://'.$_SERVER['HTTP_HOST'].'/massage/CallBack/fddSignCallBacks');

        $res = \FddApi::CancelExtsignAutoPage($input);

        return $res;
    }



}