<?php
namespace app\massage\model;

use app\BaseModel;
use think\facade\Db;

class ChannelScanQr extends BaseModel
{
    //定义表名 添加扫码记录 type 1渠道码 2分销码 3经纪人邀请技-师码 4代理商邀请技-师 5代理商邀请业务员 6代理商邀请渠道商 7技-师邀请充值 8业务员邀请渠道商 9分销员邀请分销员 10代理商邀请分销员
    protected $name = 'massage_channel_scan_code_record';

//添加扫码记录 type 1渠道码 2分销码 3经纪人邀请技-师码 4代理商邀请技-师 5代理商邀请业务员 6代理商邀请渠道商 7技-师邀请充值 8业务员邀请渠道商 9渠道商默认码 10分销员邀请分销员 11代理商邀请分销员
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
     * @DataTime: 2024-03-06 16:11
     * @功能说明:查询是否是扫码注册
     */
    public function getQrRegister($open_id,$arr=[]){

        $qr_find = $this->where(['open_id'=>$open_id,'user_id'=>0])->where('type','<>',0)->order('id desc')->find();

        if(!empty($qr_find)){

            $this->dataUpdate(['id'=>$qr_find['id']],['is_new'=>1]);

            $arr['is_qr'] = 1;

            $arr['source_type'] = $qr_find['type'];

            $arr['admin_id'] = !empty($qr_find['admin_id'])?$qr_find['admin_id']:$this->getQrAdminId($qr_find);
        }

        return $arr;
    }


    /**
     * @param $qr_find
     * @功能说明:获取扫码角色的代理商
     * @author chenniang
     * @DataTime: 2024-06-07 15:29
     */
    public function getQrAdminId($qr_find){

        $list = [

            [

                'type' => 1,

                'table'=> 'massage_channel_qr',

                'title'=> 'admin_id',
            ],
            [

                'type' => 2,

                'table'=> 'massage_distribution_list',

                'title'=> 'admin_id',
            ],
            [

                'type' => 4,

                'table'=> 'shequshop_school_admin',

                'title'=> 'id',
            ],
            [
                'type' => 5,

                'table'=> 'shequshop_school_admin',

                'title'=> 'id',
            ],
            [
                'type' => 6,

                'table'=> 'shequshop_school_admin',

                'title'=> 'id',
            ],
            [
                'type' => 7,

                'table'=> 'massage_service_coach_list',

                'title'=> 'admin_id',
            ],
            [
                'type' => 8,

                'table'=> 'massage_salesman_list',

                'title'=> 'admin_id',
            ],
            [
                'type' => 9,

                'table'=> 'massage_channel_list',

                'title'=> 'admin_id',
            ],
            [
                'type' => 10,

                'table'=> 'massage_distribution_list',

                'title'=> 'admin_id',
            ],
            [
                'type' => 11,

                'table'=> 'shequshop_school_admin',

                'title'=> 'id',
            ],
            [
                'type' => 12,

                'table'=> 'shequshop_school_admin',

                'title'=> 'id',
            ],
            [
                'type' => 13,

                'table'=> 'massage_service_coach_list',

                'title'=> 'admin_id',
            ],
            [
                'type' => 14,

                'table'=> 'massage_service_coach_list',

                'title'=> 'admin_id',
            ]
        ];

        $admin_id = 0;

        foreach ($list as $v){

            if($v['type']==$qr_find['type']){

                if($v['type']==2){

                    $admin_id = Db::name($v['table'])->where(['user_id'=>$qr_find['qr_id'],'status'=>2])->value($v['title']);

                }else{

                    $admin_id = Db::name($v['table'])->where(['id'=>$qr_find['qr_id']])->value($v['title']);
                }

                $admin_id = !empty($admin_id)?$admin_id:0;
            }
        }

        return $admin_id;
    }


    /**
     * @param $user_id
     * @功能说明:获取扫码的名称
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-03-12 11:59
     */
    //定义表名 添加扫码记录 type 1渠道码 2分销码 3经纪人邀请技-师码 4代理商邀请技-师 5代理商邀请业务员 6代理商邀请渠道商 7技-师邀请充值 8业务员邀请渠道商 9分销员邀请分销员 10代理商邀请分销员
    public function getChannelQrTitle($user_id){

        $dis = [

            'a.user_id'=> $user_id,

            'a.is_new' => 1,

            'a.type'   => 1
        ];

        $title = $this->alias('a')
                 ->join('massage_channel_qr b','a.qr_id = b.id')
                 ->where($dis)
                 ->group('a.id')
                 ->order('a.id desc')
                 ->value('b.title');

        return $title;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-04-30 18:27
     * @功能说明:获取码来源
     */
    //定义表名 添加扫码记录 type 1渠道码 2分销码 3经纪人邀请技-师码 4代理商邀请技-师 5代理商邀请业务员 6代理商邀请渠道商 7技-师邀请充值 8业务员邀请渠道商 9分销员邀请分销员 10代理商邀请分销员
    public function getQrTitle($user_id){

        $dis = [

            'user_id'=> $user_id,

            'is_new' => 1,
        ];

        $data = $this->where($dis)->field('type,qr_id')->order('id desc')->find();

        $arr = [

            [

                'type' => 1,

                'table'=> 'massage_channel_qr',

                'title'=> 'title',
            ],
            [

                'type' => 2,

                'table'=> 'massage_service_user_list',

                'title'=> 'nickName'
            ],
            [

                'type' => 3,

                'table'=> 'massage_coach_broker_list',

                'title'=> 'user_name',

                'filed'=> 'user_id'
            ],
            [

                'type' => 4,

                'table'=> 'shequshop_school_admin',

                'title'=> 'agent_name',
            ],
            [
                'type' => 5,

                'table'=> 'shequshop_school_admin',

                'title'=> 'agent_name',
            ],
            [
                'type' => 6,

                'table'=> 'shequshop_school_admin',

                'title'=> 'agent_name',
            ],
            [
                'type' => 7,

                'table'=> 'massage_service_coach_list',

                'title'=> 'coach_name',
            ],
            [
                'type' => 8,

                'table'=> 'massage_salesman_list',

                'title'=> 'user_name',
            ],
            [
                'type' => 9,

                'table'=> 'massage_channel_list',

                'title'=> 'user_name',
            ],
            [
                'type' => 10,

                'table'=> 'massage_distribution_list',

                'title'=> 'user_name',
            ],
            [
                'type' => 11,

                'table'=> 'shequshop_school_admin',

                'title'=> 'agent_name',
            ],
            [
                'type' => 12,

                'table'=> 'shequshop_school_admin',

                'title'=> 'agent_name',
            ]

        ];

        if(!empty($data)){

            foreach ($arr as $v){

                if($v['type']==$data['type']){

                    $filed = isset($v['filed'])?$v['filed']:'id';

                    $dis = [

                        $filed => $data['qr_id']
                    ];

                    if(isset($v['filed'])){

                        $dis['status'] = 2;
                    }

                    $title =  Db::name($v['table'])->where($dis)->value($v['title']);

                    if($v['type']==1){

                        $channel_id = Db::name($v['table'])->where([$filed=>$data['qr_id']])->value('channel_id');

                        $channel_model = new ChannelList();

                        $channel_title = $channel_model->where(['id'=>$channel_id])->value('user_name');

                        if(!empty($channel_title)){

                            $title = $channel_title.'-'.$title;
                        }
                    }
                    return $title;
                }
            }
        }

        return '';
    }












}