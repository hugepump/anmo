<?php
namespace app\massage\model;

use app\BaseModel;
use think\facade\Db;

class CouponAtv extends BaseModel
{
    //定义表名
    protected $name = 'massage_service_coupon_atv';



    protected $append = [

        'coupon'

    ];


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-23 13:46
     * @功能说明:分类下面端商品数量
     */
    public function getCouponAttr($value,$data){

        if(!empty($data['id'])){

            $coupom_model = new Coupon();

            $dis = [

                'a.status' => 1,

                'b.atv_id' => $data['id']
            ];

            $list =  $coupom_model->alias('a')
                ->join('massage_service_coupon_atv_coupon b','b.coupon_id = a.id')
                ->where($dis)
                ->field('a.*,b.num,b.coupon_id')
                ->group('b.coupon_id')
                ->order('a.top desc,id desc')
                ->select()
                ->toArray();

            return $list;
        }

    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:04
     * @功能说明:添加
     */
    public function dataAdd($data){


        $service = $data['coupon'];

        unset($data['coupon']);

        $res = $this->insert($data);

        $id  = $this->getLastInsID();

        $this->updateSome($id,$data['uniacid'],$service);

        return $id;

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:05
     * @功能说明:编辑
     */
    public function dataUpdate($dis,$data){


//        $data['update_time'] = time();

        if(isset($data['coupon'])){

            $service = $data['coupon'];

            unset($data['coupon']);
        }

        $res = $this->where($dis)->update($data);

        if(!empty($service)){

            $id = $this->where($dis)->value('id');

            $this->updateSome($id,$data['uniacid'],$service);
        }


        return $res;

    }


    /**
     * @param $id
     * @param $uniacid
     * @param $spe
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-23 13:35
     */
    public function updateSome($id,$uniacid,$coupon){

        $s_model = new CouponAtvCoupon();

        $s_model->where(['atv_id'=>$id])->delete();

        if(!empty($coupon)){

            foreach ($coupon as $value){

                $insert['uniacid']   = $uniacid;

                $insert['atv_id']    = $id;

                $insert['coupon_id'] = $value['id'];

                $insert['num']       = $value['num'];

                $s_model->dataAdd($insert);

            }

        }

        return true;

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

        if(empty($data)){

            $this->insert($dis);

            $data = $this->where($dis)->find();
        }

        return !empty($data)?$data->toArray():[];

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-12 17:52
     * @功能说明:优惠券活动邀请新用户
     */
    public function invUser($user_id,$record_id){

        $atv_record_model        = new CouponAtvRecord();

        $atv_record_list_model   = new CouponAtvRecordList();

        $record = $atv_record_model->dataInfo(['id'=>$record_id]);

        Db::startTrans();

        if(!empty($record)&&$record['end_time']>time()&&$record['status']==1){

            $insert = [

                'uniacid'  => $record['uniacid'],

                'user_id'  => $record['user_id'],

                'to_inv_id'=> $user_id,

                'record_id'=> $record_id

            ];
            //添加邀请者记录
            $res = $atv_record_list_model->dataAdd($insert);

            if(!empty($res['code'])){

                Db::rollback();

                return ['code'=>500,'msg'=>'登陆失败，请刷新重试'];
            }
            //新用户获得卡券
            if($record['to_inv_user']==1&&!empty($record['coupon'])){

                $res = $this->giveAtvCoupon($record_id,$user_id);

                if(!empty($res['code'])){

                    Db::rollback();

                    return $res;
                }

            }
            //检查任务是否完成
            $res = $this->recordSuccess($record_id);

            if(!empty($res['code'])){

                Db::rollback();

                return $res;
            }

        }

        Db::commit();

        return true;
    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-13 00:07
     * @功能说明:给用户获得卡券
     */
    public function giveAtvCoupon($record_id,$user_id){

        $atv_record_model = new CouponAtvRecord();

        $record_model     = new CouponRecord();

        $atv_record_coupon_model = new CouponAtvRecordCoupon();

        $record = $atv_record_model->dataInfo(['id'=>$record_id]);

        foreach ($record['coupon'] as $value){
            //派发卡券
//            $num = $value['stock']>=$value['num']?$value['num']:$value['stock'];

            $num = $value['num'];

            if($num>0){

                $res = $record_model->recordAdd($value['coupon_id'],$user_id,$num);

                if($res==0){

                    return ['code'=>500,'msg'=>'登陆失败，请刷新重试'];
                }
                //添加派发记录
                $insert = [

                    'uniacid'     => $record['uniacid'],

                    'user_id'     => $user_id,

                    'atv_id'      => $record['atv_id'],

                    'coupon_id'   => $value['coupon_id'],

                    'num'         => $value['num'],

                    'status'      => 2,

                    'success_num' => $num

                ];

                $res = $atv_record_coupon_model->dataAdd($insert);

                if($res==0){

                    return ['code'=>500,'msg'=>'登陆失败，请刷新重试'];
                }

            }

        }

        return true;

    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-12 23:46
     * @功能说明:获得成功
     */
    public function recordSuccess($record_id){

        $atv_record_list_model = new CouponAtvRecordList();

        $atv_record_model      = new CouponAtvRecord();
        //已经邀请多少人了
        $have_num = $atv_record_list_model->where(['record_id'=>$record_id])->count();

        $record   = $atv_record_model->dataInfo(['id'=>$record_id]);
        //如果成功
        if($have_num>=$record['inv_user_num']){
            //修改获得状态
            $res = $atv_record_model->dataUpdate(['id'=>$record['id'],'status'=>1],['status'=>2]);

            if($res==0){

                return ['code'=>500,'msg'=>'登陆失败，请刷新重试'];
            }

            if($record['inv_user']==1){
                //给发起者派送卡券
                $res = $this->giveAtvCoupon($record_id,$record['user_id']);

                if(!empty($res['code'])){

                    return $res;
                }

            }

        }

        return true;
    }







}