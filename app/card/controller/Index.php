<?php

namespace app\card\controller;

use app\Activity\controller\WxAppActivityItem;
use app\admin\info\PermissionAdmin;
use app\ApiRest;
use app\boss\info\PermissionBoss;
use app\card\model\CardBoss;
use app\card\model\CardCount;
use app\card\model\CardCoupon;
use app\card\model\CardCouponRecord;
use app\card\model\CardExtension;
use app\card\model\CardFormId;
use app\card\model\CardJob;
use app\card\model\CardTags;
use app\card\model\CardType;
use app\card\model\CardUserLabel;
use app\card\model\CardUserTags;
use app\card\model\Collection;
use app\card\model\Company;
use app\card\model\Config;
use app\card\model\DefaultSetting;
use app\card\model\Job;
use app\card\model\User;
use app\card\model\UserInfo;
use app\card\model\UserPhone;
use app\card\model\UserSk;
use app\Common\model\LongbingCardFromId;
use app\company\model\CardCompany;
use app\diy\model\DiyModel;
use app\radar\model\RadarCount;
use app\shop\model\IndexCoupon;
use app\shop\model\IndexUserInfo;
use app\shop\model\IndexShopCollage;
use app\website\model\CardTimelineComment;
use longbingcore\permissions\Tabbar;
use longbingcore\wxcore\WxSetting;
use think\App;
use think\facade\Cache;
use think\facade\Db;
use function Qiniu\explodeUpToken;

class Index extends ApiRest
{
    protected $modelUser;
    protected $modelUserInfo;
    protected $modelCollection;
    protected $modelCompany;
    protected $modelConfig;
    protected $app;

    protected $noNeedLogin = ['getWxCodeData'];

    // 继承 验证用户登陆
    public function __construct ( App $app )
    {
        parent::__construct( $app );
        $this->app = $app;
        $this->modelUser       = new User();
        $this->modelUserInfo   = new UserInfo();
        $this->modelCollection = new Collection();
        $this->modelCompany    = new Company();
        $this->modelConfig     = new Config();
        //$this->_user_id        = '2';
    }

