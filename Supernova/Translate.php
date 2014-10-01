<?php

namespace Supernova;

class Translate
{
    private static $language;
    private static $lang = array();

    public static function setLanguage($language = "")
    {
        self::$language = (!empty($language)) ? $language : LANGUAGE_DEFAULT;
    }

    public static function text($str)
    {
        $file = ROOT.DS.'Locale'.DS.self::$language.'.php';
        if (file_exists($file)) {
            include $file;
            if (array_key_exists($str, ${self::$language})) {
                $str = htmlentities(${self::$language}[$str]);
            }
        }
        return $str;
    }
}
