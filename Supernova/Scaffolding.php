<?php

namespace Supernova;

class Scaffolding
{
    public static function executeindex()
    {
        $namespace = "\App\Model\\".\Supernova\Controller::$model['singular'];
        $puralModelName = \Supernova\Controller::$model['plural'];
        $$puralModelName = $namespace::find();
        \Supernova\Controller::set(compact($puralModelName));
    }

    public static function executeAdd()
    {
    }

    public static function executeEdit($id = null)
    {
        $modelName = \Supernova\Controller::$model['singular'];
        $namespace = "\App\Model\\".$modelName;
        $object = new $namespace();
        $$modelName = $namespace::findOne([ 'where' => [ $object->primaryKey => [ "=" => $id ]]]);
        if ($$modelName) {
            \Supernova\Controller::set(compact($modelName));
        } else {
            \Supernova\Controller::flash(["message" => __("Data does not exist for this Id"), "status" => "danger"]);
            \Supernova\Controller::redirect(["prefix" => \Supernova\Core::$elements['prefix'], "controller" => $modelName, "action" => "index"]);
        }
    }

    public static function executeDelete($id = null)
    {
        $modelName = \Supernova\Controller::$model['singular'];
        $namespace = "\App\Model\\".$modelName;
        $object = new $namespace();
        $$modelName = $namespace::findOne([ 'where' => [ $object->primaryKey => [ "=" => $id ]]]);
        if ($$modelName) {
            $$modelName->delete();
            \Supernova\Controller::flash(["message" => __("Delete successful"), "status" => "success"]);
        } else {
            \Supernova\Controller::flash(["message" => __("Data does not exist for this Id"), "status" => "danger"]);
        }
        \Supernova\Controller::redirect(["prefix" => \Supernova\Core::$elements['prefix'], "controller" => $modelName, "action" => "index"]);
    }

    public static function templateIndex()
    {
        extract(\Supernova\View::$values);
        $name = \Supernova\Core::$elements['controller'];
        $pluralName = \Supernova\Inflector::pluralize($name);
        $title = inject(__("List from %name%"), [ "name" => $pluralName ]);
        $table = \Supernova\Helper::table(
            [
                'values' => $$pluralName,
                'use' =>
                [
                    'created' => '\Supernova\Helper::formatDate',
                    'updated' => '\Supernova\Helper::formatDate'
                ]
            ]
        );

        $link = \Supernova\Helper::link(
            [
                "href" => \Supernova\Route::generateUrl(["prefix" => \Supernova\Core::$elements['prefix'], "controller" => $name, "action" => "Add"]),
                "text" => inject(__("Add %name%"), [ "name" => $name ])
            ]
        );

        return "
        <div class='panel panel-default' id='buttons'>
            <div class='panel-heading'>$title</div>
            <div class='panel-body'>
                $table
                $link
            </div>
        </div>
        ";

    }

    public static function templateAdd()
    {
        extract(\Supernova\View::$values);
        $name = \Supernova\Core::$elements['controller'];
        $title = inject(__("Add %name%"), [ "name" => $name ]);
        $form = \Supernova\Form::create([ "model" => $name ]);
        $link = \Supernova\Helper::link(
            [
                "href" => \Supernova\Route::generateUrl(["prefix" => \Supernova\Core::$elements['prefix'], "controller" => $name, "action" => "index"]),
                "text" => __("<< Back")
            ]
        );

        return "
        <h3>$title</h3>
        $form
        $link
        ";

    }

    public static function templateEdit()
    {
        extract(\Supernova\View::$values);
        $name = \Supernova\Core::$elements['controller'];
        $title = inject(__("Edit %name%: %item%"), [ "name" => $name, "item" => $$name ]);
        $form = \Supernova\Form::create([ "model" => $name, "values" => $$name ]);
        $link = \Supernova\Helper::link(
            [
                "href" => \Supernova\Route::generateUrl(["prefix" => \Supernova\Core::$elements['prefix'], "controller" => $name, "action" => "index"]),
                "text" => __("<< Back")
            ]
        );

        return "
        <h3>$title</h3>
        $form
        $link
        ";
    }
}