    /**
     * @Purpose: 清除缓存
     *
     * @Method：GET
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function clearCacheData ()
    {
        clearCache( $this->_uniacid );
        return $this->success( [] );
    }

    /**
     * @Purpose: 用户在小程序授权之后跟新信息
     *
     * @Method：POST
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function updateWechatInfo ()
    {
        $userId   = $this->getUserId();
        $userInfo = $this->getUserInfo();

        $verify = [ 'avatarUrl' => '', 'city' => '', 'country' => '', 'gender' => '', 'language' => '', 'nickName' => '',
            'province'  => '' ];

        $params = lbGetParamVerify( $this->_input, $verify );

        $result = User::update( $params, [ 'id' => $userId ] );

        if ( $result === false )
        {
            return $this->error( 'update failed' );
        }

        $key = 'longbing_user_autograph_' . $userId;
        $key = md5( $key );
        $userInfo[ 'need_auth' ] = 0;
        setCache( $key, $userInfo, 3600, $this->_uniacid );
        if(!empty($params))
        {
            $user = longbingGetUser($userId ,$this->_uniacid);
            if(!empty($user)){
                if(isset($params['avatarUrl']) && !empty($params['avatarUrl'])) $user['avatarUrl'] = $params['avatarUrl'];
                if(isset($params['city']) && !empty($params['city'])) $user['city'] = $params['city'];
                if(isset($params['country']) && !empty($params['country'])) $user['country'] = $params['country'];
                if(isset($params['gender']) && !empty($params['gender'])) $user['gender'] = $params['gender'];
                if(isset($params['language']) && !empty($params['language'])) $user['language'] = $params['language'];
                if(isset($params['nickName']) && !empty($params['nickName'])) $user['nickName'] = $params['nickName'];
                if(isset($params['province']) && !empty($params['avataprovincerUrl'])) $user['province'] = $params['province'];
                if(!in_array($user['avatarUrl'] ,[$this->defaultImage['avatar']]) && !empty($user['avatarUrl'])) $user[ 'need_auth' ] = 0;
                longbingSetUser($userId ,$this->_uniacid ,$user);
            }
        }
        return $this->success( [] );
    }

    /**
     * @Purpose: 个人中心
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function userCenter()
    {
        $userInfo = $this->getUserInfo();
        $userId = $this->getUserId();
        $userInfo = longbingGetUser($userId, $this->_uniacid);
        //       $modelCompany = new Company();
//        $company = $modelCompany->getInfo($this->_uniacid, $userId, 0);

        $page = isset( $this->_param[ 'page' ] ) ? $this->_param[ 'page' ] : 1;

        //  判断有没有浏览过名片
        $checkCollection = $this->modelCollection->where( [ [ 'uniacid', '=', $this->_uniacid ], [ 'uid', '=', $userId ],
                [ 'status', '=', 1 ] ]
        )
            ->count();

        //  浏览过名片，返回已绑定的名片列表
        if ( $checkCollection )
        {
            $cardList = $this->modelCollection->bindCardList( $userId, $userInfo, $page, $this->_uniacid );
        }
        //  没有浏览过名片返回推荐名片列表
        else
        {
            $cardList = $this->modelCollection->defaultCardList( $page, $this->_uniacid ,$userId);
        }

//        $cardList['company_info'] = $company;
        $cardList['is_staff'] = $userInfo['is_staff'];

        return $this->success( $cardList, 200 );
    }

    /**
     * @Purpose: 用户信息
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function userInfo ()
    {

        $uniacid = $this->_uniacid;


        $permissionAdmin = new PermissionAdmin($this->_uniacid);
        $longbing_auth_mini = $permissionAdmin->getAuthNumber();

        $userModel = new User();
        $userInfoModel   = new UserInfo();
        $cardCountModel  = new CardCount();

        //By.jingshuixian  2020年4月21日12:12:53
        //解决微擎版本同一个域名授权问题
        if($this->_is_weiqin ){
            $app_model_name = APP_MODEL_NAME;
            $wxapp = Db::name('account')
                ->alias('a')
                ->join('wxapp_versions v' , 'a.uniacid = v.uniacid')
                ->join('account_wxapp account_wxapp' , 'a.uniacid = account_wxapp.uniacid')
                ->field(['account_wxapp.name as mini_name', 'account_wxapp.uniacid as  modular_id' , 'account_wxapp.uniacid' ])
                ->where([ ['v.modules', 'like', "%{$app_model_name}%"]  ,  ['a.type', '=', 4] ,['a.isdeleted', '=', 0]  ])
                ->group('account_wxapp.uniacid')
                ->order('account_wxapp.uniacid asc')
                ->select()
                ->toArray();

            $check_mini_user_count =  count($wxapp) ;
            if ( $check_mini_user_count > $longbing_auth_mini) {
                foreach ($wxapp as $index => $item) {
                    $now = $index + 1;
                    if ($item['uniacid'] == $uniacid && $now > $longbing_auth_mini) {
                        $msg = '小程序数量达到上限! 请联系管理员(' . $check_mini_user_count . '-' . $longbing_auth_mini.')';
                        //echo json_encode(['code' => 402, 'error' => $msg]);
                        //exit;
                        return $this->error( $msg , 402);
                    }
                }
            }


        }else{
            $check_mini_user = $userModel
                ->field('uniacid')
                ->group('uniacid')
                ->order('id asc')
                ->select();

            $check_mini_user_count = count($check_mini_user);

            if ($check_mini_user_count > $longbing_auth_mini) {
                foreach ($check_mini_user as $index => $item) {
                    $now = $index + 1;
                    if ($item['uniacid'] == $uniacid && $now > $longbing_auth_mini) {
                        $msg = '小程序数量达到上限! 请联系管理员' . $check_mini_user_count . '-' . $longbing_auth_mini;
                        //echo json_encode(['code' => 402, 'error' => $msg]);
                        //exit;
                        return $this->error( $msg , 402);
                    }
                }
            }

            $check_mini_info = $userInfoModel
                ->field('uniacid')
                ->group('uniacid')
                ->order('id asc')
                ->select();
            $check_mini_info_count = count($check_mini_user);

            if ($check_mini_info_count > $longbing_auth_mini) {
                foreach ($check_mini_info as $index => $item) {
                    $now = $index + 1;
                    if ($item['uniacid'] == $uniacid && $now > $longbing_auth_mini) {
                        $msg = '小程序数量达到上限!! 请联系管理员' . $check_mini_info_count . '-' . $longbing_auth_mini;
                        //echo json_encode(['code' => 402, 'error' => $msg]);
                        //exit;
                        return $this->error( $msg , 402);
                    }
                }
            }

            $check_mini_count = $cardCountModel
                ->field('uniacid')
                ->group('uniacid')
                ->order('id asc')
                ->select();

            $check_mini_count_count = count($check_mini_user);
            if ($check_mini_count_count > $longbing_auth_mini) {
                foreach ($check_mini_count as $index => $item) {
                    $now = $index + 1;
//                    longbingDebugOneService('now ' . $now . 'uniacid '. $item[ 'uniacid' ]);
                    if ($item['uniacid'] == $uniacid && $now > $longbing_auth_mini) {
                        $msg = '小程序数量达到上限!!! 请联系管理员' . $check_mini_count_count . '-' . $longbing_auth_mini;
                        //echo json_encode(['code' => 402, 'error' => $msg]);
                        //exit;

                        return $this->error( $msg , 402);
                    }
                }
            }
        }

        $user_id = $this->getUserId();
        $userInfo = longbingGetUser($user_id, $this->_uniacid);


        //最后访问的名片处理
        if (!empty($userInfo) && isset($userInfo['last_staff_id'])) {
            //判断last_staff_id是否是员工 chenniang(龙兵科技)
            $staff_user = $userModel->where(['id'=>$userInfo['last_staff_id'],'is_staff'=>1])->count();
            //如果不是
            if(empty($staff_user)){
                //模型
                $collectionModel = new Collection();
                //分配一个
                $user['last_staff_id'] = $collectionModel->getCard($user_id,$uniacid);
                //从新获取信息
                $userInfo = longbingGetUser($user_id, $this->_uniacid);
            }
        }
        $userInfo['cardInfo'] = [];
        //是员工则把名片信息查出来
        if (isset($userInfo['is_staff']) && $userInfo['is_staff'] == 1) {
            $key = 'longbing_user_card_info_' . $userInfo['id'];

//            $value = getCache( $key, $this->_uniacid );
            $value = [];
            if ($value && false) {
                $value['from_cache'] = 1;
                $userInfo['cardInfo'] = $value;
            } else {
//              $cardInfo = UserInfo::where( [ [ 'fans_id', '=', $userInfo[ 'id' ] ] ] )
//                                  ->find();
                $cardInfo = longbingGetUserInfo($user_id, $this->_uniacid);
                if ($cardInfo) {
//                  $cardInfo = $cardInfo->toArray();

                    $job = Job::where([['id', '=', $cardInfo['job_id']]])
                        ->find();

                    if (!$job) {
                        $cardInfo['job_name'] = '未设置职位';
                    } else {
                        $job = $job->toArray();
                        $cardInfo['job_name'] = $job['name'];
                    }
                    $cardInfo = transImages($cardInfo, ['images'], ',');
                    $cardInfo = transImagesOne($cardInfo, ['avatar', 'voice', 'my_url', 'my_video', 'my_video_cover', 'bg',
                            'vr_cover', 'vr_path']
                    );
                    if (isset($cardInfo['my_video']) && is_array($cardInfo['my_video']) && !empty($cardInfo['my_video'])) {
                        $cardInfo['my_video_vid'] = lbGetTencentVideo($cardInfo['my_video'][0]);
                    }

                    $modelCount = new RadarCount();
                    list($viewCount, $thumbCount) = $modelCount->RadarNumber($userInfo['id'], $this->_uniacid);
                    $cardInfo['viewCount'] = $viewCount;
                    $cardInfo['thumbCount'] = $thumbCount;
                    //获取公司信息

                    $company = longbingGetUserCompany($cardInfo['company_id'], $uniacid);
                    if (isset($company['name'])) $company_name = $company['name'];

                    $cardInfo['company_info'] = $company;

                    $modelCompany = new Company();

                    $cardInfo[ 'company_info' ] = $modelCompany->changeTopName($cardInfo[ 'company_info' ]);

                    $cardInfo['company_name']   = !empty($cardInfo[ 'company_info' ]['name'])? $cardInfo[ 'company_info' ]['name']:$cardInfo['company_name'];
                    //获取递名片数量
                    $radar_model = new RadarCount();
                    $cardInfo['share_number'] = $radar_model->getShareNumberV2($user_id);

                    //生成活动二维码
                    $qrData = [
                        'pid' => $this->_user_id,
                        'staff_id' => $this->_user_id,
                        'type' => 12,
                        'key' => 1
                    ];


                    $src = 'image/' . $this->_uniacid . '/' . 'wxcode/' . md5($this->_uniacid . json_encode($qrData, true)) . '.jpeg';
                    if (!longbingHasLocalFile($src)) {
                        $push_data = array(
                            'action' => 'longbingCreateWxCode',
                            'event' => 'longbingCreateWxCode',
                            'uniacid' => $this->_uniacid,
                            'data' => $qrData,
                            'page' => 'pages/user/home',
                            'type' => 3
                        );
                        publisher(json_encode($push_data, true));
                    }
                    //获取名片图
                    $cardInfo['share_img'] = "images/share_img/{$uniacid}/share-{$this->_user_id}.png";
                    if (!longbingHasLocalFile($cardInfo['share_img'])) {
                        $user['share_img'] = null;
                        $cardInfo['share_img'] = null;
                    } else {
                        $cardInfo = transImagesOne($cardInfo, ['share_img']);
                    }

                    //
                    $cardInfo['posterQr'] = $src;
                    $cardInfo = transImagesOne($cardInfo, ['posterQr'], $this->_uniacid);
                    //获取名片码
                    $card_code_data = ["data" => ["staff_id" => (string)$user_id, "pid" => (string)$user_id, "type" => 4, "key" => 1]];
                    $qr_path = 'image/' . $this->_uniacid . '/' . 'wxcode/' . md5($this->_uniacid . json_encode($card_code_data, true)) . '.jpeg';
                    $cardInfo['qr_path'] = null;
                    if (longbingHasLocalFile($qr_path)) {
                        $cardInfo['qr_path'] = $qr_path;
                    } else {
                        $wxcode_data = longbingCreateWxCode($this->_uniacid, $card_code_data);


                        if (isset($wxcode_data['path']) && !empty($wxcode_data['path'])) $cardInfo['qr_path'] = $wxcode_data['path'];
                    }
                    if (!empty($cardInfo['qr_path'])) $cardInfo = transImagesOne($cardInfo, ['qr_path'], $this->_uniacid);
                    $userInfo['cardInfo'] = $cardInfo;
                    setCache($key, $cardInfo, 1800, $this->_uniacid);
                    //数据处理
                    if (isset($cardInfo['share_text']) && !empty($cardInfo['share_text'])) {

                        $share_text = $cardInfo['share_text'];
                        if (strpos($share_text, '#公司#')) $share_text = str_replace('#公司#', $company_name, $share_text);
                        if (strpos($share_text, '#职务#')) $share_text = str_replace('#职务#', $cardInfo['job_name'], $share_text);
                        if (strpos($share_text, '#我的名字#')) $share_text = str_replace('#我的名字#', $cardInfo['name'], $share_text);
                        if (strpos($share_text, '$company')) $share_text = str_replace('$company', $company_name, $share_text);
                        if (strpos($share_text, '$job')) $share_text = str_replace('$job', $cardInfo['job_name'], $share_text);
                        if (strpos($share_text, '$name')) $share_text = str_replace('$name', $cardInfo['name'], $share_text);
                        $userInfo['cardInfo']['share_text'] = $share_text;
                    } else {
                        $userInfo['cardInfo']['share_text'] = lang("card share text");
                    }
                }
            }
        }
        return $this->success($userInfo);
    }

    /**
     * @Purpose: 小程序配置接口
     *
     * @Method GET
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function config ()
    {

        $data = longbingGetAppConfig($this->_uniacid);

        unset( $data[ 'auth_code' ] );

        $exist = Db::query( 'show tables like "%longbing_card_config%"' );

        $auth_info = false;

        $cardauth2_config_exist = Db::query('show tables like "%longbing_cardauth2_config%"');

        if (!empty($exist) && !empty($cardauth2_config_exist)) {
            $auth_info = Db::name('longbing_cardauth2_config')
                ->where([['modular_id', '=', $this->_uniacid]])
                ->find();
        }
        $data[ 'is_pay_shop' ] = 1;
        //  判断能不能使用商城的支付功能
        if ( $auth_info && isset( $auth_info[ 'pay_shop' ] ) && $auth_info[ 'pay_shop' ] == 0 )
        {
            $data[ 'is_pay_shop' ] = 0;
        }

        if ( isset( $data[ 'btn_talk' ] ) && !$data[ 'btn_talk' ] )
        {

            $data[ 'btn_talk' ] = '面议';

        }
        $data['tabBar1'] = [];
        //tabbar用新的方式返回
        $data['tabBar1'] = Tabbar::all($this->_uniacid, $this->_user_id);

        $pluginAuth      = longbingGetPluginAuth($this->_uniacid, $this->_user_id, $auth_info);


        $data            = array_merge($data, $pluginAuth);

        return $this->success($data);

    }





    /**
     * @Purpose: 小程序允许使用的名片样式
     *
     * @Method GET
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function cardType ()
    {
        $userId = $this->getUserId();

        $card = UserInfo::where( [ [ 'fans_id', '=', $userId ], [ 'is_staff', '=', 1 ], [ 'uniacid', '=', $this->_uniacid ] ] )
            ->field( [ 'card_type' ] )
            ->find();

        if ( !$card )
        {
            $this->error( 'card info not found', 402 );
        }

        $modelCardType = new CardType();

        $count = $modelCardType->where( [ [ 'uniacid', '=', $this->_uniacid ] ] )
            ->count();
        if ( !$count )
        {
            //  初始化名片样式
            $data = $modelCardType->initCardType( $this->_uniacid );
        }
        else
        {
            $data = $modelCardType->where( [ [ 'uniacid', '=', $this->_uniacid ], [ 'status', '=', 1 ] ] )
                ->field( [ 'card_type', 'img' ] )
                ->select();
        }

        foreach ( $data as $index => $item )
        {
            $data[ $index ][ 'selected' ] = 0;
            if ( $item[ 'card_type' ] == $card->card_type )
            {
                $data[ $index ][ 'selected' ] = 1;
            }
        }

        return $this->success( $data );
    }

    /**
     * @Purpose: 修改名片样式
     *
     * @Method POST
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function editCardType ()
    {
        $userId = $this->getUserId();

        $verify = [ 'card_type' => 'required' ];

        $params = lbGetParamVerify( $this->_input, $verify );

        $card = UserInfo::where( [ [ 'fans_id', '=', $userId ], [ 'is_staff', '=', 1 ], [ 'uniacid', '=', $this->_uniacid ] ] )
            ->field( [ 'card_type' ] )
            ->find();

        if ( !$card )
        {
            $this->error( 'card info not found', 402 );
        }

        $card->card_type = $params[ 'card_type' ];
        $result          = $card->save();

        if ( $result === false )
        {
            $this->error( 'edit fail', 402 );
        }else{
            //清除名片缓存
            $key = 'longbing_card_card_info_' . $userId;
            delCache($key ,$this->_uniacid);
        }
        return $this->success( [] );
    }

    /**
     * @Purpose: 修改名片录音
     *
     * @Method POST
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function editCardVoice ()
    {
        $userId = $this->getUserId();

        $verify = [ 'voice_id' => 'required', 'voice_time' => 0 ];

        $params = lbGetParamVerify( $this->_input, $verify );

        $card = UserInfo::where( [ [ 'fans_id', '=', $userId ], [ 'is_staff', '=', 1 ], [ 'uniacid', '=', $this->_uniacid ] ] )
            ->field( [ 'voice', 'voice_time' ] )
            ->find();

        if ( !$card )
        {
            $this->error( 'card info not found', 402 );
        }

        $card->voice      = $params[ 'voice_id' ];
        $card->voice_time = $params[ 'voice_time' ];
        $result           = $card->save();

        if ( $result === false )
        {
            $this->error( 'edit fail', 402 );
        }else{
            //清除名片缓存
            $key = 'longbing_card_info_' . $userId;
            delCache($key ,$this->_uniacid);
            longbingGetUserInfo($userId ,$this->_uniacid ,true);
        }
        return $this->success( [] );
    }

    /**
     * @Purpose: 创建 / 编辑名片时回显数据
     *
     * @Method GET
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function reviewData ()
    {
        $userId   = $this->getUserId();
        $userInfo = $this->getUserInfo();

        $modelCompany = new Company();
        $modelJob     = new CardJob();
        $modelType    = new CardType();

        $card = UserInfo::where( [ [ 'uniacid', '=', $this->_uniacid ], [ 'fans_id', '=', $userId ] ] )
            ->find();

        if ( $card )
        {
            $card                   = $card->toArray();

            $data                   = transImagesOne( $card, [ 'avatar' ] ,$this->_uniacid);

            $data[ 'companyList' ]  = $modelCompany->getListByUser( $userId, $this->_uniacid, 0, $card[ 'company_id' ] );

            $data[ 'jobList' ]      = $modelJob->getListByUser( $card[ 'job_id' ], $this->_uniacid );

            $data[ 'typeList' ]     = $modelType->getCardTypeList( $this->_uniacid, $card[ 'card_type' ] );

            $data[ 'company_info' ] = $modelCompany->getInfo( $this->_uniacid, 0, $card[ 'company_id' ] );

            if(!empty($data['company_info'])){

                $data[ 'company_info' ]['top_name'] = $modelCompany->where(['id'=>$data['company_info']['top_id'],'status'=>1])->value('name');
            }

        }
        else
        {
            $data                   = [ 'avatar' => $userInfo[ 'avatarUrl' ], 'name' => $userInfo[ 'nickName' ],
                'phone'  => $userInfo[ 'phone' ], 'email' => '' ];
            $data[ 'companyList' ]  = $modelCompany->getListByUser( $userId, $this->_uniacid, 1, 0 );
            $data[ 'jobList' ]      = $modelJob->getListByUser( 0, $this->_uniacid );
            $data[ 'typeList' ]     = $modelType->getCardTypeList( $this->_uniacid );
            $data[ 'company_info' ] = [];
        }

        $config = $this->modelConfig->getConfig( $this->_uniacid );

        $data[ 'job_switch' ] = $config[ 'job_switch' ];

        return $this->success( $data );
    }

    /**
     * @Purpose: 名片信息
     *
     * @Method GET
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function cardInfo ()
    {
        //获取用户id
        $userId = $this->getUserId();
        $uniacid = $this->_uniacid;
        //获取必要参数
        $verify = [ 'staff_id' => 'required' ];
        $params = lbGetParamVerify( $this->_param, $verify );
        $staff_id = $params['staff_id'];

        $staff = longbingGetUserInfo($staff_id ,$this->_uniacid);
        if(empty($staff) || empty($staff['is_staff'])) return $this->error(lang('card info not found') ,403);



        /*
         //获取缓存数据
         $key = 'longbing_card_info_'.$staff_id;
         $plugin_model = new PluginManager($this->app);
         if(hasCache($key ,$this->_uniacid))
         {
             $result = getCache($key ,$this->_uniacid);
             $result['plugin'] = $plugin_model->trigger('decorate');
             if(!empty($result)) return $this->success($result);

         }
         */


