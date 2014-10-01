<?php

namespace Supernova;

class Controller
{
    public static $model = array();
    public static $action;

    public function __construct()
    {
        self::$model['singular'] = ucfirst(\Supernova\Inflector::singularize(\Supernova\Core::$elements['controller']));
        self::$model['plural'] = ucfirst(\Supernova\Inflector::pluralize(\Supernova\Core::$elements['controller']));
        self::$action = ucfirst(\Supernova\Inflector::underToCamel(\Supernova\Core::$elements['prefix']));
        self::$action.= ucfirst(\Supernova\Inflector::underToCamel(\Supernova\Core::$elements['action']));
    }

    public function executeIndex()
    {
        \Supernova\Scaffolding::executeIndex();
    }

    public function executeAdd()
    {
        \Supernova\Scaffolding::executeAdd();
    }

    public function executeEdit($id = null)
    {
        \Supernova\Scaffolding::executeEdit($id);
    }

    public function executeDelete($id = null)
    {
        \Supernova\Scaffolding::executeDelete($id);
    }

    /**
    * Set variables to the view
    * @param	mixed	$name	Key for value or array with values
    * @param	mixed	$value	Values
    */
    public static function set($name, $value = null)
    {
        if (is_array($name)) {
            foreach ($name as $key => $value) {
                self::set($key, $value);
            }
        } else {
            \Supernova\View::set($name, $value);
        }
    }

    /**
     * Redirect to url
     * @param	mixed	$url	Url or array width controller and action
     */
    public static function redirect($url = null)
    {
        ob_start();
        header('Location:'.\Supernova\Route::generateUrl($url));
        ob_flush();
        die();
    }

    public static function flash($args)
    {
        $class = (isset($args['status'])) ? $args['status'] : "success";
        $output= "<div class='alert alert-$class alert-dismissable'>";
        //$output.= "<i class='fa fa-$class'></i>";
        $output.= '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>';
        $output.= (isset($args['message'])) ? $args['message'] : "" ;
        $output.= "</div>";
        \Supernova\View::setMessage($output);
    }

    public static function setLayout($layout = "default")
    {
        \Supernova\View::$layout = $layout;
    }

    public static function disabled()
    {
        debug(__("Disabled view"));
        \Supernova\View::callError(404);
    }
}
