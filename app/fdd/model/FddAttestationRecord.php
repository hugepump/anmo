<?php
namespace app\fdd\model;

use app\admin\model\Admin;
use app\BaseModel;
use app\massage\model\User;
use longbingcore\wxcore\Fdd;
use think\facade\Db;

class FddAttestationRecord extends BaseModel
{
    //status 1注册 2实名认证 3绑定
    //定义表名
    protected $name = 'massage_fdd_attestation_record';


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-12 15:25
     * @功能说明:用户实名认证
     */
    public function getAttestationInfo($user_id,$uniacid,$type=1){
        //注册账号
        $record = $this->registerUser($user_id,$uniacid,$type);

        return $record;
    }


    /**
     * @param $user_id
     * @param $uniacid
     * @功能说明:获取认证地址
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-12 16:12
     */
    public function getPersonVerifyUrl($user_id,$uniacid){
        //注册账号
        $record = $this->registerUser($user_id,$uniacid);

        if(!empty($record['code'])){

            return $record;
        }

        $core = new Fdd($uniacid);

        $res = $core->getPersonVerifyUrl($record['customer_id']);

        if(isset($res['code'])&&$res['code']!=1){

            $msg = !empty($res['msg'])?$res['msg']:'注册失败';

            return ['code'=>'500','msg'=>$msg];

        }

        $this->dataUpdate(['customer_id'=>$record['customer_id']],['transactionNo'=>$res['data']['transactionNo']]);

        return base64_decode($res['data']['url']);

    }


    /**
     * @param $user_id
     * @param $uniacid
     * @功能说明:获取企业认证地址
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-12 16:12
     */
    public function getCompanyVerifyUrl($user_id,$uniacid){
        //注册账号
        $record = $this->registerUser($user_id,$uniacid,2);

        if(!empty($record['code'])){

            return $record;
        }

        $core = new Fdd($uniacid);

        $res = $core->getCompanyVerifyUrl($record['customer_id']);

        if(isset($res['code'])&&$res['code']!=1){

            $msg = !empty($res['msg'])?$res['msg']:'注册失败';

            return ['code'=>'500','msg'=>$msg];

        }

        $this->dataUpdate(['customer_id'=>$record['customer_id']],['transactionNo'=>$res['data']['transactionNo']]);

        return base64_decode($res['data']['url']);

    }



