<?php
namespace app\massage\model;

use app\BaseModel;
use think\facade\Db;

class CouponAtvCoupon extends BaseModel
{
    //定义表名
    protected $name = 'massage_service_coupon_atv_coupon';




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:04
     * @功能说明:添加
     */
    public function dataAdd($data){

//        $data['create_time'] = time();

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
     * @DataTime: 2021-03-17 15:23
     * @功能说明:通过
     */
    public function subSh($id,$goods_id){

        $goods_list_model = new GoodsShList();

        $goods_model = new Goods();

        $list = $goods_model->where('id','in',$goods_id)->select()->toArray();

        Db::startTrans();

        foreach ($list as $value){

            $insert = [

                'uniacid'    => $value['uniacid'],

                'sh_id'      => $id,

                'goods_id'   => $value['id'],

                'goods_name' => $value['goods_name'],

                'cover'      => $value['cover'],

                'imgs'       => !empty($value['imgs'])?implode(',',$value['imgs']):'',

                'text'       => $value['text'],

                'cate_id'    => $value['cate_id'],

            ];
            //添加到审核商品表
            $res = $goods_list_model->dataAdd($insert);

            if($res!=1){

                Db::rollback();

                return ['code'=>500,'msg'=>'提交失败'];
            }

            $goods_sh_id = $goods_list_model->getLastInsID();

            if(!empty($value['spe'])){

                foreach ($value['spe'] as $v){

                    $insert = [

                        'uniacid'     => $v['uniacid'],

                        'sh_goods_id' => $goods_sh_id,

                        'title'       => $v['title'],

                        'stock'       => $v['stock'],

                        'price'       => $v['price'],

                        'spe_id'      => $v['id'],

                    ];
                    //添加审核规格表
                    $res = Db::name('shequshop_school_goods_sh_spe')->insert($insert);

                    if($res!=1){

                        Db::rollback();

                        return ['code'=>500,'msg'=>'提交失败'];
                    }

                }

            }

        }
        //将商品状态改为审核中
        $goods_model->where('id','in',$goods_id)->update(['status'=>4]);

        Db::commit();

        return true;

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