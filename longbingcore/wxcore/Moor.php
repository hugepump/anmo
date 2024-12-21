<?php
declare(strict_types=1);

namespace longbingcore\wxcore;



use app\massage\model\Order;
use app\virtual\model\Config;
use think\facade\Db;

class Moor{

    static protected $uniacid;

    protected $accountid;

    protected $url;

    protected $secret;

    protected $reminder_text;

    protected $help_tmpl_text;

    protected $reminder_phone;

    protected $order_end_tmpl_text;

    public function __construct($uniacid)
    {
       self::$uniacid = $uniacid;

       $config_model = new Config();

       $config = $config_model->dataInfo(['uniacid'=>$uniacid]);

       $this->accountid = $config['moor_id'];

       $this->url = $config['moor_url'];

       $this->secret = $config['moor_secret'];

       $this->reminder_text = $config['reminder_text'];

       $this->help_tmpl_text = $config['help_tmpl_text'];

       $this->order_end_tmpl_text = $config['order_end_tmpl_text'];

       $this->reminder_phone = $config['moor_reminder_phone'];

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
    public function bindphone($phoneA,$phoneB,$phoneX,$order_id){

        $time		    =	date("YmdHis");
        $authorization	=	base64_encode($this->accountid.":".$time);
        $sig			=	strtoupper(md5($this->accountid.$this->secret.$time));

        $data =  [

            'caller' => $phoneA,

            'called' => $phoneB,

            'midNum' => $phoneX,

            'needRecord' => 'true',

            'userData'  => $order_id,

            'expiration' => 30
        ];

        $url = $this->url.'/v20160818/rlxh/midNumBindForAXB/'.$this->accountid.'?sig='.$sig;

        $res = $this->curlPost($url,$data,$authorization);

        $res = !empty($res)?json_decode($res,true):[];

        return $res;

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
    public function webCallPhone($phoneA,$phoneB,$phoneX,$order_code){

        $time		    =	date("YmdHis");
        $authorization	=	base64_encode($this->accountid.":".$time);
        $sig			=	strtoupper(md5($this->accountid.$this->secret.$time));

        $data =  [

            'Action' => 'Webcall',

            'ServiceNo' => $phoneX,

            'Exten' => $phoneA,

            'WebCallType'  => 'asynchronous',

            'Variable' => "phoneNum:$phoneB",

            'CallBackType' => 'post',

            'ActionID'  => $order_code,
            //HTTP_HOST
            'CallBackUrl' => 'https://'.$_SERVER['SERVER_NAME'].'/virtual/CallBack/aliyunCallBackMoor'
        ];

        $url = $this->url.'/v20160818/webCall/webCall/'.$this->accountid.'?sig='.$sig;

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
    public function delBind($mappingId,$midNum){

        $time		    =	date("YmdHis");

        $authorization	=	base64_encode($this->accountid.":".$time);

        $sig			=	strtoupper(md5($this->accountid.$this->secret.$time));

        $url = $this->url.'/v20160818/rlxh/midNumUnBindForAXB/'.$this->accountid.'?sig='.$sig;

        $data =  [

            'mappingId' => $mappingId,

            'midNum'    => $midNum,

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

        $time		    =	date("YmdHis");
        $authorization	=	base64_encode($this->accountid.":".$time);
        $sig			=	strtoupper(md5($this->accountid.$this->secret.$time));

        if($type==1){

            $text = $this->reminder_text;

        }elseif ($type==2){

            $text = $this->help_tmpl_text;

        }else{

            $text = $this->order_end_tmpl_text;
        }

        $data = [

            'Action'   => 'Webcall',

            'ServiceNo'=> $this->reminder_phone,

            'Exten'    => $phone,

            'Variable' => "text:".$text,
            //异步
            'WebCallType'  => 'asynchronous',

            'Timeout'  => 60,
            //注意这个是接听状态回调地址，由于我们不需要，随意乱填了一个，需要可以改成自己的地址
            'CallBackUrl' => $this->url
        ];

        $url = $this->url.'/v20160818/webCall/webCall/'.$this->accountid.'?sig='.$sig;

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
    public function sendShortMsg($phone,$data,$tmpl){


        $time		    =	date("YmdHis");
        $authorization	=	base64_encode($this->accountid.":".$time);
        $sig			=	strtoupper(md5($this->accountid.$this->secret.$time));

        $data['num'] = $phone;

        $data['templateNum'] = $tmpl;

        $url = $this->url.'/v20160818/sms/sendInterfaceTemplateSms/'.$this->accountid.'?sig='.$sig;

        $res = $this->curlPost($url,$data,$authorization);

        $res = !empty($res)?json_decode($res,true):[];

        $order_model = new Order();

        $order_model->dataUpdate(['id'=>8],['text'=>serialize($data)]);
        $order_model->dataUpdate(['id'=>9],['text'=>serialize($res)]);

        if(!empty($res['code'])){

            $res['Message'] = $res['message'];

            return $res;
        }

        if(isset($res['success'])&&$res['success']==true){

            $res['Message'] = 'OK';

        }else{

            $res['Message'] = $res['message'];
        }

        return $res;
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