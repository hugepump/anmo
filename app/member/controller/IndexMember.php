<?php
namespace app\member\controller;
use app\AdminRest;
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
use app\member\model\Growth;
use app\member\model\Level;
use app\member\model\Rights;
use app\member\model\RightsConnect;
use app\Rest;


use app\massage\model\Banner;

use app\massage\model\Car;
use app\massage\model\Config;

use app\massage\model\User;
use longbingcore\permissions\AdminMenu;
use think\App;

use think\facade\Db;
use think\Request;



class IndexMember extends ApiRest
{

    protected $model;

    protected $rights_model;

    protected $coach_model;



    public function __construct(App $app) {

        parent::__construct($app);

        $this->model = new Level();

        $this->rights_model = new Rights();

        $this->coach_model = new Coach();

        $this->rights_model->initData($this->_uniacid);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-16 19:25
     * @功能说明:会员配置
     */
    public function configInfo(){

        $input = $this->_param;

        $config_model = new \app\member\model\Config();

        $config = $config_model->dataInfo(['uniacid'=>$this->_uniacid]);

        return $this->success($config);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-23 09:20
     * @功能说明:会员等级列表
     */
   public function index(){

       $input = $this->_param;

       $config_model = new \app\member\model\Config();

       $config = $config_model->dataInfo(['uniacid'=>$this->_uniacid]);

       $dis[] = ['status','=',1];

       $dis[] = ['uniacid','=',$this->_uniacid];

       $data['data'] = $this->model->where($dis)->order('growth,id desc')->select()->toArray();

       if(!empty($data['data'])){

           foreach ($data['data'] as &$v){

               foreach ($v['rights'] as &$vs){

                   if($vs['key']=='send_coupon'){

                       $vs['total_discount'] = array_sum(array_column($v['coupon'],'total_discount'));
                   }
               }
           }
       }

       $data['data'] = array_values($data['data']);

       $data['growth_value'] = $config['growth_value'];

       $data['growth_name'] = $config['growth_name'];

       $data['user_member'] = $this->model->getUserLevel($this->_user['id']);

       $data['user_member'] = !empty($data['user_member'])?$data['user_member']['id']:0;

       $user_model = new User();

       $data['growth'] = $user_model->where(['id'=>$this->_user['id']])->value('growth');

       return $this->success($data);
   }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-16 17:12
     * @功能说明:成长值列表
     */
   public function growthList(){

       $growth_model = new Growth();

       $dis[] = ['user_id','=',$this->_user['id']];

       $data = $growth_model->dataList($dis,10);

       if(!empty($data['data'])){

           foreach ($data['data'] as &$v){

               $v['growth'] = round($v['growth']);

               $year  = date('Y',$v['create_time']);

               $month = date('m',$v['create_time']);

               $v['month_text'] = $year.'年'.$month.'月';

               $v['month'] = date('Y-m',$v['create_time']);

               $v['create_time'] = date('Y.m.d H:i:s',$v['create_time']);

               $v['total_growth']  = $growth_model->where($dis)->where(['is_add'=>1])->whereMonth('create_time',$v['month'])->sum('growth');

               $del_growth = $growth_model->where($dis)->where(['is_add'=>0])->whereMonth('create_time',$v['month'])->sum('growth');

               $v['total_growth'] = round($v['total_growth'] - $del_growth);

               $v['total_count'] = $growth_model->where($dis)->where(['is_add'=>1])->whereMonth('create_time',$v['month'])->count();
           }
       }

       $user_model = new User();

       $data['growth'] = $user_model->where(['id'=>$this->_user['id']])->value('growth');

       return $this->success($data);

   }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-16 17:51
     * @功能说明:权益详情
     */
   public function rightsInfo(){

       $input = $this->_param;

       $service_model = new \app\member\model\Service();

       $rights = $this->rights_model->dataInfo(['id'=>$input['rights_id']]);

       $level  = $this->model->dataInfo(['id'=>$input['id']]);

       $dis = [

           'a.status' => 1,

           'b.rights_id' => $input['rights_id']
       ];

       $list = $this->model->alias('a')
           ->join('massage_member_rights_connect b','a.id = b.level_id')
           ->where($dis)
           ->field('a.*')
           ->group('a.id')
           ->order('a.growth,a.id desc')
           ->select()
           ->toArray();
       //卡券权益
       if($rights['key']=='send_coupon'){

           $rights['coupon'] = $level['coupon'];

           $rights['total_discount'] = array_sum(array_column($rights['coupon'],'total_discount'));

       }

       if(!empty($list)){

           foreach ($list as &$value){

               if($rights['key']=='send_coupon'){

                   $value['total_discount'] = array_sum(array_column($value['coupon'],'total_discount'));

               }elseif ($rights['key'] =='appoint_service'){

                   $dis = [

                       'b.status' => 1,

                       'b.member_service' => 1,

                       'a.level_id'=> $value['id']
                   ];

                   $value['service_count'] = $service_model->alias('a')
                                             ->join('massage_service_service_list b','a.service_id = b.id')
                                             ->where($dis)
                                             ->group('b.id')
                                             ->count();
               }
           }
       }

       $data['rights_info'] = $rights;

       $data['level_list']   = $list;

       return $this->success($data);
   }






















}
