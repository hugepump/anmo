<?php
namespace app\massage\model;

use app\BaseModel;
use think\facade\Db;

class ActionLog extends BaseModel
{
    //定义表名
    protected $name = 'massage_action_log';




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

        return !empty($data)?$data->toArray():[];

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-19 16:08
     * @功能说明:开启默认
     */
    public function updateOne($id){

        $user_id = $this->where(['id'=>$id])->value('user_id');

        $res = $this->where(['user_id'=>$user_id])->where('id','<>',$id)->update(['status'=>0]);

        return $res;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-20 18:48
     * @功能说明:
     */
    public function logList($dis,$limit=10){

        $datas = $this->alias('a')
                 ->join('shequshop_school_admin b','a.user_id = b.id','left')
                 ->where($dis)
                 ->field('a.*, b.username as user_name')
                 ->group('a.id')
                 ->order('id desc')
                 ->paginate($limit)
                 ->toArray();

        $dataPath   = APP_PATH  . 'massage/info/LogSetting.php';

        $actionPath = APP_PATH  . 'massage/info/LogAction.php';

        $log    =  include $dataPath ;

        $action =  include $actionPath;

        $coach_name =  getConfigSetting(666,'attendant_name');

        $channel_name =  getConfigSetting(666,'channel_menu_name');

        if(!empty($datas['data'])){

            foreach ($datas['data'] as &$v){

                $v['create_time'] = date('Y-m-d H:i:s',$v['create_time']);

                if(empty($log[$v['model']])){

                    continue;
                }

                $vs = $log[$v['model']];

                foreach ($vs as $value){

                    if($v['method']==$value['method']&&$v['action_type']==$value['action_type']&&$v['code_action']==$value['code_action']){

                        $action_data = $action[$v['action']];

                        $boj_unit = $boj_name = '';

                        if(isset($value['title'])){
                            //当参数是数组时
                            if(!empty($value['parameter_arr'])){

                                $v['obj_id'] = @unserialize($v['obj_id']);

                                $boj_name = Db::name($value['table'])->where('id','in',$v['obj_id'])->column($value['title']);

                                $boj_name = implode(',',$boj_name);

                            } else{

                                $boj_name = Db::name($value['table'])->where(['id'=>$v['obj_id']])->find();

                                $boj_name = isset($boj_name[$value['title']])?$boj_name[$value['title']]:'';
                            }

                            if(in_array($value['title'],['code','order_code'])&&$v['model']!='AdminChannel'){

                                $boj_unit = '单号:';

                            }elseif (in_array($value['title'],['id'])){

                                $boj_unit = 'ID:';

                            }elseif(!empty($value['text'])){

                                $boj_unit = $value['text'].':';
                            }
                        }

                        $boj_unit = !empty($boj_unit)?'-'.$boj_unit:'';

                        $v['text'] = '操作'.$value['name'].'-'.$action_data.$boj_unit.$boj_name;

                        $v['text'] = str_replace('技师',$coach_name,$v['text']);

                        $v['text'] = str_replace('渠道商',$channel_name,$v['text']);
                    }
                }
            }
        }
        return $datas;
    }




}