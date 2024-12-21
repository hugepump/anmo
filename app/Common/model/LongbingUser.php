<?php
namespace app\Common\model;
use app\BaseModel;
use app\Common\model\LongbingCardUserMark as MarkModel;

use app\Common\model\LongbingClientInfo as ClientModel;
use app\Common\model\LongbingCardCollection as CollectionModel;
use think\facade\Db;

class LongbingUser extends BaseModel
{
    //定义表名称
    protected $name = 'longbing_card_user';
    
    //获取用户信息
    public function getUser($filter)
    {
        $filter['deleted'] = 0;
        $result = $this->where($filter)->find();
        if(!empty($result)) $result = $result->toArray();
        return $result;
    }

    public function listCustomerData($filter ,$page_config)
    {
        $uniacid = $filter['uniacid'];
        //设置查询条件
        $where = [
            ['user.uniacid'  ,'=' , $uniacid],
            ['user.is_staff' ,'=' , 0]
        ];
        //判断是否根据昵称查询
//      if(isset($filter['nickName']))
//      {
//          $where[] = ['nickName' ,'like' ,'%' . $filter['nickName'] . '%'];
//      }
        //判断是否查询电话号码授权
        if(isset($filter['is_phone']) && !empty($filter['is_phone'])) $where[] = ['phone.phone' ,'<>' ,'null'];

        //判断是否授权头像/登陆信息
        if(isset($filter['avatarUrl']) && !empty($filter['avatarUrl'])) $where[] = ['user.avatarUrl' ,'<>' ,''];

        //设置转换参数

        $mark_arr = array(
            0 => array(
                'id' => 3,
                'value' => '未跟进'
            ),
            1 => array(
                'id' => 1,
                'value' => '跟进中'
            ),
            2 => array(
                'id' => 2,
                'value' => '已成交'
            ),
        );

        $deal_arr = array(
            0 => array(
                'id' => 1,
                'value' => '未成交'
            ),
            1 => array(
                'id' => 2,
                'value' => '已成交'
            ),
        );
        $count = 0;
        $users = [];
        $mark_value = '';
        $deal_value = '';
//      var_dump(isset($filter['mark']) || isset($filter['deal']) || isset($filter['nickName']));
        if (isset($filter['mark']) || isset($filter['deal']) || isset($filter['nickName']))
        {
            $user_ids = array();
            if (isset($filter['mark']))
            {
                $mark_value = $filter['mark'];
                //未跟进
                if ($filter['mark'] == 3)
                {
                    //$list_mark = pdo_getall('longbing_card_user_mark', ['uniacid' => $uniacid]);
                    //获取已经跟进的客户数据
                    $mark_model = new MarkModel();

                    $list_mark = $mark_model->listMarkData(['uniacid' => $uniacid ,'status' => 1] ,['user_id']);

                    $tmp_arr = array();
                    foreach ($list_mark as $k => $v)
                    {
                        array_push($tmp_arr, $v['user_id']);
                    }
                    //查询为跟进的数据
                    if (count($tmp_arr) > 1)
                    {
                        $tmp_arr = '(' . implode(',', $tmp_arr) . ')';
                        $list_mark = $this->where([['uniacid' , '=' ,$uniacid] , ['id' ,'not in' , $tmp_arr] , ['is_staff' ,'=' , 0]])->field(['id'])->select();
                    }
                    else if (count($tmp_arr) == 1)
                    {
                        $tmp_arr = implode(',', $tmp_arr);
                        $list_mark = $this->where([['uniacid' , '=' ,$uniacid] , ['id' ,'!=' , $tmp_arr] , ['is_staff' ,'=' , 0]])->field(['id'])->select();
                    }
                    else
                    {
                        $list_mark = $this->where([['uniacid' , '=' ,$uniacid] , ['is_staff' ,'=' , 0]])->field(['id'])->select();;
                    }
                    //判断数据是否为空
                    if(empty($list_mark)) $list_mark = [];
                    //获取未跟进用户id列表
                    foreach ($list_mark as $k => $v)
                    {
                        array_push($user_ids, $v['id']);
                    }
                }
                //已跟进数据
                else
                {
                    $mark_model = new MarkModel();
                    $list_mark = $mark_model->listMarkData(['uniacid' => $uniacid ,'status' => 1] ,['user_id']);
                    foreach ($list_mark as $k => $v)
                    {
                        array_push($user_ids, $v['user_id']);
                    }
                }
            }
            //成交
            if (isset($filter['deal']))
            {
                $deal_value = $filter['deal'];
                //未成交
                if ($filter['deal'] == 1)
                {
                    //获取已成交的数据
                    $mark_model = new MarkModel();
                    $list_mark = $mark_model->listMarkData(['uniacid' => $uniacid ,'mark' => 2 ,'status' => 1] ,['user_id']);
                    if(empty($list_mark)) $list_mark = [];
                    //跟进状态查询
                    if (isset($filter['mark']))
                    {
                        //获取用户id数据
                        $tmp1 = array();
                        foreach($list_mark as $v)
                        {
                            if(!in_array($v['user_id'], $tmp1)) $tmp1[] = $v['user_id'];
                        }
                        $tmp2 = [];
                        foreach($user_ids as $v)
                        {
                            if(!in_array($v, $tmp1)) $tmp2[] = $v;
                        }
                        $user_ids = $tmp2;
                    }
                    else
                    {

                        $tmp_arr = array();
                        foreach ($list_mark as $k => $v)
                        {
                            array_push($tmp_arr, $v['user_id']);
                        }

                        if (count($tmp_arr) > 1)
                        {
                            $tmp_arr = '(' . implode(',', $tmp_arr) . ')';
                            $list_mark = $this->where([['uniacid' , '=' ,$uniacid] , ['id' ,'not in' , $tmp_arr] , ['is_staff' ,'=' , 0]])->field(['id'])->select();
                        }
                        else if (count($tmp_arr) == 1)
                        {
                            $tmp_arr = implode(',', $tmp_arr);
                            $list_mark = $this->where([['uniacid' , '=' ,$uniacid] , ['id' ,'!=' , $tmp_arr] , ['is_staff' ,'=' , 0]])->field(['id'])->select();
                        }
                        else
                        {
                            $list_mark = $this->where([['uniacid' , '=' ,$uniacid] , ['is_staff' ,'=' , 0]])->field(['id'])->select();;
                        }
                        if(empty($list_mark)) $list_mark = [];

                        foreach ($list_mark as $k => $v)
                        {
                            array_push($user_ids, $v['id']);
                        }
                    }
                }
                //已成交
                else
                {
                    //获取已经成交的数据
                    $mark_model = new MarkModel();
                    $list_mark = $mark_model->listMarkData(['uniacid' => $uniacid ,'mark' => 2 ,'status' => 1] ,['user_id']);
                    //如果根据跟进状态查询
                    if (isset($filter['mark']))
                    {
                        //取两者交集
                        $tmp1 = array();
                        $tmp2 = array();
                        foreach ($list_mark as $k => $v)
                        {
                            if(!in_array($v['user_id'], $tmp1)) $tmp1[] = $v['user_id'];
                        }
                        foreach($tmp1 as $v)
                        {
                            if(in_array($v, $user_ids) && !in_array($v, $tmp2)) $tmp2[] = $v;
                        }

                        $user_ids = $tmp2;
                    }
                    else
                    {
                        foreach ($list_mark as $k => $v)
                        {
                            array_push($user_ids, $v['user_id']);
                        }
                    }
                }
            }

            if (isset($filter['nickName']))
            {
//              $keyword = $filter['search'];
                $search = '%' . $filter['nickName'] . '%';
//              $users1 = pdo_getall('longbing_card_client_info', ['uniacid' => $uniacid, 'name like' => $search]);
                $client_model = new ClientModel();
                $users1 = $client_model->listClientData([['uniacid' ,'=' ,$uniacid] ,['name' ,'like' ,$search]] ,['user_id']);
//              $users2 = pdo_getall('longbing_card_user', ['uniacid' => $uniacid, 'nickName like' => $search]);
                $users2 = $this->where([['uniacid' ,'=' ,$uniacid] ,['nickName' ,'like' ,$search]])->field(['id'])->select();

                $tmp = array();
                foreach ($users1 as $k => $v)
                {
                    if (!in_array($v['user_id'], $tmp))
                    {
                        array_push($tmp, $v['user_id']);
                    }
                }
                foreach ($users2 as $k => $v)
                {
                    if (!in_array($v['id'], $tmp))
                    {
                        array_push($tmp, $v['id']);
                    }
                }
                $tmp = array_unique($tmp);

                if (isset($filter['mark']) || isset($filter['deal']))
                {
                    $tmp1 = array();
                    $tmp2 = $user_ids;
                    foreach ($tmp as $k => $v)
                    {
                        if (in_array($v, $tmp2))
                        {
                            if(!in_array($v, $tmp1)) array_push($tmp1, $v);
                        }
                    }
                    $user_ids = $tmp1;
                }
                else
                {
                    foreach ($tmp as $k => $v)
                    {
                        if(!in_array($v, $user_ids)) array_push($user_ids, $v);
                    }
                }
            }
//          var_dump($user_ids);die;
            $where[] = ['user.id' ,'in' , $user_ids];

            //获取用户数据
//          $users = pdo_getslice('longbing_card_user', $where, $limit, $count, [], '');
            $result = $this->alias('user')
                ->leftJoin('longbing_card_user_phone phone' ,'user.id = phone.user_id')
                ->where($where);

            $count  = $result->count();
            $users  = $result->page($page_config['page'] ,$page_config['page_count'])
                ->field('user.* ,phone.phone')
                ->order('user.id desc')
                ->select()->toArray();
        }
        else
        {
            $result = $this->alias('user')
                ->leftJoin('longbing_card_user_phone phone' ,'user.id = phone.user_id')
                ->where($where);

            $count  = $result->count();
            $users  = $result->page($page_config['page'] ,$page_config['page_count'])
                ->field('user.* ,phone.phone')
                ->order('user.id desc')
                ->select()->toArray();
        }



//      var_dump(json_encode($users));die;
        foreach ($users as $k => $v)
        {


            $users[$k]['user_name'] =  '';
//          $client_info = pdo_get('longbing_card_client_info', ['user_id' => $v['id']]);
            $client_model = new ClientModel();
            $client_info = $client_model->getClientData([['uniacid' ,'=' ,$uniacid] ,['user_id' ,'=' ,$v['id']]] ,['name']);
            if ($client_info)
            {
                $users[$k]['user_name'] =  $client_info['name'];
            }

            $users[$k]['deal_time'] =  '';
            //获取客户跟进数据
            $collection_model = new CollectionModel();
            $collections = $collection_model->listCollection(['uniacid' => $uniacid ,'uid' =>$v['id'] ,'status' => 1]);
            //获取数据


            $users[$k]['mark'] =  0;
            if(isset($filter['mark']) && in_array($filter['mark'], [1,2,'1','2'])) $users[$k]['mark'] = $filter['mark'];
            if(isset($filter['deal']) && in_array($filter['deal'] ,[2,'2'])) $users[$k]['mark'] = 2;
            $mark_staff = '';

            $mark_model = new MarkModel();
            $list_mark = $mark_model->where(['uniacid' => $uniacid ,'status' => 1 ,'user_id'=>$v['id'],'staff_id'=>$v['last_staff_id']])->value('mark');

            $users[$k]['mark'] = !empty($list_mark)?$list_mark:0;

            foreach($collections as $collection)
            {

                //获取跟进员工数据
                $staff = longbingGetUserInfo($collection['to_uid'] ,$uniacid);


                //判断是否是员工
                if(!empty($staff) && isset($staff['is_staff']) && !empty($staff['is_staff']))
                {
                    if(!empty($mark_staff)) $mark_staff = $mark_staff . ',' ;
                    $mark_staff = $mark_staff . $staff['name'];
                    //获取跟进数据
                    $mark_model = new MarkModel();
                    $mark       = $mark_model->getMarkData(['user_id' => $v['id'] ,'staff_id' => $collection['to_uid'] ,'status' => 1] ,['id' ,'mark']);


                    if(!empty($mark))
                    {
                        if(in_array($mark['mark'], [2,'2']))
                        {
                            $mark_staff = $mark_staff . '(' . lang('traded') . ')';
                        }else{
                            $mark_staff = $mark_staff . '(' . lang('not traded') . ')';
                        }
                        if($mark['mark'] > $users[$k]['mark']) $users[$k]['mark'] = $mark['mark'];
                    }else{
                        $mark_staff = $mark_staff . '(' . lang('not traded') . ')';
                    }
                }
            }
            $users[$k]['mark_staff'] = $mark_staff;

            //  用户成交率
//          $users[$k]['rate'] = rate($v['id'], $uniacid);
            $users[$k]['rate'] = 0;

            if ($v['import'] == 1)
            {
                // $users[$k]['avatarUrl'] = transImagesOne($v ,['avatarUrl'] ,$uniacid);
            }
        }
//die;
        $result = array(
            'data'  => $users,
            'count' => $count
        );
        return $result;
    }


