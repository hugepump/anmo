<?php


namespace app\agent\model;


use app\BaseModel;
use think\facade\Db;
use think\facade\Env;

class Cardauth2DefaultModel extends BaseModel
{

    protected $name = 'longbing_cardauth2_default';
    public function getinfo(){
        $data = $this->order('id','desc')->select()->toArray();
        if($data){
            $data = $data[0];
        }
        return $data;
    }
    public function is_table(){
        $prefix = config('database.connections.mysql.prefix');
        $table  = $prefix.$this->name;
        $res    = Db::query('SHOW TABLES LIKE '."'".$table."'");
        return $res;
    }


    /**
     * 获得配置值
     *
     * @param string|\think\model\concern\string $key
     * @return int|mixed
     * @author shuixian
     * @DataTime: 2020/1/2 22:51
     */
    public function getDefaultValue($key ,$defualt = -1 ){

        $defaultConfig = $this->getinfo();
        $number = isset($defaultConfig[$key]) ? $defaultConfig[$key] : $defualt ;
        return  $number;
    }


    /**
     * 获得名片数量默认配置
     *
     * @return array
     * @author shuixian
     * @DataTime: 2020/1/2 22:51
     */
    public  function  getCardNumber(){

        return $this->getDefaultValue('card_number');
    }

    /**
     *  获取短信群发默认配置
     *
     * @return array
     * @author shuixian
     * @DataTime: 2020/1/2 22:53
     */
    public function getSendMsg(){
        return $this->getDefaultValue('send_switch' , 0 );
    }
}