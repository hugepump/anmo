<?php
/**
 * Created by PhpStorm.
 * User: shuixian
 * Date: 2019/11/20
 * Time: 18:29
 */



//模块组件 后台DIY页面的左侧
return [
    //链接类型
    [
        'key' => 2,
        "title" => "商城",
        "type" => "shop",
        "data" => [
            [
                //接口请求路径   api_path 不为空, 返回 page + '?' 数据参数
                "api_path" => "/diy/admin/Module/functionPage",
                //已经取消了
                //"params"=>"{"page" : "PAGE", "page_count" : "PAGE_COUNT"}",
                "title" => "功能页面",
                //小程序路径
                "page" => "common_page"
            ],[
                //接口请求路径   api_path 不为空, 返回 page + '?' 数据参数
                "api_path" => "/shop/admin/AdminShopType/cateInfoPage",
                //已经取消了
                //"params"=>"{"page" : "PAGE", "page_count" : "PAGE_COUNT"}",
                "title" => "商品分类页面",
                //小程序路径
                "page" => "/shop/pages/filter"
            ],[
                //接口请求路径   api_path 不为空, 返回 page + '?' 数据参数
                "api_path" => "/shop/admin/AdminShopGoods/goodsInfoPage",
                //已经取消了
                //"params"=>"{"page" : "PAGE", "page_count" : "PAGE_COUNT"}",
                "title" => "商品详情页面",
                //小程序路径
                "page" => "/pages/shop/detail"
            ],
        ]
    ],
];