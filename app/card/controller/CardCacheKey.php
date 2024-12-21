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

namespace app\card\controller;

/**
 * @author shuixian
 * @DataTime: 2019/12/26 14:16
 * Class CardCacheKey
 * @package app\card\controller
 */
class CardCacheKey
{

    /**
     * 名片配置缓存key
     *
     * @param $uniacid
     * @return string
     * @author shuixian
     * @DataTime: 2019/12/26 14:21
     */
    public static function cardAppConfig($uniacid){

        return  longbing_get_cache_key('card_app_config' , $uniacid) ;

    }

}