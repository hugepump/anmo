<?php

namespace app\card\controller;

use app\admin\service\UpdateService;
use app\AdminRest;
use app\agent\model\Cardauth2ConfigModel;
use app\card\model\CardAuth2Activity;
use app\card\model\CardAuth2Article;
use app\card\model\CardBoss;
use app\card\model\CardCount;
use app\card\model\CardExtension;
use app\card\model\CardForward;
use app\card\model\CardJob;
use app\card\model\CardMessage;
use app\card\model\CardTags;
use app\card\model\CardValue;
use app\card\model\Collection;
use app\card\model\Company;
use app\card\model\Config;
use app\card\model\DefaultSetting;
use app\card\model\User;
use app\card\model\UserFollow;
use app\card\model\UserInfo;
use app\card\model\UserMark;
use app\card\service\UserService;
use app\diy\model\DiyModel;
use app\diy\service\DiyService;
use app\sendmsg\model\SendConfig;
use longbingcore\permissions\AdminMenu;
use longbingcore\permissions\Tabbar;
use think\App;
use think\facade\Db;
use think\process\exception\Failed;

class Admin extends AdminRest
{
    protected $modelCompany;
    protected $modelConfig;

    // 继承 验证用户登陆
    public function __construct ( App $app )
    {

        parent::__construct( $app );



        $this->modelCompany = new Company();
        $this->modelConfig  = new Config();

//        dump('ccc');exit;
    }

