<?php

namespace app\card\model;

use app\BaseModel;
use app\dynamic\model\CardShareGroup;
use think\Model;


class CardValue extends BaseModel
{
    //定义表名
    protected $name = 'longbing_card_value';


    protected static function init ()
    {
        //TODO:初始化内容
    }
    public function getlist($where){
        return $this->where($where)->order('update_time','desc')->find();
    }
    public function bossGetAiValue ( $id,$uniacid )
    {
        $value   = [
            'client'      => 0,        //  获客能力值
            'charm'       => 0,        //  个人魅力值
            'interaction' => 0,        //  客户互动值
            'product'     => 0,        //  产品推广值
            'website'     => 0,        //  官网推广度
            'active'      => 0,        //  销售主动性值
        ];
        $check = $this->getlist([ 'staff_id' => $id ]);
        if ( !$check || ( $check && time() - $check[ 'update_time' ] > 24 * 60 * 60 ) ) {
           $colle = new Collection();
            //  获客能力值
            $client = $colle->getlistCount(['status' => 1, 'to_uid' => $id,]);
            if ( $client > 0 ) {
                $client -= 1;
            }
            $value[ 'client' ] = $client;
           $card_count =  new CardCount();
            //  个人魅力值
            $list1 = $card_count->getCount([ 'sign' => 'praise', 'type' => 1, 'to_uid' => $id ]);
            $list2 = $card_count->getCount( [ 'sign' => 'praise', 'type' => 3, 'to_uid' => $id ]);
            $list3 = $card_count->getCount([ 'sign' => 'copy', 'to_uid' => $id ]);
            $count            = $list1 + $list2+ $list3;
            $value[ 'charm' ] = $count;
            $card_message = new CardMessage();

            //  客户互动值
            $list1                  = $card_message->getCount([ 'user_id' => $id ]);
            $list2                  = $card_message->getCount([ 'target_id' => $id ]);
            $list3                  = $card_count->getCount([ 'sign' => 'view', 'to_uid' => $id ]);
            $count                  = $list1 + $list2 + $list3;
            $value[ 'interaction' ] = $count;
            $extension = new CardExtension();
            $mark = new UserMark();
            $forward = new CardForward();
            $group = new CardShareGroup();

            //  产品推广值
            $list1              =  $extension->getCount([ 'user_id' => $id, 'uniacid' => $uniacid ]);
            $list2              = $mark->getCount([ 'staff_id' => $id, 'uniacid' => $uniacid, 'mark' => 2 ]);
            $list3              = $forward->forwardCount([ 'staff_id' => $id, 'uniacid' => $uniacid, 'type' => 2 ]);

            $where=[['user_id','=',$id],['uniacid','=',$uniacid], ['view_goods','<>',''] ];
            $list4              = $group->getCount($where);
            $count              = $list1 + $list2 + $list3 + $list4;
            $value[ 'product' ] = $count;

            //  官网推广度
            $list1              =  $card_count->getCount([ 'sign' => 'view', 'type' => 6, 'to_uid' => $id ]);
            $list2              = $forward->forwardCount([ 'staff_id' => $id, 'uniacid' => $uniacid, 'type' => 4 ]);
            $count              = $list1 + $list2;
            $value[ 'website' ] = $count;
            $follow = new UserFollow();

            //  销售主动性值
            $list1             = $card_message->getCount([ 'user_id' => $id ]);
            $list2             = $card_message->getCount([ 'target_id' => $id ]);
            $list3             = $follow->getCount([ 'staff_id' => $id ]);
            $list4             = $mark->getCount([ 'staff_id' => $id ]);
            $count             = $list1 + $list2 + $list3+ $list4;
            $value[ 'active' ] = $count;

            $insertData                  = $value;
            $insertData[ 'staff_id' ]    = $id;
            $time                        = time();
            $insertData[ 'update_time' ] = $time;
            $insertData[ 'uniacid' ]     = $uniacid;
            if ( !$check ) {
                $insertData[ 'create_time' ] = $time;
                $this->createRow($insertData);
            } else {
                $insertData[ 'update_time' ] = $time;
                $this->upsave([ 'id' => $check[ 'id' ]],$insertData);
            }
        } else {
            $value = [
                'client'      => $check[ 'client' ],        //  获客能力值
                'charm'       => $check[ 'charm' ],        //  个人魅力值
                'interaction' => $check[ 'interaction' ],    //  客户互动值
                'product'     => $check[ 'product' ],        //  产品推广值
                'website'     => $check[ 'website' ],        //  官网推广度
                'active'      => $check[ 'active' ],        //  销售主动性值
            ];
        }

        $max=array_sum(array($value[ 'active' ],$value[ 'charm' ],$value[ 'client' ],$value[ 'interaction' ],$value[ 'product' ],$value[ 'website' ]));

        $web_name = tabbarName(4,$uniacid);

        $data = [
            [
                'name'=>'销售主动性值',
                'number'=>$value[ 'active' ],

            ],
            [
                'name'=>!empty($web_name)?$web_name.'推广度':'官网推广度',
                'number'=>$value[ 'website' ],
                $bb[] = $value[ 'website' ]
            ],
            [
                'name'=>'产品推广值',
                'number'=>$value[ 'product' ],

            ],
            [
                'name'=>'客户互动值',
                'number'=>$value[ 'interaction' ],

            ],
            [
                'name'=>'获客能力值',
                'number'=>$value[ 'client' ],

            ],
            [
                'name'=>'个人魅力值',
                'number'=>$value[ 'charm' ],

            ],
        ];

        return  ['max'=>$max,'data'=>$data];
    }
    function bossAi ($user_list, $uniacid )
    {
        $max        = [
            0      => 0,        //  销售主动性值
            1       => 0,        //  官网推广度
            2       => 0,        //  产品推广值
            3     => 0,        //  客户互动值
            4     => 0,        //  获客能力值
            5     => 0,        //  个人魅力值
        ];
        $staff_list = User::where( [ [ 'uniacid', '=', $uniacid ], [ 'is_staff', '=', 1 ] ] )
            ->field( [ 'id', 'nickName' ] )
            ->where('id','in',$user_list)
            ->select()
            ->toArray();

        foreach ( $staff_list as $k => $v )
        {
            $total = 0;
            $value = $this->bossGetAiValue( $v[ 'id' ], $uniacid );

            foreach ( $value['data'] as $k2 => $v2 )
            {
                if ( $v2[ 'number' ] > $max[ $k2 ] )
                {
                    $max[ $k2 ] = $v2[ 'number' ];
                }
                $total += $v2[ 'number' ];
            }

            $staff_list[ $k ][ 'number' ] = $value['data'];
            //$staff_list[ $k ][ 'total' ] = $total;
//            $staff_list[ $k ][ 'info' ]  = $info;
        }

//        //  二维数组排序
//        array_multisort( array_column( $staff_list, 'total' ), SORT_DESC, $staff_list );
//
//        $staff_list = array_splice( $staff_list, 0, 3 );


        $data = ['max_data' => $max,'max_number'=>max($max) ];

        return $data;
    }

}