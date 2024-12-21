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


namespace app\recommend\info;

use longbingcore\permissions\PermissionAbstract;

/**
 * 商城模块功能权限
 * Class PermissionAppstore
 */
class PermissionRecommend extends PermissionAbstract {

    const tabbarKey = null;
    //后台管理菜单对应key[必填] , 当前模块文件夹名称
    const adminMenuKey = 'recommend';
    public $saasKey ;
    const apiPaths = [];


    public function __construct($uniacid,$infoConfigOptions=[],$saasKey='RECOMMEND')
    {

        $this->saasKey  = longbing_get_auth_prefix($saasKey) ;

        parent::__construct($uniacid, self::tabbarKey, self::adminMenuKey, $this->saasKey, self::apiPaths , $infoConfigOptions);
    }


    /**
     * 返回saas端授权结果
     * @return bool
     */
    public function sAuth(): bool
    {

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







}