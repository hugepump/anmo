<?php
namespace app\recording\controller;
use app\AdminRest;
use app\ApiRest;
use app\massage\model\ActionLog;
use app\massage\model\Admin;
use app\massage\model\AdminRole;
use app\node\model\RoleAdmin;
use app\node\model\RoleList;
use app\node\model\RoleNode;
use think\App;



class Recording extends ApiRest
{

    public function __construct(App $app) {

        parent::__construct($app);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-01-04 14:02
     * @功能说明:上传录音
     */
    public function recordingAdd(){

        $input = $this->_input;

        $insert = [

            'uniacid'  => $this->_uniacid,

            'order_id' => $input['order_id'],

            'link'     => $input['link'],
        ];

        $model = new \app\recording\model\Recording();

        $data = $model->dataAdd($insert);

        return $this->success($data);


    }








}
