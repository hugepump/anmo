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

namespace app\card\info;

use app\card\model\Job;
use app\card\model\User;
use app\card\service\UserService;
use app\Common\model\LongbingUserInfo;
use longbingcore\diy\BaseSubscribe;

/**
 * @author shuixian
 * @DataTime: 2019/12/11 16:23
 * Class Subscribe
 * @package app\ucenter\info
 */
class Subscribe extends BaseSubscribe
{

    /**
     * 监听个人中心用户监听事件
     *
     * @param $data
     * @return mixed
     * @author shuixian
     * @DataTime: 2019/12/12 9:36
     */
    public function onDiyUserInfo($data)
    {
        return  $data ;
    }

    /**
     * 监听用户创建模块
     *
     * @param $data
     * @return mixed
     * @author shuixian
     * @DataTime: 2019/12/18 14:05
     */
    public function onDiyCreateCard($data)
    {

        //这里需要优化
       /* $data['dataList'] = [
            "createText" => "",
            "createBtn" => ""
        ];*/

        return $data;
    }

    /**
     * 监听用户中心切换按钮
     *
     * @param $data
     * @author shuixian
     * @DataTime: 2019/12/18 14:07
     */
    public function onDiyChangeStaff($data)
    {

        $userModel = new User();

        $last_staff_id = $userModel->where('id',$this->getUserId())->value('last_staff_id ');

        if ( $last_staff_id) {

            $staff_model = new LongbingUserInfo();

            $staff_info = $staff_model->getStaff($last_staff_id, $this->_uniacid);

            if(!empty($staff_info)){

                $job_model = new Job();
                $job_name = $job_model->where( 'id' , $staff_info['job_id'])->value('name') ;
                $staff_info['job_name'] = $job_name;

                $staff_info = longbing_array_columns([$staff_info],['id','fans_id','name','avatar','job_name']) ;
                $staff_info = $staff_info[0] ;

                //获取职位信息

            }

            $data['dataList']  = $staff_info;
        }

        return  $data ;
    }
    /**
     * 监听用户中心模块
     *
     * @return array
     * @author shuixian
     * @DataTime: 2019/12/18 14:04
     */
    public function onAddUcenterCompoent(){

        $userInfo = <<<COMPOENT
{"title":"用户信息","type":"userInfo","icon":"iconyonghuxinxi","isDelete":false,"addNumber":1,"attr":[{"title":"字体颜色","type":"ColorPicker","name":"fontColor"},{"title":"背景图片","type":"UploadImage","desc":"750*440","name":"bgImage"}],"data":{"nickName":"用户昵称","avatarUrl":"https://retail.xiaochengxucms.com/defaultAvatar.png","nickText":"更新我的个人资料","fontColor":"#F9DEAF","bgImage":[{"url":"http://longbingcdn.xiaochengxucms.com/admin/diy/user_bg.jpg"}]}}
COMPOENT;

        $createCard = <<<COMPOENT
{"title":"创建名片","type":"createCard","icon":"iconchuangjianmingpian","isDelete":false,"addNumber":1,"data":{"createText":"创建我的名片","createBtn":"创建名片"}}
COMPOENT;

        $changeStaff = <<<COMPOENT
{"title":"切换销售","type":"changeStaff","icon":"iconqiehuanmingpian-copy","isDelete":false,"addNumber":1,"attr":[{"title":"模板名称","type":"Input","name":"title"},{"title":"是否显示更多","type":"Switch","name":"isShowMore"}],"data":{"title":"切换销售","isShowMore":true},"dataList":[]}
COMPOENT;

        $distribution = <<<DISTRIBUTION

 {"title":"分销申请","type":"distributionApply","icon":"iconDistributionApply","isDelete":true,"addNumber":1,"data":{"title":"分销申请","img":"http://longbingcdn.xiaochengxucms.com/admin/shop/distridution.jpg"}}
DISTRIBUTION;



        $compoentList = [
            json_decode($userInfo, true),

            json_decode($createCard, true),

            json_decode($changeStaff, true),

        ] ;

        $app_name = config('app.AdminModelList')['app_model_name'];

        if(!in_array($app_name,['longbing_web'])){

            $compoentList[] =   json_decode($distribution, true);
        }

        return $compoentList ;
    }

    /**
     * 监听用户登录
     *
     * @param $user
     * @author shuixian
     * @DataTime: 2019/12/30 17:28
     */
    public function onUserLoginApp($user){
        $userService = new UserService();
        $userService->initFirstUserToStaff($user['id'], $user['uniacid']) ;
    }

}