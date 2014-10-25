<?php

namespace Supernova;

class Sql
{
    public static $connection;
    private static $db_name;

    public static function connect()
    {
        try {
            require ROOT. DS . "Config" . DS . "database.php";
            extract($dbconfig[ENVIRONMENT]);
            $dbn = $driver.":host=".$host.";dbname=".$database;
            self::$db_name = $database;
            self::$connection = new \PDO($dbn, $username, $password);
            self::$connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            self::$connection->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

            return true;
        } catch (\PDOException $e) {
            self::pdoErrors($e);

            return false;
        }
    }

    private static function pdoErrors($e)
    {
        $c = __('Conection failed');
        $c2 = __('Check your database configuration file');
        switch ($e->getCode()) {
            case '0':
                trigger_error($c.' :: '.__('No conection parameters').' :: '.$c2, E_USER_WARNING);
                break;
            case '2002':
                trigger_error($c.' :: '.__('Incorrect Host').' :: '.$c2, E_USER_WARNING);
                break;
            case '1044':
                trigger_error($c.' :: '.__('Incorrect Username').' :: '.$c2, E_USER_WARNING);
                break;
            case '1045':
                trigger_error($c.' :: '.__('Incorrect Password').' :: '.$c2, E_USER_WARNING);
                break;
            case '1049':
                trigger_error($c.' :: '.__('Incorrect Database Name').' :: '.$c2, E_USER_WARNING);
                break;
            case '42S02':
                $table = explode("'", $e->errorInfo[2]);
                trigger_error($c.' :: '.inject(__('Table %tablename% not found in database'), array( 'tablename' => '<strong>'.$table[1].'</strong>' )), E_USER_WARNING);
                break;
            case '42S22':
                $column = explode("'", $e->errorInfo[2]);
                trigger_error($c.' :: '.inject(__('Column %columnname% not found in database'),array( 'columnname' => '<strong>'.$column[1].'</strong>' )), E_USER_WARNING);
                break;
            default:
                trigger_error(__('SQL Error').' :: '.$e->getMessage(), E_USER_WARNING);
                break;
        }
    }

    public static function find($args = array())
    {
        $namespace = explode("\\", get_called_class());
        $model = end($namespace);
        $table = \Supernova\Inflector::camelToUnder(\Supernova\Inflector::pluralize($model));
        $query = 'SELECT * FROM `'.$table.'` '.self::parseConditions($args);

        return self::getQuery($query, $model);
    }

    public static function findOne($args = array())
    {
        $args['limit'] = 1;
        $find = self::find($args);

        return ($find) ? current($find) : false;
    }

    public static function getList($args = array())
    {
        $vars =  get_class_vars(get_called_class());
        $namespace = explode("\\", get_called_class());
        $model = end($namespace);
        $table = \Supernova\Inflector::pluralize(\Supernova\Inflector::camelToUnder($model));
        $query = 'SELECT '.$vars['primaryKey'].','.$vars['defaultKey'].' FROM `'.$table.'` '.self::parseConditions($args);
        $data = self::getQuery($query, $model);
        $getPrimary = "get".ucfirst(\Supernova\Inflector::underToCamel($vars['primaryKey']));
        $getDefault = "get".ucfirst(\Supernova\Inflector::underToCamel($vars['defaultKey']));
        foreach ($data as $dat) {
            $array[$dat->$getPrimary()] = $dat->$getDefault();
        }
        return $array;
    }

    public static function distinct($columns = array(), $args = array())
    {
        $query = 'SELECT DISTINCT '.explode(',', $columns).' FROM `'.$table.'` '.self::parseConditions($args);

        return self::getQuery($query);
    }

    private static function getQuery($query, $model)
    {
        \Supernova\Debug::logQuery($query);
        try {
            if (self::connect()) {
                $namespace = "\App\Model\\".$model;
                $result = self::$connection->query($query);
                $result->setFetchMode(\PDO::FETCH_CLASS, $namespace);
                $results = array();
                while ($row = $result->fetch()) {
                    $results[] = $row;
                }

                return $results;
            }
        } catch (\PDOException $e) {
            self::PDOErrors($e);

            return false;
        }
    }

