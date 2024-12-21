<?php
namespace app\admin\model;

use app\BaseModel;
use think\facade\Db;

class Coupon extends BaseModel
{
    //定义表名
    protected $name = 'longbing_card_coupon';


//    protected static function init()
//    {
//        //TODO:初始化内容
//    }
    /**
     * @var array
     * 查询器
     */
    protected $append = [
        'status_text',
        'type_text'
    ];
    /**
     * @param $value
     * @param $data
     * @return mixed
     * 状态
     */
    public function getStatusTextAttr($value,$data){
        $status = [-1=>'已删除',0=>'未发布',1=>'已发布'];
        return $status[$data['status']];
    }

    /**
     * @param $value
     * @param $data
     * @return mixed
     * 类型
     */
    public function getTypeTextAttr($value,$data){
        $type = [1=>'线上',2=>'线下'];
        return $type[$data['type']];
    }

    /**
     * @param $value
     * @param $data
     * @return false|string
     * 到期时间
     */
    public function getEndTimeAttr($value,$data){
        return date('Y-m-d H:i:s',$data['end_time']);
    }
    /**
     * @param $value
     * @param $data
     * @return false|string
     * 创建时间
     */
    public function getCreateTimeAttr($value,$data){
        return date('Y-m-d H:i:s',$data['create_time']);
    }

    /*获取福包列表
     */
    public function couponList($dis,$page=10){

        $data = self::where($dis)->order('top desc')->paginate($page)->toArray();
        return $data;
    }

    /**
     * 修改福包
     */

    public function couponUpdate($dis,$data){
        $data['update_time'] = time();
        $res = Db::name($this->name)->where($dis)->update($data);
        return $res;
    }

    /**
     *
     * 添加福包
     */

    public function couponAdd($data){
        $data['create_time'] = time();
        $res = Db::name($this->name)->insert($data);
        return $res;
    }

    /**
     * 获取福包详情
     */

    public function couponInfo($dis){
        $data = Db::name($this->name)->where($dis)->find();
        return $data;
    }




}