<?php
namespace app\index\controller;
use think\facade\View;
class Index
{
    public function index()
    {
        $timestamp = '?v=' . time();
        $is_we7 = defined('IS_WEIQIN') ? true : false;
        $appcss = $is_we7 ? '/addons/'. APP_MODEL_NAME .'/core2/public/static/css/app.css' : '/static/css/app.css';
        $manifest = $is_we7 ? '/addons/'.APP_MODEL_NAME.'/core2/public/static/js/manifest.js' : '/static/js/manifest.js';
        $vendor =  $is_we7 ?  '/addons/'.APP_MODEL_NAME.'/core2/public/static/js/vendor.js' : '/static/js/vendor.js';
        $app = $is_we7 ? '/addons/'.APP_MODEL_NAME .'/core2/public/static/js/app.js' : '/static/js/app.js';
        $jsPath = $is_we7 ?  '/addons/'.APP_MODEL_NAME .'/core2/public/' : '/';
        $jquery = $is_we7 ? '/addons/'.APP_MODEL_NAME.'/core2/public/js/jquery-3.5.1.min.js' : '/js/jquery-3.5.1.min.js';
        global  $_W;
        $is_founder = isset($_W['isfounder']) ? $_W['isfounder'] : false;

        View::assign('jsPath', $jsPath);
        View::assign('jquery', $jquery .$timestamp);
        View::assign('is_founder', $is_founder);
        View::assign('isWe7', $is_we7);
        View::assign('appcss', $appcss.$timestamp);
        View::assign('manifest', $manifest .$timestamp);
        View::assign('vendor', $vendor .$timestamp );
        View::assign('app', $app . $timestamp);

        //var_dump(View());die;
        return View::fetch();
        // return View::engine('think')->fetch('index');
    }
}
