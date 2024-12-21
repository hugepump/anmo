<?php
namespace app\massage\model;

use AlibabaCloud\Client\AlibabaCloud;
use app\BaseModel;
use Exception;
use think\facade\Db;

class HelpConfig extends BaseModel
{
    //定义表名
    protected $name = 'massage_help_notice_config';


    /**
     * @param $value
     * @param $data
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-09-28 14:42
     */
    public function getHelpPhoneAttr($value,$data){

        return !empty($value)?explode(',',$value):[];

    }


    /**
     * @param $value
     * @param $data
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-09-28 14:42
     */
    public function getOrderTmplTextAttr($value,$data){

        return !empty($value)?@explode(',',$value):[];

    }

    /**
     * @param $value
     * @param $data
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-09-28 14:42
     */
    public function getReminderNoticePhoneAttr($value,$data){

        return !empty($value)?explode(',',$value):[];

    }


    /**
     * @param $value
     * @param $data
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-09-28 14:42
     */
    public function getHelpUserIdAttr($value,$data){

        return !empty($value)?explode(',',$value):[];

    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:04
     * @功能说明:添加
     */
    public function dataAdd($data){

        $res = $this->insert($data);

        return $res;

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:05
     * @功能说明:编辑
     */
    public function dataUpdate($dis,$data){

        $res = $this->where($dis)->update($data);

        return $res;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:06
     * @功能说明:列表
     */
    public function dataList($dis,$page){

        $data = $this->where($dis)->order('id desc')->paginate($page)->toArray();

        return $data;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:43
     * @功能说明:
     */
    public function dataInfo($dis){

        $data = $this->where($dis)->find();

        if(empty($data)){

            $this->dataAdd($dis);

            $data = $this->where($dis)->find();

        }

        return !empty($data)?$data->toArray():[];

    }





}