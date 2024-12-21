<?php
/**
 * Created by PhpStorm.
 * User: shuixian
 * Date: 2019/11/20
 * Time: 18:29
 */


$copyRight    = '{"name": "CopyrightConfig","url": "/sys/copyright"},' ;

$uplode_wecht = !longbingIsWeiqin()|| config('app.AdminModelList')['app_model_name'] =='longbing_card'?',{"name": "SProWechat","url": "/sys/wechat"}':'';

$menu = <<<SYS
{
    "path": "/sys",
    "component": "Layout",
    "redirect": "/sys/config",
    "meta": {
        "menuName": "System",
        "icon": "icon-xitong",
        "subNavName": [
            {
                "name": "SProSetting",
                "url": [
                    {
                        "name": "SProConfig",
                        "url": "/sys/config"
                    },
                    {
                        "name": "SProLink",
                        "url": "/sys/link"
                    },
                    {
                            "name": "SProPayment",
                            "url": "/sys/payment"
                    }
               
                    $uplode_wecht
                    
                ]
            },
            {
                "name": "OtherSetting",
                "url": [
                    $copyRight
                    {
                    "name": "AllNotice",
                    "url": "/sys/clientNotice"
                    },{
                        "name": "ShareSet",
                        "url": "/sys/share"
                    }
                ]
            }
          
        ]
},
"children": [
    {
        "path": "config",
        "name": "SProConfig",
        "component": "/system/smallProcedure/config",
        "meta": {
            "keepAlive": true,
            "title": "SystemSetting",
            "auth": [
            ],
            "isOnly": false,
            "pagePermission": [
                {
                    "title": "SProConfig",
                    "index": 0,
                    "auth": [
                        "view",
                        "add",
                        "edit",
                        "del",
                        "outport"
                    ]
                }
            ]
        }
    },
    {
        "path": "payment",
        "name": "SProPayment",
        "component": "/system/smallProcedure/payment",
        "meta": {
            "title": "SystemSetting",
            "isOnly": false,
            "auth": [
            ],
            "pagePermission": [
                {
                    "title": "SProPayment",
                    "index": 0,
                    "auth": [
                        "view",
                        "add",
                        "edit",
                        "del",
                        "outport"]
                }]
        }
    },
    {
        "path": "wechat",
        "name": "SProWechat",
        "component": "/system/smallProcedure/wechat",
        "meta": {
            "title": "SystemSetting",
            "isOnly": false,
            "auth": [
            ],
            "pagePermission": [
                {
                    "title": "SProWechat",
                    "index": 0,
                    "auth": [
                        "view",
                        "add",
                        "edit",
                        "del",
                        "outport"] 
                }]
        }
    },
    {
        "path": "link",
        "name": "SProLink",
        "component": "/system/smallProcedure/link",
        "meta": {
            "keepAlive": true,
            "title": "SystemSetting",
            "auth": [
            ],
            "isOnly": false,
            "pagePermission": [
                {
                    "title": "SProLink",
                    "index": 0,
                    "auth": [
                        "view",
                        "add",
                        "edit",
                        "del",
                        "outport"
                    ]
                }
            ]
        }
    },
    {
        "path": "copyright",
        "name": "CopyrightConfig",
        "component": "/system/other/copyright",
        "meta": {
            "keepAlive": true,
            "title": "SystemSetting",
            "auth": [
            ],
            "isOnly": false,
            "pagePermission": [
                {
                    "title": "CopyrightConfig",
                    "index": 0,
                    "auth": [
                        "view",
                        "add",
                        "edit",
                        "del",
                        "outport"
                    ]
                }
            ]
        }
    },
    {
        "path": "clientNotice",
        "name": "AllNotice",
        "component": "/system/other/clientNotice",
        "meta": {
            "keepAlive": true,
            "title": "SystemSetting",
            "auth": [
            ],
            "isOnly": false,
            "pagePermission": [
                {
                    "title": "clientNotice",
                    "url": "/sys/clientNotice",
                    "index": 0,
                    "auth": [
                        "view",
                        "add",
                        "edit",
                        "del",
                        "outport"
                    ]
                },
                {
                    "title": "radarNotice",
                    "url": "/sys/radarNotice",
                    "index": 1,
                    "auth": [
                        "view",
                        "add",
                        "edit",
                        "del",
                        "outport"
                    ]
                }
            ]
        }
    },
    {
        "path": "radarNotice",
        "name": "AllNotice",
        "component": "/system/other/radarNotice",
        "meta": {
            "keepAlive": true,
            "title": "SystemSetting",
            "auth": [
            ],
            "isOnly": false,
            "pagePermission": [
                {
                    "title": "clientNotice",
                    "url": "/sys/clientNotice",
                    "index": 0,
                    "auth": [
                        "view",
                        "add",
                        "edit",
                        "del",
                        "outport"
                    ]
                },
                {
                    "title": "radarNotice",
                    "url": "/sys/radarNotice",
                    "index": 1,
                    "auth": [
                        "view",
                        "add",
                        "edit",
                        "del",
                        "outport"
                    ]
                }
            ]
        }
    },
    {
        "path": "share",
        "name": "ShareSet",
        "component": "/system/other/share",
        "meta": {
            "keepAlive": true,
            "title": "SystemSetting",
            "auth": [],
            "isOnly": false,
            "pagePermission": [{
                "title": "ShareSet",
                "index": 0,
                "auth": ["view", "add", "edit", "del", "outport"
                
                ]
            }
            
            ]
        }
    }
]
}

SYS;

//return json_decode(['System' => $menu], true);
return ['admin' => $menu];


