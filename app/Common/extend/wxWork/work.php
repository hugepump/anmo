<?php
namespace app\Common\extend\wxWork;
/**
 * Created by PhpStorm.
 * User: ZengZhengFu
 * Date: 2018/11/20 0020
 * Time: 上午 10:16
 */

class work
{
    //  每个企业都拥有唯一的corpid，获取此信息可在管理后台“我的企业”－“企业信息”下查看“企业ID”（需要有管理员权限）
    protected $appid;
    //  secret是企业应用里面用于保障数据安全的“钥匙”，每一个应用都有一个独立的访问密钥，为了保证数据的安全，secret务必不能泄漏。
    protected $appsecret;

    function __construct($appid = '', $appsecret = '')
    {
        $this->appid     = $appid;
        $this->appsecret = $appsecret;
        $path = ROOT_PATH;
        if (!is_dir($path . 'runtime/data')) {
            mkdir($path . 'runtime/data',0777,true);
        }
        if (!is_dir($path . 'runtime/data/tpl')) {
            mkdir($path . 'runtime/data/tpl',0777,true);
        }
        if (!is_dir($path . 'runtime/data/tpl/web')) {
            mkdir($path . 'runtime/data/tpl/web',0777,true);
        }

    }

    /*
     * 给员工推送应用消息
     * array $data 发送数据
     */
    public function send(array $data)
    {
        if (is_array($data)) {
            $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        }
        $accessTokenWW = $this->getAccessTokenWW();

        if (is_array($accessTokenWW)) {
            return $accessTokenWW;
        }
        $url = "https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token={$accessTokenWW}";

        $res = $this->curlPost($url, $data);
        return $res;
    }
    /*
     * 给员工推送应用消息
     * array $data 发送数据
     */
    public function send_multi(array $data)
    {
        $accessTokenWW = $this->getAccessTokenWW();
        if (is_array($accessTokenWW)) {
            return $accessTokenWW;
        }
        $url = "https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token={$accessTokenWW}";
        foreach ($data as $index => $item)
        {
            $data[$index]['url'] = $url;
        }
        $res = $this->curl_multi($data);
        return $res;
    }

    /*
     * 获取AccessToken
     */
    protected function getAccessTokenWW()
    {
        $appidMd5 = md5($this->appid);
        if (!is_file(ROOT_PATH . 'runtime/data/tpl/web/' . $appidMd5 . '.txt')) {
            $url  = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid={$this->appid}&corpsecret={$this->appsecret}";
            $data = $this->curlPost($url);
            $data = json_decode($data, true);
            if (!isset($data['access_token'])) {
                return $data;
            }
            $access_token = $data['access_token'];


            file_put_contents(ROOT_PATH . 'runtime/data/tpl/web/' . $appidMd5 . '.txt', json_encode(['at' => $access_token, 'time' => time() + 6200]));
            return $access_token;
        }
        if (is_file(ROOT_PATH . 'runtime/data/tpl/web/' . $appidMd5 . '.txt')) {
            $fileInfo = file_get_contents(ROOT_PATH . 'runtime/data/tpl/web/' . $appidMd5 . '.txt');
            if (!$fileInfo) {
                $url  = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid={$this->appid}&corpsecret={$this->appsecret}";
                $data = $this->curlPost($url);
                $data = json_decode($data, true);
                if (!isset($data['access_token'])) {
                    return $data;
                }
                $access_token = $data['access_token'];

                file_put_contents(ROOT_PATH . '/data/tpl/web/' . $appidMd5 . '.txt', json_encode(['at' => $access_token, 'time' => time() + 6200]));
                return $access_token;
            } else {
                $fileInfo = json_decode($fileInfo, true);
                if ($fileInfo['time'] < time()) {
                    $url  = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid={$this->appid}&corpsecret={$this->appsecret}";
                    $data = $this->curlPost($url);
                    $data = json_decode($data, true);
                    if (!isset($data['access_token'])) {
                        return $data;
                    }
                    $access_token = $data['access_token'];

                    file_put_contents(ROOT_PATH . 'runtime/data/tpl/web/' . $appidMd5 . '.txt', json_encode(['at' => $access_token, 'time' => time() + 6200]));
                    return $access_token;
                }
                return $fileInfo['at'];
            }
        }

        return false;
    }

    /*
     * 发送http请求
     * string $url 请求链接
     * string（json） $data 请求数据
     * number $time 请求超时时间
     */
    protected function curlPost($url, $data = '', $time = 20)
    {
//        if (!empty($data))
//            $data = $this->arr2xml($data);

        //初使化init方法
        $ch = curl_init();

        //指定URL
        curl_setopt($ch, CURLOPT_URL, $url);

        //设定请求后返回结果
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        //声明使用POST方式来进行发送
        curl_setopt($ch, CURLOPT_POST, 1);

        //发送什么数据呢
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);


        //忽略证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        //忽略header头信息
        curl_setopt($ch, CURLOPT_HEADER, 0);

        //设置超时时间
        curl_setopt($ch, CURLOPT_TIMEOUT, $time);

        //发送请求
        $output = curl_exec($ch);

        //关闭curl
        curl_close($ch);

        //返回数据
//        $output = (array)\simplexml_load_string($output, null, LIBXML_NOCDATA | LIBXML_COMPACT);
        return $output;
    }

    /*
    * 批量发送http请求
    * string $url 请求链接
    * string（json） $data 请求数据
    * number $time 请求超时时间
    */
    protected function curl_multi ($array)
    {
        global $_GPC, $_W;
        $mh = curl_multi_init();
        $curls = array();

        foreach ($array as $index => $item)
        {
            $tmp = curl_init();

            curl_setopt($tmp, CURLOPT_URL, $item['url']);
            curl_setopt($tmp, CURLOPT_HEADER, 0);



            //设定请求后返回结果
            curl_setopt($tmp, CURLOPT_RETURNTRANSFER, 1);

            //声明使用POST方式来进行发送
            curl_setopt($tmp, CURLOPT_POST, 1);

            //发送什么数据呢
            curl_setopt($tmp, CURLOPT_POSTFIELDS, $item['data']);


            //忽略证书
            curl_setopt($tmp, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($tmp, CURLOPT_SSL_VERIFYHOST, false);

            //忽略header头信息
            curl_setopt($tmp, CURLOPT_HEADER, 0);

            //设置超时时间
            curl_setopt($tmp, CURLOPT_TIMEOUT, 100);


            array_push($curls, $tmp);
            curl_multi_add_handle($mh, $tmp);
        }

        $running=null;

        do {
            $mrc = curl_multi_exec($mh, $running);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);

        while ($running && $mrc == CURLM_OK) {
            if (curl_multi_select($mh) != -1) {
                do {
                    $mrc = curl_multi_exec($mh, $running);
                } while ($mrc == CURLM_CALL_MULTI_PERFORM);
            }
        }

        foreach ($curls as $index => $item)
        {
            curl_multi_remove_handle($mh, $item);
        }
        curl_multi_close($mh);

        return true;
    }
}