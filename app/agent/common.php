<?php
// 这是系统自动生成的admin应用公共文件
use app\admin\model\Admin as AdminModel;
use app\admin\model\Role as RoleModel;
use app\agent\model\LongbingPlugeKey;
use think\facade\Config;


//删除用户登录信息
function delUserForToken($token) {
    return delCache("Token_".$token);
}

//生成token
function createToken() {
    return uuid();
}

//检查密码是否正确
function checkPasswd(string $passwd ,string $offset ,string $hash) {
    //检查秘钥是否正确
    return password_verify($offset . $passwd . $offset ,$hash);
}

//获取缓存数据中的 accounts 列表
function getAccountList($uniacid = 7777){
    //获取缓存数据
    $result = getCache('accounts_' . $uniacid);
//    var_dump(getCache('accounts_7777'));die;
    //数据存在返回数据
    if(!empty($result)) return $result;
    //缓存数据不存在时，从数据库获取数据，同时写入缓存

    return $result;
}

//设置账号缓存
function setAccountToCache($account ,$uniacid = 7777){
    if(empty(getAccountList($uniacid))) return setCache('accounts_'.$uniacid ,[$account]);
    return pushCache('accounts_'.$uniacid ,$account);
}
//移除账号缓存
function delAccountToCache($account ,$uniacid = 7777){
    
}

//检查检查用户账号是否存在
function checkAccountIsExist($account ,$uniacid = 7777){
//    //获取账号列表
//    $account_list = getAccountList($uniacid);
//    if(empty($account_list)) $account_list = [];
//    //判断账号是否存在
//    return in_array($account ,$account_list);
    $result = false;
    $count = AdminModel::where(['account' => $account ,'uniacid' => $uniacid ,'deleted' => 0])->count();
    if(!empty($count)) $result = true;
    return $result;
}
//获取角色信息
function getRole($role_name = 'user',$uniacid = 7777) {
    $list_role = listRole($uniacid);
    foreach($list_role as $role) {
        if(in_array($role_name, [$role['role_name']]))  unset($role_name) ;return $role;
    }
    return false;
}

//获取角色模型
function getRoleModel() {
    return new RoleModel();
}

//获取角色列表
function listRole($uniacid = 7777){
    //从缓存中回去数据
    $result = getCache('ListRole' . $uniacid);
    if(!empty($result)) return $result;
    $role_modle = getRoleModel();
    $list_role = $role_modle->listRoleAll(['uniacid' => $uniacid]);
    if(!empty($list_role)) {
        setCache('ListRole' . $uniacid ,$list_role ,3600);
        foreach($list_role as $role) {
            setCache('role_' . $uniacid . '_' . $role['role_id'] ,$role ,3600);
        }
    }
    return $list_role; 
}

function ckeckRole($role_id ,$uniacid = 7777) {
    $list_role = listRole($uniacid);
    unset($list_role);
    return getCache('role_' . $uniacid . '_' . $role['role_id']);
}

function listUserFilter($filter) {
    $data = ['user.deleted' => 0 ,'role.deleted' => 0];
    if(isset($filter['user_id'])) $data['user.user_id']       = $filter['user_id'];
    if(isset($filter['account'])) $data['user.account']       = ['like' ,'%' . $filter['account'] . '%'];
    if(isset($filter['name'])) $data['user.name']             = ['like' ,'%' . $filter['name'] . '%'];
    if(isset($filter['creator_id'])) $data['user.creator_id'] = $filter['creator_id'];
    if(isset($filter['status'])) $data['status']              = $filter['status'];
    if(isset($filter['nickname'])) $data['user.nickname']     = ['like' ,'%' . $filter['nickname'] . '%'];
    if(isset($filter['certificate_num'])) $data['user.certificate_num'] = $filter['certificate_num'];
    if(isset($filter['email'])) $data['user.email']           = ['like' , '%' . $filter['email'] . '%'];
    if(isset($filter['wechat'])) $data['user.wechat']         = ['like' , '%' . $filter['wechat'] . '%'];
    if(isset($filter['qq'])) $data['user.qq']                 = ['like' , '%' . $filter['qq'] . '%'];
    if(isset($filter['mobile'])) $data['user.mobile']         = ['like' , '%' . $filter['mobile'] . '%'];
    if(isset($filter['role_id'])) $data['user.role_id']       = $filter['role_id'];
    if(isset($filter['uniacid'])) $data['user.uniacid']       = $filter['uniacid'];
    if(isset($filter['department_id'])) $data['user.department_id']       = $filter['department_id'];
    return $data;
}

