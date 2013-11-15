<?php

class TwitterApiException extends Exception
{
    public function __construct($message, $code = 1000) {
        parent::__construct($message, $code);
    }
}

class TwitterApiInvalidArgumentException extends TwitterApiException
{
    public function __construct($message, $code = 1001) {
        parent::__construct($message, $code);
    }
}

class TwitterApiRuntimeException extends TwitterApiException
{
    public function __construct($message, $code = 2000) {
        parent::__construct($message, $code);
    }
}

class TwitterApiConnectionException extends TwitterApiRuntimeException
{
    public function __construct($message, $code = 2001) {
        parent::__construct($message, $code);
    }
}