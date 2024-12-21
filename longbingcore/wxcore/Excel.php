<?php
declare(strict_types=1);

namespace longbingcore\wxcore;

use app\Common\LongbingServiceNotice;

use think\facade\Db;

class Excel{

//    static protected $uniacid;
//
//    public function __construct($uniacid)
//    {
//        self::$uniacid = $uniacid;
//
//    }


    /**
     * @param $filename
     * @功能说明:读取excel文件 返回一个数组
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-02-13 18:21
     */
   public function readExcel($filename){

       //引用excel库
       require_once  EXTEND_PATH.'PHPExcel/PHPExcel.php';
       //判断是否文件
       if(empty($filename)||!is_file($filename)){
           return '该文件错误';
       }
       //准备打开文件
       $objReader   = \PHPExcel_IOFactory::createReaderForFile($filename);
       //载入文件
       $objPHPExcel = $objReader->load($filename);
       //设置第一个Sheet
       $objPHPExcel->setActiveSheetIndex(0);

       $sheet      = $objPHPExcel->getSheet(0);

       $highestRow = $sheet->getHighestRow();

       $highestColumn = $sheet->getHighestColumn();

       for ($row = 2; $row <= $highestRow; $row++){
           //每一个文件
           $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);

           $data[]  = $rowData;
       }

       return $data;

   }



    /**
     * User: chenniang(龙兵科技)
     * Date: 2019-09-10 10:47
     * @return \think\Response
     * descption:phpExcel 导出方法
     */
    function excelExport($fileName = '', $headArr = [], $data = [],$type='',$status=0) {
        require_once  EXTEND_PATH.'PHPExcel/PHPExcel.php';

        ini_set("memory_limit", "1024M"); // 设置php可使用内存

        set_time_limit(30);
        # 设置执行时间最大值
//        if(empty($fileName)){

            $fileName   .= ".xls";
//        }

        $objPHPExcel = new \PHPExcel();

        $objWriter   = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

        $objPHPExcel->getProperties();

        $key = ord("A"); // 设置表头

        $key2 = ord("@"); //64

        foreach ($headArr as $v) {

            $e=mb_detect_encoding($v, array('UTF-8', 'GBK'));

            if($e!='UTF-8') {

                $v = mb_convert_encoding($v, "UTF-8", "GBK");
            }

            if($key > ord("Z"))
            {
                $colum = chr(ord("A")).chr(++$key2);//超过26个字母 AA1,AB1,AC1,AD1...BA1,BB1...
            }
            else
            {
                $colum = chr($key++);
            }

            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($colum . '1', $v);

        }
        $column = 2;

        $objActSheet = $objPHPExcel->getActiveSheet(0);

        if($type == 1){
            //合并名片的单元格
            $this->mergeCard($objPHPExcel);
        }

        $fileName = iconv("utf-8", "GBK", $fileName);

        foreach ($data as $key => $rows) { // 行写入

            $span = ord("A");

            $key2 = ord("@"); //64

            foreach ($rows as $keyName => $value) { // 列写入

                if($span > ord("Z"))
                {
                    $colum = chr(ord("A")).chr(++$key2);//超过26个字母 AA1,AB1,AC1,AD1...BA1,BB1...
                }
                else
                {
                    $colum = chr($span);
                }

                if(!empty($value)&&is_string($value)){

                    $e=mb_detect_encoding($value, array('UTF-8', 'GBK'));

                    $value = $this->filterEmoji($value);

                    if($e!='UTF-8'){

                        $value = mb_convert_encoding($value, "UTF-8", "GBK");
                    }

                }

                $objActSheet->setCellValueExplicit($colum . $column, $value);

                $span++;
            }
            $column++;
        }

        if(in_array($status,[1,2,3])){

            $this->mergeOrderExcel($objPHPExcel,$data,$status);
        }

        ob_end_clean();

        header('Content-Type: application/vnd.ms-excel');

        header("Content-Disposition: attachment;filename=$fileName");

        header('Cache-Control: max-age=0');

        header('content-Type:application/vnd.ms-excel;charset=utf-8');

        $objWriter->save('php://output');// 文件通过浏览器下载

        return $fileName;
        exit();
    }

    /**
     * @param $fileName
     * @param $header
     * @param $result
     * @功能说明:
     * @author chenniang(龙兵科技)
     * @DataTime: 2021-10-13 11:30
     */
    public function excelCsv($fileName,$header,$result,$type='',$status=0)
    {

        //让程序一直运行
        set_time_limit(0);
        //设置程序运行内存
        ini_set('memory_limit', '1024M');

        header('Content-Encoding: UTF-8');
        header("Content-type:application/vnd.ms-excel;charset=UTF-8");
        header('Content-Disposition: attachment;filename="' . $fileName . '.csv"');
        //打开php标准输出流
        $fp = fopen('php://output', 'a');
        //添加BOM头，以UTF8编码导出CSV文件，如果文件头未添加BOM头，打开会出现乱码。
        fwrite($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));
        //添加导出标题
        fputcsv($fp, $header);
        //链接数据库
        foreach ($result as $item) {

            fputcsv($fp, $item);
        }
        //每1万条数据就刷新缓冲区
        ob_flush();

        flush();

    }


    /**
     * @param $obj
     * @param $data
     * 合并订单单元格
     */
    public function mergeOrderExcel($obj,$data,$status=1){

        foreach ($data as $k=>$v){

            if(!empty($v)){

                $v['key'] = $k+2;

                $newdata[$v[0]][] = $v;

            }
        }

        switch ($status){
            case 1 :
                $arr = ['A','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y'];
                break;
            case 2 :
                $arr = ['A','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X'];
                break;
            case 3 :
                $arr = ['A','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V'];
                break;
        }

        if(!empty($newdata)){

            foreach ($newdata as $k=>$v){
                if(count($v)>1){
                    $count = count($v)-1;
                    foreach ($arr as $value){
                        $me = $value.$v[0]['key'].':'.$value.$v[$count]['key'];

                        $obj->getActiveSheet()->mergeCells($me);
                    }
                }
            }
        }

    }


    /**
     * @param $str
     * @return string|string[]|null
     * 过滤表情包
     */
    public function filterEmoji($str)
    {
        $str = preg_replace_callback(
            '/./u',
            function (array $match) {
                return strlen($match[0]) >= 4 ? '' : $match[0];
            },
            $str);

        return $str;
    }

    /**
     * @author chenniang(龙兵科技)
     * @DataTime: 2020-04-15 15:19
     * @功能说明:合并订单导出的单元格
     */
    public function mergeCard($objPHPExcel){


        $arr = ['A','B','C','D'];

        foreach ($arr as $value){

            $s_icon = $value.'1'.':'.$value.'2';

            $h_icon = $value.'1';

            $objPHPExcel->getActiveSheet()->mergeCells($s_icon);

            $objPHPExcel->getActiveSheet()->getstyle($s_icon)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

            $objPHPExcel->getActiveSheet()->getStyle($h_icon)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        }

        $arr2 = ['E1:F1','G1:H1','I1:J1','K1:L1','M1:N1','O1:P1'];

        foreach ($arr2 as $value){


            $objPHPExcel->getActiveSheet()->mergeCells($value);

            $objPHPExcel->getActiveSheet()->getstyle($value)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

            $objPHPExcel->getActiveSheet()->getStyle($value)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        }

        $arr3 = ['E2','F2','G2','H2','I2','J2','K2','L2','M2','N2','O2','P2'];

        foreach ($arr3 as $value){

            $objPHPExcel->getActiveSheet()->getStyle($value)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        }

        return true;
    }











}