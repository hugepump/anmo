<?php

$survey = <<<SURVEY
{
        "path": "/survey",
        "component": "Layout",
        "redirect": "/survey/index",
        "meta": {
            "menuName": "Survey",
            "icon": "icon-gaikuang"
        },
        "children": [
            {
                "path": "index",
                "name": "Survey",
                "component": "/survey/index",
                "meta": {
                    "keepAlive": false,
                    "refresh": false,
                    "title": "",
                    "isOnly": true,
                    "auth": [
                        "view",
                        "add",
                        "edit",
                        "del",
                        "outport"
                    ],
                    "pagePermission": []
                }
            }
        ]
    }
SURVEY;

$businessCard = <<<BUSINESSCARD
{
        "path": "/businessCard",
        "component": "Layout",
        "redirect": "/businessCard/staffCard",
        "meta": {
            "menuName": "BusinessCard",
            "icon": "icon-mingpian",
            "subNavName": [
                {
                    "name": "CardInfo",
                    "url": [
                        {
                            "name": "StaffCard",
                            "url": "/businessCard/staffCard"
                        },
                        {
                            "name": "ImpressionLabel",
                            "url": "/businessCard/tag"
                        }
                    ]
                },
                {
                    "name": "CardSetting",
                    "url": [
                        {
                            "name": "ExemptionPassword",
                            "url": "/businessCard/exemptionPwd"
                        },
                        {
                            "name": "MobileSetting",
                            "url": "/businessCard/mobileSetting"
                        },
                        {
                            "name": "MediaSetting",
                            "url": "/businessCard/mediaSetting"
                        },
                        {
                            "name": "CardSetting",
                            "url": "/businessCard/cardSetting"
                        }
                    ]
                }
            ]
        },
        "children": [
            {
                "path": "staffCard",
                "name": "StaffCard",
                "component": "/businessCard/manage/staffCard",
                "meta": {
                    "keepAlive": true,
                    "title": "CardManage",
                    "isOnly": false,
                    "auth": [],
                    "pagePermission": [
                        {
                            "title": "StaffCard",
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
                "path": "editCard",
                "name": "EditCard",
                "component": "/businessCard/manage/editCard",
                "meta": {
                    "keepAlive": false,
                    "refresh": false,
                    "title": "CardManage",
                    "isOnly": false,
                    "auth": [],
                    "pagePermission": [
                        {
                            "title": "EditCard",
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
                "path": "tag",
                "name": "ImpressionLabel",
                "component": "/businessCard/manage/tag",
                "meta": {
                    "keepAlive": true,
                    "title": "CardManage",
                    "isOnly": false,
                    "auth": [],
                    "pagePermission": [
                        {
                            "title": "ImpressionLabel",
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
                "path": "exemptionPwd",
                "name": "ExemptionPassword",
                "component": "/businessCard/set/exemptionPwd",
                "meta": {
                    "keepAlive": true,
                    "refresh": false,
                    "title": "CardManage",
                    "isOnly": false,
                    "auth": [],
                    "pagePermission": [
                        {
                            "title": "ExemptionPassword",
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
                "path": "mobileSetting",
                "name": "MobileSetting",
                "component": "/businessCard/set/mobileSetting",
                "meta": {
                    "keepAlive": true,
                    "refresh": false,
                    "title": "CardManage",
                    "isOnly": false,
                    "auth": [],
                    "pagePermission": [
                        {
                            "title": "MobileSetting",
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
                "path": "mediaSetting",
                "name": "MediaSetting",
                "component": "/businessCard/set/mediaSetting",
                "meta": {
                    "keepAlive": true,
                    "refresh": false,
                    "title": "CardManage",
                    "isOnly": false,
                    "auth": [],
                    "pagePermission": [
                        {
                            "title": "MediaSetting",
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
                "path": "cardSetting",
                "name": "CardSetting",
                "component": "/businessCard/set/cardSetting",
                "meta": {
                    "keepAlive": true,
                    "refresh": false,
                    "title": "CardManage",
                    "isOnly": false,
                    "auth": [],
                    "pagePermission": [
                        {
                            "title": "CardSetting",
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
            }
        ]
    }
BUSINESSCARD;




$malls = <<<MALLS
{
        "path": "/malls",
        "component": "Layout",
        "redirect": "/malls/list",
        "meta": {
            "menuName": "Malls",
            "icon": "icon-gouwudai",
            "subNavName": [
                {
                    "name": "MallsManage",
                    "url": [
                        {
                            "name": "GoodsList",
                            "url": "/malls/list"
                        },
                        {
                            "name": "GoodsClassify",
                            "url": "/malls/classify"
                        }
                    ]
                },
                 {
                    "name": "StoreManage",
                    "url": [{
                    "name": "StoreList",
                    "url": "/malls/storeManage"
                    }
                    ]
                },
                {
                    "name": "OrderManage",
                    "url": [
                        {
                            "name": "OrderManage",
                            "url": "/malls/orderManage"
                        },
                        {
                            "name": "RefundManage",
                            "url": "/malls/refundManage"
                        }
                    ]
                },
                {
                    "name": "MarketingManage",
                    "url": [
                        {
                            "name": "AssembleList",
                            "url": "/malls/assemble"
                        },
                        {
                            "name": "RedPackit",
                            "url": "/malls/redPackit"
                        }
                    ]
                },
                {
                    "name": "MallsSet",
                    "url": [
                        {
                            "name": "DealSet",
                            "url": "/malls/dealSet"
                        },
                        {
                            "name": "VirtualPaymentSet",
                            "url": "/malls/virtualPayment"
                        },
                        {
                            "name": "StaffChoiceGoods",
                            "url": "/malls/staffGoods"
                        },
                        {
                            "name": "PaymentSetting",
                            "url": "/malls/paymentSetting"
                        },
                        {
                            "name": "MallsBanner",
                            "url": "/malls/banner"
                        }
                    ]
                },
                {
                    "name": "Distributioninfo",
                    "url": [
                        {
                            "name": "ProfitInfo",
                            "url": "/malls/profit"
                        },
                        {
                            "name": "CommissionInfo",
                            "url": "/malls/commission"
                        },
                        {
                            "name": "TakeCashInfo",
                            "url": "/malls/cash"
                        },
                        {
                            "name": "DistributionRelation",
                            "url": "/malls/relation"
                        },
                        {
                            "name": "DistributionSetting",
                            "url": "/malls/disSet"
                        }
                    ]
                }
            ]
        },
        "children": [
            {
                "path": "list",
                "name": "GoodsList",
                "component": "/malls/goods/list",
                "meta": {
                    "keepAlive": true,
                    "refresh": false,
                    "title": "MallsSet",
                    "isOnly": false,
                    "auth": [],
                    "pagePermission": [
                        {
                            "title": "GoodsList",
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
                "path": "classify",
                "name": "GoodsClassify",
                "component": "/malls/goods/classify",
                "meta": {
                    "keepAlive": true,
                    "refresh": false,
                    "title": "MallsSet",
                    "isOnly": false,
                    "auth": [],
                    "pagePermission": [
                        {
                            "title": "GoodsClassify",
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
                "path": "addClassify",
                "name": "SpecsClassify",
                "component": "/malls/goods/addClassify",
                "meta": {
                    "title": "MallsSet",
                    "isOnly": false,
                    "auth": [],
                    "pagePermission": [
                        {
                            "title": "AddClassify",
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
                "path": "edit",
                "name": "GoodsEdit",
                "component": "/malls/goods/edit",
                "meta": {
                    "title": "MallsSet",
                    "isOnly": false,
                    "auth": [],
                    "pagePermission": [
                        {
                            "title": "GoodsEdit",
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
            }
            ,
            {
                "path": "storeManage",
                "name": "StoreList",
                "component": "/malls/store/list",
                "meta": {
                "keepAlive": true,
                "title": "MallsSet",
                "isOnly": false,
                "auth": [],
                "pagePermission": [{
                "title": "StoreList",
                "index": 0,
                "auth": ["view", "add", "edit", "del", "outport"] 
                }]
                }
                },
             
                {
                "path": "store",
                "name": "StoreAdd",
                "component": "/malls/store/edit",
                "meta": {
                "title": "MallsSet",
                "isOnly": false,
                "auth": [],
                "pagePermission": [{
                "title": "StoreAdd",
                "index": 0,
                "auth": ["view", "add", "edit", "del", "outport"] 
                }]
                }
                },
            {
                "path": "orderManage",
                "name": "OrderManage",
                "component": "/malls/order/manage",
                "meta": {
                    "keepAlive": true,
                    "title": "MallsSet",
                    "isOnly": false,
                    "auth": [],
                    "pagePermission": [
                        {
                            "title": "OrderManage",
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
                "path": "orderDetail",
                "name": "OrderDetail",
                "component": "/malls/order/detail",
                "meta": {
                    "title": "MallsSet",
                    "isOnly": false,
                    "auth": [],
                    "pagePermission": [
                        {
                            "title": "OrderDetail",
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
                "path": "refundManage",
                "name": "RefundManage",
                "component": "/malls/order/refund",
                "meta": {
                    "keepAlive": true,
                    "title": "MallsSet",
                    "isOnly": false,
                    "auth": [],
                    "pagePermission": [
                        {
                            "title": "RefundManage",
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
                "path": "newAssemble",
                "name": "NewAssemble",
                "component": "/malls/marketing/newAssemble",
                "meta": {
                    "keepAlive": true,
                    "title": "MallsSet",
                    "isOnly": false,
                    "auth": [],
                    "pagePermission": [
                        {
                            "title": "NewAssemble",
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
                "path": "assemble",
                "name": "AssembleList",
                "component": "/malls/marketing/assemble",
                "meta": {
                    "title": "MallsSet",
                    "isOnly": false,
                    "auth": [],
                    "pagePermission": [
                        {
                            "title": "AssembleList",
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
                "path": "assembleManage",
                "name": "AssembleManage",
                "component": "/malls/marketing/assembleManage",
                "meta": {
                    "title": "MallsSet",
                    "isOnly": false,
                    "auth": [],
                    "pagePermission": [
                        {
                            "title": "AssembleManage",
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
                "path": "redPackit",
                "name": "RedPackit",
                "component": "/malls/marketing/redPackit",
                "meta": {
                    "keepAlive": true,
                    "title": "MallsSet",
                    "isOnly": false,
                    "auth": [],
                    "pagePermission": [
                        {
                            "title": "RedPackit",
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
                "path": "addRedPackit",
                "name": "EditRedPackit",
                "component": "/malls/marketing/addRedPackit",
                "meta": {
                    "title": "MallsSet",
                    "isOnly": false,
                    "auth": [],
                    "pagePermission": [
                        {
                            "title": "EditRedPackit",
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
                "path": "dealSet",
                "name": "DealSet",
                "component": "/malls/set/deal",
                "meta": {
                    "title": "MallsSet",
                    "isOnly": false,
                    "auth": [],
                    "pagePermission": [
                        {
                            "title": "DealSet",
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
                "path": "virtualPayment",
                "name": "VirtualPaymentSet",
                "component": "/malls/set/payment",
                "meta": {
                    "title": "MallsSet",
                    "isOnly": false,
                    "auth": [],
                    "pagePermission": [
                        {
                            "title": "VirtualPaymentSet",
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
                "path": "banner",
                "name": "MallsBanner",
                "component": "/malls/set/banner",
                "meta": {
                    "keepAlive": true,
                    "title": "MallsSet",
                    "isOnly": false,
                    "auth": [],
                    "pagePermission": [
                        {
                            "title": "MallsBanner",
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
                "path": "editBanner",
                "name": "EditBanner",
                "component": "/malls/set/editBanner",
                "meta": {
                    "title": "MallsSet",
                    "isOnly": false,
                    "auth": [],
                    "pagePermission": [
                        {
                            "title": "EditBanner",
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
                "path": "staffGoods",
                "name": "StaffChoiceGoods",
                "component": "/malls/set/staffGoods",
                "meta": {
                    "title": "MallsSet",
                    "isOnly": false,
                    "auth": [],
                    "pagePermission": [
                        {
                            "title": "StaffChoiceGoods",
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
                "path": "profit",
                "component": "/malls/distribution/profit",
                "name": "Distributioninfo",
                "meta": {
                    "title": "MallsSet",
                    "auth": [],
                    "isOnly": false,
                    "keepAlive": true,
                    "pagePermission": [
                        {
                            "title": "Distributioninfo",
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
                "path": "commission",
                "component": "/malls/distribution/commission",
                "name": "CommissionInfo",
                "meta": {
                    "title": "MallsSet",
                    "auth": [],
                    "isOnly": false,
                    "keepAlive": true,
                    "pagePermission": [
                        {
                            "title": "CommissionInfo",
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
                "path": "cash",
                "component": "/malls/distribution/takeCash",
                "name": "TakeCashInfo",
                "meta": {
                    "title": "MallsSet",
                    "auth": [],
                    "isOnly": false,
                    "keepAlive": true,
                    "pagePermission": [
                        {
                            "title": "TakeCashInfo",
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
                "path": "relation",
                "component": "/malls/distribution/relation",
                "name": "DistributionRelation",
                "meta": {
                    "title": "MallsSet",
                    "auth": [],
                    "isOnly": false,
                    "keepAlive": true,
                    "pagePermission": [
                        {
                            "title": "DistributionRelation",
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
                "path": "disSet",
                "component": "/malls/distribution/set",
                "name": "DistributionSetting",
                "meta": {
                    "title": "MallsSet",
                    "auth": [],
                    "isOnly": false,
                    "pagePermission": [
                        {
                            "title": "DistributionSetting",
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
            }
        ]
    }
MALLS;
//动态
$dynamic = <<<DYNAMIC
{
        "path": "/dynamic",
        "component": "Layout",
        "redirect": "/dynamic/manage",
        "meta": {
            "menuName": "Dynamic",
            "icon": "icon-dongtai1",
            "subNavName": [
                {
                    "name": "DynamicManage",
                    "url": [
                        {
                            "name": "DynamicManage",
                            "url": "/dynamic/manage"
                        },
                        {
                            "name": "CommentManage",
                            "url": "/dynamic/comment"
                        }
                    ]
                }
            ]
        },
        "children": [
            {
                "path": "manage",
                "name": "DynamicManage",
                "component": "/dynamic/manage",
                "meta": {
                    "title": "DynamicSet",
                    "isOnly": false,
                    "auth": [],
                    "keepAlive": true,
                    "pagePermission": [
                        {
                            "title": "DynamicManage",
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
                "path": "comment",
                "name": "CommentManage",
                "component": "/dynamic/comment",
                "meta": {
                    "title": "DynamicSet",
                    "isOnly": false,
                    "auth": [],
                    "keepAlive": true,
                    "pagePermission": [
                        {
                            "title": "CommentManage",
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
                "path": "editOrdinary",
                "name": "EditOrdinary",
                "component": "/dynamic/addOrdinary",
                "meta": {
                    "title": "DynamicSet",
                    "isOnly": false,
                    "auth": [],
                    "pagePermission": [
                        {
                            "title": "EditOrdinary",
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
                "path": "editLink",
                "name": "EditLink",
                "component": "/dynamic/addLink",
                "meta": {
                    "title": "DynamicSet",
                    "isOnly": false,
                    "auth": [],
                    "pagePermission": [
                        {
                            "title": "EditLink",
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
                "path": "editVideo",
                "name": "EditVideo",
                "component": "/dynamic/addVideo",
                "meta": {
                    "title": "DynamicSet",
                    "isOnly": false,
                    "auth": [],
                    "pagePermission": [
                        {
                            "title": "EditVideo",
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
            }
        ]
    }
DYNAMIC;

//官网
$website = <<<WEBSITE
{
        "path": "/website",
        "component": "Layout",
        "redirect": "/website/column",
        "meta": {
            "menuName": "Website",
            "icon": "icon-guanwang",
            "subNavName": [
                {
                    "name": "WebSiteInfo",
                    "url": [
                        {
                            "name": "WebsiteColumn",
                            "url": "/website/column"
                        }
                    ]
                }
            ]
        },
        "children": [
            {
                "path": "column",
                "name": "WebsiteColumn",
                "component": "/website/info/column",
                "meta": {
                    "title": "WebsiteManage",
                    "isOnly": false,
                    "keepAlive": true,
                    "auth": [],
                    "pagePermission": [
                        {
                            "title": "WebsiteColumn",
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
                "path": "editModule",
                "name": "EditModule",
                "component": "/website/info/editModule",
                "meta": {
                    "title": "WebsiteManage",
                    "isOnly": false,
                    "auth": [],
                    "pagePermission": [
                        {
                            "title": "EditModule",
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
                "path": "newsList",
                "name": "newsList",
                "component": "/website/info/module/news",
                "meta": {
                    "title": "WebsiteManage",
                    "isOnly": false,
                    "keepAlive": true,
                    "auth": [],
                    "pagePermission": [
                        {
                            "title": "newsList",
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
                "path": "editNews",
                "name": "NewsEdit",
                "component": "/website/info/module/newsEdit",
                "meta": {
                    "title": "WebsiteManage",
                    "isOnly": false,
                    "auth": [],
                    "pagePermission": [
                        {
                            "title": "NewsEdit",
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
                "path": "imgText",
                "name": "imgText",
                "component": "/website/info/module/imgText",
                "meta": {
                    "title": "WebsiteManage",
                    "isOnly": false,
                    "auth": [],
                    "pagePermission": [
                        {
                            "title": "imgText",
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
                "path": "recruit",
                "name": "recruit",
                "component": "/website/info/module/recruit",
                "meta": {
                    "title": "WebsiteManage",
                    "isOnly": false,
                    "auth": [],
                    "keepAlive": true,
                    "pagePermission": [
                        {
                            "title": "recruit",
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
                "path": "editRecruit",
                "name": "RecruitEdit",
                "component": "/website/info/module/editRecruit",
                "meta": {
                    "title": "WebsiteManage",
                    "isOnly": false,
                    "auth": [],
                    "pagePermission": [
                        {
                            "title": "RecruitEdit",
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
                "path": "contact",
                "name": "contact",
                "component": "/website/info/module/contact",
                "meta": {
                    "title": "WebsiteManage",
                    "isOnly": false,
                    "auth": [],
                    "pagePermission": [
                        {
                            "title": "contact",
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
                "path": "staff",
                "name": "staff",
                "component": "/website/info/module/staff",
                "meta": {
                    "title": "WebsiteManage",
                    "isOnly": false,
                    "auth": [],
                    "keepAlive": true,
                    "pagePermission": [
                        {
                            "title": "staff",
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
                "path": "editStaff",
                "name": "EditStaff",
                "component": "/website/info/module/addStaff",
                "meta": {
                    "title": "WebsiteManage",
                    "isOnly": false,
                    "auth": [],
                    "pagePermission": [
                        {
                            "title": "EditStaff",
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
                "path": "video",
                "name": "video",
                "component": "/website/info/module/video",
                "meta": {
                    "title": "WebsiteManage",
                    "isOnly": false,
                    "auth": [],
                    "keepAlive": true,
                    "pagePermission": [
                        {
                            "title": "video",
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
                "path": "editVideo",
                "name": "VideoEdit",
                "component": "/website/info/module/videoEdit",
                "meta": {
                    "title": "WebsiteManage",
                    "isOnly": false,
                    "auth": [],
                    "pagePermission": [
                        {
                            "title": "VideoEdit",
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
                "path": "form",
                "name": "form",
                "component": "/website/info/module/form",
                "meta": {
                    "title": "WebsiteManage",
                    "isOnly": false,
                    "auth": [],
                    "keepAlive": true,
                    "pagePermission": [
                        {
                            "title": "form",
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
            }
        ]
    }
WEBSITE;

//客户
$customer = <<<CUSTOMER
{
        "path": "/customer",
        "component": "Layout",
        "redirect": "/customer/list",
        "meta": {
            "menuName": "Customer",
            "icon": "icon-kehu",
            "subNavName": [
                {
                    "name": "CustomerInfo",
                    "url": [
                        {
                            "name": "CustomerList",
                            "url": "/customer/list"
                        },
                        {
                            "name": "StaffHandover",
                            "url": "/customer/handover"
                        }
                    ]
                },
                {
                    "name": "CustomerTalkingSkill",
                    "url": [
                        {
                            "name": "TalkingSkillList",
                            "url": "/customer/talkingSkill"
                        },
                        {
                            "name": "TalkingSkillClassify",
                            "url": "/customer/classify"
                        }
                    ]
                },
                {
                    "name": "CustomerQuestionnaire",
                    "url": [
                        {
                            "name": "QuestionnaireList",
                            "url": "/customer/qList"
                        },
                        {
                            "name": "QuestionnaireSetting",
                            "url": "/customer/qSetting"
                        }
                    ]
                }
            ]
        },
        "children": [
            {
                "path": "list",
                "component": "/customer/info/list",
                "name": "CustomerList",
                "meta": {
                    "title": "CustomerManagement",
                    "auth": [],
                    "isOnly": false,
                    "keepAlive": true,
                    "pagePermission": [
                        {
                            "title": "CustomerList",
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
                "path": "handover",
                "component": "/customer/info/handover",
                "name": "StaffHandover",
                "meta": {
                    "title": "CustomerManagement",
                    "auth": [],
                    "isOnly": false,
                    "keepAlive": true,
                    "pagePermission": [
                        {
                            "title": "StaffHandover",
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
                "path": "talkingSkill",
                "component": "/customer/talkingSkill/list",
                "name": "TalkingSkillList",
                "meta": {
                    "title": "CustomerManagement",
                    "auth": [],
                    "isOnly": false,
                    "keepAlive": true,
                    "pagePermission": [
                        {
                            "title": "TalkingSkillList",
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
                "path": "classify",
                "component": "/customer/talkingSkill/classify",
                "name": "TalkingSkillClassify",
                "meta": {
                    "title": "CustomerManagement",
                    "auth": [],
                    "isOnly": false,
                    "keepAlive": true,
                    "pagePermission": [
                        {
                            "title": "TalkingSkillClassify",
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
                "path": "qList",
                "component": "/customer/questionnaire/list",
                "name": "QuestionnaireList",
                "meta": {
                    "title": "CustomerManagement",
                    "auth": [],
                    "isOnly": false,
                    "keepAlive": true,
                    "pagePermission": [
                        {
                            "title": "QuestionnaireList",
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
                "path": "qEdit",
                "component": "/customer/questionnaire/edit",
                "name": "QuestionnaireEdit",
                "meta": {
                    "title": "CustomerManagement",
                    "auth": [],
                    "isOnly": false,
                    "pagePermission": [
                        {
                            "title": "QuestionnaireEdit",
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
                "path": "qSetting",
                "component": "/customer/questionnaire/setting",
                "name": "QuestionnaireSetting",
                "meta": {
                    "title": "CustomerManagement",
                    "auth": [],
                    "isOnly": false,
                    "keepAlive": true,
                    "pagePermission": [
                        {
                            "title": "QuestionnaireSetting",
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
                "path": "lookInto",
                "component": "/customer/info/lookInto",
                "name": "CustomerLookInto",
                "meta": {
                    "title": "CustomerManagement",
                    "auth": [],
                    "isOnly": false,
                    "keepAlive": false,
                    "pagePermission": [
                        {
                            "title": "CustomerLookInto",
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
            }
        ]
    }
CUSTOMER;

//公司
$company = <<<COMPANY
{
        "path": "/company",
        "component": "Layout",
        "redirect": "/company/list",
        "meta": {
            "menuName": "Company",
            "icon": "icon-gongsi",
            "subNavName": [
                {
                    "name": "CompanyInfo",
                    "url": [
                        {
                            "name": "CompanyList",
                            "url": "/company/list"
                        }
                    ]
                },
                {
                    "name": "PostInfo",
                    "url": [
                        {
                            "name": "PostManage",
                            "url": "/company/postManage"
                        }
                    ]
                }
            ]
        },
        "children": [
            {
                "path": "addCompany",
                "name": "EditCompany",
                "component": "/company/manage/addCompany",
                "meta": {
                    "keepAlive": true,
                    "title": "CompanyManage",
                    "isOnly": false,
                    "auth": [],
                    "pagePermission": [
                        {
                            "title": "EditCompany",
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
                "path": "list",
                "name": "CompanyList",
                "component": "/company/manage/list",
                "meta": {
                    "keepAlive": true,
                    "title": "CompanyManage",
                    "isOnly": false,
                    "auth": [],
                    "pagePermission": [
                        {
                            "title": "CompanyList",
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
                "path": "postManage",
                "name": "PostManage",
                "component": "/company/manage/postManage",
                "meta": {
                    "keepAlive": true,
                    "title": "CompanyManage",
                    "isOnly": false,
                    "auth": [],
                    "pagePermission": [
                        {
                            "title": "PostManage",
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
            }
        ]
    }
COMPANY;

//系统设置
$sys = <<<SYS
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
                    ]
                },
                {
                    "name": "OtherSetting",
                    "url": [
                        {
                            "name": "CopyrightConfig",
                            "url": "/sys/copyright"
                        },
                        {
                            "name": "AllNotice",
                            "url": "/sys/notice"
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
                    "auth": [],
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
                "auth": [],
                "pagePermission": [{
                "title": "SProPayment",
                "index": 0,
                "auth": ["view", "add", "edit", "del", "outport"]
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
                    "auth": [],
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
                    "auth": [],
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
                "path": "upload",
                "name": "UploadConfig",
                "component": "/system/other/uploadConfig",
                "meta": {
                    "keepAlive": true,
                    "title": "SystemSetting",
                    "auth": [],
                    "isOnly": false,
                    "pagePermission": [
                        {
                            "title": "UploadConfig",
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
                "path": "notice",
                "name": "AllNotice",
                "component": "/system/other/notice",
                "meta": {
                    "keepAlive": true,
                    "title": "SystemSetting",
                    "auth": [],
                    "isOnly": false,
                    "pagePermission": [
                        {
                            "title": "AllNotice",
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
            }
          
        ]
    }
SYS;

//$sys = <<<SYS
//{
//        "path": "/sys",
//        "component": "Layout",
//        "redirect": "/sys/config",
//        "meta": {
//            "menuName": "System",
//            "icon": "icon-xitong",
//            "subNavName": [
//                {
//                    "name": "SProSetting",
//                    "url": [
//                        {
//                            "name": "SProConfig",
//                            "url": "/sys/config"
//                        },
//                        {
//                            "name": "SProLink",
//                            "url": "/sys/link"
//                        },
//                        {
//                            "name": "TabbarSetting",
//                            "url": "/sys/tabbar"
//                        }
//                    ]
//                },
//                {
//                    "name": "OtherSetting",
//                    "url": [
//                        {
//                            "name": "CopyrightConfig",
//                            "url": "/sys/copyright"
//                        },
//                        {
//                            "name": "AllNotice",
//                            "url": "/sys/notice"
//                        },
//                        {
//                            "name": "UploadConfig",
//                            "url": "/sys/upload"
//                        }
//                    ]
//                }
//            ]
//        },
//        "children": [
//            {
//                "path": "config",
//                "name": "SProConfig",
//                "component": "/system/smallProcedure/config",
//                "meta": {
//                    "keepAlive": true,
//                    "title": "SystemSetting",
//                    "auth": [],
//                    "isOnly": false,
//                    "pagePermission": [
//                        {
//                            "title": "SProConfig",
//                            "index": 0,
//                            "auth": [
//                                "view",
//                                "add",
//                                "edit",
//                                "del",
//                                "outport"
//                            ]
//                        }
//                    ]
//                }
//            },
//            {
//                "path": "link",
//                "name": "SProLink",
//                "component": "/system/smallProcedure/link",
//                "meta": {
//                    "keepAlive": true,
//                    "title": "SystemSetting",
//                    "auth": [],
//                    "isOnly": false,
//                    "pagePermission": [
//                        {
//                            "title": "SProLink",
//                            "index": 0,
//                            "auth": [
//                                "view",
//                                "add",
//                                "edit",
//                                "del",
//                                "outport"
//                            ]
//                        }
//                    ]
//                }
//            },
//            {
//                "path": "tabbar",
//                "name": "TabbarSetting",
//                "component": "/system/smallProcedure/tabbar",
//                "meta": {
//                    "keepAlive": true,
//                    "title": "SystemSetting",
//                    "auth": [],
//                    "isOnly": false,
//                    "pagePermission": [
//                        {
//                            "title": "TabbarSetting",
//                            "index": 0,
//                            "auth": [
//                                "view",
//                                "add",
//                                "edit",
//                                "del",
//                                "outport"
//                            ]
//                        }
//                    ]
//                }
//            },
//            {
//                "path": "copyright",
//                "name": "CopyrightConfig",
//                "component": "/system/other/copyright",
//                "meta": {
//                    "keepAlive": true,
//                    "title": "SystemSetting",
//                    "auth": [],
//                    "isOnly": false,
//                    "pagePermission": [
//                        {
//                            "title": "CopyrightConfig",
//                            "index": 0,
//                            "auth": [
//                                "view",
//                                "add",
//                                "edit",
//                                "del",
//                                "outport"
//                            ]
//                        }
//                    ]
//                }
//            },
//            {
//                "path": "upload",
//                "name": "UploadConfig",
//                "component": "/system/other/uploadConfig",
//                "meta": {
//                    "keepAlive": true,
//                    "title": "SystemSetting",
//                    "auth": [],
//                    "isOnly": false,
//                    "pagePermission": [
//                        {
//                            "title": "UploadConfig",
//                            "index": 0,
//                            "auth": [
//                                "view",
//                                "add",
//                                "edit",
//                                "del",
//                                "outport"
//                            ]
//                        }
//                    ]
//                }
//            },
//            {
//                "path": "notice",
//                "name": "AllNotice",
//                "component": "/system/other/notice",
//                "meta": {
//                    "keepAlive": true,
//                    "title": "SystemSetting",
//                    "auth": [],
//                    "isOnly": false,
//                    "pagePermission": [
//                        {
//                            "title": "AllNotice",
//                            "index": 0,
//                            "auth": [
//                                "view",
//                                "add",
//                                "edit",
//                                "del",
//                                "outport"
//                            ]
//                        }
//                    ]
//                }
//            }
//
//        ]
//    }
//SYS;

$app = <<<APP
{
        "path": "/app",
        "component": "Layout",
        "redirect": "/app/tool",
        "meta": {
            "menuName": "App",
            "img": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADAAAAAwCAYAAABXAvmHAAAFrElEQVRoQ+1ZzU4jRxD+aswpiRTvLQpkYe8JthU4Bx5gg3mC2OQBMiiQa9hrIKJ5gIB5gtjwADHn3QiT5L42YGlvsaW9JMJTUQ2e2emenh/zI7SbbckXT091ffX7dQ3hLV/0luuPXADYdYsgKtnA0u7uyW2MwKfTZTB9HJNBVz2qvOpmyU4FwK5bBfAdgKUUQQMAbQDrpFTmgSKHT4tFjD7cBUjkFxNlM3dAULTQP0zaYwXgWxw4ACAHTLK2SKlnaS/w79M1MO2mKm4KECBTo1WbR2IAxsr/BqA8ieaRvQ1Sqm57d6y8GOYma4DCVcUEYQOgxmFzk0OCd1ZJqWZUAJ9+MofR1MvbCAVzhxb7lagMDQC77hwA85AhABdAk5SSeA/XeL88kzyJri4p9UTb+3ymDcJXxr4WCrxFlX4nFjUvPq0CpACa1Z4R1+nLfiP4zwRgs36FlIodYAAREBLX0RV64TppP/pbf8x7tNCX9xLXONk7GgjGCS1ehkXFBCDVJGqlPVIq9ZDgdHZdARkttc9IqS15zr41nV81TQuvH1FloHnUhsT2Li1chnqbADjJilmxy65req9FSvlVjJ/PbIHw45vY062Y7gVL7hSungTJnAVgmZQSr2Qudl2x9hslgRNSynf1bQBce3BGNyxjmRYvfb3eA4jkwHsPWJPYVsFScuA2VehePDBpFTIriZQ56QOZJO2+kphfTEsfiJRnPqOFfkhzzCSWqiE8KLpE+XpWNbprAGPqIbxJZ8JpndgvWa4rHGbFEo/ijbSOLDREfsFKLqNAhiyeAygqayyTe7TQ1/5PYqOSC9YLTGZDyAdgAjHh1iEKvGTyprT7wG1BJHfiydW3Kh9rZKZcdl3hQfLTGWG2AsJgq0HejMmc5FL86pguawiGwtRrlcSb8t6JJevllpZ2tQxUEUXj1Nuv5x8sgZ3sixJ5HbAzCOhCGsZcALIN/nA73n0AtZ+47BSwwpwrfAJXNA42SJskfPbH06rjOd/kvcwzcZuJOxfzx60bhZAoTg52KV/cx89grO9vypUQmPmzulTw2GyQ+eKO0fUcb/1i/li7YwcvW0No7WeuMfvKJ89sMo5nxsnBJvlJ//js6y0CRe8K+ZSP7GLw1nnpKDayiQHwQ8bB6cQnGC/cNQAR75FXv5g/Di/01j6wtsNSBs2632MpjexTAOtiYIkiU4dUAIweE2uKGEKLJDlnjjMZg3+YKq8qzZBcah6Q0AH7E7lwMXDIBbiNdUq9gK9ts3bvTfcAn/RKR5k95fHZU5fgaNMOBu+dl47CQYMGoL7NTSKNyJ3tb1B24wFwHwDG+dMgkFSv68Xo9sqtcOake8AMn0glycqJ+wJgq2C9Uss+VlnbYe327zGWG5uUaypxXwDEcLNnK5peI4eWL79oxqcS7wFEhld3kcRB2P6PPLDNA1CEsxPq+99TWr0Oc7u+ww0Cwmoh5fdgg2rWTszo9MotbUyeVCSm/6qWp0asNVby6FG30vTLullG29FmJLx+f4NWsypQbZeLNMJLjXpkcKGrAlX6nzdTp94J4Ie9ciukOHoZ3WYXpI/JPcBtbNBeEghR3hnFP0d5HiqNHyhUcLazonuX0SGm5cCSNvm2Esrgw/PSke/ZmAd8Za7Q1cLoel/TA9rk6FMJaffMqJE+jUA0gYODrISOMWBi5RFrpdphmgOTUJNQ0UCO6bkYmbPRiawQ0p4zhh5jKWp9eT53Wi0yee2kz7V5zjBpRMwDgRCzKeURPm7zQ7HaLxtk5e5+Ql6xfGqa9HIPM3SsIRRV9NsdrjKjMcFhZ56Hmml5E7zvCcdrAmR+L7PbiTGUMDsvHflfe8yVeif2c8JDlT1UifypmzbsklgHoesAzSSrJ3lPEtTxvCrJ51ymsmYoRg/EXQY3//UKzSh9nghA7tB5wI3v/lTiAY2b6+j/AMlKXV5pYFX0AAAAAElFTkSuQmCC",
            "subNavName": [
                {
                    "name": "AppCenter",
                    "url": [
                        {
                            "name": "AppTool",
                            "url": "/app/tool"
                        }
                    ]
                }
            ]
        },
        "children": [
            {
                "path": "tool",
                "name": "AppTool",
                "component": "/application/tool",
                "meta": {
                    "keepAlive": true,
                    "title": "AppManage",
                    "auth": [],
                    "isOnly": false,
                    "pagePermission": [
                        {
                            "title": "AppTool",
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
                "path": "sweepPayment/code",
                "name": "SweepPaymentCode",
                "component": "/application/sweepPayment/code",
                "meta": {
                    "metaName" : "payqr",
                    "keepAlive": true,
                    "title": "AppManage",
                    "auth": [],
                    "isOnly": false,
                    "pagePermission": [
                        {
                            "title": "PaymentCode",
                            "url": "/app/sweepPayment/code",
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
                            "title": "PaymentRecord",
                            "url": "/app/sweepPayment/record",
                            "index": 1,
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "Swiper",
                            "url": "/app/sweepPayment/swiper",
                            "index": 2,
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "BasicSetting",
                            "url": "/app/sweepPayment/setting",
                            "index": 3,
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
                "path": "sweepPayment/record",
                "name": "SweepPaymentRecord",
                "component": "/application/sweepPayment/record",
                "meta": {
                    "metaName" : "payqr",
                    "keepAlive": true,
                    "title": "AppManage",
                    "auth": [],
                    "isOnly": false,
                    "pagePermission": [
                        {
                            "title": "PaymentCode",
                            "url": "/app/sweepPayment/code",
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
                            "title": "PaymentRecord",
                            "url": "/app/sweepPayment/record",
                            "index": 1,
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "Swiper",
                            "url": "/app/sweepPayment/swiper",
                            "index": 2,
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "BasicSetting",
                            "url": "/app/sweepPayment/setting",
                            "index": 3,
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
                "path": "sweepPayment/swiper",
                "name": "SweepPaymentSwiper",
                "component": "/application/sweepPayment/swiper",
                "meta": {
                    "metaName" : "payqr",
                    "keepAlive": true,
                    "title": "AppManage",
                    "auth": [],
                    "isOnly": false,
                    "pagePermission": [
                        {
                            "title": "PaymentCode",
                            "url": "/app/sweepPayment/code",
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
                            "title": "PaymentRecord",
                            "url": "/app/sweepPayment/record",
                            "index": 1,
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "Swiper",
                            "url": "/app/sweepPayment/swiper",
                            "index": 2,
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "BasicSetting",
                            "url": "/app/sweepPayment/setting",
                            "index": 3,
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
                "path": "sweepPayment/setting",
                "name": "SweepPaymentSetting",
                "component": "/application/sweepPayment/setting",
                "meta": {
                    "metaName" : "payqr",
                    "keepAlive": true,
                    "title": "AppManage",
                    "auth": [],
                    "isOnly": false,
                    "pagePermission": [
                        {
                            "title": "PaymentCode",
                            "url": "/app/sweepPayment/code",
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
                            "title": "PaymentRecord",
                            "url": "/app/sweepPayment/record",
                            "index": 1,
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "Swiper",
                            "url": "/app/sweepPayment/swiper",
                            "index": 2,
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "BasicSetting",
                            "url": "/app/sweepPayment/setting",
                            "index": 3,
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
                "path": "sweepPayment/addSwiper",
                "name": "SweepPaymentAddSwiper",
                "component": "/application/sweepPayment/addSwiper",
                "meta": {
                    "metaName" : "payqr",
                    "keepAlive": false,
                    "title": "AppManage",
                    "auth": [],
                    "isOnly": false,
                    "pagePermission": [
                        {
                            "title": "EditSwiper",
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
                "path": "poster/classify",
                "name": "GuestWinningPosterClassify",
                "component": "/application/poster/classify",
                "meta": {
                    "metaName" : "poster",
                    "keepAlive": true,
                    "title": "AppManage",
                    "auth": [],
                    "isOnly": false,
                    "pagePermission": [
                        {
                            "title": "PosterClassify",
                            "url": "/app/poster/classify",
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
                            "title": "PosterList",
                            "url": "/app/poster/list",
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
                "path": "poster/list",
                "name": "GuestWinningPosterList",
                "component": "/application/poster/list",
                "meta": {
                    "metaName" : "poster",
                    "keepAlive": true,
                    "title": "AppManage",
                    "auth": [],
                    "isOnly": false,
                    "pagePermission": [
                        {
                            "title": "PosterClassify",
                            "url": "/app/poster/classify",
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
                            "title": "PosterList",
                            "url": "/app/poster/list",
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
                "path": "poster/edit",
                "name": "GuestWinningPosterEdit",
                "component": "/application/poster/editPoster",
                "meta": {
                    "metaName" : "poster",
             
                    "title": "AppManage",
                    "auth": [],
                    "isOnly": false,
                    "pagePermission": [
                        {
                            "title": "EditPoster",
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
                "path": "group/msg",
                "name": "GMsg",
                "component": "/application/groupPush/msg",
                "meta": {
                    "metaName" : "send",
                    "keepAlive": true,
                    "title": "AppManage",
                    "isOnly": false,
                    "auth": [],
                    "pagePermission": [
                        {
                            "title": "GMsg",
                            "index": 0,
                            "url": "/app/group/msg",
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "GNews",
                            "url": "/app/group/news",
                            "index": 1,
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "ContentSetting",
                            "url": "/app/group/setting",
                            "index": 2,
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
                "path": "group/news",
                "name": "GNews",
                "component": "/application/groupPush/news",
                "meta": {
                    "metaName" : "send",
                    "keepAlive": true,
                    "title": "AppManage",
                    "isOnly": false,
                    "auth": [],
                    "pagePermission": [
                        {
                            "title": "GMsg",
                            "index": 0,
                            "url": "/app/group/msg",
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "GNews",
                            "url": "/app/group/news",
                            "index": 1,
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "ContentSetting",
                            "url": "/app/group/setting",
                            "index": 2,
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
                "path": "group/setting",
                "name": "ContentSetting",
                "component": "/application/groupPush/setting",
                "meta": {
                    "metaName" : "send",
                    "keepAlive": true,
                    "title": "AppManage",
                    "isOnly": false,
                    "auth": [],
                    "pagePermission": [
                        {
                            "title": "GMsg",
                            "index": 0,
                            "url": "/app/group/msg",
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "GNews",
                            "url": "/app/group/news",
                            "index": 1,
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "ContentSetting",
                            "url": "/app/group/setting",
                            "index": 2,
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
                "path": "appointment/classify",
                "name": "ServiceClassify",
                "component": "/application/appointment/classifyList",
                "meta": {
                    "metaName" : "appiont",
                    "keepAlive": true,
                    "title": "AppManage",
                    "isOnly": false,
                    "auth": [],
                    "pagePermission": [
                        {
                            "title": "ServiceClassify",
                            "index": 0,
                            "url": "/app/appointment/classify",
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "AppointmentService",
                            "url": "/app/appointment/service",
                            "index": 1,
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "GuestOrder",
                            "url": "/app/appointment/order",
                            "index": 2,
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "AppointmentSetting",
                            "url": "/app/appointment/setting",
                            "index": 3,
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

                "path": "appointment/editService",
                "name": "AddService",
                "component": "/application/appointment/editService",
                "meta": {
                    "metaName" : "appiont",
                    "keepAlive": false,
                    "title": "AppManage",
                    "isOnly": false,
                    "auth": [],
                    "pagePermission": [
                        {
                            "title": "ServiceClassify",
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
                "path": "appointment/order",
                "name": "GuestOrder",
                "component": "/application/appointment/order",
                "meta": {
                    "metaName" : "appiont",
                    "keepAlive": true,
                    "title": "AppManage",
                    "isOnly": false,
                    "auth": [],
                    "pagePermission": [
                        {
                            "title": "ServiceClassify",
                            "index": 0,
                            "url": "/app/appointment/classify",
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "AppointmentService",
                            "url": "/app/appointment/service",
                            "index": 1,
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "GuestOrder",
                            "url": "/app/appointment/order",
                            "index": 2,
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "AppointmentSetting",
                            "url": "/app/appointment/setting",
                            "index": 3,
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
                "metaName" : "appiont",
                "path": "appointment/service",
                "name": "AppointmentService",
                "component": "/application/appointment/serviceList",
                "meta": {
                    "metaName" : "appiont",
                    "keepAlive": true,
                    "title": "AppManage",
                    "isOnly": false,
                    "auth": [],
                    "pagePermission": [
                        {
                            "title": "ServiceClassify",
                            "index": 0,
                            "url": "/app/appointment/classify",
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "AppointmentService",
                            "url": "/app/appointment/service",
                            "index": 1,
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "GuestOrder",
                            "url": "/app/appointment/order",
                            "index": 2,
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "AppointmentSetting",
                            "url": "/app/appointment/setting",
                            "index": 3,
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
                "path": "appointment/setting",
                "name": "AppointmentSetting",
                "component": "/application/appointment/setting",
                "meta": {
                    "metaName" : "appiont",
                    "keepAlive": true,
                    "title": "AppManage",
                    "isOnly": false,
                    "auth": [],
                    "pagePermission": [
                        {
                            "title": "ServiceClassify",
                            "index": 0,
                            "url": "/app/appointment/classify",
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "AppointmentService",
                            "url": "/app/appointment/service",
                            "index": 1,
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "GuestOrder",
                            "url": "/app/appointment/order",
                            "index": 2,
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "AppointmentSetting",
                            "url": "/app/appointment/setting",
                            "index": 3,
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
                "path": "house/classify",
                "name": "HouseClassify",
                "component": "/application/houseProperty/classify",
                "meta": {
                    "metaName" : "house",
                    "keepAlive": true,
                    "title": "AppManage",
                    "isOnly": false,
                    "auth": [],
                    "pagePermission": [
                        {
                            "title": "HouseClassify",
                            "index": 0,
                            "url": "/app/house/classify",
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "HouseBanner",
                            "url": "/app/house/banner",
                            "index": 1,
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "HouseManage",
                            "url": "/app/house/manage",
                            "index": 2,
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "HouseScreenManage",
                            "url": "/app/house/screen",
                            "index": 3,
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "HouseAppointmentCustomer",
                            "url": "/app/house/customer",
                            "index": 4,
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
                "path": "house/banner",
                "name": "HouseBanner",
                "component": "/application/houseProperty/banner",
                "meta": {
                    "metaName" : "house",
                    "keepAlive": true,
                    "title": "AppManage",
                    "isOnly": false,
                    "auth": [],
                    "pagePermission": [
                        {
                            "title": "HouseClassify",
                            "index": 0,
                            "url": "/app/house/classify",
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "HouseBanner",
                            "url": "/app/house/banner",
                            "index": 1,
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "HouseManage",
                            "url": "/app/house/manage",
                            "index": 2,
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "HouseScreenManage",
                            "url": "/app/house/screen",
                            "index": 3,
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "HouseAppointmentCustomer",
                            "url": "/app/house/customer",
                            "index": 4,
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
                "path": "house/manage",
                "name": "HouseManage",
                "component": "/application/houseProperty/manage",
                "meta": {
                    "metaName" : "house",
                    "keepAlive": true,
                    "title": "AppManage",
                    "isOnly": false,
                    "auth": [],
                    "pagePermission": [
                        {
                            "title": "HouseClassify",
                            "index": 0,
                            "url": "/app/house/classify",
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "HouseBanner",
                            "url": "/app/house/banner",
                            "index": 1,
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "HouseManage",
                            "url": "/app/house/manage",
                            "index": 2,
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "HouseScreenManage",
                            "url": "/app/house/screen",
                            "index": 3,
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "HouseAppointmentCustomer",
                            "url": "/app/house/customer",
                            "index": 4,
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
                "path": "house/screen",
                "name": "HouseScreenManage",
                "component": "/application/houseProperty/screen",
                "meta": {
                    "metaName" : "house",
                    "keepAlive": true,
                    "title": "AppManage",
                    "isOnly": false,
                    "auth": [],
                    "pagePermission": [
                        {
                            "title": "HouseClassify",
                            "index": 0,
                            "url": "/app/house/classify",
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "HouseBanner",
                            "url": "/app/house/banner",
                            "index": 1,
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "HouseManage",
                            "url": "/app/house/manage",
                            "index": 2,
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "HouseScreenManage",
                            "url": "/app/house/screen",
                            "index": 3,
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "HouseAppointmentCustomer",
                            "url": "/app/house/customer",
                            "index": 4,
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
                "path": "house/customer",
                "name": "HouseAppointmentCustomer",
                "component": "/application/houseProperty/customer",
                "meta": {
                    "metaName" : "house",
                    "keepAlive": true,
                    "title": "AppManage",
                    "isOnly": false,
                    "auth": [],
                    "pagePermission": [
                        {
                            "title": "HouseClassify",
                            "index": 0,
                            "url": "/app/house/classify",
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "HouseBanner",
                            "url": "/app/house/banner",
                            "index": 1,
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "HouseManage",
                            "url": "/app/house/manage",
                            "index": 2,
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "HouseScreenManage",
                            "url": "/app/house/screen",
                            "index": 3,
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "HouseAppointmentCustomer",
                            "url": "/app/house/customer",
                            "index": 4,
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
                "path": "house/editClassify",
                "name": "EditHouseClassify",
                "component": "/application/houseProperty/editClassify",
                "meta": {
                    "metaName" : "house",
                   
                    "title": "AppManage",
                    "isOnly": false,
                    "auth": [],
                    "pagePermission": [
                        {
                            "title": "EditHouseClassify",
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
                "path": "house/editBanner",
                "name": "EditHouseBanner",
                "component": "/application/houseProperty/editBanner",
                "meta": {
                    "metaName" : "house",
                   
                    "title": "AppManage",
                    "isOnly": false,
                    "auth": [],
                    "pagePermission": [
                        {
                            "title": "EditHouseBanner",
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
                "path": "house/editHouse",
                "name": "EditHouse",
                "component": "/application/houseProperty/editHouse",
                "meta": {
                    "metaName" : "house",
                   
                    "title": "AppManage",
                    "isOnly": false,
                    "auth": [],
                    "pagePermission": [
                        {
                            "title": "EditHouse",
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
                "path": "activity/classify",
                "name": "ActivityClassify",
                "component": "/application/activity/classify",
                "meta": {
                    "metaName" : "activity",
                    "keepAlive": true,
                    "title": "AppManage",
                    "isOnly": false,
                    "auth": [],
                    "pagePermission": [
                        {
                            "title": "ActivityClassify",
                            "index": 0,
                            "url": "/app/activity/classify",
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "SignUpContent",
                            "url": "/app/activity/content",
                            "index": 1,
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "ActivityManage",
                            "url": "/app/activity/manage",
                            "index": 2,
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
                "path": "activity/content",
                "name": "SignUpContent",
                "component": "/application/activity/content",
                "meta": {
                    "metaName" : "activity",
                    "keepAlive": true,
                    "title": "AppManage",
                    "isOnly": false,
                    "auth": [],
                    "pagePermission": [
                        {
                            "title": "ActivityClassify",
                            "index": 0,
                            "url": "/app/activity/classify",
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "SignUpContent",
                            "url": "/app/activity/content",
                            "index": 1,
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "ActivityManage",
                            "url": "/app/activity/manage",
                            "index": 2,
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
                "path": "activity/manage",
                "name": "ActivityManage",
                "component": "/application/activity/list",
                "meta": {
                    "metaName" : "activity",
                    "keepAlive": true,
                    "title": "AppManage",
                    "isOnly": false,
                    "auth": [],
                    "pagePermission": [
                        {
                            "title": "ActivityClassify",
                            "index": 0,
                            "url": "/app/activity/classify",
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "SignUpContent",
                            "url": "/app/activity/content",
                            "index": 1,
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "ActivityManage",
                            "url": "/app/activity/manage",
                            "index": 2,
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
                "path": "activity/edit",
                "name": "EditActivity",
                "component": "/application/activity/edit",
                "meta": {
                    "metaName" : "activity",
                    "keepAlive": false,
                    "title": "AppManage",
                    "isOnly": false,
                    "auth": [],
                    "pagePermission": [
                        {
                            "title": "EditActivity",
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
                "path": "activity/signUp",
                "name": "SignUpDetail",
                "component": "/application/activity/signUp",
                "meta": {
                    "metaName" : "activity",
                    "keepAlive": false,
                    "title": "AppManage",
                    "isOnly": false,
                    "auth": [],
                    "pagePermission": [
                        {
                            "title": "SignUpDetail",
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
                "path": "article/classify",
                "name": "ArticleClassify",
                "component": "/application/article/classify",
                "meta": {
                    "metaName" : "acticle",
                    "keepAlive": true,
                    "title": "AppManage",
                    "isOnly": false,
                    "auth": [],
                    "pagePermission": [
                        {
                            "title": "ArticleClassify",
                            "index": 0,
                            "url": "/app/article/classify",
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "ArticleList",
                            "url": "/app/article/list",
                            "index": 1,
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "StaffArticle",
                            "url": "/app/article/staff",
                            "index": 2,
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "ArticleSetting",
                            "url": "/app/article/setting",
                            "index": 3,
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "AssignedArticle",
                            "url": "/app/article/assigne",
                            "index": 4,
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
                "path": "article/list",
                "name": "ArticleList",
                "component": "/application/article/articleList",
                "meta": {
                    "metaName" : "acticle",
                    "keepAlive": true,
                    "title": "AppManage",
                    "isOnly": false,
                    "auth": [],
                    "pagePermission": [
                        {
                            "title": "ArticleClassify",
                            "index": 0,
                            "url": "/app/article/classify",
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "ArticleList",
                            "url": "/app/article/list",
                            "index": 1,
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "StaffArticle",
                            "url": "/app/article/staff",
                            "index": 2,
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "ArticleSetting",
                            "url": "/app/article/setting",
                            "index": 3,
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "AssignedArticle",
                            "url": "/app/article/assigne",
                            "index": 4,
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
                "path": "article/staff",
                "name": "StaffArticle",
                "component": "/application/article/staffArticleList",
                "meta": {
                    "metaName" : "acticle",
                    "keepAlive": true,
                    "title": "AppManage",
                    "isOnly": false,
                    "auth": [],
                    "pagePermission": [
                        {
                            "title": "ArticleClassify",
                            "index": 0,
                            "url": "/app/article/classify",
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "ArticleList",
                            "url": "/app/article/list",
                            "index": 1,
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "StaffArticle",
                            "url": "/app/article/staff",
                            "index": 2,
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "ArticleSetting",
                            "url": "/app/article/setting",
                            "index": 3,
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "AssignedArticle",
                            "url": "/app/article/assigne",
                            "index": 4,
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
                "path": "article/setting",
                "name": "ArticleSetting",
                "component": "/application/article/setting",
                "meta": {
                    "metaName" : "acticle",
                    "keepAlive": true,
                    "title": "AppManage",
                    "isOnly": false,
                    "auth": [],
                    "pagePermission": [
                        {
                            "title": "ArticleClassify",
                            "index": 0,
                            "url": "/app/article/classify",
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "ArticleList",
                            "url": "/app/article/list",
                            "index": 1,
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "StaffArticle",
                            "url": "/app/article/staff",
                            "index": 2,
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "ArticleSetting",
                            "url": "/app/article/setting",
                            "index": 3,
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "AssignedArticle",
                            "url": "/app/article/assigne",
                            "index": 4,
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
                "path": "article/assigne",
                "name": "AssignedArticle",
                "component": "/application/article/assignedArticle",
                "meta": {
                    "metaName" : "acticle",
                    "keepAlive": true,
                    "title": "AppManage",
                    "isOnly": false,
                    "auth": [],
                    "pagePermission": [
                        {
                            "title": "ArticleClassify",
                            "index": 0,
                            "url": "/app/article/classify",
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "ArticleList",
                            "url": "/app/article/list",
                            "index": 1,
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "StaffArticle",
                            "url": "/app/article/staff",
                            "index": 2,
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "ArticleSetting",
                            "url": "/app/article/setting",
                            "index": 3,
                            "auth": [
                                "view",
                                "add",
                                "edit",
                                "del",
                                "outport"
                            ]
                        },
                        {
                            "title": "AssignedArticle",
                            "url": "/app/article/assigne",
                            "index": 4,
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
                "path": "article/edit",
                "name": "EditArticle",
                "component": "/application/article/editArticle",
                "meta": {
                    "metaName" : "acticle",
                    "keepAlive": false,
                    "title": "AppManage",
                    "isOnly": false,
                    "auth": [],
                    "pagePermission": [
                        {
                            "title": "EditArticle",
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
            }
        ]
    }
APP;

$renovation = <<<DECORATE
{
    "path":"/renovation",
    "component":"Layout",
    "redirect":"/renovation/case",
    "meta":{
        "menuName":"Renovation",
        "icon":"icon-zhuangxiu",
        "subNavName":[
            {
                "name":"RenovationCase",
                "url":[
                    {
                        "name":"CaseList",
                        "url":"/renovation/case"
                    }
                ]
            },
            {
                "name":"BuildingSite",
                "url":[
                    {
                        "name":"SiteList",
                        "url":"/renovation/site"
                    },
                    {
                        "name":"SiteDynamic",
                        "url":"/renovation/siteDynamic"
                    }
                ]
            },
            {
                "name":"RenovationStrategy",
                "url":[
                    {
                        "name":"StrategyList",
                        "url":"/renovation/strategy"
                    },
                    {
                        "name":"StrategyClassify",
                        "url":"/renovation/strategyClassify"
                    }
                ]
            },
            {
                "name":"MasterTeam",
                "url":[
                    {
                        "name":"TeamList",
                        "url":"/renovation/master"
                    }
                ]
            },
            {
                "name":"RenovationMarketingManage",
                "url":[
                    {
                        "name":"IntelligenceOffer",
                        "url":"/renovation/offer"
                    },
                    {
                        "name":"FreeDesign",
                        "url":"/renovation/design"
                    }
                ]
            },
            {
                "name":"RenovationSetting",
                "url":[
                    {
                        "name":"RenovationScreenMange",
                        "url":"/renovation/screen/regionSetting"
                    }
                ]
            },
            {
                "name":"RenovationAbout",
                "url":[
                    {
                        "name":"CompanyProfile",
                        "url":"/renovation/about/index"
                    }
                ]
            }
        ]
    },
    "children":[
        {
            "path":"case",
            "name":"CaseList",
            "component":"/renovation/case/list",
            "meta":{
                "keepAlive":true,
                "title":"RenovationManage",
                "auth":[

                ],
                "isOnly":false,
                "pagePermission":[
                    {
                        "title":"CaseList",
                        "index":0,
                        "auth":[
                            "view",
                            "add",
                            "edit",
                            "del",
                            "outport"
                        ]
                    }
                ]
            }
        }
                
        ,{
            "path":"editCase",
            "name":"EditCase",
            "component":"/renovation/case/edit",
            "meta":{
                "keepAlive":false,
                "title":"RenovationManage",
                "auth":[

                ],
                "isOnly":false,
                "pagePermission":[
                    {
                        "title":"EditCase",
                        "index":0,
                        "auth":[
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
            "path":"site",
            "name":"SiteList",
            "component":"/renovation/site/list",
            "meta":{
                "keepAlive":true,
                "title":"RenovationManage",
                "auth":[

                ],
                "isOnly":false,
                "pagePermission":[
                    {
                        "title":"SiteList",
                        "index":0,
                        "auth":[
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
            "path":"editsite",
            "name":"EditSite",
            "component":"/renovation/site/edit",
            "meta":{
               
                "title":"RenovationManage",
                "auth":[

                ],
                "isOnly":false,
                "pagePermission":[
                    {
                        "title":"EditSite",
                        "index":0,
                        "auth":[
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
            "path":"siteDynamic",
            "name":"SiteDynamic",
            "component":"/renovation/site/dynamic",
            "meta":{
                "keepAlive":true,
                "title":"RenovationManage",
                "auth":[

                ],
                "isOnly":false,
                "pagePermission":[
                    {
                        "title":"SiteDynamic",
                        "index":0,
                        "auth":[
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
            "path":"dyDetail",
            "name":"SiteDynamicDetail",
            "component":"/renovation/site/detail",
            "meta":{
               
                "title":"RenovationManage",
                "auth":[

                ],
                "isOnly":false,
                "pagePermission":[
                    {
                        "title":"SiteDynamicDetail",
                        "index":0,
                        "auth":[
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
            "path":"strategy",
            "name":"StrategyList",
            "component":"/renovation/strategy/list",
            "meta":{
                "keepAlive":true,
                "title":"RenovationManage",
                "auth":[

                ],
                "isOnly":false,
                "pagePermission":[
                    {
                        "title":"StrategyList",
                        "index":0,
                        "auth":[
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
            "path":"editStrategy",
            "name":"EditStrategy",
            "component":"/renovation/strategy/edit",
            "meta":{
                
                "title":"RenovationManage",
                "auth":[

                ],
                "isOnly":false,
                "pagePermission":[
                    {
                        "title":"EditStrategy",
                        "index":0,
                        "auth":[
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
            "path":"strategyClassify",
            "name":"StrategyClassify",
            "component":"/renovation/strategy/classify",
            "meta":{
                "keepAlive":true,
                "title":"RenovationManage",
                "auth":[

                ],
                "isOnly":false,
                "pagePermission":[
                    {
                        "title":"StrategyClassify",
                        "index":0,
                        "auth":[
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
            "path":"master",
            "name":"TeamList",
            "component":"/renovation/master/list",
            "meta":{
                "keepAlive":true,
                "title":"RenovationManage",
                "auth":[

                ],
                "isOnly":false,
                "pagePermission":[
                    {
                        "title":"TeamList",
                        "index":0,
                        "auth":[
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
            "path":"offer",
            "name":"IntelligenceOffer",
            "component":"/renovation/offer/list",
            "meta":{
                "keepAlive":true,
                "title":"RenovationManage",
                "auth":[

                ],
                "isOnly":false,
                "pagePermission":[
                    {
                        "title":"IntelligenceOffer",
                        "index":0,
                        "url":"/renovation/offer",
                        "auth":[
                            "view",
                            "add",
                            "edit",
                            "del",
                            "outport"
                        ]
                    },
                    {
                        "title":"OfferContentSetting",
                        "index":1,
                        "url":"/renovation/offerSetting",
                        "auth":[
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
            "path":"offerSetting",
            "name":"OfferContentSetting",
            "component":"/renovation/offer/setting",
            "meta":{
                "keepAlive":false,
                "title":"RenovationManage",
                "auth":[

                ],
                "isOnly":false,
                "pagePermission":[
                    {
                        "title":"IntelligenceOffer",
                        "index":0,
                        "url":"/renovation/offer",
                        "auth":[
                            "view",
                            "add",
                            "edit",
                            "del",
                            "outport"
                        ]
                    },
                    {
                        "title":"OfferContentSetting",
                        "index":1,
                        "url":"/renovation/offerSetting",
                        "auth":[
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
            "path":"design",
            "name":"FreeDesign",
            "component":"/renovation/design/list",
            "meta":{
                "keepAlive":true,
                "title":"RenovationManage",
                "auth":[

                ],
                "isOnly":false,
                "pagePermission":[
                    {
                        "title":"FreeDesign",
                        "index":0,
                        "url":"/renovation/design",
                        "auth":[
                            "view",
                            "add",
                            "edit",
                            "del",
                            "outport"
                        ]
                    },
                    {
                        "title":"DesignContentSetting",
                        "index":1,
                        "url":"/renovation/designSetting",
                        "auth":[
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
            "path":"designSetting",
            "name":"DesignContentSetting",
            "component":"/renovation/design/setting",
            "meta":{
                "keepAlive":false,
                "title":"RenovationManage",
                "auth":[

                ],
                "isOnly":false,
                "pagePermission":[
                    {
                        "title":"FreeDesign",
                        "index":0,
                        "url":"design",
                        "auth":[
                            "view",
                            "add",
                            "edit",
                            "del",
                            "outport"
                        ]
                    },
                    {
                        "title":"DesignContentSetting",
                        "index":1,
                        "url":"/renovation/designSetting",
                        "auth":[
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
            "path":"screen/regionSetting",
            "name":"RenovationScreenRegionSet",
            "component":"/renovation/screen/region",
            "meta":{
                "keepAlive":true,
                "title":"RenovationManage",
                "auth":[

                ],
                "isOnly":false,
                "pagePermission":[
                    {
                        "title":"RegionSetting",
                        "index":0,
                        "url":"/renovation/screen/regionSetting",
                        "auth":[
                            "view",
                            "add",
                            "edit",
                            "del",
                            "outport"
                        ]
                    },
                    {
                        "title":"AreaSetting",
                        "index":1,
                        "url":"/renovation/screen/areaSetting",
                        "auth":[
                            "view",
                            "add",
                            "edit",
                            "del",
                            "outport"
                        ]
                    },
                    {
                        "title":"TotalPriceSetting",
                        "index":2,
                        "url":"/renovation/screen/priceSetting",
                        "auth":[
                            "view",
                            "add",
                            "edit",
                            "del",
                            "outport"
                        ]
                    },
                    {
                        "title":"StyleSetting",
                        "index":3,
                        "url":"/renovation/screen/styleSetting",
                        "auth":[
                            "view",
                            "add",
                            "edit",
                            "del",
                            "outport"
                        ]
                    },
                    {
                        "title":"ApartmentSetting",
                        "index":4,
                        "url":"/renovation/screen/apartmentSetting",
                        "auth":[
                            "view",
                            "add",
                            "edit",
                            "del",
                            "outport"
                        ]
                    },
                    {
                        "title":"StageSetting",
                        "index":5,
                        "url":"/renovation/screen/stageSetting",
                        "auth":[
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
            "path":"screen/areaSetting",
            "name":"RenovationScreenAreaSet",
            "component":"/renovation/screen/area",
            "meta":{
                "keepAlive":true,
                "title":"RenovationManage",
                "auth":[

                ],
                "isOnly":false,
                "pagePermission":[
                    {
                        "title":"RegionSetting",
                        "index":0,
                        "url":"/renovation/screen/regionSetting",
                        "auth":[
                            "view",
                            "add",
                            "edit",
                            "del",
                            "outport"
                        ]
                    },
                    {
                        "title":"AreaSetting",
                        "index":1,
                        "url":"/renovation/screen/areaSetting",
                        "auth":[
                            "view",
                            "add",
                            "edit",
                            "del",
                            "outport"
                        ]
                    },
                    {
                        "title":"TotalPriceSetting",
                        "index":2,
                        "url":"/renovation/screen/priceSetting",
                        "auth":[
                            "view",
                            "add",
                            "edit",
                            "del",
                            "outport"
                        ]
                    },
                    {
                        "title":"StyleSetting",
                        "index":3,
                        "url":"/renovation/screen/styleSetting",
                        "auth":[
                            "view",
                            "add",
                            "edit",
                            "del",
                            "outport"
                        ]
                    },
                    {
                        "title":"ApartmentSetting",
                        "index":4,
                        "url":"/renovation/screen/apartmentSetting",
                        "auth":[
                            "view",
                            "add",
                            "edit",
                            "del",
                            "outport"
                        ]
                    },
                    {
                        "title":"StageSetting",
                        "index":5,
                        "url":"/renovation/screen/stageSetting",
                        "auth":[
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
            "path":"screen/priceSetting",
            "name":"RenovationScreenTotalPriceSet",
            "component":"/renovation/screen/price",
            "meta":{
                "keepAlive":true,
                "title":"RenovationManage",
                "auth":[

                ],
                "isOnly":false,
                "pagePermission":[
                    {
                        "title":"RegionSetting",
                        "index":0,
                        "url":"/renovation/screen/regionSetting",
                        "auth":[
                            "view",
                            "add",
                            "edit",
                            "del",
                            "outport"
                        ]
                    },
                    {
                        "title":"AreaSetting",
                        "index":1,
                        "url":"/renovation/screen/areaSetting",
                        "auth":[
                            "view",
                            "add",
                            "edit",
                            "del",
                            "outport"
                        ]
                    },
                    {
                        "title":"TotalPriceSetting",
                        "index":2,
                        "url":"/renovation/screen/priceSetting",
                        "auth":[
                            "view",
                            "add",
                            "edit",
                            "del",
                            "outport"
                        ]
                    },
                    {
                        "title":"StyleSetting",
                        "index":3,
                        "url":"/renovation/screen/styleSetting",
                        "auth":[
                            "view",
                            "add",
                            "edit",
                            "del",
                            "outport"
                        ]
                    },
                    {
                        "title":"ApartmentSetting",
                        "index":4,
                        "url":"/renovation/screen/apartmentSetting",
                        "auth":[
                            "view",
                            "add",
                            "edit",
                            "del",
                            "outport"
                        ]
                    },
                    {
                        "title":"StageSetting",
                        "index":5,
                        "url":"/renovation/screen/stageSetting",
                        "auth":[
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
            "path":"screen/styleSetting",
            "name":"RenovationScreenStyleSet",
            "component":"/renovation/screen/style",
            "meta":{
                "keepAlive":true,
                "title":"RenovationManage",
                "auth":[

                ],
                "isOnly":false,
                "pagePermission":[
                    {
                        "title":"RegionSetting",
                        "index":0,
                        "url":"/renovation/screen/regionSetting",
                        "auth":[
                            "view",
                            "add",
                            "edit",
                            "del",
                            "outport"
                        ]
                    },
                    {
                        "title":"AreaSetting",
                        "index":1,
                        "url":"/renovation/screen/areaSetting",
                        "auth":[
                            "view",
                            "add",
                            "edit",
                            "del",
                            "outport"
                        ]
                    },
                    {
                        "title":"TotalPriceSetting",
                        "index":2,
                        "url":"/renovation/screen/priceSetting",
                        "auth":[
                            "view",
                            "add",
                            "edit",
                            "del",
                            "outport"
                        ]
                    },
                    {
                        "title":"StyleSetting",
                        "index":3,
                        "url":"/renovation/screen/styleSetting",
                        "auth":[
                            "view",
                            "add",
                            "edit",
                            "del",
                            "outport"
                        ]
                    },
                    {
                        "title":"ApartmentSetting",
                        "index":4,
                        "url":"/renovation/screen/apartmentSetting",
                        "auth":[
                            "view",
                            "add",
                            "edit",
                            "del",
                            "outport"
                        ]
                    },
                    {
                        "title":"StageSetting",
                        "index":5,
                        "url":"/renovation/screen/stageSetting",
                        "auth":[
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
            "path":"screen/apartmentSetting",
            "name":"RenovationScreenApartmentSet",
            "component":"/renovation/screen/apartment",
            "meta":{
                "keepAlive":true,
                "title":"RenovationManage",
                "auth":[

                ],
                "isOnly":false,
                "pagePermission":[
                    {
                        "title":"RegionSetting",
                        "index":0,
                        "url":"/renovation/screen/regionSetting",
                        "auth":[
                            "view",
                            "add",
                            "edit",
                            "del",
                            "outport"
                        ]
                    },
                    {
                        "title":"AreaSetting",
                        "index":1,
                        "url":"/renovation/screen/areaSetting",
                        "auth":[
                            "view",
                            "add",
                            "edit",
                            "del",
                            "outport"
                        ]
                    },
                    {
                        "title":"TotalPriceSetting",
                        "index":2,
                        "url":"/renovation/screen/priceSetting",
                        "auth":[
                            "view",
                            "add",
                            "edit",
                            "del",
                            "outport"
                        ]
                    },
                    {
                        "title":"StyleSetting",
                        "index":3,
                        "url":"/renovation/screen/styleSetting",
                        "auth":[
                            "view",
                            "add",
                            "edit",
                            "del",
                            "outport"
                        ]
                    },
                    {
                        "title":"ApartmentSetting",
                        "index":4,
                        "url":"/renovation/screen/apartmentSetting",
                        "auth":[
                            "view",
                            "add",
                            "edit",
                            "del",
                            "outport"
                        ]
                    },
                    {
                        "title":"StageSetting",
                        "index":5,
                        "url":"/renovation/screen/stageSetting",
                        "auth":[
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
            "path":"screen/stageSetting",
            "name":"RenovationScreenStageSet",
            "component":"/renovation/screen/stage",
            "meta":{
                "keepAlive":true,
                "title":"RenovationManage",
                "auth":[

                ],
                "isOnly":false,
                "pagePermission":[
                    {
                        "title":"RegionSetting",
                        "index":0,
                        "url":"/renovation/screen/regionSetting",
                        "auth":[
                            "view",
                            "add",
                            "edit",
                            "del",
                            "outport"
                        ]
                    },
                    {
                        "title":"AreaSetting",
                        "index":1,
                        "url":"/renovation/screen/areaSetting",
                        "auth":[
                            "view",
                            "add",
                            "edit",
                            "del",
                            "outport"
                        ]
                    },
                    {
                        "title":"TotalPriceSetting",
                        "index":2,
                        "url":"/renovation/screen/priceSetting",
                        "auth":[
                            "view",
                            "add",
                            "edit",
                            "del",
                            "outport"
                        ]
                    },
                    {
                        "title":"StyleSetting",
                        "index":3,
                        "url":"/renovation/screen/styleSetting",
                        "auth":[
                            "view",
                            "add",
                            "edit",
                            "del",
                            "outport"
                        ]
                    },
                    {
                        "title":"ApartmentSetting",
                        "index":4,
                        "url":"/renovation/screen/apartmentSetting",
                        "auth":[
                            "view",
                            "add",
                            "edit",
                            "del",
                            "outport"
                        ]
                    },
                    {
                        "title":"StageSetting",
                        "index":5,
                        "url":"/renovation/screen/stageSetting",
                        "auth":[
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
            "path":"about/index",
            "name":"CompanyProfile",
            "component":"/renovation/about/index",
            "meta":{
                "keepAlive":true,
                "title":"RenovationManage",
                "auth":[
                    "view",
                    "add",
                    "edit",
                    "del",
                    "outport"
                ],
                "isOnly":true,
                "pagePermission":[
                    {
                        "title":"CompanyProfile",
                        "index":0,
                        "auth":[
                            "view",
                            "add",
                            "edit",
                            "del",
                            "outport"
                        ]
                    }
                ]
            }
        }
    ]
}
DECORATE;

$diy = <<<DIY
{"path":"/diy","component":"Layout","redirect":"/diy/list","meta":{"menuName":"diy","icon":"iconchaifenyemian","subNavName":[{"name":"diySub","url":[{"name":"diyList","url":"/diy/list"}]}]},"children":[{"path":"list","name":"diyList","component":"/diy/list","meta":{"keepAlive":true,"title":"diySub","auth":[],"isOnly":false,"pagePermission":[{"title":"diyList","index":0,"auth":["view","add","edit","del","outport"]}]}},{"path":"edit","name":"diySub","component":"/diy/edit","meta":{"keepAlive":true,"title":"diySub","auth":[],"isOnly":false,"pagePermission":[{"title":"diySub","index":0,"auth":["view","add","edit","del","outport"]}]}}]}
DIY;


return [
    'Survey' => $survey,
    'BusinessCard' => $businessCard,
//  'Malls' => $malls,
//  'Dynamic' => $dynamic,
//  'Website' => $website,
    'Customer' => $customer,
    'Company' => $company,
    'System' => $sys,
    'Renovation' => $renovation,
    'Diy' => $diy,
    'App' => $app,
];
