<?php
// +----------------------------------------------------------------------
// | Longbing [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright Chengdu longbing Technology Co., Ltd.
// +----------------------------------------------------------------------
// | Website http://longbing.org/
// +----------------------------------------------------------------------
// | Sales manager: +86-13558882532 / +86-13330887474
// | Technical support: +86-15680635005
// | After-sale service: +86-17361005938
// +----------------------------------------------------------------------

declare(strict_types=1);

namespace longbingcore\tools;


class LongbingArr
{
    /**
     * 根据Key删除素组值
     *
     * @param $arr
     * @param $key   str\array
     * @return mixed
     * @author shuixian
     * @DataTime: 2019/12/25 17:32
     */
    public static function delBykey($arr , $key)
    {


        if(is_array($key)){
            foreach ($key as $k => $v){
                $arr = self::delBykey($arr , $v);
            }
            return $arr ;
        }

        if(!array_key_exists($key, $arr)){
            return $arr;
        }
        $keys = array_keys($arr);
        $index = array_search($key, $keys);
        if($index !== FALSE){
            array_splice($arr, $index, 1);
        }
        return $arr;
    }

    /**
     * 删除多维数组,根据
     *
     * @param $array
     * @param $key
     * @return array
     * @author shuixian
     * @DataTime: 2019/12/25 22:37
     *
     * demo
     *  $details = array(
     *  0 => array("id"=>"1", "name"=>"Mike",    "num"=>"9876543210"),
     *  1 => array("id"=>"2", "name"=>"Carissa", "num"=>"08548596258"),
     *  2 => array("id"=>"1", "name"=>"Mathew",  "num"=>"784581254"),
     *  );
     *   $details = unique_multidim_array($details,'id');
     *   Output will be like this :
     *   $details = array(
     *   0 => array("id"=>"1","name"=>"Mike","num"=>"9876543210"),
     *   1 => array("id"=>"2","name"=>"Carissa","num"=>"08548596258"),
     *   );
     */
    public static function unique_multidim_array($array, $key) {
        $temp_array = array();
        $i = 0;
        $key_array = array();

        foreach($array as $val) {
            if (!in_array($val[$key], $key_array)) {
                $key_array[$i] = $val[$key];
                $temp_array[$i] = $val;
            }
            $i++;
        }
        return $temp_array;
    }


    /**
     * 把所有的一级数组进行合并，主要用于 event 返回数据进行合并
     *
     * @param $array
     * @return mixed
     * @author shuixian
     * @DataTime: 2019/12/26 11:04
     *
     *  demo
     *
     *  $array = [ [0,1,2]  , [3,4] ]] ;
     *  LongbingArr::array_merge($array);
     *  $returnArray = [0,1,2,3,4] ;
     */
    public static function array_merge($array){
        $returnArr = [];
        foreach ($array as $item){
            if(!empty($item)) $returnArr = array_merge($returnArr,$item);
        }
        return $returnArr;
    }

}