    /**
     * @Purpose: 后台概览页面
     *
     * @Method: GET
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function overview ()
    {
        $time        = time();
        $modelConfig = new Config();
        $config      = $modelConfig->where( [ [ 'uniacid', '=', $this->_uniacid ] ] )
            ->find();
        if ( !$config )
        {
            $modelConfig->initConfig( $this->_uniacid );
            $config = $modelConfig->where( [ [ 'uniacid', '=', $this->_uniacid ] ] )
                ->find();
        }


        $data = [];

        $exist = Db::query( 'show tables like "%longbing_card_config%"' );

        $auth_info = false;

        if ( !empty( $exist ) )
        {
            $auth_info = Db::name( 'longbing_cardauth2_config' )
                ->where( [ [ 'modular_id', '=', $this->_uniacid ] ] )
                ->find();
        }

        //  小程序版本
        $data[ 'version' ] = '多功能无限开版';
        if ( $this->card_auth_version )
        {
            $data[ 'version' ] = $this->card_auth_version . '开版';
        }

        //  已开员工名片数量
        $data[ 'card_number' ] = User::where( [ [ 'uniacid', '=', $this->_uniacid ] ] )
            ->count();

        //  可开通名片数量
        $data[ 'total_card_number' ] = '无限制';
        if ( $this->card_auth_card )
        {
            $data[ 'total_card_number' ] = $this->card_auth_card;
        }
        if ( $auth_info )
        {
            $data[ 'total_card_number' ] = $auth_info[ 'number' ];
        }
        if ( $exist && !$auth_info )
        {
            $existTmp = Db::query( 'show tables like "longbing_cardauth2_default"' );

            if ( $existTmp )
            {
                $auth_info_tmp = Db::name( 'longbing_cardauth2_default' )
                    ->find();
                if ( $auth_info_tmp )
                {
                    $data[ 'total_card_number' ] = $auth_info_tmp[ 'number' ];
                }
            }
        }

        //  小程序使用天数
        $data[ 'use_days' ]   = ( $time - $config[ 'create_time' ] ) / ( 24 * 60 * 60 );
        $data[ 'start_date' ] = date( 'Y年m月d日', $config[ 'create_time' ] );
        if ( $data[ 'use_days' ] < 0 )
        {
            $data[ 'use_days' ] = 0;
        }
        else
        {
            $data[ 'use_days' ] = ceil( $data[ 'use_days' ] );
        }

        //  小程序剩余可用天数 / 日期
        $data[ 'left_days' ] = [];
        if ( defined( 'IS_WEIQIN' ) && IS_WEIQIN )
        {
            $tmp = Db::name( 'account' )
                ->where( [ [ 'uniacid', '=', $this->_uniacid ] ] )
                ->find();
            if ( $tmp && $tmp[ 'endtime' ] )
            {
                $data[ 'left_days' ][] = $tmp[ 'endtime' ];
            }
        }

        if ( $auth_info )
        {
            $data[ 'left_days' ][] = $auth_info[ 'end_time' ];
        }

        if ( empty( $data[ 'left_days' ] ) )
        {
            $data[ 'left_days' ] = '无限制';
            $data[ 'left_date' ] = '无限制';
        }
        else
        {
            sort( $data[ 'left_days' ] );
            $data[ 'left_days' ] = $data[ 'left_days' ][ 0 ];
            $data[ 'left_date' ] = date( 'Y年m月d日', $data[ 'left_days' ] );
            $data[ 'left_days' ] = ( $time - $config[ 'left_days' ] ) / ( 24 * 60 * 60 );
            if ( $data[ 'left_days' ] < 0 )
            {
                $data[ 'left_days' ] = 0;
            }
            else
            {
                $data[ 'left_days' ] = ceil( $data[ 'left_days' ] );
            }
        }


        //  新增客户数量
        $last                    = 30;
        $data[ 'new_client_30' ] = [];

        for ( $i = 0; $i < $last; $i++ )
        {
            $beginTime = mktime( 0, 0, 0, date( 'm' ), date( 'd' ) - $i, date( 'Y' ) );
            $endTime   = mktime( 0, 0, 0, date( 'm' ), date( 'd' ) - $i + 1, date( 'Y' ) ) - 1;

            $number = User::where( [ [ 'uniacid', '=', $this->_uniacid ], [ 'create_time', 'BETWEEN', [ $beginTime, $endTime ] ] ]
            )
                ->count();


            $tmp = [ 'date' => date( 'm-d', $beginTime ), 'time' => $beginTime, 'number' => $number, ];
            array_push( $data[ 'new_client_30' ], $tmp );
        }

        $data[ 'new_client_7' ]  = array_slice( $data[ 'new_client_30' ], 0, 7 );
        $data[ 'new_client_15' ] = array_slice( $data[ 'new_client_30' ], 0, 15 );


        //咨询客户数量
        $last                        = 30;
        $data[ 'consult_client_30' ] = [];

        for ( $i = 0; $i < $last; $i++ )
        {
            $beginTime = mktime( 0, 0, 0, date( 'm' ), date( 'd' ) - $i, date( 'Y' ) );
            $endTime   = mktime( 0, 0, 0, date( 'm' ), date( 'd' ) - $i + 1, date( 'Y' ) ) - 1;

            $number = CardMessage::where( [ [ 'uniacid', '=', $this->_uniacid ],
                    [ 'create_time', 'BETWEEN', [ $beginTime, $endTime ] ] ]
            )
                ->group( 'user_id' )
                ->count();


            $tmp = [ 'date' => date( 'm-d', $beginTime ), 'time' => $beginTime, 'number' => $number, ];
            array_push( $data[ 'consult_client_30' ], $tmp );
        }

        $data[ 'consult_client_7' ]  = array_slice( $data[ 'consult_client_30' ], 0, 7 );
        $data[ 'consult_client_15' ] = array_slice( $data[ 'consult_client_30' ], 0, 15 );


        //  客户兴趣占比
        $interest_total = 0;
        //  对公司感兴趣
        $data[ 'interest_company' ][ 'number' ] = CardCount::whereOr( [ [ 'sign', '=', 'view' ], [ 'type', '=', 6 ],
                [ 'uniacid', '=', $this->_uniacid ] ]
        )
            ->count();

        $interest_total += $data[ 'interest_company' ][ 'number' ];


        //  对产品感兴趣
        $map1                                 = [ [ 'sign', '=', 'copy' ], [ 'type', '=', 2 ],
            [ 'uniacid', '=', $this->_uniacid ] ];
        $map2                                 = [ [ 'sign', '=', 'copy' ], [ 'type', '=', 1 ],
            [ 'uniacid', '=', $this->_uniacid ] ];
        $data[ 'interest_goods' ][ 'number' ] = CardCount::whereOr( [ $map1, $map2 ]
        )
            ->count();

        $interest_total += $data[ 'interest_goods' ][ 'number' ];


        //  对员工感兴趣
        $map1                                 = [ [ 'sign', '=', 'copy' ], [ 'uniacid', '=', $this->_uniacid ] ];
        $map2                                 = [ [ 'sign', '<>', 'praise' ], [ 'uniacid', '=', $this->_uniacid ] ];
        $data[ 'interest_staff' ][ 'number' ] = CardCount::whereOr( [ $map1, $map2 ]
        )
            ->count();

        $interest_total += $data[ 'interest_staff' ][ 'number' ];


        $data[ 'interest_company' ][ 'rate' ] = $data[ 'interest_company' ][ 'number' ] / $interest_total * 100;
        $data[ 'interest_company' ][ 'rate' ] = sprintf( "%.0f", $data[ 'interest_company' ][ 'rate' ] );

        $data[ 'interest_goods' ][ 'rate' ] = $data[ 'interest_goods' ][ 'number' ] / $interest_total * 100;
        $data[ 'interest_goods' ][ 'rate' ] = sprintf( "%.0f", $data[ 'interest_goods' ][ 'rate' ] );

        $data[ 'interest_staff' ][ 'rate' ] = $data[ 'interest_staff' ][ 'number' ] / $interest_total * 100;
        $data[ 'interest_staff' ][ 'rate' ] = sprintf( "%.0f", $data[ 'interest_staff' ][ 'rate' ] );


        $data[ 'staff_power' ] = $this->bossAi( $this->_uniacid );

        return $this->success( $data );
    }

    function bossAi ( $uniacid )
    {

        $default    = [ 'client'      => 0,        //  获客能力值
            'charm'       => 0,        //  个人魅力值
            'interaction' => 0,        //  客户互动值
            'product'     => 0,        //  产品推广值
            'website'     => 0,        //  官网推广度
            'active'      => 0,        //  销售主动性值
        ];
        $max        = [ 'client'      => 0,        //  获客能力值
            'charm'       => 0,        //  个人魅力值
            'interaction' => 0,        //  客户互动值
            'product'     => 0,        //  产品推广值
            'website'     => 0,        //  官网推广度
            'active'      => 0,        //  销售主动性值
        ];
        $staff_list = User::where( [ [ 'uniacid', '=', $uniacid ], [ 'is_staff', '=', 1 ] ] )
            ->field( [ 'id', 'nickName' ] )
            ->select()
            ->toArray();

        foreach ( $staff_list as $k => $v )
        {
            $info = UserInfo::where( [ [ 'uniacid', '=', $uniacid ], [ 'fans_id', '=', $v[ 'id' ] ] ] )
                ->field( [ 'name', 'avatar', 'phone', 'job_id' ] )
                ->find();


            $total = 0;

            $value = $this->bossGetAiValue( $v[ 'id' ], $uniacid );

            foreach ( $value as $k2 => $v2 )
            {
                if ( $v2[ 'value' ] > $max[ $k2 ] )
                {
                    $max[ $k2 ] = $v2[ 'value' ];
                }
                $total += $v2[ 'value' ];
            }

            $staff_list[ $k ][ 'value' ] = $value;
            $staff_list[ $k ][ 'total' ] = $total;
            $staff_list[ $k ][ 'info' ]  = $info;
        }

        //  二维数组排序
        array_multisort( array_column( $staff_list, 'total' ), SORT_DESC, $staff_list );

        $staff_list = array_splice( $staff_list, 0, 3 );


        $data = [ 'list' => $staff_list, 'max' => $max ];

        return $data;
    }

    function bossGetAiValue ( $id, $uniacid )
    {
        $value = [ 'client'      => 0,        //  获客能力值
            'charm'       => 0,        //  个人魅力值
            'interaction' => 0,        //  客户互动值
            'product'     => 0,        //  产品推广值
            'website'     => 0,        //  官网推广度
            'active'      => 0,        //  销售主动性值
        ];
        $check = CardValue::where( [ [ 'staff_id', '=', $id ] ] )
            ->find();

        if ( ( empty( $check ) ) || ( !empty( $check ) && $check[ 'update_time' ] - time() > 24 * 60 * 60 ) )
        {
            //  获客能力值
            $value[ 'client' ] = Collection::where( [ [ 'status', '=', 1 ], [ 'to_uid', '=', $id ] ] )
                ->count();

            //  个人魅力值
            $list1 = CardCount::where( [ [ 'type', '=', 'praise' ], [ 'type', '=', 1 ], [ 'to_uid', '=', $id ] ] )
                ->count();
            $list2 = CardCount::where( [ [ 'type', '=', 'praise' ], [ 'type', '=', 3 ], [ 'to_uid', '=', $id ] ] )
                ->count();
            $list3 = CardCount::where( [ [ 'type', '=', 'copy' ], [ 'to_uid', '=', $id ] ] )
                ->count();

            $count            = $list1 + $list2 + $list3;
            $value[ 'charm' ] = $count;

            //  客户互动值
            $list1 = CardMessage::where( [ [ 'user_id', '=', $id ] ] )
                ->count();
            $list2 = CardMessage::where( [ [ 'target_id', '=', $id ] ] )
                ->count();
            $list3 = CardCount::where( [ [ 'type', '=', 'view' ], [ 'to_uid', '=', $id ] ] )
                ->count();

            $count                  = $list1 + $list2 + $list3;
            $value[ 'interaction' ] = $count;

            //  产品推广值
            $list1 = CardExtension::where( [ [ 'user_id', '=', $id ] ] )
                ->count();
            $list2 = UserMark::where( [ [ 'staff_id', '=', $id ], [ 'mark', '=', 2 ] ] )
                ->count();
            $list3 = UserFollow::where( [ [ 'staff_id', '=', $id ] ] )
                ->count();

            $count              = $list1 + $list2 + $list3;
            $value[ 'product' ] = $count;

            //  官网推广度
            $list1              = CardCount::where( [ [ 'type', '=', 'view' ], [ 'to_uid', '=', $id ], [ 'type', '=', 6 ] ] )
                ->count();
            $list2              = CardForward::where( [ [ 'staff_id', '=', $id ], [ 'type', '=', 4 ] ] )
                ->count();
            $count              = $list1 + $list2;
            $value[ 'website' ] = $count;

            //  销售主动性值
            $list1 = CardMessage::where( [ [ 'user_id', '=', $id ] ] )
                ->count();
            $list2 = CardMessage::where( [ [ 'target_id', '=', $id ] ] )
                ->count();
            $list3 = UserFollow::where( [ [ 'staff_id', '=', $id ] ] )
                ->count();
            $list4 = UserMark::where( [ [ 'staff_id', '=', $id ] ] )
                ->count();

            $count             = $list1 + $list2 + $list3 + $list4;
            $value[ 'active' ] = $count;

            $insertData                  = $value;
            $insertData[ 'staff_id' ]    = $id;
            $time                        = time();
            $insertData[ 'create_time' ] = $time;
            $insertData[ 'update_time' ] = $time;
            $insertData[ 'uniacid' ]     = $uniacid;
            if ( empty( $check ) )
            {
                CardValue::create( $insertData );
            }
            else
            {
                $updateData                  = $value;
                $insertData[ 'update_time' ] = $time;
                CardValue::update( $updateData, [ 'id' => $check[ 'id' ] ] );
            }
        }
        else
        {
            $value = [ 'client'      => $check[ 'client' ],        //  获客能力值
                'charm'       => $check[ 'charm' ],        //  个人魅力值
                'interaction' => $check[ 'interaction' ],        //  客户互动值
                'product'     => $check[ 'product' ],        //  产品推广值
                'website'     => $check[ 'website' ],        //  官网推广度
                'active'      => $check[ 'active' ],        //  销售主动性值
            ];
        }
        $data = [ 'client'      => [ 'titlle' => '获客能力值', 'value' => $value[ 'client' ] ],
            'charm'       => [ 'titlle' => '个人魅力值', 'value' => $value[ 'charm' ] ],
            'interaction' => [ 'titlle' => '客户互动值', 'value' => $value[ 'interaction' ] ],
            'product'     => [ 'titlle' => '产品推广值', 'value' => $value[ 'product' ] ],
            'website'     => [ 'titlle' => '官网推广度', 'value' => $value[ 'website' ] ],
            'active'      => [ 'titlle' => '销售主动性值', 'value' => $value[ 'active' ] ], ];
        return $data;
    }

    /**
     * @Purpose: 工具中心
     *
     * @Method: GET
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function toolCenter ()
    {
        $toolList = array( 0 => array( 'sign'  => 'appoint', 'title' => '预约插件', 'desc' => '为客户提供上门服务预约、到店消费预约功能',
            'image' => 'https://retail.xiaochengxucms.com/%E9%A2%84%E7%BA%A6.png', ),
            1 => array( 'sign'  => 'payqr', 'title' => '线下付款',
                'desc'  => '让门店能精准抓取客户手机号，并反复的向客户发广告，最后达到二次、三次销售提升复购率的目地',
                'image' => 'https://retail.xiaochengxucms.com/%E6%94%AF%E4%BB%98.png', ),
            2 => array( 'sign'  => 'article', 'title' => '获客文章',
                'desc'  => '为员工提供高质量文章库，员工每天发送文章，吸引用户点击阅读。再通过寻客雷达精准提取意向客户，起到引流获客的作用',
                'image' => 'https://retail.xiaochengxucms.com/article.png', ),
            3 => array( 'sign'  => 'activity', 'title' => '活动报名', 'desc' => '为企业提供营销活动、团建活动等各类活动报名和签到功能',
                'image' => 'https://retail.xiaochengxucms.com/activity.png', ), );

        $payList    = array();
        $notPayList = array();


        $exist = Db::query( 'show tables like "%longbing_card_config%"' );

        $auth_info = false;

        if ( !empty( $exist ) )
        {
            $auth_info = Db::name( 'longbing_cardauth2_config' )
                ->where( [ [ 'modular_id', '=', $this->_uniacid ] ] )
                ->find();
        }

        foreach ( $toolList as $key => $value )
        {
            $payed = false;
            switch ( $value[ 'sign' ] )
            {
                case 'appoint':
                    $existTmp = Db::query( 'show tables like "%lb_appoint_record_check%"' );

                    if ( $existTmp )
                    {
                        $payed = true;
                        if ( $auth_info && isset( $auth_info[ 'appoint' ] ) && $auth_info[ 'appoint' ] == 0 )
                        {
                            $payed = false;
                        }
                    }
                    break;
                case 'payqr':
                    $existTmp = Db::query( 'show tables like "%lb_pay_qr_config%"' );

                    if ( $existTmp )
                    {
                        $payed = true;
                        if ( $auth_info && isset( $auth_info[ 'payqr' ] ) && $auth_info[ 'payqr' ] == 0 )
                        {
                            $payed = false;
                        }
                    }
                    break;
                case 'article':
                    $existTmp = Db::query( 'show tables like "%longbing_cardauth2_article%"' );

                    if ( $existTmp )
                    {
                        $articleAuthInfo = CardAuth2Article::where( [ [ 'modular_id', '=', $this->_uniacid ] ] )
                            ->find();
                        if ( $articleAuthInfo && isset( $articleAuthInfo[ 'number' ] ) && $articleAuthInfo[ 'number' ] > 0 )
                        {
                            $payed = true;
                        }
                    }

                    break;
                case 'activity':
                    $existTmp = Db::query( 'show tables like "%longbing_cardauth2_activity%"' );
                    if ( $existTmp )
                    {
                        $activityAuthInfo = CardAuth2Activity::where( [ [ 'modular_id', '=', $this->_uniacid ] ] )
                            ->find();
                        if ( $activityAuthInfo && isset( $activityAuthInfo[ 'sign' ] ) && $activityAuthInfo[ 'sign' ] > time() )
                        {
                            $payed = true;
                        }
                    }

                    break;
                default:
                    $payed = false;
            }
            if ( $payed )
            {
                array_push( $payList, $value );
            }
            else
            {
                array_push( $notPayList, $value );
            }

        }

        return $this->success( [ 'payList' => $payList, 'notPayList' => $notPayList ] );
    }

    /**
     * @Purpose: 公司列表
     *
     * @Method: GET
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function companyList ()
    {
        $topCompany = $this->modelCompany->where( [ [ 'uniacid', '=', $this->_uniacid ], [ 'status', '>', -1 ],
                [ 'pid', '=', 0 ] ]
        )
            ->field( [ 'id', 'pid', 'name', 'addr', 'status' ] )
            ->select()
            ->toArray();

        $sonCompany = $this->modelCompany->where( [ [ 'uniacid', '=', $this->_uniacid ], [ 'status', '>', -1 ],
                [ 'pid', '<>', 0 ] ]
        )
            ->field( [ 'id', 'pid', 'name', 'addr', 'status' ] )
            ->select()
            ->toArray();

        $companyList = $this->modelCompany->handleCompanyLevel( $topCompany, $sonCompany, 'children' );

        return $this->success( $companyList );
    }

    /**
     * @Purpose: 下架 / 上架 / 删除公司
     *
     * @Method: POST
     *
     * @Param：$company_id   number  公司id
     * @Param：$method   number  操作类型  0 = 下架 1 = 上架 2 = 删除
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function updateCompanyStatus ()
    {
        $verify = [ 'company_id' => 'required', 'method' => 0 ];

        $params = lbGetParamVerify( $this->_input, $verify );

        $result = $this->modelCompany->updateStatus( $params[ 'company_id' ], $params[ 'method' ] );

        if ( $result === false )
        {
            return $this->error( 'operation failed' );
        }

        return $this->success( [] );
    }

    /**
     * @Purpose: 新增 / 编辑公司
     *
     * @Method: POST
     *
     * @Param：$company_id   number  公司id 编辑公司信息是需要传入
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function editCompany ()
    {
        //  desc/shop_bg 商城背景图 culture/carousel 官网轮播图
        $verify = [ 'company_id' => 0, 'name' => 'required', 'addr' => '', 'logo' => '', 'shop_bg' => '', 'shop_name' => '',
            'carousel'   => '', 'longitude' => '', 'latitude' => '', 'auth_code' => '', 'short_name' => '', 'top' => 0 ];

        $params = lbGetParamVerify( $this->_input, $verify );

        $dataTmp = [ 'name'       => $params[ 'name' ], 'addr' => $params[ 'addr' ], 'logo' => $params[ 'logo' ],
            'desc'       => $params[ 'shop_bg' ], 'shop_name' => $params[ 'shop_name' ],
            'culture'    => trim( implode( ',', $params[ 'carousel' ] ), ',' ), 'longitude' => $params[ 'longitude' ],
            'latitude'   => $params[ 'latitude' ], 'auth_code' => $params[ 'auth_code' ],
            'short_name' => $params[ 'short_name' ], 'top' => $params[ 'top' ] ];

        //  编辑公司
        if ( $params[ 'company_id' ] )
        {
            $company_id = $params[ 'company_id' ];
            unset( $params[ 'company_id' ] );

            $result = $this->modelCompany->update( $dataTmp, [ 'id' => $company_id ]
            );

            unset( $dataTmp[ 'name' ] );
            $this->modelCompany->updateSonCompanyInfo( $company_id, $dataTmp );
        }
        //  新增公司
        else
        {
            $dataTmp[ 'uniacid' ] = $this->_uniacid;
            $result               = $this->modelCompany->create( $dataTmp );
        }

        if ( $result === false )
        {
            return $this->error( 'operation failed' );
        }

        return $this->success( [ 'operation success' ] );
    }

    /**
     * @Purpose: 公司信息
     *
     * @Method: GET
     *
     * @Param：$company_id   number  公司id 编辑公司信息是需要传入
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function companyInfo ()
    {
        //  desc/shop_bg 商城背景图 culture/carousel 官网轮播图
        $verify = [ 'company_id' => 'require' ];

        $params = lbGetParamVerify( $this->_param, $verify );

        $company = $this->modelCompany->where( [ [ 'uniacid', '=', $this->_uniacid ], [ 'status', '>', -1 ],
                [ 'id', '=', $params[ 'company_id' ] ] ]
        )
            ->find();


        if ( !$company )
        {
            return $this->error( 'company not fount' );
        }

        $company = $company->toArray();

        $company               = transImages( $company, [ 'logo', 'desc', 'culture' ] );
        $company[ 'shop_bg' ]  = $company[ 'desc' ];
        $company[ 'carousel' ] = $company[ 'culture' ];

        return $this->success( $company );
    }

    /**
     * @Purpose: 新增 / 编辑 部门信息
     *
     * @Method: POST
     *
     * @Param：$company_id   number  公司id 编辑公司信息是需要传入
     * @Param：$name   string  部门名
     * @Param：$method   method  操作类型 1 = 新增部门 2 = 修改部门
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function editDepartment ()
    {
        $verify = [ 'company_id' => 'require', 'name' => 'require', 'method' => 1 ];

        $params = lbGetParamVerify( $this->_input, $verify );

        $company_id = $params[ 'company_id' ];
        //  新增部门
        if ( $params[ 'method' ] == 1 )
        {
            $comapny = $this->modelCompany->where( [ [ 'id', '=', $company_id ] ] )
                ->find();

            if ( !$comapny )
            {
                return $this->error( 'company not found' );
            }

            $company = $comapny->toArray();
            unset( $company[ 'id' ] );
            unset( $company[ 'create_time' ] );
            unset( $company[ 'update_time' ] );
            $company[ 'name' ] = $params[ 'name' ];

            $company[ 'pid' ]    = $company_id;
            $company[ 'status' ] = 1;

            $key   = 'CARD_AUTH_COMPANY_';
            $value = getCache( $key, $this->_uniacid );

            if ( !$value && false )
            {
                $company[ 'pid' ] = 0;
            }
            $company[ 'uniacid' ] = $this->_uniacid;

            $result = $this->modelCompany->create( $company );
        }
        //  修改部门
        else if ( $params[ 'method' ] == 2 )
        {

            $result = $this->modelCompany->update( [ 'name' => $params[ 'name' ] ], [ 'id' => $company_id ] );
        }
        else
        {
            return $this->error( 'method failed' );
        }

        if ( $result === false )
        {
            return $this->error( 'operation failed' );
        }

        return $this->success( [ 'operation success' ] );
    }

    /**
     * @Purpose: 职位列表
     *
     * @Method: GET
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function position ()
    {
        $page    = isset( $this->_param[ 'page' ] ) ? $this->_param[ 'page' ] : 1;
        $limit   = isset( $this->_param[ 'limit' ] ) ? $this->_param[ 'limit' ] : 10;
        $keyword = isset( $this->_param[ 'keyword' ] ) ? $this->_param[ 'keyword' ] : '';

        $where = [ [ 'uniacid', '=', $this->_uniacid ], [ 'status', '>', -1 ] ];

        if ( $keyword )
        {
            $where[] = [ 'name', 'like', "%{$keyword}%" ];
        }

        $data = CardJob::where( $where )
            ->order( 'top', 'desc' )
            ->paginate( [ 'list_rows' => $limit, 'page' => $page ]
            )
            ->toArray();

        $data[ 'data' ] = handleTimes( $data[ 'data' ], 'create_time', 'Y-m-d H:i:s' );

        $data[ 'keyword' ] = $keyword;

        return $this->success( $data );
    }

    /**
     * @Purpose: 下架 / 上架 / 删除职位
     *
     * @Method: POST
     *
     * @Param：$job_id   number  职位id
     * @Param：$method   number  操作类型  0 = 下架 1 = 上架 2 = 删除
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function updatePositionStatus ()
    {
        $verify = [ 'id' => 'required', 'method' => 0 ];

        $params = lbGetParamVerify( $this->_input, $verify );

        switch ( $params[ 'method' ] )
        {
            case 0:
                $result = CardJob::update( [ 'status' => 0 ], [ 'id' => $params[ 'id' ] ] );
                break;
            case 1:
                $result = CardJob::update( [ 'status' => 1 ], [ 'id' => $params[ 'id' ] ] );
                break;
            case 2:
                $result = CardJob::update( [ 'status' => -1 ], [ 'id' => $params[ 'id' ] ] );
                break;
            default:
                return $this->error( 'method failed' );
        }

        if ( $result === false )
        {
            return $this->error( 'operation failed' );
        }

        return $this->success( [] );
    }

    /**
     * @Purpose: 编辑职位
     *
     * @Method: POST
     *
     * @Param：$job_id   number  职位id
     * @Param：$name   string  职位名
     * @Param：$top   number  排序值
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function editPosition ()
    {
        $verify = [ 'job_id' => 0, 'name' => 'required', 'top' => 0 ];

        $params = lbGetParamVerify( $this->_input, $verify );

        if ( $params[ 'job_id' ] )
        {
            $result = CardJob::update( [ 'name' => $params[ 'name' ], 'top' => $params[ 'top' ] ], [ 'id' => $params[ 'job_id' ] ]
            );
        }
        else
        {
            $result = CardJob::create( [ 'name' => $params[ 'name' ], 'top' => $params[ 'top' ], 'uniacid' => $this->_uniacid ] );
        }


        if ( $result === false )
        {
            return $this->error( 'operation failed' );
        }

        return $this->success( [] );
    }

    /**
     * @Purpose: 员工列表
     *
     * @Method: GET
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function staffs ()
    {
        $page    = isset( $this->_param[ 'page' ] ) ? $this->_param[ 'page' ] : 1;
        $limit   = isset( $this->_param[ 'limit' ] ) ? $this->_param[ 'limit' ] : 10;
        $keyword = isset( $this->_param[ 'keyword' ] ) ? $this->_param[ 'keyword' ] : '';

        $where[] =  [ 'a.uniacid', '=', $this->_uniacid ];
        $where[] =  [ 'a.is_staff', '=', 1 ];

        if ( $keyword )
        {
            $where2   = $where;
            $tmp      = '%' . $keyword . '%';
            $where[]  = [ 'b.name', 'like', $tmp ];
            $where2[] = [ 'a.nickName', 'like', $tmp ];

            $data = User::alias( 'a' )
                ->field( [ 'b.id as card_id', 'b.name', 'b.avatar', 'b.job_id', 'b.company_id', 'b.phone',
                        'b.create_time', 'a.nickName', 'a.avatarUrl', 'a.is_staff', 'a.is_boss',
                        'c.name as job_name', 'a.qr_path', 'b.is_default', 'a.id' ]
                )
                ->join( 'longbing_card_user_info b', 'b.fans_id = a.id' )
                ->join( 'longbing_card_job c', 'b.job_id = c.id', 'LEFT' )
                ->whereOr( [ $where, $where2 ] )
                ->order( [ 'a.is_boss' => 'desc', 'a.update_time' => 'desc', 'a.id' => 'desc' ] )
                ->paginate( [ 'list_rows' => $limit, 'page' => $page ] )
                ->toArray();

        }
        else
        {
            $data = User::alias( 'a' )
                ->field( [ 'b.id as card_id', 'b.name', 'b.avatar', 'b.job_id', 'b.company_id', 'b.phone',
                        'b.create_time', 'a.nickName', 'a.avatarUrl', 'a.is_staff', 'a.is_boss',
                        'c.name as job_name', 'a.qr_path', 'b.is_default', 'a.id' ]
                )
                ->join( 'longbing_card_user_info b', 'b.fans_id = a.id' )
                ->join( 'longbing_card_job c', 'b.job_id = c.id', 'LEFT' )
                ->where( $where )
                ->order( [ 'a.is_boss' => 'desc', 'a.is_staff' => 'desc', 'a.update_time' => 'desc', 'a.id' => 'desc' ] )
                ->paginate( [ 'list_rows' => $limit, 'page' => $page ] )
                ->toArray();
        }

        foreach ( $data[ 'data' ] as $index => $item )
        {
            if ( !$item[ 'name' ] && $item[ 'nickName' ] )
            {
                $data[ 'data' ][ $index ][ 'name' ] = $item[ 'nickName' ];
            }
            //  获取公司名，部门名
            list ( $data[ 'data' ][ $index ][ 'companyName' ], $data[ 'data' ][ $index ][ 'departmentName' ]
                ) = $this->modelCompany->getCompanyAndDepartmentName( $item[ 'company_id' ] );
        }


        $data[ 'data' ] = transImagesOne( $data[ 'data' ], [ 'avatar', 'qr_path' ] );
        $data[ 'data' ] = handleImagesWe7Local( $data[ 'data' ], [ 'avatar' ] );

        $data[ 'data' ] = handleTimes( $data[ 'data' ] );

        return $this->success( $data );
    }


    /**
     * @param $str
     * @return string|string[]|null
     * 过滤表情包
     */
    public function filterEmoji($str)
    {
        $str = preg_replace_callback(
            '/./u',
            function (array $match) {
                return strlen($match[0]) >= 4 ? '' : $match[0];
            },
            $str);

        return $str;
    }

