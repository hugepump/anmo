<?php
/**
 * Created by PhpStorm.
 * User: shuixian
 * Date: 2019/11/20
 * Time: 18:29
 */

$menu = <<<BUSINESSCARD

{
	"path": "/businessCard",
	"component": "Layout",
	"redirect": "/businessCard/staffCard",
	"meta": {
		"menuName": "BusinessCard",
		"icon": "icon-mingpian",
		"subNavName": [{
			"name": "CardInfo",
			"url": [{
				"name": "StaffCard",
				"url": "/businessCard/staffCard"
			}, {
				"name": "ImpressionLabel",
				"url": "/businessCard/tag"
			}]
		}, {
			"name": "CardSetting",
			"url": [{
				"name": "ExemptionPassword",
				"url": "/businessCard/exemptionPwd"
			}, {
				"name": "MobileSetting",
				"url": "/businessCard/mobileSetting"
			}, {
				"name": "MediaSetting",
				"url": "/businessCard/mediaSetting"
			}, {
				"name": "CardSetting",
				"url": "/businessCard/cardSetting"
			}]
		}]
	},
	"children": [{
		"path": "staffCard",
		"name": "StaffCard",
		"component": "/businessCard/manage/staffCard",
		"meta": {
			"keepAlive": true,
			"title": "CardManage",
			"isOnly": false,
			"auth": [],
			"pagePermission": [{
				"title": "StaffCard",
				"index": 0,
				"auth": ["view", "add", "edit", "del", "outport"]
			}]
		}
	}, {
		"path": "editCard",
		"name": "EditCard",
		"component": "/businessCard/manage/editCard",
		"meta": {
			"refresh": false,
			"title": "CardManage",
			"isOnly": false,
			"auth": [],
			"pagePermission": [{
				"title": "EditCard",
				"index": 0,
				"auth": ["view", "add", "edit", "del", "outport"]
			}]
		}
	}, {
		"path": "tag",
		"name": "ImpressionLabel",
		"component": "/businessCard/manage/tag",
		"meta": {
			"keepAlive": true,
			"title": "CardManage",
			"isOnly": false,
			"auth": [],
			"pagePermission": [{
				"title": "ImpressionLabel",
				"index": 0,
				"auth": ["view", "add", "edit", "del", "outport"]
			}]
		}
	}, {
		"path": "exemptionPwd",
		"name": "ExemptionPassword",
		"component": "/businessCard/set/exemptionPwd",
		"meta": {
			"keepAlive": true,
			"refresh": false,
			"title": "CardManage",
			"isOnly": false,
			"auth": [],
			"pagePermission": [{
				"title": "ExemptionPassword",
				"index": 0,
				"auth": ["view", "add", "edit", "del", "outport"]
			}]
		}
	}, {
		"path": "mobileSetting",
		"name": "MobileSetting",
		"component": "/businessCard/set/mobileSetting",
		"meta": {
			"keepAlive": true,
			"refresh": false,
			"title": "CardManage",
			"isOnly": false,
			"auth": [],
			"pagePermission": [{
				"title": "MobileSetting",
				"index": 0,
				"auth": ["view", "add", "edit", "del", "outport"]
			}]
		}
	}, {
		"path": "mediaSetting",
		"name": "MediaSetting",
		"component": "/businessCard/set/mediaSetting",
		"meta": {
			"keepAlive": true,
			"refresh": false,
			"title": "CardManage",
			"isOnly": false,
			"auth": [],
			"pagePermission": [{
				"title": "MediaSetting",
				"index": 0,
				"auth": ["view", "add", "edit", "del", "outport"]
			}]
		}
	}, {
		"path": "cardSetting",
		"name": "CardSetting",
		"component": "/businessCard/set/cardSetting",
		"meta": {
			"keepAlive": true,
			"refresh": false,
			"title": "CardManage",
			"isOnly": false,
			"auth": [],
			"pagePermission": [{
				"title": "CardSetting",
				"index": 0,
				"auth": ["view", "add", "edit", "del", "outport"]
			}]
		}
	}]
}


BUSINESSCARD;

//return json_decode($menu, true) ;

return ['card' => $menu];


