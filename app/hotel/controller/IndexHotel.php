<?php
namespace app\hotel\controller;
use app\AdminRest;
use app\ApiRest;
use app\card\model\Config;
use app\hotel\model\HotelList;
use app\hotel\model\HotelService;
use app\massage\model\ActionLog;
use app\massage\model\Admin;
use app\massage\model\AdminRole;
use app\massage\model\CateList;
use app\massage\model\Coach;
use app\massage\model\Comment;
use app\massage\model\Service;
use app\massage\model\ServicePositionConnect;
use app\node\model\RoleAdmin;
use app\node\model\RoleList;
use app\node\model\RoleNode;
use app\package\info\PermissionPackage;
use app\store\model\StoreCate;
use app\store\model\StoreList;
use app\store\model\StorePackage;
use think\App;



class IndexHotel extends ApiRest
{

    protected $model;

    public function __construct(App $app) {

        parent::__construct($app);

        $this->model = new HotelList();
    }


    /**
     * @author chenniang
     * @DataTime: 2024-11-18 10:20
     * @功能说明:酒店列表
     */
    public function hotelList(){

        $input = $this->_param;

        $hotel_model = new HotelList();

        $dis[] = ['a.uniacid','=',$this->_uniacid];

        $dis[] = ['a.status','=',2];

        if(!empty($input['title'])){

            $dis[] = ['a.title','like','%'.$input['title'].'%'];
        }

        if(!empty($input['star'])){

            $dis[] = ['a.star','=',$input['star']];
        }

        $where = [];

        if(!empty($input['city'])){

            $where[] = ['a.city','=',$input['city']];

            $where[] = ['a.area','=',$input['city']];
        }

        $dis1 = [

            ['a.admin_id' ,'=', 0],
        ];

        $dis2 =[

            ['a.admin_id' ,'<>', 0],

            ['b.hotel_auth','=',  1]
        ];

        $lat = $input['lat'];

        $lng = $input['lng'];

        $alh = 'ACOS(SIN(('.$lat.' * 3.1415) / 180 ) *SIN((a.lat * 3.1415) / 180 ) +COS(('.$lat.' * 3.1415) / 180 ) * COS((a.lat * 3.1415) / 180 ) *COS(('.$lng.' * 3.1415) / 180 - (a.lng * 3.1415) / 180 ) ) * 6378.137*1000 as distance';

        $sort = $input['sort']==1?'asc':'desc';

        $data = $hotel_model->alias('a')
                ->join('shequshop_school_admin b','a.admin_id = b.id','left')
            ->where($dis)->where(function ($query) use ($where){
            $query->whereOr($where);
        })->where(function ($query) use ($dis1,$dis2){
                $query->whereOr([$dis1,$dis2]);
            })->field(['a.id,a.title,a.cover,a.star,a.address,a.min_price',$alh])->order("distance $sort,a.id desc")->paginate(10)->toArray();

        if(!empty($data['data'])){

            foreach ($data['data'] as &$v){

                $v['distance'] = distance_text($v['distance']);
            }
        }

        return $this->success($data);
    }


    /**
     * @author chenniang
     * @DataTime: 2024-11-18 10:35
     * @功能说明:
     */
    public function hotelInfo(){

        $input = $this->_param;

        $dis= [

            'id' => $input['id']
        ];

        $data = $this->model->dataInfo($dis);

        $data['service'] = HotelService::getService($data);

        $service_model = new Service();

       // if(!empty($this->getUserId())){
            //获取服务的会员信息
            $data['service'] = $service_model->giveListMemberInfo($data['service'],$this->_uniacid,$this->getUserId(),2);
            //会员价
            $data['service'] = giveMemberPrice($this->_uniacid,$data['service']);
       // }

        return $this->success($data);
    }






}
