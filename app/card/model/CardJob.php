<?php

namespace app\card\model;

use app\BaseModel;
use think\Model;


class CardJob extends BaseModel
{
    //定义表名
    protected $name = 'longbing_card_job';

    

    protected static function init ()
    {
        //TODO:初始化内容
    }

    /**
     * @Purpose: 根据员工获取工具列表
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function getListByUser ( $card_id = 0, $uniacid )
    {
        $list = self::where( [ [ 'uniacid', '=', $uniacid ], [ 'status', '=', 1 ] ] )
                    ->field( [ 'id', 'name' ] )
                    ->order( 'top', 'desc' )
                    ->select()
                    ->toArray();

        if ( !$list )
        {
            $list = $this->initCardJob( $uniacid );
        }

        foreach ( $list as $index => $item )
        {
            $list[ $index ][ 'selected' ] = 0;
            if ( isset( $item[ 'id' ] ) && $item[ 'id' ] == $card_id )
            {
                $list[ $index ][ 'selected' ] = 1;
            }
        }

        return $list;
    }

    /**
     * @Purpose: 初始化名片样式
     *
     * @Author: zzf
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function initCardJob ( $uniacid )
    {
        $data   = [ [ 'name' => '首席服务官', 'uniacid' => $uniacid ] ];
        $result = self::createRows( $data );
        return $result->toArray();
    }
    
//  public function getJob($filter)
//  {
//      $result = $this->where($filter)->find();
//      if(!empty($result)) $result = $result->toArray();
//      return $result;
//  }
    
    /**
     * @Purpose: 创建职位
     *
     * @Author: yangqi
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function createJob($data){
        $data['create_time'] = time();
        $result = $this->save($data);
        return !empty($result);
    }
    /**
     * @Purpose: 更新职位
     *
     * @Author: yangqi
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function updateJob($filter ,$data){
        $data['update_time'] = time();
        $result = $this->where($filter)->update($data);
        return !empty($result);
    }
    /**
     * @Purpose: 获取职位列表
     *
     * @Author: yangqi
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function listJob($filter ,$page_config = ['page' => 1 ,'page_count' => 20 ]){
        $result = $this->where($filter)
                       ->order('top desc , id desc')
                       ->page($page_config['page'] ,$page_config['page_count'])
                       ->select();
        if(!empty($result)) $result = $result->toArray();
        return $result;
    }
    /**
     * @Purpose: 获取职位详情
     *
     * @Author: yangqi
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function getJob($filter){
        $result = $this->where($filter)->find();
        if(!empty($result)) $result = $result->toArray();
        return $result;
    }
    /**
     * @Purpose: 删除职位信息
     *
     * @Author: yangqi
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function delJob($filter){
        $result = $this->updateJob($filter ,['status' => -1 ,'update_time' => time()]);
        return !empty($result);
    }
    /**
     * @Purpose: 获取职位总数
     *
     * @Author: yangqi
     *
     * @Return: mixed 查询返回值（结果集对象）
     */
    public function getJobCount($filter){
        $result = $this->where($filter)->count();
        return $result;
    }
}