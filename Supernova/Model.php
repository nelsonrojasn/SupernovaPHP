<?php

namespace Supernova;

class Model extends \Supernova\Sql
{
    protected $results;

    public function __set($name, $value)
    {
        $this->results[$name] = $value;
    }

    public function __toString()
    {
        if (isset($this->results[$this->defaultKey])) {
            return $this->results[$this->defaultKey];
        }

        return null;
    }

    public function __call($name, $arguments = array())
    {
        $functionPerfix = substr($name, 0, 3);
        $functionName = substr($name, 3);
        if (method_exists($this, $functionPerfix.$functionName)) {
            return call_user_func_array(array($this, $name), $arguments);
        } else {
            switch ($functionPerfix) {
                case 'get':
                    return $this->get(substr($name, 3));
                    break;
                case 'set':
                    $this->set(substr($name, 3), $arguments);
                    break;
                default:
                    return call_user_func_array(array($this, $name), $arguments);
                    break;
            }
        }
    }

    private function get($value)
    {
        $modelName = \Supernova\Inflector::singularize($value);
        $value = \Supernova\Inflector::camelToUnder($value);
        if (isset($this->results[$value])) {
            return $this->results[$value];
        } else {
            // Check for belongsTo or hasMany
            if (isset($this->belongsTo) && !empty($this->belongsTo)) {
                if (array_key_exists($modelName, $this->belongsTo)) {
                    $namespace = "\App\Model\\".$modelName;
                    $vars = get_class_vars($namespace);
                    $foreingKey = $this->belongsTo[$modelName]['foreingKey'];
                    return $namespace::find(array('where' => array($vars['primaryKey'] => array('=' => $this->results[$foreingKey]))));
                }
            }
            trigger_error(__("Can't get value, column not exist").": ".$value, E_USER_ERROR);
        }
    }

    private function set($value, $args = null)
    {
        if ($args) {
            $value = \Supernova\Inflector::camelToUnder($value);
            $this->results[$value] = current($args);
        }
    }

    private function toArray()
    {
        return $this->results;
    }

    private function fromArray($array = array())
    {
        foreach ($array as $k => $v) {
            $this->results[$k] = $v;
        }
    }

    private function notEmpty()
    {
        return ( count($this->results) > 0 ) ? false : true;
    }

    private function save()
    {
        return \Supernova\Sql::saveResult($this);
    }

    private function delete()
    {
        return \Supernova\Sql::removeResult($this);
    }

    private function validate()
    {
        return true;
    }

    private function isValid()
    {
        if ($this->save()) {
            \Supernova\Controller::flash(array("message" => __("Save successful"), "status" => "success"));
            return true;
        } else {
            \Supernova\Controller::flash(array("message" => __("Save failed"), "status" => "danger"));
            return false;
        }
    }

    private function onError()
    {
        \Supernova\Controller::flash(array("message" => __("Validation error"), "status" => "danger"));
        return false;
    }

    public static function __callStatic($name, $arguments)
    {
        return call_user_func_array("\Supernova\Sql::".$name, $arguments);
    }

    public static function processPost()
    {
        if ($post = \Supernova\Core::getPostParameters()) {
            $namespace = "\App\Model\\".\Supernova\Core::$elements['controller'];
            $formNamespace = $namespace."Form";
            $objectForm = new $formNamespace();
            foreach ($post['data'] as $model => $values) {
                if ($namespace == "\App\Model\\".ucfirst(\Supernova\Inflector::underToCamel($model))) {
                    $object = new $namespace();
                    foreach($values as $key => $val){
                        $setter = "set".ucfirst(\Supernova\Inflector::underToCamel($key));
                        if (isset($objectForm->settings[$key]['widget']['use']) && !empty($objectForm->settings[$key]['widget']['use'])){
                            $fn = explode("::",$objectForm->settings[$key]['widget']['use']);
                            if (method_exists($fn[0], $fn[1])) {
                                $val = $fn[0]::$fn[1]($val);
                            }
                        }
                        $object->$setter($val);
                    }
                    return ($object->isValid()) ? true : $object->onError();
                }
            }
        }
        return null;
    }

    public static function processFiles()
    {
        $files = \Supernova\Core::getFilesParameters();
        if (!empty($files)) {
            debug($files);
        }
    }
}
