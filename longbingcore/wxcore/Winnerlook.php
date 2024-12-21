<?php
declare(strict_types=1);

namespace longbingcore\wxcore;



use app\massage\model\ShortCodeConfig;
use app\virtual\model\Config;
use think\facade\Db;

class Winnerlook{

    static protected $uniacid;

    protected $accountid;

    protected $url;

    protected $secret;

    protected $reminder_text;

    protected $reminder_phone;

    protected $template_id;

    protected $help_tmpl_id;

    protected $config;

    public function __construct($uniacid,$appid='',$token='')
    {
       self::$uniacid = $uniacid;

       $config_model = new Config();

       $config = $config_model->dataInfo(['uniacid'=>$uniacid]);

       $this->accountid = !empty($appid)?$appid:$config['winnerlook_appid'];

       $this->url = 'http://101.37.133.245:11108/';

       $this->secret = !empty($token)?$token:$config['winnerlook_token'];

       $this->reminder_text = $config['reminder_text'];

       $this->reminder_phone = $config['moor_reminder_phone'];

       $this->template_id = $config['reminder_tmpl_id'];

       $this->help_tmpl_id = $config['help_tmpl_id'];

       $this->config = $config;

    }


    /**
     * @param $phoneA
     * @param $phoneB
     * @param $phoneX
     * @param $order_id
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-16 14:22
     */
    public function bindphone($phoneA,$phoneB,$phoneX,$order_id,$is_city=0){

        $time		    =	date("YmdHis");
        $time  = time()*1000;

        $authorization	=	base64_encode($this->accountid.":".$time);
        $sig			=	md5($this->accountid.$this->secret.$time);

        $data =  [

            'bindNumberA' => $phoneA,

            'bindNumberB' => $phoneB,

           // 'middleNumber' => $phoneX,

            'callbackUrl' => 'http://'.$_SERVER['HTTP_HOST'].'/virtual/CallBack/aliyunCallBackLook',

            'customerData'  => $order_id,

            'maxBindingTime' => 60,

        ];

        if($is_city==0){

            $data['middleNumber'] = $phoneX;
        }

        $url = $this->url.'voice/1.0.0/middleNumberAXB/'.$this->accountid.'/'.$sig;

        $res = $this->curlPost($url,$data,$authorization);

        $res = !empty($res)?json_decode($res,true):[];

        return $res;

    }




    /**
     * @param $mappingId
     * @param $midNum
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-16 14:48
     */
    public function delBind($phoneA,$phoneB,$phoneX){

        $time  = time()*1000;

        $authorization	=	base64_encode($this->accountid.":".$time);
        $sig			=	md5($this->accountid.$this->secret.$time);

      //  $url = $this->url.'/v20160818/rlxh/midNumUnBindForAXB/'.$this->accountid.'?sig='.$sig;

        $url = $this->url.'voice/1.0.0/middleNumberUnbind/'.$this->accountid.'/'.$sig;
        $data =  [

            'middleNumber' => $phoneX,

            'bindNumberA'  => $phoneA,

            'bindNumberB'  => $phoneB,

            'mode'         => 0,

        ];

        $res = $this->curlPost($url,$data,$authorization);

        $res = !empty($res)?json_decode($res,true):[];

        return $res;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-19 11:12
     * @功能说明:语音通知
     */
    public function webCall($phone,$type=1){

        $time  = time()*1000;
        $authorization	=	base64_encode($this->accountid.":".$time);
        $sig			=	md5($this->accountid.$this->secret.$time);

        if($type==1){

            $tmpl_id = $this->config['reminder_tmpl_id'];

        }elseif ($type==2){

            $tmpl_id = $this->config['help_tmpl_id'];

        }else{

            $tmpl_id = $this->config['order_end_tmpl_id'];
        }

        $data = [

            'templateId'=> $tmpl_id,

            'calleeNumber'  => $phone,

            'templateArgs'  => (object)[],

            'replayTimes' => 1
        ];

        if(!empty($this->config['winnerlook_phone'])){

            $data['displayNumber'] = $this->config['winnerlook_phone'];
        }

        $url = $this->url.'voice/1.0.0/notify/'.$this->accountid.'/'.$sig;

        $res = $this->curlPost($url,$data,$authorization);

        $res = !empty($res)?json_decode($res,true):[];

        return $res;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-19 14:33
     * @功能说明:获取短信模版
     */
    public function getShortTmp(){

        $time		    =	date("YmdHis");
        $authorization	=	base64_encode($this->accountid.":".$time);
        $sig			=	strtoupper(md5($this->accountid.$this->secret.$time));

        $url = $this->url.'/v20160818/sms/getSmsTemplate/'.$this->accountid.'?sig='.$sig;

        $res = $this->curlPost($url,[],$authorization);

        $res = !empty($res)?json_decode($res,true):[];

        return $res;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-19 14:36
     * @功能说明:发送短信
     */
    public function sendShortMsg($phone,$msg){

        $config_model = new ShortCodeConfig();

        $config = $config_model->dataInfo(['uniacid'=>self::$uniacid]);

        $data['userCode'] = $config['winner_user'];

        $data['userPass'] = $config['winner_pass'];

        $data['DesNo'] = $phone;

        $data['Msg'] = $config['short_sign'].$msg;

        $url = 'http://118.178.116.15/winnerrxd/api/trigger/SendMsg';

        $res = lbCurlPost($url,$data);

        $res = simplexml_load_string($res);

        $res = json_encode($res);

        $res = json_decode($res,true);

        if($res[0]>0){

            $arr['Message'] = 'OK';
        }else{

            $arr['Message'] = '发送失败'.$res[0];
        }

        return $arr;
    }


    /**
     * @param $url
     * @param $data
     * @param $authorization
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-16 14:23
     */
    public function curlPost($url,$data,$authorization){

        $header[] = "Accept: application/json";
        $header[] = "Content-type: application/json;charset='utf-8'";
        $header[] = "Content-Length: ".strlen( json_encode($data) );
        $header[] = "Authorization: ".$authorization;
        $ch = curl_init ();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_URL, ($url) );//地址
        curl_setopt($ch, CURLOPT_POST, 1);   //请求方式为post
        curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($data)); //post传输的数据。
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        $return = curl_exec ( $ch );


        if($return === FALSE ){
            echo "CURL Error:".curl_error($ch);exit;
        }

        curl_close ( $ch );

        return $return;
    }






}