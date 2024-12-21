<?php
declare ( strict_types = 1 );

namespace app;

use app\admin\model\Admin;
use app\admin\service\UpdateService;
use app\BaseController;
use app\massage\info\PermissionMassage;
use app\massage\model\ActionLog;
use app\massage\model\CompanyWater;
use app\massage\model\Config;
use app\massage\model\PayConfig;
use app\massage\model\Service;
use app\massage\model\ShopCarte;
use app\store\model\StoreList;
use LongbingUpgrade;
use think\App;
use think\exception\HttpResponseException;
use think\exception\ValidateException;
use think\facade\Db;
use think\facade\Env;
use think\Validate;
use think\Response;
use think\facade\Lang;



/**
 * 控制器基础类
 */
abstract class AdminRest extends BaseController
{

    //头部
    public $_header = [];
    //头部token
    public $_token = null;
    //语言信息
    public $_lang = 'zh-cn';
    //角色
    public $_role = 'guest';
    //用户信息
    public $_user = null;
    //唯一app标示
    public $_uniacid = '2';
    //定义检查中间件
    protected $middleware = ['app\middleware\AppInit'];
    //判断是否是微擎
    public $_is_weiqin = false;
    /**
     * 小程序版本
     * 0 => 无限开版 其他 = 几开版
     * @var array
     */
    protected $card_auth_version = 0;

    protected $admin_arr = [];



    /**
     * 可开通名片数量
     * 0 => 无限开版 其他 = 名片数量
     * @var array
     */
    protected $card_auth_card = 0;
    //@ioncube.dk myk("sha256", "cnjdbvjdnjd") -> "cff6bcac6bd92467e0cee72e5c879cdbf7044386eda8f464c817bd5c5c963d6f" RANDOM
    public function __construct ( App $app )
    {

        parent::__construct( $app);
        //获取token 通过header获取token,如果不存在,则从param中获取。
        if(!empty($this->_param['token'])){

            $this->_header['token'] = $this->_param['token'];
        }
        if(empty($this->_header[ 'token' ])){

            echo json_encode(['code' => 401, 'error' => '请重新登录!']);exit;
        }
        //获取token
        $this->_token = $this->_header[ 'token' ];
        //语言
        if ( isset( $this->_header[ 'lang' ] ) ) $this->_lang = $this->_header[ 'lang' ];
        //获取用户信息
        $this->_user = getUserForToken($this->_token );

        if ($this->_user == null) {

            echo json_encode(['code' => 401, 'error' => '请登录系统!']);exit;
        }

        setUserForToken($this->_token, $this->_user);

        $this->_uniacid = !empty( $this->_user ) && isset( $this->_user[ 'uniacid' ] )  ? $this->_user[ 'uniacid' ] : 2;

        $admin_model = new \app\massage\model\Admin();

        $this->_user = $admin_model->dataInfo(['id'=>$this->_user['id']]);

        if($this->_user['is_admin']!=1&&$this->_user['status']<0){

            echo json_encode(['code' => 401, 'error' => '请登录系统!']);exit;
        }

        $this->admin_arr = $admin_model->getAdminId($this->_user);

        if($this->_user['is_admin']==3){

            $user_info = $admin_model->dataInfo(['id'=>$this->_user['admin_id']]);

            $this->_user['true_is_admin'] = $this->_user['is_admin'];

            $this->_user['is_admin'] = $user_info['is_admin'];

            $this->_user['phone_encryption'] = $user_info['phone_encryption'];

            $this->_user['login_auth_phone'] = $user_info['login_auth_phone'];

        }else{

            $this->_user['admin_id'] = $this->_user['id'];
        }
        if(in_array($this->_user['is_admin'],[1,2])){

            $this->_user['phone_encryption'] = 1;
        }

        $admin_model->initAgentName();

        $water_model = new CompanyWater();

        $water_model->addWaterQueue($this->_uniacid,0,10);

       // $service_model = new Service();
//        //删除不同代理商的技师关联的服务
//        $service_model->delDiffCoachAndService($this->_uniacid);

    }


