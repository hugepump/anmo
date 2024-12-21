<?php

namespace app\card\model;

use app\BaseModel;
use think\Model;


class Company extends BaseModel
{
    //定义表名
    protected $name = 'longbing_card_company';


    protected static function init ()
    {
        //TODO:初始化内容
    }
	
	/**
	 *  @Purpose: 创建公司
	 *  @Author: yangqi
	 *  @create time : 2019年11月25日20:28:45
	 */
	
	public function createRow($data)
	{
		$data['create_time'] = time();
		$result = $this->save($data);
		return !empty($result);
	}
	
    /**
     * @Purpose: 获取公司信息
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function getInfo ( $uniacid = 0, $userId = 0, $company_id = 0 )
    {
        $company = [];

        if ( $userId )
        {
            $userInfo = UserInfo::where( [ [ 'fans_id', '=', $userId ] ] )
                                ->find();
            if ( $userInfo && $userInfo[ 'company_id' ] )
            {
                $company = self::where( [ [ 'id', '=', $userInfo[ 'company_id' ] ], [ 'status', '=', 1 ] ] )
                    //                               ->withoutField( [ 'auth_code' ] )
                               ->find();
            }
        }

        if ( $company_id )
        {
            $company = self::where( [ [ 'id', '=', $company_id ], [ 'status', '=', 1 ] ] )
                //                           ->withoutField( [ 'auth_code' ] )
                           ->find();
        }

        if ( is_array( $company ) && $company = [] )
        {
            $company = self::where( [ [ 'status', '=', 1 ], [ 'uniacid', '=', $uniacid ] ] )
                //                           ->withoutField( [ 'auth_code' ] )
                           ->find();
        }

        if ( $company )
        {
            $company               = $company->toArray();
            $company               = transImages( $company, [ 'culture' ] );
            $company               = transImagesOne( $company, [ 'logo', 'desc' ] );
            $company[ 'shop_bg' ]  = $company[ 'desc' ];
            $company[ 'carousel' ] = $company[ 'culture' ];
        }
        return $company;
    }


    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-08-21 14:02
     * @功能说明:替换顶级公司的名字
     */

    public function changeTopName($company){

        if(!empty($company['top_id'])){

            $dis = [

                'status' => 1,

                'id'     => $company['top_id'],

                'uniacid'=> $company['uniacid']

            ];

            $top_name = $this->where($dis)->value('name');

            $short_name = $this->where($dis)->value('short_name');

            if(!empty($top_name)){

                $company['name'] = $top_name;

                $company['short_name'] = $short_name;

            }
        }

        return $company;
    }

