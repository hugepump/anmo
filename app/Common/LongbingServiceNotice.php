<?php
/*
 * 服务通知
 *
 *************************************/  
namespace app\Common;
use app\admin\model\AppConfig;
use app\Common\model\LongbingCardFromId;
use app\Common\model\LongbingCardCount;
use app\Common\model\LongbingUserInfo;
use app\Common\model\LongbingClientInfo;
use app\Common\model\LongbingTabbar;
use app\Common\model\LongbingCardCommonModel;
use app\Common\LongbingCurl;
use app\Common\extend\wxWork\work;
use app\Common\service\LongbingUserInfoService;
use app\Common\service\LongbingUserService;
use longbingcore\tools\LongbingTime;
use longbingcore\wxcore\WxMsg;
use longbingcore\wxcore\WxSetting;

class LongbingServiceNotice extends LongbingCurl
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
    function __construct($uniacid = '7777'){
        $this->uniacid   = $uniacid;
        $this->config    = $this->getConfig($this->uniacid);
        $this->appid     = $this->getAppid();
        $this->appsecret = $this->getAppsecret();
    }
    
    //获取appid
    protected function getAppid()
    {
        if(isset($this->config['appid'])) return $this->config['appid'];
        return null;
    }
    
    //获取appsecret
    protected function getAppsecret()
    {
        if(isset($this->config['app_secret'])) return $this->config['app_secret'];
        return null;
    }
    
    //获取config信息    
    public function getConfig($uniacid)
    {
        
        //config key
        $key    = 'longbing_card_app_config_' . $uniacid;
        //获取config
        $config = getCache($key, $uniacid);
        
        //判断缓存是否存在
        if(!empty($config)) return $config;
        //不存在时查询数据库
        //生成操作模型
        $config_model = new AppConfig();
        //获取数据
        $config = $config_model -> getConfigByUniacid($uniacid);
        
        //判断数据是否存在
        if(!empty($config)) setCache($key ,$config ,3600,$uniacid);
        //返回数据
        return $config;
    }

    //检查信息是否存在
    function checkConfig()
    {
        $result = true;
        if(empty($this->uniacid) || empty($this->appid) || empty($this->appsecret)) $result = false;
        return $result;
    }
    
    /**
     * @Purpose: 获取用户的AccessToken
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    function lbSingleGetAccessToken ()
    {

        $setting = new WxSetting($this->uniacid);

        $token   = $setting->lbSingleGetAccessToken();

        return $token;


        //基础检查
        if(!$this->checkConfig()) return false;
        //appid
        $appid     = $this->appid;
        //生成md5文件
        $key = 'longbing_app_access_' . md5( $appid );
        //生成必要文件
        $access_token = null;
        $ac_time      = 0;
        //获取access-data缓存
        $access_data = getCache($key ,$this->uniacid);
        //判断缓存是否存在
        if(isset($access_data['access_token'])) $access_token = $access_data['access_token']; //access_token
        if(isset($access_data['access_token'])) $ac_time = $access_data['ac_time']; //access_token 有效期
        //判断缓存是否有效

        if ( empty($access_token) || empty($ac_time) || $ac_time < time())
        {
            $access_token = $this->lbSingleGetAccessTokenNew();
        }
        return $access_token;
    }
    //生成新的token
    function lbSingleGetAccessTokenNew ()
    {
        //基础检查
        if(!$this->checkConfig()) return false;
        //appid
        $appid     = $this->appid;
        //appsecret
        $appsecret = $this->appsecret;
        //生成key
        $key       = 'longbing_app_access_' . md5( $appid );
        $url      = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appid}&secret={$appsecret}";
        $data     = file_get_contents( $url );
        $data     = json_decode( $data, true );
        if ( !isset( $data[ 'access_token' ] ) )
        {
            return false;
        }
        //获取access_token
        $access_data['access_token'] = $access_token = $data[ 'access_token' ];
        $access_data['ac_time']      = time() + 7200;
        //设置缓存
        $result = setCache($key ,$access_data , 7200,$this->uniacid);
        return $access_token;
        return false;
    }
    
    //发送微信服务通知
    public function sendServiceNoticeToStaff($count_id ,$to_user = null ,$extra_data = [])
    {
        //获取count
        $count_model = new LongbingCardCount();
        $count       = $count_model->getCount(['id' => $count_id]);
        //判断count是否存在
        if(empty($count) || empty($count['user_id']) || empty($count['to_uid'])) return false;
        if($count['user_id'] == $count['to_uid']) $to_user = false;
        //判断是第几次
        $check_where = ['user_id' => $count[ 'user_id' ], 
                        'to_uid' => $count[ 'to_uid' ], 
                        'type' => $count[ 'type' ],
                        'uniacid' => $count[ 'uniacid' ], 
                        'target' => $count[ 'target' ],
                        'sign'    => $count[ 'sign' ] ];
        if ( ( $count[ 'type' ] == 18 || $count[ 'type' ] == 19 || $count[ 'type' ] == 20 ) && $count[ 'sign' ] == 'view' )
        {
            unset( $check_where[ 'target' ] );
        }
        $count_num = $count_model->getCountNum($check_where);
        if(empty($count_num)) $count_num = 1;
        //获取client
        $client_info_model = new LongbingClientInfo();
        $client_info       = $client_info_model->getClientInfo(['user_id' => $count['user_id'] ,'staff_id' => $count['to_uid']]);
        //
//        $send = true;
        if(!empty($client_info) && isset($client_info[ 'is_mask' ]) && !empty($client_info['is_make'])) return false;
        //获取发送数据
        $send_body = $this->lbSingleGetSendBody($count , 0 ,$extra_data);



        if(empty($send_body)) return false;
        
//        if ( $count[ 'sign' ] != 'order' && $count[ 'type' ] < 18 && !( $count[ 'sign' ] == 'praise' && $count[ 'type' ] == 8 ) )
//        {
//            $send_body = '第' . $count_num . '次' . $send_body;
//        }
        //获取用户信息
        $user  = longbingGetUser($count['user_id'] ,$this->uniacid);
        //员工信息
        $staff = longbingGetUser($count['to_uid'] ,$this->uniacid);
        if(!empty($to_user)) $this->sendServiceNoticeToUser($count ,$count_num ,$send_body ,$user);

//        dump($send_body,$user['nickName']);exit;
        
        switch($this->config['notice_switch'])
        {
            //发送公众号通知
            case 1:
                if(!isset($staff['openid'])) break;
                $this->sendWxOAServiceNotice($staff['openid'] ,$user['nickName'] ,$send_body ,$count['update_time']);
                break;
            //发送企业微信通知
            case 2:
                $this->sendWxEnterpriseServiceNotice($count['to_uid'] ,$user['nickName'] ,$send_body ,$count);
                break;
            //发送微信服务通知
            case 3:
                $from_id = $this->lbSingleGetFormId($count['to_uid']);
                if(!isset($staff['openid']) || empty($from_id)) break;
                $this->sendWxService($staff['openid'] ,$from_id ,$user['nickName'] ,$send_body ,$count['update_time']);
                break;
            case 4:
                $this->sendCompanyWxEnterpriseServiceNotice($count['to_uid'] ,$user['nickName'] ,$send_body);
                break;
            default:
                $from_id = $this->lbSingleGetFormId($count['to_uid']);
                if(!isset($staff['openid']) || empty($from_id)) break;
                $this->sendWxService($staff['openid'] ,$from_id ,$user['nickName'] ,$send_body ,$count['update_time']);
                break;
        }
    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-05-06 13:14
     * @功能说明:发送企业微信小程序通知
     */
    public function sendCompanyWxEnterpriseServiceNotice($to_uid,$nick_name,$send_body){

        $wx_msg = new WxMsg( $this->uniacid);
        //获取用户详情
        $user_info_model = new LongbingUserInfo();

        $user_info       = $user_info_model->getUser(['fans_id' => $to_uid]);
        //判断用户是否存在
        $touser = null;
        if(isset($user_info['ww_account'])) $touser = $user_info['ww_account'];
        //判断数据是否存在
        if(empty($touser)) return false;

        $content = [

            [
                'key'   => '访问用户',

                'value' => $nick_name

            ],
            [
                'key'   => '内容',

                'value' => $send_body

            ],
        ];

        $res = $wx_msg->WxMsg($touser,'访问通知','pages/admin/radar/radar',date('Y-m-d H:i:s',time()),$content);

        return $res;

    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-05-06 13:14
     * @功能说明:发送企业微信小程序通知
     */
    public function sendCompanyWxEnterpriseServiceNoticeIm($to_uid,$nick_name,$send_body){

        $wx_msg = new WxMsg( $this->uniacid);
        //获取用户详情
        $user_info_model = new LongbingUserInfo();

        $user_info       = $user_info_model->getUser(['fans_id' => $to_uid]);
        //判断用户是否存在
        $touser = null;

        if(isset($user_info['ww_account'])) $touser = $user_info['ww_account'];
        //判断数据是否存在
        if(empty($touser)) return false;

        $content = [

            [
                'key'   => '访问用户',

                'value' => $nick_name

            ],
            [
                'key'   => '内容',

                'value' => $send_body

            ],
        ];

        $res = $wx_msg->WxMsg($touser,'聊天通知','pages/admin/chat/chat',date('Y-m-d H:i:s',time()),$content);

        return $res;

    }


    //发送微信服务通知
    public function sendImMessageServiceNoticeToStaff( $staff_id  ,$message, $name = '')
    {

        //判断是否为员工,是员工才发送

        if(LongbingUserInfoService::isStraffByUserId($staff_id)) {


            $openid = LongbingUserService::getUserOpenId($staff_id);
            $name = empty($name) ?  LongbingUserInfoService::getNameByUserId($staff_id) : $name;
            $time = LongbingTime::getChinaNowTime();

            switch ($this->config['notice_switch']) {
                //发送公众号通知
                case 1:
                    $this->sendWxOAServiceNotice($openid, $name, $message, $time);
                    break;
                //发送企业微信通知
                case 2:
                    $count = [

                        'sign' => 'view',

                        'type' => 10000,
                    ];

                    $this->sendWxEnterpriseServiceNotice($staff_id, $name, $message, $count);
                    break;
                case 4:
                //企业微信小程序通知
                    $this->sendCompanyWxEnterpriseServiceNoticeIm($staff_id ,$name ,$message);
                    break;

            }

        }
    }
    
    
    //向用户发送服务通知
    public function sendServiceNoticeToUser($count ,$count_num ,$send_body ,$user)
    {
        //获取uniacid
        $uniacid = $this->uniacid;
        //判断是第几次
        if(empty($count_num)) $count_num = 1;
        //判断send_body是否存在
        if(empty($send_body)) return false;
        //判断count是否存在
        if(empty($count) || !isset($count['user_id'])) return false;
        //获取open_id
        if(!isset($user['openid']) || !isset($user['nickName'])) return false;
        $openid = $user['openid'];
        //获取时间
        if(!isset($count[ 'create_time' ]) || empty($count[ 'create_time' ])) $count[ 'create_time' ] = time();
        //获取FromId
        $from_id = $this->lbSingleGetFormId($count['user_id']);
        //判断from_id是否存在
        if(empty($from_id)) return false;
        //发送数据
        return $this->sendWxService($openid ,$from_id ,$user['nickName'] ,$send_body ,$count[ 'create_time' ]);
    }
    
    //发送聊天服务通知
    public function sendMessageServiceNotice($message)
    {
        //获取uniacid
        $uniacid = $this->uniacid;
        //判断send_body是否存在
        if(empty($message) || !isset($message['content'])) return false;
        //获取用户信息
        $user = longbingGetUser($message['user_id'] ,$this->uniacid);
//        var_dump(1111111);die;
        //判断User是否存在
        if(empty($user) || !isset($user['nickName'])) return false;
        //获取open_id
        $to_user = longbingGetUser($message['to_user_id'] ,$this->uniacid);
        if(!isset($to_user['openid']) || !isset($to_user['id'])) return false;
        $openid = $to_user['openid'];
        //获取时间
        if(!isset($message[ 'create_time' ]) || empty($message[ 'create_time' ])) $message[ 'create_time' ] = time();
        //获取FromId
        $from_id = $this->lbSingleGetFormId($to_user['id']);
        //判断from_id是否存在
        if(empty($from_id)) return false;
        //跳转地址
        $page = "pages/user/home";
        if(isset($to_user['is_staff']) && !empty($to_user['is_staff']))
        {
            $page = "pages/admin/chat/chat";
        }
        //发送数据
        return $this->sendWxService($openid ,$from_id ,$user['nickName'] ,$message['content'] ,$message[ 'create_time' ] ,$page);
    }
    
    
    
    /*
     * 发送微信服务通知
     * openid    微信openid
     * from      fromid
     * nickName  昵称
     * sendbody  数据
     * date      消息产生的时间
     */
    public function sendWxService($openid ,$form ,$nickName ,$send_body ,$time = null ,$page_data = null)
    {
        //获取access_token
        $access_token = $this->lbSingleGetAccessToken();
        //判断access_token是否存在
        if(empty($access_token)) return false;
        //判断mini_template_id是否存在    
        if(!isset($this->config[ 'mini_template_id' ])) return false;
        //生成请求url
        $url  = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token={$access_token}";
//        $page = "longbing_card/pages/index/index?to_uid={$count_info['to_uid']}&currentTabBar=toCard";
        $page     = "pages/admin/radar/radar";
        if(!empty($page_data)) $page = $page_data;
        //获取时间
        if(empty($time) || !is_int($time)) $time = time();
        $date = date( 'Y-m-d H:i', $time );
        $postData = [ 'touser' => $openid, 
                      'template_id' => $this->config[ 'mini_template_id' ], 
                      'page' => $page, 
                      'form_id' => $form,
                      'data'   => [ 'keyword1' => [ 'value' => $nickName ], 'keyword2' => [ 'value' => $send_body ],
                                    'keyword3' => [ 'value' => $date ], ], ];
        //封装数据
        $postData = json_encode( $postData, JSON_UNESCAPED_UNICODE );
        //请求数据
        $response = $this->curlPost( $url, $postData );
        return $response;
    }

    /**
     * User: chenniang(龙兵科技)
     * Date: 2019-12-25 11:52
     * @param $data
     * @return bool|string
     * descrption:添加模版 返回模版消息id
     */
    public function addTmpl($data){
        $access_token = $this->lbSingleGetAccessToken();

        $url  =  "https://api.weixin.qq.com/wxaapi/newtmpl/addtemplate?access_token={$access_token}";
//        $data = [
//          'tid'      => '1754',
//          'kidList'  => [4,2,1,3],
//          'sceneDesc'=>'test'
//        ];
        $data =  json_encode( $data, JSON_UNESCAPED_UNICODE );

        $res  =  $this->curlPost( $url,$data );
        return $res;

    }






    //发送公众号通知 (WeChat Official Account)
    public function sendWxOAServiceNotice($openid ,$nickName ,$send_body ,$time = null)
    {
        //微信公众号appid
        $wx_appid = null;
        //wx_tplid
        $wx_tplid = null;
        if(isset($this->config['wx_appid'])) $wx_appid = $this->config['wx_appid'];
        if(isset($this->config['wx_tplid'])) $wx_tplid = $this->config['wx_tplid'];
        //判断关键数据是否存在
        if(empty($wx_appid) || empty($wx_tplid)) return false;
        //获取access_token
        $access_token = $this->lbSingleGetAccessToken();
        if(empty($access_token)) return false;
        //请求地址
        $url = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/uniform_send?access_token={$access_token}";
        //获取时间
        if(empty($time) || !is_int($time)) $time = time();
        $date = date( 'Y-m-d H:i', $time);
        $page = "pages/admin/radar/radar";
        
        $data = [ 'touser'          => $openid,
                  'mp_template_msg' => [ 'appid'       => $wx_appid, 
                  "url" => "http://weixin.qq.com/download",
                  'template_id' => $wx_tplid,
                  'miniprogram' => [ 'appid' => $this->appid, 'pagepath' => $page, ],
                  'data'        => array( 'first' => array( 'value' => '' ,'color' => '#c27ba0', ),
                                          'keyword1' => array( 'value' => $nickName ,'color' => '#93c47d', ),
                                          'keyword2' => array( 'value' => $send_body ,'color' => '#0000ff', ),
                                          'remark' => array( 'value' => $date ,'color' => '#45818e', ), ) ], ];
        //数据转化
        $data = json_encode( $data, JSON_UNESCAPED_UNICODE );
        //发送数据
        $res =  $this->curlPost( $url, $data );
    }
    
    //发送企业微信通知
    public function sendWxEnterpriseServiceNotice($to_uid ,$nick_name ,$send_body ,$count_info)
    {
        //获取appid
        $app_id    = null;
        //获取appsecret
        $appsecret = null;
        //获取agentid
        $agentid   = null;
        //获取数据
        if(isset($this->config['corpid'])) $app_id        = $this->config['corpid'];
        if(isset($this->config['corpsecret'])) $appsecret = $this->config['corpsecret'];
        if(isset($this->config['agentid'])) $agentid      = $this->config['agentid'];
        //判断数据是否存在
        if(empty($app_id) || empty($appsecret) || empty($agentid)) return false;
        //获取用户详情
        $user_info_model = new LongbingUserInfo();

        $user_info       = $user_info_model->getUser(['fans_id' => $to_uid]);

        //判断用户是否存在
        $touser = null;


        if(isset($user_info['ww_account'])) $touser = $user_info['ww_account'];
        //判断数据是否存在
        if(empty($touser)) return false;
        //封装数据
        $data = array( 'touser' => $touser, 'msgtype' => 'text', 'agentid' => $agentid,
                           'text'   => array( 'content' => $nick_name . ',' . $send_body, ), );



        //获取count
        if ( $count_info[ 'sign' ] == 'view' && $count_info[ 'type' ] )
        {

            if(in_array($count_info[ 'type' ],[662,663,664,665,666])){

                $table_name = 'longbing_card_shortvideo';

            }elseif(in_array($count_info[ 'type' ],[1,19,21,22,23,24])){

                $table_name = 'longbing_card_goods';
            }

            if(!empty($table_name)){

                $info = $this->longbingGetRow( $table_name, [ 'id' => $count_info[ 'target' ] ] );
            }

            if(!empty($info['cover'])){

                $info  = transImagesOne($info,['cover']);

                $cover = $info['cover'];
            }

            $data = array(
                'touser' => $touser,

                'msgtype' => 'news',

                'agentid' => $agentid,

                'news'    => array(
                    'articles' => array(
                       array(
                           'title'       => $nick_name,

                           'description' => $send_body,

//                           'url'         => $cover,
//
//                           'picurl'      => $cover,
                       ),
                    ),
                ),
            );

        }

        if(!empty($cover)){

            $data['news']['articles'][0]['url']    = $cover;

            $data['news']['articles'][0]['picurl'] = $cover;

        }
//        include_once APP_PATH . '/Common/extend/wxWork/work.weixin.class.php';
        $work = new work( $app_id, $appsecret );
        $result = $work->send( $data );


    }
    
    public function lbSingleAutoDelFromId()
    {
        $from_id_model = new LongbingCardFromId();
        //获取数据
        $from_id_model->autoDelFromId();
    }
    
    //获取fromid
    public function lbSingleGetFormId ( $to_uid )
    {
        // 七天前开始的的时间戳
        $beginTime = mktime( 0, 0, 0, date( 'm' ), date( 'd' ) - 6, date( 'Y' ) );
        //自动删除
        $this->lbSingleAutoDelFromId();
        //生成fromId模型
        $from_id_model = new LongbingCardFromId();
        //获取数据
        $from_id  = $from_id_model->getFromId([ 'user_id' => $to_uid ]);
        if ( empty( $from_id ) )
        {
            return false;
        }
        if ( $from_id[ 'create_time' ] < $beginTime )
        {
            $from_id_model->delFromId(['id' => $from_id['id']]);
            return $this->lbSingleGetFormId ( $to_uid );
        }
        else
        {
            $from_id_model->delFromId(['id' => $from_id['id']]);
            return $from_id[ 'formId' ];
        }
        return false;
    }
    
    public function longbingGetRow($table_name ,$filter)
    {
        if(empty($table_name) || empty($filter) || !is_array($filter)) return false; 
        $common_model = new LongbingCardCommonModel();
        return $common_model->getRows($table_name ,$filter);
    }
    
    
    /**
     * @Purpose: 给员工发送内容
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    function lbSingleGetSendBody ( $count_info = array(), $count_id = 0, $extra_data = [] )
    {
        //获取uniacid
        $uniacid = $this->uniacid;
        //获取数据
        $tabbar_model = new LongbingTabbar();
        $tabbar = $tabbar_model->getTabbar(['uniacid' => $uniacid]);
        if ( empty( $count_info ) && $count_id == 0 )
        {
            return false;
        }
        if ( empty( $count_info ) && $count_id != 0 )
        {
            $count_model = new LongbingCardCount();
            $count_info = $count_model->getCount(['id' => $count_id]);
            if ( !$count_info )
            {
                return false;
            }
        }
        //修改雷达文案
        $datas = lbHandelRadarMsg([$count_info]);

        $body = '';

        if(!empty($datas[0]['radar_arr'])){

            foreach ($datas[0]['radar_arr'] as $value){

                $body.= $value['title'];

            }

        }

        return  $body;

        if ( $count_info[ 'sign' ] == 'praise' )
        {
            switch ( $count_info[ 'type' ] )
            {
                case 2:
                    $body = '浏览你的名片';
                    break;
                case 4:
                    $body = '分享你的名片';
                    break;
    
                case 5:
                    $body = '资讯你的房产';
                    if ( $count_info[ 'target' ] )
                    {
                        $info = $this->longbingGetRow( 'longbing_card_house', [ 'id' => $count_info[ 'target' ] ] );
//                        $info = $this->longbingGetHouse($count_info[ 'target' ]);
                        if ( $info )
                        {
                            $body .= $info[ 'title' ];
                        }
                    }
    
                    break;
                case 6:
                    $body = '收藏你的房产';
                    if ( $count_info[ 'target' ] )
                    {
                        $info = $this->longbingGetRow( 'longbing_card_house', [ 'id' => $count_info[ 'target' ] ] );
//                        $info = $this->longbingGetHouse($count_info[ 'target' ]);
                        if ( $info )
                        {
                            $body .= $info[ 'title' ];
                        }
                    }
                    break;
                case 7:
                    $body = '拨打你的房产';
                    if ( $count_info[ 'target' ] )
                    {
                        $info = $this->longbingGetRow( 'longbing_card_house', [ 'id' => $count_info[ 'target' ] ] );
//                        $info = $this->longbingGetHouse($count_info[ 'target' ]);
                        if ( $info )
                        {
                            $body .= $info[ 'title' ];
                        }
                    }
                    break;
                case 8:
                    $time = time();
                    if(!empty($count_info['update_time'])) $time = $count_info['update_time'];
                    $time = date('Y/m/d-h:i' ,$time);
                    $body = '于' . $time . '预约看房 ';
                    if ( $count_info[ 'target' ] )
                    {
                        $info = $this->longbingGetRow( 'longbing_card_house', [ 'id' => $count_info[ 'target' ] ] );
//                        $info = $this->longbingGetHouse($count_info[ 'target' ]);
                        if ( $info )
                        {
                            $body .= $info[ 'title' ];
                        }
                    }
                    break;
            }
        }
    
        if ( $count_info[ 'sign' ] == 'view' )
        {
            switch ( $count_info[ 'type' ] )
            {
                case 1:
                    $body = '浏览' . $tabbar[ 'menu2_name' ] . '列表';
                    break;
                case 2:
                    $body = '浏览' . $tabbar[ 'menu2_name' ] . '详情';
                    if ( $count_info[ 'target' ] )
                    {
                        $info = $this->longbingGetRow( 'longbing_card_goods', [ 'id' => $count_info[ 'target' ] ] );
//                        $info = $this->longbingGetGoods($count_info[ 'target' ]);
                        $body .= ':' . $info[ 'name' ];
                    }
                    break;
                case 3:
                    $body = '浏览' . $tabbar[ 'menu3_name' ] . '列表';
                    break;
                case 4:
                    $body = '点赞' . $tabbar[ 'menu3_name' ];
                    break;
                case 5:
                    $body = $tabbar[ 'menu3_name' ] . '留言';
                    break;
                case 6:
                    $body = '浏览公司' . $tabbar[ 'menu4_name' ];
                    break;
                case 7:
                    $body = '浏览' . $tabbar[ 'menu3_name' ] . '详情';
                    if ( $count_info[ 'target' ] )
                    {
                        $info = $this->longbingGetRow( 'longbing_card_timeline', [ 'id' => $count_info[ 'target' ] ] );
                        $body .= ':' . $info[ 'title' ];
                    }
                    break;
                case 8:
                    $body = '浏览' . $tabbar[ 'menu3_name' ] . '视频详情';
                    if ( $count_info[ 'target' ] )
                    {
                        $info = $this->longbingGetRow( 'longbing_card_timeline', [ 'id' => $count_info[ 'target' ] ] );
                        $body .= ':' . $info[ 'title' ];
                    }
                    break;
                case 9:
                    $body = '浏览' . $tabbar[ 'menu3_name' ] . '外链详情';
                    if ( $count_info[ 'target' ] )
                    {
                        $info = $this->longbingGetRow( 'longbing_card_timeline', [ 'id' => $count_info[ 'target' ] ] );
                        $body .= ':' . $info[ 'title' ];
                    }
                    break;
                case 10:
                    $body = '浏览' . $tabbar[ 'menu3_name' ] . '跳转小程序';
                    if ( $count_info[ 'target' ] )
                    {
                        $info = $this->longbingGetRow( 'longbing_card_timeline', [ 'id' => $count_info[ 'target' ] ] );
                        $body .= ':' . $info[ 'title' ];
                    }
                    break;
                case 12:
                    $body = '浏览' . $tabbar[ 'menu3_name' ] . '获客文章';
                    if ( $count_info[ 'target' ] )
                    {
                        $info = $this->longbingGetRow( 'longbing_card_timeline', [ 'id' => $count_info[ 'target' ] ] );
                        $body .= ':' . $info[ 'title' ];
                    }
                    break;
                case 15:
                    $body = '浏览预约全部列表';
                    break;
                case 16:
                    $body = '浏览预约分类';
                    if ( $count_info[ 'target' ] )
                    {
                        $info = $this->longbingGetRow( 'lb_appoint_classify', [ 'id' => $count_info[ 'target' ] ] );
                        $body .= ':' . $info[ 'title' ] . '列表';
                    }
                    break;
                case 17:
                    $body = '浏览预约项目';
                    if ( $count_info[ 'target' ] )
                    {
                        $info = $this->longbingGetRow( 'lb_appoint_project', [ 'id' => $count_info[ 'target' ] ] );
                        $body .= ':' . $info[ 'title' ];
                    }
                    break;
                case 18:
                    $body = '在官网留言';
                    if ( $count_info[ 'target' ] )
                    {
                        $info = $this->longbingGetRow( 'longbing_card_form', [ 'id' => $count_info[ 'target' ] ] );
                        $body .= ', 姓名: ' . $info[ 'name' ] . ', 手机号: ' . $info[ 'phone' ] . ', 内容: ' . $info[ 'content' ];
                    }
                    break;
                case 19:
                    $body = '订单已发货';
                    if ( $count_info[ 'target' ] )
                    {
                        $order_info  = $this->longbingGetRow( 'longbing_card_shop_order', [ 'id' => $count_info[ 'target' ] ] );
                        $target_info = $this->longbingGetRow( 'longbing_card_shop_order_item', [ 'order_id' => $order_info[ 'id' ] ] );
    
                        if ( $order_info )
                        {
                            $body .= '，商品：' . $target_info[ 'name' ] . '-' . $target_info[ 'content' ] . '...';
                        }
                    }
                    break;
                case 20:
                    $body = '订单已自提';
                    if ( $count_info[ 'target' ] )
                    {
                        $order_info  = $this->longbingGetRow( 'longbing_card_shop_order', [ 'id' => $count_info[ 'target' ] ] );
                        $target_info = $this->longbingGetRow( 'longbing_card_shop_order_item', [ 'order_id' => $order_info[ 'id' ] ] );
    
                        if ( $order_info )
                        {
                            $body .= '，商品：' . $target_info[ 'name' ] . '-' . $target_info[ 'content' ] . '...';
                        }
                    }
                    break;
                case 21:
                    $body = '退款申请已提交, 请等待管理员审核';
                    if ( $count_info[ 'target' ] )
                    {
                        $order_info  = $this->longbingGetRow( 'longbing_card_shop_order', [ 'id' => $count_info[ 'target' ] ] );
                        $target_info = $this->longbingGetRow( 'longbing_card_shop_order_item', [ 'order_id' => $order_info[ 'id' ] ] );
    
                        if ( $order_info )
                        {
                            $body .= '，商品：' . $target_info[ 'name' ] . '-' . $target_info[ 'content' ] . '...';
                        }
                    }
                    break;
                case 22:
                    $body = '退款申请已取消';
                    if ( $count_info[ 'target' ] )
                    {
                        $order_info  = $this->longbingGetRow( 'longbing_card_shop_order', [ 'id' => $count_info[ 'target' ] ] );
                        $target_info = $this->longbingGetRow( 'longbing_card_shop_order_item', [ 'order_id' => $order_info[ 'id' ] ] );
    
                        if ( $order_info )
                        {
                            $body .= '，商品：' . $target_info[ 'name' ] . '-' . $target_info[ 'content' ] . '...';
                        }
                    }
                    break;
                case 23:
                    $body = '退款申请已被拒绝';
                    if ( $count_info[ 'target' ] )
                    {
                        $order_info  = $this->longbingGetRow( 'longbing_card_shop_order', [ 'id' => $count_info[ 'target' ] ] );
                        $target_info = $this->longbingGetRow( 'longbing_card_shop_order_item', [ 'order_id' => $order_info[ 'id' ] ] );
    
                        if ( $order_info )
                        {
                            $body .= '，商品：' . $target_info[ 'name' ] . '-' . $target_info[ 'content' ] . '...';
                        }
                    }
                    break;
                case 24:
                    $body = '退款已成功, 请注意查收';
                    if ( $count_info[ 'target' ] )
                    {
                        $order_info  = $this->longbingGetRow( 'longbing_card_shop_order', [ 'id' => $count_info[ 'target' ] ] );
                        $target_info = $this->longbingGetRow( 'longbing_card_shop_order_item', [ 'order_id' => $order_info[ 'id' ] ] );
    
                        if ( $order_info )
                        {
                            $body .= '，商品：' . $target_info[ 'name' ] . '-' . $target_info[ 'content' ] . '...';
                        }
                    }
                    break;
                case 25:
                    $body = '浏览' . $tabbar[ 'menu_activity_name' ] . '全部列表';
                    break;
                case 26:
                    $body = '浏览' . $tabbar[ 'menu_activity_name' ] . '分类: ';
                    if ( $count_info[ 'target' ] )
                    {
                        $info = $this->longbingGetRow( 'lb_activity_classify', [ 'id' => $count_info[ 'target' ] ] );
    
                        if ( $info )
                        {
                            $body .= $info[ 'title' ] . '列表';
                        }
                    }
                    break;
                case 27:
                    $body = '浏览' . $tabbar[ 'menu_activity_name' ] . ', ';
                    if ( $count_info[ 'target' ] )
                    {
                        $info = $this->longbingGetRow( 'lb_activity_activity', [ 'id' => $count_info[ 'target' ] ] );
    
                        if ( $info )
                        {
                            $body .= $info[ 'title' ] . '详情';
                        }
                    }
                    break;
                case 28:
                    $body = '报名参加' . $tabbar[ 'menu_activity_name' ] . ': ';
                    if ( $count_info[ 'target' ] )
                    {
                        $info = $this->longbingGetRow( 'lb_activity_activity', [ 'id' => $count_info[ 'target' ] ] );
    
                        if ( $info )
                        {
                            $body .= $info[ 'title' ];
                        }
                    }
                    break;
    
                case 29:
                    $body = '查看房产首页';
                    break;
    
                case 30:
                    $body = '查看房产';
                    if ( $count_info[ 'target' ] )
                    {
                        $info = $this->longbingGetRow( 'longbing_card_house', [ 'id' => $count_info[ 'target' ] ] );
                        if ( $info )
                        {
                            $body .= $info[ 'title' ];
                        }
                    }
                    break;
            }
        }
    
        if ( $count_info[ 'sign' ] == 'copy' )
        {
            switch ( $count_info[ 'type' ] )
            {
                case 1:
                    $body = '同步到通讯录';
                    break;
                case 2:
                    $body = '拨打手机号';
                    break;
                case 3:
                    $body = '拨打座机号';
                    break;
                case 4:
                    $body = '复制微信';
                    break;
                case 5:
                    $body = '复制邮箱';
                    break;
                case 6:
                    $body = '复制公司名';
                    break;
                case 7:
                    $body = '查看定位';
                    break;
                case 8:
                    $body = '咨询产品';
                    break;
                case 9:
                    $body = '播放语音';
                    break;
                case 10:
                    $body = '保存名片海报';
                    break;
                case 11:
                    $body = '拨打400热线';
                    break;
            }
        }
        if ( $count_info[ 'sign' ] == 'order' )
        {
            switch ( $count_info[ 'type' ] )
            {
                case 1:
                    $body = '购买商品';
                    if ( $count_info[ 'target' ] )
                    {
                        $order_info  = $this->longbingGetRow( 'longbing_card_shop_order', [ 'id' => $count_info[ 'target' ] ] );
                        $target_info = $this->longbingGetRow( 'longbing_card_shop_order_item', [ 'order_id' => $order_info[ 'id' ] ] );
    
                        if ( $order_info )
                        {
                            //                    $body .= '，订单号：' . $order_info[ 'transaction_id' ];
    
                            $body .= '，商品：' . $target_info[ 'name' ] . '-' . $target_info[ 'content' ];
                        }
                    }
                    break;
                case 2:
                    $body = '参与拼团';
                    if ( $count_info[ 'target' ] )
                    {
                        $order_info  = $this->longbingGetRow( 'longbing_card_shop_order', [ 'id' => $count_info[ 'target' ] ] );
                        $target_info = $this->longbingGetRow( 'longbing_card_shop_order_item', [ 'order_id' => $order_info[ 'id' ] ] );
    
                        if ( $order_info )
                        {
                            //                    $body .= '，订单号：' . $order_info[ 'transaction_id' ];
    
                            $body .= '，商品：' . $target_info[ 'name' ] . '-' . $target_info[ 'content' ];
                        }
                    }
                    break;
                case 3:
                    $body = '预约订单';
                    if ( $count_info[ 'target' ] )
                    {
                        $order_info = $this->longbingGetRow( 'lb_appoint_record', [ 'id' => $count_info[ 'target' ] ] );
                        $info       = $this->longbingGetRow( 'lb_appoint_project', [ 'id' => $order_info[ 'project_id' ] ] );
                        $body       .= ', 预约开始时间: ' . date( 'Y-m-d H:i:s', $order_info[ 'start_time' ] );
                        if ( $order_info[ 'remark' ] )
                        {
                            $body .= ', 预约备注信息: ' . $order_info[ 'remark' ];
                        }
    
                        $body .= ', 预约项目: ' . $info[ 'title' ];
    
                    }
                    break;
            }
    
        }
    
        //  扫码支付
        if ( $count_info[ 'sign' ] == 'qr' )
        {
            if ( $count_info[ 'target' ] )
            {
                $order_info = $this->longbingGetRow( 'lb_pay_qr_record', [ 'id' => $count_info[ 'target' ] ] );
                $body       = '扫码支付: ￥' . $order_info[ 'money' ];
    
            }
        }
        if ( $body )
        {
            return $body;
        }
        return false;
    }
    
    
}