function getUpdateUserFilter($data) {
    if(isset($data['user_id'])) unset($data['user_id']);
    if(isset($data['offset'])) unset($data['offset']);
    if(isset($data['create_time'])) unset($data['create_time']);
    if(isset($data['update_time'])) unset($data['update_time']);
    if(isset($data['delete_time'])) unset($data['delete_time']);
    return $data;
}

function getOssConfigData($data)
{
    $result['open_oss'] = 0;
    if(isset($data['miniapp_name'])) $result['miniapp_name'] = $data['miniapp_name'];
    if(isset($data['open_oss']) && in_array($data['open_oss'], [0,1,2,3,'0','1','2','3'])) $result['open_oss'] = $data['open_oss'];
    if(isset($data['aliyun_bucket'])) $result['aliyun_bucket'] = $data['aliyun_bucket'];
    if(isset($data['aliyun_access_key_id'])) $result['aliyun_access_key_id'] = $data['aliyun_access_key_id'];
    if(isset($data['aliyun_access_key_secret'])) $result['aliyun_access_key_secret'] = $data['aliyun_access_key_secret'];
    if(isset($data['aliyun_base_dir'])) $result['aliyun_base_dir'] = $data['aliyun_base_dir'];
    if(isset($data['aliyun_zidinyi_yuming'])) $result['aliyun_zidinyi_yuming'] = $data['aliyun_zidinyi_yuming'];
    if(isset($data['aliyun_endpoint'])) $result['aliyun_endpoint'] = $data['aliyun_endpoint'];
    if(isset($data['aliyun_rules'])) $result['aliyun_rules'] = $data['aliyun_rules'];
    if(isset($data['qiniu_accesskey'])) $result['qiniu_accesskey'] = $data['qiniu_accesskey'];
    if(isset($data['qiniu_secretkey'])) $result['qiniu_secretkey'] = $data['qiniu_secretkey'];
    if(isset($data['qiniu_bucket'])) $result['qiniu_bucket'] = $data['qiniu_bucket'];
    if(isset($data['qiniu_yuming'])) $result['qiniu_yuming'] = $data['qiniu_yuming'];
    if(isset($data['qiniu_rules'])) $result['qiniu_rules'] = $data['qiniu_rules'];
    if(isset($data['tenxunyun_appid'])) $result['tenxunyun_appid'] = $data['tenxunyun_appid'];
    if(isset($data['tenxunyun_secretid'])) $result['tenxunyun_secretid'] = $data['tenxunyun_secretid'];
    if(isset($data['tenxunyun_secretkey'])) $result['tenxunyun_secretkey'] = $data['tenxunyun_secretkey'];
    if(isset($data['tenxunyun_bucket'])) $result['tenxunyun_bucket'] = $data['tenxunyun_bucket'];
    if(isset($data['tenxunyun_region'])) $result['tenxunyun_region'] = $data['tenxunyun_region'];
    if(isset($data['tenxunyun_yuming'])) $result['tenxunyun_yuming'] = $data['tenxunyun_yuming'];
    if(isset($data['apiclient_cert'])) $result['apiclient_cert'] = $data['apiclient_cert'];
    if(isset($data['apiclient_key'])) $result['apiclient_key'] = $data['apiclient_key'];
    return $result;    
}
//底部菜单数据封装
function longbingGetAppTabbarResponse($data)
{
    if(empty($data)) return [];
    //数据处理
    $data['data'] = [];
    //处理过的参数
    $menus = [];
    //名片
    if(isset($data['menu1_is_hide']))
    {
        $val = ['menu_name' => 'card'];
        $val['is_show'] = $data['menu1_is_hide'];
        if(isset($data['menu1_name'])) $val['name'] = $data['menu1_name'];
        if(isset($data['menu1_url'])) $val['url'] = $data['menu1_url'];
        if(isset($data['menu1_url_out'])) $val['url_out'] = $data['menu1_url_out'];
        if(isset($data['menu1_url_jump_way'])) $val['url_jump_way'] = $data['menu1_url_jump_way'];
        $data['data']['card'] = $val;
    }
    //商城
    if(isset($data['menu2_is_hide']))
    {
        $val = ['menu_name' => 'shop'];
        $val['is_show'] = $data['menu2_is_hide'];
        if(isset($data['menu2_name'])) $val['name'] = $data['menu2_name'];
        if(isset($data['menu2_url'])) $val['url'] = $data['menu2_url'];
        if(isset($data['menu2_url_out'])) $val['url_out'] = $data['menu2_url_out'];
        if(isset($data['menu2_url_jump_way'])) $val['url_jump_way'] = $data['menu2_url_jump_way'];
        $data['data']['shop'] = $val;
    }
    //动态
    if(isset($data['menu3_is_hide']))
    {
        $val = ['menu_name' => 'dynamic'];
        $val['is_show'] = $data['menu3_is_hide'];
        if(isset($data['menu3_name'])) $val['name'] = $data['menu3_name'];
        if(isset($data['menu3_url'])) $val['url'] = $data['menu3_url'];
        if(isset($data['menu3_url_out'])) $val['url_out'] = $data['menu3_url_out'];
        if(isset($data['menu3_url_jump_way'])) $val['url_jump_way'] = $data['menu3_url_jump_way'];
        $data['data']['dynamic'] = $val;
    }
    //官网
    if(isset($data['menu4_is_hide']))
    {
        $val = ['menu_name' => 'website'];
        $val['is_show'] = $data['menu4_is_hide'];
        $menus[] = 'menu4_name';
        if(isset($data['menu4_name'])) $val['name'] = $data['menu4_name'];
        if(isset($data['menu4_url'])) $val['url'] = $data['menu4_url'];
        if(isset($data['menu4_url_out'])) $val['url_out'] = $data['menu4_url_out'];
        if(isset($data['menu4_url_jump_way'])) $val['url_jump_way'] = $data['menu4_url_jump_way'];
        $data['data']['website'] = $val;
    }
    //预约
    if(isset($data['menu_appoint_is_hide']))
    {
        $val = ['menu_name' => 'appointment'];
        $val['is_show'] = $data['menu_appoint_is_hide'];
        if(isset($data['menu_appoint_name'])) $val['name'] = $data['menu_appoint_name'];
        if(isset($data['menu_appoint_url'])) $val['url'] = $data['menu_appoint_url'];
        if(isset($data['menu_appoint_url_out'])) $val['url_out'] = $data['menu_appoint_url_out'];
        if(isset($data['menu_appoint_url_jump_way'])) $val['url_jump_way'] = $data['menu_appoint_url_jump_way'];
        $data['data']['appointment'] = $val;
    }
    //活动报名
    if(isset($data['menu_activity_is_show']))
    {
        $val = ['menu_name' => 'activity'];
        $val['is_show'] = $data['menu_activity_is_show'];
        if(isset($data['menu_activity_name'])) $val['name'] = $data['menu_activity_name'];
        if(isset($data['menu_activity_url'])) $val['url'] = $data['menu_activity_url'];
        if(isset($data['menu_activity_url_out'])) $val['url_out'] = $data['menu_activity_url_out'];
        if(isset($data['menu_activity_url_jump_way'])) $val['url_jump_way'] = $data['menu_activity_url_jump_way'];
        $data['data']['activity'] = $val;
    }
    //房产
    if(isset($data['menu_house_is_show']))
    {
        $val = ['menu_name' => 'house'];
        $val['is_show'] = $data['menu_house_is_show'];
        if(isset($data['menu_house_name']))$val['name'] = $data['menu_house_name'];
        if(isset($data['menu_house_url']))$val['url'] = $data['menu_house_url'];
        if(isset($data['menu_house_url_out']))$val['url_out'] = $data['menu_house_url_out'];
        if(isset($data['menu_house_url_jump_way']))$val['url_jump_way'] = $data['menu_house_url_jump_way'];
        $data['data']['house'] = $val;
    }
    $menus = ["menu1_name","menu1_is_hide","menu1_url","menu1_url_out","menu1_url_jump_way","menu2_name","menu2_is_hide","menu2_url","menu2_url_out","menu2_url_jump_way","menu3_name","menu3_is_hide","menu3_url","menu3_url_out","menu3_url_jump_way","menu4_name","menu4_is_hide","menu4_url","menu4_url_out","menu4_url_jump_way","menu_appoint_name","menu_appoint_is_hide","menu_appoint_url","menu_appoint_url_out","menu_appoint_url_jump_way","menu_activity_is_show","menu_activity_name","menu_activity_is_hide","menu_activity_url","menu_activity_url_out","menu_activity_url_jump_way","menu_house_is_show","menu_house_name","menu_house_is_hide","menu_house_url","menu_house_url_out","menu_house_url_jump_way"];
    foreach($menus as $menu)
    {
        unset($data[$menu]);
    }
    return $data;
}

