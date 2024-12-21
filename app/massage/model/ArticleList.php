<?php
namespace app\massage\model;

use AlibabaCloud\Client\AlibabaCloud;
use app\BaseModel;
use Exception;
use think\facade\Db;

class ArticleList extends BaseModel
{
    //定义表名
    protected $name = 'massage_article_list';


    protected $append = [

        'field'
    ];


    /**
     * @param $value
     * @param $data
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-12-12 10:12
     */
    public function getFieldAttr($value,$data){

        if(!empty($data['id'])){

            $connect_model = new ArticleConnect();

            $dis = [

                'a.article_id' => $data['id'],

                'b.status' => 1
            ];

            $list = $connect_model->alias('a')
                    ->join('massage_article_form_field b','a.field_id = b.id')
                    ->where($dis)
                    ->field('a.*,b.title,b.field_type,b.is_required')
                    ->group('b.id')
                    ->order('b.top desc,a.id desc')
                    ->select()
                    ->toArray();

            return $list;

        }


    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-12-12 11:25
     * @功能说明:获取表单当前的字段
     */
    public function getFieldTitle($id){

        $dis = [

            'a.article_id' => $id,

            'b.status' => 1
        ];

        $connect_model = new ArticleConnect();

        $list = $connect_model->alias('a')
            ->join('massage_article_form_field b','a.field_id = b.id')
            ->where($dis)
            ->field('a.*,b.title,b.field_type')
            ->group('b.id')
            ->order('b.top desc,b.id desc')
            ->select()
            ->toArray();

        return $list;

    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:04
     * @功能说明:添加
     */
    public function dataAdd($data){

        $data['create_time'] = time();

        if(isset($data['field'])){

            $field = $data['field'];

            unset($data['field']);
        }

        $res = $this->insert($data);

        $id = $this->getLastInsID();

        if(!empty($field)){

            $this->updateSome($id,$field,$data['uniacid']);
        }

        return $res;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-12-12 10:03
     * @功能说明:
     */
    public function updateSome($id,$field,$uniacid){

        $connect_model = new ArticleConnect();

        $connect_model->where(['article_id'=>$id])->delete();

        foreach ($field as $k=>$value){

            $insert[$k] = [

                'uniacid' => $uniacid,

                'article_id' => $id,

                'field_id' => $value
            ];
        }

        $connect_model->saveAll($insert);

        return true;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:05
     * @功能说明:编辑
     */
    public function dataUpdate($dis,$data){

        if(isset($data['field'])){

            $field = $data['field'];

            unset($data['field']);
        }

        $res = $this->where($dis)->update($data);

        if(!empty($field)){

            $this->updateSome($dis['id'],$field,$data['uniacid']);
        }

        return $res;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:06
     * @功能说明:列表
     */
    public function dataList($dis,$page){

        $data = $this->where($dis)->order('top desc,id desc')->paginate($page)->toArray();

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









}