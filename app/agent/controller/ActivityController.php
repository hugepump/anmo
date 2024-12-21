<?php


namespace app\agent\controller;

use app\agent\service\AdminUserService;
use think\App;
use app\agent\model\Cardauth2ActivityModel;
use app\AgentRest;
use think\facade\Db;

class ActivityController extends AgentRest
{
    public function __construct ( App $app ){
        parent::__construct( $app );
        if ($this->_user['role_name'] != 'admin') {
            echo json_encode(['code' => 401, 'error' => lang('Permission denied')]);
            exit;
        }
    }

    public function list()
    {
        $param = $this->_param;
        $m_activity_auth2 = new Cardauth2ActivityModel();

        //By.jingshuixian   2020年4月21日15:13:50
        //区分行业版数据
        if($this->_is_weiqin){
            $app_model_name = APP_MODEL_NAME;
            $list = $m_activity_auth2->alias('a')
                ->field(['a.id', 'a.modular_id', 'a. create_time', 'a.sign',  'c.mini_app_name'])
                ->join('longbing_card_config c', 'a.modular_id = c.uniacid')
                ->join('account' , 'a.modular_id = account.uniacid')
                ->join('wxapp_versions v' , 'a.modular_id = v.uniacid')
                ->where([['a.status', '=', 1] , ['account.type', '=', 4]  ,['account.isdeleted', '=', 0] ,  ['v.modules', 'like', "%{$app_model_name}%"]])
                ->group('a.modular_id')
                ->paginate(['list_rows' => $param['page_count'] ? $param['page_count'] : 10, 'page' => $param['page'] ? $param['page'] : 1])->toArray();



        }else{


            $list = $m_activity_auth2->alias('a')
                ->field(['a.id', 'a.modular_id', 'a. create_time', 'a.sign',  'c.mini_app_name'])
                ->join('longbing_card_config c', 'a.modular_id = c.uniacid')
                ->where([['a.status', '=', 1]])
                ->paginate(['list_rows' => $param['page_count'] ? $param['page_count'] : 10, 'page' => $param['page'] ? $param['page'] : 1])->toArray();


        }




        $wxapp_map = [];
        $wxapp = Db::name('account_wxapp')->field(['uniacid', 'name'])->select();
        foreach ($wxapp as $item) {
            $wxapp_map[$item['uniacid']] = $item['name'];
        }

        foreach ($list['data'] as $k => $item) {
            $list['data'][$k]['name'] = $wxapp_map[$item['modular_id']] ?? $item['mini_app_name'];
            unset($list['data'][$k]['mini_app_name']);
        }


        $list['total_activity_number'] = AdminUserService::getSassNum('activity',$this->_uniacid);

        $list['total_activity_used']   = (int)$m_activity_auth2->where([['uniacid','in',$this->_uniacid_arr]])->sum('count');

        return $this->success($list);
    }


    public function create()
    {
        $data = $this->_input;

        if (!isset($data['modular_id'])) {
            return $this->success('参数错误');
        }

        $time = time();
        $auth_activity = Cardauth2ActivityModel::where([['modular_id', '=', $data['modular_id']]])->findOrEmpty();

        if (!$auth_activity->isEmpty()) {
            return $this->error('已存在此小程序');
        }

        $total_activity_number = AdminUserService::getSassNum('activity',$this->_uniacid);

        $total_activity_used   = (int)$auth_activity->where([['uniacid','in',$this->_uniacid_arr]])->sum('count');
        $remain = $total_activity_number - $total_activity_used;
        if ($remain <= 0) {
            return $this->error('分配的数量超过可用的总数');
        }

        $rst = $auth_activity->save([
            'modular_id'  => $data[ 'modular_id' ],
            'create_time' => $time,
            'update_time' => $time,
            'sign'        => intval( $time + ( 366 * 24 * 60 * 60 ) ),
            'count'       => 1,
            'uniacid'     => $this->_uniacid,
        ]);

        if ($rst) {
            return $this->success('success');
        }

        return $this->error('fail');
    }

//51062319931013082X
    public function extendedOneYear ()
    {
        $data = $this->_input;
        if (!isset($data['modular_id'])) {
            return $this->success('参数错误');
        }

        $time = time();
        $auth_activity = Cardauth2ActivityModel::where([['modular_id', '=', $data['modular_id']]])->findOrEmpty();

        if ($auth_activity->isEmpty()) {
            return $this->error('小程序不存在');
        }

        $total_activity_number = AdminUserService::getSassNum('activity',$this->_uniacid);

        $total_activity_used   = (int)$auth_activity->where([['uniacid','in',$this->_uniacid_arr]])->sum('count');
        $remain = $total_activity_number - $total_activity_used;
        if ($remain <= 0) {
            return $this->error('分配的数量超过可用的总数');
        }

        $rst = $auth_activity->save([
            'sign'  => $auth_activity[ 'sign' ] > $time ?  ($auth_activity[ 'sign' ] + ( 366 * 24 * 60 * 60 )) : ( $time + ( 366 * 24 * 60 * 60 ) ),
            'count' => $auth_activity['count'] + 1,
            'update_time' => $time,
        ]);


        if ($rst) {
            return $this->success('success');
        }


        return $this->error('fail');
    }

}