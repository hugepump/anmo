<?php


namespace app\agent\model;


use app\BaseModel;
use app\dynamic\model\CardConfig;

class Cardauth2ConfigModel extends BaseModel
{
    protected $name = 'longbing_cardauth2_config';

    public function cardConfig()
    {
        return $this->hasOne(CardConfig::class, 'uniacid', 'modular_id');
    }
}