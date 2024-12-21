<?php
namespace app\adminuser\model;

use AlibabaCloud\Client\AlibabaCloud;
use app\BaseModel;
use Exception;
use longbingcore\permissions\AdminMenu;
use think\facade\Db;

class AdminUser extends BaseModel
{
    //定义表名
    protected $name = 'massage_admin_user';


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
    public function dataInfo($dis,$is_int=0){

        $data = $this->where($dis)->find();

        if(empty($data)&&$is_int==1){

            $this->dataAdd($dis);

            $data = $this->where($dis)->find();
        }

        return !empty($data)?$data->toArray():[];
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-06 16:43
     * @功能说明:获取审核结果
     */
    public function checkAuthData($data){

        $auth = AdminMenu::getAuthList((int)$data['uniacid'],['adminuser']);

        if($auth['adminuser']==true){

            $cap_dis[] = ['user_id','=',$data['id']];
            //查看是否是团长
            $admin = $this->where($cap_dis)->order('id desc')->find();
        }

        $arr['admin_user_status'] = !empty($admin)?1:0;

        return $arr;
    }

}