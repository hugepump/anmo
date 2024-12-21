<?php
namespace app\card\model;

use app\BaseModel;
use think\Model;



class Config extends BaseModel
{
    //定义表名
    protected $name = 'longbing_card_config';


    protected static function init ()
    {
        //TODO:初始化内容
    }


    public function initConfig ($uniacid)
    {
        return self::create( [ 'uniacid' => $uniacid,'agreement'=>'' ] );
    }

    public function getConfig ($uniacid)
    {
        $key  = 'longbing_card_config_';

        $cacheData = getCache($key, $uniacid);

        //  暂时关闭缓存
        $cacheData = false;

        if ($cacheData)
        {
            $cacheData['fromCache'] = 1;
            return  $cacheData;
        }

        $data = self::where( [ [ 'uniacid', '=', $uniacid ] ] )
                      ->find();

        if ( !$data )
        {
            $data = $this->initConfig($uniacid);
        }

        $data = $data->toArray();


        $data = transImagesOne( $data, [ 'vr_cover', 'default_video', 'default_voice', 'appoint_pic', 'click_copy_show_img',
                                         'shop_carousel_more', 'copyright', 'default_video_cover' ], $uniacid
        );
        setCache( $key, $data, 36000, $uniacid );

        return  $data;
    }
	//获取
	public function getConfigByUniacid($uniacid)
	{
		$result = $this->where(['uniacid' => $uniacid])->find();
		if(!empty($result)){
			$result = $result->toArray();
			$result = transImagesOne($data, [ 'vr_cover', 'default_video', 'default_voice', 'appoint_pic', 'click_copy_show_img',
                                         'shop_carousel_more', 'copyright', 'default_video_cover' ], $uniacid);
		}
		return $result;
	}

}