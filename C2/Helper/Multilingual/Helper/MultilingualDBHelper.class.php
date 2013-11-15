<?php

class MultilingualDBHelper
{
    public static function getMultiLangValue($con, $multiLangId, $langCode = LANG)
    {
        $sql = 'SELECT value FROM lang_values WHERE id = ? AND langcode = ?';
        $r = $con->table('lang_values')->query($sql, array($multiLangId, $langCode));
        return $row[0]->value;
    }
}