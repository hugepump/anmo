<?php
require_once "Fdd.Exception.php";
require_once "Fdd.Config.php";
require_once "Fdd.Data.php";
require_once "Fdd.Encryption.php";

/**
 * 
 * 接口访问类，包含所有法大大API列表的封装，类中方法为static方法，
 * 每个接口有默认超时时间
 */
date_default_timezone_set('PRC');//其中PRC为“中华人民共和国”
class FddApi
{/**
     * 4.1合规化接口 注册账号
     * @param Account $param
     * @param int $timeOut
     * @return array|mixed
     */
    public static function registerAccount(FddAccount $param, $timeOut = 6)
    {


        //注册接口
        $url = FddConfig::FddServer.'/account_register.api';
        try{
            //实例化3DES类
            $des = new FddEncryption();
            if (!$param->IsOpenIDSet())
                throw new FddException("缺少必填参数-open_id");
            if (!$param->IsAccountTypeSet())
                throw new FddException("缺少必填参数-account_type");
            $encArr = $param->GetValues();
            $encKey = array_keys($encArr);
            array_multisort($encKey);
            $enc = [
                'md5' => [],
                'sha1'=>$encKey
            ];

            //dump(FddConfig::AppId);exit;
            $param->SetApp_id(FddConfig::AppId);
            $param->SetTimestamp(date('YmdHis'));
            if (!$param->IsVSet()){
                $param->SetV('2.0');
            }
            $param->SetMsg_digest($des::GeneralDigest($param,$enc));
            $input = $param->GetValues();
            $res = self::https_request($url,$input);
            return $res;

        }catch (FddException $e){

            return ['result'=>'error','code'=>2001,'msg'=>$e->errorMessage()];
        }

    }


    /**
     * 4.2获取企业实名认证地址
     * @param FddCertification $param
     * @param int $timeOut
     * @throws FddException
     * @return 成功时返回，其他抛异常
     */
    public static function getCompanyVerifyUrl(FddCertification $param, $timeOut = 6)
    {
        //获取企业实名认证地址
        $url = FddConfig::FddServer.'/get_company_verify_url.api';


        try{
            // 参数处理
            if (!$param->IsCustomerIDSet()){
                throw new FddException("缺少必填参数-customer_id");
            }
            if (!$param->IsPageModifySet()){
                throw new FddException("缺少必填参数-page_modify");
            }

//
//            $AgentInfo = [
//                'agent_id'=>$param->GetAgentID(),
//                'agent_id_front_path'=>$param->GetAgentIdFrontPath(),
//                'agent_mobile'=>$param->GetAgentMobile(),
//                'agent_name'=>$param->GetAgentName(),
//                'bank_card_no'=>$param->getBank_card_no(),
//                'agent_id_back_path'=>$param->GetAgent_id_back_path()
//            ];
//
//
//
//            $bankInfo = [
//                'bank_id'=>$param->GetBankId(),
//                'bank_name'=>$param->GetBankName(),
//                'subbranch_name'=>$param->GetSubbranchName(),
//            ];
//
//            $companyInfo = [
//                'company_name'=>$param->GetCompanyName(),
//                'credit_image_path'=>$param->GetCreditImagePath(),
//                'credit_no'=>$param->GetCreditNo(),
//            ];
//
//            $LegalInfo = [
//                'legal_id'=>$param->GetLegalId(),
//                'legal_id_front_path'=>$param->GetlegaldIFrontPath(),
//                'legal_name'=>$param->GetLegalName(),
//                'legal_mobile'=>$param->GetlegalMobile(),
//                'bank_card_no'=>$param->getBank_card_no(),
//                'legal_id_back_path'=>$param->GetLegal_id_back_path()
//            ];

            //实例化3DES类
            $des = new FddEncryption();
//            $param->SetAgentInfo(json_encode($AgentInfo));
//            $param->SetBankInfo(json_encode($bankInfo));
//            $param->SetCompanyInfo(json_encode($companyInfo));
//            $param->SetLegalnfo(json_encode($LegalInfo));

            // legalName 在 legalInfo 里面，外部的 legalName 值置空，不然会出现摘要问题。
            $param->SetLegalName(null);

            $encArr = $param->GetValues();
            $encKey = array_keys($encArr);

            // 删除字段名称，AgentInfo、bankInfo、companyInfo、LegalInfo的内部字段不直接参与摘要计算
            $encKey = array_diff($encKey, ["agent_id","agent_id_front_path","agent_mobile","agent_name",
                "bank_card_no",'bank_id','bank_name','subbranch_name','company_name','credit_image_path',
                'credit_no','legal_id','legal_id_front_path','legal_name','legal_mobile','m_verified_way',
                'agent_id_front_img', 'authorization_file', 'agent_id_back_path', 'legal_id_back_path',
                'agent_id_back_img', 'legal_id_back_img']);

            array_multisort($encKey);
            $enc = [
                'md5' => [],
                'sha1'=>$encKey
            ];

            $param->SetApp_id(FddConfig::AppId);
            $param->SetTimestamp(date('YmdHis'));
            if (!$param->IsVSet()){
                $param->SetV('2.0');
            }
            $param->SetMsg_digest($des::GeneralDigest($param,$enc));
            $input = $param->GetValues();
            $res = self::https_request($url,$input);
            return $res;

        }catch (FddException $e){

            return ['result'=>'error','code'=>2001,'msg'=>$e->errorMessage()];
        }
    }

    /**
     * 4.3获取个人实名认证地址
     * @param FddCertification $param
     * @param int $timeOut
     * @throws FddException
     * @return 成功时返回，其他抛异常
     */
    public static function getPersonVerifyUrl(FddCertification $param, $timeOut = 6)
    {
        //获取个人实名认证地址
        $url = FddConfig::FddServer.'/get_person_verify_url.api';
        try{
            // 参数处理
            if (!$param->IsCustomerIDSet()){
                throw new FddException("缺少必填参数-customer_id");
            }
            if (!$param->IsVerifiedWaySet()){
                throw new FddException("缺少必填参数-verified_way");
            }
            if (!$param->IsPageModifySet()){
                throw new FddException("缺少必填参数-page_modify");
            }

            //实例化3DES类
            $des = new FddEncryption();
            $encArr = $param->GetValues();
            $encKey = array_keys($encArr);
            // 删除字段名称，file 类型参数
            $encKey = array_diff($encKey, ["ident_front_img","ident_back_img"]);
            array_multisort($encKey);
            $enc = [
                'md5' => [],
                'sha1'=>$encKey
            ];

            $param->SetApp_id(FddConfig::AppId);
            $param->SetTimestamp(date('YmdHis'));
            if (!$param->IsVSet()){
                $param->SetV('2.0');
            }
            $param->SetMsg_digest($des::GeneralDigest($param,$enc));

            $input = $param->GetValues();
            $res = self::https_request($url,$input);
            return $res;

        }catch (FddException $e){

            return ['result'=>'error','code'=>2001,'msg'=>$e->errorMessage()];
        }

    }
    /**
     * 4.4实名证书申请接口
     * @param FddCertification $param
     * @param int $timeOut
     * @throws FddException
     * @return 成功时返回，其他抛异常
     */
    public static function ApplyCert(FddCertification $param, $timeOut = 6)
    {
        //获取实名证书申请接口
        $url = FddConfig::FddServer.'/apply_cert.api';
        try{
            // 参数处理
            if (!$param->IsCustomerIDSet()){
                throw new FddException("缺少必填参数-customer_id");
            }
            if (!$param->IsVerifiedSerialNo()){
                throw new FddException("缺少必填参数-verified_serialno");
            }


            //实例化3DES类
            $des = new FddEncryption();
            $enc = [
                'md5' => [],
                'sha1'=>['customer_id','verified_serialno']
            ];

            $param->SetApp_id(FddConfig::AppId);
            $param->SetTimestamp(date('YmdHis'));
            if (!$param->IsVSet()){
                $param->SetV('2.0');
            }
            $param->SetMsg_digest($des::GeneralDigest($param,$enc));

            $input = $param->GetValues();
            $res = self::https_request($url,$input);
            return $res;

        }catch (FddException $e){

            return ['result'=>'error','code'=>2001,'msg'=>$e->errorMessage()];
        }

    }


    /**
     * 4.5上传印章
     * @param FddSignature $param
     * @param int $timeOut
     * @throws FddException
     * @return 成功时返回，其他抛异常
     */
    public static function UploadSignature(FddSignature $param, $timeOut = 6)
    {
        //合同模板传输接口 地址
        $url = FddConfig::FddServer.'/add_signature.api';
        try{
            //参数处理
            if (!$param->IsCustomerId())
                throw new FddException("缺少必填参数-customer_id");
            if (!$param->IsSignatureImgBase64())
                throw new FddException("缺少必填参数-signature_img_base64");
            $param->SetApp_id(FddConfig::AppId);
            $param->SetTimestamp(date('YmdHis'));
            if (!$param->IsVSet()){
                $param->SetV('2.0');
            }
            //实例化3DES类
            $des = new FddEncryption();
            //设置加密串
            $enc = [
                'md5' => [],
                'sha1'=> ['customer_id','signature_img_base64']
            ];
            $param->SetMsg_digest($des::GeneralDigest($param,$enc));
            $input = $param->GetValues();
            $res = self::https_request($url,$input);
            return $res;
        }catch (FddException $e){
            return ['result'=>'error','code'=>2001,'msg'=>$e->errorMessage()];
        }

    }
    /**
     * 4.6 自定义印章内容
     * @param FddSignatureContent $param
     * @param int $timeOut
     * @throws FddException
     * @return 成功时返回，其他抛异常
     */
    public static function UploadSignatureContent(FddSignatureContent $param, $timeOut = 6)
    {
        //合同模板传输接口 地址
        $url = FddConfig::FddServer.'/custom_signature.api';
        try{
            //参数处理
            if (!$param->IsCustomerId())
                throw new FddException("缺少必填参数-customer_id");
            if (!$param->IsContent())
                throw new FddException("缺少必填参数-content");
            $param->SetApp_id(FddConfig::AppId);
            $param->SetTimestamp(date('YmdHis'));
            if (!$param->IsVSet()){
                $param->SetV('2.0');
            }
            //实例化3DES类
            $des = new FddEncryption();
            //设置加密串
            $enc = [
                'md5' => [],
                'sha1'=> ['content','customer_id']
            ];
            $param->SetMsg_digest($des::GeneralDigest($param,$enc));
            $input = $param->GetValues();
            $res = self::https_request($url,$input);
            return $res;
        }catch (FddException $e){
            return ['result'=>'error','code'=>2001,'msg'=>$e->errorMessage()];
        }

    }