        //获取名片数据
        $card = UserInfo::alias( 'a' )
            ->field( [ 'a.*', 'b.name as job_name' ] )
            ->join( 'longbing_card_job b', 'a.job_id = b.id', 'LEFT' )
            ->where( [ [ 'a.fans_id', '=', $params[ 'staff_id' ] ], [ 'a.is_staff', '=', 1 ],
                    [ 'a.uniacid', '=', $this->_uniacid ] ]
            )
            ->find();
        //判断名片是否存在
        if ( empty($card) )
        {
            return $this->error( lang('card info not found') ,403);
        }
        //更新最后查看信息
        if(!in_array($userId, [$params[ 'staff_id' ]]))
        {
            $user_update = User::update( [ 'last_staff_id' => $params[ 'staff_id' ] ], [ 'id' => $userId] );
            if(!empty($user_update)){
                $user = longbingGetUser($userId ,$this->_uniacid);
                $user['last_staff_id']  = $params[ 'staff_id' ];
                longbingSetUser($userId ,$this->_uniacid ,$user);
            }
        }
        $cardInfo = $card->toArray();
        //判断名片是否拥有职位（没有就默认）
        if ( !$cardInfo[ 'job_name' ] )
        {
            $cardInfo[ 'job_name' ] = lang('not set job');
        }
        //默认vr数据
        if(!isset($cardInfo['vr_tittle']) || empty($cardInfo['vr_tittle']))
        {
            $config = longbingGetAppConfig($this->_uniacid);
            if(!empty($config) && !empty($config['vr_tittle']))
            {
                $cardInfo['vr_tittle'] = $config['vr_tittle'];
            }else{
                $cardInfo['vr_tittle'] = lang('panoramic');
            }
        }
        $modelConfig = new Config();
//      $config      = $modelConfig->getConfig( $this->_uniacid );
        $config      = longbingGetAppConfig($this->_uniacid);
        //默认背景音乐
        if ( !$cardInfo[ 'bg' ] )
        {
            $cardInfo[ 'bg' ] = $config[ 'default_voice' ];
        }

        if ( !$cardInfo[ 'bg_switch' ] )
        {
            $cardInfo[ 'bg_switch' ] = $config[ 'default_voice_switch' ];
        }


        // 处理图片
        $cardInfo = transImages( $cardInfo, [ 'images' ], ',' );
        $cardInfo = transImagesOne( $cardInfo, [ 'avatar', 'voice', 'my_url', 'my_video', 'my_video_cover', 'bg', 'vr_cover',
                'vr_path' ]
        );
        $modelCount = new RadarCount();
        list( $viewCount, $thumbCount ) = $modelCount->RadarNumber( $cardInfo[ 'fans_id' ] ,$this->_uniacid);
        $cardInfo[ 'viewCount' ]  = $viewCount;
        $cardInfo[ 'thumbCount' ] = $thumbCount;

        //  名片最近浏览情况
        list( $cardInfo[ 'view_list' ], $cardInfo[ 'view_count' ] ) = $modelCount->getCardViewInfo( $params[ 'staff_id' ], $this->_uniacid );
        //获取公司信息
        $modelCompany = new Company();
        if ( $cardInfo[ 'company_id' ] )
        {
            $card_company = new CardCompany();
            $company_id = $card_company->getUserTopCompanyId($cardInfo[ 'company_id' ]);
            $company = $card_company->getinfo(['uniacid'=>$this->_uniacid,'id'=>$company_id]);
//            $company = $modelCompany->getInfo( $this->_uniacid, 0, $cardInfo[ 'company_id' ] );
        }
        else
        {
            $company = $modelCompany->getInfo( $this->_uniacid, 0, 0 );
        }
        $cardInfo[ 'company_info' ] = $company;
        $company_name = '';
        if(isset($company['name'])) $company_name = $company['name'];
        //获取名片码
        $card_code_data = ["data" => ["staff_id" => $params['staff_id'] ,"pid" => $userId ,"type" => 4 ,"key" => 1]];
        $qr_path = 'image/' . $this->_uniacid . '/' . 'wxcode/' . md5($this->_uniacid . json_encode($card_code_data ,true)) . 'jpeg';
        $cardInfo['qr_path'] = null;
        if(longbingHasLocalFile($qr_path))
        {
            $cardInfo['qr_path'] = $qr_path;
        }else{
            $wxcode_data = longbingCreateWxCode($this->_uniacid ,$card_code_data);
            if(isset($wxcode_data['path']) && !empty($wxcode_data['path'])) $cardInfo['qr_path'] = $wxcode_data['path'];
        }
        if(!empty($cardInfo['qr_path'])) $cardInfo = transImagesOne($cardInfo ,['qr_path']);
        //设置分享数据
        if(isset($cardInfo['share_text']) && !empty($cardInfo['share_text']))
        {
            $share_text = $cardInfo['share_text'];
            if(strpos($share_text,'#公司#')) $share_text = str_replace('#公司#',$company_name,$share_text);
            if(strpos($share_text,'#职务#')) $share_text = str_replace('#职务#',$cardInfo[ 'job_name' ],$share_text);
            if(strpos($share_text,'#我的名字#')) $share_text = str_replace('#我的名字#',$cardInfo[ 'name' ],$share_text);
            if(strpos($share_text,'$company')) $share_text = str_replace('$company',$company_name,$share_text);
            if(strpos($share_text,'$job')) $share_text = str_replace('$job',$cardInfo[ 'job_name' ],$share_text);
            if(strpos($share_text,'$name')) $share_text = str_replace('$name',$cardInfo[ 'name' ],$share_text);
            $cardInfo['share_text'] = $share_text;
        }else{
            $cardInfo['share_text'] = lang("card share text");
        }
        //名片分享链接
        $cardInfo['share_img'] = "images/share_img/{$uniacid}/share-{$staff_id}.png";
        if(!longbingHasLocalFile($cardInfo['share_img'])) {
            $user['share_img'] = null;
            $cardInfo['share_img'] = null;
        }else{
            $cardInfo = transImagesOne($cardInfo, ['share_img']);
        }
        //判断视频等参数
//        $config = longbingGetAppConfig($this->_uniacid);
        //默认视频
        if(isset($config['default_video']) && empty($cardInfo['my_video'])) $cardInfo['my_video'] = $config['default_video'];
        //默认视频图片
        if(isset($config['default_video_cover']) && empty($cardInfo['my_video_cover'])) $cardInfo['my_video_cover'] = $config['default_video_cover'];
        //默认背景音乐
        if(isset($config['default_voice']) && empty($cardInfo['bg'])) $cardInfo['bg'] = $config['default_voice'];
        //VR图片
        if(isset($config['vr_cover']) && empty($cardInfo['vr_cover'])) $cardInfo['vr_cover'] = $config['vr_cover'];
        //默认VR路径
        if(isset($config['vr_path']) && empty($cardInfo['vr_path'])) {
            $cardInfo['vr_path'] = $config['vr_path'];
            //默认VR switch
            $cardInfo['vr_switch'] = $config['vr_switch'];
        }
        //默认VR标题
        if(isset($config['vr_tittle']) && empty($cardInfo['vr_tittle'])) $cardInfo['vr_tittle'] = $config['vr_tittle'];
        //默认视频(处理)
        if ( isset( $cardInfo[ 'my_video' ] ) && $cardInfo[ 'my_video' ] ) $cardInfo[ 'my_video_vid' ] = lbGetTencentVideo( $cardInfo[ 'my_video' ] );
        $modelTags = new CardTags();

        $cardInfo[ 'tag_list' ] = $modelTags->cardTagList( $params[ 'staff_id' ], $userId, $this->_uniacid );



        //获取商品推荐列表  By.jingshuixian

        /*$modelExtension = new CardExtension();

        $cardInfo[ 'goods_list' ] = $modelExtension->cardExtensionList( $params[ 'staff_id' ], $this->_uniacid );
        foreach($cardInfo[ 'goods_list']  as $key => $val)
        {
            $cardInfo[ 'goods_list'][$key]['is_collage'] = 0;
            $collage_model  = new IndexShopCollage();
            $count          = $collage_model->getCollage(['goods_id' => $val['id'] ,'uniacid' => $this->_uniacid ,'status' => 1]);
            if(!empty($count)) $cardInfo[ 'goods_list'][$key]['is_collage'] = 1;
        }*/





        if ( isset( $cardInfo[ 'vr_tittle' ] ) && !$cardInfo[ 'vr_tittle' ] )
        {
            $cardInfo[ 'vr_tittle' ] = 'VR全景';
        }


        //  是否语音点赞
        $checkVoice = CardCount::where( [ [ 'type', '=', 1 ], [ 'user_id', '=', $userId ],
                [ 'to_uid', '=', $params[ 'staff_id' ] ], [ 'sign', '=', 'praise' ] ]
        )
            ->count();
        //  是否给名片点赞
        $checkThumb = CardCount::where( [ [ 'type', '=', 3 ], [ 'user_id', '=', $userId ],
                [ 'to_uid', '=', $params[ 'staff_id' ] ], [ 'sign', '=', 'praise' ] ]
        )
            ->count();
        //递名片
        $radar_model = new RadarCount();
        $share_number = $radar_model->getShareNumberV2($params['staff_id']);
        if ( $checkVoice )
        {
            $cardInfo[ 'voiceThumbs' ] = 1;
        }
        else
        {
            $cardInfo[ 'voiceThumbs' ] = 0;
        }
        if ( $checkThumb )
        {
            $cardInfo[ 'isThumbs' ] = 1;
        }
        else
        {
            $cardInfo[ 'isThumbs' ] = 0;
        }

        $cardInfo[ 'view_count' ] = $cardInfo[ 'view_count' ] + $cardInfo[ 'view_number' ];
        $cardInfo[ 'thumbCount' ] = $cardInfo[ 'thumbCount' ] + $cardInfo[ 't_number' ];
        $cardInfo[ 'share_number' ] = $share_number;



        $time = time();

        $coupon = CardCoupon::where( [ [ 'end_time', '>', $time ], [ 'status', '=', 1 ], [ 'uniacid', '=', $this->_uniacid ] , [ 'number' , '>' , 0]] )
            ->order( [ 'top' => 'desc', 'id' => 'desc' ] )
            ->select()
            ->toArray();

        $cardInfo[ 'coupon' ]                  = array();
        $cardInfo[ 'coupon_last_record' ]      = array();
        $cardInfo[ 'coupon_last_record_user' ] = array();
        if ( $coupon )
        {
            foreach ( $coupon as $index => $item )
            {
                $record_list = CardCouponRecord::where( [ [ 'coupon_id', '=', $item[ 'id' ] ],
                    [ 'staff_id', '=', $params[ 'staff_id' ] ],
                    [ 'uniacid', '=', $this->_uniacid ] ,

                ] )
                    ->order( [ 'id' => 'desc' ] )
                    ->select()
                    ->toArray();
                if ( count( $record_list ) < $item[ 'number' ] )
                {
                    $cardInfo[ 'coupon' ] = $item;
                    if ( count( $record_list ) )
                    {
                        $cardInfo[ 'coupon_last_record' ] = $record_list;
                    }
                    break;
                }
            }
        }
        if ( !empty( $cardInfo[ 'coupon_last_record' ] ) )
        {
            foreach ( $cardInfo[ 'coupon_last_record' ] as $index => $item )
            {
                $user = longbingGetUser($item[ 'user_id' ] ,$this->_uniacid);
                if ( mb_strlen( $user[ 'nickName' ], 'utf8' ) > 4 )
                {
                    $user[ 'nickName' ] = mb_substr( $user[ 'nickName' ], 0, 4, "UTF-8" );
                }
                $cardInfo[ 'coupon_last_record' ][ $index ][ 'user_info' ] = $user;
            }
        }


