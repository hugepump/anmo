<?php
namespace app\admin\model;

use app\BaseModel;
use think\Model;



class WxUpload extends BaseModel
{
    //定义表名
    protected $name = 'massage_wx_upload';

    protected $resultSetType = 'collection';


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-03-18 12:23
     * @功能说明:查找配置
     */
    public function settingInfo($dis,$field='*'){

        $data = $this->where($dis)->find();

        if(empty($data)){

            $this->insert($dis);
        }

        return $this->where($dis)->field($field)->find()->toArray();
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-06-08 18:49
     * @功能说明:添加
     */
    public function settingAdd($data){

        $res = $this->insert($data);

        return $res;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-03-18 12:24
     * @功能说明:配置编辑
     */
    public function settingUpdate($dis,$data){

        $data['app_id'] = !empty($data['app_id'])?implode(',',$data['app_id']):'';

        $res = $this->where($dis)->update($data);

        return $res;
    }





}