    /**
     *
     * 4.7合同文档传输接口
     * app_id、timestamp、msg_digest、v 、contract_id、doc_type 、doc_title必填参数
     * file、doc_url  选填参数
     * @param FddTemplate $param
     * @param int $timeOut
     * @throws FddException
     * @return 成功时返回，其他抛异常
     */
    public static function Uploaddocs(FddTemplate $param, $timeOut = 6)
    {
        //合同文档传输接口 地址
        $url = FddConfig::FddServer.'/uploaddocs.api';
        try{
            //参数处理
            if (!$param->IsContract_idSet())
                throw new FddException("缺少必填参数-contract_id");
            if (!$param->IsDoc_titleSet())
                throw new FddException("缺少必填参数-doc_title");
            if (!$param->IsDoc_typeSet())
                throw new FddException("缺少必填参数-doc_type");
            if (!$param->IsFileSet() && !$param->IsDoc_urlSet())
                throw new FddException("缺少必填参数-file、doc_url 二选一");
            $param->SetApp_id(FddConfig::AppId);
            $param->SetTimestamp(date('YmdHis'));
            if (!$param->IsVSet()){
                $param->SetV('2.0');
            }
            //实例化3DES类
            $des = new FddEncryption();
            //设置加密串
            $enc = [
                'md5' => [],
                'sha1' => ['contract_id']
            ];
            // $param->SetMsg_digest($des::ContractDigest($param));
            $param->SetMsg_digest($des::GeneralDigest($param,$enc));
            $input = $param->GetValues();
            // file文件是为header 跳转
            // header('location:'.$url.$des->ArrayParamToStr($input));
            $res = self::https_request($url,$input);
            return $res;
        }catch (FddException $e){

            return ['result'=>'error','code'=>2001,'msg'=>$e->errorMessage()];
        }
        
    }
    /**
     *
     * 4.8合同模板传输接口
     * app_id、timestamp、msg_digest、v 、template_id 必填参数
     * file、doc_url  选填参数
     * @param FddTemplate $param
     * @param int $timeOut
     * @throws FddException
     * @return 成功时返回，其他抛异常
     */
    public static function UploadTemplate(FddTemplate $param, $timeOut = 6)
    {
        //合同模板传输接口 地址
        $url = FddConfig::FddServer.'/uploadtemplate.api';
        try{
            //参数处理
            if (!$param->IsTemplate_idSet())
                throw new FddException("缺少必填参数-template_id");
            if (!$param->IsFileSet() && !$param->IsDoc_urlSet())
                throw new FddException("缺少必填参数-file、doc_url 二选一");
            $param->SetApp_id(FddConfig::AppId);
            $param->SetTimestamp(date('YmdHis'));
            if (!$param->IsVSet()){
                $param->SetV('2.0');
            }
            //实例化3DES类
            $des = new FddEncryption();
            //设置加密串
            $enc = [
                'md5' => [],
                'sha1'=> ['template_id']
            ];
            $param->SetMsg_digest($des::GeneralDigest($param,$enc));
            $input = $param->GetValues();
            $res = self::https_request($url,$input);
            return $res;
        }catch (FddException $e){

            return ['result'=>'error','code'=>2001,'msg'=>$e->errorMessage()];
        }

    }
    /**
     *
     * 4.9 模板填充生成合同接口
     *
     * 动态表单参数使用。参数实体用 FddTemplateDynamicTable。多个表格使用数组包装 FddTemplateDynamicTable。
     * 然后使用 json_encode(array($你的数组变量), JSON_UNESCAPED_UNICODE) 赋值给 FddTemplate->SetDynamic_tables()
     *
     * app_id、timestamp、msg_digest、v 、template_id 必填参数
     * @param FddTemplate $param
     * @param int $timeOut
     * @throws FddException
     * @return 成功时返回，其他抛异常
     */
    public static function GenerateContract(FddTemplate $param, $timeOut = 6)
    {
        //合同生成接口 地址
        $url = FddConfig::FddServer.'/generate_contract.api';
        try{
            //参数处理
            if (!$param->IsTemplate_idSet())
                throw new FddException("缺少必填参数-template_id");
            if (!$param->IsContract_idSet())
                throw new FddException("缺少必填参数-contract_id");
            if (!$param->IsParameter_mapSet())
                throw new FddException("缺少必填参数-parameter_map");
            $param->SetApp_id(FddConfig::AppId);
            $param->SetTimestamp(date('YmdHis'));
            if (!$param->IsVSet()){
                $param->SetV('2.0');
            }
//            $data = [];
//            if($param->GetInsertWay() != null){
//                $data['insertWay'] = $param->GetInsertWay();
//            }
//            if($param->GetKeyword() != null){
//                $data['keyword'] = $param->GetKeyword();
//            }
//            if($param->GetPageBegin() != null){
//                $data['pageBegin'] = $param->GetPageBegin();
//            }
//            if($param->GetBorderFlag() != null){
//                $data['borderFlag'] = $param->GetBorderFlag();
//            }
//            if($param->GetCellHeight() != null){
//                $data['cellHeight'] = $param->GetCellHeight();
//            }
//            if($param->GetCellHorizontalAlignment() != null){
//                $data['cellHorizontalAlignment'] = $param->GetCellHorizontalAlignment();
//            }
//            if($param->GetCellVerticalAlignment() != null){
//                $data['cellVerticalAlignment'] = $param->GetCellVerticalAlignment();
//            }
//            if($param->GetTheFirstHeader() != null){
//                $data['theFirstHeader'] = $param->GetTheFirstHeader();
//            }
//            if($param->GetHeaders() != null){
//                $data['headers'] = $param->GetHeaders();
//            }
//            if($param->GetHeadersAlignment() != null){
//                $data['headersAlignment'] = $param->GetHeadersAlignment();
//            }
//            if($param->GetDatas() != null){
//               $data['datas']  = $param->GetDatas();
//            }
//            if($param->GetColWidthPercent() != null){
//                $data['colWidthPercent'] = $param->GetColWidthPercent();
//            }
//            if($param->GetTableHorizontalAlignment() != null){
//                $data['tableHorizontalAlignment'] = $param->GetTableHorizontalAlignment();
//            }
//            if($param->GetTableWidthPercentage() != null){
//                $data['tableWidthPercentage'] = $param->GetTableWidthPercentage();
//            }
//            if($param->GetTableHorizontalOffset() != null){
//                $data['tableHorizontalOffset'] = $param->GetTableHorizontalOffset();
//            }
//            $param->SetHeaders(json_encode($param->GetHeaders()));
//            $param->SetDatas(json_encode($param->GetDatas()));
//            $param->SetColWidthPercent(json_encode($param->GetColWidthPercent()));
//            $arr = array($data);
//            echo "count:".count($arr);
//            if (count($arr) >= 1){
//                $param->SetDynamic_tables(json_encode($arr));
//                echo "table:".$param->GetDynamic_tables();
//            }
//            if (!$param->IsHeadersSet())
//                    throw new FddException("缺少必填参数-headers");
            //实例化3DES类
            $des = new FddEncryption();
            $param->SetMsg_digest($des::ContractDigest($param));
            $input = $param->GetValues();
            $res = self::https_request($url,$input);
            return $res;
        }catch (FddException $e){

            return ['result'=>'error','code'=>2001,'msg'=>$e->errorMessage()];
        }
        
    }
    /**
     *
     * 4.10文档签署接口（自动签）
     * app_id、timestamp、msg_digest、contract_id 、transaction_id、customer_id、必填参数
     * notify_url 选填参数
     * @param FddSignContract $param
     * @param int $timeOut
     * @throws FddException
     * @return 成功时返回，其他抛异常
     */
    public static function ExtsignAuto(FddSignContract $param, $timeOut = 6)
    {
        // 文档签署接口（自动签） 地址
        $url = FddConfig::FddServer.'/extsign_auto.api';
        try{
            //参数处理
            if (!$param->IsTransaction_idSet())
                throw new FddException("缺少必填参数-transaction_id");
            if (!$param->IsContract_idSet())
                throw new FddException("缺少必填参数-contract_id");
            if (!$param->IsCustomer_idSet())
                throw new FddException("缺少必填参数-customer_id");
            if (!$param->IsDoc_titleSet())
                throw new FddException("缺少必填参数-doc_title");
            if (!$param->IsClient_roleSet())
                throw new FddException("缺少必填参数-client_role");
            if ($param->IsPosition_typeSet()){
                if($param->GetPosition_type() == 1){
                    if(!$param->IsYSet() && !$param->IsXSet() && !$param->IsPagenumSet())
                        throw new FddException("缺少必填参数- x 、y 、pagenum");
                }
                if($param->GetPosition_type() == 0){
                    if(!$param->IsSign_keywordSet())
                        throw new FddException("缺少必填参数- Sign_keyword");
                }
            }
            $pagenum = $param->GetPagenum();
            $x = $param->GetX();
            $y = $param->GetY();
            $SearchLocation = array(
                array(
                    'pagenum' => $pagenum,
                    'x' => $x,
                    'y' => $y
                )
            );
            $param->SetSignature_positions(json_encode($SearchLocation));
            $param->SetApp_id(FddConfig::AppId);
            $param->SetTimestamp(date('YmdHis'));
            $param->SetDoc_title(urlencode($param->GetDoc_title()));
            if (!$param->IsVSet()){
                $param->SetV('2.0');
            }
            //实例化3DES类
            $des = new FddEncryption();
            $param->SetMsg_digest($des::ExtsignDigest($param));
            $input = $param->GetValues();
            $res = self::https_request($url,$input);
            return $res;
        }catch (FddException $e){

            return ['result'=>'error','code'=>2001,'msg'=>$e->errorMessage()];
        }

    }

