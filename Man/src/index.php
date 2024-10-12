<?php

use App\Loaders\Parcours;
use App\Loaders\Arrets;
use App\Loaders\Routes;

require_once 'vendor/autoload.php';

echo '----- DEBUT -----' . PHP_EOL;

$arretsJson = json_decode(json: file_get_contents(filename: 'data/arrets.json'), associative: true);
$routesJson = json_decode(json: file_get_contents(filename: 'data/routes.json'), associative: true);
$parcoursJson = json_decode(json: file_get_contents(filename: 'data/parcours.json'), associative: true);

Arrets::load(arrets: $arretsJson);
Routes::load(routes: $routesJson);

Arrets::map();

Parcours::load(parcours: $parcoursJson);

// Vérification des routes
// var_dump(Routes::getRouteStr(arretA: 'E', arretB: 'C')->nom);


foreach (Routes::$routes as $route) {
    echo $route . PHP_EOL;
}

foreach (Arrets::$arrets as $arret) {
    echo $arret . PHP_EOL;
}

echo '------ FIN ------' . PHP_EOL;
