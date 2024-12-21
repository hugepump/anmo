<?php


namespace app\agent\model;


use app\BaseModel;
use app\dynamic\model\CardConfig;

class Cardauth2ArticleModel extends BaseModel
{
    protected $name = 'longbing_cardauth2_article';

    public function cardConfig()
    {
        return $this->hasOne(CardConfig::class, 'uniacid', 'modular_id');
    }

}