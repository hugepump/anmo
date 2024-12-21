<?php

namespace app\card\model;

use app\BaseModel;
use think\Model;


class CardTags extends BaseModel
{
    //定义表名
    protected $name = 'longbing_card_tags';


    protected static function init ()
    {
        //TODO:初始化内容
    }

    /**
     * @Purpose: 名片的标签列表
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function cardTagList ( $staff_id, $client_id, $uniacid )
    {
        $data = self::where( [ [ 'user_id', '=', $staff_id ], [ 'uniacid', '=', $uniacid ], [ 'status', '=', 1 ] ] )
                    ->select()
                    ->toArray();

        foreach ( $data as $index => $item )
        {
            $data[ $index ][ 'thumbed' ] = 0;
            $check                       = CardUserTags::where( [ [ 'tag_id', '=', $item[ 'id' ] ],
                                                                    [ 'user_id', '=', $client_id ], [ 'uniacid', '=', $uniacid ],
                                                                    [ 'status', '=', 1 ] ]
            )
                                                       ->count();

            if ($check)
            {
                $data[ $index ][ 'thumbed' ] = 1;
            }
        }

        return $data;
    }
	
	public function getTag($filter)
	{
		$result = $this->where($filter)->find();
		if(!empty($result)) $result = $result->toArray();
		return $result;
	}
	
	public function getTagCount($filter)
	{
		$count = $this->where($filter)->count();
		return $count;
	}
}