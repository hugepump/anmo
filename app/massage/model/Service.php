<?php
namespace app\massage\model;

use app\BaseModel;
use think\facade\Db;

class Service extends BaseModel
{
    //定义表名
    protected $name = 'massage_service_service_list';


    protected $append = [

        'create_time_text',

    ];



    /**
     * @param $value
     * @param $data
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-23 11:12
     */
    public function getImgsAttr($value,$data){

        if(!empty($value)){

            return explode(',',$value);

        }

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-23 11:12
     * @功能说明:
     */
    public function getCreateTimeTextAttr($value,$data){

        if(!empty($data['create_time'])){

            return date('Y-m-d H:i:s',$data['create_time']);
        }

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:04
     * @功能说明:添加
     */
    public function dataAdd($data){

        $data['create_time'] = time();

        if(isset($data['sale'])){

            $data['total_sale'] = $data['sale'];
        }

        $coach = $data['coach'];

        unset($data['coach']);

        $cate_id = $data['cate_id'];

        unset($data['cate_id']);

        $service_level = [];

        if(isset($data['member_service'])){

            $service_level = $data['service_level'];

            unset($data['service_level']);
        }

        $position = [];

        if(isset($data['position'])){

            $position = $data['position'];

            unset($data['position']);
        }

        $guarantee = [];

        if(isset($data['guarantee'])){

            $guarantee = $data['guarantee'];

            unset($data['guarantee']);
        }

        $data['imgs'] = !empty($data['imgs'])?implode(',',$data['imgs']):'';

        $res = $this->insert($data);

        $id  = $this->getLastInsID();

        $this->updateSome($id,$data['uniacid'],$coach,$cate_id,$service_level,$position,$guarantee);

        return $id;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:05
     * @功能说明:编辑
     */
    public function dataUpdate($dis,$data){

        if(isset($data['coach'])){

            $coach = $data['coach'];

            unset($data['coach']);
        }

        if(isset($data['cate_id'])){

            $cate_id = $data['cate_id'];

            unset($data['cate_id']);
        }

        $service_level = [];

        if(isset($data['service_level'])){

            $service_level = $data['service_level'];

            unset($data['service_level']);
        }

        if(isset($data['imgs'])){

            $data['imgs'] = !empty($data['imgs'])?implode(',',$data['imgs']):'';
        }

        if(isset($data['sale'])&&isset($data['true_sale'])){

            $data['total_sale'] = $data['sale']+$data['true_sale'];
        }

        $position = [];

        if(isset($data['position'])){

            $position = $data['position'];

            unset($data['position']);
        }

        $guarantee = [];

        if(isset($data['guarantee'])){

            $guarantee = $data['guarantee'];

            unset($data['guarantee']);
        }

        $res = $this->where($dis)->update($data);

        if(isset($coach)&&isset($cate_id)){

            $this->updateSome($dis['id'],$data['uniacid'],$coach,$cate_id,$service_level,$position,$guarantee);
        }

        return $res;
    }


    /**
     * @param $id
     * @param $uniacid
     * @param $spe
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-23 13:35
     */
    public function updateSome($id,$uniacid,$coach,$cate_id=[],$service_level=[],$position=[],$guarantee=[]){

        $s_model = new ServiceCoach();

        $s_model->where(['ser_id'=>$id])->delete();

        if(!empty($coach)){

            foreach ($coach as $value){

                $insert['coach_id'] = $value['coach_id'];

                $insert['uniacid']  = $uniacid;

                $insert['ser_id']   = $id;

                $insert['price']    = $value['price'];

                $s_model->dataAdd($insert);

            }

        }

        $cate_model = new CateConnect();

        $cate_model->where(['service_id'=>$id])->delete();

        if(!empty($cate_id)){

            foreach ($cate_id as $value){

                $insert = [

                    'uniacid' => $uniacid,

                    'service_id' => $id,

                    'cate_id' => $value
                ];

                $cate_model->dataAdd($insert);
            }

        }

        $member_service_model = new \app\member\model\Service();

        $member_service_model->where(['service_id'=>$id])->delete();

        if(!empty($service_level)){

            foreach ($service_level as $value){

                $insert = [

                    'uniacid' => $uniacid,

                    'service_id' => $id,

                    'level_id' => $value
                ];

                $member_service_model->dataAdd($insert);
            }
        }

        $position_model = new ServicePositionConnect();

        $position_model->where(['service_id'=>$id])->delete();

        if(!empty($position)){

            foreach ($position as $value){

                $insert = [

                    'uniacid' => $uniacid,

                    'service_id' => $id,

                    'position_id' => $value
                ];

                $position_model->dataAdd($insert);
            }
        }


        ServiceGuaranteeConnect::where(['service_id' => $id])->delete();

        if (!empty($guarantee)) {

            foreach ($guarantee as $item) {

                $insert = [

                    'uniacid' => $uniacid,

                    'service_id' => $id,

                    'guarantee_id' => $item
                ];

                ServiceGuaranteeConnect::insert($insert);
            }
        }

        return true;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:06
     * @功能说明:列表
     */
    public function dataList($dis,$page,$top='top desc,id desc',$where=[]){

        $data = $this->where($dis)
            ->where(function ($query) use ($where) {
                $query->whereOr($where);
            })
            ->order($top)->paginate($page)->toArray();

        return $data;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:06
     * @功能说明:列表
     */
    public function indexDataList($dis,$page,$sort){

        $data = $this->where($dis)->field('show_salenum,member_service,id,material_price,title,cover,round(init_price+material_price,2) as init_price,ROUND(price+material_price,2) as price,is_add,type,time_long,total_sale,sub_title,show_unit')->order("$sort,id desc")->paginate($page)->toArray();

        if(!empty($data['data'])){

            $position_model = new ServicePositionConnect();

            foreach ($data['data'] as $k=>$v){
                //关连的服务部位
                $data['data'][$k]['position_title'] = $position_model->positionTitle($v['id']);
            }
        }

        return $data;
    }





    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:43
     * @功能说明:
     */
    public function dataInfo($dis,$admin_id=0){

        $data = $this->where($dis)->find();

        if(!empty($data)){

            $data->toArray();

            $coach = $this->getServiceCoach($data['id'],$admin_id);

            if(!empty($coach)){

                $admin_model = new Admin();

                foreach ($coach as $k=>$vs){

                    $coach[$k]['price'] = $vs['price']>=0?$vs['price']:$data['price'];

                    if(!empty($vs['admin_id'])){

                        $coach[$k]['admin_name'] = $admin_model->where(['id'=>$vs['admin_id'],'status'=>1,'agent_coach_auth'=>1])->value('agent_name');
                    }else{

                        $coach[$k]['admin_name'] = '';
                    }
                }
            }

            $data['coach'] = $coach;

            return $data;
        }else{

            return [];
        }

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:43
     * @功能说明:
     */
    public function serviceInfo($dis){

        $data = $this->where($dis)->find();

        return !empty($data)?$data->toArray():[];


    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-07 10:21
     * @功能说明:服务技师列表
     */
    public function serviceCoachList($dis){

        $data = $this->alias('a')
            ->join('massage_service_service_coach b','a.id = b.ser_id','left')
            ->where($dis)
            ->field(['a.show_salenum,a.industry_type,a.member_service,a.id,a.title,a.cover,round(a.init_price+a.material_price,2) as init_price,a.price,a.is_add,a.type,a.time_long,a.total_sale,a.sub_title,a.admin_id,a.material_price,b.price as coach_price,IF(b.price<0,a.price,b.price) as price,a.show_unit'])
            ->group('a.id')
            ->order('a.top desc,a.id desc')
            ->select()
            ->toArray();

        if(!empty($data)){

            $position_model = new ServicePositionConnect();

            foreach ($data as $k=>$v){
                //关连的服务部位
                $data[$k]['position_title'] = $position_model->positionTitle($v['id']);
            }
        }

        return $data;

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-07 10:21
     * @功能说明:服务技师列表
     */
    public function serviceCoachPageList($dis,$sort){

        $sort = 'a.'.$sort.',a.top desc,a.id desc';

        $data = $this->alias('a')
            ->join('massage_service_service_coach b','a.id = b.ser_id')
            ->join('massage_service_coach_list c','b.coach_id = c.id')
            ->where($dis)
            ->field(['a.show_salenum,a.industry_type,a.member_service,a.id,a.title,a.cover,round(a.init_price+a.material_price,2) as init_price,a.price,a.is_add,a.type,a.time_long,a.total_sale,a.sub_title,a.admin_id,a.material_price,b.price as coach_price,ROUND(a.price+a.material_price,2) as price,a.show_unit'])
            ->group('a.id')
            ->order("$sort")
            ->paginate(10)
            ->toArray();

        if(!empty($data['data'])){

            $position_model = new ServicePositionConnect();

            foreach ($data['data'] as $k=>$v){
                //关连的服务部位
                $data['data'][$k]['position_title'] = $position_model->positionTitle($v['id']);;
            }
        }

        return $data;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-11-07 17:50
     * @功能说明:如果技师和服务不属于同一个代理商就删除
     */
    public function delDiffCoachAndService($uniacid){

        $dis[] = ['a.admin_id','>',0];

        $dis[] = ['a.uniacid','=',$uniacid];

        $data = $this->alias('a')
            ->join('massage_service_service_coach b','a.id = b.ser_id')
            ->join('massage_service_coach_list c','b.coach_id = c.id AND a.admin_id <> c.admin_id')
            ->where($dis)
            ->group('b.id')
            ->column('b.id');

        $ser_coach_model = new ServiceCoach();

        if(!empty($data)){

            $ser_coach_model->where('id','in',$data)->delete();
        }

        return $data;
    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-07 10:21
     * @功能说明:服务技师列表
     */
    public function upServiceCoachList($dis,$price){

        $data = $this->alias('a')
            ->join('massage_service_service_coach b','a.id = b.ser_id','left')
            ->where($dis)
            ->field(['a.member_service,a.id,a.title,a.cover,a.init_price,a.is_add,a.type,a.time_long,a.total_sale,a.sub_title,a.admin_id,a.material_price,ROUND(IF(b.price<0,a.price,b.price)+a.material_price,2) as total_price,ROUND(IF(b.price<0,a.price,b.price)+a.material_price,2) as price'])
            ->having("total_price>=$price")
            ->group('a.id')
            ->order('a.top desc,a.id desc')
            ->select()
            ->toArray();

        return $data;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-17 11:15
     * @功能说明:查看每个商品的会员权限
     */
    public function giveListMemberInfo($data,$uniacid,$user_id,$type=1){

        $member_service = new \app\member\model\Service();
        //获取自己的会员等级
        $user_member = $member_service->getUserMember($uniacid,$user_id);

        if(!empty($data)){

            foreach ($data as $k=>$v){

                $v['member_service'] = $user_member['member_auth']==1?$v['member_service']:0;

                if($type==1){
                    //该服务是否支持
                    $data[$k]['member_info'] = $member_service->getServiceMember($v['id'],$user_member['member_level'],$v['member_service']);
                }else{
                    //该服务是否支持
                    $data[$k]['member_info'] = $member_service->getServiceMember($v['service_id'],$user_member['member_level'],$v['member_service']);
                }
            }
        }

        return $data;
    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-09-15 16:20
     * @功能说明:获取销量最高的技师
     */
    public function getSaleTopOne($uniacid,$city_id=0,$coach_icon_type=0){

        if($coach_icon_type==1){

            return [];
        }

        $key = 'getSaleTopOnezzzss'.$city_id;

        $data = getCache($key,$uniacid);

        if(!empty($data)){

            return $data;
        }

        $coach_model = new Coach();

        $dis[] = ['b.pay_type','>',1];

        $dis[] = ['a.uniacid','=',$uniacid];

        $dis[] = ['a.status','=',2];

        $dis[] = ['a.recommend_icon','=',0];

        if(!empty($city_id)){

            $dis[] = ['a.city_id','=',$city_id];
        }

        $num = getConfigSetting($uniacid,'coach_top_num');

        $data = $coach_model->alias('a')
                ->join('massage_service_order_list b','a.id = b.coach_id')
                ->where($dis)
                ->whereTime('b.create_time','week')
                ->field('sum(b.true_service_price) as all_price,a.id as coach_id')
                ->group('a.id')
                ->order('all_price desc,a.id desc')
                ->limit($num)
                ->select()
                ->toArray();

        $data = array_column($data,'coach_id');

        setCache($key,$data,5,$uniacid);

        return $data;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-09-15 16:20
     * @功能说明:获取最近其他的技师
     */
    public function getSaleTopSeven($uniacid,$city_id=0,$coach_icon_type=0){

        if($coach_icon_type==1){

            return [];
        }

        $key = 'getSaleTopSeven_getSaleTopSeven'.$city_id;

        $data = getCache($key,$uniacid);

        if(!empty($data)){

            return $data;
        }

        $coach_model = new Coach();

        $dis[] = ['status','=',2];

        $dis[] = ['uniacid','=',$uniacid];

        $dis[] = ['recommend_icon','=',0];

        if(!empty($city_id)){

            $dis[] = ['city_id','=',$city_id];
        }

        $data = $coach_model->where($dis)->whereTime('sh_time','-7 days')->column('id');

        setCache($key,$data,5,$uniacid);

        return $data;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-09-15 16:20
     * @功能说明:获取销量最高的技师
     */
    public function getSaleTopFive($uniacid,$coach_arr,$city_id=0,$coach_icon_type=0){

        if($coach_icon_type==1){

            return [];
        }

        $key = 'getSaleTopFive_getSaleTopFive'.$city_id;

        $value = getCache($key,$uniacid);

        if(!empty($value)){

            return $value;
        }

        $coach_model = new Coach();

        $where = [

            'status' => 2,

            'uniacid'=> $uniacid,

            'recommend_icon'=> 0,
        ];

        if(!empty($city_id)){

            $where['city_id'] = $city_id;
        }

        $coach_id = $coach_model->where($where)->column('id');

        $coach_id = array_diff($coach_id,$coach_arr);

        $order_model = new Order();

        $dis[] = ['pay_type','>',1];

        $dis[] = ['uniacid','=',$uniacid];

        $dis[] = ['coach_id','in',$coach_id];

        $num = getConfigSetting($uniacid,'coach_hot_num');

        $data = $order_model->where($dis)->field('sum(true_service_price) as counts,coach_id')->whereTime('create_time','week')->group('coach_id')->order('counts desc,id desc')->limit($num)->select()->toArray();

        $info =  array_column($data,'coach_id');

        setCache($key,$info,5,$uniacid);

        return $info;

    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-18 10:07
     * @功能说明:增加|减少库存 增加|减少销量
     */
    public function setOrDelStock($goods_id,$num,$type=2){

        if(empty($goods_id)){

            return true;
        }

        $goods_info = $this->dataInfo(['id'=>$goods_id]);

        if(empty($goods_info)){

            return true;
        }
        //退货
        if($type==1){

            $update = [

                'true_sale' => $goods_info['true_sale']-$num,

                'total_sale'=> $goods_info['total_sale']-$num,

                'lock'      => $goods_info['lock']+1,

            ];
            //如果是售后增加退款数量
//            if($refund==1){
//
//                $update['refund_num'] = $goods_info['refund_num']+$num;
//            }
            //减销量 加退款数量
            $res = $this->where(['id'=>$goods_id,'lock'=>$goods_info['lock']])->update($update);

            if($res!=1){

                return ['code'=>500,'msg'=>'提交失败'];
            }

        }else{

            $update = [

                'true_sale' => $goods_info['true_sale']+$num,

                'total_sale'=> $goods_info['total_sale']+$num,

                'lock'      => $goods_info['lock']+1,

            ];
            //增加销量
            $res = $this->where(['id'=>$goods_id,'lock'=>$goods_info['lock']])->update($update);

            if($res!=1){

                return ['code'=>500,'msg'=>'提交失败'];
            }


        }

        return true;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-06 23:52
     * @功能说明:绑定的技师
     */
    public function getServiceCoach($ser_id,$admin_id=0){

        if(!empty($ser_id)){

            $dis = [

                'a.ser_id' => $ser_id,

                'b.status' => 2
            ];

            if(!empty($admin_id)){

                $dis['b.admin_id'] = $admin_id;
            }

            $coach_s_model = new ServiceCoach();

            $list = $coach_s_model->alias('a')
                    ->join('massage_service_coach_list b','a.coach_id = b.id')
                    ->where($dis)
                    ->field('b.id,b.coach_name,b.work_img,a.price,b.admin_id')
                    ->group('a.coach_id')
                    ->select()
                    ->toArray();

            return $list;

            $coach_model = new Coach();

            $list  = $coach_s_model->where(['ser_id'=>$ser_id])->column('coach_id');
            //门店服务只查询 代理商关联的技师
            if($type==2){

                $store_model = new StoreList();

                $coach_id = $store_model->getAdminStoreCoach($admin_id);

                $list = array_intersect($coach_id,$list);
            }

            $coach = $coach_model->where('id','in',$list)->where(['status'=>2])->field('id,coach_name,work_img')->select()->toArray();

            return $coach;
        }

    }


    /**
     * @param $store_id
     * @功能说明:获取门店关联的服务
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-28 22:23
     */
    public function getStoreService($store_id,$where=[]){

        $dis = [

            'a.status' => 1,

            'a.is_store' => 1,

            'a.is_add' => 0,

            'a.check_status' => 2,

            'c.status' => 2,
        ];

        $store_model = new \app\store\model\StoreList();

        $coach_id = $store_model->getStoreCoachId($store_id);

        $data = $this->alias('a')
                ->join('massage_service_service_coach b','a.id = b.ser_id')
                ->join('massage_service_coach_list c','b.coach_id = c.id')
                ->where($dis)
                ->where($where)
                ->where('c.id','in',$coach_id)
                ->field('a.show_salenum,a.member_service,a.id,a.title,a.cover,ROUND(a.init_price+a.material_price,2) as init_price,ROUND(a.price+a.material_price,2) as price,a.sub_title,a.time_long,a.total_sale,a.show_unit')
                ->group('a.id')
                ->order('a.top desc,a.id desc')
                ->limit(5)
                ->select()
                ->toArray();

        if(!empty($data)){

            $position_model = new ServicePositionConnect();

            foreach ($data as $k=>$v){
                //关连的服务部位
                $data[$k]['position_title'] = $position_model->positionTitle($v['id']);
            }
        }

        return $data;
    }







    /**
     * @param $store_id
     * @功能说明:获取门店关联的服务列表
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-29 17:46
     */
    public function getStoreServicePage($store_id,$where=[]){

        $dis = [

            'a.status' => 1,

            'a.is_store' => 1,

            'c.status' => 2,

          //  'c.store_id'=> $store_id,

            'a.is_add' => 0,

            'a.check_status' => 2
        ];

        $store_model = new \app\store\model\StoreList();

        $coach_id = $store_model->getStoreCoachId($store_id);

        $data = $this->alias('a')
            ->join('massage_service_service_coach b','a.id = b.ser_id')
            ->join('massage_service_coach_list c','b.coach_id = c.id')
            ->where($dis)
            ->where($where)
            ->where('c.id','in',$coach_id)
            ->field('a.id,a.show_salenum,a.member_service,a.title,a.cover,a.material_price,a.init_price,ROUND(a.price+a.material_price,2) as price,a.sub_title,a.time_long,a.total_sale,show_unit')
            ->group('a.id')
            ->order('a.top desc,a.id desc')
            ->paginate(10)
            ->toArray();

        return $data;
    }


    /**
     * @param $dis
     * @功能说明:技师关联的服务
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-09-13 11:09
     */
    public function getCoachService($dis,$page=10){

        $data = $this->alias('a')
            ->join('massage_service_service_coach b','a.id = b.ser_id','left')
            ->where($dis)
            ->field(['a.member_service,a.industry_type,b.id,a.id as service_id,a.title,a.cover,a.init_price,a.price,a.is_add,a.type,a.time_long,a.total_sale,a.sub_title,a.admin_id,a.material_price,b.price as coach_price'])
            ->group('a.id')
            ->order('a.top desc,a.id desc')
            ->paginate($page)
            ->toArray();

        return $data;
    }



}