<?php

class MultiLanguageSet
{
    private $_langCodes;
    private $_langValues;
    
    public function __construct($value = null, $langcode = LANG)
    {
        if ($value !== null) {
            $this->set($value, $langcode);
        }
    }
    
    public function set($value, $langcode = LANG)
    {
        $this->_langValues[$langcode] = $value;
        $this->_langCodes[] = $langcode;
    }
    
    public function get($langcode = LANG)
    {
        if (!isset($this->_langValues[$langcode])) {
            return null;
        }
        return $this->_langValues[$langcode];
    }
    
    public function getLangValues()
    {
        return $this->_langValues;
    }
    
    public function getLangCodes()
    {
        return $this->_langCodes;
    }
}