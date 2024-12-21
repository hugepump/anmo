<?php
namespace app\massage\model;

use app\BaseModel;
use think\facade\Db;

class CouponAtvRecord extends BaseModel
{
    //定义表名
    protected $name = 'massage_service_coupon_atv_record';




    protected $append = [

        'coupon'
    ];


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-12 23:06
     * @功能说明:派发的优惠券
     */
    public function getCouponAttr($value,$data){

        if(!empty($data['id'])){

            $list_model = new CouponAtvRecordCoupon();

            $dis = [

                'a.record_id' => $data['id'],

                'a.user_id'   => 0,

               // 'b.status'    => 1
            ];

            $list = $list_model->alias('a')
                    ->join('massage_service_coupon b','a.coupon_id = b.id')
                    ->where($dis)
                    ->where('b.status','>',-1)
                    ->field('a.*,b.title,b.stock,b.i')
                    ->group('a.coupon_id')
                    ->select()
                    ->toArray();

            return $list;

        }
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-19 10:47
     * @功能说明:判断卡券是否参加活动
     */
    public function couponIsAtv($coupon_id){

        $dis_where[] = ['status','=',1];

        $dis_where[] = ['end_time','<',time()];
        //修改过期状态
        $this->dataUpdate($dis_where,['status'=>3]);

        $dis = [
            //活动进行中
            'a.status' => 1,

            'b.coupon_id' => $coupon_id
        ];

        $data = $this->alias('a')
                ->join('massage_service_coupon_atv_record_coupon b','a.id = b.record_id')
                ->where($dis)
                ->count();

        return !empty($data)?true:false;
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
     * @DataTime: 2021-04-08 17:08
     * @功能说明:审核中
     */
    public function shIng($cap_id){

        $dis = [

            'cap_id' => $cap_id,

            'status' => 1
        ];

        $count = $this->where($dis)->count();

        return $count;

    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:06
     * @功能说明:列表
     */
    public function dataList($dis,$page=10){

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
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-06 00:02
     * @功能说明:用户订单数
     */
    public function couponCount($user_id){

        $dis[] = ['user_id','=',$user_id];

        $dis[] = ['status','=',1];

        $dis[] = ['end_time','>',time()];

        $data = $this->where($dis)->count();

        return $data;
    }



















}