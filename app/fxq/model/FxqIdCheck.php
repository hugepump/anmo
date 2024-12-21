<?php
/**
 * Created by PhpStorm
 * User: shurong(龙兵科技)
 * Date: 2024/10/15
 * Time: 15:30
 * docs:
 */

namespace app\fxq\model;

use app\BaseModel;
use app\fxq\info\PermissionFxq;
use longbingcore\wxcore\Fxq;

class FxqIdCheck extends BaseModel
{
    protected $name = 'massage_fxq_id_check';

    /**
     * @Desc: 默认二要素注册
     * @param $name
     * @param $id_code
     * @param $uniacid
     * @param $coach_id
     * @return bool
     * @Auther: shurong(龙兵科技)
     * @Time: 2024/10/21 14:54
     */
    public static function defCheck($name, $id_code, $uniacid, $coach_id)
    {

        $p = new PermissionFxq($uniacid);

        $auth = $p->pAuth();

        if (!$auth) {

            return false;
        }
        $check_type = getConfigSetting($uniacid, 'fxq_check_type');

        $model = Fxq::create($uniacid, $coach_id);

        if (is_array($model) && isset($model['code'])) {

            return false;
        }

        if ($check_type == 1) {

            $code = $model->idCheck($name, $id_code);

            if (isset($code['code'])) {

                return false;
            }
        }

        return true;
    }
}