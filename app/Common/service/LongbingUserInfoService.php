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

namespace app\Common\service;

use app\Common\model\LongbingUserInfo;

class LongbingUserInfoService
{

    /**
     * @param $userId
     * @功能说明:获取员工姓名
     * @author jingshuixian
     * @DataTime: 2020/1/14 14:14
     */
    public static function getNameByUserId($userId){
        $userInfo = new LongbingUserInfo();
        $name = $userInfo->where(['fans_id'=>$userId])->value('name');

        return $name;
    }

    /**
     * @param $userId
     * @功能说明:判断是否为员工
     * @author jingshuixian
     * @DataTime: 2020/1/14 14:54
     */
    public static function isStraffByUserId($userId){

        $userInfo = new LongbingUserInfo();
        $is_staff = $userInfo->where(['fans_id'=>$userId])->value('is_staff');
        return $is_staff ? true : false ;
    }


}