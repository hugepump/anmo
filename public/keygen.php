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
function myk($hasher,$randstr)
{

    $salt = "ckmkc";

    $salt = hash("sha3-512","cndnvdfnv". $salt);

    return hash($hasher,$randstr . $salt);
}