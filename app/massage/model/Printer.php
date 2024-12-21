<?php
namespace app\massage\model;

use app\BaseModel;
use think\facade\Db;

class Printer extends BaseModel
{
    //定义表名
    protected $name = 'massage_service_printer';



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:04
     * @功能说明:添加
     */
    public function dataAdd($data){

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

        $data = $this->where($dis)->order('id desc')->paginate($page)->toArray();

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
     * @DataTime: 2020-11-11 09:25
     * @功能说明:订单打印
     */
    public function userOrder($id){

        $order_model   = new Order();
        //订单信息
        $order = $order_model->dataInfo(['id'=>$id]);

        $brs = "<BR>";

        $orderInfo = '<CB>'.'订单小票'.'</CB><BR>';

        $orderInfo .= '--------------------------------'.$brs;


        $orderInfo .= '服务/数量/价格'.$brs;

        $orderInfo .= '--------------------------------'.$brs;


        foreach ($order['order_goods'] as $v) {

            $v['goods_name'] = mb_convert_encoding($v['goods_name'], "UTF-8", "auto");

            $orderInfo .= $v['goods_name'].$brs;

            $orderInfo .= '  X'.$v['num'].'               '.round($v['true_price']*$v['num'],2).'元'.$brs;
//            $orderInfo .=  round($v['true_price']*$v['num'],2).'元'.$brs;

            $orderInfo .= ''.$brs;

            $orderInfo .= ''.$brs;

        }


        $coach_model = new Coach();

        $address_model = new OrderAddress();

        $coach_name  = $coach_model->where(['id'=>$order['coach_id']])->value('coach_name');

        $user_name   = $address_model->where(['order_id'=>$order['id']])->value('user_name');

        $orderInfo .= '预约技师:'.$coach_name.$brs;

        $orderInfo .= '下单人:'.$user_name.$brs;

        $time = date('Y-m-d H:i',$order['start_time']).'-'.date('Y-m-d H:i',$order['end_time']);

        $orderInfo .= '预约时间:'.$time.$brs;

        $orderInfo .= '预约金额:'.round($order['pay_price'],2).$brs;

        $orderInfo .= '付款时间:'.date('Y-m-d H:i',$order['pay_time']).$brs;

        return $orderInfo;

    }





    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-11-11 10:20
     * @功能说明:
     */
    public function storeOrderPrinter($uniacid,$orderInfo,$aotu=1){

        $dis = [

            'uniacid' => $uniacid,

            'status'   => 1,
        ];

        if($aotu==1){

            $dis['auto'] =1;
        }

        $printer_config = $this->where($dis)->select()->toArray();

        if(!empty($printer_config)){

            foreach ($printer_config as $value){

                $value['ukey'] = $value['api_key'];

                $value['sn']   = $value['printer_key'];
                //用户小票
                $res = \longbingcore\printer\Printer::FeiePrintMsg($value,$orderInfo,'Open_printMsg',$value['user_ticket_num']);

            }

        }

        return true;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-11-12 09:57
     * @功能说明:打印
     */
    public function printer($id,$aotu=1){

        $user_order    = $this->userOrder($id);

        $order_model = new Order();

        $uniacid = $order_model->where(['id'=>$id])->value('uniacid');
        //打印
        $res = $this->storeOrderPrinter($uniacid,$user_order,$aotu);

        return $res;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-11-12 10:22
     * @功能说明:订单取消打印机通知
     */
    public function cancelOrderPrinter(){



    }








}