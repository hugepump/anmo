<?php

namespace app\card\controller;

use app\ApiRest;
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
use app\radar\model\RadarCount;
use app\shop\model\IndexUserInfo;
use app\shop\model\IndexShopCollage;
use longbingcore\permissions\Tabbar;
use longbingcore\tools\LongbingArr;
use think\App;
use think\facade\Cache;
use think\facade\Db;
use function Qiniu\explodeUpToken;

class IndexV2 extends ApiRest
{

    protected $noNeedLogin = ['configV2'];

    protected $modelUser;
    protected $modelUserInfo;
    protected $modelCollection;
    protected $modelCompany;
    protected $modelConfig;
    protected $app;

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
        $data['tabBar1'] =  Tabbar::all($this->_uniacid, $this->_user_id);

        $pluginAuth = longbingGetPluginAuth($this->_uniacid, $this->_user_id, $auth_info);

        $data = array_merge($data, $pluginAuth);

        $data = LongbingArr::delBykey($data , ['web_manage_meta_config','wx_appid','wx_tplid'
            ,'update_time','create_time','app_secret','appid',
            'aliyun_sms_access_key_id','aliyun_sms_access_key_secret'
            ,'coupon_pass','corpsecret',
            'coupon_pass','order_pwd','mini_template_id']) ;

        $config_model   = new DefaultSetting();
        //默认配置
        $DefaultSetting = $config_model->settingInfo(['uniacid'=>$this->_uniacid],'primaryColor,subColor,share_more');
        //主色
        $data['primaryColor'] = !empty($DefaultSetting['primaryColor'])?$DefaultSetting['primaryColor']:'#19c865';
        //辅色
        $data['subColor']     = !empty($DefaultSetting['subColor'])?$DefaultSetting['subColor']:'#f86c53';

        $data['share_more']   = $DefaultSetting['share_more'];

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-08-25 09:44
     * @功能说明:
     */
    public function configV2(){

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
        //$data['tabBar1'] =  Tabbar::all($this->_uniacid, $this->_user_id);
        $config_model   = new DefaultSetting();
        //默认配置
        $DefaultSetting = $config_model->settingInfo(['uniacid'=>$this->_uniacid],'primaryColor,subColor,share_more');
        //主色
        $data['primaryColor'] = !empty($DefaultSetting['primaryColor'])?$DefaultSetting['primaryColor']:'#19c865';
        //辅色
        $data['subColor']     = !empty($DefaultSetting['subColor'])?$DefaultSetting['subColor']:'#f86c53';

        $data['share_more']   = $DefaultSetting['share_more'];

        return $this->success($data);

    }


}
