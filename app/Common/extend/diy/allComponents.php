<?php

$base = [
    "title" => "基本组件",
    "type" => "base",
    "data" => [
        [
            'title' => "轮播图",
            'type' => "banner",
            'iconPath' => 'icontupian1'
        ],
        [
            'title' => "导航",
            'type' => "column",
            'iconPath' => 'icondaohang'
        ],
        [
            'title' => "广告",
            'type' => "imagewindow",
            'iconPath' => 'iconguanggaogongguan'
        ],
    ]
];

$decoration = [
    "title" => "装修组件",
    'type' => 'decoration',
    "data" => [
        [
            'title' => "工地列表",
            'type' => "site",
            'iconPath' => 'icongongdiweixuanzhong'
        ],
        [
            'title' => "案例列表",
            'type' => "case",
            'iconPath' => 'iconanli'
        ],
        [
            'title' => "攻略列表",
            'type' => "strategy",
            'iconPath' => 'icongonglve'
        ],
    ]
];


return [
   20 => [$base, $decoration],
//   21 => [$base, $decoration],
];