    /**
     * @param $filter
     * @param $page_config
     * @功能说明:客户数据
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-08-07 11:23
     */
    public function listCustomerDataV2($filter)
    {

        $mark_model       = new MarkModel();

        $client_model     = new ClientModel();

        $collection_model = new CollectionModel();

        $uniacid = $filter['uniacid'];
        //设置查询条件
        $where = [

            ['user.uniacid'  ,'=' , $uniacid],

            ['user.is_staff' ,'=' , 0]
        ];

        if (!empty($filter['mark']))
        {

            $dis = [

                'uniacid' => $uniacid,

                'status'  => 1,

            ];

            if($filter['mark'] != 3){

                $dis['mark'] = $filter['mark'];

            }
            //已经跟进
            $user_ids = $mark_model->where($dis)->column('user_id');
            //未跟进
            if ($filter['mark'] == 3)
            {

                $user_ids = $this->where([['uniacid' , '=' ,$uniacid] , ['id' ,'not in' , $user_ids] , ['is_staff' ,'=' , 0]])->field(['id'])->column('id');

            }

            $where[] = ['user.id' ,'in' , $user_ids];

        }
        //昵称搜索
        if (isset($filter['nickName']))
        {

            $where[] = ['user.nickName' ,'like' ,'%'.$filter['nickName'].'%'];

        }
        //判断是否查询电话号码授权
        if(!empty($filter['empower'])&&$filter['empower']==1){

            $where[] = ['phone.phone' ,'<>' ,'null'];
        }

        //判断是否授权头像/登陆信息
        if(!empty($filter['empower'])&&$filter['empower']==2){

            $where[] = ['user.avatarUrl' ,'<>' ,''];

        }

        //判断是否授权头像/登陆信息
        if(!empty($filter['start_time'])&&!empty($filter['end_time'])){

            $start_time = $filter['start_time'];

            $end_time = $filter['end_time'];

            $where[] = ['user.create_time' ,'between' ,"$start_time,$end_time"];

        }

        $usersdata = $this->alias('user')
                ->leftJoin('longbing_card_user_phone phone' ,'user.id = phone.user_id')
                ->where($where)
                ->field('user.* ,phone.phone')
                ->order('user.id desc')
                ->paginate($filter['limit'])
                ->toArray();

        $users = $usersdata['data'];

        if(!empty($users)){

            foreach ($users as $k => $v)
            {

                $users[$k]['user_name'] = $client_model->where([['uniacid' ,'=' ,$uniacid] ,['user_id' ,'=' ,$v['id']]] ,['name'])->value('name');

                $users[$k]['mark']      = !empty($filter['mark'])?$filter['mark']:0;

                $users[$k]['deal_time'] =  '';
                //获取数据
                $mark_staff = '';

                $list_mark = $mark_model->where(['uniacid' => $uniacid ,'status' => 1 ,'user_id'=>$v['id'],'staff_id'=>$v['last_staff_id']])->value('mark');

                $users[$k]['mark'] = !empty($list_mark)?$list_mark:0;

                $users[$k]['update_time'] = $mark_model->where(['uniacid' => $uniacid ,'status' => 1 ,'user_id'=>$v['id'],'mark'=>2])->value('update_time');

                $users[$k]['update_time'] = !empty($users[$k]['update_time'])?$users[$k]['update_time']:'-';

                $collections = $collection_model->listCollection(['uniacid' => $uniacid ,'uid' =>$v['id'] ,'status' => 1]);

                $rate_text = '';

                $remark_name = '';

                foreach($collections as $collection)
                {
                    //获取跟进员工数据
                    $staff = longbingGetUserInfo($collection['to_uid'] ,$uniacid);
                    //判断是否是员工
                    if(!empty($staff) && isset($staff['is_staff']) && !empty($staff['is_staff']))
                    {
                        if(!empty($mark_staff)) $mark_staff = $mark_staff . ',' ;

                        if(empty($staff['name'])){

                            $staff['name'] = $this->where(['id'=>$collection['to_uid']])->value('nickName');
                        }

                        $mark_staff = $mark_staff . $staff['name'];

                        $mark       = $mark_model->getMarkData(['user_id' => $v['id'] ,'staff_id' => $collection['to_uid'] ,'status' => 1] ,['id' ,'mark']);

                        if(!empty($mark))
                        {
                            if(in_array($mark['mark'], [2,'2']))
                            {
                                $mark_staff = $mark_staff . '(' . lang('traded') . ')';
                            }else{
                                $mark_staff = $mark_staff . '(' . lang('In the follow up') . ')';
                            }
                            if($mark['mark'] > $users[$k]['mark']) $users[$k]['mark'] = $mark['mark'];
                        }else{

                            $mark_staff = $mark_staff . '(' . lang('not follow up') . ')';
                        }

                        $rate_text .= $staff['name'].'('.$collection['rate'].'%)';

                    }

                    $dis = [

                        'user_id'  =>  $v['id'],

                        'staff_id' =>  $collection['to_uid'],
                    ];

//                    dump($dis);exit;

                    $staff_name = Db::name('longbing_card_client_info')->where($dis)->value('name');

                    if(!empty($staff_name)||!empty($staff['name'])){

                        $remark_name .= $staff['name'].'('.$staff_name.')';
                    }

                }
                $users[$k]['mark_staff']  = $mark_staff;

                $users[$k]['rate']        = 0;

                $users[$k]['rate_text']   = $rate_text;

                $users[$k]['remark_name'] = $remark_name;

            }
        }

        $usersdata['data'] = $users ;

        return $usersdata;
    }





