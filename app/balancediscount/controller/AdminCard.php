<?php
namespace app\balancediscount\controller;
use app\AdminRest;

use app\balancediscount\model\Card;
use think\App;





class AdminCard extends AdminRest
{



    protected $card_model;

    protected $coupon_model;


    public function __construct(App $app) {

        parent::__construct($app);

        $this->card_model = new Card();

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-16 19:25
     * @功能说明:套餐列表
     */
    public function cardSelect(){

        $input = $this->_param;

        $dis = [

            'uniacid' => $this->_uniacid,

            'status'  => 1
        ];

        $data = $this->card_model->where($dis)->order('top desc,id desc')->select()->toArray();

        return $this->success($data);
    }




    /**
     * @author chenniang
     * @DataTime: 2024-09-03 18:46
     * @功能说明:套餐列表
     */
   public function cardList(){

       $input = $this->_param;

       $dis[] = ['uniacid','=',$this->_uniacid];

       $dis[] = ['status','>',-1];

       $data = $this->card_model->dataList($dis,$input['limit']);

       return $this->success($data);

   }


    /**
     * @author chenniang
     * @DataTime: 2024-09-04 10:08
     * @功能说明:添加套餐
     */
   public function cardAdd(){

       $input = $this->_input;

       $insert = [

           'uniacid' => $this->_uniacid,

           'discount'=> $input['discount'],

           'title'   => $input['title'],

           'price'   => $input['price'],

           'month'   => $input['month'],

           'status'  => 1,

//           'coach_balance' => $input['coach_balance'],
//
//           'admin_balance' => $input['admin_balance'],

           'top'=> $input['top'],

           'create_time' => time()
       ];

       $res = $this->card_model->insert($insert);


       return $this->success($res);
   }


    /**
     * @author chenniang
     * @DataTime: 2024-09-04 10:08
     * @功能说明:添加套餐
     */
    public function cardUpdate(){

        $input = $this->_input;

        $dis = [

            'id' => $input['id']
        ];

        $insert = [

            'uniacid' => $this->_uniacid,

            'discount'=> $input['discount'],

            'title'   => $input['title'],

            'price'   => $input['price'],

//            'coach_balance' => $input['coach_balance'],
//
//            'admin_balance' => $input['admin_balance'],

            'month'   => $input['month'],

            'top'=> $input['top'],

        ];

        $res = $this->card_model->dataUpdate($dis,$insert);

        return $this->success($res);
    }


    /**
     * @author chenniang
     * @DataTime: 2024-09-04 10:19
     * @功能说明:套餐详情
     */
    public function cardInfo(){

        $input = $this->_param;

        $dis = [

            'id' => $input['id']
        ];

        $data = $this->card_model->dataInfo($dis);

        return $this->success($data);
    }


    /**
     * @author chenniang
     * @DataTime: 2024-09-04 10:20
     * @功能说明:套餐上下架|删除
     */
    public function cardStatusUpdate(){

        $input = $this->_input;

        $dis = [

            'id' => $input['id']
        ];

        $data = $this->card_model->dataUpdate($dis,['status'=>$input['status']]);

        return $this->success($data);
    }


    /**
     * @author chenniang
     * @DataTime: 2024-11-26 16:22
     * @功能说明:储值折扣卡流水
     */
    public function discountCardWaterList(){

        $input= $this->_param;

        $data = lbData('massage/admin/Index/discountCardWaterList',$this->_token,1,$input);

        $data = $data['data'];

        return $this->success($data);

    }








}
