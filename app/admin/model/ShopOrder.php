<?php
namespace app\admin\model;
use app\BaseModel;
use think\facade\Db;
use app\admin\model\ShopOrderGoods;



class ShopOrder extends BaseModel
{
    //定义表名
    protected $name = 'longbing_card_shop_order';

    protected static function init()
    {
        //TODO:初始化内容
    }
    protected $append = [
        'goods_text',
    ];
    /**
     * @param $value
     * @param $data
     * @return mixed
     * 查询器 获取子订单
     */
    public function getGoodsTextAttr($value,$data)
    {
        $orer_goods      = new ShopOrderGoods();
        $dis['order_id'] = $data['id'];
        return $orer_goods->orderGoodsInfo($dis);
    }
    /**
     * @param $dis
     * @param int $page
     * @return mixed
     * 获取订单列表
     */
    public function orderList($dis,$page=10){
        $data = self::where($dis)->paginate($page)->toArray();
        return $data;
    }
    /**
     * @param $dis
     * @param $data
     * @return int
     * 修改订单
     */

    public function orderUpdate($dis,$data){
        $data['update_time'] = time();
        $res = self::where($dis)->update($data);
        return $res;
    }

    /**
     * @param $dis
     * @return mixed
     * 获取订单详情
     */

    public function orderInfo($dis,$field='*'){
        $data = self::where($dis)->field($field)->find();
        return !empty($data)?$data->toArray():$data;
    }

    //查询公司的金额
    public function getCompanyPrice($company_id,$start,$end){
        return $this->whereIn('company_id',$company_id)->whereBetween('create_time',[$start,$end])->where('pay_status',1)->where('order_status',3)->sum('total_price');
    }


}