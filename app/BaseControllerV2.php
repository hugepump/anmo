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
use think\Request;
/**
 * 控制器基础类
 */
abstract class BaseControllerV2
{
//唯一app标示
    public $_uniacid = 0;
    //query参数
    public $_param = [];
    //头部token
    public $_token = null;
    //
    public $_autograph = null;
    //请求对象 $request 兼容处理,即将废弃
    protected $request;
    protected $_request;
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->_request = $this->request;
        $this->_param = $this->request->param();
        $this->_token = $this->request->header('token');
        $this->_autograph = $this->request->header('autograph');
        $this->_uniacid = intval( $this->request->param('i') ) ;
        $this->_uniacid = $this->_uniacid ? $this->_uniacid  :  intval( $this->request->param('uniacid') );
        //兼容微擎新版本
        if(empty($this->_uniacid)&&longbingIsWeiqin()){
            global $_GPC, $_W;
            $this->_uniacid = $_W[ 'uniacid' ];
        }
        //独立版拿不到uniacid
        if(empty($this->_uniacid)){
            $user_info = getUserForToken($this->_token);
            $this->_uniacid = $user_info['uniacid'];
        }
    }
    /**
     * 获取用户ID
     *
     * @return int
     * @author shuixian
     * @DataTime: 2019/12/24 12:45
     */
    protected function getUserId()
    {
        $value = getCache($this->_autograph, $this->_uniacid);
        if ($value === false) {
            return 0;
        }
        return $value['id'];
    }
}
