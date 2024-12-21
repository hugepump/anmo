<?php
namespace app\agent\controller;

use think\App;
use app\AdminRest;
use app\agent\model\Cardauth2DefaultModel;
use app\AgentRest;
use think\Validate;

class ConfigDefault extends AgentRest
{
    public function __construct ( App $app ){
        parent::__construct( $app );
        if ($this->_user['role_name'] != 'admin') {
            echo json_encode(['code' => 401, 'error' => lang('Permission denied')]);
            exit;
        }
    }
    public function getOne()
    {
        $default = Cardauth2DefaultModel::order('id', 'desc')->limit(1)->select();

        if (!isset($default[0])) {
            $default = new Cardauth2DefaultModel();
            $default->uniacid = $this->_uniacid;
            $default->card_number = 0;
            $default->send_switch = 0;
            $default->save();
            return $this->success($default);
        }
        return $this->success($default[0]->toArray());
    }


    public function update()
    {

        $input = $this->_input;
        $validate = new Validate();
        $validate->rule([
            'card_number|名片数量'  => 'require|number|egt:0',
            'send_switch|短信群发' => 'require|number|in:0,1',
        ]);

        if (!$validate->check($input)) {
            return $this->error($validate->getError());
        }


        $default = Cardauth2DefaultModel::order('id', 'desc')->limit(1)->select();
        if (!$default) {
            $default->card_number = $input['card_number'] ;
            $default->send_switch = $input['send_switch'] ;
            $default->save();
        }
        $default[0]->card_number = $input['card_number'] ;
        $default[0]->send_switch = $input['send_switch'] ;
        $default[0]->save();

        return $this->success('success');
    }
}