<?php
namespace app\massage\model;

use app\BaseModel;
use think\Collection;
use think\facade\Db;

class StoreCoach extends BaseModel
{
    //定义表名
    protected $name = 'massage_store_coach';




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
     * @DataTime: 2021-07-11 01:54
     * @功能说明:关联的门店
     */
    public static function getStoreList($coach_id){

        $store_model = new \app\store\model\StoreList();

        $dis = [

            'b.coach_id' => $coach_id,

            'd.store_auth' => 1
        ];

        $list =  $store_model->alias('a')
            ->join('massage_store_coach b','b.store_id = a.id')
            ->join('massage_service_coach_list c','c.id = b.coach_id')
            ->join('shequshop_school_admin d','(c.admin_id = d.id ||c.admin_id = 0) AND a.admin_id = d.id')
            ->where($dis)
            ->where('a.status','>',-1)
            ->field('a.id,a.title,b.store_id,a.admin_id,a.cover,a.status')
            ->group('a.id')
            ->order('a.id desc')
            ->select()
            ->toArray();

        return $list;
    }


    /**
     * @param $coach_id
     * @param $admin_id
     * @param $lng
     * @param $lat
     * @功能说明:获取距离最近的门店
     * @author chenniang
     * @DataTime: 2024-10-21 19:04
     */
    public static function getNearStore($coach_id,$admin_id,$lng=0,$lat=0,$store_id=0){

        $store_model = new \app\store\model\StoreList();

        $dis = [

            'b.coach_id' => $coach_id,

            'd.store_auth' => 1
        ];

        if(!empty($store_id)){

            $dis['a.id'] = $store_id;
        }

        if(!empty($admin_id)){

            $dis['d.id'] = $admin_id;
        }

        $alh = 'ACOS(SIN(('.$lat.' * 3.1415) / 180 ) *SIN((a.lat * 3.1415) / 180 ) +COS(('.$lat.' * 3.1415) / 180 ) * COS((a.lat * 3.1415) / 180 ) *COS(('.$lng.' * 3.1415) / 180 - (a.lng * 3.1415) / 180 ) ) * 6378.137*1000 as distance';

        $list =  $store_model->alias('a')
            ->join('massage_store_coach b','b.store_id = a.id')
            ->join('shequshop_school_admin d','a.admin_id = d.id')
            ->where($dis)
            ->where('a.status','=',1)
            ->field(['a.id,a.title,a.address,a.lng,a.lat,a.phone,a.start_time,a.end_time',$alh])
            ->group('a.id')
            ->order('distance,a.id desc')
            ->find();

        return $list;
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-11 01:54
     * @功能说明:关联的门店
     */
    public static function getStoreListOrderDistance($dis,$lng=0,$lat=0){

        $store_model = new \app\store\model\StoreList();

        $alh = 'ACOS(SIN(('.$lat.' * 3.1415) / 180 ) *SIN((a.lat * 3.1415) / 180 ) +COS(('.$lat.' * 3.1415) / 180 ) * COS((a.lat * 3.1415) / 180 ) *COS(('.$lng.' * 3.1415) / 180 - (a.lng * 3.1415) / 180 ) ) * 6378.137*1000 as distance';

        $list =  $store_model->alias('a')
            ->join('massage_store_coach b','b.store_id = a.id')
            ->join('massage_service_coach_list c','c.id = b.coach_id')
            ->join('shequshop_school_admin d','(c.admin_id = d.id ||c.admin_id = 0) AND a.admin_id = d.id')
            ->where($dis)
            ->field(['a.*',$alh])
            ->group('a.id')
            ->order('distance,a.id desc')
            ->paginate(10)
            ->toArray();

        return $list;
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-11 01:54
     * @功能说明:关联的门店
     */
    public static function getStoreName($coach_id){

        $store_model = new \app\store\model\StoreList();

        $dis = [

            'b.coach_id' => $coach_id,

            'd.store_auth' => 1
        ];

        $list = $store_model->alias('a')
            ->join('massage_store_coach b','b.store_id = a.id')
            ->join('massage_service_coach_list c','c.id = b.coach_id')
            ->join('shequshop_school_admin d','(c.admin_id = d.id ||c.admin_id = 0) AND a.admin_id = d.id')
            ->where($dis)
            ->where('a.status','>',-1)
            ->group('a.id')
            ->order('a.id desc')
            ->column('a.title');

        return !empty($list)?implode('、',$list):'';
    }

    /**
     * @param $uniacid
     * @功能说明:初始化技师门店
     * @author chenniang
     * @DataTime: 2024-10-17 18:17
     */
    public static function initCoachData($uniacid){

        $list = Db::name('massage_service_coach_list')->where(['status'=>2])->where('store_id','>',0)->field('id,store_id')->limit(300)->select()->toArray();

        $key = 'initCoachDataStore';

        incCache($key,1,$uniacid,30);

        if(getCache($key,$uniacid)==1){

            if(!empty($list)){

                foreach ($list as $value){

                    $insert = [

                        'uniacid' => $uniacid,

                        'coach_id'=> $value['id'],

                        'store_id'=> $value['store_id'],
                    ];

                    StoreCoach::insert($insert);

                    Db::name('massage_service_coach_list')->where(['id'=>$value['id']])->update(['store_id'=>0]);
                }
            }
        }

        decCache($key,1,$uniacid);

        return true;
    }


    /**
     * @param $store
     * @param $uniacid
     * @param $id
     * @功能说明:关联门店信息
     * @author chenniang
     * @DataTime: 2024-10-21 18:28
     */
    public static function dataSave($store,$uniacid,$id){

        if(!empty($store)){

            foreach ($store as $key=>$value){

                $store_insert[$key] = [

                    'uniacid' => $uniacid,

                    'store_id'=> $value,

                    'coach_id'=> $id
                ];
            }

            StoreCoach::createAll($store_insert);
        }

        return true;
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-11 01:54
     * @功能说明:关联的门店
     */
    public static function getStoreCount($coach_id){

        $store_model = new \app\store\model\StoreList();

        $dis = [

            'b.coach_id' => $coach_id,

            'd.store_auth' => 1
        ];

        $list =  $store_model->alias('a')
            ->join('massage_store_coach b','b.store_id = a.id')
            ->join('massage_service_coach_list c','c.id = b.coach_id')
            ->join('shequshop_school_admin d','(c.admin_id = d.id ||c.admin_id = 0) AND a.admin_id = d.id')
            ->where($dis)
            ->where('a.status','=',1)
            ->group('a.id')
            ->column('a.id');

        return count($list);
    }




}