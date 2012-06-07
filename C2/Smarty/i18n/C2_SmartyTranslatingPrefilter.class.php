<?php
require_once 'C2_DictionaryTranslatingHelper.class.php';
require_once 'C2_SmartyTranslatingHelper.class.php';

class C2_SmartyTranslatingPrefilter
{
    private $translatingLanguage;
    private $loadDictionaries;
    private $dictionaryBaseDirectory;
    private $dictionaryRequire;
    
    public function __construct($translatingLanguage = 'en', $loadDictionaries = array('default'), $dictionaryBaseDirectory = '/lang', $dictionaryRequire = false)
    {
        $this->translatingLanguage = $translatingLanguage;
        $this->loadDictionaries = $loadDictionaries;
        $this->dictionaryBaseDirectory = $dictionaryBaseDirectory;
        $this->dictionaryRequire = $dictionaryRequire;
    }
    
    public function filter($template, &$smarty)
    {
        $translatingHelper = new C2_DictionaryTranslatingHelper($this->translatingLanguage, $this->dictionaryBaseDirectory);
        foreach ((array)$this->loadDictionaries as $dictionary) {
            $translatingHelper->loadDictionary($dictionary, $this->dictionaryRequire);
        }
        $smartyTranslatingHelper = new C2_SmartyTranslatingHelper($template, $translatingHelper);
        return $smartyTranslatingHelper->translate();
    }
}