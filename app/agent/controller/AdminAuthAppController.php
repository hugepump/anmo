<?php
namespace app\agent\controller;

use app\admin\info\PermissionAdmin;
use app\agent\model\Cardauth2ActivityModel;
use app\agent\model\Cardauth2AuthAppModel as Cardauth2Model;
use think\App;
use app\AgentRest;
use app\agent\service\AdminUserService;
use think\facade\Db;

/**
 * @author shuixian
 * @DataTime: 2020/1/3 9:44
 * Class AdminAuthAppController
 * @package app\agent\controller
 */
class AdminAuthAppController extends AgentRest
{

    private $auth_app_name = '' ;

    public function __construct ( App $app ){
        parent::__construct( $app );

        //自动配置APPNAME
        if(isset($this->_param['app_name']))  $this->auth_app_name = $this->_param['app_name'] ;

        $this->error(lang('app_name is empty')) ;

        if ($this->_user['role_name'] != 'admin') {
            echo json_encode(['code' => 401, 'error' => lang('Permission denied')]);
            exit;
        }
    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-01-03 13:46
     * @功能说明:授权列表
     */
    public function list(){
        $param = $this->_param;
        if (!isset($param['app_name'])) {
            return $this->error('参数错误');
        }


        //By.jingshuixian   2020年4月21日15:13:50
        //区分行业版数据

        //获取列表
        if($this->_is_weiqin){

            $app_model_name = APP_MODEL_NAME;
            $list = Cardauth2Model
                ::alias('a')
                ->field(['a.id', 'a.modular_id', 'a. create_time', 'a.sign',  'c.mini_app_name'])
                ->join('longbing_card_config c', 'a.modular_id = c.uniacid')

                ->join('account' , 'a.modular_id = account.uniacid')
                ->join('wxapp_versions v' , 'a.modular_id = v.uniacid')

                ->where([['a.status', '=', 1],['app_name','like',"%". $param['app_name'] ."%"]  , ['account.type', '=', 4]  ,['account.isdeleted', '=', 0] ,  ['v.modules', 'like', "%{$app_model_name}%"]   ])
                ->group('a.id')
                ->paginate(['list_rows' => $param['page_count'] ? $param['page_count'] : 10, 'page' => $param['page'] ? $param['page'] : 1])
                ->toArray();

        }else{

            $list = Cardauth2Model
                ::alias('a')
                ->field(['a.id', 'a.modular_id', 'a. create_time', 'a.sign',  'c.mini_app_name'])
                ->join('longbing_card_config c', 'a.modular_id = c.uniacid')
                ->where([['a.status', '=', 1],['app_name','=',$param['app_name']]])
                ->group('a.id')
                ->paginate(['list_rows' => $param['page_count'] ? $param['page_count'] : 10, 'page' => $param['page'] ? $param['page'] : 1])
                ->toArray();

        }

        $wxapp_map = [];
        $wxapp     = Db::name('account_wxapp')->field(['uniacid', 'name'])->select();
        foreach ($wxapp as $item) {
            $wxapp_map[$item['uniacid']] = $item['name'];
        }
        //小程序名称
        foreach ($list['data'] as $k => $item) {
            $list['data'][$k]['name'] = $wxapp_map[$item['modular_id']] ?? $item['mini_app_name'];
            unset($list['data'][$k]['mini_app_name']);
        }
        //总的授权数量
        $list['total_number'] = AdminUserService::getSassNum($param['app_name'],$this->_uniacid);
        //已经使用数量
        $list['total_used']   = Cardauth2Model::where([['uniacid','in',$this->_uniacid_arr],['app_name','=',$param['app_name']]])->sum('count');

        return $this->success($list);

    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-01-03 13:31
     * @功能说明:创建小程序授权
     */
    public function create()
    {
        $data = $this->_input;
        if (!isset($data['modular_id'])||!isset($data['app_name'])) {
            return $this->error('参数错误');
        }
        //获取代理端授权数量
        $num  = AdminUserService::getSassNum($data['app_name'],$this->_uniacid);


        $time = time();
        //查询是否有该小程序下的模块
        $auth = Cardauth2Model::where([['modular_id', '=', $data['modular_id']],['app_name','=',$data['app_name']]])->findOrEmpty();

        if (!$auth->isEmpty()) {
            return $this->error('已存在此小程序');
        }
        //已经使用
        $total_num = Cardauth2Model::where([['uniacid','in',$this->_uniacid_arr],['app_name','=',$data['app_name']]])->sum('count');
        //剩余数量
        $remain    = $num - $total_num;
        if ($remain <= 0) {
            return $this->error('分配的数量超过可用的总数');
        }
        //添加新的授权
        $rst = $auth->save([
            'modular_id'  => $data[ 'modular_id' ],
            'create_time' => $time,
            'update_time' => $time,
            'sign'        => intval( $time + ( 366 * 24 * 60 * 60 ) ),
            'count'       => 1,
            'uniacid'     => $this->_uniacid,
            'app_name'    => $data['app_name'],
            'sign_data'   =>'ndvjnfjvnjnv'.$time
        ]);
        if ($rst) {
            return $this->success('success');
        }
        return $this->error('fail');
    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-01-03 13:42
     * @功能说明:增加小程序授权一年
     */
    public function extendedOneYear ()
    {
        $data = $this->_input;
        //参数验证
        if (!isset($data['modular_id'])||!isset($data['app_name'])) {
            return $this->error('参数错误');
        }
        $time = time();
        $auth = Cardauth2Model::where([['modular_id', '=', $data['modular_id']],['app_name','=',$data['app_name']]])->findOrEmpty();
        if ($auth->isEmpty()) {
            return $this->error('小程序不存在');
        }
        //获取授权数量
        $num  = AdminUserService::getSassNum($data['app_name'],$this->_uniacid);
        //获取已使用数量
        $total_used = Cardauth2Model::where([['uniacid','in',$this->_uniacid_arr],['app_name','=',$data['app_name']]])->sum('count');

        $remain     = $num - $total_used;
        //判断剩余数量
        if ($remain <= 0) {
            return $this->error('分配的数量超过可用的总数');
        }
        //修改授权时间|增加使用数量
        $rst = $auth->save([
            'sign'  => $auth[ 'sign' ] > $time ?  ($auth[ 'sign' ] + ( 366 * 24 * 60 * 60 )) : ( $time + ( 366 * 24 * 60 * 60 ) ),
            'count' => $auth['count'] + 1,
            'update_time' => $time,
        ]);

        if ($rst) {
            return $this->success('success');
        }

        return $this->error('fail');
    }

}