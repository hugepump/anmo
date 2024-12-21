<?php
namespace app\member\model;

use app\BaseModel;
use app\massage\model\BalanceOrder;
use app\massage\model\Order;
use app\massage\model\User;
use think\facade\Db;

class Growth extends BaseModel
{
    //定义表名
    protected $name = 'massage_member_growth';


    protected $append = [

        'type_text'
    ];


    /**
     * @param $value
     * @param $data
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-10 17:00
     */
    public function getTypeTextAttr($value,$data){

        if(isset($data['type'])&&isset($data['order_id'])){

            switch ($data['type']){

                case 1:

                    $text = '服务订单消费';

                    break;
                case 2:

                    $text = '系统编辑';

                    break;

                case 3:

                    $text = '系统降级';

                    break;

                default:

                    $text = '储值订单消费';

                    break;

            }

            if(in_array($data['type'],[1,4])){

                $model = $data['type']==1?new Order():new BalanceOrder();

                $order_code = $model->where(['id'=>$data['order_id']])->value('order_code');

                $text .= "【订单号:$order_code".'】';

            }
            return $text;

        }

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:04
     * @功能说明:添加
     */
    public function dataAdd($data){

        $data['create_time'] = time();

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
    public function dataList($dis,$page){

        $data = $this->where($dis)->order('create_time desc,id desc')->paginate($page)->toArray();

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


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-10 14:21
     * @功能说明:添加记录
     */
    public function addRecord($growth,$is_add,$user,$type=1,$order_id=0,$create_user=0){

        $insert = [

            'uniacid' => $user['uniacid'],

            'user_id' => $user['id'],

            'is_add'  => $is_add,

            'growth'  => $growth,

            'order_id'=> $order_id,

            'type'    => $type,

            'create_user' => $create_user,

            'before_growth'=> $user['growth'],

            'after_growth'=> $is_add==1?$user['growth']+$growth:$user['growth']-$growth,
        ];

        $res = $this->dataAdd($insert);

        $id = $this->getLastInsID();

        $user_model = new User();

        if($is_add==1){

            $user_model->where(['id'=>$user['id']])->update(['growth'=>Db::Raw("growth+$growth")]);
        }else{

            $user_model->where(['id'=>$user['id']])->update(['growth'=>Db::Raw("growth-$growth")]);
        }

        return $id;
    }








}