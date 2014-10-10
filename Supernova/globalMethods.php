<?php

/**
 * K.I.S.S. Autoloader :: Keep It Simple and Stupid ;)
 */
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

/**
 * debug : muestra depuración de variables en el navegador
 * @param  mixed $str Arreglo o string
 * @return null
 */
function debug($str)
{
    \Supernova\Debug::render($str);
}

/**
 * __ : function para traducción de texto
 * @param  string $str String original
 * @return string      String reemplazado
 */
function __($str)
{
    return \Supernova\Translate::text($str);
}

/**
 * inject : Inyecta variables a un string
 * @param  string $str  texto
 * @param  array  $vars variables a reemplazar en el string
 * @return string       string inyectado
 */
function inject($str = "", $vars = array())
{
    return \Supernova\Inflector::inject($str, $vars);
}
