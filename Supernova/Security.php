<?php

namespace Supernova;

/**
 * Seguridad
 */
class Security
{
    /**
     * Limpia todos los request recibidos
     */
    public static function cleanAll(){
        self::removeMagicQuotes();
        self::unregisterGlobals();
        self::cleanAllVars();
    }

    private static function clean(&$target, $labels, $limit= 3)
    {
         if ($target) {
            foreach ($target as $key => $value) {
                if (is_array($value) && $limit > 0) {
                    self::clean($value, $labels, $limit - 1);
                } else {
                    $target[$key] = preg_replace_callback($labels, function ($matches) {
                        return "";
                    }, $value);
                }
            }
            return $target;
        }
    }

    private static function cleanAllVars()
    {
        if (isset($_SERVER['QUERY_STRING']) && strpos(urldecode($_SERVER['QUERY_STRING']), chr(0)) !== false) {
            \Supernova\View::callError(500);
        }

        if (@ini_get('register_globals')) {
            foreach ($_REQUEST as $key => $value) {
                $$key = null;
                unset ($$key);
            }
        }
        $labels = array (
            '@<script[^>]*?>.*?</script>@si',
            '@&#(\d+);@',
            '@\[\[(.*?)\]\]@si',
            '@\[!(.*?)!\]@si',
            '@\[\~(.*?)\~\]@si',
            '@\[\((.*?)\)\]@si',
            '@{{(.*?)}}@si',
            '@\[\+(.*?)\+\]@si',
            '@\[\*(.*?)\*\]@si'
        );

        foreach (array($_GET,$_POST,$_COOKIE,$_REQUEST) as $eachClean) {
            self::clean($eachClean, $labels);
        }

        foreach (array ('PHP_SELF', 'HTTP_USER_AGENT', 'HTTP_REFERER', 'QUERY_STRING') as $key) {
            $_SERVER[$key] = isset ($_SERVER[$key]) ? htmlspecialchars($_SERVER[$key], ENT_QUOTES) : null;
        }

        unset ($etiquetas, $key, $value);
    }

    private static function stripSlashesDeep($value)
    {
        if (!empty($value)) {
            $value = is_array($value) ? array_map('self::stripSlashesDeep', $value) : stripslashes($value);
        }
        return $value;
    }

    private static function removeMagicQuotes()
    {
        if ( get_magic_quotes_gpc() ) {
            $_GET    = self::stripSlashesDeep($_GET   );
            $_POST   = self::stripSlashesDeep($_POST  );
            $_COOKIE = self::stripSlashesDeep($_COOKIE);
        }
    }

    private static function unregisterGlobals()
    {
        if (ini_get('register_globals')) {
            $array = array('_SESSION', '_POST', '_GET', '_COOKIE', '_REQUEST', '_SERVER', '_ENV', '_FILES');
            foreach ($array as $value) {
                foreach ($GLOBALS[$value] as $key => $var) {
                    if ($var === $GLOBALS[$key]) {
                        unset($GLOBALS[$key]);
                    }
                }
            }
        }
    }

    /**
	 * Sanitiza datos
	 *
	 * @param	mixed	$data	Datos sin limpiar
	 * @return	mixed		    Datos limpios
	 */
    public static function sanitize($data)
    {
        if (!is_array($data)) {
            $data = trim($data);
            if (get_magic_quotes_gpc()) {
                $data = stripslashes($data);
            }
        } else {
            foreach ($data as $key => $dat) {
                $data[$key] = self::sanitize($dat);
            }
        }
        return $data;
    }

}
