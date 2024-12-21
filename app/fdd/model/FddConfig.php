<?php
namespace app\fdd\model;

use app\BaseModel;
use app\fdd\info\PermissionFdd;
use app\virtual\info\PermissionVirtual;
use think\facade\Db;

class FddConfig extends BaseModel
{
    //定义表名
    protected $name = 'massage_fdd_config';




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

        $data = $this->where($dis)->order('status desc,id desc')->paginate($page)->toArray();

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


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-16 15:47
     * @功能说明:获取发大大状态
     */
    public function getStatus($uniacid){

        $p = new PermissionFdd((int)$uniacid);

        $auth = $p->pAuth();

        if($auth==false){

            return 0;
        }

        $data = $this->dataInfo(['uniacid'=>$uniacid]);

        return $data['status'];

    }








}