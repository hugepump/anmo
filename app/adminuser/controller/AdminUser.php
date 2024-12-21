<?php
namespace app\adminuser\controller;
use app\admin\model\User;
use app\AdminRest;

use app\massage\model\Commission;
use app\massage\model\ShortCodeConfig;
use app\massage\model\Wallet;
use think\App;



class AdminUser extends AdminRest
{

    protected $model;


    public function __construct(App $app) {

        parent::__construct($app);

        $this->model = new \app\adminuser\model\AdminUser();

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 13:28
     * @功能说明:字段详情
     */
    public function configInfo(){

        $input = $this->_param;

        $dis = [

            'uniacid' => $this->_uniacid
        ];

        $data = $this->model->dataInfo($dis,1);

        if(!empty($data['user_id'])){

            $user_model = new \app\massage\model\User();

            $data['user_info'] = $user_model->where(['id'=>$data['user_id']])->field('id,nickName,avatarUrl')->find();
        }

        $comm_model = new Commission();

        $wallet_model = new Wallet();

        $data['total_cash'] = $comm_model->where(['status'=>2,'type'=>16])->sum('company_cash');

        $comm_cash = $comm_model->where(['status'=>2])->where('type','in',[21,22])->sum('company_cash');

        $data['wallet_cash']= $wallet_model->where(['type'=>11])->where('status','in',[2])->sum('total_price');

        $data['total_cash'] = round($data['total_cash']+$comm_cash,2);

        $data['wallet_cash']= round($data['wallet_cash'],2);

        return $this->success($data);
    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 13:29
     * @功能说明:添加字段
     */
    public function configUpdate(){

        $input = $this->_input;

        if(empty($input['phone_code'])){

            $this->errorMsg('请输入验证码');
        }

        $phone  = getConfigSetting($this->_uniacid,'login_auth_phone');

        $key    = $phone.'SendAdminShortMsg';

        if($input['phone_code']!=getCache($key,$this->_uniacid)){

            $this->errorMsg('验证码错误');
        }

        setCache($key,'',10,$this->_uniacid);

        $dis = [

            'uniacid' => $this->_uniacid
        ];

        $update = [

            'user_id' => $input['user_id'],

            'phone'   => $input['phone'],

            'user_name' => $input['user_name'],

            'bind_time' => time()
        ];

        $data = $this->model->dataUpdate($dis,$update);

        return $this->success($data);
    }
    //yuyue.roudaojia.cn


    /**
     * @author chenniang
     * @DataTime: 2024-06-19 17:45
     * @功能说明:校验用户手机号
     */
    public function checkPhoneCode(){

        $input = $this->_input;

        $key    = $input['phone'].'bindadminuser';

        if(empty($input['phone_code'])){

            $this->errorMsg('请输入验证码');
        }

        if($input['phone_code']!=getCache($key,$this->_uniacid)){

            $this->errorMsg('验证码错误');
        }

        setCache($key,'',10,$this->_uniacid);

        return $this->success(true);
    }



    /**
     * @author chenniang
     * @DataTime: 2024-06-19 16:32
     * @功能说明:发送短信验证码
     */
    public function bindsendPhoneCode(){

        $input  = $this->_input;

        $config = new ShortCodeConfig();

        $key    = 'bindadminuser';

        $res    = $config->sendSmsCode($input['phone'],$this->_uniacid,$key);

        if(!empty($res['Message'])&&$res['Message']=='OK'){

            return $this->success(1);

        }else{

            return $this->error($res['Message']);
        }
    }


    /**
     * @author chenniang
     * @DataTime: 2024-06-19 16:32
     * @功能说明:解除绑定
     */
    public function delbind(){

        $input = $this->_input;

        $data = $this->model->dataInfo(['uniacid'=>$this->_uniacid]);

        $key    = $data['phone'].'delbindadminuser';

        if(empty($input['phone_code'])){

            $this->errorMsg('请输入验证码');
        }

        if($input['phone_code']!=getCache($key,$this->_uniacid)){

            $this->errorMsg('验证码错误');
        }

        $dis = [

            'uniacid' => $this->_uniacid
        ];

        $update = [

            'user_id' => '',

            'phone'   => ''
        ];

        $data = $this->model->dataUpdate($dis,$update);

        return $this->success($data);
    }


    /**
     * @author chenniang
     * @DataTime: 2024-06-19 16:32
     * @功能说明:发送短信验证码
     */
    public function delBindsendPhoneCode(){

        $input = $this->_input;

        $config = new ShortCodeConfig();

        $data = $this->model->dataInfo(['uniacid'=>$this->_uniacid]);

        if(empty($data['phone'])){

            $this->errorMsg('还未绑定人员');
        }

        $key    = 'delbindadminuser';

        $res    = $config->sendSmsCode($data['phone'],$this->_uniacid,$key);

        if(!empty($res['Message'])&&$res['Message']=='OK'){

            return $this->success(1);

        }else{

            return $this->error($res['Message']);
        }
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-03-14 11:55
     * @功能说明:发送验证码
     */
    public function sendAdminShortMsg(){

        $input = $this->_input;
        //验证码验证
        $config = new ShortCodeConfig();

        $phone  = getConfigSetting($this->_uniacid,'login_auth_phone');

        $key    = 'SendAdminShortMsg';

        $res    = $config->sendSmsCode($phone,$this->_uniacid,$key);

        if(!empty($res['Message'])&&$res['Message']=='OK'){

            return $this->success(1);

        }else{

            return $this->error($res['Message']);
        }
    }














}