function longbingGetWxAppTabbarResponse($data)
{
    if(empty($data)) return [];
    //数据处理
    $data['data'] = [];
    //处理过的参数
    $menus = [];
    //名片
    if(isset($data['menu1_is_hide']) && !empty($data['menu1_is_hide']))
    {
        $val = [];
        $val['is_show']  = $data['menu1_is_hide'];
        $val['key']      = 1;
        $val['iconPath'] = 'icon-mingpian';
        $val['selectedIconPath'] = 'icon-mingpian1';
        $val['pageComponents']   = 'cardHome';
        if(isset($data['menu1_name'])) $val['name'] = $data['menu1_name'];
        if(isset($data['menu1_url'])) $val['url'] = $data['menu1_url'];
        if(isset($data['menu1_url_out'])) $val['url_out'] = $data['menu1_url_out'];
        if(isset($data['menu1_url_jump_way'])) $val['jump_way'] = $data['menu1_url_jump_way'];
        $data['data'][] = $val;
    }
    //商城
    if(isset($data['menu2_is_hide']) && !empty($data['menu2_is_hide']))
    {
        $val = [];
        $val['key']      = 2;
        $val['is_show']  = $data['menu2_is_hide'];
        $val['iconPath'] = 'icon-shangcheng1';
        $val['selectedIconPath'] = 'icon-shangcheng';
        $val['pageComponents']   = 'shopHome';
        if(isset($data['menu2_name'])) $val['name'] = $data['menu2_name'];
        if(isset($data['menu2_url'])) $val['url'] = $data['menu2_url'];
        if(isset($data['menu2_url_out'])) $val['url_out'] = $data['menu2_url_out'];
        if(isset($data['menu2_url_jump_way'])) $val['url_jump_way'] = $data['menu2_url_jump_way'];
        $data['data'][] = $val;
    }
    //动态
    if(isset($data['menu3_is_hide']) && !empty($data['menu3_is_hide']))
    {
        $val = [];
        $val['key']      = 3;
        $val['is_show']  = $data['menu3_is_hide'];
        $val['iconPath'] = 'icon-dongtai1';
        $val['selectedIconPath'] = 'icon-dongtai';
        $val['pageComponents']   = 'infoHome';
        if(isset($data['menu3_name'])) $val['name'] = $data['menu3_name'];
        if(isset($data['menu3_url'])) $val['url'] = $data['menu3_url'];
        if(isset($data['menu3_url_out'])) $val['url_out'] = $data['menu3_url_out'];
        if(isset($data['menu3_url_jump_way'])) $val['url_jump_way'] = $data['menu3_url_jump_way'];
        $data['data'][] = $val;
    }
    //官网
    if(isset($data['menu4_is_hide']) && !empty($data['menu4_is_hide']))
    {
        $val = [];
        $val['key']      = 4;
        $val['is_show']  = $data['menu4_is_hide'];
        $val['iconPath'] = 'icon-guanwang';
        $val['selectedIconPath'] = 'icon-guanwang1';
        $val['pageComponents']   = 'websiteHome';
        if(isset($data['menu4_name'])) $val['name'] = $data['menu4_name'];
        if(isset($data['menu4_url'])) $val['url'] = $data['menu4_url'];
        if(isset($data['menu4_url_out'])) $val['url_out'] = $data['menu4_url_out'];
        if(isset($data['menu4_url_jump_way'])) $val['url_jump_way'] = $data['menu4_url_jump_way'];
        $data['data'][] = $val;
    }
    //预约
    if(isset($data['menu_appoint_is_hide']) && !empty($data['menu_appoint_is_hide']))
    {
        $val = [];
        $val['key']      = 7;
        $val['is_show']  = $data['menu_appoint_is_hide'];
        $val['iconPath'] = 'icon-yuyue';
        $val['selectedIconPath'] = 'icon-yuyue1';
        $val['pageComponents']   = 'reserveHome';
        if(isset($data['menu_appoint_name'])) $val['name'] = $data['menu_appoint_name'];
        if(isset($data['menu_appoint_url'])) $val['url'] = $data['menu_appoint_url'];
        if(isset($data['menu_appoint_url_out'])) $val['url_out'] = $data['menu_appoint_url_out'];
        if(isset($data['menu_appoint_url_jump_way'])) $val['url_jump_way'] = $data['menu_appoint_url_jump_way'];
        $data['data'][] = $val;
    }
    //活动报名
    if(isset($data['menu_activity_is_show']) && !empty($data['menu_activity_is_show']))
    {
        $val = [];
        $val['key']      = 6;
        $val['is_show']  = $data['menu_activity_is_show'];
        $val['iconPath'] = 'icon-huodong1';
        $val['selectedIconPath'] = 'icon-huodong';
        $val['pageComponents']   = 'avtivityHome';
        if(isset($data['menu_activity_name'])) $val['name'] = $data['menu_activity_name'];
        if(isset($data['menu_activity_url'])) $val['url'] = $data['menu_activity_url'];
        if(isset($data['menu_activity_url_out'])) $val['url_out'] = $data['menu_activity_url_out'];
        if(isset($data['menu_activity_url_jump_way'])) $val['url_jump_way'] = $data['menu_activity_url_jump_way'];
        $data['data'][] = $val;
    }
    //房产
    if(isset($data['menu_house_is_show']) && !empty($data['menu_house_is_show']))
    {
        $val = [];
        $val['key']      = 5;
        $val['is_show']  = $data['menu_house_is_show'];
        $val['iconPath'] = 'icon-fangchan1';
        $val['selectedIconPath'] = 'icon-fangchan';
        $val['pageComponents']   = 'houseHome';
        if(isset($data['menu_house_name']))$val['name'] = $data['menu_house_name'];
        if(isset($data['menu_house_url']))$val['url'] = $data['menu_house_url'];
        if(isset($data['menu_house_url_out']))$val['url_out'] = $data['menu_house_url_out'];
        if(isset($data['menu_house_url_jump_way']))$val['url_jump_way'] = $data['menu_house_url_jump_way'];
        $data['data'][] = $val;
    }
    $menus = ["menu1_name","menu1_is_hide","menu1_url","menu1_url_out","menu1_url_jump_way","menu2_name","menu2_is_hide","menu2_url","menu2_url_out","menu2_url_jump_way","menu3_name","menu3_is_hide","menu3_url","menu3_url_out","menu3_url_jump_way","menu4_name","menu4_is_hide","menu4_url","menu4_url_out","menu4_url_jump_way","menu_appoint_name","menu_appoint_is_hide","menu_appoint_url","menu_appoint_url_out","menu_appoint_url_jump_way","menu_activity_is_show","menu_activity_name","menu_activity_is_hide","menu_activity_url","menu_activity_url_out","menu_activity_url_jump_way","menu_house_is_show","menu_house_name","menu_house_is_hide","menu_house_url","menu_house_url_out","menu_house_url_jump_way"];
    foreach($menus as $menu)
    {
        unset($data[$menu]);
    }
    return $data;
}

