<?php
namespace app\massage\controller;
use app\AdminRest;
use app\ApiRest;
use app\card\model\User;
use app\fdd\model\FddAgreementRecord;
use app\fdd\model\FddAttestationRecord;
use app\fdd\model\FddConfig;
use app\massage\model\ChannelList;
use app\massage\model\ChannelQr;
use app\massage\model\ChannelScanQr;
use app\massage\model\Coach;
use app\massage\model\FddRealnameCallback;
use app\massage\model\Order;
use app\massage\model\QrBind;
use app\massage\model\UserChannel;
use app\virtual\model\PlayRecord;
use longbingcore\permissions\AdminMenu;
use longbingcore\wxcore\Fdd;
use longbingcore\wxcore\PayNotify;
use longbingcore\wxcore\WxSetting;
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
     * @功能说明:发大大实名认证回调
     */
    public function fddAttestationCallBack(){

        $inputs = $_POST;

        $dis = [

            'customer_id' => $inputs['customerId'],

            'status' => 1
        ];

        $att_model = new FddAttestationRecord();

        $record = $att_model->dataInfo($dis);

        if(!empty($record)){

            if(($inputs['status']==2&&$record['type']==1)||($inputs['status']==4&&$record['type']==2)){

                $update['status'] = 2;

                $core = new Fdd($record['uniacid']);

                $res  = $core->ApplyCert($record['customer_id'],$record['transactionNo']);

                if(isset($res['code'])&&$res['code']==1){

                    $update['status'] = 3;
                }
            }

            $update['statusDesc'] = $inputs['statusDesc'];

            $update['sign'] = base64_encode($inputs['sign']);

           // $update['result_code']= $inputs['result_code'];

            $att_model->dataUpdate($dis,$update);
        }

        $res = ['code'=>0,'msg'=>'成功'];

        echo json_encode($res);exit;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-14 11:24
     * @功能说明:发大大签署回调
     */
    public function fddSignCallBack(){

        $inputs = $_POST;

        $dis = [

          //  'transaction_id' => $inputs['transaction_id'],

            'contract_id'    => $inputs['contract_id'],

            //'status'         => 1
        ];

        $model = new FddAgreementRecord();

        $data = $model->dataInfo($dis);

        if(!empty($data)){

            $update = [

                'result_code' => $inputs['result_code'],

                'result_desc' => $inputs['result_desc'],

                'msg_digest'  => base64_decode($inputs['msg_digest']),
            ];
            //签署成功
            if($inputs['result_code']==3000){

                $update['status'] = $data['status']==1?2:3;
            }

            if(!empty($inputs['download_url'])){

                $update['download_url'] = $inputs['download_url'];

            }

            if(!empty($inputs['viewpdf_url'])){

                $update['viewpdf_url'] = $inputs['viewpdf_url'];

            }

            $admin_model = new \app\massage\model\Admin();

            if(!empty($data['admin_id'])){

                $admin  = $admin_model->dataInfo(['id'=>$data['admin_id']]);
            }else{

                $admin  = $admin_model->dataInfo(['is_admin'=>1]);
            }

            $model->dataUpdate($dis,$update);
            //合同归档
            if(!empty($update['status'])&&$update['status']==3){

                $attestation_model = new FddAttestationRecord();

                $res = $attestation_model->ContractFiling($data['user_id'],$data['uniacid'],$data['admin_id']);
                //归档成功
                if(empty($res['code'])){

                    $year = $admin['agreement_time'];

                    $time = strtotime("+$year year");

                    $model->dataUpdate(['id'=>$data['id']],['status'=>4,'end_time'=>$time]);
                }
            }
        }

        $res = ['code'=>0,'msg'=>'成功'];

        echo json_encode($res);exit;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-06-05 18:14
     * @功能说明:扫码绑定用户
     */

    public function valid(){

        if(!empty($_GET['echostr'])){

            echo $_GET['echostr'].'';exit;
        }

        $xml_str = file_get_contents("php://input");

        $xml = (array)simplexml_load_string($xml_str,"SimpleXMLElement",LIBXML_NOCDATA);

        if(!empty($xml['FromUserName'])&&!empty($xml['Event'])&&in_array($xml['Event'],['subscribe','SCAN'])){

            $open_id = $xml['FromUserName'];

            $unionid = '';
            //获取unionid
            if(!empty($open_id)){

                $wx_setting = new WxSetting($this->_uniacid);

                $access_token = $wx_setting->getGzhToken();

                $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=$access_token&openid=$open_id&lang=zh_CN";

                $user_info = @file_get_contents($url);

                if(!empty($user_info)){

                    $user_info = json_decode($user_info,true);

                    $unionid = !empty($user_info['unionid'])?$user_info['unionid']:'';
                }
            }

            $pid = $xml['Event']=='subscribe'?str_replace('qrscene_','',$xml['EventKey']):$xml['EventKey'];

//                        $order_model = new Order();
////
//            $order_model->where(['id'=>25])->update(['text'=>serialize($xml)]);
            if(!empty($pid)){

                $pid_count = explode('_',$pid);
                //绑定渠道商
                if(count($pid_count)>1&&$pid_count[1]=='channel'){

                    $this->bindChannel($open_id,$pid_count[0],$unionid);
                //渠道码绑定渠道商
                }elseif(count($pid_count)>1&&$pid_count[1]=='chaqr'){

                    $is_qr = 1;

                    $this->bindChannelQr($open_id,$pid_count[0],$unionid);

                }else{
                    //绑定分销
                    $this->bindPid($open_id,$pid,$unionid);
                }
            }

            if(!empty($is_qr)){

                $this->sendRegisterChannelMsg($xml,$open_id,$pid_count[0]);
            }

            if($xml['Event']=='subscribe'){

                $text = getConfigSetting($this->_uniacid,'wechat_reply_text');

                if(!empty($text)){

                    $this->SRCnt($xml,$text);
                }
            }
        }

        echo "";exit;

    }


    /**
     * @param $xml
     * @param $open_id
     * @param $qr_code
     * @功能说明:当渠道码未绑定渠道商时候，非渠道商的人扫码可以注册成为渠道商
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-11-30 11:01
     */
    public function sendRegisterChannelMsg($xml,$open_id,$qr_code){

        $qr_model = new ChannelQr();

        $channel_model = new ChannelList();

        $user_model = new \app\massage\model\User();

        $qr = $qr_model->dataInfo(['code'=>$qr_code,'status'=>1]);

        if(empty($qr)){

            return false;
        }

        if(!empty($qr['channel_id'])){

            $channel = $channel_model->where(['id'=>$qr['channel_id']])->where('status','>',-1)->find();

            if(!empty($channel)){

                return false;
            }

            $qr_model->dataUpdate(['id'=>$qr['id']],['channel_id'=>0]);
        }

        $user = $user_model->dataInfo(['openid'=>$open_id,'status'=>1]);

        if(!empty($user)){

            $channel = $channel_model->where(['user_id'=>$user['id']])->where('status','in',[1,2,3])->find();

            if(!empty($channel)){

                return false;
            }
        }

        $url = 'https://'.$_SERVER['HTTP_HOST'].'/h5/#/user/pages/channel/apply?channel_qr_id='.$qr['id'];

        $text = '<a href = "'.$url.'">请点击申请渠道商</a>';

        $this->SRCnt($xml,$text);

        return true;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-11-23 15:39
     * @功能说明:绑定分销上下级
     */
    public function bindPid($open_id,$pid,$unionid=''){

        $pid = str_replace('d_','',$pid);

        $db_code_do = getConfigSetting($this->_uniacid,'db_code_do');

        if($db_code_do==1){

            $count_data = explode(',',$pid);

            if(count($count_data)<2){

                return true;
            }

            $pid = $count_data[0];
        }

        $user_model = new \app\massage\model\User();

        $user = $user_model->dataInfo(['web_openid'=>$open_id,'status'=>1]);

        $bind_model = new QrBind();

        $dis = [

            'openid' => $open_id
        ];

        $find = $bind_model->dataInfo($dis);

        $fx_time_type = getConfigSetting($this->_uniacid,'fx_time_type');

        $fx_time_day  = getConfigSetting($this->_uniacid,'fx_time_day');

        $insert = [

            'uniacid' => $this->_uniacid,

            'openid'  => $open_id,

            'pid'     => $pid,

            'create_time'=> time(),

            'over_time'  => time()+$fx_time_day*86400,

            'forever'    => $fx_time_type==1?0:1,

            'unionid' => $unionid
        ];

        if(!empty($find)){

            $bind_model->dataUpdate(['id'=>$find['id']],$insert);
        }else{

            $bind_model->insert($insert);
        }
        //添加扫码记录
        $insert = [

            'uniacid' => $this->_uniacid,

            'user_id' => !empty($user['id'])?$user['id']:0,

            'qr_id'   => $pid,

            'type'    => 2,

            'open_id' => $open_id,
            //是否是新用户扫码 2代表没有进入平台 新用户注册后会改为1
            'is_new'  => !empty($user)?0:2,
        ];

        $scan_model = new ChannelScanQr();

        $scan_model->dataAdd($insert);

        return true;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-29 18:28
     * @功能说明:绑定渠道嘛
     */
    public function bindChannelQr($open_id,$qr_code,$unionid=''){

        $channel_model = new UserChannel();

        $user_model = new \app\massage\model\User();

        $user = $user_model->dataInfo(['web_openid'=>$open_id,'status'=>1]);
        //是否设置的永久绑定
        $auth = AdminMenu::getAuthList((int)$this->_uniacid,['channelforeverbind']);

        $channel_bind_type = getConfigSetting((int)$this->_uniacid,'channel_bind_type');

        $channel_bind_forever = $auth['channelforeverbind'];

        if(!empty($user)){

            $dis['user_id'] = $user['id'];
        }

        $dis['open_id'] = $open_id;

        if($channel_bind_type==1) {
            //永久绑定
            if ($channel_bind_forever == true) {

                $find = $channel_model->whereOr($dis)->find();

                $time = strtotime(date('Y-m-d', time()));
                //只有当日注册的用户可以扫
                if (!empty($user) && $user['create_time'] < $time) {

                    $find = 1;
                }

            } else {

                $channel_model->whereOr($dis)->delete();
            }
        }else{
            //时效性内不能换绑
            if($channel_bind_forever==true){

                $find = $channel_model->whereOr($dis)->find();
            }else{

                $find = $channel_model->where('over_time','>',time())->where(function ($query) use ($dis){
                    $query->whereOr($dis);
                })->find();

                if(empty($find)){

                    $channel_model->whereOr($dis)->delete();
                }
            }
        }

        $user_channel_over_time = getConfigSetting($this->_uniacid,'user_channel_over_time');

        $qr_model = new ChannelQr();

        $qr = $qr_model->dataInfo(['code'=>$qr_code,'status'=>1]);

        if(!empty($qr['channel_id'])){

            $channels_model = new ChannelList();

            $channel = $channels_model->where(['id'=>$qr['channel_id']])->where('status','in',[2,3])->find();

            if(!empty($channel['time_type'])&&$channel['time_type']==1){

                $user_channel_over_time = $channel['time'];
            }
        }

        if(!empty($qr)){

            if(empty($find)){

                $insert = [

                    'uniacid'   => $this->_uniacid,

                    'user_id'   => !empty($user['id'])?$user['id']:0,

                    'open_id'   => $open_id,

                    'channel_id'=> $qr['channel_id'],

                    'channel_qr_id'=> $qr['id'],

                    'channel_qr_code'=> $qr['code'],

                    'unionid' => $unionid,

                    'over_time' => time()+$user_channel_over_time*3600
                ];

                $channel_model->dataAdd($insert);
            }
            //添加扫码记录
            $insert = [

                'uniacid' => $this->_uniacid,

                'user_id' => !empty($user['id'])?$user['id']:0,

                'qr_id'   => $qr['id'],

                'qr_code' => $qr['code'],

                'city'    => $qr['city'],

                'province'=> $qr['province'],

                'open_id' => $open_id,
                //是否是新用户扫码
                'is_new'  => !empty($user)?0:2,
            ];

//            $auth = getPromotionRoleAuth(2,$this->_uniacid);
//
//            if($auth!=1){
//
//                $insert['wechat_type'] = $insert['type'];
//
//                $insert['type'] = 0;
//            }

            $scan_model = new ChannelScanQr();

            $scan_model->dataAdd($insert);
        }

        return true;
    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-29 18:28
     * @功能说明:绑定渠道商
     */
    public function bindChannel($open_id,$channel_id,$unionid=''){

        $channel_model = new UserChannel();

        $user_model = new \app\massage\model\User();

        $user = $user_model->dataInfo(['web_openid'=>$open_id,'status'=>1]);
        //是否设置的永久绑定
        $auth = AdminMenu::getAuthList((int)$this->_uniacid,['channelforeverbind']);

        $channel_bind_forever = $auth['channelforeverbind'];

        if(!empty($user)){

            $dis['user_id'] = $user['id'];
        }

        $dis['open_id'] = $open_id;

        $channel_bind_type = getConfigSetting((int)$this->_uniacid,'channel_bind_type');
        //时效性内可以换绑
        if($channel_bind_type==1){
            //永久绑定
            if($channel_bind_forever==true){

                $find = $channel_model->whereOr($dis)->find();

                $time = strtotime(date('Y-m-d',time()));
                //只有当日注册的用户可以扫
                if(!empty($user)&&$user['create_time']<$time){

                    $find = 1;
                }

            }else{

                $channel_model->whereOr($dis)->delete();
            }
        }else{
           //时效性内不能换绑
           if($channel_bind_forever==true){

               $find = $channel_model->whereOr($dis)->find();
           }else{

               $find = $channel_model->where('over_time','>',time())->where(function ($query) use ($dis){
                   $query->whereOr($dis);
               })->find();

               if(empty($find)){

                   $channel_model->whereOr($dis)->delete();
               }
           }
        }

        $user_channel_over_time = getConfigSetting($this->_uniacid,'user_channel_over_time');

        if(!empty($channel_id)){

            $channels_model = new ChannelList();

            $channel = $channels_model->where(['id'=>$channel_id])->where('status','in',[2,3])->find();

            if(!empty($channel['time_type'])&&$channel['time_type']==1){

                $user_channel_over_time = $channel['time'];
            }
        }

        if(empty($find)){

            $insert = [

                'uniacid'   => $this->_uniacid,

                'user_id'   => !empty($user['id'])?$user['id']:0,

                'open_id'   => $open_id,

                'channel_id'=> $channel_id,

                'over_time' => time()+$user_channel_over_time*3600,

                'unionid' => $unionid
            ];

            $res = $channel_model->dataAdd($insert);

           // return $res;
        }
        //添加扫码记录
        $insert = [

            'uniacid' => $this->_uniacid,

            'user_id' => !empty($user['id'])?$user['id']:0,

            'qr_id'   => $channel_id,

            'type'    => 9,

            'open_id' => $open_id,
            //是否是新用户扫码
            'is_new'  => !empty($user)?0:2,
        ];

//        $auth = getPromotionRoleAuth(2,$this->_uniacid);
//
//        if($auth!=1){
//
//            $insert['wechat_type'] = $insert['type'];
//
//            $insert['type'] = 0;
//        }

        $scan_model = new ChannelScanQr();

        $scan_model->dataAdd($insert);

        return true;
    }


    /**
     * @param $postObj
     * @param $text
     * @功能说明:公众号自动回复内容
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-07-24 14:05
     */
    public function SRCnt($postObj,$text){

        $toUser = $postObj['FromUserName'];  //获取用户OpenID

        $fromUser = $postObj['ToUserName'];  //获取公众号原始ID

        $time = time();  //获取当前时间戳

        $msgType = 'text';  //回复消息类型为文本

        $content = $text;  //回复消息内容

        $template = '<xml>

        <ToUserName><![CDATA[%s]]></ToUserName>

        <FromUserName><![CDATA[%s]]></FromUserName>

        <CreateTime>%s</CreateTime>

        <MsgType><![CDATA[%s]]></MsgType>

        <Content><![CDATA[%s]]></Content>

        </xml>';

        $info = sprintf($template, $toUser, $fromUser, $time, $msgType, $content);  //构造回复消息XML

        echo $info;  //返回回复消息给微信服务器
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-06-20 13:58
     * @功能说明:获取技师地图数据
     */
    public function getMapCoach(){

        $input = $this->_param;

        $dis[] = ['uniacid','=',666];

        $dis[] = ['status','=',2];

        if(!empty($input['city_id'])){

            $dis[] = ['city_id','=',$input['city_id']];

        }

        $coach_model = new Coach();

        $data = $coach_model->where($dis)->field('id as coach_id,lng,lat,coach_name,work_img')->select()->toArray();

        return $this->success($data);
    }







}
