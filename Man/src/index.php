<?php

use App\Loaders\Parcours;
use App\Loaders\Arrets;
use App\Loaders\Bus;
use App\Loaders\Personnes;
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

$personnesList = [];

loadPersonnes(personnesList: $personnesList);

Bus::load(bus: $busList, config: $busJson);

Personnes::load(personnesList: $personnesList);

foreach (Bus::$buses as $bus) {
    $bus->demarrerParcours();
}

// foreach (Arrets::getArret("A")->vehiculesEnApproche as $key => $value) {
//     echo $key . '  ' . implode('/', $value) . PHP_EOL;
// }


foreach (Arrets::getArret("C")->vehiculesEnApproche as $key => $value) {
    echo $key . '  ' . implode('/', $value) . PHP_EOL;
}


echo '------ FIN ------' . PHP_EOL;

function loadPersonnes(array &$personnesList) {
    for ($i = 0; $i < 6; $i++) {
        $personnesList[] = [
            'nom' => "Albert{$i}",
            'aller' => [
                'depart' => 'B',
                'arrivee' => 'D',
                'temps' => 0,
            ],
            'retour' => [
                'depart' => 'D',
                'arrivee' => 'A',
                'temps' => 500,
            ]
        ];
    }
    
    for ($i = 0; $i < 12; $i++) {
        $personnesList[] = [
            'nom' => "Bob{$i}",
            'aller' => [
                'depart' => 'B',
                'arrivee' => 'C',
                'temps' => 0,
            ],
            'retour' => [
                'depart' => 'C',
                'arrivee' => 'B',
                'temps' => 500,
            ]
        ];
    
        $personnesList[] = [
            'nom' => "Charles{$i}",
            'aller' => [
                'depart' => 'B',
                'arrivee' => 'C',
                'temps' => 0,
            ],
            'retour' => [
                'depart' => 'C',
                'arrivee' => 'B',
                'temps' => 500,
            ]
        ];
    
        $personnesList[] = [
            'nom' => "Damien{$i}",
            'aller' => [
                'depart' => 'E',
                'arrivee' => 'A',
                'temps' => 0,
            ],
            'retour' => [
                'depart' => 'A',
                'arrivee' => 'E',
                'temps' => 600,
            ]
        ];
    }
    
    for ($i = 0; $i < 45; $i++) {
        $personnesList[] = [
            'nom' => "Edouard{$i}",
            'aller' => [
                'depart' => 'A',
                'arrivee' => 'C',
                'temps' => 0,
            ],
            'retour' => [
                'depart' => 'C',
                'arrivee' => 'A',
                'temps' => 300,
            ]
        ];
    }
}
