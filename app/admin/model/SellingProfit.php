<?php
namespace app\admin\model;
use app\admin\model\ShopOrderGoods;
use app\BaseModel;
use think\facade\Db;



class SellingProfit extends BaseModel
{
    //定义表名
    protected $name = 'longbing_card_selling_profit';

    protected static function init()
    {
        //TODO:初始化内容
    }
    /**
     * @param $dis
     * @param int $page
     * @return mixed
     * 获取分销列表
     */
    public function profitList($dis,$page = 10) {
        $data = Db::name($this->name)
            ->alias('a')
            ->leftJoin('longbing_card_user b' ,'a.user_id = b.id')
            ->leftJoin('longbing_card_user_info c' ,'a.user_id = c.fans_id')
            ->where($dis)
            ->field(['a.*' ,'b.nickName','c.name'])
            ->order('a.total_profit desc','a.id decs')
            ->paginate($page)
            ->toArray();
        return $data;
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