<?php


declare (strict_types=1);
namespace app\admin\controller;

require_once "keygen.php";
abstract class Brcv
{
	protected $request;
	protected $app;
	protected $batchValidate = false;
	protected $middleware = [];
	public $_app;
	public $_controller;
	public $_action;
	public $_method = "GET";
	public $_param = [];
	public $_input = [];
	public $_header = [];
	public $_host;
	public $_ip;
	public $_is_weiqin = false;
	public function __construct(\think\App $app)
	{
		longbing_check_install();
		$this->app = $app;
		$this->request = $this->app->request;
		$this->_method = $this->request->method(true);
		$this->_is_weiqin = longbingIsWeiqin();
		$this->_app = $app->http->getName();
		$this->_controller = $this->request->controller();
		$this->_action = $this->request->action();
		$this->_param = $this->request->param();
		$this->_input = json_decode($this->request->getInput(), true);
		$this->_header = $this->request->header();
		$this->_host = $this->_header["host"];
		$this->_ip = $_SERVER["REMOTE_ADDR"];
		$this->initialize();
		$action = $this->request->action();
		$auth = $this->shareChangeDatasssss($action);
		if ($auth == true) {
			$this->isAuth(666);
		}
	}
	public function shareChangeDatasssss($action)
	{
		$arr = ["clearCache", "noLookCount", "getW7TmpV2", "getSaasAuth", "isWe7", "getConfig", "login", "adminNodeInfo"];
		if (!empty($action) && in_array($action, $arr)) {
			return false;
		}
		return true;
	}
	public function isAuth($uniacid)
	{
		$key = "sass_auth_auth_authsisd";
		$value = getCache($key, $uniacid);
		if (empty($value)) {
			include_once LONGBING_EXTEND_PATH . "Site.php";
			$goods_name = config("app.AdminModelList")["app_model_name"];
			$auth_uniacid = config("app.AdminModelList")["auth_uniacid"];
			$upgrade = new \Site($auth_uniacid, $goods_name, \think\facade\Env::get("j2hACuPrlohF9BvFsgatvaNFQxCBCc", false));
			$p = $upgrade->isAuthPa($uniacid);
			if ($p == 813) {
				setCache($key, 1813, 432000, $uniacid);
				return true;
			}
			return false;
		}
	}
	protected function errorMsg($msg = "", $code = 400)
	{
		$msg = \think\facade\Lang::get($msg);
		$this->results($msg, $code);
	}
	protected function results($msg, $code, array $header = [])
	{
		$result = ["error" => $msg, "code" => $code];
		$response = \think\Response::create($result, "json", 200)->header($header);
		throw new \think\exception\HttpResponseException($response);
	}
	protected function initialize()
	{
	}
	public function success($data, $code = 200)
	{
		$result["data"] = $data;
		$result["code"] = $code;
		$result["sign"] = null;
		$result["return_code"] = "SUCCESS";
		$result["return_msg"] = "OK";
		if (!empty($this->_token)) {
			$result["sign"] = createSimpleSign($this->_token, is_string($data) ? $data : json_encode($data));
		}
		return $this->response($result, "json", $code);
	}
	public function error($msg, $code = 400)
	{
		$result["error"] = \think\facade\Lang::get($msg);
		$result["code"] = $code;
		return $this->response($result, "json", 200);
	}
	protected function response($data, $type = "json", $code = 200)
	{
		return \think\Response::create($data, $type)->code($code);
	}
	protected function validate(array $data, $validate, array $message = [], bool $batch = false)
	{
		if (is_array($validate)) {
			$v = new \think\Validate();
			$v->rule($validate);
		} else {
			if (strpos($validate, ".")) {
				list($validate, $scene) = explode(".", $validate);
			}
			$class = false !== strpos($validate, "\\") ? $validate : $this->app->parseClass("validate", $validate);
			$v = new $class();
			if (!empty($scene)) {
				$v->scene($scene);
			}
		}
		$v->message($message);
		if ($batch || $this->batchValidate) {
			$v->batch(true);
		}
		return $v->failException(true)->check($data);
	}
	public function getCityByLongLat($lng, $lat)
	{
		$dis = ["uniacid" => 666];
		$config_model = new \app\massage\model\Config();
		$config = $config_model->dataInfo($dis);
		$map_secret = !empty($config["map_secret"]) ? $config["map_secret"] : "bViFglag7C7G7QlZ1MglFyvh40yK1Tir";
		$URL = "https://apis.map.qq.com/ws/geocoder/v1/?location={$lat},{$lng}&key={$map_secret}";
		$data = longbingCurl($URL, []);
		$data = json_decode($data, true);
		$data = !empty($data["result"]["address_component"]["city"]) ? $data["result"]["address_component"]["city"] : "";
		return $data;
	}
	public function getCityByLongLatArr($lng, $lat, $uniacid)
	{
		$key = "getCityByLongLatArr" . round($lng, 4) . "-" . round($lat, 4);
		$value = getCache($key, $uniacid);
		if (!empty($value)) {
			return $value;
		}
		$dis = ["uniacid" => $uniacid];
		$config_model = new \app\massage\model\Config();
		$config = $config_model->dataInfo($dis);
		$map_secret = !empty($config["map_secret"]) ? $config["map_secret"] : "bViFglag7C7G7QlZ1MglFyvh40yK1Tir";
		$URL = "https://apis.map.qq.com/ws/geocoder/v1/?location={$lat},{$lng}&key={$map_secret}";
		$data = longbingCurl($URL, []);
		$data = @json_decode($data, true);
		$arr = [];
		if (!empty($data["result"]["address_component"]["city"])) {
			$arr[] = $data["result"]["address_component"]["city"];
		}
		if (!empty($data["result"]["address_component"]["district"])) {
			$arr[] = $data["result"]["address_component"]["district"];
		}
		if (!empty($arr)) {
			setCache($key, $value, 86400, $uniacid);
		}
		return $arr;
	}
}