    /**
     * @param $user_id
     * @param $uniacid
     * @功能说明:绑定实名认证
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-12 16:29
     */
    public function ApplyCert($user_id,$uniacid,$type=1){

        $dis = [

            'uniacid' => $uniacid,

            'user_id' => $user_id,

            'type'    => $type
        ];
        //查询实名认证记录
        $record = $this->dataInfo($dis);

        if(empty($record['customer_id'])){

            return ['code'=>500,'msg'=>'请先注册账号'];
        }

        if(empty($record['transactionNo'])){

            return ['code'=>500,'msg'=>'请先实名认证'];
        }

        if($record['status']!=3){

            $core = new Fdd($uniacid);

            $res = $core->ApplyCert($record['customer_id'],$record['transactionNo']);

            if(isset($res['code'])&&$res['code']==1){

                $this->dataUpdate($dis,['status'=>3]);

                return true;

            }else{

                $msg = !empty($res['msg'])?$res['msg']:'注册失败';

                return ['code'=>'500','msg'=>$msg];

            }
        }

        return $record;

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-12 16:37
     * @功能说明:上传合同
     */
    public function Uploaddocs($user_id,$uniacid,$admin_id,$type=1){

        $dis = [

            'uniacid' => $uniacid,

            'user_id' => $user_id,

            'type'    => $type
        ];
        //查询实名认证记录
        $user_record = $this->dataInfo($dis);

        if(empty($user_record['customer_id'])){

            return ['code'=>'500','msg'=>'请先认证'];

        }

        $dis = [];

        $dis[] = ['uniacid','=',$uniacid];

        $dis[] = ['user_id','=',$user_id];

        $dis[] = ['admin_id','=',$admin_id];

        $dis[] = ['status','>',-1];

        $record_model = new FddAgreementRecord();

        $admin_model = new \app\massage\model\Admin();

        $data = $record_model->dataInfo($dis);

        $contract_id = orderCode();

        if(!empty($admin_id)){

            $admin = $admin_model->dataInfo(['id'=>$admin_id]);
        }else{

            $admin = $admin_model->dataInfo(['uniacid'=>$uniacid,'is_admin'=>1]);

        }

        $core = new Fdd($uniacid);

        $res = $core->Uploaddocs($contract_id,$admin['agreement_title'],$admin['agreement']);

        if(isset($res['code'])&&$res['code']!=1000){

            $msg = !empty($res['msg'])?$res['msg']:'上传合同失败';

            return ['code'=>'500','msg'=>$msg];

        }

        $insert = [

            'uniacid' => $uniacid,

            'user_id' => $user_id,

            'admin_id'=> $admin_id,

            'contract_id' => $contract_id,

            'agreement_title' => $admin['agreement_title'],

            'customer_id' => $user_record['customer_id'],

        ];

        if(empty($data)){

            $record_model->dataAdd($insert);
        }else{

            $record_model->dataUpdate(['id'=>$data['id']],$insert);
        }

        return true;

    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-12 16:37
     * @功能说明:上传合同
     */
    public function companyUploaddocs($coach_info,$uniacid,$admin_id){

        $dis = [

            'uniacid' => $uniacid,

            'user_id' => $admin_id,

            'type'    => 2
        ];
        //查询实名认证记录
        $user_record = $this->dataInfo($dis);

        if(empty($user_record['customer_id'])){

            return ['code'=>'500','msg'=>'请先认证'];

        }
        $dis = [];

        $dis[] = ['uniacid','=',$uniacid];

        $dis[] = ['user_id','=',$coach_info['user_id']];

        $dis[] = ['admin_id','=',$admin_id];

        $dis[] = ['status','>',0];

        $record_model = new FddAgreementRecord();

        $admin_model = new \app\massage\model\Admin();

        $data = $record_model->dataInfo($dis);

        if(!empty($data)){

            return true;
        }

        $contract_id = orderCode();

        if($admin_id==0){

            $admin = $admin_model->dataInfo(['is_admin'=>1]);
        }else{

            $admin = $admin_model->dataInfo(['id'=>$admin_id]);
        }

        $admin['agreement_title'] = $coach_info['coach_name'].'-'.$admin['agreement_title'];

        $core = new Fdd($uniacid);

        $res = $core->Uploaddocs($contract_id,$admin['agreement_title'],$admin['agreement']);

        if(isset($res['code'])&&$res['code']!=1000){

            $msg = !empty($res['msg'])?$res['msg']:'上传合同失败';

            return ['code'=>'500','msg'=>$msg];

        }

        $insert = [

            'uniacid' => $uniacid,

            'user_id' => $coach_info['user_id'],

            'admin_id'=> $admin_id,

            'coach_id'=> $coach_info['id'],

            'contract_id' => $contract_id,

            'agreement_title' => $admin['agreement_title'],

            'company_customer_id' => $user_record['customer_id'],

            'status' => 1

        ];

        if(empty($data)){

            $record_model->dataAdd($insert);
        }else{

            $record_model->dataUpdate(['id'=>$data['id']],$insert);
        }

        return true;

    }
    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-12 17:00
     * @功能说明:手动签署合同
     */
    public function Extsign($user_id,$uniacid,$admin_id){

        $dis[] = ['uniacid','=',$uniacid];

        $dis[] = ['user_id','=',$user_id];

        $dis[] = ['admin_id','=',$admin_id];

        $dis[] = ['status','>',-1];

        $record_model = new FddAgreementRecord();

        $data = $record_model->dataInfo($dis);

        if(empty($data)){

            return ['code'=>'500','msg'=>'请先上传合同'];

        }

        $core = new Fdd($uniacid);

        $transaction_id = orderCode();

        $res = $core->Extsign($transaction_id,$data['contract_id'],$data['customer_id'],$data['agreement_title']);

        if(isset($res['code'])&&$res['code']!=1){

            $msg = !empty($res['msg'])?$res['msg']:'上传合同失败';

            return ['code'=>'500','msg'=>$msg];

        }

        $record_model->dataUpdate(['id'=>$data['id']],['transaction_id'=>$transaction_id]);

        return $res;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-12 17:00
     * @功能说明:手动签署合同
     */
    public function ExtsignCompany($user_id,$uniacid,$admin_id){

        $dis[] = ['uniacid','=',$uniacid];

        $dis[] = ['user_id','=',$user_id];

        $dis[] = ['admin_id','=',$admin_id];

        $dis[] = ['status','>',0];

        $record_model = new FddAgreementRecord();

        $data = $record_model->dataInfo($dis);

        if(empty($data)){

            return ['code'=>'500','msg'=>'请先上传合同'];

        }

        if($data['status']>1){

            return ['code'=>'500','msg'=>'你已经签署过合同'];
        }

        $core = new Fdd($uniacid);

        $transaction_id = orderCode();

        $res = $core->Extsign($transaction_id,$data['contract_id'],$data['company_customer_id'],$data['agreement_title'],2);

        if(isset($res['code'])&&$res['code']!=1){

            $msg = !empty($res['msg'])?$res['msg']:'上传合同失败';

            return ['code'=>'500','msg'=>$msg];

        }

        $record_model->dataUpdate(['id'=>$data['id']],['company_transaction_id'=>$transaction_id]);

        return $res;
    }



    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-12 17:00
     * @功能说明:手动签署合同
     */
    public function ExtsignAuto($user_id,$uniacid,$admin_id){

        $dis[] = ['uniacid','=',$uniacid];

        $dis[] = ['user_id','=',$user_id];

        $dis[] = ['admin_id','=',$admin_id];

        $dis[] = ['status','>',-1];

        $record_model = new FddAgreementRecord();

        $data = $record_model->dataInfo($dis);

        if(empty($data)){

            return ['code'=>'500','msg'=>'请先上传合同'];

        }

        if($data['status']>1){

            return ['code'=>'500','msg'=>'你已经签署过合同,请刷新'];
        }

        $core = new Fdd($uniacid);

    //    $res = $core->CancelExtsignAutoPage($data['company_customer_id']);

        $find = $core->findAutoAuth($data['company_customer_id']);
        //没有
        if($find['data']['status']==0){

            $find = $core->getAutoAuth($data['company_customer_id']);

            return $find;

        }

        $transaction_id = orderCode();

        $res = $core->ExtsignAuto($transaction_id,$data['contract_id'],$data['company_customer_id'],$data['agreement_title']);

        if(isset($res['code'])&&$res['code']!='1000'){

            $msg = !empty($res['msg'])?$res['msg']:'上传合同失败';

            return ['code'=>'500','msg'=>$msg];

        }

        if(isset($res['code'])&&$res['code']=='1000'){

            $record_model->dataUpdate(['id'=>$data['id']],['status'=>2]);
        }

        $record_model->dataUpdate(['id'=>$data['id']],['company_transaction_id'=>$transaction_id]);

        return 'https://'.$_SERVER['HTTP_HOST'].'/#/sys/fdd-record';
      //  return $res['viewpdf_url'];
    }





    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-12 17:09
     * @功能说明:合同归档
     */
    public function ContractFiling($user_id,$uniacid,$admin_id){

        $dis[] = ['uniacid','=',$uniacid];

        $dis[] = ['user_id','=',$user_id];

        $dis[] = ['admin_id','=',$admin_id];

        $dis[] = ['status','>',-1];

        $record_model = new FddAgreementRecord();

        $data = $record_model->dataInfo($dis);

        if(empty($data)){

            return ['code'=>'500','msg'=>'请先上传合同'];

        }

        $core = new Fdd($uniacid);

        $res = $core->ContractFiling($data['contract_id']);

        if(isset($res['code'])&&$res['code']!=1000){

            $msg = !empty($res['msg'])?$res['msg']:'归档失败';

            return ['code'=>'500','msg'=>$msg];

        }

        return true;
    }













    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2023-04-12 15:30
     * @功能说明:注册账号
     */
    public function registerUser($user_id,$uniacid,$type=1){

        $dis = [

            'uniacid' => $uniacid,

            'user_id' => $user_id,

            'type'    => $type
        ];
        //查询实名认证记录
        $record = $this->dataInfo($dis);

        if(empty($record)){

            $core = new Fdd($uniacid);
            //向发大大注册账号
            $res = $core->registerAccount($user_id,$type);

            if(isset($res['code'])&&$res['code']==1){

                $customer_id = $res['data'];

            }else{

                $msg = !empty($res['msg'])?$res['msg']:'注册失败';

                return ['code'=>'500','msg'=>$msg];

            }

            $insert = [

                'uniacid' => $uniacid,

                'user_id' => $user_id,

                'customer_id' => $customer_id,

                'type'   => $type

            ];

            $this->dataAdd($insert);

            $record = $this->dataInfo($dis);

        }

        return $record;

    }







    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-09-29 11:04
     * @功能说明:添加
     */
    public function dataAdd($data){

        $data['create_time'] = time();

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

        $data = $this->where($dis)->where('status','>',-1)->find();

        return !empty($data)?$data->toArray():[];

    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-03-19 16:08
     * @功能说明:开启默认
     */
    public function updateOne($id){

        $user_id = $this->where(['id'=>$id])->value('user_id');

        $res = $this->where(['user_id'=>$user_id])->where('id','<>',$id)->update(['status'=>0]);

        return $res;
    }





    public function companySign(){



    }










}