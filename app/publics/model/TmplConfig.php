<?php
namespace app\publics\model;

use app\BaseModel;
use think\facade\Db;

class TmplConfig extends BaseModel
{
    //定义表名
    protected $name = 'longbing_card_tmpl_config';


    /**
     * User: chenniang(龙兵科技)
     * Date: 2019-12-25 10:49
     * @param $dis
     * @param int $page
     * @return array
     * descrption:模版消息列表
     */
    public function tmplSelect($dis){
        $data = self::where($dis)->select()->toArray();
        return $data;
    }

    /**
     * User: chenniang(龙兵科技)
     * Date: 2019-12-25 10:50
     * @param $dis
     * @param $data
     * @return TmplConfig
     * descrption:模版消息编辑
     */
    public function tmplUpdate($dis,$data){
        $data['update_time'] = time();
        $res = self::where($dis)->update($data);
        return $res;
    }

    /**
     * User: chenniang(龙兵科技)
     * Date: 2019-12-25 10:51
     * @param $data
     * @return int|string
     * descrption:编辑模版消息
     */

    public function tmplAdd($data){
        $data['create_time'] = time();
        $data['update_time'] = time();
        $res = self::insert($data);

        return $res;
    }

    /**
     * User: chenniang(龙兵科技)
     * Date: 2019-12-25 10:51
     * @param $dis
     * @return array|\think\Model|null
     * descrption:添加模版消息
     */
    public function tmplInfo($dis){
        $data = self::where($dis)->find();
        if(empty($data)){
            $data = $this->tmplAdd($dis);
            $data = self::where($dis)->find();
        }
        return !empty($data)?$data->toArray():$data;
    }


    /**
     * @param $dis
     * @功能说明: 获取模版id 列表
     * @author chenniang(龙兵科技)
     * @DataTime: 2019-12-27 17:00
     */
    public function tmplIdList($dis){
        $data = $this->where($dis)->column('tmpl_id');
        return array_values($data);
    }



}