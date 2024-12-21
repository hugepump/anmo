<?php
namespace app\massage\model;

use app\BaseModel;
use think\facade\Db;

class ConfigSetting extends BaseModel
{
    //定义表名
    protected $name = 'massage_config_setting';


    /**
     * @param $value
     * @param $data
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-23 14:14
     */
    public function getValueAttr($value,$data){

        $arr = ['salesman_balance','salesman_coach_balance','salesman_admin_balance','channel_balance','channel_coach_balance','channel_admin_balance','coach_agent_balance','tax_point','user_agent_balance','partner_coach_balance','partner_admin_balance'];
        //数字类型
        if(isset($data['field_type'])){

            if ($data['field_type']==3||in_array($data['key'],$arr)){

                $value = floatval($value);

            }elseif($data['field_type']==1){

                $value = (int)$value;
            }
        }

        return $value;
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
     * @DataTime: 2023-02-22 16:59
     * @功能说明:初始化配置记录
     */
    public function initData($uniacid,$key_arr=[]){

        $dataPath = APP_PATH  . 'massage/info/ConfigSetting.php' ;

        $data=  include $dataPath ;

        $key = 'config_setting_init_data';

        incCache($key,1,$uniacid,99);

        $value = getCache($key,$uniacid);

        if($value==1){

            foreach ($data as $v){

                if(!empty($key_arr)&&!in_array($v['key'],$key_arr)){

                    continue;
                }

                $dis = [

                    'uniacid' => $uniacid,

                    'key'     => $v['key'],
                ];

                $find = $this->where($dis)->find();

                if(empty($find)){

                    $dis['text'] = $v['text'];

                    $dis['value'] = $v['default_value'];

                    $dis['default_value'] = $v['default_value'];

                    $dis['field_type'] = $v['field_type'];

                    $this->insert($dis);
                }

            }

        }

        decCache($key,1,$uniacid,90);

        return true;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:43
     * @功能说明:
     */
    public function dataInfo($uniacid,$key_arr=[]){

        if(empty($uniacid)){

            return false;
        }

        //$this->initData($uniacid,$key_arr);

        $dis[] = ['uniacid','=',$uniacid];

        if(!empty($key_arr)){

            $dis[] = ['key','in',$key_arr];
        }

        $data = $this->where($dis)->select()->toArray();

        foreach ($data as $v){

            $arr[$v['key']] = $v['value'];

        }

       // $arr = $this->where($dis)->column('value','key');

        return !empty($arr)?$arr:'';
    }


    /**
     * @param $arr
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-23 13:50
     */
    public function dataUpdate($arr,$uniacid){

        if(!empty($arr)){

            foreach ($arr as $k=>$value){

                if('order_rules'==$k){

                    $config_model = new MassageConfig();

                    $config_model->dataInfo(['uniacid'=>$uniacid]);

                    $config_model->dataUpdate(['uniacid'=>$uniacid],[$k=>$value]);

                    continue;
                }

                $dis = [

                    'uniacid' => $uniacid,

                    'key'     => $k
                ];

                $this->where($dis)->update(['value'=>$value]);
            }
        }

        return true;
    }









}