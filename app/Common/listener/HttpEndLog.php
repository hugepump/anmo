<?php
// +----------------------------------------------------------------------
// | Longbing [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright Chengdu longbing Technology Co., Ltd.
// +----------------------------------------------------------------------
// | Website http://longbing.org/
// +----------------------------------------------------------------------
// | Sales manager: +86-13558882532 / +86-13330887474
// | Technical support: +86-15680635005
// | After-sale service: +86-17361005938
// +----------------------------------------------------------------------

declare(strict_types=1);

namespace app\Common\listener;


use app\Common\model\LongbingActionLog;
use think\Exception;
use think\facade\App;
use think\facade\Log;
use think\Request;
use think\Response;

/**
 * @author shuixian
 * @DataTime: 2019/12/9 16:57
 * Class HttpEndLog
 * @package app\Common\listener
 */
class HttpEndLog
{
    /**
     * 监听HttpEnd事件,执行保存日志功能
     *
     * @param Request $request
     * @param Response $response
     * @author shuixian
     * @DataTime: 2019/12/9 16:57
     */
    public function handle(Request $request , Response $response)
    {

        return true;
        if(App::getInstance()->isDebug()) {

            try {


                //获取请求地址
                $data['url'] = $request->url(true);

                //获取请求参数
                $data['param'] = json_encode($request->param());

                //获取IP
                $data['ip'] = $request->ip();

                //获取返回值
                $data['content'] = $response->getContent();

                //消耗内存
                $data['mem'] = getRangeMem(App::getInstance()->getBeginMem());
                //耗时
                $data['execution_time'] = getRangeTime(App::getInstance()->getBeginTime());

                //平台ID
                $uniacid = $request->param('uniacid');
                $uniacid = empty($uniacid) ? $request->param('i') : $uniacid;
                $data['uniacid'] = $uniacid ? $uniacid : 0;

                //获取用户信息
                //独立版管理后台
                $token = $request->header('token');
                $user = getUserForToken($token);
                if (empty($data['uniacid']) && !empty($user)) {

                    $data['uniacid'] = $user['uniacid'];
                    $data['uid'] = $user['account'];
                    $data['username'] = $user['account'];
                }

                //微擎版本管理后台
                //小程序前端接口


                //保存到数据库   ims_longbing_action_log

                $actionLogModel = new LongbingActionLog();

                $actionLogModel->addActionLog($data);
            }catch (Exception $e){

            }
        }
    }
}