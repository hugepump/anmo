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

use app\diy\model\DiyModel;
use think\facade\Db;

/**
 * 小程序底部菜单
 * @author ArtizanZhang
 * @DataTime: 2019/12/6 18:56
 * Class Tabbar
 * @package longbingcore\permissions
 */
class Tabbar {

    /**
     * 根据权限来返回有权限的小程序底部菜单
     *
     * @param int $uniacid
     * @param int $user_id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ArtizanZhang
     * @DataTime: 2019/12/10 15:56
     */
    static public function all (int $uniacid, int $user_id) : array {
        $diy =  DiyModel::where([['uniacid', '=', $uniacid], ['status', '=', 1]])->find();
        if (empty($diy)) {
            return  [];
        }
        $diy_tabbar =  json_decode($diy['tabbar'], true);


        //从查找没有权限的tabbarKey
        $denyTabbarKeys = [];
        $permissions = config('permissions');
        foreach ($permissions as $permissionClass) {
            if (!is_subclass_of($permissionClass, PermissionAbstract::class)) {
                continue;
            }

            /**
             * @var PermissionAbstract $permission
             */
            $permission = new $permissionClass($uniacid, $user_id);
            if (!$permission->cAuth($user_id) && !empty($permission->tabbarKey)) {
                $denyTabbarKeys[] = $permission->tabbarKey;
            }
            
        }

        //返回有权限的菜单
        foreach ($diy_tabbar['list'] as $k => $tabbar) {
            if (in_array($tabbar['key'], $denyTabbarKeys)) {
                unset($diy_tabbar['list'][$k]);
            }
        }

        return  $diy_tabbar;
    }


    /**
     * diy时，返回有diy权限的小程序菜单
     *
     * @param int $uniacid
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ArtizanZhang
     * @DataTime: 2019/12/10 15:56
     */
    static public function allForDiySelect (int $uniacid) : array {
        $diy_tabbar = [] ;


        $adminModelListInfo = config('app.AdminModelList') ;
        $saas_auth_admin_model_list =  $adminModelListInfo['saas_auth_admin_model_list'];


        foreach ($saas_auth_admin_model_list as $key=>$item) {

            $className =  'Permission' . ucfirst($key);
            $permissionPath = APP_PATH . $key . '/info/' . $className . '.php';
            if (file_exists($permissionPath) && require_once($permissionPath)) {

                $permissionClassName = 'app\\' . $key . '\\info\\'. $className;
                $permission = new $permissionClassName($uniacid , $item);

                if ( $permission->pAuth() &&  !empty($permission->adminMenuKey)) {

                    $diyTabbarPath = APP_PATH . $key . '/info/DiyTabbar.php';
                    if (file_exists($diyTabbarPath)) {

                        $tabbar = include_once ($diyTabbarPath) ;

                        $diy_tabbar = array_merge($diy_tabbar , $tabbar) ;
                    }
                }
            }
        }

        $data['list'] = $diy_tabbar;
        //默认设置
        $data['color'] = '#5d6268';
        $data['selectedColor'] = '#19c865';
        $data['backgroundColor'] = '#fff';
        $data['borderStyle'] = 'white';

        return  $data;
    }


    /**
     * @param $uniacid
     * @功能说明:获取有权限的默认数据
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-05-15 17:28
     */
    static public function getAuthDefultTabbar($data){

        $url = config('app.AdminModelList')['diy_default_data']['tabbar'];
        //默认配置
        //$url  =  '{"id":1,"uniacid":4,"status":1,"create_time":1578106749,"update_time":1578106749,"list":[{"is_show":1,"key":1,"iconPath":"icon-mingpian","selectedIconPath":"icon-mingpian1","pageComponents":"cardHome","name":"\u540d\u7247","url":"\/pages\/user\/home","url_out":"","jump_way":0},{"key":2,"is_show":1,"iconPath":"icon-shangcheng1","selectedIconPath":"icon-shangcheng","pageComponents":"shopHome","name":"\u5546\u57ce","url":"","url_jump_way":"0","url_out":"","is_delete":false,"bind_compoents":[],"bind_links":[],"page":[]},{"key":3,"is_show":1,"iconPath":"icon-dongtai1","selectedIconPath":"icon-dongtai","pageComponents":"infoHome","name":"\u52a8\u6001","url":"","url_jump_way":"0","url_out":"","is_delete":false,"bind_compoents":[],"bind_links":[],"page":[]},{"key":4,"is_show":1,"iconPath":"icon-guanwang","selectedIconPath":"icon-guanwang1","pageComponents":"websiteHome","name":"\u5b98\u7f51","url":"","url_jump_way":"0","url_out":"","is_delete":false,"bind_compoents":[],"bind_links":[],"page":[]},{"key":20001,"is_show":1,"iconPath":"iconyonghuduangerenzhongxin","selectedIconPath":"iconyonghuduangerenzhongxin1","pageComponents":"","name":"\u4e2a\u4eba\u4e2d\u5fc3","url":"","url_jump_way":"0","url_out":"","is_delete":false,"bind_compoents":["ucenterCompoent"],"bind_links":["case"],"page":[]}],"color":"#5d6268","selectedColor":"#19c865","backgroundColor":"#fff","borderStyle":"white"}';

        $url  = json_decode($url,true);

        foreach ($url['list'] as $k=>$v){

            if(!in_array($v['key'],$data)){

                unset($url['list'][$k]);
            }

        }

        $url['list'] = array_values($url['list']);

        return $url;
    }


