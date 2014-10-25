<?php

namespace Supernova;

/**
 * Núcleo de Supernova
 */
class Core
{
    public static $namespace;
    public static $elements = array();
    private static $request = array();
    private static $dependences = array('mcrypt','mysql','pdo');

    /**
     * Ingresa los elementos
     *
     * Supernova separa cada capa de la aplicación en "elementos",
     * entre los que se encuentran:
     * Lenguaje, Prefijo, Controlador, Accion
     */
    public static function setElements()
    {
        require ROOT. DS . "Config" . DS . "routing.php";
        $currentAlias = "/".current(self::$request['get']);
        if (array_key_exists($currentAlias, $routing['alias'])) {
            self::$elements = $routing['alias'][$currentAlias];
        } else {
            if ($routing['actAsBehaviour']) {
                foreach ($routing['behaviourOrder'] as $eachCall) {
                    $function = "set".ucfirst($eachCall);
                    $$eachCall = \Supernova\Core::$function(self::getGetParameters());
                    if ($$eachCall !== false) {
                        array_shift(self::$request['get']);
                    }
                }
            }
        }
    }

    /**
     * Verifica si las dependencias necesarias estan cargadas en PHP
     */
    public static function moduleCheck()
    {
        try {
            foreach (self::$dependences as $extension) {
                if (!extension_loaded($extension)) {
                    throw new Exception($extension);
                }
            }
        } catch (Exception $e) {
            debug(inject(__('extension %1 not loaded'), array("%1" => $e->getMessage())));
            \Supernova\View::callError(500);
        }
    }

    /**
     * Inicializa y limpia parametros
     */
    public static function initialize()
    {
        date_default_timezone_set(TIMEZONE);
        \Supernova\Profiler::start();
        \Supernova\Session::start();
        \Supernova\Security::cleanAll();
        \Supernova\Form::setContentType();
        \Supernova\Translate::setLanguage(LANGUAGE_DEFAULT);
        self::setPostParameters(\Supernova\Security::sanitize($_POST));
        self::setFilesParameters(\Supernova\Security::sanitize($_FILES));
    }

    /**
     * Ingresa parametros GET
     * @param array $params Parametros GET
     */
    public static function setGetParameters($params = array())
    {
        self::$request['get'] = \Supernova\Security::sanitize($params);
        unset($_GET);
    }

    /**
     * Ingresa parametros POST
     * @param array $params Parametros POST
     */
    public static function setPostParameters($params = array())
    {
        self::$request['post'] = $params;
        unset($_POST);
    }

    /**
     * Ingresa parametros de FILES (Archivos)
     * @param array $params Parametros FILES
     */
    public static function setFilesParameters($params = array())
    {
        self::$request['files'] = $params;
        unset($_FILES);
    }

    /**
     * Obtiene parametros de FILES (Archivos)
     * @return array Parametros de FILES
     */
    public static function getFilesParameters()
    {
        return self::$request['files'];
    }

    /**
     * Obtiene parametros GET
     * @return array Parametros GET
     */
    public static function getGetParameters()
    {
        return self::$request['get'];
    }

    /**
     * Obtener parametros POST
     * @return array Parametros POST
     */
    public static function getPostParameters()
    {
        return self::$request['post'];
    }

    /**
     * Ingresa el request de la ruta
     */
    public static function setRequest()
    {
        if (HTACCESS) {
            $pos = strrpos($_SERVER['QUERY_STRING'], "url=");
            self::setGetParameters(explode('/', ($pos !== false) ? str_replace('url=', '', $_SERVER['QUERY_STRING']) : ''));
        } else {
            self::setGetParameters(explode('/', $_SERVER['QUERY_STRING']));
        }
    }

    /**
     * Ingresa el nombre del controlador
     * @param array $urlQuery Arreglo con request
     */
    public static function setController($urlQuery)
    {
        $controller = \Supernova\Inflector::underToCamel(current($urlQuery));
        if (empty($controller)) {
            debug(__("No controller called"));
            \Supernova\View::callError(404);
        }
        self::$elements['controller'] = $controller;
        return true;
    }

    /**
     * Ingresa el nombre de la acción
     * @param array $urlQuery Arreglo con request
     */
    public static function setAction($urlQuery)
    {
        $action = \Supernova\Inflector::underToCamel(current($urlQuery));
        $action = (!empty($action)) ? $action : "Index"; // default action: Index
        self::$elements['action'] = $action;
        return true;
    }

    /**
     * Ingresa el nombre del prefijo
     * @param array $urlQuery Arreglo con request
     */
    public static function setPrefix($urlQuery)
    {
        $prefix = ucfirst(\Supernova\Inflector::underToCamel(current($urlQuery)));
        require ROOT. DS . "Config" . DS . "routing.php";
        if (in_array($prefix, $routing['prefix'])) {
            self::$elements['prefix'] = $prefix;
            return true;
        }
        self::$elements['prefix'] = "";
        return false;
    }

    /**
     * Ingresa el prefijo de lenguaje
     * @param array $urlQuery Arreglo con request
     */
    public static function setLanguage($urlQuery)
    {
        $language = current($urlQuery);
        $file = ROOT.DS.'Locale'.DS.$language.'.php';
        if (file_exists($file) || $language == "en") {
            \Supernova\Translate::setLanguage($language);
            return true;
        }
        \Supernova\Translate::setLanguage(LANGUAGE_DEFAULT);
        return false;
    }

    /**
     * Carga el controlador en memoria
     */
    public static function checkController()
    {
        $controller = self::$elements['controller'];
        $appClass = "\App\Controller\\".$controller."Controller";
        $pluginClass = "\Plugins\\".$controller."\Controller\\".$controller."Controller";
        if (class_exists($appClass)) {
            self::$namespace = $appClass;
        } elseif (class_exists($pluginClass)) {
            self::$namespace = $pluginClass;
        } else {
            debug(__("Controller not exist:")." <strong>".$controller."<strong>");
            \Supernova\View::callError(404);
        }
    }

    /**
     * Carga la acción en memoria
     */
    public static function checkAction()
    {
        $mainAppController = new \App\Main();
        $mainAppController->beforeController();
        $mainController = new \Supernova\Controller();
        $namespace = self::$namespace;
        $actionClass = new $namespace();
        if (method_exists($namespace, "execute".self::$elements['prefix'].self::$elements['action'])) {
            call_user_func_array(array($actionClass, "execute".self::$elements['prefix'].self::$elements['action']), self::$request['get']);
        } else {
            if (method_exists($namespace, "execute".self::$elements['action'])) {
                call_user_func_array(array($actionClass, "execute".self::$elements['action']), self::$request['get']);
            } else {
                debug(__("Action not exist:")." <strong>execute".self::$elements['action']."</strong> ".__("in controller:")." <strong>".$namespace."</strong>");
                \Supernova\View::callError(404);
            }
        }
        $mainAppController->afterController();
        \Supernova\View::render();
    }

    /**
     * Procesa el formulario recibido en POST
     */
    public static function processForm()
    {
        $namespace = "\App\Model\\".self::$elements['controller'];
        if (class_exists($namespace)) {
            $namespace::processPost();
            $namespace::processFiles();
        }
    }

    /**
     * Verifica si la conexión es segura (SSL)
     * @return bool true o false
     */
    public static function checkSSL()
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? true : false;
    }
}
