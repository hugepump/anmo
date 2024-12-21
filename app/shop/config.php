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


$tabbar_shop = [
    "key"             => 20,
    "is_show"         => 1,
    "iconPath"        =>"icon-shangcheng1",
    "selectedIconPath"=> "icon-shangcheng",
    "pageComponents"  => "shopHome",
    "name"            => "商城",
    "url"             => "/pages/user/home",
    "url_out"         => "",
    "url_jump_way"    => 0
];

$page_shop = [

];


return [

    'Malls_tabbar' => $tabbar_shop,
    'Malls_page'   => $page_shop

];