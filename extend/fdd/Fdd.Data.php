<?php
require_once "Fdd.Config.php";
require_once "Fdd.Exception.php";

/**
 * 数据对象基础类，该类中定义数据类最基本的行为，包括：
 * 计算/设置/获取签名、输出xml格式的参数、从xml读取数据对象等
 * @author widyhu
 */
class FddDataBase
{
    protected $values = array();

    /**
     * 输出xml字符
     * @throws FddException
     **/
    public function ToXml()
    {
        if (!is_array($this->values) || count($this->values) <= 0) {
            throw new FddException("数组数据异常！");
        }

        $xml = "<xml>";
        foreach ($this->values as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "<" . $key . ">";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]><" . $key . ">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }

    /**
     * 将xml转为array
     * @param string $xml
     * @return  $this->value
     * @throws FddException
     */
    public function FromXml($xml)
    {
        if (!$xml) {
            throw new FddException("xml数据异常！");
        }
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $this->values = json_decode(json_encode(simplexml_load_string($xml, "SimpleXMLElement", LIBXML_NOCDATA)), true);
        return $this->values;
    }

    /**
     * 格式化参数格式化成url参数
     */
    public function ToUrlParams()
    {
        $buff = "";
        foreach ($this->values as $k => $v) {
            if ($k != "sign" && $v != "" && !is_array($v)) {
                $buff .= $k . "=" . $v . "&";
            }
        }

        $buff = trim($buff, "&");
        return $buff;
    }

    /**
     * 设置AppID
     * @param string $value
     **/
    public function SetApp_id($value)
    {
        $this->values["app_id"] = $value;
    }

    /**
     * 获取AppId
     * @return 值
     **/
    public function GetApp_id()
    {
        return $this->values["app_id"];
    }

    /**
     * 判断AppId是否存在
     * @return true 或 false
     **/
    public function IsApp_idSet()
    {
        return array_key_exists("organization", $this->values);
    }

    /**
     * 设置请求时间
     * @param string $value
     **/
    public function SetTimestamp($value)
    {
        $this->values["timestamp"] = $value;
    }

    /**
     * 获取请求时间
     * @return 值
     **/
    public function GetTimestamp()
    {
        return $this->values["timestamp"];
    }

    /**
     * 判断请求时间是否存在
     * @return true 或 false
     **/
    public function IsTimestampSet()
    {
        return array_key_exists("timestamp", $this->values);
    }

    /**
     * 设置版本号
     * @param string $value
     **/
    public function SetV($value)
    {
        $this->values["v"] = $value;
    }

    /**
     * 获取版本号
     * @return 值
     **/
    public function GetV()
    {
        return $this->values["v"];
    }

    /**
     * 判断版本号是否存在
     * @return true 或 false
     **/
    public function IsVSet()
    {
        return array_key_exists("v", $this->values);
    }

    /**
     * 设置消息摘要
     * @param string $value
     **/
    public function SetMsg_digest($value)
    {
        $this->values["msg_digest"] = $value;
    }

    /**
     * 获取消息摘要
     * @return 值
     **/
    public function GetMsg_digest()
    {
        return $this->values["msg_digest"];
    }

    /**
     * 判断消息摘要是否存在
     * @return true 或 false
     **/
    public function IsMsg_digestSet()
    {
        return array_key_exists("msg_digest", $this->values);
    }

    /**
     * 获取设置的值
     */
    public function GetValues()
    {
        return $this->values;
    }
}

/**
 * 合规化方案 账号注册
 * Class FddAccount
 */
class FddAccount extends FddDataBase
{

    /**
     * 设置 用户在接入方的唯一标识
     * @param string $value
     **/
    public function SetOpenID($value)
    {
        $this->values['open_id'] = $value;
    }

    /**
     * 判断 唯一标识 是否存在
     * @return true 或 false
     **/
    public function IsOpenIDSet()
    {


        return array_key_exists('open_id', $this->values);
    }

    /**
     * 设置用户类型 1:个人，2:企业
     * @param string $value
     **/
    public function SetAccountType($value)
    {
        $this->values['account_type'] = $value;
    }

    /**
     * 判断 唯一标识 是否存在
     * @return true 或 false
     **/
    public function IsAccountTypeSet()
    {
        return array_key_exists('account_type', $this->values);
    }
}

/**
 * 合规化方案 印章
 * Class FddAccount
 */
class FddSignature extends FddDataBase
{

    /**
     * 设置 客户编号
     * @param string $value
     **/
    public function SetCustomerId($value)
    {
        $this->values['customer_id'] = $value;
    }

    /**
     * 判断 客户编号 是否存在
     * @return true 或 false
     **/
    public function IsCustomerId()
    {
        return array_key_exists('customer_id', $this->values);
    }

    /**
     * @param string $value
     **/
    public function SetSignatureImgBase64($value)
    {
        $this->values['signature_img_base64'] = $value;
    }

    /**
     * @return true 或 false
     **/
    public function IsSignatureImgBase64()
    {
        return array_key_exists('signature_img_base64', $this->values);
    }

    /**
     * 设置 企业客户编号
     * @param string $value
     **/
    public function SetCompany_id($value)
    {
        $this->values['company_id'] = $value;
    }

    /**
     * 判断 企业客户编号 是否存在
     * @return true 或 false
     **/
    public function IsCompany_id()
    {
        return array_key_exists('company_id', $this->values);
    }

    /**
     * 设置 个人客户编号
     * @param string $value
     **/
    public function SetPerson_id($value)
    {
        $this->values['person_id'] = $value;
    }

    /**
     * 判断 个人客户编号 是否存在
     * @return true 或 false
     **/
    public function IsPerson_id()
    {
        return array_key_exists('person_id', $this->values);
    }

    /**
     * 设置 操作类型
     * @param string $value
     **/
    public function SetOperate_type($value)
    {
        $this->values['operate_type'] = $value;
    }

    /**
     * 判断 操作类型 是否存在
     * @return true 或 false
     **/
    public function IsOperate_type()
    {
        return array_key_exists('operate_type', $this->values);
    }

    /**
     * 设置 签章Id
     * @param string $value
     **/
    public function SetSignature_id($value)
    {
        $this->values['signature_id'] = $value;
    }

    /**
     * 判断 签章Id 是否存在
     * @return true 或 false
     **/
    public function IsSignature_id()
    {
        return array_key_exists('signature_id', $this->values);
    }

    /**
     * 设置 查询类型,1查授权关系 2查印章持有人 3查印章所属，此时传客户编号
     * @param string $value
     **/
    public function SetType($value)
    {
        $this->values['type'] = $value;
    }

    /**
     * 判断 查询类型,1查授权关系 2查印章持有人 3查印章所属，此时传客户编号 是否存在
     * @return true 或 false
     **/
    public function IsTypeSet()
    {
        return array_key_exists('type', $this->values);
    }
}

/**
 * 合规化方案 印章自定义内容
 * Class FddAccount
 */
class FddSignatureContent extends FddDataBase
{

    /**
     * 设置 客户编号
     * @param string $value
     **/
    public function SetCustomerId($value)
    {
        $this->values['customer_id'] = $value;
    }

    /**
     * 判断 客户编号 是否存在
     * @return true 或 false
     **/
    public function IsCustomerId()
    {
        return array_key_exists('customer_id', $this->values);
    }

    /**
     * @param string $value
     **/
    public function SetContent($value)
    {
        $this->values['content'] = $value;
    }

    /**
     * @return true 或 false
     **/
    public function IsContent()
    {
        return array_key_exists('content', $this->values);
    }
}

/**
 * 模板填充的动态表格参数实体
 */
class FddTemplateDynamicTable {

    /**
     * @var 动态表格插入方式:0：新建页面添加table（默认） 1：在某个关键字后添加table
     */
    var $insertWay=0;

    /**
     * @var 关键字方式插入动态表格:1. 当insertWay为1时，必填
     *                          2. 要求该关键字后（当前页）必须不包含内容，否则会被覆盖
     *                          3. 若关键字为多个 ，则取第一个关键字，在此关键字后插入table
     */
    var $keyword;

    /**
     * @var 表格需要插入的页数:1. 当insertWay为0时，必填
     *                       2. 表示从第几页开始插入表格，如要从末尾插入table,则pageBegin为pdf总页数加1
     *                       3. 多个表格指定相同pageBegin，则多个表格按顺序插入，一个表格新起一页
     *                       4. pageBegin为-1时，则从pdf末尾插入table
     */
    var $pageBegin;

    /**
     * @var table是否有边框:true：有（默认）   false：无边框
     */
    var $borderFlag=true;

    /**
     * @var 正文行高（表头不受影响）:单位：pt，即point，等于1/72英寸
     */
    var $cellHeight;

    /**
     * @var Table中每个单元的水平对齐方式:0：居左；1：居中；2：居右  默认为0
     */
    var $cellHorizontalAlignment=0;

    /**
     * @var Table中每个单元的垂直对齐方式: (4：居上；5：居中；6：居下)   默认为4
     */
    var $cellVerticalAlignment=4;

    /**
     * @var 表头上方的一级标题
     */
    var $theFirstHeader;

    /**
     * @var 表头信息: 类型是 Array[String]
     */
    var $headers;

    /**
     * @var 表头对齐方式:(0居左；1居中；2居右)   默认0
     */
    var $headersAlignment=0;

    /**
     * @var 正文: 类型是 Array[Array[String]]。(外层表示行，内层表示列)
     */
    var $datas;

    /**
     * @var 各列宽度比例: 类型是 Array[Integer]。 默认值：各列1:1
     */
    var $colWidthPercent=array();

    /**
     * @var table的水平对齐方式: (0居左，1居中，2居右) 默认1
     */
    var $tableHorizontalAlignment=1;

    /**
     * @var table宽度的百分比: (0<tableWidthPercentage<=100) 默认为100.0
     */
    var $tableWidthPercentage=100.0;

    /**
     * @var 设置table居左居中居右后的水平偏移量: (向右偏移值为正数，向左偏移值为负数)默认为0.0，单位px(像素)
     */
    var $tableHorizontalOffset=0.0;


