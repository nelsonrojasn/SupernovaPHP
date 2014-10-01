<?php

include ROOT . DS . "Supernova" . DS . "config.php";
include ROOT . DS . "Supernova" . DS . "globalMethods.php";

\Supernova\Core::initialize();
\Supernova\Core::setRequest();
\Supernova\Core::setElements();
\Supernova\Core::checkController();
\Supernova\Core::processForm();
\Supernova\Core::checkAction();
