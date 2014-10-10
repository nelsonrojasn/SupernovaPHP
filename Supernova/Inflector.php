<?php

namespace Supernova;

class Inflector
{
    /**
     * Transforma texto camelizado a underscore
     * @param  string $str Texto camelizado
     * @return string      Texto underscore
     */
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
    * Transforma texto underscore a camelizado
    * @param  string  $str             Texto underscore
    * @param  boolean $capitaliseFirst Capitalizar el primer caracter
    * @return string                   Texto capitalizado
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
     * Pasar palabra a Singular
     * @param  string $str Palabra en Plural
     * @return string      Palabra en Singular
     */
    public static function singularize($str)
    {
        switch (LANGUAGE_BASE) {
            case 'es':
                // Inflector Español
                $origin = array('/([rln])es([A-Z]|_|$)/','/ises([A-Z]|_|$)/','/ices([A-Z]|_|$)/','/([d])es([A-Z]|_|$)/','/([rbtaeiou])s([A-Z]|_|$)/');
                break;
            case 'en':
            default:
                // Inflector Inglés
                $origin = array('/([ln])es([A-Z]|_|$)/','/ises([A-Z]|_|$)/','/ices([A-Z]|_|$)/','/([d])es([A-Z]|_|$)/','/([rbtaeioun])s([A-Z]|_|$)/');
                break;
        }
        $destiny = array('\1\2','\1is','\1iz','\1','\1\2');
        return preg_replace($origin, $destiny, $str);
    }

    /**
     * Pasar palabra a Plural
     * @param  string $str Palabra en Singular
     * @return string      Palabra en Plural
     */
    public static function pluralize($str)
    {
        switch (LANGUAGE_BASE) {
            case 'es':
                // Inflector en español
                $origin = array('/([rtbaeiou])([A-Z]|_|$)/','/([rlnd])([A-Z]|_|$)/', '/(is)([A-Z]|_|$)/','/(i)(z)([A-Z]|_|$)/');
                break;
            case 'en':
            default:
                // Inflector en inglés
                $origin = array('/([rtbaeioun])([A-Z]|_|$)/','/([rld])([A-Z]|_|$)/', '/(is)([A-Z]|_|$)/','/(i)(z)([A-Z]|_|$)/');
                break;
        }
        $destiny = array('\1s\2','\1es\2','\1es','\1ces');
        return preg_replace($origin, $destiny, $str);
    }

    /**
     * Convertir un arreglo a objeto
     * @param  array  $array Arreglo
     * @return object        Objeto
     */
    public static function arrayToObject(array $array)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = self::arrayToObject($value);
            }
        }
        return (object) $array;
    }

    /**
     * Injecta variables dentro de los comodines en un string
     * @param  string $str  String con comodines
     * @param  array  $vars Arreglo con los comodines y valores a reemplazar
     * @param  string $char Caracter comodin
     * @return string       String con comodines reemplazados
     */
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

    /**
     * Crea un slug a partir de un texto
     * @param  string $text Texto
     * @return string       Texto con slug
     */
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
