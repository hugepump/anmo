<?php


namespace app\massage\model;


use app\BaseModel;

class ShopCarte extends BaseModel
{
    protected $name = 'massage_service_shop_carte';

    /**
     * 添加分类
     * @param $input
     * @return int|string
     */
    public static function add($input)
    {
        $input['create_time'] = time();
        return self::insert($input);
    }

    /**
     * 单条信息
     * @param $where
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function getInfo($where, $field = '*')
    {
        return self::where($where)->field($field)->find();
    }

    /**
     * 列表
     * @param $where
     * @param int $page
     * @return array
     * @throws \think\db\exception\DbException
     */
    public static function getList($where, $page = 10)
    {
        return self::where($where)
            ->field('id,name,sort,status,create_time')
            ->order('sort desc,create_time desc')
            ->paginate($page)
            ->each(function ($item) {
                $item['create_time'] = date('Y-m-d H:i:s', $item['create_time']);
                return $item;
            })->toArray();
    }

    /**
     * 列表不分页
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function getListNoPage($where)
    {
        return self::where($where)
            ->field('id,name')
            ->order('sort desc')
            ->select();
    }
}