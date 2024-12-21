<?php
namespace app\massage\controller;
use app\AdminRest;


use app\ApiRest;
use app\massage\model\ArticleList;
use app\massage\model\FieldList;
use app\massage\model\SubData;
use app\massage\model\SubList;
use think\App;

use app\massage\model\Printer as model;

use think\facade\Db;


class IndexArticle extends ApiRest
{

    protected $model;

    protected $sub_list_model;

    protected $sub_data_model;

    protected $field_model;

    public function __construct(App $app) {

        parent::__construct($app);

        $this->model = new ArticleList();

        $this->sub_list_model = new SubList();

        $this->sub_data_model = new SubData();

        $this->field_model = new FieldList();

    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 13:28
     * @功能说明:字段列表
     */
    public function articleList(){

        $input = $this->_param;

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['status','=',1];

        if(!empty($input['title'])){

            $dis[] = ['title','like','%'.$input['title'].'%'];

        }

        $data = $this->model->dataList($dis,10);

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 13:28
     * @功能说明:字段详情
     */
    public function articleInfo(){

        $input = $this->_param;

        $dis = [

            'id' => $input['id']
        ];

        $data = $this->model->dataInfo($dis);

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-12-12 10:46
     * @功能说明:提交文章表单
     */
    public function subArticleForm(){

        $input = $this->_input;

        $insert = [

            'uniacid' => $this->_uniacid,

            'user_id' => $this->getUserId(),

            'article_id' => $input['article_id']
        ];

        $this->sub_list_model->dataAdd($insert);

        $id = $this->sub_list_model->getLastInsID();

        foreach ($input['sub_data'] as $k=>$v){

            $insert_data[$k] = [

                'uniacid' => $this->_uniacid,

                'sub_id'  => $id,

                'key'     => $v['key'],

                'value'   => $v['value'],

                'field_id'   => $v['field_id'],

                'field_type' => $v['field_type'],
            ];

        }

        $this->sub_data_model->saveAll($insert_data);

        return $this->success(true);
    }












}
