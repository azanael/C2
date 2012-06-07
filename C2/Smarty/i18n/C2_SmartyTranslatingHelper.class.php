<?php

class C2_SmartyTranslatingHelper
{
    const DELIMITER_LEFT = "\_\(\'";
    const DELIMITER_RIGHT = "\'\)";
    
    private $template = null;
    private $helper = null;
    
    public function __construct($template, C2_DictionaryTranslatingHelper $helper)
    {
        $this->template = $template;
        $this->helper = $helper;
    }
    
    public function getTemplate()
    {
        return $this->template;
    }
    
    public function setTemplate($template)
    {
        $this->template = $template;
    }
    
    public function getHelper()
    {
        return $this->helper;
    }
    
    public function setHelper($helper)
    {
        $this->helper = $helper;
    }
    
    public function translate()
    {
        if (empty($this->template) || empty($this->helper)) {
            throw new C2_InternationalizationException('Template or Helper is empty.');
        }
        $regex = self::DELIMITER_LEFT . '(.+)' . self::DELIMITER_RIGHT;
        $translatedTemplate = preg_replace("/$regex/esUm", "\$this->helper->translate('$1')", $this->template);
        if ($translatedTemplate === null) {
            throw new C2_InternationalizationException('preg_replace: error. regex=' . $regex);
        }
        return $translatedTemplate;
    }
}