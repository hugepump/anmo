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


class LongbingDefault
{
    public static $avatarImgUrl = 'https://retail.xiaochengxucms.com/defaultAvatar.png' ;
    public static $notImgUrl = 'https://retail.xiaochengxucms.com/lbCardDefaultImage.png' ;


    /**
     * @param $data
     * @param $target
     * @param $default
     * @param $defaultArr
     * @功能说明: 格式换默认图片
     * @author jingshuixian
     * @DataTime: 2020/1/17 11:32
     */
    public static function formatDefaultImage ( $data, $target, $default, $defaultArr )
    {

        foreach ( $data as $index => $item )
        {
            if ( is_array( $item ) )
            {
                $data[ $index ] = formatDefaultImage( $item, $target, $default, $defaultArr );
            }
            else
            {
                if ($index == $target && $item == '' && isset($defaultArr[$default]))
                {
                    $data[$index] = $defaultArr[$default];
                }
            }
        }
        return $data;
    }
}