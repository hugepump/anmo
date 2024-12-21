<?php
/**
 * Created by PhpStorm.
 * User: 方琪
 * Date: 2021/11/29
 * 签名加密算法
 */


class SignUtils
{


    public static function readParams($data,&$string){
        $size = count($data);
        foreach ($data as $key=>$val){
            $size --;
            SignUtils::readChildParams($key,json_encode($val),$string,$size);
        }
        return $string;
    }

   public static function readChildParams($key,$value,&$string,$index){
        if(is_string($key)){
            $string.= $key."=";
        }
        if(SignUtils::startsWith($value,"{")){
            $string.="{";
            SignUtils::readParams(json_decode($value,true),$string);
            $string.="}";
        }elseif (SignUtils::startsWith($value,"[")){
            $string.="[";
            $list  = json_decode($value,true);
            $size = count($list);
            foreach ($list as $list1){
                $size --;
                SignUtils::readChildParams(null,json_encode($list1),$string,$size);
            }
            $string.="]";
        }else{
            //去除双引号
            $string.= str_replace('"', '',json_decode($value,true));
        }
        if($index != 0){
            $string.= "||";
        }
        return $string;
    }

    public static function startsWith($haystack, $needle, $case=true) {
        if ($case){
            return strncasecmp($haystack, $needle, strlen($needle)) == 0 ;
        }else{
            return strncmp($haystack, $needle, strlen($needle)) == 0;
        }
    }

    public static function sortParam(&$param){
        if(is_array($param)){
            ksort($param);
            foreach ($param as &$value){
                SignUtils::sortParam($value);
            }
        }
        return $param;
    }


}