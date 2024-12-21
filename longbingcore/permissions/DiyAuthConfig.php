<?php
// +----------------------------------------------------------------------
// | Longbing [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright Chengdu longbing Technology Co., Ltd.
// +----------------------------------------------------------------------
// | Website http://longbing.org/
// +----------------------------------------------------------------------
// | Sales manager: +86-13558882532 / +86-13330887474
// | Technical support: +86-15680635005
// | After-sale service: +86-17361005938
// +----------------------------------------------------------------------

declare(strict_types=1);


namespace longbingcore\permissions;

include_once LONGBING_EXTEND_PATH . 'LongbingUpgrade.php';

use app\Common\Rsa2Sign;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\Db;
use think\facade\Env;
use LongbingUpgrade;

/**
 * saas端请求到的权限数据（k-v格式）
 *
 * @author ArtizanZhang
 * @DataTime: 2019/12/9 14:23
 * Class SaasAuthConfig
 * @package longbingcore\permissions
 */
Class DiyAuthConfig {


    static public $sAuthConfig = [];


    /**
     * 获取s端的权限配置
     *
     * @return array|null
     * @author ArtizanZhang
     * @DataTime: 2019/12/9 11:26
     */
    public static function getSAuthConfig (int $uniacid): ?array
    {
        if (isset(self::$sAuthConfig[$uniacid])) {
            return self::$sAuthConfig[$uniacid];
        }

        try {
            $sAuthConfig = self::_getsAuthConfig($uniacid);


            if(empty($sAuthConfig)) {
                $sAuthConfig = [] ;
            }

            self::$sAuthConfig[$uniacid] = $sAuthConfig;

            return $sAuthConfig;
        } catch (\Exception $exception) {

        }

        return null;

    }

    /**
     * 获取saas端的值
     * @param string $server_url
     * @return array|bool|mixed
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     * @author ArtizanZhang
     * @DataTime: 2019/12/6 19:01
     *
     */
    private static function _getsAuthConfig($uniacid, $server_url = 'http://api.longbing.org')
    {

        $app_model_name =  config('app.AdminModelList')['app_model_name'];
        //By.jingshuixian   2019年12月20日16:48:41 优化代码逻辑
        //代理管理端是固定小程
        $uniacid =  $uniacid ? $uniacid : 8888 ;

        $domain_name = $_SERVER['HTTP_HOST'];

        $auth_data   =  getCache('single_checked_auth_'. $app_model_name . $domain_name, $uniacid );

        if (!empty($auth_data)&&!empty($auth_data[0][0]))  {

            return $auth_data;

        }
        //By.jingshuixian   2019年12月20日16:48:41 优化代码逻辑  end

        $goods_name   = config('app.AdminModelList')['app_model_name'];

        $auth_uniacid =  config('app.AdminModelList')['auth_uniacid'];

        $upgrade    = new LongbingUpgrade($auth_uniacid , $goods_name , Env::get('j2hACuPrlohF9BvFsgatvaNFQxCBCc' , false));

        $param_list = $upgrade->getsAuthConfig();

        if (!empty($param_list)) {

            $data = $param_list;

            $auth_data = [];
            //解密
            foreach ($data as $k => $item) {

                $a = explode(':', $item);
                //存入缓存  一天的有效期
                if ($a[0] == 'LONGBING_AUTH_GOODS_SINGLE') {

                    $a[0] = 'LONGBING_AUTH_GOODS';

                }
                $auth_data[] = $a;
            }
            setCache('single_checked_auth_' . $app_model_name . $domain_name, $auth_data,  3600,  $uniacid);

            return $auth_data;

        }
        return null;

    }


    /**
     * 获取s端的权限配置
     *
     * @return array|null
     * @author ArtizanZhang
     * @DataTime: 2019/12/9 11:26
     */
    public static function getSAuConfig (int $uniacid): ?array
    {
        if (isset(self::$sAuthConfig[$uniacid])) {
            return self::$sAuthConfig[$uniacid];
        }

        try {
            $sAuthConfig = self::_getsAuthConfig($uniacid);


            if(empty($sAuthConfig)) {
                $sAuthConfig = [] ;
            }

            self::$sAuthConfig[$uniacid] = $sAuthConfig;

            return $sAuthConfig;
        } catch (\Exception $exception) {

        }

        return null;

    }






}