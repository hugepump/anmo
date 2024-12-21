<?php
namespace app\massage\model;

use app\BaseModel;
use app\industrytype\model\Type;
use longbingcore\permissions\AdminMenu;
use think\facade\Db;

class CoachCollect extends BaseModel
{
    //定义表名
    protected $name = 'massage_service_coach_collect';






    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-15 14:37
     * @功能说明:后台列表
     */
    public function adminDataList($dis,$mapor,$page=10){

        $data = $this->alias('a')
                ->join('shequshop_school_user_list b','a.user_id = b.id')
                ->where($dis)
                ->where(function ($query) use ($mapor){
                    $query->whereOr($mapor);
                })
                ->field('a.*,b.nickName,b.avatarUrl')
                ->group('a.id')
                ->order('a.id desc')
                ->paginate($page)
                ->toArray();

        return $data;

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:04
     * @功能说明:添加
     */
    public function dataAdd($data){

        $data['create_time'] = time();

        $res = $this->insert($data);

        return $res;

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:05
     * @功能说明:编辑
     */
    public function dataUpdate($dis,$data){

        $res = $this->where($dis)->update($data);

        return $res;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:06
     * @功能说明:列表
     */
    public function dataList($dis,$page=10,$mapor){

        $data = $this->where($dis)->where(function ($query) use ($mapor){
            $query->whereOr($mapor);
        })->order('distance asc,id desc')->paginate($page)->toArray();

        return $data;

    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:43
     * @功能说明:
     */
    public function dataInfo($dis,$file='*'){

        $data = $this->where($dis)->field($file)->find();

        return !empty($data)?$data->toArray():[];

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-07 22:41
     * @功能说明:教练收藏列表
     */

    public function coachCollectListTypeOne($input,$user_id,$uniacid){

        $lat = !empty($input['lat'])?$input['lat']:0;

        $lng = !empty($input['lng'])?$input['lng']:0;

        $dis[] = ['a.uniacid','=',$uniacid];

        $dis[] = ['a.status','=',2];

        $dis[] = ['b.user_id','=',$user_id];

        $dis[] = ['a.is_work','=',1];

        if (!empty($input['industry_type'])) {

            $dis[] = ['a.industry_type', '=', $input['industry_type']];
        }

        $shield_model = new ShieldList();

        $coach_model  = new Coach();

        $coach_id = $shield_model->where(['user_id'=>$user_id])->where('type','in',[2,3])->column('coach_id');

        $dis[] = ['a.id','not in',$coach_id];
        //服务中
        $working_coach = $coach_model->getWorkingCoach($uniacid);
        //当前时间不可预约
        $cannot = CoachTimeList::getCannotCoach($uniacid);

        $cannot = array_diff($cannot,$working_coach);

        $alh = 'ACOS(SIN(('.$lat.' * 3.1415) / 180 ) *SIN((a.lat * 3.1415) / 180 ) +COS(('.$lat.' * 3.1415) / 180 ) * COS((a.lat * 3.1415) / 180 ) *COS(('.$lng.' * 3.1415) / 180 - (a.lng * 3.1415) / 180 ) ) * 6378.137*1000 as distance';

        $data  = $coach_model->coachCollectList($dis,$alh);

        $auth = AdminMenu::getAuthList((int)$uniacid,['recommend','coachcredit']);

        if(!empty($data['data'])){

            $config_model = new Config();

            $service_model= new Service();

            $record_model = new CreditRecord();

            $icon_model   = new CoachIcon();

            $config= $config_model->dataInfo(['uniacid'=>$uniacid]);

            $coach_icon_type = getConfigSetting($uniacid,'coach_icon_type');
            //销冠
            $top   = $service_model->getSaleTopOne($uniacid,0,$coach_icon_type);
            //销售单量前5
            $five  = $service_model->getSaleTopFive($uniacid,$top,0,$coach_icon_type);
            //最近七天注册
            $seven = $service_model->getSaleTopSeven($uniacid,0,$coach_icon_type);

            $type_model = new Type();

            $type = $type_model->dataSelect(['uniacid' => $uniacid]);

            $station_model = new StationIcon();

            $station_icon = $station_model->where(['status' => 1])->column('title', 'id');

            $personality_icon_model = new IconCoach();

            $personality_icon = $personality_icon_model->where(['status' => 1])->column('title', 'id');

            foreach ($data['data'] as &$v) {

                if($auth['coachcredit']==true){

                    $v['credit_value'] = $record_model->getSingleCoachValue($uniacid,$v['id']);
                }

                $v['near_time'] = $coach_model->getCoachEarliestTime($v['id'],$config);

                if (in_array($v['id'],$working_coach)){

                    $text_type = 2;

                }elseif (empty($v['near_time'])){

                    $text_type = 4;

                }elseif (!in_array($v['id'],$cannot)){

                    $text_type = 1;

                }else{

                    $text_type = 3;
                }

                $v['text_type'] = $text_type;

                if(in_array($v['id'],$top)){

                    $v['coach_type_status'] = 1;

                }elseif (in_array($v['id'],$five)){

                    $v['coach_type_status'] = 2;

                }elseif (in_array($v['id'],$seven)){

                    $v['coach_type_status'] = 3;

                }elseif ($v['recommend_icon']==1){

                    $v['coach_type_status'] = 4;

                }else{

                    $v['coach_type_status'] = 0;
                }

                if($coach_icon_type==1){

                    $v['coach_icon'] = $icon_model->where(['id'=>$v['coach_icon']])->value('icon');

                }else{

                    $v['coach_icon'] = '';
                }

                $v['year'] = !empty($v['birthday']) ? floor((time() - $v['birthday']) / (86400 * 365)) : 0;

                $v['industry_data'] = empty($v['industry_type']) ? [] : $type[$v['industry_type']];
                //岗位标签
                $v['station_icon_name'] = isset($station_icon[$v['station_icon']]) ? $station_icon[$v['station_icon']] : '';
                //个性标签
                $v['personality_icon'] = isset($personality_icon[$v['personality_icon']]) ? $personality_icon[$v['personality_icon']] : '';;
            }

        }

        return $data;

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-07 22:41
     * @功能说明:教练收藏列表
     */

    public function coachCollectListTypeTow($input,$user_id,$uniacid){

        $lat = !empty($input['lat'])?$input['lat']:0;

        $lng = !empty($input['lng'])?$input['lng']:0;

        $dis[] = ['a.uniacid','=',$uniacid];

        $dis[] = ['a.status','=',2];

        $dis[] = ['b.user_id','=',$user_id];

        if (!empty($input['industry_type'])) {

            $dis[] = ['a.industry_type', '=', $input['industry_type']];
        }

        $shield_model = new ShieldList();

        $coach_id = $shield_model->where(['user_id'=>$user_id])->where('type','in',[2,3])->column('coach_id');

        $dis[] = ['a.id','not in',$coach_id];

        $coach_model = new Coach();

        $coach_model->setIndexTopCoach($uniacid);

        $alh = 'ACOS(SIN(('.$lat.' * 3.1415) / 180 ) *SIN((a.lat * 3.1415) / 180 ) +COS(('.$lat.' * 3.1415) / 180 ) * COS((a.lat * 3.1415) / 180 ) *COS(('.$lng.' * 3.1415) / 180 - (a.lng * 3.1415) / 180 ) ) * 6378.137*1000 as distance';

        $data  = $coach_model->typeCoachCollectList($dis,$alh);

        if(!empty($data['data'])){

            $config_model = new Config();

            $service_model= new Service();

            $record_model = new CreditRecord();

            $icon_model   = new CoachIcon();

            $config= $config_model->dataInfo(['uniacid'=>$uniacid]);

            $coach_icon_type = getConfigSetting($uniacid,'coach_icon_type');
            //销冠
            $top   = $service_model->getSaleTopOne($uniacid,0,$coach_icon_type);
            //销售单量前5
            $five  = $service_model->getSaleTopFive($uniacid,$top,0,$coach_icon_type);
            //最近七天注册
            $seven = $service_model->getSaleTopSeven($uniacid,0,$coach_icon_type);

            $auth = AdminMenu::getAuthList((int)$uniacid,['coachcredit']);

            $type_model = new Type();

            $type = $type_model->dataSelect(['uniacid' => $uniacid]);

            $station_model = new StationIcon();

            $station_icon = $station_model->where(['status' => 1])->column('title', 'id');

            $personality_icon_model = new IconCoach();

            $personality_icon = $personality_icon_model->where(['status' => 1])->column('title', 'id');

            foreach ($data['data'] as &$v) {

                if($auth['coachcredit']==true){

                    $v['credit_value'] = $record_model->getSingleCoachValue($uniacid,$v['id']);
                }

                $v['near_time']  = $coach_model->getCoachEarliestTime($v['id'],$config);

                if ($v['is_work']==0||empty($v['near_time'])){

                    $text_type = 4;

                }elseif ($v['index_top']==1){

                    $text_type = 1;

                }else{

                    $text_type = 3;
                }

                $v['text_type'] = $text_type;

                if(in_array($v['id'],$top)){

                    $v['coach_type_status'] = 1;

                }elseif (in_array($v['id'],$five)){

                    $v['coach_type_status'] = 2;

                }elseif (in_array($v['id'],$seven)){

                    $v['coach_type_status'] = 3;

                }elseif ($v['recommend_icon']==1){

                    $v['coach_type_status'] = 4;

                }else{

                    $v['coach_type_status'] = 0;
                }

                if($coach_icon_type==1){

                    $v['coach_icon'] = $icon_model->where(['id'=>$v['coach_icon']])->value('icon');

                }else{

                    $v['coach_icon'] = '';
                }
                $v['year'] = !empty($v['birthday']) ? floor((time() - $v['birthday']) / (86400 * 365)) : 0;

                $v['industry_data'] = empty($v['industry_type']) ? [] : $type[$v['industry_type']];
                //岗位标签
                $v['station_icon_name'] = isset($station_icon[$v['station_icon']]) ? $station_icon[$v['station_icon']] : '';
                //个性标签
                $v['personality_icon'] = isset($personality_icon[$v['personality_icon']]) ? $personality_icon[$v['personality_icon']] : '';;
            }

        }

        return $data;

    }




}