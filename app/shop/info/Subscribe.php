<?php
// +----------------------------------------------------------------------
// | Longbing [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright Chengdu longbing Technology Co., Ltd.
// +----------------------------------------------------------------------
// | Website http://longbing.org/
// +----------------------------------------------------------------------
// | Sales manager: +86-13558882532 / +86-13330887474
// | Technical support: +86-15680635005
// | After-sale service: +86-17361005938
// +----------------------------------------------------------------------

declare(strict_types=1);

namespace app\shop\info;

use app\bargain\info\PermissionBargain;
use app\card\model\CardExtension;
use app\radar\model\RadarOrder;
use app\shop\model\IndexShopCollage;
use longbingcore\diy\BaseSubscribe;

/**
 * @author shuixian
 * @DataTime: 2019/12/11 16:23
 * Class Subscribe
 * @package app\ucenter\info
 */
class Subscribe extends BaseSubscribe
{

    /**
     * 相应个人中心工具菜单
     *
     * @return mixed
     * @author shuixian
     * @DataTime: 2019/12/12 11:24
     */
    public function onAddWorkCenterModelMenu()
    {

        $permissson = new PermissionShop($this->_uniacid);
        if($permissson->pAuth()) {

            $modelMenu = [
                "title" => '商品服务',
                "desc" => '',
                "show" => true,
                "row" => 4,
                "list" => [
                    [
                        "title" => "我的收入",
                        "icon" => "icontixianguanli",
                        "link" => "/shop/pages/partner/income",
                        "linkType" => 4
                    ],
                    [
                        "title" => "订单管理",
                        "icon" => "iconwodedingdan",
                        "link" => "/shop/pages/order/list?target=staff",
                        "linkType" => 4
                    ],
                    [
                        "title" => "退款管理",
                        "icon" => "iconwodeshouhou",
                        "link" => "/shop/pages/refund/list?target=staff",
                        "linkType" => 4
                    ],
                    [
                        "title" => "推荐商品",
                        "icon" => "icontuijianshangpin",
                        "link" => "/shop/pages/staff/goods/push",
                        "linkType" => 4
                    ],
                    [
                        "title" => "卡券管理",
                        "icon" => "iconwodekaquan",
                        "link" => "/shop/pages/staff/coupon/list",
                        "linkType" => 4
                    ]
                ]
            ];

            return [$modelMenu];
        }
        return [];

    }

    /**
     * 名片展示页获取其他模块数据
     *
     * @param $params
     * @return array
     * @author shuixian
     * @DataTime: 2019/12/24 14:24
     */
    public function onCardInfo($params)
    {

        //获取推荐商品 By.jingshuixian
        $modelExtension = new CardExtension();
        $goods_list = $modelExtension->cardExtensionList($params['staff_id'], $this->_uniacid);

        $collage_model = new IndexShopCollage();
        foreach ($goods_list as $key => $val) {
            $goods_list[$key]['is_collage'] = 0;
            $count = $collage_model->getCollage(['goods_id' => $val['id'], 'uniacid' => $this->_uniacid, 'status' => 1]);
            if (!empty($count)) $goods_list[$key]['is_collage'] = 1;
        }

        return ['goods_list'=>$goods_list];
    }

    /**
     * 客户获取列表查询
     *
     * @param $data
     * @return array
     * @author shuixian
     * @DataTime: 2019/12/26 10:30
     */
    public function onStaffCustomerList($data)
    {
        //  商城订单
        $orderCount  = RadarOrder::where( [ [ 'pay_status', '=', 1 ],
                [ 'order_status', '<>', 1 ],
                [ 'user_id', '=', $data[ 'uid' ] ],
                [ 'to_uid', '=', $data[ 'to_uid' ]],
                [ 'refund_status', '=', 0 ] ]
        )
            ->count();

        $returnData[ 'count' ] = $orderCount;
        $returnData[ 'title' ] = "订单";

        return [$returnData];
    }


    /**
     * @param $data
     * @功能说明:处理一下数据 主要是权限方面的
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-12-16 16:21
     */
    public function onDiyModuleMenuShop($data){


        if(!empty($data['data']['list'])){

            foreach ($data['data']['list'] as $v){

                if($v['icon']!='icontemplate'||$data['shop_auth']==true){

                    $arr[] = $v;
                }

            }
            $data['data']['list'] = $arr;
        }

        return $data;
    }