    /**
     *
     * 4.11文档签署接口（手动签）
     * app_id、timestamp、msg_digest、contract_id 、transaction_id、customer_id、必填参数
     * notify_url 选填参数
     * @param FddSignContract $param
     * @param int $timeOut
     * @throws FddException
     * @return 成功时返回，其他抛异常
     */
    public static function Extsign(FddSignContract $param, $timeOut = 6)
    {
        // 文档签署接口（手动签） 地址
        $url = FddConfig::FddServer.'/extsign.api';
        try{
            //参数处理
            if (!$param->IsTransaction_idSet())
                throw new FddException("缺少必填参数-transaction_id");
            if (!$param->IsContract_idSet())
                throw new FddException("缺少必填参数-contract_id");
            if (!$param->IsCustomer_idSet())
                throw new FddException("缺少必填参数-customer_id");
            if (!$param->IsDoc_titleSet())
                throw new FddException("缺少必填参数-doc_title");
            $param->SetApp_id(FddConfig::AppId);
            $param->SetTimestamp(date('YmdHis'));
            $param->SetDoc_title(urlencode($param->GetDoc_title()));
            if (!$param->IsVSet()){
                $param->SetV('2.0');
            }
            //实例化3DES类
            $des = new FddEncryption();
            $param->SetMsg_digest($des::ExtsignDigest($param));
            $input = $param->GetValues();
//            header('location:'.$url.$des->ArrayParamToStr($input));
            // 2022-03-02 将手动签地址直接进行返回，不进行打开操作
            // 注意：如果是用作 web ，则使用 htmlspecialchars 方法，防止转义
//            return htmlspecialchars($url.$des->ArrayParamToStr($input));

           // dump($input);exit;
            return $url.$des->ArrayParamToStr($input);
            // $res = self::https_request($url,$input);
            // return $res;
        }catch (FddException $e){

            return ['result'=>'error','code'=>2001,'msg'=>$e->errorMessage()];
        }
        
    }
    

    /**
     *
     * 文档签署接口（含有效期和次数限制）
     * app_id、timestamp、msg_digest、contract_id 、transaction_id 、customer_id、doc_title、return_url、validity、quantity必填参数
     * notify_url 、sign_keyword 、keyword_strategy 选填参数
     * @param FddSignContract $param
     * @param int $timeOut
     * @throws FddException
     * @return 成功时返回，其他抛异常
     */
    public static function ExtsignValidation(FddSignContract $param, $timeOut = 6)
    {
        // 文档签署接口（含有效期和次数限制） 地址
        $url = FddConfig::FddServer.'/extsign_validation.api';
        try{
            //参数处理
            if (!$param->IsTransaction_idSet())
                throw new FddException("缺少必填参数-transaction_id");
            if (!$param->IsContract_idSet())
                throw new FddException("缺少必填参数-contract_id");
            if (!$param->IsCustomer_idSet())
                throw new FddException("缺少必填参数-customer_id");
            if (!$param->IsDoc_titleSet())
                throw new FddException("缺少必填参数-doc_title");
            if (!$param->IsReturn_urlSet())
                throw new FddException("缺少必填参数-return_url");
            if (!$param->IsValiditySet())
                throw new FddException("缺少必填参数-validity");
            if (!$param->IsQuantitySet())
                throw new FddException("缺少必填参数-quantity");  
            $param->SetApp_id(FddConfig::AppId);
            $param->SetTimestamp(date('YmdHis'));
            $param->SetDoc_title(urlencode($param->GetDoc_title()));
            if (!$param->IsVSet()){
                $param->SetV('2.0');
            }
            //实例化3DES类
            $des = new FddEncryption();
            $param->SetMsg_digest($des::ExtsignValiityDigest($param));
            $input = $param->GetValues();

            // 注意：如果是用作 web ，则使用 htmlspecialchars 方法，防止转义
//            return htmlspecialchars($url.$des->ArrayParamToStr($input));
            return $url.$des->ArrayParamToStr($input);
            // $res = self::https_request($url,$input);
            // return $res;
        }catch (FddException $e){

            return ['result'=>'error','code'=>2001,'msg'=>$e->errorMessage()];
        }
        
    }
    
    /**
     *
     * 客户签署结果查询接口
     * @param FddQuerySignResult $param
     * @param int $timeOut
     * @throws FddException
     * @return 成功时返回，其他抛异常
     */
    public static function QuerySignResult(FddQuerySignResult $param, $timeOut = 6)
    {
        //客户签署结果查询接口 地址
        $url = FddConfig::FddServer.'/query_sign_result.api';
        try{
            //参数处理
            if (!$param->IsContract_idSet())
                throw new FddException("缺少必填参数-contract_id");
            if (!$param->IsCustomer_idSet())
                throw new FddException("缺少必填参数-customer_id");
            if (!$param->IsTransaction_idSet())
                throw new FddException("缺少必填参数-transaction_id");
            $param->SetApp_id(FddConfig::AppId);
            $param->SetTimestamp(date('YmdHis'));
            if (!$param->IsVSet()){
                $param->SetV('2.0');
            }
            //实例化3DES类
            $des = new FddEncryption();
            //设置加密串
            $enc = [
                'md5' => [],
                'sha1'=> ['contract_id','customer_id','transaction_id']
            ];
            $param->SetMsg_digest($des::GeneralDigest($param,$enc));
            $input = $param->GetValues();
            $res = self::https_request($url,$input);
            return $res;
        }catch (FddException $e){

            return ['result'=>'error','code'=>2001,'msg'=>$e->errorMessage()];
        }
        
    }
    
    /**
     *
     * 4.12文档查看接口
     * @param FddContractManageMent $param
     * @param int $timeOut
     * @throws FddException
     * @return 成功时返回，其他抛异常
     */
    public static function ViewContract(FddContractManageMent $param, $timeOut = 6)
    {
        //文档查看接口
        $url = FddConfig::FddServer.'/viewContract.api';
        try{
            if (!$param->IsContract_idSet())
                throw new FddException("缺少必填参数-contract_id");
            $param->SetApp_id(FddConfig::AppId);
            $param->SetTimestamp(date('YmdHis'));
            if (!$param->IsVSet()){
                $param->SetV('2.0');
            }
            //实例化3DES类
            $des = new FddEncryption();
            //设置加密串
            $enc = [
                'md5' => [],
                'sha1'=> ['contract_id']
            ];
            $param->SetMsg_digest($des::GeneralDigest($param,$enc));
            $input = $param->GetValues();

            // 注意：如果是用作 web ，则使用 htmlspecialchars 方法，防止转义
//            return htmlspecialchars($url.$des->ArrayParamToStr($input));
            return $url.$des->ArrayParamToStr($input);

            // $res = self::https_request($url,$input);
            // return $res;
        }catch (FddException $e){

            return ['result'=>'error','code'=>2001,'msg'=>$e->errorMessage()];
        }

    }
    
    /**
     *
     * 文档临时查看/下载地址接口（含有效期和次数）
     * @param FddContractManageMent $param
     * @param int $timeOut
     * @throws FddException
     * @return 成功时返回，其他抛异常 Geturl
     */
    public static function GetUrl(FddContractManageMent $param, $timeOut = 6)
    {
        //文档临时查看/下载地址接口（含有效期和次数）
        $url = FddConfig::FddServer.'/geturl.api';
        try{
            if (!$param->IsContract_idSet())
                throw new FddException("缺少必填参数-contract_id");
            if (!$param->IsValiditySet())
                throw new FddException("缺少必填参数-validity");
            if (!$param->IsQuantitySet())
                throw new FddException("缺少必填参数-quantity");
            $param->SetApp_id(FddConfig::AppId);
            $param->SetTimestamp(date('YmdHis'));
            if (!$param->IsVSet()){
                $param->SetV('2.0');
            }
            //实例化3DES类
            $des = new FddEncryption();
            //设置加密串
            $enc = [
                'md5' => ['validity','quantity'],
                'sha1'=> ['contract_id']
            ];
            $param->SetMsg_digest($des::GeneralDigest($param,$enc));
            $input = $param->GetValues();
            // header('location:'.$url.$des->ArrayParamToStr($input));
            $res = self::https_request($url,$input);
            return $res;
        }catch (FddException $e){

            return ['result'=>'error','code'=>2001,'msg'=>$e->errorMessage()];
        }

    }
    
