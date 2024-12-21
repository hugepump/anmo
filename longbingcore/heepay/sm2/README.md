# hyapi_php_demo

#### 介绍

汇元网Open API开放平台对接客户中心等API，PHP语言的DEMO。 [汇元开放平台客户中心对接相关文档链接](https://open.heepay.com/www/index.html#/openDoc?type=menu&id=2022360361)

本项目支持php版本的国密sm2的签名算法，非对称加解密算法，sm3的hash, sm4的对称加解密，要求PHP７，打开gmp支持。

如果由于PHP版本等原因集成国密困难，可以考虑使用gmTool服务来提供SM2签名验签功能。gmTool服务开源代码地址：[gmTool: 此仓库提供一个签名验签服务工具给用户方便对接汇元网API服务接口](https://gitee.com/LeagueJ/gmTool)

#### 使用说明

- composer require lpilp/guom

- please make sure you upgrade to Composer 2+

- PHP >=7.2

- 如需要使用php5.6 请使用wzhih童鞋fork修改的 [https://github.com/wzhih/guomi](https://gitee.com/link?target=https%3A%2F%2Fgithub.com%2Fwzhih%2Fguomi) ; composer require wzhih/guomi

### SM2签名常见问题

- 提供的私钥是base64的短串，一般直接 bin2hex(base64_decode(str)) 就是明文的密钥了
- 文件格式的密钥一般有pkcs1与pkcs8两个格式，本项目只支持pkcs1格式的密钥，使用前请先进行相关的转换，一般 pkcs8是四行，pkcs1是三行，区别见 [https://www.jianshu.com/p/a428e183e72e](https://gitee.com/link?target=https%3A%2F%2Fwww.jianshu.com%2Fp%2Fa428e183e72e)
- 关于签名的字符串的问题，有些项目会将原始字符串哈希后，再对哈希值进行签名，有些对这哈希值又进行了hex2bin操作后再签名，请双方按约定的标准确定最后签名的数据值，双方保持一致即可
- 个别项目会碰到asn1的时候解析不了，如果是密钥解析不了，一般就是pkcs8的问题，如果只是数据解析不了的话看报错，调试下，目前碰到的是招行的一个问题，对int数据的asn1编码有前面补0与否不按标准来，全都补0
- 签名的结果是asn1(r,s),有极个别的项目签名出来的只是 r+s的字符串组合，验证签名的时候注意下。

## 测试案例

```textile
SM2密钥对生成测试

private key: string(64) "550b1a390c47ed344bd026c3c3830139e7a1aed70f43830dcc8f615efad8019d"
public key: string(130) "045fefd2c24e2bcba51ec08e30d6f65f8915c5b9f43d1ca5c26fb1f47d5381cf52877d6563afcbe7116e54fcbfd657cbd18a940f46eb0a14ddfc513dbf332488f2"
SM2私钥签名验签测试

message: To be signed message
private key: string(64) "fcde55bd1cd084decd3ed03a205277fa2779146f99a5bf918be55f1f55cda0a4"
public key: string(130) "04a14539132ba78440838e9952a363447acced34cf627761596c99191683c38c18fa4e54a57c5ee3704593d2c30801be53d50147abd872acd964be20e6514e1092"
签名数据: string(140) "304402202d14d9110cdd877dcd911e7ca5da21203d8a05c459fc3e6900d1215670e61115022059349c06645edcd3d8c3891ca562ea6ea49de16e36326f03528fd1d64cd6a453"
验签结果: bool(true)
汇元公钥SM2验签测试

汇元公钥 pkcs8 base64: MIIBMzCB7AYHKoZIzj0CATCB4AIBATAsBgcqhkjOPQEBAiEA/////v////////////////////8AAAAA//////////8wRAQg/////v////////////////////8AAAAA//////////wEICjp+p6dn140TVqeS89lCafzl4n1FauPkt28vUFNlA6TBEEEMsSuLB8ZgRlfmQRGajnJlI/jC7/yZgvhcVpFiTNMdMe8Nzai9PZ3nFm9zuNraSFT0KmHfMYqR0AC3zLlITnwoAIhAP////7///////////////9yA99rIcYFK1O79Ak51UEjAgEBA0IABChf5Gs11hyWHD4Tn0MfyZvHjd9L5XO3xz2cU/hmXb+YcL9lk4xKMC+VZ0JEx6Pm/oVjwcGBINUEgi05oyNj7+U=
汇元公钥 hex串: 04285fe46b35d61c961c3e139f431fc99bc78ddf4be573b7c73d9c53f8665dbf9870bf65938c4a302f95674244c7a3e6fe8563c1c18120d504822d39a32363efe5

汇元返回的json消息【带SM2签名】: { "msg": "无效参数", "code": 40002, "sub_msg": "参数【biz_content】无效,请传入json对象", "sub_code": "invalid_param", "sign": "jhs+CGKfo2htaq/VSzehsPdBjZ2UAy+i65FXS12xMlSsne/ZpKDicPvcWQPMyeAqvRFZb9pAntl+5sAPYaDwBA==" }
变换成签名检查的格式: code=40002&msg=无效参数&sub_code=invalid_param&sub_msg=参数【biz_content】无效,请传入json对象
汇元签名【base64格式】: jhs+CGKfo2htaq/VSzehsPdBjZ2UAy+i65FXS12xMlSsne/ZpKDicPvcWQPMyeAqvRFZb9pAntl+5sAPYaDwBA==
汇元签名【转成HEX格式，长度为128】: 8e1b3e08629fa3686d6aafd54b37a1b0f7418d9d94032fa2eb91574b5db13254ac9defd9a4a0e270fbdc5903ccc9e02abd11596fda409ed97ee6c00f61a0f004
调用convert_sign_rs_to_pkcs1转换成pkcs1的格式: 30460221008e1b3e08629fa3686d6aafd54b37a1b0f7418d9d94032fa2eb91574b5db13254022100ac9defd9a4a0e270fbdc5903ccc9e02abd11596fda409ed97ee6c00f61a0f004
验签结果: bool(true)
```

## PHP国密算法资源

[GitHub - GmSSL/GmSSL-PHP: PHP binding to the GmSSL library.](https://github.com/GmSSL/GmSSL-PHP) 这个需要自己来编译扩展，windows环境下不易处理，Linux环境下按照Readme说明处理应该不难（不过有没有坑没试过）。

[孙豫晋/gmssl234 国密算法整理](https://gitee.com/sunyujin/gmssl234) 这个网站代码比较可行，DEMO是按照这个来写的。

[PHP国密SM2\SM4对接Java SM2SignWithSM3 (招商银行云直联) - 佚小名 - 博客园](https://www.cnblogs.com/blog-dyn/p/16301425.html)

[ASN.1数据查看工具](http://lapo.it/asn1js) SM2公私钥数据（PKCS1,PKCS8格式的）可以通过该工具进行分析查看
