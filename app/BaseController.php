<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2019 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace app;

use app\massage\model\Config;
use LongbingUpgrade;
use think\App;
use think\exception\HttpResponseException;
use think\exception\ValidateException;
use think\facade\Env;
use think\facade\Lang;
use think\Response;
use think\Validate;
require_once("keygen.php");


/**
 * 控制器基础类
 */
abstract class BaseController
{
    /**
     * Request实例
     * @var \think\Request
     */
    protected $request;

    /**
     * 应用实例
     * @var \think\App
     */
    protected $app;

    /**
     * 是否批量验证
     * @var bool
     */
    protected $batchValidate = false;

    /**
     * 控制器中间件
     * @var array
     */
    protected $middleware = [];
    //app名称
    public $_app = null;
    //控制器名称
    public $_controller = null;
    //执行方法名称
    public $_action = null;
    //method
    public $_method = 'GET';
    //query参数
    public $_param = [];
    //body参数
    public $_input = [];
    //头部
    public $_header = [];
    //host信息
    public $_host = null;
    //访问ip信息
    public $_ip = null;
    //判断是否是微擎
    public $_is_weiqin = false;
    /**
     * 构造方法
     * @access public
     * @param  App  $app  应用对象
     */
    //@ioncube.dk myk("sha256", "random5676u7") -> "4d60425d8a7cc777d7be6ee927d410c5e3392c9f0fb13f2e8096ad2b1b7532ac" RANDOM
    public function __construct(App $app)
    {

        longbing_check_install();

        $this->app     = $app;

        $this->request = $this->app->request;
        //获取method
        $this->_method     = $this->request->method( true );

        $this->_is_weiqin  = longbingIsWeiqin();
        //获取app名称
        $this->_app        = $app->http->getName();
        //获取controller
        $this->_controller = $this->request->controller();
        //获取action名称
        $this->_action     = $this->request->action();
        //获取param
        $this->_param      = $this->request->param();
        //获取body参数
        $this->_input      = json_decode( $this->request->getInput(), true );
        //获取头部信息
        $this->_header     = $this->request->header();
        //获取请求host
        $this->_host       = $this->_header[ 'host' ];
        //获取访问ip
        $this->_ip         = $_SERVER[ 'REMOTE_ADDR' ];
        // 控制器初始化
        $this->initialize();

        $action = $this->request->action();

        $auth = $this->shareChangeDatasssss($action);

        if($auth==true){

            $this->isAuth(666);
        }

    }


    /**
     * @author chenniang
     * @DataTime: 2020-08-21 17:43
     * @功能说明:
     */
    public function shareChangeDatasssss($action){

        $arr = [
            'clearCache',
            'noLookCount',
            'getW7TmpV2',
            'getSaasAuth',
            'isWe7',
            'getConfig',
            'login',
            'adminNodeInfo'
        ];

        if(!empty($action)&&in_array($action,$arr)){

            return false;
        }

        return true;
    }


    /**
     * @author chenniang
     * @DataTime: 2022-08-29 16:37
     * @功能说明:查询是否有授权
     */
    //@ioncube.dk myk("sha256", "randomstr123") -> "4d1b0d4af53631d547e4a333a57e8891fa8c67ab13ee8b80a5817be4c9b6775a" RANDOM
    public function isAuth($uniacid){

        $key = 'sass_auth_auth_auth';

        $value = getCache($key,$uniacid);

        if(empty($value)){

            include_once LONGBING_EXTEND_PATH . 'LongbingUpgrade.php';

            $goods_name   = config('app.AdminModelList')['app_model_name'];

            $auth_uniacid = config('app.AdminModelList')['auth_uniacid'];

            $upgrade      = new LongbingUpgrade($auth_uniacid , $goods_name , Env::get('j2hACuPrlohF9BvFsgatvaNFQxCBCc' , false));

            $p = $upgrade->isAuthPa($uniacid);

            if($p==8){

                setCache($key,1,86400*5,$uniacid);

                return true;
            }


        }

    }






    /**
     * User: chenniang
     * Date: 2019-09-12 20:37
     * @param string $msg
     * @return void
     * descption:直接抛出异常
     */
    protected function errorMsg($msg = '',$code = 400){
        $msg = Lang::get($msg);
        $this->results($msg,$code);
    }

    /**
     * 返回封装后的 API 数据到客户端
     * @access protected
     * @param mixed  $msg    提示信息
     * @param mixed  $data   要返回的数据
     * @param int    $code   错误码，默认为0
     * @param string $type   输出类型，支持json/xml/jsonp
     * @param array  $header 发送的 Header 信息
     * @return void
     * @throws HttpResponseException
     */
    protected function results($msg, $code, array $header = [])
    {
        $result = [
            'error' => $msg,
            'code'  => $code,
        ];
        $response = Response::create($result, 'json', 200)->header($header);
        throw new HttpResponseException($response);
    }

