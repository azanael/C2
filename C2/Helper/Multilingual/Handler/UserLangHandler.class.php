<?php
require_once dirname(__FILE__) . '/../Helper/MultilingualHelper.class.php';

class UserLangHandler
{
    const COOKIE_EXPIRE_DAYS = 365;
    
    /**
     * ユーザの言語コードをCookieにセットする
     *
     * @param unknown_type $langCode
     */
    public static function setMyCode($langCode)
    {
        $expire = time() + 60 * 60 * 24 * self::COOKIE_EXPIRE_DAYS;
        setcookie('_lang', $langCode, $expire, '/');
        return true;
    }
    
    /**
     * ユーザが設定済の言語コードをそのまま返す
     *
     * ここで返されるのは実際に表示すべき言語ではなく、ユーザが自主的に選んだコード。
     * 例えば「中国語」を「英語」で表示するよう設定していたとしても、ここで返すのは
     * 中国語の言語コードとなる。
     */
    public static function getMyCode()
    {
        if (isset($_COOKIE['_lang'])) {
            $lang = $_COOKIE['_lang'];
        } else if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $l = array_shift(explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']));
            switch ($l) {
                case 'en':
                case 'en-us':
                    $lang = LangCode::en_US; break;
                case 'ja':
                    $lang = LangCode::JA; break;
                case 'ms':
                    $lang = LangCode::ms; break;
                case 'zh-tw':
                    $lang = LangCode::zh_TW; break;
                case 'zh-cn':
                    $lang = LangCode::zh_CN; break;
                case 'zh-hk':
                    $lang = LangCode::zh_HK; break;
                case 'zh-sg':
                    $lang = LangCode::zh_CN; break;
                case 'in':
                    $lang = LangCode::in; break;
                default:
                    $lang = BASE_LANG;
            }
        }
        return $lang;
    }
    
    /**
     * ユーザが設定済の言語コードから実際に表示に利用すべき言語を返す
     *
     * 例えばDBで「中国語」を「英語」にするよう設定されていたら、中国語ではなく英語の言語コードを返す。
     * getMyCodeとの違いに留意。
     */
    public static function getMyRealCode()
    {
        return MultilingualHelper::getRealCode(self::getMyCode());
    }
}