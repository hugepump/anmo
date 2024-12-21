<?php
namespace app\map\controller;
use app\ApiRest;

use app\massage\model\ArticleList;
use app\massage\model\CateConnect;
use app\massage\model\City;
use app\massage\model\Coach;
use app\massage\model\CoachCollect;
use app\massage\model\CoachTimeList;
use app\massage\model\Comment;
use app\massage\model\ConfigSetting;
use app\massage\model\Coupon;
use app\massage\model\CouponRecord;
use app\massage\model\MassageConfig;
use app\massage\model\Order;
use app\massage\model\PayConfig;
use app\massage\model\Service;
use app\massage\model\ServiceCoach;
use app\massage\model\ShortCodeConfig;
use app\massage\model\StoreList;
use app\Rest;


use app\massage\model\Banner;

use app\massage\model\Car;
use app\massage\model\Config;

use app\massage\model\User;
use longbingcore\permissions\AdminMenu;
use think\App;

use think\facade\Db;
use think\Request;



class Index extends ApiRest
{

    protected $model;

    protected $coach_model;



    public function __construct(App $app) {

        parent::__construct($app);

        $this->model = new Service();

        $this->coach_model = new Coach();

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-23 09:20
     * @功能说明:地图技师详情
     */
   public function coachList(){

       $input = $this->_param;

       $shield_coach = $this->coach_model->getShieldCoach($this->getUserId());

       if(!empty($input['service_time'])){
           //服务中
           $working_coach = $this->coach_model->getWorkingCoach($this->_uniacid,$input['service_time']);
           //当前时间不可预约
           $cannot = CoachTimeList::getCannotCoach($this->_uniacid,$input['service_time']);

           $working_coach = array_merge($working_coach,$cannot);

           $shield_coach = array_merge($working_coach,$shield_coach);
       }

       if(!empty($input['cate_id'])){

           $cate_model = new CateConnect();

           $service_model = new ServiceCoach();

           $id = $cate_model->where(['cate_id'=>$input['cate_id']])->column('service_id');

           $coach_id = $service_model->where('ser_id','in',$id)->column('coach_id');

           $dis[] = ['id','in',$coach_id];

       }
       $dis[] = ['id','not in',$shield_coach];

       $dis[] = ['uniacid','=',$this->_uniacid];

       $dis[] = ['status','=',2];

       $dis[] = ['auth_status','=',2];

       $dis[] = ['is_work','=',1];

       if(!empty($input['coach_name'])){

           $dis[] = ['coach_name','like','%'.$input['coach_name'].'%'];
       }

       $lat = !empty($input['lat'])?$input['lat']:0;

       $lng = !empty($input['lng'])?$input['lng']:0;

       if(!empty($input['city_id'])){

           $dis[] = ['city_id','=',$input['city_id']];
//
//           $city_model = new City();
//
//           $city = $city_model->where(['id'=>$input['city_id']])->field('lat,lng')->find();
//
//           if(!empty($city)){
//
//               $lat = $city->lat;
//
//               $lng = $city->lng;
//           }
       }

       if(isset($input['sex'])){

           $dis[] = ['sex','=',$input['sex']];
       }

       if(!empty($input['work_time_start'])&&!empty($input['work_time_end'])){

           $dis[] = ['work_time','between',"{$input['work_time_start']},{$input['work_time_end']}"];
       }

       if($this->is_app==0&&getConfigSetting($this->_uniacid,'shield_massage')==1){

           $dis[] = ['industry_type','<>',1];
       }

       $distance = !empty($input['distance'])?$input['distance']:100000;

       $alh = 'ACOS(SIN(('.$lat.' * 3.1415) / 180 ) *SIN((lat * 3.1415) / 180 ) +COS(('.$lat.' * 3.1415) / 180 ) * COS((lat * 3.1415) / 180 ) *COS(('.$lng.' * 3.1415) / 180 - (lng * 3.1415) / 180 ) ) * 6378.137*1000 as distance';

       $data = $this->coach_model->mapCoachList($dis,$alh,$distance);

       return $this->success($data);

   }










}
