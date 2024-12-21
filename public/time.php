<?php
// +----------------------------------------------------------------------
// | Longbing [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright Chengdu longbing Technology Co., Ltd.
// +----------------------------------------------------------------------
// | Website http://longbing.org/
// +----------------------------------------------------------------------
// | Sales manager: +86-13558882532 / +86-13330887474
// | Technical support: +86-15680635005
// | After-sale service: +86-17361005938
// +----------------------------------------------------------------------
namespace think;
require_once("keygen.php");
require_once("generator_dk.php");
require __DIR__ . '/../vendor/autoload.php';

( new App() )->http->run();


use app\massage\model\CouponRecord;
use app\massage\model\CouponService;

$coupon_model = new CouponRecord();

$goods_model  = new CouponService();

//$data = $coupon_model->alias('a')
//    ->join('massage_service_coupon_goods b','a.id = b.coupon_id')
//    ->where('a.status','in',[3])
//    ->where('b.type','=',1)
//    ->limit(10000)
//    ->group('b.id')
//    ->column('b.id');
//
//$goods_model->where('id','in',$data)->delete();

$count = $goods_model->where(['uniacid'=>666])->count();

var_dump($count);exit;