<?php
namespace app\massage\model;

use app\BaseModel;
use app\node\info\PermissionNode;
use longbingcore\permissions\AdminMenu;
use think\facade\Db;

class Admin extends BaseModel
{
    //定义表名
    protected $name = 'shequshop_school_admin';


    /**
     * @param $value
     * @param $data
     * @功能说明:手机号加密
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-30 16:17
     */
    public function getPhoneAttr($value,$data){

        if(!empty($value)&&isset($data['uniacid'])){

            if(numberEncryption($data['uniacid'])==1){

                return  substr_replace($value, "****", 2,4);
            }
        }

        return $value;
    }


    /**
     * @param $value
     * @param $data
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-04-15 14:27
     */
    public function getLoginAuthPhoneAttr($value,$data){

        if(isset($value)&&isset($data['phone'])){

            if(empty($value)){

                $value = $data['phone'];
            }

            if(isset($data['uniacid'])&&numberEncryption($data['uniacid'])==1){

                return  substr_replace($value, "****", 2,4);
            }

            return $value;
        }

    }

    /**
     * @param $value
     * @param $data
     * @功能说明:判断代理商是否有发展技师的权限
     * @author chenniang
     * @DataTime: 2024-06-13 15:07
     */
    public function getAdminPidAttr($value,$data){

        if(!empty($value)){

            $admin_model = new Admin();

            $admin = $admin_model->where(['id'=>$value,'status'=>1,'sub_agent_auth'=>1])->count();

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
    public function dataList($dis,$page=10,$where=[]){

        $data = $this->where($dis)->where(function ($query) use ($where){
            $query->whereOr($where);
        })->order('id desc')->paginate($page)->toArray();

        return $data;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:43
     * @功能说明:
     */
    public function dataInfo($dis,$field='*'){

        $data = $this->where($dis)->field($field)->find();

        return !empty($data)?$data->toArray():[];

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-06-08 17:21
     * @功能说明:加盟商列表
     */
    public function adminUserList($dis,$page){

        $data = $this->alias('a')
                ->join('massage_service_user_list b','a.user_id = b.id','left')
                ->where($dis)
                ->field('a.*,b.nickName')
                ->group('a.id')
                ->order('a.id')
                ->paginate($page)
                ->toArray();

        return $data;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-24 18:21
     * @功能说明:初始化代理商名字
     */
    public function initAgentName(){

        $dis = [

            'agent_name' => '',
         ];

        $data = $this->where($dis)->select()->toArray();

        if(!empty($data)){

            foreach ($data as $v){

                if(isset($v['agent_name'])){

                    $this->dataUpdate(['id'=>$v['id']],['agent_name'=>$v['username']]);
                }

            }
        }

        return true;
    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-06-08 23:06
     * @功能说明:加盟商
     */
    function jionAdminCheck($input){

        if(!empty($input['username']&&$input['username']=='admin')){

            return ['code'=>500,'msg'=>'用户名不能和超管账号相同'];

        }

        $dis[] = ['user_id','=',$input['user_id']];

        $dis[] = ['is_admin','=',0];

        $dis[] = ['uniacid','=',$input['uniacid']];

        $dis[] = ['status','>',-1];

        if(!empty($input['id'])){

            $dis[] = ['id','<>',$input['id']];

        }

        $find = $this->where($dis)->find();

        if(!empty($find)){

            return ['code'=>500,'msg'=>'该用户已经绑定过加盟商'];

        }

        $where[] = ['username','=',$input['username']];

        $where[] = ['uniacid','=',$input['uniacid']];

        $where[] = ['status','>',-1];

        if(!empty($input['id'])){

            $where[] = ['id','<>',$input['id']];

        }

        $find = $this->where($where)->find();

        if(!empty($find)){

            return ['code'=>500,'msg'=>'已经有该用户名的账户'];

        }

        return true;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-10-21 14:06
     * @功能说明:获取代理商已经下级的id
     */
    public function getAdminAndSon($admin_id){

        $dis = [

            'admin_pid' => $admin_id
        ];

        $id = $this->where($dis)->column('id');

        $id[] = $admin_id;

        return $id;

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-16 10:45
     * @功能说明:代理商的分销比例
     */
    public function agentBalanceData($admin_id,$order){

        $admin = $this->dataInfo(['id'=>$admin_id,'status'=>1,'agent_coach_auth'=>1]);
        //平台抽层比列
        $order['admin_balance'] = !empty($admin)?$admin['balance']:0;

        $order['admin_id']      = !empty($admin)?$admin['id']:0;

        $auth = AdminMenu::getAuthList((int)$order['uniacid'],['caradmin']);

        if($auth['caradmin']==true){

            $order['caradmin'] = !empty($admin)?$admin['car_admin']:0;

        }else{

            $order['caradmin'] = 0;
        }
        //这里的city_admin 主要是找哪一级代理商是城市代理商
        if(!empty($admin)&&$admin['city_type']==1){

            $order['city_admin'] = 'surplus_cash';

            $order['city_admin_id'] = 'admin_id';
        }
        //县级代理商
        if(!empty($admin)&&!empty($admin['admin_pid'])&&$order['is_store_admin']==0){

            $order['level_balance'] = $admin['level_balance'];

            $order['admin_pid']     = $admin['level_balance']>0?$admin['admin_pid']:0;
            //查看是否还有上级
            $admin_pdata = $this->dataInfo(['id'=>$order['admin_pid'],'status'=>1]);
            //只有市才有上级
            if(!empty($admin_pdata)&&in_array($admin_pdata['city_type'],[1])){

                $order['p_level_balance'] = $admin_pdata['level_balance'];

                $order['p_admin_pid']     = $admin_pdata['level_balance']>0?$admin_pdata['admin_pid']:0;
                //这里的city_admin 主要是找哪一级代理商是城市代理商
                $order['city_admin']      = 'level_cash';

                $order['city_admin_id']   = 'admin_pid';
            }
        }

        if($order['free_fare']==3&&$order['admin_id']==0){

            $order['free_fare'] = 1;
        }

        return $order;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-16 17:03
     * @功能说明:代理商佣金
     */
    public function agentCashData($order,$admin_cash){

        if(isset($order['p_level_balance'])){
            //上级代理提成
            $order['p_level_cash'] = round($order['p_level_balance']*$order['true_service_price']/100,2);

            $order['p_level_cash'] = $order['p_level_cash'] - $admin_cash>0?$admin_cash:$order['p_level_cash'];

            $admin_cash -= $order['p_level_cash'];

        }

        if(isset($order['level_balance'])){
            //上级代理提成
            $order['level_cash'] = round($order['level_balance']*$order['true_service_price']/100,2);

            $order['level_cash'] = $order['level_cash'] - $admin_cash>0?$admin_cash:$order['level_cash'];

            $admin_cash -= $order['level_cash'];

        }

        $order['over_cash'] = $admin_cash;

        return $order;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-16 10:45
     * @功能说明:代理商的分销比例
     */
    public function agentBalanceDataCustom($admin_id,$order){

        $admin = $this->dataInfo(['id'=>$admin_id]);

        $order['admin_id'] = $admin_id;
        //县级代理商
        if(!empty($admin)){

            $distri_model = new DistributionConfig();
            //查看是市代还是县代
            $name = $admin['city_type']==1?'getCityCash':'getDistrictCash';

            $config = $distri_model->dataInfo(['uniacid'=>$order['uniacid'],'name'=>$name]);

            $order[$config['balance_name']] = $config['balance'];

            $order['admin_balance_name']    = $config['balance_name'];

            $order['admin_cash_name']       = $admin['city_type']==1?'city_cash':'district_cash';
            //查看是否还有上级
            $admin_pdata = $this->dataInfo(['id'=>$admin['admin_pid']]);
            //只有市才有上级
            if(!empty($admin_pdata)&&$admin_pdata['city_type']==1&&$admin['city_type']==2){

                $config = $distri_model->dataInfo(['uniacid'=>$order['uniacid'],'name'=>'getCityCash']);

                $order[$config['balance_name']] = $config['balance'];

                $order['admin_pid']     = $admin['admin_pid'];

                $order['level_balance_name'] = $config['balance_name'];

                $order['level_cash_name']    = 'city_cash';
            }

        }

        return $order;

    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-11-03 13:38
     * @功能说明:获取代理商以及下级id
     */
    public function getAdminId($user){

        if($user['is_admin']==3){

            $user = $this->dataInfo(['id'=>$user['admin_id']]);
        }

        if(empty($user)||!in_array($user['is_admin'],[0])){

            return [];
        }
        //县级代理
        if(isset($user['city_type'])&&$user['city_type']==2){

            return [$user['id']];
        }
        //市级代理
        if(isset($user['city_type'])&&$user['city_type']==1){

            $admin_model = new \app\massage\model\Admin();

            $id = $admin_model->where(['admin_pid'=>$user['id']])->column('id');

            $id[] = $user['id'];

            return $id;
        }
        //省级代理
        if(isset($user['city_type'])&&$user['city_type']==3){

            $admin_model = new \app\massage\model\Admin();

            $id = $admin_model->where(['admin_pid'=>$user['id']])->column('id');

            $son_id = $admin_model->where('admin_pid','in',$id)->column('id');

            $id = array_merge($id,$son_id);

            $id[] = $user['id'];

            return $id;
        }

        return [];
    }


    /**
     * @param $data
     * @功能说明:是否是管理员
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-06 16:50
     */
    public function checkAuthData($data){

        $admin_where = [

            'user_id' => $data['id'],

            'status'  => 1,

          //  'agent_coach_auth' => 1
        ];

        $admin_user = $this->dataInfo($admin_where);
        //是否是加盟商
        $arr['is_admin'] = !empty($admin_user)?1:0;

        $arr['admin_id'] = !empty($admin_user)?$admin_user['id']:0;

        $arr['agent_name'] = !empty($admin_user)?$admin_user['agent_name']:'';

        $arr['wallet_status'] = in_array($arr['is_admin'],[1])?1:0;

        return $arr;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-04 17:54
     * @功能说明:校验添加文件
     */
    public function checkDatas($uniacid){

        $p = new PermissionNode($uniacid);

        $auth_num = $p->getAuthNumber();

        $num = $this->where(['uniacid'=>$uniacid,'is_admin'=>2])->where('status','>',-1)->count();

        if($auth_num<=$num){

            return ['code'=>500,'msg'=>'授权数量不足'.$auth_num.'-'.$num];
        }

        return true;
    }



    public function checkAdminCash($admin_id){

        $comm_model = new Commission();

        $wallet_model = new Wallet();

        $cash = $comm_model->where(['status'=>2,'admin_id'=>$admin_id])->where('type','in',[2,5,6,13])->sum('cash');

        $wallet = $wallet_model->where(['user_id'=>$admin_id])->where('type','=',3)->where('status','in',[1,2,4,5])->sum('total_price');

        return $cash - $wallet;

    }


    /**
     * @param $dis
     * @param $day
     * @param $limit
     * @功能说明:代理商业绩排行
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-05-15 15:01
     */
    public function agentDataTop($dis,$limit,$start_time,$end_time){

        $data = $this->alias('a')
                ->join('massage_service_order_list b',"a.id = b.admin_id AND b.pay_time>0 AND b.create_time between $start_time AND $end_time",'left')
                ->where($dis)
                ->field('a.agent_name,ifnull(round(sum(if(b.coach_refund_time>0,0,b.true_service_price)),2),0) as sale_price,a.id')
                ->group('a.id')
                ->order('sale_price desc,a.id desc')
                ->paginate($limit)
                ->toArray();

        return $data;
    }




    public static function adminCityData($admin_id){

        $city_id = Admin::where(['id'=>$admin_id])->value('city_id');

        $city = City::where(['id'=>$city_id])->field('city_type,province,city,area')->find();

        return $city;

    }


}