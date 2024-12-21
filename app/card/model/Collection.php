<?php

namespace app\card\model;

use app\BaseModel;
use app\dynamic\model\CardShopOrder;
use app\dynamic\model\CardStatistics;
use app\dynamic\model\UserInfo;
use app\radar\model\RadarCount;
use think\facade\Db;
use think\Model;


class Collection extends BaseModel
{
    //定义表名
    protected $name = 'longbing_card_collection';


    protected static function init ()
    {
        //TODO:初始化内容
    }

    /**
     * @Purpose: 绑定的员工列表
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function bindCardList ( $userId, $userInfo, $page = 1, $uniacid = 0 )
    {
        $page = intval( $page );

        $page = $page ? $page : 1;

        $data = self::alias( 'a' )
                    ->join( 'longbing_card_user_info b', 'a.to_uid = b.fans_id' )
                    ->join( 'longbing_card_job c', 'b.job_id = c.id', 'LEFT' )
                    ->where( [ [ 'a.uniacid', '=', $uniacid ], [ 'a.uid', '=', $userId ], [ 'a.status', '=', 1 ],
                                 [ 'b.is_staff', '=', 1 ], [ 'a.to_uid', '<>', $userId ] ]
                    )
                    ->field( [ 'a.to_uid', 'b.name', 'b.avatar', 'b.job_id', 'b.phone', 'b.email', 'c.name as job_name' ,'b.name']
                    )
                    ->order( [ 'a.id' => 'desc' ] )
                    ->paginate( [ 'list_rows' => 10, 'page' => $page ]
                    )
                    ->toArray();


        //  自己如果是员工则把自己返回在第一页第一个
//      if ( $userInfo[ 'is_staff' ] == 1 && $page == 1 )
//      {
//          $card = self::alias( 'a' )
//                      ->where( [ [ 'a.uniacid', '=', $uniacid ], [ 'a.uid', '=', $userId ], [ 'a.status', '=', 1 ],
//                                   [ 'b.is_staff', '=', 1 ], [ 'a.to_uid', '=', $userId ] ]
//                      )
//                      ->field( [ 'a.to_uid', 'b.name', 'b.avatar', 'b.job_id', 'b.phone', 'b.email', 'c.name as job_name' ]
//                      )
//                      ->join( 'longbing_card_user_info b', 'a.to_uid = b.fans_id' )
//                      ->join( 'longbing_card_job c', 'b.job_id = c.id', 'LEFT' )
//                      ->find();
//          if ($card)
//          {
//              $card = $card->toArray();
//
//              $data[ 'data' ] = array_merge([$card], $data[ 'data' ]);
//          }
//      }

        foreach ($data[ 'data' ] as $index => $item)
        {
            if ($item['job_name'] === null)
            {
                $data[ 'data' ][$index]['job_name'] = '未设置职位';
            }
        }

        $data[ 'data' ] = transImages( $data[ 'data' ], [ 'avatar' ] );

        return $data;
    }

    /**
     * @Purpose: 默认推荐员工列表
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function defaultCardList ($page = 1, $uniacid = 0 ,$staff_id = null)
    {
        $page = intval( $page );

        $page = $page ? $page : 1;

        $modelUserInfo = new UserInfo();
        //Update by jingshuixian    'a.fans_id', 'a.fans_id as to_uid',  修复无法访问的bug
        $where = [ [ 'a.uniacid', '=', $uniacid ], [ 'a.is_default', '=', 1 ], [ 'a.is_staff', '=', 1 ]];
        if(!empty($staff_id)) $where[] = ['fans_id' ,'<>' , $staff_id];
        $data = $modelUserInfo->alias('a')
                              ->where($where)
                               ->field( [ 'a.fans_id', 'a.fans_id as to_uid', 'a.name', 'a.avatar', 'a.job_id', 'a.phone', 'a.email', 'b.name as job_name' ]
                              )
                              ->join( 'longbing_card_job b', 'a.job_id = b.id', 'LEFT' )
                              ->order( [ 'a.top' => 'desc' ] )
                              ->paginate( [ 'list_rows' => 10, 'page' => $page ]
                              )
                              ->toArray();

        foreach ($data[ 'data' ] as $index => $item)
        {
            if ($item['job_name'] === null)
            {
                $data[ 'data' ][$index]['job_name'] = '未设置职位';
            }
        }

        $data[ 'data' ] = transImages( $data[ 'data' ], [ 'avatar' ] );
        
        return $data;
    }
    //收藏名片
    public function createCollection($data)
    {
        $data['create_time'] = time();
//        var_dump($data);die;
        $result = $this->save($data);
        return !empty($result);
    }
    //修改
    public function updateCollection($filter ,$data)
    {
        $data['update_time'] = time();
        $result = $this->where($filter)->update($data);
        return !empty($result);
    }
    //获取收藏名片总数
    public function getCollectionCount($filter)
    {
        $result = $this->where($filter)->count();
        return $result;
    }
    //获取收藏名片信息
    public function checkCollection($filter)
    {
        $result = $this->where($filter)->field('id ,status')->find();
        if($result) $result = $result->toArray();
        return $result;
    }
    //获去信息
    public function getCollection($filter)
    {
        $result = $this->where($filter)->find();
        if($result) $result = $result->toArray();
        return $result;
    }
    //获取需要更新的rate数据总数
//  public function getCollectionJoinRateCount()
//  {
//      $time = time() - 24*60*60;
//      $result = $this->alias('a')
//                     ->leftJoin('longbing_card_rate b' ,'a.uid = b.user_id && a.to_uid = b.staff_id && a.uniacid = b.uniacid')
////                     ->whereOr([['b.update_time' ,'<' ,$time] ,['b.id' , '=',null]])
////                     ->fetchSql()
//                     ->count();
////      var_dump($result);die;
//      return $result;
//  }
    //获取需要更新的rate数据
//  public function listCollectionJoinRate($page_config = ['page' => 1 ,'page_count' =>200])
//  {
//      $time = time() - 24*60*60;
//      $result = $this->alias('a')
//                     ->leftJoin('longbing_card_rate b' ,'a.uid = b.user_id && a.to_uid = b.staff_id && a.uniacid = b.uniacid')
//                     ->whereOr([['b.update_time' ,'<' ,$time] ,['b.id' ,'=' ,null]])
//                     ->field('a.id ,b.id as rate_id')
//                     ->page($page_config['page'] ,$page_config['page_count'])
//                     ->select();
//      if(!empty($result)) $result = $result->toArray();
//      return $result;
//  }
//  
    public function getCollectionJoinRateCount()
    {
        $time = time() - 24*60*60;
        $result = $this->where([['update_rate_time' ,'<' ,$time]])->count();
//      var_dump($result);die;
        return $result;
    }
    //获取需要更新的rate数据
    public function listCollectionJoinRate($page_config = ['page' => 1 ,'page_count' =>200])
    {
        $time = time() - 24*60*60;
        $result = $this->where([['update_rate_time' ,'<' ,$time]])
                       ->field('id')
                       ->page($page_config['page'] ,$page_config['page_count'])
                       ->select();
        if(!empty($result)) $result = $result->toArray();
        return $result;
    }
    public function getYesterdaylist($where){
        $data = $this->alias('a')
            ->join( 'longbing_card_user_info b', 'a.to_uid = b.fans_id')
            ->join( 'longbing_card_user d', 'b.fans_id = d.id && d.is_staff = 1','left')
            ->join( 'longbing_card_job c', 'c.id = b.job_id ','left')
            ->field('a.to_uid,count(a.to_uid) as number,a.uniacid,b.name,avatar,c.name as job_name,nickName,avatarUrl')
            ->where($where)
            ->limit(3)
            ->group('a.to_uid')
            ->order('number desc ,a.to_uid asc')
            ->whereDay('a.create_time','yesterday')
            ->select()->toArray();
        foreach ($data as $k=>$v){
            if(!$v['name']){
                $data[$k]['name'] = $v['nickName'];
            }
            if(!$v['avatar']){
                $data[$k]['avatar'] = $v['avatarUrl'];
            }
            if(!$v['job_name']){
                $data[$k]['job_name'] = '未设置职位';
            }
        }
        $data = transImagesOne($data,['avatar']);
        return $data;
    }

    public function getlistAll($where,$page,$list_rows,$type =0,$desc = 0){
        if($desc == 0){
            $de = 'asc';
        }else{
            $de = 'desc';
        }
        if($desc == 0){
            $de1 = 'desc';
        }else{
            $de1 = 'asc';
        }
        $data = $this->alias('a')
            ->join( 'longbing_card_user_info b', 'a.to_uid = b.fans_id')
            ->join( 'longbing_card_user d', 'b.fans_id = d.id && d.is_staff = 1','left')
            ->join( 'longbing_card_job c', 'c.id = b.job_id ','left')
            ->field('a.to_uid,count(a.to_uid) as number,a.uniacid,b.name,avatar,c.name as job_name,nickName,avatarUrl')
            ->where($where)
            ->group('a.to_uid')
            ->order("number $de,a.to_uid $de1")
            ->paginate([ 'list_rows' => $list_rows, 'page' => $page ])->each(function ($item,$key)use ($type){
                if(!$item['name']){
                    $item['name'] = $item['nickName'];
                }
                if(!$item['avatar']){
                    $item['avatar'] = $item['avatarUrl'];
                }
                if(!$item['job_name']){
                    $item['job_name'] = '未设置职位';
                }
                return $item;
            })->toArray();
        $data = transImagesOne($data,['avatar']);
        return $data;
    }
    //查询昨天新增客户的数据
    public function getTodaylist($where){
        $data =  $this->alias('a')
            //->leftJoin( 'longbing_card_count b', 'a.uid = b.user_id && a.to_uid = b.to_uid' )
            ->join( 'longbing_card_user c', 'a.uid = c.id' )
            ->where($where)
            ->field('a.to_uid as user_id,count(a.to_uid) as number,a.uniacid')
            ->whereDay('a.create_time','yesterday')
            ->group( 'a.to_uid' )
            ->select()->toArray();
        if($data){
            foreach ($data as $key=>$val){
                $data[$key]['table'] = 'customer';
                $data[$key]['create_time'] = strtotime("-1 day");
            }
            $stat = new CardStatistics();
            $stat->createRows($data);
        }
        return $data;
    }
    public function getCount($where){
        return $this->alias('a')
            ->where($where)
            ->count();
    }
    //查询今天线索
    public function today($where){
        return $this->alias('a')
            ->leftJoin( 'longbing_card_count b', 'a.uid = b.user_id && a.to_uid = b.to_uid' )
                ->join( 'longbing_card_user c', 'a.uid = c.id' )
                ->where($where)
            ->whereDay('a.create_time')
            ->group( 'a.uid, a.to_uid' )
            ->count();
    }
    //查询七天类的客户数量
    public function weekToday($where){
        $weekday = date("Y-m-d",strtotime("-6 day"));
        $data = $this->alias('a')
            ->leftJoin( 'longbing_card_count b', 'a.uid = b.user_id && a.to_uid = b.to_uid' )
            ->join( 'longbing_card_user c', 'a.uid = c.id' )
            ->where($where)
            ->whereWeek('a.update_time',$weekday)
            ->group( 'a.uid, a.to_uid' )
            ->count();
        return $data;
    }
    //查询昨天线索的数据
    public function Yesterday($where){
        return $this->alias('a')
            ->leftJoin( 'longbing_card_count b', 'a.uid = b.user_id && a.to_uid = b.to_uid' )
            ->join( 'longbing_card_user c', 'a.uid = c.id' )
            ->where($where)
            ->whereDay('a.create_time','yesterday')
            ->group( 'a.uid, a.to_uid' )
            ->count();
    }
//    public function getLookCount($where){
//        return $this->alias('a')
//            ->join( 'longbing_card_count b', 'a.uid = b.user_id && a.to_uid = b.to_uid' )
//            ->where($where)
//            ->count();
//    }
    //查询客户当天的数据
    public function todayLook($where){
        return $this->alias('a')
            ->join( 'longbing_card_count b', 'a.uid = b.user_id && a.to_uid = b.to_uid' )
            ->whereDay('a.update_time')
            ->where($where)
            ->count();
    }
    //更新变更线索人数
//    public function changeNember($where){
//        $data =  $this->where($where)
//            ->alias('a')
//            //->join( 'longbing_card_count b', 'a.uid = b.user_id && a.to_uid = b.to_uid' )
//            ->field('a.to_uid as user_id,count(a.to_uid) as number,a.uniacid')
//            //->whereDay('a.create_time')
//            ->group('a.to_uid')
//            ->select()
//            ->toArray();
//        if($data){
//            $stat = new CardStatistics();
//            foreach ($data as $key=>$val){
//                $id = $stat->getUserid(['user_id'=>$val['user_id'],'sign'=>'customer_no']);
//                if($id){
//                    $stat->updateinfo(['user_id'=>$val['user_id'],'sign'=>'customer_no'],['number'=>$val['number'],'create_time'=>strtotime("-1 day")]);
//                }else{
//                    $val['sign'] = 'customer_no';
//                    $val['create_time'] = strtotime("-1 day");
//                    $stat->addinfo($val);
//                }
//            }
//        }
//        return $data;
//    }
    //查询线索数据
    public function todayUid($where){
        $data = $this->alias('a')
                ->leftJoin( 'longbing_card_count b', 'a.uid = b.user_id && a.to_uid = b.to_uid' )
                ->join( 'longbing_card_user c', 'a.uid = c.id','left' )
                ->where($where)
                ->group( 'a.uid, a.to_uid' )
                ->count();
        return $data;
         }
    //线索池
    public function xian($where){
        $sc1 = $this->alias('a')
            //->join( 'longbing_card_collection b', 'a.uid = b.user_id' )
            ->field('uid,to_uid,intention')
            ->where($where)
            ->column('to_uid','uid');
//        if($sc1){
//            foreach ($sc1 as $key=>$value){
//                $uid = $this->where(['uid'=>$key,'intention'=>1])->value('uid');
//                if($uid){
//                    unset($sc1[$key]);
//                }
//            }
//        }
        return count($sc1);
    }
    //今天线索池
    public function Todayxian($where){
        $sc1 = $this->alias('a')
            //->join( 'longbing_card_collection b', 'a.uid = b.user_id' )
            ->field('uid,to_uid,intention')
            ->whereDay('create_time')
            ->where($where)
            ->column('to_uid','uid');
//        if($sc1){
//            foreach ($sc1 as $key=>$value){
//                $uid = $this->where(['uid'=>$key,'intention'=>1])->value('uid');
//                if($uid){
//                    unset($sc1[$key]);
//                }
//            }
//        }
        return count($sc1);
    }
    //总线索数量
    public function allXian($where){
       $data = $this->alias('a')->leftJoin( 'longbing_card_count b', 'a.uid = b.user_id && a.to_uid = b.to_uid' )
            ->join( 'longbing_card_user c', 'a.uid = c.id' )
            ->where($where)
            ->group( 'a.uid, a.to_uid' )->count();
        return $data;
    }
    public function getlistCount($where){
        return $this->where($where)->count();
    }
    //累计访问量
    public function visit($where){
        $visit_number = 0;
        $visit = $this->alias('a')
            ->join( 'longbing_card_count b', 'a.uid = b.user_id && a.to_uid = b.to_uid' )
            ->field('count(a.to_uid) as number')
            ->where($where)
            ->group('a.to_uid')
            // ->where($where)
            ->select()->toArray();
        if($visit){
            foreach ($visit as $value){
                $visit_number += $value['number'];
            }
        }
        return $visit_number;
    }
    public function staffInfo($where_s,$page,$list_rows){
        $data = $this->alias('a')
            ->join( 'longbing_card_user_info b', 'a.to_uid = b.fans_id' )
            ->join( 'longbing_card_company c', 'b.company_id = c.id')
            ->join( 'longbing_card_job d', 'b.job_id = d.id','left'  )
            ->join( 'longbing_card_user e', 'e.id = b.fans_id' )
            ->where($where_s)
            ->field('avatar,b.name,company_id,job_id,b.phone,a.create_time,c.name as company_name,d.name as job_name,b.fans_id,a.uniacid,b.create_time as start_time,a.to_uid,e.create_time as start_time')
            ->find();//
        $arr =[] ;
        $follow_info = [];
        $radar_count = [];
        $behavior = [];
        $ability = [];
        $Interest = [];
        $active = [];
        $start_time = 0;
        if($data){
            $data = $data ->toArray();
            if ($data['job_name'] === null)
            {
                $data[ 'job_name' ] = '未设置职位';
            }
            $timediff = time() - $data['start_time'];
            $data['day'] = intval($timediff / 86400);
            $data['time'] = '今天'.date('H:i',time());
 //           $card_value = new CardValue();
//            $rest = $card_value->bossGetAiValue($data['to_uid'],$data['uniacid']);
//            $data['total_info'] = $rest['data'];
            $data['create_time'] = date('Y-m-d',$data['create_time']);
                //新增线索
            $collect = new Collection();
            $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
            $endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;

            $beginYesterday=mktime(0,0,0,date('m'),date('d')-1,date('Y'));
            $endYesterday=mktime(0,0,0,date('m'),date('d'),date('Y'))-1;

            $beginbeforeYesterday=mktime(0,0,0,date('m'),date('d')-2,date('Y'));
            $endbeforeYesterday=mktime(0,0,0,date('m'),date('d')-1,date('Y'))-1;
            $user_id = $data['fans_id'];
            $stat = new CardStatistics();
            //累计线索
//            $wherex[] =[
//                ['user_id','=',$user_id],
//                ['uniacid','=',$this->_uniacid],
//                ['table','=','customer']
//            ];

            //查询关联公司的员工
//            $user_info   = new UserInfo();
            //一个公司的员工
//            $other_staff = $user_info->getIsStaff($user_id,$data['uniacid']);

            //日增长线索
            $whes[] = [
                ['intention','=',0],
                ['a.uid','<>',$user_id],['a.to_uid','=',$user_id],['a.uniacid','=',$data['uniacid']]

            ];
            $cumulative_clues = $collect->todayUid($whes);
            $todayKh = $collect->today($whes);
            //昨天线索
            $yesterdaytodayKh = $collect->Yesterday($whes);
            //前天的线索
            $whes[1] =[
                [ 'a.create_time', 'BETWEEN', [ $beginbeforeYesterday, $endbeforeYesterday ]]
            ];
            $beforXs = $collect->todayUid($whes);
            //昨天对比前天新增数
            $contrastKh =$yesterdaytodayKh-$beforXs;

            //线索池
            $whes1[] =[

                ['a.uid','<>',$user_id],['a.to_uid','=',$user_id],['a.uniacid','=',$data['uniacid']]
            ];
            $xs = $collect->todayUid($whes1);
            //今天的线索池
            $whes1[1] =[[ 'a.create_time', 'BETWEEN', [ $beginToday, $endToday ]]];
            $todayxs = $collect->todayUid($whes1);
            //昨天线索池
            $whes1[1] =[[ 'a.create_time', 'BETWEEN', [ $beginYesterday, $endYesterday ]]];
            $yesterdayxs = $collect->todayUid($whes1);
            //前天线索池
            $whes1[1] =[[ 'a.create_time', 'BETWEEN', [ $beginbeforeYesterday, $endbeforeYesterday ]]];
            $beforexs = $collect->todayUid($whes1);
            //昨天对比前天新增数
            $contrastxs =$yesterdayxs-$beforexs;
            $whez[] =[
                ['a.uid','<>',$user_id],
                ['intention','=',1],
                ['a.to_uid','=',$user_id],
                ['a.uniacid','=',$data['uniacid']]
            ];

//        //累计客户数量
           $customer = $collect->todayUid($whez);

            //日增长客户
            $today = $collect->today($whez);
            //日线索增长
            $addTodayxs = $todayxs-$today;
            //昨天客户数
            $yesterdaykh = $collect->Yesterday($whez);
            //前天的客户
            $whez[] = [
                [ 'a.update_time', 'BETWEEN', [ $beginbeforeYesterday, $endbeforeYesterday ]]
            ];
            $beforkh = $collect->todayUid($whez);
            //昨天对比前天新增数
            $contrastkh =$yesterdaykh-$beforkh;

            //累计访问量
            $card_count = new CardCount();
//            $visit_number = $stat->getCustomerCount([['user_id','=',$user_id],
//                ['sign','=','praise'],
//                ['type','=',2],['uniacid','=',$this->_uniacid]]);

            //今天的访问量
            $wheres[] = [
                ['to_uid','=',$user_id],
                ['sign','=','praise'],
                ['type','=',2],
                ['uniacid','=',$data['uniacid']]
            ];
            $visit_number = $card_count->getCount($wheres);
            $wheres[1] =[[ 'create_time', 'BETWEEN', [ $beginToday, $endToday ]]];
            $todayVisitNumber = $card_count->getCount($wheres);
            //昨天的访问量
            $wheres[1] = [
                [ 'create_time', 'BETWEEN', [ $beginYesterday, $endYesterday ] ],
            ];
            $yesterdayVisit = $card_count->getCount($wheres);
            //前天的访问量
            $wheres[1] = [
                [ 'create_time', 'BETWEEN', [ $beginbeforeYesterday, $endbeforeYesterday] ],
            ];
            $beforVisit = $card_count->getCount($wheres);
            //昨天对比前天新增数
            $contrastVisit =$yesterdayVisit-$beforVisit;

            //咨询
            //消息
            $whereMsg[] = [
                ['message_type','=','text'],
                ['deleted','=',0],
                ['uniacid','=',$this->_uniacid],
                ['target_id','=',$user_id]
            ];
            $card_message = new CardMessage();
            //累计消息
            $message = $card_message->getCount($whereMsg);
            //今天消息
            $whereMsg[1] = [
                [ 'create_time', 'BETWEEN', [ $beginToday, $endToday ] ],
            ];
            $today_message = $card_message->getCount($whereMsg);
            //昨天的消息
            $whereMsg[1] = [
                [ 'create_time', 'BETWEEN', [ $beginYesterday, $endYesterday ] ],
            ];
            $Yesterday_message = $card_message->getCount($whereMsg);
            //前天的消息
            $whereMsg[1] = [
                [ 'create_time', 'BETWEEN', [ $beginbeforeYesterday, $endbeforeYesterday ] ],
            ];
            $before_message = $card_message->getCount($whereMsg);
            $zx = $card_count->zxInfo($user_id,[],1);
            $zx = $zx+$message;
            $where1 =  ['create_time','BETWEEN',[ $beginToday, $endToday ]];
            $zxtoday = $card_count->zxInfo($user_id,$where1,1);
            $zxtoday = $zxtoday+$today_message;
            //昨天的咨询
            $where1 =  ['create_time','BETWEEN',[ $beginYesterday, $endYesterday ]];
            $zxYesterday = $card_count->zxInfo($user_id,$where1,1);
            $zxYesterday = $zxYesterday+$Yesterday_message;
            //前天的咨询
            $where1 =  ['create_time','BETWEEN',[ $beginbeforeYesterday, $endbeforeYesterday ]];
            $zxbefore = $card_count->zxInfo($user_id,$where1,1);
            $zxbefore = $zxbefore+$before_message;
            //昨天对比前天新增数
            $contrastzx =$zxYesterday-$zxbefore;

            //跟进客户次数
            $user_mark = new UserFollow();
            $where3[] = [
                ['staff_id','=',$user_id],
                ['status','=',1],
                //['uniacid','=',$this->_uniacid]
            ];
            $mark = $user_mark->getCount($where3);
            $where3[1] = [
                [ 'create_time', 'BETWEEN', [ $beginToday, $endToday ] ],
            ];
            $today_mark = $user_mark->getCount($where3);
            //昨天的跟进客户
            $where3[1] = [
                [ 'create_time', 'BETWEEN', [ $beginYesterday, $endYesterday ] ],
            ];
            $Yesterday_mark = $user_mark->getCount($where3);
            //前天的跟进客户
            $where3[1] = [
                [ 'create_time', 'BETWEEN', [ $beginbeforeYesterday, $endbeforeYesterday ] ],
            ];
            $before_mark = $user_mark->getCount($where3);
            $contrastzmark =$Yesterday_mark-$before_mark;
            //被转发次数
//            $forward = new CardForward();
            $where4[] = [
                ['to_uid','=',$user_id],
                ['type','=',4],
                ['sign','=','praise'],
                ['uniacid','=',$data['uniacid']]
            ];
            $forward_count = $card_count->getCount($where4);
            $today_forward_count =$card_count->gettoday($where4);
            //昨天转发
            $Yesterday_forward_count =$card_count->getYesterday($where4);
            //前天转发
            $where4[] = [
                [ 'create_time', 'BETWEEN', [$beginbeforeYesterday, $endbeforeYesterday] ],
            ];
            $before_forward_count =$card_count->getCount($where4);
            $contrastzforward =$Yesterday_forward_count-$before_forward_count;
            //累计被点赞次数
            $where5[] = [
                ['to_uid','=',$user_id],
                ['type','=',3],
                ['sign','=','praise'],
                ['uniacid','=',$data['uniacid']]
            ];
            $dz = $card_count->getCount($where5);
            $where5[1] = [
                [ 'create_time', 'BETWEEN', [ $beginToday, $endToday ] ],
            ];
            $today_dz = $card_count->getCount($where5);
            //昨天点赞
            $where5[1] = [
                [ 'create_time', 'BETWEEN', [$beginYesterday, $endYesterday] ],
            ];
            $Yesterday_dz = $card_count->getCount($where5);
            //前天点赞
            $where5[1] = [
                [ 'create_time', 'BETWEEN', [$beginbeforeYesterday, $endbeforeYesterday] ],
            ];
            $before_dz = $card_count->getCount($where5);
            $contrastzdz =$Yesterday_dz-$before_dz;
            //累计被保存次数
            $where6[] = [
                ['to_uid','=',$user_id],
                ['type','=',1],
                ['sign','=','copy'],
                ['uniacid','=',$data['uniacid']]
            ];
            $bc = $card_count->getCount($where6);
            $where6[1] = [
                [ 'create_time', 'BETWEEN', [ $beginToday, $endToday ] ],
            ];
            $today_bc = $card_count->getCount($where6);
            //昨天保存
            $where6[1] = [
                [ 'create_time', 'BETWEEN', [$beginYesterday, $endYesterday] ],
            ];
            $Yesterday_bc = $card_count->getCount($where6);
            //前天保存
            $where6[1] = [
                [ 'create_time', 'BETWEEN', [$beginbeforeYesterday, $endbeforeYesterday] ],
            ];
            $before_bc = $card_count->getCount($where6);
            $contrastzbc =$Yesterday_bc-$before_bc;
            //商城订单笔数和销售金额
            $admin_goods = new CardShopOrder();
            $where7[]= [
                ['company_id','=',$data['company_id']],
                ['to_uid','=',$user_id],
                ['uniacid','=',$data['uniacid']]
            ];
            $sales_count = $admin_goods->getGoosSale($where7);
            //昨天的商品销售
            $where7[] = [
                [ 'create_time', 'BETWEEN', [$beginYesterday, $endYesterday] ],
            ];
            $Yesterday_sales_count = $admin_goods->getGoosSale($where7);
            //数据
            $arr = [
                'count'=>[
                    [
                        [
                            'name'=>'累计线索(人)',
                            'number'=>$xs,
                            'rate'=>$todayKh,
                        ],
                        [
                            'name'=>'线索池(人)',
                            'number'=>$cumulative_clues,
                            // 'text'=>$title,
                            'rate'=>$addTodayxs,
                            // 'up'=>$up
                        ],
                        [
                            'name'=>'累计客户(人)',
                            'number'=>$customer,
                            'rate'=>$today,
                        ],
                        [
                            'name'=>'累计访问量(次)',
                            'number'=>$visit_number,
                            'rate'=>$todayVisitNumber,
                        ],
                        [
                            'name'=>'累计咨询(次)',
                            'number'=>$zx,
                            'rate'=>$zxtoday,
                        ],
                        [
                            'name'=>'累计跟进客户(次)',
                            'number'=>$mark,
                            'rate'=>$today_mark,
                        ],
                        [
                            'name'=>'累计被转发(次)',
                            'number'=>$forward_count,
                            'rate'=>$today_forward_count,
                        ],
                        [
                            'name'=>'累计被点赞(次)',
                            'number'=>$dz,
                            'rate'=>$today_dz,
                        ],
                        [
                            'name'=>'累计被保存(次)',
                            'number'=>$bc,
                            'rate'=>$today_bc,
                        ]
                    ],
                    [
                        [
                            'name'=>'新增线索(人)',
                            'number'=>$yesterdaytodayKh,
                            'rate'=>$contrastKh,
                        ],
                        [
                            'name'=>'变动线索池(人)',
                            'number'=>$yesterdayxs,
                            'rate'=>$contrastxs,
                        ],
                        [
                            'name'=>'新增客户(人)',
                            'number'=>$yesterdaykh,
                            'rate'=>$contrastkh,
                        ],
                        [
                            'name'=>'新增访问量(次)',
                            'number'=>$yesterdayVisit,
                            'rate'=>$contrastVisit,
                        ],
                        [
                            'name'=>'新增咨询(次)',
                            'number'=>$zxYesterday,
                            'rate'=>$contrastzx,
                        ],
                        [
                            'name'=>'新增跟进客户(次)',
                            'number'=>$Yesterday_mark,
                            'rate'=>$contrastzmark,
                        ],
                        [
                            'name'=>'新增被转发(次)',
                            'number'=>$Yesterday_forward_count,
                            'rate'=>$contrastzforward,
                        ],
                        [
                            'name'=>'新增被点赞(次)',
                            'number'=>$Yesterday_dz,
                            'rate'=>$contrastzdz,
                        ],
                        [
                            'name'=>'新增被保存(次)',
                            'number'=>$Yesterday_bc,
                            'rate'=>$contrastzbc,
                        ]
                    ],
                ],
                'goods_sale'=>[
                    $sales_count,$Yesterday_sales_count
                ]
            ];
            $title1 = '日增涨';
            $title2 = '日减少';
            $title3 = '持平 -';
            foreach ($arr['count'][0] as $key=>$value){
                if($value['rate']>0){
                    $arr['count'][0][$key]['text'] = $title1;
                    $arr['count'][0][$key]['up'] =1;
                }elseif ($value['rate']==0){
                    $arr['count'][0][$key]['up'] =-1;
                    $arr['count'][0][$key]['text'] = $title3;
                }else{
                    $arr['count'][0][$key]['up'] =0;
                    $arr['count'][0][$key]['text'] = $title2;
                }
                $arr['count'][0][$key]['rate'] = abs($value['rate']);
            }
            foreach ($arr['count'][1] as $k=>$val){

                if($val['rate']>0){
                    $arr['count'][1][$k]['text'] = $title1;
                    $arr['count'][1][$k]['up'] =1;
                }elseif ($val['rate']==0){
                    $arr['count'][1][$k]['up'] =-1;
                    $arr['count'][1][$k]['text'] = $title3;
                }else{
                    $arr['count'][1][$k]['up'] =0;
                    $arr['count'][1][$k]['text'] = $title2;
                }
                $arr['count'][1][$k]['rate'] = abs($val['rate']);
            }
            //跟进客户记录
            $follow_info = UserFollow::alias( 'a' )
                ->join( 'longbing_card_user b', 'a.user_id = b.id')
                ->field( [ 'content', 'a.create_time','user_id','b.nickName as name' ] )
                ->where( [
                        [ 'a.staff_id', '=',$user_id ] ]
                )->order( 'a.id', 'desc' )
                ->paginate( [ 'list_rows' => 4, 'page' => 1 ])->toArray();
            $follow_info['data'] = lbHandelRadarDate( $follow_info['data'], 'create_time' );
            foreach ($follow_info['data'] as $index => $item )
            {
                $follow_info['data'][ $index ][ 'create_time' ] = date( 'Y-m-d H:i', $item[ 'create_time' ] );
            }

            $ardar = new CardCount();
            $radar_count = $ardar->radarList([ [ 'a.to_uid', '=', $user_id ],[ 'a.user_id', '<>', $user_id ] ], 1,4);
            $radar_count['data'] = lbHandelRadarDate( $radar_count['data'], 'create_time' );
            if($radar_count['data']){
                foreach ($radar_count['data'] as $k => $v )
                {
                    $radar_count['data'][ $k ][ 'create_time' ] = date( 'Y-m-d H:i', $v[ 'create_time' ] );
                }
            }
        //客户行为
            $card_count = new CardCount();
            $behavior =  $card_count->getsss($user_id,$data['uniacid'],1);
            //能力雷达
            $card_value = new CardValue();

            $ability = $card_value->bossGetAiValue($user_id,$data['uniacid']);
           //兴趣占比
            $Interest = $ardar->doPageBossInterest(['to_uid','=',$user_id],$data['uniacid']);
            //客户活跃度
            $active = $ardar->active($user_id,$data['uniacid']);
            $timediff = time() - $data['start_time'];
            $start_time = intval($timediff / 86400);
        }
        $data['look'] =$arr;
        $data['interaction'] =$radar_count;
        $data['follow_info'] =$follow_info;
        $data['behavior'] =$behavior;
        $data['ability'] =$ability;
        $data['interest'] =$Interest;
        $data['active'] =$active;
        $data['day'] = $start_time;
        $data = transImagesOne($data, ['avatar']);
       return $data;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-03-30 15:06
     * @功能说明:获取浏览过的名片
     */
    public function getCard($uid,$uniacid){
        //查看浏览过的名片
        $data = $this->alias('a')
               ->join('longbing_card_user b', 'a.to_uid = b.id AND b.is_staff = 1')
               ->where(['uid'=>$uid])
               ->value('to_uid');

        //如果没有就给一个推荐的
        if(empty($data)){

            $data = Db::name('longbing_card_user_info')->where(['uniacid'=>$uniacid,'is_default'=>1])->value('fans_id');
        }
        //修改
        if(!empty($data)){
            //修改最近浏览的名片
            Db::name('longbing_card_user')->where(['id'=>$uid])->update(['last_staff_id'=>$data]);
            //增肌默认分配次数
            Db::name('longbing_card_user')->where(['id'=>$data])->inc('auto_count');
            //缓存的键
            $key = 'longbing_card_user_' . $uid;
            //删除缓存
            delCache($key,$uniacid);
        }
        return $data;
    }
}