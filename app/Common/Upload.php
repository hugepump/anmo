<?php
namespace app\Common;

use app\common\model\CoreAttachment;
use OSS\Core\OssException;
use think\helper\Arr;
use think\Request;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use think\Image;
use Qcloud\Cos\Client;
use think\Session;  
use think\facade\Db;
use think\facade\Filesystem;
class Upload {

    protected $request;
    protected $config;//配置
    protected $uniacid;
    protected $path_type;//图片  音频   视频
    protected $attachment_model;
    protected $is_weiqin = false;
    /**
     * 架构函数
     * @param Request $request Request对象
     * @access public
     */
    public function __construct($uniacid = '7777')
    {
        $this->uniacid = $uniacid;
        $this->config = longbingGetOssConfig($uniacid);
        $this->is_weiqin = longbingIsWeiqin();
    }
    
    //上传
    public function upload($type ,$file,$config=array(),$is_check='')
    {

        if(!empty($config)){

            $this->config = $config;

        }
        $base_path = '/';
        $type_data = 1;
        switch($type)
        {

            //图片
            case 'picture':
                $base_path = $base_path . 'image/';
                $type_data = 1;
                break;
            //音频
            case 'audio':
                $base_path = $base_path . 'audio/';
                $type_data = 2;
                break;
            //视频
            case 'video':
                $base_path = $base_path . 'video/';
                $type_data = 3;
                break;
            //证书
            case 'cert':
                $base_path = $base_path . 'cert/';
                $type_data = 4;
                break;
            //证书
            case 'wxuploadkey':
                $base_path = $base_path . 'wxuploadkey/';
                $type_data = 5;
                break;
            //证书
            case 'file':
                $base_path = $base_path . 'file/';
                $type_data = 6;
                break;

            case 'local_picture':
                $base_path = $base_path . 'local_picture/';
                $type_data = 7;
                break;
            default:
                $base_path = $base_path . 'image/';
                $type_data = 1;
                break;
        }
        //根据时间生成路径
        $base_path = $base_path . $this->uniacid . '/' . date('y') . '/' . date('m');

        $info = null;
        $upload_status = false;
        //数据检查
        if($this->checkFile($type ,$file,$is_check))
        {
            $file_name = null;
            //本地保存
            if(in_array($type, ['cert','wxuploadkey','local_picture'])){
                $file_name = $this->uniacid . '_' . $file->getOriginalName();
                $this->config['open_oss'] = 0;
            }

            $file_name.= $is_check;

            $info_path = $this->fileLoaclSave($base_path ,$file ,$file_name);
            //获取数据
            $info = $this->fileInfo($info_path ,$file->getOriginalName().$is_check ,$type_data);
            //云服务器上传
            if(isset($this->config['open_oss']))
            {
                switch($this->config['open_oss'])
                {
                    //本地
                    case 0:
                        $upload_status = true;
                        $info['longbing_driver'] = 'loacl';
                        break;
                    case 1:
                        $oss_res = $this->aliyunUpload($info_path);

                        if(isset($this->config['aliyun_base_dir']) && !empty($this->config['aliyun_base_dir'])) $info_path = $this->config['aliyun_base_dir'] . '/' . $info_path;
                        if(in_array(substr($info_path,0,1) ,['/' ,"/"])) {
                            $info_path = substr($info_path,1,(strlen($info_path)-1));
                        }
                        $info['attachment'] = $info_path;
                        $info['longbing_driver'] = 'aliyun';
                        if(isset($oss_res['info']['url'])) $upload_status = true;
                        break;
                    case 2:
                        $oss_res = $this->qiniuUpload($info_path);
                        $info['longbing_driver'] = 'qiniuyun';
                        if(!empty($oss_res) && empty($oss_res[1])) $upload_status = true;
                        break;
                    case 3:
                        $oss_res = $this->tenxunUpoload($info_path);

                        $info['longbing_driver'] = 'tengxunyun';
                        if(isset($oss_res['ETag'])  && isset($oss_res['ObjectURL'])) $upload_status = true;
                        break;
                    default:
                        $info['longbing_driver'] = 'loacl';
                        $upload_status = true;
                        break;
                }
            }else{
                $upload_status = true;
                $info['longbing_driver'] = 'loacl';
            }
        }
        if(!$upload_status) $info = null;

        return $info;
    }

