<?php


namespace app\massage\controller;


use app\adapay\model\Member;
use app\AdminRest;
use app\massage\model\ActionLog;
use app\massage\model\CashUpdateRecord;
use app\massage\model\ChannelScanQr;
use app\massage\model\Coach;
use app\massage\model\CoachLevel;
use app\massage\model\CoachTimeList;
use app\massage\model\Commission;
use app\massage\model\Config;
use app\massage\model\CouponRecord;
use app\massage\model\DistributionList;
use app\massage\model\Order;
use app\massage\model\Police;
use app\massage\model\RefundOrder;
use app\massage\model\ResellerRecommendCash;
use app\massage\model\SendMsgConfig;
use app\massage\model\ShopCarte;
use app\massage\model\ShopGoods;
use app\massage\model\ShortCodeConfig;
use app\massage\model\User;
use app\massage\model\Wallet;
use app\massage\model\WorkLog;
use Exception;
use longbingcore\heepay\HeePay;
use longbingcore\heepay\WeixinPay;

use longbingcore\wxcore\Adapay;
use longbingcore\wxcore\Winnerlook;
use think\Env;
use think\facade\Db;
use LongbingUpgrade;

use think\facade\Request;

class AdminShop extends AdminRest
{



    public function changeOpenid(){

        $user_model = new User();

        $list = $user_model->where(['uniacid'=>$this->_uniacid])->field('openid')->order('id desc')->paginate(100)->toArray();

        $token = $this->getGzhToken();

        $url = 'https://api.weixin.qq.com/cgi-bin/changeopenid?access_token='.$token;

        $openidArray = array_column($list['data'],'openid');

       // $openidArray = ['oaGb56SIiypTWFv_VqHWuzLC-TzY'];

        $data['from_appid']="wx23662c103a851f47";

        $data['openid_list']= $openidArray;

        $data = json_encode($data);

        $res = lbCurlPost($url,$data);

        $res = json_decode($res,true);

        if(!empty($res['result_list'])){

            foreach ($res['result_list'] as $v){

                if(isset($v['err_msg'])&&$v['err_msg']=='ok'){

                    $find = $user_model->where(['openid'=>$v['new_openid']])->find();

                    if(!empty($find)){

                        $user_model->where(['id'=>$find->id])->update(['openid'=>$v['new_openid'].'-1111']);
                    }

                    $user_model->dataUpdate(['openid'=>$v['ori_openid']],['openid'=>$v['new_openid'],'web_openid'=>$v['new_openid']]);
                }

            }
        }

        return $this->success($res);

    }






    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-02-26 17:19
     * @功能说明:
     */
    public function getGzhToken($update=0){

        $uniacid = $this->_uniacid;

        $appid   = 'wx77fbc7f427ce2d9a';

        $gzh_secret  = 'ff749988ef752969d6b411a43477a62a';

        $key         = 'articleToken-';

        $value = getCache($key, $uniacid);

        if ($value&&$update==0)
        {
            return $value;
        }

        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appid}&secret={$gzh_secret}";

        $js_token = file_get_contents($url);

        $js_token = json_decode($js_token, true);

        if (isset($js_token['access_token']))
        {
            $js_token = $js_token['access_token'];

            setCache($key, $js_token, 1200, $uniacid);

            return $js_token;
        }

