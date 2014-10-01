<?php
define('HTACCESS', false); // cuando mod_rewrite esta inactivo en apache queda falso
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', str_replace('\\', DS, dirname(__FILE__)));

include (ROOT.DS."Supernova".DS."bootstrap.php");
