<?php
namespace app\Common;

use think\Request;
use think\Image;
use think\Session;	
use think\facade\Db;
use app\Common\Upload;
//生成微信小程序码
class WeChatCode {
	//小程序唯一识别码
	protected $uniacid;
	//系统配置
	protected $config;
	//accesstoken
	protected $access_token;
	//构造函数
	public function __construct($uniacid)
    {
    	$this->uniacid = $uniacid;
		$this->config  = longbingGetAppConfig($uniacid);
		$this->access_token  = $this->getAccessToken();
    }
	
	//获取accesstoken
	public function getAccessToken()
	{
		return longbingGetAccessToken($this->uniacid ,true);
	}
	
	//获取code (方法一)
	public function getQRCode($path ,$width = 430)
	{
		//url
		$url = "https://api.weixin.qq.com/cgi-bin/wxaapp/createwxaqrcode?access_token={$this->access_token}";
		$post_data = ['path' => $path ,'width' => $width];
		//发送数据
		$result = longbingCurl($url ,json_encode($post_data ,true) ,'POTH');
		return $result;
	}
	//获取code (方法二)
	public function getWxCode($path ,$width = 430 ,$auto_color = true ,$line_color = null ,$is_hyaline	= false)
	{
		//url
		$url = "https://api.weixin.qq.com/wxa/getwxacode?access_token={$this->access_token}";
		$post_data = ['path' => $path ,
					  'width' => $width ,
					  'auto_color' => $auto_color ,
					  'line_color' => $line_color ,
					  'is_hyaline' => $is_hyaline];
		//生成
		$result = longbingCurl($url ,json_encode($post_data ,true) ,'POTH');
		return $result;
	}
	//获取code (方法三)
	public function getUnlimitedCode($scene ,$path = '',$width = 430 ,$auto_color = false ,$line_color = '{"r":0,"g":0,"b":0}' ,$is_hyaline	= true)
	{
		$access_token = $this->access_token;


//		$access_token = '123';
		$url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token={$access_token}";
		$post_data = ['scene' => $scene ];
		if(!empty($width)) $post_data['width'] = $width;
		if(!empty($auto_color)) $post_data['auto_color'] = $auto_color;
//		if(!empty($line_color)) $post_data['line_color'] = $line_color;
		if(!empty($is_hyaline)) $post_data['is_hyaline'] = $is_hyaline;
//		if(!empty($auto_color)) unset($post_data['line_color']);
        if(!empty($path)) $post_data['page'] = $path;
		//生成
		$result = longbingCurl($url ,json_encode($post_data ,true) ,'POST');
		return $result;
	}
	
}
