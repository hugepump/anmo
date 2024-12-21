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

namespace longbingcore\tools;


class LongbingImg
{
    /**
     * 检查远程图片是否存在
     *
     * @param $imgUrl
     * @return bool
     * @author shuixian
     * @DataTime: 2019/12/28 10:17
     */
    public static function exits($imgUrl) {

        if(empty($imgUrl)){
            return false ;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$imgUrl);
        curl_setopt($ch, CURLOPT_NOBODY, 1); // 不下载
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        if(curl_exec($ch)!==false)
            return true;
        else
            return false;

    }
}