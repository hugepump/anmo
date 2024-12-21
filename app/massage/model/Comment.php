<?php
namespace app\massage\model;

use app\BaseModel;
use think\facade\Db;

class Comment extends BaseModel
{
    //定义表名
    protected $name = 'massage_service_order_comment';


    protected $append = [

        'lable_text',

        'order_goods',


    ];

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-19 17:05
     * @功能说明:子订单信息
     */

    public function getOrderGoodsAttr($value,$data){

        if(!empty($data['order_id'])&&isset($data['id'])){

            $order_goods_model = new OrderGoods();

            $comment_goods = new CommentGoods();

            $dis = [

                'order_id' => $data['order_id']
            ];

            $list = $order_goods_model->where($dis)->select()->toArray();

            if(!empty($list)){

                foreach ($list as &$value){

                    $dis = [

                        'service_id' => $value['goods_id'],

                        'comment_id' => $data['id']
                    ];

                    $info = $comment_goods->dataInfo($dis);

                    if(!empty($info)){

                        $value['star'] = $info['star'];
                    }

                }

            }

            return $list;

        }

    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-05 23:32
     * @功能说明:标签列表
     */
    public function getLableTextAttr($vaule,$data){

        if(!empty($data['id'])){

            $lable_model = new Lable();

            $dis = [

                'b.comment_id' => $data['id'],

                'a.status'     => 1

            ];
            $list = $lable_model->alias('a')
                    ->join('massage_service_comment_lable b','a.id = b.lable_id')
                    ->where($dis)
                    ->column('a.title');

            return array_values($list);

        }

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:04
     * @功能说明:添加
     */
    public function dataAdd($data){

        $data['create_time'] = isset($data['create_time']) ? $data['create_time'] : time();

        $data['created_time'] = time();

        $res = $this->insert($data);

        return $res;

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:05
     * @功能说明:编辑
     */
    public function dataUpdate($dis,$data){

        $res = $this->where($dis)->update($data);

        return $res;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:06
     * @功能说明:列表
     */
    public function dataList($dis,$page=10){

      $data = $this->alias('a')
             ->join('massage_service_order_list b','a.order_id = b.id','left')
             ->join('massage_service_order_goods_list c','a.order_id = c.order_id','left')
             ->join('massage_service_coach_list d','a.coach_id = d.id','left')
             ->join('massage_service_user_list e','a.user_id = e.id','left')
             ->where($dis)
             ->field('a.*,b.order_code,e.nickName,e.avatarUrl,c.goods_name,c.goods_cover,c.num,c.price,d.coach_name')
             ->group('a.id')
             ->order('a.is_good desc,a.create_time desc')
             ->paginate($page)
             ->toArray();

      return $data;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:06
     * @功能说明:列表
     */
    public function adminDataList($dis,$page=10){

        $data = $this->alias('a')
            ->join('massage_service_order_list b','a.order_id = b.id','left')
            ->join('massage_service_order_goods_list c','a.order_id = c.order_id','left')
            ->join('massage_service_coach_list d','a.coach_id = d.id','left')
            ->join('massage_service_user_list e','a.user_id = e.id','left')
            ->where($dis)
            ->field('a.*,b.order_code,e.nickName,e.avatarUrl,c.goods_name,c.goods_cover,c.num,c.price,d.coach_name')
            ->group('a.id')
            ->order('a.created_time desc,a.id desc')
            ->paginate($page)
            ->toArray();

        return $data;
    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:43
     * @功能说明:
     */
    public function dataInfo($dis){

        $data = $this->where($dis)->find();

        if(empty($data)){

            $this->dataAdd($dis);

            $data = $this->where($dis)->find();

        }

        return !empty($data)?$data->toArray():[];

    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-02-22 09:51
     * @功能说明:修改分数
     */
    public function updateStar($coach_id){

        $all_count= $this->where(['coach_id'=>$coach_id])->where('status','>',-1)->count();

        $all_star = $this->where(['coach_id'=>$coach_id])->where('status','>',-1)->sum('star');

        $now_star = $all_count>0?round($all_star/$all_count,1):5;

        $now_star = $now_star>5?5:$now_star;

        $coach_model = new Coach();

        $coach_model->dataUpdate(['id'=>$coach_id],['star'=>$now_star]);

        return true;

    }











}