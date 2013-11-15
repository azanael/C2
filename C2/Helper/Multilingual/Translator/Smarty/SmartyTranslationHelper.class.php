<?php

class SmartyTranslationHelper
{
    const DELIMITER_LEFT = "\_\(\'";
    const DELIMITER_RIGHT = "\'\)";

    private $template = null;
    private $_dictionary = array();

    public function __construct($template, array $dictionary)
    {
        $this->template = $template;
        $this->_dictionary = $dictionary;
    }

    public function getTemplate()
    {
        return $this->template;
    }

    public function setTemplate($template)
    {
        $this->template = $template;
    }
    
    public function setDictionary($dictionary){
        $this->_dictionary = $dictionary;
    }
    
    public function getDictionary(){
        return $this->_dictionary;
    }

    public function translate()
    {
        if (empty($this->template)) {
            throw new MultilingualException('Template is empty.');
        }
        $regex = self::DELIMITER_LEFT . '(.+)' . self::DELIMITER_RIGHT;
        return preg_replace_callback("/$regex/sUm", array($this, '_translateLine'), $this->template);
    }
    
    private function _translateLine($line)
    {
        if (isset($this->_dictionary[$line[1]])) {
            return $this->_dictionary[$line[1]];
        }
        return $line[1];
    }
}