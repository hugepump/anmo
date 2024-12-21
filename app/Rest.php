<?php
declare ( strict_types = 1 );

namespace app;

use app\BaseController;
use think\App;
use think\exception\ValidateException;
use think\Validate;
use think\Response;

/**
 * 控制器基础类
 */
abstract class Rest extends BaseController
{
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
    //头部token
    public $_token = null;
    //语言信息
    public $_lang = 'zh-cn';
    //角色
    public $_role = 'guest';
    //host信息
    public $_host = null;
    //访问ip信息
    public $_ip = null;
    //用户信息
    public $_user = null;
    //唯一app标示
    public $_uniacid = '7777';
    //定义检查中间件
    protected $middleware = [ 'app\middleware\CheckInput' ];

    public function __construct ( App $app )
    {
        dump(1);exit;

        parent::__construct( $app );
        if ( defined( 'IS_WEIQIN' ) ) {
            global $_GPC, $_W;
            $this->_uniacid = $_W[ 'uniacid' ];
        }


        //获取app名称
        $this->_app = $this->request->app();
        //获取controller
        $this->_controller = $this->request->controller();
        //获取action名称
        $this->_action = $this->request->action();
        //获取method
        $this->_method = $this->request->method( true );
        //获取param
        $this->_param = $this->request->param();
        //获取body参数
        $this->_input = json_decode( $this->request->getInput(), true );
        //获取头部信息
        $this->_header = $this->request->header();
        //		//判断是否为json
        //		if(!isset($this->request->header()['Content-Type'])) {
        //			$this->_header['Content-Type'] = 'application/json';
        //			$this->app->request->withHeader($this->_header);
        //		}

        //获取token
        if ( isset( $this->_header[ 'token' ] ) ) $this->_token = $this->_header[ 'token' ];
        //语言
        if ( isset( $this->_header[ 'lang' ] ) ) $this->_token = $this->_header[ 'lang' ];
        //获取请求host
        $this->_host = $this->_header[ 'host' ];
        //获取访问ip
        $this->_ip = $_SERVER[ 'REMOTE_ADDR' ];
        // 控制器初始化
        $this->initialize();

        //获取用户信息
        if ( !empty( $this->_token ) ) $this->_user = getUserForToken( $this->_token );
        //获取角色名称
        if ( !empty( $this->_user ) && isset( $this->_user[ 'role_name' ] ) ) $this->_role = $this->_user[ 'role_name' ];
        //数据检查
        //		if (!empty($this->_input)) {
        //          $schemaMethod =ucfirst($this->_app) . ucfirst($this->_controller) . ucfirst($this->_action) . 'Request';
        //          if (jsonSchemaExist($schemaMethod)) {
        //              $result = jsonSchemaValidate($schemaMethod, $this->_input);
        //              if ($result) {
        //                  $this->_validate = true;
        //              } else {
        //                  $this->_validate = false;
        //                  exit();
        //              }
        //          } else {
        //              // 暂不处理
        //              todo('Schema文件找不到！');
        //          }
        //      }
        //rbac
        //		if(!in_array($this->_app. $this->_controller . $this->_action, ['adminAuthauth']) && in_array($this->_role, ['guest'])){
        //			$result['error'] = 'permissions is not enough.';
        //			echo json_encode($result);exit;
        //		}
    }

    //返回请求成功的数据
    public function success ( $data, $code = 200 )
    {
        $result[ 'data' ] = $data;
        $result[ 'code' ] = $code;
        $result[ 'sign' ] = null;
        //复杂的签名
        //		if(isset($this->_user['keys'])){
        //			$result['sign'] = rsa2CreateSign($this->_user['keys'] ,json_encode($data));
        //		}
        //简单的签名
        if ( !empty( $this->_token ) ) $result[ 'sign' ] = createSimpleSign( $this->_token, is_string( $data ) ? $data : json_encode( $data ) );
        return $this->response( $result, 'json', $code  );
    }

    //返回错误数据
    public function error ( $msg, $code = 400 )
    {
        $result[ 'error' ] = $msg;
        $result[ 'code' ]  = $code;
        return $this->response( $result, 'json', $code );
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
     * REST 调用
     * @access public
     * @param string $method 方法名
     * @return mixed
     * @throws \Exception
     */
    public function _empty ( $method )
    {
        if ( method_exists( $this, $method . '_' . $this->method . '_' . $this->type ) ) {
            // RESTFul方法支持
            $fun = $method . '_' . $this->method . '_' . $this->type;
        }
        elseif ( $this->method == $this->restDefaultMethod && method_exists( $this, $method . '_' . $this->type ) ) {
            $fun = $method . '_' . $this->type;
        }
        elseif ( $this->type == $this->restDefaultType && method_exists( $this, $method . '_' . $this->method ) ) {
            $fun = $method . '_' . $this->method;
        }
        if ( isset( $fun ) ) {
            return App::invokeMethod( [
                $this,
                $fun
            ]
            );
        }
        else {
            // 抛出异常
            throw new \Exception( 'error action :' . $method );
        }
    }
}
