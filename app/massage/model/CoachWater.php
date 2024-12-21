<?php
namespace app\massage\model;

use app\BaseModel;
use think\facade\Db;

class CoachWater extends BaseModel
{
    //定义表名
    protected $name = 'massage_coach_water';




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:04
     * @功能说明:添加
     */
    public function dataAdd($data){

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

        $data = $this->where($dis)->order('status desc,id desc')->paginate($page)->toArray();

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
     * @param $coach_id
     * @功能说明:校验技师车费
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-01-22 18:47
     */
    public function checkCarPrice($coach_id){

        $comm_model   = new Commission();

        $wallet_model = new Wallet();

        $coach_model  = new Coach();

        $arr['cash'] = $comm_model->where(['top_id'=>$coach_id,'type'=>8,'status'=>2])->sum('cash');

        $arr['wallet_cash'] = $wallet_model->where(['coach_id'=>$coach_id,'type'=>2])->where('status','in',[1,2,4,5])->sum('total_price');

        $arr['coach_cash'] = $coach_model->where(['id'=>$coach_id])->sum('car_price');

        $arr['have_cash'] = $arr['cash']-$arr['wallet_cash']-$arr['coach_cash'];

        return $arr;
    }


    /**
     * @param $coach_id
     * @功能说明:校验技师服务费
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-01-22 18:49
     */
    public function checkServicePrice($coach_id){

        $comm_model   = new Commission();

        $wallet_model = new Wallet();

        $coach_model  = new Coach();

        $record_model = new CashUpdateRecord();

        $arr['cash']  = $comm_model->where(['top_id'=>$coach_id,'status'=>2])->where('type','in',[3,7])->sum('cash');

        $arr['wallet_cash'] = $wallet_model->where(['coach_id'=>$coach_id,'type'=>1])->where('status','in',[1,2,4,5])->sum('total_price');

        $arr['coach_cash'] = $coach_model->where(['id'=>$coach_id])->sum('service_price');

        $arr['update_inc_cash'] = $record_model->where(['coach_id'=>$coach_id,'status'=>1,'type'=>1,'is_add'=>1])->sum('cash');

        $arr['update_del_cash'] = $record_model->where(['coach_id'=>$coach_id,'status'=>1,'type'=>1,'is_add'=>0])->sum('cash');

        $arr['have_cash'] = $arr['cash']-$arr['wallet_cash']-$arr['coach_cash']+$arr['update_inc_cash']-$arr['update_del_cash'];

        return $arr;
    }

    /**
     * @author chenniang
     * @DataTime: 2024-07-25 13:46
     * @功能说明:修改佣金
     */
    public function updateCash($uniacid,$coach_id,$cash,$is_add,$type=1){

        $water_version = getConfigSetting($uniacid,'water_version');

        if($water_version==0){

            $this->where(['uniacid'=>$uniacid])->delete();

            $config_setting_model = new ConfigSetting();

            $config_setting_model->dataUpdate(['water_version'=>1],$uniacid);
        }

        $is_add = $is_add==1?$is_add:-1;

        $coach_model = new Coach();

        $coach_info = $coach_model->dataInfo(['id'=>$coach_id]);

        if(empty($coach_info)){

            return true;
        }

        $last_record = $this->where(['coach_id'=>$coach_id,'type'=>$type])->order('id desc')->find();

        $coach_cash  = $type==1?$coach_info['service_price']:$coach_info['car_price'];

        if(!empty($last_record)&&round($last_record->after_cash,2)!=round($coach_cash,2)){

            return 0;
        }

        $true_cash = $is_add==1?$cash:$cash*-1;

        if($true_cash==0){

            return true;
        }

        if($type==1){

            $res = $coach_model->where(['id'=>$coach_id,'service_price'=>$coach_info['service_price']])->update(['service_price'=>Db::Raw("service_price+$true_cash")]);
        }else{

            $res = $coach_model->where(['id'=>$coach_id,'car_price'=>$coach_info['car_price']])->update(['car_price'=>Db::Raw("car_price+$true_cash")]);
        }

        if($res==0){

            return 0;
        }

        $insert = [

            'uniacid' => $uniacid,

            'coach_id'=> $coach_id,

            'before_cash' => $coach_cash,

            'after_cash'  => $coach_cash+$true_cash,

            'cash'        => $cash,

            'type'        => $type,

            'res'         => $res,

            'create_time' => time(),

            'add'         => $is_add
        ];

        $res = $this->insert($insert);

        if($res==0){

            return 0;
        }

        return $res;
    }


    /**
     * @param $uniacid
     * @param $coach_id
     * @功能说明:初始化技师流水
     * @author chenniang
     * @DataTime: 2024-11-06 11:58
     */
    public static function initWater($uniacid,$coach_id){

        $arr = [1,2];

        foreach ($arr as $k=>$value){

            $insert[$k] = [

                'uniacid' => $uniacid,

                'coach_id'=> $coach_id,

                'before_cash' => 0,

                'after_cash'  => 0,

                'cash'        => 0,

                'type'        => $value,

                'res'         => 1,

                'create_time' => time(),

                'add'         => 1
            ];
        }

        return self::createAll($insert);
    }







}