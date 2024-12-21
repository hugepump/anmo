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


use ZipArchive;

class LongbingFile
{

    /**
     * @param $url
     * @param string $folderPath  = './data/upgradex/'
     * @param null $fileName  有文件名就使用,没有就随机一个名字(主要 不带后缀)
     * @param string $ext 文件后缀
     * @功能说明:下载文件
     * @author jingshuixian
     * @DataTime: 2020-06-06 0:33
     */
    public static function downloadFile($url, $folderPath ,$fileName = null , $ext='.zip'  ) {
        set_time_limit(24 * 60 * 60);
        $target_dir = $folderPath . '';
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $newfname = $fileName ? $fileName .$ext  :  date('Ymd') . rand(1000, 10000000) . uniqid() . $ext;
        $newfname = $target_dir . $newfname;
        $file = @fopen($url, "rb");
        if ($file) {
            $newf = fopen($newfname, "wb");
            if ($newf) while (!feof($file)) {
                fwrite($newf, fread($file, 1024 * 8) , 1024 * 8);
            }

            fclose($file);
            if ($newf) {
                fclose($newf);
            }

        }else{
            return false ;
        }

        return $newfname;
    }

    /**
     * @param $filename
     * @param $toFilepath
     * @param null $password 解压密码
     * @功能说明: 解压文件
     * @author jingshuixian
     * @DataTime: 2020-06-05 14:44
     */
    public static function unzip($filename  , $toFilepath  , $password = null){
        $zip = new ZipArchive();
        $res = $zip->open($filename);
        if ($res === true){
            $password  && $zip->setPassword($password);    //解压密码
            $zip->extractTo($toFilepath);
            $zip->close();
        }

        return true ;
    }


    /**
     * @param $fileName
     * @param string $fancyName
     * @param bool $forceDownload
     * @param int $speedLimit
     * @param string $contentType
     * @功能说明:  函数参数: 服务器文件路径，下载文件名字(默认为服务器文件名)，是否许可用户下载方式(默认可选)，速度限制(默认自动)，文件类型(默认所有)
     * 使用方法
     * $file_path = './a.zip'; // 只能是本地服务器文件, 多大的文件都支持!!
     * down_file($file_path);
     * @author jingshuixian
     * @DataTime: 2020-06-05 23:50
     */
    function downLocationServiceFile($fileName, $fancyName = '', $forceDownload = true, $speedLimit = 0, $contentType = '') {
        if (!is_readable($fileName))
        {
            header("HTTP/1.1 404 Not Found");
            return false;
        }
        $fileStat = stat($fileName);
        $lastModified = $fileStat['mtime'];
        $md5 = md5($fileStat['mtime'] .'='. $fileStat['ino'] .'='. $fileStat['size']);
        $etag = '"' . $md5 . '-' . crc32($md5) . '"';
        header('Last-Modified: ' . gmdate("D, d M Y H:i:s", $lastModified) . ' GMT');
        header("ETag: $etag");
        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $lastModified)
        {
            header("HTTP/1.1 304 Not Modified");
            return true;
        }
        if (isset($_SERVER['HTTP_IF_UNMODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_UNMODIFIED_SINCE']) < $lastModified)
        {
            header("HTTP/1.1 304 Not Modified");
            return true;
        }
        if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] == $etag)
        {
            header("HTTP/1.1 304 Not Modified");
            return true;
        }
        if ($fancyName == '')
        {
            $fancyName = basename($fileName);
        }
        if ($contentType == '')
        {
            $contentType = 'application/octet-stream';
        }
        $fileSize = $fileStat['size'];
        $contentLength = $fileSize;
        $isPartial = false;
        if (isset($_SERVER['HTTP_RANGE']))
        {
            if (preg_match('/^bytes=(\d*)-(\d*)$/', $_SERVER['HTTP_RANGE'], $matches))
            {
                $startPos = $matches[1];
                $endPos = $matches[2];
                if ($startPos == '' && $endPos == '')
                {
                    return false;
                }
                if ($startPos == '')
                {
                    $startPos = $fileSize - $endPos;
                    $endPos = $fileSize - 1;
                }
                else if ($endPos == '')
                {
                    $endPos = $fileSize - 1;
                }
                $startPos = $startPos < 0 ? 0 : $startPos;
                $endPos = $endPos > $fileSize - 1 ? $fileSize - 1 : $endPos;
                $length = $endPos - $startPos + 1;
                if ($length < 0)
                {
                    return false;
                }
                $contentLength = $length;
                $isPartial = true;
            }
        }
        // send headers
        if ($isPartial)
        {
            header('HTTP/1.1 206 Partial Content');
            header("Content-Range: bytes $startPos-$endPos/$fileSize");
        }
        else
        {
            header("HTTP/1.1 200 OK");
            $startPos = 0;
            $endPos = $contentLength - 1;
        }
        header('Pragma: cache');
        header('Cache-Control: public, must-revalidate, max-age=0');
        header('Accept-Ranges: bytes');
        header('Content-type: ' . $contentType);
        header('Content-Length: ' . $contentLength);
        if ($forceDownload)
        {
            header('Content-Disposition: attachment; filename="' . rawurlencode($fancyName). '"');
        }
        header("Content-Transfer-Encoding: binary");
        $bufferSize = 2048;
        if ($speedLimit != 0)
        {
            $packetTime = floor($bufferSize * 1000000 / $speedLimit);
        }
        $bytesSent = 0;
        $fp = fopen($fileName, "rb");
        fseek($fp, $startPos);
        while ($bytesSent < $contentLength && !feof($fp) && connection_status() == 0 )
        {
            if ($speedLimit != 0)
            {
                list($usec, $sec) = explode(" ", microtime());
                $outputTimeStart = ((float)$usec + (float)$sec);
            }
            $readBufferSize = $contentLength - $bytesSent < $bufferSize ? $contentLength - $bytesSent : $bufferSize;
            $buffer = fread($fp, $readBufferSize);
            echo $buffer;
            ob_flush();
            flush();
            $bytesSent += $readBufferSize;
            if ($speedLimit != 0)
            {
                list($usec, $sec) = explode(" ", microtime());
                $outputTimeEnd = ((float)$usec + (float)$sec);
                $useTime = ((float) $outputTimeEnd - (float) $outputTimeStart) * 1000000;
                $sleepTime = round($packetTime - $useTime);
                if ($sleepTime > 0)
                {
                    usleep($sleepTime);
                }
            }
        }
        return true;
    }


    /**
     * @param $path
     * @param string $mode
     * @功能说明: 创建目录
     * @author jingshuixian
     * @DataTime: 2020-06-06 0:11
     */
    public static function createDir($path , $mode = '0777'){

        if(!is_dir($path)){
            return mkdir($path,$mode);
        }

    }

    /**
     * @param $path
     * @功能说明: 删除指定目录下的文件
     * @author jingshuixian
     * @DataTime: 2020-06-06 0:14
     */
    public static function deldir($path){
        //如果是目录则继续
        if(is_dir($path)){
            //扫描一个文件夹内的所有文件夹和文件并返回数组
            $p = scandir($path);
            foreach($p as $val){
                //排除目录中的.和..
                if($val !="." && $val !=".."){
                    //如果是目录则递归子目录，继续操作
                    if(is_dir($path.$val)){
                        //子目录中操作删除文件夹和文件
                        LongbingFile::deldir($path.$val.'/');
                        //目录清空后删除空文件夹
                        @rmdir($path.$val.'/');
                    }else{
                        //如果是文件直接删除
                        unlink($path.$val);
                    }
                }
            }
        }
    }



}