<?php
// 这是系统自动生成的index应用公共文件
//通过Token获取用户信息


//删除用户登录信息
function delUserForToken($token) {
    return delCache("Token_".$token);
}

//生成token
function longbingCreateToken() {
    return uuid();
}

//检查密码是否正确
function longbingCheckPasswd(string $passwd ,string $offset ,string $hash) {
    //检查秘钥是否正确
    return password_verify($offset . $passwd . $offset ,$hash);
}

//获取缓存数据中的 accounts 列表
function getAccountList($uniacid = 7777){
    //获取缓存数据
    $result = getCache('accounts_' . $uniacid);
//  var_dump(getCache('accounts_7777'));die;
    //数据存在返回数据
    if(!empty($result)) return $result;
    //缓存数据不存在时，从数据库获取数据，同时写入缓存

    return $result;
}