        $str = '请求ac错误' . isset($js_token['errmsg']) ? $js_token['errmsg'] : '';
        echo $str;
        die;
    }

    /**
     * 添加分类
     * @return \think\Response
     */
    public function addCarte()
    {

        $input = $this->request->only(['name', 'sort']);
        $rule = [
            'name' => 'require',
            'sort' => 'require',
        ];
        $validate = \think\facade\Validate::rule($rule);
        if (!$validate->check($input)) {
            return $this->error($validate->getError());
        }
        $input['name'] = trim($input['name']);
        $where = [
            ['name', '=', $input['name']],
            ['status', 'in', [0, 1]],
            ['uniacid', '=', $this->_uniacid]
        ];
        $info = ShopCarte::getInfo($where);

        if (!empty($info)) {
            return $this->error('此分类已存在，不可创建');
        }
        $input['uniacid'] = $this->_uniacid;
        $res = ShopCarte::add($input);
        if ($res) {
            return $this->success('');
        }

        return $this->error('创建失败');
    }

    /**
     * 编辑分类
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function editCarte()
    {
        $input = $this->request->only(['id', 'name', 'sort']);
        if ($this->request->isPost()) {
            $rule = [
                'name' => 'require',
                'sort' => 'require',
            ];
            $validate = \think\facade\Validate::rule($rule);
            if (!$validate->check($input)) {
                return $this->error($validate->getError());
            }
            $input['name'] = trim($input['name']);
            $where = [
                ['name', '=', $input['name']],
                ['status', 'in', [0, 1]],
                ['uniacid', '=', $this->_uniacid],
                ['id', '<>', $input['id']]
            ];
            $info = ShopCarte::getInfo($where);
            if (!empty($info)) {
                return $this->error('此分类已存在，不可编辑');
            }
            $res = ShopCarte::update($input, ['id' => $input['id']]);
            if ($res === false) {
                return $this->error('编辑失败');
            }
            return $this->success('');
        }
        if (empty($input['id'])) {
            return $this->error('参数错误');
        }
        $info = $info = ShopCarte::getInfo(['id' => $input['id']]);
        return $this->success($info);
    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-10-26 17:06
     * @功能说明:获取技师上个半月可提现的金额
     */
    public function getCoachCashByHalfMonthV2($coach_id,$true_cash,$type=1,$date_type=1){

        if($date_type==1){

            $half = strtotime(date('Y-m-16'));

            if(time()>=$half){

                $time = strtotime(date('Y-m-01'));

            }else{

                $time = strtotime(date('Y-m-16',strtotime('-1 month')));
            }
        }else{

            $time = strtotime(date('Y-m-d'));

            $currentWeekDay = date('w', time());

            $time = $time - ($currentWeekDay - 1)*86400;

            $time -= 86400*7;
        }
        //前15天的
        // $time = 15*86400;

        // $time = 86400;

        $order_model = new Order();

        $dis[] = ['b.status','=',2];

        $dis[] = ['b.top_id','=',$coach_id];

        $dis[] = ['a.create_time','<=',$time];
        //服务费
        if($type==1){
          //  $dis[] = ['a.pay_type','=',7];

            $dis[] = ['b.type','in',[3,7,17,18,24,25]];
        }else{
            //车费
            $dis[] = ['b.type','in',[8]];
        }

        $cash  = $order_model->alias('a')
            ->join('massage_service_order_commission b','a.id = b.order_id')
            ->where($dis)
            ->group('b.id')
            ->sum('b.cash');

        $wallet_model = new Wallet();

        $where[] = ['coach_id','=',$coach_id];

        $where[] = ['status','in',[1,2,4,5]];

        if($type==1){

            $where[] = ['type','=',1];

        }else{
            $where[] = ['type','=',2];
        }

        $wallt_cash = $wallet_model->where($where)->sum('total_price');

        $update_model = new CashUpdateRecord();

        if($type==1){

            $add_update_cash = $update_model->where(['coach_id'=>$coach_id,'status'=>1,'is_add'=>1,'type'=>1])->where('create_time','<=',$time)->sum('cash');

            $del_update_cash = $update_model->where(['coach_id'=>$coach_id,'status'=>1,'is_add'=>0,'type'=>1])->where('create_time','<=',$time)->sum('cash');

            $coach_cash = $cash-$wallt_cash+$add_update_cash-$del_update_cash;
        }else{

            $coach_cash = $cash-$wallt_cash;
        }

        $coach_cash = $coach_cash>0?$coach_cash:0;

        $coach_cash = $coach_cash>$true_cash?$true_cash:$coach_cash;

        return round($coach_cash,2);
    }


    /**
     * @author chenniang
     * @DataTime: 2023-10-26 17:06
     * @功能说明:获取技师上个半月可提现的金额
     */
    public function getCoachCashByHalfMonth($coach_id,$true_cash,$type=1){
        //前15天的
        $time = 15*86400;

        $order_model = new Order();

        $dis[] = ['a.pay_type','=',7];

        $dis[] = ['b.status','=',2];

        $dis[] = ['b.top_id','=',$coach_id];

        $dis[] = ['a.create_time','<=',time()-$time];
        //服务费
        if($type==1){

            $dis[] = ['b.type','in',[3,7]];
        }else{
            //车费
            $dis[] = ['b.type','in',[8]];
        }

        $cash  = $order_model->alias('a')
            ->join('massage_service_order_commission b','a.id = b.order_id')
            ->where($dis)
            ->group('b.id')
            ->sum('b.cash');

        $wallet_model = new Wallet();

        $where[] = ['coach_id','=',$coach_id];

        $where[] = ['status','in',[1,2,4,5]];

        // $where[] = ['create_time','<=',time()-$time];

        if($type==1){

            $where[] = ['type','=',1];

        }else{
            $where[] = ['type','=',2];
        }

        $wallt_cash = $wallet_model->where($where)->sum('total_price');

        $update_model = new CashUpdateRecord();

        if($type==1){

            $add_update_cash = $update_model->where(['coach_id'=>$coach_id,'status'=>1,'is_add'=>1])->where('create_time','<=',time()-$time)->sum('cash');

            $del_update_cash = $update_model->where(['coach_id'=>$coach_id,'status'=>1,'is_add'=>0])->where('create_time','<=',time()-$time)->sum('cash');

            $coach_cash = $cash-$wallt_cash+$add_update_cash-$del_update_cash;
        }else{

            $coach_cash = $cash-$wallt_cash;
        }

        $coach_cash = $coach_cash>0?$coach_cash:0;

        $coach_cash = $coach_cash>$true_cash?$true_cash:$coach_cash;

        return round($coach_cash,2);
    }


