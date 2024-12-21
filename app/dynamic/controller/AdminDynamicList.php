<?php

namespace app\dynamic\controller;

use app\AdminRest;
use app\ApiRest;

use app\dynamic\model\DynamicComment;
use app\dynamic\model\DynamicFollow;
use app\dynamic\model\DynamicList;
use app\dynamic\model\DynamicThumbs;
use app\dynamic\model\DynamicWatchRecord;
use app\massage\model\Coach;

use app\massage\model\Goods;

use app\massage\model\MassageConfig;
use app\massage\model\Order;

use think\App;
use think\facade\Db;
use think\Request;


class AdminDynamicList extends AdminRest
{

    protected $order_model;

    protected $model;

    protected $cap_info;

    protected $list_model;

    protected $commment_model;

    protected $thumbs_model;

    protected $config_model;

    public function __construct(App $app)
    {

        parent::__construct($app);

        $this->model = new Coach();

        $this->order_model = new Order();

        $this->list_model = new DynamicList();

        $this->commment_model = new DynamicComment();

        $this->thumbs_model = new DynamicThumbs();

        $this->config_model = new MassageConfig();


    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-01-29 11:00
     * @功能说明:动态列表
     */
    public function dynamicList(){

        $input = $this->_param;

        $dis[] = ['a.uniacid','=',$this->_uniacid];

        if(!empty($input['coach_name'])){

            $dis[] = ['b.coach_name','like','%'.$input['coach_name'].'%'];

        }

        if(!empty($input['start_time'])&&!empty($input['end_time'])){

            $dis[] = ['a.create_time','between',"{$input['start_time']},{$input['end_time']}"];

        }

        if(!empty($input['status'])){

            $dis[] = ['a.status','=',$input['status']];

        }else{

            $dis[] = ['a.status','>',-1];
        }

        $data = $this->list_model->adminDataList($dis,$input['limit']);

        $list = [

            0 =>'all',

            1 =>'ing',

            2 =>'pass',

            3 =>'nopass',

        ];

        $where = [

            'uniacid' => $this->_uniacid
        ];

        foreach ($list as $k=> $value){

            if(!empty($k)){

                $where['status'] = $k;

                $data[$value] = $this->list_model->where($where)->count();

            }else{

                $data[$value] = $this->list_model->where($where)->where('status','>',-1)->count();
            }

        }

        return $this->success($data);

    }





    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-01-29 11:00
     * @功能说明:动态列表
     */
    public function dynamicInfo(){

        $input = $this->_param;

        $dis= [

            'id' => $input['id']
        ];


        $data = $this->list_model->dataInfo($dis);

        if(empty($data)){

            $this->errorMsg('动态被删除');
        }
        //技师情况
        $data['coach_info'] = $this->model->where(['id'=>$data['coach_id']])->field('coach_name,work_img,is_work')->find();

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-01-29 15:08
     * @功能说明:删除评论
     */
    public function dynamicDel(){

        $input = $this->_input;

        $res = $this->list_model->dataUpdate(['id'=>$input['id']],['status'=>-1]);

        return $this->success($res);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-01-29 17:45
     * @功能说明:动态审核
     */
    public function dynamicCheck(){

        $input = $this->_input;

        $dis = [

            'id' => $input['id']
        ];

        $input['check_time'] = time();

        $res = $this->list_model->dataUpdate($dis,$input);

        return $this->success($res);
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-01-29 17:45
     * @功能说明:动态审核
     */
    public function dynamicTop(){

        $input = $this->_input;

        $dis = [

            'id' => $input['id']
        ];

        $res = $this->list_model->dataUpdate($dis,$input);

        return $this->success($res);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-01-29 11:47
     * @功能说明:评论列表
     */
    public function commentList(){

        $input = $this->_param;

        $dis[] = ['a.uniacid','=',$this->_uniacid];

        if(!empty($input['status'])){

            $dis[] = ['a.status','=',$input['status']];

        }else{

            $dis[] = ['a.status','>',-1];
        }

        if(!empty($input['title'])){

            $dis[] = ['c.title','like','%'.$input['title'].'%'];

        }

        if(!empty($input['start_time'])&&!empty($input['end_time'])){

            $dis[] = ['a.create_time','between',"{$input['start_time']},{$input['end_time']}"];

        }

        if(!empty($input['user_name'])){

            $dis[] = ['b.nickName','like','%'.$input['user_name'].'%'];

        }

        $data = $this->commment_model->getCommentList($dis,$input['limit']);

        $list = [

            0 =>'all',

            1 =>'ing',

            2 =>'pass',

            3 =>'nopass',

        ];

        $where = [

            'uniacid' => $this->_uniacid
        ];

        foreach ($list as $k=> $value){

            if(!empty($k)){

                $where['status'] = $k;

                $data[$value] = $this->commment_model->where($where)->count();

            }else{

                $data[$value] = $this->commment_model->where($where)->where('status','>',-1)->count();
            }

        }

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-01-29 15:08
     * @功能说明:发布评论
     */
    public function commentCheck(){

        $input = $this->_input;

        $dis= [

            'id' => $input['id']
        ];

        $res = $this->commment_model->dataUpdate($dis,$input);

        return $this->success($res);
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-01-29 15:08
     * @功能说明:删除评论
     */
    public function commentDel(){

        $input = $this->_input;

        $res = $this->commment_model->dataUpdate(['id'=>$input['id']],['status'=>-1]);

        return $this->success($res);
    }

















}