    /**
     *
     * 4.13文档下载接口
     * @param FddContractManageMent $param
     * @param int $timeOut
     * @throws FddException
     * @return 成功时返回，其他抛异常 
     */
    public static function DownLoadContract(FddContractManageMent $param, $timeOut = 6)
    {
        //文档下载接口 地址
        $url = FddConfig::FddServer.'/downLoadContract.api';
        try{
            if (!$param->IsContract_idSet())
                throw new FddException("缺少必填参数-contract_id");
            $param->SetApp_id(FddConfig::AppId);
            $param->SetTimestamp(date('YmdHis'));
            if (!$param->IsVSet()){
                $param->SetV('2.0');
            }
            //实例化3DES类
            $des = new FddEncryption();
            //设置加密串
            $enc = [
                'md5' => [],
                'sha1'=> ['contract_id']
            ];
            $param->SetMsg_digest($des::GeneralDigest($param,$enc));
            $input = $param->GetValues();
            // 注意：如果是用作 web ，则使用 htmlspecialchars 方法，防止转义
//            return htmlspecialchars($url.$des->ArrayParamToStr($input));
            return $url.$des->ArrayParamToStr($input);
        }catch (FddException $e){

            return ['result'=>'error','code'=>2001,'msg'=>$e->errorMessage()];
        }

    }
    
    /**
     *
     * 查询合同hash值接口
     * contract_id 必填参数
     * @param FddContractManageMent $param
     * @param int $timeOut
     * @throws FddException
     * @return 成功时返回，其他抛异常
     */
    public static function GetContractHash(FddContractManageMent $param, $timeOut = 6)
    {
        //查询合同hash值接口
        $url = FddConfig::FddServer.'/getContractHash.api';
        try{
            if (!$param->IsContract_idSet())
                throw new FddException("缺少必填参数-contract_id");
            $param->SetApp_id(FddConfig::AppId);
            $param->SetTimestamp(date('YmdHis'));
            if (!$param->IsVSet()){
                $param->SetV('2.0');
            }
            //实例化3DES类
            $des = new FddEncryption();
            //设置加密串
            $enc = [
                'md5' => [],
                'sha1'=> ['contract_id']
            ];
            $param->SetMsg_digest($des::GeneralDigest($param,$enc));
            $input = $param->GetValues();
            $res = self::https_request($url,$input);
            return $res;
        }catch (FddException $e){

            return ['result'=>'error','code'=>2001,'msg'=>$e->errorMessage()];
        }

    }

    
    /**
     *
     * 4.14合同归档接口
     * @param FddContractManageMent $param
     * @param int $timeOut
     * @throws FddException
     * @return 成功时返回，其他抛异常
     */
    public static function ContractFiling(FddContractManageMent $param, $timeOut = 6)
    {
        //合同归档接口
        $url = FddConfig::FddServer.'/contractFiling.api';
        try{
            if (!$param->IsContract_idSet())
                throw new FddException("缺少必填参数-contract_id");
            $param->SetApp_id(FddConfig::AppId);
            $param->SetTimestamp(date('YmdHis'));
            if (!$param->IsVSet()){
                $param->SetV('2.0');
            }
            //实例化3DES类
            $des = new FddEncryption();
            //设置加密串
            $enc = [
                'md5' => [],
                'sha1'=> ['contract_id']
            ];
            $param->SetMsg_digest($des::GeneralDigest($param,$enc));
            $input = $param->GetValues();
            // header('location:'.$url.$des->ArrayParamToStr($input));
            $res = self::https_request($url,$input);
            return $res;
        }catch (FddException $e){

            return ['result'=>'error','code'=>2001,'msg'=>$e->errorMessage()];
        }

    }
    
    /**
     *
     *  文档验签接口
     * app_id、timestamp、msg_digest、doc_url、file必填参数 
     * @param FddSignContract $param
     * @param int $timeOut
     * @throws FddException
     * @return 成功时返回，其他抛异常
     */
    public static function ContractVerify(FddSignContract $param, $timeOut = 6)
    {
        //  文档验签接口 地址
        $url = FddConfig::FddServer.'/contract_verify.api';
        try{
            //参数处理
            if (!$param->IsFileSet() && !$param->IsDoc_urlSet())
                throw new FddException("缺少必填参数-file、doc_url 二选一");
                
            $param->SetApp_id(FddConfig::AppId);
            $param->SetTimestamp(date('YmdHis'));
            if (!$param->IsVSet()){
                $param->SetV('2.0');
            }
            //实例化3DES类
            $des = new FddEncryption();
            //设置加密串
            $enc = [
                'md5' => [],
                'sha1'=> ['doc_url']
            ];
            $param->SetMsg_digest($des::GeneralDigest($param,$enc));
            $input = $param->GetValues();
            $res = self::https_request($url,$input);
            return $res;
        }catch (FddException $e){

            return ['result'=>'error','code'=>2001,'msg'=>$e->errorMessage()];
        }
        
    }

    /**
     * 4.15两个接口为回调接口，法大大回调平台方
     */

     /**
     * 4.16查询个人实名认证信息
     * @param FddCertification $param
     * @param int $timeOut
     * @throws FddException
     * @return 成功时返回，其他抛异常
     */
    public static function FindPersonCertInfo(FddCertification $param, $timeOut = 6)
    {
        //查询个人实名认证信息
        $url = FddConfig::FddServer.'/find_personCertInfo.api';
        try{
            //实例化3DES类
            $des = new FddEncryption();
            $enc = [
                'md5' => [],
                'sha1'=>['verified_serialno']
            ];

            $param->SetApp_id(FddConfig::AppId);
            $param->SetTimestamp(date('YmdHis'));
            if (!$param->IsVSet()){
                $param->SetV('2.0');
            }
            $param->SetMsg_digest($des::GeneralDigest($param,$enc));

            $input = $param->GetValues();
            $res = self::https_request($url,$input);

            return $res;

        }catch (FddException $e){

            return ['result'=>'error','code'=>2001,'msg'=>$e->errorMessage()];
        }

    }
    
      /**
     * 4.17查询企业实名认证信息
     * @param FddCertification $param
     * @param int $timeOut
     * @throws FddException
     * @return 成功时返回，其他抛异常
     */
    public static function FindCompanyCertInfo(FddCertification $param, $timeOut = 6)
    {
        //查询企业实名认证信息
        $url = FddConfig::FddServer.'/find_companyCertInfo.api';
        try{
            //实例化3DES类
            $des = new FddEncryption();
            $enc = [
                'md5' => [],
                'sha1'=>['verified_serialno']
            ];

            $param->SetApp_id(FddConfig::AppId);
            $param->SetTimestamp(date('YmdHis'));
            if (!$param->IsVSet()){
                $param->SetV('2.0');
            }
            $param->SetMsg_digest($des::GeneralDigest($param,$enc));

            $input = $param->GetValues();
            $res = self::https_request($url,$input);
            return $res;

        }catch (FddException $e){

            return ['result'=>'error','code'=>2001,'msg'=>$e->errorMessage()];
        }

    }
    
    /**
     *
     * 4.18通过uuid下载文件
     * @param
     * @throws FddException
     * @return 成功时返回，其他抛异常
     */
    public static function getFile(FddCertification $param, $timeOut = 6)
    {
        // 通过uuid下载文件
        $url = FddConfig::FddServer.'/get_file.api';
        try{
            //参数处理
            if (!$param->IsUUID())
                throw new FddException("缺少必填参数-uuid");

            //实例化3DES类
            $des = new FddEncryption();
            $encArr = $param->GetValues();
            $encKey = array_keys($encArr);
            // 参数升序排序
            array_multisort($encKey);
            $enc = [
                'md5' => [],
                'sha1'=>$encKey
            ];

            $param->SetApp_id(FddConfig::AppId);
            $param->SetTimestamp(date('YmdHis'));
            if (!$param->IsVSet()){
                $param->SetV('2.0');
            }

            $param->SetMsg_digest($des::GeneralDigest($param,$enc));
            $input = $param->GetValues();
            header('location:'.$url.$des->ArrayParamToStr($input));
             $res = self::https_request($url,$input);
             return $res;
            $end = $url.$des->ArrayParamToStr($input);
            return $end;
        }catch (FddException $e){
            return ['result'=>'error','code'=>2001,'msg'=>$e->errorMessage()];
        }

    }

    /* =========================================== 2021-10-22 xjf 新增接口 === start =============================================*/


    /**
     * 4.21 获取授权自动签页面接口
     *
     * @param FddAuthSign $param
     * @param int $timeOut
     * @return array|mixed
     */
    public static function BeforeAuthsign(FddAuthSign $param, $timeOut = 6){
        // 获取授权自动签页面接口
        $url = FddConfig::FddServer.'/before_authsign.api';

        try{
            // 参数处理
            if (!$param->IsTransaction_idSet()){
                throw new FddException("缺少必填参数-transaction_id");
            }
            if (!$param->IsAuth_typeSet()){
                throw new FddException("缺少必填参数-auth_type");
            }
            if (!$param->IsContract_idSet()){
                throw new FddException("缺少必填参数-contract_id");
            }
            if (!$param->IsCustomerId()){
                throw new FddException("缺少必填参数-customer_id");
            }
            if (!$param->IsReturn_urlSet()){
                throw new FddException("缺少必填参数-return_url");
            }
            if (!$param->IsNotify_urlSet()){
                throw new FddException("缺少必填参数-notify_url");
            }

            $param->SetApp_id(FddConfig::AppId);
            $param->SetTimestamp(date('YmdHis'));
            if (!$param->IsVSet()){
                $param->SetV('2.0');
            }

            //实例化3DES类
            $des = new FddEncryption();
            $enc = [
                'md5' => ['transaction_id'],
                'sha1'=>['customer_id']
            ];
            $param->SetMsg_digest($des::AuthSignDigest($param,$enc));

            $input = $param->GetValues();
//            return self::https_request($url,$input);
            // 注意：如果是用作 web ，则使用 htmlspecialchars 方法，防止转义
//            return htmlspecialchars($url.$des->ArrayParamToStr($input));
            return $url.$des->ArrayParamToStr($input);
        }catch (FddException $e){
            return ['result'=>'error','code'=>2001,'msg'=>$e->errorMessage()];
        }
    }

