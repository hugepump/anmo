<?php
namespace app\massage\model;

use app\BaseModel;
use app\massage\model\User;
use longbingcore\permissions\AdminMenu;
use think\facade\Db;

class DistributionList extends BaseModel
{



    protected $name = 'massage_distribution_list';


    public function getTrueUserNameAttr($value,$data){

        if(isset($value)){

            if(!empty($value)){

                return $value;

            }elseif (!empty($data['user_name'])){

                return $data['user_name'];
            }
        }
    }


    public function getMobileAttr($value,$data){

        if(!empty($value)&&isset($data['uniacid'])){

            if(numberEncryption($data['uniacid'])==1){

                return  substr_replace($value, "****", 2,4);
            }
        }

        return $value;
    }


    public function getBalanceAttr($value,$data){

        if(isset($value)){

            return floatval($value);
        }
    }


    public function getLevelBalanceAttr($value,$data){

        if(isset($value)){

            return floatval($value);
        }
    }


    /**
     * @param $value
     * @param $data
     * @功能说明:判断代理商是否有发展技师的权限
     * @author chenniang
     * @DataTime: 2024-06-13 15:07
     */
    public function getAdminIdAttr($value,$data){

        if(!empty($value)){

            $admin_model = new Admin();

            $admin = $admin_model->where(['id'=>$value,'status'=>1,'reseller_auth'=>1])->count();

            return $admin>0?$value:0;

        }else{

            return 0;
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

        $data = $this->where($dis)->order('id desc')->paginate($page)->toArray();

        return $data;

    }


    public function dataIndexList($dis, $page, $order = 'a.id desc')
    {

        $data = $this->alias('a')
            ->field('a.*,ifnull(SUM(b.cash),0) as total_cash')
            ->where($dis)
            ->leftJoin('massage_service_order_commission b','b.top_id = a.user_id and b.type in (1,14,15) and b.create_time > a.create_time')
            ->order($order)
            ->group('a.id')
            ->paginate($page)
            ->toArray();

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
     * @param $user_id
     * @param int $type
     * @功能说明:团队人数
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-07-28 17:58
     */
    public function teamCount($user_id,$type=1){

        $user_model = new User();

        $dis[] = ['is_fx','=',1];

        if($type==1){

            $dis[] = ['pid','=',$user_id];

        }else{

            $top_id = $user_model->where(['pid'=>$user_id,'is_fx'=>1])->column('id');

            $dis[] = ['pid','in',$top_id];

        }

        $data = $user_model->where($dis)->count();

        return $data;

    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-12-30 11:26
     * @功能说明:后台列表
     */
    public function adminDataList($dis,$page=10,$where=[]){

        $data = $this->alias('a')
            ->join('massage_service_user_list b','a.user_id = b.id','left')
            ->where($dis)
            ->where(function ($query) use ($where){
                $query->whereOr($where);
            })
            ->field('a.*,b.nickName,b.avatarUrl')
            ->group('a.id')
            ->order('a.id desc')
            ->paginate($page)
            ->toArray();

        if(!empty($data['data'])){

            $admin_model = new Admin();

            foreach ($data['data'] as &$v){

                $admin = $admin_model->dataInfo(['id'=>$v['admin_id'],'status'=>1]);

                $v['recommend_cash_auth'] = 1;

                if(!empty($admin['reseller_auth'])){

                    $v['admin_name'] = $admin['agent_name'];

                    $v['recommend_cash_auth'] = $admin['recommend_cash_auth'];
                }else{

                    $v['admin_id'] = 0;
                }

                $v['balance'] = $v['balance']>=0?$v['balance']:getConfigSetting($v['uniacid'],'user_agent_balance');

                $v['level_balance'] = $v['level_balance']>=0?$v['level_balance']:getConfigSetting($v['uniacid'],'user_level_balance');

                if(!empty($v['pid'])){

                    $v['top_name'] = $this->where(['id'=>$v['pid'],'status'=>2])->value('user_name');
                }
            }
        }

        return $data;
    }


    /**
     * @param $dis
     * @param int $page
     * @功能说明:用户收益列表
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-07-29 14:50
     */
    public function userProfitList($dis,$page=10,$where=[]){

        $user_model = new User();

        $data = $user_model->alias('a')
                ->join('massage_distribution_list b','a.id = b.user_id','left')
                ->where($dis)
                ->where(function ($query) use ($where){
                    $query->whereOr($where);
                })
                ->field('b.*,a.nickName,a.avatarUrl,a.fx_cash')
                ->group('a.id')
                ->order('a.id desc')
                ->paginate($page)
                ->toArray();

        return $data;
    }


    /**
     * @param $dis
     * @param int $page
     * @功能说明:用户收益列表
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-07-29 14:50
     */
    public function userProfitSelect($dis,$where=[]){

        $user_model = new User();

        $data = $user_model->alias('a')
            ->join('massage_distribution_list b','a.id = b.user_id','left')
            ->where($dis)
            ->where(function ($query) use ($where){
                $query->whereOr($where);
            })
            ->field('b.*,a.nickName,a.avatarUrl,a.fx_cash')
            ->group('a.id')
            ->order('a.id desc')
            ->select()
            ->toArray();

        return $data;
    }


    /**
     * @param $dis
     * @param $where
     * @param int $page
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-23 10:28
     */
    public function userDataList($dis,$where,$page=10){

        $user_model = new User();

        $data = $user_model
            ->alias('a')
            ->join('massage_distribution_list b','a.id = b.user_id AND b.status in (2,3)','left')
            ->where($dis)
            ->where(function ($query) use ($where){
                $query->whereOr($where);
            })
            ->field('b.*,b.uniacid,ifnull(b.sh_time,0) as sh_time,a.nickName,a.avatarUrl,a.new_cash,a.cash,a.id,ifnull(b.create_time,0) as reseller_create_time,ifnull(b.id,-1) as id,a.id as user_id')
            ->group('a.id')
            ->order('sh_time desc,a.id desc')
            ->paginate($page)
            ->toArray();

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $v['date'] = !empty($v['sh_time'])?date('Y-m-d H:i:s',$v['sh_time']):'';

                if(numberEncryption($v['uniacid'])==1){

                    $v['mobile'] = substr_replace($v['mobile'], "****", 2,4);
                }
            }
        }

        $data['total_cash'] = $user_model
            ->alias('a')
            ->join('massage_distribution_list b','a.id = b.user_id AND b.status in (2,3)','left')
            ->where($dis)
            ->where(function ($query) use ($where){
                $query->whereOr($where);
            })
            ->group('a.id')
            ->sum('a.new_cash');

        $data['total_cash'] = round($data['total_cash'],2);

        return $data;
    }


    /**
     * @param $dis
     * @param $where
     * @param int $page
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-23 10:28
     */
    public function userDataSelect($dis,$where){

        $user_model = new User();

        $data = $user_model
            ->alias('a')
            ->join('massage_distribution_list b','a.id = b.user_id AND b.status in (2,3)','left')
            ->where($dis)
            ->where(function ($query) use ($where){
                $query->whereOr($where);
            })
            ->field('b.*,b.uniacid,ifnull(b.sh_time,0) as sh_time,a.nickName,a.avatarUrl,a.new_cash,a.cash,a.id,ifnull(b.create_time,0) as reseller_create_time,ifnull(b.id,-1) as id,a.id as user_id')
            ->group('a.id')
            ->order('sh_time desc,a.id desc')
            ->select()
            ->toArray();

        if(!empty($data)){

            foreach ($data as &$v){

                $v['date'] = !empty($v['sh_time'])?date('Y-m-d H:i:s',$v['sh_time']):'';

                if(numberEncryption($v['uniacid'])==1){

                    $v['mobile'] = substr_replace($v['mobile'], "****", 2,4);
                }
            }
        }

        $total_cash = $user_model
            ->alias('a')
            ->join('massage_distribution_list b','a.id = b.user_id AND b.status in (2,3)','left')
            ->where($dis)
            ->where(function ($query) use ($where){
                $query->whereOr($where);
            })
            ->group('a.id')
            ->sum('a.new_cash');

        $arr['data'] = $data;

        $arr['cash'] = round($total_cash,2);

        return $arr;
    }

    /**
     * @param $partner_id
     * @功能说明:合伙人相关订单数量
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-23 11:53
     */
    public function partnerOrderCount($partner_id){

        $commis_model = new Commission();

        $count = $commis_model->where(['top_id'=>$partner_id,'status'=>2])->where('type','in',[1,9])->group('order_id')->count();

        return $count;
    }




    /**
     * @param $partner_id
     * @功能说明:合伙人相关订单数量
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-23 11:53
     */
    public function partnerOrderPrice($partner_id){

        $commis_model = new Commission();

        $order_model  = new Order();

        $order_id = $commis_model->where(['top_id'=>$partner_id,'status'=>2])->where('type','in',[1,9])->column('order_id');

        $count = $order_model->where('id','in',$order_id)->sum('true_service_price');

        return $count;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-06 16:43
     * @功能说明:获取审核结果
     */
    public function checkAuthData($data){

        $cap_dis[] = ['user_id','=',$data['id']];

        $cap_dis[] = ['status','in',[1,2,3,4]];
        //查看是否是团长
        $cap_info = $this->where($cap_dis)->order('id desc')->find();

        $fx = !empty($cap_info)?$cap_info->toArray():[];
        //-1表示未申请团长，1申请中，2已通过，3取消,4拒绝
        $arr['fx_status'] = !empty($fx)?$fx['status']:-1;

        $arr['fx_text']   = !empty($fx)?$fx['sh_text']:'';

        $arr['wallet_status'] = in_array($arr['fx_status'],[2,3])?1:0;

        return $arr;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-12-26 15:09
     * @功能说明:获取分销员的统计数据
     */
    public function getResellerData($user_id,$type,$create_time=0,$resller_id=0, $count_start_time = 0, $count_end_time = 0)
    {
        //累计佣金
        $data['order_comm_cash']   = $this->getOrderPrice($user_id,$type,$create_time,0,2, $count_start_time, $count_end_time);
        //累计成交金额
        $data['order_cash']        = $this->getOrderServicePrice($user_id,$type,$create_time);
        //未入帐
        $data['not_recorded']      = $this->getOrderPrice($user_id,$type,$create_time,0,1);
        //累计订单量
        $data['total_order_count'] = $this->getOrderCount($user_id,$type,$create_time,0,$count_start_time, $count_end_time);
        //今日订单量
        $data['today_order_count'] = $this->getOrderCount($user_id,$type,$create_time,1);
        //累计邀请用户
        $data['total_user_count']  = $this->getInvUserCount($user_id,$type,$create_time,0, $count_start_time, $count_end_time);
        //今日邀请用户
        $data['today_user_count']  = $this->getInvUserCount($user_id,$type,$create_time,1);
        //提现
        $data['wallet_cash']       = $this->getWalletCash($user_id,$type,$create_time);
        //邀请下级
        $data['total_sub_count']   = $this->getInvSubCount($resller_id, $count_start_time, $count_end_time);

        return $data;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-12-26 15:49
     * @功能说明:获取成交金额
     */
    public function getOrderServicePrice($user_id,$type,$crate_time){

        $dis[] = ['a.top_id','=',$user_id];

        $dis[] = ['a.type','in',[1,14,15]];

        $dis[] = ['a.status','=',2];

        $dis[] = ['a.cash','>',0];

        if($type==1){

            $dis[] = ['b.create_time','>',$crate_time];
        }

        $commis_model = new Commission();

        $data = $commis_model->alias('a')
                ->join('massage_service_order_list b','a.order_id = b.id')
                ->where($dis)
                ->group('a.order_id')
                ->sum('b.true_service_price');

        return round($data,2);
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-12-26 14:17
     * @功能说明:累计邀请下级数量
     */
    public function getInvSubCount($id, $count_start_time=0, $count_end_time=0){

        $dis = [

            ['pid', '=', $id],

            ['status', '=', 2]
        ];

        if (!empty($count_start_time) && !empty($count_end_time)) {

            $dis[] = ['create_time', 'between', "{$count_start_time},{$count_end_time}"];
        }

        $count = $this->where($dis)->count();

        return $count;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-12-26 14:51
     * @功能说明:获取累计提现金额
     */
    public function getWalletCash($user_id,$type,$crate_time){

        $wallet_model = new Wallet();

        $dis[] = ['user_id','=',$user_id];

        $dis[] = ['type','=',4];

        $dis[] = ['status','<>',3];

        if($type==1){

            $dis[] = ['create_time','>',$crate_time];
        }

        $data = $wallet_model->where($dis)->sum('total_price');

        return round($data,2);
    }





    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-12-26 14:33
     * @功能说明:累计邀请客户数量
     */
    public function getInvUserCount($user_id,$type,$crate_time,$today=0, $count_start_time = 0, $count_end_time = 0)
    {

        $user_model = new User();

        $dis[] = ['pid','=',$user_id];

        if($type==1){

            $dis[] = ['create_time','>',$crate_time];
        }

        if($today==1){

            $count = $user_model->where($dis)->whereTime('create_time','today')->count();

        }else{

            if (!empty($count_start_time) && !empty($count_end_time)) {

                $dis[] = ['create_time', 'between', "{$count_start_time},{$count_end_time}"];
            }

            $count = $user_model->where($dis)->count();

        }

        return $count;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-12-26 14:33
     * @功能说明:累计获取佣金
     */
    public function getOrderPrice($user_id,$type,$crate_time,$today=0,$not_recorded=2, $count_start_time = 0, $count_end_time = 0)
    {

        $comm_model  = new Commission();

        $dis[] = ['top_id','=',$user_id];

        $dis[] = ['type','in',[1,14,15]];

        if($type==1){

            $dis[] = ['create_time','>',$crate_time];
        }

        if(!empty($not_recorded)){

            $dis[] = ['status','=',$not_recorded];
        }else{

            $dis[] = ['status','>',-1];
        }
        if($today==1){

            $count = $comm_model->where($dis)->whereTime('create_time','today')->sum('cash');

        }else{

            if (!empty($count_start_time) && !empty($count_end_time)) {

                $dis[] = ['create_time', 'between', "{$count_start_time},{$count_end_time}"];
            }

            $count = $comm_model->where($dis)->sum('cash');
        }

        return round($count,2);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-12-26 14:33
     * @功能说明:累计单量
     */
    public function getOrderCount($user_id,$type,$crate_time,$today=0, $count_start_time = 0, $count_end_time = 0)
    {

        $comm_model  = new Commission();

        $dis[] = ['top_id','=',$user_id];

        $dis[] = ['type','in',[1,14]];

        $dis[] = ['status','=',2];

        $dis[] = ['cash','>',0];

        if($type==1){

            $dis[] = ['create_time','>',$crate_time];
        }

        if($today==1){

            $count = $comm_model->where($dis)->whereTime('create_time','today')->group('order_id')->count();
        }else{
            if (!empty($count_start_time) && !empty($count_end_time)) {

                $dis[] = ['create_time', 'between', "{$count_start_time},{$count_end_time}"];
            }

            $count = $comm_model->where($dis)->group('order_id')->count();
        }

        return $count;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-01-05 18:16
     * @功能说明:获取分销员付费插件权限
     */
    public function getPayResellerAuth($uniacid){

        $auth = AdminMenu::getAuthList((int)$uniacid,['payreseller']);

        return $auth['payreseller'];
    }


}