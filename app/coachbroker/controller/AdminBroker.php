<?php
namespace app\coachbroker\controller;
use app\AdminRest;
use app\coachbroker\model\BrokerLevel;
use app\coachbroker\model\CoachBroker;
use app\massage\model\City;
use app\massage\model\Coach;
use app\massage\model\CoachLevel;
use app\massage\model\Commission;
use app\massage\model\Config;
use app\massage\model\ConfigSetting;
use app\massage\model\DistributionList;
use app\massage\model\User;
use app\massage\model\Wallet;
use longbingcore\wxcore\YsCloudApi;
use think\App;



class AdminBroker extends AdminRest
{


    protected $model;

    protected $user_model;

    protected $cash_model;

    protected $wallet_model;

    protected $level_model;

    public function __construct(App $app) {

        parent::__construct($app);

        $this->model        = new CoachBroker();

        $this->user_model   = new User();

        $this->cash_model   = new Commission();

        $this->wallet_model = new Wallet();

        $this->level_model  = new BrokerLevel();

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-15 14:43
     * @功能说明:列表
     */
    public function brokerList(){

        $input = $this->_param;

        $dis[] = ['a.uniacid','=',$this->_uniacid];

        if(!empty($input['status'])){

            $dis[] = ['a.status','=',$input['status']];

        }else{

            $dis[] = ['a.status','>',-1];
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

        $level_model = new BrokerLevel();

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $v['balance'] = $level_model->getBrokerBalance($v['id'],$v['uniacid']);
            }
        }

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

            $data[$value] = $this->model->where($dis_s)->count();
        }

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-08-03 11:53
     * @功能说明:经纪人详情
     */
    public function brokerInfo(){

        $input = $this->_param;

        $dis = [

            'id' => $input['id']
        ];

        $info = $this->model->dataInfo($dis);

        $user_model = new User();

        $info['nickName'] = $user_model->where(['id'=>$info['user_id']])->value('nickName');

        return $this->success($info);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-12-19 18:23
     * @功能说明:添加经纪人
     */
    public function brokerAdd(){

        $input = $this->_input;

        $find = $this->model->where(['user_id'=>$input['user_id']])->where('status','>',-1)->find();

        if(!empty($find)&&in_array($find['status'],[1,2,3])){

            $this->errorMsg('已经申请过经纪人了');
        }

        $insert = [

            'uniacid'  => $this->_uniacid,

            'user_id'  => $input['user_id'],

            'user_name'=> $input['user_name'],

            'true_user_name' => !empty($input['true_user_name'])?$input['true_user_name']:$input['user_name'],

            'mobile'   => $input['mobile'],

            'text'     => $input['text'],

            'sh_time'  => time(),

            'status'   => 2,
        ];

        if(!empty($find)&&$find['status']==4){

            $res = $this->model->dataUpdate(['id'=>$find['id']],$insert);

        }else{

            $res = $this->model->dataAdd($insert);
        }

        return $this->success($res);
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-03 00:15
     * @功能说明:审核(2通过,3取消,4拒绝)
     */
    public function brokerUpdate(){

        $input = $this->_input;

        $diss = [

            'id' => $input['id']
        ];

        $info = $this->model->dataInfo($diss);

        if(!empty($input['status'])&&in_array($input['status'],[2,4,-1])){

            if($info['status']==1){

                $input['sh_time'] = time();
            }

            if($input['status']==-1){

                if($info['cash']>0){

                   // $this->errorMsg('分销商还有佣金未提现');
                }

               // $coach_model = new CoachBroker();

               // $coach_model->where(['broker_id'=>$input['id']])->update(['']);

//                $dis = [
//
//                    'top_id'  => $info['user_id'],
//
//                    'status'  => 1,
//
//                    'type'    => 9
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

        if(isset($input['cash'])){

            unset($input['cash']);
        }

        $data = $this->model->dataUpdate($diss,$input);

        return $this->success($data);
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-23 10:22
     * @功能说明:经纪人数据统计
     */
    public function brokerDataList(){

        $input = $this->_param;

        $dis[] = ['a.uniacid','=',$this->_uniacid];

        $dis[] = ['b.status','=',2];

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

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-12-20 16:43
     * @功能说明:不是经纪人的用户
     */
    public function noBrokerUserList(){

        $input = $this->_param;

        $user_model = new User();

        $user_id = $this->model->where(['uniacid'=>$this->_uniacid])->where('status','in',[1,2,3])->column('user_id');

        $dis[] = ['id','not in',$user_id];

        $dis[] = ['status','=',1];

        $where = [];

        if(!empty($input['name'])){

            $where[] = ['nickName','like','%'.$input['name'].'%'];

            $where[] = ['phone','like','%'.$input['name'].'%'];
        }

        $data = $user_model->dataList($dis,$input['limit'],$where,'id,nickName,avatarUrl,phone');

        return $this->success($data);
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-23 14:30
     * @功能说明:合伙人邀请的技师
     */
    public function brokerCoachList(){

        $input = $this->_param;

        $dis[] = ['status','=',2];

        $dis[] = ['broker_id','=',$input['id']];

        if(!empty($input['coach_name'])){

            $dis[] = ['coach_name','like','%'.$input['coach_name'].'%'];

        }

        $coach_model = new Coach();

        $data = $coach_model->where($dis)->field('admin_id,id,coach_name,work_img,city_id,sh_time')->order('partner_time desc,id desc')->paginate($input['limit'])->toArray();

        if(!empty($data['data'])){

            $config_model = new Config();

            $level_model  = new CoachLevel();

            $city_model   = new City();

            $admin_model = new \app\massage\model\Admin();

            $config = $config_model->dataInfo(['uniacid'=>$this->_uniacid]);

            $level_cycle = $config['level_cycle'];

          //  $is_current  = $config['is_current'];

            foreach ($data['data'] as &$v){

                $v['city'] = $city_model->where(['id'=>$v['city_id'],'status'=>1])->value('city');

                $admin = $admin_model->dataInfo(['id'=>$v['admin_id'],'status'=>1]);
                //代理商
                $v['admin_name'] = !empty($admin)?$admin['agent_name']:'平台';
                //周期业绩
                $v['order_price']= $level_model->getMinPrice($v['id'],$level_cycle,0,1,$input['id']);
            }
        }
        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-12-20 17:26
     * @功能说明:配置详情
     */
    public function configInfo(){

        $arr = [
            //经纪人申请端口 1前端可以申请 0不能
            'broker_apply_port',
            //经纪人返佣模式 0固定 1浮动
            'broker_cash_type',
            //经纪人海报
            'broker_poster',
            //经纪人返佣金
            'coach_agent_balance',
            //合伙人佣金技师承担比例
            'partner_coach_balance',
            //合伙人佣金代理商承担比例
            'partner_admin_balance',
        ];

        $data = getConfigSettingArr($this->_uniacid,$arr);

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-12-20 17:29
     * @功能说明:编辑配置
     */
    public function configUpdate(){

        $input = $this->_input;

        $config_model = new ConfigSetting();

        $arr = [
            //经纪人申请端口 1前端可以申请 0不能
            'broker_apply_port',
            //经纪人返佣模式 0固定 1浮动
            'broker_cash_type',
            //经纪人海报
            'broker_poster',
            //经纪人返佣金
            'coach_agent_balance',
            //合伙人佣金技师承担比例
            'partner_coach_balance',
            //合伙人佣金代理商承担比例
            'partner_admin_balance',
        ];

        foreach ($input as $key=>$value){

            if(!in_array($key,$arr)){

                $this->errorMsg('请求不合法');

            }
        }

        $res = $config_model->dataUpdate($input,$this->_uniacid);

        return $this->success($res);

    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-04 18:53
     * @功能说明:等级列表
     */
    public function levelList(){

        $input = $this->_param;

        $this->level_model->initTop($this->_uniacid);

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['status','=',1];

        $dis = [

            'uniacid' => $this->_uniacid,

            'status'  => 1
        ];

        $data = $this->level_model->dataList($dis,$input['limit']);

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $v['lower'] = $this->level_model->where($dis)->where('inv_num','<',$v['inv_num'])->max('inv_num');

            }
        }

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-04 18:56
     * @功能说明:添加等级
     */
    public function levelAdd(){

        $input = $this->_input;

        $input['uniacid'] = $this->_uniacid;

        $dis = [

            'uniacid' => $this->_uniacid,

            'status'  => 1,
        ];

        $arr = [

            'inv_num'     => '邀请技师数量不能相同',
        ];

        foreach ($arr as $k=>$value){

            $find = $this->level_model->where($dis)->where([$k=>$input[$k]])->find();

            if(!empty($find)){

                $this->errorMsg($value);
            }
        }

        $res = $this->level_model->dataAdd($input);

        return $this->success($res);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-04 18:57
     * @功能说明:编辑等级
     */
    public function levelUpdate(){

        $input = $this->_input;

        $dis = [

            'id' => $input['id']
        ];

        if(isset($input['inv_num'])){

            $diss = [

                'uniacid' => $this->_uniacid,

                'status'  => 1,
            ];

            $arr = [

                'inv_num'     => '邀请技师数量不能相同',
            ];


            foreach ($arr as $k=>$value){

                $find = $this->level_model->where($diss)->where('id','<>',$input['id'])->where([$k=>$input[$k]])->find();

                if(!empty($find)){

                    $this->errorMsg($value);
                }
            }
        }

        $res = $this->level_model->dataUpdate($dis,$input);

        return $this->success($res);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-04 18:59
     * @功能说明:等级详情
     */
    public function levelInfo(){

        $input = $this->_input;

        $dis = [

            'id' => $input['id']
        ];

        $res = $this->level_model->dataInfo($dis);

        return $this->success($res);
    }


    /**
     * @author chenniang
     * @DataTime: 2024-08-13 13:43
     * @功能说明:批量设置经纪人提成比例
     */
    public function setBrokerBalance(){

        $input = $this->_input;

        $dis[] = ['id','in',$input['id']];

        $res = $this->model->dataUpdate($dis,['balance'=>$input['balance']]);

        return $this->success($res);
    }





}
