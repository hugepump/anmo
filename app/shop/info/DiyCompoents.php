<?php
/**
 * Created by PhpStorm.
 * User: shuixian
 * Date: 2019/11/20
 * Time: 18:29
 */


$goods = <<<GOODS
{
    "title":"商品列表",
    "type":"goodsList",
    "icon":"iconGoodsList",
    "isDelete":true,
    "addNumber":1,
    "attr":[
       ],
    "data":{
        "title":"商品列表",
        "isShowPush":false,
        "limit":""
    },
    "dataList":[
    ]
}

GOODS;

$search = <<<SEARCH
{"title":"搜索栏","type":"search","iconPath":"iconsousuo","isDelete":true,"addNumber":1,"attr":[{"title":"是否显示分类","type":"Switch","name":"isShowCateAll"}],"data":{"placeholder":"请输入搜索内容","isShowCateAll":true}}

SEARCH;

//模块组件 后台DIY页面的左侧
return [

    [
        "title" => "业务组件",

        'type' => 'shopCompoent',

        'data' =>[

            json_decode($search, true),

            json_decode($goods, true),

        ]
    ],
];