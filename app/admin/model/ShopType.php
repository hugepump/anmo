<?php
namespace app\admin\model;

use app\BaseModel;
use think\facade\Db;

class ShopType extends BaseModel
{
    //定义表名
    protected $name = 'longbing_card_shop_type';
//    protected $pk   = 'role_id';
    protected $schema = [

    ];
    protected $resultSetType  = 'array';
    //初始化
    function __construct() {
        parent::__construct();
    }

    protected static function init()
    {
        //TODO:初始化内容
    }

    /**
     * 获取商品列表
     */
    public function cateList($dis,$page=10){
        $data = Db::name($this->name)->where($dis)->paginate($page)->toArray();
        return $this->getTree($data['data'],0);
    }
    /**
     * @param $data
     * @param $pId
     * @return array
     * 递归无限极
     */
    public function getTree($data, $pId){
         $tree = array();
        if(!empty($data)){
            foreach($data as $k => $v) {
                if($v['pid'] == $pId) {
                    $v[''] = $this->getTree($data, $v['id']);
                    $tree[]   = $v;
                }
            }
        }
        return $tree;
    }

    /**
     * 修改分类
     */
    public function cateUpdate($dis,$data){
        $data['update_time'] = time();
        $res = Db::name($this->name)->where($dis)->update($data);
        return $res;
    }

    /**
     *
     * 添加分类
     */

    public function cateAdd($data){
        $data['create_time'] = time();
        $res = Db::name($this->name)->insert($data);
        return $res;
    }

    /**
     * 获取分类详情
     */

    public function cateInfo($dis){
        $data = Db::name($this->name)->where($dis)->find();
        return $data;
    }


    /**
     * 查询分类
     */
    public function catSelect($dis=array()){
        $data = Db::name($this->name)->where($dis)->select();
        return $data;
    }

    /**
     * 排序好的分类
     */

    public function catSortSelect($dis=array()){
        $data = Db::name($this->name)->where($dis)->select();
        return $this->getTree($data,0);
    }



}