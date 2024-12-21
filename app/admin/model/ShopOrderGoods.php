<?php
namespace app\admin\model;

use app\BaseModel;
use think\facade\Db;


class ShopOrderGoods extends BaseModel
{
    //定义表名
    protected $name = 'longbing_card_shop_order_item';

    //初始化


    protected static function init()
    {
        //TODO:初始化内容
    }

    /**
     *
     * 获取自订单详情
     */
    public function orderGoodsInfo($dis){
       return self::where($dis)->select()->toArray();
    }





}