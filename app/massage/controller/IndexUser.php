<?php
namespace app\massage\controller;
use app\adapay\model\Bank;
use app\adapay\model\Member;
use app\admin\model\CardUser;
use app\ApiRest;

use app\balancediscount\model\CardWater;
use app\balancediscount\model\UserCard;
use app\card\model\UserPhone;
use app\card\model\UserSk;
use app\coachbroker\model\CoachBroker;
use app\dynamic\model\DynamicFollow;
use app\fdd\model\FddAgreementRecord;
use app\fdd\model\FddConfig;
use app\industrytype\model\Type;
use app\massage\model\AgentApply;
use app\massage\model\BrokerWater;
use app\massage\model\ChannelCate;
use app\massage\model\ChannelList;
use app\massage\model\ChannelQr;
use app\massage\model\ChannelScanQr;
use app\massage\model\ChannelWater;
use app\massage\model\City;
use app\massage\model\Coach;
use app\massage\model\CoachAccount;
use app\massage\model\CoachCollect;
use app\massage\model\CoachLevel;
use app\massage\model\CoachTimeList;
use app\massage\model\CoachType;
use app\massage\model\Commission;
use app\massage\model\CommShare;
use app\massage\model\Config;
use app\massage\model\ConfigSetting;
use app\massage\model\Coupon;
use app\massage\model\CouponAtv;
use app\massage\model\CouponAtvRecord;
use app\massage\model\CouponAtvRecordCoupon;
use app\massage\model\CouponAtvRecordList;
use app\massage\model\CouponRecord;
use app\massage\model\Address;

use app\massage\model\DistributionList;
use app\massage\model\Feedback;
use app\massage\model\IconCoach;
use app\massage\model\Order;
use app\massage\model\QrBind;
use app\massage\model\Salesman;
use app\massage\model\SalesmanWater;
use app\massage\model\Service;
use app\massage\model\ShieldList;
use app\massage\model\ShortCodeConfig;
use app\massage\model\StationIcon;
use app\massage\model\StoreCoach;
use app\massage\model\StoreList;
use app\massage\model\User;
use app\massage\model\UserChannel;
use app\massage\model\UserWater;
use app\massage\model\Wallet;
use app\member\model\Level;
use app\memberdiscount\model\Card;
use app\mobilenode\model\RoleAdmin;
use app\partner\model\PartnerOrder;
use longbingcore\permissions\AdminMenu;
use longbingcore\wxcore\PayModel;
use longbingcore\wxcore\WxSetting;
use think\App;
use think\facade\Db;
use think\Request;


class IndexUser extends ApiRest
{

    protected $model;

    protected $address_model;

    protected $coach_model;

    protected $coupon_record_model;

    protected $follow_model;

