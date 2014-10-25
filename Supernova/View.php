<?php

namespace Supernova;

class View extends \Supernova\Controller
{
    public static $values = array();
    public static $layout = "default";

    public static function set($name, $value = null)
    {
        self::$values[$name] = $value;
    }

    /**
     * Ingresa mensaje en sesión
     * @param string $msg Mensaje
     * @param string $key Nombre de la variable de sesión
     */
    public static function setMessage($msg, $key = 'message')
    {
        \Supernova\Session::create($key, $msg);
    }

    /**
     * Obtiene mensaje en sesión
     * @param   String  $key    Tipo
     */
    public static function getMessage($key = 'message')
    {
        //if (empty($this->errors)) {
        $msg = \Supernova\Session::read($key);
        if (!empty($msg)) {
            \Supernova\Session::destroy($key);
            return $msg;
        }
    }

    /**
     * Muestra Layout y Template
     */
    public static function render()
    {
        //header('Content-Type: text/html; charset=UTF-8');
        $model = \Supernova\Core::$elements['controller'];
        $prefix = (empty(\Supernova\Core::$elements['prefix'])) ? "Default" : \Supernova\Core::$elements['prefix'];
        $namespace = "\App\Model\\".$model;
        $viewName = \Supernova\Inflector::camelToUnder(\Supernova\Core::$elements['prefix'].\Supernova\Core::$elements['action']);
        $viewFile = "View".DS.$model.DS.$viewName.".php";
        $appView = ROOT.DS."App".DS.$viewFile;
        $pluginView = ROOT.DS."Plugins".DS.$model.DS.$viewFile;
        $views = array($appView, $pluginView);
        $content_for_layout = self::getContent($views);
        
        if (empty($content_for_layout)) {
            if (class_exists($namespace)) {
                $object = new $namespace();
                if ($object->scaffolding == true) {
                    preg_match("/(index|add|edit|delete)/i", \Supernova\Core::$elements['action'], $matches);
                    $actionFnName = "template".current($matches);
                    $content_for_layout = \Supernova\Scaffolding::$actionFnName();
                }
            }
        }
        
        if ($content_for_layout === false) {
            trigger_error(__("View not found:")." ".\Supernova\Core::$elements['prefix'].\Supernova\Core::$elements['action']." ".__('in')." ".\Supernova\Core::$elements['controller'], E_USER_ERROR);
            die();
        }

        $layoutFile = ROOT.DS."App".DS."Prefix".DS.$prefix.DS.self::$layout.".php";
        if (file_exists($layoutFile)) {
            include($layoutFile);
        } else {
            trigger_error(__("Layout")." ".self::$layout.".php ".__("not found in prefix")." ".$prefix, E_USER_ERROR);
            die();
        }
    }

    /**
     * Obtener contenido del template
     * @param  array  $views Arreglo con los archivos con los templates
     * @return mixed         Retorna el contenido del primer archivo que encuentre o false si es ninguno
     */
    public static function getContent($views = array())
    {
        extract(self::$values);
        ob_start((ini_get("zlib.output_compression") == 'On') ? "ob_gzhandler" : null);
        foreach ($views as $view) {
            if (file_exists($view)) {
                include($view);
                $content = ob_get_contents();
                ob_end_clean();
                return $content;
            }
        }
        return false;
    }

    /**
     * Incluye un archivo CSS
     * @param  string $cssFile nombre de archivo sin extension .css
     * @return string          Vinculo con el archivo CSS
     */
    public static function includeCss($cssFile)
    {
        $publicUrl = \Supernova\Route::getPublicUrl();
        $prefix = (empty(\Supernova\Core::$elements['prefix'])) ? "Default" : \Supernova\Core::$elements['prefix'];
        $output = "<link rel='stylesheet' href='$publicUrl/$prefix/css/$cssFile.css' />";
        return $output;
    }

    /**
     * Incluye un archivo javascript
     * @param  string $jsFile Nombre de archivo sin extension .js
     * @return string         Vinculo con el archivo JS
     */
    public static function includeJs($jsFile)
    {
        $publicUrl = \Supernova\Route::getPublicUrl();
        $prefix = (empty(\Supernova\Core::$elements['prefix'])) ? "Default" : \Supernova\Core::$elements['prefix'];
        $output = "<script type='text/javascript' src='$publicUrl/$prefix/js/$jsFile.js' ></script>";
        return $output;
    }

    /**
     * Llama a las paginas de error
     * @param  integer $num          Numero de error a mostrar
     * @param  string  $encodedError Depuración de error encriptado
     */
    public static function callError($num = 404, $encodedError = "")
    {
        include(ROOT.DS.\Supernova\Route::getPublicFolder().DS."errors".DS.$num.'.php');
        die();
    }
}
