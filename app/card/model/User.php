<?php
namespace app\card\model;

use app\BaseModel;
use longbingcore\wxcore\Excel;
use think\Model;



class User extends BaseModel
{
    //定义表名
    protected $name = 'longbing_card_user';


    protected static function init ()
    {
        //TODO:初始化内容
    }
    
    public function searchNickNameAttr($query, $value, $data)
    {
        $query->where('nickName','like', '%' . $value . '%');
    }
    
    public function createUser($data)
    {
        $data['create_time'] = time();
        $result = $this->save($data);
        return !empty($result);
    }
    
    public function updateUser($filter ,$data)
    {
        $data['update_time'] = time();
        $result = $this->where($filter)->update($data);
        return !empty($result);
    }
    
    public function getUser($filter)
    {
        $result = $this->where($filter)->find();
        if(!empty($result)) $result = $result->toArray();
        return $result;
    }
    public function listUser($filter)
    {
        
    }


    /**
     * @param $where
     * @功能说明:名片导出
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-04-14 14:41
     */
    public function cardExcel($where,$mapor,$type=0,$start_time='',$end_time=''){

        $data = User::alias( 'a' )
                ->join( 'longbing_card_user_info b', 'b.fans_id = a.id' ,'LEFT')
                ->join( 'longbing_card_company c', 'b.company_id = c.id', 'LEFT' )
                ->join( 'longbing_card_company d', 'c.top_id = d.id', 'LEFT' )
                ->where( $where )
                ->where(function ($query) use ($mapor){
                    $query->whereOr($mapor);
                })
                ->field( [ 'b.id as card_id', 'b.name', 'b.avatar', 'b.job_id', 'b.company_id', 'b.phone',
                        'b.create_time', 'a.nickName', 'a.avatarUrl', 'a.is_staff', 'a.is_boss',
                        'c.name as company_name', 'd.name as top_company_name', 'b.is_default', 'a.id' ,'a.import','a.uniacid']
                )
                ->group('a.id')
                ->order( [ 'a.is_boss' => 'desc', 'a.is_staff' => 'desc', 'a.update_time' => 'desc', 'a.id' => 'desc' ] )
                ->select()
                ->toArray();


        if(!empty($data)){

            foreach ($data as $k=>$v){

                if(empty($v['top_company_name'])){

                    $data[$k]['top_company_name'] = $v['company_name'];

                    $data[$k]['company_name']     = '未设置部门';
                }
                $data[$k]['name'] = !empty($v['name'])?$v['name']:$v['nickName'];
                //累计客户数量
                $data[$k]['all_customer']   = $this->customerCount($v['id'],$v['uniacid'],0,$end_time);
               //新增客户
                $data[$k]['new_customer']   = $this->customerCount($v['id'],$v['uniacid'],$start_time,$end_time);
                //累计线索
                $data[$k]['all_collection'] = $this->collectionCount($v['id'],$v['uniacid'],0,$end_time);
                //新增线索
                $data[$k]['new_collection'] = $this->collectionCount($v['id'],$v['uniacid'],$start_time,$end_time);
                //总浏览量
                $data[$k]['all_visit']      = $this->visitCount($v['id'],$v['uniacid'],0,$end_time);
                //新增浏览量
                $data[$k]['new_visit']      = $this->visitCount($v['id'],$v['uniacid'],$start_time,$end_time);
                //累计转发
                $data[$k]['all_zf']         = $this->zfCount($v['id'],$v['uniacid'],0,$end_time);
                //新增转发
                $data[$k]['new_zf']         = $this->zfCount($v['id'],$v['uniacid'],$start_time,$end_time);
                //累计被保存
                $data[$k]['all_save']       = $this->saveCount($v['id'],$v['uniacid'],0,$end_time);
                //新增保存
                $data[$k]['new_save']       = $this->saveCount($v['id'],$v['uniacid'],$start_time,$end_time);
                //累计点赞
                $data[$k]['all_dz']         = $this->dzCount($v['id'],$v['uniacid'],0,$end_time);
                //新增点赞
                $data[$k]['new_dz']         = $this->dzCount($v['id'],$v['uniacid'],$start_time,$end_time);
            }
        }

        $header=[
            '序号',
            '分公司',
            '部门',
            '员工姓名',
            '客户数',
            '',
            '累计线索',
            '',
            '累计浏览量',
            '',
            '累计被转发',
            '',
            '累计被保存',
            '',
            '累计被点赞',
            '',
        ];

        if($type==1){
            $header_one=[
                '',
                '',
                '',
                '',
                '新增',
                '总数',
                '新增',
                '总数',
                '新增',
                '总数',
                '新增',
                '总数',
                '新增',
                '总数',
                '新增',
                '总数',
            ];
        }else{

            $header_one=[
                '',
                '',
                '',
                '',
                '累计新增',
                '总数',
                '累计新增',
                '总数',
                '累计新增',
                '总数',
                '累计新增',
                '总数',
                '累计新增',
                '总数',
                '累计新增',
                '总数',
            ];
        }
        $new_data = [];

        $new_data[] = $header_one;

        $data = array_values($data);

        foreach ($data as $k=>$v){

            $info   = array();

            $info[] = $k+1;

            $info[] = $v['top_company_name'];

            $info[] = $v['company_name'];

            $info[] = $v['name'];

            $info[] = $v['new_customer'];

            $info[] = $v['all_customer'];

            $info[] = $v['new_collection'];

            $info[] = $v['all_collection'];

            $info[] = $v['new_visit'];

            $info[] = $v['all_visit'];

            $info[] = $v['new_zf'];

            $info[] = $v['all_zf'];

            $info[] = $v['new_save'];

            $info[] = $v['all_save'];

            $info[] = $v['new_dz'];

            $info[] = $v['all_dz'];

            $new_data[] = $info;
        }

        $excel = new Excel();

        $name  = '员工列表';

        if($type==1){

            $name  = date('Y-m-d',$start_time).'——'.'员工列表';
        }

        if($type==0&&!empty($start_time)&&!empty($end_time)){

            $name = date('Y-m-d',$start_time).'——'.date('Y-m-d',$end_time).'-'.'员工列表';
        }


//        dump($name);exit;
        $fileName=$excel->excelExport($name,$header,$new_data,1);

        return $data;

    }


