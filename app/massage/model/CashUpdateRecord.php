<?php
namespace app\massage\model;

use app\adminuser\model\AdminUser;
use app\BaseModel;
use app\coachbroker\model\CoachBroker;
use think\facade\Db;

class CashUpdateRecord extends BaseModel
{
    //定义表名
    protected $name = 'massage_coach_cash_update_record';




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
     * @DataTime: 2023-08-19 15:48
     * @功能说明:添加记录
     */
    public function recordAdd($coach_id,$cash,$is_add,$create_user=0,$text='',$info_id=0){

        $coach_model = new Coach();

        $coach_info = $coach_model->dataInfo(['id'=>$coach_id]);

        if($coach_info['service_price']<$cash&&$is_add==0&&$info_id==0){

            return ['code'=>500,'msg'=>'服务费小于扣款金额'];
        }

        $insert = [

            'uniacid' => $coach_info['uniacid'],

            'coach_id'=> $coach_id,

            'user_id' => $coach_info['user_id'],

            'cash'    => $cash,

            'is_add'  => $is_add,

            'text'    => $text,

            'before_cash'=> $coach_info['service_price'],

            'create_user'=> $create_user,

            'info_id'    => $info_id,

            'after_cash' => $is_add==1?$coach_info['service_price']+$cash:$coach_info['service_price']-$cash,
        ];

        $res = $this->dataAdd($insert);

        if($res==0){

            return ['code'=>500,'msg'=>'修改失败'];
        }

        $res = $coach_model->dataUpdate(['id'=>$insert['coach_id'],'service_price'=>$coach_info['service_price']],['service_price'=>$insert['after_cash']]);

        return $res;
    }


