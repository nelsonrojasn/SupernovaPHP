<?php

namespace Supernova;

class Profiler
{
    private static $startTime;
    private static $endTime;

    public static function start()
    {
        self::$startTime = microtime(true);
    }

    public static function end()
    {
        self::$endTime = microtime(true);
    }

    public static function show()
    {
        if (!defined("ENVIRONMENT") || ENVIRONMENT == "dev") {
            $performance = self::$endTime - self::$startTime;
            echo self::styleTime();
            echo "<div class='profiler'>".
            "Supernova Framework &copy; 2014 :: ".__("Profiler")." :: ".//"<br/>".
            __("Performance").": ".number_format($performance, 2)." ".__("seconds")." :: ".//"<br/>".
            __("Memory allocated").": ".round(memory_get_usage()/1024)."Kb :: ".//"<br/>".
            __("Memory peak").": ".round(memory_get_peak_usage()/1024)."Kb"."<br/>".
            \Supernova\Debug::showQuery().
            "</div>";
        }
    }

    private static function styleTime()
    {
        $output = <<<EOL
        <style>
            .profiler{
                position: fixed;
                width: 100%;
                text-align: center;
                bottom: 0px;
                color: yellow;
                font-size: 11px;
                font-family: Verdana, Arial;
                background: rgba(0,0,0,0.8);
                padding: 2px;
                margin: 0;
                text-shadow: 0px 0px 10px rgba(0, 0, 0, 1),1px 1px #000, -1px -1px 0 #000, 1px -1px 0 #000, -1px 1px 0 #000;
            }
        </style>
EOL;

        return $output;
    }
}
