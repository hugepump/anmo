<?php
namespace app\publics\controller;
use app\AdminRest;
use app\ApiRest;

use app\card\model\Job;
use app\dynamic\model\CardUser;
use app\dynamic\model\UserInfo;
use app\publics\model\TmplConfig;
use app\shop\model\AdminCompany;
use app\shop\model\AdminGoods;
use app\shop\model\AdminShopSpe;
use app\shop\model\AdminShopSpePrice;
use app\shop\model\AdminShopType;
use longbingcore\wxcore\WxTmpl;
use think\App;


class SomeThing extends AdminRest
{

    protected $model;
    public function __construct(App $app) {
        parent::__construct($app);
        $this->type_model      = new AdminShopType();
        $this->company_model   = new AdminCompany();
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2019-12-26 15:40
     * @功能说明: 获取模版消息
     */
    public function getCompany(){
        $dis[] = ['status' ,'=',1];
        $dis[] = ['uniacid','=',$this->_uniacid];
        $data  = $this->company_model->companySelect($dis);
        return $this->success($data);
    }

    /**
     * User: chenniang(龙兵科技)
     * Date: 2019-11-29 17:28
     * @return void
     * descption:获取员工
     */
    public function getStaffInfo(){
        $input = $this->_input;
        $user_model = new UserInfo();
        $user_info  = new CardUser();
//        $data = [];
        $dis[] = ['is_staff','=',1];
        $dis[] = ['uniacid','=',$this->_uniacid];
        if(!empty($input['name'])){
            $dis[]  = ['name','like',"%".$input['name']."%"];
        }
        $data = $user_model->where($dis)->field('fans_id as id,name')->select();
        if(!empty($data)){
            foreach ($data as &$v){
                if(empty($v['name'])){
                    $v['name'] = $user_info->where(['id'=>$v['id']])->value('nickName');
                }
            }
        }
        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-05-18 16:29
     * @功能说明:职位列表
     */
    public function jobList(){

        $job_model = new Job();

        $input = $this->_input;

        $dis[]  = ['uniacid','=',$this->_uniacid];

        $dis[]  = !empty($input['status'])?['status','>',0]:['status','>',-1];

        $list   = $job_model->where($dis)->order('top desc')->field('id,name')->select();

        return $this->success($list);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-03 15:16
     * @功能说明:获取平台所有的用户
     */
    public function getAllUser(){

        $user_info  = new CardUser();

        $dis = [

            'uniacid' => $this->_uniacid

        ];
        //只查id和昵称,一般用于下拉框
        $data = $user_info->where($dis)->field('id,nickName')->select()->toArray();

        return $this->success($data);
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-12-08 13:47
     * @功能说明:商品列表
     */
    public function goodsSelect(){

        $input = $this->_input;

        $goods_model = new AdminGoods();

        $spe_price_model = new AdminShopSpePrice();

        $dis[] = ['status','=',1];

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['is_member','=',0];

        if(!empty($input['title'])){

            $dis[] = ['name','like','%'.$input['title'].'%'];
        }

        $data = $goods_model->where($dis)->order('top desc,id desc')->paginate($input['limit'])->toArray();

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){
                //总库存
                $v['stock'] = $spe_price_model->getGoodsStock($v['id']);


            }
        }

        return $this->success($data);

    }


    /**
     * @param $goods_id
     * @功能说明:商品规格
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-01-19 14:26
     */
    public function goodsSpeList(){

        $input     = $this->_input;

        $spe_model = new AdminShopSpe();

        $spe_price_model = new AdminShopSpePrice();

        $goods_id = $input['goods_id'];

        $dis['goods_id'] = $goods_id;

        $dis['status']   = 1;

        $data['text']    = $spe_model->goodsSpe($dis);

        $data['price']   = $spe_price_model->goodsSpePrice($dis);

        if(!empty($data['price'])){

            foreach ($data['price'] as &$v){

                $v['title'] = $v['spe_name_text'];

                $v['true_id'] = $v['id'];

                $v['id']   = implode(',',$v['spe_array_text']);

            }
        }
        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-01-30 11:00
     * @功能说明:获取商品规格
     */
    public function getSpePrice(){

        $input = $this->_input;

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['goods_id','=',$input['goods_id']];

        $dis[] = ['status','>',-1];

        $spe_price_model = new AdminShopSpePrice();

        $data  = $spe_price_model->goodsSpePrice($dis);

        if(!empty($data)){

            foreach ($data as &$v){

                $v['spe_content'] = $v['spe_name_text'].':'.$v['price'];

                $v['spe_price_id']= $v['id'];
            }
        }
        return $this->success($data);
    }



}
