<?php
namespace app\massage\model;

use app\BaseModel;
use think\facade\Db;

class CouponService extends BaseModel
{
    //定义表名
    protected $name = 'massage_service_coupon_goods';





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
    public function goodsUpdate($dis,$data){

        $spe = $data['spe'];

        unset($data['spe']);

        $res = $this->where($dis)->update($data);

        $this->updateSome($dis['id'],$data['uniacid'],$spe);

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
    public function updateSome($id,$uniacid,$coach){

        $spe_model = new GoodsSpe();

        $spe_model->where(['goods_id'=>$id])->delete();

        if(!empty($spe)){

            foreach ($spe as $value){

                $value['uniacid']  = $uniacid;

                $value['goods_id'] = $id;

                $spe_model->dataAdd($value);
            }
        }

        return true;
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









}