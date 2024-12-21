<?php
declare(strict_types=1);

namespace longbingcore\wxcore;

use app\Common\LongbingServiceNotice;

use think\facade\Db;

class WxTmpl{

    static protected $uniacid;

    public function __construct($uniacid)
    {
       self::$uniacid = $uniacid;

    }

    /**
     * $data = ['tmpl_name' => '模版名', 'model_name'=> '模块名', 'tid' => 模版消息的标题id, 'kidList'   => [4,2,1,3] 模版消息的列表, 'sceneDesc' => '订单支付成功',];
     * @param $send_data
     * @功能说明: 添加模版消息
     * @author chenniang(龙兵科技)
     * @DataTime: 2019-12-26 14:18
     */

    static public function addtmpl ($dis)
    {
        //查询数据库里的模版消息
        $tmpl = Db::name('longbing_card_tmpl_config')->where($dis)->find();
        //判断数据库里面有没有tmpl_id 没有就生成
        if(empty($tmpl['tmpl_id'])){
            //初始化配置模型
            $service_model = new WxSetting(self::$uniacid);
            //生成模版消息的参数
            $send_data = [
                'tid'       => $tmpl['tid'],
                'kidList'   => explode(',',$tmpl['kid']),
                'sceneDesc' => $tmpl['sceneDesc']
            ];
            //给客户的小程序添加模版 并返回模版id
            $tmpl_info = $service_model->addTmpl($send_data);
            $tmpl_info = json_decode($tmpl_info,true);
        }else{
            //有数据就直接返回模版id
            $tmpl_info['priTmplId'] = $tmpl['tmpl_id'];
            $tmpl_info['errcode']   = 0;

        }
        return $tmpl_info;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-01-02 11:34
     * @功能说明:获取模版消息 需要发送的key
     */
    static public function getTmplKey($tmpl_id){

        if(empty($tmpl_id)){
            return [];
        }
        $cach_data = getCache($tmpl_id,self::$uniacid);
        //如果有缓存直接返回
        if(!empty($cach_data)){
            return $cach_data;
        }
        //微信库类
        $service_model = new WxSetting(self::$uniacid);
        //获取所有模版消息
        $data = $service_model->getUserTmpl();
        //没有返回空
        if(empty($data)){
            return [];
        }
        $data = json_decode($data,true);
        //如果报错 直接返回
        if(empty($data['data'])){
            return [];
        }
        $data = $data['data'];
        //找到该模版id 的模版
        $found_key = array_search($tmpl_id, array_column($data, 'priTmplId'));

        if(!is_numeric($found_key)){
            return [];
        }
        //获取该模版
        $tmpl = $data[$found_key];
        //获取该模版的内容
        $content = explode('{{',$tmpl['content']);
        //转换格式
        foreach ($content as $k =>$value){
            if($k == 0){
                unset($content[$k]);
                continue;
            }
            $content[$k] = substr($value,0,strpos($value, '.'));
        }
        //存入缓存
        setCache($tmpl_id,$content,86400,self::$uniacid);
        return $content;
    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2019-12-26 15:04
     * @功能说明:模版参数
     */

    static public function tmplParam($key){

        //订单支付通知
        $data['pay_order'] = [
            //标题id
            'tid'       => 1754,
            //内容的key(暂时不需要)
            'kidList'   => 'thing4,amount2,character_string1,date3',
            //模版场景类容
            'sceneDesc' => '订单支付成功',
            //自动生成模版时内容的顺序
            'kid'       => '4,2,1,3',
            //模版内容的样板
            'example'   => '物品名称、金额、单号、支付时间',
        ];
        //订单发货通知
        $data['send_order'] = [
            //标题id
            'tid'       => 2414,
            //内容的key(暂时不需要)
            'kidList'   => '',
            //模版场景类容
            'sceneDesc' => '订单发货通知',
            //自动生成模版时内容的顺序
            'kid'       => '1,2,3,4',
            //模版内容的样板
            'example'   => '发货时间、订单编号、物流编号、物流公司',
        ];


        //IM 未读私信通知
        $data['im_msg'] = [
            //标题id
            'tid'       => 1076,
            //内容的key(暂时不需要)
            'kidList'   => '',
            //模版场景类容
            'sceneDesc' => '未读私信通知',
            //自动生成模版时内容的顺序
            'kid'       => '1,2,3',
            //模版内容的样板
            'example'   => '留言人、留言内容、留言时间',
        ];

        if(key_exists($key,$data)){
            return $data[$key];
        }else{
            return [];
        }
    }

    /**
     * @param $poenid
     * @param $key_data
     * @param $send_data
     * @param $page
     * @功能说明: 发送模版消息
     * @author chenniang(龙兵科技)
     * @DataTime: 2019-12-26 11:22
     */
    static public function sendTmpl($poenid,$tmpl_id,$send_data,$page){
        //发送信息内容
        $data = [
            //用户openid
            'touser'      => $poenid,
            //模版id
            'template_id' => $tmpl_id,
            //跳转页面
            'page'        => $page,
            //发送内容
            'data'        => $send_data
        ];
        //初始化配置模型
        $service_model = new WxSetting(self::$uniacid);
        //发送消息
        $res = $service_model->sendTmpl($data);
        return $res;
    }


    /**
     * @param $str
     * @功能说明:转utf 8
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-01-03 19:23
     */

    static public function strToUtf8($str){

        $encode = mb_detect_encoding($str, array("ASCII",'UTF-8',"GB2312","GBK",'BIG5'));

        if($encode == 'UTF-8'){
            return $str;
        }else{
            return mb_convert_encoding($str, 'UTF-8', $encode);
        }
    }











}