    //获取客户列表
    public function listCustomer($filter ,$page_config)
    {
//      $filter_data['user.is_staff'] = 0;
//      $filter_data['user.deleted']  = 0;
//      if(isset($filter['uniacid'])) $filter_data['user.uniacid'] = $filter['uniacid'];
//      $result = $this->alias('user')
//                     ->where($filter_data);
//      if(isset($filter['nickName'])) $result = $result->where('user.nickName' ,'like' , '%' . $filter['nickName'] .'%');
//      switch($filter['follow'])
//      {
//          case 0:
//              switch($filter['mark']):
//                  case 0:
//                      
//                      break;
//                  case 1:
//                      
//                      break;
//                  case 2:
//                      
//                      break;
//                  default:
//                      return [];
//                      break;
//              break;
//          case 1:
//              switch($filter['mark']):
//                  case 0:
//                      
//                      break;
//                  case 1:
//                      
//                      break;
//                  case 2:
//                      
//                      break;
//                  default:
//                      return [];
//                      break;
//              break;
//          case 2:
//              switch($filter['mark']):
//                  case 0:
//                      
//                      break;
//                  case 1:
//                      
//                      break;
//                 case 2:
//                      
//                      break;
//                  default:
//                      return [];
//                      break;
//              break;
//          default:
//              return [];
//              
//      }
//
//      if(isset($filter['mark'])) 
//      {
//          switch($filter['mark'])
//          {
//              case 0:
//                  $result = $result->leftJoin('longbing_card_user_mark mark' ,'mark.user_id = user.id');
//                  $result = $result->whereOr([['mark.mark' ,'in' ,[0 ,1] ],['mark.mark' , '=' , null]]);
//                  break;
//              case 1:
//                  $result = $result->Join('longbing_card_user_mark mark' ,'mark.user_id = user.id');
//                  $result = $result->where('mark.mark' ,'=' ,2);
//                  break;
//              case 2:
//                  $result = $result->Join('longbing_card_user_mark mark' ,'mark.user_id = user.id');
//                  $result = $result->where('mark.mark' ,'=' ,2);
//                  break;
//              default:
//                  $result = $result->leftJoin('longbing_card_user_mark mark' ,'mark.user_id = user.id');
//                  break;
//          }
//      }
//      
//      $result = $result->leftjoin('longbing_card_user_phone phone' ,'user.id = phone.user_id');
//      if(isset($filter['is_phone']) && !empty($filter['is_phone'])) $result->where('phone.phone' ,'<>' ,null);
//      $result = $result->order('id' ,'desc')
//                       ->page($page_config['page'] ,$page_config['page_count'])
////                       ->field('user.*,mark.staff_id,mark.mark,phone.phone')
//                       ->field('user.id ,mark.mark')
//                       ->select();
//      if(!empty($result)) $result = $result->toArray();
//      return $result;
    }
    public function getCoustomerCount($filter)
    {
        $filter_data['user.is_staff'] = 0;
        $filter_data['user.deleted']  = 0;
        if(isset($filter['uniacid'])) $filter_data['user.uniacid'] = $filter['uniacid'];
        $result = $this->alias('user')
                       ->where($filter_data);
        if(isset($filter['nickName'])) $result = $result->where('user.nickName' ,'like' , '%' . $filter['nickName'] .'%');
        $result = $result->leftJoin('longbing_card_user_mark mark' ,'mark.user_id = user.id');
        if(isset($filter['mark'])) 
        {
            switch($filter['mark'])
            {
                case 0:
                    $result = $result->whereOr([['mark.mark' ,'in' ,[0]] ,['mark.mark' , '=' , null]]);
                    break;
                case 1:
                    $result = $result->where('mark.mark' ,'=' ,1);
                    break;
                case 2:
                    $result = $result->where('mark.mark' ,'=' ,2);
                    break;
                case 3:
                    $result = $result->whereOr([['mark.mark' ,'in' ,[0,1]] ,['mark.mark' ,'=' ,null]]);
                    break;
                default:
                    break;
            }
        }
        $result = $result->leftjoin('longbing_card_user_phone phone' ,'user.id = phone.user_id');
        if(isset($filter['is_phone']) && !empty($filter['is_phone'])) $result->where('phone.phone' ,'<>' ,null);
        $count = $result->count();
        return $count;
    }