    function SetInsertWay($value){
        $this->insertWay = $value;
    }

    function GetInsertWay(){
        return $this->insertWay;
    }

    function SetKeyword($value){
        $this->keyword = $value;
    }

    function GetKeyword(){
        return $this -> keyword;
    }

    function SetPageBegin($value){
        $this->pageBegin = $value;
    }

    function GetPageBegin(){
        return $this -> pageBegin;
    }

    function SetBorderFlag($value){
        $this-> borderFlag = $value;
    }

    function GetBorderFlag(){
        return $this -> borderFlag;
    }

    function SetCellHeight($value){
        $this-> cellHeight = $value;
    }

    function GetCellHeight(){
        return $this -> cellHeight;
    }

    function SetCellHorizontalAlignment($value){
        $this-> cellHorizontalAlignment = $value;
    }

    function GetCellHorizontalAlignment(){
        return $this -> cellHorizontalAlignment;
    }

    function SetCellVerticalAlignment($value){
        $this-> cellVerticalAlignment = $value;
    }

    function GetCellVerticalAlignment(){
        return $this -> cellVerticalAlignment;
    }

    function SetTheFirstHeader($value){
        $this-> theFirstHeader = $value;
    }

    function GetTheFirstHeader(){
        return $this -> theFirstHeader;
    }

    function SetHeaders($value){
        $this-> headers = $value;
    }

    function GetHeaders(){
        return $this -> headers;
    }

    function SetHeadersAlignment($value){
        $this-> headersAlignment = $value;
    }

    function GetHeadersAlignment(){
        return $this -> headersAlignment;
    }

    function SetDatas($value){
        $this-> datas = $value;
    }

    function GetDatas(){
        return $this -> datas;
    }

    function SetColWidthPercent($value){
        $this-> colWidthPercent = $value;
    }

    function GetColWidthPercent(){
        return $this -> colWidthPercent;
    }

    function SetTableHorizontalAlignment($value){
        $this-> tableHorizontalAlignment = $value;
    }

    function GetTableHorizontalAlignment(){
        return $this -> tableHorizontalAlignment;
    }

    function SetTableWidthPercentage($value){
        $this-> tableWidthPercentage = $value;
    }

    function GetTableWidthPercentage(){
        return $this -> tableWidthPercentage;
    }

    function SetTableHorizontalOffset($value){
        $this-> tableHorizontalOffset = $value;
    }

    function GetTableHorizontalOffset(){
        return $this -> tableHorizontalOffset;
    }
}

/**
 * 合同文档模板和生成类
 * Class FddTemplate
 */
class FddTemplate extends FddDataBase
{
    /**
     * 设置 模板编号
     * @param string $value
     **/
    public function SetTemplate_id($value)
    {
        $this->values['template_id'] = $value;
    }

    /**
     * 获取 模板编号
     * @return 值
     **/
    public function GetTemplate_id()
    {
        return $this->values['template_id'];
    }

    /**
     * 判断 模板编号 是否存在
     * @return true 或 false
     **/
    public function IsTemplate_idSet()
    {
        return array_key_exists('template_id', $this->values);
    }

    /**
     * 设置 文档类型
     * @param string $value
     **/
    public function SetDoc_type($value)
    {
        $this->values['doc_type'] = $value;
    }

    /**
     * 判断 文档类型 是否存在
     * @param string $value
     **/
    public function IsDoc_typeSet()
    {
        return array_key_exists('doc_type', $this->values);
    }

    /**
     * 设置 文档地址
     * @param string $value
     **/
    public function SetDoc_url($value)
    {
        $this->values['doc_url'] = $value;
    }

    /**
     * 判断 文档地址 是否存在
     * @return true 或 false
     **/
    public function IsDoc_urlSet()
    {
        return array_key_exists('doc_url', $this->values);
    }

    /**
     * 设置 文档标题
     * @param string $value
     **/
    public function SetDoc_title($value)
    {
        $this->values['doc_title'] = $value;
    }

    /**
     * 获取 文档标题
     * @return 值
     **/
    public function GetDoc_title()
    {
        return $this->values['doc_title'];
    }

    /**
     * 判断 文档标题 是否存在
     * @return true 或 false
     **/
    public function IsDoc_titleSet()
    {
        return array_key_exists('doc_title', $this->values);
    }

    /**
     * 设置 PDF模板
     * @param string $value
     **/
    public function SetFile($value)
    {
        $this->values['file'] = $value;
    }

    /**
     * 判断 PDF模板 是否存在
     * @return true 或 false
     **/
    public function IsFileSet()
    {
        return array_key_exists('file', $this->values);
    }

    /**
     * 设置 合同编号
     * @param string $value
     **/
    public function SetContract_id($value)
    {
        $this->values['contract_id'] = $value;
    }

    /**
     * 获取 合同编号
     * @return 值
     **/
    public function GetContract_id()
    {
        return $this->values['contract_id'];
    }

    /**
     * 判断 合同编号 是否存在
     * @return true 或 false
     **/
    public function IsContract_idSet()
    {
        return array_key_exists('contract_id', $this->values);
    }

    /**
     * 设置 字体大小
     * @param string $value
     **/
    public function SetFont_size($value)
    {
        $this->values['font_size'] = $value;
    }

    /**
     * 获取 字体大小
     * @return 值
     **/
    public function GetFont_size()
    {
        return $this->values['font_size'];
    }

    /**
     * 判断 字体大小 是否存在
     * @return true 或 false
     **/
    public function IsFont_sizeSet()
    {
        return array_key_exists('font_size', $this->values);
    }

    /**
     * 设置 字体类型
     * @param string $value
     **/
    public function SetFont_type($value)
    {
        $this->values['font_type'] = $value;
    }

    /**
     * 获取 字体类型
     * @return 值
     **/
    public function GetFont_type()
    {
        return $this->values['font_type'];
    }

    /**
     * 判断 字体类型 是否存在
     * @return true 或 false
     **/
    public function IsFont_typeSet()
    {
        return array_key_exists('font_type', $this->values);
    }

    /**
     * 设置 填充内容
     * @param string $value
     **/
    public function SetParameter_map($value)
    {
        $this->values['parameter_map'] = $value;
    }

    /**
     * 获取 填充内容
     * @return 值
     **/
    public function GetParameter_map()
    {
        return $this->values['parameter_map'];
    }

    /**
     * 判断 填充内容 是否存在
     * @return true 或 false
     **/
    public function IsParameter_mapSet()
    {
        return array_key_exists('parameter_map', $this->values);
    }

    /**
     * 设置 动态表格
     * @param string $value
     **/
    public function SetDynamic_tables($value)
    {
        $this->values['dynamic_tables'] = $value;
    }

    /**
     * 获取 动态表格
     * @return 值
     **/
    public function GetDynamic_tables()
    {
        return $this->values['dynamic_tables'];
    }

    /**
     * 判断 动态表格 是否存在
     * @return true 或 false
     **/
    public function IsDynamic_tablesSet()
    {
        return array_key_exists('dynamic_tables', $this->values);
    }



    /**
     * 设置 0：pdf模板；1：在线填充模板 不填默认为0
     * @param string $value
     **/
    public function SetFile_type($value)
    {
        $this->values['fill_type'] = $value;
    }

    /**
     * 判断 0：pdf模板；1：在线填充模板 不填默认为0 是否存在
     * @return true 或 false
     **/
    public function IsFill_typeSet()
    {
        return array_key_exists('fill_type', $this->values);
    }

    /**
     * 设置 在线模板 Id
     * @param string $value
     **/
    public function SetContract_template_id($value)
    {
        $this->values['contract_template_id'] = $value;
    }

    /**
     * 判断 在线模板 Id 是否存在
     * @return true 或 false
     **/
    public function IsContract_template_idSet()
    {
        return array_key_exists('contract_template_id', $this->values);
    }

    /**
     * 设置 模板名称
     * @param string $value
     **/
    public function SetTemplate_name($value)
    {
        $this->values['template_name'] = $value;
    }

    /**
     * 判断 模板名称 是否存在
     * @return true 或 false
     **/
    public function IsTemplate_nameSet()
    {
        return array_key_exists('template_name', $this->values);
    }

    /**
     * 设置 页面跳转url（签名结果同步通知）
     * @param string $value
     **/
    public function SetReturn_url($value)
    {
        $this->values['return_url'] = $value;
    }

    /**
     * 判断 页面跳转url（签名结果同步通知） 是否存在
     **/
    public function IsReturn_urlSet()
    {
        return array_key_exists('return_url', $this->values);
    }

}

/**
 * 合同签署类
 * Class FddSignContract
 */
class FddSignContract extends FddDataBase
{

    /**
     * 设置 存证方案手动签署时所传身份证--用于刷脸验证，姓名和身份证需要同时传
     * @param string $value
     **/
    public function SetCustomerIdentNo($value)
    {
        $this->values['customer_ident_no'] = $value;
    }

    /**
     * 获取 存证方案手动签署时所传身份证--用于刷脸验证，姓名和身份证需要同时传
     * @param string $value
     **/
    public function GetCustomerIdentNo()
    {
        return $this->values['customer_ident_no'];
    }

    /**
     * 判断 存证方案手动签署时所传身份证 是否存在
     * @return true 或 false
     **/
    public function IsCustomerIdentNoSet()
    {
        return array_key_exists('customer_ident_no', $this->values);
    }

    /**
     * 设置 存证方案手动签署时所传姓名--用于刷脸验证，姓名和身份证需要同时传
     * @param string $value
     **/
    public function SetCustomerName($value)
    {
        $this->values['customer_name'] = $value;
    }

    /**
     * 获取 存证方案手动签署时所传姓名--用于刷脸验证，姓名和身份证需要同时传
     * @param string $value
     **/
    public function GetCustomerName()
    {
        return $this->values['customer_name'];
    }

    /**
     * 判断 存证方案手动签署时所传姓名 是否存在
     * @return true 或 false
     **/
    public function IsCustomerNameSet()
    {
        return array_key_exists('customer_name', $this->values);
    }

