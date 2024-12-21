<?php
namespace app\balancediscount\model;

use app\BaseModel;
use app\massage\model\ConfigSetting;
use think\facade\Db;

class CardWater extends BaseModel
{
    //定义表名
    protected $name = 'massage_balance_discount_card_water';




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

        $data = $this->where($dis)->order('top desc,id desc')->paginate($page)->toArray();

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
     * @author chenniang
     * @DataTime: 2024-07-25 13:46
     * @功能说明:修改佣金
     */
    public function updateCash($uniacid,$card_id,$cash,$is_add,$order_id,$type,$user_id,$refund_id=0){

        $is_add = $is_add==1?$is_add:-1;

        $card_model = new UserCard();

        $card_info = $card_model->dataInfo(['id'=>$card_id]);

        if(empty($card_info)){

            return true;
        }

        $last_record = $this->where(['card_id'=>$card_id])->order('id desc')->find();

        $card_cash  = $card_info['cash'];

        if(!empty($last_record)&&round($last_record->after_cash,2)!=round($card_cash,2)){

            return 0;
        }
        //不能为负
        if($is_add==-1&&$card_cash<$cash){

            return 0;
        }

        $true_cash = $is_add==1?$cash:$cash*-1;

        if($true_cash==0){

            return true;
        }

        $res = $card_model->where(['id'=>$card_id,'cash'=>$card_cash])->update(['cash'=>Db::Raw("cash+$true_cash")]);

        if($res==0){

            return 0;
        }

        $insert = [

            'uniacid' => $uniacid,

            'card_id'=> $card_id,

            'user_id'=> $user_id,

            'before_cash' => $card_cash,

            'after_cash'  => $card_cash+$true_cash,

            'cash'        => $cash,

            'res'         => $res,

            'create_time' => time(),

            'add'         => $is_add,

            'order_id'    => $order_id,

            'type'        => $type,

            'refund_id'   => $refund_id,
        ];

        $res = $this->insert($insert);

        if($res==0){

            return 0;
        }

        return $res;
    }









}