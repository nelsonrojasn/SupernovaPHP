<?php

namespace Supernova;

/**
 * Controlador de Supernova
 */
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
    * Envía variables a la vista
    * @param	mixed	$name	arreglo
    * @param	mixed	$value	valores
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
     * Redirige a una url
     * @param	mixed	$url	Url o arreglo que especifica prefijo, controlador y accion
     */
    public static function redirect($url = null)
    {
        ob_start();
        header('Location:'.\Supernova\Route::generateUrl($url));
        ob_flush();
        die();
    }

    /**
     * Asigna mensaje flash en memoria hasta mostrarlo en la vista
     * @param  array $args  array("Mensaje","tipo")
     * @return null
     */
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

    /**
     * Define el layout a usar en el template
     * @param string $layout nombre del layout
     */
    public static function setLayout($layout = "default")
    {
        \Supernova\View::$layout = $layout;
    }

    /**
     * Deshabilita una acción en el controlador
     * @return null
     */
    public static function disabled()
    {
        debug(__("Disabled view"));
        \Supernova\View::callError(404);
    }
}
