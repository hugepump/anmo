<?php


namespace app\agent\controller;


use app\agent\model\AdminModel;
use app\agent\model\Cardauth2ConfigModel;
use app\BaseController;
use think\facade\Lang;
use think\Response;

class AdminAuthController extends  BaseController
{
    //登陆
    public function auth()
    {
        $input = json_decode( $this->request->getInput(), true );
        $admin = AdminModel::where([
            ['account', '=', $input['account'] ?? ''],
            ['deleted', '=', 0],
            ['status', '=', 1],
        ])->findOrEmpty();

        if ($admin->isEmpty()) {
            return $this->error('用户不存在', 400);
        }

        //判断密码是否正确
        if (!checkPasswd($input['passwd'], $admin['offset'], $admin['passwd'])) {
            return $this->error('密码错误', 400);
        }
        //返回数据
        $user = [
            'admin_id' => $admin->admin_id,
            'level' => $admin->level,
            'account'=> $admin->account,
            'role' => $admin->role->role_name ?? 'user',
            'role_name' => $admin->role->role_name ?? 'user',
            'uniacid' => $admin->appAdmin->modular_id ?? -1,
        ];

        if ($user['uniacid'] == -1) {
            return $this->error("用户没有绑定小程序， 请联系代理端超级管理员");
        }
        if (isset($input['isAgent']) && $input['isAgent'] == true && $user['role'] != 'admin') {
            return $this->error('普通用户禁止访问');
        }

        if (isset($input['isAgent']) && $input['isAgent'] == false && $user['role'] == 'admin') {
            return $this->error('超级管理员禁止访问， 请创建子管理员账号并绑定小程序登录');
        }

        $result['user'] = $user;
        $result['token'] = createToken();
        if (empty($result['token'])) {
            return $this->error('系统错误', 400);
        }

        //添加缓存数据
        setUserForToken($result['token'], $user, 99999999);
        return $this->success($result, 200);
    }


    //注销
    public function unAuth()
    {
        $header = $this->request->header();
        $token = $header['token'] ?? null;
        if ($token == null || !getUserForToken($token)) {
            $this->error('用户未登录');
        }

        //删除缓存
        delUserForToken($token);
        //返回数据
        return $this->success(true);
    }

    //获取账户状态
    public function AuthStatus(){
        $header = $this->request->header();
        $token = $header['token'] ?? null;
        $user = getUserForToken($token);

        $resData = longbing_auth_status($user['uniacid']);
        return $this->success($resData);
    }

    public function isWe7()
    {
        $is_we7 = defined('IS_WEIQIN');

        return $this->success($is_we7);
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