function longbingGetAppTabbarRequest($data)
{
    $result = [];
    foreach($data as $key => $val)
    {
        switch($val['menu_name'])
        {
            case 'card':
                if(isset($val['is_show'])) $result['menu1_is_hide'] = $val['is_show'];
                if(isset($val['name'])) $result['menu1_name'] = $val['name'];
//                if(isset($val['url'])) $result['menu1_url'] = $val['url'];
                if(isset($val['url_out'])) $result['menu1_url_out'] = $val['url_out'];
                if(isset($val['url_jump_way'])) $result['menu1_url_jump_way'] = $val['url_jump_way'];
                break;
            case 'shop':
                if(isset($val['is_show'])) $result['menu2_is_hide'] = $val['is_show'];
                if(isset($val['name'])) $result['menu2_name'] = $val['name'];
//                if(isset($val['url'])) $result['menu2_url'] = $val['url'];
                if(isset($val['url_out'])) $result['menu2_url_out'] = $val['url_out'];
                if(isset($val['url_jump_way'])) $result['menu2_url_jump_way'] = $val['url_jump_way'];
                break;
            case 'dynamic':
                if(isset($val['is_show'])) $result['menu3_is_hide'] = $val['is_show'];
                if(isset($val['name'])) $result['menu3_name'] = $val['name'];
//                if(isset($val['url'])) $result['menu3_url'] = $val['url'];
                if(isset($val['url_out'])) $result['menu3_url_out'] = $val['url_out'];
                if(isset($val['url_jump_way'])) $result['menu3_url_jump_way'] = $val['url_jump_way'];
                break;
            case 'website':
                if(isset($val['is_show'])) $result['menu4_is_hide'] = $val['is_show'];
                if(isset($val['name'])) $result['menu4_name'] = $val['name'];
//                if(isset($val['url'])) $result['menu4_url'] = $val['url'];
                if(isset($val['url_out'])) $result['menu4_url_out'] = $val['url_out'];
                if(isset($val['url_jump_way'])) $result['menu4_url_jump_way'] = $val['url_jump_way'];
                break;
            case 'appointment':
                if(isset($val['is_show'])) $result['menu_appoint_is_hide'] = $val['is_show'];
                if(isset($val['name'])) $result['menu_appoint_name'] = $val['name'];
//                if(isset($val['url'])) $result['menu_appoint_url'] = $val['url'];
                if(isset($val['url_out'])) $result['menu_appoint_url_out'] = $val['url_out'];
                if(isset($val['url_jump_way'])) $result['menu_appoint_url_jump_way'] = $val['url_jump_way'];
                break;
            case 'activity':
                if(isset($val['is_show'])) $result['menu_activity_is_show'] = $val['is_show'];
                if(isset($val['name'])) $result['menu_activity_name'] = $val['name'];
//                if(isset($val['url'])) $result['menu_activity_url'] = $val['url'];
                if(isset($val['url_out'])) $result['menu_activity_url_out'] = $val['url_out'];
                if(isset($val['url_jump_way'])) $result['menu_activity_url_jump_way'] = $val['url_jump_way'];
                break;
            case 'house':
                if(isset($val['is_show'])) $result['menu_house_is_show'] = $val['is_show'];
                if(isset($val['name'])) $result['menu_house_name'] = $val['name'];
//                if(isset($val['url'])) $result['menu_house_url'] = $val['url'];
                if(isset($val['url_out'])) $result['menu_house_url_out'] = $val['url_out'];
                if(isset($val['url_jump_way'])) $result['menu_house_url_jump_way'] = $val['url_jump_way'];
                break;
            default:
                break;
        }
    }
    return $result;
}

