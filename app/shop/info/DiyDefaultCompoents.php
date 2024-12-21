<?php
/**
 * Created by PhpStorm.
 * User: shuixian
 * Date: 2019/11/20
 * Time: 18:29
 */


$defaultPage=<<<DEFAULT

{
	"key": 2,
	"list": [{
			"title": "搜索栏",
			"type": "search",
			"iconPath": "iconsousuo",
			"isDelete": true,
			"addNumber": 1,
			"attr": [{
				"title": "是否显示全部分类",
				"type": "Switch",
				"name": "isShowCateAll"
			}],
			"data": {
				"placeholder": "请输入搜索内容",
				"isShowCateAll": true
			}
		},
		{
			"title": "轮播图",
			"type": "banner",
			"icon": "iconlunbo",
			"isDelete": true,
			"addNumber": 9999,
			"attr": [{
					"title": "选择模板",
					"type": "ChooseModule",
					"name": "bannerName",
					"data": [{
						"title": "通屏轮播",
						"name": "banner-tongping",
						"img": "http://longbingcdn.xiaochengxucms.com/admin/diy/banner-tongping.png"
					}]
				},
				{
					"title": "图片列表",
					"type": "ImageLink",
					"name": "bannerList",
					"isDraggable": true,
					"isDelete": true,
					"data": [{
							"title": "图片",
							"type": "UploadImage",
							"name": "img",
							"desc": "750*350"
						},
						{
							"title": "链接类型",
							"type": "Select",
							"name": "linkType",
							"data": [{
									"label": "小程序内部页面",
									"value": 4
								},
								{
									"label": "其他小程序",
									"value": 2
								},
								{
									"label": "跳转网页",
									"value": 3
								},
								{
									"label": "拨打电话",
									"value": 1
								}
							]
						},
						{
							"title": "链接地址",
							"type": "Tag",
							"name": "link"
						}
					]
				},
				{
					"title": "添加模板",
					"type": "Add",
					"name": "addMouduleName",
					"addNumber": 10,
					"data": [{
						"link": [{
							"title": ""
						}],
						"linkType": 4,
						"img": [{
							"url": "http://longbingcdn.xiaochengxucms.com/admin/diy/default.png"
						}]
					}]
				}
			],
			"data": {
				"style": {
					"height": 350,
					"whiteSpace": 0,
					"wingBlank": 0
				},
				"bannerName": "banner-tongping",
				"addMouduleName": "bannerList",
				"bannerList": []
			},
			"id": 1591856492396,
			"compontents": "base"
		},
		{
			"title": "导航",
			"type": "column",
			"icon": "icondaohang1",
			"isDelete": true,
			"addNumber": 9999,
			"attr": [{
					"title": "多少行",
					"type": "InputNumber",
					"name": "row"
				},
				{
					"title": "每行多少列",
					"type": "InputNumber",
					"name": "col"
				},
				{
					"title": "图片列表",
					"type": "ImageLink",
					"name": "columnList",
					"isDraggable": true,
					"isDelete": true,
					"data": [{
							"title": "按钮文字",
							"type": "Input",
							"name": "title"
						},
						{
							"title": "图片",
							"type": "UploadImage",
							"name": "img",
							"desc": "100*100"
						},
						{
							"title": "链接类型",
							"type": "Select",
							"name": "linkType",
							"data": [{
									"label": "小程序内部页面",
									"value": 4
								},
								{
									"label": "其他小程序",
									"value": 2
								},
								{
									"label": "跳转网页",
									"value": 3
								},
								{
									"label": "拨打电话",
									"value": 1
								}
							]
						},
						{
							"title": "链接地址",
							"type": "Tag",
							"name": "link"
						}
					]
				},
				{
					"title": "添加模板",
					"type": "Add",
					"name": "addMouduleName",
					"addNumber": 16,
					"data": [{
						"link": [{
							"title": ""
						}],
						"linkType": 4,
						"img": [{
							"url": "http://longbingcdn.xiaochengxucms.com/admin/diy/default.png"
						}]
					}]
				}
			],
			"data": {
				"row": {
					"number": 2,
					"min": 1,
					"max": 2
				},
				"col": {
					"number": 4,
					"min": 4,
					"max": 5
				},
				"style": {
					"fontColor": "#666",
					"background": "#ffffff",
					"whiteSpace": 30,
					"wingBlank": 0
				},
				"addMouduleName": "columnList",
				"columnList": []
			},
			"id": 1591856495749,
			"compontents": "base"
		},
		{
			"title": "卡券",
			"type": "couponList",
			"icon": "iconCouponList",
			"isDelete": true,
			"addNumber": 1,
			"attr": [{
					"title": "模板名称",
					"type": "Input",
					"name": "title",
					"maxLength": 10
				},
				{
					"title": "卡券样式",
					"type": "Radio",
					"name": "type",
					"data": [{
							"label": 1,
							"title": "弹窗样式"
						},
						{
							"label": 2,
							"title": "列表样式"
						}
					]
				}
			],
			"data": {
				"title": "领取卡券",
				"type": 2,
				"dataList": []
			},
			"id": 1591856498485,
			"compontents": "operate"
		},
		{
			"title": "商品列表",
			"type": "goodsList",
			"icon": "iconGoodsList",
			"isDelete": true,
			"addNumber": 1,
			"attr": [],
			"data": {
				"title": "商品列表",
				"limit": "",
				"dataList": []
			},
			"id": 1591856499273,
			"compontents": "shopCompoent"
		}
	]
}

DEFAULT;


$pages = json_decode( $defaultPage , true);


return $pages;