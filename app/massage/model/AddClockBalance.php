<?php
namespace app\massage\model;

use app\BaseModel;
use think\facade\Db;

class AddClockBalance extends BaseModel
{
    //定义表名
    protected $name = 'massage_addclock_balance_config';



    public function getBalanceAttr($value){

        if(!empty($value)){

            return round($value,2);
        }
    }

    /**
     * @param $uniacid
     * @功能说明:初始化数据
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-03-27 18:20
     */
    public function initData($uniacid,$admin_id=0){

        $key = 'addclock_balance_config';

        incCache($key,1,$uniacid,10);

        if(getCache($key,$uniacid)==1){

            $arr = [1,2,3,4];

            foreach ($arr as $value){

                $find = $this->where(['type'=>$value,'uniacid'=>$uniacid,'admin_id'=>$admin_id])->find();

                if(empty($find)){

                    $insert = [

                        'uniacid' => $uniacid,

                        'type'    => $value,

                        'admin_id' => $admin_id
                    ];

                    $this->insert($insert);
                }
            }
        }

        decCache($key,1,$uniacid);

        return true;
    }


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
     * @DataTime: 2024-03-28 14:46
     * @功能说明:获取各个角色的佣金比列
     */
    public function getObjBalance($order,$balance,$type,$admin_id=0){
        //只有加钟才有
        if($order['is_add']==0){

            return $balance;
        }

        $admin_model = new Admin();

        $auth = 1;

        if($type==1&&!empty($admin_id)){

            $auth = $admin_model->where(['id'=>$admin_id,'agent_coach_auth'=>1])->value('reseller_auth');
        }

        if($type==3&&!empty($admin_id)){

            $auth = $admin_model->where(['id'=>$admin_id,'agent_coach_auth'=>1])->value('salesman_auth');
        }

        if($type==4&&!empty($admin_id)){

            $auth = $admin_model->where(['id'=>$admin_id,'agent_coach_auth'=>1])->value('channel_auth');
        }
        //经纪人用平台
        if($auth!=1||$type==2){

            $admin_id = 0;
        }

        $dis = [

            'uniacid' => $order['uniacid'],

            'type'    => $type,

            'admin_id'=> $admin_id
        ];

        $data = $this->dataInfo($dis);

        if(empty($data)){

            return $balance;
        }

        if($data['status']==0){

            return 0;
        }

        if($data['add']==1){

            return $balance+$data['balance']>100?100:$balance+$data['balance'];
        }else{

            return $balance-$data['balance']<0?0:$balance-$data['balance'];
        }

    }






}