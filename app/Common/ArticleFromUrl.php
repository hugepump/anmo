<?php
/**
 * @Purpose: 获取文章内容
 *
 * @Param: string   $url  公众号文章地址
 *
 * @Author: zzf
 *
 * @Return: mixed 查询返回值（结果集对象）
 */

namespace app\Common;

use think\Exception;

class ArticleFromUrl
{
    protected $html;

    /**
     * @Purpose: 初始化方法
     *
     * @Param: string   $url  公众号文章地址
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function __construct ( $url )
    {
        $this->html = file_get_contents( $url );
    }

    /**
     * @Purpose: 获取文章内容
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function getArticle ()
    {
        $file = $this->html;

        $file = str_replace( "data-src", "src", $file );
        $file = str_replace( "data-croporisrc", "src", $file );
        $file = str_replace( "preview.html", "player.html", $file );
        $file = str_replace( "display: inline-block;", "display: block;", $file );

        $html = '';

        $title = $this->get_between( $file, '<h2 class="rich_media_title" id="activity-name">', "</h2>" );


        if(strpos($file,'<div class="rich_media_content " id="js_content" style="visibility: hidden;">') !== false){
            $html_content = '<div class="rich_media_content " id="js_content" style="visibility: hidden;">';
        }else{
            $html_content = '<div class="rich_media_content " id="js_content">';

        }
//        $html_content  = str_replace('style="visibility: hidden;"','','<div class="rich_media_content " id="js_content" style="visibility: hidden;">');
//        dump($html_content);exit;
//        $content = $this->get_between( $file, '<div class="rich_media_content " id="js_content" ', "var first_sceen__time = (+new Date());" );
        $content = $this->get_between( $file, $html_content, "var first_sceen__time = (+new Date());" );
        $res     = file_put_contents( 'tmp_article.txt', $content );
        if ( $res ) {
            $content = file( 'tmp_article.txt' );
            unset( $content[ count( $content ) - 1 ] );
            unset( $content[ count( $content ) - 1 ] );
            unset( $content[ count( $content ) - 1 ] );
        }
        $res = file_put_contents( 'tmp_article.txt', $content );
        if ( $res ) {
            $content = file_get_contents( 'tmp_article.txt' );
        }
        @unlink( 'tmp_article.txt' );

        $html = $content;

        $cover = '';

        $title = trim( $title );
        $cover = trim( $cover );
        $html  = trim( $html );
        return [ 'cover' => $cover, 'title' => $title, 'content' => $html ];
    }


    /**
     * @Purpose: php截取指定两个字符之间字符串
     *
     * @Param: string   $input  字符串
     * @Param: int      $start  开始截取位置
     * @Param: int      $end    结束截取位置
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    protected function get_between ( $input, $start, $end )
    {

        $substr = substr( $input, strlen( $start ) + strpos( $input, $start ), ( strlen( $input ) - strpos( $input, $end ) ) * ( -1 ) );
//        $substr = substr( $input,  strpos( $input, $start ), ( strlen( $input ) - strpos( $input, $end ) ) * ( -1 ) );

        return $substr;
    }

    protected function getImageDown ( $str )
    {

        if ( !is_dir( ATTACHMENT_ROOT . '/' . "images" ) ) {
            mkdir( ATTACHMENT_ROOT . '/' . "images" );
        }
        if ( !is_dir( ATTACHMENT_ROOT . '/' . "images/a_qr" ) ) {
            mkdir( ATTACHMENT_ROOT . '/' . "images/a_qr" );
        }
        if ( !is_dir( ATTACHMENT_ROOT . '/' . "images/a_image/" ) ) {
            mkdir( ATTACHMENT_ROOT . '/' . "images/a_image/" );
        }

        $preg = '/<img.*?src=[\"|\']?(.*?)[\"|\']?\s.*?>/i';//匹配img标签的正则表达式

        preg_match_all( $preg, $str, $allImg );//这里匹配所有的img

        $allImg             = $allImg[ 1 ];
        $allImgNot          = array();
        $destination_folder = ATTACHMENT_ROOT . '/images' . "/a_image/";

        foreach ( $allImg as $index2 => $item2 ) {
            $ftype = '.png';
            $res   = getimagesize( $item2 );

            if ( isset( $res[ 'mime' ] ) ) {
                if ( strpos( $res[ 'mime' ], 'gif' ) ) {
                    $ftype = '.gif';
                }
            }
            $name        = str_shuffle( time() . rand( 111111, 999999 ) ) . $ftype;
            $destination = $destination_folder . $name;
            $file        = file_get_contents( $item2 );
            file_put_contents( $destination, $file );
            $image = 'images' . "/a_image/" . $name;
            array_push( $allImgNot, $image );
            $image             = tomedia( $image );
            $allImg[ $index2 ] = $image;
            $str               = str_replace( $item2, $image, $str );
        }
        return [ $str, $allImg, $allImgNot ];
    }

}