    /**
     * 4.22 查询授权自动签状态接口
     *
     * @param FddAuthSign $param
     * @param int $timeOut
     * @return array|mixed
     */
    public static function GetAuthStatus(FddAuthSign $param, $timeOut = 6){
        // 查询授权自动签状态接口
        $url = FddConfig::FddServer.'/get_auth_status.api';

        try{
            // 参数处理
            if (!$param->IsCustomerId()){
                throw new FddException("缺少必填参数-customer_id");
            }

            $param->SetApp_id(FddConfig::AppId);
            $param->SetTimestamp(date('YmdHis'));
            if (!$param->IsVSet()){
                $param->SetV('2.0');
            }

            //实例化3DES类
            $des = new FddEncryption();
            $enc = [
                'md5' => [],
                'sha1'=>['customer_id']
            ];
            $param->SetMsg_digest($des::AuthSignDigest($param,$enc));

            $input = $param->GetValues();
            $res = self::https_request($url,$input);
            return $res;
        }catch (FddException $e){
            return ['result'=>'error','code'=>2001,'msg'=>$e->errorMessage()];
        }
    }

    /**
     * 4.23 取消授权签协议接口
     *
     * @param FddAuthSign $param
     * @param int $timeOut
     * @return array|mixed
     */
    public static function CancelExtsignAutoPage(FddAuthSign $param, $timeOut = 6){
        // 取消授权签协议接口
        $url = FddConfig::FddServer.'/cancel_extsign_auto_page.api';

        try{
            // 参数处理
            if (!$param->IsCustomerId()){
                throw new FddException("缺少必填参数-customer_id");
            }
            if (!$param->IsReturn_urlSet()){
                throw new FddException("缺少必填参数-return_url");
            }
            if (!$param->IsNotify_urlSet()){
                throw new FddException("缺少必填参数-notify_url");
            }

            $param->SetApp_id(FddConfig::AppId);
            $param->SetTimestamp(date('YmdHis'));
            if (!$param->IsVSet()){
                $param->SetV('2.0');
            }

            //实例化3DES类
            $des = new FddEncryption();
            $enc = [
                'md5' => [],
                'sha1'=>['customer_id','notify_url','return_url']
            ];
            $param->SetMsg_digest($des::AuthSignDigest($param,$enc));

            $input = $param->GetValues();
            return self::https_request($url,$input);
        }catch (FddException $e){
            return ['result'=>'error','code'=>2001,'msg'=>$e->errorMessage()];
        }
    }

    /**
     * 客户签署状态查询接口
     *
     * @param GeneralParam $param
     * @param int $timeOut
     * @return array|mixed
     */
    public static function QuerySignstatus(GeneralParam $param, $timeOut = 6){
        // 客户签署状态查询接口
        $url = FddConfig::FddServer.'/query_signstatus.api';

        try{
            // 参数处理
            if (!$param->IsCustomerId()){
                throw new FddException("缺少必填参数-customer_id");
            }
            if (!$param->IsContract_idSet()){
                throw new FddException("缺少必填参数-contract_id");
            }

            $param->SetApp_id(FddConfig::AppId);
            $param->SetTimestamp(date('YmdHis'));
            if (!$param->IsVSet()){
                $param->SetV('2.0');
            }

            //实例化3DES类
            $des = new FddEncryption();
            $enc = [
                'md5' => [],
                'sha1'=>['contract_id','customer_id']
            ];
            $param->SetMsg_digest($des::GeneralDigest($param,$enc));

            $input = $param->GetValues();
            return self::https_request($url,$input);
        }catch (FddException $e){
            return ['result'=>'error','code'=>2001,'msg'=>$e->errorMessage()];
        }
    }

    /**
     * 文档批量下载接口
     *
     * @param FddContractManageMent $param
     * @param int $timeOut
     * @return array|mixed
     */
    public static function BatchDownloadContract(FddContractManageMent $param, $timeOut = 6){
        // 文档批量下载接口
        $url = FddConfig::FddServer.'/batch_download_contract.api';

        try{
            // 参数处理
            if (!$param->IsContract_idsSet()){
                throw new FddException("缺少必填参数-contract_ids");
            }

            $param->SetApp_id(FddConfig::AppId);
            $param->SetTimestamp(date('YmdHis'));
            if (!$param->IsVSet()){
                $param->SetV('2.0');
            }

            //实例化3DES类
            $des = new FddEncryption();
            $enc = [
                'md5' => [],
                'sha1'=>['contract_ids']
            ];
            $param->SetMsg_digest($des::GeneralDigest($param,$enc));

            $input = $param->GetValues();
            return self::https_request($url,$input);
        }catch (FddException $e){
            return ['result'=>'error','code'=>2001,'msg'=>$e->errorMessage()];
        }
    }

    /**
     * 生成短链接口
     *
     * @param FddSignContract $param
     * @param int $timeOut
     * @return array|mixed
     */
    public static function ShortUrl(FddSignContract $param, $timeOut = 6){
        // 生成短链接口
        $url = FddConfig::FddServer.'/short_url.api';

        try{
            // 参数处理
            if (!$param->IsSource_urlSet()){
                throw new FddException("缺少必填参数-source_url");
            }

            $param->SetApp_id(FddConfig::AppId);
            $param->SetTimestamp(date('YmdHis'));
            if (!$param->IsVSet()){
                $param->SetV('2.0');
            }

            //实例化3DES类
            $des = new FddEncryption();
            $enc = [
                'md5' => [],
                'sha1'=>['expire_time', 'source_url']
            ];
            $param->SetMsg_digest($des::GeneralDigest($param,$enc));

            $input = $param->GetValues();
            return self::https_request($url,$input);
        }catch (FddException $e){
            return ['result'=>'error','code'=>2001,'msg'=>$e->errorMessage()];
        }
    }

    /**
     * 自定义短信发送短链接口
     *
     * @param SmsParam $param
     * @param int $timeOut
     * @return array|mixed
     */
    public static function PushShortUrlSms(SmsParam $param, $timeOut = 6){
        // 自定义短信发送短链接口
        $url = FddConfig::FddServer.'/push_short_url_sms.api';

        try{
            // 参数处理
            if (!$param->IsSource_urlSet()){
                throw new FddException("缺少必填参数-source_url");
            }
            if (!$param->IsExpire_timeSet()){
                throw new FddException("缺少必填参数-expire_time");
            }
            if (!$param->IsMobile()){
                throw new FddException("缺少必填参数-mobile");
            }
            if (!$param->IsMessage_typeSet()){
                throw new FddException("缺少必填参数-message_type");
            }

            $param->SetApp_id(FddConfig::AppId);
            $param->SetTimestamp(date('YmdHis'));
            if (!$param->IsVSet()){
                $param->SetV('2.0');
            }

            //实例化3DES类
            $des = new FddEncryption();
            $enc = [
                'md5' => [],
                'sha1'=>['expire_time', 'message_content','message_type','mobile', 'sms_template_type','source_url']
            ];
            $param->SetMsg_digest($des::GeneralDigest($param,$enc));

            $input = $param->GetValues();
            return self::https_request($url,$input);
        }catch (FddException $e){
            return ['result'=>'error','code'=>2001,'msg'=>$e->errorMessage()];
        }
    }

    /**
     * 自定义短信发送接口
     *
     * @param SmsParam $param
     * @param int $timeOut
     * @return array|mixed
     */
    public static function SmsText(SmsParam $param, $timeOut = 6){
        // 自定义短信发送接口
        $url = FddConfig::FddServer.'/sms_text.api';

        try{
            // 参数处理
            if (!$param->IsMobile()){
                throw new FddException("缺少必填参数-mobile");
            }
            if (!$param->IsMessage_typeSet()){
                throw new FddException("缺少必填参数-message_type");
            }

            // 手机号加密：encrypt_type：不传默认为0。
            //                     0-3DES，密钥为appsecret，
            //                     1-SM4（ECB模式），密钥为appsecret转为16进制后取后32位。（此方式不做）
            //                     2-不加密
            if ($param->IsEncrypt_typeSet()){
                if ("0" == $param->GetEncrypt_type()){
                    $encrypt = new FddEncryption();
                    $param->SetMobile($encrypt->encrypt($param->getMobile()));
                }
            }

            $param->SetApp_id(FddConfig::AppId);
            $param->SetTimestamp(date('YmdHis'));
            if (!$param->IsVSet()){
                $param->SetV('2.0');
            }

            //实例化3DES类
            $des = new FddEncryption();
            $enc = [
                'md5' => ['mobile', 'message_type', 'message_content', 'code','encrypt_type'],
                'sha1'=>[]
            ];
            $param->SetMsg_digest($des::GeneralDigest($param,$enc));

            $input = $param->GetValues();
            return self::https_request($url,$input);
        }catch (FddException $e){
            return ['result'=>'error','code'=>2001,'msg'=>$e->errorMessage()];
        }
    }

    /**
     * 电子文件签署线上出证专业版接口
     *
     * @param SmsParam $param
     * @param int $timeOut
     * @return array|mixed
     */
    public static function ComplianceContractReport(ComplianceContractReport $param, $timeOut = 6){
        // 电子文件签署线上出证专业版接口
        $url = FddConfig::FDDWitnessServer.'/api/compliance-contract-report';

        try{
            // 参数处理
            if (!$param->IsContractNumSet()){
                throw new FddException("缺少必填参数-contractNum");
            }

            $param->SetTimestamp(date('YmdHis'));
            if (!$param->IsVSet()){
                $param->SetV('2.0');
            }

            //实例化3DES类
            $des = new FddEncryption();
            $enc = [
                'md5' => [],
                'sha1'=>['contractNum', 'account']
            ];
            $param->SetMsgDigest($des::GeneralDigest($param,$enc));

            $input = $param->GetValues();
            return self::https_request($url,$input);
        }catch (FddException $e){
            return ['result'=>'error','code'=>2001,'msg'=>$e->errorMessage()];
        }
    }