    /**
     * @Purpose: 根据用户id返回公司列表
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function getListByUser ( $user_id, $uniacid, $is_all = 0, $company_id = 0 )
    {
        if ( $is_all )
        {
            $companyList = self::where( [ [ 'uniacid', '=', $uniacid ], [ 'status', '=', 1, ], [ 'pid', '=', 0 ] ] )
                               ->field( [ 'id', 'pid', 'name', 'logo', 'addr' ] )
                                ->order('top desc,pid asc,id desc')
                               ->select()
                               ->toArray();

            $sonList = self::where( [ [ 'uniacid', '=', $uniacid ], [ 'status', '=', 1, ], [ 'pid', '<>', 0 ] ] )
                           ->field( [ 'id', 'pid', 'name', 'logo', 'addr' ] )
                            ->order('top desc,pid asc,id desc')
                           ->select()
                           ->toArray();

            $companyList = self::handleCompanyLevel( $companyList, $sonList );

        }
        else
        {
            $modelCardBoss = new CardBoss();
            $check         = $modelCardBoss->where( [ [ 'user_id', '=', $user_id ], [ 'uniacid', '=', $uniacid ],
                                                        [ 'boss', '<>', '' ] ]
            )
                                           ->field( [ 'boss' ] )
                                           ->find();
            if ( !$check )
            {
                return $this->getListByUser( $user_id, $uniacid, $is_all = 1 );
            }

            $tmpArr      = explode( ',', $check[ 'boss' ] );
            $companyList = self::where( [ [ 'uniacid', '=', $uniacid ], [ 'status', '=', 1, ], [ 'id', 'in', $tmpArr ] ] )
                               ->field( [ 'id', 'pid', 'name', 'logo', 'addr' ] )
                               ->order('top desc,pid asc,id desc')
                               ->select()
                               ->toArray();
            foreach ( $companyList as $index => $item )
            {
                $companyList[ $index ][ 'sec' ] = [];
            }

        }

        $companyList = transImagesOne( $companyList, [ 'logo' ] );

        foreach ( $companyList as $index => $item )
        {
            $companyList[ $index ][ 'selected' ] = 0;
            if ( isset( $item[ 'id' ] ) && $item[ 'id' ] == $company_id )
            {
                $companyList[ $index ][ 'selected' ] = 1;
            }
        }

        return $companyList;
    }

    /**
     * @Purpose: 处理公司层级--无限级
     *
     * @Param: array $list 顶级公司列表
     * @Param: array $son 部门列表
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function handleCompanyLevel ( $list, $son, $item_name = 'sec' )
    {
        foreach ( $list as $index => $item )
        {
            if ( !isset( $list[ $index ][ $item_name ] ) )
            {
                $list[ $index ][ $item_name ] = array();
            }
            foreach ( $son as $key => $value )
            {
                if ( $item[ 'id' ] == $value[ 'pid' ] )
                {
                    array_push( $list[ $index ][ $item_name ], $value );
                    unset( $son[ $key ] );
                }
            }

            if ( $list[ $index ][ $item_name ] && count( $list[ $index ][ $item_name ] ) && count( $son ) )
            {

                $list[ $index ][ $item_name ] = self::handleCompanyLevel( $list[ $index ][ $item_name ], $son, $item_name );
            }
        }


        return $list;
    }

    /**
     * @Purpose: 改变公司状态
     *
     * @Param：$company_id   number  公司id
     * @Param：$method   number  操作类型  0 = 下架 1 = 上架 2 = 删除
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function updateStatus ( $company_id, $method = 0 )
    {
        $result = false;
        switch ( $method )
        {
            case 0:
                $result = self::update( [ 'status' => 0 ], [ 'id' => $company_id ] );
                $this->updateSonCompanyInfo( $company_id, [ 'status' => 0 ] );
                break;
            case 1:
                $result = self::update( [ 'status' => 1 ], [ 'id' => $company_id ] );
                break;
            case 2:
                $result = self::update( [ 'status' => -1 ], [ 'id' => $company_id ] );
                $this->updateSonCompanyInfo( $company_id, [ 'status' => -1 ] );
                break;
            default:
                return false;
        }

        if ( $result === false )
        {
            return false;
        }

        return $result;
    }

    /**
     * @Purpose: 改变下级公司信息
     *
     * @Param：$company_id   number  公司id
     * @Param：$data   array  修改内容
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function updateSonCompanyInfo ( $company_id, $data )
    {
        $list = self::where( [ [ 'pid', '=', $company_id ] ] )
                    ->select()
                    ->toArray();

        foreach ( $list as $index => $item )
        {
            $check = self::where( [ [ 'pid', '=', $item[ 'id' ] ] ] )
                         ->count();
            if ( $check )
            {
                $this->updateSonCompanyInfo( $item[ 'id' ], $data );
            }
            self::update( $data, [ 'id' => $item[ 'id' ] ] );
        }

        return true;
    }

    /**
     * @Purpose: 获取公司和部门名
     *
     * @Param：$company_id   number  公司id
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function getCompanyAndDepartmentName ( $company_id )
    {
        $info = self::where( [ [ 'id', '=', $company_id ] ] )
                    ->find();

        if ( !$info )
        {
            return [ '未设置公司', '未设置部门' ];
        }

        if ( $info[ 'pid' ] == 0 )
        {
            return [ $info[ 'name' ], '未设置部门' ];
        }

        $topCompany = $this->getTopCompany( $info[ 'pid' ] );

        $companyName = '未设置公司';

        if ( $topCompany && $topCompany[ 'status' ] )
        {
            $companyName = $topCompany[ 'name' ];
        }

        return [ $companyName, $info[ 'name' ] ];

    }

    protected function getTopCompany ( $pid )
    {
        $info = self::where( [ [ 'id', '=', $pid ] ] )
                    ->find();
        if ( !$info )
        {
            return '';
        }

        if ( $info[ 'pid' ] )
        {
            return $this->getTopCompany( $info[ 'pid' ] );
        }

        return $info;
    }
    
    public function getCompany($filter ,$field = [])
    {
        if(isset($filter['company_id'])) 
        {
            $filter['id'] = $filter['company_id'];
            unset($filter['company_id']);
        }
        $result = $this->where($filter);
        if(!empty($field)) $result = $result->field($field);
        $result = $result->find();
        if(!empty($result)) $result = $result->toArray();
        return $result;
    }

}