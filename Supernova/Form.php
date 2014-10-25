<?php

namespace Supernova;

/**
 * Modulo de Formularios
 */
class Form extends \Supernova\Model
{
    public static $counter = 0;

    public static function setContentType($type = "application/x-www-form-urlencoded")
    {
        return $_SERVER['CONTENT_TYPE'] = $type;
    }

    public static function csrfToken()
    {
        return md5(time().uniqid());
    }

    public static function create($args)
    {
        // Carga un nuevo objeto formulario // Load new form object
        $modelForm = "\App\Model\\".$args['model']."Form";
        $form = new $modelForm();
        $form->modelName = $args['model'];
        $form->values = (isset($args['values']) && !empty($args['values'])) ? $args['values']->toArray() : array();
        $form->schema = \Supernova\Cache::load($args['model']);
        // Genera el formulario // Generate form
        $action = (isset($args['action'])) ? \Supernova\Route::generateUrl($args['action']) : "";
        $output = "<form class='form-horizontal' role='form' action='$action' method='POST' >";
        // Genera los inputs // Generate inputs
        $form->notShow = array( "created", "updated", "created_at", "updated_at", "modified" , "creado_en", "actualizado_en" );
        foreach ($form->schema as $field) {
            if (!in_array($field['Field'], $form->notShow)) {
                $output.= self::input($field, $form);
            }
        }
        // Agrega boton submit en el formulario // Add submit button in form
        if (!(isset($args['submit']) && $args['submit'] == false)) {
            $output.= self::submit();
        }
        self::$counter++;
        return $output;
    }

    public static function input($field, $form)
    {
        $fieldName = $field['Field'];
        $value = (isset($form->values[$fieldName]) ) ? $form->values[$fieldName] : null ;
        if (isset($form->settings[$fieldName]["widget"])) {
            extract($form->settings[$fieldName]["widget"]);
        }
        $inputId = (isset($id)) ? $id : $fieldName."_form";
        $label = (isset($label)) ? $label : $fieldName;
        $class = (isset($class)) ? $class : "form-control";
        $type = (isset($field['Type'])) ? ((isset($type)) ? $type : $field['Type'] ): "text";
        $extraOutput = "";
        $output = "";
        if (isset($extra)) {
            foreach ($extra as $k => $v) {
                $extraOutput.="$k='$v' ";
            }
        }

        if ($fieldName == $form->primaryKey) {
            $type="hidden";
            $labelRender = "";
        } else {
            $labelRender = "<label for='$inputId' class='col-sm-3 control-label'>$label</label>";
        }

        $model = explode("\\", get_parent_class($form));
        $model = end($model);
        $counter = self::$counter;
        $inputName = "data[$model][$fieldName]";
        $type = explode("(", $type);
        $type = current($type);
        $inputRender = "";
        switch ($type) {
            case 'varchar':
            default:
                $inputRender = "<input type='$type' name='$inputName' class='$class' id='$inputId' value='$value' $extraOutput>";
                break;
            case 'select':
            case 'int':
                $inputRender.="<select name='$inputName' class='$class' id='$inputId' $extraOutput >";
                if (isset($form->settings[$fieldName]["widget"]["empty"]) && $form->settings[$fieldName]["widget"]["empty"]) {
                    $inputRender.="<option value=''>-- ".__("None")." --</option>";
                }
                if (isset($form->settings[$fieldName]["widget"]["options"])) {
                    if ($options = $form->settings[$fieldName]["widget"]["options"]) {
                        if (is_string($options)) {
                            $fn = explode("::", $form->settings[$fieldName]["widget"]["options"]);
                            if (method_exists($fn[0], $fn[1])) {
                                $options = $fn[0]::$fn[1]();
                            } else {
                                $options = array();
                            }
                        }
                        foreach ($options as $k => $v) {
                            $selected = ($k == $value) ? "selected" : "";
                            $inputRender.="  <option value='$k' $selected>$v</option>";
                        }
                    }
                }
                $inputRender.="</select>";
                break;
            case 'text':
            case 'textarea':
                $inputRender.="<textarea name='$inputName' class='$class' id='$inputId' $extraOutput>$value</textarea>";
                break;
            case 'checkbox':
            case 'tinyint':
            case 'bool':
            case 'boolean':
                $inputRender = "<input type='checkbox' name='$inputName' class='$class' id='$inputId' value='$value' $extraOutput>";
                break;
            case 'radio':
                $inputRender = "<input type='radio' name='$inputName' class='$class' id='$inputId' value='$value' $extraOutput>";
                break;
            /*
            TODO:
            case 'time' : break;
            case 'date' : break;
            case 'datetime' : break;
            */
        }

        $output = <<<EOL
        <div class="form-group">
            $labelRender
            <div class="col-sm-9">
                $inputRender
            </div>
        </div>
EOL;

        return $output;
    }

    public static function submit($submit = "Submit")
    {
        $submit = __($submit);
        $output=<<<EOL
        <div class="form-group">
            <div class="col-sm-offset-3 col-sm-9">
                <button type="submit" class="btn btn-default">$submit</button>
            </div>
        </div>
EOL;

        return $output;
    }
}
