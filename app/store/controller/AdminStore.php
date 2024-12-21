<?php
namespace app\store\controller;
use app\AdminRest;
use app\massage\model\ActionLog;
use app\massage\model\Admin;
use app\massage\model\AdminRole;
use app\massage\model\Coach;
use app\massage\model\Commission;
use app\massage\model\Order;
use app\massage\model\Wallet;
use app\node\model\RoleAdmin;
use app\node\model\RoleList;
use app\node\model\RoleNode;
use app\store\model\CateConnect;
use app\store\model\CateConnectUpdate;
use app\store\model\StoreCate;
use app\store\model\StoreList;
use app\store\model\StoreUpdate;
use think\App;
use think\facade\Db;


class AdminStore extends AdminRest
{

    public function __construct(App $app) {

        parent::__construct($app);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-23 16:25
     * @功能说明:门店列表
     */
    public function storeList(){

        $input = $this->_param;

        $dis[] = ['uniacid','=',$this->_uniacid];

        if(isset($input['status'])){

            $dis[] = ['status','=',$input['status']];

        }else{

            $dis[] = ['status','>',-1];
        }

        if(!empty($input['title'])){

            $dis[] = ['title','like','%'.$input['title'].'%'];
        }

        if($this->_user['is_admin']==0){

            $dis[] = ['admin_id','=',$this->_user['admin_id']];
        }

        if(!empty($input['auth_status'])){

            $dis[] = ['auth_status','=',$input['auth_status']];
        }
        if(!empty($input['is_update'])){

            $dis[] = ['is_update','=',1];
        }

        $store_model = new StoreList();

        $data = $store_model->dataList($dis,$input['limit']);

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                if(empty($v['admin_id'])){

                    $v['status'] = 0;
                }
            }
        }

        $list = [

            0=>'all',

            1=>'ing',

            2=>'pass',

            4=>'nopass',

            5=>'update_num'
        ];

