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


use app\Common\Rsa2Sign;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\Db;
use think\Model;

/**
 * 权限的抽象类
 * @author ArtizanZhang
 * @DataTime: 2019/12/6 18:56
 * Class PermissionAbstract
 * @package longbingcore\permissions
 */
abstract class  PermissionAbstract
{
    /**
     * @var int|null 小程序底部菜单的key
     */
    public $tabbarKey;

    /**
     * @var string|null   后台菜单的key
     */
    public $adminMenuKey;

    /**
     * @var array 权限关联的apiPaths
     */
    public $apiPaths;

    /**
     * @var string|null saas端授权的key
     */
    public $saasKey;


    /**
     * @var int|null sass端授权的值
     */
    protected $sassValue;

    /**
     * @var int 小程序id
     */
    protected $uniacid;

    /**
     * @var array info控制配置信息
     */
    public $infoConfig = [
        'auth_platform'   => true ,
        'auth_is_platform_check' =>true ,
        'auth_is_saas_check' => true ,
    ];

    public $info = [];

    static public $pAuthConfig;


    public function __construct(int $uniacid, ?int $tabbarKey, ?string $adminMenuKey, ?string $saasKey, ?array $apiPaths , $infoConfigOptions = [])
    {
        $this->tabbarKey = $tabbarKey;
        $this->adminMenuKey = $adminMenuKey;
        $this->saasKey = $saasKey;
        $this->apiPaths = $apiPaths;
        $this->uniacid = $uniacid;

        if(!empty($infoConfigOptions)){
            $this->infoConfig = array_merge($this->infoConfig , $infoConfigOptions);
        }else{
            //自动从全局Info里获取
            $adminModelListInfo = config('app.AdminModelList') ;
            $saas_auth_admin_model_list =  $adminModelListInfo['saas_auth_admin_model_list'];
            if(array_key_exists($this->adminMenuKey , $saas_auth_admin_model_list)){
                $this->infoConfig = array_merge($this->infoConfig , $saas_auth_admin_model_list[$this->adminMenuKey]);
            }
        }

        $this->info = $this->getModelInfo($this->adminMenuKey);

        $this->sassValue = $this->getAuthVaule($this->saasKey );
    }

    /**
     * 获取当前模块/app配置的info信息
     *
     * @param $model_name
     * @return array|mixed
     * @author shuixian
     * @DataTime: 2019/12/27 17:47
     */
    public function getModelInfo($model_name){

        //导入info信息查看
        $infoDataPath =  APP_PATH . $model_name . '/info/Info.php' ;
        $infoData = [] ;
        if(file_exists($infoDataPath)){
            $infoData =  include $infoDataPath ;
        }
        return $infoData ;
    }

    /**
     * 返回saas端授权结果
     *
     * @return bool
     * @author ArtizanZhang
     * @DataTime: 2019/12/6 18:57
     */
    abstract public function sAuth(): bool;

    /**
     * 返回p端授权结果
     *
     * @return bool
     * @author ArtizanZhang
     * @DataTime: 2019/12/6 18:57
     */
    abstract public function pAuth();

    /**
     * 返回c端授权结果
     *
     * @param int $user_id
     * @return bool
     * @author ArtizanZhang
     * @DataTime: 2019/12/9 17:13
     */
    abstract public function cAuth(int $user_id): bool;




    /**
     * 返回saasValue
     *
     * @return int
     * @author ArtizanZhang
     * @DataTime: 2019/12/6 18:58
     */
    public function getSaasValue(): int
    {
        return $this->sassValue;
    }


    /**
     * 返回当前实例
     *
     * @param int $uniacid
     * @return PermissionAbstract
     * @author ArtizanZhang
     * @DataTime: 2019/12/9 10:59
     */
    static function this (int $uniacid) : self {
        return  new static($uniacid);
    }


    /**
     * 获取p端的权限配置
     *
     * @return array|null
     * @author ArtizanZhang
     * @DataTime: 2019/12/9 14:22
     */
    public function getPAuthConfig(): ?array
    {
        if (isset(self::$pAuthConfig[$this->uniacid])) {
            return self::$pAuthConfig[$this->uniacid];
        }

        try {
            $cardauth2_config_exist = Db::query('show tables like "%longbing_cardauth2_config%"');
            if (empty($cardauth2_config_exist)) {
                return null;
            }
            $pAuthConfig = Db::name('longbing_cardauth2_config')->where([['modular_id', '=', $this->uniacid]])->find();
        } catch (DataNotFoundException $notFoundException) {
            return null;
        } catch (ModelNotFoundException $modelNotFoundException) {
            return null;
        } catch (DbException $exception) {
            return null;
        }

        self::$pAuthConfig[$this->uniacid] = $pAuthConfig;
        return $pAuthConfig;
    }

    /**
     * 根据saasAuthKey获得值
     *
     * @param $saasAuthKey
     * @return int|null
     * @author shuixian
     * @DataTime: 2019/12/19 19:07
     */
    public function getAuthVaule($saasAuthKey , $defaultAuthNumber = -1,$update=0){

//        if($update==1){
//
//            dump(1);exit;
//        }

        $returnNumber = 0 ;
        $auth = SaasAuthConfig::getSAuthConfig($this->uniacid,$update);

        if($auth){
            $authkey =  array_column($auth,1 , 0) ;
        }else{
            $authkey = [];
        }



        if(array_key_exists($saasAuthKey , $authkey)){

            $returnNumber = intval( $authkey[$saasAuthKey] ) ;
            //0 代表无限制数量, 默认给 99999999
            $returnNumber  = $returnNumber == 0 ? 0 : $returnNumber ;

        }else{
            $returnNumber =   $defaultAuthNumber  ;
        }

        /* if($saasAuthKey == 'LONGBING_BAIDU'){

             longbing_dd($auth);

             longbing_dd($saasAuthKey . '=========='.$defaultAuthNumber .'===========' . $this->uniacid);
         }*/


        return $returnNumber ;
    }


    public function getAuthPlatform(){
        return $this->infoConfig['auth_platform'] ;
    }

    public function getAuthIsPlatformCheck(){
        return $this->infoConfig['auth_is_platform_check'] ;
    }

    public function getAuthIsSaasCheck(){
        return $this->infoConfig['auth_is_saas_check'] ;
    }

    /**
     * 获取授权数量
     *
     * @author shuixian
     * @DataTime: 2019/12/19 19:02
     */
    public function getAuthNumber(){

        return $this->getAuthVaule( $this->saasKey);
    }

}