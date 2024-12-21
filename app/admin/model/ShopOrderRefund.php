<?php
namespace app\admin\model;
use app\BaseModel;
use think\facade\Db;
use app\admin\model\ShopOrderGoods;
use app\admin\model\ShopOrder;



class ShopOrderRefund extends BaseModel
{
    //定义表名
    protected $name = 'card_shop_order_refund';
    protected $orer_goods_model;


    protected static function init()
    {
        //TODO:初始化内容
    }
    protected $append = [
        'goods_text',
        'order_text'
    ];
    /**
     * @param $value
     * @param $data
     * @return mixed
     * 获取器 获取子订单
     */
    public function getGoodsTextAttr($value,$data){
        $orer_goods_model = new ShopOrderGoods();
        $dis = !empty($data['order_goods_id'])?['id'=>$data['order_goods_id']]:['order_id'=>$data['order_id']];
        return $orer_goods_model->orderGoodsInfo($dis);
    }
    /**
     * @param $value
     * @param $data
     * @return mixed
     * 获取器 获取订单
     */
    public function getOrderTextAttr($value,$data){
        $orer_model = new ShopOrder();
        $dis['id']  = $data['order_id'];
//        $field      = ''
        return $orer_model->orderInfo($dis);

    }
    /**
     * 获取订单列表
     */
    public function refundList($dis,$page=10){
        $data         = self::where($dis)->paginate($page)->toArray();
        $data['data'] = $this->changeInfo($data['data']);
        return $data;
    }

    /**
     * 修改订单
     */

    public function refundUpdate($dis,$data){
        $data['update_time'] = time();
        $res = Db::name($this->name)->where($dis)->update($data);
        return $res;
    }

    /**
     * 获取订单详情
     */

    public function refundInfo($dis){
        $data = self::where($dis)->find()->toArray();
        return $data;
    }

    /**
     * @param $data
     * 转换数据
     */
    public function changeInfo($data){
        if(!empty($data)){
            foreach ($data as &$v){
                $v['price']  = empty($v['price'])?$v['order_text']['price']:$v['price'];
                $v['goods_text'][0]['number'] = !empty($v['number'])?$v['number']:$v['goods_text'][0]['number'];
            }
        }
        return $data;
    }




}