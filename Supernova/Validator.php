<?php

    namespace Supernova;

    class Validator
    {
        public static function check($post, $name)
        {
            $model = get_class_vars( "\App\Model\\".$name );
            $modelForm = get_class_vars( "\App\Model\\".$name."Form" );

        }

    }
