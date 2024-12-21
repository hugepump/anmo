<?php


namespace app\Common;

require_once "keygen.php";
class order
{
	private $uniacid;
	private $goods_name;
	private $base_url;
	private $check_url;
	private $get_auth_url;
	private $get_domain_param_url;
	private $http_host;
	private $server_name;
	private $request_time;
	private $public_key;
	private $domain_name_info;
	private $is_debug = false;
	private $token_path;
	public function __construct($uniacid = 1, $goodsName = "longbing_massages_city", $is_debug = false)
	{
		$this->is_debug = $is_debug;
		$this->token_path = dirname(__FILE__) . "/token.key";
		$this->uniacid = $uniacid . "";
		$this->goods_name = $goodsName;
		$this->base_url = base64_decode('aHR0cDovLzU5LjExMC42Ni4xMjA6ODMv');
		$this->check_url = $this->base_url . "auth/home.Index/index";
		$this->get_auth_url = $this->base_url . "auth/home.Index/getAuth";
		$this->get_domain_param_url = $this->base_url . "auth/home.Index/domain_param";
		$this->http_host = $_SERVER["HTTP_HOST"];
		$this->server_name = $_SERVER["SERVER_NAME"];
		$this->request_time = $_SERVER["REQUEST_TIME"] . "";
	}
	public function order()
	{
		$this->public_key = $this->getPublicKey();
		$siginStr = $this->getSiginData([]);
		$result = $this->curl_post($this->check_url, $this->getPostData($siginStr));
		$result = json_decode($result, true);
		$data = $result["data"];
		if (empty($data)) {
			$data = -1;
		} else {
			if (!empty($data["goods_version_info"])) {
				$data = 1;
			}
		}
		$arr["data"] = $data;
		return $arr;
	}
	private function getPostData($siginStr)
	{
		$postData = $this->getPublicPostData();
		$postData["sigin"] = $siginStr;
		return $postData;
	}
	private function getSiginData($extData = [], $siginType = 1)
	{
		$data = $this->getPublicPostData();
		if (!empty($extData)) {
			$data["ext_data"] = $extData;
		}
		ksort($data);
		$str_data = json_encode($data);
		if ($siginType == 1) {
			@openssl_public_encrypt($str_data, $encrypted, $this->public_key);
			if (empty($encrypted)) {
				return false;
			}
			$encrypted = base64_encode($encrypted);
		} else {
			$encrypted = $this->getSiginDataByHash($data);
		}
		return $encrypted;
	}
	private function getSiginDataByHash($data)
	{
		$data["token"] = $data["token"] ? $data["token"] : "";
		$data = is_array($data) ? json_encode($data) : (is_string($data) ? $data : time() . "") . "LongbingShuixian";
		$siginStr = hash("sha256", $data);
		return $siginStr;
	}
	private function getPublicPostData()
	{
		$app_model_name = $this->goods_name;
		$token = @file_get_contents($this->token_path);
		$token = $token ? json_decode($token, true) : "";
		if (!empty($token)) {
			$token = $token["token"];
		}
		$data = ["uniacid" => $this->uniacid, "app_model_name" => $app_model_name, "goods_name" => $this->goods_name, "http_host" => $this->http_host, "server_name" => $this->server_name, "request_time" => $this->request_time, "token" => $token];
		return $data;
	}
	private function getPublicKey()
	{
		if (!empty($this->public_key)) {
			return $this->public_key;
		}
		$siginStr = $this->getSiginData([], 2);
		$result = $this->curl_post($this->get_auth_url, $this->getPostData($siginStr));
		$result = json_decode($result, true);
		$this->domain_name_info = $result["data"]["domain_name_info"];
		$token = $result["data"]["token"];
		$resultWriteToken = $this->writein_token($token);
		$this->public_key = $result["data"]["public_key"];
		return $this->public_key;
	}
	private function curl_post($url, $data = [])
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		$output = curl_exec($ch);
		curl_close($ch);
		return $output;
	}
	private function writein_token($token) : bool
	{
		$resultWriteToken = false;
		if (is_array($token)) {
			$resultWriteToken = file_put_contents($this->token_path, json_encode($token));
		} else {
			$token = @file_get_contents($this->token_path);
			$token = $token ? json_decode($token, true) : "";
			if (!empty($token)) {
				if ($token["token_expiration_time"] < time()) {
					$token["token"] = "";
					$resultWriteToken = file_put_contents($this->token_path, json_encode($token));
				}
			}
		}
		return $resultWriteToken ? true : false;
	}
}