    /**
     * 设置 存证方案手动签署时所传手机号
     * @param string $value
     **/
    public function SetCustomerMobile($value)
    {
        $this->values['customer_mobile'] = $value;
    }

    /**
     * 获取 存证方案手动签署时所传手机号
     * @param string $value
     **/
    public function GetCustomerMobile()
    {
        return $this->values['customer_mobile'];
    }

    /**
     * 判断 存证方案手动签署时所传手机号 是否存在
     * @return true 或 false
     **/
    public function IsCustomerMobileSet()
    {
        return array_key_exists('customer_mobile', $this->values);
    }

    /**
     * 设置 签署时所传合同编号
     * @param string $value
     **/
    public function SetContract_id($value)
    {
        $this->values['contract_id'] = $value;
    }

    /**
     * 获取 签署时所传合同编号
     * @param string $value
     **/
    public function GetContract_id()
    {
        return $this->values['contract_id'];
    }

    /**
     * 判断 签署时所传合同编号 是否存在
     * @return true 或 false
     **/
    public function IsContract_idSet()
    {
        return array_key_exists('contract_id', $this->values);
    }

    /**
     * 设置 签署时所传客户编号
     * @param string $value
     **/
    public function SetCustomer_id($value)
    {
        $this->values['customer_id'] = $value;
    }

    /**
     * 获取 签署时所传合同编号
     * @param string $value
     **/
    public function GetCustomer_id()
    {
        return $this->values['customer_id'];
    }

    /**
     * 判断 签署时所传客户编号 是否存在
     * @return true 或 false
     **/
    public function IsCustomer_idSet()
    {
        return array_key_exists('customer_id', $this->values);
    }

    /**
     * 设置 签署时所传交易号
     * @param string $value
     **/
    public function SetTransaction_id($value)
    {
        $this->values['transaction_id'] = $value;
    }

    /**
     * 获取 是否设置有效期
     * @param string $value
     **/
    public function GetTransaction_id()
    {
        return $this->values['transaction_id'];
    }

    /**
     * 判断 签署时所传交易号 是否存在
     **/
    public function IsTransaction_idSet()
    {
        return array_key_exists('transaction_id', $this->values);
    }

    /**
     * 设置 有效时间
     * @param string $value
     **/
    public function SetExpire_time($value)
    {
        $this->values['expire_time'] = $value;
    }

    /**
     * 设置 传入url
     * @param string $value
     **/
    public function SetSource_url($value)
    {
        $this->values['source_url'] = $value;
    }

    /**
     * 判断 传入url 是否存在
     **/
    public function IsSource_urlSet()
    {
        return array_key_exists('source_url', $this->values);
    }

    /**
     * 设置 短信标识
     * @param string $value
     **/
    public function SetPush_type($value)
    {
        $this->values['push_type'] = $value;
    }

    /**
     * 判断 短信标识 是否存在
     **/
    public function IsPush_typeSet()
    {
        return array_key_exists('push_type', $this->values);
    }

    /**
     * 设置 定位关键字
     * @param string $value
     **/
    public function SetSign_keyword($value)
    {
        $this->values['sign_keyword'] = $value;
    }

    /**
     * 获取 有效期
     **/
    public function GetSign_keyword()
    {
        return $this->values['sign_keyword'];
    }

    /**
     * 判断 定位关键字 是否存在
     **/
    public function IsSign_keywordSet()
    {
        return array_key_exists('sign_keyword', $this->values);
    }

    /**
     * 设置 定位关键字(多)
     * @param string $value
     **/
    public function SetSign_keywords($value)
    {
        $this->values['sign_keywords'] = $value;
    }

    /**
     * 判断 定位关键字（多） 是否存在
     **/
    public function IsSign_keywordsSet()
    {
        return array_key_exists('sign_keywords', $this->values);
    }

    /**
     * 设置 是否设置有效期
     * @param string $value
     **/
    public function SetLimit_type($value)
    {
        $this->values['limit_type'] = $value;
    }

    /**
     * 获取 是否设置有效期
     **/
    public function GetLimit_type()
    {
        return $this->values['limit_type'];
    }

    /**
     * 判断 是否设置有效期 是否存在
     **/
    public function IsLimit_typeSet()
    {
        return array_key_exists('limit_type', $this->values);
    }

    /**
     * 设置 有效期
     * @param string $value
     **/
    public function SetValidity($value)
    {
        $this->values['validity'] = $value;
    }

    /**
     * 获取 有效期
     **/
    public function GetValidity()
    {
        return $this->values['validity'];
    }

    /**
     * 判断 有效期 是否存在
     **/
    public function IsValiditySet()
    {
        return array_key_exists('validity', $this->values);
    }

    /**
     * 设置 页面跳转url（签名结果同步通知）
     * @param string $value
     **/
    public function SetReturn_url($value)
    {
        $this->values['return_url'] = $value;
    }

    /**
     * 判断 页面跳转url（签名结果同步通知） 是否存在
     **/
    public function IsReturn_urlSet()
    {
        return array_key_exists('return_url', $this->values);
    }

    /**
     * 设置 签名结果异步步通知url
     * @param string $value
     **/
    public function SetNotify_url($value)
    {
        $this->values['notify_url'] = $value;
    }

    /**
     * 设置 签名结果异步步通知url
     * @param string $value
     **/
    public function IsNotify_urlSet()
    {
        return array_key_exists('notify_url', $this->values);
    }

    /**
     * 设置 文档标题
     * @param string $value
     **/
    public function SetDoc_title($value)
    {
        $this->values['doc_title'] = $value;
    }

    /**
     * 获取 文档标题
     * @param string $value
     **/
    public function GetDoc_title()
    {
        return $this->values['doc_title'];
    }

    /**
     * 判断 文档标题 是否存在
     * @return true 或 false
     **/
    public function IsDoc_titleSet()
    {
        return array_key_exists('doc_title', $this->values);
    }

    /**
     * 设置 手写章
     * @param string $value
     **/
    public function SetHandsignimg($value)
    {
        $this->values['handsignimg'] = $value;
    }

    /**
     * 设置 短信验证码
     * @param string $value
     **/
    public function SetSms($value)
    {
        $this->values['sms'] = $value;
    }

    /**
     * 判断 短信验证码 是否存在
     * @param string $value
     **/
    public function IsSmsSet()
    {
        return array_key_exists('sms', $this->values);
    }

    /**
     * 设置 短信校验令牌
     * @param string $value
     **/
    public function SetMarkUUID($value)
    {
        $this->values['markUUID'] = $value;
    }

    /**
     * 判断 短信校验令牌 是否存在
     * @param string $value
     **/
    public function IsMarkUUIDSet()
    {
        return array_key_exists('markUUID', $this->values);
    }

    /**
     * 设置 批量签署记录主键
     * @param string $value
     **/
    public function SetExtBatchSignId($value)
    {
        $this->values['extBatchSignId'] = $value;
    }

    /**
     * 判断 批量签署记录主键 是否存在
     * @param string $value
     **/
    public function IsExtBatchSignIdSet()
    {
        return array_key_exists('extBatchSignId', $this->values);
    }

    /**
     * 设置 填充内容
     * @param string $value
     **/
    public function SetParameter_map($value)
    {
        $this->values['parameter_map'] = $value;
    }

    /**
     * 判断 填充内容 是否存在
     * @return true 或 false
     **/
    public function IsParameter_mapSet()
    {
        return array_key_exists('parameter_map', $this->values);
    }

    /**
     * 设置 签署截止时间
     * @param string $value
     **/
    public function SetExpiration_time($value)
    {
        $this->values['expiration_time'] = $value;
    }

    /**
     * 判断 签署截止时间 是否存在
     * @return true 或 false
     **/
    public function IsExpiration_timeSet()
    {
        return array_key_exists('expiration_time', $this->values);
    }

    /**
     * 设置 是否发送通知（短信 及邮件）
     * @param string $value
     **/
    public function SetSend_msg($value)
    {
        $this->values['send_msg'] = $value;
    }

    /**
     * 判断 是否发送通知（短信 及邮件） 是否存在
     * @return true 或 false
     **/
    public function IsSend_msgSet()
    {
        return array_key_exists('send_msg', $this->values);
    }

    /**
     * 设置 待签署人姓名
     * @param string $value
     **/
    public function SetUser_names($value)
    {
        $this->values['user_names'] = $value;
    }

    /**
     * 判断 待签署人姓名 是否存在
     * @return true 或 false
     **/
    public function IsUser_namesSet()
    {
        return array_key_exists('user_names', $this->values);
    }

    /**
     * 设置 待签署人手机号
     * @param string $value
     **/
    public function SetUser_mobiles($value)
    {
        $this->values['user_mobiles'] = $value;
    }

    /**
     * 判断 待签署人手机号 是否存在
     * @return true 或 false
     **/
    public function IsUser_mobilesSet()
    {
        return array_key_exists('user_mobiles', $this->values);
    }

    /**
     * 设置 待签署人邮箱
     * @param string $value
     **/
    public function SetUser_emails($value)
    {
        $this->values['user_emails'] = $value;
    }

    /**
     * 判断 待签署人邮箱 是否存在
     * @return true 或 false
     **/
    public function IsUser_emailsSet()
    {
        return array_key_exists('user_emails', $this->values);
    }

    /**
     * 设置 批次号（流水号）
     * @param string $value
     **/
    public function SetBatch_id($value)
    {
        $this->values['batch_id'] = $value;
    }

    /**
     * 获取 批次号（流水号）
     * @param string $value
     **/
    public function GetBatch_id()
    {
        return $this->values['batch_id'];
    }

    /**
     * 判断 批次号（流水号） 是否存在
     * @return true 或 false
     **/
    public function IsBatch_idSet()
    {
        return array_key_exists('batch_id', $this->values);
    }

    /**
     * 设置 代理人客户编号
     * @param string $value
     **/
    public function SetOuth_customer_id($value)
    {
        $this->values['outh_customer_id'] = $value;
    }