    public function __construct(App $app) {

        parent::__construct($app);

        $this->model = new User();

        $this->address_model = new Address();

        $this->coach_model = new Coach();

        $this->coupon_record_model = new CouponRecord();

        $this->follow_model = new DynamicFollow();

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-12-08 14:12
     * @功能说明:认证技师
     */
    public function attestationCoach(){

        $data = $this->model->dataInfo(['id'=>$this->getUserId()]);

        $cap_dis[] = ['user_id','=',$this->getUserId()];

        $cap_dis[] = ['status','in',[1,2,3,4]];
        //查看是否是团长
        $cap_info = $this->coach_model->where($cap_dis)->order('status')->find();

        $cap_info = !empty($cap_info)?$cap_info->toArray():[];
        //-1表示未申请团长，1申请中，2已通过，3取消,4拒绝
        $data['coach_status'] = !empty($cap_info)?$cap_info['status']:-1;
        //认证技师
        if($data['coach_status']==-1){

            $this->coach_model->attestationCoach($data);
        }
        //查看是否是团长
        $cap_info = $this->coach_model->where($cap_dis)->order('status')->find();

        $cap_info = !empty($cap_info)?$cap_info->toArray():[];
        //-1表示未申请团长，1申请中，2已通过，3取消,4拒绝
        $data['coach_status'] = !empty($cap_info)?$cap_info['status']:-1;

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-19 15:48
     * @功能说明:个人中心
     */
    public function index(){

        if(empty($this->getUserId())){

            return $this->success([]);
        }

        $key = 'userindex_user_index'.$this->getUserId();

        $value = getCache($key,$this->_uniacid);

        if(!empty($value)){

            return $this->success($value);
        }

        $data = $this->model->dataInfo(['id'=>$this->getUserId()]);

        $data['member_discount_auth'] = memberDiscountAuth($this->_uniacid)['status'];

        if($data['member_discount_auth']==1){

            $data['member_status'] = $data['member_discount_time']>time()?1:0;

            if($data['member_status']==1){

                $member_card_model = new Card();
                //会员折扣
                $data['member_discount_title'] = $member_card_model->where(['id'=>$data['member_discount_id']])->value('title');
                //天
                $data['member_discount_day']   = ceil(($data['member_discount_time']-time())/86400);
            }
        }
        //是否有提现功能
      // $data['wallet_status'] = getFxStatus($this->_uniacid)==1?0:1;
        $data['wallet_status'] = 1;
        //获取各类角色的审核结果
        $data = $this->model->authCheckData($data);
        //优惠券数
        $data['coupon_count'] = $this->coupon_record_model->couponCount($this->getUserId());
        //用户余额
        $data['balance'] = getUserBalance($this->_user['id'],$this->_uniacid);

        list($balance, $card_balance) = getUserBalanceTwo($this->_user['id'], $this->_uniacid);

        $data['some_balance'] = $balance;

        $data['card_balance'] = $card_balance;

        //说明是技师
        if(in_array($data['coach_status'],[2,3])){
            //技师等级
            $data['coach_level'] = $this->coach_model->getCoachLevel($data['coach_id'],$this->_uniacid);
        }

        $atv_model  = new CouponAtv();

        $atv_record_model = new CouponAtvRecord();

        $dis = [

            'user_id' => $this->getUserId(),

            'status'  => 1
        ];
        //查询有没有进行中的活动
        $atv_ing = $atv_record_model->dataInfo($dis);

        $is_atv  = 0;

        if(!empty($atv_ing)){

            $is_atv = 1;

        }else{

            $atv_config = $atv_model->dataInfo(['uniacid'=>$this->_uniacid]);

            $where[] = ['user_id','=',$this->getUserId()];

            $where[] = ['status','<>',3];

            $count = $atv_record_model->where($where)->count();

            if($atv_config['status']==1&&$atv_config['start_time']<time()&&$atv_config['end_time']>time()&&$count<$atv_config['atv_num']){

                $is_atv = 1;
            }
        }

        $data['is_atv'] = $is_atv;
        //是否开启了推荐有礼
        $data['is_atv_status'] = $atv_model->where(['uniacid'=>$this->_uniacid])->value('is_atv_status');

        $data['is_atv_status'] = !empty($data['is_atv_status'])?$data['is_atv_status']:0;
        //技师收藏
        $data['collect_count'] = $this->coach_model->coachCollectCount($this->getUserId(),$this->_uniacid);
        //关注技师数量
        $data['follow_count']  = $this->follow_model->followCoachNum($this->getUserId());
        //搭子冻结金额
        $data['partner_wait_money'] = PartnerOrder::getWaitPrice($this->_uniacid,$this->getUserId());

        $level_model = new Level();
        //初始化会员权益
        $level_model->initMemberRights($this->getUserId());

        setCache($key,$data,3,$this->_uniacid);

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-07-25 11:11
     * @功能说明:判断用户是否是技师
     */
    public function getUserCoachStatus(){

        if(empty($this->getUserId())){

            return $this->success(['status'=>-1]);
        }

        $key = 'getUserCoachStatus_key'.$this->getUserId();

        $value = getCache($key,$this->_uniacid);

        if(!empty($value)){

            return $this->success($value);
        }

        $cap_dis[] = ['user_id','=',$this->getUserId()];

        $cap_dis[] = ['status','in',[1,2,3,4]];

        $coach_model = new Coach();

        $coach_info = $coach_model->where($cap_dis)->order('id desc')->field('id as coach_id,lng,lat,address,coach_position,status')->find();

        if(empty($coach_info)){

            $coach_info = ['status'=>-1];
        }

        setCache($key,$coach_info,3,$this->_uniacid);

        return $this->success($coach_info);
    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-19 15:54
     * @功能说明:用户地址列表
     */
    public function addressList(){

        $dis[] = ['user_id','=',$this->getUserId()];

        $dis[] = ['status','>',-1];

        $data = $this->address_model->dataList($dis,10);

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-19 15:57
     * @功能说明:用户地址详情
     */
    public function addressInfo(){

        $input = $this->_param;

        $dis = [

            'id' => $input['id']
        ];

        $data = $this->address_model->dataInfo($dis);

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-19 15:58
     * @功能说明:添加用户地址
     */
    public function addressAdd(){

        $input = $this->_input;

        $input['uniacid'] = $this->_uniacid;

        $input['user_id'] = $this->getUserId();

        if(empty($input['area'])){

            $city_data = getCityByLat($input['lng'],$input['lat'],$this->_uniacid);

            $input = array_merge($city_data,$input);
        }
        //验证码校验
        if(!empty($input['phone_code'])){

            $phone_code = getCache($input['mobile'],$this->_uniacid);

            if($input['phone_code']!=$phone_code){

                return $this->error('验证码错误');
            }

            delCache($input['phone_code'],$this->_uniacid);

            unset($input['phone_code']);
        }

        $res = $this->address_model->dataAdd($input);

        if($input['status']==1){

            $id = $this->address_model->getLastInsID();

            $this->address_model->updateOne($id);
        }

        return $this->success($res);
    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-19 15:58
     * @功能说明:添加用户地址
     */
    public function addressUpdate(){

        $input = $this->_input;

        $dis = [

            'id' => $input['id']
        ];

        if(empty($input['area'])&&!empty($input['lng'])){

            $city_data = getCityByLat($input['lng'],$input['lat'],$this->_uniacid);

            $input = array_merge($city_data,$input);
        }
        //验证码校验
        if(!empty($input['phone_code'])){

            $phone_code = getCache($input['mobile'],$this->_uniacid);

            if($input['phone_code']!=$phone_code){

                return $this->error('验证码错误');
            }

            delCache($input['phone_code'],$this->_uniacid);

            unset($input['phone_code']);
        }

        $res = $this->address_model->dataUpdate($dis,$input);

        if(!empty($input['status'])&&$input['status']==1){

            $this->address_model->updateOne($input['id']);
        }

        return $this->success($res);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-11 22:54
     * @功能说明:获取默认地址
     */
    public function getDefultAddress(){

        $address_model = new Address();

        $address = $address_model->dataInfo(['user_id'=>$this->getUserId(),'status'=>1]);

        return $this->success($address);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-19 16:13
     * @功能说明:删除地址
     */
    public function addressDel(){

        $input = $this->_input;

        $dis = [

            'id' => $input['id']
        ];

        $res = $this->address_model->where($dis)->delete();

        return $this->success($res);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-22 13:56
     * @功能说明:修改用户信息 授权微信信息等
     */
    public function userUpdate(){

        $input = $this->_input;

        $dis = [

            'id' => $this->getUserId()
        ];

        if(!empty($input['coupon_atv_id'])&&empty($this->getUserInfo()['nickName'])&&!empty($input['nickName'])){

            $coupon_atv_model = new CouponAtv();

            $coupon_atv_model->invUser($this->getUserId(),$input['coupon_atv_id']);
        }

        if($this->is_app==0){

            $setting = new WxSetting($this->_uniacid);

            $res = $setting->checkKeyWordsv2($input['nickName']);

            if($res==false){

                return $this->error('昵称含有敏感违禁词');
            }
        }
        $update = [

            'nickName' => $input['nickName'],

            'gender'   => !empty($input['gender'])?$input['gender']:'',

            'language' => !empty($input['language'])?$input['language']:'',

            'city'     => !empty($input['city'])?$input['city']:'',

            'province' => !empty($input['province'])?$input['province']:'',

            'country'  => !empty($input['country'])?$input['country']:'',

            'avatarUrl'=> !empty($input['avatarUrl'])?$input['avatarUrl']:'',

        ];

        $res = $this->model->dataUpdate($dis,$update);

        //
        if(!empty($input['encryptedData'])){

            $encryptedData = $input[ 'encryptedData' ];

            $iv            = $input[ 'iv' ];

            $config    = longbingGetAppConfig($this->_uniacid);

            $appid     = $config[ 'appid' ];

            $session_key = $this->model->where(['id'=>$this->getUserId()])->value('session_key');

            if(empty($session_key)){

                $this->errorMsg('need login',401);
            }
            $datas = null;
            //  解密
            $errCode = decryptDataLongbing( $appid, $session_key, $encryptedData, $iv, $datas );
            //获取unionid
            if ( $errCode == 0 )
            {
                $data = json_decode( $datas, true );

                $unionid = !empty($data['unionid'])?$data['unionid']:'';

                if(!empty($unionid)){

                    $dis = [

                        'unionid' => $unionid,

                        'uniacid' => $this->uniacid

                    ];

                    $find = $this->model->dataInfo($dis);

                    if(!empty($find)){

                        $this->errorMsg('need login',401);

                    }else{

                        $this->model->dataUpdate(['id'=>$this->getUserId()],['unionid'=>$unionid]);

                    }
                }
            }else{

                return $this->error( $errCode );
            }
        }

        $user_info = $this->model->dataInfo(['id'=>$this->getUserId()]);

        setCache($this->autograph, $user_info, 7200, $this->_uniacid);

        return $this->success($res);

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-22 14:08
     * @功能说明:用户信息
     */
    public function userInfo(){

        $data = $this->model->dataInfo(['id'=>$this->getUserId()]);

        $cap_dis[] = ['user_id','=',$this->getUserId()];

        $cap_dis[] = ['status','in',[1,2,3,4]];
        //查看是否是团长
        $cap_info = $this->coach_model->where($cap_dis)->order('status')->find();

        $cap_info = !empty($cap_info)?$cap_info->toArray():[];
        //-1表示未申请团长，1申请中，2已通过，3取消,4拒绝
        $data['coach_status'] = !empty($cap_info)?$cap_info['status']:-1;

        $data['coach_position'] = !empty($cap_info['coach_position'])&&$data['coach_status']==2?1:0;

        $distri_model = new DistributionList();

        $fx = $distri_model->dataInfo($cap_dis);

        $data['fx_status'] = !empty($fx)?$fx['status']:-1;

        $data['fx_text']   = !empty($fx)?$fx['sh_text']:'';

        $channel_model = new ChannelList();

        $channel = $channel_model->dataInfo($cap_dis);

        $data['channel_status'] = !empty($channel)?$channel['status']:-1;

        $data['channel_text']   = !empty($channel)?$channel['sh_text']:'';
        //税点
        $data['tax_point']   =  getConfigSetting($this->_uniacid,'tax_point');

        $auth = AdminMenu::getAuthList((int)$this->_uniacid,['heepay','adapay']);

        if($auth['adapay']!=false){

            $adapay_config = new \app\adapay\model\Config();

            $config_data = $adapay_config->dataInfo(['uniacid'=>$this->_uniacid]);

            $auth['adapay'] = !empty($config_data['status'])?$auth['adapay']:0;
        }

        if($auth['heepay']!=false&&$auth['adapay']!=1){

            $adapay_config  = new \app\heepay\model\Config();

            $config_data    = $adapay_config->dataInfo(['uniacid'=>$this->_uniacid]);

            $auth['heepay'] = !empty($config_data['status'])?$auth['heepay']:0;
        }

        $data['bank_card_id']= '';

        $data['bank_status'] = -1;

        if($auth['adapay']==1){

            $adapay_member_model = new Member();

            $adapay_bank_model   = new Bank();
            //分账系统绑定的银行卡
            $adapay_member = $adapay_member_model->where(['user_id'=>$this->getUserId()])->where('status','>',-1)->find();

            if(!empty($adapay_member)){

                $data['bank_card_id'] = $adapay_bank_model->where(['order_member_id'=>$adapay_member['id']])->value('card_id');
            }

            $data['bank_status'] = $adapay_member['status'];
        }

        if($auth['heepay']==1){

            $member = new \app\heepay\model\Member();

            $bank_card_id = $member->where(['user_id'=>$this->getUserId()])->where('status','>',-1)->field('bank_card_no,audit_status as status')->find();

            $data['bank_card_id'] = $bank_card_id['bank_card_no'];

            $data['bank_status'] = $bank_card_id['status'];
        }

        return $this->success($data);
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-23 09:39
     * @功能说明:技师详情
     */
    public function coachInfo(){

        if(empty($this->getUserId())){

            return $this->success([]);
        }
        $order_model = new Order();

        $order_model->coachBalanceArr($this->_uniacid);

        $cap_dis[] = ['user_id','=',$this->getUserId()];

        $cap_dis[] = ['status','in',[1,2,3,4]];

        $cap_info = $this->coach_model->dataInfo($cap_dis);

        $city_model = new City();

        if(!empty($cap_info)){

            $cap_info['city'] = $city_model->where(['id'=>$cap_info['city_id']])->value('title');
            //技师真正的等级
            $coach_level = $this->coach_model->getCoachLevel($cap_info['id'],$this->_uniacid);
            //技师等级
            $cap_info['coach_level'] = $this->coach_model->coachLevelInfo($coach_level);

            $cap_info['text_type']  = $this->coach_model->getCoachWorkStatus($cap_info['id'],$this->_uniacid);

            $record_model= new FddAgreementRecord();

            $config_model= new FddConfig();

            $dis = [

                'user_id'  => $this->_user['id'],

                'status'   => 2,

                'admin_id' => $cap_info['admin_id']
            ];
            //待签约
            $fdd_agreement = $record_model->where($dis)->field('download_url,viewpdf_url,end_time')->order('id desc')->find();

            $dis['status'] = 4;
            //已经签约待合同
            $cap_info['fdd_agreement'] = $record_model->where($dis)->field('download_url,viewpdf_url,end_time')->order('id desc')->find();

            $fdd_status = $config_model->getStatus($this->_uniacid);

            $cap_info['fdd_auth_status'] = $fdd_status;
            //开启了法大大
            if($fdd_status==1){

                if(!empty($fdd_agreement)){

                    $cap_info['fdd_status'] = 1;

                }else{

                    $cap_info['fdd_status'] = 0;
                }
            }else{

                $cap_info['fdd_status'] = 2;
            }

            $cap_info['address'] = getCoachAddress($cap_info['lng'],$cap_info['lat'],$cap_info['uniacid'],$cap_info['id']);

            $industry_model = new Type();

            $cap_info['industry_info'] = $industry_model->where(['id'=>$cap_info['industry_type'],'status'=>1])->find();
            //绑定门店(新)
            $cap_info['store'] = StoreCoach::getStoreList($cap_info['id']);
        }

        $level_model = new CoachLevel();
        //技师最高可提成
        $cap_info['max_level'] = $level_model->where(['uniacid'=>$this->_uniacid,'status'=>1])->max('balance');

        return $this->success($cap_info);
    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-22 13:35
     * @功能说明:申请技师
     */
    public function coachApply(){

        $input = $this->_input;

        if(!empty($input['short_code'])){

            $short_code = getCache($input['mobile'],$this->_uniacid);
            //验证码验证手机号
            if($input['short_code']!=$short_code){

                return $this->error('验证码错误');
            }

            unset($input['short_code']);
        }

        $res = $this->coach_model->coachApply($input,$this->getUserId(),$this->_uniacid);

        if(!empty($res['code'])){

            $this->errorMsg($res['msg']);
        }

        setCache($input['mobile'],'',99,$this->_uniacid);

        return $this->success($res);
    }





    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-07 22:41
     * @功能说明:教练收藏列表
     */

    public function coachCollectList(){

        $input = $this->_param;

        $config_model = new ConfigSetting();

        $config = $config_model->dataInfo($this->_uniacid);

        $collect_model = new CoachCollect();

        if(in_array($config['coach_format'],[1,3])){

            $data = $collect_model->coachCollectListTypeOne($input,$this->_user['id'],$this->_uniacid);

        }else{

            $data = $collect_model->coachCollectListTypeTow($input,$this->_user['id'],$this->_uniacid);
        }

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-07 22:57
     * @功能说明:添加技师收藏
     */
    public function addCollect(){

        $input = $this->_input;

        $insert = [

            'uniacid' => $this->_uniacid,

            'coach_id'=> $input['coach_id'],

            'user_id' => $this->getUserId()
        ];

        $collect_model = new CoachCollect();

        $res = $collect_model->dataAdd($insert);

        return $this->success($res);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-07 22:59
     * @功能说明:取消收藏
     */
    public function delCollect(){

        $input = $this->_input;

        $dis = [

            'uniacid' => $this->_uniacid,

            'coach_id'=> $input['coach_id'],

            'user_id' => $this->getUserId()
        ];

        $collect_model = new CoachCollect();

        $res = $collect_model->where($dis)->delete();

        return $this->success($res);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-08 11:51
     * @功能说明:用户优惠券列表
     */
    public function userCouponList(){

        $input = $this->_param;

        $this->coupon_record_model->initCoupon($this->_uniacid);

        $dis[] = ['a.user_id','=',$this->getUserId()];

        $dis[] = ['a.status','=',$input['status']];

        $dis[] = ['a.is_show','=',1];

        if(isset($input['use_scene'])){

            $dis[] = ['a.use_scene','=',$input['use_scene']];
        }

        if(!empty($input['title'])){

            $dis[] = ['c.title','like','%'.$input['title'].'%'];
        }
      //  $data = $this->coupon_record_model->dataList($dis);

        $data = $this->coupon_record_model->alias('a')
                ->join('massage_service_coupon_store b','a.id=b.coupon_id AND b.type=1','left')
                ->join('massage_store_list c','b.store_id=c.id','left')
                ->where($dis)
                ->field('a.*')
                ->group('a.id')
                ->order('a.id desc')
                ->paginate(10)
                ->toArray();

        $coupon_model = new Coupon();

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $v['send_type'] = $coupon_model->where(['id'=>$v['coupon_id']])->value('send_type');

                $v['start_time'] = date('Y.m.d H:i',$v['start_time']).' - '.date('Y.m.d H:i',$v['end_time']);
            }
        }

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-16 22:09
     * @功能说明:删除优惠券
     */
    public function couponDel(){

        $input = $this->_input;

        $coupon = $this->coupon_record_model->dataInfo(['id'=>$input['coupon_id']]);

        if($coupon['status']==1){

            $this->errorMsg('待使用待卡券不能删除');
        }

        $res = $this->coupon_record_model->dataUpdate(['id'=>$input['coupon_id']],['is_show'=>0]);

        return $this->success($res);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-13 19:45
     * @功能说明:优惠券活动详情
     */
    public function couponAtvInfo(){

        $input = $this->_input;

        $atv_record_model = new CouponAtvRecord();

        $atv_record_list_model = new CouponAtvRecordList();

        $atv_model = new CouponAtv();

        if(empty($input['id'])){

            $dis_where[] = ['status','=',1];

            $dis_where[] = ['end_time','<',time()];
            //修改过期状态
            $atv_record_model->dataUpdate($dis_where,['status'=>3]);

            $dis = [

                'user_id' => $this->getUserId(),

                'status'  => 1
            ];
            //查询有没有进行中的活动
            $atv_ing = $atv_record_model->dataInfo($dis);

            if(empty($atv_ing)){

                $atv_ing = $this->couponAtvAdd();
            }
            //
            if(empty($atv_ing)){

                $atv_ing = $atv_record_model->where(['user_id'=>$this->getUserId()])->order('id desc')->find();

                $atv_ing = !empty($atv_ing)?$atv_ing->toArray():[];
            }

            if(empty($atv_ing)){

                $this->errorMsg('你没有可以进行的活动');
            }

        }else{

            $dis = [

                'id'  => $input['id']
            ];
            //查询有没有进行中的活动
            $atv_ing = $atv_record_model->dataInfo($dis);
        }

        $atv = $atv_model->dataInfo(['uniacid'=>$this->_uniacid]);

        $atv_ing['atv_num']   = $atv['atv_num'];

        $atv_ing['end_time'] -= time();

        $atv_ing['end_time']  = $atv_ing['end_time']>0?$atv_ing['end_time']:0;

        $data['atv_info']    = $atv_ing;
        //邀请记录
        $data['record_list'] = $atv_record_list_model->dataList(['a.record_id'=>$atv_ing['id']],50);

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-12 16:29
     * @功能说明:发起优惠券活动
     */
    public function couponAtvAdd(){

        $atv_model  = new CouponAtv();

        $atv_record_model = new CouponAtvRecord();

        $atv_record_coupon_model = new CouponAtvRecordCoupon();

        $atv_config = $atv_model->dataInfo(['uniacid'=>$this->_uniacid]);

        if($atv_config['status']==0){

            return [];
        }

        if($atv_config['start_time']>time()||$atv_config['end_time']<time()){

            return [];
        }

        if(empty($atv_config['coupon'])){

            return [];
        }

        $where[] = ['user_id','=',$this->getUserId()];

        $where[] = ['status','<>',3];

        $count = $atv_record_model->where($where)->count();

        if($count>=$atv_config['atv_num']){

            return [];
        }

        $insert = [

            'uniacid' => $this->_uniacid,

            'user_id' => $this->getUserId(),

            'atv_id'  => $atv_config['id'],

            'atv_start_time' => $atv_config['start_time'],

            'atv_end_time'   => $atv_config['end_time'],

            'inv_user_num'   => $atv_config['inv_user_num'],

            'inv_time'     => $atv_config['inv_time'],

            'start_time'   => time(),

            'end_time'     => time()+$atv_config['inv_time']*3600,

            'inv_user'     => $atv_config['inv_user'],

            'to_inv_user'  => $atv_config['to_inv_user'],

            'share_img'    => $atv_config['share_img'],

         ];

        Db::startTrans();

         $res = $atv_record_model->dataAdd($insert);

         if($res==0){

             Db::rollback();

             $this->errorMsg('发起活动失败');
         }

         $record_id = $atv_record_model->getLastInsID();
         //记录该活动需要派发那些券
         foreach ($atv_config['coupon'] as $value){

             $insert = [

                 'uniacid'   => $this->_uniacid,

                 'atv_id'    => $atv_config['id'],

                 'record_id' => $record_id,

                 'coupon_id' => $value['coupon_id'],

                 'num'       => $value['num'],

             ];

             $res = $atv_record_coupon_model->dataAdd($insert);

             if($res==0){

                 Db::rollback();

                 $this->errorMsg('发起活动失败');
             }
         }

        Db::commit();

        $record = $atv_record_model->dataInfo(['id'=>$record_id]);

        return $record;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-25 17:40
     * @功能说明：生产二维码
     */

    public function atvQr(){

        $input = $this->_input;

        $key = 'atv_coupon'.$input['coupon_atv_id'];

        $qr  = getCache($key,$this->_uniacid);

        if(empty($qr)){

//            $qr_insert = [
//
//                'coupon_atv_id' => $input['coupon_atv_id']
//            ];
            //获取二维码
            $qr = $this->model->orderQr($input,$this->_uniacid);

            setCache($key,$qr,86400,$this->_uniacid);
        }

        $qr = !empty($qr)?$qr:$this->defaultImage['image'];

        return $this->success($qr);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-14 19:22
     * @功能说明:授权手机号
     */
    public function reportPhone ()
    {

        $params = $this->_input;

        $encryptedData = $params[ 'encryptedData' ];

        if(!empty($params['decode'])){

            $encryptedData = rawurldecode($encryptedData);
        }

        $iv            = $params[ 'iv' ];

        $config    = longbingGetAppConfig($this->_uniacid);

        $appid     = $config[ 'appid' ];

        $session_key = $this->model->where(['id'=>$this->getUserId()])->value('session_key');

        if(empty($session_key)){

            $this->errorMsg('need login',401);
        }
        $data = null;
        //  解密
        $errCode = decryptDataLongbing( $appid, $session_key, $encryptedData, $iv, $data );

        if ( $errCode == 0 )
        {
            $data = json_decode( $data, true );

            $phone = $data[ 'purePhoneNumber' ];

        }else{

            return $this->error( $errCode );
        }

        $res = $this->model->dataUpdate(['id'=>$this->getUserId()],['phone'=>$phone]);

        $user_info = $this->model->dataInfo(['id'=>$this->getUserId()]);

        setCache($this->autograph, $user_info, 7200, $this->_uniacid);

        return $this->success($phone);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-08-28 23:03
     * @功能说明:佣金记录
     */
    public function commList(){

        $input = $this->_param;

        $limit = !empty($input['limit'])?$input['limit']:10;

        $dis[] = ['a.uniacid','=',$this->_uniacid];

        $dis[] = ['a.cash','>',0];

        $dis[] = ['a.top_id','=',$this->getUserId()];

        $dis[] = ['a.type','=',1];

        if(!empty($input['status'])){

            $dis[] = ['a.status','=',$input['status']];
        }else{

            $dis[] = ['a.status','>',-1];

        }

        $comm_model = new Commission();

        $order_model = new Order();

        $coach_model = new Coach();

        $data = $comm_model->recordList($dis,$limit);

        $data['total_cash'] = $comm_model->where(['top_id'=>$this->getUserId()])->where('type','in',[1,9])->where('status','>',-1)->sum('cash');

        $data['total_cash'] = round($data['total_cash'],2);

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $coach_id = $order_model->where(['id'=>$v['order_id']])->value('coach_id');

                $v['coach_name'] = $coach_model->where(['id'=>$coach_id])->value('coach_name');

                $v['nickName']   = $this->model->where(['id'=>$v['user_id']])->value('nickName');

                $v['create_time'] = date('Y-m-d H:i:s',$v['create_time']);
            }
        }

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-03-09 13:43
     * @功能说明:base64转图片
     */
    public function base64ToImg(){

        $input = $this->_input;

        $image = $input['img'];

        $imageName = "25220_".date("His",time())."_".rand(1111,9999).'.png';

        if (strstr($image,",")){
            $image = explode(',',$image);
            $image = $image[1];
        }

        $base_path = '/image/' . $this->uniacid . '/' . date('y') . '/' . date('m');

        $path = FILE_UPLOAD_PATH.$base_path;

        if (!is_dir($path)){ //判断目录是否存在 不存在就创建
            mkdir($path,0777,true);
        }
        $imageSrc=  $path."/". $imageName;  //图片名字

        $r = file_put_contents($imageSrc, base64_decode($image));//返回的是字节数
//        if (!$r) {
//            return json(['data'=>null,"code"=>1,"msg"=>"图片生成失败"]);
//        }else{
//            return json(['data'=>1,"code"=>0,"msg"=>"图片生成成功"]);
//        }

        $img = UPLOAD_PATH.$imageSrc;

        return $this->success($img);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-25 17:40
     * @功能说明：加盟商邀请技师二维码
     */

    public function adminCoachQr(){

        $input = $this->_param;

        $admin_model = new \app\massage\model\Admin();

        $dis = [

            'user_id' => $this->getUserId(),

            'status'  => 1
        ];

        $admin_user = $admin_model->dataInfo($dis);

        if(empty($admin_user)){

            $this->errorMsg('你还不是加盟商');
        }

        $key = 'join_admin'.$admin_user['id'].'-'.$this->is_app;

        $qr  = getCache($key,$this->_uniacid);

        if(empty($qr)){
            //小程序
            if($this->is_app==0){

                $input['page'] = 'technician/pages/apply';

             //   $input['page'] = 'pages/user/home';

                $input['admin_id'] = $admin_user['id'];
                //获取二维码
                $qr = $this->model->orderQr($input,$this->_uniacid);

            }else{

                $page = 'https://'.$_SERVER['HTTP_HOST'].'/h5/#/technician/pages/apply?admin_id='.$admin_user['id'];

                $qr = base64ToPng(getCode($this->_uniacid,$page));

            }

            setCache($key,$qr,86400,$this->_uniacid);
        }

        $qr = !empty($qr)?$qr:'https://'.$_SERVER['HTTP_HOST'].'/favicon.ico';

        return $this->success($qr);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-06-14 18:44
     * @功能说明:用户分销数据
     */
    public function userCashInfo(){

        $user_info = $this->model->dataInfo(['id'=>$this->getUserId()]);

        $data['new_cash'] = $user_info['new_cash'];

        $dis = [

            'top_id' => $this->getUserId(),

            'type'     => 1
        ];

        $comm_model = new Commission();

        $wallet_model = new Wallet();

        $data['total_cash']      = $comm_model->where($dis)->where('status','>',-1)->sum('cash');

        $dis['status'] = 1;
        //未入账金额
        $data['unrecorded_cash'] = $comm_model->where($dis)->sum('cash');

        $data['unrecorded_cash'] = round($data['unrecorded_cash'],2);

        $data['total_cash']      = round($data['total_cash'],2);
        //累计提现
        $data['extract_total_price'] = $wallet_model->userCash($this->getUserId(),2);

        $data['extract_ing_price'] = $wallet_model->userCash($this->getUserId(),1);

        return $this->success($data);

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-24 13:33
     * @功能说明:用户申请提现
     */
    public function applyWallet(){

        $input = $this->_input;

        $key = 'user_wallet'.$this->getUserId();
        //加一个锁防止重复提交
        incCache($key, 1, $this->_uniacid,30);

        $value = getCache($key,$this->_uniacid);

        if ($value!=1) {

            decCache($key,1, $this->_uniacid);

            $this->errorMsg('网络错误，请刷新重试');
        }

        $user_info = $this->model->dataInfo(['id'=>$this->getUserId()]);

        $new_cash = $user_info['new_cash'];

        if(empty($input['apply_price'])||$input['apply_price']<0.01){

            decCache($key,1, $this->_uniacid);

            $this->errorMsg('提现费最低一分');
        }
        //服务费
        if($input['apply_price']>$new_cash){

            decCache($key,1, $this->_uniacid);

            $this->errorMsg('余额不足');
        }

        $reseller_model = new DistributionList();

        $admin_id = $reseller_model->where(['user_id'=>$this->getUserId(),'status'=>2])->value('admin_id');
        //获取税点
        $tax_point = getConfigSetting($this->_uniacid,'tax_point');

        $balance = 100-$tax_point;

        Db::startTrans();

        $insert = [

            'uniacid'       => $this->_uniacid,

            'user_id'       => $this->getUserId(),

            'coach_id'      => 0,

            'admin_id'      => !empty($admin_id)?$admin_id:0,

            'total_price'   => $input['apply_price'],

            'balance'       => $balance,

            'apply_price'   => round($input['apply_price']*$balance/100,2),

            'service_price' => round( $input['apply_price'] * $tax_point / 100, 2),

            'tax_point'     => $tax_point,

            'code'          => orderCode(),

            'text'          => $input['text'],

            'type'          => 4,

            'last_login_type'=> $this->is_app,

            'apply_transfer' => !empty($input['apply_transfer'])?$input['apply_transfer']:0
        ];

        $wallet_model = new Wallet();
        //提交审核
        $res = $wallet_model->dataAdd($insert);

        if($res!=1){

            Db::rollback();
            //减掉
            decCache($key,1, $this->_uniacid);

            $this->errorMsg('申请失败');
        }

        $id = $wallet_model->getLastInsID();

        $water_model = new UserWater();

        $res = $water_model->updateCash($this->_uniacid,$this->getUserId(),$input['apply_price'],2,$id,0,2);

        if ($res == 0) {

            Db::rollback();
            //减掉
            decCache($key,1, $this->_uniacid);

            $this->errorMsg('申请失败');
        }

        Db::commit();
        //减掉
        decCache($key,1, $this->_uniacid);

        return $this->success($res);
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-30 14:39
     * @功能说明:用户分销提现记录
     */
    public function walletList(){

        $wallet_model = new Wallet();

        $input = $this->_param;

        $dis[] = ['user_id','=', $this->getUserId()];

        if(!empty($input['status'])){

            $dis[] = ['status','=', $input['status']];

        }

        $resller_model = new DistributionList();

        $del_time = $resller_model->where(['user_id'=>$this->getUserId(),'status'=>-1])->max('del_time');

        if(!empty($del_time)){

            $dis[] = ['create_time','>', $del_time];
        }

        $dis[] = ['type','=', 4];
        //提现记录
        $data = $wallet_model->dataList($dis,10);

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $v['create_time'] = date('Y-m-d H:i:s',$v['create_time']);
            }
        }
        //累计提现
        $data['extract_total_price'] = $wallet_model->userCash($this->getUserId(),2,$del_time);

        $data['personal_income_tax_text'] = getConfigSetting($this->_uniacid,'personal_income_tax_text');

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-06-15 14:56
     * @功能说明:我的团队
     */
    public function myTeam(){

        $order = $this->request->param('order', 0);

        $user_model = new User();

        $dis = [

            'a.pid' => $this->getUserId()
        ];

        switch ($order) {

            case 1:
                $sort = 'pay_price desc,create_time desc';
                break;
            case 2:
                $sort = 'pay_price asc,create_time desc';
                break;
            case 3:
                $sort = 'create_time asc';
                break;
            case 4:
            default:
                $sort = 'create_time desc';
        }

        $data = $user_model->alias('a')
                ->join('massage_service_order_list b','a.id = b.user_id AND b.pay_type > 1','left')
                ->where($dis)
                ->field('a.id,a.city,a.area,a.province,a.create_time,a.nickName,a.avatarUrl,ifnull(SUM(b.true_service_price),0) as pay_price,ifnull(COUNT(b.id),0) as order_count')
                ->group('a.id')
                ->order($sort)
                ->paginate(10)
                ->toArray();

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $v['create_time'] = date('Y-m-d H:i:s',$v['create_time']);

                $v['pay_price'] = round($v['pay_price'],2);
            }
        }

        return $this->success($data);
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-25 17:40
     * @功能说明：加盟商邀请技师二维码
     */

    public function userCommQr(){

        $input = $this->_param;

        $type = getConfigSetting($this->_uniacid,'wechat_qr_type');

        $key = 'user_commsssssss'.$this->getUserId().'-'.$this->is_app.'-'.$type;

        $qr  = getCache($key,99999999);

        if(empty($qr)){
            //小程序
            if($this->is_app==0){

                $input['page'] = 'user/pages/gzh';

                $input['pid'] = $this->getUserId();
                //获取二维码
                $qr = $this->model->orderQr($input,$this->_uniacid);

            }else{
                if($type==0){

                    $page = 'https://'.$_SERVER['HTTP_HOST'].'/h5/#/user/pages/gzh?pid='.$this->getUserId();

                    $qr = base64ToPng(getCode($this->_uniacid,$page));

                }else{

                    $core = new WxSetting($this->_uniacid);

                    $db_code_do = getConfigSetting($this->_uniacid,'db_code_do');

                    if($db_code_do==1){

                        $qr = $core->qrCode($this->getUserId().',aa');
                    }else{

                        $qr = $core->qrCode($this->getUserId());
                    }
                }
            }

            setCache($key,$qr,864000000,99999999);
        }

        $qr = !empty($qr)?$qr:'https://'.$_SERVER['HTTP_HOST'].'/favicon.ico';

        return $this->success($qr);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-07-21 17:08
     * @功能说明:申请分销商
     */
    public function applyReseller(){

        $input = $this->_input;

        $distribution_model = new DistributionList();

        $dis[] = ['status','>',-1];

        $dis[] = ['user_id','=',$this->getUserId()];

        $find = $distribution_model->dataInfo($dis);

        if(!empty($find)&&in_array($find['status'],[1,2,3])){

            $this->errorMsg('你已经申请');
        }
        //有没有授权付费分销插件
        $auth = $distribution_model->getPayResellerAuth($this->_uniacid);

        if(!empty($input['level_reseller_id'])){

            $top = $distribution_model->where(['id'=>$input['level_reseller_id']])->where('status','in',[2,3])->find();
        }

        $insert = [

            'uniacid'  => $this->_uniacid,

            'user_id'  => $this->getUserId(),

            'user_name'=> $input['user_name'],

            'true_user_name' => !empty($input['true_user_name'])?$input['true_user_name']:$input['user_name'],

            'mobile'   => $input['mobile'],

            'text'     => $input['text'],

            'status'   => $auth==true?-1:1,

            'pid'      => !empty($top)?$top['id']:0,

            'admin_id' => !empty($input['admin_id'])?$input['admin_id']:0
        ];

        if($auth!=true&&getConfigSetting($this->_uniacid,'reseller_check_type')==1){

            $insert['status'] = 2;

            $insert['sh_time'] = time();
        }

        Db::startTrans();

        if(!empty($top)){

            $insert['admin_id'] = $top['admin_id'];
        }

        if(!empty($find)&&$find['status']==4){

            $insert['status'] = $auth==true?4:$insert['status'];

            $res = $distribution_model->dataUpdate(['id'=>$find['id']],$insert);

            $id = $find['id'];

        }else{

            $res = $distribution_model->dataAdd($insert);

            $id  = $distribution_model->getLastInsID();
        }
        //如果开启了分销员付费插件
        if($auth==true){

            if(empty($input['pay_reseller_type'])){

                Db::rollback();

                $this->errorMsg('请选择套餐');
            }

            $config = getConfigSettingArr($this->_uniacid,['reseller_threshold','level_reseller_threshold','reseller_inv_balance','level_reseller_inv_balance','wx_point','ali_point']);

            $pay_price = $input['pay_reseller_type']==1?$config['reseller_threshold']:$config['level_reseller_threshold'];

            $order_insert = [

                'uniacid' => $this->_uniacid,

                'user_id' => $this->_user['id'],

                'reseller_id'=> $id,

                'pay_price' => $pay_price,

                'order_code'=> orderCode(),

                'type'      => $input['pay_reseller_type'],

                'pay_model' => $input['pay_model'],

                'app_pay'   => $this->is_app,
            ];

            if(!empty($top)){

                $order_insert['top_reseller_id'] = $top['id'];

                $order_insert['top_user_id'] = $top['user_id'];
            }

            $order_model = new \app\payreseller\model\Order();

            $order_model->dataAdd($order_insert);

            $order_id = $order_model->getLastInsID();
            //如果有分享人 需要给佣金
            if(!empty($top)){

                $balance = $input['pay_reseller_type']==1?$config['reseller_inv_balance']:$config['level_reseller_inv_balance'];

                $cash = round($order_insert['pay_price']*$balance/100,2);

                if ($input['pay_model']==3){

                    $point = $config['ali_point'];

                }else{

                    $point = $config['wx_point'];
                }

                $point_cash = round($cash*$point/100,2);

                $cash -= $point_cash;

                $comm_insert = [

                    'uniacid' => $this->_uniacid,

                    'user_id' => $this->_user['id'],

                    'top_id'  => $order_insert['top_user_id'],

                    'order_id'=> $order_id,

                    'order_code' => $order_insert['order_code'],

                    'cash' => $cash,

                    'type' => 15,

                    'balance' => $balance,

                    'admin_id' => !empty($input['admin_id'])?$input['admin_id']:0,

                    'sub_reseller_id' => $id,

                    'reseller_id' => $order_insert['top_reseller_id'],

                    'status' => -1
                ];

                $comm_model = new Commission();

                $comm_model->dataAdd($comm_insert);

                $comm_id = $comm_model->getLastInsID();

                $share_data = [

                    'pay_point' => $point,

                    'inv_reseller_point_cash' => $point_cash,

                    'id' => $order_id,

                    'uniacid' => $this->_uniacid
                ];

                $share_model = new CommShare();
                //添加手续费
                $share_model->addPointData($comm_id,$share_data,$comm_insert['type'],$comm_insert['top_id']);
            }
        }

        Db::commit();

        if($auth==true){

            if($order_insert['pay_price']<=0){

                $order_model->orderResult($order_insert['order_code'],$order_insert['order_code']);

                return $this->success(true);
            }

            if ($input['pay_model']==3){

                $pay_model = new PayModel($this->payConfig());

                $jsApiParameters  = $pay_model->aliPay($order_insert['order_code'],$order_insert['pay_price'],'ResellerPay',4,['openid'=>$this->getUserInfo()['openid'],'uniacid'=>$this->_uniacid,'type' => 'ResellerPay' , 'out_trade_no' => $order_insert['order_code'],'order_id'=>(string)$order_id]);

                $arr['pay_list']  = $jsApiParameters;

                $arr['order_code']= $order_insert['order_code'];

                $arr['order_id']  = $order_id;

            }else{
                //微信支付
                $pay_controller = new \app\shop\controller\IndexWxPay($this->app);
                //支付
                $jsApiParameters= $pay_controller->createWeixinPay($this->payConfig(),$this->getUserInfo()['openid'],$this->_uniacid,"reseller",['type' => 'ResellerPay' , 'out_trade_no' => $order_insert['order_code'],'order_id'=>(string)$order_id],$order_insert['pay_price']);

                $arr['pay_list']= $jsApiParameters;

                $arr['order_id']= $order_id;
            }

            return $this->success($arr);
        }

        return $this->success($res);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-01-08 15:17
     * @功能说明:申请分销重新支付
     */
    public function reApplyResellerOrder(){

        $input = $this->_input;

        $order_model = new \app\payreseller\model\Order();

        $order_insert = $order_model->dataInfo(['id'=>$input['id']]);

        $order_id = $input['id'];

        if ($order_insert['pay_model']==3){

            $pay_model = new PayModel($this->payConfig());

            $jsApiParameters  = $pay_model->aliPay($order_insert['order_code'],$order_insert['pay_price'],'ResellerPay',4,['openid'=>$this->getUserInfo()['openid'],'uniacid'=>$this->_uniacid,'type' => 'ResellerPay' , 'out_trade_no' => $order_insert['order_code'],'order_id'=>(string)$order_id]);

            $arr['pay_list']  = $jsApiParameters;

            $arr['order_code']= $order_insert['order_code'];

            $arr['order_id']  = $order_id;

        }else{
            //微信支付
            $pay_controller = new \app\shop\controller\IndexWxPay($this->app);
            //支付
            $jsApiParameters= $pay_controller->createWeixinPay($this->payConfig(),$this->getUserInfo()['openid'],$this->_uniacid,"reseller",['type' => 'ResellerPay' , 'out_trade_no' => $order_insert['order_code'],'order_id'=>(string)$order_id],$order_insert['pay_price']);

            $arr['pay_list']= $jsApiParameters;

            $arr['order_id']= $order_id;
        }

        return $this->success($arr);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-01-08 14:12
     * @功能说明:获取付费分销员价格
     */
    public function getPayResellerData(){

        $config = getConfigSettingArr($this->_uniacid,['reseller_threshold','level_reseller_threshold']);

        return $this->success($config);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-23 09:39
     * @功能说明:分销商详情
     */
    public function resellerInfo(){

        $cap_dis[] = ['user_id','=',$this->getUserId()];

        $cap_dis[] = ['status','in',[1,2,3,4]];

        $distribution_model = new DistributionList();

        $cap_info = $distribution_model->dataInfo($cap_dis);

        return $this->success($cap_info);

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

        $dis[] = ['user_id','=',$this->getUserId()];

        $find = $distribution_model->dataInfo($dis);

        if(!empty($find)&&in_array($find['status'],[1,2,3])){

            $this->errorMsg('你已经申请');

        }
        //如果是业务员邀请需要绑定业务员以及业务员的代理商
        if(!empty($input['salesman_id'])){

            $salesman_model = new Salesman();

            $salesman = $salesman_model->dataInfo(['id'=>$input['salesman_id'],'status'=>2]);

            if(!empty($salesman)){

                $salesman_id = $salesman['id'];

                $admin_id    = $salesman['admin_id'];

                $inv_channel_balance = $salesman['inv_channel_balance'];
            }
        }

        if(!empty($input['admin_id'])){

            $admin_model = new \app\massage\model\Admin();

            $admin = $admin_model->dataInfo(['id'=>$input['admin_id'],'status'=>1]);

            if(!empty($admin)){

                $admin_id = $input['admin_id'];
            }
        }

        $insert = [

            'uniacid'  => $this->_uniacid,

            'user_id'  => $this->getUserId(),

            'user_name'=> $input['user_name'],

            'true_user_name' => !empty($input['true_user_name'])?$input['true_user_name']:$input['user_name'],

            'mobile'   => $input['mobile'],

            'cate_id'  => $input['cate_id'],

            'text'     => !empty($input['text'])?$input['text']:'',

            'status'   => 1,

            'salesman_id'=> !empty($salesman_id)?$salesman_id:0,

            'admin_id'   => !empty($admin_id)?$admin_id:0,

            'balance'    => isset($inv_channel_balance)?$inv_channel_balance:-1
        ];

        Db::startTrans();

        if(!empty($find)&&$find['status']==4){

            $res = $distribution_model->dataUpdate(['id'=>$find['id']],$insert);

            $channel_id = $find['id'];

        }else{

            $res = $distribution_model->dataAdd($insert);

            $channel_id = $distribution_model->getLastInsID();

            ChannelWater::initWater($this->_uniacid,$channel_id);
        }
        //渠道码
        if(!empty($input['channel_qr_id'])){

            $qr_model = new ChannelQr();

            $qr = $qr_model->dataInfo(['id'=>$input['channel_qr_id'],'status'=>1]);

            if(!empty($qr)){

                if(!empty($qr['channel_id'])){

                    $channel = $distribution_model->where(['id'=>$qr['channel_id']])->where('status','>',-1)->find();

                    if(!empty($channel)){

                        $this->errorMsg('该码已经绑定渠道商');
                    }

                    $qr_model->dataUpdate(['id'=>$input['channel_qr_id']],['channel_id'=>0]);
                }

                $update = [

                    'salesman_id' => $qr['salesman_id'],

                    'admin_id'    => $qr['admin_id'],

                    'sh_time'     => time(),

                    'status'      => 2,

                    'is_qr'       => 1
                ];

                $distribution_model->dataUpdate(['id'=>$channel_id],$update);
                //渠道码绑定渠道商
                $res = $qr_model->dataUpdate(['id'=>$qr['id'],'channel_id'=>0],['channel_id'=>$channel_id]);

               if($res==0){

                   Db::rollback();

                   $this->errorMsg('该码已经绑定渠道商');
               }

            }else{

                Db::rollback();

                $this->errorMsg('该码已经绑定渠道商');
            }
        }

        Db::commit();

        return $this->success($res);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-23 09:39
     * @功能说明:渠道商详情
     */
    public function channelInfo(){

        if(empty($this->getUserId())){

            return $this->success([]);

        }

        $cap_dis[] = ['user_id','=',$this->getUserId()];

        $cap_dis[] = ['status','in',[1,2,3,4]];

        $distribution_model = new ChannelList();

        $cap_info = $distribution_model->where($cap_dis)->order('id desc')->find();

        return $this->success($cap_info);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-08-30 14:57
     * @功能说明:渠道商分类下拉框
     */
    public function channelCateSelect(){

        $cate_model = new ChannelCate();

        $dis = [

            'uniacid' => $this->_uniacid,

            'status'  => 1
        ];

        $data = $cate_model->where($dis)->select()->toArray();

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-03-14 11:55
     * @功能说明:发送验证码
     */
    public function sendShortMsg(){

        $input = $this->_input;
        //验证码验证
        $config = new ShortCodeConfig();

        $dis = [

            'uniacid' =>$this->_uniacid,

            'phone'   => $input['phone']
        ];

        $find = $this->model->dataInfo($dis);

        if(!empty($find)){

           // $this->errorMsg('该手机号已经被绑定');
        }

        $res = $config->sendSmsCode($input['phone'],$this->_uniacid);

        if(!empty($res['Message'])&&$res['Message']=='OK'){

            return $this->success(1);

        }else{

            return $this->error($res['Message']);

        }
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-08-26 10:29
     * @功能说明:判断用户手机号
     */
    public function bindUserPhone(){

        $input = $this->_input;

        $dis = [

            'uniacid' =>$this->_uniacid,

            'phone'   => $input['phone']
        ];

        $find = $this->model->dataInfo($dis);

//        if(!empty($find)){
//
//           // $this->errorMsg('该手机号已经被绑定');
//        }

        $short_code = getCache($input['phone'],$this->_uniacid);
        //验证码验证手机号
        if($input['short_code']!=$short_code){

            return $this->error('验证码错误');

        }
        if(!empty($find)){
            //解除绑定
             $this->model->dataUpdate($dis,['phone'=>'']);
        }

        $res = $this->model->dataUpdate(['id'=>$this->getUserId()],$dis);

        $user = $this->getUserInfo();

        $user['phone'] = $input['phone'];

        $key = 'longbing_user_autograph_' . $user['id'];

        $key = md5($key);

        setCache($key, $user, 7200);

        return $this->success($res);

    }

    /**
     * 添加反馈
     * @return \think\Response
     */
    public function addFeedback()
    {
        $input = $this->request->only(['type_name', 'order_code', 'content', 'images', 'video_url']);
        $rule = [
            'type_name' => 'require',
            'content' => 'require',
        ];
        $validate = \think\facade\Validate::rule($rule);
        if (!$validate->check($input)) {
            return $this->error($validate->getError());
        }
        $input['coach_id'] = $this->getUserId();
        $input['uniacid'] = $this->_uniacid;
        if (!empty($input['images'])) {
            $input['images'] = json_encode($input['images']);
        }
        $input['create_time'] = time();

        $cap_dis[] = ['user_id', '=', $this->getUserId()];

        $cap_dis[] = ['status', 'in', [2]];

        $coach_model = new Coach();

        $coach_info = $coach_model->dataInfo($cap_dis);

        if(!empty($coach_info)){

            $input['type'] = 2;

            $input['true_coach_id'] = $coach_info['id'];
        }

        $res = Feedback::insert($input);

        if ($res) {

            return $this->success('');
        }

        return $this->error('提交失败');
    }

    /**
     * 反馈列表
     * @return \think\Response
     */
    public function listFeedback()
    {
        $input = $this->request->param();
        $limit = $this->request->param('limit',10);
        $where = [];
        if (isset($input['status']) && in_array($input['status'], [1, 2])) {
            $where[] = ['a.status', '=', $input['status']];
        }
        $where[] = ['a.coach_id', '=', $this->getUserId()];
        $where[] = ['a.uniacid', '=', $this->_uniacid];
        $data = Feedback::getList($where,$limit);
        $data['wait'] = Feedback::where(['coach_id' => $this->getUserId(), 'uniacid' => $this->_uniacid, 'status' => 1])->count();
        return $this->success($data);
    }

    /**
     * 详情
     * @return \think\Response
     */
    public function feedbackInfo()
    {
        $id = $this->request->param('id');
        if (empty($id)) {
            return $this->error('参数错误');
        }
        $data = Feedback::getInfo(['a.id' => $id]);
        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-10-18 15:31
     * @功能说明:
     */
    public function delUserInfo(){

        if(empty($this->getUserId())){

            $this->errorMsg('请先登录');
        }

        $order_model = new Order();

        $order = $order_model->where(['user_id'=>$this->getUserId()])->where('pay_type','not in',[-1,7])->find();

        if(!empty($order)){

            $this->errorMsg('你还有订单未完成');
        }

        $cap_dis[] = ['user_id','=',$this->getUserId()];

        $cap_dis[] = ['status','>',-1];

        $cap_info = $this->coach_model->dataInfo($cap_dis);

        if(!empty($cap_info)){

            $this->errorMsg('技师不能注销');
        }

        $channel_model = new ChannelList();

        $channel = $channel_model->dataInfo($cap_dis);

        if(!empty($channel)){

            $this->errorMsg('渠道商不能注销');
        }

        $salesman_model = new Salesman();

        $salesman = $salesman_model->dataInfo($cap_dis);

        if(!empty($salesman)){

            $this->errorMsg('业务员不能注销');
        }

        $admin_model = new \app\massage\model\Admin();

        $admin = $admin_model->dataInfo($cap_dis);

        if(!empty($admin)){

            $this->errorMsg('代理商不能注销');
        }

        $reseller_model = new DistributionList();

        $reseller = $reseller_model->dataInfo($cap_dis);

        if(!empty($reseller)){

            $this->errorMsg('分销员不能注销');
        }

        Db::startTrans();

        $user_info = $this->model->dataInfo(['id'=>$this->_user['id']]);

        $open_id = $this->getUserInfo()['openid'].time();

        $res = $this->model->dataUpdate(['id'=>$this->getUserId()],['status'=>-1,'openid'=>$open_id]);

        if($res==0){

            Db::rollback();

            $this->errorMsg('注销失败');
        }

        $dis = [

            'openid' => $user_info['web_openid']
        ];

        if(!empty($user_info['unionid'])){

            $dis['unionid'] = $user_info['unionid'];
        }

        $bind_model = new QrBind();

        $bind_model->whereOr($dis)->delete();

        $where = [

            'open_id' => $user_info['web_openid']
        ];

        if(!empty($user_info['unionid'])){

            $where['unionid'] = $user_info['unionid'];
        }

        $channel_model = new UserChannel();

        $channel_model->whereOr($where)->delete();

        if(!empty($user_info['web_openid'])){

            $scan_model = new ChannelScanQr();

            $scan_model->where(['open_id'=>$user_info['web_openid'],'user_id'=>0])->update(['user_id'=>$user_info['id']]);
        }

        Db::commit();

        setCache($this->autograph,'',0);

        return $this->success(true);
    }




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-01-30 16:03
     * @功能说明:屏蔽技师和屏蔽技师动态 type1动态 2技师
     */
    public function shieldCoachAdd(){

        $input = $this->_input;

        $dis = [

            'coach_id' => $input['coach_id'],

            'user_id'  => $this->_user_id,

            'type'     => $input['type'],

            'uniacid'  => $this->_uniacid
        ];

        $shield_model = new ShieldList();
        //没屏蔽过再屏蔽
        $find = $shield_model->dataInfo($dis);

        if(empty($find)){

            $shield_model->dataAdd($dis);

        }

        return $this->success(true);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-01-30 16:03
     * @功能说明:解除技师屏蔽
     */
    public function shieldCoachDel(){

        $input = $this->_input;

        $dis = [

            'id' => $input['id'],
        ];

        $shield_model = new ShieldList();

        $res = $shield_model->where($dis)->delete();

        return $this->success($res);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-01-30 16:03
     * @功能说明:解除技师屏蔽
     */
    public function shieldCoachList(){

        $input = $this->_param;

        $dis = [

            'a.user_id' => $this->_user_id,

            'a.type'    => $input['type']
        ];

        $shield_model = new ShieldList();

        $res = $shield_model->dataList($dis);

        return $this->success($res);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-02-24 11:31
     * @功能说明:绑定支付宝账号
     */
    public function bindAlipayNumber(){

        $input = $this->_input;



        $dis = [

            'id' => $this->_user_id
        ];

        $res = $this->model->dataUpdate($dis,['alipay_number'=>$input['alipay_number'],'alipay_name'=>$input['alipay_name']]);

        return $this->success($res);

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-12-09 15:18
     * @功能说明:技师获取客户虚拟电话
     */
    public function getVirtualPhone(){

        $input = $this->_param;

        $phone = !empty($input['phone'])?$input['phone']:0;

        $order_model = new Order();

        $order = $order_model->dataInfo(['id'=>$input['order_id']]);

        if(in_array($order['pay_type'],[-1,7])){

            return $this->error('接单已结束');
        }

        $called = new \app\virtual\model\Config();

        $res = $called->getVirtual($order,2,$phone);

        return $this->success($res);
    }






    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-03 10:30
     * @功能说明:获取门店下拉框
     */
    public function getStoreSelect(){

        $input = $this->_param;

        $store_model = new StoreList();

        $dis = [

            'uniacid'=> $this->_uniacid,

            'status' => 1
        ];

        if(!empty($input['admin_id'])){

            $dis['admin_id'] = $input['admin_id'];
        }

        $data = $store_model->where($dis)->field('id,title')->select()->toArray();

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-21 10:33
     * @功能说明:申请业务员
     */
    public function applySalesman(){

        $input = $this->_input;

        $salesman_model = new Salesman();

        $dis[] = ['user_id','=',$this->getUserId()];

        $dis[] = ['status','>',-1];

        $info = $salesman_model->dataInfo($dis);

        if(!empty($info)&&in_array($info['status'],[1,2,3])){

            $this->errorMsg('你已经申请过分销员了');
        }
        //是否开启审核
      //  if(getConfigSetting($this->_uniacid,'salesman_check_status')==1){

            $status = 1;

            $sh_time = 0;
//        }else{
//
//            $status = 2;
//
//            $sh_time = time();
//
//        }

        $insert = [

            'uniacid' => $this->_uniacid,

            'user_id' => $this->getUserId(),

            'admin_id'=> $input['admin_id'],

            'phone'   => $input['phone'],

            'user_name'=> $input['user_name'],

            'true_user_name' => !empty($input['true_user_name'])?$input['true_user_name']:$input['user_name'],

            'status'   => $status,

            'sh_time'  => $sh_time,
        ];

        $res = $salesman_model->dataAdd($insert);

        $id  = $salesman_model->getLastInsID();

        SalesmanWater::initWater($this->_uniacid,$id);

        return $this->success($res);

    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-21 11:30
     * @功能说明:业务员详情
     */
    public function salesmanInfo(){

        $input = $this->_param;

        $salesman_model = new Salesman();

        $admin_model = new \app\massage\model\Admin();

        $cap_dis[] = ['user_id','=',$this->getUserId()];

        $cap_dis[] = ['status','in',[1,2,3,4]];

        $data = $salesman_model->where($cap_dis)->order('id desc')->find();

        if(!empty($data)){

            $data = $data->toArray();

            $data['admin_name'] = $admin_model->where(['id'=>$data['admin_id'],'status'=>1])->value('agent_name');
        }

        return $this->success($data);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-09 15:28
     * @功能说明:申请代理商合伙人
     */
    public function agentApply(){

        $input = $this->_input;

        $agent_model = new AgentApply();

        $input['uniacid'] = $this->_uniacid;

        $input['user_id'] = $this->_user['id'];

        if(!empty($input['short_code'])){

            $short_code = getCache($input['phone'],$this->_uniacid);
            //验证码验证手机号
            if($input['short_code']!=$short_code){

                return $this->error('验证码错误');
            }

            unset($input['short_code']);

            setCache($input['phone'],'',99,$this->_uniacid);

        }

        $res = $agent_model->dataAdd($input);

        return $this->success($res);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-29 18:28
     * @功能说明:绑定渠道商
     */
    public function bindChannel(){

        $input = $this->_input;

        $channel_model = new UserChannel();

        $user_channel_over_time = getConfigSetting($this->_uniacid,'user_channel_over_time');
        //是否设置的永久绑定
        $auth = AdminMenu::getAuthList((int)$this->_uniacid,['channelforeverbind']);

        $channel_bind_type = getConfigSetting((int)$this->_uniacid,'channel_bind_type');

        $channel_bind_forever = $auth['channelforeverbind'];

        $dis = [

            'user_id' => $this->_user['id'],

            'open_id' => $this->_user['openid']
        ];

        if($channel_bind_type==1){
            //永久绑定
            if($channel_bind_forever==true){

                $find = $channel_model->whereOr($dis)->find();

                $time = strtotime(date('Y-m-d',time()));
                //只有当日注册的用户可以扫
                if($this->_user['create_time']<$time){

                    $find = 1;
                }

            }else{

                $res = $channel_model->whereOr($dis)->delete();
            }
        }else{
            //时效性内不能换绑
            if($channel_bind_forever==true){

                $find = $channel_model->whereOr($dis)->find();
            }else{

                $find = $channel_model->where('over_time','>',time())->where(function ($query) use ($dis){
                    $query->whereOr($dis);
                })->find();

                if(empty($find)){

                    $channel_model->whereOr($dis)->delete();
                }
            }
        }
        if(!empty($input['channel_id'])){

            $channels_model = new ChannelList();

            $channel = $channels_model->where(['id'=>$input['channel_id']])->where('status','in',[2,3])->find();

            if(!empty($channel['time_type'])&&$channel['time_type']==1){

                $user_channel_over_time = $channel['time'];
            }
        }

        if(empty($find)){

            $insert = [

                'uniacid' => $this->_uniacid,

                'user_id' => $this->_user['id'],

                'channel_id'=> $input['channel_id'],

                'over_time' => time()+$user_channel_over_time*3600
            ];

            $res = $channel_model->dataAdd($insert);

            return $this->success($res);
        }
        return $this->success(true);
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-09-20 10:44
     * @功能说明:随机获取企业微信客服
     */
    public function getWecomStaff(){

        $config = longbingGetAppConfig($this->_uniacid,true);

        if(!empty($config['wecom_staff'])){

            $wecom_staff = unserialize($config['wecom_staff']);

            if(!empty($wecom_staff)){

                $key = array_rand($wecom_staff);

                $data = $wecom_staff[$key];

                return $this->success($data);
            }
        }

        return $this->success('');
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-11-21 16:20
     * @功能说明:添加扫码记录
     */
    public function addScanRecord(){

        $input = $this->_input;

        $scan_model = new ChannelScanQr();

        $insert = [

            'uniacid' => $this->_uniacid,

            'type'    => $input['type'],

            'qr_id'   => $input['qr_id'],

            'is_qr'   => isset($input['is_qr'])?$input['is_qr']:1,

            'admin_id'=> !empty($input['admin_id'])?$input['admin_id']:0
        ];

        if(!empty($this->getUserInfo())){

            $insert['user_id'] = $this->_user['id'];

            $insert['open_id'] = $this->_user['openid'];
        }
//        //type 1渠道码 2分销码 3经纪人邀请技师码 4代理商邀请技师 5代理商邀请业务员 6代理商邀请渠道商 7技师邀请充值 8业务员邀请渠道商 9渠道商默认码 10分销员邀请分销员 11代理商邀请分销员
//        if($input['type']==1){
//
//            $auth = getPromotionRoleAuth(2,$this->_uniacid);
//
//            if($auth==1){
//
//                $channel_qr_model = new ChannelQr();
//
//                $find = $channel_qr_model->where(['id'=>$input['qr_id'],'status'=>1])->find();
//            }
//
//        }elseif ($input['type']==2){
//
//            $auth = getPromotionRoleAuth(1,$this->_uniacid);
//
//            if($auth==1){
//
//                if(getFxStatus($this->_uniacid)==1){
//
//                    $reseller_model = new DistributionList();
//
//                    $find = $reseller_model->where(['user_id'=>$input['qr_id']])->where('status','in',[2,3])->find();
//
//                }else{
//
//                    $find = 1;
//                }
//            }
//        }elseif ($input['type']==3){
//
//            $auth = getPromotionRoleAuth(3,$this->_uniacid);
//
//            if($auth==1){
//
//                $broker_model = new CoachBroker();
//
//                $find = $broker_model->where(['user_id'=>$input['qr_id']])->where('status','in',[2,3])->find();
//            }
//        }elseif (in_array($input['type'],[4,5,6,11])){
//
//            $admin_model = new \app\massage\model\Admin();
//
//            $find = $admin_model->where(['id'=>$input['qr_id']])->where('status','in',[1])->find();
//        }elseif ($input['type']==7){
//
//            $coach_model = new Coach();
//
//            $find = $coach_model->where(['id'=>$input['qr_id']])->where('status','in',[2,3])->find();
//
//        }elseif ($input['type']==8){
//
//            $auth = getPromotionRoleAuth(4,$this->_uniacid);
//
//            if($auth==1){
//
//                $salesman_model = new Salesman();
//
//                $find = $salesman_model->where(['id'=>$input['qr_id']])->where('status','in',[2,3])->find();
//            }
//        }elseif ($input['type']==9){
//
//            $auth = getPromotionRoleAuth(2,$this->_uniacid);
//
//            if($auth==1){
//
//                $channel_model = new ChannelList();
//
//                $find = $channel_model->where(['id'=>$input['qr_id']])->where('status','in',[2,3])->find();
//            }
//        }elseif ($input['type']==10){
//
//            $auth = getPromotionRoleAuth(1,$this->_uniacid);
//
//            if($auth==1){
//
//                $reseller_model = new DistributionList();
//
//                $find = $reseller_model->where(['id'=>$input['qr_id']])->where('status','in',[2,3])->find();
//            }
//        }
//
//        if(empty($find)){
//
//            $insert['wechat_type'] = $insert['type'];
//
//            $insert['type'] = 0;
//        }

        $scan_model->dataAdd($insert);

        $id = $scan_model->getLastInsID();

        $id = !empty($this->getUserInfo())?0:$id;

        return $this->success($id);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-11-21 16:29
     * @功能说明:修改扫码记录
     */
    public function updateScanRecord(){

        $input = $this->_input;

        $scan_model = new ChannelScanQr();

        $is_register = !empty($input['is_register'])?$input['is_register']:0;

        $insert['user_id'] = $this->_user['id'];

        $insert['open_id'] = $this->_user['openid'];

        $insert['is_new']   = $is_register;
        //添加扫码记录 type 1渠道码 2分销码 3经纪人邀请技-师码 4代理商邀请技-师 5代理商邀请业务员 6代理商邀请渠道商 7技-师邀请充值 8业务员邀请渠道商 9渠道商默认码 10分销员邀请分销员 11代理商邀请分销员
        $res = $scan_model->dataUpdate(['id'=>$input['id']],$insert);

        if($is_register==1){

            $qr_find = $scan_model->dataInfo(['id'=>$input['id']]);

            if(!empty($qr_find)){

                $update = [

                    'source_type' => $qr_find['type'],

                    'is_qr' => $qr_find['is_qr'],

                    'admin_id' => !empty($qr_find['admin_id'])?$qr_find['admin_id']:$scan_model->getQrAdminId($qr_find)
                ];

                $this->model->dataUpdate(['id'=>$this->_user['id']],$update);
            }
        }

        return $this->success($res);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-12-21 18:04
     * @功能说明:申请经纪人
     */
    public function applyBroker(){

        $input = $this->_input;

        $broker_model = new CoachBroker();

        $dis[] = ['user_id','=',$this->getUserId()];

        $dis[] = ['status','>',-1];

        $info = $broker_model->dataInfo($dis);

        if(!empty($info)&&in_array($info['status'],[1,2,3])){

            $this->errorMsg('你已经申请过经纪人了');
        }

        $insert = [

            'uniacid'  => $this->_uniacid,

            'user_id'  => $this->getUserId(),

            'user_name'=> $input['user_name'],

            'true_user_name' => !empty($input['true_user_name'])?$input['true_user_name']:$input['user_name'],

            'mobile'   => $input['mobile'],

            'text'     => $input['text'],
        ];

        if(!empty($info)&&$info['status']==4){

            $insert['status'] = 1;

            $res = $broker_model->dataUpdate(['id'=>$info['id']],$insert);

        }else{

            $res = $broker_model->dataAdd($insert);

            $id = $broker_model->getLastInsID();

            BrokerWater::initWater($this->_uniacid,$id);
        }

        return $this->success($res);
    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-08-03 11:53
     * @功能说明:经纪人详情
     */
    public function brokerInfo(){

        $input = $this->_param;

        $dis[] = ['user_id','=',$this->getUserId()];

        $dis[] = ['status','in',[1,2,3,4]];

        $broker_model = new CoachBroker();

        $info = $broker_model->dataInfo($dis);

        if(!empty($info)){

            $user_model = new User();

            $info['nickName'] = $user_model->where(['id'=>$info['user_id']])->value('nickName');
        }

        return $this->success($info);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-01-29 13:41
     * @功能说明:
     */
    public function getAddressByIp(){

        return $this->success(getAddressByIp($this->_uniacid));
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-03-06 10:41
     * @功能说明:登录的时候获取自己的技师信息
     */
    public function getAccountCoach(){

        $coach_model = new Coach();

        $dis[] = ['status', 'in', [1,2, 3]];

        $dis[] = ['user_id', '=', $this->_user['id']];

        $info = $coach_model->where($dis)->field('id,coach_name,work_img')->find();

        if(!empty($info)){

            $account_model = new CoachAccount();
            //账号
            $info['account_info'] = $account_model->dataInfo(['coach_id'=>$info['id'],'status'=>1]);
        }

        return $this->success($info);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-03-20 15:11
     * @功能说明:
     */
    public function coachTypeSelect(){

        $input = $this->_param;

        $type_model = new CoachType();

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['status','=',1];

        $data = $type_model->where($dis)->order('top desc,id desc')->select()->toArray();

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-05-09 10:43
     * @功能说明:代理商列表
     */
    public function adminList(){

        $admin_model = new \app\massage\model\Admin();

        $dis = [

            'status'       => 1,

            'is_admin'     => 0,

            'sub_agent_auth'=> 1
        ];

        $input = $this->_param;

        $where = [];

        if(!empty($input['nickName'])){

            $where[] =['agent_name','like','%'.$input['nickName'].'%'];
        }

        $data = $admin_model->where($dis)->where($where)->field('agent_name,id,city_type,user_id')->paginate(10)->toArray();

        $user_model = new User();

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $v['avatarUrl'] = $user_model->where(['id'=>$v['user_id']])->value('avatarUrl');
            }
        }

        return $this->success($data);
    }


    /**
     * @author chenniang
     * @DataTime: 2024-06-06 16:41
     * @功能说明:是否是代理商
     */
    public function adminAuth(){

        $admin_model = new \app\massage\model\Admin();

        $admin_where = [

            'user_id' => $this->_user['id'],

            'status'  => 1
        ];

        $admin_user = $admin_model->dataInfo($admin_where);
        //是否是加盟商
        $data['is_admin'] = !empty($admin_user)?1:0;

        $data['phone'] = $this->model->where(['id'=>$this->_user['id']])->value('phone');

        $input = $this->_param;

        if(!empty($input['admin_id'])){

            $data['top_name'] = $admin_model->where(['id'=>$input['admin_id'],'status'=>1])->value('agent_name');
        }

        return $this->success($data);

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 13:28
     * @功能说明:类型列表
     */
    public function industryTypeSelect(){

        $model = new Type();

        $model->initData($this->_uniacid);

        $input = $this->_param;

        $dis[] = ['uniacid','=',$this->_uniacid];

        if(isset($input['status'])){

            $dis[] = ['status','=',$input['status']];
        }else{

            $dis[] = ['status','=',1];
        }

        if(!empty($input['title'])){

            $dis[] = ['title','like','%'.$input['title'].'%'];
        }

        if($this->is_app==0&&getConfigSetting($this->_uniacid,'shield_massage')==1){

            $dis[] = ['type','<>',1];
        }

        $data = $model->where($dis)->order('top desc,id desc')->select()->toArray();

        return $this->success($data);
    }

    /**
     * @Desc: 岗位标签列表
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @Auther: shurong
     * @Time: 2024/8/15 11:01
     */
    public function stationIconSelect()
    {

        $input = $this->_param;

        $model = new StationIcon();

        $dis[] = ['uniacid', '=', $this->_uniacid];

        $dis[] = ['status', '=', 1];

        if (!empty($input['industry_type'])) {

            $dis[] = ['industry_type', '=', $input['industry_type']];
        }

        $data = $model->where($dis)->order('id desc')->select()->toArray();

        return $this->success($data);
    }

    /**
     * @Desc: 个性标签列表
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @Auther: shurong
     * @Time: 2024/8/15 11:02
     */
    public function coachIconSelect(){

        $input = $this->_param;

        $model = new IconCoach();

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['status','=',1];

        $data = $model->where($dis)->order('id desc')->select()->toArray();

        return $this->success($data);
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-03-28 15:48
     * @功能说明:门店下拉框
     */
    public function storeList(){

        $input = $this->_param;

        $dis[] = ['uniacid','=',$this->_uniacid];

        $dis[] = ['status','=',1];

        $dis[] = ['auth_status','=',2];

        if(!empty($input['title'])){

            $dis[] = ['title','like','%'.$input['title'].'%'];
        }

        if(!empty($input['admin_id'])){

            $dis[] = ['admin_id','=',$input['admin_id']];
        }

        $store_model = new \app\store\model\StoreList();

        $data = $store_model->where($dis)->order('id desc')->paginate(10)->toArray();

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $v['work_status'] = $store_model->workStatus($v);
            }
        }

        return $this->success($data);
    }


    /**
     * @author chenniang
     * @DataTime: 2024-10-22 11:41
     * @功能说明:根据技师查询关联的门店
     */
    public function storeListByCoach(){

        $input = $this->_param;

        $dis[] = ['b.coach_id','=',$input['coach_id']];

        $dis[] = ['d.store_auth','=',1];

        $dis[] = ['a.status','=',1];

        if(!empty($input['title'])){

            $dis[] = ['a.title','like',"%".$input['title']."%"];
        }

        $data = StoreCoach::getStoreListOrderDistance($dis,$input['lng'],$input['lat']);

        $store_model = new \app\store\model\StoreList();

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $v['work_status'] = $store_model->workStatus($v);

                $v['distance'] = distance_text($v['distance']);
            }
        }
        return $this->success($data);
    }


    /**
     * @author chenniang
     * @DataTime: 2024-10-23 15:22
     * @功能说明:卡券详情
     */
    public function userCouponInfo(){

        $input = $this->_param;

        $coupon_record_model = new CouponRecord();

        $dis = [

            'id' => $input['id']
        ];

        $data = $coupon_record_model->dataInfo($dis);

        $data['end_time'] = date('Y.m.d H:i',$data['end_time']);

        $data['use_time'] = date('Y.m.d H:i',$data['use_time']);

        if($data['use_scene']==1){

            $store_model = new \app\store\model\StoreList();

            $dis = [

                'b.coupon_id' => $data['id'],

                'b.type' => 1
            ];

            $data['store'] =  $store_model->alias('a')
                ->join('massage_service_coupon_store b','b.store_id = a.id')
                ->where($dis)
                ->where('a.status','=',1)
                ->field('a.id,a.title,b.store_id,a.admin_id,a.cover,a.status')
                ->group('a.id')
                ->order('a.id desc')
                ->select()
                ->toArray();
        }

        if($data['status']==4){

            $data['cancel_name'] = \app\massage\model\Admin::where(['id'=>$data['hx_admin_id']])->value('agent_name');
        }

        if(!empty($data['hx_user_id'])){

            $data['hx_user_name'] = User::where(['id'=>$data['hx_user_id']])->value('nickName');
        }

        if(!empty($data['hx_store_id'])){

            $data['hx_store_name'] = \app\store\model\StoreList::where(['id'=>$data['hx_store_id']])->value('title');
        }

        return $this->success($data);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-08-30 14:18
     * @功能说明 卡券核销码
     */
    public function couponHxQr(){

        $input = $this->_param;

        $key = 'couponHxQr'.$input['id'].'-'.$this->is_app;

        $qr  = getCache($key,$this->_uniacid);

        $user_model = new User();

        if(empty($qr)){
            //小程序
            if($this->is_app==0){

                $input['page'] = 'agent/pages/shopstore/hx-coupon';

                $input['id'] = $input['id'];
                //获取二维码
                $qr = $user_model->orderQr($input,$this->_uniacid);

            }else{

                $page = 'https://'.$_SERVER['HTTP_HOST'].'/h5/#/agent/pages/shopstore/hx-coupon?id='.$input['id'];

                $qr = base64ToPng(getCode($this->_uniacid,$page));
            }

            setCache($key,$qr,86400,$this->_uniacid);
        }

        $qr = !empty($qr)?$qr:'https://'.$_SERVER['HTTP_HOST'].'/favicon.ico';

        return $this->success($qr);
    }

    /**
     * @Desc: 活动经费流水
     * @return mixed
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/31  11:05
     */
    public function getPartnerWater()
    {
        PartnerOrder::userBalance($this->_uniacid);

        $input = \request()->param();

        $data = PartnerOrder::getWater($this->_uniacid, $this->getUserId(), $input['limit'] ?? 10);

        return $this->success($data);
    }

    /**
     * @Desc: 活动经费详情
     * @return mixed
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/31 15:57
     */
    public function partnerMoneyInfo()
    {
        PartnerOrder::userBalance($this->_uniacid);

        $user_info = $this->model->dataInfo(['id' => $this->getUserId()]);

        $data = [

            'partner_wait_money' => PartnerOrder::getWaitPrice($this->_uniacid, $this->getUserId()),
            'partner_money' => $user_info['partner_money'],
            'total_partner_money' => $user_info['total_partner_money'],
        ];

        $wallet_model = new Wallet();
        //累计提现
        $data['extract_total_price'] = $wallet_model->userPartner($this->getUserId(), 2);
        //提现中
        $data['extract_ing_price'] = $wallet_model->userPartner($this->getUserId(), 1);
        //税点
        $data['tax_point'] = getConfigSetting($this->_uniacid, 'tax_point');

        return $this->success($data);
    }

    /**
     * @Desc: 活动经费提现
     * @return mixed
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/31 16:20
     */
    public function applyPartnerWallet(){

        $input = $this->_input;

        $user_info = $this->model->dataInfo(['id' => $this->getUserId()]);

        $new_cash = $user_info['partner_money'];

        if (empty($input['apply_price']) || $input['apply_price'] < 0.01) {

            $this->errorMsg('提现费最低一分');
        }
        //服务费
        if ($input['apply_price'] > $new_cash) {

            $this->errorMsg('余额不足');
        }

        //获取税点
        $tax_point = getConfigSetting($this->_uniacid, 'tax_point');

        $balance = 100 - $tax_point;

        $key = 'user_partner_wallet' . $this->getUserId();

        $value = getCache($key, $this->_uniacid);

        if (!empty($value)) {

            $this->errorMsg('网络错误，请刷新重试');

        }
        //加一个锁防止重复提交
        incCache($key, 1, $this->_uniacid);

        Db::startTrans();
        //减佣金
        $res = $this->model->dataUpdate(['id' => $this->getUserId(), 'lock' => $user_info['lock']], ['partner_money' => $user_info['partner_money'] - $input['apply_price'], 'lock' => $user_info['lock'] + 1]);

        if ($res != 1) {

            Db::rollback();
            //减掉
            delCache($key, $this->_uniacid);

            $this->errorMsg('申请失败');
        }

        $insert = [

            'uniacid'       => $this->_uniacid,

            'user_id'       => $this->getUserId(),

            'coach_id'      => 0,

            'admin_id'      => 0,

            'total_price'   => $input['apply_price'],

            'balance'       => $balance,

            'apply_price'   => round($input['apply_price']*$balance/100,2),

            'service_price' => round( $input['apply_price'] * $tax_point / 100, 2),

            'tax_point'     => $tax_point,

            'code'          => orderCode(),

            'text'          => $input['text'],

            'type'          => 12,

            'last_login_type' => $this->is_app,

            'apply_transfer' => !empty($input['apply_transfer'])?$input['apply_transfer']:0

        ];

        $wallet_model = new Wallet();
        //提交审核
        $res = $wallet_model->dataAdd($insert);

        if ($res != 1) {

            Db::rollback();
            //减掉
            delCache($key, $this->_uniacid);

            $this->errorMsg('申请失败');
        }

        Db::commit();
        //减掉
        delCache($key, $this->_uniacid);

        return $this->success($res);

    }

    /**
     * @Desc:活动经费提现记录
     * @return mixed
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/11/7 15:50
     */
    public function partnerWalletList()
    {

        $wallet_model = new Wallet();

        $input = $this->_param;

        $dis[] = ['user_id', '=', $this->getUserId()];

        if (!empty($input['status'])) {

            $dis[] = ['status', '=', $input['status']];

        }

        $dis[] = ['type', '=', 12];
        //提现记录
        $data = $wallet_model->dataList($dis, 10);

        if (!empty($data['data'])) {

            foreach ($data['data'] as &$v) {

                $v['create_time'] = date('Y-m-d H:i:s', $v['create_time']);
            }
        }
        //累计提现
        $data['extract_total_price'] = $wallet_model->userPartner($this->getUserId(), 2);
        //提现中
        $data['extract_ing_price'] = $wallet_model->userPartner($this->getUserId(), 1);

        return $this->success($data);
    }

}
