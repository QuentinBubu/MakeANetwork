<?php

use Dotenv\Dotenv;
use App\Loaders\Bus;
use App\Loaders\Arrets;
use App\Loaders\Routes;
use App\Loaders\Trajets;
use App\Loaders\Parcours;
use App\Loaders\Personnes;
use App\Log\Message;
use App\State\State;
use App\Timer\Time;
use Dotenv\Repository\RepositoryBuilder;

require_once 'vendor/autoload.php';

$repository = RepositoryBuilder::createWithDefaultAdapters()->make();
$dotenv = Dotenv::create($repository, './');
$dotenv->load();
$dotenv->required(['UNIVERS_START', 'UNIVERS_END']);

Message::log('----- DEBUT -----');

$arretsJson = json_decode(json: file_get_contents(filename: 'data/arrets.json'), associative: true);
$routesJson = json_decode(json: file_get_contents(filename: 'data/routes.json'), associative: true);
$parcoursJson = json_decode(json: file_get_contents(filename: 'data/parcours.json'), associative: true);
$busJson = json_decode(json: file_get_contents(filename: 'data/bus.json'), associative: true);

Message::log('Chargement des arrêts');
Arrets::load(arrets: $arretsJson);

Message::log('Chargement des routes');
Routes::load(routes: $routesJson);

Message::log('Mapping des routes');
Arrets::map();

Message::log('Chargement des parcours');
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

Message::log('Chargement des bus');
Bus::load(bus: $busList, config: $busJson);

Message::log('Démarrage des parcours / bus');
/** @var App\Entities\Bus $bus */
// foreach (Bus::$buses as $bus) {
//     $bus->demarrerParcours();
// }

Message::log('Chargement des personnes');
$personnesList = [];
loadPersonnes(personnesList: $personnesList);
Personnes::load(personnesList: $personnesList);

foreach (Bus::$buses as $bus) {
    Message::log("Bus : " . spl_object_id($bus) . " affecté au parcours " . $bus->getParcours()->nom);
}


Message::log('------ FIN ------');

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

// Enregistrement des données
State::registerFunction(Arrets::class, 'export', 'arrets');
State::registerFunction(Bus::class, 'export', 'bus');
// State::registerFunction(Parcours::class, 'export', 'parcours');
// State::registerFunction(Personnes::class, 'export', 'personnes');
// State::registerFunction(Routes::class, 'export', 'routes');
// State::registerFunction(Trajets::class, 'export', 'trajets');
State::registerFunction(Time::class, 'export', 'time');

// Message::log(State::exportData());

while (Time::getTick() <= $_ENV['UNIVERS_END'] && count(Personnes::$personnes) > 0) {
    if (Time::getTick() < 15) {
        Message::log('GLOBAL TICK ' . Time::getTick(), Message::INFO);
        Message::log(State::exportData(), Message::INFO);
    }

    Time::run();
    Time::incrementTick();
    // Message::log(State::exportData());
    if (Time::getTick() % 100 === 0) {
        Message::log('GLOBAL TICK ' . Time::getTick(), Message::INFO);
        Message::log(State::exportData(), Message::INFO);
    }
}

Message::log('------ FIN ------', Message::INFO);
Message::log(State::exportData(), Message::INFO);
