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

namespace app\agent\service;


use app\agent\model\AdminModel;
use app\agent\model\AdminRoleModel;
use app\agent\model\AppAdminModel;

class AdminUserService
{

    /**
     * 新增管理员
     *
     * @param $uniacid
     * @param $input
     * @return bool
     * @author shuixian
     * @DataTime: 2019/12/31 18:46
     */
    public static function addAdminUser($uniacid , $input){

        /**
         * @var AdminModel $subAdmin
         */
        $subAdmin = AdminModel::where([['account', '=', $input['account']], ['status', '=', 1]])->findOrEmpty();
        if (!$subAdmin->isEmpty()) {
            return false;
        }

        $offset = createOffset();
        $new = [
            'admin_id' => $input['uuid'],
            'account' => $input['account'],
            'uniacid' => $uniacid ,
            'offset' => $offset,
            'passwd' => createPasswd($input['passwd'], $offset),
            'role_id' => $input['role_id'],
            'creator_id' => $input['admin_id'],
            'status' => 1,
        ];
        $rst = $subAdmin->save($new);
        if (!$rst) {
            return false;
        }
        return true;


    }

    /**
     * 初始化默认角色
     *
     * @throws \Exception
     * @author shuixian
     * @DataTime: 2019/12/31 18:46
     */
    public static function initDefaultRole(){

        $adminRole = new AdminRoleModel();
        $data = [] ;
        if(!$adminRole->where(['role_id'=> 'e1af223e997011e997cb77d35351cc48'])->count()){


            $data[] = [
                'role_id' =>'e1af223e997011e997cb77d35351cc48',
                'role_name' =>'admin',
                'description' =>'超级管理员',
                'create_time' =>null,
                'update_time' =>null,
                'delete_time' =>null,
                'deleted' =>'0',
                'uniacid' =>'8888',

            ];

        }
        if(!$adminRole->where(['role_id'=> 'e7d81116997011e99b985595a87cbdcb'])->count()) {
            $data[] = [
                'role_id' => 'e7d81116997011e99b985595a87cbdcb',
                'role_name' => 'user',
                'description' => '普通用户',
                'create_time' => null,
                'update_time' => null,
                'delete_time' => null,
                'deleted' => '0',
                'uniacid' => '8888',

            ];
        }

        $adminRole->saveAll($data);



    }

    /**
     * 绑定小程序管理员
     *
     * @param $data
     * @author shuixian
     * @DataTime: 2019/12/31 18:49
     */
    public static function bindAppAdmin($data){

        if(AppAdminModel::where(['id'=> $data['id']])->count()) {
            return false;
        }

        $appAdmin = new AppAdminModel();

        $appAdmin->save($data) ;

    }


    /**
     * @param $app_name
     * @功能说明:获取授权数量
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-01-03 11:19
     */
    public static function getSassNum($key,$uniacid){
        //类名
        $className      =  'Permission' . ucfirst($key);
        //类路径
        $permissionPath =  APP_PATH . $key . '/info/' . $className . '.php';

//        dump(file_exists($permissionPath),$permissionPath);exit;
        $num = 0;
        if (file_exists($permissionPath) && require_once($permissionPath)) {



            //实例文件名
            $permissionClassName = 'app\\' . $key . '\\info\\'. $className;

            $permission = new $permissionClassName($uniacid);
            //获取授权数量
            $num = $permission->getAuthNumber();
        }
        return $num;
    }

}