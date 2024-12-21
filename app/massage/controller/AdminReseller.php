<?php
namespace app\massage\controller;
use app\AdminRest;
use app\massage\model\Commission;
use app\massage\model\CommShare;
use app\massage\model\Config;
use app\massage\model\DistributionList;
use app\massage\model\ResellerRecommendCash;
use app\massage\model\User;
use app\massage\model\UserWater;
use app\massage\model\Wallet;
use app\payreseller\model\Order;
use longbingcore\wxcore\PayModel;
use longbingcore\wxcore\YsCloudApi;
use think\App;



class AdminReseller extends AdminRest
{


    protected $model;

    protected $user_model;

    protected $cash_model;

    protected $wallet_model;



    public function __construct(App $app) {

        parent::__construct($app);

        $this->model        = new DistributionList();

        $this->user_model   = new User();

        $this->cash_model   = new Commission();

        $this->wallet_model   = new Wallet();



    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-15 14:43
     * @功能说明:列表
     */
    public function resellerList(){

        $input = $this->_param;

        $dis[] = ['a.uniacid','=',$this->_uniacid];

        if(!empty($input['status'])){

            $dis[] = ['a.status','=',$input['status']];

        }else{

            $dis[] = ['a.status','>',-1];
        }

        if(!empty($input['reseller_id'])){

            $dis[] = ['a.id','<>',$input['reseller_id']];

        }

        if($this->_user['is_admin']==0){

            $admin_model = new \app\massage\model\Admin();

            $admin_arr = $admin_model->where('id','in',$this->admin_arr)->where(['reseller_auth'=>1])->column('id');

            $dis[] = ['a.admin_id','in',$admin_arr];
        }

        if(!empty($input['start_time'])&&!empty($input['end_time'])){

            $start_time = $input['start_time'];

            $end_time   = $input['end_time'];

            $dis[] = ['a.create_time','between',"$start_time,$end_time"];
        }

        $where = [];

        if(!empty($input['name'])){

            $where[] = ['a.user_name','like','%'.$input['name'].'%'];

            $where[] = ['a.mobile','like','%'.$input['name'].'%'];
        }

        $data = $this->model->adminDataList($dis,$input['limit'],$where);

        $list = [

            0=>'all',

            1=>'ing',

            2=>'pass',

            4=>'nopass'
        ];

        foreach ($list as $k=> $value){

            $dis_s = [];

            $dis_s[] =['uniacid','=',$this->_uniacid];

            if(!empty($k)){

                $dis_s[] = ['status','=',$k];

            }else{

                $dis_s[] = ['status','>',-1];

            }

            if($this->_user['is_admin']==0){

                $dis_s[] = ['admin_id', '=' ,$this->_user['admin_id']];
            }

            $data[$value] = $this->model->where($dis_s)->count();

        }

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-08-03 11:53
     * @功能说明:DXV RGWU TUFH RFCY
     */
    public function resellerInfo(){

        $input = $this->_param;

        $dis = [

            'id' => $input['id']
        ];

        $info = $this->model->dataInfo($dis);

        $user_model = new User();

        $admin_model = new \app\massage\model\Admin();

        $info['nickName'] = $user_model->where(['id'=>$info['user_id']])->value('nickName');

        $info['admin_name'] = $admin_model->where(['id'=>$info['admin_id'],'status'=>1])->value('agent_name');

        return $this->success($info);

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-03 00:15
     * @功能说明:审核(2通过,3取消,4拒绝)
     */
    public function resellerUpdate(){

        $input = $this->_input;

        $diss = [

            'id' => $input['id']
        ];

        $info = $this->model->dataInfo($diss);

        if(!empty($input['status'])&&in_array($input['status'],[2,4,-1])){

            $input['sh_time'] = time();

            if($input['status']==-1){

                $cash = $this->user_model->where(['id'=>$info['user_id']])->sum('new_cash');

                $this->user_model->where(['id'=>$info['user_id']])->update(['new_cash'=>0]);

                $this->user_model->where(['pid'=>$info['user_id']])->update(['pid'=>0]);

                $input['del_time'] = time();

                $input['del_cash'] = $cash;

                $water_model = new UserWater();

                $res = $water_model->updateCash($this->_uniacid,$info['user_id'],$cash,2,0,0,-1);

                if($res==false){

                    $this->errorMsg('删除失败');
                }

//                $fx_cash = $this->user_model->where(['id'=>$info['user_id']])->sum('new_cash');
//
//                if($fx_cash>0){
//
//                    $this->errorMsg('分销商还有佣金未提现');
//                }
//
//                $dis = [
//
//                    'top_id'  => $info['user_id'],
//
//                    'status'  => 1,
//
//                    'type'    => 1
//                ];
//
//                $cash = $this->cash_model->dataInfo($dis);
//
//                if(!empty($cash)){
//
//                    $this->errorMsg('分销商还有佣金未到账');
//
//                }
//
//                $dis = [
//
//                    'user_id' => $info['user_id'],
//
//                    'status'  => 1,
//
//                    'type'    => 4
//                ];
//
//                $wallet = $this->wallet_model->dataInfo($dis);
//
//                if(!empty($wallet)){
//
//                    $this->errorMsg('分销商还有提现未处理');
//
//                }

            }

        }

//        if($this->_user['is_admin']==0){
//
//            if(isset($input['balance'])&&isset($input['level_balance'])){
//
//                $agent_reseller_max_balance = getConfigSetting($this->_uniacid,'agent_reseller_max_balance');
//
//                if($input['balance']>$agent_reseller_max_balance||$input['level_balance']>$agent_reseller_max_balance){
//
//                    $this->errorMsg('分销比例不能大于平台设置比例,'.$agent_reseller_max_balance.'%');
//                }
//            }
//        }

        $data = $this->model->dataUpdate($diss,$input);

        if(isset($input['status'])){

            $update = [

                'is_fx' => 0
            ];

            if($input['status']==2){

                $update['is_fx'] = 1;

            }

            $this->user_model->dataUpdate(['id'=>$info['user_id']],$update);

        }

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-23 10:22
     * @功能说明:合伙人数据统计
     */
    public function partnerDataList(){

        $input = $this->_param;

        $dis[] = ['a.uniacid','=',$this->_uniacid];

        if($this->_user['is_admin']==0){

            $dis[] = ['b.admin_id', '=' ,$this->_user['admin_id']];
        }

        if(getFxStatus($this->_uniacid)==1){

            $dis[] = ['b.status','=',2];
        }

        $where = [];

        if(!empty($input['name'])){

            $where[] = ['a.nickName','like','%'.$input['name'].'%'];

            $where[] = ['b.user_name','like','%'.$input['name'].'%'];

            $where[] = ['b.mobile','like','%'.$input['name'].'%'];

        }

        if(!empty($input['start_time'])&&!empty($input['end_time'])){

            $dis[] = ['b.sh_time','between',"{$input['start_time']},{$input['end_time']}"];
        }

        $data = $this->model->userDataList($dis,$where,$input['limit']);

        if(!empty($data['data'])){

            $count_start_time = $input['count_start_time'] ?? 0;

            $count_end_time = $input['count_end_time'] ?? 0;

            foreach ($data['data'] as &$v){

                $del_time = $this->model->where(['user_id'=>$v['user_id'],'status'=>-1])->max('del_time');

                $fx_check = !empty($del_time)?1:0;

                $resller_data = $this->model->getResellerData($v['user_id'], $fx_check, $del_time, $v['id'], $count_start_time, $count_end_time);

                $v = array_merge($v,$resller_data);
            }
        }

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-12-26 11:15
     * @功能说明:下级用户
     */
    public function subUser(){

        $input = $this->_param;

        $dis[] = ['status','=',1];

        $dis[] = ['pid','=',$input['user_id']];

        if(!empty($input['name'])){

            $dis[] = ['nickName','like','%'.$input['name'].'%'];
        }

        $data = $this->user_model->where($dis)->field('id,nickName,avatarUrl,create_time,phone')->paginate($input['limit'])->toArray();

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-12-26 13:46
     * @功能说明:解除下级绑定用户关系
     */
    public function delSubUser(){

        $input = $this->_input;

        $res = $this->user_model->dataUpdate(['id'=>$input['user_id']],['pid'=>0]);

        return $this->success($res);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-12-26 11:24
     * @功能说明:下级分销员
     */
    public function subReseller(){

        $input = $this->_param;

        if(!empty($input['id'])){

            $dis[] = ['pid','=',$input['id']];
        }

        if(!empty($input['user_id'])){

            $cap_dis[] = ['user_id','=',$input['user_id']];

            $cap_dis[] = ['status','in',[2,3]];

            if($this->_user['is_admin']==0){

                $cap_dis[] = ['admin_id', '=' ,$this->_user['admin_id']];
            }
            $resller = $this->model->dataInfo($cap_dis);

            $resller_id = !empty($resller)?$resller['id']:-1;

            $dis[] = ['pid','=',$resller_id];
        }

        $dis[] = ['status','in',[2,3]];

        if(!empty($input['name'])){

            $dis[] = ['user_name','like','%'.$input['name'].'%'];
        }

        $data = $this->model->dataList($dis,$input['limit']);

        $config_model = new Config();

        $user_model   = new User();

     //   $config_info = $config_model->dataInfo(['uniacid'=>$this->_uniacid]);

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                if($v['status']==2){

                    $v['sh_time'] = !empty($v['sh_time'])?$v['sh_time']:$v['create_time'];
                }

                $v['avatarUrl'] = $user_model->where(['id'=>$v['user_id']])->value('avatarUrl');

                $v['nickName']  = $user_model->where(['id'=>$v['user_id']])->value('nickName');

                $del_time = $this->model->where(['user_id'=>$v['id'],'status'=>-1])->max('del_time');

                $fx_check = !empty($del_time)?1:0;
                //累计邀请用户
                $resller_data = $this->model->getResellerData($v['user_id'],$fx_check,$del_time,$v['id']);

                $v = array_merge($v,$resller_data);

                $v['balance'] = $v['balance']>=0?$v['balance']:getConfigSetting($v['uniacid'],'user_agent_balance');

                $v['level_balance'] = $v['level_balance']>=0?$v['level_balance']:getConfigSetting($v['uniacid'],'user_level_balance');
            }
        }

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-12-26 17:21
     * @功能说明:分销关系
     */
    public function resellerRelationshipTop(){

        $input = $this->_param;

        $user_model = new User();

        if($this->_user['is_admin']==0){

            $cap_dis[] = ['admin_id', '=' ,$this->_user['admin_id']];
        }

        $cap_dis[] = ['user_id','=',$input['user_id']];

        $cap_dis[] = ['status','in',[2,3]];

        $resller = $this->model->dataInfo($cap_dis);

        if(!empty($resller)){

            $resller['nickName'] = $user_model->where(['id'=>$resller['user_id']])->value('nickName');

            $resller['avatarUrl']= $user_model->where(['id'=>$resller['user_id']])->value('avatarUrl');
        }

        $data['user_info'] = !empty($resller)?$resller:[];

        if(!empty($resller)){

            $dis[] = ['id','=',$resller['pid']];

            $dis[] = ['status','in',[2,3]];

            $level_resller = $this->model->dataInfo($dis);

            if(!empty($level_resller)){

                $level_resller['nickName'] = $user_model->where(['id'=>$level_resller['user_id']])->value('nickName');

                $level_resller['avatarUrl']= $user_model->where(['id'=>$level_resller['user_id']])->value('avatarUrl');
            }

            $data['level_info'] = $level_resller;

        }else{

            $data['level_info'] = [];
        }

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-01-08 17:27
     * @功能说明:分销员付费订单
     */
    public function resellerOrderList(){

        $input = $this->_param;

        $order_model = new Order();

        $user_model  = new User();

        $comm_model  = new Commission();

        $dis[] = ['pay_type','=',2];

        $dis[] = ['uniacid','=',$this->_uniacid];

        if(!empty($input['order_code'])){

            $dis[] = ['order_code','like',"%".$input['order_code'].'%'];
        }

        if(!empty($input['start_time'])){

            $dis[] = ['create_time','between',"{$input['start_time']},{$input['end_time']}"];
        }

        $data = $order_model->dataList($dis,$input['limit']);

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $v['user_info']  = $user_model->where(['id'=>$v['user_id']])->field('nickName,avatarUrl')->find();
                //推荐人
                if(!empty($v['top_reseller_id'])){

                    $v['inv_user_name'] = $this->model->where(['id'=>$v['top_reseller_id']])->value('user_name');

                    $v['inv_comm_cash'] = $comm_model->where(['type'=>15,'order_id'=>$v['id'],'status'=>2])->sum('cash');
                }
            }
        }

        $data['total_pay_price'] = $order_model->where($dis)->sum('pay_price');

        foreach ($dis as &$vs){

            $vs[0] = 'a.'.$vs[0];
        }

        $data['total_comm_cash'] = $order_model->alias('a')
                                    ->join('massage_service_order_commission b','a.id = b.order_id')
                                    ->where($dis)
                                    ->where(['b.type'=>15,'b.status'=>2])
                                    ->group('b.id')
                                    ->sum('cash');
        //门槛总计
        $data['total_pay_price'] = round($data['total_pay_price'],2);
        //推荐费总计
        $data['total_comm_cash'] = round($data['total_comm_cash'],2);
        //平台利润
        $data['company_comm_cash'] = round($data['total_pay_price']-$data['total_comm_cash'],2);

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-05-24 11:33
     * @功能说明:修改推荐费
     */
    public function updateRecommendCash(){

        $input = $this->_input;

        $update = [

            'recommend_cash' => $input['recommend_cash'],

            'recommend_range' => $input['recommend_range'],

            'recommend_day' => $input['recommend_day'],

            'recommend_time' => time(),
        ];

        foreach ($input['id'] as $value){

            $res = $this->model->dataUpdate(['id'=>$value],$update);
        }

        return $this->success($res);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-05-31 16:33
     * @功能说明:推荐记录
     */
    public function recommendRecord(){

        $input = $this->_param;

        $recommend_model = new ResellerRecommendCash();

        $user_model = new User();

        $dis[] = ['a.uniacid','=',$this->_uniacid];

        if(!empty($input['reseller_name'])){

            $dis[] = ['b.user_name','like','%'.$input['reseller_name'].'%'];
        }

        if(!empty($input['start_time'])){

            $dis[] = ['a.create_time','between',"{$input['start_time']},{$input['end_time']}"];
        }

        if($this->_user['is_admin']==0){

            $dis[] = ['b.admin_id','=',$this->_user['admin_id']];
        }

        $data = $recommend_model->alias('a')
                ->join('massage_distribution_list b','a.reseller_id = b.id')
                ->where($dis)
                ->field('a.*,b.user_name as reseller_name')
                ->group('a.id')
                ->order('a.id desc')
                ->paginate($input['limit'])
                ->toArray();

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $v['user_info'] = $user_model->where(['id'=>$v['user_id']])->field('nickName,avatarUrl')->find();
            }
        }

        return $this->success($data);
    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-09-22 15:19
     * @功能说明:用户列表
     */
    public function noresellerUserList(){

        $input = $this->_param;

        if($this->_user['is_admin']==0&&empty($input['nickName'])){

            $where[] = ['id','=',-1];
        }

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['status','in',[0,1,2,3]];

        $user_id = $this->model->where($dis)->column('user_id');

        $where1 = [];

        if(!empty($input['nickName'])){

            if($this->_user['is_admin']==0){

                $where1[] = ['nickName','=',$input['nickName']];

                $where1[] = ['phone','=',$input['nickName']];

            }else{

                $where1[] = ['nickName','like','%'.$input['nickName'].'%'];

                $where1[] = ['phone','like','%'.$input['nickName'].'%'];
            }
        }

        $user_model = new User();

        $where[] = ['uniacid','=',$this->_uniacid];

        $where[] = ['id','not in',$user_id];

        $where[] = ['status', '=', 1];

        $list = $user_model->dataList($where,$input['limit'],$where1);

        return $this->success($list);

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-07-21 17:08
     * @功能说明:申请分销商
     */
    public function applyReseller(){

        $input = $this->_input;

        $distribution_model = new DistributionList();

        $dis[] = ['status','>',-1];

        $dis[] = ['user_id','=',$input['user_id']];

        $find = $distribution_model->dataInfo($dis);

        if(!empty($find)&&in_array($find['status'],[1,2,3])){

            $this->errorMsg('该用户已经申请分销员');
        }

        $insert = [

            'uniacid'  => $this->_uniacid,

            'user_id'  => $input['user_id'],

            'user_name'=> $input['user_name'],
            'balance'=> $input['balance'],
            'level_balance'=> $input['level_balance'],

            'true_user_name' => !empty($input['true_user_name'])?$input['true_user_name']:$input['user_name'],

            'mobile'   => $input['mobile'],

            'text'     => $input['text'],

            'status'   => 2,

            'pid'      => !empty($top)?$top['id']:0,

            'admin_id' => !empty($input['admin_id'])?$input['admin_id']:0,

            'sh_time'   => time(),

        ];

        $res = $distribution_model->dataAdd($insert);

        return $this->success($res);

    }




}
