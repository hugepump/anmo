<?php
namespace app\massage\model;

use app\BaseModel;
use think\facade\Db;

class ChannelQr extends BaseModel
{
    //定义表名
    protected $name = 'massage_channel_qr';




    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:04
     * @功能说明:添加
     */
    public function dataAdd($data){

        $res = $this->insert($data);

        return $res;

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:05
     * @功能说明:编辑
     */
    public function dataUpdate($dis,$data){

        $res = $this->where($dis)->update($data);

        return $res;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:06
     * @功能说明:列表
     */
    public function dataList($dis,$page){

        $data = $this->where($dis)->order('status desc,id desc')->paginate($page)->toArray();

        return $data;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:43
     * @功能说明:
     */
    public function dataInfo($dis){

        $data = $this->where($dis)->find();

        return !empty($data)?$data->toArray():[];

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-08-01 10:00
     * @功能说明:列表
     */
    public function qrDataList($dis,$page=10,$where=[]){

        $data = $this->alias('a')
            ->join('massage_channel_list b','a.channel_id = b.id AND b.status=2','left')
            ->join('massage_salesman_list c','c.id = a.salesman_id AND c.status=2','left')
            ->join('shequshop_school_admin d','d.id = a.admin_id AND d.status=1','left')
            ->join('massage_service_user_list e','b.user_id = e.id','left')
            ->where($dis)
            ->where(function ($query) use ($where){
                $query->whereOr($where);
            })
            ->field('a.*,c.id as salesman_id,b.user_name as channel_name,b.user_id,c.user_name as salesman_name,d.agent_name,e.nickName')
            ->group('a.id')
            ->order('a.create_time desc,a.id desc')
            ->paginate($page)
            ->toArray();

        if(!empty($data['data'])){

            $cate_model = new ChannelCate();

            foreach ($data['data'] as &$v){

                $v['cate_name'] = $cate_model->where(['id'=>$v['cate_id']])->value('title');
            }
        }

        return $data;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-07-25 18:21
     * @功能说明:后端列表
     */
    public function adminDataList($dis,$dis_sql,$type,$page=1,$limit=10,$start_time='',$end_time = ''){

        $s_model = new ChannelScanQr();

        $l = $s_model->alias('a')
             ->join('massage_channel_qr b','a.qr_id = b.id')
             ->whereColumn('a.create_time','<','b.create_time')
             ->where(['a.type'=>1])
             ->where(['a.open_id'=>''])
             ->field('a.id')
             ->group('a.id')
             ->select()
             ->toArray();

        if(!empty($l)){

            foreach ($l as $k=> $value){

                $up[$k] = [

                    'id'   => $value['id'],

                    'type' => 9,

                    'v'    => 2
                ];
            }

            $s_model->saveAll($up);
        }


        switch ($type){

            case 1:
                $sort = 'scan_old_num';
                break;
            case 2:
                $sort = 'scan_new_num';
                break;
            case 3:
                $sort = 'order_count';
                break;
            case 4:
                $sort = 'order_price';
                break;
            case 5:
                $sort = 'returned_money';
                break;
            case 6:
                $sort = 'channel_cash';
                break;
            default:
                $sort = 'create_time DESC,id';
                break;
        }

        $count = $this->alias('a')
                ->join('massage_channel_list b','a.channel_id = b.id AND b.status = 2','left')
                ->where($dis)
                ->group('a.id')
                ->count();

        $start = $limit;

        $end   = ($page-1)*$limit;

        if(!empty($start_time)&&!empty($end_time)){

            $sql = "SELECT a.*,d.scan_new_num,d.scan_old_num,d.scan_loaction_num,d.scan_offsite_num,e.returned_money,e.order_price,e.order_count,f.channel_cash,b.user_name as channel_name,b.user_id
             FROM `ims_massage_channel_qr` `a`
             LEFT JOIN `ims_massage_channel_list` `b` ON a.channel_id=b.id AND b.status = 2
             LEFT JOIN (SELECT count(CASE WHEN is_new = 1 AND type = 1 THEN open_id ELSE null END ) as scan_new_num,count(CASE WHEN is_new = 0 AND type = 1 THEN id ELSE null END ) as scan_old_num,count(CASE WHEN is_location = 1 AND type = 1 THEN open_id ELSE null END ) as scan_loaction_num,count(CASE WHEN is_location = 2 AND type = 1 THEN open_id ELSE null END ) as scan_offsite_num,qr_id FROM `ims_massage_channel_scan_code_record` where  create_time between $start_time AND $end_time GROUP BY qr_id) AS d ON a.id=d.qr_id
             LEFT JOIN (SELECT sum(true_service_price) as order_price,count(id) as order_count,channel_qr_id,(sum(true_service_price)+sum(material_price)) as returned_money,id FROM `ims_massage_service_order_list` where pay_type = 7 AND create_time between $start_time AND $end_time GROUP BY channel_qr_id) AS e ON a.id=e.channel_qr_id
             LEFT JOIN (SELECT sum(cash) as channel_cash,channel_qr_id FROM `ims_massage_service_order_commission` where status = 2 AND create_time between $start_time AND $end_time GROUP BY channel_qr_id) AS f ON a.id=f.channel_qr_id
             WHERE $dis_sql
             GROUP BY a.id ORDER BY $sort DESC LIMIT $start OFFSET $end";

        }else{

            $sql = "SELECT a.*,d.scan_new_num,d.scan_old_num,d.scan_loaction_num,d.scan_offsite_num,e.returned_money,e.order_price,e.order_count,f.channel_cash,b.user_name as channel_name,b.user_id
             FROM `ims_massage_channel_qr` `a`
             LEFT JOIN `ims_massage_channel_list` `b` ON a.channel_id=b.id AND b.status = 2
             LEFT JOIN (SELECT count(CASE WHEN is_new = 1 AND type = 1 THEN id ELSE null END ) as scan_new_num,count(CASE WHEN is_new = 0 AND type = 1 THEN id ELSE null END ) as scan_old_num,count(CASE WHEN is_location = 1 AND type = 1 THEN open_id ELSE null END ) as scan_loaction_num,count(CASE WHEN is_location = 2 AND type = 1 THEN open_id ELSE null END ) as scan_offsite_num,qr_id FROM `ims_massage_channel_scan_code_record` GROUP BY qr_id) AS d ON a.id=d.qr_id
             LEFT JOIN (SELECT sum(true_service_price) as order_price,count(id) as order_count,channel_qr_id,(sum(true_service_price)+sum(material_price)) as returned_money,id FROM `ims_massage_service_order_list` where pay_type = 7 GROUP BY channel_qr_id) AS e ON a.id=e.channel_qr_id
             LEFT JOIN (SELECT sum(cash) as channel_cash,channel_qr_id FROM `ims_massage_service_order_commission` where status = 2 GROUP BY channel_qr_id) AS f ON a.id=f.channel_qr_id
             WHERE $dis_sql
             GROUP BY a.id ORDER BY $sort DESC LIMIT $start OFFSET $end";
        }

        $data = Db::query($sql);

        if(!empty($data)){

            $cate_model = new ChannelCate();

            $user_model = new User();

            foreach ($data as &$v){
                $v['nickName'] = $user_model->where(['id'=>$v['user_id']])->value('nickName');
                $v['scan_new_num'] = !empty($v['scan_new_num'])?$v['scan_new_num']:0;
                $v['scan_old_num'] = !empty($v['scan_old_num'])?$v['scan_old_num']:0;
                $v['scan_loaction_num']= !empty($v['scan_loaction_num'])?$v['scan_loaction_num']:0;
                $v['scan_offsite_num'] = !empty($v['scan_offsite_num'])?$v['scan_offsite_num']:0;
                $v['returned_money'] = !empty($v['returned_money'])?round($v['returned_money'],2):0;
                $v['order_price']  = !empty($v['order_price'])?round($v['order_price'],2):0;
                $v['order_count']  = !empty($v['order_count'])?$v['order_count']:0;
                $v['channel_cash'] = !empty($v['channel_cash'])?round($v['channel_cash'],2):0;
                $v['cate_name'] = $cate_model->where(['id'=>$v['cate_id']])->value('title');
                $v['return_rate'] = $v['cost']>0?round($v['returned_money']/$v['cost']*100,2):0;
            }
        }

        $arr['data'] = $data;

        $arr['total']= $count;

        $arr['current_page'] = $page;

        $arr['per_page']     = $limit;

        $arr['last_page']    = ceil($arr['total']/$limit);

        $sql = "SELECT ifnull(sum(scan_qr_count),0) as scan_qr_count
             FROM `ims_massage_channel_qr` `a`
             LEFT JOIN `ims_massage_channel_list` `b` ON a.channel_id=b.id AND b.status = 2
             LEFT JOIN (SELECT count(id) as scan_qr_count,qr_id FROM `ims_massage_channel_scan_code_record` GROUP BY qr_id) AS d ON a.id=d.qr_id 
             WHERE $dis_sql
           ";

        $scan_qr_count = Db::query($sql);

        $arr['scan_qr_count'] = $scan_qr_count;

        return $arr;
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-07-25 15:11
     * @功能说明:二维码画图将编号加上
     */
    public function channelQrImg($data){

        $im    = imagecreatetruecolor(460, 500);

        $color = imagecolorallocate($im, 255, 255, 255);

        imagefill($im, 0, 0, $color);

        $font_file = APP_PATH . "Common/extend/vista.ttf";

        list( $l_w, $l_h ) = getimagesize( $data[ 'qr' ] );

        $ext = longbingSingleGetImageExtWx( $data[ 'qr' ] );

        if ( $ext == 'jpg' || $ext == 'jpeg' )
        {
            $logoImg = @imagecreatefromjpeg( $data[ 'qr' ] );
        }
        else if ( $ext == 'png' )
        {
            $logoImg = @imagecreatefrompng( $data[ 'qr' ] );
        }
        else
        {
            return false;
        }
        imagecopyresized($im, $logoImg, 0, 0, 0, 0, 450, 450, $l_w, $l_h);

        $black = imagecolorallocate($logoImg, 0, 0, 0);

        imagettftext($im, 25, 0, 100, 470, $black, $font_file, $data['code']); //现价

        $path = MATER_UPLOAD_PATH.date('Y-m-d',time()).'/img';

        if(!is_dir($path)){

            mkdir($path,0777,true);
        }

        $imageName = $path."/25220_".date("His",time())."_".rand(1111,9999).'.jpg';

        imagepng($im,$imageName);

        imagedestroy($im);

        return str_replace(FILE_UPLOAD_PATH,HTTPS_PATH,$imageName);
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-07-26 10:16
     * @功能说明:初始化没有画图的二维码
     */
    public function batchChannelQrImgInit($uniacid){

        $dis[] = ['uniacid','=',$uniacid];

        $dis[] = ['status','>',-1];

        $dis[] = ['qr_img','=',''];

        $data = $this->where($dis)->order('id desc')->limit(20)->select()->toArray();

        if(!empty($data)){

            foreach ($data as $v){

                $qr = $this->channelQrImg($v);

                $this->dataUpdate(['id'=>$v['id']],['qr_img'=>$qr]);

            }
        }

        return true;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-09-18 10:35
     * @功能说明:获取渠道码id
     */
    public function getQrID($name,$uniacid){

        $id = $this->where('title','like','%'.$name.'%')->where(['uniacid'=>$uniacid])->column('id');

        return $id;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-11-13 18:25
     * @功能说明:扫码所在地注册
     */
    public function locationLogin($user_id){

        $user_model = new User();

        $user = $user_model->dataInfo(['id'=>$user_id]);

        if(empty($user['province'])){

            return true;
        }

        $find = Db::name('massage_channel_scan_code_record')->where(['open_id'=>$user['openid'],'is_location'=>0])->order('id desc')->find();

        if(empty($find)){

            return true;
        }

        if($find['province']==$user['province']&&$find['city']==$user['city']){

            Db::name('massage_channel_scan_code_record')->where(['id'=>$find['id']])->update(['is_location'=>1,'user_id'=>$user_id]);
        }else{
            Db::name('massage_channel_scan_code_record')->where(['id'=>$find['id']])->update(['is_location'=>2,'user_id'=>$user_id]);

        }
        return true;
    }














}