    /**
     * @param $uniacid
     * @功能说明:获取有权限的
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-05-15 17:50
     */
    static public function getAuthDefultPage($data){

        $url = config('app.AdminModelList')['diy_default_data']['page'];
        //默认配置
        //$url  =  '{"1":{"list":[]},"2":{"list":[]},"3":{"list":[]},"4":{"list":[]},"20001":{"list":[{"title":"\u7528\u6237\u4fe1\u606f","type":"userInfo","icon":"iconyonghuxinxi","isDelete":false,"addNumber":1,"attr":[{"title":"\u5b57\u4f53\u989c\u8272","type":"ColorPicker","name":"fontColor"},{"title":"\u80cc\u666f\u56fe\u7247","type":"UploadImage","desc":"750*440","name":"bgImage"}],"data":{"nickName":"\u7528\u6237\u6635\u79f0","avatarUrl":"https:\/\/retail.xiaochengxucms.com\/defaultAvatar.png","nickText":"\u66f4\u65b0\u6211\u7684\u4e2a\u4eba\u8d44\u6599","fontColor":"#F9DEAF","bgImage":[{"url":"http:\/\/longbingcdn.xiaochengxucms.com\/admin\/diy\/user_bg.jpg"}]},"id":1578137234868,"compontents":"ucenterCompoent"},{"title":"\u521b\u5efa\u540d\u7247","type":"createCard","icon":"iconchuangjianmingpian","isDelete":false,"addNumber":1,"data":{"createText":"\u521b\u5efa\u6211\u7684\u540d\u7247","createBtn":"\u521b\u5efa\u540d\u7247"},"id":1578137237049,"compontents":"ucenterCompoent"},{"title":"\u8ba2\u5355\u7ba1\u7406","type":"moduleMenuShopOrder","icon":"iconshoporder","isDelete":true,"addNumber":1,"attr":[{"title":"\u6a21\u677f\u540d\u79f0","type":"Switch","name":"isShowTitle"},{"title":"\u9009\u62e9\u6a21\u677f","type":"ChooseModule","name":"module","data":[{"title":"\u4e00\u884c\u591a\u5217","name":"module-menu-row","img":"http:\/\/longbingcdn.xiaochengxucms.com\/admin\/diy\/module-menu-col.jpg"},{"title":"\u4e00\u884c\u4e00\u5217","name":"module-menu-col","img":"http:\/\/longbingcdn.xiaochengxucms.com\/admin\/diy\/module-menu-row.jpg"}]},{"title":"\u4e00\u884c\u591a\u5c11\u5217","type":"InputNumber","name":"row"}],"data":{"isShowTitle":false,"module":"module-menu-row","row":{"number":4,"min":2,"max":5,"label":"\u8bf7\u8f93\u5165"},"list":[{"title":"\u5168\u90e8","icon":"iconwodedingdan","link":{"type":2,"url":"\/shop\/pages\/order\/list?index=0"}},{"title":"\u5f85\u4ed8\u6b3e","icon":"icondingdandaifukuan","link":{"type":2,"url":"\/shop\/pages\/order\/list?index=1"}},{"title":"\u5f85\u53d1\u8d27","icon":"icondingdandaifahuo","link":{"type":2,"url":"\/shop\/pages\/order\/list?index=2"}},{"title":"\u5f85\u6536\u8d27","icon":"icondingdandaishouhuo","link":{"type":2,"url":"\/shop\/pages\/order\/list?index=3"}},{"title":"\u5df2\u5b8c\u6210","icon":"icondingdanyiwancheng","link":{"type":2,"url":"\/shop\/pages\/order\/list?index=4"}}]},"id":1578137248488,"compontents":"ucenterCompoent"},{"title":"\u5fc5\u5907\u5de5\u5177","type":"moduleMenuShop","icon":"iconshop","isDelete":true,"addNumber":1,"attr":[{"title":"\u6a21\u677f\u540d\u79f0","type":"Switch","name":"isShowTitle"},{"title":"\u9009\u62e9\u6a21\u677f","type":"ChooseModule","name":"module","data":[{"title":"\u4e00\u884c\u591a\u5217","name":"module-menu-row","img":"http:\/\/longbingcdn.xiaochengxucms.com\/admin\/diy\/module-menu-col.jpg"},{"title":"\u4e00\u884c\u4e00\u5217","name":"module-menu-col","img":"http:\/\/longbingcdn.xiaochengxucms.com\/admin\/diy\/module-menu-row.jpg"}]},{"title":"\u4e00\u884c\u591a\u5c11\u5217","type":"InputNumber","name":"row"}],"data":{"isShowTitle":false,"module":"module-menu-row","row":{"number":4,"min":2,"max":5,"label":"\u8bf7\u8f93\u5165"},"list":[{"title":"\u6211\u7684\u552e\u540e","icon":"iconwodeshouhou","link":{"type":2,"url":"\/shop\/pages\/refund\/list"}},{"title":"\u6211\u7684\u6536\u5165","icon":"icontixianguanli","link":{"type":2,"url":"\/shop\/pages\/partner\/income"}},{"title":"\u6211\u7684\u4f18\u60e0\u5238","icon":"iconwodekaquan","link":{"type":2,"url":"\/shop\/pages\/coupon\/list"}},{"title":"\u5206\u9500\u5546\u54c1","icon":"iconquanmianfenxiao","link":{"type":2,"needStaffId":true,"url":"\/shop\/pages\/partner\/distribution?staff_id="}},{"title":"\u6211\u7684\u5730\u5740","icon":"icondizhi2","link":{"type":2,"url":"\/shop\/pages\/address\/list"}}]},"id":1578137252032,"compontents":"ucenterCompoent"},{"title":"\u5207\u6362\u9500\u552e","type":"changeStaff","icon":"iconqiehuanmingpian-copy","isDelete":false,"addNumber":1,"attr":[{"title":"\u6a21\u677f\u540d\u79f0","type":"Input","name":"title"},{"title":"\u662f\u5426\u663e\u793a\u66f4\u591a","type":"Switch","name":"isShowMore"}],"data":{"title":"\u5207\u6362\u9500\u552e","isShowMore":true},"dataList":[],"id":1578137250013,"compontents":"ucenterCompoent"}]}}';

        $url  = json_decode($url,true);

        foreach ($url as $k=>$v){

            if(!in_array($k,$data)){

                unset($url[$k]);
            }

        }

        return $url;

    }


    /**
     * 获得拥有模块/app权限列表
     *
     * @param $uniacid
     * @return array
     * @author shuixian
     * @DataTime: 2019/12/20 13:39
     */
     public function getAuthList($uniacid){
        $denyAdminMenuKeys  = [] ;


        if(empty($uniacid)){
            return  $denyAdminMenuKeys ;
        }

        $adminModelListInfo = config('app.AdminModelList') ;
        $saas_auth_admin_model_list =  $adminModelListInfo['saas_auth_admin_model_list'];

        foreach ($saas_auth_admin_model_list as $key=>$item) {

            $className =  'Permission' . ucfirst($key);
            $permissionPath = APP_PATH . $key . '/info/' . $className . '.php';
            if (file_exists($permissionPath) && require_once($permissionPath)) {

                $permissionClassName = 'app\\' . $key . '\\info\\'. $className;
                $permission = new $permissionClassName($uniacid , $item);

                if ( $permission->pAuth() &&  !empty($permission->adminMenuKey)) {
                    $denyAdminMenuKeys[$key] = $item;
                }
            }

        }

        return $denyAdminMenuKeys ;
    }

}