    /**
     * @param $user_id
     * @param $start_time
     * @param $end_time
     * @功能说明:客户数量
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-04-14 16:14
     */
    public function customerCount($user_id,$uniacid,$start_time='',$end_time=''){
        //线索模型
        $collect    = new Collection();

        $whez[] =[

            ['a.uid','<>',$user_id],

            ['intention','=',1],

            ['a.to_uid','=',$user_id],

            ['a.uniacid','=',$uniacid]
        ];

        //如果选了时间
        if(!empty($start_time)||!empty($end_time)){

            $whez[] = ['a.create_time','between',"$start_time,$end_time"];
        }

        $new_customer = $collect->todayUid($whez);

        return $new_customer;

    }

    /**
     * @param $user_id
     * @param $start_time
     * @param $end_time
     * @功能说明:线索数量
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-04-14 16:14
     */
    public function collectionCount($user_id,$uniacid,$start_time='',$end_time=''){
        //线索模型
        $collect    = new Collection();


        $whes[] = [

            ['a.uid','<>',$user_id],

            ['a.to_uid','=',$user_id],

            ['a.uniacid','=',$uniacid],

//            ['intention','=',0],
        ];

        //如果选了时间
        if(!empty($start_time)||!empty($end_time)){

            $whes[] = ['a.create_time','between',"$start_time,$end_time"];
        }

        $data = $collect->todayUid($whes);

        return $data;

    }

    /**
     * @param $user_id
     * @param $start_time
     * @param $end_time
     * @功能说明:线索数量
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-04-14 16:14
     */
    public function visitCount($user_id,$uniacid,$start_time='',$end_time=''){

        //雷达模型
        $card_count = new CardCount();

        $wheres[] = [
            ['to_uid','=',$user_id],

            ['sign','=','praise'],

            ['type','=',2],

            ['uniacid','=',$uniacid]
        ];

        //如果选了时间
        if(!empty($start_time)||!empty($end_time)){

            $wheres[] = ['create_time','between',"$start_time,$end_time"];
        }
        //新增浏览量
        $data = $card_count->getCount($wheres);

        return $data;

    }

    /**
     * @param $user_id
     * @param $start_time
     * @param $end_time
     * @功能说明:转发数量
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-04-14 16:14
     */
    public function zfCount($user_id,$uniacid,$start_time='',$end_time=''){

        //雷达模型
        $card_count = new CardCount();

        $where4[] = [

            ['to_uid','=',$user_id],

            ['type','=',4],

            ['sign','=','praise'],

            ['uniacid','=',$uniacid]
        ];

        //如果选了时间
        if(!empty($start_time)||!empty($end_time)){

            $where4[] = ['create_time','between',"$start_time,$end_time"];
        }
        //新增浏览量
        $data = $card_count->getCount($where4);

        return $data;

    }

    /**
     * @param $user_id
     * @param $start_time
     * @param $end_time
     * @功能说明:保存数量
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-04-14 16:14
     */
    public function saveCount($user_id,$uniacid,$start_time='',$end_time=''){
        //雷达模型
        $card_count = new CardCount();

        $where6[] = [

            ['to_uid','=',$user_id],

            ['type','=',1],

            ['sign','=','copy'],

            ['uniacid','=',$uniacid]
        ];
        //如果选了时间
        if(!empty($start_time)||!empty($end_time)){

            $where6[] = ['create_time','between',"$start_time,$end_time"];
        }
        //保存
        $data = $card_count->getCount($where6);

        return $data;

    }

    /**
     * @param $user_id
     * @param $start_time
     * @param $end_time
     * @功能说明:点赞数量
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-04-14 16:14
     */
    public function dzCount($user_id,$uniacid,$start_time='',$end_time=''){
        //雷达模型
        $card_count = new CardCount();

        $where5[] = [

            ['to_uid','=',$user_id],

            ['type','=',3],

            ['sign','=','praise'],

            ['uniacid','=',$uniacid]
        ];
        //如果选了时间
        if(!empty($start_time)&&!empty($end_time)){

            $where5[] = ['create_time','between',"$start_time,$end_time"];
        }
        //点赞
        $data = $card_count->getCount($where5);

        return $data;

    }

}