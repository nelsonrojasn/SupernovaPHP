<?php

namespace Supernova;

/**
 * Traductor
 */
class Translate
{
    private static $language;
    private static $lang = array();

    /**
     * Ajusta el lenguage por defecto a mostrar
     * @param string $language Prefijo de lenguaje
     */
    public static function setLanguage($language = "")
    {
        self::$language = (!empty($language)) ? $language : LANGUAGE_DEFAULT;
    }

    /**
     * Traduce texto
     * @param  string $str Texto
     * @return string      Texto traducido
     */
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
