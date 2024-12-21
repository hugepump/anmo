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



use app\admin\info\PermissionAdmin;

/**
 * c端后台菜单
 * @author ArtizanZhang
 * @DataTime: 2019/12/6 18:56
 * Class AdminMenu
 * @package longbingcore\permissions
 */
class  AdminMenu {

    /**
     *  根据权限来返回有权限的菜单
     *
     * @param int $uniacid
     * @return array
     * @author ArtizanZhang
     * @DataTime: 2019/12/6 19:01
     */
    static public function all (int $uniacid) : array {

        $menu_data = longbing_init_info_data('AdminMenu' , 'model');


//        dump($menu_data);exit;
        $denyAdminMenuKeys = self::getAuthList($uniacid);




        //获取权限接口  2019年12月20日09:51:18  By.jingshuixian
        /*
         foreach ($saas_auth_admin_model_list as $key=>$item){


            if($item['auth_is_saas_check'] || $item['auth_platform'] ){
                $permissionPath =  APP_PATH . $key . '/info/Permission.php' ;
                if(file_exists($permissionPath) && require_once($permissionPath)){

                    $permissionClassName = 'app\\' . $key .'\\info\\Permission' ;
                    $permissionClass = new $permissionClassName($uniacid);

                    if($item['auth_is_saas_check']  && $permissionClass->sAuth()   && $item['auth_platform'] && $permissionClass->pAuth() ){
                        $denyAdminMenuKeys[] = $key;
                    }else if ( !$item['auth_is_saas_check']  && $item['auth_platform'] && $permissionClass->pAuth() ) {
                        $denyAdminMenuKeys[] = $key;
                    }
                }

            }else{
                $denyAdminMenuKeys[] = $key;
            }
        }
        */


        //从查找没有权限的adminMenuKey
        /*$denyAdminMenuKeys = [];
        $permissions = config('permissions');
        foreach ($permissions as $permissionClass) {

            //判断一个对象是否为一个类的子类
            if (!is_subclass_of($permissionClass, PermissionAbstract::class)) {
                continue;
            }


            $permission = new $permissionClass($uniacid, 0);
            if (!$permission->pAuth() && !empty($permission->adminMenuKey)) {
                $denyAdminMenuKeys[] = $permission->adminMenuKey;
            }
        }*/

        //返回有权限的菜单
        $rst = [];


        foreach ($menu_data as $k => $menu) {


            if (array_key_exists($k, $denyAdminMenuKeys) ) {
                //装载插件权限  By.jingshuixian
                if($k == 'appstore'){
                    //后去插件所有菜单 需要过滤权限
                    $appMenudataList = longbing_init_info_data('AdminMenu','app');
                    $app = json_decode($menu, true);
                    $children = $app['children'] ;

                    foreach ($appMenudataList as $appKey => $appMenu) {

                        //过滤插件全选,需要验证是否正确
                        if (array_key_exists($appKey, $denyAdminMenuKeys)){


                            $m = json_decode($appMenu, true);

                            if(!empty($m)){
                                foreach ($m as $item ){
                                    $children[] = $item;
                                }
                            }
                        }
                    }

                    //应用中心主菜单

                    $app['children'] =  $children ;

                    $rst[] = $app ;
                }else if($k == 'admin'){

                    $adminMenu = json_decode($menu, true) ;
                    $permission = new PermissionAdmin($uniacid);
                    $pAuthConfig = $permission->getPAuthConfig();
                    if($pAuthConfig && $pAuthConfig['copyright_id'] != 0 ){
                        $children = $adminMenu['children'];
                        foreach ($children as $k => $child) {
                            if ($child['path'] == 'copyright') {
                                unset($children[$k]);
                            }
                        }
                        $adminMenu['children'] = array_values($children);

                        $url = $adminMenu['meta']['subNavName'][1]['url'];
                        unset($url[0]);
                        $adminMenu['meta']['subNavName'][1]['url'] = array_values($url);
                    }

                    $rst[] =  $adminMenu ;

                }else{
                    $rst[] = json_decode($menu, true);
                }


            }
        }




        return  $rst;

    }


    /**
     * 获取所有应用列表权限
     *
     * @param int $uniacid
     * @return array
     * @author shuixian
     * @DataTime: 2019/12/20 13:51
     */
    static public function getAppstoreInfoList (int $uniacid) : array {

        $dataList = longbing_init_info_data('Info','app');

        $denyAdminMenuKeys = self::getAuthList($uniacid);

        $returnList = [] ;

        foreach ($dataList as $key => $item ){

            if(array_key_exists($item['name'], $denyAdminMenuKeys) ){
                $returnList[] = $item ;
            }

        }


        return   $returnList ;

    }


    /**
     * 获得拥有模块/app权限列表
     *
     * @param $uniacid
     * @return array
     * @author shuixian
     * @DataTime: 2019/12/20 13:39
     */
    static public function getAuthList($uniacid,$arr= []){
        $denyAdminMenuKeys  = [] ;

        if(empty($uniacid)){

            return  $denyAdminMenuKeys ;
        }

        $adminModelListInfo = config('app.AdminModelList') ;

        $saas_auth_admin_model_list =  $adminModelListInfo['saas_auth_admin_model_list'];

        if(!empty($saas_auth_admin_model_list)){

            foreach ($saas_auth_admin_model_list as $key=>$item) {

                if(!empty($arr)&&!in_array($key,$arr)){

                    continue;
                }

                $className =  'Permission' . ucfirst($key);
                $permissionPath = APP_PATH . $key . '/info/' . $className . '.php';

                if (file_exists($permissionPath) && require_once($permissionPath)) {

                    $permissionClassName = 'app\\' . $key . '\\info\\'. $className;
                    $permission = new $permissionClassName($uniacid , $item);

                    $denyAdminMenuKeys[$key] = $permission->pAuth();
                }
            }
        }

        return $denyAdminMenuKeys ;
    }

}