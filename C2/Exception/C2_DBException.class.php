<?php
require_once 'C2_Exception.class.php';

class C2_DBException extends C2_Exception
{
    protected $code = '400';
    protected $message;

}