    /**
     * 获取 代理人客户编号
     * @param string $value
     **/
    public function GetOuth_customer_id()
    {
        return $this->values['outh_customer_id'];
    }

    /**
     * 判断 代理人客户编号 是否存在
     * @return true 或 false
     **/
    public function IsOuth_customer_idSet()
    {
        return array_key_exists('outh_customer_id', $this->values);
    }

    /**
     * 设置 签章数据
     * @param string $value
     **/
    public function SetSign_data($value)
    {
        $this->values['sign_data'] = $value;
    }

    /**
     * 获取 签章数据
     * @param string $value
     **/
    public function GetSign_data()
    {
        return $this->values['sign_data'];
    }

    /**
     * 判断 签章数据 是否存在
     * @return true 或 false
     **/
    public function IsSign_dataSet()
    {
        return array_key_exists('sign_data', $this->values);
    }

    /**
     * 设置 批量请求标题
     * @param string $value
     **/
    public function SetBatch_title($value)
    {
        $this->values['batch_title'] = $value;
    }

    /**
     * 获取 批量请求标题
     * @param string $value
     **/
    public function GetBatch_title()
    {
        return $this->values['batch_title'];
    }

    /**
     * 判断 批量请求标题 是否存在
     * @return true 或 false
     **/
    public function IsBatch_titleSet()
    {
        return array_key_exists('batch_title', $this->values);
    }

    /**
     * 设置 客户类型
     * @param string $value
     **/
    public function SetClientType($value)
    {
        $this->values['clientType'] = $value;
    }

    /**
     * 判断 客户类型 是否存在
     * @return true 或 false
     **/
    public function IsClientTypeSet()
    {
        return array_key_exists('clientType', $this->values);
    }

    /**
     * 设置 客户角色
     * @param string $value
     **/
    public function SetClient_role($value)
    {
        $this->values['client_role'] = $value;
    }

    /**
     * 判断 客户角色 是否存在
     * @return true 或 false
     **/
    public function IsClient_roleSet()
    {
        return array_key_exists('client_role', $this->values);
    }

    /**
     * 设置 有效次数
     * @param string $value
     **/
    public function SetQuantity($value)
    {
        $this->values['quantity'] = $value;
    }

    /**
     * 获取 有效次数
     * @param string $value
     **/
    public function GetQuantity()
    {
        return $this->values['quantity'];
    }

    /**
     * 判断 有效次数 是否存在
     * @return true 或 false
     **/
    public function IsQuantitySet()
    {
        return array_key_exists('quantity', $this->values);
    }

    /**
     * 设置 关键字签章策略
     * @param string $value
     **/
    public function SetKeyword_strategy($value)
    {
        $this->values['keyword_strategy'] = $value;
    }

    /**
     * 判断 关键字签章策略 是否存在
     * @return true 或 false
     **/
    public function IsKeyword_strategySet()
    {
        return array_key_exists('keyword_strategy', $this->values);
    }

    /**
     * 设置 关键字签章策略
     * @param string $value
     **/
    public function SetAcrosspage_customer_id($value)
    {
        $this->values['acrosspage_customer_id'] = $value;
    }

    /**
     * 判断 关键字签章策略 是否存在
     * @return true 或 false
     **/
    public function IsAcrosspage_customer_idSet()
    {
        return array_key_exists('acrosspage_customer_id', $this->values);
    }

    /**
     * 设置 定位类型
     * @param string $value
     **/
    public function SetPosition_type($value)
    {
        $this->values['position_type'] = $value;
    }

    /**
     * 获取 定位类型
     * @param string $value
     **/
    public function GetPosition_type()
    {
        return $this->values['position_type'];
    }

    /**
     * 判断 定位类型 是否存在
     * @return true 或 false
     **/
    public function IsPosition_typeSet()
    {
        return array_key_exists('position_type', $this->values);
    }

    /**
     * 设置 盖章点x坐标
     * @param string $value
     **/
    public function SetX($value)
    {
        $this->values['x'] = $value;
    }

    /**
     * 获取 盖章点X坐标
     * @param string $value
     **/
    public function GetX()
    {
        return $this->values['x'];
    }

    /**
     * 判断 盖章点x坐标 是否存在
     * @return true 或 false
     **/
    public function IsXSet()
    {
        return array_key_exists('x', $this->values);
    }

    /**
     * 设置 签章页码，从0开始。
     * @param string $value
     **/
    public function SetPagenum($value)
    {
        $this->values['pagenum'] = $value;
    }

    /**
     * 获取 盖章点Y坐标
     * @param string $value
     **/
    public function GetPagenum()
    {
        return $this->values['pagenum'];
    }

    /**
     * 判断 签章页码，从 0开始。 是否存在
     * @return true 或 false
     **/
    public function IsPagenumSet()
    {
        return array_key_exists('pagenum', $this->values);
    }

    /**
     * 设置 定位坐标
     * @param string $value
     **/
    public function SetSignature_positions($value)
    {
        $this->values['signature_positions'] = $value;
    }

    /**
     * 设置 盖章点Y坐标
     * @param string $value
     **/
    public function SetY($value)
    {
        $this->values['y'] = $value;
    }

    /**
     * 获取 盖章点Y坐标
     * @param string $value
     **/
    public function GetY()
    {
        return $this->values['y'];
    }

    /**
     * 判断 盖章点Y坐标 是否存在
     * @return true 或 false
     **/
    public function IsYSet()
    {
        return array_key_exists('Y', $this->values);
    }

    /**
     * 设置 签章图片类型
     * @param string $value
     **/
    public function SetShow_type($value)
    {
        $this->values['show_type'] = $value;
    }

    /**
     * 设置 替换标志
     * @param string $value
     **/
    public function SetReplace_signature_flag($value)
    {
        $this->values['replace_signature_flag'] = $value;
    }

    /**
     * 设置 合同 url 地址
     * @param string $value
     **/
    public function SetDoc_url($value)
    {
        $this->values['doc_url'] = $value;
    }

    /**
     * 判断 合同 url 地址 是否存在
     * @return true 或 false
     **/
    public function IsDoc_urlSet()
    {
        return array_key_exists('doc_url', $this->values);
    }

    /**
     * 设置 合同流文件
     * @param string $value
     **/
    public function SetFile($value)
    {
        $this->values['file'] = $value;
    }

    /**
     * 判断 合同流文件 是否存在
     * @return true 或 false
     **/
    public function IsFileSet()
    {
        return array_key_exists('file', $this->values);
    }

    /**
     * 是否允许用户页面修改 1允许，2不允许
     * @param $value
     */
    public function SetPageModify($value)
    {
        $this->values['page_modify'] = $value;
    }

    /**
     *  判断 是否允许用户页面修改 是否存在
     * @return bool
     */
    public function IsPageModifySet()
    {
        return array_key_exists('page_modify', $this->values);
    }

    /**
     * 是否支持身份证以外其他证件类型:0：不支持（默认）；1：支持
     * @return
     **/
    public function GetCustomerIdentType()
    {
        return $this->values['customer_ident_type'];
    }

    /**
     * 判断 是否支持身份证以外其他证件类型:0：不支持（默认）；1：支持 是否存在
     * @return true 或 false
     **/
    public function IsCustomerIdentType()
    {
        return array_key_exists('customer_ident_type', $this->values);
    }

    /**
     * 设置 证书类型
     *
     * @param $value
     */
    public function SetCertType($value)
    {
        $this->values['cert_type'] = $value;
    }

    /**
     * 判断 证书类型 是否存在
     * @return true 或 false
     **/
    public function IsCertTypeSet()
    {
        return array_key_exists('cert_type', $this->values);
    }

    /**
     * 设置 手机号码
     * @param string $value
     **/
    public function SetMobile($value)
    {
        $this->values['mobile'] = $value;
    }

    /**
     * 判断 手机号码 是否存在
     * @return true 或 false
     **/
    public function IsMobileSet()
    {
        return array_key_exists('mobile', $this->values);
    }

    /**
     * 设置 地区码
     *
     * @param $value
     */
    public function SetAreaCode($value)
    {
        $this->values['area_code'] = $value;
    }

    /**
     * 判断 地区码 是否存在
     *
     * @return bool
     */
    public function IsAreaCodeSet()
    {
        return array_key_exists('area_code', $this->values);
    }

    /**
     * 设置 签章类型
     *
     * @param $value
     */
    public function SetMobile_sign_type($value)
    {
        $this->values['mobile_sign_type'] = $value;
    }

    /**
     * 判断 签章类型 是否存在
     *
     * @return bool
     */
    public function IsMobile_sign_typeSet()
    {
        return array_key_exists('mobile_sign_type', $this->values);
    }

    /**
     * 设置 认证结果异步回调地址
     *
     * @param $value
     */
    public function SetVerified_notify_url($value)
    {
        $this->values['verified_notify_url'] = $value;
    }

    /**
     * 判断 认证结果异步回调地址 是否存在
     *
     * @return bool
     */
    public function IsVerified_notify_urlSet()
    {
        return array_key_exists('verified_notify_url', $this->values);
    }

    /**
     * 实名认证套餐类型
     * @param $value
     */
    public function SetVerifiedWay($value)
    {
        $this->values['verified_way'] = $value;
    }

    /**
     *  判断 实名认证套餐类型 是否存在
     * @return bool
     */
    public function IsVerifiedWaySet()
    {
        return array_key_exists('verified_way', $this->values);
    }

    /**
     * 个人实名认证刷脸未通过是否允许人工审核
     * @param $value
     */
    public function SetPerson_auth_fail_allow_manual_audit($value)
    {
        $this->values['person_auth_fail_allow_manual_audit'] = $value;
    }

    /**
     *  判断 个人实名认证刷脸未通过是否允许人工审核 是否存在
     * @return bool
     */
    public function IsPerson_auth_fail_allow_manual_auditSet()
    {
        return array_key_exists('person_auth_fail_allow_manual_audit', $this->values);
    }

