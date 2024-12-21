<?php
namespace app\massage\server;
use app\ApiRest;
use app\BaseController;
use app\member\model\Config;
use app\member\model\Goods;
use app\member\model\Member;
use app\member\model\StoredOrder;
use app\publics\model\TmplConfig;
use app\shop\model\BargainRecord;
use longbingcore\wxcore\WxTmpl;
use think\App;
use think\facade\Db;
use app\shop\model\IndexShopOrder as OrderModel;
use app\shop\model\IndexUserInfo;
use app\shop\model\IndexUser;
use app\shop\model\IndexGoods;
use app\shop\model\IndexShopSpePrice;
use app\shop\model\IndexAddress;
use app\shop\model\IndexShopOrderGoods;
use app\shop\model\IndexCouponRecord;
use app\shop\model\IndexShopCollageList;
use app\shop\model\IndexUserCollage;
use app\shop\model\IndexSellingProfit;
use app\shop\model\IndexSellingWater;
use app\shop\model\IndexCardCount;
use work;


class RoleAuth
{

    public $_observer = [];


    public function __construct() {


    }

    /**
     * @purpose: 添加观察者
     * @param string $key 给所添加的观察者的一个唯一 key，方便从注册树中移除观察者
     * @param Observer $observer 观察者对象
     * @return mixed
     */
    public function addObserver( $observer)
    {
        $this->_observer[] = $observer;
    }

    /**
     * @purpose: 从注册树中移除观察者
     * @param string $key 给所添加的观察者的一个唯一 key，方便从注册树中移除观察者
     * @return mixed
     */
    public function removeObserver($key)
    {
        unset($this->_observer[$key]);
    }

    /**
     * @purpose: 广播通知以注册的观察者，对注册树进行遍历，让每个对象实现其接口提供的操作
     * @return mixed
     */
    public function notify($data)
    {

        if(!empty($this->_observer)){

            foreach ($this->_observer as $observer){

                $arr = $observer->checkAuthData($data);

                if(isset($arr['wallet_status'])&&$arr['wallet_status']==0){

                    unset($arr['wallet_status']);
                }

                $data = array_merge($data,$arr);

            }

        }

        return $data;

    }


}