/*
 * 获取branch id 程序唯一识别号
 * @author yangqi
 */
function longbingGetBranchId()
{
    $app_config = Config::get( 'app' );
    $key        = 'branch_id';
    $result     = false;
    if ( isset( $app_config[ $key ] ) ) $result = $app_config[ $key ];
    return $result;
}

/*
 * 获取branch id 程序版本号
 * @author yangqi
 */
function longbingGetBranchVersionId()
{
    $app_config = Config::get( 'app' );
    $key        = 'version_id';
    $result     = false;
    if ( isset( $app_config[ $key ] ) ) $result = $app_config[ $key ];
    return $result;
}

/*
 * 获取branch version name
 * @author yangqi
 */
function longbingGetBranchVersionName()
{
    $app_config = Config::get( 'app' );
    $key        = 'version_name';
    $result     = '2.0.1';
    if ( isset( $app_config[ $key ] ) ) $result = $app_config[ $key ];
    return $result;
}

/**
 * 获取站点绑定的数据
 * @yangqi
 */
function longbingGetWebSiteBingData()
{
    //生成数据
    $pluge_key_model = new LongbingPlugeKey();
    $result          = $pluge_key_model->getPlugeKey();
    return $result;
}

//更新站点绑定数据
function longbingUpdateWebSiteBingData($filter ,$data)
{
    $pluge_key_model = new LongbingPlugeKey();
    $result          = $pluge_key_model->updatePlugeKey($filter ,$data);
    return !empty($result);
}