    private static function parseConditions($conditions, $table = null)
    {
        $query = array();
        $wheres = array();
        if (is_array($conditions)) {
            foreach ($conditions as $type => $args) {
                switch (strtolower($type)) {
                    case 'where':
                        foreach ($args as $whereKey => $whereValues) {
                            foreach ($whereValues as $operand => $value) {
                                $in = array();
                                switch (strtolower($operand)) {
                                    case 'like':
                                        $wheres[] = " `".\Supernova\Security::sanitize($whereKey)."` LIKE '%".\Supernova\Security::sanitize($value)."%'";
                                        break;
                                    case '!like':
                                    case 'notLike':
                                    case 'not like':
                                        $wheres[] = " `".\Supernova\Security::sanitize($whereKey)."` NOT LIKE '%".\Supernova\Security::sanitize($value)."%'";
                                        break;
                                    case 'in':
                                        foreach ($value as $values) {
                                            $in[] = \Supernova\Security::sanitize($values);
                                        }
                                        $wheres[] = " IN (".explode(",", $in).") ";
                                        break;
                                    case '!in':
                                    case 'notIn':
                                    case 'not in':
                                        foreach ($value as $values) {
                                            $in[] = \Supernova\Security::sanitize($values);
                                        }
                                        $wheres[] = " NOT IN (".explode(",", $in).") ";
                                        break;
                                    case 'between':
                                        $wheres[] = " BETWEEN ".\Supernova\Security::sanitize(current($value)).",".\Supernova\Security::sanitize(end($value));
                                        break;
                                    default:
                                        $wheres[] = " `".\Supernova\Security::sanitize($whereKey)."` ".$operand." '".\Supernova\Security::sanitize($value)."'";
                                        break;
                                }
                            }
                        }
                        break;
                    case 'group':
                    case 'groupBy':
                    case 'group by':
                        array_push($query, " GROUP BY ".\Supernova\Security::sanitize($args));
                        break;
                    case 'order':
                    case 'orderBy':
                    case 'order by':
                        array_push($query, " ORDER BY ".\Supernova\Security::sanitize(current($args)))." ".end($args);
                        break;
                    case 'limit':
                        array_push($query, " LIMIT ".\Supernova\Security::sanitize($args));
                        break;
                    case 'join':
                    case 'innerJoin':
                    case 'inner join':
                        foreach ($args as $whereKey => $whereValues) {
                            foreach ($whereValues as $operand => $value) {
                                $joins[] = " `".\Supernova\Security::sanitize($whereKey)."` ".$operand." '".\Supernova\Security::sanitize($value)."'";
                            }
                        }
                        array_push($query, " INNER JOIN ".$table." ON ".explode(" AND ", $joins));
                        break;
                    case 'leftJoin':
                    case 'left join':
                        foreach ($args as $whereKey => $whereValues) {
                            foreach ($whereValues as $operand => $value) {
                                $joins[] = " `".\Supernova\Security::sanitize($whereKey)."` ".$operand." '".\Supernova\Security::sanitize($value)."'";
                            }
                        }
                        array_push($query, " LEFT JOIN ".$table." ON ".explode(" AND ", $joins));
                        break;
                    case 'rightJoin':
                    case 'right join':
                        foreach ($args as $whereKey => $whereValues) {
                            foreach ($whereValues as $operand => $value) {
                                $joins[] = " `".\Supernova\Security::sanitize($whereKey)."` ".$operand." '".\Supernova\Security::sanitize($value)."'";
                            }
                        }
                        array_push($query, " LEFT JOIN ".$table." ON ".explode(" AND ", $joins));
                        break;
                }
            }
        }
        $whereConditions = (!empty($wheres)) ? " AND ".implode(' AND ', $wheres) : "";
        array_unshift($query, " WHERE 1=1 ".$whereConditions);

        return implode(" ", $query).";";
    }

    public static function saveAll($objects)
    {
        foreach ($objects as $object) {
            self::saveResult($object);
        }
    }

