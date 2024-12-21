<?php
/**
 * Notes:
 * User: chenniang(龙兵科技)
 * Date: 2019-11-14
 * Time: 18:32
 * ${PARAM_DOC}
 * @return ${TYPE_HINT}
 * ${THROWS_DOC}
 */


$tabbar_BusinessCard = [
    "key"             => 1,
    "is_show"         => 1,
    "iconPath"        =>"icon-mingpian",
    "selectedIconPath"=> "icon-mingpian1",
    "pageComponents"  => "cardHome",
    "name"            => "名片",
    "url"             => "/pages/user/home",
    "url_out"         => "",
    "url_jump_way"    => 0
];

$page_BusinessCard = [

];


return [

    'BusinessCard_tabbar' => $tabbar_BusinessCard,
    'BusinessCard_page'   => $page_BusinessCard

];