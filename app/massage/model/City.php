<?php
namespace app\massage\model;

use app\BaseModel;
use think\facade\Db;

class City extends BaseModel
{
    //定义表名
    protected $name = 'massage_service_city_list';






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

        return !empty($data)?$data->toArray():[];

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-06-16 11:35
     * @功能说明:
     */
    public function checkCity($input){

        $dis[] = ['uniacid','=',$input['uniacid']];

        $dis[] = ['status','>',-1];

        if(!empty($input['id'])){

            $dis[] = ['id','<>',$input['id']];

        }

        $count = $this->where($dis)->where('city_type','=',1)->count();

        $dis[] = ['city_type','=',$input['city_type']];

        $dis[] = ['title','=',$input['title']];

        $data = $this->dataInfo($dis);

        if(!empty($data)){

            return ['code'=>500,'msg'=>'已有该城市'];
        }

        $num = getCityNumber($input['uniacid']);



        return true;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-20 11:25
     * @功能说明:初始化省份
     */
    public function provinceInit($uniacid){

        $dis = [

            'uniacid'=> $uniacid,

            'city_type' => 1
        ];

        $data = $this->where($dis)->where('status','>',-1)->select()->toArray();

        $key = 'province_data_int';

        incCache($key,1,$uniacid,99);

        $key_value = getCache($key,$uniacid);

        if($key_value==1){

            if(!empty($data)){

                foreach ($data as $v){

                    $dis = [

                        'uniacid' => $uniacid,

                        'title'   => $v['province'],

                        'city_type'=> 3,

                        'city_code'=> $v['province_code'],

                        'status'=>1
                    ];

                    $find = $this->dataInfo($dis);

                    if(empty($find)){

                        $insert = [

                            'uniacid' => $uniacid,

                            'city_type'=> 3,

                            'province' => $v['province'],

                            'province_code' => $v['province_code'],

                            'title'   => $v['province'],

                            'city_code'=> $v['province_code'],

                            'create_time' => time()
                        ];

                        $this->insert($insert);

                        $id = $this->getLastInsID();

                    }else{

                        $id = $find['id'];
                    }

                    $this->dataUpdate(['id'=>$v['id']],['pid'=>$id]);

                }
            }
        }

        decCache($key,1,$uniacid);

        return true;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-20 11:44
     * @功能说明:删除没有
     */
    public function provinceDel(){

        $dis = [

            'status' => 1,

            'city_type' => 3
        ];

        $data = $this->where($dis)->select()->toArray();

        if(!empty($data)){

            foreach ($data as $v){

                $find = $this->where(['pid'=>$v['id']])->where('status','>',-1)->find();

                if(empty($find)){

                    $this->dataUpdate(['id'=>$v['id']],['status'=>-1]);
                }
            }
        }

        return true;
    }


    /**
     * @param $admin
     * @功能说明:
     * @author chenniang
     * @DataTime: 2024-06-05 16:17
     */
    public function cityData($admin){

        $admin_model = new Admin();

        $admin['city_data'] = [];

        $city = $this->dataInfo(['id'=>$admin['city_id']]);

        if(!empty($city)) {
            //城市代理
            if ($admin['city_type'] == 1) {

                array_push($admin['city_data'], $city['pid']);

                array_push($admin['city_data'], $admin['city_id']);

                $admin['city'] = $city['title'];

                $admin['province'] = $this->where(['id' => $city['pid']])->value('title');

                $admin['admin_ptitle'] = $admin_model->where(['id' => $admin['admin_pid'],'status'=>1, 'city_type' => 3,'sub_agent_auth'=>1])->value('agent_name');

            } elseif ($admin['city_type'] == 2) {

                $admin['area'] = $city['title'];

                $admin['city'] = $this->where(['id' => $city['pid']])->value('title');

                $province_id = $this->where(['id' => $city['pid']])->value('pid');

                $admin['province'] = $this->where(['id' => $province_id])->value('title');

                array_push($admin['city_data'], $province_id);
                //区县代理
                array_push($admin['city_data'], $city['pid']);

                array_push($admin['city_data'], $admin['city_id']);

                $admin['admin_ptitle'] = $admin_model->where(['id' => $admin['admin_pid'],'sub_agent_auth'=>1,'status'=>1])->where('city_type', 'in', [1, 3])->value('agent_name');

            } else {

                array_push($admin['city_data'], $admin['city_id']);

                $admin['province'] = $city['title'];
            }
        }
        return $admin;
    }






}