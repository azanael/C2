<?php

class MultilingualHelper
{
    public static $langConfig = null;
    
    /**
     * サイトで利用可能とされているすべての言語コードを返す
     * 設定で0になっている利用不可コードは返さない。
     * 代替言語が指定されている言語は返す。
     */
    public static function getEnabledLangCodes()
    {
        $codes = array();
        foreach (self::_get() as $lang) {
            if ($lang->status == "0") {
                continue;
            }
            if (BASE_LANG == $lang->langcode) {
                array_unshift($codes, array('code' => $lang->langcode, 'name' => $lang->langname));
            } else {
                array_push($codes, array('code' => $lang->langcode, 'name' => $lang->langname));
            }
        }
        return $codes;
    }
    
    public static function getLangName($langCode)
    {
        foreach (self::_get() as $lang) {
            if ($lang->langcode == $langCode) {
                return $lang->langname;
            }
        }
        return null;
    }
    
    /**
     * 実際に利用する言語コードを取得する
     *
     * 設定ファイルで未設定の言語コードや代替言語が指定されている言語コードも考慮して
     * 実際に利用してほしい言語コードを返す。
     *
     * @param unknown_type $langCode
     */
    public static function getRealCode($langCode)
    {
        foreach (self::_get() as $lang) {
            if ($lang->langcode != $langCode) {
                continue;
            }
            switch ($lang->status) {
                case '1':
                    return $lang->langcode;
                case '0':
                    return BASE_LANG;
                default:
                    return self::getRealCode($lang->status);
            }
        }
        return BASE_LANG;
    }
    
    private static function _get()
    {
        if (self::$langConfig === null) {
            self::$langConfig = con()->table('lang')->retrieveAll();
        }
        return self::$langConfig;
    }
}