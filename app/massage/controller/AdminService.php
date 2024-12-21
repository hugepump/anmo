<?php
namespace app\massage\controller;
use app\AdminRest;
use app\industrytype\model\Type;
use app\massage\model\CateConnect;
use app\massage\model\CateList;
use app\massage\model\City;
use app\massage\model\Config;
use app\massage\model\Order;
use app\massage\model\Service;
use app\massage\model\ServiceGuarantee;
use app\massage\model\ServiceGuaranteeConnect;
use app\massage\model\ServicePositionConnect;
use app\massage\model\ServicePositionList;
use app\massage\model\StoreService;
use app\shop\model\Article;
use app\shop\model\Banner;
use app\shop\model\Cap;
use app\shop\model\GoodsCate;
use app\shop\model\GoodsSh;
use app\shop\model\GoodsShList;
use longbingcore\permissions\AdminMenu;
use longbingcore\wxcore\aliyun;
use longbingcore\wxcore\aliyunVirtual;
use think\App;
use app\shop\model\Goods as Model;
use think\Db;


class AdminService extends AdminRest
{


    protected $model;

    protected $goods_sh;

    protected $goods_sh_list;

    public function __construct(App $app) {

        parent::__construct($app);

        $this->model = new Service();


    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-15 14:43
     * @功能说明:商品列表
     */
    public function serviceList(){

        $this->model->where(['type'=>1,'admin_id'=>11])->update(['admin_id'=>0]);

        $input = $this->_param;

        $admin_model = new \app\massage\model\Admin();

        $dis[] = ['uniacid','=',$this->_uniacid];

        $type = !empty($input['type'])?$input['type']:1;

        if(!empty($input['status'])){

            $dis[] = ['status','=',$input['status']];

        }else{

            $dis[] = ['status','>',-1];
        }

        $where = [];

        if (!empty($input['name'])) {

            $where[] = ['title', 'like', '%' . $input['name'] . '%'];

            $admin_id = $admin_model->where('agent_name', 'like', '%' . $input['name'] . '%')->column('id');

            if ($input['name'] == '平台') {

                $admin_id[] = 0;
            }

            $where[] = ['admin_id', 'in', $admin_id];
        }

        if($type==1){

            $top = 'top desc,id desc';

            $dis[] = ['check_status','=',2];

        }else{

            $top = 'id desc';

            $dis[] = ['type','=',$type];

        }
        //判断插件权限没有返回空
        $auth = AdminMenu::getAuthList((int)$this->_uniacid,['store']);

        if(empty($auth['store'])){

            $dis[] = ['is_door','=',1];
        }

        if(!empty($input['check_status'])){

            $dis[] = ['check_status','=',$input['check_status']];
        }
        //可以看自己的和平台的
        if($this->_user['is_admin']==0){

            $dis[] = ['admin_id','in',[$this->_user['admin_id'],0]];
        }
        //平台编辑卡券是用
        if(!empty($input['coupon_admin_id'])){

            $dis[] = ['admin_id','in',[$input['coupon_admin_id'],0]];
        }

        if(!empty($input['admin_id'])){

            $dis[] = ['admin_id','=',$input['admin_id']];
        }

        if(!empty($input['industry_type'])){

            $dis[] = ['industry_type','=',$input['industry_type']];
        }

        if(!empty($input['start_time'])&&!empty($input['end_time'])){

            $dis[] = ['create_time','between',"{$input['start_time']},{$input['end_time']}"];
        }

        $is_add = !empty($input['is_add'])?$input['is_add']:0;

        $dis[] = ['is_add','=',$is_add];

        $data = $this->model->dataList($dis,$input['limit'],$top,$where);

        if(!empty($data['data'])){

            $city_model  = new City();

            $industry_model = new Type();

            foreach ($data['data'] as &$v){

                $v['industry_title'] = $industry_model->where(['id'=>$v['industry_type'],'status'=>1])->value('title');

                if(!empty($v['admin_id'])){

                    $v['admin_name'] = $admin_model->where(['id'=>$v['admin_id']])->value('agent_name');

                    $city_id = $admin_model->where(['id'=>$v['admin_id']])->value('city_id');

                    $v['admin_city'] = $city_model->where(['id'=>$city_id])->value('title');

                }else{

                    $v['admin_name'] = '平台';
                }
            }
        }

        $list = [

            0 =>'all',

            1 =>'ing',

            2 =>'pass',

            3 =>'nopass',
        ];

        foreach ($list as $k=> $value){

            $dis = [

                'uniacid' => $this->_uniacid,

                'type'    => 2
            ];

            if($this->_user['is_admin']==0){

                $dis['admin_id'] = $this->_user['admin_id'];
            }

            if(!empty($k)){

                $dis['check_status'] = $k;
            }

            $data[$value] = $this->model->where($dis)->where('status','>',-1)->count();
        }

        return $this->success($data);

    }





    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-15 14:58
     * @功能说明:审核详情
     */
    public function serviceInfo(){

        $input = $this->_param;

        $dis = [

            'id' => $input['id']
        ];

        $admin_id = $this->_user['is_admin']==0?$this->_user['admin_id']:0;

        $data = $this->model->dataInfo($dis,$admin_id);

        $store_model = new StoreService();

        $data['store'] = $store_model->getServiceStore($input['id']);

        $cate_model = new CateConnect();

        $data['cate_id'] = $cate_model->getServiceCate($input['id']);

        $member_service_model = new \app\member\model\Service();
        //关联的会员等级
        $data['service_level'] = $member_service_model->getMemberService($input['id']);

        $cate_model = new CateList();

        $data['cate_name'] = $cate_model->where('id','in',$data['cate_id'])->column('title');

        $data['cate_name'] = array_values($data['cate_name']);

        if(!empty($data['admin_id'])){

            $admin_model = new \app\massage\model\Admin();

            $data['reseller_auth'] = $admin_model->where(['id'=>$data['admin_id']])->value('reseller_auth');
        }else{

            $data['reseller_auth'] = true;
        }

        $position_model = new ServicePositionConnect();
        //关连的服务部位
        $position = $position_model->positionInfo($data['id']);

        $data['position']    = array_column($position,'id');

        $data['position_title'] = array_column($position,'title');

        $industry_model = new Type();

        $data['industry_title'] = $industry_model->where(['id'=>$data['industry_type'],'status'=>1])->value('title');

        //服务保障
        $guarantee_model = new ServiceGuaranteeConnect();

        $guarantee = $guarantee_model->guaranteeInfo($data['id']);

        $data['guarantee']    = array_column($guarantee,'id');

        $data['guarantee_title'] = array_column($guarantee,'title');

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-03 00:27
     * @功能说明:添加
     */
    public function serviceAdd(){

        $input = $this->_input;

        $input['uniacid'] = $this->_uniacid;

        $input['admin_id']= 0;

        if(!empty($this->_user['is_admin']==0)){

            $input['type']     = 2;

            $input['check_status'] = 1;

            $input['admin_id'] = $this->_user['admin_id'];

        }

        $res = $this->model->dataAdd($input);

        return $this->success($res,200,$res);

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-03 00:27
     * @功能说明:添加
     */
    public function serviceUpdate(){

        $input = $this->_input;

        $input['uniacid'] = $this->_uniacid;

        $dis = [

            'id' => $input['id']
        ];

        $info = $this->model->dataInfo($dis);
        //代理商重新审核
        if($this->_user['is_admin']==0&&isset($input['title'])){

            $input['check_status'] = 1;

           // $input['status'] = 0;
        }

        $res = $this->model->dataUpdate($dis,$input);

        return $this->success($res);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-26 21:42
     * @功能说明:审核门店服务
     */
    public function checkStoreGoods(){

        $input = $this->_input;

        $dis = [

            'id' => $input['id']
        ];

        $data = $this->model->dataInfo($dis);

        if($data['check_status']!=1){

            $this->errorMsg('服务已经审核');
        }

        $update = [

            'check_status' => $input['check_status'],

            'check_text'   => $input['check_text'],

            'check_time'   => time()
        ];

        $res = $this->model->dataUpdate($dis,$update);

        return $this->success($res);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-09 11:45
     * @功能说明:服务分类
     */
    public function cateList(){

        //dump(file_get_contents('https://yue.xinyuedaojia.cn/'));exit;

        $input = $this->_param;

        if(!empty($input['status'])){

            $dis[] = ['status','=',$input['status']];
        }else{

            $dis[] = ['status','>',-1];
        }

        $dis[] = ['uniacid','=',$this->_uniacid];

        if(!empty($input['title'])){

            $dis[] = ['title','like','%'.$input['title'].'%'];

        }

        $cate_model = new CateList();

        $data = $cate_model->dataList($dis,$input['limit']);

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-09 11:47
     * @功能说明:添加服务分类
     */
    public function cateAdd(){

        $input = $this->_input;

        $input['uniacid'] = $this->_uniacid;

        $cate_model = new CateList();

        $res = $cate_model->dataAdd($input);

        return $this->success($res);

    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-09 11:47
     * @功能说明:添加服务分类
     */
    public function cateUpdate(){

        $input = $this->_input;

        $dis = [

            'id' => $input['id']
        ];

        $cate_model = new CateList();
        //下架和删除
        if(isset($input['status'])&&in_array($input['status'],[-1])){

            $connect_model = new CateConnect();

            $have = $connect_model->getCateService($input['id']);

            if(!empty($have)){

                $this->errorMsg('有服务正在使用该分类');
            }
        }

        $res = $cate_model->dataUpdate($dis,$input);

        return $this->success($res);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-09 11:50
     * @功能说明:详情
     */
    public function cateInfo(){

        $input = $this->_param;

        $dis = [

            'id' => $input['id']
        ];

        $cate_model = new CateList();

        $res = $cate_model->dataInfo($dis);

        return $this->success($res);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-09 11:50
     * @功能说明:下拉
     */
    public function cateSelect(){

        $input = $this->_param;

        $dis = [

            'uniacid' => $this->_uniacid,

            'status'  => 1
        ];

        $cate_model = new CateList();

        $res = $cate_model->where($dis)->order('top desc,id desc')->select()->toArray();

        return $this->success($res);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-02-20 14:22
     * @功能说明:部位列表
     */
    public function positionList(){

        $input = $this->_param;

        $model = new ServicePositionList();

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['status','>',-1];

        if(!empty($input['title'])){

            $dis[] = ['title','like','%'.$input['title'].'%'];
        }

        $data = $model->dataList($dis,$input['limit']);

        return $this->success($data);
    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-02-20 14:22
     * @功能说明:部位列表
     */
    public function positionSelect(){

        $input = $this->_param;

        $model = new ServicePositionList();

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['status','=',1];

        $data = $model->where($dis)->order('top desc,id desc')->select()->toArray();

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-02-20 14:22
     * @功能说明:添加部位
     */
    public function positionAdd(){

        $input = $this->_input;

        $model = new ServicePositionList();

        $input['uniacid'] = $this->_uniacid;

        $data = $model->dataAdd($input);

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-02-20 14:25
     * @功能说明:编辑部位
     */
    public function positionUpdate(){

        $input = $this->_input;

        $model = new ServicePositionList();

        $dis = [

            'id' => $input['id']

        ];

        $data = $model->dataUpdate($dis,$input);

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-02-20 14:25
     * @功能说明:部位详情
     */
    public function positionInfo(){

        $input = $this->_param;

        $model = new ServicePositionList();

        $dis = [

            'id' => $input['id']

        ];

        $data = $model->dataInfo($dis);

        return $this->success($data);
    }

    /**
     * @Desc: 服务保障列表（分页）
     * @return \think\Response
     * @throws \think\db\exception\DbException
     * @Auther: shurong
     * @Time: 2024/7/12 17:59
     */
    public function guaranteeList()
    {
        $input = $this->_param;

        $where = [
            ['uniacid', '=', $this->_uniacid],

            ['status', '=', 1]
        ];

        $data = ServiceGuarantee::getList($where, $input['limit'] ?? 10);

        return $this->success($data);
    }

    /**
     * @Desc: 插入
     * @return \think\Response
     * @Auther: shurong
     * @Time: 2024/7/12 18:03
     */
    public function guaranteeAdd()
    {
        $data = $this->request->only(['title', 'sub_title', 'top']);

        $data['uniacid'] = $this->_uniacid;

        $res = ServiceGuarantee::add($data);

        return $this->success($res);
    }

    /**
     * @Desc: 修改
     * @return \think\Response
     * @Auther: shurong
     * @Time: 2024/7/12 18:06
     */
    public function guaranteeUpdate()
    {
        $data = $this->request->only(['id', 'title', 'sub_title', 'top', 'status']);

        $res = ServiceGuarantee::edit(['id' => $data['id']], $data);

        return $this->success($res);
    }

    /**
     * @Desc: 保障列表（不分页）
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @Auther: shurong
     * @Time: 2024/7/12 18:09
     */
    public function guaranteeListNoPage()
    {
        $where = [
            ['uniacid', '=', $this->_uniacid],

            ['status', '=', 1]
        ];

        $data = ServiceGuarantee::getListNoPae($where);

        return $this->success($data);
    }
}
