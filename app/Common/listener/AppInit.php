<?php
// +----------------------------------------------------------------------
// | Longbing [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright Chengdu longbing Technology Co., Ltd.
// +----------------------------------------------------------------------
// | Website http://longbing.org/
// +----------------------------------------------------------------------
// | Author: shuixian
// | Data: 2019/12/516:01
// +----------------------------------------------------------------------


namespace app\Common\listener;


use think\facade\Config;
use think\facade\Env;

class AppInit
{
    public $method = 'DES-ECB';

    public function handle()
    {

        // 初始化系统常量
        $this->initConst();

    }

    /**
     * 初始化系统常量
     * @author shuixian
     * @DataTime: 2019/12/516:52
     */
    private function initConst()
    {

        $appDebug = Env::get('APP_DEBUG',false);
        defined('APP_DEBUG') or define('APP_DEBUG',$appDebug) ;

        defined('DS') or define('DS', DIRECTORY_SEPARATOR);

        //获取当前应用目录
        defined('APP_PATH') or define('APP_PATH', app_path());
        //获取当前系统根目录
        defined('LONGBING_ROOT_PATH') or define('LONGBING_ROOT_PATH', APP_PATH . '..' . DS);

        defined('ROOT_PATH') or define('ROOT_PATH', LONGBING_ROOT_PATH);


        //系统扩展目录
        defined('EXTEND_PATH') or define('EXTEND_PATH', LONGBING_ROOT_PATH . 'extend/');
        //app\Common里的扩展目录
        defined('LONGBING_EXTEND_PATH') or define('LONGBING_EXTEND_PATH', APP_PATH . 'Common/extend' . DS);//文件上传目录

        //微擎先删除 在处理
        //defined( 'IA_PATH' ) or define('IA_PATH', ROOT_PATH);
        //defined( 'IA_ROOT' ) or define('IA_ROOT', ROOT_PATH);


        defined('PAY_PATH') or define('PAY_PATH', LONGBING_ROOT_PATH);

        //微擎
        if (longbingIsWeiqin()) {
            global $_W;
            defined('APP_MODEL_NAME') or define('APP_MODEL_NAME', $_W['current_module']['name']);

            //发现没有使用
            //defined('UPLOAD_OBPATH') or define('UPLOAD_OBPATH', 'https://' . $_SERVER['HTTP_HOST'] . '/attachment/upload' . DS); // 配置文件目录
            //defined('APP_PUBLIC_OBPATH') or define('APP_PUBLIC_OBPATH', 'https://' . $_SERVER['HTTP_HOST'] . '/addons/longbing_shequpintuan/core/public' . DS); // 配置文件目录
            //defined('APP_HOST_PUBLIC') or define('APP_HOST_PUBLIC', 'https://' . $_SERVER['HTTP_HOST'] . '/addons/longbing_shequpintuan/core/public/init_image' . DS); // 配置文件目录

            defined('UPLOAD_PATH') or define('UPLOAD_PATH', $_SERVER['DOCUMENT_ROOT'] . '/attachment/upload/' . DS); // 配置文件目录
            defined('PUBLIC_PATH') or define('PUBLIC_PATH', ADDON_PATH . '/core/public' . DS); // 配置文件目录
            defined('FILE_UPLOAD_PATH') or define('FILE_UPLOAD_PATH', LONGBING_ROOT_PATH . '../../../attachment' . DS);//文件上传目录

            defined('MATER_UPLOAD_PATH') or define('MATER_UPLOAD_PATH', LONGBING_ROOT_PATH . '../../../attachment/file' . DS);//文件上传目录

            defined('HTTPS_PATH') or define('HTTPS_PATH', 'https://'.$_SERVER['HTTP_HOST'].'/attachment'. DS); //文件上传目录

            defined('H5_PATH') or define('H5_PATH', $_SERVER['DOCUMENT_ROOT'].'/h5'. DS); //文件上传目录

            defined('COACH_PATH') or define('COACH_PATH', $_SERVER['DOCUMENT_ROOT'].'/coach/h5'. DS); //文件上传目录


        } //独立版
        else {

            defined('H5_PATH') or define('H5_PATH', $_SERVER['DOCUMENT_ROOT'].'/h5'. DS); //文件上传目录

            defined('HTTPS_PATH') or define('HTTPS_PATH', 'https://'.$_SERVER['HTTP_HOST'].'/attachment'. DS); //文件上传目录

            defined('MATER_UPLOAD_PATH') or define('MATER_UPLOAD_PATH', ROOT_PATH . 'public/attachment/file' . DS); //文件上传目录

            defined('HTTP_HOST') or define('HTTP_HOST', $_SERVER['HTTP_HOST']); //文件上传目录

            defined('APP_MODEL_NAME') or define('APP_MODEL_NAME', Config::get('app.AdminModelList.app_model_name','longbing_card') );
            defined('UPLOAD_PATH') or define('UPLOAD_PATH', $_SERVER['DOCUMENT_ROOT'] . '/uploads' . DS); // 配置文件目录
            defined('PUBLIC_PATH') or define('PUBLIC_PATH', ROOT_PATH . 'public' . DS); // 配置文件目录
            defined('FILE_UPLOAD_PATH') or define('FILE_UPLOAD_PATH', ROOT_PATH . 'public/attachment' . DS); //文件上传目录

            defined('COACH_PATH') or define('COACH_PATH', $_SERVER['DOCUMENT_ROOT'].'/coach/h5'. DS); //文件上传目录
        }

        //修改文件存储配置信息
        $filesystemConfig  =   Config::get('filesystem') ;
        $filesystemConfig['disks']['longbing']['root'] =  FILE_UPLOAD_PATH ;
        Config::set( $filesystemConfig, 'filesystem');



    }


}