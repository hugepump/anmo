<?php

namespace app\index\controller;

use app\baidu\model\AdminSetting;

use app\baiying\model\BaiYingPhoneRecord;
use app\farm\model\User;
use app\fxq\model\FxqContractFile;
use app\massage\model\ChannelQr;
use app\massage\model\ChannelScanQr;
use app\massage\model\Coach;
use app\massage\model\CoachAccount;
use app\massage\model\Config;
use app\massage\model\CouponAtv;
use app\massage\model\DistributionList;
use app\massage\model\MassageConfig;
use app\massage\model\QrBind;
use app\massage\model\ResellerRecommendCash;
use app\massage\model\SendMsgConfig;
use app\massage\model\ShortCodeConfig;
use app\partner\model\PartnerOrder;
use app\redbag\model\Invitation;
use app\restaurant\model\ClassDate;
use Exception;
use longbingcore\tools\LongbingDefault;
use longbingcore\wxcore\Fxq;
use longbingcore\wxcore\WxSetting;
use think\App;
use think\facade\Db;
use think\facade\View;
use think\Response;
use app\baidu\model\AdminActive;

class Login
{
    //  小程序登陆每个用户产生的唯一表示
    protected $code = '';

    protected $uniacid;
    protected $request;
    protected $_param;
    protected $_input;

