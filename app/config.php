<?php

define('APP_NAME', 'Basic Name Gathering');

// Database
define('MYSQL_HOST',     'localhost');
define('MYSQL_DATABASE', 'db_bng');
define('MYSQL_USERNAME', 'bng_user');
define('MYSQL_PASSWORD', ''); // criamos este usuário sem senha

// Chave para descriptografar os dados
define('MYSQL_AES_KEY', 'Vduu47qL51hLn6bkYkY6NlO1nivsmdfD');

// Destino do arquivo de log
define('LOGS_PATH', __DIR__ . '/../logs/app.log');