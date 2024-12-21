<?php
namespace app\massage\model;

use app\BaseModel;
use think\facade\Db;

class NoticeList extends BaseModel
{
    //定义表名
    protected $name = 'massage_service_notice_list';


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-16 10:44
     * @功能说明:
     */
    public function getCreateTimeAttr($value,$data){

        if(!empty($value)){

            return date('Y-m-d H:i:s',$value);

        }

    }
    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:04
     * @功能说明:添加
     */
    public function dataAdd($uniacid,$order_id,$type=1,$admin_id=0){

        $data['uniacid']  = $uniacid;

        $data['order_id'] = $order_id;

        $data['type']     = $type;

        $data['admin_id'] = $admin_id;

        $data['is_pop']   = 0;

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

        return !empty($data)?$data->toArray():[];

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-20 16:09
     * @功能说明:手机端订单操作页面的通知
     */
    public function indexOrderNoticeV2($data,$uniacid,$admin_id,$node=[]){
        //通知
        $arr = [1=>'order_id',2=>'refund_id',3=>'refuse_id',
        //    4=>'unaccepted_orders',5=>'lat_order',6=>'jump_order'
        ];

        $dis = [

            'uniacid' => $uniacid,

            'have_look'=> 0
        ];

        if(!empty($admin_id)&&!is_array($admin_id)){

            $dis['admin_id'] = $admin_id;

        }

        $where = [];

        if(is_array($admin_id)){

            $where[] = ['admin_id','in',$admin_id];

        }

        foreach ($arr as $k=>$value){

            $dis['type'] = $k;

            $wheres = [];

            if($k==3&&!empty($node)&&!in_array('shopRefuseOrder',$node)){

                $wheres[] = ['id','=',-1];

            }
            //判断权限
            if(in_array($k,[1,4,5,6])&&!empty($node)){

                $order_arr = [];

                $order_model = new Order();

                if(in_array('shopOrder',$node)){

                    $order_id = $order_model->where(['is_add'=>0])->column('id');

                    $order_arr = array_merge($order_id,$order_arr);

                }

                if(in_array('shopBellOrder',$node)){

                    $order_id = $order_model->where(['is_add'=>1])->column('id');

                    $order_arr = array_merge($order_id,$order_arr);

                }

                $wheres[] = ['order_id','in',$order_arr];

            }
            //判断权限
            if($k==2&&!empty($node)){

                $order_arr = [];

                $order_model = new RefundOrder();

                if(in_array('shopRefund',$node)){

                    $order_id = $order_model->where(['is_add'=>0])->column('id');

                    $order_arr = array_merge($order_id,$order_arr);

                }

                if(in_array('shopBellRefund',$node)){

                    $order_id = $order_model->where(['is_add'=>1])->column('id');

                    $order_arr = array_merge($order_id,$order_arr);

                }

                $wheres[] = ['order_id','in',$order_arr];

            }

            $data['notice'][$value] = $this->where($dis)->where($where)->where($wheres)->order('id desc')->field('id,order_id,type')->find();

            $data['notice'][$value] = !empty($data['notice'][$value])?$data['notice'][$value]:[];
        }

        return $data;

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-20 16:09
     * @功能说明:后端订单操作页面的通知
     */
    public function adminOrderNotice($uniacid,$node,$have_look=1){

        $notice_arr = [];
        //通知
        $arr = [1=>'order_id',2=>'refund_id',3=>'refuse_id',4=>'unaccepted_orders',5=>'lat_order',6=>'jump_order'];

        $dis = [

            'uniacid'  => $uniacid,

        ];
        //查未读
        if($have_look==0){

            $dis['have_look'] = 0;
        }

        $where = [];

        foreach ($arr as $k=>$value){

            $dis['type'] = $k;

            $wheres = [];

            if($k==3&&!empty($node)&&!in_array('ShopRefuseOrder',$node)){

                $wheres[] = ['id','=',-1];

            }
            //判断权限
            if(in_array($k,[1,4,5,6])&&!empty($node)){

                $order_arr = [];

                $order_model = new Order();

                if(in_array('ShopOrder',$node)){

                    $order_id = $order_model->where(['is_add'=>0])->column('id');

                    $order_arr = array_merge($order_id,$order_arr);

                }

                if(in_array('ShopBellOrder',$node)){

                    $order_id = $order_model->where(['is_add'=>1])->column('id');

                    $order_arr = array_merge($order_id,$order_arr);

                }

                $wheres[] = ['order_id','in',$order_arr];

            }
            //判断权限
            if($k==2&&!empty($node)){

                $order_arr = [];

                $order_model = new RefundOrder();

                if(in_array('ShopRefund',$node)){

                    $order_id = $order_model->where(['is_add'=>0])->column('id');

                    $order_arr = array_merge($order_id,$order_arr);

                }

                if(in_array('ShopBellRefund',$node)){

                    $order_id = $order_model->where(['is_add'=>1])->column('id');

                    $order_arr = array_merge($order_id,$order_arr);

                }

                $wheres[] = ['order_id','in',$order_arr];

            }

            $notice_id = $this->where($dis)->where($where)->where($wheres)->column('id');

            $notice_arr = array_merge($notice_arr,$notice_id);
        }

        return $notice_arr;

    }


    /**
     * @param $dis
     * @param int $is_admin
     * @param array $node
     * @param int $type
     * @param int $limit
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-09-06 16:38
     */
    public function noticeData($dis,$is_admin = 1,$node=[],$type=1,$limit=10,$have_order_notice=1){

        $where = [];

        if(in_array($is_admin,[2,3])){

            $where[] = [

                ['a.type','=',-1]
            ];
            //有拒单
            if(in_array('ShopRefuseOrder',$node)){

                $where[] = [

                    ['a.type','=',3]
                ];
            }
            //
            if(in_array('ShopOrder',$node)){

                $where[] = [

                    ['a.type','in',[1,4,5,6]],

                    ['b.is_add','=',0]
                ];
            }
            //加钟
            if(in_array('ShopBellOrder',$node)){

                $where[] = [

                    ['a.type','in',[1,4,5,6]],

                    ['b.is_add','=',1]
                ];
            }
            //
            if(in_array('ShopRefund',$node)){

                $where[] = [

                    ['a.type','=',2],

                    ['c.is_add','=',0]
                ];
            }
            //加钟退单
            if(in_array('ShopBellRefund',$node)){

                $where[] = [

                    ['a.type','=',2],

                    ['c.is_add','=',1]
                ];
            }
        }

        if($have_order_notice==0){

            $dis[] = ['a.type','<>',1];
        }

        if($type==1){
            //列表
            $data = $this->alias('a')
                ->join('massage_service_order_list b','a.order_id = b.id','left')
                ->join('massage_service_refund_order c','a.order_id = c.id','left')
                ->where($dis)
                ->where(function ($query) use ($where){
                    $query->whereOr($where);
                })
                ->field('a.*,b.is_add,c.is_add as refund_add')
                ->group('a.id')
                ->order('a.id desc')
                ->paginate($limit)
                ->toArray();

        }elseif($type==2){

            $data = $this->alias('a')
                ->join('massage_service_order_list b','a.order_id = b.id','left')
                ->join('massage_service_refund_order c','a.order_id = c.id','left')
                ->where($dis)
                ->where(function ($query) use ($where){
                    $query->whereOr($where);
                })
                ->field('a.*,b.is_add,c.is_add as refund_add')
                ->group('a.id')
                ->order('a.id desc')
                ->find();

        }else{

            $data = $this->alias('a')
                ->join('massage_service_order_list b','a.order_id = b.id','left')
                ->join('massage_service_refund_order c','a.order_id = c.id','left')
                ->where($dis)
                ->where(function ($query) use ($where){
                    $query->whereOr($where);
                })
                ->field('a.*,b.is_add,c.is_add as refund_add')
                ->group('a.id')
                ->count();
        }

        return $data;
    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-20 16:09
     * @功能说明:手机端订单操作页面的通知
     */
    public function indexOrderNotice($data,$uniacid,$admin_id,$node=[]){
        //通知
        $arr = [1=>'order_id',2=>'refund_id',3=>'refuse_id',
            //    4=>'unaccepted_orders',5=>'lat_order',6=>'jump_order'
        ];

        $dis[] = ['a.uniacid','=',$uniacid];

        $dis[] = ['a.have_look','=',0];

        if(!empty($admin_id)&&!is_array($admin_id)){

            $dis[] = ['a.admin_id','=',$admin_id];

        }

        if(is_array($admin_id)){

            $dis[] = ['a.admin_id','in',$admin_id];

        }

        foreach ($arr as $k=>$value){

            $where = $dis;

            $where[] = ['a.type','=',$k];

            $data['notice'][$value] = $this->indexNoticeData($where,$node);

            $data['notice'][$value] = !empty($data['notice'][$value])?$data['notice'][$value]:[];
        }

        return $data;

    }



    /**
     * @param $dis
     * @param int $is_admin
     * @param array $node
     * @param int $type
     * @param int $limit
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-09-06 16:38
     */
    public function indexNoticeData($dis,$node=[]){

        $where = [];

        if(!empty($node)){

            $where[] = [

                ['a.type','=',-1]
            ];
            //有拒单
            if(in_array('shopRefuseOrder',$node)){

                $where[] = [

                    ['a.type','=',3]
                ];
            }
            //
            if(in_array('shopOrder',$node)){

                $where[] = [

                    ['a.type','in',[1,4,5,6]],

                    ['b.is_add','=',0]
                ];
            }
            //加钟
            if(in_array('shopBellOrder',$node)){

                $where[] = [

                    ['a.type','in',[1,4,5,6]],

                    ['b.is_add','=',1]
                ];
            }
            //
            if(in_array('shopRefund',$node)){

                $where[] = [

                    ['a.type','=',2],

                    ['c.is_add','=',0]
                ];
            }
            //加钟退单
            if(in_array('shopBellRefund',$node)){

                $where[] = [

                    ['a.type','=',2],

                    ['c.is_add','=',1]
                ];
            }
        }

        $data = $this->alias('a')
            ->join('massage_service_order_list b','a.order_id = b.id','left')
            ->join('massage_service_refund_order c','a.order_id = c.id','left')
            ->where($dis)
            ->where(function ($query) use ($where){
                $query->whereOr($where);
            })
            ->field('a.*,b.is_add,c.is_add as refund_add')
            ->group('a.id')
            ->order('a.id desc')
            ->find();

        return $data;
    }


}