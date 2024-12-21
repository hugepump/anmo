<?php


namespace app\agent\controller;



use think\facade\View;

class Index
{


    public function index()
    {

        $timestamp = '?v=' . time();
        $is_we7 = defined('IS_WEIQIN') ? true : false;
        $appcss = $is_we7 ? '/addons/'. APP_MODEL_NAME .'/core2/public/agent/static/css/app.css' : '/agent/static/css/app.css';
        $manifest = $is_we7 ? '/addons/'. APP_MODEL_NAME .'/core2/public/agent/static/js/manifest.js' : '/agent/static/js/manifest.js';
        $vendor =  $is_we7 ?  '/addons/'. APP_MODEL_NAME .'/core2/public/agent/static/js/vendor.js' : '/agent/static/js/vendor.js';
        $app = $is_we7 ? '/addons/'. APP_MODEL_NAME .'/core2/public/agent/static/js/app.js' : '/agent/static/js/app.js';

        $jsPath = $is_we7 ?  '/addons/'. APP_MODEL_NAME .'/core2/public/agent/' : '/agent/';
        global  $_W;
        $is_founder = isset($_W['isfounder']) ? $_W['isfounder'] : false;
        View::assign('jsPath', $jsPath);
        View::assign('isWe7', $is_we7);
        View::assign('is_founder', $is_founder);
        View::assign('appcss', $appcss.$timestamp);
        View::assign('manifest', $manifest .$timestamp);
        View::assign('vendor', $vendor .$timestamp );
        View::assign('app', $app . $timestamp);
        return View::fetch();

    }

}