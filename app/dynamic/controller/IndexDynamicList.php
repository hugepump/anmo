<?php

namespace app\dynamic\controller;

use app\ApiRest;

use app\dynamic\model\DynamicComment;
use app\dynamic\model\DynamicFollow;
use app\dynamic\model\DynamicList;
use app\dynamic\model\DynamicThumbs;
use app\dynamic\model\DynamicWatchRecord;
use app\industrytype\model\Type;
use app\massage\model\Coach;

use app\massage\model\Goods;

use app\massage\model\MassageConfig;
use app\massage\model\Order;

use app\massage\model\ShieldList;
use longbingcore\wxcore\WxSetting;
use think\App;
use think\facade\Db;
use think\Request;


class IndexDynamicList extends ApiRest
{

    protected $order_model;

    protected $model;

    protected $cap_info;

    protected $list_model;

    protected $commment_model;

    protected $thumbs_model;

    protected $config_model;

    public function __construct(App $app)
    {

        parent::__construct($app);

        $this->model = new Coach();

        $this->order_model = new Order();

        $this->list_model = new DynamicList();

        $this->commment_model = new DynamicComment();

        $this->thumbs_model = new DynamicThumbs();

        $this->config_model = new MassageConfig();
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-01-29 11:00
     * @功能说明:动态列表
     */
    public function dynamicList(){

        $input = $this->_param;

        $dis[] = ['a.status','=',2];

        $dis[] = ['b.status','=',2];

        if(!empty($input['coach_name'])){

            $dis[] = ['b.coach_name','like','%'.$input['coach_name'].'%'];
        }

        if($this->is_app==0&&getConfigSetting($this->_uniacid,'shield_massage')==1){

            $dis[] = ['b.industry_type','<>',1];
        }

        if (!empty($input['coach_id'])) {

            $dis[] = ['a.coach_id', '=', $input['coach_id']];
        }
        //如果登录了就不返回屏蔽技师的动态
        if(!empty($this->getUserId())){

            $shield_model = new ShieldList();

            $coach_id = $shield_model->where(['user_id'=>$this->getUserId()])->column('coach_id');

            $dis[] = ['a.coach_id','not in',$coach_id];
        }

        $lat = !empty($input['lat'])?$input['lat']:0;

        $lng = !empty($input['lng'])?$input['lng']:0;

        $alh = 'ACOS(SIN(('.$lat.' * 3.1415) / 180 ) *SIN((a.lat * 3.1415) / 180 ) +COS(('.$lat.' * 3.1415) / 180 ) * COS((a.lat * 3.1415) / 180 ) *COS(('.$lng.' * 3.1415) / 180 - (a.lng * 3.1415) / 180 ) ) * 6378.137*1000 as distance';

        $data = $this->list_model->coachDataList($dis,$alh);

        if ($data['data']) {

            $dynamic_id = DynamicThumbs::where(['user_id' => $this->getUserId(), 'status' => 1])->column(['dynamic_id']);

            foreach ($data['data'] as &$datum) {

                $datum['is_thumbs'] = in_array($datum['id'], $dynamic_id) ? 1 : 0;
            }
        }

        return $this->success($data);
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-01-29 11:00
     * @功能说明:关注动态列表
     */
    public function followDynamicList(){

        $input = $this->_param;

        $dis[] = ['a.status','=',2];

        $dis[] = ['b.status','=',2];

        $dis[] = ['a.uniacid','=',$this->_uniacid];

        $dis[] = ['c.status','=',1];

        $dis[] = ['a.user_id','=',$this->_user_id];

        if($this->is_app==0&&getConfigSetting($this->_uniacid,'shield_massage')==1){

            $dis[] = ['b.industry_type','<>',1];
        }

        if(!empty($input['coach_name'])){

            $dis[] = ['b.coach_name','like','%'.$input['coach_name'].'%'];

        }
        $shield_model = new ShieldList();

        $coach_id = $shield_model->where(['user_id'=>$this->_user_id])->column('coach_id');

        $dis[] = ['a.coach_id','not in',$coach_id];

        $lat = !empty($input['lat'])?$input['lat']:0;

        $lng = !empty($input['lng'])?$input['lng']:0;

        $alh = 'ACOS(SIN(('.$lat.' * 3.1415) / 180 ) *SIN((a.lat * 3.1415) / 180 ) +COS(('.$lat.' * 3.1415) / 180 ) * COS((a.lat * 3.1415) / 180 ) *COS(('.$lng.' * 3.1415) / 180 - (a.lng * 3.1415) / 180 ) ) * 6378.137*1000 as distance';

        $data = $this->list_model->coachFollowDataList($dis,$alh);
        //清除数字
        $follow_model = new DynamicFollow();

        $record = $follow_model->getFollowDynamicData($this->_user_id);

        if(!empty($record)){

            foreach ($record as $k=>$value){

                $insert[$k] = [

                    'uniacid' => $value['uniacid'],

                    'user_id' => $this->_user_id,

                    'dynamic_id' => $value['id']
                ];
            }

            $record_model = new DynamicWatchRecord();

            $record_model->saveAll($insert);
        }

        return $this->success($data);

    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-01-29 11:00
     * @功能说明:动态列表
     */
    public function dynamicInfo(){

        $input = $this->_param;

        $dis= [

            'id' => $input['id']
        ];

        $lat = !empty($input['lat'])?$input['lat']:0;

        $lng = !empty($input['lng'])?$input['lng']:0;
        //增加浏览量
        $this->list_model->where($dis)->update(['pv'=>Db::Raw('pv+1')]);

        $data = $this->list_model->dataInfo($dis);

        if(empty($data)){

            $this->errorMsg('动态被删除');
        }
        //技师情况
        $data['coach_info'] = $this->model->where(['id'=>$data['coach_id']])->field('industry_type,coach_name,work_img,is_work,work_time,status')->find();

        if(empty($data['coach_info'])){

            $this->errorMsg('技师已下架');
        }


        $type_model = new Type();

        $data['coach_info']['industry_data'] = $type_model->dataInfo(['id' => $data['coach_info']['industry_type']],'employment_years');
        //技师工作状态
        $data['coach_info']['work_status'] = $this->model->getCoachWorkStatus($data['coach_id'],$this->_uniacid);
        //距离
        $data['distance'] = distance_text(getdistances($data['lng'],$data['lat'],$lng,$lat));
        //是否关注
        $follow_model = new DynamicFollow();

        $find = $follow_model->dataInfo(['coach_id'=>$data['coach_id'],'user_id'=>$this->_user_id,'status'=>1]);

        $data['follow_status'] = !empty($find)?1:0;
        //是否点赞
        $find = $this->thumbs_model->dataInfo(['dynamic_id'=>$data['id'],'user_id'=>$this->_user_id,'status'=>1]);

        $data['thumbs_status'] = !empty($find)?1:0;

        $dis= [

            'a.id'     => $input['id'],
        ];
        //评论数量
        $data['comment_num'] = getFriendNum($this->list_model->getCommentNum($dis));
        //点赞数量
        $data['thumbs_num']  = getFriendNum($this->list_model->getThumbsNum($dis));

        $shield_model = new ShieldList();

        $coach_id = $shield_model->where(['user_id'=>$this->_user_id])->column('coach_id');
        //不看他的作品
        $data['is_shield'] = in_array($data['coach_id'],$coach_id)?1:0;

        return $this->success($data);

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-01-29 11:47
     * @功能说明:评论列表
     */
    public function commentList(){

        $input = $this->_param;

        $dis[] = ['a.dynamic_id','=',$input['dynamic_id']];

        $where[] = ['a.status','=',2];

        $id_arr = $this->commment_model->where(['user_id'=>$this->_user_id,'dynamic_id'=>$input['dynamic_id']])->where('status','in',[1,2])->column('id');

        $where[] = ['a.id','in',$id_arr];

        $data = $this->commment_model->getCommentList($dis,10,$where);

        $add_user = $this->list_model->where(['id'=>$input['dynamic_id']])->value('user_id');

        if(!empty($data)){

            foreach ($data['data'] as &$v){
                //是否具有删除权限
                $v['del_auth'] = $add_user==$this->_user_id||$v['user_id']==$this->_user_id?1:0;

            }

        }

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-01-29 15:08
     * @功能说明:发布评论
     */
    public function commentAdd(){

        $input = $this->_input;

        $config = $this->config_model->dataInfo(['uniacid'=>$this->_uniacid]);

        $dynamic = $this->list_model->dataInfo(['id'=>$input['dynamic_id']]);

        if(empty($dynamic)){

            $this->errorMsg('动态已被删除');

        }

        $insert = [

            'uniacid' => $this->_uniacid,

            'user_id' => $this->_user_id,

            'dynamic_id' => $input['dynamic_id'],

            'text' => $input['text'],

            'coach_id' => $dynamic['coach_id'],

            'status' => $config['dynamic_comment_check']

        ];

        $setting = new WxSetting($this->_uniacid);

        $check = $setting->checkKeyWords(trim($input['text']));

        if($check!=true){

            $this->errorMsg('输入文字内容含有敏感违禁词');
        }

        $res = $this->commment_model->dataAdd($insert);

        return $this->success(['status'=>$insert['status']]);
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-01-29 15:08
     * @功能说明:删除评论
     */
    public function commentDel(){

        $input = $this->_input;

        $res = $this->commment_model->dataUpdate(['id'=>$input['id']],['status'=>-1]);

        return $this->success($res);
    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-01-29 11:47
     * @功能说明:关注或者取消关注
     */
    public function followAddOrCancek(){

        $input = $this->_input;

        $input['dynamic_id'] = empty($input['dynamic_id']) ? 0 : $input['dynamic_id'];

        $follow_model = new DynamicFollow();

        $find = $follow_model->dataInfo(['coach_id'=>$input['coach_id'],'user_id'=>$this->_user_id]);

        if(!empty($find)&&$find['status']==1){
            //取消关注
            $res = $follow_model->dataUpdate(['id'=>$find['id']],['status'=>-1]);

        }elseif(!empty($find)){
            //关注
            $res = $follow_model->dataUpdate(['id'=>$find['id']],['status'=>1,'create_time'=>time(),'dynamic_id'=>$input['dynamic_id'],'have_look'=>0]);

        }else{

            $insert = [

                'uniacid' => $this->_uniacid,

                'coach_id'=> $input['coach_id'],

                'user_id' => $this->_user_id,

                'dynamic_id' => $input['dynamic_id'],
            ];

            $res = $follow_model->dataAdd($insert);

        }

        return $this->success($res);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-01-29 11:47
     * @功能说明:点赞或者取消点赞
     */
    public function thumbsAddOrCancek(){

        $input = $this->_input;

        $dynamic = $this->list_model->dataInfo(['id'=>$input['dynamic_id']]);

        if(empty($dynamic)){

            $this->errorMsg('动态已被删除');

        }

        $find = $this->thumbs_model->dataInfo(['dynamic_id'=>$input['dynamic_id'],'user_id'=>$this->_user_id]);

        if(!empty($find)&&$find['status']==1){
            //取消点赞
            $res = $this->thumbs_model->dataUpdate(['id'=>$find['id']],['status'=>-1]);

        }elseif(!empty($find)){
            //点赞
            $res = $this->thumbs_model->dataUpdate(['id'=>$find['id']],['status'=>1,'create_time'=>time(),'have_look'=>0]);

        }else{
            //点赞
            $insert = [

                'uniacid' => $this->_uniacid,

                'user_id' => $this->_user_id,

                'dynamic_id' => $input['dynamic_id'],

                'coach_id' => $dynamic['coach_id'],
            ];

            $res = $this->thumbs_model->dataAdd($insert);

        }

        return $this->success($res);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-01-29 16:50
     * @功能说明:获取关注技师的最新动态数量
     */
    public function getFollowData(){

        if(empty($this->getUserId())){

            $arr['num'] = $arr['coach_status']=0;

            return $this->success($arr);

        }
        $follow_model = new DynamicFollow();

        $dis = [];

        if($this->is_app==0&&getConfigSetting($this->_uniacid,'shield_massage')==1){

            $dis[] = ['d.industry_type','<>',1];
        }

        $arr['num'] = $follow_model->getFollowDynamicNum($this->getUserId(),$dis);

        $cap_dis[] = ['user_id','=',$this->getUserId()];

        $cap_dis[] = ['status','=',2];

        $coach_model = new Coach();
        //查看是否是团长
        $cap_info = $coach_model->where($cap_dis)->find();

        $arr['coach_status'] = !empty($cap_info)?1:0;

        return $this->success($arr);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-08 18:00
     * @功能说明:用户关注技师列表
     */
    public function followCoachList(){

        $input = $this->_param;

        $follow_model = new DynamicFollow();

        $shield_model = new ShieldList();

        $coach_id = $shield_model->where(['user_id'=>$this->getUserId()])->where('type','in',[2,3])->column('coach_id');

        $dis[] = ['a.coach_id','not in',$coach_id];

        $dis[] = ['a.status','=',1];

        $dis[] = ['b.status','=',2];

        $dis[] = ['a.user_id','=',$this->getUserId()];

        $lat = !empty($input['lat'])?$input['lat']:0;

        $lng = !empty($input['lng'])?$input['lng']:0;

        $alh = 'ACOS(SIN(('.$lat.' * 3.1415) / 180 ) *SIN((b.lat * 3.1415) / 180 ) +COS(('.$lat.' * 3.1415) / 180 ) * COS((b.lat * 3.1415) / 180 ) *COS(('.$lng.' * 3.1415) / 180 - (b.lng * 3.1415) / 180 ) ) * 6378.137*1000 as distance';

        $data = $follow_model->followCoachList($dis,$alh);

        if(!empty($data['data'])){

            $comm_model = new Order();

            foreach ($data['data'] as &$v){

                $num = $comm_model->where(['coach_id' => $v['coach_id'], 'pay_type' => 7])->count();
                //接单数量
                $v['order_num'] += $num;
                //距离
                $v['distance'] = distance_text($v['distance']);
                //技师的粉丝数
                $v['fans_num'] = $follow_model->where(['status'=>1,'coach_id'=>$v['coach_id']])->count();

                $v['fans_num'] = getFriendNum($v['fans_num']);

                $v['order_num'] = getFriendNum($v['order_num']);
            }

        }

        return $this->success($data);

    }
















}