    /**
     * @param $data
     * @param int $code
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-17 15:15
     */
    public function success ( $data, $code = 200,$obj_id=null)
    {

        $result[ 'data' ] = $data;
        $result[ 'code' ] = $code;
        $result[ 'sign' ] = null;
        //简单的签名
        if ( !empty( $this->_token ) ) $result[ 'sign' ] = createSimpleSign( $this->_token, is_string( $data ) ? $data : json_encode( $data ) );

        $this->controlActionLog($obj_id);

        return $this->response( $result, 'json', $code  );
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-17 14:57
     * @功能说明:添加操作日志
     */
    //@ioncube.dk myk("sha256", "cnjdbvjdnjd") -> "cff6bcac6bd92467e0cee72e5c879cdbf7044386eda8f464c817bd5c5c963d6f" RANDOM
    public function controlActionLog($obj_ids=0){

        $dataPath = APP_PATH  . 'massage/info/LogSetting.php' ;

        $data =  include $dataPath ;

        if($this->_method=='post'){

            $input = $this->_input;

        }else{

            $input = $this->_param;
        }

        $log_model = new ActionLog();

        foreach ($data as $k=>$v){

            if($k==$this->_controller){

                foreach ($v as $value){

                    if($value['code_action']==$this->_action&&$value['method']==$this->_method){
                        //操作动作
                        $action = isset($input['status'])&&$input['status']==-1?'del':$value['action'];

                        if(!empty($obj_ids)){

                            $obj_id = $obj_ids;

                        }elseif($action=='add'){

                            $obj_id = Db::name($value['table'])->getLastInsID();

                        }elseif(isset($input[$value['parameter']])){
                            //目标id
                            $obj_id = $input[$value['parameter']];

                        }else{

                            $obj_id = 0;
                        }

                        $obj_id = is_array($obj_id)?serialize($obj_id):$obj_id;
                        //自定义参数 主要针对同一方法 通过参数区分的接口
                        if(!empty($value['custom_parameters'])){

                            $custom_parameters = Db::name($value['table'])->where(['id'=>$obj_id])->value($value['custom_parameters']['key']);

                            if($custom_parameters!=$value['custom_parameters']['value']){

                                continue;

                            }
                        }

                        if(!empty($value['transmit_parameters'])){
                            //没有默认值 只需检测是否传了参数
                            if(!isset($value['transmit_parameters']['value'])&&!isset($input[$value['transmit_parameters']['key']])){

                                continue;
                            //有默认值 还需要校验默认值和传值是否相同
                            }elseif(isset($value['transmit_parameters']['value'])&&(!isset($input[$value['transmit_parameters']['key']])||$input[$value['transmit_parameters']['key']]!=$value['transmit_parameters']['value'])){

                                continue;
                            }
                        }

                        $insert = [

                            'uniacid'     => $this->_uniacid,

                            'user_id'     => $this->_user['id'],

                            'obj_id'      => $obj_id,

                            'ip'          => getIP(),

                            'model'       => $k,

                            'method'      => $value['method'],

                            'table'       => $value['table'],

                            'code_action' => $value['code_action'],

                            'action_type' => $value['action_type'],

                            'action'      => $action,

                            'text'        => serialize($input)

                        ];

                        $log_model->dataAdd($insert);

                        break;

                    }

                }

            }

        }

        return true;

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-08-21 17:43
     * @功能说明:
     */
    public function shareChangeData($input){

        $arr = ['/admin/admin/config/clear','/massage/admin/AdminOrder/noLookCount','/massage/admin/AdminSetting/getSaasAuth'];

        if(!empty($input['s'])&&in_array($input['s'],$arr)){

            return false;
        }

        return true;
    }



    /**
     * REST 调用
     * @access public
     * @param string $method 方法名
     * @return mixed
     * @throws \Exception
     */
    public function _empty ( $method )
    {
        if ( method_exists( $this, $method . '_' . $this->method . '_' . $this->type ) ) {
            // RESTFul方法支持
            $fun = $method . '_' . $this->method . '_' . $this->type;
        }
        elseif ( $this->method == $this->restDefaultMethod && method_exists( $this, $method . '_' . $this->type ) ) {
            $fun = $method . '_' . $this->type;
        }
        elseif ( $this->type == $this->restDefaultType && method_exists( $this, $method . '_' . $this->method ) ) {
            $fun = $method . '_' . $this->method;
        }
        if ( isset( $fun ) ) {
            return App::invokeMethod( [
                $this,
                $fun
            ]
            );
        }
        else {
            // 抛出异常
            throw new \Exception( 'error action :' . $method );
        }
    }



    /**
     *
     * 获取支付信息
     */
    //@ioncube.dk myk("sha256", "cnjdbvjdnjd") -> "cff6bcac6bd92467e0cee72e5c879cdbf7044386eda8f464c817bd5c5c963d6f" RANDOM
    public function payConfig ($is_app=0){

        $uniacid_id = !empty($uniacid)?$uniacid:$this->_uniacid;

        $pay_model    = new PayConfig();

        $config_model = new Config();

        $pay    = $pay_model->dataInfo(['uniacid' => $uniacid_id]);

        $config = $config_model->dataInfo(['uniacid' => $uniacid_id]);

        if (empty($pay['mch_id']) || empty($pay['pay_key'])) {

            $this->errorMsg('未配置支付信息'.$uniacid_id);
        }

        $setting['payment'] = [
            'merchant_id'         => $pay['mch_id'],
            'key'                 => $pay['pay_key'],
            'cert_path'           => $pay['cert_path'],
            'key_path'            => $pay['key_path'],
            'ali_appid'           => $pay['ali_appid'],
            'ali_privatekey'      => $pay['ali_privatekey'],
            'ali_publickey'       => $pay['ali_publickey'],
            'appCretPublicKey'    => $pay['appCretPublicKey'],
            'alipayCretPublicKey' => $pay['alipayCretPublicKey'],
            'alipayRootCret'      => $pay['alipayRootCret'],
            'alipay_type'         => $pay['alipay_type'],
            'wx_certificates'     => $pay['wx_certificates'],
        ];


        $setting['company_pay'] = $config['company_pay'];

        if($is_app==0){

            $setting[ 'app_id' ] = $config['appid'];

            $setting[ 'secret' ] = $config['appsecret'];

        }elseif($is_app==1){

            $setting[ 'app_id' ] = $config['app_app_id'];

            $setting[ 'secret' ] = $config['app_app_secret'];

        }else{

            $setting[ 'app_id' ] = $config['web_app_id'];

            $setting[ 'secret' ] = $config['web_app_secret'];

        }

        $setting[ 'is_app' ]= $is_app;

        return $setting;
    }








}
