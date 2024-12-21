<?php

namespace app\massage\model;

use AlibabaCloud\SDK\Dyplsapi\V20170525\Models\CreatePickUpWaybillPreQueryResponseBody\data\cpTimeSelectList;
use app\adapay\model\AccountsRecord;
use app\adapay\model\ErrLog;
use app\adapay\model\Member;
use app\BaseModel;
use app\coachbroker\model\BrokerLevel;
use app\coachbroker\model\CoachBroker;
use app\fdd\model\FddConfig;
use app\fxq\model\FxqIdCheck;
use longbingcore\heepay\HeePay;
use longbingcore\permissions\AdminMenu;
use longbingcore\wxcore\Adapay;
use longbingcore\wxcore\PayModel;
use longbingcore\wxcore\WxPay;
use longbingcore\wxcore\WxSetting;
use think\facade\Db;
use think\session\Store;

class Coach extends BaseModel
{
    //定义表名
    protected $name = 'massage_service_coach_list';


    protected $append = [

        'comment_num',

        'collect_num',

        'admin_name',

        'merchant_name'
    ];


    /**
     * @param $value
     * @param $data
     * @功能说明:真实姓名
     * @author chenniang
     * @DataTime: 2024-06-12 11:06
     */
    public function getTrueUserNameAttr($value,$data){

        if(isset($value)){

            if(!empty($value)){

                return $value;

            }elseif (!empty($data['coach_name'])){

                return $data['coach_name'];
            }
        }
    }


    /**
     * @param $value
     * @param $data
     * @功能说明:判断代理商是否有发展技师的权限
     * @author chenniang
     * @DataTime: 2024-06-13 15:07
     */
    public function getAdminIdAttr($value,$data){

        if(!empty($value)){

            $admin_model = new Admin();

            $admin = $admin_model->where(['id'=>$value,'status'=>1,'agent_coach_auth'=>1])->count();

            return $admin>0?$value:0;

        }else{

            return 0;
        }
    }


    /**
     * @param $value
     * @param $data
     * @功能说明:代理商名字
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-26 14:24
     */
    public function getAdminNameAttr($value,$data){

        if(!empty($data['admin_id'])){

            $admin_model = new Admin();

            $admin = $admin_model->where(['id'=>$data['admin_id'],'status'=>1,'agent_coach_auth'=>1])->value('agent_name');

            return $admin;
        }
    }


    /**
     * @param $value
     * @param $data
     * @功能说明:代理商商户名字名字
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-26 14:24
     */
    public function getMerchantNameAttr($value,$data){

        if(!empty($data['admin_id'])){

            $admin_model = new Admin();

            $name = $admin_model->where(['id'=>$data['admin_id'],'status'=>1,'agent_coach_auth'=>1])->value('merchant_name');

            return !empty($name)?$name:'';
        }
    }

