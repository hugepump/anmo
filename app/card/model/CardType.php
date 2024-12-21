<?php

namespace app\card\model;

use app\BaseModel;
use think\Model;


class CardType extends BaseModel
{
    //定义表名
    protected $name = 'longbing_card_card_type';


    protected static function init ()
    {
        //TODO:初始化内容
    }


    /**
     * @Purpose: 初始化名片样式
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function initCardType ( $uniacid )
    {
        $data   = [ [ 'card_type' => 'cardType1', 'img' => 'https://retail.xiaochengxucms.com/1.png', 'uniacid' => $uniacid ],
            [ 'card_type' => 'cardType4', 'img' => 'https://retail.xiaochengxucms.com/2.png', 'uniacid' => $uniacid ],
            [ 'card_type' => 'cardType2', 'img' => 'https://retail.xiaochengxucms.com/4.png', 'uniacid' => $uniacid ],];
        $result = self::createRows( $data );
        return $result->toArray();
    }

    /**
     * @Purpose: 初始化名片样式
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function getCardTypeList ( $uniacid, $type = '' )
    {
        $list = self::where( [ [ 'uniacid', '=', $uniacid ], [ 'status', '=', 1 ] ] )
                    ->select()
                    ->toArray();

        foreach ( $list as $index => $item )
        {
            $list[$index]['selected'] = 0;

            if ($item['card_type'] == $type)
            {
                $list[$index]['selected'] = 1;
            }
        }
        return $list;
    }
}