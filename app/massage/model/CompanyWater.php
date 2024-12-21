<?php
namespace app\massage\model;

use app\adminuser\model\AdminUser;
use app\BaseModel;
use think\facade\Db;

class CompanyWater extends BaseModel
{
    //定义表名
    protected $name = 'massage_company_water';




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
     * @param $comm_id
     * @param $uniacid
     * @功能说明:结算平台佣金
     * @author chenniang
     * @DataTime: 2024-08-05 14:11
     */
    public function addWaterQueue($uniacid,$comm_id=0,$i=1){

        if(!empty($comm_id)){

            cacheLpush('company_water_success',$comm_id,$uniacid);
        }

        $comm_model = new Commission();

        $num = 0;

        while ($num<$i){

            $num++;

            $data_id = cacheLpop('company_water_success',$uniacid);

            if(!empty($data_id)){

                $comm = $comm_model->dataInfo(['status'=>1,'id'=>$data_id]);

                if(!empty($comm)){

                    $res = $this->updateCash($uniacid,0,$comm['company_cash'],1);

                    if($res==0){

                        cacheLpush('company_water_success',$data_id,$uniacid);
                    }

                    $res = $comm_model->dataUpdate(['status'=>1,'id'=>$data_id],['status'=>2,'cash_time'=>time()]);

                    if($res==0){

                        cacheLpush('company_water_success',$data_id,$uniacid);
                    }
                }
            }else{

                return true;
            }
        }

        return true;
    }




    /**
     * @author chenniang
     * @DataTime: 2024-07-25 13:46
     * @功能说明:修改佣金
     */
    public function updateCash($uniacid,$user_id,$cash,$is_add){

        $is_add = $is_add==1?$is_add:-1;

        $admin_user_model = new AdminUser();

        $admin_info  = $admin_user_model->dataInfo(['uniacid'=>$uniacid],1);

        $last_record = $this->where(['uniacid'=>$uniacid])->order('id desc')->find();

        $admin_cash  = $admin_info['cash'];

        if(!empty($last_record)&&round($last_record->after_cash,2)!=round($admin_cash,2)){

            return 0;
        }

        $true_cash = $is_add==1?$cash:$cash*-1;

        if($true_cash==0){

            return true;
        }

        $res = $admin_user_model->where(['id'=>$admin_info['id'],'cash'=>$admin_cash])->update(['cash'=>Db::Raw("cash+$true_cash")]);

        if($res==0){

            return 0;
        }

        $insert = [

            'uniacid' => $uniacid,

            'user_id'=> $admin_info['id'],

            'before_cash' => $admin_cash,

            'after_cash'  => $admin_cash+$true_cash,

            'cash'        => $cash,

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







}