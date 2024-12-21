<?php


namespace app\card\controller;

use app\BaseController;

class GetImage extends BaseController
{

    /**
     * 将线上图片转为本地图片用于前端cavans画图
     */
    public function getImage ()
    {
        $param = $this->request->param();
        $path = $param['path'] ?? null ;
        if (!$path ) {
            return $this->error('请传入参数');
        }
//
//        $path     = $_SERVER[ 'QUERY_STRING' ];
//        $position = strpos($path, 'getImage&path=');
//        $sub_str  = substr($path, $position + 14);
//        $path     = urldecode($sub_str);
        //把https 替换为  http
        $path = str_replace("https://"  , "http://" , $path) ;
        //判断类型
        $type_img = getimagesize($path);

        ob_start();

        if ( strpos($type_img[ 'mime' ], 'jpeg') ) {
            $resourch = imagecreatefromjpeg($path);
            imagejpeg($resourch);
        } elseif ( strpos($type_img[ 'mime' ], 'png') ) {
            $resourch = imagecreatefrompng($path);
            imagepng($resourch);
        }

        $content = ob_get_clean();
        imagedestroy($resourch);
        return response($content, 200, [ 'Content-Length' => strlen($content) ])->contentType('image/png');
    }
}