    //获取客户信息
    public function getCustomer($filter)
    {
        $filter_data['user.is_staff'] = 0;
        $filter_data['user.deleted']  = 0;
        if(isset($filter['uniacid'])) $filter_data['user.uniacid'] = $filter['uniacid'];
        if(isset($filter['customer_id'])) $filter_data['user.id'] = $filter['customer_id'];
//        var_dump($filter_data);die;
        $result = $this->alias('user')
                       ->where($filter_data);
//      $result = $result->leftJoin('longbing_card_user_mark mark' ,'mark.user_id = user.id');
        $result = $result->leftjoin('longbing_card_user_phone phone' ,'user.id = phone.user_id');
//        $result = $result->find();
        $result = $result->field('user.* ,phone.phone')
                         ->find();
        if(!empty($result)) 
        {
            $result = $result->toArray();
//          //获取客服信息
//          $collection_model = new CollectionModel();
//          //获取mark信息
//          $mark_model = new MarkModel();
            
        }
        
        return $result;
    }
    //修改客户信息
    public function updateCustomer($filter ,$data)
    {
        $data['update_time'] = time();
        $result = $this->where($filter)->update($data);
        return !empty($result);
    }
    //修改用户信息
    public function updateUser($filter ,$data)
    {
        $data['update_time'] = time();
        $result = $this->where($filter)->update($data);
        return !empty($result);
    }
    //获取总数
    public function getUserCount($filter = [])
    {
        $count = $this->where($filter)->count();
        return $count;
    }
}