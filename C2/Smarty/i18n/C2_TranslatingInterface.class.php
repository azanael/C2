<?php

Interface C2_TranslatingHelperInterface
{
    public function getTranslatingLang();
    public function setTranslatingLang($translatingLang);
    public function translate($message);
}