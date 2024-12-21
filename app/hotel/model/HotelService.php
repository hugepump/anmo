<?php
namespace app\hotel\model;

use app\BaseModel;
use app\massage\model\City;
use app\massage\model\ServicePositionConnect;
use think\facade\Db;

class HotelService extends BaseModel
{
    //定义表名
    protected $name = 'massage_hotel_service';



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

        return !empty($data)?$data->toArray():[];

    }


    /**
     * @param $hotel
     * @功能说明:酒店关联的项目
     * @author chenniang
     * @DataTime: 2024-11-14 14:32
     */
    public static function getService($hotel,$type=0){

        $dis[] = ['a.hotel_id','=',$hotel['id']];

        $dis[] = ['a.type','=',$type];

        if($hotel['admin_id']!=0){

            $dis[] = ['b.admin_id','in',[$hotel['admin_id'],0]];
        }

        $data = self::alias('a')
                ->join('massage_service_service_list b','a.service_id=b.id')
                ->where($dis)
                ->field('a.*,b.id,b.title,b.cover,b.admin_id,b.time_long,b.sub_title,b.total_sale,round(b.init_price+b.material_price,2) as init_price,b.show_salenum,b.member_service,b.show_unit,ROUND(b.price+b.material_price,2) as price')
                ->group('a.id')
                ->order('a.id desc')
                ->select()
                ->toArray();

        $admin_model = new \app\massage\model\Admin();

        $city_model  = new City();

        $position_model = new ServicePositionConnect();

        if(!empty($data)){

            foreach ($data as &$v){
                //关连的服务部位
                $v['position_title'] = $position_model->positionTitle($v['service_id']);;

                if(!empty($v['admin_id'])){

                    $v['admin_name'] = $admin_model->where(['id'=>$v['admin_id']])->value('agent_name');

                    $city_id = $admin_model->where(['id'=>$v['admin_id']])->value('city_id');

                    $v['admin_city'] = $city_model->where(['id'=>$city_id])->value('title');

                }else{

                    $v['admin_name'] = '平台';
                }
            }
        }

        return $data;
    }



    /**
     * @param $hotel
     * @功能说明:酒店关联的项目
     * @author chenniang
     * @DataTime: 2024-11-14 14:32
     */
    public static function getServiceUpdate($id,$type=1){

        $dis[] = ['a.update_id','=',$id];

        $dis[] = ['a.type','=',$type];


        $data = self::alias('a')
            ->join('massage_service_service_list b','a.service_id=b.id')
            ->where($dis)
            ->field('a.*,b.title,b.cover,b.admin_id')
            ->group('a.id')
            ->order('a.id desc')
            ->select()
            ->toArray();

        $admin_model = new \app\massage\model\Admin();

        $city_model  = new City();

        if(!empty($data)){

            foreach ($data as &$v){

                if(!empty($v['admin_id'])){

                    $v['admin_name'] = $admin_model->where(['id'=>$v['admin_id']])->value('agent_name');

                    $city_id = $admin_model->where(['id'=>$v['admin_id']])->value('city_id');

                    $v['admin_city'] = $city_model->where(['id'=>$city_id])->value('title');

                }else{

                    $v['admin_name'] = '平台';
                }
            }
        }

        return $data;
    }







}