        //By.jingshuixian  删除 缓存 和 装修

        /*if(!empty($cardInfo)) setCache($key ,$cardInfo ,600 ,$this->_uniacid);
        $cardInfo['plugin'] = $plugin_model->trigger('decorate');*/
        return $this->success( $cardInfo );
    }


    /**
     * @Purpose: 名片信息
     *
     * @Method GET
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function cardInfoV2 ()
    {

        //获取用户id
        $userId = (string) $this->getUserId();
        $uniacid = $this->_uniacid;

        //获取必要参数
        $verify = [ 'staff_id' => 'required' ,'is_base' => 0];
        $params = lbGetParamVerify( $this->_param, $verify );
        $input  = $this->_param;
        $staff_id = $params['staff_id'];
        //获取员工数据
        $cardInfo = longbingGetUserCard($staff_id ,$this->_uniacid);
        if(empty($cardInfo) || empty($cardInfo['is_staff'])) return $this->error(lang('card info not found') ,403);
        //判断名片是否拥有职位（没有就默认）
        if ( !$cardInfo[ 'job_name' ] )
        {
            $cardInfo[ 'job_name' ] = lang('not set job');
        }
        //获取名片码
        $card_code_data = ["data" => ["staff_id" => $params['staff_id'] ,"pid" => $userId ,"type" => 4 ,"key" => 1]];
        $qr_path = 'image/' . $this->_uniacid . '/' . 'wxcode/' . md5($this->_uniacid . json_encode($card_code_data ,true)) . '.jpeg';
        if(!longbingHasLocalFile($qr_path))
        {
            $push_data = array(

                'action'     => 'longbingCreateWxCode',
                'event'      => 'longbingCreateWxCode',
                'uniacid'    => $this->_uniacid,
                'data'       => $card_code_data,
                'page'    => '',
                'type'    => 3
            );

            publisher(json_encode($push_data ,true));

        }
        $cardInfo['qr_path'] = $qr_path;

        if(!empty($cardInfo['qr_path'])) $cardInfo = transImagesOne($cardInfo ,['qr_path']);


        $modelCompany = new Company();
        //检查公司默认数据
        if ( empty($cardInfo['company_info']) )
        {
            //jingshuixian 0 =  $cardInfo['fans_id']
            $company = $modelCompany->getInfo( $this->_uniacid, $cardInfo['fans_id'], 0 );

            $cardInfo[ 'company_info' ] = $company;

            if(isset($company['name'])) $company_name = $company['name'];
        }

        $cardInfo[ 'company_info' ] = $modelCompany->changeTopName($cardInfo[ 'company_info' ]);

        $cardInfo['company_name']   = !empty($cardInfo[ 'company_info' ]['name'])? $cardInfo[ 'company_info' ]['name']:$cardInfo['company_name'];

        //设置分享数据
        if(isset($cardInfo['share_text']) && !empty($cardInfo['share_text']))
        {
            $share_text = $cardInfo['share_text'];

            if(strpos($share_text,'#公司#')) $share_text = str_replace('#公司#',$cardInfo[ 'company_name' ],$share_text);

            if(strpos($share_text,'#职务#')) $share_text = str_replace('#职务#',$cardInfo[ 'job_name' ],$share_text);

            if(strpos($share_text,'#我的名字#')) $share_text = str_replace('#我的名字#',$cardInfo[ 'name' ],$share_text);

            if(strpos($share_text,'$company')) $share_text = str_replace('$company',$cardInfo[ 'company_name' ],$share_text);

            if(strpos($share_text,'$job')) $share_text = str_replace('$job',$cardInfo[ 'job_name' ],$share_text);

            if(strpos($share_text,'$name')) $share_text = str_replace('$name',$cardInfo[ 'name' ],$share_text);
            $cardInfo['share_text'] = $share_text;
        }else{

//            $cardInfo['share_text'] = lang("card share text");
            $cardInfo['share_text'] = '您好，我是'.$cardInfo[ 'company_name' ].'的'.$cardInfo[ 'job_name' ].$cardInfo[ 'name' ].',请惠存';
        }
        //名片分享链接
        $cardInfo['share_img'] = "images/share_img/{$uniacid}/share-{$staff_id}.png";
        // var_dump($cardInfo['share_img']);die;
        if(!longbingHasLocalFile($cardInfo['share_img'])) {
            $user['share_img'] = null;
            $cardInfo['share_img'] = null;
        }else{
            $cardInfo = transImagesOne($cardInfo, ['share_img']);
        }




        //更新最后查看信息
        //By.jingshuixian 最后访问的员工ID不同是更新
//        if(!in_array($userId, [$params[ 'staff_id' ]]))
//        {
        $user_update = User::update( [ 'last_staff_id' => $params[ 'staff_id' ] ], [ 'id' => $userId] );
        if(!empty($user_update)){
            $user = longbingGetUser($userId ,$this->_uniacid);
            $user['last_staff_id']  = $params[ 'staff_id' ];
            longbingSetUser($userId ,$this->_uniacid ,$user);
        }
//        }


        //是否只需要基础数据======================================================================================================有返回结果==============================
        if(isset($params['is_base']) && !empty($params['is_base']))
        {
            $cardInfo = transImagesOne( $cardInfo, [ 'avatar']);
            return $this->success($cardInfo);
        }
        //是否只需要基础数据======================================================================================================有返回结果==============================


        //默认vr数据
        if(!isset($cardInfo['vr_tittle']) || empty($cardInfo['vr_tittle']))
        {
            $config = longbingGetAppConfig($this->_uniacid);
            if(!empty($config) && !empty($config['vr_tittle']))
            {
                $cardInfo['vr_tittle'] = $config['vr_tittle'];
            }else{
                $cardInfo['vr_tittle'] = lang('panoramic');
            }
        }

        $config      = longbingGetAppConfig($this->_uniacid);
        //默认背景音乐
        if ( !$cardInfo[ 'bg' ] )
        {
            $cardInfo[ 'bg' ] = $config[ 'default_voice' ];
        }

        if ( !$cardInfo[ 'bg_switch' ] )
        {
            $cardInfo[ 'bg_switch' ] = $config[ 'default_voice_switch' ];
        }
        // 处理图片
        $cardInfo = transImages( $cardInfo, [ 'images' ], ',' );
        $cardInfo = transImagesOne( $cardInfo, [ 'avatar', 'voice', 'my_url', 'my_video', 'my_video_cover', 'bg', 'vr_cover',
                'vr_path' ]
        );

        //判断视频等参数
        $config = longbingGetAppConfig($this->_uniacid);

        //默认视频
        if(isset($config['default_video']) && empty($cardInfo['my_video'])) $cardInfo['my_video'] = $config['default_video'];
        //默认视频图片
        if(isset($config['default_video_cover']) && empty($cardInfo['my_video_cover'])) $cardInfo['my_video_cover'] = $config['default_video_cover'];
        //默认背景音乐
        if(isset($config['default_voice']) && empty($cardInfo['bg'])) $cardInfo['bg'] = $config['default_voice'];
        //VR图片
        if(isset($config['vr_cover']) && empty($cardInfo['vr_cover'])) $cardInfo['vr_cover'] = $config['vr_cover'];
        //默认VR路径
        if(isset($config['vr_path']) && empty($cardInfo['vr_path'])) {
            $cardInfo['vr_path'] = $config['vr_path'];
            //默认VR switch
            $cardInfo['vr_switch'] = $config['vr_switch'];
        }
        //默认VR标题
        if(isset($config['vr_tittle']) && empty($cardInfo['vr_tittle'])) $cardInfo['vr_tittle'] = $config['vr_tittle'];
        //默认视频(处理)
        if ( isset( $cardInfo[ 'my_video' ] ) && $cardInfo[ 'my_video' ] ) $cardInfo[ 'my_video_vid' ] = lbGetTencentVideo( $cardInfo[ 'my_video' ] );
        //设置vr tatile
        if ( isset( $cardInfo[ 'vr_tittle' ] ) && !$cardInfo[ 'vr_tittle' ] )
        {
            $cardInfo[ 'vr_tittle' ] = 'VR全景';
        }

        if(isset($input['is_update'])&&$input['is_update']!=1){
            //新的默认配置模型
            $defult_setting = new DefaultSetting();
            //默认配置数据
            $defult_setting_data = $defult_setting->settingInfo(['uniacid'=>$this->_uniacid]);

            $defult_setting_data = transImages($defult_setting_data,['my_photo_cover']);
            //我的签名
            $cardInfo['desc']   = !empty($cardInfo['desc'])?$cardInfo['desc']:$defult_setting_data['my_sign'];
            //我的照片
            $cardInfo['images'] = !empty($cardInfo['images'])?$cardInfo['images']:$defult_setting_data['my_photo_cover'];
            //我的照片链接
            $cardInfo['my_url'] = !empty($cardInfo['my_url'])?$cardInfo['my_url']:$defult_setting_data['my_photo_link'];
            //个人简介语音
            $cardInfo['voice']  = !empty($cardInfo['voice'])?$cardInfo['voice']:$defult_setting_data['voice_text'];
            //我的语音时长
            $cardInfo['voice_time']  = !empty($cardInfo['voice_time'])?$cardInfo['voice_time']:$defult_setting_data['voice_time'];
        }

        //获取福报
//        $coupon = CardCoupon::where( [ [ 'end_time', '>', time() ], [ 'status', '=', 1 ], [ 'uniacid', '=', $this->_uniacid ] , [ 'number' , '>' , 0]] )
//            ->order( [ 'top' => 'desc', 'id' => 'desc' ] )
//            ->select()
//            ->toArray();

        $coupon_dis    = ['status'=>1,'uniacid'=>$this->_uniacid];

        $coupon_model  = new IndexCoupon();

        $coupon        = $coupon_model->couponListSelect($coupon_dis,$this->getUserId(),$staff_id,100);

        $cardInfo[ 'coupon' ]                  = array();
        $cardInfo[ 'coupon_last_record' ]      = array();
        $cardInfo[ 'coupon_last_record_user' ] = array();
        if ( $coupon )
        {
            foreach ( $coupon as $index => $item )
            {
                $record_list = CardCouponRecord::where( [ [ 'coupon_id', '=', $item[ 'id' ] ],
                    [ 'staff_id', '=', $params[ 'staff_id' ] ],
                    [ 'uniacid', '=', $this->_uniacid ] ,

                ] )
                    ->order( [ 'id' => 'desc' ] )
                    ->select()
                    ->toArray();
                if ( count( $record_list ) < $item[ 'number' ] )
                {
                    $cardInfo[ 'coupon' ] = $item;
                    if ( count( $record_list ) )
                    {
                        $cardInfo[ 'coupon_last_record' ] = $record_list;
                    }
                    break;
                }
            }
        }


        if ( !empty( $cardInfo[ 'coupon_last_record' ] ) )
        {

            foreach ( $cardInfo[ 'coupon_last_record' ] as $index => $item )
            {
//
                $user = longbingGetUser($item[ 'user_id' ] ,$this->_uniacid);
                if ( mb_strlen( $user[ 'nickName' ], 'utf8' ) > 4 )
                {
                    $user[ 'nickName' ] = mb_substr( $user[ 'nickName' ], 0, 4, "UTF-8" );
                }

                $cardInfo[ 'coupon_last_record' ][ $index ][ 'user_info' ] = $user;
            }

        }

        //数据统计
        $modelCount = new RadarCount();
        list( $viewCount, $thumbCount ) = $modelCount->RadarNumber( $staff_id ,$this->_uniacid);
        $cardInfo[ 'view_count' ]  = $viewCount;
        $cardInfo[ 'thumbCount' ] = $thumbCount;

        //  是否语音点赞
        $checkVoice = CardCount::where( [ [ 'type', '=', 1 ], [ 'user_id', '=', $userId ],
                [ 'to_uid', '=', $params[ 'staff_id' ] ], [ 'sign', '=', 'praise' ] ]
        )
            ->count();
        //  是否给名片点赞
        $checkThumb = CardCount::where( [ [ 'type', '=', 3 ], [ 'user_id', '=', $userId ],
                [ 'to_uid', '=', $params[ 'staff_id' ] ], [ 'sign', '=', 'praise' ] ]
        )
            ->count();
        //递名片
        $radar_model = new RadarCount();
        $share_number = $radar_model->getShareNumber($params['staff_id']);
        if ( $checkVoice )
        {
            $cardInfo[ 'voiceThumbs' ] = 1;
        }
        else
        {
            $cardInfo[ 'voiceThumbs' ] = 0;
        }
        if ( $checkThumb )
        {
            $cardInfo[ 'isThumbs' ] = 1;
        }
        else
        {
            $cardInfo[ 'isThumbs' ] = 0;
        }

        $cardInfo[ 'view_count' ] = $cardInfo[ 'view_count' ] + $cardInfo[ 'view_number' ];
        $cardInfo[ 'thumbCount' ] = $cardInfo[ 'thumbCount' ] + $cardInfo[ 't_number' ];
        $cardInfo[ 'share_number' ] = $share_number;
        $modelTags = new CardTags();
        $cardInfo[ 'tag_list' ] = $modelTags->cardTagList( $params[ 'staff_id' ], $userId, $this->_uniacid );


        //By.jingshuixian
        //监听获取名片展示信息
        $eventCardInfoData  = event("CardInfo" , $params);
        //兼容老数据
        foreach ($eventCardInfoData as $items){

            foreach ($items as $key => $item){
                if( $key  == 'decorate'){  //装修老板兼容
                    $cardInfo['plugin'] = $item ;
                }else{  //默认都放到根节点上 例如: goods_list
                    $cardInfo[$key] = $item ;
                }
            }

        }

        return $this->success( $cardInfo );
    }


    /**
     * @Purpose: 名片信息
     *
     * @Method GET
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function cardInfoV3 ()
    {

        //获取diy的数据
        $diy_data = DiyModel::where([['status', '=', 1],['uniacid', '=', $this->_uniacid]])->find();
        //获取商城的
        $page_data= json_decode($diy_data['page'], true)[1] ?? [];
        //判断有没有diy过
        if(empty($page_data)|| (array_key_exists('list',$page_data) && empty($page_data['list']))){

            $page_data = longbing_default_Page(1);

        }
        //获取用户id
        $userId = (string) $this->getUserId();
        $uniacid = $this->_uniacid;

        //获取必要参数
        $verify = [ 'staff_id' => 'required' ,'is_base' => 0];
        $params = lbGetParamVerify( $this->_param, $verify );
        $input  = $this->_param;
        $staff_id = $params['staff_id'];
        //获取员工数据
        $cardInfo = longbingGetUserCard($staff_id ,$this->_uniacid);
        if(empty($cardInfo) || empty($cardInfo['is_staff'])) return $this->error(lang('card info not found') ,403);
        //判断名片是否拥有职位（没有就默认）
        if ( !$cardInfo[ 'job_name' ] )
        {
            $cardInfo[ 'job_name' ] = lang('not set job');
        }
        //获取名片码
        $card_code_data = ["data" => ["staff_id" => $params['staff_id'] ,"pid" => $userId ,"type" => 4 ,"key" => 1]];
        $qr_path = 'image/' . $this->_uniacid . '/' . 'wxcode/' . md5($this->_uniacid . json_encode($card_code_data ,true)) . '.jpeg';
        if(longbingHasLocalFile($qr_path))
        {
            $cardInfo['qr_path'] = $qr_path;
        }else{
//          $wxcode_data = longbingCreateWxCode($this->_uniacid ,$card_code_data);
//          if(isset($wxcode_data['path']) && !empty($wxcode_data['path'])) $cardInfo['qr_path'] = $wxcode_data['path'];
            $push_data = array(
                'action'     => 'longbingCreateWxCode',
                'event'      => 'longbingCreateWxCode',
                'uniacid'    => $this->_uniacid,
                'data'       => $card_code_data,
                'page'    => '',
                'type'    => 3
            );
            publisher(json_encode($push_data ,true));

            $cardInfo['qr_path'] = $qr_path;

        }

        if(!empty($cardInfo['qr_path'])) $cardInfo = transImagesOne($cardInfo ,['qr_path']);

        $modelCompany = new Company();

        //检查公司默认数据
        if ( empty($cardInfo['company_info']) )
        {
            //jingshuixian 0 =  $cardInfo['fans_id']
            $company = $modelCompany->getInfo( $this->_uniacid, $cardInfo['fans_id'], 0 );

            $cardInfo[ 'company_info' ] = $company;
            if(isset($company['name'])) $company_name = $company['name'];
        }
        $cardInfo[ 'company_info' ] = $modelCompany->changeTopName($cardInfo[ 'company_info' ]);

        $cardInfo['company_name']   = !empty($cardInfo[ 'company_info' ]['name'])? $cardInfo[ 'company_info' ]['name']:$cardInfo['company_name'];

        //设置分享数据
        if(isset($cardInfo['share_text']) && !empty($cardInfo['share_text']))
        {
            $share_text = $cardInfo['share_text'];

            if(strpos($share_text,'#公司#')) $share_text = str_replace('#公司#',$cardInfo[ 'company_name' ],$share_text);

            if(strpos($share_text,'#职务#')) $share_text = str_replace('#职务#',$cardInfo[ 'job_name' ],$share_text);

            if(strpos($share_text,'#我的名字#')) $share_text = str_replace('#我的名字#',$cardInfo[ 'name' ],$share_text);

            if(strpos($share_text,'$company')) $share_text = str_replace('$company',$cardInfo[ 'company_name' ],$share_text);

            if(strpos($share_text,'$job')) $share_text = str_replace('$job',$cardInfo[ 'job_name' ],$share_text);

            if(strpos($share_text,'$name')) $share_text = str_replace('$name',$cardInfo[ 'name' ],$share_text);
            $cardInfo['share_text'] = $share_text;
        }else{

//            $cardInfo['share_text'] = lang("card share text");
            $cardInfo['share_text'] = '您好，我是'.$cardInfo[ 'company_name' ].'的'.$cardInfo[ 'job_name' ].$cardInfo[ 'name' ].',请惠存';
        }
        //名片分享链接
        $cardInfo['share_img'] = "images/share_img/{$uniacid}/share-{$staff_id}.png";
        // var_dump($cardInfo['share_img']);die;
        if(!longbingHasLocalFile($cardInfo['share_img'])) {
            $user['share_img'] = null;
            $cardInfo['share_img'] = null;
        }else{
            $cardInfo = transImagesOne($cardInfo, ['share_img']);
        }




        //更新最后查看信息
        //By.jingshuixian 最后访问的员工ID不同是更新
//        if(!in_array($userId, [$params[ 'staff_id' ]]))
//        {
        $user_update = User::update( [ 'last_staff_id' => $params[ 'staff_id' ] ], [ 'id' => $userId] );
        if(!empty($user_update)){
            $user = longbingGetUser($userId ,$this->_uniacid);
            $user['last_staff_id']  = $params[ 'staff_id' ];
            longbingSetUser($userId ,$this->_uniacid ,$user);
        }
//        }


        //是否只需要基础数据======================================================================================================有返回结果==============================
        if(isset($params['is_base']) && !empty($params['is_base']))
        {
            $cardInfo = transImagesOne( $cardInfo, [ 'avatar']);
            return $this->success($cardInfo);
        }
        //是否只需要基础数据======================================================================================================有返回结果==============================


        //默认vr数据
        if(!isset($cardInfo['vr_tittle']) || empty($cardInfo['vr_tittle']))
        {
            $config = longbingGetAppConfig($this->_uniacid);
            if(!empty($config) && !empty($config['vr_tittle']))
            {
                $cardInfo['vr_tittle'] = $config['vr_tittle'];
            }else{
                $cardInfo['vr_tittle'] = lang('panoramic');
            }
        }

        $config      = longbingGetAppConfig($this->_uniacid);
        //默认背景音乐
        if ( !$cardInfo[ 'bg' ] )
        {
            $cardInfo[ 'bg' ] = $config[ 'default_voice' ];
        }

        if ( !$cardInfo[ 'bg_switch' ] )
        {
            $cardInfo[ 'bg_switch' ] = $config[ 'default_voice_switch' ];
        }
        // 处理图片
        $cardInfo = transImages( $cardInfo, [ 'images' ], ',' );
        $cardInfo = transImagesOne( $cardInfo, [ 'avatar', 'voice', 'my_url', 'my_video', 'my_video_cover', 'bg', 'vr_cover',
                'vr_path' ]
        );

        //判断视频等参数
        $config = longbingGetAppConfig($this->_uniacid);

        //默认视频
        if(isset($config['default_video']) && empty($cardInfo['my_video'])) $cardInfo['my_video'] = $config['default_video'];
        //默认视频图片
        if(isset($config['default_video_cover']) && empty($cardInfo['my_video_cover'])) $cardInfo['my_video_cover'] = $config['default_video_cover'];
        //默认背景音乐
        if(isset($config['default_voice']) && empty($cardInfo['bg'])) $cardInfo['bg'] = $config['default_voice'];
        //VR图片
        if(isset($config['vr_cover']) && empty($cardInfo['vr_cover'])) $cardInfo['vr_cover'] = $config['vr_cover'];
        //默认VR路径
        if(isset($config['vr_path']) && empty($cardInfo['vr_path'])) {
            $cardInfo['vr_path'] = $config['vr_path'];
            //默认VR switch
            $cardInfo['vr_switch'] = $config['vr_switch'];
        }
        //默认VR标题
        if(isset($config['vr_tittle']) && empty($cardInfo['vr_tittle'])) $cardInfo['vr_tittle'] = $config['vr_tittle'];
        //默认视频(处理)
        if ( isset( $cardInfo[ 'my_video' ] ) && $cardInfo[ 'my_video' ] ) $cardInfo[ 'my_video_vid' ] = lbGetTencentVideo( $cardInfo[ 'my_video' ] );
        //设置vr tatile
        if ( isset( $cardInfo[ 'vr_tittle' ] ) && !$cardInfo[ 'vr_tittle' ] )
        {
            $cardInfo[ 'vr_tittle' ] = 'VR全景';
        }

        if(empty($input['is_update'])){
            //新的默认配置模型
            $defult_setting = new DefaultSetting();
            //默认配置数据
            $defult_setting_data = $defult_setting->settingInfo(['uniacid'=>$this->_uniacid]);

            $defult_setting_data = transImages($defult_setting_data,['my_photo_cover']);
            //我的签名
            $cardInfo['desc']   = !empty($cardInfo['desc'])?$cardInfo['desc']:$defult_setting_data['my_sign'];
            //我的照片
            $cardInfo['images'] = !empty($cardInfo['images'])?$cardInfo['images']:$defult_setting_data['my_photo_cover'];
            //我的照片链接
            $cardInfo['my_url'] = !empty($cardInfo['my_url'])?$cardInfo['my_url']:$defult_setting_data['my_photo_link'];
            //个人简介语音
            $cardInfo['voice']  = !empty($cardInfo['voice'])?$cardInfo['voice']:$defult_setting_data['voice_text'];
            //我的语音时长
            $cardInfo['voice_time']  = !empty($cardInfo['voice_time'])?$cardInfo['voice_time']:$defult_setting_data['voice_time'];
        }

        //获取福报
//        $coupon = CardCoupon::where( [ [ 'end_time', '>', time() ], [ 'status', '=', 1 ], [ 'uniacid', '=', $this->_uniacid ] , [ 'number' , '>' , 0]] )
//            ->order( [ 'top' => 'desc', 'id' => 'desc' ] )
//            ->select()
//            ->toArray();

        $coupon_model  = new IndexCoupon();
        //循环diy获取优惠券样式
        foreach ($page_data['list'] as &$value){

            if($value['type']=='couponList'&&empty($value['data']['dataList'])) {

                $coupon_dis = ['status'=>1,'uniacid'=>$this->_uniacid];

                $dataList   = $coupon_model->couponListSelect($coupon_dis,$this->getUserId(),$staff_id);

                if(!empty($dataList)){
                    //列表样式
                    if($value['data']['type']==2){

                        $value['data']['dataList'] = $dataList;

                        $cardInfo['coupon'] = $dataList;

                    }else{
                        //弹窗样式
                        $cardInfo['coupon'] = $dataList[0];
                        //领取记录
                        $r_dis = [
                            //优惠券id
                            'coupon_id' => $dataList[0]['id'],
                            //员工
                            'staff_id'  => $staff_id,
                            //uniacid
                            'uniacid'   => $this->_uniacid
                        ];
                        $cardInfo['coupon_last_record'] = CardCouponRecord::where($r_dis)->order('id desc')->limit(3)->select()->toArray();

                        if ( !empty( $cardInfo[ 'coupon_last_record' ] ) )
                        {

                            foreach ( $cardInfo[ 'coupon_last_record' ] as $index => $item )
                            {
//
                                $user = longbingGetUser($item[ 'user_id' ] ,$this->_uniacid);
                                if ( mb_strlen( $user[ 'nickName' ], 'utf8' ) > 4 )
                                {
                                    $user[ 'nickName' ] = mb_substr( $user[ 'nickName' ], 0, 4, "UTF-8" );
                                }

                                $cardInfo[ 'coupon_last_record' ][ $index ][ 'user_info' ] = $user;
                            }

                        }
                    }
                }

            }
        }
        //数据统计
        $modelCount = new RadarCount();
        list( $viewCount, $thumbCount ) = $modelCount->RadarNumber( $staff_id ,$this->_uniacid);
        $cardInfo[ 'view_count' ]  = $viewCount;
        $cardInfo[ 'thumbCount' ] = $thumbCount;

        //  是否语音点赞
        $checkVoice = CardCount::where( [ [ 'type', '=', 1 ], [ 'user_id', '=', $userId ],
                [ 'to_uid', '=', $params[ 'staff_id' ] ], [ 'sign', '=', 'praise' ] ]
        )
            ->count();
        //  是否给名片点赞
        $checkThumb = CardCount::where( [ [ 'type', '=', 3 ], [ 'user_id', '=', $userId ],
                [ 'to_uid', '=', $params[ 'staff_id' ] ], [ 'sign', '=', 'praise' ] ]
        )
            ->count();
        //递名片
        $radar_model = new RadarCount();
        $share_number = $radar_model->getShareNumber($params['staff_id']);
        if ( $checkVoice )
        {
            $cardInfo[ 'voiceThumbs' ] = 1;
        }
        else
        {
            $cardInfo[ 'voiceThumbs' ] = 0;
        }
        if ( $checkThumb )
        {
            $cardInfo[ 'isThumbs' ] = 1;
        }
        else
        {
            $cardInfo[ 'isThumbs' ] = 0;
        }



        $cardInfo[ 'view_count' ] = $cardInfo[ 'view_count' ] + $cardInfo[ 'view_number' ];
        $cardInfo[ 'thumbCount' ] = $cardInfo[ 'thumbCount' ] + $cardInfo[ 't_number' ];
        $cardInfo[ 'share_number' ] = $share_number;
        $modelTags = new CardTags();
        $cardInfo[ 'tag_list' ] = $modelTags->cardTagList( $params[ 'staff_id' ], $userId, $this->_uniacid );


        //By.jingshuixian
        //监听获取名片展示信息
        $eventCardInfoData  = event("CardInfo" , $params);

        //兼容老数据
        foreach ($eventCardInfoData as $items){

            foreach ($items as $key => $item){
                if( $key  == 'decorate'){  //装修老板兼容
                    $cardInfo['plugin'] = $item ;
                }else{  //默认都放到根节点上 例如: goods_list
                    $cardInfo[$key] = $item ;
                }
            }

        }
        $arr_data['card_info'] = $cardInfo;

        $arr_data['list']      = $page_data['list'];

        return $this->success( $arr_data );
    }
    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-03-18 16:11
     * @功能说明:默认配置
     */
    public function defaultSetting(){

        $d_model = new DefaultSetting();

        $data  = $d_model->settingInfo(['uniacid'=>$this->_uniacid]);

        return $this->success( $data );
    }
    /**
     * 获取统计数据
     */

    public function getCardCount()
    {
        //获取用户id
        $userId = $this->getUserId();
        $uniacid = $this->_uniacid;
        //获取必要参数
        $verify = [ 'staff_id' => 'required' ];
        $params = lbGetParamVerify( $this->_param, $verify );
        $staff_id = $params['staff_id'];
        //获取员工数据
        $cardInfo_default = longbingGetUserCard($staff_id ,$this->_uniacid);
        if(empty($cardInfo_default) || empty($cardInfo_default['is_staff'])) return $this->success([]);
        $cardInfo=[];


        //  名片最近浏览情况
        $modelCount = new RadarCount();
        list( $cardInfo[ 'view_list' ] ) = $modelCount->getCardViewInfo( $params[ 'staff_id' ], $this->_uniacid );
        return $this->success($cardInfo);
    }
    /**
     * @Purpose: 编辑自我描述
     *
     * @Method POST
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function editDesc ()
    {
        $userId = $this->getUserId();

        $verify = [ 'desc' => '' ];

        $params = lbGetParamVerify( $this->_input, $verify );

        $card = UserInfo::where( [ [ 'fans_id', '=', $userId ], [ 'is_staff', '=', 1 ], [ 'uniacid', '=', $this->_uniacid ] ] )
            ->field( [ 'desc' ] )
            ->find();

        if ( !$card )
        {
            $this->error( 'card info not found', 402 );
        }

        $card->desc = $params[ 'desc' ] ?? '';
        $result     = $card->save();

        if ( $result === false )
        {
            $this->error( 'edit fail', 402 );
        }else{
            //清除名片缓存
            $key = 'longbing_card_info_' . $userId;
            delCache($key ,$this->_uniacid);
        }
        longbingGetUserInfo($userId ,$this->_uniacid ,true);
        return $this->success( [] );
    }

    /**
     * @Purpose: 编辑名片详情图片
     *
     * @Method POST
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function editImages ()
    {
        $userId = $this->getUserId();

        $verify = [ 'images' => 'required' ];

        $params = lbGetParamVerify( $this->_input, $verify );

        $card = UserInfo::where( [ [ 'fans_id', '=', $userId ], [ 'is_staff', '=', 1 ], [ 'uniacid', '=', $this->_uniacid ] ] )
            ->field( [ 'images' ] )
            ->find();

        if ( !$card )
        {
            $this->error( 'card info not found', 402 );
        }


        $params[ 'images' ] = implode(',', $params[ 'images' ]);


        $card->images = trim( $params[ 'images' ], ',' );
        $result       = $card->save();

        if ( $result === false )
        {
            $this->error( 'edit fail', 402 );
        }else{
            //清除名片缓存
            $key = 'longbing_card_info_' . $userId;
            delCache($key ,$this->_uniacid);
        }
        longbingGetUserInfo($userId ,$this->_uniacid ,true);
        return $this->success( [] );
    }

    /**
     * @Purpose: 创建 / 修改名片
     *
     * @Method POST
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function createCard ()
    {
        $userId  = $this->getUserId();
        $uniacid = $this->_uniacid;

        $verify = [ 'avatar'     => '', 'name' => 'required', 'job_id' => 'required', 'phone' => 'required', 'email' => '',
            'company_id' => 'required', 'auth_code' => '', 'ww_account' => '', 'telephone' => '', 'wechat' => '' ];

        $params = lbGetParamVerify( $this->_input, $verify );
        //微信库类
        $service_model = new WxSetting($this->_uniacid);
        //违禁词检查
        $rest          = $service_model->wxContentRlue($params['name']);

        if($rest['errcode'] != 0){

            return $this->error('内容含有违法违规内容');
        }


        $modelConfig = new Config();

//      $config = $modelConfig->getConfig( $this->_uniacid );
        //获取配置信息
        $config = longbingGetAppConfig($this->_uniacid);

        $defaultCode = $config[ 'code' ];

        $companyInfo = $this->modelCompany->getInfo( $this->_uniacid, 0, $params[ 'company_id' ] );

        if ( !$companyInfo )
        {
            return $this->error( lang('not the company') );
        }

        $modelCompany= new Company();

        $companyInfo = $modelCompany->changeTopName($companyInfo);
        //获取工作信息
        $job_model = new CardJob();
        $job = $job_model->getJob(['uniacid' => $uniacid ,'id' => $params['job_id']]);
        if(empty($job)) return $this->error( lang('not the job') );
        $companyCode = $companyInfo[ 'auth_code' ];

        //  是否需要免审口令
        $code = '';
        if ( $companyCode )
        {
            $code = $companyCode;
        }
        if ( !$code && $defaultCode )
        {
            $code = $defaultCode;
        }


        $modelUserInfo = new UserInfo();
        $userInfo      = $modelUserInfo->where( [ [ 'fans_id', '=', $userId ], [ 'uniacid', '=', $this->_uniacid ] ] )
            ->find();
        $user          = longbingGetUser($userId ,$this->_uniacid);
        if ( $code && $code != $params[ 'auth_code' ] && empty($user['is_staff']))
        {
            if(isset($params['auth_code']) && !empty($params['auth_code']))
            {
                if(isset($config['btn_code_err']) && !empty($config['btn_code_err']))
                {
                    return $this->error($config['btn_code_err'] ,402);
                }
            }else{
                if(isset($config['btn_code_miss']) && !empty($config['btn_code_miss']))
                {
                    return $this->error($config['btn_code_miss'] ,402);
                }
            }
            return $this->error(lang('code error') ,402);
        }


        //  编辑
        if(empty($userInfo) || empty($userInfo['is_staff']))
        {
            $permissions = longbingGetPluginAuth($this->_uniacid);
            if(!empty($permissions) && isset($permissions['card_number']) && !empty($permissions['card_number']) && !(longbingGetCardNum($this->_uniacid) < $permissions['card_number']))
            {
                return $this->error(lang('not card num'));
            }
        }
        if ( $userInfo )
        {
            $userInfo->avatar     = $params[ 'avatar' ];
            $userInfo->name       = $params[ 'name' ];
            $userInfo->job_id     = $params[ 'job_id' ];
            $userInfo->phone      = $params[ 'phone' ];
            $userInfo->email      = $params[ 'email' ];
            $userInfo->company_id = $params[ 'company_id' ];
            $userInfo->ww_account = $params[ 'ww_account' ];
            $userInfo->telephone  = $params[ 'telephone' ];

            $userInfo->wechat     = !empty($params[ 'wechat' ])?$params[ 'wechat' ]:'';

            $userInfo->is_staff   = 1;

            User::update( [ 'is_staff' => 1 ], [ 'id' => $userId ] );

            $result = $userInfo->save();
            //清除名片缓存
            $key = 'longbing_card_info_' . $userId;
            delCache($key ,$this->_uniacid);
        }
        //  创建
        else
        {


            $modelUserInfo             = new UserInfo();
            $modelUserInfo->uniacid    = $this->_uniacid;
            $modelUserInfo->fans_id    = $userId;
            $modelUserInfo->avatar     = $params[ 'avatar' ];
            $modelUserInfo->name       = $params[ 'name' ];
            $modelUserInfo->job_id     = $params[ 'job_id' ];
            $modelUserInfo->phone      = $params[ 'phone' ];
            $modelUserInfo->email      = $params[ 'email' ];
            $modelUserInfo->company_id = $params[ 'company_id' ];
            $modelUserInfo->ww_account = $params[ 'ww_account' ];
            $modelUserInfo->telephone  = $params[ 'telephone' ];
            $modelUserInfo->wechat     = $params[ 'wechat' ];
            $modelUserInfo->is_staff   = 1;
            $modelUserInfo->auto_count = longbingGetUserInfoMinAutoCount($this->_uniacid);;
            $modelUserInfo->is_default = 1;
            User::update( [ 'is_staff' => 1 ], [ 'id' => $userId ] );
//          if ( $is_staff )
//          {
//              User::update( [ 'is_staff' => $is_staff ], [ 'id' => $userId ] );
//          }
            $result = $modelUserInfo->save();

        }


        //By.jingshuixian   判断是否拥有分公司的boss权限
        $permissions = new  PermissionBoss($uniacid);
        //有分公司权限  自动绑定当前公司
        if( $permissions->pAuth() ) {
            //如果没有Boss信息就绑定一个
            $bossData = CardBoss::where( [ [ 'user_id', '=', $userId ] , ['uniacid' , '=' , $this->_uniacid  ] ] )->find();
            if(empty($bossData)){
                CardBoss::create( [ 'user_id' => $userId , 'boss' => $params[ 'company_id' ], 'uniacid' => $this->_uniacid ] );
            }
        }


        if ( $result === false )
        {
            return $this->error( 'operation failed' );
        }else{
            //画像
            $file_path = "images/share_img/{$uniacid}/share-{$userId}.png";
//          longbingchmodr(FILE_UPLOAD_PATH);
            if(longbingHasLocalFile($file_path)) unlink(FILE_UPLOAD_PATH . $file_path);
            $params = transImagesOne($params ,['avatar']);
            $gData = array(
                'company_logo' => $companyInfo['logo'],
                'company_name' => longbingSortStr ( $companyInfo['name'] ,10),
                'name'         => longbingSortStr ( $params[ 'name' ] ,10),
                'job'          => longbingSortStr ( $job['name'] ,10),
                'phone'        => longbingSortStr ( $params[ 'phone' ] ,12),
                'email'        => longbingSortStr ( $params[ 'email' ] ,18),
                'address'      => longbingSortStr ( $companyInfo['addr'] ,10),
                'img'          => $params['avatar']
            );
            if(empty($gData['company_logo'])) $gData['company_logo'] = $this->defaultImage['image'];
            if(empty($gData['img'])) $gData['img'] = $this->defaultImage['avatar'];

            try{
//              longbingCreateSharePng( $gData, $userId, $uniacid );
                $push_data = array(
                    'action'     => 'longbingCreateSharePng',
                    'event'      => 'longbingCreateSharePng',
                    'gData'      => $gData,
                    'user_id'    => $userId,
                    'uniacid'    => $uniacid
                );
                publisher(json_encode($push_data ,true));
//          longbingCreateWxCode($data['gData'] ,$data['user_id'] ,$data['uniacid']);
            }catch (Exception $e) { }
        }
        longbingGetUser($userId ,$this->_uniacid ,true);
        longbingGetUserInfo($userId ,$this->_uniacid ,true);
        return $this->success( [] );
    }

    /**
     * @Purpose: 点赞 / 取消点赞 名片 / 语音
     *
     * @Param: $type number 操作类型 0 = 名片 1 = 语音
     *
     * @Method POST
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function thumbStaff ()
    {
        $userId = $this->getUserId();

        $verify = [ 'staff_id' => 'required', 'type' => 0 ];

        $params = lbGetParamVerify( $this->_input, $verify );

        if ( $params[ 'type' ] == 0 )
        {
            //  是否给名片点赞
            $check = CardCount::where( [ [ 'type', '=', 3 ], [ 'user_id', '=', $userId ],
                    [ 'to_uid', '=', $params[ 'staff_id' ] ], [ 'sign', '=', 'praise' ] ]
            )
                ->find();
            $type  = 3;
        }
        else
        {
            //  是否语音点赞
            $check = CardCount::where( [ [ 'type', '=', 1 ], [ 'user_id', '=', $userId ],
                    [ 'to_uid', '=', $params[ 'staff_id' ] ], [ 'sign', '=', 'praise' ] ]
            )
                ->find();
            $type  = 1;
        }


        if ( $check )
        {
            $check  = $check->toArray();
            $result = CardCount::where( [ [ 'id', '=', $check[ 'id' ] ] ] )
                ->delete();
        }
        else {


            $result = CardCount::create( [ 'type' => $type, 'user_id' => $userId, 'to_uid' => $params[ 'staff_id' ],
                    'sign' => 'praise', 'uniacid' => $this->_uniacid ]
            );
        }


        return $this->success( [] );
    }

    /**
     * @Purpose: 点赞 / 取消点赞 印象标签
     *
     * @Method POST
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function thumbTag ()
    {
        $userId = $this->getUserId();

        $verify = [ 'tag_id' => 'required' ];

        $params = lbGetParamVerify( $this->_input, $verify );

        $check = CardUserTags::where( [ [ 'user_id', '=', $userId ], [ 'tag_id', '=', $params[ 'tag_id' ] ] ] )
            ->find();

        if ( $check )
        {
            CardUserTags::where( [ [ 'id', '=', $check[ 'id' ] ] ] )
                ->delete();
            CardTags::where( [ [ 'id', '=', $check[ 'tag_id' ] ] ] )
                ->dec( 'count' )
                ->update();
        }
        else
        {
            CardUserTags::create( [ 'user_id' => $userId, 'tag_id' => $params[ 'tag_id' ], 'uniacid' => $this->_uniacid ] );
            CardTags::where( [ [ 'id', '=', $params[ 'tag_id' ] ] ] )
                ->inc( 'count' )
                ->update();
        }


        return $this->success( [] );
    }

    /**
     * @Purpose: 收集formId
     *
     * @Method POST
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function getFormIdFromMini ()
    {
        $userId = $this->getUserId();

        $verify = [ 'form_id' => 'required' ];

        $params = lbGetParamVerify( $this->_input, $verify );

        $data = explode( ',', $params[ 'form_id' ] );
        $insertData = [];
        foreach ( $data as $index => $item )
        {
            if ($item !== 'the formId is a mock one')
            {
                $insertData[] = [ 'user_id' => $userId, 'formId' => $item, 'uniacid' => $this->_uniacid ];
            }
        }
        if(empty($insertData)) return $this->success( [] );
        $push_data = array(
            'action'     => 'longbingSaveFormId',
            'event'      => 'longbingSaveFormId',
            'data'       => $insertData
        );
        publisher(json_encode($push_data ,true));

        return $this->success( [] );
    }

    /**
     * @Purpose: 上报手机号
     *
     * @Method POST
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function reportPhone ()
    {
        $userId = $this->getUserId();


        $verify = [ 'staff_id' => 'required', 'encryptedData' => 'required', 'iv' => 'required' ];

        $params = lbGetParamVerify( $this->_input, $verify );

        $staff_id      = $params[ 'staff_id' ];
        $uniacid       = $this->_uniacid;
        $encryptedData = $params[ 'encryptedData' ];
        $iv            = $params[ 'iv' ];

        $info = UserPhone::where( [ [ 'user_id', '=', $userId ], [ 'uniacid', '=', $uniacid ] ] )
            ->find();

        if ( $info )
        {
            return $this->success( [ 'phone' => $info[ 'phone' ], 'new' => 3, 'iv' => $iv ] );
        }


        $config    = longbingGetAppConfig($this->_uniacid);
        // var_dump(json_encode($config));die;
        $appid     = $config[ 'appid' ];
        $appsecret = $config[ 'app_secret' ];


        $check_sk = UserSk::where( [ [ 'user_id', '=', $userId ] ] )
            ->find();
        if ( !$check_sk )
        {
            return $this->error( -1, 'need login', [] );
        }
        else
        {
            $session_key = $check_sk[ 'sk' ];
        }


        $data = null;
        //  解密
        $errCode = decryptDataLongbing( $appid, $session_key, $encryptedData, $iv, $data );
//        $errCode = baiduDecryptDataLongbing( $appid, $session_key, $encryptedData, $iv, $data );

        if ( $errCode == 0 )
        {
            $data = json_decode( $data, true );

            $phone = $data[ 'purePhoneNumber' ];

        }
        else
        {
            return $this->error( $errCode );
        }

        $data = [ 'user_id' => $userId, 'to_uid' => $params[ 'staff_id' ], 'phone' => $phone, 'uniacid' => $uniacid ];
        $data = UserPhone::create( $data );
        if(!empty($data))
        {
            $user = longbingGetUser($userId ,$this->_uniacid);
            $user['phone'] = $phone;
            longbingSetUser($userId ,$this->_uniacid ,$user);
        }
        return $this->success( [ 'phone' => $phone, 'new' => 1, ] );
    }


    /**
     * @Purpose: 上报手机号
     *
     * @Method POST
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function baiduReportPhone ()
    {
        $userId = $this->getUserId();

        $verify = [ 'staff_id' => 'required', 'encryptedData' => 'required', 'iv' => 'required' ];

        $params = lbGetParamVerify( $this->_input, $verify );

        $staff_id      = $params[ 'staff_id' ];
        $uniacid       = $this->_uniacid;
        $encryptedData = $params[ 'encryptedData' ];
        $iv            = $params[ 'iv' ];

        $info = UserPhone::where( [ [ 'user_id', '=', $userId ], [ 'uniacid', '=', $uniacid ] ] )
            ->find();

        if ( $info )
        {
            return $this->success( [ 'phone' => $info[ 'phone' ], 'new' => 3, 'iv' => $iv ] );
        }


        $config    = Db::name('longbing_card_baidu_setting')->where(['uniacid'=>$this->_uniacid])->find();
        $appid     = $config[ 'client_id' ];
        $appsecret = $config[ 'client_secret' ];


        $check_sk = UserSk::where( [ [ 'user_id', '=', $userId ] ] )->find();
        if ( !$check_sk )
        {
            return $this->error( -1, 'need login', [] );
        }
        else
        {
            $session_key = $check_sk[ 'sk' ];
        }

        $data = null;
        //  解密
        $errCode = baiduDecryptDataLongbing( $encryptedData,$iv,$appid, $session_key);


        if ( $errCode!=false)
        {
            $errCode = json_decode($errCode,true);
            $phone   = $errCode['mobile'];
        } else {
            return $this->error( $errCode );
        }
        $data = [ 'user_id' => $userId, 'to_uid' => $params[ 'staff_id' ], 'phone' => $phone, 'uniacid' => $uniacid ];
        $data = UserPhone::create( $data );
        if(!empty($data))
        {
            $user = longbingGetUser($userId ,$this->_uniacid);
            $user['phone'] = $phone;
            longbingSetUser($userId ,$this->_uniacid ,$user);
        }
        return $this->success( [ 'phone' => $phone, 'new' => 1, ] );
    }

    /**
     * @Purpose: 编辑标签回显数据
     *
     * @Method GET
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function reviewTags ()
    {
        $userId = $this->getUserId();

        $staffTags = CardTags::where( [ [ 'user_id', '=', $userId ], [ 'uniacid', '=', $this->_uniacid ], [ 'status', '=', 1 ] ] )
            ->field( [ 'id', 'tag', 'count' ] )
            ->select()
            ->toArray();

        $sysTags = CardTags::where( [ [ 'user_id', '=', 0 ], [ 'uniacid', '=', $this->_uniacid ], [ 'status', '=', 1 ] ] )
            ->field( [ 'id', 'tag', 'count' ] )
            ->select()
            ->toArray();

        foreach ( $sysTags as $index => $item )
        {
            $sysTags[ $index ][ 'selected' ] = 0;
            foreach ( $staffTags as $index2 => $item2 )
            {
                if ( $item2[ 'tag' ] == $item[ 'tag' ] )
                {
                    $sysTags[ $index ][ 'selected' ] = 1;
                    break;
                }
            }
        }

        $data[ 'staffTags' ] = $staffTags;
        $data[ 'sysTags' ]   = $sysTags;

        return $this->success( $data );
    }

    /**
     * @Purpose: 编辑标签
     *
     * @Method POST
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function editTags ()
    {
        $userId = $this->getUserId();

        $verify = [ 'tags' => 'required' ];

        $params = lbGetParamVerify( $this->_input, $verify );

        $model = new CardTags();

        $staffTags = $model->where( [ [ 'user_id', '=', $userId ], [ 'uniacid', '=', $this->_uniacid ], [ 'status', '=', 1 ] ] )
            ->field( [ 'id', 'tag', 'count' ] )
            ->select()
            ->toArray();

        $tmpArr = [];

        $updateData = [];

        foreach ( $staffTags as $index => $item )
        {
            $tmpArr[] = $item[ 'tag' ];
            if ( !in_array( $item[ 'tag' ], $params[ 'tags' ] ) )
            {
                $updateData[] = [ 'id' => $item[ 'id' ], 'status' => -1 ];
            }
        }
        if ( !empty( $updateData ) )
        {
            $model->saveAll( $updateData );
        }

        $insertData = [];

        foreach ( $params[ 'tags' ] as $index => $item )
        {
            if ( !in_array( $item, $tmpArr ) )
            {
                $insertData[] = [ 'user_id' => $userId, 'tag' => $item, 'count' => 0, 'uniacid' => $this->_uniacid, 'status' => 1,
                    'top'     => 0 ];
            }
        }
        if ( !empty( $insertData ) )
        {
            $model->saveAll( $insertData );
        }


        return $this->success( [] );
    }
    //收藏名片
    public function collectionCard()
    {
        //获取用户信息
        $userInfo = $this->getUserInfo();
        $userId   = $this->getUserId();
        //获取参数
        $input    = $this->_input;
        if(isset($input['collection'])) $input  =$input['collection'] ;
        //判断员工id是否存在
//        if(!isset($input['staff_id']) || empty($input['staff_id'])) return $this->error(lang('not staff id ,please check param.'));
        if(!isset($input['staff_id']) || empty($input['staff_id'])) return $this->success([]);
        //检查员工是否已被收藏
        $check_filter = array(
            'uniacid' => $this->_uniacid,
            'uid'     => $userId,
            'to_uid'  => $input['staff_id']
        );
        $check = $this->modelCollection->checkCollection($check_filter);
//        var_dump($check);die;
//        if(!empty($check) && isset($check['status']) && in_array($check['status'], ['1' ,1])) return $this->error(lang('the card has been collected ,Please don not double collect'));
        if(!empty($check) && isset($check['status']) && in_array($check['status'], ['1' ,1])) return  $this->success([]);
        //检查员工是否存在
        $staff = longbingGetUserInfo($input['staff_id'] ,$this->_uniacid);
        //检查被收藏的员工是否存在和是否是员工
//        if(!isset($staff['is_staff']) || empty($staff['is_staff'])) return $this->error(lang('the staff is not exist.'));
        if(!isset($staff['is_staff']) || empty($staff['is_staff'])) return $this->success([]); //Update by jingshuixian    $this->success([])
        //判断来源
        if(isset($input['from_uid']))
        {
            $from_user = longbingGetUser($input['from_uid'] ,$this->_uniacid);
            if(!empty($from_user)) $check_filter['from_uid'] = $input['from_uid'];
        }
        //判断encryptedData 和 iv是否存在
        if(isset($input['encryptedData'])){
            if(!isset($input['iv'])) $input['iv'] = '';
            //获取session key
            $sk = longbingGetUserSk($userId ,$this->_uniacid);
            //获取appid
            $appid = null;
            //获取config
            $config  = longbingGetAppConfig($this->_uniacid);
            if(isset($config['appid']) && !empty($config['appid'])) $appid = $config['appid'];
            //判断session key 是否存在
            if(!empty($sk) && !empty($appid)) {
                $data = null;
                $errCode = decryptDataLongbing( $appid, $sk, $input['encryptedData'], $input['iv'], $data );
                //  判断解密是否有错误
                if ( $errCode == 0 )
                {
                    $data    = json_decode( $data, true );
                    $check_filter['openGId'] = $data[ 'openGId' ];
                }
            }
        }
        //判断scene是否存在
        if(isset($input['scene'])) $check_filter['scene'] = $input['scene'];
        //判断type是否存在
        if(isset($input['type'])) $check_filter['type'] = $input['type'];
        //创建收藏关系
        $result = false;
        if(empty($check)){
            $result = $this->modelCollection->createCollection($check_filter);
        }else{
            $check_filter['status'] = 1;
            $result = $this->modelCollection->updateCollection(['id' => $check['id']] ,$check_filter);
        }
        return $this->success([]);
//        return $this->success($result);
    }

    //取消收藏
    public function unCollectionCard()
    {
        //获取用户信息
        $userInfo = $this->getUserInfo();
        $userId   = $this->getUserId();
        //获取参数
        $input    = $this->_input;
        if(isset($input['collection'])) $input  =$input['collection'] ;
        //判断员工id是否存在
        if(!isset($input['staff_id']) || empty($input['staff_id'])) return $this->error(lang('not staff id ,please check param.'));
        //检查员工是否已被收藏
        $check_filter = array(
            'uniacid' => $this->_uniacid,
            'uid'     => $userId,
            'to_uid'  => $input['staff_id']
        );
        $check = $this->modelCollection->checkCollection($check_filter);
        if(empty($check) || !isset($check['status']) || !in_array($check['status'], ['1' ,1])) return $this->error(lang('the card has not been collected ,Please collect'));
        //创建收藏关系
        $result = $this->modelCollection->updateCollection(['id' => $check['id']] ,['status' => 0]);
        return $this->success($result);
    }

    //生成小程序码
    public function getWxCode()
    {
        //获取数据
        $input = null;
        if(isset($this->_input['data'])) $input = $this->_input['data'];
        if(empty($input)) return $this->error('not data ,please check input data.');
        $input['user_id'] = $this->_user_id;
        $result = longbingCreateWxCode($this->_uniacid ,$input);
        return $this->success($result);
    }


    //获取微信小程序码信息
    public function getWxCodeData()
    {
        $code_id = null;
        if(isset($this->_param['code_id'])) $code_id = $this->_param['code_id'];
        if(empty($code_id)) return $this->error('not code id ,please check param.');
        $result = longbingGetWxCode($code_id ,$this->_uniacid , $is_update = false);

        return $this->success($result);
    }



    /**
     * 将线上图片转为本地图片用于前端cavans画图
     */
    public function getImage ()
    {

        $path = $this->_param['path'] ?? 'http://longbing.cncnconnect.com/attachment/image/2/wxcode/8e1cc25f24bd0c10ad238e1ce8b7a2e2.jpeg' ;
        if (!$path ) {
            return $this->error('请传入参数');
        }
//
//        $path     = $_SERVER[ 'QUERY_STRING' ];
//        $position = strpos($path, 'getImage&path=');
//        $sub_str  = substr($path, $position + 14);
//        $path     = urldecode($sub_str);

        //判断类型
        $type_img = getimagesize($path);

        ob_start();

        if ( strpos($type_img[ 'mime' ], 'jpeg') ) {
            $resourch = imagecreatefromjpeg($path);
            imagejpeg($resourch);
        } elseif ( strpos($type_img[ 'mime' ], 'png') ) {
            $resourch = imagecreatefrompng($path);
            imagepng($resourch);
        }
        $content = ob_get_clean();
        imagedestroy($resourch);
        return response($content, 200, [ 'Content-Length' => strlen($content) ])->contentType('image/png');
    }



    /**
     * 剩余通知条数
     * @access public
     * @return json
     */
    public function formIds ()
    {
        global $_GPC, $_W;
        $uid       =  $this->_user_id ?? $_GPC[ 'user_id' ] ?? null;
        $beginTime = mktime( 0, 0, 0, date( 'm' ), date( 'd' ) - 7, date( 'Y' ) );

        $m_form_id = new LongbingCardFromId();
        $count = $m_form_id->where([
            ['uniacid', '=', $this->_uniacid],
            ['user_id', '=', $uid],
            ['create_time', '>', $beginTime]

        ])->count();

        return  $this->success(['count' => $count]);
    }

    //删除录音
    public function clearCardInfoVoice()
    {
        //获取员工信息
        $userId = $this->getUserId();
        $card = longbingGetUserInfo($userId ,$this->_uniacid);
        //判断员工是否存在
        if ( !$card )
        {
            return $this->success([]);
        }
        //清除录音数据
        $result = $this->modelUserInfo->updateUser(['fans_id' => $userId] ,['voice' => null , 'voice_time' => 0]);
        if(!empty($result)) longbingGetUserInfo($userId ,$this->_uniacid ,true);
        return $this->success( [] );
    }

    /**
     * User: chenniang(龙兵科技)
     * Date: 2019-09-26 10:32
     * @return void
     * descption:生成商品二维码
     */
    public function getQr(){
        $input = $this->_input;
        $qr    = getCache($this->getUserId().'-'.$input['type'].'-'.$input['staff_id'].'-'.$input['id']."-qr",$this->_uniacid);
        if(empty($qr)){
            $user = $this->getUserInfo();
            $input['user_id'] = $this->getUserId();
            $input['pid']     = $user['pid'];
            $data = longbingCreateWxCode($this->_uniacid,$input,$input['page']);
            $data = transImagesOne($data ,['qr_path'] ,$this->_uniacid);
            $qr   = $data['qr_path'];
            setCache($this->getUserId().'-'.$input['type'].'-'.$input['staff_id'].'-'.$input['id']."-qr",$qr,3600,$this->_uniacid);
        }
        return $this->success($qr);
    }

    /**
     **@author lichuanming
     * @DataTime: 2020/5/15 10:58
     * @功能说明:判断当前套餐是否过期
     */
    public function authStatus(){
        $user_id = $this->getUserId();
        $is_staff = (new User())->where('id','=',$user_id)->value('is_staff');
        $info = longbing_auth_status($this->_uniacid);
        $info['is_staff'] = $is_staff;
        return $this->success($info);
    }




//    public function

}