    /**
     * @Purpose: 员工列表
     *
     * @Method: GET
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function users ()
    {



        $page    = isset( $this->_param[ 'page' ] ) ? $this->_param[ 'page' ] : 1;
        $limit   = isset( $this->_param[ 'limit' ] ) ? $this->_param[ 'limit' ] : 10;
        $keyword = isset( $this->_param[ 'keyword' ] ) ? $this->_param[ 'keyword' ] : '';
        $type    = isset( $this->_param[ 'type' ] ) ? $this->_param[ 'type' ] : '';

        $is_default    = isset( $this->_param[ 'is_default' ] ) ? $this->_param[ 'is_default' ] : '';

        $where = [ [ 'a.uniacid', '=', $this->_uniacid ] ];

        if(in_array($type, [1 ,'1'])) $where[] = ['a.is_staff' ,'=' ,1];

        if(in_array($type, [2 ,'2'])) $where[] = ['a.is_staff' ,'=' ,0];


        //是否推荐
        if($is_default != 2 ) {
            //兼容 有些是空
            if($is_default==0){

                $where[]  = ['b.is_default' , '<>' , 1];
            }else{
                $where[]  = ['b.is_default' , '=' , $is_default];
            }
        }

        if ( $keyword )
        {
            $keyword  = $this->filterEmoji($keyword);
            $where2   = $where;
            $tmp      = '%' . $keyword . '%';
            $where[]  = [ 'b.name', 'like', $tmp ];
            $where2[] = [ 'a.nickName', 'like', $tmp ];

            $data = User::alias( 'a' )
                ->field( [ 'b.id as card_id', 'b.name', 'b.avatar', 'b.job_id', 'b.company_id', 'b.phone',
                        'b.create_time', 'a.nickName', 'a.avatarUrl', 'a.is_staff', 'a.is_boss',
                        'c.name as job_name', 'a.qr_path', 'b.is_default', 'a.id','a.import','b.top' ]
                )
                ->join( 'longbing_card_user_info b', 'b.fans_id = a.id' ,'LEFT')
                ->join( 'longbing_card_job c', 'b.job_id = c.id', 'LEFT' )
                ->whereOr( [ $where, $where2 ] )
                ->order( [ 'a.is_boss' => 'desc','b.top'=>'desc', 'a.update_time' => 'desc', 'a.id' => 'desc' ] )
                ->paginate( [ 'list_rows' => $limit, 'page' => $page ] )
                ->toArray();

        }
        else
        {

//            dump($where);exit;

            $data = User::alias( 'a' )
                ->field( [ 'b.id as card_id', 'b.name', 'b.avatar', 'b.job_id', 'b.company_id', 'b.phone',
                        'b.create_time', 'a.nickName', 'a.avatarUrl', 'a.is_staff', 'a.is_boss',
                        'c.name as job_name', 'a.qr_path', 'b.is_default', 'a.id' ,'a.import','b.top']
                )
                ->join( 'longbing_card_user_info b', 'b.fans_id = a.id' ,'LEFT')
                ->join( 'longbing_card_job c', 'b.job_id = c.id', 'LEFT' )
                ->where( $where )
                ->order( [ 'a.is_boss' => 'desc', 'a.is_staff' => 'desc', 'b.top'=>'desc','a.update_time' => 'desc', 'a.id' => 'desc' ] )
                ->paginate( [ 'list_rows' => $limit, 'page' => $page ] )
                ->toArray();
        }

        foreach ( $data[ 'data' ] as $index => $item )
        {
            if ( !$item[ 'name' ] && $item[ 'nickName' ] )
            {
                $data[ 'data' ][ $index ][ 'name' ] = $item[ 'nickName' ];
            }
            //  获取公司名，部门名
            list ( $data[ 'data' ][ $index ][ 'companyName' ], $data[ 'data' ][ $index ][ 'departmentName' ]
                ) = $this->modelCompany->getCompanyAndDepartmentName( $item[ 'company_id' ] );
        }
        foreach($data[ 'data' ] as $key => $val)
        {

            if(!empty($val['avatar'])&&$val['avatar']) $data[ 'data' ][$key]['avatarUrl'] = $val['avatar'];

            if(empty($data[ 'data' ][$key]['avatarUrl'])) $data[ 'data' ][$key]['avatarUrl'] = "https://retail.xiaochengxucms.com/defaultAvatar.png";
        }

        $data[ 'data' ] = transImagesOne( $data[ 'data' ], [ 'avatar', 'avatarUrl', 'qr_path' ] );
        $data[ 'data' ] = handleImagesWe7Local( $data[ 'data' ], [ 'avatar' ] );

        $data[ 'data' ] = handleTimes( $data[ 'data' ] );

        return $this->success( $data );
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-04-14 14:23
     * @功能说明:
     */
    public function cardExcel(){

        $post = $this->_param;


        $keyword    = !empty($post[ 'keyword' ])?$post[ 'keyword' ]:'' ;

        $user_model = new User();

        $where[]    = ['a.uniacid','=',$this->_uniacid];

        $where[]    = ['b.is_staff','=',1];

        //搜索名字
        $where2 = [];

        if(!empty($keyword)){

            $keyword  = $this->filterEmoji($keyword);

            $tmp      = '%' . $keyword . '%';

            $where2[] = [ 'b.name', 'like', $tmp ];

            $where2[] = [ 'a.nickName', 'like', $tmp ];
        }

        if(!empty($post['company'])){

            $post['company'] = explode(',',$post['company']);

            $where[]  = ['b.company_id' , 'IN' , $post['company']];

        }
        if($post['date_type']==0){

            $start_time = 0;

            $end_time   = 0;

            if(!empty($post['range'])&&$post['range']!='null'){

                $post['range'] = explode(',',$post['range']);

                $start_time = strtotime($post['range'][0]);

                $end_time   = strtotime($post['range'][1])+86400-1;

            }

            if(!empty($end_time)){

                $where[] = ['b.create_time','<',$end_time];
            }

        }else{

            $start_time = strtotime(date('Y-m-d',time()));

            $end_time   = $start_time+86400-1;

            if(!empty($post['time'])&&$post['time']!='null'){

                $start_time = strtotime($post['time']);

                $end_time   = strtotime($post['time'])+86400-1;

            }
            $where[] = ['b.create_time','<',$end_time];
        }

        $data = $user_model->cardExcel($where,$where2,$post['date_type'],$start_time,$end_time);

        return $this->success( $data );

    }

