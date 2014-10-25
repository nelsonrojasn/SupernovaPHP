<?php

namespace Supernova;

/**
 * Depuración
 */
class Debug
{
    /**
     * Almacena el historico de peticiones SQL
     * @var array
     */
    private static $logQuery = array();

    /**
     * Tipos de errores
     * @var array
     */
    private static $errorType = array(
            E_ERROR              => 'Error',
            E_WARNING            => 'Warning',
            E_PARSE              => 'Parsing Error',
            E_NOTICE             => 'Notice',
            E_CORE_ERROR         => 'Core Error',
            E_CORE_WARNING       => 'Core Warning',
            E_COMPILE_ERROR      => 'Compile Error',
            E_COMPILE_WARNING    => 'Compile Warning',
            E_USER_ERROR         => 'User Error',
            E_USER_WARNING       => 'User Warning',
            E_USER_NOTICE        => 'User Notice',
            E_STRICT             => 'Runtime Notice',
            E_RECOVERABLE_ERROR  => 'Catchable Fatal Error',
            8192                 => 'Unknown'
    );

    /**
     * Muestra cuadro Debug
     * @param  mixed $str String o array con valores
     */
    public static function render($str)
    {
        if (!defined("ENVIRONMENT") || ENVIRONMENT == "dev") {
            $backtrace = debug_backtrace();
            $firstTrace = $backtrace[0];
            $secondTrace = $backtrace[1];
            $file = str_replace(ROOT.DS, '', $secondTrace['file']);
            $line = $secondTrace['line'];
            $object = "";
            if (isset($secondTrace['object']) && is_object($secondTrace['object'])) {
                $object = get_class($object);
            }
            echo self::style();
            echo self::drawDebugBox($object, $line, $file, $str);
        }
    }

    /**
     * Muestra cuadro de Error
     * @param  integer $type    Numero de error
     * @param  string $message Mensaje de error
     * @param  string $file    Archivo de error
     * @param  string $line    Linea de error
     * @param  string $str     Depurado de error
     */
    public static function renderError($type, $message = "", $file = "", $line = "", $str = "")
    {
        $fullfile = $file;
        $file = str_replace(ROOT.DS, '', $file);
        if (ob_get_contents()){
            ob_end_clean();  
        }
        if (!defined("ENVIRONMENT") || ENVIRONMENT == "dev") {
            $errorColor = (in_array($type, array(E_ERROR,E_CORE_ERROR,E_USER_ERROR))) ? "#F6D8CE" : "#FFFFCC";
            echo self::style($errorColor);
            echo self::drawDebugBox($type, $line, $file, $message);
        }
        echo self::showLines(self::getLines($fullfile, $line), $line);
        $br = "\n";
        $text = $type.$br.$line.$br.$file.$br.$message.$br.implode($br, self::getLines($fullfile, $line));
        $encodedError = \Supernova\Crypt::encrypt($text);
        //$encodedError = \Supernova\Crypt::decrypt($encodedError);
        \Supernova\View::callError(500, $encodedError);
    }
    
    /**
     * Dibuja cuadro de Debug
     * @param  string $object Nombre del objeto
     * @param  integer $line  Linea de error
     * @param  string $file   Archivo de error
     * @param  string $str    Mensaje de error
     */
    private static function drawDebugBox($object, $line, $file, $str = "")
    {
        $object = ( isset(self::$errorType[$object]) ) ? __(self::$errorType[$object]) : __($object);
        $str = print_r($str, true);
        $lineStr = __('Line');
        $fileStr = __('File');
        if ($file != "index.php") {
            $output = <<<EOL
            <div class='debug-box'>
                <h3>$object</h3>
                <p>$lineStr <strong>$line</strong> :: $fileStr <strong>$file</strong></p>
                <pre>$str</pre>
            </div>
EOL;
        } else {
            $output = <<<EOL
            <div class='debug-box'>
                <pre>$str</pre>
            </div>
EOL;
        }

        return $output;
    }

