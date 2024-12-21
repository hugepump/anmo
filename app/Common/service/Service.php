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


use app\Common\model\LongbingUser;

class Service
{

    /**
     * @param $userId
     * @功能说明:获取用户openid
     * @author jingshuixian
     * @DataTime: 2020/1/14 12:02
     */
    public static function getUserOpenId($userId){
        $user = new LongbingUser();
        $openid = $user->where(['id'=>$userId])->value('openid');

        return $openid;
    }

    /**
     * @param $userId
     * @功能说明:根据用户ID获得用户昵称
     * @author jingshuixian
     * @DataTime: 2020/2/27 11:48
     */
    public static function getUserNickNameOpenId($userId){
        $user = new LongbingUser();
        $nickName = $user->where(['id'=>$userId])->value('nickName');
        return $nickName;
    }

    /**
     * @param $userId
     * @功能说明:判断用户是否存在
     * @author jingshuixian
     * @DataTime: 2020/1/16 14:58
     */
    public static function isUser($uniacid ,$userId){
        $user = new LongbingUser();
        $info = $user->where(['uniacid' => $uniacid ])->find($userId);

        return  $info ? true : false ;
    }
}