    /**
     * 银行卡号
     * @param $value
     */
    public function SetBankCardNo($value)
    {
        $this->values['bank_card_no'] = $value;
    }

    /**
     *  判断 银行卡号 是否存在
     * @return bool
     */
    public function IsBankCardNoSet()
    {
        return array_key_exists('bank_card_no', $this->values);
    }

    /**
     * 设置 证件正面照下载地址
     * @param string $value
     **/
    public function SetIdentFrontPath($value)
    {
        $this->values['ident_front_path'] = $value;
    }

    /**
     * 获取 证件正面照下载地址
     * @return
     **/
    public function GetIdentFrontPath()
    {
        return $this->values['ident_front_path'];
    }

    /**
     * 设置 证件反面照下载地址
     * @param string $value
     **/
    public function SetIdent_back_path($value)
    {
        $this->values['ident_back_path'] = $value;
    }

    /**
     * 获取 证件反面照下载地址
     * @return
     **/
    public function GetIdent_back_path()
    {
        return $this->values['ident_back_path'];
    }

    /**
     * 设置 是否需要上传身份证照片
     * @param string $value
     **/
    public function SetId_photo_optional($value)
    {
        $this->values['id_photo_optional'] = $value;
    }

    /**
     * 获取 是否需要上传身份证照片
     * @return
     **/
    public function GetId_photo_optional()
    {
        return $this->values['id_photo_optional'];
    }

    /**
     * 设置 印章是否显示时间
     * @param string $value
     **/
    public function SetSignature_show_time($value)
    {
        $this->values['signature_show_time'] = $value;
    }

    /**
     * 设置 关键字偏移量，偏移x位置
     * @param string $value
     **/
    public function SetKeyx($value)
    {
        $this->values['keyx'] = $value;
    }

    /**
     * 设置 关键字偏移量，偏移y位置
     * @param string $value
     **/
    public function SetKeyy($value)
    {
        $this->values['keyy'] = $value;
    }

    /**
     * 设置 意愿认证方式
     * @param string $value
     **/
    public function SetVerification_type($value)
    {
        $this->values['verification_type'] = $value;
    }

    /**
     * 设置 签章id
     * @param string $value
     **/
    public function SetSignature_id($value)
    {
        $this->values['signature_id'] = $value;
    }

    /**
     * 设置 是否开启手写轨迹
     * @param string $value
     **/
    public function SetWriting_track($value)
    {
        $this->values['writing_track'] = $value;
    }

    /**
     * 设置 合同必读时间
     * @param string $value
     **/
    public function SetRead_time($value)
    {
        $this->values['read_time'] = $value;
    }

    /**
     * 设置 页面语言
     *
     * @param $value
     */
    public function SetLang($value)
    {
        $this->values['lang'] = $value;
    }

    /**
     * 设置 支持pc手写印章
     *
     * @param $value
     */
    public function SetPc_hand_signature($value)
    {
        $this->values['pc_hand_signature'] = $value;
    }

    /**
     * 设置 签署意愿方式
     *
     * @param $value
     */
    public function SetSign_verify_way($value)
    {
        $this->values['sign_verify_way'] = $value;
    }

    /**
     * 设置 签署意愿方式选择人脸识别时， 人脸识别失败后自动调整为短信
     *
     * @param $value
     */
    public function SetVerify_way_flag($value)
    {
        $this->values['verify_way_flag'] = $value;
    }

    /**
     * 设置 打开环境
     *
     * @param $value
     */
    public function SetOpen_environment($value)
    {
        $this->values['open_environment'] = $value;
    }

    /**
     * 设置 骑缝章id
     * @param string $value
     **/
    public function SetAcross_signature_id($value)
    {
        $this->values['across_signature_id'] = $value;
    }

    /**
     * 判断 骑缝章id 是否存在
     * @return true 或 false
     **/
    public function IsAcross_signature_idSet()
    {
        return array_key_exists('across_signature_id', $this->values);
    }

}

/**
 * 合同签署状态查询类
 * Class FddQuerySignResult
 */
class FddQuerySignResult extends FddDataBase
{
    /**
     * 设置 签署时所传合同编号
     * @param string $value
     **/
    public function SetContract_id($value)
    {
        $this->values['contract_id'] = $value;
    }

    /**
     * 判断 签署时所传合同编号 是否存在
     * @return true 或 false
     **/
    public function IsContract_idSet()
    {
        return array_key_exists('contract_id', $this->values);
    }

    /**
     * 设置 签署时所传客户编号
     * @param string $value
     **/
    public function SetCustomer_id($value)
    {
        $this->values['customer_id'] = $value;
    }

    /**
     * 判断 签署时所传客户编号 是否存在
     * @return true 或 false
     **/
    public function IsCustomer_idSet()
    {
        return array_key_exists('customer_id', $this->values);
    }

    /**
     * 设置 签署时所传交易号
     * @param string $value
     **/
    public function SetTransaction_id($value)
    {
        $this->values['transaction_id'] = $value;
    }

    /**
     * 判断 签署时所传交易号 是否存在
     * @param string $value
     **/
    public function IsTransaction_idSet()
    {
        return array_key_exists('transaction_id', $this->values);
    }


}

/**
 * 合同管理类
 * Class FddContractManageMent
 */
class FddContractManageMent extends FddDataBase
{
    /**
     * 设置 合同编号
     * @param string $value
     **/
    public function SetContract_id($value)
    {
        $this->values['contract_id'] = $value;
    }

    /**
     * 获取 签署时所传合同编号
     * @param string $value
     **/
    public function GetContract_id()
    {
        return $this->values['contract_id'];
    }

    /**
     * 判断 签署时所传合同编号 是否存在
     * @return true 或 false
     **/
    public function IsContract_idSet()
    {
        return array_key_exists('contract_id', $this->values);
    }

    /**
     * 设置 合同编号（多）
     * @param string $value
     **/
    public function SetContract_ids($value)
    {
        $this->values['contract_ids'] = $value;
    }

    /**
     * 判断 签署时所传合同编号 是否存在
     * @return true 或 false
     **/
    public function IsContract_idsSet()
    {
        return array_key_exists('contract_ids', $this->values);
    }

    /**
     * 设置用户ID
     * @param string $value
     **/
    public function SetCustomer_id($value)
    {
        $this->values['customer_id'] = $value;
    }

    /**
     * 设置 有效期
     * @param string $value
     **/
    public function SetValidity($value)
    {
        $this->values['validity'] = $value;
    }

    /**
     * 判断 有效期 是否存在
     * @param string $value
     **/
    public function IsValiditySet()
    {
        return array_key_exists('validity', $this->values);
    }

    /**
     * 设置 有效次数
     * @param string $value
     **/
    public function SetQuantity($value)
    {
        $this->values['quantity'] = $value;
    }

    /**
     * 判断 有效次数 是否存在
     * @return true 或 false
     **/
    public function IsQuantitySet()
    {
        return array_key_exists('quantity', $this->values);
    }
}

/**
 * 用户管理类
 * Class FddUserManage
 */
class FddUserManage extends FddDataBase
{
    /**
     * 设置 合同ID
     * @param string $value
     **/
    public function SetContract_id($value)
    {
        $this->values['contract_id'] = $value;
    }

    /**
     * 设置 用户ID
     * @param string $value
     **/
    public function SetCustomer_id($value)
    {
        $this->values['customer_id'] = $value;
    }

    /**
     * 判断 用户ID 是否存在
     * @return true 或 false
     **/
    public function IsCustomer_idSet()
    {
        return array_key_exists('customer_id', $this->values);
    }

    /**
     * 设置 电子邮箱
     * @param string $value
     **/
    public function SetEmail($value)
    {
        $this->values['email'] = $value;
    }

    /**
     * 设置 手机号码
     * @param string $value
     **/
    public function SetMobile($value)
    {
        $this->values['mobile'] = $value;
    }
}

/**
 * 合规化方案 实名认证类
 * Class FddCertification
 */
class FddCertification extends FddDataBase
{
    /**
     * 客户编号 注册账号时返回
     * @param $value
     */
    public function SetCustomerID($value)
    {
        $this->values['customer_id'] = $value;
    }

    /**
     *  判断 客户编号 是否存在
     * @return bool
     */
    public function IsCustomerIDSet()
    {
        return array_key_exists('customer_id', $this->values);
    }

    /**
     * 实名认证套餐类型
     * @param $value
     */
    public function SetVerifiedWay($value)
    {
        $this->values['verified_way'] = $value;
    }

    /**
     *  判断 实名认证套餐类型 是否存在
     * @return bool
     */
    public function IsVerifiedWaySet()
    {
        return array_key_exists('verified_way', $this->values);
    }

    /**
     * 管理员 实名认证套餐类型 0:三要素标准方案； 6补充三要素方案+人工审核
     * @param $value
     */
    public function SetMVerifieday($value)
    {
        $this->values['m_verified_way'] = $value;
    }

    /**
     *  判断 管理员 实名认证套餐类型 是否存在
     * @return bool
     */
    public function IsMVerifiedaySet()
    {
        return array_key_exists('m_verified_way', $this->values);
    }

    /**
     * 是否允许用户页面修改 1允许，2不允许
     * @param $value
     */
    public function SetPageModify($value)
    {
        $this->values['page_modify'] = $value;
    }

    /**
     *  判断 是否允许用户页面修改 是否存在
     * @return bool
     */
    public function IsPageModifySet()
    {
        return array_key_exists('page_modify', $this->values);
    }

    /**
     *  认证回调地址
     * @param $value
     */
    public function SetNotifyUrl($value)
    {
        $this->values['notify_url'] = $value;
    }

    /**
     *  判断 认证回调地址 是否存在
     * @return bool
     */
    public function IsNotifyUrlSet()
    {
        return array_key_exists('notify_url', $this->values);
    }

    /**
     *  认证同步通知url
     * @param $value
     */
    public function SetReturnUrl($value)
    {
        $this->values['return_url'] = $value;
    }

    /**
     *  判断 认证同步通知url 是否存在
     * @return bool
     */
    public function IsReturnUrlSet()
    {
        return array_key_exists('return_url', $this->values);
    }