    /**
     * 查看合同模板
     *
     * @param SmsParam $param
     * @param int $timeOut
     * @return array|mixed
     */
    public static function ViewTemplate(FddTemplate $param, $timeOut = 6){
        // 查看合同模板
        $url = FddConfig::FddServer.'/view_template.api';

        try{
            // 参数处理
            if (!$param->IsTemplate_idSet()){
                throw new FddException("缺少必填参数-template_id");
            }

            $param->SetApp_id(FddConfig::AppId);
            $param->SetTimestamp(date('YmdHis'));
            if (!$param->IsVSet()){
                $param->SetV('2.0');
            }

            //实例化3DES类
            $des = new FddEncryption();
            $enc = [
                'md5' => [],
                'sha1'=>['template_id']
            ];
            $param->SetMsg_digest($des::GeneralDigest($param,$enc));

            $input = $param->GetValues();
            // 注意：如果是用作 web ，则使用 htmlspecialchars 方法，防止转义
//            return htmlspecialchars($url.$des->ArrayParamToStr($input));
            return $url.$des->ArrayParamToStr($input);
        }catch (FddException $e){
            return ['result'=>'error','code'=>2001,'msg'=>$e->errorMessage()];
        }
    }

    /**
     * 合同模板下载
     *
     * @param SmsParam $param
     * @param int $timeOut
     * @return array|mixed
     */
    public static function DownloadTemplate(FddTemplate $param, $timeOut = 6){
        // 合同模板下载
        $url = FddConfig::FddServer.'/api/download_template.api';

        try{
            // 参数处理
            if (!$param->IsTemplate_idSet()){
                throw new FddException("缺少必填参数-template_id");
            }

            $param->SetApp_id(FddConfig::AppId);
            $param->SetTimestamp(date('YmdHis'));
            if (!$param->IsVSet()){
                $param->SetV('2.0');
            }

            //实例化3DES类
            $des = new FddEncryption();
            $enc = [
                'md5' => [],
                'sha1'=>['template_id']
            ];
            $param->SetMsg_digest($des::GeneralDigest($param,$enc));

            $input = $param->GetValues();
            return self::https_request($url,$input);
        }catch (FddException $e){
            return ['result'=>'error','code'=>2001,'msg'=>$e->errorMessage()];
        }
    }

    /**
     * 合同模板删除
     *
     * @param SmsParam $param
     * @param int $timeOut
     * @return array|mixed
     */
    public static function TemplateDelete(FddTemplate $param, $timeOut = 6){
        // 合同模板删除
        $url = FddConfig::FddServer.'/api/template_delete.api';

        try{
            // 参数处理
            if (!$param->IsTemplate_idSet()){
                throw new FddException("缺少必填参数-template_id");
            }

            $param->SetApp_id(FddConfig::AppId);
            $param->SetTimestamp(date('YmdHis'));
            if (!$param->IsVSet()){
                $param->SetV('2.0');
            }

            //实例化3DES类
            $des = new FddEncryption();
            $enc = [
                'md5' => [],
                'sha1'=>['template_id']
            ];
            $param->SetMsg_digest($des::GeneralDigest($param,$enc));

            $input = $param->GetValues();
            return self::https_request($url,$input);
        }catch (FddException $e){
            return ['result'=>'error','code'=>2001,'msg'=>$e->errorMessage()];
        }
    }

    /**
     * 合同模板图片下载
     *
     * @param SmsParam $param
     * @param int $timeOut
     * @return array|mixed
     */
    public static function DownloadTemplateImgs(FddTemplate $param, $timeOut = 6){
        // 合同模板图片下载
        $url = FddConfig::FddServer.'/api/download_template_imgs.api';

        try{
            // 参数处理
            if (!$param->IsTemplate_idSet()){
                throw new FddException("缺少必填参数-template_id");
            }

            $param->SetApp_id(FddConfig::AppId);
            $param->SetTimestamp(date('YmdHis'));
            if (!$param->IsVSet()){
                $param->SetV('2.0');
            }

            //实例化3DES类
            $des = new FddEncryption();
            $enc = [
                'md5' => [],
                'sha1'=>['template_id']
            ];
            $param->SetMsg_digest($des::GeneralDigest($param,$enc));

            $input = $param->GetValues();
            return self::https_request($url,$input);
        }catch (FddException $e){
            return ['result'=>'error','code'=>2001,'msg'=>$e->errorMessage()];
        }
    }

    /**
     * 获取 pdf 模版表单域 key 值接口
     *
     * @param SmsParam $param
     * @param int $timeOut
     * @return array|mixed
     */
    public static function GetPdftemplateKeys(FddTemplate $param, $timeOut = 6){
        // 获取 pdf 模版表单域 key 值接口
        $url = FddConfig::FddServer.'/api/get_pdftemplate_keys.api';

        try{
            // 参数处理
            if (!$param->IsTemplate_idSet()){
                throw new FddException("缺少必填参数-template_id");
            }

            $param->SetApp_id(FddConfig::AppId);
            $param->SetTimestamp(date('YmdHis'));
            if (!$param->IsVSet()){
                $param->SetV('2.0');
            }

            //实例化3DES类
            $des = new FddEncryption();
            $enc = [
                'md5' => [],
                'sha1'=>['template_id']
            ];
            $param->SetMsg_digest($des::GeneralDigest($param,$enc));

            $input = $param->GetValues();
            return self::https_request($url,$input);
        }catch (FddException $e){
            return ['result'=>'error','code'=>2001,'msg'=>$e->errorMessage()];
        }
    }

    /**
     * 添加表单域到模板
     *
     * @param SmsParam $param
     * @param int $timeOut
     * @return array|mixed
     */
    public static function AddKeys(FddTemplate $param, $timeOut = 6){
        // 添加表单域到模板
        $url = FddConfig::FddServer.'/api/add_keys.api';

        try{
            // 参数处理
            if (!$param->IsContract_template_idSet()){
                throw new FddException("缺少必填参数-contract_template_id");
            }

            $param->SetApp_id(FddConfig::AppId);
            $param->SetTimestamp(date('YmdHis'));
            if (!$param->IsVSet()){
                $param->SetV('2.0');
            }

            //实例化3DES类
            $des = new FddEncryption();
            $enc = [
                'md5' => [],
                'sha1'=>['contract_template_id']
            ];
            $param->SetMsg_digest($des::GeneralDigest($param,$enc));

            $input = $param->GetValues();
            return self::https_request($url,$input);
        }catch (FddException $e){
            return ['result'=>'error','code'=>2001,'msg'=>$e->errorMessage()];
        }
    }

    /**
     * 上传合同模板接口
     *
     * @param SmsParam $param
     * @param int $timeOut
     * @return array|mixed
     */
    public static function UploadTemplateDocs(FddTemplate $param, $timeOut = 6){
        // 上传合同模板接口
        $url = FddConfig::FddServer.'/api/upload_template_docs.api';

        try{
            // 参数处理
            if (!$param->IsContract_template_idSet()){
                throw new FddException("缺少必填参数-contract_template_id");
            }
            if (!$param->IsFileSet()){
                throw new FddException("缺少必填参数-file");
            }

            $param->SetApp_id(FddConfig::AppId);
            $param->SetTimestamp(date('YmdHis'));
            if (!$param->IsVSet()){
                $param->SetV('2.0');
            }

            //实例化3DES类
            $des = new FddEncryption();
            $enc = [
                'md5' => [],
                'sha1'=>['contract_template_id']
            ];
            $param->SetMsg_digest($des::GeneralDigest($param,$enc));

            $input = $param->GetValues();
            return self::https_request($url,$input);
        }catch (FddException $e){
            return ['result'=>'error','code'=>2001,'msg'=>$e->errorMessage()];
        }
    }

    /**
     * 根据模板id跳转编辑页面
     *
     * @param SmsParam $param
     * @param int $timeOut
     * @return array|mixed
     */
    public static function GetDocStream(FddTemplate $param, $timeOut = 6){
        // 根据模板id跳转编辑页面
        $url = FddConfig::FddServer.'/api/get_doc_stream.api';

        try{
            // 参数处理
            if (!$param->IsContract_template_idSet()){
                throw new FddException("缺少必填参数-contract_template_id");
            }

            $param->SetApp_id(FddConfig::AppId);
            $param->SetTimestamp(date('YmdHis'));
            if (!$param->IsVSet()){
                $param->SetV('2.0');
            }

            //实例化3DES类
            $des = new FddEncryption();
            $enc = [
                'md5' => [],
                'sha1'=>['contract_template_id']
            ];
            $param->SetMsg_digest($des::GeneralDigest($param,$enc));

            $input = $param->GetValues();
            // 注意：如果是用作 web ，则使用 htmlspecialchars 方法，防止转义
//            return htmlspecialchars($url.$des->ArrayParamToStr($input));
            return $url.$des->ArrayParamToStr($input);
        }catch (FddException $e){
            return ['result'=>'error','code'=>2001,'msg'=>$e->errorMessage()];
        }
    }

    /**
     * 跳转合同填充页面接口
     *
     * @param SmsParam $param
     * @param int $timeOut
     * @return array|mixed
     */
    public static function FillPage(FddTemplate $param, $timeOut = 6){
        // 跳转合同填充页面接口
        $url = FddConfig::FddServer.'/api/fill_page.api';

        try{
            // 参数处理
            if (!$param->IsContract_template_idSet()){
                throw new FddException("缺少必填参数-contract_template_id");
            }
            if (!$param->IsContract_idSet()){
                throw new FddException("缺少必填参数-contract_id");
            }
            if (!$param->IsDoc_titleSet()){
                throw new FddException("缺少必填参数-doc_title");
            }

            $param->SetApp_id(FddConfig::AppId);
            $param->SetTimestamp(date('YmdHis'));
            if (!$param->IsVSet()){
                $param->SetV('2.0');
            }

            //实例化3DES类
            $des = new FddEncryption();
            $enc = [
                'md5' => [],
                'sha1'=>['contract_id', 'contract_template_id']
            ];
            $param->SetMsg_digest($des::GeneralDigest($param,$enc));

            $input = $param->GetValues();
            // 注意：如果是用作 web ，则使用 htmlspecialchars 方法，防止转义
//            return htmlspecialchars($url.$des->ArrayParamToStr($input));
            return $url.$des->ArrayParamToStr($input);
        }catch (FddException $e){
            return ['result'=>'error','code'=>2001,'msg'=>$e->errorMessage()];
        }
    }

