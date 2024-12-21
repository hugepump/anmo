<?php
namespace app\massage\model;

use app\BaseModel;
use think\facade\Db;

class AgentApply extends BaseModel
{
    //定义表名
    protected $name = 'massage_agent_apply';




    public function getPhoneAttr($value,$data){

        if(!empty($value)&&isset($data['uniacid'])){

            if(numberEncryption($data['uniacid'])==1){

                return  substr_replace($value, "****", 2,4);
            }
        }

        return $value;
    }
    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:04
     * @功能说明:添加
     */
    public function dataAdd($data){

        $data['create_time'] = time();

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
    public function dataList($dis,$page,$where=[]){

        $data = $this->where($dis)->where(function ($query) use ($where){
            $query->whereOr($where);
        })->order('status desc,id desc')->paginate($page)->toArray();

        return $data;

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:06
     * @功能说明:列表
     */
    public function adminDataList($dis,$page,$where=[]){

        $data = $this->alias('a')
                ->join('massage_service_user_list b','a.user_id = b.id','left')
                ->join('shequshop_school_admin c','a.admin_pid = c.id','left')
                ->where($dis)
                ->where(function ($query) use ($where){
                    $query->whereOr($where);
                })
                ->field('a.*,b.nickName,b.avatarUrl,c.agent_name as top_name')
                ->group('a.id')
                ->order('a.id desc')
                ->paginate($page)
                ->toArray();

        return $data;
    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:43
     * @功能说明:
     */
    public function dataInfo($dis){

        $data = $this->where($dis)->find();

        return !empty($data)?$data->toArray():[];
    }








}