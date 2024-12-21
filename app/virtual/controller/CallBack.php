<?php
namespace app\virtual\controller;
use app\AdminRest;
use app\ApiRest;
use app\massage\model\Order;
use app\virtual\model\PlayRecord;
use app\virtual\model\Record;
use longbingcore\wxcore\PayNotify;
use think\App;
use think\facade\Db;
use WxPayApi;


class CallBack  extends ApiRest
{

    protected $app;

    public function __construct ( App $app )
    {
        $this->app = $app;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-12-08 15:33
     * @功能说明:阿里云支付回调
     */
    public function aliyunCallBack(){

        $this->request = $this->app->request;

        $inputs = json_decode($this->request->getInput(), true);

        if(empty($inputs)){

            $inputs = $_POST;
        }

        $model = new PlayRecord();

        if(!empty($inputs)){

            foreach ($inputs as $input){

                $insert = [

                    'uniacid' => $this->_uniacid,

                    'pool_key'=> !empty($input['pool_key'])?$input['pool_key']:'',

                    'phone_x' => $input['secret_no'],

                    'phone_a' => $input['phone_no'],

                    'phone_b' => $input['peer_no'],

                    'call_time' => strtotime($input['call_time']),

                    'start_time' => strtotime($input['start_time']),

                    'end_time' => strtotime($input['release_time']),

                    'record_url' => !empty($input['record_url'])?$input['record_url']:'',

                    'call_type'  => !empty($input['call_type'])?$input['call_type']:0,

                    'ring_record_url' => !empty($input['ring_record_url'])?$input['ring_record_url']:'',

                    'out_id' => !empty($input['out_id'])?$input['out_id']:'',

                    'call_id' => $input['call_id'],

                    'sub_id' => $input['sub_id'],
                ];

                $model->dataAdd($insert);
            }
        }

        $res = ['code'=>0,'msg'=>'成功'];

        echo json_encode($res);exit;


    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-16 14:57
     * @功能说明:
     */
    public function aliyunCallBackMoor(){

        $input = $this->_param;

        if(empty($input)){

            $input = $_GET;
        }

        if(!empty($input['called_show'])){

            $insert = [

                'uniacid' => $this->_uniacid,

                'phone_x' => $input['called_show'],

                'phone_a' => $input['caller'],

                'phone_b' => $input['called'],

                'call_time' => strtotime($input['begin_time']),

                'start_time' => strtotime($input['connect_time']),

                'end_time' => strtotime($input['release_time']),

                'record_url' => !empty($input['record_file_url'])?$input['record_file_url']:'',

                'ring_record_url' => !empty($input['record_file_url'])?$input['record_file_url']:'',

                'out_id' => !empty($input['userData'])?$input['userData']:'',

                'call_id' => $input['recorder_id'],

                'sub_id' => $input['mappingId'],
            ];
        }else{

            $input['RecordFile'] = $input['FileServer'].'/'.$input['RecordFile'];

            $order_model = new Order();

            $order_id = $order_model->where(['order_code'=>$input['WebcallActionID']])->value('id');

            if(empty($order_id)){

                $res = ['code'=>0,'msg'=>'成功'];

                echo json_encode($res);exit;
            }

            $record_model = new Record();

            $phonex = $record_model->where(['order_id'=>$order_id])->value('phone_x');

            $insert = [

                'uniacid' => $this->_uniacid,

                'phone_x' => $phonex,

                'phone_a' => $input['CallNo'],

                'phone_b' => $input['CalledNo'],

                'call_time' => strtotime($input['Ring']),

                'start_time' => strtotime($input['Begin']),

                'end_time' => strtotime($input['End']),

                'record_url' => $input['RecordFile'],

                'ring_record_url' => $input['RecordFile'],

                'out_id' => $order_id,

                'call_id' => $input['CallID'],

                'sub_id' => $input['CallSheetID'],
            ];
        }

        $model = new PlayRecord();

        $model->dataAdd($insert);

        $res = ['code'=>0,'msg'=>'成功'];

        echo json_encode($res);exit;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-07-25 16:57
     * @功能说明:电话回调通知云信
     */
    public function aliyunCallBackLook(){

       // $input = $this->_input;

        $this->request = $this->app->request;

        $input = json_decode($this->request->getInput(), true);

        if(empty($input)){

            $input = $_POST;
        }

        $insert = [

            'uniacid' => $this->_uniacid,

            'phone_x' => $input['displayNumber'],

            'phone_a' => $input['callerNumber'],

            'phone_b' => $input['calleeNumber'],

            'call_time' => strtotime($input['startCallTime']),

            'start_time' => strtotime($input['callerAnsweredTime']),

            'end_time' => strtotime($input['endTime']),

            'record_url' => !empty($input['recUrl'])?$input['recUrl']:'',

            'ring_record_url' => !empty($input['recUrl'])?$input['recUrl']:'',

            'out_id' => !empty($input['customerData'])?$input['customerData']:'',

            'call_id'=> $input['callId'],

            'sub_id' => $input['bindId'],
        ];

        $model = new PlayRecord();

        $model->dataAdd($insert);

        $res = ['code'=>0,'msg'=>'成功'];

        echo json_encode($res);exit;
    }
}
