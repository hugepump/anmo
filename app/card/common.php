<?php
use app\card\model\UserInfo;
use think\facade\Db;

//获取最小account
function longbingGetUserInfoMinAutoCount($uniacid)
{
	$user_info_model = new UserInfo();
	$count = $user_info_model->getStaffMaxAutoCount(['uniacid' => $uniacid]);
	if(empty($count)) $count = 0;
	return (int)$count;
}
function longbingGetWxAppTabbarResponse($data)
{
	if(empty($data)) return [];
	//数据处理
	$data['list'] = [];
	//处理过的参数
	$menus = [];
	//默认设置
	$data['color'] = '#5d6268';
//	$data['selectedColor'] = '#21bf34';
	$data['selectedColor'] = '#19c865';
	$data['backgroundColor'] = '#fff';
	$data['borderStyle'] = 'white';
	//名片
	if(isset($data['menu1_is_hide']) && !empty($data['menu1_is_hide']))
	{
		$val = [];
		$val['is_show']  = $data['menu1_is_hide'];
		$val['key']      = 1;
		$val['iconPath'] = 'icon-mingpian';
		$val['selectedIconPath'] = 'icon-mingpian1';
		$val['pageComponents']   = 'cardHome';
		if(isset($data['menu1_name'])) $val['name'] = $data['menu1_name'];
		if(isset($data['menu1_url'])) $val['url'] = $data['menu1_url'];
		$val['url'] = "/pages/user/home";
		if(isset($data['menu1_url_out'])) $val['url_out'] = $data['menu1_url_out'];
		if(isset($data['menu1_url_jump_way'])) $val['jump_way'] = $data['menu1_url_jump_way'];
		$data['list'][] = $val;
	}
	//商城
	if(isset($data['menu2_is_hide']) && !empty($data['menu2_is_hide']))
	{
		$val = [];
		$val['key']      = 2;
		$val['is_show']  = $data['menu2_is_hide'];
		$val['iconPath'] = 'icon-shangcheng1';
		$val['selectedIconPath'] = 'icon-shangcheng';
		$val['pageComponents']   = 'shopHome';
		if(isset($data['menu2_name'])) $val['name'] = $data['menu2_name'];
		if(isset($data['menu2_url'])) $val['url'] = $data['menu2_url'];
		$val['url'] = "/pages/user/home";
		if(isset($data['menu2_url_out'])) $val['url_out'] = $data['menu2_url_out'];
		if(isset($data['menu2_url_jump_way'])) $val['url_jump_way'] = $data['menu2_url_jump_way'];
		$data['list'][] = $val;
	}
	//动态
	if(isset($data['menu3_is_hide']) && !empty($data['menu3_is_hide']))
	{
		$val = [];
		$val['key']      = 3;
		$val['is_show']  = $data['menu3_is_hide'];
		$val['iconPath'] = 'icon-dongtai1';
		$val['selectedIconPath'] = 'icon-dongtai';
		$val['pageComponents']   = 'infoHome';
		if(isset($data['menu3_name'])) $val['name'] = $data['menu3_name'];
		if(isset($data['menu3_url'])) $val['url'] = $data['menu3_url'];
		$val['url'] = "/pages/user/home";
		if(isset($data['menu3_url_out'])) $val['url_out'] = $data['menu3_url_out'];
		if(isset($data['menu3_url_jump_way'])) $val['url_jump_way'] = $data['menu3_url_jump_way'];
		$data['list'][] = $val;
	}
	//官网
	if(isset($data['menu4_is_hide']) && !empty($data['menu4_is_hide']))
	{
		$val = [];
		$val['key']      = 4;
		$val['is_show']  = $data['menu4_is_hide'];
		$val['iconPath'] = 'icon-guanwang';
		$val['selectedIconPath'] = 'icon-guanwang1';
		$val['pageComponents']   = 'websiteHome';
		if(isset($data['menu4_name'])) $val['name'] = $data['menu4_name'];
		if(isset($data['menu4_url'])) $val['url'] = $data['menu4_url'];
		$val['url'] = "/pages/user/home";
		if(isset($data['menu4_url_out'])) $val['url_out'] = $data['menu4_url_out'];
		if(isset($data['menu4_url_jump_way'])) $val['url_jump_way'] = $data['menu4_url_jump_way'];
		$data['list'][] = $val;
	}
	//预约
	if(isset($data['menu_appoint_is_hide']) && !empty($data['menu_appoint_is_hide']))
	{
		$val = [];
		$val['key']      = 7;
		$val['is_show']  = $data['menu_appoint_is_hide'];
		$val['iconPath'] = 'icon-yuyue';
		$val['selectedIconPath'] = 'icon-yuyue1';
		$val['pageComponents']   = 'reserveHome';
		if(isset($data['menu_appoint_name'])) $val['name'] = $data['menu_appoint_name'];
		if(isset($data['menu_appoint_url'])) $val['url'] = $data['menu_appoint_url'];
		$val['url'] = "/pages/user/home";
		if(isset($data['menu_appoint_url_out'])) $val['url_out'] = $data['menu_appoint_url_out'];
		if(isset($data['menu_appoint_url_jump_way'])) $val['url_jump_way'] = $data['menu_appoint_url_jump_way'];
		$data['list'][] = $val;
	}
	//活动报名
	if(isset($data['menu_activity_is_show']) && !empty($data['menu_activity_is_show']))
	{
		$val = [];
		$val['key']      = 6;
		$val['is_show']  = $data['menu_activity_is_show'];
		$val['iconPath'] = 'icon-huodong1';
		$val['selectedIconPath'] = 'icon-huodong';
		$val['pageComponents']   = 'avtivityHome';
		if(isset($data['menu_activity_name'])) $val['name'] = $data['menu_activity_name'];
		if(isset($data['menu_activity_url'])) $val['url'] = $data['menu_activity_url'];
		$val['url'] = "/pages/user/home";
		if(isset($data['menu_activity_url_out'])) $val['url_out'] = $data['menu_activity_url_out'];
		if(isset($data['menu_activity_url_jump_way'])) $val['url_jump_way'] = $data['menu_activity_url_jump_way'];
		$data['list'][] = $val;
	}
	//房产
	if(isset($data['menu_house_is_show']) && !empty($data['menu_house_is_show']))
	{
		$val = [];
		$val['key']      = 5;
		$val['is_show']  = $data['menu_house_is_show'];
		$val['iconPath'] = 'icon-fangchan1';
		$val['selectedIconPath'] = 'icon-fangchan';
		$val['pageComponents']   = 'houseHome';
		if(isset($data['menu_house_name']))$val['name'] = $data['menu_house_name'];
		if(isset($data['menu_house_url']))$val['url'] = $data['menu_house_url'];
		$val['url'] = "/pages/user/home";
		if(isset($data['menu_house_url_out']))$val['url_out'] = $data['menu_house_url_out'];
		if(isset($data['menu_house_url_jump_way']))$val['url_jump_way'] = $data['menu_house_url_jump_way'];
		$data['list'][] = $val;
	}
	$menus = ["menu1_name","menu1_is_hide","menu1_url","menu1_url_out","menu1_url_jump_way","menu2_name","menu2_is_hide","menu2_url","menu2_url_out","menu2_url_jump_way","menu3_name","menu3_is_hide","menu3_url","menu3_url_out","menu3_url_jump_way","menu4_name","menu4_is_hide","menu4_url","menu4_url_out","menu4_url_jump_way","menu_appoint_name","menu_appoint_is_hide","menu_appoint_url","menu_appoint_url_out","menu_appoint_url_jump_way","menu_activity_is_show","menu_activity_name","menu_activity_is_hide","menu_activity_url","menu_activity_url_out","menu_activity_url_jump_way","menu_house_is_show","menu_house_name","menu_house_is_hide","menu_house_url","menu_house_url_out","menu_house_url_jump_way"];
	foreach($menus as $menu)
	{
		unset($data[$menu]);
	}
	return $data;
}
if ( !function_exists( 'wxContentRlue' ) ) {
//检测评论发表是否合法
    function wxContentRlue($content, $access_token)
    {
        $url = "https://api.weixin.qq.com/wxa/msg_sec_check?access_token={$access_token}";
        $tmp = [
            'url' => $url,
            'data' => [
                'content' => urlencode($content)
            ],
        ];
        $rest = lbCurlPost($tmp['url'], urldecode(json_encode($tmp['data'])));
        $rest = json_decode($rest, true);
        return $rest;
    }
}

