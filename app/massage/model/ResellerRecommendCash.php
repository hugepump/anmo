<?php
namespace app\massage\model;

use app\BaseModel;
use think\facade\Db;

class ResellerRecommendCash extends BaseModel
{
    //定义表名
    protected $name = 'massage_reseller_recommend_cash';




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
     * @DataTime: 2024-05-24 11:55
     * @功能说明:
     */
    public function addRecommendCash($user_info,$reseller){

        if(!empty($reseller)){

            if($reseller['recommend_cash'] == 0){

                return true;
            }

            $err_type = 0;
            //校验时间
            if($reseller['recommend_day']*86400+$reseller['recommend_time']<time()){

                $reseller['recommend_cash'] = 0;

                $err_type = 1;
            }
            $city_model = new City();

            $admin_model = new Admin();
            //分销员同城市
            if(!empty($reseller['admin_id'])&&$reseller['recommend_range']==1){

                $admin = $admin_model->where(['id'=>$reseller['admin_id'],'status'=>1])->field('city_type,city_id')->find();

                if(!empty($admin)){

                    if($admin['city_type']==1){

                        $city_filed = 'city';

                    }elseif ($admin['city_type']==2){

                        $city_filed = 'area';
                    }else{

                        $city_filed = 'province';
                    }

                    $city = $user_info[$city_filed];

                    if(!empty($city)){

                        $city_count = $city_model->where(['id'=>$admin['city_id'],'status'=>1,'title'=>$city])->count();

                        if($city_count==0){

                            $reseller['recommend_cash'] = 0;

                            $err_type = 2;
                        }
                    }
                }
            }
            //平台开放城市
            if($reseller['recommend_range']==2){

                $city_count = $city_model->where(['province'=>$user_info['province'],'status'=>1,'city'=>$user_info['city']])->count();

                if($city_count==0){

                    $reseller['recommend_cash'] = 0;

                    $err_type = 3;
                }
            }

            if(!empty($reseller['admin_id'])){

                $admin = $admin_model->where(['id'=>$reseller['admin_id'],'status'=>1])->field('id,recommend_cash_auth')->find();

                if(!empty($admin)&&$admin['recommend_cash_auth']==0){

                    return true;

                    $reseller['recommend_cash'] = 0;
                }
            }

            Db::startTrans();

            $insert = [

                'uniacid' => $reseller['uniacid'],

                'reseller_id' => $reseller['id'],

                'user_id' => $user_info['id'],

                'province' => $user_info['province'],

                'city' => $user_info['city'],

                'area' => $user_info['area'],

                'recommend_cash' => $reseller['recommend_cash'],

                'create_time' => time(),

                'err_type' => $err_type,

                'admin_id' => !empty($admin['id'])?$admin['id']:0,

                'status'   => 0
            ];

            $this->dataAdd($insert);

            $id = $this->getLastInsID();

            if(!empty($admin['id'])&&$reseller['recommend_cash']>0){

                $record_model = new CashUpdateRecord();

                $res = $record_model->totalUpdateCash($reseller['uniacid'],3,$admin['id'],$reseller['recommend_cash'],0,'',$admin['id'],-2,$id,$reseller['id']);

                if(!empty($res['code'])){

                    $this->dataUpdate(['id'=>$id],['recommend_cash'=>0,'err_type'=>4]);

                    $reseller['recommend_cash'] = 0;
                }
            }

          //  $user_model->where(['id'=>$reseller['user_id']])->update(['new_cash'=>Db::Raw("new_cash+$cash"),'cash'=>Db::Raw("cash+$cash")]);

            Db::commit();

            Db::startTrans();

            $cash = $reseller['recommend_cash'];

            $water_model = new UserWater();

            $res = $water_model->updateCash($reseller['uniacid'],$reseller['user_id'],$cash,1,$id,0,5);

            if($res==0){

                Db::rollback();

                return 0;
            }

            $res = $this->dataUpdate(['id'=>$id],['status'=>1]);

            if($res==0){

                Db::rollback();

                return 0;
            }

            Db::commit();
        }

        return true;
    }






}