<?php
/*
 * 服务通知
 *
 *************************************/
namespace longbingcore\wxcore;
use app\admin\model\AppConfig;
use app\massage\info\PermissionMassage;
use app\massage\model\ShortCodeConfig;
use app\sendmsg\model\SendConfig;
use longbingcore\tools\LongbingFile;
use think\facade\Db;
use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;
use AlibabaCloud\Green\Green;


class WxSetting
{
    //accesstoken
    protected $access_token = null;
    //appid
    protected $appid = null;
    //appsecret
    protected $appsecret = null;
    //uniacid
    protected $uniacid = '7777';
    //配置信息
    protected $config = [];
    //初始化
    function __construct($uniacid = '666'){
        $this->uniacid   = $uniacid;

        $this->config    = $this->getConfig($this->uniacid);

        $this->appid     = $this->getAppid();

        $this->appsecret = $this->getAppsecret();
    }

    /**
     * 功能说明 获取appid
     *
     * @return mixed|null
     * @author chenniang(龙兵科技)
     * @DataTime: 2019-12-25 17:36
     */
    protected function getAppid()
    {
        if(isset($this->config['appid'])) return $this->config['appid'];
        return null;
    }

    /**
     * 功能说明 获取appsecret
     *
     * @return mixed|null
     * @author chenniang(龙兵科技)
     * @DataTime: 2019-12-25 17:36
     */
    protected function getAppsecret()
    {
        if(isset($this->config['appsecret'])) return $this->config['appsecret'];
        return null;
    }

    /**
     * 功能说明 获取配置信息
     *
     * @param $uniacid
     * @return array|bool|mixed|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author chenniang(龙兵科技)
     * @DataTime: 2019-12-25 17:35
     */
    public function getConfig($uniacid)
    {

        $config = longbingGetAppConfig($uniacid);
        //返回数据
        return $config;
    }

    //检查信息是否存在
    public function checkConfig()
    {
        $result = true;
        if(empty($this->uniacid) || empty($this->appid) || empty($this->appsecret)) $result = false;
        return $result;
    }

    /**
     * 功能说明 获取token
     *
     * @return bool|null
     * @author chenniang(龙兵科技)
     * @DataTime: 2019-12-25 17:35
     */
    public function lbSingleGetAccessToken ()
    {
        $key = "longbing_card_access_token";

        $value = getCache( $key,$this->uniacid );


        if ( $value !=false )
        {
            return $value;
        }

        $uniacid = $this->uniacid;
        //基础检查
        if(!$this->checkConfig()){


            return false;
        }
        //appid
        $appid     = $this->appid;
        //appsecret
        $appsecret = $this->appsecret;

        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret={$appsecret}";


        $accessToken = file_get_contents( $url );

       // dump($accessToken);exit;

        if ( strstr( $accessToken, 'errcode' ) )
        {
            return 0;
        }

        $accessToken = json_decode( $accessToken, true );
        $accessToken = $accessToken[ 'access_token' ];

        setCache( $key, $accessToken, 7000, $uniacid );

        return $accessToken;
    }



