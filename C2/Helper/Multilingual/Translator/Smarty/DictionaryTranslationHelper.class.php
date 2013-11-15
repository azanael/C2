<?php

class DictionaryTranslationHelper
{
    private $_langCode;
    
    public function __construct($langCode)
    {
        $this->_langCode = $langCode;
    }
    
    public function loadDictionaries($localeDir = null)
    {
        $dictionary = array();
        if ($localeDir === null) {
            $localeDir = dirname(__FILE__) . '/../../../../conf/locale/' . $this->_langCode;
        }
        $files = @scandir($localeDir);
        if ($files === false) {
            return array();
        }
        foreach ($files as $file) {
            if (strpos($file, '.') === 0) {
                continue;
            }
            if (is_dir("$localeDir/$file")) {
                $dictionary = array_merge($dictionary, $this->loadDictionaries("$localeDir/$file"));
                continue;
            }
            if (preg_match('/\.lang$/', $file) !== 1) {
                continue;
            }
            $dictionary = array_merge($dictionary, $this->_loadDictionary("$localeDir/$file"));
        }
        return $dictionary;
    }
    
    private function _loadDictionary($filePath)
    {
        $dictionary = array();
        $contents = @file_get_contents($filePath);
        if (empty($contents)) {
            return array();
        }
        $preg = preg_match_all('/^\s*([\w\.]+)\s*=\s*(.*)\s*$/m', $contents, $matches, PREG_SET_ORDER);
        if ($preg === false) {
            throw new MultilingualException("preg_match_all occured error. filePath=$filePath");
        }
        foreach ($matches as $key => $match) {
            $dictionary[$match[1]] = $match[2];
        }
        return $dictionary;
    }
}