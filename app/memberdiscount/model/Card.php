<?php
namespace app\memberdiscount\model;

use app\BaseModel;
use think\facade\Db;

class Card extends BaseModel
{
    //定义表名
    protected $name = 'massage_member_discount_card_list';



    public function getPriceAttr($value,$data){

        if(isset($value)){

            return round($value,2);
        }
    }




    public function getInitPriceAttr($value,$data){

        if(isset($value)){

            return round($value,2);
        }
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
     * @DataTime: 2024-09-04 11:24
     * @功能说明:卡券关联的优惠券
     */
    public function cardCoupon($card_id){

        $coupon_model = new Coupon();

        $dis = [

            'a.card_id' => $card_id,

            'b.status'  => 1
        ];

        $data = $coupon_model->alias('a')
                ->join('massage_service_coupon b','a.coupon_id = b.id')
                ->where($dis)
                ->group('a.coupon_id')
                ->field('a.*,b.title,b.full,b.discount,b.type,round(a.num*b.discount,2) as discount_price')
                ->select()
                ->toArray();

        return $data;
    }








}