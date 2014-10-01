<?php

namespace Supernova;

class Cache
{
    public static function load($model)
    {
        $filename = ROOT.DS."Cache".DS.$model;
        if (!file_exists($filename)) {
            self::generate($model);
        }
        parse_str(\Supernova\Crypt::decrypt(file_get_contents($filename)), $output);

        return $output;
    }

    public static function generate($model)
    {
        $dirName = ROOT.DS.'Cache';
        if (!file_exists($dirName)) {
            mkdir($dirName, 0777, true);
        }
        chdir($dirName);
        $table = \Supernova\Inflector::camelToUnder(\Supernova\Inflector::pluralize($model));
        $fields = \Supernova\Sql::getFields($table);
        file_put_contents($model, \Supernova\Crypt::encrypt(http_build_query($fields)));
        chmod($model, 0777);
    }
}
