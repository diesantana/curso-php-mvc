<?php

// require_once('../app/config.php');
// require_once('../app/controllers/Main.php');
require_once('../vendor/autoload.php');
use BNG\Controllers\Main as MainController;

echo APP_NAME;
echo '<br>';
$controller = new MainController();
echo $controller->teste();