<?php

namespace Supernova;

class Helper extends \Supernova\View
{
    public static function table($args)
    {
        $objects = $args['values'];
        if (!($objects)) {
            return "<table class='table table-striped' ><tr><td>".__("No results")."</td></tr></table>";
        } else {
            if (!is_object(current($objects))) {
                trigger_error(__("Objects not found in value called for Helper::table. Array or string found"), E_USER_WARNING);
            }
            $modelName = explode("\\", get_class(current($objects)));
            $modelName = end($modelName);
            $model = "\App\Model\\".$modelName;
            $modelForm = $model."Form";
            $form = new $modelForm();

            // get header
            $fields = current($objects)->toArray();
            $labels = array_keys($fields);
            $labelTH = "";
            foreach ($labels as $label) {
                $labelTH.= "<th>";
                $labelTH.= (isset( $form->settings[$label]["widget"]["label"] )) ? $form->settings[$label]["widget"]["label"] : $label ;
                $labelTH.= "</th>";
            }
            $labelTH.="<th>Acciones</th>";

            $eachData="";
            foreach ($objects as $object) {
                $results = $object->toArray();
                $eachData.="<tr>";
                foreach ($results as $fields => $values) {
                    $eachData.="<td>";
                    if (isset($args['use']) && isset($args['use'][$fields])) {
                        if (is_array($args['use'][$fields])) {
                            $eachData.=$args['use'][$fields][$values];
                        }
                        if (is_string($args['use'][$fields])) {
                            $fn = explode("::", $args['use'][$fields]);
                            if (method_exists($fn[0], $fn[1])) {
                                if ($fn[1] != "getList") {
                                    $eachData.=$fn[0]::$fn[1]($values);
                                } else {
                                    $list = $fn[0]::$fn[1]();
                                    $eachData.= (isset($list[$values])) ? $list[$values] : "";
                                }
                            }
                        }
                    } else {
                        $eachData.= $values;
                    }
                    $eachData.="</td>";
                }
                $actions = (isset($args['actions'])) ? $args['actions'] : array( 'edit' => __('Edit'), 'delete' => __('Delete') );
                if ($actions) {
                    $eachData.="<td>";
                    $eachLink="";
                    foreach ($actions as $action => $label) {
                        $eachLink.= \Supernova\Helper::link(
                            array(
                                "href" => \Supernova\Route::generateUrl(array("prefix" => \Supernova\Inflector::camelToUnder(\Supernova\Core::$elements['prefix']), "controller" => \Supernova\Inflector::camelToUnder(\Supernova\Core::$elements['controller']), "action" => $action, "id" => $results['id'])),
                                "text" => $label
                            )
                        );
                    }
                    $eachData.=$eachLink;
                    $eachData.="</td>";
                }
                $eachData.="</tr>";
            }
        }

        $output = <<<EOL
        <table class="table table-striped" >
            <thead>
                <tr>
                    $labelTH
                </tr>
            </thead>
            <tbody>
                $eachData
            </tbody>
            <tfoot>
            </tfoot>
        </table>
EOL;

        return $output;
    }

    public static function link($args = array())
    {
        $args['class'] = (isset($args['class']) && !empty($args['class'])) ? $args['class'] : array( "btn", "btn-default" );

        return "<a href='".$args["href"]."' class='".implode(" ", $args['class'])."' >".$args["text"]."</a>";
    }

    public static function formatDate($date, $args = array())
    {
        $args['from'] = (isset($args['from'])) ? $args['from'] : "Y-m-d h:i:s";
        $args['to'] = (isset($args['to'])) ? $args['to'] : __($args['from']);
        extract(date_parse_from_format($args['from'], $date));

        return date($args['to'], mktime($hour, $minute, $second, $month, $day, $year));
    }
}
