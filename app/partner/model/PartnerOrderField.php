<?php
/**
 * Created by PhpStorm
 * User: shurong(龙兵科技)
 * Date: 2024/10/28
 * Time: 11:04
 * docs:
 */

namespace app\partner\model;

use app\BaseModel;

class PartnerOrderField extends BaseModel
{
    protected $name = 'massage_partner_order_field';

    /**
     * @Desc: 获取订单自定义字段列表
     * @param $id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/29 15:12
     */
    public static function getListByOrderId($id)
    {
        $data = self::where('order_id', $id)
            ->field('id,name,type,select,is_required,top')
            ->select()
            ->toArray();
        if ($data) {

            foreach ($data as &$item) {

                $item['select'] = empty($item['select']) ? '' : json_decode($item['select'], true);
            }
        }

        return $data;
    }

    /**
     * @Desc: 处理数据
     * @param $field
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/29 17:43
     */
    public static function handle($field)
    {
        $data = self::where('id', 'in', array_column($field, 'id'))->field('id,name,select,type')->order('top desc')->select()->toArray();

        foreach ($data as &$value) {

            foreach ($field as $item) {

                if ($item['id'] == $value['id']) {

                    $value['value'] = $item['value'];
                }
            }
        }

        return $data;
    }
}