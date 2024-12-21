<?php
namespace app\massage\model;

use app\adminuser\model\AdminUser;
use app\BaseModel;
use app\coachbroker\model\CoachBroker;
use app\massage\controller\AdminCoach;
use longbingcore\wxcore\WxPay;
use think\facade\Db;

class Wallet extends BaseModel
{
    //定义表名
    protected $name = 'massage_service_wallet_list';
    //type 1服务费 2是车费  3代理商 4用户分销 5渠道商 6业务员 7车费给代理商 8新的代理商，如果新版本代理商类型不再是3 9新的车费给代理商 新的类型不再是7 10技师经纪人 11平台管理员 12用户AA组局
    protected $append = [

        'service_cash'
    ];


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-04-09 09:28
     * @功能说明:手续费
     */
    public function getServiceCashAttr($value,$data){

        if(isset($data['apply_cash'])&&isset($data['true_cash'])&&isset($data['pay_cash'])){

            return !empty($data['pay_cash'])?round($data['apply_cash']-$data['pay_cash'],2):round($data['apply_cash']-$data['true_cash'],2);

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
    public function dataList($dis,$page,$where=[]){

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
    public function dataInfo($dis){

        $data = $this->where($dis)->find();

        return !empty($data)?$data->toArray():[];

    }


    /**
     * @param $name
     * @功能说明:通过搜索获取订单号
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-01-17 15:54
     */
    public function getIdByNameV2($name){

        $dis = [

            ['b.coach_name' ,'like', "%".$name.'%'],

            ['a.type'    ,'in',  [1,2]],
        ];

        $dis1 =[

            ['c.agent_name' ,'like', "%".$name.'%'],

            ['a.type'   ,'=',  3]
        ];

        $dis2 =[

            ['d.nickName' ,'like', "%".$name.'%'],

            ['a.type'   ,'=',  4]
        ];

        $dis3 =[

            ['e.user_name' ,'like', "%".$name.'%'],

            ['a.type'   ,'=',  5]
        ];

        $dis4 =[

            ['f.user_name' ,'like', "%".$name.'%'],

            ['a.type'   ,'=',  6]
        ];

        $dis5 =[

            ['g.agent_name' ,'like', "%".$name.'%'],

            ['a.type'   ,'in',  [7,8,9]]
        ];

        $dis6 =[

            ['h.user_name' ,'like', "%".$name.'%'],

            ['a.type'   ,'=',  10]
        ];

        $data = $this->alias('a')
                ->join('massage_service_coach_list b','a.coach_id = b.id','left')
                ->join('shequshop_school_admin c','a.user_id = c.id','left')
                ->join('massage_service_user_list d','a.user_id = d.id','left')
                ->join('massage_channel_list e','a.coach_id = e.id','left')
                ->join('massage_salesman_list f','a.coach_id = f.id','left')
                ->join('shequshop_school_admin g','a.coach_id = g.id','left')
                ->join('massage_coach_broker_list h','a.coach_id = h.id','left')
                ->where(function ($query) use ($dis,$dis1,$dis2,$dis3,$dis4,$dis5,$dis6){
                    $query->whereOr([$dis,$dis1,$dis2,$dis3,$dis4,$dis5,$dis6]);
                })
                ->group('a.id')
                ->column('a.id');

        return $data;
    }


    /**
     * @param $name
     * @功能说明:通过搜索获取订单号
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-01-17 15:54
     */
    public function getIdByName($name){

        $dis = [

            ['b.coach_name' ,'like', "%".$name.'%'],

            ['b.true_user_name' ,'like', "%".$name.'%'],

            ['c.agent_name' ,'like', "%".$name.'%'],

            ['d.nickName' ,'like', "%".$name.'%'],

            ['e.user_name' ,'like', "%".$name.'%'],

            ['e.true_user_name' ,'like', "%".$name.'%'],

            ['f.user_name' ,'like', "%".$name.'%'],

            ['f.true_user_name' ,'like', "%".$name.'%'],

            ['g.agent_name' ,'like', "%".$name.'%'],

            ['h.user_name' ,'like', "%".$name.'%'],

            ['h.true_user_name' ,'like', "%".$name.'%'],

            ['i.true_user_name' ,'like', "%".$name.'%'],
        ];

        $data = $this->alias('a')
            ->join('massage_service_coach_list b','a.coach_id = b.id AND a.type in (1,2)','left')
            ->join('shequshop_school_admin c','a.user_id = c.id AND a.type = 3','left')
            ->join('massage_service_user_list d','a.user_id = d.id AND a.type = 4','left')
            ->join('massage_channel_list e','a.coach_id = e.id AND a.type = 5','left')
            ->join('massage_salesman_list f','a.coach_id = f.id AND a.type = 6','left')
            ->join('shequshop_school_admin g','a.coach_id = g.id AND a.type in (7,8,9)','left')
            ->join('massage_coach_broker_list h','a.coach_id = h.id AND a.type = 10','left')
            ->join('massage_distribution_list i','a.user_id = i.user_id AND i.status in (2,3) AND a.type = 4','left')
            ->where(function ($query) use ($dis){
                $query->whereOr($dis);
            })
            ->group('a.id')
            ->column('a.id');

        return $data;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-18 14:33
     * @功能说明:
     */
    public function datePrice($date,$uniacid,$cap_id,$end_time='',$type=1){

        $end_time = !empty($end_time)?$end_time:$date+86399;

        $dis = [];

        $dis[] = ['status','=',2];

        $dis[] = ['create_time','between',"$date,$end_time"];

        $dis[] = ['uniacid',"=",$uniacid];

        if(!empty($cap_id)){

            $dis[] = ['cap_id','=',$cap_id];
        }

        if($type==1){

            $price = $this->where($dis)->sum('true_cash');

            return round($price,2);

        }else{

            $count = $this->where($dis)->count();

            return $count;

        }

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-18 16:06
     * @功能说明:
     */
    public function adminList($dis,$page=10){

        $data = $this->alias('a')
                ->join('massage_service_coach_list b','a.coach_id = b.id','left')
                ->where($dis)
                ->field('a.*,b.coach_name')
                ->group('a.id')
                ->order('a.id desc')
                ->paginate($page)
                ->toArray();

        return $data;
    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-30 14:36
     * @功能说明:团长提现
     */
    public function capCash($cap_id,$status=2,$type=0){

        $dis = [

            'coach_id' => $cap_id,

            'status' => $status
        ];

        if(!empty($type)){

            $dis['type'] = $type;
        }

        $price = $this->where($dis)->sum('total_price');

        return round($price,2);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-30 14:36
     * @功能说明:用户提现
     */
    public function userCash($user_id,$status=2,$time=0){

        $dis = [

            'user_id'=> $user_id,

            'type'   => 4
        ];

        if(!empty($status)){

            $dis['status'] = $status;
        }

        $where=[];

        if(!empty($time)){

            $where[] = ['create_time','>',$time];
        }

        $price = $this->where($dis)->where($where)->sum('total_price');

        return round($price,2);

    }

    /**
     * @Desc: 用户搭子提现
     * @param $user_id
     * @param $status
     * @param $time
     * @return float
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/31 16:01
     */
    public function userPartner($user_id, $status = 2, $time = 0)
    {

        $dis = [

            'user_id' => $user_id,

            'type' => 12
        ];

        if (!empty($status)) {

            $dis['status'] = $status;
        }

        $where = [];

        if (!empty($time)) {

            $where[] = ['create_time', '>', $time];
        }

        $price = $this->where($dis)->where($where)->sum('total_price');

        return round($price, 2);

    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-30 14:36
     * @功能说明:团长提现
     */
    public function capCashCount($cap_id,$status=2){

        $dis = [


            'cap_id' => $cap_id,

            'status' => $status
        ];

        $count = $this->where($dis)->count();

        return $count;

    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-30 14:36
     * @功能说明:代理商提现
     */
    public function adminCash($admin_id,$status=2){

        $where = [

            ['user_id' ,'=', $admin_id],

            [ 'type'    ,'=',  3]
        ];

        $where1 = [

            ['coach_id' ,'=', $admin_id],

            ['type'   ,'in',  [7,8,9]]
        ];

        $dis = [

            'status' => $status,
        ];

        $price = $this->where(function ($query) use ($where,$where1){
            $query->whereOr([$where,$where1]);
        })->where($dis)->sum('total_price');

        return round($price,2);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-09-03 17:29
     * @功能说明:查询微信转账是否到账
     */
    public function wxCheck($uniacid,$payConfig,$id=[]){

        $dis = [

            'online' => 1,

            'status' => 4
        ];

        $where = [];

        if(!empty($id)){

            $where[] = ['id','in',$id];
        }

        $data = $this->where($dis)->where($where)->limit(10)->select()->toArray();

        $wx_pay = new WxPay($uniacid);

        if(!empty($data)){

            foreach ($data as $v){

                $res = $wx_pay->getMchPayRecord($v['payment_no'],$payConfig);

                if(isset($res['detail_status'])){
                    //成功
                    if($res['detail_status']=='SUCCESS'){

                        $this->dataUpdate(['id'=>$v['id']],['status'=>2]);
                    }
                    //失败
                    if($res['detail_status']=='FAIL'){

                        $this->dataUpdate(['id'=>$v['id']],['status'=>5,'failure_reason'=>$res['fail_reason']]);
                    }
                }

                if(isset($res['code'])&&$res['code']=='NOT_FOUND'){

                    $this->dataUpdate(['id'=>$v['id']],['status'=>5,'failure_reason'=>'NOT_FOUND']);
                }
            }
        }
        return true;
    }


    /**
     * @param $uniacid
     * @功能说明:汇付转账校验
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-04-09 14:14
     */
    public function adapayWalletCheck($uniacid){

        $dis = [

            'online' => 4,

            'status' => 4
        ];

        $where = [];

        if(!empty($id)){

            $where[] = ['id','in',$id];
        }

        $data = $this->where($dis)->where($where)->limit(20)->select()->toArray();

        $adapay = new \longbingcore\wxcore\Adapay($uniacid);

        $callbak_model = new \app\adapay\model\Callback();

        if(!empty($data)){

            foreach ($data as $v){

                $res = $adapay->drawCashInfo($v['adapay_code']);

                if(isset($res['status'])){
                    //成功
                    if($res['status']=='succeeded'){

                        $this->dataUpdate(['id'=>$v['id']],['status'=>2]);

                        $insert = [

                            'uniacid' => $uniacid,

                            'adapay_id' => $v['payment_no'],

                            'status' => $res['status'],

                            'type'   => 'wallet',

                            'text'   => serialize($res)
                        ];

                        $callbak_model->dataAdd($insert);
                    }
                }
            }
        }
        return true;
    }


    /**
     * @param $admin_id
     * @param $data
     * @功能说明:代理商审核权限
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-01-16 11:58
     */
    public function agentCheckAuth($admin_id,$data,$auth){

        if($data['type']==3){
            //是否有审核对权限
            $wallet_check_auth = $data['user_id'] == $admin_id?0:$auth;

        }elseif (in_array($data['type'],[8,9])){

            $wallet_check_auth = $data['coach_id'] == $admin_id?0:$auth;

        }else{

            $wallet_check_auth = $auth;
        }

        return $wallet_check_auth;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-05-22 11:54
     * @功能说明:获取提现对象的各类信息
     */
    //type 1服务费 2是车费  3代理商 4用户分销 5渠道商 6业务员 7车费给代理商 8新的代理商，如果新版本代理商类型不再是3 9新的车费给代理商 新的类型不再是7 10技师经纪人
    public function getWalletObjInfo($type){

        switch ($type){

            case 1:
                $data['model'] = new Coach();

                $data['title'] = 'coach_name';

                $data['admin_id'] = 'admin_id';

                $data['true_user_name'] = 'true_user_name';

                break;
            case 2:
                $data['model'] = new Coach();

                $data['title'] = 'coach_name';

                $data['admin_id'] = 'admin_id';

                $data['true_user_name'] = 'true_user_name';

                break;
            case 3:
                $data['model'] = new Admin();

                $data['title'] = 'agent_name';

                $data['admin_id'] = 'admin_id';

                $data['user_id'] = 'user_id';

                break;
            case 4:
                $data['model'] = new User();

                $data['title'] = 'nickName';

                $data['user_id'] = 'user_id';

                break;
            case 5:
                $data['model'] = new ChannelList();

                $data['title'] = 'user_name';

                $data['admin_id'] = 'admin_id';

                $data['true_user_name'] = 'true_user_name';

                break;
            case 6:
                $data['model'] = new Salesman();

                $data['title'] = 'user_name';

                $data['admin_id'] = 'admin_id';

                $data['true_user_name'] = 'true_user_name';

                break;
            case 7:
                $data['model'] = new Admin();

                $data['title'] = 'agent_name';

                $data['admin_id'] = 'admin_id';

                break;
            case 8:
                $data['model'] = new Admin();

                $data['title'] = 'agent_name';

                $data['admin_id'] = 'admin_id';

                break;
            case 9:
                $data['model'] = new Admin();

                $data['title'] = 'agent_name';

                $data['admin_id'] = 'admin_id';

                break;
            case 10:
                $data['model'] = new CoachBroker();

                $data['title'] = 'user_name';

                $data['true_user_name'] = 'true_user_name';

                break;

            case 11:
                $data['model'] = new User();

                $data['title'] = 'nickName';

                $data['user_id'] = 'user_id';

                break;
            case 12:
                $data['model'] = new User();

                $data['title'] = 'nickName';

                $data['user_id'] = 'user_id';

                break;
        }

        return $data;
    }


    /**
     * @author chenniang
     * @DataTime: 2024-06-11 17:54
     * @功能说明:校验代理商转账方式
     */
    public function agentTransferCheck($type){

        $arr = [

            0 => 'offline_transfer_auth',

            1 => 'wechat_transfer_auth',

            2 => 'alipay_transfer_auth',

            4 => 'bank_transfer_auth',
        ];

        return array_key_exists($type,$arr)?$arr[$type]:"offline_transfer_auth";
    }



}