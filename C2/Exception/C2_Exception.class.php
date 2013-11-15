<?php

class C2_Exception extends Exception
{
    const ERROR_CODE = 500;
    
    public function __construct($message, $code = self::ERROR_CODE)
    {
        parent::__construct($message, $code);
    }
}

class C2_RuntimeException extends RuntimeException
{
    const ERROR_CODE = 600;
    
    public function __construct($message, $code = self::ERROR_CODE)
    {
        parent::__construct($message, $code);
    }
}