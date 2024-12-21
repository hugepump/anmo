<?php
namespace app\massage\controller;
use app\AdminRest;
use app\massage\model\BalanceCard;
use app\massage\model\BalanceOrder;
use app\massage\model\ChannelCate;
use app\massage\model\ChannelList;
use app\massage\model\ChannelQr;
use app\massage\model\ChannelWater;
use app\massage\model\Commission;
use app\massage\model\Coupon;
use app\massage\model\Order;
use app\massage\model\Salesman;
use app\massage\model\User;
use app\shop\model\Article;
use app\shop\model\Banner;
use app\shop\model\Cap;
use app\shop\model\Date;
use app\shop\model\MsgConfig;
use app\shop\model\OrderAddress;
use app\shop\model\OrderGoods;
use app\shop\model\RefundOrder;
use app\shop\model\RefundOrderGoods;
use app\shop\model\Wallet;
use longbingcore\wxcore\WxSetting;
use think\App;
use app\shop\model\Order as Model;
use think\facade\Db;


class AdminChannel extends AdminRest
{


    protected $model;

    protected $cate_model;

    public function __construct(App $app) {

        parent::__construct($app);

        $this->model = new ChannelList();

        $this->cate_model = new ChannelCate();


    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-04 19:09
     * @功能说明:类目列表
     */
    public function cateList(){

        $input = $this->_param;

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['status','>',-1];

        if(!empty($input['title'])){

            $dis[] = ['title','like','%'.$input['title'].'%'];

        }

        $data = $this->cate_model->dataList($dis,$input['limit']);

        return $this->success($data);

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-08-30 14:54
     * @功能说明:渠道商下拉
     */
    public function cateSelect(){

        $input = $this->_param;

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['status','=',1];

        $data = $this->cate_model->where($dis)->select()->toArray();

        return $this->success($data);

    }
    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-08-30 10:53
     * @功能说明:添加类目
     */
    public function cateAdd(){

        $input = $this->_input;

        $input['uniacid'] = $this->_uniacid;

        $res = $this->cate_model->dataAdd($input);

        return $this->success($res);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-08-30 10:53
     * @功能说明:添加类目
     */
    public function cateUpdate(){

        $input = $this->_input;

        $dis = [

            'id' => $input['id']
        ];

        $input['uniacid'] = $this->_uniacid;

        $res = $this->cate_model->dataUpdate($dis,$input);

        return $this->success($res);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-08-30 10:56
     * @功能说明:分类详情
     */
    public function cateInfo(){

        $input = $this->_param;

        $dis = [

            'id' => $input['id']
        ];

        $res = $this->cate_model->dataInfo($dis);

        return $this->success($res);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-08-30 14:54
     * @功能说明:渠道商下拉
     */
    public function channelSelect(){

        $input = $this->_param;

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['status','in',[2,3]];

        $data = $this->model->where($dis)->field('id,user_name')->select()->toArray();

        return $this->success($data);

    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-08-30 11:30
     * @功能说明:渠道商列表
     */
    public function channelList(){

        $input = $this->_param;

        $dis[] = ['a.uniacid','=',$this->_uniacid];

        if(!empty($input['status'])){

            $dis[] = ['a.status','=',$input['status']];

        }else{

            $dis[] = ['a.status','>',-1];
        }

        if($this->_user['is_admin']==0){

            $admin_model = new \app\massage\model\Admin();

            $admin_arr = $admin_model->where('id','in',$this->admin_arr)->where(['channel_auth'=>1])->column('id');

            $dis[] = ['a.admin_id','in',$admin_arr];
        }

        if(!empty($input['admin_id'])){

            $dis[] = ['a.admin_id','=',$input['admin_id']];
        }

        if(!empty($input['salesman_id'])){

            $dis[] = ['a.salesman_id','=',$input['salesman_id']];
        }

        if(!empty($input['no_salesman'])){

            $channel_id = $this->model->getBindSalesmanChannel();

            $dis[] = ['a.id','not in',$channel_id];

        }

        if(!empty($input['start_time'])&&!empty($input['end_time'])){

            $start_time = $input['start_time'];

            $end_time   = $input['end_time'];

            $dis[] = ['a.create_time','between',"$start_time,$end_time"];

        }

        $where = [];

        if(!empty($input['name'])){

            $where[] = ['a.user_name','like','%'.$input['name'].'%'];

            $where[] = ['a.mobile','like','%'.$input['name'].'%'];
        }

        $data = $this->model->adminDataList($dis,$input['limit'],$where);

        if(!empty($data['data'])){

            $saleman_model = new Salesman();

            $admin_model   = new \app\massage\model\Admin();

            $comm_model    = new Commission();

            foreach ($data['data'] as &$v){

                $v['salesman_name'] = $saleman_model->where(['id'=>$v['salesman_id'],'status'=>2])->value('user_name');

                $admin = $admin_model->dataInfo(['id'=>$v['admin_id'],'status'=>1]);

                if(!empty($admin['channel_auth'])){

                    $v['admin_name'] = $admin['agent_name'];
                }else{

                    $v['admin_id'] = 0;
                }

                if($v['balance']<0){

                    $v['balance'] = getConfigSetting($v['uniacid'],'channel_balance');
                }

                $v['total_cash'] = $comm_model->where(['top_id'=>$v['id'],'type'=>10])->where('status','=',2)->sum('cash');

                $v['total_cash'] = round($v['total_cash'],2);
            }
        }

        $list = [

            0=>'all',

            1=>'ing',

            2=>'pass',

            4=>'nopass'
        ];

        foreach ($list as $k=> $value){

            $dis_s = [];

            $dis_s[] =['uniacid','=',$this->_uniacid];

            if(!empty($k)){

                $dis_s[] = ['status','=',$k];

            }else{

                $dis_s[] = ['status','>',-1];

            }
            //是否是代理商
            if($this->_user['is_admin']==0){

                $dis_s[] = ['admin_id','in',$this->admin_arr];
            }

            $data[$value] = $this->model->where($dis_s)->count();

        }

        return $this->success($data);
    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-08-03 11:53
     * @功能说明:
     */
    public function channelInfo(){

        $input = $this->_param;

        $dis = [

            'id' => $input['id']
        ];

        $info = $this->model->dataInfo($dis);

        $user_model = new User();

        $info['nickName'] = $user_model->where(['id'=>$info['user_id']])->value('nickName');

        $saleman_model = new Salesman();

        $info['salesman_name'] = $saleman_model->where(['id'=>$info['salesman_id'],'status'=>2])->value('user_name');

        $admin_model   = new \app\massage\model\Admin();

        $info['admin_name'] = $admin_model->where(['id'=>$info['admin_id']])->value('agent_name');

        return $this->success($info);

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-03 00:15
     * @功能说明:审核(2通过,3取消,4拒绝)
     */
    public function channelUpdate(){

        $input = $this->_input;

        $diss = [

            'id' => $input['id']
        ];

        $info = $this->model->dataInfo($diss);

        if(!empty($input['status'])&&in_array($input['status'],[2,4,-1])){

            $input['sh_time'] = time();
        }
        //删除需要判断佣金提现
        if(isset($input['status'])&&$input['status']==-1){

            if($info['cash']>0){

                $this->errorMsg('还有佣金未提现');
            }

            $dis = [

                'top_id'  => $input['id'],

                'status'  => 1,

                'type'    => 10
            ];

            $cash_model = new Commission();

            $cash = $cash_model->where($dis)->where('cash','>',0)->find();

            if(!empty($cash)){

                $this->errorMsg('还有佣金未到账');

            }

            $dis = [

                'coach_id'=> $input['id'],

                'status'  => 1,

                'type'    => 5
            ];

            $wallet_model = new \app\massage\model\Wallet();

            $wallet = $wallet_model->dataInfo($dis);

            if(!empty($wallet)){

                $this->errorMsg('还有提现未处理');
            }
        }

        if(isset($input['cash'])){

            unset($input['cash']);
        }

        $data = $this->model->dataUpdate($diss,$input);

        $info = $this->model->dataInfo($diss);

        $qr_model = new ChannelQr();

        $qr_model->dataUpdate(['channel_id'=>$info['id']],['salesman_id'=>$info['salesman_id'],'admin_id'=>$info['admin_id']]);

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-06-21 14:26
     * @功能说明:绑定业务员
     */
    public function bindSalesman(){

        $input = $this->_input;

        if(empty($input['channel_id'])||!is_array($input['channel_id'])){

            $this->errorMsg('请选择渠道商');
        }

        $qr_model = new ChannelQr();

        foreach ($input['channel_id'] as $value){

            $this->model->dataUpdate(['id'=>$value],['salesman_id'=>$input['salesman_id']]);

            $qr_model->dataUpdate(['channel_id'=>$value],['salesman_id'=>$input['salesman_id']]);
        }

        return $this->success(true);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-07-14 14:03
     * @功能说明:设置渠道商单独的佣金比例
     */
    public function setChannelBalance(){

        $input = $this->_input;

        $res = $this->model->where('id','in',$input['id'])->update(['balance'=>$input['balance']]);

        return $this->success($res);
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-07-14 14:03
     * @功能说明:设置渠道商单独的佣金比例
     */
    public function setChannelQrBalance(){

        $input = $this->_input;

        $qr_model  = new ChannelQr();

        $salesman_model = new Salesman();

        if(getConfigSetting($this->_uniacid,'salesman_channel_fx_type')==2){

            $list = $qr_model->where('id','in',$input['id'])->select()->toArray();

            $defult_salesman_balance = getConfigSetting($this->_uniacid,'salesman_balance');

            foreach ($list as $value){

                if(!empty($value['salesman_id'])){

                    $salesman_balance = $salesman_model->where(['id'=>$value['salesman_id']])->value('balance');

                    if($salesman_balance<0){

                        $salesman_balance = $defult_salesman_balance;
                    }

                    if($salesman_balance<$input['balance']){

                        $err_code[] = $value['code'];
                    }
                }
            }

            if(!empty($err_code)){

                $this->errorMsg('提成比例不能大于渠道码关联业务员比例,渠道码编码'.implode(',',$err_code));
            }

        }

        $res = $qr_model->where('id','in',$input['id'])->update(['balance'=>$input['balance']]);

        return $this->success($res);
    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-07-14 14:03
     * @功能说明:删除渠道商单独的佣金比例
     */
    public function delChannelBalance(){

        $input = $this->_input;

        $res = $this->model->where('id','in',$input['id'])->update(['balance'=>-1]);

        return $this->success($res);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-07-25 13:58
     * @功能说明:批量添加渠道码
     */
    public function channelQrAdd(){

        $input = $this->_input;

        $core  = new WxSetting($this->_uniacid);

        if($input['num']>50||$input['num']<=0){

            $this->errorMsg('渠道码一次生成数量1-20');
        }
        if(!empty($input['salesman_id'])&&getConfigSetting($this->_uniacid,'salesman_channel_fx_type')==2){

            $salesman_model = new Salesman();

            $salesman_balance = $salesman_model->where(['id'=>$input['salesman_id']])->value('balance');

            if($salesman_balance<0){

                $salesman_balance = getConfigSetting($this->_uniacid,'salesman_balance');
            }

            if($salesman_balance<$input['balance']){

                $this->errorMsg('提成比例不能大于业务员比例');
            }
        }

        //批量生成微信的二维码
        $list = $core->batchChannelWechatQr($input['num']);

        $channel_id = 0;

        if($this->_user['is_admin']==0){

            $input['admin_id'] = $this->_user['admin_id'];
        }

        $admin_id    = !empty($input['admin_id'])?$input['admin_id']:0;

        $salesman_id = !empty($input['salesman_id'])?$input['salesman_id']:0;

        if(!empty($input['user_id'])){

            $channel_id = $this->model->getUserChannel($input['user_id'],$this->_uniacid,$admin_id,$salesman_id);
        }

        $qr_model = new ChannelQr();

        $id = [];

        if(!empty($list)){

            foreach ($list as $k=>$value){

                $insert= [
                    'uniacid' => $this->_uniacid,
                    'cate_id' => $input['cate_id'],
                    'title'   => $input['title'],
                    'balance' => $input['balance'],
                    'cost'    => $input['cost'],
                    'start_time'=> $input['start_time'],
                    'end_time'  => $input['end_time'],
                    'text'      => $input['text'],
                    'lng'       => $input['lng'],
                    'lat'       => $input['lat'],
                    'city'      => $input['city'],
                    'province'  => $input['province'],
                    'area'      => $input['area'],
                    'salesman_id'=> $salesman_id,
                    'code'    => $value['code'],
                    'qr'      => $value['qr'],
                    'create_time' => time(),
                    'admin_id' => $admin_id,
                    'channel_id' => $channel_id,
                ];
                $qr_model->insert($insert);
                $id[] = $qr_model->getLastInsID();
            }
        }else{

            $this->errorMsg('未获取到微信配置，请右上角清除缓存重试');
        }

        return $this->success(true,200,$id);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-01 09:59
     * @功能说明:渠道商二维码
     */
    public function channelQr(){

        $input = $this->_param;

        $dis[] = ['a.uniacid','=',$this->_uniacid];

        $dis[] = ['a.status','>',-1];

        $admin_id = $this->_user['is_admin']==0?$this->_user['admin_id']:0;

        $dis[] = ['a.admin_id','=',$admin_id];

        if(!empty($input['channel_name'])){

            $dis[] = ['b.user_name','like','%'.$input['channel_name'].'%'];
        }

        if(!empty($input['code'])){

            $dis[] = ['a.code','like','%'.$input['code'].'%'];

        }

        $where = [];
        //查询上级
        if(!empty($input['top_name'])){

            $where[] = ['c.user_name','like','%'.$input['top_name'].'%'];

            $where[] = ['d.agent_name','like','%'.$input['top_name'].'%'];

            $where[] = ['e.nickName','like','%'.$input['top_name'].'%'];
        }

        $qr_model = new ChannelQr();
        //异步批量画图
        publisher(json_encode(['action'=>'channelQr','uniacid'=>$this->_uniacid], true));

        $data = $qr_model->qrDataList($dis,$input['limit'],$where);

        return $this->success($data);


    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-07-25 18:03
     * @功能说明:渠道商二维码列表（统计）
     */
    public function channelQrList(){

        $input = $this->_param;

        $dis[] = ['a.uniacid','=',$this->_uniacid];

        $dis[] = ['a.status','>',-1];

        $admin_id = $this->_user['is_admin']==0?$this->_user['admin_id']:0;

        $dis_sql  = "a.uniacid = $this->_uniacid AND a.status > '-1'";

        if($this->_user['is_admin']==0){

            $dis[] = ['a.admin_id','=',$admin_id];

            $dis_sql .= "AND a.admin_id = $admin_id ";
        }

        if(!empty($input['channel_name'])){

            $channel_name = $input['channel_name'];

            $dis_sql.= "AND b.user_name LIKE '%".$channel_name."%'";

            $dis[] = ['b.user_name','like','%'.$input['channel_name'].'%'];
        }

        if(!empty($input['code'])){

            $code = $input['code'];

            $dis_sql.= "AND a.code LIKE '%".$code."%'";

            $dis[] = ['a.code','like','%'.$input['code'].'%'];

        }

        if(!empty($input['title'])){

            $dis_sql.= "AND a.title LIKE '%".$input['title']."%'";

            $dis[] = ['a.title','like','%'.$input['title'].'%'];
        }

        $qr_model = new ChannelQr();

        $start_time = !empty($input['start_time'])?$input['start_time']:'';

        $end_time   = !empty($input['end_time'])?$input['end_time']:'';

        $data = $qr_model->adminDataList($dis,$dis_sql,$input['type'],$input['page'],$input['limit'],$start_time,$end_time);
        //异步批量画图
        publisher(json_encode(['action'=>'channelQr','uniacid'=>$this->_uniacid], true));

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-03 11:58
     * @功能说明:下载二维码 返回码图片
     */
    public function downloadQr(){

        $input = $this->_input;

        $qr_model = new ChannelQr();

        if(empty($input['id'])){

            $this->errorMsg('请勾选渠道码');

        }

        $data = $qr_model->where('id','in',$input['id'])->select()->toArray();

        foreach ($data as $k=>$v){

            if(empty($v['qr_img'])){

                $qr = $qr_model->channelQrImg($v);

                $qr_model->dataUpdate(['id'=>$v['id']],['qr_img'=>$qr]);

                $v['qr_img'] = $qr;
            }

            $arr[$k]['qr_img'] = $v['qr_img'];

            $arr[$k]['code']   = $v['code'];

        }
        return $this->success($arr);
    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-07-25 18:37
     * @功能说明:渠道码详情
     */
    public function channelQrInfo(){

        $input = $this->_param;

        $dis = [

            'id' => $input['id']
        ];

        $qr_model = new ChannelQr();

        $data = $qr_model->dataInfo($dis);

        if(empty($data['qr_img'])){

            $img = $qr_model->channelQrImg($data);

            $qr_model->dataUpdate($dis,['qr_img'=>$img]);

            $data = $qr_model->dataInfo($dis);

        }

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-07-25 18:38
     * @功能说明:渠道码编辑
     */
    public function channelQrUpdate(){

        $input = $this->_input;

        $dis = [

            'id' => $input['id']
        ];

        $qr_model = new ChannelQr();

        if(key_exists('user_id',$input)){

            $user_id = $input['user_id'];

            unset($input['user_id']);
        }

        $input['salesman_id'] = !empty($input['salesman_id'])?$input['salesman_id']:0;

        if($this->_user['is_admin']==0){

            $input['admin_id'] = $this->_user['admin_id'];
        }

        if(!empty($input['salesman_id'])&&getConfigSetting($this->_uniacid,'salesman_channel_fx_type')==2){

            $salesman_model = new Salesman();

            $salesman_balance = $salesman_model->where(['id'=>$input['salesman_id']])->value('balance');

            if($salesman_balance<0){

                $salesman_balance = getConfigSetting($this->_uniacid,'salesman_balance');
            }

            if($salesman_balance<$input['balance']){

                $this->errorMsg('提成比例不能大于业务员比例');
            }
        }

        $data = $qr_model->dataUpdate($dis,$input);

        if(!empty($user_id)){

            $admin_id   = !empty($input['admin_id'])?$input['admin_id']:0;

            $channel_id = $this->model->getUserChannel($user_id,$this->_uniacid,$admin_id,$input['salesman_id']);

            $qr_model->dataUpdate($dis,['channel_id'=>$channel_id]);

        }else{

            $qr_model->dataUpdate($dis,['channel_id'=>0]);
        }

        return $this->success($data);
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-07-25 18:38
     * @功能说明:渠道码批量删除
     */
    public function channelQrDel(){

        $input = $this->_input;

        $dis[] = ['id','in',$input['id']];

        $qr_model = new ChannelQr();

        $data = $qr_model->dataUpdate($dis,['status'=>-1]);

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-07-31 14:15
     * @功能说明:绑定渠道商
     */
    public function bindChannel(){

        $input = $this->_input;

        $admin_id = $this->_user['is_admin']==0?$this->_user['admin_id']:0;

        $channel_id = $this->model->getUserChannel($input['user_id'],$this->_uniacid,$admin_id);

        $qr_model = new ChannelQr();

        foreach ($input['id'] as $value ){

            $qr_model->dataUpdate(['id'=>$value],['channel_id'=>$channel_id]);
        }

        return $this->success(true);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-07-31 14:43
     * @功能说明:用户列表是渠道商的话要返渠道商信息
     */
    public function channelUserList(){

        $input = $this->_param;

        $admin_id = $this->_user['is_admin']==0?$this->_user['admin_id']:0;

        $user_id = $this->model->where(['uniacid'=>$this->_uniacid])->where('admin_id','<>',$admin_id)->where('status','>',-1)->column('user_id');

        $dis[] = ['a.uniacid','=',$this->_uniacid];

        $dis[] = ['a.status','=',1];

        $dis[] = ['a.id','not in',$user_id];

        $mapor = [];

        if(!empty($input['name'])&&$this->_user['is_admin']!=0){

            $mapor[] = ['a.nickName','like','%'.$input['name'].'%'];

            $mapor[] = ['a.phone','like','%'.$input['name'].'%'];

            $mapor[] = ['b.user_name','like','%'.$input['name'].'%'];

            $mapor[] = ['b.mobile','like','%'.$input['name'].'%'];
        }

        if($this->_user['is_admin']==0&&empty($input['name'])){

            $dis[] = ['a.id','=',-1];
        }

        if($this->_user['is_admin']==0&&!empty($input['name'])){

            $mapor[] = ['a.nickName','=',$input['name']];

            $mapor[] = ['a.phone','=',$input['name']];

            $mapor[] = ['b.user_name','=',$input['name']];

            $mapor[] = ['b.mobile','=',$input['name']];

        }

        $user_model = new User();

        $data = $user_model->alias('a')
                ->join('massage_channel_list b','a.id = b.user_id AND b.status = 2','left')
                ->where($dis)
                ->where(function ($query) use ($mapor){
                    $query->whereOr($mapor);
                })
                ->field('a.id,a.nickName,a.avatarUrl,a.phone,b.user_name as channel_name,b.id as channel_id,b.mobile,ifnull(b.salesman_id,0) as salesman_id,ifnull(b.admin_id,0) as admin_id')
                ->group('a.id')
                ->order('a.id desc')
                ->paginate($input['limit'])
                ->toArray();

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-09-22 15:19
     * @功能说明:用户列表
     */
    public function nochannelUserList(){

        $input = $this->_param;

        if($this->_user['is_admin']==0&&empty($input['nickName'])){

            $where[] = ['id','=',-1];
        }

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['status','in',[0,1,2,3]];

        $user_id = $this->model->where($dis)->column('user_id');

        $where1 = [];

        if(!empty($input['nickName'])){

            if($this->_user['is_admin']==0){

                $where1[] = ['nickName','=',$input['nickName']];

                $where1[] = ['phone','=',$input['nickName']];

            }else{

                $where1[] = ['nickName','like','%'.$input['nickName'].'%'];

                $where1[] = ['phone','like','%'.$input['nickName'].'%'];
            }
        }

        $user_model = new User();

        $where[] = ['uniacid','=',$this->_uniacid];

        $where[] = ['id','not in',$user_id];

        $where[] = ['status', '=', 1];

        $list = $user_model->dataList($where,$input['limit'],$where1);

        return $this->success($list);

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-07-21 17:08
     * @功能说明:申请渠道商
     */
    public function applyChannel(){

        $input = $this->_input;

        $distribution_model = new ChannelList();

        $dis[] = ['status','>',-1];

        $dis[] = ['user_id','=',$input['user_id']];

        $find = $distribution_model->dataInfo($dis);

        if(!empty($find)&&in_array($find['status'],[1,2,3])){

            $this->errorMsg('该用户已经申请');

        }
        //如果是业务员邀请需要绑定业务员以及业务员的代理商
        if(!empty($input['salesman_id'])){

            $salesman_model = new Salesman();

            $salesman = $salesman_model->dataInfo(['id'=>$input['salesman_id'],'status'=>2]);

            if(!empty($salesman)){

                $salesman_id = $salesman['id'];

                $admin_id    = $salesman['admin_id'];
            }
        }

        if(!empty($input['admin_id'])){

            $admin_model = new \app\massage\model\Admin();

            $admin = $admin_model->dataInfo(['id'=>$input['admin_id'],'status'=>1]);

            if(!empty($admin)){

                $admin_id = $input['admin_id'];
            }
        }

        if($this->_user['is_admin']==0){

            $admin_id = $this->_user['admin_id'];
        }

        $insert = [

            'uniacid'  => $this->_uniacid,

            'user_id'  => $input['user_id'],

            'user_name'=> $input['user_name'],

            'true_user_name' => !empty($input['true_user_name'])?$input['true_user_name']:$input['user_name'],

            'mobile'   => $input['mobile'],

            'cate_id'  => $input['cate_id'],

            'time'     => !empty($input['time'])?$input['time']:0,

            'time_type'=> !empty($input['time_type'])?$input['time_type']:0,

            'text'     => !empty($input['text'])?$input['text']:'',

            'status'   => 2,

            'salesman_id'=> !empty($salesman_id)?$salesman_id:0,

            'admin_id'   => !empty($admin_id)?$admin_id:0,

            'controller' => $this->_user['id'],

            'sh_time'    => time()
        ];

        if(!empty($find)&&$find['status']==4){

            $res = $distribution_model->dataUpdate(['id'=>$find['id']],$insert);

        }else{

            $res = $distribution_model->dataAdd($insert);

            $channel_id = $distribution_model->getLastInsID();

            ChannelWater::initWater($this->_uniacid,$channel_id);
        }

        return $this->success($res);
    }







}
