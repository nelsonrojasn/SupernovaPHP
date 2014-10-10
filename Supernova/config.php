<?php

/**
 * Carga constanstes desde el archivo de configuración
 */
require_once ROOT. DS . "Config" . DS . "config.php";
foreach ($config as $k => $v) {
    define($k, $v);
}
unset($config);

/**
 * Ajustes para el reporte de errores
 */
if (!is_defined("ENVIRONMENT") || ENVIRONMENT == "dev") {
    ini_set('display_errors', 'On');
    ini_set('error_reporting', E_ALL);
    ini_set('display_startup_errors', 'On');
    ini_set('log_errors', 'On');
} else {
    ini_set('display_errors', 'Off');
    ini_set('error_reporting', 0);
    ini_set('display_startup_errors', 'Off');
    ini_set('log_errors', 'Off');
}