    /**
     * 快捷签署接口（个人）
     *
     * @param SmsParam $param
     * @param int $timeOut
     * @return array|mixed
     */
    public static function PersonVerifySign(FddSignContract $param, $timeOut = 6){
        // 快捷签署接口（个人）
        $url = FddConfig::FddServer.'/api/person_verify_sign.api';

        try{
            // 参数处理
            if (!$param->IsTransaction_idSet()){
                throw new FddException("缺少必填参数-transaction_id");
            }
            if (!$param->IsContract_idSet()){
                throw new FddException("缺少必填参数-contract_id");
            }
            if (!$param->IsCustomer_idSet()){
                throw new FddException("缺少必填参数-customer_id");
            }
            if (!$param->IsNotify_urlSet()){
                throw new FddException("缺少必填参数-notify_url");
            }
            if (!$param->IsPageModifySet()){
                throw new FddException("缺少必填参数-page_modify");
            }
            if (!$param->IsVerified_notify_urlSet()){
                throw new FddException("缺少必填参数-verified_notify_url");
            }
            if (!$param->IsVerifiedWaySet()){
                throw new FddException("缺少必填参数-verified_way");
            }

            //实例化3DES类
            $des = new FddEncryption();
            $encArr = $param->GetValues();
            $encKey = array_keys($encArr);
            // 参数升序排序
            array_multisort($encKey);
            $enc = [
                'md5' => [],
                'sha1'=>$encKey
            ];

            $param->SetApp_id(FddConfig::AppId);
            $param->SetTimestamp(date('YmdHis'));
            if (!$param->IsVSet()){
                $param->SetV('2.0');
            }

            $param->SetMsg_digest($des::GeneralDigest($param,$enc));

            $input = $param->GetValues();
            return self::https_request($url,$input);
        }catch (FddException $e){
            return ['result'=>'error','code'=>2001,'msg'=>$e->errorMessage()];
        }
    }

    /**
     * 批量快捷签署接口（个人）
     *
     * @param SmsParam $param
     * @param int $timeOut
     * @return array|mixed
     */
    public static function BatchQuickSign(FddSignContract $param, $timeOut = 6){
        // 批量快捷签署接口（个人）
        $url = FddConfig::FddServer.'/api/batch_quick_sign.api';

        try{
            // 参数处理
            if (!$param->IsCustomer_idSet()){
                throw new FddException("缺少必填参数-customer_id");
            }
            if (!$param->IsBatch_idSet()){
                throw new FddException("缺少必填参数-batch_id");
            }
            if (!$param->IsSign_dataSet()){
                throw new FddException("缺少必填参数-sign_data");
            }
            if (!$param->IsBatch_titleSet()){
                throw new FddException("缺少必填参数-batch_title");
            }
            if (!$param->IsReturn_urlSet()){
                throw new FddException("缺少必填参数-return_url");
            }
            if (!$param->IsNotify_urlSet()){
                throw new FddException("缺少必填参数-notify_url");
            }
            if (!$param->IsPageModifySet()){
                throw new FddException("缺少必填参数-page_modify");
            }
            if (!$param->IsVerified_notify_urlSet()){
                throw new FddException("缺少必填参数-verified_notify_url");
            }
            if (!$param->IsVerifiedWaySet()){
                throw new FddException("缺少必填参数-verified_way");
            }

            // sign_data 使用 URLEncoder，编码UTF-8
            $param->SetSign_data(urlencode($param->GetSign_data()));

            //实例化3DES类
            $des = new FddEncryption();
            $encArr = $param->GetValues();
            $encKey = array_keys($encArr);
            // 参数升序排序
            array_multisort($encKey);
            $enc = [
                'md5' => [],
                'sha1'=>$encKey
            ];

            $param->SetApp_id(FddConfig::AppId);
            $param->SetTimestamp(date('YmdHis'));
            if (!$param->IsVSet()){
                $param->SetV('2.0');
            }

            $param->SetMsg_digest($des::GeneralDigest($param,$enc));

            $input = $param->GetValues();
            return self::https_request($url,$input);
        }catch (FddException $e){
            return ['result'=>'error','code'=>2001,'msg'=>$e->errorMessage()];
        }
    }

    /**
     * 骑缝章自动签
     *
     * @param SmsParam $param
     * @param int $timeOut
     * @return array|mixed
     */
    public static function DocusignAcrosspage(DocusignAcrosspage $param, $timeOut = 6){
        // 骑缝章自动签
        $url = FddConfig::FddServer.'/api/docusign_acrosspage.api';

        try{
            // 参数处理
            if (!$param->IsTransaction_idSet()){
                throw new FddException("缺少必填参数-transaction_id");
            }
            if (!$param->IsContract_idSet()){
                throw new FddException("缺少必填参数-contract_id");
            }
            if (!$param->IsCustomer_idSet()){
                throw new FddException("缺少必填参数-customer_id");
            }

            //实例化3DES类
            $des = new FddEncryption();
            $enc = [
                'md5' => [],
                'sha1'=>['transaction_id','contract_id','customer_id']
            ];

            $param->SetApp_id(FddConfig::AppId);
            $param->SetTimestamp(date('YmdHis'));
            if (!$param->IsVSet()){
                $param->SetV('2.0');
            }

            $param->SetMsg_digest($des::GeneralDigest($param,$enc));

            $input = $param->GetValues();
            return self::https_request($url,$input);
        }catch (FddException $e){
            return ['result'=>'error','code'=>2001,'msg'=>$e->errorMessage()];
        }
    }

    /**
     * 文档批量签署接口(半自动模式)
     *
     * @param SmsParam $param
     * @param int $timeOut
     * @return array|mixed
     */
    public static function GotoBatchSemiautoSignPage(FddSignContract $param, $timeOut = 6){
        // 文档批量签署接口(半自动模式)
        $url = FddConfig::FddServer.'/api/gotoBatchSemiautoSignPage.api';

        try{
            // 参数处理
            if (!$param->IsBatch_idSet()){
                throw new FddException("缺少必填参数-batch_id");
            }
            if (!$param->IsBatch_titleSet()){
                throw new FddException("缺少必填参数-batch_title");
            }
            if (!$param->IsSign_dataSet()){
                throw new FddException("缺少必填参数-sign_data");
            }
            if (!$param->IsCustomer_idSet()){
                throw new FddException("缺少必填参数-customer_id");
            }
            if (!$param->IsReturn_urlSet()){
                throw new FddException("缺少必填参数-return_url");
            }

            // sign_data 使用 URLEncoder，编码UTF-8
            $param->SetSign_data(urlencode($param->GetSign_data()));

            //实例化3DES类
            $des = new FddEncryption();
            $enc = [
                'md5' => ['batch_id'],
                'sha1'=>['customer_id','outh_customer_id']
            ];

            $param->SetApp_id(FddConfig::AppId);
            $param->SetTimestamp(date('YmdHis'));
            if (!$param->IsVSet()){
                $param->SetV('2.0');
            }

            $param->SetMsg_digest($des::AuthSignDigest($param,$enc));

            $input = $param->GetValues();
            // 注意：如果是用作 web ，则使用 htmlspecialchars 方法，防止转义
//            return htmlspecialchars($url.$des->ArrayParamToStr($input));
            return $url.$des->ArrayParamToStr($input);
        }catch (FddException $e){
            return ['result'=>'error','code'=>2001,'msg'=>$e->errorMessage()];
        }
    }

    /**
     * 文档批量签署接口（全自动模式）
     *
     * @param SmsParam $param
     * @param int $timeOut
     * @return array|mixed
     */
    public static function ExtBatchSignAuto(FddSignContract $param, $timeOut = 6){
        // 文档批量签署接口（全自动模式）
        $url = FddConfig::FddServer.'/api/extBatchSignAuto.api';

        try{
            // 参数处理
            if (!$param->IsBatch_idSet()){
                throw new FddException("缺少必填参数-batch_id");
            }
            if (!$param->IsBatch_titleSet()){
                throw new FddException("缺少必填参数-batch_title");
            }
            if (!$param->IsSign_dataSet()){
                throw new FddException("缺少必填参数-sign_data");
            }
            if (!$param->IsNotify_urlSet()){
                throw new FddException("缺少必填参数-notify_url");
            }

            //实例化3DES类
            $des = new FddEncryption();
            $enc = [
                'md5' => ['batch_id'],
                'sha1'=>['sign_data']
            ];

            $param->SetApp_id(FddConfig::AppId);
            $param->SetTimestamp(date('YmdHis'));
            if (!$param->IsVSet()){
                $param->SetV('2.0');
            }

            $param->SetMsg_digest($des::AuthSignDigest($param,$enc));

            $input = $param->GetValues();
            return self::https_request($url,$input);
        }catch (FddException $e){
            return ['result'=>'error','code'=>2001,'msg'=>$e->errorMessage()];
        }
    }

