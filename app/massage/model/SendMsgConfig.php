<?php
namespace app\massage\model;

use AlibabaCloud\Client\AlibabaCloud;
use app\baiying\model\BaiYingPhoneRecord;
use app\BaseModel;
use Exception;
use longbingcore\wxcore\BaiYing;
use longbingcore\wxcore\WxSetting;
use think\facade\Db;

class SendMsgConfig extends BaseModel
{
    //定义表名
    protected $name = 'massage_send_msg_config';


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
        if(!empty($config['gzh_appid'])){

            $update = [

                'help_tmpl_id' => $config['help_tmpl_id'],
                'order_tmp_id' => $config['order_tmp_id'],
                'cancel_tmp_id' => $config['cancel_tmp_id'],
                'coachupdate_tmp_id' => $config['coachupdate_tmp_id'],
                'gzh_appid' => $config['gzh_appid'],
            ];

            $this->dataUpdate(['id'=>$data['id']],$update);

            $prefix = longbing_get_prefix();
            //执行sql删除废弃字段
            $sql = <<<updateSql
            
ALTER TABLE `{$prefix}shequshop_school_config` DROP COLUMN  `help_tmpl_id`;
ALTER TABLE `{$prefix}shequshop_school_config` DROP COLUMN  `order_tmp_id`;
ALTER TABLE `{$prefix}shequshop_school_config` DROP COLUMN  `cancel_tmp_id`;
ALTER TABLE `{$prefix}shequshop_school_config` DROP COLUMN  `coachupdate_tmp_id`;
ALTER TABLE `{$prefix}shequshop_school_config` DROP COLUMN  `gzh_appid`;

                

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
     * @DataTime: 2023-08-01 10:44
     * @功能说明:退款通知代理商
     */
    public function webRefundMsgAdmin($order){

        $order_wechat_agent_status = getConfigSetting($order['uniacid'],'order_wechat_agent_status');

        if(!empty($order['admin_id'])&&$order_wechat_agent_status==1){

            $uniacid = $order['uniacid'];

            $config_model = new SendMsgConfig();

            $config_model->initData($uniacid);

            $x_config = $config_model->dataInfo(['uniacid'=>$uniacid]);

            if (empty($x_config['gzh_appid']) || empty($x_config['cancel_tmp_id'])) {

                return false;
            }

            $admin_model = new Admin();

            $user_model = new User();

            $user_id = $admin_model->where(['id'=>$order['admin_id']])->value('user_id');
            //获取楼长openid
            $openid = $user_model->where(['id'=>$user_id])->value('web_openid');

            $wx_setting = new WxSetting($uniacid);

            $access_token = $wx_setting->getGzhToken();

            $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token={$access_token}";

            $order_model = new Order();

            $start_time = $order_model->where(['id' => $order['order_id']])->value('start_time');

            $key = explode('&',$x_config['cancel_tmp_id']);

            for ($i=1;$i<4;$i++){

                $arr[$i] = !empty($key[$i])?$key[$i]:'keyword'.$i;
            }

            $data = [
                //用户小程序openid
                'touser'=> $openid,
                //公众号appid
                'appid' => $x_config['gzh_appid'],

               // "url"   => 'https://' . $_SERVER['HTTP_HOST'] . '/h5/#/user/pages/order/detail?id=' . $order['order_id'],
                "url"   => 'https://' . $_SERVER['HTTP_HOST'] . '/h5/#/agent/pages/refund/detail?agent=1&id='. $order['id'],
                //公众号模版id
                'template_id' => $key[0],

                'data' => array(

                    'first' => array(

                        'value' => '您的技师有一笔订单正在申请退款',

                        'color' => '#93c47d',
                    ),

                    $arr[1] => array(

                        'value' => implode(',', array_column($order['order_goods'], 'goods_name')),

                        'color' => '#93c47d',
                    ),
                    //预约时间
                    $arr[2] => array(
                        //内容
                        'value' => date('Y-m-d H:i', $start_time),

                        'color' => '#0000ff',
                    ),
                    //取消原因
                    $arr[3] => array(
                        //内容
                        'value' => $order['text'],

                        'color' => '#0000ff',
                    ),

                )

            ];

            $data = json_encode($data);

            $tmp = [

                'url' => $url,

                'data' => $data,
            ];
            $rest = lbCurlPost($tmp['url'], $tmp['data']);

            $rest = json_decode($rest, true);

            return $rest;

        }
        return true;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-01 10:59
     * @功能说明:退款通知代理商
     */
    public function wechatRefundMsgAdmin($order){

        if(empty($order['admin_id'])){

            return false;
        }

        $uniacid = $order['uniacid'];

        $config_model = new SendMsgConfig();

        $config_model->initData($uniacid);

        $x_config = $config_model->dataInfo(['uniacid'=>$uniacid]);

        if (empty($x_config['gzh_appid']) || empty($x_config['order_tmp_id'])) {

            return false;
        }

        $config = longbingGetAppConfig($uniacid);

        $admin_model = new Admin();

        $user_model = new User();

        $user_id = $admin_model->where(['id'=>$order['admin_id']])->value('user_id');
        //获取楼长openid
        $openid = $user_model->where(['id'=>$user_id])->value('wechat_openid');

        $access_token = longbingGetAccessToken($uniacid);

        $page = "user/pages/order/detail?id=".$order['id'];
        //post地址
        $url = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/uniform_send?access_token={$access_token}";

        $order_model = new Order();

        $start_time = $order_model->where(['id' => $order['order_id']])->value('start_time');

        $data = [
            //用户小程序openid
            'touser' => $openid,

            'mp_template_msg' => [
                //公众号appid
                'appid' => $x_config['gzh_appid'],

                "url" => "http://weixin.qq.com/download",
                //公众号模版id
                'template_id' => $x_config['cancel_tmp_id'],

                'miniprogram' => [
                    //小程序appid
                    'appid' => $config['appid'],
                    //跳转小程序地址
                    'page' => $page,
                ],
                'data' => array(

                    'first' => array(

                        'value' =>  '您的技师有一笔订单正在申请退款',

                        'color' => '#93c47d',
                    ),

                    'keyword1' => array(

                        'value' => implode(',', array_column($order['order_goods'], 'goods_name')),

                        'color' => '#93c47d',
                    ),
                    //预约时间
                    'keyword2' => array(
                        //内容
                        'value' => date('Y-m-d H:i', $start_time),

                        'color' => '#0000ff',
                    ),
                    //取消原因
                    'keyword3' => array(
                        //内容
                        'value' => $order['text'],

                        'color' => '#0000ff',
                    ),

                )
            ],
        ];

        $data = json_encode($data);

        $tmp = [

            'url' => $url,

            'data' => $data,
        ];
        $rest = lbCurlPost($tmp['url'], $tmp['data']);

        $rest = json_decode($rest, true);

        return $rest;
    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-01 10:44
     * @功能说明:退款通知平台管理员
     */
    public function wechatRefundMsgCompany($order){

        $uniacid = $order['uniacid'];

        $config_model = new SendMsgConfig();

        $user_model = new User();

        $config_model->initData($uniacid);

        $x_config = $config_model->dataInfo(['uniacid'=>$uniacid]);

        if (empty($x_config['gzh_appid']) || empty($x_config['order_tmp_id'])) {

            return false;
        }
        //获取平台设置的管理员
        $admin_id = getConfigSetting($order['uniacid'],'wechat_tmpl_admin');

        if(empty($admin_id)){

            return false;
        }

        $admin_id = explode(',',$admin_id);

        foreach ($admin_id as $value){
            //获取楼长openid
            $openid = $user_model->where(['id'=>$value])->value('openid');

            $config = longbingGetAppConfig($uniacid);

            $access_token = longbingGetAccessToken($uniacid);

            $page = "user/pages/order/detail?id=".$order['id'];
            //post地址
            $url  = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/uniform_send?access_token={$access_token}";

            $order_model = new Order();

            $start_time = $order_model->where(['id' => $order['order_id']])->value('start_time');

            $data = [
                //用户小程序openid
                'touser' => $openid,

                'mp_template_msg' => [
                    //公众号appid
                    'appid' => $x_config['gzh_appid'],

                    "url" => "http://weixin.qq.com/download",
                    //公众号模版id
                    'template_id' => $x_config['cancel_tmp_id'],

                    'miniprogram' => [
                        //小程序appid
                        'appid' => $config['appid'],
                        //跳转小程序地址
                        'page' => $page,
                    ],
                    'data' => array(

                        'first' => array(

                            'value' =>  '您的技师有一笔订单正在申请退款',

                            'color' => '#93c47d',
                        ),

                        'keyword1' => array(

                            'value' => implode(',', array_column($order['order_goods'], 'goods_name')),

                            'color' => '#93c47d',
                        ),
                        //预约时间
                        'keyword2' => array(
                            //内容
                            'value' => date('Y-m-d H:i', $start_time),

                            'color' => '#0000ff',
                        ),
                        //取消原因
                        'keyword3' => array(
                            //内容
                            'value' => $order['text'],

                            'color' => '#0000ff',
                        ),

                    )
                ],
            ];

            $data = json_encode($data);

            $tmp = [

                'url' => $url,

                'data' => $data,
            ];
            $rest = lbCurlPost($tmp['url'], $tmp['data']);

            $rest = json_decode($rest, true);

            return $rest;
        }

        return true;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-01 10:44
     * @功能说明:退款通知平台管理员
     */
    public function webRefundMsgCompany($order){

        $order_wechat_admin_status = getConfigSetting($order['uniacid'],'order_wechat_admin_status');
        //代理商订单是否通知平台
        if(!empty($order['admin_id'])&&$order_wechat_admin_status==0){

            return true;
        }

        $user_model = new User();

        $uniacid = $order['uniacid'];

        $config_model = new SendMsgConfig();

        $config_model->initData($uniacid);

        $x_config = $config_model->dataInfo(['uniacid'=>$uniacid]);

        if (empty($x_config['gzh_appid']) || empty($x_config['cancel_tmp_id'])) {

            return false;
        }
        //获取平台设置的管理员
        $admin_id = getConfigSetting($order['uniacid'],'wechat_tmpl_admin');

        if(empty($admin_id)){

            return false;
        }

        $admin_id = explode(',',$admin_id);

        foreach ($admin_id as $value){
            //获取楼长openid
            $openid = $user_model->where(['id'=>$value])->value('web_openid');

            $wx_setting = new WxSetting($uniacid);

            $access_token = $wx_setting->getGzhToken();

            $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token={$access_token}";

            $order_model = new Order();

            $start_time = $order_model->where(['id' => $order['order_id']])->value('start_time');

            $key = explode('&',$x_config['cancel_tmp_id']);

            for ($i=1;$i<4;$i++){

                $arr[$i] = !empty($key[$i])?$key[$i]:'keyword'.$i;
            }

            $data = [
                //用户小程序openid
                'touser'=> $openid,
                //公众号appid
                'appid' => $x_config['gzh_appid'],

               // "url"   => 'https://' . $_SERVER['HTTP_HOST'] . '/h5/#/user/pages/order/detail?id=' . $order['order_id'],
                "url"   => 'https://' . $_SERVER['HTTP_HOST'] .  '/h5/#/agent/pages/refund/detail?agent=0&id='. $order['id'],
                //公众号模版id
                'template_id' => $key[0],

                'data' => array(

                    'first' => array(

                        'value' =>  '您的技师有一笔订单正在申请退款',

                        'color' => '#93c47d',
                    ),

                    $arr[1] => array(

                        'value' => implode(',', array_column($order['order_goods'], 'goods_name')),

                        'color' => '#93c47d',
                    ),
                    //预约时间
                    $arr[2] => array(
                        //内容
                        'value' => date('Y-m-d H:i', $start_time),

                        'color' => '#0000ff',
                    ),
                    //取消原因
                    $arr[3] => array(
                        //内容
                        'value' => $order['text'],

                        'color' => '#0000ff',
                    ),

                )
            ];

            $data = json_encode($data);

            $tmp = [

                'url' => $url,

                'data' => $data,
            ];
            $rest = lbCurlPost($tmp['url'], $tmp['data']);

            $rest = json_decode($rest, true);

        }

        return isset($rest)?$rest:true;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-01 16:33
     * @功能说明:获取通知的类型
     */
    public function getNoticeType($type){

        switch ($type){

            case 1:
                $data['text'] = '未接单通知';
                break;
            case 2:
                $data['text'] = '拒单通知';
                break;
            case 3:
                $data['text'] = '迟到通知';
                break;
            case 4:
                $data['text'] = '跳单通知';
                break;
        }

        return $data;
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-01 10:44
     * @功能说明:订单服务迟到通知
     */
    public function webOrderServiceNoticeCompany($order,$type){

        $order_wechat_admin_status = getConfigSetting($order['uniacid'],'order_wechat_admin_status');
        //代理商订单是否通知平台
        if(!empty($order['admin_id'])&&$order_wechat_admin_status==0){

            return true;
        }
        //通知类型
        $text = $this->getNoticeType($type);

        $user_model = new User();

        $uniacid = $order['uniacid'];

        $config_model = new SendMsgConfig();

        $config_model->initData($uniacid);

        $x_config = $config_model->dataInfo(['uniacid'=>$uniacid]);

        if (empty($x_config['gzh_appid']) || empty($x_config['order_service_tmpl_id'])) {

            return true;
        }
        //获取平台设置的管理员
        $admin_id = getConfigSetting($order['uniacid'],'wechat_tmpl_admin');

        if(empty($admin_id)){

            return true;
        }

        $admin_id = explode(',',$admin_id);

        foreach ($admin_id as $value){
            //获取楼长openid
            $openid = $user_model->where(['id'=>$value])->value('openid');

            $wx_setting = new WxSetting($uniacid);

            $access_token = $wx_setting->getGzhToken();

            $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token={$access_token}";

            $key = explode('&',$x_config['order_service_tmpl_id']);

            for ($i=1;$i<3;$i++){

                $arr[$i] = !empty($key[$i])?$key[$i]:'keyword'.$i;
            }

            $data = [
                //用户小程序openid
                'touser'=> $openid,
                //公众号appid
                'appid' => $x_config['gzh_appid'],

              //  "url"   => 'https://' . $_SERVER['HTTP_HOST'] . '/h5/#/user/pages/order/detail?id=' . $order['id'],
                "url"   => 'https://' . $_SERVER['HTTP_HOST'] . '/h5/',
                //公众号模版id
                'template_id' => $key[0],

                'data' => array(

                    $arr[1] => array(

                        'value' => $order['order_code'],

                        'color' => '#93c47d',
                    ),
                    //预约时间
                    $arr[2] => array(
                        //内容
                        'value' => $text['text'],

                        'color' => '#0000ff',
                    )
                )

            ];

            $data = json_encode($data);

            $tmp = [

                'url' => $url,

                'data' => $data,
            ];
            $rest = lbCurlPost($tmp['url'], $tmp['data']);

            $rest = json_decode($rest, true);
        }

        return $rest;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-01 10:44
     * @功能说明:订单服务通知通知代理商
     */
    public function webOrderServiceNoticeAdmin($order,$type){
        //是否通知代理商
        $order_wechat_agent_status = getConfigSetting($order['uniacid'],'order_wechat_agent_status');

        if(!empty($order['admin_id'])&&$order_wechat_agent_status==1){
            //获取通知类型
            $text = $this->getNoticeType($type);

            $uniacid = $order['uniacid'];

            $config_model = new SendMsgConfig();

            $config_model->initData($uniacid);

            $x_config = $config_model->dataInfo(['uniacid'=>$uniacid]);

            if (empty($x_config['gzh_appid']) || empty($x_config['order_service_tmpl_id'])) {

                return true;
            }

            $admin_model = new Admin();

            $user_model = new User();

            $user_id = $admin_model->where(['id'=>$order['admin_id']])->value('user_id');
            //获取楼长openid
            $openid = $user_model->where(['id'=>$user_id])->value('web_openid');

            $wx_setting = new WxSetting($uniacid);

            $access_token = $wx_setting->getGzhToken();

            $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token={$access_token}";

            $key = explode('&',$x_config['order_service_tmpl_id']);

            for ($i=1;$i<3;$i++){

                $arr[$i] = !empty($key[$i])?$key[$i]:'keyword'.$i;
            }

            $data = [
                //用户小程序openid
                'touser'=> $openid,
                //公众号appid
                'appid' => $x_config['gzh_appid'],

               // "url"   => 'https://' . $_SERVER['HTTP_HOST'] . '/h5/#/user/pages/order/detail?id=' . $order['id'],
                "url"   => 'https://' . $_SERVER['HTTP_HOST'] . '/h5',
                //公众号模版id
                'template_id' => $key[0],

                'data' => array(

                    $arr[1] => array(

                        'value' => $order['order_code'],

                        'color' => '#93c47d',
                    ),
                    //预约时间
                    $arr[2] => array(
                        //内容
                        'value' => $text['text'],

                        'color' => '#0000ff',
                    )

                )

            ];

            $data = json_encode($data);

            $tmp = [

                'url' => $url,

                'data' => $data,
            ];
            $rest = lbCurlPost($tmp['url'], $tmp['data']);

            $rest = json_decode($rest, true);

        }
        return true;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-02 14:50
     * @功能说明:订单服务通知
     */
    public function orderServiceNotice($uniacid){

        $key = 'orderServiceNotice_key_value'.date('Y-m-d H:i');

        incCache($key,1,$uniacid);

        $value = getCache($key,$uniacid);

        if($value==1){
            //迟到通知
            $this->latOrderNotice($uniacid);
            //未接单通知
            $this->unacceptedOrdersNotice($uniacid);
            //跳单通知
            $this->jumpOrdersNotice($uniacid);
            //未支付通知
            $this->unpaidOrdersNotice($uniacid);
        }

        decCache($key,1,$uniacid);

        return true;
    }





    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-01 18:26
     * @功能说明:技师迟到提醒
     */
    public function latOrderNotice($uniacid){

        $setting = getConfigSettingArr($uniacid,['wechat_tmpl','service_lat_type','service_lat_minute']);

        $order_data_model = new OrderData();

        $dis[] = ['a.uniacid','=',$uniacid];

        $dis[] = ['a.is_add','=',0];

        $dis[] = ['b.late_notice','=',0];

        $dis[] = ['a.pay_type','in',[3,4]];

        $notice_model = new NoticeList();

        $dis[] = ['a.store_id','=',0];
        //服务开始前未到达
        if($setting['service_lat_type']==0){

            $data = Db::name('massage_service_order_list')
                    ->alias('a')
                    ->join('massage_service_order_list_data b','a.id = b.order_id')
                    ->where('start_time','<',time()+$setting['service_lat_minute']*60)
                    ->where($dis)
                    ->field('a.*')
                    ->order('id desc')
                    ->limit(10)
                    ->select()
                    ->toArray();

        }else{

            $data = Db::name('massage_service_order_list')
                ->alias('a')
                ->join('massage_service_order_list_data b','a.id = b.order_id')
                ->where('start_time','<',time()-$setting['service_lat_minute']*60)
                ->where($dis)
                ->field('a.*')
                ->order('id desc')
                ->limit(10)
                ->select()
                ->toArray();
            //服务开始后未到达
          //  $data = $order_model->where('start_time','<',time()-$setting['service_lat_minute']*60)->where($dis)->order('id desc')->limit(10)->select()->toArray();
        }

        if(!empty($data)){

            foreach ($data as $v){

                $notice_model->dataAdd($uniacid,$v['id'],5,$v['admin_id']);

                $order_data_model->dataUpdate(['order_id'=>$v['id']],['late_notice'=>1]);

                $this->webOrderServiceNoticeAdmin($v,3);
                //需要发送模版消息
                if($setting['wechat_tmpl']==1){

                    $this->webOrderServiceNoticeCompany($v,3);
                }
            }
        }

        return true;
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-01 18:26
     * @功能说明:技师未接单通知
     */
    public function unacceptedOrdersNotice($uniacid){

        $setting = getConfigSettingArr($uniacid,['wechat_tmpl','coach_receiving_minute']);

        $order_model = new Order();

        $order_data_model = new OrderData();

        $dis[] = ['a.uniacid','=',$uniacid];

        $dis[] = ['a.pay_type','=',2];

        $dis[] = ['b.receiving_order_notice','=',0];

        $notice_model = new NoticeList();

        $data = Db::name('massage_service_order_list')
            ->alias('a')
            ->join('massage_service_order_list_data b','a.id = b.order_id')
            ->where('a.pay_time','<',time()-$setting['coach_receiving_minute']*60)
            ->where($dis)
            ->field('a.*')
            ->order('id desc')
            ->limit(10)
            ->select()
            ->toArray();

        if(!empty($data)){

            foreach ($data as $v){

                $find = $notice_model->dataInfo(['type'=>4,'order_id'=>$v['id']]);

                $notice_model->dataAdd($uniacid,$v['id'],4,$v['admin_id']);

                $order_data_model->dataUpdate(['order_id'=>$v['id']],['receiving_order_notice'=>1]);
                //需要发送模版消息
                if(empty($find)){

                    $this->webOrderServiceNoticeAdmin($v,1);

                    if($setting['wechat_tmpl']==1){

                        $this->webOrderServiceNoticeCompany($v,1);
                    }
                }
            }
        }

        return true;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-01 18:26
     * @功能说明:技师跳单
     */
    public function jumpOrdersNotice($uniacid){

        $setting = getConfigSettingArr($uniacid,['wechat_tmpl','jump_order_minute','jump_order_distance']);

        $order_model = new Order();

        $coach_model = new Coach();

        $notice_model = new NoticeList();

        $dis[] = ['uniacid','=',$uniacid];

        $dis[] = ['pay_type','=',7];

        $dis[] = ['is_safe','=',0];

        $dis[] = ['store_id','=',0];

        $dis[] = ['is_add','=',0];

        $dis[] = ['coach_id','>',0];

        $start_time = time()-$setting['jump_order_minute']*60-3600;

        $end_time   = time()-$setting['jump_order_minute']*60;

        $dis[] = ['order_end_time','between',"$start_time,$end_time"];

        $data = $order_model->where($dis)->order('id desc')->limit(10)->select()->toArray();

        if(!empty($data)){

            foreach ($data as $v){

                $lat = !empty($v['address_info'])?$v['address_info']['lat']:0;

                $lng = !empty($v['address_info'])?$v['address_info']['lng']:0;

                $coach_info = $coach_model->dataInfo(['id'=>$v['coach_id']]);

                $coach_lat  = !empty($coach_info)?$coach_info['lat']:90;

                $coach_lng  = !empty($coach_info)?$coach_info['lng']:90;
                //获取距离
                $distance   = getDriveDistance($coach_lng,$coach_lat,$lng,$lat,$uniacid);
                //有跳单风险
                if($setting['jump_order_distance']>0&&$distance<$setting['jump_order_distance']*1000){

                    $notice_model->dataAdd($uniacid,$v['id'],6,$v['admin_id']);

                    $this->webOrderServiceNoticeAdmin($v,4);
                    //需要发送模版消息
                    if($setting['wechat_tmpl']==1){

                        $this->webOrderServiceNoticeCompany($v,4);
                    }

                    $order_model->dataUpdate(['id'=>$v['id']],['is_safe'=>2]);
                }else{

                    $order_model->dataUpdate(['id'=>$v['id']],['is_safe'=>1]);
                }
            }
        }

        return true;
    }


    /**
     * @param $order
     * @功能说明:下单模版消息通知平台和代理商
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-11-09 11:26
     */
    public function sendOrderTmplAdmin($order){

        $config_model = new HelpConfig();

        $user_model   = new User();

        $admin_model  = new Admin();

        $x_config = $this->dataInfo(['uniacid'=>$order['uniacid']]);

        if (empty($x_config['gzh_appid']) || empty($x_config['order_tmp_id'])) {

            return false;
        }

        $virtual_config_model = new \app\virtual\model\Config();

        $mobile_auth = $virtual_config_model->getVirtualAuth($order['uniacid']);

        $config = $config_model->dataInfo(['uniacid'=>$order['uniacid']]);

        $wx_setting = new WxSetting($order['uniacid']);

        $access_token = $wx_setting->getGzhToken();

        $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token={$access_token}";

        $key = explode('&',$x_config['order_tmp_id']);

        for ($i=1;$i<6;$i++){

            $arr[$i] = !empty($key[$i])?$key[$i]:'keyword'.$i;
        }

        $coach_model = new Coach();

        $coach_name = $coach_model->where(['id'=>$order['coach_id']])->value('coach_name');

        $order['address_info']['address_info'] = !empty($order['address_info']['address_info'])?$order['address_info']['address_info']:$order['address_info']['address'];

        if($config['order_tmpl_admin_status']==1&&is_array($config['order_tmpl_text'])&&(empty($order['admin_id'])||$config['order_tmpl_notice_admin'])){

            foreach ($config['order_tmpl_text'] as $value){

                $openid = $user_model->where(['id'=>$value])->value('web_openid');

                $data = [
                    //用户小程序openid
                    'touser' => $openid,
                    //公众号appid
                    'appid' => $x_config['gzh_appid'],

                    "url" => 'https://' . $_SERVER['HTTP_HOST'] . '/h5/#/agent/pages/order/detail?agent=0&id=' . $order['id'],
                    //公众号模版id
                    'template_id' => $key[0],

                    'data' => array(
                        //服务名称
                        $arr[1] => array(

                            'value' => mb_substr(implode(',', array_column($order['order_goods'], 'goods_name')),0,20),

                            'color' => '#93c47d',
                        ),
                        //下单人
                        $arr[2]  => array(
                            //内容
                            'value' => mb_substr($order['address_info']['user_name'].'(技师:'.$coach_name.')',0,20),

                            'color' => '#0000ff',
                        ),
                        $arr[3] => array(
                            //内容
                            'value' => $mobile_auth==false?$order['address_info']['mobile']:'-',

                            'color' => '#0000ff',
                        ),
                        //客户电话
                        $arr[4] => array(
                            //内容
                            'value' => $order['order_code'],

                            'color' => '#0000ff',
                        ),
                        $arr[5] => array(
                            //内容
                            'value' => mb_substr($order['address_info']['address_info'],0,20),

                            'color' => '#0000ff',
                        ),
                    )
                ];

                $data = json_encode($data);

                $tmp = [

                    'url'  => $url,

                    'data' => $data,
                ];

                $rest = lbCurlPost($tmp['url'], $tmp['data']);
            }
        }
        //给代理商发
        if($config['order_tmpl_agent_status']==1&&!empty($order['admin_id'])){

            $user_id = $admin_model->where(['id'=>$order['admin_id']])->value('user_id');

            $openid = $user_model->where(['id'=>$user_id])->value('web_openid');

            $data = [
                //用户小程序openid
                'touser' => $openid,
                //公众号appid
                'appid' => $x_config['gzh_appid'],

                "url" => 'https://' . $_SERVER['HTTP_HOST'] . '/h5/#/agent/pages/order/detail?agent=1&id=' . $order['id'],
                //公众号模版id
                'template_id' => $key[0],

                'data' => array(
                    //服务名称
                    $arr[1] => array(

                        'value' => mb_substr(implode(',', array_column($order['order_goods'], 'goods_name')),0,20),

                        'color' => '#93c47d',
                    ),
                    //下单人
                    $arr[2]  => array(
                        //内容
                        'value' => mb_substr($order['address_info']['user_name'].'(技师:'.$coach_name.')',0,20),

                        'color' => '#0000ff',
                    ),
                    $arr[3] => array(
                        //内容
                        'value' => $mobile_auth==false?$order['address_info']['mobile']:'-',

                        'color' => '#0000ff',
                    ),
                    //客户电话
                    $arr[4] => array(
                        //内容
                        'value' => $order['order_code'],

                        'color' => '#0000ff',
                    ),
                    $arr[5] => array(
                        //内容
                        'value' => mb_substr($order['address_info']['address_info'],0,20),

                        'color' => '#0000ff',
                    ),
                )
            ];

           // dump($data);exit;

            $data = json_encode($data);

            $tmp = [

                'url'  => $url,

                'data' => $data,
            ];

            $rest = lbCurlPost($tmp['url'], $tmp['data']);

        }

        return true;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-05-15 10:35
     * @功能说明:发送优惠券通知
     */
    public function sendCouponNotice($user,$coupon){

        $x_config = $this->dataInfo(['uniacid'=>$coupon['uniacid']]);

        if (empty($x_config['gzh_appid']) || empty($x_config['coupon_notice_tmpl_id'])) {

            return false;
        }

        $end_time = $coupon['time_limit']==1?time()+$coupon['day']*86400:$coupon['end_time'];

        $user_model = new User();

        $dis[] = ['id','in',$user];

        $user = $user_model->userSelect($dis,'web_openid');

        $wx_setting = new WxSetting($coupon['uniacid']);

        $access_token = $wx_setting->getGzhToken();

        $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token={$access_token}";

        $key = explode('&',$x_config['coupon_notice_tmpl_id']);

        for ($i=1;$i<4;$i++){

            $arr[$i] = !empty($key[$i])?$key[$i]:'keyword'.$i;
        }

        if(!empty($user)){

            foreach ($user as $value){

                $data = [
                    //用户小程序openid
                    'touser' => $value['web_openid'],
                    //公众号appid
                    'appid' => $x_config['gzh_appid'],

                    "url" => 'https://' . $_SERVER['HTTP_HOST'] . '/h5',
                    //公众号模版id
                    'template_id' => $key[0],

                    'data' => array(
                        //卡券时间
                        $arr[1] => array(

                            'value' => date('Y-m-d H:i',$end_time),

                            'color' => '#93c47d',
                        ),
                        //卡券金额
                        $arr[2]  => array(

                            'value' => round($coupon['discount'],2).'元',

                            'color' => '#0000ff',
                        ),
                        //卡券名字
                        $arr[3] => array(
                            'value' => mb_substr($coupon['title'],0,20),

                            'color' => '#0000ff',
                        )
                    )
                ];

                $data = json_encode($data);

                $tmp = [

                    'url'  => $url,

                    'data' => $data,
                ];

                $rest = lbCurlPost($tmp['url'], $tmp['data']);
            }
            return $rest;
        }
        return true;
    }

    /**
     * @Desc: 未支付订单提醒
     * @param $uniacid
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @Auther: shurong
     * @Time: 2024/8/13 11:39
     */
    public function unpaidOrdersNotice($uniacid)
    {
        $timeout = getConfigSetting($uniacid,'order_pay_timeout_remind');

        if (empty($timeout)){

            return false;
        }

        $order_id = BaiYingPhoneRecord::where(['uniacid' => $uniacid, 'type' => 1])->column('order_id');

        $timeout = time() - $timeout * 60;

        $where = [
            ['create_time', '<', $timeout],

            ['pay_type', '=', 1],

            ['over_time', '>', time()],

            ['id', 'not in', $order_id]
        ];

        $order = Order::where($where)->select()->toArray();

        if (empty($order)){

            return false;
        }

        $address = OrderAddress::whereIn('order_id', array_column($order, 'id'))->field('uniacid,order_id,mobile,user_name')->select()->toArray();

        $binying = new BaiYing($uniacid);

        $res = $binying->water($address);

        return $res;
    }


    /**
     * @author chenniang
     * @DataTime: 2024-11-19 12:41
     * @功能说明:卡券过期提醒
     */
    public function couponOverNotice($uniacid){

        $dis[] = ['uniacid','=',$uniacid];

        $dis[] = ['have_over_notice','=',0];

        $dis[] = ['status','=',1];

        $dis[] = ['end_time','<',time()+86400*3];

        $coupon_model = new CouponRecord();

        $user_model   = new User();

        $x_config = $this->dataInfo(['uniacid'=>$uniacid]);

        if (empty($x_config['gzh_appid']) || empty($x_config['coupon_over_tmpl_id'])) {

            return false;
        }

        $wx_setting = new WxSetting($uniacid);

        $access_token = $wx_setting->getGzhToken();

        $data = $coupon_model->where($dis)->field('id as record_id,title,user_id,end_time')->limit(30)->order('id desc')->select()->toArray();

        $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token={$access_token}";

        $key = explode('&',$x_config['coupon_over_tmpl_id']);

        for ($i=1;$i<4;$i++){

            $arr[$i] = !empty($key[$i])?$key[$i]:'keyword'.$i;
        }

        if(!empty($data)){

            foreach ($data as $v){

                $coupon_model->where(['id'=>$v['record_id']])->update(['have_over_notice'=>1]);

                $openid = $user_model->where(['id'=>$v['user_id']])->value('web_openid');

                $data = [
                    //用户小程序openid
                    'touser' => $openid,
                    //公众号appid
                    'appid' => $x_config['gzh_appid'],

                    "url" => 'https://' . $_SERVER['HTTP_HOST'] . '/h5/?#/user/pages/coupon/list',
                    //公众号模版id
                    'template_id' => $key[0],

                    'data' => array(
                        //卡券名字
                        $arr[1]  => array(

                            'value' => $v['title'],

                            'color' => '#0000ff',
                        ),
                        //到期时间
                        $arr[2] => array(

                            'value' => date('Y-m-d H:i',$v['end_time']),

                            'color' => '#93c47d',
                        ),
                        //备注
                        $arr[3] => array(
                            'value' => '卡券即将过期',

                            'color' => '#0000ff',
                        )
                    )
                ];

                $data = json_encode($data);

                $tmp = [

                    'url'  => $url,

                    'data' => $data,
                ];

                $rest = lbCurlPost($tmp['url'], $tmp['data']);

               // dump($rest,$v,$data);exit;
            }

            return $rest;
        }

        return true;
    }







}