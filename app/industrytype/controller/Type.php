<?php
namespace app\industrytype\controller;
use app\admin\model\User;
use app\AdminRest;

use app\massage\model\Coach;
use app\massage\model\Commission;
use app\massage\model\Service;
use app\massage\model\ShortCodeConfig;
use app\massage\model\Wallet;
use think\App;



class Type extends AdminRest
{

    protected $model;


    public function __construct(App $app) {

        parent::__construct($app);

        $this->model = new \app\industrytype\model\Type();

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 13:28
     * @功能说明:类型列表
     */
    public function typeList(){

        $this->model->initData($this->_uniacid);

        $input = $this->_param;

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['status','>',-1];

        if(!empty($input['title'])){

            $dis[] = ['title','like','%'.$input['title'].'%'];
        }

        $data = $this->model->dataList($dis,$input['limit']);

        return $this->success($data);
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 13:28
     * @功能说明:类型列表
     */
    public function typeSelect(){

        $this->model->initData($this->_uniacid);

        $input = $this->_param;

        $dis[] = ['uniacid','=',$this->_uniacid];

        if(isset($input['status'])){

            $dis[] = ['status','=',$input['status']];
        }else{

            $dis[] = ['status','=',1];
        }

        if(!empty($input['title'])){

            $dis[] = ['title','like','%'.$input['title'].'%'];
        }

        $data = $this->model->where($dis)->order('top desc,id desc')->select()->toArray();

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 13:29
     * @功能说明:添加类型
     */
    public function typeAdd(){

        $input = $this->_input;

        $input['uniacid'] = $this->_uniacid;

        $data = $this->model->dataAdd($input);

        return $this->success($data);
    }



    /**
     * @author chenniang
     * @DataTime: 2024-06-19 17:45
     * @功能说明:类型详情
     */
    public function typeInfo(){

        $input = $this->_param;

        $dis = [

            'id' => $input['id']
        ];

        $data = $this->model->dataInfo($dis);

        return $this->success($data);
    }




    /**
     * @author chenniang
     * @DataTime: 2024-06-19 16:32
     * @功能说明:发送短信验证码
     */
    public function typeUpdate(){

        $input = $this->_input;

        $dis = [

            'id' => $input['id']
        ];

        if(isset($input['status'])&&in_array($input['status'],[0,-1])){

            $coach_model = new Coach();

            $service_model = new Service();

            $find = $coach_model->where(['industry_type'=>$input['id']])->where('status','in',[1,2,3])->find();

            if(!empty($find)){

                $attendant_name = getConfigSetting($this->_uniacid,'attendant_name');

                return $this->error("有{$attendant_name}正在使用该类型");
            }

            $find = $service_model->where(['industry_type'=>$input['id']])->where('status','>',-1)->find();

            if(!empty($find)){

                return $this->error('有服务正在使用该类型');
            }
        }

        if(isset($input['index_show'])&&$input['index_show']==1){

            $count = $this->model->where('id','<>',$input['id'])->where(['index_show'=>1])->count();

            if($count>=3){

                return $this->error('首页推荐最多3个');
            }
        }

        $data = $this->model->dataUpdate($dis,$input);

        return $this->success($data);
    }


}