    /**
     * @Purpose: 取消员工名片
     *
     * @Method: POST
     *
     * @Param: $method   number   操作类型  1 => 取消名片 2 = 取消boss 3 = 取消推荐
     * @Param: $staff_id   number   员工id
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function cancelStaff ()
    {


        //By.jingshuixian
        //校验必须要设置一张名片并推荐名片
//        if(UserService::getDefaultStraffNumber($this->_uniacid) == 1 ){
//            return $this->error( 'At least one staff is required' );
//        }

       //获取数据
        $verify = [ 'staff_id' => 'required', 'method' => 0 ];

        $params = lbGetParamVerify( $this->_input, $verify );


        //获取员工信息
        $info = User::where( [ [ 'uniacid', '=', $this->_uniacid ], [ 'id', '=', $params[ 'staff_id' ] ], [ 'is_staff', '=', 1 ] ]
        )
            ->find();
        //判断员工是否存在
        if ( !$info )
        {
            return $this->error( 'staff info not found' );
        }

        $is_d = Db::name('longbing_card_user_info')->where(['fans_id'=>$params[ 'staff_id' ]])->value('is_default');
       if(!empty($params[ 'method' ])&&$params[ 'method' ]!=2){
           if(UserService::getDefaultStraffNumber($this->_uniacid) == 1&&$is_d==1 ){
               return $this->error( 'At least one staff is required' );
           }
       }

        //操作
        switch ( $params[ 'method' ] )
        {
            //取消名片
            case 1:
                $data = User::update( [ 'is_staff' => 0, 'is_boss' => 0 ], [ 'id' => $params[ 'staff_id' ] ] );
                if(!empty($data)) {
                    $data = UserInfo::update( [ 'is_staff' => 0, 'is_default' => 0 ,'is_boss' => 0], [ 'fans_id' => $params[ 'staff_id' ] ] );

                    User::update( [ 'is_boss' => 0 ], [ 'id' => $params[ 'staff_id' ] ] );
                }
                if(!empty($data))
                {
                    CardBoss::where( [ [ 'user_id', '=', $params[ 'staff_id' ] ] ] )->delete();
                    longbingGetCardNum($this->_uniacid ,true);
                    longbingGetBossNum($this->_uniacid ,true);
                }

                break;
            //取消boss权限
            case 2:
                $data = User::update( [ 'is_boss' => 0 ], [ 'id' => $params[ 'staff_id' ] ] );
                if(!empty($data))
                {
//                  CardBoss::where( [ [ 'user_id', '=', $params[ 'staff_id' ] ] ] )->delete();
                    longbingGetBossNum($this->_uniacid ,true);
                }
                break;
            //取消推荐
            case 3:
                UserInfo::update( [ 'is_default' => 0 ], [ 'fans_id' => $params[ 'staff_id' ] ] );
                break;
            default:
                return $this->error( 'method error' );
        }
        longbingGetUser($params[ 'staff_id' ] ,$this->_uniacid ,true);
        longbingGetUserInfo($params[ 'staff_id' ] ,$this->_uniacid ,true);
        return $this->success( [] );
    }

    /**
     * @Purpose: 设置员工名片
     *
     * @Method: POST
     *
     * @Param: $method   number   操作类型  1 => 设置名片 2 = 设置boss 3 = 设置推荐
     * @Param: $staff_id   number   员工id
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function setStaff ()
    {
        $verify = [ 'staff_id' => 'required', 'method' => 0 ];

        $params = lbGetParamVerify( $this->_param, $verify );

        $info = User::where( [ [ 'uniacid', '=', $this->_uniacid ], [ 'id', '=', $params[ 'staff_id' ] ] ] )
            ->find();

        if ( !$info )
        {
            return $this->error( 'staff info not found' );
        }
        $user_info = longbingGetUserInfo($params['staff_id'] ,$this->_uniacid);
        $permissions = longbingGetPluginAuth($this->_uniacid);
        switch ( $params[ 'method' ] )
        {
            case 1:
                //检查名片数量是否超标
                if(empty($info['is_staff']) && !empty($permissions) && isset($permissions['card_number']) && !empty($permissions['card_number']))
                {
                    if(!(longbingGetCardNum($this->_uniacid) < $permissions['card_number']))
                    {
                        return $this->error(lang('not card num'));
                    }
                }
                //修改信息
                $data = User::update( [ 'is_staff' => 1 ], [ 'id' => $params[ 'staff_id' ] ] );
                if(!empty($data)) UserInfo::update( [ 'is_staff' => 1 ], [ 'fans_id' => $params[ 'staff_id' ] ] );
                longbingGetCardNum($this->_uniacid ,true);
                break;
            case 2:
                if ( empty($info[ 'is_staff' ]) )
                {
                    return $this->error( 'need set card first' );
                }
                //检查名片数量是否超标
                if(empty($info['is_boss']) && !empty($permissions) && isset($permissions['boss_num']) && !empty($permissions['boss_num']))
                {
                    if(!(longbingGetBossNum($this->_uniacid) < $permissions['boss_num']))
                    {
                        return $this->error(lang('not boss num'));
                    }
                }
                User::update( [ 'is_boss' => 1 ], [ 'id' => $params[ 'staff_id' ] ] );
                longbingGetBossNum($this->_uniacid ,true);
                break;
            case 3:
                if ( $info[ 'is_staff' ] != 1 )
                {
                    return $this->error( 'need set card first' );
                }
                //获取最小auto_count
                $count = longbingGetUserInfoMinAutoCount($this->_uniacid);
                UserInfo::update( [ 'is_default' => 1 ,'auto_count' => $count], [ 'fans_id' => $params[ 'staff_id']]);
                break;
            default:
                return $this->error( 'method error' );
        }
        longbingGetUser($params[ 'staff_id' ] ,$this->_uniacid ,true);
        longbingGetUserInfo($params[ 'staff_id' ] ,$this->_uniacid ,true);
        return $this->success( [] );
    }

    /**
     * @Purpose: 编辑员工名片回显数据
     *
     * @Method: GET
     *
     * @Param: $staff_id   number   员工id
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function staffInfo ()
    {
        //判断是否拥有分公司的boss权限
        $permissions = longbingGetPluginAuth($this->_uniacid);

        $verify = [ 'staff_id' => 'required' ];

        $params = lbGetParamVerify( $this->_param, $verify );

        $info = UserInfo::where( [ [ 'uniacid', '=', $this->_uniacid ], [ 'fans_id', '=', $params[ 'staff_id' ] ] ] )
            ->find();

        if ( !$info )
        {
            return $this->error( lang('staff info not found') );
        }

        $info = $info->toArray();
//      if(empty($info['avatar']))
//      {
//          $user = longbingGetUser($params['staff_id'] ,$this->_uniacid);
//          if(!empty($user)) $info['avatar'] = $user['avatarUrl'];
//          if(empty($info['avatar'])) $info['avatar'] = "https://retail.xiaochengxucms.com/defaultAvatar.png";
//      }
        // 处理图片
        $info = transImages( $info, [ 'images' ], ',' );
        $info = transImagesOne( $info, [ 'avatar', 'voice', 'my_url', 'my_video', 'my_video_cover', 'bg', 'vr_cover', 'vr_path' ] ,$this->_uniacid);

        $jobs = CardJob::where( [ [ 'uniacid', '=', $this->_uniacid ], [ 'status', '=', 1 ] ] )
            ->field( [ 'id', 'name' ] )
            ->order( [ 'top' => 'desc' ] )
            ->select()
            ->toArray();

        foreach ( $jobs as $index => $item )
        {
            $jobs[ $index ][ 'selected' ] = 0;
            if ( $item[ 'id' ] == $info[ 'job_id' ] )
            {
                $jobs[ $index ][ 'selected' ] = 1;
                $info[ 'job_name' ] = $jobs[ $index ]['name'];
            }
        }
        if(!isset($info[ 'job_name' ])) $info[ 'job_name' ] = lang('not set job');

        $companyCheck = CardBoss::where( [ [ 'user_id', '=', $params[ 'staff_id' ] ] ] )
            ->find();
        $info['company_ids'] = longbingGetUpCompanyIds($info['company_id']);
        $boss_permission = true;
        if(empty($permissions) || !isset($permissions['plugin']) || !isset($permissions['plugin']['boss']) || empty($permissions['plugin']['boss']))
        {
            $boss_permission = false;
        }
        //顶级公司列表
        $topCompany = $this->modelCompany->where( [ [ 'uniacid', '=', $this->_uniacid ],
            [ 'status', '>', -1 ],
            [ 'pid', '=', 0 ]
        ])
            ->field( [ 'id', 'pid', 'name', 'addr', 'status' ,'top_id'] )
            ->select()
            ->toArray();

        $sonCompany = $this->modelCompany->where( [ [ 'uniacid', '=', $this->_uniacid ],
            [ 'status', '>', -1 ],
            [ 'pid', '<>', 0 ]
        ])
            ->field( [ 'id', 'pid', 'name', 'addr', 'status' ,'top_id'] )
            ->select()
            ->toArray();

        if ( !empty($companyCheck) && $boss_permission)
        {
            $companyCheck = explode( ',', $companyCheck['boss'] );
            foreach($topCompany as $key => $val)
            {
                $topCompany[$key]['selected'] = 0;
                if(in_array($val['id'], $companyCheck)) $topCompany[$key]['selected'] = 1;
            }
            foreach($sonCompany as $key => $val)
            {
                $sonCompany[$key]['selected'] = 0;
                if(in_array($val['id'], $companyCheck)) $sonCompany[$key]['selected'] = 1;
            }
        }
        $company = $this->modelCompany->handleCompanyLevel( $topCompany, $sonCompany, 'children' );
//      if ( !empty($companyCheck) && $boss_permission)
//      {
//          $companyCheck = explode( ',', $companyCheck['boss'] );
//
////          $company = $this->modelCompany->where( [ [ 'id', 'in', $companyCheck ], [ 'status', '=', 1 ] ,['pid' , '=', 0]] )
//          $company = $this->modelCompany->where( [ [ 'id', 'in', $companyCheck ], [ 'status', '=', 1 ]] )
//                                        ->field( [ 'id', 'name' ] )
//                                        ->select()
//                                        ->toArray();
//      }

//      else
//      {
//          $topCompany = $this->modelCompany->where( [ [ 'uniacid', '=', $this->_uniacid ], [ 'status', '>', -1 ],
//                                                        [ 'pid', '=', 0 ] ]
//          )
//                                           ->field( [ 'id', 'pid', 'name', 'addr', 'status' ] )
//                                           ->select()
//                                           ->toArray();
//
//          $sonCompany = $this->modelCompany->where( [ [ 'uniacid', '=', $this->_uniacid ], [ 'status', '>', -1 ],
//                                                        [ 'pid', '<>', 0 ] ]
//          )
//                                           ->field( [ 'id', 'pid', 'name', 'addr', 'status' ] )
//                                           ->select()
//                                           ->toArray();
//
//          $company = $this->modelCompany->handleCompanyLevel( $topCompany, $sonCompany, 'children' );
////          $company = [];
//      }


//      foreach ( $company as $index => $item )
//      {
//          $company[ $index ][ 'selected' ] = 0;
//          if ( $item[ 'id' ] == $info[ 'company_id' ] )
//          {
//              $company[ $index ][ 'selected' ] = 1;
//              $info[ 'company_name' ] =  $company[ $index ]['name'];
//          }
//      }
//      if(!isset($info[ 'company_name' ])) $info[ 'company_name' ] = lang('not set company');
        $info[ 'job_list' ]     = $jobs;
        $info[ 'company_list' ] = $company;
        //字符替换
        $info['desc'] = str_replace("&nbsp;"," ",$info['desc']);
        $info['boss_permission'] = $boss_permission;
            return $this->success( $info );
    }

    /**
     * @Purpose: 编辑员工名片
     *
     * @Method: POST
     *
     * @Param: $staff_id   number   员工id
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function editStaff ()
    {
        //获取数据
        $verify = [ 'staff_id'   => 'required', 'name' => 'required', 'avatar' => '', 'job_id' => 'required',
            'company_id' => 'required', 'phone' => '', 'wechat' => '', 'telephone' => '', 'phone400' => '', 'email' => '',
            'share_text' => '', 'voice' => '', 'voice_time' => 0, 'desc' => '', 'my_video' => '', 'my_video_cover' => '',
            'images'     => '', 'my_url' => '', 'view_number' => '', 't_number' => '', 'ww_account' => '', 'bg' => '',
            'bg_switch'  => 0, 'vr_tittle' => '', 'vr_cover' => '', 'vr_path' => '', 'vr_switch' => '' ,'from_company_ids' => '','top'=>''];

        $params = lbGetParamVerify( $this->_input, $verify );

//        dump($params);exit;
        //获取所属公司列表
        $company_list = $params['from_company_ids'];
        unset($params['company_list']);
        //获取权限信息
        $permissions = longbingGetPluginAuth($this->_uniacid);
        //检查权限
        if(empty($permissions) || !isset($permissions['plugin']) || !isset($permissions['plugin']['boss']) || empty($permissions['plugin']['boss']))
        {
            CardBoss::where( [ [ 'user_id', '=', $params[ 'staff_id' ] ] ] )
                ->delete();
        }else{
            //检查所属公司是否存在
            if(empty($company_list)) return $this->error(lang('not from company'));
            //检查公司是否在所属公司中
            if(!in_array($params['company_id'], $company_list)) return $this->error('not set company');
            CardBoss::where( [ [ 'user_id', '=', $params[ 'staff_id' ] ] ] )
                ->delete();
            $boss = implode( ',', $company_list );
            $result = CardBoss::create( [ 'user_id' => $params[ 'staff_id' ], 'boss' => $boss, 'uniacid' => $this->_uniacid ] );
            if(empty($result)) return $this->error(lang('chage card error'));
        }
        if ( is_array( $params[ 'images' ] ) && !empty( $params[ 'images' ] ) )
        {
            $params[ 'images' ] = implode( ',', $params[ 'images' ] );
        }else{
            $params[ 'images' ] = '';
        }
        if ( is_array( $params[ 'avatar' ] ) && !empty( $params[ 'avatar' ] ) )
        {
            $params[ 'avatar' ] = implode( ',', $params[ 'avatar' ] );
        }

        if ( is_array( $params[ 'my_video_cover' ] ) && !empty( $params[ 'my_video_cover' ] ) )
        {
            $params[ 'my_video_cover' ] = implode( ',', $params[ 'my_video_cover' ] );
        }else{
            $params[ 'my_video_cover' ]  = '';
        }

        if ( is_array( $params[ 'vr_cover' ] ) && !empty( $params[ 'vr_cover' ] ) )
        {
            $params[ 'vr_cover' ] = implode( ',', $params[ 'vr_cover' ] );
        }else{
            $params[ 'vr_cover' ] = '';
        }

        $staff_id = $params[ 'staff_id' ];
        unset( $params[ 'staff_id' ] );

        $result = UserInfo::update( $params, [ 'fans_id' => $staff_id ] );

        if ( $result === false )
        {
            return $this->error( 'edit failed' );
        }

        UserService::createHeaderQr( $this->_uniacid ,['staff_id'=> $staff_id ]);

        longbingGetUserInfo($staff_id ,$this->_uniacid ,true);
        //清除名片缓存
        $key = 'longbing_card_card_info_' . $staff_id;
        delCache($key ,$this->_uniacid);

        return $this->success([]);
    }

    /**
     * @Purpose: 分配BOSS权限回显数据
     *
     * @Method: GET
     *
     * @Param: $staff_id   number   员工id
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function authBossInfo ()
    {
        //判断是否拥有分公司的boss权限
        $permissions = longbingGetPluginAuth($this->_uniacid);

        if(empty($permissions) || !isset($permissions['plugin']) || !isset($permissions['plugin']['boss']) || empty($permissions['plugin']['boss']))
        {
            return $this->success([]);
        }
        $verify = [ 'staff_id' => 'required' ];

        $params = lbGetParamVerify( $this->_param, $verify );

        $authInfo = CardBoss::where( [ [ 'user_id', '=', $params[ 'staff_id' ] ] ] )
            ->find();

        $authArr = [];
        if ( $authInfo )
        {
            $authArr = explode( ',', $authInfo[ 'boss' ] );
        }

        $topCompany = $this->modelCompany->where( [ [ 'uniacid', '=', $this->_uniacid ], [ 'status', '>', -1 ],
                [ 'pid', '=', 0 ] ]
        )
            ->field( [ 'id', 'pid', 'name', 'addr', 'status' ] )
            ->select()
            ->toArray();

        $sonCompany = $this->modelCompany->where( [ [ 'uniacid', '=', $this->_uniacid ], [ 'status', '>', -1 ],
                [ 'pid', '<>', 0 ] ]
        )
            ->field( [ 'id', 'pid', 'name', 'addr', 'status' ] )
            ->select()
            ->toArray();

        foreach ( $topCompany as $index => $item )
        {
            $topCompany[ $index ][ 'selected' ] = 0;
            if ( in_array( $item[ 'id' ], $authArr ) )
            {
                $topCompany[ $index ][ 'selected' ] = 1;
            }
        }

        foreach ( $sonCompany as $index => $item )
        {
            $sonCompany[ $index ][ 'selected' ] = 0;
            if ( in_array( $item[ 'id' ], $authArr ) )
            {
                $sonCompany[ $index ][ 'selected' ] = 1;
            }
        }

        $company = $this->modelCompany->handleCompanyLevel( $topCompany, $sonCompany, 'children' );

        return $this->success( $company );

    }

    /**
     * @Purpose: 分配BOSS权限
     *
     * @Method: POST
     *
     * @Param: $staff_id   number   员工id
     * @Param: $company_ids   array   公司ID组成的数组
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function editAuthBoss ()
    {
        $verify = [ 'staff_id' => 'required', 'company_ids' => '' ];
        //获取数据
        $params = lbGetParamVerify( $this->_input, $verify );
        //检查数据是否存在

        $userService = new  UserService() ;

        $result = $userService->addUserToStaff($params['staff_id'] , $params['company_ids'] , $this->_uniacid) ;

        if(is_string($result)){
            return $this->error( $result );
        }

        return $this->success( $result );

    }

    /**
     * @Purpose: 生成名片码
     *
     * @Method: POST
     *
     * @Param: $staff_id   number   员工id
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function createStaffQr ()
    {

        $verify = [ 'staff_id' => 'required' ];

        $params = lbGetParamVerify( $this->_input, $verify );

        $result = UserService::createHeaderQr($this->_uniacid ,$params );

        if(is_string($result)){

            return $this->error($result);
        }
        //返回数据
        return $this->success( ['src' => $result['qr_path']]);
    }

    /**
     * @Purpose: 印象标签列表
     *
     * @Method: GET
     *
     * @Param: $staff_id   number   员工id
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function tags ()
    {
        $page  = isset( $this->_param[ 'page' ] ) ? $this->_param[ 'page' ] : 1;
        $limit = isset( $this->_param[ 'limit' ] ) ? $this->_param[ 'limit' ] : 10;

        $data = CardTags::where( [ [ 'uniacid', '=', $this->_uniacid ], [ 'user_id', '=', 0 ], [ 'status', '>', -1 ] ] )
            ->order( [ 'top' => 'desc', 'id' => 'desc' ] )
            ->paginate( [ 'list_rows' => $limit, 'page' => $page ] )
            ->toArray();

        $data[ 'data' ] = handleTimes( $data[ 'data' ] );

        return $this->success( $data );
    }

    /**
     * @Purpose: 下架 / 上架 / 删除印象标签
     *
     * @Method: POST
     *
     * @Param：$id   number  标签id
     * @Param：$method   number  操作类型  0 = 下架 1 = 上架 2 = 删除
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function updateTagStatus ()
    {
        $verify = [ 'id' => 'required', 'method' => 0 ];

        $params = lbGetParamVerify( $this->_input, $verify );

        switch ( $params[ 'method' ] )
        {
            case 0:
                $result = CardTags::update( [ 'status' => 0 ], [ 'id' => $params[ 'id' ] ] );
                break;
            case 1:
                $result = CardTags::update( [ 'status' => 1 ], [ 'id' => $params[ 'id' ] ] );
                break;
            case 2:
                $result = CardTags::update( [ 'status' => -1 ], [ 'id' => $params[ 'id' ] ] );
                break;
            default:
                return $this->error( 'method failed' );
        }

        if ( $result === false )
        {
            return $this->error( 'operation failed' );
        }

        return $this->success( [] );
    }

    /**
     * @Purpose: 新增 / 编辑标签
     *
     * @Method: POST
     *
     * @Param：$id   number  标签id
     * @Param：$name   string  标签名
     * @Param：$top   number  排序值
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function editTag ()
    {
        $this->_input['name'] = trim( $this->_input['name'] );

        $verify = [ 'id' => 0, 'name' => 'required', 'top' => 0 ];

        if(!$this->_input['name']){
            return $this->error('请填写标签名');
        }
        $params = lbGetParamVerify( $this->_input, $verify );

        if ( $params[ 'id' ] )
        {
            $tag_model = new CardTags();
            $count = $tag_model->getTagCount([  ['id' , '<>', $params[ 'id' ] ] , [ 'tag' , '=' , $params['name']  ], [ 'uniacid' , '=',$this->_uniacid ], ['status' , '=',1], ['user_id' ,'=', 0]]);
            if (!empty($count)) return $this->error(lang('tag is exist'));

            $result = CardTags::update( [ 'tag' => $params[ 'name' ], 'top' => $params[ 'top' ] ], [ 'id' => $params[ 'id' ] ]);
        }
        else {
            $tag_model = new CardTags();
            $count = $tag_model->getTagCount(['tag' => $params['name'], 'uniacid' => $this->_uniacid, 'status' => 1, 'user_id' => 0]);
            if (!empty($count)) return $this->error(lang('tag is exist'));
            $result = CardTags::create(['tag' => $params['name'], 'top' => $params['top'], 'uniacid' => $this->_uniacid, 'user_id' => 0]);
        }


        if ( $result === false )
        {
            return $this->error( 'operation failed' );
        }

        return $this->success( [] );
    }

    /**
     * @Purpose: 免审口令
     *
     * @Method: POST
     *
     * @Param：$action   string  操作类型 edit = 修改 其他 = 回显数据
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function authCode ()
    {

        $config = $this->modelConfig->getConfig( $this->_uniacid );

        if ( $this->_input[ 'action' ] !== 'edit' )
        {
            return $this->success( [ 'code'          => $config[ 'code' ], 'btn_code_err' => $config[ 'btn_code_err' ],
                    'btn_code_miss' => $config[ 'btn_code_miss' ] ]
            );
        }
        else
        {

            $verify = [ 'code' => '', 'btn_code_err' => 'required', 'btn_code_miss' => 'required', 'action' => '' ];
            $params = lbGetParamVerify( $this->_input, $verify );

            $result = $this->modelConfig->update( [ 'code'          => $params[ 'code' ],
                'btn_code_err'  => $params[ 'btn_code_err' ],
                'btn_code_miss' => $params[ 'btn_code_miss' ] ], [ 'id' => $config[ 'id' ] ]
            );

            if ( $result === false )
            {
                return $this->error( 'operation failed' );
            }

            $key  = 'longbing_card_config_';

            delCache($key, $this->_uniacid);
            longbingGetAppConfig($this->_uniacid ,true);
            return $this->success( [] );
        }
    }

    /**
     * @Purpose: 手机创建设置
     *
     * @Method: POST
     *
     * @Param：$action   string  操作类型 edit = 修改 其他 = 回显数据
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function phoneCreate ()
    {

        $config = $this->modelConfig->getConfig( $this->_uniacid );

        if ( $this->_input[ 'action' ] !== 'edit' )
        {
            return $this->success( [ 'allow_create' => $config[ 'allow_create' ], 'create_text' => $config[ 'create_text' ],'agreement_status'=>$config['agreement_status'],'agreement'=>$config['agreement'] ]
            );
        }
        else
        {

            $verify = [ 'allow_create' => 0, 'create_text' => 'required', 'action' => '','agreement'=>'','agreement_status'=>0 ];

            $params = lbGetParamVerify( $this->_input, $verify );

//            dump($params['agreement']);exit;
            $result = $this->modelConfig->update( [ 'allow_create' => $params[ 'allow_create' ],
                'create_text'  => $params[ 'create_text' ],'agreement'=>$params['agreement'],'agreement_status'=>$params['agreement_status'] ], [ 'id' => $config[ 'id' ] ]
            );

            if ( $result === false )
            {
                return $this->error( 'operation failed' );
            }

            longbingGetAppConfig($this->_uniacid ,true);
            return $this->success( [] );
        }
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-03-18 12:26
     * @功能说明:默认配置
     */
    public function defaultSetting(){

        $input   = $this->_input;

        $default = new DefaultSetting();

        if(!empty($input)&&count($input)>1){

            $input['new_setting']['my_photo_cover'] = !empty($input['new_setting']['my_photo_cover'])?implode(',',$input['new_setting']['my_photo_cover'] ):'';
            //新的配置
            $default->settingUpdate(['id' => $input['new_setting']['id']],$input['new_setting']);
            //新的配置
            $data = $this->modelConfig->where(['id' => $input['old_setting']['id']])->update($input['old_setting']);

        }else{
            //新的配置
            $data['new_setting'] = $default->settingInfo(['uniacid' => $this->_uniacid]);

            $data['new_setting'] = transImages($data['new_setting'],['my_photo_cover']);
            //新的配置
            $data['old_setting'] = $this->modelConfig->getConfig( $this->_uniacid );
        }

        longbingGetAppConfig($this->_uniacid ,true);

        return $this->success($data);
    }



