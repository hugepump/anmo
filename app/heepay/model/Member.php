<?php
namespace app\heepay\model;

use app\BaseModel;
use longbingcore\heepay\HeePay;
use think\facade\Db;

class Member extends BaseModel
{
    //定义表名
    protected $name = 'massage_heepay_user_list';




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
    public function adminList($dis,$page,$where=[]){

        $data = $this->alias('a')
            ->join('massage_service_user_list c','a.user_id = c.id','left')
            ->where($dis)
            ->where(function ($query) use ($where){
                $query->whereOr($where);
            })
            ->field('a.*,c.nickName')
            ->group('a.id desc')
            ->paginate($page)
            ->toArray();

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
     * @DataTime: 2021-03-19 16:08
     * @功能说明:开启默认
     */
    public function updateOne($id){

        $user_id = $this->where(['id'=>$id])->value('user_id');

        $res = $this->where(['user_id'=>$user_id])->where('id','<>',$id)->update(['status'=>0]);

        return $res;
    }


    /**
     * @param $user_id
     * @功能说明:校验用户余额
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-04-29 15:54
     */
    public function checkUserCash($user_id,$tx_cash){

        $dis = [

            'user_id' => $user_id,

            'status'  => 1
        ];

        $member = $this->dataInfo($dis);

        if(empty($member)){

            return ['code'=>500,'msg'=>'该用户没有账户'];
        }

        $heepay = new HeePay($member['uniacid']);

        $user_info = $heepay->userResInfo($member['apply_no']);

        if(empty($user_info)){

            return ['code'=>500,'msg'=>'用户信息错误'];
        }

        if(empty($user_info['biz_content']['audit_status'])||$user_info['biz_content']['audit_status']!=3){

            return ['code'=>500,'msg'=>'用户信息未审核通过'];
        }

        $cash = $heepay->agentCash($user_info['biz_content']['heepay_id']);

        if(!isset($cash['can_Used_Amt'])){

            return ['code'=>500,'msg'=>'用户信息错误'];
        }

        $can_Used_Amt = $cash['can_Used_Amt']-$cash['lock_Amt'];
        //钱不够需要充值
        if($can_Used_Amt<$tx_cash){

            $now_cash = $tx_cash-$can_Used_Amt;

            $order_code = orderCode();

            $res = $heepay->agentRechargev2($member['contact_email'],$order_code,$now_cash,$member['bank_account_name']);

            if(!isset($res['ret_code'])||$res['ret_code']!='0000'){

                return ['code'=>500,'msg'=>$res['ret_msg']];
            }

            $insert = [

                'uniacid' => $member['uniacid'],

                'apply_no'=> $member['apply_no'],

                'heepay_id'=> $user_info['biz_content']['heepay_id'],

                'cash' => $now_cash,

                'agent_bill_id' => $order_code,
            ];

            $record_model = new RechargeRecord();

            $record_model->dataAdd($insert);
        }

        return ['heepay_id'=>$user_info['biz_content']['heepay_id']];
    }






}