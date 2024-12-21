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


namespace app\card\info;

use app\agent\model\Cardauth2DefaultModel;
use longbingcore\permissions\PermissionAbstract;

/**
 * 模块功能权限
 * Class PermissionAppstore
 */
class PermissionCard extends PermissionAbstract {

    const tabbarKey = null;
    //后台管理菜单对应key[必填] , 当前模块文件夹名称
    const adminMenuKey = 'card';
    public $saasKey ;
    const apiPaths = [];


    public function __construct(int $uniacid,$infoConfigOptions = [])
    {
        $this->saasKey  = longbing_get_auth_prefix('AUTH_CARD') ;
        parent::__construct($uniacid, self::tabbarKey, self::adminMenuKey, $this->saasKey, self::apiPaths , $infoConfigOptions);
    }


    /**
     * 返回saas端授权结果
     * @return bool
     */
    public function sAuth(): bool
    {
        return  true ;
    }

    /**
     * 返回p端授权结果
     * @return bool
     */
    public function pAuth(): bool
    {
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
     * 添加名片数量
     *
     * @author shuixian
     * @DataTime: 2019/12/19 19:02
     */
    public function getAuthNumber()
    {
        $returnAuthNumber = -1;
        //代理管理端控制的数量
        $pAuthConfig    = $this->getPAuthConfig();

        $authCardNumber = $this->getAuthVaule($this->saasKey, 5);

        $pAuthNumber    = isset($pAuthConfig['number']) ? $pAuthConfig['number'] : -1;
        //全局设置配置
        $cardauth2DefaultModel = new Cardauth2DefaultModel();

        $defaultAuthNumber =  $cardauth2DefaultModel->getCardNumber() ;

        if ($authCardNumber > 0) {

            if ($pAuthNumber > 0){

                $returnAuthNumber = $pAuthNumber >= $authCardNumber ? $authCardNumber : $pAuthNumber;

            } else {

                $returnAuthNumber = $authCardNumber;

            }

        }else if( $authCardNumber == 0 ){
            //无限开模式
            if ($pAuthNumber >=0) {

                $returnAuthNumber = $pAuthNumber;

            }else if($defaultAuthNumber >= 0 ){

                $returnAuthNumber = $defaultAuthNumber;

            }else{

                $returnAuthNumber = $pAuthNumber;
            }

        } else {

            $returnAuthNumber = $pAuthNumber;
        }

        return $returnAuthNumber;

    }

}