    /**
     * @Purpose: 音频视频设置
     *
     * @Method: POST
     *
     * @Param：$action   string  操作类型 edit = 修改 其他 = 回显数据
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function defaultMedia ()
    {
        $verify = [ 'default_video' => '', 'default_voice' => '', 'default_video_cover' => '', 'default_voice_switch' => 0,
            'action'        => '' ];

        $params = lbGetParamVerify( $this->_input, $verify );
        $config = $this->modelConfig->getConfig( $this->_uniacid );

//        zDumpAndDie($config);

        if ( $params[ 'action' ] !== 'edit' )
        {
            return $this->success(
                [

                    'default_video'        => $config[ 'default_video' ],
                    'default_voice'        => $config[ 'default_voice' ],
                    'default_video_cover'  => $config[ 'default_video_cover' ],
                    'default_voice_switch' => $config[ 'default_voice_switch' ],
                    'vr_tittle'            => $config[ 'vr_tittle' ],
                    'vr_cover'             => $config[ 'vr_cover' ],
                    'vr_path'              => $config[ 'vr_path' ],
                    'vr_switch'            => $config[ 'vr_switch' ],

                ]
            );
        }
        else
        {
            if(isset($params[ 'default_video_cover' ][0])){
                $params[ 'default_video_cover' ] = $params[ 'default_video_cover' ][0];
            } else{
                $params[ 'default_video_cover' ] = '';
            }
            $result = $this->modelConfig->update(
                [
                'default_video'        => $params[ 'default_video' ],
                'default_voice'        => $params[ 'default_voice' ],
                'default_video_cover'  => $params[ 'default_video_cover' ],
                'default_voice_switch' => $params[ 'default_voice_switch' ],

                'vr_tittle'            => $params[ 'vr_tittle' ],

                'vr_cover'             => $params[ 'vr_cover' ],

                'vr_path'              => $params[ 'vr_path' ],

                'vr_switch'            => $params[ 'vr_switch' ]

            ]
                , [ 'id' => $config[ 'id' ] ]
            );

            if ( $result === false )
            {
                return $this->error( 'operation failed' );
            }

            longbingGetAppConfig($this->_uniacid ,true);
            return $this->success( [] );
        }
    }

    /**
     * @Purpose: 名片设置
     *
     * @Method: POST
     *
     * @Param：$action   string  操作类型 edit = 修改 其他 = 回显数据
     * @Param：$force_phone   number  强制授权手机号开关
     * @Param：$preview_switch   number  大图模式开关
     * @Param：$card_type   number  名片板式 1=点击向下展开 2=左右滑动模式
     * @Param：$exchange_switch   number 交换名片开关
     * @Param：$motto_switch   number    员工名片是否显示个性签名开关
     * @Param：$btn_consult   number 咨询按钮文字
     * @Param：$exchange_btn   number    名片详情也交换手机号自定义文字
     * @Param：$vr_tittle   number   默认vr展示标题
     * @Param：$vr_cover   number    默认vr封面图
     * @Param：$vr_path   number 默认vr连接地址
     * @Param：$vr_switch   number   默认VR地址类型 1=网页链接 0=小程序APPID
     * @Param：$qr_avatar_switch   number    替换名片码中间头像开关
     * @Param：$auto_switch   number 自动分配开关
     * @Param：$auto_switch_way   number 分配方式 1=随机分配 2=顺序分配
     * @Param：$job_switch   number 是否允许员工在手机端切换职位开关
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function cardSetting ()
    {
        $verify = [ 'force_phone' => 0, 'preview_switch' => 0, 'card_type' => 1, 'exchange_switch' => 0, 'motto_switch' => 0,
            'btn_consult' => '', 'exchange_btn' => '', 'vr_tittle' => '', 'vr_cover' => 0, 'vr_path' => '',
            'vr_switch'   => 0, 'qr_avatar_switch' => 0, 'auto_switch' => 1, 'auto_switch_way' => 0, 'job_switch' => 0,
            'action'      => '' ,'auth_switch' => 1 ,'chat_switch' => 1 ,'dynamic_switch' => 0 ];

        $params = lbGetParamVerify( $this->_input, $verify );

        if ( is_array( $params[ 'vr_cover' ] ) && !empty( $params[ 'vr_cover' ] ) )
        {
            $params[ 'vr_cover' ] = implode( ',', $params[ 'vr_cover' ] );
        }else{
            $params[ 'vr_cover' ] = '';
        }


        $config = $this->modelConfig->getConfig( $this->_uniacid );

        if ( $params[ 'action' ] !== 'edit' )
        {
            return $this->success( [ 'force_phone'      => $config[ 'force_phone' ],
                    'preview_switch'   => $config[ 'preview_switch' ], 'card_type' => $config[ 'card_type' ],
                    'exchange_switch'  => $config[ 'exchange_switch' ],
                    'motto_switch'     => $config[ 'motto_switch' ], 'btn_consult' => $config[ 'btn_consult' ],
                    'exchange_btn'     => $config[ 'exchange_btn' ], 'vr_tittle' => $config[ 'vr_tittle' ],
                    'vr_cover'         => $config[ 'vr_cover' ], 'vr_path' => $config[ 'vr_path' ],
                    'vr_switch'        => $config[ 'vr_switch' ],
                    'qr_avatar_switch' => $config[ 'qr_avatar_switch' ],
                    //'auto_switch'      => $config[ 'auto_switch' ],
                    'auto_switch'      => 1,
                    'auto_switch_way'  => $config[ 'auto_switch_way' ],
                    'job_switch'       => $config[ 'job_switch' ],
                    'auth_switch'      => $config[ 'auth_switch' ],
                    'chat_switch'      => $config[ 'chat_switch' ],
                    'dynamic_switch'   => $config[ 'dynamic_switch'] ,
                ]
            );
        }
        else
        {
            $result = $this->modelConfig->update( [ 'force_phone'      => $params[ 'force_phone' ],
                'preview_switch'   => $params[ 'preview_switch' ],
                'card_type'        => $params[ 'card_type' ],
                'exchange_switch'  => $params[ 'exchange_switch' ],
                'motto_switch'     => $params[ 'motto_switch' ],
                'btn_consult'      => $params[ 'btn_consult' ],
                'exchange_btn'     => $params[ 'exchange_btn' ],
                'vr_tittle'        => $params[ 'vr_tittle' ],
                'vr_cover'         => $params[ 'vr_cover' ],
                'vr_path'          => $params[ 'vr_path' ],
                'vr_switch'        => $params[ 'vr_switch' ],
                'qr_avatar_switch' => $params[ 'qr_avatar_switch' ],
                //'auto_switch'      => $params[ 'auto_switch' ],
                'auto_switch'      => 1,
                'auto_switch_way'  => $params[ 'auto_switch_way' ],
                'job_switch'       => $params[ 'job_switch' ] ,
                'auth_switch'      => $params[ 'auth_switch' ],
                'chat_switch'      => $params[ 'chat_switch' ] ,
                'dynamic_switch'   => $params[ 'dynamic_switch'] ,
            ],
                [ 'id' => $config[ 'id' ] ]
            );


            if ( $result === false )
            {
                return $this->error( 'operation failed' );
            }

            longbingGetAppConfig($this->_uniacid ,true);

            return $this->success( [] );
        }
    }


    /**
     * User: chenniang(龙兵科技)
     * Date: 2019-09-24 17:40
     * @return void
     * descption:获取微擎版权接口
     */
    public function getW7Tmp(){
        global $_W;
        if(defined('IS_WEIQIN')){
            $w['footerleft'] = !empty($_W['setting']['copyright']['footerleft'])?$_W['setting']['copyright']['footerleft']:'';
            $w['version']    = !empty($_W['setting']['site']['version'])?$_W['setting']['site']['version']:'';
            $w['icp']        = !empty($_W['setting']['copyright']['icp'])?$_W['setting']['copyright']['icp']:'';
        }else{
            $w = 1;
        }
        return $this->success($w);
    }