    /**
     *  企业信息
     * @param $value
     */
    public function SetCompanyInfo($value)
    {
        $this->values['company_info'] = $value;
    }

    /**
     *  判断 企业信息 是否存在
     * @return bool
     */
    public function IsCompanyInfo()
    {
        return array_key_exists('company_info', $this->values);
    }

    /**
     *  对公账号信息
     * @param $value
     */
    public function SetBankInfo($value)
    {
        $this->values['bank_info'] = $value;
    }

    /**
     *  判断 对公账号信息 是否存在
     * @return bool
     */
    public function IsBankInfo()
    {
        return array_key_exists('bank_info', $this->values);
    }

    /**
     *  企业负责人身份: 1.法人，2 代理人
     * @param $value
     */
    public function SetCompanyPrincipalType($value)
    {
        $this->values['company_principal_type'] = $value;
    }

    /**
     *  判断 企业负责人身份: 1.法人，2 代理人 是否存在
     * @return bool
     */
    public function IsCompanyPrincipalType()
    {
        return array_key_exists('company_principal_type', $this->values);
    }

    /**
     *  企业负责人身份: 1.法人，2 代理人
     * @param $value
     */
    public function SetLegalnfo($value)
    {
        $this->values['legal_info'] = $value;
    }

    /**
     *  判断 企业负责人身份: 1.法人，2 代理人 是否存在
     * @return bool
     */
    public function IsLegalnfo()
    {
        return array_key_exists('legal_info', $this->values);
    }

    /**
     *  企业负责人身份: 1.法人，2 代理人
     * @param $value
     */
    public function SetAgentInfo($value)
    {
        $this->values['agent_info'] = $value;
    }

    /**
     *  判断 企业负责人身份: 1.法人，2 代理人 是否存在
     * @return bool
     */
    public function IsAgentInfo()
    {
        return array_key_exists('agent_info', $this->values);
    }

    /**
     *  管理员 实名认证套餐类型
     * @param $value
     */
    public function SetMVerifiedWay($value)
    {
        $this->values['m_verified_way'] = $value;
    }

    /**
     *  判断 实名认证套餐类型 是否存在
     * @return bool
     */
    public function IsMVerifiedWay()
    {
        return array_key_exists('m_verified_way', $this->values);
    }


    /**
     * 设置代理人姓名
     * @param string $value
     **/
    public function SetAgentName($value)
    {
        $this->values['agent_name'] = $value;
    }

    /**
     * 获取代理人姓名
     * @return 值
     **/
    public function GetAgentName()
    {
        return $this->values['agent_name'];
    }

    /**
     * 判断代理人姓名是否存在
     * @return true 或 false
     **/
    public function IsAgentName()
    {
        return array_key_exists('agent_name', $this->values);
    }

    /**
     * 设置代理人身份证号码
     * @param string $value
     **/
    public function SetAgentID($value)
    {
        $this->values['agent_id'] = $value;
    }

    /**
     * 获取代理人身份证号码
     * @return 值
     **/
    public function GetAgentID()
    {
        return $this->values['agent_id'];
    }

    /**
     * 判断代理人身份证号码是否存在
     * @return true 或 false
     **/
    public function IsAgentIDSet()
    {
        return array_key_exists('agent_id', $this->values);
    }


    /**
     * 设置代理人身份证号码
     * @param string $value
     **/
    public function SetAgentMobile($value)
    {
        $this->values['agent_mobile'] = $value;
    }

    /**
     * 获取代理人身份证号码
     * @return 值
     **/
    public function GetAgentMobile()
    {
        return $this->values['agent_mobile'];
    }

    /**
     * 判断代理人身份证号码是否存在
     * @return true 或 false
     **/
    public function IsAgentMobileSet()
    {
        return array_key_exists('agent_mobile', $this->values);
    }


    /**
     * 设置代理人身份证号码
     * @param string $value
     **/
    public function SetAgentIdFrontPath($value)
    {
        $this->values['agent_id_front_path'] = $value;
    }

    /**
     * 获取代理人身份证号码
     * @return 值
     **/
    public function GetAgentIdFrontPath()
    {
        return $this->values['agent_id_front_path'];
    }

    /**
     * 判断代理人身份证号码是否存在
     * @return true 或 false
     **/
    public function IsAgentIdFrontPath()
    {
        return array_key_exists('agent_id_front_path', $this->values);
    }

    /**
     * 设置代理人姓名
     * @param string $value
     **/
    public function SetLegal_name($value)
    {
        $this->values['legal_name'] = $value;
    }

    /**
     * 获取法人姓名
     * @return 值
     **/
    public function GetLegal_name()
    {
        return $this->values['legal_name'];
    }

    /**
     * 判断法人姓名是否存在
     * @return true 或 false
     **/
    public function IsLegal_nameSet()
    {
        return array_key_exists('legal_name', $this->values);
    }

    /**
     * 设置代理人姓名
     * @param string $value
     **/
    public function SetlegaldIFrontPath($value)
    {
        $this->values['legal_id_front_path'] = $value;
    }

    /**
     * 获取法人姓名
     * @return 值
     **/
    public function GetlegaldIFrontPath()
    {
        return $this->values['legal_id_front_path'];
    }

    /**
     * 判断法人姓名是否存在
     * @return true 或 false
     **/
    public function IslegaldIFrontPath()
    {
        return array_key_exists('legal_id_front_path', $this->values);
    }

    /**
     * 设置银行名称
     * @param string $value
     **/
    public function SetBankName($value)
    {
        $this->values['bank_name'] = $value;
    }

    /**
     * 获取银行名称
     * @return
     **/
    public function GetBankName()
    {
        return $this->values['bank_name'];
    }

    /**
     * 判断银行名称是否存在
     * @return true 或 false
     **/
    public function IsBankNameSet()
    {
        return array_key_exists('bank_name', $this->values);
    }

    /**
     * 设置银行账号
     * @param string $value
     **/
    public function SetBankId($value)
    {
        $this->values['bank_id'] = $value;
    }

    /**
     * 获取银行账号
     * @return
     **/
    public function GetBankId()
    {
        return $this->values['bank_id'];
    }

    /**
     * 判断银行账号是否存在
     * @return true 或 false
     **/
    public function IsBankIdSet()
    {
        return array_key_exists('bank_id', $this->values);
    }

    /**
     * 设置开户支行名称
     * @param string $value
     **/
    public function SetSubbranchName($value)
    {
        $this->values['subbranch_name'] = $value;
    }

    /**
     * 获取开户支行名称
     * @return
     **/
    public function GetSubbranchName()
    {
        return $this->values['subbranch_name'];
    }

    /**
     * 判断开户支行名称是否存在
     * @return true 或 false
     **/
    public function IsSubbranchNameSet()
    {
        return array_key_exists('subbranch_name', $this->values);
    }

    /**
     * 设置企业名称
     * @param string $value
     **/
    public function SetCompanyName($value)
    {
        $this->values['company_name'] = $value;
    }

    /**
     * 获取企业名称
     * @return
     **/
    public function GetCompanyName()
    {
        return $this->values['company_name'];
    }

    /**
     * 判断企业名称是否存在
     * @return true 或 false
     **/
    public function IsCompanyNameSet()
    {
        return array_key_exists('company_name', $this->values);
    }

    /**
     * 设置统一社会信用代码
     * @param string $value
     **/
    public function SetCreditNo($value)
    {
        $this->values['credit_no'] = $value;
    }

    /**
     * 获取统一社会信用代码
     * @return
     **/
    public function GetCreditNo()
    {
        return $this->values['credit_no'];
    }

    /**
     * 判断统一社会信用代码是否存在
     * @return true 或 false
     **/
    public function IsCreditNoSet()
    {
        return array_key_exists('credit_no', $this->values);
    }

    /**
     * 设置统一社会信用代码证件照路径
     * @param string $value
     **/
    public function SetCreditImagePath($value)
    {
        $this->values['credit_image_path'] = $value;
    }

    /**
     * 获取统一社会信用代码证件照路径
     * @return
     **/
    public function GetCreditImagePath()
    {
        return $this->values['credit_image_path'];
    }

    /**
     * 判断统一社会信用代码证件照路径是否存在
     * @return true 或 false
     **/
    public function IsCreditImagePathSet()
    {
        return array_key_exists('credit_image_path', $this->values);
    }

    /**
     * 设置法人姓名
     * @param string $value
     **/
    public function SetLegalName($value)
    {
        $this->values['legal_name'] = $value;
    }

    /**
     * 获取法人姓名
     * @return
     **/
    public function GetLegalName()
    {
        return $this->values['legal_name'];
    }

    /**
     * 判断法人姓名是否存在
     * @return true 或 false
     **/
    public function IsLegalNameSet()
    {
        return array_key_exists('legal_name', $this->values);
    }

    /**
     * 设置法人证件号（身份证）
     * @param string $value
     **/
    public function SetLegalId($value)
    {
        $this->values['legal_id'] = $value;
    }

    /**
     * 获取法人证件号（身份证）
     * @return
     **/
    public function GetLegalId()
    {
        return $this->values['legal_id'];
    }

    /**
     * 判断法人证件号（身份证）是否存在
     * @return true 或 false
     **/
    public function IsLegalIdSet()
    {
        return array_key_exists('legal_id', $this->values);
    }

    /**
     * 设置法人手机号（仅支持国内运营商）
     * @param string $value
     **/
    public function SetlegalMobile($value)
    {
        $this->values['legal_mobile'] = $value;
    }

    /**
     * 获取法人手机号（仅支持国内运营商）
     * @return
     **/
    public function GetlegalMobile()
    {
        return $this->values['legal_mobile'];
    }

    /**
     * 判断法人手机号（仅支持国内运营商）是否存在
     * @return true 或 false
     **/
    public function IslegalMobileSet()
    {
        return array_key_exists('legal_mobile', $this->values);
    }

