<?php
/**
 * AdaPay 查询结算账户
 * author: adapay.com https://docs.adapay.tech/api/04-trade.html
 * Date: 2019/09/17
 */

# 加载SDK需要的文件
include_once dirname(__FILE__) . "/../../AdapaySdk/init.php";
# 加载商户的配置文件
include_once dirname(__FILE__) . "/../config.php";



# 初始化账户冻结对象
$fz_account = new \AdaPaySdk\FreezeAccount();

$fz_params = array(
    'app_id'=> 'app_7d87c043-aae3-4357-9b2c-269349a980d6',
    'order_no'=> 'FZ_'. date("YmdHis").rand(100000, 999999),
    'status'=> 'succeeded', //succeeded-成功，failed-失败，pending-处理中
    'page_index'=> 1,
    'page_size'=> 1,
    'created_gte'=> '',
    'created_lte'=> ''
);

# 查询账户冻结对象
$fz_account->queryList($fz_params);

# 对查询账户冻结对象结果进行处理
if ($fz_account->isError()){
    //失败处理
    var_dump($fz_account->result);
} else {
    //成功处理
    var_dump($fz_account->result);
}