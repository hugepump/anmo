<?php
/**
 * Created by PhpStorm.
 * User: 方琪
 * Date: 2021/11/29
 * 接口URl
 */

class Constant
{
    //鉴权tokenURl（正式）
    const TOKEN_URL = 'https://identity.fangxinqian.cn/auth/v1/token';

    //合同关键词签署多文件（正式）
    const CONTRACT_DETAIL_URL = 'https://restapi.fangxinqian.cn/contract/v1/key/multisign';

    //公安二要素（正式）
    const ID_CHECK_URL = 'https://identity.fangxinqian.cn/identity/v1/2';

    //pdf转图片（正式）
    const PDF_TO_IMG_URL = 'https://restapi.fangxinqian.cn/template/v1/pdf2Img/asyn';

    //人脸识别
    const FACE_URL = 'https://identity.fangxinqian.cn/face/v1/h5';

    //短信
    const SEND_URL = 'https://identity.fangxinqian.cn/sms/v1/send';

    //获取E证通token
    const E_TOKEN_URL = 'https://identity.fangxinqian.cn/face/v1/getEidToken';

    //word转pdf（正式）
    const WORD_TO_PDF_URL = 'https://restapi.fangxinqian.cn/api/base64';

    //企业签章（正式）
    const COMPANY_SIGN_URL = 'https://restapi.fangxinqian.cn/seal/v1/company';

    //个人签章（正式）
    const PERSONAL_SIGN_URL = 'https://restapi.fangxinqian.cn/seal/v1/personal';

    //企业实名认证（正式）
    const COMPANY_CHECK_URL = 'https://identity.fangxinqian.cn/company/v1/3';

    //蚂蚁链存证（正式）
    const FULL_URL = 'https://restapi.fangxinqian.cn/evidence/v1/full';

    //蚂蚁链存证（沙箱）
//    const FULL_URL = 'https://restapitest.fangxinqian.cn/evidence/v1/full';

    //企业实名认证（沙箱）
//    const COMPANY_CHECK_URL = 'https://identitytest.fangxinqian.cn/company/v1/3';

    //个人签章（沙箱）
//    const PERSONAL_SIGN_URL = 'https://restapitest.fangxinqian.cn/seal/v1/personal';

    //企业签章（沙箱）
//    const COMPANY_SIGN_URL = 'https://restapitest.fangxinqian.cn/seal/v1/company';

    //小程序人脸识别结果查询
    const E_RESULT_URL = 'https://identity.fangxinqian.cn/face/v1/getEidResult';

    //word转pdf（沙箱）
//    const WORD_TO_PDF_URL = 'https://restapitest.fangxinqian.cn/api/base64';

    //鉴权tokenURl（沙箱环境）
//    const TOKEN_URL = 'https://identitytest.fangxinqian.cn/auth/v1/token';

    //合同关键词签署多文件（沙箱环境）
//    const CONTRACT_DETAIL_URL = 'https://restapitest.fangxinqian.cn/contract/v1/key/multisign';

    //公安二要素（沙箱环境）
//    const ID_CHECK_URL = 'https://identitytest.fangxinqian.cn/identity/v1/2';

    //pdf转图片（沙箱）
//    const PDF_TO_IMG_URL = 'https://restapitest.fangxinqian.cn/template/v1/pdf2Img/asyn';
}