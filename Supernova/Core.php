<?php

namespace Supernova;

class Core
{
    public static $elements = array();
    private static $request = array();
    public static $namespace;

    public static function initialize()
    {
        \Supernova\Profiler::start();
        session_start();
        $_SERVER['CONTENT_TYPE'] = "application/x-www-form-urlencoded";
        date_default_timezone_set(TIMEZONE);
        \Supernova\Translate::setLanguage(LANGUAGE_DEFAULT);
        \Supernova\Security::removeMagicQuotes();
        \Supernova\Security::unregisterGlobals();
        \Supernova\Security::cleanAllVars();
        self::setPostParameters(\Supernova\Security::sanitize($_POST));
        self::setFilesParameters(\Supernova\Security::sanitize($_FILES));
    }

    public static function setGetParameters($params = array())
    {
        self::$request['get'] = \Supernova\Security::sanitize($params);
        unset($_GET);
    }

    public static function setPostParameters($params = array())
    {
        self::$request['post'] = $params;
        unset($_POST);
    }

    public static function setFilesParameters($params = array())
    {
        self::$request['files'] = $params;
        unset($_FILES);
    }

    public static function getFilesParameters()
    {
        return self::$request['files'];
    }

    public static function getGetParameters()
    {
        return self::$request['get'];
    }

    public static function getPostParameters()
    {
        return self::$request['post'];
    }

    public static function setRequest()
    {
        if (HTACCESS) {
            $pos = strrpos($_SERVER['QUERY_STRING'], "url=");
            self::setGetParameters(explode('/', ($pos !== false) ? str_replace('url=', '', $_SERVER['QUERY_STRING']) : ''));
        } else {
            self::setGetParameters(explode('/', $_SERVER['QUERY_STRING']));
        }
    }

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

    public static function setAction($urlQuery)
    {
        $action = \Supernova\Inflector::underToCamel(current($urlQuery));
        $action = (!empty($action)) ? $action : "Index"; // default action: Index
        self::$elements['action'] = $action;
        return true;
    }

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

    public static function checkAction()
    {
        $mainAppController = new \App\Main();
        $mainAppController->beforeController();
        $mainController = new \Supernova\Controller();
        $namespace = self::$namespace;
        $actionClass = new $namespace();
        if (method_exists($namespace, "execute".self::$elements['prefix'].self::$elements['action'])) {
            call_user_func_array([$actionClass, "execute".self::$elements['prefix'].self::$elements['action']], self::$request['get']);
        } else {
            if (method_exists($namespace, "execute".self::$elements['action'])) {
                call_user_func_array([$actionClass, "execute".self::$elements['action']], self::$request['get']);
            } else {
                debug(__("Action not exist:")." <strong>execute".$action."</strong> ".__("in controller:")." <strong>".$namespace."</strong>");
                \Supernova\View::callError(404);
            }
        }
        $mainAppController->afterController();
        \Supernova\View::render();
    }

    public static function processForm()
    {
        $namespace = "\App\Model\\".self::$elements['controller'];
        if (class_exists($namespace)) {
            $namespace::processPost();
            $namespace::processFiles();
        }
    }

    public static function checkSSL()
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? true : false;
    }
}
