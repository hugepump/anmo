<?php
namespace app\store\controller;
use app\AdminRest;
use app\ApiRest;
use app\card\model\Config;
use app\massage\model\ActionLog;
use app\massage\model\Admin;
use app\massage\model\AdminRole;
use app\massage\model\CateList;
use app\massage\model\Coach;
use app\massage\model\Comment;
use app\massage\model\Service;
use app\massage\model\ServicePositionConnect;
use app\node\model\RoleAdmin;
use app\node\model\RoleList;
use app\node\model\RoleNode;
use app\package\info\PermissionPackage;
use app\store\model\StoreCate;
use app\store\model\StoreList;
use app\store\model\StorePackage;
use think\App;



class IndexStore extends ApiRest
{

    public function __construct(App $app) {

        parent::__construct($app);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-07-24 11:14
     * @功能说明:分类列表
     */
    public function storeCateList(){

        $input = $this->_param;

        $dis = [

            'status' => 1,

            'uniacid' => $this->_uniacid
        ];

        $cate_model = new StoreCate();

        $data = $cate_model->where($dis)->order('top desc,id desc')->field('id,title,cover')->select()->toArray();

        return $this->success($data);
    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-23 16:25
     * @功能说明:门店列表
     */
    public function storeList(){

        $input = $this->_param;

        $dis[] = ['a.uniacid','=',$this->_uniacid];

        $dis[] = ['a.status','=',1];

        $dis[] = ['b.status','=',1];

        $dis[] = ['b.store_auth','=',1];

        if(!empty($input['title'])){

            $dis[] = ['a.title','like','%'.$input['title'].'%'];
        }

        if(!empty($input['cate_id'])){

            $dis[] = ['c.cate_id','=',$input['cate_id']];
        }

        $lat = !empty($input['lat'])?$input['lat']:0;

        $lng = !empty($input['lng'])?$input['lng']:0;

        $alh = 'ACOS(SIN(('.$lat.' * 3.1415) / 180 ) *SIN((a.lat * 3.1415) / 180 ) +COS(('.$lat.' * 3.1415) / 180 ) * COS((a.lat * 3.1415) / 180 ) *COS(('.$lng.' * 3.1415) / 180 - (a.lng * 3.1415) / 180 ) ) * 6378.137*1000 as distance';

        $store_model = new StoreList();

        $data = $store_model->indexDataListV2($dis,$alh);

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $v['work_status'] = $store_model->workStatus($v);

                $v['distance'] = distance_text($v['distance']);

            }
        }

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-29 17:43
     * @功能说明:门店详情
     */
    public function storeInfo(){

        $input = $this->_param;

        $store_model = new StoreList();

        $service_model = new Service();

        $coach_model   = new Coach();

        $dis = [

            'id' => $input['id']
        ];

        $data['info'] = $store_model->dataInfo($dis);

        $where = $where1 = [];

        if($this->is_app==0&&getConfigSetting($this->_uniacid,'shield_massage')==1){

            $where[] = ['a.industry_type','<>',1];

            $where1[] = ['industry_type','<>',1];
        }
        //获取门店关联的服务
        $data['service_list'] = $service_model->getStoreService($input['id'],$where);
        //屏蔽技师
        $shield_coach = $coach_model->getShieldCoach($this->getUserId());

        $store_coach_id = $store_model->getStoreCoachId($input['id']);

        $coach_format = getConfigSetting($this->_uniacid,'coach_format');

        if(in_array($coach_format,[1,3])){
            //获取门店关联的技师
            $data['coach_list'] = $coach_model->where($where1)->where(['status'=>2,'auth_status'=>2,'is_work'=>1])->where('id','in',$store_coach_id)->where('id','not in',$shield_coach)->field('id,coach_name,work_img,city_id')->order('id desc')->limit(10)->select()->toArray();
        }else{
            //获取门店关联的技师
            $data['coach_list'] = $coach_model->where($where1)->where(['status'=>2,'auth_status'=>2])->where('id','in',$store_coach_id)->where('id','not in',$shield_coach)->field('id,coach_name,work_img,city_id')->order('id desc')->limit(10)->select()->toArray();
        }

        $member_service = new \app\member\model\Service();

        $user_member = $member_service->getUserMember($this->_uniacid,$this->getUserId());

        if(!empty($data['service_list'])){

            foreach ($data['service_list'] as $k=>&$v){

                $v['member_service'] = $user_member['member_auth']==1?$v['member_service']:0;

                $v['member_info']    = $member_service->getServiceMember($v['id'],$user_member['member_level'],$v['member_service']);
            }
        }
        //会员价
        $data['service_list'] = giveMemberPrice($this->_uniacid,$data['service_list']);

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-29 17:54
     * @功能说明:门店管理的服务列表
     */
    public function storeServiceList(){

        $input = $this->_param;

        $service_model = new Service();

        $position_model = new ServicePositionConnect();

        $where = [];

        if($this->is_app==0&&getConfigSetting($this->_uniacid,'shield_massage')==1){

            $where[] = ['c.industry_type','<>',1];
        }
        //获取门店关联的服务
        $data = $service_model->getStoreServicePage($input['id'],$where);

        $member_service = new \app\member\model\Service();

        $user_member = $member_service->getUserMember($this->_uniacid,$this->getUserId());

        if(!empty($data['data'])){

            foreach ($data['data'] as $k=>&$v){
                //关连的服务部位
                $v['position_title'] = $position_model->positionTitle($v['id']);

                $v['member_service'] = $user_member['member_auth']==1?$v['member_service']:0;

                $v['member_info']    = $member_service->getServiceMember($v['id'],$user_member['member_level'],$v['member_service']);
            }
        }
        //会员价
        $data['data'] = giveMemberPrice($this->_uniacid,$data['data']);

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-31 11:39
     * @功能说明:门店详情评价列表
     */
    public function commentList(){

        $input = $this->_param;

        $comment_model = new Comment();

        $coach_model   = new Coach();

        $store_model = new StoreList();
        //屏蔽技师
        $shield_coach = $coach_model->getShieldCoach($this->getUserId());

        $store_coach_id = $store_model->getStoreCoachId($input['id']);

        $coach_list = $coach_model->where(['status'=>2,'is_work'=>1])->where('id','in',$store_coach_id)->where('id','not in',$shield_coach)->column('id');

        $dis[] = ['a.status','=',1];

        $dis[] = ['a.coach_id','in',$coach_list];

        $data = $comment_model->dataList($dis);

        $config_model  = new \app\massage\model\Config();

        $anonymous_evaluate = $config_model->where(['uniacid'=>$this->_uniacid])->value('anonymous_evaluate');

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $v['create_time'] = date('Y-m-d H:i:s',$v['create_time']);
                //开启匿名评价
                if($anonymous_evaluate==1||$v['user_id']==0){

                    $v['nickName'] = '匿名用户';

                    $v['avatarUrl']= 'https://' . $_SERVER['HTTP_HOST'] . '/admin/farm/default-user.png';
                }
            }
        }

        return $this->success($data);

    }

    /**
     * @Desc: 门店套餐列表
     * @return mixed
     * @throws \think\db\exception\DbException
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/24 16:57
     */
    public function storePackList()
    {
        StorePackage::cancel($this->_uniacid);

        $input = request()->param();

        $admin_id = StoreList::getFirstValue(['id' => $input['store_id']]);

        $auth = Admin::where('id', $admin_id)->value('store_package_auth');

        $model = new PermissionPackage((int)$this->_uniacid);

        $p_auth = $model->pAuth();

        $where = [
            ['store_id', '=', $input['store_id']],
            ['uniacid', '=', $this->_uniacid],
            ['status', '=', 1]
        ];

        if (!$auth || !$p_auth) {

            $where[] = ['id', '<', 1];
        }

        $data = StorePackage::getIndexList($where, $input['limit'] ?? 10);

        $where[] = ['ensure', '=', 1];

        $count = StorePackage::where($where)->count();

        $data['is_ensure'] = $count > 0 ? 1 : 0;

        return $this->success($data);
    }

    /**
     * @Desc: 套餐详情
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/24 16:57
     */
    public function storePackInfo()
    {
        $id = request()->param('id', '');

        $data = StorePackage::getInfo($id);

        if (empty($data)) {

            $this->errorMsg('套餐不存在');
        }

        $data['discount'] = 0;

        if ($data['price'] < $data['init_price']) {

            $data['discount'] = round(($data['price'] / $data['init_price']) * 10, 1);
        }

        $data['sale'] = $data['total_sale'] > 10 ? (int)($data['total_sale'] / 10) * 10 : $data['total_sale'];

        return $this->success($data);
    }



}
