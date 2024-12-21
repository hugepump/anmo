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

namespace app\publics\service;


use app\publics\model\TmplConfig;
use longbingcore\wxcore\WxTmpl;

class PublicsService
{


    public static function getTmplByModelName($uniacid,$model_name){

        //获取每个模块下面的配置参数
        $dataPath = APP_PATH . $model_name.'/info/Info.php';
        //实列化模版订阅通知库类；
        $service_model = new WxTmpl($uniacid);

        $tmpl_data = array();

        if(file_exists($dataPath)){

            $model = new TmplConfig();

            //获取配置
            $info = include $dataPath;
            //模块名
            $model_name = $info['name'];
            //模版名
            if(  isset( $info['tmpl_name'] ) ){
                $tmpl_name  = $info['tmpl_name'];
                //循环生成数据库模版
                foreach ($tmpl_name as $value){
                    //模版消息的内容配置
                    $param = $service_model::tmplParam($value);
                    //查询|生成条件
                    $dis = [
                        'uniacid'    => $uniacid,
                        'model_name' => $model_name,
                        'tmpl_name'  => $value,
                        'tid'        => $param['tid'],
                        'kidList'    => $param['kidList'],
                        'kid'        => $param['kid'],
                        'sceneDesc'  => $param['sceneDesc'],
                        'example'    => $param['example'],
                    ];
                    //获取每天模版消息
                    $tmpl_info = $model->tmplInfo($dis);
                    //返回值
                    $tmpl_data[] = $tmpl_info;
                }
            }
        }


        return $tmpl_data;
    }

}