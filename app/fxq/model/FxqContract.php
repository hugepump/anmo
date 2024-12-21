<?php
/**
 * Created by PhpStorm
 * User: shurong(龙兵科技)
 * Date: 2024/10/18
 * Time: 11:03
 * docs:
 */

namespace app\fxq\model;

use app\BaseModel;

class FxqContract extends BaseModel
{
    protected $name = 'massage_fxq_contract_list';

    protected $append = [

        'file_list'
    ];

    public function getFileListAttr($value, $data)
    {
        if (isset($data['id'])) {

            return FxqContractFile::where(['contract_id' => $data['id']])->field('id,contract_id,contract_no,type,company_view_contract,coach_view_contract,company_view_contract_img,coach_view_contract_img')->select()->toArray();
        }
    }

    /**
     * @Desc: 获取合同编号
     * @param $uniacid
     * @return int|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/18 11:44
     */
    public static function getNo($uniacid)
    {

        $info = self::where(['uniacid' => $uniacid, 'date' => date('Y-m-d')])->order('create_time desc')->find();

        $num = '0001';

        if ($info) {

            $num = $info['number'];

            $num = (int)$num + 1;

            $num = str_pad($num, 4, '0', STR_PAD_LEFT);
        }

        return $num;
    }

    /**
     * @Desc: 插入
     * @param $insert
     * @return int|string
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/18 11:59
     */
    public static function add($insert)
    {
        $insert['create_time'] = time();

        return self::insertGetId($insert);
    }

    /**
     * @Desc: 获取列表
     * @param $where
     * @param $limit
     * @return mixed
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/18 14:33
     */
    public static function getList($where, $limit = 10)
    {
        $data = self::alias('a')
            ->field('a.id,a.admin_id,a.coach_id,a.company_name,a.status,a.create_time,a.start_time,a.end_time,b.coach_name,ifnull(c.user_name,"") as agent_name,a.id_code')
            ->where($where)
            ->leftJoin('massage_service_coach_list b', 'a.coach_id = b.id')
            ->leftJoin('massage_agent_apply c', 'a.admin_id = c.id')
            ->order('a.create_time desc')
            ->paginate($limit)
            ->toArray();

        return $data;
    }

    public static function getInfo($where, $field = '*')
    {
        return self::where($where)->field($field)->order('create_time desc')->find();
    }

    /**
     * @Desc: 编辑
     * @param $where
     * @param $update
     * @return FxqContract
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/21 15:10
     */
    public static function edit($where, $update)
    {
        return self::where($where)->update($update);
    }
}