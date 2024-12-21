<?php
namespace app\agent\controller;

use app\activity\info\PermissionActivity;
use app\article\info\PermissionArticle;
use app\baidu\info\PermissionBaidu;
use app\bargain\info\PermissionBargain;
use app\boss\info\PermissionBoss;
use app\closure\info\PermissionClosure;
use app\house\info\PermissionHouse;
use app\livevideo\info\PermissionLivevideo;
use app\member\info\PermissionMember;
use app\overlord\info\PermissionOverlord;
use app\payclass\info\PermissionPayclass;
use app\question\info\PermissionQuestion;
use app\restaurant\info\PermissionRestaurant;
use app\passenger\info\PermissionPassenger;
use app\redbag\info\PermissionRedbag;
use app\reduction\info\PermissionReduction;
use app\shortvideo\info\PermissionShortvideo;
use think\App;
use app\AdminRest;
use app\AgentRest;
use think\facade\Config;

class PermissionContrller extends AgentRest
{
    public function __construct ( App $app ){
        parent::__construct( $app );
        if ($this->_user['role_name'] != 'admin') {
            echo json_encode(['code' => 401, 'error' => lang('Permission denied')]);
            exit;
        }
    }
    public function getAgentPermission()
    {
       $is_super_admin = ($this->_role == 'admin') ? true : false;

        if (!$is_super_admin) {
            return $this->error('非法请求， 请联系超级管理员');
        }

        $is_we7 = defined('IS_WEIQIN');

        $permissionArticle   = new PermissionArticle(0);

        $permissionBoss      = new PermissionBoss(0);

        $permissionBaidu     = new PermissionBaidu(0);
        //截流
        $permissionClosure   = new PermissionClosure(0);
        //带客有礼
        $permissionPassenger = new PermissionPassenger(0);
        //新客福包
        $permissionRedbag    = new PermissionRedbag(0);
        //满减
        $permissionReduction = new PermissionReduction(0);
        //直播
        $permissionLivevideo = new PermissionLivevideo(0);
        //短视频
        $permissionShortvideo = new PermissionShortvideo(0);
		// 会员等级
        $permissionMember = new PermissionMember(0);
		// 餐饮
        $permissionRestaurant = new PermissionRestaurant(0);
        //霸王餐
        $permissionOverlord = new PermissionOverlord(0);
        //付费课程
        $permissionPayclass = new PermissionPayclass(0);

        $permissionBargain = new PermissionBargain(0);

        $permissionQuestion = new PermissionQuestion(0);

        $has_article   = $permissionArticle->sAuth();

        $has_boss      = $permissionBoss->sAuth();

        if($has_boss==true){

            $has_boss = $permissionBoss->getSaasValue()>0?true:false;

        }

        $has_baidu     = $permissionBaidu->sAuth();
        //截流
        $has_closure   = $permissionClosure->sAuth();
        //带客有礼
        $has_passenger = $permissionPassenger->sAuth();
        //新客户包
        $has_redbag    = $permissionRedbag->sAuth();
        //满减
        $has_reduction = $permissionReduction->sAuth();
        //直播
        $has_livevideo = $permissionLivevideo->sAuth();
        //短视频
        $has_shortvideo= $permissionShortvideo->sAuth();
        //会员
        $has_member    =  $permissionMember->sAuth();
        //餐饮
        $has_restaurant  =  $permissionRestaurant->sAuth();
		// 霸王餐
        $has_overlord    =  $permissionOverlord->sAuth();
		// 付费课程
        $has_payclass    =  $permissionPayclass->sAuth();
        //临时写法====  By.jingshuixian
        $permissionHouse  = new PermissionHouse(0);

        $has_house        = $permissionHouse->sAuth();

        $permissionActivity = new PermissionActivity(0);

        $has_activity       = $permissionActivity->sAuth();

        $has_bargain       = $permissionBargain->sAuth();

        $has_question      = $permissionQuestion->sAuth();


        if(APP_MODEL_NAME=='longbing_restaurant'){

            $has_restaurant = false;
        }

        if(APP_MODEL_NAME=='longbing_shortvideo'){

            $has_shortvideo = false;
        }

        if(APP_MODEL_NAME=='longbing_member'){

            $has_member    = false;
        }

        if(APP_MODEL_NAME=='longbing_house'){

            $has_house    = false;
        }

        if(APP_MODEL_NAME=='longbing_liveshop'){

            $has_livevideo    = false;
        }

$all_meta_json = <<<AGENT

[
    {
        "path":"/",
        "name":"Auth",
        "component":"Layout",
        "hidden":false,
        "redirect":"/auth",
        "meta":{
            "menuName":"AuthManage",
            "icon":"iconauthorise"
        },
        "children":[
            {
                "path":"auth",
                "name":"AuthManage",
                "component":"/auth",
                "hidden":false,
                "meta":{
                    "keepAlive":true,
                    "menuName":"AuthManage",
                    "icon":"iconauthorise",
                    "url":"",
                    "pagePermission":[
                    ]
                }
            },
            {
                "path":"editAuth",
                "name":"EditAuth",
                "component":"/editAuth",
                "hidden":true,
                "meta":{
                    "keepAlive":false,
                    "menuName":"EditAuth",
                    "icon":"",
                    "url":"",
                    "pagePermission":[
                    ]
                }
            }
        ]
    },
    {
        "path": "/",
        "name": "AgentLevel",
        "component": "Layout",
        "hidden": false,
        "redirect": "/agentLevel",
        "meta": {
            "menuName": "AgentLevelManage",
            "icon": "iconAgentLevel"
        },
        "children": [
            {
                "path": "agentLevel",
                "name": "AgentLevelManage",
                "component": "/agentLevel",
                "hidden": false,
                "meta": {
                    "keepAlive": true,
                    "menuName": "AgentLevelManage",
                    "icon": "iconAgentLevel",
                    "url": "",
                    "pagePermission": [
                    ]
                }
            },
            {
                "path": "editAgentLevel",
                "name": "EditAgentLevel",
                "component": "/editAgentLevel",
                "hidden": true,
                "meta": {
                    "keepAlive": false,
                    "menuName": "EditAgentLevel",
                    "icon": "",
                    "url": "",
                    "pagePermission": [
                    ]
                }
            }
        ]
    },
    {
        "path": "/",
        "name": "AgentAuth",
        "component": "Layout",
        "hidden": false,
        "redirect": "/agentAuth",
        "meta": {
            "menuName": "AgentAuthManage",
            "icon": "iconagentauth"
        },
        "children": [
            {
                "path": "agentAuth",
                "name": "AgentAuthManage",
                "component": "/agentAuth",
                "hidden": false,
                "meta": {
                    "keepAlive": true,
                    "menuName": "AgentAuthManage",
                    "icon": "iconagentauth",
                    "url": "",
                    "pagePermission": [
                    ]
                }
            },
            {
                "path": "editAgentAuth",
                "name": "EditAgentAuth",
                "component": "/editAgentAuth",
                "hidden": true,
                "meta": {
                    "keepAlive": false,
                    "menuName": "EditAgentAuth",
                    "icon": "",
                    "url": "",
                    "pagePermission": [
                    ]
                }
            }
        ]
    },
    {
        "path":"/copyright",
        "name":"Copyright",
        "component":"Layout",
        "redirect":"/copyright/index",
        "hidden":false,
        "meta":{
            "menuName":"CopyrightManage",
            "icon":"iconbanquan"
        },
        "children":[
            {
                "path":"index",
                "name":"CopyrightIndex",
                "component":"/copyright",
                "hidden":false,
                "meta":{
                    "keepAlive":true,
                    "menuName":"CopyrightManage",
                    "icon":"iconbanquan",
                    "url":"",
                    "pagePermission":[
                    ]
                }
            },
            {
                "path":"edit",
                "name":"EditCopyright",
                "component":"/editCopyright",
                "hidden":true,
                "meta":{
                    "keepAlive":false,
                    "menuName":"EditCopyright",
                    "icon":"",
                    "url":"",
                    "pagePermission":[
                    ]
                }
            }
        ]
    }
    ,
    {
        "path": "/",
        "name": "Upload",
        "component": "Layout",
        "hidden": false,
        "redirect": "/upload",
        "meta": {
            "menuName": "UploadManage",
            "icon": "iconuploadset"
        },
        "children": [
            {
                "path": "upload",
                "name": "UploadManage",
                "component": "/upload",
                "hidden": false,
                "meta": {
                    "keepAlive": true,
                    "menuName": "UploadManage",
                    "icon": "iconuploadset",
                    "url": "",
                    "pagePermission": [
                    ]
                }
            },
            {
                "path": "editUpload",
                "name": "EditUpload",
                "component": "/editUpload",
                "hidden": true,
                "meta": {
                    "keepAlive": false,
                    "menuName": "EditUpload",
                    "icon": "",
                    "url": "",
                    "pagePermission": [
                    ]
                }
            }
        ]
    },
    {
        "path":"/default",
        "name":"Default",
        "component":"Layout",
        "redirect":"/default/setting",
        "hidden":false,
        "meta":{
            "menuName":"DefaultSetting",
            "icon":"iconshezhi"
        },
        "children":[
            {
                "path":"setting",
                "name":"DefaultSetting",
                "component":"/default",
                "hidden":false,
                "meta":{
                    "keepAlive":false,
                    "menuName":"DefaultSetting",
                    "icon":"iconshezhi",
                    "url":"",
                    "pagePermission":[
                    ]
                }
            }
        ]
    },
    {
        "path":"/chat",
        "name":"Chat",
        "component":"Layout",
        "redirect":"/chat/record",
        "hidden":false,
        "meta":{
            "menuName":"ChatRecord",
            "icon":"iconliaotian"
        },
        "children":[
            {
                "path":"record",
                "name":"ChatRecord",
                "component":"/chatRecord",
                "hidden":false,
                "meta":{
                    "keepAlive":true,
                    "menuName":"ChatRecord",
                    "icon":"iconliaotian",
                    "url":"",
                    "pagePermission":[
                    ]
                }
            }
        ]
    },
    {
        "path":"/article",
        "name":"Article",
        "component":"Layout",
        "redirect":"/article/index",
        "hidden":false,
        "meta":{
            "menuName":"Article",
            "icon":"iconwenzhangguanli"
        },
        "children":[
            {
                "path":"index",
                "name":"ArticleIndex",
                "component":"/article",
                "hidden":false,
                "meta":{
                    "keepAlive":true,
                    "menuName":"Article",
                    "icon":"iconwenzhangguanli",
                    "url":"",
                    "pagePermission":[
                    ]
                }
            }
        ]
    },
    {
        "path":"/activity",
        "name":"Activity",
        "component":"Layout",
        "redirect":"/activity/index",
        "hidden":false,
        "meta":{
            "menuName":"Activity",
            "icon":"iconhuodongyingxiao"
        },
        "children":[
            {
                "path":"index",
                "name":"ActivityIndex",
                "component":"/activity",
                "hidden":false,
                "meta":{
                    "keepAlive":true,
                    "menuName":"Activity",
                    "icon":"iconhuodongyingxiao",
                    "url":"",
                    "pagePermission":[
                    ]
                }
            }
        ]
    },
    {
        "path":"/house",
        "name":"House",
        "component":"Layout",
        "redirect":"/house/index",
        "hidden":false,
        "meta":{
            "menuName":"House",
            "icon":"iconfangchan01"
        },
        "children":[
            {
                "path":"index",
                "name":"HouseIndex",
                "component":"/house",
                "hidden":false,
                "meta":{
                    "keepAlive":true,
                    "menuName":"House",
                    "icon":"iconfangchan01",
                    "url":"",
                    "pagePermission":[
                    ]
                }
            }
        ]
    },
    {
        "path":"/baidu",
        "name":"Baidu",
        "component":"Layout",
        "redirect":"/baidu/index",
        "hidden":false,
        "meta":{
            "menuName":"Baidu",
            "icon":"iconicon_baidulogo"
        },
        "children":[
            {
                "path":"index",
                "name":"BaiduIndex",
                "component":"/baidu",
                "hidden":false,
                "meta":{
                    "keepAlive": true,
                    "menuName":"Baidu",
                    "icon":"iconicon_baidulogo",
                    "url": "",
                    "pagePermission":[
                    ]
                }
            }
        ]
    },
    {
        "path": "/closure",
        "name": "Closure",
        "component": "Layout",
        "redirect": "/closure/index",
        "hidden":false,
        "meta": {
            "menuName": "Closure",
            "icon": "icon_closure"
        },
        "children": [
            {
                "path": "index",
                "name": "ClosureIndex",
                "component": "/closure",
                "hidden": false,
                "meta": {
                    "keepAlive": true,
                    "menuName": "Closure",
                    "icon": "icon_closure",
                    "url": "",
                    "pagePermission": [
                    ]
                }
            }]
    },
    {
        "path": "/passenger",
        "name": "Passenger",
        "component": "Layout",
        "redirect": "/passenger/index",
        "hidden": false,
        "meta": {
            "menuName": "Passenger",
            "icon": "icon_passenger"
        },
        "children": [
            {
                "path": "index",
                "name": "PassengerIndex",
                "component": "/passenger",
                "hidden": false,
                "meta": {
                    "keepAlive": true,
                    "menuName": "Passenger",
                    "icon": "icon_passenger",
                    "url": "",
                    "pagePermission": [
                    ]
                }
            }]
    },
    {
        "path": "/cash",
        "name": "Cash",
        "component": "Layout",
        "redirect": "/cash/index",
        "hidden": false,
        "meta": {
            "menuName": "Cash",
            "icon": "iconxinkefuli"
        },
        "children": [
            {
                "path": "index",
                "name": "CashIndex",
                "component": "/cash",
                "hidden": false,
                "meta": {
                    "keepAlive": true,
                    "menuName": "Cash",
                    "icon": "iconxinkefuli",
                    "url": "",
                    "pagePermission": [
                    ]
                }
            }]
    }
    ,
    {
        "path": "/reduction",
        "name": "Reduction",
        "component": "Layout",
        "redirect": "/reduction/index",
        "hidden": false,
        "meta": {
            "menuName": "Reduction",
            "icon": "iconreduction"
        },
        "children": [
            {
                "path": "index",
                "name": "ReductionIndex",
                "component": "/reduction",
                "hidden": false,
                "meta": {
                    "keepAlive": true,
                    "menuName": "Reduction",
                    "icon": "iconreduction",
                    "url": "",
                    "pagePermission": [
                    ]
                }
            }]
    },
    {
        "path": "/live",
        "name": "Live",
        "component": "Layout",
        "redirect": "/live/index",
        "hidden": false,
        "meta": {
            "menuName": "Live",
            "icon": "iconlive"
        },
        "children": [
            {
                "path": "index",
                "name": "LiveIndex",
                "component": "/live",
                "hidden": false,
                "meta": {
                    "keepAlive": true,
                    "menuName": "Live",
                    "icon": "iconlive",
                    "url": "",
                    "pagePermission": [
                    ]
                }
            }]
    },
    {
        "path": "/shortvideo",
        "name": "Shortvideo",
        "component": "Layout",
        "redirect": "/shortvideo/index",
        "hidden": false,
        "meta": {
            "menuName": "Shortvideo",
            "icon": "iconShortVideo"
        },
        "children": [
            {
                "path": "index",
                "name": "ShortvideoIndex",
                "component": "/shortvideo",
                "hidden": false,
                "meta": {
                    "keepAlive": true,
                    "menuName": "Shortvideo",
                    "icon": "iconShortVideo",
                    "url": "",
                    "pagePermission": [
                    ]
                }
            }]
    },
    {
        "path":"/company",
        "name":"Company",
        "component":"Layout",
        "redirect":"/company/index",
        "hidden":false,
        "meta":{
            "menuName":"Company",
            "icon":"icongongsikehuguanli"
        },
        "children":[
            {
                "path":"index",
                "name":"CompanyIndex",
                "component":"/company",
                "hidden":false,
                "meta":{
                    "keepAlive":true,
                    "menuName":"Company",
                    "icon":"icongongsikehuguanli",
                    "url":"",
                    "pagePermission":[
                    ]
                }
            }
        ]
    },
    {
        "path":"/vip",
        "name":"Member",
        "component":"Layout",
        "redirect":"/member/index",
        "hidden":false,
        "meta":{
            "menuName":"Member",
            "icon":"iconreduction"
        },
        "children":[
            {
                "path":"index",
                "name":"MemberIndex",
                "component":"/member",
                "hidden":false,
                "meta":{
                    "keepAlive":true,
                    "menuName":"Member",
                    "icon":"iconMember",
                    "url":"",
                    "pagePermission":[
                    ]
                }
            }]
    },
    {
        "path":"/restaurant",
        "name":"Restaurant",
        "component":"Layout",
        "redirect":"/restaurant/index",
        "hidden":false,
        "meta":{
            "menuName":"Restaurant",
            "icon":"iconcanyin1"
        },
        "children":[
            {
                "path":"index",
                "name":"RestaurantIndex",
                "component":"/restaurant",
                "hidden":false,
                "meta":{
                    "keepAlive":true,
                    "menuName":"Restaurant",
                    "icon":"iconcanyin1",
                    "url":"",
                    "pagePermission":[
                    ]
                }
            }]
    },
    {
        "path": "/overlord",
        "name": "Overlord",
        "component": "Layout",
        "redirect": "/overlord/index",
        "hidden": false,
        "meta": {
            "menuName": "Overlord",
            "icon": "iconhuodongyingxiao"
        },
        "children": [{
            "path": "index",
            "name": "OverlordIndex",
            "component": "/overlord",
            "hidden": false,
            "meta": {
                "keepAlive": true,
                "menuName": "Overlord",
                "icon": "iconhuodongyingxiao",
                "url": "",
                "pagePermission": []
            }
        }]
    }
    ,
    {
         "path": "/bargain",
         "name": "Bargain",
         "component": "Layout",
         "redirect": "/bargain/index",
         "hidden": false,
         "meta": {
          "menuName": "Bargain",
          "icon": "iconkanjiajilu"
         },
         "children": [{
          "path": "index",
          "name": "BargainIndex",
          "component": "/bargain",
          "hidden": false,
          "meta": {
           "keepAlive": true,
           "menuName": "Bargain",
           "icon": "iconkanjiajilu",
           "url": "",
           "pagePermission": []
          }
         }]
        },
    {
        "path": "/payclass",
        "name": "Payclass",
        "component": "Layout",
        "redirect": "/payclass/index",
        "hidden": false,
        "meta": {
            "menuName": "Payclass",
            "icon": "iconcourse"
        },
        "children": [{
            "path": "index",
            "name": "PayclassIndex",
            "component": "/payclass",
            "hidden": false,
            "meta": {
                "keepAlive": true,
                "menuName": "Payclass",
                "icon": "iconcourse",
                "url": "",
                "pagePermission": []
            }
        }]
    },
    {
         "path": "/questionnaire",
         "name": "Questionnaire",
         "component": "Layout",
         "redirect": "/questionnaire/index",
         "hidden": false,
         "meta": {
          "menuName": "Questionnaire",
          "icon": "iconrenwu"
         },
         "children": [{
          "path": "index",
          "name": "QuestionnaireIndex",
          "component": "/questionnaire",
          "hidden": false,
          "meta": {
           "keepAlive": true,
           "menuName": "Questionnaire",
           "icon": "iconrenwu",
           "url": "",
           "pagePermission": []
          }
         }]
        },
    {
        "path":"/account",
        "name":"Account",
        "component":"Layout",
        "redirect":"/account/index",
        "hidden":false,
        "meta":{
            "menuName":"Account",
            "icon":""
        },
        "children":[
            {
                "path":"index",
                "name":"AccountIndex",
                "component":"/account",
                "hidden":false,
                "meta":{
                    "keepAlive":true,
                    "menuName":"AccountManage",
                    "icon":"iconshezhi",
                    "url":"",
                    "pagePermission":[
                    ]
                }
            },
            {
                "path":"edit",
                "name":"EditAccount",
                "component":"/editAccount",
                "hidden":true,
                "meta":{
                    "keepAlive":false,
                    "menuName":"EditAccount",
                    "icon":"iconshezhi",
                    "url":"",
                    "pagePermission":[
                    ]
                }
            }
        ]
    },
    {
        "path":"/site",
        "name":"Site",
        "component":"Layout",
        "redirect":"/account/index",
        "hidden":false,
        "meta":{
            "menuName":"Site",
            "icon":""
        },
        "children":[
            {
                "path":"index",
                "name":"SiteBind",
                "component":"/site",
                "hidden":false,
                "meta":{
                    "menuName":"SiteBind",
                    "icon":"iconqiu",
                    "pagePermission":[
                    ]
                }
            }
        ]
    }
]

AGENT;


        $all_meta = json_decode($all_meta_json, true);

        foreach ($all_meta as $k => $v) {
            $name = $v['name'];

            switch ($name) {
                case  'Article' :
                    if (!$has_article)  unset($all_meta[$k]);
                    break;
                case  'Activity' :
                    if (!$has_activity)  unset($all_meta[$k]);
                    break;
                case  'House' :
                    if (!$has_house)  unset($all_meta[$k]);
                    break;
                case  'Company' :
                    if (!$has_boss)  unset($all_meta[$k]);
                    break;
                case  'Account' :
                    if ($is_we7)  unset($all_meta[$k]);
                    break;
                case 'Default' :
                    if (!$is_we7)  unset($all_meta[$k]);
                    break;
                case 'Baidu' :
                    if (!$has_baidu)  unset($all_meta[$k]);
                    break;
                case 'Closure' :
                    if (!$has_closure)  unset($all_meta[$k]);
                    break;
                case 'Passenger' :
                    if (!$has_passenger)  unset($all_meta[$k]);
                    break;
                case 'Cash' :
                    if (!$has_redbag)  unset($all_meta[$k]);
                    break;
                case 'Reduction' :
                    if (!$has_reduction)  unset($all_meta[$k]);
                    break;
                case 'Live' :
                    if (!$has_livevideo)  unset($all_meta[$k]);
                    break;
                case 'Shortvideo':
                    if (!$has_shortvideo)  unset($all_meta[$k]);
                    break;
                case 'Member':
                    if (!$has_member)  unset($all_meta[$k]);
                    break;
                case 'Restaurant':
                    if (!$has_restaurant)  unset($all_meta[$k]);
                    break;
                case 'Overlord':
                    if (!$has_overlord)  unset($all_meta[$k]);
                    break;
                case 'Payclass':
                    if (!$has_payclass)  unset($all_meta[$k]);
                    break;
                case 'Bargain':
                    if (!$has_bargain)  unset($all_meta[$k]);
                    break;
                case 'Questionnaire':
                    if (!$has_question)  unset($all_meta[$k]);
                    break;
            }
        }


        return $this->success(array_values($all_meta));
    }


}