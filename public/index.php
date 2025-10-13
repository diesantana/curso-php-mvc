<?php
require_once('../vendor/autoload.php');

use bng\System\Router;



Router::dispatch();
echo "Conteúdo do index.php";

$nomes = ['bob brown', 'maria green', 'alex grey'];
$nome = 'ana rose';

printData($nomes);
// printData($nome);


