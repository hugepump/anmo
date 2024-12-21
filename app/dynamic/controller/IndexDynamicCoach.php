<?php

namespace app\dynamic\controller;

use app\ApiRest;

use app\dynamic\model\DynamicComment;
use app\dynamic\model\DynamicFollow;
use app\dynamic\model\DynamicList;
use app\dynamic\model\DynamicThumbs;
use app\industrytype\model\Type;
use app\massage\model\Coach;

use app\massage\model\MassageConfig;
use app\massage\model\Order;

use longbingcore\wxcore\WxSetting;
use think\App;
use think\facade\Db;
use think\Request;


class IndexDynamicCoach extends ApiRest
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

        if(!empty($this->_user['coach_account_login'])){

            $cap_dis[] = ['id', '=', $this->_user['coach_id']];

        }else{

            $cap_dis[] = ['user_id', '=', $this->getUserId()];
        }

        $cap_dis[] = ['status', 'in', [2, 3]];

        $this->cap_info = $this->model->dataInfo($cap_dis);

        if (empty($this->cap_info)) {

            $this->errorMsg('只有技师才能发布');
        }

    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-08 11:39
     * @功能说明:添加动态
     */
    public function dynamicAdd()
    {
        $input = $this->_input;

        $config = $this->config_model->dataInfo(['uniacid'=>$this->_uniacid]);

        $insert = [

            'uniacid'  => $this->_uniacid,

            'title'    => $input['title'],

            'cover'    => $input['cover'],

            'text'     => $input['text'],

            'lng'      => $input['lng'],

            'lat'      => $input['lat'],

            'type'     => $input['type'],

            'address'  => $input['address'],

            'user_id'  => $this->_user_id,

            'coach_id' => $this->cap_info['id'],

            'imgs'     => implode(',',$input['imgs']),
            //审核方式
            'status'   => $config['dynamic_check'],

            'check_time' => $config['dynamic_check']==2?time():0,

        ];

        $setting = new WxSetting($this->_uniacid);

        $check_text = $setting->checkKeyWords(trim($input['text']));

        $check_title = $setting->checkKeyWords(trim($input['title']));

        if($check_text!=true||$check_title!=true){

            $this->errorMsg('输入文字内容含有敏感违禁词');
        }

        $res = $this->list_model->dataAdd($insert);

        $arr['status'] = $insert['status'];

        return $this->success($arr);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-01-29 15:08
     * @功能说明:删除评论
     */
    public function dynamicDel(){

        $input = $this->_input;

        $res = $this->list_model->dataUpdate(['id'=>$input['id']],['status'=>-1]);

        return $this->success($res);
    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-01-29 10:45
     * @功能说明:编辑动态
     */
    public function dynamicUpdate(){

        $input = $this->_input;

        $dis = [

            'id' => $input['id']
        ];

        $data = $this->list_model->dataInfo($dis);

        if(empty($data)||$data['status']==-1){

            $this->errorMsg('该动态已被删除');
        }

        $config = $this->config_model->dataInfo(['uniacid'=>$this->_uniacid]);
        //审核方式
        $input['status'] = $config['dynamic_check'];

        if(isset($input['imgs'])){

            $input['imgs'] = !empty($input['imgs'])?implode(',',$input['imgs']):'';
        }

        if(isset($input['text'])&&isset($input['title'])){

            $setting = new WxSetting($this->_uniacid);

            $check_text = $setting->checkKeyWords(trim($input['text']));

            $check_title = $setting->checkKeyWords(trim($input['title']));

            if($check_text!=true||$check_title!=true){

                $this->errorMsg('输入文字内容含有敏感违禁词');
            }

        }

        $this->list_model->dataUpdate($dis,$input);

        $arr['status'] = $input['status'];

        return $this->success($arr);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-01-29 11:00
     * @功能说明:动态列表
     */
    public function dynamicList(){

        $input = $this->_param;

        if(!empty($input['status'])){

            $dis[] = ['a.status','=',$input['status']];
        }else{

            $dis[] = ['a.status','>',-1];
        }

        $dis[] = ['a.coach_id','=',$this->cap_info['id']];

        $lat = !empty($input['lat'])?$input['lat']:0;

        $lng = !empty($input['lng'])?$input['lng']:0;

        $alh = 'ACOS(SIN(('.$lat.' * 3.1415) / 180 ) *SIN((a.lat * 3.1415) / 180 ) +COS(('.$lat.' * 3.1415) / 180 ) * COS((a.lat * 3.1415) / 180 ) *COS(('.$lng.' * 3.1415) / 180 - (a.lng * 3.1415) / 180 ) ) * 6378.137*1000 as distance';

        $data = $this->list_model->coachDataList($dis,$alh);

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
        $data['coach_info'] = $this->model->where(['id'=>$data['coach_id']])->field('industry_type,coach_name,work_img,is_work,work_time')->find();

        if(empty($data['coach_info'])){

            $this->errorMsg('技师已下架');
        }
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

        $type_model = new Type();

        $data['coach_info']['industry_data'] = $type_model->dataInfo(['id' => $data['coach_info']['industry_type']],'employment_years');

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-01-29 11:00
     * @功能说明:动态点赞关注
     */
    public function dynamicData(){

        $input = $this->_param;

        $dis[] = ['a.coach_id','=',$this->cap_info['id']];

        $dis[] = ['a.status','>',-1];

        $dis[] = ['b.have_look','=',0];
        //评论数量
        $data['comment_num'] = $this->list_model->getCommentNum($dis);
        //点赞数量
        $data['thumbs_num']  = $this->list_model->getThumbsNum($dis);

        $follow_model = new DynamicFollow();
        //关注数量
        $data['follow_num']  = $follow_model->where(['coach_id'=>$this->cap_info['id'],'have_look'=>0,'status'=>1])->count();

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-01-29 11:47
     * @功能说明:点赞列表
     */
    public function thumbsList(){

        $input = $this->_param;

        $this->thumbs_model->dataUpdate(['coach_id'=>$this->cap_info['id']],['have_look'=>1]);

        $dis[] = ['a.coach_id','=',$this->cap_info['id']];

        $dis[] = ['a.status','=',1];

        $dis[] = ['c.status','>',-1];

        $data = $this->thumbs_model->getThumbsList($dis);

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-01-29 11:47
     * @功能说明:评论列表510321199507146914
     */
    public function commentList(){

        $input = $this->_param;

        $this->commment_model->dataUpdate(['coach_id'=>$this->cap_info['id']],['have_look'=>1]);

        $dis[] = ['a.coach_id','=',$this->cap_info['id']];

        $dis[] = ['c.status','>',-1];

        $dis[] = ['a.status','in',[2]];

        $data = $this->commment_model->getCommentList($dis);

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-01-29 11:47
     * @功能说明:关注列表
     */
    public function followList(){

        $follow_model = new DynamicFollow();

        $follow_model->dataUpdate(['coach_id'=>$this->cap_info['id']],['have_look'=>1]);

        $dis[] = ['a.coach_id','=',$this->cap_info['id']];

        $dis[] = ['a.status','=',1];

        $data = $follow_model->getFollowList($dis,15);

        return $this->success($data);

    }














}