    /**
     * @param $info_path
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-11-15 11:42
     */
    public function uploadFile($info_path,$type=1){
        $info = [];
        $upload_status = false;
        if(isset($this->config['open_oss']))
        {
            switch($this->config['open_oss'])
            {
                //本地
                case 0:
                    $upload_status = true;
                    $info['longbing_driver'] = 'loacl';
                    break;
                case 1:
                    $oss_res = $this->aliyunUpload($info_path);
                    if(isset($this->config['aliyun_base_dir']) && !empty($this->config['aliyun_base_dir'])) $info_path = $this->config['aliyun_base_dir'] . '/' . $info_path;
                    if(in_array(substr($info_path,0,1) ,['/' ,"/"])) {
                        $info_path = substr($info_path,1,(strlen($info_path)-1));
                    }
                    $info['attachment'] = $info_path;
                    $info['longbing_driver'] = 'aliyun';
                    if(isset($oss_res['info']['url'])) $upload_status = true;
                    break;
                case 2:
                    $oss_res = $this->qiniuUpload($info_path);
                    $info['longbing_driver'] = 'qiniuyun';
                    if(empty($oss_res[1])) $upload_status = true;
                    break;
                case 3:
                    $oss_res = $this->tenxunUpoload($info_path);
                    $info['longbing_driver'] = 'tengxunyun';
                    if(isset($oss_res['ETag'])  && isset($oss_res['ObjectURL'])) $upload_status = true;
                    break;
                default:
                    $info['longbing_driver'] = 'loacl';
                    $upload_status = true;
                    break;
            }
        }else{
            $upload_status = true;
            $info['longbing_driver'] = 'loacl';
        }

        if($type==1){

            return $upload_status;
        }else{

            $info['status'] = $upload_status;
        }

        return $info;

    }
    //检查
    public function checkFileV2($type ,$file)
    {

        $result = false;
        switch($type)
        {
            case 'picture':
                $result = validate(['image'=>'filesize:2097152|fileExt:jpg,jpeg,bmp,png|image:*'])->check([$file]);
                break;
            case 'audio':
                $result = validate(['audio'=>'filesize:2097152|fileExt:mp3,wma,wav,m4a'])->check([$file]);

                break;
            case 'video':
                $result = validate(['video'=>'filesize:2097152|fileExt:wmv,mp4,avi,mpg,rmvb'])->check([$file]);
                break;
            case 'cert':
                $result = validate(['cert'=>'filesize:2097152|fileExt:cert'])->check([$file]);
                break;
            case 'wxuploadkey':
                $result = validate(['cert'=>'filesize:2097152|fileExt:cert'])->check([$file]);
                break;
            default:
                break;
        }
        return $result;
    }


    public function checkFile($type ,$file,$is_check='')
    {
        $result = false;

        switch($type)
        {
            case 'picture':
                $result = validate(['file' => ['fileSize' => 8 * 1024 * 1024, 'fileExt' => 'jpg,jpeg,bmp,png,image,gif']])->check(['file' => $file]);
                break;
            case 'audio':

                $check = empty($is_check)?'mp3,wma,wav,m4a':'';

                $result = validate(['file' => ['fileSize' => 200 * 1024 * 1024, 'fileExt' => $check]])->check(['file' => $file]);

                break;
            case 'video':

                $result = validate(['file' => ['fileSize' => 50 * 1024 * 1024, 'fileExt' => 'wmv,mp4,avi,mpg,rmvb,MPEG-4,MPEG,MOV,3GP,MPV,quicktime,Quicktime,mov']])->check(['file' => $file]);
                break;
            case 'cert':
                $result = validate(['file' => ['fileSize' => 50 * 1024 * 1024, 'fileExt' => 'cert,pem,zip']])->check(['file' => $file]);
                break;
            case 'wxuploadkey':
                $result = validate(['file' => ['fileSize' => 50 * 1024 * 1024, 'fileExt' => 'cert,pem,key']])->check(['file' => $file]);

                break;
            case 'file':
                $result = validate(['file' => ['fileSize' => 50 * 1024 * 1024, 'fileExt' => 'doc,xls,docx,xlsx,ppt,pptx,pdf,zip']])->check(['file' => $file]);

                break;
            case 'local_picture':
                $result = validate(['file' => ['fileSize' => 8 * 1024 * 1024, 'fileExt' => 'jpg,jpeg,bmp,png,image,gif']])->check(['file' => $file]);
                break;
            default:
                break;
        }

        return $result;
    }
    
    //生成返回数据
    public function fileInfo($path ,$file_name ,$type_data)
    {
        $result = array(
            'attachment' => ltrim($path,'/'),
            'uniacid'    => $this->config['uniacid'],
            'filename'   => $file_name,
            'createtime' => time(),
            'type'       => $type_data
        );
        return $result;
    }
    
    
    