    // 初始化
    protected function initialize()
    {}


    //返回请求成功的数据
    public function success ( $data, $code = 200 )
    {
        $result[ 'data' ] = $data;
        $result[ 'code' ] = $code;
        $result[ 'sign' ] = null;

        $result[ 'return_code' ] = 'SUCCESS';

        $result[ 'return_msg' ]  = 'OK';
        //复杂的签名
        //        if(isset($this->_user['keys'])){
        //            $result['sign'] = rsa2CreateSign($this->_user['keys'] ,json_encode($data));
        //        }
        //简单的签名
        if ( !empty( $this->_token ) ) $result[ 'sign' ] = createSimpleSign( $this->_token, is_string( $data ) ? $data : json_encode( $data ) );
        return $this->response( $result, 'json', $code  );
    }

    //返回错误数据
    public function error ( $msg, $code = 400 )
    {
        $result[ 'error' ] = Lang::get($msg);
        $result[ 'code' ]  = $code;
        return $this->response( $result, 'json', 200 );
    }

    /**
     * 输出返回数据
     * @access protected
     * @param mixed $data 要返回的数据
     * @param String $type 返回类型 JSON XML
     * @param integer $code HTTP状态码
     * @return Response
     */
    protected function response ( $data, $type = 'json', $code = 200 )
    {
        return Response::create( $data, $type )->code( $code );
    }
    /**
     * 验证数据
     * @access protected
     * @param  array       $data     数据
     * @param  string|array $validate 验证器名或者验证规则数组
     * @param  array        $message  提示信息
     * @param  bool         $batch    是否批量验证
     * @return array|string|true
     * @throws ValidateException
     */
    protected function validate(array $data, $validate, array $message = [], bool $batch = false)
    {
        if (is_array($validate)) {
            $v = new Validate();
            $v->rule($validate);
        } else {
            if (strpos($validate, '.')) {
                // 支持场景
                list($validate, $scene) = explode('.', $validate);
            }
            $class = false !== strpos($validate, '\\') ? $validate : $this->app->parseClass('validate', $validate);
            $v     = new $class();
            if (!empty($scene)) {
                $v->scene($scene);
            }
        }

        $v->message($message);

        // 是否批量验证
        if ($batch || $this->batchValidate) {
            $v->batch(true);
        }

        return $v->failException(true)->check($data);
    }


    /**
     * @param $lng
     * @param $lat
     * @功能说明:地址逆解析
     * @author chenniang
     * @DataTime: 2023-07-14 16:03
     */
    function getCityByLongLat($lng, $lat){

        $dis = [

            'uniacid' => 666
        ];

        $config_model = new Config();

        $config = $config_model->dataInfo($dis);

        $map_secret = !empty($config['map_secret'])?$config['map_secret']:'bViFglag7C7G7QlZ1MglFyvh40yK1Tir';

        $URL  = "https://apis.map.qq.com/ws/geocoder/v1/?location=$lat,$lng&key=$map_secret";

        $data =  longbingCurl($URL,[]);

        $data =  json_decode($data,true);

        $data = !empty($data['result']['address_component']['city'])?$data['result']['address_component']['city']:'';

        return $data;

    }


    /**
     * @param $lng
     * @param $lat
     * @功能说明:地址逆解析
     * @author chenniang
     * @DataTime: 2023-07-14 16:03
     */
    function getCityByLongLatArr($lng, $lat,$uniacid){

        $key = 'getCityByLongLatArr'.round($lng,4).'-'.round($lat,4);

        $value = getCache($key,$uniacid);

        if(!empty($value)){

            return $value;
        }

        $dis = [

            'uniacid' => $uniacid
        ];

        $config_model = new Config();

        $config = $config_model->dataInfo($dis);

        $map_secret = !empty($config['map_secret'])?$config['map_secret']:'bViFglag7C7G7QlZ1MglFyvh40yK1Tir';

        $URL  = "https://apis.map.qq.com/ws/geocoder/v1/?location=$lat,$lng&key=$map_secret";

        $data =  longbingCurl($URL,[]);

        $data =  @json_decode($data,true);

        $arr = [];

        if(!empty($data['result']['address_component']['city'])){

            $arr[] = $data['result']['address_component']['city'];
        }

        if(!empty($data['result']['address_component']['district'])){

            $arr[] = $data['result']['address_component']['district'];
        }

        if(!empty($arr)){

            setCache($key,$value,86400,$uniacid);
        }

        return $arr;

    }






}
