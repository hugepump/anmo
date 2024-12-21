<?php
namespace app\abnormalorder\controller;
use app\abnormalorder\model\OrderInfo;
use app\abnormalorder\model\OrderInfoHandle;
use app\abnormalorder\model\OrderList;
use app\abnormalorder\model\OrderProcess;
use app\abnormalorder\model\OrderRole;
use app\abnormalorder\model\OrderWander;
use app\AdminRest;


use app\massage\model\Admin;
use app\massage\model\CashUpdateRecord;
use app\massage\model\Coach;
use app\massage\model\Order;
use app\massage\model\OrderAddress;
use app\node\model\RoleAdmin;
use app\node\model\RoleList;
use think\App;


use think\facade\Db;


class AdminOrder extends AdminRest
{

    protected $model;

    protected $process_model;

    protected $info_model;

    public function __construct(App $app) {

        parent::__construct($app);

        $this->model = new OrderList();

        $this->process_model = new OrderProcess();

        $this->info_model = new OrderInfo();

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-11-27 17:10
     * @功能说明:标记异常订单
     */
    public function orderAdd(){

        $input = $this->_input;

        $order_model = new Order();

        $admin_id = $order_model->where(['id'=>$input['order_id']])->value('admin_id');

        $type = empty($admin_id)?1:2;
        //获取第一个流程
        $process_id = $this->process_model->where(['uniacid'=>$this->_uniacid,'status'=>1,'type'=>$type])->order('top,id desc')->value('id');

        $insert = [

            'uniacid' => $this->_uniacid,
            //订单id
            'order_id'=> $input['order_id'],
            //类型
            'type'    => $input['type'],
            //差评原因
            'bad_text'=> $input['bad_text'],
            //客服处理意见
            'customer_text'=> $input['customer_text'],
            //扣除金额
            'deduct_cash'=> $input['deduct_cash'],

            'process_id' => $process_id,

            'user_id'    => $this->_user['id'],
        ];

        $res = $this->model->dataAdd($insert);

        $order_id = $this->model->getLastInsID();
        //添加流程
        $process = $this->process_model->where(['uniacid'=>$this->_uniacid,'status'=>1,'type'=>$type])->order('top,id desc')->select()->toArray();

        if(!empty($process)){

            foreach ($process as $k=>$v){

                $insert = [

                    'uniacid'    => $this->_uniacid,

                    'order_id'   => $order_id,

                    'process_id' => $v['id'],

                    'top'        => $k,

                    'is_default' => 1,

                    'sub_type'   => $v['sub_type'],
                ];

                $this->info_model->dataAdd($insert);

                if($k==0){

                    $info_id = $this->info_model->getLastInsID();

                    $this->model->dataUpdate(['id'=>$order_id],['info_id'=>$info_id]);
                }
            }
        }
        return $this->success($order_id);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-12-14 13:37
     * @功能说明:异常订单详情
     */
    public function orderInfo(){

        $input = $this->_param;

        $process_model = new OrderProcess();

        $handle_model  = new OrderInfoHandle();

        $wander_model  = new OrderWander();

        $data = $this->model->dataInfo(['id'=>$input['id']]);

        $data = $this->model->getOrderResult($data);
        //扣款时间
        $data['deduct_time'] = $handle_model->where(['order_id'=>$data['id']])->where('status','>',1)->where('deduct_cash','>',0)->value('create_time');

        $process = $this->info_model->where(['order_id'=>$data['id']])->where('status','>',-1)->order('top,id desc')->select()->toArray();

        if(!empty($process)){

            foreach ($process as &$v){
                //已经处理
                if($v['status']>1){

                    $v['role_list'] = $handle_model->handleUserList($v['id']);

                }else{
                    //待处理
                    $process_info = $process_model->dataInfo(['id'=>$v['process_id']]);

                    $v['role_list'] = $process_model->getRoleData($v['process_id'],$v['pass_type'],$v['id']);
                }
                //是否已经扣款
                if($data['is_deduct']==1){

                    $v['deduct_status'] = 0;

                }else{
                    //是否有扣款权限
                    $v['deduct_status'] = isset($process_info['deduct_status'])?$process_info['deduct_status']:0;
                    //流转流程
                    if(!empty($v['pass_type'])){

                        $wander = $wander_model->dataInfo(['process_id'=>$v['process_id'],'pass_type'=>$v['pass_type'],'status'=>1]);

                        $v['deduct_status'] = isset($wander['deduct_status'])?$wander['deduct_status']:$v['deduct_status'];
                    }
                }
                //是否可以处理
                if($v['status']==1){

                    $v['can_handle'] = $this->model->getCanHandleUser($data,$this->_user,$v['process_id']);
                }else{

                    $v['can_handle'] = 0;
                }
                //只能处理一次
                $handel = $handle_model->where(['user_id'=>$this->_user['id'],'order_info_id'=>$v['id']])->find();

                if(!empty($handel)){

                    $v['can_handle'] = 0;
                }
            }
        }

        $arr['info']    = $data;

        $arr['process'] = $process;

        return $this->success($arr);
    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-12-13 17:07
     * @功能说明:
     */
    public function updateOrder(){

        $input = $this->_input;

        $insert = [

            'uniacid' => $this->_uniacid,
            //订单id
            'order_id'=> $input['order_id'],
            //类型
            'type'    => $input['type'],
            //差评原因
            'bad_text'=> $input['bad_text'],
            //客服处理意见
            'customer_text'=> $input['customer_text'],
            //扣除金额
            'deduct_cash'=> $input['deduct_cash'],

            'first_cancel' => 0
        ];

        $res = $this->model->dataUpdate(['id'=>$input['id']],$insert);

        return $this->success($res);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-12-11 18:13
     * @功能说明:处理订单
     */
    public function handleOrder(){

        $input = $this->_input;

        $info = $this->info_model->dataInfo(['id'=>$input['id']]);

        if(empty($info)){

            $this->errorMsg('参数错误');
        }

        $start = $this->info_model->where(['order_id'=>$info['order_id'],'status'=>1])->order('top,id desc')->value('id');

        if(empty($start)){

            $this->errorMsg('参数错误2');

        }
        //必须前面的审核了，后面的才能审核
        if($info['id']!=$start){

            $this->errorMsg('参数错误1');
        }

        $key = 'abnormalorder_handle'.$input['id'];

        incCache($key,1,$this->_uniacid);

        if(getCache($key,$this->_uniacid)!=1){

            decCache($key,1,$this->_uniacid);

            $this->errorMsg('当前订单正在被处理');
        }

        $deduct_cash = !empty($input['deduct_cash'])?$input['deduct_cash']:0;

        Db::startTrans();

        $insert = [

            'uniacid' => $this->_uniacid,

            'order_info_id' => $input['id'],

            'status' => $input['status'],

            'deduct_cash' => $deduct_cash,

            'text' => !empty($input['text'])?$input['text']:'',

            'process_id' => $info['process_id'],

            'order_id' => $info['order_id'],

            'user_id'  => $this->_user['id']
        ];

        $handle_model = new OrderInfoHandle();

        $process_model= new OrderProcess();
        //添加处理情况
        $handle_model->dataAdd($insert);

        $is_next = 1;
        //所有人审核才能进入下一流程
        if($info['sub_type']==2&&$input['status']==2&&$this->_user['is_admin']!=1){

            $process_info = $process_model->getRoleData($info['process_id'],$info['pass_type'],$info['id']);
            //说明没结束
            if(in_array(1,array_column($process_info,'handle_status'))){

                $is_next = 0;
            }
        }

        if($is_next==1){

            $update = [

                'status' => $input['status'],

                'create_time' => time()
            ];

            $res = $this->info_model->dataUpdate(['id'=>$input['id']],$update);
            //到下一个流程
            if($update['status']==2){

                $process_id = $this->info_model->where(['order_id'=>$info['order_id'],'status'=>1])->where('top','>',$info['top'])->order('top,id desc')->value('process_id');

                $info_id = $this->info_model->where(['order_id'=>$info['order_id'],'status'=>1])->where('top','>',$info['top'])->order('top,id desc')->value('id');

                $this->model->dataUpdate(['id'=>$info['order_id']],['process_id'=>$process_id,'info_id'=>$info_id]);
            }
        }

        $wander_model = new OrderWander();

        if($info['pass_type']==0){
            //正常流程
            if($info['is_cancel']==0){
                //拒绝
                if($input['status']==3){
                    //返回上一个流程
                    $this->info_model->completionProcess($info);
                }
            }else{

                $pass_type = $input['status']==2?1:2;
                //查看有无拒绝流转流程
                $wander = $wander_model->dataInfo(['process_id'=>$info['process_id'],'pass_type'=>$pass_type,'status'=>1]);

                if(!empty($wander)){

                    if(($input['status']==2&&$is_next==1)||$input['status']==3){
                        //流转
                        $this->info_model->wanderProcess($info,$wander);
                    }
                }elseif($input['status']==3&&empty($wander)){
                    //返回上一个流程
                    $this->info_model->completionProcess($info);
                }
            }
        }
        //给技师扣款
        if(isset($input['deduct_cash'])&&$input['deduct_cash']>0){

            $find = $this->model->dataInfo(['id'=>$info['order_id'],'is_deduct'=>1]);

            if(!empty($find)){

                decCache($key,1,$this->_uniacid);

                Db::rollback();

                $this->errorMsg('该订单已经扣款');
            }

            $order_model = new Order();

            $order = $this->model->dataInfo(['id'=>$info['order_id']]);

            $coach_id = $order_model->where(['id'=>$order['order_id']])->value('coach_id');
            //开始扣款
            $record_model = new CashUpdateRecord();

            $res = $record_model->recordAdd($coach_id,$input['deduct_cash'],0,$this->_user['id'],'异常订单扣款',$info['id']);

            if(!empty($res['code'])){

                decCache($key,1,$this->_uniacid);

                Db::rollback();

                $this->errorMsg($res['msg']);
            }

            $this->info_model->dataUpdate(['id'=>$info['id']],['deduct_cash'=>$input['deduct_cash']]);

            $this->model->dataUpdate(['id'=>$info['order_id']],['is_deduct'=>1]);
        }

        $next = $this->info_model->where(['order_id'=>$info['order_id'],'status'=>1])->where('top','>',$info['top'])->find();
        //结束
        if(empty($next)&&$is_next==1){

            $update = [

                'status' => $input['status'],

                'is_handle' => 1,

                'end_time'  => time(),

                'end_user_id'=> $this->_user['id'],

                'info_id'    => 0
            ];

            $this->model->dataUpdate(['id'=>$info['order_id']],$update);
        }

        Db::commit();

        decCache($key,1,$this->_uniacid);

        return $this->success(true);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-11-28 14:13
     * @功能说明:待处理异常订单列表
     */
    public function pendingOrderList(){

        $input = $this->_param;

        $adminRole_mdoel = new RoleAdmin();

        $dis[] = ['a.uniacid','=',$this->_uniacid];

        $dis[] = ['a.is_handle','=',0];

        $dis[] = ['a.first_cancel','=',0];

        if(!empty($input['order_code'])){

            $dis[] = ['b.order_code','like','%'.$input['order_code'].'%'];
        }

        if(!empty($input['type'])){

            $dis[] = ['a.type','=',$input['type']];
        }

        if(!empty($input['start_time'])){

            $dis[] = ['a.create_time','between',"{$input['start_time']},{$input['end_time']}"];
        }

        $this->_user['admin_id'] = !empty($this->_user['admin_id'])?$this->_user['admin_id']:$this->_user['id'];

        if($this->_user['is_admin']==2){

            $role = $adminRole_mdoel->getUserRole($this->_user['id'],$this->_uniacid);

            $where = [

                ['d.user_id','=',$this->_user['id']],

                ['d.type','=',2],
            ];

            $where1 =[
                ['d.user_id','in',$role],

                ['d.type','=',1]
            ];

        }elseif ($this->_user['is_admin']==0){

            $where = [

                ['b.admin_id' ,'=', $this->_user['admin_id']],

                ['d.type','=',  3],
            ];
        }

        if(empty($where1)&&empty($where)){

            $where = $where1 = ['a.uniacid','=',$this->_uniacid];

        }elseif(empty($where)){

            $where = ['a.uniacid','=',-1];

        }elseif (empty($where1)){

            $where1 = ['a.uniacid','=',-1];
        }

        $user_id = $this->model->getAdminUser($this->_user);

        $user_id = implode(',',$user_id);

        $data = $this->model->alias('a')
                ->join('massage_service_order_list b','a.order_id = b.id')
                ->join('massage_service_abnormal_order_role d','d.process_id = a.process_id AND a.pass_type = d.pass_type','left')
                ->join('massage_service_abnormal_order_info_handle e',"a.info_id = e.order_info_id AND e.user_id in ($user_id)",'left')
                ->where($dis)
                ->whereNull('e.id')
                ->where(function ($query) use ($where,$where1){
                    $query->whereOr([$where,$where1]);
                })
                ->field('a.*,b.order_code,b.coach_id,b.create_time as order_create_time,b.admin_id')
                ->group('a.id')
                ->order('a.id desc')
                ->paginate($input['limit'])
                ->toArray();

        if(!empty($data['data'])){

            $coach_model = new Coach();

            $address_model = new OrderAddress();

            $admin_model = new Admin();

            $handle_model  = new OrderInfoHandle();

            foreach ($data['data'] as &$v){
                //技师名字
                $v['coach_name'] = $coach_model->where(['id'=>$v['coach_id']])->value('coach_name');
                //下单用户名称
                $v['user_name']  = $address_model->where(['order_id'=>$v['order_id']])->value('user_name');
                //代理商名称
                $v['admin_name'] = $admin_model->where(['id'=>$v['admin_id']])->value('agent_name');
                //获取首次处理人最终处理人
                $v = $this->model->getOrderResult($v);
                //扣款时间
                $v['deduct_time'] = $handle_model->where(['order_id'=>$v['id']])->where('status','>',1)->where('deduct_cash','>',0)->value('create_time');
            }
        }

        $data['already_count'] = $this->model->getAlreadyOrderCount($this->_uniacid,$this->_user);

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-11-28 14:58
     * @功能说明:已经处理的异常订单列表
     */
    public function alreadyOrderList(){

        $input = $this->_param;

        $dis[] = ['a.uniacid','=',$this->_uniacid];

        $dis[] = ['a.status','>',-1];

        if(!empty($input['order_code'])){

            $dis[] = ['b.order_code','like','%'.$input['order_code'].'%'];
        }

        if(!empty($input['type'])){

            $dis[] = ['a.type','like',$input['type']];
        }

        if(!empty($input['start_time'])){

            $dis[] = ['a.create_time','between',"{$input['start_time']},{$input['end_time']}"];
        }

        if($this->_user['is_admin']!=1){

            $user_id = $this->model->getAdminUser($this->_user);

            $dis[] = ['d.user_id','in',$user_id];
        }

        if(isset($input['is_handle'])){

            $dis[] = ['a.is_handle','=',$input['is_handle']];
        }

        $data = $this->model->alias('a')
            ->join('massage_service_order_list b','a.order_id = b.id')
            ->join('massage_service_abnormal_order_info_handle d','a.id = d.order_id')
            ->where($dis)
            ->field('a.*,b.order_code,b.coach_id,b.create_time as order_create_time,b.admin_id')
            ->group('a.id')
            ->order('a.id desc')
            ->paginate($input['limit'])
            ->toArray();

        if(!empty($data['data'])){

            $coach_model = new Coach();

            $address_model = new OrderAddress();

            $admin_model = new Admin();

            $handle_model  = new OrderInfoHandle();

            foreach ($data['data'] as &$v){
                //技师名字
                $v['coach_name'] = $coach_model->where(['id'=>$v['coach_id']])->value('coach_name');
                //下单用户名称
                $v['user_name']  = $address_model->where(['order_id'=>$v['order_id']])->value('user_name');
                //代理商名称
                $v['admin_name'] = $admin_model->where(['id'=>$v['admin_id']])->value('agent_name');
                //获取首次处理人最终处理人
                $v = $this->model->getOrderResult($v);
                //扣款时间
                $v['deduct_time'] = $handle_model->where(['order_id'=>$v['id']])->where('status','>',1)->where('deduct_cash','>',0)->value('create_time');
            }
        }

        $data['pending_count'] = $this->model->getPendingOrderCount($this->_uniacid,$this->_user['admin_id'],$this->_user['is_admin']);

        return $this->success($data);
    }















}
