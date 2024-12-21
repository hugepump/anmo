<?php
namespace app\store\model;

use app\BaseModel;
use app\massage\model\Admin;
use app\massage\model\Coach;
use think\facade\Db;

class StoreList extends BaseModel
{
    //定义表名
    protected $name = 'massage_store_list';



    protected $append = [

        'admin_name'
    ];



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

            $admin = $admin_model->where(['id'=>$value,'status'=>1,'store_auth'=>1])->count();

            return $admin>0?$value:0;

        }else{

            return 0;
        }
    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-23 16:49
     * @功能说明:代理商名字
     */
    public function getAdminNameAttr($value,$data){

        if(!empty($data['admin_id'])){

            $admin_model = new Admin();

            $name = $admin_model->where(['id'=>$data['admin_id']])->value('agent_name');

            return $name;
        }
    }

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
     * @DataTime: 2023-01-31 10:15
     * @功能说明:推荐技师下拉框
     */
    public function indexDataList($dis,$alh,$page=10){

        $data = $this->where($dis)
            ->field(['title,cover,star,total_num,positive_rate,id,start_time,end_time,order_rate', $alh])
            ->order('distance asc,id desc')
            ->paginate($page)
            ->toArray();

        return $data;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-01-31 10:15
     * @功能说明:推荐技师下拉框
     */
    public function indexDataListV2($dis,$alh,$page=10){

        $data = $this->alias('a')
            ->join('shequshop_school_admin b','a.admin_id = b.id')
            ->join('massage_store_cate_connect c','a.id = c.store_id','left')
            ->where($dis)
            ->field(['a.title,a.cover,a.star,a.total_num,a.positive_rate,a.id,a.start_time,a.end_time,a.order_rate', $alh])
            ->group('a.id')
            ->order('distance asc,id desc')
            ->paginate($page)
            ->toArray();

        return $data;

    }


    /**
     * @param $store_id
     * @功能说明:获取门店的技师id  门店的代理商必须和技师的代理商相同
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-15 23:50
     */
    public function getStoreCoachId($store_id,$type=1){

        $coach_model = new Coach();

        $dis[] = ['b.status','=',1];

        $dis[] = ['c.status','=',1];

        $dis[] = ['c.store_auth','=',1];

        if($type==1){

            $dis[] = ['d.store_id','=',$store_id];

        }else{
            //搜索名字
            $dis[] = ['b.title','like','%'.$store_id.'%'];
        }

        $data = $coach_model->alias('a')
                ->join('massage_store_coach d','a.id = d.coach_id')
                ->join('massage_store_list b','d.store_id = b.id')
                ->join('shequshop_school_admin c','(a.admin_id = c.id ||a.admin_id = 0) AND b.admin_id = c.id')
                ->where($dis)
                ->column('a.id');

        //dump($dis,$data);exit;
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
     * @DataTime: 2023-03-29 16:45
     * @功能说明:获取门店营业状态
     */
    public function workStatus($store){

        $start_time = strtotime($store['start_time']);

        $end_time   = strtotime($store['end_time']);
        //跨日
        if($end_time <=$start_time){
            //查看此时处于上一个周期还是这个周期
            //上一个周期
            if(time()<$end_time){

                $start_time -= 86400;

            }else{
                //当前周期
                $end_time += 86400;
            }
        }

        if(time()>=$start_time&&time()<=$end_time){

            return 1;

        }else{

            return 0;
        }
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-29 18:42
     * @功能说明:根据技师获取绑定的门店
     */
    public function checkStoreStatus($store_id,$start_time,$end_time){

        if(empty($store_id)){

            return true;
        }

        $store = $this->dataInfo(['status'=>1,'id'=>$store_id]);

        if(empty($store)){

            return ['code'=>500,'msg'=>'门店已经下架'];
        }

        $work_status = $this->checkStoreTime($store,$start_time,$end_time);

        if(!empty($work_status['code'])){

            return $work_status;
        }

        return true;
    }




    /**
     * @param $coach
     * @param $start_time
     * @param $end_time
     * @功能说明:校验技师时间
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-02 14:48
     */
    public function checkStoreTime($store,$start_time,$end_time){

        $all_day = 1;
        //判断不是全体24小时上班
        if($store['start_time']!=$store['end_time']){

            $all_day = 0;

        }
        //全天不判断
        if($all_day==0){
            //门店上班时间
            $coach_start_time = strtotime($store['start_time'])-strtotime(date('Y-m-d',time()))+strtotime(date('Y-m-d',$start_time));
            //门店下班时间
            $coach_end_time   = strtotime($store['end_time'])-strtotime(date('Y-m-d',time()))+strtotime(date('Y-m-d',$start_time));

            if($end_time<$coach_start_time){

                $coach_start_time -= 86400;

                $coach_end_time   -= 86400;
            }


            $coach_end_time = $coach_end_time>$coach_start_time?$coach_end_time:$coach_end_time+86400;

            if($start_time<$coach_start_time||$end_time>$coach_end_time){

                return ['code'=>500,'msg'=>'不在门店营业时间内,门店营业时间:'.$store['start_time'].'-'.$store['end_time']];
            }
        }

        return true;

    }


    public static function getFirstValue($where, $field = 'admin_id')
    {
        return self::where($where)->value($field);
    }







}