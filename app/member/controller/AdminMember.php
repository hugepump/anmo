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



class AdminMember extends AdminRest
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
     * @DataTime: 2021-03-23 09:20
     * @功能说明:会员等级列表
     */
   public function levelList(){

       $input = $this->_param;

       $dis[] = ['status','>',-1];

       $dis[] = ['uniacid','=',$this->_uniacid];

       $data = $this->model->dataList($dis,$input['limit']);

       $user_model = new User();

       if(!empty($data['data'])){

           foreach ($data['data'] as $k=>&$v){

               $v['vip'] = ($data['current_page']-1)*$input['limit']+$k+1;

               $next = $this->model->where(['status'=>1,'uniacid'=>$this->_uniacid])->where('growth','>',$v['growth'])->order('growth,id desc')->find();

               if(!empty($next)){

                   $next->growth = $next->growth-0.01;

                   $v['member_num'] = $user_model->where(['uniacid'=>$this->_uniacid])->where('growth','between',"{$v['growth']},{$next->growth}")->count();
               }else{

                   $v['member_num'] = $user_model->where(['uniacid'=>$this->_uniacid])->where('growth','>=',$v['growth'])->count();

               }
           }
       }

       return $this->success($data);

   }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-08 09:51
     * @功能说明:添加
     */
   public function levelAdd(){

       $input = $this->_input;

       $find = $this->model->where(['growth'=>$input['growth'],'uniacid'=>$this->_uniacid])->where('status','>',-1)->find();

       if(!empty($find)){

           $this->errorMsg('不能添加相同成长值的等级');
       }

       $min = $this->model->where(['uniacid'=>$this->_uniacid])->where('growth','<',$input['growth'])->where('status','=',0)->find();

       if(!empty($min)){

           $this->errorMsg('请先开启下级等级');
       }

       $insert = [

           'uniacid' => $this->_uniacid,

           'title'   => $input['title'],

           'color'   => $input['color'],

           'growth'  => $input['growth'],
       ];

       $this->model->dataAdd($insert);

       $id = $this->model->getLastInsID();

       $connect_model = new RightsConnect();

       $coupon_model  = new \app\member\model\Coupon();
       //关联权益
       if(!empty($input['rights'])){

           foreach ($input['rights'] as $k=>$v){

               $inserts[$k] = [

                   'uniacid' => $this->_uniacid,

                   'level_id'=> $id,

                   'rights_id'=> $v
               ];

           }

           $connect_model->saveAll($inserts);
       }

       if(!empty($input['coupon'])){

           foreach ($input['coupon'] as $k=>$v){

               $insert_data[$k] = [

                   'uniacid' => $this->_uniacid,

                   'level_id'=> $id,

                   'coupon_id'=> $v['coupon_id'],

                   'num'=> $v['num'],
               ];
           }

           $coupon_model->saveAll($insert_data);
       }

       return $this->success(true,200,$id);
   }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-08 10:07
     * @功能说明:等级详情
     */
   public function levelInfo(){

       $input = $this->_param;

       $dis = [

           'id' => $input['id']
       ];

       $data = $this->model->dataInfo($dis);

       $data['rights'] = array_values(array_column($data['rights'],'rights_id'));

       $data['next_level'] = $this->model->where('status','>',-1)->where('growth','>',$data['growth'])->order('growth,id desc')->find();

       return $this->success($data);
   }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-08 10:43
     * @功能说明:编辑等级
     */
   public function levelUpdate(){

       $input = $this->_input;

       $find = $this->model->where(['growth'=>$input['growth'],'uniacid'=>$this->_uniacid])->where('id','<>',$input['id'])->where('status','>',-1)->find();

       if(!empty($find)){

           $this->errorMsg('不能添加相同成长值的等级');
       }

       $dis = [

           'id' => $input['id']
       ];

       $insert = [

           'uniacid' => $this->_uniacid,

           'title'   => $input['title'],

           'color'   => $input['color'],

           'growth'  => $input['growth'],
       ];

       $this->model->dataUpdate($dis,$insert);

       $id = $input['id'];

       $connect_model = new RightsConnect();

       $coupon_model  = new \app\member\model\Coupon();

       $connect_model->where(['level_id'=>$id])->delete();

       $coupon_model->where(['level_id'=>$id])->delete();
       //关联权益
       if(!empty($input['rights'])){

           foreach ($input['rights'] as $k=>$v){

               $inserts[$k] = [

                   'uniacid' => $this->_uniacid,

                   'level_id'=> $id,

                   'rights_id'=> $v
               ];

           }

           $connect_model->saveAll($inserts);
       }

       if(!empty($input['coupon'])){

           foreach ($input['coupon'] as $k=>$v){

               $insert_data[$k] = [

                   'uniacid' => $this->_uniacid,

                   'level_id'=> $id,

                   'coupon_id'=> $v['coupon_id'],

                   'num'=> $v['num'],
               ];
           }

           $coupon_model->saveAll($insert_data);
       }

       return $this->success(true);
   }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-08 10:53
     * @功能说明:修改等级状态
     */
   public function levelStatusUpdate(){

       $input = $this->_input;

       $dis = [

           'id' => $input['id']
       ];

       $data = $this->model->dataInfo($dis);

       if($input['status']==-1&&$data['status']!=0){

           $this->errorMsg('请先停用');

       }

       if($input['status']==0){

           $max = $this->model->where(['uniacid'=>$this->_uniacid,'status'=>1])->order('growth desc,id desc')->find();

           if($input['id']!=$max->id){

               $this->errorMsg('请先停用最高等级');

           }
       }

       if($input['status']==1){

           $min = $this->model->where(['uniacid'=>$this->_uniacid,'status'=>0])->order('growth,id desc')->find();

           if($input['id']!=$min->id){

               $this->errorMsg('请先启用开启等级的最近等级');
           }
       }

       $res = $this->model->dataUpdate($dis,['status'=>$input['status']]);

       return $this->success($res);
   }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-08 10:56
     * @功能说明:权益列表
     */
   public function rightsList(){

       $input = $this->_param;

       $dis[] = ['uniacid','=',$this->_uniacid];

       $dis[] = ['status','>',-1];

       $data = $this->rights_model->dataList($dis,$input['limit']);

       return $this->success($data);
   }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-08 10:56
     * @功能说明:权益列表
     */
    public function rightsSelect(){

        $input = $this->_param;

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['status','=',1];

        $data = $this->rights_model->where($dis)->field('title,id,key,show_title')->order('top desc,id desc')->select()->toArray();

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-08 10:59
     * @功能说明:权益详情
     */
   public function rightsInfo(){

       $input = $this->_param;

       $dis = [

           'id' => $input['id']
       ];

       $data = $this->rights_model->dataInfo($dis);

       return $this->success($data);
   }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-08 10:59
     * @功能说明:编辑权益
     */
    public function rightsUpdate(){

        $input = $this->_param;

        $dis = [

            'id' => $input['id']
        ];

        $data = $this->rights_model->dataUpdate($dis,$input);

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-08 11:01
     * @功能说明:会员配置详情
     */
    public function configInfo(){

        $input = $this->_param;

        $config_model = new \app\member\model\Config();

        $dis = [

            'uniacid' => $this->_uniacid
        ];

        $data = $config_model->dataInfo($dis);

        return $this->success($data);
    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-08 11:01
     * @功能说明:会员配置编辑
     */
    public function configUpdate(){

        $input = $this->_input;

        $config_model = new \app\member\model\Config();

        $dis = [

            'uniacid' => $this->_uniacid
        ];

        $data = $config_model->dataUpdate($dis ,$input);

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-10 17:22
     * @功能说明:会员等级下拉框
     */
    public function levelSelect(){

        $input = $this->_param;

        $dis[] = ['a.uniacid','=',$this->_uniacid];

        $dis[] = ['a.status','=',1];

        if(!empty($input['is_service'])){

            $dis[] = ['c.key','=','appoint_service'];

        }

        $data = $this->model->alias('a')
                  ->join('massage_member_rights_connect b','a.id = b.level_id','left')
                  ->join('massage_member_rights c','b.rights_id = c.id','left')
                  ->where($dis)
                  ->field('a.*')
                  ->group('a.id')
                  ->order('growth,id desc')
                  ->select()
                  ->toArray();

        return $this->success($data);

    }














}