function longbingGetSaasUrl()
{
    $app_config = Config::get( 'app' );
    $key        = 'longbing_saas_url';
    $result     = 'http://api.longbing.org';
    if ( isset( $app_config[ $key ] ) && !empty($app_config[ $key ])) $result = $app_config[ $key ];
    return $result;
}

/**
 * 下载文件
 */
function longbingGetFile($url, $save_dir = '', $filename = '', $type = 0) {
    if (trim($url) == '') {
        return false;
    }
    if (trim($save_dir) == '') {
        $save_dir = './';
    }
    if (0 !== strrpos($save_dir, '/')) {
        $save_dir.= '/';
    }
    //创建保存目录
    if (!file_exists($save_dir) && !mkdir($save_dir, 0777, true)) {
        return false;
    }
    //获取远程文件所采用的方法
    if ($type) {
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $content = curl_exec($ch);
        curl_close($ch);
    } else {
        ob_start();
        readfile($url);
        $content = ob_get_contents();
        ob_end_clean();
    }
    $size = strlen($content);
    //文件大小
    $fp2 = @fopen($save_dir . $filename, 'a');

    fwrite($fp2, $content);
    fclose($fp2);
//  unset($content, $url);
    return array(
        'file_name' => $filename,
        'save_path' => $save_dir . $filename
    );
}
/*
php 从zip压缩文件中提取文件
*/
function longbingUnZipFile($zip_file_url ,$path = './upload/') {
    $zip = new ZipArchive;
    if ($zip->open($zip_file_url) === TRUE) {//中文文件名要使用ANSI编码的文件格式
        $zip->extractTo($path);//提取全部文件
        $zip->close();
        return true;
    } else {
        return false;
    }
}

