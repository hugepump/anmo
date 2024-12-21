<?php
/**
 * 
 * 异常处理类
 *
 */
class FddException extends Exception {

    public function errorMessage()
    {
        return $this->getMessage();
    }
}
