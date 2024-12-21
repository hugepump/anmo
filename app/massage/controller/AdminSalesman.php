<?php
namespace app\massage\controller;
use app\AdminRest;
use app\massage\model\City;
use app\massage\model\Coach;
use app\massage\model\CoachTimeList;
use app\massage\model\Comment;
use app\massage\model\Commission;
use app\massage\model\Order;

use app\massage\model\Salesman;
use app\massage\model\SalesmanWater;
use app\massage\model\User;
use app\shop\model\OrderGoods;

use app\shop\model\Wallet;
use think\App;
use app\shop\model\Order as Model;
use think\facade\Db;


class AdminSalesman extends AdminRest
{


    protected $model;

    public function __construct(App $app) {

        parent::__construct($app);

        $this->model = new Salesman();

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-21 11:17
     * @功能说明:业务员列表
     */
    public function salesmanList(){

        $input = $this->_param;

        $dis[] = ['a.uniacid','=',$this->_uniacid];
        //是否是代理商
        if($this->_user['is_admin']==0){

            $admin_model = new \app\massage\model\Admin();

            $admin_arr = $admin_model->where('id','in',$this->admin_arr)->where(['salesman_auth'=>1])->column('id');

            $dis[] = ['a.admin_id','in',$admin_arr];

        }
        if(!empty($input['admin_id'])){

            $dis[] = ['a.admin_id','=',$input['admin_id']];

        }
        if(!empty($input['status'])){

            $dis[] = ['a.status','=',$input['status']];

        }else{

            $dis[] = ['a.status','>',-1];
        }

        if(!empty($input['salesman_id'])){

            $dis[] = ['a.id','<>',$input['salesman_id']];

        }

        if(!empty($input['start_time'])&&!empty($input['end_time'])){

            $dis[] = ['a.create_time','between',"{$input['start_time']},{$input['end_time']}"];
        }

        $where = [];

        if(!empty($input['name'])){

            $where[] = ['a.user_name','like','%'.$input['name'].'%'];

            $where[] = ['a.phone','like','%'.$input['name'].'%'];
        }

        $data = $this->model->adminDataList($dis,$where,$input['limit']);
        //获取各类状态的数量
        $list = [

            0=>'all',

            1=>'ing',

            2=>'pass',

            4=>'nopass',
        ];

        foreach ($list as $key=>$value){

            $dis = [

                'uniacid' => $this->_uniacid,
            ];

            if(!empty($key)){

                $dis['status'] = $key;
            }

            $where = [];
            //是否是代理商
            if($this->_user['is_admin']==0){

                $where[] = ['admin_id','in',$this->admin_arr];
            }

            $data[$value] = $this->model->where($dis)->where($where)->where('status','>',-1)->count();
        }

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-21 11:30
     * @功能说明:业务员详情
     */
    public function salesmanInfo(){

        $input = $this->_param;

        $dis = [

            'id' => $input['id']
        ];

        $data = $this->model->dataInfo($dis);

        $admin_model = new \app\massage\model\Admin();

        $user_modle  = new User();

        $admin = $admin_model->dataInfo(['id'=>$data['admin_id'],'status'=>1]);

        if(!empty($admin['salesman_auth'])){

            $data['admin_name'] = $admin['agent_name'];
        }else{

            $data['admin_id'] = 0;
        }

        $data['nickName']   = $user_modle->where(['id'=>$data['user_id'],'status'=>1])->value('nickName');

        if($data['balance']<0){

            $data['balance'] = getConfigSetting($this->_uniacid,'salesman_balance');
        }

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-21 11:31
     * @功能说明:审核业务员
     */
    public function checkSalesman(){

        $input = $this->_input;

        $diss = [

            'id' => $input['id']
        ];

        $salesman = $this->model->dataInfo($diss);

        if(isset($input['status'])&&in_array($input['status'],[2,4])&&$salesman['status']==1){

            $input['sh_time'] = time();
        }
        //删除需要判断佣金提现
        if(isset($input['status'])&&$input['status']==-1){

            if($salesman['cash']>0){

                $this->errorMsg('业务员还有佣金未提现');
            }

            $dis = [

                'top_id'  => $input['id'],

                'status'  => 1,

                'type'    => 12
            ];

            $cash_model = new Commission();

            $cash = $cash_model->dataInfo($dis);

            if(!empty($cash)){

                $this->errorMsg('业务员还有佣金未到账');

            }

            $dis = [

                'coach_id' => $input['id'],

                'status'  => 1,

                'type'    => 6
            ];

            $wallet_model = new \app\massage\model\Wallet();

            $wallet = $wallet_model->dataInfo($dis);

            if(!empty($wallet)){

                $this->errorMsg('业务员还有提现未处理');
            }

        }

        if(isset($input['cash'])){

            unset($input['cash']);
        }

        $res = $this->model->dataUpdate($diss,$input);

        return $this->success($res);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-21 11:17
     * @功能说明:业务员数据列表
     */
    public function salesmanDataList(){

        $input = $this->_param;

        $dis[] = ['a.uniacid','=',$this->_uniacid];
        //是否是代理商
        if($this->_user['is_admin']==0){

            $dis[] = ['a.admin_id','in',$this->admin_arr];

        }

        $dis[] = ['a.status','=',2];

        if(!empty($input['start_time'])&&!empty($input['end_time'])){

            $dis[] = ['a.sh_time','between',"{$input['start_time']},{$input['end_time']}"];

        }

        $where = [];

        if(!empty($input['name'])){

            $where[] = ['a.user_name','like','%'.$input['name'].'%'];

            $where[] = ['a.phone','like','%'.$input['name'].'%'];

            $where[] = ['b.nickName','like','%'.$input['name'].'%'];
        }

        $data = $this->model->adminDataList($dis,$where,$input['limit']);

        if(!empty($data['data'])){

            $wallet_model = new \app\massage\model\Wallet();

            $count_start_time = $input['count_start_time'] ?? 0;

            $count_end_time = $input['count_end_time'] ?? 0;

            foreach ($data['data'] as &$v){
                //累计提现
                $where = [
                    ['coach_id', '=', $v['id']],
                    ['type', '=', 6]
                ];

                if (!empty($count_start_time) && !empty($count_end_time)) {

                    $where[] = ['create_time', 'between', "{$count_start_time},{$count_end_time}"];
                }
                $v['wallet_price'] = $wallet_model->where($where)->where('status','in',[1,2])->sum('apply_price');
                //总成交金额
                $v['order_price']  = $this->model->salesmanOrderPrice($v['id'],0,1,$count_start_time,$count_end_time);

                $v['balance']      = floatval($v['balance']);

                $v['wallet_price'] = round($v['wallet_price'],2);

                $where = [
                    ['top_id','=',$v['id']],
                    ['status','=',2],
                    ['type', '=', 12]
                ];
                if (!empty($count_start_time) && !empty($count_end_time)) {

                    $where[] = ['create_time', 'between', "{$count_start_time},{$count_end_time}"];
                }
                $v['total_cash'] = round(Commission::where($where)->sum('cash'), 2);
            }
        }

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-07-14 14:03
     * @功能说明:设置业务员单独的佣金比例
     */
    public function setSalesmanBalance(){

        $input = $this->_input;

        $res = $this->model->where('id','in',$input['id'])->update(['balance'=>$input['balance']]);

        return $this->success($res);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-07-14 14:03
     * @功能说明:删除业务员单独的佣金比例
     */
    public function delSalesmanBalance(){

        $input = $this->_input;

        $res = $this->model->where('id','in',$input['id'])->update(['balance'=>-1]);

        return $this->success($res);
    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-21 10:33
     * @功能说明:申请业务员
     */
    public function addSalesman(){

        $input = $this->_input;

        $salesman_model = new Salesman();

        $dis[] = ['user_id','=',$input['user_id']];

        $dis[] = ['status','>',-1];

        $info = $salesman_model->dataInfo($dis);

        if(!empty($info)&&in_array($info['status'],[1,2,3])){

            $this->errorMsg('你已经申请过分销员了');
        }
//        //是否开启审核
//        if(getConfigSetting($this->_uniacid,'salesman_check_status')==1){
//
//            $status = 1;
//
//            $sh_time = 0;
//        }else{
//
//            $status = 2;
//
//            $sh_time = time();
//        }

        $admin_id = $this->_user['is_admin']==0?$this->_user['id']:0;

//        if($this->_user['is_admin']!=0){
//
//            $status = 2;
//        }

        if(!empty($input['admin_id'])){

            $admin_id = $input['admin_id'];
        }

        $insert = [

            'uniacid' => $this->_uniacid,

            'user_id' => $input['user_id'],

            'admin_id'=> $admin_id,

            'phone'   => $input['phone'],

            'user_name'=> $input['user_name'],

            'true_user_name' => !empty($input['true_user_name'])?$input['true_user_name']:$input['user_name'],

            'status'   => 2,

            'sh_time'  => time(),

          //  'sh_text'  => $input['sh_text'],

        ];

        $res = $salesman_model->dataAdd($insert);

        $id  = $salesman_model->getLastInsID();

        SalesmanWater::initWater($this->_uniacid,$id);

        return $this->success($res);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-03-29 19:00
     * @功能说明:用户列表
     */
    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-09-22 15:19
     * @功能说明:用户列表
     */
    public function noSalesmanUserList(){

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










}
