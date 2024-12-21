<?php
namespace app\member\model;

use app\BaseModel;
use think\facade\Db;

class Config extends BaseModel
{
    //定义表名
    protected $name = 'massage_member_config';




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

            $dis['member_text'] = '<p>一、等级介绍</p ><p><span style=\"font-size: 14px;\">会员等级是每年1月1日更新，是由用户上一年度在本系统内累计有效成长值决定的，成长值越高等级越高，等级的升级也是由成长值决定的。</span></p ><p>二、等级变化规则</p ><p><span style=\"font-size: 14px;\">每年1月1日，系统将根据用户上一年度使用系统下单累计获得的有效成长值，实行等级降一级的福利更新（例如：上一年度累计达到黄金等级，下一年度可直接解锁白银等级特权和福利，同时扣除成长值到白银等级的门槛值），若用户使用系统获得的成长值提前达到更高等级的标准，即可享受实时升级（年度成长值累计达到铂金等级，即可享受铂金等级福利特权）。</span></p ><p>三、成长等级有效期是多久？</p ><p><span style=\"font-size: 14px;\">每年1月1日，系统将根据用户上一年度在本系统累计获得的有效成长值，进行成长等级更新。更新后的成长等级有效期至本年度最后一天12月31日。</span></p ><p><br/></p >';

            $this->insert($dis);

            unset($dis['member_text']);

            $data = $this->where($dis)->find();

        }

        return !empty($data)?$data->toArray():[];

    }








}