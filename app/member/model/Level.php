<?php
namespace app\member\model;

use app\BaseModel;
use app\massage\model\Admin;
use app\massage\model\CouponRecord;
use app\massage\model\RefundOrder;
use app\massage\model\User;
use app\member\info\PermissionMember;
use think\facade\Db;

class Level extends BaseModel
{
    //定义表名
    protected $name = 'massage_member_level';



    protected $append = [

        'rights',

        'coupon'

    ];


    /**
     * @param $value
     * @param $data
     * @功能说明:关联的权益
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-08 10:12
     */
    public function getRightsAttr($value,$data){

        if(isset($data['id'])){

            $connect_model = new RightsConnect();

            $dis = [

                'a.level_id' => $data['id'],

                'b.status'   => 1
            ];

            $list = $connect_model->alias('a')
                    ->join('massage_member_rights b','a.rights_id = b.id')
                    ->where($dis)
                    ->field('a.*,b.title,b.key,b.show_title,b.rights_icon')
                    ->group('a.rights_id')
                    ->order('b.top desc,a.id desc')
                    ->select()
                    ->toArray();

            return $list;
        }

    }


    /**
     * @param $value
     * @param $data
     * @功能说明:关联的优惠券
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-08 10:36
     */
    public function getCouponAttr($value,$data){

        if(isset($data['id'])){

            $coupon = new Coupon();

            $dis = [

                'a.level_id' => $data['id'],

                'b.status'   => 1
            ];

            $list = $coupon->alias('a')
                    ->join('massage_service_coupon b','a.coupon_id = b.id')
                    ->where($dis)
                    ->field('a.*,b.title,b.full,b.discount,(b.discount*a.num) as total_discount')
                    ->group('a.coupon_id')
                    ->order('a.id desc')
                    ->select()
                    ->toArray();

            return $list;

        }

    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:04
     * @功能说明:添加
     */
    public function dataAdd($data){

        $data['create_time'] = time();

        $res = $this->insert($data);

        return $res;

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:05
     * @功能说明:编辑
     */
    public function dataUpdate($dis,$data){

        $res = $this->where($dis)->update($data);

        return $res;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:06
     * @功能说明:列表
     */
    public function dataList($dis,$page){

        $data = $this->where($dis)->order('growth,id desc')->paginate($page)->toArray();

        return $data;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:43
     * @功能说明:
     */
    public function dataInfo($dis){

        $data = $this->where($dis)->find();

        return !empty($data)?$data->toArray():[];

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-08 11:22
     * @功能说明:获取用户的会员等级
     */
    public function getUserLevel($user_id,$is_auth=1){

        $start = strtotime(date('Y-01-01'));

        $user_model = new User();

        $growth_model = new Growth();

        $user = $user_model->dataInfo(['id'=>$user_id]);

        if(empty($user)){

            return [];
        }

        if($is_auth==1){

            $m_auth = new PermissionMember($user['uniacid']);

            if($m_auth->pAuth()==false){

                return [];
            }
            $config_model = new Config();

            $config = $config_model->dataInfo(['uniacid'=>$user['uniacid']]);

            if($config['status']==0){

                return [];
            }
        }

        $dis[] = ['status','=',1];

        $dis[] = ['uniacid','=',$user['uniacid']];

        $dis[] = ['growth','<=',$user['growth']];

        $data = $this->where($dis)->order('growth desc,id desc')->find();
        //核算今年是否有过降级
        if($user['member_calculate_time']<$start){
            //没有降过
            if(!empty($data)){
                //上一等级
                $find = $this->where(['status'=>1,'uniacid'=>$user['uniacid']])->where('growth','<',$data['growth'])->order('growth desc,id desc')->find();

                if(!empty($find)){

                    $del_growth = $user['growth'] - $find['growth'];
                }else{

                    $del_growth = $user['growth'];
                }

                $admin_model = new Admin();

                $create_user = $admin_model->where(['is_admin'=>1])->value('id');
                //减去成长值
                $growth_model->addRecord($del_growth,0,$user,3,0,$create_user);
            }

            $user_model->dataUpdate(['id'=>$user_id],['member_calculate_time'=>$start]);

            $data = $this->where($dis)->order('growth desc,id desc')->find();
        }
        //查询该等级是vip多少
        if(!empty($data)){

            $data['vip'] = $this->where('status','>',-1)->where('growth','<=',$data['growth'])->count();
        }

        return $data;
    }


    /**
     * @param $user
     * @功能说明:获取用户会员等级
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-03-07 10:40
     */
    public function getUserLevelV2($user,$create_user){

        $growth_model = new Growth();

        $user_model = new User();

        $start = strtotime(date('Y-01-01'));

        $dis[] = ['status','=',1];

        $dis[] = ['uniacid','=',$user['uniacid']];

        $dis[] = ['growth','<=',$user['growth']];

        $data = Db::name('massage_member_level')->where($dis)->order('growth desc,id desc')->find();
        //核算今年是否有过降级
        if($user['member_calculate_time']<$start){
            //没有降过
            if(!empty($data)){
                //上一等级
                $find = $this->where(['status'=>1,'uniacid'=>$user['uniacid']])->where('growth','<',$data['growth'])->order('growth desc,id desc')->find();

                if(!empty($find)){

                    $del_growth = $user['growth'] - $find['growth'];
                }else{

                    $del_growth = $user['growth'];
                }
                //减去成长值
                $growth_model->addRecord($del_growth,0,$user,3,0,$create_user);
            }

            $user_model->dataUpdate(['id'=>$user['id']],['member_calculate_time'=>$start]);

            $data = Db::name('massage_member_level')->where($dis)->order('growth desc,id desc')->find();
        }

        return !empty($data)?$data['title']:'';
    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-05 16:34
     * @功能说明:是否是管理员
     */
    public function checkAuthData($data){

        $member_info = $this->getUserLevel($data['id']);

        if(!empty($member_info)){

            $arr['member_info']['title'] = $member_info['title'];
        }else{

            $arr['member_info'] = [];
        }

        return $arr;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-10 16:14
     * @功能说明:修改用户的成长值
     */
    public function updateUserGrowth($user_id,$uniacid,$is_add,$growth,$type,$order_id=0,$create_user=0){

        $growth = round($growth);

        $config_model = new Config();

        $growth_model = new Growth();

        $user_model   = new User();

        $record_model = new UpRecord();

        $user = $user_model->dataInfo(['id'=>$user_id]);

        if(empty($user)){

            return false;
        }

        $config = $config_model->dataInfo(['uniacid'=>$uniacid]);

        if($config['status']==0){

            return false;
        }

        Db::startTrans();
        //获取当前会员的等级
        $level = $this->getUserLevel($user_id);
        //有成长值限制
        if($config['growth_limit']==1&&$is_add==1&&$type!=2){
            //当日获取成长值
            $today_growth = $growth_model->where(['user_id'=>$user_id,'is_add'=>1,'status'=>1])->whereTime('create_time','today')->sum('growth');
            //还能获取多少成长值
            $c_growth = $config['max_growth_day']-$today_growth;

            $growth = $growth<$c_growth?$growth:$c_growth;

        }

        if($growth>0){
            //增加记录
            $id = $growth_model->addRecord($growth,$is_add,$user,$type,$order_id,$create_user);
        }

        $next_level = $this->getUserLevel($user_id);

        $start_level_id = !empty($level)?$level['growth']:0;

        $next_level_id  = !empty($next_level)?$next_level['growth']:0;
        //说明升级了
        if($next_level_id>$start_level_id){

            $up_record = $record_model->dataInfo(['user_id'=>$user_id,'level_id'=>$next_level['id']]);

            $coupon_model = new CouponRecord();

            $rights  = $next_level['rights'];

            if(!empty($rights)&&empty($up_record)){

                foreach ($rights as $v){
                    //赠送优惠券
                    if(in_array($v['key'],['send_coupon'])){

                        $coupon = $next_level['coupon'];

                        if(!empty($coupon)){

                            foreach ($coupon as $value){

                                $coupon_model->recordAdd($value['coupon_id'],$user_id,$value['num']);
                            }
                        }
                    }
                }
            }
            $growth_model->dataUpdate(['id'=>$id],['is_up'=>$next_level['id']]);

            $insert = [

                'uniacid' => $uniacid,

                'user_id' => $user_id,

                'level_id'=> $next_level['id'],

                'level'   => $next_level['vip']
            ];
            //增加升级福利记录
            $record_model->dataAdd($insert);
        }

        Db::commit();

        return true;
    }


    /**
     * @param $user_id
     * @功能说明:这个方法主要针对平台设置成长值0时候，需要初始化用户的权益
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-29 11:37
     */
    public function initMemberRights($user_id){

        $level = $this->getUserLevel($user_id);

        if(!empty($level)){

            $record_model = new UpRecord();

            $up_record = $record_model->dataInfo(['user_id'=>$user_id,'level_id'=>$level['id']]);

            $coupon_model = new CouponRecord();

            $rights  = $level['rights'];

            if(!empty($rights)&&empty($up_record)){

                foreach ($rights as $v){
                    //赠送优惠券
                    if(in_array($v['key'],['send_coupon'])){

                        $coupon = $level['coupon'];

                        if(!empty($coupon)){

                            foreach ($coupon as $value){

                                $coupon_model->recordAdd($value['coupon_id'],$user_id,$value['num']);
                            }
                        }
                    }
                }

                $insert = [

                    'uniacid' => $level['uniacid'],

                    'user_id' => $user_id,

                    'level_id'=> $level['id'],

                    'level'   => $level['vip'],
                ];
                //增加升级福利记录
                $record_model->dataAdd($insert);
            }
        }
        return true;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-08 11:43
     * @功能说明:会员升级
     */

    public function levelUp($order,$type=1,$is_car=1){

        $m_auth = new PermissionMember($order['uniacid']);

        if($m_auth->pAuth()==false){

            return false;
        }

        $config_model = new Config();

        $config = $config_model->dataInfo(['uniacid'=>$order['uniacid']]);

        if($config['status']==0){

            return false;
        }
        //新增 余额支付没有成长值
        if($order['pay_model']==2){

            return false;
        }
        //按摩订单
        if($type==1){
            $refund_model = new RefundOrder();

            $refund_price = $refund_model->where(['order_id'=>$order['id'],'status'=>2])->sum('refund_price');

            $price = round($order['pay_price'] - $refund_price,2);

        }elseif ($type==4){

            $price = $order['pay_price'];

        }
        //本次获取的成长值
        $growth = $config['growth_value']*$price;

        $res = $this->updateUserGrowth($order['user_id'],$order['uniacid'],1,$growth,$type,$order['id']);

        return $res;
    }









}