public function publisher ( $messages, $delayTime = null )
    {


        $data = ['message' => $messages];

        $param = Request::param() ;
        $param['s'] =  "publics/HttpAsyn/message" ;
        $url = Request::baseFile(true);
        $url = $url . '?' . http_build_query($param);

        $url = 'https://am.22ud.com/index.php?page=1&limit=10&name=&s=publics%2FHttpAsyn%2Fmessage';

        if (is_array($data)) {
            $data = http_build_query($data, null, '&');
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('User-Agent: MyUserAgent/1.0'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result['response'] = curl_exec($ch);
        $result['httpCode'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
dump($url,$result);exit;
        $res = asyncCurl($url,  ['message' => $messages] );

        return $res;

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-07-08 09:58
     * @功能说明:获取技师等级
     */
    public function getCoachLevel($caoch_id, $uniacid){

        $config_model = new Config();

        $level_model  = new CoachLevel();

        $config = $config_model->dataInfo(['uniacid'=>$uniacid]);

        $level_cycle = $config['level_cycle'];

        $is_current = $config['is_current'];
        //时长(分钟)
        $time_long = $level_model->getMinTimeLong($caoch_id,$level_cycle,$is_current);
        //最低业绩
        $price     = $level_model->getMinPrice($caoch_id,$level_cycle,0,$is_current);
        //加钟订单
        $add_price = $level_model->getMinPrice($caoch_id,$level_cycle,1,$is_current);
        //积分
        $integral  = $level_model->getMinIntegral($caoch_id,$level_cycle,$is_current);
        //在线时长
        $online_time = $level_model->getCoachOnline($caoch_id,$level_cycle,$is_current);

        $level       = $level_model->where(['uniacid' => $uniacid, 'status' => 1])->order('time_long,id desc')->select()->toArray();


        $log_model = new WorkLog();


        $coach_level = [];

        $add_balance = $price>0?$add_price/$price*100:0;

        $int_integral = $integral;

        if (!empty($level)) {

            foreach ($level as $key=>$value) {

                $integral = $int_integral;
                //时长
                $level_time_long = $key>0?$level[$key-1]['time_long']:0;
                //在线时长兑换积分
                if($value['online_change_integral_status']==1){

                    $more_online_time = floor($online_time - $value['online_time']);

                    if($more_online_time>0){

                        $change_integral = $more_online_time*$value['online_change_integral'];

                        $integral+= $change_integral;
                    }
                }

                if($time_long>=$level_time_long&&$price>=$value['price']&&$add_balance>=$value['add_balance']&&$integral>=$value['integral']&&$online_time>=$value['online_time']){

                    $coach_level = $value;

                }elseif (empty($coach_level)) {
                    //都不符合给一个最低都等级
                    $coach_level = $value;
                }
            }
        }

        return !empty($coach_level)?$coach_level : [];
    }


    /**
     * @param bool $debug
     *
     * @return $this
     */
    /**
     * @param bool $debug
     *
     * @return $this
     */
    function decrypt($data, $key) {

        list($encryptedData, $iv) = explode('::', base64_decode($data), 2);

        return openssl_decrypt($encryptedData, 'aes-256-cbc', $key, 0, $iv);
    }

    /**
     * 分类列表
     * @return \think\Response
     * @throws \think\db\exception\DbException
     */
    public function carteList(){



//        $a  = new SendMsgConfig();
//
//        $a->couponOverNotice(666);


        $input = $this->_param;

        $limit = $this->request->param('limit', 10);

        $where = [

            ['uniacid', '=', $this->_uniacid],

            ['status', '<>', '-1']
        ];

        if(!empty($input['name'])){

            $where[] = ['name','like','%'.$input['name'].'%'];
        }

        $data = ShopCarte::getList($where, $limit);

        return $this->success($data);
    }





    /**
     * @param $coach_id
     * @功能说明:校验技师服务费
     * @author chenniang(龙兵科技)
     * @DataTime: 2024-01-22 18:49
     */
    public function checkServicePrice($coach_id){

        $comm_model   = new Commission();

        $wallet_model = new Wallet();

        $coach_model  = new Coach();

        $record_model = new CashUpdateRecord();

        $arr['cash']  = $comm_model->where(['top_id'=>$coach_id,'status'=>2])->where('type','in',[3,7])->sum('cash');

        $arr['wallet_cash'] = $wallet_model->where(['coach_id'=>$coach_id,'type'=>1])->where('status','in',[1,2,4,5])->sum('total_price');

        $arr['coach_cash'] = $coach_model->where(['id'=>$coach_id])->sum('service_price');

        $arr['update_inc_cash'] = $record_model->where(['coach_id'=>$coach_id,'status'=>1,'type'=>1,'is_add'=>1])->sum('cash');

        $arr['update_del_cash'] = $record_model->where(['coach_id'=>$coach_id,'status'=>1,'type'=>1,'is_add'=>0])->sum('cash');

        $arr['have_cash'] = $arr['cash']-$arr['wallet_cash']-$arr['coach_cash']+$arr['update_inc_cash']-$arr['update_del_cash'];

        return $arr;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-11-04 15:50
     * @功能说明:获取技师在线时长
     */
    public function getCoachOnline($caoch_id,$level_cycle,$type=1){

        $log_model = new WorkLog();
        //初始化每一天的工作时间
        $log_model->updateTimeOnline($caoch_id,1);

        $dis = [

            'coach_id' => $caoch_id,
        ];
        //每周
        if($level_cycle==1){

            $week = $type==1?'week':'last week';

            $time_long = $log_model->where($dis)->whereTime('create_time',$week)->sum('time');
            //每月
        }elseif ($level_cycle==2){

            $month = $type==1?'month':'last month';

            $time_long = $log_model->where($dis)->whereTime('create_time',$month)->sum('time');
            //每季度
        }elseif ($level_cycle==3){

            $quarter = $type==1 ? ceil((date('n'))/3) : ceil((date('n'))/3)-1;//获取当前季度

            $start_quarter = mktime(0, 0, 0,$quarter*3-2,1,date('Y'));

            $end_quarter   = mktime(0, 0, 0,$quarter*3+1,1,date('Y'));

            $time_long = $log_model->where($dis)->where('create_time','between',"$start_quarter,$end_quarter")->sum('time');
            //每年
        }elseif ($level_cycle==4){

            $year = $type==1?'year':'last year';

            $time_long = $log_model->where($dis)->whereTime('create_time',$year)->sum('time');

        }elseif ($level_cycle==5){

            $day = date('d',time());
            //本期
            if($type==1){
                //下半月
                if($day>15){

                    $start_time = strtotime(date ('Y-m-16', time()));

                    $end_time   = strtotime(date('Y-m-t', time()))+86399;

                }else{

                    $start_time = strtotime(date ('Y-m-01', time()));

                    $end_time   = strtotime(date('Y-m-16', time()))-1;
                }

            }else{
                //下半月
                if($day>15){

                    $start_time = strtotime(date ('Y-m-01', time()));

                    $end_time   = strtotime(date('Y-m-16', time()))-1;

                }else{

                    $start_time = strtotime(date ('Y-m-16', strtotime('-1 month')));

                    $end_time   = strtotime(date('Y-m-t', strtotime('-1 month')))+86399;

                }

            }

            $time_long = $log_model->where($dis)->where('create_time','between',"$start_time,$end_time")->sum('time');
        }else{
            //不限
            $time_long = $log_model->where($dis)->sum('time');
        }
        $coach_time_model = new CoachTimeList();
        //休息时间
        $rest_time_long = $this->getCoachRestTimeLong($caoch_id,$level_cycle,$type);
        //4772280.0
        $time_long =  ($time_long-$rest_time_long)>0?$time_long-$rest_time_long:0;

        return floor($time_long/3600);

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2022-11-04 15:50
     * @功能说明:获取技师休息时长
     */
    public function getCoachRestTimeLong($caoch_id,$level_cycle,$type=1){

        $coach_time_model = new CoachTimeList();

        $dis = [

            'coach_id' => $caoch_id,

            'status'   => 0,

            'is_click' => 1,

            'is_work'  => 1
        ];
        //每周
        if($level_cycle==1){

            $week = $type==1?'week':'last week';

            $price = $coach_time_model->where($dis)->where('time_str','<',time())->whereTime('time_str',$week)->field('SUM(time_str_end-time_str) as time_long')->find();
            //每月
        }elseif ($level_cycle==2){

            $month = $type==1?'month':'last month';

            $price = $coach_time_model->where($dis)->where('time_str','<',time())->whereTime('time_str',$month)->field('SUM(time_str_end-time_str) as time_long')->find();
            //每季度
        }elseif ($level_cycle==3){

            $quarter = $type==1 ? ceil((date('n'))/3) : ceil((date('n'))/3)-1;//获取当前季度

            $start_quarter = mktime(0, 0, 0,$quarter*3-2,1,date('Y'));

            $end_quarter   = mktime(0, 0, 0,$quarter*3+1,1,date('Y'));

            $price = $coach_time_model->where($dis)->where('time_str','<',time())->where('time_str','between',"$start_quarter,$end_quarter")->field('SUM(time_str_end-time_str) as time_long')->find();
            //每年
        }elseif ($level_cycle==4){

            $year = $type==1?'year':'last year';

            $price = $coach_time_model->where($dis)->where('time_str','<',time())->whereTime('time_str',$year)->field('SUM(time_str_end-time_str) as time_long')->find();

        }elseif ($level_cycle==5){

            $day = date('d',time());
            //本期
            if($type==1){
                //下半月
                if($day>15){

                    $start_time = strtotime(date ('Y-m-16', time()));

                    $end_time   = strtotime(date('Y-m-t', time()))+86399;

                }else{

                    $start_time = strtotime(date ('Y-m-01', time()));

                    $end_time   = strtotime(date('Y-m-16', time()))-1;
                }

            }else{
                //下半月
                if($day>15){

                    $start_time = strtotime(date ('Y-m-01', time()));

                    $end_time   = strtotime(date('Y-m-16', time()))-1;

                }else{

                    $start_time = strtotime(date ('Y-m-16', strtotime('-1 month')));

                    $end_time   = strtotime(date('Y-m-t', strtotime('-1 month')))+86399;

                }

            }

            $price = $this->where($dis)->where('time_str','<',time())->where('time_str','between',"$start_time,$end_time")->field('SUM(time_str_end-time_str) as time_long')->find();
        }else{
            //不限
            $price = $this->where($dis)->where('time_str','<',time())->field('SUM(time_str_end-time_str) as time_long')->find();

        }

        return !empty($price->time_long)?$price->time_long:0;

    }

    /**
     * 上下架、删除
     * @return \think\Response
     */
    public function carteStatus()
    {
        $input = $this->request->only(['id', 'status']);
        $rule = [
            'id' => 'require',
            'status' => 'require|in:0,1,-1',
        ];
        $validate = \think\facade\Validate::rule($rule);
        if (!$validate->check($input)) {
            return $this->error($validate->getError());
        }
        if ($input['status'] == -1) {
            $where = [
                ['', 'exp', Db::raw("find_in_set({$input['id']},carte)")],
                ['status', '<>', '-1'],
                ['uniacid', '=', $this->_uniacid]
            ];
            $info = ShopGoods::getInfo($where);
            if (!empty($info)) {
                return $this->error('此分类下有商品，不可删除');
            }
        }
        $res = ShopCarte::update(['status' => $input['status']], ['id' => $input['id']]);
        if ($res === false) {
            return $this->error('操作失败');
        }
        return $this->success('');
    }

    /**
     * 下拉
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function goodsCarteList()
    {
        $list = ShopCarte::getListNoPage(['status' => 1, 'uniacid' => $this->_uniacid]);
        return $this->success($list);
    }

    /**
     *添加商品
     * @return \think\Response
     */
    public function addGoods()
    {
        $input = $this->request->only(['name', 'carte', 'cover', 'images', 'image_url', 'video_url', 'phone', 'desc', 'sort', 'price']);
        $rule = [
            'name' => 'require',
            'carte' => 'require',
            'cover' => 'require',
            'images' => 'require',
            'phone' => 'require',
            'desc' => 'require',
            'price' => 'require',
        ];
        $validate = \think\facade\Validate::rule($rule);
        if (!$validate->check($input)) {
            return $this->error($validate->getError());
        }
        $input['create_time'] = time();
        $input['images'] = json_encode($input['images']);
        $input['carte'] = implode(',', $input['carte']);
        $input['uniacid'] = $this->_uniacid;
        $res = ShopGoods::insert($input);
        if ($res) {
            return $this->success('');
        }
        return $this->error('添加失败');
    }

    /**
     * 编辑商品
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function editGoods()
    {
        $input = $this->request->only(['id', 'name', 'carte', 'cover', 'images', 'image_url', 'video_url', 'phone', 'desc', 'sort', 'price']);
        if ($this->request->isPost()) {
            $rule = [
                'id' => 'require',
                'name' => 'require',
                'carte' => 'require',
                'cover' => 'require',
                'images' => 'require',
                'phone' => 'require',
                'desc' => 'require',
                'price' => 'require',
            ];
            $validate = \think\facade\Validate::rule($rule);
            if (!$validate->check($input)) {
                return $this->error($validate->getError());
            }
            $input['images'] = json_encode($input['images']);
            $input['carte'] = implode(',', $input['carte']);
            $input['uniacid'] = $this->_uniacid;
            $res = ShopGoods::update($input, ['id' => $input['id']]);
            if ($res === false) {
                return $this->error('编辑失败');
            }
            return $this->success('');
        }
        if (empty($input['id'])) {
            return $this->error('参数错误');
        }
        $data = ShopGoods::getInfo(['id' => $input['id']]);
        $data['carte'] = explode(',', $data['carte']);
        $data['images'] = json_decode($data['images'], true);
        return $this->success($data);
    }

    /**
     * 商品列表
     * @return \think\Response
     * @throws \think\db\exception\DbException
     */
    public function goodsList()
    {
        $input = $this->request->param();
        $limit = $this->request->param('limit', 10);
        $where = [];
        $where[] = ['status', '<>', -1];
        $where[] = ['uniacid', '=', $this->_uniacid];
        if (!empty($input['name'])) {
            $where[] = ['name', 'like', '%' . $input['name'] . '%'];
        }
        if (!empty($input['carte'])) {
            $where[] = ['', 'exp', Db::raw("find_in_set({$input['carte']},carte)")];
        }
        $data = ShopGoods::getList($where, $limit);
        return $this->success($data);
    }

    /**
     * 上下架删除
     * @return \think\Response
     */
    public function goodsStatus()
    {
        $input = $this->request->only(['id', 'status']);
        $rule = [
            'id' => 'require',
            'status' => 'require|in:0,1,-1',
        ];
        $validate = \think\facade\Validate::rule($rule);
        if (!$validate->check($input)) {
            return $this->error($validate->getError());
        }
        $res = ShopGoods::update(['status' => $input['status']], ['id' => $input['id']]);
        if ($res === false) {
            return $this->error('操作失败');
        }
        return $this->success('');
    }
}