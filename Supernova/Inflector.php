<?php

namespace Supernova;

class Inflector
{

    /**
    * Transform Camelized string to relative path
    * @param string $str Text to inflect
    * @return array $aux2 Inflected array
    **/
    public static function camelToUnder($str)
    {
        if (is_string($str) && !empty($str)) {
            $str[0] = strtolower($str[0]);
            $func = create_function('$c', 'return "_" . strtolower($c[1]);');
            return preg_replace_callback('/([A-Z])/', $func, (string) $str);
        } else {
            return $str;
        }
    }

    /**
    * Transform Underscore string to Camelized
    * @param    String  $str    String to parse
    * @param    Boolean $capitaliseFirst    Capitalize first character
    * @return   String              Inflected string
    */
    public static function underToCamel($str = '', $capitaliseFirst = true)
    {
        if (!empty($str)) {
            if ($capitaliseFirst) {
                $str[0] = strtoupper($str[0]);
            }
            $func = create_function('$c', 'return strtoupper($c[1]);');
            return preg_replace_callback('/_([a-z])/', $func, $str);
        } else {
            return false;
        }
    }

    /**
    * Singularize string
    * @param string $str Text to inflect
    * @return string $str Inflected text
    **/
    public static function singularize($str)
    {
        switch (LANGUAGE_BASE) {
            case 'es':
                // Spanish inflector
                $origin = array('/([rln])es([A-Z]|_|$)/','/ises([A-Z]|_|$)/','/ices([A-Z]|_|$)/','/([d])es([A-Z]|_|$)/','/([rbtaeiou])s([A-Z]|_|$)/');
                break;
            case 'en':
            default:
                // English inflector
                $origin = array('/([ln])es([A-Z]|_|$)/','/ises([A-Z]|_|$)/','/ices([A-Z]|_|$)/','/([d])es([A-Z]|_|$)/','/([rbtaeioun])s([A-Z]|_|$)/');
                break;
        }
        $destiny = array('\1\2','\1is','\1iz','\1','\1\2');
        return preg_replace($origin, $destiny, $str);
    }

    /**
    * Pluralize string
    * @param string $str Text to inflect
    * @return string $str Inflected text
    **/
    public static function pluralize($str)
    {
        switch (LANGUAGE_BASE) {
            case 'es':
                // Spanish inflector
                $origin = array('/([rtbaeiou])([A-Z]|_|$)/','/([rlnd])([A-Z]|_|$)/', '/(is)([A-Z]|_|$)/','/(i)(z)([A-Z]|_|$)/');
                break;
            case 'en':
            default:
                // English inflector
                $origin = array('/([rtbaeioun])([A-Z]|_|$)/','/([rld])([A-Z]|_|$)/', '/(is)([A-Z]|_|$)/','/(i)(z)([A-Z]|_|$)/');
                break;
        }
        $destiny = array('\1s\2','\1es\2','\1es','\1ces');
        return preg_replace($origin, $destiny, $str);
    }

    public static function arrayToObject(array $array)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = self::arrayToObject($value);
            }
        }
        return (object) $array;
    }

    public static function inject($str = '', $vars = array(), $char = '%')
    {
        if (!$str) {
            return '';
        }
        if (count($vars) > 0) {
            foreach ($vars as $k => $v) {
                $str = str_replace($char.$k.$char, $v, $str);
            }
        }
        return $str;
    }

    public static function slugify($text)
    {
        $text = preg_replace('~[^\\pL\d]+~u', '-', $text);
        $text = trim($text, '-');
        if (function_exists('iconv')) {
            $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        }
        $text = strtolower($text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        if (empty($text)) {
            return 'n-a';
        }
        return $text;
    }
}
