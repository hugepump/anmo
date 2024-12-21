<?php
namespace app\massage\controller;
use app\AdminRest;


use think\App;

use app\massage\model\Printer as model;

use think\facade\Db;


class AdminPrinter extends AdminRest
{

    protected $model;

    public function __construct(App $app) {

        parent::__construct($app);

        $this->model = new Model();

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 13:28
     * @功能说明:详情
     */
    public function printerList(){

        $input = $this->_param;

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['status','>',-1];

        $data = $this->model->dataList($dis,$input['limit']);

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 13:28
     * @功能说明:详情
     */
    public function printerInfo(){

        $input = $this->_param;

        $dis = [

            'uniacid' => $this->_uniacid
        ];

        $data = $this->model->dataInfo($dis);

        return $this->success($data);

    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 13:29
     * @功能说明:编辑
     */
    public function printerAdd(){

        $input = $this->_input;

        $input['uniacid']  = $this->_uniacid;

        $data = $this->model->dataAdd($input);

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 13:29
     * @功能说明:编辑
     */
    public function printerUpdate(){

        $input = $this->_input;

        $dis = [

            'uniacid' => $this->_uniacid
        ];

        $data = $this->model->dataUpdate($dis,$input);

        return $this->success($data);

    }











}
