<?php

namespace app\card\model;

use app\BaseModel;
use app\dynamic\model\CardStatistics;
use think\Model;


class CardCount extends BaseModel
{
    //定义表名
    protected $name = 'longbing_card_count';


    protected static function init ()
    {
        //TODO:初始化内容
    }
    public function getYesterdaylist($where){
        $data = $this->where($where)
            ->whereDay('create_time','yesterday')
            ->field('to_uid as user_id,type,sign,count(to_uid) as number,uniacid')
            ->group('to_uid')
            ->order('create_time','desc')
            ->select()
            ->toArray();
        if($data){
            $stat = new CardStatistics();
            foreach ($data as $key=>$val){
                $data[$key]['create_time'] = strtotime("-1 day");
                $data[$key]['table'] = 'CardCount';
//                $whe['user_id'] = $val['to_uid'];
//                $whe['type'] = $val['type'];
//                $whe['sign'] = $val['sign'];
//                $info = $stat->getUserid($whe);
//                if($info){
//                    $stat->where($whe)->inc('number',$val['number'])->update();
//                }else{
//                    $whe['number'] =$val['number'];
//                    $stat->addinfo($whe);
                }
//            }print_r(123);exit;
            $stat->createRows($data);
        }
        return $data;
    }
    //昨日数据
    public function getYesterday($where){
        return $this->where($where)->whereDay('create_time','yesterday')->count();
    }
    //今日数据
    public function gettoday($where){
        return $this->where($where)->whereDay('create_time')->count();
    }
    //新增咨询
    public function zxInfo($user_id,$where=[],$type=0){
        if($type==1){
            $de = '=';
        }else{
            $de = 'in';
        }
        $where1 = [
            ['to_uid',$de,$user_id],
            ['sign','=','copy'],
            ['type','=','8'],
            $where
        ];
        $where2 = [
            ['to_uid',$de,$user_id],
            ['sign','=','praise'],
            ['type','=','5'],
            $where
        ];
        $count = $this->where([array_filter($where1)])->whereor([array_filter($where2)])->count();
        return $count;
    }
    public function getCount($where5){
        $data = $this->where($where5)->count();
        return $data;
    }
    public function radarList ( $where = [], $page = 1, $list_rows = 20 )
    {
        $data = self::where( $where )
            ->alias( 'a' )
            ->field( [ 'a.*','b.nickName as name']
            )
            ->join( 'longbing_card_user b', 'a.user_id = b.id' )
            ->join( 'longbing_card_collection c', 'a.user_id = c.uid && a.to_uid = c.to_uid' )
            ->order( [ 'a.id' => 'desc' ] )
            ->paginate( [ 'list_rows' => $list_rows, 'page' => $page ]
            )
            ->toArray();

        //  查询手机号
//        $tmpArr = [];
//        foreach ( $data[ 'data' ] as $index => $item )
//        {
//            $data[ 'data' ][ $index ][ 'phone' ] = '';
//            array_push( $tmpArr, $item[ 'user_id' ] );
//        }
//        if ( !empty( $tmpArr ) )
//        {
//            $tmpArr = array_unique( $tmpArr );
//
//            $list = UserPhone::where( [ [ 'user_id', 'in', $tmpArr ] ] )
//                ->field( [ 'user_id', 'phone' ] )
//                ->select()
//                ->toArray();
//
//            $tmpArr = [];
//            foreach ( $list as $index => $item )
//            {
//                $tmpArr[ $item[ 'user_id' ] ] = $item[ 'phone' ];
//            }
//            foreach ( $data[ 'data' ] as $index => $item )
//            {
//                if ( isset( $tmpArr[ $item[ 'user_id' ] ] ) )
//                {
//                    $data[ 'data' ][ $index ][ 'phone' ] = $tmpArr[ $item[ 'user_id' ] ];
//                }
//            }
//        }

        if (!empty($data[ 'data' ]))
        {
            //  处理雷达展示日期
            //$data[ 'data' ] = lbHandelRadarDate( $data[ 'data' ] );

            //  处理用户来源
            //$data[ 'data' ] = lbHandelRadarSource( $data[ 'data' ] );

            //  处理雷达激励提醒文案
            $data[ 'data' ] = lbHandelRadarMsg( $data[ 'data' ] );
        }
        return $data;
    }
    //兴趣占比
    public function doPageBossInterest ($where,$uniacid)
    {

        $filter = [
            ['sign','=','copy'] , ['uniacid','=',$uniacid],$where,[ 'type', 'in', [ 6, 7 ,11] ]
        ];
        $filter1 = [
            ['sign','=','view'] ,   [ 'type', '=', 6 ],['uniacid','=',$uniacid],$where
        ];
////        //  对公司感兴趣
        $compony =  $this->where([$filter])->whereor([$filter1])->count();
        $filter2 = [
            ['sign','=','copy'] , ['uniacid','=',$uniacid],$where,[ 'type', '=', 8 ]
        ];
        $where3 = [
            ['sign','=','view'] ,['uniacid','=',$uniacid],$where,[ 'type', 'in', [ 1, 2, 11, 15, 16, 17, 19, 20, 25, 26, 27, 28, 29, 30 ] ]
        ];
        $where21 = [
            ['sign','=','praise'] ,  ['uniacid','=',$uniacid],$where, [ 'type', 'in', [ 5, 6, 7, 8 ] ]
        ];
        $where31 = [
            ['sign','=','order'] ,['uniacid','=',$uniacid],$where
        ];

        //  对产品感兴趣
        $goods =  $this->where([$filter2])->whereor([$where3])->whereor([$where21])->whereor([$where31])->count();

        //  对员工感兴趣
        $where4 = [
            ['sign','=','copy'],['uniacid','=',$uniacid],$where, [ 'type', 'in', [ 1, 2, 3, 4, 5, 9, 10 ] ]
        ];
        $where5 = [
            ['sign','=','praise'],['uniacid','=',$uniacid],$where,[ 'type', 'in', [ 1, 2, 3, 4 ] ]
        ];
        $staff = $this->where([$where4])->whereor([array_filter($where5)])->count();

        $total = $compony + $goods + $staff;
        $data  = [
             [
                'data' => $compony,
                'rate'   => 0,
                 'name'=>'对公司感兴趣'
            ],
             [
                'data' => $goods,
                'rate'   => 0,
                 'name'=>'对产品感兴趣'
            ],
             [
                'data' => $staff,
                'rate'   => 0,
                 'name'=>'对我感兴趣'
            ],
        ];
        if ( $total ) {
            foreach ( $data as $k => $v ) {
                $data[ $k ][ 'rate' ] = sprintf( "%.2f", $v[ 'data' ] / $total ) * 100;
            }
        }
        return $data;
    }
    //活跃度0,1,2 $type 单个用户还是多个
//    public function doPageActivity ($uid,$type=0)
//    {
//        if($type ==1){
//            $str = 'in';
//            $last = 7;
//        }else{
//            $str = '=';
//            $last = 30;
//        }
//        //$forward = new CardForward();
//        //$stat = new CardStatistics();
//        $card_count = new CardCount();
//        $data = [];
//        $ss = [];
//        for ( $i = 0; $i < $last; $i++ ) {
//            $beginTime                 = mktime( 0, 0, 0, date( 'm' ), date( 'd' ) - $i, date( 'Y' ) );
//            $endTime                   = mktime( 0, 0, 0, date( 'm' ), date( 'd' ) - $i + 1, date( 'Y' ) ) - 1;
//            $date                      = date( 'm/d', $beginTime );
//            $data[ $i ][ 'date' ]      = $date;
//            $data[ $i ][ 'beginTime' ] = $beginTime;
//            $data[ $i ][ 'endTime' ]   = $endTime;
//
//
////            $where1[] = [
////                ['user_id',$str,$uid],['table','=','forward'],['create_time','BETWEEN',[$beginTime,$endTime]]
////            ];
//           // $count2 = $forward->forwardCount($where1);
////            $wherez = [['sign','=','praise'], ['type','=',4],['to_uid','=',$uid],['uniacid','=',$this->_uniacid],['create_time','BETWEEN',[$beginTime,$endTime]]];
////            $count2 =$card_count->getCount($wherez);
//            //$count2 = $stat->getCustomerCount($where1);
//            $card_message = new CardMessage();
////            $where2[] = [
////                ['user_id',$str,$uid],['table','=','message'],['create_time','BETWEEN',[$beginTime,$endTime]]
////            ];
//            $where3 = [['status','=',1], ['deleted','=',0],['target_id',$str,$uid],['uniacid','=',$this->_uniacid],['create_time','BETWEEN',[$beginTime,$endTime]]];
//            $count3 =  $card_message->getCount($where3);
//            //$count3 = $stat->getCustomerCount($where2);
//
//            //$card_count = new CardCount();
//            $where4 = [['sign','=','copy'],['to_uid',$str,$uid],['uniacid','=',$this->_uniacid],['create_time','BETWEEN',[$beginTime,$endTime]]];
////            $where3[] = [
////                ['user_id',$str,$uid],['create_time','BETWEEN',[$beginTime,$endTime],['sign','=','copy']]
////            ];
//            $count4 = $card_count->getCount($where4);
////            $where3[] = [
////                ['user_id',$str,$uid],['create_time','BETWEEN',[$beginTime,$endTime],['sign','=','view']]
////            ];
//            $where5 = [['sign','=','view'],['to_uid',$str,$uid],['uniacid','=',$this->_uniacid],['create_time','BETWEEN',[$beginTime,$endTime]]];
//            $count5 = $card_count->getCount($where5);
//            $where3[] = [
//                ['user_id',$str,$uid],['create_time','BETWEEN',[$beginTime,$endTime],['sign','=','praise']]
//            ];
//            $where6 = [['sign','=','praise'],['to_uid',$str,$uid],['uniacid','=',$this->_uniacid],['create_time','BETWEEN',[$beginTime,$endTime]]];
//            $count6 = $card_count->getCount($where6);
//
////            $count6 = $stat->getCustomerCount($where3);$count2 +
//            $count   =  $count3 + $count4 + $count5 + $count6;
//            $data[ $i ][ 'number' ] = $count;
//            $ss[] = $count;
//        }
//        $arr = max($ss);
//        if($arr ==0){
//            $arr =5;
//        }
//        return ['max'=>$arr,'data'=>$data];
//    }
    public function active (  $staff_id, $uniacid,$type=0 )
    {
        if($type ==1){
            $str = 'in';
           // $last = 7;['uniacid'=>$uniacid],
        }else{
            $str = '=';
           // $last = 30;
        }
        $data = [];


        $lastday = date( 'Y/m/d' );

        $number = self::where( [  [ 'to_uid',$str, $staff_id ], [ 'create_time', 'between',
                [ strtotime( $lastday . ' 00:00:00' ), strtotime( $lastday . ' 23:59:59' ) ] ] ]
        )
            ->count();
        $data[] = [ 'date' => date( 'm/d' ), 'number' => $number ];

        $max = $number;

        for ( $i = 1; $i < 15; $i++ )
        {

            $start_time = strtotime( "$lastday -$i days" );

            $md  = date( 'm/d', $start_time );
            $ymd = date( 'Y/m/d', $start_time );

            $end_time = strtotime( $ymd . ' 23:59:59' );

            $number = self::where( [  [ 'to_uid',$str, $staff_id ],
                    [ 'create_time', 'between', [ $start_time, $end_time ] ] ]
            )
                ->count();

            if ( $number > $max )
            {
                $max = $number;
            }


            $data[] = [ 'date' => $md, 'number' => $number ];
        }

        return [ 'data'=>$data, 'max'=>$max ];
    }
    public function getsss($staff_id,$uniacid,$type=0){
        if($type==1){
            $str = '=';
        }else{
            $str = 'in';
        }
        $data = [ 'max_number' => 0, 'total' => 0, 'data' => [] ];

        //咨询产品
        $count = self::where( [ [ 'sign', '=', 'copy' ], [ 'to_uid',$str, $staff_id ],
                [ 'type', '=', 8 ],[ 'uniacid', '=', $uniacid ] ]
        )
            ->count();

        $data[ 'max_number' ] = $count;
        $data[ 'total' ]      += $count;
        $data[ 'data' ][]     = [ 'name' => '咨询产品', 'number' => $count ];

        //  保存电话
        $count = self::where( [ [ 'sign', '=', 'copy' ], [ 'to_uid', $str, $staff_id ],
                [ 'type', '=', 1 ] ,[ 'uniacid', '=', $uniacid ]]
        )
            ->count();

        if ( $count > $data[ 'max_number' ] )
        {
            $data[ 'max_number' ] = $count;
        }
        $data[ 'total' ]  += $count;
        $data[ 'data' ][] = [ 'name' => '保存电话', 'number' => $count ];

        //  拨打电话
        $count = self::where( [ [ 'sign', '=', 'copy' ], [ 'to_uid', $str, $staff_id ],[ 'uniacid', '=', $uniacid ],
                [ 'type', 'in', [ 2, 3, 11 ] ] ]
        )
            ->count();

        $count += self::where( [ [ 'sign', '=', 'praise' ],  [ 'to_uid', $str, $staff_id ],[ 'uniacid', '=', $uniacid ],
                [ 'type', '=', 7 ] ]
        )
            ->count();

        if ( $count > $data[ 'max_number' ] )
        {
            $data[ 'max_number' ] = $count;
        }
        $data[ 'total' ]  += $count;
        $data[ 'data' ][] = [ 'name' => '拨打电话', 'number' => $count ];



        $view_base_filter = [
            ['to_uid', $str, $staff_id],
            ['sign', '=', 'view'],[ 'uniacid', '=', $uniacid ]
        ];

        //浏览商城
        $view_shop_filter = $view_base_filter;
        $view_shop_filter[] = ['type' , '=' , 1];
        $view_shop_number = self::where($view_shop_filter)->count();
        $data['total'] += $view_shop_number;
        $data['data'][] = ['name' => '浏览商城', 'number' => $view_shop_number];


        //浏览商品
        $view_goods_filter = $view_base_filter;
        $view_goods_filter[] = ['type' , '=' , 2];
        $view_goods_number = self::where($view_goods_filter)->count();
        $data['total'] += $view_goods_number;
        $data['data'][] = ['name' => '浏览商品', 'number' => $view_goods_number];

        //查看官网
        $view_official_web_filter = $view_base_filter;
        $view_official_web_filter[] = ['type' , '=' , 6];
        $view_official_web_number = self::where($view_official_web_filter)->count();
        $data['total'] += $view_official_web_number;
        $name = tabbarName(4,$uniacid);

        $data['data'][] = ['name' =>!empty($name)?'查看'.$name: '查看官网', 'number' => $view_official_web_number];


        //浏览动态
        $view_timeline_filter = $view_base_filter;
        $view_timeline_filter[] = ['type' , '=' , 3];
        $view_timeline_number = self::where($view_timeline_filter)->count();
        $data['total'] += $view_timeline_number;
        $data['data'][] = ['name' => '浏览动态', 'number' => $view_timeline_number];


        //点赞
        $praise_filter = [
            ['to_uid', $str, $staff_id],[ 'uniacid', '=', $uniacid ],
            ['sign', '=', 'praise'],
            ['type', 'IN', [1, 3]]
        ];
        $praise_number = self::where($praise_filter)->count();
        $data['total'] += $praise_number;
        $data['data'][] = ['name' => '点赞', 'number' => $praise_number];



        //分享名片
        $share_filter = [
            ['to_uid', $str, $staff_id],[ 'uniacid', '=', $uniacid ],
            ['sign', '=', 'praise'],
            ['type', '=', 4]
        ];
        $share_number = self::where($share_filter)->count();
        $data['total'] += $share_number;
        $data['data'][] = ['name' => '分享', 'number' => $share_number];




        //按number倒序
        $sort_arr = [];
        foreach ($data['data'] as $k => $v) {
            $sort_arr[] = $v['number'];
        }

        array_multisort($sort_arr, SORT_DESC, $data['data']);
        $data['max_number'] = $data['data'][0]['number'] ?? 0;

        //计算rate
        foreach ($data['data'] as $index => $item) {
            if ($data['max_number'] == 0) {
                $rate = 0;
            } else {
                $rate = $item['number'] / $data['max_number'] * 100;
            }
            $rate = sprintf("%.0f", $rate);
            $data['data'][$index]['rate'] = $rate;
        }

        return $data;
    }
    public function getCountUser($where,$time){
        $data = $this->alias('a')
            ->join( 'longbing_card_user_info b', 'a.to_uid = b.fans_id' )
            ->where($where)
            ->whereDay('a.create_time',$time)
            ->group('user_id')
            ->count();
        return $data;
    }
}