<?php


namespace app\agent\model;


use app\BaseModel;
use think\facade\Db;

class OssConfig extends BaseModel
{
    protected  $name = 'longbing_oos_config';


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-05-14 09:53
     * @功能说明:添加配置
     */
    public function configAdd($data){

        $data['create_time'] = time();

        $data['update_time'] = time();

        $data['status']      = 1;

        $data['is_sync']     = 1;

        $res = $this->insert($data);

        return $res;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-05-14 10:02
     * @功能说明:编辑
     */
    public function configUpdate($dis,$data){

        $data['update_time'] = time();

        $res = $this->where($dis)->update($data);

        return $res;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-05-14 10:04
     * @功能说明:列表
     */
    public function configList($dis,$page){

        $data = $this->where($dis)->order('id desc')->paginate($page)->toArray();

        return $data;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-05-14 10:15
     * @功能说明:详情
     */
    public function configInfo($dis){

        $data = $this->where($dis)->find();

        return !empty($data)?$data->toArray():[];

    }


    /**
     * @param $dis
     * @功能说明:删除
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-05-14 10:18
     */
    public function configDel($dis){

        $config_dis = [

            'upload_setting' => $dis['id']

        ];

        $info = Db::name('longbing_cardauth2_config')->where($config_dis)->find();

        if(!empty($info)){

            return 200;
        }

        $res = $this->where($dis)->delete();

        return $res;
    }




}