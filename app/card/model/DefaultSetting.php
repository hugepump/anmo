<?php
namespace app\card\model;

use app\BaseModel;
use think\Model;



class DefaultSetting extends BaseModel
{
    //定义表名
    protected $name = 'longbing_card_default_setting';


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-03-18 12:23
     * @功能说明:查找配置
     */
    public function settingInfo($dis,$field='*'){

        $data = $this->where($dis)->find();

        if(empty($data)){

            $dis['my_photo_cover'] = !empty($dis['my_photo_cover'])?$dis['my_photo_cover']:'';

            $dis['share_text']     = !empty($dis['share_text'])?$dis['share_text']:'';

            $this->insert($dis);
        }

        return $this->where($dis)->field($field)->find()->toArray();
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-03-18 12:24
     * @功能说明:配置编辑
     */
    public function settingUpdate($dis,$data){


        $res = $this->where($dis)->update($data);

        return $res;
    }





}