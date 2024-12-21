<?php
/**
 * Created by PhpStorm
 * User: shurong(龙兵科技)
 * Date: 2024/10/18
 * Time: 15:51
 * docs:
 */

namespace app\fxq\model;

use app\BaseModel;

class FxqContractFile extends BaseModel
{
    protected $name = 'massage_fxq_contract_file_list';

    public static function edit($where, $update)
    {

        return self::where($where)->update($update);
    }

    public static function getInfo($where)
    {
        return self::where($where)->find();
    }
}