    /**
     * User: chenniang(龙兵科技)
     * Date: 2019-12-25 11:52
     * @param $data
     * @return bool|string
     * descrption:添加模版 返回模版消息id
     */
    public function addTmpl($data){
        //获取token
        $access_token = $this->lbSingleGetAccessToken();
//        dump($access_token);exit;
        //添加模版的小程序地址
        $url  =  "https://api.weixin.qq.com/wxaapi/newtmpl/addtemplate?access_token={$access_token}";
        //请求
        $data =  json_encode( $data, JSON_UNESCAPED_UNICODE );
        //返回模版消息id 是个array
        $res  =  $this->curlPost( $url,$data );
        return $res;
    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2019-12-26 11:39
     * @功能说明:获取所有模版消息列表
     */
    public function getAllTpmlList(){
        //获取token
        $access_token = $this->lbSingleGetAccessToken();
        //获取小程序所有 模版消息的url
        $url = "https://api.weixin.qq.com/wxaapi/newtmpl/gettemplate?access_token={$access_token}";
        //请求
        $res  =  $this->curlPost($url,NULL,'GET');
        return $res;
    }


    /**
     * @param $tpml_id
     * @功能说明: 删除模版消息
     * @author chenniang(龙兵科技)
     * @DataTime: 2019-12-26 14:08
     */
    public function deleteTmpl($tpml_id){
        //获取token
        $access_token = $this->lbSingleGetAccessToken();
        //获取小程序所有 模版消息的url
        $url = "https://api.weixin.qq.com/wxaapi/newtmpl/deltemplate?access_token={$access_token}";
        //请求参数
        $data['priTmplId'] = $tpml_id;
        //请求
        $data =  json_encode( $data, JSON_UNESCAPED_UNICODE );
        $res  =  $this->curlPost($url,$data);
        return $res;
    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2019-12-26 18:54
     * @功能说明:发送模版消息
     */
    public function sendTmpl($data){
        //获取token
        $access_token = $this->lbSingleGetAccessToken();
        //获取小程序所有 模版消息的url
        $url  = "https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token={$access_token}";
        //请求
        $data =  json_encode( $data, JSON_UNESCAPED_UNICODE );
//        dump($data);exit;
        $res  =  $this->curlPost($url,$data);
        return $res;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2019-12-26 18:54
     * @功能说明:获取当前账号下的所有模版消息
     */
    public function getUserTmpl(){
        //获取token
        $access_token = $this->lbSingleGetAccessToken();
        //获取小程序所有 模版消息的url
        $url  = "https://api.weixin.qq.com/wxaapi/newtmpl/gettemplate?access_token={$access_token}";
        //请求
        $res  =  $this->curlPost($url,[],'GET');
        return $res;
    }


    /**
     * @param array $data
     * @功能说明:企业微信消息
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-04-27 18:33
     */
    public function sendCompanyMsg(array $data)
    {
        if (is_array($data)) {
            $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        }

        $accessTokenWW = $this->companyMsgToken();

        $url = "https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token={$accessTokenWW}";

        $res = $this->curlPost($url, $data);


        return $res;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-04-28 13:40
     * @功能说明:获取企业消息token
     */
    public function companyMsgToken(){

        $key = "longbing_card_access_token_qy_cn";

        $value = getCache($key,$this->uniacid);

        if ( $value !== false )
        {
            return $value;
        }

        $send_model  = new SendConfig();
        //配置信息
        $config      = $send_model->configInfo(['uniacid'=>$this->uniacid]);

        if (!empty($config['yq_corpid'])&& !empty($config['yq_corpsecret'] ))
        {
            $key    = $config['yq_corpid'];

            $secret = $config['yq_corpsecret'];

            $url    = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=$key&corpsecret=$secret";

            $accessToken = file_get_contents( $url );

            $accessToken = json_decode( $accessToken, true );

            if($accessToken['errcode'] != 0){

                echo json_encode( [ 'code' => 402, 'error' => '获取token失败' ] );

                exit;
            }
            $accessToken = $accessToken[ 'access_token' ];

            setCache( $key, $accessToken, 7200, $this->uniacid );
        }

        return !empty($accessToken)?$accessToken:'';
    }
    /**
     * 功能说明 post 请求
     *
     * @param $url
     * @param $post
     * @param $method
     * @param int $header
     * @return bool|string
     * @author chenniang(龙兵科技)
     * @DataTime: 2019-12-25 18:40
     */
    public function curlPost($url,$post=NULL,$post_type='POST'){
        $result = $this->crulGetData($url,$post,$post_type);
        return $result;
    }


    /**
     * 功能说明 post 请求
     *
     * @param $url
     * @param $post
     * @param $method
     * @param int $header
     * @return bool|string
     * @author chenniang(龙兵科技)
     * @DataTime: 2019-12-25 18:40
     */
    public function crulGetData($url, $post, $method, $header=1){
        $this->_errno = NULL;
        $this->_error = NULL;
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if($post != NULL)
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION,true);
        curl_setopt($ch, CURLOPT_AUTOREFERER,true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);

        if($header)
            curl_setopt($ch, CURLOPT_HEADER, false);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Content-Type: application/json; charset=utf-8",
            )
        );
        $result = curl_exec($ch);
        return $result;
    }




    /**
     * @param $accesstoken  token
     * @param $uid 我给二维码传的参数
     * @return string
     */
    public function qrCode($uid){

        $accesstoken = $this->getGzhToken();
        $u = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=" . $accesstoken;
        $param = array();
        $param['action_name'] = "QR_LIMIT_STR_SCENE";
        $param['action_info'] = array('scene' => array('scene_str'=>$uid));

        $param = json_encode($param);
        // 返回二维码的ticket和二维码图片解析地址
        $res = $this->curlPost($u, $param,'post');
        $res = json_decode($res,true);
        // 通过ticket换取二维码
        $qrcode = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=" . $res['ticket'];

        $path = MATER_UPLOAD_PATH.date('Y-m-d',time()).'/img';

        if(!is_dir($path)){

            mkdir($path,0777,true);
        }

        $imageName = "/25220_".date("His",time())."_".rand(1111,9999).'.jpg';

        file_put_contents($path.$imageName, file_get_contents($qrcode));

        $qrcode = str_replace(FILE_UPLOAD_PATH,HTTPS_PATH,$path.$imageName);

        return $qrcode;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-07-25 14:01
     * @功能说明:批量生产微信二维码
     */
    public function batchChannelWechatQr($num){

        $accesstoken = $this->getGzhToken();

        $qrcode = [];

        for ($i=0;$i<$num;$i++){

            $code = longbingorderCode().$i;

            $u = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=" . $accesstoken;
            $param = array();
            $param['action_name'] = "QR_LIMIT_STR_SCENE";
            $param['action_info'] = array('scene' => array('scene_str'=>$code.'_chaqr'));

            $param = json_encode($param);
            // 返回二维码的ticket和二维码图片解析地址
            $res = $this->curlPost($u, $param,'post');
            $res = json_decode($res,true);

            if(!empty($res['ticket'])){

                $qrcode[$i]['code'] = $code;
                // 通过ticket换取二维码
                $qrcode[$i]['qr'] = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=" . $res['ticket'];
            }
        }

        return $qrcode;
    }


    /**
     * @param $img_path
     * @功能说明:图片检测
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-03-27 11:44
     */
    function imgSecCheck($img_path){

        //token
        $access_token = $this->lbSingleGetAccessToken();
        //地址
        $url ='https://api.weixin.qq.com/wxa/img_sec_check?access_token='.$access_token;
        //请求包
        $post_data = [
            'media'=>new \CURLFile($img_path)
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');

        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 100);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);
        $output = curl_exec($ch);
        curl_close($ch);

        return $output;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-02-26 17:19
     * @功能说明:
     */
    public function getGzhToken($update=0){

        $uniacid = $this->uniacid;

        $config  = longbingGetAppConfig($this->uniacid);

        $appid   = $config['web_app_id'];

        $gzh_secret  = $config['web_app_secret'];

        $key         = 'articleToken-';

        $value = getCache($key, $uniacid);

        if ($value&&$update==0)
        {
            return $value;
        }

        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appid}&secret={$gzh_secret}";

        $js_token = file_get_contents($url);

        $js_token = json_decode($js_token, true);

        if (isset($js_token['access_token']))
        {
            $js_token = $js_token['access_token'];

            setCache($key, $js_token, 1200, $uniacid);

            return $js_token;
        }

        $str = '请求ac错误' . isset($js_token['errmsg']) ? $js_token['errmsg'] : '';
        echo $str;
        die;
    }


    /**
     * @param $appid
     * @param $gzh_secret
     * @param $js_token
     * @param $uniacid
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-02-26 17:21
     */
    public function getArticleJsapiTicket( $js_token)
    {


        $ticket_url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token={$js_token}&type=jsapi";

        $ticket_arr = @file_get_contents($ticket_url);

        $ticket_arr = @json_decode($ticket_arr, true);

        if (isset($ticket_arr['errcode']) && $ticket_arr['errcode'] != 0)
        {
            return false;
        }
        $jsapi_ticket = $ticket_arr['ticket'];

        return $jsapi_ticket;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-07-14 22:23
     * @功能说明:
     */
    public function getWebTicket(){

        $config  = longbingGetAppConfig($this->uniacid);

        $appid   = $config['web_app_id'];

        $key = 'ticket'.$appid;

        $ticket = getCacheAll($key,$this->uniacid);

        if($ticket){

            return $ticket;
        }

        $token  = $this->getGzhToken(1);

        $ticket = $this->getArticleJsapiTicket($token);

        if(!empty($ticket)){

            setCacheAll($key,$ticket,3600,$this->uniacid);

        }else{

            clearCache($this->uniacid);

        }

        return $ticket;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-03-14 18:56
     * @功能说明:php二维码 返回base64
     */
    public function phpQrCode($data){

        require_once  EXTEND_PATH.'phpqrcode/phpqrcode.php';

        $errorCorrectionLevel = 'L';  //容错级别

        $matrixPointSize = 10;
        //生成图片大小
        ob_start();

        \QRcode::png($data,false,$errorCorrectionLevel,$matrixPointSize,2,false,0xFFFFFF,0x000000);

        $imgstr = base64_encode(ob_get_contents());

        ob_end_clean();

        $imgstr = 'data:image/png;base64,'.$imgstr;

        return $imgstr;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-08-12 10:58
     * @功能说明:初始化h5所需文件
     */
    public function setH5Info(){

        $p = new PermissionMassage($this->uniacid);

        $value = $p->getSaasValue();

        if(is_numeric($value)&&$value>0){

            $config = $this->getConfig($this->uniacid);

            $app_id = $config['web_app_id'];

            $map = $config['map_secret'];

            $url  = 'https://'.$_SERVER['HTTP_HOST'].'/index.php';

            $fileName = 'siteinfo.json';

            $tempStr =<<<str
                let siteInfo = {
                    "uniacid": "666",
                    "multiid": "0",
                    "version": "3.0",
                    "gzh_appid": "$app_id",
                    "siteroot": "$url",
                    "qqMapKey":"$map"
                    
                }

str;

            LongbingFile::createDir(H5_PATH) ;

            defined('COACH_PATH') or define('COACH_PATH', $_SERVER['DOCUMENT_ROOT'].'/coach/h5'. DS); //文件上传目录

            LongbingFile::createDir(COACH_PATH) ;

            file_put_contents(H5_PATH . $fileName, $tempStr);

            file_put_contents(COACH_PATH . $fileName, $tempStr);

        }

        return true;


    }


    /**
     * @param $content
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-03 15:57
     */
    public function checkContent($content){

        $access_token  = $this->getGzhToken(1);

        $url = 'https://api.weixin.qq.com/wxa/msg_sec_check'.'?access_token='.$access_token;

        $data = ['content'=>$content];

        $res = lbCurlPost($url,urldecode(json_encode($data,JSON_UNESCAPED_UNICODE)));

        return $res;

    }

    /**
     * @param $content
     * @param $access_token
     * @功能说明: 违禁词检测
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-01-02 17:07
     */
    public function checkKeyWordsv2($content)
    {
        $access_token = $this->lbSingleGetAccessToken();

        if(empty($access_token)){

            return false;
        }

        $url = "https://api.weixin.qq.com/wxa/msg_sec_check?access_token={$access_token}";
        $tmp = [
            'url' => $url,
            'data' => [
                'content' => urlencode($content)
            ],
        ];
        $rest = $this->curlPost($tmp['url'], urldecode(json_encode($tmp['data'])));

        $rest = json_decode($rest, true);
        $res= $rest['errcode']==0?true:false;
        return $res;
    }


    /**
     * @param $content
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-06 10:12
     */
    public function checkKeyWords($content)
    {

        $file_path =ROOT_PATH. "public/pub_sms_banned_words.txt"; // 文件的绝对路径
        // 检测文件是否存在
        if (file_exists($file_path)) {

            $fp = fopen($file_path, "r"); // 以只读的方式打开

            while (!feof($fp)) {

                $str_line = base64_decode(fgets($fp));

                $str_line = trim($str_line);

                if (false !== strstr($content, $str_line)) {

                    return false;
                }
            }
            fclose($fp); // 关闭文件
        }

        return true;
    }


    /**
     * @param $uniacid
     * @功能说明:获取模版萧
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-07-07 19:10
     */
    public function getTmplList($uniacid){

        $access_token = longbingGetAccessToken($uniacid);

        $url = 'https://api.weixin.qq.com/cgi-bin/template/get_all_private_template?access_token='.$access_token;

        $a = file_get_contents($url);

        dump($a);exit;
        $rest = lbCurlPost($tmp['url'], $tmp['data']);


    }






}
