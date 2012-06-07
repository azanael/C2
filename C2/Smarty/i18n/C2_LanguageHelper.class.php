<?php
/**
 * 表示言語のヘルパクラス
 */
class C2_LanguageHelper
{
    private static $enableLangcodes = array('ja', 'en');
    private $defaultLangcode = 'ja';
    
    public function __construct($defaultLangcode = null)
    {
        if ($defaultLangcode !== null) {
            $this->defaultLangcode = $defaultLangcode;
        }
    }
    
    /**
     * 選択中の表示言語を返す。
     *
     * [優先順位]
     * 1. URL (Get Parameter "lang")
     * 2. session "lang"
     * 3. cookie "lang"
     * 4. accept language
     * 5. DEFAULT_LANGCODE
     */
    public function loadSelectedLanguage()
    {
        if (isset($_GET['lang']) && self::validateLangcode($_GET['lang']) === true) {
            return $_GET['lang'];
        }
        if (isset($_SESSION['lang']) && self::validateLangcode($_SESSION['lang']) === true) {
            return $_SESSION['lang'];
        }
        if (isset($_COOKIE['lang']) && self::validateLangcode($_COOKIE['lang']) === true) {
            return $_COOKIE['lang'];
        }
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $acceptLanguage = array_shift(explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']));
            if (self::validateLangcode($acceptLanguage) === true) {
                return $acceptLanguage;
            }
        }
        return $this->defaultLangcode;
    }
    
    /**
     * 表示言語をセッションにセットする
     */
    public function setLangToSession($code)
    {
        if (self::validateLangcode($code) === false) {
            return false;
        }
        unset($_SESSION['lang']);
        $_SESSION['lang'] = $code;
        return true;
    }
    
    /**
     * 表示言語をクッキーにセットする
     */
    public function setLangToCookie($code, $expireDays = 365)
    {
        if (self::validateLangcode($code) === false) {
            return false;
        }
        if ($expireDays === null || $expireDays < 0) {
            throw new InvalidArgumentException('expireDays is null or minus.');
        }
        $expire = time() + 60 * 60 * 24 * $expireDays;
        setcookie('lang', $code, $expire, '/');
        return true;
    }
    
    /**
     * 引数で指定された言語コードが利用可能かどうか返す
     */
    private static function validateLangcode($code)
    {
        if ($code === null) {
            return false;
        }
        if (!in_array($code, self::$enableLangcodes)) {
            return false;
        }
        return true;
    }
}