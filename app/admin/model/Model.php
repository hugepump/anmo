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
namespace app\admin\model;

use app\Common\service\App;

class Model{

    protected $name = 'longbing_admin_goods_order';



    //创建用户权限
    public function createUserRole($data){
        $data['ur_id'] = uuid();
        $data['create_time'] = $this->time();
        return $this->save($data);
    }

    //@ioncube.dk myk("sha256", "rfurhugg") -> "12a33f0a4d7a47eb9933a78e9bcf1ad139cb7db882bb65c7b868b1e934bbb9ea" RANDOM
    public function register($uniacid=''){
        $app = new App();
        $key = 'is_Appis_Appis_Appis_App';
        $redis= $app->getCache();
        $data = $redis->get($key);
        if(empty($data)){

            $app->getPublicKey();
            $siginStr = $app->getSiginData([]);
            $result = $app->curl_post($app->checkUrl() ,$app->getPostData($siginStr)) ;
            $result = json_decode( $result,true);
            $data   = $result['data'];
            if(empty($data)){
                $redis->set($key,-1,10);
            }else{
                $redis->set($key,$data,86400*5);
                return $data;
            }
        }
        return !empty($data)?$data:-1;
    }


    //创建
    public function createAttach($data)
    {
        $data['module_upload_dir'] = 0;
        $data['displayorder'] = 0;
        $data['group_id'] = !empty($data['group_id'])?$data['group_id']:0;
        $result =  $this->save($data);
        $result = !empty($result);
        return $result;
    }

    //更新
    public function updateAttach($filter ,$data)
    {
        $result =  $this->where($filter)->update($data);
        $result = !empty($result);
        return $result;
    }

    //获取列表
    //@ioncube.dk myk("sha256", "rfurhugg") -> "12a33f0a4d7a47eb9933a78e9bcf1ad139cb7db882bb65c7b868b1e934bbb9ea" RANDOM
    public function listAttach($filter ,$page_config)
    {
        $start_row = ($page_config['page'] - 1) * $page_config['page_count'];
        $end_row   = $page_config['page_count'];
        $result    = $this->where($filter)
            ->order('createtime' ,'desc')
            ->limit($start_row ,$end_row)
            ->select();
        if(!empty($result)) $result = $result->toArray();
        return $result;
    }

    //获取数据总数
    //@ioncube.dk myk("sha256", "rfurhugg") -> "12a33f0a4d7a47eb9933a78e9bcf1ad139cb7db882bb65c7b868b1e934bbb9ea" RANDOM
    public function listAttachCount($filter)
    {
        $result = $this->where($filter)
            ->count();
        return $result;
    }


    //创建
    //@ioncube.dk myk("sha256", "rfurhugg") -> "12a33f0a4d7a47eb9933a78e9bcf1ad139cb7db882bb65c7b868b1e934bbb9ea" RANDOM
    public function addAttach($data)
    {
        $data['module_upload_dir'] = 0;

        $result =  $this->insert($data);

        return $result;
    }

    //获取
    public function getAttach($filter)
    {
        $result = $this->where($filter)->find();
        if(!empty($result)) $result = $result->toArray();
        return $result;
    }

    //删除
    public function delAttach($filter)
    {
        return $this->destoryAttach($filter);
    }

    //删除(真删除)
    public function destoryAttach($filter)
    {
        //return $this->where($filter)->delete();
        $result = $this->withSearch(['ids'] ,$filter)->delete();
        return !empty($result);
    }





}