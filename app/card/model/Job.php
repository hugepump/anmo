<?php
namespace app\card\model;

use app\BaseModel;


class Job extends BaseModel
{
    //定义表名
    protected $name = 'longbing_card_job';


    protected static function init ()
    {
        //TODO:初始化内容
    }
    
   /**
     * @Purpose: 创建职位
     *
     * @Author: yangqi
     *
     * @Return: boolean
     */
    
    public function createRow($data)
    {
    	$data['create_time'] = time();
    	$result = $this->save($data);
    	return !empty($result);
    }

}