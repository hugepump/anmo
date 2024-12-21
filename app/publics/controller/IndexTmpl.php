<?php
namespace app\publics\controller;
use app\ApiRest;

use app\publics\model\TmplConfig;
use longbingcore\wxcore\WxTmpl;
use think\App;


class IndexTmpl extends ApiRest
{

    protected $model;
    public function __construct(App $app) {
        parent::__construct($app);

        $this->model = new TmplConfig();
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2019-12-26 15:40
     * @功能说明: 获取模版消息
     */
    public function getTmplId(){
        $input = $this->_input;
        $dis[] = ['uniacid','=',$this->_uniacid];
        $dis[] = ['tmpl_id','<>',0];
        $dis[] = ['tmpl_name','in',$input['tmpl_name']];
        $data  = $this->model->tmplIdList($dis);
        return $this->success($data);
    }






}
