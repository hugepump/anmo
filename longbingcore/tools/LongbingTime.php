<?php
// +----------------------------------------------------------------------
// | Longbing [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright Chengdu longbing Technology Co., Ltd.
// +----------------------------------------------------------------------
// | Website http://longbing.org/
// +----------------------------------------------------------------------
// | Sales manager: +86-13558882532 / +86-13330887474
// | Technical support: +86-15680635005
// | After-sale service: +86-17361005938
// +----------------------------------------------------------------------

namespace longbingcore\tools;


class LongbingTime
{

    /**
     * @param $str
     * @功能说明:转utf 8
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-01-03 19:23
     */

    public static function getChinaNowTime(){
        return date('Y-m-d H:i:s',   time() ) ;
    }


    /**
     * @param $data
     * @param string $field
     * @功能说明:格式化友好时间
     * @author jingshuixian
     * @DataTime: 2020/1/17 10:26
     */
    public static function frendlyTime ( $data, $field = 'create_time' )
    {
        // 今天的时间戳
        $time = time();
        // 昨天的时间戳
        $Yesterday = $time - (24 * 60 * 60);

        $today = mktime(0, 0, 0, date("m", $time), date("d", $time), date("Y", $time));
        $Yesterday = mktime(0, 0, 0, date("m", $Yesterday), date("d", $Yesterday), date("Y", $Yesterday));

        foreach ($data as $index => $item) {
            $tmpTime = $item[$field];
            if ($tmpTime > $today) {
                //                $data[ $index ][ 'radar_time' ] = '今天 ';
                $data[$index]['radar_group'] = '今天';
                $data[$index]['radar_time'] = date('H:i', $item[$field]);
            } else if ($tmpTime > $Yesterday) {
                //                $data[ $index ][ 'radar_time' ] = '昨天 ';
                $data[$index]['radar_group'] = '昨天';
                $data[$index]['radar_time'] = date('H:i', $item[$field]);
            } else {
                $thisYear = date('Y');
                $itemYear = date('Y', $item[$field]);
                if ($thisYear == $itemYear) {
                    $data[$index]['radar_group'] = date('m-d', $item[$field]);
                    $data[$index]['radar_time'] = date(' H:i', $item[$field]);
                } else {
                    $data[$index]['radar_group'] = date('Y-m-d', $item[$field]);
                    $data[$index]['radar_time'] = date(' H:i', $item[$field]);
                }

            }
        }

        return $data;
    }

}