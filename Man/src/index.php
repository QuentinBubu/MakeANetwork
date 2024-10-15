<?php

use App\Loaders\Parcours;
use App\Loaders\Arrets;
use App\Loaders\Bus;
use App\Loaders\Routes;
use App\Loaders\Trajets;

require_once 'vendor/autoload.php';

echo '----- DEBUT -----' . PHP_EOL;

$arretsJson = json_decode(json: file_get_contents(filename: 'data/arrets.json'), associative: true);
$routesJson = json_decode(json: file_get_contents(filename: 'data/routes.json'), associative: true);
$parcoursJson = json_decode(json: file_get_contents(filename: 'data/parcours.json'), associative: true);
$busJson = json_decode(json: file_get_contents(filename: 'data/bus.json'), associative: true);

Arrets::load(arrets: $arretsJson);
Routes::load(routes: $routesJson);

Arrets::map();

Parcours::load(parcours: $parcoursJson);

// Vérification des routes
// var_dump(Routes::getRouteStr(arretA: 'E', arretB: 'C')->nom);

Trajets::findTrajet(depart: 'A', arrivee: 'C');
Trajets::findTrajet(depart: 'B', arrivee: 'C');

$busList = [
    [
        'type' => 'double',
        'parcours' => 'p1',
    ],
    [
        'type' => 'double',
        'parcours' => 'p2',
    ],
    [
        'type' => 'double',
        'parcours' => 'p3',
    ],
    [
        'type' => 'fast',
        'parcours' => 'p4',
    ],
];

Bus::load(bus: $busList, config: $busJson);

foreach (Bus::$buses as $bus) {
    echo $bus . PHP_EOL;
}

echo '------ FIN ------' . PHP_EOL;
