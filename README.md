Supernova Framework
===================

Partió el año 2012 con el codename: *SopaipillaPHP*, una base simple derivada de *VanillaPHP* con similitudes con *CakePHP*. Con el tiempo fue creciendo y se fue utilizando para distintos proyectos, aplicaciones, cms, blogs, en distintas agencias de desarrollo y proyectos freelance. El año 2013 se cambió el nombre y se lanzó la primera beta de Supernova. Como era de esperarse de un beta, se conocieron las carencias del proyecto y se abandonó a principios del 2013, sin embargo, la utilización, análisis de otros frameworks como *Symfony*, *Laravel* y *Yii* dieron nuevas ideas frescas para el proyecto, y se decidió partir desde cero en **Junio de 2014**, creando un nuevo *Supernova Framework* con su codename: *Supernova2*, mas rápido, mejor pensado, y superando todas las carencias del anterior

Las empresas de desarrollo necesitan que sus proyectos se cumplan en el menor tiempo posible, y para ello, se necesita de una herramienta en que el desarrollo no tenga perdidas de tiempo en estudiar y comprender un framework. Los desarrolladores pasaran con menos niveles de estrés, mas contentos, y mas motivados en sus trabajos, y evitará ponerlos en malas experiencias.

Actualmente los primeros beta de Supernova funcionan bajo otro nombre clave en instituciones de gobierno de Chile, carros de compra con Transbank, DineroMail, Servipag, landing pages de facebook y start-ups de los cuales no se puede hacer mención directa por cláusulas de privacidad, pero son al rededor de 20 sitios de los cuales el cliente ha quedado satisfecho.


Caracteristicas:
* Facil integración
* Curva rápida de aprendizaje
* Documentación actualizada
* Plugins externos

Filosofías:
* KISS (Keep it Simple and Stupid)
* DRY (Don't repeat yourself)


Documentación de uso, configuración y links de descarga:
[www.supernovaphp.com](www.supernovaphp.com)

----------

Ejemplo primitivo de uso
========================
Esto te permitirá utilizar el scaffolding integrado del framework,
éste hará ingenieria inversa de la tabla de tu base de datos con el nombre de tu modelo y
traerá los campos del formulario para el CRUD

Importante:
* Asegurate de tener el modulo **rewrite** activado
* Las tablas en la base de datos se llaman en plural
* Los modelos en singular
* Los campos en singular

Los 3 ejemplos son obligatorios para que funcione un modelo

Ejemplo de un modelo:
Nombre de archivo : Modelo.php en \App\Model
```
<?php

namespace App\Model;

class Modelo extends \Supernova\Model
{
    public $primaryKey = "id";
    public $defaultKey = "nombre";
    public $scaffolding = true;
}
```

Ejemplo de un modelo de formulario:
Nombre de archivo: ModeloForm.php en \App\Model
```
<?php

namespace App\Model;

class ModeloForm extends \App\Model\Modelo
{
    public $values;
    public $schema;
    public $notShow;
}
```

Ejemplo de un controlador:
Nombre de archivo : ModeloController.php en \App\Controller
```
<?php

namespace App\Controller;

class ModeloController extends \App\Main
{
}
```
