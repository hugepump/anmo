<?php
namespace app\agent\controller;

use longbingcore\tools\LongbingArr;
use think\App;
use app\agent\model\AppAdminModel;
use app\agent\model\Cardauth2ConfigModel;
use app\agent\model\Cardauth2CopyrightModel;
use app\AgentRest;
use app\admin\model\AppConfig as CardConfig;
use app\agent\validate\Cardauth2ConfigValidate;
use think\facade\Db;
use app\Common\Rsa2Sign;

class AppController extends AgentRest
{
    public function __construct ( App $app ){

        parent::__construct( $app );
        if ($this->_user['role_name'] != 'admin') {
            echo json_encode(['code' => 401, 'error' => lang('Permission denied')]);
            exit;
        }
    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-05-14 14:18
     * @功能说明:授权管理
     */
    public function list()
    {
        $param = $this->_param;

        $m_config = new Cardauth2ConfigModel();

        $dis[] = ['a.status', '=', 1];
        //小程序名字搜索
        if(!empty($param['mini_name'])){

            $dis[] = ['a.mini_name','like','%'.$param['mini_name'].'%'];
        }
        //代理商搜索
        if(!empty($param['agent_id'])){

            $dis[] = ['a.agent_id','=',$param['agent_id']];
        }

        if($this->_is_weiqin){

            //By.jingshuixian   2020年4月16日18:31:41
            //新增根据模块名称关联查询 , 目的是 装修只能找到装修的授权列表, 名片只能找到名片授权列表
            //新增授权  小程序列表也需要调整
            //ims_account   微擎小程序记录表
            //ims_wxapp_versions  微擎小程序版本表

            $app_model_name = APP_MODEL_NAME;
            $dis[] = ['v.modules', 'like', "%{$app_model_name}%"] ;
            $list = $m_config
                ->alias('a')
                ->join('wxapp_versions v' , 'a.modular_id = v.uniacid')
                ->field(['a.id', 'a.mini_name', 'a.modular_id', 'a.number', 'a.copyright_id', 'a.create_time', 'a.end_time', 'a.remark','a.agent_id','a.upload_setting'])
                ->group('a.modular_id')
                ->where($dis)
                ->paginate(['list_rows' => $param['page_count'] ? $param['page_count'] : 10, 'page' => $param['page'] ? $param['page'] : 1])
                ->toArray();

        }else{


            $list = $m_config
                ->alias('a')
                ->field(['id', 'mini_name', 'modular_id', 'number', 'copyright_id', 'create_time', 'end_time', 'remark','agent_id','upload_setting'])
                ->where($dis)
                ->paginate(['list_rows' => $param['page_count'] ? $param['page_count'] : 10, 'page' => $param['page'] ? $param['page'] : 1])
                ->toArray();

        }




        $copyrights = [];
        $copyright_ids = array_column($list['data'] ?? [], 'copyright_id');
        $copyrights_temp = Cardauth2CopyrightModel::where([['id', 'IN', $copyright_ids], ['status', '=', 1]])->field(['id', 'name'])->select();
        foreach ($copyrights_temp as $item) {
            $copyrights[$item['id']] = $item['name'];
        }

        //代理商模型
        $agent_model = new \app\agent\model\AgentList();

        foreach ($list['data'] as $k => $item) {

            $list['data'][$k]['copyright'] = $copyrights[$item['copyright_id']] ?? "";
//            //代理商名字
            $agent_name = $agent_model->where(['id'=>$item['agent_id']])->value('user_name');

            $list['data'][$k]['agent_name'] = !empty($agent_name)?$agent_name:'';

        }

        return $this->success($list);
    }

    public function get()
    {
        $id = $this->_param['id'] ?? null;
        if (!$id) {
            return $this->error('参数错误');
        }

        $config = Cardauth2ConfigModel::find($id);
        $config['boss'] = $config['boos'] ?? null;
        $config['activity'] = $config['activity_switch'] ?? null;
        $config['appiont'] = $config['appoint'] ?? null;

        $eventData = event('AgentAppAuthEdit' , $config);


        $eventData = LongbingArr::array_merge($eventData);

        $config['authList'] = $eventData ;

        return $this->success($config);
    }

    /**
     * 新增小程序授权时,获取所有权限列表
     *
     * @return \think\Response
     * @author shuixian
     * @DataTime: 2019/12/30 14:00
     */
    public function getAuthList(){

        $eventData = event('AgentAppAuthEdit' , []);
        $eventData = LongbingArr::array_merge($eventData);
        $config['authList'] = $eventData ;

        return $this->success($config);
    }

    public function create()
    {
        $input = $this->_input;

        $time = time();
        if (defined('IS_WEIQIN')) {
            $validate = new Cardauth2ConfigValidate();
            $check = $validate->scene('create')->append('modular_id', 'require')->check($input);
            if ($check == false) {
                return $this->error($validate->getError());
            }

            $m_auth2_config = Cardauth2ConfigModel::where([['modular_id', '=', $input['modular_id']], ['status', '=', 1]])->findOrEmpty();

            if (!$m_auth2_config->isEmpty()) {
                return $this->error('小程序已存在');
            }

            $mini_name = Db::name('account_wxapp')->field(['acid', 'name'])->where([['uniacid', '=', $input['modular_id']]])->find();
            $rst = $m_auth2_config->save(
                [
                    //代理商
                    'agent_id'   => !empty($input['agent_id'])?$input['agent_id']:0,

                    'upload_setting'   => !empty($input['upload_setting'])?$input['upload_setting']:0,

                    'modular_id' => $input['modular_id'],
                    'number' => $input['number'],
                    'uniacid' => 0,
                    'create_time' => $time,
                    'update_time' => $time,
                    'remark' => $input['remark'],
                    'end_time' => $input['end_time'],
                    'mini_name' => $mini_name['name'] ?? '',
                    'copyright_id' => $input['copyright_id'],
                    'send_switch' => $input['send_switch'],
                    'boos' => $input['boss'],
                    'appoint' => $input['appiont'],
                    'payqr' => $input['payqr'],
                    'shop_switch' => $input['shop_switch'],
                    'timeline_switch' => $input['timeline_switch'],
                    'website_switch' => $input['website_switch'],
                    'article' => $input['article'],
                    'activity' => $input['activity'],
                    'pay_shop' => $input['pay_shop'],
                    'house_switch'     => $input['house_switch'],
                    //带客有礼
                    'passenger_switch' => $input['passenger_switch'],
                    //百度
                    'baidu_switch'     => $input['baidu_switch'],
                    //截流
                    'closure_switch'   => $input['closure_switch'],
                    //红包
                    'redbag_switch'    => $input['redbag_switch'],
                    //满减
                    'reduction_switch' => $input['reduction_switch'],
                    //直播
                    'livevideo_switch' => $input['livevideo_switch'],
                    //短视频
                    'shortvideo_switch'=> $input['shortvideo_switch'],
                    //会员
                    'member_switch'    => $input['member_switch'],
                    //餐饮
                    'restaurant_switch' => $input['restaurant_switch'],
					// 霸王餐
                    'overlord_switch'  => $input['overlord_switch'],
					// 付费课程
                    'payclass_switch'  => $input['payclass_switch'],

                    'bargain_switch'  => $input['bargain_switch'],

                    'question_switch'  => $input['question_switch'],


//                    'tool_switch' => $input['tool_switch'],
                ]
            );

            return $this->success($rst);

        }


        $validate = new Cardauth2ConfigValidate();
        $check = $validate->scene('create')->check($input);
        if ($check == false) {
            return $this->error($validate->getError());
        }

        $m_auth2_config = Cardauth2ConfigModel::where([['mini_name', '=', $input['mini_name']]])->findOrEmpty();
        if (!$m_auth2_config->isEmpty()) {
            return $this->error('小程序已存在');
        }

        $lenth_name = strlen($input['mini_name']);
        if ($lenth_name <= 0 || $lenth_name >= 30 ) {
            return $this->error("名字长度只能在0到30个字符");
        }

        $max_uniacid_card_config = CardConfig::field('uniacid')->order('uniacid', 'desc')->limit(1)->select()->toArray();
        $max_uniacid = $max_uniacid_card_config[0]['uniacid'] ?? 0;

        $auth_name = Cardauth2ConfigModel::where([['mini_name', '=', $input['mini_name']], ['status', '=', 1]])->find();
        if(!empty($auth_name)){
            return $this->error("小程序名字不能重复");
        }
        $rst = $m_auth2_config->save(
            [
                //代理商
                'agent_id'   => !empty($input['agent_id'])?$input['agent_id']:0,

                'upload_setting'   => !empty($input['upload_setting'])?$input['upload_setting']:0,

                'modular_id' => $max_uniacid + 1,
                'number' => $input['number'],
                'uniacid' => $this->_uniacid,
                'create_time' => $time,
                'update_time' => $time,
                'remark' => $input['remark'],
                'end_time' => $input['end_time'],
                'mini_name' => $input['mini_name'],
                'copyright_id' => $input['copyright_id'],
                'send_switch' => $input['send_switch'],
                'boos' => $input['boss'],
                'appoint' => $input['appiont'],
                'payqr' => $input['payqr'],
                'shop_switch' => $input['shop_switch'],
                'timeline_switch' => $input['timeline_switch'],
                'website_switch' => $input['website_switch'],
                'article' => $input['article'],
                'activity_switch' => $input['activity'] ?? $input['activity_switch'],
                'pay_shop' => $input['pay_shop'],
                'house_switch' => $input['house_switch'],
                //带客有礼
                'passenger_switch' => $input['passenger_switch'],
                //百度
                'baidu_switch'     => $input['baidu_switch'],
                //截流
                'closure_switch'   => $input['closure_switch'],
                //红包
                'redbag_switch'    => $input['redbag_switch'],
                //满减
                'reduction_switch' => $input['reduction_switch'],
                //直播
                'livevideo_switch' => $input['livevideo_switch'],
                //短视频
                'shortvideo_switch'=> $input['shortvideo_switch'],
                //会员
                'member_switch'    => $input['member_switch'],
				// 餐饮
                'restaurant_switch' => $input['restaurant_switch'],
				// 霸王餐
                'overlord_switch' => $input['overlord_switch'],
				// 付费课程
                'payclass_switch' => $input['payclass_switch'],

                'bargain_switch' => $input['bargain_switch'],

                'question_switch' => $input['question_switch'],
//                'tool_switch' => $input['tool_switch'],
            ]
        );

        $rst = $rst && $m_auth2_config->cardConfig()->save([
                'uniacid' => $m_auth2_config->modular_id,
                'create_time' => $m_auth2_config->create_time,
                'update_time' => $m_auth2_config->update_time,
                'copyright' => "",
                'is_sync' =>1,
                'mini_app_name' => $m_auth2_config->mini_name,
                'create_txt' => "创建我的智能名片",
            ]);


        if ($rst) {
            return $this->success($m_auth2_config->id);
        }

        return $this->error('fail');

    }

    public function update()
    {


        $input = $this->_input;

        $input['boos']    = $input['boss'];
        $input['appoint'] = $input['appiont'];
        $input['activity_switch'] = $input['activity'];

        if (defined('IS_WEIQIN')) {
            $validate = new Cardauth2ConfigValidate();
            $check = $validate->scene('update')->check($input);
            if ($check == false) {
                return $this->error($validate->getError());
            }

            $m_auth2_config = Cardauth2ConfigModel::where('id', '=', $input['id'])->findOrEmpty();
            if ($m_auth2_config->isEmpty()) {
                return $this->error('未找到小程序');
            }

            /**
             * @var Cardauth2ConfigModel $m_auth2_config
             */
            $rst = $m_auth2_config->allowField([

                'agent_id',

                'upload_setting',

                'number',
                'end_time',
                'copyright_id',
                'send_switch',
                'boos',
                'appoint',
                'payqr',
                'shop_switch',
                'timeline_switch',
                'website_switch',
                'article',
                'activity_switch',
                'pay_shop' ,
                'house_switch',
                'tool_switch',
                'remark',
                'closure_switch',
                'baidu_switch',
                //带客有礼
                'passenger_switch',
                //红包
                'redbag_switch',
                //满减
                'reduction_switch',
                //视频
                'livevideo_switch',
                //短视频
                'shortvideo_switch',
                //会员
                'member_switch',
				// 餐饮
                'restaurant_switch',
				// 霸王餐
                'overlord_switch',
				// 付费课程
                'payclass_switch',

                'bargain_switch',

                'question_switch',
            ])->save($input);

            return $this->success($rst);

        }



        $validate = new Cardauth2ConfigValidate();
        $check = $validate->scene('update')->check($input);
        if ($check == false) {
            return $this->error($validate->getError());
        }

        $m_auth2_config = Cardauth2ConfigModel::where('id', '=', $input['id'])->findOrEmpty();
        if ($m_auth2_config->isEmpty()) {
            return $this->error('未找到小程序');
        }

        clearCache($m_auth2_config['uniacid']);

        $auth_name = Cardauth2ConfigModel::where([['mini_name', '=', $input['mini_name']], ['status', '=', 1],['id','<>',$input['id']]])->find();
        if(!empty($auth_name)){
            return $this->error("小程序名字不能重复");
        }

        /**
         * @var Cardauth2ConfigModel $m_auth2_config
         */
        $rst = $m_auth2_config->allowField([
            'agent_id',

            'upload_setting',

            'mini_name',
            'number',
            'end_time',
            'copyright_id',
            'send_switch',
            'boos',
            'appoint',
            'payqr',
            'shop_switch',
            'timeline_switch',
            'website_switch',
            'article',
            'activity_switch',
            'pay_shop' ,
            'house_switch',
            'remark',
            'closure_switch',
            //百度
            'baidu_switch',
            //带课有礼
            'passenger_switch',
            //红包
            'redbag_switch',
            //满减
            'reduction_switch',
            //视频
            'livevideo_switch',
            //短视频
            'shortvideo_switch',
            //会员
            'member_switch',
			// 餐饮
            'restaurant_switch',
			// 霸王餐
            'overlord_switch',
			// 付费课程
            'payclass_switch',

            'bargain_switch',

            'question_switch',
        ])->save($input);


        $rst = $rst && $m_auth2_config->cardConfig->save([
                'uniacid' => $m_auth2_config->modular_id,
                'update_time' => $m_auth2_config->update_time,
                'copyright' => "",
                'mini_app_name' => $m_auth2_config->mini_name,
            ]);

        if ($rst) {
            return $this->success($m_auth2_config->id);
        }

        return $this->error('fail');


    }


    public function delete()
    {
        $input = $this->_input;

        $validate = new Cardauth2ConfigValidate();
        $check = $validate->scene('delete')->check($input);
        if ($check == false) {
            return $this->error($validate->getError());
        }

        /**
         * @var Cardauth2ConfigModel $m_auth2_config
         */
        $m_auth2_config = Cardauth2ConfigModel::where('id', '=', $input['id'])->findOrEmpty();
        if ($m_auth2_config->isEmpty()) {
            return $this->error('未找到小程序');
        }
        $rst = $m_auth2_config->delete();

        if ($rst) {
            if (!defined('IS_WEIQIN')) {
                //删除小程序
                CardConfig::where([['uniacid', '=', $m_auth2_config->modular_id]])->delete();
                //删除绑定关系
                AppAdminModel::where([['modular_id', '=', $m_auth2_config->modular_id]])->delete();

            }

            return $this->success($m_auth2_config->id);
        }




        return $this->error('fail');

    }


    public function getWxApp()
    {
        if (defined('IS_WEIQIN')) {

            //By.jingshuixian   2020年4月20日15:54:09
            //解决行业版数据独立的问题
            //$account_uniacids = Db::name('account')->where([['type', '=', 4], ['isdeleted', '=', 0]])->column('uniacid');
            //$wxapp = Db::name('account_wxapp')->field(['acid', 'name'])->where([['uniacid', 'IN', $account_uniacids]])->select()->toArray();
            /*foreach ($wxapp as $k => $item) {
                $wxapp[$k]['mini_name'] = $item['name'];
                $wxapp[$k]['modular_id'] = $item['acid'];
            }*/

           // $a = Db::name('account_wxapp')->select();

            $app_model_name = APP_MODEL_NAME;
            $m_config = new Cardauth2ConfigModel();
            $wxapp = Db::name('account')
                ->alias('a')
                ->join('wxapp_versions v' , 'a.uniacid = v.uniacid')
                ->join('account_wxapp account_wxapp' , 'a.uniacid = account_wxapp.uniacid')
                ->field(['account_wxapp.name as mini_name', 'account_wxapp.uniacid as  modular_id'])
                ->where([ ['v.modules', 'like', "%".'"'.$app_model_name.'"'."%"]  ,  ['a.type', '=', 4] ,['a.isdeleted', '=', 0]  ])
                ->group('a.uniacid')
                ->select()
                ->toArray();


            return $this->success($wxapp);
        }else{

            $dis[] = ['uniacid','<>',8888];

            $dis[] = ['status','=',1];

            $dis[] = ['mini_app_name','<>',''];

            $list = CardConfig::field(['mini_app_name' => 'mini_name', 'uniacid' => 'modular_id'])->where($dis)->select()->toArray();
            return $this->success($list);
        }


    }


    public function isWe7()
    {
        $is_we7 = defined('IS_WEIQIN');

        return $this->success($is_we7);
    }


    public function redirectAppBackgroundToken()
    {
        if (defined('IS_WEIQIN')) {
            return $this->error('微擎用户禁止访问');
        };

        $modular_id = $this->_input['modular_id'];

        $user = [
            'admin_id' => $this->_user['admin_id'],
            'account'=> $this->_user['account'],
            'role' => $this->_user['role'],
            'role_name' => $this->_user['role_name'],
            'uniacid' => $modular_id,
        ];

        $result['user'] = $user;
        $result['token'] = createToken();
        if (empty($result['token'])) {
            return $this->error('System is busy,please try again later.', 400);
        }
        //添加缓存数据
        setUserForToken($result['token'], $user);
        return $this->success($result, 200);

    }

    //站点绑定
    public function websitebind()
    {
        $type = $this->_input['type'] ?? null;
        if (!in_array($type, ['get', 'post'])) {
            return $this->error(lang('param error'));
        }

        $version_id = longbingGetBranchVersionId();
        $branch_id = longbingGetBranchId();
        $version_name = longbingGetBranchVersionName();

        $domain_name = $_SERVER['HTTP_HOST'];
        $server_url = longbingGetSaasUrl();//接口地址

        $bindInfo = longbingGetWebSiteBingData();


        if ($type == 'get') {
            $data = array(
                'version_id'   => $version_id,
                'branch_id'    => $branch_id,
                'version_name' => $version_name,
                'domain_name'  => $domain_name,
            );
            //获取最新版本
            if(!empty($bindInfo))
            {
                //检查数据是否存在
                if(empty($bindInfo) || empty($bindInfo['website_keys']) || empty($bindInfo['website_keys']) || empty($bindInfo['domain_keys'])) {
                    $data['newest_version_name'] = $data['version_name'];
                    $data['website_keys'] = '';
                }else{
                    $data['website_keys'] = $bindInfo['website_keys'];
                    //获取最新的版本信息
                    $new_branch = json_decode($this->lb_api_notice_increment_we7($server_url.'/app_version/'.$branch_id,null,['Accept-Charset:utf-8','Origin:'.$bindInfo['domain_name'],'Referer:'.$bindInfo['domain_name']],'GET'),true);

                    if(empty($new_branch) || !isset($new_branch['result']['data']) || empty($new_branch['result']['data']) || isset($new_branch['error']) || in_array($new_branch['result']['data']['version_id'], [$version_id]))
                    {
                        $data['newest_version_name'] = $data['version_name'];
                        $data['newest_version_id']   = $data['version_id'];
                    }else{
                        $data['newest_version_id']   = $new_branch['result']['data']['version_id'];
                        $data['newest_version_name'] = $new_branch['result']['data']['version_name'];
                    }
                }
            }
            return $this->success($data);

        } else {
            if (isset($bindInfo['website_keys']) && $bindInfo['website_keys'] ) {
                return $this->error('已经绑定过了, 无需重复绑定');
            }
            $website_key = $this->_input['website_keys'] ?? null;
            if ($type == 'post' && $website_key == null) {
                return $this->error('请输入密钥');
            }

            $res = json_decode(($this->lb_api_notice_increment_we7($server_url . '/website/check?keys=' . $website_key, [], ['Accept-Charset:utf-8', 'Origin:' . $domain_name], 'GET')), true);

            if (isset($res['error'])) {
                return $this->error($res['error']['message']);
            }
            $data = $res['result']['data'];


            $save_data = [
                'domain_name' => $domain_name,
                'domain_keys' => json_encode($data['domain_keys'], true),
                'domain_id' => $data['domain_id'],
                'website_id' => $data['website_id'],
                'website_keys' => $website_key,
            ];
            if(empty($bindInfo))
            {
                $result =   Db::name('lb_pluge_key')->save($save_data);
            }else{
                $result =   Db::name('lb_pluge_key')->where('id', '=', $bindInfo['id'])->update($save_data);
            }


            if ($result === false) {
                return $this->error(lang('faild'));
            }
        }

        return $this->success(true);
    }
    /**
     * 更新程序
     * @author yangqi
     */
    public function updateApp()
    {
        if(longbingIsWeiqin()) return $this->error(lang('not need update'));
        //app 识别号
        $branch_id  = longbingGetBranchId();
        //version_id
        $version_id = longbingGetBranchVersionId();
        //获取saas url
        $server_url = longbingGetSaasUrl();

        if(empty($branch_id) || empty($version_id) || empty($server_url)) return $this->error(lang('not need update'));

        //获取站点绑定信息
        $bing_data = longbingGetWebSiteBingData();
        //检查数据是否存在
        if(empty($bing_data) || empty($bing_data['website_keys']) || empty($bing_data['website_keys']) || empty($bing_data['domain_keys'])) return $this->error(lang('webiste not bing.'));
        //获取最新的版本信息
        $new_branch = json_decode($this->lb_api_notice_increment_we7($server_url.'/app_version/'.$branch_id,null,['Accept-Charset:utf-8','Origin:'.$bing_data['domain_name'],'Referer:'.$bing_data['domain_name']],'GET'),true);

        if(empty($new_branch) || !isset($new_branch['result']['data']) || empty($new_branch['result']['data']) || isset($new_branch['error']) || in_array($new_branch['result']['data']['version_id'], [$version_id])) return $this->error(lang('not need update'));
        //      $version_id = $new_branch['result']['data']['version_id'];
        //获取加密秘钥
        $keys = $bing_data['domain_keys'];
        $keys = json_decode($keys ,true);

        $signModel=new Rsa2Sign($keys);
        //生成查询条件
        $data =["branch_id"=>$branch_id ,"version_id"=>$version_id];
        //机密数据
        $sign=$signModel->createSign(json_encode($data,true));
        // 授权检查
        $res= json_decode($this->lb_api_notice_increment_we7($server_url.'/authorization?sign='.$sign,json_encode(["authorization"=>$data]),['Accept-Charset:utf-8','Origin:'.$bing_data['domain_name'],'Referer:'.$bing_data['domain_name']],'POST'),true);

        //接口结果 判断是否成功
        if(isset($res['error'])){
            return $this->error(lang($res['error']['message']));
        }
        //获取更新数据url
        $down_load_url=isset($res['result']['data']['backstage_url']) && $res['result']['data']['backstage_url'] ? $res['result']['data']['backstage_url'] : '';
        if(!$down_load_url){
            //无需更新
            return $this->returnSuccess(['code'=>-1],'无需更新');
        }
        //模块名称（目录名称）
        $model_name=isset($res['result']['data']['model_name'])  ?   $res['result']['data']['model_name']: '';
        $version_id=isset($res['result']['data']['version_id'])  ?   $res['result']['data']['version_id']: '';
        $version_name =isset($res['result']['data']['version_name'])  ?   $res['result']['data']['version_name']: '';
        $file_name    = isset($res['result']['data']['backstage_name'])  ?   $res['result']['data']['backstage_name']: '';
        //下载覆盖文件
        $save_dir=ROOT_PATH . 'temp';
        $cp_dir=ROOT_PATH ;

        //更新文件
        if(longbingUpdateAppFile($down_load_url,$save_dir ,$file_name,$cp_dir)){
            //是否有更新数据库
            if($model_name && file_exists(APP_PATH.$model_name.'/upgrade.php')){
                $sql_all='';//更新数据库
                require_once APP_PATH.$model_name.'/upgrade.php';
            }
            //写入最新版本
            $data=[
//              'uniacid'      => $uniacid,
                'version_id'   => $version_id,
                'branch_id'    => $branch_id,
                'version_name' => $version_name
            ];
            $data = longbingGetWebSiteBingData(['id' => $bing_data['id']] ,$data);
            return $this->success(lang('update success'));
        }else{
            return $this->success(lang('update error'));
        }
    }

    private function lb_api_notice_increment_we7 ( $url, $data, $headers = [ 'Accept-Charset:utf-8' ], $request_type = 'POST' )
    {
        $ch = curl_init();
        //    $header = "Accept-Charset: utf-8";
        curl_setopt( $ch, CURLOPT_URL, $url );
        //设置头文件的信息作为数据流输出
        curl_setopt( $ch, CURLOPT_HEADER, 0 );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
        curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, $request_type );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );
        curl_setopt( $ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)' );
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
        curl_setopt( $ch, CURLOPT_AUTOREFERER, 1 );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        $tmpInfo = curl_exec( $ch );
        //     var_dump($tmpInfo);
        //    exit;
        if ( curl_errno( $ch ) ) {
            return false;
        } else {
            // var_dump($tmpInfo);
            return $tmpInfo;
        }
    }


}