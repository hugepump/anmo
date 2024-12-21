<?php
namespace app\admin\controller;
use app\AdminRest;
use think\App;
use think\Request;
use app\admin\model\AttachmentGroup;
use app\admin\model\CoreAttachment;
use app\Common\Upload;
class File extends AdminRest
{
    public $uid = 0;

    public function __construct(App $app) {
        parent::__construct($app);
        //测试数据
//        $this->_uniacid = 2;
        $this->uid = 0;
    }
    
    //创建分组
    public function createGroup()
    {
        //获取参数
        $input = $this->_input;
        //兼容写法-- lichuanming 2020/5/13
        if(!isset($input['group']['name']) && !isset($input['name'])) return $this->error('not group name ,please check .');
        $group['name'] = isset($input['group']['name'])?$input['group']['name']:$input['name'];
        //获取uid
//      if(!empty($this->uid)) $group['uid'] = $this->uid;
        $group['uniacid'] = $this->_uniacid;

        $group['admin_id'] = $this->_user['id'];
        //生成分组模型
        $group_model = new AttachmentGroup();
        $group_count = $group_model->where(['uniacid'=>$this->_uniacid,'admin_id'=>$this->_user['id']])->count('id');
        if($group_count >= 20){ //分组不超过20个
            return $this->error('分组最多限制为20个！');
        }

        $repeat = $group_model->where(['uniacid'=>$this->_uniacid,'name'=>$group['name'],'admin_id'=>$this->_user['id']])->count();

        if($repeat){

            return $this->error('已存在同名分组');
        }

        $result = $group_model->createGroup($group);

        return $this->success($result);
    }
    
    //获取分组列表
    public function listGroup()
    {
        //获取参数
        $param = $this->_param;
        if(isset($param['name'])) $filter[] = ['name','=',$param['name']];

        $filter[] = ['uniacid','=',$this->_uniacid];

        if(empty($this->_user['is_admin'])){

            $filter[] = ['admin_id','=',$this->_user['id']];

        }else{

            $filter[] = ['admin_id','in',[$this->_user['id'],0]];
        }
        //获取uid
//      if(!empty($this->uid)) $filter['uid'] = $this->uid;
        //生成分组模型
        $group_model = new AttachmentGroup();
        //获取数据
        $result = $group_model->where($filter)->select()->toArray();
        return $this->success(['groups' => $result]);
    }
    
    //更新分组列表
    public function updateGroup()
    {
        //获取参数
        $group_id = $this->_param['group_id'];
        //获取更新数据
        $data = $this->_input['group'];
        //生成分组模型
        $group_model = new AttachmentGroup();

        $repeat = $group_model->where(['uniacid'=>$this->_uniacid,'name'=>$data['name']])->where('id','<>',$group_id)->count();
        if($repeat){
            return $this->error('已存在同名分组');
        }

        //更新数据
        $result = $group_model->updateGroup(['id' => $group_id,'uniacid'=>$this->_uniacid] ,$data);
        //返回数据
        return $this->success($result);
    }
    
    //删除分组信息
    public function delGroup()
    {
        //获取参数
        if(!isset($this->_param['group_id'])) return $this->error('not group id');
        $group_id = $this->_param['group_id'];

        $where = array(
            ['group_id','=',$group_id],
            ['uniacid','=',$this->_uniacid]
        );
        //生成分组模型
        $attachment_model = new CoreAttachment();
        $file_count = $attachment_model->where($where)->count('id');
        if($file_count > 0){
            return $this->error('目前分组中有文件数据，不可删除，如要删除，请先清除文件数据');
        }
        $group_model = new AttachmentGroup();
        //删除数据
        $result = $group_model->delGroup(['id' => $group_id]);
        //返回数据
        return $this->success($result);
    }

    /**
     **@author lichuanming
     * @DataTime: 2020/5/15 14:35
     * @功能说明:批量删除分组
     */
    public function delAllGroup(){
        if(!isset($this->_param['group_id'])) return $this->error('not group id');
        $group_id = $this->_param['group_id'];

        $attachment_model = new CoreAttachment();
        if(is_array($group_id)){
            foreach ($group_id as $id){
                $where = array(
                    ['group_id','=',$id],
                    ['uniacid','=',$this->_uniacid]
                );
                $file_count = $attachment_model->where($where)->count('id');
                if($file_count > 0){
                    return $this->error('目前分组中有文件数据，不可删除，如要删除，请先清除文件数据');
                }
            }
            $group_model = new AttachmentGroup();
            //删除数据
            $result = $group_model->delGroup(['id' => $group_id]);
            //返回数据
            return $this->success($result);
        }else{
            return $this->delGroup();
        }
    }
    
