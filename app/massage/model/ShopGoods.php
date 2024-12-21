<?php


namespace app\massage\model;


use app\BaseModel;

class ShopGoods extends BaseModel
{
    protected $name = 'massage_service_shop_goods';

    /**
     * 获取单条数据
     * @param $where
     * @param string $field
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function getInfo($where, $field = '')
    {
        return self::where($where)->field($field)->find();
    }

    /**
     * 获取列表
     * @param $where
     * @param int $page
     * @return array
     * @throws \think\db\exception\DbException
     */
    public static function getList($where, $page = 10)
    {
        return self::where($where)
            ->field('name,carte,cover,price,sort,status,id,create_time')
            ->order('sort desc,create_time desc')
            ->paginate($page)
            ->each(function ($item) {
                $item['create_time'] = date('Y-m-d H:i:s', $item['create_time']);
                $carte = ShopCarte::where('id', 'in', explode(',', $item['carte']))->column('name');
                $item['carte'] = implode(',', $carte);
                return $item;
            })->toArray();
    }
}