    /**
     * 设置姓名
     * @param string $value
     **/
    public function SetCustomerName($value)
    {
        $this->values['customer_name'] = $value;
    }

    /**
     * 获取姓名
     * @return
     **/
    public function GetCustomerName()
    {
        return $this->values['customer_name'];
    }

    /**
     * 判断姓名 是否存在
     * @return true 或 false
     **/
    public function IsCustomerName()
    {
        return array_key_exists('customer_name', $this->values);
    }

    /**
     * 设置证件类型 目前仅支持身份证-0
     * @param string $value
     **/
    public function SetCustomerIdentType($value)
    {
        $this->values['customer_ident_type'] = $value;
    }

    /**
     * 获取证件类型
     * @return
     **/
    public function GetCustomerIdentType()
    {
        return $this->values['customer_ident_type'];
    }

    /**
     * 判断证件类型 是否存在
     * @return true 或 false
     **/
    public function IsCustomerIdentType()
    {
        return array_key_exists('customer_ident_type', $this->values);
    }


    /**
     * 设置证件类型 目前仅支持身份证-0
     * @param string $value
     **/
    public function SetCustomerIdentNo($value)
    {
        $this->values['customer_ident_no'] = $value;
    }

    /**
     * 获取证件类型
     * @return
     **/
    public function GetCustomerIdentNo()
    {
        return $this->values['customer_ident_no'];
    }

    /**
     * 判断证件类型 是否存在
     * @return true 或 false
     **/
    public function IsCustomerIdentNo()
    {
        return array_key_exists('customer_ident_no', $this->values);
    }

    /**
     * 设置 手机号码
     * @param string $value
     **/
    public function SetMobile($value)
    {
        $this->values['mobile'] = $value;
    }

    /**
     * 获取 手机号码
     * @return
     **/
    public function GetMobile()
    {
        return $this->values['mobile'];
    }

    /**
     * 判断 手机号码 是否存在
     * @return true 或 false
     **/
    public function IsMobile()
    {
        return array_key_exists('mobile', $this->values);
    }

    /**
     * 设置 证件正面照下载地址
     * @param string $value
     **/
    public function SetIdentFrontPath($value)
    {
        $this->values['ident_front_path'] = $value;
    }

    /**
     * 获取 证件正面照下载地址
     * @return
     **/
    public function GetIdentFrontPath()
    {
        return $this->values['ident_front_path'];
    }

    /**
     * 判断 证件正面照下载地址  是否存在
     * @return true 或 false
     **/
    public function IsIdentFrontPath()
    {
        return array_key_exists('ident_front_path', $this->values);
    }




    /**
     * 设置 实名认证序列号
     * @param string $value
     **/
    public function SetVerifiedVSerialNo($value)
    {
        $this->values['verified_serialno'] = $value;
    }

    /**
     * 获取 实名认证序列号
     * @return
     **/
    public function GetVerifiedVSerialNo()
    {
        return $this->values['verified_serialno'];
    }

    /**
     * 判断 实名认证序列号  是否存在
     * @return true 或 false
     **/
    public function IsVerifiedSerialNo()
    {
        return array_key_exists('verified_serialno', $this->values);
    }

    /**
     * 设置 uuid 查询认证结果时返回
     * @param string $value
     **/
    public function SetUUID($value)
    {
        $this->values['uuid'] = $value;
    }

    /**
     * 获取 uuid 查询认证结果时返回
     * @return
     **/
    public function GetUUID()
    {
        return $this->values['uuid'];
    }

    /**
     * 判断uuid 查询认证结果时返回  是否存在
     * @return true 或 false
     **/
    public function IsUUID()
    {
        return array_key_exists('uuid', $this->values);
    }

    public function SetResultType($value)
    {
        $this->values['result_type'] = $value;
    }

    public function IsResultTypeSet()
    {
        return array_key_exists('result_type', $this->values);
    }

    public function SetCertFlag($value)
    {
        $this->values['cert_flag'] = $value;
    }

    public function IsCertFlagSet()
    {
        return array_key_exists('cert_flag', $this->values);
    }

    public function SetOption($value)
    {
        $this->values['option'] = $value;
    }

    public function IsOptionSet()
    {
        return array_key_exists('option', $this->values);
    }

    public function SetAuthorizationFile($value)
    {
        $this->values['authorization_file'] = $value;
    }

    public function IsAuthorizationFileSet()
    {
        return array_key_exists('authorization_file', $this->values);
    }

    public function SetLang($value)
    {
        $this->values['lang'] = $value;
    }

    public function IsLangSet()
    {
        return array_key_exists('lang', $this->values);
    }

    public function SetIdPhotoOptional($value)
    {
        $this->values['id_photo_optional'] = $value;
    }

    public function IsIdPhotoOptionalSet()
    {
        return array_key_exists('id_photo_optional', $this->values);
    }

    public function SetOrganizationType($value)
    {
        $this->values['organization_type'] = $value;
    }

    public function IsOrganizationTypeSet()
    {
        return array_key_exists('organization_type', $this->values);
    }

    public function SetEncryption($value)
    {
        $this->values['encryption'] = $value;
    }

    public function IsEncryptionSet()
    {
        return array_key_exists('encryption', $this->values);
    }

    public function SetBankCardNo($value)
    {
        $this->values['bank_card_no'] = $value;
    }

    public function IsBankCardNoSet()
    {
        return array_key_exists('bank_card_no', $this->values);
    }

    public function SetCertType($value)
    {
        $this->values['cert_type'] = $value;
    }

    public function IsCertTypeSet()
    {
        return array_key_exists('cert_type', $this->values);
    }

    public function SetIsMiniProgram($value)
    {
        $this->values['is_mini_program'] = $value;
    }

    public function IsIsMiniProgramSet()
    {
        return array_key_exists('is_mini_program', $this->values);
    }

    public function SetAreaCode($value)
    {
        $this->values['area_code'] = $value;
    }

    public function IsAreaCodeSet()
    {
        return array_key_exists('area_code', $this->values);
    }

    /**
     * 设置 指定管理员为"法人"身份下，允许的认证方式∶
    1.法人身份认证;
    2.对公打款认证;
    3.纸质材料认证;
     *
     * @param $value
     */
    public function SetLegal_allow_company_verify_way($value)
    {
        $this->values['legal_allow_company_verify_way'] = $value;
    }

    /**
     * 设置 指定管理员为"代理人"身份下，允许的认证方式∶
    1.法人授权认证;
    2.对公打款认证;
    3.纸质材料认证;
     *
     * @param $value
     */
    public function SetAgent_allow_company_verify_way($value)
    {
        $this->values['agent_allow_company_verify_way'] = $value;
    }

    /**
     * 设置 代理人证件正面照
     *
     * @param $value
     */
    public function SetAgent_id_front_img($value)
    {
        $this->values['agent_id_front_img'] = $value;
    }

    /**
     * 设置 法人证件正面照
     *
     * @param $value
     */
    public function SetLegal_id_front_img($value)
    {
        $this->values['legal_id_front_img'] = $value;
    }

    /**
     * 设置 银行所在省份
     *
     * @param $value
     */
    public function SetBank_province_name($value)
    {
        $this->values['bank_province_name'] = $value;
    }

    /**
     * 设置 银行所在市
     *
     * @param $value
     */
    public function SetBank_city_name($value)
    {
        $this->values['bank_city_name'] = $value;
    }

    /**
     * 设置 法人授权手机号
     *
     * @param $value
     */
    public function SetLegal_authorized_mobile($value)
    {
        $this->values['legal_authorized_mobile'] = $value;
    }

    /**
     * 设置 银行卡号
     *
     * @param $value
     */
    public function SetBank_card_no($value)
    {
        $this->values['bank_card_no'] = $value;
    }

    /**
     * 获取 银行卡号
     *
     * @return mixed
     */
    public function getBank_card_no(){
        return $this->values['bank_card_no'];
    }

    /**
     * 设置 证件正面照图片文件
    cert_type=0:身份证正面
    cert_type=1:护照带人像图片
    cert_type=B:港澳居民来往内地通行证照带人像图片
    cert_type=C:台湾居民来往大陆通行证照带人像图片
     *
     * @param $value
     */
    public function SetIdent_front_img($value)
    {
        $this->values['ident_front_img'] = $value;
    }

    /**
     * 设置 证件反面照图片文件
    cert_type=0:身份证反面
    cert_type=1:护照封图片
    cert_type=B:港澳居民来往内地通行证照封图图片
    cert_type=C:台湾居民来往大陆通行证照封图图片
     *
     * @param $value
     */
    public function SetIdent_back_img($value)
    {
        $this->values['ident_back_img'] = $value;
    }

    /**
     * 设置 证件反面照图片文件地址
    cert_type=0:身份证反面
    cert_type=1:护照封图片
    cert_type=B:港澳居民来往内地通行证照封图图片
    cert_type=C:台湾居民来往大陆通行证照封图图片
     *
     * @param $value
     */
    public function SetIdent_back_path($value)
    {
        $this->values['ident_back_path'] = $value;
    }

    /**
     * 获取证件反面照图片文件地址
     *
     * @return mixed
     */
    public function GetIdent_back_path(){
        return $this->values['ident_back_path'];
    }

    /**
     * 设置 海外用户是否支持银行卡认证：0-否，1-是，
    当接口中该参数传入有值时，以接口传入的配置为准，否则则取运营后台配置；
     *
     * @param $value
     */
    public function SetIs_allow_overseas_bank_card_auth($value)
    {
        $this->values['is_allow_overseas_bank_card_auth'] = $value;
    }

    /**
     * 设置 0：图片（默认图片）
    1：pdf (仅支持企业申请表模板)
     * @param string $value
     **/
    public function SetDoc_type($value)
    {
        $this->values['doc_type'] = $value;
    }

    /**
     * 判断 0：图片（默认图片）
    1：pdf (仅支持企业申请表模板) 是否存在
     * @param string $value
     **/
    public function IsDoc_typeSet()
    {
        return array_key_exists('doc_type', $this->values);
    }

