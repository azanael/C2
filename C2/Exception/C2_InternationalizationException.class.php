<?php
require_once 'C2_Exception.class.php';

class C2_InternationalizationException extends C2_Exception
{
    const ERROR_CODE = 520;
    
    public function __construct($message, $code = self::ERROR_CODE)
    {
        parent::__construct($message, $code);
    }
}

class C2_InternationalizationRuntimeException extends RuntimeException
{
    const ERROR_CODE = 620;
    
    public function __construct($message, $code = self::ERROR_CODE)
    {
        parent::__construct($message, $code);
    }
}

