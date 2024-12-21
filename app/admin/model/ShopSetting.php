<?php
namespace app\admin\model;
use app\admin\model\ShopOrderGoods;
use app\BaseModel;
use think\facade\Db;



class ShopSetting extends BaseModel
{
    //定义表名
    protected $name = 'longbing_card_config';

    protected static function init()
    {
        //TODO:初始化内容
    }
    /**
     * @param $dis
     * @param int $page
     * @return mixed
     * 获取商城配置
     */
    public function configInfo($dis){
        $data = self::where($dis)->find();
        return !empty($data)?$data->toArray():$data;
    }
    /**
     * @param $dis
     * @param $data
     * @return int
     * 修改商城配置
     */
    public function configUpdate($dis,$data){
        $data['update_time'] = time();
        $res = self::where($dis)->update($data);
        return $res;
    }




}