    /**
     * @param $value
     * @param $data
     * @功能说明:电话加密
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-26 14:24
     */
    public function getMobileAttr($value,$data){

        if(!empty($value)&&isset($data['uniacid'])){

            if(numberEncryption($data['uniacid'])==1){

                return  substr_replace($value, "****", 2,4);
            }
        }

        return $value;
    }
    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-16 00:09
     * @功能说明:关联的门店必须和关联的代理商相同
     */
    public function getStoreIdAttr($value, $data){

        if(!empty($value)&&(!empty($data['id'])||!empty($data['coach_id']))){

            $id = !empty($data['id'])?$data['id']:$data['coach_id'];

            $store_model = new \app\store\model\StoreList();

            $coach_id = $store_model->getStoreCoachId($value);

            if(in_array($id,$coach_id)){

                return $value;
            }else{

                return 0;
            }
        }
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-22 14:33
     * @功能说明:保留两位
     */
    public function getServicePriceAttr($value, $data)
    {

        if (isset($value)) {

            return round($value, 2);
        }

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-22 14:33
     * @功能说明:保留两位
     */
    public function getCarPriceAttr($value, $data)
    {

        if (isset($value)) {

            return round($value, 2);
        }
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-10 13:45
     * @功能说明:初始化一下真实的订单数量
     */
    public function getTotalOrderNumAttr($value, $data){

        if(isset($value)){

            if (!empty($data['id'])&&$value==-1) {

                $comm_model = new Order();

                $value = $comm_model->where(['coach_id' => $data['id'], 'pay_type' => 7])->count();

                $this->dataUpdate(['id'=>$data['id']],['total_order_num'=>$value]);
            }
            return $value;
        }
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-07 22:47
     * @功能说明:评论数量
     */
    public function getCommentNumAttr($value, $data){

        if (!empty($data['id'])) {

            $comm_model = new Comment();

            $num = $comm_model->where(['coach_id' => $data['id'], 'status' => 1])->count();

            return $num + ($data['virtual_comment'] ?? 0);
        }
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-07 22:47
     * @功能说明:收藏数量
     */
    public function getCollectNumAttr($value, $data){

        if (!empty($data['id'])) {

            $comm_model = new CoachCollect();

            $num = $comm_model->where(['coach_id' => $data['id']])->count();

            return $num + ($data['virtual_collect'] ?? 0);
        }
    }

    /**
     * @param $value
     * @param $data
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-22 16:47
     */
    public function getIdCardAttr($value, $data){

        if (!empty($value)) {

            return explode(',', $value);
        }
    }


    /**
     * @param $value
     * @param $data
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-22 16:47
     */
    public function getLicenseAttr($value, $data)
    {

        if (!empty($value)) {

            return explode(',', $value);
        }

    }


    /**
     * @param $value
     * @param $data
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-22 16:47
     */
    public function getSelfImgAttr($value, $data)
    {

        if (!empty($value)) {

            return explode(',', $value);
        }

    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-15 14:37
     * @功能说明:后台列表
     */
    public function adminDataList($dis, $mapor, $page = 10)
    {

        $data = $this->alias('a')
            ->join('shequshop_school_user_list b', 'a.user_id = b.id')
            ->where($dis)
            ->where(function ($query) use ($mapor) {
                $query->whereOr($mapor);
            })
            ->field('a.*,b.nickName,b.avatarUrl')
            ->group('a.id')
            ->order('a.id desc')
            ->paginate($page)
            ->toArray();

        return $data;

    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-10-21 15:21
     * @功能说明:保留两位小数
     */
    public function getDistanceAttr($value)
    {

        if (isset($value)) {

            if ($value > 1000) {

                $value = ceil($value) / 1000;

                $value = round($value, 2);

                $value = $value . 'km';
            } else {

//                $value = round($value, 2);
//
//                $value = $value . 'm';

                $value = '<1km';
            }

            return $value;

        }

    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:04
     * @功能说明:添加
     */
    public function dataAdd($data)
    {

        $data['create_time'] = time();

        $res = $this->insert($data);

        return $res;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:05
     * @功能说明:编辑
     */
    public function dataUpdate($dis, $data)
    {

        if (isset($data['id']) && $data['id'] == 0) {

            unset($data['id']);
        }

        if(isset($data['lat'])){

            $data['address_update_time'] = time();
        }

        $res = $this->where($dis)->update($data);

        return $res;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:06
     * @功能说明:列表
     */
    public function dataList($dis, $page = 10, $mapor = [],$field='*')
    {

        $data = $this->where($dis)->where(function ($query) use ($mapor) {
            $query->whereOr($mapor);
        })->field($field)->order('id desc')->paginate($page)->toArray();

        return $data;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:43
     * @功能说明:
     */
    public function dataInfo($dis, $file = '*')
    {

        $data = $this->where($dis)->field($file)->find();

        return !empty($data) ? $data->toArray() : [];

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-04-06 11:54
     * @功能说明:获取团长的openid
     */
    public function capOpenid($id, $type = 2)
    {

        if ($type == 1) {

            $id = $this->alias('a')
                ->join('massage_service_user_list b', 'a.user_id = b.id')
                ->where(['a.id' => $id])
                ->value('b.wechat_openid');
        } else {

            $id = $this->alias('a')
                ->join('massage_service_user_list b', 'a.user_id = b.id')
                ->where(['a.id' => $id])
                ->value('b.web_openid');
        }


        return $id;

    }

    /**
     * @Desc: 排序处理
     * @param $uniacid
     * @param $sort
     * @param $free_fare_top_type
     * @return string
     * @Auther: shurong
     * @Time: 2024/7/22 15:41
     */
    protected function sortHandle($uniacid, $sort, $free_fare_top_type, $type = 1)
    {
        if ($type == 1) {

            switch ($sort) {

                case 1:
                    $sort_msg = '';
                    break;
                case 2:
                    $sort_msg  = 'distance asc,';
                    break;
                case 3:
                    $sort_msg = 'a.create_time desc,';
                    break;
                case 4:
                    $sort_msg = 'order_num desc,';
                    break;
                case 5:
                    $sort_msg = 'a.star desc,';
                    break;
                case 6:
                    $coach_format = getConfigSetting($uniacid, 'coach_format');

                    if (in_array($coach_format, [1, 3]) && $free_fare_top_type == 1) {

                        $sort_msg = '';
                    } elseif (in_array($coach_format, [1, 3]) && $free_fare_top_type != 1) {

                        $sort_msg = 'distance asc,a.id desc,';
                    } elseif (!in_array($coach_format, [1, 3])) {

                        $sort_msg = 'a.is_work desc,a.index_top desc,distance asc,a.id desc,';
                    }
                    break;
                default:
                    $sort_msg = '';
            }
        } else {

            switch ($sort) {

                case 1:
                    $sort_msg = '';
                    break;
                case 2:
                    $sort_msg = 'distance asc,';
                    break;
                case 3:
                    $sort_msg = 'a.create_time desc,';
                    break;
                case 4:
                    $sort_msg = 'order_num desc,';
                    break;
                case 5:
                    $sort_msg = 'a.star desc,';
                    break;
                case 6:
                    $coach_format = getConfigSetting($uniacid, 'coach_format');

                    if (in_array($coach_format, [1, 3])) {

                        $sort_msg = 'distance asc,';
                    } elseif (!in_array($coach_format, [1, 3])) {

                        $sort_msg = '';
                    }
                    break;
                default:
                    $sort_msg = '';
            }
        }

        return $sort_msg;
    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-07 10:21
     * @功能说明:服务技师列表
     */
    public function serviceCoachList($dis,$alh,$page=10,$credit_auth=false,$uniacid=666,$city_id=0,$free_fare=0,$sql_type=1,$sort=0)
    {

        $credit_config_model = new CreditConfig();

        if($credit_auth==true){

            $credit_config = $credit_config_model->dataInfo(['uniacid'=>$uniacid]);

            $credit_auth = $credit_config['status']==1?true:false;
        }

        $alh_data = str_replace('distance','distance_data',$alh);

        $where = [];
        //只查免费出行的
        if($free_fare==1){

            $dis[] = ['a.free_fare_distance','>',0];

            $dis[] = ['a.free_fare_bear','>',0];

            $where = str_replace('as distance','<= a.free_fare_distance*1000',$alh);
        }
        //开启技师信用分 将按信用分排序
        if($credit_auth==true){

            $credit_record_model = new CreditRecord();
            //初始化排序值
            $credit_record_model->updateCoachValue($uniacid,$alh_data,$credit_config,$city_id);

            $init_value = $credit_config['init_value'];

            $new_protect_value = $credit_config['new_protect_value'];

            $new_protect_day = time()-$credit_config['new_protect_day']*86400;

            if($sql_type==1){

                $data = $this->alias('a')
                    ->where($dis)
                    ->where($where)
                    ->field([$alh_data,'a.personality_icon,a.start_time,a.free_fare_bear,a.industry_type,a.end_time,a.show_salenum,a.coach_icon,a.free_fare_distance,a.credit_value,a.recommend,a.recommend_icon,a.city_id,a.store_id,a.admin_id,a.id,a.work_img,a.coach_name,a.self_img,(a.order_num+a.total_order_num) as order_num ,a.is_work,a.index_top,a.user_id,a.text,a.work_time,a.star', $alh,"round(if(a.sh_time>$new_protect_day,a.credit_value+$new_protect_value,a.credit_value+$init_value),2) as credit_values",'a.height,a.weight,a.constellation,a.sex,a.birthday,a.station_icon,a.virtual_collect,a.virtual_comment'])
                    ->group('a.id')
                    ->order('a.credit_top,credit_values desc,distance asc,a.id desc')
                    ->paginate($page)
                    ->toArray();
            }else{

                $data = $this->alias('a')
                    ->join('massage_service_service_coach b', 'a.id = b.coach_id', 'left')
                    ->join('massage_service_service_list c', 'b.ser_id = c.id', 'left')
                    ->where($dis)
                    ->where($where)
                    ->field([$alh_data,'a.personality_icon,a.start_time,a.free_fare_bear,a.industry_type,a.end_time,a.show_salenum,a.coach_icon,a.free_fare_distance,a.credit_value,a.recommend,a.recommend_icon,a.city_id,a.store_id,a.admin_id,a.id,a.work_img,a.coach_name,a.self_img,(a.order_num+a.total_order_num) as order_num ,a.is_work,a.index_top,a.user_id,a.text,a.work_time,a.star', $alh,"round(if(a.sh_time>$new_protect_day,a.credit_value+$new_protect_value,a.credit_value+$init_value),2) as credit_values",'a.height,a.weight,a.constellation,a.sex,a.birthday,a.station_icon,a.virtual_collect,a.virtual_comment'])
                    ->group('a.id')
                    ->order('a.credit_top,credit_values desc,distance asc,a.id desc')
                    ->paginate($page)
                    ->toArray();
            }
        }else{

            $free_fare_top_type = getConfigSetting($uniacid,'free_fare_top_type');

            $sort_msg = $this->sortHandle($uniacid, $sort, $free_fare_top_type);

            if($sql_type==1){

                if($free_fare_top_type==1) {

                    $data = $this->alias('a')
                        ->where($dis)
                        ->where($where)
                        ->field([$alh_data, 'a.personality_icon,a.start_time,a.free_fare_bear,a.industry_type,a.end_time,a.show_salenum,a.coach_icon,a.free_fare_distance,a.free_fare_distance,a.recommend,a.recommend_icon,a.city_id,a.store_id,a.admin_id,a.id,a.work_img,a.coach_name,a.self_img,(a.order_num+a.total_order_num) as order_num ,a.is_work,a.index_top,a.user_id,a.text,a.work_time,a.star,a.height,a.weight,a.constellation,a.sex,a.birthday,a.station_icon,a.virtual_collect,a.virtual_comment', $alh])
                        ->group('a.id')
                        ->order($sort_msg . 'distance asc,a.id desc')
                        ->paginate($page)
                        ->toArray();

                }else{

                    $free_fare_sql = str_replace('as distance','<= a.free_fare_distance*1000 AND a.free_fare_bear>0,1,0) as is_free_fare',$alh);

                    $free_fare_sql = 'if('.$free_fare_sql;

                    $data = $this->alias('a')
                        ->where($dis)
                        ->where($where)
                        ->field([$alh_data, 'a.personality_icon,a.start_time,a.free_fare_bear,a.industry_type,a.end_time,a.show_salenum,a.coach_icon,a.free_fare_distance,a.free_fare_distance,a.recommend,a.recommend_icon,a.city_id,a.store_id,a.admin_id,a.id,a.work_img,a.coach_name,a.self_img,(a.order_num+a.total_order_num) as order_num ,a.is_work,a.index_top,a.user_id,a.text,a.work_time,a.star,a.height,a.weight,a.constellation,a.sex,a.birthday,a.station_icon,a.virtual_collect,a.virtual_comment', $alh,$free_fare_sql])
                        ->group('a.id')
                        ->order($sort_msg . 'is_free_fare desc,distance asc,a.id desc')
                        ->paginate($page)
                        ->toArray();
                }

            }else{

                if($free_fare_top_type==1) {

                    $data = $this->alias('a')
                        ->join('massage_service_service_coach b', 'a.id = b.coach_id', 'left')
                        ->join('massage_service_service_list c', 'b.ser_id = c.id', 'left')
                        ->where($dis)
                        ->where($where)
                        ->field([$alh_data, 'a.personality_icon,a.start_time,a.free_fare_bear,a.industry_type,a.end_time,a.show_salenum,a.coach_icon,a.free_fare_distance,a.free_fare_distance,a.recommend,a.recommend_icon,a.city_id,a.store_id,a.admin_id,a.id,a.work_img,a.coach_name,a.self_img,(a.order_num+a.total_order_num) as order_num ,a.is_work,a.index_top,a.user_id,a.text,a.work_time,a.star,a.height,a.weight,a.constellation,a.sex,a.birthday,a.station_icon,a.virtual_collect,a.virtual_comment', $alh])
                        ->group('a.id')
                        ->order($sort_msg . 'distance asc,a.id desc')
                        ->paginate($page)
                        ->toArray();
                }else{

                    $free_fare_sql = str_replace('as distance','<= a.free_fare_distance*1000 AND a.free_fare_bear>0,1,0) as is_free_fare',$alh);

                    $free_fare_sql = 'if('.$free_fare_sql;

                    $data = $this->alias('a')
                        ->join('massage_service_service_coach b', 'a.id = b.coach_id', 'left')
                        ->join('massage_service_service_list c', 'b.ser_id = c.id', 'left')
                        ->where($dis)
                        ->where($where)
                        ->field([$alh_data, 'a.personality_icon,a.start_time,a.free_fare_bear,a.industry_type,a.end_time,a.show_salenum,a.coach_icon,a.free_fare_distance,a.free_fare_distance,a.recommend,a.recommend_icon,a.city_id,a.store_id,a.admin_id,a.id,a.work_img,a.coach_name,a.self_img,(a.order_num+a.total_order_num) as order_num ,a.is_work,a.index_top,a.user_id,a.text,a.work_time,a.star,a.height,a.weight,a.constellation,a.sex,a.birthday,a.station_icon,a.virtual_collect,a.virtual_comment', $alh,$free_fare_sql])
                        ->group('a.id')
                        ->order($sort_msg . 'is_free_fare desc,distance asc,a.id desc')
                        ->paginate($page)
                        ->toArray();
                }
            }
        }

        return $data;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-07 10:21
     * @功能说明:服务技师列表
     */
    public function typeServiceCoachList($dis, $alh, $page = 10,$credit_auth=false,$uniacid=666,$city_id=0,$free_fare=0,$sql_type=1,$sort=0)
    {
        $credit_config_model = new CreditConfig();

        if($credit_auth==true){

            $credit_config = $credit_config_model->dataInfo(['uniacid'=>$uniacid]);

            $credit_auth = $credit_config['status']==1?true:false;
        }
        $alh_data = str_replace('distance','distance_data',$alh);

        $where = [];
        //只查免费出行的
        if($free_fare==1){

            $dis[] = ['a.free_fare_distance','>',0];

            $dis[] = ['a.free_fare_bear','>',0];

            $where = str_replace('as distance','<= a.free_fare_distance*1000',$alh);
        }
        //开启技师信用分 将按信用分排序
        if($credit_auth==true){

            $credit_record_model = new CreditRecord();
            //初始化排序值
            $credit_record_model->updateCoachValue($uniacid,$alh_data,$credit_config,$city_id);

            $init_value = $credit_config['init_value'];

            $new_protect_value = $credit_config['new_protect_value'];

            $new_protect_day = time()-$credit_config['new_protect_day']*86400;

            if($sql_type==1){

                $data = $this->alias('a')
                    ->where($dis)
                    ->where($where)
                    ->field([$alh_data,'a.personality_icon,a.start_time,a.free_fare_bear,a.industry_type,a.end_time,a.show_salenum,a.coach_icon,a.free_fare_distance,a.credit_value,a.recommend,a.recommend_icon,a.city_id,a.store_id,a.admin_id,a.id,a.work_img,a.coach_name,a.self_img,(a.order_num+a.total_order_num) as order_num ,a.is_work,a.index_top,a.user_id,a.text,a.work_time,a.star,a.height,a.weight,a.constellation,a.sex,a.birthday,a.station_icon', $alh,"round(if(a.sh_time>$new_protect_day,a.credit_value+$new_protect_value,a.credit_value+$init_value),2) as credit_values", 'a.virtual_collect,a.virtual_comment'])
                    ->group('a.id')
                    ->order('a.is_work desc,a.index_top desc,a.credit_top,credit_values desc,distance asc,a.id desc')
                    ->paginate($page)
                    ->toArray();

            }else{

                $data = $this->alias('a')
                    ->join('massage_service_service_coach b', 'a.id = b.coach_id', 'left')
                    ->join('massage_service_service_list c', 'b.ser_id = c.id', 'left')
                    ->where($dis)
                    ->where($where)
                    ->field([$alh_data,'a.personality_icon,a.start_time,a.free_fare_bear,a.industry_type,a.end_time,a.show_salenum,a.coach_icon,a.free_fare_distance,a.credit_value,a.recommend,a.recommend_icon,a.city_id,a.store_id,a.admin_id,a.id,a.work_img,a.coach_name,a.self_img,(a.order_num+a.total_order_num) as order_num ,a.is_work,a.index_top,a.user_id,a.text,a.work_time,a.star,a.height,a.weight,a.constellation,a.sex,a.birthday,a.station_icon', $alh,"round(if(a.sh_time>$new_protect_day,a.credit_value+$new_protect_value,a.credit_value+$init_value),2) as credit_values", 'a.virtual_collect,a.virtual_comment'])
                    ->group('a.id')
                    ->order('a.is_work desc,a.index_top desc,a.credit_top,credit_values desc,distance asc,a.id desc')
                    ->paginate($page)
                    ->toArray();
            }

        }else{

            $free_fare_top_type = getConfigSetting($uniacid,'free_fare_top_type');

            $sort_msg = $this->sortHandle($uniacid,$sort,$free_fare_top_type,2);

            if($sql_type==1){

                if($free_fare_top_type==1){

                    $data = $this->alias('a')
                        ->where($dis)
                        ->where($where)
                        ->field([$alh_data,'a.personality_icon,a.start_time,a.free_fare_bear,a.industry_type,a.end_time,a.show_salenum,a.coach_icon,a.free_fare_distance,a.recommend,a.recommend_icon,a.city_id,a.id,a.admin_id,a.store_id,a.work_img,a.coach_name,a.self_img,(a.order_num+a.total_order_num) as order_num ,a.is_work,a.index_top,a.user_id,a.text,a.work_time,a.star,a.height,a.weight,a.constellation,a.sex,a.birthday,a.station_icon,a.virtual_collect,a.virtual_comment', $alh])
                        ->group('a.id')
                        ->order($sort_msg . 'a.is_work desc,a.index_top desc,distance asc,a.id desc')
                        ->paginate($page)
                        ->toArray();
                }else{

                    $free_fare_sql = str_replace('as distance','<= a.free_fare_distance*1000 AND a.free_fare_bear>0,1,0) as is_free_fare ',$alh);

                    $free_fare_sql = 'if('.$free_fare_sql;

                    $data = $this->alias('a')
                        ->where($dis)
                        ->where($where)
                        ->field([$alh_data,'a.personality_icon,a.start_time,a.free_fare_bear,a.industry_type,a.end_time,a.show_salenum,a.coach_icon,a.free_fare_distance,a.recommend,a.recommend_icon,a.city_id,a.id,a.admin_id,a.store_id,a.work_img,a.coach_name,a.self_img,(a.order_num+a.total_order_num) as order_num ,a.is_work,a.index_top,a.user_id,a.text,a.work_time,a.star,a.height,a.weight,a.constellation,a.sex,a.birthday,a.station_icon,a.virtual_collect,a.virtual_comment', $alh,$free_fare_sql])
                        ->group('a.id')
                        ->order($sort_msg . 'a.is_work desc,a.index_top desc,is_free_fare desc,distance asc,a.id desc')
                        ->paginate($page)
                        ->toArray();

                }
            }else{

                if($free_fare_top_type==1){

                    $data = $this->alias('a')
                        ->join('massage_service_service_coach b', 'a.id = b.coach_id', 'left')
                        ->join('massage_service_service_list c', 'b.ser_id = c.id', 'left')
                        ->where($dis)
                        ->where($where)
                        ->field([$alh_data,'a.personality_icon,a.start_time,a.free_fare_bear,a.industry_type,a.end_time,a.show_salenum,a.coach_icon,a.free_fare_distance,a.recommend,a.recommend_icon,a.city_id,a.id,a.admin_id,a.store_id,a.work_img,a.coach_name,a.self_img,(a.order_num+a.total_order_num) as order_num ,a.is_work,a.index_top,a.user_id,a.text,a.work_time,a.star,a.height,a.weight,a.constellation,a.sex,a.birthday,a.station_icon,a.virtual_collect,a.virtual_comment', $alh])
                        ->group('a.id')
                        ->order($sort_msg . 'a.is_work desc,a.index_top desc,distance asc,a.id desc')
                        ->paginate($page)
                        ->toArray();
                }else{

                    $free_fare_sql = str_replace('as distance','<= a.free_fare_distance*1000 AND a.free_fare_bear>0,1,0) as is_free_fare',$alh);

                    $free_fare_sql = 'if('.$free_fare_sql;

                    $data = $this->alias('a')
                        ->join('massage_service_service_coach b', 'a.id = b.coach_id', 'left')
                        ->join('massage_service_service_list c', 'b.ser_id = c.id', 'left')
                        ->where($dis)
                        ->where($where)
                        ->field([$alh_data,'a.personality_icon,a.start_time,a.free_fare_bear,a.industry_type,a.end_time,a.show_salenum,a.coach_icon,a.free_fare_distance,a.recommend,a.recommend_icon,a.city_id,a.id,a.admin_id,a.store_id,a.work_img,a.coach_name,a.self_img,(a.order_num+a.total_order_num) as order_num ,a.is_work,a.index_top,a.user_id,a.text,a.work_time,a.star,a.height,a.weight,a.constellation,a.sex,a.birthday,a.station_icon,a.virtual_collect,a.virtual_comment', $alh,$free_fare_sql])
                        ->group('a.id')
                        ->order($sort_msg . 'a.is_work desc,a.index_top desc,is_free_fare desc,distance asc,a.id desc')
                        ->paginate($page)
                        ->toArray();

                }
            }
        }

        return $data;

    }
    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-17 13:54
     * @功能说明:地图技师列表
     */
    public function mapCoachList($dis,$alh,$distance=100000){

        $data = $this->where($dis)->field(['id,coach_name,work_img,lng,lat',$alh])->having("distance<$distance")->select()->toArray();

        return $data;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-01-31 10:15
     * @功能说明:推荐技师下拉框
     */
    public function coachRecommendSelect($dis,$alh){

        $dis[] = ['recommend','=',1];

        $data = $this->where($dis)
            ->field(['id as coach_id,coach_name,work_img,star,city_id,is_work,index_top,(order_num+total_order_num) as order_count', $alh])
            ->order('distance asc,id desc')
            ->limit(12)
            ->select()
            ->toArray();

        return $data;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-01-31 10:15
     * @功能说明:推荐技师下拉框(自动推荐)
     */
    public function aotuCoachRecommendSelect($dis,$alh){
        //初始化一下数据
       // $this->where(['total_order_num'=>-1])->field('id,total_order_num')->select()->toArray();

        $data = $this->where($dis)
            ->field(['id as coach_id,coach_name,work_img,star,city_id,(order_num+total_order_num) as order_count,is_work,index_top', $alh])
            ->order('order_count desc,id desc')
            ->limit(12)
            ->select()
            ->toArray();

        return $data;

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-07 22:41
     * @功能说明:教练收藏列表
     */
    public function coachCollectList($dis, $alh, $page = 10)
    {

        $data = $this->alias('a')
            ->join('massage_service_coach_collect b', 'a.id = b.coach_id')
            ->where($dis)
            ->field(['a.show_salenum,a.recommend,a.coach_icon,a.recommend_icon,a.id,a.work_img,a.coach_name,a.self_img,(a.order_num + a.total_order_num) as order_num ,a.is_work,a.index_top,a.user_id,a.text,a.work_time,a.star,a.height,a.weight,a.constellation,a.sex,a.birthday,a.industry_type,a.station_icon,a.personality_icon,a.virtual_collect,a.virtual_comment', $alh])
            ->order('distance asc,a.id desc')
            ->paginate($page)
            ->toArray();

        return $data;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-07 22:41
     * @功能说明:教练收藏列表
     */
    public function typeCoachCollectList($dis, $alh, $page = 10)
    {

        $data = $this->alias('a')
            ->join('massage_service_coach_collect b', 'a.id = b.coach_id')
            ->where($dis)
            ->field(['a.show_salenum,a.recommend,a.coach_icon,a.recommend_icon,a.id,a.work_img,a.coach_name,a.self_img,(a.order_num + a.total_order_num) as order_num,a.is_work,a.index_top,a.user_id,a.text,a.work_time,a.star,a.height,a.weight,a.constellation,a.sex,a.birthday,a.industry_type,a.station_icon,a.personality_icon,a.virtual_collect,a.virtual_comment', $alh])
            ->order('a.is_work desc,a.index_top desc,distance asc,a.id desc')
            ->paginate($page)
            ->toArray();

        return $data;
    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-07 22:41
     * @功能说明:教练收藏列表
     */
    public function coachCollectCount($user_id,$uniacid)
    {

        $shield_model = new ShieldList();
        //除开屏蔽技师的
        $coach_id = $shield_model->where(['user_id'=>$user_id])->where('type','in',[2,3])->column('coach_id');

        $dis[] = ['a.status','=',2];

        $dis[] = ['b.user_id','=',$user_id];

        $config_model = new ConfigSetting();

        $config = $config_model->dataInfo($uniacid);

        if(in_array($config['coach_format'],[1,3])){

            $dis[] = ['a.is_work','=',1];

        }

        $data = $this->alias('a')
            ->join('massage_service_coach_collect b', 'a.id = b.coach_id')
            ->where($dis)
            ->where('a.id','not in',$coach_id)
            ->group('a.id')
            ->count();

        return $data;
    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-08 09:58
     * @功能说明:获取技师等级
     */
    public function getCoachLevelBak($caoch_id, $uniacid)
    {

        $level_model = new CoachLevel();

        $order_model = new Order();

        $dis = [

            'coach_id' => $caoch_id,

            'pay_type' => 7
        ];

        $time_long = $order_model->where($dis)->sum('true_time_long');

        $level = $level_model->where(['uniacid' => $uniacid, 'status' => 1])->order('time_long desc,id desc')->select()->toArray();

        if (!empty($level)) {

            foreach ($level as $value) {

                if ($time_long <= $value['time_long']) {

                    $coach_level = $value;

                } elseif (empty($coach_level)) {

                    $coach_level = $value;
                }

            }

        }

        return !empty($coach_level) ? $coach_level : '';

    }






    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-08 09:58
     * @功能说明:获取技师等级
     */
    public function getCoachLevel($caoch_id, $uniacid,$type=1){

        $config_model = new Config();

        $level_model  = new CoachLevel();

        $config = $config_model->dataInfo(['uniacid'=>$uniacid]);

        $level_cycle = $config['level_cycle'];

        $is_current  = $config['is_current'];
        //时长(分钟)
        $time_long = $level_model->getMinTimeLong($caoch_id,$level_cycle,$is_current);
        //最低业绩
        $price     = $level_model->getMinPrice($caoch_id,$level_cycle,0,$is_current);
        //加钟订单
        $add_price = $level_model->getMinPrice($caoch_id,$level_cycle,1,$is_current);
        //积分
        $integral  = $level_model->getMinIntegral($caoch_id,$level_cycle,$is_current);
        //在线时长
        $online_time = $level_model->getCoachOnline($caoch_id,$level_cycle,$is_current);

        $level       = $level_model->where(['uniacid' => $uniacid, 'status' => 1])->order('time_long,id desc')->select()->toArray();

        $coach_level = [];

        $add_balance = $price>0?$add_price/$price*100:0;

        $int_integral = $integral;

        if (!empty($level)) {

            foreach ($level as $key=>$value) {

                $integral = $int_integral;
                //时长
                $level_time_long = $key>0?$level[$key-1]['time_long']:0;
                //在线时长兑换积分
                if($value['online_change_integral_status']==1){

                    $more_online_time = floor($online_time - $value['online_time']);

                    if($more_online_time>0){

                        $change_integral = $more_online_time*$value['online_change_integral'];

                        $integral+= $change_integral;
                    }
                }

                if($time_long>=$level_time_long&&$price>=$value['price']&&$add_balance>=$value['add_balance']&&$integral>=$value['integral']&&$online_time>=$value['online_time']){

                    $coach_level = $value;

                }elseif (empty($coach_level)) {
                    //都不符合给一个最低都等级
                    $coach_level = $value;
                }
            }
        }

        $coach_level = !empty($coach_level)?$coach_level : [];

        if($type==1){

            return $coach_level;

        }else{

            $arr = [
                //考核数据
                'check_data' => [

                    'coach_time_long'   => timeHour($time_long),

                    'coach_price'       => $price,

                    'coach_add_price'   => $add_price,

                    'coach_integral'    => round($integral,2),

                    'online_time'       => timeHour($online_time*60),

                    'coach_add_balance' => round($add_balance,2),
                ],

                'coach_level' => $coach_level,

                'level_cycle' => $level_cycle,

                'is_current'  => $is_current
            ];

            return $arr;
        }
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-11-17 11:09
     * @功能说明:获取本期业绩
     */
    public function getCurrentAchievement($caoch_id,$uniacid){

        $config_model = new Config();

        $level_model  = new CoachLevel();

        $config = $config_model->dataInfo(['uniacid'=>$uniacid]);

        $level_cycle = $config['level_cycle'];
        //时长(分钟)
        $data['coach_time_long'] = $level_model->getMinTimeLong($caoch_id,$level_cycle,1);
        //最低业绩
        $data['coach_price']     = $level_model->getMinPrice($caoch_id,$level_cycle,0,1);
        //加钟订单
        $data['coach_add_price'] = $level_model->getMinPrice($caoch_id,$level_cycle,1,1);
        //积分
        $data['coach_integral']  = $level_model->getMinIntegral($caoch_id,$level_cycle,1);
        //在线时长
        $data['online_time']     = $level_model->getCoachOnline($caoch_id,$level_cycle,1);

        $data['coach_add_balance'] = $data['coach_price']>0?round($data['coach_add_price']/$data['coach_price']*100,2):0;;

        return $data;

    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-04-01 09:51
     * @功能说明:公众号楼长端订单退款通知
     */
    public function refundSendMsgWeb($order)
    {

        $cap_model = new Coach();

        $cap_id  = $order['coach_id'];

        $uniacid = $order['uniacid'];

        $config_model = new SendMsgConfig();

        $config_model->initData($uniacid);

        $x_config = $config_model->dataInfo(['uniacid'=>$uniacid]);

        if (empty($x_config['gzh_appid']) || empty($x_config['cancel_tmp_id'])) {

            return false;
        }
        //获取楼长openid
        $openid = $cap_model->capOpenid($cap_id, 2);

        $store_name = $cap_model->where(['id' => $cap_id])->value('coach_name');

        $wx_setting = new WxSetting($uniacid);

        $access_token = $wx_setting->getGzhToken();

        $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token={$access_token}";

        $order_model = new Order();

        $start_time = $order_model->where(['id' => $order['order_id']])->value('start_time');

        $key = explode('&',$x_config['cancel_tmp_id']);

        for ($i=1;$i<4;$i++){

            $arr[$i] = !empty($key[$i])?$key[$i]:'keyword'.$i;
        }

        $data = [
            //用户小程序openid
            'touser'=> $openid,
            //公众号appid
            'appid' => $x_config['gzh_appid'],

            "url"   => 'https://' . $_SERVER['HTTP_HOST'] . '/h5/#/technician/pages/order/detail?id=' . $order['order_id'],
            //公众号模版id
            'template_id' => $key[0],

            'data' => array(

                'first' => array(

                    'value' => $store_name . '您有一笔订单正在申请退款',

                    'color' => '#93c47d',
                ),

                $arr[1] => array(

                    'value' => implode(',', array_column($order['order_goods'], 'goods_name')),

                    'color' => '#93c47d',
                ),
                //预约时间
                $arr[2] => array(
                    //内容
                    'value' => date('Y-m-d H:i', $start_time),

                    'color' => '#0000ff',
                ),
                //取消原因
                $arr[3] => array(
                    //内容
                    'value' => $order['text'],

                    'color' => '#0000ff',
                ),

            )

        ];

        $data = json_encode($data);

        $tmp = [

            'url' => $url,

            'data' => $data,
        ];
        $rest = lbCurlPost($tmp['url'], $tmp['data']);

        $rest = json_decode($rest, true);

        return $rest;

    }


    /**
     * @param $order
     * @功能说明:发送模版消息
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-06-01 15:15
     */
    public function refundSendMsg($order)
    {

        $coach_user_id = $this->where(['id' => $order['coach_id']])->value('user_id');

        $user_model = new User();

        $user_info = $user_model->dataInfo(['id' => $coach_user_id]);

        if (empty($user_info)) {

            return false;
        }
        //type 1小程序 2公众号
        $type = $user_info['last_login_type'] == 0 && !empty($user_info['wechat_openid']) ? 1 : 2;

        $wechat_tmpl = getConfigSetting($order['uniacid'],'wechat_tmpl');

//        if ($type == 1) {
//
//            $res = $this->refundSendMsgWechat($order);
//
//            if($wechat_tmpl==1){
//
//                $config_model = new SendMsgConfig();
//                //通知管理员
//                $config_model->wechatRefundMsgAdmin($order);
//                //通知平台
//                $config_model->wechatRefundMsgCompany($order);
//
//            }
//
//        } else {

        $res = $this->refundSendMsgWeb($order);

        $config_model = new SendMsgConfig();
        //通知管理员
        $config_model->webRefundMsgAdmin($order);

        if($wechat_tmpl==1){
            //通知平台
            $config_model->webRefundMsgCompany($order);

        }
//        }

        $this->sendShortMsg($order, 2);

        return true;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-04-01 09:51
     * @功能说明:公众号楼长端订单退款通知
     */
    public function refundSendMsgWechat($order)
    {

        $cap_model = new Coach();

        $cap_id = $order['coach_id'];

        $uniacid = $order['uniacid'];

        $config_model = new SendMsgConfig();

        $config_model->initData($uniacid);

        $x_config = $config_model->dataInfo(['uniacid'=>$uniacid]);

        if (empty($x_config['gzh_appid']) || empty($x_config['order_tmp_id'])) {

            return false;
        }

        $config = longbingGetAppConfig($uniacid);
        //获取楼长openid
        $openid = $cap_model->capOpenid($cap_id, 1);

        $store_name = $cap_model->where(['id' => $cap_id])->value('coach_name');

        $access_token = longbingGetAccessToken($uniacid);

        $page = "master/pages/order/list";
        //post地址
        $url = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/uniform_send?access_token={$access_token}";

        $order_model = new Order();

        $start_time = $order_model->where(['id' => $order['order_id']])->value('start_time');

        $data = [
            //用户小程序openid
            'touser' => $openid,

            'mp_template_msg' => [
                //公众号appid
                'appid' => $x_config['gzh_appid'],

                "url" => "http://weixin.qq.com/download",
                //公众号模版id
                'template_id' => $x_config['cancel_tmp_id'],

                'miniprogram' => [
                    //小程序appid
                    'appid' => $config['appid'],
                    //跳转小程序地址
                    'page' => $page,
                ],
                'data' => array(

                    'first' => array(

                        'value' => $store_name . '您有一笔订单正在申请退款',

                        'color' => '#93c47d',
                    ),

                    'keyword1' => array(

                        'value' => implode(',', array_column($order['order_goods'], 'goods_name')),

                        'color' => '#93c47d',
                    ),
                    //预约时间
                    'keyword2' => array(
                        //内容
                        'value' => date('Y-m-d H:i', $start_time),

                        'color' => '#0000ff',
                    ),
                    //取消原因
                    'keyword3' => array(
                        //内容
                        'value' => $order['text'],

                        'color' => '#0000ff',
                    ),

                )
            ],
        ];

        $data = json_encode($data);

        $tmp = [

            'url' => $url,

            'data' => $data,
        ];
        $rest = lbCurlPost($tmp['url'], $tmp['data']);

        $rest = json_decode($rest, true);

        return $rest;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-06-01 15:17
     * @功能说明:发送模版消息
     */
    public function paySendMsg($order)
    {

        $coach_user_id = $this->where(['id' => $order['coach_id']])->value('user_id');

        $user_model = new User();

        $user_info = $user_model->dataInfo(['id' => $coach_user_id]);

        if (!empty($user_info)) {

            $type = 2;

            if ($type == 1) {

                $res = $this->paySendMsgWechat($order);

            } else {

                $res = $this->paySendMsgWeb($order);
            }
        }
        //type 1小程序 2公众号
      //  $type = $user_info['last_login_type'] == 0 && !empty($user_info['wechat_openid']) ? 1 : 2;

        $send_config_model = new SendMsgConfig();
        //给代理商和平台发送模版消息
        $send_config_model->sendOrderTmplAdmin($order);
        //发送短信
        $this->sendShortMsg($order);

        return true;

    }


    /**
     * @param $order
     * @功能说明:发送短信
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-08-01 14:59
     */
    public function sendShortMsg($order, $type = 1)
    {

        $mobile = $this->where(['id' => $order['coach_id']])->value('mobile');

        $config_model = new ShortCodeConfig();

        $res = $config_model->sendSms($mobile, $order['uniacid'], $order['order_code'], $type);

        return $res;
    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-04-01 09:51
     * @功能说明:公众号楼长端订单支付通知
     */
    public function paySendMsgWechat($order)
    {

        $cap_model = new Coach();

        $cap_id = $order['coach_id'];

        $uniacid = $order['uniacid'];

        $config_model = new SendMsgConfig();

        $config_model->initData($uniacid);

        $x_config = $config_model->dataInfo(['uniacid'=>$uniacid]);

        $config = longbingGetAppConfig($uniacid);

        if (empty($x_config['gzh_appid']) || empty($x_config['order_tmp_id'])) {

            return false;
        }
        //获取楼长openid
        $openid = $cap_model->capOpenid($cap_id, 1);

        $store_name = $cap_model->where(['id' => $cap_id])->value('coach_name');

        $user_model = new User();

        $mobile = $user_model->where(['id' => $order['user_id']])->value('phone');

        $mobile = !empty($mobile)?$mobile:'-';

        $access_token = longbingGetAccessToken($uniacid);

        $virtual_config_model = new \app\virtual\model\Config();

        $mobile_auth = $virtual_config_model->getVirtualAuth($uniacid);

        $page = "master/pages/order/list";
        //post地址
        $url = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/uniform_send?access_token={$access_token}";

        $data = [
            //用户小程序openid
            'touser' => $openid,

            'mp_template_msg' => [
                //公众号appid
                'appid' => $x_config['gzh_appid'],

                "url" => "http://weixin.qq.com/download",
                //公众号模版id
                'template_id' => $x_config['order_tmp_id'],

                'miniprogram' => [
                    //小程序appid
                    'appid' => $config['appid'],
                    //跳转小程序地址
                    'page' => $page,
                ],

                'data' => array(

                    'first' => array(

                        'value' => $store_name . '您有一笔新的订单',

                        'color' => '#93c47d',
                    ),
                    //服务名称
                    'keyword1' => array(

                        'value' => implode(',', array_column($order['order_goods'], 'goods_name')),

                        'color' => '#93c47d',
                    ),
                    //下单人
                    'keyword2' => array(
                        //内容
                        'value' => $order['address_info']['user_name'],

                        'color' => '#0000ff',
                    ),
                    'keyword3' => array(
                        //内容
                        'value' => $mobile_auth==false?$order['address_info']['mobile']:'-',

                        'color' => '#0000ff',
                    ),
                    //客户电话
                    'keyword4' => array(
                        //内容
                        'value' => $order['order_code'],

                        'color' => '#0000ff',
                    ),
                    'keyword5' => array(
                        //内容
                        'value' => $order['address_info']['address'],

                        'color' => '#0000ff',
                    ),

                )

            ]

        ];

        $data = json_encode($data);

        $tmp = [

            'url' => $url,

            'data' => $data,
        ];
        $rest = lbCurlPost($tmp['url'], $tmp['data']);

        $rest = json_decode($rest, true);

        return $rest;

    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-04-01 09:51
     * @功能说明:公众号楼长端订单支付通知
     */
    public function paySendMsgWeb($order)
    {

        $cap_model = new Coach();

        $cap_id = $order['coach_id'];

        $uniacid = $order['uniacid'];

        $config_model = new SendMsgConfig();

        $config_model->initData($uniacid);

        $x_config = $config_model->dataInfo(['uniacid'=>$uniacid]);

        if (empty($x_config['gzh_appid']) || empty($x_config['order_tmp_id'])) {

            return false;
        }
        //获取楼长openid
        $openid = $cap_model->capOpenid($cap_id, 2);

        $store_name = $cap_model->where(['id' => $cap_id])->value('coach_name');

        $wx_setting = new WxSetting($uniacid);

        $access_token = $wx_setting->getGzhToken();

       // $user_model = new User();

      //  $mobile = $user_model->where(['id' => $order['user_id']])->value('phone');

       // $mobile = !empty($mobile)?$mobile:'-';

        $virtual_config_model = new \app\virtual\model\Config();

        $mobile_auth = $virtual_config_model->getVirtualAuth($uniacid);

        $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token={$access_token}";

        $key = explode('&',$x_config['order_tmp_id']);

        for ($i=1;$i<6;$i++){

            $arr[$i] = !empty($key[$i])?$key[$i]:'keyword'.$i;
        }

        $order['address_info']['address_info'] = !empty($order['address_info']['address_info'])?$order['address_info']['address_info']:$order['address_info']['address'];

        $data = [
            //用户小程序openid
            'touser' => $openid,
            //公众号appid
            'appid' => $x_config['gzh_appid'],

            "url" => 'https://' . $_SERVER['HTTP_HOST'] . '/h5/#/technician/pages/order/detail?id=' . $order['id'],
            //公众号模版id
            'template_id' => $key[0],

            'data' => array(

                'first' => array(

                    'value' => $store_name . '您有一笔新的订单',

                    'color' => '#93c47d',
                ),
                //服务名称
                $arr[1] => array(

                    'value' => mb_substr(implode(',', array_column($order['order_goods'], 'goods_name')),0,20),

                    'color' => '#93c47d',
                ),
                //下单人
                $arr[2]  => array(
                    //内容
                    'value' => $order['address_info']['user_name'],

                    'color' => '#0000ff',
                ),
                $arr[3] => array(
                    //内容
                    'value' => $mobile_auth==false?$order['address_info']['mobile']:'-',

                    'color' => '#0000ff',
                ),
                //客户电话
                $arr[4] => array(
                    //内容
                    'value' => $order['order_code'],

                    'color' => '#0000ff',
                ),
                $arr[5] => array(
                    //内容
                    'value' => mb_substr($order['address_info']['address_info'],0,20),

                    'color' => '#0000ff',
                ),
            )
        ];

        $data = json_encode($data);

        $tmp = [

            'url'  => $url,

            'data' => $data,
        ];

        $rest = lbCurlPost($tmp['url'], $tmp['data']);

        $rest = json_decode($rest, true);

        return $rest;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-08-23 23:57
     * @功能说明:获取正在进行中的技师
     */
    public function getWorkingCoach($uniacid,$time=0)
    {

        $dis[] = ['uniacid', '=', $uniacid];

        $dis[] = ['pay_type', 'in', [4, 5, 6]];

        $order = new Order();

        $refund_model = new RefundOrder();

        $refund_ing_order = $refund_model->where('status','in',[4,5])->where(['refund_end'=>1])->column('order_id');

        if(!empty($refund_ing_order)){

            $dis[] = ['id', 'not in', $refund_ing_order];
        }

        $data = $order->where($dis)->column('coach_id');

        return $data;
    }




    /**
     * @param $coach_id
     * @param $config
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-10-20 18:50
     */
    public function getCoachEarliestTime($coach_id,$config,$type=0,$time_long=0,$is_date=0){

        $key = $type.'getCoachEarliestTime'.$coach_id.$is_date;

        $data = getCache($key,$config['uniacid']);

        if(!empty($data)&&$type==0){

            return $data;
        }

        $coach_model = new Coach();

        $coach = $coach_model->dataInfo(['id' => $coach_id]);

        $i = 0;

        while ($i<$config['max_day']&&empty($data)){

            $time = $i*86400;

            $data = $this->getCoachEarliestTimeData($coach,$config,$time,$type,$time_long,$is_date);

            $i++;
        }

        if(!empty($data)){

            setCache($key,$data,10,$config['uniacid']);
        }

        return $data;
    }

    /**
     * @param $coach_id
     * @param $config
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-10-20 18:50
     */
    public function getCoachEarliestTimev3($coach,$config,$type=0,$time_long=0,$is_date=0){

        $coach_id = $coach['id'];

        $key = $type.'getCoachEarliestTime'.$coach_id.$is_date;

        $data = getCache($key,$config['uniacid']);

        if(!empty($data)&&$type==0){

            return $data;
        }

        $i = 0;

        while ($i<$config['max_day']&&empty($data)){

            $time = $i*86400;

            $data = $this->getCoachEarliestTimeData($coach,$config,$time,$type,$time_long,$is_date);

            $i++;
        }

        if(!empty($data)){

            setCache($key,$data,10,$config['uniacid']);
        }

        return $data;
    }


    /**
     * @param $coach_id
     * @param $config
     * @param int $time
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-10-20 18:50
     */
    public function getCoachEarliestTimeData($coach, $config,$time=0,$time_style=0,$time_long=0,$is_date=0)
    {

        $tt = time();

        $order_model = new Order();

        if (empty($coach['start_time'])) {

            return '';
        }

        $coach_id = $coach['id'];

        $end_time = strtotime($coach['end_time']);

        $start_time = strtotime($coach['start_time']);

        $is_eve = $end_time==$start_time||$end_time-$start_time==86400?1:0;
        //跨日
        if($end_time <=$start_time){
            //查看此时处于上一个周期还是这个周期
            //上一个周期
            if($tt<$end_time){

                $start_time -= 86400;

            }else{
                //当前周期
                $end_time += 86400;
            }
        }

        $max_day = strtotime(date('Y-m-d',time()))+$config['max_day']*86400;

        $start_time += $time;

        $end_time   += $time;

        $rest_arr = $this->getCoachRestTime($coach,$start_time,$end_time,$config);

        $i = 0;

        $time = $start_time;

        $where = [];

       // $where[] = ['coach_id', '=', $coach_id];

        $where[] = ['end_time', '>=', $time];

        $where[] = ['coach_id', '>', 0];

        $where[] = ['pay_type', 'not in', [-1,7]];

        $order_key = 'order_near_keyyghvggv';

        $order = getCache($order_key,666);

        if(empty($order)){

            $order = $order_model->where($where)->field('start_time,end_time,order_end_time,pay_type,coach_id')->order('start_time,end_time')->select()->toArray();

            setCache($order_key,$order,7,666);
        }

        $time_interval = $config['time_interval']>0?$config['time_interval']*60-1:0;

        while ($time < $end_time) {

            $time = $start_time + $config['time_unit'] * $i * 60;

            $max_time = $time + $config['time_unit']* 60-1;

            $max_time = $max_time>$time_long*60+$time?$max_time:$time_long*60+$time;

            $status = 1;

            if($time-$time_interval<=time()){

                $status = 0;
            }

            $time_text = $time_style==0?date('H:i', $time):$time;
            //加一个日期标签
            if($is_date==1){

                $date_arr = [

                    date('Y-m-d',time())=>'今',

                    date('Y-m-d',time()+86400)=>'明',

                    date('Y-m-d',time()+86400*2)=>'后',
                ];

                $date = !empty($date_arr[date('Y-m-d',$time)])?$date_arr[date('Y-m-d',$time)]:'';

                $time_text = $date.$time_text;
            }
            if(!empty($order)){

                foreach ($order as $value){

                    if(!empty($value['coach_id'])&&$value['coach_id']==$coach['id']){
                        //查询订单
                        $res = is_time_crossV2($time,$max_time,$value['start_time']-$time_interval,$value['end_time']+$time_interval);

                        if($res==false){

                            $status = 0;
                        }
                    }
                }
            }

            if(!empty($rest_arr)&&$status==1){

                $res = $order_model->checkCoachRestTime($rest_arr,$time,$max_time);

                if(!empty($res['code'])){

                    $status = 0;
                }

            }
            if(empty($is_eve)){

                $status = $time == $end_time ? 0 : $status;
            }

            $status = $time < $tt||$time>$end_time? 0 : $status;

            if($time>=$max_day){

                $status = 0;
            }

            if ($status == 1) {

                return $time_text;
            }

            $i++;
        }

        return '';

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-10-19 23:03
     * @功能说明:获取技师休息时间
     */
    public function getCoachRestTime($coach,$start_time,$end_time,$config){

        $where = [

            'start_time' => $coach['start_time'],

            'end_time'   => $coach['end_time'],

            'coach_id'   => $coach['id'],

            'max_day'    => $config['max_day'],

            'time_unit'  => $config['time_unit']
        ];

        $start_date =  date('Y-m-d', $start_time);

        $end_date   =  date('Y-m-d', $end_time);

        $list = Db::name('massage_service_coach_time')->where($where)->where('date','in',[$start_date,$end_date])->select();

        if(!empty($list)){

            foreach ($list as $value){

                $info = json_decode($value['info'], true);

                foreach ($info as $vs){

                    if($vs['status']==0&&$vs['is_click']==1){

                        $vs['time_str_end'] = $vs['time_str']+$config['time_unit']*60;

                        $arr[] = $vs;
                    }
                }
            }
        }
        return !empty($arr)?$arr:[];
    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-08-24 18:06
     * @功能说明:获取教练最早可预约时间
     */
    public function getCoachEarliestTimeV2($coach_id, $config)
    {


        $coach_model = new Coach();

        $coach = $coach_model->dataInfo(['id' => $coach_id]);

        if (empty($coach['start_time'])) {

            return '';
        }

        $min_time = strtotime($coach['start_time']) > time() ? strtotime($coach['start_time']) : time();

        $dis[] = ['coach_id', '=', $coach_id];

        $dis[] = ['pay_type', 'in', [2, 3, 4, 5, 6]];

        $dis[] = ['start_time', '<=', $min_time];

        $dis[] = ['end_time', '>=', $min_time];

        $order = new Order();

        $data = $order->dataInfo($dis);

        if (!empty($data)) {

            return date('H:i', $data['end_time']);
        }
        $now_time = strtotime(date('H', time()));
        //整点
        if ($config['time_unit'] == 60) {

            return date('H:i', $now_time + 3600);
        }
        //查看是在上半时还是下半时
        $y = time() - $now_time;
        //下半时
        if ($y > 1800) {

            return date('H:i', $now_time + 1800);

        } else {

            return date('H:i', $now_time + 3600);

        }

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-12-07 15:18
     * @功能说明:认证未认证的教练 通过电话号码
     */
    public function attestationCoach($user)
    {

        if (!empty($user['phone'])) {

            $dis = [

                'user_id' => 0,

                'mobile' => $user['phone']
            ];

            $this->dataUpdate($dis, ['user_id' => $user['id']]);

        }

        return true;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-04-01 09:51
     * @功能说明:技师修改结果通知
     */
    public function updateTmpWechat($cap_id, $uniacid, $type, $sh_text)
    {

        $cap_model = new Coach();

        $config_model = new SendMsgConfig();

        $config_model->initData($uniacid);

        $x_config = $config_model->dataInfo(['uniacid'=>$uniacid]);

        if (empty($x_config['gzh_appid']) || empty($x_config['coachupdate_tmp_id'])) {

            return false;
        }

        $config = longbingGetAppConfig($uniacid);
        //获取楼长openid
        $openid = $cap_model->capOpenid($cap_id, 1);

        $access_token = longbingGetAccessToken($uniacid);

        $page = "pages/mine?type=2";
        //post地址
        $url = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/uniform_send?access_token={$access_token}";

        $data = [
            //用户小程序openid
            'touser' => $openid,

            'mp_template_msg' => [
                //公众号appid
                'appid' => $x_config['gzh_appid'],

                "url" => "http://weixin.qq.com/download",
                //公众号模版id
                'template_id' => $x_config['coachupdate_tmp_id'],

                'miniprogram' => [
                    //小程序appid
                    'appid' => $config['appid'],
                    //跳转小程序地址
                    'page' => $page,
                ],

                'data' => array(

                    'first' => array(

                        'value' => '技师修改审核通知',

                        'color' => '#93c47d',
                    ),
                    //服务名称
                    'keyword1' => array(

                        'value' => '平台',

                        'color' => '#93c47d',
                    ),
                    'keyword2' => array(

                        'value' => $type == 2 ? '通过 ' . $sh_text : '未通过 ' . $sh_text,

                        'color' => '#93c47d',
                    ),
                    'keyword3' => array(

                        'value' => date('Y-m-d H:i', time()),

                        'color' => '#93c47d',
                    )

                )

            ]

        ];

        $data = json_encode($data);

        $tmp = [

            'url' => $url,

            'data' => $data,
        ];
        $rest = lbCurlPost($tmp['url'], $tmp['data']);

        $rest = json_decode($rest, true);

        return $rest;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-04-01 09:51
     * @功能说明:技师修改结果通知
     */
    public function updateTmpWeb($cap_id, $uniacid, $type, $sh_text)
    {

        $cap_model = new Coach();

        $config_model = new SendMsgConfig();

        $config_model->initData($uniacid);

        $x_config = $config_model->dataInfo(['uniacid'=>$uniacid]);

        if (empty($x_config['gzh_appid']) || empty($x_config['coachupdate_tmp_id'])) {

            return false;
        }
        //获取楼长openid
        $openid = $cap_model->capOpenid($cap_id, 2);

        $wx_setting = new WxSetting($uniacid);

        $access_token = $wx_setting->getGzhToken();

        $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token={$access_token}";

        $key = explode('&',$x_config['coachupdate_tmp_id']);

        for ($i=1;$i<4;$i++){

            $arr[$i] = !empty($key[$i])?$key[$i]:'keyword'.$i;
        }

        $data = [
            //用户小程序openid
            'touser' => $openid,
            //公众号appid
            'appid' => $x_config['gzh_appid'],

//            "url"   => "http://weixin.qq.com/download",
            "url" => 'https://' . $_SERVER['HTTP_HOST'] . '/h5/#/pages/mine?type=2',
            //公众号模版id
            'template_id' => $key[0],

            'data' => array(

                'first' => array(

                    'value' => '技师修改审核通知',

                    'color' => '#93c47d',
                ),
                //服务名称
                $arr[1] => array(

                    'value' => '平台',

                    'color' => '#93c47d',
                ),
                $arr[2] => array(

                    'value' => $type == 2 ? '通过 ' . $sh_text : '未通过 ' . $sh_text,

                    'color' => '#93c47d',
                ),
                $arr[3] => array(

                    'value' => date('Y-m-d H:i', time()),

                    'color' => '#93c47d',
                )


            )

        ];

        $data = json_encode($data);

        $tmp = [

            'url' => $url,

            'data' => $data,
        ];
        $rest = lbCurlPost($tmp['url'], $tmp['data']);

        $rest = json_decode($rest, true);

        return $rest;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-06-01 15:17
     * @功能说明:发送模版消息
     */
    public function updateSendMsg($coach_id, $status, $sh_text)
    {

        $coach_user_id = $this->where(['id' => $coach_id])->value('user_id');

        $user_model = new User();

        $user_info = $user_model->dataInfo(['id' => $coach_user_id]);

        if (empty($user_info)) {

            return false;
        }
        //type 1小程序 2公众号
        $type = $user_info['last_login_type'] == 0 && !empty($user_info['wechat_openid']) ? 1 : 2;

        $type = 2;

        if ($type == 1) {

            $res = $this->updateTmpWechat($coach_id, $user_info['uniacid'], $status, $sh_text);

        } else {

            $res = $this->updateTmpWeb($coach_id, $user_info['uniacid'], $status, $sh_text);

        }

        return $res;

    }

    /**
     * 时间管理
     * @param $data
     * @return bool
     */
    public static function timeEditOld($data)
    {
        $update = [
            'is_work'   => $data['is_work'],
            'start_time'=> $data['start_time'],
            'end_time'  => $data['end_time'],
        ];
        $config_model = new Config();

        $config = $config_model->dataInfo(['uniacid' => $data['uniacid']]);
        $insert = [];

        foreach ($data['time_text'] as $item) {
            $time = $item['sub'];
            $hours = 0;
            foreach ($time as $ti) {
                if ($ti['status'] == 1) {
                    $hours += 1;
                }
            }
            if (!empty($item['sub'])) {
                $insert[] = [
                    'coach_id' => $data['coach_id'],
                    'date' => date('Y-m-d', $item['dat_str']),
                    'info' => $item['sub'],
                    'hours' => $hours * ($data['time_unit'] / 60),
                    'uniacid' => $data['uniacid'],
                    'create_time' => time(),
                    'start_time' => $data['start_time'],
                    'end_time' => $data['end_time'],
                    'max_day' => $config['max_day'],
                    'time_unit' => $data['time_unit']
                ];
            }
        }

        $is_work = $data['is_work'];
        Db::startTrans();
        try {
            $date = array_column($insert, 'date');
            $ids = CoachTime::where(['coach_id' => $data['coach_id']])->whereIn('date', $date)->column('id');
            if ($ids) {
                CoachTime::whereIn('id', $ids)->delete();
                CoachTimeList::whereIn('time_id', $ids)->delete();
            }
            $res = self::update($update, ['id' => $data['coach_id']]);
            if ($res === false) {
                throw new \Exception();
            }

            if ($insert) {
                foreach ($insert as $item) {
                    $info = $item['info'];
                    $item['info'] = json_encode($item['info']);
                    $id = CoachTime::insertGetId($item);
                    $list_insert = [];
                    foreach ($info as $value) {
                        $list_insert[] = [
                            'uniacid' => $data['uniacid'],
                            'coach_id' => $data['coach_id'],
                            'time_id' => $id,
                            "time_str" => $value['time_str'],
                            "time_str_end" => (int)$value['time_str'] + ($item['time_unit'] * 60),
                            "time_text" => $value['time_text'],
                            "time_texts" => $value['time_texts'],
                            "status" => $value['status'],
                            'create_time' => time(),
                            'is_click' => $value['is_click'],
                            'is_work' => $is_work
                        ];
                    }
                    $res = CoachTimeList::insertAll($list_insert);
                    if (!$res) {
                        throw new \Exception();
                    }
                }
            }
            Db::commit();
        } catch (\Exception $exception) {
            Db::rollback();
            return false;
        }
        return true;
    }


    /**
     * @param $data
     * @功能说明:技师时间管理
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-01-24 16:46
     */
    public static function timeEdit($data)
    {
        $update = [
            'is_work'   => $data['is_work'],
            'start_time'=> $data['start_time'],
            'end_time'  => $data['end_time'],
        ];

        Db::startTrans();
        //修改技师工作时间
        self::update($update, ['id' => $data['coach_id']]);
        //修改技师休息时间
        CoachTimeList::timeEdit($data);

        Db::commit();

        return true;
    }
    /**
     * 获取不接单的技师
     * @param $uniacid
     * @return array
     */
    public function getCancelCoach($uniacid)
    {
        return $this->where(['is_work' => 0, 'uniacid' => $uniacid])->column('id');
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-11-08 18:27
     * @功能说明:
     */
    public function coachLevelInfo($coach_level){

        if(!empty($coach_level)){

            $time_long  = $coach_level['time_long'];

            $level_model= new CoachLevel();

            $dis = [

                'uniacid' => $coach_level['uniacid'],

                'status'  => 1
            ];
            //查下个等级
            $next_level = $level_model->where($dis)->where('time_long','>',$time_long)->order('time_long,id desc')->find();

            if(!empty($next_level)){

                $next_level = $next_level->toArray();

                if($next_level['top']<=$coach_level['top']){
                    //相差时间
                    $coach_level['differ_time_long'] = 0;
                    //相差业绩
                    $coach_level['differ_price']    = 0;
                    //相差积分
                    $coach_level['differ_integral'] = 0;
                    //还差加钟
                    $coach_level['differ_add_price'] = 0;

                    $coach_level['differ_online_time'] = 0;

                }else{

                    $min_time_long = $level_model->where($dis)->where('time_long','<',$time_long)->max('time_long');

                    $min_time_long = !empty($min_time_long)?$min_time_long:0;
                    //相差时间
                    $coach_level['differ_time_long']= $coach_level['time_long']-$min_time_long>0?$coach_level['time_long']-$min_time_long:0;
                    //相差业绩
                    $coach_level['differ_price']    = $next_level['price']-$coach_level['price']>0?$next_level['price']-$coach_level['price']:0;
                    //相差积分
                    $coach_level['differ_integral'] = $next_level['integral']-$coach_level['integral']>0?$next_level['integral']-$coach_level['integral']:0;
                    //相差在线时长
                    $coach_level['differ_online_time'] = $next_level['online_time']-$coach_level['online_time']>0?$next_level['online_time']-$coach_level['online_time']:0;
                    //加钟金额
                    $next_add_price = $next_level['price']*$next_level['add_balance']/100;

                    $now_add_price  = $coach_level['price']*$coach_level['add_balance']/100;
                    //还差加钟
                    $coach_level['differ_add_price'] = $next_add_price -$now_add_price>0?round($next_add_price -$now_add_price,2):0 ;
                }
                //下一等级
                $coach_level['next_level_title'] = $next_level['title'];
            }
        }

        return $coach_level;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-01-29 14:48
     * @功能说明:获取技师工作状态
     */
    public function getCoachWorkStatus($coach_id,$uniacid){

        $config_model = new ConfigSetting();

        $config = $config_model->dataInfo($uniacid);
        //服务中
        $working_coach = $this->getWorkingCoach($uniacid);
        //当前时间不可预约
        $cannot = CoachTimeList::getCannotCoach($uniacid);

        $coach = $this->dataInfo(['id'=>$coach_id]);

        $config_model = new Config();

        $configs= $config_model->dataInfo(['uniacid'=>$uniacid]);

        $time = $this->getCoachEarliestTimev3($coach,$configs);

        if(in_array($config['coach_format'],[1,3])){

            $cannot = array_diff($cannot,$working_coach);

            if (in_array($coach_id,$working_coach)){

                $text_type = 2;

            }elseif (empty($time)||$coach['is_work']==0){

                $text_type = 4;

            }elseif (!in_array($coach_id,$cannot)){

                $text_type = 1;

            }else{

                $text_type = 3;
            }

        }else{

            $working_coach = array_merge($working_coach,$cannot);

            $this->where(['index_top'=>0])->update(['index_top'=>1]);

            $this->where('id','in',$working_coach)->update(['index_top'=>0]);

            if ($coach['is_work']==0||empty($time)){

                $text_type = 4;

            }elseif ($coach['index_top']==1){

                $text_type = 1;

            }else{

                $text_type = 3;
            }
        }

        return $text_type;
    }


    /**
     * @param $user_id
     * @功能说明:获取屏蔽的技师
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-01-30 17:42
     */
    public function getShieldCoach($user_id){

        $shield_model = new ShieldList();

        $dis = [

            'user_id' => $user_id,
        ];

        $coach_id = $shield_model->where($dis)->where('type','in',[2,3])->column('coach_id');

        return $coach_id;
    }


    /**
     * @param $order
     * @功能说明:获取订单里想关联服务的技师
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-28 16:19
     */
    public function getOrderServiceCoach($order){

        $service_model = new ServiceCoach();

        $data = [];

        foreach ($order['order_goods'] as $k=>$v){

            $arr = $service_model->where(['ser_id'=>$v['goods_id']])->column('coach_id');

            if($k==0){

                $data = $arr;

            }else{

                $data = array_intersect($arr,$data);
            }

        }

        return $data;

    }


    /**
     * @param $order
     * @功能说明:转单获取订单对应技师的价格
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-06-28 18:27
     */
    public function getCoachServicePrice($order,$coach_id){

        $service_coach_model = new ServiceCoach();

        $service_model = new Service();

        $total_price = 0;

        foreach ($order['order_goods'] as $k=>$v){

            $price = $service_coach_model->where(['ser_id'=>$v['goods_id'],'coach_id'=>$coach_id])->value('price');

            if(!is_numeric($price)||$price<0){

                $price = $service_model->where(['id'=>$v['goods_id']])->value('price');
            }

            $price = $price*$v['num'];

            $total_price+=$price;

        }

        return round($total_price,2);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-03 10:53
     * @功能说明:设置可服务的技师
     */
    public function setIndexTopCoach($uniacid){

        $key = 'indexTopCoach_key';

        incCache($key,1,$uniacid);

        $value = getCache($key,$uniacid);

        if($value==1){

            $keys = 'indexTopCoach_keys';

            if(empty(getCache($keys,$uniacid))){
                //服务中
                $working_coach = $this->getWorkingCoach($uniacid);
                //当前时间不可预约
                $cannot = CoachTimeList::getCannotCoach($uniacid);

                $working_coach = array_merge($working_coach,$cannot);

                $this->where(['index_top'=>0])->update(['index_top'=>1]);

                $this->where('id','in',$working_coach)->update(['index_top'=>0]);

            }

            setCache($keys,1,7,$uniacid);
        }

        decCache($key,1,$uniacid);

        return true;
    }


    /**
     * @param $uniacid
     * @功能说明:工作状态
     * @author chenniang
     * @DataTime: 2024-11-25 18:55
     */
    public function setWorkType($uniacid){
        //服务中
        $working_coach = $this->getWorkingCoach($uniacid);
        //当前时间不可预约
        $cannot = CoachTimeList::getCannotCoach($uniacid);

        $this->where(['work_type'=>0])->update(['work_type'=>1]);

        $this->where('id','in',$cannot)->update(['work_type'=>3]);

        $this->where('id','in',$working_coach)->update(['work_type'=>2]);

        return true;
    }



    /**
     * @param $coach
     * @功能说明:获取合伙人分销比例
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-22 13:52
     */
    public function partnerBalance($coach,$order){

        $config = getConfigSettingArr($order['uniacid'],['coach_agent_balance','partner_coach_balance','partner_admin_balance']);

        $order  = array_merge($order,$config);
        //平台承担多少
        $order['partner_company_balance'] = 100-$order['partner_coach_balance'] - $order['partner_admin_balance'];

        $order['partner_id'] = $order['broker_id'] = 0;

        if(getPromotionRoleAuth(3,$order['uniacid'])==0){

            $coach['broker_id'] = 0;
        }

        if(!empty($coach['broker_id'])){

            $broker_model = new CoachBroker();

            $broker = $broker_model->dataInfo(['id'=>$coach['broker_id'],'status'=>2]);

            if(!empty($broker)){

                $order['partner_id'] = $broker['user_id'];

                $order['broker_id']  = $broker['id'];
            }
        }

        $level_model = new BrokerLevel();

        $order['coach_agent_balance'] = $level_model->getBrokerBalance($order['broker_id'],$order['uniacid']);

        if($order['coach_agent_balance']<=0){

            $order['partner_id'] = $order['broker_id'] = 0;
        }

        $addclockBalance_model = new AddClockBalance();

        $order['coach_agent_balance'] = $addclockBalance_model->getObjBalance($order,$order['coach_agent_balance'],2,$order['admin_id']);

        return $order;
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-22 13:35
     * @功能说明:申请技师
     */
    public function coachApply($input,$user_id,$uniacid,$admin_user=0){

        if(!empty($user_id)){

            $cap_dis[] = ['user_id','=',$user_id];

            $cap_dis[] = ['status','>',-1];

            $cap_info = $this->dataInfo($cap_dis);

            if(!empty($cap_info)&&in_array($cap_info['status'],[1,2,3])){

                return ['code'=>500,'msg'=>'用户已经申请过'];
            }
        }else{

            $wehre[] = ['mobile','=',$input['mobile']];

            $wehre[] = ['status','>',-1];

            if(!empty($input['id'])){

                $wehre[] = ['id','<>',$input['id']];

            }

            $find = $this->where($wehre)->find();

            if(!empty($find)){

              //  return ['code'=>500,'msg'=>'该电话号码已经注册过'];
            }
        }

        $input['status']      = !empty($input['status'])?$input['status']:1;

        $input['auth_status'] = isset($input['auth_status'])?$input['auth_status']:0;

        if(!empty($admin_user)){

            $input['admin_id'] = $admin_user;

            $admin_model = new Admin();

            $coach_check_auth = $admin_model->where(['id'=>$admin_user])->value('coach_check_auth');

            if($coach_check_auth==1){

                $input['status'] = 2;

                $input['auth_status'] = 2;

            }else{

                $input['status'] = 1;

                $input['auth_status'] = 0;
            }
        }

        if(isset($input['id'])){

            unset($input['id']);
        }

        $input['uniacid'] = $uniacid;

        $input['user_id'] = $user_id;

        $input['is_work'] = !empty($input['is_work'])?$input['is_work']:0;

        $input['id_card'] = !empty($input['id_card'])?implode(',',$input['id_card']):'';

        $input['license'] = !empty($input['license'])?implode(',',$input['license']):'';

        $input['self_img']= !empty($input['self_img'])?implode(',',$input['self_img']):'';

        if(isset($input['service_price'])){

            unset($input['service_price']);
        }

        if(isset($input['car_price'])){

            unset($input['car_price']);
        }
        //经纪人
        if(!empty($input['partner_id'])){

            $broker_model = new CoachBroker();

            $broker = $broker_model->dataInfo(['user_id'=>$input['partner_id'],'status'=>2]);

            if(!empty($broker)){

                $input['broker_id'] = $broker['id'];

            }else{

                unset($input['partner_id']);
            }
        }

        if(!empty($input['partner_id'])){

            $input['partner_time'] = time();
        }

        if(!empty($input['status'])&&in_array($input['status'],[2,4])){

            $input['sh_time'] = time();
        }
        //自定义佣金比例
        if(isset($input['custom_balance'])){

            $custom_balance = $input['custom_balance'];

            unset($input['custom_balance']);
        }

        if(!empty($input['account_id'])){

            $account_id = $input['account_id'];

            unset($input['account_id']);
        }

        if(isset($input['store'])){

            $store = $input['store'];

            unset($input['store']);
        }
        //同步技师的免出行配置
        $input = $this->synCarConfig($input);
        //驳回后再次申请
        if(!empty($cap_info)&&$cap_info['status']==4){

            if(isset($store)){

                StoreCoach::where(['coach_id'=>$cap_info['id']])->delete();
                //关联门店
                StoreCoach::dataSave($store,$uniacid,$cap_info['id']);
            }

            $res = $this->dataUpdate(['id'=>$cap_info['id']],$input);

            $id = $cap_info['id'];
        }else{

            if(empty($input['start_time'])){

                $input['start_time'] = $input['end_time'] = '00:00';
            }

            $res = $this->dataAdd($input);

            $id = $this->getLastInsID();
            //自定义佣金比例
            if(!empty($custom_balance)){

                $insert = [

                    'uniacid'   => $uniacid,

                    'coach_id'  => $id,

                    'balance'   => $custom_balance['balance'],

                    'start_time'=> $custom_balance['start_time'],

                    'end_time'  => $custom_balance['end_time'],

                    'add_balance_status'=> $custom_balance['add_balance_status'],

                    'add_basis_balance' => $custom_balance['add_basis_balance'],
                ];
                $customBalalance_model = new CustomBalance();

                $customBalalance_model->dataAdd($insert);
            }
            //关联门店
            StoreCoach::dataSave($store,$uniacid,$id);

            if(!empty($account_id)){

                $account_model = new CoachAccount();

                $account_model->dataUpdate(['id'=>$account_id],['coach_id'=>$id]);
            }
            //初始化技师流水
            CoachWater::initWater($uniacid,$id);
        }
        return $res;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-05 09:46
     * @功能说明:平台订单需要改成拒单状态
     */
    public function companyOrderResult($order){

        $user_id = $this->where(['id'=>$order['coach_id']])->value('user_id');

        if(empty($user_id)&&!empty($order['coach_id'])){

            $order_model = new Order();

            $order_model->dataUpdate(['id'=>$order['id']],['pay_type'=>8]);

            $notice_model = new NoticeList();
            //增加后台提醒
            $notice_model->dataAdd($order['uniacid'],$order['id'],3,$order['admin_id']);

        }

        return true;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-06 16:43
     * @功能说明:获取审核结果
     */
    public function checkAuthData($data){

        $cap_dis[] = ['user_id','=',$data['id']];

        $cap_dis[] = ['status','in',[1,2,3,4]];
        //查看是否是团长
        $cap_info = $this->where($cap_dis)->order('id desc')->find();

        $cap_info = !empty($cap_info)?$cap_info->toArray():[];
        //-1表示未申请团长，1申请中，2已通过，3取消,4拒绝
        $arr['coach_status'] = !empty($cap_info)?$cap_info['status']:-1;

        $arr['sh_text'] = !empty($cap_info)?$cap_info['sh_text']:'';

        $arr['coach_id'] = !empty($cap_info)?$cap_info['id']:0;

        $arr['coach_name'] = !empty($cap_info)?$cap_info['coach_name']:'';

        $arr['wallet_status'] = in_array($arr['coach_status'],[2,3])?1:0;

        return $arr;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-06-14 16:11
     * @功能说明:技师车费到账
     */
    public function coachCarPriceAccount($order,$payConfig){

        $arr = getConfigSettingArr($order['uniacid'],['car_price_account','account_pay_type','tax_point']);
        //秒到账
        if($arr['car_price_account']==1&&!empty($order['coach_id'])){

            $member = new Member();

            $comm_model  = new Commission();

            $admin_model = new Admin();

            $comm_where = [

                [
                    'order_id','=',$order['id']
                ],
                [
                    'status','=',1
                ],
                [
                    'type','in',[8,13]
                ]
            ];

            $data = $comm_model->dataInfo($comm_where);
            //银行卡转账
            if($arr['account_pay_type']==4){

                $auth = AdminMenu::getAuthList((int)$order['uniacid'],['heepay']);

                if($auth['heepay']==true){

                    $arr['account_pay_type']=5;
                }
            }

            if($arr['account_pay_type']==4){
                //分账
                $member->adapayCommission($data,$order['id']);
            }

            Db::startTrans();

            if(!empty($data)&&$data['cash']>0){
                //修改佣金状态
                $res = $comm_model->dataUpdate(['id'=>$data['id']],['status'=>2,'cash_time'=>time()]);

                if($res==0){

                    Db::rollback();

                    return false;
                }

                $admin_share_car = $comm_model->where(['order_id'=>$data['order_id'],'status'=>1,'type'=>23])->find();

                if(!empty($admin_share_car)){

                    $admin_share_car = $admin_share_car->toArray();

                    $res = $comm_model->where(['id'=>$admin_share_car['id']])->update(['status'=>2,'cash_time'=>time()]);

                    if($res==0){

                        Db::rollback();

                        return false;
                    }
                    //代理商车费，注意是扣除
                    $record_model = new CashUpdateRecord();

                    $res = $record_model->totalUpdateCash($data['uniacid'],3,$admin_share_car['top_id'],$admin_share_car['cash'],0,'',1,9,$admin_share_car['id']);

                    if(!empty($res['code'])){

                        Db::rollback();

                        return false;
                    }
                }

                $car_cash  = $data['cash'];
                //给技师
                if($data['type']==8){
                    //申请提现
                    $coachInfo = $this->dataInfo(['id'=>$order['coach_id']]);
                }else{
                    //给代理商
                    $coachInfo = $admin_model->dataInfo(['id'=>$order['admin_id']]);

                    $coachInfo['admin_id'] = $coachInfo['admin_pid'];
                }
                //获取税点
                $tax_point = $arr['tax_point'];

                $balance   = 100-$tax_point;

                $insert = [

                    'uniacid'  => $order['uniacid'],

                    'user_id'  => $coachInfo['user_id'],

                    'coach_id' => $coachInfo['id'],

                    'admin_id' => $coachInfo['admin_id'],

                    'total_price' => $car_cash,

                    'balance' => $balance,

                    'apply_price' => round($car_cash * $balance / 100, 2),

                    'service_price' => round( $car_cash * $tax_point / 100, 2),

                    'code' => orderCode(),

                    'tax_point' => $tax_point,

                    'text' => '自动到账，订单号:'.$order['order_code'],

                    'type' => $data['type']==8?2:9,

                    'is_auto' => 1,

                    'apply_transfer' => $arr['account_pay_type']
                ];

                $wallet_model = new Wallet();
                //提交审核
                $res = $wallet_model->dataAdd($insert);

                if ($res == 0) {

                    Db::rollback();

                    return false;
                }

                $id = $wallet_model->getLastInsID();

                $user_model = new User();

                $update = [

                    'sh_time'   => time(),

                    'status'    => 2,

                    'online'    => $arr['account_pay_type'],

                    'true_price'=> $insert['apply_price']
                ];

                $user = $user_model->dataInfo(['id'=>$coachInfo['user_id']]);

                if(empty($user)){

                    Db::rollback();

                    return false;
                }
                //微信
                if($arr['account_pay_type']==1){

                    $openid_text = [

                        0 => 'wechat_openid',

                        1 => 'app_openid',

                        2 => 'web_openid'
                    ];

                    $openid = $user[$openid_text[$order['app_pay']]];
                    //微信相关模型
                    $wx_pay = new WxPay($order['uniacid']);
                    //微信提现
                    $res    = $wx_pay->crteateMchPay($payConfig,$openid,$insert['apply_price']);

                    if($res['result_code']=='SUCCESS'&&$res['return_code']=='SUCCESS'){

                        $update['user_num'] = $openid;

                        if(!empty($res['out_batch_no'])){

                            $update['payment_no'] = $res['out_batch_no'];
                        }

                        if(!empty($res['batch_id'])){

                            $update['detail_id'] = $res['batch_id'];
                        }
                        //转账中
                        if(isset($res['batch_status'])&&(in_array($res['batch_status'],['ACCEPTED','PROCESSING','CLOSED']))){

                            $update['status'] = 4;
                        }

                        $wallet_model->dataUpdate(['id'=>$id],$update);

                    }else{

                        $errlog_model = new ErrLog();

                        $errinsert = [

                            'uniacid' => $order['uniacid'],

                            'text'    => serialize($res),

                            'type'    => 'coach_car',

                            'order_id'=> $data['id']
                        ];
                        //加个错误日志,有些傻逼嘴硬
                        $errlog_model->dataAdd($errinsert);

                        Db::rollback();

                        return false;
                    }

                }elseif ($arr['account_pay_type']==2){
                    //支付宝
                    if(empty($user['alipay_number'])){

                        Db::rollback();

                        return false;
                    }
                    $pay_model = new PayModel($payConfig);

                    $res = $pay_model->onPaymentByAlipay($user['alipay_number'],$insert['apply_price'],$user['alipay_name']);

                    if(!empty($res['alipay_fund_trans_toaccount_transfer_response']['code'])&&$res['alipay_fund_trans_toaccount_transfer_response']['code']==10000&&$res['alipay_fund_trans_toaccount_transfer_response']['msg']=='Success'){

                        $update['payment_no'] = $res['alipay_fund_trans_toaccount_transfer_response']['order_id'];

                        $update['user_num']   = $user['alipay_number'];

                        $wallet_model->dataUpdate(['id'=>$id],$update);

                    }else{

                        $errlog_model = new ErrLog();

                        $errinsert = [

                            'uniacid' => $order['uniacid'],

                            'text'    => serialize($res),

                            'type'    => 'coach_car',

                            'order_id'=> $data['id']
                        ];

                        $errlog_model->dataAdd($errinsert);

                        Db::rollback();

                        return false;
                    }

                }elseif ($arr['account_pay_type']==4){
                    //分账银行卡
                    $code = orderCode();

                    $adapay = new Adapay($order['uniacid']);

                    $member = $member->dataInfo(['user_id'=>$coachInfo['user_id'],'status'=>1]);

                    if(empty($member)){

                        Db::rollback();

                        return false;
                    }

                    $record_model = new AccountsRecord();
                    //初始化账户
                    $record_model->giveMemberCash($member['id'],$insert['tax_point'],$order['uniacid'],$insert['apply_price']);
                    //打款
                    $res = $adapay->drawCash($code,$member['member_id'],$insert['apply_price'],$order['uniacid']);

                    if($res['status']=='failed'){

                        Db::rollback();

                        return false;
                    }
                    $update['payment_no'] = $res['id'];

                    $update['adapay_code']= $code;
                    //处理中
                    if($res['status']=='pending'){

                        $update['status'] = 4;
                    }

                    $wallet_model->dataUpdate(['id'=>$id],$update);

                }elseif ($arr['account_pay_type']==5){

                    $heepay_model = new \app\heepay\model\Member();

                    $heepay_res = $heepay_model->checkUserCash($coachInfo['user_id'],$insert['apply_price']);

                    if(!empty($heepay_res['code'])){

                        Db::rollback();

                        return false;
                    }
                    $heepay_model = new HeePay($order['uniacid']);

                    $code = longbingorderCodetf();

                    $res = $heepay_model->wallet($code,$insert['true_price'],$heepay_res['heepay_id']);

                    if($res['body']['bill_status']=='-1'){

                        Db::rollback();

                        return false;
                    }

                    $update = [

                        'payment_no' => $code,

                        'adapay_code'=> $res['body']['hy_bill_no'],

                        'status' => 4
                    ];

                    $wallet_model->dataUpdate(['id'=>$data['id']],$update);

                }else{

                    Db::rollback();

                    return false;
                }
            }
            Db::commit();
        }
        return true;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-06-25 16:43
     * @功能说明:
     */
    public function getCoachOnlieTime($uniacid){

        $dis = [

            'uniacid' => $uniacid,

            'status'  => 2
        ];

        $log_model = new WorkLog();

        $list = $this->where($dis)->field('id as coach_id')->select()->toArray();

        if(!empty($list)){

            foreach ($list as &$value){
                //初始化每一天的工作时间
                $log_model->updateTimeOnline($value['coach_id'],1);
            }
        }

        return true;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-10-26 17:06
     * @功能说明:获取技师上个半月可提现的金额
     */
    public function getCoachCashByHalfMonth($coach_id,$true_cash,$type=1){
        //前15天的
        $time = 15*86400;

        $order_model = new Order();

        $dis[] = ['status','=',2];

        $dis[] = ['top_id','=',$coach_id];

        $dis[] = ['create_time','<=',time()-$time];
        //服务费
        if($type==1){

            $dis[] = ['pay_type','=',7];

            $dis[] = ['type','in',[3,7,17,18,24,25]];
        }else{
            //车费
            $dis[] = ['type','in',[8]];
        }

//        $cash  = $order_model->alias('a')
//                ->join('massage_service_order_commission b','a.id = b.order_id')
//                ->where($dis)
//                ->group('b.id')
//                ->sum('b.cash');

        $cash = Commission::where($dis)->sum('cash');

        $wallet_model = new Wallet();

        $where[] = ['coach_id','=',$coach_id];

        $where[] = ['status','in',[1,2,4,5]];

        if($type==1){

            $where[] = ['type','=',1];

        }else{
            $where[] = ['type','=',2];
        }

        $wallt_cash = $wallet_model->where($where)->sum('total_price');

        $update_model = new CashUpdateRecord();

        if($type==1){

            $add_update_cash = $update_model->where(['coach_id'=>$coach_id,'status'=>1,'is_add'=>1])->where('create_time','<=',time()-$time)->sum('cash');

            $del_update_cash = $update_model->where(['coach_id'=>$coach_id,'status'=>1,'is_add'=>0])->where('create_time','<=',time()-$time)->sum('cash');

            $coach_cash = $cash-$wallt_cash+$add_update_cash-$del_update_cash;
        }else{

            $coach_cash = $cash-$wallt_cash;
        }

        $coach_cash = $coach_cash>0?$coach_cash:0;

        $coach_cash = $coach_cash>$true_cash?$true_cash:$coach_cash;

        return round($coach_cash,2);
    }


    /**
     * @param $coach_id
     * @功能说明:校验技师车费
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-01-22 18:47
     */
    public function checkCarPrice($coach_id){

        $comm_model   = new Commission();

        $wallet_model = new Wallet();

        $coach_model  = new Coach();

        $arr['cash'] = $comm_model->where(['top_id'=>$coach_id,'type'=>8,'status'=>2])->sum('cash');

        $arr['wallet_cash'] = $wallet_model->where(['coach_id'=>$coach_id,'type'=>2])->where('status','in',[1,2,4,5])->sum('total_price');

        $arr['coach_cash'] = $coach_model->where(['id'=>$coach_id])->sum('car_price');

        $arr['have_cash'] = $arr['cash']-$arr['wallet_cash']-$arr['coach_cash'];

        return $arr;
    }


    /**
     * @param $coach_id
     * @功能说明:校验技师服务费
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-01-22 18:49
     */
    public function checkServicePrice($coach_id){

        $comm_model   = new Commission();

        $wallet_model = new Wallet();

        $coach_model  = new Coach();

        $record_model = new CashUpdateRecord();

        $arr['cash']  = $comm_model->where(['top_id'=>$coach_id,'status'=>2])->where('type','in',[3,7])->sum('cash');

        $arr['wallet_cash'] = $wallet_model->where(['coach_id'=>$coach_id,'type'=>1])->where('status','in',[1,2,4,5])->sum('total_price');

        $arr['coach_cash'] = $coach_model->where(['id'=>$coach_id])->sum('service_price');

        $arr['update_inc_cash'] = $record_model->where(['coach_id'=>$coach_id,'status'=>1,'type'=>1,'is_add'=>1])->sum('cash');

        $arr['update_del_cash'] = $record_model->where(['coach_id'=>$coach_id,'status'=>1,'type'=>1,'is_add'=>0])->sum('cash');

        $arr['have_cash'] = $arr['cash']-$arr['wallet_cash']-$arr['coach_cash']+$arr['update_inc_cash']-$arr['update_del_cash'];

        return $arr;
    }


    /**
     * @param $coach_id
     * @param $version
     * @功能说明:初始化技师佣金信息 兼容1.0版本
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-05-06 11:43
     */
    public function coachCashInit($coach_id,$version,$uniacid){

        $key = 'coachCashInit_key';

        incCache($key,1,$uniacid,10);

        if($version==0&&getCache($key,$uniacid)==1&&time()>strtotime('2024-05-16')){

            $comm_model = new Commission();

            $order_model= new Order();

            $order_id = $comm_model->where(['top_id'=>$coach_id,'type'=>3])->where('status','>',-1)->column('order_id');

            $order_list = $order_model->where(['coach_id'=>$coach_id,'pay_type'=>7])->where('coach_cash','>',0)->where('id','not in',$order_id)->field('*,id as order_id')->select()->toArray();

            if(!empty($order_list)){

                foreach ($order_list as $value){

                    $insert = [

                        'uniacid' => $value['uniacid'],

                        'user_id' => $value['user_id'],

                        'top_id'  => $value['coach_id'],

                        'order_id'=> $value['order_id'],

                        'order_code' => $value['order_code'],

                        'type'    => 3,

                        'cash'    => $value['coach_cash'],

                        'admin_id'=> $value['admin_id'],

                        'balance' => $value['coach_balance'],

                        'status'  => 2,

                        'create_time' => $value['create_time'],

                        'update_time' => $value['create_time'],

                        'cash_time'  => $value['create_time'],

                        'is_init' => 7
                    ];

                    $res = $comm_model->dataAdd($insert);
                }
            }

            $coach_model = new Coach();

            $coach_model->dataUpdate(['id'=>$coach_id],['version'=>1]);
        }

        decCache($key,1,$uniacid);

        return true;
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-01-29 14:48
     * @功能说明:获取技师工作状态
     */
    public function getCoachListWorkStatus($coach_list,$uniacid){

        if(empty($coach_list)){

            return $coach_list;
        }

        $config_model = new ConfigSetting();

        $config = $config_model->dataInfo($uniacid);
        //服务中
        $working_coach = $this->getWorkingCoach($uniacid);
        //当前时间不可预约
        $cannot = CoachTimeList::getCannotCoach($uniacid);

        $config_model = new Config();

        $configs= $config_model->dataInfo(['uniacid'=>$uniacid]);

        foreach ($coach_list as &$value){

            $coach_id = $value['id'];

            $time = $this->getCoachEarliestTime($coach_id,$configs);

            if(in_array($config['coach_format'],[1,3])){

                $cannot = array_diff($cannot,$working_coach);

                if (in_array($coach_id,$working_coach)){

                    $text_type = 2;

                }elseif (empty($time)||$value['is_work']==0){

                    $text_type = 4;

                }elseif (!in_array($coach_id,$cannot)){

                    $text_type = 1;

                }else{

                    $text_type = 3;
                }

            }else{

                $working_coach = array_merge($working_coach,$cannot);

                if ($value['is_work']==0||empty($time)){

                    $text_type = 4;

                }elseif (!in_array($coach_id,$working_coach)){

                    $text_type = 1;

                }else{

                    $text_type = 3;
                }
            }

            $value['text_type'] = $text_type;
        }

        return $coach_list;
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-10-26 17:06
     * @功能说明:获取技师上个半月可提现的金额
     */
    public function getCoachCashByHalfMonthV2($coach_id,$true_cash,$type=1,$date_type=1){

        if($date_type==1){

            $half = strtotime(date('Y-m-16'));

            if(time()>=$half){

                $time = strtotime(date('Y-m-01'));

            }else{

                $time = strtotime(date('Y-m-16',strtotime('-1 month')));
            }
        }else{

            $time = strtotime(date('Y-m-d'));

            $currentWeekDay = date('w', time());

            $time = $time - ($currentWeekDay - 1)*86400;

            $time -= 86400*7;
        }
        $order_model = new Order();

        $dis[] = ['status','=',2];

        $dis[] = ['top_id','=',$coach_id];

        $dis[] = ['create_time','<=',$time];
        //服务费
        if($type==1){
           // $dis[] = ['a.pay_type','=',7];

            $dis[] = ['type','in',[3,7,17,18,24,25]];
        }else{
            //车费
            $dis[] = ['type','in',[8]];
        }

//        $cash  = $order_model->alias('a')
//                ->join('massage_service_order_commission b','a.id = b.order_id')
//                ->where($dis)
//                ->group('b.id')
//                ->sum('b.cash');

        $cash = Commission::where($dis)->sum('cash');

        $wallet_model = new Wallet();

        $where[] = ['coach_id','=',$coach_id];

        $where[] = ['status','in',[1,2,4,5]];

        if($type==1){

            $where[] = ['type','=',1];

        }else{
            $where[] = ['type','=',2];
        }

        $wallt_cash = $wallet_model->where($where)->sum('total_price');

        $update_model = new CashUpdateRecord();

        if($type==1){

            $add_update_cash = $update_model->where(['coach_id'=>$coach_id,'status'=>1,'is_add'=>1,'type'=>1])->where('create_time','<=',$time)->sum('cash');

            $del_update_cash = $update_model->where(['coach_id'=>$coach_id,'status'=>1,'is_add'=>0,'type'=>1])->where('create_time','<=',$time)->sum('cash');

            $coach_cash = $cash-$wallt_cash+$add_update_cash-$del_update_cash;
        }else{

            $coach_cash = $cash-$wallt_cash;
        }

        $coach_cash = $coach_cash>0?$coach_cash:0;

        $coach_cash = $coach_cash>$true_cash?$true_cash:$coach_cash;

        return round($coach_cash,2);
    }


    /**
     * @param $coach
     * @功能说明:同步技师的免出行配置
     * @author chenniang
     * @DataTime: 2024-07-16 17:44
     */
    public function synCarConfig($coach){

        if(isset($coach['admin_id'])){

            if($coach['admin_id']==0){

                $coach['free_fare_bear'] = getConfigSetting($coach['uniacid'],'free_fare_bear');
            }else{

                $config_model = new AdminConfig();

                $coach['free_fare_bear'] = $config_model->where(['admin_id'=>$coach['admin_id']])->value('free_fare_bear');

                $coach['free_fare_bear'] = !empty($coach['free_fare_bear'])?$coach['free_fare_bear']:0;
            }
        }

        return $coach;
    }


    /**
     * @author chenniang
     * @DataTime: 2024-08-13 16:06
     * @功能说明:技师佣金统计
     */
    public function getCoachCommTotal($dis,$type){

        $cash = $this->alias('a')
                ->join('massage_service_order_commission b','b.top_id = a.id')
                ->where($dis)
                ->where('b.status','=',2)
                ->where('b.type','in',$type)
                ->group('b.id')
                ->sum('cash');

        return round($cash,2);
    }



}