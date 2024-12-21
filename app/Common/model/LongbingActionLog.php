<?php

namespace app\Common\model;
use app\BaseModel;

/**
 * @author shuixian
 * @DataTime: 2019/12/9 15:22
 * Class LongbingActionLog
 * @package app\Common\model
 */
class LongbingActionLog extends BaseModel
{
    //定义表名称
    protected $name = 'longbing_action_log';

    /**
     * 新增日志信息
     *
     * @param $data
     * @return bool
     * @author shuixian
     * @DataTime: 2019/12/9 16:56
     */
    public function addActionLog($data)
    {
        $result = $this->save($data);
        return $result;
    }
}