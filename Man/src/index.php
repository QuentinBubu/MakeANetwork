<?php

use Man\App\Loaders\Arrets;
use Man\App\Loaders\Routes;

require_once 'vendor/autoload.php';

echo '----- DEBUT -----' . PHP_EOL;

$arretsJson = json_decode(json: file_get_contents(filename: 'data/arrets.json', use_include_path: true), associative: true);
$routesJson = json_decode(json: file_get_contents(filename: 'data/routes.json'), associative: true);

Arrets::load(arrets: $arretsJson);
Routes::load(routes: $routesJson);

Arrets::map();



foreach (Routes::$routes as $route) {
    echo $route . PHP_EOL;
    echo '------' . PHP_EOL;
}

echo '------ FIN ------' . PHP_EOL;
