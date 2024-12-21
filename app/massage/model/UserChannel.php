<?php
namespace app\massage\model;

use app\BaseModel;
use longbingcore\permissions\AdminMenu;
use think\facade\Db;

class UserChannel extends BaseModel
{
    //定义表名
    protected $name = 'massage_user_channel_bind';




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

        $data = $this->where($dis)->order('status desc,id desc')->paginate($page)->toArray();

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
     * @DataTime: 2023-05-29 18:36
     * @功能说明:获取用户绑定的渠道商
     */
    public function getChannelId($user_id){

        $user_model = new User();

        $user = $user_model->dataInfo(['id'=>$user_id]);

        if(empty($user)){

            return [];
        }

        $openid = $user['web_openid'];

        $where = [

            'user_id' => $user_id,

            'open_id' => $openid,
        ];

        if(!empty($user['unionid'])){

            $where['unionid'] = $user['unionid'];
        }
        //是否设置的永久绑定
        $auth = AdminMenu::getAuthList((int)$user['uniacid'],['channelforeverbind']);

        $channel_bind_forever = $auth['channelforeverbind'];

        if($channel_bind_forever==true){

            $data = $this->where(function ($query) use ($where){
                $query->whereOr($where);
            })->find();

        }else{

            $data = $this->where(function ($query) use ($where){
                $query->whereOr($where);
            })->where('over_time','>',time())->find();
        }

        if(empty($data)){

            return [];

        }else{

            $data = $data->toArray();
        }
        //扫的渠道码
        if(!empty($data['channel_qr_id'])){

            $qr_model = new ChannelQr();

            $qr = $qr_model->dataInfo(['id'=>$data['channel_qr_id']]);
            //非永久绑定
            if($channel_bind_forever!=true){

                if(empty($qr)||$qr['status']!=1){

                    return [];
                }

                $data['channel_id'] = $qr['channel_id'];

            }else{
                //永久绑定
                if($qr['status']!=1){

                    $data['channel_qr_id'] = 0;
                }

                if(!empty($qr['channel_id'])){

                    $data['channel_id'] = $qr['channel_id'];

                }else{

                    $data['channel_qr_id'] = 0;
                }
            }
        }
        //扫的渠道商默认码
        if(!empty($data['channel_id'])){

            $channel_model = new ChannelList();

            $channel = $channel_model->dataInfo(['id'=>$data['channel_id'],'status'=>2]);

            if(empty($channel)){

                if(!empty($data['channel_qr_id'])){

                    $data['channel_id'] = 0;

                }else{

                    $data = [];
                }
            }
        }
        return $data;
    }









}