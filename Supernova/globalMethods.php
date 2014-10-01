<?php

// K.I.S.S. Autoloader :: Keep It Simple and Stupid ;)
spl_autoload_register(
    function ($className) {
        $file = str_replace('\\', DS, $className);
        if (file_exists(ROOT.DS.$file.".php")) {
            require_once ROOT.DS.$file.".php";
        } else if (file_exists(ROOT.DS.$file.".class.php")) {
            require_once ROOT.DS.$file.".class.php";
        }
    }
);

set_error_handler("errorHandler");
function errorHandler($type, $message, $file, $line, $str = "")
{
    \Supernova\Debug::renderError($type, $message, $file, $line, $str);
};

register_shutdown_function(
    function () {
        \Supernova\Profiler::end();
        \Supernova\Profiler::show();
        $error = error_get_last();
        if (!is_null($error)) {
            extract($error);
            errorHandler($type, $message, $file, $line);
        }
    }
);

function debug($str)
{
    \Supernova\Debug::render($str);
}

function __($str)
{
    return \Supernova\Translate::text($str);
}

function inject($str = "", $vars = array())
{
    return \Supernova\Inflector::inject($str, $vars);
}
