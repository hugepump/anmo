<?php
namespace app\massage\model;

use AlibabaCloud\Client\AlibabaCloud;
use app\BaseModel;
use Exception;
use longbingcore\wxcore\Moor;
use longbingcore\wxcore\Winnerlook;
use think\facade\Db;

class ShortCodeConfig extends BaseModel
{
    //定义表名
    protected $name = 'massage_short_code_config';


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:04
     * @功能说明:添加
     */
    public function dataAdd($data){

        $res = $this->insert($data);

        return $res;

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:05
     * @功能说明:编辑
     */
    public function dataUpdate($dis,$data){

        $res = $this->where($dis)->update($data);

        return $res;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:06
     * @功能说明:列表
     */
    public function dataList($dis,$page){

        $data = $this->where($dis)->order('id desc')->paginate($page)->toArray();

        return $data;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:43
     * @功能说明:
     */
    public function dataInfo($dis){

        $data = $this->where($dis)->find();

        if(empty($data)){

            $this->dataAdd($dis);

            $data = $this->where($dis)->find();

        }

        return !empty($data)?$data->toArray():[];

    }


    /**
     * @param $uniacid
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-03 10:39
     */
    public function initData($uniacid){

        $data = $this->dataInfo(['uniacid'=>$uniacid]);

        $config_model = new Config();

        $config = $config_model->dataInfo(['uniacid'=>$uniacid]);
        //开始初始化
        if(!empty($config['short_sign'])){

            $update = [

                'short_sign' => $config['short_sign'],
                'order_short_code' => $config['order_short_code'],
                'refund_short_code' => $config['refund_short_code'],
                'help_short_code' => $config['help_short_code'],
                'short_code' => $config['short_code'],
                'short_code_status' => $config['short_code_status'],
            ];

            $this->dataUpdate(['id'=>$data['id']],$update);

            $prefix = longbing_get_prefix();
            //执行sql删除废弃字段
            $sql = <<<updateSql
            
ALTER TABLE `{$prefix}shequshop_school_config` DROP COLUMN  `short_sign`;
ALTER TABLE `{$prefix}shequshop_school_config` DROP COLUMN  `order_short_code`;
ALTER TABLE `{$prefix}shequshop_school_config` DROP COLUMN  `refund_short_code`;
ALTER TABLE `{$prefix}shequshop_school_config` DROP COLUMN  `help_short_code`;
ALTER TABLE `{$prefix}shequshop_school_config` DROP COLUMN  `short_code`;
ALTER TABLE `{$prefix}shequshop_school_config` DROP COLUMN  `short_code_status`;
                

updateSql;

            $sql = str_replace(PHP_EOL, '', $sql);
            $sqlArray = explode(';', $sql);

            foreach ($sqlArray as $_value) {
                if(!empty($_value)){

                    try{
                        Db::query($_value) ;
                    }catch (\Exception $e){
                        if (!APP_DEBUG){

                        }

                    }
                }
            }
        }

        return true;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-19 15:13
     * @功能说明:七莫发送订单通知
     */
    public function sendSmsMoor($str_phone,$uniacid,$order_code,$type=1){

        $dis = [

            'uniacid' => $uniacid
        ];

        $config = $this->dataInfo($dis);

        if($type==1){

            $TemplateCode = trim($config['moor_order_short_code']);

        }else{

            $TemplateCode = trim($config['moor_refund_short_code']);

        }

        $moor = new Moor($uniacid);

        $res = $moor->sendShortMsg($str_phone,['var1'=>$order_code],$TemplateCode);

        return $res;
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-19 15:01
     * @功能说明:发送短信验证码
     */
    public function sendSms($str_phone,$uniacid,$order_code,$type=1){

        $dis = [

            'uniacid' => $uniacid
        ];

        $config = $this->dataInfo($dis);

        if($config['type']==1){

            $res = $this->sendSmsAliyun($str_phone,$uniacid,$order_code,$type);

        }elseif($config['type']==2){

            $res = $this->sendSmsMoor($str_phone,$uniacid,$order_code,$type);

        }else{

            $res = $this->sendSmsWinner($str_phone,$uniacid,$order_code,$type);

        }

        return $res;

    }


    /**
     * @param $str_phone
     * @param $uniacid
     * @param $order_code
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-10-07 15:15
     */
    public function sendSmsWinner($str_phone,$uniacid,$order_code,$type){

        $config_model = new ShortCodeConfig();

        $config = $config_model->dataInfo(['uniacid'=>$uniacid]);

        if($type==1){

            $msg = str_replace('{order_code}',$order_code,$config['winner_order_text']);
        }else{

            $msg = str_replace('{order_code}',$order_code,$config['winner_refund_text']);

        }

        $winner = new Winnerlook($uniacid);

        $res = $winner->sendShortMsg($str_phone,$msg);

        return $res;
    }


    /**
     * @param $str_phone
     * @param $uniacid
     * @功能说明:发送短信验证码
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-03-14 10:43
     */
    public function sendSmsAliyun($str_phone,$uniacid,$order_code,$type=1){

        $dis = [

            'uniacid' => $uniacid
        ];

        $config = $this->dataInfo($dis);

        $setting_model = new Config();

        $setting = $setting_model->dataInfo($dis);

        $keyId     = trim($setting['short_id']);

        $keySecret = trim($setting['short_secret']);

        $SignName  = trim($config['short_sign']);

        if($type==1){

            $TemplateCode = trim($config['order_short_code']);

        }else{

            $TemplateCode = trim($config['refund_short_code']);
        }

        if(empty($keyId)||empty($keySecret)||empty($TemplateCode)){

            return false;
        }

        AlibabaCloud::accessKeyClient($keyId, $keySecret)->regionId('cn-hangzhou') // replace regionId as you need
        ->asDefaultClient();

        try {
            $result = AlibabaCloud::rpc()
                ->product('Dysmsapi')
                // ->scheme('https') // https | http
                ->version('2017-05-25')
                ->action('SendSms')
                ->method('POST')
                ->host('dysmsapi.aliyuncs.com')
                ->options([
                    'query' => [
                        'RegionId' => "default",
                        'PhoneNumbers' => $str_phone,
                        //必填项 签名(需要在阿里云短信服务后台申请)
                        'SignName' => $SignName,
                        //必填项 短信模板code (需要在阿里云短信服务后台申请)
                        'TemplateCode' => $TemplateCode,
                        //如果在短信中添加了${code} 变量则此项必填 要求为JSON格式
                        'TemplateParam' => "{'name':$order_code}",
                    ],
                ])
                ->request();

            return !empty($result)?$result->toArray():[];
        } catch(Exception $e)
        {}
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-19 15:01
     * @功能说明:发送短信验证码
     */
    public function sendSmsCode($str_phone,$uniacid,$key=''){

        $dis = [

            'uniacid' => $uniacid
        ];

        $config = $this->dataInfo($dis);

        if($config['type']==1){

            $res = $this->sendSmsCodeAliyun($str_phone,$uniacid,$key);

        }elseif($config['type']==2){

            $res = $this->sendSmsCodeMoor($str_phone,$uniacid,$key);
        }else{

            $res = $this->sendSmsCodeWinner($str_phone,$uniacid,$key);
        }

        return $res;
    }


    /**
     * @param $str_phone
     * @param $uniacid
     * @功能说明:发送短信验证码
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-03-14 10:43
     */
    public function sendSmsCodeAliyun($str_phone,$uniacid,$key=''){

        $dis = [

            'uniacid' => $uniacid
        ];

        $config = $this->dataInfo($dis);

        $setting_model = new Config();

        $setting   = $setting_model->dataInfo($dis);

        $keyId     = trim($setting['short_id']);

        $keySecret = trim($setting['short_secret']);

        $SignName = $config['short_sign'];

        $TemplateCode = $config['short_code'];

        if(empty($keyId)||empty($keySecret)||empty($TemplateCode)){

           // return false;
        }
        $code = mt_rand(100000,999999);

        setCache($str_phone.$key,$code,600,$uniacid);

        AlibabaCloud::accessKeyClient($keyId, $keySecret)->regionId('cn-hangzhou') // replace regionId as you need
        ->asDefaultClient();

        try {
            $result = AlibabaCloud::rpc()
                ->product('Dysmsapi')
                // ->scheme('https') // https | http
                ->version('2017-05-25')
                ->action('SendSms')
                ->method('POST')
                ->host('dysmsapi.aliyuncs.com')
                ->options([
                    'query' => [
                        'RegionId' => "default",
                        'PhoneNumbers' => $str_phone,
                        //必填项 签名(需要在阿里云短信服务后台申请)
                        'SignName' => $SignName,
                        //必填项 短信模板code (需要在阿里云短信服务后台申请)
                        'TemplateCode' => $TemplateCode,
                        //如果在短信中添加了${code} 变量则此项必填 要求为JSON格式
                        'TemplateParam' => "{'code':$code}",
                    ],
                ])
                ->request();


            return !empty($result)?$result->toArray():[];
        } catch(Exception $e)
        {}
    }


    /**
     * @param $str_phone
     * @param $uniacid
     * @功能说明:发送短信验证码
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-03-14 10:43
     */
    public function sendSmsCodeWinner($str_phone,$uniacid,$key=''){

        $code = mt_rand(100000,999999);

        setCache($str_phone.$key,$code,600,$uniacid);

        $config_model = new ShortCodeConfig();

        $config = $config_model->dataInfo(['uniacid'=>$uniacid]);

        $msg = str_replace('{code}',$code,$config['winner_code_text']);

        $winner = new Winnerlook($uniacid);

        $res = $winner->sendShortMsg($str_phone,$msg);

        return $res;

    }


    /**
     * @param $str_phone
     * @param $uniacid
     * @功能说明:发送短信验证码
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-03-14 10:43
     */
    public function sendSmsCodeMoor($str_phone,$uniacid,$key=''){

        $dis = [

            'uniacid' => $uniacid
        ];

        $config = $this->dataInfo($dis);

        $TemplateCode = $config['moor_short_code'];

        if(empty($TemplateCode)){

            return false;
        }
        $code = mt_rand(100000,999999);

        setCache($str_phone.$key,$code,600,$uniacid);

        $moor = new Moor($uniacid);

        $res = $moor->sendShortMsg($str_phone,['var1'=>$code],$TemplateCode);

        return $res;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-19 15:01
     * @功能说明:发送短信验证码
     */
    public function sendHelpCode($uniacid,$coach_id,$address){

        $dis = [

            'uniacid' => $uniacid
        ];

        $config = $this->dataInfo($dis);

        if($config['type']==1){

            $res = $this->sendHelpCodeAliyun($uniacid,$coach_id,$address);

        }elseif($config['type']==2){

            $res = $this->sendHelpCodeMoor($uniacid,$coach_id,$address);
        }else{

            $res = $this->sendHelpCodeWinner($uniacid,$coach_id,$address);
        }

        return $res;
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-19 15:13
     * @功能说明:七莫发送订单通知
     */
    public function sendHelpCodeWinner($uniacid,$coach_id,$address){

        $dis = [

            'uniacid' => $uniacid
        ];

        $config = $this->dataInfo($dis);

        $help_cofig_model = new HelpConfig();

        $help_cofig = $help_cofig_model->dataInfo(['uniacid'=>$uniacid]);

        $coach_model= new Coach();

        $coach_info= $coach_model->dataInfo(['id'=>$coach_id]);

        $msg = str_replace('{coach_name}',$coach_info['coach_name'] . '(ID:' . $coach_id . ')',$config['winner_police_text']);

        $msg = str_replace('{address}',$address,$msg);

        $winner = new Winnerlook($uniacid);

        $res = 1;

        if(!empty($help_cofig['help_phone'])&&$help_cofig['short_admin_status']==1&&(empty($coach_info['admin_id'])||$help_cofig['short_notice_admin']==1)) {

            foreach ($help_cofig['help_phone'] as $value) {

                $res = $winner->sendShortMsg($value,$msg);
            }
        }
        //通知代理商
        if(!empty($coach_info['admin_id'])&&$help_cofig['help_short_agent_status']){

            $admin_model = new Admin();

            $admin = $admin_model->dataInfo(['id'=>$coach_info['admin_id']]);

            if(!empty($admin)){

                $res = $winner->sendShortMsg($admin['phone'],$msg);
            }
        }

        return $res;
    }

    /**
     * @param $str_phone
     * @param $uniacid
     * @功能说明:发送求救通知
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-03-14 10:43
     */
    public function sendHelpCodeAliyun($uniacid,$coach_id,$address){

        $address = !empty($address)?$address:'暂无';

        $dis = [

            'uniacid' => $uniacid
        ];

        $config = $this->dataInfo($dis);

        $setting_model = new Config();

        $setting   = $setting_model->dataInfo($dis);

        $keyId     = trim($setting['short_id']);

        $keySecret = trim($setting['short_secret']);

        $SignName = $config['short_sign'];

        $TemplateCode = $config['help_short_code'];

        if(empty($keyId)||empty($keySecret)||empty($TemplateCode)){

            return false;
        }

        $help_cofig_model = new HelpConfig();

        $help_cofig = $help_cofig_model->dataInfo(['uniacid'=>$uniacid]);

        $coach_model= new Coach();

        $coach_info= $coach_model->dataInfo(['id'=>$coach_id]);

        AlibabaCloud::accessKeyClient($keyId, $keySecret)->regionId('cn-hangzhou') // replace regionId as you need
        ->asDefaultClient();
        //通知平台

       // dump($help_cofig['short_admin_status']);
        if(!empty($help_cofig['help_phone'])&&$help_cofig['short_admin_status']==1&&(empty($coach_info['admin_id'])||$help_cofig['short_notice_admin']==1)){

            foreach ($help_cofig['help_phone'] as $value){

                try {
                    $result = AlibabaCloud::rpc()
                        ->product('Dysmsapi')
                        // ->scheme('https') // https | http
                        ->version('2017-05-25')
                        ->action('SendSms')
                        ->method('POST')
                        ->host('dysmsapi.aliyuncs.com')
                        ->options([
                            'query' => [
                                'RegionId' => "default",
                                'PhoneNumbers' => $value,
                                //必填项 签名(需要在阿里云短信服务后台申请)
                                'SignName' => $SignName,
                                //必填项 短信模板code (需要在阿里云短信服务后台申请)
                                'TemplateCode' => $TemplateCode,
                                //如果在短信中添加了${code} 变量则此项必填 要求为JSON格式
                                //'TemplateParam' => "{'name':$coach_name,'address':$address}",

                                'TemplateParam' => json_encode(['name'=>$coach_info['coach_name'].'(ID:'.$coach_id.')','address'=>mb_substr($address, 0, 35)]),
                            ],
                        ])
                        ->request();

                  //  dump(mb_substr($address, 0, 35),$address,$result->toArray());exit;
                  //  return !empty($result)?$result->toArray():[];
                } catch(Exception $e)
                {}
            }
        }
        //通知代理商
        if(!empty($coach_info['admin_id'])&&$help_cofig['help_short_agent_status']){

            $admin_model = new Admin();

            $admin = $admin_model->dataInfo(['id'=>$coach_info['admin_id']]);

            if(!empty($admin)){

                try {
                    $result = AlibabaCloud::rpc()
                        ->product('Dysmsapi')
                        // ->scheme('https') // https | http
                        ->version('2017-05-25')
                        ->action('SendSms')
                        ->method('POST')
                        ->host('dysmsapi.aliyuncs.com')
                        ->options([
                            'query' => [
                                'RegionId' => "default",
                                'PhoneNumbers' => $admin['phone'],
                                //必填项 签名(需要在阿里云短信服务后台申请)
                                'SignName' => $SignName,
                                //必填项 短信模板code (需要在阿里云短信服务后台申请)
                                'TemplateCode' => $TemplateCode,
                                //如果在短信中添加了${code} 变量则此项必填 要求为JSON格式
                                //'TemplateParam' => "{'name':$coach_name,'address':$address}",

                                'TemplateParam' => json_encode(['name'=>$coach_info['coach_name'].'(ID:'.$coach_id.')','address'=>$address]),
                            ],
                        ])
                        ->request();

                } catch(Exception $e)
                {}

            }
        }

        return !empty($result)?$result->toArray():[];

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-19 15:13
     * @功能说明:七莫发送订单通知
     */
    public function sendHelpCodeMoor($uniacid,$coach_id,$address){

        $dis = [

            'uniacid' => $uniacid
        ];

        $config = $this->dataInfo($dis);

        $TemplateCode = trim($config['moor_help_short_code']);

        $help_cofig_model = new HelpConfig();

        $help_cofig = $help_cofig_model->dataInfo(['uniacid'=>$uniacid]);

        $coach_model= new Coach();

        $coach_info= $coach_model->dataInfo(['id'=>$coach_id]);

        $moor = new Moor($uniacid);

        $res = 1;

        if(!empty($help_cofig['help_phone'])&&$help_cofig['short_admin_status']==1&&(empty($coach_info['admin_id'])||$help_cofig['short_notice_admin']==1)) {

            foreach ($help_cofig['help_phone'] as $value) {

                $res = $moor->sendShortMsg($value, ['var1' => $coach_info['coach_name'] . '(ID:' . $coach_id . ')', 'var2' => $address], $TemplateCode);
            }
        }
        //通知代理商
        if(!empty($coach_info['admin_id'])&&$help_cofig['help_short_agent_status']){

            $admin_model = new Admin();

            $admin = $admin_model->dataInfo(['id'=>$coach_info['admin_id']]);

            if(!empty($admin)){

                $res = $moor->sendShortMsg($admin['phone'], ['var1' => $coach_info['coach_name'] . '(ID:' . $coach_id . ')', 'var2' => $address], $TemplateCode);
            }
        }

        return $res;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-24 11:44
     * @功能说明:登录短信验证
     */
    public function loginShortConfig($phone,$uniacid){

        $config = $this->dataInfo(['uniacid'=>$uniacid]);
        //用自己的短信
        if($config['short_code_status']==1){

            $result = $this->sendSmsCode($phone,$uniacid,'login');

        }else {

            $url = $_SERVER['HTTP_HOST'];

            $code = mt_rand(100000, 999999);

            setCache($phone . 'login', $code, 600, $uniacid);

            $url = "http://checkauth.cncnconnect.com/massage/admin/Admin/sendCode?phone=$phone&url=$url&code=$code";

            $result = file_get_contents($url);

            $result = !empty($result)?json_decode($result,true):[];

        }
        return $result;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-24 11:44
     * @功能说明:登录短信验证
     */
    public function loginShortConfigV2($phone,$uniacid){

        $this->sendSmsCodeAliyun($phone,$uniacid,'keys');

        return true;
    }



}