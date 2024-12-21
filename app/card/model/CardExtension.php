<?php

namespace app\card\model;

use app\BaseModel;
use app\member\model\Member;
use app\shop\model\AdminShopSpePrice;
use app\shop\model\IndexUserInfo;
use app\shop\model\IndexUserShop;
use think\facade\Db;
use think\Model;


class CardExtension extends BaseModel
{
    //定义表名
    protected $name = 'longbing_card_extension';


    protected static function init ()
    {
        //TODO:初始化内容
    }

    /**
     * @Purpose: 名片的标签列表
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function cardExtensionList ( $staff_id, $uniacid )
    {


        $user_model = new IndexUserInfo();

        $company_id = $user_model->getCompanyId(['fans_id'=>$staff_id],$uniacid);

        $member_model= new Member();

        $arr         = [];

        if($member_model->getAuth($uniacid)==true){

            $arr[] = ['b.is_member','=',0];

        }

        $dis[] = ['d.company_id','=',$company_id];

        $dis[] = ['b.public_goods','=',1];

        $config = longbingGetAppConfig($uniacid);
        //自选
        if($config['myshop_switch']==1){
            $user_shop = new IndexUserShop();

            $my_goods  = $user_shop->MyGoodList(['user_id'=>$staff_id,'uniacid'=>$uniacid]);

            $arrs[] = ['a.id','in',$my_goods];

        }else{

            $arrs[] = ['a.index_show', '=', 1];
        }

        $data = self::where( [ [ 'a.user_id', '=', $staff_id ], [ 'a.status', '=', 1 ], [ 'b.status', '=', 1 ], [ 'a.uniacid', '=', $uniacid ] ] )
                    ->where($arr)
                    ->alias( 'a' )
                    ->field( [ 'a.id as e_id','b.id', 'b.name', 'b.cover', 'b.price', 'b.status', 'b.unit', 'b.recommend', 'b.is_collage' ]
                    )
                    ->where(function ($query) use ($dis){
                        $query->whereOr($dis);
                    })
                    ->join('longbing_card_goods b', 'a.goods_id = b.id')
                    ->join('longbing_card_user_info c','a.user_id = c.fans_id')
                    ->join('longbing_card_company_goods d' ,'c.company_id = d.company_id and a.goods_id = d.goods_id','left')
//                    ->order( [ 'a.id' => 'desc' ] )
                    ->order('a.status desc,b.recommend desc,b.top desc,b.id desc,b.update_time desc')
                    ->select()
                    ->toArray();



         if($data){
             $spe_price = new AdminShopSpePrice();
             foreach ($data as $key=>$val){
                 $o_dis   = [];
                 $o_dis[] = ['status','=',1];
                 $o_dis[] = ['goods_id','=',$val['id']];
                 $data[$key]['price_text'] = $spe_price->where($o_dis)->min('price');
                 $o_dis[] = ['original_price','<>',0];
                 $data[$key]['original_price'] = $spe_price->where($o_dis)->min('original_price');
             }
         }
        $data = transImagesOne($data, ['cover']);
        $data = formatNumberPrice($data);



        return $data;
    }
    public function getlist($dis){
        $data = $this
            ->alias('a')
            ->join('longbing_card_user_info b','a.user_id = b.fans_id')
            ->join('longbing_card_company_goods c' ,'b.company_id = c.company_id and a.goods_id = c.goods_id')
            ->join('longbing_card_goods d' ,'a.goods_id = d.id and d.status = 1')
            ->where($dis)
            ->field(['a.*'])
            ->order('a.create_time','desc')
            ->select()
            ->toArray();
        return $data;
    }
    public function getCount($where){
        return $this->where($where)->count();
    }


}