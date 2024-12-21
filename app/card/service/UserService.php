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

namespace app\card\service;


use app\card\model\CardBoss;
use app\card\model\Company;
use app\card\model\User;
use app\card\model\UserInfo;

class UserService
{

    private $_uniacid ;


    /**
     * 初始化系统没有员工的情况
     *
     * @param $staff_id
     * @param $uniacid
     * @return bool
     * @author shuixian
     * @DataTime: 2019/12/30 16:47
     */
    public function initFirstUserToStaff($staff_id ,$uniacid){
        longbingGetCompanyConfig($uniacid);
        $count = User::where(['uniacid' => $uniacid ,'is_staff' => 1] )->count() ;
        $result = false ;
        if (!$count){
            $result = $this->addUserToStaff($staff_id , [] , $uniacid) ;
        }

        return $result ;
    }

    /**
     * 添加用户为模拟人员工说
     *
     * @param $staff_id
     * @param $companyIds
     * @param $uniacid
     * @return CardBoss|bool|\think\Model
     * @throws \Exception
     * @author shuixian
     * @DataTime: 2019/12/30 16:50
     */
    public function addUserToStaff($staff_id , $companyIds ,$uniacid){

        $params = [ 'staff_id' =>  $staff_id  , 'company_ids' => $companyIds];

        $this->_uniacid = $uniacid ;

        //检查数据是否存在
        if(!isset($params['staff_id']) || empty($params['staff_id'])) return lang('not staff');

        $user   = longbingGetUser($params[ 'staff_id' ] ,$this->_uniacid);
        //判断用户是否存在
        if(empty($user)) return lang('not user');
        //获取boss端权限
        $permissions = longbingGetPluginAuth($this->_uniacid);
        if(empty($user['is_staff']))
        {
            //获取名片总数
            if(!empty($permissions) && isset($permissions['card_number']) && !empty($permissions['card_number']))
            {
                if(!(longbingGetCardNum($this->_uniacid) < $permissions['card_number']))
                {
                    return lang('not card num');
                }
            }
        }
        $result = true;
        if(empty($permissions) || !isset($permissions['plugin']) || !isset($permissions['plugin']['boss']) || empty($permissions['plugin']['boss']))
        {
            $params['company_ids'] = [];
            //获取一个默认的公司id
            $company_model = new Company();
            $company       = $company_model->getCompany(['uniacid' => $this->_uniacid ,'status' => 1] ,['id']);
            if(!empty($company)) $params['company_ids'][] = $company['id'];
        }else{
            if(!isset($params['company_ids']) || empty($params['company_ids'])) return lang('not company id');
            CardBoss::where( [ [ 'user_id', '=', $params[ 'staff_id' ] ] ] )
                ->delete();

            $boss = implode( ',', $params[ 'company_ids' ] );

            $result = CardBoss::create( [ 'user_id' => $params[ 'staff_id' ], 'boss' => $boss, 'uniacid' => $this->_uniacid ] );
        }

        if ( $result === false )
        {
            return 'edit failed' ;
        }else{
            $check = true;
            //判断用户是否是员工
            if(empty($user['is_staff'])){
                $user['is_staff'] = 1;
                longbingSetUser($params[ 'staff_id' ] ,$this->_uniacid ,$user);
                //更新数据
                $user_model = new User();
                $check      = $user_model->updateUser(['id' => $params[ 'staff_id' ] ,'uniacid' => $this->_uniacid] ,['is_staff' => 1]);
            }
            if($check){
                //判断员工名片是否存在
                $staff = longbingGetUserInfo($params[ 'staff_id' ] ,$this->_uniacid);
                $staff_model = new UserInfo();
                $company_id = '';
                if(isset($params[ 'company_ids' ][0]) && !empty($params[ 'company_ids' ][0])) $company_id = $params[ 'company_ids' ][0];
                if(empty($staff))
                {
                    $result = $staff_model->createUser(['fans_id' => $params[ 'staff_id' ] ,'uniacid' => $this->_uniacid ,'is_staff' => 1 ,'company_id' => $company_id ,'is_default' => 1]);
                    longbingGetUserInfo($params[ 'staff_id' ] ,$this->_uniacid ,true);
                }
                if(!empty($staff))
                {
                    $company_id = $staff['company_id'];
                    if(isset($params[ 'company_ids' ]) && !empty($params[ 'company_ids' ]) && !in_array($staff['company_id'], $params[ 'company_ids' ])) $company_id = $params[ 'company_ids' ][0];
                    $result = $staff_model->updateUser(['fans_id' => $params[ 'staff_id' ] ,'uniacid' => $this->_uniacid] ,['is_staff' => 1 , 'is_default' => 1 ,'company_id' => $company_id ]);
                    $staff['is_staff'] = 1;
                    $staff['is_default'] = 1;
                    $staff['company_id'] = $company_id;
                    longbingSetUserInfo($params[ 'staff_id' ] ,$this->_uniacid ,$staff);
                }

            }
        }

        return $result ;
    }

