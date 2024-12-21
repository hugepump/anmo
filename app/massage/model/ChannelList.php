<?php
namespace app\massage\model;

use app\BaseModel;
use longbingcore\permissions\AdminMenu;
use think\facade\Db;

class ChannelList extends BaseModel
{
    //定义表名
    protected $name = 'massage_channel_list';


    protected $append = [

        'cate_text'

    ];


    public function getTrueUserNameAttr($value,$data){

        if(isset($value)){

            if(!empty($value)){

                return $value;

            }elseif (!empty($data['user_name'])){

                return $data['user_name'];
            }
        }
    }

    public function getMobileAttr($value,$data){

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
     * @功能说明:返回渠道
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-09-02 16:32
     */
    public function getCateTextAttr($value,$data){

        if(isset($data['cate_id'])){

            $cate_model = new ChannelCate();

            return $cate_model->where(['id'=>$data['cate_id']])->value('title');

        }
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

            $admin = $admin_model->where(['id'=>$value,'status'=>1,'channel_auth'=>1])->count();

            return $admin>0?$value:0;

        }else{

            return 0;
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
    public function dataList($dis,$page=10,$field='*'){

        $data = $this->where($dis)->field($field)->order('id desc')->paginate($page)->toArray();

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
     * @DataTime: 2021-12-30 11:26
     * @功能说明:后台列表
     */
    public function adminDataList($dis,$page=10,$where=[]){

        $data = $this->alias('a')
            ->join('massage_service_user_list b','a.user_id = b.id','left')
            ->where($dis)
            ->where(function ($query) use ($where){
                $query->whereOr($where);
            })
            ->field('a.*,b.nickName,b.avatarUrl')
            ->group('a.id')
            ->order('a.id desc')
            ->paginate($page)
            ->toArray();

        return $data;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-23 11:58
     * @功能说明:渠道商提成比例 业务员
     */
    public function channelBalance($order){

        if(getPromotionRoleAuth(2,$order['uniacid'])==0){

            $order['channel_id'] = 0;

            $order['salesman_id'] = 0;
        }

        $config = getConfigSettingArr($order['uniacid'],['salesman_channel_fx_type','channel_balance','salesman_balance','channel_coach_balance','channel_admin_balance','salesman_coach_balance','salesman_admin_balance']);

        $channel_model = new ChannelList();

        $channel_balance = $channel_model->where(['id'=>$order['channel_id']])->value('balance');
        //渠道商单独设置了比例
        if($channel_balance>=0){

            $config['channel_balance'] = $channel_balance;
        }

        //使用的渠道码
        if(!empty($order['channel_qr_id'])&&!empty($order['channel_id'])){

            $qr_model = new ChannelQr();

            $qr = $qr_model->dataInfo(['id'=>$order['channel_qr_id'],'status'=>1]);

            if(!empty($qr)){

                $config['channel_balance'] = $qr['balance'];
            }
        }

        $order = array_merge($order,$config);
        //渠道商平台承担比例
        $order['channel_company_balance'] = 100-$order['channel_coach_balance'] - $order['channel_admin_balance'];
        //业务员平台承担比例
        $order['salesman_company_balance']= 100-$order['salesman_coach_balance'] - $order['salesman_admin_balance'];

        if(!empty($order['channel_id'])){

            $channel = $this->dataInfo(['id'=>$order['channel_id'],'status'=>2]);

            if(!empty($channel)){

                $channel['channel_id'] = $channel['id'];

                $salesman_model = new Salesman();

                $salesman = $salesman_model->dataInfo(['id'=>$channel['salesman_id'],'status'=>2]);

                if(!empty($salesman)&&getPromotionRoleAuth(4,$order['uniacid'])!=0){
                    //业务员比例可能单独设置
                    $order['salesman_balance'] = $salesman['balance']>=0?$salesman['balance']:$order['salesman_balance'];

                    $order['salesman_id'] = $salesman['id'];
                }
            }else{

                $channel['channel_id'] = 0;
            }
        }
        //业务员渠道商分销方式 相减模式
        if($config['salesman_channel_fx_type']==2&&!empty($order['salesman_id'])){

            $order['channel_balance'] = $order['channel_balance']<$order['salesman_balance']?$order['channel_balance']:$order['salesman_balance'];

            $order['salesman_balance'] = $order['salesman_balance']-$order['channel_balance'];
        }

        $addclockBalance_model = new AddClockBalance();

        if(!empty($channel)){

            $order['channel_balance'] = $addclockBalance_model->getObjBalance($order,$order['channel_balance'],4,$channel['admin_id']);
        }

        if(!empty($salesman)){

            $order['salesman_balance'] = $addclockBalance_model->getObjBalance($order,$order['salesman_balance'],3,$salesman['admin_id']);
        }

        if(empty($order['channel_balance'])||$order['channel_balance']<=0){

            $order['channel_id'] = 0;
        }

        if(empty($order['salesman_balance'])||$order['salesman_balance']<=0){

            $order['salesman_id'] = 0;
        }

        return $order;
    }



    /**
     * @param $data
     * @功能说明:是否是管理员
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-06 16:50
     */
    public function checkAuthData($data){

        $cap_dis[] = ['user_id','=',$data['id']];

        $cap_dis[] = ['status','in',[1,2,3,4]];
        //查看是否是团长
        $cap_info = $this->where($cap_dis)->order('id desc')->find();

        $cap_info = !empty($cap_info)?$cap_info->toArray():[];
        //-1表示未申请团长，1申请中，2已通过，3取消,4拒绝
        $arr['channel_status'] = !empty($cap_info)?$cap_info['status']:-1;

        $arr['channel_text'] = !empty($cap_info)?$cap_info['sh_text']:'';

        $arr['wallet_status']  = in_array($arr['channel_status'],[2,3])?1:0;

        return $arr;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-06-21 13:48
     * @功能说明:获取已经绑定了业务员的渠道商
     */
    public function getBindSalesmanChannel(){

        $dis = [

            'b.status' => 2
        ];

        $data = $this->alias('a')
                ->join('massage_salesman_list b','a.salesman_id = b.id')
                ->where($dis)
                ->where('a.status','>',-1)
                ->field('a.id')
                ->group('a.id')
                ->column('a.id');

        return $data;
    }


    /**
     * @param $user_id
     * @功能说明:通过用户获取渠道商没有则生成
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-07-31 16:45
     */
    public function getUserChannel($user_id,$uniacid,$admin_id=0,$salesman_id=0){

        $dis[] = ['user_id','=',$user_id];

        $dis[] = ['status','>',-1];

        $channel = $this->dataInfo($dis);

        if(!empty($channel)&&$channel['status']!=2){

            $this->dataUpdate(['id'=>$channel['id']],['status'=>2,'is_qr'=>1,'sh_time'=>time()]);
        }
        if(empty($channel)){

            $user_model = new User();

            $user = $user_model->dataInfo(['id'=>$user_id]);

            $insert = [

                'uniacid'  => $uniacid,

                'status'   => 2,

                'user_id'  => $user_id,

                'admin_id' => $admin_id,

                'salesman_id' => $salesman_id,

                'user_name'=> $user['nickName'],

                'mobile'   => $user['phone'],

                'is_qr'    => 1,

                'create_time' => time(),

                'sh_time' => time(),

            ];

            $this->dataAdd($insert);

            $channel_id = $this->getLastInsID();
        }else{

            $channel_id = $channel['id'];
        }

        return $channel_id;

    }
   









}