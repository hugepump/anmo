<?php
namespace app\massage\controller;
use app\ApiRest;

use app\massage\model\OrderAddress;
use app\massage\model\ShopGoods;
use app\Rest;

use app\shop\model\Cap;
use app\shop\model\Car;
use app\shop\model\Goods;
use app\shop\model\GoodsCate;
use app\shop\model\User;
use think\App;

use think\Request;



class IndexGoods extends ApiRest
{

    protected $model;

    protected $cate_model;

    protected $car_model;

    public function __construct(App $app) {

        parent::__construct($app);

//        $this->model = new Goods();
//
//        $this->cate_model = new GoodsCate();
//
//        $this->car_model = new Car();



    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-18 16:46
     * @功能说明：分类列表
     */
    public function cateList(){

        $input = $this->_param;

        $dis = [

            'uniacid' => $this->_uniacid,

            'status'  => 1,

            'cap_id'  => $this->getCapInfo()['id']
        ];

        $data = $this->cate_model->where($dis)->order('id desc')->select()->toArray();

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-18 16:49
     * @功能说明:首页
     */
    public function index(){

        $input = $this->_param;

        $lat = !empty($input['lat'])?$input['lat']:0;

        $lng = !empty($input['lng'])?$input['lng']:0;

        $dis = [

            'uniacid' => $this->_uniacid,

            'status'  => 1,

            'cap_id'  => $this->getCapInfo()['id']
        ];

        if(!empty($input['cate_id'])){

            $dis['cate_id'] = $input['cate_id'];

        }
        //商品信息
        $data['goods_list'] = $this->model->dataList($dis,10);
        //门店信息
        $data['store_info'] = $this->getCapInfo();
        //计算距离
        $distance = getDistances( $data['store_info']['lng'], $data['store_info']['lat'],$lng,$lat);

        $cap_model = new Cap();

        $data['store_info']['distance'] = $cap_model->getDistanceAttr($distance);

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-24 14:19
     * @功能说明:商品列表
     */
    public function goodsList(){

        $input = $this->_param;

        $dis = [

            'uniacid' => $this->_uniacid,

            'status'  => 1,

            'cap_id'  => $this->getCapInfo()['id']
        ];

        if(!empty($input['cate_id'])){

            $dis['cate_id'] = $input['cate_id'];

        }
        //商品信息
        $data = $this->model->dataList($dis,10);

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-24 14:07
     * @功能说明:购物车信息
     */
    public function carInfo(){
        //购物车信息
        $car_info   = $this->car_model->carPriceAndCount($this->getUserId(),$this->getCapInfo()['id'],1);

        return $this->success($car_info);

    }


    /**
     * 商品详情
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function goodsInfo()
    {
        $id = $this->request->param('id', '');
        if (empty($id)) {
            return $this->error('商品不存在');
        }


        $data = ShopGoods::getInfo(['id' => $id]);
        $data['images'] = json_decode($data['images'], true);
        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-19 11:57
     * @功能说明:首页选择楼长列表
     */
    public function indexCapList(){

        $input = $this->_param;

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['status','=',2];

        $dis[] = ['business_status','=',1];

        if(!empty($input['store_name'])){

            $dis[] = ['store_name','like','%'.$input['store_name'].'%'];
        }

        $lat = !empty($input['lat'])?$input['lat']:0;

        $lng = !empty($input['lng'])?$input['lng']:0;

        $alh     = '(2 * 6378.137* ASIN(SQRT(POW(SIN(3.1415926535898*('.$lat.'-lat)/360),2)+COS(3.1415926535898*'.$lat.'/180)* COS('.$lat.' * 3.1415926535898/180)*POW(SIN(3.1415926535898*('.$lng.'-lng)/360),2))))*1000 as distance';

        $alhs    = '(2 * 6378.137* ASIN(SQRT(POW(SIN(3.1415926535898*('.$lat.'-lat)/360),2)+COS(3.1415926535898*'.$lat.'/180)* COS('.$lat.' * 3.1415926535898/180)*POW(SIN(3.1415926535898*('.$lng.'-lng)/360),2))))*1000<20000';

        $cap_model = new Cap();

        $data = $cap_model->dataList($dis,$alh,$alhs,10);

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $v['distance'] = getDistances($v['lng'],$v['lat'],$lng,$lat);

                $v['distance'] = $cap_model->getDistanceAttr($v['distance']);

            }

        }

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-19 13:24
     * @功能说明:选择楼长
     */
    public function selectCap(){

        $input = $this->_input;

        $user_model = new User();

        $res = $user_model->dataUpdate(['id'=>$this->getUserId()],['cap_id'=>$input['cap_id']]);

        return $this->success($res);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-24 14:46
     * @功能说明:添加到购物车
     */
    public function addCar(){

        $input = $this->_input;

        $insert = [

            'uniacid' => $this->_uniacid,

            'user_id' => $this->getUserId(),

            'cap_id'  => $this->getCapInfo()['id'],

            'goods_id'=> $input['goods_id'],

            'spe_id'  => $input['spe_id']

        ];

        $info = $this->car_model->dataInfo($insert);
        //增加数量
        if(!empty($info)){

            $res = $this->car_model->dataUpdate(['id'=>$info['id']],['goods_num'=>$info['goods_num']+$input['goods_num']]);

        }else{
            //添加到购物车
            $insert['goods_num'] = $input['goods_num'];

            $insert['status']    = 1;

            $res = $this->car_model->dataAdd($insert);

        }

        return $this->success($res);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-24 14:54
     * @功能说明:删除购物车
     */
    public function delCar(){

        $input = $this->_input;

        $info = $this->car_model->dataInfo(['id'=>$input['id']]);
        //加少数量
        if($info['goods_num']>$input['goods_num']){

            $res = $this->car_model->dataUpdate(['id'=>$info['id']],['goods_num'=>$info['goods_num']-$input['goods_num']]);

        }else{

            $res = $this->car_model->where(['id'=>$info['id']])->delete();
        }

        return $this->success($res);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-25 10:39
     * @功能说明:
     */
    public function carUpdate(){

        $input = $this->_input;

        $res = $this->car_model->where('id','in',$input['id'])->update(['status'=>$input['status']]);

        return $this->success($res);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-24 14:59
     * @功能说明:批量删除购物车
     */
    public function delSomeCar(){

        $input = $this->_input;

        $dis = [

            'uniacid' => $this->_uniacid,

            'user_id' => $this->getUserId(),

            'cap_id'  => $this->getCapInfo()['id'],

        ];

        $res = $this->car_model->where($dis)->delete();

        return $this->success($res);
    }






}