    /**
     * 获取推荐员工数量
     *
     * @param $uniacid
     * @return int
     * @author shuixian
     * @DataTime: 2020/1/2 17:04
     */
     public static function  getDefaultStraffNumber($uniacid){

         $count = User::alias( 'a' )
             ->join( 'longbing_card_user_info b', 'b.fans_id = a.id' )
             ->where('a.is_staff' , 1)
             ->where('b.is_staff' , 1)
             ->where('b.is_default' , 1)
             ->where('a.uniacid',$uniacid)
             ->where('b.uniacid',$uniacid)
             ->count();

         return $count ;
     }


    /**
     * 生成员工头像二维码
     *
     * @param $_uniacid
     * @param $params
     * @return array|bool|mixed
     * @author shuixian
     * @DataTime: 2020/1/2 21:15
     */
     public static function createHeaderQr($_uniacid , $params){

         $staff  = longbingGetUserInfo($params['staff_id'] ,$_uniacid);

         if(empty($staff) || empty($staff['is_staff'])) return lang('staff info not found');
         //生成数据
         $data   = ["data" => ["staff_id" => $params['staff_id'] ,"pid" => $params['staff_id'] ,"type" => 4 ,"key" => 1]];
         //生成二维码
         $result = longbingCreateWxCode($_uniacid,$data);

         if(isset($result['qr_path'])) $result = transImagesOne($result ,['qr_path'] ,$_uniacid);
         //获取名片设置
         $config = longbingGetAppConfig($_uniacid);
         //是否用头像替换二维码中心
         if(!empty($staff['avatar']) && !empty($result) &&  !empty($result['qr_path']) && !empty($result['path']) && isset($config['qr_avatar_switch']) && !empty($config['qr_avatar_switch']))
         {
             $staff = transImagesOne($staff ,['avatar'] ,$_uniacid);

             $dat   = longbingUpdateQrByAvatar($staff['avatar'] ,$result['qr_path'] , $_uniacid ,$result['path']);
         }
         //判断是否成功
         if(empty($result) || !isset($result['qr_path'])) return lang('create qr error');
         //改写数据
         User::update( [ 'qr_path' => $result['qr_path'] ], [ 'id' => $params[ 'staff_id' ] ] );
         //更新缓存
         longbingGetUser($params['staff_id'] , $_uniacid ,true);

         return $result;
     }

    /**
     * @param $_uniacid
     * @param $userId
     * @功能说明:获取用户最后访问名片的ID
     * @author jingshuixian
     * @DataTime: 2020/1/7 17:22
     */
     public static function getLastStaffId($_uniacid,$userId){

         $where[] = ['uniacid','=' , $_uniacid ];
         $where[] = ['id','=' , $userId ];
         $last_staff_id = User::where($where)->value('last_staff_id');

         return $last_staff_id;
     }


    /**
     * @param $_uniacid
     * @param $userId
     * @功能说明:删除用户缓存信息
     * @author jingshuixian
     * @DataTime: 2020/1/10 14:43
     */
     public static function delUserInfoCache($_uniacid , $userId){
         $key = 'longbing_card_info_' . $userId;
         return delCache($key ,$_uniacid);
     }
}