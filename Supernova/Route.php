<?php

namespace Supernova;

class Route
{
    public static function getPublicFolder()
    {
        if (HTACCESS){
            $public_directory = dirname($_SERVER['PHP_SELF']);
            $directory_array = explode('/', $public_directory);
        } else {
            $directory_array[] = "Public";
        }
        return end($directory_array);
    }

    public static function getRelativePath()
    {
        $path = explode('/', dirname($_SERVER['PHP_SELF']));
        unset($path[array_search(self::getPublicFolder(), $path)]);
        $path = array_filter($path);
        return ($path) ? "/".implode("/", $path) : "/";
    }

    public static function getPublicUrl()
    {
        return self::getBaseUrl().self::getPublicFolder();
    }

    public static function getHost(){
        return str_replace("/","",$_SERVER['HTTP_HOST']);
    }

    public static function getBaseUrl()
    {
        $httpString = (\Supernova\Core::checkSSL()) ? "https://" : "http://";
        return $httpString.self::getHost().self::getRelativePath();
    }

    public static function generateUrl($url)
    {
        require ROOT. DS . "Config" . DS . "routing.php";

        if (!is_array($url)) {
            if (array_key_exists($url, $routing['alias'])) {
                return self::generateUrl($routing['alias'][$url]);
            }
            return str_replace(' ', '-', $url);
        }

        $newUrl = array();
        foreach ($routing['behaviourOrder'] as $eachOrder) {
            if (isset($url[$eachOrder])) {
                $newUrl[] = \Supernova\Inflector::camelToUnder($url[$eachOrder]);
                unset($url[$eachOrder]);
            }
        }
        
        foreach ($url as $k => $v) {
            $newUrl[$k]= $v;
        }

        array_filter($newUrl);
        $url = implode('/', $newUrl);
        return self::getBaseUrl().$url;
    }
}
