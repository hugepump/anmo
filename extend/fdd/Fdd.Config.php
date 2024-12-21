<?php
/**
*   配置账号信息
*/

class FddConfig
{
    //=======【基本信息设置】=====================================
    /**
     * TODO: 修改这里配置法大大调用接口地址
     *
     * FddServer ：法大大接口调用地址（必须配置）
     *
     */
    //通用接口调用地址
   // const FddServer     = 'http://localhost:8001/api';
    const FddServer     = 'https://textapi.fadada.com/api2/';
   // const FddServer     = 'https://testapi.fadada.com:8443/api/';
    //法大大企业页面认证接口调用地址
    const FddServerSyncCompany = 'https://partner-test.fadada.com';
    // 法大大存证服务地址
    const FDDWitnessServer = "http://czapi-test.fadada.com:7500/evidence-api";

    //=======【法大大商户密钥信息】===================================
    /**
     * TODO: 修改这里配置法大大密钥
     *
     * AppId ：接入方的ID
     *
     * ApiPort：接入方的密钥
     *
     */

//    const AppId     = '407424';
//    const AppSecret = 'zMqxZwhWb0UHHn0ylvZQlf65';


    const AppId     = FDD_ADDPID;

    const AppSecret = FDD_SECERT;





}
