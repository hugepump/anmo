<?php
namespace app\publics\controller;
use app\card\model\User;
use app\publics\model\TmplConfig;
use app\AdminRest;
use app\publics\service\PublicsService;
use longbingcore\permissions\AdminMenu;
use longbingcore\wxcore\WxSetting;
use longbingcore\wxcore\WxTmpl;
use think\App;


class AdminTmpl extends AdminRest
{

    protected $model;
    public function __construct(App $app) {
        parent::__construct($app);

        $this->model = new TmplConfig();
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2019-12-26 15:40
     * @功能说明: 添加模版消息 并返回模版id
     */
    public function getTmplId(){

        $input = $this->_input;
        //实列化模版订阅通知库类；
        $service_model = new WxTmpl($this->_uniacid);

        $dis['id'] = $input['id'];
        //生成模版消息
        $tmpl_data = $service_model::addtmpl($dis);
        //返回结果
        if(isset($tmpl_data['errcode'])&&$tmpl_data['errcode']==0){
            //修改数据库
            $this->model->tmplUpdate($dis,['tmpl_id'=>$tmpl_data['priTmplId']]);

            return $this->success($tmpl_data['priTmplId']);
        }else{
            return $this->error($tmpl_data['errmsg']);
        }
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2019-12-30 11:08
     * @功能说明:生成数据库模版消息
     */
    public function tmpList(){
        //获取模块名
        $input   = $this->_input;

        $tmpl_data = [] ;
        if(isset($input['model_name'])){
            $tmpl_data = PublicsService::getTmplByModelName($this->_uniacid , $input['model_name']) ;
        }
        return $this->success($tmpl_data);

    }

    /**
     * @author jingshuixian
     * @DataTime: 2020/1/15 11:16
     * @功能说明:获取所有有权限的订阅消息配置列表
     */
    public function tmpLists(){

        $authModelList = AdminMenu::getAuthList($this->_uniacid);
        $tmpl_data = [] ;
        foreach ($authModelList as $key => $item ){

            $modelTmplData = PublicsService::getTmplByModelName($this->_uniacid , $key ) ;

            $tmpl_data = array_merge($tmpl_data , $modelTmplData ) ;

        }

        return $this->success($tmpl_data);
    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2019-12-26 18:38
     * @功能说明:商城模块编辑
     */
    public function tmplUpdate(){
        $input = $this->_input;
        if(!empty($input)&&is_array($input)){
            foreach ($input as $value){
                $res = $this->model->tmplUpdate(['id'=>$value['id']],['tmpl_id'=>$value['tmpl_id'],'kidList'=>$value['kidList']]);
            }
            return $this->success($res);
        }else{
            return $this->error('参数错误');
        }
    }








}
