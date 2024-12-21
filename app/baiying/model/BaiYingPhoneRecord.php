<?php
/**
 * Created by PhpStorm
 * User: shurong
 * Date: 2024/8/13
 * Time: 15:49
 * docs:
 */

namespace app\baiying\model;

use app\BaseModel;
use app\massage\model\NoPayRecord;
use longbingcore\wxcore\BaiYing;
use think\facade\Db;

class BaiYingPhoneRecord extends BaseModel
{
    protected $name = 'massage_bai_ying_phone_record';

    /**
     * @Desc: 自动拨打
     * @param $id
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/23 15:26
     */
    public static function auto($uniacid)
    {

        $key = 'by_auto_phone_' . $uniacid;

        incCache($key, 1, $uniacid);

        $value = getCache($key, $uniacid);

        if ($value != 1) {

            decCache($key, 1, $uniacid);

            return false;
        }

        $by = BaiYingConfig::getInfo(['uniacid' => $uniacid]);

        if ($by['phone_type'] != 2) {

            decCache($key, 1, $uniacid);

            return false;
        }

        $where = [
            ['a.uniacid', '=', $uniacid],

            ['b.phone', '<>', ''],

            ['a.status', '=', 1],

            ['a.create_time', '>', 1730886289],

            ['a.by_status', '=', -1],
        ];

        $phone = NoPayRecord::alias('a')
            ->field('a.id,b.nickName as user_name,b.phone as mobile,a.uniacid')
            ->join('massage_service_user_list b', 'a.user_id = b.id')
            ->join('massage_service_coach_list c', 'a.coach_id = c.id')
            ->where($where)
            ->group('a.id')
            ->select()
            ->toArray();

        if (empty($phone)) {

            decCache($key, 1, $uniacid);

            return false;
        }

        //当日已拨打过
        $start_time = strtotime(date('Y-m-d 00:00:00'));

        $end_time = strtotime(date('Y-m-d 23:59:59'));

        foreach ($phone as $k => $item) {

            $res = self::where([['mobile', '=', $item['mobile']], ['create_time', 'between', "$start_time,$end_time"]])->order('create_time asc')->find();

            if ($res) {
                //当天已拨打，修改状态
                $no = NoPayRecord::where('id', $res['order_id'])->find();

                NoPayRecord::where([['user_id', '=', $no['user_id']], ['create_time', 'between', "$start_time,$end_time"]])->update(['by_status' => $no['by_status']]);

                unset($phone[$k]);
            }
        }

        if (empty($phone)) {

            decCache($key, 1, $uniacid);

            return false;
        }

        foreach ($phone as &$item) {

            $item['order_id'] = $item['id'];

            unset($item['id']);
        }

        $baiYing = new BaiYing($uniacid);

        $res = $baiYing->water($phone, 2);

        if (isset($res['code'])) {

            decCache($key, 1, $uniacid);

            return false;
        }

        $ids = array_column($phone, 'order_id');

        NoPayRecord::where('id', 'in', $ids)->update(['by_status' => 100]);

        decCache($key, 1, $uniacid);

        return true;
    }
}