    /**
     * 监听用户中心模块
     *
     * @return array
     * @author shuixian
     * @DataTime: 2019/12/18 14:04
     */
    public function onAddUcenterCompoent(){
        $this->getUserId();

        $user = longbingGetUserInfo($this->getUserId() , $this->_uniacid);

        $last_staff_id = !empty($user['last_staff_id'])?$user['last_staff_id']:0;

        $moduleMenuShopOrder = <<<COMPOENT
{
    "title": "商城订单",
    "type": "moduleMenuShopOrder",
    "icon": "iconshoporder",
    "isDelete": true,
    "addNumber": 1,
    "attr": [
        {
            "title": "模板名称",
            "type": "Switch",
            "name": "isShowTitle"
        },
        {
            "title": "选择模板",
            "type": "ChooseModule",
            "name": "module",
            "data": [
                {
                    "title": "一行多列",
                    "name": "module-menu-row",
                    "img": "http://longbingcdn.xiaochengxucms.com/admin/diy/module-menu-col.jpg"
                },
                {
                    "title": "一行一列",
                    "name": "module-menu-col",
                    "img": "http://longbingcdn.xiaochengxucms.com/admin/diy/module-menu-row.jpg"
                }
            ]
        },
        {
            "title": "一行多少列",
            "type": "InputNumber",
            "name": "row"
        }
    ],
    "data": {
        "isShowTitle": false,
        "module": "module-menu-row",
        "row": {
            "number": 4,
            "min": 2,
            "max": 5,
            "label": "请输入"
        },
        "list": [
            {
                "title": "全部",
                "icon": "iconwodedingdan",
                "link": {
                    "type": 2,
                    "url": "/shop/pages/order/list?index=0"
                }
            },
            {
                "title": "待付款",
                "icon": "icondingdandaifukuan",
                "link": {
                    "type": 2,
                    "url": "/shop/pages/order/list?index=1"
                }
            },
            {
                "title": "待发货",
                "icon": "icondingdandaifahuo",
                "link": {
                    "type": 2,
                    "url": "/shop/pages/order/list?index=2"
                }
            },
            {
                "title": "待收货",
                "icon": "icondingdandaishouhuo",
                "link": {
                    "type": 2,
                    "url": "/shop/pages/order/list?index=3"
                }
            },
            {
                "title": "已完成",
                "icon": "icondingdanyiwancheng",
                "link": {
                    "type": 2,
                    "url": "/shop/pages/order/list?index=4"
                }
            }
            
        ]
    }
}
COMPOENT;

        $tmp = sassAuth()==1?',
            {
            
                "title": "我的采购模板",
                
                "icon": "icontemplate",
                
                "link": {
                
                    "type": 2,
                    
                    "url": "/shop/pages/purchase/list?staff_id='.$last_staff_id.'"'.'
                }
            }':'';



        $bargain_p = new PermissionBargain($this->_uniacid);

        $bargain_auth = $bargain_p->pAuth();

        $bargain = $bargain_auth==true?',
            {
                "title": "我的砍价",
                "icon": "iconkanjiajilu",
                "link": {
                    "type": 2,
                    "url": "/shop/pages/bargain/record"
                }
            }':'';

        $moduleMenuShop = <<<COMPOENT
{
    "title": "商城工具",
    "type": "moduleMenuShop",
    "icon": "iconshop",
    "isDelete": true,
    "addNumber": 1,
    "attr": [
        {
            "title": "模板名称",
            "type": "Switch",
            "name": "isShowTitle"
        },
        {
            "title": "选择模板",
            "type": "ChooseModule",
            "name": "module",
            "data": [
                {
                    "title": "一行多列",
                    "name": "module-menu-row",
                    "img": "http://longbingcdn.xiaochengxucms.com/admin/diy/module-menu-col.jpg"
                },
                {
                    "title": "一行一列",
                    "name": "module-menu-col",
                    "img": "http://longbingcdn.xiaochengxucms.com/admin/diy/module-menu-row.jpg"
                }
            ]
        },
        {
            "title": "一行多少列",
            "type": "InputNumber",
            "name": "row"
        }
    ],
    "data": {
        "isShowTitle": false,
        "module": "module-menu-row",
        "row": {
            "number": 4,
            "min": 2,
            "max": 5,
            "label": "请输入"
        },
        "list": [
            {
                "title": "我的售后",
                "icon": "iconwodeshouhou",
                "link": {
                    "type": 2,
                    "url": "/shop/pages/refund/list"
                }
            },
            {
                "title": "我的收入",
                "icon": "icontixianguanli",
                "link": {
                    "type": 2,
                    "url": "/shop/pages/partner/income"
                }
            },
            {
                "title": "我的卡券",
                "icon": "iconwodekaquan",
                "link": {
                    "type": 2,
                    "url": "/shop/pages/coupon/list"
                }
            },
            {
                "title": "分销商品",
                "icon": "iconquanmianfenxiao",
                "link": {
                    "type": 2,
                    "needStaffId": true,
                    "url": "/shop/pages/partner/distribution?staff_id=$last_staff_id"
                }
            },
            {
                "title": "我的地址",
                "icon": "icondizhi2",
                "link": {
                    "type": 2,
                    "url": "/shop/pages/address/list"
                }
            }$bargain
            $tmp
        ]
    }

}
COMPOENT;


        $permission = new PermissionShop($this->_uniacid);
        $compoentList = [] ;
        if($permission->pAuth()){
            $compoentList = [
                json_decode($moduleMenuShopOrder, true),
                json_decode($moduleMenuShop, true)
            ] ;
        }

        return $compoentList ;
    }



    /**
     * 监听代理管理端授权小程序事件
     *
     * @param $data
     * @return array
     * @author shuixian
     * @DataTime: 2019/12/27 17:33
     */
    public function onAgentAppAuthEdit($config){


        $returnArr =  [] ;

        $permission = new PermissionShop(0);
        $shop_switch = [] ;
        if($permission->sAuth() && $permission->infoConfig['auth_platform'] ) {

            $shop_switch['formType'] = 'radio';
            $shop_switch['name'] = 'shop_switch';

            $shop_switch['value'] = $config ? $config[ $shop_switch['name'] ] : 0;
            $shop_switch['title'] = $permission->info['title'];
            $returnArr[] = $shop_switch;
        }


        $pay_shop = [] ;
        if($permission->sAuth() && $permission->infoConfig['auth_platform'] ) {

            $pay_shop['formType'] = 'radio';
            $pay_shop['name'] = 'pay_shop';

            $pay_shop['value'] = $config ? $config[ $pay_shop['name'] ] : 0;
            $pay_shop['title'] = $permission->info['title'].'支付';
            $returnArr[] = $pay_shop;
        }

        return $returnArr ;
    }


}