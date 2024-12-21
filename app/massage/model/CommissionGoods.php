<?php
namespace app\massage\model;

use app\BaseModel;
use think\facade\Db;

class CommissionGoods extends BaseModel
{
    //定义表名
    protected $name = 'massage_service_order_commission_goods';


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-08-25 23:19
     * @功能说明:
     */
    public function goodsList($dis){

        $data = $this->alias('a')
                ->join('massage_service_order_goods_list b','a.order_goods_id = b.id')
                ->where($dis)
                ->field('a.*,b.goods_name,b.goods_cover,round(b.true_price,2) as true_price,b.material_price,b.price,b.init_material_price')
                ->group('a.id')
                ->select()
                ->toArray();

        return $data;
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
    public function dataList($dis,$page=10){

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