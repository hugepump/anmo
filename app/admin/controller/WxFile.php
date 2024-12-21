<?php
namespace app\admin\controller;
use app\ApiRest;
use think\App;
use think\Request;
use app\admin\model\AttachmentGroup;
use app\admin\model\CoreAttachment;
use app\Common\Upload;
class WxFile extends ApiRest
{
    protected $uid = null;
    public function __construct(App $app) {
        parent::__construct($app);
        //测试数据
        $this->uid = $this->getUserId();
    }
    //上传文件
    public function uploadFile()
    {
        $input = $this->_param;
        $file  = $this->request->file('file');

        if(empty($file)) $file  = $this->request->file('files');

        if(empty($file)) $file  = $this->request->file('filePath');

        if(empty($file)) return $this->error('not file ,please check file.');

        $uploda_model = new Upload($this->_uniacid);
        $type = 'picture';
        if(isset($input['type'])) $type = $input['type'];

        $check = !empty($input['name'])?$input['name']:'';

        $info = $uploda_model->upload($type ,$file,[],$check);

        $result = false;

        if(!empty($info))
        {
            if(isset($info['attachment']) && !empty($info['attachment'])) 
            {
                $info['attachment_path'] = $info['attachment']; 
                $info = transImagesOne($info ,['attachment_path'] ,$this->_uniacid);
            }
            //获取上传者id
            $info['uid'] = $this->uid;
//                $info['attachment_path'] = longbingGetFilePath($info['attachment'] , $this->_host,$this->_uniacid);
            //数据来源
            $info['from'] = 'wx';
            //写入数据库
            $attachment_model = new CoreAttachment();
            $data = $attachment_model->createAttach($info);
            //判断写入数据库是否成功
            if(!empty($data)) $result = $info;
        }
        //数据处理
        return $this->success($result);
    }    
    
    public function uploadFiles()
    {
        //获取参数
        $input = $this->_param;
        //获取文件列表
        $files  = $this->request->file('file');

        if(empty($files)) $files  = $this->request->file('files');
        if(empty($files)) $files  = $this->request->file('filePath');
        //检查文件是否存在
        if(empty($files)) return $this->error('not file ,please check file.');
        //设置类型
        $type = 'picture';
        if(isset($input['type'])) $type = $input['type'];
        $result = [];
        //生成上传模型
        $uploda_model = new Upload($this->_uniacid);
        foreach($files as $file)
        {
            //上传文件
            $info = $uploda_model->upload($type ,$file);


            if(!empty($info))
            {
                if(isset($info['attachment']) && !empty($info['attachment'])) 
                {
                    $info['attachment_path'] = $info['attachment']; 
                    $info = transImagesOne($info ,['attachment_path'] ,$this->_uniacid);
                }
                //获取上传者id
                $info['uid'] = $this->uid;
//                $info['attachment_path'] = longbingGetFilePath($info['attachment'] , $this->_host,$this->_uniacid);
                //数据来源
                $info['from'] = 'wx';
                //写入数据库
                $attachment_model = new CoreAttachment();
                $data = $attachment_model->createAttach($info);
                //判断写入数据库是否成功
                if(!empty($data)) $result[] = $info;
            }
        }
        //数据处理
        return $this->success($result);
    }
}