    public static function saveResult($object)
    {
        if (self::connect()) {
            $namespace = explode("\\", get_class($object));
            $model = end($namespace);
            $results = $object->toArray();
            $table = \Supernova\Inflector::camelToUnder(\Supernova\Inflector::pluralize($model));

            // Update datetime
            if (self::checkField($table, "updated")) {
                $results["updated"] = date('Y-m-d H:i:s');
            }
            if (isset($results[$object->primaryKey]) && (is_null($results[$object->primaryKey]) || empty($results[$object->primaryKey]))) {
                if (self::checkField($table, "created")) {
                    $results["created"] = date('Y-m-d H:i:s');
                }
            }

            foreach ($results as $k => $v) {
                $keysName[$k] = $v;
                $keys[":".$k] = $v;
            }

            try {
                if (isset($results[$object->primaryKey]) && (is_null($results[$object->primaryKey]) || empty($results[$object->primaryKey]))) {
                    $query = "INSERT INTO ".$table." (".implode(",", array_keys($keysName)).") VALUES (".implode(",", array_keys($keys)).")";
                    $sth = self::$connection->prepare($query);
                    $sth->execute($keys);
                    $results[$object->primaryKey] = self::$connection->lastInsertId();
                    $object->fromArray($results);
                } else {
                    foreach ($results as $k => $v) {
                        $results[$k] = $k."=:".$k;
                    }
                    $query = "UPDATE ".$table." SET ".implode(",", $results)." WHERE ".$object->primaryKey."=:".$object->primaryKey;
                    $sth = self::$connection->prepare($query);
                    $sth->execute($keys);
                }
            } catch (\PDOException $e) {
                self::PDOErrors($e);

                return false;
            }

            return $object;
        }

        return false;
    }

    public static function removeAll($objects)
    {
        // Implementar transacciones
        foreach ($objects as $object) {
            self::removeResult($object);
        }
    }

    public static function removeResult($object)
    {
        if (self::connect()) {
            $namespace = explode("\\", get_class($object));
            $model = end($namespace);
            $results = $object->toArray();
            $table = \Supernova\Inflector::camelToUnder(\Supernova\Inflector::pluralize($model));
            $query = 'DELETE FROM '.$table.' WHERE `'.$object->primaryKey.'`=\''.\Supernova\Security::sanitize($results[$object->primaryKey]).'\'';
            $sth = self::$connection->prepare($query);

            return $sth->execute();
        }

        return false;
    }

    /**
     * Get tables from database
     * @return mixed Returns array with tables or false in error
     */
    public static function getTables()
    {
        if (self::connect()) {
            $query = 'SHOW TABLES FROM '.self::$db_name;
            $sth = self::$connection->prepare($query);

            return $sth->execute();
        }

        return false;
    }

    /**
     * Get fields from table
     * @param  string $table Table name
     * @return array  $fields Fields from table
     */
    public static function getFields($table)
    {
        if (self::connect()) {
            $query = 'SHOW FIELDS FROM '.self::$db_name.".".$table;
            $sth = self::$connection->prepare($query);
            $sth->execute();

            return $sth->fetchAll();
        }

        return false;
    }

    public static function checkField($table, $column)
    {
        if (self::connect()) {
            $table = self::$db_name.".".$table;
            $query = "SHOW COLUMNS FROM $table WHERE FIELD='$column'";
            $sth = self::$connection->prepare($query);
            $sth->execute();
            $row = $sth->fetch();

            return (empty($row)) ? false : true;
        }

        return false;
    }
}

 /*
        // Busqueda con todo lo que se pudiese agregar
        $table = \App\Model\Table::find([
                                            "where"=>[
                                                "column"=>[
                                                    ">=" => "2",
                                                    "!=" => "4",
                                                    "in" => [5,6,7,8],
                                                    "!in" => [9,10],
                                                    "between" => [
                                                        1,10
                                                    ]
                                                ]
                                            ],
                                            "order"=> ["column" => "asc"],
                                            "group"=> "column",
                                            "limit"=> "2",
                                            "join"=> ["table2" => "column2"],
                                        ]);

*/