    /**
     * 企业授权接口
     *
     * @param SmsParam $param
     * @param int $timeOut
     * @return array|mixed
     */
    public static function Authorization(FddSignature $param, $timeOut = 6){
        // 企业授权接口
        $url = FddConfig::FddServer.'/api/authorization.api';

        try{
            // 参数处理
            if (!$param->IsCompany_id()){
                throw new FddException("缺少必填参数-company_id");
            }
            if (!$param->IsPerson_id()){
                throw new FddException("缺少必填参数-person_id");
            }
            if (!$param->IsOperate_type()){
                throw new FddException("缺少必填参数-operate_type");
            }

            //实例化3DES类
            $des = new FddEncryption();
            $enc = [
                'md5' => [],
                'sha1'=>['company_id','person_id','operate_type']
            ];

            $param->SetApp_id(FddConfig::AppId);
            $param->SetTimestamp(date('YmdHis'));
            if (!$param->IsVSet()){
                $param->SetV('2.0');
            }

            $param->SetMsg_digest($des::GeneralDigest($param,$enc));

            $input = $param->GetValues();
            return self::https_request($url,$input);
        }catch (FddException $e){
            return ['result'=>'error','code'=>2001,'msg'=>$e->errorMessage()];
        }
    }

    /**
     * 企业印章单个授权接口
     *
     * @param SmsParam $param
     * @param int $timeOut
     * @return array|mixed
     */
    public static function AuthorizeSignature(FddSignature $param, $timeOut = 6){
        // 企业印章单个授权接口
        $url = FddConfig::FddServer.'/api/authorize_signature.api';

        try{
            // 参数处理
            if (!$param->IsCompany_id()){
                throw new FddException("缺少必填参数-company_id");
            }
            if (!$param->IsPerson_id()){
                throw new FddException("缺少必填参数-person_id");
            }
            if (!$param->IsOperate_type()){
                throw new FddException("缺少必填参数-operate_type");
            }
            if (!$param->IsSignature_id()){
                throw new FddException("缺少必填参数-signature_id");
            }

            //实例化3DES类
            $des = new FddEncryption();
            $enc = [
                'md5' => [],
                'sha1'=>['company_id','operate_type','person_id','signature_id']
            ];

            $param->SetApp_id(FddConfig::AppId);
            $param->SetTimestamp(date('YmdHis'));
            if (!$param->IsVSet()){
                $param->SetV('2.0');
            }

            $param->SetMsg_digest($des::GeneralDigest($param,$enc));

            $input = $param->GetValues();
            return self::https_request($url,$input);
        }catch (FddException $e){
            return ['result'=>'error','code'=>2001,'msg'=>$e->errorMessage()];
        }
    }

    /**
     * 查询印章授权关系
     *
     * @param SmsParam $param
     * @param int $timeOut
     * @return array|mixed
     */
    public static function FindSignatureAuthList(FddSignature $param, $timeOut = 6){
        // 查询印章授权关系
        $url = FddConfig::FddServer.'/api/find_signature_auth_list.api';

        try{
            // 参数处理
            if (!$param->IsTypeSet()){
                throw new FddException("缺少必填参数-type");
            }

            //实例化3DES类
            $des = new FddEncryption();
            $enc = [
                'md5' => [],
                'sha1'=>['customer_id','signature_id','type']
            ];

            $param->SetApp_id(FddConfig::AppId);
            $param->SetTimestamp(date('YmdHis'));
            if (!$param->IsVSet()){
                $param->SetV('2.0');
            }

            $param->SetMsg_digest($des::GeneralDigest($param,$enc));

            $input = $param->GetValues();
            return self::https_request($url,$input);
        }catch (FddException $e){
            return ['result'=>'error','code'=>2001,'msg'=>$e->errorMessage()];
        }
    }

    /**
     * 查询签章接口
     *
     * @param SmsParam $param
     * @param int $timeOut
     * @return array|mixed
     */
    public static function QuerySignature(FddSignature $param, $timeOut = 6){
        // 查询签章接口
        $url = FddConfig::FddServer.'/api/query_signature.api';

        try{
            // 参数处理
            if (!$param->IsCustomerId()){
                throw new FddException("缺少必填参数-customer_id");
            }

            //实例化3DES类
            $des = new FddEncryption();
            $enc = [
                'md5' => [],
                'sha1'=>['customer_id','signature_id']
            ];

            $param->SetApp_id(FddConfig::AppId);
            $param->SetTimestamp(date('YmdHis'));
            if (!$param->IsVSet()){
                $param->SetV('2.0');
            }

            $param->SetMsg_digest($des::GeneralDigest($param,$enc));

            $input = $param->GetValues();
            return self::https_request($url,$input);
        }catch (FddException $e){
            return ['result'=>'error','code'=>2001,'msg'=>$e->errorMessage()];
        }
    }

    /**
     * 替换签章接口
     *
     * @param SmsParam $param
     * @param int $timeOut
     * @return array|mixed
     */
    public static function ReplaceSignature(FddSignature $param, $timeOut = 6){
        // 替换签章接口
        $url = FddConfig::FddServer.'/api/replace_signature.api';

        try{
            // 参数处理
            if (!$param->IsCustomerId()){
                throw new FddException("缺少必填参数-customer_id");
            }
            if (!$param->IsSignature_id()){
                throw new FddException("缺少必填参数-signature_id");
            }
            if (!$param->IsSignatureImgBase64()){
                throw new FddException("缺少必填参数-signature_img_base64");
            }

            //实例化3DES类
            $des = new FddEncryption();
            $enc = [
                'md5' => [],
                'sha1'=>['customer_id','signature_id','signature_img_base64']
            ];

            $param->SetApp_id(FddConfig::AppId);
            $param->SetTimestamp(date('YmdHis'));
            if (!$param->IsVSet()){
                $param->SetV('2.0');
            }

            $param->SetMsg_digest($des::GeneralDigest($param,$enc));

            $input = $param->GetValues();
            return self::https_request($url,$input);
        }catch (FddException $e){
            return ['result'=>'error','code'=>2001,'msg'=>$e->errorMessage()];
        }
    }

    /**
     * 删除签章接口
     *
     * @param SmsParam $param
     * @param int $timeOut
     * @return array|mixed
     */
    public static function RemoveSignature(FddSignature $param, $timeOut = 6){
        // 删除签章接口
        $url = FddConfig::FddServer.'/api/remove_signature.api';

        try{
            // 参数处理
            if (!$param->IsCustomerId()){
                throw new FddException("缺少必填参数-customer_id");
            }
            if (!$param->IsSignature_id()){
                throw new FddException("缺少必填参数-signature_id");
            }

            //实例化3DES类
            $des = new FddEncryption();
            $enc = [
                'md5' => [],
                'sha1'=>['customer_id','signature_id']
            ];

            $param->SetApp_id(FddConfig::AppId);
            $param->SetTimestamp(date('YmdHis'));
            if (!$param->IsVSet()){
                $param->SetV('2.0');
            }

            $param->SetMsg_digest($des::GeneralDigest($param,$enc));

            $input = $param->GetValues();
            return self::https_request($url,$input);
        }catch (FddException $e){
            return ['result'=>'error','code'=>2001,'msg'=>$e->errorMessage()];
        }
    }

    /**
     * 设置默认章接口
     *
     * @param SmsParam $param
     * @param int $timeOut
     * @return array|mixed
     */
    public static function DefaultSignature(FddSignature $param, $timeOut = 6){
        // 设置默认章接口
        $url = FddConfig::FddServer.'/api/default_signature.api';

        try{
            // 参数处理
            if (!$param->IsCustomerId()){
                throw new FddException("缺少必填参数-customer_id");
            }

            //实例化3DES类
            $des = new FddEncryption();
            $enc = [
                'md5' => [],
                'sha1'=>['customer_id','signature_id']
            ];

            $param->SetApp_id(FddConfig::AppId);
            $param->SetTimestamp(date('YmdHis'));
            if (!$param->IsVSet()){
                $param->SetV('2.0');
            }

            $param->SetMsg_digest($des::GeneralDigest($param,$enc));

            $input = $param->GetValues();
            return self::https_request($url,$input);
        }catch (FddException $e){
            return ['result'=>'error','code'=>2001,'msg'=>$e->errorMessage()];
        }
    }


    /* =========================================== 2021-10-22 xjf 新增接口 === end =============================================*/


    /**
     * 通用http函数
     * @param $url
     * @param string $data
     * @param string $type
     * @param string $res
     * @return mixed
     */
    public static function https_request($url,$data = "",$type="post",$res="json"){
        //1.初始化curl
        $curl = curl_init();
        // 设置 utf-8 编码
        $this_header=array(
            "charset=UTF-8"
        );
        //2.设置curl的参数
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,2);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl,CURLOPT_HTTPHEADER,$this_header);
        if ($type == "post"){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        //3.采集
        $output = curl_exec($curl);
        //4.关闭
        curl_close($curl);
        if ($res == "json") {
            return json_decode($output,true);
        }
        return $output;
    }

    /**
     * 文件输出http函数
     * @param $url
     * @param string $data
     * @param string $type
     * @return mixed
     */
    public static function https_request_file($url,$data = "",$type="post"){
        //1.初始化curl
        $curl = curl_init();
        //2.设置curl的参数
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,2);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        if ($type == "post"){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        //3.采集
        $output = curl_exec($curl);
        header("Content-type: application/octet-stream");

        header("Content-Disposition: attachment; filename=原文出证".time().".pdf");

        echo $output;

        //4.关闭
        curl_close($curl);
    }

    /**
     * 图片转base64文件
     * @param $image_file
     * @return string
     */
    public function base64EncodeImage ($image_file)
    {
        $base64_image = "";
        $image_info = getimagesize($image_file);
        $image_data = fread(fopen($image_file, "r"), filesize($image_file));
        $base64_image = "data:" . $image_info["mime"] . ";base64," . chunk_split(base64_encode($image_data));
        return $base64_image;
    }

} ?>