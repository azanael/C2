<?php

class C2_Validator
{
    /**
     * Validate Controller. (Facade)
     *
     * @param $type string validation type.
     * @param $property mixed target property.
     * @param $args array validation arguments.
     */
    public static function validate($type, $property, array $args = null)
    {
        switch ($type) {
            case 'notnull':
                return self::notNull($property);
                break;
            case 'notempty':
                return self::notEmpty($property);
                break;
            case 'maxlength':
                return self::maxLength($property, $args[0]);
                break;
            case 'minlength':
                return self::minLength($property, $args[0]);
                break;
            case 'isnumeric':
                return self::isNumeric($property);
                break;
            default:
                throw new C2_Exception("Unknown validation type: $type");
        }
    }

    public static function notNull($property)
    {
        return is_null($property) ? false : true;
    }

    public static function notEmpty($property)
    {
        return empty($property) ? false : true;
    }

    public static function maxLength($property, $length, $encoding = 'utf-8')
    {
        return mb_strlen($property, $encoding) > $length ? false : true;
    }

    public static function minLength($property, $length, $encoding = 'utf-8')
    {
        return mb_strlen($property, $encoding) < $length ? false : true;
    }

    public static function length($property, $maxLength, $minLength, $encoding = 'utf-8')
    {
        return mb_strlen($property, $encoding) < $minLength || mb_strlen($property, $encoding) > $maxLength ? false : true;
    }

    public static function isNumeric($property)
    {
        return !is_numeric($property) ? false : true;
    }

    public static function isUnsigned($value)
    {
        unset($v);
        if (is_array($value)) {
            foreach ($value as &$v) {
                if (!is_numeric($v) || $v < 0) {
                    return false;
                }
            }
            return true;
        }
        return (isset($value) && is_numeric($value) && $value >= 0) ? true : false;
    }

    public static function isEmail($property)
    {
        return preg_match('/^(?:[\w\!\#\$\%\&\'\*\+\-\/\=\?\^\`\{\|\}\~]+\.)*[\w\!\#\$\%\&\'\*\+\-\/\=\?\^\`\{\|\}\~]+@(?:(?:(?:[a-zA-Z0-9_](?:[a-zA-Z0-9_\-](?!\.)){0,61}[a-zA-Z0-9_-]?\.)+[a-zA-Z0-9_](?:[a-zA-Z0-9_\-](?!$)){0,61}[a-zA-Z0-9_]?)|(?:\[(?:(?:[01]?\d{1,2}|2[0-4]\d|25[0-5])\.){3}(?:[01]?\d{1,2}|2[0-4]\d|25[0-5])\]))$/', $property) != 0 ? true : false;
    }
}