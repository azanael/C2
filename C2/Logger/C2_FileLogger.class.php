<?php

class C2_Logger
{
    private static $_level;
    private static $_path;

    private static $_facilities = array('debug', 'info', 'warn', 'error', 'crit');

    /**
     * Initialization logger.
     *
     * @param string $path
     * @param string $addr
     */
    public static function init($path, $level = 'debug')
    {
        self::$_path = $path;
        self::$_level = $level;
    }

    public static function error_handler($errno, $errstr, $errfile, $errline)
    {
        if (!error_reporting()) {
            return false;
        }
        switch ($errno) {
            case E_USER_ERROR:
            case E_RECOVERABLE_ERROR:
                self::error($errstr);
                break;
            case E_WARNING:
            case E_USER_WARNING:
                self::warn($errstr);
                break;
            case E_NOTICE:
            case E_USER_NOTICE:
                self::debug($errstr);
                break;
        }
        return true;
    }

    public static function crit($string)
    {
        self::output('crit', $string);
    }

    public static function error($string)
    {
        self::output('error', $string);
    }

    public static function warn($string)
    {
        self::output('warn', $string);
    }

    public static function info($string)
    {
        self::output('info', $string);
    }

    public static function debug($string)
    {
        self::output('debug', $string);
    }

    private static function output($facility, $string)
    {
        if (in_array(self::$_level, self::$_facilities) && array_search($facility, self::$_facilities) < array_search(self::$_level, self::$_facilities)) {
            return true;
        }

        // If facility level above error, add backtrace automatically.
        $addTrace = (array_search($facility, self::$_facilities) >= 3) ? true : false;
        // output
        self::_putFile($facility, self::_buildMessage($facility, $string, $addTrace));
    }

    private static function _putFile($facility, $message)
    {
        $filename = sprintf('%s_%s.log', $facility, date('Ymd'));
        if (!$fp = @fopen(self::$_path . $filename, 'a')) {
            die("can't open log file. " . self::$_path . $filename);
        }
        if (fwrite($fp, $message) === false) {
            die("log output failed: $message");
        }
        fclose($fp);
    }

    private static function _buildMessage($facility, $outputString, $addTrace = true)
    {
        $message = sprintf("%s [%s][%s](%s) %s", date('c'), $facility, $_SERVER['SERVER_ADDR'], $_SERVER['REQUEST_URI'], $outputString);
        if ($addTrace === true) {
            $b = debug_backtrace(false);
            $clazz = $b[3]['class'];
            $func = $b[3]['function'];
            $line = $b[2]['line'];
            $message .= " at $clazz::$func() LINE $line";
        }
        $message .= PHP_EOL;
        return $message;
    }
}