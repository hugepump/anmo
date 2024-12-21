<?php
/**
 * Created by PhpStorm.
 * User: shuixian
 * Date: 2019/11/20
 * Time: 18:29
 */


$tmp = sassAuth()==1?',{
				"name": "PurchaseList",
				"url": "/malls/purchase"
			}':'';

$malls = <<<MALLS


{
	"path": "/malls",
	"component": "Layout",
	"redirect": "/malls/list",
	"meta": {
		"menuName": "Malls",
		"icon": "icon-gouwudai",
		"subNavName": [{
			"name": "MallsManage",
			"url": [{
				"name": "GoodsList",
				"url": "/malls/list"
			}, {
				"name": "GoodsClassify",
				"url": "/malls/classify"
			}, {
				"name": "ParameterManagement",
				"url": "/malls/parameter"
			} $tmp
			]
		}, {
			"name": "StoreManage",
			"url": [{
				"name": "StoreList",
				"url": "/malls/storeManage"
			}]
		}, {
			"name": "OrderManage",
			"url": [{
				"name": "OrderManage",
				"url": "/malls/orderManage"
			}, {
				"name": "RefundManage",
				"url": "/malls/refundManage"
			}]
		}, {
			"name": "MarketingManage",
			"url": [{
				"name": "AssembleList",
				"url": "/malls/assemble"
			}, {
				"name": "RedPackit",
				"url": "/malls/redPackit"
			}]
		}, {
			"name": "MallsSet",
			"url": [{
				"name": "DealSet",
				"url": "/malls/dealSet"
			}, {
				"name": "VirtualPaymentSet",
				"url": "/malls/virtualPayment"
			}, {
				"name": "StaffChoiceGoods",
				"url": "/malls/staffGoods"
			}, {
				"name": "MallsBanner",
				"url": "/malls/banner"
			}]
		}, {
			"name": "Distributioninfo",
			"url": [{
				"name": "ProfitInfo",
				"url": "/malls/profit"
			}, {
				"name": "CommissionInfo",
				"url": "/malls/commission"
			}, {
				"name": "TakeCashInfo",
				"url": "/malls/cash"
			}, {
				"name": "DistributionRelation",
				"url": "/malls/relation"
			}, {
				"name": "DistributionSetting",
				"url": "/malls/disSet"
			}, {
				"name": "DistributionCash",
				"url": "/malls/disCash"
			}, {
				"name": "DistributionAudit",
				"url": "/malls/disaudit"
			}]
		}]
	},
	"children": [{
		"path": "list",
		"name": "GoodsList",
		"component": "/malls/goods/list",
		"meta": {
			"keepAlive": true,
			"refresh": false,
			"title": "MallsManage",
			"isOnly": false,
			"auth": [],
			"pagePermission": [{
				"title": "GoodsList",
				"index": 0,
				"auth": ["view", "add", "edit", "del", "outport"]
			}]
		}
	}, {
		"path": "edit",
		"name": "GoodsEdit",
		"component": "/malls/goods/edit",
		"meta": {
			"title": "MallsManage",
			"isOnly": false,
			"auth": [],
			"pagePermission": [{
				"title": "GoodsEdit",
				"index": 0,
				"auth": ["view", "add", "edit", "del", "outport"]
			}]
		}
	}, {
		"path": "classify",
		"name": "GoodsClassify",
		"component": "/malls/goods/classify",
		"meta": {
			"keepAlive": true,
			"refresh": false,
			"title": "MallsManage",
			"isOnly": false,
			"auth": [],
			"pagePermission": [{
				"title": "GoodsClassify",
				"index": 0,
				"auth": ["view", "add", "edit", "del", "outport"]
			}]
		}
	}, {
		"path": "addClassify",
		"name": "SpecsClassify",
		"component": "/malls/goods/addClassify",
		"meta": {
			"title": "MallsManage",
			"isOnly": false,
			"auth": [],
			"pagePermission": [{
				"title": "AddClassify",
				"index": 0,
				"auth": ["view", "add", "edit", "del", "outport"]
			}]
		}
	}, {
		"path": "parameter",
		"name": "ParameterManagement",
		"component": "/malls/goods/parameter",
		"meta": {
			"keepAlive": true,
			"refresh": false,
			"title": "MallsManage",
			"isOnly": false,
			"auth": [],
			"pagePermission": [{
				"title": "ParameterManagement",
				"index": 0,
				"auth": ["view", "add", "edit", "del", "outport"]
			}]
		}
	}, {
		"path": "parimary",
		"name": "parimar",
		"component": "/malls/goods/parimary",
		"meta": {
			"title": "MallsManage",
			"isOnly": false,
			"auth": [],
			"pagePermission": [{
				"title": "parimar",
				"index": 0,
				"auth": ["view", "add", "edit", "del", "outport"]
			}]
		}
	}, {
		"path": "secndparimary",
		"name": "secndParimar",
		"component": "/malls/goods/secndparimary",
		"meta": {
			"title": "MallsManage",
			"isOnly": false,
			"auth": [],
			"pagePermission": [{
				"title": "secndParimar",
				"index": 0,
				"auth": ["view", "add", "edit", "del", "outport"]
			}]
		}
	}, {
		"path": "purchase",
		"name": "PurchaseList",
		"component": "/malls/goods/purchase",
		"meta": {
			"keepAlive": true,
			"refresh": false,
			"title": "MallsManage",
			"isOnly": false,
			"auth": [],
			"pagePermission": [{
				"title": "PurchaseList",
				"index": 0,
				"auth": ["view", "add", "edit", "del", "outport"]
			}]
		}
	}, {
		"path": "editPurchase",
		"name": "PurchaseAdd",
		"component": "/malls/goods/editPurchase",
		"meta": {
			"title": "MallsManage",
			"isOnly": false,
			"auth": [],
			"pagePermission": [{
				"title": "PurchaseAdd",
				"index": 0,
				"auth": ["view", "add", "edit", "del", "outport"]
			}]
		}
	},
	 {
         "path": "wholesale",
         "name": "WholesaleList",
         "component": "/malls/goods/wholesale",
         "meta": {
          "keepAlive": true,
          "refresh": false,
          "title": "MallsManage",
          "isOnly": false,
          "auth": [],
          "pagePermission": [{
           "title": "WholesaleList",
           "index": 0,
           "auth": ["view", "add", "edit", "del", "outport"]
          }]
         }
        }, {
         "path": "editWholesale",
         "name": "WholesaleAdd",
         "component": "/malls/goods/editWholesale",
         "meta": {
          "title": "MallsManage",
          "isOnly": false,
          "auth": [],
          "pagePermission": [{
           "title": "WholesaleAdd",
           "index": 0,
           "auth": ["view", "add", "edit", "del", "outport"]
          }]
         }
        }
	 ,{
		"path": "storeManage",
		"name": "StoreList",
		"component": "/malls/store/list",
		"meta": {
			"keepAlive": true,
			"title": "MallsManage",
			"isOnly": false,
			"auth": [],
			"pagePermission": [{
				"title": "StoreList",
				"index": 0,
				"auth": ["view", "add", "edit", "del", "outport"]
			}]
		}
	}, {
		"path": "store",
		"name": "StoreAdd",
		"component": "/malls/store/edit",
		"meta": {
			"title": "MallsManage",
			"isOnly": false,
			"auth": [],
			"pagePermission": [{
				"title": "StoreAdd",
				"index": 0,
				"auth": ["view", "add", "edit", "del", "outport"]
			}]
		}
	}, {
		"path": "orderManage",
		"name": "OrderManage",
		"component": "/malls/order/manage",
		"meta": {
			"keepAlive": true,
			"title": "MallsManage",
			"isOnly": false,
			"auth": [],
			"pagePermission": [{
				"title": "OrderManage",
				"index": 0,
				"auth": ["view", "add", "edit", "del", "outport"]
			}]
		}
	}, {
		"path": "orderDetail",
		"name": "OrderDetail",
		"component": "/malls/order/detail",
		"meta": {
			"title": "MallsManage",
			"isOnly": false,
			"auth": [],
			"pagePermission": [{
				"title": "OrderDetail",
				"index": 0,
				"auth": ["view", "add", "edit", "del", "outport"]
			}]
		}
	}, {
		"path": "refundManage",
		"name": "RefundManage",
		"component": "/malls/order/refund",
		"meta": {
			"keepAlive": true,
			"title": "MallsManage",
			"isOnly": false,
			"auth": [],
			"pagePermission": [{
				"title": "RefundManage",
				"index": 0,
				"auth": ["view", "add", "edit", "del", "outport"]
			}]
		}
	}, {
		"path": "newAssemble",
		"name": "NewAssemble",
		"component": "/malls/marketing/newAssemble",
		"meta": {
			"keepAlive": true,
			"title": "MallsManage",
			"isOnly": false,
			"auth": [],
			"pagePermission": [{
				"title": "NewAssemble",
				"index": 0,
				"auth": ["view", "add", "edit", "del", "outport"]
			}]
		}
	}, {
		"path": "assemble",
		"name": "AssembleList",
		"component": "/malls/marketing/assemble",
		"meta": {
			"title": "MallsManage",
			"isOnly": false,
			"auth": [],
			"pagePermission": [{
				"title": "AssembleList",
				"index": 0,
				"auth": ["view", "add", "edit", "del", "outport"]
			}]
		}
	}, {
		"path": "assembleManage",
		"name": "AssembleManage",
		"component": "/malls/marketing/assembleManage",
		"meta": {
			"title": "MallsManage",
			"isOnly": false,
			"auth": [],
			"pagePermission": [{
				"title": "AssembleManage",
				"index": 0,
				"auth": ["view", "add", "edit", "del", "outport"]
			}]
		}
	}, {
		"path": "redPackit",
		"name": "RedPackit",
		"component": "/malls/marketing/redPackit",
		"meta": {
			"keepAlive": true,
			"title": "MallsManage",
			"isOnly": false,
			"auth": [],
			"pagePermission": [{
				"title": "RedPackit",
				"index": 0,
				"auth": ["view", "add", "edit", "del", "outport"]
			}]
		}
	}, {
		"path": "addRedPackit",
		"name": "EditRedPackit",
		"component": "/malls/marketing/addRedPackit",
		"meta": {
			"title": "MallsManage",
			"isOnly": false,
			"auth": [],
			"pagePermission": [{
				"title": "EditRedPackit",
				"index": 0,
				"auth": ["view", "add", "edit", "del", "outport"]
			}]
		}
	}, {
		"path": "dealSet",
		"name": "DealSet",
		"component": "/malls/set/deal",
		"meta": {
			"title": "MallsManage",
			"isOnly": false,
			"auth": [],
			"pagePermission": [{
				"title": "DealSet",
				"index": 0,
				"auth": ["view", "add", "edit", "del", "outport"]
			}]
		}
	}, {
		"path": "virtualPayment",
		"name": "VirtualPaymentSet",
		"component": "/malls/set/payment",
		"meta": {
			"title": "MallsManage",
			"isOnly": false,
			"auth": [],
			"pagePermission": [{
				"title": "VirtualPaymentSet",
				"index": 0,
				"auth": ["view", "add", "edit", "del", "outport"]
			}]
		}
	}, {
		"path": "banner",
		"name": "MallsBanner",
		"component": "/malls/set/banner",
		"meta": {
			"keepAlive": true,
			"title": "MallsManage",
			"isOnly": false,
			"auth": [],
			"pagePermission": [{
				"title": "MallsBanner",
				"index": 0,
				"auth": ["view", "add", "edit", "del", "outport"]
			}]
		}
	}, {
		"path": "editBanner",
		"name": "EditBanner",
		"component": "/malls/set/editBanner",
		"meta": {
			"title": "MallsManage",
			"isOnly": false,
			"auth": [],
			"pagePermission": [{
				"title": "EditBanner",
				"index": 0,
				"auth": ["view", "add", "edit", "del", "outport"]
			}]
		}
	}, {
		"path": "staffGoods",
		"name": "StaffChoiceGoods",
		"component": "/malls/set/staffGoods",
		"meta": {
			"title": "MallsManage",
			"isOnly": false,
			"auth": [],
			"pagePermission": [{
				"title": "StaffChoiceGoods",
				"index": 0,
				"auth": ["view", "add", "edit", "del", "outport"]
			}]
		}
	}, {
		"path": "profit",
		"component": "/malls/distribution/profit",
		"name": "ProfitInfo",
		"meta": {
			"title": "MallsManage",
			"auth": [],
			"isOnly": false,
			"keepAlive": true,
			"pagePermission": [{
				"title": "ProfitInfo",
				"index": 0,
				"auth": ["view", "add", "edit", "del", "outport"]
			}]
		}
	}, {
		"path": "commission",
		"component": "/malls/distribution/commission",
		"name": "CommissionInfo",
		"meta": {
			"title": "MallsManage",
			"auth": [],
			"isOnly": false,
			"keepAlive": true,
			"pagePermission": [{
				"title": "CommissionInfo",
				"index": 0,
				"auth": ["view", "add", "edit", "del", "outport"]
			}]
		}
	}, {
		"path": "cash",
		"component": "/malls/distribution/takeCash",
		"name": "TakeCashInfo",
		"meta": {
			"title": "MallsManage",
			"auth": [],
			"isOnly": false,
			"keepAlive": true,
			"pagePermission": [{
				"title": "TakeCashInfo",
				"index": 0,
				"auth": ["view", "add", "edit", "del", "outport"]
			}]
		}
	}, {
		"path": "relation",
		"component": "/malls/distribution/relation",
		"name": "DistributionRelation",
		"meta": {
			"title": "MallsManage",
			"auth": [],
			"isOnly": false,
			"keepAlive": true,
			"pagePermission": [{
				"title": "DistributionRelation",
				"index": 0,
				"auth": ["view", "add", "edit", "del", "outport"]
			}]
		}
	}, {
		"path": "disSet",
		"component": "/malls/distribution/set",
		"name": "DistributionSetting",
		"meta": {
			"title": "MallsManage",
			"auth": [],
			"isOnly": false,
			"pagePermission": [{
				"title": "DistributionSetting",
				"index": 0,
				"auth": ["view", "add", "edit", "del", "outport"]
			}]
		}
	}, {
		"path": "disCash",
		"component": "/malls/distribution/cash",
		"name": "DistributionCash",
		"meta": {
			"title": "MallsManage",
			"auth": [],
			"isOnly": false,
			"pagePermission": [{
				"title": "DistributionCash",
				"index": 0,
				"auth": ["view", "add", "edit", "del", "outport"]
			}]
		}
	}, {
		"path": "disaudit",
		"component": "/malls/distribution/audit",
		"name": "DistributionAudit",
		"meta": {
			"title": "MallsManage",
			"auth": [],
			"isOnly": false,
			"pagePermission": [{
				"title": "DistributionAudit",
				"index": 0,
				"auth": ["view", "add", "edit", "del", "outport"]
			}]
		}
	}]
}

MALLS;

//return json_decode($menu, true) ;

return ["shop" => $malls];