    /**
     * 设置代理人证件反面照下载地址
     * @param string $value
     **/
    public function SetAgent_id_back_path($value)
    {
        $this->values['agent_id_back_path'] = $value;
    }

    public function GetAgent_id_back_path()
    {
        return $this->values['agent_id_back_path'];
    }

    /**
     * 设置法人证件反面照下载地址
     * @param string $value
     **/
    public function SetLegal_id_back_path($value)
    {
        $this->values['legal_id_back_path'] = $value;
    }

    public function GetLegal_id_back_path()
    {
        return $this->values['legal_id_back_path'];
    }

    /**
     * 设置代理人证件反面照
     * @param string $value
     **/
    public function SetAgent_id_back_img($value)
    {
        $this->values['agent_id_back_img'] = $value;
    }

    public function GetAgent_id_back_img()
    {
        return $this->values['agent_id_back_img'];
    }

    /**
     * 设置法人证件反面照
     * @param string $value
     **/
    public function SetLegal_id_back_img($value)
    {
        $this->values['legal_id_back_img'] = $value;
    }

    public function GetLegal_id_back_img()
    {
        return $this->values['legal_id_back_img'];
    }
}

/**
 * 授权自动签参数类
 *
 * Class FddAuthSign
 */
class FddAuthSign extends FddDataBase
{
    /**
     * 设置 签署时所传交易号
     * @param string $value
     **/
    public function SetTransaction_id($value)
    {
        $this->values['transaction_id'] = $value;
    }

    /**
     * 判断 签署时所传交易号 是否存在
     * @param string $value
     *
     * @return bool
     */
    public function IsTransaction_idSet()
    {
        return array_key_exists('transaction_id', $this->values);
    }

    /**
     * 设置授权类型：1:授权自动签（目前只能填1）
     *
     * @param $value
     */
    public function SetAuth_type($value)
    {
        $this->values['auth_type'] = $value;
    }

    /**
     * 判断授权类型是否存在
     *
     * @return bool
     */
    public function IsAuth_typeSet()
    {
        return array_key_exists('auth_type', $this->values);
    }

    /**
     * 设置 合同编号
     * @param string $value
     **/
    public function SetContract_id($value)
    {
        $this->values['contract_id'] = $value;
    }

    /**
     * 判断 合同编号 是否存在
     * @return true 或 false
     **/
    public function IsContract_idSet()
    {
        return array_key_exists('contract_id', $this->values);
    }

    /**
     * 设置 客户编号
     * @param string $value
     **/
    public function SetCustomerId($value)
    {
        $this->values['customer_id'] = $value;
    }

    /**
     * 判断 客户编号 是否存在
     * @return true 或 false
     **/
    public function IsCustomerId()
    {
        return array_key_exists('customer_id', $this->values);
    }

    /**
     * 设置 页面跳转url（签署结果同步通知）
     * @param string $value
     **/
    public function SetReturn_url($value)
    {
        $this->values['return_url'] = $value;
    }

    /**
     * 判断 页面跳转url（签署结果同步通知） 是否存在
     **/
    public function IsReturn_urlSet()
    {
        return array_key_exists('return_url', $this->values);
    }

    /**
     * 设置 签署结果异步步通知url
     * @param string $value
     **/
    public function SetNotify_url($value)
    {
        $this->values['notify_url'] = $value;
    }

    /**
     * 设置 签署结果异步步通知url
     * @param string $value
     *
     * @return bool
     */
    public function IsNotify_urlSet()
    {
        return array_key_exists('notify_url', $this->values);
    }
}

/**
 * 通用参数类
 *
 * Class GeneralParam
 */
class GeneralParam extends FddDataBase
{
    /**
     * 设置 签署时所传交易号
     * @param string $value
     **/
    public function SetTransaction_id($value)
    {
        $this->values['transaction_id'] = $value;
    }

    /**
     * 判断 签署时所传交易号 是否存在
     * @param string $value
     *
     * @return bool
     */
    public function IsTransaction_idSet()
    {
        return array_key_exists('transaction_id', $this->values);
    }

    /**
     * 设置 合同编号
     * @param string $value
     **/
    public function SetContract_id($value)
    {
        $this->values['contract_id'] = $value;
    }

    /**
     * 判断 合同编号 是否存在
     * @return true 或 false
     **/
    public function IsContract_idSet()
    {
        return array_key_exists('contract_id', $this->values);
    }

    /**
     * 设置 客户编号
     * @param string $value
     **/
    public function SetCustomerId($value)
    {
        $this->values['customer_id'] = $value;
    }

    /**
     * 判断 客户编号 是否存在
     * @return true 或 false
     **/
    public function IsCustomerId()
    {
        return array_key_exists('customer_id', $this->values);
    }

    /**
     * 设置 页面跳转url（签署结果同步通知）
     * @param string $value
     **/
    public function SetReturn_url($value)
    {
        $this->values['return_url'] = $value;
    }

    /**
     * 判断 页面跳转url（签署结果同步通知） 是否存在
     **/
    public function IsReturn_urlSet()
    {
        return array_key_exists('return_url', $this->values);
    }

    /**
     * 设置 签署结果异步步通知url
     * @param string $value
     **/
    public function SetNotify_url($value)
    {
        $this->values['notify_url'] = $value;
    }

    /**
     * 设置 签署结果异步步通知url
     * @param string $value
     *
     * @return bool
     */
    public function IsNotify_urlSet()
    {
        return array_key_exists('notify_url', $this->values);
    }

    /**
     * 设置 手机号码
     * @param string $value
     **/
    public function SetMobile($value)
    {
        $this->values['mobile'] = $value;
    }

    /**
     * 获取 手机号码
     *
     * @return mixed
     */
    public function getMobile(){
        return $this->values['mobile'];
    }

    /**
     * 判断 手机号码 是否存在
     * @return true 或 false
     **/
    public function IsMobile()
    {
        return array_key_exists('mobile', $this->values);
    }
}

/**
 * 短信参数
 *
 * Class SmsParam
 */
class SmsParam extends GeneralParam{
    /**
     * 设置 传入url
     * @param string $value
     **/
    public function SetSource_url($value)
    {
        $this->values['source_url'] = $value;
    }

    /**
     * 判断 传入url 是否存在
     **/
    public function IsSource_urlSet()
    {
        return array_key_exists('source_url', $this->values);
    }

    /**
     * 设置 有效时间
     * @param string $value
     **/
    public function SetExpire_time($value)
    {
        $this->values['expire_time'] = $value;
    }

    /**
     * 判断 有效时间 是否存在
     */
    public function IsExpire_timeSet(){
        return array_key_exists('expire_time', $this->values);
    }

    /**
     * 设置 发送短信类型
     * @param string $value
     **/
    public function SetMessage_type($value)
    {
        $this->values['message_type'] = $value;
    }

    /**
     * 判断 发送短信类型 是否存在
     *
     * @param $value
     */
    public function IsMessage_typeSet(){
        return array_key_exists('message_type', $this->values);
    }

    /**
     * 设置 自定义短信模板内容：message_type为2时候不能为空
     * @param string $value
     **/
    public function SetMessage_content($value)
    {
        $this->values['message_content'] = $value;
    }

    /**
     * 判断 自定义短信模板内容：message_type为2时候不能为空 是否存在
     *
     * @param $value
     */
    public function IsMessage_contentSet(){
        return array_key_exists('message_content', $this->values);
    }

    /**
     * 设置 短信模板:message_type为1时候不能为空
     * @param string $value
     **/
    public function SetSms_template_type($value)
    {
        $this->values['sms_template_type'] = $value;
    }

    /**
     * 判断 短信模板:message_type为1时候不能为空 是否存在
     *
     * @param $value
     */
    public function IsSms_template_typeSet(){
        return array_key_exists('sms_template_type', $this->values);
    }

    /**
     * 设置 加密方式
     *
     * @param $value
     */
    public function SetEncrypt_type($value){
        $this->values['encrypt_type'] = $value;
    }

    /**
     * 获取 加密方式
     *
     * @return mixed
     */
    public function GetEncrypt_type(){
        return $this->values['encrypt_type'];
    }

    /**
     * 判断 加密方式 是否存在
     *
     * @return bool
     */
    public function IsEncrypt_typeSet(){
        return array_key_exists('encrypt_type', $this->values);
    }

    /**
     * 设置 验证码
     *
     * @param $value
     */
    public function SetCode($value){
        $this->values['code'] = $value;
    }

    /**
     * 判断 验证码 是否存在
     *
     * @return bool
     */
    public function IsCodeSet(){
        return array_key_exists('code', $this->values);
    }
}

/**
 * 电子文件签署线上出证专业版接口
 *
 * Class ComplianceContractReport
 */
class ComplianceContractReport extends FddDataBase {
    /**
     * 设置 appId
     * @param string $value
     **/
    public function SetAppId($value)
    {
        $this->values["appId"] = $value;
    }

    /**
     * 设置 申请出证方账号
     *
     * @param $value
     */
    public function SetAccount($value){
        $this->values["account"] = $value;
    }

    /**
     * 设置消息摘要
     * @param string $value
     **/
    public function SetMsgDigest($value)
    {
        $this->values["msgDigest"] = $value;
    }

    /**
     * 设置 合同编号
     * @param string $value
     **/
    public function SetContractNum($value)
    {
        $this->values['contractNum'] = $value;
    }

    /**
     * 判断 合同编号 是否存在
     * @return true 或 false
     **/
    public function IsContractNumSet()
    {
        return array_key_exists('contractNum', $this->values);
    }

}

/**
 * 骑缝章签署参数
 *
 * Class DocusignAcrosspage
 */
class DocusignAcrosspage extends FddSignContract{
    /**
     * 设置 骑缝章id
     * @param string $value
     **/
    public function SetAcross_signature_id($value)
    {
        $this->values['across_signature_id'] = $value;
    }

    /**
     * 判断 骑缝章id 是否存在
     * @return true 或 false
     **/
    public function IsAcross_signature_idSet()
    {
        return array_key_exists('across_signature_id', $this->values);
    }
}
?>