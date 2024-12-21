<?php
namespace app\agent\controller;

use app\Common\Upload;
use think\App;
use app\AgentRest;
use app\agent\model\OssConfig as model;
use think\facade\Db;
use think\file\UploadedFile;

class OssConfig extends AgentRest
{


    protected $model;

    public function __construct ( App $app ){

        parent::__construct( $app );

        $this->model = new model();

    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-05-14 10:08
     * @功能说明:列表
     */
    public function configList(){

        $input = $this->_input;

        $dis[] = [

            'uniacid','in' , $this->_uniacid_arr

        ];

        if(!empty($input['name'])){

            $dis[] = ['name','like','%'.$input['name'].'%'];
        }

        $data = $this->model->configList($dis,$input['page_count']);

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-05-14 10:12
     * @功能说明:添加
     */
    public function configAdd(){

        $input = $this->_input;

        $input['uniacid'] = $this->_uniacid;
        Db::startTrans();
        $res = $this->model->configAdd($input);

        $id  = $this->model->getLastInsID();

        if(!empty($id) && !empty($input['open_oss']))
        {
            $path = LONGBING_EXTEND_PATH . 'timg.jpg';

            if(file_exists($path)){

                $dis['id'] = $id;

                $config = $this->model->configInfo($dis);

                $file   = new UploadedFile($path ,'test.jpg');

                $file_upload_model = new Upload($this->_uniacid);

                $check  = $file_upload_model->upload('picture' ,$file,$config);

                if(empty($check)) return $this->error(lang('上传配置错误,请检查上传配置是否正确'));
            }
        }
        Db::commit();

        return $this->success($res);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-05-14 10:14
     * @功能说明:编辑
     */
    public function configUpdate(){

        $input = $this->_input;

        $dis = [

            'id' => $input['id']

        ];
        Db::startTrans();
        $res = $this->model->configUpdate($dis,$input);

        if(!empty($res) && !empty($input['open_oss']))
        {
            $path = LONGBING_EXTEND_PATH . 'timg.jpg';

            if(file_exists($path)){

                $config = $this->model->configInfo($dis);

                $file   = new UploadedFile($path ,'test.jpg');

                $file_upload_model = new Upload($this->_uniacid);

                $check  = $file_upload_model->upload('picture' ,$file,$config);

                if(empty($check)) return $this->error(lang('上传配置错误,请检查上传配置是否正确'));
            }
        }

        Db::commit();

        return $this->success($res);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-05-14 10:17
     * @功能说明:详情
     */
    public function configInfo(){

        $input = $this->_input;

        $dis = [

            'id' => $input['id']

        ];

        $res = $this->model->configInfo($dis);

        return $this->success($res);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-05-14 10:18
     * @功能说明:删除
     */
    public function configDel(){

        $input = $this->_input;

        $dis = [

            'id' => $input['id']

        ];

        $res = $this->model->configDel($dis);

        if($res==200){

            $this->errorMsg('已有小程序使用该配置');

        }

        return $this->success($res);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-05-14 10:35
     * @功能说明:配置下拉框
     */
    public function configSelect(){

        $dis[] = [

            'uniacid','in' , $this->_uniacid_arr

        ];

        $data = $this->model->where($dis)->order('id desc')->select();

        return $this->success($data);
    }





}