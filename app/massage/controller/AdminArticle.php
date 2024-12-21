<?php
namespace app\massage\controller;
use app\AdminRest;


use app\massage\model\ArticleList;
use app\massage\model\Coach;
use app\massage\model\FieldList;
use app\massage\model\Order;
use app\massage\model\SubData;
use app\massage\model\SubList;
use app\massage\model\User;
use think\App;

use app\massage\model\Printer as model;

use think\facade\Db;


class AdminArticle extends AdminRest
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
    public function fieldList(){

        $input = $this->_param;

        $dis[] = ['uniacid','=',$this->_uniacid];

        if(isset($input['status'])){

            $dis[] = ['status','=',1];
        }else{

            $dis[] = ['status','>',-1];
        }

        $data = $this->field_model->dataList($dis,$input['limit']);

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 13:28
     * @功能说明:字段列表
     */
    public function fieldSelect(){

        $input = $this->_param;

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['status','=',1];

        $data = $this->field_model->where($dis)->order('top desc,id desc')->select()->toArray();

        return $this->success($data);

    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 13:28
     * @功能说明:字段详情
     */
    public function fieldInfo(){

        $input = $this->_param;

        $dis = [

            'id' => $input['id']
        ];

        $data = $this->field_model->dataInfo($dis);

        return $this->success($data);

    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 13:29
     * @功能说明:添加字段
     */
    public function fieldAdd(){

        $input = $this->_input;

        $input['uniacid']  = $this->_uniacid;

        $data = $this->field_model->dataAdd($input);

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 13:29
     * @功能说明:编辑字段
     */
    public function fieldUpdate(){

        $input = $this->_input;

        $dis = [

            'id' => $input['id']
        ];

        $input['uniacid']  = $this->_uniacid;

        $data = $this->field_model->dataUpdate($dis,$input);

        return $this->success($data);

    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 13:28
     * @功能说明:字段列表
     */
    public function articleList(){

        $input = $this->_param;

        $dis[] = ['uniacid','=',$this->_uniacid];

        if(isset($input['status'])){

            $dis[] = ['status','=',1];
        }else{

            $dis[] = ['status','>',-1];
        }

        if(!empty($input['title'])){

            $dis[] = ['title','like','%'.$input['title'].'%'];

        }

        $data = $this->model->dataList($dis,$input['limit']);

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

        $data['field'] = array_values(array_column($data['field'],'field_id'));

        return $this->success($data);

    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 13:29
     * @功能说明:添加字段
     */
    public function articleAdd(){

        $input = $this->_input;

        $input['uniacid']  = $this->_uniacid;

        $data = $this->model->dataAdd($input);

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 13:29
     * @功能说明:编辑字段
     */
    public function articleUpdate(){

        $input = $this->_input;

        $dis = [

            'id' => $input['id']
        ];

        $input['uniacid']  = $this->_uniacid;

        $data = $this->model->dataUpdate($dis,$input);

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-12-12 11:24
     * @功能说明:当前文章关联的表单标题
     */
    public function subTitle(){

        $input = $this->_param;

        $data = $this->model->getFieldTitle($input['article_id']);

        return $this->success($data);

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-12-12 11:23
     * @功能说明:提交内容
     */
    public function subDataList(){

        $input = $this->_param;

        $data = $this->model->getFieldTitle($input['article_id']);

        $diss[] = ['article_id','=',$input['article_id']];

        $diss[] = ['status','=',1];

        if(!empty($input['start_time'])&&!empty($input['end_time'])){

            $diss[] = ['create_time','between',"{$input['start_time']},{$input['end_time']}"];

        }

        $list = $this->sub_list_model->dataList($diss,$input['limit']);

        if(!empty($list['data'])){

            $user_model = new User();

            foreach ($list['data'] as &$v){

                $user_info = $user_model->where(['id'=>$v['user_id']])->field('nickName,avatarUrl')->find();

                $v['nickName'] = $user_info['nickName'];

                $v['avatarUrl'] = $user_info['avatarUrl'];

                if(!empty($data)){

                    foreach ($data as $vs){

                        $dis = [

                            'field_id' => $vs['field_id'],

                            'sub_id'   => $v['id']
                        ];

                        $v[$vs['field_id']] = $this->sub_data_model->where($dis)->value('value');

                    }
                }

            }

        }

        return $this->success($list);

    }










}