    //本地保存
    public function fileLoaclSave($path ,$file ,$file_name = null)
    {
        if(empty($file_name)) $file_name = uuid() . '.' .  $file->getOriginalExtension();

//        dump($path ,$file ,$file_name);exit;
        //保存
        $info = Filesystem::disk('longbing')->putFileAs($path ,$file ,$file_name);
        return $info;
    }
    
    //阿里云上传
    public function aliyunUpload($path)
    {
        //左边的/去掉
        $path   = ltrim($path,'/');
        try{
            $bucket   = $this->config['aliyun_bucket'];
            $filePath = FILE_UPLOAD_PATH . $path;
            if(isset($this->config['aliyun_base_dir']) && !empty($this->config['aliyun_base_dir'])) $path = $this->config['aliyun_base_dir'] . '/' . $path;
            if(in_array(substr($path,0,1) ,['/' ,"/"])) {
                $path = substr($path,1,(strlen($path)-1)); 
            }
            if (file_exists($filePath)) {
                //实例化OSS
                require_once  dirname(__FILE__) . '/extend/aliyuncs/oss-sdk-php/autoload.php';
                $ossClient =new \OSS\OssClient($this->config['aliyun_access_key_id'] 
                    ,$this->config['aliyun_access_key_secret'] 
                    ,$this->config['aliyun_endpoint']);
                //uploadFile的上传方法
                $res=$ossClient->uploadFile($bucket, $path, $filePath);
                if(is_file($filePath)){
                    unlink($filePath);
                }
                return $res;
            }
        }catch(OssException $e) {
            //如果出错这里返回报错信息
            return false;
            //return $e->getMessage();
        }
        //否则，完成上传操作
        return false;
        
    }
    
    //七牛云上传
    public function qiniuUpload($path)
    {
        try{
            //加载驱动
            require_once dirname(__FILE__) . '/extend/qiniu/autoload.php';
            //左边的/去掉
            $path = ltrim($path,'/');
            // 需要填写你的 Access Key 和 Secret Key
            $accessKey = $this->config['qiniu_accesskey'];
            $secretKey = $this->config['qiniu_secretkey'];
            $bucket    = $this->config['qiniu_bucket'];
            // 构建鉴权对象
            $auth = new Auth($accessKey, $secretKey);
            // 生成上传 Token
            $token = $auth->uploadToken($bucket);
            //获取本地文件
            $filePath = FILE_UPLOAD_PATH . $path;
            //上传
            $client = new UploadManager();
            list($ret, $err) = $client->putFile($token, $path, $filePath);
            if(is_file($filePath)){
                unlink($filePath);
            }
            return array($ret,$err);
        }catch (\Exception $e){
            return false;
        }
        
    }
    
    //腾讯云上传
    public function tenxunUpoload($path)
    {
        try{
            $path = ltrim($path,'/');//左边的/去掉
            //加载驱动
            require dirname(__FILE__)  . '/extend/tencentcloud/vendor/autoload.php';

           // dump(dirname(__FILE__)  . '/extend/tencentcloud/vendor/autoload.php');exit;
            // 需要填写你的 Access Key 和 Secret Key
            $appid    = $this->config['tenxunyun_appid'];
            $secretid = $this->config['tenxunyun_secretid'];
            $secretkey= $this->config['tenxunyun_secretkey'];
            $bucket   = $this->config['tenxunyun_bucket'];
            $region   = $this->config['tenxunyun_region'];
            $yuming   = $this->config['tenxunyun_yuming'];
            
            //初始化对象
            $cosClient = new Client(array(
                'region'      => $region, #地域，如ap-guangzhou,ap-beijing-1
                'credentials' => array(
                    'secretId' => $secretid,
                    'secretKey'=> $secretkey,
                ),
            ));

            // 若初始化 Client 时未填写 appId，则 bucket 的命名规则为{name}-{appid} ，此处填写的存储桶名称必须为此格式
          //  $bucket = $bucket . '-' . $appid;
            // 要上传文件的本地路径
            $body =  FILE_UPLOAD_PATH . $path;


            try {
                $result = $cosClient->putObject(array(
                    'Bucket' => $bucket,
                    'Key' => $path,
                    'Body' => file_get_contents($body)
                ));

                if(is_file($body)){
                    unlink($body);
                }
                return  $result;
            } catch (\Exception $e) {
                return  false;
            }
        }catch(\Exception $e){
            return false;
        }
    }
    
}