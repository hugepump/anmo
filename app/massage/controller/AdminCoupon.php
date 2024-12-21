<?php
namespace app\massage\controller;
use app\AdminRest;
use app\massage\model\City;
use app\massage\model\Coupon;
use app\massage\model\CouponAtv;
use app\massage\model\CouponAtvRecord;
use app\massage\model\CouponRecord;
use app\massage\model\SendMsgConfig;
use app\massage\model\User;
use app\shop\model\Article;
use app\shop\model\Banner;
use app\shop\model\Cap;
use app\shop\model\Date;
use app\shop\model\MsgConfig;
use app\shop\model\OrderAddress;
use app\shop\model\OrderGoods;
use app\shop\model\RefundOrder;
use app\shop\model\RefundOrderGoods;
use app\shop\model\Wallet;
use think\App;
use app\shop\model\Order as Model;
use think\facade\Db;


class AdminCoupon extends AdminRest
{


    protected $model;

    protected $atv_model;

    protected $record_model;

    public function __construct(App $app) {

        parent::__construct($app);

        $this->model = new Coupon();

        $this->atv_model = new CouponAtv();

        $this->record_model = new CouponRecord();


    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-04 19:09
     * @功能说明:优惠券列表
     */
    public function couponList(){

        $input = $this->_param;

        $dis[] = ['uniacid','=',$this->_uniacid];

        if(!empty($input['status'])){

            $dis[] = ['status','=',$input['status']];

        }else{

            $dis[] = ['status','>',-1];

        }

        if($this->_user['is_admin']==0){

            $dis[] = ['admin_id','=',$this->_user['admin_id']];
        }

        if(isset($input['send_type'])){

            $dis[] = ['send_type','=',$input['send_type']];
        }

        if(isset($input['use_scene'])){

            $dis[] = ['use_scene','=',$input['use_scene']];
        }

        if(!empty($input['name'])){

            $dis[] = ['title','like','%'.$input['name'].'%'];
        }

        $admin_model = new \app\massage\model\Admin();

        if(isset($input['admin_id'])){

            $dis[] = ['admin_id','=',$input['admin_id']];
        }

        $data = $this->model->dataList($dis,$input['limit']);

        if(!empty($data['data'])){

            $city_model  = new City();

            foreach ($data['data'] as &$v){

                if(!empty($v['admin_id'])){

                    $v['admin_name'] = $admin_model->where(['id'=>$v['admin_id']])->value('agent_name');

                    $city_id = $admin_model->where(['id'=>$v['admin_id']])->value('city_id');

                    $v['admin_city'] = $city_model->where(['id'=>$city_id])->value('title');
                }else{

                    $v['admin_name'] = '平台';
                }
            }
        }

        return $this->success($data);

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-04 18:56
     * @功能说明:添加优惠券
     */
    public function couponAdd(){

        $input = $this->_input;

        $input['uniacid'] = $this->_uniacid;

        if($this->_user['is_admin']==0){

            $input['admin_id'] = $this->_user['admin_id'];
        }

        $res = $this->model->dataAdd($input);

        return $this->success($res,200,$res);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-04 18:57
     * @功能说明:编辑优惠券
     */
    public function couponUpdate(){

        $input = $this->_input;

        $dis = [

            'id' => $input['id']
        ];

        $input['uniacid'] = $this->_uniacid;
        //删除优惠券 需要判断该优惠券是否正在参加活动
        if(isset($input['status'])&&$input['status']==-1){

            $atv_record_model = new CouponAtvRecord();

            $have_atv = $atv_record_model->couponIsAtv($input['id']);

            if($have_atv==true){

                $this->errorMsg('该优惠券正在参加活动，只有等用户发起等活动结束后才能删除');
            }
        }

        $res = $this->model->dataUpdate($dis,$input);

        return $this->success($res);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-04 18:59
     * @功能说明:优惠券详情
     */
    public function couponInfo(){

        $input = $this->_param;

        $dis = [

            'id' => $input['id']
        ];

        $res = $this->model->dataInfo($dis);

        if(!empty($res['store'])){

            $admin_model = new \app\massage\model\Admin();

            foreach ($res['store'] as &$v){

                if(!empty($v['admin_id'])){

                    $v['admin_name'] = $admin_model->where(['id'=>$v['admin_id'],'status'=>1])->value('agent_name');
                }

                if(empty($v['admin_name'])){

                    $v['admin_name'] = '平台';
                }
            }
        }

        return $this->success($res);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-06 19:19
     * @功能说明:活动详情
     */
    public function couponAtvInfo(){

        $input = $this->_param;

        $dis = [

            'uniacid' => $this->_uniacid
        ];

        $data = $this->atv_model->dataInfo($dis);

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-06 19:22
     * @功能说明:活动编辑
     */
    public function couponAtvUpdate(){

        $input = $this->_input;

        $dis = [

            'uniacid' => $this->_uniacid
        ];

        $input['uniacid'] = $this->_uniacid;

        $data = $this->atv_model->dataUpdate($dis,$input);

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-12 16:26
     * @功能说明:后台派发卡劵
     */
    public function couponRecordAdd(){

        $input = $this->_input;

        foreach ($input['user'] as $value){

            $res = $this->record_model->recordAdd($input['coupon_id'],$value['id'],$value['num']);

            if(!empty($res['code'])){

                $this->errorMsg($res['msg']);
            }
        }

        $user = array_column($input['user'],'id');

        $coupon = $this->model->dataInfo(['id'=>$input['coupon_id']]);
        //发送通知
        publisher(json_encode(['user'=>$user,'coupon'=>$coupon,'action'=>'coupon_notice'],true));

        return $this->success($res);
    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-04-15 16:12
     * @功能说明:优惠券统计
     */
    public function couponData(){

        $input = $this->_param;

        $limit = $input['limit'];

        $page  = $input['page'];

        $start = $limit;

        $end   = ($page-1)*$limit;

        $dis_sql  = "a.uniacid = $this->_uniacid";

        $where[] = ['uniacid','=',$this->_uniacid];

        if(!empty($input['title'])){

            $title = $input['title'];

            $dis_sql.= " AND a.title like '%$title%'";

            $where[] = ['title','like','%'.$title.'%'];
        }

        if(isset($input['admin_id'])){

            $admin_id = $input['admin_id'];

            $dis_sql.= " AND a.admin_id = $admin_id";

            $where[] = ['admin_id','=',$admin_id];
        }

        if($this->_user['is_admin']==0){

            $admin_id = $this->_user['admin_id'];

            $dis_sql.= " AND a.admin_id = $admin_id";

            $where[] = ['admin_id','=',$admin_id];
        }

        $input['top_type'] = !empty($input['top_type'])?$input['top_type']:0;

        $sort = !empty($input['sort'])&&$input['sort']==2?'asc':'desc';

        switch ($input['top_type']){

            case 1:

                $top = "send_type_desc $sort,total_num $sort,a.id desc";

                break;

            case 2:

                $top = "get_num $sort,a.id desc";

                break;
            case 3:

                $top = "use_num $sort,a.id desc";

                break;
            case 4:

                $top = "discount_price $sort,a.id desc";

                break;

            case 5:

                $top = "total_price $sort,a.id desc";

                break;

            case 6:

                $top = "total_balance $sort,a.id desc";

                break;
            case 7:

                $top = "order_count $sort,a.id desc";

                break;
            case 8:

                $top = "old_user_num $sort,a.id desc";

                break;
            case 9:

                $top = "new_user_num $sort,a.id desc";

                break;

            default:
                $top = "a.id desc";

                break;
        }

        $coupon_model = new Coupon();

        $coupon_record = new CouponRecord();

        $list = $coupon_record->alias('a')
            ->join('massage_service_user_list b','a.user_id = b.id')
            ->where(['a.status'=>2,'a.is_new'=>2])
            ->whereRaw(("DATE_FORMAT(FROM_UNIXTIME(a.use_time),'%m/%d/%Y') = DATE_FORMAT(FROM_UNIXTIME(b.create_time),'%m/%d/%Y')"))
            ->group('a.id')
            ->column('a.id');

        $coupon_record->where('id','in',$list)->update(['is_new'=>1]);

        $count = $coupon_model->where($where)->count();

        $sql = "SELECT if(a.send_type=2,0,1) as send_type_desc,a.send_type,a.id,b.coupon_id as record_id,b.id as order_id,a.admin_id,h.agent_name,a.title,a.status,a.stock+ifnull(b.total_num,0) as total_num,ifnull(b.new_user_num,0) as new_user_num,ifnull(b.old_user_num,0) as old_user_num,ifnull(b.total_num,0) as get_num,ifnull(order_count,0) as order_count,ifnull(b.use_num,0) as use_num,round(ifnull(discount_price,0),2) as discount_price,round(ifnull(total_price,0),2) as total_price,round(ifnull(discount_price/total_price*100,0),2) as total_balance
             FROM `ims_massage_service_coupon` `a`
             LEFT JOIN (SELECT sum(num) as total_num,sum(CASE WHEN status = 2 THEN num ELSE 0 END ) as use_num,sum(CASE WHEN status = 2 THEN discount ELSE 0 END ) as discount_price,sum(CASE WHEN status = 2 AND is_new = 2 THEN num ELSE 0 END ) as old_user_num,sum(CASE WHEN status = 2 AND is_new = 1 THEN num ELSE 0 END ) as new_user_num,coupon_id,id FROM `ims_massage_service_coupon_record` GROUP BY coupon_id) AS b ON a.id=b.coupon_id 
             LEFT JOIN (SELECT agent_name,id FROM `ims_shequshop_school_admin`) AS h ON a.admin_id=h.id
             LEFT JOIN (SELECT sum(dd.true_service_price+dd.material_price) as total_price,count(dd.id) as order_count,aa.coupon_id FROM `ims_massage_service_order_list` dd LEFT JOIN `ims_massage_service_coupon_record` as aa ON dd.id=aa.order_id where dd.pay_type > -1 GROUP BY aa.coupon_id) as gg ON gg.coupon_id = a.id
             WHERE $dis_sql GROUP BY a.id ORDER BY $top LIMIT $start OFFSET $end";

//        $sql = "SELECT if(a.send_type=2,0,1) as send_type_desc,a.send_type,a.id,c.coupon_id as record_id,c.id as order_id,a.admin_id,h.agent_name,a.title,a.status,a.stock+ifnull(b.total_num,0) as total_num,ifnull(g.new_user_num,0) as new_user_num,ifnull(f.old_user_num,0) as old_user_num,ifnull(b.total_num,0) as get_num,ifnull(order_count,0) as order_count,ifnull(c.use_num,0) as use_num,round(ifnull(discount_price,0),2) as discount_price,round(ifnull(total_price,0),2) as total_price,round(ifnull(discount_price/total_price*100,0),2) as total_balance
//             FROM `ims_massage_service_coupon` `a`
//             LEFT JOIN (SELECT sum(num) as total_num,coupon_id FROM `ims_massage_service_coupon_record` GROUP BY coupon_id) AS b ON a.id=b.coupon_id
//             LEFT JOIN (SELECT sum(num) as use_num,coupon_id,order_id,user_id,id,use_time,uniacid FROM `ims_massage_service_coupon_record` where status = 2 GROUP BY coupon_id) AS c ON a.id=c.coupon_id
//             LEFT JOIN (SELECT count(user_id) as old_user_num,coupon_id FROM `ims_massage_service_coupon_record` where status = 2 AND is_new = 2 GROUP BY coupon_id) AS f ON a.id=f.coupon_id
//             LEFT JOIN (SELECT count(user_id) as new_user_num,coupon_id FROM `ims_massage_service_coupon_record` where status = 2 AND is_new = 1 GROUP BY coupon_id) AS g ON a.id=g.coupon_id
//             LEFT JOIN (SELECT agent_name,id FROM `ims_shequshop_school_admin`) AS h ON a.admin_id=h.id
//             LEFT JOIN (SELECT sum(aa.discount) as discount_price,sum(dd.true_service_price+dd.material_price) as total_price,count(dd.id) as order_count,aa.coupon_id FROM `ims_massage_service_order_list` dd LEFT JOIN `ims_massage_service_coupon_record` as aa ON dd.id=aa.order_id where dd.pay_type > -1 GROUP BY aa.coupon_id) as gg ON gg.coupon_id = a.id
//             WHERE $dis_sql GROUP BY a.id ORDER BY $top LIMIT $start OFFSET $end";

        $data = Db::query($sql);

        $admin_model = new \app\massage\model\Admin();

        $city_model  = new City();

        if(!empty($data)){

            foreach ($data as &$v){

                if($v['send_type']!=2){

                    $v['total_num'] = '不限';
                }

                if($v['admin_id']==0){

                    $v['agent_name'] = '平台';

                }else{

                    $city_id = $admin_model->where(['id'=>$v['admin_id']])->value('city_id');

                    $v['admin_city'] = $city_model->where(['id'=>$city_id])->value('title');
                }
            }
        }

        $arr['data'] = $data;

        $arr['total']= $count;

        $arr['current_page'] = $page;

        $arr['per_page']     = $limit;

        $arr['last_page']    = ceil($arr['total']/$limit);

        return $this->success($arr);
    }


    /**
     * @author chenniang
     * @DataTime: 2024-10-24 10:09
     * @功能说明:卡券核销记录
     */
    public function couponHxRecordList(){

        $input = $this->_param;

        $coupon_model = new CouponRecord();

        $coupon_model->initCoupon($this->_uniacid);

        $dis[] = ['a.use_scene','=',$input['use_scene']];
        //核销时间
        if(!empty($input['start_time'])&&!empty($input['end_time'])){

            $dis[] = ['a.use_time','between',"{$input['start_time']},{$input['end_time']}"];
        }
        //门店
        if(!empty($input['store_name'])){

            $dis[] = ['b.title','like','%'.$input['store_name'].'%'];
        }
        //状态 1待核销 2核销 3过期 4被作废
        if(!empty($input['status'])){

            $dis[] = ['a.status','=',$input['status']];
        }
        //领取用户
        if(!empty($input['nickName'])){

            $where[] = ['nickName','like','%'.$input['nickName'].'%'];

            $user_id = User::where($where)->column('id');

            $dis[] = ['a.user_id','in',$user_id];
        }

        $map = [];

        if(!empty($input['name'])){

            $map[] = ['a.title','like','%'.$input['name'].'%'];

            $map[] = ['b.title','like','%'.$input['name'].'%'];
        }

        if(!empty($input['coupon_id'])){

            $dis[] = ['a.coupon_id','=',$input['coupon_id']];
        }

        if($this->_user['is_admin']==0){

            if($input['use_scene']==1){

                $dis[] = ['a.hx_admin_id','=',$this->_user['admin_id']];
            }else{

                $dis[] = ['a.admin_id','=',$this->_user['admin_id']];
            }
        }

        $coupon_coupon_model= new Coupon();

        if(isset($input['admin_id'])){

            $coupon_id = $coupon_coupon_model->where(['admin_id'=>$input['admin_id']])->column('id');

            $dis[] = ['a.coupon_id','in',$coupon_id];
        }

        $data = $coupon_model->alias('a')
                ->join('massage_store_list b','a.hx_store_id = b.id','left')
                ->where($dis)
                ->where(function ($query) use ($map){
                    $query->whereOr($map);
                })
                ->field('a.*,b.title as hx_store_name')
                ->group('a.id')
                ->order('a.id desc')
                ->paginate($input['limit'])
                ->toArray();

        $admin_model = new \app\massage\model\Admin();

        $city_model  = new City();

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $coupon_admin_id = $coupon_coupon_model->where(['id'=>$v['coupon_id']])->value('admin_id');

                if(!empty($v['admin_id'])){

                    $v['admin_name'] = $admin_model->where(['id'=>$coupon_admin_id])->value('agent_name');

                    $city_id = $admin_model->where(['id'=>$coupon_admin_id])->value('city_id');

                    $v['admin_city'] = $city_model->where(['id'=>$city_id])->value('title');
                }else{

                    $v['admin_name'] = '平台';
                }

                if(!empty($v['hx_user_id'])){

                    $v['hx_user_name'] = User::where(['id'=>$v['hx_user_id']])->value('nickName');
                }

                $v['user_info'] = User::where(['id'=>$v['user_id']])->field('nickName,avatarUrl')->find();

                if($v['status']==4){

                    $v['cancel_name'] = \app\massage\model\Admin::where(['id'=>$v['hx_admin_id']])->value('agent_name');
                }

                $v['coupon_status'] = $coupon_coupon_model->where(['id'=>$v['coupon_id']])->value('status');
            }
        }

        $data['get_num'] = $coupon_model->alias('a')
            ->join('massage_store_list b','a.hx_store_id = b.id','left')
            ->where($dis)
            ->where(function ($query) use ($map){
                $query->whereOr($map);
            })
            ->group('a.id')
            ->sum('num');

        $dis[] = ['a.status','=',2];

        $data['hx_num'] = $coupon_model->alias('a')
                        ->join('massage_store_list b','a.hx_store_id = b.id','left')
                        ->where($dis)
                        ->where(function ($query) use ($map){
                            $query->whereOr($map);
                        })
                        ->group('a.id')
                        ->count();

        $data['discount_cash'] = $coupon_model->alias('a')
                                ->join('massage_store_list b','a.hx_store_id = b.id','left')
                                ->where($dis)
                                ->where(function ($query) use ($map){
                                    $query->whereOr($map);
                                })
                                ->group('a.id')
                                ->sum('discount');

        $data['discount_cash'] = round($data['discount_cash'],2);

        return $this->success($data);
    }


    /**
     * @author chenniang
     * @DataTime: 2024-10-24 11:26
     * @功能说明:卡券作废
     */
    public function couponCancel(){

        $input = $this->_input;

        $coupon_model = new CouponRecord();

        if($input['num']<1){

            $this->errorMsg('作废数量不能小于1');
        }

        $res = $coupon_model->couponCancel($input['id'],$input['num'],$this->_user['id']);

        if(!empty($res['code'])){

            $this->errorMsg($res['msg']);

        }
        return $this->success($res);
    }






}
