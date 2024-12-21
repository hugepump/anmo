<?php


namespace app\agent\model;


use app\agent\model\Cardauth2AuthAppModel as Cardauth2Model;
use app\BaseModel;

class Cardauth2AuthAppModel extends BaseModel
{
    protected $name = 'longbing_cardauth2_auth_app';


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-06-04 17:53
     * @功能说明:获取插件使用数量
     */
    public static function getUseNum($app_name,$is_weiqin){


        if($is_weiqin){

            $app_model_name = APP_MODEL_NAME;
            $count = Cardauth2Model
                ::alias('a')
                ->field(['a.id', 'a.modular_id', 'a. create_time', 'a.sign',  'c.mini_app_name'])
                ->join('longbing_card_config c', 'a.modular_id = c.uniacid')

                ->join('account' , 'a.modular_id = account.uniacid')
                ->join('wxapp_versions v' , 'a.modular_id = v.uniacid')

                ->where([['a.status', '=', 1],['app_name','like',"%".$app_name ."%"]  , ['account.type', '=', 4]  ,['account.isdeleted', '=', 0] ,  ['v.modules', 'like', "%{$app_model_name}%"]   ])
                ->group('a.id')
                ->sum('count');

        }else{

            $count = Cardauth2Model
                ::alias('a')
                ->field(['a.id', 'a.modular_id', 'a. create_time', 'a.sign',  'c.mini_app_name'])
                ->join('longbing_card_config c', 'a.modular_id = c.uniacid')
                ->where([['a.status', '=', 1],['app_name','=',$app_name]])
                ->group('a.id')
                ->sum('count');
        }

        return $count;

    }

}