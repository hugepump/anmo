<?php
// +----------------------------------------------------------------------
// | Longbing [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright Chengdu longbing Technology Co., Ltd.
// +----------------------------------------------------------------------
// | Website http://longbing.org/
// +----------------------------------------------------------------------
// | Sales manager: +86-13558882532 / +86-13330887474
// | Technical support: +86-15680635005
// | After-sale service: +86-17361005938
// +----------------------------------------------------------------------

declare(strict_types=1);

namespace app\admin\controller;


include_once LONGBING_EXTEND_PATH . 'LongbingUpgrade.php';

use app\admin\model\WxUpload;
use app\AdminRest;
use app\AgentRest;
use LongbingUpgrade;
use think\facade\Db;
use think\facade\Env;

class AppUpgrade extends AdminRest
{

    /**
     * @author jingshuixian
     * @DataTime: 2020-06-08 15:00
     * @功能说明: 上传小程序代码
     */
    public function uploadWxapp(){
        $wxapp_version = isset($this->_param['wxapp_version'])?$this->_param['wxapp_version']:''; //微信小程序上传版本

        $model        = new WxUpload();

        $goods_name   = config('app.AdminModelList')['app_model_name'];

        $auth_uniacid = config('app.AdminModelList')['auth_uniacid'];

        $version_no   = config('app.AdminModelList')['version_no'];

        $upgrade      = new LongbingUpgrade($auth_uniacid , $goods_name, Env::get('j2hACuPrlohF9BvFsgatvaNFQxCBCc' , false));
        //微信上传配置
        $upload_config= $model->settingInfo(['uniacid'=>$this->_uniacid]);

        if(empty($upload_config['key'])){

            $this->errorMsg('还未填写密钥');
        }
        //密钥
        $upload_key_url = $upload_config['key'];

        $config_model = new \app\massage\model\Config();
        //appid
        $app_id         = $config_model->where(['uniacid'=>$this->_uniacid])->value('appid');

        if(empty($app_id)){

            $this->errorMsg('小程序未配置');
        }
        $uploadInfo = [

            'siteinfo' => [

                'uniacid'  => $this->_uniacid,

                "multiid"  => "0",

                "version"  => "3.0",
                //小程序接口
                'siteroot' => $this->_is_weiqin?'https://'.$_SERVER['HTTP_HOST']."/app/index.php":'https://'.$_SERVER['HTTP_HOST']."/index.php"
            ],
            //密钥
            'upload_key' => 'http://'.$_SERVER['HTTP_HOST'].'/attachment/'.$upload_key_url ,
            //版本号
            'version_no' => $version_no,
            //版本号
            'version'    => $upload_config['version'],
            //描述
            'content'    => $upload_config['content'],
            //app_id
            'app_id'     => $app_id,

            'app_id_list'     => explode(',' , $upload_config['app_id']),
        ];

        $data = $upgrade->uploadWxapp($uploadInfo,$wxapp_version);

        return $this->success( $data );

    }


    /**
     * @author jingshuixian
     * @DataTime: 2020-06-08 15:00
     * @功能说明: 上传小程序代码(技师端)
     */
    public function uploadWxappCoach(){

        $wxapp_version = isset($this->_param['coach_wxapp_version'])?$this->_param['coach_wxapp_version']:''; //微信小程序上传版本

        $model        = new WxUpload();

        $goods_name   = config('app.AdminModelList')['app_model_name'];

        $auth_uniacid = config('app.AdminModelList')['auth_uniacid'];

        $version_no   = config('app.AdminModelList')['version_no'];

        $upgrade      = new LongbingUpgrade($auth_uniacid , $goods_name, Env::get('j2hACuPrlohF9BvFsgatvaNFQxCBCc' , false));
        //微信上传配置
        $upload_config= $model->settingInfo(['uniacid'=>$this->_uniacid]);

        if(empty($upload_config['coach_key'])){

            $this->errorMsg('还未填写密钥');
        }
        //密钥
        $upload_key_url = $upload_config['coach_key'];

        //appid
        $app_id         = getConfigSetting($this->_uniacid,'coach_appid');

        if(empty($app_id)){

            $this->errorMsg('小程序未配置');
        }
        $uploadInfo = [

            'siteinfo' => [

                'uniacid'  => $this->_uniacid,

                "multiid"  => "0",

                "version"  => "3.0",
                //小程序接口
                'siteroot' => $this->_is_weiqin?'https://'.$_SERVER['HTTP_HOST']."/app/index.php":'https://'.$_SERVER['HTTP_HOST']."/index.php"
            ],
            //密钥
            'upload_key' => 'http://'.$_SERVER['HTTP_HOST'].'/attachment/'.$upload_key_url ,
            //版本号
            'version_no' => $version_no,
            //版本号
            'version'    => $upload_config['coach_version'],
            //描述
            'content'    => $upload_config['coach_content'],
            //app_id
            'app_id'     => $app_id,

            'app_id_list'     => explode(',' , $upload_config['app_id']),
        ];

        $data = $upgrade->uploadWxapp($uploadInfo,$wxapp_version);

        return $this->success( $data );

    }

    /**
     **@author lichuanming
     * @DataTime: 2020/6/22 17:30
     * @功能说明: 获取微信版本信息
     */
    public function getWxappVersion(){
        $goods_name   = config('app.AdminModelList')['app_model_name'];

        $auth_uniacid = config('app.AdminModelList')['auth_uniacid'];

        $version_no   = config('app.AdminModelList')['version_no'];

        $upgrade      = new LongbingUpgrade($auth_uniacid , $goods_name, Env::get('j2hACuPrlohF9BvFsgatvaNFQxCBCc' , false));
        $version = $upgrade->getWxappVersion($version_no);
        return $this->success($version);
    }
}