    //上传文件
    public function uploadFile()
    {
        $input = $this->_param;
        $file  = $this->request->file('file');
        if(empty($file)) return $this->error('not file ,please check file.');
        $uploda_model = new Upload($this->_uniacid);
        $type = 'picture';
        if(isset($input['type'])) $type = $input['type'];
        $info = $uploda_model->upload($type ,$file);
        $result = false;
        if(!empty($info))
        {
            $info['uid'] = $this->uid;
            $info['admin_id'] = $this->_user['id'];
            $info['longbing_attachment_path'] = longbingGetFilePath($info['attachment'] , $this->_host,$this->_uniacid);
            $info['longbing_from'] = 'web';
            $attachment_model = new CoreAttachment();
            $result = $attachment_model->createAttach($info);
            if(!empty($result)) $result = $info;
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

        //检查文件是否存在
        if(empty($files)) return $this->error('not file ,please check file.');
        //设置类型
        $type = 'picture';
        if(isset($input['type'])) $type = $input['type'];

        //上传文件分组 --lichuanming 2020/5/13
        $group_id = -1;
        if(isset($input['group_id'])) $group_id = $input['group_id'];

        $result = [];
        //生成上传模型
        $uploda_model = new Upload($this->_uniacid);

        foreach($files as $file)
        {
            //上传文件
            $info = $uploda_model->upload($type ,$file);

            if(!empty($info))
            {
                //获取上传者id
                $info['uid'] = $this->uid;

                $info['admin_id'] = $this->_user['id'];

                $info['attachment_path'] = longbingGetFilePath($info['attachment'] , $this->_host,$this->_uniacid ,$info['longbing_driver']);
                //文件分组界定 --lichuanming 2020/5/13
                $info['group_id'] = $group_id;
                $info['uniacid'] = $this->_uniacid;
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


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-13 12:00
     * @功能说明:文件列表
     */
    public function listFile()
    {
        $param = $this->_param;

        $filter['uniacid'] = $this->_uniacid;


        if(!empty($param['type'])){

            $filter['type']    = $param['type'];
        }else{

            $filter['type']    = 1;
        }

        $filter['uid']     = 0;

        if(empty($this->_user['is_admin'])){

            $where[] = ['admin_id','=',$this->_user['id']];

        }else{

            $where[] = ['admin_id','in',[$this->_user['id'],0]];
        }
        //判断分组是否存在
        if(isset($param['group_id']))  $filter['group_id'] = $param['group_id']?$param['group_id']:['0','-1'];
        //判断文件类型是否存在
        if(isset($param['type']) && in_array($param['type'], [1,2,3])) $filter['type'] = $param['type'];
        //生成模型类
        $file_model = new CoreAttachment();

        $result = $file_model->where($filter)->where($where)->order('id desc')->paginate($param['limit'])->toArray();

        $result['files'] = $result['data'];

        unset($result['data']);

        $result['files'] = transImagesOne($result['files'] ,['attachment'] ,$this->_uniacid);
        //返回数据
        return $this->success($result);
    }
    
    //获取文件
    public function getFile()
    {
        $param = $this->_param;
        $filter['uniacid'] = $this->_uniacid;
        $filter['id']      = $param['id'];
//      $filter['uid'] = $this->uid;
        //生成模型
        $file_model = new CoreAttachment();
        //查询数据
        $file       = $file_model->getFile($filter);
//        if(isset($file['attachment']))
//        {
//            $file['attachment_path'] = longbingGetFilePath($file['attachment'] ,$this->_host ,$this->_uniacid);
//        }
        //返回数据
        return $this->success($file);
    }
    
    //删除文件
    public function delFile()
    {
        //获取参数
        $input = $this->_input;
        //获取参数
        if(!isset($input['ids'])) return $this->error('not file id,please check.');
        $filter['ids'] = $input['ids'];
//      $filter['uid'] = $this->uid;
        $filter['uniacid'] = $this->_uniacid;
        //生成模型
        $file_model = new CoreAttachment();
        //删除
        $result = $file_model->delAttach($filter);
        return $this->success($result);
    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-04-08 14:16
     * @功能说明:获取上传配置
     */
    public function uploadConfig(){

        $data = longbingGetOssConfig($this->_uniacid);

        $data['uniacid'] = $this->_uniacid;

        return $this->success($data);

    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-04-08 14:17
     * @功能说明:上传文件到数据库
     */
    public function addFile(){

        $info = $this->_input;

        $group_id = -1;

        if(isset($info['group_id'])) {

            $group_id = $info['group_id'];
        }
        //获取上传者id
        $info['uid'] = $this->uid;
        //$info['attachment_path'] = longbingGetFilePath($info['attachment'] , $this->_host,$this->_uniacid ,$info['longbing_driver']);
        //文件分组界定 --lichuanming 2020/5/13
        $info['group_id']   = $group_id;

        $info['admin_id'] = $this->_user['id'];

        $info['uniacid']    = $this->_uniacid;

        $info['createtime'] = time();

        $attachment_model   = new CoreAttachment();

        $data = $attachment_model->addAttach($info);

        return $this->success($data);

    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-08-13 15:26
     * @功能说明:图片移动分组
     */
    public function moveGroup(){

        $input = $this->_input;

        $attachment_model = new CoreAttachment();

        $res = $attachment_model->where('id','in',$input['file_id'])->update(['group_id'=>$input['group_id']]);

        return $this->success($res);

    }
}