    function __construct(App $app)
    {
        global $_GPC, $_W;

        $this->request = $app->request;
        //获取param
        $this->_param = $this->request->param();

        $this->_input = json_decode($this->request->getInput(), true);
        //获取uniacid
        if (!isset($this->_param['i']) || !$this->_param['i']) {

            return $this->error('need uniacid',400);
        }

        $this->uniacid = $this->_param['i'];
    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-17 10:06
     * @功能说明:小程序用户登陆接口
     */
    function index()
    {
        $input = $this->_input;

        if (!isset($input['code']) || !$input['code']) {

            return $this->error(['code' => 400, 'error' => 'need code']);

        }

        $cap_id = !empty($input['cap_id'])?$input['cap_id']:0;

        $code   = $input['code'];
        //  是否是微擎
        $config = longbingGetAppConfig($this->uniacid,true);

        if(!empty($input['is_coach'])){

            $appid = getConfigSetting($this->uniacid,'coach_appid');

            $appsecret = getConfigSetting($this->uniacid,'coach_appsecret');

        }else{

            $appid     = $config['appid'];

            $appsecret = $config['appsecret'];

        }
        //  从微信获取openid等
        $url  = "https://api.weixin.qq.com/sns/jscode2session?appid={$appid}&secret={$appsecret}&js_code={$code}&grant_type=authorization_code";

        $arrContextOptions = array(

            "ssl"=>array(

                "verify_peer"     => false,

                "verify_peer_name"=> false,
            ),
        );

        $info = file_get_contents($url ,false, stream_context_create($arrContextOptions));

        $info = @json_decode($info, true);
        //  微信端返回错误
        if (isset($info['errcode'])) {

            return $this->error($info['errcode'] . ', errmsg: ' . $info['errmsg']);

        }
        if (!isset($info['session_key'])) {

            return $this->error('session_key not exists','402');
        }

        if(empty($info['openid'])){

            return $this->error('openid not exists');
        }

        $user_model = new \app\massage\model\User();

        $unionid = !empty($info['unionid'])?$info['unionid']:'';

        if(empty($unionid)){

            return $this->error('请将小程序绑定到开发平台');

        }

        if(!empty($unionid)){

            $dis = [

                'unionid' => $unionid,

                'uniacid' => $this->uniacid,

                'status'  => 1

            ];

        }else{
            //没有移动应用
            $dis = [

                'openid'  => $info['openid'],

                'uniacid' => $this->uniacid,

                'status'  => 1

            ];
        }

        $user_info = $user_model->dataInfo($dis);

//        if(!empty($unionid)&&!empty($user_info)){
//
//            $web = $user_model->dataInfo(['openid'=>$info['openid']]);
//
//            if(!empty($web['id'])&&$web['id']!=$user_info['id']){
//
//                $user_model->dataUpdate(['id'=>$user_info['id']],['unionid'=>$unionid.'--11']);
//
//                $dis = [
//
//                    'openid'  => $info['openid'],
//
//                    'uniacid' => $this->uniacid
//
//                ];
//
//                $user_info = $user_model->dataInfo($dis);
//            }
//        }

        if(empty($user_info)){

            $dis = [

                'wechat_openid' => $info['openid'],

                'status'        => 1
            ];

            $user_info = $user_model->dataInfo($dis);
        }

        $insert = [

            'uniacid'     => $this->uniacid,

            'openid'      => $info['openid'],

            'wechat_openid'=> $info['openid'],

            'cap_id'      => $cap_id,

            'session_key' => $info['session_key'],

            'unionid'     => $unionid,

            'last_login_type' => 0,

        ];

        if(empty($user_info)){
            //获取用户所在地
            $insert = $user_model->getRegisterLocation($input,$insert,$this->uniacid);

            $bind_model = new QrBind();

            $del_user = \app\massage\model\User::getDelUser($insert);

            if(empty($del_user)){

                if(!empty($input['pid'])){

                    $insert['pid'] = $input['pid'];
                }else{

                    $insert['pid'] = $bind_model->getPid($insert['openid'],$insert['unionid']);
                }

                if(!empty($insert['pid'])){
                    //开启了分销审核
                    if(getFxStatus($this->uniacid)==1){

                        $distribu_model = new DistributionList();

                        $dis = [

                            'user_id' => $insert['pid'],

                            'status'  => 2
                        ];

                        $distribu_user = $distribu_model->dataInfo($dis);

                        if(!empty($distribu_user)){

                            $insert['admin_id'] = $distribu_user['admin_id'];
                        }else{

                            $insert['pid'] = 0;
                        }
                    }
                }
            }else{

                $insert['admin_id'] = $del_user['admin_id'];

                $insert['pid'] = $del_user['pid'];

                $insert['source_type'] = $del_user['source_type'];

                $insert['is_qr'] = $del_user['is_qr'];

                $insert['del_user_id'] = $del_user['id'];

            }

            $user_model->dataAdd($insert);

            $user_id = $user_model->getLastInsID();

            $user_info = $user_model->dataInfo($insert);

            $qr_model = new ChannelQr();

            $qr_model->locationLogin($user_id);
            //分销员推荐费
            if(!empty($distribu_user)){

                $insert['id'] = $user_id;

                $reseller_recommend_model = new ResellerRecommendCash();

                $reseller_recommend_model->addRecommendCash($insert,$distribu_user);
            }

            $register = 1;
        }else{

            $register = 0;

            $user_model->dataUpdate(['id'=>$user_info['id']],$insert);
        }

        $key = 'longbing_user_autograph_wechat' . $user_info['openid'];

        $key = md5($key);

        setCache($key, $user_info, 86400*3,999999999999);

        $arr = [

            'data'      => $user_info,

            'autograph' => $key,

            'is_register'=> $register
        ];

        $coach_model = new Coach();

        $cap_dis[] = ['user_id','=',$user_info['id']];

        $cap_dis[] = ['status','in',[1,2,3,4]];
        //查看是否是团长
        $cap_info = $coach_model->where($cap_dis)->order('status')->find();

        $cap_info = !empty($cap_info)?$cap_info->toArray():[];
        //-1表示未申请团长，1申请中，2已通过，3取消,4拒绝
        $arr['coach_status'] = !empty($cap_info)?$cap_info['status']:-1;

        return $this->success($arr);

    }

    //返回成功
    public function success($data, $code = 200)
    {
        $result['data'] = $data;
        $result['code'] = $code;
        $result['sign'] = null;

        return $this->response($result, 'json', $code);
    }

    //返回错误数据
    public function error($msg, $code = 400,$msg_code = 400)
    {
        $result['error'] = $msg;
        $result['code']  = $msg_code;
        return $this->response($result, 'json', $code);
    }

    //response
    protected function response($data, $type = 'json', $code = 200)
    {
        return Response::create($data, $type)->code($code);
    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-17 10:06
     * @功能说明:微信登陆
     */
    function appLogin()
    {
        $input = $this->_input;

        $uniacid = $this->uniacid;

        $input = $input['data'];

        $cap_id = !empty($input['cap_id'])?$input['cap_id']:0;

        $user_model = new \app\massage\model\User();

        $dis = [

            'unionid' => $input['unionId'],

            'status'  => 1

        ];

        $user_info = $user_model->dataInfo($dis);

        if(empty($user_info)&&!empty($input['openId'])){

            $dis = [

                'app_openid' => $input['openId'],

                'status'  => 1
            ];

            $user_info = $user_model->dataInfo($dis);
        }

        $insert = [

            'uniacid'  => $uniacid,

            'nickName' => !empty($user_info['nickName'])?$user_info['nickName']:$input['nickName'],

            'avatarUrl'=> !empty($user_info['avatarUrl'])?$user_info['avatarUrl']:$input['avatarUrl'],

            'unionid'  => $input['unionId'],

            'gender'   => $input['gender'],

            'city'     => $input['city'],

            'province' => $input['province'],

            'country'  => $input['country'],

            'openid'   => $input['openId'],

            'app_openid'  => $input['openId'],

            //'create_time' => time(),

            'cap_id'      => $cap_id,

            'last_login_type'  => 1,

        ];

        if(empty($user_info)){
            //获取用户所在地
            $insert = $user_model->getRegisterLocation($input,$insert,$this->uniacid);

            $bind_model = new QrBind();

            $del_user = \app\massage\model\User::getDelUser($insert);

            if(empty($del_user)){

                if(!empty($input['pid'])){

                    $insert['pid'] = $input['pid'];
                }else{

                    $insert['pid'] = $bind_model->getPid($insert['openid'],$insert['unionid']);
                }

                if(!empty($insert['pid'])){
                    //开启了分销审核
                    if(getFxStatus($this->uniacid)==1){

                        $distribu_model = new DistributionList();

                        $dis = [

                            'user_id' => $insert['pid'],

                            'status'  => 2
                        ];

                        $distribu_user = $distribu_model->dataInfo($dis);

                        if(!empty($distribu_user)){

                            $insert['admin_id'] = $distribu_user['admin_id'];
                        }else{

                            $insert['pid'] = 0;
                        }
                    }
                }
            }else{

                $insert['admin_id'] = $del_user['admin_id'];

                $insert['pid'] = $del_user['pid'];

                $insert['source_type'] = $del_user['source_type'];

                $insert['is_qr'] = $del_user['is_qr'];

                $insert['del_user_id'] = $del_user['id'];
            }

            $user_model->dataAdd($insert);

            $user_id  = $user_model->getLastInsID();

            $qr_model = new ChannelQr();

            $qr_model->locationLogin($user_id);
            //分销员推荐费
            if(!empty($distribu_user)){

                $insert['id'] = $user_id;

                $reseller_recommend_model = new ResellerRecommendCash();

                $reseller_recommend_model->addRecommendCash($insert,$distribu_user);
            }

        }else{

            $user_id = $user_info['id'];

            $user_model->dataUpdate(['id'=>$user_id],$insert);

        }

        $user_info = $user_model->dataInfo(['id'=>$user_id]);

        $key = 'longbing_user_autograph_app' . $user_info['unionid'];

        $key = md5($key);

        setCache($key, $user_info, 86400*3,999999999999);

        $arr = [

            'data'      => $user_info,

            'autograph' => $key
        ];
        $coach_model = new Coach();

        $cap_dis[] = ['user_id','=',$user_info['id']];

        $cap_dis[] = ['status','in',[1,2,3,4]];
        //查看是否是团长
        $cap_info = $coach_model->where($cap_dis)->order('status')->find();

        $cap_info = !empty($cap_info)?$cap_info->toArray():[];
        //-1表示未申请团长，1申请中，2已通过，3取消,4拒绝
        $arr['coach_status'] = !empty($cap_info)?$cap_info['status']:-1;

        return $this->success($arr);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-10-27 16:32
     * @功能说明:获取code
     */
    public function getCode($appId){

        $url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'];

        $url = str_replace('&state=STATE','',$url);

        $url  = str_replace('&from=singlemessage','',$url);

        $url2 = urlencode($url);

        //  $redirectUrl = urlencode('https://' . $_SERVER['HTTP_HOST'] . '/index.php?i=666&t=0&v=3.0&from=wxapp&c=entry&a=wxapp&do=api&core=core2&m=longbing_massages_city&s=index/webLogin');

        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$appId}&redirect_uri={$url2}&response_type=code&scope=snsapi_userinfo&state=STATE&connect_redirect=1#wechat_redirect";

        header('Location:' . $url);

        exit;

    }





    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-02-24 10:28
     * @功能说明:h5公众号登陆
     */
    public function webLogin(){

        $input = $this->_input;

        $config = longbingGetAppConfig($this->uniacid,true);

        $appid  = $config['web_app_id'];

        if (!isset($input['code']) || !$input['code']) {

            return $this->error('need code');
        }

        $uniacid = $this->_param['i'];

        $cap_id = !empty($input['cap_id'])?$input['cap_id']:0;

        $code   = $input['code'];
        //  是否是微擎
        $appsecret = $config['web_app_secret'];

        $url  = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$appid}&secret={$appsecret}&code={$code}&grant_type=authorization_code";

        $info = file_get_contents($url);

        $info = @json_decode($info, true);

        if (isset($info['errcode'])) {

            return $this->error($info['errcode'] . ', errmsg: ' . $info['errmsg'],40163);

        }

        $token = $info['access_token'];

        $openid = $info['openid'];
        //拿到token后就可以获取用户基本信息了
        $url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$token.'&openid='.$openid;

        $json = file_get_contents($url);//获取微信用户基本信息

        $arr = json_decode($json,true);

        if (isset($arr['errcode'])) {

            return $this->error($arr['errcode'] . ', errmsg: ' . $arr['errmsg'],40163);

        }

        if(empty($info['openid'])){

            return $this->error('openid not exists');

        }

        $user_model = new \app\massage\model\User();

        $unionid = !empty($info['unionid'])?$info['unionid']:'';

        if(empty($unionid)){

            // return $this->error('请将公众号绑定到开发平台');

        }

        if(!empty($unionid)){

            $dis = [

                'unionid' => $unionid,

                'uniacid' => $this->uniacid,

                'status'  => 1

            ];

        }else{
            //没有移动应用
            $dis = [

                'openid'  => $info['openid'],

                'uniacid' => $this->uniacid,

                'status'  => 1

            ];
        }

        $user_info = $user_model->dataInfo($dis);
        //这个user_info 可能是app的账号确又不是web的账号
//        if(!empty($unionid)&&!empty($user_info)){
//
//            $web = $user_model->dataInfo(['web_openid'=>$info['openid'],'status'=>1]);
//
//            if(!empty($web['id'])&&$web['id']!=$user_info['id']){
//
//                $user_model->dataUpdate(['id'=>$user_info['id']],['status'=>-1,'openid'=>$user_info['openid'].time()]);
//
//                $user_info = $user_model->dataInfo($dis);
//            }
//        }

        if(empty($user_info)){

            $dis = [

                'web_openid' => $info['openid'],

                'status'     => 1
            ];

            $user_info = $user_model->dataInfo($dis);
        }

        $insert = [

            'uniacid'  => $uniacid,

            'nickName' => !empty($user_info['nickName'])?$user_info['nickName']:$arr['nickname'],

            'avatarUrl'=> !empty($user_info['avatarUrl'])?$user_info['avatarUrl']:$arr['headimgurl'],

            'unionid'  => $unionid,

            'gender'   => $arr['sex'],

            'country'  => $arr['country'],

            'openid'   => $arr['openid'],

            'web_openid'=> $arr['openid'],

            'cap_id'    => $cap_id,

            'last_login_type' => 2,

            'ip' => getIP()
        ];

        if(empty($user_info)){
            //获取用户所在地
            $insert = $user_model->getRegisterLocation($input,$insert,$this->uniacid);

            $bind_model = new QrBind();

            $del_user = \app\massage\model\User::getDelUser($insert);

            if(empty($del_user)){

                if(!empty($input['pid'])){

                    $insert['pid'] = $input['pid'];
                }else{

                    $insert['pid'] = $bind_model->getPid($insert['openid'],$insert['unionid']);
                }

                if(!empty($insert['pid'])){
                    //开启了分销审核
                    if(getFxStatus($this->uniacid)==1){

                        $distribu_model = new DistributionList();

                        $dis = [

                            'user_id' => $insert['pid'],

                            'status'  => 2
                        ];

                        $distribu_user = $distribu_model->dataInfo($dis);

                        if(!empty($distribu_user)){

                            $insert['admin_id'] = $distribu_user['admin_id'];
                        }else{

                            $insert['pid'] = 0;
                        }
                    }
                }

                $scan_model = new ChannelScanQr();
                //是否是扫码注册
                $insert = $scan_model->getQrRegister($insert['web_openid'],$insert);
            }else{

                $insert['admin_id'] = $del_user['admin_id'];

                $insert['pid'] = $del_user['pid'];

                $insert['source_type'] = $del_user['source_type'];

                $insert['is_qr'] = $del_user['is_qr'];

                $insert['del_user_id'] = $del_user['id'];
            }

            $user_model->dataAdd($insert);

            $user_id = $user_model->getLastInsID();

            if(!empty($input['coupon_atv_id'])){

                $coupon_atv_model = new CouponAtv();

                $coupon_atv_model->invUser($user_id,$input['coupon_atv_id']);
            }

            $qr_model = new ChannelQr();

            $qr_model->locationLogin($user_id);

            $register = 1;
            //分销员推荐费
            if(!empty($distribu_user)){

                $insert['id'] = $user_id;

                $reseller_recommend_model = new ResellerRecommendCash();

                $reseller_recommend_model->addRecommendCash($insert,$distribu_user);
            }
        }else{

            $register = 0;

            $user_id = $user_info['id'];

            $user_model->dataUpdate(['id'=>$user_id],$insert);
        }

        $user_info = $user_model->dataInfo(['id'=>$user_id]);

        $key = 'longbing_user_autograph_' . $user_info['openid'];

        $key = md5($key);

        setCache($key, $user_info, 86400*3,999999999999);

        $arr = [

            'data'      => $user_info,

            'autograph' => $key,

            'is_register'=> $register
        ];
        $coach_model = new Coach();

        $cap_dis[] = ['user_id','=',$user_info['id']];

        $cap_dis[] = ['status','in',[1,2,3,4]];
        //查看是否是团长
        $cap_info = $coach_model->where($cap_dis)->field('status')->order('status')->find();

        $cap_info = !empty($cap_info)?$cap_info->toArray():[];
        //-1表示未申请团长，1申请中，2已通过，3取消,4拒绝
        $arr['coach_status'] = !empty($cap_info)?$cap_info['status']:-1;

        return $this->success($arr);
    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-01-09 13:47
     * @功能说明:获取隐私协议
     */
    public function getLoginProtocol(){

        $input  = $this->_param;

        $config = longbingGetAppConfig($this->uniacid);

        $data = !empty($config['login_protocol'])?$config['login_protocol']:'';

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-08-10 18:30
     * @功能说明:
     */
    public function getConfig(){

        $config = longbingGetAppConfig($this->uniacid);

        $data['login_protocol'] = $config['login_protocol'];

        $data['app_text'] = $config['app_text'];

        $data['primaryColor'] = $config['primaryColor'];

        $data['subColor'] = $config['subColor'];

        $data['app_logo'] = $config['app_logo'];

        $data['web_code_img'] = $config['web_code_img'];

        $data['information_protection'] = $config['information_protection'];

        $setting_model = new MassageConfig();

        $setting = $setting_model->dataInfo(['uniacid'=>$this->uniacid]);

        $data['app_download_img'] = $setting['app_download_img'];

        $data['android_link'] = $setting['android_link'];

        $data['ios_link'] = $setting['ios_link'];

        $arr = getConfigSettingArr($this->uniacid,['coach_android_link','coach_ios_link']);

        $data['coach_android_link'] = $arr['coach_android_link'];

        $data['coach_ios_link'] = $arr['coach_ios_link'];

        $short_config_model = new ShortCodeConfig();

        $short_config = $short_config_model->dataInfo(['uniacid'=>$this->uniacid]);

        $data['short_code_status'] = $short_config['short_code_status'];

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-02-26 17:12
     * @功能说明:获取h5配置
     *
     */
    public function getWebConfig(){

        $input= $this->_param;

        $update = !empty($input['update'])?$input['update']:0;

        $config = longbingGetAppConfig($this->uniacid,false);

        $data['appid'] = $config['web_app_id'];

        $data['timestamp'] = time();

        $data['nonceStr']  = uniqid() . rand(10000, 99999);

        $wx_config = new WxSetting($this->uniacid);

        $jsapi_ticket = $wx_config->getWebTicket();

        $url_now = $input['page'];

        $str = "jsapi_ticket={$jsapi_ticket}&noncestr={$data['nonceStr']}&timestamp={$data['timestamp']}&url={$url_now}";

        $signature = sha1($str);

        $data['signature'] = $signature;

        $data['jsapi_ticket'] = $jsapi_ticket;

        return $this->success($data);
    }




    public function checkToken(){

        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = '2232eefrfg'; //对应微信公众平台配置的token
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            echo $_GET['echostr'];
            exit;
        }else{
            return false;
        }
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-11-24 18:19
     * @功能说明:
     */
    public function incCoachOrderNum(){

        $time = rand(1,3);

        \think\facade\Db::name('massage_service_coach_list')->where(['uniacid'=>666])->update(['order_num'=>Db::raw("order_num+$time")]);

        $arr = [

            1 => rand(5,10),

            2 => rand(10,20),

            3 => rand(20,30),

            4 => rand(20,30),

            5 => rand(10,20),

            6 => rand(1,5),

            7 => rand(1,3),

            8 => rand(1,3),
        ];

        foreach ($arr as $key=> $value){

            Db::name('massage_service_service_list')->where(['id'=>$key])->update(['sale'=>Db::raw("sale+$value"),'total_sale'=>Db::raw("total_sale+$value")]);

        }

        return $this->success(true);

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-17 10:06
     * @功能说明:ios登陆
     */
    function iosLogin()
    {
        $input = $this->_input;

        $uniacid = $this->uniacid;

        $input = $input['data'];

        $cap_id = !empty($input['cap_id'])?$input['cap_id']:0;

        $user_model = new \app\massage\model\User();

        $dis = [

            'openid'  => $input['openId'],

            'status'  => 1

        ];

        $user_info = $user_model->dataInfo($dis);

        $familyName = !empty($input['fullName']['familyName'])?$input['fullName']['familyName']:'';

        $giveName   = !empty($input['fullName']['giveName'])?$input['fullName']['giveName']:'';

        $name = !empty($familyName.$giveName)?$familyName.$giveName:'默认用户';

        $insert = [

            'uniacid'  => $uniacid,

            'nickName' => $name,

            'avatarUrl'=> 'https://' . $_SERVER['HTTP_HOST'] . '/admin/farm/default-user.png',

            'openid'   => $input['openId'],

            'push_id'  => !empty($input['push_id'])?$input['push_id']:'',

            'cap_id'   => $cap_id,

            'last_login_type' => 4,

            'ios_openid' => $input['openId'],

        ];

        $config = longbingGetAppConfig($this->uniacid,true);

        if(empty($user_info)){

            if(!empty($input['pid'])){
                //开启了分销审核
                if(getFxStatus($this->uniacid)==1){

                    $distribu_model = new DistributionList();

                    $dis = [

                        'user_id' => $input['pid'],

                        'status'  => 2
                    ];

                    $distribu_user = $distribu_model->dataInfo($dis);

                    if(!empty($distribu_user)){

                        $insert['pid'] = $input['pid'];
                    }

                }else{

                    $insert['pid'] = $input['pid'];
                }
            }

            $user_model->dataAdd($insert);

            $user_id  = $user_model->getLastInsID();

            if(!empty($input['coupon_atv_id'])){

                $coupon_atv_model = new CouponAtv();

                $coupon_atv_model->invUser($user_id,$input['coupon_atv_id']);
            }

        }else{

            if(empty($familyName.$giveName)){

                unset($insert['nickName']);
            }

            $user_id = $user_info['id'];

            $user_model->dataUpdate(['id'=>$user_id],$insert);

        }

        $user_info = $user_model->dataInfo(['id'=>$user_id]);

        $key = 'longbing_user_autograph_' . $user_info['id'];

        $key = md5($key);

        setCache($key, $user_info, 86400*3,999999999999);

        $arr = [

            'data'      => $user_info,

            'autograph' => $key
        ];

        return $this->success($arr);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-02-28 10:52
     * @功能说明:技师账号登录
     */
    public function coachAccountLogin(){

        $input = $this->_input;

        $uniacid = $this->uniacid;

        $dis = [

            'uniacid'   => $uniacid,

            'user_name' => $input['user_name'],

            'pass_word'=> $input['pass_word'],

            'pass_word_text'=> checkPass($input['pass_word']),

            'status' => 1
        ];

        $account_model = new CoachAccount();

        $find = $account_model->where($dis)->where('coach_id','>',0)->find();

        if(empty($find)){

            return $this->error('账号不存在或密码错误',200,400);
        }

        $coach_model = new Coach();

        $where[] = ['id','=',$find['coach_id']];

        $where[] = ['status','in',[1,2,3]];

        $coach_info = $coach_model->dataInfo($where);

        if(empty($coach_info)){

            return $this->error('技师审核被驳回',200,400);
        }

        if(!empty($coach_info)&&$coach_info['status']==1){

            return $this->error('技师正在审核中',200,400);
        }

        $data = [

            'coach_id' => $coach_info['id'],

            'id'       => $coach_info['user_id'],

            'uniacid'  => $coach_info['uniacid'],

            'coach_account_login' => 1
        ];

        $key = 'longbing_coach_autograph_' . $coach_info['id'];

        $key = md5($key);

        setCache($key, $data, 86400*3,999999999999);

        $arr = [

            'data'      => $data,

            'autograph' => $key
        ];

        return $this->success($arr);
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-02-27 17:39
     * @功能说明:设置技师账号
     */
    public function setCoachAccount(){

        $input = $this->_input;

        $account_model = new CoachAccount();

        $coach_account_phone_status = getConfigSetting($this->uniacid,'coach_account_phone_status');

        if($coach_account_phone_status==1){

            if (empty($input['phone_code'])){

                return $this->error('请输入验证码');
            }

            $phone = getConfigSetting($this->uniacid, 'login_auth_phone');

            $phone = $input['user_name'];

            $key   = $phone . 'coachAccountSendShortMsgkey';

            if ($input['phone_code'] != getCache($key, $this->uniacid)) {

                return $this->error('验证码错误', 200, 400);
            }
        }

        $insert = [

            'uniacid'   => $this->uniacid,

            'user_name' => $input['user_name'],

            'pass_word' => $input['pass_word'],

            'pass_word_text'=> checkPass($input['pass_word']),

            'status'    => 1
        ];
        //新增
        $check = $account_model->where($insert)->find();

        if(!empty($check)){

            if($check['coach_id']==0){

                return $this->error('账号密码失效',200,400);
            }

            return $this->error('该账号密码已被设置',200,400);
        }

        $res = $account_model->dataAdd($insert);

        $id  = $account_model->getLastInsID();

        return $this->success($id);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-03-04 18:45
     * @功能说明:绑定技师账号
     */
    public function bindCoachAccount(){

        $input = $this->_input;

        $account_model = new CoachAccount();

        $find = $account_model->where(['coach_id'=>$input['coach_id'],'status'=>1])->delete();

//        if(!empty($find)){
//
//            $account_model->where(['coach_id'=>$input['coach_id'],'status'=>1])->delete();
//
//           // return $this->error('该技师已有账号密码',200,400);
//        }

        $res = $account_model->dataUpdate(['id'=>$input['id']],['coach_id'=>$input['coach_id']]);

        return $this->success($res);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-03-05 10:00
     * @功能说明:根据手机号获取未设置账号的技师
     */
    public function getCoachList(){

        $input = $this->_param;

        $account_model = new CoachAccount();

        $coach_model = new Coach();

        $coach_id = $account_model->where(['uniacid'=>$this->uniacid,'status'=>1])->column('coach_id');

        $data     = $coach_model->where(['uniacid'=>$this->uniacid,'mobile'=>$input['phone']])->where('status','in',[1,2,3])->where('id','not in',$coach_id)->field('id as coach_id,coach_name,work_img')->order('id desc')->select()->toArray();

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-03-14 11:55
     * @功能说明:发送验证码
     */
    public function coachAccountSendShortMsg(){

        $input = $this->_input;
        //验证码验证
        $config = new ShortCodeConfig();

        $phone  = getConfigSetting($this->uniacid,'login_auth_phone');

        $phone = $input['phone'];

        $key    = 'coachAccountSendShortMsgkey';

        $res    = $config->sendSmsCode($phone,$this->uniacid,$key);

        if(!empty($res['Message'])&&$res['Message']=='OK'){

            return $this->success(1);

        }else{

            return $this->error($res['Message']);
        }
    }








}
