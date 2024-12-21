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


namespace app\shop\info;

use longbingcore\permissions\PermissionAbstract;

/**
 * 商城模块功能权限
 * Class PermissionAppstore
 */
class PermissionShop extends PermissionAbstract {

    const tabbarKey = null;
    //后台管理菜单对应key[必填] , 当前模块文件夹名称
    const adminMenuKey = 'shop';
    public $saasKey ;
    const apiPaths = [];


    public function __construct( $uniacid,$infoConfigOptions = [])
    {

        $this->saasKey  = longbing_get_auth_prefix('AUTH_SHOP') ;
        parent::__construct($uniacid, self::tabbarKey, self::adminMenuKey, $this->saasKey, self::apiPaths , $infoConfigOptions);
    }


    /**
     * 返回saas端授权结果
     * @return bool
     */
    public function sAuth(): bool
    {
        if(!$this->getAuthIsSaasCheck()){
            return  true ;
        }
        return  $this->sassValue == 1 ? true : false;
    }

    /**
     * 返回p端授权结果
     * @return bool
     */
    public function pAuth(): bool
    {
       if (!$this->sAuth()) {
            return  false;
        };

        //代理管理端可以控制商城是否展示权限 , 这里需要判断权限
        $pAuthConfig = $this->getPAuthConfig();
        //必须平台授权才能使用

        //dump($this->getAuthIsPlatformCheck());exit;
        if($this->getAuthIsPlatformCheck()){
            //根据授权而定
            if ($pAuthConfig ){

//                dump($pAuthConfig);exit;
                return $pAuthConfig['shop_switch'] ? true : false ;
            }
        }

        return true;

    }

    /**
     * 返回c端授权结果
     *
     * @param int $user_id
     * @return bool
     * @author ArtizanZhang
     * @DataTime: 2019/12/9 17:13
     */
    public function cAuth(int $user_id): bool
    {

        return true;
    }

    /**
     * 添加商品数量
     *
     * @author shuixian
     * @DataTime: 2019/12/19 19:02
     */
    public function getAddGoodsNumber(){
        return $this->getAuthVaule(  longbing_get_auth_prefix('AUTH_GOODS') , 4);

    }
}