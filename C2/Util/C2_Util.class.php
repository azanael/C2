<?php

class C2_Util
{
	/**
	 * Returns converted string including underscore to camel case.
	 * ex.) hoge_sample => hogeSample
	 *
	 * If argument is not string, return empty string.
	 *
	 * @param string $string
	 */
	public static function toCamel($string)
	{
		if (empty($string) || !is_string($string)) {
			return '';
		}
		$camelString = '';
		$len = strlen($string);
		for ($i = 0; $i < $len; $i++) {
			$char = $string[$i];
			if ($char == '_') {
				$toCamelFlag = true;
				continue;
			}
			if ($toCamelFlag === true) {
				$camelString .= ucfirst($char);
				$toCamelFlag = false;
				continue;
			}
			if ($i == 0) {
				$camelString .= lcfirst($char);
				continue;
			}
			$camelString .= $char;
		}
		return $camelString;
	}
	
	/**
	 * preg_match wrapper.
	 * Easy to get value using preg_match.
	 *
	 * @param string $regex
	 * @param string $value
	 * @return mixed|NULL
	 */
	public static function pregGet($regex, $value)
	{
		$matchNum = preg_match($regex, $value, $matches, PREG_OFFSET_CAPTURE);
		if ($matchNum > 0) {
			return $matches[1];
		}
		return null;
	}
	
	/**
	 * Generate random string for using password.
	 */
	public static function generateRandomPassword($length = 8)
	{
		$charList = "abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXY23456789";
		mt_srand();
		$res = "";
		for($i = 0; $i < $length; $i++) {
			$res .= $charList{mt_rand(0, strlen($charList) - 1)};
		}
		return $res;
	}
}
