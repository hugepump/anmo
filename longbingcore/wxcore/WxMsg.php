<?php
declare(strict_types=1);

namespace longbingcore\wxcore;

use app\Common\extend\wxWork\work;
use app\Common\LongbingServiceNotice;

use think\facade\Db;

class WxMsg{

    static protected $uniacid;

    public function __construct($uniacid)
    {
       self::$uniacid = $uniacid;

        $this->config    = $this->getConfig($uniacid);

        $this->appid     = $this->getAppid();

//        $this->appsecret = $this->getAppsecret();

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-04-27 17:35
     * @功能说明:企业微信消息
     */
    public function WxMsg($to_user,$title='',$page='',$description='',$content_item=''){

        $data = [
            //接受者
            'touser' => $to_user,
            //类型
            'msgtype'=> 'miniprogram_notice',

            'miniprogram_notice' => [

                'appid' => $this->appid ,
                //放大第一个字段
//                'emphasis_first_item' => true,
            ]
        ];


        if(!empty($page)){
            //路径
            $data['miniprogram_notice']['page'] = $page;
        }
        if(!empty($title)){
            //标题
            $data['miniprogram_notice']['title'] = $title;
        }
        if(!empty($content_item)){
            //内容
            $data['miniprogram_notice']['content_item'] = $content_item;
        }
        if(!empty($description)){
            //描述
            $data['miniprogram_notice']['description'] = $description;
        }
        //初始化配置模型
        $service_model = new WxSetting(self::$uniacid);
        //发送消息
        $result = $service_model->sendCompanyMsg($data);

        return $result;
    }


    /**
     * 功能说明 获取appid
     *
     * @return mixed|null
     * @author chenniang(龙兵科技)
     * @DataTime: 2019-12-25 17:36
     */
    protected function getAppid()
    {
        if(isset($this->config['appid'])) return $this->config['appid'];
        return null;
    }

    /**
     * 功能说明 获取appsecret
     *
     * @return mixed|null
     * @author chenniang(龙兵科技)
     * @DataTime: 2019-12-25 17:36
     */
    protected function getAppsecret()
    {
        if(isset($this->config['app_secret'])) return $this->config['app_secret'];
        return null;
    }

    /**
     * 功能说明 获取配置信息
     *
     * @param $uniacid
     * @return array|bool|mixed|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author chenniang(龙兵科技)
     * @DataTime: 2019-12-25 17:35
     */
    public function getConfig($uniacid)
    {
        //config key
        $key    = 'longbing_card_app_config_' . $uniacid;
        //获取config
        $config = getCache($key, $uniacid);
        //判断缓存是否存在
        if(!empty($config)) return $config;
        //获取数据
        $config = Db::name('longbing_card_config')->where(['uniacid'=>$uniacid])->find();

        if(empty($config)){
            Db::name('longbing_card_config')->insert(['uniacid'=>$uniacid]);
            $config = Db::name('longbing_card_config')->where(['uniacid'=>$uniacid])->find();
        }
        //判断数据是否存在
        if(!empty($config)) setCache($key ,$config ,3600,$uniacid);
        //返回数据
        return $config;
    }






}