<?php
/**
 * Created by PhpStorm.
 * User: shuixian
 * Date: 2019/11/20
 * Time: 18:29
 */

//底部菜单自定义
return [

    [
        //底部菜单编号
        'key'      => 1 ,
        //是否显示
        'is_show'  => 1 ,
        //图标
        'iconPath' => 'icon-mingpian',
        //选中图标样式
        'selectedIconPath' => 'icon-mingpian1',
        //那个页面 英文名称
        'pageComponents'   => 'cardHome',
        //名称
        'name' => '名片',
        'url' => '',
        'url_jump_way' => '0',
        'url_out' => '',
        'is_delete' => false ,
        'bind_compoents'=>[

            'cardCompoent',

            'operate'
        ],
        'bind_links' => [],
        'page'=> []
    ],

];