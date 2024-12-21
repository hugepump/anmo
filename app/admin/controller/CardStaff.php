<?php
namespace app\admin\controller;
use app\admin\model\CardUser;
use app\Rest;
use think\App;

class CardStaff extends Rest
{
    protected $model;
    public function __construct(App $app) {
        parent::__construct($app);
        $this->model = new CardUser();
    }
}