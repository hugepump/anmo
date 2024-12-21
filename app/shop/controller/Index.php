<?php
namespace app\shop\controller;
use app\ApiRest;

use app\Rest;

use app\shop\model\Article;
use app\shop\model\Banner;
use app\shop\model\Cap;
use app\shop\model\Car;
use app\shop\model\Config;
use app\shop\model\Goods;
use app\shop\model\GoodsCate;
use app\shop\model\User;
use think\App;

use think\Request;



class Index extends ApiRest
{

    protected $model;

    protected $article_model;


    public function __construct(App $app) {

        parent::__construct($app);

//        $this->model = new Banner();
//
//        $this->article_model = new Article();

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-23 09:20
     * @功能说明:首页
     */
   public function index(){

       $dis = [

           'uniacid' => $this->_uniacid,

           'status'  => 1
       ];

       $data['article'] = $this->article_model->where($dis)->field('id,title')->order('top desc,id desc')->select()->toArray();

       $data['banner']  = $this->model->where($dis)->field('id,img,link')->order('top desc,id desc')->select()->toArray();

       return $this->success($data);

   }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-23 09:55
     * @功能说明:文章详情
     */
   public function articleInfo(){

       $input = $this->_param;

       $dis = [

           'id' => $input['id']
       ];

       $data = $this->article_model->dataInfo($dis);

       $data['create_time'] = date('Y-m-d H:i:s',$data['create_time']);

       return $this->success($data);

   }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-23 14:16
     * @功能说明:获取配置信息
     */
   public function configInfo(){

       $dis = [

           'uniacid' => $this->_uniacid
       ];

       $config_model = new \app\massage\model\Config();

       $config = $config_model->dataInfo($dis);

       return $this->success($config);

   }





}
