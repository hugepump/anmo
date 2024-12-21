<?php
// +----------------------------------------------------------------------
// | Longbing [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright Chengdu longbing Technology Co., Ltd.
// +----------------------------------------------------------------------
// | Website http://longbing.org/
// +----------------------------------------------------------------------
// | Author: ArtizanZhang
// | DataTime: 2019/12/9 9:46
// +----------------------------------------------------------------------

declare(strict_types=1);


use app\appstore\info\PermissionActivity;
use app\appstore\info\PermissionAppiont;
use app\appstore\info\PermissionArticle;
use app\appstore\info\Permission;
use app\appstore\info\PermissionPayqr;
use app\appstore\info\PermissionPoster;
use app\appstore\info\PermissionSend;

return [
    //appstore
    PermissionSend::class,                    //群发短信
    PermissionActivity::class,
    PermissionAppiont::class,
    PermissionArticle::class,
    Permission::class,
    PermissionPayqr::class,
    PermissionPoster::class,
];