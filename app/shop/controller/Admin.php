<?php
namespace app\shop\controller;
use app\AdminRest;
use app\BaseController;
use think\App;
use app\shop\model\Admin as Model;

use think\facade\Db;
use think\facade\Lang;
use think\Response;

class Admin extends BaseController
{


    protected $model;

    protected $config_model;



    public function __construct(App $app) {

        parent::__construct($app);

        $this->model = new Model();


    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-11 13:53
     * @功能说明:登陆
     */
    public function login(){

//        $input = $this->_input;

        initLogin();

        $input = json_decode( $this->request->getInput(), true );

        //dump($input);exit;

        $dis = [

           // 'uniacid' => $this->_uniacid,

            'username'=> $input['username']
        ];

        $data = $this->model->dataInfo($dis);

        if(empty($data)){

            return $this->error('该用户不存在', 400);

        }

        if($data['passwd']!=checkPass($input['passwd'])){


            return $this->error('密码错误', 400);
        }

        $result['user'] = $data;

        $result['token'] = uuid();

        if (empty($result['token'])) {

            return $this->error('系统错误', 400);
        }
        //添加缓存数据
        setUserForToken($result['token'], $data, 99999999);

        return $this->success($result);

    }

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














}