/**
PHP文件目录copy
@param   string $dirsrc   原目录名称字符串
@param   string $dirto    目标目录名称字符串
 */
function longbingCopyDir($dirSrc,$dirTo)
{
    //判断目标文件夹是否存在
    if(is_file($dirTo))
    {
        return false;
    }
    //判断目的文件夹是否存在
    if(!file_exists($dirTo))
    {
        mkdir($dirTo);
    }
    //拷贝文件
    if($handle=opendir($dirSrc))
    {
        while($filename=readdir($handle))
        {
            if($filename!='.' && $filename!='..')
            {
                $subsrcfile=$dirSrc . '/' . $filename;
                $subtofile=$dirTo . '/' . $filename;
                if(is_dir($subsrcfile))
                {
                    longbingCopyDir($subsrcfile,$subtofile);//再次递归调用copydir
                }
                if(is_file($subsrcfile))
                {
                    copy($subsrcfile,$subtofile);
                }
            }
        }
        closedir($handle);
        return true;
    }
    return false;
}

function longbingGetBetween($input, $start, $end) {
    $str = substr($input, strlen($start)+strpos($input, $start),(strlen($input) - strpos($input, $end))*(-1));
    return $str;
}


function longbingGetZipName ($url) {
    return longbingGetBetween($url ,"/" ,".zip");
}

/**
 * 更新APP
 */
function longbingUpdateAppFile($down_url ,$save_dir, $file_name ,$cp_dir) {
    $file_name = longbingGetZipName('/' .$file_name);
    $download_file = longbingGetFile($down_url, $save_dir, $filename = $file_name.'.zip', $type = 1);


    if(empty($download_file)) return FALSE;
    $zip_url = $save_dir . "/" . $file_name.".zip";
    $get_app_file  = longbingUnZipFile($zip_url ,$save_dir);

    if(empty($get_app_file)) return FALSE;
    $update_file   = longbingCopyDir($save_dir. "/" . $file_name , $cp_dir);
    return $update_file;
}