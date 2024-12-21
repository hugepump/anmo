<?php
namespace app\shop\controller;
use app\AdminRest;
use app\shop\model\Article;
use app\shop\model\Banner;
use app\shop\model\MsgConfig;
use app\shop\model\PayConfig;
use think\App;
use app\shop\model\Config as Model;


class AdminSetting extends AdminRest
{


    protected $model;


    public function __construct(App $app) {

        parent::__construct($app);

        $this->model = new Model();


    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-12 15:04
     * @功能说明:配置详情
     */
    public function configInfo(){


        $dis = [

            'uniacid' => $this->_uniacid
        ];

        $data  = $this->model->dataInfo($dis);

        return $this->success($data);


    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-12 16:14
     * @功能说明:编辑配置
     */
    public function configUpdate(){

        $input = $this->_input;

        $dis = [

            'uniacid' => $this->_uniacid
        ];

        $data  = $this->model->dataUpdate($dis,$input);

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-12 16:15
     * @功能说明:banner列表
     */
    public function bannerList(){

        $input = $this->_param;

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['status','>',-1];

        $banner_model = new Banner();

        $data = $banner_model->dataList($dis,$input['limit']);

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-12 16:18
     * @功能说明:添加banner
     */
    public function bannerAdd(){

        $input = $this->_input;

        $input['uniacid'] = $this->_uniacid;

        $banner_model = new Banner();

        $res = $banner_model->dataAdd($input);

        return $this->success($res);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-12 16:20
     * @功能说明:编辑banner
     */
    public function bannerUpdate(){

        $input = $this->_input;

        $dis = [

            'id' => $input['id']
        ];

        $banner_model = new Banner();

        $res = $banner_model->dataUpdate($dis,$input);

        return $this->success($res);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-19 13:27
     * @功能说明:banner详情
     */
    public function bannerInfo(){

        $input = $this->_param;

        $dis = [

            'id' => $input['id']
        ];

        $banner_model = new Banner();

        $res = $banner_model->dataInfo($dis);

        return $this->success($res);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-12 16:27
     * @功能说明:新闻列表
     */
    public function articleList(){

        $input = $this->_param;

        $article_model = new Article();

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['status','>',-1];

        if(!empty($input['title'])){

            $dis[] = ['title','like','%'.$input['title'].'%'];
        }

        $data = $article_model->dataList($dis,$input['limit']);

        return $this->success($data);

    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-12 16:37
     * @功能说明:添加文章
     *
     */
    public function articleAdd(){

        $input = $this->_input;

        $article_model = new Article();

        $input['uniacid'] = $this->_uniacid;

        $res = $article_model->dataAdd($input);

        return $this->success($res);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-19 13:35
     * @功能说明：编辑文章
     */
    public function articleUpdate(){

        $input = $this->_input;

        $article_model = new Article();

        $dis = [

            'id' => $input['id']
        ];

        $res = $article_model->dataUpdate($dis,$input);

        return $this->success($res);

    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-19 13:35
     * @功能说明：文章详情
     */
    public function articleInfo(){

        $input = $this->_param;

        $article_model = new Article();

        $dis = [

            'id' => $input['id']
        ];

        $res = $article_model->dataInfo($dis);

        return $this->success($res);


    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-19 13:50
     * @功能说明:文章下拉框
     */
    public function articleSelect(){

        $input = $this->_param;

        $article_model = new Article();

        $dis = [

            'uniacid' => $this->_uniacid,

            'status' => 1
        ];

        $res = $article_model->where($dis)->field('id,title')->select()->toArray();

        return $this->success($res);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-18 10:53
     * @功能说明:支付配置详情
     */
    public function payConfigInfo(){

        $dis = [

            'uniacid' => $this->_uniacid
        ];

        $pay_model = new PayConfig();

        $data = $pay_model->dataInfo($dis);

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-18 10:55
     * @功能说明:编辑支付配置
     */
    public function payConfigUpdate(){

        $input = $this->_input;


        $dis = [

            'uniacid' => $this->_uniacid
        ];

        if(!strstr($input['cert_path'],FILE_UPLOAD_PATH)){

            $input['cert_path'] = FILE_UPLOAD_PATH.$input['cert_path'];

        }
        if(!strstr($input['key_path'],FILE_UPLOAD_PATH)){

            $input['key_path']  = FILE_UPLOAD_PATH.$input['key_path'];
        }

        $pay_model = new PayConfig();

        $data = $pay_model->dataUpdate($dis,$input);

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-31 15:16
     * @功能说明:修改密码
     */
    public function updatePass(){

        $input = $this->_input;

        $admin = new \app\shop\model\Admin();

        $update = [

            'passwd'  => checkPass($input['pass']),
        ];

        $res = $admin->dataUpdate(['uniacid'=>$this->_uniacid],$update);



        return $this->success($res);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-12 15:04
     * @功能说明:配置详情
     */
    public function msgConfigInfo(){

        $msg_model = new MsgConfig();

        $dis = [

            'uniacid' => $this->_uniacid
        ];

        $data  = $msg_model->dataInfo($dis);

        return $this->success($data);


    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-12 16:14
     * @功能说明:编辑配置
     */
    public function msgConfigUpdate(){

        $input = $this->_input;

        $msg_model = new MsgConfig();

        $dis = [

            'uniacid' => $this->_uniacid
        ];

        $data  = $msg_model->dataUpdate($dis,$input);

        return $this->success($data);

    }







}
