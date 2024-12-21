<?php
namespace app\virtual\controller;
use app\AdminRest;


use app\massage\model\Banner;
use app\massage\model\MsgConfig;
use app\massage\model\PayConfig;
use app\virtual\info\PermissionVirtual;
use app\virtual\model\Config;
use app\virtual\model\PlayRecord;
use app\virtual\model\Record;
use think\App;
use app\massage\model\Config as Model;


class AdminSetting extends AdminRest
{


    protected $model;

    protected $admin_model;


    public function __construct(App $app) {

        parent::__construct($app);

        $this->model = new Config();

        $this->admin_model = new \app\massage\model\Admin();


    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-12 15:04
     * @功能说明:配置详情
     */
    public function configInfo(){

        $dis = [

            'uniacid' => $this->_uniacid
        ];

        $data  = $this->model->dataInfo($dis);

        $data['moor_phone_arr'] = !empty($data['moor_phone_arr'])?explode(',',$data['moor_phone_arr']):[];

        $data['winnerlook_phone_arr'] = !empty($data['winnerlook_phone_arr'])?explode(',',$data['winnerlook_phone_arr']):[];

        return $this->success($data);


    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-12 16:14
     * @功能说明:编辑配置
     */
    public function configUpdate(){

        $input = $this->_input;

        $dis = [

            'uniacid' => $this->_uniacid
        ];

        if(isset($input['reminder_admin_phone'])){

            $input['reminder_admin_phone'] = !empty($input['reminder_admin_phone'])?implode(',',$input['reminder_admin_phone']):'';
        }

        if(isset($input['moor_phone_arr'])){

            $input['moor_phone_arr'] = !empty($input['moor_phone_arr'])?implode(',',$input['moor_phone_arr']):'';
        }

        if(isset($input['winnerlook_phone_arr'])){

            $input['winnerlook_phone_arr'] = !empty($input['winnerlook_phone_arr'])?implode(',',$input['winnerlook_phone_arr']):'';
        }

        $data  = $this->model->dataUpdate($dis,$input);

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-12-09 16:03
     * @功能说明:通话记录
     */
    public function phoneRecordList(){


//        Queue::later();
        $input = $this->_param;

        $record_model = new PlayRecord();

        $dis[] = ['uniacid','=',$this->_uniacid];

        if(!empty($input['order_id'])){

            $dis[] = ['out_id','=',$input['order_id']];

        }

        if(!empty($input['start_time'])&&!empty($input['end_time'])){

            $dis[] = ['call_time','between',"{$input['start_time']},{$input['end_time']}"];

        }

        $data = $record_model->dataList($dis,$input['limit']);

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){
                //手机号加密
                if($this->_user['phone_encryption']==0){

                   // $v['phone_a'] = substr_replace($v['phone_a'], "****", 2,4);

                    $v['phone_b'] = substr_replace($v['phone_b'], "****", 2,4);
                }
            }
        }

        return $this->success($data);

    }



}
