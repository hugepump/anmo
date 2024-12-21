<?php
/**
 * Created by PhpStorm.
 * User: shuixian
 * Date: 2019/11/20
 * Time: 18:29
 */



//模块组件 后台DIY页面的左侧
$tmp = [
    //链接类型

    [
        'id'=>'',

        'level'=>1,

        'key'=> 2,
        //"params"=>"{"page" : "PAGE", "page_count" : "PAGE_COUNT"}",
        "title" => "商城页面",
        //小程序路径
        "path" => "/pages/user/home?key=2&staff_id="
    ],[
        'id'=>'',

        'level'=>2,

        'key'=> 2,
        //"params"=>"{"page" : "PAGE", "page_count" : "PAGE_COUNT"}",
        "title" => "商城购物车",
        //小程序路径
        "path" => "/shop/pages/cart"
    ],[
        'id'=>'',

        'level'=>2,

        'key'=> 2,
        //"params"=>"{"page" : "PAGE", "page_count" : "PAGE_COUNT"}",
        "title" => "商城卡券列表",
        //小程序路径
        "path" => "/shop/pages/coupon/receive?staff_id="
    ],

    [
        'id'=>'',

        'level'=>2,

        'key'=> 2,
        //"params"=>"{"page" : "PAGE", "page_count" : "PAGE_COUNT"}",
        "title" => "商城全部分类",
        //小程序路径
        "path" => "/shop/pages/cate"
    ],
    [
        'id'=>'',

        'level'=>2,

        'key'=> 2,
        //"params"=>"{"page" : "PAGE", "page_count" : "PAGE_COUNT"}",
        "title" => "商城采购模版",
        //小程序路径
        "path" => "/shop/pages/purchase/list?staff_id="
    ],
    [
        'id'=>'',

        'level'=>2,

        'key'=> 2,
        //"params"=>"{"page" : "PAGE", "page_count" : "PAGE_COUNT"}",
        "title" => "商城砍价列表页",
        //小程序路径
        "path" => "/shop/pages/bargain/list?staff_id="
    ],



];


return $tmp;