    /**
     * @param $type
     * @param $id
     * @param $cash
     * @param $is_add
     * @param $text
     * @param int $admin_type
     * @param int $info_id
     * @功能说明:修改各类角色的佣金
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-05-07 18:14
     */
    public function totalUpdateCash($uniacid,$type,$id,$cash,$is_add,$text,$create_user,$admin_type=0,$info_id=0,$admin_update_id=0){

        $arr = [
            [
                //技师服务费
                'type'  => 1,

                'model' => new Coach(),

                'field' => 'service_price',
            ],
            [
                //技师车费
                'type'  => 2,

                'model' => new Coach(),

                'field' => 'car_price',
            ],
            [
                //代理商
                'type'  => 3,

                'model' => new \app\massage\model\Admin(),

                'field' => 'cash'
            ],
            [
                //分销员
                'type'  => 4,

                'model' => new User(),

                'field' => 'new_cash'
            ],
            [
                //渠道商
                'type'  => 5,

                'model' => new ChannelList(),

                'field' => 'cash'
            ],
            [
                //业务员
                'type'  => 6,

                'model' => new Salesman(),

                'field' => 'cash'
            ],
            [
                //经济人
                'type'  => 7,

                'model' => new CoachBroker(),

                'field' => 'cash'
            ],
            [
                //平台管理员
                'type'  => 8,

                'model' => new AdminUser(),

                'field' => 'cash'
            ]
        ];

        foreach ($arr as $value){

            if($value['type']==$type){

                $obj = $value;
            }
        }

        $record_model = new CashUpdateRecord();

        $obj_info = $obj['model']->dataInfo(['id'=>$id]);

        if(empty($obj_info)){

            return ['code'=>500,'msg'=>'信息错误'];
        }

        if($obj_info[$obj['field']]<$cash&&$is_add==0&&$admin_type!=9){

            $msg = $admin_type==0?'服务费小于修改金额':'当前您的佣金余额不足，无法增加下级人员余额，请您前往手机端代理商操作台进行充值';

            return ['code'=>500,'msg'=>$msg];
        }

        $insert = [

            'uniacid' => $uniacid,

            'coach_id'=> $id,

            'user_id' => $type==4?$obj_info['id']:$obj_info['user_id'],

            'cash'    => $cash,

            'is_add'  => $is_add,

            'text'    => $text,

            'type'    => $type,

            'before_cash'=> $obj_info[$obj['field']],

            'create_user'=> $create_user,

            'after_cash' => $is_add==1?$obj_info[$obj['field']]+$cash:$obj_info[$obj['field']]-$cash,

            'admin_type' => $admin_type,

            'info_id'    => $info_id,

            'admin_update_id' => $admin_update_id,

            'ip' => getIP()
        ];

        $res = $record_model->dataAdd($insert);

        if($res==0){

            return ['code'=>500,'msg'=>'修改失败'];
        }

        $record_id = $record_model->getLastInsID();

        if(in_array($type,[1,2])){
            //技师
            $coach_water_model = new CoachWater();

            $res = $coach_water_model->updateCash($uniacid,$insert['coach_id'],$cash,$is_add,$type);

        }elseif (in_array($type,[3])){
            //代理商
            $admin_water_model = new AdminWater();

            $res = $admin_water_model->updateCash($uniacid,$insert['coach_id'],$cash,$is_add);

        }elseif (in_array($type,[8])){

            $admin_water_model = new CompanyWater();

            $res = $admin_water_model->updateCash($uniacid,$insert['coach_id'],$cash,$is_add);

        }elseif (in_array($type,[5])){

            $water_model = new ChannelWater();

            $res = $water_model->updateCash($uniacid,$insert['coach_id'],$cash,$is_add,0,0,4);

        }elseif (in_array($type,[6])){

            $water_model = new SalesmanWater();

            $res = $water_model->updateCash($uniacid,$insert['coach_id'],$cash,$is_add,0,0,4);

        }elseif (in_array($type,[7])){

            $water_model = new BrokerWater();

            $res = $water_model->updateCash($uniacid,$insert['coach_id'],$cash,$is_add,0,0,4);

        }elseif (in_array($type,[4])){

            $water_model = new UserWater();

            $res = $water_model->updateCash($uniacid,$insert['coach_id'],$cash,$is_add,0,0,4);

        }else{

            $res = $obj['model']->where(['id'=>$insert['coach_id'],$obj['field']=>$obj_info[$obj['field']]])->update([$obj['field']=>$insert['after_cash']]);
        }

        if($res==0){

            return ['code'=>500,'msg'=>'修改失败'];
        }

        return $record_id;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-05-08 13:43
     * @功能说明:代理商修改的时候获取修改人的名字
     */
    public function getUpdateObjTitle($id,$type){

        $arr = [
            [
                //技师服务费
                'type'  => 1,

                'model' => new Coach(),

                'field' => 'coach_name',
            ],
            [
                //技师车费
                'type'  => 2,

                'model' => new Coach(),

                'field' => 'coach_name',
            ],
            [
                //代理商
                'type'  => 3,

                'model' => new \app\massage\model\Admin(),

                'field' => 'agent_name'
            ],
            [
                //分销员
                'type'  => 4,

                'model' => new User(),

                'field' => 'nickName'
            ],
            [
                //渠道商
                'type'  => 5,

                'model' => new ChannelList(),

                'field' => 'user_name'
            ],
            [
                //业务员
                'type'  => 6,

                'model' => new Salesman(),

                'field' => 'user_name'
            ],
            [
                //经济人
                'type'  => 7,

                'model' => new CoachBroker(),

                'field' => 'user_name'
            ],
            [
                //经济人
                'type'  => -2,

                'model' => new DistributionList(),

                'field' => 'user_name'
            ],
            [
                //经济人
                'type'  => 8,

                'model' => new AdminUser(),

                'field' => 'user_name'
            ],
            [
                //分摊技师车费
                'type'  => 9,

                'model' => new Coach(),

                'field' => 'coach_name',
            ]
        ];

        foreach ($arr as $value){

            if($value['type']==$type){

                $obj = $value;
            }
        }

        $res = $obj['model']->where(['id'=>$id])->value($obj['field']);

        return $res;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-05-15 13:48
     * @功能说明:通过搜索获取信息
     */
    public function getDataByTitle($title){

        $dis[] = ['b.coach_name','like','%'.$title.'%',];
        $dis[] = ['c.agent_name','like','%'.$title.'%'];
        $dis[] = ['d.nickName','like','%'.$title.'%'];
        $dis[] = ['e.user_name','like','%'.$title.'%'];
        $dis[] = ['h.user_name','like','%'.$title.'%'];
        $dis[] = ['i.user_name','like','%'.$title.'%'];

        $data = $this->alias('a')
                ->join('massage_service_coach_list b','a.coach_id = b.id AND a.type in (1,2)','left')
                ->join('shequshop_school_admin c','a.coach_id = c.id AND a.type = 3','left')
                ->join('massage_service_user_list d','a.coach_id = d.id AND a.type = 4','left')
                ->join('massage_channel_list e','a.coach_id = e.id AND a.type = 5','left')
                ->join('massage_salesman_list h','a.coach_id = h.id AND a.type = 6','left')
                ->join('massage_coach_broker_list i','a.coach_id = i.id AND a.type = 7','left')
                ->whereOr($dis)
                ->column('a.id');
        return $data;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-05-16 17:00
     * @功能说明:修改记录
     */
    public function updateRecordList($dis,$admin_id,$page=10){

        $where[] = ['b.admin_id','=',$admin_id];
        $where[] = ['c.admin_id','=',$admin_id];
        $where[] = ['d.admin_id','=',$admin_id];
        $where[] = ['e.admin_id','=',$admin_id];
        $where[] = ['h.admin_id','=',$admin_id];

        $data = $this->alias('a')
            ->join('massage_service_coach_list b','a.coach_id = b.id AND a.type in (1,2) AND b.status in (2,3)','left')
            ->join('shequshop_school_admin c','a.coach_id = c.id AND a.type = 3 AND c.status = 1','left')
            ->join('massage_distribution_list d','a.coach_id = d.user_id AND a.type = 4 AND d.status in (2,3)','left')
            ->join('massage_channel_list e','a.coach_id = e.id AND a.type = 5 AND e.status in (2,3)','left')
            ->join('massage_salesman_list h','a.coach_id = h.id AND a.type = 6 AND h.status in (2,3)','left')
            ->where($dis)
            ->where(function ($query) use ($where){
                $query->whereOr($where);
            })
            ->field('a.*')
            ->group('a.id')
            ->order('a.id desc')
            ->paginate($page)
            ->toArray();

        return $data;

    }


    /**
     * @param $dis
     * @功能说明:代理商下面人员的修改佣金
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-05-31 14:51
     */
    public function coachUpdateCash($dis){

        $inc_cash = $this->alias('a')
                ->join('massage_service_coach_list b','a.coach_id = b.id')
                ->where($dis)
                ->where(['a.is_add'=>1,'a.type'=>1])
                ->group('a.id')
                ->sum('a.cash');

        $del_cash = $this->alias('a')
            ->join('massage_service_coach_list b','a.coach_id = b.id')
            ->where($dis)
            ->where(['a.is_add'=>0,'a.type'=>1])
            ->group('a.id')
            ->sum('a.cash');

        return round($inc_cash-$del_cash);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-05-31 15:22
     * @功能说明:分销员修改金额
     */
    public function resellerUpdateCash($dis){

        $inc_cash = $this->alias('a')
            ->join('massage_distribution_list b','a.coach_id = b.user_id')
            ->where($dis)
            ->where(['a.is_add'=>1,'a.type'=>4])
            ->group('a.id')
            ->sum('a.cash');

        $del_cash = $this->alias('a')
            ->join('massage_distribution_list b','a.coach_id = b.user_id')
            ->where($dis)
            ->where(['a.is_add'=>0,'a.type'=>4])
            ->group('a.id')
            ->sum('a.cash');

        return round($inc_cash-$del_cash);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-05-31 15:22
     * @功能说明:业务员修改金额
     */
    public function salesmanUpdateCash($dis){

        $inc_cash = $this->alias('a')
            ->join('massage_salesman_list b','a.coach_id = b.id')
            ->where($dis)
            ->where(['a.is_add'=>1,'a.type'=>6])
            ->group('a.id')
            ->sum('a.cash');

        $del_cash = $this->alias('a')
            ->join('massage_salesman_list b','a.coach_id = b.id')
            ->where($dis)
            ->where(['a.is_add'=>0,'a.type'=>6])
            ->group('a.id')
            ->sum('a.cash');

        return round($inc_cash-$del_cash);
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-05-31 15:22
     * @功能说明:渠道商修改金额
     */
    public function channelUpdateCash($dis){

        $inc_cash = $this->alias('a')
            ->join('massage_channel_list b','a.coach_id = b.id')
            ->where($dis)
            ->where(['a.is_add'=>1,'a.type'=>5])
            ->group('a.id')
            ->sum('a.cash');

        $del_cash = $this->alias('a')
            ->join('massage_channel_list b','a.coach_id = b.id')
            ->where($dis)
            ->where(['a.is_add'=>0,'a.type'=>5])
            ->group('a.id')
            ->sum('a.cash');

        return round($inc_cash-$del_cash);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-05-31 15:31
     * @功能说明:代理商的所有角色的修改佣金
     */
    public function totalUopdateCash($dis){

        $coach = $this->coachUpdateCash($dis);

        $reseller = $this->resellerUpdateCash($dis);

        $salesman = $this->salesmanUpdateCash($dis);

        $channel  = $this->channelUpdateCash($dis);

        return round($coach+$reseller+$salesman+$channel,2);
    }






}