<?php
namespace app\coachbroker\model;

use app\BaseModel;
use app\massage\model\Coach;
use app\massage\model\Commission;
use app\massage\model\Config;
use app\massage\model\DistributionList;
use app\massage\model\Order;
use app\massage\model\RefundOrder;
use app\massage\model\User;
use think\facade\Db;

class CoachBroker extends BaseModel
{



    protected $name = 'massage_coach_broker_list';


    public function getMobileAttr($value,$data){

        if(!empty($value)&&isset($data['uniacid'])){

            if(numberEncryption($data['uniacid'])==1){

                return  substr_replace($value, "****", 2,4);
            }

        }

        return $value;
    }



    public function getTrueUserNameAttr($value,$data){

        if(isset($value)){

            if(!empty($value)){

                return $value;

            }elseif (!empty($data['user_name'])){

                return $data['user_name'];
            }
        }
    }


    /**
     * @param $uniacid
     * @功能说明:初始化经纪人 兼容以前的数据
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-12-19 14:45
     */
    public function initBroker($uniacid){

        $coach_model = new Coach();

        $distribution_model = new DistributionList();

        $comm_model   = new Commission();

        $order_model  = new Order();

        $refund_model = new RefundOrder();

        $config_model = new Config();

        $config_info = $config_model->dataInfo(['uniacid'=>$uniacid]);

        $coach_broker_id  = $coach_model->where(['uniacid'=>$uniacid,'broker_id'=>0])->where('partner_id','>',0)->where('status','in',[1,2,3])->group('partner_id')->column('partner_id');

        $order_broker_id  = $order_model->where(['uniacid'=>$uniacid,'broker_id'=>0])->where('partner_id','>',0)->group('partner_id')->column('partner_id');

        $broker_id = array_values(array_unique(array_merge($coach_broker_id,$order_broker_id)));

        $key = 'initBroker';

        incCache($key,1,$uniacid);

        if(getCache($key,$uniacid)==1){

            Db::startTrans();

            if(!empty($broker_id)){

                foreach ($broker_id as $key=>$value){

                    $find = $this->dataInfo(['user_id'=>$value]);

                    if(!empty($find)){

                        continue;
                    }

                    $distribution_info = $distribution_model->where(['user_id'=>$value])->order('id desc')->find();

                    if(!empty($distribution_info)){

                        $distribution_info = $distribution_info->toArray();

                        $user_name = $distribution_info['user_name'];

                        $mobile    = $distribution_info['mobile'];

                        $sh_time   = $distribution_info['sh_time'];

                        $create_time = $distribution_info['create_time'];

                        $status    = $distribution_info['status'];
                    }else{

                        $user_model = new \app\massage\model\User();

                        $user = $user_model->dataInfo(['id'=>$value]);

                        if(empty($user)){

                            continue;
                        }

                        $user_name = $user['nickName'];

                        $mobile    = $user['phone'];

                        $sh_time   = $user['create_time'];

                        $create_time = $user['create_time'];

                        $status = 2;

                    }

                    $insert[$key] = [

                        'uniacid' => $uniacid,

                        'user_name' => $user_name,

                        'user_id' => $value,

                        'mobile' => $mobile,

                        'create_time' => $create_time,

                        'sh_time' => $sh_time,

                        'status' => $status,
                    ];
                }

                if(!empty($insert)){

                    $this->saveAll(array_values($insert));
                }
            }

            if(!empty($order_broker_id)){

                foreach ($order_broker_id as $value){

                    $id = $this->where(['user_id'=>$value])->order('id desc')->value('id');

                    if(!empty($id)){

                        $order_model->where(['partner_id'=>$value,'broker_id'=>0])->update(['broker_id'=>$id]);

                        $refund_model->where(['partner_id'=>$value,'broker_id'=>0])->update(['broker_id'=>$id]);

                        $comm_model->where(['top_id'=>$value,'type'=>9,'broker_id'=>0])->update(['broker_id'=>$id]);
                    }
                }
            }

            if(!empty($coach_broker_id)){

                foreach ($coach_broker_id as $value){

                    $id = $this->where(['user_id'=>$value])->value('id');

                    if(!empty($id)){

                        $coach_model->where(['partner_id'=>$value,'broker_id'=>0])->update(['broker_id'=>$id]);
                    }
                }
            }
        }

        Db::commit();

        decCache($key,1,$uniacid);

        return true;
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
     * @param $user_id
     * @param int $type
     * @功能说明:团队人数
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-07-28 17:58
     */
    public function teamCount($user_id,$type=1){

        $user_model = new User();

        $dis[] = ['is_fx','=',1];

        if($type==1){

            $dis[] = ['pid','=',$user_id];

        }else{

            $top_id = $user_model->where(['pid'=>$user_id,'is_fx'=>1])->column('id');

            $dis[] = ['pid','in',$top_id];

        }

        $data = $user_model->where($dis)->count();

        return $data;

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
     * @param $dis
     * @param int $page
     * @功能说明:用户收益列表
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-07-29 14:50
     */
    public function userProfitList($dis,$page=10,$where=[]){

        $user_model = new User();

        $data = $user_model->alias('a')
                ->join('massage_distribution_list b','a.id = b.user_id','left')
                ->where($dis)
                ->where(function ($query) use ($where){
                    $query->whereOr($where);
                })
                ->field('b.*,a.nickName,a.avatarUrl,a.fx_cash')
                ->group('a.id')
                ->order('a.id desc')
                ->paginate($page)
                ->toArray();

        return $data;
    }


    /**
     * @param $dis
     * @param int $page
     * @功能说明:用户收益列表
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-07-29 14:50
     */
    public function userProfitSelect($dis,$where=[]){

        $user_model = new User();

        $data = $user_model->alias('a')
            ->join('massage_distribution_list b','a.id = b.user_id','left')
            ->where($dis)
            ->where(function ($query) use ($where){
                $query->whereOr($where);
            })
            ->field('b.*,a.nickName,a.avatarUrl,a.fx_cash')
            ->group('a.id')
            ->order('a.id desc')
            ->select()
            ->toArray();

        return $data;
    }


    /**
     * @param $dis
     * @param $where
     * @param int $page
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-23 10:28
     */
    public function userDataList($dis,$where,$page=10){

        $user_model = new User();

        $data = $user_model
            ->alias('a')
            ->join('massage_coach_broker_list b','a.id = b.user_id','left')
            ->where($dis)
            ->where(function ($query) use ($where){
                $query->whereOr($where);
            })
            ->field('b.*, ifnull(b.sh_time,0) as sh_time,a.nickName,a.avatarUrl,a.new_cash,a.cash as new_cash')
            ->group('a.id')
            ->order('sh_time desc,a.id desc')
            ->paginate($page)
            ->toArray();

        if(!empty($data['data'])){

            $coach_model = new Coach();

            foreach ($data['data'] as &$v){

                $v['date'] = date('Y-m-d H:i:s',$v['sh_time']);
                //邀请技师数量
                $v['coach_count'] = $coach_model->where(['status'=>2,'broker_id'=>$v['id']])->count();
                //订单量
                $v['order_count'] = $this->partnerOrderCount($v['id']);
                //佣金
                $v['order_price'] = $this->partnerOrderCount($v['id'],2);

                if(numberEncryption($v['uniacid'])==1){

                    $v['mobile'] = substr_replace($v['mobile'], "****", 2,4);
                }
            }
        }

        return $data;
    }


    /**
     * @param $partner_id
     * @功能说明:合伙人相关订单数量
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-23 11:53
     */
    public function partnerOrderCount($broker_id,$type=1){

        $commis_model = new Commission();

        if($type==1){
            //订单量
            $count = $commis_model->where(['broker_id'=>$broker_id,'status'=>2,'type'=>9])->group('order_id')->count();

        }elseif($type==2){
            //佣金
            $count = $commis_model->where(['broker_id'=>$broker_id,'status'=>2,'type'=>9])->group('order_id')->sum('cash');

            $count = round($count,2);

        }elseif($type==3){

            $count = $commis_model->where(['broker_id'=>$broker_id,'status'=>2,'type'=>9])->whereTime('create_time','today')->group('order_id')->count();

        }elseif ($type==4){
            //佣金
            $count = $commis_model->where(['broker_id'=>$broker_id,'status'=>1,'type'=>9])->group('order_id')->sum('cash');

            $count = round($count,2);
        }

        return $count;
    }




    /**
     * @param $partner_id
     * @功能说明:合伙人相关订单数量
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-23 11:53
     */
    public function partnerOrderPrice($partner_id){

        $commis_model = new Commission();

        $order_model  = new Order();

        $order_id = $commis_model->where(['top_id'=>$partner_id,'status'=>2])->where('type','in',[1,9])->column('order_id');

        $count = $order_model->where('id','in',$order_id)->sum('true_service_price');

        return $count;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-06 16:43
     * @功能说明:获取审核结果
     */
    public function checkAuthData($data){

        $cap_dis[] = ['user_id','=',$data['id']];

        $cap_dis[] = ['status','in',[1,2,3,4]];
        //查看是否是团长
        $cap_info = $this->where($cap_dis)->order('id desc')->find();

        $fx = !empty($cap_info)?$cap_info->toArray():[];

        $arr['broker_status'] = !empty($fx)?$fx['status']:-1;

        $arr['broker_sh_text']   = !empty($fx)?$fx['sh_text']:'';

     //   $arr['broker_wallet_status']  = in_array($arr['fx_status'],[2,3])?1:0;

        return $arr;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-19 18:20
     * @功能说明:获取合伙人id
     */
    public function getBrokerId($name){

     //   $where[] = ['id','=',$name];

        $where[] = ['user_name','like','%'.$name.'%'];

        $where[] = ['status','=',2];

        $id = $this->where($where)->column('id');

        return $id;
    }







}