    private static function style($errorColor = "#FFC")
    {
        $output = <<<EOL
        <style>
            .debug-box{
                margin: 10px 10px;
                padding: 13px 20px 15px 15px;
                border: 1px solid grey;
                background: $errorColor;
                border-radius: 3px;
                overflow: hidden;
                font-family: Verdana, Arial;
                font-size: 11px;
                text-shadow: 5px 3px 8px rgba(0, 0, 0, .30)
            }
            .debug-box pre{
                overflow: auto;
                border: 1px solid grey;
                border-radius: 5px;
                background: #FEFEFE;
                padding: 10px;
                word-wrap: break-word;
            }
            .encodedError{
                width: 95%;
                border: 1px solid grey;
                border-radius: 5px;
                overflow: auto;
                font-family: “Consolas”,monospace;
                font-size: 9pt;
                text-align:left;
                background-color: #FCF7EC;
                overflow-x: auto; /* Use horizontal scroller if needed; for Firefox 2, not
                white-space: pre-wrap; /* css-3 */
                white-space: -moz-pre-wrap !important; /* Mozilla, since 1999 */
                word-wrap: break-word; /* Internet Explorer 5.5+ */
                margin: 15px 0px 0px 0px;
                padding: 7px 9px 7px 11px;
                white-space : normal; /* crucial for IE 6, maybe 7? */
                height: 150px;
            }

            .debug-box .checkLine{
                background: #FFDDDD;
                min-width: 100%;
            }

            .debug-box .checkError{
                background: #FFD4D4;
                min-width: 100%;
            }
        </style>
EOL;

        return $output;
    }

    /**
     * Almacena peticiones SQL en Histórico
     * @param  string $query pretición SQL
     */
    public static function logQuery($query = "")
    {
        $backtrace = debug_backtrace();
        $class = $backtrace[3]['class'];
        $method = $backtrace[3]['function'];
        $line = $backtrace[2]['line'];
        self::$logQuery[$class."->".$method."() ".__("Line").":".$line] = $query;
    }

    /**
     * Muestra el histórico de peticiones SQL registrados
     * @return [type] [description]
     */
    public static function showQuery()
    {
        $output ="<table style='font-size: 9px; width:750px; text-align:left; margin: 5px auto;'>";
        $output.="<tr><th colspan='2' style='color: yellow;'>".__("Log SQL Query")."</th></tr>";
        foreach (self::$logQuery as $where => $query) {
            $output.="<tr><td style='color: white; border-top: 1px solid black; border-bottom: 1px solid black;'>$where</td>".
            "<td style='color:lightgreen; border-top: 1px solid black; border-bottom: 1px solid black;'>$query</td></tr>";
        }
        $output.="</table>";
        return $output;
    }

    /**
     * Obtiene las lineas de un archivo a depurar
     * @param  string  $file   Nombre del archivo
     * @param  integer $line   Numero de linea
     * @param  integer $expand Expandir cantidad de lineas
     */
    private static function getLines($file, $line, $expand = 6)
    {
        $source = file($file);
        $body = array_slice($source, $line-$expand, $expand*2);
        return $body;
    }

    /**
     * Mostrar lineas en el depurador
     * @param  string  $body   Cuerpo del archivo
     * @param  integer $line   Numero de linea del archivo a marcar
     * @param  integer $expand Expandir cantidad de lineas
     */
    private static function showLines($body, $line, $expand = 6)
    {
        $output = "<div class='debug-box'>";
        $output.="<pre>";
        $output.="".__("Line")."\t".__("Source")."\n";
        $output.="<hr/>";
        $counter = 1;
        foreach ($body as $eachLine) {
            $linenum = $line - $expand + $counter++;
            $checkLine = ($linenum >= $line-1 && $linenum <= $line+1) ? "checkLine" : "";
            $checkError = ($linenum == $line) ? "checkError" : "";
            $output.="<div class='".$checkLine." ".$checkError."'>".$linenum."\t".htmlentities($eachLine)."</div>";
        }
        $output.="</pre>";
        $output.="</div>";
        return $output;
    }
}
