<?php
require_once 'C2_Exception.class.php';

class C2_DBException extends C2_Exception
{
    const ERROR_CODE = 510;
    
    public function __construct($message, $code = self::ERROR_CODE)
    {
        parent::__construct($message, $code);
    }
}