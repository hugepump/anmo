<?php


namespace app\massage\model;


use app\BaseModel;

class Feedback extends BaseModel
{
    protected $name = 'massage_service_coach_feedback';

    /**
     * 获取列表
     * @param $where
     * @param $page
     * @return mixed
     */
    public static function getList($where, $page = 10)
    {
        $data =  self::where($where)
            ->alias('a')
            ->field('a.uniacid,a.type,a.id,a.type_name,a.create_time,a.order_code,a.content,a.images,a.video_url,a.status,a.reply_content,a.reply_date,if(a.type=1,b.nickName,c.coach_name) as coach_name,if(a.type=1,b.phone,c.mobile) as mobile')
            ->leftJoin('massage_service_user_list b', 'a.coach_id=b.id AND a.type=1')
            ->leftJoin('massage_service_coach_list c', 'a.true_coach_id=c.id AND a.type = 2')
            ->order('a.create_time desc')
            ->paginate($page)->each(function ($item) {
                $item['images'] = json_decode($item['images'], true);
                $item['create_time'] = date('Y-m-d H:i:s', $item['create_time']);
                return $item;
            })->toArray();

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                if(numberEncryption($v['uniacid'])==1){

                    $v['mobile'] = substr_replace($v['mobile'], "****", 2,4);
                }
            }
        }
        return $data;
    }

    /**
     * 单条数据
     * @param $where
     * @return mixed
     */
    public static function getInfo($where)
    {
        $data = self::where($where)
            ->alias('a')
            ->field('a.uniacid,a.type,a.id,a.type_name,a.create_time,a.order_code,a.content,a.images,a.video_url,a.status,a.reply_content,a.reply_date,if(a.type=1,b.nickName,c.coach_name) as coach_name,if(a.type=1,b.phone,c.mobile) as mobile,b.phone as mobile,b.id as user_id')
            ->leftJoin('massage_service_user_list b', 'a.coach_id=b.id AND a.type=1')
            ->leftJoin('massage_service_coach_list c', 'a.true_coach_id=c.id AND a.type = 2')
            ->find();
        if ($data) {
            $data['images'] = json_decode($data['images'], true);
            $data['create_time'] = date('Y-m-d H:i:s', $data['create_time']);
        }

        if(numberEncryption($data['uniacid'])==1){

            $data['mobile'] = substr_replace($data['mobile'], "****", 2,4);
        }
        return $data;
    }
}