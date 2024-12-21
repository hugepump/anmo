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

require __DIR__ . '/../vendor/autoload.php';

( new App() )->http->run();


use app\massage\model\CouponRecord;
use app\massage\model\CouponService;

$coupon_model = new CouponRecord();

$goods_model  = new CouponService();