    //插件权限列表
    public function getPluginAuth()
    {
        $pluginAuth = longbingGetPluginAuth($this->_uniacid);
        return $this->success($pluginAuth);
    }

    
    public function getPermissionV2()
    {

        $adminMenes = AdminMenu::all($this->_uniacid);

        $data       =  $adminMenes;

        if(longbingIsZhihuituike()){

            $level = Db::name('longbing_admin')->where(['admin_id'=>$this->_user['admin_id']])->value('level');

            $this->changeUserCache($level);

            if($level==1){

                $data = [];

                foreach ($adminMenes as &$v){

                    if($v['path']=='/customer'){

                        $v['redirect'] = '/customer/talkingSkill';
                        $v['meta']['subNavName'] = array_splice($v['meta']['subNavName'],1);
                    }

                    if($v['path']!='/sys'){

                        $data[] = $v;
                    }
                }
            }

        }
        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-07-17 18:04
     * @功能说明:
     */
    public function changeUserCache($level){

        if($level!=$this->_user['level']){

            setCache("Token_" . $this->_token ,'' ,72000);

        }

        return true;
    }



    public function websitebind()
    {
        $type = $this->_input['type'] ?? null;
        if (!in_array($type, ['get', 'post'])) {
            return $this->error('参数错误');
        }

        $version_id = '64c7ad0322f14b9c894e95220c9d00d5';
        $branch_id = '64c7ad0322f14b9c894e95220c9d00d6';
        $version_name = '1.0.1';

        $domain_name = $_SERVER['HTTP_HOST'];
        $server_url = 'http://api.longbing.org';//接口地址

        $bindInfo = Db::name('lb_pluge_key')->where('domain_name', '=', $domain_name)->find();


        if ($type == 'get') {
            if ( !$bindInfo ) {
                $data = array(
                    'version_id'   => $version_id,
                    'branch_id'    => $branch_id,
                    'version_name' => $version_name,
                    'domain_name'  => $domain_name,
                );
                Db::name('lb_pluge_key')->insert($data);
                $bindInfo = Db::name('lb_pluge_key')->where('domain_name', '=', $domain_name)->find();
            }

            return $this->success($bindInfo);

        } else {
            $website_key = $this->_input['website_key'] ?? null;
            if ($type == 'post' && $website_key == null) {
                return $this->error('请输入密钥');
            }

            if (isset($bindInfo['website_keys']) && $bindInfo['website_keys']) {
                return $this->error('已经绑定过了, 无需重复绑定');
            }

            $res = json_decode(($this->lb_api_notice_increment_we7($server_url . '/website/check?keys=' . $website_key, [], ['Accept-Charset:utf-8', 'Origin:' . $domain_name], 'GET')), true);

            if (isset($res['error'])) {
                return $this->error($res['error']['message']);
            }
            $data = $res['result']['data'];


            $save_data = [
                'domain_name' => $domain_name,
                'domain_keys' => json_encode($data['domain_keys'], true),
                'domain_id' => $data['domain_id'],
                'website_id' => $data['website_id'],
                'website_keys' => $website_key,
            ];

            $result =   Db::name('lb_pluge_key')->where('domain_name', '=', $domain_name)->save($save_data);

            if ($result === false) {
                return $this->error('fail');
            }
        }

        return $this->success(true);
    }



    private function lb_api_notice_increment_we7 ( $url, $data, $headers = [ 'Accept-Charset:utf-8' ], $request_type = 'POST' )
    {
        $ch = curl_init();
        //    $header = "Accept-Charset: utf-8";
        curl_setopt( $ch, CURLOPT_URL, $url );
        //设置头文件的信息作为数据流输出
        curl_setopt( $ch, CURLOPT_HEADER, 0 );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
        curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, $request_type );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );
        curl_setopt( $ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)' );
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
        curl_setopt( $ch, CURLOPT_AUTOREFERER, 1 );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        $tmpInfo = curl_exec( $ch );
        //     var_dump($tmpInfo);
        //    exit;
        if ( curl_errno( $ch ) ) {
            return false;
        } else {
            // var_dump($tmpInfo);
            return $tmpInfo;
        }
    } 

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-03-18 14:38
     * @功能说明:返回json
     */
    public function returnJson(){

        $input = $this->_input;

        $m_diy = new DiyModel();

        $data = $m_diy->where(['uniacid'=>$this->_uniacid,'status'=>1])->find();

        $tabbar = [];
        if(!empty($data)){

            $data = $data->toArray();

            $tabbar = json_decode($data['tabbar'],true);

        }

        return $this->success($tabbar);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-12-17 15:58
     * @功能说明:
     */
    public function admin(){

        $input = $this->_input;

        $key = 'return_admin'.$this->_uniacid;

        $arr = getCache($key,$this->_uniacid);

        if(!empty($arr)){

            return $this->success($arr);

        }

        $this->update();

        $arr['checkAuth'] = $this->checkAuth();

        $arr['permission'] = $this->getPermissionV3();

        $arr['w7tmp'] = $this->getW7TmpV2();

        $arr['app_model_name'] = config('app.AdminModelList')['app_model_name'];

        $arr['copyright'] = Db::name('longbing_card_config')->where(['uniacid'=>$this->_uniacid])->value('copyright');

        $arr = transImagesOne($arr,['copyright'],$this->_uniacid);

        setCache($key,$arr,3600,$this->_uniacid);

        return $this->success($arr);
    }


    /**
     * By.jingshuixian
     * 2019年11月23日21:43:47
     * 升级脚本导入执行
     */
    public function update(){

//        return $this->success([]);
        $key  = 'init_all_data';

        $data = getCache($key,$this->_uniacid);

        if(!empty($data)){

            return [];

        }

        setCache($key,1,7200,$this->_uniacid);

        UpdateService::installSql($this->_uniacid);

        UpdateService::initWeiqinConfigData();

        DiyService::addDefaultDiyData($this->_uniacid);
        //各个模块初始化数据事件
        event('InitModelData');
        //处理雷达
        lbInitRadarMsg($this->_uniacid);

        return [];

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-06-06 15:24
     * @功能说明:检查短视频的权限
     */
    public function checkAuth(){


        if(longbingIsWeiqin()){
            //是否授权
            $saasKey  = longbing_get_auth_prefix('AUTH_CARD') ;
            //是否给过验证码
            $pass     = getCache('AUTH_CARD','99999');
            //如果授权过或者给过验证码
            if(defined($saasKey)||(!empty($pass)&&$pass==1)){

                return 1;

            }else{

                return 0;

            }
        }else{

            return 1;
        }

    }


    public function getPermissionV3()
    {

        $adminMenes = AdminMenu::all($this->_uniacid);

        $data       =  $adminMenes;

        if(longbingIsZhihuituike()){

            $level = Db::name('longbing_admin')->where(['admin_id'=>$this->_user['admin_id']])->value('level');

            $this->changeUserCache($level);

            if($level==1){

                $data = [];

                foreach ($adminMenes as &$v){

                    if($v['path']=='/customer'){

                        $v['redirect'] = '/customer/talkingSkill';
                        $v['meta']['subNavName'] = array_splice($v['meta']['subNavName'],1);
                    }

                    if($v['path']!='/sys'){

                        $data[] = $v;
                    }
                }
            }

        }
        return $data;

    }



    public function getW7TmpV2(){
        global $_W;
        if(defined('IS_WEIQIN')){
            $w['footerleft'] = !empty($_W['setting']['copyright']['footerleft'])?$_W['setting']['copyright']['footerleft']:'';
            $w['version']    = !empty($_W['setting']['site']['version'])?$_W['setting']['site']['version']:'';
            $w['icp']        = !empty($_W['setting']['copyright']['icp'])?$_W['setting']['copyright']['icp']:'';
        }else{
            $w = 1;
        }
        return $w;
    }


}
