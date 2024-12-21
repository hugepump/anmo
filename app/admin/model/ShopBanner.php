<?php
namespace app\admin\model;
use app\admin\model\ShopOrderGoods;
use app\BaseModel;
use think\facade\Db;

class ShopBanner extends BaseModel
{
    //定义表名
    protected $name = 'longbing_card_shop_carousel';

    protected static function init()
    {
        //TODO:初始化内容
    }
    /**
     * @param $dis
     * @param int $page
     * @return mixed
     * 获取商城轮播图列表
     */
    public function bannerList($dis,$page=10){
        $data = self::where($dis)->paginate($page)->toArray();
        return $data;
    }
    /**
     * @param $dis
     * @param $data
     * @return int
     * 修改商城轮播图
     */
    public function bannerUpdate($dis,$data){
        $data['update_time'] = time();
        $res = self::where($dis)->update($data);
        return $res;
    }

    /**
     * @param $data
     * 添加商城轮播图
     */
    public function bannerAdd($data){
        $data['create_time'] = time();
        $res = self::insert($data);
        return $res;
    }

    /**
     * @param $dis
     * 删除商城轮播图
     */

    public function bannerDel($dis){
        $res = self::where($dis)->delete();
        return $res;
    }

    /**
     * @param $dis
     * 轮播图详情
     */
    public function bannerInfo($dis){
       $data = self::where($dis)->find();
       return !empty($data)?$data->toArray():$data;
    }


}