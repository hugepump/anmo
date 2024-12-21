<?php
namespace app\massage\model;

use app\BaseModel;
use think\facade\Db;

class Salesman extends BaseModel
{
    //定义表名
    protected $name = 'massage_salesman_list';




    public function getTrueUserNameAttr($value,$data){

        if(isset($value)){

            if(!empty($value)){

                return $value;

            }elseif (!empty($data['user_name'])){

                return $data['user_name'];
            }
        }
    }



    public function getPhoneAttr($value,$data){

        if(!empty($value)&&isset($data['uniacid'])){

            if(numberEncryption($data['uniacid'])==1){

                return  substr_replace($value, "****", 2,4);
            }
        }

        return $value;
    }

    /**
     * @param $value
     * @param $data
     * @功能说明:判断代理商是否有发展技师的权限
     * @author chenniang
     * @DataTime: 2024-06-13 15:07
     */
    public function getAdminIdAttr($value,$data){

        if(!empty($value)){

            $admin_model = new Admin();

            $admin = $admin_model->where(['id'=>$value,'status'=>1,'salesman_auth'=>1])->count();

            return $admin>0?$value:0;

        }else{

            return 0;
        }
    }


    /**
     * @param $value
     * @param $data
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-11-29 11:06
     */
    public function getBalanceAttr($value,$data){

        if(isset($value)){

            return floatval($value);
        }

    }


    /**
     * @param $value
     * @param $data
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-11-29 11:06
     */
    public function getInvChannelBalanceAttr($value,$data){

        if(isset($value)){

            return floatval($value);
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
     * @DataTime: 2023-04-21 11:13
     * @功能说明:后台业务员列表
     */
    public function adminDataList($dis,$mapor,$page=10){


        $data = $this->alias('a')
            ->join('massage_service_user_list b','a.user_id = b.id','left')
            ->where($dis)
            ->where(function ($query) use ($mapor) {
            $query->whereOr($mapor);
             })
            ->field('a.*,b.nickName,b.avatarUrl')
            ->group('a.id')
            ->order('id desc')
            ->paginate($page)
            ->toArray();

        if(!empty($data['data'])){

            $admin_model = new Admin();

            foreach ($data['data'] as &$v){

                $admin = $admin_model->dataInfo(['id'=>$v['admin_id'],'status'=>1]);

                if(!empty($admin['salesman_auth'])){

                    $v['admin_name'] = $admin['agent_name'];
                }else{

                    $v['admin_id'] = 0;
                }

                if($v['balance']<0){

                    $v['balance'] = getConfigSetting($v['uniacid'],'salesman_balance');
                }

            }
        }

        return $data;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-27 15:32
     * @功能说明:业务员相关的订单金额
     */
    public function salesmanOrderPrice($salesman_id,$channel_id=0,$type=1,$count_start_time=0,$count_end_time=0){

        $order_model = new Order();

        $dis = [

            ['pay_type', '=', 7],

            ['salesman_id', '=', $salesman_id]
        ];

        if (!empty($channel_id)) {

            $dis[] = ['channel_id', '=', $channel_id];
        }

        if (!empty($count_start_time) && !empty($count_end_time)) {

            $dis[] = ['create_time', 'between', "{$count_start_time},{$count_end_time}"];
        }

        if($type==1){

            $price = $order_model->where($dis)->sum('true_service_price');

            return round($price,2);

        }elseif ($type==3){

            $price = $order_model->where($dis)->sum('material_price');

            return round($price,2);

        }else{

            $order_id = $order_model->where($dis)->column('id');

            return $order_id;
        }

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-28 10:47
     * @功能说明:业务员渠道商佣金
     */
    public function getSalesmanChannelCash($salesman_id,$channel_id){

        $order_id = $this->salesmanOrderPrice($salesman_id,$channel_id,2);

        $comm_model = new Commission();

        $dis = [

            'type' => 12,

            'status' => 2
        ];

        $cash = $comm_model->where('order_id','in',$order_id)->where($dis)->sum('cash');

        return round($cash,2);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-27 15:55
     * @功能说明:获取业务员历史订单的渠道商
     */
    public function getSalesmanCash($salesman_id){

        $comm_model = new Commission();

        $dis = [

            'top_id' => $salesman_id,

            'status' => 2,

            'type'   => 12
        ];

        $cash = $comm_model->where($dis)->sum('cash');

        return round($cash,2);
    }


    /**
     * @param $data
     * @功能说明:获取业务员审核结果
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-06 16:28
     */
    public function checkAuthData($data){

        $cap_dis[] = ['user_id','=',$data['id']];

        $cap_dis[] = ['status','in',[1,2,3,4]];
        //查看是否是团长
        $cap_info = $this->where($cap_dis)->order('id desc')->find();

        $cap_info = !empty($cap_info)?$cap_info->toArray():[];
        //-1表示未申请，1申请中，2已通过，3取消,4拒绝
        $arr['salesman_status']  = !empty($cap_info)?$cap_info['status']:-1;

        $arr['salesman_sh_text'] = !empty($cap_info)?$cap_info['sh_text']:'';

        $arr['wallet_status']    = in_array($arr['salesman_status'],[2,3])?1:0;

        return $arr;
    }













}