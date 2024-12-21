<?php
namespace app\agent\controller;

use think\App;
use app\AdminRest;
use app\AgentRest;
use think\facade\Db;
use think\Validate;

class MessageConfigController extends AgentRest
{
    public function __construct ( App $app ){
        parent::__construct( $app );
        if ($this->_user['role_name'] != 'admin') {
            echo json_encode(['code' => 401, 'error' => lang('Permission denied')]);
            exit;
        }
    }
    public function delMessageByDay()
    {
        if (!isset($this->_input['modular_id']) || !isset($this->_input['days'])) {
            return $this->success('success');
        }

        $validate = new Validate();
        $validate->rule([
            'days|清除时间' => 'integer',
        ]);

        if (!$validate->check($this->_input)) {
            return $this->error($validate->getError());
        }

        $modular_id = $this->_input['modular_id'];
        $days = $this->_input['days'];

        if ($days > 0) {
            $beginTime = mktime(0, 0, 0, date('m'), date('d') - $days, date('Y'));
        } else {
            $beginTime = 99999999999;
        }

        $rst = Db::name('longbing_card_message')->where([['uniacid', '=', $modular_id], ['create_time', '<', $beginTime]])->delete(true);
        return $this->success($rst);
    }


}