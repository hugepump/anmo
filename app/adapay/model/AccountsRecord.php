<?php
namespace app\adapay\model;

use app\BaseModel;
use longbingcore\wxcore\Adapay;
use think\facade\Db;

class AccountsRecord extends BaseModel
{
    //定义表名
    protected $name = 'shequshop_adapay_accounts_record';




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
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-18 16:33
     * @功能说明:提现的时候余额不足，需要给账户分账
     */
    public function giveMemberCash($member_id,$point,$uniacid,$true_cash){

        $adapay_model = new Adapay($uniacid);

        $member_model = new Member();

        $bank_model   = new Bank();

        $admin_pay_model = new AdminPay();

        $config_model = new Config();

        $acc_record_model = new AccountsRecord();

        $config = $config_model->dataInfo(['uniacid'=>$uniacid]);

        $member = $member_model->dataInfo(['id'=>$member_id]);

        if(!empty($member)){

            $bank = $bank_model->dataInfo(['order_member_id'=>$member['id'],'status'=>1]);

            if(!empty($bank)){
                //查询账户
                $acct = $adapay_model->settleAccount($bank['member_id'],$bank['settle_account_id']);

                if(!isset($acct['avl_balance'])){

                    return ['code'=>500,'msg'=>'账户信息错误'];
                }
                //如果账号余额不足
                if($true_cash>$acct['avl_balance']){

                    $del_cash = $true_cash-$acct['avl_balance'];

                    $adapay_balance_point = getConfigSetting($uniacid,'adapay_balance_point');

                    $del_cash = round($del_cash/(100-$adapay_balance_point)*100,2);
                    //汇付内部的余额充值
                    $res = $adapay_model->balancePay($bank['member_id'],$del_cash);

                    if(!empty($res['status'])&&$res['status']=='succeeded'){

                        $insert = [

                            'uniacid' => $uniacid,

                            'cash'    => $true_cash-$acct['avl_balance'],

                            'pay_id'  => 0,

                            'member_id'  => $member['id'],

                            'bank_id'  => $bank['id'],

                            'adapay_member_id'  => $bank['member_id'],

                            'settle_account_id'  => $bank['settle_account_id'],

                            'adapay_id'  => $res['order_no'],
                        ];

                        $acc_record_model->dataAdd($insert);

                    }else{

                        $admin_cash = $admin_pay_model->whereTime('pay_time','-547 days')->where('true_price','>',0)->where(['pay_type'=>2])->sum('true_price');

                        if($admin_cash+$acct['avl_balance']<$true_cash){

                            return ['code'=>500,'msg'=>'账户资金不足,请先充值'];

                        }
                        //需要充值的钱
                        $del_cash = $true_cash-$acct['avl_balance'];

                        $del_cash = round($del_cash/(100-$point)*100,2);
                        //查询平台充值的订单
                        $list = $admin_pay_model->whereTime('pay_time','-547 days')->where('true_price','>',0)->where(['pay_type'=>2])->order('id desc')->select()->toArray();

                        if(!empty($list)){

                            foreach ($list as $value){

                                $d_cash = $value['true_price']>$del_cash?$del_cash:$value['true_price'];

                                $del_cash -= $d_cash;

                                if($d_cash>0){

                                    $div_members = [

                                        'member_id' => $bank['member_id'],

                                        'amount'    => sprintf("%01.2f",$d_cash),
                                    ];
                                    if($config['commission']==1){

                                        $div_members['fee_flag'] = 'Y';
                                    }
                                    //发起分账
                                    $res = $adapay_model->confirmorderCreate($value['adapay_id'],orderCode(),$d_cash,[$div_members],'账户余额不足，平台分账',$config['commission']);
                                    //成功添加记录
                                    if($res['status']=='succeeded'){

                                        $insert = [

                                            'uniacid' => $uniacid,

                                            'cash'    => $d_cash,

                                            'pay_id'  => $value['id'],

                                            'member_id'  => $member['id'],

                                            'bank_id'  => $bank['id'],

                                            'adapay_member_id'  => $bank['member_id'],

                                            'settle_account_id'  => $bank['settle_account_id'],

                                            'adapay_id'  => $res['id'],
                                        ];

                                        $acc_record_model->dataAdd($insert);

                                        $admin_pay_model->where(['id'=>$value['id']])->update(['true_price'=>Db::raw("true_price-$d_cash")]);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return true;
    }






}