        foreach ($list as $k=> $value){

            $dis_s = [];

            $dis_s[] =['uniacid','=',$this->_uniacid];

            $dis_s[] =['status','>',-1];

            if($this->_user['is_admin']==0){

                $dis_s[] = ['admin_id','=',$this->_user['admin_id']];
            }

            if(!empty($k)&&$k!=5){

                $dis_s[] = ['auth_status','=',$k];

            }else{

                $dis_s[] = ['auth_status','>',-1];
            }

            if($k==5){

                $dis_s[] = ['is_update','=',1];
            }

            $data[$value] = $store_model->where($dis_s)->count();
        }

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-28 15:48
     * @功能说明:门店下拉框
     */
    public function storeSelect(){

        $input = $this->_param;

        $dis[] = ['a.uniacid','=',$this->_uniacid];

        $dis[] = ['a.status','=',1];

        $dis[] = ['b.store_auth','=',1];

        if(!empty($input['title'])){

            $dis[] = ['a.title','like','%'.$input['title'].'%'];
        }

        if($this->_user['is_admin']==0){

            $dis[] = ['a.admin_id','=',$this->_user['id']];
        }

        if(!empty($input['admin_id'])){

            $dis[] = ['a.admin_id','=',$input['admin_id']];
        }

        $store_model = new StoreList();

        $data = $store_model->alias('a')
                ->join('shequshop_school_admin b','a.admin_id = b.id')
                ->where($dis)
                ->field('a.*')
                ->group('a.id')
                ->select()
                ->toArray();

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-23 16:25
     * @功能说明:添加门店信息
     */
    public function storeAdd(){

        $input = $this->_input;

        $input['uniacid'] = $this->_uniacid;

        $input['total_num'] = $input['order_num'];

        $store_model = new StoreList();

        if(isset($input['cate_id'])){

            $cate_id = $input['cate_id'];

            unset($input['cate_id']);
        }

        if($this->_user['is_admin']==0){

            $input['admin_id'] = $this->_user['admin_id'];

            $input['status']   = 0;

            $input['auth_status'] = 1;
        }

        $res = $store_model->dataAdd($input);

        $id  = $store_model->getLastInsID();

        if(!empty($cate_id)){

            foreach ($cate_id as $k=>$value){

                $insert[$k] = [

                    'uniacid' => $this->_uniacid,

                    'store_id'=> $id,

                    'cate_id' => $value
                ];
            }

            $cate_model = new CateConnect();

            $cate_model->saveAll($insert);
        }

        return $this->success($res,200,$id);
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-23 16:34
     * @功能说明:门店信息
     */
    public function storeInfo(){

        $input = $this->_param;

        $dis = [

            'id' => $input['id']
        ];

        $store_model = new StoreList();

        $res = $store_model->dataInfo($dis);

        $cate_model = new CateConnect();

        $res['cate_id'] = $cate_model->where(['store_id'=>$input['id']])->column('cate_id');

        $res['cate_id'] = array_values($res['cate_id']);

        $cate_model = new StoreCate();

        $res['cate_name'] = $cate_model->where('id','in',$res['cate_id'])->where(['status'=>1])->column('title');

        $res['cate_name'] = !empty($res['cate_name'])?implode('、',$res['cate_name']):'';

        return $this->success($res);
    }


    /**
     * @author chenniang
     * @DataTime: 2024-10-14 10:43
     * @功能说明:代理商编辑门店
     */
    public function adminStoreUpdate(){

        $input = $this->_input;

        $store_update_model = new StoreUpdate();

        $store_model = new StoreList();

        $input['uniacid'] = $this->_uniacid;

        $input['store_id']= $input['id'];

        unset($input['id']);

        if(isset($input['cate_id'])&&$input['cate_id']!=-1734593){

            $cate_id = $input['cate_id'];

            unset($input['cate_id']);
        }

        Db::startTrans();

        $res = $store_model->dataUpdate(['id'=>$input['store_id']],['is_update'=>1]);

        if($res==0){

            Db::rollback();

            $this->errorMsg('修改失败');
        }

        $store_update_model->dataUpdate(['store_id'=>$input['store_id'],'status'=>1],['status'=>-1]);

        $res = $store_update_model->dataAdd($input);

        if($res==0){

            Db::rollback();

            $this->errorMsg('修改失败');
        }

        $update_id = $store_update_model->getLastInsID();

        if(!empty($cate_id)&&is_array($cate_id)){

            foreach ($cate_id as $k=>$value){

                $insert[$k] = [

                    'uniacid' => $this->_uniacid,

                    'store_id'=> $input['store_id'],

                    'cate_id' => $value,

                    'update_id'=>$update_id
                ];
            }

            $cate_model = new CateConnectUpdate();

            $cate_model->saveAll($insert);
        }

        Db::commit();

        return $this->success($res);
    }


    /**
     * @author chenniang
     * @DataTime: 2024-10-12 18:17
     * @功能说明:门店审核
     */
    public function storeCheck(){

        $input = $this->_input;

        $store_model = new StoreList();

        $update['auth_status'] = $input['auth_status'];

        if($input['auth_status']==2){

            $update['status'] = 1;
        }

        if($input['auth_status']==4){

            $update['status'] = 0;
        }

        $update['sh_time'] = time();

        $update['sh_text'] = $input['sh_text'];

        $res = $store_model->dataUpdate(['id'=>$input['id']],$update);

        return $this->success($res);
    }


    /**
     * @author chenniang
     * @DataTime: 2024-10-14 11:00
     * @功能说明:门店内容修改审核
     */
    public function storeDataCheck(){

        $input = $this->_input;

        $store_update_model = new StoreUpdate();

        $store_model = new StoreList();

        $data = $store_update_model->dataInfo(['id'=>$input['id'],'status'=>1]);

        if(empty($data)){

            $this->errorMsg('没有修改记录');
        }

        $store = $store_model->dataInfo(['id'=>$data['store_id']]);

        $arr = ['title','attestation','star','order_num','order_rate','positive_rate','business_license','text','start_time','end_time','lng','lat','address','cover','phone'];

        $update = ['is_update'=>0];

        foreach ($arr as $value){

            if($data[$value]!=-1734593){

                $update[$value] = $data[$value];
            }
        }

        if(isset($update['order_num'])){

            $update['total_num'] = $update['order_num']+$store['true_order_num'];
        }

        Db::startTrans();

        $res = $store_model->dataUpdate(['id'=>$data['store_id']],$update);

        if($res==0){

            Db::rollback();

            $this->errorMsg('审核失败');
        }

        $res = $store_update_model->dataUpdate(['id'=>$input['id']],['status'=>2]);

        if($res==0){

            Db::rollback();

            $this->errorMsg('审核失败');
        }

        $connect_model = new CateConnect();

        $connect_update_model = new CateConnectUpdate();

        $cate = $connect_update_model->where(['update_id'=>$input['id']])->select()->toArray();

        if($data['cate_id']!=-1734593){

            $connect_model->where(['store_id'=>$data['store_id']])->delete();

            if(!empty($cate)){

                foreach ($cate as $key=>$value){

                    $cate_update[$key]=[

                        'uniacid' => $this->_uniacid,

                        'store_id'=> $value['store_id'],

                        'cate_id' => $value['cate_id'],
                    ];
                }

                $connect_model->saveAll($cate_update);
            }
        }

        Db::commit();

        return $this->success($res);
    }


    /**
     * @author chenniang
     * @DataTime: 2024-10-14 11:40
     * @功能说明:修改内容详情
     */
    public function storeUpdateInfo(){

        $input = $this->_param;

        $store_update_model = new StoreUpdate();

        $data = $store_update_model->dataInfo(['store_id'=>$input['id'],'status'=>1]);

        if(empty($data)){

            $this->errorMsg('没有修改记录');
        }

        $cate_model = new CateConnectUpdate();

        $data['cate_id'] = $cate_model->where(['update_id'=>$data['id']])->column('cate_id');

        $data['cate_id'] = array_values($data['cate_id']);

        $cate_model = new StoreCate();

        $data['cate_name'] = $cate_model->where('id','in',$data['cate_id'])->where(['status'=>1])->column('title');

        $data['cate_name'] = !empty($data['cate_name'])?implode('、',$data['cate_name']):'';

        $data['cate_id'] = !empty($data['cate_id'])?$data['cate_id']:'-1734593';

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-23 16:35
     * @功能说明:编辑门店
     */
    public function storeUpdate(){

        $input = $this->_input;

        $dis = [

            'id' => $input['id']
        ];

        $store_model = new StoreList();

        $data = $store_model->dataInfo($dis);

        if(isset($input['status'])&&$input['status']==1&&empty($data['admin_id'])){

            $this->errorMsg('未绑定代理商');
        }

        if(isset($input['order_num'])){

            $input['total_num'] = $input['order_num']+$data['true_order_num'];
        }

        $cate_model = new CateConnect();

        if(isset($input['cate_id'])){

            $cate_id = $input['cate_id'];

            $cate_model->where(['store_id'=>$input['id']])->delete();

            unset($input['cate_id']);
        }

        $res = $store_model->dataUpdate($dis,$input);

        if(!empty($cate_id)){

            foreach ($cate_id as $k=>$value){

                $insert[$k] = [

                    'uniacid' => $this->_uniacid,

                    'store_id'=> $input['id'],

                    'cate_id' => $value
                ];
            }
            $cate_model->saveAll($insert);
        }

        return $this->success($res);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-07-21 17:27
     * @功能说明:分类列表
     */
    public function cateList(){

        $input = $this->_param;

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['status','>',-1];

        if(!empty($input['title'])){

            $dis[] = ['title','like','%'.$input['title'].'%'];

        }

        $cate_model = new StoreCate();

        $data = $cate_model->dataList($dis,$input['limit']);

        return $this->success($data);

    }





    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-07-21 17:27
     * @功能说明:分类列表
     */
    public function cateSelect(){

        $input = $this->_param;

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['status','=',1];

        if(!empty($input['title'])){

            $dis[] = ['title','like','%'.$input['title'].'%'];

        }

        $cate_model = new StoreCate();

        $data = $cate_model->where($dis)->order('top desc')->select()->toArray();

        return $this->success($data);

    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-07-21 17:31
     * @功能说明:分类添加
     */
    public function cateAdd(){

        $input = $this->_input;

        $input['uniacid'] = $this->_uniacid;

        $cate_model = new StoreCate();

        $data = $cate_model->dataAdd($input);

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-07-21 17:56
     * @功能说明:分类编辑
     */
    public function cateUpdate(){

        $input = $this->_input;

        $dis = [

            'id' => $input['id']
        ];

        $cate_model = new StoreCate();

        $res = $cate_model->dataUpdate($dis,$input);

        return $this->success($res);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-07-21 17:57
     * @功能说明:分类详情
     */
    public function cateInfo(){

        $input = $this->_param;

        $dis = [

            'id' => $input['id']
        ];

        $cate_model = new StoreCate();

        $res = $cate_model->dataInfo($dis);

        return $this->success($res);
    }




















}
