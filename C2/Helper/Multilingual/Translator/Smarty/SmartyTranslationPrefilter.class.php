<?php
require_once dirname(__FILE__) . '/../../Exception/MultilingualException.class.php';
require_once 'DictionaryTranslationHelper.class.php';
require_once 'SmartyTranslationHelper.class.php';

class SmartyTranslationPrefilter
{
    private $langCode;

    public function __construct($langCode)
    {
        $this->langCode = $langCode;
    }

    public function filter($template, Smarty &$smarty)
    {
        $translationHelper = new DictionaryTranslationHelper($this->langCode);
        $smartyTranslationHelper = new SmartyTranslationHelper($template, $translationHelper->loadDictionaries());
        return $smartyTranslationHelper->translate();
    }
}